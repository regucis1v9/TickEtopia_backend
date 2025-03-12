<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTicketPriceForeignKeyToEvents extends Migration // Renamed class to avoid conflict
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {

            // Add foreign key reference to 'ticket_prices' table
            $table->foreignId('ticket_price_id')->constrained('ticket_prices')->onDelete('cascade');
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

            // Drop the foreign key constraint for 'ticket_price_id'
            $table->dropForeign(['ticket_price_id']);
            $table->dropColumn('ticket_price_id');
        });
    }
}
