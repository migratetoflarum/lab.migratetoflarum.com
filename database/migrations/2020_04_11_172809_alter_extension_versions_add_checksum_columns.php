<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExtensionVersionsAddChecksumColumns extends Migration
{
    public function up()
    {
        Schema::table('extension_versions', function (Blueprint $table) {
            $table->string('javascript_forum_checksum')->nullable();
            $table->string('javascript_admin_checksum')->nullable();
        });
    }

    public function down()
    {
        Schema::table('extension_versions', function (Blueprint $table) {
            $table->dropColumn('javascript_forum_checksum');
            $table->dropColumn('javascript_admin_checksum');
        });
    }
}
