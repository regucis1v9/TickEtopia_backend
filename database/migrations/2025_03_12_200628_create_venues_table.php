<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            $table->string('address')->nullable(); 
            $table->string('contact_email')->nullable(); 
            $table->string('contact_phone')->nullable(); 
            $table->integer('capacity')->nullable();  
            $table->text('notes')->nullable(); 
            $table->timestamps();  
            $table->string('image')->nullable(); 
            
            $table->primary('id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('venues');
    }
};
