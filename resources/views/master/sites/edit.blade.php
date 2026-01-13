@extends('layouts.master')

@section('title', '사이트 수정')
@section('page-title', '사이트 수정')
@section('page-subtitle', $site->name . ' 정보 수정')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>사이트 정보 수정</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('master.sites.update', $site->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">사이트 이름 <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $site->name) }}" 
                           required 
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="slug" class="form-label">슬러그</label>
                    <input type="text" 
                           class="form-control @error('slug') is-invalid @enderror" 
                           id="slug" 
                           name="slug" 
                           value="{{ old('slug', $site->slug) }}">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="domain" class="form-label">도메인</label>
                    <input type="text" 
                           class="form-control @error('domain') is-invalid @enderror" 
                           id="domain" 
                           name="domain" 
                           value="{{ old('domain', $site->domain) }}"
                           placeholder="example.com">
                    @error('domain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="plan" class="form-label">요금제 <span class="text-danger">*</span></label>
                    <select class="form-select @error('plan') is-invalid @enderror" 
                            id="plan" 
                            name="plan" 
                            required>
                        <option value="">선택하세요</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->slug }}" 
                                    {{ old('plan', $site->plan) === $plan->slug ? 'selected' : '' }}
                                    data-price="{{ $plan->price }}"
                                    data-description="{{ $plan->description }}"
                                    data-type="{{ $plan->type }}"
                                    data-billing="{{ $plan->billing_type }}">
                                {{ $plan->name }}
                                @if($plan->billing_type === 'free')
                                    (무료)
                                @elseif($plan->price > 0)
                                    ({{ number_format($plan->price) }}원/월)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted" id="plan-description"></small>
                    @error('plan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">상태 <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" 
                            name="status" 
                            required>
                        <option value="active" {{ old('status', $site->status) === 'active' ? 'selected' : '' }}>활성</option>
                        <option value="suspended" {{ old('status', $site->status) === 'suspended' ? 'selected' : '' }}>정지</option>
                        <option value="deleted" {{ old('status', $site->status) === 'deleted' ? 'selected' : '' }}>삭제됨</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if(isset($serverPlans) && $serverPlans->count() > 0)
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="server_plan" class="form-label">서버 용량</label>
                    <select class="form-select @error('server_plan') is-invalid @enderror" 
                            id="server_plan" 
                            name="server_plan">
                        <option value="">기본 (플랜에 포함된 용량 사용)</option>
                        @foreach($serverPlans as $serverPlan)
                            @php
                                $currentServerPlan = $site->traffic_limit_mb == $serverPlan->traffic_limit_mb;
                            @endphp
                            <option value="{{ $serverPlan->slug }}" 
                                    {{ $currentServerPlan ? 'selected' : '' }}
                                    data-traffic="{{ $serverPlan->traffic_limit_mb }}">
                                {{ $serverPlan->name }}
                                @if($serverPlan->price > 0)
                                    ({{ number_format($serverPlan->price) }}원/월)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">선택한 서버 용량 플랜의 트래픽 제한이 적용됩니다.</small>
                    @error('server_plan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">현재 트래픽 사용량</label>
                    <div class="progress" style="height: 25px;">
                        @php
                            $trafficUsed = $site->traffic_used_mb ?? 0;
                            $trafficLimit = $site->getTotalTrafficLimit() ?? 0;
                            $trafficPercent = $trafficLimit > 0 ? min(100, ($trafficUsed / $trafficLimit) * 100) : 0;
                        @endphp
                        <div class="progress-bar {{ $trafficPercent > 80 ? 'bg-danger' : ($trafficPercent > 50 ? 'bg-warning' : 'bg-success') }}" 
                             role="progressbar" 
                             style="width: {{ $trafficPercent }}%">
                            {{ number_format($trafficPercent, 1) }}%
                        </div>
                    </div>
                    <small class="text-muted">
                        {{ number_format($trafficUsed) }} MB / {{ $trafficLimit ? number_format($trafficLimit) . ' MB' : '무제한' }}
                    </small>
                </div>
            </div>
            @endif

            @include('master.sites.edit-features-section')

            {{-- 관리자 계정 설정 섹션 --}}
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-person-gear me-2"></i>관리자 계정 설정</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="login_type" class="form-label">로그인 타입</label>
                            <select class="form-select @error('login_type') is-invalid @enderror" 
                                    id="login_type" 
                                    name="login_type">
                                <option value="email" {{ old('login_type', $site->login_type ?? 'email') === 'email' ? 'selected' : '' }}>이메일</option>
                                <option value="username" {{ old('login_type', $site->login_type ?? 'email') === 'username' ? 'selected' : '' }}>아이디</option>
                            </select>
                            <small class="form-text text-muted">사이트 로그인 시 사용할 계정 타입을 선택합니다.</small>
                            @error('login_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>테스트 관리자 계정</h6>
                    </div>
                    
                    @php
                        $testAdmin = $site->test_admin ?? [];
                        $testAdminId = $testAdmin['id'] ?? $testAdmin['username'] ?? '';
                        $hasTestAdmin = !empty($testAdmin) && (!empty($testAdminId) || !empty($testAdmin['password'] ?? ''));
                    @endphp
                    
                    {{-- 등록된 테스트 관리자가 있는 경우 --}}
                    @if($hasTestAdmin)
                        <div id="testAdminDisplay">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">관리자 ID</small>
                                            <strong>{{ $testAdminId ?: '-' }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">비밀번호</small>
                                            <span id="passwordDisplay">••••••••</span>
                                            <button type="button" class="btn btn-sm btn-link p-0 ms-2" onclick="showTestAdminPassword()">
                                                <i class="bi bi-eye" id="displayPasswordIcon"></i>
                                            </button>
                                            <span id="passwordActual" class="d-none">{{ $testAdmin['password'] ?? '' }}</span>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showTestAdminEditForm()">
                                                <i class="bi bi-pencil me-1"></i>수정
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearTestAdmin()">
                                                <i class="bi bi-trash me-1"></i>삭제
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- 숨겨진 입력 필드 (폼 제출용) --}}
                            <input type="hidden" name="test_admin[id]" id="hidden_test_admin_id" value="{{ $testAdminId }}">
                            <input type="hidden" name="test_admin[password]" id="hidden_test_admin_password" value="{{ $testAdmin['password'] ?? '' }}">
                        </div>
                        
                        {{-- 수정 폼 (숨김) --}}
                        <div id="testAdminEditForm" class="d-none">
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="test_admin_id" class="form-label">관리자 ID</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="test_admin_id" 
                                           value="{{ $testAdminId }}"
                                           placeholder="admin">
                                </div>

                                <div class="col-md-5 mb-3">
                                    <label for="test_admin_password" class="form-label">관리자 비밀번호</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="test_admin_password" 
                                               value="{{ $testAdmin['password'] ?? '' }}"
                                               placeholder="••••••••">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('test_admin_password')">
                                            <i class="bi bi-eye" id="test_admin_password_icon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary btn-sm me-2" onclick="saveTestAdminEdit()">
                                        <i class="bi bi-check me-1"></i>저장
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelTestAdminEdit()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- 등록된 테스트 관리자가 없는 경우 --}}
                        <div id="testAdminAddButton">
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="showTestAdminAddForm()">
                                <i class="bi bi-plus-circle me-1"></i>테스트 관리자 추가
                            </button>
                        </div>
                        
                        {{-- 추가 폼 (숨김) --}}
                        <div id="testAdminAddForm" class="d-none">
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="test_admin_id" class="form-label">관리자 ID</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="test_admin_id" 
                                           name="test_admin[id]"
                                           placeholder="admin">
                                </div>

                                <div class="col-md-5 mb-3">
                                    <label for="test_admin_password" class="form-label">관리자 비밀번호</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="test_admin_password" 
                                               name="test_admin[password]"
                                               placeholder="••••••••">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('test_admin_password')">
                                            <i class="bi bi-eye" id="test_admin_password_icon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="hideTestAdminAddForm()">
                                        <i class="bi bi-x me-1"></i>취소
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        {{-- 빈 값 전송용 숨겨진 필드 --}}
                        <input type="hidden" name="test_admin[id]" id="hidden_test_admin_id" value="">
                        <input type="hidden" name="test_admin[password]" id="hidden_test_admin_password" value="">
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('master.sites.show', $site->id) }}" class="btn btn-secondary me-2">
                    <i class="bi bi-x-circle me-1"></i>취소
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// 폼 제출 시 모든 체크박스 값을 명시적으로 전송
(function() {
    const form = document.querySelector('form[method="POST"]');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        console.log('Form submit - processing checkboxes');
        
        // 모든 체크박스 그룹에 대해 처리
        const checkboxGroups = [
            'custom_main_features',
            'custom_board_types',
            'custom_registration_features',
            'custom_sidebar_widget_types',
            'custom_main_widget_types',
            'custom_custom_page_widget_types'
        ];
        
        checkboxGroups.forEach(function(groupName) {
            console.log('Processing group:', groupName);
            
            // 해당 그룹의 모든 체크박스 찾기
            const checkboxes = form.querySelectorAll('input[name="' + groupName + '[]"][type="checkbox"]');
            const checkedValues = [];
            
            // 체크된 값 수집
            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    checkedValues.push(checkbox.value);
                    console.log('Checked:', checkbox.value);
                }
                // 체크박스를 disabled로 만들어서 전송되지 않도록 함
                checkbox.disabled = true;
            });
            
            // 기존 hidden input 제거 (있다면)
            const existingHidden = form.querySelectorAll('input[name="' + groupName + '[]"][type="hidden"]');
            existingHidden.forEach(function(input) {
                input.remove();
            });
            
            // 체크된 값이 있으면 각각을 hidden input으로 추가
            checkedValues.forEach(function(value) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = groupName + '[]';
                hiddenInput.value = value;
                form.appendChild(hiddenInput);
                console.log('Added hidden input:', groupName + '[] =', value);
            });
            
            // 체크된 값이 없어도 빈 배열임을 명시하기 위해 빈 값의 hidden input 추가
            if (checkedValues.length === 0) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = groupName + '[]';
                hiddenInput.value = '';
                form.appendChild(hiddenInput);
                console.log('Added empty hidden input for:', groupName);
            }
        });
        
        console.log('Form data before submit:', new FormData(form));
    });
})();
</script>

