<?php

namespace Deployments\Factories;

use Deployment\Exceptions\InvalidDeploymentTypeException;

use Deployments\Strategies\DeploymentStrategyGitHub;

class DeploymentFactory
{
	protected $types = [
		'github' => DeploymentStrategyGitHub::class,
	];

	public static function fromType($type) {
		if(in_array($type, $this->types)) {
			return $types[$type];
		}

		throw new InvalidDeploymentTypeException(
			"{$type} is an invalid deployment type"
		);
	}
}