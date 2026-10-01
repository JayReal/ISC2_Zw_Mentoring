<?php

namespace Tests\Feature;

use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MentorReadinessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_mentor_can_update_readiness_and_capacity(): void
    {
        $mentor = User::factory()->create(['roles' => ['mentor']]);
        $profile = ParticipantProfile::factory()->create(['user_id' => $mentor->id, 'participation_type' => 'mentor']);

        $this->actingAs($mentor)->put(route('mentor-readiness.update'), [
            'mentor_expertise' => 'I can mentor governance, risk, audit and cybersecurity leadership development.',
            'mentor_prerequisites' => 'A clear learning objective is helpful.',
            'mentor_capacity' => 2,
            'mentor_availability_status' => 'available',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('participant_profiles', ['id' => $profile->id, 'mentor_capacity' => 2, 'mentor_availability_status' => 'available']);
    }

    public function test_mentee_cannot_access_mentor_readiness(): void
    {
        $mentee = User::factory()->create(['roles' => ['mentee']]);
        ParticipantProfile::factory()->create(['user_id' => $mentee->id, 'participation_type' => 'mentee']);

        $this->actingAs($mentee)->get(route('mentor-readiness.edit'))->assertForbidden();
    }
}
