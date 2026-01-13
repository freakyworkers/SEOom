<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Board;
use App\Models\Post;
use App\Models\Site;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 게시판 뷰어 위젯용 게시글 목록 API
Route::get('/boards/{boardId}/posts', function (Request $request, $boardId) {
    $siteSlug = $request->query('site');
    $topicId = $request->query('topic');
    
    $site = Site::where('slug', $siteSlug)->first();
    if (!$site) {
        return response()->json(['success' => false, 'message' => 'Site not found'], 404);
    }
    
    $board = Board::where('id', $boardId)->where('site_id', $site->id)->first();
    if (!$board) {
        return response()->json(['success' => false, 'message' => 'Board not found'], 404);
    }
    
    $query = Post::where('site_id', $site->id)
        ->where('board_id', $board->id)
        ->with(['user', 'topics', 'comments'])
        ->where(function($q) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                $q->where('is_secret', '=', 0)->orWhereNull('is_secret');
            }
        })
        ->orderBy('created_at', 'desc');
    
    if ($topicId) {
        $query->whereHas('topics', function($q) use ($topicId) {
            $q->where('topics.id', $topicId);
        });
    }
    
    $perPage = $board->posts_per_page ?? 20;
    $posts = $query->limit($perPage)->get();
    
    // 컬럼 클래스 계산
    $mobileCols = $board->pinterest_columns_mobile ?? 2;
    $tabletCols = $board->pinterest_columns_tablet ?? 3;
    $desktopCols = $board->pinterest_columns_desktop ?? 4;
    $largeCols = $board->pinterest_columns_large ?? 6;
    
    $colClass = 'col-' . (12 / $mobileCols);
    if ($tabletCols > 0) {
        $colClass .= ' col-md-' . (12 / $tabletCols);
    }
    if ($desktopCols > 0) {
        $colClass .= ' col-lg-' . (12 / $desktopCols);
    }
    if ($largeCols > 0) {
        $colClass .= ' col-xl-' . (12 / $largeCols);
    }
    
    $postsData = $posts->map(function($post) {
        // 첫 번째 이미지 추출
        $firstImage = null;
        if (!$post->thumbnail_path && $post->content) {
            preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->content, $matches);
            $firstImage = $matches[1] ?? null;
        }
        
        return [
            'id' => $post->id,
            'title' => $post->title,
            'thumbnail_path' => $post->thumbnail_path,
            'first_image' => $firstImage,
            'author' => $post->user ? $post->user->nickname : '익명',
            'created_at' => $post->created_at->format('Y-m-d'),
            'like_count' => $post->like_count ?? 0,
            'comment_count' => $post->comments->count(),
            'topics' => $post->topics->map(function($topic) {
                return [
                    'id' => $topic->id,
                    'name' => $topic->name,
                    'color' => $topic->color,
                ];
            }),
        ];
    });
    
    return response()->json([
        'success' => true,
        'posts' => $postsData,
        'board_type' => $board->type,
        'show_title' => $board->pinterest_show_title ?? false,
        'title_align' => $board->pinterest_title_align ?? 'left',
        'col_class' => $colClass,
    ]);
});













