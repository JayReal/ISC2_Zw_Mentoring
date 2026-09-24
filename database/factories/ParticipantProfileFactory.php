<?php

namespace Database\Factories;

use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParticipantProfile>
 */
class ParticipantProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date_of_birth' => fake()->dateTimeBetween('-55 years', '-18 years'),
            'participation_type' => 'mentee',
            'preferred_language' => 'English',
            'preferred_format' => 'virtual',
            'intake_status' => 'not_started',
        ];
    }
}