<script>
// 플랜 데이터를 JavaScript에서 사용할 수 있도록 전달
const plansData = @json($plansData);

document.addEventListener('DOMContentLoaded', function() {
    const planSelect = document.getElementById('plan');
    const planDescription = document.getElementById('plan-description');
    
    if (planSelect && planDescription) {
        planSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const description = selectedOption.getAttribute('data-description');
            
            if (description) {
                planDescription.textContent = description;
            } else {
                planDescription.textContent = '';
            }
            
            // 플랜 변경 시 해당 플랜의 features 자동 체크
            const selectedPlanSlug = this.value;
            if (selectedPlanSlug && plansData[selectedPlanSlug]) {
                applyPlanFeatures(plansData[selectedPlanSlug]);
                updateBadgesForPlan(selectedPlanSlug);
            } else {
                // 플랜이 선택되지 않았으면 모든 배지를 "추가 기능"으로 설정
                document.querySelectorAll('input[type="checkbox"][name^="custom_"]').forEach(cb => {
                    updateBadge(cb, false);
                });
            }
        });
        
        // 초기 로드 시 설명 표시만 하고 features는 서버에서 설정한 상태 유지
        if (planSelect.value) {
            const selectedOption = planSelect.options[planSelect.selectedIndex];
            const description = selectedOption.getAttribute('data-description');
            if (description) {
                planDescription.textContent = description;
            } else {
                planDescription.textContent = '';
            }
            // 배지만 업데이트 (체크박스 상태는 변경하지 않음)
            updateBadgesForPlan(planSelect.value);
        }
    }
});

