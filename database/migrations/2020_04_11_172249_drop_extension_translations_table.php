<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropExtensionTranslationsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('extension_translations');

        Schema::table('extension_versions', function (Blueprint $table) {
            $table->dropColumn('locale_errors');
            $table->dropColumn('scanned_locales_at');
        });
    }

    public function down()
    {
        // Not implemented on purpose
    }
}
