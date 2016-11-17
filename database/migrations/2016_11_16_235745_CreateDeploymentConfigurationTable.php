<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeploymentConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deployment_configuration', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('remote_host_id');
            $table->string('deployment_type')->default('github');
            $table->string('deployment_name');
            $table->string('directory'); // absolute path to where the .git directory resides
            $table->string('user')->default('metadeploy'); // user account on the remote host that will deploy

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('deployment_configuration');
    }
}
