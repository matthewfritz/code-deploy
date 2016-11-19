<?php

namespace Deployments\Exceptions;

use Exception;

class InvalidDeploymentSecretException extends Exception
{
	public function __construct($message="The deployment secret does not match") {
		parent::__construct($message);
	}
}