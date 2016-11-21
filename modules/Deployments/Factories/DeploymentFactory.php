<?php

namespace Deployments\Factories;

use Deployments\Exceptions\InvalidDeploymentTypeException;

use Deployments\Strategies\DeploymentStrategyGitHub;

class DeploymentFactory
{
	protected static $types = [
		'github' => DeploymentStrategyGitHub::class,
	];

	public static function fromType($type) {
		if(array_key_exists($type, self::$types)) {
			return self::$types[$type];
		}

		throw new InvalidDeploymentTypeException(
			"{$type} is an invalid deployment type"
		);
	}
}