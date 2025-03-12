<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->string('organizer_name');
            $table->string('organizer_registration_number')->nullable();
            $table->string('organizer_email')->nullable();
            $table->string('organizer_phone')->nullable();
            $table->string('organizer_address')->nullable();
            $table->timestamps(0);
            $table->string('image')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('organizers');
    }
};
