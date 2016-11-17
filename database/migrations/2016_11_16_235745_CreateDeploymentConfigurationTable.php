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
            $table->string('remote_host_name');
            $table->string('deployment_name');
            $table->string('deployment_type')->default('github');
            $table->string('description')->nullable();
            $table->string('secret')->nullable(); // any secret value passed along to identify the deployment
            $table->string('directory'); // absolute path to where the .git directory resides
            $table->string('branch')->default('master')->nullable(); // Git branch to pull
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
