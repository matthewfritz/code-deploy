<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentCommandTemplate extends Model
{
    protected $table = 'deployment_command_templates';
    protected $primaryKey = 'name';
    
    public $incrementing = false;

    protected $fillable = [
    	'name',
    	'description',
        'commands'
    ];
}
