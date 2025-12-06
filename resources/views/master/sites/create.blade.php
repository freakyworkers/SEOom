@extends('layouts.master')

@section('title', '사이트 생성')
@section('page-title', '사이트 생성')
@section('page-subtitle', '새로운 사이트를 생성합니다')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>사이트 정보</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('master.sites.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">사이트 이름 <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
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
                           value="{{ old('slug') }}"
                           placeholder="자동 생성됩니다">
                    <small class="form-text text-muted">비워두면 사이트 이름으로 자동 생성됩니다.</small>
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
                           value="{{ old('domain') }}"
                           placeholder="example.com">
                    @error('domain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="plan" class="form-label">요금제 <span class="text-danger">*</span></label>
                    <select class="form-select @error('plan') is-invalid @enderror" 
                            id="plan" 
                            name="plan" 
                            required>
                        <option value="">선택하세요</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->slug }}" 
                                    {{ old('plan') === $plan->slug ? 'selected' : '' }}
                                    data-price="{{ $plan->price }}"
                                    data-description="{{ $plan->description }}">
                                {{ $plan->name }}
                                @if($plan->price > 0)
                                    ({{ number_format($plan->price) }}원/월)
                                @else
                                    (무료)
                                @endif
                                @if($plan->is_default)
                                    - 기본 플랜
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted" id="plan-description"></small>
                    @error('plan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <!-- 사이트별 기능 설정 -->
            @php
                $currentPlan = null;
                $customFeaturesArray = null;
                if (old('plan')) {
                    $currentPlan = \App\Models\Plan::where('slug', old('plan'))->first();
                }
                
                // 게시판 타입 목록
                $boardTypes = \App\Models\Board::getTypes();
                
                // 위젯 타입 목록
                $sidebarWidgetTypes = \App\Models\SidebarWidget::getAvailableTypes();
                unset($sidebarWidgetTypes['statistics'], $sidebarWidgetTypes['notice'], $sidebarWidgetTypes['user_activity']);
                $mainWidgetTypes = \App\Models\MainWidget::getAvailableTypes();
                unset($mainWidgetTypes['statistics'], $mainWidgetTypes['notice'], $mainWidgetTypes['user_activity']);
                $customPageWidgetTypes = \App\Models\CustomPageWidget::getAvailableTypes();
                unset($customPageWidgetTypes['statistics'], $customPageWidgetTypes['notice'], $customPageWidgetTypes['user_activity']);
                
                // 모든 플랜 정보를 JavaScript에서 사용할 수 있도록 전달
                $allPlans = \App\Models\Plan::where('is_active', true)->get();
                $allPlansData = $allPlans->mapWithKeys(function($plan) {
                    return [$plan->slug => [
                        'features' => $plan->features ?? [],
                        'limits' => $plan->limits ?? [],
                    ]];
                });
            @endphp
            @include('master.sites.edit-features-section')

            <hr class="my-4">

            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>관리자 계정 정보</strong><br>
                사이트 생성 시 함께 생성될 관리자 계정 정보를 입력해주세요. 이 계정으로 해당 사이트의 관리자 페이지에 접근할 수 있습니다.
            </div>

            <h5 class="mb-3"><i class="bi bi-person-gear me-2"></i>관리자 계정</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="admin_name" class="form-label">관리자 이름 <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('admin_name') is-invalid @enderror" 
                           id="admin_name" 
                           name="admin_name" 
                           value="{{ old('admin_name') }}" 
                           placeholder="관리자 이름을 입력하세요"
                           required>
                    @error('admin_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="admin_email" class="form-label">관리자 이메일 <span class="text-danger">*</span></label>
                    <input type="email" 
                           class="form-control @error('admin_email') is-invalid @enderror" 
                           id="admin_email" 
                           name="admin_email" 
                           value="{{ old('admin_email') }}" 
                           placeholder="admin@example.com"
                           required>
                    <small class="form-text text-muted">로그인 시 사용할 이메일 주소입니다.</small>
                    @error('admin_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="admin_password" class="form-label">관리자 비밀번호 <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control @error('admin_password') is-invalid @enderror" 
                           id="admin_password" 
                           name="admin_password" 
                           placeholder="비밀번호를 입력하세요"
                           minlength="8"
                           required>
                    <small class="form-text text-muted">최소 8자 이상의 비밀번호를 입력해주세요.</small>
                    @error('admin_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="admin_password_confirmation" class="form-label">비밀번호 확인 <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control @error('admin_password') is-invalid @enderror" 
                           id="admin_password_confirmation" 
                           name="admin_password_confirmation" 
                           placeholder="비밀번호를 다시 입력하세요"
                           minlength="8"
                           required>
                    <small class="form-text text-muted">위에서 입력한 비밀번호와 동일하게 입력해주세요.</small>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('master.sites.index') }}" class="btn btn-secondary me-2">
                    <i class="bi bi-x-circle me-1"></i>취소
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>사이트 생성
                </button>
            </div>
        </form>
    </div>
</div>

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
        
        // 초기 로드 시 설명 표시 및 features 적용
        if (planSelect.value) {
            planSelect.dispatchEvent(new Event('change'));
        } else {
            // 초기 로드 시 모든 배지를 "추가 기능"으로 설정
            document.querySelectorAll('input[type="checkbox"][name^="custom_"]').forEach(cb => {
                updateBadge(cb, false);
            });
        }
    }
});

// 플랜의 features를 체크박스에 자동 적용
function applyPlanFeatures(planData) {
    // 먼저 모든 체크박스 해제
    document.querySelectorAll('input[type="checkbox"][name^="custom_"]').forEach(cb => cb.checked = false);
    
    const features = planData.features || {};
    
    // 대 메뉴 기능
    if (features.main_features) {
        features.main_features.forEach(feature => {
            const checkbox = document.getElementById('custom_main_feature_' + feature);
            if (checkbox) {
                checkbox.checked = true;
                // 배지 업데이트
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
const allPlansData = @json($allPlansData ?? []);

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
</script>
@endsection







