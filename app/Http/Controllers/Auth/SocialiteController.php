<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Update Google info if user exists
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(Str::random(16)), // Random password
                    'is_approved' => false, // Requires admin approval
                ]);

                // Assign default role
                $user->assignRole('Usuario Activo');
            }

            // Check if user is approved
            if (!$user->isApproved()) {
                return redirect()->route('login')
                    ->with('error', 'Tu cuenta está pendiente de aprobación por un administrador.');
            }

            // Login
            Auth::login($user);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Error al autenticar con Google. Intenta nuevamente.');
        }
    }
}
