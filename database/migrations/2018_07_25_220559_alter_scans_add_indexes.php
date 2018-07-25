<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScansAddIndexes extends Migration
{
    public function up()
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->index('hidden');
            $table->index('scanned_at');
        });
    }

    public function down()
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropIndex('scans_hidden_index');
            $table->dropIndex('scans_scanned_at_index');
        });
    }
}
