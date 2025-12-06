@extends('layouts.admin')

@section('title', '포인트 교환 관리')
@section('page-title', '포인트 교환 관리')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Settings Form -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>설정</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.point-exchange.update-settings', ['site' => $site->slug]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="page_title" class="form-label">페이지 제목</label>
                            <input type="text" class="form-control" id="page_title" name="page_title" 
                                   value="{{ old('page_title', $setting->page_title) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="notice_title" class="form-label">공지사항 제목</label>
                            <input type="text" class="form-control" id="notice_title" name="notice_title" 
                                   value="{{ old('notice_title', $setting->notice_title) }}" required>
                        </div>
                    </div>

                    <!-- Notices -->
                    <div class="mb-3">
                        <label class="form-label">안내 항목</label>
                        <div id="notices-container">
                            @if(old('notices'))
                                @foreach(old('notices') as $index => $notice)
                                    <div class="input-group mb-2 notice-item">
                                        <span class="input-group-text">{{ $index + 1 }}.</span>
                                        <input type="text" class="form-control" name="notices[]" value="{{ $notice }}">
                                        <button type="button" class="btn btn-outline-danger remove-notice">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @elseif($setting->notices)
                                @foreach($setting->notices as $index => $notice)
                                    <div class="input-group mb-2 notice-item">
                                        <span class="input-group-text">{{ $index + 1 }}.</span>
                                        <input type="text" class="form-control" name="notices[]" value="{{ $notice }}">
                                        <button type="button" class="btn btn-outline-danger remove-notice">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-notice">
                            <i class="bi bi-plus me-1"></i>안내 추가
                        </button>
                    </div>

                    <!-- Amount Range -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="min_amount" class="form-label">최소값</label>
                            <input type="number" class="form-control" id="min_amount" name="min_amount" 
                                   value="{{ old('min_amount', $setting->min_amount) }}" required min="1">
                        </div>
                        <div class="col-md-6">
                            <label for="max_amount" class="form-label">최대값</label>
                            <input type="number" class="form-control" id="max_amount" name="max_amount" 
                                   value="{{ old('max_amount', $setting->max_amount) }}" required min="1">
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="mb-3">
                        <label class="form-label">교환 신청 폼</label>
                        <div id="form-fields-container">
                            @if(old('form_fields'))
                                @foreach(old('form_fields') as $index => $field)
                                    <div class="row mb-2 form-field-item">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="form_fields[{{ $index }}][title]" 
                                                   placeholder="제목 (ex: 은행)" value="{{ $field['title'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="form_fields[{{ $index }}][content]" 
                                                   placeholder="내용 (ex: 국민은행)" value="{{ $field['content'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger w-100 remove-form-field">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif($setting->form_fields)
                                @foreach($setting->form_fields as $index => $field)
                                    <div class="row mb-2 form-field-item">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="form_fields[{{ $index }}][title]" 
                                                   placeholder="제목 (ex: 은행)" value="{{ $field['title'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="form_fields[{{ $index }}][content]" 
                                                   placeholder="내용 (ex: 국민은행)" value="{{ $field['content'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger w-100 remove-form-field">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-form-field">
                            <i class="bi bi-plus me-1"></i>신청 작성 항목 추가
                        </button>
                    </div>

                    <!-- Requirements -->
                    <div class="mb-3">
                        <label class="form-label">신청 조건</label>
                        <p class="text-muted small mb-2">교환 신청 버튼을 누르기 위한 조건입니다.</p>
                        <div id="requirements-container">
                            @php
                                $boards = \App\Models\Board::where('site_id', $site->id)->active()->ordered()->get();
                            @endphp
                            @if(old('requirements'))
                                @foreach(old('requirements') as $index => $requirement)
                                    <div class="row mb-2 requirement-item">
                                        <div class="col-md-4">
                                            <select class="form-select" name="requirements[{{ $index }}][board_id]" required>
                                                <option value="">게시판 선택</option>
                                                @foreach($boards as $board)
                                                    <option value="{{ $board->id }}" {{ ($requirement['board_id'] ?? '') == $board->id ? 'selected' : '' }}>
                                                        {{ $board->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="requirements[{{ $index }}][post_count]" 
                                                   placeholder="게시글수" value="{{ $requirement['post_count'] ?? '' }}" min="1" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="requirements[{{ $index }}][min_characters]" 
                                                   placeholder="최소 글자 수" value="{{ $requirement['min_characters'] ?? '' }}" min="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger w-100 remove-requirement">
                                                <i class="bi bi-x"></i> 삭제
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif($setting->requirements)
                                @foreach($setting->requirements as $index => $requirement)
                                    <div class="row mb-2 requirement-item">
                                        <div class="col-md-4">
                                            <select class="form-select" name="requirements[{{ $index }}][board_id]" required>
                                                <option value="">게시판 선택</option>
                                                @foreach($boards as $board)
                                                    <option value="{{ $board->id }}" {{ ($requirement['board_id'] ?? '') == $board->id ? 'selected' : '' }}>
                                                        {{ $board->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="requirements[{{ $index }}][post_count]" 
                                                   placeholder="게시글수" value="{{ $requirement['post_count'] ?? '' }}" min="1" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="requirements[{{ $index }}][min_characters]" 
                                                   placeholder="최소 글자 수" value="{{ $requirement['min_characters'] ?? '' }}" min="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger w-100 remove-requirement">
                                                <i class="bi bi-x"></i> 삭제
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-requirement">
                            <i class="bi bi-plus me-1"></i>조건추가
                        </button>
                    </div>

                    <!-- Display Settings -->
                    <div class="mb-3">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="random_order" name="random_order" 
                                       value="1" {{ old('random_order', $setting->random_order) ? 'checked' : '' }}>
                                <label class="form-check-label" for="random_order">
                                    랜덤배치
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">체크 시 교환 상품 리스트가 랜덤하게 표시됩니다.</small>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="products_per_page" class="form-label">페이지당 표시 개수</label>
                                <input type="number" class="form-control" id="products_per_page" name="products_per_page" 
                                       value="{{ old('products_per_page', $setting->products_per_page ?? 12) }}" 
                                       min="1" required>
                                <small class="text-muted">한 페이지에 표시할 교환 상품의 개수를 설정합니다.</small>
                            </div>
                            <div class="col-md-4">
                                <label for="pc_columns" class="form-label">PC가로열</label>
                                <input type="number" class="form-control" id="pc_columns" name="pc_columns" 
                                       value="{{ old('pc_columns', $setting->pc_columns ?? 4) }}" 
                                       min="1" max="12" required>
                                <small class="text-muted">PC 화면에서 가로로 표시할 개수입니다.</small>
                            </div>
                            <div class="col-md-4">
                                <label for="mobile_columns" class="form-label">모바일가로열</label>
                                <input type="number" class="form-control" id="mobile_columns" name="mobile_columns" 
                                       value="{{ old('mobile_columns', $setting->mobile_columns ?? 2) }}" 
                                       min="1" max="6" required>
                                <small class="text-muted">모바일 화면에서 가로로 표시할 개수입니다.</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>저장
                    </button>
                </form>
            </div>
        </div>

        <!-- Products -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-box me-2"></i>교환상품</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus me-1"></i>상품추가
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>썸네일</th>
                                <th>항목명</th>
                                <th>항목내용</th>
                                <th>대기</th>
                                <th>완료</th>
                                <th>보류</th>
                                <th>총합</th>
                                <th>총액</th>
                                <th>작업</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td>
                                        @if($product->thumbnail_path)
                                            <img src="{{ asset('storage/' . $product->thumbnail_path) }}" 
                                                 alt="{{ $product->item_content }}" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 0.25rem;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->item_name }}</td>
                                    <td>{{ $product->item_content }}</td>
                                    <td>{{ $product->pending_count }}</td>
                                    <td>{{ $product->completed_count }}</td>
                                    <td>{{ $product->rejected_count }}</td>
                                    <td>{{ $product->total_count }}</td>
                                    <td>{{ number_format($product->total_amount) }}P</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editProductModal{{ $product->id }}">
                                            <i class="bi bi-pencil"></i> 수정
                                        </button>
                                        <a href="{{ route('admin.point-exchange.applications', ['site' => $site->slug, 'product' => $product->id]) }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-list"></i> 신청목록
                                        </a>
                                        <form action="{{ route('admin.point-exchange.destroy-product', ['site' => $site->slug, 'product' => $product->id]) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> 삭제
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">등록된 상품이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.point-exchange.store-product', ['site' => $site->slug]) }}" 
                  method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">상품 추가</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">썸네일 업로드</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="item_name" class="form-label">항목명</label>
                        <input type="text" class="form-control" id="item_name" name="item_name" 
                               placeholder="ex: 사이트" required>
                    </div>
                    <div class="mb-3">
                        <label for="item_content" class="form-label">항목내용</label>
                        <input type="text" class="form-control" id="item_content" name="item_content" 
                               placeholder="ex: 에그벳" required>
                    </div>
                    <div class="mb-3">
                        <label for="notice" class="form-label">공지</label>
                        <textarea class="form-control" id="notice" name="notice" rows="3" 
                                  placeholder="ex: *포인트 지급이 어려운 경우 계좌로 입금해드리고있습니다."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modals -->
@foreach($products as $product)
<div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.point-exchange.update-product', ['site' => $site->slug, 'product' => $product->id]) }}" 
                  method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">상품 수정</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="thumbnail{{ $product->id }}" class="form-label">썸네일 업로드</label>
                        <input type="file" class="form-control" id="thumbnail{{ $product->id }}" name="thumbnail" accept="image/*">
                        @if($product->thumbnail_path)
                            <small class="text-muted">현재: {{ basename($product->thumbnail_path) }}</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="item_name{{ $product->id }}" class="form-label">항목명</label>
                        <input type="text" class="form-control" id="item_name{{ $product->id }}" name="item_name" 
                               value="{{ $product->item_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="item_content{{ $product->id }}" class="form-label">항목내용</label>
                        <input type="text" class="form-control" id="item_content{{ $product->id }}" name="item_content" 
                               value="{{ $product->item_content }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="notice{{ $product->id }}" class="form-label">공지</label>
                        <textarea class="form-control" id="notice{{ $product->id }}" name="notice" rows="3">{{ $product->notice }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add notice
    document.getElementById('add-notice').addEventListener('click', function() {
        const container = document.getElementById('notices-container');
        const index = container.children.length;
        const div = document.createElement('div');
        div.className = 'input-group mb-2 notice-item';
        div.innerHTML = `
            <span class="input-group-text">${index + 1}.</span>
            <input type="text" class="form-control" name="notices[]" placeholder="안내 내용 입력">
            <button type="button" class="btn btn-outline-danger remove-notice">
                <i class="bi bi-x"></i>
            </button>
        `;
        container.appendChild(div);
    });

    // Remove notice
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-notice')) {
            e.target.closest('.notice-item').remove();
            updateNoticeNumbers();
        }
    });

    function updateNoticeNumbers() {
        const items = document.querySelectorAll('.notice-item');
        items.forEach((item, index) => {
            item.querySelector('.input-group-text').textContent = (index + 1) + '.';
        });
    }

    // Add form field
    document.getElementById('add-form-field').addEventListener('click', function() {
        const container = document.getElementById('form-fields-container');
        const index = container.children.length;
        const div = document.createElement('div');
        div.className = 'row mb-2 form-field-item';
        div.innerHTML = `
            <div class="col-md-5">
                <input type="text" class="form-control" name="form_fields[${index}][title]" 
                       placeholder="제목 (ex: 은행)" required>
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control" name="form_fields[${index}][content]" 
                       placeholder="내용 (ex: 국민은행)" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100 remove-form-field">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });

    // Remove form field
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-form-field')) {
            e.target.closest('.form-field-item').remove();
        }
    });

    // Add requirement
    document.getElementById('add-requirement').addEventListener('click', function() {
        const container = document.getElementById('requirements-container');
        const index = container.children.length;
        const boards = @json($boards);
        let boardOptions = '<option value="">게시판 선택</option>';
        boards.forEach(function(board) {
            boardOptions += `<option value="${board.id}">${board.name}</option>`;
        });
        const div = document.createElement('div');
        div.className = 'row mb-2 requirement-item';
        div.innerHTML = `
            <div class="col-md-4">
                <select class="form-select" name="requirements[${index}][board_id]" required>
                    ${boardOptions}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="requirements[${index}][post_count]" 
                       placeholder="게시글수" min="1" required>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="requirements[${index}][min_characters]" 
                       placeholder="최소 글자 수" min="1" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100 remove-requirement">
                    <i class="bi bi-x"></i> 삭제
                </button>
            </div>
        `;
        container.appendChild(div);
    });

    // Remove requirement
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-requirement')) {
            e.target.closest('.requirement-item').remove();
        }
    });
});
</script>
@endpush
@endsection

