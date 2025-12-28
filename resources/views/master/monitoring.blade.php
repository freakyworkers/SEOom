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

<!-- AWS Instance Info & Server Resources -->
<div class="row mb-4">
    <!-- AWS EC2 Instance Info -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-cloud me-2"></i>AWS EC2 인스턴스 정보</h5>
            </div>
            <div class="card-body">
                @if(isset($awsInfo) && $awsInfo['is_aws'])
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 40%;">인스턴스 ID</th>
                                <td><code>{{ $awsInfo['instance_id'] ?? 'N/A' }}</code></td>
                            </tr>
                            <tr>
                                <th>인스턴스 타입</th>
                                <td><span class="badge bg-info">{{ $awsInfo['instance_type'] ?? 'N/A' }}</span></td>
                            </tr>
                            <tr>
                                <th>리전</th>
                                <td>{{ $awsInfo['region'] ?? 'N/A' }} ({{ $awsInfo['availability_zone'] ?? '' }})</td>
                            </tr>
                            <tr>
                                <th>퍼블릭 IP</th>
                                <td><code>{{ $awsInfo['public_ip'] ?? 'N/A' }}</code></td>
                            </tr>
                            <tr>
                                <th>프라이빗 IP</th>
                                <td><code>{{ $awsInfo['private_ip'] ?? 'N/A' }}</code></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    @if(isset($awsInfo['monthly_cost_estimate']) && $awsInfo['monthly_cost_estimate'])
                        <hr>
                        <div class="mt-3">
                            <h6 class="text-muted mb-2">💰 예상 월간 비용 (On-Demand 기준)</h6>
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <h4 class="mb-0 text-success">
                                        ${{ number_format($awsInfo['monthly_cost_estimate']['monthly'], 2) }} USD
                                    </h4>
                                    <small class="text-muted">
                                        ≈ ₩{{ number_format($awsInfo['monthly_cost_estimate']['monthly_krw']) }} 원
                                    </small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">시간당</small>
                                    <span class="fw-bold">${{ number_format($awsInfo['monthly_cost_estimate']['hourly'], 4) }}</span>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-info-circle me-1"></i>
                                예상 비용은 On-Demand 요금 기준이며, 실제 비용은 Reserved Instance, Savings Plan, 데이터 전송량 등에 따라 다를 수 있습니다.
                            </small>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-cloud-slash display-4 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">AWS EC2 인스턴스가 아니거나 메타데이터 서비스에 접근할 수 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Server Resources -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>서버 리소스 사용량</h5>
            </div>
            <div class="card-body">
                @if(isset($serverResources))
                    <!-- Uptime -->
                    @if($serverResources['uptime'])
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-clock-history me-2"></i>서버 가동 시간</span>
                                <span class="fw-bold">{{ $serverResources['uptime'] }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- CPU Usage -->
                    @if(isset($serverResources['load_average']))
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="bi bi-cpu me-2"></i>CPU 부하</span>
                                <span class="fw-bold">
                                    {{ $serverResources['load_average']['1min'] }} / {{ $serverResources['load_average']['5min'] }} / {{ $serverResources['load_average']['15min'] }}
                                </span>
                            </div>
                            @if(isset($serverResources['cpu_usage']))
                                @php
                                    $cpuClass = $serverResources['cpu_usage'] >= 80 ? 'bg-danger' : ($serverResources['cpu_usage'] >= 60 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $cpuClass }}" role="progressbar" 
                                         style="width: {{ min($serverResources['cpu_usage'], 100) }}%">
                                        {{ $serverResources['cpu_usage'] }}% ({{ $serverResources['cpu_cores'] ?? 1 }} cores)
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Memory Usage -->
                    @if(isset($serverResources['memory_percent']))
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="bi bi-memory me-2"></i>메모리</span>
                                <span class="fw-bold">
                                    {{ $serverResources['memory_used'] }} GB / {{ $serverResources['memory_total'] }} GB
                                </span>
                            </div>
                            @php
                                $memClass = $serverResources['memory_percent'] >= 90 ? 'bg-danger' : ($serverResources['memory_percent'] >= 70 ? 'bg-warning' : 'bg-info');
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $memClass }}" role="progressbar" 
                                     style="width: {{ $serverResources['memory_percent'] }}%">
                                    {{ $serverResources['memory_percent'] }}%
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Disk Usage -->
                    @if(isset($serverResources['disk_percent']))
                        <div class="mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="bi bi-hdd me-2"></i>디스크</span>
                                <span class="fw-bold">
                                    {{ $serverResources['disk_used'] }} GB / {{ $serverResources['disk_total'] }} GB
                                </span>
                            </div>
                            @php
                                $diskClass = $serverResources['disk_percent'] >= 90 ? 'bg-danger' : ($serverResources['disk_percent'] >= 70 ? 'bg-warning' : 'bg-primary');
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $diskClass }}" role="progressbar" 
                                     style="width: {{ $serverResources['disk_percent'] }}%">
                                    {{ $serverResources['disk_percent'] }}%
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-exclamation-circle display-4 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">서버 리소스 정보를 가져올 수 없습니다.</p>
                    </div>
                @endif
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










