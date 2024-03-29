<?php

use Illuminate\Database\Seeder;

use App\Models\DeploymentType;

class DeploymentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$types = [
    		[
    			'name' => 'github',
    			'description' => 'A deployment from a GitHub repository'
    		],
    		[
    			'name' => 'git',
    			'description' => 'A deployment from a custom Git server'
    		],
    	];

    	foreach($types as $type) {
        	DeploymentType::create($type);
    	}
    }
}
