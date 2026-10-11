<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\StaffAccessChangedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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
        Notification::fake();
        $administrator = User::factory()->create(['roles' => ['admin']]);
        $participant = User::factory()->create(['roles' => ['mentor']]);

        $response = $this->actingAs($administrator)->put(route('admin.staff-access.update', $participant), [
            'current_password' => 'password',
            'roles' => ['matching-team', 'cluster-lead'],
        ]);

        $response->assertRedirect()->assertSessionHas('status', "Staff access updated for {$participant->name}.");
        $this->assertSame(['mentor', 'matching-team', 'cluster-lead'], $participant->fresh()->roles);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $administrator->id,
            'action' => 'staff.access_updated',
            'subject_id' => $participant->id,
        ]);
        Notification::assertSentTo($participant, StaffAccessChangedNotification::class, fn (StaffAccessChangedNotification $notification): bool => $notification->afterRoles === ['matching-team', 'cluster-lead']);
    }

    public function test_administrator_can_remove_staff_access_without_removing_participant_roles(): void
    {
        Notification::fake();
        $administrator = User::factory()->create(['roles' => ['admin']]);
        $participant = User::factory()->create(['roles' => ['mentee', 'matching-team']]);

        $response = $this->actingAs($administrator)->delete(route('admin.staff-access.destroy', $participant), [
            'confirm_removal' => 'yes',
            'current_password' => 'password',
        ]);

        $response->assertRedirect()->assertSessionHas('status', "Staff access removed for {$participant->name}.");
        $this->assertSame(['mentee'], $participant->fresh()->roles);
        Notification::assertSentTo($participant, StaffAccessChangedNotification::class, fn (StaffAccessChangedNotification $notification): bool => $notification->afterRoles === []);
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

    public function test_final_administrator_cannot_be_removed_with_a_stale_privileged_session(): void
    {
        Notification::fake();
        $actingAdministrator = User::factory()->create(['roles' => ['admin']]);
        $finalAdministrator = User::factory()->create(['roles' => ['admin']]);
        $this->actingAs($actingAdministrator);
        DB::table('users')->where('id', $actingAdministrator->id)->update(['roles' => json_encode(['programme-lead'])]);

        $response = $this->delete(route('admin.staff-access.destroy', $finalAdministrator), [
            'confirm_removal' => 'yes',
            'current_password' => 'password',
        ]);

        $response->assertSessionHasErrors('roles');
        $this->assertSame(['admin'], $finalAdministrator->fresh()->roles);
        Notification::assertNothingSent();
    }

    public function test_recent_access_changes_are_visible_to_administrators(): void
    {
        Notification::fake();
        $administrator = User::factory()->create(['name' => 'Access Administrator', 'roles' => ['admin']]);
        $participant = User::factory()->create(['name' => 'New Staff Member', 'roles' => ['mentor']]);
        $this->actingAs($administrator)->put(route('admin.staff-access.update', $participant), [
            'current_password' => 'password',
            'roles' => ['matching-team'],
        ]);

        $response = $this->actingAs($administrator)->get(route('admin.staff-access.index'));

        $response->assertOk()
            ->assertSee('Recent access changes')
            ->assertSee('New Staff Member')
            ->assertSee('Access Administrator')
            ->assertSee('Matching team');
    }

    public function test_incorrect_current_password_prevents_staff_access_change(): void
    {
        Notification::fake();
        $administrator = User::factory()->create(['roles' => ['admin']]);
        $participant = User::factory()->create(['roles' => ['mentor']]);

        $response = $this->actingAs($administrator)->put(route('admin.staff-access.update', $participant), [
            'current_password' => 'incorrect-password',
            'roles' => ['matching-team'],
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertSame(['mentor'], $participant->fresh()->roles);
        Notification::assertNothingSent();
    }
}
