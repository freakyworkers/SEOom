@extends('layouts.app')

@section('title', '저장한 글')

@section('content')
@php
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $pointColor = $themeDarkMode === 'dark' ? $site->getSetting('color_dark_point_main', '#ffffff') : $site->getSetting('color_light_point_main', '#0d6efd');
@endphp

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h4 class="mb-0">
                    <i class="bi bi-bookmark-fill me-2"></i>저장한 글
                </h4>
            </div>
            <div class="card-body p-0">
                @if($savedPosts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($savedPosts as $savedPost)
                            @php
                                $post = $savedPost->post;
                                $board = $post->board ?? null;
                            @endphp
                            @if($post && $board)
                                <div class="list-group-item list-group-item-action border-start-0 border-end-0 {{ !$loop->last ? 'border-bottom' : '' }}" style="padding: 1rem;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="mb-2">
                                                <a href="{{ route('boards.show', ['site' => $site->slug, 'slug' => $board->slug]) }}" class="badge bg-secondary text-decoration-none me-2">
                                                    {{ $board->name }}
                                                </a>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i>{{ $savedPost->created_at->format('Y-m-d H:i') }}
                                                </small>
                                            </div>
                                            <h6 class="mb-2">
                                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" class="text-decoration-none text-dark">
                                                    {{ $post->title }}
                                                </a>
                                            </h6>
                                            <div class="text-muted small">
                                                <i class="bi bi-person me-1"></i>{{ $post->user->name ?? '익명' }}
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-eye me-1"></i>{{ number_format($post->views ?? 0) }}
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-calendar me-1"></i>{{ $post->created_at->format('Y-m-d H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @if($savedPosts->hasPages())
                        <div class="card-footer bg-white">
                            <div class="mt-4 mb-4">
                                {{ $savedPosts->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-bookmark" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="mt-3 text-muted">저장한 글이 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
