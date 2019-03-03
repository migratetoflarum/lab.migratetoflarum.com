<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWebsitesAddPingColumns extends Migration
{
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->timestamp('pinged_at')->nullable();
            $table->timestamp('confirmed_flarum_at')->nullable();
            $table->boolean('is_flarum')->nullable();
        });
    }

    public function down()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('pinged_at');
            $table->dropColumn('confirmed_flarum_at');
            $table->dropColumn('is_flarum');
        });
    }
}
