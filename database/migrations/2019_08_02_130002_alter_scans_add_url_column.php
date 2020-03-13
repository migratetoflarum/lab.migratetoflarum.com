<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScansAddUrlColumn extends Migration
{
    public function up()
    {

        Schema::table('scans', function (Blueprint $table) {
            $table->string('url')->nullable();
        });
    }

    public function down()
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
}
