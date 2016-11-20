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
     * Performs the deployment to a server. Returns a boolean after the deployment.
     *
     * @param DeploymentConfiguration $config The deployment configuration object
     *
     * @return boolean
     */
	public abstract function deploy(DeploymentConfiguration $config);

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
	public abstract function checkDeploymentSecret(Request $request,
		DeploymentConfiguration $config,
        $secret);
}