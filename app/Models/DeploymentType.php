<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentType extends Model
{
    protected $table = 'deployment_types';
    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $fillable = [
    	'name',
    	'description'
    ];
}
