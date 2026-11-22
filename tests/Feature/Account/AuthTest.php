<?php

namespace Tests\Feature\Account;

use App\Models\Story;
use App\Models\User;
use App\Services\StoryDraftService;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStories;
use Tests\TestCase;

// F6: US-10 AC5 · US-11 AC1, AC2 · US-12 AC1, AC2 · US-13 AC2 · US-19.
class AuthTest extends TestCase
{
    use RefreshDatabase, BuildsStories;

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kiss Anna',
            'email' => 'anna@example.com',
            'password' => 'titkos-jelszo',
            'password_confirmation' => 'titkos-jelszo',
            'privacy_accepted' => '1',
        ], $overrides);
    }

    public function test_registration_logs_in_and_stores_consent_time()
    {
        $this->post(route('register'), $this->registration())->assertRedirect(route('home'));

        $user = User::where('email', 'anna@example.com')->first();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertNotNull($user->privacy_accepted_at);
    }

    public function test_registration_validates_fields_and_consent()
    {
        User::factory()->create(['email' => 'anna@example.com']);

        $this->post(route('register'), $this->registration(['password' => 'rovid', 'password_confirmation' => 'rovid']))
            ->assertSessionHasErrors(['email', 'password']);

        $this->post(route('register'), $this->registration(['email' => 'mas@example.com', 'privacy_accepted' => null]))
            ->assertSessionHasErrors('privacy_accepted');

        $this->assertGuest();
    }

    public function test_registration_cannot_grant_admin_role()
    {
        $this->post(route('register'), $this->registration(['role' => 'admin']));

        $this->assertSame(User::ROLE_USER, User::where('email', 'anna@example.com')->value('role'));
    }

    public function test_login_with_valid_and_invalid_credentials()
    {
        $user = User::factory()->create(['email' => 'anna@example.com']);

        $this->post(route('login'), ['email' => 'anna@example.com', 'password' => 'rossz'])
            ->assertSessionHasErrors(['login' => __('auth.failed')]);
        $this->assertGuest();

        $this->post(route('login'), ['email' => 'anna@example.com', 'password' => 'password'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_protected_pages_are_unavailable_after_logout()
    {
        $this->actingAs(User::factory()->create())->post(route('logout'))->assertRedirect(route('home'));

        $this->get(route('stories.index'))->assertRedirect(route('login'));
        $this->get(route('account.show'))->assertRedirect(route('login'));
    }

    public function test_login_is_rate_limited()
    {
        for ($i = 0; $i < config('story.rate_limits.login_attempts_per_minute'); $i++) {
            $this->post(route('login'), ['email' => 'x@example.com', 'password' => 'rossz']);
        }

        $this->post(route('login'), ['email' => 'x@example.com', 'password' => 'rossz'])->assertStatus(429);
    }

    public function test_guest_story_is_kept_after_login_and_can_be_saved()
    {
        $this->makeCatalog();
        $user = User::factory()->create(['email' => 'anna@example.com']);
        $this->generateStory();

        $this->post(route('login'), ['email' => 'anna@example.com', 'password' => 'password'])
            ->assertRedirect(route('stories.result'));
        $this->assertSame(StoryDraftService::STATUS_GENERATED, session(StoryDraftService::KEY)['status']);

        $this->post(route('stories.store'))->assertRedirect(route('stories.result', ['page' => 0]));
        $this->assertSame(1, $user->stories()->count());
    }

    public function test_guest_story_in_progress_is_kept_after_registration()
    {
        $this->makeCatalog();
        $this->completeSteps();
        $this->post(route('stories.create.page.store', 1), $this->pageData(['target' => 'page:3']));

        // Az utoljára szerkesztett (mentett) oldalra tér vissza (App Flow D-23). / Back to the last edited page (D-23).
        $this->post(route('register'), $this->registration())->assertRedirect(route('stories.create.page', 1));
        $this->assertSame('Panni', session(StoryDraftService::KEY)['child']['name']);
    }

    public function test_account_deletion_removes_user_and_stories()
    {
        $user = User::factory()->create();
        $story = Story::factory()->withPages()->create(['user_id' => $user->id]);

        $this->actingAs($user)->delete(route('account.destroy'), ['password' => 'rossz'])
            ->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->actingAs($user)->delete(route('account.destroy'), ['password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('stories', ['id' => $story->id]);
        $this->assertDatabaseCount('story_pages', 0);
        $this->assertDatabaseCount('story_page_placements', 0);
    }

    public function test_admin_account_cannot_be_deleted()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('account.destroy'), ['password' => 'password'])->assertForbidden();
    }

    public function test_admin_seeder_does_not_duplicate()
    {
        config(['story.admin.email' => 'admin@example.com', 'story.admin.password' => 'admin-jelszo']);

        $this->seed(AdminUserSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->assertSame(1, User::where('email', 'admin@example.com')->count());
        $this->assertTrue(User::where('email', 'admin@example.com')->first()->isAdmin());
    }
}
