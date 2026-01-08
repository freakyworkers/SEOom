@php
    // 실제 설정된 색상 사용
    $headerBgColor = $headerBg ?? '#0d6efd';
    $headerTextColor = $headerText ?? '#ffffff';
    
    // 기본값 설정
    $theme = $theme ?? 'design1';
    $siteName = $siteName ?? 'SEOom Builder';
    $logoType = $logoType ?? 'image';
    $logoDesktopSize = $logoDesktopSize ?? '300';
    $logoMobileSize = $logoMobileSize ?? '200';
    $themeTopHeaderShow = isset($themeTopHeaderShow) ? $themeTopHeaderShow : '0';
    $topHeaderLoginShow = isset($topHeaderLoginShow) ? $topHeaderLoginShow : '0';
    $menuLoginShow = isset($menuLoginShow) ? $menuLoginShow : '0';
    $headerHideSearch = isset($headerHideSearch) ? $headerHideSearch : '0';
    $headerSticky = isset($headerSticky) ? $headerSticky : '0';
    $themeDarkMode = isset($themeDarkMode) ? $themeDarkMode : 'light';
    $isDark = $themeDarkMode === 'dark';
    
    // 로고 설정: 다크 모드일 때 다크 모드 로고 사용
    if (!isset($siteLogo) || empty($siteLogo)) {
        $siteLogo = $isDark ? ($settings['site_logo_dark'] ?? $settings['site_logo'] ?? '') : ($settings['site_logo'] ?? '');
    } else {
        // 전달된 siteLogo가 있지만 다크 모드이고 다크 모드 로고가 있으면 다크 모드 로고 사용
        if ($isDark && !empty($settings['site_logo_dark'] ?? '')) {
            $siteLogo = $settings['site_logo_dark'];
        }
    }
    
    // 최상단 헤더 바 색상
    $topBarBg = $themeDarkMode === 'dark' ? ($settings['color_dark_header_bg'] ?? '#000000') : ($settings['color_light_header_bg'] ?? '#ffffff');
    $topBarText = $themeDarkMode === 'dark' ? ($settings['color_dark_header_text'] ?? '#ffffff') : ($settings['color_light_header_text'] ?? '#000000');
    
    // 포인트 컬러 설정 (전달된 값 우선, 없으면 설정값 사용)
    if (!isset($pointColor)) {
        $pointColor = $themeDarkMode === 'dark' ? ($settings['color_dark_point_main'] ?? '#ffffff') : ($settings['color_light_point_main'] ?? '#0d6efd');
    }
    
    // 한국 시간
    $koreaTime = now()->setTimezone('Asia/Seoul');
    $koreaDate = $koreaTime->format('Y년 m월 d일');
    
    // 방문자 수 (미리보기용 더미 데이터)
    $todayVisitors = 123;
    $totalVisitors = 4567;
    
    // 헤더 그림자 및 테두리 설정
    $headerShadow = isset($headerShadow) ? ($headerShadow == '1' || $headerShadow === '1') : false;
    $headerBorder = isset($headerBorder) ? ($headerBorder == '1' || $headerBorder === '1') : false;
    $headerBorderWidth = isset($headerBorderWidth) ? $headerBorderWidth : '1';
    // 헤더 테두리 컬러는 포인트 컬러 사용
    $headerBorderColor = isset($headerBorderColor) ? $headerBorderColor : $pointColor;
    
    // 메뉴 폰트 설정 (rem 단위 보장)
    $menuFontSize = isset($menuFontSize) ? $menuFontSize : ($settings['menu_font_size'] ?? '1.25rem');
    $menuFontPadding = isset($menuFontPadding) ? $menuFontPadding : ($settings['menu_font_padding'] ?? '0.5rem');
    $menuFontWeight = isset($menuFontWeight) ? $menuFontWeight : ($settings['menu_font_weight'] ?? '700');
    $menuFontColor = isset($menuFontColor) ? $menuFontColor : ($settings['menu_font_color'] ?? null);
    
    // rem 단위가 아닌 경우 기본값 사용
    if (!preg_match('/^\d+(\.\d+)?rem$/', $menuFontSize)) {
        $menuFontSize = '1.25rem';
    }
    if (!preg_match('/^\d+(\.\d+)?rem$/', $menuFontPadding)) {
        $menuFontPadding = '0.5rem';
    }
    
    // CSS calc에서 사용할 수 있도록 숫자만 추출 (rem 단위 제거)
    $menuFontSizeNum = floatval(preg_replace('/[^0-9.]/', '', $menuFontSize));
    $menuFontPaddingNum = floatval(preg_replace('/[^0-9.]/', '', $menuFontPadding));
    
    // 기본값 보장 및 유효성 검사
    if ($menuFontSizeNum <= 0 || !is_numeric($menuFontSizeNum) || is_nan($menuFontSizeNum)) {
        $menuFontSizeNum = 1.25;
    }
    if ($menuFontPaddingNum <= 0 || !is_numeric($menuFontPaddingNum) || is_nan($menuFontPaddingNum)) {
        $menuFontPaddingNum = 0.5;
    }
    
    // 계산된 값 검증 (너무 큰 값 방지)
    $menuFontSizeScaled = $menuFontSizeNum * 0.7;
    $menuFontPaddingScaled = $menuFontPaddingNum * 0.7;
    
    if ($menuFontSizeScaled <= 0 || $menuFontSizeScaled > 10) {
        $menuFontSizeScaled = 0.875; // 1.25 * 0.7
    }
    if ($menuFontPaddingScaled <= 0 || $menuFontPaddingScaled > 5) {
        $menuFontPaddingScaled = 0.35; // 0.5 * 0.7
    }
    
    // 메뉴 로드 (테이블이 존재하는 경우에만)
    $menus = collect([]);
    try {
        if (isset($site) && $site) {
            $siteId = is_object($site) && isset($site->id) ? $site->id : (is_array($site) && isset($site['id']) ? $site['id'] : null);
            if ($siteId && \Illuminate\Support\Facades\Schema::hasTable('menus')) {
                $menus = \App\Models\Menu::where('site_id', $siteId)
                    ->whereNull('parent_id')
                    ->with('children')
                    ->orderBy('order')
                    ->get();
            }
        }
    } catch (\Exception $e) {
        // 테이블이 없거나 오류가 발생한 경우 빈 컬렉션 사용
        \Log::warning('Menu load error in preview: ' . $e->getMessage());
        $menus = collect([]);
    }
    
    // 헤더 스타일 생성
    $headerStyle = "background-color: {$headerBgColor}; color: {$headerTextColor};";
    if ($headerShadow) {
        $headerStyle .= " box-shadow: 0 2px 4px rgba(0,0,0,0.1);";
    }
    if ($headerBorder) {
        $headerStyle .= " border-bottom: {$headerBorderWidth}px solid {$headerBorderColor};";
    }
