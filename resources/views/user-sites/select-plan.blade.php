@extends('layouts.app')

@section('title', '플랜 선택 - ' . $site->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>플랜 선택
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>안내:</strong> 홈페이지를 생성하기 전에 플랜을 선택하고 결제를 진행해주세요.
                    </div>

                    @if($freePlans->isEmpty() && $paidPlans->isEmpty())
                        <div class="text-center py-5">
                            <p class="text-muted">사용 가능한 플랜이 없습니다.</p>
                        </div>
                    @else
                        {{-- 무료 플랜 섹션 --}}
                        @if($freePlans->isNotEmpty())
                            <div class="mb-4">
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
                            <div class="mb-4">
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

