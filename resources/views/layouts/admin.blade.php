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
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar">
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
                       href="{{ $site->isMasterSite() ? route('master.admin.dashboard') : route('admin.dashboard', ['site' => $site->slug]) }}">
                        <i class="bi bi-speedometer2 me-2"></i>대시보드
                    </a>
                </li>
                @endif

                {{-- 2. 사이트 설정 및 디자인 --}}
                @if($site->hasFeature('settings'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings') || request()->routeIs('master.admin.settings') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.settings') : route('admin.settings', ['site' => $site->slug]) }}">
                        <i class="bi bi-gear me-2"></i>사이트 설정
                    </a>
                </li>
                @endif
                @if($site->hasFeature('menus'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.menus.*') || request()->routeIs('master.admin.menus.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.menus') : route('admin.menus', ['site' => $site->slug]) }}">
                        <i class="bi bi-list-ul me-2"></i>메뉴 설정
                    </a>
                </li>
                @endif
                @if($site->hasFeature('main_widgets'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.main-widgets') || request()->routeIs('master.admin.main-widgets') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.main-widgets') : route('admin.main-widgets', ['site' => $site->slug]) }}">
                        <i class="bi bi-grid me-2"></i>메인 위젯
                    </a>
                </li>
                @endif
                @if($site->hasFeature('custom_pages'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.custom-pages*') || request()->routeIs('master.admin.custom-pages*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.custom-pages') : route('admin.custom-pages', ['site' => $site->slug]) }}">
                        <i class="bi bi-file-earmark-text me-2"></i>커스텀 페이지
                    </a>
                </li>
                @endif
                @if($site->hasFeature('sidebar_widgets'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.sidebar-widgets') || request()->routeIs('master.admin.sidebar-widgets') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.sidebar-widgets') : route('admin.sidebar-widgets', ['site' => $site->slug]) }}">
                        <i class="bi bi-layout-sidebar me-2"></i>사이드 위젯
                    </a>
                </li>
                @endif
                @if($site->hasFeature('banners'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.banners.*') || request()->routeIs('master.admin.banners.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.banners.index') : route('admin.banners.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-image me-2"></i>배너
                    </a>
                </li>
                @endif
                @if($site->hasFeature('popups'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.popups.*') || request()->routeIs('master.admin.popups.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.popups.index') : route('admin.popups.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-window me-2"></i>팝업
                    </a>
                </li>
                @endif

                {{-- 3. 사용자 관련 --}}
                @if($site->hasFeature('users'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users') || request()->routeIs('master.admin.users') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.users') : route('admin.users', ['site' => $site->slug]) }}">
                        <i class="bi bi-people me-2"></i>사용자 관리
                    </a>
                </li>
                @endif
                @if($site->hasFeature('registration_settings'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.registration-settings') || request()->routeIs('master.admin.registration-settings') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.registration-settings') : route('admin.registration-settings', ['site' => $site->slug]) }}">
                        <i class="bi bi-person-plus me-2"></i>회원가입 설정
                    </a>
                </li>
                @endif
                @if($site->hasFeature('user_ranks'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.user-ranks.*') || request()->routeIs('master.admin.user-ranks.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.user-ranks') : route('admin.user-ranks', ['site' => $site->slug]) }}">
                        <i class="bi bi-trophy me-2"></i>회원등급
                    </a>
                </li>
                @endif
                @if($site->hasFeature('attendance'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.attendance.*') || request()->routeIs('master.admin.attendance.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.attendance.index') : route('admin.attendance.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-calendar-check me-2"></i>출첵
                    </a>
                </li>
                @endif

                {{-- 4. 커뮤니케이션 --}}
                @if($site->hasFeature('mail_settings'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.mail-settings') || request()->routeIs('master.admin.mail-settings') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.mail-settings') : route('admin.mail-settings', ['site' => $site->slug]) }}">
                        <i class="bi bi-envelope me-2"></i>메일 설정
                    </a>
                </li>
                @endif
                @if($site->hasFeature('messages'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.messages.*') || request()->routeIs('master.admin.messages.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.messages.index') : route('admin.messages.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-envelope me-2"></i>쪽지 관리
                    </a>
                </li>
                @endif
                @if($site->hasFeature('chat_widget'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.chat*') || request()->routeIs('master.admin.chat*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.chat.index') : route('admin.chat.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-chat-dots me-2"></i>채팅
                    </a>
                </li>
                @endif

                {{-- 5. 콘텐츠 관리 --}}
                @if($site->hasFeature('boards'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.boards') || request()->routeIs('master.admin.boards') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.boards') : route('admin.boards', ['site' => $site->slug]) }}">
                        <i class="bi bi-grid me-2"></i>게시판 관리
                    </a>
                </li>
                @endif
                @if($site->hasFeature('posts'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.posts') || request()->routeIs('master.admin.posts') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.posts') : route('admin.posts', ['site' => $site->slug]) }}">
                        <i class="bi bi-file-text me-2"></i>게시글 관리
                    </a>
                </li>
                @endif

                {{-- 6. 포인트/이벤트 --}}
                @if($site->hasFeature('point_exchange'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.point-exchange.*') || request()->routeIs('master.admin.point-exchange.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.point-exchange.index') : route('admin.point-exchange.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-currency-exchange me-2"></i>포인트 교환
                    </a>
                </li>
                @endif
                @if($site->hasFeature('event_application'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.event-application.*') || request()->routeIs('master.admin.event-application.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.event-application.index') : route('admin.event-application.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-calendar-event me-2"></i>신청형 이벤트
                    </a>
                </li>
                @endif

                {{-- 7. 디자인/UI (추가) --}}
                @if($site->hasFeature('toggle_menus'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.toggle-menus*') || request()->routeIs('master.admin.toggle-menus*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.toggle-menus') : route('admin.toggle-menus', ['site' => $site->slug]) }}">
                        <i class="bi bi-list-nested me-2"></i>토글 메뉴
                    </a>
                </li>
                @endif

                {{-- 8. 기능/통합 --}}
                @if($site->hasFeature('contact_forms'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.contact-forms.*') || request()->routeIs('master.admin.contact-forms.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.contact-forms.index') : route('admin.contact-forms.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-chat-dots me-2"></i>컨텍트폼
                    </a>
                </li>
                @endif
                @if($site->hasFeature('maps'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.maps.*') || request()->routeIs('master.admin.maps.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.maps.index') : route('admin.maps.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-geo-alt me-2"></i>지도
                    </a>
                </li>
                @endif
                @if($site->hasFeature('crawlers'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.crawlers.*') || request()->routeIs('master.admin.crawlers.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.crawlers.index') : route('admin.crawlers.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-robot me-2"></i>크롤러
                    </a>
                </li>
                @endif

                {{-- 9. 보안/관리 --}}
                @if($site->hasFeature('blocked_ips'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.blocked-ips.*') || request()->routeIs('master.admin.blocked-ips.*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.blocked-ips.index') : route('admin.blocked-ips.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-shield-x me-2"></i>아이피 차단
                    </a>
                </li>
                @endif
                @if($site->hasFeature('chat_widget') || $site->hasFeature('boards'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports*') || request()->routeIs('master.admin.reports*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.reports.index') : route('admin.reports.index', ['site' => $site->slug]) }}">
                        <i class="bi bi-flag me-2"></i>신고
                    </a>
                </li>
                @endif

                {{-- 10. 고급 설정 --}}
                @if($site->hasFeature('custom_code'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.custom-codes*') || request()->routeIs('master.admin.custom-codes*') ? 'active' : '' }}" 
                       href="{{ $site->isMasterSite() ? route('master.admin.custom-codes') : route('admin.custom-codes', ['site' => $site->slug]) }}">
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
    
    @stack('scripts')
</body>
</html>
