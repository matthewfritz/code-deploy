<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentConfiguration extends Model
{
    protected $table = 'deployment_configuration';

    protected $fillable = [
    	'remote_host_name',
    	'deployment_type',
    	'deployment_name',
    	'description',
    	'secret',
    	'directory',
    	'branch',
    	'user'
    ];
}
