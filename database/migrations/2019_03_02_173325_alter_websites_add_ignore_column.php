<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWebsitesAddIgnoreColumn extends Migration
{
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->boolean('ignore')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('ignore');
        });
    }
}
