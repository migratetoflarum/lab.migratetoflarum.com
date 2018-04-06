<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScansAddRatingColumn extends Migration
{
    public function up()
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->string('rating')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
}
