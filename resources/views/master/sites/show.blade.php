@extends('layouts.master')

@section('title', $site->name . ' - 상세 정보')
@section('page-title', $site->name)
@section('page-subtitle', '사이트 상세 정보 및 관리')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>사이트 정보</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">사이트 이름</dt>
                    <dd class="col-sm-9">{{ $site->name }}</dd>

                    <dt class="col-sm-3">슬러그</dt>
                    <dd class="col-sm-9"><code>{{ $site->slug }}</code></dd>

                    <dt class="col-sm-3">도메인</dt>
                    <dd class="col-sm-9">
                        @if($site->domain)
                            <a href="http://{{ $site->domain }}" target="_blank">
                                {{ $site->domain }} <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        @else
                            <span class="text-muted">설정되지 않음</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">요금제</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-{{ $site->plan === 'premium' ? 'danger' : ($site->plan === 'basic' ? 'warning' : 'secondary') }}">
                            {{ $site->plan === 'premium' ? '프리미엄' : ($site->plan === 'basic' ? '베이직' : '무료') }}
                        </span>
                    </dd>

                    <dt class="col-sm-3">상태</dt>
                    <dd class="col-sm-9">
                        @if($site->status === 'active')
                            <span class="badge bg-success">활성</span>
                        @elseif($site->status === 'suspended')
                            <span class="badge bg-warning text-dark">정지</span>
                        @else
                            <span class="badge bg-secondary">삭제됨</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">생성일</dt>
                    <dd class="col-sm-9">{{ $site->created_at->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">수정일</dt>
                    <dd class="col-sm-9">{{ $site->updated_at->format('Y-m-d H:i:s') }}</dd>
                </dl>

                @php
                    $storageUsed = $site->storage_used_mb ?? 0;
                    $storageLimit = $site->getTotalStorageLimit();
                    $storagePercent = $storageLimit > 0 ? min(100, ($storageUsed / $storageLimit) * 100) : 0;
                    $trafficUsed = $site->traffic_used_mb ?? 0;
                    $trafficLimit = $site->getTotalTrafficLimit();
                    $trafficPercent = $trafficLimit > 0 ? min(100, ($trafficUsed / $trafficLimit) * 100) : 0;
                @endphp

                <hr>

                <h6 class="mb-3"><i class="bi bi-hdd me-2"></i>저장 용량 사용량</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">사용 중</span>
                        <span class="fw-bold">
                            {{ number_format($storageUsed) }}MB / {{ $storageLimit > 0 ? number_format($storageLimit) . 'MB' : '무제한' }}
                            @if($storageLimit > 0)
                                ({{ number_format($storagePercent, 1) }}%)
                            @endif
                        </span>
                    </div>
                    @if($storageLimit > 0)
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar @if($storagePercent >= 90) bg-danger @elseif($storagePercent >= 70) bg-warning @else bg-success @endif" 
                                 role="progressbar" 
                                 style="width: {{ $storagePercent }}%"
                                 aria-valuenow="{{ $storagePercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($storagePercent, 1) }}%
                            </div>
                        </div>
                    @else
                        <div class="text-muted small">무제한</div>
                    @endif
                </div>

                <h6 class="mb-3"><i class="bi bi-arrow-left-right me-2"></i>트래픽 사용량</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">사용 중</span>
                        <span class="fw-bold">
                            {{ number_format($trafficUsed) }}MB / {{ $trafficLimit > 0 ? number_format($trafficLimit) . 'MB' : '무제한' }}
                            @if($trafficLimit > 0)
                                ({{ number_format($trafficPercent, 1) }}%)
                            @endif
                        </span>
                    </div>
                    @if($trafficLimit > 0)
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar @if($trafficPercent >= 90) bg-danger @elseif($trafficPercent >= 70) bg-warning @else bg-info @endif" 
                                 role="progressbar" 
                                 style="width: {{ $trafficPercent }}%"
                                 aria-valuenow="{{ $trafficPercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($trafficPercent, 1) }}%
                            </div>
                        </div>
                        @if($site->traffic_reset_date)
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>다음 리셋일: {{ \Carbon\Carbon::parse($site->traffic_reset_date)->addMonth()->startOfMonth()->format('Y-m-d') }}
                            </small>
                        @endif
                    @else
                        <div class="text-muted small">무제한</div>
                    @endif
                </div>

                <div class="mt-4">
                    <a href="{{ route('master.sites.edit', $site->id) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil me-1"></i>수정
                    </a>
                    @if($site->status === 'active')
                        <form action="{{ route('master.sites.suspend', $site->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning me-2">
                                <i class="bi bi-pause me-1"></i>정지
                            </button>
                        </form>
                    @elseif($site->status === 'suspended')
                        <form action="{{ route('master.sites.activate', $site->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success me-2">
                                <i class="bi bi-play me-1"></i>활성화
                            </button>
                        </form>
                    @endif
                    <button type="button" 
                            onclick="openSsoLogin({{ $site->id }});"
                            class="btn btn-info me-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i>SSO 로그인
                    </button>
                    <form action="{{ route('master.sites.destroy', $site->id) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>삭제
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>통계</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>사용자</span>
                        <strong>{{ number_format($stats['users']) }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>게시판</span>
                        <strong>{{ number_format($stats['boards']) }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>게시글</span>
                        <strong>{{ number_format($stats['posts']) }}</strong>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span>댓글</span>
                        <strong>{{ number_format($stats['comments']) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>빠른 링크</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('home', ['site' => $site->slug]) }}" 
                   target="_blank" 
                   class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-house me-1"></i>사이트 보기
                </a>
                <button type="button" 
                        onclick="openSsoLogin({{ $site->id }});"
                        class="btn btn-outline-info w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i>관리자로 로그인
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.openSsoLogin = function(siteId) {
        // CSRF 토큰 가져오기
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        // SSO 토큰 생성
        fetch(`/master/sites/${siteId}/sso-token`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // 새 창에서 SSO 로그인 URL 열기
                window.open(data.url, '_blank');
            } else {
                alert(data.message || 'SSO 로그인에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('SSO 로그인에 실패했습니다. 콘솔을 확인해주세요.');
        });
    };
});
</script>
@endpush
@endsection







