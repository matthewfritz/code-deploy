<?php

namespace Deployments\Strategies;

use Deployments\Strategies\DeploymentStrategy;
use Deployments\Models\DeploymentConfiguration;

use Illuminate\Http\Request;

use SSH;

class DeploymentStrategyGitHub extends DeploymentStrategy
{
	/**
	 * Constructs a new DeploymentStrategyGitHub object with the specified
	 * array of deployment commands.
	 *
	 * @param array $commands Array of commands to execute
	 */
	public function __construct(array $commands) {
		parent::__construct($commands);
	}

	// documentation in implemented interface
	protected function after(DeploymentConfiguration $config) {
		return true;
	}

	// documentation in implemented interface
	protected function before(DeploymentConfiguration $config) {
		return true;
	}

	// documentation in implemented interface
	protected function checkDeploymentSecret(Request $request,
		DeploymentConfiguration $config,
        $secret) {
		if(!empty($config->secret)) {
            // there are configurations with secrets so we need to perform validity
            // checks. GitHub works differently with its secret values than a custom
            // git server would so we need to take that into account.

            // retrieve the secret header and strip off the sha1= portion
            $secretParts = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE']);
            $hAlgorithm = $secretParts[0];
            $hValue = $secretParts[1];

            // if the HMAC digest of the request from GitHub is different than the
            // the calculated digest below, the secrets do not match
            $calculated = hash_hmac($hAlgorithm, $request->getContent(), trim($c->secret));

            // if there are any configurations still in the collection, let's throw
            // the exeception
            if(!hash_equals($calculated, $hValue)) {
                throw new InvalidDeploymentSecretException(
                    "Deployment '{$config->deployment_name}' has a different secret for the following remote host: " .
                        $config->remote_host_name
                );
            }
        }
	}

	// documentation in implemented interface
	public function deploy(Request $request,
		DeploymentConfiguration $config,
		$secret) {
		// check the deployment secret, if any
		$this->checkDeploymentSecret($request, $config, $secret);

		// execute the necesary configuration functionality before the deployment
		$this->configure($config);

		// execute the necessary pre-deployment commands
		$this->before($config);

		// connect to the remote host and execute the commands
        SSH::run($commands, function($line) {
            $this->outputLines[] = trim($line);
        });
        $this->outputLines[] = "Done.";

		// execute the necessary post-deployment commands
		$this->after($config);

		return true;
	}
}