<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySiteUser
{
    /**
     * Handle an incoming request.
     * 사이트 접근 시 로그인된 사용자가 해당 사이트의 사용자인지 확인합니다.
     * 다른 사이트의 사용자가 접근하면 자동으로 로그아웃하고 해당 사이트의 로그인 페이지로 리다이렉트합니다.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // my-sites 라우트는 특별 처리 (사용자가 자신이 만든 사이트를 관리하기 위해 마스터 사이트에 접근)
        if ($request->is('site/*/my-sites*')) {
            return $next($request);
        }
        
        // Site가 route parameter로 있는 경우에만 체크
        if ($request->route('site')) {
            $site = $request->route('site');
            
            // 로그인된 사용자가 있는 경우
            if (auth()->check()) {
                $user = auth()->user();
                
                // 마스터 관리자는 모든 사이트에 접근 가능 (SSO 로그인 시 세션에 저장된 정보 확인)
                $isMasterUser = session('is_master_user', false) || auth('master')->check();
                
                // 사이트를 생성한 사용자도 마스터 사용자로 간주 (my-sites 라우트 접근용)
                if (!$isMasterUser && $site->isMasterSite()) {
                    // 마스터 사이트에 접근하는 경우, 사용자가 다른 사이트를 생성한 경우 허용
                    $userSites = \App\Models\Site::where('created_by', $user->id)
                        ->where('is_master_site', false)
                        ->exists();
                    if ($userSites) {
                        $isMasterUser = true;
                    }
                }
                
                // 마스터 관리자가 아니고, 사용자가 해당 사이트의 사용자가 아닌 경우
                if (!$isMasterUser && $user->site_id !== $site->id) {
                    // 로그아웃 처리
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    // 해당 사이트의 로그인 페이지로 리다이렉트
                    return redirect()->route('login', ['site' => $site->slug])
                        ->with('error', '다른 사이트의 계정으로 로그인되어 있습니다. 해당 사이트의 계정으로 다시 로그인해주세요.');
                }
            }
        }

        return $next($request);
    }
}


