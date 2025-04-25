<?php

namespace Tests\Feature;

use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class OrganizerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_organizer()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'organizer_name' => 'Test Organizer',
            'organizer_registration_number' => '123456789',
            'organizer_email' => 'test@organizer.com',
            'organizer_phone' => '+123456789',
            'organizer_address' => '123 Test St',
        ];

        $response = $this->postJson('/api/organizers', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment(['organizer_name' => 'Test Organizer']);
    }

    public function test_get_all_organizers()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $organizer = Organizer::factory()->create();

        $response = $this->getJson('/api/organizers');

        $response->assertStatus(200);
        $response->assertJsonFragment(['organizer_name' => $organizer->organizer_name]);
    }

    public function test_edit_organizer()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $organizer = Organizer::factory()->create();
        $newData = ['organizer_name' => 'Updated Organizer'];

        $response = $this->postJson("/api/organizers/{$organizer->id}", $newData);

        $response->assertStatus(200);
        $response->assertJsonFragment(['organizer_name' => 'Updated Organizer']);
    }

    public function test_delete_organizer()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $organizer = Organizer::factory()->create();

        $response = $this->deleteJson("/api/organizers/{$organizer->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('organizers', [
            'id' => $organizer->id,
        ]);
    }
}