// 플랜의 features를 체크박스에 자동 적용
function applyPlanFeatures(planData) {
    // 서버에서 설정한 체크박스 상태를 유지하기 위해, 먼저 현재 체크된 상태를 저장
    const currentChecked = {};
    document.querySelectorAll('input[type="checkbox"][name^="custom_"]').forEach(cb => {
        if (cb.checked) {
            const name = cb.name;
            if (!currentChecked[name]) {
                currentChecked[name] = [];
            }
            currentChecked[name].push(cb.value);
        }
    });
    
    // 먼저 모든 체크박스 해제
    document.querySelectorAll('input[type="checkbox"][name^="custom_"]').forEach(cb => cb.checked = false);
    
    const features = planData.features || {};
    
    // 대 메뉴 기능
    if (features.main_features) {
        features.main_features.forEach(feature => {
            const checkbox = document.getElementById('custom_main_feature_' + feature);
            if (checkbox) {
                checkbox.checked = true;
                updateBadge(checkbox, true);
            }
        });
    }
    
    // 게시판 타입
    if (features.board_types) {
        features.board_types.forEach(type => {
            const checkbox = document.getElementById('custom_board_type_' + type);
            if (checkbox) {
                checkbox.checked = true;
                updateBadge(checkbox, true);
            }
        });
    }
    
    // 회원가입 세부 기능
    if (features.registration_features) {
        features.registration_features.forEach(feature => {
            const checkbox = document.getElementById('custom_reg_feature_' + feature);
            if (checkbox) {
                checkbox.checked = true;
                updateBadge(checkbox, true);
            }
        });
    }
    
    // 사이드바 위젯 타입
    if (features.sidebar_widget_types) {
        features.sidebar_widget_types.forEach(type => {
            const checkbox = document.getElementById('custom_sidebar_widget_type_' + type);
            if (checkbox) {
                checkbox.checked = true;
                updateBadge(checkbox, true);
            }
        });
    }
    
    // 메인 위젯 타입
    if (features.main_widget_types) {
        features.main_widget_types.forEach(type => {
            const checkbox = document.getElementById('custom_main_widget_type_' + type);
            if (checkbox) {
                checkbox.checked = true;
                updateBadge(checkbox, true);
            }
        });
    }
    
    // 커스텀 페이지 위젯 타입
    if (features.custom_page_widget_types) {
        features.custom_page_widget_types.forEach(type => {
            const checkbox = document.getElementById('custom_custom_page_widget_type_' + type);
            if (checkbox) {
                checkbox.checked = true;
                updateBadge(checkbox, true);
            }
        });
    }
    
    // 제한 사항
    if (planData.limits) {
        Object.keys(planData.limits).forEach(key => {
            const input = document.getElementById('custom_limit_' + key);
            if (input) {
                const value = planData.limits[key];
                input.value = value === null || value === '' ? '' : value;
            }
        });
    }
}

