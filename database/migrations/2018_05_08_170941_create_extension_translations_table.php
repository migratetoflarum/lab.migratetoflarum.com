<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('extension_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('version_id');
            $table->unsignedInteger('locale_id');
            $table->string('namespace')->index();
            $table->unsignedInteger('namespace_extension_id')->nullable();
            $table->unsignedInteger('strings_count');
            $table->timestamps();

            $table->unique(['version_id', 'locale_id', 'namespace']);

            $table->foreign('version_id')->references('id')->on('extension_versions')->onDelete('cascade');
            $table->foreign('locale_id')->references('id')->on('locales')->onDelete('cascade');
            $table->foreign('namespace_extension_id')->references('id')->on('extensions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extension_translations');
    }
}
