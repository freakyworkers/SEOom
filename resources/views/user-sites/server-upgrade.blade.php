@extends('layouts.app')

@section('title', '서버 업그레이드 - ' . $site->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-server me-2"></i>서버 업그레이드
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <x-alert type="success">{{ session('success') }}</x-alert>
                    @endif
                    @if(session('error'))
                        <x-alert type="error">{{ session('error') }}</x-alert>
                    @endif
                    @if(session('info'))
                        <x-alert type="info">{{ session('info') }}</x-alert>
                    @endif

                    {{-- 현재 사이트 정보 --}}
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="bi bi-info-circle me-2"></i>현재 사이트 정보
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>사이트명:</strong> {{ $userSite->name }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>현재 플랜:</strong> {{ $subscription->plan->name }}
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <i class="bi bi-hdd me-2"></i>
                                        <strong>현재 저장 용량:</strong> 
                                        @php
                                            $currentStorage = $userSite->getTotalStorageLimit();
                                        @endphp
                                        {{ $currentStorage ? number_format($currentStorage) . 'MB (' . number_format($currentStorage / 1024, 2) . 'GB)' : '무제한' }}
                                    </div>
                                    <div class="col-md-6">
                                        <i class="bi bi-arrow-left-right me-2"></i>
                                        <strong>현재 트래픽:</strong> 
                                        @php
                                            $currentTraffic = $userSite->getTotalTrafficLimit();
                                        @endphp
                                        {{ $currentTraffic ? number_format($currentTraffic) . 'MB/월 (' . number_format($currentTraffic / 1024, 2) . 'GB/월)' : '무제한' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>안내:</strong> 서버 용량 플랜은 기존 용량에 추가로 제공됩니다. 선택한 서버 용량 플랜의 스토리지와 트래픽이 현재 사이트에 추가됩니다.
                    </div>

                    @if($serverPlans->isEmpty())
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            현재 사용 가능한 서버 용량 플랜이 없습니다.
                        </div>
                        <div class="text-center mt-4">
                            <a href="{{ route('users.my-sites', ['site' => $site->slug]) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>돌아가기
                            </a>
                        </div>
                    @else
                        {{-- 서버 용량 플랜 섹션 --}}
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="bi bi-server me-2 text-primary"></i>서버 용량 플랜
                            </h5>
                            <div class="row g-3">
                                @foreach($serverPlans as $plan)
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0 fw-bold">
                                                    <i class="bi bi-server me-2"></i>{{ $plan->name }}
                                                </h6>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <h3 class="card-title">
                                                    {{ number_format($plan->price) }}원
                                                    <small class="text-muted fw-light">/월</small>
                                                </h3>
                                                <p class="card-text text-muted small mb-3">{{ $plan->description }}</p>
                                                
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="small text-muted">
                                                            <i class="bi bi-hdd me-1"></i>추가 스토리지
                                                        </span>
                                                        <strong>{{ number_format(($plan->limits['storage'] ?? 0) / 1024, 2) }} GB</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-muted">
                                                            <i class="bi bi-arrow-left-right me-1"></i>추가 트래픽
                                                        </span>
                                                        <strong>{{ number_format(($plan->traffic_limit_mb ?? 0) / 1024, 2) }} GB/월</strong>
                                                    </div>
                                                </div>

                                                <div class="mt-auto">
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                            자세히 보기
                                                        </button>
                                                        <form method="POST" action="{{ route('payment.process-subscription', ['plan' => $plan->slug]) }}" class="mt-2">
                                                            @csrf
                                                            <input type="hidden" name="target_site_id" value="{{ $userSite->id }}">
                                                            <button type="submit" class="btn btn-primary w-100">
                                                                <i class="bi bi-check-circle me-1"></i>구독하기
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- 플랜 상세 모달 --}}
                                        <div class="modal fade" id="planModal{{ $plan->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ $plan->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-3">{{ $plan->description }}</p>
                                                        <div class="mb-3">
                                                            <h6>가격</h6>
                                                            <p class="h4 text-primary">
                                                                {{ number_format($plan->price) }}원/월
                                                            </p>
                                                        </div>
                                                        @if($plan->limits || $plan->traffic_limit_mb)
                                                            <div class="mb-3">
                                                                <h6>제한 사항</h6>
                                                                <ul class="list-unstyled">
                                                                    @if($plan->traffic_limit_mb)
                                                                        <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                                                    @endif
                                                                    @if(isset($plan->limits['storage']))
                                                                        <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB (' . number_format($plan->limits['storage'] / 1024, 2) . 'GB)' }}</li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif
                                                        <div class="alert alert-info mb-0">
                                                            <i class="bi bi-info-circle me-2"></i>
                                                            서버 용량 플랜은 기존 용량에 추가로 제공됩니다. 선택한 서버 용량 플랜의 스토리지와 트래픽이 현재 사이트에 추가됩니다.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                        <form method="POST" action="{{ route('payment.process-subscription', ['plan' => $plan->slug]) }}" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="target_site_id" value="{{ $userSite->id }}">
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="bi bi-check-circle me-1"></i>구독하기
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('users.my-sites', ['site' => $site->slug]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>돌아가기
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
