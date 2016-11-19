<?php

namespace Integrations\Providers;

use Illuminate\Support\ServiceProvider;

use Laraway\Traits\RegistersMigrations;

class IntegrationServiceProvider extends ServiceProvider
{
	use RegistersMigrations;

	public function register()
	{
		$this->registerMigrations(__DIR__);
	}

	public function boot()
	{

	}
}