@endphp

<style>
    .header-preview-wrapper {
        transform: scale(0.7);
        transform-origin: top left;
        width: 142.86%;
        margin-bottom: -30%;
        position: relative;
    }
    .header-preview-wrapper .navbar {
        min-height: auto;
        padding: 0.5rem 1rem;
        position: relative;
        z-index: 1;
    }
    .header-preview-wrapper .navbar-brand {
        font-size: calc(1.5rem * 0.7);
        font-weight: bold;
    }
    .header-preview-wrapper .navbar-brand img {
        height: auto;
        width: auto;
        display: block;
        max-width: none;
    }
    .header-preview-wrapper .nav-link {
        font-size: {{ number_format($menuFontSizeScaled, 3, '.', '') }}rem !important;
        padding: {{ number_format($menuFontPaddingScaled, 3, '.', '') }}rem !important;
        font-weight: {{ $menuFontWeight }} !important;
    }
    .header-preview-wrapper .form-control-sm {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }
    .header-preview-wrapper .dropdown-menu {
        font-size: 1rem;
    }
    .header-preview-wrapper .top-header-bar-preview {
        font-size: 0.875rem;
    }
    .header-preview-wrapper .top-header-bar-preview small {
        font-size: 0.875rem !important;
    }
    .header-preview-wrapper .top-header-bar-preview .btn-sm {
        font-size: calc(0.75rem * 0.7) !important;
        padding: calc(0.15rem * 0.7) calc(0.5rem * 0.7) !important;
    }
    .header-preview-wrapper .nav-item {
        margin-right: {{ number_format($menuFontPaddingScaled, 3, '.', '') }}rem !important;
    }
    .header-preview-wrapper .btn {
        font-size: calc(0.875rem * 0.7) !important;
        padding: calc(0.375rem * 0.7) calc(0.75rem * 0.7) !important;
    }
