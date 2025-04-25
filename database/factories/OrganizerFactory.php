<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organizer>
 */
class OrganizerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organizer_name' => $this->faker->company,
            'organizer_registration_number' => $this->faker->numerify('ORG-####'),
            'organizer_email' => $this->faker->unique()->safeEmail(),
            'organizer_phone' => $this->faker->phoneNumber(),
            'organizer_address' => $this->faker->address(),
            'image' => $this->faker->imageUrl(),
        ];
    }
}
