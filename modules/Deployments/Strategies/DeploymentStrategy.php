<?php

namespace Deployment\Strategies;

use Deployments\Models\DeploymentConfiguration;

use Illuminate\Http\Request;

abstract class DeploymentStrategy
{
     /**
      * Common commands for deployment.
      *
      * @var array
      */
     protected $commands;

     /**
      * Lines of output from the deployment.
      *
      * @var array
      */
     protected $outputLines;

     /**
      * Constructs a new DeploymentStrategyGitHub object with the specified
      * array of deployment commands.
      *
      * @param array $commands Array of commands to execute
      */
     public function __construct(array $commands) {
          $this->commands = $commands;
          $this->outputLines = [];
     }

     /**
      * Configures and executes the commands before a deployment.
      *
      * @param DeploymentConfiguration $config The configuration to use
      */
     protected function configure(DeploymentConfiguration $config) {
          $this->configureSSH($config);
     }

     /**
     * Configures the SSH capabilities using a complete deployment configuration.
     *
     * @param DeploymentConfiguration $config The configuration to use
     */
    protected function configureSSH(DeploymentConfiguration $config) {
        $host = $config->remoteHost->host;
        $user = $config->user;
        $key = $config->remoteHost->privateKey->path;

        $dc = config('remote.default');
        config([
            "remote.connections.{$dc}.host" => $host,
            "remote.connections.{$dc}.username" => $user,
            "remote.connections.{$dc}.key" => $key
        ]);
    }

     /**
      * Returns the array containing the server commands for this deployment.
      *
      * @return array
      */
     public function getDeploymentCommands() {
          return $this->commands;
     }

     /**
      * Returns the array containing the lines of output for this deployment.
      *
      * @return array
      */
     public function getOutputLines() {
          return $this->outputLines;
     }

     /**
      * Executes necessary commands after the deployment.
      *
      * @param DeploymentConfiguration $config The configuration to use
      */
     protected abstract function after(DeploymentConfiguration $config);

     /**
      * Executes necessary commands before the deployment.
      *
      * @param DeploymentConfiguration $config The configuration to use
      */
     protected abstract function before(DeploymentConfiguration $config);

    /**
     * Performs the deployment to a server. Returns a boolean after the deployment.
     *
     * @param Request $request The contents of the request for deployment
     * @param DeploymentConfiguration $config Deployment configuration object
     * @param string $secret The secret to validate
     *
     * @return bool
     */
	public abstract function deploy(Request $request,
          DeploymentConfiguration $config
          $secret);

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
	protected abstract function checkDeploymentSecret(Request $request,
		DeploymentConfiguration $config,
        $secret);
}