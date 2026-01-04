<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // 테스트 어드민 세션이 있으면 인증 우회
        if (session('is_test_admin') && session('test_admin_site_id')) {
            // 현재 사이트가 테스트 어드민 세션의 사이트와 일치하는지 확인
            $site = $request->attributes->get('site') ?? $request->route('site');
            $siteId = is_object($site) ? $site->id : null;
            
            if ($siteId === session('test_admin_site_id')) {
                return $next($request);
            }
        }
        
        return parent::handle($request, $next, ...$guards);
    }
    
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

