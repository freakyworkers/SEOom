<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use App\Services\FileUploadService;
use App\Models\Post;
use App\Models\Board;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected $postService;
    protected $fileUploadService;

    public function __construct(PostService $postService, FileUploadService $fileUploadService)
    {
        $this->postService = $postService;
        $this->fileUploadService = $fileUploadService;
        // Auth middleware is already applied in routes, so we don't need it here
        // $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of posts.
     */
    public function index(Site $site, $boardSlug)
    {
        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        $randomOrder = $board->type === 'bookmark' && $board->random_order;
        $topicId = request()->query('topic');
        $searchKeyword = request()->query('search');
        $searchType = request()->query('search_type', 'title_content');
        $perPage = $board->posts_per_page ?? 20;
        $posts = $this->postService->getPostsByBoard($board->id, $perPage, $randomOrder, $topicId, $site->id, $searchKeyword, $searchType);
        
        // 질의응답 게시판인 경우 기존 게시글에 qa_status가 없으면 기본값 설정
        if ($board->type === 'qa') {
            $qaStatuses = $board->qa_statuses ?? [];
            if (!empty($qaStatuses)) {
                $defaultStatus = $qaStatuses[0]['name'] ?? null;
                if ($defaultStatus) {
                    // 페이지네이션된 게시글에 대해서만 처리
                    foreach ($posts->items() as $post) {
                        if (empty($post->qa_status)) {
                            // DB에 저장
                            $post->update(['qa_status' => $defaultStatus]);
                            // 현재 객체에도 반영
                            $post->qa_status = $defaultStatus;
                        }
                    }
                }
            }
        }
        
        // 주제 필터링 및 검색을 위해 쿼리 파라미터 유지
        $posts->appends(request()->query());

        // 추천 기능 활성화 여부 확인
        $showLikes = $board->enable_likes && Schema::hasTable('post_likes');
        $hasPostLikesTable = Schema::hasTable('post_likes');
        
        // 저장된 게시글 ID 목록 가져오기 (로그인한 경우)
        $savedPostIds = [];
        if (auth()->check() && $board->saved_posts_enabled && Schema::hasTable('saved_posts')) {
            $savedPostIds = \App\Models\SavedPost::where('user_id', auth()->id())
                ->where('site_id', $site->id)
                ->whereIn('post_id', $posts->pluck('id'))
                ->pluck('post_id')
                ->toArray();
        }

        return view('posts.index', compact('posts', 'board', 'site', 'topicId', 'showLikes', 'hasPostLikesTable', 'savedPostIds'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(Site $site, $boardSlug)
    {
        try {
            $board = Board::where('site_id', $site->id)
                ->where('slug', $boardSlug)
                ->firstOrFail();

            // 권한 체크
            if (!$this->checkWritePermission($board)) {
                return redirect()->route('boards.show', ['site' => $site->slug, 'slug' => $boardSlug])
                    ->with('error', '게시글 작성 권한이 없습니다.');
            }

            // 하루 최대 글 수 체크
            if ($board->max_posts_per_day > 0) {
                $todayPostsCount = Post::where('board_id', $board->id)
                    ->where('user_id', auth()->id())
                    ->whereDate('created_at', today())
                    ->count();
                
                if ($todayPostsCount >= $board->max_posts_per_day) {
                    return redirect()->route('boards.show', ['site' => $site->slug, 'slug' => $boardSlug])
                        ->with('error', "하루 최대 {$board->max_posts_per_day}개의 게시글만 작성할 수 있습니다.");
                }
            }

            // 답글 작성인 경우 원본 게시글 가져오기
            $parentPost = null;
            if (request()->has('reply_to')) {
                $parentPost = Post::where('id', request('reply_to'))
                    ->where('board_id', $board->id)
                    ->first();
                if (!$parentPost) {
                    return redirect()->route('boards.show', ['site' => $site->slug, 'slug' => $boardSlug])
                        ->with('error', '원본 게시글을 찾을 수 없습니다.');
                }
            }

            return view('posts.create', compact('board', 'site', 'parentPost'));
        } catch (\Exception $e) {
            abort(404, 'Board not found: ' . $boardSlug);
        }
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request, Site $site, $boardSlug)
    {
        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        // 권한 체크
        if (!$this->checkWritePermission($board)) {
            return back()->with('error', '게시글 작성 권한이 없습니다.')->withInput();
        }

        // 하루 최대 글 수 체크
        if ($board->max_posts_per_day > 0) {
            $todayPostsCount = Post::where('board_id', $board->id)
                ->where('user_id', auth()->id())
                ->whereDate('created_at', today())
                ->count();
            
            if ($todayPostsCount >= $board->max_posts_per_day) {
                return back()->with('error', "하루 최대 {$board->max_posts_per_day}개의 게시글만 작성할 수 있습니다.")->withInput();
            }
        }

        // 글쓰기 텀 체크
        $writeInterval = (int) $site->getSetting('write_interval', 0);
        if ($writeInterval > 0) {
            $lastPost = Post::where('board_id', $board->id)
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($lastPost && !$lastPost->created_at->isFuture()) {
                $secondsSinceLastPost = now()->diffInSeconds($lastPost->created_at);
                if ($secondsSinceLastPost < $writeInterval) {
                    $remainingSeconds = $writeInterval - $secondsSinceLastPost;
                    $remainingMinutes = ceil($remainingSeconds / 60);
                    return back()->with('error', "게시글 작성 후 {$writeInterval}초가 지나야 다시 작성할 수 있습니다. (약 {$remainingMinutes}분 남음)")->withInput();
                }
            }
        }

        // 금지단어 체크
        $bannedWords = $site->getSetting('banned_words', '');
        if (!empty($bannedWords)) {
            $bannedWordsArray = array_map('trim', explode(',', $bannedWords));
            $bannedWordsArray = array_filter($bannedWordsArray); // 빈 값 제거
            
            $title = $request->input('title', '');
            $content = strip_tags($request->input('content', '')); // HTML 태그 제거
            
            foreach ($bannedWordsArray as $bannedWord) {
                if (!empty($bannedWord) && (stripos($title, $bannedWord) !== false || stripos($content, $bannedWord) !== false)) {
                    if ($request->expectsJson() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => '금지단어가 포함된 게시글을 작성할 수 없습니다.',
                        ], 422);
                    }
                    return back()->with('error', '금지단어가 포함된 게시글을 작성할 수 없습니다.')->withInput();
                }
            }
        }

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'site_name' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'link' => 'nullable|url|max:500',
            'bookmark_items' => 'nullable|array',
            'bookmark_items.*.name' => 'nullable|string|max:255',
            'bookmark_items.*.value' => 'nullable|string|max:500',
            'topic_ids' => 'nullable|array',
            'topic_ids.*' => 'exists:topics,id',
            'is_notice' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
            'is_secret' => 'nullable|boolean',
            'adoption_points' => 'nullable|integer|min:0',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
            'event_type' => 'nullable|in:general,application,quiz',
            'event_start_date' => 'nullable|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
            'event_end_undecided' => 'nullable|boolean',
            'event_options' => 'nullable|array',
            'event_options.*.text' => 'required_with:event_options|string|max:255',
            'event_options.*.is_correct' => 'nullable|boolean',
        ];

        // 일반 이벤트일 때는 event_options 제거
        if ($request->input('event_type') === 'general') {
            $request->merge(['event_options' => null]);
        }

        // quiz 타입일 때만 event_options 필수
        if ($request->input('event_type') === 'quiz') {
            $rules['event_options'] = 'required|array|min:1';
            $rules['event_options.*.text'] = 'required|string|max:255';
        } else {
            // 일반/신청 이벤트일 때는 event_options가 있으면 안 됨
            $rules['event_options'] = 'nullable|array';
            $rules['event_options.*.text'] = 'nullable|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['board_id'] = $board->id;
        
        // 답글 처리
        if ($request->has('reply_to') && $request->input('reply_to')) {
            $parentPost = Post::where('id', $request->input('reply_to'))
                ->where('board_id', $board->id)
                ->first();
            if ($parentPost) {
                $data['reply_to'] = $parentPost->id;
            }
        }
        
        // 질의응답 게시판 최초 상태 설정
        if ($board->type === 'qa') {
            $qaStatuses = $board->qa_statuses ?? [];
            if (!empty($qaStatuses)) {
                // 첫 번째 상태를 최초 상태로 설정
                $data['qa_status'] = $qaStatuses[0]['name'] ?? null;
            }
        }
        
        // 비밀글 처리
        if ($board->force_secret) {
            // 비밀글 상시 활성화가 켜져 있으면 무조건 비밀글로 처리
            $data['is_secret'] = true;
        } elseif ($board->enable_secret) {
            // 체크박스가 체크되었는지 확인 ('on', '1', true 등)
            $isSecretChecked = $request->has('is_secret') && 
                              ($request->input('is_secret') === 'on' || 
                               $request->input('is_secret') === '1' || 
                               $request->input('is_secret') === true ||
                               filter_var($request->input('is_secret'), FILTER_VALIDATE_BOOLEAN));
            $data['is_secret'] = (bool) $isSecretChecked; // 명시적으로 boolean으로 변환
        } else {
            $data['is_secret'] = false;
        }
        
        // 채택 포인트 처리
        if ($board->enable_author_comment_adopt) {
            $data['adoption_points'] = max(0, (int) $request->input('adoption_points', 0));
        } else {
            $data['adoption_points'] = 0;
        }

        // 운영자가 관리 권한이 있는 경우 공지/고정 체크박스 처리
        // 체크박스가 체크 해제되면 서버로 전송되지 않으므로 명시적으로 처리
        if (auth()->user()->canManage()) {
            $data['is_notice'] = $request->has('is_notice') && 
                                ($request->input('is_notice') === '1' || 
                                 $request->input('is_notice') === 'on' || 
                                 $request->input('is_notice') === true);
            $data['is_pinned'] = $request->has('is_pinned') && 
                                ($request->input('is_pinned') === '1' || 
                                 $request->input('is_pinned') === 'on' || 
                                 $request->input('is_pinned') === true);
        } else {
            // 일반 사용자는 공지/고정 설정 불가
            $data['is_notice'] = false;
            $data['is_pinned'] = false;
        }

        // Handle thumbnail upload (for photo, bookmark, blog, pinterest, and event boards with photo display type)
        $isEventPhotoType = $board->type === 'event' && ($board->event_display_type ?? 'photo') === 'photo';
        if ($request->hasFile('thumbnail') && (in_array($board->type, ['photo', 'bookmark', 'blog', 'pinterest']) || $isEventPhotoType)) {
            $thumbnail = $request->file('thumbnail');
            $directory = 'thumbnails/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($thumbnail, $directory);
            $data['thumbnail_path'] = $result['file_path'];
        }

        // Handle event data (for event boards with photo display type - benefit item)
        if ($isEventPhotoType) {
            // Process benefit item (bookmark_items의 첫 번째 항목만)
            $benefitItems = [];
            if ($request->has('bookmark_items')) {
                $items = $request->input('bookmark_items', []);
                if (isset($items[0]) && (!empty($items[0]['name']) || !empty($items[0]['value']))) {
                    $benefitItems[] = [
                        'name' => $items[0]['name'] ?? '혜택',
                        'value' => $items[0]['value'] ?? '',
                    ];
                }
            }
            $data['bookmark_items'] = !empty($benefitItems) ? $benefitItems : null;
        }

        // Handle bookmark data (for bookmark boards)
        if ($board->type === 'bookmark') {
            $data['site_name'] = $request->input('site_name');
            $data['code'] = $request->input('code');
            $data['link'] = $request->input('link');
            
            // Process bookmark items
            $bookmarkItems = [];
            if ($request->has('bookmark_items')) {
                foreach ($request->input('bookmark_items', []) as $item) {
                    if (!empty($item['name']) || !empty($item['value'])) {
                        $bookmarkItems[] = [
                            'name' => $item['name'] ?? '',
                            'value' => $item['value'] ?? '',
                        ];
                    }
                }
            }
            $data['bookmark_items'] = !empty($bookmarkItems) ? $bookmarkItems : null;
        }

        // Handle topic selection
        if ($request->has('topic_ids')) {
            $topicIds = $request->input('topic_ids', []);
            // 복수 주제가 허용되지 않은 경우 첫 번째 주제만 사용
            if (!$board->allow_multiple_topics && !empty($topicIds)) {
                $topicIds = [reset($topicIds)];
            }
            $data['topic_ids'] = array_filter($topicIds); // 빈 값 제거
        }

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($this->fileUploadService->validateAttachment($file)) {
                    // Upload to temp directory first
                    $uploaded = $this->fileUploadService->uploadAttachment($file, 0);
                    $attachments[] = $uploaded;
                }
            }
        }

        $post = $this->postService->create($data, auth()->id(), $site->id, $attachments);
        
        // 답글 작성 시 알림 전송
        if ($post->reply_to) {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->createReplyNotification($post);
        }
        
        // Move temp files to post directory and update attachment records
        if (!empty($attachments) && $post->attachments->count() > 0) {
            foreach ($post->attachments as $attachment) {
                if (str_contains($attachment->file_path, 'attachments/temp')) {
                    $newPath = $this->fileUploadService->moveAttachmentToPost($attachment->file_path, $post->id);
                    $attachment->update(['file_path' => $newPath]);
                }
            }
        }

        // Handle event options (for quiz events)
        if ($board->type === 'event' && $request->input('event_type') === 'quiz' && $request->has('event_options')) {
            $eventOptions = $request->input('event_options', []);
            $order = 0;
            foreach ($eventOptions as $option) {
                if (!empty($option['text'])) {
                    \App\Models\EventOption::create([
                        'post_id' => $post->id,
                        'option_text' => $option['text'],
                        'order' => $order++,
                        'is_correct' => false, // 정답은 운영자가 나중에 선택
                    ]);
                }
            }
        }

        return redirect()->route('posts.show', [
            'site' => $site->slug,
            'boardSlug' => $boardSlug,
            'post' => $post->id
        ]);
    }

    /**
     * Display the specified post.
     */
    public function show(Site $site, $boardSlug, Post $post)
    {
        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        // 권한 체크
        if (!$this->checkReadPermission($board)) {
            abort(403, '게시글 읽기 권한이 없습니다.');
        }

        // 1:1 게시판 권한 체크 (작성자와 관리자만 확인 가능)
        if ($board->type === 'one_on_one') {
            if (!auth()->check()) {
                return redirect()->route('login', ['site' => $site->slug])
                    ->with('error', '로그인이 필요합니다.');
            }
            if (!auth()->user()->canManage() && auth()->id() !== $post->user_id) {
                return redirect()->route('boards.show', ['site' => $site->slug, 'slug' => $boardSlug])
                    ->with('error', '본인이 작성한 글만 확인할 수 있습니다.');
            }
        }

        // 비밀글 접근 제한 체크
        $isSecret = $board->force_secret || ($board->enable_secret && $post->is_secret);
        if ($isSecret) {
            $canViewSecret = false;
            if (auth()->check()) {
                $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
            }
            if (!$canViewSecret) {
                return redirect()->route('boards.show', ['site' => $site->slug, 'slug' => $boardSlug])
                    ->with('error', '비밀글은 작성자와 운영자만 확인할 수 있습니다.');
            }
        }

        $this->postService->incrementViews($post, auth()->id());
        // 게시글을 다시 로드하여 최신 상태 반영 (qa_status 포함)
        $post = $this->postService->getPost($post->id);
        // qa_status가 제대로 로드되었는지 확인
        if ($board->type === 'qa') {
            $post->refresh();
        }
        
        // likes 관계가 eager load되었는지 확인하고, 없으면 다시 로드
        if (!$post->relationLoaded('likes') && \Schema::hasTable('post_likes')) {
            $post->load('likes');
        }
        
        // 이벤트 게시판인 경우 eventParticipants와 user 관계 eager load
        if ($board->type === 'event' && $post->isEventPost()) {
            $post->load(['eventParticipants.user', 'eventOptions']);
        }

        // 해당 게시판의 모든 게시글 가져오기 (현재 게시글 제외)
        $randomOrder = $board->type === 'bookmark' && $board->random_order;
        $perPage = $board->posts_per_page ?? 20;
        $boardPosts = $this->postService->getPostsByBoard($board->id, $perPage, $randomOrder, null);

        // 저장 상태 확인
        $isSaved = false;
        if (auth()->check() && $board->saved_posts_enabled && Schema::hasTable('saved_posts')) {
            $isSaved = $post->isSavedByUser(auth()->id());
        }

        return view('posts.show', compact('post', 'board', 'site', 'boardPosts', 'isSaved'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Site $site, $boardSlug, Post $post)
    {
        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        $this->authorize('update', $post);

        // 이벤트 게시판인 경우 eventOptions eager load
        if ($board->type === 'event' && $post->isEventPost()) {
            $post->load('eventOptions');
        }

        return view('posts.edit', compact('post', 'board', 'site'));
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Site $site, $boardSlug, Post $post)
    {
        $this->authorize('update', $post);

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'site_name' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'link' => 'nullable|url|max:500',
            'bookmark_items' => 'nullable|array',
            'bookmark_items.*.name' => 'nullable|string|max:255',
            'bookmark_items.*.value' => 'nullable|string|max:500',
            'topic_ids' => 'nullable|array',
            'topic_ids.*' => 'exists:topics,id',
            'is_notice' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
            'event_type' => 'nullable|in:general,application,quiz',
            'event_start_date' => 'nullable|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
            'event_end_undecided' => 'nullable|boolean',
            'event_options' => 'nullable|array',
        ];

        // quiz 타입일 때만 event_options 필수
        if ($request->input('event_type') === 'quiz') {
            $rules['event_options'] = 'required|array|min:1';
            $rules['event_options.*.text'] = 'required|string|max:255';
        } else {
            // 일반/신청 이벤트일 때는 event_options가 있으면 안 됨
            $rules['event_options'] = 'nullable|array';
            $rules['event_options.*.text'] = 'nullable|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $board = $post->board;
        
        // 업데이트할 데이터만 선별
        $data = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ];
        
        // 비밀글 처리
        if ($board->force_secret) {
            $data['is_secret'] = true;
        } elseif ($board->enable_secret) {
            $isSecretChecked = $request->has('is_secret') && 
                              ($request->input('is_secret') === 'on' || 
                               $request->input('is_secret') === '1' || 
                               $request->input('is_secret') === true ||
                               filter_var($request->input('is_secret'), FILTER_VALIDATE_BOOLEAN));
            $data['is_secret'] = (bool) $isSecretChecked;
        } else {
            $data['is_secret'] = false;
        }
        
        // 채택 포인트 처리
        if ($board->enable_author_comment_adopt) {
            $data['adoption_points'] = max(0, (int) $request->input('adoption_points', 0));
        }

        // 운영자가 관리 권한이 있는 경우 공지/고정 체크박스 처리
        // 체크박스가 체크 해제되면 서버로 전송되지 않으므로 명시적으로 처리
        if (auth()->user()->canManage()) {
            $data['is_notice'] = $request->has('is_notice') && 
                                ($request->input('is_notice') === '1' || 
                                 $request->input('is_notice') === 'on' || 
                                 $request->input('is_notice') === true);
            $data['is_pinned'] = $request->has('is_pinned') && 
                                ($request->input('is_pinned') === '1' || 
                                 $request->input('is_pinned') === 'on' || 
                                 $request->input('is_pinned') === true);
        } else {
            // 일반 사용자는 공지/고정 설정 불가
            $data['is_notice'] = $post->is_notice;
            $data['is_pinned'] = $post->is_pinned;
        }

        // Handle thumbnail upload (for photo, bookmark, blog, pinterest, and event boards with photo display type)
        $isEventPhotoType = $board->type === 'event' && ($board->event_display_type ?? 'photo') === 'photo';
        if ($request->hasFile('thumbnail') && (in_array($board->type, ['photo', 'bookmark', 'blog', 'pinterest']) || $isEventPhotoType)) {
            // Delete old thumbnail if exists
            if ($post->thumbnail_path && Storage::disk('public')->exists($post->thumbnail_path)) {
                Storage::disk('public')->delete($post->thumbnail_path);
            }
            
            $thumbnail = $request->file('thumbnail');
            $directory = 'thumbnails/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($thumbnail, $directory);
            $data['thumbnail_path'] = $result['file_path'];
        } elseif (in_array($board->type, ['photo', 'bookmark', 'blog', 'pinterest']) || $isEventPhotoType) {
            // 썸네일을 업로드하지 않았으면 기존 썸네일 유지
            $data['thumbnail_path'] = $post->thumbnail_path;
        }

        // Handle event data (for event boards with photo display type - benefit item)
        if ($isEventPhotoType) {
            // Process benefit item (bookmark_items의 첫 번째 항목만)
            $benefitItems = [];
            if ($request->has('bookmark_items')) {
                $items = $request->input('bookmark_items', []);
                if (isset($items[0]) && (!empty($items[0]['name']) || !empty($items[0]['value']))) {
                    $benefitItems[] = [
                        'name' => $items[0]['name'] ?? '혜택',
                        'value' => $items[0]['value'] ?? '',
                    ];
                }
            }
            $data['bookmark_items'] = !empty($benefitItems) ? $benefitItems : null;
        }

        // Handle bookmark data (for bookmark boards)
        if ($board->type === 'bookmark') {
            $data['site_name'] = $request->input('site_name');
            $data['code'] = $request->input('code');
            $data['link'] = $request->input('link');
            
            // Process bookmark items
            $bookmarkItems = [];
            if ($request->has('bookmark_items')) {
                foreach ($request->input('bookmark_items', []) as $item) {
                    if (!empty($item['name']) || !empty($item['value'])) {
                        $bookmarkItems[] = [
                            'name' => $item['name'] ?? '',
                            'value' => $item['value'] ?? '',
                        ];
                    }
                }
            }
            $data['bookmark_items'] = !empty($bookmarkItems) ? $bookmarkItems : null;
        }

        // Handle event data (for event boards)
        if ($board->type === 'event') {
            $data['event_type'] = $request->input('event_type');
            $data['event_start_date'] = $request->input('event_start_date') ?: null;
            
            // 종료일 미정 체크박스 처리
            if ($request->has('event_end_undecided') && $request->input('event_end_undecided')) {
                $data['event_end_date'] = null;
                $data['event_end_undecided'] = true;
            } else {
                $data['event_end_date'] = $request->input('event_end_date') ?: null;
                $data['event_end_undecided'] = false;
            }
        } else {
            // 이벤트 게시판이 아니면 이벤트 관련 필드 제거
            unset($data['event_type']);
            unset($data['event_start_date']);
            unset($data['event_end_date']);
            unset($data['event_end_undecided']);
        }

        // event_type이 quiz가 아니면 event_options를 제거
        if ($request->input('event_type') !== 'quiz') {
            unset($data['event_options']);
        }

        // Handle QA status (for QA boards, only if user is admin)
        if ($board->type === 'qa' && auth()->user()->canManage()) {
            if ($request->has('qa_status')) {
                $qaStatus = $request->input('qa_status');
                // 상태가 게시판에 설정된 상태인지 확인
                $qaStatuses = $board->qa_statuses ?? [];
                $statusNames = array_column($qaStatuses, 'name');
                if (in_array($qaStatus, $statusNames)) {
                    $data['qa_status'] = $qaStatus;
                }
            }
        }

        // Handle topic selection
        if ($request->has('topic_ids')) {
            $topicIds = $request->input('topic_ids', []);
            // 복수 주제가 허용되지 않은 경우 첫 번째 주제만 사용
            if (!$board->allow_multiple_topics && !empty($topicIds)) {
                $topicIds = [reset($topicIds)];
            }
            $data['topic_ids'] = array_filter($topicIds); // 빈 값 제거
        } else {
            // topic_ids가 없으면 기존 주제 유지 (변경하지 않음)
            // PostService에서 topic_ids가 null이면 주제를 업데이트하지 않음
            $data['topic_ids'] = null;
        }

        // 디버깅: 업데이트할 데이터 확인
        \Log::info('Post Update Data', [
            'post_id' => $post->id,
            'data_keys' => array_keys($data),
            'title' => $data['title'] ?? null,
            'content_length' => isset($data['content']) ? strlen($data['content']) : 0,
        ]);

        $this->postService->update($post, $data);

        // Handle event options (for quiz events)
        if ($board->type === 'event' && $request->input('event_type') === 'quiz' && $request->has('event_options')) {
            // 기존 선택지 삭제
            \App\Models\EventOption::where('post_id', $post->id)->delete();
            
            // 새 선택지 추가
            $eventOptions = $request->input('event_options', []);
            $order = 0;
            foreach ($eventOptions as $option) {
                if (!empty($option['text'])) {
                    \App\Models\EventOption::create([
                        'post_id' => $post->id,
                        'option_text' => $option['text'],
                        'order' => $order++,
                        'is_correct' => false, // 정답은 운영자가 나중에 선택
                    ]);
                }
            }
        } elseif ($board->type === 'event' && $request->input('event_type') !== 'quiz') {
            // 정답형이 아니면 기존 선택지 삭제
            \App\Models\EventOption::where('post_id', $post->id)->delete();
        }

        return redirect()->route('posts.show', [
            'site' => $site->slug,
            'boardSlug' => $boardSlug,
            'post' => $post->id
        ])->with('success', '게시글이 수정되었습니다.');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Site $site, $boardSlug, Post $post)
    {
        $this->authorize('delete', $post);

        $this->postService->delete($post);

        return redirect()->route('posts.index', [
            'site' => $site->slug,
            'boardSlug' => $boardSlug
        ])->with('success', '게시글이 삭제되었습니다.');
    }

    /**
     * End an event post.
     */
    public function endEvent(Site $site, $boardSlug, Post $post)
    {
        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        // 권한 체크 (관리자만 가능)
        if (!auth()->check() || !auth()->user()->canManage()) {
            return back()->with('error', '이벤트를 종료할 권한이 없습니다.');
        }

        // 이벤트 게시판인지 확인
        if ($board->type !== 'event' || !$post->isEventPost()) {
            return back()->with('error', '이벤트 게시글이 아닙니다.');
        }

        // 이미 종료된 이벤트인지 확인
        if ($post->event_is_ended) {
            return back()->with('error', '이미 종료된 이벤트입니다.');
        }

        // 이벤트 종료 처리
        $post->update(['event_is_ended' => true]);

        return redirect()->route('posts.show', [
            'site' => $site->slug,
            'boardSlug' => $boardSlug,
            'post' => $post->id
        ]);
    }

    /**
     * Update QA status for a post.
     */
    public function updateQaStatus(Request $request, Site $site, $boardSlug, Post $post)
    {
        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        // 권한 체크 (관리자만 가능)
        if (!auth()->check() || !auth()->user()->canManage()) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => '상태를 변경할 권한이 없습니다.'], 403);
            }
            return back()->with('error', '상태를 변경할 권한이 없습니다.');
        }

        // 질의응답 게시판인지 확인
        if ($board->type !== 'qa') {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => '질의응답 게시판이 아닙니다.'], 400);
            }
            return back()->with('error', '질의응답 게시판이 아닙니다.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator);
        }

        // 상태가 게시판에 설정된 상태인지 확인
        $qaStatuses = $board->qa_statuses ?? [];
        $statusNames = array_column($qaStatuses, 'name');
        if (!in_array($request->status, $statusNames)) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => '유효하지 않은 상태입니다.'], 400);
            }
            return back()->with('error', '유효하지 않은 상태입니다.');
        }

        // 상태 업데이트
        $oldStatus = $post->qa_status;
        $post->qa_status = $request->status;
        $saved = $post->save();
        
        // 저장 후 모델 새로고침하여 최신 데이터 확인
        $post->refresh();

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '상태가 변경되었습니다.',
                'status' => $post->qa_status,
                'saved' => $saved
            ]);
        }

        return redirect()->route('posts.show', [
            'site' => $site->slug,
            'boardSlug' => $boardSlug,
            'post' => $post->id
        ])->with('success', '상태가 변경되었습니다.');
    }

    /**
     * Upload image for Summernote editor.
     */
    public function uploadImage(Request $request, Site $site)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first('image')
            ], 422);
        }

        try {
            $file = $request->file('image');
            $directory = 'editor-images/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($file, $directory);
            
            // 절대 URL 생성 (asset 헬퍼 사용)
            $url = asset('storage/' . $result['file_path']);

            return response()->json([
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => '이미지 업로드 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * Check if user has read permission for the board.
     */
    protected function checkReadPermission(Board $board)
    {
        $permission = $board->read_permission ?? 'guest';
        
        // guest: anyone can read
        if ($permission === 'guest') {
            return true;
        }
        
        // user: logged in users only
        if ($permission === 'user') {
            return auth()->check();
        }
        
        // admin: only admins/managers
        if ($permission === 'admin') {
            return auth()->check() && auth()->user()->canManage();
        }
        
        return false;
    }

    /**
     * Check if user has write permission for the board.
     */
    protected function checkWritePermission(Board $board)
    {
        $permission = $board->write_permission ?? 'user';
        
        // guest: anyone can write
        if ($permission === 'guest') {
            return true;
        }
        
        // user: logged in users only
        if ($permission === 'user') {
            return auth()->check();
        }
        
        // admin: only admins/managers
        if ($permission === 'admin') {
            return auth()->check() && auth()->user()->canManage();
        }
        
        return false;
    }

    /**
     * Toggle like/dislike for a post.
     */
    public function toggleLike(Request $request, Site $site, $boardSlug, $postId)
    {
        \Log::info('toggleLike method called', [
            'postId_param' => $postId,
            'site_slug' => $site->slug,
            'board_slug' => $boardSlug,
            'request_all' => $request->all()
        ]);
        
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        // 추천 기능이 활성화되어 있는지 확인
        if (!$board->enable_likes) {
            return response()->json([
                'success' => false,
                'message' => '이 게시판에서는 추천 기능을 사용할 수 없습니다.'
            ], 403);
        }

        // 게시글 확인 (site_id와 board_id로 검증)
        // $postId를 정수로 변환하여 라우트 모델 바인딩 문제 방지
        $postIdInt = (int) $postId;
        \Log::info('Post lookup', [
            'postId_param' => $postId,
            'postIdInt' => $postIdInt,
            'site_id' => $site->id,
            'board_id' => $board->id
        ]);
        
        $post = Post::where('id', $postIdInt)
            ->where('site_id', $site->id)
            ->where('board_id', $board->id)
            ->firstOrFail();
        
        \Log::info('Post found', [
            'post_id' => $post->id,
            'post_title' => $post->title
        ]);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:like,dislike',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $type = $request->input('type');
        $userId = auth()->id();
        
        \Log::info('toggleLike called', [
            'post_id' => $post->id,
            'postId_param' => $postId,
            'site_id' => $site->id,
            'board_id' => $board->id,
            'user_id' => $userId,
            'type' => $type
        ]);

        try {
            DB::beginTransaction();
            
            // 기존 추천/비추천 확인
            $existingLike = \App\Models\PostLike::where('post_id', $post->id)
                ->where('user_id', $userId)
                ->first();

            if ($existingLike) {
                if ($existingLike->type === $type) {
                    // 같은 타입이면 취소 (삭제)
                    $deleted = $existingLike->delete();
                    \Log::info('PostLike deleted', [
                        'post_id' => $post->id,
                        'user_id' => $userId,
                        'deleted' => $deleted
                    ]);
                    $userLikeType = null;
                } else {
                    // 다른 타입이면 변경
                    $existingLike->type = $type;
                    $saved = $existingLike->save();
                    \Log::info('PostLike updated', [
                        'post_id' => $post->id,
                        'user_id' => $userId,
                        'type' => $type,
                        'saved' => $saved,
                        'id' => $existingLike->id
                    ]);
                    $userLikeType = $type;
                }
            } else {
                // 새로 생성
                $newLike = \App\Models\PostLike::create([
                    'post_id' => $post->id,
                    'user_id' => $userId,
                    'type' => $type,
                ]);
                
                \Log::info('PostLike created', [
                    'post_id' => $post->id,
                    'user_id' => $userId,
                    'type' => $type,
                    'id' => $newLike->id ?? 'null',
                    'exists' => $newLike->exists ?? 'null'
                ]);
                
                // 생성 확인
                if (!$newLike || !$newLike->id) {
                    throw new \Exception('PostLike 생성 실패: id=' . ($newLike->id ?? 'null'));
                }
                
                $userLikeType = $type;
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('PostLike toggle error: ' . $e->getMessage(), [
                'post_id' => $post->id,
                'user_id' => $userId,
                'type' => $type,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => '추천 처리 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }

        // 카운트 다시 계산 (직접 쿼리로 확인)
        $likeCount = \App\Models\PostLike::where('post_id', $post->id)
            ->where('type', 'like')
            ->count();
        $dislikeCount = \App\Models\PostLike::where('post_id', $post->id)
            ->where('type', 'dislike')
            ->count();
        
        // 사용자 추천 상태 다시 확인
        $currentUserLike = \App\Models\PostLike::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();
        $finalUserLikeType = $currentUserLike ? $currentUserLike->type : null;

        return response()->json([
            'success' => true,
            'like_count' => $likeCount,
            'dislike_count' => $dislikeCount,
            'user_like_type' => $finalUserLikeType,
        ]);
    }

    /**
     * Toggle save/unsave for a post.
     */
    public function toggleSave(Request $request, Site $site, $boardSlug, Post $post)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        // 저장하기 기능이 활성화되어 있는지 확인
        if (!$board->saved_posts_enabled) {
            return response()->json([
                'success' => false,
                'message' => '이 게시판에서는 저장하기 기능을 사용할 수 없습니다.'
            ], 403);
        }

        // 게시글 확인 (site_id와 board_id로 검증)
        if ($post->site_id !== $site->id || $post->board_id !== $board->id) {
            return response()->json([
                'success' => false,
                'message' => '게시글을 찾을 수 없습니다.'
            ], 404);
        }

        $userId = auth()->id();

        try {
            DB::beginTransaction();
            
            // 기존 저장 확인
            $existingSave = \App\Models\SavedPost::where('post_id', $post->id)
                ->where('user_id', $userId)
                ->where('site_id', $site->id)
                ->first();

            if ($existingSave) {
                // 저장 취소 (삭제)
                $existingSave->delete();
                $isSaved = false;
            } else {
                // 저장
                \App\Models\SavedPost::create([
                    'post_id' => $post->id,
                    'user_id' => $userId,
                    'site_id' => $site->id,
                ]);
                $isSaved = true;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'is_saved' => $isSaved,
                'message' => $isSaved ? '게시글이 저장되었습니다.' : '저장이 취소되었습니다.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Toggle save error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '저장 처리 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * Participate in an event.
     */
    public function participate(Request $request, Site $site, $boardSlug, Post $post)
    {
        if (!auth()->check()) {
            return back()->with('error', '로그인이 필요합니다.');
        }

        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        // 이벤트 게시판인지 확인
        if ($board->type !== 'event' || !$post->isEventPost()) {
            return back()->with('error', '이벤트 게시글이 아닙니다.');
        }

        // 이벤트 상태 확인
        $eventStatus = $post->event_status;
        if ($eventStatus !== 'ongoing' && $eventStatus !== 'upcoming') {
            return back()->with('error', '참여 가능한 이벤트가 아닙니다.');
        }

        // 이미 참여했는지 확인 (중복 방지 강화)
        $existingParticipant = \App\Models\EventParticipant::where('post_id', $post->id)
            ->where('user_id', auth()->id())
            ->lockForUpdate() // 동시성 제어를 위한 락
            ->first();

        if ($existingParticipant) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 이벤트에 참여하셨습니다.'
                ], 400);
            }
            return back()->with('error', '이미 이벤트에 참여하셨습니다.');
        }

        $validator = Validator::make($request->all(), [
            'event_option_id' => 'nullable|exists:event_options,id',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $eventOptionId = null;
            $isCorrect = false;
            $pointsAwarded = 0;

            if ($post->event_type === 'quiz') {
                // 정답형 이벤트인 경우 선택지 필수
                if (!$request->has('event_option_id')) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => '정답을 선택해주세요.'
                        ], 422);
                    }
                    return back()->with('error', '정답을 선택해주세요.');
                }

                $eventOptionId = $request->input('event_option_id');
                $eventOption = \App\Models\EventOption::where('id', $eventOptionId)
                    ->where('post_id', $post->id)
                    ->firstOrFail();

                // 정답 여부는 운영자가 나중에 선택하므로 여기서는 확인하지 않음
                $isCorrect = false; // 운영자가 정답을 선택하기 전까지는 false
                $pointsAwarded = 0; // 운영자가 일괄 지급
            } elseif ($post->event_type === 'application') {
                // 신청형 이벤트는 포인트 지급하지 않음 (운영자가 일괄 지급)
                $pointsAwarded = 0;
            }

            // 참여자 생성 (중복 방지를 위해 다시 확인)
            $checkDuplicate = \App\Models\EventParticipant::where('post_id', $post->id)
                ->where('user_id', auth()->id())
                ->first();
            
            if ($checkDuplicate) {
                DB::rollBack();
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => '이미 이벤트에 참여하셨습니다.'
                    ], 400);
                }
                return back()->with('error', '이미 이벤트에 참여하셨습니다.');
            }

            \App\Models\EventParticipant::create([
                'post_id' => $post->id,
                'user_id' => auth()->id(),
                'event_option_id' => $eventOptionId,
                'is_correct' => $isCorrect,
                'points_awarded' => $pointsAwarded,
            ]);

            DB::commit();

            // 참여자 수 및 선택지 통계 계산
            $participantCount = \App\Models\EventParticipant::where('post_id', $post->id)->count();
            $optionStats = [];
            if ($post->event_type === 'quiz') {
                foreach ($post->eventOptions as $option) {
                    $optionStats[] = [
                        'option_id' => $option->id,
                        'count' => $option->participants()->count()
                    ];
                }
            }

            $message = '이벤트에 참여했습니다.';
            if ($post->event_type === 'quiz') {
                if ($isCorrect) {
                    $message = '정답입니다! 이벤트에 참여했습니다.' . ($pointsAwarded > 0 ? " (+{$pointsAwarded}포인트)" : '');
                } else {
                    $message = '오답입니다. 이벤트에 참여했습니다.';
                }
            } elseif ($post->event_type === 'application') {
                $message = '이벤트 신청이 완료되었습니다.';
            }

            // AJAX 요청인 경우 JSON 응답
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'participant_count' => $participantCount,
                    'is_correct' => $isCorrect,
                    'option_stats' => $optionStats
                ]);
            }

            return redirect()->route('posts.show', [
                'site' => $site->slug,
                'boardSlug' => $boardSlug,
                'post' => $post->id
            ])->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Event participation error: ' . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '이벤트 참여 중 오류가 발생했습니다.'
                ], 500);
            }
            return back()->with('error', '이벤트 참여 중 오류가 발생했습니다.');
        }
    }

    /**
     * Award points to event participants (Admin only).
     */
    public function awardPoints(Request $request, Site $site, $boardSlug, Post $post)
    {
        if (!auth()->check() || (!auth()->user()->canManage() && auth()->id() !== $post->user_id)) {
            return back()->with('error', '권한이 없습니다.');
        }

        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        // 이벤트 게시판인지 확인
        if ($board->type !== 'event' || !$post->isEventPost()) {
            return back()->with('error', '이벤트 게시글이 아닙니다.');
        }

        // 신청형 또는 정답형 이벤트만 가능
        if (!in_array($post->event_type, ['application', 'quiz'])) {
            return back()->with('error', '신청형 또는 정답형 이벤트만 포인트 지급이 가능합니다.');
        }

        $validator = Validator::make($request->all(), [
            'points_amount' => 'required|integer|min:1',
            'target' => 'required|in:all,unpaid',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pointsAmount = (int) $request->input('points_amount');
        $target = $request->input('target');

        try {
            DB::beginTransaction();

            // 대상 참여자 조회 (해당 이벤트에 신청한 인원만 조회)
            if ($target === 'unpaid') {
                // 미지급 참여자만 (points_awarded가 0인 참여자)
                // 주의: post_id로 필터링하므로 해당 이벤트에 신청한 인원만 조회됨
                $participants = \App\Models\EventParticipant::where('post_id', $post->id)
                    ->where('points_awarded', 0)
                    ->with('user')
                    ->get();
            } else {
                // 전체 참여자 (해당 이벤트에 신청한 모든 인원)
                // 주의: post_id로 필터링하므로 해당 이벤트에 신청한 인원만 조회됨 (사이트 전체 회원이 아님)
                $participants = \App\Models\EventParticipant::where('post_id', $post->id)
                    ->with('user')
                    ->get();
            }

            if ($participants->isEmpty()) {
                return back()->with('error', '포인트를 지급할 참여자가 없습니다.');
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($participants as $participant) {
                if ($participant->user) {
                    try {
                        // 포인트 지급
                        $participant->user->addPoints($pointsAmount);
                        
                        // 참여자 기록 업데이트
                        $participant->points_awarded = ($participant->points_awarded ?? 0) + $pointsAmount;
                        $participant->save();
                        
                        // 알림 생성
                        $notificationService = new \App\Services\NotificationService();
                        $notificationService->createPointAwardNotification(
                            $participant->user_id,
                            $site->id,
                            $pointsAmount,
                            $post->title
                        );
                        
                        $successCount++;
                    } catch (\Exception $e) {
                        \Log::error('Point award error for participant: ' . $participant->id, [
                            'error' => $e->getMessage()
                        ]);
                        $failedCount++;
                    }
                } else {
                    $failedCount++;
                }
            }

            // 모든 참여자에게 포인트 지급이 완료되었는지 확인
            $totalParticipants = \App\Models\EventParticipant::where('post_id', $post->id)->count();
            $paidParticipants = \App\Models\EventParticipant::where('post_id', $post->id)
                ->where('points_awarded', '>', 0)
                ->count();
            
            // 모든 참여자에게 포인트 지급이 완료되었으면 이벤트 종료
            if ($totalParticipants > 0 && $paidParticipants >= $totalParticipants) {
                $post->event_is_ended = true;
                $post->save();
            }

            DB::commit();

            $message = "{$successCount}명의 참여자에게 {$pointsAmount}포인트를 지급했습니다.";
            if ($failedCount > 0) {
                $message .= " ({$failedCount}명 실패)";
            }
            
            // 모든 참여자에게 지급 완료 시 종료 메시지 추가
            if ($totalParticipants > 0 && $paidParticipants >= $totalParticipants) {
                $message .= " 모든 참여자에게 포인트 지급이 완료되어 이벤트가 종료되었습니다.";
            }

            return redirect()->route('posts.show', [
                'site' => $site->slug,
                'boardSlug' => $boardSlug,
                'post' => $post->id
            ])->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk point award error: ' . $e->getMessage());
            return back()->with('error', '포인트 지급 중 오류가 발생했습니다.');
        }
    }

    /**
     * Award points to quiz event participants who selected the correct answer (Admin only).
     */
    public function awardQuizPoints(Request $request, Site $site, $boardSlug, Post $post)
    {
        if (!auth()->check() || (!auth()->user()->canManage() && auth()->id() !== $post->user_id)) {
            return back()->with('error', '권한이 없습니다.');
        }

        $board = Board::where('site_id', $site->id)
            ->where('slug', $boardSlug)
            ->firstOrFail();

        // 이벤트 게시판인지 확인
        if ($board->type !== 'event' || !$post->isEventPost()) {
            return back()->with('error', '이벤트 게시글이 아닙니다.');
        }

        // 정답형 이벤트만 가능
        if ($post->event_type !== 'quiz') {
            return back()->with('error', '정답형 이벤트만 가능합니다.');
        }

        $validator = Validator::make($request->all(), [
            'correct_option_id' => 'required|exists:event_options,id',
            'points_amount' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $correctOptionId = (int) $request->input('correct_option_id');
        $pointsAmount = (int) $request->input('points_amount');

        try {
            DB::beginTransaction();

            // 정답 옵션 확인
            $correctOption = \App\Models\EventOption::where('id', $correctOptionId)
                ->where('post_id', $post->id)
                ->firstOrFail();

            // 해당 정답을 선택한 참여자만 조회
            $participants = \App\Models\EventParticipant::where('post_id', $post->id)
                ->where('event_option_id', $correctOptionId)
                ->where('points_awarded', 0) // 아직 지급받지 않은 참여자만
                ->with('user')
                ->get();

            if ($participants->isEmpty()) {
                return back()->with('error', '포인트를 지급할 참여자가 없습니다.');
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($participants as $participant) {
                if ($participant->user) {
                    try {
                        // 포인트 지급
                        $participant->user->addPoints($pointsAmount);
                        
                        // 참여자 기록 업데이트
                        $participant->points_awarded = ($participant->points_awarded ?? 0) + $pointsAmount;
                        $participant->save();
                        
                        // 알림 생성
                        $notificationService = new \App\Services\NotificationService();
                        $notificationService->createPointAwardNotification(
                            $participant->user_id,
                            $site->id,
                            $pointsAmount,
                            $post->title
                        );
                        
                        $successCount++;
                    } catch (\Exception $e) {
                        \Log::error('Point award error for participant: ' . $participant->id, [
                            'error' => $e->getMessage()
                        ]);
                        $failedCount++;
                    }
                } else {
                    $failedCount++;
                }
            }

            // 정답 옵션을 정답으로 설정
            // 기존 정답 해제
            \App\Models\EventOption::where('post_id', $post->id)
                ->update(['is_correct' => false]);
            
            // 새 정답 설정
            $correctOption->is_correct = true;
            $correctOption->save();

            // 모든 정답 선택자에게 포인트 지급이 완료되었는지 확인
            $totalCorrectParticipants = \App\Models\EventParticipant::where('post_id', $post->id)
                ->where('event_option_id', $correctOptionId)
                ->count();
            $paidCorrectParticipants = \App\Models\EventParticipant::where('post_id', $post->id)
                ->where('event_option_id', $correctOptionId)
                ->where('points_awarded', '>', 0)
                ->count();
            
            // 모든 정답 선택자에게 포인트 지급이 완료되었으면 이벤트 종료
            if ($totalCorrectParticipants > 0 && $paidCorrectParticipants >= $totalCorrectParticipants) {
                $post->event_is_ended = true;
                $post->save();
            }

            DB::commit();

            $message = "정답 '{$correctOption->option_text}'을 선택한 {$successCount}명의 참여자에게 {$pointsAmount}포인트를 지급했습니다.";
            if ($failedCount > 0) {
                $message .= " ({$failedCount}명 실패)";
            }
            
            // 모든 정답 선택자에게 지급 완료 시 종료 메시지 추가
            $isEventEnded = false;
            if ($totalCorrectParticipants > 0 && $paidCorrectParticipants >= $totalCorrectParticipants) {
                $message .= " 모든 정답 선택자에게 포인트 지급이 완료되어 이벤트가 종료되었습니다.";
                $isEventEnded = true;
            }

            return redirect()->route('posts.show', [
                'site' => $site->slug,
                'boardSlug' => $boardSlug,
                'post' => $post->id
            ])->with('quiz_award_success', $message)->with('quiz_award_event_ended', $isEventEnded);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Quiz point award error: ' . $e->getMessage());
            return back()->with('error', '포인트 지급 중 오류가 발생했습니다.');
        }
    }

    /**
     * Report a post (API).
     */
    public function reportPost(Site $site, Post $post, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first('reason') ?: '신고 사유를 입력해주세요.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Post가 해당 site에 속하는지 확인
        if ($post->site_id !== $site->id) {
            return response()->json([
                'success' => false,
                'error' => '게시글을 찾을 수 없습니다.'
            ], 404);
        }

        // Get reporter info
        $reporterId = Auth::id();
        $reporterGuestSessionId = null;
        $reporterNickname = null;
        
        if ($reporterId) {
            $reporterNickname = Auth::user()->nickname ?? Auth::user()->name;
        } else {
            $sessionId = session()->getId();
            $guestSession = \App\Models\ChatGuestSession::where('session_id', $sessionId)
                ->where('site_id', $site->id)
                ->first();
            if ($guestSession) {
                $reporterGuestSessionId = $guestSession->session_id;
                $reporterNickname = $guestSession->getNickname();
            } else {
                return response()->json(['error' => '세션을 찾을 수 없습니다.'], 404);
            }
        }

        // Get reported user info
        $reportedUserId = $post->user_id;
        $reportedNickname = $post->user ? ($post->user->nickname ?? $post->user->name) : '알 수 없음';

        // Create report
        $report = \App\Models\Report::create([
            'site_id' => $site->id,
            'reporter_id' => $reporterId,
            'reporter_guest_session_id' => $reporterGuestSessionId,
            'reporter_nickname' => $reporterNickname,
            'reported_user_id' => $reportedUserId,
            'reported_nickname' => $reportedNickname,
            'report_type' => 'post',
            'post_id' => $post->id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => '신고가 접수되었습니다.',
        ]);
    }
}
