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
        return Socialite::driver('google')->redirect();
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

        if ($user === null || $user->role === null || ($user->role === Role::TimNaskah && $user->team_id === null)) {
            return redirect()->route('login')->withErrors([
                'google' => 'Akun Google Anda belum terdaftar atau belum memiliki akses di Grobook. Hubungi Ketua Tim atau Petugas DLS.',
            ]);
        }

        $user->update([
            'google_id' => $googleUser->getId(),
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