<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketPricesTable extends Migration
{
    public function up()
    {
        Schema::create('ticket_prices', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade'); // Foreign Key to events
            $table->string('ticket_type'); // Type of ticket (e.g., Regular, VIP)
            $table->decimal('price', 10, 2); // Price of the ticket
            $table->boolean('early_bird')->default(false); // Early bird flag
            $table->timestamps(); // created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_prices');
    }
}
