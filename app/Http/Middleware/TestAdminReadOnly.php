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
     * 마스터 콘솔에서 테스트 어드민이 등록된 사이트에서만 동작합니다.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 테스트 어드민 세션 확인
        if (session('is_test_admin')) {
            // 현재 사이트 가져오기
            $site = $request->attributes->get('site');
            
            // 사이트가 없거나 테스트 어드민이 등록되어 있지 않으면 세션 클리어 후 통과
            if (!$site || !$this->siteHasTestAdmin($site)) {
                // 테스트 어드민이 등록되지 않은 사이트에서는 세션을 클리어하고 통과
                session()->forget(['is_test_admin', 'test_admin_site_id', 'test_admin_username']);
                return $next($request);
            }
            
            // 세션에 저장된 사이트 ID와 현재 사이트 ID가 다르면 세션 클리어 후 통과
            $testAdminSiteId = session('test_admin_site_id');
            if ($testAdminSiteId && (int)$site->id !== (int)$testAdminSiteId) {
                session()->forget(['is_test_admin', 'test_admin_site_id', 'test_admin_username']);
                return $next($request);
            }
            
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
    
    /**
     * 사이트에 테스트 어드민이 등록되어 있는지 확인
     */
    private function siteHasTestAdmin($site): bool
    {
        $testAdmin = $site->test_admin;
        
        // test_admin이 null이거나 비어있으면 등록되지 않음
        if (empty($testAdmin)) {
            return false;
        }
        
        // id와 password가 모두 있어야 등록된 것으로 판단
        if (empty($testAdmin['id']) || empty($testAdmin['password'])) {
            return false;
        }
        
        return true;
    }
}

