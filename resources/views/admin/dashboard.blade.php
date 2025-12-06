@extends('layouts.admin')

@section('title', '대시보드')
@section('page-title', '대시보드')
@section('page-subtitle', '사이트 현황을 한눈에 확인하세요')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
<style>
    .stat-card {
        border-left: 4px solid;
        transition: all 0.3s;
    }
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .stat-card.border-primary { border-left-color: #0d6efd; }
    .stat-card.border-success { border-left-color: #198754; }
    .stat-card.border-info { border-left-color: #0dcaf0; }
    .stat-card.border-warning { border-left-color: #ffc107; }
    .stat-card.border-danger { border-left-color: #dc3545; }
    .stat-card.border-secondary { border-left-color: #6c757d; }
    
    .stat-icon {
        font-size: 3rem;
        opacity: 0.2;
    }
    
    .activity-item {
        border-left: 3px solid transparent;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s;
    }
    .activity-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@section('content')
<!-- Site Info Card -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">{{ $site->getSetting('site_name', $site->name) }}</h4>
                <p class="text-muted mb-0">
                    <i class="bi bi-link-45deg me-1"></i>Slug: <code>{{ $site->slug }}</code>
                    <span class="mx-2">|</span>
                    <i class="bi bi-circle-fill me-1 text-{{ $site->status === 'active' ? 'success' : 'danger' }}"></i>
                    상태: {{ $site->status === 'active' ? '활성' : '비활성' }}
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('admin.settings', ['site' => $site->slug]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-gear me-1"></i>설정 변경
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-primary shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2 small">전체 사용자</h6>
                        <h2 class="mb-0">{{ number_format($stats['users']) }}</h2>
                        @if($stats['today_users'] > 0)
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> 오늘 +{{ $stats['today_users'] }}
                            </small>
                        @endif
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <a href="{{ route('admin.users', ['site' => $site->slug]) }}" class="btn btn-sm btn-outline-primary w-100">
                    관리하기 <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-success shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2 small">게시판</h6>
                        <h2 class="mb-0">{{ number_format($stats['boards']) }}</h2>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="bi bi-grid"></i>
                    </div>
                </div>
                <a href="{{ route('admin.boards', ['site' => $site->slug]) }}" class="btn btn-sm btn-outline-success w-100">
                    관리하기 <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-info shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2 small">전체 게시글</h6>
                        <h2 class="mb-0">{{ number_format($stats['posts']) }}</h2>
                        @if($stats['today_posts'] > 0)
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> 오늘 +{{ $stats['today_posts'] }}
                            </small>
                        @endif
                    </div>
                    <div class="stat-icon text-info">
                        <i class="bi bi-file-text"></i>
                    </div>
                </div>
                <a href="{{ route('admin.posts', ['site' => $site->slug]) }}" class="btn btn-sm btn-outline-info w-100">
                    관리하기 <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-warning shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2 small">전체 댓글</h6>
                        <h2 class="mb-0">{{ number_format($stats['comments']) }}</h2>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                </div>
                <a href="{{ route('admin.posts', ['site' => $site->slug]) }}" class="btn btn-sm btn-outline-warning w-100">
                    확인하기 <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>활동 통계</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary chart-period-btn active" data-period="week">1주일</button>
                        <button type="button" class="btn btn-outline-secondary chart-period-btn" data-period="month">1개월</button>
                        <button type="button" class="btn btn-outline-secondary chart-period-btn" data-period="year">1년</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="btn-group btn-group-sm mb-3" role="group">
                    <button type="button" class="btn btn-outline-primary chart-type-btn active" data-type="posts">게시글</button>
                    <button type="button" class="btn btn-outline-primary chart-type-btn" data-type="users">사용자</button>
                    <button type="button" class="btn btn-outline-primary chart-type-btn" data-type="comments">댓글</button>
                    <button type="button" class="btn btn-outline-primary chart-type-btn" data-type="visitors">접속자</button>
                    <button type="button" class="btn btn-outline-primary chart-type-btn" data-type="views">조회수</button>
                </div>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity and Quick Actions Row -->
<div class="row">
    <!-- Recent Activity -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>최근 활동</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#recent-posts" type="button">최근 게시글</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#recent-comments" type="button">최근 댓글</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#recent-users" type="button">최근 가입</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="recent-posts">
                        @if($recentPosts->count() > 0)
                            @foreach($recentPosts as $post)
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug, 'post' => $post->id]) }}" class="text-decoration-none">
                                                    {{ Str::limit($post->title, 50) }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-person"></i> {{ $post->user->name }}
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-grid"></i> {{ $post->board->name }}
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-clock"></i> {{ $post->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center py-4">최근 게시글이 없습니다.</p>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="recent-comments">
                        @if($recentComments->count() > 0)
                            @foreach($recentComments as $comment)
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $comment->post->board->slug, 'post' => $comment->post->id]) }}" class="text-decoration-none">
                                                    {{ Str::limit($comment->post->title, 40) }}
                                                </a>
                                            </h6>
                                            <p class="mb-1 small">{{ Str::limit(strip_tags($comment->content), 60) }}</p>
                                            <small class="text-muted">
                                                <i class="bi bi-person"></i> {{ $comment->user->name }}
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-clock"></i> {{ $comment->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center py-4">최근 댓글이 없습니다.</p>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="recent-users">
                        @if($recentUsers->count() > 0)
                            @foreach($recentUsers as $user)
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $user->name }}</h6>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope"></i> {{ $user->email }}
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-clock"></i> {{ $user->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'secondary' }}">
                                            {{ $user->role === 'admin' ? '관리자' : '일반' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center py-4">최근 가입한 사용자가 없습니다.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>빠른 작업</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('boards.create', ['site' => $site->slug]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-2"></i>게시판 만들기
                    </a>
                    <a href="{{ route('admin.settings', ['site' => $site->slug]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-gear me-2"></i>사이트 설정
                    </a>
                    <a href="{{ route('home', ['site' => $site->slug]) }}" class="btn btn-outline-info">
                        <i class="bi bi-house me-2"></i>사이트로 이동
                    </a>
                    <a href="{{ route('admin.users', ['site' => $site->slug]) }}" class="btn btn-outline-success">
                        <i class="bi bi-people me-2"></i>사용자 관리
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    let activityChart = null;
    let currentType = 'posts';
    let currentPeriod = 'week';

    function loadChart(type, period) {
        $.ajax({
            url: '{{ route("admin.dashboard.chart", ["site" => $site->slug]) }}',
            method: 'GET',
            data: {
                type: type,
                period: period
            },
            success: function(response) {
                if (activityChart) {
                    activityChart.destroy();
                }

                const ctx = document.getElementById('activityChart').getContext('2d');
                const typeLabels = {
                    'posts': '게시글',
                    'users': '사용자',
                    'comments': '댓글',
                    'visitors': '접속자',
                    'views': '조회수'
                };
                const colors = {
                    'posts': 'rgba(13, 110, 253, 0.8)',
                    'users': 'rgba(25, 135, 84, 0.8)',
                    'comments': 'rgba(255, 193, 7, 0.8)',
                    'visitors': 'rgba(220, 53, 69, 0.8)',
                    'views': 'rgba(108, 117, 125, 0.8)'
                };

                activityChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: response.labels,
                        datasets: [{
                            label: typeLabels[type],
                            data: response.data,
                            borderColor: colors[type],
                            backgroundColor: colors[type].replace('0.8', '0.1'),
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        });
    }

    // 초기 차트 로드
    loadChart(currentType, currentPeriod);

    // 차트 타입 변경
    $('.chart-type-btn').on('click', function() {
        $('.chart-type-btn').removeClass('active');
        $(this).addClass('active');
        currentType = $(this).data('type');
        loadChart(currentType, currentPeriod);
    });

    // 차트 기간 변경
    $('.chart-period-btn').on('click', function() {
        $('.chart-period-btn').removeClass('active');
        $(this).addClass('active');
        currentPeriod = $(this).data('period');
        loadChart(currentType, currentPeriod);
    });
});
</script>
@endpush
