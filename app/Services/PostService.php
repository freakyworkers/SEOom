<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Board;
use App\Models\PostAttachment;
use App\Models\User;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService
{
    /**
     * Get posts for a board.
     */
    public function getPostsByBoard($boardId, $perPage = 15, $randomOrder = false, $topicId = null, $siteId = null, $searchKeyword = null, $searchType = 'title_content')
    {
        $eagerLoad = ['user', 'board', 'comments', 'topics', 'replies.user', 'replies.board'];
        
        // post_likes 테이블이 존재하는 경우에만 likes 관계 eager load
        if (Schema::hasTable('post_likes')) {
            $eagerLoad[] = 'likes';
        }
        
        $query = Post::where('board_id', $boardId)
            ->whereNull('reply_to') // 답글은 제외하고 원본 게시글만 표시
            ->with($eagerLoad);
        
        // 1:1 게시판인 경우 작성자 필터링 (관리자는 모든 글 확인 가능)
        $board = Board::where('id', $boardId)->first();
        if ($board && $board->type === 'one_on_one' && auth()->check() && !auth()->user()->canManage()) {
            // 일반 사용자는 자신이 작성한 글만 확인 가능
            $query->where('user_id', auth()->id());
        }
        
        // 주제 필터링
        if ($topicId) {
            $query->whereHas('topics', function($q) use ($topicId) {
                $q->where('topics.id', $topicId);
            });
        }
        
        // 검색 키워드 필터링
        if ($searchKeyword) {
            $query->where(function($q) use ($searchKeyword, $searchType) {
                switch ($searchType) {
                    case 'author':
                        // 작성자 검색
                        $q->whereHas('user', function($userQuery) use ($searchKeyword) {
                            $userQuery->where('name', 'like', '%' . $searchKeyword . '%');
                        });
                        break;
                    case 'title_content':
                    default:
                        // 제목 또는 내용 검색
                        $q->where('title', 'like', '%' . $searchKeyword . '%')
                          ->orWhere('content', 'like', '%' . $searchKeyword . '%');
                        break;
                }
            });
        }
        
        // 이벤트 게시판인 경우 종료된 이벤트를 리스트 끝으로 이동
        // $board가 null일 수 있으므로 다시 확인 (캐시 문제 방지를 위해 직접 쿼리 사용)
        if (!$board) {
            $board = Board::where('id', $boardId)->first();
        }
        $isEventBoard = $board && $board->type === 'event';
        
        if ($randomOrder) {
            if ($isEventBoard) {
                // 이벤트 게시판: 고정 -> 공지 -> 진행중/예정 -> 종료 순으로 정렬
                // 종료된 이벤트는 리스트의 가장 뒤로 이동
                // 종료 판단: event_is_ended = 1 이거나 event_end_date가 오늘보다 이전 (당일 이벤트는 종료되지 않음)
                $query->orderByRaw('CASE 
                    WHEN (COALESCE(event_is_ended, 0) = 1 OR (event_end_date IS NOT NULL AND DATE(event_end_date) < DATE(NOW()))) THEN 3
                    WHEN is_pinned = 1 THEN 0
                    WHEN is_notice = 1 THEN 1
                    ELSE 2
                END ASC')
                ->orderByRaw('RAND()');
            } else {
                // 공지/고정 게시글은 항상 상단에 고정
                $query->orderByRaw('CASE WHEN is_pinned = 1 THEN 0 WHEN is_notice = 1 THEN 1 ELSE 2 END')
                      ->orderByRaw('RAND()');
            }
        } else {
            if ($isEventBoard) {
                // 이벤트 게시판: PHP에서 정렬하므로 SQL에서는 기본 정렬만
                $query->orderBy('created_at', 'desc');
            } else {
                // 공지/고정 게시글은 항상 상단에 고정, 그 외는 작성일 순
                $query->orderByRaw('CASE WHEN is_pinned = 1 THEN 0 WHEN is_notice = 1 THEN 1 ELSE 2 END')
                      ->orderBy('created_at', 'desc');
            }
        }
        
        $posts = $query->paginate($perPage);
        
        // 이벤트 게시판인 경우 종료된 이벤트를 리스트 끝으로 이동 (PHP에서 정렬)
        // 캐시 문제 방지를 위해 직접 쿼리로 최신 데이터 가져오기
        $boardForSort = Board::where('id', $boardId)->first();
        $isEventBoardForSort = $boardForSort && $boardForSort->type === 'event';
        
        // 항상 이벤트 게시판인지 확인하고 정렬 실행
        if ($isEventBoardForSort && !$randomOrder) {
            \Log::info('Event board sorting started', [
                'boardId' => $boardId,
                'boardType' => $boardForSort->type ?? 'null',
                'randomOrder' => $randomOrder,
                'itemsCount' => count($posts->items())
            ]);
            $items = $posts->items();
            $now = now();
            
            // 진행중과 종료된 이벤트 분리
            $ongoingItems = [];
            $endedItems = [];
            
            foreach ($items as $item) {
                $isEnded = false;
                
                // 수동 종료 체크 (다양한 형식 지원: boolean, int, string)
                if ($item->event_is_ended === true || $item->event_is_ended === 1 || $item->event_is_ended === '1') {
                    $isEnded = true;
                }
                // 날짜 기반 자동 종료 체크 (당일 이벤트는 종료되지 않음)
                elseif ($item->event_end_date && !$item->event_end_undecided) {
                    try {
                        // event_end_date가 이미 Carbon 인스턴스인 경우와 문자열인 경우 모두 처리
                        $endDate = $item->event_end_date instanceof \Carbon\Carbon 
                            ? $item->event_end_date->copy()->startOfDay()
                            : \Carbon\Carbon::parse($item->event_end_date)->startOfDay();
                        $today = $now->copy()->startOfDay();
                        // 오늘이 종료일보다 큰 경우에만 종료 (같거나 작으면 진행중)
                        // 예: 오늘이 11월 25일이고 종료일이 11월 25일이면 종료되지 않음
                        if ($today->isAfter($endDate)) {
                            $isEnded = true;
                        }
                    } catch (\Exception $e) {
                        // 날짜 파싱 실패 시 진행중으로 처리
                        \Log::warning('Event end date parsing failed', [
                            'post_id' => $item->id,
                            'event_end_date' => $item->event_end_date,
                            'error' => $e->getMessage()
                        ]);
                        $isEnded = false;
                    }
                }
                
                if ($isEnded) {
                    $endedItems[] = $item;
                } else {
                    $ongoingItems[] = $item;
                }
            }
            
            // 진행중인 이벤트만 정렬 (고정 -> 공지 -> 일반 -> 작성일 내림차순)
            if (count($ongoingItems) > 0) {
                usort($ongoingItems, function($a, $b) {
                    // 고정 우선
                    if ($a->is_pinned && !$b->is_pinned) return -1;
                    if (!$a->is_pinned && $b->is_pinned) return 1;
                    
                    // 공지 우선
                    if ($a->is_notice && !$b->is_notice) return -1;
                    if (!$a->is_notice && $b->is_notice) return 1;
                    
                    // 같은 우선순위면 created_at 내림차순
                    return $b->created_at->timestamp <=> $a->created_at->timestamp;
                });
            }
            
            // 종료된 이벤트는 정렬하지 않고 그대로 유지 (작성날짜, 이벤트 기간과 상관없이)
            
            // 진행중 이벤트 + 종료된 이벤트 순서로 합치기
            $sortedItems = array_merge($ongoingItems, $endedItems);
            
            \Log::info('Event board sorting completed', [
                'boardId' => $boardId,
                'ongoingCount' => count($ongoingItems),
                'endedCount' => count($endedItems),
                'totalCount' => count($sortedItems)
            ]);
            
            // Collection으로 변환
            $sortedCollection = collect($sortedItems);
            
            // 새로운 Paginator 인스턴스 생성
            $posts = new LengthAwarePaginator(
                $sortedCollection,
                $posts->total(),
                $posts->perPage(),
                $posts->currentPage(),
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
            $posts->appends(request()->query());
        }
        
        return $posts;
    }

    /**
     * Get a post by ID.
     */
    public function getPost($postId)
    {
        $eagerLoad = ['user', 'board', 'comments.user', 'comments.post.user', 'comments.post.board', 'comments.replies.user', 'comments.replies.post.user', 'comments.replies.post.board', 'attachments', 'topics', 'eventOptions', 'eventParticipants.user', 'eventParticipants.eventOption'];
        
        // post_likes 테이블이 존재하는 경우에만 likes 관계 eager load
        if (Schema::hasTable('post_likes')) {
            $eagerLoad[] = 'likes';
        }
        
        return Post::with($eagerLoad)
            ->findOrFail($postId);
    }

    /**
     * Create a new post.
     */
    public function create(array $data, $userId, $siteId, array $attachments = [])
    {
        $post = Post::create([
            'site_id' => $siteId,
            'board_id' => $data['board_id'],
            'user_id' => $userId,
            'title' => $data['title'],
            'content' => $data['content'],
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'site_name' => $data['site_name'] ?? null,
            'code' => $data['code'] ?? null,
            'link' => $data['link'] ?? null,
            'bookmark_items' => $data['bookmark_items'] ?? null,
            'is_notice' => $data['is_notice'] ?? false,
            'is_pinned' => $data['is_pinned'] ?? false,
            'is_secret' => $data['is_secret'] ?? false,
            'adoption_points' => $data['adoption_points'] ?? 0,
            'reply_to' => $data['reply_to'] ?? null,
            'event_type' => $data['event_type'] ?? null,
            'event_start_date' => $data['event_start_date'] ?? null,
            'event_end_date' => $data['event_end_date'] ?? null,
            'event_end_undecided' => $data['event_end_undecided'] ?? false,
            'event_is_ended' => $data['event_is_ended'] ?? false,
        ]);

        // Save attachments
        foreach ($attachments as $index => $attachment) {
            PostAttachment::create([
                'post_id' => $post->id,
                'original_name' => $attachment['original_name'],
                'file_name' => $attachment['file_name'],
                'file_path' => $attachment['file_path'],
                'mime_type' => $attachment['mime_type'],
                'file_size' => $attachment['file_size'],
                'order' => $index,
            ]);
        }

        // 주제 연결
        if (isset($data['topic_ids']) && is_array($data['topic_ids'])) {
            $topicIds = array_filter($data['topic_ids']); // 빈 값 제거
            if (!empty($topicIds)) {
                $post->topics()->sync($topicIds);
            }
        }

        // 포인트 지급 (게시글 쓰기)
        $board = Board::find($data['board_id']);
        if ($board && $board->write_points != 0) {
            $user = User::find($userId);
            if ($user) {
                $user->addPoints($board->write_points);
            }
        }

        // 새 게시글 알림 메일 발송
        $post->load(['board', 'user']);
        $this->sendNewPostNotification($post);

        return $post;
    }

    /**
     * Send admin notification email for new post.
     */
    private function sendNewPostNotification(Post $post)
    {
        $site = Site::find($post->site_id);
        if (!$site) {
            return;
        }

        // 알림 설정 확인
        if (!$site->getSetting('notify_new_post', false)) {
            return;
        }

        $adminEmail = $site->getSetting('admin_notification_email', '');
        if (!$adminEmail) {
            return;
        }

        // 선택된 게시판 확인
        $notifyPostBoardsRaw = $site->getSetting('notify_post_boards', '[]');
        $notifyPostBoards = is_array($notifyPostBoardsRaw) ? $notifyPostBoardsRaw : (json_decode($notifyPostBoardsRaw, true) ?? []);
        if (!empty($notifyPostBoards) && !in_array($post->board_id, $notifyPostBoards)) {
            return;
        }

        // 메일 설정 가져오기
        $mailer = $site->getSetting('mail_mailer', 'smtp');
        if (empty($mailer)) {
            $mailer = 'smtp';
        }

        $mailUsername = $site->getSetting('mail_username', '');
        $mailConfig = [
            'mailer' => $mailer,
            'host' => $site->getSetting('mail_host', 'smtp.gmail.com'),
            'port' => (int)$site->getSetting('mail_port', '587'),
            'username' => $mailUsername,
            'password' => $site->getSetting('mail_password', ''),
            'encryption' => $site->getSetting('mail_encryption', 'tls'),
            'from' => [
                'address' => $mailUsername,
                'name' => $site->getSetting('mail_from_name', $site->name),
            ],
        ];

        // Config::set을 사용하여 메일 설정 변경
        Config::set('mail.default', $mailConfig['mailer']);
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $mailConfig['host']);
        Config::set('mail.mailers.smtp.port', $mailConfig['port']);
        Config::set('mail.mailers.smtp.encryption', $mailConfig['encryption']);
        Config::set('mail.mailers.smtp.username', $mailConfig['username']);
        Config::set('mail.mailers.smtp.password', $mailConfig['password']);
        Config::set('mail.mailers.smtp.timeout', null);
        Config::set('mail.mailers.smtp.auth_mode', null);
        Config::set('mail.from.address', $mailConfig['from']['address']);
        Config::set('mail.from.name', $mailConfig['from']['name']);

        // 메일러 재설정
        app('mail.manager')->forgetMailers();

        try {
            Mail::to($adminEmail)->send(new \App\Mail\NewPostNotificationMail($site, $post));
        } catch (\Exception $e) {
            \Log::error('New post notification mail failed: ' . $e->getMessage());
        }
    }

    /**
     * Update a post.
     */
    public function update(Post $post, array $data)
    {
        // topic_ids는 별도로 처리하고 $data에서 제거
        $topicIds = null;
        if (isset($data['topic_ids'])) {
            if (is_array($data['topic_ids'])) {
                $topicIds = array_filter($data['topic_ids']); // 빈 값 제거
            }
            unset($data['topic_ids']); // fillable에 없으므로 제거
        }
        
        // 업데이트할 필드만 선별 (fillable에 있는 필드만)
        $updateData = [];
        $fillable = $post->getFillable();
        foreach ($data as $key => $value) {
            if (in_array($key, $fillable)) {
                $updateData[$key] = $value;
            }
        }
        
        $post->update($updateData);
        
        // 주제 연결 업데이트
        if ($topicIds !== null) {
            if (!empty($topicIds)) {
                $post->topics()->sync($topicIds);
            } else {
                // 빈 배열인 경우 모든 주제 제거
                $post->topics()->detach();
            }
        }
        // topic_ids가 null이면 주제는 변경하지 않음 (기존 주제 유지)
        
        return $post;
    }

    /**
     * Delete a post.
     */
    public function delete(Post $post)
    {
        // 포인트 차감 (게시글 삭제)
        $board = $post->board;
        if ($board && $board->delete_points != 0) {
            $user = $post->user;
            if ($user) {
                $user->addPoints($board->delete_points); // 음수 값이므로 차감됨
            }
        }

        return $post->delete();
    }

    /**
     * Increment post views.
     */
    public function incrementViews(Post $post, $userId = null)
    {
        $post->incrementViews();
        
        // 포인트 지급 (게시글 읽기) - 중복 방지를 위해 사용자별로 하루에 한 번만
        $board = $post->board;
        if ($board && $board->read_points != 0 && $userId) {
            $user = User::find($userId);
            if ($user) {
                // 오늘 이미 이 게시글을 읽었는지 확인
                $todayReadKey = 'post_read_' . $post->id . '_' . $userId;
                if (!session()->has($todayReadKey)) {
                    $user->addPoints($board->read_points);
                    session()->put($todayReadKey, true);
                }
            }
        }
        
        return $post;
    }
}


