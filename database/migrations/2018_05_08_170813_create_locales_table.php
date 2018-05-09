<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocalesTable extends Migration
{
    public function up()
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('localized_name')->nullable()->index();
            $table->string('english_name')->nullable()->index();
            $table->boolean('hidden')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('locales');
    }
}
