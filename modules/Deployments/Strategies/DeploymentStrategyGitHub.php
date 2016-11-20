<?php

namespace Deployments\Strategies;

use Deployments\Interfaces\DeploymentStrategyInterface;
use Deployments\Models\DeploymentConfiguration;

class DeploymentStrategyGitHub implements DeploymentStrategyInterface
{
	protected $commands;

	/**
	 * Constructs a new DeploymentStrategyGitHub object with the specified
	 * array of deployment commands.
	 *
	 * @param array $commands Array of commands to execute
	 */
	public function __construct(array $commands) {
		$this->commands = $commands;
	}

	// documentation in implemented interface
	public function deploy(DeploymentConfiguration $config) {
		return true;
	}

	// documentation in implemented interface
	public function checkDeploymentSecret(Request $request,
		DeploymentConfiguration $config,
        $secret) {
		if(!empty($config->secret)) {
            // there are configurations with secrets so we need to perform validity
            // checks. GitHub works differently with its secret values than a custom
            // git server would so we need to take that into account.
            $invalid = $configs->filter(function($c) use ($secret, $request) {
                // retrieve the secret header and strip off the sha1= portion
                $secretParts = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE']);
                $hAlgorithm = $secretParts[0];
                $hValue = $secretParts[1];

                // if the HMAC digest of the request from GitHub is different than the
                // the calculated digest below, the secrets do not match
                $calculated = hash_hmac($hAlgorithm, $request->getContent(), trim($c->secret));
                return !hash_equals($calculated, $hValue);
            });

            // if there are any configurations still in the collection, let's throw
            // the exeception
            if(!$invalid->isEmpty()) {
                throw new InvalidDeploymentSecretException(
                    "Deployment '{$config->deployment_name}' has a different secret for the following remote host: " .
                        $config->remote_host_name
                );
            }
        }
	}
}