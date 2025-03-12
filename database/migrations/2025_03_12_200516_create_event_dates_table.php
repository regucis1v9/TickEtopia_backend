<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events');
            $table->dateTime('start_date_time');
            $table->dateTime('end_date_time');
            $table->timestamps(0);
            $table->foreignId('venue_id')->nullable()->constrained('venues');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('event_dates');
    }
};
