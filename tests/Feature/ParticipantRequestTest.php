<?php

namespace Tests\Feature;

use App\Models\ParticipantRequest;
use App\Models\User;
use App\Notifications\ParticipantRequestNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ParticipantRequestTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_participant_can_submit_encrypted_privacy_request_for_staff_review(): void
    {
        Notification::fake();
        $administrator = User::factory()->create(['roles' => ['admin']]);
        $participant = User::factory()->create(['roles' => ['mentee']]);

        $response = $this->actingAs($participant)->post(route('account.requests.store'), [
            'type' => 'data-copy',
            'details' => 'Please provide a copy of the personal information held about me.',
        ]);

        $response->assertRedirect()->assertSessionHas('status');
        $participantRequest = ParticipantRequest::firstOrFail();
        $this->assertSame('data-copy', $participantRequest->type);
        $this->assertNotSame($participantRequest->details, $participantRequest->getRawOriginal('details'));
        Notification::assertSentTo($administrator, ParticipantRequestNotification::class);
    }

    public function test_duplicate_open_request_of_same_type_is_rejected(): void
    {
        $participant = User::factory()->create();
        ParticipantRequest::factory()->create(['user_id' => $participant->id, 'type' => 'withdrawal']);

        $response = $this->actingAs($participant)->post(route('account.requests.store'), [
            'type' => 'withdrawal',
            'details' => 'I would like staff to pause my participation while circumstances change.',
        ]);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('participant_requests', 1);
    }

    public function test_programme_lead_can_resolve_participant_request(): void
    {
        $programmeLead = User::factory()->create(['roles' => ['programme-lead']]);
        $participantRequest = ParticipantRequest::factory()->create();

        $response = $this->actingAs($programmeLead)->put(route('admin.participant-requests.update', $participantRequest), [
            'status' => 'resolved',
            'resolution' => 'The requested data copy was securely provided to the participant.',
        ]);

        $response->assertRedirect()->assertSessionHas('status');
        $participantRequest->refresh();
        $this->assertSame('resolved', $participantRequest->status);
        $this->assertSame($programmeLead->id, $participantRequest->handled_by);
        $this->assertNotNull($participantRequest->resolved_at);
    }

    public function test_matching_staff_cannot_resolve_participant_privacy_request(): void
    {
        $matchingStaff = User::factory()->create(['roles' => ['matching-team']]);
        $participantRequest = ParticipantRequest::factory()->create();

        $this->actingAs($matchingStaff)->put(route('admin.participant-requests.update', $participantRequest), [
            'status' => 'resolved',
            'resolution' => 'This must not be accepted.',
        ])->assertForbidden();

        $this->assertSame('open', $participantRequest->fresh()->status);
    }
}
