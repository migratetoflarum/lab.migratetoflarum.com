<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRequestsAddCertificateColumn extends Migration
{
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->json('certificate')->nullable();
            $table->string('ip')->nullable();
        });
    }

    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('certificate');
            $table->dropColumn('ip');
        });
    }
}
