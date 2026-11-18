<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortUrlAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_a_short_url(): void
    {
        $this->assertCreationForbidden('Admin');
    }

    public function test_member_cannot_create_a_short_url(): void
    {
        $this->assertCreationForbidden('Member');
    }

    public function test_superadmin_cannot_create_a_short_url(): void
    {
        $this->assertCreationForbidden('SuperAdmin');
    }

    public function test_sales_and_managers_can_create_links_for_their_company(): void
    {
        $company = Company::factory()->create();

        foreach (['Sales', 'Manager'] as $role) {
            $user = User::factory()->create(['role' => $role, 'company_id' => $company->id]);
            $this->actingAs($user)
                ->post(route('short-urls.store'), [
                    'title' => $role.' campaign',
                    'destination_url' => 'https://example.com/'.$role,
                ])
                ->assertRedirect(route('dashboard'));

            $this->assertDatabaseHas('short_urls', [
                'company_id' => $company->id,
                'created_by' => $user->id,
                'destination_url' => 'https://example.com/'.$role,
            ]);
        }
    }

    public function test_admin_only_sees_short_urls_from_outside_their_company(): void
    {
        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $admin = User::factory()->create(['role' => 'Admin', 'company_id' => $ownCompany->id]);
        $ownLink = $this->makeShortUrl($ownCompany, User::factory()->create(['company_id' => $ownCompany->id]));
        $otherLink = $this->makeShortUrl($otherCompany, User::factory()->create(['company_id' => $otherCompany->id]));

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee(route('short-urls.resolve', $otherLink->slug))
            ->assertDontSee(route('short-urls.resolve', $ownLink->slug));
    }

    public function test_member_only_sees_short_urls_they_did_not_create(): void
    {
        $company = Company::factory()->create();
        $member = User::factory()->create(['role' => 'Member', 'company_id' => $company->id]);
        $theirLink = $this->makeShortUrl($company, $member);
        $colleagueLink = $this->makeShortUrl(
            $company,
            User::factory()->create(['role' => 'Sales', 'company_id' => $company->id]),
        );

        $response = $this->actingAs($member)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee(route('short-urls.resolve', $colleagueLink->slug))
            ->assertDontSee(route('short-urls.resolve', $theirLink->slug));
    }

    public function test_superadmin_does_not_see_the_cross_company_short_url_list(): void
    {
        $company = Company::factory()->create();
        $link = $this->makeShortUrl(
            $company,
            User::factory()->create(['role' => 'Sales', 'company_id' => $company->id]),
        );
        $superAdmin = User::factory()->create(['role' => 'SuperAdmin']);

        $this->actingAs($superAdmin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Short-link listings are not available to SuperAdmins.')
            ->assertDontSee(route('short-urls.resolve', $link->slug));
    }

    public function test_short_urls_require_authentication_and_then_redirect(): void
    {
        $company = Company::factory()->create();
        $link = $this->makeShortUrl(
            $company,
            User::factory()->create(['role' => 'Sales', 'company_id' => $company->id]),
        );
        $path = route('short-urls.resolve', $link->slug);

        $this->get($path)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($path)
            ->assertRedirect('https://example.com/original');
    }

    private function assertCreationForbidden(string $role): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['role' => $role, 'company_id' => $company->id]);

        $this->actingAs($user)
            ->post(route('short-urls.store'), [
                'title' => 'A link',
                'destination_url' => 'https://example.com/original',
            ])
            ->assertForbidden();
    }

    private function makeShortUrl(Company $company, User $creator): ShortUrl
    {
        return ShortUrl::create([
            'company_id' => $company->id,
            'created_by' => $creator->id,
            'title' => 'Link '.$creator->id,
            'destination_url' => 'https://example.com/original',
            'slug' => 'short'.str_pad((string) $creator->id, 3, '0', STR_PAD_LEFT),
        ]);
    }
}
