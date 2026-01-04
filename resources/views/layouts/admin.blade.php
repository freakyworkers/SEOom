<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '관리자') - SEOom Builder</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #212529;
            --sidebar-active: #0d6efd;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        /* 모바일에서만 가로 스크롤 방지 (테이블 스크롤은 허용) */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }
            
            /* table-responsive는 스크롤 허용 */
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }
        }
        
        * {
            box-sizing: border-box;
        }
        
        .sidebar {
            height: 100vh;
            max-height: 100vh;
            background-color: var(--sidebar-bg);
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 1rem;
            padding-bottom: 1rem;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .sidebar-header {
            flex-shrink: 0;
            padding: 0 1rem;
            margin-bottom: 1rem;
        }
        
        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 0.5rem;
            min-height: 0;
        }
        
        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
        
        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            margin: 0.25rem 1rem;
            transition: all 0.2s;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: rgba(255, 255, 255, 0.75) !important;
            background-color: transparent !important;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            width: calc(100% - var(--sidebar-width));
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .page-header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            border: none;
            border-radius: 0.5rem;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        /* 모바일 헤더 */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background-color: var(--sidebar-bg);
            z-index: 1001;
            padding: 0 1rem;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .mobile-header .admin-title {
            color: white;
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0;
        }
        
        .mobile-header .hamburger-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s;
        }
        
        .mobile-header .hamburger-btn:hover {
            opacity: 0.8;
        }
        
        /* 사이드바 오버레이 */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        
        .sidebar-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        
        @media (max-width: 768px) {
            .mobile-header {
                display: flex !important;
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                top: 56px;
                height: calc(100vh - 56px);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-overlay {
                display: block;
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 1rem;
                padding-top: calc(1rem + 56px);
            }
            
            .page-header {
                margin-bottom: 1rem;
            }
            
            .container-fluid {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- 모바일 헤더 -->
    <div class="mobile-header">
        <h5 class="admin-title mb-0">관리자</h5>
        <button class="hamburger-btn" id="hamburgerBtn" type="button" aria-label="메뉴 열기">
            <i class="bi bi-list" id="hamburgerIcon"></i>
        </button>
    </div>
    
    <!-- 사이드바 오버레이 -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h4 class="text-white mb-0">
                    <i class="bi bi-speedometer2 me-2"></i>관리자
                </h4>
                <small class="text-muted">{{ $site->name ?? 'Site' }}</small>
            </div>
            
            <div class="sidebar-menu">
                <ul class="nav flex-column">
                {{-- 1. 대시보드 --}}
                @if($site->hasFeature('dashboard'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('master.admin.dashboard') ? 'active' : '' }}" 
                       href="{{ $site->getAdminDashboardUrl() }}">
                        <i class="bi bi-speedometer2 me-2"></i>대시보드
                    </a>
                </li>
                @endif

                {{-- 2. 사이트 설정 및 디자인 --}}
                @if($site->hasFeature('settings'))
                <li class="nav-item">
                    @php
                        $settingsUrl = $site->isMasterSite() && Route::has('master.admin.settings') 
                            ? url('/admin/settings') 
                            : ($site->isMasterSite() 
                                ? '/admin/settings' 
                                : route('admin.settings', ['site' => $site->slug]));
                    @endphp
                    <a class="nav-link {{ request()->routeIs('admin.settings') || request()->routeIs('master.admin.settings') ? 'active' : '' }}" 
                       href="{{ $settingsUrl }}">
                        <i class="bi bi-gear me-2"></i>사이트 설정
                    </a>
                </li>
                @endif
                @if($site->hasFeature('menus'))
                <li class="nav-item">
                    @php
                        $menusUrl = $site->isMasterSite() && Route::has('master.admin.menus') 
                            ? url('/admin/menus') 
                            : ($site->isMasterSite() 
                                ? '/admin/menus' 
                                : route('admin.menus', ['site' => $site->slug]));
                    @endphp
                    <a class="nav-link {{ request()->routeIs('admin.menus.*') || request()->routeIs('master.admin.menus.*') ? 'active' : '' }}" 
                       href="{{ $menusUrl }}">
                        <i class="bi bi-list-ul me-2"></i>메뉴 설정
                    </a>
                </li>
                @endif
                @if($site->hasFeature('main_widgets'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.main-widgets') || request()->routeIs('master.admin.main-widgets') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/main-widgets') : route('admin.main-widgets', ['site' => $site->slug]) }}">
                        <i class="bi bi-grid me-2"></i>메인 위젯
                    </a>
                </li>
                @endif
                @if($site->hasFeature('custom_pages'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.custom-pages*') || request()->routeIs('master.admin.custom-pages*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/custom-pages') : route('admin.custom-pages', ['site' => $site->slug]) }}">
                        <i class="bi bi-file-earmark-text me-2"></i>커스텀 페이지
                    </a>
                </li>
                @endif
                @if($site->hasFeature('sidebar_widgets'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.sidebar-widgets') || request()->routeIs('master.admin.sidebar-widgets') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/sidebar-widgets') : route('admin.sidebar-widgets', ['site' => $site->slug]) }}">
                        <i class="bi bi-layout-sidebar me-2"></i>사이드 위젯
                    </a>
                </li>
                @endif
                @if($site->hasFeature('banners'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.banners.*') || request()->routeIs('master.admin.banners.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/banners') : route('admin.banners.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-image me-2"></i>배너
                    </a>
                </li>
                @endif
                @if($site->hasFeature('popups'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.popups.*') || request()->routeIs('master.admin.popups.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/popups') : route('admin.popups.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-window me-2"></i>팝업
                    </a>
                </li>
                @endif

                {{-- 3. 사용자 관련 --}}
                @if($site->hasFeature('users'))
                <li class="nav-item">
                    @php
                        $usersUrl = $site->isMasterSite() && Route::has('master.admin.users') 
                            ? url('/admin/users') 
                            : ($site->isMasterSite() 
                                ? '/admin/users' 
                                : route('admin.users', ['site' => $site->slug]));
                    @endphp
                    <a class="nav-link {{ request()->routeIs('admin.users') || request()->routeIs('master.admin.users') ? 'active' : '' }}" 
                       href="{{ $usersUrl }}">
                        <i class="bi bi-people me-2"></i>사용자 관리
                    </a>
                </li>
                @endif
                @if($site->hasFeature('registration_settings'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.registration-settings') || request()->routeIs('master.admin.registration-settings') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/registration-settings') : route('admin.registration-settings', ['site' => $site->slug]) }}">
                        <i class="bi bi-person-plus me-2"></i>회원가입 설정
                    </a>
                </li>
                @endif
                @if($site->hasFeature('user_ranks'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.user-ranks.*') || request()->routeIs('master.admin.user-ranks.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/user-ranks') : route('admin.user-ranks', ['site' => $site->slug]) }}">
                        <i class="bi bi-trophy me-2"></i>회원등급
                    </a>
                </li>
                @endif
                @if($site->hasFeature('users'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.my-page-settings') || request()->routeIs('master.admin.my-page-settings') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/my-page-settings') : route('admin.my-page-settings', ['site' => $site->slug]) }}">
                        <i class="bi bi-person-circle me-2"></i>마이페이지
                    </a>
                </li>
                @endif
                @if($site->hasFeature('attendance'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.attendance.*') || request()->routeIs('master.admin.attendance.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/attendance') : route('admin.attendance.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-calendar-check me-2"></i>출첵
                    </a>
                </li>
                @endif

                {{-- 4. 커뮤니케이션 --}}
                @if($site->hasFeature('mail_settings'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.mail-settings') || request()->routeIs('master.admin.mail-settings') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/mail-settings') : route('admin.mail-settings', ['site' => $site->slug]) }}">
                        <i class="bi bi-envelope me-2"></i>메일 설정
                    </a>
                </li>
                @endif
                @if($site->hasFeature('messages'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.messages.*') || request()->routeIs('master.admin.messages.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/messages') : route('admin.messages.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-envelope me-2"></i>쪽지 관리
                    </a>
                </li>
                @endif
                @if($site->hasFeature('chat_widget'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.chat*') || request()->routeIs('master.admin.chat*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/chat') : route('admin.chat.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-chat-dots me-2"></i>채팅
                    </a>
                </li>
                @endif

                {{-- 5. 콘텐츠 관리 --}}
                @if($site->hasFeature('boards'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.boards') || request()->routeIs('master.admin.boards') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/boards') : route('admin.boards', ['site' => $site->slug]) }}">
                        <i class="bi bi-grid me-2"></i>게시판 관리
                    </a>
                </li>
                @endif
                @if($site->hasFeature('posts'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.posts') || request()->routeIs('master.admin.posts') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/posts') : route('admin.posts', ['site' => $site->slug]) }}">
                        <i class="bi bi-file-text me-2"></i>게시글 관리
                    </a>
                </li>
                @endif

                {{-- 6. 포인트/이벤트 --}}
                @if($site->hasFeature('point_exchange'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.point-exchange.*') || request()->routeIs('master.admin.point-exchange.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/point-exchange') : route('admin.point-exchange.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-currency-exchange me-2"></i>포인트 교환
                    </a>
                </li>
                @endif
                @if($site->hasFeature('event_application'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.event-application.*') || request()->routeIs('master.admin.event-application.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/event-application') : route('admin.event-application.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-calendar-event me-2"></i>신청형 이벤트
                    </a>
                </li>
                @endif

                {{-- 7. 디자인/UI (추가) --}}
                @if($site->hasFeature('toggle_menus'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.toggle-menus*') || request()->routeIs('master.admin.toggle-menus*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/toggle-menus') : route('admin.toggle-menus', ['site' => $site->slug]) }}">
                        <i class="bi bi-list-nested me-2"></i>토글 메뉴
                    </a>
                </li>
                @endif

                {{-- 8. 기능/통합 --}}
                @if($site->hasFeature('contact_forms'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.contact-forms.*') || request()->routeIs('master.admin.contact-forms.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/contact-forms') : route('admin.contact-forms.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-chat-dots me-2"></i>컨텍트폼
                    </a>
                </li>
                @endif
                @if($site->hasFeature('maps'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.maps.*') || request()->routeIs('master.admin.maps.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/maps') : route('admin.maps.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-geo-alt me-2"></i>지도
                    </a>
                </li>
                @endif
                @if($site->hasFeature('crawlers'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.crawlers.*') || request()->routeIs('master.admin.crawlers.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/crawlers') : route('admin.crawlers.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-robot me-2"></i>크롤러
                    </a>
                </li>
                @endif

                {{-- 9. 보안/관리 --}}
                @if($site->hasFeature('blocked_ips'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.blocked-ips.*') || request()->routeIs('master.admin.blocked-ips.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/blocked-ips') : route('admin.blocked-ips.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-shield-x me-2"></i>아이피 차단
                    </a>
                </li>
                @endif
                @if($site->hasFeature('chat_widget') || $site->hasFeature('boards'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports*') || request()->routeIs('master.admin.reports*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/reports') : route('admin.reports.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-flag me-2"></i>신고
                    </a>
                </li>
                @endif

                {{-- 10. 고급 설정 --}}
                @if($site->hasFeature('custom_code'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.custom-codes*') || request()->routeIs('master.admin.custom-codes*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? url('/admin/custom-codes') : route('admin.custom-codes', ['site' => $site->slug]) }}">
                        <i class="bi bi-code-square me-2"></i>코드 커스텀
                    </a>
                </li>
                @endif
                @if(!$site->isMasterSite())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.server-management') ? 'active' : '' }}" 
                       href="{{ route('admin.server-management', ['site' => $site->slug]) }}">
                        <i class="bi bi-server me-2"></i>서버 관리
                    </a>
                </li>
                @endif

                {{-- 10. 사이트로 돌아가기 --}}
                <li class="nav-item mt-4">
                    <a class="nav-link" href="{{ $site->isMasterSite() ? '/' : route('home', ['site' => $site->slug]) }}">
                        <i class="bi bi-arrow-left me-2"></i>사이트로 돌아가기
                    </a>
                </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content flex-grow-1">
            <div class="page-header">
                <h1 class="h3 mb-0">@yield('page-title', '관리자')</h1>
                @hasSection('page-subtitle')
                    <p class="text-muted mb-0 mt-2">@yield('page-subtitle')</p>
                @endif
            </div>

            @if(session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif

            @if(session('error'))
                <x-alert type="error">{{ session('error') }}</x-alert>
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

            {{-- 샘플 사이트 안내 배너 --}}
            @if(isset($site) && $site->isSample())
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>
                        <strong>샘플 사이트입니다.</strong> 이 사이트는 미리보기 전용으로, 모든 변경 사항은 저장되지 않습니다.
                        <a href="{{ route('user-sites.select-plan', ['site' => \App\Models\Site::getMasterSite()?->slug ?? 'master']) }}" class="alert-link">직접 사이트 만들기 →</a>
                    </div>
                </div>
            @endif

            @if(session('is_test_admin'))
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-person-badge me-2 fs-5"></i>
                    <div>
                        <strong>테스트 어드민으로 접속 중입니다.</strong> 관리자 기능을 체험할 수 있지만, 모든 변경 사항은 저장되지 않습니다.
                        <a href="{{ url('/logout') }}" class="alert-link ms-2">로그아웃 →</a>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>
    
    <script>
        // 모바일 햄버거 메뉴 토글
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const hamburgerIcon = document.getElementById('hamburgerIcon');
            
            if (hamburgerBtn && sidebar && sidebarOverlay) {
                // 햄버거 버튼 클릭
                hamburgerBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                    
                    // 아이콘 변경 (햄버거 <-> X)
                    if (sidebar.classList.contains('show')) {
                        hamburgerIcon.classList.remove('bi-list');
                        hamburgerIcon.classList.add('bi-x-lg');
                    } else {
                        hamburgerIcon.classList.remove('bi-x-lg');
                        hamburgerIcon.classList.add('bi-list');
                    }
                });
                
                // 오버레이 클릭 시 사이드바 닫기
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    hamburgerIcon.classList.remove('bi-x-lg');
                    hamburgerIcon.classList.add('bi-list');
                });
                
                // 사이드바 메뉴 링크 클릭 시 모바일에서 사이드바 닫기
                if (window.innerWidth <= 768) {
                    const sidebarLinks = sidebar.querySelectorAll('.nav-link');
                    sidebarLinks.forEach(function(link) {
                        link.addEventListener('click', function() {
                            sidebar.classList.remove('show');
                            sidebarOverlay.classList.remove('show');
                            hamburgerIcon.classList.remove('bi-x-lg');
                            hamburgerIcon.classList.add('bi-list');
                        });
                    });
                }
            }
        });
    </script>
    
    @stack('scripts')
    
    <!-- 플랜 제한 초과 모달 -->
    <div class="modal fade" id="planLimitModal" tabindex="-1" aria-labelledby="planLimitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="planLimitModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>플랜 제한 도달
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="planLimitMessage" class="mb-3"></p>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>업그레이드 안내:</strong> 더 많은 기능을 사용하려면 플랜을 업그레이드하세요.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                    <a href="{{ config('app.url') }}/store?site=master" class="btn btn-primary" target="_blank">
                        <i class="bi bi-arrow-up-circle me-1"></i>플랜 업그레이드
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // 플랜 제한 초과 모달 표시 함수
    function showPlanLimitModal(message) {
        document.getElementById('planLimitMessage').textContent = message;
        var modal = new bootstrap.Modal(document.getElementById('planLimitModal'));
        modal.show();
    }
    
    // 전역 AJAX 에러 핸들러 - 제한 초과 응답 처리
    window.handleLimitExceededError = function(response) {
        if (response && response.limit_exceeded) {
            showPlanLimitModal(response.error || '플랜 제한에 도달했습니다.');
            return true;
        }
        return false;
    };
    </script>
</body>
</html>
