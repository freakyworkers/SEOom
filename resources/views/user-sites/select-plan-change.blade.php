@extends('layouts.app')

@section('title', '플랜 변경 - ' . $site->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-arrow-left-right me-2"></i>플랜 변경
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

                    @if($userSites->isEmpty())
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            플랜을 변경할 수 있는 사이트가 없습니다. 먼저 사이트를 생성하고 플랜을 구독해주세요.
                        </div>
                        <div class="text-center mt-4">
                            <a href="{{ route('users.my-sites', ['site' => $site->slug]) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>돌아가기
                            </a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('user-sites.change-plan-process', ['site' => $site->slug]) }}" id="plan-change-form">
                            @csrf
                            
                            {{-- 사이트 선택 --}}
                            <div class="mb-4">
                                <label for="target_site_id" class="form-label fw-bold">
                                    <i class="bi bi-globe me-2"></i>플랜을 변경할 사이트 선택
                                </label>
                                <select class="form-select form-select-lg" id="target_site_id" name="target_site_id" required>
                                    <option value="">사이트를 선택해주세요</option>
                                    @foreach($userSites as $siteItem)
                                        <option value="{{ $siteItem->id }}" 
                                                data-subscription-id="{{ $siteItem->subscription->id ?? '' }}"
                                                data-plan-id="{{ $siteItem->subscription->plan->id ?? '' }}"
                                                data-plan-name="{{ $siteItem->subscription->plan->name ?? '' }}"
                                                data-plan-price="{{ $siteItem->subscription->plan->one_time_price ?? ($siteItem->subscription->plan->price ?? 0) }}"
                                                @if($userSite && $userSite->id === $siteItem->id) selected @endif>
                                            {{ $siteItem->name }}
                                            @if($siteItem->subscription && $siteItem->subscription->plan)
                                                (현재: {{ $siteItem->subscription->plan->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">변경할 플랜을 적용할 사이트를 선택해주세요.</small>
                            </div>

                            {{-- 현재 플랜 정보 (사이트 선택 시 표시) --}}
                            <div id="current-plan-info" class="mb-4" style="display: none;">
                                <h5>현재 플랜</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="mb-2" id="current-plan-name">-</h6>
                                        <p class="text-muted mb-0">
                                            <strong>가격:</strong> <span id="current-plan-price">-</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- 무료 플랜 섹션 --}}
                            @if($freePlans->count() > 0)
                                <div class="mb-4">
                                    <h5 class="mb-3">
                                        <i class="bi bi-gift me-2 text-success"></i>무료 플랜
                                    </h5>
                                    <div class="row g-3">
                                        @foreach($freePlans as $plan)
                                            <div class="col-md-4">
                                                <div class="card h-100 plan-card shadow-sm plan-card-item" data-plan-id="{{ $plan->id }}" data-plan-price="0">
                                                    <div class="card-header bg-success text-white">
                                                        <div class="form-check">
                                                            <input class="form-check-input plan-radio" 
                                                                   type="radio" 
                                                                   name="plan_id" 
                                                                   id="plan_{{ $plan->id }}" 
                                                                   value="{{ $plan->id }}"
                                                                   required>
                                                            <label class="form-check-label fw-bold" for="plan_{{ $plan->id }}">
                                                                {{ $plan->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body d-flex flex-column">
                                                        <h3 class="card-title text-success">
                                                            무료
                                                        </h3>
                                                        <p class="card-text text-muted small mb-3">{{ $plan->description }}</p>
                                                        <div id="plan_{{ $plan->id }}_difference" class="plan-difference mb-3"></div>
                                                        <div class="mt-auto">
                                                            <div class="d-grid gap-2">
                                                                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                                    자세히 보기
                                                                </button>
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
                                                                    <p class="h4 text-success">무료</p>
                                                                </div>
                                                                @if($plan->features)
                                                                    @php
                                                                        $mainFeatures = [
                                                                            'dashboard' => '대시보드',
                                                                            'users' => '사용자 관리',
                                                                            'registration_settings' => '회원가입 설정',
                                                                            'mail_settings' => '메일 설정',
                                                                            'contact_forms' => '컨텍트 폼',
                                                                            'maps' => '지도',
                                                                            'crawlers' => '크롤러',
                                                                            'user_ranks' => '회원등급',
                                                                            'boards' => '게시판 관리',
                                                                            'posts' => '게시글 관리',
                                                                            'attendance' => '출석',
                                                                            'point_exchange' => '포인트 교환',
                                                                            'event_application' => '신청형 이벤트',
                                                                            'menus' => '메뉴 설정',
                                                                            'messages' => '쪽지 관리',
                                                                            'banners' => '배너',
                                                                            'popups' => '팝업',
                                                                            'blocked_ips' => '아이피 차단',
                                                                            'custom_code' => '코드 커스텀',
                                                                            'settings' => '사이트 설정',
                                                                            'sidebar_widgets' => '사이드 위젯',
                                                                            'main_widgets' => '메인 위젯',
                                                                            'custom_pages' => '커스텀 페이지',
                                                                            'toggle_menus' => '토글 메뉴',
                                                                        ];
                                                                    @endphp
                                                                    <div class="mb-3">
                                                                        <h6>포함된 기능</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if(isset($plan->features['main_features']))
                                                                                @foreach($plan->features['main_features'] as $feature)
                                                                                    <li><i class="bi bi-check-circle text-success me-2"></i>{{ $mainFeatures[$feature] ?? $feature }}</li>
                                                                                @endforeach
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                                @if($plan->limits || $plan->traffic_limit_mb)
                                                                    <div class="mb-3">
                                                                        <h6>제한 사항</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if($plan->traffic_limit_mb)
                                                                                <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                                                            @endif
                                                                            @if(isset($plan->limits['storage']))
                                                                                <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB' }}</li>
                                                                            @endif
                                                                            @if($plan->limits)
                                                                                @php
                                                                                    $limitLabels = [
                                                                                        'boards' => '게시판 수',
                                                                                        'widgets' => '위젯 수',
                                                                                        'custom_pages' => '커스텀 페이지 수',
                                                                                        'users' => '사용자 수',
                                                                                    ];
                                                                                @endphp
                                                                                @foreach($plan->limits as $key => $limit)
                                                                                    @if($key !== 'storage' && isset($limitLabels[$key]))
                                                                                        <li><i class="bi bi-info-circle text-info me-2"></i>{{ $limitLabels[$key] }}: {{ $limit === null || $limit === '-' ? '무제한' : number_format($limit) }}</li>
                                                                                    @endif
                                                                                @endforeach
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- 유료 플랜 섹션 --}}
                            @if($paidPlans->count() > 0)
                                <div class="mb-4">
                                    <h5 class="mb-3">
                                        <i class="bi bi-credit-card me-2 text-primary"></i>유료 플랜
                                    </h5>
                                    <div class="row g-3">
                                        @foreach($paidPlans as $plan)
                                            <div class="col-md-4">
                                                <div class="card h-100 plan-card shadow-sm plan-card-item" data-plan-id="{{ $plan->id }}" data-plan-price="{{ $plan->one_time_price ?? 0 }}">
                                                    <div class="card-header bg-primary text-white">
                                                        <div class="form-check">
                                                            <input class="form-check-input plan-radio" 
                                                                   type="radio" 
                                                                   name="plan_id" 
                                                                   id="plan_{{ $plan->id }}" 
                                                                   value="{{ $plan->id }}"
                                                                   required>
                                                            <label class="form-check-label fw-bold" for="plan_{{ $plan->id }}">
                                                                {{ $plan->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body d-flex flex-column">
                                                        <h3 class="card-title">
                                                            @if($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                {{ number_format($plan->one_time_price) }}원
                                                                <small class="text-muted fw-light" style="font-size: 0.4em;">(1회 결제)</small>
                                                            @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                {{ number_format($plan->price) }}원
                                                                <small class="text-muted fw-light">/월</small>
                                                            @else
                                                                <span class="text-success">무료</span>
                                                            @endif
                                                        </h3>
                                                        <p class="card-text text-muted small mb-3">{{ $plan->description }}</p>
                                                        <div id="plan_{{ $plan->id }}_difference" class="plan-difference mb-3"></div>
                                                        <div class="mt-auto">
                                                            <div class="d-grid gap-2">
                                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                                    자세히 보기
                                                                </button>
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
                                                                        @if($plan->billing_type === 'free')
                                                                            <span class="text-success">무료</span>
                                                                        @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                            {{ number_format($plan->one_time_price) }}원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>
                                                                        @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                            {{ number_format($plan->price) }}원/월
                                                                        @else
                                                                            <span class="text-success">무료</span>
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                                @if($plan->features)
                                                                    @php
                                                                        $mainFeatures = [
                                                                            'dashboard' => '대시보드',
                                                                            'users' => '사용자 관리',
                                                                            'registration_settings' => '회원가입 설정',
                                                                            'mail_settings' => '메일 설정',
                                                                            'contact_forms' => '컨텍트 폼',
                                                                            'maps' => '지도',
                                                                            'crawlers' => '크롤러',
                                                                            'user_ranks' => '회원등급',
                                                                            'boards' => '게시판 관리',
                                                                            'posts' => '게시글 관리',
                                                                            'attendance' => '출석',
                                                                            'point_exchange' => '포인트 교환',
                                                                            'event_application' => '신청형 이벤트',
                                                                            'menus' => '메뉴 설정',
                                                                            'messages' => '쪽지 관리',
                                                                            'banners' => '배너',
                                                                            'popups' => '팝업',
                                                                            'blocked_ips' => '아이피 차단',
                                                                            'custom_code' => '코드 커스텀',
                                                                            'settings' => '사이트 설정',
                                                                            'sidebar_widgets' => '사이드 위젯',
                                                                            'main_widgets' => '메인 위젯',
                                                                            'custom_pages' => '커스텀 페이지',
                                                                            'toggle_menus' => '토글 메뉴',
                                                                        ];
                                                                    @endphp
                                                                    <div class="mb-3">
                                                                        <h6>포함된 기능</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if(isset($plan->features['main_features']))
                                                                                @foreach($plan->features['main_features'] as $feature)
                                                                                    <li><i class="bi bi-check-circle text-success me-2"></i>{{ $mainFeatures[$feature] ?? $feature }}</li>
                                                                                @endforeach
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                                @if($plan->limits || $plan->traffic_limit_mb)
                                                                    <div class="mb-3">
                                                                        <h6>제한 사항</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if($plan->traffic_limit_mb)
                                                                                <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                                                            @endif
                                                                            @if(isset($plan->limits['storage']))
                                                                                <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB' }}</li>
                                                                            @endif
                                                                            @if($plan->limits)
                                                                                @php
                                                                                    $limitLabels = [
                                                                                        'boards' => '게시판 수',
                                                                                        'widgets' => '위젯 수',
                                                                                        'custom_pages' => '커스텀 페이지 수',
                                                                                        'users' => '사용자 수',
                                                                                    ];
                                                                                @endphp
                                                                                @foreach($plan->limits as $key => $limit)
                                                                                    @if($key !== 'storage' && isset($limitLabels[$key]))
                                                                                        <li><i class="bi bi-info-circle text-info me-2"></i>{{ $limitLabels[$key] }}: {{ $limit === null || $limit === '-' ? '무제한' : number_format($limit) }}</li>
                                                                                    @endif
                                                                                @endforeach
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif


                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="{{ route('users.my-sites', ['site' => $site->slug]) }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>취소
                                </a>
                                <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                    <i class="bi bi-check-circle me-1"></i>플랜 변경하기
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const siteSelect = document.getElementById('target_site_id');
    const planRadios = document.querySelectorAll('input[name="plan_id"]');
    const form = document.getElementById('plan-change-form');
    const submitBtn = document.getElementById('submit-btn');
    const currentPlanInfo = document.getElementById('current-plan-info');
    const currentPlanName = document.getElementById('current-plan-name');
    const currentPlanPrice = document.getElementById('current-plan-price');
    
    let currentPlanId = null;
    let currentPlanPriceValue = 0;

    // 사이트 선택 시 현재 플랜 정보 표시
    siteSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            currentPlanId = selectedOption.dataset.planId;
            currentPlanPriceValue = parseFloat(selectedOption.dataset.planPrice) || 0;
            const planName = selectedOption.dataset.planName || '-';
            
            currentPlanName.textContent = planName;
            currentPlanPrice.innerHTML = currentPlanPriceValue > 0 ? number_format(currentPlanPriceValue) + '원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>' : '무료';
            currentPlanInfo.style.display = 'block';
            
                                            // 폼 action은 그대로 유지 (target_site_id로 처리)
            
            // 플랜 차이 계산 업데이트
            updatePlanDifferences();
        } else {
            currentPlanInfo.style.display = 'none';
            submitBtn.disabled = true;
        }
        checkFormValidity();
    });

    // 플랜 선택 시 차이 계산 및 스타일 업데이트
    planRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            updatePlanDifferences();
            updateSelectedPlanStyle();
            checkFormValidity();
        });
    });

    // 선택된 플랜 카드 스타일 업데이트
    function updateSelectedPlanStyle() {
        // 모든 카드에서 선택 스타일 제거
        document.querySelectorAll('.plan-card-item').forEach(card => {
            card.classList.remove('border-primary', 'border-success', 'border-3', 'shadow-lg', 'selected-plan');
            card.style.transform = '';
            card.style.transition = '';
        });

        // 선택된 플랜 카드에 스타일 적용
        const selectedRadio = document.querySelector('input[name="plan_id"]:checked');
        if (selectedRadio) {
            const selectedCard = selectedRadio.closest('.plan-card-item');
            if (selectedCard) {
                const isFreePlan = selectedCard.querySelector('.bg-success') !== null;
                selectedCard.classList.add('border-3', 'shadow-lg', 'selected-plan');
                selectedCard.classList.add(isFreePlan ? 'border-success' : 'border-primary');
                selectedCard.style.transform = 'scale(1.02)';
                selectedCard.style.transition = 'all 0.3s ease';
            }
        }
    }

    function updatePlanDifferences() {
        if (!currentPlanId) return;

        planRadios.forEach(radio => {
            const planCard = radio.closest('.plan-card');
            const planId = planCard.dataset.planId;
            const planPrice = parseFloat(planCard.dataset.planPrice) || 0;
            const differenceDiv = document.getElementById('plan_' + planId + '_difference');
            
            if (planId === currentPlanId) {
                differenceDiv.innerHTML = '<div class="alert alert-info mb-0"><small><i class="bi bi-info-circle me-1"></i>현재 플랜</small></div>';
            } else {
                const difference = planPrice - currentPlanPriceValue;
                if (difference > 0) {
                    differenceDiv.innerHTML = '<div class="alert alert-warning mb-0"><small><i class="bi bi-arrow-up me-1"></i>상향: +' + number_format(difference) + '원 <span style="font-size: 0.7em;">(1회 결제)</span></small></div>';
                } else if (difference < 0) {
                    differenceDiv.innerHTML = '<div class="alert alert-success mb-0"><small><i class="bi bi-arrow-down me-1"></i>하향: ' + number_format(difference) + '원 <span style="font-size: 0.7em;">(1회 결제)</span></small></div>';
                } else {
                    differenceDiv.innerHTML = '';
                }
            }
        });
    }

    function checkFormValidity() {
        const siteSelected = siteSelect.value !== '';
        const planSelected = document.querySelector('input[name="plan_id"]:checked') !== null;
        submitBtn.disabled = !(siteSelected && planSelected);
    }

    function number_format(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // 초기 상태 설정
    if (siteSelect.value) {
        siteSelect.dispatchEvent(new Event('change'));
    }

    // 초기 선택된 플랜 스타일 적용
    updateSelectedPlanStyle();
});
</script>

<style>
.selected-plan {
    border-width: 3px !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 123, 255, 0.3) !important;
    position: relative;
}

.selected-plan::before {
    content: '✓ 선택됨';
    position: absolute;
    top: -10px;
    right: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: bold;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.plan-card-item {
    transition: all 0.3s ease;
    cursor: pointer;
}

.plan-card-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

.plan-card-item.selected-plan:hover {
    transform: scale(1.02) translateY(-5px);
}
</style>
@endpush
@endsection

