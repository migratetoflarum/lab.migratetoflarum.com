<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionsTable extends Migration
{
    public function up()
    {
        Schema::create('extensions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('package')->unique();
            $table->string('flarumid')->index();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('abandoned')->nullable();
            $table->string('repository')->nullable();
            $table->json('icon')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('extensions');
    }
}
