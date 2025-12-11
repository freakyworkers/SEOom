@php
    $theme = $theme ?? 'theme1';
    
    // 실제 설정된 색상 사용
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $isDark = $themeDarkMode === 'dark';
    
    $headerTextColor = $isDark ? $site->getSetting('color_dark_header_text', '#ffffff') : $site->getSetting('color_light_header_text', '#000000');
    $headerBgColor = $isDark ? $site->getSetting('color_dark_header_bg', '#000000') : $site->getSetting('color_light_header_bg', '#ffffff');
    
    // 포인트 컬러 설정
    $pointColor = $isDark ? $site->getSetting('color_dark_point_main', '#ffffff') : $site->getSetting('color_light_point_main', '#0d6efd');
    
    // 헤더 그림자 및 테두리 설정
    $headerShadow = $site->getSetting('header_shadow', '0') == '1';
    $headerBorder = $site->getSetting('header_border', '0') == '1';
    $headerBorderWidth = $site->getSetting('header_border_width', '1');
    $headerBorderColor = $site->getSetting('header_border_color', '#dee2e6');
    
    // 모바일 메뉴 아이콘 및 방향 설정
    $mobileMenuIcon = $site->getSetting('mobile_menu_icon', 'bi-list');
    $mobileMenuDirection = $site->getSetting('mobile_menu_direction', 'top-to-bottom');
    $mobileMenuIconBorder = $site->getSetting('mobile_menu_icon_border', '0') == '1';
    $mobileMenuLoginWidget = $site->getSetting('mobile_menu_login_widget', '0') == '1';
    
    // 투명헤더 설정
    $mobileHeaderTransparent = $site->getSetting('mobile_header_transparent', '0') == '1';
    
    // 헤더 스타일 생성
    $headerStyle = "color: {$headerTextColor};";
    if ($mobileHeaderTransparent) {
        // 투명헤더가 활성화된 경우 배경색 제거
        $headerStyle .= " background-color: transparent;";
    } else {
        $headerStyle .= " background-color: {$headerBgColor};";
    }
    // 하단 메뉴가 있는 테마(5,6,7,8)는 헤더 하단에 회색 구분선만 적용하고 그림자 제거
    if (in_array($theme, ['theme5', 'theme6', 'theme7', 'theme8'])) {
        $headerStyle .= " border-bottom: 1px solid #dee2e6;";
        // 그림자는 하단 메뉴에 적용되므로 헤더에는 그림자 제거
    } else {
        if ($headerShadow) {
            $headerStyle .= " box-shadow: 0 2px 4px rgba(0,0,0,0.1);";
        }
        if ($headerBorder) {
            $headerStyle .= " border-bottom: {$headerBorderWidth}px solid {$headerBorderColor};";
        }
    }
    
    // 헤더 클래스 추가
    $headerClass = $mobileHeaderTransparent ? 'mobile-header-transparent' : '';
    
    // 로고 설정
    $siteName = $site->getSetting('site_name', $site->name ?? 'SEOom Builder');
    $siteLogo = $site->getSetting('site_logo', '');
    $logoType = $site->getSetting('logo_type', 'text');
    $logoMobileSize = $site->getSetting('logo_mobile_size', '200');
    
    // 메뉴 로드
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
        $menus = collect([]);
    }
    
    // 메뉴 방향에 따른 애니메이션 클래스
    $menuAnimationClass = '';
    switch($mobileMenuDirection) {
        case 'left-to-right':
            $menuAnimationClass = 'slide-in-left';
            break;
        case 'right-to-left':
            $menuAnimationClass = 'slide-in-right';
            break;
        case 'bottom-to-top':
            $menuAnimationClass = 'slide-in-bottom';
            break;
        default: // top-to-bottom
            $menuAnimationClass = 'slide-in-top';
    }
@endphp

