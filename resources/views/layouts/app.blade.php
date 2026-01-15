<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
        // 에러 발생 시에도 head가 렌더링되도록 최상위 try-catch
        $layoutError = false;
        try {
            // $site 변수가 없으면 마스터 사이트 가져오기
            if (!isset($site) || !$site) {
                try {
                    $site = \App\Models\Site::getMasterSite();
                } catch (\Exception $e) {
                    $site = null;
                }
            }
            
            // $site가 여전히 null이면 기본값으로 처리
            if (!$site || !$site->id) {
                // 기본값 설정
                $siteName = 'SEOom Builder';
                $siteDescription = '';
                $siteKeywords = '';
                $siteLogo = '';
                $siteFavicon = '';
                $ogImage = '';
                $logoType = 'image';
                $logoDesktopSize = '300';
                $logoMobileSize = '200';
                $themeDarkMode = 'light';
                $themeTop = 'design1';
                $themeTopHeaderShow = '0';
                $headerSticky = '0';
                $themeBottom = 'theme03';
                $themeMain = 'round';
                $themeFullWidth = false;
                $themeSidebar = 'left';
                $widgetShadow = true;
                $colorLightHeaderText = '#000000';
                $colorLightHeaderBg = '#ffffff';
                $colorLightFooterText = '#000000';
                $colorLightFooterBg = '#f8f9fa';
                $colorLightBodyText = '#000000';
                $colorLightBodyBg = '#f8f9fa';
                $colorLightPointMain = '#0d6efd';
                $colorLightPointBg = '#000000';
                $colorDarkHeaderText = '#ffffff';
                $colorDarkHeaderBg = '#000000';
                $colorDarkFooterText = '#ffffff';
                $colorDarkFooterBg = '#000000';
                $colorDarkBodyText = '#ffffff';
                $colorDarkBodyBg = '#000000';
                $colorDarkPointMain = '#ffffff';
                $colorDarkPointBg = '#212529';
                $fontDesign = 'noto-sans';
                $fontSize = 'normal';
            } else {
                // $site가 존재하면 정상적으로 설정 가져오기
                $siteName = $site->getSetting('site_name', $site->name ?? 'SEOom Builder');
                $siteDescription = $site->getSetting('site_description', '');
                $siteKeywords = $site->getSetting('site_keywords', '');
                // 테마 설정
                $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                $isDark = $themeDarkMode === 'dark';
                
                // 로고 설정: 다크 모드일 때 다크 모드 로고 사용
                $siteLogo = $isDark ? ($site->getSetting('site_logo_dark', '') ?: $site->getSetting('site_logo', '')) : $site->getSetting('site_logo', '');
                $siteFavicon = $site->getSetting('site_favicon', '');
                $ogImage = $site->getSetting('og_image', $siteLogo);
                $logoType = $site->getSetting('logo_type', 'image');
                $logoDesktopSize = $site->getSetting('logo_desktop_size', '300');
                $logoMobileSize = $site->getSetting('logo_mobile_size', '200');
                $themeTop = $site->getSetting('theme_top', 'design1');
                $themeTopHeaderShow = $site->getSetting('theme_top_header_show', '0');
                $headerSticky = $site->getSetting('header_sticky', '0');
                $themeBottom = $site->getSetting('theme_bottom', 'theme03');
                $themeMain = $site->getSetting('theme_main', 'round');
                $themeFullWidth = $site->getSetting('theme_full_width', '0') == '1';
                $widgetShadow = $site->getSetting('widget_shadow', '1') == '1';
                
                // 사이드 위젯 기능이 없는 플랜의 경우 사이드바를 'none'으로 강제 설정
                try {
                    if (!$site->hasFeature('sidebar_widgets')) {
                        $themeSidebar = 'none';
                        // 설정도 강제로 업데이트
                        $site->setSetting('theme_sidebar', 'none');
                    } else {
                        $themeSidebar = $site->getSetting('theme_sidebar', 'left');
                    }
                } catch (\Exception $e) {
                    $themeSidebar = 'left';
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
                
                // 투명헤더 설정 (기본값, 나중에 재정의됨)
                $headerTransparent = false;
                $isHomePage = false;
            }
        } catch (\Exception $e) {
            // 에러 발생 시 기본값 사용
            $siteName = 'SEOom Builder';
            $siteDescription = '';
            $siteKeywords = '';
            $siteLogo = '';
            $siteFavicon = '';
            $ogImage = '';
            $logoType = 'image';
            $logoDesktopSize = '300';
            $logoMobileSize = '200';
            $themeDarkMode = 'light';
            $themeTop = 'design1';
            $themeTopHeaderShow = '0';
            $headerSticky = '0';
            $themeBottom = 'theme03';
            $themeMain = 'round';
            $themeFullWidth = false;
            $themeSidebar = 'left';
            $colorLightHeaderText = '#000000';
            $colorLightHeaderBg = '#ffffff';
            $colorLightFooterText = '#000000';
            $colorLightFooterBg = '#f8f9fa';
            $colorLightBodyText = '#000000';
            $colorLightBodyBg = '#f8f9fa';
            $colorLightPointMain = '#0d6efd';
            $colorLightPointBg = '#000000';
            $colorDarkHeaderText = '#ffffff';
            $colorDarkHeaderBg = '#000000';
            $colorDarkFooterText = '#ffffff';
            $colorDarkFooterBg = '#000000';
            $colorDarkBodyText = '#ffffff';
            $colorDarkBodyBg = '#000000';
            $colorDarkPointMain = '#ffffff';
            $colorDarkPointBg = '#212529';
            $fontDesign = 'noto-sans';
            $fontSize = 'normal';
            $headerTransparent = false;
            $isHomePage = false;
        }
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
        try {
            $googleVerification = (isset($site) && $site && $site->id) ? $site->getSetting('google_site_verification', '') : '';
            $naverVerification = (isset($site) && $site && $site->id) ? $site->getSetting('naver_site_verification', '') : '';
            $daumVerification = (isset($site) && $site && $site->id) ? $site->getSetting('daum_site_verification', '') : '';
        } catch (\Exception $e) {
            $googleVerification = '';
            $naverVerification = '';
            $daumVerification = '';
        }
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
        try {
            $googleAnalyticsId = (isset($site) && $site && $site->id) ? $site->getSetting('google_analytics_id', '') : '';
        } catch (\Exception $e) {
            $googleAnalyticsId = '';
        }
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
                --header-text-color: {{ $colorDarkHeaderText ?? '#ffffff' }};
                --header-bg-color: {{ $colorDarkHeaderBg ?? '#000000' }};
                --body-text-color: {{ $colorDarkBodyText ?? '#ffffff' }};
                --body-bg-color: {{ $colorDarkBodyBg ?? '#000000' }};
                --point-main-color: {{ $colorDarkPointMain ?? '#ffffff' }};
                --point-bg-color: {{ $colorDarkPointBg ?? '#212529' }};
            }
            body {
                background-color: var(--body-bg-color);
                color: var(--body-text-color);
            }
            /* .header-transparent-overlay 내부의 .navbar는 제외하고 배경색 설정 */
            .navbar {
                background-color: var(--header-bg-color) !important;
                color: var(--header-text-color);
            }
            /* 투명헤더일 때는 오버라이드됨 (아래 블록에서 처리) */
            .navbar-brand, .navbar-nav .nav-link {
                color: var(--header-text-color) !important;
            }
            
            /* 다크 모드 게시판 스타일 */
            .card.bg-white,
            .card-header.bg-white,
            .bg-white,
            .card.shadow,
            .card.shadow-sm {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
            
            /* 게시판 리스트 요소 배경 */
            .list-group-item,
            .list-group-item-action {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
            
            .list-group-item:hover,
            .list-group-item-action:hover {
                background-color: rgb(53, 53, 53) !important;
                color: #ffffff !important;
            }
            
            /* 게시판 제목 영역 */
            .card-header,
            .card-body {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
            }
            
            /* 텍스트 색상 반전 */
            .text-dark,
            .text-muted {
                color: rgba(255, 255, 255, 0.7) !important;
            }
            
            h1, h2, h3, h4, h5, h6,
            .h1, .h2, .h3, .h4, .h5, .h6 {
                color: #ffffff !important;
            }
            
            /* 입력 필드 */
            .form-control,
            .form-select {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
                border-color: rgba(255, 255, 255, 0.2) !important;
            }
            
            .form-control:focus,
            .form-select:focus {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
                border-color: var(--point-main-color) !important;
            }
            
            /* 모달 다크모드 스타일 */
            .modal-content {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
            
            .modal-header {
                background-color: rgb(53, 53, 53) !important;
                color: #ffffff !important;
                border-bottom-color: rgba(255, 255, 255, 0.1) !important;
            }
            
            .modal-body {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
            }
            
            .modal-footer {
                background-color: rgb(43, 43, 43) !important;
                border-top-color: rgba(255, 255, 255, 0.1) !important;
            }
            
            .modal-title {
                color: #ffffff !important;
            }
            
            .btn-close {
                filter: invert(1) grayscale(100%) brightness(200%);
            }
            
            /* 알림창 다크모드 */
            .alert {
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
            
            .alert-info {
                background-color: rgba(13, 202, 240, 0.15) !important;
                color: #6edff6 !important;
            }
            
            .alert-warning {
                background-color: rgba(255, 193, 7, 0.15) !important;
                color: #ffca2c !important;
            }
            
            .alert-success {
                background-color: rgba(25, 135, 84, 0.15) !important;
                color: #75b798 !important;
            }
            
            .alert-danger {
                background-color: rgba(220, 53, 69, 0.15) !important;
                color: #ea868f !important;
            }
            
            /* 포인트 컬러가 화이트일 때 버튼 텍스트 색상 자동 조정 */
            @php
                $pointColorRgb = $colorDarkPointMain ?? '#ffffff';
                // #ffffff 또는 white인 경우 검은색 텍스트 사용
                $isWhitePoint = (strtolower($pointColorRgb) === '#ffffff' || strtolower($pointColorRgb) === 'white' || strtolower($pointColorRgb) === '#fff');
            @endphp
            
            @if($isWhitePoint)
                .btn-primary,
                .btn[style*="background-color: {{ $colorDarkPointMain }}"],
                .btn[style*="background-color: var(--point-main-color)"],
                button[style*="background-color: {{ $colorDarkPointMain }}"],
                a[style*="background-color: {{ $colorDarkPointMain }}"] {
                    color: #000000 !important;
                }
                
                .btn-primary:hover,
                .btn-primary:focus,
                .btn[style*="background-color: {{ $colorDarkPointMain }}"]:hover,
                .btn[style*="background-color: var(--point-main-color)"]:hover,
                button[style*="background-color: {{ $colorDarkPointMain }}"]:hover,
                a[style*="background-color: {{ $colorDarkPointMain }}"]:hover {
                    color: #000000 !important;
                }
            @endif
            
            /* 알림 메시지 */
            .alert {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
            
            .alert-info {
                background-color: rgba(13, 202, 240, 0.2) !important;
                color: #0dcaf0 !important;
            }
            
            .alert-success {
                background-color: rgba(25, 135, 84, 0.2) !important;
                color: #198754 !important;
            }
            
            .alert-warning {
                background-color: rgba(255, 193, 7, 0.2) !important;
                color: #ffc107 !important;
            }
            
            .alert-danger {
                background-color: rgba(220, 53, 69, 0.2) !important;
                color: #dc3545 !important;
            }
            
            /* 테이블 */
            .table {
                color: #ffffff !important;
                background-color: rgb(43, 43, 43) !important;
            }
            
            .table td,
            .table th {
                border-color: rgba(255, 255, 255, 0.1) !important;
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
            }
            
            /* 테이블 bg-light 클래스 다크 모드 스타일 */
            .table .bg-light,
            .table td.bg-light,
            .table th.bg-light {
                background-color: rgb(53, 53, 53) !important;
                color: #ffffff !important;
            }
            
            /* 테이블 내부 모든 텍스트 색상 강제 적용 */
            .table *,
            .table a,
            .table span,
            .table div,
            .table p {
                color: #ffffff !important;
            }
            
            /* 테이블 링크 호버 색상 */
            .table a:hover {
                color: rgba(255, 255, 255, 0.8) !important;
            }
            
            /* 드롭다운 메뉴 */
            .dropdown-menu {
                background-color: rgb(43, 43, 43) !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
            
            .dropdown-item {
                color: #ffffff !important;
            }
            
            .dropdown-item:hover,
            .dropdown-item:focus {
                background-color: rgb(53, 53, 53) !important;
                color: #ffffff !important;
            }
            
            /* 메인/커스텀 위젯 다크모드 강제 스타일 */
            .widget-card,
            .widget-card .card,
            .widget-card .card-body,
            .main-widget-area .card,
            .main-widget-area .card-body,
            .custom-page-widget .card,
            .custom-page-widget .card-body,
            [class*="widget"] .card,
            [class*="widget"] .card-body {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
            }
            
            /* 모든 위젯 텍스트 색상 강제 */
            .widget-card *,
            .main-widget-area *,
            .custom-page-widget *,
            [class*="widget"] p,
            [class*="widget"] span,
            [class*="widget"] div,
            [class*="widget"] h1,
            [class*="widget"] h2,
            [class*="widget"] h3,
            [class*="widget"] h4,
            [class*="widget"] h5,
            [class*="widget"] h6,
            [class*="widget"] a:not(.btn),
            [class*="widget"] li {
                color: #ffffff !important;
            }
            
            /* 링크 호버 색상 */
            [class*="widget"] a:not(.btn):hover {
                color: rgba(255, 255, 255, 0.8) !important;
            }
            
            /* 사이드바 위젯도 동일하게 적용 */
            .sidebar .card,
            .sidebar .card-body,
            .sidebar .card-header {
                background-color: rgb(43, 43, 43) !important;
                color: #ffffff !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
            
            .sidebar * {
                color: #ffffff !important;
            }
            
            .sidebar a:not(.btn):hover {
                color: rgba(255, 255, 255, 0.8) !important;
            }
        @endif
        @if($themeDarkMode !== 'dark')
            :root {
                --header-text-color: {{ $colorLightHeaderText ?? '#000000' }};
                --header-bg-color: {{ $colorLightHeaderBg ?? '#ffffff' }};
                --body-text-color: {{ $colorLightBodyText ?? '#000000' }};
                --body-bg-color: {{ $colorLightBodyBg ?? '#f8f9fa' }};
                --point-main-color: {{ $colorLightPointMain ?? '#0d6efd' }};
                --point-bg-color: {{ $colorLightPointBg ?? '#000000' }};
            }
            body {
                background-color: var(--body-bg-color);
                color: var(--body-text-color);
            }
            /* .header-transparent-overlay 내부의 .navbar는 제외하고 배경색 설정 */
            .navbar {
                background-color: var(--header-bg-color) !important;
            }
            /* 투명헤더일 때는 오버라이드됨 (아래 블록에서 처리) */
        @endif
        
        /* 투명헤더일 때 CSS 변수 오버라이드 (PHP에서 설정한 CSS 변수보다 나중에 적용) */
        /* 더 강력하게 :root와 html 모두에서 오버라이드 */
        {{-- @if($headerTransparent && $isHomePage) --}}
            {{-- :root,
            html,
            html body {
                --header-bg-color: transparent !important;
            }
            
            /* CSS 변수를 사용하는 .navbar 규칙을 완전히 무시 */
            /* .navbar { background-color: var(--header-bg-color) !important; } 규칙보다 더 높은 우선순위 */
            /* 가장 구체적인 선택자로 .header-transparent-overlay 내부의 .navbar에 직접 transparent 설정 */
            html body .header-transparent-overlay .navbar,
            html body .header-transparent-overlay nav.navbar,
            html body div.header-transparent-overlay .navbar,
            html body div.header-transparent-overlay nav.navbar,
            html body .header-transparent-overlay nav,
            html body .header-transparent-overlay .navbar.navbar-expand-lg,
            html body .header-transparent-overlay nav.navbar.navbar-expand-lg,
            html body .header-transparent-overlay nav.navbar.navbar-dark,
            html body .header-transparent-overlay nav.navbar.navbar-expand-lg.navbar-dark,
            html body .header-transparent-overlay nav.pc-header,
            html body .header-transparent-overlay .pc-header {
                /* CSS 변수를 무시하고 직접 transparent 설정 (가장 강력한 방법) */
                background-color: transparent !important;
                background: none !important;
                background-image: none !important;
                --header-bg-color: transparent !important;
            } --}}
        {{-- @endif --}}
        
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
            
            // 반응형 폰트 사이즈 - clamp(최소, 선호, 최대)를 사용하여 화면 크기에 따라 자연스럽게 조절
            $fontSizes = [
                'small' => 'clamp(12px, 2vw, 14px)',
                'normal' => 'clamp(14px, 2.5vw, 16px)',
                'large' => 'clamp(15px, 3vw, 18px)',
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
            /* 위젯 최상위 컨테이너 - 그림자 클래스와 독립적으로 라운드 적용 */
            .card.mb-3,
            .card.mb-0,
            .widget-card,
            .main-widget-container,
            .sidebar-widget-container,
            .mb-3:not(.rounded-0),
            .mb-0:not(.rounded-0):not(.no-shadow-widget),
            .image-slide-wrapper:not(.rounded-0),
            .block-slide-wrapper:not(.rounded-0) {
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
            .bg-white.p-3.rounded,
            .post-list-container,
            .card.bg-white,
            .card:not(.rounded-0),
            .card.mb-4 {
                border-radius: 0.5rem !important;
            }
            
            /* 게시판 카드 라운드 적용 (더 구체적인 선택자) */
            .card.bg-white[style*="border-radius"],
            .card.bg-white:not(.rounded-0),
            .card:not(.rounded-0) {
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
            .post-card, .menu-item,
            .card-header, .card-body {
                border-radius: 0.5rem !important;
            }
            
            /* 카드 헤더와 바디의 라운드 조정 */
            .card:not(.rounded-0) .card-header:first-child {
                border-top-left-radius: 0.5rem !important;
                border-top-right-radius: 0.5rem !important;
            }
            
            .card:not(.rounded-0) .card-body:last-child {
                border-bottom-left-radius: 0.5rem !important;
                border-bottom-right-radius: 0.5rem !important;
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
            .card.mb-3 .nav-tabs,
            .card.mb-3 .tab-content,
            .card.mb-3 .tab-pane,
            .card.mb-3 .list-group,
            .card.mb-3 .list-group-item,
            .card.mb-3 .table,
            .card.mb-3 .table tbody tr,
            .card.mb-3 .table td,
            .card.mb-3 .table th,
            .card.mb-0 .nav-tabs,
            .card.mb-0 .tab-content,
            .card.mb-0 .tab-pane,
            .card.mb-0 .list-group,
            .card.mb-0 .list-group-item,
            .card.mb-0 .table,
            .card.mb-0 .table tbody tr,
            .card.mb-0 .table td,
            .card.mb-0 .table th {
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
        @endif
        
        /* 위젯 그림자 통일 및 opacity 조정 */
        /* 모든 위젯에 동일한 그림자 적용 - 시각적 통일성 확보 */
        @if(isset($widgetShadow) && $widgetShadow)
            /* 모든 shadow-sm 클래스를 가진 요소에 그림자 적용 */
            /* 그림자 값을 약간 더 진하게 조정하여 배경색 차이를 보완 */
            .shadow-sm,
            .card.shadow-sm,
            .mb-3.shadow-sm,
            div.mb-3.shadow-sm,
            .shadow-sm.mb-3,
            div.shadow-sm.mb-3,
            .image-slide-wrapper.shadow-sm,
            .block-slide-wrapper.shadow-sm,
            div[class*="mb-3"][class*="shadow-sm"],
            div[class*="shadow-sm"][class*="mb-3"] {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
            }
        @endif
        
        /* 배경색 없음 위젯은 그림자 제거 */
        .no-shadow-widget {
            box-shadow: none !important;
        }
        
        /* 위젯 애니메이션 스타일 - 스크롤 시 트리거 */
        .widget-animate {
            opacity: 0;
        }
        
        /* 초기 상태 - 애니메이션 방향에 따른 시작 위치 */
        .widget-animate-left {
            transform: translateX(-50px);
        }
        
        .widget-animate-right {
            transform: translateX(50px);
        }
        
        .widget-animate-up {
            transform: translateY(50px);
        }
        
        .widget-animate-down {
            transform: translateY(-50px);
        }
        
        /* 화면에 보일 때 애니메이션 실행 */
        .widget-animate-left.widget-animate-visible {
            animation: slideInLeft 0.6s ease-out forwards;
        }
        
        .widget-animate-right.widget-animate-visible {
            animation: slideInRight 0.6s ease-out forwards;
        }
        
        .widget-animate-up.widget-animate-visible {
            animation: slideInUp 0.6s ease-out forwards;
        }
        
        .widget-animate-down.widget-animate-visible {
            animation: slideInDown 0.6s ease-out forwards;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @if($themeMain === 'round')
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
        
        /* 가로 100% 컨테이너 라운드 제거 (데스크탑 + 모바일) */
        .container-fluid.px-0,
        .container-fluid.px-0 .card,
        .container-fluid.px-0 .widget-card,
        .container-fluid.px-0 .card.shadow-sm,
        .container-fluid.px-0 .main-widget-container .card,
        .container-fluid.px-0 [class*="banner-item"],
        .container-fluid.px-0 .banner-link,
        .container-fluid.px-0 .banner-image,
        .container-fluid.px-0 img,
        .container-fluid.px-0 .card-header,
        .container-fluid.px-0 .card-body,
        .container-fluid.px-0 .card-footer {
            border-radius: 0 !important;
            -webkit-border-radius: 0 !important;
            -moz-border-radius: 0 !important;
        }
        
        /* 가로 100% 컨테이너 내 모든 요소 라운드 제거 */
        .container-fluid.px-0 .rounded,
        .container-fluid.px-0 .rounded-top,
        .container-fluid.px-0 .rounded-bottom,
        .container-fluid.px-0 .rounded-start,
        .container-fluid.px-0 .rounded-end,
        .container-fluid.px-0 .rounded-0,
        .container-fluid.px-0 .rounded-1,
        .container-fluid.px-0 .rounded-2,
        .container-fluid.px-0 .rounded-3,
        .container-fluid.px-0 .rounded-4,
        .container-fluid.px-0 .rounded-5,
        .container-fluid.px-0 [class*="rounded-"],
        .container-fluid.px-0 [style*="border-radius"] {
            border-radius: 0 !important;
            -webkit-border-radius: 0 !important;
            -moz-border-radius: 0 !important;
        }
        
        /* 모바일에서 가로 100% 컨테이너 추가 스타일 */
        @media (max-width: 999px) {
            .container-fluid.px-0 .card,
            .container-fluid.px-0 .widget-card,
            .container-fluid.px-0 .card.shadow-sm,
            .container-fluid.px-0 .main-widget-container .card,
            .container-fluid.px-0 [class*="banner-item"],
            .container-fluid.px-0 .banner-link,
            .container-fluid.px-0 .banner-image,
            .container-fluid.px-0 img,
            .container-fluid.px-0 *,
            .container-fluid.px-0 .card-header,
            .container-fluid.px-0 .card-body,
            .container-fluid.px-0 .card-footer {
                border-radius: 0 !important;
                -webkit-border-radius: 0 !important;
                -moz-border-radius: 0 !important;
            }
            
            /* 가로 100% 컨테이너 내 모든 요소 라운드 제거 - 강화 */
            .container-fluid.px-0 .rounded,
            .container-fluid.px-0 .rounded-top,
            .container-fluid.px-0 .rounded-bottom,
            .container-fluid.px-0 .rounded-start,
            .container-fluid.px-0 .rounded-end,
            .container-fluid.px-0 .rounded-0,
            .container-fluid.px-0 .rounded-1,
            .container-fluid.px-0 .rounded-2,
            .container-fluid.px-0 .rounded-3,
            .container-fluid.px-0 .rounded-4,
            .container-fluid.px-0 .rounded-5,
            .container-fluid.px-0 [class*="rounded-"],
            .container-fluid.px-0 [style*="border-radius"] {
                border-radius: 0 !important;
                -webkit-border-radius: 0 !important;
                -moz-border-radius: 0 !important;
            }
            
            /* 사이드바 로그인 위젯 모바일에서 숨기기 */
            .sidebar-user-widget {
                display: none !important;
            }
        }
        
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
            $customCodeHead = (isset($site) && $site && $site->id && \Illuminate\Support\Facades\Schema::hasTable('custom_codes')) ? \App\Models\CustomCode::getByLocation($site->id, 'head') : null;
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
            $customCodeHeadCss = (isset($site) && $site && $site->id && \Illuminate\Support\Facades\Schema::hasTable('custom_codes')) ? \App\Models\CustomCode::getByLocation($site->id, 'head_css') : null;
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
            $customCodeHeadJs = (isset($site) && $site && $site->id && \Illuminate\Support\Facades\Schema::hasTable('custom_codes')) ? \App\Models\CustomCode::getByLocation($site->id, 'head_js') : null;
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
@push('styles')
<style>
    .full-height-container {
        max-height: 100vh;
        overflow: hidden;
    }
    .full-height-container .main-widget-container,
    .full-height-container .custom-page-widget-container {
        max-height: 100vh;
    }
    .full-height-container .col-md-1,
    .full-height-container .col-md-2,
    .full-height-container .col-md-3,
    .full-height-container .col-md-4,
    .full-height-container .col-md-6,
    .full-height-container .col-md-12 {
        max-height: 100%;
    }
</style>
@endpush
    @php
        // 체크박스 값 비교 (문자열 '1' 또는 숫자 1 모두 처리)
        $showTopHeader = ($themeTopHeaderShow == '1' || $themeTopHeaderShow === '1' || $themeTopHeaderShow === 1);
        $isHeaderSticky = ($headerSticky == '1' || $headerSticky === '1' || $headerSticky === 1);
        // 투명헤더 설정 (최적화 완료)
        $headerTransparent = $site->getSetting('header_transparent', '0') == '1';
        
        // 사이드바 설정 확인 (투명헤더는 사이드바가 없을 때만 적용 가능)
        $hasSidebar = $themeSidebar !== 'none';
        if ($hasSidebar) {
            $headerTransparent = false;
        }
        
        // 메인 페이지인지 확인 - 루트 경로(/)와 /site/{site} 모두 HomeController::index를 호출하므로 동일하게 처리
        $currentPath = request()->path();
        $currentHost = request()->getHost();
        $isCustomDomain = $site->domain && ($currentHost === $site->domain || $currentHost === 'www.' . $site->domain);
        
        // 루트 경로(/)와 /site/{site} 모두 메인 페이지로 간주
        // 커스텀 도메인을 연결한 경우 루트 경로가 메인 페이지
        $isHomePage = request()->routeIs('home') 
            || request()->routeIs('home.root')  // 루트 경로 라우트 이름
            || $currentPath === '/' 
            || $currentPath === ''
            || ($isCustomDomain && ($currentPath === '/' || $currentPath === ''))
            || (request()->segment(1) === 'site' && request()->segment(2) !== null && request()->segment(3) === null);
        
        // 추가 확인: 현재 라우트의 액션이 HomeController::index인지 확인
        $currentRoute = request()->route();
        if ($currentRoute && !$isHomePage) {
            $action = $currentRoute->getActionName();
            if ($action === 'App\\Http\\Controllers\\HomeController@index' || 
                $action === 'App\\Http\\Controllers\\HomeController::index' ||
                (is_string($action) && str_contains($action, 'HomeController') && str_contains($action, 'index'))) {
                $isHomePage = true;
            }
        }
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
        @php
            // 투명헤더가 활성화되면 모든 페이지에서 최상단 헤더도 투명하게
            $topHeaderBg = $headerTransparent ? 'transparent' : $topBarBg;
            $topHeaderBorder = $headerTransparent ? 'none' : '1px solid rgba(0,0,0,0.1)';
            // 투명헤더일 때 최상단 헤더도 absolute로 설정
            $topHeaderPosition = $headerTransparent ? 'position: absolute; top: 0; left: 0; right: 0; width: 100%;' : 'position: relative;';
        @endphp
        <div class="top-header-bar d-none d-xl-block" style="background-color: {{ $topHeaderBg }}; color: {{ $topBarText }}; padding: 0.5rem 0; border-bottom: {{ $topHeaderBorder }}; {{ $topHeaderPosition }} z-index: 1051;">
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
                                            <li><a class="dropdown-item" href="{{ $site->getAdminDashboardUrl() }}"><i class="bi bi-speedometer2 me-2"></i>관리자</a></li>
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
        
        // 투명헤더가 활성화되고 메인 페이지인 경우 헤더를 absolute로 위치시켜 첫 번째 컨테이너 위로 오버레이
        $headerWrapperStyle = '';
        $headerWrapperClass = '';
        if ($headerTransparent) {
            // 투명헤더: absolute position으로 첫 번째 컨테이너 위에 오버레이 (모든 페이지에서 적용)
            // 최상단 헤더가 활성화되어 있으면 top을 최상단 헤더 높이만큼 조정 (약 40px)
            $navTopPosition = $showTopHeader ? '40px' : '0';
            $headerWrapperStyle = 'position: absolute; top: ' . $navTopPosition . '; left: 0; right: 0; z-index: 1030; width: 100%;';
            $headerWrapperClass = 'header-transparent-overlay';
            if ($isHeaderSticky) {
                // sticky일 때는 스크롤 시 fixed로 변경되도록 클래스 추가
                $headerWrapperClass .= ' header-transparent-sticky-overlay';
            }
        } else {
            // 일반 헤더: relative 또는 sticky
            $headerWrapperStyle = 'position: relative; z-index: 1030;';
            if ($isHeaderSticky) {
                $headerWrapperStyle = 'position: sticky; top: ' . $stickyTop . '; z-index: 1030;';
                $headerWrapperClass = 'sticky-header-wrapper';
            }
        }
    @endphp
    <div class="{{ $headerWrapperClass }}" style="{{ $headerWrapperStyle }}">
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
    @php
        // 메인 위젯 컨테이너 첫 번째가 세로 100%인지 확인
        $firstContainerFullHeight = isset($mainWidgetContainers) && $mainWidgetContainers->isNotEmpty() && ($mainWidgetContainers->first()->full_height ?? false);
        // 메인 위젯 컨테이너 첫 번째가 가로 100%인지 확인 (사이드바가 없을 때만 적용)
        $firstContainerFullWidth = isset($mainWidgetContainers) && $mainWidgetContainers->isNotEmpty() && ($mainWidgetContainers->first()->full_width ?? false) && ($themeSidebar === 'none');
        
        // 커스텀 페이지 컨테이너 첫 번째가 세로 100%인지 확인
        $customPageFirstFullHeight = isset($containers) && $containers->isNotEmpty() && ($containers->first()->full_height ?? false);
        // 커스텀 페이지 컨테이너 첫 번째가 가로 100%인지 확인 (사이드바가 없을 때만 적용)
        $customPageFirstFullWidth = isset($containers) && $containers->isNotEmpty() && ($containers->first()->full_width ?? false) && ($themeSidebar === 'none');
        
        // 투명헤더이거나 첫 번째 컨테이너가 세로 100% 또는 가로 100%일 때 상단 여백 제거
        $removeTopMargin = $headerTransparent || $firstContainerFullHeight || $firstContainerFullWidth || $customPageFirstFullHeight || $customPageFirstFullWidth;
    @endphp
    
    {{-- 샘플 사이트 안내 배너 (상단 고정) --}}
    @if(isset($site) && $site->isSample())
        <div class="bg-warning bg-opacity-25 border-bottom border-warning py-2">
            <div class="container d-flex align-items-center justify-content-center gap-2 text-center flex-wrap">
                <i class="bi bi-info-circle text-warning"></i>
                <span><strong>샘플 사이트</strong> - 미리보기 전용입니다.</span>
                <a href="{{ route('user-sites.select-plan', ['site' => \App\Models\Site::getMasterSite()?->slug ?? 'master']) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>직접 만들기
                </a>
            </div>
        </div>
    @endif
    
    <main class="container {{ $removeTopMargin ? '' : 'my-4' }} flex-grow-1">
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
            
            @php
                // 사이드바 모바일 표시 설정
                $sidebarMobileDisplay = $site->getSetting('sidebar_mobile_display', 'top');
                // 사이드바가 활성화되어 있을 때만 모바일 표시 옵션 적용
                $showMobileSidebar = ($themeSidebar !== 'none') && ($sidebarMobileDisplay !== 'none');
                
                // 사이드바 위젯 데이터 (모바일 사이드바에도 사용)
                try {
                    $sidebarWidgets = \App\Models\SidebarWidget::where('site_id', $site->id)
                        ->where('is_active', true)
                        ->orderBy('order', 'asc')
                        ->get();
                } catch (\Exception $e) {
                    $sidebarWidgets = collect();
                }
            @endphp
            
            @if($themeSidebar === 'left')
                <aside class="col-md-3 mb-4 {{ $showMobileSidebar ? 'd-md-block d-none' : '' }}">
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
                    // 현재 라우트가 홈페이지인지 확인 - 루트 경로(/)와 /site/{site} 모두 HomeController::index를 호출하므로 동일하게 처리
                    $currentPathForCheck = request()->path();
                    $currentHostForCheck = request()->getHost();
                    $isCustomDomainForCheck = $site->domain && ($currentHostForCheck === $site->domain || $currentHostForCheck === 'www.' . $site->domain);
                    
                    // 루트 경로(/)와 /site/{site} 모두 메인 페이지로 간주
                    // 커스텀 도메인을 연결한 경우 루트 경로가 메인 페이지
                    $isHomePage = request()->routeIs('home') 
                        || request()->routeIs('home.root')  // 루트 경로 라우트 이름
                        || $currentPathForCheck === '/' 
                        || $currentPathForCheck === ''
                        || ($isCustomDomainForCheck && ($currentPathForCheck === '/' || $currentPathForCheck === ''))
                        || (request()->segment(1) === 'site' && request()->segment(2) !== null && request()->segment(3) === null);
                    
                    // 추가 확인: 현재 라우트의 액션이 HomeController::index인지 확인
                    $currentRouteForCheck = request()->route();
                    if ($currentRouteForCheck && !$isHomePage) {
                        $action = $currentRouteForCheck->getActionName();
                        if ($action === 'App\\Http\\Controllers\\HomeController@index' || 
                            $action === 'App\\Http\\Controllers\\HomeController::index' ||
                            (is_string($action) && str_contains($action, 'HomeController') && str_contains($action, 'index'))) {
                            $isHomePage = true;
                        }
                    }
                    // 내정보 관련 페이지인지 확인
                    $isUserProfilePage = request()->routeIs('users.profile', 'users.point-history', 'users.saved-posts', 'users.my-posts', 'users.my-comments');
                    // 알림 및 쪽지함 페이지인지 확인
                    $isNotificationOrMessagePage = request()->routeIs('notifications.index', 'messages.index', 'messages.show');
                    
                @endphp
                
                {{-- 모바일 사이드바 (본문 상단) --}}
                @if($showMobileSidebar && $sidebarMobileDisplay === 'top')
                    <aside class="d-md-none mb-4">
                        {{-- 사용자 위젯 --}}
                        @if($site->getSetting('enable_sidebar_login_widget', true))
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        @endif
                        
                        {{-- 커스텀 코드: 사이드바 상단 --}}
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
                        @foreach($sidebarWidgets as $widget)
                            <x-sidebar-widget :widget="$widget" :site="$site" />
                        @endforeach
                        
                        {{-- 사이드바 하단 배너 --}}
                        <x-banner-display :site="$site" location="sidebar_bottom" />
                    </aside>
                @endif
                
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
                
                {{-- 모바일 사이드바 (본문 하단) --}}
                @if($showMobileSidebar && $sidebarMobileDisplay === 'bottom')
                    <aside class="d-md-none mt-4">
                        {{-- 사용자 위젯 --}}
                        @if($site->getSetting('enable_sidebar_login_widget', true))
                            <x-sidebar-user-widget :site="$site" :themeDarkMode="$themeDarkMode" />
                        @endif
                        
                        {{-- 커스텀 코드: 사이드바 상단 --}}
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
                        @foreach($sidebarWidgets as $widget)
                            <x-sidebar-widget :widget="$widget" :site="$site" />
                        @endforeach
                        
                        {{-- 사이드바 하단 배너 --}}
                        <x-banner-display :site="$site" location="sidebar_bottom" />
                    </aside>
                @endif
            </div>

            @if($themeSidebar === 'right')
                <aside class="col-md-3 mb-4 {{ $showMobileSidebar ? 'd-md-block d-none' : '' }}">
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
    
    @php
        // 투명헤더 CSS/JS 출력 전에 변수 재확인 (자식 뷰에서 재정의될 수 있으므로)
        // 투명헤더 설정 (최적화 완료)
        $headerTransparentFinal = $site->getSetting('header_transparent', '0') == '1';
        $themeSidebarFinal = $site->getSetting('theme_sidebar', 'none');
        if ($themeSidebarFinal !== 'none') {
            $headerTransparentFinal = false;
        }
    @endphp
    
    @if($headerTransparentFinal)
    <style>
        /* 투명헤더 오버레이 스타일 - 모든 페이지에서 적용 */
        /* PHP에서 생성된 .navbar 규칙보다 나중에 로드되도록 하기 위해 가장 마지막에 배치 */
        .header-transparent-overlay {
            position: absolute !important;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        
        /* 모바일에서 투명헤더 시 최상단 헤더 영역 없이 nav를 상단에 배치 */
        /* 최상단 헤더는 d-xl-block으로 모바일에서 숨겨지므로 nav를 top: 0으로 설정 */
        @media (max-width: 1199px) {
            .header-transparent-overlay {
                top: 0 !important;
            }
        }
        
        .header-transparent-overlay {
            width: 100%;
            background: none !important;
            background-color: transparent !important;
        }
        
        /* :root CSS 변수 오버라이드 - 투명헤더일 때 (가장 강력한 방법) */
        /* PHP에서 설정한 CSS 변수를 완전히 오버라이드 */
        /* !important를 사용하여 모든 CSS 변수 설정을 덮어씀 */
        :root,
        html,
        html body,
        html body .header-transparent-overlay {
            --header-bg-color: transparent !important;
        }
        
        /* 헤더 오버레이 내부의 모든 요소에도 CSS 변수 오버라이드 */
        .header-transparent-overlay,
        .header-transparent-overlay *,
        .header-transparent-overlay nav,
        .header-transparent-overlay .navbar,
        .header-transparent-overlay nav.navbar,
        .header-transparent-overlay nav.pc-header,
        .header-transparent-overlay .pc-header {
            --header-bg-color: transparent !important;
        }
        
        /* .navbar { background-color: var(--header-bg-color) !important; } 규칙을 직접 오버라이드 */
        /* 가장 구체적인 선택자로 CSS 변수 기반 배경색 규칙을 완전히 무시 */
        /* CSS 변수를 사용하는 규칙보다 더 높은 우선순위를 가진 직접 배경색 설정 */
        html body .header-transparent-overlay .navbar,
        html body .header-transparent-overlay nav.navbar,
        html body .header-transparent-overlay nav,
        html body .header-transparent-overlay .navbar.navbar-expand-lg,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg,
        html body .header-transparent-overlay nav.navbar.navbar-dark,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg.navbar-dark {
            /* CSS 변수를 무시하고 직접 transparent 설정 */
            background-color: transparent !important;
            background: none !important;
            background-image: none !important;
            /* CSS 변수도 함께 오버라이드 */
            --header-bg-color: transparent !important;
        }
        
        /* 투명헤더일 때 :root의 헤더 배경 변수 무시 - 모든 nav 요소에 강제 적용 */
        /* .navbar { background-color: var(--header-bg-color) !important; } 규칙보다 더 높은 우선순위 */
        /* 더 구체적인 선택자로 CSS 변수 기반 배경색도 오버라이드 */
        html body .header-transparent-overlay nav,
        html body .header-transparent-overlay .navbar,
        html body .header-transparent-overlay nav.navbar,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg,
        html body .header-transparent-overlay nav.navbar.navbar-dark,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg.navbar-dark,
        html body .header-transparent-overlay nav.pc-header,
        html body .header-transparent-overlay .pc-header,
        html body .header-transparent-overlay nav[style*="background-color"],
        html body .header-transparent-overlay .navbar[style*="background-color"],
        html body .header-transparent-overlay nav[style],
        html body .header-transparent-overlay .navbar[style],
        /* 가장 구체적인 선택자로 .navbar 규칙을 완전히 오버라이드 */
        /* .navbar { background-color: var(--header-bg-color) !important; } 규칙보다 더 높은 우선순위 */
        html body div.header-transparent-overlay nav.navbar,
        html body div.header-transparent-overlay .navbar,
        html body div.header-transparent-overlay nav.navbar.navbar-expand-lg,
        html body div.header-transparent-overlay nav.navbar.navbar-dark,
        html body div.header-transparent-overlay nav.navbar.navbar-expand-lg.navbar-dark,
        /* CSS 변수를 사용하는 .navbar 규칙을 직접 오버라이드 (가장 강력) */
        html body div.header-transparent-overlay nav.navbar[class*="navbar"],
        html body div.header-transparent-overlay .navbar[class*="navbar"] {
            background-color: transparent !important;
            background: none !important;
            background-image: none !important;
            --header-bg-color: transparent !important;
        }
        
        /* CSS 변수를 사용하는 경우에도 투명하게 - 더 구체적인 선택자 */
        html body .header-transparent-overlay nav.navbar,
        html body .header-transparent-overlay .navbar,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg,
        html body .header-transparent-overlay nav.navbar.navbar-dark,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg.navbar-dark {
            background-color: transparent !important;
            --header-bg-color: transparent !important;
        }
        
        /* 인라인 스타일이 있어도 투명하게 강제 적용 - 더 구체적인 선택자 사용 */
        /* CSS 변수 기반 배경색도 오버라이드하는 가장 구체적인 선택자 */
        /* html body를 추가하여 최고 우선순위 보장 */
        html body .header-transparent-overlay nav[style*="background-color: rgb"],
        html body .header-transparent-overlay nav[style*="background-color: #"],
        html body .header-transparent-overlay .navbar[style*="background-color: rgb"],
        html body .header-transparent-overlay .navbar[style*="background-color: #"],
        html body .header-transparent-overlay nav.navbar[style],
        html body .header-transparent-overlay .navbar[style],
        html body .header-transparent-overlay .pc-header[style],
        html body .header-transparent-overlay nav.pc-header[style],
        /* 메인 페이지에서도 강제 적용 - 더 구체적인 선택자 */
        html body > div.header-transparent-overlay nav,
        html body > div.header-transparent-overlay .navbar,
        html body > div.header-transparent-overlay nav.navbar,
        html body > div.header-transparent-overlay .pc-header,
        html body > div.header-transparent-overlay nav.pc-header,
        /* 모든 가능한 조합에 대해 강제 적용 - CSS 변수 기반 배경색 오버라이드 */
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg,
        html body .header-transparent-overlay nav.navbar.navbar-dark,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg.navbar-dark,
        html body .header-transparent-overlay nav.pc-header.header-transparent-sticky,
        html body .header-transparent-overlay nav.pc-header.navbar-expand-lg.navbar-dark,
        html body .header-transparent-overlay .navbar.navbar-expand-lg,
        html body .header-transparent-overlay .navbar.navbar-dark {
            background-color: transparent !important;
            background: none !important;
            background-image: none !important;
            --header-bg-color: transparent !important;
        }
        
        /* 모든 가능한 nav 요소에 투명 배경 강제 적용 */
        .header-transparent-overlay * {
            --header-bg-color: transparent !important;
        }
        
        /* 스크롤 시 글래스모피즘 배경 허용 - PC 및 모바일 공통 */
        /* .scrolled 클래스가 있을 때는 글래스모피즘 배경이 적용되도록 예외 처리 */
        /* 가장 높은 특정성을 위해 매우 구체적인 선택자 사용 */
        html body .header-transparent-overlay nav.scrolled,
        html body .header-transparent-overlay .navbar.scrolled,
        html body .header-transparent-overlay nav.navbar.scrolled,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg.scrolled,
        html body .header-transparent-overlay nav.navbar.d-xl-none.scrolled,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg.d-xl-none.scrolled,
        html body .header-transparent-overlay nav.header-transparent-sticky.scrolled,
        html body .header-transparent-overlay nav.mobile-header-transparent-sticky.scrolled,
        html body .header-transparent-overlay nav.navbar.mobile-header-transparent-sticky.scrolled,
        html body .header-transparent-overlay nav.navbar.navbar-expand-lg.d-xl-none.mobile-header-transparent-sticky.scrolled,
        html body div.header-transparent-overlay nav.scrolled,
        html body div.header-transparent-overlay .navbar.scrolled,
        html body div.header-transparent-overlay nav.navbar.scrolled,
        html body div.header-transparent-overlay nav.navbar.d-xl-none.scrolled,
        html body div.header-transparent-overlay nav.mobile-header-transparent-sticky.scrolled,
        html body div.header-transparent-overlay nav[class*="mobile-header-transparent"].scrolled,
        html body div.header-transparent-overlay .navbar[class*="mobile-header-transparent"].scrolled {
            background: rgba(255, 255, 255, 0.25) !important;
            background-color: rgba(255, 255, 255, 0.25) !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
            box-shadow: 
                0 4px 30px rgba(0, 0, 0, 0.1),
                0 1px 3px rgba(0, 0, 0, 0.05) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3) !important;
        }
        
        /* 다크 모드일 때 스크롤 시 글래스모피즘 */
        [data-theme="dark"] .header-transparent-overlay nav.scrolled,
        [data-theme="dark"] .header-transparent-overlay .navbar.scrolled,
        [data-theme="dark"] .header-transparent-overlay nav.mobile-header-transparent-sticky.scrolled,
        .theme-dark .header-transparent-overlay nav.scrolled,
        .theme-dark .header-transparent-overlay .navbar.scrolled,
        .theme-dark .header-transparent-overlay nav.mobile-header-transparent-sticky.scrolled,
        body.dark-mode .header-transparent-overlay nav.scrolled,
        body.dark-mode .header-transparent-overlay .navbar.scrolled,
        body.dark-mode .header-transparent-overlay nav.mobile-header-transparent-sticky.scrolled {
            background: rgba(0, 0, 0, 0.6) !important;
            background-color: rgba(0, 0, 0, 0.6) !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
            box-shadow: 
                0 4px 30px rgba(0, 0, 0, 0.3),
                0 1px 3px rgba(0, 0, 0, 0.2) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        
        /* 투명헤더일 때 메인 컨텐츠 영역의 상단 마진 제거 - 모든 페이지에서 적용 */
        @if($headerTransparentFinal)
        main.container {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        @endif
        
        /* 첫 번째 컨테이너가 세로 100%일 때 main 영역의 상단 마진 제거 */
        main.container:has(> .row:first-child > .col-md-9:first-child > .full-height-container:first-child),
        main.container:has(> .full-height-container:first-child) {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        
        /* 투명헤더 sticky 오버레이 - 스크롤 시 fixed로 변경 */
        .header-transparent-sticky-overlay {
            transition: all 0.3s ease;
        }
        
        /* 스크롤 시 wrapper는 투명 - nav에서 글래스모피즘 효과 적용 */
        .header-transparent-sticky-overlay.scrolled {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 1030 !important;
            /* wrapper는 투명하게 - nav 자체에서 글래스모피즘 적용 */
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        
        /* 스크롤 시 nav 배경도 투명하게 (글래스 효과는 wrapper에서 적용) */
        /* 단, navbar에 .scrolled 클래스가 있으면 navbar 자체의 글래스모피즘 스타일이 적용되도록 예외 처리 */
        .header-transparent-sticky-overlay.scrolled .navbar:not(.scrolled),
        .header-transparent-sticky-overlay.scrolled nav.pc-header:not(.scrolled) {
            background-color: transparent !important;
            background: transparent !important;
        }
        
        /* 모바일에서 글래스 효과 적용 (스크롤 전에도 적용) */
        @media (max-width: 1199px) {
            /* 모바일에서는 스크롤 전에도 약간의 글래스 배경 적용하여 가독성 확보 */
            .header-transparent-sticky-overlay {
                background: rgba(255, 255, 255, 0.15) !important;
                backdrop-filter: blur(8px) saturate(120%);
                -webkit-backdrop-filter: blur(8px) saturate(120%);
            }
            @if($themeDarkMode === 'dark')
            .header-transparent-sticky-overlay {
                background: rgba(0, 0, 0, 0.15) !important;
            }
            @endif
            
            /* 모바일에서 스크롤 시 wrapper는 투명하게 - nav에서 글래스모피즘 적용 */
            .header-transparent-sticky-overlay.scrolled {
                background: transparent !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }
        }
        
        /* 투명헤더 + 최상단 헤더 + sticky일 때 스크롤 시 최상단 헤더 숨기기 */
        .top-header-bar.transparent-header-scrolled {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        
        /* 투명헤더일 때 첫 번째 컨테이너의 패딩 제거 */
        .first-container-with-transparent-header {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }
    </style>
    <script>
        // 투명헤더 적용 - 최적화된 버전 (성능 개선)
        (function() {
            // 투명 배경 적용 함수 - 필요한 요소만 선택
            function applyTransparentBackground() {
                const headerWrapper = document.querySelector('.header-transparent-overlay');
                if (!headerWrapper) return;
                
                // CSS 변수 오버라이드 (헤더 wrapper에만 적용)
                headerWrapper.style.setProperty('--header-bg-color', 'transparent', 'important');
                
                // nav 요소만 선택 (성능을 위해 * 선택자 제거)
                const navElements = headerWrapper.querySelectorAll('nav, .navbar, .pc-header');
                navElements.forEach(function(nav) {
                    nav.style.setProperty('--header-bg-color', 'transparent', 'important');
                    nav.style.setProperty('background-color', 'transparent', 'important');
                    nav.style.setProperty('background', 'none', 'important');
                });
            }
            
            // 초기 실행 (한 번만)
            applyTransparentBackground();
            
            // DOMContentLoaded 후 한 번만 재적용
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', applyTransparentBackground);
            }
        })();
        
        // 다크 모드일 때 포인트 컬러가 화이트인 경우 버튼 텍스트 색상 자동 조정
        @if($themeDarkMode === 'dark')
            @php
                $pointColorRgb = $colorDarkPointMain ?? '#ffffff';
                $isWhitePoint = (strtolower($pointColorRgb) === '#ffffff' || strtolower($pointColorRgb) === 'white' || strtolower($pointColorRgb) === '#fff');
            @endphp
            @if($isWhitePoint)
                (function() {
                    function adjustButtonTextColor() {
                        document.querySelectorAll('.btn-primary, .btn-light').forEach(function(btn) {
                            const style = window.getComputedStyle(btn);
                            const bgColor = style.backgroundColor;
                            if (bgColor === 'rgb(255, 255, 255)' || bgColor === '#ffffff') {
                                btn.style.color = '#000000';
                            }
                        });
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', adjustButtonTextColor);
                    } else {
                        adjustButtonTextColor();
                    }
                })();
            @endif
        @endif
        
        document.addEventListener('DOMContentLoaded', function() {
            const headerWrapper = document.querySelector('.header-transparent-overlay');
            
            if (headerWrapper) {
                // 헤더 내부의 nav 요소들 투명하게 설정
                headerWrapper.querySelectorAll('nav, .navbar, .pc-header').forEach(function(nav) {
                    nav.style.setProperty('background-color', 'transparent', 'important');
                    nav.style.setProperty('background', 'none', 'important');
                });
                
                // 첫 번째 컨테이너 여백 제거
                const firstContainer = document.querySelector('.main-widget-container, .custom-page-container');
                if (firstContainer) {
                    const container = firstContainer.closest('.container, .container-fluid');
                    if (container) {
                        container.style.setProperty('margin-top', '0', 'important');
                        container.style.setProperty('padding-top', '0', 'important');
                    }
                }
            }
            
            // sticky 헤더 스크롤 처리 (throttle 적용)
            const stickyOverlay = document.querySelector('.header-transparent-sticky-overlay');
            const topHeaderBar = document.querySelector('.top-header-bar');
            if (stickyOverlay) {
                let ticking = false;
                window.addEventListener('scroll', function() {
                    if (!ticking) {
                        window.requestAnimationFrame(function() {
                            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                            if (scrollTop > 50) {
                                stickyOverlay.classList.add('scrolled');
                                if (topHeaderBar) topHeaderBar.classList.add('transparent-header-scrolled');
                            } else {
                                stickyOverlay.classList.remove('scrolled');
                                if (topHeaderBar) topHeaderBar.classList.remove('transparent-header-scrolled');
                            }
                            ticking = false;
                        });
                        ticking = true;
                    }
                }, { passive: true });
            }
        });
    </script>
    @endif
    
    @if($isHeaderSticky && !$headerTransparentFinal)
    <style>
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
    
    {{-- 첫 번째 컨테이너가 세로 100%일 때 상단 여백 제거 --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 첫 번째 full-height-container 확인
            const firstFullHeightContainer = document.querySelector('.full-height-container');
            if (firstFullHeightContainer) {
                // 첫 번째 컨테이너인지 확인 (이전에 다른 컨테이너가 없는지)
                const allContainers = document.querySelectorAll('[class*="main-widget-container"], [class*="custom-page-widget-container"]');
                if (allContainers.length > 0) {
                    const firstContainer = allContainers[0].closest('.full-height-container, [class*="container"]');
                    if (firstContainer && firstContainer.classList.contains('full-height-container')) {
                        // main 요소 상단 여백 제거
                        const mainElement = document.querySelector('main.container');
                        if (mainElement) {
                            mainElement.style.setProperty('margin-top', '0', 'important');
                            mainElement.style.setProperty('padding-top', '0', 'important');
                            mainElement.classList.remove('my-4');
                        }
                        // 첫 번째 컨테이너 상단 여백도 제거
                        firstFullHeightContainer.style.setProperty('margin-top', '0', 'important');
                        firstFullHeightContainer.style.setProperty('padding-top', '0', 'important');
                    }
                }
            }
        });
    </script>
    
    <!-- 앵커 링크 스무스 스크롤 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 앵커 링크 클릭 시 스무스 스크롤
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');
                    if (targetId && targetId.length > 1) {
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            e.preventDefault();
                            
                            // 헤더 높이 계산 (고정 헤더 고려)
                            const header = document.querySelector('header, .header, .navbar, [data-header]');
                            const headerHeight = header ? header.offsetHeight : 0;
                            
                            // 타겟 위치로 스무스 스크롤
                            const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
                            
                            window.scrollTo({
                                top: targetPosition,
                                behavior: 'smooth'
                            });
                            
                            // URL 해시 업데이트
                            history.pushState(null, null, targetId);
                        }
                    }
                });
            });
            
            // 페이지 로드 시 URL에 해시가 있으면 해당 위치로 스크롤
            if (window.location.hash) {
                const targetElement = document.querySelector(window.location.hash);
                if (targetElement) {
                    setTimeout(() => {
                        const header = document.querySelector('header, .header, .navbar, [data-header]');
                        const headerHeight = header ? header.offsetHeight : 0;
                        const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }, 100);
                }
            }
        });
    </script>
    
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
    
    {{-- 위젯 애니메이션 트리거 JavaScript --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Intersection Observer를 사용하여 위젯이 화면에 나타날 때 애니메이션 트리거
        const animatedWidgets = document.querySelectorAll('.widget-animate');
        
        if (animatedWidgets.length > 0) {
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -50px 0px', // 하단 50px 여유를 두고 트리거
                threshold: 0.1 // 위젯이 10% 보이면 트리거
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        // 위젯이 화면에 나타나면 애니메이션 클래스 추가
                        entry.target.classList.add('widget-animate-visible');
                        observer.unobserve(entry.target); // 한 번만 실행
                    }
                });
            }, observerOptions);
            
            // 모든 애니메이션 위젯을 관찰
            animatedWidgets.forEach(function(widget) {
                observer.observe(widget);
            });
        }
    });
    </script>
    
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
    
    @if($themeDarkMode === 'dark')
    <script>
    // 다크모드에서 인라인 스타일의 흰색 배경을 다크 배경으로 변환
    document.addEventListener('DOMContentLoaded', function() {
        // 흰색 배경 요소 찾아서 변환
        const whiteBackgrounds = ['#ffffff', '#fff', 'white', 'rgb(255, 255, 255)', 'rgba(255, 255, 255, 1)'];
        const darkBg = 'rgb(43, 43, 43)';
        const darkBorder = 'rgba(255, 255, 255, 0.1)';
        
        // 모든 요소 검사
        document.querySelectorAll('[style]').forEach(function(el) {
            const style = el.getAttribute('style');
            if (style) {
                let newStyle = style;
                
                // 배경색 변환
                whiteBackgrounds.forEach(function(whiteBg) {
                    const bgRegex = new RegExp('background(-color)?\\s*:\\s*' + whiteBg.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
                    if (bgRegex.test(newStyle)) {
                        newStyle = newStyle.replace(bgRegex, 'background-color: ' + darkBg);
                    }
                });
                
                // 테두리 색상 변환 (#dee2e6 등 라이트 색상)
                newStyle = newStyle.replace(/border(-color)?:\s*#dee2e6/gi, 'border-color: ' + darkBorder);
                newStyle = newStyle.replace(/border-bottom:\s*1px\s+solid\s+#dee2e6/gi, 'border-bottom: 1px solid ' + darkBorder);
                
                if (newStyle !== style) {
                    el.setAttribute('style', newStyle);
                }
            }
        });
        
        // 검은색 텍스트를 흰색으로 변환 (카드/위젯 내부)
        const blackTextColors = ['#000000', '#000', 'black', 'rgb(0, 0, 0)'];
        document.querySelectorAll('.card, .widget-card, [class*="widget"], .sidebar').forEach(function(container) {
            container.querySelectorAll('[style]').forEach(function(el) {
                const style = el.getAttribute('style');
                if (style) {
                    let newStyle = style;
                    blackTextColors.forEach(function(blackColor) {
                        const colorRegex = new RegExp('color\\s*:\\s*' + blackColor.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
                        if (colorRegex.test(newStyle) && !newStyle.includes('background')) {
                            newStyle = newStyle.replace(colorRegex, 'color: #ffffff');
                        }
                    });
                    if (newStyle !== style) {
                        el.setAttribute('style', newStyle);
                    }
                }
            });
        });
    });
    </script>
    @endif
</body>
</html>
