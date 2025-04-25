<?php

namespace Tests\Feature;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class VenueControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_venue()
    {
        $user = User::factory()->create(); // Add this
        $this->actingAs($user, 'sanctum'); // Add this

        $data = [
            'name' => 'Test Venue',
            'address' => '123 Venue St',
            'contact_email' => 'contact@venue.com',
            'contact_phone' => '+123456789',
            'capacity' => 500,
        ];

        $response = $this->postJson('/api/venues', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'Test Venue']);
    }

    public function test_get_all_venues()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $venue = Venue::factory()->create();

        $response = $this->getJson('/api/venues');

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $venue->name]);
    }

    public function test_edit_venue()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $venue = Venue::factory()->create();
        $newData = ['name' => 'Updated Venue Name'];

        $response = $this->postJson("/api/venues/{$venue->id}", $newData);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Updated Venue Name']);
    }

    public function test_delete_venue()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $venue = Venue::factory()->create();

        $response = $this->deleteJson("/api/venues/{$venue->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('venues', [
            'id' => $venue->id,
        ]);
    }
}