</style>

<div class="header-preview-wrapper">
    @if($themeTopHeaderShow == '1' || $themeTopHeaderShow === '1')
        <div class="top-header-bar-preview" style="background-color: {{ $topBarBg }}; color: {{ $topBarText }}; padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.1); margin-bottom: 0; border-radius: 0.375rem 0.375rem 0 0;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="{{ ($topHeaderLoginShow == '1' || $topHeaderLoginShow === '1') ? 'col-md-6 text-start' : 'col-md-6 text-start' }}">
                        <div class="d-flex align-items-center gap-2">
                            <small style="color: {{ $topBarText }} !important; font-size: 0.875rem;">
                                {{ $koreaDate }}
                            </small>
                            @if(($topHeaderLoginShow == '1' || $topHeaderLoginShow === '1'))
                                <small style="color: {{ $topBarText }} !important; font-size: 0.875rem;">
                                    오늘 : <strong>{{ number_format($todayVisitors) }}</strong>명 전체 : <strong>{{ number_format($totalVisitors) }}</strong>명
                                </small>
                            @endif
                        </div>
                    </div>
                    @if($topHeaderLoginShow == '1' || $topHeaderLoginShow === '1')
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex align-items-center justify-content-md-end gap-1">
                            <a class="btn btn-sm" href="#" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                <i class="bi bi-box-arrow-in-right me-1"></i>로그인
                            </a>
                            <a class="btn btn-sm" href="#" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $topBarBg }} !important; padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                <i class="bi bi-person-plus me-1"></i>회원가입
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="col-md-6 text-md-end">
                        <small style="color: {{ $topBarText }} !important; font-size: 0.875rem;">
                            오늘 : <strong>{{ number_format($todayVisitors) }}</strong>명 전체 : <strong>{{ number_format($totalVisitors) }}</strong>명
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
    @if($theme === 'design1')
        {{-- 테마1: 로고 좌측 메뉴 우측 --}}
        <nav class="navbar navbar-expand-lg navbar-dark" style="{{ $headerStyle }} border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <a class="navbar-brand" href="#" style="color: {{ $headerTextColor }} !important; font-size: calc(1.5rem * 0.7); font-weight: bold;">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: calc({{ $logoDesktopSize }}px * 0.7); height: auto; max-width: none;">
                    @endif
                </a>
                <ul class="navbar-nav ms-auto">
                    @if($menus->count() > 0)
                        @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight, 'menuFontColor' => $menuFontColor])
                    @else
                    <li class="nav-item me-2">
                        <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                            <i class="bi bi-grid me-1"></i>게시판
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                            <i class="bi bi-search me-1"></i>검색
                        </a>
                    </li>
                    @endif
                    @if($menuLoginShow == '1' || $menuLoginShow === '1')
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">로그인</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">회원가입</a>
                    </li>
                    @endif
                </ul>
            </div>
        </nav>

    @elseif($theme === 'design2')
        {{-- 테마2: 로고 좌측 메뉴 중앙 검색창 우측 --}}
        <nav class="navbar navbar-expand-lg navbar-dark" style="{{ $headerStyle }} border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <a class="navbar-brand" href="#" style="color: {{ $headerTextColor }} !important; font-size: calc(1.5rem * 0.7); font-weight: bold;">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: calc({{ $logoDesktopSize }}px * 0.7); height: auto; max-width: none;">
                    @endif
                </a>
                <ul class="navbar-nav mx-auto">
                    @if($menus->count() > 0)
                        @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight, 'menuFontColor' => $menuFontColor])
                    @else
                    <li class="nav-item me-3">
                        <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                            <i class="bi bi-grid me-1"></i>게시판
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                            <i class="bi bi-search me-1"></i>검색
                        </a>
                    </li>
                    @endif
                </ul>
                <div class="d-flex align-items-center">
                    @if($menuLoginShow == '1' || $menuLoginShow === '1')
                    <ul class="navbar-nav flex-row">
                        <li class="nav-item">
                            <a class="nav-link" href="#" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">로그인</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">회원가입</a>
                        </li>
                    </ul>
                    @elseif($headerHideSearch != '1' && $headerHideSearch !== '1')
                    <form class="d-flex me-2" style="min-width: 150px; max-width: 200px;">
                        <input class="form-control form-control-sm" type="search" placeholder="검색..." style="flex: 1;">
                        <button class="btn btn-outline-light btn-sm ms-2" type="submit" style="--bs-btn-padding-y: 0.25rem; --bs-btn-padding-x: 0.75rem;">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </nav>

    @elseif($theme === 'design3')
        {{-- 테마3: 메뉴 좌측 로고 중앙 검색창 우측 --}}
        <nav class="navbar navbar-expand-lg navbar-dark" style="{{ $headerStyle }} border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <ul class="navbar-nav flex-row me-3">
                    @if($menus->count() > 0)
                        @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight, 'menuFontColor' => $menuFontColor])
                    @else
                    <li class="nav-item me-2">
                        <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                            <i class="bi bi-grid me-1"></i>게시판
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                            <i class="bi bi-search me-1"></i>검색
                        </a>
                    </li>
                    @endif
                </ul>
                <a class="navbar-brand mx-auto" href="#" style="color: {{ $headerTextColor }} !important; font-size: calc(1.5rem * 0.7); font-weight: bold;">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: calc({{ $logoDesktopSize }}px * 0.7); height: auto; max-width: none;">
                    @endif
                </a>
                <div class="d-flex align-items-center">
                    @if($menuLoginShow == '1' || $menuLoginShow === '1')
                    <ul class="navbar-nav flex-row">
                        <li class="nav-item">
                            <a class="nav-link" href="#" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">로그인</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">회원가입</a>
                        </li>
                    </ul>
                    @elseif($headerHideSearch != '1' && $headerHideSearch !== '1')
                    <form class="d-flex me-2" style="min-width: 150px; max-width: 200px;">
                        <input class="form-control form-control-sm" type="search" placeholder="검색..." style="flex: 1;">
                        <button class="btn btn-outline-light btn-sm ms-2" type="submit" style="--bs-btn-padding-y: 0.25rem; --bs-btn-padding-x: 0.75rem;">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </nav>

    @elseif($theme === 'design4')
        {{-- 테마4: 로고 좌측 검색창 우측, 하단 중앙 메뉴 --}}
        <nav class="navbar navbar-expand-lg navbar-dark" style="{{ $headerStyle }} border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <div class="w-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <a class="navbar-brand mb-0" href="#" style="color: {{ $headerTextColor }} !important; font-size: calc(1.5rem * 0.7); font-weight: bold;">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: calc({{ $logoDesktopSize }}px * 0.7); height: auto; max-width: none;">
                            @endif
                        </a>
                        <div class="d-flex align-items-center">
                            @if($menuLoginShow == '1' || $menuLoginShow === '1')
                            <ul class="navbar-nav flex-row">
                                <li class="nav-item">
                                    <a class="nav-link" href="#" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">로그인</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">회원가입</a>
                                </li>
                            </ul>
                            @elseif($headerHideSearch != '1' && $headerHideSearch !== '1')
                            <form class="d-flex me-2" style="min-width: 150px; max-width: 200px;">
                                <input class="form-control form-control-sm" type="search" placeholder="검색..." style="flex: 1;">
                                <button class="btn btn-outline-light btn-sm ms-2" type="submit" style="--bs-btn-padding-y: 0.25rem; --bs-btn-padding-x: 0.75rem;">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex justify-content-center border-top pt-2" style="border-color: rgba(255,255,255,0.2) !important;">
                        <ul class="navbar-nav flex-row">
                            @if($menus->count() > 0)
                                @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight, 'menuFontColor' => $menuFontColor])
                            @else
                            <li class="nav-item me-3">
                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-grid me-1"></i>게시판
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-search me-1"></i>검색
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

    @elseif($theme === 'design5')
        {{-- 테마5: 로고 중앙, 하단 메뉴 중앙 정렬 --}}
        <nav class="navbar navbar-expand-lg navbar-dark" style="{{ $headerStyle }} border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <div class="w-100">
                    <div class="d-flex justify-content-center mb-2">
                        <a class="navbar-brand" href="#" style="color: {{ $headerTextColor }} !important;">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: calc({{ $logoDesktopSize }}px * 0.7); height: auto; max-width: none;">
                            @endif
                        </a>
                    </div>
                    <div class="d-flex justify-content-center align-items-center">
                        <ul class="navbar-nav flex-row">
                            @if($menus->count() > 0)
                                @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight, 'menuFontColor' => $menuFontColor])
                            @else
                            <li class="nav-item me-3">
                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-grid me-1"></i>게시판
                                </a>
                            </li>
                            <li class="nav-item me-3">
                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                                    <i class="bi bi-search me-1"></i>검색
                                </a>
                            </li>
                            @endif
                            @if($menuLoginShow == '1' || $menuLoginShow === '1')
                            <li class="nav-item">
                                <a class="nav-link" href="#" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">로그인</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">회원가입</a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

    @elseif($theme === 'design6')
        {{-- 테마6: 메뉴 좌측 로고 우측 --}}
        <nav class="navbar navbar-expand-lg navbar-dark" style="{{ $headerStyle }} border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <ul class="navbar-nav flex-row me-3">
                    @if($menuLoginShow == '1' || $menuLoginShow === '1')
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">로그인</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $headerBgColor }} !important; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.25rem;">회원가입</a>
                    </li>
                    @endif
                    @if($menus->count() > 0)
                        @include('components.menu-items', ['menus' => $menus, 'headerTextColor' => $headerTextColor, 'pointColor' => $pointColor, 'headerBorder' => $headerBorder, 'headerBorderWidth' => $headerBorderWidth, 'headerBorderColor' => $headerBorderColor, 'menuFontSize' => $menuFontSize, 'menuFontPadding' => $menuFontPadding, 'menuFontWeight' => $menuFontWeight, 'menuFontColor' => $menuFontColor])
                    @else
                    <li class="nav-item me-2">
                        <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                            <i class="bi bi-grid me-1"></i>게시판
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">
                            <i class="bi bi-search me-1"></i>검색
                        </a>
                    </li>
                    @endif
                </ul>
                <a class="navbar-brand ms-auto" href="#" style="color: {{ $headerTextColor }} !important; font-size: calc(1.5rem * 0.7); font-weight: bold;">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: calc({{ $logoDesktopSize }}px * 0.7); height: auto; max-width: none;">
                    @endif
                </a>
            </div>
        </nav>
    @endif
</div>

