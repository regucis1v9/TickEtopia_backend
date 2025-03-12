<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToOrganizersTable extends Migration
{
    public function up()
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->string('image')->nullable(); // Add the image column
        });
    }

    public function down()
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->dropColumn('image'); // Remove the image column
        });
    }
}
