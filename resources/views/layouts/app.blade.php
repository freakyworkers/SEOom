<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
        $siteName = $site->getSetting('site_name', $site->name ?? 'SEOom Builder');
        $siteDescription = $site->getSetting('site_description', '');
        $siteKeywords = $site->getSetting('site_keywords', '');
        $siteLogo = $site->getSetting('site_logo', '');
        $siteFavicon = $site->getSetting('site_favicon', '');
        $ogImage = $site->getSetting('og_image', $siteLogo);
        $logoType = $site->getSetting('logo_type', 'image');
        $logoDesktopSize = $site->getSetting('logo_desktop_size', '300');
        $logoMobileSize = $site->getSetting('logo_mobile_size', '200');
        
        // 테마 설정
        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
        $themeTop = $site->getSetting('theme_top', 'design1');
        $themeTopHeaderShow = $site->getSetting('theme_top_header_show', '0');
        $headerSticky = $site->getSetting('header_sticky', '0');
        $themeBottom = $site->getSetting('theme_bottom', 'theme03');
        $themeMain = $site->getSetting('theme_main', 'round');
        $themeFullWidth = $site->getSetting('theme_full_width', '0') == '1';
        
        // 사이드 위젯 기능이 없는 플랜의 경우 사이드바를 'none'으로 강제 설정
        if (!$site->hasFeature('sidebar_widgets')) {
            $themeSidebar = 'none';
            // 설정도 강제로 업데이트
            $site->setSetting('theme_sidebar', 'none');
        } else {
            $themeSidebar = $site->getSetting('theme_sidebar', 'left');
        }
        
        // 색상 설정 (라이트 모드)
        $colorLightHeaderText = $site->getSetting('color_light_header_text', '#000000');
        $colorLightHeaderBg = $site->getSetting('color_light_header_bg', '#ffffff');
        $colorLightFooterText = $site->getSetting('color_light_footer_text', '#000000');
        $colorLightFooterBg = $site->getSetting('color_light_footer_bg', '#f8f9fa');
        $colorLightBodyText = $site->getSetting('color_light_body_text', '#000000');
        $colorLightBodyBg = $site->getSetting('color_light_body_bg', '#f8f9fa');
        $colorLightPointMain = $site->getSetting('color_light_point_main', '#0d6efd');
        $colorLightPointBg = $site->getSetting('color_light_point_bg', '#000000');
        
        // 색상 설정 (다크 모드)
        $colorDarkHeaderText = $site->getSetting('color_dark_header_text', '#ffffff');
        $colorDarkHeaderBg = $site->getSetting('color_dark_header_bg', '#000000');
        $colorDarkFooterText = $site->getSetting('color_dark_footer_text', '#ffffff');
        $colorDarkFooterBg = $site->getSetting('color_dark_footer_bg', '#000000');
        $colorDarkBodyText = $site->getSetting('color_dark_body_text', '#ffffff');
        $colorDarkBodyBg = $site->getSetting('color_dark_body_bg', '#000000');
        $colorDarkPointMain = $site->getSetting('color_dark_point_main', '#ffffff');
        $colorDarkPointBg = $site->getSetting('color_dark_point_bg', '#212529');
        
        // 폰트 설정
        $fontDesign = $site->getSetting('font_design', 'noto-sans');
        $fontSize = $site->getSetting('font_size', 'normal');
    @endphp
    
    <!-- SEO Meta Tags -->
    @php
        // 페이지 타이틀 처리
        $pageTitle = '';
        try {
            if (isset($__env) && method_exists($__env, 'hasSection') && $__env->hasSection('title')) {
                $pageTitle = trim($__env->yieldContent('title'));
            }
        } catch (\Exception $e) {
            // 에러 발생 시 빈 문자열 유지
            $pageTitle = '';
        }
        
        if (empty($pageTitle) || $pageTitle === $siteName) {
            // 타이틀이 없거나 사이트 이름과 같으면 사이트 이름만 표시
            $fullTitle = $siteName;
            $ogTitle = $siteName;
        } else {
            // 타이틀이 있으면 "페이지명 - 사이트명" 형식
            $fullTitle = $pageTitle . ' - ' . $siteName;
            $ogTitle = $pageTitle;
        }
    @endphp
    <title>{{ $fullTitle }}</title>
    @if(!empty($siteDescription))
        <meta name="description" content="{{ $siteDescription }}">
    @endif
    @if(!empty($siteKeywords))
        <meta name="keywords" content="{{ $siteKeywords }}">
    @endif
    
    <!-- 검색 엔진 인증 메타 태그 -->
    @php
        $googleVerification = $site->getSetting('google_site_verification', '');
        $naverVerification = $site->getSetting('naver_site_verification', '');
        $daumVerification = $site->getSetting('daum_site_verification', '');
    @endphp
    @if(!empty($googleVerification))
        <meta name="google-site-verification" content="{{ $googleVerification }}">
    @endif
    @if(!empty($naverVerification))
        <meta name="naver-site-verification" content="{{ $naverVerification }}">
    @endif
    @if(!empty($daumVerification))
        <meta name="daum-site-verification" content="{{ $daumVerification }}">
    @endif
    
    <!-- Favicon -->
    @if(!empty($siteFavicon))
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $siteFavicon }}">
    @endif
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $ogTitle }}">
    @if(!empty($siteDescription))
        <meta property="og:description" content="{{ $siteDescription }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @elseif(!empty($siteLogo))
        <meta property="og:image" content="{{ $siteLogo }}">
    @endif
    <meta property="og:site_name" content="{{ $siteName }}">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    @if(!empty($siteDescription))
        <meta name="twitter:description" content="{{ $siteDescription }}">
    @endif
    @if(!empty($ogImage))
        <meta name="twitter:image" content="{{ $ogImage }}">
    @elseif(!empty($siteLogo))
        <meta name="twitter:image" content="{{ $siteLogo }}">
    @endif
    
    <!-- Google Analytics -->
    @php
        $googleAnalyticsId = $site->getSetting('google_analytics_id', '');
    @endphp
    @if(!empty($googleAnalyticsId))
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $googleAnalyticsId }}');
        </script>
    @endif
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans KR", "Malgun Gothic", sans-serif;
        }
        
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        /* 테마 설정 적용 */
        @if($themeDarkMode === 'dark')
            :root {
                --header-text-color: {{ $colorDarkHeaderText }};
                --header-bg-color: {{ $colorDarkHeaderBg }};
                --body-text-color: {{ $colorDarkBodyText }};
                --body-bg-color: {{ $colorDarkBodyBg }};
                --point-main-color: {{ $colorDarkPointMain }};
                --point-bg-color: {{ $colorDarkPointBg }};
            }
            body {
                background-color: var(--body-bg-color);
                color: var(--body-text-color);
            }
            .navbar {
                background-color: var(--header-bg-color) !important;
                color: var(--header-text-color);
            }
            .navbar-brand, .navbar-nav .nav-link {
                color: var(--header-text-color) !important;
            }
        @else
            :root {
                --header-text-color: {{ $colorLightHeaderText }};
                --header-bg-color: {{ $colorLightHeaderBg }};
                --body-text-color: {{ $colorLightBodyText }};
                --body-bg-color: {{ $colorLightBodyBg }};
                --point-main-color: {{ $colorLightPointMain }};
                --point-bg-color: {{ $colorLightPointBg }};
            }
            body {
                background-color: var(--body-bg-color);
                color: var(--body-text-color);
            }
            .navbar {
                background-color: var(--header-bg-color) !important;
            }
        @endif
        
        /* 포인트 색상 적용 */
        .btn-primary, .bg-primary {
            background-color: var(--point-main-color) !important;
            border-color: var(--point-main-color) !important;
        }
        .text-primary {
            color: var(--point-main-color) !important;
        }
        
        /* 폰트 설정 적용 */
        @php
            $fontFamilies = [
                'noto-sans' => '"Noto Sans KR", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                'malgun-gothic' => '"Malgun Gothic", "맑은 고딕", sans-serif',
                'nanum-gothic' => '"Nanum Gothic", "나눔고딕", sans-serif',
                'nanum-myeongjo' => '"Nanum Myeongjo", "나눔명조", serif',
                'pretendard' => '"Pretendard", -apple-system, BlinkMacSystemFont, sans-serif',
                'roboto' => '"Roboto", sans-serif',
                'arial' => 'Arial, sans-serif',
            ];
            $fontFamily = $fontFamilies[$fontDesign] ?? $fontFamilies['noto-sans'];
            
            $fontSizes = [
                'small' => '14px',
                'normal' => '16px',
                'large' => '18px',
            ];
            $baseFontSize = $fontSizes[$fontSize] ?? $fontSizes['normal'];
        @endphp
        body {
            font-family: {!! $fontFamily !!};
            font-size: {{ $baseFontSize }};
        }
        
        /* 테마 스타일 적용 */
        @if($themeMain === 'round')
            /* 라운드 스타일 - 위젯, 배너, 게시판의 최상위 컨테이너에만 라운드 적용 */
            /* 위젯 최상위 컨테이너 */
            .card.shadow-sm.mb-3,
            .widget-card,
            .main-widget-container,
            .sidebar-widget-container {
                border-radius: 0.5rem !important;
            }
            
            /* 배너 최상위 컨테이너 */
            .banner-item,
            .banner-container .banner-item,
            [class*="banner-item-"],
            .banner-link,
            .banner-slider,
            .banner-slide {
                border-radius: 0.5rem !important;
            }
            
            .banner-item img,
            .banner-link img,
            .banner-image {
                border-radius: 0.5rem !important;
            }
            
            /* 게시판 최상위 컨테이너 */
            .board-container,
            .bg-white.p-3.rounded.shadow-sm,
            .post-list-container,
            .card.bg-white.shadow-sm {
                border-radius: 0.5rem !important;
            }
            
            /* 게시판 카드 라운드 적용 (더 구체적인 선택자) */
            .card.bg-white.shadow-sm[style*="border-radius"],
            .card.bg-white.shadow-sm:not(.rounded-0) {
                border-radius: 0.5rem !important;
            }
            
            /* 일반 UI 요소 */
            .btn, .form-control, .form-select, 
            .badge, .shadow-sm, 
            img.rounded, .modal-content, .alert, 
            .nav-link, .dropdown-menu, .dropdown-item,
            .pagination .page-link,
            .input-group .form-control, .input-group-text,
            .bookmark-thumbnail-container, .bookmark-thumbnail-container-mobile,
            .bookmark-item-name-mobile, .bookmark-item-value-mobile,
            .post-card, .menu-item {
                border-radius: 0.5rem !important;
            }
            
            /* rounded 클래스 오버라이드 */
            .rounded, .rounded-top, .rounded-bottom, .rounded-start, .rounded-end {
                border-radius: 0.5rem !important;
            }
            
            /* 내부 요소에는 라운드 제거 (위젯만) */
            .widget-card .nav-tabs,
            .widget-card .tab-content,
            .widget-card .tab-pane,
            .widget-card .list-group,
            .widget-card .list-group-item,
            .widget-card .table,
            .widget-card .table tbody tr,
            .widget-card .table td,
            .widget-card .table th,
            .card.shadow-sm.mb-3 .nav-tabs,
            .card.shadow-sm.mb-3 .tab-content,
            .card.shadow-sm.mb-3 .tab-pane,
            .card.shadow-sm.mb-3 .list-group,
            .card.shadow-sm.mb-3 .list-group-item,
            .card.shadow-sm.mb-3 .table,
            .card.shadow-sm.mb-3 .table tbody tr,
            .card.shadow-sm.mb-3 .table td,
            .card.shadow-sm.mb-3 .table th {
                border-radius: 0 !important;
            }
            
            /* 위젯 내부 탭 메뉴 라운드 제거 */
            .card-header + .card-body .nav-tabs,
            .card-header + .card-body .tab-content,
            .card-header + .card-body .tab-pane {
                border-radius: 0 !important;
            }
            
            /* 게시판 리스트는 라운드 유지 (list-group-flush 사용 시) */
            .card.bg-white.shadow-sm .list-group-flush .list-group-item:first-child {
                border-top-left-radius: 0.5rem !important;
                border-top-right-radius: 0.5rem !important;
            }
            .card.bg-white.shadow-sm .list-group-flush .list-group-item:last-child {
                border-bottom-left-radius: 0.5rem !important;
                border-bottom-right-radius: 0.5rem !important;
            }
            
            /* 게시판 테이블도 라운드 유지 */
            .card.bg-white.shadow-sm .table-responsive {
                border-radius: 0.5rem !important;
                overflow: hidden !important;
            }
            .card.bg-white.shadow-sm .table-responsive:first-child {
                border-top-left-radius: 0.5rem !important;
                border-top-right-radius: 0.5rem !important;
            }
        @elseif($themeMain === 'square')
            /* 스퀘어 스타일 - 모든 요소에 직각 적용 */
            .btn, .card, .form-control, .form-select, 
            .badge, .list-group-item, .shadow-sm, 
            img.rounded, .modal-content, .alert, 
            .nav-link, .dropdown-menu, .dropdown-item,
            .list-group, .table, .pagination .page-link,
            .input-group .form-control, .input-group-text,
            .bookmark-thumbnail-container, .bookmark-thumbnail-container-mobile,
            .bookmark-item-name-mobile, .bookmark-item-value-mobile,
            .post-card, .widget-card, .menu-item,
            .nav-tabs, .tab-content, .tab-pane,
            [class*="rounded"], [style*="border-radius"] {
                border-radius: 0 !important;
            }
            /* rounded 클래스 오버라이드 */
            .rounded, .rounded-top, .rounded-bottom, .rounded-start, .rounded-end,
            .rounded-0, .rounded-1, .rounded-2, .rounded-3, .rounded-4, .rounded-5,
            .rounded-pill, .rounded-circle {
                border-radius: 0 !important;
            }
        @endif
        
        .card {
            border: none;
        }
        
        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        
        /* 사이드바 링크 hover 효과 */
        .card-body a {
            transition: color 0.2s;
        }
        .card-body a:hover {
            color: #212529 !important;
        }
        
        /* 클릭 가능한 텍스트 hover 효과 */
        .bookmark-btn-detail:hover {
            background-color: #6c757d;
            color: white !important;
            border-color: #6c757d !important;
        }
        
        /* Summernote 모달 스타일 수정 */
        .modal-dialog {
            max-width: 600px;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .note-image-dialog {
            max-width: 500px !important;
        }
        
        .note-image-dialog .modal-body {
            overflow: visible;
            padding: 1.5rem;
        }
        
        .note-image-dialog .note-group-select-from-files {
            margin-bottom: 1rem;
        }
        
        .note-image-dialog .note-image-input {
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .note-image-dialog .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            flex-wrap: wrap;
        }
        
        .note-image-dialog .modal-footer .btn {
            white-space: nowrap;
            min-width: auto;
            flex-shrink: 0;
        }
        
        .note-image-dialog .modal-content {
            overflow: hidden;
        }
        
        .table {
            background-color: white;
        }
        
        .table thead th {
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            color: var(--secondary-color);
        }
        
        footer {
            margin-top: auto;
            border-top: 1px solid var(--border-color);
        }
        
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 1rem;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
        }
    </style>
    
    @stack('styles')
    @stack('meta')
    
    {{-- 커스텀 코드: HEAD 태그 안 --}}
    @php
        try {
            $customCodeHead = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'head') : null;
        } catch (\Exception $e) {
            $customCodeHead = null;
        }
    @endphp
    @if($customCodeHead && !empty(trim($customCodeHead->code)))
        {!! $customCodeHead->code !!}
    @endif
    
    {{-- 커스텀 코드: 추가 CSS --}}
    @php
        try {
            $customCodeHeadCss = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'head_css') : null;
        } catch (\Exception $e) {
            $customCodeHeadCss = null;
        }
    @endphp
    @if($customCodeHeadCss && !empty(trim($customCodeHeadCss->code)))
        <style>
            {!! $customCodeHeadCss->code !!}
        </style>
    @endif
    
    {{-- 커스텀 코드: JavaScript --}}
    @php
        try {
            $customCodeHeadJs = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'head_js') : null;
        } catch (\Exception $e) {
            $customCodeHeadJs = null;
        }
    @endphp
    @if($customCodeHeadJs && !empty(trim($customCodeHeadJs->code)))
        <script>
            {!! $customCodeHeadJs->code !!}
        </script>
    @endif
