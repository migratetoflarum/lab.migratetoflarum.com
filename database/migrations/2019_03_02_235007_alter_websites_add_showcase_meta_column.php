<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWebsitesAddShowcaseMetaColumn extends Migration
{
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->json('showcase_meta')->nullable();
        });
    }

    public function down()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('showcase_meta');
        });
    }
}
