<?php

namespace Deployments\Http\Controllers;

use App\Http\Controllers\Controller;

use Carbon\Carbon;

use Illuminate\Http\Request;

use Deployments\Exceptions\InvalidDeploymentNameException;
use Deployments\Exceptions\InvalidDeploymentSecretException;
use Deployments\Exceptions\InvalidDeploymentTypeException;

use Deployments\Factories\DeploymentFactory;

use Deployments\Models\DeploymentCommandTemplate;
use Deployments\Models\DeploymentConfiguration;
use Deployments\Models\DeploymentLog;

use Deployments\Strategies\DeploymentStrategyGitHub;

use PrivateKeys\Exceptions\invalidPrivateKeyException;

use RemoteHosts\Exceptions\InvalidRemoteHostException;

class DeploymentController extends Controller
{
    /**
     * Performs the deployment to a server.
     */
    public function deploy(Request $request) {
        $deploymentName = $request->input('name');
        $deploymentSecret = $request->input('secret');

        // metadata about the deployment
        $success = true;
        $code = 200;
        $data = [
            'deployment_name' => $deploymentName,
            'deployment_time' =>
                Carbon::now(config('app.timezone'))->toDateTimeString(),
            'results' => []
        ];

        // perform a deployment check and create its configuration. Note that
        // this method can throw exceptions
        $configSet = $this->createDeploymentConfiguration(
            $request,
            $deploymentName,
            $deploymentSecret
        );

        // deploy for each configuration in the collection
        foreach($configSet as $config) {
            // create the common deployment commands
            $commands = createCommonDeploymentCommands(
                $config->directory,
                $config->branch,
                $config->user,
                $config->group
            );

            // retrieve and add the additional post-deployment commands, if any
            $additionalCommands = (!empty($config->commandTemplate) ?
                explode("\n", $config->commandTemplate->commands) : []);
            if(!empty($additionalCommands)) {
                foreach($additionalCommands as $command) {
                    // take whitespace and control characters into account
                    if(!empty($command)) {
                        $commands[] = trim($command);
                    }
                }
            }

            // figure out the deployment strategy and execute it
            $strategy = DeploymentFactory::fromType($config->deployment_type_name);
            $strategy = new $strategy($commands);
            $strategy->deploy($request, $config, $deploymentSecret);

            // generate some metadata about the deployment
            $message = "Deployment complete";
            
            // create a log record of the deployment
            $log = DeploymentLog::create([
                'remote_host_name' => $config->remoteHost->name,
                'remote_host' => $config->remoteHost->host,
                'deployment_type' => $config->deployment_type_name,
                'deployment_name' => $config->deployment_name,
                'directory' => $config->directory,
                'branch' => $config->branch,
                'user' => $config->user,
                'group' => $config->group,
                'success' => $success,
                'message' => $message,
                'output' => implode("\n", $outputLines),
            ]);
            $data['results'][] = $log;
        }

        return sendJsonResponse(
            $success,
            $code,
            "Deployment complete",
            $data
        );
    }

    /**
     * Performs a deployment validity check. Returns the Collection of configuration
     * objects on a valid configuration or throws an exception.
     *
     * @param Request $request The contents of the request for deployment
     * @param string $deploymentName The name of the deployment
     * @param string $deploymentSecret Optional secret value from the request
     *
     * @throws InvalidDeploymentNameException
     * @throws InvalidDeploymentTypeException
     * @throws InvalidPrivateKeyException
     * @throws InvalidRemoteHostException
     */
    private function createDeploymentConfiguration(
        Request $request,
        $deploymentName,
        $deploymentSecret=NULL) {
        $config = DeploymentConfiguration::with(
            'commandTemplate',
            'deploymentType',
            'remoteHost.privateKey'
        )
        ->where('deployment_name', $deploymentName)
        ->get();

        // if there are no deployment configurations retrieved with the
        // deployment name, throw an exception
        if($config->isEmpty()) {
            throw new InvalidDeploymentNameException(
                "'{$deploymentName}' is an invalid deployment name"
            );
        }

        // if there is an invalid deployment type anywhere, throw an exception
        $invalid = $config->filter(function($conf) {
            return is_null($conf->deploymentType);
        });
        if(!$invalid->isEmpty()) {
            throw new InvalidDeploymentTypeException(
                "Deployment '{$deploymentName}' contains the following invalid deployment types: " .
                    $invalid->implode('deployment_type_name', ', ')
            );
        }

        // if there is an invalid remote host anywhere, throw an exception
        $invalid = $config->filter(function($conf) {
            return is_null($conf->remoteHost);
        });
        if(!$invalid->isEmpty()) {
            throw new InvalidRemoteHostException(
                "Deployment '{$deploymentName}' contains the following invalid remote hosts: " .
                    $invalid->implode('remote_host_name', ', ')
            );
        }

        // if there is an invalid private key anywhere (no private key), throw an exception
        $invalid = $config->filter(function($conf) {
            return is_null($conf->remoteHost->privateKey);
        });
        if(!$invalid->isEmpty()) {
            throw new InvalidPrivateKeyException(
                "Deployment '{$deploymentName}' contains no private key for the following remote hosts: " .
                    $invalid->implode('remote_host_name', ', ')
            );
        }

        // if there is an invalid private key anywhere (non-readable), throw an exception
        $invalid = $config->filter(function($conf) {
            return (!file_exists($conf->remoteHost->privateKey->path) ||
                !is_readable($conf->remoteHost->privateKey->path));
        });
        if(!$invalid->isEmpty()) {
            throw new InvalidPrivateKeyException(
                "Deployment '{$deploymentName}' contains invalid private keys for the following remote hosts: " .
                    $invalid->implode('remote_host_name', ', ') .
                ". Check that the keys exist and are readable by the web server."
            );
        }

        // all checks passed, so return the config objects
        return $config;
    }
}
