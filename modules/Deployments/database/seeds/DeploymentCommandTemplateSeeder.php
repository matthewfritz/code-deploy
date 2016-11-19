<?php

use Illuminate\Database\Seeder;

use Deployments\Models\DeploymentCommandTemplate;

class DeploymentCommandTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$templates = [
    		[
                'name' => 'minimal',
                'description' => 'Minimal set of additional commands for deploying a repository',
                'commands' => ""
    		],
            [
                'name' => 'minimal-composer',
                'description' => 'Minimal set of additional commands with Composer for deploying a repository',
                'commands' => "composer install"
            ]
    	];

    	foreach($templates as $template) {
        	DeploymentCommandTemplate::create($template);
    	}
    }
}
