<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Board;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate sitemap.xml
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
        
        $baseUrl = url('/');
        
        // 도메인 기반 접근인 경우 직접 URL 사용, 아니면 route 사용
        if ($site->domain && $request->getHost() === $site->domain) {
            $siteUrl = url('/');
        } else {
            $siteUrl = route('home', ['site' => $site->slug]);
        }
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // 홈페이지
        $xml .= $this->urlElement($siteUrl, now(), '1.0');
        
        // 게시판 목록
        $boards = Board::where('site_id', $site->id)
            ->where('is_active', true)
            ->get();
        
        foreach ($boards as $board) {
            $boardUrl = route('boards.show', ['site' => $site->slug, 'slug' => $board->slug]);
            $xml .= $this->urlElement($boardUrl, $board->updated_at ?? $board->created_at, '0.8');
        }
        
        // 게시글 목록 (최근 1000개)
        $posts = Post::where('site_id', $site->id)
            ->with('board')
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();
        
        foreach ($posts as $post) {
            if ($post->board) {
                try {
                    $postUrl = route('posts.show', [
                        'site' => $site->slug,
                        'boardSlug' => $post->board->slug,
                        'post' => $post->id
                    ]);
                    $lastmod = $post->updated_at ?? $post->created_at ?? now();
                    $xml .= $this->urlElement($postUrl, $lastmod, '0.6');
                } catch (\Exception $e) {
                    // 라우트 생성 실패 시 해당 게시글 스킵
                    continue;
                }
            }
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
    
    /**
     * Generate URL element for sitemap
     */
    private function urlElement($url, $lastmod, $priority)
    {
        if ($lastmod instanceof \Carbon\Carbon || $lastmod instanceof \DateTime) {
            $lastmodFormatted = $lastmod->format('Y-m-d\TH:i:s+00:00');
        } else {
            $lastmodFormatted = now()->format('Y-m-d\TH:i:s+00:00');
        }
        return "  <url>\n" .
               "    <loc>" . htmlspecialchars($url) . "</loc>\n" .
               "    <lastmod>{$lastmodFormatted}</lastmod>\n" .
               "    <priority>{$priority}</priority>\n" .
               "  </url>\n";
    }
}

