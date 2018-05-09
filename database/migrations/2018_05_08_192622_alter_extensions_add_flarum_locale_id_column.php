<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExtensionsAddFlarumLocaleIdColumn extends Migration
{
    public function up()
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->unsignedInteger('flarum_locale_id')->nullable();
            $table->unsignedInteger('last_version_id')->nullable();
            $table->timestamp('packagist_time')->nullable()->index();
            $table->timestamp('last_version_time')->nullable()->index();

            $table->foreign('flarum_locale_id')->references('id')->on('locales')->onDelete('set null');
            $table->foreign('last_version_id')->references('id')->on('extension_versions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->dropForeign('extensions_flarum_locale_id_foreign');
            $table->dropForeign('extensions_last_version_id_foreign');

            $table->dropColumn('flarum_locale_id');
            $table->dropColumn('last_version_id');
            $table->dropColumn('packagist_time');
            $table->dropColumn('last_version_time');
        });
    }
}
