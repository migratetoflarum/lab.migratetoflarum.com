<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExtensionVersionsAddVersionNormalizedColumn extends Migration
{
    public function up()
    {
        Schema::table('extension_versions', function (Blueprint $table) {
            $table->string('version_normalized')->nullable()->index();
            $table->timestamp('packagist_time')->nullable()->index();
            $table->timestamp('scanned_modules_at')->nullable()->index();
            $table->timestamp('scanned_locales_at')->nullable()->index();
            $table->json('locale_errors')->nullable();
        });
    }

    public function down()
    {
        Schema::table('extension_versions', function (Blueprint $table) {
            $table->dropColumn('version_normalized');
            $table->dropColumn('packagist_time');
            $table->dropColumn('scanned_modules_at');
            $table->dropColumn('scanned_locales_at');
            $table->dropColumn('locale_errors');
        });
    }
}
