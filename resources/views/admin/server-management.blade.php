@extends('layouts.admin')

@section('title', '서버 관리 - ' . $site->name)

@section('page-title', '서버 관리')
@section('page-subtitle', '사이트의 서버 용량 및 구독 정보를 확인하고 관리할 수 있습니다.')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-server me-2"></i>서버 정보
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">생성일자</th>
                        <td>{{ $site->created_at->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>서버</th>
                        <td>
                            @if($serverPlan)
                                <span class="badge bg-primary">{{ $serverPlan->name }}</span>
                            @else
                                <span class="badge bg-secondary">기본 서버</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>서버 결제일</th>
                        <td>
                            @if($serverSubscription && $serverSubscription->current_period_end)
                                {{ \Carbon\Carbon::parse($serverSubscription->current_period_end)->format('Y-m-d') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>현재 플랜</th>
                        <td>
                            @if($plan)
                                <span class="badge bg-info">{{ $plan->name }}</span>
                            @else
                                <span class="badge bg-secondary">플랜 없음</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-database me-2"></i>저장 용량
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>사용량:</strong> {{ number_format($storageUsedMB, 2) }} MB ({{ number_format($storageUsedMB / 1024, 2) }} GB)</span>
                        <span>
                            @if($storageLimitMB)
                                <strong>제한:</strong> {{ number_format($storageLimitMB, 2) }} MB ({{ number_format($storageLimitMB / 1024, 2) }} GB)
                            @else
                                <strong>제한:</strong> 무제한
                            @endif
                        </span>
                    </div>
                    @if($storageLimitMB)
                        <div class="progress" style="height: 25px;">
                            @php
                                $storagePercentage = min(100, ($storageUsedMB / $storageLimitMB) * 100);
                                $progressColor = $storagePercentage >= 90 ? 'danger' : ($storagePercentage >= 70 ? 'warning' : 'success');
                            @endphp
                            <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" 
                                 style="width: {{ $storagePercentage }}%" 
                                 aria-valuenow="{{ $storagePercentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($storagePercentage, 1) }}%
                            </div>
                        </div>
                    @else
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%">
                                무제한
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>트래픽 용량
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>사용량:</strong> {{ number_format($trafficUsedMB, 2) }} MB ({{ number_format($trafficUsedMB / 1024, 2) }} GB)</span>
                        <span>
                            @if($trafficLimitMB)
                                <strong>제한:</strong> {{ number_format($trafficLimitMB, 2) }} MB ({{ number_format($trafficLimitMB / 1024, 2) }} GB)
                            @else
                                <strong>제한:</strong> 무제한
                            @endif
                        </span>
                    </div>
                    @if($trafficLimitMB)
                        <div class="progress" style="height: 25px;">
                            @php
                                $trafficPercentage = min(100, ($trafficUsedMB / $trafficLimitMB) * 100);
                                $progressColor = $trafficPercentage >= 90 ? 'danger' : ($trafficPercentage >= 70 ? 'warning' : 'info');
                            @endphp
                            <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" 
                                 style="width: {{ $trafficPercentage }}%" 
                                 aria-valuenow="{{ $trafficPercentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($trafficPercentage, 1) }}%
                            </div>
                        </div>
                    @else
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 100%">
                                무제한
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>관리
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($masterSite)
                        <a href="https://seoomweb.com" target="_blank" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left-right me-2"></i>플랜 변경하기
                        </a>
                        <a href="https://seoomweb.com" target="_blank" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-server me-2"></i>서버 업그레이드
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