// 배지 업데이트 함수
function updateBadge(checkbox, isInPlan) {
    const label = checkbox.closest('.form-check').querySelector('label');
    if (!label) return;
    
    // 기존 배지 제거
    const existingBadge = label.querySelector('.badge');
    if (existingBadge) {
        existingBadge.remove();
    }
    
    // 새 배지 추가
    if (isInPlan) {
        const badge = document.createElement('span');
        badge.className = 'badge bg-success ms-1';
        badge.style.fontSize = '0.7em';
        badge.textContent = '플랜 포함';
        label.appendChild(badge);
    } else {
        const badge = document.createElement('span');
        badge.className = 'badge bg-warning text-dark ms-1';
        badge.style.fontSize = '0.7em';
        badge.textContent = '추가 기능';
        label.appendChild(badge);
    }
}

// 모든 플랜 정보를 JavaScript에서 사용할 수 있도록 전달
const allPlansData = @json($plansData ?? []);

// 플랜 선택 시 배지 업데이트
function updateBadgesForPlan(planSlug) {
    if (!allPlansData[planSlug]) return;
    
    const planFeatures = allPlansData[planSlug].features || {};
    
    // 대 메뉴 기능 배지 업데이트
    document.querySelectorAll('input[name="custom_main_features[]"]').forEach(cb => {
        const featureKey = cb.value;
        const isInPlan = planFeatures.main_features && planFeatures.main_features.includes(featureKey);
        updateBadge(cb, isInPlan);
    });
    
    // 게시판 타입 배지 업데이트
    document.querySelectorAll('input[name="custom_board_types[]"]').forEach(cb => {
        const typeKey = cb.value;
        const isInPlan = planFeatures.board_types && planFeatures.board_types.includes(typeKey);
        updateBadge(cb, isInPlan);
    });
    
    // 회원가입 세부 기능 배지 업데이트
    document.querySelectorAll('input[name="custom_registration_features[]"]').forEach(cb => {
        const featureKey = cb.value;
        const isInPlan = planFeatures.registration_features && planFeatures.registration_features.includes(featureKey);
        updateBadge(cb, isInPlan);
    });
    
    // 사이드바 위젯 타입 배지 업데이트
    document.querySelectorAll('input[name="custom_sidebar_widget_types[]"]').forEach(cb => {
        const typeKey = cb.value;
        const isInPlan = planFeatures.sidebar_widget_types && planFeatures.sidebar_widget_types.includes(typeKey);
        updateBadge(cb, isInPlan);
    });
    
    // 메인 위젯 타입 배지 업데이트
    document.querySelectorAll('input[name="custom_main_widget_types[]"]').forEach(cb => {
        const typeKey = cb.value;
        const isInPlan = planFeatures.main_widget_types && planFeatures.main_widget_types.includes(typeKey);
        updateBadge(cb, isInPlan);
    });
    
    // 커스텀 페이지 위젯 타입 배지 업데이트
    document.querySelectorAll('input[name="custom_custom_page_widget_types[]"]').forEach(cb => {
        const typeKey = cb.value;
        const isInPlan = planFeatures.custom_page_widget_types && planFeatures.custom_page_widget_types.includes(typeKey);
        updateBadge(cb, isInPlan);
    });
}

