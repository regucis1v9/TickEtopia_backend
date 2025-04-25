<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_event()
    {
        $user = User::factory()->create(); // Add this
        $this->actingAs($user, 'sanctum'); // Add this

        $organizer = Organizer::factory()->create(); // Assuming Organizer has a factory

        $data = [
            'title' => 'Test Event',
            'description' => 'This is a test event.',
            'is_public' => true,
            'organizer_id' => $organizer->id,
        ];

        $response = $this->postJson('/api/events', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment(['title' => 'Test Event']);
    }

    public function test_get_events()
    {
        $event = Event::factory()->create(); // Assuming Event has a factory

        $response = $this->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => $event->title]);
    }

    public function test_delete_event()
    {
        $user = User::factory()->create(); // Add this
        $this->actingAs($user, 'sanctum'); // Add this

        $event = Event::factory()->create();

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }

    public function test_update_event()
    {
        $user = User::factory()->create(); // Add this
        $this->actingAs($user, 'sanctum'); // Add this

        $event = Event::factory()->create();
        $newData = ['title' => 'Updated Event Title'];

        $response = $this->postJson("/api/events/{$event->id}", $newData);

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Updated Event Title']);
    }
}
