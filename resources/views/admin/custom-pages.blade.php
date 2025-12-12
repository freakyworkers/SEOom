@extends('layouts.admin')

@section('title', '커스텀 페이지')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header mb-4">
                <h1 class="h3 mb-2">커스텀 페이지</h1>
                <p class="text-muted">메인 페이지와 동일한 방식으로 위젯을 구성하여 커스텀 페이지를 만들 수 있습니다</p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div></div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPageModal">
                    <i class="bi bi-plus-circle me-1"></i>페이지 만들기
                </button>
            </div>

            @if($customPages->count() > 0)
                {{-- 데스크탑 버전 (기존 테이블) --}}
                <div class="card d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>이름</th>
                                    <th>슬러그</th>
                                    <th>설명</th>
                                    <th style="width: 150px;">작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customPages as $index => $page)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $page->name }}</strong>
                                        </td>
                                        <td><code class="small">{{ $page->slug }}</code></td>
                                        <td>
                                            <small class="text-muted">{{ Str::limit($page->description ?? '', 50) }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('custom-pages.show', ['site' => $site->slug, 'slug' => $page->slug]) }}" 
                                                   class="btn btn-outline-info" 
                                                   title="보기" 
                                                   target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.custom-pages.edit', ['site' => $site->slug, 'customPage' => $page->id]) }}" 
                                                   class="btn btn-outline-primary" title="편집">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        title="삭제"
                                                        onclick="deleteCustomPage({{ $page->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 모바일 버전 (카드 레이아웃) --}}
                <div class="d-md-none">
                    <div class="d-grid gap-3">
                        @foreach($customPages as $index => $page)
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-2">
                                        <div class="mb-2">
                                            <span class="badge bg-secondary me-2">#{{ $index + 1 }}</span>
                                            <span class="small text-muted">이름 :</span>
                                            <strong class="ms-1">{{ $page->name }}</strong>
                                        </div>
                                        <div class="mb-2">
                                            <span class="small text-muted">슬러그 :</span>
                                            <code class="small ms-1">{{ $page->slug }}</code>
                                        </div>
                                        @if($page->description)
                                            <div class="mb-2">
                                                <span class="small text-muted">설명 :</span>
                                                <span class="small text-muted ms-1">{{ Str::limit($page->description, 100) }}</span>
                                            </div>
                                        @else
                                            <div class="mb-2">
                                                <span class="small text-muted">설명 :</span>
                                                <span class="small text-muted ms-1">-</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end pt-2 border-top">
                                        <a href="{{ route('custom-pages.show', ['site' => $site->slug, 'slug' => $page->slug]) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="보기" 
                                           target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.custom-pages.edit', ['site' => $site->slug, 'customPage' => $page->id]) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="편집">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="삭제"
                                                onclick="deleteCustomPage({{ $page->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="mt-3 mb-2">등록된 페이지가 없습니다</h4>
                        <p class="text-muted mb-4">첫 커스텀 페이지를 만들어보세요!</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPageModal">
                            <i class="bi bi-plus-circle me-1"></i>페이지 만들기
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 페이지 만들기 모달 -->
<div class="modal fade" id="createPageModal" tabindex="-1" aria-labelledby="createPageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPageModalLabel">페이지 만들기</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createPageForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="page_name" class="form-label">페이지 이름</label>
                        <input type="text" class="form-control" id="page_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="page_slug" class="form-label">연결주소</label>
                        <input type="text" class="form-control" id="page_slug" name="slug" required>
                        <small class="form-text text-muted">예: about, contact 등</small>
                    </div>
                    <div class="mb-3">
                        <label for="page_description" class="form-label">설명</label>
                        <textarea class="form-control" id="page_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">페이지 생성</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('createPageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>생성 중...';
    
    fetch('{{ route("admin.custom-pages.store", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('페이지 생성에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('페이지 생성 중 오류가 발생했습니다.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

function deleteCustomPage(pageId) {
    if (!confirm('정말 이 페이지를 삭제하시겠습니까? 페이지 내의 모든 위젯도 함께 삭제됩니다.')) {
        return;
    }
    
    fetch('{{ route("admin.custom-pages.delete", ["site" => $site->slug, "customPage" => ":id"]) }}'.replace(':id', pageId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('페이지 삭제에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('페이지 삭제 중 오류가 발생했습니다.');
    });
}
</script>
@endpush
@endsection

