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
        
        // 깔끔한 사이트맵 URL 사용
        $sitemapUrl = url('/sitemap.xml');
        
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
        
        // 깔끔한 사이트맵 URL 사용
        $sitemapUrl = url('/sitemap.xml');
        
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
}

