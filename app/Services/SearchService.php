<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Board;
use Illuminate\Support\Facades\DB;

class SearchService
{
    /**
     * Search posts by keyword.
     */
    public function searchPosts(int $siteId, string $keyword, ?int $boardId = null, ?string $sortBy = 'latest', ?string $dateFilter = null, int $perPage = 15)
    {
        $query = Post::where('site_id', $siteId)
            ->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('content', 'like', "%{$keyword}%");
            })
            ->with(['user', 'board', 'comments']);

        if ($boardId) {
            $query->where('board_id', $boardId);
        }

        // 날짜 필터 적용
        if ($dateFilter) {
            $dateFilterMap = [
                'today' => now()->startOfDay(),
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
            ];
            
            if (isset($dateFilterMap[$dateFilter])) {
                $query->where('created_at', '>=', $dateFilterMap[$dateFilter]);
            }
        }

        // 정렬 옵션 적용
        switch ($sortBy) {
            case 'views':
                $query->orderBy('views', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'comments':
                $query->withCount('comments')->orderBy('comments_count', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'latest':
            default:
                $query->ordered();
                break;
        }

        return $query->paginate($perPage);
    }

    /**
     * Search users by keyword.
     */
    public function searchUsers(int $siteId, string $keyword, int $perPage = 15)
    {
        return User::where('site_id', $siteId)
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            })
            ->withCount('posts')
            ->withCount('comments')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search boards by keyword.
     */
    public function searchBoards(int $siteId, string $keyword, int $perPage = 15)
    {
        return Board::where('site_id', $siteId)
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->withCount('posts')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Global search across all content types.
     */
    public function globalSearch(int $siteId, string $keyword, ?string $sortBy = 'latest', ?string $dateFilter = null, int $perPage = 10)
    {
        $results = [
            'posts' => $this->searchPosts($siteId, $keyword, null, $sortBy, $dateFilter, $perPage),
            'users' => $this->searchUsers($siteId, $keyword, $perPage),
            'boards' => $this->searchBoards($siteId, $keyword, $perPage),
        ];

        return $results;
    }

    /**
     * Highlight keywords in text.
     */
    public function highlightKeywords(string $text, string $keyword, int $maxLength = 200): string
    {
        // HTML 태그 제거
        $text = strip_tags($text);
        
        // 키워드 위치 찾기
        $keywordLower = mb_strtolower($keyword);
        $textLower = mb_strtolower($text);
        $pos = mb_strpos($textLower, $keywordLower);
        
        if ($pos === false) {
            // 키워드가 없으면 앞부분만 반환
            return mb_strlen($text) > $maxLength ? mb_substr($text, 0, $maxLength) . '...' : $text;
        }
        
        // 키워드 주변 텍스트 추출
        $start = max(0, $pos - ($maxLength / 2));
        $length = min(mb_strlen($text), $maxLength);
        $extracted = mb_substr($text, $start, $length);
        
        // 앞뒤에 ... 추가
        if ($start > 0) {
            $extracted = '...' . $extracted;
        }
        if ($start + $length < mb_strlen($text)) {
            $extracted = $extracted . '...';
        }
        
        // 키워드 하이라이트
        $highlighted = preg_replace('/(' . preg_quote($keyword, '/') . ')/iu', '<mark>$1</mark>', $extracted);
        
        return $highlighted;
    }
}

