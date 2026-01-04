<?php

namespace App\Services;

use App\Models\MasterUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class MasterAuthService
{
    /**
     * Attempt to login master user.
     */
    public function login(array $credentials, $remember = false)
    {
        $guard = Auth::guard('master');
        
        if ($guard->attempt($credentials, $remember)) {
            request()->session()->regenerate();
            return true;
        }

        return false;
    }

    /**
     * Logout master user.
     */
    public function logout()
    {
        Auth::guard('master')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Get authenticated master user.
     */
    public function user()
    {
        return Auth::guard('master')->user();
    }

    /**
     * Check if master user is authenticated.
     */
    public function check()
    {
        return Auth::guard('master')->check();
    }
}












