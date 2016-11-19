<?php

namespace Laraway\Traits;

trait RegistersSeeds
{
	/**
     * Register the migrations so Laravel can publish them
     */
    private function registerSeeds($dir)
    {
        $this->publishes([
            $dir.'/database/seeds' => base_path('database/seeds'),
        ]);
    }
}