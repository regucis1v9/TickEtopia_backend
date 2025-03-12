<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('events')->insert([
            [
                'title' => 'Summer Music Festival',
                'description' => 'An exciting summer festival featuring top artists from around the world.',
                'is_public' => true,
                'organizer_id' => 1,
                'venue_id' => 1,
                'image' => 'http://example.com/events/summer_festival.jpg',
                'location' => 'New York City, USA', // Add this line
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Tech Conference 2025',
                'description' => 'The largest tech conference bringing together industry leaders and startups.',
                'is_public' => true,
                'organizer_id' => 2,
                'venue_id' => 2,
                'image' => 'http://example.com/events/tech_conference.jpg',
                'location' => 'San Francisco, USA', // Add this line
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Comedy Night Live',
                'description' => 'A hilarious evening with top stand-up comedians.',
                'is_public' => true,
                'organizer_id' => 3,
                'venue_id' => 3,
                'image' => 'http://example.com/events/comedy_night.jpg',
                'location' => 'Chicago, USA', // Add this line
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
};

