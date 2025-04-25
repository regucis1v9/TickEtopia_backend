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
            // Check if foreign key exists before trying to drop it
            $foreignKeys = $this->getForeignKeys('events');
            if (in_array('events_organizer_id_foreign', $foreignKeys)) {
                $table->dropForeign('events_organizer_id_foreign');
            }
            
            $table->foreign('organizer_id')
                ->references('id')->on('organizers')
                ->onDelete('cascade');
        });

        // EVENT_DATES → EVENTS
        Schema::table('event_dates', function (Blueprint $table) {
            $foreignKeys = $this->getForeignKeys('event_dates');
            if (in_array('event_dates_event_id_foreign', $foreignKeys)) {
                $table->dropForeign('event_dates_event_id_foreign');
            }
            
            $table->foreign('event_id')
                ->references('id')->on('events')
                ->onDelete('cascade');
        });

        // TICKET_PRICES → EVENTS + EVENT_DATES
        Schema::table('ticket_prices', function (Blueprint $table) {
            $foreignKeys = $this->getForeignKeys('ticket_prices');
            
            if (in_array('ticket_prices_event_id_foreign', $foreignKeys)) {
                $table->dropForeign('ticket_prices_event_id_foreign');
            }
            
            if (in_array('ticket_prices_event_date_id_foreign', $foreignKeys)) {
                $table->dropForeign('ticket_prices_event_date_id_foreign');
            }
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
                $foreignKeys = $this->getForeignKeys('tickets');
                if (in_array('tickets_event_id_foreign', $foreignKeys)) {
                    $table->dropForeign('tickets_event_id_foreign');
                }

                $table->foreign('event_id')
                    ->references('id')->on('events')
                    ->onDelete('cascade');
            }
        });
    }

    private function getForeignKeys($tableName)
    {
        $database = config('database.connections.mysql.database');
        $foreignKeys = [];
        
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND TABLE_SCHEMA = '$database'
            AND TABLE_NAME = '$tableName'
        ");
        
        foreach ($constraints as $constraint) {
            $foreignKeys[] = $constraint->CONSTRAINT_NAME;
        }
        
        return $foreignKeys;
    }

    public function down()
    {
        // Add reverse logic here if needed
    }
};