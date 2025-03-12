<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('organizers')->insert([
            [
                'organizer_name' => 'Global Events Ltd.',
                'organizer_registration_number' => 'EVT123456',
                'organizer_email' => 'contact@globalevents.com',
                'organizer_phone' => '+1 800 555 0199',
                'organizer_address' => '100 Main Street, New York, NY 10001',
                'image' => 'http://example.com/organizers/global_events.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_name' => 'Starlight Productions',
                'organizer_registration_number' => 'STL789012',
                'organizer_email' => 'info@starlightpro.com',
                'organizer_phone' => '+1 800 555 0246',
                'organizer_address' => '567 Broadway Ave, Los Angeles, CA 90001',
                'image' => 'http://example.com/organizers/starlight_productions.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_name' => 'Comedy Central Hosts',
                'organizer_registration_number' => 'CCH543210',
                'organizer_email' => 'bookings@comedycentral.com',
                'organizer_phone' => '+1 800 555 0321',
                'organizer_address' => '789 Laugh Ln, Chicago, IL 60601',
                'image' => 'http://example.com/organizers/comedy_central_hosts.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('organizers');
    }
};
