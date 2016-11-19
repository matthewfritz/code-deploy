<?php

namespace Deployments\Exceptions;

use Exception;

class InvalidDeploymentTypeException extends Exception
{
	public function __construct($message="The deployment type is invalid") {
		parent::__construct($message);
	}
}