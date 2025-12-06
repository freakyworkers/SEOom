@php
    $theme = $theme ?? 'design1';
    
    // 실제 설정된 색상 사용
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $isDark = $themeDarkMode === 'dark';
    
    $headerTextColor = $isDark ? $site->getSetting('color_dark_header_text', '#ffffff') : $site->getSetting('color_light_header_text', '#000000');
    $headerBgColor = $isDark ? $site->getSetting('color_dark_header_bg', '#000000') : $site->getSetting('color_light_header_bg', '#ffffff');
    
    // 디자인 타입 확인
    $designType = in_array($theme, ['design1', 'design2', 'design3', 'design4', 'design5', 'design6']) ? $theme : 'design1';
    
    // 메뉴 로그인 표시 설정 확인
    $showMenuLogin = $site->getSetting('menu_login_show', '0') == '1';
    
    // 포인트 컬러 설정
    $pointColor = $isDark ? $site->getSetting('color_dark_point_main', '#ffffff') : $site->getSetting('color_light_point_main', '#0d6efd');
    
    // 헤더 그림자 및 테두리 설정
    $headerShadow = $site->getSetting('header_shadow', '0') == '1';
    $headerBorder = $site->getSetting('header_border', '0') == '1';
    $headerBorderWidth = $site->getSetting('header_border_width', '1');
    $headerBorderColor = $site->getSetting('header_border_color', '#dee2e6');
    
    // 메뉴 폰트 설정
    $menuFontSize = $site->getSetting('menu_font_size', '1.25rem');
    $menuFontPadding = $site->getSetting('menu_font_padding', '0.5rem');
    $menuFontWeight = $site->getSetting('menu_font_weight', '700');
    
    // 가로100% 설정
    $themeFullWidth = $site->getSetting('theme_full_width', '0') == '1';
    $containerClass = $themeFullWidth ? 'container-fluid' : 'container';
    $containerStyle = $themeFullWidth ? 'max-width: 100%; padding-left: 15px; padding-right: 15px;' : '';
    
    // 헤더 스타일 생성
    $headerStyle = "background-color: {$headerBgColor}; color: {$headerTextColor};";
    if ($headerShadow) {
        $headerStyle .= " box-shadow: 0 2px 4px rgba(0,0,0,0.1);";
    }
    if ($headerBorder) {
        $headerStyle .= " border-bottom: {$headerBorderWidth}px solid {$headerBorderColor};";
    }
    
    // 메뉴 로드 (테이블이 존재하는 경우에만)
    $menus = collect([]);
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('menus')) {
            $menus = \App\Models\Menu::where('site_id', $site->id)
                ->whereNull('parent_id')
                ->with('children')
                ->orderBy('order')
                ->get();
        }
    } catch (\Exception $e) {
        // 테이블이 없거나 오류가 발생한 경우 빈 컬렉션 사용
        $menus = collect([]);
    }
    
    // 알림 개수 가져오기
    $unreadNotificationCount = 0;
    if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
        $unreadNotificationCount = \App\Models\Notification::getUnreadCount(auth()->id(), $site->id);
    }
    
    // 쪽지 개수 가져오기
    $unreadMessageCount = 0;
    if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('messages')) {
        $unreadMessageCount = \App\Models\Message::getUnreadCount(auth()->id(), $site->id);
    }
@endphp

<style>
    /* PC 헤더: 1000px 이상에서만 표시 */
    @media (max-width: 999px) {
        .pc-header {
            display: none !important;
        }
    }
    @media (min-width: 1000px) {
        .pc-header {
            display: block !important;
        }
    }
</style>

