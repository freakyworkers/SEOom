<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SampleSiteReadOnly
{
    /**
     * 샘플 사이트에서 수정 요청을 차단합니다.
     * 마스터 사용자는 수정 가능합니다.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $site = $request->attributes->get('site');
        
        // 사이트가 없거나 샘플 사이트가 아니면 통과
        if (!$site || !$site->isSample()) {
            return $next($request);
        }

        // GET 요청은 허용 (읽기 전용)
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        // 마스터 사용자인 경우 수정 허용
        if (session()->has('master_user_id')) {
            return $next($request);
        }

        // POST, PUT, PATCH, DELETE 요청 차단
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // AJAX 요청인 경우 JSON 응답
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '샘플 사이트는 수정할 수 없습니다. 직접 사이트를 만들어서 사용해주세요.',
                ], 403);
            }

            // 일반 요청인 경우 리다이렉트
            return redirect()->back()->with('error', '샘플 사이트는 수정할 수 없습니다. 직접 사이트를 만들어서 사용해주세요.');
        }

        return $next($request);
    }
}

