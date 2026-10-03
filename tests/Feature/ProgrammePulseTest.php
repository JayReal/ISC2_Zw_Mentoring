<?php

namespace Tests\Feature;

use App\Models\MentoringCheckIn;
use App\Models\MentoringMatch;
use App\Models\MentoringSupportRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProgrammePulseTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_programme_lead_sees_relationships_missing_core_foundations(): void
    {
        $lead = User::factory()->create(['roles' => ['programme-lead']]);
        $mentor = User::factory()->create(['name' => 'Pulse Mentor']);
        $mentee = User::factory()->create(['name' => 'Pulse Mentee']);
        $match = MentoringMatch::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'status' => 'active',
            'tier' => 'matched',
            'rationale' => 'A test relationship requiring programme attention.',
            'started_at' => now()->subDays(40),
        ]);

        $response = $this->actingAs($lead)->get(route('admin.pulse'));

        $response->assertOk()->assertSee('Programme pulse')->assertSee('Pulse Mentor and Pulse Mentee');
        foreach (['charterIncomplete', 'withoutGoals', 'withoutMeetings', 'checkInsOutstanding', 'inactiveMatches'] as $queue) {
            $this->assertTrue($response->viewData('queues')[$queue]->contains('id', $match->id), "Expected match in {$queue} queue.");
        }
    }

    public function test_two_current_month_check_ins_clear_the_monthly_pulse_flag(): void
    {
        $lead = User::factory()->create(['roles' => ['programme-lead']]);
        $mentor = User::factory()->create();
        $mentee = User::factory()->create();
        $match = MentoringMatch::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'status' => 'active',
            'tier' => 'matched',
            'rationale' => 'A relationship with both monthly check-ins complete.',
            'started_at' => now(),
        ]);
        foreach ([$mentor, $mentee] as $participant) {
            MentoringCheckIn::create([
                'mentoring_match_id' => $match->id,
                'user_id' => $participant->id,
                'meeting_held' => true,
                'usefulness_score' => 4,
                'needs_support' => false,
                'period_month' => now()->startOfMonth(),
            ]);
        }

        $response = $this->actingAs($lead)->get(route('admin.pulse'));

        $response->assertOk();
        $this->assertFalse($response->viewData('queues')['checkInsOutstanding']->contains('id', $match->id));
    }

    public function test_matching_staff_can_view_pulse_without_confidential_support_requests(): void
    {
        $staff = User::factory()->create(['roles' => ['matching-team']]);
        $mentor = User::factory()->create();
        $mentee = User::factory()->create();
        $match = MentoringMatch::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'status' => 'active',
            'tier' => 'matched',
            'rationale' => 'A test relationship with a confidential request.',
        ]);
        MentoringSupportRequest::create([
            'mentoring_match_id' => $match->id,
            'requested_by' => $mentee->id,
            'type' => 'support',
            'reason' => 'Sensitive support details must remain restricted.',
        ]);

        $response = $this->actingAs($staff)->get(route('admin.pulse'));

        $response->assertOk()->assertDontSee('Support and rematch requests')->assertDontSee('Sensitive support details');
        $this->assertFalse($response->viewData('canSeeConfidentialRequests'));
        $this->assertTrue($response->viewData('queues')['supportRequests']->isEmpty());
    }

    public function test_reporting_staff_cannot_access_the_operational_pulse(): void
    {
        $reporter = User::factory()->create(['roles' => ['reporting-lead']]);

        $this->actingAs($reporter)->get(route('admin.pulse'))->assertForbidden();
    }
}
