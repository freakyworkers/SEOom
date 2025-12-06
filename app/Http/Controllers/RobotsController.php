<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt
     */
    public function index(Site $site)
    {
        $customRobotsTxt = $site->getSetting('robots_txt', '');
        $sitemapUrl = route('sitemap', ['site' => $site->slug]);
        
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
    public function download(Site $site)
    {
        $customRobotsTxt = $site->getSetting('robots_txt', '');
        $sitemapUrl = route('sitemap', ['site' => $site->slug]);
        
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

