<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class ResolveSiteByDomain
{
    /**
     * 리라이트하지 않을 경로들 (이미 site/{site}로 시작하거나 특수 경로)
     */
    protected $excludedPaths = [
        'site/',
        'master/',
        'api/',
        'livewire/',
        'storage/',
        '_debugbar/',
        'sanctum/',
        'horizon/',
        'telescope/',
    ];
    
    /**
     * Handle an incoming request.
     * 도메인 기반으로 사이트를 찾아서 라우트에 바인딩합니다.
     * 도메인/서브도메인으로 접속 시 URL을 내부적으로 /site/{slug}/ 형태로 리라이트합니다.
     * 
     * 우선순위:
     * 1. 마스터 도메인 → 마스터 사이트
     * 2. 서브도메인 → slug로 사이트 조회
     * 3. 커스텀 도메인 → domain으로 사이트 조회
     */
    public function handle(Request $request, Closure $next): Response
    {
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
        
        $site = null;
        
        if (in_array($host, $masterDomains)) {
            // 마스터 사이트 처리
            $site = $masterSite;
        } else {
            // 서브도메인 체크 (예: test-site.seoom.com)
            $subdomain = $this->extractSubdomain($host, $masterDomain);
            if ($subdomain) {
                $site = Site::where('slug', $subdomain)
                    ->where('status', 'active')
                    ->first();
            }
            
            // 커스텀 도메인 체크
            if (!$site) {
                $site = Site::where('domain', $host)
                    ->where('status', 'active')
                    ->first();
            }
            
            // www. 접두사 제거 후 재시도
            if (!$site && str_starts_with($host, 'www.')) {
                $hostWithoutWww = substr($host, 4);
                
                $site = Site::where('domain', $hostWithoutWww)
                    ->where('status', 'active')
                    ->first();
                
                // www. 제거 후 마스터 사이트 도메인 체크
                if (!$site && $masterSite && $masterSite->domain === $hostWithoutWww) {
                    $site = $masterSite;
                }
            }
            
            // 마스터 사이트가 도메인으로 설정된 경우
            if (!$site && $masterSite && $masterSite->domain === $host) {
                $site = $masterSite;
            }
        }
        
        // 사이트를 찾은 경우
        if ($site) {
            $request->attributes->set('site', $site);
            
            // URL 리라이트가 필요한지 확인
            $path = $request->getPathInfo();
            
            if ($this->shouldRewriteUrl($path)) {
                // /site/{slug}/ prefix가 없는 경우 내부적으로 리라이트
                $newPath = '/site/' . $site->slug . $path;
                
                // Request의 경로를 내부적으로 변경
                $this->rewriteRequest($request, $newPath);
                
                // 도메인 기반 접근임을 표시 (링크 생성 시 사용)
                $request->attributes->set('domain_based_access', true);
                $request->attributes->set('original_path', $path);
            }
        }
        
        // 마스터 콘솔 라우트는 허용
        if ($request->is('master/*')) {
            return $next($request);
        }
        
        return $next($request);
    }
    
    /**
     * URL 리라이트가 필요한지 확인
     */
    protected function shouldRewriteUrl(string $path): bool
    {
        // 이미 /site/{slug}/ 형태이거나 제외 경로인 경우 리라이트하지 않음
        foreach ($this->excludedPaths as $excludedPath) {
            if (str_starts_with(ltrim($path, '/'), $excludedPath)) {
                return false;
            }
        }
        
        // 정적 파일 요청은 리라이트하지 않음
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'map'];
        if (in_array(strtolower($extension), $staticExtensions)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Request 경로를 내부적으로 리라이트
     */
    protected function rewriteRequest(Request $request, string $newPath): void
    {
        // SERVER 변수 업데이트
        $request->server->set('REQUEST_URI', $newPath . ($request->getQueryString() ? '?' . $request->getQueryString() : ''));
        
        // PathInfo 초기화를 위해 request 재초기화
        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $request->getContent()
        );
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

