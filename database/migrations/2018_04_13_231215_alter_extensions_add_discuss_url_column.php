<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExtensionsAddDiscussUrlColumn extends Migration
{
    public function up()
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->string('discuss_url')->nullable();
        });
    }

    public function down()
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->dropColumn('discuss_url');
        });
    }
}
