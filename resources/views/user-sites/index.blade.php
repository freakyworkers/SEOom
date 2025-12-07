@extends('layouts.app')

@section('title', '내 홈페이지' . (isset($site) && $site ? ' - ' . $site->name : ''))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm" style="display: block !important;">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-house-door me-2"></i>내 홈페이지
                </h4>
            </div>
            <div class="card-body">
                    @if(session('success'))
                        <x-alert type="success">{{ session('success') }}</x-alert>
                    @endif
                    @if(session('error'))
                        <x-alert type="error">{{ session('error') }}</x-alert>
                    @endif

                    @if($userSites->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                            <p class="mt-3 text-muted">아직 생성한 홈페이지가 없습니다.</p>
                            <a href="{{ route('user-sites.create', ['site' => $site->slug]) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>새 홈페이지 만들기
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($userSites as $userSite)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                @if($userSite->slug)
                                                    @php
                                                        $siteUrl = $userSite->domain 
                                                            ? 'https://' . $userSite->domain 
                                                            : 'https://' . $userSite->slug . '.' . config('app.master_domain', 'seoomweb.com');
                                                    @endphp
                                                    <a href="{{ $siteUrl }}" target="_blank" class="text-decoration-none">
                                                        {{ $userSite->name }}
                                                    </a>
                                                @else
                                                    {{ $userSite->name }}
                                                @endif
                                            </h5>
                                            <p class="text-muted small mb-2">
                                                <i class="bi bi-link-45deg me-1"></i>
                                                @if($userSite->domain)
                                                    <a href="https://{{ $userSite->domain }}" target="_blank" class="text-decoration-none">
                                                        {{ $userSite->domain }}
                                                    </a>
                                                    <span class="badge bg-success ms-2">커스텀 도메인</span>
                                                @elseif($userSite->slug)
                                                    @php
                                                        $subdomainUrl = 'https://' . $userSite->slug . '.' . config('app.master_domain', 'seoomweb.com');
                                                    @endphp
                                                    <a href="{{ $subdomainUrl }}" target="_blank" class="text-decoration-none">
                                                        {{ $subdomainUrl }}
                                                    </a>
                                                    <span class="badge bg-info ms-2">서브도메인</span>
                                                @else
                                                    <span class="text-muted">슬러그 없음</span>
                                                @endif
                                            </p>
                                            @if($userSite->domain)
                                                <p class="text-success small mb-2">
                                                    <i class="bi bi-check-circle me-1"></i>커스텀 도메인 연결됨
                                                </p>
                                            @endif
                                            
                                            @if($userSite->subscription)
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="badge bg-primary">
                                                            {{ $userSite->subscription->plan->name ?? '플랜 없음' }}
                                                        </span>
                                                        <span class="badge 
                                                            @if($userSite->subscription->status === 'active') bg-success
                                                            @elseif($userSite->subscription->status === 'trial') bg-info
                                                            @elseif($userSite->subscription->status === 'past_due') bg-warning
                                                            @elseif($userSite->subscription->status === 'suspended') bg-danger
                                                            @else bg-secondary
                                                            @endif">
                                                            @if($userSite->subscription->status === 'active') 활성
                                                            @elseif($userSite->subscription->status === 'trial') 체험 중
                                                            @elseif($userSite->subscription->status === 'past_due') 결제 대기
                                                            @elseif($userSite->subscription->status === 'suspended') 일시 중지
                                                            @else 취소됨
                                                            @endif
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="small text-muted">
                                                        <div class="mb-1">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            <strong>생성일자:</strong> 
                                                            {{ $userSite->created_at->format('Y-m-d') }}
                                                        </div>
                                                        <div class="mb-1">
                                                            <i class="bi bi-server me-1"></i>
                                                            <strong>서버:</strong> 
                                                            @php
                                                                $serverSubscription = $userSite->serverSubscription ?? null;
                                                            @endphp
                                                            @if($serverSubscription && $serverSubscription->plan)
                                                                {{ $serverSubscription->plan->name }}
                                                            @else
                                                                기본 서버
                                                            @endif
                                                        </div>
                                                        <div class="mb-1">
                                                            <i class="bi bi-calendar-x me-1"></i>
                                                            <strong>서버 결제일:</strong> 
                                                            @if($serverSubscription && $serverSubscription->current_period_end)
                                                                {{ $serverSubscription->current_period_end->format('Y-m-d') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    @php
                                                        $storageUsed = $userSite->storage_used_mb ?? 0;
                                                        $storageLimit = $userSite->getTotalStorageLimit();
                                                        $storagePercent = $storageLimit > 0 ? min(100, ($storageUsed / $storageLimit) * 100) : 0;
                                                        $trafficUsed = $userSite->traffic_used_mb ?? 0;
                                                        $trafficLimit = $userSite->getTotalTrafficLimit();
                                                        $trafficPercent = $trafficLimit > 0 ? min(100, ($trafficUsed / $trafficLimit) * 100) : 0;
                                                    @endphp
                                                    
                                                    <div class="mt-3">
                                                        <div class="mb-2">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <small class="text-muted">
                                                                    <i class="bi bi-hdd me-1"></i>저장 용량
                                                                </small>
                                                                <small class="text-muted">
                                                                    {{ number_format($storageUsed) }}MB / {{ $storageLimit > 0 ? number_format($storageLimit) . 'MB' : '무제한' }}
                                                                </small>
                                                            </div>
                                                            <div class="progress" style="height: 6px;">
                                                                <div class="progress-bar @if($storagePercent >= 90) bg-danger @elseif($storagePercent >= 70) bg-warning @else bg-success @endif" 
                                                                     role="progressbar" 
                                                                     style="width: {{ $storagePercent }}%"
                                                                     aria-valuenow="{{ $storagePercent }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <small class="text-muted">
                                                                    <i class="bi bi-arrow-left-right me-1"></i>트래픽
                                                                </small>
                                                                <small class="text-muted">
                                                                    {{ number_format($trafficUsed) }}MB / {{ $trafficLimit > 0 ? number_format($trafficLimit) . 'MB' : '무제한' }}
                                                                </small>
                                                            </div>
                                                            <div class="progress" style="height: 6px;">
                                                                <div class="progress-bar @if($trafficPercent >= 90) bg-danger @elseif($trafficPercent >= 70) bg-warning @else bg-info @endif" 
                                                                     role="progressbar" 
                                                                     style="width: {{ $trafficPercent }}%"
                                                                     aria-valuenow="{{ $trafficPercent }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mb-3">
                                                    <span class="badge bg-secondary">구독 없음</span>
                                                    <p class="small text-muted mt-2 mb-0">
                                                        아직 구독 플랜이 없습니다.
                                                    </p>
                                                </div>
                                            @endif
                                            
                                            <div class="d-flex flex-column gap-2">
                                                @php
                                                    $hasSubscription = $userSite->subscription;
                                                    $isActive = $hasSubscription && $userSite->subscription->status === 'active';
                                                    $isFreePlan = $hasSubscription && $userSite->subscription->plan && $userSite->subscription->plan->billing_type === 'free';
                                                @endphp
                                                @if($hasSubscription)
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('user-sites.change-plan', ['site' => $site->slug, 'userSite' => $userSite->slug]) }}" class="btn btn-sm btn-outline-secondary flex-fill plan-change-btn">
                                                            <i class="bi bi-arrow-left-right me-1"></i><span>플랜 변경하기</span>
                                                        </a>
                                                        @if($isActive && !$isFreePlan)
                                                            <a href="{{ route('user-sites.server-upgrade', ['site' => $site->slug, 'userSite' => $userSite->slug]) }}" class="btn btn-sm btn-outline-secondary flex-fill server-upgrade-btn">
                                                                <i class="bi bi-server me-1"></i><span>서버 업그레이드</span>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endif
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex gap-2">
                                                        @if($userSite->slug)
                                                            @php
                                                                $siteUrl = $userSite->domain 
                                                                    ? 'https://' . $userSite->domain 
                                                                    : 'https://' . $userSite->slug . '.' . config('app.master_domain', 'seoomweb.com');
                                                            @endphp
                                                            <a href="{{ $siteUrl }}" class="btn btn-sm btn-outline-primary flex-fill" target="_blank">
                                                                <i class="bi bi-box-arrow-up-right me-1"></i>사이트 보기
                                                            </a>
                                                            @if(auth()->user()->canManage() || $userSite->users()->where('id', auth()->id())->exists())
                                                                <a href="{{ route('admin.dashboard', ['site' => $userSite->slug]) }}" class="btn btn-sm btn-outline-secondary flex-fill">
                                                                    <i class="bi bi-gear me-1"></i>관리
                                                                </a>
                                                            @endif
                                                        @else
                                                            <span class="btn btn-sm btn-outline-secondary flex-fill disabled">슬러그 없음</span>
                                                        @endif
                                                    </div>
                                                    @if($hasSubscription && !$isFreePlan)
                                                        <a href="{{ route('user-sites.sso', ['site' => $site->slug, 'userSite' => $userSite->slug]) }}" class="btn btn-sm btn-outline-info w-100">
                                                            <i class="bi bi-globe me-1"></i>도메인 연결
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="{{ route('user-sites.create', ['site' => $site->slug]) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>새 홈페이지 만들기
                            </a>
                        </div>
                    @endif
            </div>
        </div>
    </div>
</div>

{{-- 도메인 연결 모달 --}}
@foreach($userSites as $userSite)
<div class="modal fade" id="domainModal{{ $userSite->id }}" tabindex="-1" aria-labelledby="domainModalLabel{{ $userSite->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="domainModalLabel{{ $userSite->id }}">
                    <i class="bi bi-globe me-2"></i>도메인 연결
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('user-sites.update-domain', ['site' => $site->slug, 'userSite' => $userSite->slug]) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="domain{{ $userSite->id }}" class="form-label">도메인</label>
                        <input type="text" 
                               class="form-control @error('domain') is-invalid @enderror" 
                               id="domain{{ $userSite->id }}" 
                               name="domain" 
                               value="{{ old('domain', $userSite->domain) }}"
                               placeholder="예: example.com">
                        @error('domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
    a.btn.btn-outline-secondary.plan-change-btn,
    a.btn.btn-outline-secondary.server-upgrade-btn {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        color: #6c757d !important;
    }
    
    a.btn.btn-outline-secondary.plan-change-btn:hover,
    a.btn.btn-outline-secondary.plan-change-btn:focus,
    a.btn.btn-outline-secondary.plan-change-btn:active,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus,
    a.btn.btn-outline-secondary.server-upgrade-btn:active {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: #ffffff !important;
    }
    
    a.btn.btn-outline-secondary.plan-change-btn:hover,
    a.btn.btn-outline-secondary.plan-change-btn:hover *,
    a.btn.btn-outline-secondary.plan-change-btn:hover i,
    a.btn.btn-outline-secondary.plan-change-btn:hover span,
    a.btn.btn-outline-secondary.plan-change-btn:focus,
    a.btn.btn-outline-secondary.plan-change-btn:focus *,
    a.btn.btn-outline-secondary.plan-change-btn:focus i,
    a.btn.btn-outline-secondary.plan-change-btn:focus span,
    a.btn.btn-outline-secondary.plan-change-btn:active,
    a.btn.btn-outline-secondary.plan-change-btn:active *,
    a.btn.btn-outline-secondary.plan-change-btn:active i,
    a.btn.btn-outline-secondary.plan-change-btn:active span,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover *,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover i,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover span,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus *,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus i,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus span,
    a.btn.btn-outline-secondary.server-upgrade-btn:active,
    a.btn.btn-outline-secondary.server-upgrade-btn:active *,
    a.btn.btn-outline-secondary.server-upgrade-btn:active i,
    a.btn.btn-outline-secondary.server-upgrade-btn:active span {
        color: #ffffff !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // 페이지 로드 후 높이 강제 계산
    document.addEventListener('DOMContentLoaded', function() {
        const card = document.querySelector('.card.shadow-sm');
        if (card && card.offsetHeight === 0) {
            // 강제로 높이 계산
            card.style.minHeight = '1px';
            const cardBody = card.querySelector('.card-body');
            if (cardBody) {
                cardBody.style.minHeight = '1px';
            }
            // 레이아웃 재계산
            void card.offsetHeight;
        }
    });
    
    // 네임서버 복사 기능
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check me-1"></i>복사됨';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
            
            setTimeout(function() {
                button.innerHTML = originalHtml;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        }).catch(function(err) {
            alert('복사에 실패했습니다. 수동으로 복사해주세요.');
        });
    }
    
    // Tooltip 초기화
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

