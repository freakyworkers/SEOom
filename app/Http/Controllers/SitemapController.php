<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Board;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate sitemap.xml
     */
    public function index(Site $site)
    {
        $baseUrl = url('/');
        $siteUrl = route('home', ['site' => $site->slug]);
        
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

