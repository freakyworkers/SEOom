<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // /admin 경로는 IP 차단 제외
        if ($request->is('admin*')) {
            return $next($request);
        }
        
        // Site가 route parameter로 있는 경우에만 체크
        if ($request->route('site')) {
            $site = $request->route('site');
            $userIp = $request->ip();
            
            // 관리자 페이지는 IP 차단 제외
            $path = $request->path();
            if (str_starts_with($path, 'site/' . $site->slug . '/admin')) {
                return $next($request);
            }
            
            // 차단된 IP 목록 가져오기
            $blockedIpsValue = $site->getSetting('blocked_ips', '');
            
            // getSetting이 이미 배열을 반환할 수 있으므로 타입 확인
            if (is_array($blockedIpsValue)) {
                $blockedIps = $blockedIpsValue;
            } elseif (is_string($blockedIpsValue) && !empty($blockedIpsValue)) {
                $blockedIps = json_decode($blockedIpsValue, true);
                if (!is_array($blockedIps)) {
                    $blockedIps = [];
                }
            } else {
                $blockedIps = [];
            }
            
            // 현재 IP가 차단 목록에 있는지 확인
            if (in_array($userIp, $blockedIps)) {
                abort(403, '접근이 차단된 IP 주소입니다.');
            }
        }

        return $next($request);
    }
}

