@extends('layouts.master')

@section('title', '요금제 수정')
@section('page-title', '요금제 수정')
@section('page-subtitle', $plan->name . ' 정보 수정')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>요금제 정보 수정</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('master.plans.update', $plan->id) }}" id="planForm">
            @csrf
            @method('PUT')

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">플랜 이름 <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $plan->name) }}" 
                           required 
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="slug" class="form-label">슬러그 <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('slug') is-invalid @enderror" 
                           id="slug" 
                           name="slug" 
                           value="{{ old('slug', $plan->slug) }}"
                           required>
                    <small class="form-text text-muted">영문, 숫자, 하이픈만 사용 가능 (예: landing, brand, community)</small>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">설명</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" 
                          name="description" 
                          rows="3">{{ old('description', $plan->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label for="type" class="form-label">요금제 타입 <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" 
                            id="type" 
                            name="type" 
                            required>
                        @php
                            $currentType = old('type', $plan->type);
                            $isPlanType = in_array($currentType, ['landing', 'brand', 'community']);
                            $selectedType = $isPlanType ? 'plan' : ($currentType === 'server' ? 'server' : 'plan');
                        @endphp
                        <option value="plan" {{ $selectedType === 'plan' ? 'selected' : '' }}>플랜타입</option>
                        <option value="server" {{ $selectedType === 'server' ? 'selected' : '' }}>서버용량</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">플랜 이름이 플랜 종류를 나타냅니다 (랜딩페이지/브랜드/커뮤니티)</small>
                </div>
                {{-- 플랜 종류는 플랜 이름으로 구분하므로 제거 --}}
                <input type="hidden" name="plan_type" value="{{ $isPlanType ? $currentType : 'landing' }}">

                <div class="col-md-4 mb-3">
                    <label for="billing_type" class="form-label">결제 유형 <span class="text-danger">*</span></label>
                    <select class="form-select @error('billing_type') is-invalid @enderror" 
                            id="billing_type" 
                            name="billing_type" 
                            required>
                        <option value="free" {{ old('billing_type', $plan->billing_type) === 'free' ? 'selected' : '' }}>무료</option>
                        <option value="one_time" {{ old('billing_type', $plan->billing_type) === 'one_time' ? 'selected' : '' }}>1회 결제</option>
                        <option value="monthly" {{ old('billing_type', $plan->billing_type) === 'monthly' ? 'selected' : '' }}>월간 결제</option>
                    </select>
                    @error('billing_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="price" class="form-label">월간 가격</label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control @error('price') is-invalid @enderror" 
                               id="price" 
                               name="price" 
                               value="{{ old('price', $plan->price) }}" 
                               min="0" 
                               step="1000">
                        <span class="input-group-text">원</span>
                    </div>
                    <small class="form-text text-muted">월간 결제 시 사용</small>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3" id="one_time_price_container" style="display: {{ old('billing_type', $plan->billing_type) === 'one_time' ? 'block' : 'none' }};">
                    <label for="one_time_price" class="form-label">1회 결제 가격</label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control @error('one_time_price') is-invalid @enderror" 
                               id="one_time_price" 
                               name="one_time_price" 
                               value="{{ old('one_time_price', $plan->one_time_price) }}" 
                               min="0" 
                               step="1000">
                        <span class="input-group-text">원</span>
                    </div>
                    <small class="form-text text-muted">1회 결제 시 사용</small>
                    @error('one_time_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label for="sort_order" class="form-label">정렬 순서</label>
                    <input type="number" 
                           class="form-control @error('sort_order') is-invalid @enderror" 
                           id="sort_order" 
                           name="sort_order" 
                           value="{{ old('sort_order', $plan->sort_order) }}" 
                           min="0">
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">활성화</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_default" 
                               name="is_default" 
                               value="1"
                               {{ old('is_default', $plan->is_default) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">기본 플랜</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- 플랜타입 선택 시 표시되는 섹션들 -->
            <div id="plan_type_sections" style="display: {{ $selectedType === 'plan' ? 'block' : 'none' }};">
            <!-- 대 메뉴 기능 선택 -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="bi bi-list-check me-2"></i>대 메뉴 기능</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
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
                                    'chat_widget' => '채팅 위젯',
                                ];
                                $planMainFeatures = $plan->main_features ?? [];
                                $oldMainFeatures = old('main_features', $planMainFeatures);
                            @endphp
                            @foreach($mainFeatures as $key => $label)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input main-feature-checkbox" 
                                               type="checkbox" 
                                               id="main_feature_{{ $key }}" 
                                               name="main_features[]" 
                                               value="{{ $key }}"
                                               {{ in_array($key, $oldMainFeatures) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="main_feature_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllMainFeatures()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllMainFeatures()">전체 해제</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 게시판 타입 선택 -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="bi bi-grid me-2"></i>게시판 타입</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            "게시판 관리" 기능이 선택된 경우에만 게시판 타입을 선택할 수 있습니다.
                        </div>
                        <div class="row">
                            @php
                                $boardTypes = \App\Models\Board::getTypes();
                                $planBoardTypes = $plan->board_types ?? [];
                                $oldBoardTypes = old('board_types', $planBoardTypes);
                            @endphp
                            @foreach($boardTypes as $key => $label)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input board-type-checkbox" 
                                               type="checkbox" 
                                               id="board_type_{{ $key }}" 
                                               name="board_types[]" 
                                               value="{{ $key }}"
                                               {{ in_array($key, $oldBoardTypes) ? 'checked' : '' }}
                                               data-requires="boards">
                                        <label class="form-check-label" for="board_type_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllBoardTypes()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllBoardTypes()">전체 해제</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 회원가입 세부 기능 선택 -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="bi bi-person-plus me-2"></i>회원가입 세부 기능</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            "회원가입 설정" 기능이 선택된 경우에만 회원가입 세부 기능을 선택할 수 있습니다.
                        </div>
                        <div class="row">
                            @php
                                $registrationFeatures = [
                                    'signup_points' => '가입 포인트',
                                    'email_verification' => '이메일 인증',
                                    'phone_verification' => '전화번호 인증',
                                    'identity_verification' => '본인인증',
                                    'referrer' => '추천인 기능',
                                    'point_message' => '포인트 쪽지',
                                ];
                                $planRegFeatures = $plan->registration_features ?? [];
                                $oldRegFeatures = old('registration_features', $planRegFeatures);
                            @endphp
                            @foreach($registrationFeatures as $key => $label)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input registration-feature-checkbox" 
                                               type="checkbox" 
                                               id="reg_feature_{{ $key }}" 
                                               name="registration_features[]" 
                                               value="{{ $key }}"
                                               {{ in_array($key, $oldRegFeatures) ? 'checked' : '' }}
                                               data-requires="registration_settings">
                                        <label class="form-check-label" for="reg_feature_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllRegFeatures()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllRegFeatures()">전체 해제</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 사이드바 위젯 타입 선택 -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="bi bi-layout-sidebar me-2"></i>사이드바 위젯 타입</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            "사이드 위젯" 기능이 선택된 경우에만 사이드바 위젯 타입을 선택할 수 있습니다.
                        </div>
                        <div class="row">
                            @php
                                $sidebarWidgetTypes = \App\Models\SidebarWidget::getAvailableTypes();
                                // statistics, notice, user_activity 제외
                                unset($sidebarWidgetTypes['statistics'], $sidebarWidgetTypes['notice'], $sidebarWidgetTypes['user_activity']);
                                $planSidebarWidgetTypes = $plan->features['sidebar_widget_types'] ?? [];
                                $oldSidebarWidgetTypes = old('sidebar_widget_types', $planSidebarWidgetTypes);
                            @endphp
                            @foreach($sidebarWidgetTypes as $key => $label)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input sidebar-widget-type-checkbox" 
                                               type="checkbox" 
                                               id="sidebar_widget_type_{{ $key }}" 
                                               name="sidebar_widget_types[]" 
                                               value="{{ $key }}"
                                               {{ in_array($key, $oldSidebarWidgetTypes) ? 'checked' : '' }}
                                               data-requires="sidebar_widgets">
                                        <label class="form-check-label" for="sidebar_widget_type_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllSidebarWidgetTypes()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllSidebarWidgetTypes()">전체 해제</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 메인 위젯 타입 선택 -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="bi bi-grid-3x3-gap me-2"></i>메인 위젯 타입</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            "메인 위젯" 기능이 선택된 경우에만 메인 위젯 타입을 선택할 수 있습니다.
                        </div>
                        <div class="row">
                            @php
                                $mainWidgetTypes = \App\Models\MainWidget::getAvailableTypes();
                                // statistics, notice, user_activity 제외
                                unset($mainWidgetTypes['statistics'], $mainWidgetTypes['notice'], $mainWidgetTypes['user_activity']);
                                $planMainWidgetTypes = $plan->features['main_widget_types'] ?? [];
                                $oldMainWidgetTypes = old('main_widget_types', $planMainWidgetTypes);
                            @endphp
                            @foreach($mainWidgetTypes as $key => $label)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input main-widget-type-checkbox" 
                                               type="checkbox" 
                                               id="main_widget_type_{{ $key }}" 
                                               name="main_widget_types[]" 
                                               value="{{ $key }}"
                                               {{ in_array($key, $oldMainWidgetTypes) ? 'checked' : '' }}
                                               data-requires="main_widgets">
                                        <label class="form-check-label" for="main_widget_type_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllMainWidgetTypes()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllMainWidgetTypes()">전체 해제</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 커스텀 페이지 위젯 타입 선택 -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="bi bi-file-earmark-text me-2"></i>커스텀 페이지 위젯 타입</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            "커스텀 페이지" 기능이 선택된 경우에만 커스텀 페이지 위젯 타입을 선택할 수 있습니다.
                        </div>
                        <div class="row">
                            @php
                                $customPageWidgetTypes = \App\Models\CustomPageWidget::getAvailableTypes();
                                // statistics, notice, user_activity 제외
                                unset($customPageWidgetTypes['statistics'], $customPageWidgetTypes['notice'], $customPageWidgetTypes['user_activity']);
                                $planCustomPageWidgetTypes = $plan->features['custom_page_widget_types'] ?? [];
                                $oldCustomPageWidgetTypes = old('custom_page_widget_types', $planCustomPageWidgetTypes);
                            @endphp
                            @foreach($customPageWidgetTypes as $key => $label)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input custom-page-widget-type-checkbox" 
                                               type="checkbox" 
                                               id="custom_page_widget_type_{{ $key }}" 
                                               name="custom_page_widget_types[]" 
                                               value="{{ $key }}"
                                               {{ in_array($key, $oldCustomPageWidgetTypes) ? 'checked' : '' }}
                                               data-requires="custom_pages">
                                        <label class="form-check-label" for="custom_page_widget_type_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllCustomPageWidgetTypes()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllCustomPageWidgetTypes()">전체 해제</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 제한 사항 -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="bi bi-slash-circle me-2"></i>제한 사항</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            @php
                                $planLimits = $plan->limits ?? [];
                            @endphp
                            <div class="col-md-6 mb-3">
                                <label for="limit_boards" class="form-label">게시판 수</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="limit_boards" 
                                       name="limits[boards]" 
                                       value="{{ old('limits.boards', $planLimits['boards'] ?? '') }}"
                                       min="0"
                                       placeholder="비워두면 무제한">
                                <small class="text-muted">0 또는 비워두면 무제한</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="limit_widgets" class="form-label">위젯 수</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="limit_widgets" 
                                       name="limits[widgets]" 
                                       value="{{ old('limits.widgets', $planLimits['widgets'] ?? '') }}"
                                       min="0"
                                       placeholder="비워두면 무제한">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="limit_custom_pages" class="form-label">커스텀 페이지 수</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="limit_custom_pages" 
                                       name="limits[custom_pages]" 
                                       value="{{ old('limits.custom_pages', $planLimits['custom_pages'] ?? '') }}"
                                       placeholder="숫자 입력 또는 '-' 입력 시 무제한">
                                <small class="text-muted">숫자 입력 또는 '-' 입력 시 무제한</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="limit_users" class="form-label">사용자 수</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="limit_users" 
                                       name="limits[users]" 
                                       value="{{ old('limits.users', $planLimits['users'] ?? '') }}"
                                       placeholder="숫자 입력 또는 '-' 입력 시 무제한">
                                <small class="text-muted">숫자 입력 또는 '-' 입력 시 무제한</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="limit_storage" class="form-label">스토리지 (MB)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="limit_storage" 
                                       name="limits[storage]" 
                                       value="{{ old('limits.storage', $planLimits['storage'] ?? '') }}"
                                       min="0"
                                       placeholder="비워두면 무제한">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="limit_traffic" class="form-label">트래픽 (MB/월)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="limit_traffic" 
                                       name="limits[traffic]" 
                                       value="{{ old('limits.traffic', $planLimits['traffic'] ?? ($plan->traffic_limit_mb ?? '')) }}"
                                       min="0"
                                       placeholder="비워두면 무제한">
                                <small class="text-muted">월간 트래픽 제한 (MB 단위)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <!-- 서버용량 선택 시 표시되는 섹션 -->
            <div id="server_type_sections" style="display: {{ $selectedType === 'server' ? 'block' : 'none' }};">
                <div class="mb-4">
                    <h5 class="mb-3"><i class="bi bi-server me-2"></i>서버 용량 설정</h5>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="server_limit_storage" class="form-label">스토리지 (MB) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="server_limit_storage" 
                                           name="limits[storage]" 
                                           value="{{ old('limits.storage', $planLimits['storage'] ?? '') }}"
                                           min="0"
                                           {{ $selectedType === 'server' ? 'required' : '' }}>
                                    <small class="text-muted">서버 용량 플랜의 스토리지 용량을 설정합니다.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="server_limit_traffic" class="form-label">트래픽 (MB/월) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="server_limit_traffic" 
                                           name="limits[traffic]" 
                                           value="{{ old('limits.traffic', $planLimits['traffic'] ?? ($plan->traffic_limit_mb ?? '')) }}"
                                           min="0"
                                           {{ $selectedType === 'server' ? 'required' : '' }}>
                                    <small class="text-muted">서버 용량 플랜의 월간 트래픽 용량을 설정합니다.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('master.plans.index') }}" class="btn btn-secondary me-2">취소</a>
                <button type="submit" class="btn btn-primary">수정</button>
            </div>
        </form>
    </div>
</div>

<script>
// 상위 플랜의 기능 데이터
const parentPlansFeatures = @json($parentPlansFeatures ?? []);
const currentPlanType = '{{ $plan->type }}';

// 상위 플랜의 기능을 자동으로 체크하는 함수
function applyParentPlanFeatures() {
    if (currentPlanType === 'brand' || currentPlanType === 'community') {
        // 기본 플랜(랜딩페이지) 기능 적용
        if (parentPlansFeatures.landing) {
            applyFeatures(parentPlansFeatures.landing);
        }
    }
    
    if (currentPlanType === 'community') {
        // 브랜드 플랜 기능 적용
        if (parentPlansFeatures.brand) {
            applyFeatures(parentPlansFeatures.brand);
        }
    }
}

// 상위 플랜의 기능을 강제로 체크하는 함수 (기존 체크 상태 무시)
function forceApplyParentPlanFeatures() {
    // 먼저 모든 대 메뉴 기능을 체크하여 의존성 체크박스들이 활성화되도록 함
    if (currentPlanType === 'brand' || currentPlanType === 'community') {
        // 기본 플랜(랜딩페이지) 기능 강제 적용
        if (parentPlansFeatures.landing) {
            // 대 메뉴 기능 먼저 적용
            if (parentPlansFeatures.landing.main_features) {
                parentPlansFeatures.landing.main_features.forEach(feature => {
                    const checkbox = document.getElementById('main_feature_' + feature);
                    if (checkbox) checkbox.checked = true;
                });
            }
        }
    }
    
    if (currentPlanType === 'community') {
        // 브랜드 플랜의 대 메뉴 기능도 먼저 적용
        if (parentPlansFeatures.brand && parentPlansFeatures.brand.main_features) {
            parentPlansFeatures.brand.main_features.forEach(feature => {
                const checkbox = document.getElementById('main_feature_' + feature);
                if (checkbox) checkbox.checked = true;
            });
        }
    }
    
    // 의존성 체크박스 업데이트 (위젯 타입들이 활성화되도록)
    updateDependentCheckboxes();
    
    // 이제 모든 기능을 적용
    if (currentPlanType === 'brand' || currentPlanType === 'community') {
        // 기본 플랜(랜딩페이지) 기능 강제 적용
        if (parentPlansFeatures.landing) {
            forceApplyFeatures(parentPlansFeatures.landing);
        }
    }
    
    if (currentPlanType === 'community') {
        // 브랜드 플랜 기능 강제 적용
        if (parentPlansFeatures.brand) {
            forceApplyFeatures(parentPlansFeatures.brand);
        }
    }
    
    // 최종 의존성 체크박스 업데이트
    updateDependentCheckboxes();
}

// 기능을 강제로 체크박스에 적용하는 함수 (기존 체크 상태 무시)
function forceApplyFeatures(features) {
    // 대 메뉴 기능
    if (features.main_features) {
        features.main_features.forEach(feature => {
            const checkbox = document.getElementById('main_feature_' + feature);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    // 게시판 타입
    if (features.board_types) {
        features.board_types.forEach(type => {
            const checkbox = document.getElementById('board_type_' + type);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
    
    // 회원가입 세부 기능
    if (features.registration_features) {
        features.registration_features.forEach(feature => {
            const checkbox = document.getElementById('reg_feature_' + feature);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
    
    // 사이드바 위젯 타입
    if (features.sidebar_widget_types) {
        features.sidebar_widget_types.forEach(type => {
            const checkbox = document.getElementById('sidebar_widget_type_' + type);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
    
    // 메인 위젯 타입
    if (features.main_widget_types) {
        features.main_widget_types.forEach(type => {
            const checkbox = document.getElementById('main_widget_type_' + type);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
    
    // 커스텀 페이지 위젯 타입
    if (features.custom_page_widget_types) {
        features.custom_page_widget_types.forEach(type => {
            const checkbox = document.getElementById('custom_page_widget_type_' + type);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
}

// 기능을 체크박스에 적용하는 함수
function applyFeatures(features) {
    // 대 메뉴 기능
    if (features.main_features) {
        features.main_features.forEach(feature => {
            const checkbox = document.getElementById('main_feature_' + feature);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    // 게시판 타입
    if (features.board_types) {
        features.board_types.forEach(type => {
            const checkbox = document.getElementById('board_type_' + type);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
    
    // 회원가입 세부 기능
    if (features.registration_features) {
        features.registration_features.forEach(feature => {
            const checkbox = document.getElementById('reg_feature_' + feature);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
    
    // 사이드바 위젯 타입
    if (features.sidebar_widget_types) {
        features.sidebar_widget_types.forEach(type => {
            const checkbox = document.getElementById('sidebar_widget_type_' + type);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
    
    // 메인 위젯 타입
    if (features.main_widget_types) {
        features.main_widget_types.forEach(type => {
            const checkbox = document.getElementById('main_widget_type_' + type);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
    
    // 커스텀 페이지 위젯 타입
    if (features.custom_page_widget_types) {
        features.custom_page_widget_types.forEach(type => {
            const checkbox = document.getElementById('custom_page_widget_type_' + type);
            if (checkbox && !checkbox.disabled) checkbox.checked = true;
        });
    }
    
    // 의존성 체크박스 업데이트
    updateDependentCheckboxes();
}

// 전체 선택/해제 함수들
function selectAllMainFeatures() {
    document.querySelectorAll('.main-feature-checkbox').forEach(cb => cb.checked = true);
    updateDependentCheckboxes();
}

function deselectAllMainFeatures() {
    document.querySelectorAll('.main-feature-checkbox').forEach(cb => cb.checked = false);
    updateDependentCheckboxes();
}

function selectAllBoardTypes() {
    document.querySelectorAll('.board-type-checkbox').forEach(cb => {
        if (!cb.disabled) cb.checked = true;
    });
}

function deselectAllBoardTypes() {
    document.querySelectorAll('.board-type-checkbox').forEach(cb => cb.checked = false);
}

function selectAllRegFeatures() {
    document.querySelectorAll('.registration-feature-checkbox').forEach(cb => {
        if (!cb.disabled) cb.checked = true;
    });
}

function deselectAllRegFeatures() {
    document.querySelectorAll('.registration-feature-checkbox').forEach(cb => cb.checked = false);
}

// 의존성 체크박스 업데이트
function updateDependentCheckboxes() {
    const boardsChecked = document.getElementById('main_feature_boards')?.checked;
    const regSettingsChecked = document.getElementById('main_feature_registration_settings')?.checked;
    const sidebarWidgetsChecked = document.getElementById('main_feature_sidebar_widgets')?.checked;
    const mainWidgetsChecked = document.getElementById('main_feature_main_widgets')?.checked;
    const customPagesChecked = document.getElementById('main_feature_custom_pages')?.checked;

    // 게시판 타입 체크박스 활성화/비활성화
    document.querySelectorAll('.board-type-checkbox').forEach(cb => {
        cb.disabled = !boardsChecked;
        if (!boardsChecked) cb.checked = false;
    });

    // 회원가입 세부 기능 체크박스 활성화/비활성화
    document.querySelectorAll('.registration-feature-checkbox').forEach(cb => {
        cb.disabled = !regSettingsChecked;
        if (!regSettingsChecked) cb.checked = false;
    });
    
    // 사이드바 위젯 타입 체크박스 활성화/비활성화
    document.querySelectorAll('.sidebar-widget-type-checkbox').forEach(cb => {
        cb.disabled = !sidebarWidgetsChecked;
        if (!sidebarWidgetsChecked) cb.checked = false;
    });
    
    // 메인 위젯 타입 체크박스 활성화/비활성화
    document.querySelectorAll('.main-widget-type-checkbox').forEach(cb => {
        cb.disabled = !mainWidgetsChecked;
        if (!mainWidgetsChecked) cb.checked = false;
    });
    
    // 커스텀 페이지 위젯 타입 체크박스 활성화/비활성화
    document.querySelectorAll('.custom-page-widget-type-checkbox').forEach(cb => {
        cb.disabled = !customPagesChecked;
        if (!customPagesChecked) cb.checked = false;
    });
}

// 대 메뉴 기능 체크박스 변경 시
document.querySelectorAll('.main-feature-checkbox').forEach(cb => {
    cb.addEventListener('change', updateDependentCheckboxes);
});

// 요금제 타입 변경 시 섹션 표시/숨김
const planTypeSelect = document.getElementById('type');
const planTypeSections = document.getElementById('plan_type_sections');
const serverTypeSections = document.getElementById('server_type_sections');

if (planTypeSelect) {
    planTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        if (selectedType === 'plan') {
            planTypeSections.style.display = 'block';
            serverTypeSections.style.display = 'none';
            document.getElementById('server_limit_storage').required = false;
            document.getElementById('server_limit_traffic').required = false;
            
            // 기존 플랜의 type에 따라 상위 플랜 기능 적용
            const currentPlanType = '{{ $plan->type }}';
            if (currentPlanType === 'brand' || currentPlanType === 'community') {
                applyParentPlanFeatures(currentPlanType);
            }
        } else if (selectedType === 'server') {
            planTypeSections.style.display = 'none';
            serverTypeSections.style.display = 'block';
            document.getElementById('server_limit_storage').required = true;
            document.getElementById('server_limit_traffic').required = true;
        }
    });
}

// 폼 제출 시 features 구조화 및 type 설정
document.getElementById('planForm').addEventListener('submit', function(e) {
    const planType = document.getElementById('type').value;
    
    if (planType === 'plan') {
        // 플랜 종류는 기존 플랜의 type을 유지하므로 hidden 필드로 전송
        // plan_type은 hidden 필드로 이미 설정되어 있음
        
        const mainFeatures = Array.from(document.querySelectorAll('.main-feature-checkbox:checked')).map(cb => cb.value);
        const boardTypes = Array.from(document.querySelectorAll('.board-type-checkbox:checked')).map(cb => cb.value);
        const regFeatures = Array.from(document.querySelectorAll('.registration-feature-checkbox:checked')).map(cb => cb.value);
        const sidebarWidgetTypes = Array.from(document.querySelectorAll('.sidebar-widget-type-checkbox:checked')).map(cb => cb.value);
        const mainWidgetTypes = Array.from(document.querySelectorAll('.main-widget-type-checkbox:checked')).map(cb => cb.value);
        const customPageWidgetTypes = Array.from(document.querySelectorAll('.custom-page-widget-type-checkbox:checked')).map(cb => cb.value);

        const featuresInput = document.createElement('input');
        featuresInput.type = 'hidden';
        featuresInput.name = 'features';
        featuresInput.value = JSON.stringify({
            main_features: mainFeatures,
            board_types: boardTypes,
            registration_features: regFeatures,
            sidebar_widget_types: sidebarWidgetTypes,
            main_widget_types: mainWidgetTypes,
            custom_page_widget_types: customPageWidgetTypes
        });
        this.appendChild(featuresInput);
        
        const trafficLimit = document.getElementById('limit_traffic').value;
        if (trafficLimit) {
            const trafficLimitInput = document.createElement('input');
            trafficLimitInput.type = 'hidden';
            trafficLimitInput.name = 'traffic_limit_mb';
            trafficLimitInput.value = trafficLimit;
            this.appendChild(trafficLimitInput);
        }
    } else if (planType === 'server') {
        const trafficLimit = document.getElementById('server_limit_traffic').value;
        if (trafficLimit) {
            const trafficLimitInput = document.createElement('input');
            trafficLimitInput.type = 'hidden';
            trafficLimitInput.name = 'traffic_limit_mb';
            trafficLimitInput.value = trafficLimit;
            this.appendChild(trafficLimitInput);
        }
    }
});

// 초기 로드 시 상위 플랜 기능 강제 적용 및 의존성 체크
// billing_type 변경 시 one_time_price 필드 표시/숨김
document.addEventListener('DOMContentLoaded', function() {
    const billingTypeSelect = document.getElementById('billing_type');
    const oneTimePriceContainer = document.getElementById('one_time_price_container');
    
    if (billingTypeSelect && oneTimePriceContainer) {
        billingTypeSelect.addEventListener('change', function() {
            if (this.value === 'one_time') {
                oneTimePriceContainer.style.display = 'block';
            } else {
                oneTimePriceContainer.style.display = 'none';
            }
        });
    }
    
    updateDependentCheckboxes();
    
    // 초기 요금제 타입에 따라 섹션 표시
    const initialType = planTypeSelect?.value;
    if (initialType === 'server') {
        planTypeSections.style.display = 'none';
        serverTypeSections.style.display = 'block';
        document.getElementById('server_limit_storage').required = true;
        document.getElementById('server_limit_traffic').required = true;
    } else {
        planTypeSections.style.display = 'block';
        serverTypeSections.style.display = 'none';
        document.getElementById('server_limit_storage').required = false;
        document.getElementById('server_limit_traffic').required = false;
        
        // 상위 플랜의 기능을 강제로 적용 (기존 체크 상태 무시)
        forceApplyParentPlanFeatures();
    }
});
</script>
@endsection

