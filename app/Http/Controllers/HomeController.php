<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Post;
use App\Models\Board;
use App\Models\MainWidgetContainer;
use App\Models\CustomPage;
use App\Models\CustomPageWidgetContainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index(Site $site)
    {
        // 최근 게시글 (비밀글 제외)
        $recentPosts = Post::where('site_id', $site->id)
            ->with(['user', 'board'])
            ->whereHas('board', function($boardQuery) {
                // force_secret이 false인 게시판만
                $boardQuery->where(function($bq) {
                    $bq->where('force_secret', false)
                       ->orWhereNull('force_secret');
                });
            })
            ->where(function($q) {
                // enable_secret이 false이거나, is_secret이 false인 게시글만
                $q->whereHas('board', function($boardQuery) {
                    $boardQuery->where(function($bq) {
                        $bq->where('enable_secret', false)
                           ->orWhereNull('enable_secret');
                    });
                })
                ->orWhere(function($q2) {
                    if (Schema::hasColumn('posts', 'is_secret')) {
                        $q2->where('is_secret', false)
                           ->orWhereNull('is_secret');
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $popularBoards = Board::where('site_id', $site->id)
            ->active()
            ->withCount('activePosts')
            ->orderBy('active_posts_count', 'desc')
            ->limit(5)
            ->get();

        // 베스트글 기준 가져오기
        $bestPostCriteria = $site->getSetting('best_post_criteria', 'views');
        
        // 일간 베스트글 (오늘 작성된 게시글 기준)
        $dailyBestPosts = $this->getBestPosts($site->id, $bestPostCriteria, 'daily', 10);
        
        // 주간 베스트글 (최근 7일 작성된 게시글 기준)
        $weeklyBestPosts = $this->getBestPosts($site->id, $bestPostCriteria, 'weekly', 10);

        // 메인 위젯 컨테이너 및 위젯 가져오기
        try {
            $mainWidgetContainers = MainWidgetContainer::where('site_id', $site->id)
                ->with(['widgets' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('order', 'asc');
                }])
                ->orderBy('order', 'asc')
                ->get();
        } catch (\Exception $e) {
            $mainWidgetContainers = collect();
        }

        return view('home', compact('site', 'recentPosts', 'popularBoards', 'dailyBestPosts', 'weeklyBestPosts', 'bestPostCriteria', 'mainWidgetContainers'));
    }

    /**
     * Get best posts based on criteria and period.
     */
    private function getBestPosts($siteId, $criteria, $period, $limit = 10)
    {
        $query = Post::where('site_id', $siteId)
            ->with(['user', 'board']);
        
        // 비밀글 제외 (컬럼이 존재하는 경우에만)
        if (Schema::hasColumn('posts', 'is_secret')) {
            $query->where(function($q) {
                $q->where('is_secret', '=', 0)
                  ->orWhereNull('is_secret');
            });
        }
        
        // 기간 필터링
        if ($period === 'daily') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'weekly') {
            $query->where('created_at', '>=', now()->subDays(7));
        }
        
        // 베스트글 기준에 따라 정렬
        switch ($criteria) {
            case 'likes':
                // 추천수 기준
                if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                    $query->withCount(['likes as likes_count' => function($q) {
                        $q->where('type', 'like');
                    }])
                    ->orderBy('likes_count', 'desc');
                } else {
                    $query->orderBy('created_at', 'desc');
                }
                break;
            case 'comments':
                // 댓글수 기준
                $query->withCount('comments')
                      ->orderBy('comments_count', 'desc');
                break;
            case 'views':
            default:
                // 조회수 기준
                $query->orderBy('views', 'desc');
                break;
        }
        
        // 동일한 값일 경우 최신순
        $query->orderBy('created_at', 'desc');
        
        return $query->limit($limit)->get();
    }

    /**
     * Display a custom page.
     */
    public function showCustomPage(Site $site, $slug)
    {
        // URL 디코딩 (한글 슬러그 처리)
        $decodedSlug = urldecode($slug);
        
        $customPage = CustomPage::where('site_id', $site->id)
            ->where('slug', $decodedSlug)
            ->firstOrFail();

        // 커스텀 페이지 위젯 컨테이너 및 위젯 가져오기
        try {
            $containers = CustomPageWidgetContainer::where('custom_page_id', $customPage->id)
                ->with(['widgets' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('order', 'asc');
                }])
                ->orderBy('order', 'asc')
                ->get();
        } catch (\Exception $e) {
            $containers = collect();
        }

        return view('custom-page', compact('site', 'customPage', 'containers'));
    }
}



