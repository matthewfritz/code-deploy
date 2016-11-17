<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentLog extends Model
{
    protected $table = 'deployment_log';

    protected $fillable = [
    	'remote_host',
    	'deployment_type',
    	'deployment_name',
    	'directory',
    	'branch',
    	'user',
    	'success',
    	'message',
        'output',
    ];
}
