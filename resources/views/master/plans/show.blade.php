@extends('layouts.master')

@section('title', $plan->name . ' - 상세 정보')
@section('page-title', $plan->name)
@section('page-subtitle', '요금제 상세 정보 및 관리')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>요금제 정보</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">플랜 이름</dt>
                    <dd class="col-sm-9">
                        {{ $plan->name }}
                        @if($plan->is_default)
                            <span class="badge bg-primary ms-2">기본 플랜</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">슬러그</dt>
                    <dd class="col-sm-9"><code>{{ $plan->slug }}</code></dd>

                    <dt class="col-sm-3">플랜 타입</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-info">{{ $plan->type_name }}</span>
                    </dd>

                    <dt class="col-sm-3">결제 유형</dt>
                    <dd class="col-sm-9">
                        @if($plan->billing_type === 'free')
                            <span class="text-success"><strong>무료</strong></span>
                        @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                            <strong>{{ number_format($plan->one_time_price) }}원</strong> <span class="text-muted">(1회 결제)</span>
                        @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                            <strong>{{ number_format($plan->price) }}원</strong> <span class="text-muted">(월간)</span>
                        @else
                            <span class="text-success"><strong>무료</strong></span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">상태</dt>
                    <dd class="col-sm-9">
                        @if($plan->is_active)
                            <span class="badge bg-success">활성</span>
                        @else
                            <span class="badge bg-secondary">비활성</span>
                        @endif
                    </dd>

                    @if($plan->description)
                        <dt class="col-sm-3">설명</dt>
                        <dd class="col-sm-9">{{ $plan->description }}</dd>
                    @endif

                    <dt class="col-sm-3">생성일</dt>
                    <dd class="col-sm-9">{{ $plan->created_at->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">수정일</dt>
                    <dd class="col-sm-9">{{ $plan->updated_at->format('Y-m-d H:i:s') }}</dd>
                </dl>

                <div class="mt-4">
                    <a href="{{ route('master.plans.edit', $plan->id) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil me-1"></i>수정
                    </a>
                    <form action="{{ route('master.plans.destroy', $plan->id) }}" 
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

        <!-- 대 메뉴 기능 -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>대 메뉴 기능</h5>
            </div>
            <div class="card-body">
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
                @endphp
                @if(count($planMainFeatures) > 0)
                    <div class="row">
                        @foreach($planMainFeatures as $feature)
                            @if(isset($mainFeatures[$feature]))
                                <div class="col-md-4 mb-2">
                                    <i class="bi bi-check-circle text-success me-1"></i>{{ $mainFeatures[$feature] }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">선택된 기능이 없습니다.</p>
                @endif
            </div>
        </div>

        <!-- 게시판 타입 -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-grid me-2"></i>게시판 타입</h5>
            </div>
            <div class="card-body">
                @php
                    $boardTypes = \App\Models\Board::getTypes();
                    $planBoardTypes = $plan->board_types ?? [];
                @endphp
                @if(count($planBoardTypes) > 0)
                    <div class="row">
                        @foreach($planBoardTypes as $type)
                            @if(isset($boardTypes[$type]))
                                <div class="col-md-4 mb-2">
                                    <i class="bi bi-check-circle text-success me-1"></i>{{ $boardTypes[$type] }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">선택된 게시판 타입이 없습니다.</p>
                @endif
            </div>
        </div>

        <!-- 회원가입 세부 기능 -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>회원가입 세부 기능</h5>
            </div>
            <div class="card-body">
                @php
                    $registrationFeatures = [
                        'signup_points' => '가입 포인트',
                        'email_verification' => '이메일 인증',
                        'phone_verification' => '전화번호 인증',
                        'identity_verification' => '본인인증',
                        'referrer' => '추천인 기능',
                    ];
                    $planRegFeatures = $plan->registration_features ?? [];
                @endphp
                @if(count($planRegFeatures) > 0)
                    <div class="row">
                        @foreach($planRegFeatures as $feature)
                            @if(isset($registrationFeatures[$feature]))
                                <div class="col-md-6 mb-2">
                                    <i class="bi bi-check-circle text-success me-1"></i>{{ $registrationFeatures[$feature] }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">선택된 회원가입 세부 기능이 없습니다.</p>
                @endif
            </div>
        </div>

        <!-- 사이드바 위젯 타입 -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-layout-sidebar me-2"></i>사이드바 위젯 타입</h5>
            </div>
            <div class="card-body">
                @php
                    $sidebarWidgetTypes = \App\Models\SidebarWidget::getAvailableTypes();
                    $planSidebarWidgetTypes = $plan->sidebar_widget_types ?? [];
                @endphp
                @if(count($planSidebarWidgetTypes) > 0)
                    <div class="row">
                        @foreach($planSidebarWidgetTypes as $type)
                            @if(isset($sidebarWidgetTypes[$type]))
                                <div class="col-md-4 mb-2">
                                    <i class="bi bi-check-circle text-success me-1"></i>{{ $sidebarWidgetTypes[$type] }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">선택된 사이드바 위젯 타입이 없습니다.</p>
                @endif
            </div>
        </div>

        <!-- 메인 위젯 타입 -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>메인 위젯 타입</h5>
            </div>
            <div class="card-body">
                @php
                    $mainWidgetTypes = \App\Models\MainWidget::getAvailableTypes();
                    $planMainWidgetTypes = $plan->main_widget_types ?? [];
                @endphp
                @if(count($planMainWidgetTypes) > 0)
                    <div class="row">
                        @foreach($planMainWidgetTypes as $type)
                            @if(isset($mainWidgetTypes[$type]))
                                <div class="col-md-4 mb-2">
                                    <i class="bi bi-check-circle text-success me-1"></i>{{ $mainWidgetTypes[$type] }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">선택된 메인 위젯 타입이 없습니다.</p>
                @endif
            </div>
        </div>

        <!-- 커스텀 페이지 위젯 타입 -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>커스텀 페이지 위젯 타입</h5>
            </div>
            <div class="card-body">
                @php
                    $customPageWidgetTypes = \App\Models\CustomPageWidget::getAvailableTypes();
                    $planCustomPageWidgetTypes = $plan->custom_page_widget_types ?? [];
                @endphp
                @if(count($planCustomPageWidgetTypes) > 0)
                    <div class="row">
                        @foreach($planCustomPageWidgetTypes as $type)
                            @if(isset($customPageWidgetTypes[$type]))
                                <div class="col-md-4 mb-2">
                                    <i class="bi bi-check-circle text-success me-1"></i>{{ $customPageWidgetTypes[$type] }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">선택된 커스텀 페이지 위젯 타입이 없습니다.</p>
                @endif
            </div>
        </div>

        <!-- 제한 사항 -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-slash-circle me-2"></i>제한 사항</h5>
            </div>
            <div class="card-body">
                @php
                    $limits = $plan->limits ?? [];
                @endphp
                <dl class="row mb-0">
                    @if($plan->traffic_limit_mb)
                        <dt class="col-sm-4">트래픽 (MB/월)</dt>
                        <dd class="col-sm-8">
                            {{ number_format($plan->traffic_limit_mb) }}MB/월 
                            ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)
                        </dd>
                    @endif
                    @if(isset($limits['storage']))
                        <dt class="col-sm-4">스토리지 (MB)</dt>
                        <dd class="col-sm-8">
                            @if($limits['storage'] === null || $limits['storage'] === '-')
                                <span class="text-success">무제한</span>
                            @else
                                {{ number_format($limits['storage']) }}MB
                            @endif
                        </dd>
                    @endif
                    @if(count($limits) > 0)
                        @php
                            $limitLabels = [
                                'boards' => '게시판 수',
                                'widgets' => '위젯 수',
                                'custom_pages' => '커스텀 페이지 수',
                                'users' => '사용자 수',
                            ];
                        @endphp
                        @foreach($limitLabels as $key => $label)
                            @if(isset($limits[$key]))
                                <dt class="col-sm-4">{{ $label }}</dt>
                                <dd class="col-sm-8">
                                    @if($limits[$key] === null || $limits[$key] === '-')
                                        <span class="text-success">무제한</span>
                                    @else
                                        {{ is_numeric($limits[$key]) ? number_format($limits[$key]) : $limits[$key] }}
                                    @endif
                                </dd>
                            @endif
                        @endforeach
                    @endif
                </dl>
                @if(!$plan->traffic_limit_mb && count($limits) === 0)
                    <p class="text-muted mb-0">설정된 제한 사항이 없습니다.</p>
                @endif
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
                        <span>사용 중인 사이트</span>
                        <strong>{{ number_format($stats['sites']) }}</strong>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span>활성 사이트</span>
                        <strong>{{ number_format($stats['active_sites']) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

