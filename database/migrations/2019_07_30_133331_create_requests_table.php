<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_id');
            $table->boolean('sensitive');
            $table->string('method');
            $table->string('url');
            $table->timestamp('fetched_at')->index();
            $table->unsignedInteger('duration');
            $table->json('exception')->nullable();
            $table->json('request_headers');
            $table->json('response_headers')->nullable();
            $table->unsignedSmallInteger('response_status_code')->nullable();
            $table->string('response_reason_phrase')->nullable();
            $table->text('response_body')->nullable();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requests');
    }
}
