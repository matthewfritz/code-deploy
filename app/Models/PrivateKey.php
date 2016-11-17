<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateKey extends Model
{
    protected $table = 'private_keys';

    protected $fillable = [
    	'remote_host_name',
    	'path',
    	'active'
    ];
}
