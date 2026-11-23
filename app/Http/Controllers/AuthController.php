<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
        $driver = Socialite::driver('google');

        return $driver
            ->scopes(['https://www.googleapis.com/auth/drive.file'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()->route('login')->withErrors([
                'google' => 'Login Google gagal. Silakan coba lagi.',
            ]);
        }

        $email = mb_strtolower((string) $googleUser->getEmail());
        $user = User::where('email', $email)->first();

        if ($user === null || $user->assignedRoles() === []) {
            return redirect()->route('login')->withErrors([
                'google' => 'Akun Google Anda belum terdaftar atau belum memiliki akses di Grobook. Hubungi Ketua Tim atau Petugas DLS.',
            ]);
        }

        // If user only has TimNaskah role and has no team, block login
        $nonTeamRoles = array_filter($user->assignedRoles(), fn (Role $r) => $r !== Role::TimNaskah);
        if ($user->team_id === null && $nonTeamRoles === []) {
            return redirect()->route('login')->withErrors([
                'google' => 'Akun Google Anda belum terdaftar di sebuah tim. Hubungi Ketua Tim atau Petugas DLS.',
            ]);
        }

        // If current active role is TimNaskah without a team, switch active role to one of their non-team roles
        $activeRole = $user->role;
        if ($activeRole === Role::TimNaskah && $user->team_id === null && $nonTeamRoles !== []) {
            $activeRole = array_values($nonTeamRoles)[0];
        }

        $accessToken = property_exists($googleUser, 'token') ? $googleUser->token : null;
        $refreshToken = property_exists($googleUser, 'refreshToken') ? $googleUser->refreshToken : null;
        $expiresIn = property_exists($googleUser, 'expiresIn') ? $googleUser->expiresIn : null;

        $user->update([
            'role' => $activeRole,
            'google_id' => $googleUser->getId(),
            'google_access_token' => $accessToken ?? $user->google_access_token,
            'google_refresh_token' => $refreshToken ?? $user->google_refresh_token,
            'google_token_expires_at' => $expiresIn ? now()->addSeconds((int) $expiresIn) : $user->google_token_expires_at,
            'name' => $googleUser->getName() ?? $user->name,
            'avatar' => $googleUser->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}