<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionScanTable extends Migration
{
    public function up()
    {
        Schema::create('extension_scan', function (Blueprint $table) {
            $table->unsignedInteger('extension_id');
            $table->unsignedInteger('scan_id');
            $table->json('possible_versions')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('extension_scan');
    }
}
