<?php

namespace Deployments\Models;

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
        'group',
    	'success',
    	'message',
        'output',
    ];

    protected $appends = ['output_lines'];

    /**
     * Property to retrieve the output lines as an array.
     *
     * @return array
     */
    public function getOutputLinesAttribute() {
        return explode("\n", $this->output);
    }
}
