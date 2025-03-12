<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventDatesTable extends Migration
{
    public function up()
    {
        Schema::create('event_dates', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade'); // Foreign Key to events
            $table->date('event_date'); // Date of the event
            $table->time('event_time')->nullable(); // Time when the event starts
            $table->time('doors_open_time')->nullable(); // Time when doors open
            $table->timestamps(); // created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_dates');
    }
}
