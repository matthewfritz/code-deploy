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
        $config = DeploymentConfiguration::with('remoteHost.privateKey')
            ->where('deployment_name', $deploymentName)
            ->get();

        // if there are no deployment configurations retrieved with the
        // deployment name, error out
        if($config->isEmpty()) {
            throw new InvalidDeploymentNameException(
                "{$deploymentName} is an invalid deployment name"
            );
        }

        // TODO: Throw errors if there is an invalid remote host or there
        // is an invalid private key

        // TODO: Use the SSH facade to perform an SSH connection using
        // the host and private key config parameters (make sure to set
        // these with the config() helper and the remote.php values

        //return $config;
    }
}
