<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user()
    {
        $admin = User::factory()->create(); // Authenticated user
        $this->actingAs($admin, 'sanctum');

        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment(['email' => 'testuser@example.com']);
    }

    public function test_update_user()
    {
        $authUser = User::factory()->create(); // logged in as this user
        $this->actingAs($authUser, 'sanctum');
    
        $user = User::factory()->create();
    
        $newData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'user', // ✅ Required to pass validation
        ];
    
        $response = $this->putJson("/api/users/{$user->id}", $newData);
    
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_delete_user()
    {
        $password = 'secret123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);
    
        $this->actingAs($user, 'sanctum');
    
        $response = $this->deleteJson("/api/users/{$user->id}", [
            'password' => $password, // Required!
        ]);
    
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_index_users()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $user = User::factory()->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $user->email]);
    }
}
