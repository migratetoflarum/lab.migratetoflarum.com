<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptOutChecksTable extends Migration
{
    public function up()
    {
        Schema::create('opt_out_checks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid')->unique();
            $table->string('source');
            $table->string('domain')->index();
            $table->string('url');
            $table->string('normalized_url');
            $table->string('canonical_url');
            $table->boolean('ignore')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('opt_out_checks');
    }
}
