<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Http\Request;

use App\Exceptions\InvalidDeploymentNameException;
use App\Exceptions\InvalidDeploymentTypeException;
use App\Exceptions\InvalidPrivateKeyException;
use App\Exceptions\InvalidRemoteHostException;

use App\Http\Controllers\Controller;

use App\Models\DeploymentCommandTemplate;
use App\Models\DeploymentConfiguration;
use App\Models\DeploymentLog;
use App\Models\DeploymentType;
use App\Models\PrivateKey;
use App\Models\RemoteHost;

use SSH;

class DeployController extends Controller
{
    /**
     * Performs the deployment to a server.
     */
    public function deploy(Request $request) {
        // metadata about the deployment
        $success = true;
        $code = 200;
        $messages = [];
        $data = [
            'deployment_time' => Carbon::now(),
        ];

        $deploymentName = $request->input('name');

        // perform a deployment check and create its configuration. Note that
        // this method can throw exceptions
        $configSet = $this->createDeploymentConfiguration($deploymentName);

        // deploy for each configuration in the collection
        foreach($configSet as $config) {
            // create the common deployment commands
            $commands = createCommonDeploymentCommands(
                $config->directory,
                $config->branch
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

            // configure the SSH connection for the deployment
            $this->configureSSH($config);

            // connect to the remote host and execute the commands
            $outputLines = [];
            SSH::run($commands, function($line) use (&$outputLines) {
                $outputLines[] = $line;
            });
            $outputLines[] = "Done.";

            // spit out a success message (temp for now)
            $message = "Deployment was successful";
            $messages[] = $message . " (host={$config->remoteHost->host}, dir={$config->directory})";
            
            // create a log record of the deployment
            DeploymentLog::create([
                'remote_host' => $config->remoteHost->host,
                'deployment_type' => $config->deployment_type_name,
                'deployment_name' => $config->deployment_name,
                'directory' => $config->directory,
                'branch' => $config->branch,
                'user' => $config->user,
                'success' => $success,
                'message' => $message,
                'output' => implode("\n", $outputLines);
            ]);
        }

        $data['deployment_name'] => $deploymentName;
        return sendJsonResponse(
            $success,
            $code,
            implode("\n", $messages),
            $data
        );
    }

    /**
     * Performs a deployment validity check. Returns the Collection of configuration
     * objects on a valid configuration or throws an exception.
     *
     * @param string $deploymentName The name of the deployment
     *
     * @throws InvalidDeploymentNameException
     * @throws InvalidDeploymentTypeException
     * @throws InvalidPrivateKeyException
     * @throws InvalidRemoteHostException
     */
    private function createDeploymentConfiguration($deploymentName) {
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

    /**
     * Configures the SSH capabilities using a complete deployment configuration.
     *
     * @param DeploymentConfiguration $deploymentConfiguration The configuration to use
     */
    private function configureSSH($deploymentConfiguration) {
        $host = $deploymentConfiguration->remoteHost->host;
        $user = $deploymentConfiguration->user;
        $key = $deploymentConfiguration->remoteHost->privateKey->path;

        $dc = config('remote.default');
        config([
            "remote.connections.{$dc}.host" => $host,
            "remote.connections.{$dc}.username" => $user,
            "remote.connections.{$dc}.key" => $key
        ]);
    }
}
