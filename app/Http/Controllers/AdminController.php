<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Models\Board;
use App\Models\Post;
use App\Models\Visitor;
use App\Models\Menu;
use App\Models\MobileMenu;
use App\Models\Message;
use App\Models\Banner;
use App\Models\Popup;
use App\Models\UserRank;
use App\Models\CustomCode;
use App\Models\SidebarWidget;
use App\Models\MainWidgetContainer;
use App\Models\MainWidget;
use App\Models\CustomPage;
use App\Models\CustomPageWidgetContainer;
use App\Models\CustomPageWidget;
use App\Models\ContactForm;
use App\Models\ContactFormSubmission;
use App\Models\Map;
use App\Models\Crawler;
use App\Models\Topic;
use App\Models\ToggleMenu;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class AdminController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            // 마스터 관리자는 모든 사이트에 접근 가능 (SSO 로그인 시 세션에 저장된 정보 확인)
            $isMasterUser = session('is_master_user', false) || auth('master')->check();
            
            if (!$isMasterUser) {
                if (!$user) {
                    abort(403, 'Unauthorized action.');
                }
                
                // 사용자가 관리 권한이 있는지 확인
                if (!$user->canManage()) {
                    abort(403, 'Unauthorized action.');
                }
                
                // 사이트별 사용자 검증 (마스터 관리자가 아닌 경우만)
                if ($request->route('site')) {
                    $site = $request->route('site');
                    if ($user->site_id !== $site->id) {
                        abort(403, '이 사이트에 대한 접근 권한이 없습니다.');
                    }
                }
            } else {
                // 마스터 사용자인 경우, 사용자가 없어도 통과하되 사용자가 있으면 역할 확인
                if ($user && !$user->canManage()) {
                    // 마스터 사용자로 SSO 로그인한 경우 역할을 admin으로 업데이트
                    if ($user->role !== 'admin') {
                        $user->update(['role' => 'admin']);
                    }
                }
            }
            
            return $next($request);
        });
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display admin dashboard.
     */
    public function dashboard(Site $site)
    {
        $stats = [
            'users' => User::where('site_id', $site->id)->count(),
            'boards' => Board::where('site_id', $site->id)->count(),
            'posts' => Post::where('site_id', $site->id)->count(),
            'comments' => \App\Models\Comment::whereHas('post', function($q) use ($site) {
                $q->where('site_id', $site->id);
            })->count(),
            'today_posts' => Post::where('site_id', $site->id)
                ->whereDate('created_at', today())
                ->count(),
            'today_users' => User::where('site_id', $site->id)
                ->whereDate('created_at', today())
                ->count(),
        ];

        // 최근 활동 (비밀글 제외)
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
                    if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                        $q2->where('is_secret', false)
                           ->orWhereNull('is_secret');
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentComments = \App\Models\Comment::whereHas('post', function($q) use ($site) {
                $q->where('site_id', $site->id);
            })
            ->with(['user', 'post'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentUsers = User::where('site_id', $site->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('site', 'stats', 'recentPosts', 'recentComments', 'recentUsers'));
    }

    /**
     * Get chart data for dashboard.
     */
    public function getChartData(Site $site, Request $request)
    {
        $type = $request->get('type', 'posts'); // posts, users, comments
        $period = $request->get('period', 'week'); // week, month, year

        $data = [];
        $labels = [];

        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : 365);
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('m/d');
            
            switch ($type) {
                case 'posts':
                    $count = Post::where('site_id', $site->id)
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->count();
                    break;
                case 'users':
                    $count = User::where('site_id', $site->id)
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->count();
                    break;
                case 'comments':
                    $count = \App\Models\Comment::whereHas('post', function($q) use ($site) {
                            $q->where('site_id', $site->id);
                        })
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->count();
                    break;
                case 'visitors':
                    $count = \App\Models\Visitor::where('site_id', $site->id)
                        ->whereDate('visited_date', $date->format('Y-m-d'))
                        ->distinct('ip_address')
                        ->count('ip_address');
                    break;
                case 'views':
                    // 해당 날짜에 생성된 게시글의 조회수 합계
                    // 실제로는 날짜별 조회수 증가량을 추적하기 어려우므로,
                    // 해당 날짜에 생성된 게시글의 조회수 합계를 사용
                    $count = Post::where('site_id', $site->id)
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->sum('views');
                    break;
                default:
                    $count = 0;
            }
            
            $data[] = $count;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Display users management.
     */
    public function users(Site $site, Request $request)
    {
        $query = User::where('site_id', $site->id);
        
        // 마스터 사용자 제외 (마스터 사용자의 이메일 목록 가져오기)
        $masterUserEmails = \App\Models\MasterUser::pluck('email')->toArray();
        if (!empty($masterUserEmails)) {
            $query->whereNotIn('email', $masterUserEmails);
        }
        
        // 검색 기능
        if ($request->filled('search')) {
            $search = $request->get('search');
            $searchType = $request->get('search_type', 'all');
            
            if ($searchType === 'username') {
                $query->where('username', 'like', "%{$search}%");
            } elseif ($searchType === 'name') {
                $query->where('name', 'like', "%{$search}%");
            } elseif ($searchType === 'nickname') {
                $query->where('nickname', 'like', "%{$search}%");
            } else {
                // 전체 검색
                $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('nickname', 'like', "%{$search}%");
                });
            }
        }
        
        // 정렬 기능
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // 페이지당 항목 수 (기본 15개)
        $perPage = $request->get('per_page', 15);
        
        if ($sortBy === 'rank') {
            // 등급 정렬: 모든 사용자를 가져온 후 메모리에서 등급 rank 값으로 정렬
            // (복잡한 서브쿼리 대신 간단한 방법 사용)
            $allUsers = $query->get();
            
            // 각 사용자의 등급 rank 값 계산
            $usersWithRank = $allUsers->map(function($user) use ($site) {
                $userRank = $user->getUserRank($site->id);
                $rankValue = 0; // 기본값 (가장 낮은 등급: 1등급)
                
                if ($userRank) {
                    $rankValue = $userRank->rank ?? 0;
                } elseif ($user->isAdmin() || $user->isManager()) {
                    $rankValue = 999999; // 관리자/매니저는 최상위
                }
                
                return [
                    'user' => $user,
                    'rank_value' => $rankValue,
                    'points' => $user->points ?? 0
                ];
            });
            
            // 등급 rank 값으로 정렬 (클수록 높은 등급: 1등급 < 2등급 < 3등급)
            if ($sortOrder === 'asc') {
                $usersWithRank = $usersWithRank->sortBy(function($item) {
                    return [-$item['rank_value'], -$item['points']]; // 등급 오름차순(낮은 등급부터), 포인트 내림차순
                });
            } else {
                $usersWithRank = $usersWithRank->sortBy(function($item) {
                    return [$item['rank_value'], -$item['points']]; // 등급 내림차순(높은 등급부터), 포인트 내림차순
                });
            }
            
            // User 모델로 변환
            $sortedUsers = $usersWithRank->pluck('user')->values();
            
            // 페이지네이션을 위해 수동으로 처리
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedUsers = $sortedUsers->slice($offset, $perPage);
            
            // LengthAwarePaginator 생성
            $users = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedUsers,
                $sortedUsers->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } elseif ($sortBy === 'points') {
            $query->orderBy('points', $sortOrder);
            $users = $query->paginate($perPage)->withQueryString();
        } elseif ($sortBy === 'created_at') {
            $query->orderBy('created_at', $sortOrder);
            $users = $query->paginate($perPage)->withQueryString();
        } else {
            // 기본 정렬: 가입일 오름차순 (오래된 사용자부터)
            $query->orderBy('created_at', 'asc');
            $users = $query->paginate($perPage)->withQueryString();
        }

        // No 계산을 위해 전체 사용자 수와 가입 순서 정보 필요 (마스터 사용자 제외)
        $masterUserEmails = \App\Models\MasterUser::pluck('email')->toArray();
        $totalUsersQuery = User::where('site_id', $site->id);
        if (!empty($masterUserEmails)) {
            $totalUsersQuery->whereNotIn('email', $masterUserEmails);
        }
        $totalUsers = $totalUsersQuery->count();
        
        return view('admin.users', compact('site', 'users', 'totalUsers'));
    }

    /**
     * Store a new user.
     */
    public function storeUser(Request $request, Site $site)
    {
        $rules = [
            'username' => 'required|string|max:255|unique:users,username,NULL,id,site_id,' . $site->id,
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255|unique:users,nickname,NULL,id,site_id,' . $site->id,
            'email' => 'required|string|email|max:255|unique:users,email,NULL,id,site_id,' . $site->id,
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'address_detail' => 'nullable|string|max:255',
            'role' => 'required|in:admin,manager,user',
            'points' => 'nullable|integer|min:0',
        ];

        try {
            $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 정보를 확인해주세요.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $userData = [
                'site_id' => $site->id,
                'username' => $request->username,
                'name' => $request->name,
                'nickname' => $request->nickname,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => $request->role,
            ];

            // 전화번호
            if ($request->filled('phone')) {
                $userData['phone'] = $request->phone;
            }

            // 주소
            if ($request->filled('postal_code')) {
                $userData['postal_code'] = $request->postal_code;
            }
            if ($request->filled('address')) {
                $userData['address'] = $request->address;
            }
            if ($request->filled('address_detail')) {
                $userData['address_detail'] = $request->address_detail;
            }

            // 초기 포인트
            $initialPoints = (int)($request->points ?? 0);
            if ($initialPoints > 0) {
                $userData['points'] = $initialPoints;
            }

            $user = User::create($userData);

            // 가입 포인트 지급 (설정에 따라)
            $signupPoints = (int)$site->getSetting('registration_signup_points', 0);
            if ($signupPoints > 0) {
                $user->addPoints($signupPoints);
            }

            return response()->json([
                'success' => true,
                'message' => '사용자가 추가되었습니다.',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '사용자 추가 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display user detail.
     */
    public function userDetail(Site $site, User $user)
    {
        // 사용자가 해당 사이트에 속하는지 확인
        if ($user->site_id !== $site->id) {
            abort(404);
        }

        return view('admin.user-detail', compact('site', 'user'));
    }

    /**
     * Update user information.
     */
    public function updateUser(Request $request, Site $site, User $user)
    {
        // 사용자가 해당 사이트에 속하는지 확인
        if ($user->site_id !== $site->id) {
            abort(404);
        }

        $rules = [
            'username' => 'required|string|max:255|unique:users,username,' . $user->id . ',id,site_id,' . $site->id,
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255|unique:users,nickname,' . $user->id . ',id,site_id,' . $site->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id . ',id,site_id,' . $site->id,
            'phone' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'address_detail' => 'nullable|string|max:255',
            'points' => 'required|integer|min:0',
            'role' => 'required|in:admin,manager,user',
        ];

        // 비밀번호가 입력된 경우에만 검증
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8';
        }

        try {
            $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '입력 정보를 확인해주세요.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $updateData = [
            'username' => $request->username,
            'name' => $request->name,
            'nickname' => $request->nickname,
            'email' => $request->email,
            'phone' => $request->phone,
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'address_detail' => $request->address_detail,
            'points' => $request->points,
            'role' => $request->role,
        ];

        // 비밀번호가 입력된 경우에만 업데이트
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '사용자 정보가 성공적으로 수정되었습니다.',
            ]);
        }

        return redirect()->route('admin.users.detail', ['site' => $site->slug, 'user' => $user->id])
            ->with('success', '사용자 정보가 성공적으로 수정되었습니다.');
    }

    /**
     * Display boards management.
     */
    public function boards(Site $site)
    {
        $boards = Board::where('site_id', $site->id)->ordered()->paginate(15);

        return view('admin.boards', compact('site', 'boards'));
    }

    /**
     * Get topics for a board.
     */
    public function getBoardTopics(Site $site, Board $board)
    {
        if ($board->site_id !== $site->id) {
            abort(403);
        }

        $topics = Topic::where('board_id', $board->id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'topics' => $topics,
        ]);
    }

    /**
     * Update banned words.
     */
    public function updateBannedWords(Request $request, Site $site)
    {
        $request->validate([
            'banned_words' => 'nullable|string',
        ]);

        $site->setSetting('banned_words', $request->input('banned_words', ''));

        return response()->json([
            'success' => true,
            'message' => '금지단어가 저장되었습니다.',
        ]);
    }

    /**
     * Display posts management.
     */
    public function posts(Site $site, Request $request)
    {
        $query = Post::where('site_id', $site->id)
            ->with(['board', 'user']);

        // 게시판 필터
        if ($request->filled('board_id')) {
            $query->where('board_id', $request->board_id);
        }

        // 작성자 검색
        if ($request->filled('author')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->author . '%');
            });
        }

        // 날짜 필터 (시작일)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // 날짜 필터 (종료일)
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 조회수 필터 (최소)
        if ($request->filled('views_min')) {
            $query->where('views', '>=', $request->views_min);
        }

        // 조회수 필터 (최대)
        if ($request->filled('views_max')) {
            $query->where('views', '<=', $request->views_max);
        }

        // 정렬
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 페이지당 항목 수
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 30, 50, 100]) ? $perPage : 20;

        $posts = $query->paginate($perPage)->withQueryString();

        // 게시판 목록 (필터용)
        $boards = Board::where('site_id', $site->id)->ordered()->get();

        return view('admin.posts', compact('site', 'posts', 'boards'));
    }

    /**
     * Update post board.
     */
    public function updatePostBoard(Site $site, Post $post, Request $request)
    {
        $request->validate([
            'board_id' => 'required|exists:boards,id',
        ]);

        // 게시판이 같은 사이트에 속하는지 확인
        $board = Board::findOrFail($request->board_id);
        if ($board->site_id !== $site->id) {
            return response()->json(['success' => false, 'message' => '잘못된 게시판입니다.'], 400);
        }

        $post->board_id = $request->board_id;
        $post->save();

        return response()->json([
            'success' => true,
            'message' => '게시판이 변경되었습니다.',
            'board_name' => $board->name,
        ]);
    }

    /**
     * Update post views.
     */
    public function updatePostViews(Site $site, Post $post, Request $request)
    {
        $request->validate([
            'views' => 'required|integer|min:0',
        ]);

        $post->views = $request->views;
        $post->save();

        return response()->json([
            'success' => true,
            'message' => '조회수가 변경되었습니다.',
            'views' => number_format($post->views),
        ]);
    }

    /**
     * Display menu management page.
     */
    public function menus(Site $site)
    {
        // 테이블이 없으면 자동으로 생성
        if (!\Illuminate\Support\Facades\Schema::hasTable('menus')) {
            \Illuminate\Support\Facades\Schema::create('menus', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                $table->string('name');
                $table->string('link_type');
                $table->string('link_target')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
                
                $table->index(['site_id', 'parent_id']);
                $table->index('order');
            });
            
            // parent_id 외래 키는 테이블 생성 후 추가
            \Illuminate\Support\Facades\Schema::table('menus', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');
            });
        }
        
        $menus = Menu::where('site_id', $site->id)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();
        
        $boards = Board::where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $customPages = CustomPage::where('site_id', $site->id)
            ->orderBy('name')
            ->get();

        // 모바일 메뉴 로드
        $mobileMenus = collect([]);
        if (\Illuminate\Support\Facades\Schema::hasTable('mobile_menus')) {
            $mobileMenus = MobileMenu::where('site_id', $site->id)
                ->orderBy('order')
                ->get();
        }

        // 모바일 메뉴 디자인 타입 설정 로드
        $mobileMenuDesignType = $site->getSetting('mobile_menu_design_type', 'default');

        return view('admin.menus', compact('site', 'menus', 'boards', 'customPages', 'mobileMenus', 'mobileMenuDesignType'));
    }

    /**
     * Store a new menu item.
     */
    public function storeMenu(Site $site, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'link_type' => 'required|in:board,custom_page,external_link,attendance,point_exchange,event_application',
            'link_target' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
        ]);

        // 게시판 타입인 경우 link_target이 게시판 ID인지 확인
        if ($request->link_type === 'board') {
            $board = Board::where('site_id', $site->id)
                ->where('id', $request->link_target)
                ->first();
            
            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => '존재하지 않는 게시판입니다.',
                ], 400);
            }
        }

        // 커스텀 페이지 타입인 경우 link_target이 커스텀 페이지 ID인지 확인
        if ($request->link_type === 'custom_page') {
            $customPage = CustomPage::where('site_id', $site->id)
                ->where('id', $request->link_target)
                ->first();
            
            if (!$customPage) {
                return response()->json([
                    'success' => false,
                    'message' => '존재하지 않는 커스텀 페이지입니다.',
                ], 400);
            }
        }

        // 외부 링크인 경우 URL 형식 확인
        if ($request->link_type === 'external_link') {
            if (empty($request->link_target)) {
                return response()->json([
                    'success' => false,
                    'message' => '외부 링크 URL을 입력해주세요.',
                ], 400);
            }
        }

        // 부모 메뉴가 같은 사이트에 속하는지 확인
        if ($request->parent_id) {
            $parent = Menu::where('site_id', $site->id)
                ->where('id', $request->parent_id)
                ->first();
            
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => '잘못된 부모 메뉴입니다.',
                ], 400);
            }
        }

        // 테이블이 없으면 자동으로 생성
        if (!\Illuminate\Support\Facades\Schema::hasTable('menus')) {
            \Illuminate\Support\Facades\Schema::create('menus', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                $table->string('name');
                $table->string('link_type');
                $table->string('link_target')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
                
                $table->index(['site_id', 'parent_id']);
                $table->index('order');
            });
            
            // parent_id 외래 키는 테이블 생성 후 추가
            \Illuminate\Support\Facades\Schema::table('menus', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');
            });
        }

        // 순서 결정
        $maxOrder = Menu::where('site_id', $site->id)
            ->where('parent_id', $request->parent_id)
            ->max('order') ?? 0;

        $menu = Menu::create([
            'site_id' => $site->id,
            'name' => $request->name,
            'link_type' => $request->link_type,
            'link_target' => $request->link_target,
            'parent_id' => $request->parent_id,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => '메뉴가 추가되었습니다.',
            'menu' => $menu->load('children'),
        ]);
    }

    /**
     * Update menu order.
     */
    public function updateMenuOrder(Site $site, Request $request)
    {
        $request->validate([
            'menus' => 'required|array',
            'menus.*.id' => 'required|exists:menus,id',
            'menus.*.order' => 'required|integer',
        ]);

        foreach ($request->menus as $menuData) {
            $menu = Menu::where('site_id', $site->id)
                ->where('id', $menuData['id'])
                ->first();
            
            if ($menu) {
                $menu->update([
                    'order' => $menuData['order'],
                    'parent_id' => $menuData['parent_id'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '메뉴 순서가 저장되었습니다.',
        ]);
    }

    /**
     * Delete a menu item.
     */
    public function deleteMenu(Site $site, Menu $menu)
    {
        // 같은 사이트에 속하는지 확인
        if ($menu->site_id !== $site->id) {
            abort(403, 'Unauthorized action.');
        }

        // 하위 메뉴도 함께 삭제됨 (cascade)
        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => '메뉴가 삭제되었습니다.',
        ]);
    }

    /**
     * Store a new mobile menu item.
     */
    public function storeMobileMenu(Site $site, Request $request)
    {
        // mobile_menus 테이블이 존재하는지 확인하고, name 컬럼이 nullable이 아니면 수정
        if (Schema::hasTable('mobile_menus')) {
            try {
                // 직접 SQL로 컬럼 정보 확인 및 수정
                $connection = Schema::getConnection();
                $tableName = 'mobile_menus';
                $columnName = 'name';
                
                // 컬럼이 NULL을 허용하는지 확인
                $result = $connection->select("
                    SELECT IS_NULLABLE 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = ? 
                    AND COLUMN_NAME = ?
                ", [$tableName, $columnName]);
                
                if (!empty($result) && $result[0]->IS_NULLABLE === 'NO') {
                    // nullable이 아니면 수정
                    $connection->statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `{$columnName}` VARCHAR(255) NULL");
                }
            } catch (\Exception $e) {
                // 오류가 발생하면 무시하고 계속 진행
            }
        }

        $request->validate([
            'icon_type' => 'required|in:image,default,emoji',
            'icon_path' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'link_type' => 'required|in:board,custom_page,external_link,attendance,point_exchange,event_application',
            'link_target' => 'nullable|string',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // 아이콘 경로 처리
        $iconPath = null;
        if ($request->icon_type === 'image' && $request->hasFile('icon_file')) {
            $iconPath = $request->file('icon_file')->store('mobile-menu-icons', 'public');
        } elseif ($request->icon_type === 'image' && $request->icon_path) {
            // 기존 이미지 경로 사용
            $iconPath = $request->icon_path;
        } elseif ($request->icon_type === 'default') {
            $iconPath = $request->icon_path; // 기본 아이콘 클래스명
        } elseif ($request->icon_type === 'emoji') {
            $iconPath = $request->icon_path; // 이모지 문자
        }

        // 게시판 타입인 경우 link_target이 게시판 ID인지 확인
        if ($request->link_type === 'board') {
            $board = Board::where('site_id', $site->id)
                ->where('id', $request->link_target)
                ->first();
            
            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => '존재하지 않는 게시판입니다.',
                ], 400);
            }
        }

        // 커스텀 페이지 타입인 경우 link_target이 커스텀 페이지 ID인지 확인
        if ($request->link_type === 'custom_page') {
            $customPage = CustomPage::where('site_id', $site->id)
                ->where('id', $request->link_target)
                ->first();
            
            if (!$customPage) {
                return response()->json([
                    'success' => false,
                    'message' => '존재하지 않는 커스텀 페이지입니다.',
                ], 400);
            }
        }

        // 외부 링크인 경우 URL 형식 확인
        if ($request->link_type === 'external_link') {
            if (empty($request->link_target)) {
                return response()->json([
                    'success' => false,
                    'message' => '외부 링크 URL을 입력해주세요.',
                ], 400);
            }
        }

        // 테이블 존재 확인 및 자동 생성
        if (!Schema::hasTable('mobile_menus')) {
            Schema::create('mobile_menus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                $table->string('icon_type')->default('default');
                $table->string('icon_path')->nullable();
                $table->string('name')->nullable();
                $table->string('link_type');
                $table->string('link_target')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
                
                $table->index(['site_id', 'order']);
            });
        } else {
            // 기존 테이블의 name 컬럼이 nullable이 아니면 수정
            try {
                $connection = Schema::getConnection();
                $tableName = 'mobile_menus';
                $columnName = 'name';
                
                // 컬럼이 NULL을 허용하는지 확인
                $result = $connection->select("
                    SELECT IS_NULLABLE 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = ? 
                    AND COLUMN_NAME = ?
                ", [$tableName, $columnName]);
                
                if (!empty($result) && $result[0]->IS_NULLABLE === 'NO') {
                    // nullable이 아니면 수정
                    $connection->statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `{$columnName}` VARCHAR(255) NULL");
                }
            } catch (\Exception $e) {
                // 오류가 발생하면 무시하고 계속 진행
            }
        }

        // 순서 결정
        $maxOrder = MobileMenu::where('site_id', $site->id)->max('order') ?? 0;

        $mobileMenu = MobileMenu::create([
            'site_id' => $site->id,
            'icon_type' => $request->icon_type,
            'icon_path' => $iconPath,
            'name' => $request->name ?? '',
            'link_type' => $request->link_type,
            'link_target' => $request->link_target,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => '모바일 하단 메뉴가 추가되었습니다.',
            'menu' => $mobileMenu,
        ]);
    }

    /**
     * Update mobile menu order.
     */
    public function updateMobileMenuOrder(Site $site, Request $request)
    {
        $request->validate([
            'menus' => 'required|array',
            'menus.*.id' => 'required|exists:mobile_menus,id',
            'menus.*.order' => 'required|integer',
        ]);

        foreach ($request->menus as $menuData) {
            $menu = MobileMenu::where('site_id', $site->id)
                ->where('id', $menuData['id'])
                ->first();
            
            if ($menu) {
                $menu->update([
                    'order' => $menuData['order'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '모바일 메뉴 순서가 저장되었습니다.',
        ]);
    }

    /**
     * Update mobile menu design type.
     */
    public function updateMobileMenuDesignType(Site $site, Request $request)
    {
        $request->validate([
            'design_type' => 'required|in:default,top_round,round,glass',
            'bg_color' => 'nullable|string|max:7',
            'font_color' => 'nullable|string|max:7',
        ]);

        $site->setSetting('mobile_menu_design_type', $request->design_type);
        
        if ($request->has('bg_color')) {
            $site->setSetting('mobile_menu_bg_color', $request->bg_color);
        }
        
        if ($request->has('font_color')) {
            $site->setSetting('mobile_menu_font_color', $request->font_color);
        }

        return response()->json([
            'success' => true,
            'message' => '모바일 메뉴 설정이 저장되었습니다.',
        ]);
    }

    /**
     * Update a mobile menu item.
     */
    public function updateMobileMenu(Site $site, MobileMenu $mobileMenu, Request $request)
    {
        // 같은 사이트에 속하는지 확인
        if ($mobileMenu->site_id !== $site->id) {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.',
            ], 403);
        }

        $request->validate([
            'icon_type' => 'required|in:image,default,emoji',
            'icon_path' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'link_type' => 'required|in:board,custom_page,external_link,attendance,point_exchange,event_application',
            'link_target' => 'nullable|string',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // 이미지 업로드 처리
        $iconPath = $mobileMenu->icon_path;
        if ($request->icon_type === 'image' && $request->hasFile('icon_file')) {
            // 기존 이미지 삭제
            if ($mobileMenu->icon_path && Storage::disk('public')->exists($mobileMenu->icon_path)) {
                Storage::disk('public')->delete($mobileMenu->icon_path);
            }
            $iconPath = $request->file('icon_file')->store('mobile-menu-icons', 'public');
        } elseif ($request->icon_type === 'image' && $request->icon_path) {
            $iconPath = $request->icon_path;
        } elseif ($request->icon_type === 'default') {
            // 기본 아이콘으로 변경 시 기존 이미지 삭제
            if ($mobileMenu->icon_type === 'image' && $mobileMenu->icon_path && Storage::disk('public')->exists($mobileMenu->icon_path)) {
                Storage::disk('public')->delete($mobileMenu->icon_path);
            }
            $iconPath = $request->icon_path;
        }

        // 게시판 타입인 경우 link_target이 게시판 ID인지 확인
        if ($request->link_type === 'board') {
            $board = Board::where('site_id', $site->id)
                ->where('id', $request->link_target)
                ->first();
            
            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => '존재하지 않는 게시판입니다.',
                ], 400);
            }
        }

        // 커스텀 페이지 타입인 경우 link_target이 커스텀 페이지 ID인지 확인
        if ($request->link_type === 'custom_page') {
            $customPage = CustomPage::where('site_id', $site->id)
                ->where('id', $request->link_target)
                ->first();
            
            if (!$customPage) {
                return response()->json([
                    'success' => false,
                    'message' => '존재하지 않는 커스텀 페이지입니다.',
                ], 400);
            }
        }

        $mobileMenu->update([
            'icon_type' => $request->icon_type,
            'icon_path' => $iconPath,
            'name' => $request->name ?? '',
            'link_type' => $request->link_type,
            'link_target' => $request->link_target,
        ]);

        return response()->json([
            'success' => true,
            'message' => '모바일 하단 메뉴가 수정되었습니다.',
            'menu' => $mobileMenu->fresh(),
        ]);
    }

    /**
     * Delete a mobile menu item.
     */
    public function deleteMobileMenu(Site $site, MobileMenu $mobileMenu)
    {
        // 같은 사이트에 속하는지 확인
        if ($mobileMenu->site_id !== $site->id) {
            abort(403, 'Unauthorized action.');
        }

        // 이미지 파일 삭제
        if ($mobileMenu->icon_type === 'image' && $mobileMenu->icon_path && Storage::disk('public')->exists($mobileMenu->icon_path)) {
            Storage::disk('public')->delete($mobileMenu->icon_path);
        }

        $mobileMenu->delete();

        return response()->json([
            'success' => true,
            'message' => '모바일 하단 메뉴가 삭제되었습니다.',
        ]);
    }

    /**
     * Display messages management page.
     */
    public function messages(Site $site, Request $request)
    {
        if (!Schema::hasTable('messages')) {
            $messages = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            return view('admin.messages', compact('site', 'messages'));
        }

        $query = Message::where('site_id', $site->id)
            ->with(['sender', 'receiver']);

        // 검색 기능
        if ($request->filled('user_name')) {
            $userName = $request->user_name;
            $query->whereHas('sender', function($q) use ($userName) {
                $q->where('name', 'like', "%{$userName}%");
            })->orWhereHas('receiver', function($q) use ($userName) {
                $q->where('name', 'like', "%{$userName}%");
            });
        }

        if ($request->filled('content')) {
            $query->where('content', 'like', "%{$request->content}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.messages', compact('site', 'messages'));
    }

    /**
     * Update message content.
     */
    public function updateMessage(Site $site, Message $message, Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $message->content = $request->content;
        $message->save();

        return response()->json([
            'success' => true,
            'message' => '쪽지가 수정되었습니다.',
        ]);
    }

    /**
     * Update message settings.
     */
    public function updateMessageSettings(Site $site, Request $request)
    {
        $enablePointMessage = $request->has('enable_point_message') && $request->enable_point_message == '1' ? '1' : '0';
        
        $site->setSetting('enable_point_message', $enablePointMessage);

        return response()->json([
            'success' => true,
            'message' => '설정이 저장되었습니다.',
        ]);
    }

    /**
     * Delete message.
     */
    public function deleteMessage(Site $site, Message $message)
    {
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => '쪽지가 삭제되었습니다.',
        ]);
    }

    /**
     * Display banner management page.
     */
    public function bannersIndex(Site $site)
    {
        // 고정 필드 컬럼이 없으면 자동으로 생성
        if (!Schema::hasColumn('banners', 'is_pinned_top')) {
            try {
                DB::statement('ALTER TABLE `banners` ADD COLUMN `is_pinned_top` TINYINT(1) NOT NULL DEFAULT 0 AFTER `order`');
            } catch (\Exception $e) {
                \Log::error('Failed to create is_pinned_top column: ' . $e->getMessage());
            }
        }
        
        if (!Schema::hasColumn('banners', 'pinned_position')) {
            try {
                DB::statement('ALTER TABLE `banners` ADD COLUMN `pinned_position` INT NULL AFTER `is_pinned_top`');
            } catch (\Exception $e) {
                \Log::error('Failed to create pinned_position column: ' . $e->getMessage());
            }
        }
        
        $bannerLocations = [
            'header' => ['name' => '헤더', 'key' => 'header', 'description' => '최상단 헤더 상단으로 배너를 표시'],
            'main_top' => ['name' => '메인 상단', 'key' => 'main_top', 'description' => '메인 페이지 본문의 상단에 배너 표시'],
            'main_bottom' => ['name' => '메인 하단', 'key' => 'main_bottom', 'description' => '메인 페이지 본문의 하단에 배너 표시'],
            'content_top' => ['name' => '본문 상단', 'key' => 'content_top', 'description' => '사이트 내 생성된 게시판이나 페이지 상단에 표시 (게시판의 게시글 볼 때 게시글의 상단에도 표시)'],
            'content_bottom' => ['name' => '본문 하단', 'key' => 'content_bottom', 'description' => '사이트 내 생성된 게시판이나 페이지 하단에 표시 (게시판의 게시글 볼 때 게시글의 하단에도 표시) - 게시글 내용 하단에 게시판 리스트 바로 위쪽'],
            'sidebar_top' => ['name' => '사이드바 상단', 'key' => 'sidebar_top', 'description' => '로그인 위젯과 다른 위젯의 중간 즉 로그인 위젯 바로 하단에 배너 표시'],
            'sidebar_bottom' => ['name' => '사이드바 하단', 'key' => 'sidebar_bottom', 'description' => '모든 사이드바 위젯의 가장 하단에 배너 표시'],
            'left_margin' => ['name' => '좌측 여백', 'key' => 'left_margin', 'description' => 'container 클래스의 row클래스 좌측에 배너 표시'],
            'right_margin' => ['name' => '우측 여백', 'key' => 'right_margin', 'description' => 'container 클래스의 row클래스 우측에 배너 표시'],
            'mobile_menu_top' => ['name' => 'M메뉴상단', 'key' => 'mobile_menu_top', 'description' => '모바일 메뉴 로그인 위젯 하단에 배너 표시'],
            'mobile_menu_bottom' => ['name' => 'M메뉴하단', 'key' => 'mobile_menu_bottom', 'description' => '모바일 메뉴 가장 하단에 배너 표시'],
        ];
        
        // 우측 여백 하단으로 모바일 메뉴 배너 이동
        $rightMarginKey = 'right_margin';
        $mobileMenuTopKey = 'mobile_menu_top';
        $mobileMenuBottomKey = 'mobile_menu_bottom';
        
        $orderedLocations = [];
        foreach ($bannerLocations as $key => $location) {
            if ($key === $rightMarginKey) {
                $orderedLocations[$key] = $location;
                $orderedLocations[$mobileMenuTopKey] = $bannerLocations[$mobileMenuTopKey];
                $orderedLocations[$mobileMenuBottomKey] = $bannerLocations[$mobileMenuBottomKey];
            } elseif ($key !== $mobileMenuTopKey && $key !== $mobileMenuBottomKey) {
                $orderedLocations[$key] = $location;
            }
        }
        $bannerLocations = $orderedLocations;

        // 각 위치별 설정 가져오기
        $bannerSettings = [];
        foreach ($bannerLocations as $key => $location) {
            $count = Banner::where('site_id', $site->id)
                ->where('location', $key)
                ->count();
            
            $bannerSettings[$key] = [
                'count' => $count,
                'exposure_type' => $site->getSetting("banner_{$key}_exposure_type", 'basic'),
                'sort' => $site->getSetting("banner_{$key}_sort", 'created'),
                'desktop_per_line' => $site->getSetting("banner_{$key}_desktop_per_line", '3'),
                'mobile_per_line' => $site->getSetting("banner_{$key}_mobile_per_line", '1'),
                'desktop_rows' => $site->getSetting("banner_{$key}_desktop_rows", '0'),
                'mobile_rows' => $site->getSetting("banner_{$key}_mobile_rows", '0'),
                'slide_interval' => $site->getSetting("banner_{$key}_slide_interval", '3'),
                'slide_direction' => $site->getSetting("banner_{$key}_slide_direction", ''),
            ];
        }

        return view('admin.banners', compact('site', 'bannerLocations', 'bannerSettings'));
    }

    /**
     * Display banner detail page for a specific location.
     */
    public function bannersDetail(Site $site, $location)
    {
        // 고정 필드 컬럼이 없으면 자동으로 생성
        if (!Schema::hasColumn('banners', 'is_pinned_top')) {
            try {
                DB::statement('ALTER TABLE `banners` ADD COLUMN `is_pinned_top` TINYINT(1) NOT NULL DEFAULT 0 AFTER `order`');
            } catch (\Exception $e) {
                \Log::error('Failed to create is_pinned_top column: ' . $e->getMessage());
            }
        }
        
        if (!Schema::hasColumn('banners', 'pinned_position')) {
            try {
                DB::statement('ALTER TABLE `banners` ADD COLUMN `pinned_position` INT NULL AFTER `is_pinned_top`');
            } catch (\Exception $e) {
                \Log::error('Failed to create pinned_position column: ' . $e->getMessage());
            }
        }
        
        $bannerLocations = [
            'header' => '헤더',
            'main_top' => '메인 상단',
            'main_bottom' => '메인 하단',
            'content_top' => '본문 상단',
            'content_bottom' => '본문 하단',
            'sidebar_top' => '사이드바 상단',
            'sidebar_bottom' => '사이드바 하단',
            'left_margin' => '좌측 여백',
            'right_margin' => '우측 여백',
            'mobile_menu_top' => 'M메뉴상단',
            'mobile_menu_bottom' => 'M메뉴하단',
        ];

        if (!isset($bannerLocations[$location])) {
            abort(404);
        }

        $banners = Banner::where('site_id', $site->id)
            ->where('location', $location)
            ->orderByRaw('CASE WHEN is_pinned_top = 1 THEN 0 ELSE 1 END')
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.banners-detail', compact('site', 'location', 'bannerLocations', 'banners'));
    }

    /**
     * Store a new banner.
     */
    public function bannersStore(Site $site, Request $request)
    {
        // type 컬럼이 없으면 자동으로 생성
        if (!Schema::hasColumn('banners', 'type')) {
            try {
                Schema::table('banners', function (Blueprint $table) {
                    $table->string('type')->default('image')->after('location');
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create type column: ' . $e->getMessage());
            }
        }
        
        // html_code 컬럼이 없으면 자동으로 생성
        if (!Schema::hasColumn('banners', 'html_code')) {
            try {
                Schema::table('banners', function (Blueprint $table) {
                    $table->text('html_code')->nullable()->after('image_path');
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create html_code column: ' . $e->getMessage());
            }
        }
        
        // image_path를 nullable로 변경 (HTML 배너를 위해)
        if (Schema::hasColumn('banners', 'image_path')) {
            try {
                // 직접 SQL로 nullable 변경 시도 (이미 nullable이면 에러 무시)
                DB::statement('ALTER TABLE `banners` MODIFY COLUMN `image_path` VARCHAR(255) NULL');
            } catch (\Exception $e) {
                // 이미 nullable이거나 다른 이유로 실패해도 계속 진행
                \Log::info('image_path column modification: ' . $e->getMessage());
            }
        }

        $validator = Validator::make($request->all(), [
            'location' => 'required|in:header,main_top,main_bottom,content_top,content_bottom,sidebar_top,sidebar_bottom,left_margin,right_margin,mobile_menu_top,mobile_menu_bottom',
            'type' => 'required|in:image,html',
            'image' => 'required_if:type,image|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'html_code' => 'required_if:type,html|nullable|string',
            'link' => 'nullable|url|max:500',
            'open_new_window' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $imagePath = null;
            
            if ($request->type === 'image') {
                $file = $request->file('image');
                $directory = 'banners/' . $site->id . '/' . $request->location;
                $result = $this->fileUploadService->upload($file, $directory);
                $imagePath = $result['file_path'];
            }

            // Get max order for this location
            $maxOrder = Banner::where('site_id', $site->id)
                ->where('location', $request->location)
                ->max('order') ?? 0;

            $banner = Banner::create([
                'site_id' => $site->id,
                'location' => $request->location,
                'type' => $request->type,
                'image_path' => $imagePath,
                'html_code' => $request->type === 'html' ? $request->html_code : null,
                'link' => $request->link,
                'open_new_window' => $request->has('open_new_window') && $request->open_new_window == '1',
                'order' => $maxOrder + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => '배너가 등록되었습니다.',
                'banner' => $banner->load('site'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '배너 등록 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update banner order.
     */
    public function bannersUpdateOrder(Site $site, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'banner_id' => 'required|exists:banners,id',
            'direction' => 'required|in:up,down',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $banner = Banner::where('site_id', $site->id)
            ->findOrFail($request->banner_id);

        if ($request->direction === 'up') {
            $targetBanner = Banner::where('site_id', $site->id)
                ->where('location', $banner->location)
                ->where('order', '<', $banner->order)
                ->orderBy('order', 'desc')
                ->first();
        } else {
            $targetBanner = Banner::where('site_id', $site->id)
                ->where('location', $banner->location)
                ->where('order', '>', $banner->order)
                ->orderBy('order', 'asc')
                ->first();
        }

        if ($targetBanner) {
            $tempOrder = $banner->order;
            $banner->order = $targetBanner->order;
            $targetBanner->order = $tempOrder;
            $banner->save();
            $targetBanner->save();
        }

        return response()->json([
            'success' => true,
            'message' => '순서가 변경되었습니다.',
        ]);
    }

    /**
     * Update banner.
     */
    public function bannersUpdateItem(Site $site, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'banner_id' => 'required|exists:banners,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|url|max:500',
            'open_new_window' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $banner = Banner::where('site_id', $site->id)
            ->findOrFail($request->banner_id);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }

            $file = $request->file('image');
            $directory = 'banners/' . $site->id . '/' . $banner->location;
            $result = $this->fileUploadService->upload($file, $directory);
            $banner->image_path = $result['file_path'];
        }

        if ($request->has('link')) {
            $banner->link = $request->link;
        }

        if ($request->has('open_new_window')) {
            $banner->open_new_window = $request->open_new_window == '1';
        }

        $banner->save();
        $banner->refresh();

        return response()->json([
            'success' => true,
            'message' => '배너가 수정되었습니다.',
            'banner' => [
                'id' => $banner->id,
                'image_url' => $banner->image_url,
                'image_path' => $banner->image_path,
            ],
        ]);
    }

    /**
     * Delete banner.
     */
    public function bannersDelete(Site $site, Banner $banner)
    {
        if ($banner->site_id !== $site->id) {
            abort(403);
        }

        // Delete image file
        if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => '배너가 삭제되었습니다.',
        ]);
    }

    /**
     * Save all banner updates.
     */
    public function bannersSaveAll(Site $site, Request $request)
    {
        $bannerIds = $request->input('banner_ids', []);
        $links = $request->input('links', []);
        $openNewWindows = $request->input('open_new_windows', []);

        // banner_ids가 배열이 아닌 경우 배열로 변환
        if (!is_array($bannerIds)) {
            $bannerIds = $bannerIds ? [$bannerIds] : [];
        }
        if (!is_array($links)) {
            $links = $links ? [$links] : [];
        }
        if (!is_array($openNewWindows)) {
            $openNewWindows = $openNewWindows ? [$openNewWindows] : [];
        }

        $htmlCodes = $request->input('html_codes', []);
        if (!is_array($htmlCodes)) {
            $htmlCodes = $htmlCodes ? [$htmlCodes] : [];
        }

        $isPinnedTop = $request->input('is_pinned_top', []);
        if (!is_array($isPinnedTop)) {
            $isPinnedTop = $isPinnedTop ? [$isPinnedTop] : [];
        }

        $pinnedPositions = $request->input('pinned_position', []);
        if (!is_array($pinnedPositions)) {
            $pinnedPositions = $pinnedPositions ? [$pinnedPositions] : [];
        }

        // location을 첫 번째 배너에서 가져오거나 request에서 가져옴
        $location = null;
        if (!empty($bannerIds)) {
            $firstBanner = Banner::where('site_id', $site->id)->find($bannerIds[0]);
            if ($firstBanner) {
                $location = $firstBanner->location;
            }
        }
        
        if (!$location) {
            $location = $request->input('location', '');
        }
        
        // 최상단 고정 배너의 order를 조정하기 위해 먼저 모든 배너를 가져옴
        $allBanners = Banner::where('site_id', $site->id)
            ->where('location', $location)
            ->get();
        
        // 최상단 고정 배너와 일반 배너 분리
        $pinnedTopBanners = $allBanners->where('is_pinned_top', true);
        $normalBanners = $allBanners->where('is_pinned_top', false);
        
        // 최상단 고정 배너의 order를 0부터 시작하도록 조정
        $pinnedTopOrder = 0;
        foreach ($pinnedTopBanners->sortBy('order') as $pinnedBanner) {
            $pinnedBanner->order = $pinnedTopOrder++;
            $pinnedBanner->save();
        }
        
        // 일반 배너의 order를 최상단 고정 배너 개수부터 시작하도록 조정
        $normalOrder = $pinnedTopBanners->count();
        foreach ($normalBanners->sortBy('order') as $normalBanner) {
            $normalBanner->order = $normalOrder++;
            $normalBanner->save();
        }
        
        foreach ($bannerIds as $bannerId) {
            $banner = Banner::where('site_id', $site->id)
                ->find($bannerId);

            if ($banner) {
                if ($banner->type === 'html' && isset($htmlCodes[$bannerId])) {
                    $banner->html_code = $htmlCodes[$bannerId];
                } elseif ($banner->type === 'image' && isset($links[$bannerId])) {
                    $banner->link = $links[$bannerId];
                }
                if (isset($openNewWindows[$bannerId])) {
                    $banner->open_new_window = $openNewWindows[$bannerId] == '1';
                }
                
                // 고정 설정 저장
                $wasPinnedTop = $banner->is_pinned_top;
                if (isset($isPinnedTop[$bannerId])) {
                    $banner->is_pinned_top = $isPinnedTop[$bannerId] == '1';
                } else {
                    $banner->is_pinned_top = false;
                }
                
                // 위치 설정 저장 (0이면 null로 저장)
                if (isset($pinnedPositions[$bannerId]) && $pinnedPositions[$bannerId] !== '' && $pinnedPositions[$bannerId] != '0') {
                    $banner->pinned_position = (int)$pinnedPositions[$bannerId];
                } else {
                    $banner->pinned_position = null;
                }
                
                // 최상단 고정 상태가 변경된 경우 order 재조정
                if ($wasPinnedTop != $banner->is_pinned_top) {
                    // 모든 배너의 order를 다시 조정
                    $allBanners = Banner::where('site_id', $site->id)
                        ->where('location', $banner->location)
                        ->get();
                    
                    $pinnedTopBanners = $allBanners->where('is_pinned_top', true);
                    $normalBanners = $allBanners->where('is_pinned_top', false);
                    
                    $pinnedTopOrder = 0;
                    foreach ($pinnedTopBanners->sortBy('order') as $pinnedBanner) {
                        $pinnedBanner->order = $pinnedTopOrder++;
                        $pinnedBanner->save();
                    }
                    
                    $normalOrder = $pinnedTopBanners->count();
                    foreach ($normalBanners->sortBy('order') as $normalBanner) {
                        $normalBanner->order = $normalOrder++;
                        $normalBanner->save();
                    }
                }
                
                $banner->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => '모든 변경사항이 저장되었습니다.',
        ]);
    }

    /**
     * Update banner settings.
     */
    public function bannersUpdate(Site $site, Request $request)
    {
        $bannerLocations = [
            'header', 'main_top', 'main_bottom', 'content_top', 'content_bottom',
            'sidebar_top', 'sidebar_bottom', 'left_margin', 'right_margin',
            'mobile_menu_top', 'mobile_menu_bottom'
        ];

        foreach ($bannerLocations as $location) {
            if ($request->has("banner_{$location}_exposure_type")) {
                $site->setSetting("banner_{$location}_exposure_type", $request->input("banner_{$location}_exposure_type"));
            }
            if ($request->has("banner_{$location}_sort")) {
                $site->setSetting("banner_{$location}_sort", $request->input("banner_{$location}_sort"));
            }
            if ($request->has("banner_{$location}_desktop_per_line")) {
                $site->setSetting("banner_{$location}_desktop_per_line", $request->input("banner_{$location}_desktop_per_line"));
            }
            if ($request->has("banner_{$location}_mobile_per_line")) {
                $site->setSetting("banner_{$location}_mobile_per_line", $request->input("banner_{$location}_mobile_per_line"));
            }
            if ($request->has("banner_{$location}_desktop_rows")) {
                $site->setSetting("banner_{$location}_desktop_rows", $request->input("banner_{$location}_desktop_rows"));
            }
            if ($request->has("banner_{$location}_mobile_rows")) {
                $site->setSetting("banner_{$location}_mobile_rows", $request->input("banner_{$location}_mobile_rows"));
            }
            if ($request->has("banner_{$location}_slide_interval")) {
                $site->setSetting("banner_{$location}_slide_interval", $request->input("banner_{$location}_slide_interval"));
            }
            if ($request->has("banner_{$location}_slide_direction")) {
                $site->setSetting("banner_{$location}_slide_direction", $request->input("banner_{$location}_slide_direction"));
            }
        }
        
        // 통일된 여백 설정 저장
        if ($request->has('banner_desktop_gap')) {
            $site->setSetting('banner_desktop_gap', $request->input('banner_desktop_gap'));
        }
        if ($request->has('banner_mobile_gap')) {
            $site->setSetting('banner_mobile_gap', $request->input('banner_mobile_gap'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '배너 설정이 저장되었습니다.',
            ]);
        }

        return redirect()->route('admin.banners.index', ['site' => $site->slug])
            ->with('success', '배너 설정이 저장되었습니다.');
    }

    /**
     * Display popup management page.
     */
    public function popupsIndex(Site $site)
    {
        // 팝업 테이블이 없으면 자동으로 생성
        if (!Schema::hasTable('popups')) {
            try {
                Schema::create('popups', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('site_id')->constrained()->onDelete('cascade');
                    $table->enum('type', ['image', 'html'])->default('image');
                    $table->string('image_path')->nullable();
                    $table->text('html_code')->nullable();
                    $table->string('link')->nullable();
                    $table->boolean('open_new_window')->default(false);
                    $table->enum('display_type', ['overlay', 'list'])->default('overlay');
                    $table->enum('position', ['center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'])->default('center');
                    $table->string('target_type')->default('all'); // 'all', 'main', 'attendance', 'point-exchange', 'event-application', 'board_{id}'
                    $table->string('target_id')->nullable(); // For board_id or page slug
                    $table->integer('order')->default(0);
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create popups table: ' . $e->getMessage());
            }
        } else {
            // 기존 테이블에 target_id 컬럼이 없으면 추가
            if (!Schema::hasColumn('popups', 'target_id')) {
                try {
                    Schema::table('popups', function (Blueprint $table) {
                        $table->string('target_id')->nullable()->after('target_type');
                    });
                } catch (\Exception $e) {
                    \Log::error('Failed to add target_id column to popups table: ' . $e->getMessage());
                }
            }
            
            // target_type 컬럼이 enum이거나 길이가 부족하면 string(255)로 변경
            try {
                $column = \DB::select("SHOW COLUMNS FROM popups WHERE Field = 'target_type'");
                if (!empty($column)) {
                    $columnType = strtolower($column[0]->Type);
                    // enum 타입이거나 varchar 길이가 부족한 경우 수정
                    $needsModify = false;
                    if (strpos($columnType, 'enum') !== false) {
                        $needsModify = true;
                    } elseif (strpos($columnType, 'varchar') !== false) {
                        // varchar 길이 추출
                        preg_match('/varchar\((\d+)\)/', $columnType, $matches);
                        if (!empty($matches[1]) && (int)$matches[1] < 255) {
                            $needsModify = true;
                        }
                    }
                    
                    if ($needsModify) {
                        \DB::statement("ALTER TABLE popups MODIFY COLUMN target_type VARCHAR(255) DEFAULT 'all'");
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to modify target_type column: ' . $e->getMessage());
            }
        }

        $popups = Popup::where('site_id', $site->id)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        // 표시 방식 설정 가져오기
        $displayType = $site->getSetting('popup_display_type', 'overlay');
        $position = $site->getSetting('popup_position', 'center');

        // 게시판 목록 가져오기
        $boards = Board::where('site_id', $site->id)
            ->orderBy('name', 'asc')
            ->get();
        
        // 포인트 교환 설정 가져오기
        $pointExchangeSetting = \App\Models\PointExchangeSetting::where('site_id', $site->id)->first();
        $pointExchangeTitle = $pointExchangeSetting ? $pointExchangeSetting->page_title : '포인트교환';
        
        // 신청형 이벤트 설정 가져오기
        $eventApplicationSetting = \App\Models\EventApplicationSetting::where('site_id', $site->id)->first();
        $eventApplicationTitle = $eventApplicationSetting ? $eventApplicationSetting->page_title : '신청형 이벤트';

        return view('admin.popups', compact('site', 'popups', 'displayType', 'position', 'boards', 'pointExchangeTitle', 'eventApplicationTitle'));
    }

    /**
     * Store a new popup.
     */
    public function popupsStore(Site $site, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:image,html',
            'image' => 'required_if:type,image|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'html_code' => 'required_if:type,html|nullable|string',
            'link' => 'nullable|url|max:500',
            'open_new_window' => 'boolean',
            'target_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $imagePath = null;
            
            if ($request->type === 'image') {
                $file = $request->file('image');
                $directory = 'popups/' . $site->id;
                $result = $this->fileUploadService->upload($file, $directory);
                $imagePath = $result['file_path'];
            }

            // Get max order
            $maxOrder = Popup::where('site_id', $site->id)->max('order') ?? 0;
            
            // 표시 방식과 위치는 전체 설정에서 가져옴
            $displayType = $site->getSetting('popup_display_type', 'overlay');
            $position = $site->getSetting('popup_position', 'center');
            
            // target_type 파싱
            $targetType = $request->target_type;
            $targetId = null;
            
            if (strpos($targetType, 'board_') === 0) {
                // board_123 형식인 경우 - target_id에 board ID 저장
                $boardId = str_replace('board_', '', $targetType);
                $targetId = $boardId;
            }

            $popup = Popup::create([
                'site_id' => $site->id,
                'type' => $request->type,
                'image_path' => $imagePath,
                'html_code' => $request->type === 'html' ? $request->html_code : null,
                'link' => $request->link,
                'open_new_window' => $request->has('open_new_window') && $request->open_new_window == '1',
                'display_type' => $displayType,
                'position' => $position,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'order' => $maxOrder + 1,
                'is_active' => true,
            ]);

            // target_type이 board_로 시작하는 경우에만 targetBoard 관계 로드
            if (!empty($popup->target_type) && strpos($popup->target_type, 'board_') === 0) {
                $popup->load('targetBoard');
            }

            return response()->json([
                'success' => true,
                'message' => '팝업이 등록되었습니다.',
                'popup' => $popup,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '팝업 등록 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update popup settings.
     */
    public function popupsUpdateSettings(Site $site, Request $request)
    {
        $request->validate([
            'display_type' => 'required|in:overlay,list',
            'position' => 'required|in:center,top-left,top-right,bottom-left,bottom-right',
        ]);

        $site->setSetting('popup_display_type', $request->display_type);
        $site->setSetting('popup_position', $request->position);

        return response()->json([
            'success' => true,
            'message' => '팝업 설정이 저장되었습니다.',
        ]);
    }

    /**
     * Update popup order.
     */
    public function popupsUpdateOrder(Site $site, Request $request)
    {
        $request->validate([
            'popup_ids' => 'required|array',
            'popup_ids.*' => 'exists:popups,id',
        ]);

        foreach ($request->popup_ids as $index => $popupId) {
            Popup::where('site_id', $site->id)
                ->where('id', $popupId)
                ->update(['order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => '팝업 순서가 저장되었습니다.',
        ]);
    }

    /**
     * Update popup item.
     */
    public function popupsUpdateItem(Site $site, Popup $popup, Request $request)
    {
        if ($popup->site_id !== $site->id) {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'link' => 'nullable|url|max:500',
            'open_new_window' => 'boolean',
            'display_type' => 'in:overlay,list',
            'position' => 'in:center,top-left,top-right,bottom-left,bottom-right',
            'target_type' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'html_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            // 이미지 업데이트
            if ($request->hasFile('image')) {
                // 기존 이미지 삭제
                if ($popup->image_path && Storage::disk('public')->exists($popup->image_path)) {
                    Storage::disk('public')->delete($popup->image_path);
                }

                $file = $request->file('image');
                $directory = 'popups/' . $site->id;
                $result = $this->fileUploadService->upload($file, $directory);
                $popup->image_path = $result['file_path'];
                $popup->type = 'image';
                // 이미지가 업로드되면 html_code는 null로 설정
                $popup->html_code = null;
            }

            // html_code가 비어있지 않을 때만 타입을 html로 변경
            if ($request->has('html_code') && !empty(trim($request->html_code))) {
                $popup->html_code = $request->html_code;
                // html_code가 있고 이미지가 없을 때만 타입을 html로 변경
                if (!$request->hasFile('image')) {
                    $popup->type = 'html';
                    // HTML 타입으로 변경되면 이미지는 null로 설정
                    if ($popup->image_path && Storage::disk('public')->exists($popup->image_path)) {
                        Storage::disk('public')->delete($popup->image_path);
                    }
                    $popup->image_path = null;
                }
            } elseif ($request->has('html_code') && empty(trim($request->html_code))) {
                // html_code가 빈 문자열로 전송된 경우 (이미지 타입 팝업에서)
                // 타입을 변경하지 않고 html_code만 null로 설정
                if ($popup->type === 'image') {
                    $popup->html_code = null;
                }
            }

            if ($request->has('link')) {
                $popup->link = $request->link;
            }

            if ($request->has('open_new_window')) {
                $popup->open_new_window = $request->open_new_window == '1';
            }

            if ($request->has('display_type')) {
                $popup->display_type = $request->display_type;
            }

            if ($request->has('position')) {
                $popup->position = $request->position;
            }

            if ($request->has('target_type')) {
                $targetType = $request->target_type;
                $targetId = null;
                
                if (!empty($targetType) && strpos($targetType, 'board_') === 0) {
                    // board_123 형식인 경우 - target_id에 board ID 저장
                    $boardId = str_replace('board_', '', $targetType);
                    $targetId = $boardId;
                } else {
                    // board_로 시작하지 않는 경우 (all, main, attendance 등) target_id는 null
                    $targetId = null;
                }
                
                $popup->target_type = $targetType;
                $popup->target_id = $targetId;
            }

            $popup->save();

            // target_type이 board_로 시작하는 경우에만 targetBoard 관계 로드
            if (!empty($popup->target_type) && strpos($popup->target_type, 'board_') === 0) {
                $popup->load('targetBoard');
            }

            return response()->json([
                'success' => true,
                'message' => '팝업이 수정되었습니다.',
                'popup' => $popup,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '팝업 수정 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete popup.
     */
    public function popupsDelete(Site $site, Popup $popup)
    {
        if ($popup->site_id !== $site->id) {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.',
            ], 403);
        }

        // 이미지 파일 삭제
        if ($popup->image_path && Storage::disk('public')->exists($popup->image_path)) {
            Storage::disk('public')->delete($popup->image_path);
        }

        $popup->delete();

        return response()->json([
            'success' => true,
            'message' => '팝업이 삭제되었습니다.',
        ]);
    }

    /**
     * Display user ranks management page.
     */
    public function userRanks(Site $site)
    {
        // 테이블이 없으면 생성
        if (!Schema::hasTable('user_ranks')) {
            Schema::create('user_ranks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                $table->integer('rank')->default(1);
                $table->string('name');
                $table->enum('criteria_type', ['current_points', 'max_points', 'post_count'])->default('current_points');
                $table->integer('criteria_value')->default(0);
                $table->enum('display_type', ['icon', 'color'])->default('icon');
                $table->string('icon_path')->nullable();
                $table->string('color')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['site_id', 'rank']);
                $table->index('order');
            });
        }

        $ranks = UserRank::where('site_id', $site->id)
            ->orderBy('order', 'asc')
            ->orderBy('rank', 'asc')
            ->get();

        $criteriaType = $site->getSetting('rank_criteria_type', 'current_points');
        $displayType = $site->getSetting('rank_display_type', 'icon');
        $adminIcon = $site->getSetting('admin_icon_path', '');
        $managerIcon = $site->getSetting('manager_icon_path', '');

        return view('admin.user-ranks', compact('site', 'ranks', 'criteriaType', 'displayType', 'adminIcon', 'managerIcon'));
    }

    /**
     * Store a new user rank.
     */
    public function userRanksStore(Request $request, Site $site)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rank' => 'required|integer|min:1',
            'criteria_value' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $maxOrder = UserRank::where('site_id', $site->id)->max('order') ?? 0;

        $rank = UserRank::create([
            'site_id' => $site->id,
            'name' => $request->name,
            'rank' => $request->rank,
            'criteria_type' => $site->getSetting('rank_criteria_type', 'current_points'),
            'criteria_value' => $request->criteria_value,
            'display_type' => $site->getSetting('rank_display_type', 'icon'),
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => '등급이 생성되었습니다.',
            'rank' => $rank,
        ]);
    }

    /**
     * Update user ranks settings and all ranks.
     */
    public function userRanksUpdate(Request $request, Site $site)
    {
        $validator = Validator::make($request->all(), [
            'criteria_type' => 'required|in:current_points,max_points,post_count',
            'display_type' => 'required|in:icon,color',
            'admin_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'manager_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'ranks' => 'required|array',
            'ranks.*.id' => 'required',
            'ranks.*.name' => 'required|string|max:255',
            'ranks.*.rank' => 'required|integer|min:1',
            'ranks.*.criteria_value' => 'required|integer|min:0',
            'ranks.*.display_type' => 'required|in:icon,color',
            'ranks.*.icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'ranks.*.color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // 전역 설정 저장
        $site->setSetting('rank_criteria_type', $request->criteria_type);
        $site->setSetting('rank_display_type', $request->display_type);

        // 관리자/매니저 아이콘 저장
        if ($request->hasFile('admin_icon')) {
            $result = $this->fileUploadService->upload($request->file('admin_icon'), 'icons');
            $site->setSetting('admin_icon_path', $result['file_path']);
        }

        if ($request->hasFile('manager_icon')) {
            $result = $this->fileUploadService->upload($request->file('manager_icon'), 'icons');
            $site->setSetting('manager_icon_path', $result['file_path']);
        }

        // 등급 업데이트 및 생성
        $maxOrder = UserRank::where('site_id', $site->id)->max('order') ?? 0;
        
        foreach ($request->ranks as $rankData) {
            // 새 등급인 경우 (ID가 new_로 시작)
            if (strpos($rankData['id'], 'new_') === 0) {
                $createData = [
                    'site_id' => $site->id,
                    'name' => $rankData['name'],
                    'rank' => $rankData['rank'],
                    'criteria_type' => $request->criteria_type,
                    'criteria_value' => $rankData['criteria_value'],
                    'display_type' => $request->display_type,
                    'order' => ++$maxOrder,
                ];

                // 색상 설정
                if (isset($rankData['color'])) {
                    $createData['color'] = $rankData['color'];
                }

                // 아이콘 업로드
                if ($request->hasFile('ranks.' . $rankData['id'] . '.icon')) {
                    $result = $this->fileUploadService->upload($request->file('ranks.' . $rankData['id'] . '.icon'), 'icons');
                    $createData['icon_path'] = $result['file_path'];
                }

                UserRank::create($createData);
            } else {
                // 기존 등급 업데이트
                $rank = UserRank::find($rankData['id']);
                if (!$rank || $rank->site_id !== $site->id) {
                    continue;
                }

                $updateData = [
                    'name' => $rankData['name'],
                    'rank' => $rankData['rank'],
                    'criteria_type' => $request->criteria_type,
                    'criteria_value' => $rankData['criteria_value'],
                    'display_type' => $request->display_type,
                ];

                // 아이콘 업데이트
                if ($request->hasFile('ranks.' . $rankData['id'] . '.icon')) {
                    if ($rank->icon_path) {
                        Storage::disk('public')->delete($rank->icon_path);
                    }
                    $result = $this->fileUploadService->upload($request->file('ranks.' . $rankData['id'] . '.icon'), 'icons');
                    $updateData['icon_path'] = $result['file_path'];
                }

                // 색상 업데이트
                if (isset($rankData['color'])) {
                    $updateData['color'] = $rankData['color'];
                }

                $rank->update($updateData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '등급 설정이 저장되었습니다.',
        ]);
    }

    /**
     * Delete a user rank.
     */
    public function userRanksDelete(UserRank $userRank)
    {
        if ($userRank->icon_path) {
            Storage::disk('public')->delete($userRank->icon_path);
        }

        $userRank->delete();

        return response()->json([
            'success' => true,
            'message' => '등급이 삭제되었습니다.',
        ]);
    }

    /**
     * Display blocked IPs management page.
     */
    public function blockedIpsIndex(Site $site)
    {
        $blockedIpsValue = $site->getSetting('blocked_ips', '');
        
        // getSetting이 이미 배열을 반환할 수 있으므로 타입 확인
        if (is_array($blockedIpsValue)) {
            $blockedIps = $blockedIpsValue;
        } elseif (is_string($blockedIpsValue) && !empty($blockedIpsValue)) {
            $blockedIps = json_decode($blockedIpsValue, true);
            if (!is_array($blockedIps)) {
                $blockedIps = [];
            }
        } else {
            $blockedIps = [];
        }

        return view('admin.blocked-ips', compact('site', 'blockedIps'));
    }

    /**
     * Store a blocked IP.
     */
    public function blockedIpsStore(Request $request, Site $site)
    {
        $request->validate([
            'ip' => 'required|ip',
        ]);

        $blockedIpsValue = $site->getSetting('blocked_ips', '');
        
        // getSetting이 이미 배열을 반환할 수 있으므로 타입 확인
        if (is_array($blockedIpsValue)) {
            $blockedIps = $blockedIpsValue;
        } elseif (is_string($blockedIpsValue) && !empty($blockedIpsValue)) {
            $blockedIps = json_decode($blockedIpsValue, true);
            if (!is_array($blockedIps)) {
                $blockedIps = [];
            }
        } else {
            $blockedIps = [];
        }

        $ip = trim($request->input('ip'));
        
        // 이미 차단된 IP인지 확인
        if (in_array($ip, $blockedIps)) {
            return response()->json([
                'success' => false,
                'message' => '이미 차단된 IP 주소입니다.',
            ], 422);
        }

        // IP 추가
        $blockedIps[] = $ip;
        $site->setSetting('blocked_ips', json_encode($blockedIps));

        return response()->json([
            'success' => true,
            'message' => 'IP 주소가 차단되었습니다.',
        ]);
    }

    /**
     * Remove a blocked IP.
     */
    public function blockedIpsDestroy(Site $site, $ip)
    {
        $blockedIpsValue = $site->getSetting('blocked_ips', '');
        
        // getSetting이 이미 배열을 반환할 수 있으므로 타입 확인
        if (is_array($blockedIpsValue)) {
            $blockedIps = $blockedIpsValue;
        } elseif (is_string($blockedIpsValue) && !empty($blockedIpsValue)) {
            $blockedIps = json_decode($blockedIpsValue, true);
            if (!is_array($blockedIps)) {
                $blockedIps = [];
            }
        } else {
            $blockedIps = [];
        }

        // IP 제거
        $blockedIps = array_values(array_filter($blockedIps, function($blockedIp) use ($ip) {
            return $blockedIp !== $ip;
        }));

        $site->setSetting('blocked_ips', json_encode($blockedIps));

        return response()->json([
            'success' => true,
            'message' => 'IP 차단이 해제되었습니다.',
        ]);
    }

    /**
     * Display custom codes management page.
     */
    public function customCodes(Site $site)
    {
        // 테이블이 없으면 자동으로 생성
        if (!Schema::hasTable('custom_codes')) {
            try {
                Schema::create('custom_codes', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                    $table->string('location');
                    $table->text('code')->nullable();
                    $table->timestamps();
                    
                    $table->unique(['site_id', 'location']);
                    $table->index('site_id');
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create custom_codes table: ' . $e->getMessage());
            }
        }

        $locations = [
            'head' => 'HEAD 태그 안',
            'head_css' => '추가 CSS',
            'head_js' => 'JavaScript',
            'first_page_top' => '첫 페이지 상단',
            'first_page_bottom' => '첫 페이지 하단',
            'content_top' => '본문 상단',
            'content_bottom' => '본문 하단',
            'sidebar_top' => '사이드바 상단',
            'sidebar_bottom' => '사이드바 하단',
            'body' => 'BODY 태그 안',
        ];

        $customCodes = [];
        foreach ($locations as $key => $name) {
            $customCode = CustomCode::getByLocation($site->id, $key);
            $customCodes[$key] = $customCode ? $customCode->code : '';
        }

        return view('admin.custom-codes', compact('site', 'locations', 'customCodes'));
    }

    /**
     * Update custom codes.
     */
    public function updateCustomCodes(Site $site, Request $request)
    {
        // 테이블이 없으면 자동으로 생성
        if (!Schema::hasTable('custom_codes')) {
            try {
                Schema::create('custom_codes', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                    $table->string('location');
                    $table->text('code')->nullable();
                    $table->timestamps();
                    
                    $table->unique(['site_id', 'location']);
                    $table->index('site_id');
                });
            } catch (\Exception $e) {
                \Log::error('Failed to create custom_codes table: ' . $e->getMessage());
            }
        }

        $validLocations = [
            'head',
            'head_css',
            'head_js',
            'first_page_top',
            'first_page_bottom',
            'content_top',
            'content_bottom',
            'sidebar_top',
            'sidebar_bottom',
            'body',
        ];

        foreach ($validLocations as $location) {
            $code = $request->input($location, '');
            
            $customCode = CustomCode::getByLocation($site->id, $location);
            
            if ($customCode) {
                if (empty(trim($code))) {
                    // 코드가 비어있으면 삭제
                    $customCode->delete();
                } else {
                    // 코드가 있으면 업데이트
                    $customCode->code = $code;
                    $customCode->save();
                }
            } else {
                if (!empty(trim($code))) {
                    // 코드가 있고 레코드가 없으면 생성
                    CustomCode::create([
                        'site_id' => $site->id,
                        'location' => $location,
                        'code' => $code,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => '코드가 저장되었습니다.',
        ]);
    }

    /**
     * Display registration settings page.
     */
    public function registrationSettings(Site $site)
    {
        $settings = [
            'login_method' => $site->getSetting('registration_login_method', 'email'),
            'enable_phone' => $site->getSetting('registration_enable_phone', false),
            'enable_address' => $site->getSetting('registration_enable_address', false),
            'enable_phone_verification' => $site->getSetting('registration_enable_phone_verification', false),
            'enable_identity_verification' => $site->getSetting('registration_enable_identity_verification', false),
            'enable_email_verification' => $site->getSetting('registration_enable_email_verification', false),
            'enable_referrer' => $site->getSetting('registration_enable_referrer', false),
            'signup_points' => $site->getSetting('registration_signup_points', 0),
            'referrer_points' => $site->getSetting('registration_referrer_points', 0),
            'new_user_points' => $site->getSetting('registration_new_user_points', 0),
            // SMS 설정
            'sms_sender_name' => $site->getSetting('sms_sender_name', ''),
            'sms_provider' => $site->getSetting('sms_provider', 'cool_sms'),
            'sms_cool_api_key' => $site->getSetting('sms_cool_api_key', ''),
            'sms_cool_api_secret' => $site->getSetting('sms_cool_api_secret', ''),
            'sms_cool_from' => $site->getSetting('sms_cool_from', ''),
            'sms_naver_api_key' => $site->getSetting('sms_naver_api_key', ''),
            'sms_naver_api_secret' => $site->getSetting('sms_naver_api_secret', ''),
            'sms_naver_service_id' => $site->getSetting('sms_naver_service_id', ''),
            'sms_naver_caller_id' => $site->getSetting('sms_naver_caller_id', ''),
            'sms_twilio_sid' => $site->getSetting('sms_twilio_sid', ''),
            'sms_twilio_auth_token' => $site->getSetting('sms_twilio_auth_token', ''),
            'sms_twilio_from' => $site->getSetting('sms_twilio_from', ''),
            'sms_solapi_api_key' => $site->getSetting('sms_solapi_api_key', ''),
            'sms_solapi_api_secret' => $site->getSetting('sms_solapi_api_secret', ''),
            'sms_solapi_from' => $site->getSetting('sms_solapi_from', ''),
            // 소셜 로그인 설정
            'enable_social_login' => $site->getSetting('registration_enable_social_login', false),
            'google_client_id' => $site->getSetting('google_client_id', ''),
            'google_client_secret' => $site->getSetting('google_client_secret', ''),
            'naver_client_id' => $site->getSetting('naver_client_id', ''),
            'naver_client_secret' => $site->getSetting('naver_client_secret', ''),
            'kakao_client_id' => $site->getSetting('kakao_client_id', ''),
            'kakao_client_secret' => $site->getSetting('kakao_client_secret', ''),
        ];

        return view('admin.registration-settings', compact('site', 'settings'));
    }

    /**
     * Update registration settings.
     */
    public function updateRegistrationSettings(Site $site, Request $request)
    {
        $request->validate([
            'login_method' => 'required|in:email,username',
            'enable_phone' => 'boolean',
            'enable_address' => 'boolean',
            'enable_phone_verification' => 'boolean',
            'enable_identity_verification' => 'boolean',
            'enable_email_verification' => 'boolean',
            'enable_referrer' => 'boolean',
            'signup_points' => 'nullable|integer|min:0',
            'referrer_points' => 'nullable|integer|min:0',
            'new_user_points' => 'nullable|integer|min:0',
        ]);

        $site->setSetting('registration_login_method', $request->input('login_method', 'email'));
        $site->setSetting('registration_enable_phone', $request->boolean('enable_phone'));
        $site->setSetting('registration_enable_address', $request->boolean('enable_address'));
        $site->setSetting('registration_enable_phone_verification', $request->boolean('enable_phone_verification'));
        $site->setSetting('registration_enable_identity_verification', $request->boolean('enable_identity_verification'));
        $site->setSetting('registration_enable_email_verification', $request->boolean('enable_email_verification'));
        $site->setSetting('registration_enable_referrer', $request->boolean('enable_referrer'));
        $site->setSetting('registration_signup_points', $request->input('signup_points', 0));
        $site->setSetting('registration_referrer_points', $request->input('referrer_points', 0));
        $site->setSetting('registration_new_user_points', $request->input('new_user_points', 0));
        
        // 소셜 로그인 설정 저장
        $site->setSetting('registration_enable_social_login', $request->boolean('enable_social_login'));
        if ($request->has('google_client_id')) {
            $site->setSetting('google_client_id', $request->input('google_client_id', ''));
        }
        if ($request->has('google_client_secret')) {
            $site->setSetting('google_client_secret', $request->input('google_client_secret', ''));
        }
        if ($request->has('naver_client_id')) {
            $site->setSetting('naver_client_id', $request->input('naver_client_id', ''));
        }
        if ($request->has('naver_client_secret')) {
            $site->setSetting('naver_client_secret', $request->input('naver_client_secret', ''));
        }
        if ($request->has('kakao_client_id')) {
            $site->setSetting('kakao_client_id', $request->input('kakao_client_id', ''));
        }
        if ($request->has('kakao_client_secret')) {
            $site->setSetting('kakao_client_secret', $request->input('kakao_client_secret', ''));
        }
        
        // SMS 설정 저장
        if ($request->has('sms_sender_name')) {
            $site->setSetting('sms_sender_name', $request->input('sms_sender_name', ''));
        }
        if ($request->has('sms_provider')) {
            $site->setSetting('sms_provider', $request->input('sms_provider', 'cool_sms'));
        }
        
        // Cool SMS 설정
        if ($request->has('sms_cool_api_key')) {
            $site->setSetting('sms_cool_api_key', $request->input('sms_cool_api_key', ''));
        }
        if ($request->has('sms_cool_api_secret')) {
            $site->setSetting('sms_cool_api_secret', $request->input('sms_cool_api_secret', ''));
        }
        if ($request->has('sms_cool_from')) {
            $site->setSetting('sms_cool_from', $request->input('sms_cool_from', ''));
        }
        
        // 네이버 클라우드 설정
        if ($request->has('sms_naver_api_key')) {
            $site->setSetting('sms_naver_api_key', $request->input('sms_naver_api_key', ''));
        }
        if ($request->has('sms_naver_api_secret')) {
            $site->setSetting('sms_naver_api_secret', $request->input('sms_naver_api_secret', ''));
        }
        if ($request->has('sms_naver_service_id')) {
            $site->setSetting('sms_naver_service_id', $request->input('sms_naver_service_id', ''));
        }
        if ($request->has('sms_naver_caller_id')) {
            $site->setSetting('sms_naver_caller_id', $request->input('sms_naver_caller_id', ''));
        }
        
        // Twilio 설정
        if ($request->has('sms_twilio_sid')) {
            $site->setSetting('sms_twilio_sid', $request->input('sms_twilio_sid', ''));
        }
        if ($request->has('sms_twilio_auth_token')) {
            $site->setSetting('sms_twilio_auth_token', $request->input('sms_twilio_auth_token', ''));
        }
        if ($request->has('sms_twilio_from')) {
            $site->setSetting('sms_twilio_from', $request->input('sms_twilio_from', ''));
        }
        
        // SOLAPI 설정
        if ($request->has('sms_solapi_api_key')) {
            $site->setSetting('sms_solapi_api_key', $request->input('sms_solapi_api_key', ''));
        }
        if ($request->has('sms_solapi_api_secret')) {
            $site->setSetting('sms_solapi_api_secret', $request->input('sms_solapi_api_secret', ''));
        }
        if ($request->has('sms_solapi_from')) {
            $site->setSetting('sms_solapi_from', $request->input('sms_solapi_from', ''));
        }

        // AJAX 요청인 경우 JSON 응답
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '회원가입 설정이 저장되었습니다.',
            ]);
        }
        
        // 일반 요청인 경우 리다이렉트
        return redirect()->route('admin.registration-settings', ['site' => $site->slug])
            ->with('success', '회원가입 설정이 저장되었습니다.');
    }

    /**
     * Test SMS sending.
     */
    public function testSms(Site $site, Request $request)
    {
        $request->validate([
            'provider' => 'required|in:solapi,cool_sms,naver_cloud,twilio',
            'phone' => 'required|string|max:20',
        ]);

        try {
            // 테스트용으로 SMS 서비스에 직접 설정 전달
            $smsService = new \App\Services\SmsService($site);
            
            // 테스트 시에는 요청에서 받은 설정을 사용
            $provider = $request->provider;
            $config = [];
            
            if ($provider === 'cool_sms' || $provider === 'solapi') {
                // Cool SMS는 SOLAPI를 백엔드로 사용
                $config = [
                    'api_key' => $request->input('api_key', ''),
                    'api_secret' => $request->input('api_secret', ''),
                    'from' => $request->input('from', ''),
                ];
                // Cool SMS의 경우 SOLAPI API 사용
                $smsService->setConfig('solapi', $config);
            } elseif ($provider === 'naver_cloud') {
                $config = [
                    'api_key' => $request->input('api_key', ''),
                    'api_secret' => $request->input('api_secret', ''),
                    'service_id' => $request->input('service_id', ''),
                    'caller_id' => $request->input('from', ''),
                ];
                $smsService->setConfig('naver_cloud', $config);
            } elseif ($provider === 'twilio') {
                $config = [
                    'sid' => $request->input('api_key', ''),
                    'auth_token' => $request->input('api_secret', ''),
                    'from' => $request->input('from', ''),
                ];
                $smsService->setConfig('twilio', $config);
            } else {
                // solapi 직접 사용
                $config = [
                    'api_key' => $request->input('api_key', ''),
                    'api_secret' => $request->input('api_secret', ''),
                    'from' => $request->input('from', ''),
                ];
                $smsService->setConfig('solapi', $config);
            }
            
            $result = $smsService->testSms($request->phone);
            
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('SMS 테스트 실패: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'provider' => $request->provider,
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'SMS 발송 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display mail settings page.
     */
    public function mailSettings(Site $site)
    {
        $mailUsername = $site->getSetting('mail_username', '');
        
        // 게시판 선택 정보 가져오기 (이미 배열일 수 있으므로 확인)
        $notifyPostBoardsRaw = $site->getSetting('notify_post_boards', '[]');
        $notifyPostBoards = is_array($notifyPostBoardsRaw) ? $notifyPostBoardsRaw : (json_decode($notifyPostBoardsRaw, true) ?? []);
        
        $notifyCommentBoardsRaw = $site->getSetting('notify_comment_boards', '[]');
        $notifyCommentBoards = is_array($notifyCommentBoardsRaw) ? $notifyCommentBoardsRaw : (json_decode($notifyCommentBoardsRaw, true) ?? []);
        
        $settings = [
            'mail_mailer' => $site->getSetting('mail_mailer', 'smtp'),
            'mail_host' => $site->getSetting('mail_host', 'smtp.gmail.com'),
            'mail_port' => $site->getSetting('mail_port', '587'),
            'mail_username' => $mailUsername,
            'mail_password' => $site->getSetting('mail_password', ''),
            'mail_encryption' => $site->getSetting('mail_encryption', 'tls'),
            'mail_from_address' => $site->getSetting('mail_from_address', $mailUsername), // 기존 설정이 있으면 사용, 없으면 mail_username 사용
            'mail_from_name' => $site->getSetting('mail_from_name', $site->name),
            'admin_notification_email' => $site->getSetting('admin_notification_email', ''),
            'notify_new_user' => $site->getSetting('notify_new_user', false),
            'notify_new_post' => $site->getSetting('notify_new_post', false),
            'notify_new_comment' => $site->getSetting('notify_new_comment', false),
            'notify_new_message' => $site->getSetting('notify_new_message', false),
            'notify_post_boards' => $notifyPostBoards,
            'notify_comment_boards' => $notifyCommentBoards,
        ];

        // 게시판 리스트 가져오기
        $boards = Board::where('site_id', $site->id)->orderBy('name')->get();

        return view('admin.mail-settings', compact('site', 'settings', 'boards'));
    }

    /**
     * Update mail settings.
     */
    public function updateMailSettings(Site $site, Request $request)
    {
        $request->validate([
            'mail_mailer' => 'required|in:smtp,sendmail',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'required|email|max:255',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|in:tls,ssl',
            'mail_from_name' => 'nullable|string|max:255',
            'admin_notification_email' => 'nullable|email|max:255',
            'notify_new_user' => 'boolean',
            'notify_new_post' => 'boolean',
            'notify_new_comment' => 'boolean',
            'notify_new_message' => 'boolean',
        ]);

        $site->setSetting('mail_mailer', $request->input('mail_mailer'));
        $site->setSetting('mail_host', $request->input('mail_host'));
        $site->setSetting('mail_port', $request->input('mail_port'));
        $site->setSetting('mail_username', $request->input('mail_username'));
        $site->setSetting('mail_password', $request->input('mail_password'));
        $site->setSetting('mail_encryption', $request->input('mail_encryption'));
        // mail_username을 발신자 이메일로 사용
        $site->setSetting('mail_from_address', $request->input('mail_username'));
        $site->setSetting('mail_from_name', $request->input('mail_from_name', $site->name));
        $site->setSetting('admin_notification_email', $request->input('admin_notification_email', ''));
        $site->setSetting('notify_new_user', $request->boolean('notify_new_user'));
        $site->setSetting('notify_new_post', $request->boolean('notify_new_post'));
        $site->setSetting('notify_new_comment', $request->boolean('notify_new_comment'));
        $site->setSetting('notify_new_message', $request->boolean('notify_new_message'));
        
        // 게시판 선택 정보 저장
        $notifyPostBoards = $request->input('notify_post_boards', []);
        $notifyCommentBoards = $request->input('notify_comment_boards', []);
        $site->setSetting('notify_post_boards', json_encode($notifyPostBoards));
        $site->setSetting('notify_comment_boards', json_encode($notifyCommentBoards));

        return response()->json([
            'success' => true,
            'message' => '메일 설정이 저장되었습니다.',
        ]);
    }

    /**
     * Display my page settings.
     */
    public function myPageSettings(Site $site)
    {
        // 기본값 설정
        $defaultSettings = [
            // 사이드바 로그인 위젯 표시 항목
            'sidebar_widget_show_experience' => true,
            'sidebar_widget_show_rank' => true,
            'sidebar_widget_show_points' => true,
            // 로그인 위젯 하단 메뉴
            'sidebar_widget_show_notifications' => true,
            'sidebar_widget_show_messages' => true,
            'sidebar_widget_show_my_posts' => true,
            'sidebar_widget_show_profile' => true,
            'sidebar_widget_show_edit_profile' => true,
            'sidebar_widget_show_saved_posts' => true,
            'sidebar_widget_show_my_comments' => true,
            // 마이페이지 표시 항목
            'my_page_show_experience' => true,
            'my_page_show_rank' => true,
            'my_page_show_points' => true,
            // 마이페이지 하단 메뉴
            'my_page_show_notifications' => true,
            'my_page_show_messages' => true,
            'my_page_show_edit_profile' => true,
            'my_page_show_my_posts' => true,
            'my_page_show_saved_posts' => true,
            'my_page_show_my_comments' => true,
        ];

        $settings = [];
        foreach ($defaultSettings as $key => $defaultValue) {
            $settings[$key] = $site->getSetting($key, $defaultValue);
        }

        return view('admin.my-page-settings', compact('site', 'settings'));
    }

    /**
     * Update my page settings.
     */
    public function updateMyPageSettings(Site $site, Request $request)
    {
        // 체크박스는 체크되지 않으면 폼에서 전송되지 않으므로, has()로 확인하고 boolean()으로 변환
        // 체크박스가 전송되지 않으면 false로 처리
        $site->setSetting('sidebar_widget_show_experience', $request->has('sidebar_widget_show_experience') ? $request->boolean('sidebar_widget_show_experience') : false);
        $site->setSetting('sidebar_widget_show_rank', $request->has('sidebar_widget_show_rank') ? $request->boolean('sidebar_widget_show_rank') : false);
        $site->setSetting('sidebar_widget_show_points', $request->has('sidebar_widget_show_points') ? $request->boolean('sidebar_widget_show_points') : false);
        $site->setSetting('sidebar_widget_show_notifications', $request->has('sidebar_widget_show_notifications') ? $request->boolean('sidebar_widget_show_notifications') : false);
        $site->setSetting('sidebar_widget_show_messages', $request->has('sidebar_widget_show_messages') ? $request->boolean('sidebar_widget_show_messages') : false);
        $site->setSetting('sidebar_widget_show_my_posts', $request->has('sidebar_widget_show_my_posts') ? $request->boolean('sidebar_widget_show_my_posts') : false);
        $site->setSetting('sidebar_widget_show_profile', $request->has('sidebar_widget_show_profile') ? $request->boolean('sidebar_widget_show_profile') : false);
        $site->setSetting('sidebar_widget_show_edit_profile', $request->has('sidebar_widget_show_edit_profile') ? $request->boolean('sidebar_widget_show_edit_profile') : false);
        $site->setSetting('sidebar_widget_show_saved_posts', $request->has('sidebar_widget_show_saved_posts') ? $request->boolean('sidebar_widget_show_saved_posts') : false);
        $site->setSetting('sidebar_widget_show_my_comments', $request->has('sidebar_widget_show_my_comments') ? $request->boolean('sidebar_widget_show_my_comments') : false);
        $site->setSetting('my_page_show_experience', $request->has('my_page_show_experience') ? $request->boolean('my_page_show_experience') : false);
        $site->setSetting('my_page_show_rank', $request->has('my_page_show_rank') ? $request->boolean('my_page_show_rank') : false);
        $site->setSetting('my_page_show_points', $request->has('my_page_show_points') ? $request->boolean('my_page_show_points') : false);
        $site->setSetting('my_page_show_notifications', $request->has('my_page_show_notifications') ? $request->boolean('my_page_show_notifications') : false);
        $site->setSetting('my_page_show_messages', $request->has('my_page_show_messages') ? $request->boolean('my_page_show_messages') : false);
        $site->setSetting('my_page_show_edit_profile', $request->has('my_page_show_edit_profile') ? $request->boolean('my_page_show_edit_profile') : false);
        $site->setSetting('my_page_show_my_posts', $request->has('my_page_show_my_posts') ? $request->boolean('my_page_show_my_posts') : false);
        $site->setSetting('my_page_show_saved_posts', $request->has('my_page_show_saved_posts') ? $request->boolean('my_page_show_saved_posts') : false);
        $site->setSetting('my_page_show_my_comments', $request->has('my_page_show_my_comments') ? $request->boolean('my_page_show_my_comments') : false);

        return back()->with('success', '마이페이지 설정이 저장되었습니다.');
    }

    /**
     * Send test mail.
     */
    public function testMail(Site $site, Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // SiteSetting에서 메일 설정 가져오기
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
                    'address' => $mailUsername, // mail_username을 발신자 이메일로 사용
                    'name' => $site->getSetting('mail_from_name', $site->name),
                ],
            ];

            // 필수 설정 검증
            if (empty($mailConfig['username']) || empty($mailConfig['password'])) {
                return response()->json([
                    'success' => false,
                    'message' => '이메일 주소와 비밀번호를 입력해주세요.',
                ], 400);
            }

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

            \Mail::raw('이것은 테스트 메일입니다. 메일 설정이 정상적으로 작동하고 있습니다.', function ($message) use ($request, $mailConfig) {
                $message->to($request->email)
                        ->subject('[' . $mailConfig['from']['name'] . '] 메일 설정 테스트');
            });

            return response()->json([
                'success' => true,
                'message' => '테스트 메일이 발송되었습니다. 이메일을 확인해주세요.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Test mail failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '메일 발송에 실패했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display sidebar widgets management page.
     */
    public function sidebarWidgets(Site $site, Request $request)
    {
        // 사이드바 로그인 위젯 활성화 설정 저장
        if ($request->has('enable_sidebar_login_widget')) {
            $site->setSetting('enable_sidebar_login_widget', $request->boolean('enable_sidebar_login_widget'));
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '설정이 저장되었습니다.',
                ]);
            }
        }
        
        // 사이드바 모바일 표시 설정 저장
        if ($request->has('sidebar_mobile_display')) {
            $displayValue = $request->input('sidebar_mobile_display');
            if (in_array($displayValue, ['top', 'bottom', 'none'])) {
                $site->setSetting('sidebar_mobile_display', $displayValue);
            }
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '설정이 저장되었습니다.',
                ]);
            }
        }
        
        // 위젯 정보 조회 (AJAX 요청)
        if ($request->has('widget_id')) {
            $widget = SidebarWidget::where('site_id', $site->id)
                ->where('id', $request->widget_id)
                ->first();
            
            if ($widget) {
                return response()->json([
                    'success' => true,
                    'widget' => $widget,
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => '위젯을 찾을 수 없습니다.',
            ], 404);
        }
        
        $widgets = SidebarWidget::where('site_id', $site->id)
            ->orderBy('order')
            ->get();
        
        // 플랜에 활성화된 위젯 타입만 필터링
        $allTypes = SidebarWidget::getAvailableTypes();
        $availableTypes = [];
        foreach ($allTypes as $key => $label) {
            if ($site->hasSidebarWidgetType($key)) {
                $availableTypes[$key] = $label;
            }
        }
        
        return view('admin.sidebar-widgets', compact('site', 'widgets', 'availableTypes'));
    }

    /**
     * Store a new sidebar widget.
     */
    public function storeSidebarWidget(Site $site, Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'nullable|string|max:255',
            'settings' => 'nullable|string',
        ]);

        $maxOrder = SidebarWidget::where('site_id', $site->id)->max('order') ?? 0;

        // settings 처리
        $settings = [];
        if ($request->has('settings') && !empty($request->settings)) {
            $settingsInput = $request->settings;
            if (is_string($settingsInput)) {
                $decoded = json_decode($settingsInput, true);
                $settings = is_array($decoded) ? $decoded : [];
            } elseif (is_array($settingsInput)) {
                $settings = $settingsInput;
            }
        }
        
        // 블록 타입 이미지 업로드 처리
        if ($request->type === 'block' && $request->hasFile('block_background_image_file')) {
            $image = $request->file('block_background_image_file');
            $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($image, $directory);
            $settings['background_image_url'] = asset('storage/' . $result['file_path']);
        }
        
        // 블록 슬라이드 타입 이미지 업로드 처리
        if ($request->type === 'block_slide' && isset($settings['blocks']) && is_array($settings['blocks'])) {
            foreach ($settings['blocks'] as $index => &$block) {
                if (isset($block['background_type']) && $block['background_type'] === 'image') {
                    // FormData에서 이미지 파일 찾기
                    $fileKey = "block_slide.{$index}.background_image_file";
                    if ($request->hasFile($fileKey)) {
                        $image = $request->file($fileKey);
                        $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                        $result = $this->fileUploadService->upload($image, $directory);
                        $block['background_image_url'] = asset('storage/' . $result['file_path']);
                    }
                }
            }
            unset($block); // 참조 해제
        }
        
        // 이미지 위젯 이미지 업로드 처리
        if ($request->type === 'image' && $request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($image, $directory);
            $settings['image_url'] = asset('storage/' . $result['file_path']);
        }
        
        // 이미지 슬라이드 타입 이미지 업로드 처리
        if ($request->type === 'image_slide' && isset($settings['images']) && is_array($settings['images'])) {
            foreach ($settings['images'] as $index => &$imageItem) {
                // FormData에서 이미지 파일 찾기
                $fileKey = "image_slide.{$index}.image_file";
                if ($request->hasFile($fileKey)) {
                    $image = $request->file($fileKey);
                    $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                    $result = $this->fileUploadService->upload($image, $directory);
                    $imageItem['image_url'] = asset('storage/' . $result['file_path']);
                }
            }
            unset($imageItem); // 참조 해제
        }
        
        // 제목 처리: 갤러리 위젯의 경우 제목이 없으면 빈 문자열로 저장
        $title = $request->title;
        if ($request->type === 'gallery' && (empty($title) || trim($title) === '')) {
            $title = '';
        }
        
        $widget = SidebarWidget::create([
            'site_id' => $site->id,
            'type' => $request->type,
            'title' => $title ?? '',
            'settings' => $settings,
            'order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => '위젯이 추가되었습니다.',
            'widget' => $widget,
        ]);
    }

    /**
     * Update a sidebar widget.
     */
    public function updateSidebarWidget(Site $site, SidebarWidget $widget, Request $request)
    {
        // Ensure widget belongs to site
        if ($widget->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'settings' => 'nullable|string',
            'is_active' => 'nullable|in:0,1,true,false',
        ]);

        // settings 처리
        $settings = $widget->settings ?? [];
        if ($request->has('settings')) {
            $settingsInput = $request->settings;
            if (is_string($settingsInput)) {
                $settings = json_decode($settingsInput, true) ?? $settings;
            } else {
                $settings = $settingsInput;
            }
        }
        
        // 블록 타입 이미지 업로드 처리
        if ($widget->type === 'block' && $request->hasFile('block_background_image_file')) {
            $image = $request->file('block_background_image_file');
            $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($image, $directory);
            $settings['background_image_url'] = asset('storage/' . $result['file_path']);
        }
        
        // 블록 슬라이드 타입 이미지 업로드 처리
        if ($widget->type === 'block_slide' && isset($settings['blocks']) && is_array($settings['blocks'])) {
            foreach ($settings['blocks'] as $index => &$block) {
                if (isset($block['background_type']) && $block['background_type'] === 'image') {
                    // FormData에서 이미지 파일 찾기
                    $fileKey = "edit_block_slide.{$index}.background_image_file";
                    if ($request->hasFile($fileKey)) {
                        $image = $request->file($fileKey);
                        $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                        $result = $this->fileUploadService->upload($image, $directory);
                        $block['background_image_url'] = asset('storage/' . $result['file_path']);
                    }
                }
            }
            unset($block); // 참조 해제
        }
        
        // 이미지 위젯 이미지 업로드 처리
        if ($widget->type === 'image' && $request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($image, $directory);
            $settings['image_url'] = asset('storage/' . $result['file_path']);
        }
        
        // 이미지 슬라이드 타입 이미지 업로드 처리
        if ($widget->type === 'image_slide' && isset($settings['images']) && is_array($settings['images'])) {
            foreach ($settings['images'] as $index => &$imageItem) {
                // FormData에서 이미지 파일 찾기
                $fileKey = "edit_image_slide.{$index}.image_file";
                if ($request->hasFile($fileKey)) {
                    $image = $request->file($fileKey);
                    $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                    $result = $this->fileUploadService->upload($image, $directory);
                    $imageItem['image_url'] = asset('storage/' . $result['file_path']);
                }
            }
            unset($imageItem); // 참조 해제
        }

        // is_active 처리
        $isActive = $widget->is_active;
        if ($request->has('is_active')) {
            $isActiveValue = $request->input('is_active');
            if (in_array($isActiveValue, ['1', 'true', true, 1], true)) {
                $isActive = true;
            } elseif (in_array($isActiveValue, ['0', 'false', false, 0], true)) {
                $isActive = false;
            }
        }

        // 제목 처리: 갤러리 위젯의 경우 제목이 없으면 빈 문자열로 저장
        $title = $request->title;
        if ($widget->type === 'gallery' && (empty($title) || trim($title) === '')) {
            $title = '';
        }

        $widget->update([
            'title' => $title ?? '',
            'settings' => $settings,
            'is_active' => $isActive,
        ]);

        return response()->json([
            'success' => true,
            'message' => '위젯이 수정되었습니다.',
            'widget' => $widget->fresh(),
        ]);
    }

    /**
     * Delete a sidebar widget.
     */
    public function deleteSidebarWidget(Site $site, SidebarWidget $widget)
    {
        // Ensure widget belongs to site
        if ($widget->site_id !== $site->id) {
            abort(403);
        }

        $widget->delete();

        return response()->json([
            'success' => true,
            'message' => '위젯이 삭제되었습니다.',
        ]);
    }

    /**
     * Reorder sidebar widgets.
     */
    public function reorderSidebarWidgets(Site $site, Request $request)
    {
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:sidebar_widgets,id',
            'widgets.*.order' => 'required|integer',
        ]);

        foreach ($request->widgets as $widgetData) {
            SidebarWidget::where('id', $widgetData['id'])
                ->where('site_id', $site->id)
                ->update(['order' => $widgetData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => '위젯 순서가 저장되었습니다.',
        ]);
    }

    /**
     * Display main widgets management page.
     */
    public function mainWidgets(Site $site, Request $request)
    {
        // 컨테이너 정보 조회 (AJAX 요청)
        if ($request->has('container_id')) {
            $container = MainWidgetContainer::where('site_id', $site->id)
                ->where('id', $request->container_id)
                ->with('widgets')
                ->first();
            
            if ($container) {
                return response()->json([
                    'success' => true,
                    'container' => $container,
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => '컨테이너를 찾을 수 없습니다.',
            ], 404);
        }
        
        // 위젯 정보 조회 (AJAX 요청)
        if ($request->has('widget_id')) {
            $widget = MainWidget::where('site_id', $site->id)
                ->where('id', $request->widget_id)
                ->first();
            
            if ($widget) {
                return response()->json([
                    'success' => true,
                    'widget' => $widget,
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => '위젯을 찾을 수 없습니다.',
            ], 404);
        }
        
        $containers = MainWidgetContainer::where('site_id', $site->id)
            ->orderBy('order')
            ->with(['widgets' => function($query) {
                $query->orderBy('column_index')->orderBy('order');
            }])
            ->get();
        
        // 플랜에 활성화된 위젯 타입만 필터링
        $allTypes = MainWidget::getAvailableTypes();
        $availableTypes = [];
        foreach ($allTypes as $key => $label) {
            if ($site->hasMainWidgetType($key)) {
                $availableTypes[$key] = $label;
            }
        }
        
        return view('admin.main-widgets', compact('site', 'containers', 'availableTypes'));
    }

    /**
     * Store a new main widget container.
     */
    public function storeMainWidgetContainer(Site $site, Request $request)
    {
        $request->validate([
            'columns' => 'required|integer|in:1,2,3,4',
            'vertical_align' => 'nullable|string|in:top,center,bottom',
            'full_width' => 'nullable|boolean',
            'full_height' => 'nullable|boolean',
            'widget_spacing' => 'nullable|integer|min:0|max:5',
        ]);

        $maxOrder = MainWidgetContainer::where('site_id', $site->id)->max('order') ?? 0;
        
        // 사이드바가 없을 때만 full_width 허용
        $themeSidebar = $site->getSetting('theme_sidebar', 'left');
        $fullWidth = false;
        if ($themeSidebar === 'none' && $request->has('full_width')) {
            $fullWidthValue = $request->input('full_width');
            $fullWidth = ($fullWidthValue == '1' || $fullWidthValue === '1' || $fullWidthValue === true || $fullWidthValue === 1);
        }
        
        // full_height 처리
        $fullHeight = false;
        if ($request->has('full_height')) {
            $fullHeightValue = $request->input('full_height');
            $fullHeight = ($fullHeightValue == '1' || $fullHeightValue === '1' || $fullHeightValue === true || $fullHeightValue === 1);
        }

        $container = MainWidgetContainer::create([
            'site_id' => $site->id,
            'columns' => $request->columns,
            'vertical_align' => $request->vertical_align ?? 'top',
            'full_width' => $fullWidth,
            'full_height' => $fullHeight,
            'widget_spacing' => $request->widget_spacing ?? 3,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => '컨테이너가 추가되었습니다.',
            'container' => $container,
        ]);
    }

    /**
     * Delete a main widget container.
     */
    public function updateMainWidgetContainer(Site $site, MainWidgetContainer $container, Request $request)
    {
        // Ensure container belongs to site
        if ($container->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'columns' => 'sometimes|required|integer|in:1,2,3,4',
            'column_merges' => 'nullable|string',
            'vertical_align' => 'nullable|string|in:top,center,bottom',
            'full_width' => 'nullable|boolean',
            'full_height' => 'nullable|boolean',
            'widget_spacing' => 'nullable|integer|min:0|max:5',
            'background_type' => 'nullable|string|in:none,color,gradient,image',
            'background_color' => 'nullable|string|max:7',
            'background_gradient_start' => 'nullable|string|max:7',
            'background_gradient_end' => 'nullable|string|max:7',
            'background_gradient_direction' => 'nullable|string|max:50',
            'background_image_url' => 'nullable|string|max:500',
        ]);

        $oldColumns = $container->columns;
        $newColumns = $request->has('columns') ? $request->columns : $container->columns;

        // 컬럼 수가 줄어들면, 삭제되는 컬럼의 위젯들을 기존 컬럼들에 재배치
        if ($newColumns < $oldColumns) {
            // 삭제되는 컬럼의 위젯들을 가져옴
            $widgetsToRelocate = MainWidget::where('container_id', $container->id)
                ->where('column_index', '>=', $newColumns)
                ->orderBy('column_index')
                ->orderBy('order')
                ->get();
            
            // 각 기존 컬럼의 최대 order 값 계산
            $maxOrders = [];
            for ($i = 0; $i < $newColumns; $i++) {
                $maxOrder = MainWidget::where('container_id', $container->id)
                    ->where('column_index', $i)
                    ->max('order');
                $maxOrders[$i] = $maxOrder ?? 0;
            }
            
            // 위젯들을 기존 컬럼들에 순서대로 재배치 (round-robin 방식)
            $currentColumn = 0;
            foreach ($widgetsToRelocate as $widget) {
                $maxOrders[$currentColumn]++;
                $widget->column_index = $currentColumn;
                $widget->order = $maxOrders[$currentColumn];
                $widget->save();
                
                // 다음 컬럼으로 이동 (round-robin)
                $currentColumn = ($currentColumn + 1) % $newColumns;
            }
        }

        $container->columns = $newColumns;
        if ($request->has('vertical_align')) {
            $container->vertical_align = $request->vertical_align;
        }
        
        // 사이드바가 없을 때만 full_width 허용
        $themeSidebar = $site->getSetting('theme_sidebar', 'left');
        if ($themeSidebar === 'none') {
            // full_width 값이 명시적으로 전송되었는지 확인
            if ($request->has('full_width')) {
                $fullWidthValue = $request->input('full_width');
                $container->full_width = ($fullWidthValue == '1' || $fullWidthValue === '1' || $fullWidthValue === true || $fullWidthValue === 1);
            } else {
                // full_width 값이 전송되지 않았으면 false로 설정
                $container->full_width = false;
            }
        } else {
            // 사이드바가 있으면 항상 false
            $container->full_width = false;
        }
        
        // full_height 처리
        if ($request->has('full_height')) {
            $fullHeightValue = $request->input('full_height');
            $container->full_height = ($fullHeightValue == '1' || $fullHeightValue === '1' || $fullHeightValue === true || $fullHeightValue === 1);
        } else {
            // full_height 값이 전송되지 않았으면 false로 설정
            $container->full_height = false;
        }
        
        // widget_spacing 처리
        if ($request->has('widget_spacing')) {
            $container->widget_spacing = $request->widget_spacing;
        }
        
        // column_merges 처리
        if ($request->has('column_merges')) {
            $columnMerges = json_decode($request->column_merges, true);
            if (is_array($columnMerges)) {
                // 유효성 검사: 병합 정보가 컬럼 수를 초과하지 않도록
                $validMerges = [];
                foreach ($columnMerges as $colIndex => $span) {
                    if (is_numeric($colIndex) && is_numeric($span) && $colIndex >= 0 && $colIndex < $newColumns && $span > 0) {
                        $validMerges[(int)$colIndex] = (int)$span;
                    }
                }
                $container->column_merges = $validMerges;
            } else {
                $container->column_merges = null;
            }
        }
        
        // 배경 설정 처리
        if ($request->has('background_type')) {
            $container->background_type = $request->background_type;
            
            if ($request->background_type === 'color') {
                $container->background_color = $request->background_color ?? null;
                $container->background_gradient_start = null;
                $container->background_gradient_end = null;
                $container->background_gradient_direction = null;
                $container->background_image_url = null;
            } elseif ($request->background_type === 'gradient') {
                $container->background_color = null;
                $container->background_gradient_start = $request->background_gradient_start ?? null;
                $container->background_gradient_end = $request->background_gradient_end ?? null;
                $container->background_gradient_direction = $request->background_gradient_direction ?? 'to right';
                $container->background_image_url = null;
            } elseif ($request->background_type === 'image') {
                $container->background_color = null;
                $container->background_gradient_start = null;
                $container->background_gradient_end = null;
                $container->background_gradient_direction = null;
                $container->background_image_url = $request->background_image_url ?? null;
            } else {
                // none
                $container->background_color = null;
                $container->background_gradient_start = null;
                $container->background_gradient_end = null;
                $container->background_gradient_direction = null;
                $container->background_image_url = null;
            }
        }
        
        $container->save();

        return response()->json([
            'success' => true,
            'message' => '컨테이너가 업데이트되었습니다.',
            'container' => $container,
        ]);
    }

    public function deleteMainWidgetContainer(Site $site, MainWidgetContainer $container)
    {
        // Ensure container belongs to site
        if ($container->site_id !== $site->id) {
            abort(403);
        }

        $container->delete();

        return response()->json([
            'success' => true,
            'message' => '컨테이너가 삭제되었습니다.',
        ]);
    }

    /**
     * Store a new main widget.
     */
    public function storeMainWidget(Site $site, Request $request)
    {
        try {
            $request->validate([
                'container_id' => 'required|exists:main_widget_containers,id',
                'column_index' => 'required|integer|min:0',
                'type' => 'required|string',
                'title' => 'nullable|string|max:255',
                'settings' => 'nullable|string',
            ]);

            // Ensure container belongs to site
            $container = MainWidgetContainer::where('id', $request->container_id)
                ->where('site_id', $site->id)
                ->first();
            
            if (!$container) {
                return response()->json([
                    'success' => false,
                    'message' => '컨테이너를 찾을 수 없습니다.',
                ], 404);
            }

            // Validate column_index
            if ($request->column_index >= $container->columns) {
                return response()->json([
                    'success' => false,
                    'message' => '잘못된 칸 인덱스입니다.',
                ], 400);
            }

            $maxOrder = MainWidget::where('container_id', $container->id)
                ->where('column_index', $request->column_index)
                ->max('order') ?? 0;

            // settings 처리
            $settings = [];
            if ($request->has('settings') && !empty($request->settings)) {
                $settingsInput = $request->settings;
                if (is_string($settingsInput)) {
                    $decoded = json_decode($settingsInput, true);
                    $settings = is_array($decoded) ? $decoded : [];
                } elseif (is_array($settingsInput)) {
                    $settings = $settingsInput;
                }
            }
            
            // 블록 타입 이미지 업로드 처리
            if ($request->type === 'block' && $request->hasFile('block_background_image_file')) {
                $image = $request->file('block_background_image_file');
                $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                $result = $this->fileUploadService->upload($image, $directory);
                $settings['background_image_url'] = asset('storage/' . $result['file_path']);
            }
            
            // 블록 슬라이드 타입 이미지 업로드 처리
            if ($request->type === 'block_slide' && isset($settings['blocks']) && is_array($settings['blocks'])) {
                foreach ($settings['blocks'] as $index => &$block) {
                    if (isset($block['background_type']) && $block['background_type'] === 'image') {
                        $fileKey = "block_slide.{$index}.background_image_file";
                        if ($request->hasFile($fileKey)) {
                            $image = $request->file($fileKey);
                            $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                            $result = $this->fileUploadService->upload($image, $directory);
                            $block['background_image_url'] = asset('storage/' . $result['file_path']);
                        }
                    }
                }
                unset($block);
            }
            
            // 이미지 위젯 이미지 업로드 처리
            if ($request->type === 'image' && $request->hasFile('image_file')) {
                $image = $request->file('image_file');
                $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                $result = $this->fileUploadService->upload($image, $directory);
                $settings['image_url'] = asset('storage/' . $result['file_path']);
            }
            
            // 이미지 슬라이드 타입 이미지 업로드 처리
            if ($request->type === 'image_slide' && isset($settings['images']) && is_array($settings['images'])) {
                foreach ($settings['images'] as $index => &$imageItem) {
                    $fileKey = "image_slide.{$index}.image_file";
                    if ($request->hasFile($fileKey)) {
                        $image = $request->file($fileKey);
                        $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                        $result = $this->fileUploadService->upload($image, $directory);
                        $imageItem['image_url'] = asset('storage/' . $result['file_path']);
                    }
                }
                unset($imageItem);
            }
            
            // 제목 처리: 갤러리 위젯의 경우 제목이 없으면 빈 문자열로 저장
            $title = $request->title;
            if ($request->type === 'gallery' && (empty($title) || trim($title) === '')) {
                $title = '';
            }
            
            $widget = MainWidget::create([
                'site_id' => $site->id,
                'container_id' => $container->id,
                'column_index' => $request->column_index,
                'type' => $request->type,
                'title' => $title ?? '',
                'settings' => $settings,
                'order' => $maxOrder + 1,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => '위젯이 추가되었습니다.',
                'widget' => $widget,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 정보를 확인해주세요.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('위젯 추가 오류: ' . $e->getMessage(), [
                'site_id' => $site->id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => '위젯 추가 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a main widget.
     */
    public function updateMainWidget(Site $site, MainWidget $widget, Request $request)
    {
        // Ensure widget belongs to site
        if ($widget->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'settings' => 'nullable|string',
            'is_active' => 'nullable|in:0,1,true,false',
        ]);

        // settings 처리
        $settings = $widget->settings ?? [];
        if ($request->has('settings')) {
            $settingsInput = $request->settings;
            if (is_string($settingsInput)) {
                $settings = json_decode($settingsInput, true) ?? $settings;
            } else {
                $settings = $settingsInput;
            }
        }
        
        // 블록 타입 이미지 업로드 처리
        if ($widget->type === 'block' && $request->hasFile('block_background_image_file')) {
            $image = $request->file('block_background_image_file');
            $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($image, $directory);
            $settings['background_image_url'] = asset('storage/' . $result['file_path']);
        }
        
        // 블록 슬라이드 타입 이미지 업로드 처리
        if ($widget->type === 'block_slide' && isset($settings['blocks']) && is_array($settings['blocks'])) {
            foreach ($settings['blocks'] as $index => &$block) {
                if (isset($block['background_type']) && $block['background_type'] === 'image') {
                    $fileKey = "edit_block_slide.{$index}.background_image_file";
                    if ($request->hasFile($fileKey)) {
                        $image = $request->file($fileKey);
                        $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                        $result = $this->fileUploadService->upload($image, $directory);
                        $block['background_image_url'] = asset('storage/' . $result['file_path']);
                    }
                }
            }
            unset($block);
        }
        
        // 이미지 위젯 이미지 업로드 처리
        if ($widget->type === 'image' && $request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($image, $directory);
            $settings['image_url'] = asset('storage/' . $result['file_path']);
        }
        
        // 이미지 슬라이드 타입 이미지 업로드 처리
        if ($widget->type === 'image_slide' && isset($settings['images']) && is_array($settings['images'])) {
            foreach ($settings['images'] as $index => &$imageItem) {
                $fileKey = "edit_image_slide.{$index}.image_file";
                if ($request->hasFile($fileKey)) {
                    $image = $request->file($fileKey);
                    $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                    $result = $this->fileUploadService->upload($image, $directory);
                    $imageItem['image_url'] = asset('storage/' . $result['file_path']);
                }
            }
            unset($imageItem);
        }

        // is_active 처리
        $isActive = $widget->is_active;
        if ($request->has('is_active')) {
            $isActiveValue = $request->input('is_active');
            if (in_array($isActiveValue, ['1', 'true', true, 1], true)) {
                $isActive = true;
            } elseif (in_array($isActiveValue, ['0', 'false', false, 0], true)) {
                $isActive = false;
            }
        }

        // 제목 처리: 갤러리 위젯의 경우 제목이 없으면 빈 문자열로 저장
        $title = $request->input('title', '');
        if ($widget->type === 'gallery' && (empty($title) || trim($title) === '')) {
            $title = '';
        }

        // title이 null이면 빈 문자열로 설정
        if ($title === null) {
            $title = '';
        }

        $widget->title = $title;
        $widget->settings = $settings;
        $widget->is_active = $isActive;
        $widget->save();

        return response()->json([
            'success' => true,
            'message' => '위젯이 수정되었습니다.',
            'widget' => $widget->fresh(),
        ]);
    }

    /**
     * Reorder main widgets within a container column.
     */
    public function reorderMainWidgets(Site $site, Request $request)
    {
        $request->validate([
            'container_id' => 'required|integer',
            'column_index' => 'required|integer',
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|integer',
            'widgets.*.order' => 'required|integer',
            'widgets.*.container_id' => 'nullable|integer', // 이동된 위젯의 새 컨테이너 ID
            'widgets.*.column_index' => 'nullable|integer', // 이동된 위젯의 새 컬럼 인덱스
        ]);

        $containerId = $request->container_id;
        $columnIndex = $request->column_index;
        $widgets = $request->widgets;

        // 컨테이너가 해당 사이트에 속하는지 확인
        $container = MainWidgetContainer::where('id', $containerId)
            ->where('site_id', $site->id)
            ->first();

        if (!$container) {
            return response()->json([
                'success' => false,
                'message' => '컨테이너를 찾을 수 없습니다.',
            ], 404);
        }

        // 위젯 순서 업데이트 및 컨테이너 이동 처리
        foreach ($widgets as $widgetData) {
            $widget = MainWidget::where('id', $widgetData['id'])
                ->where('site_id', $site->id)
                ->first();

            if (!$widget) {
                continue;
            }

            // 위젯이 다른 컨테이너로 이동한 경우
            if (isset($widgetData['container_id']) && isset($widgetData['column_index'])) {
                $newContainerId = $widgetData['container_id'];
                $newColumnIndex = $widgetData['column_index'];
                
                // 새 컨테이너가 해당 사이트에 속하는지 확인
                $newContainer = MainWidgetContainer::where('id', $newContainerId)
                    ->where('site_id', $site->id)
                    ->first();
                
                if ($newContainer && $newColumnIndex < $newContainer->columns) {
                    // 위젯을 새 컨테이너로 이동
                    $widget->container_id = $newContainerId;
                    $widget->column_index = $newColumnIndex;
                    $widget->order = $widgetData['order'];
                    $widget->save();
                }
            } else {
                // 같은 컨테이너 내에서 순서만 변경
                if ($widget->container_id == $containerId && $widget->column_index == $columnIndex) {
                    $widget->update(['order' => $widgetData['order']]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => '위젯 순서가 업데이트되었습니다.',
        ]);
    }

    /**
     * Delete a main widget.
     */
    public function deleteMainWidget(Site $site, MainWidget $widget)
    {
        // Ensure widget belongs to site
        if ($widget->site_id !== $site->id) {
            abort(403);
        }

        $widget->delete();

        return response()->json([
            'success' => true,
            'message' => '위젯이 삭제되었습니다.',
        ]);
    }

    /**
     * Reorder main widget containers.
     */
    public function reorderMainWidgetContainers(Site $site, Request $request)
    {
        $request->validate([
            'containers' => 'required|array',
            'containers.*.id' => 'required|exists:main_widget_containers,id',
            'containers.*.order' => 'required|integer',
        ]);

        foreach ($request->containers as $containerData) {
            MainWidgetContainer::where('id', $containerData['id'])
                ->where('site_id', $site->id)
                ->update(['order' => $containerData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => '컨테이너 순서가 저장되었습니다.',
        ]);
    }

    /**
     * Display custom pages management page.
     */
    public function customPages(Site $site)
    {
        $customPages = \App\Models\CustomPage::where('site_id', $site->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.custom-pages', compact('site', 'customPages'));
    }

    /**
     * Store a new custom page.
     */
    public function storeCustomPage(Site $site, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:custom_pages,slug',
            'description' => 'nullable|string',
        ]);

        $customPage = \App\Models\CustomPage::create([
            'site_id' => $site->id,
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => '페이지가 생성되었습니다.',
            'customPage' => $customPage,
        ]);
    }

    /**
     * Display custom page edit page.
     */
    public function editCustomPage(Site $site, \App\Models\CustomPage $customPage, Request $request)
    {
        // Ensure custom page belongs to site
        if ($customPage->site_id !== $site->id) {
            abort(403);
        }

        // 위젯 정보 조회 (AJAX 요청)
        if ($request->has('widget_id')) {
            $widget = CustomPageWidget::where('custom_page_id', $customPage->id)
                ->where('id', $request->widget_id)
                ->first();
            
            if ($widget) {
                return response()->json([
                    'success' => true,
                    'widget' => $widget,
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => '위젯을 찾을 수 없습니다.',
            ], 404);
        }

        $containers = $customPage->containers()
            ->orderBy('order')
            ->with(['widgets' => function($query) {
                $query->orderBy('column_index')->orderBy('order');
            }])
            ->get();
        
        // 플랜에 활성화된 위젯 타입만 필터링
        $allTypes = \App\Models\CustomPageWidget::getAvailableTypes();
        $availableTypes = [];
        foreach ($allTypes as $key => $label) {
            if ($site->hasCustomPageWidgetType($key)) {
                $availableTypes[$key] = $label;
            }
        }
        
        return view('admin.custom-page-edit', compact('site', 'customPage', 'containers', 'availableTypes'));
    }

    /**
     * Update a custom page.
     */
    public function updateCustomPage(Site $site, \App\Models\CustomPage $customPage, Request $request)
    {
        // Ensure custom page belongs to site
        if ($customPage->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:custom_pages,slug,' . $customPage->id,
            'description' => 'nullable|string',
        ]);

        $customPage->name = $request->name;
        $customPage->slug = $request->slug;
        $customPage->description = $request->description;
        $customPage->save();

        return response()->json([
            'success' => true,
            'message' => '페이지가 업데이트되었습니다.',
            'customPage' => $customPage,
        ]);
    }

    /**
     * Delete a custom page.
     */
    public function deleteCustomPage(Site $site, \App\Models\CustomPage $customPage)
    {
        // Ensure custom page belongs to site
        if ($customPage->site_id !== $site->id) {
            abort(403);
        }

        $customPage->delete();

        return response()->json([
            'success' => true,
            'message' => '페이지가 삭제되었습니다.',
        ]);
    }

    /**
     * Store a new custom page widget container.
     */
    public function storeCustomPageWidgetContainer(Site $site, CustomPage $customPage, Request $request)
    {
        // Ensure custom page belongs to site
        if ($customPage->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'columns' => 'required|integer|in:1,2,3,4',
            'vertical_align' => 'nullable|string|in:top,center,bottom',
            'full_width' => 'nullable|boolean',
            'full_height' => 'nullable|boolean',
        ]);

        $maxOrder = CustomPageWidgetContainer::where('custom_page_id', $customPage->id)->max('order') ?? 0;
        
        // 사이드바가 없을 때만 full_width 허용
        $themeSidebar = $site->getSetting('theme_sidebar', 'left');
        $fullWidth = false;
        if ($themeSidebar === 'none' && $request->has('full_width')) {
            $fullWidthValue = $request->input('full_width');
            $fullWidth = ($fullWidthValue == '1' || $fullWidthValue === '1' || $fullWidthValue === true || $fullWidthValue === 1);
        }
        
        // full_height 처리
        $fullHeight = false;
        if ($request->has('full_height')) {
            $fullHeightValue = $request->input('full_height');
            $fullHeight = ($fullHeightValue == '1' || $fullHeightValue === '1' || $fullHeightValue === true || $fullHeightValue === 1);
        }

        $container = CustomPageWidgetContainer::create([
            'custom_page_id' => $customPage->id,
            'columns' => $request->columns,
            'vertical_align' => $request->vertical_align ?? 'top',
            'full_width' => $fullWidth,
            'full_height' => $fullHeight,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => '컨테이너가 추가되었습니다.',
            'container' => $container,
        ]);
    }

    /**
     * Update a custom page widget container.
     */
    public function updateCustomPageWidgetContainer(Site $site, CustomPage $customPage, CustomPageWidgetContainer $container, Request $request)
    {
        // Ensure custom page and container belong to site
        if ($customPage->site_id !== $site->id || $container->custom_page_id !== $customPage->id) {
            abort(403);
        }

        $request->validate([
            'columns' => 'sometimes|required|integer|in:1,2,3,4',
            'column_merges' => 'nullable|string',
            'vertical_align' => 'nullable|string|in:top,center,bottom',
            'full_width' => 'nullable|boolean',
            'full_height' => 'nullable|boolean',
            'background_type' => 'nullable|string|in:none,color,gradient,image',
            'background_color' => 'nullable|string|max:7',
            'background_gradient_start' => 'nullable|string|max:7',
            'background_gradient_end' => 'nullable|string|max:7',
            'background_gradient_direction' => 'nullable|string|max:50',
            'background_image_url' => 'nullable|string|max:500',
        ]);

        $oldColumns = $container->columns;
        $newColumns = $request->has('columns') ? $request->columns : $container->columns;

        // 컬럼 수가 줄어들면, 삭제되는 컬럼의 위젯들을 기존 컬럼들에 재배치
        if ($newColumns < $oldColumns) {
            // 삭제되는 컬럼의 위젯들을 가져옴
            $widgetsToRelocate = CustomPageWidget::where('container_id', $container->id)
                ->where('column_index', '>=', $newColumns)
                ->orderBy('column_index')
                ->orderBy('order')
                ->get();
            
            // 각 기존 컬럼의 최대 order 값 계산
            $maxOrders = [];
            for ($i = 0; $i < $newColumns; $i++) {
                $maxOrder = CustomPageWidget::where('container_id', $container->id)
                    ->where('column_index', $i)
                    ->max('order');
                $maxOrders[$i] = $maxOrder ?? 0;
            }
            
            // 위젯들을 기존 컬럼들에 순서대로 재배치 (round-robin 방식)
            $currentColumn = 0;
            foreach ($widgetsToRelocate as $widget) {
                $maxOrders[$currentColumn]++;
                $widget->column_index = $currentColumn;
                $widget->order = $maxOrders[$currentColumn];
                $widget->save();
                
                // 다음 컬럼으로 이동 (round-robin)
                $currentColumn = ($currentColumn + 1) % $newColumns;
            }
        }

        $container->columns = $newColumns;
        if ($request->has('vertical_align')) {
            $container->vertical_align = $request->vertical_align;
        }
        
        // 사이드바가 없을 때만 full_width 허용
        $themeSidebar = $site->getSetting('theme_sidebar', 'left');
        if ($themeSidebar === 'none' && $request->has('full_width')) {
            $fullWidthValue = $request->input('full_width');
            $container->full_width = ($fullWidthValue == '1' || $fullWidthValue === '1' || $fullWidthValue === true || $fullWidthValue === 1);
        } else {
            $container->full_width = false;
        }
        
        // full_height 처리
        if ($request->has('full_height')) {
            $fullHeightValue = $request->input('full_height');
            $container->full_height = ($fullHeightValue == '1' || $fullHeightValue === '1' || $fullHeightValue === true || $fullHeightValue === 1);
        } else {
            // full_height 값이 전송되지 않았으면 false로 설정
            $container->full_height = false;
        }
        
        // column_merges 처리
        if ($request->has('column_merges')) {
            $columnMerges = json_decode($request->column_merges, true);
            if (is_array($columnMerges)) {
                // 유효성 검사: 병합 정보가 컬럼 수를 초과하지 않도록
                $validMerges = [];
                foreach ($columnMerges as $colIndex => $span) {
                    if (is_numeric($colIndex) && is_numeric($span) && $colIndex >= 0 && $colIndex < $newColumns && $span > 0) {
                        $validMerges[(int)$colIndex] = (int)$span;
                    }
                }
                $container->column_merges = $validMerges;
            } else {
                $container->column_merges = null;
            }
        }
        
        // 배경 설정 처리
        if ($request->has('background_type')) {
            $container->background_type = $request->background_type;
            
            if ($request->background_type === 'color') {
                $container->background_color = $request->background_color ?? null;
                $container->background_gradient_start = null;
                $container->background_gradient_end = null;
                $container->background_gradient_direction = null;
                $container->background_image_url = null;
            } elseif ($request->background_type === 'gradient') {
                $container->background_color = null;
                $container->background_gradient_start = $request->background_gradient_start ?? null;
                $container->background_gradient_end = $request->background_gradient_end ?? null;
                $container->background_gradient_direction = $request->background_gradient_direction ?? 'to right';
                $container->background_image_url = null;
            } elseif ($request->background_type === 'image') {
                $container->background_color = null;
                $container->background_gradient_start = null;
                $container->background_gradient_end = null;
                $container->background_gradient_direction = null;
                $container->background_image_url = $request->background_image_url ?? null;
            } else {
                // none
                $container->background_color = null;
                $container->background_gradient_start = null;
                $container->background_gradient_end = null;
                $container->background_gradient_direction = null;
                $container->background_image_url = null;
            }
        }
        
        $container->save();

        return response()->json([
            'success' => true,
            'message' => '컨테이너가 업데이트되었습니다.',
            'container' => $container,
        ]);
    }

    /**
     * Delete a custom page widget container.
     */
    public function deleteCustomPageWidgetContainer(Site $site, CustomPage $customPage, CustomPageWidgetContainer $container)
    {
        // Ensure custom page and container belong to site
        if ($customPage->site_id !== $site->id || $container->custom_page_id !== $customPage->id) {
            abort(403);
        }

        $container->delete();

        return response()->json([
            'success' => true,
            'message' => '컨테이너가 삭제되었습니다.',
        ]);
    }

    /**
     * Store a new custom page widget.
     */
    public function storeCustomPageWidget(Site $site, CustomPage $customPage, Request $request)
    {
        // Ensure custom page belongs to site
        if ($customPage->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'container_id' => 'required|exists:custom_page_widget_containers,id',
            'column_index' => 'required|integer|min:0',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'settings' => 'nullable|string',
        ]);

        // Ensure container belongs to custom page
        $container = CustomPageWidgetContainer::where('id', $request->container_id)
            ->where('custom_page_id', $customPage->id)
            ->firstOrFail();

        // Validate column_index
        if ($request->column_index >= $container->columns) {
            return response()->json([
                'success' => false,
                'message' => '잘못된 칸 인덱스입니다.',
            ], 400);
        }

        $maxOrder = CustomPageWidget::where('container_id', $request->container_id)
            ->where('column_index', $request->column_index)
            ->max('order') ?? 0;

        // settings 처리
        $settings = [];
        if ($request->has('settings') && !empty($request->settings)) {
            $settingsInput = $request->settings;
            if (is_string($settingsInput)) {
                $decoded = json_decode($settingsInput, true);
                $settings = is_array($decoded) ? $decoded : [];
            } elseif (is_array($settingsInput)) {
                $settings = $settingsInput;
            }
        }
        
        // 블록 타입 이미지 업로드 처리
        if ($request->type === 'block' && $request->hasFile('block_background_image_file')) {
            $image = $request->file('block_background_image_file');
            $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($image, $directory);
            $settings['background_image_url'] = asset('storage/' . $result['file_path']);
        }
        
        // 블록 슬라이드 타입 이미지 업로드 처리
        if ($request->type === 'block_slide' && isset($settings['blocks']) && is_array($settings['blocks'])) {
            foreach ($settings['blocks'] as $index => &$block) {
                if (isset($block['background_type']) && $block['background_type'] === 'image') {
                    // FormData에서 이미지 파일 찾기
                    $fileKey = "block_slide.{$index}.background_image_file";
                    if ($request->hasFile($fileKey)) {
                        $image = $request->file($fileKey);
                        $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                        $result = $this->fileUploadService->upload($image, $directory);
                        $block['background_image_url'] = asset('storage/' . $result['file_path']);
                    }
                }
            }
        }
        
        // 이미지 슬라이드 타입 이미지 업로드 처리
        if ($request->type === 'image_slide' && isset($settings['images']) && is_array($settings['images'])) {
            foreach ($settings['images'] as $index => &$imageItem) {
                $fileKey = "image_slide.{$index}.image_file";
                if ($request->hasFile($fileKey)) {
                    $image = $request->file($fileKey);
                    $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
                    $result = $this->fileUploadService->upload($image, $directory);
                    $imageItem['image_url'] = asset('storage/' . $result['file_path']);
                }
            }
        }
        
        // 이미지 타입 이미지 업로드 처리
        if ($request->type === 'image' && $request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $directory = 'widget-images/' . $site->id . '/' . date('Y/m');
            $result = $this->fileUploadService->upload($image, $directory);
            $settings['image_url'] = asset('storage/' . $result['file_path']);
        }

        $widget = CustomPageWidget::create([
            'custom_page_id' => $customPage->id,
            'container_id' => $request->container_id,
            'column_index' => $request->column_index,
            'type' => $request->type,
            'title' => $request->title,
            'settings' => $settings,
            'order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => '위젯이 추가되었습니다.',
            'widget' => $widget,
        ]);
    }

    /**
     * Update a custom page widget.
     */
    public function updateCustomPageWidget(Site $site, CustomPage $customPage, CustomPageWidget $widget, Request $request)
    {
        // Ensure custom page and widget belong to site
        if ($customPage->site_id !== $site->id || $widget->custom_page_id !== $customPage->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'settings' => 'nullable|string',
            'is_active' => 'nullable|in:0,1,true,false',
        ]);

        // settings 처리
        $settings = $widget->settings ?? [];
        if ($request->has('settings')) {
            $settingsInput = $request->settings;
            if (is_string($settingsInput)) {
                $settings = json_decode($settingsInput, true) ?? $settings;
            } else {
                $settings = $settingsInput;
            }
        }

        $widget->title = $request->input('title', '');
        $widget->settings = $settings;
        if ($request->has('is_active')) {
            $widget->is_active = $request->is_active;
        }
        $widget->save();

        return response()->json([
            'success' => true,
            'message' => '위젯이 업데이트되었습니다.',
            'widget' => $widget,
        ]);
    }

    /**
     * Delete a custom page widget.
     */
    public function deleteCustomPageWidget(Site $site, CustomPage $customPage, CustomPageWidget $widget)
    {
        // Ensure custom page and widget belong to site
        if ($customPage->site_id !== $site->id || $widget->custom_page_id !== $customPage->id) {
            abort(403);
        }

        $widget->delete();

        return response()->json([
            'success' => true,
            'message' => '위젯이 삭제되었습니다.',
        ]);
    }

    /**
     * Reorder custom page widget containers.
     */
    public function reorderCustomPageWidgetContainers(Site $site, CustomPage $customPage, Request $request)
    {
        // Ensure custom page belongs to site
        if ($customPage->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'containers' => 'required|array',
            'containers.*.id' => 'required|exists:custom_page_widget_containers,id',
            'containers.*.order' => 'required|integer',
        ]);

        foreach ($request->containers as $containerData) {
            CustomPageWidgetContainer::where('id', $containerData['id'])
                ->where('custom_page_id', $customPage->id)
                ->update(['order' => $containerData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => '컨테이너 순서가 저장되었습니다.',
        ]);
    }

    /**
     * Reorder custom page widgets within a container column.
     */
    public function reorderCustomPageWidgets(Site $site, CustomPage $customPage, Request $request)
    {
        $request->validate([
            'container_id' => 'required|integer',
            'column_index' => 'required|integer',
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|integer',
            'widgets.*.order' => 'required|integer',
            'widgets.*.container_id' => 'nullable|integer', // 이동된 위젯의 새 컨테이너 ID
            'widgets.*.column_index' => 'nullable|integer', // 이동된 위젯의 새 컬럼 인덱스
        ]);

        $containerId = $request->container_id;
        $columnIndex = $request->column_index;
        $widgets = $request->widgets;

        // 컨테이너가 해당 커스텀 페이지에 속하는지 확인
        $container = CustomPageWidgetContainer::where('id', $containerId)
            ->where('custom_page_id', $customPage->id)
            ->first();

        if (!$container) {
            return response()->json([
                'success' => false,
                'message' => '컨테이너를 찾을 수 없습니다.',
            ], 404);
        }

        // 위젯 순서 업데이트 및 컨테이너 이동 처리
        foreach ($widgets as $widgetData) {
            $widget = CustomPageWidget::where('id', $widgetData['id'])
                ->where('custom_page_id', $customPage->id)
                ->first();

            if (!$widget) {
                continue;
            }

            // 위젯이 다른 컨테이너로 이동한 경우
            if (isset($widgetData['container_id']) && isset($widgetData['column_index'])) {
                $newContainerId = $widgetData['container_id'];
                $newColumnIndex = $widgetData['column_index'];
                
                // 새 컨테이너가 해당 커스텀 페이지에 속하는지 확인
                $newContainer = CustomPageWidgetContainer::where('id', $newContainerId)
                    ->where('custom_page_id', $customPage->id)
                    ->first();
                
                if ($newContainer && $newColumnIndex < $newContainer->columns) {
                    // 위젯을 새 컨테이너로 이동
                    $widget->container_id = $newContainerId;
                    $widget->column_index = $newColumnIndex;
                    $widget->order = $widgetData['order'];
                    $widget->save();
                }
            } else {
                // 같은 컨테이너 내에서 순서만 변경
                if ($widget->container_id == $containerId && $widget->column_index == $columnIndex) {
                    $widget->update(['order' => $widgetData['order']]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => '위젯 순서가 업데이트되었습니다.',
        ]);
    }

    /**
     * Show contact forms index page.
     */
    public function contactForms(Site $site)
    {
        $contactForms = ContactForm::where('site_id', $site->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // AJAX 요청인 경우 JSON 반환
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'contactForms' => $contactForms->map(function ($form) {
                    return [
                        'id' => $form->id,
                        'title' => $form->title ?? '제목 없음',
                    ];
                }),
            ]);
        }

        return view('admin.contact-forms.index', compact('site', 'contactForms'));
    }

    /**
     * Store a new contact form.
     */
    public function storeContactForm(Site $site, Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'has_inquiry_content' => 'boolean',
            'button_text' => 'required|string|max:255',
            'checkboxes' => 'nullable|array',
            'checkboxes.enabled' => 'nullable|boolean',
            'checkboxes.allow_multiple' => 'nullable|boolean',
            'checkboxes.items' => 'nullable|array',
            'checkboxes.items.*.label' => 'nullable|string|max:255',
        ]);

        $checkboxesData = null;
        if ($request->has('checkboxes') && $request->input('checkboxes.enabled')) {
            $checkboxesData = [
                'enabled' => true,
                'allow_multiple' => $request->boolean('checkboxes.allow_multiple', false),
                'items' => $request->input('checkboxes.items', []),
            ];
        }

        $contactForm = ContactForm::create([
            'site_id' => $site->id,
            'title' => $request->input('title'),
            'fields' => $request->input('fields'),
            'has_inquiry_content' => $request->boolean('has_inquiry_content'),
            'button_text' => $request->input('button_text', '신청하기'),
            'checkboxes' => $checkboxesData,
        ]);

        return response()->json([
            'success' => true,
            'message' => '컨텍트폼이 생성되었습니다.',
            'contact_form' => $contactForm,
        ]);
    }

    /**
     * Show contact form detail page with submissions.
     */
    public function showContactForm(Site $site, ContactForm $contactForm)
    {
        if ($contactForm->site_id !== $site->id) {
            abort(404);
        }

        $submissions = ContactFormSubmission::where('contact_form_id', $contactForm->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.contact-forms.show', compact('site', 'contactForm', 'submissions'));
    }

    /**
     * Update a contact form.
     */
    public function updateContactForm(Site $site, ContactForm $contactForm, Request $request)
    {
        if ($contactForm->site_id !== $site->id) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'has_inquiry_content' => 'boolean',
            'button_text' => 'required|string|max:255',
            'checkboxes' => 'nullable|array',
            'checkboxes.enabled' => 'nullable|boolean',
            'checkboxes.allow_multiple' => 'nullable|boolean',
            'checkboxes.items' => 'nullable|array',
            'checkboxes.items.*.label' => 'nullable|string|max:255',
        ]);

        $checkboxesData = null;
        if ($request->has('checkboxes') && $request->input('checkboxes.enabled')) {
            $checkboxesData = [
                'enabled' => true,
                'allow_multiple' => $request->boolean('checkboxes.allow_multiple', false),
                'items' => $request->input('checkboxes.items', []),
            ];
        }

        $contactForm->update([
            'title' => $request->input('title'),
            'fields' => $request->input('fields'),
            'has_inquiry_content' => $request->boolean('has_inquiry_content'),
            'button_text' => $request->input('button_text', '신청하기'),
            'checkboxes' => $checkboxesData,
        ]);

        return response()->json([
            'success' => true,
            'message' => '컨텍트폼이 수정되었습니다.',
            'contact_form' => $contactForm,
        ]);
    }

    /**
     * Delete a contact form.
     */
    public function deleteContactForm(Site $site, ContactForm $contactForm)
    {
        if ($contactForm->site_id !== $site->id) {
            abort(404);
        }

        $contactForm->delete();

        return response()->json([
            'success' => true,
            'message' => '컨텍트폼이 삭제되었습니다.',
        ]);
    }

    /**
     * Display a listing of the maps.
     */
    public function mapsIndex(Site $site)
    {
        // 마스터 콘솔에서 설정한 지도 API 키 가져오기
        $googleApiKey = $this->getMasterSetting('map_api_google_key');
        $naverApiKey = $this->getMasterSetting('map_api_naver_key');
        $kakaoApiKey = $this->getMasterSetting('map_api_kakao_key');
        
        // 사용 가능한 지도 서비스 목록
        $availableMapTypes = [];
        if (!empty($googleApiKey)) {
            $availableMapTypes['google'] = '구글 지도';
        }
        if (!empty($naverApiKey)) {
            $availableMapTypes['naver'] = '네이버 지도';
        }
        if (!empty($kakaoApiKey)) {
            $availableMapTypes['kakao'] = '카카오맵';
        }
        
        // API 키가 설정된 지도 타입만 필터링하여 지도 목록 가져오기
        $maps = Map::where('site_id', $site->id);
        
        if (!empty($availableMapTypes)) {
            $maps->whereIn('map_type', array_keys($availableMapTypes));
        } else {
            // API 키가 하나도 없으면 빈 결과 반환
            $maps->whereRaw('1 = 0');
        }
        
        $maps = $maps->orderBy('created_at', 'desc')->get();
        
        return view('admin.maps.index', compact('site', 'maps', 'availableMapTypes', 'googleApiKey', 'naverApiKey', 'kakaoApiKey'));
    }
    
    /**
     * Get master setting value.
     */
    private function getMasterSetting($key, $default = null)
    {
        $masterSite = \App\Models\Site::getMasterSite();
        if (!$masterSite) {
            return $default;
        }
        
        $setting = \Illuminate\Support\Facades\DB::table('site_settings')
            ->where('site_id', $masterSite->id)
            ->where('key', $key)
            ->first();
        
        return $setting ? $setting->value : $default;
    }

    /**
     * Store a newly created map in storage.
     */
    public function mapsStore(Site $site, Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'map_type' => 'required|in:google,kakao,naver',
                'address' => 'required|string|max:500',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'zoom' => 'nullable|integer|min:1|max:20',
            ]);

            $map = Map::create([
                'site_id' => $site->id,
                'name' => $validated['name'],
                'map_type' => $validated['map_type'],
                'address' => $validated['address'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'zoom' => $validated['zoom'] ?? 15,
            ]);

            return response()->json(['success' => true, 'message' => '지도가 생성되었습니다.', 'map' => $map]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력한 정보를 확인해주세요.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '지도 생성 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified map.
     */
    public function mapsEdit(Site $site, Map $map)
    {
        if ($map->site_id !== $site->id) {
            abort(403);
        }
        
        // 마스터 콘솔에서 설정한 지도 API 키 가져오기
        $googleApiKey = $this->getMasterSetting('map_api_google_key');
        $naverApiKey = $this->getMasterSetting('map_api_naver_key');
        $kakaoApiKey = $this->getMasterSetting('map_api_kakao_key');
        
        // 사용 가능한 지도 서비스 목록
        $availableMapTypes = [];
        if (!empty($googleApiKey)) {
            $availableMapTypes['google'] = '구글 지도';
        }
        if (!empty($naverApiKey)) {
            $availableMapTypes['naver'] = '네이버 지도';
        }
        if (!empty($kakaoApiKey)) {
            $availableMapTypes['kakao'] = '카카오맵';
        }
        
        return view('admin.maps.edit', compact('site', 'map', 'availableMapTypes', 'googleApiKey', 'naverApiKey', 'kakaoApiKey'));
    }

    /**
     * Update the specified map in storage.
     */
    public function mapsUpdate(Site $site, Map $map, Request $request)
    {
        if ($map->site_id !== $site->id) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'map_type' => 'required|in:google,kakao,naver',
                'address' => 'required|string|max:500',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'zoom' => 'nullable|integer|min:1|max:20',
            ]);

            $map->update([
                'name' => $validated['name'],
                'map_type' => $validated['map_type'],
                'address' => $validated['address'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'zoom' => $validated['zoom'] ?? 15,
            ]);

            return response()->json(['success' => true, 'message' => '지도가 업데이트되었습니다.', 'map' => $map]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력한 정보를 확인해주세요.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '지도 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified map from storage.
     */
    public function mapsDestroy(Site $site, Map $map)
    {
        if ($map->site_id !== $site->id) {
            abort(403);
        }
        $map->delete();
        return response()->json(['success' => true, 'message' => '지도가 삭제되었습니다.']);
    }

    /**
     * Geocode address to get latitude and longitude.
     */
    public function mapsGeocode(Site $site, Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:500',
            'map_type' => 'required|in:google,kakao,naver',
        ]);

        $address = $request->address;
        $mapType = $request->map_type;
        
        // 마스터 콘솔에서 설정한 API 키 가져오기
        $googleApiKey = $this->getMasterSetting('map_api_google_key');
        $naverApiKey = $this->getMasterSetting('map_api_naver_key');
        $kakaoApiKey = $this->getMasterSetting('map_api_kakao_key');
        
        $latitude = null;
        $longitude = null;
        
        try {
            if ($mapType === 'google' && !empty($googleApiKey)) {
                // 구글 지오코딩 API
                $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . $googleApiKey . '&language=ko';
                $response = file_get_contents($url);
                $data = json_decode($response, true);
                
                if ($data['status'] === 'OK' && !empty($data['results'])) {
                    $location = $data['results'][0]['geometry']['location'];
                    $latitude = $location['lat'];
                    $longitude = $location['lng'];
                }
            } elseif ($mapType === 'naver' && !empty($naverApiKey)) {
                // 네이버 지오코딩 API
                $clientId = $naverApiKey;
                $clientSecret = $this->getMasterSetting('map_api_naver_secret', '');
                
                $url = 'https://naveropenapi.apigw.ntruss.com/map-geocode/v2/geocode?query=' . urlencode($address);
                $headers = [
                    'X-NCP-APIGW-API-KEY-ID: ' . $clientId,
                    'X-NCP-APIGW-API-KEY: ' . $clientSecret,
                ];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $response = curl_exec($ch);
                curl_close($ch);
                
                $data = json_decode($response, true);
                
                if (!empty($data['addresses']) && count($data['addresses']) > 0) {
                    $addressData = $data['addresses'][0];
                    $longitude = floatval($addressData['x']);
                    $latitude = floatval($addressData['y']);
                }
            } elseif ($mapType === 'kakao' && !empty($kakaoApiKey)) {
                // 카카오 지오코딩 API
                $url = 'https://dapi.kakao.com/v2/local/search/address.json?query=' . urlencode($address);
                $headers = [
                    'Authorization: KakaoAK ' . $kakaoApiKey,
                ];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $response = curl_exec($ch);
                curl_close($ch);
                
                $data = json_decode($response, true);
                
                if (!empty($data['documents']) && count($data['documents']) > 0) {
                    $document = $data['documents'][0];
                    $longitude = floatval($document['x']);
                    $latitude = floatval($document['y']);
                }
            }
            
            if ($latitude && $longitude) {
                return response()->json([
                    'success' => true,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '주소를 찾을 수 없습니다. 주소를 확인해주세요.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '지오코딩 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
        
        // 기존 코드 (사용하지 않음)
        
        try {
            // 실제로는 Google Geocoding API를 호출해야 합니다
            // 예시: https://maps.googleapis.com/maps/api/geocode/json?address={address}&key={API_KEY}
            
            return response()->json([
                'success' => false,
                'message' => '지오코딩 기능은 API 키 설정이 필요합니다. 수동으로 좌표를 입력해주세요.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '주소 변환 중 오류가 발생했습니다.',
            ]);
        }
    }

    /**
     * Add default maps to the site.
     */
    public function mapsAddDefault(Site $site)
    {
        $defaultMaps = [
            [
                'name' => '구글 지도',
                'map_type' => 'google',
                'address' => '서울특별시 강남구 테헤란로 152',
                'latitude' => 37.5013,
                'longitude' => 127.0396,
                'zoom' => 15,
            ],
            [
                'name' => '카카오맵',
                'map_type' => 'kakao',
                'address' => '서울특별시 강남구 테헤란로 152',
                'latitude' => 37.5013,
                'longitude' => 127.0396,
                'zoom' => 15,
            ],
            [
                'name' => '네이버 지도',
                'map_type' => 'naver',
                'address' => '서울특별시 강남구 테헤란로 152',
                'latitude' => 37.5013,
                'longitude' => 127.0396,
                'zoom' => 15,
            ],
        ];

        $addedCount = 0;
        foreach ($defaultMaps as $mapData) {
            // 이미 같은 이름의 지도가 있는지 확인
            $existingMap = Map::where('site_id', $site->id)
                ->where('name', $mapData['name'])
                ->first();
            
            if (!$existingMap) {
                Map::create(array_merge($mapData, [
                    'site_id' => $site->id,
                ]));
                $addedCount++;
            }
        }

        if ($addedCount > 0) {
            return response()->json([
                'success' => true,
                'message' => "기본 지도 {$addedCount}개가 추가되었습니다.",
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => '이미 모든 기본 지도가 존재합니다.',
            ]);
        }
    }

    /**
     * Display a listing of the crawlers.
     */
    public function crawlersIndex(Site $site)
    {
        $crawlers = Crawler::where('site_id', $site->id)
            ->with(['board', 'topic'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $boards = Board::where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('admin.crawlers.index', compact('site', 'crawlers', 'boards'));
    }

    /**
     * Store a newly created crawler in storage.
     */
    public function crawlersStore(Site $site, Request $request)
    {
        // 체크박스 값을 boolean으로 변환
        $request->merge([
            'use_random_user' => $request->has('use_random_user') ? filter_var($request->use_random_user, FILTER_VALIDATE_BOOLEAN) : false,
            'bypass_cloudflare' => $request->has('bypass_cloudflare') ? filter_var($request->bypass_cloudflare, FILTER_VALIDATE_BOOLEAN) : false,
        ]);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'list_title_selector' => 'required|string|max:255',
            'post_title_selector' => 'required|string|max:255',
            'post_content_selector' => 'required|string|max:255',
            'board_id' => 'required|exists:boards,id',
            'topic_id' => 'nullable|exists:topics,id',
            'author_nickname' => 'nullable|string|max:255',
            'use_random_user' => 'boolean',
            'bypass_cloudflare' => 'boolean',
        ]);

        // Check if board belongs to site
        $board = Board::where('id', $request->board_id)
            ->where('site_id', $site->id)
            ->first();
        
        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => '존재하지 않는 게시판입니다.',
            ], 400);
        }

        // Check if topic belongs to board
        if ($request->topic_id) {
            $topic = Topic::where('id', $request->topic_id)
                ->where('board_id', $board->id)
                ->first();
            
            if (!$topic) {
                return response()->json([
                    'success' => false,
                    'message' => '존재하지 않는 주제입니다.',
                ], 400);
            }
        }

        $crawler = Crawler::create([
            'site_id' => $site->id,
            'name' => $request->name,
            'url' => $request->url,
            'list_title_selector' => $request->list_title_selector,
            'post_title_selector' => $request->post_title_selector,
            'post_content_selector' => $request->post_content_selector,
            'board_id' => $request->board_id,
            'topic_id' => $request->topic_id,
            'author_nickname' => $request->use_random_user ? null : $request->author_nickname,
            'use_random_user' => $request->has('use_random_user') ? (bool)$request->use_random_user : false,
            'bypass_cloudflare' => $request->has('bypass_cloudflare') ? (bool)$request->bypass_cloudflare : false,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => '크롤러가 생성되었습니다.',
            'crawler' => $crawler->load(['board', 'topic']),
        ]);
    }

    /**
     * Show the form for editing the specified crawler.
     */
    public function crawlersEdit(Site $site, Crawler $crawler)
    {
        if ($crawler->site_id !== $site->id) {
            abort(403);
        }
        
        $boards = Board::where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $topics = [];
        if ($crawler->board_id) {
            $topics = Topic::where('board_id', $crawler->board_id)
                ->orderBy('name')
                ->get();
        }
        
        return view('admin.crawlers.edit', compact('site', 'crawler', 'boards', 'topics'));
    }

    /**
     * Get crawler data as JSON for modal editing.
     */
    public function crawlersShow(Site $site, Crawler $crawler)
    {
        if ($crawler->site_id !== $site->id) {
            abort(403);
        }
        
        $crawler->load(['board', 'topic']);
        
        return response()->json([
            'success' => true,
            'crawler' => $crawler,
        ]);
    }

    /**
     * Update the specified crawler in storage.
     */
    public function crawlersUpdate(Site $site, Crawler $crawler, Request $request)
    {
        if ($crawler->site_id !== $site->id) {
            abort(403);
        }

        // 체크박스 값을 boolean으로 변환
        $request->merge([
            'use_random_user' => $request->has('use_random_user') ? filter_var($request->use_random_user, FILTER_VALIDATE_BOOLEAN) : false,
            'bypass_cloudflare' => $request->has('bypass_cloudflare') ? filter_var($request->bypass_cloudflare, FILTER_VALIDATE_BOOLEAN) : false,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'list_title_selector' => 'required|string|max:255',
            'post_title_selector' => 'required|string|max:255',
            'post_content_selector' => 'required|string|max:255',
            'board_id' => 'required|exists:boards,id',
            'topic_id' => 'nullable|exists:topics,id',
            'author_nickname' => 'nullable|string|max:255',
            'use_random_user' => 'boolean',
            'bypass_cloudflare' => 'boolean',
        ]);

        // Check if board belongs to site
        $board = Board::where('id', $request->board_id)
            ->where('site_id', $site->id)
            ->first();
        
        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => '존재하지 않는 게시판입니다.',
            ], 400);
        }

        // Check if topic belongs to board
        if ($request->topic_id) {
            $topic = Topic::where('id', $request->topic_id)
                ->where('board_id', $board->id)
                ->first();
            
            if (!$topic) {
                return response()->json([
                    'success' => false,
                    'message' => '존재하지 않는 주제입니다.',
                ], 400);
            }
        }

        $crawler->update([
            'name' => $request->name,
            'url' => $request->url,
            'list_title_selector' => $request->list_title_selector,
            'post_title_selector' => $request->post_title_selector,
            'post_content_selector' => $request->post_content_selector,
            'board_id' => $request->board_id,
            'topic_id' => $request->topic_id,
            'author_nickname' => $request->use_random_user ? null : $request->author_nickname,
            'use_random_user' => $request->has('use_random_user') ? (bool)$request->use_random_user : false,
            'bypass_cloudflare' => $request->has('bypass_cloudflare') ? (bool)$request->bypass_cloudflare : false,
        ]);

        return response()->json([
            'success' => true,
            'message' => '크롤러가 업데이트되었습니다.',
            'crawler' => $crawler->load(['board', 'topic']),
        ]);
    }

    /**
     * Remove the specified crawler from storage.
     */
    public function crawlersDestroy(Site $site, Crawler $crawler)
    {
        if ($crawler->site_id !== $site->id) {
            abort(403);
        }
        
        $crawler->delete();
        
        return response()->json([
            'success' => true,
            'message' => '크롤러가 삭제되었습니다.',
        ]);
    }

    /**
     * Toggle crawler active status.
     */
    public function crawlersToggleActive(Site $site, Crawler $crawler, Request $request)
    {
        if ($crawler->site_id !== $site->id) {
            abort(403);
        }

        $crawler->update([
            'is_active' => !$crawler->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $crawler->is_active ? '크롤러가 활성화되었습니다.' : '크롤러가 비활성화되었습니다.',
            'is_active' => $crawler->is_active,
        ]);
    }

    /**
     * Test crawler with one result.
     */
    public function crawlersTest(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'list_title_selector' => 'required|string|max:255',
            'post_title_selector' => 'required|string|max:255',
            'post_content_selector' => 'required|string|max:255',
        ]);

        try {
            // Use fetchHtml method with bypass_cloudflare option
            $bypassCloudflare = $request->has('bypass_cloudflare') && $request->bypass_cloudflare;
            $html = $this->fetchHtml($request->url, $bypassCloudflare);
            
            if ($html === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL을 가져올 수 없습니다.',
                ], 400);
            }

            // Use DOMDocument to parse HTML
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            $xpath = new \DOMXPath($dom);

            // Convert CSS selector to XPath
            $listSelector = $this->cssToXPath($request->list_title_selector);
            
            // Find first list item
            $listItems = $xpath->query($listSelector);
            
            if ($listItems === false || $listItems->length === 0) {
                return response()->json([
                    'success' => false,
                    'message' => '리스트 항목을 찾을 수 없습니다. 선택자를 확인해주세요.',
                ], 400);
            }

            $firstItem = $listItems->item(0);
            $postUrl = $firstItem->getAttribute('href');
            
            // Make absolute URL if relative
            if (!filter_var($postUrl, FILTER_VALIDATE_URL)) {
                $baseUrl = parse_url($request->url, PHP_URL_SCHEME) . '://' . parse_url($request->url, PHP_URL_HOST);
                $postUrl = $baseUrl . $postUrl;
            }

            // Fetch post page
            $postHtml = $this->fetchHtml($postUrl, $bypassCloudflare);
            
            if ($postHtml === false) {
                return response()->json([
                    'success' => false,
                    'message' => '게시글 페이지를 가져올 수 없습니다.',
                ], 400);
            }

            $postDom = new \DOMDocument();
            @$postDom->loadHTML(mb_convert_encoding($postHtml, 'HTML-ENTITIES', 'UTF-8'));
            $postXpath = new \DOMXPath($postDom);

            // Convert CSS selectors to XPath
            $titleSelector = $this->cssToXPath($request->post_title_selector);
            $contentSelector = $this->cssToXPath($request->post_content_selector);

            // Extract title
            $titleNodes = $postXpath->query($titleSelector);
            $title = ($titleNodes !== false && $titleNodes->length > 0) ? trim($titleNodes->item(0)->textContent) : '';

            // Extract content - preserve original HTML
            $contentNodes = $postXpath->query($contentSelector);
            $content = '';
            if ($contentNodes !== false && $contentNodes->length > 0) {
                // Get original HTML from source string to preserve attributes
                foreach ($contentNodes as $node) {
                    $content .= $this->getOriginalInnerHTML($postHtml, $node, $postDom);
                }
            } else {
                // If no content found, try alternative selectors
                $altSelector = str_replace(' > ', ' ', $contentSelector);
                $contentNodes = $postXpath->query($altSelector);
                if ($contentNodes !== false && $contentNodes->length > 0) {
                    foreach ($contentNodes as $node) {
                        $content .= $this->getOriginalInnerHTML($postHtml, $node, $postDom);
                    }
                } else {
                    // Fallback to saveHTML if original extraction fails
                    $contentNodes = $postXpath->query($contentSelector);
                    if ($contentNodes !== false && $contentNodes->length > 0) {
                        foreach ($contentNodes as $node) {
                            $content .= $postDom->saveHTML($node);
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $postUrl,
                    'title' => $title,
                    'content' => $content,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '크롤링 테스트 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run all active crawlers.
     */
    public function crawlersRunAll(Site $site)
    {
        $crawlers = Crawler::where('site_id', $site->id)
            ->where('is_active', true)
            ->get();

        $results = [];
        
        foreach ($crawlers as $crawler) {
            try {
                $count = $this->runCrawler($crawler);
                $results[] = [
                    'crawler_id' => $crawler->id,
                    'name' => $crawler->name,
                    'success' => true,
                    'count' => $count,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'crawler_id' => $crawler->id,
                    'name' => $crawler->name,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => '크롤링이 완료되었습니다.',
            'results' => $results,
        ]);
    }

    /**
     * Fetch HTML content from URL with optional Cloudflare bypass.
     */
    private function fetchHtml($url, $bypassCloudflare = false)
    {
        if ($bypassCloudflare) {
            try {
                $client = new Client([
                    'timeout' => 30,
                    'verify' => false,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                        'Accept-Language' => 'ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Connection' => 'keep-alive',
                        'Upgrade-Insecure-Requests' => '1',
                        'Sec-Fetch-Dest' => 'document',
                        'Sec-Fetch-Mode' => 'navigate',
                        'Sec-Fetch-Site' => 'none',
                        'Cache-Control' => 'max-age=0',
                    ],
                ]);
                
                $response = $client->get($url);
                return $response->getBody()->getContents();
            } catch (RequestException $e) {
                // Fallback to file_get_contents if Guzzle fails
                $html = @file_get_contents($url);
                if ($html === false) {
                    throw new \Exception('URL을 가져올 수 없습니다: ' . $e->getMessage());
                }
                return $html;
            }
        } else {
            $html = @file_get_contents($url);
            if ($html === false) {
                throw new \Exception('URL을 가져올 수 없습니다.');
            }
            return $html;
        }
    }

    /**
     * Run a single crawler.
     */
    private function runCrawler(Crawler $crawler)
    {
        $count = 0;
        $processedUrls = [];
        $currentUrl = $crawler->url;
        $maxPages = 100; // 최대 페이지 수 제한
        
        // 전체 페이지 크롤링
        for ($page = 1; $page <= $maxPages; $page++) {
            try {
                // 페이지 URL 구성 (첫 페이지는 원본 URL, 이후는 페이지 파라미터 추가)
                if ($page > 1) {
                    $parsedUrl = parse_url($currentUrl);
                    $query = [];
                    if (isset($parsedUrl['query'])) {
                        parse_str($parsedUrl['query'], $query);
                    }
                    $query['page'] = $page;
                    $pageUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . 
                               (isset($parsedUrl['path']) ? $parsedUrl['path'] : '/') . 
                               '?' . http_build_query($query);
                } else {
                    $pageUrl = $currentUrl;
                }

                $html = $this->fetchHtml($pageUrl, $crawler->bypass_cloudflare);
                
                if ($html === false) {
                    break; // 페이지를 가져올 수 없으면 중단
                }

                libxml_use_internal_errors(true);
                $dom = new \DOMDocument();
                @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
                $xpath = new \DOMXPath($dom);

                // Convert CSS selector to XPath
                $listSelector = $this->cssToXPath($crawler->list_title_selector);
                $listItems = $xpath->query($listSelector);
                
                if ($listItems === false || $listItems->length === 0) {
                    // 더 이상 항목이 없으면 중단
                    break;
                }
                
                $pageCount = 0;

                foreach ($listItems as $item) {
                    try {
                        $postUrl = $item->getAttribute('href');
                        
                        // Make absolute URL if relative
                        if (!filter_var($postUrl, FILTER_VALIDATE_URL)) {
                            $baseUrl = parse_url($pageUrl, PHP_URL_SCHEME) . '://' . parse_url($pageUrl, PHP_URL_HOST);
                            $postUrl = $baseUrl . $postUrl;
                        }

                        // 이미 처리한 URL은 건너뛰기
                        if (in_array($postUrl, $processedUrls)) {
                            continue;
                        }
                        $processedUrls[] = $postUrl;

                        // Check if post already exists
                        $existingPost = Post::where('board_id', $crawler->board_id)
                            ->where('external_url', $postUrl)
                            ->first();

                        if ($existingPost) {
                            continue;
                        }

                        // Fetch post page
                        $postHtml = $this->fetchHtml($postUrl, $crawler->bypass_cloudflare);
                        
                        if ($postHtml === false) {
                            continue;
                        }

                $postDom = new \DOMDocument();
                @$postDom->loadHTML(mb_convert_encoding($postHtml, 'HTML-ENTITIES', 'UTF-8'));
                $postXpath = new \DOMXPath($postDom);

                // Convert CSS selectors to XPath
                $titleSelector = $this->cssToXPath($crawler->post_title_selector);
                $contentSelector = $this->cssToXPath($crawler->post_content_selector);

                // Extract title
                $titleNodes = $postXpath->query($titleSelector);
                $title = ($titleNodes !== false && $titleNodes->length > 0) ? trim($titleNodes->item(0)->textContent) : '';

                // Extract content - preserve original HTML
                $contentNodes = $postXpath->query($contentSelector);
                $content = '';
                if ($contentNodes !== false && $contentNodes->length > 0) {
                    // Get original HTML from source string to preserve attributes
                    foreach ($contentNodes as $node) {
                        $content .= $this->getOriginalInnerHTML($postHtml, $node, $postDom);
                    }
                } else {
                    // If no content found, try to get innerHTML
                    $contentNodes = $postXpath->query($contentSelector . '//text()');
                    if ($contentNodes !== false && $contentNodes->length > 0) {
                        foreach ($contentNodes as $node) {
                            $content .= $node->textContent;
                        }
                    }
                }

                        if (empty($title) || empty($content)) {
                            continue;
                        }

                        // Keep original content as-is (no image URL conversion)

                        // Get or create user
                        $user = null;
                        if ($crawler->use_random_user) {
                            $users = User::where('site_id', $crawler->site_id)->get();
                            if ($users->count() > 0) {
                                $user = $users->random();
                            }
                        } else if ($crawler->author_nickname) {
                            $user = User::where('site_id', $crawler->site_id)
                                ->where('nickname', $crawler->author_nickname)
                                ->first();
                    
                            if (!$user) {
                                // Create user with nickname
                                $user = User::create([
                                    'site_id' => $crawler->site_id,
                                    'name' => $crawler->author_nickname,
                                    'nickname' => $crawler->author_nickname,
                                    'email' => strtolower(str_replace(' ', '', $crawler->author_nickname)) . '@crawler.local',
                                    'password' => Hash::make(uniqid()),
                                ]);
                            }
                        }

                        if (!$user) {
                            continue;
                        }

                        // Create post
                        $post = Post::create([
                            'site_id' => $crawler->site_id,
                            'board_id' => $crawler->board_id,
                            'user_id' => $user->id,
                            'title' => $title,
                            'content' => $content,
                            'external_url' => $postUrl,
                        ]);

                        // Attach topic if exists
                        if ($crawler->topic_id) {
                            $post->topics()->attach($crawler->topic_id);
                        }

                        $count++;
                        $pageCount++;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                
                // 페이지에 항목이 없으면 중단
                if ($pageCount === 0) {
                    break;
                }
            } catch (\Exception $e) {
                // 페이지 로드 실패 시 중단
                break;
            }
        }

        $crawler->update([
            'total_count' => $crawler->total_count + $count,
            'last_crawled_at' => now(),
        ]);

        return $count;
    }

    /**
     * Convert relative image URLs to absolute URLs (keep original src).
     */
    private function convertRelativeImageUrls(string $content, string $baseUrl): string
    {
        // Parse base URL for relative path resolution
        $baseUrlParts = parse_url($baseUrl);
        $baseScheme = $baseUrlParts['scheme'] ?? 'https';
        $baseHost = $baseUrlParts['host'] ?? '';
        $basePath = $baseUrlParts['path'] ?? '';
        $baseDir = dirname($basePath);
        if ($baseDir === '.' || $baseDir === '/') {
            $baseDir = '';
        }
        $baseUrlFull = $baseScheme . '://' . $baseHost . ($baseDir ? '/' . ltrim($baseDir, '/') : '');

        // Find all image tags with src or data-src
        preg_match_all('/<img[^>]+(?:src|data-src)=["\']([^"\']+)["\'][^>]*>/i', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $originalImgTag = $match[0];
            $imageUrl = $match[1];
            
            // Skip data URIs and absolute URLs
            if (strpos($imageUrl, 'data:') === 0 || filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                continue;
            }
            
            // Convert relative URL to absolute
            $absoluteUrl = '';
            if (strpos($imageUrl, '/') === 0) {
                // Absolute path from domain root
                $absoluteUrl = $baseScheme . '://' . $baseHost . $imageUrl;
            } else {
                // Relative path
                $absoluteUrl = rtrim($baseUrlFull, '/') . '/' . ltrim($imageUrl, '/');
            }
            
            // Replace relative URL with absolute URL in img tag
            $newImgTag = str_replace($imageUrl, $absoluteUrl, $originalImgTag);
            // Also handle data-src for lazy loading images
            if (preg_match('/data-src=["\']([^"\']+)["\']/', $originalImgTag, $dataSrcMatch)) {
                $dataSrcUrl = $dataSrcMatch[1];
                if (!filter_var($dataSrcUrl, FILTER_VALIDATE_URL) && strpos($dataSrcUrl, 'data:') !== 0) {
                    $absoluteDataSrcUrl = '';
                    if (strpos($dataSrcUrl, '/') === 0) {
                        $absoluteDataSrcUrl = $baseScheme . '://' . $baseHost . $dataSrcUrl;
                    } else {
                        $absoluteDataSrcUrl = rtrim($baseUrlFull, '/') . '/' . ltrim($dataSrcUrl, '/');
                    }
                    $newImgTag = str_replace($dataSrcUrl, $absoluteDataSrcUrl, $newImgTag);
                }
            }
            
            $content = str_replace($originalImgTag, $newImgTag, $content);
        }
        
        return $content;
    }

    /**
     * Get original innerHTML from source HTML string to preserve attributes
     */
    private function getOriginalInnerHTML($originalHtml, $node, $dom)
    {
        // Use saveHTML to get the HTML, then fix lazy loading images
        $html = $dom->saveHTML($node);
        
        // Fix lazy loading images: if src is placeholder and data-src exists, use data-src as src
        $html = preg_replace_callback('/<img([^>]*)>/is', function($matches) {
            $attrs = $matches[1];
            
            // Check if src is placeholder (data:image/gif) and data-src exists
            if (preg_match('/src=["\']data:image\/gif[^"\']+["\']/', $attrs) && 
                preg_match('/data-src=["\']([^"\']+)["\']/', $attrs, $dataSrcMatch)) {
                // Replace placeholder src with actual URL from data-src
                $attrs = preg_replace('/src=["\']data:image\/gif[^"\']+["\']/', 'src="' . $dataSrcMatch[1] . '"', $attrs);
                // Remove data-src attribute
                $attrs = preg_replace('/\s+data-src=["\'][^"\']+["\']/', '', $attrs);
            }
            
            return '<img' . $attrs . '>';
        }, $html);
        
        return $html;
    }

    /**
     * Convert CSS selector to XPath
     */
    private function cssToXPath($selector)
    {
        // If it starts with //, assume it's already XPath
        if (strpos($selector, '//') === 0) {
            return $selector;
        }

        // Simple CSS to XPath conversion
        $selector = trim($selector);
        
        // Handle compound selectors with > (child selector) first
        if (strpos($selector, ' > ') !== false) {
            $parts = explode(' > ', $selector);
            $xpath = '';
            foreach ($parts as $part) {
                $part = trim($part);
                if (strpos($part, '.') === 0) {
                    $class = substr($part, 1);
                    $xpath .= ($xpath ? '/' : '//') . "*[contains(@class, '{$class}')]";
                } elseif (strpos($part, '#') === 0) {
                    $id = substr($part, 1);
                    $xpath .= ($xpath ? '/' : '//') . "*[@id='{$id}']";
                } else {
                    $xpath .= ($xpath ? '/' : '//') . $part;
                }
            }
            return $xpath;
        }
        
        // Handle space-separated selectors (descendant) - like ".entry-title a"
        if (strpos($selector, ' ') !== false) {
            $parts = explode(' ', $selector);
            $xpath = '';
            foreach ($parts as $part) {
                $part = trim($part);
                if (strpos($part, '.') === 0) {
                    $class = substr($part, 1);
                    $xpath .= ($xpath ? '//' : '//') . "*[contains(@class, '{$class}')]";
                } elseif (strpos($part, '#') === 0) {
                    $id = substr($part, 1);
                    $xpath .= ($xpath ? '//' : '//') . "*[@id='{$id}']";
                } else {
                    $xpath .= ($xpath ? '//' : '//') . $part;
                }
            }
            return $xpath;
        }
        
        // Handle class selector (.class)
        if (strpos($selector, '.') === 0) {
            $class = substr($selector, 1);
            return "//*[contains(@class, '{$class}')]";
        }
        
        // Handle ID selector (#id)
        if (strpos($selector, '#') === 0) {
            $id = substr($selector, 1);
            return "//*[@id='{$id}']";
        }
        
        // Default: assume it's a tag name
        return "//{$selector}";
    }

    /**
     * Display toggle menu management page.
     */
    public function toggleMenus(Site $site)
    {
        $toggleMenus = ToggleMenu::where('site_id', $site->id)
            ->with('items')
            ->orderBy('order')
            ->get();

        return view('admin.toggle-menus', compact('site', 'toggleMenus'));
    }

    /**
     * Get toggle menu data for editing.
     */
    public function getToggleMenu(Site $site, ToggleMenu $toggleMenu)
    {
        if ($toggleMenu->site_id !== $site->id) {
            abort(403);
        }

        $toggleMenu->load('items');

        return response()->json([
            'success' => true,
            'toggleMenu' => $toggleMenu,
        ]);
    }

    /**
     * Get toggle menus list as JSON for widget selection.
     */
    public function getToggleMenusList(Site $site)
    {
        $toggleMenus = ToggleMenu::where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'toggleMenus' => $toggleMenus,
        ]);
    }

    /**
     * Store a new toggle menu.
     */
    public function storeToggleMenu(Site $site, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.content' => 'required|string',
        ]);

        $maxOrder = ToggleMenu::where('site_id', $site->id)->max('order') ?? 0;

        $toggleMenu = ToggleMenu::create([
            'site_id' => $site->id,
            'name' => $request->name,
            'order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        // 항목들 저장
        foreach ($request->items as $index => $item) {
            \App\Models\ToggleMenuItem::create([
                'toggle_menu_id' => $toggleMenu->id,
                'title' => $item['title'],
                'content' => $item['content'],
                'order' => $index,
            ]);
        }

        $toggleMenu->load('items');

        return response()->json([
            'success' => true,
            'message' => '토글 메뉴가 생성되었습니다.',
            'toggleMenu' => $toggleMenu,
        ]);
    }

    /**
     * Update a toggle menu.
     */
    public function updateToggleMenu(Site $site, ToggleMenu $toggleMenu, Request $request)
    {
        if ($toggleMenu->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.content' => 'required|string',
        ]);

        $toggleMenu->update([
            'name' => $request->name,
        ]);

        // 기존 항목들 삭제
        $toggleMenu->items()->delete();

        // 새 항목들 저장
        foreach ($request->items as $index => $item) {
            \App\Models\ToggleMenuItem::create([
                'toggle_menu_id' => $toggleMenu->id,
                'title' => $item['title'],
                'content' => $item['content'],
                'order' => $index,
            ]);
        }

        $toggleMenu->load('items');

        return response()->json([
            'success' => true,
            'message' => '토글 메뉴가 수정되었습니다.',
            'toggleMenu' => $toggleMenu,
        ]);
    }

    /**
     * Delete a toggle menu item.
     */
    public function deleteToggleMenu(Site $site, ToggleMenu $toggleMenu)
    {
        if ($toggleMenu->site_id !== $site->id) {
            abort(403);
        }

        $toggleMenu->delete();

        return response()->json([
            'success' => true,
            'message' => '토글 메뉴가 삭제되었습니다.',
        ]);
    }

    /**
     * Update toggle menu order.
     */
    public function updateToggleMenuOrder(Site $site, Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'required|integer',
        ]);

        foreach ($request->orders as $id => $order) {
            ToggleMenu::where('site_id', $site->id)
                ->where('id', $id)
                ->update(['order' => $order]);
        }

        return response()->json([
            'success' => true,
            'message' => '순서가 변경되었습니다.',
        ]);
    }

    /**
     * Toggle active status of a toggle menu.
     */
    public function toggleToggleMenuActive(Site $site, ToggleMenu $toggleMenu)
    {
        if ($toggleMenu->site_id !== $site->id) {
            abort(403);
        }

        $toggleMenu->update([
            'is_active' => !$toggleMenu->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $toggleMenu->is_active ? '활성화되었습니다.' : '비활성화되었습니다.',
            'is_active' => $toggleMenu->is_active,
        ]);
    }

    /**
     * Display server management page.
     */
    public function serverManagement(Site $site)
    {
        // Get subscription and plan
        $subscription = $site->subscription;
        $plan = $subscription ? $subscription->plan : null;
        
        // Get server subscription (server capacity plan)
        $serverSubscription = \App\Models\Subscription::where('site_id', $site->id)
            ->whereHas('plan', function($query) {
                $query->where('type', 'server');
            })
            ->where('status', 'active')
            ->first();
        
        $serverPlan = $serverSubscription ? $serverSubscription->plan : null;
        
        // Calculate actual storage usage
        $storageUsedMB = $this->calculateStorageUsage($site);
        
        // Update site's storage_used_mb if different
        if ($site->storage_used_mb != $storageUsedMB) {
            $site->update(['storage_used_mb' => $storageUsedMB]);
        }
        
        // Get traffic usage (for now, use stored value)
        $trafficUsedMB = $site->traffic_used_mb ?? 0;
        
        // Get storage and traffic limits
        $storageLimitMB = $site->getTotalStorageLimit();
        $trafficLimitMB = $site->getTotalTrafficLimit();
        
        // Get master site for links
        $masterSite = Site::getMasterSite();
        
        return view('admin.server-management', compact(
            'site',
            'subscription',
            'plan',
            'serverSubscription',
            'serverPlan',
            'storageUsedMB',
            'trafficUsedMB',
            'storageLimitMB',
            'trafficLimitMB',
            'masterSite'
        ));
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
            \Log::error('Error calculating storage usage for site ' . $site->id . ': ' . $e->getMessage());
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
            \Log::error('Error calculating directory size for ' . $directory . ': ' . $e->getMessage());
        }
        
        return $size;
    }
}
