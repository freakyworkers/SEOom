<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '마스터 운영 콘솔') - SEOom Builder</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #1a1d29;
            --sidebar-active: #0d6efd;
            --master-primary: #6f42c1;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background-color: var(--sidebar-bg);
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 1rem;
            z-index: 1000;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
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
            color: white;
            background-color: var(--master-primary);
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
        
        .master-badge {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .bg-purple {
            background-color: #6f42c1 !important;
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
            <div class="px-3 mb-4">
                <h4 class="text-white mb-2">
                    <i class="bi bi-shield-check me-2"></i>마스터 콘솔
                </h4>
                <span class="master-badge">Master</span>
            </div>
            
            <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}" 
                               href="{{ route('master.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>대시보드
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.sites.*') ? 'active' : '' }}" 
                               href="{{ route('master.sites.index') }}">
                                <i class="bi bi-building me-2"></i>사이트 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.monitoring') ? 'active' : '' }}" 
                               href="{{ route('master.monitoring') }}">
                                <i class="bi bi-graph-up me-2"></i>모니터링
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.backup.*') ? 'active' : '' }}" 
                               href="{{ route('master.backup.index') }}">
                                <i class="bi bi-archive me-2"></i>백업/복구
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.plans.*') ? 'active' : '' }}" 
                               href="{{ route('master.plans.index') }}">
                                <i class="bi bi-credit-card me-2"></i>요금제 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.addon-products.*') ? 'active' : '' }}" 
                               href="{{ route('master.addon-products.index') }}">
                                <i class="bi bi-box-seam me-2"></i>추가 구매 상품
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.subscription-settings.*') ? 'active' : '' }}" 
                               href="{{ route('master.subscription-settings.index') }}">
                                <i class="bi bi-gear me-2"></i>구독 설정
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master.map-api.*') ? 'active' : '' }}" 
                               href="{{ route('master.map-api.index') }}">
                                <i class="bi bi-geo-alt me-2"></i>지도 API 설정
                            </a>
                        </li>
                <li class="nav-item mt-4">
                    <form action="{{ route('master.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent text-white">
                            <i class="bi bi-box-arrow-right me-2"></i>로그아웃
                        </button>
                    </form>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('master.master-sites.*') ? 'active' : '' }}" 
                       href="{{ route('master.master-sites.index') }}">
                        <i class="bi bi-arrow-left me-2"></i>마스터 사이트 관리
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content flex-grow-1">
            <div class="page-header">
                <h1 class="h3 mb-0">@yield('page-title', '마스터 운영 콘솔')</h1>
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
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>
    
    @stack('scripts')
</body>
</html>

