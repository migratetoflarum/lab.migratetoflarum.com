<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionVersionsTable extends Migration
{
    public function up()
    {
        Schema::create('extension_versions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('extension_id');
            $table->string('version')->index();
            $table->json('packagist');
            $table->boolean('hidden')->default(false)->index();
            $table->timestamps();

            $table->unique(['extension_id', 'version']);

            $table->foreign('extension_id')->references('id')->on('extensions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extension_versions');
    }
}
