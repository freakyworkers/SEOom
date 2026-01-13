@extends('layouts.admin')

@section('title', '게시판 관리')
@section('page-title', '게시판 관리')
@section('page-subtitle', '게시판을 생성, 수정, 삭제할 수 있습니다')

@section('content')
<!-- 금지단어 설정 -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-shield-exclamation me-2"></i>금지단어</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="banned_words" class="form-label">
                금지단어를 , 로 구분해서 작성해주세요.
            </label>
            <textarea class="form-control" 
                      id="banned_words" 
                      name="banned_words" 
                      rows="3" 
                      placeholder="예: 단어1, 단어2, 단어3">{{ $site->getSetting('banned_words', '') }}</textarea>
        </div>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" onclick="saveBannedWords()">
                저장
            </button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ url('/boards/create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>게시판 만들기
    </a>
</div>

@if($boards->count() > 0)
    <div class="card">
        {{-- 데스크탑 버전 (테이블) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>이름</th>
                        <th>슬러그</th>
                        <th>설명</th>
                        <th style="width: 80px;">정렬</th>
                        <th style="width: 100px;">상태</th>
                        <th style="width: 100px;">게시글 수</th>
                        <th style="width: 150px;">작업</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($boards as $board)
                        <tr>
                            <td>{{ $board->id }}</td>
                            <td>
                                <strong>{{ $board->name }}</strong>
                            </td>
                            <td><code class="small">{{ $board->slug }}</code></td>
                            <td>
                                <small class="text-muted">{{ Str::limit($board->description, 50) }}</small>
                            </td>
                            <td>{{ $board->order }}</td>
                            <td>
                                @if($board->is_active)
                                    <span class="badge bg-success">활성</span>
                                @else
                                    <span class="badge bg-secondary">비활성</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $board->activePosts()->count() }}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('/boards/' . $board->slug) }}" 
                                       class="btn btn-outline-info btn-sm board-action-btn" title="보기" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ url('/boards/' . $board->id . '/edit') }}" 
                                       class="btn btn-outline-primary btn-sm board-action-btn" title="수정">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ url('/boards/' . $board->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm board-action-btn" title="삭제">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- 모바일 버전 (카드 레이아웃) --}}
        <div class="d-md-none">
            <div class="d-grid gap-3 p-3">
                @foreach($boards as $board)
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1 fw-bold">{{ $board->name }}</h6>
                                    <small class="text-muted d-block">
                                        <code class="small">{{ $board->slug }}</code>
                                    </small>
                                </div>
                                <div>
                                    @if($board->is_active)
                                        <span class="badge bg-success">활성</span>
                                    @else
                                        <span class="badge bg-secondary">비활성</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($board->description)
                                <p class="small text-muted mb-2">{{ Str::limit($board->description, 100) }}</p>
                            @endif
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-secondary">ID: {{ $board->id }}</span>
                                    <span class="badge bg-info">정렬: {{ $board->order }}</span>
                                    <span class="badge bg-primary">게시글: {{ $board->activePosts()->count() }}</span>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="{{ url('/boards/' . $board->slug) }}" 
                                   class="btn btn-outline-info btn-sm flex-fill board-action-btn" title="보기" target="_blank">
                                    <i class="bi bi-eye me-1"></i>보기
                                </a>
                                <a href="{{ url('/boards/' . $board->id . '/edit') }}" 
                                   class="btn btn-outline-primary btn-sm flex-fill board-action-btn" title="수정">
                                    <i class="bi bi-pencil me-1"></i>수정
                                </a>
                                <form action="{{ url('/boards/' . $board->id) }}" 
                                      method="POST" 
                                      class="flex-fill"
                                      onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 board-action-btn" title="삭제">
                                        <i class="bi bi-trash me-1"></i>삭제
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @if($boards->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-center mt-4">
                    @php
                        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                        $pointColor = $themeDarkMode === 'dark' 
                            ? $site->getSetting('color_dark_point_main', '#ffffff')
                            : $site->getSetting('color_light_point_main', '#0d6efd');
                    @endphp
                    {{ $boards->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                </div>
            </div>
        @endif
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3 mb-2">등록된 게시판이 없습니다</h4>
            <p class="text-muted mb-4">첫 게시판을 만들어보세요!</p>
            <a href="{{ url('/boards/create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>게시판 만들기
            </a>
        </div>
    </div>
@endif

@push('styles')
<style>
.board-action-btn {
    border-radius: 0.375rem !important;
    min-width: 38px;
    padding: 0.375rem 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
function saveBannedWords() {
    const bannedWords = document.getElementById('banned_words').value;
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>저장 중...';
    
    fetch('{{ url("/admin/banned-words") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            banned_words: bannedWords
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('금지단어가 저장되었습니다.');
        } else {
            alert('저장 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
@endpush
@endsection
