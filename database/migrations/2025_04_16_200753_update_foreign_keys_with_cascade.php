<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // EVENTS → ORGANIZERS
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->foreign('organizer_id')
                ->references('id')->on('organizers')
                ->onDelete('cascade');
        });

        // EVENT_DATES → EVENTS
        Schema::table('event_dates', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->foreign('event_id')
                ->references('id')->on('events')
                ->onDelete('cascade');
        });

        // TICKET_PRICES → EVENTS + EVENT_DATES
        Schema::table('ticket_prices', function (Blueprint $table) {
            // Safely drop foreign keys using raw SQL to avoid errors
            DB::statement('ALTER TABLE ticket_prices DROP FOREIGN KEY IF EXISTS ticket_prices_event_id_foreign');
            DB::statement('ALTER TABLE ticket_prices DROP FOREIGN KEY IF EXISTS ticket_prices_event_date_id_foreign');
        });

        Schema::table('ticket_prices', function (Blueprint $table) {
            $table->foreign('event_id')
                ->references('id')->on('events')
                ->onDelete('cascade');

            $table->foreign('event_date_id')
                ->references('id')->on('event_dates')
                ->onDelete('cascade');
        });

        // TICKETS → EVENTS
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'event_id')) {
                $table->foreignId('event_id')
                    ->constrained('events')
                    ->onDelete('cascade');
            } else {
                DB::statement('ALTER TABLE tickets DROP FOREIGN KEY IF EXISTS tickets_event_id_foreign');

                $table->foreign('event_id')
                    ->references('id')->on('events')
                    ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        // Add reverse logic here if needed
    }
};
