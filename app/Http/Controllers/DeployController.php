<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\InvalidDeploymentNameException;
use App\Exceptions\InvalidPrivateKeyException;

use App\Http\Controllers\Controller;

use App\Models\DeploymentConfiguration;
use App\Models\DeploymentLog;
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

        // perform a deployment check and create it (this can throw exceptions)
        $config = $this->createDeployment($deploymentName);

        // TODO: Use the SSH facade to perform an SSH connection using
        // the host and private key config parameters (make sure to set
        // these with the config() helper and the remote.php values

        //return $config;
    }

    /**
     * Performs a deployment validity check. Returns the Collection of configuration
     * objects on a valid configuration or throws an exception.
     *
     * @param string $deploymentName The name of the deployment
     *
     * @throws InvalidDeploymentNameException
     * @throws InvalidPrivateKeyException
     * @throws InvalidRemoteHostException
     */
    private function createDeployment($deploymentName) {
        $config = DeploymentConfiguration::with('remoteHost.privateKey')
            ->where('deployment_name', $deploymentName)
            ->get();

        // if there are no deployment configurations retrieved with the
        // deployment name, throw an exception
        if($config->isEmpty()) {
            throw new InvalidDeploymentNameException(
                "{$deploymentName} is an invalid deployment name"
            );
        }

        // if there is an invalid remote host anywhere, throw an exception
        $invalid = $config->filter(function($conf) {
            return is_null($conf->remote_host);
        });
        if(!$invalid->isEmpty()) {
            throw new InvalidRemoteHostException(
                "{$deploymentName} contains the following invalid remote hosts: " .
                    $invalid->implode('remote_host_name', ', ');
            );
        }

        // if there is an invalid private key anywhere, throw an exception
        $invalid = $config->filter(function($conf) {
            if(is_null($conf->remote_host->privateKey)) {
                return true;
            }
            return (!file_exists($conf->remote_host->private_key->path) ||
                !is_readable($conf->remote_host->private_key->path));
        });

        if(!$invalid->isEmpty()) {
            throw new InvalidRemoteHostException(
                "{$deploymentName} contains invalid private keys for the following remote hosts: " .
                    $invalid->implode('remote_host_name', ', ') .
                ". Check that the keys exist and are readable by the web server.";
            );
        }

        // all checks passed, so return the config objects
        return $config;
    }
}
