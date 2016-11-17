<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DeploymentTypesSeeder::class);
        $this->call(RemoteHostsSeeder::class);
        $this->call(DeploymentConfigurationSeeder::class);
        $this->call(PrivateKeySeeder::class);
    }
}
