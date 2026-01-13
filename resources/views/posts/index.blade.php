@extends('layouts.app')

@section('title', $board->name . ' - 게시글 목록')

@section('content')
<div style="position: relative;">
    @php
        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
        $pointColor = $themeDarkMode === 'dark' 
            ? $site->getSetting('color_dark_point_main', '#ffffff')
            : $site->getSetting('color_light_point_main', '#0d6efd');
        
        // 조회수 공개 설정 (기본값: 공개)
        $showViews = $site->getSetting('show_views', '1') == '1';
        
        // 시각 표시 설정 (기본값: 표시)
        $showDatetime = $site->getSetting('show_datetime', '1') == '1';
        
        // 새글 기준 시간 (기본값: 24시간)
        $newPostHours = (int) $site->getSetting('new_post_hours', 24);
    @endphp
    <div class="mb-4">
        @if($board->header_image_path)
            <div class="mb-3">
                <img src="{{ asset('storage/' . $board->header_image_path) }}" alt="{{ $board->name }}" class="img-fluid rounded shadow-sm" style="width: 100%; height: auto;">
            </div>
        @endif
        @php
            $hideTitleDescription = $board->hide_title_description ?? false;
        @endphp
        @if(!$hideTitleDescription)
            <div class="bg-white p-3 rounded shadow-sm">
                <h2 class="mb-1"><i class="bi bi-file-text"></i> {{ $board->name }}</h2>
            </div>
        @endif
    </div>

    @if($board->topics()->count() > 0)
        <div class="mb-3">
            <div class="topic-filter-container d-flex gap-2 align-items-center" style="overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; scrollbar-width: thin;">
                <a href="{{ route('posts.index', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" 
                   class="btn btn-sm {{ !isset($topicId) ? 'btn-primary' : 'btn-outline-secondary' }}" 
                   style="flex-shrink: 0; {{ !isset($topicId) ? 'background-color: ' . $pointColor . '; border-color: ' . $pointColor . ';' : '' }}">
                    전체
                </a>
                @foreach($board->topics()->ordered()->get() as $topic)
                    <a href="{{ route('posts.index', ['site' => $site->slug, 'boardSlug' => $board->slug, 'topic' => $topic->id]) }}" 
                       class="btn btn-sm"
                       style="background-color: {{ (isset($topicId) && $topicId == $topic->id) ? $pointColor : 'transparent' }}; border-color: {{ $pointColor }}; color: {{ (isset($topicId) && $topicId == $topic->id) ? 'white' : $pointColor }}; flex-shrink: 0;">
                        {{ $topic->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

@if(request('search'))
    @php
        $searchTypeLabel = request('search_type', 'title_content') == 'author' ? '작성자' : '제목 또는 내용';
    @endphp
    <div class="alert alert-info mb-3">
        <i class="bi bi-search me-2"></i>
        <strong>{{ $searchTypeLabel }}</strong>에서 <strong>"{{ request('search') }}"</strong> 검색 결과: <strong>{{ $posts->total() }}</strong>개
        <a href="{{ route('posts.index', array_merge(['site' => $site->slug, 'boardSlug' => $board->slug], request()->except(['search', 'search_type', 'page']))) }}" 
           class="btn btn-sm btn-outline-primary ms-2">
            <i class="bi bi-x-lg me-1"></i>검색 초기화
        </a>
    </div>
@endif

@if($posts->count() > 0)
    @php
        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
        $pointColor = $themeDarkMode === 'dark' 
            ? $site->getSetting('color_dark_point_main', '#ffffff')
            : $site->getSetting('color_light_point_main', '#0d6efd');
    @endphp
    @if($board->type === 'pinterest')
        {{-- 핀터레스트 게시판 레이아웃 --}}
        @php
            // 디바이스별 컬럼 수 설정 (기본값)
            $mobileCols = $board->pinterest_columns_mobile ?? 2;
            $tabletCols = $board->pinterest_columns_tablet ?? 3;
            $desktopCols = $board->pinterest_columns_desktop ?? 4;
            $largeCols = $board->pinterest_columns_large ?? 6;
            
            // Bootstrap 컬럼 클래스 생성 (12를 컬럼 수로 나눔)
            $colClass = 'col-' . (12 / $mobileCols);
            if ($tabletCols > 0) {
                $colClass .= ' col-md-' . (12 / $tabletCols);
            }
            if ($desktopCols > 0) {
                $colClass .= ' col-lg-' . (12 / $desktopCols);
            }
            if ($largeCols > 0) {
                $colClass .= ' col-xl-' . (12 / $largeCols);
            }
        @endphp
        <div class="row g-3">
            @foreach($posts as $post)
                <div class="{{ $colClass }}">
                    <div class="card shadow-sm" style="overflow: hidden; border-radius: 12px;">
                        <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                           class="text-decoration-none text-dark">
                            {{-- 이미지 영역 --}}
                            @if($post->thumbnail_path)
                                <div class="position-relative" style="overflow: hidden; background-color: #f8f9fa;">
                                    <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                         alt="{{ $post->title }}" 
                                         class="img-fluid"
                                         style="width: 100%; height: auto; display: block; object-fit: cover; min-height: 150px;">
                                    {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                    <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                        @if($board->enable_likes && $post->like_count > 0)
                                            <span class="badge bg-dark bg-opacity-75">
                                                <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                            </span>
                                        @endif
                                        @if($post->comments->count() > 0)
                                            <span class="badge bg-dark bg-opacity-75">
                                                <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                @php
                                    // 게시글 내용에서 첫 번째 이미지 추출
                                    $content = $post->content;
                                    preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
                                    $firstImage = $matches[1] ?? null;
                                @endphp
                                @if($firstImage)
                                    <div class="position-relative" style="overflow: hidden;">
                                        <img src="{{ $firstImage }}" 
                                             alt="{{ $post->title }}" 
                                             class="img-fluid"
                                             style="width: 100%; height: auto; display: block;">
                                        {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                        <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                            @if($board->enable_likes && $post->like_count > 0)
                                                <span class="badge bg-dark bg-opacity-75">
                                                    <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                </span>
                                            @endif
                                            @if($post->comments->count() > 0)
                                                <span class="badge bg-dark bg-opacity-75">
                                                    <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="position-relative bg-secondary bg-opacity-25 d-flex flex-column align-items-center justify-content-center" 
                                         style="min-height: 150px;">
                                        <i class="bi bi-image display-4 text-muted mb-2"></i>
                                        <span class="text-muted small">No image</span>
                                        {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                        <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                            @if($board->enable_likes && $post->like_count > 0)
                                                <span class="badge bg-dark bg-opacity-75">
                                                    <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                </span>
                                            @endif
                                            @if($post->comments->count() > 0)
                                                <span class="badge bg-dark bg-opacity-75">
                                                    <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                            {{-- 핀터레스트 게시판 제목 표시 (pinterest_show_title이 true인 경우) --}}
                            @if($board->pinterest_show_title ?? false)
                                <div class="card-body p-2" style="background-color: rgba(255,255,255,0.95);">
                                    <h6 class="card-title mb-0 small text-truncate" style="font-size: 0.85rem; line-height: 1.3;">
                                        {{ $post->title }}
                                    </h6>
                                </div>
                            @endif
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($board->type === 'photo' || $board->type === 'blog' || ($board->type === 'event' && ($board->event_display_type ?? 'photo') === 'photo'))
        {{-- 사진 게시판 레이아웃 또는 이벤트 게시판 사진 타입 --}}
        <div class="row g-4">
            @foreach($posts as $post)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm" style="overflow: hidden;">
                        <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                           class="text-decoration-none text-dark">
                            {{-- 이벤트 게시판인 경우 상태 배너 표시 (상단 전체 너비) --}}
                            @if($board->type === 'event')
                                @php
                                    $eventStatus = $post->event_status ?? 'ongoing';
                                    $statusLabel = $eventStatus === 'ended' ? '종료' : '진행중';
                                    $statusBgColor = $eventStatus === 'ended' ? '#6c757d' : $pointColor;
                                @endphp
                                <div class="text-white text-center py-2" style="background-color: {{ $statusBgColor }}; font-weight: bold;">
                                    {{ $statusLabel }}
                                </div>
                            @endif
                            
                            {{-- 이미지 영역 --}}
                            @if($board->type === 'event')
                                {{-- 이벤트 게시판 이미지 영역 --}}
                                @php
                                    $isEventPhotoType = ($board->event_display_type ?? 'photo') === 'photo';
                                    $eventStatus = $post->event_status ?? 'ongoing';
                                @endphp
                                @if($isEventPhotoType)
                                    {{-- 사진형 이벤트 게시판: 썸네일만 표시 --}}
                                    @if($post->thumbnail_path)
                                        <div class="position-relative" style="overflow: hidden;">
                                            <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                                 alt="{{ $post->title }}" 
                                                 class="img-fluid"
                                                 style="width: 100%; height: auto; display: block; @if($eventStatus === 'ended') filter: brightness(0.4); @endif">
                                            @if($eventStatus === 'ended')
                                                {{-- 종료된 이벤트 오버레이 --}}
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                                                     style="background-color: rgba(0, 0, 0, 0.3); pointer-events: none;">
                                                    <span class="text-white fw-bold fs-5">종료된 이벤트</span>
                                                </div>
                                            @endif
                                            {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                            <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                                @if($board->enable_likes && $post->like_count > 0)
                                                    <span class="badge bg-dark bg-opacity-75">
                                                        <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                    </span>
                                                @endif
                                                @if($post->comments->count() > 0)
                                                    <span class="badge bg-dark bg-opacity-75">
                                                        <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        {{-- 이미지 없음 플레이스홀더 --}}
                                        <div class="position-relative bg-secondary bg-opacity-25 d-flex flex-column align-items-center justify-content-center" 
                                             style="min-height: 200px;">
                                            <i class="bi bi-image display-1 text-muted mb-2"></i>
                                            @if($eventStatus === 'ended')
                                                <span class="text-white fw-bold mb-1">종료된 이벤트</span>
                                            @endif
                                            <span class="text-muted small">No image</span>
                                            {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                            <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                                @if($board->enable_likes && $post->like_count > 0)
                                                    <span class="badge bg-dark bg-opacity-75">
                                                        <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                    </span>
                                                @endif
                                                @if($post->comments->count() > 0)
                                                    <span class="badge bg-dark bg-opacity-75">
                                                        <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    {{-- 일반형 이벤트 게시판: 기존 로직 유지 --}}
                                    @if($post->thumbnail_path)
                                        <div class="position-relative" style="height: 250px; overflow: hidden;">
                                            <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                                 alt="{{ $post->title }}" 
                                                 style="width: 100%; height: 100%; object-fit: cover; @if($eventStatus === 'ended') filter: brightness(0.4); @endif">
                                            @if($eventStatus === 'ended')
                                                {{-- 종료된 이벤트 오버레이 --}}
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                                                     style="background-color: rgba(0, 0, 0, 0.3);">
                                                    <span class="text-white fw-bold fs-5">종료된 이벤트</span>
                                                </div>
                                            @endif
                                            {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                            <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                                @if($board->enable_likes && $post->like_count > 0)
                                                    <span class="badge bg-dark bg-opacity-75">
                                                        <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                    </span>
                                                @endif
                                                @if($post->comments->count() > 0)
                                                    <span class="badge bg-dark bg-opacity-75">
                                                        <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            // 게시글 내용에서 첫 번째 이미지 추출
                                            $content = $post->content;
                                            preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
                                            $firstImage = $matches[1] ?? null;
                                        @endphp
                                        @if($firstImage)
                                            <div class="position-relative" style="height: 250px; overflow: hidden;">
                                                <img src="{{ $firstImage }}" 
                                                     alt="{{ $post->title }}" 
                                                     style="width: 100%; height: 100%; object-fit: cover; @if($eventStatus === 'ended') filter: brightness(0.4); @endif">
                                                @if($eventStatus === 'ended')
                                                    {{-- 종료된 이벤트 오버레이 --}}
                                                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                                                         style="background-color: rgba(0, 0, 0, 0.3);">
                                                        <span class="text-white fw-bold fs-5">종료된 이벤트</span>
                                                    </div>
                                                @endif
                                                {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                                <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                                    @if($board->enable_likes && $post->like_count > 0)
                                                        <span class="badge bg-dark bg-opacity-75">
                                                            <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                        </span>
                                                    @endif
                                                    @if($post->comments->count() > 0)
                                                        <span class="badge bg-dark bg-opacity-75">
                                                            <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            {{-- 이미지 없음 플레이스홀더 --}}
                                            <div class="position-relative bg-secondary bg-opacity-25 d-flex flex-column align-items-center justify-content-center" 
                                                 style="height: 250px;">
                                                <i class="bi bi-image display-1 text-muted mb-2"></i>
                                                @if($eventStatus === 'ended')
                                                    <span class="text-white fw-bold mb-1">종료된 이벤트</span>
                                                @endif
                                                <span class="text-muted small">No image</span>
                                                {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                                <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                                    @if($board->enable_likes && $post->like_count > 0)
                                                        <span class="badge bg-dark bg-opacity-75">
                                                            <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                        </span>
                                                    @endif
                                                    @if($post->comments->count() > 0)
                                                        <span class="badge bg-dark bg-opacity-75">
                                                            <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            @elseif($board->type === 'blog')
                                {{-- 블로그 게시판 이미지 영역 (사진 게시판과 동일) --}}
                                @if($post->thumbnail_path)
                                    <div class="card-img-top" style="padding: 1rem; background-color: #ffffff; display: flex; align-items: center; justify-content: center;">
                                        <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                             class="img-fluid" 
                                             alt="{{ $post->title }}" 
                                             style="max-width: 100%; max-height: 400px; width: auto; height: auto; object-fit: contain; display: block;">
                                    </div>
                                @else
                                    @php
                                        // 게시글 내용에서 첫 번째 이미지 추출
                                        $content = $post->content;
                                        preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
                                        $firstImage = $matches[1] ?? null;
                                    @endphp
                                    @if($firstImage)
                                        <div class="card-img-top" style="padding: 1rem; background-color: #ffffff; display: flex; align-items: center; justify-content: center;">
                                            <img src="{{ $firstImage }}" 
                                                 class="img-fluid" 
                                                 alt="{{ $post->title }}" 
                                                 style="max-width: 100%; max-height: 400px; width: auto; height: auto; object-fit: contain; display: block;">
                                        </div>
                                    @else
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                             style="min-height: 200px; padding: 1rem;">
                                            <i class="bi bi-image display-4 text-muted"></i>
                                        </div>
                                    @endif
                                @endif
                            @else
                                {{-- 일반 사진 게시판 이미지 영역 --}}
                                @if($post->thumbnail_path)
                                    <div class="card-img-top" style="padding: 1rem; background-color: #ffffff; display: flex; align-items: center; justify-content: center;">
                                        <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                             class="img-fluid" 
                                             alt="{{ $post->title }}" 
                                             style="max-width: 100%; max-height: 400px; width: auto; height: auto; object-fit: contain; display: block;">
                                    </div>
                                @else
                                    @php
                                        // 게시글 내용에서 첫 번째 이미지 추출
                                        $content = $post->content;
                                        preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
                                        $firstImage = $matches[1] ?? null;
                                    @endphp
                                    @if($firstImage)
                                        <div class="card-img-top" style="padding: 1rem; background-color: #ffffff; display: flex; align-items: center; justify-content: center;">
                                            <img src="{{ $firstImage }}" 
                                                 class="img-fluid" 
                                                 alt="{{ $post->title }}" 
                                                 style="max-width: 100%; max-height: 400px; width: auto; height: auto; object-fit: contain; display: block;">
                                        </div>
                                    @else
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                             style="min-height: 200px; padding: 1rem;">
                                            <i class="bi bi-image display-4 text-muted"></i>
                                        </div>
                                    @endif
                                @endif
                            @endif
                            
                            {{-- 이벤트 게시판인 경우 제목, 진행기간, 혜택 표시 --}}
                            @if($board->type === 'event')
                                @php
                                    $benefitItem = null;
                                    if ($post->bookmark_items && is_array($post->bookmark_items) && count($post->bookmark_items) > 0) {
                                        $benefitItem = $post->bookmark_items[0];
                                    }
                                @endphp
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-3">
                                        @php
                                            $isSecret = $board->force_secret || ($board->enable_secret && $post->is_secret);
                                            $canViewSecret = false;
                                            if ($isSecret && auth()->check()) {
                                                $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                            }
                                        @endphp
                                        @if($isSecret)
                                            @if($canViewSecret)
                                                <i class="bi bi-lock me-1"></i>{{ $post->title }}
                                            @else
                                                <i class="bi bi-lock me-1"></i>비밀 글입니다.
                                            @endif
                                        @else
                                            {{ $post->title }}
                                        @endif
                                    </h5>
                                    {{-- 진행 기간 표시 --}}
                                    @if($post->event_start_date || $post->event_end_date)
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                진행기간 : 
                                                @if($post->event_start_date && $post->event_end_date)
                                                    {{ $post->event_start_date->format('y.m.d') }} ~ {{ $post->event_end_date->format('y.m.d') }}
                                                @elseif($post->event_start_date)
                                                    {{ $post->event_start_date->format('y.m.d') }} ~
                                                @elseif($post->event_end_date)
                                                    ~ {{ $post->event_end_date->format('y.m.d') }}
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                    {{-- 혜택 항목 표시 (북마크처럼) --}}
                                    @if($benefitItem && !empty($benefitItem['name']) && !empty($benefitItem['value']))
                                        <div class="mt-2 pt-2 border-top">
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <span class="badge bg-secondary">{{ $benefitItem['name'] }}</span>
                                                <span class="text-muted small">{{ $benefitItem['value'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @elseif($board->type === 'blog')
                                {{-- 블로그 게시판 본문 --}}
                                <div class="card-body">
                                    <h5 class="card-title mb-2">
                                        @php
                                            $isSecret = $board->force_secret || ($board->enable_secret && $post->is_secret);
                                            $canViewSecret = false;
                                            if ($isSecret && auth()->check()) {
                                                $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                            }
                                        @endphp
                                        @if($isSecret)
                                            @if($canViewSecret)
                                                <i class="bi bi-lock me-1"></i>{{ Str::limit($post->title, 30) }}
                                            @else
                                                <i class="bi bi-lock me-1"></i>비밀 글입니다.
                                            @endif
                                        @else
                                            {{ Str::limit($post->title, 30) }}
                                        @endif
                                    </h5>
                                    @if($post->topics->count() > 0)
                                        <div class="mb-2">
                                            @foreach($post->topics as $topic)
                                                <span class="badge me-1" style="background-color: {{ $topic->color }}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem;">
                                                    {{ $topic->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    {{-- 제목과 내용 사이 구분선 --}}
                                    <hr class="my-2">
                                    {{-- 요약 내용 표시 --}}
                                    @if($post->content)
                                        @php
                                            $summaryLength = $board->summary_length ?? 150;
                                            $plainText = strip_tags($post->content);
                                            $summary = Str::limit($plainText, $summaryLength);
                                        @endphp
                                        <p class="card-text text-muted small mb-2" style="line-height: 1.6;">
                                            {{ $summary }}
                                        </p>
                                    @endif
                                    {{-- 내용 하단 구분선 및 작성자 정보 --}}
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            @if($board->enable_anonymous)
                                                <i class="bi bi-person"></i> 익명
                                            @else
                                                <x-user-rank :user="$post->user" :site="$site" />
                                                {{ $post->user->nickname ?? $post->user->name }}
                                            @endif
                                        </small>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($board->enable_likes && $post->like_count > 0)
                                                <small class="text-muted">
                                                    <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                </small>
                                            @endif
                                            <small class="text-muted">
                                                @if($showDatetime)
                                                    {{ $post->created_at->format('Y.m.d H:i') }}
                                                @else
                                                    {{ $post->created_at->format('Y.m.d') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- 일반 사진 게시판 본문 --}}
                                <div class="card-body">
                                    <h5 class="card-title mb-2">
                                        @php
                                            $isSecret = $board->force_secret || ($board->enable_secret && $post->is_secret);
                                            $canViewSecret = false;
                                            if ($isSecret && auth()->check()) {
                                                $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                            }
                                        @endphp
                                        @if($isSecret)
                                            @if($canViewSecret)
                                                <i class="bi bi-lock me-1"></i>{{ Str::limit($post->title, 30) }}
                                            @else
                                                <i class="bi bi-lock me-1"></i>비밀 글입니다.
                                            @endif
                                        @else
                                            {{ Str::limit($post->title, 30) }}
                                        @endif
                                    </h5>
                                    @if($post->topics->count() > 0)
                                        <div class="mb-2">
                                            @foreach($post->topics as $topic)
                                                <span class="badge me-1" style="background-color: {{ $topic->color }}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem;">
                                                    {{ $topic->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            @if($board->enable_anonymous)
                                                <i class="bi bi-person"></i> 익명
                                            @else
                                                <x-user-rank :user="$post->user" :site="$site" />
                                                {{ $post->user->nickname ?? $post->user->name }}
                                            @endif
                                        </small>
                                        <small class="text-muted">
                                            @if($showDatetime)
                                                {{ $post->created_at->format('Y.m.d H:i') }}
                                            @else
                                                {{ $post->created_at->format('Y.m.d') }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        @if($post->comments->count() > 0)
                                            <small class="text-primary">
                                                <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                            </small>
                                        @endif
                                        @if($showViews)
                                            <small class="text-muted">
                                                <i class="bi bi-eye"></i> {{ number_format($post->views) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($board->type === 'bookmark')
        {{-- 북마크 게시판 레이아웃 --}}
        <div class="row g-4">
            @foreach($posts as $post)
                @php
                    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                    $pointColor = $themeDarkMode === 'dark' 
                        ? $site->getSetting('color_dark_point_main', '#ffffff')
                        : $site->getSetting('color_light_point_main', '#0d6efd');
                    
                    $displayItems = [];
                    if ($post->bookmark_items && is_array($post->bookmark_items)) {
                        foreach (array_slice($post->bookmark_items, 0, 2) as $item) {
                            if (isset($item['name']) && isset($item['value']) && !empty($item['name']) && !empty($item['value'])) {
                                $displayItems[] = $item;
                            }
                        }
                    }
                @endphp
                <div class="col-12 col-md-6 col-lg-4 mb-3 mb-md-0">
                    <div class="card h-100 shadow-sm" style="overflow: hidden;">
                        {{-- 모바일: 가로 배치 --}}
                        <div class="d-md-none">
                            <div class="row g-0">
                                {{-- 왼쪽: 이미지 --}}
                                <div class="col-5 d-flex flex-column">
                                    @if($post->thumbnail_path)
                                        <div class="bookmark-thumbnail-container-mobile flex-grow-1" style="width: 100%; padding: 0.5rem; overflow: hidden; display: flex; align-items: center; justify-content: center; background-color: #ffffff; border-right: 1px solid #dee2e6;">
                                            <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                                 class="bookmark-thumbnail-mobile" 
                                                 alt="{{ $post->title }}" 
                                                 style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; display: block;">
                                        </div>
                                    @else
                                        <div class="bg-white d-flex align-items-center justify-content-center bookmark-thumbnail-placeholder-mobile flex-grow-1" 
                                             style="min-height: 150px; padding: 0.5rem; border-right: 1px solid #dee2e6;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                {{-- 오른쪽: 항목들 + 버튼 --}}
                                <div class="col-7 d-flex flex-column">
                                    <div class="bookmark-card-body-mobile flex-grow-1" style="padding: 0.5rem;">
                                        @if(count($displayItems) > 0)
                                            <div class="bookmark-items-mobile text-center">
                                                @foreach($displayItems as $item)
                                                    <div class="mb-2">
                                                        <div class="bookmark-item-name-mobile text-center" style="background-color: #f8f9fa; color: #6c757d; padding: 0.25rem 0.5rem; font-size: 0.75rem; border: 1px solid #dee2e6; border-bottom: none;">
                                                            {{ $item['name'] }}
                                                        </div>
                                                        <div class="bookmark-item-value-mobile text-center" style="background-color: white; padding: 0.25rem 0.5rem; font-size: 0.75rem; border: 1px solid #dee2e6; border-top: none;">
                                                            {{ $item['value'] }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="bookmark-card-footer-mobile" style="padding: 0.5rem; border-top: 1px solid #dee2e6;">
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                               class="btn btn-sm flex-fill bookmark-btn-detail-mobile"
                                               style="background-color: white; border-color: #000000; color: #000000; font-size: 0.75rem; padding: 0.25rem;">
                                                상세보기 +
                                            </a>
                                            @if($post->link)
                                                <a href="{{ $post->link }}" 
                                                   target="_blank" 
                                                   rel="noopener noreferrer"
                                                   class="btn btn-sm flex-fill text-white bookmark-btn-link-mobile"
                                                   style="background-color: {{ $pointColor }}; border-color: {{ $pointColor }}; font-size: 0.75rem; padding: 0.25rem;">
                                                    바로가기 <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-secondary flex-fill" disabled style="font-size: 0.75rem; padding: 0.25rem;">
                                                    바로가기
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- PC/태블릿: 세로 배치 --}}
                        <div class="d-none d-md-block">
                            @if($post->thumbnail_path)
                                <div class="bookmark-thumbnail-container" style="width: 100%; padding: 1rem; overflow: hidden; display: flex; align-items: center; justify-content: center; background-color: #ffffff; border-bottom: 1px solid #dee2e6;">
                                    <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                         class="card-img-top bookmark-thumbnail" 
                                         alt="{{ $post->title }}" 
                                         style="max-width: 100%; max-height: 400px; width: auto; height: auto; object-fit: contain; display: block;">
                                </div>
                            @else
                                <div class="card-img-top bg-white d-flex align-items-center justify-content-center bookmark-thumbnail-placeholder" 
                                     style="height: 200px; padding: 1rem; border-bottom: 1px solid #dee2e6;">
                                    <i class="bi bi-image display-4 text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body bookmark-card-body">
                                @if(count($displayItems) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0 bookmark-items-table">
                                            <tbody>
                                                @foreach($displayItems as $item)
                                                    <tr>
                                                        <th class="bookmark-item-name text-center">{{ $item['name'] }}</th>
                                                        <td class="bookmark-item-value text-center">{{ $item['value'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-white bookmark-card-footer" style="border-top: 1px solid #dee2e6;">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                       class="btn btn-sm flex-fill bookmark-btn-detail"
                                       style="border-color: #6c757d; color: #495057;">
                                        <i class="bi bi-info-circle"></i> 상세 +
                                    </a>
                                    @if($post->link)
                                        <a href="{{ $post->link }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="btn btn-sm flex-fill text-white bookmark-btn-link"
                                           style="background-color: {{ $pointColor }}; border-color: {{ $pointColor }};">
                                            바로가기 <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary flex-fill" disabled>
                                            바로가기
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($board->type === 'classic')
        {{-- 클래식 게시판 레이아웃 (테이블 형태) --}}
        {{-- 데스크톱 테이블 뷰 --}}
        <div class="card bg-white shadow-sm d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="text-align: center;">제목</th>
                        <th style="width: 120px; text-align: center;">작성자</th>
                        @if($showViews)
                        <th style="width: 100px; text-align: center;">조회수</th>
                        @endif
                        <th style="width: 150px; text-align: center;">작성일</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                        <tr>
                            <td style="text-align: left;">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                                        @if($post->is_pinned)
                                            <i class="bi bi-pin-angle-fill" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                        @elseif($post->is_notice)
                                            <i class="bi bi-megaphone" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                        @endif
                                        @if($post->topics->count() > 0)
                                            @foreach($post->topics as $topic)
                                                <span class="badge" style="background-color: {{ $topic->color }}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem;">
                                                    {{ $topic->name }}
                                                </span>
                                            @endforeach
                                        @endif
                                        <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                           class="text-decoration-none text-dark">
                                            @php
                                                $isSecret = $board->force_secret || ($board->enable_secret && $post->is_secret);
                                                $canViewSecret = false;
                                                if ($isSecret && auth()->check()) {
                                                    $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                                }
                                            @endphp
                                            @if($isSecret)
                                                @if($canViewSecret)
                                                    <i class="bi bi-lock me-1"></i>{{ $post->title }}
                                                @else
                                                    <i class="bi bi-lock me-1"></i>비밀 글입니다.
                                                @endif
                                            @else
                                                {{ $post->title }}
                                            @endif
                                            @if($post->comments->count() > 0)
                                                <span class="fw-bold" style="color: {{ $pointColor }};">+{{ $post->comments->count() }}</span>
                                            @endif
                                            @if($board->enable_likes && $post->like_count > 0)
                                                <span class="ms-1" style="color: #0d6efd;">
                                                    <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                </span>
                                            @endif
                                        </a>
                                    </div>
                                    @if($board->type === 'qa')
                                        @php
                                            $qaStatuses = $board->qa_statuses ?? [];
                                            $currentStatus = $post->qa_status ?? null;
                                            $statusColor = '#6c757d';
                                            
                                            // qa_status가 없으면 첫 번째 상태를 기본값으로 사용 (표시용)
                                            if (empty($currentStatus) && !empty($qaStatuses)) {
                                                $currentStatus = $qaStatuses[0]['name'] ?? '';
                                            }
                                            
                                            if ($currentStatus && !empty($qaStatuses)) {
                                                $statusInfo = collect($qaStatuses)->firstWhere('name', $currentStatus);
                                                if ($statusInfo) {
                                                    $statusColor = $statusInfo['color'] ?? '#6c757d';
                                                } else {
                                                    // 상태 이름이 qa_statuses에 없으면 첫 번째 상태의 색상 사용
                                                    $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                                }
                                            }
                                        @endphp
                                        @if(!empty($qaStatuses) && $currentStatus)
                                            <span class="badge ms-2" style="background-color: {{ $statusColor }}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; flex-shrink: 0;">
                                                {{ $currentStatus }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                @if($post->replies->count() > 0)
                                    <div class="mt-2 pt-2 border-top" style="font-size: 0.8rem;">
                                        <div class="text-muted">
                                            <i class="bi bi-reply"></i> 답글:
                                            @foreach($post->replies as $reply)
                                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $reply->id]) }}" 
                                                   class="text-decoration-none ms-1">
                                                    @if($board->enable_secret && $reply->is_secret)
                                                        @php
                                                            $canViewSecret = false;
                                                            if (auth()->check()) {
                                                                $canViewSecret = (auth()->id() === $reply->user_id || auth()->user()->canManage());
                                                            }
                                                        @endphp
                                                        @if($canViewSecret)
                                                            {{ Str::limit($reply->title, 30) }}
                                                        @else
                                                            비밀 글입니다.
                                                        @endif
                                                    @else
                                                        {{ Str::limit($reply->title, 30) }}
                                                    @endif
                                                </a>
                                                @if(!$loop->last)
                                                    <span class="text-muted">|</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($board->enable_anonymous)
                                    익명
                                @else
                                    @auth
                                        @if(auth()->id() !== $post->user_id)
                                            <div class="dropdown d-inline">
                                                <a class="text-decoration-none text-muted dropdown-toggle" href="#" role="button" id="postListUserDropdown{{ $post->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <x-user-rank :user="$post->user" :site="$site" />
                                                    {{ $post->user->nickname ?? $post->user->name }}
                                                </a>
                                                <ul class="dropdown-menu" aria-labelledby="postListUserDropdown{{ $post->id }}">
                                                    <li><a class="dropdown-item" href="#" onclick="openSendMessageModal({{ $post->user_id }}, '{{ $post->user->nickname ?? $post->user->name }}'); return false;">쪽지보내기</a></li>
                                                </ul>
                                            </div>
                                        @else
                                            <x-user-rank :user="$post->user" :site="$site" />
                                            {{ $post->user->nickname ?? $post->user->name }}
                                        @endif
                                    @else
                                        <x-user-rank :user="$post->user" :site="$site" />
                                        {{ $post->user->nickname ?? $post->user->name }}
                                    @endauth
                                @endif
                            </td>
                            @if($showViews)
                            <td style="text-align: center;"><i class="bi bi-eye"></i> {{ number_format($post->views) }}</td>
                            @endif
                            <td style="text-align: center;">
                                @if($showDatetime)
                                    {{ $post->created_at->format('Y-m-d H:i') }}
                                @else
                                    {{ $post->created_at->format('Y-m-d') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        
        {{-- 모바일 리스트 뷰 --}}
        <div class="card bg-white shadow-sm d-md-none">
            <div class="list-group list-group-flush">
                @foreach($posts as $post)
                    @php
                        // 게시글 내용에서 이미지가 있는지 확인
                        $hasImage = false;
                        if ($post->content) {
                            $hasImage = preg_match('/<img[^>]+>/i', $post->content);
                        }
                        // 새글 기준 확인 (설정된 시간 내 작성된 게시글)
                        $isNew = $post->created_at->isAfter(now()->subHours($newPostHours));
                    @endphp
                    <div class="list-group-item list-group-item-action border-start-0 border-end-0 {{ !$loop->last ? 'border-bottom' : '' }}" style="padding: 1rem;">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            @if($post->topics->count() > 0)
                                @foreach($post->topics as $topic)
                                    <span class="badge" style="background-color: {{ $topic->color }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0;">
                                        {{ $topic->name }}
                                    </span>
                                @endforeach
                            @endif
                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                               class="text-decoration-none text-dark flex-grow-1 d-flex align-items-center justify-content-between" style="line-height: 1.5;">
                                <!-- TEST MARKER: QA Board Mobile View -->
                                <span>
                                    @if($post->is_pinned)
                                        <i class="bi bi-pin-angle-fill me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                    @endif
                                    @if($post->is_notice)
                                        <i class="bi bi-megaphone me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                    @endif
                                    @php
                                        $isSecret = $board->force_secret || ($board->enable_secret && $post->is_secret);
                                        $canViewSecret = false;
                                        if ($isSecret && auth()->check()) {
                                            $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                        }
                                    @endphp
                                    @if($isSecret)
                                        @if($canViewSecret)
                                            <i class="bi bi-lock me-1"></i>{{ $post->title }}
                                        @else
                                            <i class="bi bi-lock me-1"></i>비밀 글입니다.
                                        @endif
                                    @else
                                        {{ $post->title }}
                                    @endif
                                    @if($post->comments->count() > 0)
                                        <span class="fw-bold ms-1" style="color: #495057;">+{{ $post->comments->count() }}</span>
                                    @endif
                                    @if($board->enable_likes && $post->like_count > 0)
                                        <span class="ms-1" style="color: #0d6efd;">
                                            <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                        </span>
                                    @endif
                                    @if($isNew)
                                        <span class="text-warning ms-1" style="font-size: 0.75rem; font-weight: bold;">N</span>
                                    @endif
                                    @if($hasImage)
                                        <i class="bi bi-image ms-1 text-muted" style="font-size: 0.875rem;"></i>
                                    @endif
                                </span>
                                @if($board->type === 'qa')
                                    @php
                                        $qaStatuses = $board->qa_statuses ?? [];
                                        $currentStatus = $post->qa_status ?? null;
                                        $statusColor = '#6c757d';
                                        
                                        // qa_status가 없으면 첫 번째 상태를 기본값으로 사용 (표시용)
                                        if (empty($currentStatus) && !empty($qaStatuses)) {
                                            $currentStatus = $qaStatuses[0]['name'] ?? '';
                                        }
                                        
                                        if ($currentStatus && !empty($qaStatuses)) {
                                            $statusInfo = collect($qaStatuses)->firstWhere('name', $currentStatus);
                                            if ($statusInfo) {
                                                $statusColor = $statusInfo['color'] ?? '#6c757d';
                                            } else {
                                                // 상태 이름이 qa_statuses에 없으면 첫 번째 상태의 색상 사용
                                                $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                            }
                                        }
                                    @endphp
                                    @if(!empty($qaStatuses) && $currentStatus)
                                        <span class="badge ms-2" style="background-color: {{ $statusColor }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0; white-space: nowrap;">
                                            {{ $currentStatus }}
                                        </span>
                                    @endif
                                @endif
                            </a>
                        </div>
                            <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.875rem;">
                                <span>
                                    @if($board->enable_anonymous)
                                        익명
                                    @else
                                        @auth
                                            @if(auth()->id() !== $post->user_id)
                                                <div class="dropdown d-inline">
                                                    <a class="text-decoration-none text-muted dropdown-toggle" href="#" role="button" id="postListUserDropdownMobile{{ $post->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <x-user-rank :user="$post->user" :site="$site" />
                                                        {{ $post->user->nickname ?? $post->user->name }}
                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="postListUserDropdownMobile{{ $post->id }}">
                                                        <li><a class="dropdown-item" href="#" onclick="openSendMessageModal({{ $post->user_id }}, '{{ $post->user->nickname ?? $post->user->name }}'); return false;">쪽지보내기</a></li>
                                                    </ul>
                                                </div>
                                            @else
                                                <x-user-rank :user="$post->user" :site="$site" />
                                                {{ $post->user->nickname ?? $post->user->name }}
                                            @endif
                                        @else
                                            <x-user-rank :user="$post->user" :site="$site" />
                                            {{ $post->user->nickname ?? $post->user->name }}
                                        @endauth
                                    @endif
                                </span>
                                @if($showViews)
                                <span>·</span>
                                <span>조회수 {{ number_format($post->views) }}</span>
                                @endif
                                <span>·</span>
                                <span>
                                    @if($showDatetime)
                                        {{ $post->created_at->format('Y.m.d H:i') }}
                                    @else
                                        {{ $post->created_at->format('Y.m.d') }}
                                    @endif
                                </span>
                            </div>
                            @if($post->replies->count() > 0)
                                <div class="mt-2 pt-2 border-top" style="font-size: 0.8rem;">
                                    <div class="text-muted">
                                        <i class="bi bi-reply"></i> 답글:
                                        @foreach($post->replies as $reply)
                                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $reply->id]) }}" 
                                               class="text-decoration-none ms-1">
                                                @php
                                                    $isReplySecret = $board->force_secret || ($board->enable_secret && $reply->is_secret);
                                                    $canViewReplySecret = false;
                                                    if ($isReplySecret && auth()->check()) {
                                                        $canViewReplySecret = (auth()->id() === $reply->user_id || auth()->user()->canManage());
                                                    }
                                                @endphp
                                                @if($isReplySecret)
                                                    @if($canViewReplySecret)
                                                        <i class="bi bi-lock me-1"></i>{{ Str::limit($reply->title, 30) }}
                                                    @else
                                                        <i class="bi bi-lock me-1"></i>비밀 글입니다.
                                                    @endif
                                                @else
                                                    {{ Str::limit($reply->title, 30) }}
                                                @endif
                                            </a>
                                            @if(!$loop->last)
                                                <span class="text-muted">|</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($board->type === 'qa')
        {{-- 질의응답 게시판 레이아웃 (배지 표시 포함) --}}
        <div class="card bg-white shadow-sm">
            <div class="list-group list-group-flush">
            @foreach($posts as $post)
                @php
                    // 게시글 내용에서 이미지가 있는지 확인
                    $hasImage = false;
                    if ($post->content) {
                        $hasImage = preg_match('/<img[^>]+>/i', $post->content);
                    }
                    // 오늘 작성된 게시글인지 확인
                    $isToday = $post->created_at->isToday();
                    
                    // QA 상태 정보 가져오기
                    $qaStatuses = is_array($board->qa_statuses) ? $board->qa_statuses : (is_string($board->qa_statuses) ? json_decode($board->qa_statuses, true) : []);
                    if (empty($qaStatuses) || !is_array($qaStatuses)) {
                        // qa_statuses가 없으면 기본값 설정
                        $qaStatuses = [
                            ['name' => '답변대기', 'color' => '#ffc107'],
                            ['name' => '답변완료', 'color' => '#28a745']
                        ];
                    }
                    
                    $currentStatus = $post->qa_status ?? null;
                    $statusColor = '#6c757d';
                    
                    // qa_status가 없으면 첫 번째 상태를 기본값으로 사용 (표시용)
                    if (empty($currentStatus) && !empty($qaStatuses) && isset($qaStatuses[0]['name'])) {
                        $currentStatus = $qaStatuses[0]['name'];
                    }
                    
                    // 색상 설정
                    if ($currentStatus && !empty($qaStatuses)) {
                        $statusInfo = collect($qaStatuses)->firstWhere('name', $currentStatus);
                        if ($statusInfo) {
                            $statusColor = $statusInfo['color'] ?? '#6c757d';
                        } else {
                            // 상태 이름이 qa_statuses에 없으면 첫 번째 상태의 색상 사용
                            $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                        }
                    } elseif (!empty($qaStatuses) && isset($qaStatuses[0]['color'])) {
                        // currentStatus가 없으면 첫 번째 상태의 색상 사용
                        $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                    }
                    
                    // 표시용 상태 및 색상
                    $displayStatus = $currentStatus;
                    $displayColor = $statusColor;
                    if (empty($displayStatus) && isset($qaStatuses[0]['name'])) {
                        $displayStatus = $qaStatuses[0]['name'];
                        $displayColor = $qaStatuses[0]['color'] ?? '#6c757d';
                    }
                @endphp
                <div class="list-group-item list-group-item-action border-start-0 border-end-0 {{ !$loop->last ? 'border-bottom' : '' }} position-relative" style="padding: 1rem;">
                    @auth
                        @if($board->saved_posts_enabled && \Illuminate\Support\Facades\Schema::hasTable('saved_posts'))
                            @php
                                $isSaved = in_array($post->id, $savedPostIds ?? []);
                            @endphp
                            <button type="button" 
                                    class="btn btn-link p-2 position-absolute" 
                                    style="bottom: 0.5rem; right: 0.5rem; z-index: 10; color: #6c757d; text-decoration: none;"
                                    onclick="toggleSave({{ $post->id }})"
                                    id="save-btn-{{ $post->id }}">
                                <i class="bi {{ $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' }}" style="font-size: 1.25rem;"></i>
                            </button>
                        @endif
                    @endauth
                    <div class="d-flex align-items-start gap-2 mb-2">
                        @if($post->topics->count() > 0)
                            @foreach($post->topics as $topic)
                                <span class="badge" style="background-color: {{ $topic->color }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0;">
                                    {{ $topic->name }}
                                </span>
                            @endforeach
                        @endif
                        <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                           class="text-decoration-none text-dark d-flex align-items-center justify-content-between flex-grow-1" style="line-height: 1.5;">
                                <span>
                                    @if($post->is_pinned)
                                        <i class="bi bi-pin-angle-fill me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                    @endif
                                    @if($post->is_notice)
                                        <i class="bi bi-megaphone me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                    @endif
                                    @php
                                        $isSecret = $board->force_secret || ($board->enable_secret && $post->is_secret);
                                        $canViewSecret = false;
                                        if ($isSecret && auth()->check()) {
                                            $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                        }
                                    @endphp
                                    @if($isSecret)
                                        @if($canViewSecret)
                                            <i class="bi bi-lock me-1"></i>{{ $post->title }}
                                        @else
                                            <i class="bi bi-lock me-1"></i>비밀 글입니다.
                                        @endif
                                    @else
                                        {{ $post->title }}
                                    @endif
                                    @if($post->comments->count() > 0)
                                        <span class="fw-bold ms-1" style="color: {{ $pointColor }};">+{{ $post->comments->count() }}</span>
                                    @endif
                                    @if($board->enable_likes && $post->like_count > 0)
                                        <span class="ms-1" style="color: #0d6efd;">
                                            <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                        </span>
                                    @endif
                                    @if($isToday)
                                        <span class="text-warning ms-1" style="font-size: 0.75rem; font-weight: bold;">N</span>
                                    @endif
                                    @if($hasImage)
                                        <i class="bi bi-image ms-1 text-muted" style="font-size: 0.875rem;"></i>
                                    @endif
                                </span>
                                @if(!empty($displayStatus))
                                    <span class="badge ms-auto" style="background-color: {{ $displayColor }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0; white-space: nowrap;">{{ $displayStatus }}</span>
                                @endif
                            </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.875rem;">
                        <span>
                            @if($board->enable_anonymous)
                                익명
                            @else
                                @auth
                                    @if(auth()->id() !== $post->user_id)
                                        <div class="dropdown d-inline">
                                            <a class="text-decoration-none text-muted dropdown-toggle" href="#" role="button" id="postListUserDropdownQa{{ $post->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <x-user-rank :user="$post->user" :site="$site" />
                                                {{ $post->user->nickname ?? $post->user->name }}
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="postListUserDropdownQa{{ $post->id }}">
                                                <li><a class="dropdown-item" href="#" onclick="openSendMessageModal({{ $post->user_id }}, '{{ $post->user->nickname ?? $post->user->name }}'); return false;">쪽지보내기</a></li>
                                            </ul>
                                        </div>
                                    @else
                                        <x-user-rank :user="$post->user" :site="$site" />
                                        {{ $post->user->nickname ?? $post->user->name }}
                                    @endif
                                @else
                                    <x-user-rank :user="$post->user" :site="$site" />
                                    {{ $post->user->nickname ?? $post->user->name }}
                                @endauth
                            @endif
                        </span>
                        <span>·</span>
                        <span>조회수 {{ number_format($post->views) }}</span>
                        <span>·</span>
                        <span>{{ $post->created_at->format('Y.m.d H:i') }}</span>
                    </div>
                    @if($post->replies->count() > 0)
                        <div class="mt-2 pt-2 border-top" style="font-size: 0.8rem;">
                            <div class="text-muted">
                                <i class="bi bi-reply"></i> 답글:
                                @foreach($post->replies as $reply)
                                    <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $reply->id]) }}" 
                                       class="text-decoration-none ms-1">
                                        @if($board->enable_secret && $reply->is_secret)
                                            @php
                                                $canViewSecret = false;
                                                if (auth()->check()) {
                                                    $canViewSecret = (auth()->id() === $reply->user_id || auth()->user()->canManage());
                                                }
                                            @endphp
                                            @if($canViewSecret)
                                                {{ Str::limit($reply->title, 30) }}
                                            @else
                                                비밀 글입니다.
                                            @endif
                                        @else
                                            {{ Str::limit($reply->title, 30) }}
                                        @endif
                                    </a>
                                    @if(!$loop->last)
                                        <span class="text-muted">|</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
            </div>
        </div>
        @else
        {{-- 일반 게시판 레이아웃 (심플 리스트 형태) 또는 이벤트 게시판 일반 타입 --}}
        <div class="card bg-white shadow-sm">
            <div class="list-group list-group-flush">
            @foreach($posts as $post)
                @php
                    // 게시글 내용에서 이미지가 있는지 확인
                    $hasImage = false;
                    if ($post->content) {
                        $hasImage = preg_match('/<img[^>]+>/i', $post->content);
                    }
                    // 오늘 작성된 게시글인지 확인
                    $isToday = $post->created_at->isToday();
                    // 이벤트 게시판이고 일반 타입인지 확인
                    $isEventGeneralType = $board->type === 'event' && ($board->event_display_type ?? 'photo') === 'general';
                @endphp
                <div class="list-group-item list-group-item-action border-start-0 border-end-0 {{ !$loop->last ? 'border-bottom' : '' }} position-relative" style="padding: 1rem;">
                    @auth
                        @if($board->saved_posts_enabled && \Illuminate\Support\Facades\Schema::hasTable('saved_posts'))
                            @php
                                $isSaved = in_array($post->id, $savedPostIds ?? []);
                            @endphp
                            <button type="button" 
                                    class="btn btn-link p-2 position-absolute" 
                                    style="bottom: 0.5rem; right: 0.5rem; z-index: 10; color: #6c757d; text-decoration: none;"
                                    onclick="toggleSave({{ $post->id }})"
                                    id="save-btn-{{ $post->id }}">
                                <i class="bi {{ $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' }}" style="font-size: 1.25rem;"></i>
                            </button>
                        @endif
                    @endauth
                    <div class="d-flex align-items-start gap-2 mb-2">
                        @if($post->topics->count() > 0)
                            @foreach($post->topics as $topic)
                                <span class="badge" style="background-color: {{ $topic->color }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0;">
                                    {{ $topic->name }}
                                </span>
                            @endforeach
                        @endif
                        <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                           class="text-decoration-none text-dark flex-grow-1" style="line-height: 1.5;">
                                <span>
                                    @if($post->is_pinned)
                                        <i class="bi bi-pin-angle-fill me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                    @endif
                                    @if($post->is_notice)
                                        <i class="bi bi-megaphone me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                    @endif
                                    {{-- 이벤트 게시판 일반 타입인 경우 상태 배지 표시 --}}
                                    @if($isEventGeneralType)
                                        @php
                                            $eventStatus = $post->event_status ?? 'ongoing';
                                            $statusLabel = $eventStatus === 'ended' ? '종료' : '진행중';
                                            $statusBgColor = $eventStatus === 'ended' ? '#6c757d' : $pointColor;
                                        @endphp
                                        <span class="badge me-1" style="background-color: {{ $statusBgColor }}; color: white;">{{ $statusLabel }}</span>
                                    @endif
                                    @php
                                        $isSecret = $board->force_secret || ($board->enable_secret && $post->is_secret);
                                        $canViewSecret = false;
                                        if ($isSecret && auth()->check()) {
                                            $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                        }
                                    @endphp
                                    @if($isSecret)
                                        @if($canViewSecret)
                                            <i class="bi bi-lock me-1"></i>{{ $post->title }}
                                        @else
                                            <i class="bi bi-lock me-1"></i>비밀 글입니다.
                                        @endif
                                    @else
                                        {{ $post->title }}
                                    @endif
                                    @if($post->comments->count() > 0)
                                        <span class="fw-bold ms-1" style="color: {{ $pointColor }};">+{{ $post->comments->count() }}</span>
                                    @endif
                                    @if($board->enable_likes && $post->like_count > 0)
                                        <span class="ms-1" style="color: #0d6efd;">
                                            <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                        </span>
                                    @endif
                                    @if($isToday)
                                        <span class="text-warning ms-1" style="font-size: 0.75rem; font-weight: bold;">N</span>
                                    @endif
                                    @if($hasImage)
                                        <i class="bi bi-image ms-1 text-muted" style="font-size: 0.875rem;"></i>
                                    @endif
                                </span>
                            </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.875rem;">
                        <span>
                            @if($board->enable_anonymous)
                                익명
                            @else
                                @auth
                                    @if(auth()->id() !== $post->user_id)
                                        <div class="dropdown d-inline">
                                            <a class="text-decoration-none text-muted dropdown-toggle" href="#" role="button" id="postListUserDropdownEvent{{ $post->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <x-user-rank :user="$post->user" :site="$site" />
                                                {{ $post->user->nickname ?? $post->user->name }}
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="postListUserDropdownEvent{{ $post->id }}">
                                                <li><a class="dropdown-item" href="#" onclick="openSendMessageModal({{ $post->user_id }}, '{{ $post->user->nickname ?? $post->user->name }}'); return false;">쪽지보내기</a></li>
                                            </ul>
                                        </div>
                                    @else
                                        <x-user-rank :user="$post->user" :site="$site" />
                                        {{ $post->user->nickname ?? $post->user->name }}
                                    @endif
                                @else
                                    <x-user-rank :user="$post->user" :site="$site" />
                                    {{ $post->user->nickname ?? $post->user->name }}
                                @endauth
                            @endif
                        </span>
                        <span>·</span>
                        <span>조회수 {{ number_format($post->views) }}</span>
                        <span>·</span>
                        <span>{{ $post->created_at->format('Y.m.d H:i') }}</span>
                        {{-- 이벤트 게시판 일반 타입인 경우 진행 기간 표시 --}}
                        @if($isEventGeneralType && ($post->event_start_date || $post->event_end_date))
                            <span>·</span>
                            <span>
                                @if($post->event_start_date && $post->event_end_date)
                                    {{ $post->event_start_date->format('Y.m.d') }} - {{ $post->event_end_date->format('Y.m.d') }}
                                @elseif($post->event_start_date)
                                    {{ $post->event_start_date->format('Y.m.d') }} ~
                                @elseif($post->event_end_date)
                                    ~ {{ $post->event_end_date->format('Y.m.d') }}
                                @endif
                            </span>
                        @endif
                    </div>
                    @if($post->replies->count() > 0)
                        <div class="mt-2 pt-2 border-top" style="font-size: 0.8rem;">
                            <div class="text-muted">
                                <i class="bi bi-reply"></i> 답글:
                                @foreach($post->replies as $reply)
                                    <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $reply->id]) }}" 
                                       class="text-decoration-none ms-1">
                                        @if($board->enable_secret && $reply->is_secret)
                                            @php
                                                $canViewSecret = false;
                                                if (auth()->check()) {
                                                    $canViewSecret = (auth()->id() === $reply->user_id || auth()->user()->canManage());
                                                }
                                            @endphp
                                            @if($canViewSecret)
                                                {{ Str::limit($reply->title, 30) }}
                                            @else
                                                비밀 글입니다.
                                            @endif
                                        @else
                                            {{ Str::limit($reply->title, 30) }}
                                        @endif
                                    </a>
                                    @if(!$loop->last)
                                        <span class="text-muted">|</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
            </div>
        </div>
    @endif
    
    @if($posts->hasPages())
        <!-- Pagination -->
        <div class="mt-4 mb-4">
            {{ $posts->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
        </div>
    @endif
    
    <!-- 검색 폼 -->
    <div class="mt-4 d-flex justify-content-center">
        <form method="GET" action="{{ route('posts.index', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="topic" value="{{ request('topic') }}">
            <select name="search_type" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                <option value="title_content" {{ request('search_type', 'title_content') == 'title_content' ? 'selected' : '' }}>제목 또는 내용</option>
                <option value="author" {{ request('search_type') == 'author' ? 'selected' : '' }}>작성자</option>
            </select>
            <input type="text" 
                   name="search" 
                   class="form-control form-control-sm" 
                   placeholder="검색어를 입력하세요..." 
                   value="{{ request('search') }}"
                   style="max-width: 300px;">
            <button type="submit" class="btn btn-sm btn-primary" style="background-color: {{ $pointColor }}; border-color: {{ $pointColor }}; height: 31px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-search"></i>
            </button>
            @if(request('search'))
                <a href="{{ route('posts.index', array_merge(['site' => $site->slug, 'boardSlug' => $board->slug], request()->except(['search', 'search_type', 'page']))) }}" 
                   class="btn btn-sm btn-outline-secondary" style="height: 31px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
    </div>
@else
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle"></i> 등록된 게시글이 없습니다.
        @auth
            <a href="{{ route('posts.create', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" 
               class="alert-link">첫 게시글을 작성해보세요!</a>
        @endauth
        </div>
    @endif

    {{-- 우측 하단 고정 글쓰기 버튼 --}}
    @php
        $canWrite = false;
        if (auth()->check()) {
            $writePermission = $board->write_permission ?? 'user';
            if ($writePermission === 'guest') {
                $canWrite = true;
            } elseif ($writePermission === 'user') {
                $canWrite = true;
            } elseif ($writePermission === 'admin') {
                $canWrite = auth()->user()->canManage();
            }
        } else {
            $writePermission = $board->write_permission ?? 'user';
            $canWrite = ($writePermission === 'guest');
        }
    @endphp
    @if($canWrite)
        <div class="text-end mt-4 mb-3">
            <a href="{{ route('posts.create', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" 
               class="btn btn-primary floating-write-btn">
                <i class="bi bi-pencil-square"></i> 글쓰기
            </a>
        </div>
    @endif

    {{-- 게시판 하단 내용 --}}
    @if($board->footer_content)
        <div class="mt-5 mb-4">
            <div class="bg-white p-4 rounded shadow-sm">
                {!! $board->footer_content !!}
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .floating-write-btn {
        border-radius: 50px;
        padding: 12px 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    /* 모바일: 가로 배치 스타일 */
    @media (max-width: 767.98px) {
        .bookmark-thumbnail-container-mobile {
            min-height: 150px;
        }
        
        .bookmark-thumbnail-mobile {
            max-height: 200px;
        }
        
        .bookmark-thumbnail-placeholder-mobile {
            min-height: 150px;
        }
        
        .floating-write-btn {
            padding: 10px 20px !important;
            font-size: 0.875rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function toggleSave(postId) {
    @auth
    const btn = document.getElementById('save-btn-' + postId);
    if (!btn) return;
    
    const icon = btn.querySelector('i');
    
    fetch('{{ route("posts.toggle-save", ["site" => $site->slug, "boardSlug" => $board->slug, "post" => ":postId"]) }}'.replace(':postId', postId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_saved) {
                icon.classList.remove('bi-bookmark');
                icon.classList.add('bi-bookmark-fill');
            } else {
                icon.classList.remove('bi-bookmark-fill');
                icon.classList.add('bi-bookmark');
            }
        } else {
            alert(data.message || '저장 처리 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 처리 중 오류가 발생했습니다.');
    });
    @else
    alert('로그인이 필요합니다.');
    @endauth
}

// 전역 쪽지 보내기 함수
window.openSendMessageModal = function(userId, userName) {
    // 쪽지 보내기 모달이 없으면 생성
    if (!document.getElementById('sendMessageModal')) {
        const modalHtml = `
            <div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sendMessageModalLabel">쪽지보내기</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="sendMessageForm">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="messageReceiver" class="form-label">받는사람</label>
                                    <input type="text" class="form-control" id="messageReceiver" readonly>
                                    <input type="hidden" id="messageReceiverId">
                                </div>
                                <div class="mb-3">
                                    <label for="messageContent" class="form-label">내용</label>
                                    <textarea class="form-control" id="messageContent" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                                <button type="submit" class="btn btn-primary">전송</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // 쪽지 전송 이벤트 리스너 추가
        document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const receiverId = document.getElementById('messageReceiverId').value;
            const content = document.getElementById('messageContent').value;
            
            if (!content.trim()) {
                alert('내용을 입력해주세요.');
                return;
            }
            
            fetch('{{ route("messages.store", ["site" => $site->slug]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    receiver_id: receiverId,
                    content: content,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('쪽지가 전송되었습니다.');
                    bootstrap.Modal.getInstance(document.getElementById('sendMessageModal')).hide();
                    document.getElementById('messageContent').value = '';
                } else {
                    alert(data.message || '쪽지 전송 중 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('쪽지 전송 중 오류가 발생했습니다.');
            });
        });
    }
    
    document.getElementById('messageReceiverId').value = userId;
    document.getElementById('messageReceiver').value = userName || '사용자';
    document.getElementById('messageContent').value = '';
    const modal = new bootstrap.Modal(document.getElementById('sendMessageModal'));
    modal.show();
};
</script>
@endpush
