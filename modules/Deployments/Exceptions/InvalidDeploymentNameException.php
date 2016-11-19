<?php

namespace Deployments\Exceptions;

use Exception;

class InvalidDeploymentNameException extends Exception
{
	public function __construct($message="The deployment name is invalid") {
		parent::__construct($message);
	}
}