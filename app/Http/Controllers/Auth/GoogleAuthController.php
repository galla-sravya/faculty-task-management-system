<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Enforce domain restriction
            if (!str_ends_with($googleUser->getEmail(), '@psgitech.ac.in')) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Only @psgitech.ac.in accounts are authorized to access this system.'
                ]);
            }

            // Find existing user by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // If user is found, ensure they are active
                if (!$user->isActive()) {
                    return redirect()->route('login')->withErrors([
                        'email' => 'Your account is inactive. Please contact the administrator.'
                    ]);
                }

                // Log the user in
                Auth::login($user);

                // Redirect to the intended dashboard based on role
                return redirect()->intended(route('dashboard'));
            }

            // If user is not found in the database, reject login
            return redirect()->route('login')->withErrors([
                'email' => 'Your Google account is not registered in Activity Manager. Please contact the administrator.'
            ]);

        } catch (\Exception $e) {
            // Handle any exceptions (e.g., user cancelled Google login)
            return redirect()->route('login')->withErrors([
                'email' => 'Google authentication failed or was cancelled. Please try again.'
            ]);
        }
    }
}
