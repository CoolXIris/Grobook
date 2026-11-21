<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Google');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_google_redirect_points_to_google(): void
    {
        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirectContains('accounts.google.com');
    }

    public function test_callback_rejects_unregistered_google_account(): void
    {
        Socialite::shouldReceive('driver->user')->once()->andReturn($this->googleUser('baru@gmail.com'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertDatabaseMissing('users', ['email' => 'baru@gmail.com']);
        $this->assertGuest();
    }

    public function test_callback_rejects_tim_naskah_without_team(): void
    {
        User::factory()->create(['email' => 'tanpa.tim@gmail.com', 'role' => Role::TimNaskah, 'team_id' => null]);

        Socialite::shouldReceive('driver->user')->once()->andReturn($this->googleUser('tanpa.tim@gmail.com'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
    }

    public function test_callback_allows_registered_tim_naskah_with_team(): void
    {
        $user = User::factory()->inTeam()->create(['email' => 'anggota.tim@gmail.com']);

        Socialite::shouldReceive('driver->user')->once()->andReturn($this->googleUser('anggota.tim@gmail.com'));

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $this->assertSame('google-123', $user->fresh()->google_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_callback_keeps_existing_role(): void
    {
        $existing = User::factory()->ketuaDls()->create(['email' => 'ketua.dls@gmail.com']);

        Socialite::shouldReceive('driver->user')->once()->andReturn($this->googleUser('ketua.dls@gmail.com'));

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $this->assertSame(Role::KetuaDls, $existing->fresh()->role);
        $this->assertAuthenticatedAs($existing);
    }

    public function test_callback_failure_redirects_to_login_with_error(): void
    {
        Socialite::shouldReceive('driver->user')->once()->andThrow(new \RuntimeException('invalid state'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
    }

    public function test_logout_ends_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    private function googleUser(string $email): SocialiteUser
    {
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getEmail')->andReturn($email);
        $googleUser->shouldReceive('getName')->andReturn('Pengguna Uji');
        $googleUser->shouldReceive('getId')->andReturn('google-123');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.png');

        return $googleUser;
    }
}
