@extends('layouts.admin')

@section('title', '게시글 관리')
@section('page-title', '게시글 관리')
@section('page-subtitle', '모든 게시글을 관리할 수 있습니다')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>게시글 목록</h5>
        <span class="badge bg-primary">총 {{ $posts->total() }}개</span>
    </div>
    <div class="card-body">
        <!-- 필터 검색 폼 -->
        <form method="GET" action="{{ route('admin.posts', ['site' => $site->slug]) }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="board_id" class="form-label small">게시판</label>
                    <select name="board_id" id="board_id" class="form-select form-select-sm">
                        <option value="">전체 게시판</option>
                        @foreach($boards as $board)
                            <option value="{{ $board->id }}" {{ request('board_id') == $board->id ? 'selected' : '' }}>
                                {{ $board->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="author" class="form-label small">작성자</label>
                    <input type="text" 
                           name="author" 
                           id="author" 
                           class="form-control form-control-sm" 
                           placeholder="작성자 이름"
                           value="{{ request('author') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label small">시작일</label>
                    <input type="date" 
                           name="date_from" 
                           id="date_from" 
                           class="form-control form-control-sm" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label small">종료일</label>
                    <input type="date" 
                           name="date_to" 
                           id="date_to" 
                           class="form-control form-control-sm" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label for="views_min" class="form-label small">조회수 (최소)</label>
                    <input type="number" 
                           name="views_min" 
                           id="views_min" 
                           class="form-control form-control-sm" 
                           placeholder="0"
                           min="0"
                           value="{{ request('views_min') }}">
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <label for="views_max" class="form-label small">조회수 (최대)</label>
                    <input type="number" 
                           name="views_max" 
                           id="views_max" 
                           class="form-control form-control-sm" 
                           placeholder="무제한"
                           min="0"
                           value="{{ request('views_max') }}">
                </div>
                <div class="col-md-3">
                    <label for="sort_by" class="form-label small">정렬 기준</label>
                    <select name="sort_by" id="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>작성일</option>
                        <option value="views" {{ request('sort_by') == 'views' ? 'selected' : '' }}>조회수</option>
                        <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>제목</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sort_order" class="form-label small">정렬 순서</label>
                    <select name="sort_order" id="sort_order" class="form-select form-select-sm">
                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>내림차순</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>오름차순</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="per_page" class="form-label small">표시 개수</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm">
                        <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10개</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20개</option>
                        <option value="30" {{ request('per_page', 20) == 30 ? 'selected' : '' }}>30개</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50개</option>
                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100개</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>검색
                        </button>
                        <a href="{{ route('admin.posts', ['site' => $site->slug]) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise me-1"></i>초기화
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <hr class="my-3">
        
        @if(request()->hasAny(['board_id', 'author', 'date_from', 'date_to', 'views_min', 'views_max']))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <strong>필터 적용 중:</strong>
                @if(request('board_id'))
                    <span class="badge bg-primary me-1">게시판: {{ $boards->find(request('board_id'))->name ?? '' }}</span>
                @endif
                @if(request('author'))
                    <span class="badge bg-primary me-1">작성자: {{ request('author') }}</span>
                @endif
                @if(request('date_from') || request('date_to'))
                    <span class="badge bg-primary me-1">
                        날짜: {{ request('date_from') ?? '시작' }} ~ {{ request('date_to') ?? '종료' }}
                    </span>
                @endif
                @if(request('views_min') || request('views_max'))
                    <span class="badge bg-primary me-1">
                        조회수: {{ request('views_min') ?? '0' }} ~ {{ request('views_max') ?? '무제한' }}
                    </span>
                @endif
                <a href="{{ route('admin.posts', ['site' => $site->slug]) }}" class="btn btn-sm btn-outline-primary ms-2">필터 초기화</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($posts->count() > 0)
            <!-- PC 버전 테이블 -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>제목</th>
                            <th style="width: 120px;">게시판</th>
                            <th style="width: 120px;">작성자</th>
                            <th style="width: 100px;">
                                @php
                                    $viewsSortOrder = (request('sort_by') == 'views' && request('sort_order', 'desc') == 'desc') ? 'asc' : 'desc';
                                    $viewsParams = array_merge(request()->except(['page']), ['site' => $site->slug, 'sort_by' => 'views', 'sort_order' => $viewsSortOrder]);
                                @endphp
                                <a href="{{ route('admin.posts', $viewsParams) }}" class="text-decoration-none text-dark">
                                    조회수
                                    @if(request('sort_by') == 'views')
                                        <i class="bi bi-arrow-{{ request('sort_order', 'desc') == 'desc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 150px;">
                                @php
                                    $dateSortOrder = (request('sort_by', 'created_at') == 'created_at' && request('sort_order', 'desc') == 'desc') ? 'asc' : 'desc';
                                    $dateParams = array_merge(request()->except(['page']), ['site' => $site->slug, 'sort_by' => 'created_at', 'sort_order' => $dateSortOrder]);
                                @endphp
                                <a href="{{ route('admin.posts', $dateParams) }}" class="text-decoration-none text-dark">
                                    작성일
                                    @if(request('sort_by', 'created_at') == 'created_at')
                                        <i class="bi bi-arrow-{{ request('sort_order', 'desc') == 'desc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 150px;">작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                            <tr>
                                <td>{{ $post->id }}</td>
                                <td>
                                    @if($post->board)
                                        <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug, 'post' => $post->id]) }}" 
                                           class="text-decoration-none text-dark">
                                            {{ Str::limit($post->title, 50) }}
                                            @if($post->is_pinned)
                                                <span class="badge bg-warning text-dark">고정</span>
                                            @endif
                                            @if($post->is_notice)
                                                <span class="badge bg-info">공지</span>
                                            @endif
                                        </a>
                                    @else
                                        <span class="text-muted">
                                            {{ Str::limit($post->title, 50) }}
                                            @if($post->is_pinned)
                                                <span class="badge bg-warning text-dark">고정</span>
                                            @endif
                                            @if($post->is_notice)
                                                <span class="badge bg-info">공지</span>
                                            @endif
                                            <small class="text-danger">(삭제된 게시판)</small>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <select class="form-select form-select-sm board-select" 
                                            data-post-id="{{ $post->id }}" 
                                            data-current-board="{{ $post->board_id }}"
                                            style="min-width: 120px;">
                                        @foreach($boards as $board)
                                            <option value="{{ $board->id }}" {{ $post->board_id == $board->id ? 'selected' : '' }}>
                                                {{ $board->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <small>{{ $post->user->name ?? '알 수 없음' }}</small>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm" style="max-width: 120px;">
                                        <input type="number" 
                                               class="form-control views-input" 
                                               value="{{ $post->views }}" 
                                               min="0"
                                               data-post-id="{{ $post->id }}"
                                               style="text-align: right;">
                                        <span class="input-group-text">
                                            <i class="bi bi-eye"></i>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $post->created_at->format('Y-m-d H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if($post->board)
                                            <a href="{{ route('posts.edit', ['site' => $site->slug, 'boardSlug' => $post->board->slug, 'post' => $post->id]) }}" 
                                               class="btn btn-outline-primary btn-sm rounded" 
                                               title="수정"
                                               style="border-radius: 0.375rem !important;">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('posts.destroy', ['site' => $site->slug, 'boardSlug' => $post->board->slug, 'post' => $post->id]) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                        @else
                                            <button type="button" 
                                                    class="btn btn-outline-secondary btn-sm rounded" 
                                                    title="수정 불가 (게시판 삭제됨)"
                                                    disabled
                                                    style="border-radius: 0.375rem !important;">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="#" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="alert('게시판이 삭제되어 삭제할 수 없습니다.'); return false;">
                                        @endif
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger btn-sm rounded" 
                                                    title="삭제"
                                                    style="border-radius: 0.375rem !important;">
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

            <!-- 모바일 버전 카드 레이아웃 -->
            <div class="d-md-none">
                <div class="d-grid gap-2">
                    @foreach($posts as $post)
                        <div class="card border">
                            <div class="card-body p-3">
                                <!-- 헤더: ID와 배지 -->
                                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ $post->id }}</span>
                                        @if($post->is_pinned)
                                            <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">고정</span>
                                        @endif
                                        @if($post->is_notice)
                                            <span class="badge bg-info" style="font-size: 0.7rem;">공지</span>
                                        @endif
                                        @if(!$post->board)
                                            <span class="badge bg-danger" style="font-size: 0.7rem;">삭제된 게시판</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted">{{ $post->created_at->format('m-d H:i') }}</div>
                                </div>

                                <!-- 제목 -->
                                <div class="mb-2">
                                    @if($post->board)
                                        <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug, 'post' => $post->id]) }}" 
                                           class="text-decoration-none text-dark fw-bold d-block" style="font-size: 0.95rem; line-height: 1.4;">
                                            {{ Str::limit($post->title, 60) }}
                                        </a>
                                    @else
                                        <div class="text-muted fw-bold" style="font-size: 0.95rem; line-height: 1.4;">
                                            {{ Str::limit($post->title, 60) }}
                                            <small class="text-danger">(삭제된 게시판)</small>
                                        </div>
                                    @endif
                                </div>

                                <!-- 정보 그리드: 게시판, 작성자, 조회수 -->
                                <div class="row g-2 mb-2">
                                    <div class="col-12">
                                        <select class="form-select form-select-sm board-select-mobile" 
                                                data-post-id="{{ $post->id }}" 
                                                data-current-board="{{ $post->board_id }}"
                                                style="font-size: 0.85rem;">
                                            @foreach($boards as $board)
                                                <option value="{{ $board->id }}" {{ $post->board_id == $board->id ? 'selected' : '' }}>
                                                    {{ $board->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <div class="small text-muted" style="font-size: 0.75rem;">작성자</div>
                                        <div class="small fw-medium" style="font-size: 0.85rem;">{{ $post->user->name ?? '알 수 없음' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small text-muted mb-1" style="font-size: 0.75rem;">조회수</div>
                                        <div class="input-group input-group-sm">
                                            <input type="number" 
                                                   class="form-control views-input-mobile" 
                                                   value="{{ $post->views }}" 
                                                   min="0"
                                                   data-post-id="{{ $post->id }}"
                                                   style="text-align: right; font-size: 0.85rem; padding: 0.25rem 0.5rem;">
                                            <span class="input-group-text" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 작업 버튼 -->
                                <div class="d-flex gap-2 mt-2">
                                    @if($post->board)
                                        <a href="{{ route('posts.edit', ['site' => $site->slug, 'boardSlug' => $post->board->slug, 'post' => $post->id]) }}" 
                                           class="btn btn-outline-primary btn-sm flex-fill" style="font-size: 0.8rem; padding: 0.35rem 0.5rem;">
                                            <i class="bi bi-pencil"></i> 수정
                                        </a>
                                        <form action="{{ route('posts.destroy', ['site' => $site->slug, 'boardSlug' => $post->board->slug, 'post' => $post->id]) }}" 
                                              method="POST" 
                                              class="flex-fill"
                                              onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" style="font-size: 0.8rem; padding: 0.35rem 0.5rem;">
                                                <i class="bi bi-trash"></i> 삭제
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" 
                                                class="btn btn-outline-secondary btn-sm flex-fill" 
                                                disabled
                                                style="font-size: 0.8rem; padding: 0.35rem 0.5rem;">
                                            <i class="bi bi-pencil"></i> 수정 불가
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-secondary btn-sm flex-fill" 
                                                onclick="alert('게시판이 삭제되어 삭제할 수 없습니다.');"
                                                style="font-size: 0.8rem; padding: 0.35rem 0.5rem;">
                                            <i class="bi bi-trash"></i> 삭제 불가
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                @php
                    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                    $pointColor = $themeDarkMode === 'dark' 
                        ? $site->getSetting('color_dark_point_main', '#ffffff')
                        : $site->getSetting('color_light_point_main', '#0d6efd');
                @endphp
                {{ $posts->appends(request()->except('page'))->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3 mb-2">등록된 게시글이 없습니다</h4>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* 모바일 최적화 스타일 */
    @media (max-width: 767.98px) {
        /* 카드 간격 최소화 */
        .d-md-none .d-grid {
            gap: 0.75rem !important;
        }
        
        /* 카드 스타일 최적화 */
        .d-md-none .card {
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* 드롭다운 텍스트 잘림 방지 */
        .board-select-mobile {
            font-size: 0.85rem;
            padding: 0.375rem 0.5rem;
        }
        .board-select-mobile option {
            font-size: 0.85rem;
            padding: 0.5rem;
            white-space: normal;
            overflow: visible;
        }
        
        /* 입력 필드 최적화 */
        .views-input-mobile {
            font-size: 0.85rem !important;
        }
        
        /* 버튼 최적화 */
        .d-md-none .btn-sm {
            font-size: 0.8rem;
            padding: 0.35rem 0.5rem;
        }
        
        /* 배지 최적화 */
        .d-md-none .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 표시 개수 변경 시 자동 제출
    const perPageSelect = document.getElementById('per_page');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.querySelector('form').submit();
        });
    }

    // 게시판 변경 처리 (PC + 모바일)
    function handleBoardChange(select) {
        select.addEventListener('change', function() {
            const postId = this.dataset.postId;
            const boardId = this.value;
            const currentBoardId = this.dataset.currentBoard;

            if (boardId === currentBoardId) {
                return; // 변경사항 없음
            }

            // 변경 확인
            if (!confirm('게시판을 변경하시겠습니까?')) {
                this.value = currentBoardId;
                return;
            }

            // API 호출
            fetch(`/site/{{ $site->slug }}/admin/posts/${postId}/board`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    board_id: boardId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 성공 메시지 표시 (선택사항)
                    this.dataset.currentBoard = boardId;
                    // PC와 모바일 동기화
                    const allSelects = document.querySelectorAll(`.board-select[data-post-id="${postId}"], .board-select-mobile[data-post-id="${postId}"]`);
                    allSelects.forEach(s => {
                        s.value = boardId;
                        s.dataset.currentBoard = boardId;
                    });
                } else {
                    alert(data.message || '게시판 변경에 실패했습니다.');
                    this.value = currentBoardId;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('게시판 변경 중 오류가 발생했습니다.');
                this.value = currentBoardId;
            });
        });
    }

    document.querySelectorAll('.board-select').forEach(handleBoardChange);
    document.querySelectorAll('.board-select-mobile').forEach(handleBoardChange);

    // 조회수 변경 처리 (PC + 모바일)
    function handleViewsChange(input) {
        let originalValue = input.value;
        
        input.addEventListener('blur', function() {
            const postId = this.dataset.postId;
            const views = parseInt(this.value) || 0;

            if (views < 0) {
                alert('조회수는 0 이상이어야 합니다.');
                this.value = originalValue;
                return;
            }

            if (views === parseInt(originalValue)) {
                return; // 변경사항 없음
            }

            // API 호출
            fetch(`/site/{{ $site->slug }}/admin/posts/${postId}/views`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    views: views
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    originalValue = views;
                    // PC와 모바일 동기화
                    const allInputs = document.querySelectorAll(`.views-input[data-post-id="${postId}"], .views-input-mobile[data-post-id="${postId}"]`);
                    allInputs.forEach(inp => {
                        inp.value = views;
                    });
                } else {
                    alert(data.message || '조회수 변경에 실패했습니다.');
                    this.value = originalValue;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('조회수 변경 중 오류가 발생했습니다.');
                this.value = originalValue;
            });
        });

        // Enter 키 처리
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.blur();
            }
        });
    }

    document.querySelectorAll('.views-input').forEach(handleViewsChange);
    document.querySelectorAll('.views-input-mobile').forEach(handleViewsChange);
});
</script>
@endpush
