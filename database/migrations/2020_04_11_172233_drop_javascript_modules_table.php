<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class DropJavascriptModulesTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('extension_version_module');
        Schema::dropIfExists('javascript_modules');
    }

    public function down()
    {
        // Not implemented on purpose
    }
}
