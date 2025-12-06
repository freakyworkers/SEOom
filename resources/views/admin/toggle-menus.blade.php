@extends('layouts.admin')

@section('title', '토글 메뉴')
@section('page-title', '토글 메뉴')
@section('page-subtitle', '토글 메뉴를 관리합니다')

@push('styles')
<style>
    .toggle-menu-item {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
        background-color: #fff;
    }
    .toggle-menu-header {
        padding: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .toggle-menu-header:hover {
        background-color: #e9ecef;
    }
    .toggle-menu-header.active {
        background-color: #e7f3ff;
    }
    .toggle-menu-content {
        padding: 1rem;
        display: none;
    }
    .toggle-menu-content.show {
        display: block;
    }
    .toggle-icon {
        transition: transform 0.3s;
    }
    .toggle-icon.active {
        transform: rotate(90deg);
    }
    .toggle-menu-list {
        max-height: 600px;
        overflow-y: auto;
    }
    .item-row {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background-color: #f8f9fa;
    }
    .item-row-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<!-- 새로 생성 섹션 -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>토글 메뉴 추가</h5>
    </div>
    <div class="card-body">
        <form id="toggleMenuForm">
            <div class="mb-3">
                <label for="name" class="form-label">토글 이름 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required placeholder="토글 이름을 입력하세요">
            </div>
            
            <div class="mb-3">
                <label class="form-label">토글 항목</label>
                <div class="row g-2 mb-2">
                    <div class="col-md-5">
                        <input type="text" class="form-control" id="item_title" placeholder="토글 제목을 입력하세요">
                    </div>
                    <div class="col-md-5">
                        <textarea class="form-control" id="item_content" rows="2" placeholder="토글 내용을 입력하세요"></textarea>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" onclick="addItem()">
                            <i class="bi bi-plus-circle me-1"></i>항목 추가
                        </button>
                    </div>
                </div>
            </div>

            <div id="itemsList" class="mb-3"></div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 토글 메뉴 목록 -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">토글 메뉴 목록</h5>
    </div>
    <div class="card-body">
        <div class="toggle-menu-list" id="toggleMenuList">
            @if($toggleMenus->count() > 0)
                @foreach($toggleMenus as $toggleMenu)
                    <div class="toggle-menu-group mb-4" data-id="{{ $toggleMenu->id }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="text-muted small">{{ $toggleMenu->name }}</span>
                                @if(!$toggleMenu->is_active)
                                    <span class="badge bg-secondary ms-2">비활성</span>
                                @endif
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="editToggleMenu({{ $toggleMenu->id }}, event)">
                                    <i class="bi bi-pencil"></i> 수정
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="toggleActive({{ $toggleMenu->id }}, event)">
                                    <i class="bi bi-{{ $toggleMenu->is_active ? 'eye-slash' : 'eye' }}"></i> {{ $toggleMenu->is_active ? '비활성' : '활성' }}
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteToggleMenu({{ $toggleMenu->id }}, event)">
                                    <i class="bi bi-trash"></i> 삭제
                                </button>
                            </div>
                        </div>
                        @foreach($toggleMenu->items as $item)
                            <div class="toggle-menu-item mb-2" data-item-id="{{ $item->id }}">
                                <div class="toggle-menu-header" onclick="toggleItemContent(this)">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-chevron-right toggle-icon me-2"></i>
                                        <strong>{{ $item->title }}</strong>
                                    </div>
                                </div>
                                <div class="toggle-menu-content">
                                    <div class="p-3">
                                        {!! nl2br(e($item->content)) !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                    <p>등록된 토글 메뉴가 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 수정 모달 -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">토글 메뉴 수정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editToggleMenuForm">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">토글 이름 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">토글 항목</label>
                        <div class="row g-2 mb-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" id="edit_item_title" placeholder="토글 제목을 입력하세요">
                            </div>
                            <div class="col-md-5">
                                <textarea class="form-control" id="edit_item_content" rows="2" placeholder="토글 내용을 입력하세요"></textarea>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100" onclick="addEditItem()">
                                    <i class="bi bi-plus-circle me-1"></i>항목 추가
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="editItemsList" class="mb-3"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveEdit()">저장</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let items = [];
    let editItems = [];

    // 항목 추가 (생성 폼)
    function addItem() {
        const title = document.getElementById('item_title').value.trim();
        const content = document.getElementById('item_content').value.trim();
        
        if (!title || !content) {
            alert('토글 제목과 내용을 모두 입력해주세요.');
            return;
        }

        items.push({ title, content });
        renderItems();
        
        // 입력 필드 초기화
        document.getElementById('item_title').value = '';
        document.getElementById('item_content').value = '';
    }

    // 항목 추가 (수정 폼)
    function addEditItem() {
        const title = document.getElementById('edit_item_title').value.trim();
        const content = document.getElementById('edit_item_content').value.trim();
        
        if (!title || !content) {
            alert('토글 제목과 내용을 모두 입력해주세요.');
            return;
        }

        editItems.push({ title, content });
        renderEditItems();
        
        // 입력 필드 초기화
        document.getElementById('edit_item_title').value = '';
        document.getElementById('edit_item_content').value = '';
    }

    // 항목 목록 렌더링 (생성 폼)
    function renderItems() {
        const container = document.getElementById('itemsList');
        container.innerHTML = '';
        
        items.forEach((item, index) => {
            const div = document.createElement('div');
            div.className = 'item-row';
            div.innerHTML = `
                <div class="item-row-header">
                    <strong>${item.title}</strong>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">
                        <i class="bi bi-trash"></i> 삭제
                    </button>
                </div>
                <p class="mb-0">${item.content}</p>
            `;
            container.appendChild(div);
        });
    }

    // 항목 목록 렌더링 (수정 폼)
    function renderEditItems() {
        const container = document.getElementById('editItemsList');
        container.innerHTML = '';
        
        editItems.forEach((item, index) => {
            const div = document.createElement('div');
            div.className = 'item-row';
            div.innerHTML = `
                <div class="item-row-header">
                    <strong>${item.title}</strong>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeEditItem(${index})">
                        <i class="bi bi-trash"></i> 삭제
                    </button>
                </div>
                <p class="mb-0">${item.content}</p>
            `;
            container.appendChild(div);
        });
    }

    // 항목 삭제 (생성 폼)
    function removeItem(index) {
        items.splice(index, 1);
        renderItems();
    }

    // 항목 삭제 (수정 폼)
    function removeEditItem(index) {
        editItems.splice(index, 1);
        renderEditItems();
    }

    // 토글 항목 내용 펼치기/접기
    function toggleItemContent(header) {
        const item = header.closest('.toggle-menu-item');
        const content = item.querySelector('.toggle-menu-content');
        const icon = header.querySelector('.toggle-icon');
        
        if (content.classList.contains('show')) {
            content.classList.remove('show');
            header.classList.remove('active');
            icon.classList.remove('active');
        } else {
            content.classList.add('show');
            header.classList.add('active');
            icon.classList.add('active');
        }
    }

    // 토글 메뉴 추가
    document.getElementById('toggleMenuForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('name').value.trim();
        
        if (!name) {
            alert('토글 이름을 입력해주세요.');
            return;
        }

        if (items.length === 0) {
            alert('최소 하나의 토글 항목을 추가해주세요.');
            return;
        }
        
        const formData = {
            name: name,
            items: items
        };
        
        fetch('{{ route("admin.toggle-menus.store", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    });


    // 토글 메뉴 수정
    function editToggleMenu(id, event) {
        event.stopPropagation();
        
        fetch(`/site/{{ $site->slug }}/admin/toggle-menus/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const toggleMenu = data.toggleMenu;
                document.getElementById('edit_id').value = toggleMenu.id;
                document.getElementById('edit_name').value = toggleMenu.name;
                
                // 항목들 초기화 및 로드
                editItems = toggleMenu.items.map(item => ({
                    title: item.title,
                    content: item.content
                }));
                renderEditItems();
                
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('토글 메뉴 정보를 불러오는데 실패했습니다.');
        });
    }

    // 수정 저장
    function saveEdit() {
        const id = document.getElementById('edit_id').value;
        const name = document.getElementById('edit_name').value.trim();
        
        if (!name) {
            alert('토글 이름을 입력해주세요.');
            return;
        }

        if (editItems.length === 0) {
            alert('최소 하나의 토글 항목을 추가해주세요.');
            return;
        }
        
        fetch(`/site/{{ $site->slug }}/admin/toggle-menus/${id}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                items: editItems
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    }

    // 토글 메뉴 삭제
    function deleteToggleMenu(id, event) {
        event.stopPropagation();
        
        if (!confirm('정말 삭제하시겠습니까?')) {
            return;
        }
        
        fetch(`/site/{{ $site->slug }}/admin/toggle-menus/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    }

    // 활성/비활성 토글
    function toggleActive(id, event) {
        event.stopPropagation();
        
        fetch(`/site/{{ $site->slug }}/admin/toggle-menus/${id}/toggle-active`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    }
</script>
@endpush
@endsection
