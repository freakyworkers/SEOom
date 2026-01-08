<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\JsonResponse;

class CleanSiteUrls
{
    /**
     * Handle an incoming request.
     * 도메인 기반 접근 시 응답 HTML에서 /site/{slug}/ URL을 클린 URL로 변환합니다.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // 도메인 기반 접근이 아니면 변환하지 않음
        if (!$request->attributes->get('domain_based_access')) {
            return $response;
        }
        
        $site = $request->attributes->get('site');
        if (!$site) {
            return $response;
        }
        
        // HTML 응답만 처리 (JSON, 파일 다운로드 등은 제외)
        if (!$this->shouldTransformResponse($response)) {
            return $response;
        }
        
        $content = $response->getContent();
        if (empty($content)) {
            return $response;
        }
        
        // /site/{slug}/ 패턴을 / 로 변환
        $transformedContent = $this->transformUrls($content, $site->slug);
        
        $response->setContent($transformedContent);
        
        return $response;
    }
    
    /**
     * 응답을 변환해야 하는지 확인
     */
    protected function shouldTransformResponse(Response $response): bool
    {
        // JSON 응답은 제외
        if ($response instanceof JsonResponse) {
            return false;
        }
        
        // Content-Type 확인
        $contentType = $response->headers->get('Content-Type', '');
        
        // HTML 응답만 처리
        if (str_contains($contentType, 'text/html') || empty($contentType)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * HTML 콘텐츠 내 URL 변환
     */
    protected function transformUrls(string $content, string $siteSlug): string
    {
        // 이스케이프된 슬러그 (정규식용)
        $escapedSlug = preg_quote($siteSlug, '/');
        
        // href, src, action, data-* 속성 내 URL 변환
        // 예: href="/site/master/Feature" → href="/Feature"
        // 예: href="https://example.com/site/master/Feature" → href="https://example.com/Feature"
        
        // 상대 경로: /site/{slug}/... → /...
        $content = preg_replace(
            '/(["\'])\/site\/' . $escapedSlug . '(\/[^"\']*)?(["\'])/i',
            '$1$2$3',
            $content
        );
        
        // 절대 경로 (http/https): .../site/{slug}/... → .../...
        $content = preg_replace(
            '/(https?:\/\/[^"\']+)\/site\/' . $escapedSlug . '(\/[^"\']*)?/i',
            '$1$2',
            $content
        );
        
        // JavaScript 내 URL 문자열도 처리
        // 예: url: '/site/master/api/...' → url: '/api/...'
        $content = preg_replace(
            '/([\'"`])\/site\/' . $escapedSlug . '(\/[^\'"`]*)?([\'"`])/i',
            '$1$2$3',
            $content
        );
        
        return $content;
    }
}



