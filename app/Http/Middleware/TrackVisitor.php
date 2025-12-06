<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visitor;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Site가 route parameter로 있는 경우에만 추적
        if ($request->route('site')) {
            $site = $request->route('site');
            
            // 관리자 페이지나 API는 제외
            $path = $request->path();
            if (!str_starts_with($path, 'admin') && !str_starts_with($path, 'api')) {
                try {
                    Visitor::track(
                        $site->id,
                        $request->ip(),
                        $request->userAgent()
                    );
                } catch (\Exception $e) {
                    // 방문자 추적 실패해도 페이지는 정상 작동
                    \Log::warning('Visitor tracking failed: ' . $e->getMessage());
                }
            }
        }

        return $next($request);
    }
}
