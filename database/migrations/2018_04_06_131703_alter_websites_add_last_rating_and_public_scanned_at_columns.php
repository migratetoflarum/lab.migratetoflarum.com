<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWebsitesAddLastRatingAndPublicScannedAtColumns extends Migration
{
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('last_rating')->nullable()->index();
            $table->timestamp('last_public_scanned_at')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('last_rating');
            $table->dropColumn('last_public_scanned_at');
        });
    }
}
