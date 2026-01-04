<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class RewriteCleanUrls
{
    /**
     * 리라이트하지 않을 경로들
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
        'auth/',  // 소셜 로그인 콜백
        'store/',  // 스토어 페이지 (마스터 사이트 전용)
        'login',
        'register',
        'logout',
        'plans/',
        'payment/',
    ];
    
    /**
     * Handle an incoming request.
     * 도메인/서브도메인으로 접속 시 URL을 /site/{slug}/ 형태로 리라이트합니다.
     * 이 미들웨어는 글로벌 미들웨어로, 라우팅 전에 실행됩니다.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();
        $host = $request->getHost();
        
        // 포트 제거
        $host = preg_replace('/:\d+$/', '', $host);
        
        // 사이트 찾기
        $site = $this->findSiteByHost($host);
        
        if ($site) {
            // Request attributes에 site 저장 (나중에 사용)
            $request->attributes->set('site', $site);
            $request->attributes->set('domain_based_access', true);
            $request->attributes->set('original_path', $path);
            
            // 이미 /site/{slug}/ 형태이거나 제외 경로인 경우 리라이트하지 않음
            // 하지만 domain_based_access는 설정됨 (클린 URL 변환용)
            if ($this->shouldRewriteUrl($path)) {
                // URL 리라이트
                $newPath = '/site/' . $site->slug . $path;
                $this->rewriteRequest($request, $newPath);
            }
        }
        
        return $next($request);
    }
    
    /**
     * 호스트로 사이트 찾기
     */
    protected function findSiteByHost(string $host): ?Site
    {
        try {
            $masterSite = Site::getMasterSite();
            $masterDomain = config('app.master_domain', 'seoom.com');
            
            // 마스터 도메인 목록
            $masterDomains = [
                $masterDomain,
                'www.' . $masterDomain,
            ];
            
            if ($masterSite && $masterSite->domain) {
                $masterDomains[] = $masterSite->domain;
                $masterDomains[] = 'www.' . $masterSite->domain;
            }
            
            // 마스터 도메인 체크
            if (in_array($host, $masterDomains)) {
                return $masterSite;
            }
            
            // 서브도메인 체크
            $subdomain = $this->extractSubdomain($host, $masterDomain);
            if ($subdomain) {
                $site = Site::where('slug', $subdomain)
                    ->where('status', 'active')
                    ->first();
                if ($site) {
                    return $site;
                }
            }
            
            // 커스텀 도메인 체크
            $site = Site::where('domain', $host)
                ->where('status', 'active')
                ->first();
            if ($site) {
                return $site;
            }
            
            // www. 제거 후 재시도
            if (str_starts_with($host, 'www.')) {
                $hostWithoutWww = substr($host, 4);
                
                $site = Site::where('domain', $hostWithoutWww)
                    ->where('status', 'active')
                    ->first();
                if ($site) {
                    return $site;
                }
                
                if ($masterSite && $masterSite->domain === $hostWithoutWww) {
                    return $masterSite;
                }
            }
            
            // 마스터 사이트 도메인 체크
            if ($masterSite && $masterSite->domain === $host) {
                return $masterSite;
            }
            
        } catch (\Exception $e) {
            // 데이터베이스 연결 문제 등은 무시
            \Log::warning('RewriteCleanUrls: Failed to find site', ['error' => $e->getMessage()]);
        }
        
        return null;
    }
    
    /**
     * URL 리라이트가 필요한지 확인
     */
    protected function shouldRewriteUrl(string $path): bool
    {
        $trimmedPath = ltrim($path, '/');
        
        // 루트 경로는 리라이트하지 않음 (이미 처리됨)
        if (empty($trimmedPath)) {
            return false;
        }
        
        // 제외 경로 체크
        foreach ($this->excludedPaths as $excludedPath) {
            // 정확히 일치하거나 시작하는 경우 제외
            if ($trimmedPath === rtrim($excludedPath, '/') || str_starts_with($trimmedPath, $excludedPath)) {
                return false;
            }
        }
        
        // 정적 파일은 리라이트하지 않음
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'map', 'xml', 'txt'];
        if (in_array(strtolower($extension), $staticExtensions)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Request 경로를 리라이트
     */
    protected function rewriteRequest(Request $request, string $newPath): void
    {
        $queryString = $request->getQueryString();
        $newUri = $newPath . ($queryString ? '?' . $queryString : '');
        
        $request->server->set('REQUEST_URI', $newUri);
        
        // PathInfo 재초기화
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
     * 서브도메인 추출
     */
    private function extractSubdomain(string $host, string $masterDomain): ?string
    {
        if ($host === $masterDomain || $host === 'www.' . $masterDomain) {
            return null;
        }
        
        if (str_ends_with($host, '.' . $masterDomain)) {
            $subdomain = str_replace('.' . $masterDomain, '', $host);
            $subdomain = str_replace('www.', '', $subdomain);
            return $subdomain ?: null;
        }
        
        return null;
    }
}

