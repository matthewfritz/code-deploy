<?php

namespace RemoteHosts\Providers;

use Illuminate\Support\ServiceProvider;

use Laraway\Traits\RegistersMigrations;
use Laraway\Traits\RegistersSeeds;

class RemoteHostServiceProvider extends ServiceProvider
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