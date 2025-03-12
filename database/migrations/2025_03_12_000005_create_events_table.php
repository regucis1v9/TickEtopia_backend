<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(1);
            $table->timestamps(0);
            $table->timestamp('deleted_at')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('organizer_id')->constrained('organizers');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('events');
    }
};
