<?php

namespace Tests\Feature\Api;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_venues()
    {
        Venue::factory()->count(5)->create();

        $response = $this->get('/api/venues'); // Adjust the endpoint as necessary

        $response->assertStatus(200);
        $this->assertCount(5, $response->json());
    }

    public function test_add_venue()
    {
        $response = $this->post('/api/venues', [
            'name' => 'New Venue',
            'address' => '123 New Venue St',
            'contact_email' => 'contact@newvenue.com',
            'contact_phone' => '+1234567890',
            'capacity' => 100,
            'notes' => 'This is a new venue.',
            'image' => null,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('venues', [
            'name' => 'New Venue',
        ]);
    }
}
