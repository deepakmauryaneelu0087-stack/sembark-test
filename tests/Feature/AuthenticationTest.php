<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_sign_in_and_sign_out(): void
    {
        $user = User::factory()->create(['email' => 'person@example.com']);

        $this->post(route('login.store'), [
            'email' => 'person@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_database_seeder_creates_superadmin_with_raw_sql(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => env('SUPERADMIN_EMAIL', 'superadmin@example.com'),
            'role' => 'SuperAdmin',
            'company_id' => null,
        ]);
        $this->assertDatabaseHas('companies', ['name' => 'Example Company']);
    }
}
