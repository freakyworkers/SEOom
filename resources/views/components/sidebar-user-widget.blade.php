@php
    // 포인트 컬러 가져오기
    $pointColor = $themeDarkMode === 'dark' ? $site->getSetting('color_dark_point_main', '#ffffff') : $site->getSetting('color_light_point_main', '#0d6efd');
    
    // 헤더 테두리 설정 가져오기
    $headerBorder = $site->getSetting('header_border', '0') == '1';
    $headerBorderWidth = $site->getSetting('header_border_width', '1');
    $headerBorderColor = $site->getSetting('header_border_color', '#dee2e6');
    
    // 로그인 위젯 상단 테두리 스타일
    $widgetTopBorderStyle = '';
    if ($headerBorder) {
        $widgetTopBorderStyle = "border-top: {$headerBorderWidth}px solid {$headerBorderColor};";
    } else {
        // 헤더 테두리가 없으면 포인트 컬러 테두리도 제거
        $widgetTopBorderStyle = "border-top: none;";
    }
    
    // 사용자 정보
    $user = auth()->user();
    
    // 회원등급 정보 가져오기
    $userRank = null;
    $nextRank = null;
    $currentExp = 0;
    $requiredExp = 100;
    $expPercentage = 0;
    
    if ($user && \Illuminate\Support\Facades\Schema::hasTable('user_ranks')) {
        $userRank = $user->getUserRank($site->id);
        
        if ($userRank) {
            $criteriaType = $site->getSetting('rank_criteria_type', 'current_points');
            
            // 사용자의 현재 기준 값
            if ($criteriaType === 'current_points' || $criteriaType === 'max_points') {
                $currentValue = $user->points ?? 0;
            } elseif ($criteriaType === 'post_count') {
                $currentValue = $user->posts()->count();
            } else {
                $currentValue = 0;
            }
            
            // 다음 등급 찾기
            $nextRanks = \App\Models\UserRank::where('site_id', $site->id)
                ->where('criteria_type', $criteriaType)
                ->where('criteria_value', '>', $userRank->criteria_value)
                ->orderBy('criteria_value', 'asc')
                ->get();
            
            if ($nextRanks->isNotEmpty()) {
                $nextRank = $nextRanks->first();
                $currentExp = $currentValue - $userRank->criteria_value;
                $requiredExp = $nextRank->criteria_value - $userRank->criteria_value;
            } else {
                // 최고 등급인 경우
                $currentExp = $currentValue - $userRank->criteria_value;
                $requiredExp = 1; // 0으로 나누기 방지
            }
            
            $expPercentage = $requiredExp > 0 ? min(100, ($currentExp / $requiredExp) * 100) : 100;
        } else {
            // 등급이 없는 경우 (가장 낮은 등급 미만)
            $criteriaType = $site->getSetting('rank_criteria_type', 'current_points');
            $firstRank = \App\Models\UserRank::where('site_id', $site->id)
                ->where('criteria_type', $criteriaType)
                ->orderBy('criteria_value', 'asc')
                ->first();
            
            if ($firstRank) {
                if ($criteriaType === 'current_points' || $criteriaType === 'max_points') {
                    $currentValue = $user->points ?? 0;
                } elseif ($criteriaType === 'post_count') {
                    $currentValue = $user->posts()->count();
                } else {
                    $currentValue = 0;
                }
                
                $currentExp = $currentValue;
                $requiredExp = $firstRank->criteria_value;
                $expPercentage = $requiredExp > 0 ? min(100, ($currentExp / $requiredExp) * 100) : 0;
            }
        }
    }
    
    // 알림 개수 가져오기
    $unreadNotificationCount = 0;
    if ($user && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
        $unreadNotificationCount = \App\Models\Notification::getUnreadCount($user->id, $site->id);
    }
    
    // 쪽지 개수 가져오기
    $unreadMessageCount = 0;
    if ($user && \Illuminate\Support\Facades\Schema::hasTable('messages')) {
        $unreadMessageCount = \App\Models\Message::getUnreadCount($user->id, $site->id);
    }
@endphp

