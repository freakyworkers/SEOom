@extends('layouts.app')

@section('title', '서버 용량 적용 사이트 선택 - ' . $site->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-server me-2"></i>서버 용량 적용 사이트 선택
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <x-alert type="success">{{ session('success') }}</x-alert>
                    @endif
                    @if(session('error'))
                        <x-alert type="danger">{{ session('error') }}</x-alert>
                    @endif

                    <div class="mb-4">
                        <h5>서버 용량 플랜 정보</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>플랜명:</strong> {{ $plan->name }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>설명:</strong> {{ $plan->description }}
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <i class="bi bi-hdd me-2"></i>
                                        <strong>스토리지:</strong> 
                                        {{ number_format(($plan->limits['storage'] ?? 0) / 1024, 2) }} GB
                                    </div>
                                    <div class="col-md-6">
                                        <i class="bi bi-arrow-left-right me-2"></i>
                                        <strong>트래픽:</strong> 
                                        {{ number_format(($plan->traffic_limit_mb ?? 0) / 1024, 2) }} GB/월
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($isPlanChange)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>안내:</strong> 플랜 변경이 완료되었습니다. 서버 용량을 적용할 사이트를 선택해주세요.
                        </div>
                    @elseif(isset($isNewSubscription) && $isNewSubscription)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>안내:</strong> 서버 용량을 적용할 사이트를 선택해주세요. 선택 후 결제를 진행합니다.
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>결제 완료:</strong> 서버 용량 플랜 결제가 완료되었습니다. 서버 용량을 적용할 사이트를 선택해주세요.
                        </div>
                    @endif

                    <h5 class="mb-3">사이트 선택</h5>
                    <p class="text-muted mb-4">서버 용량을 적용할 사이트를 선택해주세요. 선택한 사이트에 스토리지와 트래픽 용량이 추가됩니다.</p>

                    <form method="POST" action="{{ route('payment.apply-server-capacity', ['site' => $site->slug]) }}" id="select-site-form">
                        @csrf
                        <input type="hidden" name="target_site_id" id="target_site_id" required>

                        <div class="row">
                            @foreach($userSites as $userSite)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100 site-card" data-site-id="{{ $userSite->id }}" style="cursor: pointer; transition: all 0.3s;">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="site_radio" id="site_{{ $userSite->id }}" value="{{ $userSite->id }}" required>
                                                <label class="form-check-label w-100" for="site_{{ $userSite->id }}">
                                                    <h6 class="mb-2">
                                                        {{ $userSite->name }}
                                                    </h6>
                                                    <p class="text-muted small mb-2">
                                                        <i class="bi bi-link-45deg me-1"></i>
                                                        @if($userSite->slug)
                                                            {{ url('/site/' . $userSite->slug) }}
                                                        @else
                                                            <span class="text-muted">슬러그 없음</span>
                                                        @endif
                                                    </p>
                                                    
                                                    <div class="small">
                                                        <div class="mb-1">
                                                            <i class="bi bi-hdd me-1"></i>
                                                            <strong>현재 스토리지:</strong> 
                                                            {{ number_format(($userSite->storage_limit_mb ?? 0) / 1024, 2) }} GB
                                                        </div>
                                                        <div class="mb-1">
                                                            <i class="bi bi-arrow-left-right me-1"></i>
                                                            <strong>현재 트래픽:</strong> 
                                                            {{ number_format(($userSite->traffic_limit_mb ?? 0) / 1024, 2) }} GB/월
                                                        </div>
                                                        <div class="text-success">
                                                            <i class="bi bi-plus-circle me-1"></i>
                                                            <strong>추가될 용량:</strong> 
                                                            스토리지 {{ number_format(($plan->limits['storage'] ?? 0) / 1024, 2) }} GB, 
                                                            트래픽 {{ number_format(($plan->traffic_limit_mb ?? 0) / 1024, 2) }} GB/월
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('target_site_id')
                            <div class="alert alert-danger mt-3">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="submit" class="btn btn-primary" id="apply-button" disabled>
                                <i class="bi bi-check-circle me-1"></i>서버 용량 적용하기
                            </button>
                            <a href="{{ route('users.my-sites', ['site' => $site->slug]) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const siteCards = document.querySelectorAll('.site-card');
    const applyButton = document.getElementById('apply-button');
    const targetSiteIdInput = document.getElementById('target_site_id');
    const form = document.getElementById('select-site-form');

    // 사이트 카드 클릭 이벤트
    siteCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // 라디오 버튼이 아닌 경우에만 처리
            if (e.target.type !== 'radio' && e.target.tagName !== 'INPUT') {
                const siteId = this.dataset.siteId;
                const radio = document.getElementById('site_' + siteId);
                if (radio) {
                    radio.checked = true;
                    targetSiteIdInput.value = siteId;
                    applyButton.disabled = false;
                    updateCardSelection();
                }
            }
        });
    });

    // 라디오 버튼 변경 이벤트
    document.querySelectorAll('input[name="site_radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            targetSiteIdInput.value = this.value;
            applyButton.disabled = false;
            updateCardSelection();
        });
    });

    // 카드 선택 상태 업데이트
    function updateCardSelection() {
        siteCards.forEach(card => {
            const siteId = card.dataset.siteId;
            const radio = document.getElementById('site_' + siteId);
            if (radio && radio.checked) {
                card.classList.add('border-primary');
                card.style.borderWidth = '2px';
                card.style.boxShadow = '0 0 0 0.2rem rgba(13, 110, 253, 0.25)';
            } else {
                card.classList.remove('border-primary');
                card.style.borderWidth = '';
                card.style.boxShadow = '';
            }
        });
    }

    // 폼 제출 전 확인
    form.addEventListener('submit', function(e) {
        if (!targetSiteIdInput.value) {
            e.preventDefault();
            alert('사이트를 선택해주세요.');
            return false;
        }
    });
});
</script>
<style>
.site-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}
</style>
@endpush
@endsection

