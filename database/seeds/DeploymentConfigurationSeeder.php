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
                'deployment_type_name' => 'github',
                'deployment_name' => 'test-continuous-deployment',
    			'description' => 'Configuration for testing the continuous deployment functionality',
                'secret' => 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3',
                'directory' => '/var/www/testing/test-deploy/laravel',
                'branch' => 'master',
                'user' => 'metadeploy',
                'group' => 'www-data',
    		],
            [
                'remote_host_name' => 'meta-cdn',
                'deployment_type_name' => 'github',
                'deployment_name' => 'deploy-metaphor',
                'description' => 'Configuration to deploy Metaphor to the CDN',
                'secret' => 'b231b8df0a22d518f393c4048f67aaefd7f6c77a',
                'directory' => '/var/www/repos/metaphor',
                'branch' => 'master',
                'user' => 'metadeploy',
                'group' => 'www-data',
            ],
    	];

    	foreach($deployments as $deployment) {
        	DeploymentConfiguration::create($deployment);
    	}
    }
}
