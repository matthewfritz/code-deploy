<?php

namespace Deployments\Providers;

use Illuminate\Support\ServiceProvider;

use Laraway\Traits\RegistersMigrations;
use Laraway\Traits\RegistersSeeds;

class DeploymentServiceProvider extends ServiceProvider
{
	use RegistersMigrations;
	use RegistersSeeds;

	public function register()
	{
		$this->registerMigrations(__DIR__);
		$this->registerSeeds(__DIR__);
	}

	public function boot()
	{

	}
}