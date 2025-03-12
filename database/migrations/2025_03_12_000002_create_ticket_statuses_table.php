<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->timestamps(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('ticket_statuses');
    }
};
