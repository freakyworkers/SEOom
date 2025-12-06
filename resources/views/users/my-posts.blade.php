@extends('layouts.app')

@section('title', '내 게시글')

@section('content')
@php
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $pointColor = $themeDarkMode === 'dark' ? $site->getSetting('color_dark_point_main', '#ffffff') : $site->getSetting('color_light_point_main', '#0d6efd');
@endphp

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-file-text-fill me-2"></i>내 게시글
                    </h4>
                    <div>
                        <form method="GET" action="{{ route('users.my-posts', ['site' => $site->slug]) }}" class="d-inline">
                            <select name="board_id" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                <option value="">전체 게시판</option>
                                @foreach($boards as $board)
                                    <option value="{{ $board->id }}" {{ request('board_id') == $board->id ? 'selected' : '' }}>
                                        {{ $board->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if($posts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">번호</th>
                                    <th class="text-center">제목</th>
                                    <th style="width: 120px;" class="text-center">게시판</th>
                                    <th style="width: 100px;" class="text-center">조회수</th>
                                    <th style="width: 150px;" class="text-center">작성일</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($posts as $post)
                                    <tr>
                                        <td class="text-center">{{ $post->id }}</td>
                                        <td>
                                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug, 'post' => $post->id]) }}" class="text-decoration-none text-dark">
                                                {{ $post->title }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('boards.show', ['site' => $site->slug, 'slug' => $post->board->slug]) }}" class="text-decoration-none text-dark">
                                                {{ $post->board->name }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <i class="bi bi-eye me-1"></i>{{ number_format($post->views ?? 0) }}
                                        </td>
                                        <td class="text-center">{{ $post->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($posts->hasPages())
                        <div class="card-footer bg-white">
                            <div class="mt-4 mb-4">
                                {{ $posts->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-text" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="mt-3 text-muted">작성한 게시글이 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

