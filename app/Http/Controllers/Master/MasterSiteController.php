<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Plan;
use App\Models\MasterUser;
use App\Services\SiteProvisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MasterSiteController extends Controller
{
    protected $provisionService;

    public function __construct(SiteProvisionService $provisionService)
    {
        $this->middleware(['web', 'auth:master'])->except(['sso']);
        $this->middleware('web')->only(['sso']);
        $this->provisionService = $provisionService;
    }

    /**
     * Display a listing of sites.
     */
    public function index(Request $request)
    {
        $query = Site::query();

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        $sites = $query->orderBy('created_at', 'desc')->paginate(20);

        // 각 사이트의 플랜 정보 로드 및 백업 파일 목록 추가
        $sites->getCollection()->transform(function ($site) {
            $plan = Plan::where('slug', $site->plan)->first();
            $site->planModel = $plan;
            // 해당 사이트의 백업 파일 목록 가져오기
            $site->backupFiles = $this->getSiteBackupFiles($site);
            return $site;
        });

        return view('master.sites.index', compact('sites'));
    }

    /**
     * Show the form for creating a new site.
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->orderBy('price')->get();
        
        // 게시판 타입 목록
        $boardTypes = \App\Models\Board::getTypes();
        
        // 위젯 타입 목록
        $sidebarWidgetTypes = \App\Models\SidebarWidget::getAvailableTypes();
        unset($sidebarWidgetTypes['statistics'], $sidebarWidgetTypes['notice'], $sidebarWidgetTypes['user_activity']);
        $mainWidgetTypes = \App\Models\MainWidget::getAvailableTypes();
        unset($mainWidgetTypes['statistics'], $mainWidgetTypes['notice'], $mainWidgetTypes['user_activity']);
        $customPageWidgetTypes = \App\Models\CustomPageWidget::getAvailableTypes();
        unset($customPageWidgetTypes['statistics'], $customPageWidgetTypes['notice'], $customPageWidgetTypes['user_activity']);
        
        // 플랜 데이터를 JSON으로 변환하여 JavaScript에서 사용할 수 있도록
        $plansData = $plans->map(function($plan) {
            return [
                'slug' => $plan->slug,
                'features' => $plan->features ?? [],
                'limits' => $plan->limits ?? [],
            ];
        })->keyBy('slug');
        
        return view('master.sites.create', compact('plans', 'boardTypes', 'sidebarWidgetTypes', 'mainWidgetTypes', 'customPageWidgetTypes', 'plansData'));
    }

    /**
     * Store a newly created site.
     */
    public function store(Request $request)
    {
        $loginType = $request->input('login_type', 'email');
        
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sites,slug',
            'domain' => 'nullable|string|max:255',
            'plan' => 'required|exists:plans,slug',
            'login_type' => 'required|in:email,username',
            'admin_name' => 'required|string|max:255',
            'admin_password' => 'required|string|min:8|confirmed',
        ];
        
        // 로그인 타입에 따라 필수 필드 다르게 설정
        if ($loginType === 'email') {
            $rules['admin_email'] = 'required|email';
        } else {
            $rules['admin_username'] = 'required|string|min:4|max:20|regex:/^[a-zA-Z0-9_]+$/';
        }
        
        // 테스트 어드민 유효성 검사
        if ($request->input('test_admin_enabled')) {
            $rules['test_admin_username'] = 'required|string|min:2|max:20';
            $rules['test_admin_password'] = 'required|string|min:1';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // 로그인 타입 설정
        $data['login_type'] = $loginType;
        
        // 테스트 어드민 설정
        if ($request->input('test_admin_enabled')) {
            $data['test_admin'] = [
                'enabled' => true,
                'username' => $request->input('test_admin_username'),
                'password' => $request->input('test_admin_password'),
            ];
        }

        $site = $this->provisionService->provision($data);

        // 사이트 생성 시 커스텀 features 저장
        $customFeatures = [];
        
        if ($request->has('custom_main_features')) {
            $customFeatures['main_features'] = $request->input('custom_main_features', []);
        }
        if ($request->has('custom_board_types')) {
            $customFeatures['board_types'] = $request->input('custom_board_types', []);
        }
        if ($request->has('custom_registration_features')) {
            $customFeatures['registration_features'] = $request->input('custom_registration_features', []);
        }
        if ($request->has('custom_sidebar_widget_types')) {
            $customFeatures['sidebar_widget_types'] = $request->input('custom_sidebar_widget_types', []);
        }
        if ($request->has('custom_main_widget_types')) {
            $customFeatures['main_widget_types'] = $request->input('custom_main_widget_types', []);
        }
        if ($request->has('custom_custom_page_widget_types')) {
            $customFeatures['custom_page_widget_types'] = $request->input('custom_custom_page_widget_types', []);
        }
        if ($request->has('custom_limits')) {
            $limits = [];
            foreach ($request->input('custom_limits', []) as $key => $value) {
                if ($value === '-' || $value === '' || $value === null) {
                    $limits[$key] = null;
                } else {
                    $limits[$key] = is_numeric($value) ? (int)$value : $value;
                }
            }
            $customFeatures['limits'] = $limits;
        }
        
        if (!empty($customFeatures)) {
            $site->setSetting('custom_features', $customFeatures);
        }

        return redirect()->route('master.sites.show', $site->id)
            ->with('success', '사이트가 성공적으로 생성되었습니다.');
    }

    /**
     * Display the specified site.
     */
    public function show(Site $site)
    {
        $site->load(['users', 'boards', 'posts']);
        
        $stats = [
            'users' => $site->users()->count(),
            'boards' => $site->boards()->count(),
            'posts' => $site->posts()->count(),
            'comments' => $site->comments()->count(),
        ];

        return view('master.sites.show', compact('site', 'stats'));
    }

    /**
     * Show the form for editing the specified site.
     */
    public function edit(Site $site)
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->orderBy('price')->get();
        
        // 현재 사이트의 플랜 정보
        $currentPlan = $site->planModel();
        
        // 사이트별 커스텀 features 가져오기
        // SiteSetting의 getValueAttribute가 이미 JSON을 디코딩하므로 배열로 반환됨
        $customFeatures = $site->getSetting('custom_features', null);
        // getSetting은 이미 디코딩된 배열을 반환하므로 그대로 사용
        // 하지만 안전을 위해 다시 확인
        if ($customFeatures !== null) {
            if (is_array($customFeatures)) {
                $customFeaturesArray = $customFeatures;
            } elseif (is_string($customFeatures)) {
                // 문자열인 경우 JSON 디코딩 시도
                $decoded = json_decode($customFeatures, true);
                $customFeaturesArray = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null;
            } else {
                $customFeaturesArray = null;
            }
        } else {
            $customFeaturesArray = null;
        }
        
        // 게시판 타입 목록
        $boardTypes = \App\Models\Board::getTypes();
        
        // 위젯 타입 목록
        $sidebarWidgetTypes = \App\Models\SidebarWidget::getAvailableTypes();
        unset($sidebarWidgetTypes['statistics'], $sidebarWidgetTypes['notice'], $sidebarWidgetTypes['user_activity']);
        $mainWidgetTypes = \App\Models\MainWidget::getAvailableTypes();
        unset($mainWidgetTypes['statistics'], $mainWidgetTypes['notice'], $mainWidgetTypes['user_activity']);
        $customPageWidgetTypes = \App\Models\CustomPageWidget::getAvailableTypes();
        unset($customPageWidgetTypes['statistics'], $customPageWidgetTypes['notice'], $customPageWidgetTypes['user_activity']);
        
        // 플랜 데이터를 JSON으로 변환하여 JavaScript에서 사용할 수 있도록
        $plansData = $plans->map(function($plan) {
            return [
                'slug' => $plan->slug,
                'features' => $plan->features ?? [],
                'limits' => $plan->limits ?? [],
            ];
        })->keyBy('slug');
        
        return view('master.sites.edit', compact('site', 'plans', 'currentPlan', 'customFeaturesArray', 'boardTypes', 'sidebarWidgetTypes', 'mainWidgetTypes', 'customPageWidgetTypes', 'plansData'));
    }

    /**
     * Update the specified site.
     */
    public function update(Request $request, Site $site)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sites,slug,' . $site->id,
            'domain' => 'nullable|string|max:255',
            'plan' => 'required|exists:plans,slug',
            'status' => 'required|in:active,suspended,deleted',
        ]);

        if (        $validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 무료 플랜인 경우 도메인 변경 불가
        $plan = Plan::where('slug', $request->input('plan'))->first();
        if ($plan && $plan->billing_type === 'free' && $request->has('domain')) {
            // 무료 플랜에서는 도메인 변경 불가
            if ($site->domain !== $request->input('domain')) {
                return back()
                    ->withErrors(['domain' => '무료 플랜에서는 도메인을 변경할 수 없습니다.'])
                    ->withInput();
            }
        }

        $site->update($request->only(['name', 'slug', 'domain', 'plan', 'status']));

        // 사이트별 커스텀 features 저장
        // JavaScript로 모든 체크박스 값을 전송하므로, 항상 request에서 값을 가져옴
        // 빈 값('')이 포함된 배열은 필터링
        $customFeatures = [];
        
        // main_features 처리
        $mainFeatures = $request->input('custom_main_features', []);
        if (!is_array($mainFeatures)) {
            $mainFeatures = [];
        }
        $mainFeatures = array_filter($mainFeatures, function($v) { return $v !== '' && $v !== null; });
        $customFeatures['main_features'] = array_values($mainFeatures);
        
        // board_types 처리
        $boardTypes = $request->input('custom_board_types', []);
        if (!is_array($boardTypes)) {
            $boardTypes = [];
        }
        $boardTypes = array_filter($boardTypes, function($v) { return $v !== '' && $v !== null; });
        $customFeatures['board_types'] = array_values($boardTypes);
        
        // registration_features 처리
        $regFeatures = $request->input('custom_registration_features', []);
        if (!is_array($regFeatures)) {
            $regFeatures = [];
        }
        $regFeatures = array_filter($regFeatures, function($v) { return $v !== '' && $v !== null; });
        $customFeatures['registration_features'] = array_values($regFeatures);
        
        // sidebar_widget_types 처리
        $sidebarWidgetTypes = $request->input('custom_sidebar_widget_types', []);
        if (!is_array($sidebarWidgetTypes)) {
            $sidebarWidgetTypes = [];
        }
        $sidebarWidgetTypes = array_filter($sidebarWidgetTypes, function($v) { return $v !== '' && $v !== null; });
        $customFeatures['sidebar_widget_types'] = array_values($sidebarWidgetTypes);
        
        // main_widget_types 처리
        $mainWidgetTypes = $request->input('custom_main_widget_types', []);
        if (!is_array($mainWidgetTypes)) {
            $mainWidgetTypes = [];
        }
        $mainWidgetTypes = array_filter($mainWidgetTypes, function($v) { return $v !== '' && $v !== null; });
        $customFeatures['main_widget_types'] = array_values($mainWidgetTypes);
        
        // custom_page_widget_types 처리
        $customPageWidgetTypes = $request->input('custom_custom_page_widget_types', []);
        if (!is_array($customPageWidgetTypes)) {
            $customPageWidgetTypes = [];
        }
        $customPageWidgetTypes = array_filter($customPageWidgetTypes, function($v) { return $v !== '' && $v !== null; });
        $customFeatures['custom_page_widget_types'] = array_values($customPageWidgetTypes);
        
        // limits 처리
        if ($request->has('custom_limits')) {
            $limits = [];
            foreach ($request->input('custom_limits', []) as $key => $value) {
                if ($value === '-' || $value === '' || $value === null) {
                    $limits[$key] = null;
                } else {
                    $limits[$key] = is_numeric($value) ? (int)$value : $value;
                }
            }
            $customFeatures['limits'] = $limits;
        }
        
        // 디버깅 로그
        \Log::info('Saving custom features', [
            'site_id' => $site->id,
            'request_all' => $request->all(),
            'custom_features' => $customFeatures
        ]);
        
        // 항상 저장 (빈 배열이어도)
        $result = $site->setSetting('custom_features', $customFeatures);
        
        // 저장 후 확인
        $saved = $site->getSetting('custom_features');
        \Log::info('Saved custom features', [
            'site_id' => $site->id,
            'saved' => $saved,
            'result' => $result
        ]);

        return redirect()->route('master.sites.show', $site->id)
            ->with('success', '사이트 정보가 업데이트되었습니다.');
    }

    /**
     * Remove the specified site.
     */
    public function destroy(Site $site)
    {
        $this->provisionService->deprovision($site);

        return redirect()->route('master.sites.index')
            ->with('success', '사이트가 삭제되었습니다.');
    }

    /**
     * Suspend a site.
     */
    public function suspend(Site $site)
    {
        $site->update(['status' => 'suspended']);

        return back()->with('success', '사이트가 정지되었습니다.');
    }

    /**
     * Activate a site.
     */
    public function activate(Site $site)
    {
        $site->update(['status' => 'active']);

        return back()->with('success', '사이트가 활성화되었습니다.');
    }

    /**
     * SSO to site admin.
     * 마스터 관리자는 모든 사이트에 관리자 권한으로 로그인할 수 있습니다.
     * 관리자 계정이 없어도 마스터 계정 정보로 자동 생성하여 로그인합니다.
     */
    public function sso(Site $site, Request $request)
    {
        $masterUser = null;
        
        // 토큰 기반 SSO (새 창에서 열릴 때 사용)
        if ($request->has('token')) {
            $token = $request->input('token');
            $cacheKey = 'sso_token_' . $token;
            $ssoData = \Illuminate\Support\Facades\Cache::get($cacheKey);
            
            if ($ssoData && isset($ssoData['master_user_id']) && isset($ssoData['site_id'])) {
                // 토큰이 유효한 경우
                if ($ssoData['site_id'] == $site->id) {
                    $masterUser = MasterUser::find($ssoData['master_user_id']);
                    if ($masterUser) {
                        // 토큰 삭제
                        \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    }
                }
            }
        }
        
        // 토큰이 없거나 유효하지 않은 경우, 마스터 세션에서 사용자 정보 가져오기 시도
        if (!$masterUser) {
            // 같은 창에서 열릴 때는 마스터 세션에서 가져오기
            $masterUser = auth('master')->user();
        }
        
        // 마스터 사용자가 없으면 에러
        if (!$masterUser) {
            return redirect()->route('login', ['site' => $site->slug])
                ->with('error', '마스터 관리자로 로그인되어 있지 않습니다.');
        }

        // 마스터 관리자의 이메일로 해당 사이트의 관리자 계정 찾기 또는 생성
        $admin = $site->users()
            ->where('email', $masterUser->email)
            ->first();
        
        // 관리자 계정이 없으면 마스터 관리자 정보로 관리자 계정 생성
        if (!$admin) {
            $admin = \App\Models\User::create([
                'site_id' => $site->id,
                'name' => $masterUser->name . ' (Master)',
                'email' => $masterUser->email,
                'password' => \Illuminate\Support\Facades\Hash::make(uniqid()), // 임시 비밀번호 (사용하지 않음)
                'role' => 'admin',
            ]);
        } else {
            // 이미 존재하는 경우 역할을 관리자로 업데이트
            if ($admin->role !== 'admin') {
                $admin->update(['role' => 'admin']);
            }
        }

        // 기존 세션에 마스터 사용자 정보 저장 (로그인 전에 저장해야 함)
        session(['is_master_user' => true, 'master_user_id' => $masterUser->id]);
        
        // Login as the admin user
        auth()->login($admin);
        
        // 세션 재생성
        request()->session()->regenerate();
        
        // 세션에 마스터 사용자 정보 다시 저장 (세션 재생성 후)
        session(['is_master_user' => true, 'master_user_id' => $masterUser->id]);
        
        return redirect()->route('admin.dashboard', ['site' => $site->slug]);
    }
    
    /**
     * Generate SSO token for new window login.
     */
    public function generateSsoToken(Site $site)
    {
        $masterUser = auth('master')->user();
        
        if (!$masterUser) {
            return response()->json([
                'success' => false,
                'message' => '마스터 관리자로 로그인되어 있지 않습니다.',
            ], 403);
        }
        
        // 토큰 생성 (10분 유효)
        $token = \Illuminate\Support\Str::random(32);
        \Illuminate\Support\Facades\Cache::put('sso_token_' . $token, [
            'master_user_id' => $masterUser->id,
            'site_id' => $site->id,
        ], now()->addMinutes(10));
        
        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => route('master.sites.sso', ['site' => $site->id, 'token' => $token]),
        ]);
    }

    /**
     * Get backup files for a specific site.
     */
    public function getBackupFiles(Site $site)
    {
        $backupFiles = $this->getSiteBackupFiles($site);
        
        return response()->json([
            'success' => true,
            'files' => $backupFiles,
        ]);
    }

    /**
     * Restore a site from backup.
     */
    public function restoreFromBackup(Site $site, Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string',
        ]);

        $filename = $request->input('backup_file');
        $backupPath = storage_path('app/backups/' . $filename);

        // 백업 파일이 존재하는지 확인
        if (!file_exists($backupPath)) {
            return back()->with('error', '백업 파일을 찾을 수 없습니다.');
        }

        // 파일명에서 사이트 슬러그 확인
        if (!str_contains($filename, "backup_site_{$site->slug}")) {
            return back()->with('error', '이 백업 파일은 해당 사이트의 백업이 아닙니다.');
        }

        try {
            DB::beginTransaction();

            // SQL 파일인 경우 데이터베이스 복원
            if (str_ends_with($filename, '.sql')) {
                $this->restoreDatabase($backupPath, $site);
            }

            // 파일 백업이 있는 경우 복원
            $filesBackupPath = str_replace('.sql', '_files.tar.gz', $backupPath);
            if (file_exists($filesBackupPath)) {
                $this->restoreFiles($filesBackupPath, $site);
            }

            DB::commit();

            return back()->with('success', '백업이 성공적으로 복원되었습니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '백업 복원 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * Get backup files for a specific site.
     */
    protected function getSiteBackupFiles(Site $site)
    {
        $files = Storage::files('backups');
        $backupFiles = [];

        foreach ($files as $file) {
            $filename = basename($file);
            // 사이트별 백업 파일만 필터링 (backup_site_{slug}_)
            if (str_starts_with($filename, "backup_site_{$site->slug}_") && str_ends_with($filename, '.sql')) {
                $backupFiles[] = [
                    'name' => $filename,
                    'size' => Storage::size($file),
                    'created_at' => Storage::lastModified($file),
                    'date' => date('Y-m-d H:i:s', Storage::lastModified($file)),
                ];
            }
        }

        // 생성일 기준 내림차순 정렬
        usort($backupFiles, function($a, $b) {
            return $b['created_at'] - $a['created_at'];
        });

        return $backupFiles;
    }

    /**
     * Restore database from SQL file.
     */
    protected function restoreDatabase($sqlPath, Site $site)
    {
        $sql = file_get_contents($sqlPath);
        
        if (empty($sql)) {
            throw new \Exception('백업 파일이 비어있습니다.');
        }

        // 사이트별 백업 파일은 DELETE와 INSERT 문으로 구성됨
        // 한 줄씩 읽어서 실행하는 것이 더 안전함
        
        $lines = explode("\n", $sql);
        $currentStatement = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 주석이나 빈 줄은 건너뛰기
            if (empty($line) || str_starts_with($line, '--')) {
                continue;
            }
            
            $currentStatement .= $line . ' ';
            
            // 세미콜론으로 문장 종료 확인
            if (str_ends_with($line, ';')) {
                $statement = trim($currentStatement);
                if (!empty($statement)) {
                    try {
                        DB::statement($statement);
                    } catch (\Exception $e) {
                        // 일부 오류는 무시 (예: 외래 키 제약 조건 등)
                        // 하지만 중요한 오류는 로그에 기록
                        if (str_contains($e->getMessage(), 'Duplicate entry') || 
                            str_contains($e->getMessage(), 'foreign key constraint')) {
                            \Log::warning('SQL 실행 중 오류 (무시됨): ' . $e->getMessage());
                        } else {
                            throw $e;
                        }
                    }
                }
                $currentStatement = '';
            }
        }
    }

    /**
     * Restore files from tar.gz archive.
     */
    protected function restoreFiles($tarPath, Site $site)
    {
        $siteStoragePath = storage_path("app/public/sites/{$site->slug}");
        
        // 사이트 디렉토리가 없으면 생성
        if (!file_exists($siteStoragePath)) {
            mkdir($siteStoragePath, 0755, true);
        }

        // tar.gz 파일 압축 해제
        $command = sprintf(
            'tar -xzf %s -C %s 2>&1',
            escapeshellarg($tarPath),
            escapeshellarg($siteStoragePath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('파일 복원 중 오류가 발생했습니다: ' . implode("\n", $output));
        }
    }
}

