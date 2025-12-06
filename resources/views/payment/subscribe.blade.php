@extends('layouts.app')

@section('title', $plan->name . ' 구독하기')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-credit-card me-2"></i>{{ $plan->name }} 구독하기</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>{{ $plan->name }}</h5>
                        <p class="text-muted">{{ $plan->description }}</p>
                        <div class="mb-3">
                            <h3 class="text-primary">
                                @if($plan->billing_type === 'free')
                                    <span class="text-success">무료</span>
                                @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                    {{ number_format($plan->one_time_price) }}원
                                    <small class="text-muted" style="font-size: 0.5em;">(1회 결제)</small>
                                @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                    {{ number_format($plan->price) }}원/월
                                @else
                                    <span class="text-success">무료</span>
                                @endif
                            </h3>
                        </div>
                    </div>

                    @if($plan->features)
                        <div class="mb-4">
                            <h6>포함된 기능</h6>
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
                        <div class="mb-4">
                            <h6>제한 사항</h6>
                            @php
                                $limitLabels = [
                                    'users' => '사용자',
                                    'boards' => '게시판',
                                    'storage' => '저장공간 (MB)',
                                    'widgets' => '위젯',
                                    'custom_pages' => '커스텀 페이지',
                                ];
                            @endphp
                            <ul class="list-unstyled">
                                @if($plan->traffic_limit_mb)
                                    <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                @endif
                                @if(isset($plan->limits['storage']))
                                    <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB' }}</li>
                                @endif
                                @if($plan->limits)
                                    @foreach($plan->limits as $key => $limit)
                                        @if($key !== 'storage' && isset($limitLabels[$key]))
                                            <li><i class="bi bi-info-circle text-info me-2"></i>{{ $limitLabels[$key] }}: {{ $limit === null || $limit === '-' ? '무제한' : number_format($limit) }}</li>
                                        @endif
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    @endif

                    @if($plan->type === 'server')
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>안내:</strong> 서버 용량은 무료플랜에 적용 할 수 없습니다.
                        </div>

                        @if($userSites->isEmpty())
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <strong>알림:</strong> 서버 용량을 적용할 수 있는 사이트가 없습니다. 먼저 유료 플랜으로 사이트를 생성해주세요.
                            </div>
                        @else
                            <div class="mb-4">
                                <label for="target_site_id" class="form-label">
                                    <strong>적용할 사이트:</strong>
                                </label>
                                <select class="form-select" id="target_site_id" name="target_site_id" required>
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
                                <small class="text-muted">서버 용량을 적용할 사이트를 선택해주세요.</small>
                            </div>
                        @endif
                    @endif

                    <form method="POST" action="{{ route('payment.process-subscription', ['plan' => $plan->slug]) }}">
                        @csrf
                        @if($plan->type === 'server')
                            <input type="hidden" name="target_site_id" id="form_target_site_id">
                        @endif
                        <div class="d-grid gap-2">
                            @if($plan->billing_type === 'free')
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>시작하기
                                </button>
                            @elseif($plan->type === 'server')
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>구독하기
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>구매하기
                                </button>
                            @endif
                            <a href="/" class="btn btn-outline-secondary">
                                취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if($plan->type === 'server')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const siteSelect = document.getElementById('target_site_id');
    const formTargetSiteId = document.getElementById('form_target_site_id');
    const form = document.querySelector('form');
    const submitButton = form.querySelector('button[type="submit"]');

    if (siteSelect && formTargetSiteId) {
        // 드롭다운 변경 시 hidden input에 값 설정
        siteSelect.addEventListener('change', function() {
            formTargetSiteId.value = this.value;
            if (this.value) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        });

        // 폼 제출 전 검증
        form.addEventListener('submit', function(e) {
            if (!formTargetSiteId.value) {
                e.preventDefault();
                alert('적용할 사이트를 선택해주세요.');
                return false;
            }
        });

        // 초기 상태 설정
        if (!siteSelect.value) {
            submitButton.disabled = true;
        }
    }
});
</script>
@endpush
@endif
@endsection

