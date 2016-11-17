<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteHost extends Model
{
    protected $table = 'remote_hosts';
    protected $primaryKey = 'name';
    
    public $increments = false;

    protected $fillable = [
    	'name',
    	'host',
    	'description'
    ];
}