// 기능 선택 함수들
function selectAllMainFeatures() {
    document.querySelectorAll('input[name="custom_main_features[]"]').forEach(cb => cb.checked = true);
}
function selectPlanMainFeatures() {
    document.querySelectorAll('input[name="custom_main_features[]"]').forEach(cb => {
        const label = cb.closest('.form-check').querySelector('label');
        const hasPlanBadge = label.querySelector('.badge.bg-success');
        cb.checked = hasPlanBadge !== null;
    });
}
function deselectAllMainFeatures() {
    document.querySelectorAll('input[name="custom_main_features[]"]').forEach(cb => cb.checked = false);
}

function selectAllBoardTypes() {
    document.querySelectorAll('input[name="custom_board_types[]"]').forEach(cb => cb.checked = true);
}
function selectPlanBoardTypes() {
    document.querySelectorAll('input[name="custom_board_types[]"]').forEach(cb => {
        const label = cb.closest('.form-check').querySelector('label');
        const hasPlanBadge = label.querySelector('.badge.bg-success');
        cb.checked = hasPlanBadge !== null;
    });
}
function deselectAllBoardTypes() {
    document.querySelectorAll('input[name="custom_board_types[]"]').forEach(cb => cb.checked = false);
}

function selectAllRegFeatures() {
    document.querySelectorAll('input[name="custom_registration_features[]"]').forEach(cb => cb.checked = true);
}
function selectPlanRegFeatures() {
    document.querySelectorAll('input[name="custom_registration_features[]"]').forEach(cb => {
        const label = cb.closest('.form-check').querySelector('label');
        const hasPlanBadge = label.querySelector('.badge.bg-success');
        cb.checked = hasPlanBadge !== null;
    });
}
function deselectAllRegFeatures() {
    document.querySelectorAll('input[name="custom_registration_features[]"]').forEach(cb => cb.checked = false);
}

function selectAllSidebarWidgetTypes() {
    document.querySelectorAll('input[name="custom_sidebar_widget_types[]"]').forEach(cb => cb.checked = true);
}
function selectPlanSidebarWidgetTypes() {
    document.querySelectorAll('input[name="custom_sidebar_widget_types[]"]').forEach(cb => {
        const label = cb.closest('.form-check').querySelector('label');
        const hasPlanBadge = label.querySelector('.badge.bg-success');
        cb.checked = hasPlanBadge !== null;
    });
}
function deselectAllSidebarWidgetTypes() {
    document.querySelectorAll('input[name="custom_sidebar_widget_types[]"]').forEach(cb => cb.checked = false);
}

