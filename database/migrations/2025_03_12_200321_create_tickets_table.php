<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('status_id')->constrained('ticket_statuses');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
