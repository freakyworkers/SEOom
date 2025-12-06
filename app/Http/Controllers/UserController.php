<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user profile.
     */
    public function profile(Site $site)
    {
        $user = auth()->user();
        
        $stats = [
            'posts' => $user->posts()->where('site_id', $site->id)->count(),
            'comments' => $user->comments()->whereHas('post', function($q) use ($site) {
                $q->where('site_id', $site->id);
            })->count(),
            'messages' => $user->messages_count, // Placeholder for future implementation
        ];

        // 마스터 사이트인 경우 사용자가 만든 사이트 목록 가져오기
        $userSites = collect([]);
        if ($site->isMasterSite()) {
            $userSites = \App\Models\Site::where('created_by', $user->id)
                ->where('is_master_site', false)
                ->with(['subscription.plan'])
                ->orderBy('created_at', 'desc')
                ->get();

            // 각 사이트의 구독 정보 및 서버 구독 정보 가져오기
            foreach ($userSites as $userSite) {
                if (!$userSite->subscription) {
                    $subscription = \App\Models\Subscription::where('site_id', $userSite->id)
                        ->where('user_id', $user->id)
                        ->with('plan')
                        ->first();
                    $userSite->subscription = $subscription;
                }
                
                // 서버 용량 구독 정보 가져오기
                $serverSubscription = \App\Models\Subscription::where('site_id', $userSite->id)
                    ->where('user_id', $user->id)
                    ->whereHas('plan', function($query) {
                        $query->where('type', 'server');
                    })
                    ->with('plan')
                    ->first();
                
                // 실제 저장용량 계산 및 업데이트
                $storageUsedMB = $this->calculateStorageUsage($userSite);
                if ($userSite->storage_used_mb != $storageUsedMB) {
                    $userSite->update(['storage_used_mb' => $storageUsedMB]);
                    $userSite->refresh();
                }
                
                // 업데이트 후 관계 설정
                $userSite->serverSubscription = $serverSubscription;
            }
        }

        return view('users.profile', compact('site', 'user', 'stats', 'userSites'));
    }

    /**
     * Get point history for the authenticated user.
     */
    public function pointHistory(Site $site, Request $request)
    {
        $user = auth()->user();
        
        // TODO: 포인트 로그 테이블이 생성되면 실제 데이터를 가져오도록 수정
        // 현재는 기본 구조만 제공
        $history = [];
        
        // 포인트 교환 내역 (임시로 사용)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('point_exchange_applications')) {
                $applications = \App\Models\PointExchangeApplication::where('user_id', $user->id)
                    ->whereHas('product', function($q) use ($site) {
                        $q->where('site_id', $site->id);
                    })
                    ->with('product')
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get();
                
                foreach ($applications as $app) {
                    $productName = $app->product->item_name ?? $app->product->item_content ?? '포인트 교환';
                    $history[] = [
                        'date' => $app->created_at->format('Y-m-d H:i'),
                        'description' => $productName . ' 교환',
                        'points' => -$app->points, // 차감
                        'balance' => $user->points ?? 0, // TODO: 실제 잔액 계산 필요
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Point history error: ' . $e->getMessage());
        }
        
        // 출석 체크 포인트 내역 (임시로 사용)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('attendances')) {
                $attendances = \App\Models\Attendance::where('user_id', $user->id)
                    ->where('site_id', $site->id)
                    ->where('points_earned', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get();
                
                foreach ($attendances as $attendance) {
                    $history[] = [
                        'date' => $attendance->created_at->format('Y-m-d H:i'),
                        'description' => '출석 체크',
                        'points' => $attendance->points_earned,
                        'balance' => $user->points ?? 0, // TODO: 실제 잔액 계산 필요
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Attendance point history error: ' . $e->getMessage());
        }
        
        // 이벤트 당첨 포인트 내역
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('event_participants')) {
                $eventParticipants = \App\Models\EventParticipant::where('user_id', $user->id)
                    ->where('points_awarded', '>', 0)
                    ->whereHas('post', function($q) use ($site) {
                        $q->where('site_id', $site->id);
                    })
                    ->with('post')
                    ->orderBy('updated_at', 'desc')
                    ->limit(50)
                    ->get();
                
                foreach ($eventParticipants as $participant) {
                    $post = $participant->post;
                    $eventTitle = $post ? $post->title : '이벤트';
                    $history[] = [
                        'date' => $participant->updated_at->format('Y-m-d H:i'),
                        'description' => $eventTitle . ' 당첨',
                        'points' => $participant->points_awarded,
                        'balance' => $user->points ?? 0, // TODO: 실제 잔액 계산 필요
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Event participant point history error: ' . $e->getMessage());
        }
        
        // 날짜순으로 정렬 (최신순)
        usort($history, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        // 최대 50개만 반환
        $history = array_slice($history, 0, 50);
        
        // 항상 JSON 응답 반환 (모달에서 사용)
        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    /**
     * Display saved posts for the authenticated user.
     */
    public function savedPosts(Site $site, Request $request)
    {
        $user = auth()->user();
        
        if (!\Illuminate\Support\Facades\Schema::hasTable('saved_posts')) {
            $savedPosts = collect([]);
        } else {
            $savedPosts = \App\Models\SavedPost::where('user_id', $user->id)
                ->where('site_id', $site->id)
                ->with(['post.board', 'post.user'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return view('users.saved-posts', compact('site', 'user', 'savedPosts'));
    }

    /**
     * Display user's posts.
     */
    public function myPosts(Site $site, Request $request)
    {
        $user = auth()->user();
        
        $query = \App\Models\Post::where('user_id', $user->id)
            ->where('site_id', $site->id)
            ->whereNull('reply_to') // 답글 제외
            ->with(['board', 'user'])
            ->orderBy('created_at', 'desc');
        
        // 게시판 필터
        if ($request->filled('board_id')) {
            $query->where('board_id', $request->board_id);
        }
        
        $posts = $query->paginate(15)->withQueryString();
        
        // 게시판 목록 (필터용)
        $boards = \App\Models\Board::where('site_id', $site->id)
            ->ordered()
            ->get();

        return view('users.my-posts', compact('site', 'user', 'posts', 'boards'));
    }

    /**
     * Display user's comments.
     */
    public function myComments(Site $site, Request $request)
    {
        $user = auth()->user();
        
        $query = \App\Models\Comment::where('user_id', $user->id)
            ->where('site_id', $site->id)
            ->with(['post.board', 'post.user'])
            ->orderBy('created_at', 'desc');
        
        $comments = $query->paginate(15)->withQueryString();

        return view('users.my-comments', compact('site', 'user', 'comments'));
    }

    /**
     * Calculate actual storage usage for a site (MB).
     */
    protected function calculateStorageUsage(Site $site): int
    {
        $totalSize = 0;
        $basePath = storage_path('app/public');
        
        try {
            // 1. Post Attachments - 데이터베이스에서 파일 크기 합계
            $postAttachmentsSize = \App\Models\PostAttachment::whereHas('post', function($query) use ($site) {
                $query->where('site_id', $site->id);
            })->sum('file_size');
            $totalSize += $postAttachmentsSize;
            
            // 2. Banners - 이미지 파일 크기 계산
            $banners = \App\Models\Banner::where('site_id', $site->id)
                ->whereNotNull('image_path')
                ->get();
            foreach ($banners as $banner) {
                $filePath = $basePath . '/' . $banner->image_path;
                if (file_exists($filePath) && is_file($filePath)) {
                    $totalSize += filesize($filePath);
                }
            }
            
            // 3. Popups - 이미지 파일 크기 계산
            $popups = \App\Models\Popup::where('site_id', $site->id)
                ->whereNotNull('image_path')
                ->get();
            foreach ($popups as $popup) {
                $filePath = $basePath . '/' . $popup->image_path;
                if (file_exists($filePath) && is_file($filePath)) {
                    $totalSize += filesize($filePath);
                }
            }
            
            // 4. User Avatars - 해당 사이트의 사용자 아바타 크기 계산
            $siteUserIds = $site->users()->pluck('id')->toArray();
            if (!empty($siteUserIds)) {
                foreach ($siteUserIds as $userId) {
                    $avatarPath = $basePath . '/avatars/' . $userId;
                    if (is_dir($avatarPath)) {
                        $totalSize += $this->getDirectorySize($avatarPath);
                    }
                }
            }
            
            // 5. Site-specific upload directories
            $siteUploadPath = $basePath . '/uploads/sites/' . $site->id;
            if (is_dir($siteUploadPath)) {
                $totalSize += $this->getDirectorySize($siteUploadPath);
            }
            
            // 6. Banner directories for this site
            $bannerPath = $basePath . '/banners/' . $site->id;
            if (is_dir($bannerPath)) {
                $totalSize += $this->getDirectorySize($bannerPath);
            }
            
            // 7. Attachments for posts of this site (파일 시스템에서도 확인)
            $sitePostIds = $site->posts()->pluck('id')->toArray();
            if (!empty($sitePostIds)) {
                foreach ($sitePostIds as $postId) {
                    $attachmentPath = $basePath . '/attachments/' . $postId;
                    if (is_dir($attachmentPath)) {
                        $totalSize += $this->getDirectorySize($attachmentPath);
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error calculating storage usage for site ' . $site->id . ': ' . $e->getMessage());
        }
        
        // Convert bytes to MB
        return (int) round($totalSize / 1024 / 1024);
    }

    /**
     * Get directory size recursively (bytes).
     */
    protected function getDirectorySize(string $directory): int
    {
        $size = 0;
        
        if (!is_dir($directory)) {
            return 0;
        }
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            Log::error('Error calculating directory size for ' . $directory . ': ' . $e->getMessage());
        }
        
        return $size;
    }
}
