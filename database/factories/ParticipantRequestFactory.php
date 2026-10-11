<?php

namespace Database\Factories;

use App\Models\ParticipantRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ParticipantRequest> */
class ParticipantRequestFactory extends Factory
{
    protected $model = ParticipantRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'withdrawal',
            'details' => fake()->sentence(),
            'status' => 'open',
        ];
    }
}
