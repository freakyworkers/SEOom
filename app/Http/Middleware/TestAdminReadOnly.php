<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TestAdminReadOnly
{
    /**
     * 테스트 어드민 사용자의 데이터 수정 요청을 차단합니다.
     * GET 요청은 허용하고, POST/PUT/PATCH/DELETE 요청은 차단합니다.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 테스트 어드민 세션 확인
        if (session('is_test_admin')) {
            // GET, HEAD 요청은 허용
            if (in_array($request->method(), ['GET', 'HEAD'])) {
                return $next($request);
            }
            
            // AJAX 요청인 경우
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '테스트 어드민 계정은 수정 권한이 없습니다. 변경 사항은 저장되지 않습니다.',
                    'is_test_admin' => true,
                ], 403);
            }
            
            // 일반 요청인 경우 - 이전 페이지로 리다이렉트하면서 경고 메시지 표시
            return redirect()->back()->with('warning', '테스트 어드민 계정은 수정 권한이 없습니다. 변경 사항은 저장되지 않습니다.');
        }
        
        return $next($request);
    }
}

