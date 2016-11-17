<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentType extends Model
{
    protected $table = 'deployment_types';
    protected $primaryKey = 'type';

    public $increments = false;

    protected $fillable = [
    	'type',
    	'description'
    ];
}
