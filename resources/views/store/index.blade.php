@extends('layouts.app')

@section('title', '스토어 - ' . $site->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-shop me-2"></i>스토어
                    </h4>
                </div>
                <div class="card-body">
                    {{-- 무료 플랜 섹션 --}}
                    @if($freePlans->isNotEmpty())
                        <div class="mb-5">
                            <h5 class="mb-3">
                                <i class="bi bi-gift me-2 text-success"></i>무료 플랜
                            </h5>
                            <div class="row g-3">
                                @foreach($freePlans as $plan)
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">{{ $plan->name }}</h5>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <h3 class="card-title text-success">
                                                    무료
                                                </h3>
                                                <p class="card-text text-muted small mb-3">{{ $plan->description }}</p>
                                                <div class="mt-auto">
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                            자세히 보기
                                                        </button>
                                                        <form method="GET" action="{{ route('user-sites.create', ['site' => $site->slug]) }}">
                                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                                <i class="bi bi-check-circle me-1"></i>시작하기
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
                                                        <form method="GET" action="{{ route('user-sites.create', ['site' => $site->slug]) }}" class="d-inline">
                                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="bi bi-check-circle me-1"></i>시작하기
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

                    {{-- 유료 플랜 섹션 --}}
                    @if($paidPlans->isNotEmpty())
                        <div class="mb-5">
                            <h5 class="mb-3">
                                <i class="bi bi-credit-card me-2 text-primary"></i>유료 플랜
                            </h5>
                            <div class="row g-3">
                                @foreach($paidPlans as $plan)
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0">{{ $plan->name }}</h5>
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
                                                <div class="mt-auto">
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                            자세히 보기
                                                        </button>
                                                        <form method="POST" action="{{ route('payment.process-subscription', ['plan' => $plan->slug]) }}">
                                                            @csrf
                                                            <input type="hidden" name="create_site" value="1">
                                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                                <i class="bi bi-check-circle me-1"></i>구매하기
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
                                                        <form method="POST" action="{{ route('payment.process-subscription', ['plan' => $plan->slug]) }}" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="create_site" value="1">
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="bi bi-check-circle me-1"></i>구매하기
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

                    {{-- 서버 용량 섹션 --}}
                    @if($serverPlans->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="bi bi-server me-2 text-primary"></i>서버 용량
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
                                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#serverPlanModal{{ $plan->id }}">
                                                            자세히 보기
                                                        </button>
                                                        @if($userSites->isEmpty())
                                                            <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
                                                                <i class="bi bi-exclamation-circle me-1"></i>적용 가능한 사이트가 없습니다
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#serverPlanModal{{ $plan->id }}">
                                                                <i class="bi bi-check-circle me-1"></i>구독하기
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- 서버 플랜 상세 모달 --}}
                                        <div class="modal fade" id="serverPlanModal{{ $plan->id }}" tabindex="-1">
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
                                                        <div class="alert alert-info mb-3">
                                                            <i class="bi bi-info-circle me-2"></i>
                                                            서버 용량 플랜은 기존 용량에 추가로 제공됩니다. 선택한 서버 용량 플랜의 스토리지와 트래픽이 현재 사이트에 추가됩니다.
                                                        </div>
                                                        
                                                        @if($userSites->isEmpty())
                                                            <div class="alert alert-warning mb-0">
                                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                                적용 가능한 사이트가 없습니다. 먼저 유료 플랜을 구독한 사이트를 생성해주세요.
                                                            </div>
                                                        @else
                                                            <div class="mb-3">
                                                                <label for="serverSiteSelect{{ $plan->id }}" class="form-label fw-bold">
                                                                    <i class="bi bi-globe me-2"></i>적용할 사이트 선택
                                                                </label>
                                                                <select class="form-select" id="serverSiteSelect{{ $plan->id }}" name="target_site_id" required>
                                                                    <option value="">사이트를 선택해주세요</option>
                                                                    @foreach($userSites as $userSite)
                                                                        <option value="{{ $userSite->id }}">
                                                                            {{ $userSite->name }}
                                                                            @if($userSite->subscription && $userSite->subscription->plan)
                                                                                ({{ $userSite->subscription->plan->name }})
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                        @if($userSites->isNotEmpty())
                                                            <form method="POST" action="{{ route('payment.process-subscription', ['plan' => $plan->slug]) }}" class="d-inline" id="serverPlanForm{{ $plan->id }}">
                                                                @csrf
                                                                <input type="hidden" name="target_site_id" id="serverPlanSiteId{{ $plan->id }}" value="">
                                                                <button type="submit" class="btn btn-primary" id="serverPlanSubmit{{ $plan->id }}" disabled>
                                                                    <i class="bi bi-check-circle me-1"></i>구독하기
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 서버 플랜 모달의 사이트 선택 처리
    @foreach($serverPlans as $plan)
        @if($userSites->isNotEmpty())
            const serverSiteSelect{{ $plan->id }} = document.getElementById('serverSiteSelect{{ $plan->id }}');
            const serverPlanSiteId{{ $plan->id }} = document.getElementById('serverPlanSiteId{{ $plan->id }}');
            const serverPlanSubmit{{ $plan->id }} = document.getElementById('serverPlanSubmit{{ $plan->id }}');
            
            if (serverSiteSelect{{ $plan->id }}) {
                serverSiteSelect{{ $plan->id }}.addEventListener('change', function() {
                    if (this.value) {
                        serverPlanSiteId{{ $plan->id }}.value = this.value;
                        serverPlanSubmit{{ $plan->id }}.disabled = false;
                    } else {
                        serverPlanSiteId{{ $plan->id }}.value = '';
                        serverPlanSubmit{{ $plan->id }}.disabled = true;
                    }
                });
            }
        @endif
    @endforeach
});
</script>
@endpush
@endsection

