<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt
     * 도메인 기반으로 사이트를 자동으로 찾습니다.
     */
    public function index(Request $request)
    {
        // 도메인 기반으로 사이트 찾기
        $site = $request->attributes->get('site');
        
        // 사이트를 찾을 수 없으면 404
        if (!$site) {
            abort(404, 'Site not found');
        }
        
        $customRobotsTxt = $site->getSetting('robots_txt', '');
        
        // 사이트맵 URL 생성
        $sitemapUrl = $this->getSitemapUrl($site);
        
        // 사용자가 커스텀 robots.txt를 입력한 경우 사용
        if (!empty($customRobotsTxt)) {
            $content = $customRobotsTxt;
            
            // 사이트맵 URL이 포함되어 있지 않으면 추가
            if (stripos($content, 'Sitemap:') === false) {
                $content .= "\n\nSitemap: {$sitemapUrl}\n";
            }
        } else {
            // 기본값 생성
            $content = "User-agent: *\n";
            $content .= "Allow: /\n\n";
            $content .= "Sitemap: {$sitemapUrl}\n";
        }
        
        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
    
    /**
     * Download robots.txt as file
     */
    public function download(Request $request)
    {
        // 도메인 기반으로 사이트 찾기
        $site = $request->attributes->get('site');
        
        // 사이트를 찾을 수 없으면 404
        if (!$site) {
            abort(404, 'Site not found');
        }
        
        $customRobotsTxt = $site->getSetting('robots_txt', '');
        
        // 사이트맵 URL 생성
        $sitemapUrl = $this->getSitemapUrl($site);
        
        // 사용자가 커스텀 robots.txt를 입력한 경우 사용
        if (!empty($customRobotsTxt)) {
            $content = $customRobotsTxt;
            
            // 사이트맵 URL이 포함되어 있지 않으면 추가
            if (stripos($content, 'Sitemap:') === false) {
                $content .= "\n\nSitemap: {$sitemapUrl}\n";
            }
        } else {
            // 기본값 생성
            $content = "User-agent: *\n";
            $content .= "Allow: /\n\n";
            $content .= "Sitemap: {$sitemapUrl}\n";
        }
        
        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'robots.txt', [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
    
    /**
     * 사이트에 맞는 사이트맵 URL 생성
     * - 커스텀 도메인 사용 시: https://custom-domain.com/sitemap.xml
     * - 서브도메인 사용 시: https://slug.seoomweb.com/sitemap.xml
     */
    private function getSitemapUrl(Site $site): string
    {
        // 커스텀 도메인이 있는 경우
        $customDomain = $site->getSetting('domain', '');
        if (!empty($customDomain)) {
            // https:// 또는 http://가 없으면 추가
            if (!preg_match('/^https?:\/\//', $customDomain)) {
                $customDomain = 'https://' . $customDomain;
            }
            return rtrim($customDomain, '/') . '/sitemap.xml';
        }
        
        // 커스텀 도메인이 없는 경우 서브도메인 형식 사용
        // https://slug.seoomweb.com/sitemap.xml
        $baseHost = config('app.base_domain', 'seoomweb.com');
        return 'https://' . $site->slug . '.' . $baseHost . '/sitemap.xml';
    }
}

