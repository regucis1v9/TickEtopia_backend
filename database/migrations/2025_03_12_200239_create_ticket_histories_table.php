<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('status_id')->constrained('ticket_statuses');
            $table->text('description')->nullable();
            $table->timestamps(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('ticket_histories');
    }
};
