<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class ResolveSiteByDomain
{
    /**
     * Handle an incoming request.
     * 도메인 기반으로 사이트를 찾아서 라우트에 바인딩합니다.
     * 
     * 참고: URL 리라이트는 RewriteCleanUrls 글로벌 미들웨어에서 처리됩니다.
     * 이 미들웨어는 이미 설정된 site를 확인하거나, 없으면 새로 찾습니다.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 이미 RewriteCleanUrls에서 site가 설정되었으면 그대로 사용
        if ($request->attributes->has('site')) {
            return $next($request);
        }
        
        $host = $request->getHost();
        
        // 포트 제거 (localhost:8000 → localhost)
        $host = preg_replace('/:\d+$/', '', $host);
        
        // 먼저 마스터 사이트를 가져옴
        $masterSite = Site::getMasterSite();
        
        // 마스터 도메인 체크
        $masterDomain = config('app.master_domain', 'seoom.com');
        $masterDomains = [
            $masterDomain,
            'www.' . $masterDomain,
        ];
        
        // 마스터 사이트의 도메인도 마스터 도메인 목록에 추가
        if ($masterSite && $masterSite->domain) {
            $masterDomains[] = $masterSite->domain;
            $masterDomains[] = 'www.' . $masterSite->domain;
        }
        
        if (in_array($host, $masterDomains)) {
            // 마스터 사이트 처리
            if ($masterSite) {
                $request->attributes->set('site', $masterSite);
            }
            return $next($request);
        }
        
        // 서브도메인 체크 (예: test-site.seoom.com)
        $subdomain = $this->extractSubdomain($host, $masterDomain);
        if ($subdomain) {
            $site = Site::where('slug', $subdomain)
                ->where('status', 'active')
                ->first();
            
            if ($site) {
                $request->attributes->set('site', $site);
                return $next($request);
            }
        }
        
        // 커스텀 도메인 체크 (마스터 사이트도 포함)
        $site = Site::where('domain', $host)
            ->where('status', 'active')
            ->first();
        
        if ($site) {
            $request->attributes->set('site', $site);
            return $next($request);
        }
        
        // www. 접두사 제거 후 재시도 (예: www.example.com → example.com)
        if (str_starts_with($host, 'www.')) {
            $hostWithoutWww = substr($host, 4);
            
            $site = Site::where('domain', $hostWithoutWww)
                ->where('status', 'active')
                ->first();
            
            if ($site) {
                $request->attributes->set('site', $site);
                return $next($request);
            }
            
            // www. 제거 후 마스터 사이트 도메인 체크
            if ($masterSite && $masterSite->domain === $hostWithoutWww) {
                $request->attributes->set('site', $masterSite);
                return $next($request);
            }
        }
        
        // 마스터 사이트가 도메인으로 설정된 경우도 체크
        if ($masterSite && $masterSite->domain === $host) {
            $request->attributes->set('site', $masterSite);
            return $next($request);
        }
        
        // 사이트를 찾을 수 없으면 계속 진행 (슬러그 기반 라우팅 또는 마스터 콘솔)
        // 마스터 콘솔 라우트는 허용
        if ($request->is('master/*')) {
            return $next($request);
        }
        
        // 슬러그 기반 라우팅으로 넘어감 (/site/{slug}/ 형태)
        return $next($request);
    }
    
    /**
     * Extract subdomain from host
     * 
     * @param string $host
     * @param string $masterDomain
     * @return string|null
     */
    private function extractSubdomain(string $host, string $masterDomain): ?string
    {
        // 정확히 마스터 도메인과 일치하면 null 반환
        if ($host === $masterDomain || $host === 'www.' . $masterDomain) {
            return null;
        }
        
        // 서브도메인 패턴 체크 (예: test-site.seoom.com)
        if (str_ends_with($host, '.' . $masterDomain)) {
            $subdomain = str_replace('.' . $masterDomain, '', $host);
            // www. 접두사 제거
            $subdomain = str_replace('www.', '', $subdomain);
            return $subdomain ?: null;
        }
        
        return null;
    }
}

