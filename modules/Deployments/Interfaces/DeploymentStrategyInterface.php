<?php

namespace Deployment\Strategies;

use Deployments\Models\DeploymentConfiguration;

interface DeploymentStrategyInterface
{
	public function deploy(DeploymentConfiguration $config);
}