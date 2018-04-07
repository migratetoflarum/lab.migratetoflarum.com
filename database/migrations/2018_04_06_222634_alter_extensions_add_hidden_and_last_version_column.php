<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExtensionsAddHiddenAndLastVersionColumn extends Migration
{
    public function up()
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->boolean('hidden')->default(false)->index();
            $table->string('last_version')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->dropColumn('hidden');
            $table->dropColumn('last_version');
        });
    }
}
