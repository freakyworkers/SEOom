@extends('layouts.app')

@section('title', '게시판')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="bg-white p-3 rounded shadow-sm">
        <h2 class="mb-1">게시판</h2>
        <p class="text-muted mb-0">원하는 게시판을 선택하세요</p>
    </div>
    @auth
        @if(auth()->user()->canManage())
            <div>
                <a href="{{ route('boards.create', ['site' => $site->slug]) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>게시판 만들기
                </a>
            </div>
        @endif
    @endauth
</div>

@if($boards->count() > 0)
    <div class="row">
        @foreach($boards as $board)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">
                                <a href="{{ route('boards.show', ['site' => $site->slug, 'slug' => $board->slug]) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ $board->name }}
                                </a>
                            </h5>
                            @if($board->is_active)
                                <span class="badge bg-success">활성</span>
                            @else
                                <span class="badge bg-secondary">비활성</span>
                            @endif
                        </div>
                        
                        @if($board->description)
                            <p class="card-text text-muted small">{{ Str::limit($board->description, 100) }}</p>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-file-text me-1"></i>
                                    <strong>{{ $board->activePosts()->count() }}</strong>개의 게시글
                                </small>
                            </div>
                            @auth
                                @if(auth()->user()->canManage())
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('boards.edit', ['site' => $site->slug, 'board' => $board->id]) }}" 
                                           class="btn btn-outline-secondary" title="수정">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="{{ route('boards.show', ['site' => $site->slug, 'slug' => $board->slug]) }}" 
                           class="btn btn-sm btn-outline-primary w-100">
                            게시판 보기 <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3 mb-2">등록된 게시판이 없습니다</h4>
            <p class="text-muted mb-4">첫 게시판을 만들어보세요!</p>
            @auth
                @if(auth()->user()->canManage())
                    <a href="{{ route('boards.create', ['site' => $site->slug]) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>게시판 만들기
                    </a>
                @endif
            @endauth
        </div>
    </div>
@endif
@endsection
