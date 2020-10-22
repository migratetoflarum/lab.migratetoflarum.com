<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterExtensionScanAddKeys extends Migration
{
    public function up()
    {
        Schema::table('extension_scan', function (Blueprint $table) {
            $table->primary(['extension_id', 'scan_id']);

            $table->foreign('extension_id')->references('id')->on('extensions')->onDelete('cascade');
            $table->foreign('scan_id')->references('id')->on('scans')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Not implemented on purpose
    }
}
