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

            @include('master.sites.edit-features-section')

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
</script>
@endsection







