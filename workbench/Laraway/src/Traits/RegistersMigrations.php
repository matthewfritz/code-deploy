<?php

namespace Laraway\Traits;

trait RegistersMigrations
{
	/**
     * Register the migrations so Laravel can publish them
     */
    private function registerMigrations($dir)
    {
        $this->publishes([
            $dir.'/database/migrations' => base_path('database/migrations'),
        ]);
    }
}