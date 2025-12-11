@extends('layouts.admin')

@section('title', '컨텍트폼')
@section('page-title', '컨텍트폼')
@section('page-subtitle', '사용자 신청 폼을 생성하고 관리합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>컨텍트폼 목록</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="showCreateForm()">
                    <i class="bi bi-plus-circle me-2"></i>컨텍트폼 생성
                </button>
            </div>
            <div class="card-body">
                @if($contactForms->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3">생성된 컨텍트폼이 없습니다.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>제목</th>
                                    <th>항목 수</th>
                                    <th>문의내용</th>
                                    <th>생성일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contactForms as $form)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.contact-forms.show', ['site' => $site->slug, 'contactForm' => $form->id]) }}" class="text-decoration-none">
                                                {{ $form->title }}
                                            </a>
                                        </td>
                                        <td>{{ count($form->fields) }}개</td>
                                        <td>
                                            @if($form->has_inquiry_content)
                                                <span class="badge bg-success">사용</span>
                                            @else
                                                <span class="badge bg-secondary">미사용</span>
                                            @endif
                                        </td>
                                        <td>{{ $form->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.contact-forms.show', ['site' => $site->slug, 'contactForm' => $form->id]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>보기
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteContactForm({{ $form->id }})">
                                                <i class="bi bi-trash me-1"></i>삭제
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 생성/수정 모달 -->
<div class="modal fade" id="contactFormModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">컨텍트폼 생성</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactFormForm">
                    <input type="hidden" id="contact_form_id" name="id">
                    
                    <div class="mb-3">
                        <label for="form_title" class="form-label">컨텍트폼 제목</label>
                        <input type="text" class="form-control" id="form_title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">항목 설정</label>
                        <div id="fieldsContainer">
                            <!-- 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addField()">
                            <i class="bi bi-plus-circle me-1"></i>항목 추가
                        </button>
                    </div>

                    <div class="mb-3 border-top pt-3">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="checkboxes_enabled" name="checkboxes_enabled" onchange="toggleCheckboxesSection()">
                            <label class="form-check-label" for="checkboxes_enabled">
                                체크박스 추가
                            </label>
                        </div>
                        <div id="checkboxesSection" style="display: none;">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkboxes_allow_multiple" name="checkboxes_allow_multiple">
                                    <label class="form-check-label" for="checkboxes_allow_multiple">
                                        복수 선택 허용
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">체크 항목</label>
                                <div id="checkboxesContainer">
                                    <!-- 동적으로 추가됨 -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCheckboxItem()">
                                    <i class="bi bi-plus-circle me-1"></i>체크 항목 추가
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="has_inquiry_content" name="has_inquiry_content">
                            <label class="form-check-label" for="has_inquiry_content">
                                문의내용 추가
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="button_text" class="form-label">버튼 표시</label>
                        <input type="text" class="form-control" id="button_text" name="button_text" value="신청하기" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveContactForm()">저장하기</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let fieldIndex = 0;
let checkboxIndex = 0;

function showCreateForm() {
    document.getElementById('modalTitle').textContent = '컨텍트폼 생성';
    document.getElementById('contactFormForm').reset();
    document.getElementById('contact_form_id').value = '';
    document.getElementById('fieldsContainer').innerHTML = '';
    document.getElementById('checkboxesContainer').innerHTML = '';
    document.getElementById('checkboxesSection').style.display = 'none';
    fieldIndex = 0;
    checkboxIndex = 0;
    addField(); // 기본 항목 하나 추가
    new bootstrap.Modal(document.getElementById('contactFormModal')).show();
}

function toggleCheckboxesSection() {
    const enabled = document.getElementById('checkboxes_enabled').checked;
    document.getElementById('checkboxesSection').style.display = enabled ? 'block' : 'none';
    if (!enabled) {
        document.getElementById('checkboxesContainer').innerHTML = '';
        checkboxIndex = 0;
    }
}

function addCheckboxItem() {
    const container = document.getElementById('checkboxesContainer');
    const itemDiv = document.createElement('div');
    itemDiv.className = 'card mb-2';
    itemDiv.id = 'checkbox_item_' + checkboxIndex;
    itemDiv.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-11">
                    <label class="form-label">체크 항목 라벨</label>
                    <input type="text" class="form-control checkbox-label" placeholder="예: 이용약관 동의" required>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCheckboxItem(${checkboxIndex})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(itemDiv);
    checkboxIndex++;
}

function removeCheckboxItem(index) {
    const itemDiv = document.getElementById('checkbox_item_' + index);
    if (itemDiv) {
        itemDiv.remove();
    }
}

function addField() {
    const container = document.getElementById('fieldsContainer');
    const fieldDiv = document.createElement('div');
    fieldDiv.className = 'card mb-2';
    fieldDiv.id = 'field_' + fieldIndex;
    fieldDiv.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <label class="form-label">항목이름</label>
                    <input type="text" class="form-control field-name" placeholder="예: 신청자 성함" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">항목 내용</label>
                    <input type="text" class="form-control field-placeholder" placeholder="예: 성함을 작성해주세요.">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeField(${fieldIndex})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(fieldDiv);
    fieldIndex++;
}

function removeField(index) {
    const fieldDiv = document.getElementById('field_' + index);
    if (fieldDiv) {
        fieldDiv.remove();
    }
}

function saveContactForm() {
    const form = document.getElementById('contactFormForm');
    const formData = new FormData(form);
    
    // 필드 데이터 수집
    const fields = [];
    const fieldDivs = document.querySelectorAll('[id^="field_"]');
    fieldDivs.forEach(div => {
        const nameInput = div.querySelector('.field-name');
        const placeholderInput = div.querySelector('.field-placeholder');
        if (nameInput && nameInput.value.trim()) {
            fields.push({
                name: nameInput.value.trim(),
                placeholder: placeholderInput ? placeholderInput.value.trim() : ''
            });
        }
    });

    if (fields.length === 0) {
        alert('최소 1개 이상의 항목을 추가해주세요.');
        return;
    }

    // 체크박스 데이터 수집
    const checkboxesEnabled = document.getElementById('checkboxes_enabled').checked;
    let checkboxesData = null;
    if (checkboxesEnabled) {
        const checkboxItems = [];
        const checkboxDivs = document.querySelectorAll('[id^="checkbox_item_"]');
        checkboxDivs.forEach(div => {
            const labelInput = div.querySelector('.checkbox-label');
            if (labelInput && labelInput.value.trim()) {
                checkboxItems.push({
                    label: labelInput.value.trim()
                });
            }
        });

        if (checkboxItems.length > 0) {
            checkboxesData = {
                enabled: true,
                allow_multiple: document.getElementById('checkboxes_allow_multiple').checked,
                items: checkboxItems
            };
        }
    }

    const data = {
        title: formData.get('title'),
        fields: fields,
        has_inquiry_content: document.getElementById('has_inquiry_content').checked,
        button_text: formData.get('button_text'),
        checkboxes: checkboxesData,
        _token: '{{ csrf_token() }}'
    };

    const contactFormId = document.getElementById('contact_form_id').value;
    const url = contactFormId 
        ? '{{ route("admin.contact-forms.update", ["site" => $site->slug, "contactForm" => ":id"]) }}'.replace(':id', contactFormId)
        : '{{ route("admin.contact-forms.store", ["site" => $site->slug]) }}';
    const method = contactFormId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('저장 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다.');
    });
}

function deleteContactForm(id) {
    if (!confirm('정말 삭제하시겠습니까?')) {
        return;
    }

    fetch('{{ route("admin.contact-forms.delete", ["site" => $site->slug, "contactForm" => ":id"]) }}'.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('삭제 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('삭제 중 오류가 발생했습니다.');
    });
}
</script>
@endpush
@endsection



