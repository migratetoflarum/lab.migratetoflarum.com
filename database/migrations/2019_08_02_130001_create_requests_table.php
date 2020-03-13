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
            $table->unsignedInteger('scan_id');
            $table->string('uid')->unique();
            $table->string('method');
            $table->string('url');
            $table->timestamp('fetched_at')->index();
            $table->unsignedInteger('duration');
            $table->json('exception')->nullable();
            $table->json('request_headers');
            $table->json('response_headers')->nullable();
            $table->unsignedSmallInteger('response_status_code')->nullable();
            $table->string('response_reason_phrase')->nullable();
            $table->mediumText('response_body')->nullable();
            $table->unsignedInteger('response_body_size')->nullable();
            $table->boolean('response_body_truncated')->nullable();

            $table->foreign('scan_id')->references('id')->on('scans')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requests');
    }
}
