<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class OnlyMasterDomain
{
    /**
     * Handle an incoming request.
     * 마스터 도메인에서만 요청을 허용합니다.
     * 서브도메인에서 접근 시 사이트별 라우트로 넘깁니다.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // 포트 제거
        $host = preg_replace('/:\d+$/', '', $host);
        
        // 마스터 도메인 체크
        $masterDomain = config('app.master_domain', 'seoomweb.com');
        $masterSite = Site::getMasterSite();
        
        $masterDomains = [
            $masterDomain,
            'www.' . $masterDomain,
            'localhost',
        ];
        
        // 마스터 사이트의 도메인도 마스터 도메인 목록에 추가
        if ($masterSite && $masterSite->domain) {
            $masterDomains[] = $masterSite->domain;
            $masterDomains[] = 'www.' . $masterSite->domain;
        }
        
        // 마스터 도메인이면 통과
        if (in_array($host, $masterDomains)) {
            return $next($request);
        }
        
        // 서브도메인 체크 (예: web.seoomweb.com)
        // 서브도메인에서 접근하면 해당 사이트의 라우트를 사용해야 함
        if (str_ends_with($host, '.' . $masterDomain)) {
            // 서브도메인에서 마스터 관리자 라우트에 접근한 경우
            // 해당 사이트의 관리자 라우트로 처리하도록 abort
            abort(404);
        }
        
        // 커스텀 도메인인 경우에도 사이트별 라우트를 사용해야 함
        $site = Site::where('domain', $host)
            ->where('status', 'active')
            ->first();
        
        if ($site && (!$masterSite || $site->id !== $masterSite->id)) {
            abort(404);
        }
        
        return $next($request);
    }
}
