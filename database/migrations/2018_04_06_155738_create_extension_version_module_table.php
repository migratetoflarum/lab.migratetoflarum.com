<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionVersionModuleTable extends Migration
{
    public function up()
    {
        Schema::create('extension_version_module', function (Blueprint $table) {
            $table->unsignedInteger('version_id');
            $table->unsignedInteger('module_id');
            $table->string('checksum')->nullable();
            $table->timestamps();

            $table->primary(['version_id', 'module_id']);

            $table->foreign('version_id')->references('id')->on('extension_versions')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('javascript_modules')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extension_version_module');
    }
}
