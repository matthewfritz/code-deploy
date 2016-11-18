<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentConfiguration extends Model
{
    protected $table = 'deployment_configuration';

    protected $fillable = [
    	'remote_host_name',
    	'deployment_type_name',
    	'deployment_name',
    	'description',
        'command_template_name',
    	'secret',
    	'directory',
    	'branch',
    	'user',
        'group',
    ];

    public function remoteHost() {
        return $this->hasOne(RemoteHost::class, 'name', 'remote_host_name');
    }

    public function commandTemplate() {
        return $this->hasOne(DeploymentCommandTemplate::class, 'name', 'command_template_name');
    }

    public function deploymentType() {
        return $this->hasOne(DeploymentType::class, 'name', 'deployment_type_name');
    }
}