@if($designType === 'design1')
    {{-- 테마1: 로고 좌측 메뉴 우측 --}}
    <nav class="navbar navbar-expand-lg navbar-dark pc-header" style="{{ $headerStyle }}">
        <div class="{{ $containerClass }}" style="{{ $containerStyle }}">
            <a class="navbar-brand" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoDesktopSize }}px; width: auto; height: auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                    <span style="display: none;">{{ $siteName }}</span>
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav1">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav1">
                <ul class="navbar-nav ms-auto align-items-center">
                    @if($menus->count() > 0)
                        @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight])
                    @endif
                    @if($showMenuLogin)
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center position-relative" href="#" id="navbarDropdown1" role="button" data-bs-toggle="dropdown" style="color: {{ $headerTextColor }} !important;">
                                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                                @if($unreadNotificationCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px; margin-left: 2px;"></span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item position-relative" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">
                                        <i class="bi bi-person me-2"></i>내정보
                                        @if($unreadNotificationCount > 0)
                                            <span class="position-absolute top-50 end-0 translate-middle-y badge rounded-pill bg-danger me-2" style="font-size: 0.65rem; padding: 0.25em 0.4em;">
                                                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                @php
                                    // 마스터 사용자 확인: 세션 또는 마스터 가드 또는 이메일로 확인
                                    $isMasterUser = session('is_master_user', false) || auth('master')->check();
                                    if (!$isMasterUser && auth()->check()) {
                                        $isMasterUser = \App\Models\MasterUser::where('email', auth()->user()->email)->exists();
                                    }
                                    $isMasterSite = $site->isMasterSite();
                                    // 마스터 사이트에서는 마스터 사용자만 관리자 페이지 버튼 표시
                                    $canShowAdminButton = auth()->user()->canManage() && (!$isMasterSite || $isMasterUser);
                                @endphp
                                @if($canShowAdminButton)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ ($site->isMasterSite() ?? false) ? route('master.admin.dashboard') : route('admin.dashboard', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-speedometer2 me-2"></i>관리자</a></li>
                                    @if($isMasterUser && $isMasterSite)
                                        <li><a class="dropdown-item" href="#" onclick="openMasterConsole(event); return false;"><i class="bi bi-gear-fill me-2"></i>마스터 콘솔</a></li>
                                    @endif
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>로그아웃</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                <i class="bi bi-box-arrow-in-right me-1"></i>로그인
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                <i class="bi bi-person-plus me-1"></i>회원가입
                            </a>
                        </li>
                    @endauth
                    @endif
                </ul>
            </div>
        </div>
    </nav>

@elseif($designType === 'design2')
        {{-- 테마2: 로고 좌측 메뉴 중앙 검색창 우측 --}}
        <nav class="navbar navbar-expand-lg navbar-dark pc-header" style="{{ $headerStyle }}">
        <div class="{{ $containerClass }}" style="{{ $containerStyle }}">
            <a class="navbar-brand" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoDesktopSize }}px; width: auto; height: auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                    <span style="display: none;">{{ $siteName }}</span>
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav2">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav2">
                <ul class="navbar-nav mx-auto">
                    @if($menus->count() > 0)
                        @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight])
                    @endif
                </ul>
                <div class="d-flex align-items-center">
                    @if(!$showMenuLogin)
                    <form class="d-flex me-2" action="{{ route('search', ['site' => $site->slug ?? 'default']) }}" method="GET" style="min-width: 200px; max-width: 300px;">
                        <input class="form-control form-control-sm" type="search" name="q" placeholder="검색..." aria-label="Search" style="flex: 1;">
                        <button class="btn btn-outline-light btn-sm ms-2" type="submit" style="--bs-btn-padding-y: 0.25rem; --bs-btn-padding-x: 0.75rem;">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                    @else
                    <ul class="navbar-nav align-items-center">
                        @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center position-relative" href="#" id="navbarDropdown2" role="button" data-bs-toggle="dropdown" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                                    @if($unreadNotificationCount > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px; margin-left: 2px;"></span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-person me-2"></i>내정보</a></li>
                                    <li>
                                        <a class="dropdown-item position-relative" href="{{ route('messages.index', ['site' => $site->slug ?? 'default']) }}">
                                            <i class="bi bi-envelope me-2"></i>쪽지함
                                            @if($unreadMessageCount > 0)
                                                <span class="position-absolute top-50 end-0 translate-middle-y badge rounded-pill bg-danger me-2" style="font-size: 0.65rem; padding: 0.25em 0.4em;">
                                                    {{ $unreadMessageCount > 99 ? '99+' : $unreadMessageCount }}
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                    @php
                                        // 마스터 사용자 확인: 세션 또는 마스터 가드 또는 이메일로 확인
                                        $isMasterUser = session('is_master_user', false) || auth('master')->check();
                                        if (!$isMasterUser && auth()->check()) {
                                            $isMasterUser = \App\Models\MasterUser::where('email', auth()->user()->email)->exists();
                                        }
                                        $isMasterSite = $site->isMasterSite();
                                        // 마스터 사이트에서는 마스터 사용자만 관리자 페이지 버튼 표시
                                        $canShowAdminButton = auth()->user()->canManage() && (!$isMasterSite || $isMasterUser);
                                    @endphp
                                    @if($canShowAdminButton)
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ ($site->isMasterSite() ?? false) ? route('master.admin.dashboard') : route('admin.dashboard', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-speedometer2 me-2"></i>관리자</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>로그아웃</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>로그인
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                    <i class="bi bi-person-plus me-1"></i>회원가입
                                </a>
                            </li>
                        @endauth
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </nav>

@elseif($designType === 'design3')
        {{-- 테마3: 메뉴 좌측 로고 중앙 검색창 우측 --}}
        <nav class="navbar navbar-expand-lg navbar-dark pc-header" style="{{ $headerStyle }}">
        <div class="{{ $containerClass }}" style="{{ $containerStyle }}">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav3">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav3">
                <ul class="navbar-nav">
                    @if($menus->count() > 0)
                        @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight])
                    @endif
                </ul>
            </div>
            <a class="navbar-brand mx-auto" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoDesktopSize }}px; height: auto;" class="d-none d-md-inline" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;" class="d-md-none" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                    <span style="display: none;">{{ $siteName }}</span>
                @endif
            </a>
            <div class="d-flex align-items-center">
                @if(!$showMenuLogin)
                <form class="d-flex me-2" action="{{ route('search', ['site' => $site->slug ?? 'default']) }}" method="GET" style="min-width: 200px; max-width: 300px;">
                    <input class="form-control form-control-sm" type="search" name="q" placeholder="검색..." aria-label="Search" style="flex: 1;">
                    <button class="btn btn-outline-light btn-sm ms-2" type="submit" style="--bs-btn-padding-y: 0.25rem; --bs-btn-padding-x: 0.75rem;">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                @else
                <ul class="navbar-nav align-items-center">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center position-relative" href="#" id="navbarDropdown3" role="button" data-bs-toggle="dropdown" style="color: {{ $headerTextColor }} !important;">
                                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                                @if($unreadNotificationCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px; margin-left: 2px;"></span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item position-relative" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">
                                        <i class="bi bi-person me-2"></i>내정보
                                        @if($unreadNotificationCount > 0)
                                            <span class="position-absolute top-50 end-0 translate-middle-y badge rounded-pill bg-danger me-2" style="font-size: 0.65rem; padding: 0.25em 0.4em;">
                                                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                @php
                                    // 마스터 사용자 확인: 세션 또는 마스터 가드 또는 이메일로 확인
                                    $isMasterUser = session('is_master_user', false) || auth('master')->check();
                                    if (!$isMasterUser && auth()->check()) {
                                        $isMasterUser = \App\Models\MasterUser::where('email', auth()->user()->email)->exists();
                                    }
                                    $isMasterSite = $site->isMasterSite();
                                    // 마스터 사이트에서는 마스터 사용자만 관리자 페이지 버튼 표시
                                    $canShowAdminButton = auth()->user()->canManage() && (!$isMasterSite || $isMasterUser);
                                @endphp
                                @if($canShowAdminButton)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ ($site->isMasterSite() ?? false) ? route('master.admin.dashboard') : route('admin.dashboard', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-speedometer2 me-2"></i>관리자</a></li>
                                    @if($isMasterUser && $isMasterSite)
                                        <li><a class="dropdown-item" href="#" onclick="openMasterConsole(event); return false;"><i class="bi bi-gear-fill me-2"></i>마스터 콘솔</a></li>
                                    @endif
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>로그아웃</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                <i class="bi bi-box-arrow-in-right me-1"></i>로그인
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                <i class="bi bi-person-plus me-1"></i>회원가입
                            </a>
                        </li>
                    @endauth
                </ul>
                @endif
            </div>
        </div>
    </nav>

@elseif($designType === 'design4')
        {{-- 테마4: 로고 좌측 검색창 우측, 하단 중앙 메뉴 --}}
        <nav class="navbar navbar-expand-lg navbar-dark pc-header" style="{{ $headerStyle }}">
        <div class="{{ $containerClass }}" style="{{ $containerStyle }}">
            <div class="w-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <a class="navbar-brand mb-0" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoDesktopSize }}px; height: auto; max-height: 100px;" class="d-none d-md-inline" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto; max-height: 80px;" class="d-md-none" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                            <span style="display: none;">{{ $siteName }}</span>
                        @endif
                    </a>
                    <div class="d-flex align-items-center">
                        @if(!$showMenuLogin)
                        <form class="d-flex me-2" action="{{ route('search', ['site' => $site->slug ?? 'default']) }}" method="GET" style="min-width: 200px; max-width: 300px;">
                            <input class="form-control form-control-sm" type="search" name="q" placeholder="검색..." aria-label="Search" style="flex: 1;">
                            <button class="btn btn-outline-light btn-sm ms-2" type="submit" style="--bs-btn-padding-y: 0.25rem; --bs-btn-padding-x: 0.75rem;">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                        @else
                        <ul class="navbar-nav flex-row align-items-center">
                            @auth
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle d-flex align-items-center position-relative" href="#" id="navbarDropdown4" role="button" data-bs-toggle="dropdown" style="color: {{ $headerTextColor }} !important;">
                                        <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                                        @if($unreadNotificationCount > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px; margin-left: 2px;"></span>
                                        @endif
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-person me-2"></i>내정보</a></li>
                                        @if(($site->isMasterSite() ?? false))
                                            <li><a class="dropdown-item" href="{{ route('users.my-sites', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-house-door me-2"></i>내 홈페이지</a></li>
                                        @endif
                                        @if(auth()->user()->canManage())
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="{{ ($site->isMasterSite() ?? false) ? route('master.admin.dashboard') : route('admin.dashboard', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-speedometer2 me-2"></i>관리자</a></li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>로그아웃</button>
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>로그인
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                        <i class="bi bi-person-plus me-1"></i>회원가입
                                    </a>
                                </li>
                            @endauth
                        </ul>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-center border-top pt-2" style="border-color: rgba(255,255,255,0.2) !important;">
                    <ul class="navbar-nav flex-row">
                        @if($menus->count() > 0)
                            @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight])
                        @else
                            <li class="nav-item me-3">
                                <a class="nav-link {{ request()->routeIs('boards.index') ? 'active' : '' }}" href="{{ route('boards.index', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-grid me-1"></i>게시판
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('search') ? 'active' : '' }}" href="{{ route('search', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-search me-1"></i>검색
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </nav>

@elseif($designType === 'design5')
        {{-- 테마5: 로고 중앙, 하단 메뉴 중앙 정렬 --}}
        <nav class="navbar navbar-expand-lg navbar-dark pc-header" style="{{ $headerStyle }}">
        <div class="{{ $containerClass }}" style="{{ $containerStyle }}">
            <div class="w-100">
                <div class="d-flex justify-content-center mb-3">
                    <a class="navbar-brand" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoDesktopSize }}px; height: auto; max-height: 100px;" class="d-none d-md-inline" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto; max-height: 80px;" class="d-md-none" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                            <span style="display: none;">{{ $siteName }}</span>
                        @endif
                    </a>
                </div>
                <div class="d-flex justify-content-center align-items-center">
                    <ul class="navbar-nav flex-row">
                        @if($menus->count() > 0)
                            @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight])
                        @else
                            <li class="nav-item me-3">
                                <a class="nav-link {{ request()->routeIs('boards.index') ? 'active' : '' }}" href="{{ route('boards.index', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-grid me-1"></i>게시판
                                </a>
                            </li>
                            <li class="nav-item me-3">
                                <a class="nav-link {{ request()->routeIs('search') ? 'active' : '' }}" href="{{ route('search', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-search me-1"></i>검색
                                </a>
                            </li>
                        @endif
                        @if($showMenuLogin)
                        @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center position-relative" href="#" id="navbarDropdown5" role="button" data-bs-toggle="dropdown" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                                    @if($unreadNotificationCount > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px; margin-left: 2px;"></span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-person me-2"></i>내정보</a></li>
                                    <li>
                                        <a class="dropdown-item position-relative" href="{{ route('messages.index', ['site' => $site->slug ?? 'default']) }}">
                                            <i class="bi bi-envelope me-2"></i>쪽지함
                                            @if($unreadMessageCount > 0)
                                                <span class="position-absolute top-50 end-0 translate-middle-y badge rounded-pill bg-danger me-2" style="font-size: 0.65rem; padding: 0.25em 0.4em;">
                                                    {{ $unreadMessageCount > 99 ? '99+' : $unreadMessageCount }}
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                    @php
                                        // 마스터 사용자 확인: 세션 또는 마스터 가드 또는 이메일로 확인
                                        $isMasterUser = session('is_master_user', false) || auth('master')->check();
                                        if (!$isMasterUser && auth()->check()) {
                                            $isMasterUser = \App\Models\MasterUser::where('email', auth()->user()->email)->exists();
                                        }
                                        $isMasterSite = $site->isMasterSite();
                                        // 마스터 사이트에서는 마스터 사용자만 관리자 페이지 버튼 표시
                                        $canShowAdminButton = auth()->user()->canManage() && (!$isMasterSite || $isMasterUser);
                                    @endphp
                                    @if($canShowAdminButton)
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ ($site->isMasterSite() ?? false) ? route('master.admin.dashboard') : route('admin.dashboard', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-speedometer2 me-2"></i>관리자</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>로그아웃</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>로그인
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                    <i class="bi bi-person-plus me-1"></i>회원가입
                                </a>
                            </li>
                        @endauth
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </nav>

@elseif($designType === 'design6')
        {{-- 테마6: 메뉴 좌측 로고 우측 --}}
        <nav class="navbar navbar-expand-lg navbar-dark pc-header" style="{{ $headerStyle }}">
        <div class="{{ $containerClass }}" style="{{ $containerStyle }}">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav6">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav6">
                <ul class="navbar-nav">
                    @if($showMenuLogin)
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center position-relative" href="#" id="navbarDropdown6" role="button" data-bs-toggle="dropdown" style="color: {{ $headerTextColor }} !important;">
                                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                                @if($unreadNotificationCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px; margin-left: 2px;"></span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item position-relative" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">
                                        <i class="bi bi-person me-2"></i>내정보
                                        @if($unreadNotificationCount > 0)
                                            <span class="position-absolute top-50 end-0 translate-middle-y badge rounded-pill bg-danger me-2" style="font-size: 0.65rem; padding: 0.25em 0.4em;">
                                                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                @php
                                    // 마스터 사용자 확인: 세션 또는 마스터 가드 또는 이메일로 확인
                                    $isMasterUser = session('is_master_user', false) || auth('master')->check();
                                    if (!$isMasterUser && auth()->check()) {
                                        $isMasterUser = \App\Models\MasterUser::where('email', auth()->user()->email)->exists();
                                    }
                                    $isMasterSite = $site->isMasterSite();
                                    // 마스터 사이트에서는 마스터 사용자만 관리자 페이지 버튼 표시
                                    $canShowAdminButton = auth()->user()->canManage() && (!$isMasterSite || $isMasterUser);
                                @endphp
                                @if($canShowAdminButton)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ ($site->isMasterSite() ?? false) ? route('master.admin.dashboard') : route('admin.dashboard', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-speedometer2 me-2"></i>관리자</a></li>
                                    @if($isMasterUser && $isMasterSite)
                                        <li><a class="dropdown-item" href="#" onclick="openMasterConsole(event); return false;"><i class="bi bi-gear-fill me-2"></i>마스터 콘솔</a></li>
                                    @endif
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>로그아웃</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                <i class="bi bi-box-arrow-in-right me-1"></i>로그인
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">
                                <i class="bi bi-person-plus me-1"></i>회원가입
                            </a>
                        </li>
                    @endauth
                    @endif
                    @if($menus->count() > 0)
                        @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight])
                    @endif
                </ul>
            </div>
            <a class="navbar-brand ms-auto" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoDesktopSize }}px; height: auto;" class="d-none d-md-inline" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;" class="d-md-none" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                    <span style="display: none;">{{ $siteName }}</span>
                @endif
            </a>
        </div>
    </nav>
@endif