function selectAllMainWidgetTypes() {
    document.querySelectorAll('input[name="custom_main_widget_types[]"]').forEach(cb => cb.checked = true);
}
function selectPlanMainWidgetTypes() {
    document.querySelectorAll('input[name="custom_main_widget_types[]"]').forEach(cb => {
        const label = cb.closest('.form-check').querySelector('label');
        const hasPlanBadge = label.querySelector('.badge.bg-success');
        cb.checked = hasPlanBadge !== null;
    });
}
function deselectAllMainWidgetTypes() {
    document.querySelectorAll('input[name="custom_main_widget_types[]"]').forEach(cb => cb.checked = false);
}

function selectAllCustomPageWidgetTypes() {
    document.querySelectorAll('input[name="custom_custom_page_widget_types[]"]').forEach(cb => cb.checked = true);
}
function selectPlanCustomPageWidgetTypes() {
    document.querySelectorAll('input[name="custom_custom_page_widget_types[]"]').forEach(cb => {
        const label = cb.closest('.form-check').querySelector('label');
        const hasPlanBadge = label.querySelector('.badge.bg-success');
        cb.checked = hasPlanBadge !== null;
    });
}
function deselectAllCustomPageWidgetTypes() {
    document.querySelectorAll('input[name="custom_custom_page_widget_types[]"]').forEach(cb => cb.checked = false);
}

// 비밀번호 표시/숨기기 토글
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// 테스트 관리자 비밀번호 표시
function showTestAdminPassword() {
    const display = document.getElementById('passwordDisplay');
    const actual = document.getElementById('passwordActual');
    const icon = document.getElementById('displayPasswordIcon');
    
    if (display.textContent === '••••••••') {
        display.textContent = actual.textContent;
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        display.textContent = '••••••••';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// 테스트 관리자 수정 폼 표시
function showTestAdminEditForm() {
    document.getElementById('testAdminDisplay').classList.add('d-none');
    document.getElementById('testAdminEditForm').classList.remove('d-none');
}

// 테스트 관리자 수정 취소
function cancelTestAdminEdit() {
    document.getElementById('testAdminEditForm').classList.add('d-none');
    document.getElementById('testAdminDisplay').classList.remove('d-none');
}

// 테스트 관리자 수정 저장
function saveTestAdminEdit() {
    const id = document.getElementById('test_admin_id').value;
    const password = document.getElementById('test_admin_password').value;
    
    // hidden 필드 업데이트
    document.getElementById('hidden_test_admin_id').value = id;
    document.getElementById('hidden_test_admin_password').value = password;
    
    // 디스플레이 업데이트
    const displayCard = document.querySelector('#testAdminDisplay .card-body');
    if (displayCard) {
        displayCard.querySelector('strong').textContent = id || '-';
        document.getElementById('passwordActual').textContent = password;
        document.getElementById('passwordDisplay').textContent = '••••••••';
    }
    
    cancelTestAdminEdit();
}

// 테스트 관리자 삭제
function clearTestAdmin() {
    if (confirm('테스트 관리자 계정을 삭제하시겠습니까?')) {
        document.getElementById('hidden_test_admin_id').value = '';
        document.getElementById('hidden_test_admin_password').value = '';
        
        // 화면에서 디스플레이 카드를 숨기고 추가 버튼 표시
        const displayDiv = document.getElementById('testAdminDisplay');
        if (displayDiv) {
            displayDiv.innerHTML = `
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    테스트 관리자가 삭제되었습니다. 저장 버튼을 눌러 변경사항을 저장하세요.
                </div>
            `;
        }
    }
}

// 테스트 관리자 추가 폼 표시
function showTestAdminAddForm() {
    document.getElementById('testAdminAddButton').classList.add('d-none');
    document.getElementById('testAdminAddForm').classList.remove('d-none');
    
    // hidden 필드를 실제 입력 필드로 교체
    const hiddenId = document.getElementById('hidden_test_admin_id');
    const hiddenPassword = document.getElementById('hidden_test_admin_password');
    if (hiddenId) hiddenId.remove();
    if (hiddenPassword) hiddenPassword.remove();
}

// 테스트 관리자 추가 폼 숨기기
function hideTestAdminAddForm() {
    document.getElementById('testAdminAddForm').classList.add('d-none');
    document.getElementById('testAdminAddButton').classList.remove('d-none');
    
    // 입력 필드 값 초기화
    document.getElementById('test_admin_id').value = '';
    document.getElementById('test_admin_password').value = '';
}
</script>
@endsection







