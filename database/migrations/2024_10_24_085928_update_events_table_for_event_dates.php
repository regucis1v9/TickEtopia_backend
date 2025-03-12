<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEventsTableForEventDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {

            // Remove 'start_time' and 'end_time' columns
            $table->dropColumn(['start_time', 'end_time']);

            // Add foreign key reference to 'event_dates' table
            $table->foreignId('event_date_id')->constrained('event_dates')->onDelete('cascade')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {

            // Re-add 'start_time' and 'end_time' columns
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Drop the foreign key constraint for 'event_date_id'
            $table->dropForeign(['event_date_id']);
            $table->dropColumn('event_date_id');
        });
    }
}
