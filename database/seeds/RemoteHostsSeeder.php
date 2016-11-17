<?php

use Illuminate\Database\Seeder;

use App\Models\RemoteHost;

class RemoteHostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$hosts = [
    		[
                'name' => 'meta-web-testing',
    			'host' => '130.166.38.206',
    			'description' => 'META+Lab Web-Testing virtual machine'
    		],
    		[
                'name' => 'meta-cdn',
    			'host' => '130.166.38.144',
    			'description' => 'META+Lab CDN'
    		],
    	];

    	foreach($hosts as $host) {
        	RemoteHost::create($host);
    	}
    }
}
