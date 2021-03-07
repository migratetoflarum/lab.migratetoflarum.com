<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRequestsAddCompressedSize extends Migration
{
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedInteger('response_body_compressed_size')->nullable();
        });
    }

    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('response_body_compressed_size');
        });
    }
}
