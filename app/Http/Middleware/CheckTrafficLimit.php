<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class CheckTrafficLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 사이트 정보 가져오기
        $site = $request->attributes->get('site');
        
        if (!$site || $site->is_master_site) {
            return $next($request);
        }

        // 트래픽 제한 확인
        $trafficLimit = $site->getTotalTrafficLimit();
        
        // 무제한이면 통과
        if ($trafficLimit === null) {
            return $next($request);
        }

        // 트래픽 사용량 확인
        $trafficUsed = $site->traffic_used_mb ?? 0;
        
        // 트래픽이 초과된 경우
        if ($trafficUsed >= $trafficLimit) {
            // 관리자 페이지나 API는 제외 (관리자가 플랜을 업그레이드할 수 있도록)
            $path = $request->path();
            $isAdminRoute = str_starts_with($path, 'admin') || 
                           str_starts_with($path, 'master') ||
                           str_starts_with($path, 'api') ||
                           str_starts_with($path, 'auth');
            
            if (!$isAdminRoute) {
                // 트래픽 초과 페이지 표시
                return response()->view('errors.traffic-exceeded', [
                    'site' => $site,
                    'trafficUsed' => $trafficUsed,
                    'trafficLimit' => $trafficLimit,
                ], 503);
            }
        }

        return $next($request);
    }
}

