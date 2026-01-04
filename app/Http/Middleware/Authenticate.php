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
            // 현재 사이트 가져오기 (다양한 방법 시도)
            $site = $request->attributes->get('site');
            
            if (!$site) {
                $site = $request->route('site');
            }
            
            // 도메인/서브도메인으로 접근한 경우 사이트 찾기
            if (!$site) {
                $host = $request->getHost();
                $masterDomain = config('app.master_domain', 'seoomweb.com');
                
                // 커스텀 도메인으로 찾기
                $site = \App\Models\Site::where('domain', $host)
                    ->orWhere('domain', str_replace('www.', '', $host))
                    ->first();
                
                // 서브도메인으로 찾기 (예: landing.seoomweb.com -> slug: landing)
                if (!$site && str_ends_with($host, '.' . $masterDomain)) {
                    $subdomain = str_replace('.' . $masterDomain, '', $host);
                    $subdomain = str_replace('www.', '', $subdomain);
                    $site = \App\Models\Site::where('slug', $subdomain)->first();
                }
            }
            
            $siteId = is_object($site) ? $site->id : null;
            $testAdminSiteId = session('test_admin_site_id');
            
            // 타입 변환하여 비교 (int vs int)
            if ($siteId && (int)$siteId === (int)$testAdminSiteId) {
                // 사이트 정보를 request에 설정
                if ($site && !$request->attributes->has('site')) {
                    $request->attributes->set('site', $site);
                }
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

