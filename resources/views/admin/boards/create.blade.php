@extends('layouts.admin')

@section('title', '게시판 만들기')
@section('page-title', '게시판 만들기')
@section('page-subtitle', '새로운 게시판을 생성합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- 일반 설정 -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>일반</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('boards.store', ['site' => $site->slug]) }}" id="generalForm">
                    @csrf
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td style="width: 150px;">
                                    <label for="type" class="form-label mb-0">
                                        게시판 타입
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시판의 표시 형태를 선택하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" name="type">
                                        @foreach(\App\Models\Board::getTypes() as $value => $label)
                                            @if($site->hasBoardType($value))
                                                <option value="{{ $value }}" {{ old('type', 'general') === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr id="pinterest-columns-row" style="display: none;">
                                <td style="width: 150px;">
                                    <label class="form-label mb-0">
                                        가로 개수 설정
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="핀터레스트 타입 게시판의 각 디바이스별 가로 개수를 설정하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="pinterest_columns_mobile" class="form-label small">모바일</label>
                                            <input type="number" 
                                                   class="form-control @error('pinterest_columns_mobile') is-invalid @enderror" 
                                                   id="pinterest_columns_mobile" 
                                                   name="pinterest_columns_mobile" 
                                                   value="{{ old('pinterest_columns_mobile', 2) }}" 
                                                   min="1" 
                                                   max="6"
                                                   required>
                                            <small class="form-text text-muted">기본: 2개</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pinterest_columns_tablet" class="form-label small">태블릿</label>
                                            <input type="number" 
                                                   class="form-control @error('pinterest_columns_tablet') is-invalid @enderror" 
                                                   id="pinterest_columns_tablet" 
                                                   name="pinterest_columns_tablet" 
                                                   value="{{ old('pinterest_columns_tablet', 3) }}" 
                                                   min="1" 
                                                   max="6"
                                                   required>
                                            <small class="form-text text-muted">기본: 3개</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pinterest_columns_desktop" class="form-label small">데스크톱</label>
                                            <input type="number" 
                                                   class="form-control @error('pinterest_columns_desktop') is-invalid @enderror" 
                                                   id="pinterest_columns_desktop" 
                                                   name="pinterest_columns_desktop" 
                                                   value="{{ old('pinterest_columns_desktop', 4) }}" 
                                                   min="1" 
                                                   max="6"
                                                   required>
                                            <small class="form-text text-muted">기본: 4개</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pinterest_columns_large" class="form-label small">큰 화면</label>
                                            <input type="number" 
                                                   class="form-control @error('pinterest_columns_large') is-invalid @enderror" 
                                                   id="pinterest_columns_large" 
                                                   name="pinterest_columns_large" 
                                                   value="{{ old('pinterest_columns_large', 6) }}" 
                                                   min="1" 
                                                   max="12"
                                                   required>
                                            <small class="form-text text-muted">기본: 6개</small>
                                        </div>
                                    </div>
                                    @error('pinterest_columns_mobile')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('pinterest_columns_tablet')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('pinterest_columns_desktop')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('pinterest_columns_large')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">
                                    <label for="name" class="form-label mb-0">
                                        이름
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시판의 이름을 입력하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="slug" class="form-label mb-0">
                                        연결 주소
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="URL에 사용될 슬러그를 입력하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                           id="slug" name="slug" value="{{ old('slug') }}">
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="description" class="form-label mb-0">
                                        설명
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시판에 대한 설명을 입력하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="2">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="saveAllBtn" onclick="submitAllForms()">
                            <i class="bi bi-check-circle me-2"></i>게시판 생성
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // 게시판 타입 변경 시 핀터레스트 컬럼 설정 표시/숨김
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const pinterestColumnsRow = document.getElementById('pinterest-columns-row');
        
        function togglePinterestColumns() {
            if (typeSelect && typeSelect.value === 'pinterest') {
                if (pinterestColumnsRow) pinterestColumnsRow.style.display = '';
            } else {
                if (pinterestColumnsRow) pinterestColumnsRow.style.display = 'none';
            }
        }
        
        if (typeSelect) {
            typeSelect.addEventListener('change', togglePinterestColumns);
            togglePinterestColumns(); // 초기 로드 시에도 체크
        }
    });
    
    // 일반 폼 제출 처리
    document.getElementById('generalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAllForms();
    });

    function submitAllForms() {
        const saveBtn = document.getElementById('saveAllBtn');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>저장 중...';
        }
        
        const formData = new FormData();
        
        // 일반 설정
        formData.append('type', document.getElementById('type').value);
        formData.append('name', document.getElementById('name').value);
        formData.append('slug', document.getElementById('slug').value);
        formData.append('description', document.getElementById('description')?.value || '');
        
        // 기본값 설정 (제거된 필드들의 기본값)
        formData.append('event_display_type', 'photo'); // 이벤트 타입 기본값
        formData.append('summary_length', '150'); // 블로그 타입 기본값
        
        // 핀터레스트 타입 컬럼 설정
        const pinterestColumnsMobile = document.getElementById('pinterest_columns_mobile');
        const pinterestColumnsTablet = document.getElementById('pinterest_columns_tablet');
        const pinterestColumnsDesktop = document.getElementById('pinterest_columns_desktop');
        const pinterestColumnsLarge = document.getElementById('pinterest_columns_large');
        if (pinterestColumnsMobile) formData.append('pinterest_columns_mobile', pinterestColumnsMobile.value || '2');
        if (pinterestColumnsTablet) formData.append('pinterest_columns_tablet', pinterestColumnsTablet.value || '3');
        if (pinterestColumnsDesktop) formData.append('pinterest_columns_desktop', pinterestColumnsDesktop.value || '4');
        if (pinterestColumnsLarge) formData.append('pinterest_columns_large', pinterestColumnsLarge.value || '6');
        formData.append('max_posts_per_day', '0');
        formData.append('posts_per_page', '20');
        formData.append('random_order', '0');
        formData.append('allow_multiple_topics', '0');
        formData.append('remove_links', '0');
        formData.append('enable_likes', '0');
        formData.append('saved_posts_enabled', '0');
        
        // 기본값 설정 (제거된 블록들의 기본값)
        formData.append('post_template', '');
        formData.append('footer_content', '');
        formData.append('seo_title', '');
        formData.append('seo_description', '');
        
        // 등급 & 포인트 기본값
        formData.append('read_permission', 'guest');
        formData.append('write_permission', 'user');
        formData.append('delete_permission', 'author');
        formData.append('comment_permission', 'user');
        formData.append('comment_delete_permission', 'author');
        formData.append('read_points', '0');
        formData.append('write_points', '0');
        formData.append('delete_points', '0');
        formData.append('comment_points', '0');
        formData.append('comment_delete_points', '0');
        
        // 기능 ON/OFF 기본값
        formData.append('enable_anonymous', '0');
        formData.append('enable_secret', '0');
        formData.append('force_secret', '0');
        formData.append('enable_reply', '0');
        formData.append('enable_comments', '1'); // 댓글은 기본 활성화
        formData.append('exclude_from_rss', '0');
        formData.append('prevent_drag', '0');
        formData.append('enable_attachments', '1'); // 첨부파일은 기본 활성화
        formData.append('enable_share', '1'); // 공유 기능은 기본 활성화
        formData.append('enable_author_comment_adopt', '0');
        formData.append('enable_admin_comment_adopt', '0');
        
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("boards.store", ["site" => $site->slug]) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            // 응답이 리다이렉트인 경우 (HTML 응답)
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            
            // JSON 응답 처리
            if (response.headers.get('content-type')?.includes('application/json')) {
                return response.json().then(data => {
                    if (data.success) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    } else if (data.limit_exceeded) {
                        // 플랜 제한 초과 모달 표시
                        if (typeof showPlanLimitModal === 'function') {
                            showPlanLimitModal(data.error);
                        } else {
                            alert(data.error);
                        }
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>게시판 생성';
                        }
                    } else if (data.errors) {
                        // 에러 메시지 표시
                        let errorMessages = [];
                        for (let field in data.errors) {
                            errorMessages.push(data.errors[field].join(', '));
                        }
                        alert('오류가 발생했습니다:\n' + errorMessages.join('\n'));
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>게시판 생성';
                        }
                    } else if (data.error) {
                        alert(data.error);
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>게시판 생성';
                        }
                    }
                });
            } else {
                // HTML 응답인 경우 페이지 리로드
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다.');
            const saveBtn = document.getElementById('saveAllBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>게시판 생성';
            }
        });
    }

    // 초기 로드 시 Tooltip 초기화
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltip 초기화
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@endsection


