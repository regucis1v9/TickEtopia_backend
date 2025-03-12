<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events');
            $table->string('ticket_type');
            $table->decimal('price', 10, 2);
            $table->timestamps(0);
            $table->foreignId('event_date_id')->nullable()->constrained('event_dates');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('ticket_prices');
    }
};
