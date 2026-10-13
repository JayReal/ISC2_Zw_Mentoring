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

    public function test_account_without_participant_profile_returns_not_found_instead_of_server_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertNotFound();
        $this->actingAs($user)->get('/intake')->assertNotFound();
        $cluster = Cluster::factory()->create();

        $this->actingAs($user)->put('/intake', [
            'pathway' => 'professional-growth',
            'primary_cluster_id' => $cluster->id,
            'goals' => 'Build a practical leadership development plan for the next six months.',
            'experience_summary' => 'Five years in technology operations and risk management.',
            'preferred_language' => 'English',
            'preferred_format' => 'virtual',
            'availability' => ['weekday-evening'],
        ])->assertNotFound();
    }

    public function test_inactive_cluster_cannot_be_selected(): void
    {
        $user = User::factory()->create();
        ParticipantProfile::factory()->create(['user_id' => $user->id]);
        $cluster = Cluster::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)->put('/intake', [
            'pathway' => 'professional-growth',
            'primary_cluster_id' => $cluster->id,
            'goals' => 'Build a practical leadership development plan for the next six months.',
            'experience_summary' => 'Five years in technology operations and risk management.',
            'preferred_language' => 'English',
            'preferred_format' => 'virtual',
            'availability' => ['weekday-evening'],
        ]);

        $response->assertSessionHasErrors('primary_cluster_id');
    }

    public function test_participant_can_save_structured_preferences_and_secondary_communities(): void
    {
        $user = User::factory()->create();
        $profile = ParticipantProfile::factory()->create(['user_id' => $user->id]);
        $primaryCluster = Cluster::factory()->create(['display_order' => 1]);
        $secondaryCluster = Cluster::factory()->create(['display_order' => 2]);

        $this->actingAs($user)->put(route('intake.update'), [
            'pathway' => 'professional-growth',
            'primary_cluster_id' => $primaryCluster->id,
            'secondary_cluster_ids' => [$secondaryCluster->id],
            'goals' => 'Build a practical leadership development plan for the next six months.',
            'experience_summary' => 'Five years in technology operations and risk management.',
            'preferred_language' => 'English',
            'preferred_format' => 'virtual',
            'availability' => ['weekday-evening'],
            'mentoring_styles' => ['structured', 'practical'],
            'university_context_status' => 'not-applicable',
            'conflict_status' => 'none',
        ])->assertRedirect(route('dashboard'));

        $profile->refresh();

        $this->assertSame([$secondaryCluster->id], $profile->secondary_cluster_ids);
        $this->assertSame(['structured', 'practical'], $profile->mentoring_styles);
        $this->assertNull($profile->university_context);
        $this->assertNull($profile->conflict_declarations);
    }

    public function test_primary_cluster_cannot_be_repeated_as_a_secondary_community(): void
    {
        $user = User::factory()->create();
        ParticipantProfile::factory()->create(['user_id' => $user->id]);
        $cluster = Cluster::factory()->create();

        $this->actingAs($user)->from(route('intake.edit'))->put(route('intake.update'), [
            'pathway' => 'professional-growth',
            'primary_cluster_id' => $cluster->id,
            'secondary_cluster_ids' => [$cluster->id],
            'goals' => 'Build a practical leadership development plan for the next six months.',
            'experience_summary' => 'Five years in technology operations and risk management.',
            'preferred_language' => 'English',
            'preferred_format' => 'virtual',
            'availability' => ['weekday-evening'],
            'university_context_status' => 'not-applicable',
            'conflict_status' => 'none',
        ])->assertRedirect(route('intake.edit'))->assertSessionHasErrors('secondary_cluster_ids');
    }

    public function test_no_preference_cannot_be_combined_with_another_working_style(): void
    {
        $user = User::factory()->create();
        ParticipantProfile::factory()->create(['user_id' => $user->id]);
        $cluster = Cluster::factory()->create();

        $this->actingAs($user)->from(route('intake.edit'))->put(route('intake.update'), [
            'pathway' => 'professional-growth',
            'primary_cluster_id' => $cluster->id,
            'goals' => 'Build a practical leadership development plan for the next six months.',
            'experience_summary' => 'Five years in technology operations and risk management.',
            'preferred_language' => 'English',
            'preferred_format' => 'virtual',
            'availability' => ['weekday-evening'],
            'mentoring_styles' => ['no-preference', 'structured'],
            'university_context_status' => 'not-applicable',
            'conflict_status' => 'none',
        ])->assertRedirect(route('intake.edit'))->assertSessionHasErrors('mentoring_styles');
    }

    public function test_intake_prompt_reflects_the_participant_role(): void
    {
        $mentor = User::factory()->create();
        ParticipantProfile::factory()->create(['user_id' => $mentor->id, 'participation_type' => 'mentor']);

        $this->actingAs($mentor)->get(route('intake.edit'))
            ->assertOk()
            ->assertSee('Tell us how you can contribute')
            ->assertSee('What experience, perspective or practical knowledge can you share?');
    }
}
