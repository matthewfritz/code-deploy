<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\DeploymentConfiguration;
use App\Models\DeploymentLog;
use App\Models\PrivateKey;
use App\Models\RemoteHost;

class DeployController extends Controller
{
    /**
     * Performs the deployment to a server.
     */
    public function deploy(Request $request) {
        $deploymentName = $request->input('name');
        $config = DeploymentConfiguration::with('remoteHost.privateKey')
            ->where('deployment_name', $deploymentName)
            ->firstOrFail();

        return $config;
    }
}
