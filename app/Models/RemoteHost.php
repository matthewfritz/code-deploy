<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteHost extends Model
{
    protected $table = 'remote_hosts';
    protected $primaryKey = 'name';
    
    public $incrementing = false;

    protected $fillable = [
    	'name',
    	'host',
    	'description'
    ];

    public function privateKey() {
    	return $this->hasOne(PrivateKey::class, 'remote_host_name', 'name')
    		->where('active', true);
    }
}
