<?php

namespace PrivateKeys\Providers;

use Illuminate\Support\ServiceProvider;

use Laraway\Traits\RegistersMigrations;
use Laraway\Traits\RegistersSeeds;

class PrivateKeyServiceProvider extends ServiceProvider
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