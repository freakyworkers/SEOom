<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Post;
use Illuminate\Http\Response;

class RssController extends Controller
{
    /**
     * Generate RSS feed
     */
    public function index(Site $site)
    {
        $siteName = $site->getSetting('site_name', $site->name ?? 'SEOom Builder');
        $siteDescription = $site->getSetting('site_description', '');
        $siteUrl = route('home', ['site' => $site->slug]);
        
        // 최근 게시글 20개 가져오기 (RSS 제외 게시판 제외)
        $posts = Post::where('site_id', $site->id)
            ->whereHas('board', function($query) {
                $query->where('exclude_from_rss', false)
                      ->orWhereNull('exclude_from_rss');
            })
            ->with(['user', 'board'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>' . htmlspecialchars($siteName) . '</title>' . "\n";
        $xml .= '    <link>' . htmlspecialchars($siteUrl) . '</link>' . "\n";
        $xml .= '    <description>' . htmlspecialchars($siteDescription ?: $siteName) . '</description>' . "\n";
        $xml .= '    <language>ko</language>' . "\n";
        $xml .= '    <lastBuildDate>' . now()->format('r') . '</lastBuildDate>' . "\n";
        $xml .= '    <pubDate>' . now()->format('r') . '</pubDate>' . "\n";
        
        foreach ($posts as $post) {
            if ($post->board && $post->user) {
                try {
                    $postUrl = route('posts.show', [
                        'site' => $site->slug,
                        'boardSlug' => $post->board->slug,
                        'post' => $post->id
                    ]);
                    
                    $description = strip_tags($post->content ?? '');
                    $description = mb_substr($description, 0, 200) . (mb_strlen($description) > 200 ? '...' : '');
                    
                    $xml .= '    <item>' . "\n";
                    $xml .= '      <title>' . htmlspecialchars($post->title ?? '') . '</title>' . "\n";
                    $xml .= '      <link>' . htmlspecialchars($postUrl) . '</link>' . "\n";
                    $xml .= '      <description>' . htmlspecialchars($description) . '</description>' . "\n";
                    $xml .= '      <author>' . htmlspecialchars($post->user->email ?? 'noreply@example.com') . '</author>' . "\n";
                    $xml .= '      <pubDate>' . ($post->created_at ? $post->created_at->format('r') : now()->format('r')) . '</pubDate>' . "\n";
                    $xml .= '      <guid isPermaLink="true">' . htmlspecialchars($postUrl) . '</guid>' . "\n";
                    $xml .= '      <category>' . htmlspecialchars($post->board->name ?? '') . '</category>' . "\n";
                    $xml .= '    </item>' . "\n";
                } catch (\Exception $e) {
                    // 라우트 생성 실패 시 해당 게시글 스킵
                    continue;
                }
            }
        }
        
        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }
}

