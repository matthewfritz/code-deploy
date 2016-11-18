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
            $deploymentName,
            $deploymentSecret
        );

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
                $outputLines[] = trim($line);
            });
            $outputLines[] = "Done.";

            // generate some metadata about the deployment
            $message = "Deployment complete";
            
            // create a log record of the deployment
            $log = DeploymentLog::create([
                'remote_host' => $config->remoteHost->host,
                'deployment_type' => $config->deployment_type_name,
                'deployment_name' => $config->deployment_name,
                'directory' => $config->directory,
                'branch' => $config->branch,
                'user' => $config->user,
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
     * @param string $deploymentName The name of the deployment
     * @param string $deploymentSecret Optional secret value from the request
     *
     * @throws InvalidDeploymentNameException
     * @throws InvalidDeploymentTypeException
     * @throws InvalidPrivateKeyException
     * @throws InvalidRemoteHostException
     */
    private function createDeploymentConfiguration($deploymentName,
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

        // check the secret value for validity
        $this->checkDeploymentSecret($config, $deploymentSecret);

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
     * Checks the validity of the secret for the configuration set. Throws an
     * exception if the secret cannot be verified.
     *
     * @param Collection:DeploymentConfiguration $config Set of deployment configuration
     * @param string $secret The secret to validate
     *
     * @throws InvalidDeploymentSecretException
     */
    private function checkDeploymentSecret($config, $secret) {
        // retrieve all configurations with a secret value
        $configs = $config->filter(function($c) {
            return !empty($c->secret);
        });
        if(!$configs->isEmpty()) {
            // there are configurations with secrets so we need to perform validity
            // checks. GitHub works differently with its secret values than a custom
            // git server would so we need to take that into account.
            $invalid = $configs->filter(function($c) use ($secret) {
                if($c->deployment_type_name == "github") {
                    // retrieve the secret header and strip off the sha1= portion
                    $hSecret = header('X-Hub-Signature');
                    $hSecret = trim(str_replace('sha1=', '', $hSecret));

                    // check whether the sha1 version of the secret in the deployment
                    // configuration is different the value in the header and is
                    // therefore invalid
                    return sha1(trim($c->secret)) != $hSecret;
                }
                else
                {
                    // custom Git server so it's a simple check for whether the
                    // secret is invalid
                    return trim($c->secret) != trim($secret);
                }
            });

            // if there are any configurations in the collection, let's throw the
            // exeception
            if(!$invalid->isEmpty()) {
                throw new InvalidDeploymentSecretException(
                    "Deployment '{$deploymentName}' has different secrets for the following remote hosts: " .
                        $invalid->implode('remote_host_name', ', ')
                );
            }
        }
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
