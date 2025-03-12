<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixEventsAndVenuesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify the venues table
        Schema::table('venues', function (Blueprint $table) {
            // Add the 'image' column if it doesn't exist
            if (!Schema::hasColumn('venues', 'image')) {
                $table->string('image')->nullable();
            }
        });

        // Modify the events table
        Schema::table('events', function (Blueprint $table) {
            // Add the 'image' column if it doesn't exist
            if (!Schema::hasColumn('events', 'image')) {
                $table->string('image')->nullable();
            }

            // Ensure foreign keys are set up properly
            if (!Schema::hasColumn('events', 'venue_id')) {
                $table->foreignId('venue_id')->constrained('venues')->onDelete('cascade');
            }

            if (!Schema::hasColumn('events', 'event_date_id')) {
                $table->foreignId('event_date_id')->constrained('event_dates')->onDelete('cascade');
            }

            if (!Schema::hasColumn('events', 'organizer_id')) {
                $table->foreignId('organizer_id')->constrained('organizers')->onDelete('cascade');
            }

            if (!Schema::hasColumn('events', 'ticket_price_id')) {
                $table->foreignId('ticket_price_id')->constrained('ticket_prices')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optionally, you can reverse the changes made in this migration
        Schema::table('venues', function (Blueprint $table) {
            // Drop the image column if it exists
            if (Schema::hasColumn('venues', 'image')) {
                $table->dropColumn('image');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            // Drop the image column if it exists
            if (Schema::hasColumn('events', 'image')) {
                $table->dropColumn('image');
            }

            // Drop foreign keys if you wish to remove them
            $table->dropForeign(['venue_id']);
            $table->dropForeign(['event_date_id']);
            $table->dropForeign(['organizer_id']);
            $table->dropForeign(['ticket_price_id']);
        });
    }
}