<style>
    /* PC 버전에서 모바일 헤더 및 메뉴 완전히 숨김 */
    @media (min-width: 1000px) {
        .d-xl-none,
        nav.navbar.navbar-expand-lg.d-xl-none,
        body > div.sticky-header-wrapper > nav.navbar.navbar-expand-lg.d-xl-none,
        .sticky-header-wrapper .navbar.navbar-expand-lg.d-xl-none {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            height: 0 !important;
            max-height: 0 !important;
            overflow: hidden !important;
            padding: 0 !important;
            margin: 0 !important;
            position: absolute !important;
            left: -9999px !important;
        }
        .navbar-collapse.mobile-menu-overlay {
            display: none !important;
        }
        .mobile-menu-backdrop {
            display: none !important;
        }
    }
    
    /* 모바일 버전: 1000px 미만에서만 표시 */
    @media (max-width: 999px) {
        .d-xl-none {
            display: block !important;
        }
    }
    
    /* 모바일 메뉴 오버레이 스타일 */
    .navbar-collapse.mobile-menu-overlay {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100% !important;
        height: 100vh !important;
        max-height: 100vh !important;
        z-index: 9999;
        background-color: {{ $headerBgColor }};
        padding: 0 !important;
        margin: 0 !important;
        overflow-y: auto;
        overflow-x: hidden !important;
        /* Bootstrap collapse 기본 동작: show 클래스가 없으면 숨김 */
        display: none !important;
        flex-direction: column;
        box-sizing: border-box;
    }
    
    /* show 클래스가 있을 때만 표시 */
    .navbar-collapse.mobile-menu-overlay.show {
        display: flex !important;
    }
    
    /* 위에서 아래 / 아래에서 위 메뉴는 50% 높이만 사용 */
    .navbar-collapse.mobile-menu-overlay.slide-in-top,
    .navbar-collapse.mobile-menu-overlay.slide-in-bottom {
        height: 50vh !important;
        max-height: 50vh !important;
    }
    
    /* 위에서 아래 / 아래에서 위 메뉴의 로고 영역 */
    .navbar-collapse.mobile-menu-overlay.slide-in-top .mobile-menu-logo,
    .navbar-collapse.mobile-menu-overlay.slide-in-bottom .mobile-menu-logo {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        order: -1;
        flex-shrink: 0;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
        position: relative;
    }
    
    .navbar-collapse.mobile-menu-overlay.slide-in-top .mobile-menu-logo a,
    .navbar-collapse.mobile-menu-overlay.slide-in-bottom .mobile-menu-logo a {
        color: {{ $headerTextColor }} !important;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.25rem;
    }
    
    .navbar-collapse.mobile-menu-overlay.slide-in-top .mobile-menu-logo img,
    .navbar-collapse.mobile-menu-overlay.slide-in-bottom .mobile-menu-logo img {
        max-width: 150px;
        height: auto;
        width: auto;
        display: block;
        flex-shrink: 0;
    }
    
    /* 위에서 아래 / 아래에서 위 메뉴의 X 버튼 위치 조정 - 로고와 같은 줄에 세로 중앙 정렬 */
    .navbar-collapse.mobile-menu-overlay.slide-in-top .mobile-menu-close-btn,
    .navbar-collapse.mobile-menu-overlay.slide-in-bottom .mobile-menu-close-btn {
        position: static;
        margin-left: auto;
        background: none;
        border: none;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* 위에서 아래 메뉴는 상단에 위치 */
    .navbar-collapse.mobile-menu-overlay.slide-in-top {
        bottom: auto !important;
        border-bottom: 3px solid {{ $pointColor }};
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* 아래에서 위 메뉴는 하단에 위치 */
    .navbar-collapse.mobile-menu-overlay.slide-in-bottom {
        top: auto !important;
        border-top: 3px solid {{ $pointColor }};
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* 좌에서 우 / 우에서 좌 메뉴는 85% 너비만 사용 */
    .navbar-collapse.mobile-menu-overlay.slide-in-left,
    .navbar-collapse.mobile-menu-overlay.slide-in-right {
        width: 85% !important;
    }
    
    /* 좌에서 우 메뉴는 왼쪽에서 시작 */
    .navbar-collapse.mobile-menu-overlay.slide-in-left {
        right: auto !important;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
        @if($headerBorder)
        border-right: {{ $headerBorderWidth }}px solid {{ $headerBorderColor }};
        @endif
    }
    
    /* 우에서 좌 메뉴는 오른쪽에서 시작 */
    .navbar-collapse.mobile-menu-overlay.slide-in-right {
        left: auto !important;
        box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
        @if($headerBorder)
        border-left: {{ $headerBorderWidth }}px solid {{ $headerBorderColor }};
        @endif
    }
    
    /* 모바일 메뉴 닫기 버튼 */
    .mobile-menu-close-btn {
        position: absolute;
        top: 0.5rem;
        z-index: 10000;
        background: transparent;
        border: none;
        font-size: 1.5rem;
        color: {{ $headerTextColor }};
        cursor: pointer;
        padding: 0.5rem;
        line-height: 1;
    }
    
    /* 좌에서 우 / 우에서 좌 메뉴의 로고 영역 */
    .navbar-collapse.mobile-menu-overlay.slide-in-left .mobile-menu-logo,
    .navbar-collapse.mobile-menu-overlay.slide-in-right .mobile-menu-logo {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        order: -1;
        flex-shrink: 0;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
        position: relative;
    }
    
    .navbar-collapse.mobile-menu-overlay.slide-in-left .mobile-menu-logo a,
    .navbar-collapse.mobile-menu-overlay.slide-in-right .mobile-menu-logo a {
        color: {{ $headerTextColor }} !important;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.25rem;
    }
    
    .navbar-collapse.mobile-menu-overlay.slide-in-left .mobile-menu-logo img,
    .navbar-collapse.mobile-menu-overlay.slide-in-right .mobile-menu-logo img {
        max-width: 150px;
        height: auto;
        width: auto;
        display: block;
        flex-shrink: 0;
    }
    
    /* 좌에서 우 / 우에서 좌 메뉴의 X 버튼 위치 조정 - 로고와 같은 줄에 세로 중앙 정렬 */
    .navbar-collapse.mobile-menu-overlay.slide-in-left .mobile-menu-close-btn,
    .navbar-collapse.mobile-menu-overlay.slide-in-right .mobile-menu-close-btn {
        position: static;
        margin-left: auto;
        background: none;
        border: none;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* 좌에서 우 / 우에서 좌 메뉴의 메뉴 항목들 */
    .navbar-collapse.mobile-menu-overlay.slide-in-left .navbar-nav,
    .navbar-collapse.mobile-menu-overlay.slide-in-right .navbar-nav {
        padding-top: 0;
    }
    
    /* 메뉴 버튼 아이콘에 테두리가 없는 경우 좌우 패딩 제거 */
    .navbar-toggler[style*="border: none"] {
        padding-left: 0;
        padding-right: 0;
    }
    
    /* 로고 좌측 정렬인 경우 왼쪽 마진 제거 */
    .navbar .navbar-brand:not(.ms-auto):not(.mx-auto) {
        margin-left: 0;
    }
    
    /* 로고 우측 정렬인 경우 우측 마진 제거 */
    .navbar .navbar-brand.ms-auto {
        margin-right: 0;
    }
    
    /* 모바일 헤더 높이를 로고 높이에 맞춰 자동 조정 - 상하 패딩 없음 */
    .navbar.navbar-expand-lg.d-xl-none {
        min-height: auto !important;
        height: auto !important;
        max-height: none !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        overflow: visible !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
    }
    
    /* 로고 이미지가 높을 때 헤더 높이 자동 조정 */
    .navbar.navbar-expand-lg.d-xl-none .navbar-brand {
        display: inline-flex !important;
        align-items: center !important;
        padding: 0 !important;
        height: auto !important;
        min-height: auto !important;
        max-height: none !important;
        line-height: 1 !important;
        white-space: nowrap;
        overflow: visible !important;
        flex-shrink: 0 !important;
    }
    
    .navbar.navbar-expand-lg.d-xl-none .navbar-brand img {
        max-height: none !important;
        height: auto !important;
        width: auto !important;
        max-width: {{ $logoMobileSize }}px !important;
        min-width: 0 !important;
        object-fit: contain !important;
        display: block !important;
        vertical-align: middle !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        clip: auto !important;
    }
    
    /* 컨테이너 플렉스 정렬로 로고 높이에 맞춤 - JavaScript로 동적 패딩 설정 */
    .navbar.navbar-expand-lg.d-xl-none .container-fluid {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        min-height: auto !important;
        height: auto !important;
        /* padding은 JavaScript로 동적 설정 */
    }
    
    /* navbar-toggler도 로고 높이에 맞춰 정렬 */
    .navbar.navbar-expand-lg.d-xl-none .navbar-toggler {
        height: auto !important;
        min-height: auto !important;
        align-self: center !important;
        padding: 0.5rem !important;
    }
    
    /* 로고 이미지가 헤더를 넘어가지 않도록 보장 */
    .navbar.navbar-expand-lg.d-xl-none .navbar-brand img {
        box-sizing: border-box !important;
    }
    
    .mobile-menu-overlay.slide-in-left .mobile-menu-close-btn {
        right: 1rem;
    }
    
    .mobile-menu-overlay.slide-in-right .mobile-menu-close-btn {
        left: 1rem;
    }
    
    /* 메뉴 영역 밖 배경 (클릭 시 닫기) */
    .mobile-menu-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: transparent;
        z-index: 9998;
    }
    
    .mobile-menu-backdrop.collapse:not(.show) {
        display: none;
    }
    
    .mobile-menu-backdrop.collapse.show {
        display: block;
    }
    
    /* 메뉴 항목 구분선 */
    .mobile-menu-overlay .nav-item {
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .mobile-menu-overlay .nav-item:first-child {
        border-top: none;
    }
    
    .mobile-menu-overlay .nav-item:last-child {
        border-bottom: none;
    }
    
    /* 하부 메뉴 드롭다운 */
    .mobile-menu-dropdown-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }
    
    .mobile-menu-dropdown-icon {
        transition: transform 0.3s ease;
        font-size: 0.875rem;
    }
    
    .mobile-menu-dropdown-toggle[aria-expanded="true"] .mobile-menu-dropdown-icon {
        transform: rotate(180deg);
    }
    
    .mobile-menu-submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .mobile-menu-submenu.show {
        max-height: none;
    }
    
    .mobile-menu-submenu .navbar-nav {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .mobile-menu-submenu .nav-link {
        padding: 8px 16px !important;
        font-size: 0.875rem !important;
    }
    
    .mobile-menu-overlay .nav-link {
        padding: 0.5rem 1rem;
    }
    
    .mobile-menu-submenu .nav-item {
        border-top: none;
        border-bottom: none;
    }
    
    /* 모바일 메뉴 애니메이션 - 위에서 아래 */
    @keyframes slideInTop {
        from {
            transform: translateY(-100%);
        }
        to {
            transform: translateY(0);
        }
    }
    
    /* 모바일 메뉴 애니메이션 - 왼쪽에서 오른쪽 */
    @keyframes slideInLeft {
        from {
            transform: translateX(-100%);
        }
        to {
            transform: translateX(0);
        }
    }
    
    /* 모바일 메뉴 애니메이션 - 오른쪽에서 왼쪽 */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
        }
        to {
            transform: translateX(0);
        }
    }
    
    /* 모바일 메뉴 애니메이션 - 아래에서 위 */
    @keyframes slideInBottom {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }
    
    .slide-in-top {
        animation: slideInTop 0.3s ease-out;
    }
    
    .slide-in-left {
        animation: slideInLeft 0.3s ease-out;
    }
    
    .slide-in-right {
        animation: slideInRight 0.3s ease-out;
    }
    
    .slide-in-bottom {
        animation: slideInBottom 0.3s ease-out;
    }
    
    /* 모바일 메뉴 내부 스타일 */
    .mobile-menu-overlay .navbar-nav {
        width: 100%;
        padding: 0.5rem;
        margin: 0;
    }
    
    .mobile-menu-overlay .nav-item {
        width: 100%;
        margin-bottom: 0;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .mobile-menu-overlay .nav-item:first-child {
        border-top: none;
    }
    
    .mobile-menu-overlay .nav-item:last-child {
        border-bottom: none;
    }
    
    .mobile-menu-overlay .nav-link {
        display: block;
        padding: 1rem;
        width: 100%;
        text-align: left;
        font-size: 1.1rem;
    }
    
    /* 모바일 메뉴 하단 스크롤 */
    .mobile-header-bottom-menu {
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
        width: 100vw;
        margin-left: calc(-50vw + 50%);
        margin-right: calc(-50vw + 50%);
        padding-left: 0.9375rem;
        padding-right: 0.9375rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .mobile-header-bottom-menu::-webkit-scrollbar {
        display: none;
    }
    
    @if(in_array($theme, ['theme5', 'theme6', 'theme7', 'theme8']))
    /* 하단 메뉴가 있는 테마(5,6,7,8)의 하단 메뉴 스타일 */
    .mobile-header-bottom-menu {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        bottom: 0;
        z-index: 1020;
        background-color: {{ $headerBgColor }};
        padding-left: 0.9375rem;
        padding-right: 0.9375rem;
    }
    @endif
    
    .mobile-header-bottom-menu-item {
        display: inline-block;
        padding: 0.5rem 1rem;
        margin-right: 0.5rem;
        color: {{ $headerTextColor }};
        text-decoration: none;
        white-space: nowrap;
    }
    
    .mobile-header-bottom-menu-item:first-child {
        margin-left: 0;
        padding-left: 0;
    }
    
    .mobile-header-bottom-menu-item:hover {
        color: {{ $pointColor }};
    }
    
    /* 모바일 메뉴 로그인 위젯 너비 100% */
    .mobile-menu-login-widget {
        width: 100% !important;
    }
    
    /* 모바일 메뉴 로그인 위젯 내부 카드 스타일 제거 */
    .mobile-menu-login-widget > div {
        border-top: none !important;
        margin-bottom: 0 !important;
        width: 100% !important;
    }
    
    /* 모바일 메뉴 사용자 드롭다운 버튼 너비 100% */
    #mobileMenuUserDropdown,
    #mobileMenuUserDropdown1 {
        width: 100%;
    }
    
    /* 모바일 메뉴 내부 배너 표시 */
    .mobile-menu-overlay .banner-container,
    .mobile-menu-overlay [class*="banner-container"],
    .mobile-menu-overlay [class*="banner-"] {
        width: 100% !important;
        display: block !important;
        /* margin: 0.5rem 0 !important; */
        /* padding: 0 1rem !important; */
        box-sizing: border-box !important;
        visibility: visible !important;
        opacity: 1 !important;
        overflow: hidden;
    }
    
    /* 모바일 메뉴 배너 특정 위치에 overflow visible 및 height auto 강제 적용 */
    .mobile-menu-overlay .banner-mobile_menu_top,
    .mobile-menu-overlay .banner-mobile_menu_bottom,
    .mobile-menu-overlay [class*="banner-mobile_menu_top"],
    .mobile-menu-overlay [class*="banner-mobile_menu_bottom"] {
        width: 100% !important;
        height: auto !important;
        overflow: visible !important;
        overflow-x: visible !important;
        overflow-y: visible !important;
        display: block !important;
    }
    
    .mobile-menu-overlay .banner-container img,
    .mobile-menu-overlay [class*="banner-"] img {
        max-width: 100% !important;
        width: 100% !important;
        height: auto !important;
        display: block !important;
    }
    
    /* 모바일 메뉴 내부 배너 링크 */
    .mobile-menu-overlay .banner-container a,
    .mobile-menu-overlay [class*="banner-"] a {
        display: block !important;
        width: 100% !important;
    }
</style>

@if($theme === 'theme1')
    {{-- 테마 1: 로고 좌측 메뉴 아이콘 우측 --}}
    <nav class="navbar navbar-expand-lg d-xl-none {{ $headerClass }}" style="{{ $headerStyle }}" data-bg-color="{{ $headerBgColor }}">
        <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
            <a class="navbar-brand" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar1" aria-controls="mobileNavbar1" aria-expanded="false" aria-label="Toggle navigation" style="@if($mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                <i class="bi {{ $mobileMenuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
            </button>
            <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="mobileNavbar1">
                @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                <div class="mobile-menu-logo">
                    <a href="{{ route('home', ['site' => $site->slug ?? 'default']) }}">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                        @endif
                    </a>
                    <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#mobileNavbar1" aria-label="메뉴 닫기">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif
                @if($mobileMenuLoginWidget)
                    @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
                        {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                        <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        </div>
                    @elseif(in_array($mobileMenuDirection, ['top-to-bottom', 'bottom-to-top']))
                        {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                        <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                            @auth
                                <div class="dropdown" style="width: 100%;">
                                    <button class="btn btn-sm dropdown-toggle" type="button" id="mobileMenuUserDropdown1" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                        {{ auth()->user()->nickname ?? auth()->user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileMenuUserDropdown1">
                                        <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">내정보</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}">내 게시글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}">내 댓글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}">저장한 글</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">로그아웃</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                    로그인
                                </a>
                                <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                    회원가입
                                </a>
                            @endauth
                        </div>
                    @endif
                @endif
                {{-- M메뉴상단 배너 (로그인 위젯 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_top" />
                <ul class="navbar-nav ms-auto">
                    @foreach($menus as $menu)
                        <li class="nav-item">
                            @if($menu->children && $menu->children->count() > 0)
                                <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#mobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                    <span>{{ $menu->name }}</span>
                                    <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                </a>
                                <div class="collapse mobile-menu-submenu" id="mobileSubmenu{{ $menu->id }}">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                        </li>
                                        @foreach($menu->children as $child)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ $child->url }}" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                {{-- M메뉴하단 배너 (메뉴 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_bottom" />
            </div>
            @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
            <div class="mobile-menu-backdrop collapse" id="mobileBackdrop1" data-bs-toggle="collapse" data-bs-target="#mobileNavbar1"></div>
            @endif
        </div>
    </nav>

@elseif($theme === 'theme2')
    {{-- 테마 2: 메뉴 아이콘 좌측 로고 우측 --}}
    <nav class="navbar navbar-expand-lg d-xl-none {{ $headerClass }}" style="{{ $headerStyle }}" data-bg-color="{{ $headerBgColor }}">
        <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar2" aria-controls="mobileNavbar2" aria-expanded="false" aria-label="Toggle navigation" style="@if($mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                <i class="bi {{ $mobileMenuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
            </button>
            <a class="navbar-brand ms-auto" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                @endif
            </a>
            <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="mobileNavbar2">
                @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                <div class="mobile-menu-logo">
                    <a href="{{ route('home', ['site' => $site->slug ?? 'default']) }}">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                        @endif
                    </a>
                    <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#mobileNavbar2" aria-label="메뉴 닫기">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif
                @if($mobileMenuLoginWidget)
                    @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
                        {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                        <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        </div>
                    @elseif(in_array($mobileMenuDirection, ['top-to-bottom', 'bottom-to-top']))
                        {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                        <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                            @auth
                                <div class="dropdown" style="width: 100%;">
                                    <button class="btn btn-sm dropdown-toggle" type="button" id="mobileMenuUserDropdown2" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                        {{ auth()->user()->nickname ?? auth()->user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileMenuUserDropdown2">
                                        <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">내정보</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}">내 게시글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}">내 댓글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}">저장한 글</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">로그아웃</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                    로그인
                                </a>
                                <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                    회원가입
                                </a>
                            @endauth
                        </div>
                    @endif
                @endif
                {{-- M메뉴상단 배너 (로그인 위젯 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_top" />
                <ul class="navbar-nav">
                    @foreach($menus as $menu)
                        <li class="nav-item">
                            @if($menu->children && $menu->children->count() > 0)
                                <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#mobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                    <span>{{ $menu->name }}</span>
                                    <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                </a>
                                <div class="collapse mobile-menu-submenu" id="mobileSubmenu{{ $menu->id }}">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                        </li>
                                        @foreach($menu->children as $child)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ $child->url }}" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                {{-- M메뉴하단 배너 (메뉴 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_bottom" />
            </div>
            @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
            <div class="mobile-menu-backdrop collapse" id="mobileBackdrop2" data-bs-toggle="collapse" data-bs-target="#mobileNavbar2"></div>
            @endif
        </div>
    </nav>

@elseif($theme === 'theme3')
    {{-- 테마 3: 로고 중앙 메뉴 아이콘 우측 --}}
    <nav class="navbar navbar-expand-lg d-xl-none" style="{{ $headerStyle }} position: relative;">
        <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
            <a class="navbar-brand" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important; position: absolute; left: 50%; transform: translateX(-50%);">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                @endif
            </a>
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar3" aria-controls="mobileNavbar3" aria-expanded="false" aria-label="Toggle navigation" style="@if($mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                <i class="bi {{ $mobileMenuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
            </button>
            <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="mobileNavbar3">
                @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                <div class="mobile-menu-logo">
                    <a href="{{ route('home', ['site' => $site->slug ?? 'default']) }}">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                        @endif
                    </a>
                    <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#mobileNavbar3" aria-label="메뉴 닫기">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif
                @if($mobileMenuLoginWidget)
                    @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
                        {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                        <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        </div>
                    @elseif(in_array($mobileMenuDirection, ['top-to-bottom', 'bottom-to-top']))
                        {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                        <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                            @auth
                                <div class="dropdown" style="width: 100%;">
                                    <button class="btn btn-sm dropdown-toggle" type="button" id="mobileMenuUserDropdown3" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                        {{ auth()->user()->nickname ?? auth()->user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileMenuUserDropdown3">
                                        <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">내정보</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}">내 게시글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}">내 댓글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}">저장한 글</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">로그아웃</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                    로그인
                                </a>
                                <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                    회원가입
                                </a>
                            @endauth
                        </div>
                    @endif
                @endif
                {{-- M메뉴상단 배너 (로그인 위젯 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_top" />
                <ul class="navbar-nav ms-auto">
                    @foreach($menus as $menu)
                        <li class="nav-item">
                            @if($menu->children && $menu->children->count() > 0)
                                <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#mobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                    <span>{{ $menu->name }}</span>
                                    <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                </a>
                                <div class="collapse mobile-menu-submenu" id="mobileSubmenu{{ $menu->id }}">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                        </li>
                                        @foreach($menu->children as $child)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ $child->url }}" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                {{-- M메뉴하단 배너 (메뉴 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_bottom" />
            </div>
            @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
            <div class="mobile-menu-backdrop collapse" id="mobileBackdrop3" data-bs-toggle="collapse" data-bs-target="#mobileNavbar3"></div>
            @endif
        </div>
    </nav>

@elseif($theme === 'theme4')
    {{-- 테마 4: 로고 중앙 메뉴 아이콘 좌측 --}}
    <nav class="navbar navbar-expand-lg d-xl-none" style="{{ $headerStyle }} position: relative;">
        <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar4" aria-controls="mobileNavbar4" aria-expanded="false" aria-label="Toggle navigation" style="@if($mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                <i class="bi {{ $mobileMenuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
            </button>
            <a class="navbar-brand" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important; position: absolute; left: 50%; transform: translateX(-50%);">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                @endif
            </a>
            <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="mobileNavbar4">
                @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                <div class="mobile-menu-logo">
                    <a href="{{ route('home', ['site' => $site->slug ?? 'default']) }}">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                        @endif
                    </a>
                    <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#mobileNavbar4" aria-label="메뉴 닫기">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif
                @if($mobileMenuLoginWidget)
                    @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
                        {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                        <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        </div>
                    @elseif(in_array($mobileMenuDirection, ['top-to-bottom', 'bottom-to-top']))
                        {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                        <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                            @auth
                                <div class="dropdown" style="width: 100%;">
                                    <button class="btn btn-sm dropdown-toggle" type="button" id="mobileMenuUserDropdown4" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                        {{ auth()->user()->nickname ?? auth()->user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileMenuUserDropdown4">
                                        <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">내정보</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}">내 게시글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}">내 댓글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}">저장한 글</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">로그아웃</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                    로그인
                                </a>
                                <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                    회원가입
                                </a>
                            @endauth
                        </div>
                    @endif
                @endif
                {{-- M메뉴상단 배너 (로그인 위젯 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_top" />
                <ul class="navbar-nav">
                    @foreach($menus as $menu)
                        <li class="nav-item">
                            @if($menu->children && $menu->children->count() > 0)
                                <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#mobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                    <span>{{ $menu->name }}</span>
                                    <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                </a>
                                <div class="collapse mobile-menu-submenu" id="mobileSubmenu{{ $menu->id }}">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                        </li>
                                        @foreach($menu->children as $child)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ $child->url }}" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                {{-- M메뉴하단 배너 (메뉴 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_bottom" />
            </div>
            @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
            <div class="mobile-menu-backdrop collapse" id="mobileBackdrop4" data-bs-toggle="collapse" data-bs-target="#mobileNavbar4"></div>
            @endif
        </div>
    </nav>

@elseif($theme === 'theme5')
    {{-- 테마 5: 로고 중앙 메뉴 아이콘 우측 + 하단 메뉴 --}}
    <nav class="navbar navbar-expand-lg d-xl-none" style="{{ $headerStyle }} position: relative;">
        <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
            <a class="navbar-brand" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important; position: absolute; left: 50%; transform: translateX(-50%);">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                @endif
            </a>
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar5" aria-controls="mobileNavbar5" aria-expanded="false" aria-label="Toggle navigation" style="@if($mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                <i class="bi {{ $mobileMenuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
            </button>
            <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="mobileNavbar5">
                @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                <div class="mobile-menu-logo">
                    <a href="{{ route('home', ['site' => $site->slug ?? 'default']) }}">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                        @endif
                    </a>
                    <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#mobileNavbar5" aria-label="메뉴 닫기">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif
                @if($mobileMenuLoginWidget)
                    @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
                        {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                        <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        </div>
                    @elseif(in_array($mobileMenuDirection, ['top-to-bottom', 'bottom-to-top']))
                        {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                        <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                            @auth
                                <div class="dropdown" style="width: 100%;">
                                    <button class="btn btn-sm dropdown-toggle" type="button" id="mobileMenuUserDropdown5" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                        {{ auth()->user()->nickname ?? auth()->user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileMenuUserDropdown5">
                                        <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">내정보</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}">내 게시글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}">내 댓글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}">저장한 글</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">로그아웃</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                    로그인
                                </a>
                                <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                    회원가입
                                </a>
                            @endauth
                        </div>
                    @endif
                @endif
                {{-- M메뉴상단 배너 (로그인 위젯 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_top" />
                <ul class="navbar-nav ms-auto">
                    @foreach($menus as $menu)
                        <li class="nav-item">
                            @if($menu->children && $menu->children->count() > 0)
                                <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#mobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                    <span>{{ $menu->name }}</span>
                                    <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                </a>
                                <div class="collapse mobile-menu-submenu" id="mobileSubmenu{{ $menu->id }}">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                        </li>
                                        @foreach($menu->children as $child)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ $child->url }}" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                {{-- M메뉴하단 배너 (메뉴 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_bottom" />
            </div>
            @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
            <div class="mobile-menu-backdrop collapse" id="mobileBackdrop5" data-bs-toggle="collapse" data-bs-target="#mobileNavbar5"></div>
            @endif
        </div>
    </nav>
    <div class="mobile-header-bottom-menu d-xl-none" style="background-color: {{ $headerBgColor }}; border-top: none; border-bottom: 3px solid {{ $pointColor }};">
        @foreach($menus as $menu)
            <a href="{{ $menu->url }}" class="mobile-header-bottom-menu-item">{{ $menu->name }}</a>
        @endforeach
    </div>
@elseif($theme === 'theme6')
    {{-- 테마 6: 로고 중앙 메뉴 아이콘 좌측 + 하단 메뉴 --}}
    <nav class="navbar navbar-expand-lg d-xl-none" style="{{ $headerStyle }} position: relative;">
        <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar6" aria-controls="mobileNavbar6" aria-expanded="false" aria-label="Toggle navigation" style="@if($mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                <i class="bi {{ $mobileMenuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
            </button>
            <a class="navbar-brand" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important; position: absolute; left: 50%; transform: translateX(-50%);">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                @endif
            </a>
            <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="mobileNavbar6">
                @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                <div class="mobile-menu-logo">
                    <a href="{{ route('home', ['site' => $site->slug ?? 'default']) }}">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                        @endif
                    </a>
                    <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#mobileNavbar6" aria-label="메뉴 닫기">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif
                @if($mobileMenuLoginWidget)
                    @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
                        {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                        <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        </div>
                    @elseif(in_array($mobileMenuDirection, ['top-to-bottom', 'bottom-to-top']))
                        {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                        <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                            @auth
                                <div class="dropdown" style="width: 100%;">
                                    <button class="btn btn-sm dropdown-toggle" type="button" id="mobileMenuUserDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                        {{ auth()->user()->nickname ?? auth()->user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileMenuUserDropdown">
                                        <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">내정보</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}">내 게시글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}">내 댓글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}">저장한 글</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">로그아웃</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                    로그인
                                </a>
                                <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                    회원가입
                                </a>
                            @endauth
                        </div>
                    @endif
                @endif
                {{-- M메뉴상단 배너 (로그인 위젯 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_top" />
                <ul class="navbar-nav">
                    @foreach($menus as $menu)
                        <li class="nav-item">
                            @if($menu->children && $menu->children->count() > 0)
                                <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#mobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                    <span>{{ $menu->name }}</span>
                                    <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                </a>
                                <div class="collapse mobile-menu-submenu" id="mobileSubmenu{{ $menu->id }}">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                        </li>
                                        @foreach($menu->children as $child)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ $child->url }}" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                {{-- M메뉴하단 배너 (메뉴 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_bottom" />
            </div>
            @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
            <div class="mobile-menu-backdrop collapse" id="mobileBackdrop6" data-bs-toggle="collapse" data-bs-target="#mobileNavbar6"></div>
            @endif
        </div>
    </nav>
    <div class="mobile-header-bottom-menu d-xl-none" style="background-color: {{ $headerBgColor }}; border-top: none; border-bottom: 3px solid {{ $pointColor }};">
        @foreach($menus as $menu)
            <a href="{{ $menu->url }}" class="mobile-header-bottom-menu-item">{{ $menu->name }}</a>
        @endforeach
    </div>
@elseif($theme === 'theme7')
    {{-- 테마 7: 로고 좌측 메뉴 아이콘 우측 + 하단 메뉴 --}}
    <nav class="navbar navbar-expand-lg d-xl-none {{ $headerClass }}" style="{{ $headerStyle }}" data-bg-color="{{ $headerBgColor }}">
        <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
            <a class="navbar-brand" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                @endif
            </a>
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar7" aria-controls="mobileNavbar7" aria-expanded="false" aria-label="Toggle navigation" style="@if($mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                <i class="bi {{ $mobileMenuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
            </button>
            <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="mobileNavbar7">
                @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                <div class="mobile-menu-logo">
                    <a href="{{ route('home', ['site' => $site->slug ?? 'default']) }}">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                        @endif
                    </a>
                    <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#mobileNavbar7" aria-label="메뉴 닫기">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif
                @if($mobileMenuLoginWidget)
                    @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
                        {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                        <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        </div>
                    @elseif(in_array($mobileMenuDirection, ['top-to-bottom', 'bottom-to-top']))
                        {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                        <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                            @auth
                                <div class="dropdown" style="width: 100%;">
                                    <button class="btn btn-sm dropdown-toggle" type="button" id="mobileMenuUserDropdown7" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                        {{ auth()->user()->nickname ?? auth()->user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileMenuUserDropdown7">
                                        <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">내정보</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}">내 게시글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}">내 댓글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}">저장한 글</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">로그아웃</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                    로그인
                                </a>
                                <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                    회원가입
                                </a>
                            @endauth
                        </div>
                    @endif
                @endif
                {{-- M메뉴상단 배너 (로그인 위젯 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_top" />
                <ul class="navbar-nav ms-auto">
                    @foreach($menus as $menu)
                        <li class="nav-item">
                            @if($menu->children && $menu->children->count() > 0)
                                <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#mobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                    <span>{{ $menu->name }}</span>
                                    <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                </a>
                                <div class="collapse mobile-menu-submenu" id="mobileSubmenu{{ $menu->id }}">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                        </li>
                                        @foreach($menu->children as $child)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ $child->url }}" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                {{-- M메뉴하단 배너 (메뉴 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_bottom" />
            </div>
            @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
            <div class="mobile-menu-backdrop collapse" id="mobileBackdrop7" data-bs-toggle="collapse" data-bs-target="#mobileNavbar7"></div>
            @endif
        </div>
    </nav>
    <div class="mobile-header-bottom-menu d-xl-none" style="background-color: {{ $headerBgColor }}; border-top: none; border-bottom: 3px solid {{ $pointColor }};">
        @foreach($menus as $menu)
            <a href="{{ $menu->url }}" class="mobile-header-bottom-menu-item">{{ $menu->name }}</a>
        @endforeach
    </div>
@elseif($theme === 'theme8')
    {{-- 테마 8: 로고 우측 메뉴 아이콘 좌측 + 하단 메뉴 --}}
    <nav class="navbar navbar-expand-lg d-xl-none {{ $headerClass }}" style="{{ $headerStyle }}" data-bg-color="{{ $headerBgColor }}">
        <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar8" aria-controls="mobileNavbar8" aria-expanded="false" aria-label="Toggle navigation" style="@if($mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                <i class="bi {{ $mobileMenuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
            </button>
            @if($mobileMenuLoginWidget && in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
                <div class="mx-2">
                    <x-mobile-login-widget :site="$site" :themeDarkMode="$themeDarkMode" :headerTextColor="$headerTextColor" />
                </div>
            @endif
            <a class="navbar-brand ms-auto" href="{{ route('home', ['site' => $site->slug ?? 'default']) }}" style="color: {{ $headerTextColor }} !important;">
                @if($logoType === 'text' || empty($siteLogo))
                    {{ $siteName }}
                @else
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                @endif
            </a>
            <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="mobileNavbar8">
                @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                <div class="mobile-menu-logo">
                    <a href="{{ route('home', ['site' => $site->slug ?? 'default']) }}">
                        @if($logoType === 'text' || empty($siteLogo))
                            {{ $siteName }}
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; width: auto; height: auto; display: block;">
                        @endif
                    </a>
                    <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#mobileNavbar8" aria-label="메뉴 닫기">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif
                @if($mobileMenuLoginWidget)
                    @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
                        {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                        <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        </div>
                    @elseif(in_array($mobileMenuDirection, ['top-to-bottom', 'bottom-to-top']))
                        {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                        <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                            @auth
                                <div class="dropdown" style="width: 100%;">
                                    <button class="btn btn-sm dropdown-toggle" type="button" id="mobileMenuUserDropdown8" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                        {{ auth()->user()->nickname ?? auth()->user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileMenuUserDropdown8">
                                        <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">내정보</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}">내 게시글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}">내 댓글</a></li>
                                        <li><a class="dropdown-item" href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}">저장한 글</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">로그아웃</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                    로그인
                                </a>
                                <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                    회원가입
                                </a>
                            @endauth
                        </div>
                    @endif
                @endif
                {{-- M메뉴상단 배너 (로그인 위젯 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_top" />
                <ul class="navbar-nav">
                    @foreach($menus as $menu)
                        <li class="nav-item">
                            @if($menu->children && $menu->children->count() > 0)
                                <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#mobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                    <span>{{ $menu->name }}</span>
                                    <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                </a>
                                <div class="collapse mobile-menu-submenu" id="mobileSubmenu{{ $menu->id }}">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                        </li>
                                        @foreach($menu->children as $child)
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ $child->url }}" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a class="nav-link" href="{{ $menu->url }}" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                {{-- M메뉴하단 배너 (메뉴 하단) --}}
                <x-banner-display :site="$site" location="mobile_menu_bottom" />
            </div>
            @if(in_array($mobileMenuDirection, ['left-to-right', 'right-to-left']))
            <div class="mobile-menu-backdrop collapse" id="mobileBackdrop8" data-bs-toggle="collapse" data-bs-target="#mobileNavbar8"></div>
            @endif
        </div>
    </nav>
    <div class="mobile-header-bottom-menu d-xl-none" style="background-color: {{ $headerBgColor }}; border-top: none; border-bottom: 3px solid {{ $pointColor }};">
        @foreach($menus as $menu)
            <a href="{{ $menu->url }}" class="mobile-header-bottom-menu-item">{{ $menu->name }}</a>
        @endforeach
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 좌에서 우 / 우에서 좌 메뉴의 backdrop 표시/숨김 처리
    const menuIds = ['mobileNavbar1', 'mobileNavbar2', 'mobileNavbar3', 'mobileNavbar4', 'mobileNavbar5', 'mobileNavbar6', 'mobileNavbar7', 'mobileNavbar8'];
    
    // 페이지 로드 시 1000px 미만에서 열려있는 모바일 메뉴 강제로 닫기
    function closeAllMobileMenus() {
        menuIds.forEach(function(menuId) {
            const menuElement = document.getElementById(menuId);
            if (menuElement) {
                // show 클래스가 있거나 display가 flex인 경우 강제로 닫기
                const computedStyle = window.getComputedStyle(menuElement);
                const isVisible = menuElement.classList.contains('show') || 
                                 computedStyle.display === 'flex' || 
                                 computedStyle.display === 'block';
                
                if (isVisible) {
                    // Bootstrap Collapse 인스턴스가 있으면 사용
                    const bsCollapse = bootstrap.Collapse.getInstance(menuElement);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    } else {
                        // 직접 닫기
                        menuElement.classList.remove('show');
                        menuElement.style.setProperty('display', 'none', 'important');
                    }
                }
            }
        });
        
        // backdrop도 닫기
        const backdrops = document.querySelectorAll('.mobile-menu-backdrop');
        backdrops.forEach(function(backdrop) {
            backdrop.classList.remove('show');
            backdrop.style.setProperty('display', 'none', 'important');
        });
    }
    
    // 페이지 로드 시 즉시 실행 (여러 번 실행)
    closeAllMobileMenus();
    setTimeout(closeAllMobileMenus, 50);
    setTimeout(closeAllMobileMenus, 100);
    setTimeout(closeAllMobileMenus, 200);
    
    // 1000px 이상에서는 모바일 메뉴 토글 버튼 비활성화
    document.querySelectorAll('.navbar-toggler[data-bs-toggle="collapse"]').forEach(function(toggler) {
        toggler.addEventListener('click', function(e) {
            if (window.innerWidth >= 1000) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    });
    
    menuIds.forEach(function(menuId) {
        const menuElement = document.getElementById(menuId);
        const backdropId = menuId.replace('mobileNavbar', 'mobileBackdrop');
        const backdropElement = document.getElementById(backdropId);
        
        if (menuElement && backdropElement) {
            // 1000px 이상에서는 모바일 메뉴가 열리지 않도록 방지
            menuElement.addEventListener('show.bs.collapse', function(e) {
                if (window.innerWidth >= 1000) {
                    e.preventDefault();
                    e.stopPropagation();
                    // 메뉴 강제로 닫기
                    const bsCollapse = bootstrap.Collapse.getInstance(menuElement);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                    return false;
                }
                backdropElement.classList.add('show');
            });
            
            // 동시에 닫히도록 수정 (애니메이션 없이)
            menuElement.addEventListener('hide.bs.collapse', function() {
                backdropElement.classList.remove('show');
                backdropElement.style.transition = 'none';
                setTimeout(function() {
                    backdropElement.style.transition = '';
                }, 0);
            });
            
            // X 버튼 클릭 시 동시에 닫기
            const closeBtn = menuElement.querySelector('.mobile-menu-close-btn');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // 메뉴와 backdrop을 동시에 닫기
                    const bsCollapse = new bootstrap.Collapse(menuElement, { toggle: false });
                    bsCollapse.hide();
                    backdropElement.style.transition = 'none';
                    backdropElement.classList.remove('show');
                    setTimeout(function() {
                        backdropElement.style.transition = '';
                    }, 0);
                });
            }
            
            // Backdrop 클릭 시 동시에 닫기
            if (backdropElement) {
                backdropElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // 메뉴와 backdrop을 동시에 닫기
                    const bsCollapse = new bootstrap.Collapse(menuElement, { toggle: false });
                    bsCollapse.hide();
                    backdropElement.style.transition = 'none';
                    backdropElement.classList.remove('show');
                    setTimeout(function() {
                        backdropElement.style.transition = '';
                    }, 0);
                });
            }
        }
    });
    
    // 모바일 헤더 높이를 로고 높이에 맞춰 동적 조정 (텍스트/이미지 모두 처리)
    function adjustMobileHeaderHeight() {
        const mobileHeaders = document.querySelectorAll('.navbar.navbar-expand-lg.d-xl-none');
        mobileHeaders.forEach(function(header) {
            const navbarBrand = header.querySelector('.navbar-brand');
            if (!navbarBrand) return;
            
            const containerFluid = header.querySelector('.container-fluid');
            if (!containerFluid) return;
            
            // 로고 높이 측정 (이미지 또는 텍스트)
            let logoHeight = 0;
            const logoImg = navbarBrand.querySelector('img');
            
            function measureAndAdjust() {
                if (logoImg) {
                    // 이미지 로고인 경우 - 실제 렌더링된 높이 측정
                    logoHeight = logoImg.offsetHeight || logoImg.naturalHeight;
                } else {
                    // 텍스트 로고인 경우
                    logoHeight = navbarBrand.offsetHeight;
                }
                
                if (logoHeight > 0) {
                    adjustHeaderHeight(header, containerFluid, logoHeight);
                }
            }
            
            if (logoImg) {
                // 이미지 로고인 경우
                if (logoImg.complete && logoImg.naturalHeight !== 0) {
                    // 이미 로드된 경우
                    setTimeout(measureAndAdjust, 50);
                } else {
                    // 로드 대기
                    logoImg.addEventListener('load', function() {
                        setTimeout(measureAndAdjust, 50);
                    }, { once: true });
                }
                // 이미지 크기 변경 시에도 재조정
                const observer = new ResizeObserver(function() {
                    setTimeout(measureAndAdjust, 50);
                });
                observer.observe(logoImg);
            } else {
                // 텍스트 로고인 경우
                setTimeout(measureAndAdjust, 50);
            }
        });
    }
    
    function adjustHeaderHeight(header, containerFluid, logoHeight) {
        if (logoHeight <= 0) return;
        
        // PC 버전처럼 단순하게: 로고 높이에 맞춰 헤더 높이 설정
        // container-fluid에 패딩 추가 (로고 높이의 8-12%, 최소 0.4rem, 최대 2rem)
        let padding = 0;
        if (logoHeight <= 40) {
            padding = 0;
        } else if (logoHeight <= 80) {
            padding = Math.max(logoHeight * 0.008, 0.4);
        } else {
            padding = Math.min(Math.max(logoHeight * 0.012, 0.8), 2);
        }
        
        const paddingPx = padding * 16; // rem을 px로 변환 (1rem = 16px)
        const totalHeight = logoHeight + (paddingPx * 2);
        
        // container-fluid 패딩 설정 (CSS !important 우회를 위해 setProperty 사용)
        containerFluid.style.setProperty('padding-top', padding + 'rem', 'important');
        containerFluid.style.setProperty('padding-bottom', padding + 'rem', 'important');
        
        // 헤더 높이를 로고 높이 + 패딩에 맞춤 (PC 버전처럼)
        header.style.setProperty('min-height', totalHeight + 'px', 'important');
        header.style.setProperty('height', totalHeight + 'px', 'important');
        header.style.setProperty('max-height', 'none', 'important');
    }
    
    // 즉시 실행 (DOM이 이미 로드된 경우)
    adjustMobileHeaderHeight();
    
    // DOMContentLoaded 후에도 실행
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(adjustMobileHeaderHeight, 100);
        });
    } else {
        // 이미 로드된 경우 즉시 실행
        setTimeout(adjustMobileHeaderHeight, 100);
    }
    
    // 모든 이미지 로드 완료 후 재조정
    window.addEventListener('load', function() {
        setTimeout(adjustMobileHeaderHeight, 200);
    });
    
    // 리사이즈 시에도 재조정 및 모바일 메뉴 자동 닫기
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            adjustMobileHeaderHeight();
            
            // 1000px 이상에서는 모바일 메뉴 강제로 닫기
            if (window.innerWidth >= 1000) {
                menuIds.forEach(function(menuId) {
                    const menuElement = document.getElementById(menuId);
                    if (menuElement) {
                        const bsCollapse = bootstrap.Collapse.getInstance(menuElement);
                        if (bsCollapse && menuElement.classList.contains('show')) {
                            bsCollapse.hide();
                        } else if (menuElement.classList.contains('show')) {
                            menuElement.classList.remove('show');
                            menuElement.style.display = 'none';
                        }
                    }
                });
                
                // backdrop도 닫기
                const backdrops = document.querySelectorAll('.mobile-menu-backdrop');
                backdrops.forEach(function(backdrop) {
                    backdrop.classList.remove('show');
                });
            } else {
                // 1000px 미만에서도 페이지 로드 시 열려있던 메뉴 닫기
                closeAllMobileMenus();
            }
        }, 250);
    });
    
    // MutationObserver로 DOM 변경 감지
    const observer = new MutationObserver(function(mutations) {
        setTimeout(adjustMobileHeaderHeight, 100);
    });
    
    // sticky-header-wrapper 감시
    const stickyWrapper = document.querySelector('.sticky-header-wrapper');
    if (stickyWrapper) {
        observer.observe(stickyWrapper, {
            childList: true,
            subtree: true,
            attributes: true
        });
    }
});
</script>

