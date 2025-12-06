@extends('layouts.master')

@section('title', '요금제 관리')
@section('page-title', '요금제 관리')
@section('page-subtitle', '사이트별 요금제를 관리할 수 있습니다')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('master.plans.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>요금제 생성
    </a>
</div>

@php
    $hasFreePlans = $freePlans->count() > 0;
    $hasPaidPlans = $paidPlans->count() > 0;
    $hasServerPlans = isset($serverPlans) && $serverPlans->count() > 0;
@endphp

@if($hasFreePlans || $hasPaidPlans || $hasServerPlans)
    {{-- 무료 플랜 섹션 --}}
    @if($hasFreePlans)
        <div class="mb-4">
            <h5 class="mb-3"><i class="bi bi-gift me-2"></i>무료 플랜</h5>
            <div class="row">
                @foreach($freePlans as $plan)
                    <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">{{ $plan->type_name }}</span>
                            @if($plan->is_active)
                                <span class="badge bg-success">활성</span>
                            @else
                                <span class="badge bg-secondary">비활성</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $plan->name }}</h5>
                        @if($plan->description)
                            <p class="card-text text-muted small">{{ $plan->description }}</p>
                        @endif
                        <div class="mb-2">
                            <span class="badge bg-info">
                                <i class="bi bi-globe me-1"></i>적용된 사이트: {{ $plan->sites_count ?? 0 }}개
                            </span>
                        </div>
                        <div class="mb-3">
                            <h3 class="mb-0">
                                @if($plan->billing_type === 'free')
                                    <span class="text-success">무료</span>
                                @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                    {{ number_format($plan->one_time_price) }}원
                                    <small class="text-muted" style="font-size: 0.5em;">(1회 결제)</small>
                                @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                    {{ number_format($plan->price) }}원
                                    <small class="text-muted" style="font-size: 0.5em;">/월</small>
                                @else
                                    <span class="text-success">무료</span>
                                @endif
                            </h3>
                        </div>
                        <div class="mb-3">
                            <strong>주요 기능:</strong>
                            <ul class="list-unstyled mt-2 small">
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
                                    ];
                                    $planMainFeatures = $plan->main_features ?? [];
                                    $displayFeatures = array_slice($planMainFeatures, 0, 5);
                                @endphp
                                @if(count($displayFeatures) > 0)
                                    @foreach($displayFeatures as $feature)
                                        @if(isset($mainFeatures[$feature]))
                                            <li><i class="bi bi-check-circle text-success me-1"></i>{{ $mainFeatures[$feature] }}</li>
                                        @endif
                                    @endforeach
                                    @if(count($planMainFeatures) > 5)
                                        <li class="text-muted">+ {{ count($planMainFeatures) - 5 }}개 더</li>
                                    @endif
                                @else
                                    <li class="text-muted">선택된 기능이 없습니다.</li>
                                @endif
                            </ul>
                        </div>
                        <div class="mb-3">
                            <strong>제한 사항:</strong>
                            <ul class="list-unstyled mt-2 small">
                                @php
                                    $limitLabels = [
                                        'boards' => '게시판 수',
                                        'widgets' => '위젯 수',
                                        'custom_pages' => '커스텀 페이지 수',
                                        'users' => '사용자 수',
                                        'storage' => '스토리지',
                                    ];
                                    $planLimits = $plan->limits ?? [];
                                @endphp
                                @if($plan->traffic_limit_mb)
                                    <li>
                                        <i class="bi bi-arrow-left-right text-info me-1"></i>
                                        트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)
                                    </li>
                                @endif
                                @if(isset($planLimits['storage']))
                                    <li>
                                        <i class="bi bi-info-circle text-info me-1"></i>
                                        스토리지: 
                                        @if($planLimits['storage'] === null || $planLimits['storage'] === '-')
                                            <span class="text-success">무제한</span>
                                        @else
                                            {{ number_format($planLimits['storage']) }}MB
                                        @endif
                                    </li>
                                @endif
                                @if(count($planLimits) > 0)
                                    @foreach($planLimits as $key => $value)
                                        @if($key !== 'storage' && isset($limitLabels[$key]))
                                            <li>
                                                <i class="bi bi-info-circle text-info me-1"></i>
                                                {{ $limitLabels[$key] }}: 
                                                @if($value === null || $value === '-')
                                                    <span class="text-success">무제한</span>
                                                @else
                                                    {{ is_numeric($value) ? number_format($value) : $value }}
                                                @endif
                                            </li>
                                        @endif
                                    @endforeach
                                @else
                                    @if(!$plan->traffic_limit_mb && !isset($planLimits['storage']))
                                        <li class="text-muted">설정된 제한 사항이 없습니다.</li>
                                    @endif
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('master.plans.show', $plan->id) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye me-1"></i>보기
                            </a>
                            <a href="{{ route('master.plans.edit', $plan->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>수정
                            </a>
                        </div>
                    </div>
                </div>
            </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 유료 플랜 섹션 --}}
    @if($hasPaidPlans)
        <div class="mb-4">
            <h5 class="mb-3"><i class="bi bi-credit-card me-2"></i>유료 플랜</h5>
            <div class="row">
                @foreach($paidPlans as $plan)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 {{ $plan->is_default ? 'border-primary' : '' }}" style="{{ $plan->is_default ? 'border-width: 2px;' : '' }}">
                            @if($plan->is_default)
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-star-fill me-1"></i>기본 플랜</span>
                                        @if($plan->is_active)
                                            <span class="badge bg-light text-dark">활성</span>
                                        @else
                                            <span class="badge bg-secondary">비활성</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">{{ $plan->type_name }}</span>
                                        @if($plan->is_active)
                                            <span class="badge bg-success">활성</span>
                                        @else
                                            <span class="badge bg-secondary">비활성</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">{{ $plan->name }}</h5>
                                @if($plan->description)
                                    <p class="card-text text-muted small">{{ $plan->description }}</p>
                                @endif
                                <div class="mb-2">
                                    <span class="badge bg-info">
                                        <i class="bi bi-globe me-1"></i>적용된 사이트: {{ $plan->sites_count ?? 0 }}개
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <h3 class="mb-0">
                                        @if($plan->billing_type === 'free')
                                            <span class="text-success">무료</span>
                                        @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                            {{ number_format($plan->one_time_price) }}원
                                            <small class="text-muted" style="font-size: 0.5em;">(1회 결제)</small>
                                        @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                            {{ number_format($plan->price) }}원
                                            <small class="text-muted" style="font-size: 0.5em;">/월</small>
                                        @else
                                            <span class="text-success">무료</span>
                                        @endif
                                    </h3>
                                </div>
                                <div class="mb-3">
                                    <strong>주요 기능:</strong>
                                    <ul class="list-unstyled mt-2 small">
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
                                            ];
                                            $planMainFeatures = $plan->main_features ?? [];
                                            $displayFeatures = array_slice($planMainFeatures, 0, 5);
                                        @endphp
                                        @if(count($displayFeatures) > 0)
                                            @foreach($displayFeatures as $feature)
                                                @if(isset($mainFeatures[$feature]))
                                                    <li><i class="bi bi-check-circle text-success me-1"></i>{{ $mainFeatures[$feature] }}</li>
                                                @endif
                                            @endforeach
                                            @if(count($planMainFeatures) > 5)
                                                <li class="text-muted">+ {{ count($planMainFeatures) - 5 }}개 더</li>
                                            @endif
                                        @else
                                            <li class="text-muted">선택된 기능이 없습니다.</li>
                                        @endif
                                    </ul>
                                </div>
                                <div class="mb-3">
                                    <strong>제한 사항:</strong>
                                    <ul class="list-unstyled mt-2 small">
                                        @php
                                            $limitLabels = [
                                                'boards' => '게시판 수',
                                                'widgets' => '위젯 수',
                                                'custom_pages' => '커스텀 페이지 수',
                                                'users' => '사용자 수',
                                                'storage' => '스토리지',
                                            ];
                                            $planLimits = $plan->limits ?? [];
                                        @endphp
                                        @if($plan->traffic_limit_mb)
                                            <li>
                                                <i class="bi bi-arrow-left-right text-info me-1"></i>
                                                트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)
                                            </li>
                                        @endif
                                        @if(isset($planLimits['storage']))
                                            <li>
                                                <i class="bi bi-info-circle text-info me-1"></i>
                                                스토리지: 
                                                @if($planLimits['storage'] === null || $planLimits['storage'] === '-')
                                                    <span class="text-success">무제한</span>
                                                @else
                                                    {{ number_format($planLimits['storage']) }}MB
                                                @endif
                                            </li>
                                        @endif
                                        @if(count($planLimits) > 0)
                                            @foreach($planLimits as $key => $value)
                                                @if($key !== 'storage' && isset($limitLabels[$key]))
                                                    <li>
                                                        <i class="bi bi-info-circle text-info me-1"></i>
                                                        {{ $limitLabels[$key] }}: 
                                                        @if($value === null || $value === '-')
                                                            <span class="text-success">무제한</span>
                                                        @else
                                                            {{ is_numeric($value) ? number_format($value) : $value }}
                                                        @endif
                                                    </li>
                                                @endif
                                            @endforeach
                                        @else
                                            @if(!$plan->traffic_limit_mb && !isset($planLimits['storage']))
                                                <li class="text-muted">설정된 제한 사항이 없습니다.</li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('master.plans.show', $plan->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye me-1"></i>보기
                                    </a>
                                    <a href="{{ route('master.plans.edit', $plan->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil me-1"></i>수정
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 서버 용량 플랜 섹션 --}}
    @if($hasServerPlans)
        <div class="mb-4">
            <h5 class="mb-3"><i class="bi bi-server me-2"></i>서버 용량 플랜</h5>
            <div class="row">
                @foreach($serverPlans as $plan)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-info">
                            <div class="card-header bg-info text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-server me-1"></i>서버 용량</span>
                                    @if($plan->is_active)
                                        <span class="badge bg-light text-dark">활성</span>
                                    @else
                                        <span class="badge bg-secondary">비활성</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $plan->name }}</h5>
                                @if($plan->description)
                                    <p class="card-text text-muted small">{{ $plan->description }}</p>
                                @endif
                                <div class="mb-2">
                                    <span class="badge bg-info">
                                        <i class="bi bi-globe me-1"></i>적용된 사이트: {{ $plan->sites_count ?? 0 }}개
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <h3 class="mb-0">
                                        @if($plan->billing_type === 'free')
                                            <span class="text-success">무료</span>
                                        @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                            {{ number_format($plan->one_time_price) }}원
                                            <small class="text-muted" style="font-size: 0.5em;">(1회 결제)</small>
                                        @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                            {{ number_format($plan->price) }}원
                                            <small class="text-muted" style="font-size: 0.5em;">/월</small>
                                        @else
                                            <span class="text-success">무료</span>
                                        @endif
                                    </h3>
                                </div>
                                <div class="mb-3">
                                    <strong>제한 사항:</strong>
                                    <ul class="list-unstyled mt-2 small">
                                        @if($plan->traffic_limit_mb)
                                            <li>
                                                <i class="bi bi-arrow-left-right text-info me-1"></i>
                                                트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)
                                            </li>
                                        @endif
                                        @if(isset($plan->limits['storage']))
                                            <li>
                                                <i class="bi bi-info-circle text-info me-1"></i>
                                                스토리지: 
                                                @if($plan->limits['storage'] === null || $plan->limits['storage'] === '-')
                                                    <span class="text-success">무제한</span>
                                                @else
                                                    {{ number_format($plan->limits['storage']) }}MB
                                                @endif
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('master.plans.show', $plan->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye me-1"></i>보기
                                    </a>
                                    <a href="{{ route('master.plans.edit', $plan->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil me-1"></i>수정
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-credit-card display-1 text-muted"></i>
            <h4 class="mt-3 mb-2">등록된 요금제가 없습니다</h4>
            <p class="text-muted mb-4">첫 요금제를 생성해보세요!</p>
            <a href="{{ route('master.plans.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>요금제 생성
            </a>
        </div>
    </div>
@endif
@endsection

