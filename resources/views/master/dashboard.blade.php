@extends('layouts.master')

@section('title', '대시보드')
@section('page-title', '마스터 대시보드')
@section('page-subtitle', '전체 시스템 현황을 한눈에 확인하세요')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2">전체 사이트</h6>
                        <h2 class="mb-0">{{ number_format($stats['total_sites']) }}</h2>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2">활성 사이트</h6>
                        <h2 class="mb-0">{{ number_format($stats['active_sites']) }}</h2>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2">정지 사이트</h6>
                        <h2 class="mb-0">{{ number_format($stats['suspended_sites']) }}</h2>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2">전체 사용자</h6>
                        <h2 class="mb-0">{{ number_format($stats['total_users']) }}</h2>
                    </div>
                    <div class="stat-icon text-info">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card stat-card border-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2">전체 게시판</h6>
                        <h2 class="mb-0">{{ number_format($stats['total_boards']) }}</h2>
                    </div>
                    <div class="stat-icon text-secondary">
                        <i class="bi bi-grid"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card stat-card border-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2">전체 게시글</h6>
                        <h2 class="mb-0">{{ number_format($stats['total_posts']) }}</h2>
                    </div>
                    <div class="stat-icon text-danger">
                        <i class="bi bi-file-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sites -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>최근 생성된 사이트</h5>
            </div>
            <div class="card-body">
                @if($recentSites->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentSites as $site)
                            <a href="{{ route('master.sites.show', $site->id) }}" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $site->name }}</h6>
                                        <small class="text-muted">{{ $site->slug }}</small>
                                    </div>
                                    <span class="badge bg-{{ $site->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ $site->status === 'active' ? '활성' : '비활성' }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">등록된 사이트가 없습니다.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>최근 가입한 사용자</h5>
            </div>
            <div class="card-body">
                @if($recentUsers->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentUsers as $user)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-building me-1"></i>{{ $user->site->name ?? 'N/A' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'secondary' }}">
                                        {{ $user->role === 'admin' ? '관리자' : '사용자' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">등록된 사용자가 없습니다.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>빠른 작업</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="{{ route('master.sites.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <i class="bi bi-plus-circle display-6 mb-2"></i>
                    <span>사이트 생성</span>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('master.sites.index') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <i class="bi bi-building display-6 mb-2"></i>
                    <span>사이트 관리</span>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('master.monitoring') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <i class="bi bi-graph-up display-6 mb-2"></i>
                    <span>모니터링</span>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('master.backup.index') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                    <i class="bi bi-archive display-6 mb-2"></i>
                    <span>백업 관리</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection








