<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('venues')->insert([
            [
                'name' => 'Grand Concert Hall',
                'address' => '123 Music Ave, Suite 100, New York, NY 10001',
                'contact_email' => 'info@grandconcert.com',
                'contact_phone' => '+1 234 567 890',
                'capacity' => 2000,
                'notes' => 'The premier venue for classical music concerts.',
                'image' => 'http://example.com/venues/grand_concert_hall.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'The Arena',
                'address' => '456 Sports Blvd, Los Angeles, CA 90001',
                'contact_email' => 'contact@thearena.com',
                'contact_phone' => '+1 987 654 321',
                'capacity' => 50000,
                'notes' => 'Host to major sports events and concerts.',
                'image' => 'http://example.com/venues/the_arena.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Comedy Club',
                'address' => '789 Laugh Ln, Chicago, IL 60601',
                'contact_email' => 'booking@comedyclub.com',
                'contact_phone' => '+1 555 123 456',
                'capacity' => 300,
                'notes' => 'Intimate venue for stand-up comedy performances.',
                'image' => 'http://example.com/venues/comedy_club.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('venues');
    }
};
