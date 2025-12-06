<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        
        // Extract site slug from URL path if route binding failed
        $path = $request->path();
        if (preg_match('/site\/([^\/]+)/', $path, $matches)) {
            try {
                return route('login', ['site' => $matches[1]]);
            } catch (\Exception $e) {
                // If route generation fails, return null to use default redirect
            }
        }
        
        // Try to get site from route parameter
        try {
            $site = $request->route('site');
            if ($site) {
                $slug = is_object($site) ? ($site->slug ?? null) : $site;
                if ($slug) {
                    return route('login', ['site' => $slug]);
                }
            }
        } catch (\Exception $e) {
            // Ignore errors
        }
        
        return '/login';
    }
}

