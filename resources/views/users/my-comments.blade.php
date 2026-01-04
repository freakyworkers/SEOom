@extends('layouts.app')

@section('title', '내 댓글')

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
                    <i class="bi bi-chat-dots-fill me-2"></i>내 댓글
                </h4>
            </div>
            <div class="card-body p-0">
                @if($comments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($comments as $comment)
                            @php
                                $post = $comment->post;
                                $board = $post->board ?? null;
                            @endphp
                            @if($post && $board)
                                <div class="list-group-item list-group-item-action border-start-0 border-end-0 {{ !$loop->last ? 'border-bottom' : '' }}" style="padding: 1rem;">
                                    <div class="d-flex flex-column">
                                        <div class="mb-2">
                                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" class="text-decoration-none">
                                                <h6 class="mb-1 text-dark fw-bold">{{ $post->title }}</h6>
                                            </a>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <a href="{{ route('boards.show', ['site' => $site->slug, 'slug' => $board->slug]) }}" class="text-decoration-none text-dark small">
                                                    {{ $board->name }}
                                                </a>
                                                <span class="text-muted small">|</span>
                                                <span class="text-muted small">{{ $comment->created_at->format('Y-m-d H:i') }}</span>
                                            </div>
                                        </div>
                                        <div class="bg-light p-3 rounded">
                                            <p class="mb-0 text-dark">{{ $comment->content }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @if($comments->hasPages())
                        <div class="card-footer bg-white">
                            <div class="mt-4 mb-4">
                                {{ $comments->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-chat-dots" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="mt-3 text-muted">작성한 댓글이 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection








