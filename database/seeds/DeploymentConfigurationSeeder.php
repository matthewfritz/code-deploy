<?php

use Illuminate\Database\Seeder;

use App\Models\DeploymentConfiguration;

class DeploymentConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$deployments = [
    		[
                'remote_host_name' => 'meta-web-testing',
                'deployment_type' => 'github',
                'deployment_name' => 'test-continuous-deployment',
    			'description' => 'Configuration for testing the continuous deployment functionality',
                'secret' => 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3',
                'directory' => '/var/www/testing/test-deploy/laravel',
                'branch' => 'master',
                'user' => 'metadeploy'
    		],
    	];

    	foreach($deployments as $deployment) {
        	DeploymentConfiguration::create($deployment);
    	}
    }
}
