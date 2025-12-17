@extends('layouts.master')

@section('title', '모니터링')
@section('page-title', '시스템 모니터링')
@section('page-subtitle', '전체 시스템 현황 및 통계를 확인하세요')

@section('content')
<!-- System Statistics -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2">전체 사이트</h6>
                        <h2 class="mb-0">{{ number_format($siteStats['total']) }}</h2>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2">활성 사이트</h6>
                        <h2 class="mb-0">{{ number_format($siteStats['active']) }}</h2>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2">정지 사이트</h6>
                        <h2 class="mb-0">{{ number_format($siteStats['suspended']) }}</h2>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Database Size -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-database me-2"></i>데이터베이스 용량</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <h3 class="mb-0">{{ number_format($dbSize, 2) }} MB</h3>
                <small class="text-muted">전체 데이터베이스 크기</small>
            </div>
            <div class="stat-icon text-info">
                <i class="bi bi-database display-4"></i>
            </div>
        </div>
    </div>
</div>

<!-- Top Sites -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>사용자 수 TOP 10</h5>
            </div>
            <div class="card-body">
                @if($topSitesByUsers->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topSitesByUsers as $index => $site)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-primary me-2">#{{ $index + 1 }}</span>
                                        <strong>{{ $site->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $site->slug }}</small>
                                    </div>
                                    <span class="badge bg-info">{{ number_format($site->users_count) }}명</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">데이터가 없습니다.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>게시글 수 TOP 10</h5>
            </div>
            <div class="card-body">
                @if($topSitesByPosts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topSitesByPosts as $index => $site)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-primary me-2">#{{ $index + 1 }}</span>
                                        <strong>{{ $site->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $site->slug }}</small>
                                    </div>
                                    <span class="badge bg-success">{{ number_format($site->posts_count) }}개</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">데이터가 없습니다.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection









