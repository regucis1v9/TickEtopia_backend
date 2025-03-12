<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id(); // Primary key, auto-increment
            $table->string('title'); // Event title
            $table->text('description')->nullable(); // Event description, nullable
            $table->string('location'); // Location of the event
            $table->boolean('is_public')->default(true); // Public/private event flag
            $table->foreignId('venue_id')->constrained('venues')->onDelete('cascade'); // Foreign key referencing the venues table
            $table->foreignId('event_date_id')->constrained('event_dates')->onDelete('cascade'); // Foreign key referencing the event_dates table
            $table->string('age_limit')->nullable(); // For age restrictions
            $table->foreignId('organizer_id')->constrained('organizers')->onDelete('cascade'); // Foreign key referencing the organizers table
            $table->string('image')->nullable(); // Column for event image, nullable
            $table->timestamps(); // Created_at and updated_at columns
            $table->softDeletes(); // Soft delete, adds a deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
