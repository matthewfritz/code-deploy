<?php

use Illuminate\Database\Seeder;

use App\Models\PrivateKey;

class PrivateKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // this key exists on web-testing
    	$keys = [
    		[
                'remote_host_name' => 'meta-web-testing',
    			'path' => '/home/www-data/.ssh/meta-web-testing',
                // default here is active
    		],
    	];

    	foreach($keys as $key) {
        	PrivateKey::create($key);
    	}
    }
}
