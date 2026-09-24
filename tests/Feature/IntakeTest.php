<?php

namespace Tests\Feature;

use App\Models\Cluster;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class IntakeTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/intake')->assertRedirect(route('login'));
    }

    public function test_authenticated_participant_can_complete_intake(): void
    {
        $user = User::factory()->create();
        ParticipantProfile::factory()->create(['user_id' => $user->id]);
        $cluster = Cluster::factory()->create();

        $response = $this->actingAs($user)->put('/intake', [
            'pathway' => 'professional-growth',
            'primary_cluster_id' => $cluster->id,
            'goals' => 'Build a practical leadership development plan for the next six months.',
            'experience_summary' => 'Five years in technology operations and risk management.',
            'preferred_language' => 'English',
            'preferred_format' => 'virtual',
            'availability' => ['weekday-evening'],
            'mentoring_style' => 'Structured and action focused.',
            'accessibility_needs' => 'Captions for online meetings.',
            'conflict_declarations' => 'Avoid direct reporting relationships.',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('participant_profiles', ['user_id' => $user->id, 'intake_status' => 'complete']);
    }
}
