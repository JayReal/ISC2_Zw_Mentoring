<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_can_view_current_staff_access(): void
    {
        $administrator = User::factory()->create(['roles' => ['admin']]);
        $programmeLead = User::factory()->create(['name' => 'Programme Lead', 'roles' => ['programme-lead']]);
        User::factory()->create(['name' => 'Ordinary Participant', 'roles' => ['mentee']]);

        $response = $this->actingAs($administrator)->get(route('admin.staff-access.index'));

        $response->assertOk()
            ->assertSee('Staff access')
            ->assertSee($programmeLead->email)
            ->assertDontSee('Ordinary Participant');
    }

    public function test_administrator_can_find_a_registered_participant(): void
    {
        $administrator = User::factory()->create(['roles' => ['admin']]);
        $participant = User::factory()->create(['name' => 'Searchable Participant', 'roles' => ['mentor']]);

        $response = $this->actingAs($administrator)->get(route('admin.staff-access.index', ['search' => 'Searchable']));

        $response->assertOk()->assertSee($participant->email);
    }

    public function test_non_administrator_cannot_view_or_change_staff_access(): void
    {
        $programmeLead = User::factory()->create(['roles' => ['programme-lead']]);
        $participant = User::factory()->create(['roles' => ['mentee']]);

        $this->actingAs($programmeLead)->get(route('admin.staff-access.index'))->assertForbidden();
        $this->actingAs($programmeLead)->put(route('admin.staff-access.update', $participant), [
            'roles' => ['admin'],
        ])->assertForbidden();

        $this->assertSame(['mentee'], $participant->fresh()->roles);
    }

    public function test_administrator_can_grant_staff_access_without_removing_participant_roles(): void
    {
        $administrator = User::factory()->create(['roles' => ['admin']]);
        $participant = User::factory()->create(['roles' => ['mentor']]);

        $response = $this->actingAs($administrator)->put(route('admin.staff-access.update', $participant), [
            'roles' => ['matching-team', 'cluster-lead'],
        ]);

        $response->assertRedirect()->assertSessionHas('status', "Staff access updated for {$participant->name}.");
        $this->assertSame(['mentor', 'matching-team', 'cluster-lead'], $participant->fresh()->roles);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $administrator->id,
            'action' => 'staff.access_updated',
            'subject_id' => $participant->id,
        ]);
    }

    public function test_administrator_can_remove_staff_access_without_removing_participant_roles(): void
    {
        $administrator = User::factory()->create(['roles' => ['admin']]);
        $participant = User::factory()->create(['roles' => ['mentee', 'matching-team']]);

        $response = $this->actingAs($administrator)->put(route('admin.staff-access.update', $participant));

        $response->assertRedirect();
        $this->assertSame(['mentee'], $participant->fresh()->roles);
    }

    public function test_administrator_cannot_change_their_own_staff_access(): void
    {
        $administrator = User::factory()->create(['roles' => ['admin']]);

        $response = $this->actingAs($administrator)->put(route('admin.staff-access.update', $administrator), [
            'roles' => [],
        ]);

        $response->assertForbidden();
        $this->assertSame(['admin'], $administrator->fresh()->roles);
    }

    public function test_unrecognised_staff_role_is_rejected(): void
    {
        $administrator = User::factory()->create(['roles' => ['admin']]);
        $participant = User::factory()->create(['roles' => ['mentee']]);

        $response = $this->actingAs($administrator)->put(route('admin.staff-access.update', $participant), [
            'roles' => ['super-admin'],
        ]);

        $response->assertSessionHasErrors('roles.0');
        $this->assertSame(['mentee'], $participant->fresh()->roles);
    }
}
