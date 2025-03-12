<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TickEtopiaTest extends TestCase
{

    /**
     * Test user registration
     */
    public function testUserRegistration()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'TestUser',
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'token']);

        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }

    /**
     * Test user login
     */
    public function testUserLogin()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'token', 'user' => ['id', 'name', 'email']]);
    }

    /**
     * Test adding a venue
     */
    public function testAddVenue()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'name' => 'Test Venue',
            'address' => '123 Venue St',
            'contact_email' => 'venue@example.com',
            'contact_phone' => '+123456789',
            'capacity' => 500,
        ];

        $response = $this->postJson('/api/venues', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure(['venue' => ['id', 'name', 'address', 'contact_email']]);

        $this->assertDatabaseHas('venues', ['name' => 'Test Venue']);
    }

    /**
     * Test updating a venue
     */
    public function testEditVenue()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $venue = Venue::factory()->create(['name' => 'Original Venue Name']);

        $response = $this->putJson("/api/venues/{$venue->id}", [
            'name' => 'Updated Venue Name'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Venue Name']);
    }

    /**
     * Test deleting a venue
     */
    public function testDeleteVenue()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $venue = Venue::factory()->create();

        $response = $this->deleteJson("/api/venues/{$venue->id}");
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Venue deleted successfully!']);

        $this->assertDatabaseMissing('venues', ['id' => $venue->id]);
    }
}