<div class="card shadow-sm mb-3 sidebar-user-widget" style="{{ $widgetTopBorderStyle }}">
    @auth
        {{-- 로그인 후 위젯 --}}
        <div class="card-body p-3">
            {{-- 사용자 이름 및 로그아웃 버튼 --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold">{{ $user->nickname ?? $user->name }}님</h6>
                <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                        로그아웃
                    </button>
                </form>
            </div>
            
            @php
                // 마이페이지 설정 가져오기
                $showExperience = $site->getSetting('sidebar_widget_show_experience', true);
                $showRank = $site->getSetting('sidebar_widget_show_rank', true);
                $showPoints = $site->getSetting('sidebar_widget_show_points', true);
                $showNotifications = $site->getSetting('sidebar_widget_show_notifications', true);
                $showMessages = $site->getSetting('sidebar_widget_show_messages', true);
                $showMyPosts = $site->getSetting('sidebar_widget_show_my_posts', true);
                $showProfile = $site->getSetting('sidebar_widget_show_profile', true);
                $showEditProfile = $site->getSetting('sidebar_widget_show_edit_profile', true);
                $showSavedPosts = $site->getSetting('sidebar_widget_show_saved_posts', true);
                $showMyComments = $site->getSetting('sidebar_widget_show_my_comments', true);
            @endphp
            
            {{-- 경험치 바 --}}
            @if($showExperience)
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted">경험치</small>
                    <small class="text-muted">{{ number_format($expPercentage, 1) }}%</small>
                </div>
                <div class="progress" style="height: 8px; border-radius: 4px; background-color: #e9ecef;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $expPercentage }}%; background-color: {{ $pointColor }}; border-radius: 4px;" aria-valuenow="{{ $expPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            @endif
            
            {{-- 회원등급 및 포인트 --}}
            @if($showRank || $showPoints)
            <div class="mb-3">
                @if($showRank)
                <div class="d-flex align-items-center mb-2">
                    @php
                        $adminIcon = $site->getSetting('admin_icon_path', '');
                        $managerIcon = $site->getSetting('manager_icon_path', '');
                        $displayType = $site->getSetting('rank_display_type', 'icon');
                    @endphp
                    <small class="text-muted">회원등급 : </small>
                    @if($user->isAdmin() && $adminIcon)
                        <img src="{{ asset('storage/' . $adminIcon) }}" alt="Admin" style="width: 20px; height: 20px; object-fit: contain; vertical-align: middle; margin-right: 4px;">
                        <small class="text-muted"><strong>관리자</strong></small>
                    @elseif($user->isManager() && $managerIcon)
                        <img src="{{ asset('storage/' . $managerIcon) }}" alt="Manager" style="width: 20px; height: 20px; object-fit: contain; vertical-align: middle; margin-right: 4px;">
                        <small class="text-muted"><strong>매니저</strong></small>
                    @elseif($userRank)
                        @if($displayType === 'icon' && $userRank->icon_path)
                            <img src="{{ asset('storage/' . $userRank->icon_path) }}" alt="{{ $userRank->name }}" style="width: 20px; height: 20px; object-fit: contain; vertical-align: middle; margin-right: 4px;">
                            <small class="text-muted"><strong>{{ $userRank->name }}</strong></small>
                        @elseif($displayType === 'color' && $userRank->color)
                            <span style="color: {{ $userRank->color }}; font-weight: bold; margin-right: 4px;">{{ $userRank->name }}</span>
                        @else
                            <small class="text-muted"><strong>{{ $userRank->name }}</strong></small>
                        @endif
                    @else
                        <small class="text-muted"><strong>일반회원</strong></small>
                    @endif
                </div>
                @endif
                @if($showPoints)
                <div class="d-flex align-items-center">
                    <small class="text-muted">포인트: <strong style="color: {{ $pointColor }};">{{ number_format($user->points ?? 0) }}</strong></small>
                </div>
                @endif
            </div>
            @endif
            
            {{-- 하단 버튼들 --}}
            @php
                $menuItems = [];
                if ($showNotifications) {
                    $menuItems[] = ['route' => 'notifications.index', 'icon' => 'bi-bell-fill', 'title' => '알림', 'badge' => $unreadNotificationCount];
                }
                if ($showMessages) {
                    $menuItems[] = ['route' => 'messages.index', 'icon' => 'bi-envelope-fill', 'title' => '쪽지', 'badge' => $unreadMessageCount];
                }
                if ($showMyPosts) {
                    $menuItems[] = ['route' => 'users.my-posts', 'icon' => 'bi-file-text-fill', 'title' => '내게시글', 'badge' => 0];
                }
                if ($showProfile) {
                    $menuItems[] = ['route' => 'users.profile', 'icon' => 'bi-person-circle', 'title' => '내정보', 'badge' => 0];
                }
                if ($showEditProfile) {
                    $menuItems[] = ['route' => 'users.profile', 'icon' => 'bi-pencil-square', 'title' => '정보변경', 'badge' => 0, 'hash' => '#edit-profile'];
                }
                if ($showSavedPosts) {
                    $menuItems[] = ['route' => 'users.saved-posts', 'icon' => 'bi-bookmark-fill', 'title' => '저장한글', 'badge' => 0];
                }
                if ($showMyComments) {
                    $menuItems[] = ['route' => 'users.my-comments', 'icon' => 'bi-chat-dots-fill', 'title' => '내댓글', 'badge' => 0];
                }
            @endphp
            @if(count($menuItems) > 0)
            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                @foreach($menuItems as $item)
                    @if($item['route'])
                        <a href="{{ route($item['route'], ['site' => $site->slug ?? 'default']) }}{{ isset($item['hash']) ? $item['hash'] : '' }}" class="text-decoration-none text-center sidebar-widget-btn position-relative" style="flex: 1;" title="{{ $item['title'] }}">
                            <i class="bi {{ $item['icon'] }} d-block mb-1" style="font-size: 1.25rem; color: #6c757d;"></i>
                            @if($item['badge'] > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25em 0.4em;">
                                    {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
            @endif
            
            {{-- 관리자 페이지 링크 (관리자만 표시) --}}
            @php
                // 마스터 사용자 확인: 세션 또는 마스터 가드 또는 이메일로 확인
                $isMasterUser = session('is_master_user', false) || auth('master')->check();
                if (!$isMasterUser && auth()->check()) {
                    $isMasterUser = \App\Models\MasterUser::where('email', auth()->user()->email)->exists();
                }
                $isMasterSite = $site->isMasterSite();
                // 마스터 사이트에서는 마스터 사용자만 관리자 페이지 버튼 표시
                $canShowAdminButton = $user->isAdmin() && (!$isMasterSite || $isMasterUser);
            @endphp
            @if($canShowAdminButton)
                <div class="border-top pt-2 mt-2">
                    <a href="{{ $site->isMasterSite() ? route('master.admin.dashboard') : $site->getAdminDashboardUrl() }}" class="text-decoration-none d-block text-center mb-2" style="color: {{ $pointColor }}; font-size: 0.875rem; font-weight: 500;">
                        <i class="bi bi-shield-check me-1"></i>관리자 페이지
                    </a>
                    {{-- 마스터 콘솔 바로가기 (마스터 사이트에서 마스터 사용자만 표시) --}}
                    @if($isMasterUser && $isMasterSite)
                        <a href="#" onclick="openMasterConsole(event); return false;" class="text-decoration-none d-block text-center" style="color: {{ $pointColor }}; font-size: 0.875rem; font-weight: 500;">
                            <i class="bi bi-gear-fill me-1"></i>마스터 콘솔
                        </a>
                    @endif
                </div>
            @endif
        </div>
    @else
        {{-- 로그인 전 위젯 --}}
        <div class="card-body p-3">
            <form action="{{ route('login', ['site' => $site->slug ?? 'default']) }}" method="POST">
                @csrf
                <input type="hidden" name="intended_url" value="{{ url()->current() }}">
                <div class="mb-2">
                    <div class="input-group" style="border: 1px solid #dee2e6; border-radius: 0.375rem; overflow: hidden;">
                        <span class="input-group-text bg-white border-0" style="padding: 0.5rem;">
                            <i class="bi bi-person text-muted"></i>
                        </span>
                        <input type="text" name="email" class="form-control border-0" placeholder="아이디" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group" style="border: 1px solid #dee2e6; border-radius: 0.375rem; overflow: hidden;">
                        <span class="input-group-text bg-white border-0" style="padding: 0.5rem;">
                            <i class="bi bi-lock text-muted"></i>
                        </span>
                        <input type="password" name="password" class="form-control border-0" placeholder="비밀번호" required>
                    </div>
                </div>
                <button type="submit" class="btn w-100 mb-2" style="background-color: {{ $pointColor }}; color: white; font-weight: 500; border: none; padding: 0.5rem;">
                    로그인
                </button>
                
                {{-- 소셜 로그인 버튼 --}}
                @php
                    // 마스터 사이트는 두 가지 설정 키 모두 확인 (마스터 콘솔 설정 또는 일반 관리자 설정)
                    if ($site->isMasterSite()) {
                        $socialLoginEnabledRaw = $site->getSetting('social_login_enabled', $site->getSetting('registration_enable_social_login', false));
                        $googleClientId = $site->getSetting('social_login_google_client_id', $site->getSetting('google_client_id', ''));
                        $naverClientId = $site->getSetting('social_login_naver_client_id', $site->getSetting('naver_client_id', ''));
                        $kakaoClientId = $site->getSetting('social_login_kakao_client_id', $site->getSetting('kakao_client_id', ''));
                    } else {
                        $socialLoginEnabledRaw = $site->getSetting('registration_enable_social_login', false);
                        $googleClientId = $site->getSetting('google_client_id', '');
                        $naverClientId = $site->getSetting('naver_client_id', '');
                        $kakaoClientId = $site->getSetting('kakao_client_id', '');
                    }
                    // 문자열 "1", "true", 숫자 1 등을 boolean으로 변환
                    $socialLoginEnabled = filter_var($socialLoginEnabledRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                @endphp
                @if($socialLoginEnabled && (!empty($googleClientId) || !empty($naverClientId) || !empty($kakaoClientId)))
                <div class="mb-2">
                    <div class="d-flex gap-1 justify-content-center">
                        @if(!empty($googleClientId))
                        <a href="{{ $site->isMasterSite() ? route('master.social.login', ['provider' => 'google']) : route('social.login', ['site' => $site->slug, 'provider' => 'google']) }}" 
                           class="btn btn-sm btn-outline-danger" 
                           style="padding: 0.4rem 0.6rem; border-radius: 0.375rem; flex: 1;" 
                           title="구글로 로그인">
                            <i class="bi bi-google"></i>
                        </a>
                        @endif
                        @if(!empty($naverClientId))
                        <a href="{{ $site->isMasterSite() ? route('master.social.login', ['provider' => 'naver']) : route('social.login', ['site' => $site->slug, 'provider' => 'naver']) }}" 
                           class="btn btn-sm" 
                           style="background-color: #03C75A; border-color: #03C75A; color: white; padding: 0.4rem 0.6rem; border-radius: 0.375rem; flex: 1;" 
                           title="네이버로 로그인">
                            <span class="fw-bold" style="font-size: 1em;">N</span>
                        </a>
                        @endif
                        @if(!empty($kakaoClientId))
                        <a href="{{ $site->isMasterSite() ? route('master.social.login', ['provider' => 'kakao']) : route('social.login', ['site' => $site->slug, 'provider' => 'kakao']) }}" 
                           class="btn btn-sm" 
                           style="background-color: #FEE500; border-color: #FEE500; color: #000; padding: 0.4rem 0.6rem; border-radius: 0.375rem; flex: 1;" 
                           title="카카오로 로그인">
                            <i class="bi bi-chat-fill"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" id="sidebar-remember" checked>
                        <label class="form-check-label" for="sidebar-remember" style="font-size: 0.875rem;">
                            자동로그인
                        </label>
                    </div>
                    <div>
                        <a href="#" class="text-decoration-none" style="font-size: 0.875rem; color: #6c757d;">정보찾기</a>
                        <span class="mx-1" style="color: #6c757d;">·</span>
                        <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="text-decoration-none" style="font-size: 0.875rem; color: #6c757d;">회원가입</a>
                    </div>
                </div>
            </form>
        </div>
    @endauth
</div>

<style>
.sidebar-user-widget .sidebar-widget-btn:hover i {
    color: {{ $pointColor }} !important;
    transform: scale(1.1);
    transition: all 0.2s ease;
}
</style>

@php
    // 마스터 사용자 확인: 세션 또는 마스터 가드 또는 이메일로 확인
    $isMasterUser = session('is_master_user', false) || auth('master')->check();
    if (!$isMasterUser && auth()->check()) {
        $isMasterUser = \App\Models\MasterUser::where('email', auth()->user()->email)->exists();
    }
    $isMasterSite = $site->isMasterSite();
@endphp

@if($isMasterUser && $isMasterSite)
<script>
function openMasterConsole(event) {
    event.preventDefault();
    
    // SSO 토큰 생성 요청
    fetch('{{ route("master.console.sso-token") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.url) {
            // 새창에서 마스터 콘솔 SSO URL 열기
            window.open(data.url, '_blank');
        } else {
            alert(data.message || '마스터 콘솔로 이동할 수 없습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('마스터 콘솔로 이동하는 중 오류가 발생했습니다.');
    });
}
</script>
@endif

