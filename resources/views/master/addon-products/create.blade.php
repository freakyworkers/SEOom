@extends('layouts.master')

@section('title', '추가 구매 상품 생성')
@section('page-title', '추가 구매 상품 생성')
@section('page-subtitle', '새로운 추가 구매 상품을 생성합니다')

@section('content')
<form method="POST" action="{{ route('master.addon-products.store') }}">
    @csrf
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">상품 정보</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">상품명 <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="slug" class="form-label">슬러그</label>
                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="자동 생성됩니다">
                @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">비워두면 상품명으로 자동 생성됩니다.</small>
            </div>

            <div class="mb-3">
                <label for="thumbnail" class="form-label">썸네일</label>
                <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/*">
                @error('thumbnail')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">상품 썸네일 이미지를 업로드하세요. (최대 5MB, jpeg, png, jpg, gif, webp)</small>
                <div id="thumbnail-preview" class="mt-2" style="display: none;">
                    <img id="thumbnail-preview-img" src="" alt="썸네일 미리보기" style="max-width: 200px; max-height: 200px; border-radius: 0.375rem;">
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">설명</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="10">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">상품 타입 <span class="text-danger">*</span></label>
                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                    <option value="">선택하세요</option>
                    <optgroup label="리소스">
                        <option value="storage" {{ old('type') === 'storage' ? 'selected' : '' }}>저장 용량</option>
                        <option value="traffic" {{ old('type') === 'traffic' ? 'selected' : '' }}>트래픽</option>
                    </optgroup>
                    <optgroup label="대 메뉴 기능">
                        <option value="feature_crawler" {{ old('type') === 'feature_crawler' ? 'selected' : '' }}>크롤러</option>
                        <option value="feature_event_application" {{ old('type') === 'feature_event_application' ? 'selected' : '' }}>신청형 이벤트</option>
                        <option value="feature_point_exchange" {{ old('type') === 'feature_point_exchange' ? 'selected' : '' }}>포인트 교환</option>
                    </optgroup>
                    <optgroup label="게시판 타입">
                        <option value="board_type_event" {{ old('type') === 'board_type_event' ? 'selected' : '' }}>이벤트</option>
                    </optgroup>
                    <optgroup label="회원가입 세부기능">
                        <option value="registration_referral" {{ old('type') === 'registration_referral' ? 'selected' : '' }}>추천인 기능</option>
                    </optgroup>
                    <optgroup label="쪽지 기능">
                        <option value="feature_point_message" {{ old('type') === 'feature_point_message' ? 'selected' : '' }}>포인트 쪽지</option>
                    </optgroup>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3" id="amount_mb_container">
                <label for="amount_mb" class="form-label">용량 (MB) <span class="text-danger" id="amount_mb_required">*</span></label>
                <input type="number" class="form-control @error('amount_mb') is-invalid @enderror" id="amount_mb" name="amount_mb" value="{{ old('amount_mb') }}" min="1">
                @error('amount_mb')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">제공할 용량을 MB 단위로 입력하세요. (예: 10240 = 10GB) - 리소스 타입에만 필요합니다. 옵션을 사용하는 경우 이 필드는 무시됩니다.</small>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">가격 (원) <span class="text-danger" id="price_required">*</span></label>
                <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" min="0" step="0.01">
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">옵션을 사용하는 경우 이 필드는 무시됩니다.</small>
            </div>

            <!-- 옵션 섹션 -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">상품 옵션</label>
                    <button type="button" class="btn btn-sm btn-primary" id="add-option-btn">
                        <i class="bi bi-plus-circle me-1"></i>옵션 추가
                    </button>
                </div>
                <small class="form-text text-muted d-block mb-2">옵션을 추가하면 위의 용량과 가격 필드는 무시됩니다. (예: 10000MB 매월, 20000MB 매월)</small>
                <div id="options-container">
                    <!-- 옵션들이 여기에 동적으로 추가됩니다 -->
                </div>
            </div>

            <div class="mb-3">
                <label for="billing_cycle" class="form-label">결제 주기 <span class="text-danger">*</span></label>
                <select class="form-select @error('billing_cycle') is-invalid @enderror" id="billing_cycle" name="billing_cycle" required>
                    <option value="one_time" {{ old('billing_cycle') === 'one_time' ? 'selected' : '' }}>일회성</option>
                    <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>월간</option>
                </select>
                @error('billing_cycle')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        활성화
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label for="sort_order" class="form-label">정렬 순서</label>
                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('master.addon-products.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>취소
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>생성
                </button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const typeSelect = document.getElementById('type');
    const amountMbInput = document.getElementById('amount_mb');
    const amountMbContainer = document.getElementById('amount_mb_container');
    const amountMbRequired = document.getElementById('amount_mb_required');
    
    // 리소스 타입만 amount_mb가 필요
    const resourceTypes = ['storage', 'traffic'];
    
    function updateAmountMbField() {
        const selectedType = typeSelect.value;
        const isResourceType = resourceTypes.includes(selectedType);
        
        if (isResourceType) {
            amountMbInput.required = true;
            amountMbContainer.style.display = 'block';
            amountMbRequired.style.display = 'inline';
        } else {
            amountMbInput.required = false;
            amountMbInput.value = '';
            amountMbContainer.style.display = 'none';
            amountMbRequired.style.display = 'none';
        }
    }
    
    // 타입 변경 시 amount_mb 필드 업데이트
    typeSelect.addEventListener('change', updateAmountMbField);
    
    // 초기 로드 시에도 업데이트
    updateAmountMbField();
    
    // 상품명 입력 시 슬러그 자동 생성
    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
            const slug = nameInput.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9가-힣\s-]/g, '')
                .replace(/[\s가-힣]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
    });
    
    // 슬러그 수동 입력 시 자동 생성 비활성화
    slugInput.addEventListener('input', function() {
        if (slugInput.value) {
            slugInput.dataset.autoGenerated = 'false';
        }
    });
    
    // Summernote 초기화
    if (typeof $.fn.summernote !== 'undefined') {
        $('#description').summernote({
            height: 400,
            lang: 'ko-KR',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            fontNames: ['맑은 고딕', 'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Times', 'Times New Roman', 'Verdana'],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '36', '48', '64', '82', '150']
        });
    }
    
    // 폼 제출 전 Summernote 내용을 textarea에 저장
    document.querySelector('form').addEventListener('submit', function(e) {
        if (typeof $.fn.summernote !== 'undefined' && $('#description').summernote('code')) {
            $('#description').val($('#description').summernote('code'));
        }
    });
    
    // 썸네일 미리보기
    const thumbnailInput = document.getElementById('thumbnail');
    const thumbnailPreview = document.getElementById('thumbnail-preview');
    const thumbnailPreviewImg = document.getElementById('thumbnail-preview-img');
    
    if (thumbnailInput) {
        thumbnailInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    thumbnailPreviewImg.src = e.target.result;
                    thumbnailPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                thumbnailPreview.style.display = 'none';
            }
        });
    }
    
    // 옵션 추가 기능
    let optionIndex = 0;
    const optionsContainer = document.getElementById('options-container');
    const addOptionBtn = document.getElementById('add-option-btn');
    
    function addOption(optionData = null) {
        const isResourceType = resourceTypes.includes(typeSelect.value);
        const optionId = optionData ? optionData.id : null;
        const optionName = optionData ? optionData.name : '';
        const optionAmountMb = optionData ? optionData.amount_mb : '';
        const optionPrice = optionData ? optionData.price : '';
        const optionSortOrder = optionData ? optionData.sort_order : optionIndex;
        const optionIsActive = optionData ? optionData.is_active : true;
        
        const optionHtml = `
            <div class="card mb-2 option-item" data-option-index="${optionIndex}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label small">옵션명</label>
                            <input type="text" class="form-control form-control-sm" name="options[${optionIndex}][name]" value="${optionName}" placeholder="예: 10000MB" required>
                            ${optionId ? `<input type="hidden" name="options[${optionIndex}][id]" value="${optionId}">` : ''}
                        </div>
                        ${isResourceType ? `
                        <div class="col-md-2 mb-2">
                            <label class="form-label small">용량 (MB)</label>
                            <input type="number" class="form-control form-control-sm" name="options[${optionIndex}][amount_mb]" value="${optionAmountMb}" min="1" placeholder="10000">
                        </div>
                        ` : ''}
                        <div class="col-md-2 mb-2">
                            <label class="form-label small">가격 (원)</label>
                            <input type="number" class="form-control form-control-sm" name="options[${optionIndex}][price]" value="${optionPrice}" min="0" step="0.01" placeholder="10000" required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label small">정렬 순서</label>
                            <input type="number" class="form-control form-control-sm" name="options[${optionIndex}][sort_order]" value="${optionSortOrder}" min="0">
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="options[${optionIndex}][is_active]" value="1" ${optionIsActive ? 'checked' : ''} id="option_active_${optionIndex}">
                                <label class="form-check-label small" for="option_active_${optionIndex}">활성화</label>
                            </div>
                        </div>
                        <div class="col-md-1 mb-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-danger w-100 remove-option-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        optionsContainer.insertAdjacentHTML('beforeend', optionHtml);
        optionIndex++;
        
        // 옵션이 있으면 기본 필드 비활성화
        updateFieldVisibility();
    }
    
    if (addOptionBtn) {
        addOptionBtn.addEventListener('click', function() {
            addOption();
        });
    }
    
    // 옵션 삭제
    if (optionsContainer) {
        optionsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-option-btn')) {
                e.target.closest('.option-item').remove();
                updateFieldVisibility();
            }
        });
    }
    
    // 타입 변경 시 옵션 필드 업데이트
    typeSelect.addEventListener('change', function() {
        const isResourceType = resourceTypes.includes(this.value);
        const optionItems = optionsContainer.querySelectorAll('.option-item');
        optionItems.forEach(item => {
            const amountMbContainer = item.querySelector('input[name*="[amount_mb]"]')?.closest('.col-md-2');
            if (amountMbContainer) {
                amountMbContainer.style.display = isResourceType ? 'block' : 'none';
            }
        });
    });
    
    function updateFieldVisibility() {
        const hasOptions = optionsContainer && optionsContainer.querySelectorAll('.option-item').length > 0;
        const priceRequired = document.getElementById('price_required');
        
        if (hasOptions) {
            if (amountMbRequired) amountMbRequired.style.display = 'none';
            if (priceRequired) priceRequired.style.display = 'none';
        } else {
            if (amountMbRequired) amountMbRequired.style.display = 'inline';
            if (priceRequired) priceRequired.style.display = 'inline';
        }
    }
});
</script>
@endsection

