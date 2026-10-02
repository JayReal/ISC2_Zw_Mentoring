<?php

namespace Database\Factories;

use App\Models\MentoringMatch;
use App\Models\MentoringMeeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MentoringMeeting>
 */
class MentoringMeetingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mentoring_match_id' => MentoringMatch::factory(),
            'recorded_by' => User::factory(),
            'meeting_on' => fake()->dateTimeBetween('-3 months', 'now'),
            'duration_minutes' => 45,
            'topics_discussed' => fake()->paragraph(),
            'next_actions' => fake()->sentence(),
        ];
    }
}
