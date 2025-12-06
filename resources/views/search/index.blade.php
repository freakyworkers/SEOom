@extends('layouts.app')

@section('title', '검색')

@push('styles')
<style>
    mark {
        background-color: #fff3cd;
        padding: 2px 4px;
        border-radius: 3px;
        font-weight: 600;
    }
    .search-result-item {
        transition: all 0.2s;
    }
    .search-result-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
</style>
@endpush

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home', ['site' => $site->slug]) }}">홈</a></li>
        <li class="breadcrumb-item active">검색</li>
    </ol>
</nav>

<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="bi bi-search me-2"></i>검색</h4>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('search', ['site' => $site->slug]) }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       class="form-control" 
                       name="q" 
                       value="{{ $keyword }}" 
                       placeholder="검색어를 입력하세요..." 
                       required 
                       autofocus>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="type">
                    <option value="all" {{ $type === 'all' ? 'selected' : '' }}>전체</option>
                    <option value="posts" {{ $type === 'posts' ? 'selected' : '' }}>게시글</option>
                    <option value="users" {{ $type === 'users' ? 'selected' : '' }}>사용자</option>
                    <option value="boards" {{ $type === 'boards' ? 'selected' : '' }}>게시판</option>
                </select>
            </div>
            @if($type === 'posts' || $type === 'all')
            <div class="col-md-2">
                <select class="form-select" name="board_id">
                    <option value="">전체 게시판</option>
                    @foreach($allBoards as $board)
                        <option value="{{ $board->id }}" {{ $boardId == $board->id ? 'selected' : '' }}>
                            {{ $board->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="sort">
                    <option value="latest" {{ ($sortBy ?? 'latest') === 'latest' ? 'selected' : '' }}>최신순</option>
                    <option value="views" {{ ($sortBy ?? '') === 'views' ? 'selected' : '' }}>조회수순</option>
                    <option value="comments" {{ ($sortBy ?? '') === 'comments' ? 'selected' : '' }}>댓글순</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="date">
                    <option value="">전체 기간</option>
                    <option value="today" {{ ($dateFilter ?? '') === 'today' ? 'selected' : '' }}>오늘</option>
                    <option value="week" {{ ($dateFilter ?? '') === 'week' ? 'selected' : '' }}>최근 1주일</option>
                    <option value="month" {{ ($dateFilter ?? '') === 'month' ? 'selected' : '' }}>최근 1개월</option>
                    <option value="year" {{ ($dateFilter ?? '') === 'year' ? 'selected' : '' }}>최근 1년</option>
                </select>
            </div>
            @else
            <div class="col-md-2"></div>
            <div class="col-md-2"></div>
            @endif
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>검색
                </button>
            </div>
        </form>
    </div>
</div>

@if($keyword)
    @if($type === 'all' || $type === 'posts')
        <div class="card shadow mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-file-text me-2"></i>게시글 검색 결과
                    @if($posts)
                        <span class="badge bg-primary">{{ number_format($posts->total()) }}개</span>
                    @endif
                </h5>
                @if($posts && $posts->total() > 0)
                <div class="text-muted small">
                    "{{ $keyword }}"에 대한 검색 결과
                </div>
                @endif
            </div>
            <div class="card-body">
                @if($posts && $posts->count() > 0)
                    <div class="list-group">
                        @foreach($posts as $post)
                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug, 'post' => $post->id]) }}" 
                               class="list-group-item list-group-item-action search-result-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            @if($post->is_pinned)
                                                <span class="badge bg-warning text-dark me-1">고정</span>
                                            @endif
                                            @if($post->is_notice)
                                                <span class="badge bg-info me-1">공지</span>
                                            @endif
                                            {!! str_ireplace($keyword, '<mark>' . $keyword . '</mark>', e($post->title)) !!}
                                        </h6>
                                        <p class="mb-1 text-muted small">
                                            @php
                                                $content = strip_tags($post->content);
                                                $highlighted = app(\App\Services\SearchService::class)->highlightKeywords($content, $keyword, 150);
                                            @endphp
                                            {!! $highlighted !!}
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-person me-1"></i>{{ $post->user->name }}
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-grid me-1"></i>{{ $post->board->name }}
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-calendar me-1"></i>{{ $post->created_at->format('Y-m-d H:i') }}
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-eye me-1"></i>{{ number_format($post->views) }}
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-chat me-1"></i>{{ $post->comments->count() }}
                                        </small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        {{ $posts->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-search display-6"></i>
                        <p class="mt-2 mb-0">검색 결과가 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($type === 'all' || $type === 'users')
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>사용자 검색 결과
                    @if($users)
                        <span class="badge bg-primary">{{ $users->total() }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if($users && $users->count() > 0)
                    <div class="row">
                        @foreach($users as $user)
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    {!! str_ireplace($keyword, '<mark>' . $keyword . '</mark>', e($user->name)) !!}
                                                </h6>
                                                <p class="mb-1 text-muted small">
                                                    {!! str_ireplace($keyword, '<mark>' . $keyword . '</mark>', e($user->email)) !!}
                                                </p>
                                                <small class="text-muted">
                                                    <i class="bi bi-file-text me-1"></i>게시글 {{ $user->posts_count }}
                                                    <span class="mx-2">|</span>
                                                    <i class="bi bi-chat me-1"></i>댓글 {{ $user->comments_count }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-people display-6"></i>
                        <p class="mt-2 mb-0">검색 결과가 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($type === 'all' || $type === 'boards')
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-grid me-2"></i>게시판 검색 결과
                    @if($boards)
                        <span class="badge bg-primary">{{ $boards->total() }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if($boards && $boards->count() > 0)
                    <div class="list-group">
                        @foreach($boards as $board)
                            <a href="{{ route('posts.index', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            {!! str_ireplace($keyword, '<mark>' . $keyword . '</mark>', e($board->name)) !!}
                                        </h6>
                                        <p class="mb-1 text-muted small">
                                            {!! str_ireplace($keyword, '<mark>' . $keyword . '</mark>', e($board->description)) !!}
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-file-text me-1"></i>게시글 {{ $board->posts_count }}
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-calendar me-1"></i>{{ $board->created_at->format('Y-m-d') }}
                                        </small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        {{ $boards->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-grid display-6"></i>
                        <p class="mt-2 mb-0">검색 결과가 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
@else
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="bi bi-search display-1 text-muted"></i>
            <h4 class="mt-3 mb-2">검색어를 입력하세요</h4>
            <p class="text-muted">게시글, 사용자, 게시판을 검색할 수 있습니다.</p>
        </div>
    </div>
@endif
@endsection

