<?php

namespace App\Http\Controllers;

use App\Services\BoardService;
use App\Services\PostService;
use App\Services\FileUploadService;
use App\Models\Board;
use App\Models\Site;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;

class BoardController extends Controller
{
    protected $boardService;
    protected $postService;
    protected $fileUploadService;

    public function __construct(BoardService $boardService, PostService $postService, FileUploadService $fileUploadService)
    {
        $this->boardService = $boardService;
        $this->postService = $postService;
        $this->fileUploadService = $fileUploadService;
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of boards.
     */
    public function index(Site $site)
    {
        $boards = $this->boardService->getBoardsBySite($site->id);
        return view('boards.index', compact('boards', 'site'));
    }

    /**
     * Show the form for creating a new board.
     */
    public function create(Site $site)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
        
        return view('admin.boards.create', compact('site'));
    }

    /**
     * Store a newly created board.
     */
    public function store(Request $request, Site $site)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        // 게시판 생성 제한 확인
        if (!$site->canCreateBoard()) {
            $limit = $site->getBoardLimit();
            $currentCount = $site->boards()->count();
            $errorMessage = "게시판 생성 제한에 도달했습니다. (현재 {$currentCount}개 / 최대 {$limit}개) 더 많은 게시판을 사용하려면 플랜을 업그레이드하세요.";
            
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                    'limit_exceeded' => true,
                    'limit_type' => 'boards',
                    'current' => $currentCount,
                    'limit' => $limit,
                ], 403);
            }
            return back()->with('error', $errorMessage);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:boards,slug,NULL,id,site_id,' . $site->id,
            'type' => 'nullable|in:general,photo,bookmark,blog,classic,instagram,event,one_on_one,pinterest,qa',
            'event_display_type' => 'nullable|in:photo,general',
            'summary_length' => 'nullable|integer|min:50|max:500',
            'pinterest_columns_mobile' => 'nullable|integer|min:1|max:6',
            'pinterest_columns_tablet' => 'nullable|integer|min:1|max:6',
            'pinterest_columns_desktop' => 'nullable|integer|min:1|max:6',
            'pinterest_columns_large' => 'nullable|integer|min:1|max:12',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'random_order' => 'nullable|boolean',
            'allow_multiple_topics' => 'nullable|boolean',
            'remove_links' => 'nullable|boolean',
            'enable_likes' => 'nullable|boolean',
            'enable_anonymous' => 'nullable|boolean',
            'enable_secret' => 'nullable|boolean',
            'force_secret' => 'nullable|boolean',
            'enable_reply' => 'nullable|boolean',
            'enable_comments' => 'nullable|boolean',
            'exclude_from_rss' => 'nullable|boolean',
            'prevent_drag' => 'nullable|boolean',
            'enable_attachments' => 'nullable|boolean',
            'enable_author_comment_adopt' => 'nullable|boolean',
            'enable_admin_comment_adopt' => 'nullable|boolean',
            'saved_posts_enabled' => 'nullable|boolean',
            'max_posts_per_day' => 'nullable|integer|min:0',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'read_permission' => 'nullable|in:guest,user,admin',
            'write_permission' => 'nullable|in:guest,user,admin',
            'comment_permission' => 'nullable|in:guest,user,admin',
            'read_points' => 'nullable|integer',
            'write_points' => 'nullable|integer',
            'delete_points' => 'nullable|integer',
            'comment_points' => 'nullable|integer',
            'comment_delete_points' => 'nullable|integer',
            'banned_words' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // Handle header image upload
        if ($request->hasFile('header_image')) {
            $headerImage = $request->file('header_image');
            $directory = 'board-headers/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($headerImage, $directory);
            $data['header_image_path'] = $result['file_path'];
        }
        
        // 블로그 타입인 경우 summary_length 기본값 설정
        if ($request->input('type') === 'blog' && !$request->has('summary_length')) {
            $data['summary_length'] = 150;
        } elseif ($request->input('type') !== 'blog') {
            $data['summary_length'] = null;
        }
        
        // 핀터레스트 타입인 경우 컬럼 설정 기본값 설정
        if ($request->input('type') === 'pinterest') {
            $data['pinterest_columns_mobile'] = $request->input('pinterest_columns_mobile', 2);
            $data['pinterest_columns_tablet'] = $request->input('pinterest_columns_tablet', 3);
            $data['pinterest_columns_desktop'] = $request->input('pinterest_columns_desktop', 4);
            $data['pinterest_columns_large'] = $request->input('pinterest_columns_large', 6);
        }
        
        // enable_comments 기본값 설정 (체크되지 않았으면 기본값 true)
        if (!$request->has('enable_comments')) {
            $data['enable_comments'] = true;
        } else {
            $data['enable_comments'] = ($request->input('enable_comments') === '1' || $request->input('enable_comments') === 'on' || $request->input('enable_comments') === true);
        }
        
        $board = $this->boardService->create($data, $site->id);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '게시판이 생성되었습니다.',
                'redirect' => route('boards.edit', ['site' => $site->slug, 'board' => $board->id])
            ]);
        }

        return redirect()->route('boards.edit', ['site' => $site->slug, 'board' => $board->id])
            ->with('success', '게시판이 생성되었습니다.');
    }

    /**
     * Display the specified board.
     */
    public function show(Site $site, $slug)
    {
        // Check if the slug contains '/posts/' - if so, this is a post route, not a board route
        // Laravel's route matching may have incorrectly matched /boards/{slug} instead of /boards/{boardSlug}/posts/create
        if (strpos($slug, '/posts/') !== false || strpos($slug, '/posts') !== false) {
            abort(404);
        }
        
        // Check if this is actually a board slug or a post route
        // If slug matches a board, show board. Otherwise, it might be a post route.
        $board = $this->boardService->getBoardBySlug($site->id, $slug);
        
        // 권한 체크
        $permission = $board->read_permission ?? 'guest';
        if ($permission === 'user' && !auth()->check()) {
            return redirect()->route('login', ['site' => $site->slug])
                ->with('error', '로그인이 필요합니다.');
        }
        if ($permission === 'admin' && (!auth()->check() || !auth()->user()->canManage())) {
            abort(403, '게시판 읽기 권한이 없습니다.');
        }
        
        // 1:1 게시판은 로그인 필수
        if ($board->type === 'one_on_one' && !auth()->check()) {
            return redirect()->route('login', ['site' => $site->slug])
                ->with('error', '로그인이 필요합니다.');
        }
        
        // 주제 필터링
        $topicId = request()->query('topic');
        $searchKeyword = request()->query('search');
        $searchType = request()->query('search_type', 'title_content');
        $perPage = $board->posts_per_page ?? 20;
        
        // PostService를 사용하여 게시글 가져오기 (이벤트 게시판 정렬 로직 포함)
        $randomOrder = $board->type === 'bookmark' && $board->random_order;
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
        
        // 검색 및 주제 필터링을 위해 쿼리 파라미터 유지
        $posts->appends(request()->query());
        
        // 추천 기능 활성화 여부 확인
        $showLikes = $board->enable_likes && Schema::hasTable('post_likes');
        $hasPostLikesTable = Schema::hasTable('post_likes');
        
        return view('boards.show', compact('board', 'site', 'posts', 'topicId', 'showLikes', 'hasPostLikesTable'));
    }

    /**
     * Show the form for editing the specified board.
     */
    public function edit(Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
        
        // 최신 데이터를 가져오기 위해 fresh() 사용
        $board = $board->fresh();
        
        // hide_title_description 값을 데이터베이스에서 직접 가져오기 (캐스팅 우회)
        $rawHideTitleDescription = \DB::table('boards')
            ->where('id', $board->id)
            ->value('hide_title_description');
        
        // hide_title_description 값 확인 로그
        \Log::info('hide_title_description in edit method:', [
            'board_id' => $board->id,
            'value' => $board->hide_title_description,
            'type' => gettype($board->hide_title_description),
            'raw_from_model' => $board->getRawOriginal('hide_title_description') ?? 'null',
            'raw_from_db' => $rawHideTitleDescription,
            'is_true' => $board->hide_title_description === true,
            'is_1' => $board->hide_title_description === 1,
            'is_string_1' => $board->hide_title_description === '1'
        ]);
        
        // 원시 값을 board 객체에 직접 설정 (Blade에서 사용할 수 있도록)
        if ($rawHideTitleDescription !== null) {
            $board->setRawAttributes(array_merge($board->getAttributes(), ['hide_title_description' => $rawHideTitleDescription]), true);
        }
        
        return view('admin.boards.edit', compact('board', 'site'));
    }

    /**
     * Update the specified board.
     */
    public function update(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:boards,slug,' . $board->id . ',id,site_id,' . $site->id,
            'type' => 'nullable|in:general,photo,bookmark,blog,classic,instagram,event,one_on_one,pinterest,qa',
            'event_display_type' => 'nullable|in:photo,general',
            'summary_length' => 'nullable|integer|min:50|max:500',
            'pinterest_columns_mobile' => 'nullable|integer|min:1|max:6',
            'pinterest_columns_tablet' => 'nullable|integer|min:1|max:6',
            'pinterest_columns_desktop' => 'nullable|integer|min:1|max:6',
            'pinterest_columns_large' => 'nullable|integer|min:1|max:12',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'random_order' => 'nullable|boolean',
            'allow_multiple_topics' => 'nullable|boolean',
            'max_posts_per_day' => 'nullable|integer|min:0',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'read_permission' => 'nullable|in:guest,user,admin',
            'write_permission' => 'nullable|in:guest,user,admin',
            'comment_permission' => 'nullable|in:guest,user,admin',
            'read_points' => 'nullable|integer',
            'write_points' => 'nullable|integer',
            'delete_points' => 'nullable|integer',
            'comment_points' => 'nullable|integer',
            'comment_delete_points' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // Handle header image upload
        if ($request->hasFile('header_image')) {
            $headerImage = $request->file('header_image');
            $directory = 'board-headers/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($headerImage, $directory);
            $data['header_image_path'] = $result['file_path'];
            
            // Delete old header image if exists
            if ($board->header_image_path) {
                Storage::disk('public')->delete($board->header_image_path);
            }
        }
        
        $this->boardService->update($board, $data);

        return redirect()->route('admin.boards', ['site' => $site->slug])
            ->with('success', '게시판이 수정되었습니다.');
    }

    /**
     * Remove the specified board.
     */
    public function destroy(Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $this->boardService->delete($board);

        return redirect()->route('admin.boards', ['site' => $site->slug])
            ->with('success', '게시판이 삭제되었습니다.');
    }

    /**
     * Delete posts by date range.
     */
    public function deletePostsByDateRange(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $deletedCount = Post::where('board_id', $board->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$deletedCount}개의 게시글이 삭제되었습니다."
            ]);
        }

        return back()->with('success', "{$deletedCount}개의 게시글이 삭제되었습니다.");
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
        
        // enable_comments 컬럼이 없으면 자동으로 생성
        if (!Schema::hasColumn('boards', 'enable_comments')) {
            try {
                Schema::table('boards', function (Blueprint $table) {
                    $table->boolean('enable_comments')->default(true)->after('enable_reply')->comment('댓글 기능 활성화');
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create enable_comments column: ' . $e->getMessage());
            }
        }
        
        // saved_posts_enabled 컬럼이 없으면 자동으로 생성
        if (!Schema::hasColumn('boards', 'saved_posts_enabled')) {
            try {
                Schema::table('boards', function (Blueprint $table) {
                    $table->boolean('saved_posts_enabled')->default(false)->after('enable_likes')->comment('저장하기 기능 활성화');
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create saved_posts_enabled column: ' . $e->getMessage());
            }
        }

        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|in:general,photo,bookmark,blog,classic,instagram,event,one_on_one,pinterest,qa',
            'event_display_type' => 'nullable|in:photo,general',
            'summary_length' => 'nullable|integer|min:50|max:500',
            'pinterest_columns_mobile' => 'nullable|integer|min:1|max:6',
            'pinterest_columns_tablet' => 'nullable|integer|min:1|max:6',
            'pinterest_columns_desktop' => 'nullable|integer|min:1|max:6',
            'pinterest_columns_large' => 'nullable|integer|min:1|max:12',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:boards,slug,' . $board->id . ',id,site_id,' . $site->id,
            'description' => 'nullable|string',
            'max_posts_per_day' => 'nullable|integer|min:0',
            'posts_per_page' => 'nullable|integer|min:1',
            'random_order' => 'nullable',
            'allow_multiple_topics' => 'nullable',
            'remove_links' => 'nullable',
            'enable_likes' => 'nullable',
            'saved_posts_enabled' => 'nullable',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $updateData = [];
        
        // hide_title_description 처리 (항상 처리 - 체크 해제 시에도 저장)
        // FormData에서 직접 가져오거나 기본값 '0' 사용
        // $request->has()를 사용하지 않고 항상 input()으로 가져옴 (체크 해제 시에도 저장되도록)
        $hideTitleDescription = $request->input('hide_title_description', '0');
        // 값이 null이거나 빈 문자열이면 기본값 '0' 사용
        if ($hideTitleDescription === null || $hideTitleDescription === '') {
            $hideTitleDescription = '0';
        }
        // 문자열 '1', 숫자 1, boolean true 모두 체크됨으로 처리
        // boolean으로 명시적으로 변환하여 저장 (데이터베이스에 0 또는 1로 저장됨)
        $updateData['hide_title_description'] = (bool)($hideTitleDescription == '1' || $hideTitleDescription === true || $hideTitleDescription === 'true' || $hideTitleDescription === 1 || $hideTitleDescription === 'on');
        
        // 일반 설정 필드는 요청에 있을 때만 업데이트 (기능 ON/OFF 폼에서는 제외)
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->has('slug')) {
            $updateData['slug'] = $request->slug ?? \Illuminate\Support\Str::slug($request->name);
        }
        if ($request->has('description')) {
            $updateData['description'] = $request->description ?? '';
        }
        if ($request->has('max_posts_per_day')) {
            $updateData['max_posts_per_day'] = $request->max_posts_per_day ?? 0;
        }
        if ($request->has('posts_per_page')) {
            $updateData['posts_per_page'] = $request->posts_per_page ?? 20;
        }
        
        // hide_title_description 저장 전 로그
        \Log::info('hide_title_description before update:', [
            'request_all' => $request->all(),
            'request_input' => $request->input('hide_title_description'),
            'request_has' => $request->has('hide_title_description'),
            'parsed_value' => $hideTitleDescription,
            'final_value' => $updateData['hide_title_description'],
            'board_id' => $board->id,
            'current_value' => $board->hide_title_description,
            'updateData' => $updateData
        ]);
        
        // Handle header image upload
        if ($request->hasFile('header_image')) {
            $headerImage = $request->file('header_image');
            $directory = 'board-headers/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($headerImage, $directory);
            $updateData['header_image_path'] = $result['file_path'];
            
            // Delete old header image if exists
            if ($board->header_image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($board->header_image_path);
            }
        }

        // 게시판 타입 업데이트
        if ($request->has('type')) {
            $updateData['type'] = $request->type;
        }
        
        // 이벤트 표시 타입 업데이트
        if ($request->has('event_display_type')) {
            $updateData['event_display_type'] = $request->event_display_type;
        } elseif ($request->input('type') === 'event' && !$request->has('event_display_type')) {
            // 이벤트 타입인데 event_display_type이 없으면 기본값 photo로 설정
            $updateData['event_display_type'] = 'photo';
        }
        
        // 블로그 타입인 경우 summary_length 처리
        // 컬럼이 존재하는지 확인 후 처리
        if (\Illuminate\Support\Facades\Schema::hasColumn('boards', 'summary_length')) {
            if ($request->has('summary_length')) {
                $updateData['summary_length'] = $request->summary_length;
            } elseif ($request->input('type') === 'blog' && !$request->has('summary_length')) {
                // 블로그 타입인데 summary_length가 없으면 기본값 150으로 설정
                $updateData['summary_length'] = 150;
            } elseif ($request->input('type') !== 'blog') {
                // 블로그 타입이 아니면 summary_length를 null로 설정
                $updateData['summary_length'] = null;
            }
        }
        
        // 핀터레스트 컬럼이 없으면 자동으로 생성
        if (!Schema::hasColumn('boards', 'pinterest_columns_mobile')) {
            try {
                Schema::table('boards', function (Blueprint $table) {
                    $table->integer('pinterest_columns_mobile')->default(2)->nullable()->after('posts_per_page')->comment('핀터레스트 모바일 가로 개수');
                    $table->integer('pinterest_columns_tablet')->default(3)->nullable()->after('pinterest_columns_mobile')->comment('핀터레스트 태블릿 가로 개수');
                    $table->integer('pinterest_columns_desktop')->default(4)->nullable()->after('pinterest_columns_tablet')->comment('핀터레스트 데스크톱 가로 개수');
                    $table->integer('pinterest_columns_large')->default(6)->nullable()->after('pinterest_columns_desktop')->comment('핀터레스트 큰 화면 가로 개수');
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create pinterest columns: ' . $e->getMessage());
            }
        }
        
        // 핀터레스트 타입인 경우 컬럼 설정 처리
        $boardType = $request->input('type', $board->type);
        
        // 핀터레스트 컬럼 설정이 요청에 있으면 항상 저장
        // $request->input()으로 직접 확인 (null이 아닌 경우)
        $pinterestMobile = $request->input('pinterest_columns_mobile');
        $pinterestTablet = $request->input('pinterest_columns_tablet');
        $pinterestDesktop = $request->input('pinterest_columns_desktop');
        $pinterestLarge = $request->input('pinterest_columns_large');
        
        if ($pinterestMobile !== null && $pinterestMobile !== '') {
            $updateData['pinterest_columns_mobile'] = (int)$pinterestMobile;
        }
        if ($pinterestTablet !== null && $pinterestTablet !== '') {
            $updateData['pinterest_columns_tablet'] = (int)$pinterestTablet;
        }
        if ($pinterestDesktop !== null && $pinterestDesktop !== '') {
            $updateData['pinterest_columns_desktop'] = (int)$pinterestDesktop;
        }
        if ($pinterestLarge !== null && $pinterestLarge !== '') {
            $updateData['pinterest_columns_large'] = (int)$pinterestLarge;
        }
        
        // 핀터레스트 타입이고 값이 없으면 기본값 설정
        if ($boardType === 'pinterest') {
            if (!isset($updateData['pinterest_columns_mobile']) && !$board->pinterest_columns_mobile) {
                $updateData['pinterest_columns_mobile'] = 2;
            }
            if (!isset($updateData['pinterest_columns_tablet']) && !$board->pinterest_columns_tablet) {
                $updateData['pinterest_columns_tablet'] = 3;
            }
            if (!isset($updateData['pinterest_columns_desktop']) && !$board->pinterest_columns_desktop) {
                $updateData['pinterest_columns_desktop'] = 4;
            }
            if (!isset($updateData['pinterest_columns_large']) && !$board->pinterest_columns_large) {
                $updateData['pinterest_columns_large'] = 6;
            }
        } elseif ($request->has('type') && $request->input('type') !== 'pinterest') {
            // 타입이 핀터레스트가 아닌 다른 타입으로 변경된 경우 null로 설정
            $updateData['pinterest_columns_mobile'] = null;
            $updateData['pinterest_columns_tablet'] = null;
            $updateData['pinterest_columns_desktop'] = null;
            $updateData['pinterest_columns_large'] = null;
        }

        // 랜덤 배치는 북마크 타입일 때만 적용 (항상 처리)
        $boardType = $request->input('type', $board->type);
        if ($boardType === 'bookmark') {
            $randomOrder = $request->input('random_order', '0');
            // 명시적으로 boolean으로 변환
            $updateData['random_order'] = ($randomOrder == '1' || $randomOrder === true || $randomOrder === 'true' || $randomOrder === 1);
        } else {
            // 북마크가 아닌 경우 false로 설정
            $updateData['random_order'] = false;
        }
        
        // allow_multiple_topics 처리 (항상 처리)
        $allowMultiple = $request->input('allow_multiple_topics', '0');
        $updateData['allow_multiple_topics'] = ($allowMultiple == '1' || $allowMultiple === true || $allowMultiple === 'true' || $allowMultiple === 1);
        
        // remove_links 처리 (항상 처리)
        $removeLinks = $request->input('remove_links', '0');
        $updateData['remove_links'] = ($removeLinks == '1' || $removeLinks === true || $removeLinks === 'true' || $removeLinks === 1);
        
        // enable_likes 처리 (항상 처리)
        $enableLikes = $request->input('enable_likes', '0');
        $updateData['enable_likes'] = ($enableLikes == '1' || $enableLikes === true || $enableLikes === 'true' || $enableLikes === 1);
        
        // saved_posts_enabled 처리 (항상 처리 - 다른 체크박스들과 동일)
        if (Schema::hasColumn('boards', 'saved_posts_enabled')) {
            $savedPostsEnabled = $request->input('saved_posts_enabled', '0');
            $updateData['saved_posts_enabled'] = ($savedPostsEnabled == '1' || $savedPostsEnabled === true || $savedPostsEnabled === 'true' || $savedPostsEnabled === 1);
        } else {
            // 컬럼이 없으면 자동으로 생성
            try {
                Schema::table('boards', function (Blueprint $table) {
                    $table->boolean('saved_posts_enabled')->default(false)->after('enable_likes')->comment('저장하기 기능 활성화');
                });
                $savedPostsEnabled = $request->input('saved_posts_enabled', '0');
                $updateData['saved_posts_enabled'] = ($savedPostsEnabled == '1' || $savedPostsEnabled === true || $savedPostsEnabled === 'true' || $savedPostsEnabled === 1);
            } catch (\Exception $e) {
                \Log::error('Failed to create saved_posts_enabled column: ' . $e->getMessage());
            }
        }
        
        // 질의응답 게시판 상태 설정 처리
        if (Schema::hasColumn('boards', 'qa_statuses')) {
            if ($request->has('qa_statuses') || $request->filled('qa_statuses')) {
                $qaStatuses = $request->input('qa_statuses', []);
                if (is_array($qaStatuses) && !empty($qaStatuses)) {
                    // 상태 배열을 정리하여 저장
                    $statuses = [];
                    foreach ($qaStatuses as $index => $status) {
                        if (isset($status['name']) && !empty(trim($status['name'])) && isset($status['color']) && !empty(trim($status['color']))) {
                            $statuses[] = [
                                'name' => trim($status['name']),
                                'color' => trim($status['color']),
                                'order' => (int)$index
                            ];
                        }
                    }
                    if (!empty($statuses)) {
                        $updateData['qa_statuses'] = $statuses;
                    }
                }
            } elseif ($boardType === 'qa' && !$board->qa_statuses) {
                // 질의응답 타입이고 상태가 없으면 기본값 설정
                $updateData['qa_statuses'] = [
                    ['name' => '답변대기', 'color' => '#ffc107', 'order' => 0],
                    ['name' => '답변완료', 'color' => '#28a745', 'order' => 1]
                ];
            }
        }
        
        // post_template 처리
        if ($request->has('post_template')) {
            $updateData['post_template'] = $request->post_template;
        }

        // hide_title_description이 $updateData에 포함되어 있는지 확인하고 강제로 포함
        if (!isset($updateData['hide_title_description'])) {
            // 만약 $updateData에 없으면 다시 설정
            $hideTitleDescription = $request->input('hide_title_description', '0');
            if ($hideTitleDescription === null || $hideTitleDescription === '') {
                $hideTitleDescription = '0';
            }
            $updateData['hide_title_description'] = (bool)($hideTitleDescription == '1' || $hideTitleDescription === true || $hideTitleDescription === 'true' || $hideTitleDescription === 1 || $hideTitleDescription === 'on');
        }
        
        // hide_title_description이 $updateData에 포함되어 있는지 확인
        \Log::info('hide_title_description in updateData before update:', [
            'in_updateData' => isset($updateData['hide_title_description']),
            'value' => $updateData['hide_title_description'] ?? 'not set',
            'type' => isset($updateData['hide_title_description']) ? gettype($updateData['hide_title_description']) : 'not set',
            'updateData_keys' => array_keys($updateData)
        ]);
        
        // update() 메서드 사용
        $board->update($updateData);
        
        // hide_title_description을 명시적으로 다시 설정 (이중 확인)
        // Eloquent를 통한 업데이트
        $board->hide_title_description = $updateData['hide_title_description'];
        $board->save();
        
        // DB 쿼리 빌더를 통한 직접 업데이트 (최종 확인)
        $dbValue = $updateData['hide_title_description'] ? 1 : 0;
        \DB::table('boards')
            ->where('id', $board->id)
            ->update(['hide_title_description' => $dbValue]);
        
        // refresh 전에 다시 로드하여 최신 값 확인
        $board = $board->fresh();
        
        // hide_title_description 저장 확인 로그
        \Log::info('hide_title_description after update:', [
            'value' => $board->hide_title_description,
            'type' => gettype($board->hide_title_description),
            'raw' => $board->getRawOriginal('hide_title_description'),
            'is_true' => $board->hide_title_description === true,
            'is_1' => $board->hide_title_description === 1,
            'is_string_1' => $board->hide_title_description === '1'
        ]);
        
        // saved_posts_enabled 저장 확인
        if (Schema::hasColumn('boards', 'saved_posts_enabled')) {
            \Log::info('saved_posts_enabled after refresh:', ['value' => $board->saved_posts_enabled, 'type' => gettype($board->saved_posts_enabled)]);
        }

        // AJAX 요청인 경우 JSON 응답
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $response = [
                'success' => true,
                'message' => '일반 설정이 저장되었습니다.',
                'hide_title_description' => $board->hide_title_description ? 1 : 0
            ];
            
            // saved_posts_enabled 저장 확인을 위해 응답에 포함
            if (Schema::hasColumn('boards', 'saved_posts_enabled')) {
                $response['saved_posts_enabled'] = $board->saved_posts_enabled;
            }
            
            return response()->json($response);
        }
        
        // old() 값을 초기화하기 위해 redirect 대신 route로 이동
        return redirect()->route('boards.edit', ['site' => $site->slug, 'board' => $board->id])
            ->with('success', '일반 설정이 저장되었습니다.');
    }

    /**
     * Update features (ON/OFF) settings.
     */
    public function updateFeatures(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
        
        // enable_comments 컬럼이 없으면 자동으로 생성
        if (!Schema::hasColumn('boards', 'enable_comments')) {
            try {
                Schema::table('boards', function (Blueprint $table) {
                    $table->boolean('enable_comments')->default(true)->after('enable_reply')->comment('댓글 기능 활성화');
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create enable_comments column: ' . $e->getMessage());
            }
        }

        $validator = Validator::make($request->all(), [
            'enable_anonymous' => 'nullable',
            'enable_secret' => 'nullable',
            'force_secret' => 'nullable',
            'enable_reply' => 'nullable',
            'enable_comments' => 'nullable',
            'exclude_from_rss' => 'nullable',
            'prevent_drag' => 'nullable',
            'enable_attachments' => 'nullable',
            'enable_author_comment_adopt' => 'nullable',
            'enable_admin_comment_adopt' => 'nullable',
            'enable_share' => 'nullable',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $updateData = [];
        
        // 기능 ON/OFF 필드만 처리
        $updateData['enable_anonymous'] = ($request->input('enable_anonymous', '0') == '1' || $request->input('enable_anonymous') === true || $request->input('enable_anonymous') === 'true' || $request->input('enable_anonymous') === 1);
        $updateData['enable_secret'] = ($request->input('enable_secret', '0') == '1' || $request->input('enable_secret') === true || $request->input('enable_secret') === 'true' || $request->input('enable_secret') === 1);
        $updateData['force_secret'] = ($request->input('force_secret', '0') == '1' || $request->input('force_secret') === true || $request->input('force_secret') === 'true' || $request->input('force_secret') === 1);
        $updateData['enable_reply'] = ($request->input('enable_reply', '0') == '1' || $request->input('enable_reply') === true || $request->input('enable_reply') === 'true' || $request->input('enable_reply') === 1);
        // enable_comments 처리 (컬럼이 존재하는 경우에만 업데이트)
        if (Schema::hasColumn('boards', 'enable_comments')) {
            $enableCommentsValue = $request->input('enable_comments', '0');
            $updateData['enable_comments'] = ($enableCommentsValue == '1' || $enableCommentsValue === true || $enableCommentsValue === 'true' || $enableCommentsValue === 1);
        }
        $updateData['exclude_from_rss'] = ($request->input('exclude_from_rss', '0') == '1' || $request->input('exclude_from_rss') === true || $request->input('exclude_from_rss') === 'true' || $request->input('exclude_from_rss') === 1);
        $updateData['prevent_drag'] = ($request->input('prevent_drag', '0') == '1' || $request->input('prevent_drag') === true || $request->input('prevent_drag') === 'true' || $request->input('prevent_drag') === 1);
        // enable_attachments는 기본값 '0'으로 변경 (체크 해제 시 저장되도록)
        $enableAttachmentsValue = $request->input('enable_attachments', '0');
        $updateData['enable_attachments'] = ($enableAttachmentsValue == '1' || $enableAttachmentsValue === true || $enableAttachmentsValue === 'true' || $enableAttachmentsValue === 1);
        $updateData['enable_author_comment_adopt'] = ($request->input('enable_author_comment_adopt', '0') == '1' || $request->input('enable_author_comment_adopt') === true || $request->input('enable_author_comment_adopt') === 'true' || $request->input('enable_author_comment_adopt') === 1);
        $updateData['enable_admin_comment_adopt'] = ($request->input('enable_admin_comment_adopt', '0') == '1' || $request->input('enable_admin_comment_adopt') === true || $request->input('enable_admin_comment_adopt') === 'true' || $request->input('enable_admin_comment_adopt') === 1);
        // enable_share 처리 - 컬럼이 없으면 생성하고 항상 업데이트
        if (!Schema::hasColumn('boards', 'enable_share')) {
            try {
                Schema::table('boards', function (Blueprint $table) {
                    $table->boolean('enable_share')->default(false)->after('enable_attachments')->comment('공유 기능 활성화');
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create enable_share column: ' . $e->getMessage());
            }
        }
        $enableShareValue = $request->input('enable_share', '0');
        $updateData['enable_share'] = ($enableShareValue == '1' || $enableShareValue === true || $enableShareValue === 'true' || $enableShareValue === 1);
        
        // 디버깅: enable_share 값 로깅
        \Log::info('enable_share update', [
            'request_value' => $request->input('enable_share'),
            'parsed_value' => $enableShareValue,
            'final_value' => $updateData['enable_share'],
            'board_id' => $board->id
        ]);

        $board->update($updateData);
        $board->refresh();
        
        // 디버깅: 업데이트 후 실제 DB 값 확인
        \Log::info('enable_share after update', [
            'board_id' => $board->id,
            'enable_share' => $board->enable_share,
            'enable_share_type' => gettype($board->enable_share)
        ]);

        // AJAX 요청인 경우 JSON 응답
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '기능 설정이 저장되었습니다.'
            ]);
        }
        
        return redirect()->route('boards.edit', ['site' => $site->slug, 'board' => $board->id])
            ->with('success', '기능 설정이 저장되었습니다.');
    }

    /**
     * Update SEO settings.
     */
    public function updateSeo(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $validator = Validator::make($request->all(), [
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $board->update([
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'SEO 설정이 저장되었습니다.'
            ]);
        }

        return back()->with('success', 'SEO 설정이 저장되었습니다.');
    }

    /**
     * Update grade and points settings.
     */
    public function updateGradePoints(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $validator = Validator::make($request->all(), [
            'read_permission' => 'nullable|in:guest,user,admin',
            'write_permission' => 'nullable|in:guest,user,admin',
            'delete_permission' => 'nullable|in:author,admin',
            'comment_permission' => 'nullable|in:guest,user,admin',
            'comment_delete_permission' => 'nullable|in:author,admin',
            'read_points' => 'nullable|integer',
            'write_points' => 'nullable|integer',
            'delete_points' => 'nullable|integer',
            'comment_points' => 'nullable|integer',
            'comment_delete_points' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $board->update([
            'read_permission' => $request->read_permission ?? 'guest',
            'write_permission' => $request->write_permission ?? 'user',
            'delete_permission' => $request->delete_permission ?? 'author',
            'comment_permission' => $request->comment_permission ?? 'user',
            'comment_delete_permission' => $request->comment_delete_permission ?? 'author',
            'read_points' => $request->read_points ?? 0,
            'write_points' => $request->write_points ?? 0,
            'delete_points' => $request->delete_points ?? 0,
            'comment_points' => $request->comment_points ?? 0,
            'comment_delete_points' => $request->comment_delete_points ?? 0,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '등급 & 포인트 설정이 저장되었습니다.'
            ]);
        }

        return back()->with('success', '등급 & 포인트 설정이 저장되었습니다.');
    }

    /**
     * Update post template.
     */
    public function updateTemplate(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $validator = Validator::make($request->all(), [
            'post_template' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $board->update([
            'post_template' => $request->post_template,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '게시글 양식이 저장되었습니다.'
            ]);
        }

        return back()->with('success', '게시글 양식이 저장되었습니다.');
    }

    /**
     * Update footer content.
     */
    public function updateFooter(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $validator = Validator::make($request->all(), [
            'footer_content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $board->update([
            'footer_content' => $request->footer_content,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '게시판 하단 내용이 저장되었습니다.'
            ]);
        }

        return back()->with('success', '게시판 하단 내용이 저장되었습니다.');
    }

    /**
     * Store a new topic.
     */
    public function storeTopic(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $maxOrder = Topic::where('board_id', $board->id)->max('display_order') ?? 0;

        $topic = Topic::create([
            'board_id' => $board->id,
            'name' => $request->name,
            'color' => $request->color ?? '#007bff',
            'display_order' => $maxOrder + 1,
        ]);

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '주제가 추가되었습니다.',
                'topic' => $topic
            ]);
        }

        return back()->with('success', '주제가 추가되었습니다.');
    }

    /**
     * Update a topic.
     */
    public function updateTopic(Request $request, Site $site, Board $board, Topic $topic)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        // Verify topic belongs to board
        if ($topic->board_id !== $board->id) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'display_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $topic->update([
            'name' => $request->name,
            'color' => $request->color ?? $topic->color,
            'display_order' => $request->display_order ?? $topic->display_order,
        ]);

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '주제가 수정되었습니다.',
                'topic' => $topic->fresh()
            ]);
        }

        return back()->with('success', '주제가 수정되었습니다.');
    }

    /**
     * Delete a topic.
     */
    public function destroyTopic(Site $site, Board $board, Topic $topic)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        // Verify topic belongs to board
        if ($topic->board_id !== $board->id) {
            abort(403, 'Unauthorized action.');
        }

        $topic->delete();

        if (request()->expectsJson() || request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '주제가 삭제되었습니다.'
            ]);
        }

        return back()->with('success', '주제가 삭제되었습니다.');
    }

    /**
     * Update topic order.
     */
    public function updateTopicOrder(Request $request, Site $site, Board $board)
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $validator = Validator::make($request->all(), [
            'topic_ids' => 'required|array',
            'topic_ids.*' => 'exists:topics,id',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator);
        }

        foreach ($request->topic_ids as $order => $topicId) {
            Topic::where('id', $topicId)
                ->where('board_id', $board->id)
                ->update(['display_order' => $order + 1]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '주제 순서가 변경되었습니다.'
            ]);
        }

        return back()->with('success', '주제 순서가 변경되었습니다.');
    }
}
