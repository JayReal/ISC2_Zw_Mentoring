<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_programme_lead_can_filter_read_only_audit_history(): void
    {
        $programmeLead = User::factory()->create(['roles' => ['programme-lead']]);
        $actor = User::factory()->create(['name' => 'Audit Administrator']);
        $otherActor = User::factory()->create(['name' => 'Different Administrator']);
        AuditLog::factory()->create(['actor_id' => $actor->id, 'action' => 'participant.reviewed']);
        AuditLog::factory()->create(['actor_id' => $otherActor->id, 'action' => 'cycle.updated']);

        $response = $this->actingAs($programmeLead)->get(route('admin.audit.index', [
            'search' => 'Audit Administrator',
            'action' => 'participant.reviewed',
        ]));

        $response->assertOk()->assertSee('Participant Reviewed')->assertSee('Audit Administrator')->assertDontSee('Different Administrator');
    }

    public function test_matching_staff_cannot_view_audit_history(): void
    {
        $matchingStaff = User::factory()->create(['roles' => ['matching-team']]);

        $this->actingAs($matchingStaff)->get(route('admin.audit.index'))->assertForbidden();
    }
}
