<?php

namespace Database\Factories;

use App\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'is_public' => $this->faker->boolean,
            'deleted_at' => null, // You can change this to a value if you want deleted events
            'image' => $this->faker->imageUrl(),
            'organizer_id' => Organizer::factory(),  // This generates a related organizer automatically
        ];
    }
}
