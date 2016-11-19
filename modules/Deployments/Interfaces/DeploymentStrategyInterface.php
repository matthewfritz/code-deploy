<?php

namespace Deployment\Strategies;

interface DeploymentStrategyInterface
{
	public function deploy(DeploymentConfiguration $config);
}