</head>
<body class="d-flex flex-column min-vh-100" style="overflow-x: hidden; max-width: 100vw;">
    @php
        // 체크박스 값 비교 (문자열 '1' 또는 숫자 1 모두 처리)
        $showTopHeader = ($themeTopHeaderShow == '1' || $themeTopHeaderShow === '1' || $themeTopHeaderShow === 1);
        $isHeaderSticky = ($headerSticky == '1' || $headerSticky === '1' || $headerSticky === 1);
    @endphp
    
    {{-- 헤더 배너 (최상단 헤더 상단) - sticky wrapper 밖에 배치하여 스크롤 시 자연스럽게 사라지도록 --}}
    <div class="container-fluid px-0" style="max-width: 100vw; overflow-x: hidden;">
        <div class="{{ $themeFullWidth ? 'container-fluid' : 'container' }}" style="{{ $themeFullWidth ? 'max-width: 100%; padding-left: 15px; padding-right: 15px;' : '' }}">
            <x-banner-display :site="$site" location="header" />
        </div>
    </div>
    
    <!-- Top Header (최상단 헤더) - 모바일에서 숨김, sticky wrapper 밖에 배치하여 스크롤 시 자연스럽게 사라지도록 -->
    @if($showTopHeader)
        @php
            // 한국 시간 설정
            $koreaTime = now()->setTimezone('Asia/Seoul');
            $koreaDate = $koreaTime->format('Y년 m월 d일');
            $koreaDateFormatted = $koreaTime->format('Y년 m월 d일');
            
            // 방문자수 표기 설정 확인 (기본값: 표시)
            $showVisitorCount = $site->getSetting('show_visitor_count', '1') == '1';
            
            // 로그인 버튼 표시 설정 확인 (기본값: 0)
            $showTopHeaderLogin = $site->getSetting('top_header_login_show', '0') == '1';
            
            // 방문자 수 항상 가져오기 (표시 여부와 관계없이)
            $todayVisitors = \App\Models\Visitor::getTodayCount($site->id);
            $totalVisitors = \App\Models\Visitor::getTotalCount($site->id);
            
            // 테마에 따른 색상 설정
            $topBarBg = $themeDarkMode === 'dark' ? $colorDarkHeaderBg : $colorLightHeaderBg;
            $topBarText = $themeDarkMode === 'dark' ? $colorDarkHeaderText : $colorLightHeaderText;
            
            // 포인트 컬러 설정
            $pointColor = $themeDarkMode === 'dark' ? $site->getSetting('color_dark_point_main', '#ffffff') : $site->getSetting('color_light_point_main', '#0d6efd');
            
            // 알림 개수 가져오기
            $unreadNotificationCount = 0;
            if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                $unreadNotificationCount = \App\Models\Notification::getUnreadCount(auth()->id(), $site->id);
            }
        @endphp
        <div class="top-header-bar d-none d-xl-block" style="background-color: {{ $topBarBg }}; color: {{ $topBarText }}; padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.1);">
            <div class="{{ $themeFullWidth ? 'container-fluid' : 'container' }}" style="{{ $themeFullWidth ? 'max-width: 100%; padding-left: 15px; padding-right: 15px;' : '' }}">
                <div class="row align-items-center">
                    <div class="{{ $showTopHeaderLogin ? 'col-md-6 text-start' : 'col-md-6 text-start' }}">
                        <div class="d-flex align-items-center gap-2">
                            <small style="color: {{ $topBarText }} !important;">
                                {{ $koreaDateFormatted }}
                            </small>
                            @if($showTopHeaderLogin && $showVisitorCount)
                                <small style="color: {{ $topBarText }} !important;">
                                    오늘 : <strong>{{ number_format($todayVisitors) }}</strong>명 전체 : <strong>{{ number_format($totalVisitors) }}</strong>명
                                </small>
                            @endif
                        </div>
                    </div>
                    @if($showTopHeaderLogin)
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex align-items-center justify-content-md-end gap-1">
                            @auth
                                <div class="dropdown">
                                    <a class="btn btn-sm dropdown-toggle position-relative" href="#" id="topHeaderUserDropdown" role="button" data-bs-toggle="dropdown" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                        <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->nickname ?? auth()->user()->name }}
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
                                        @if(($site->isMasterSite() ?? false))
                                            <li><a class="dropdown-item" href="{{ route('users.my-sites', ['site' => $site->slug ?? 'default']) }}"><i class="bi bi-house-door me-2"></i>내 홈페이지</a></li>
                                        @endif
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
                                </div>
                            @else
                                <a class="btn btn-sm" href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" style="background-color: transparent; border: 1px solid {{ $pointColor }}; color: {{ $pointColor }} !important; padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>로그인
                                </a>
                                <a class="btn btn-sm" href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" style="background-color: {{ $pointColor }}; border: 1px solid {{ $pointColor }}; color: {{ $topBarBg }} !important; padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                    <i class="bi bi-person-plus me-1"></i>회원가입
                                </a>
                            @endauth
                        </div>
                    </div>
                    @else
                    @if($showVisitorCount)
                    <div class="col-md-6 text-md-end">
                        <small style="color: {{ $topBarText }} !important;">
                            오늘 : <strong>{{ number_format($todayVisitors) }}</strong>명 전체 : <strong>{{ number_format($totalVisitors) }}</strong>명
                        </small>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Navigation -->
    @php
        // 헤더 고정 시 top을 0으로 설정
        $stickyTop = $isHeaderSticky ? '0' : '';
    @endphp
    <div class="{{ $isHeaderSticky ? 'sticky-header-wrapper' : '' }}" style="{{ $isHeaderSticky ? 'position: sticky; top: ' . $stickyTop . '; z-index: 1030;' : '' }}">
        {{-- PC 헤더 (데스크탑에서만 표시) --}}
        <x-header-theme 
            :theme="$themeTop" 
            :site="$site" 
            :siteName="$siteName"
            :logoType="$logoType"
            :siteLogo="$siteLogo"
            :logoDesktopSize="$logoDesktopSize"
            :logoMobileSize="$logoMobileSize" />
        
        {{-- 모바일 헤더 (모바일 및 테블릿에서만 표시) --}}
        @php
            $mobileHeaderTheme = $site->getSetting('mobile_header_theme', 'theme1');
        @endphp
        <x-mobile-header-theme 
            :theme="$mobileHeaderTheme" 
            :site="$site"
        />
    </div>

    <!-- Main Content -->
    <main class="container my-4 flex-grow-1">
        @if(session('error'))
            @if(str_contains(session('error'), '비밀글'))
                {{-- 비밀글 에러는 모달로 표시 --}}
                <div class="modal fade" id="secretPostModal" tabindex="-1" aria-labelledby="secretPostModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="secretPostModalLabel">
                                    <i class="bi bi-lock me-2"></i>비밀글 접근 제한
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">{{ session('error') }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">확인</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var modal = new bootstrap.Modal(document.getElementById('secretPostModal'));
                        modal.show();
                    });
                </script>
            @elseif(str_contains(session('error'), '게시글 작성 후'))
                {{-- 게시글 작성 간격 에러는 모달로 표시 --}}
                <div class="modal fade" id="writeIntervalModal" tabindex="-1" aria-labelledby="writeIntervalModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="writeIntervalModalLabel">
                                    <i class="bi bi-clock-history me-2"></i>게시글 작성 제한
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">{{ session('error') }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var modal = new bootstrap.Modal(document.getElementById('writeIntervalModal'));
                        modal.show();
                    });
                </script>
            @else
                {{-- 다른 에러는 기존대로 표시 --}}
                <x-alert type="error">{{ session('error') }}</x-alert>
            @endif
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> 입력한 정보를 확인해주세요.
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row position-relative" style="overflow: visible;">
            {{-- 좌측 여백 배너 --}}
            @php
                $bannerGap = (int)$site->getSetting('banner_desktop_gap', 16);
            @endphp
            <div class="banner-margin-left d-none d-xl-block position-absolute" style="right: calc(100% + {{ $bannerGap }}px); top: 0; width: auto; max-width: 500px; min-width: 200px; z-index: 1; padding-right: 0 !important; padding-left: 0 !important;">
                <x-banner-display :site="$site" location="left_margin" />
            </div>
            
            {{-- 우측 여백 배너 --}}
            <div class="banner-margin-right d-none d-xl-block position-absolute" style="left: calc(100% + {{ $bannerGap }}px); top: 0; width: auto; max-width: 500px; min-width: 200px; z-index: 1; padding-left: 0 !important; padding-right: 0 !important;">
                <x-banner-display :site="$site" location="right_margin" />
            </div>
            
            @if($themeSidebar === 'left')
                <aside class="col-md-3 mb-4">
                    {{-- 사용자 위젯 --}}
                    @if($site->getSetting('enable_sidebar_login_widget', true))
                        <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                    @endif
                    
                    {{-- 커스텀 코드: 사이드바 상단 (사이드바 상단 배너 바로 위쪽) --}}
                    @php
                        try {
                            $customCodeSidebarTop = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'sidebar_top') : null;
                        } catch (\Exception $e) {
                            $customCodeSidebarTop = null;
                        }
                    @endphp
                    @if($customCodeSidebarTop && !empty(trim($customCodeSidebarTop->code)))
                        <div class="custom-code-sidebar-top mb-3">
                            {!! $customCodeSidebarTop->code !!}
                        </div>
                    @endif
                    
                    {{-- 사이드바 상단 배너 --}}
                    <x-banner-display :site="$site" location="sidebar_top" />
                    
                    {{-- 사이드바 위젯 --}}
                    @php
                        try {
                            $sidebarWidgets = \App\Models\SidebarWidget::where('site_id', $site->id)
                                ->where('is_active', true)
                                ->orderBy('order', 'asc')
                                ->get();
                        } catch (\Exception $e) {
                            $sidebarWidgets = collect();
                        }
                    @endphp
                    @foreach($sidebarWidgets as $widget)
                        <x-sidebar-widget :widget="$widget" :site="$site" />
                    @endforeach
                    
                    {{-- 사이드바 하단 배너 --}}
                    <x-banner-display :site="$site" location="sidebar_bottom" />
                </aside>
            @endif

            <div class="{{ $themeSidebar !== 'none' ? 'col-md-9' : 'col-12' }}">
                @php
                    // 현재 라우트가 홈페이지인지 확인
                    $isHomePage = request()->routeIs('home');
                    // 내정보 관련 페이지인지 확인
                    $isUserProfilePage = request()->routeIs('users.profile', 'users.point-history', 'users.saved-posts', 'users.my-posts', 'users.my-comments');
                    // 알림 및 쪽지함 페이지인지 확인
                    $isNotificationOrMessagePage = request()->routeIs('notifications.index', 'messages.index', 'messages.show');
                @endphp
                
                @if($isHomePage)
                    {{-- 커스텀 코드: 첫 페이지 상단 (메인 상단 배너보다 위쪽) --}}
                    @php
                        try {
                            $customCodeFirstPageTop = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'first_page_top') : null;
                        } catch (\Exception $e) {
                            $customCodeFirstPageTop = null;
                        }
                    @endphp
                    @if($customCodeFirstPageTop && !empty(trim($customCodeFirstPageTop->code)))
                        <div class="custom-code-first-page-top mb-3">
                            {!! $customCodeFirstPageTop->code !!}
                        </div>
                    @endif
                    
                    {{-- 메인 상단 배너 (col-md-9 상단) - 홈페이지에만 표시 --}}
                    <x-banner-display :site="$site" location="main_top" />
                @elseif(!$isUserProfilePage && !$isNotificationOrMessagePage)
                    {{-- 커스텀 코드: 본문 상단 (본문 상단 배너보다 위쪽) --}}
                    @php
                        try {
                            $customCodeContentTop = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'content_top') : null;
                        } catch (\Exception $e) {
                            $customCodeContentTop = null;
                        }
                    @endphp
                    @if($customCodeContentTop && !empty(trim($customCodeContentTop->code)))
                        <div class="custom-code-content-top mb-3">
                            {!! $customCodeContentTop->code !!}
                        </div>
                    @endif
                    
                    {{-- 본문 상단 배너 (col-md-9 상단) - 메인페이지, 내정보 페이지, 알림/쪽지함 페이지를 제외한 모든 페이지에 표시 --}}
                    <x-banner-display :site="$site" location="content_top" />
                @endif
                
                @yield('content')
                
                @if($isHomePage)
                    {{-- 메인 하단 배너 (col-md-9 하단) - 홈페이지에만 표시 --}}
                    <x-banner-display :site="$site" location="main_bottom" />
                    
                    {{-- 커스텀 코드: 첫 페이지 하단 (메인 하단 배너보다 아래쪽) --}}
                    @php
                        try {
                            $customCodeFirstPageBottom = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'first_page_bottom') : null;
                        } catch (\Exception $e) {
                            $customCodeFirstPageBottom = null;
                        }
                    @endphp
                    @if($customCodeFirstPageBottom && !empty(trim($customCodeFirstPageBottom->code)))
                        <div class="custom-code-first-page-bottom mt-3">
                            {!! $customCodeFirstPageBottom->code !!}
                        </div>
                    @endif
                @elseif(!$isUserProfilePage && !$isNotificationOrMessagePage)
                    {{-- 본문 하단 배너 (col-md-9 하단) - 메인페이지, 내정보 페이지, 알림/쪽지함 페이지를 제외한 모든 페이지에 표시 --}}
                    <x-banner-display :site="$site" location="content_bottom" />
                    
                    {{-- 게시판 하단 내용 (본문 하단 배너 다음에 표시) --}}
                    @stack('board-footer')
                    
                    {{-- 커스텀 코드: 본문 하단 (본문 하단 배너보다 아래쪽) --}}
                    @php
                        try {
                            $customCodeContentBottom = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'content_bottom') : null;
                        } catch (\Exception $e) {
                            $customCodeContentBottom = null;
                        }
                    @endphp
                    @if($customCodeContentBottom && !empty(trim($customCodeContentBottom->code)))
                        <div class="custom-code-content-bottom mt-3">
                            {!! $customCodeContentBottom->code !!}
                        </div>
                    @endif
                @endif
            </div>

            @if($themeSidebar === 'right')
                <aside class="col-md-3 mb-4">
                    {{-- 사용자 위젯 --}}
                    @if($site->getSetting('enable_sidebar_login_widget', true))
                        <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                    @endif
                    
                    {{-- 커스텀 코드: 사이드바 상단 (사이드바 상단 배너 바로 위쪽) --}}
                    @php
                        try {
                            $customCodeSidebarTop = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'sidebar_top') : null;
                        } catch (\Exception $e) {
                            $customCodeSidebarTop = null;
                        }
                    @endphp
                    @if($customCodeSidebarTop && !empty(trim($customCodeSidebarTop->code)))
                        <div class="custom-code-sidebar-top mb-3">
                            {!! $customCodeSidebarTop->code !!}
                        </div>
                    @endif
                    
                    {{-- 사이드바 상단 배너 --}}
                    <x-banner-display :site="$site" location="sidebar_top" />
                    
                    {{-- 사이드바 위젯 --}}
                    @php
                        try {
                            $sidebarWidgets = \App\Models\SidebarWidget::where('site_id', $site->id)
                                ->where('is_active', true)
                                ->orderBy('order', 'asc')
                                ->get();
                        } catch (\Exception $e) {
                            $sidebarWidgets = collect();
                        }
                    @endphp
                    @foreach($sidebarWidgets as $widget)
                        <x-sidebar-widget :widget="$widget" :site="$site" />
                    @endforeach
                    
                    {{-- 사이드바 하단 배너 --}}
                    <x-banner-display :site="$site" location="sidebar_bottom" />
                    
                    {{-- 커스텀 코드: 사이드바 하단 (사이드바 하단 배너 바로 아래쪽) --}}
                    @php
                        try {
                            $customCodeSidebarBottom = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'sidebar_bottom') : null;
                        } catch (\Exception $e) {
                            $customCodeSidebarBottom = null;
                        }
                    @endphp
                    @if($customCodeSidebarBottom && !empty(trim($customCodeSidebarBottom->code)))
                        <div class="custom-code-sidebar-bottom mt-3">
                            {!! $customCodeSidebarBottom->code !!}
                        </div>
                    @endif
                </aside>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <x-footer-theme 
        :theme="$themeBottom"
        :site="$site" />

    <!-- Mobile Bottom Menu -->
    <x-mobile-bottom-menu :site="$site" />

    <!-- Popup Display -->
    <x-popup-display :site="$site" />

    <!-- 이용약관 & 개인정보처리방침 모달 -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">이용약관</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="termsModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">개인정보처리방침</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="privacyModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>

    {{-- 성공 메시지 팝업 모달 --}}
    @if(session('success'))
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="bi bi-check-circle me-2"></i>알림
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">{{ session('success') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- jQuery (must be before Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>
    
    <script>
    // 이용약관 & 개인정보처리방침 팝업
    $(document).ready(function() {
        // 이용약관 모달 열릴 때
        $('#termsModal').on('show.bs.modal', function() {
            const modalBody = $('#termsModalBody');
            if (modalBody.data('loaded')) return;
            
            $.ajax({
                url: '{{ route("terms-of-service", ["site" => $site->slug]) }}',
                method: 'GET',
                success: function(response) {
                    if (response.content) {
                        modalBody.html('<div style="white-space: pre-wrap; line-height: 1.6;">' + response.content.replace(/\n/g, '<br>') + '</div>');
                    } else {
                        modalBody.html('<p class="text-muted text-center py-4">등록된 이용약관이 없습니다.</p>');
                    }
                    modalBody.data('loaded', true);
                },
                error: function() {
                    modalBody.html('<p class="text-danger text-center py-4">이용약관을 불러오는 중 오류가 발생했습니다.</p>');
                }
            });
        });
        
        // 개인정보처리방침 모달 열릴 때
        $('#privacyModal').on('show.bs.modal', function() {
            const modalBody = $('#privacyModalBody');
            if (modalBody.data('loaded')) return;
            
            $.ajax({
                url: '{{ route("privacy-policy", ["site" => $site->slug]) }}',
                method: 'GET',
                success: function(response) {
                    if (response.content) {
                        modalBody.html('<div style="white-space: pre-wrap; line-height: 1.6;">' + response.content.replace(/\n/g, '<br>') + '</div>');
                    } else {
                        modalBody.html('<p class="text-muted text-center py-4">등록된 개인정보처리방침이 없습니다.</p>');
                    }
                    modalBody.data('loaded', true);
                },
                error: function() {
                    modalBody.html('<p class="text-danger text-center py-4">개인정보처리방침을 불러오는 중 오류가 발생했습니다.</p>');
                }
            });
        });
        
        // 모달 닫힐 때 내용 초기화 (다시 열 때 새로 로드)
        $('#termsModal, #privacyModal').on('hidden.bs.modal', function() {
            $(this).find('.modal-body').data('loaded', false);
        });
        
        // 성공 메시지 모달 자동 표시
        @if(session('success'))
        $(document).ready(function() {
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        });
        @endif
        
        // 헤더 고정 시 최상단 헤더 스크롤 숨김 처리
        @if($isHeaderSticky && $showTopHeader)
        (function() {
            // hide-on-scroll 애니메이션 제거 - 최상단 헤더는 자연스럽게 스크롤되도록
            // 이 코드는 더 이상 필요하지 않음
        })();
        @endif
    });
    </script>
    
    @if($isHeaderSticky)
    <style>
        /* 헤더만 고정되도록 설정 */
        .sticky-header-wrapper {
            background-color: transparent;
        }
        /* hide-on-scroll 애니메이션 제거 - 최상단 헤더는 자연스럽게 스크롤되도록 */
        .top-header-bar.hide-on-scroll {
            position: relative;
            z-index: auto;
        }
    </style>
    @endif
    
    @stack('scripts')
    
    @auth
    <!-- 포인트 내역 모달 (전역 사용) -->
    <div class="modal fade" id="pointHistoryModal" tabindex="-1" aria-labelledby="pointHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pointHistoryModalLabel">
                        <i class="bi bi-clock-history me-2"></i>포인트 내역
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="pointHistoryContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">로딩 중...</span>
                            </div>
                            <p class="mt-3 text-muted">포인트 내역을 불러오는 중...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const pointHistoryModal = document.getElementById('pointHistoryModal');
        const pointHistoryContent = document.getElementById('pointHistoryContent');
        
        if (!pointHistoryModal || !pointHistoryContent) {
            return;
        }
        
        // URL 해시가 #point-history인 경우 모달 자동 열기
        if (window.location.hash === '#point-history') {
            const modal = new bootstrap.Modal(pointHistoryModal);
            modal.show();
            // 해시 제거 (모달이 닫힌 후에도 해시가 남아있지 않도록)
            history.replaceState(null, null, window.location.pathname);
        }
        
        pointHistoryModal.addEventListener('show.bs.modal', function() {
            // 모달이 열릴 때 포인트 내역 로드
            loadPointHistory();
        });
        
        function loadPointHistory() {
            pointHistoryContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">로딩 중...</span>
                    </div>
                    <p class="mt-3 text-muted">포인트 내역을 불러오는 중...</p>
                </div>
            `;
            
            fetch('{{ route("users.point-history", ["site" => $site->slug ?? "default"]) }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.history && data.history.length > 0) {
                        let html = '<div class="table-responsive"><table class="table table-hover">';
                        html += '<thead class="table-light"><tr><th>날짜</th><th>내용</th><th>포인트</th><th>잔액</th></tr></thead>';
                        html += '<tbody>';
                        
                        data.history.forEach(function(item) {
                            const pointClass = item.points > 0 ? 'text-success' : 'text-danger';
                            const pointSign = item.points > 0 ? '+' : '';
                            html += `<tr>
                                <td>${item.date}</td>
                                <td>${item.description}</td>
                                <td class="${pointClass}">${pointSign}${item.points.toLocaleString()}</td>
                                <td>${item.balance.toLocaleString()}</td>
                            </tr>`;
                        });
                        
                        html += '</tbody></table></div>';
                        pointHistoryContent.innerHTML = html;
                    } else {
                        pointHistoryContent.innerHTML = `
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                                <p class="mt-3 text-muted">포인트 내역이 없습니다.</p>
                            </div>
                        `;
                    }
                } else {
                    pointHistoryContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>포인트 내역을 불러오는 중 오류가 발생했습니다.
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading point history:', error);
                pointHistoryContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>포인트 내역을 불러오는 중 오류가 발생했습니다.
                    </div>
                `;
            });
        }
        
        // 전역 함수로 모달 열기 함수 제공
        window.openPointHistoryModal = function() {
            const modal = new bootstrap.Modal(pointHistoryModal);
            modal.show();
        };
    });
    </script>
    @endauth
    
    {{-- 마스터 콘솔 SSO JavaScript --}}
    @php
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
    
    {{-- 커스텀 코드: BODY 태그 안 (모든 페이지 BODY 태그 영역의 하단) --}}
    @php
        try {
            $customCodeBody = \Illuminate\Support\Facades\Schema::hasTable('custom_codes') ? \App\Models\CustomCode::getByLocation($site->id, 'body') : null;
        } catch (\Exception $e) {
            $customCodeBody = null;
        }
    @endphp
    @if($customCodeBody && !empty(trim($customCodeBody->code)))
        {!! $customCodeBody->code !!}
    @endif
</body>
</html>
