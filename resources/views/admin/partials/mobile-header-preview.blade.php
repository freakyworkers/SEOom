<style>
    .mobile-header-preview-wrapper {
        width: 375px;
        max-width: 375px;
        height: 667px;
        max-height: 667px;
        overflow: hidden;
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #ffffff;
        margin: 0 auto;
        isolation: isolate; /* 새로운 stacking context 생성 */
    }
    
    .mobile-header-preview-wrapper .navbar {
        position: relative;
        z-index: 1;
        min-height: auto;
        padding: 0.5rem 0.75rem;
    }
    
    .mobile-header-preview-wrapper .navbar-brand {
        font-size: 1.25rem;
        font-weight: bold;
    }
    
    .mobile-header-preview-wrapper .navbar-brand img {
        height: auto;
        width: auto;
        display: block;
        max-width: {{ $logoMobileSize }}px;
    }
    
    .mobile-header-preview-wrapper .navbar-toggler {
        padding: 0.25rem 0.5rem;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* 메뉴 버튼 아이콘에 테두리가 없는 경우 좌우 패딩 제거 */
    .mobile-header-preview-wrapper .navbar-toggler[style*="border: none"] {
        padding-left: 0;
        padding-right: 0;
    }
    
    /* 로고 좌측 정렬인 경우 왼쪽 마진 제거 */
    .mobile-header-preview-wrapper .navbar .navbar-brand:not(.ms-auto):not(.mx-auto) {
        margin-left: 0;
    }
    
    /* 로고 우측 정렬인 경우 우측 마진 제거 */
    .mobile-header-preview-wrapper .navbar .navbar-brand.ms-auto {
        margin-right: 0;
    }
    
    .mobile-header-preview-wrapper .navbar-toggler i {
        display: inline-block;
        line-height: 1;
    }
    
    .mobile-header-preview-wrapper .nav-link {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }
    
    .mobile-header-preview-wrapper .mobile-header-bottom-menu {
        font-size: 0.75rem;
        padding: 0.5rem 0;
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }
    
    @if(isset($theme) && in_array($theme, ['theme5', 'theme6', 'theme7', 'theme8']))
    /* 하단 메뉴가 있는 테마(5,6,7,8)의 하단 메뉴 스타일 */
    .mobile-header-preview-wrapper .mobile-header-bottom-menu {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        bottom: 0;
        z-index: 1020;
        background-color: {{ $headerBgColor }};
        padding-left: 0.9375rem;
        padding-right: 0.9375rem;
    }
    @endif
    
    .mobile-header-preview-wrapper .mobile-header-bottom-menu-item {
        font-size: 0.75rem;
        padding: 0.5rem 1rem;
    }
    
    .mobile-header-preview-wrapper .mobile-header-bottom-menu-item:first-child {
        margin-left: 0;
        padding-left: 0;
    }
    
    /* wrapper 내에서만 오버레이가 표시되도록 */
    .mobile-header-preview-wrapper {
        contain: layout style paint;
        position: relative;
        overflow: hidden !important;
        clip-path: inset(0);
    }
    
    /* navbar를 wrapper 기준으로 위치시키기 */
    .mobile-header-preview-wrapper .navbar {
        position: static !important;
        z-index: 1;
    }
    
    /* 모바일 메뉴 오버레이 스타일 - wrapper 전체 영역 사용 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        z-index: 9999;
        background-color: {{ $headerBgColor }};
        padding: 0 !important;
        margin: 0 !important;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex !important;
        flex-direction: column !important;
    }
    
    /* wrapper 밖으로 나가지 않도록 */
    .mobile-header-preview-wrapper * {
        max-width: 100%;
    }
    
    /* 메뉴 오버레이가 wrapper 밖으로 나가지 않도록 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay {
        clip-path: inset(0);
    }
    
    /* 메뉴 오버레이가 wrapper 밖으로 나가지 않도록 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay {
        clip-path: inset(0);
    }
    
    
    /* 메뉴가 기본적으로 닫혀있도록 */
    .mobile-header-preview-wrapper .navbar-collapse.collapse:not(.show) {
        display: none !important;
    }
    
    /* 위에서 아래 / 아래에서 위 메뉴는 50% 높이만 사용 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-top,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-bottom {
        height: 50% !important;
        max-height: 50% !important;
    }
    
    /* 위에서 아래 메뉴는 상단에 위치 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-top {
        bottom: auto !important;
        top: 0 !important;
        border-bottom: 3px solid {{ $pointColor }};
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* 아래에서 위 메뉴는 하단에 위치 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-bottom {
        top: auto !important;
        bottom: 0 !important;
        border-top: 3px solid {{ $pointColor }};
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* 좌에서 우 / 우에서 좌 메뉴는 85% 너비만 사용 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-left,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-right {
        width: 85% !important;
        height: 100% !important;
    }
    
    /* 좌에서 우 메뉴는 왼쪽에서 시작 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-left {
        right: auto !important;
        left: 0 !important;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
        @if(isset($headerBorder) && $headerBorder)
        border-right: {{ $headerBorderWidth }}px solid {{ $headerBorderColor }};
        @endif
    }
    
    /* 우에서 좌 메뉴는 오른쪽에서 시작 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-right {
        left: auto !important;
        right: 0 !important;
        box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
        @if(isset($headerBorder) && $headerBorder)
        border-left: {{ $headerBorderWidth }}px solid {{ $headerBorderColor }};
        @endif
    }
    
    /* 모바일 메뉴 닫기 버튼 */
    .mobile-header-preview-wrapper .mobile-menu-close-btn {
        position: absolute;
        top: 0.5rem;
        z-index: 10000;
        background: transparent;
        border: none;
        font-size: calc(1.5rem * 0.8);
        color: {{ $headerTextColor }};
        cursor: pointer;
        padding: calc(0.5rem * 0.8);
        line-height: 1;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-overlay.slide-in-left .mobile-menu-close-btn {
        right: 1rem;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-overlay.slide-in-right .mobile-menu-close-btn {
        left: 1rem;
    }
    
    /* 좌에서 우 / 우에서 좌 메뉴의 로고 영역 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-left .mobile-menu-logo,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-right .mobile-menu-logo {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        order: -1;
        flex-shrink: 0;
    }
    
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-left .mobile-menu-logo a,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-right .mobile-menu-logo a {
        color: {{ $headerTextColor }} !important;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.25rem;
    }
    
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-left .mobile-menu-logo img,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-right .mobile-menu-logo img {
        max-width: 150px;
        height: auto;
    }
    
    /* 좌에서 우 / 우에서 좌 메뉴의 X 버튼 위치 조정 - 로고와 같은 줄에 세로 중앙 정렬 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-left .mobile-menu-close-btn,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-right .mobile-menu-close-btn {
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
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-left .navbar-nav,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-right .navbar-nav {
        padding-top: 0;
    }
    
    /* 위에서 아래 / 아래에서 위 메뉴의 로고 영역 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-top .mobile-menu-logo,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-bottom .mobile-menu-logo {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        order: -1;
        flex-shrink: 0;
    }
    
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-top .mobile-menu-logo a,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-bottom .mobile-menu-logo a {
        color: {{ $headerTextColor }} !important;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.25rem;
    }
    
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-top .mobile-menu-logo img,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-bottom .mobile-menu-logo img {
        max-width: 150px;
        height: auto;
    }
    
    /* 위에서 아래 / 아래에서 위 메뉴의 X 버튼 위치 조정 - 로고와 같은 줄에 세로 중앙 정렬 */
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-top .mobile-menu-close-btn,
    .mobile-header-preview-wrapper .navbar-collapse.mobile-menu-overlay.slide-in-bottom .mobile-menu-close-btn {
        position: static;
        margin-left: auto;
        background: none;
        border: none;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* 메뉴 영역 밖 배경 제거 */
    .mobile-header-preview-wrapper .mobile-menu-backdrop {
        display: none !important;
    }
    
    /* 하부 메뉴 드롭다운 */
    .mobile-header-preview-wrapper .mobile-menu-dropdown-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-dropdown-icon {
        transition: transform 0.3s ease;
        font-size: calc(0.875rem * 0.6);
    }
    
    .mobile-header-preview-wrapper .mobile-menu-dropdown-toggle[aria-expanded="true"] .mobile-menu-dropdown-icon {
        transform: rotate(180deg);
    }
    
    .mobile-header-preview-wrapper .mobile-menu-submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .mobile-header-preview-wrapper .mobile-menu-submenu.show {
        max-height: none;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-submenu .navbar-nav {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-submenu .nav-link {
        padding: 8px 16px !important;
        font-size: 0.875rem !important;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-overlay .nav-link {
        padding: 0.5rem 1rem;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-submenu .nav-item {
        border-top: none;
        border-bottom: none;
    }
    
    @keyframes slideInTop {
        from { transform: translateY(-100%); }
        to { transform: translateY(0); }
    }
    @keyframes slideInLeft {
        from { transform: translateX(-100%); }
        to { transform: translateX(0); }
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
    }
    @keyframes slideInBottom {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    
    .slide-in-top { animation: slideInTop 0.3s ease-out; }
    .slide-in-left { animation: slideInLeft 0.3s ease-out; }
    .slide-in-right { animation: slideInRight 0.3s ease-out; }
    .slide-in-bottom { animation: slideInBottom 0.3s ease-out; }
    
    /* 모바일 메뉴 내부 스타일 */
    .mobile-header-preview-wrapper .mobile-menu-overlay .navbar-nav {
        width: 100%;
        padding: 0.5rem;
        margin: 0;
        flex-direction: column !important;
        display: flex !important;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-overlay .nav-item {
        width: 100%;
        margin-bottom: 0;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: block;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-overlay .nav-item:first-child {
        border-top: none;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-overlay .nav-item:last-child {
        border-bottom: none;
    }
    
    .mobile-header-preview-wrapper .mobile-menu-overlay .nav-link {
        display: block;
        padding: 1rem;
        width: 100%;
        text-align: left;
        font-size: 1rem;
    }
    
    /* 모바일 메뉴 로그인 위젯 내부 카드 스타일 제거 */
    .mobile-header-preview-wrapper .mobile-menu-login-widget > div {
        border-top: none !important;
        margin-bottom: 0 !important;
    }
    
    /* 모바일 메뉴 사용자 드롭다운 버튼 너비 100% */
    .mobile-header-preview-wrapper #previewMobileMenuUserDropdown {
        width: 100%;
    }
</style>

<div class="mobile-header-preview-wrapper">
    @if($theme === 'theme1')
        <nav class="navbar navbar-expand-lg" style="{{ $headerStyle }}">
            <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
                <a class="navbar-brand" href="#" style="color: {{ $headerTextColor }} !important;">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                    @endif
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar1" aria-controls="previewMobileNavbar1" aria-expanded="false" aria-label="Toggle navigation" style="@if(isset($mobileMenuIconBorder) && $mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                    <i class="bi {{ $menuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
                </button>
                <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="previewMobileNavbar1">
                    @if(in_array($menuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                    <div class="mobile-menu-logo">
                        <a href="#">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                            @endif
                        </a>
                        <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar1" aria-label="메뉴 닫기">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @endif
                    @if(isset($mobileMenuLoginWidget) && $mobileMenuLoginWidget)
                        @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                            {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                            <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                            </div>
                        @elseif(in_array($menuDirection, ['top-to-bottom', 'bottom-to-top']))
                            {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                            <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                                @auth
                                    <div class="dropdown" style="width: 100%;">
                                        <button class="btn btn-sm dropdown-toggle" type="button" id="previewMobileMenuUserDropdown1" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                            {{ auth()->user()->name }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="previewMobileMenuUserDropdown1">
                                            <li><a class="dropdown-item" href="#">내정보</a></li>
                                            <li><a class="dropdown-item" href="#">내 게시글</a></li>
                                            <li><a class="dropdown-item" href="#">내 댓글</a></li>
                                            <li><a class="dropdown-item" href="#">저장한 글</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="#" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">로그아웃</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                        로그인
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                        회원가입
                                    </a>
                                @endauth
                            </div>
                        @endif
                    @endif
                    <ul class="navbar-nav ms-auto">
                        @forelse($menus as $menu)
                            <li class="nav-item">
                                @if($menu->children && $menu->children->count() > 0)
                                    <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#previewMobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                        <span>{{ $menu->name }}</span>
                                        <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                    </a>
                                    <div class="collapse mobile-menu-submenu" id="previewMobileSubmenu{{ $menu->id }}">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                            </li>
                                            @foreach($menu->children as $child)
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </nav>
    @elseif($theme === 'theme2')
        <nav class="navbar navbar-expand-lg" style="{{ $headerStyle }}">
            <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar2" aria-controls="previewMobileNavbar2" aria-expanded="false" aria-label="Toggle navigation" style="@if(isset($mobileMenuIconBorder) && $mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                    <i class="bi {{ $menuIcon }}" style="color: {{ $headerTextColor }}; font-size: calc(1.5rem * 0.6);"></i>
                </button>
                <a class="navbar-brand ms-auto" href="#" style="color: {{ $headerTextColor }} !important;">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                    @endif
                </a>
                <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="previewMobileNavbar2">
                    @if(in_array($menuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                    <div class="mobile-menu-logo">
                        <a href="#">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                            @endif
                        </a>
                        <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar2" aria-label="메뉴 닫기">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @endif
                    @if(isset($mobileMenuLoginWidget) && $mobileMenuLoginWidget)
                        @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                            {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                            <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                            </div>
                        @elseif(in_array($menuDirection, ['top-to-bottom', 'bottom-to-top']))
                            {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                            <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                                @auth
                                    <div class="dropdown" style="width: 100%;">
                                        <button class="btn btn-sm dropdown-toggle" type="button" id="previewMobileMenuUserDropdown2" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                            {{ auth()->user()->name }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="previewMobileMenuUserDropdown2">
                                            <li><a class="dropdown-item" href="#">내정보</a></li>
                                            <li><a class="dropdown-item" href="#">내 게시글</a></li>
                                            <li><a class="dropdown-item" href="#">내 댓글</a></li>
                                            <li><a class="dropdown-item" href="#">저장한 글</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="#" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">로그아웃</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                        로그인
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                        회원가입
                                    </a>
                                @endauth
                            </div>
                        @endif
                    @endif
                    <ul class="navbar-nav ms-auto">
                        @foreach($menus as $menu)
                            <li class="nav-item">
                                @if($menu->children && $menu->children->count() > 0)
                                    <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#previewMobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                        <span>{{ $menu->name }}</span>
                                        <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                    </a>
                                    <div class="collapse mobile-menu-submenu" id="previewMobileSubmenu{{ $menu->id }}">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                            </li>
                                            @foreach($menu->children as $child)
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                <div class="mobile-menu-backdrop collapse" id="previewMobileBackdrop2" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar2"></div>
                @endif
            </div>
        </nav>
    @elseif($theme === 'theme3')
        <nav class="navbar navbar-expand-lg" style="{{ $headerStyle }} position: relative;">
            <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
                <a class="navbar-brand" href="#" style="color: {{ $headerTextColor }} !important; position: absolute; left: 50%; transform: translateX(-50%);">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                    @endif
                </a>
                <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar3" aria-controls="previewMobileNavbar3" aria-expanded="false" aria-label="Toggle navigation" style="@if(isset($mobileMenuIconBorder) && $mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                    <i class="bi {{ $menuIcon }}" style="color: {{ $headerTextColor }}; font-size: calc(1.5rem * 0.6);"></i>
                </button>
                <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="previewMobileNavbar3">
                    @if(in_array($menuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                    <div class="mobile-menu-logo">
                        <a href="#">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                            @endif
                        </a>
                        <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar3" aria-label="메뉴 닫기">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @endif
                    @if(isset($mobileMenuLoginWidget) && $mobileMenuLoginWidget)
                        @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                            {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                            <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                            </div>
                        @elseif(in_array($menuDirection, ['top-to-bottom', 'bottom-to-top']))
                            {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                            <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                                @auth
                                    <div class="dropdown" style="width: 100%;">
                                        <button class="btn btn-sm dropdown-toggle" type="button" id="previewMobileMenuUserDropdown3" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                            {{ auth()->user()->name }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="previewMobileMenuUserDropdown3">
                                            <li><a class="dropdown-item" href="#">내정보</a></li>
                                            <li><a class="dropdown-item" href="#">내 게시글</a></li>
                                            <li><a class="dropdown-item" href="#">내 댓글</a></li>
                                            <li><a class="dropdown-item" href="#">저장한 글</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="#" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">로그아웃</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                        로그인
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                        회원가입
                                    </a>
                                @endauth
                            </div>
                        @endif
                    @endif
                    <ul class="navbar-nav ms-auto">
                        @foreach($menus as $menu)
                            <li class="nav-item">
                                @if($menu->children && $menu->children->count() > 0)
                                    <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#previewMobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                        <span>{{ $menu->name }}</span>
                                        <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                    </a>
                                    <div class="collapse mobile-menu-submenu" id="previewMobileSubmenu{{ $menu->id }}">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                            </li>
                                            @foreach($menu->children as $child)
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                <div class="mobile-menu-backdrop collapse" id="previewMobileBackdrop3" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar3"></div>
                @endif
            </div>
        </nav>
    @elseif($theme === 'theme4')
        <nav class="navbar navbar-expand-lg" style="{{ $headerStyle }} position: relative;">
            <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar4" aria-controls="previewMobileNavbar4" aria-expanded="false" aria-label="Toggle navigation" style="@if(isset($mobileMenuIconBorder) && $mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                    <i class="bi {{ $menuIcon }}" style="color: {{ $headerTextColor }}; font-size: calc(1.5rem * 0.6);"></i>
                </button>
                <a class="navbar-brand" href="#" style="color: {{ $headerTextColor }} !important; position: absolute; left: 50%; transform: translateX(-50%);">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                    @endif
                </a>
                <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="previewMobileNavbar4">
                    @if(in_array($menuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                    <div class="mobile-menu-logo">
                        <a href="#">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                            @endif
                        </a>
                        <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar4" aria-label="메뉴 닫기">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @endif
                    @if(isset($mobileMenuLoginWidget) && $mobileMenuLoginWidget)
                        @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                            {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                            <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                            </div>
                        @elseif(in_array($menuDirection, ['top-to-bottom', 'bottom-to-top']))
                            {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                            <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                                @auth
                                    <div class="dropdown" style="width: 100%;">
                                        <button class="btn btn-sm dropdown-toggle" type="button" id="previewMobileMenuUserDropdown4" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                            {{ auth()->user()->name }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="previewMobileMenuUserDropdown4">
                                            <li><a class="dropdown-item" href="#">내정보</a></li>
                                            <li><a class="dropdown-item" href="#">내 게시글</a></li>
                                            <li><a class="dropdown-item" href="#">내 댓글</a></li>
                                            <li><a class="dropdown-item" href="#">저장한 글</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="#" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">로그아웃</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                        로그인
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                        회원가입
                                    </a>
                                @endauth
                            </div>
                        @endif
                    @endif
                    <ul class="navbar-nav ms-auto">
                        @foreach($menus as $menu)
                            <li class="nav-item">
                                @if($menu->children && $menu->children->count() > 0)
                                    <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#previewMobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                        <span>{{ $menu->name }}</span>
                                        <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                    </a>
                                    <div class="collapse mobile-menu-submenu" id="previewMobileSubmenu{{ $menu->id }}">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                            </li>
                                            @foreach($menu->children as $child)
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                <div class="mobile-menu-backdrop collapse" id="previewMobileBackdrop4" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar4"></div>
                @endif
            </div>
        </nav>
    @elseif($theme === 'theme5')
        <nav class="navbar navbar-expand-lg" style="{{ $headerStyle }} position: relative;">
            <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
                <a class="navbar-brand" href="#" style="color: {{ $headerTextColor }} !important; position: absolute; left: 50%; transform: translateX(-50%);">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                    @endif
                </a>
                <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar5" aria-controls="previewMobileNavbar5" aria-expanded="false" aria-label="Toggle navigation" style="@if(isset($mobileMenuIconBorder) && $mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                    <i class="bi {{ $menuIcon }}" style="color: {{ $headerTextColor }}; font-size: calc(1.5rem * 0.6);"></i>
                </button>
                <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="previewMobileNavbar5">
                    @if(in_array($menuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                    <div class="mobile-menu-logo">
                        <a href="#">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                            @endif
                        </a>
                        <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar5" aria-label="메뉴 닫기">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @endif
                    @if(isset($mobileMenuLoginWidget) && $mobileMenuLoginWidget)
                        @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                            {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                            <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                            </div>
                        @elseif(in_array($menuDirection, ['top-to-bottom', 'bottom-to-top']))
                            {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                            <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                                @auth
                                    <div class="dropdown" style="width: 100%;">
                                        <button class="btn btn-sm dropdown-toggle" type="button" id="previewMobileMenuUserDropdown5" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                            {{ auth()->user()->name }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="previewMobileMenuUserDropdown5">
                                            <li><a class="dropdown-item" href="#">내정보</a></li>
                                            <li><a class="dropdown-item" href="#">내 게시글</a></li>
                                            <li><a class="dropdown-item" href="#">내 댓글</a></li>
                                            <li><a class="dropdown-item" href="#">저장한 글</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="#" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">로그아웃</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                        로그인
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                        회원가입
                                    </a>
                                @endauth
                            </div>
                        @endif
                    @endif
                    <ul class="navbar-nav ms-auto">
                        @foreach($menus as $menu)
                            <li class="nav-item">
                                @if($menu->children && $menu->children->count() > 0)
                                    <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#previewMobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                        <span>{{ $menu->name }}</span>
                                        <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                    </a>
                                    <div class="collapse mobile-menu-submenu" id="previewMobileSubmenu{{ $menu->id }}">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                            </li>
                                            @foreach($menu->children as $child)
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                <div class="mobile-menu-backdrop collapse" id="previewMobileBackdrop5" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar5"></div>
                @endif
            </div>
        </nav>
        <div class="mobile-header-bottom-menu" style="background-color: {{ $headerBgColor }}; border-top: none; border-bottom: 3px solid {{ $pointColor }}; overflow-x: auto; white-space: nowrap;">
            @foreach($menus as $menu)
                <a href="#" class="mobile-header-bottom-menu-item" style="color: {{ $headerTextColor }}; text-decoration: none; display: inline-block; padding: 0.5rem 1rem;">{{ $menu->name }}</a>
            @endforeach
        </div>
    @elseif($theme === 'theme6')
        <nav class="navbar navbar-expand-lg" style="{{ $headerStyle }} position: relative;">
            <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar6" aria-controls="previewMobileNavbar6" aria-expanded="false" aria-label="Toggle navigation" style="@if(isset($mobileMenuIconBorder) && $mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                    <i class="bi {{ $menuIcon }}" style="color: {{ $headerTextColor }}; font-size: calc(1.5rem * 0.6);"></i>
                </button>
                <a class="navbar-brand" href="#" style="color: {{ $headerTextColor }} !important; position: absolute; left: 50%; transform: translateX(-50%);">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                    @endif
                </a>
                <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="previewMobileNavbar6">
                    @if(in_array($menuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                    <div class="mobile-menu-logo">
                        <a href="#">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                            @endif
                        </a>
                        <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar6" aria-label="메뉴 닫기">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @endif
                    @if(isset($mobileMenuLoginWidget) && $mobileMenuLoginWidget)
                        @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                            {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                            <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                            </div>
                        @elseif(in_array($menuDirection, ['top-to-bottom', 'bottom-to-top']))
                            {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                            <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                                @auth
                                    <div class="dropdown" style="width: 100%;">
                                        <button class="btn btn-sm dropdown-toggle" type="button" id="previewMobileMenuUserDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                            {{ auth()->user()->name }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="previewMobileMenuUserDropdown">
                                            <li><a class="dropdown-item" href="#">내정보</a></li>
                                            <li><a class="dropdown-item" href="#">내 게시글</a></li>
                                            <li><a class="dropdown-item" href="#">내 댓글</a></li>
                                            <li><a class="dropdown-item" href="#">저장한 글</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#">로그아웃</a></li>
                                        </ul>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                        로그인
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                        회원가입
                                    </a>
                                @endauth
                            </div>
                        @endif
                    @endif
                    <ul class="navbar-nav ms-auto">
                        @foreach($menus as $menu)
                            <li class="nav-item">
                                @if($menu->children && $menu->children->count() > 0)
                                    <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#previewMobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                        <span>{{ $menu->name }}</span>
                                        <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                    </a>
                                    <div class="collapse mobile-menu-submenu" id="previewMobileSubmenu{{ $menu->id }}">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                            </li>
                                            @foreach($menu->children as $child)
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                <div class="mobile-menu-backdrop collapse" id="previewMobileBackdrop6" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar6"></div>
                @endif
            </div>
        </nav>
        <div class="mobile-header-bottom-menu" style="background-color: {{ $headerBgColor }}; border-top: none; border-bottom: 3px solid {{ $pointColor }}; overflow-x: auto; white-space: nowrap;">
            @foreach($menus as $menu)
                <a href="#" class="mobile-header-bottom-menu-item" style="color: {{ $headerTextColor }}; text-decoration: none; display: inline-block; padding: 0.5rem 1rem;">{{ $menu->name }}</a>
            @endforeach
        </div>
    @elseif($theme === 'theme7')
        <nav class="navbar navbar-expand-lg" style="{{ $headerStyle }}">
            <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
                <a class="navbar-brand" href="#" style="color: {{ $headerTextColor }} !important;">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                    @endif
                </a>
                <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar7" aria-controls="previewMobileNavbar7" aria-expanded="false" aria-label="Toggle navigation" style="@if(isset($mobileMenuIconBorder) && $mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                    <i class="bi {{ $menuIcon }}" style="color: {{ $headerTextColor }}; font-size: calc(1.5rem * 0.6);"></i>
                </button>
                <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="previewMobileNavbar7">
                    @if(in_array($menuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                    <div class="mobile-menu-logo">
                        <a href="#">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                            @endif
                        </a>
                        <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar7" aria-label="메뉴 닫기">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @endif
                    @if(isset($mobileMenuLoginWidget) && $mobileMenuLoginWidget)
                        @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                            {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                            <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                            </div>
                        @elseif(in_array($menuDirection, ['top-to-bottom', 'bottom-to-top']))
                            {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                            <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                                @auth
                                    <div class="dropdown" style="width: 100%;">
                                        <button class="btn btn-sm dropdown-toggle" type="button" id="previewMobileMenuUserDropdown7" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                            {{ auth()->user()->name }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="previewMobileMenuUserDropdown7">
                                            <li><a class="dropdown-item" href="#">내정보</a></li>
                                            <li><a class="dropdown-item" href="#">내 게시글</a></li>
                                            <li><a class="dropdown-item" href="#">내 댓글</a></li>
                                            <li><a class="dropdown-item" href="#">저장한 글</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="#" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">로그아웃</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                        로그인
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                        회원가입
                                    </a>
                                @endauth
                            </div>
                        @endif
                    @endif
                    <ul class="navbar-nav ms-auto">
                        @foreach($menus as $menu)
                            <li class="nav-item">
                                @if($menu->children && $menu->children->count() > 0)
                                    <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#previewMobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                        <span>{{ $menu->name }}</span>
                                        <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                    </a>
                                    <div class="collapse mobile-menu-submenu" id="previewMobileSubmenu{{ $menu->id }}">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                            </li>
                                            @foreach($menu->children as $child)
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                <div class="mobile-menu-backdrop collapse" id="previewMobileBackdrop7" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar7"></div>
                @endif
            </div>
        </nav>
        <div class="mobile-header-bottom-menu" style="background-color: {{ $headerBgColor }}; border-top: none; border-bottom: 3px solid {{ $pointColor }}; overflow-x: auto; white-space: nowrap;">
            @foreach($menus as $menu)
                <a href="#" class="mobile-header-bottom-menu-item" style="color: {{ $headerTextColor }}; text-decoration: none; display: inline-block; padding: 0.5rem 1rem;">{{ $menu->name }}</a>
            @endforeach
        </div>
    @elseif($theme === 'theme8')
        <nav class="navbar navbar-expand-lg" style="{{ $headerStyle }}">
            <div class="container-fluid" style="padding-left: 0.9375rem; padding-right: 0.9375rem;">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar8" aria-controls="previewMobileNavbar8" aria-expanded="false" aria-label="Toggle navigation" style="@if(isset($mobileMenuIconBorder) && $mobileMenuIconBorder)border: 1px solid {{ $headerTextColor }};@else border: none;@endif">
                    <i class="bi {{ $menuIcon }}" style="color: {{ $headerTextColor }}; font-size: 1.5rem;"></i>
                </button>
                <a class="navbar-brand ms-auto" href="#" style="color: {{ $headerTextColor }} !important;">
                    @if($logoType === 'text' || empty($siteLogo))
                        {{ $siteName }}
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                    @endif
                </a>
                <div class="collapse navbar-collapse mobile-menu-overlay {{ $menuAnimationClass }}" id="previewMobileNavbar8">
                    @if(in_array($menuDirection, ['left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top']))
                    <div class="mobile-menu-logo">
                        <a href="#">
                            @if($logoType === 'text' || empty($siteLogo))
                                {{ $siteName }}
                            @else
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="width: {{ $logoMobileSize }}px; height: auto;">
                            @endif
                        </a>
                        <button type="button" class="mobile-menu-close-btn" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar8" aria-label="메뉴 닫기">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @endif
                    @if(isset($mobileMenuLoginWidget) && $mobileMenuLoginWidget)
                        @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                            {{-- 좌에서우/우에서좌: 사이드 위젯 스타일 --}}
                            <div class="mobile-menu-login-widget" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                            </div>
                        @elseif(in_array($menuDirection, ['top-to-bottom', 'bottom-to-top']))
                            {{-- 위에서아래/아래에서위: 로그인 버튼 타입 --}}
                            <div class="mobile-menu-login-buttons" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); display: flex; justify-content: center; gap: 0.5rem;">
                                @auth
                                    <div class="dropdown" style="width: 100%;">
                                        <button class="btn btn-sm dropdown-toggle" type="button" id="previewMobileMenuUserDropdown8" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: {{ $pointColor }}; color: white; border: none; width: 100%;">
                                            {{ auth()->user()->name }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="previewMobileMenuUserDropdown8">
                                            <li><a class="dropdown-item" href="#">내정보</a></li>
                                            <li><a class="dropdown-item" href="#">내 게시글</a></li>
                                            <li><a class="dropdown-item" href="#">내 댓글</a></li>
                                            <li><a class="dropdown-item" href="#">저장한 글</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="#" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">로그아웃</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    <a href="#" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none;">
                                        로그인
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }};">
                                        회원가입
                                    </a>
                                @endauth
                            </div>
                        @endif
                    @endif
                    <ul class="navbar-nav">
                        @foreach($menus as $menu)
                            <li class="nav-item">
                                @if($menu->children && $menu->children->count() > 0)
                                    <a class="nav-link mobile-menu-dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#previewMobileSubmenu{{ $menu->id }}" aria-expanded="false" style="color: {{ $headerTextColor }} !important;">
                                        <span>{{ $menu->name }}</span>
                                        <i class="bi bi-chevron-down mobile-menu-dropdown-icon"></i>
                                    </a>
                                    <div class="collapse mobile-menu-submenu" id="previewMobileSubmenu{{ $menu->id }}">
                                        <ul class="navbar-nav">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                            </li>
                                            @foreach($menu->children as $child)
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $child->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a class="nav-link" href="#" style="color: {{ $headerTextColor }} !important;">{{ $menu->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if(in_array($menuDirection, ['left-to-right', 'right-to-left']))
                <div class="mobile-menu-backdrop collapse" id="previewMobileBackdrop8" data-bs-toggle="collapse" data-bs-target="#previewMobileNavbar8"></div>
                @endif
            </div>
        </nav>
        <div class="mobile-header-bottom-menu" style="background-color: {{ $headerBgColor }}; border-top: none; border-bottom: 3px solid {{ $pointColor }}; overflow-x: auto; white-space: nowrap;">
            @foreach($menus as $menu)
                <a href="#" class="mobile-header-bottom-menu-item" style="color: {{ $headerTextColor }}; text-decoration: none; display: inline-block; padding: 0.5rem 1rem;">{{ $menu->name }}</a>
            @endforeach
        </div>
    @endif
</div>

<script>
(function() {
    // 미리보기에서 메뉴가 기본적으로 닫혀있도록 설정
    const menuIds = ['previewMobileNavbar1', 'previewMobileNavbar2', 'previewMobileNavbar3', 'previewMobileNavbar4', 'previewMobileNavbar5', 'previewMobileNavbar6', 'previewMobileNavbar7', 'previewMobileNavbar8'];
    
    function initializeMenus() {
        menuIds.forEach(function(menuId) {
            const menuElement = document.getElementById(menuId);
            if (!menuElement) return;
            
            // 메뉴를 확실히 닫기
            menuElement.style.display = 'none';
            menuElement.classList.remove('show');
            menuElement.setAttribute('aria-expanded', 'false');
            
            // 기존 collapse 인스턴스 제거
            const existingCollapse = bootstrap.Collapse.getInstance(menuElement);
            if (existingCollapse) {
                existingCollapse.dispose();
            }
            
            // 새로운 collapse 인스턴스 생성
            const bsCollapse = new bootstrap.Collapse(menuElement, { toggle: false });
            
            // X 버튼 클릭 시 닫기
            const closeBtn = menuElement.querySelector('.mobile-menu-close-btn');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    bsCollapse.hide();
                });
            }
        });
    }
    
    // DOMContentLoaded 또는 즉시 실행
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMenus);
    } else {
        initializeMenus();
    }
    
    // 미리보기가 동적으로 로드된 경우를 대비해 MutationObserver 사용
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                setTimeout(initializeMenus, 100);
            }
        });
    });
    
    const previewContainer = document.getElementById('mobile_header_preview');
    if (previewContainer) {
        observer.observe(previewContainer, { childList: true, subtree: true });
    }
})();
</script>

