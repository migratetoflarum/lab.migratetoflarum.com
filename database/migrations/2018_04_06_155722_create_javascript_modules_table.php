<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJavascriptModulesTable extends Migration
{
    public function up()
    {
        Schema::create('javascript_modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('stack'); // forum/admin
            $table->string('module')->index();
            $table->timestamps();

            $table->unique(['stack', 'module']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('javascript_modules');
    }
}
