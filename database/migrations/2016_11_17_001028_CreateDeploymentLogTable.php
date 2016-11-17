<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeploymentLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deployment_log', function(Blueprint $table) {
            $table->increments('id');
            $table->string('remote_host'); // not a FK because we want to know if hosts change
            $table->string('deployment_type')->default('github');
            $table->string('deployment_name');
            $table->string('directory');
            $table->string('branch')->default('master')->nullable();
            $table->string('user')->default('metadeploy');

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
        Schema::drop('deployment_log');
    }
}
