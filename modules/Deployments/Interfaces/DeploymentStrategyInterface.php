<?php

namespace Deployment\Strategies;

use Deployments\Models\DeploymentConfiguration;

use Illuminate\Http\Request;

interface DeploymentStrategyInterface
{
	/**
     * Performs the deployment to a server.
     *
     * @param DeploymentConfiguration $config The deployment configuration object
     */
	public function deploy(DeploymentConfiguration $config);

	/**
     * Checks the validity of the secret for the configuration object. Throws an
     * exception if the secret cannot be verified.
     *
     * @param Request $request The contents of the request for deployment
     * @param DeploymentConfiguration $config Deployment configuration object
     * @param string $secret The secret to validate
     *
     * @throws InvalidDeploymentSecretException
     */
	public function checkDeploymentSecret(Request $request,
		DeploymentConfiguration $config,
        $secret);
}