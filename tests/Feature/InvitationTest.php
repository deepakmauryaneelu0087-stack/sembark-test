<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_invite_another_admin_or_member(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create(['role' => 'Admin', 'company_id' => $company->id]);

        foreach (['Admin', 'Member'] as $role) {
            $this->actingAs($admin)
                ->post(route('invitations.store'), [
                    'email' => strtolower($role).'@example.com',
                    'role' => $role,
                    'company_id' => $company->id,
                ])
                ->assertSessionHasErrors('role');
        }

        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_superadmin_cannot_invite_an_admin(): void
    {
        $company = Company::factory()->create();
        $superAdmin = User::factory()->create(['role' => 'SuperAdmin']);

        $this->actingAs($superAdmin)
            ->post(route('invitations.store'), [
                'email' => 'admin@example.com',
                'role' => 'Admin',
                'company_id' => $company->id,
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_admin_can_only_invite_allowed_roles_into_their_own_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $admin = User::factory()->create(['role' => 'Admin', 'company_id' => $company->id]);

        $this->actingAs($admin)
            ->post(route('invitations.store'), [
                'email' => 'sales@example.com',
                'role' => 'Sales',
                'company_id' => $company->id,
            ])
            ->assertRedirect(route('dashboard'));

        $this->actingAs($admin)
            ->post(route('invitations.store'), [
                'email' => 'manager@example.com',
                'role' => 'Manager',
                'company_id' => $otherCompany->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('invitations', [
            'email' => 'sales@example.com',
            'company_id' => $company->id,
            'role' => 'Sales',
        ]);
        $this->assertDatabaseCount('invitations', 1);
    }

    public function test_invited_user_can_accept_an_unexpired_invitation(): void
    {
        $company = Company::factory()->create();
        $inviter = User::factory()->create(['role' => 'SuperAdmin']);
        $invitation = Invitation::create([
            'company_id' => $company->id,
            'invited_by' => $inviter->id,
            'email' => 'new.person@example.com',
            'role' => 'Sales',
            'token' => str_repeat('a', 64),
            'expires_at' => now()->addDay(),
        ]);

        $this->get(route('invitations.accept', $invitation->token))->assertOk();
        $this->post(route('invitations.complete', $invitation->token), [
            'name' => 'New Person',
            'password' => 'SecurePassword1',
            'password_confirmation' => 'SecurePassword1',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'new.person@example.com',
            'company_id' => $company->id,
            'role' => 'Sales',
        ]);
        $this->assertNotNull($invitation->fresh()->accepted_at);
    }
}
