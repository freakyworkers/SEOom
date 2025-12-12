@extends('layouts.app')

@section('title', $post->title)

@section('content')
@php
    // 조회수 공개 설정 (기본값: 공개)
    $showViews = $site->getSetting('show_views', '1') == '1';
    
    // 시각 표시 설정 (기본값: 표시)
    $showDatetime = $site->getSetting('show_datetime', '1') == '1';
    
    // 포인트 컬러 설정
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $pointColor = $themeDarkMode === 'dark' 
        ? $site->getSetting('color_dark_point_main', '#ffffff')
        : $site->getSetting('color_light_point_main', '#0d6efd');
@endphp

<!-- Post Content -->
<div class="card mb-4 shadow-sm position-relative">
    <div class="card-header bg-white">
        <div class="d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1">
                    @if($post->topics->count() > 0)
                        <div class="mb-2">
                            @foreach($post->topics as $topic)
                                <span class="badge me-1" style="background-color: {{ $topic->color }}; color: white; padding: 4px 8px; border-radius: 4px;">
                                    {{ $topic->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    @if($board->type === 'qa' && $post->qa_status)
                        @php
                            $qaStatuses = $board->qa_statuses ?? [];
                            $statusInfo = collect($qaStatuses)->firstWhere('name', $post->qa_status);
                            $statusColor = $statusInfo['color'] ?? '#6c757d';
                        @endphp
                        <div class="mb-2">
                            <span class="badge" style="background-color: {{ $statusColor }}; color: white; padding: 4px 8px; border-radius: 4px;">
                                {{ $post->qa_status }}
                            </span>
                        </div>
                    @endif
                    <h4 class="mb-2">
                        @if($post->is_pinned)
                            <i class="bi bi-pin-angle-fill me-2" style="color: {{ $pointColor }}; font-size: 1.2em;"></i>
                        @endif
                        @if($post->is_notice)
                            <i class="bi bi-megaphone me-2" style="color: {{ $pointColor }}; font-size: 1.2em;"></i>
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
                    </h4>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    @if($board->enable_anonymous)
                        <i class="bi bi-person me-1"></i>익명
                    @else
                        @auth
                            @if(auth()->id() !== $post->user_id)
                                <div class="dropdown d-inline">
                                    <a class="text-decoration-none text-muted dropdown-toggle" href="#" role="button" id="postUserDropdown{{ $post->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <x-user-rank :user="$post->user" :site="$site" />
                                        {{ $post->user->nickname ?? $post->user->name }}
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="postUserDropdown{{ $post->id }}">
                                        <li><a class="dropdown-item" href="#" onclick="openReportModal('post', {{ $post->id }}, '{{ $site->slug }}'); return false;">신고하기</a></li>
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
                    <span class="mx-2">|</span>
                    <i class="bi bi-calendar me-1"></i>
                    @if($showDatetime)
                        {{ $post->created_at->format('Y-m-d H:i') }}
                    @else
                        {{ $post->created_at->format('Y-m-d') }}
                    @endif
                    @if($showViews)
                    <span class="mx-2">|</span>
                    <i class="bi bi-eye me-1"></i>{{ number_format($post->views) }}
                    @endif
                </div>
                @auth
                    @php
                        $canEdit = (auth()->id() === $post->user_id || auth()->user()->canManage());
                        $deletePermission = $board->delete_permission ?? 'author';
                        $canDelete = false;
                        if ($deletePermission === 'admin') {
                            $canDelete = auth()->user()->canManage();
                        } else {
                            $canDelete = (auth()->id() === $post->user_id || auth()->user()->canManage());
                        }
                    @endphp
                    @if($canEdit || $canDelete || ($board->type === 'event' && $post->isEventPost() && auth()->check() && auth()->user()->canManage() && !$post->event_is_ended) || ($board->type === 'qa' && auth()->check() && auth()->user()->canManage()))
                        <div class="d-flex gap-2">
                            @if($board->type === 'qa' && auth()->check() && auth()->user()->canManage())
                                @php
                                    $qaStatuses = $board->qa_statuses ?? [];
                                    $currentStatus = $post->qa_status;
                                    if (empty($currentStatus) && !empty($qaStatuses)) {
                                        $currentStatus = $qaStatuses[0]['name'] ?? '';
                                    }
                                @endphp
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="qaStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-tag"></i> 상태 변경
                                        @if($currentStatus)
                                            <span class="badge ms-1" style="background-color: {{ collect($qaStatuses)->firstWhere('name', $currentStatus)['color'] ?? '#6c757d' }};">
                                                {{ $currentStatus }}
                                            </span>
                                        @endif
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="qaStatusDropdown">
                                        @foreach($qaStatuses as $status)
                                            <li>
                                                <a class="dropdown-item qa-status-item {{ $currentStatus === $status['name'] ? 'active' : '' }}" 
                                                   href="#" 
                                                   data-status="{{ $status['name'] }}"
                                                   data-color="{{ $status['color'] }}"
                                                   onclick="updateQaStatus('{{ $status['name'] }}', '{{ $status['color'] }}'); return false;">
                                                    <span class="badge me-2" style="background-color: {{ $status['color'] }};">&nbsp;</span>
                                                    {{ $status['name'] }}
                                                    @if($currentStatus === $status['name'])
                                                        <i class="bi bi-check ms-2"></i>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if($canEdit)
                                <a href="{{ route('posts.edit', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                   class="btn btn-sm btn-outline-secondary post-action-btn d-md-inline-flex" style="border-radius: 0.375rem;">
                                    <i class="bi bi-pencil"></i> <span class="d-none d-md-inline ms-1">수정</span>
                                </a>
                            @endif
                            @if($canDelete)
                                <form action="{{ route('posts.destroy', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger post-action-btn d-md-inline-flex" style="border-radius: 0.375rem;">
                                        <i class="bi bi-trash"></i> <span class="d-none d-md-inline ms-1">삭제</span>
                                    </button>
                                </form>
                            @endif
                            @if($board->type === 'event' && $post->isEventPost() && auth()->check() && auth()->user()->canManage() && !$post->event_is_ended)
                                <form action="{{ route('posts.end-event', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('이벤트를 종료하시겠습니까? 종료된 이벤트는 리스트 하단으로 이동합니다.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning" style="border-radius: 0.375rem;">
                                        <i class="bi bi-stop-circle"></i> 종료
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($board->type === 'event' && $post->isEventPost() && ($board->event_display_type ?? 'photo') === 'photo')
            {{-- 사진형 이벤트 게시판 상세보기 레이아웃 --}}
            @php
                $eventStatus = $post->event_status ?? 'ongoing';
                $statusLabel = $eventStatus === 'ended' ? '종료된 이벤트' : '진행중 이벤트';
                $statusBgColor = $eventStatus === 'ended' ? '#6c757d' : $pointColor;
                $benefitItem = null;
                if ($post->bookmark_items && is_array($post->bookmark_items) && count($post->bookmark_items) > 0) {
                    $benefitItem = $post->bookmark_items[0];
                }
            @endphp
            
            <div class="row">
                {{-- 썸네일 이미지 (좌측) --}}
                <div class="col-md-6 mb-3 mb-md-0">
                    @if($post->thumbnail_path)
                        <div class="position-relative" style="overflow: hidden; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                            <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                 alt="{{ $post->title }}" 
                                 class="img-fluid"
                                 style="width: 100%; height: auto; display: block; @if($eventStatus === 'ended') filter: brightness(0.4); @endif">
                            @if($eventStatus === 'ended')
                                {{-- 종료된 이벤트 오버레이 --}}
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                                     style="background-color: rgba(0, 0, 0, 0.3); pointer-events: none;">
                                    <span class="text-white fw-bold fs-4">종료된 이벤트</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-secondary bg-opacity-25 d-flex flex-column align-items-center justify-content-center rounded" 
                             style="min-height: 300px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                            <i class="bi bi-image display-1 text-muted mb-2"></i>
                            @if($eventStatus === 'ended')
                                <span class="text-white fw-bold mb-1 fs-4">종료된 이벤트</span>
                            @endif
                            <span class="text-muted">No Image</span>
                        </div>
                    @endif
                </div>
                
                {{-- 이벤트 정보 (우측) --}}
                <div class="col-md-6 mb-3 mb-md-0 d-flex flex-column">
                    {{-- 진행중/종료된 이벤트 상단 표시 --}}
                    <div class="text-white text-center py-2 mb-3 rounded" style="background-color: {{ $statusBgColor }}; font-weight: bold; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                        {{ $statusLabel }}
                    </div>
                    
                    {{-- 진행기간 --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center" style="gap: 0;">
                            <span class="badge bg-dark d-flex align-items-center justify-content-center" style="min-width: 80px; height: 38px; font-size: 0.9rem; border-radius: 0.375rem 0 0 0.375rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">진행기간</span>
                            <div class="flex-grow-1 bg-white border rounded d-flex align-items-center" style="height: 38px; padding: 0 12px; font-size: 0.9rem; border-left: none; border-radius: 0 0.375rem 0.375rem 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                                @if($post->event_start_date && $post->event_end_date)
                                    {{ $post->event_start_date->format('y.m.d') }} ~ {{ $post->event_end_date->format('y.m.d') }}
                                @elseif($post->event_start_date)
                                    {{ $post->event_start_date->format('y.m.d') }} ~ 미정
                                @elseif($post->event_end_undecided)
                                    미정
                                @else
                                    미설정
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    {{-- 혜택 (항목 제목, 항목 내용 - 상하 배치) --}}
                    @if($benefitItem && !empty($benefitItem['name']) && !empty($benefitItem['value']))
                        <div class="flex-grow-1 d-flex flex-column">
                            <span class="badge bg-dark d-flex align-items-center justify-content-center" style="min-width: 80px; height: 38px; font-size: 0.9rem; border-radius: 0.375rem 0.375rem 0 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">{{ $benefitItem['name'] }}</span>
                            <div class="flex-grow-1 bg-white border rounded d-flex align-items-center justify-content-center" style="padding: 12px; font-size: 0.9rem; border-top: none; border-radius: 0 0 0.375rem 0.375rem; text-align: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                                {{ $benefitItem['value'] }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- 이벤트 내용 (기존 내용) --}}
            @if($post->content)
                <div class="mt-4 pt-4 border-top">
                    <div class="post-content">
                        {!! $post->content_with_links !!}
                    </div>
                </div>
            @endif
        @elseif($board->type === 'bookmark')
            {{-- 북마크 게시판 상세보기 레이아웃 --}}
            @php
                $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                $pointColor = $themeDarkMode === 'dark' 
                    ? $site->getSetting('color_dark_point_main', '#ffffff')
                    : $site->getSetting('color_light_point_main', '#0d6efd');
            @endphp
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    @if($post->thumbnail_path)
                        <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                             class="img-fluid rounded" 
                             alt="{{ $post->title }}" 
                             style="width: 100%; height: auto; max-height: 400px; object-fit: contain;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                             style="width: 100%; height: 300px;">
                            <i class="bi bi-image display-4 text-muted"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-8">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                @php
                                    $hasFirstItem = false;
                                @endphp
                                @if($post->bookmark_items && is_array($post->bookmark_items) && count($post->bookmark_items) > 0)
                                    @foreach($post->bookmark_items as $index => $item)
                                        @if(isset($item['name']) && isset($item['value']) && !empty($item['name']) && !empty($item['value']))
                                            <tr>
                                                <th style="width: 150px; background-color: #f8f9fa; color: #6c757d; font-weight: normal;">{{ $item['name'] }}</th>
                                                <td>{{ $item['value'] }}</td>
                                            </tr>
                                            @if($index === 0)
                                                @php $hasFirstItem = true; @endphp
                                            @endif
                                        @endif
                                        @if($index === 0 && $post->link)
                                            <tr>
                                                <th style="width: 150px; background-color: #f8f9fa; color: #6c757d; font-weight: normal;">링크</th>
                                                <td>
                                                    <a href="{{ $post->link }}" 
                                                       target="_blank" 
                                                       rel="noopener noreferrer"
                                                       class="text-decoration-none">
                                                        {{ $post->link }}
                                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                                @if($post->link && !$hasFirstItem)
                                    <tr>
                                        <th style="width: 150px; background-color: #f8f9fa; color: #6c757d; font-weight: normal;">링크</th>
                                        <td>
                                            <a href="{{ $post->link }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="text-decoration-none">
                                                {{ $post->link }}
                                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @if($post->content)
                <div class="mt-4 pt-4 border-top">
                    <h5 class="mb-3"><i class="bi bi-file-text me-2"></i>상세 내용</h5>
                    <div class="post-content">
                        {!! $post->content_with_links !!}
                    </div>
                </div>
            @endif
        @elseif($board->type === 'event' && $post->isEventPost() && ($board->event_display_type ?? 'photo') === 'general')
            {{-- 일반 타입 이벤트 게시판 상세보기 레이아웃 (썸네일 제외) --}}
            @php
                $eventStatus = $post->event_status ?? 'ongoing';
                $statusLabel = $eventStatus === 'ended' ? '종료된 이벤트' : '진행중 이벤트';
                $statusBgColor = $eventStatus === 'ended' ? '#6c757d' : $pointColor;
                $benefitItem = null;
                if ($post->bookmark_items && is_array($post->bookmark_items) && count($post->bookmark_items) > 0) {
                    $benefitItem = $post->bookmark_items[0];
                }
            @endphp
            
            <div class="mb-4">
                {{-- 진행중/종료된 이벤트 상단 표시 --}}
                <div class="text-white text-center py-2 mb-3 rounded" style="background-color: {{ $statusBgColor }}; font-weight: bold; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                    {{ $statusLabel }}
                </div>
                
                {{-- 진행기간 --}}
                <div class="mb-3">
                    <div class="d-flex align-items-center" style="gap: 0;">
                        <span class="badge bg-dark d-flex align-items-center justify-content-center" style="min-width: 80px; height: 38px; font-size: 0.9rem; border-radius: 0.375rem 0 0 0.375rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">진행기간</span>
                        <div class="flex-grow-1 bg-white border rounded d-flex align-items-center" style="height: 38px; padding: 0 12px; font-size: 0.9rem; border-left: none; border-radius: 0 0.375rem 0.375rem 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                            @if($post->event_start_date && $post->event_end_date)
                                {{ $post->event_start_date->format('y.m.d') }} ~ {{ $post->event_end_date->format('y.m.d') }}
                            @elseif($post->event_start_date)
                                {{ $post->event_start_date->format('y.m.d') }} ~ 미정
                            @elseif($post->event_end_undecided)
                                미정
                            @else
                                미설정
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- 혜택 (항목 제목, 항목 내용 - 상하 배치) --}}
                @if($benefitItem && !empty($benefitItem['name']) && !empty($benefitItem['value']))
                    <div class="mb-3">
                        <span class="badge bg-dark d-flex align-items-center justify-content-center" style="min-width: 80px; height: 38px; font-size: 0.9rem; border-radius: 0.375rem 0.375rem 0 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">{{ $benefitItem['name'] }}</span>
                        <div class="bg-white border rounded d-flex align-items-center justify-content-center" style="padding: 12px; font-size: 0.9rem; border-top: none; border-radius: 0 0 0.375rem 0.375rem; text-align: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                            {{ $benefitItem['value'] }}
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- 이벤트 내용 --}}
            @if($post->content)
                <div class="mt-4 pt-4 border-top">
                    <div class="post-content">
                        {!! $post->content_with_links !!}
                    </div>
                </div>
            @endif
        @else
            {{-- 일반 게시판 상세보기 레이아웃 --}}
            <div class="post-content" style="min-height: 200px;">
                {!! $post->content_with_links !!}
            </div>
        @endif
        
        @if($post->attachments->count() > 0)
            <div class="mt-4 pt-3 border-top">
                <h6 class="mb-3"><i class="bi bi-paperclip me-2"></i>첨부파일</h6>
                <div class="list-group">
                    @foreach($post->attachments as $attachment)
                        <a href="{{ $attachment->url }}" 
                           class="list-group-item list-group-item-action" 
                           target="_blank"
                           download="{{ $attachment->original_name }}">
                            <div class="d-flex align-items-center">
                                @if($attachment->isImage())
                                    <i class="bi bi-image me-2 text-primary"></i>
                                @else
                                    <i class="bi bi-file-earmark me-2 text-secondary"></i>
                                @endif
                                <div class="flex-grow-1">
                                    <strong>{{ $attachment->original_name }}</strong>
                                    <small class="text-muted d-block">
                                        {{ number_format($attachment->file_size / 1024, 2) }} KB
                                    </small>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 정답형 이벤트: 총 참여자 수 및 옵션 카드 (이벤트 정보 섹션 밖) --}}
        @if($board->type === 'event' && $post->isEventPost() && $post->event_type === 'quiz' && $post->eventOptions->count() > 0)
            @php
                $userParticipant = auth()->check() ? $post->eventParticipants->where('user_id', auth()->id())->first() : null;
                $canSeeAnswer = $post->event_status === 'ended' || (auth()->check() && (auth()->id() === $post->user_id || auth()->user()->canManage()));
                $userSelectedOption = $userParticipant ? $post->eventOptions->where('id', $userParticipant->event_option_id)->first() : null;
                $totalParticipants = $post->eventParticipants->count();
                $correctOption = $post->eventOptions->where('is_correct', true)->first();
            @endphp
            
            <div class="mt-4 pt-3 border-top">
                {{-- 총 참여자 수 표시 --}}
                <div class="text-center mb-4">
                    <i class="bi bi-people-fill me-2"></i>
                    <strong>현재까지 총 참여자: {{ number_format($totalParticipants) }}명</strong>
                </div>

                {{-- 옵션 카드들 (가로 배치) --}}
                <div class="row g-3 mb-4 justify-content-center">
                    @foreach($post->eventOptions as $option)
                        @php
                            $participantCount = $option->participant_count;
                            $percentage = $totalParticipants > 0 ? round(($participantCount / $totalParticipants) * 100, 2) : 0;
                            $isSelected = $userParticipant && $userParticipant->event_option_id == $option->id;
                            $showCorrect = $canSeeAnswer && $option->is_correct;
                        @endphp
                        <div class="col-md-4">
                            <div class="card h-100 quiz-option-card {{ $isSelected ? 'selected' : '' }}" 
                                 id="quiz-option-card-{{ $option->id }}"
                                 data-option-id="{{ $option->id }}"
                                 data-option-text="{{ $option->option_text }}"
                                 style="cursor: pointer; background: linear-gradient(to bottom, #ffffff, #f5f5f5); border: 1px solid #d0d0d0;">
                                <div class="card-body text-center p-4">
                                    <h5 class="card-title mb-3" style="color: {{ $pointColor }}; font-weight: bold;">
                                        {{ $option->option_text }}
                                    </h5>
                                    <p class="card-text mb-0" style="font-size: 0.9rem;">
                                        {{ number_format($participantCount) }}표 ({{ $percentage }}%)
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- 선택한 정답 표시 영역 --}}
                <div id="selected-answer-display" class="text-center mt-3 mb-3" style="display: none;">
                    <div class="mb-0">
                        <i class="bi bi-check-circle me-2"></i>
                        <span id="selected-answer-text"></span>을 선택하셨습니다.
                    </div>
                </div>

                {{-- 정답형 이벤트 참여 버튼 (진행 중일 때만) --}}
                @if($post->event_status === 'ongoing' || $post->event_status === 'upcoming')
                    @auth
                        @php
                            $hasParticipated = auth()->check() && $post->eventParticipants->where('user_id', auth()->id())->count() > 0;
                        @endphp
                        @if(!$hasParticipated)
                            {{-- 정답형 이벤트 참여 폼 --}}
                            <form method="POST" action="{{ route('posts.participate', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                  class="mt-3" 
                                  id="quiz-participate-form">
                                @csrf
                                <input type="hidden" name="event_option_id" id="selected-option-id" value="" required>
                                <div class="text-center">
                                    <button type="button" class="btn btn-primary btn-lg px-5" id="quiz-participate-btn" disabled>
                                        참여
                                    </button>
                                </div>
                            </form>

                            {{-- 확인 모달 --}}
                            <div class="modal fade" id="quiz-confirm-modal" tabindex="-1" aria-labelledby="quiz-confirm-modal-label" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="quiz-confirm-modal-label">이벤트 참여 확인</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p id="quiz-confirm-message"></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                                            <button type="button" class="btn btn-primary" id="quiz-confirm-submit">확인</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @if($userSelectedOption)
                                <div class="text-center mt-3 mb-3">
                                    <i class="bi bi-check-circle me-2"></i>
                                    "{{ $userSelectedOption->option_text }}"을 선택하셨습니다.
                                </div>
                            @else
                                <div class="text-center mt-3 mb-3">
                                    <i class="bi bi-info-circle me-2"></i>이미 이벤트에 참여하셨습니다.
                                </div>
                            @endif
                        @endif
                    @else
                        <div class="alert alert-warning mt-3 text-center">
                            <i class="bi bi-exclamation-triangle me-2"></i>이벤트에 참여하려면 
                            <a href="{{ route('login', ['site' => $site->slug]) }}" class="alert-link">로그인</a>이 필요합니다.
                        </div>
                    @endauth
                @endif

                {{-- 종료된 경우 정답 표시 --}}
                @if($post->event_status === 'ended' && $correctOption)
                    <div class="text-center mt-4">
                        <p class="mb-2">해당 이벤트는 종료되었습니다.</p>
                        <p class="mb-0">
                            최종 정답은 <span style="color: {{ $pointColor }}; font-weight: bold;">{{ $correctOption->option_text }}</span> 입니다.
                        </p>
                    </div>
                @endif

                {{-- 관리자용: 정답 선택 및 포인트 지급 (정답형 이벤트) --}}
                @if(auth()->check() && (auth()->id() === $post->user_id || auth()->user()->canManage()))
                    <div class="mt-4 pt-3 border-top">
                        <h5 class="mb-3">관리자 - 정답 선택 및 포인트 지급</h5>
                        
                        @if(!$correctOption)
                            <div class="mb-4 p-3 bg-warning bg-opacity-10 rounded">
                                <form method="POST" action="{{ route('posts.award-quiz-points', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                      id="quiz-award-points-form">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">정답 선택:</label>
                                        <div class="mt-2">
                                            @foreach($post->eventOptions as $option)
                                                @php
                                                    $optionParticipantCount = $option->participant_count;
                                                @endphp
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="correct_option_id" 
                                                           id="correct_option_{{ $option->id }}" 
                                                           value="{{ $option->id }}" 
                                                           required>
                                                    <label class="form-check-label" for="correct_option_{{ $option->id }}">
                                                        {{ $option->option_text }} ({{ $optionParticipantCount }}명)
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quiz_points_amount" class="form-label fw-bold">포인트 지급:</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="quiz_points_amount" 
                                                   name="points_amount" 
                                                   min="1" 
                                                   value="100" 
                                                   placeholder="포인트"
                                                   style="max-width: 150px;"
                                                   required>
                                            <button type="submit" class="btn btn-primary">
                                                지급하기
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="mb-3">
                                <span class="fw-bold">정답 : </span><span style="color: {{ $pointColor }}; font-weight: bold;">{{ $correctOption->option_text }}</span>
                            </div>
                        @endif

                        {{-- 선택지별 참여자 목록 --}}
                        <div class="row g-3">
                            @foreach($post->eventOptions as $option)
                                @php
                                    $optionParticipants = $post->eventParticipants->where('event_option_id', $option->id);
                                    $optionParticipantCount = $optionParticipants->count();
                                @endphp
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <strong>{{ $option->option_text }} ({{ $optionParticipantCount }}명)</strong>
                                            @if($option->is_correct)
                                                <span class="badge bg-success ms-2">정답</span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            @if($optionParticipantCount > 0)
                                                <div class="list-group list-group-flush">
                                                    @foreach($optionParticipants as $participant)
                                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i class="bi bi-person-circle text-primary"></i>
                                                                <span>{{ $participant->user->name ?? '알 수 없음' }}</span>
                                                                @if($participant->points_awarded > 0)
                                                                    <span class="badge bg-success">지급 완료</span>
                                                                @else
                                                                    <span class="badge bg-secondary">미지급</span>
                                                                @endif
                                                            </div>
                                                            @if($participant->points_awarded > 0)
                                                                <small class="text-muted">{{ number_format($participant->points_awarded) }}P</small>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-muted text-center py-3">
                                                    투표자가 없습니다.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- 신청형 이벤트: 신청 버튼 및 신청자 수 (이벤트 정보 섹션 밖) --}}
        @if($board->type === 'event' && $post->isEventPost() && $post->event_type === 'application')
            @php
                $participantCount = $post->eventParticipants->count();
                $hasParticipated = auth()->check() && $post->eventParticipants->where('user_id', auth()->id())->count() > 0;
            @endphp
            
            <div class="mt-4 pt-3 border-top">
                <div class="text-center">
                    @if($post->event_status === 'ongoing' || $post->event_status === 'upcoming')
                        @auth
                            @if(!$hasParticipated)
                                <form method="POST" action="{{ route('posts.participate', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                      class="mb-3" 
                                      id="application-participate-form">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-lg px-5" id="application-participate-btn">
                                        신청
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary btn-lg px-5 mb-3" disabled>
                                    신청 완료
                                </button>
                            @endif
                        @else
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>신청하려면 
                                <a href="{{ route('login', ['site' => $site->slug]) }}" class="alert-link">로그인</a>이 필요합니다.
                            </div>
                        @endauth
                    @else
                        @auth
                            @if($hasParticipated)
                                <button class="btn btn-secondary btn-lg px-5 mb-3" disabled>
                                    신청 완료
                                </button>
                            @endif
                        @endauth
                        <div class="alert alert-secondary mb-3">
                            <i class="bi bi-info-circle me-2"></i>이벤트가 종료되었습니다.
                        </div>
                    @endif
                    <div class="text-muted">
                        {{ $participantCount }}명 신청
                    </div>
                </div>
                
                {{-- 관리자용: 참가자 목록 및 포인트 지급 --}}
                @if(auth()->check() && (auth()->id() === $post->user_id || auth()->user()->canManage()))
                    <div class="mt-4 pt-3 border-top">
                        <h5 class="mb-3">관리자 - 참가자 목록</h5>
                        
                        {{-- 포인트 지급 폼 --}}
                        @if($participantCount > 0)
                            <div class="mb-4 p-3 bg-warning bg-opacity-10 rounded">
                                <form method="POST" action="{{ route('posts.award-points', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                      id="award-points-form">
                                    @csrf
                                    <div class="d-flex align-items-center gap-3">
                                        <label for="points_amount" class="form-label mb-0 fw-bold">포인트 지급:</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="points_amount" 
                                               name="points_amount" 
                                               min="1" 
                                               value="100" 
                                               placeholder="포인트"
                                               style="max-width: 150px;"
                                               required>
                                        <input type="hidden" name="target" value="all">
                                        <button type="submit" class="btn btn-secondary">
                                            지급하기
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                        
                        {{-- 신청자 리스트 --}}
                        @if($participantCount > 0)
                            <div class="mb-3">
                                <h6 class="mb-3">신청자 ({{ $participantCount }}명)</h6>
                                <div class="list-group">
                                    @foreach($post->eventParticipants as $participant)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-person-circle text-primary"></i>
                                                <span>{{ $participant->user->name ?? '알 수 없음' }}</span>
                                                @if($participant->points_awarded > 0)
                                                    <span class="badge bg-success">지급 완료</span>
                                                @else
                                                    <span class="badge bg-secondary">미지급</span>
                                                @endif
                                            </div>
                                            @if($participant->points_awarded > 0)
                                                <small class="text-muted">{{ number_format($participant->points_awarded) }}P</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-muted text-center py-3">
                                아직 신청자가 없습니다.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
        
        @php
            // enable_share 컬럼이 있는지 확인
            $hasEnableShareColumn = \Illuminate\Support\Facades\Schema::hasColumn('boards', 'enable_share');
            // 컬럼이 있으면 해당 값을 사용, 없으면 기본값 true (하위 호환성)
            $showShare = $hasEnableShareColumn 
                ? ($board->enable_share === true || $board->enable_share === 1) 
                : true;
        @endphp
        
        @if($board->enable_likes || ($board->saved_posts_enabled && \Illuminate\Support\Facades\Schema::hasTable('saved_posts') && auth()->check()) || $showShare)
            {{-- 추천/비추천 버튼, 저장하기 버튼, 공유 버튼 --}}
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    {{-- 왼쪽: 추천/비추천 버튼 --}}
                    <div class="d-flex align-items-center gap-3">
                        @if($board->enable_likes)
                            @auth
                                @php
                                    $userLike = $post->hasUserLike(auth()->id());
                                    $isLiked = $userLike && $userLike->type === 'like';
                                    $isDisliked = $userLike && $userLike->type === 'dislike';
                                @endphp
                                <button type="button" 
                                        class="btn btn-outline-primary like-btn {{ $isLiked ? 'active' : '' }}" 
                                        data-post-id="{{ $post->id }}"
                                        data-type="like"
                                        style="{{ $isLiked ? 'background-color: #0d6efd; color: white; border-color: #0d6efd;' : '' }}">
                                    <i class="bi bi-hand-thumbs-up{{ $isLiked ? '-fill' : '' }}"></i>
                                    <span class="like-count">{{ $post->like_count }}</span>
                                </button>
                                <button type="button" 
                                        class="btn btn-outline-danger dislike-btn {{ $isDisliked ? 'active' : '' }}" 
                                        data-post-id="{{ $post->id }}"
                                        data-type="dislike"
                                        style="{{ $isDisliked ? 'background-color: #dc3545; color: white; border-color: #dc3545;' : '' }}">
                                    <i class="bi bi-hand-thumbs-down{{ $isDisliked ? '-fill' : '' }}"></i>
                                    <span class="dislike-count">{{ $post->dislike_count }}</span>
                                </button>
                            @else
                                <button type="button" class="btn btn-outline-primary" disabled>
                                    <i class="bi bi-hand-thumbs-up"></i>
                                    <span class="like-count">{{ $post->like_count }}</span>
                                </button>
                                <button type="button" class="btn btn-outline-danger" disabled>
                                    <i class="bi bi-hand-thumbs-down"></i>
                                    <span class="dislike-count">{{ $post->dislike_count }}</span>
                                </button>
                                <small class="text-muted">로그인 후 추천할 수 있습니다.</small>
                            @endauth
                        @endif
                    </div>
                    
                    {{-- 오른쪽: 저장하기 버튼 및 공유 버튼 --}}
                    <div class="d-flex align-items-center gap-2">
                        @auth
                            @if($board->saved_posts_enabled && \Illuminate\Support\Facades\Schema::hasTable('saved_posts'))
                                @php
                                    $isSaved = $isSaved ?? $post->isSavedByUser(auth()->id());
                                @endphp
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        onclick="toggleSave({{ $post->id }})"
                                        id="save-btn-{{ $post->id }}">
                                    <i class="bi {{ $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                                </button>
                            @endif
                        @endauth
                        
                        @if($showShare)
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="shareButton">
                                <i class="bi bi-share"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        
        @if($board->enable_reply)
            <div class="mt-4 pt-3 border-top">
                <div class="text-end">
                    <a href="{{ route('posts.create', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}?reply_to={{ $post->id }}" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-reply me-1"></i>답글 작성
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

@if($board->prevent_drag)
<style>
    body {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
    }
</style>
@endif

@php
    // enable_comments 컬럼이 있는지 확인
    $hasEnableCommentsColumn = \Illuminate\Support\Facades\Schema::hasColumn('boards', 'enable_comments');
    // 컬럼이 있으면 해당 값을 사용, 없으면 기본값 true (하위 호환성)
    $showComments = $hasEnableCommentsColumn 
        ? ($board->enable_comments === true || $board->enable_comments === 1) 
        : true;
@endphp

@if($showComments)
<!-- Comments Section -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-chat-dots me-2"></i>댓글 
            <span class="badge bg-primary">{{ $post->comments->count() }}</span>
        </h5>
    </div>
    <div class="card-body">
        @auth
            <form method="POST" action="{{ route('comments.store', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" class="mb-4">
                @csrf
                <div class="mb-3">
                    <textarea class="form-control @error('content') is-invalid @enderror" 
                              name="content" 
                              rows="3" 
                              placeholder="댓글을 입력하세요..." 
                              required>{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>댓글 작성
                    </button>
                </div>
            </form>
        @else
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>댓글을 작성하려면 
                <a href="{{ route('login', ['site' => $site->slug]) }}" class="alert-link">로그인</a>이 필요합니다.
            </div>
        @endauth

        @if($post->topLevelComments()->count() > 0)
            <div class="comments-list">
                @foreach($post->topLevelComments as $comment)
                    @include('comments.item', ['comment' => $comment, 'depth' => 0, 'site' => $site, 'boardSlug' => $board->slug, 'board' => $board, 'showDatetime' => $showDatetime])
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-4">
                <i class="bi bi-chat display-6"></i>
                <p class="mt-2 mb-0">첫 댓글을 작성해보세요!</p>
            </div>
        @endif
    </div>
</div>
@endif

<div class="mt-4">
    <a href="{{ route('boards.show', ['site' => $site->slug, 'slug' => $board->slug]) }}" 
       class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i>목록으로
    </a>
</div>

{{-- 배너 영역 (나중에 추가 예정) --}}
{{-- <div class="mt-5 mb-4">
    <div class="banner-area">
        <!-- 배너 내용 -->
    </div>
</div> --}}

{{-- 게시판 게시글 리스트 --}}
@if(isset($boardPosts) && $boardPosts->count() > 0)
    @php
        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
        $pointColor = $themeDarkMode === 'dark' 
            ? $site->getSetting('color_dark_point_main', '#ffffff')
            : $site->getSetting('color_light_point_main', '#0d6efd');
        // posts/index.blade.php와 동일한 변수명 사용
        $posts = $boardPosts;
    @endphp
    
    <div class="mt-5 mb-4">
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
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($board->type === 'photo' || ($board->type === 'event' && ($board->event_display_type ?? 'photo') === 'photo'))
            {{-- 사진 게시판 레이아웃 또는 사진형 이벤트 게시판 --}}
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
                                    @endif
                                @else
                                    {{-- 일반 사진 게시판 이미지 영역 --}}
                                    @if($post->thumbnail_path)
                                        <div class="position-relative" style="overflow: hidden;">
                                            <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                                 alt="{{ $post->title }}" 
                                                 class="img-fluid"
                                                 style="width: 100%; height: auto; display: block;">
                                        </div>
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="min-height: 200px;">
                                            <i class="bi bi-image display-1 text-muted"></i>
                                        </div>
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
                                @else
                                    {{-- 일반 사진 게시판 제목 --}}
                                    <div class="card-body">
                                        <h6 class="card-title text-dark mb-0">{{ Str::limit($post->title, 30) }}</h6>
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
                                                <i class="bi bi-eye"></i> {{ number_format($post->views) }}
                                            </small>
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
                                        {{-- 제목 표시 (썸네일 하단) --}}
                                        <div class="px-2 py-1 text-center" style="border-top: 1px solid #dee2e6; border-right: 1px solid #dee2e6; background-color: #f8f9fa;">
                                            <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; line-height: 1.3;">{{ Str::limit($post->title, 20) }}</h6>
                                        </div>
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
                                {{-- 제목 표시 (썸네일 하단) --}}
                                <div class="px-3 pt-2 pb-2 text-center" style="border-bottom: 1px solid #dee2e6;">
                                    <h6 class="mb-0 fw-bold" style="font-size: 0.95rem; line-height: 1.4;">{{ Str::limit($post->title, 50) }}</h6>
                                </div>
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
                                @php
                                    // 질의응답 게시판인 경우 QA 상태 정보 가져오기
                                    $isQaBoard = $board->type === 'qa';
                                    $qaStatuses = [];
                                    $currentStatus = null;
                                    $statusColor = '#6c757d';
                                    $displayStatus = null;
                                    $displayColor = '#6c757d';
                                    
                                    if ($isQaBoard) {
                                        $qaStatuses = is_array($board->qa_statuses) ? $board->qa_statuses : (is_string($board->qa_statuses) ? json_decode($board->qa_statuses, true) : []);
                                        if (empty($qaStatuses) || !is_array($qaStatuses)) {
                                            $qaStatuses = [
                                                ['name' => '답변대기', 'color' => '#ffc107'],
                                                ['name' => '답변완료', 'color' => '#28a745']
                                            ];
                                        }
                                        
                                        $currentStatus = $post->qa_status ?? null;
                                        
                                        if (empty($currentStatus) && !empty($qaStatuses) && isset($qaStatuses[0]['name'])) {
                                            $currentStatus = $qaStatuses[0]['name'];
                                        }
                                        
                                        if ($currentStatus && !empty($qaStatuses)) {
                                            $statusInfo = collect($qaStatuses)->firstWhere('name', $currentStatus);
                                            if ($statusInfo) {
                                                $statusColor = $statusInfo['color'] ?? '#6c757d';
                                            } else {
                                                $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                            }
                                        } elseif (!empty($qaStatuses) && isset($qaStatuses[0]['color'])) {
                                            $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                        }
                                        
                                        $displayStatus = $currentStatus;
                                        $displayColor = $statusColor;
                                        if (empty($displayStatus) && isset($qaStatuses[0]['name'])) {
                                            $displayStatus = $qaStatuses[0]['name'];
                                            $displayColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td style="text-align: left;">
                                        <div class="d-flex align-items-center gap-2">
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
                                               class="text-decoration-none text-dark d-flex align-items-center gap-2">
                                                <span>
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
                                                </span>
                                                @if($isQaBoard && !empty($qaStatuses) && !empty($displayStatus))
                                                    <span class="badge" style="background-color: {{ $displayColor }}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; flex-shrink: 0; white-space: nowrap;">{{ $displayStatus }}</span>
                                                @endif
                                            </a>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        @if($board->enable_anonymous)
                                            익명
                                        @else
                                            <x-user-rank :user="$post->user" :site="$site" />
                                            {{ $post->user->nickname ?? $post->user->name }}
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
                            $newPostHours = (int) $site->getSetting('new_post_hours', 24);
                            $isNew = $post->created_at->isAfter(now()->subHours($newPostHours));
                            
                            // 질의응답 게시판인 경우 QA 상태 정보 가져오기
                            $isQaBoard = $board->type === 'qa';
                            $qaStatuses = [];
                            $currentStatus = null;
                            $statusColor = '#6c757d';
                            $displayStatus = null;
                            $displayColor = '#6c757d';
                            
                            if ($isQaBoard) {
                                $qaStatuses = is_array($board->qa_statuses) ? $board->qa_statuses : (is_string($board->qa_statuses) ? json_decode($board->qa_statuses, true) : []);
                                if (empty($qaStatuses) || !is_array($qaStatuses)) {
                                    $qaStatuses = [
                                        ['name' => '답변대기', 'color' => '#ffc107'],
                                        ['name' => '답변완료', 'color' => '#28a745']
                                    ];
                                }
                                
                                $currentStatus = $post->qa_status ?? null;
                                
                                if (empty($currentStatus) && !empty($qaStatuses) && isset($qaStatuses[0]['name'])) {
                                    $currentStatus = $qaStatuses[0]['name'];
                                }
                                
                                if ($currentStatus && !empty($qaStatuses)) {
                                    $statusInfo = collect($qaStatuses)->firstWhere('name', $currentStatus);
                                    if ($statusInfo) {
                                        $statusColor = $statusInfo['color'] ?? '#6c757d';
                                    } else {
                                        $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                    }
                                } elseif (!empty($qaStatuses) && isset($qaStatuses[0]['color'])) {
                                    $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                }
                                
                                $displayStatus = $currentStatus;
                                $displayColor = $statusColor;
                                if (empty($displayStatus) && isset($qaStatuses[0]['name'])) {
                                    $displayStatus = $qaStatuses[0]['name'];
                                    $displayColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                }
                            }
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
                                   class="text-decoration-none text-dark {{ ($isQaBoard && !empty($qaStatuses)) ? 'd-flex align-items-center justify-content-between' : 'flex-grow-1' }}" style="line-height: 1.5;">
                                    <span>
                                        @if($post->is_pinned)
                                            <i class="bi bi-pin-angle-fill me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                        @endif
                                        @if($post->is_notice)
                                            <i class="bi bi-megaphone me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                        @endif
                                        @if($board->enable_secret && $post->is_secret)
                                            @php
                                                $canViewSecret = false;
                                                if (auth()->check()) {
                                                    $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                                }
                                            @endphp
                                            @if($canViewSecret)
                                                {{ $post->title }}
                                            @else
                                                비밀 글입니다.
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
                                        @if($isNew)
                                            <span class="text-warning ms-1" style="font-size: 0.75rem; font-weight: bold;">N</span>
                                        @endif
                                        @if($hasImage)
                                            <i class="bi bi-image ms-1 text-muted" style="font-size: 0.875rem;"></i>
                                        @endif
                                    </span>
                                    @if($isQaBoard && !empty($qaStatuses) && !empty($displayStatus))
                                        <span class="badge ms-auto" style="background-color: {{ $displayColor }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0; white-space: nowrap;">{{ $displayStatus }}</span>
                                    @endif
                                </a>
                            </div>
                            <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.875rem;">
                                <span>
                                    @if($board->enable_anonymous)
                                        익명
                                    @else
                                        <x-user-rank :user="$post->user" :site="$site" />
                                        {{ $post->user->nickname ?? $post->user->name }}
                                    @endif
                                </span>
                                <span>·</span>
                                @if($showViews)
                                <span>조회수 {{ number_format($post->views) }}</span>
                                <span>·</span>
                                @endif
                                <span>
                                    @if($showDatetime)
                                        {{ $post->created_at->format('Y.m.d H:i') }}
                                    @else
                                        {{ $post->created_at->format('Y.m.d') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- 일반 게시판 레이아웃 (심플 리스트 형태) --}}
            <div class="card bg-white shadow-sm">
                <div class="list-group list-group-flush">
                @foreach($posts as $post)
                    @php
                        // 게시글 내용에서 이미지가 있는지 확인
                        $hasImage = false;
                        if ($post->content) {
                            $hasImage = preg_match('/<img[^>]+>/i', $post->content);
                        }
                        // 새글 기준 확인 (설정된 시간 내 작성된 게시글)
                        $newPostHours = (int) $site->getSetting('new_post_hours', 24);
                        $isNew = $post->created_at->isAfter(now()->subHours($newPostHours));
                        
                        // 질의응답 게시판인 경우 QA 상태 정보 가져오기
                        $isQaBoard = $board->type === 'qa';
                        $qaStatuses = [];
                        $currentStatus = null;
                        $statusColor = '#6c757d';
                        $displayStatus = null;
                        $displayColor = '#6c757d';
                        
                        if ($isQaBoard) {
                            $qaStatuses = is_array($board->qa_statuses) ? $board->qa_statuses : (is_string($board->qa_statuses) ? json_decode($board->qa_statuses, true) : []);
                            if (empty($qaStatuses) || !is_array($qaStatuses)) {
                                $qaStatuses = [
                                    ['name' => '답변대기', 'color' => '#ffc107'],
                                    ['name' => '답변완료', 'color' => '#28a745']
                                ];
                            }
                            
                            $currentStatus = $post->qa_status ?? null;
                            
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
                                    $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                }
                            } elseif (!empty($qaStatuses) && isset($qaStatuses[0]['color'])) {
                                $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                            }
                            
                            // 표시용 상태 및 색상
                            $displayStatus = $currentStatus;
                            $displayColor = $statusColor;
                            if (empty($displayStatus) && isset($qaStatuses[0]['name'])) {
                                $displayStatus = $qaStatuses[0]['name'];
                                $displayColor = $qaStatuses[0]['color'] ?? '#6c757d';
                            }
                        }
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
                               class="text-decoration-none text-dark {{ ($isQaBoard && !empty($qaStatuses)) ? 'd-flex align-items-center justify-content-between' : 'flex-grow-1' }}" style="line-height: 1.5;">
                                <span>
                                    @if($post->is_pinned)
                                        <i class="bi bi-pin-angle-fill me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                    @endif
                                    @if($post->is_notice)
                                        <i class="bi bi-megaphone me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                    @endif
                                    @if($board->enable_secret && $post->is_secret)
                                        @php
                                            $canViewSecret = false;
                                            if (auth()->check()) {
                                                $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                            }
                                        @endphp
                                        @if($canViewSecret)
                                            {{ $post->title }}
                                        @else
                                            비밀 글입니다.
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
                                    @if($isNew)
                                        <span class="text-warning ms-1" style="font-size: 0.75rem; font-weight: bold;">N</span>
                                    @endif
                                    @if($hasImage)
                                        <i class="bi bi-image ms-1 text-muted" style="font-size: 0.875rem;"></i>
                                    @endif
                                </span>
                                @if($isQaBoard && !empty($qaStatuses) && !empty($displayStatus))
                                    <span class="badge ms-auto" style="background-color: {{ $displayColor }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0; white-space: nowrap;">{{ $displayStatus }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.875rem;">
                            <span>
                                @if($board->enable_anonymous)
                                    익명
                                @else
                                    <x-user-rank :user="$post->user" :site="$site" />
                                    {{ $post->user->nickname ?? $post->user->name }}
                                @endif
                            </span>
                            <span>·</span>
                            <span>조회수 {{ number_format($post->views) }}</span>
                            <span>·</span>
                            <span>{{ $post->created_at->format('Y.m.d H:i') }}</span>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        @endif
        
        @if($posts->hasPages())
            <div class="mt-4 mb-4">
                {{ $posts->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
            </div>
        @endif
    </div>
@endif
@endsection

{{-- 포인트 지급 완료 모달 --}}
@if(session('quiz_award_success'))
<div class="modal fade" id="quizAwardSuccessModal" tabindex="-1" aria-labelledby="quizAwardSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quizAwardSuccessModalLabel">
                    <i class="bi bi-check-circle me-2"></i>포인트 지급 완료
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">{{ session('quiz_award_success') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
@if(session('quiz_award_success'))
<script>
$(document).ready(function() {
    // 모달 자동 표시
    var modal = new bootstrap.Modal(document.getElementById('quizAwardSuccessModal'));
    modal.show();
});
</script>
@endif
@endpush

@push('styles')
<style>
/* 모바일에서 게시글 수정/삭제 버튼 아이콘만 표시 (테두리 없음) */
@media (max-width: 767.98px) {
    .post-action-btn {
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        min-width: auto !important;
        box-shadow: none !important;
    }
    .post-action-btn:hover,
    .post-action-btn:focus,
    .post-action-btn:active {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    .post-action-btn.btn-outline-secondary {
        color: #6c757d !important;
    }
    .post-action-btn.btn-outline-secondary:hover {
        color: #5a6268 !important;
        opacity: 0.8;
    }
    .post-action-btn.btn-outline-danger {
        color: #dc3545 !important;
    }
    .post-action-btn.btn-outline-danger:hover {
        color: #c82333 !important;
        opacity: 0.8;
    }
    .post-action-btn i {
        margin: 0 !important;
        font-size: 1.25rem;
    }
}
</style>
@if($board->type === 'event' && $post->isEventPost() && $post->event_type === 'quiz')
<style>
    .quiz-option-card {
        transition: all 0.3s ease !important;
        background: linear-gradient(to bottom, #ffffff, #f5f5f5) !important;
        border: 1px solid #d0d0d0 !important;
        background-image: linear-gradient(to bottom, #ffffff, #f5f5f5) !important;
        cursor: pointer !important;
        user-select: none !important;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
    }
    .quiz-option-card * {
        pointer-events: none !important;
    }
    .card.quiz-option-card:hover:not(.selected) {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
    }
    .quiz-option-card.selected {
        border: 3px solid {{ $pointColor }} !important;
        box-shadow: 0 0 0 2px {{ $pointColor }}33 !important;
    }
    .card.quiz-option-card {
        background: linear-gradient(to bottom, #ffffff, #f5f5f5) !important;
        border: 1px solid #d0d0d0 !important;
    }
    #quiz-confirm-modal .btn-primary {
        background-color: {{ $pointColor }};
        border-color: {{ $pointColor }};
    }
    #quiz-confirm-modal .btn-primary:hover {
        background-color: {{ $pointColor }};
        border-color: {{ $pointColor }};
        opacity: 0.9;
    }
</style>
@endif
@endpush

@push('scripts')
<script>
function toggleSave(postId) {
    if (!{{ auth()->check() ? 'true' : 'false' }}) {
        alert('로그인이 필요합니다.');
        return;
    }
    
    const btn = document.getElementById('save-btn-' + postId);
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
}
</script>
@if($board->type === 'event' && $post->isEventPost())
<script>
$(document).ready(function() {
    var isSubmitting = false;
    window.pointColor = '{{ $pointColor }}'; // 전역 변수로 설정
    var pointColor = window.pointColor;
    
    // 디버깅: JavaScript가 실행되었는지 확인
    console.log('Event JavaScript loaded', {
        eventType: '{{ $post->event_type }}',
        pointColor: pointColor,
        quizCards: $('.quiz-option-card').length,
        hasEventOptions: {{ $post->eventOptions ? $post->eventOptions->count() : 0 }}
    });
    
    // 신청형 이벤트 참여 폼 제출
    @if($post->event_type === 'application')
    $('#application-participate-form').on('submit', function(e) {
        e.preventDefault();
        
        if (isSubmitting) {
            return false;
        }
        
        isSubmitting = true;
        var $form = $(this);
        var $btn = $('#application-participate-btn');
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>처리 중...');
        
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    $btn.html('<i class="bi bi-check-circle me-1"></i>신청 완료').removeClass('btn-info').addClass('btn-success');
                    $form.replaceWith('<div class="alert alert-success mt-3"><i class="bi bi-check-circle me-2"></i>' + response.message + '</div>');
                    // 참여자 수 업데이트
                    if (response.participant_count !== undefined) {
                        $('.badge.bg-info').text(response.participant_count + '명');
                    }
                } else {
                    alert(response.message || '신청 중 오류가 발생했습니다.');
                    $btn.prop('disabled', false).html(originalText);
                    isSubmitting = false;
                }
            },
            error: function(xhr) {
                var errorMessage = '신청 중 오류가 발생했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
                $btn.prop('disabled', false).html(originalText);
                isSubmitting = false;
            }
        });
        
        return false;
    });
    @endif
    
    // 정답형 이벤트 카드 호버 및 클릭 이벤트 (정답형 이벤트일 때만)
    // quiz-option-card가 있으면 정답형 이벤트로 간주
    if ($('.quiz-option-card').length > 0) {
        // pointColor가 없으면 기본값 사용
        if (!pointColor || pointColor === '') {
            pointColor = '#0d6efd';
        }
        
        console.log('Quiz cards found, initializing quiz handlers', {
            eventType: '{{ $post->event_type }}',
            pointColor: pointColor,
            quizCards: $('.quiz-option-card').length
        });
        
        // 정답형 이벤트 카드 호버 효과
        $(document).on('mouseenter', '.quiz-option-card', function() {
            if (!$(this).hasClass('selected')) {
                $(this).css({
                    'border-color': pointColor
                });
            }
        });
        
        $(document).on('mouseleave', '.quiz-option-card', function() {
            if (!$(this).hasClass('selected')) {
                $(this).css({
                    'border-color': '#d0d0d0'
                });
            }
        });
        
        // 정답형 이벤트 카드 클릭 이벤트
        $(document).on('click', '.quiz-option-card', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Quiz card clicked', {
                optionId: $(this).data('option-id'),
                optionText: $(this).data('option-text'),
                btnExists: $('#quiz-participate-btn').length > 0,
                btnVisible: $('#quiz-participate-btn').is(':visible'),
                btnDisabled: $('#quiz-participate-btn').prop('disabled')
            });
            
            // 버튼이 없거나 보이지 않으면 클릭 무시 (이미 참여한 경우 등)
            var $btn = $('#quiz-participate-btn');
            if ($btn.length === 0 || !$btn.is(':visible')) {
                return false; // 버튼이 없으면 클릭 무시
            }
            if ($btn.prop('disabled') && $btn.text().includes('처리 중')) {
                return false; // 이미 제출 중이면 클릭 무시
            }
            
            var optionId = $(this).data('option-id');
            var optionText = $(this).data('option-text');
            
            if (!optionId || !optionText) {
                console.error('옵션 ID 또는 텍스트가 없습니다.', optionId, optionText);
                return false;
            }
            
            // 모든 카드 스타일 초기화
            $('.quiz-option-card').removeClass('selected').css({
                'transform': 'translateY(0)',
                'box-shadow': 'none',
                'border-color': '#d0d0d0',
                'border-width': '1px'
            });
            
            // 선택된 카드 강조 (포인트 컬러 보더)
            $(this).addClass('selected').css({
                'border-color': pointColor,
                'border-width': '3px',
                'box-shadow': '0 0 0 2px ' + pointColor + '33'
            });
            
            // hidden input에 선택된 옵션 ID 설정
            $('#selected-option-id').val(optionId);
            
            // 선택한 정답 표시
            $('#selected-answer-text').text('"' + optionText + '"');
            $('#selected-answer-display').slideDown();
            
            // 제출 버튼 활성화
            $btn.prop('disabled', false);
            
            return false;
        });
        
        // 참여 버튼 클릭 시 모달 표시
        $('#quiz-participate-btn').on('click', function() {
        var selectedOptionId = $('#selected-option-id').val();
        if (!selectedOptionId) {
            alert('정답을 선택해주세요.');
            return false;
        }
        
        var selectedCard = $('#quiz-option-card-' + selectedOptionId);
        var optionText = selectedCard.data('option-text');
        
        // 모달 메시지 설정
        $('#quiz-confirm-message').text('선택지 "' + optionText + '"을 선택하시겠습니까?');
        
        // 모달 표시
        var modal = new bootstrap.Modal(document.getElementById('quiz-confirm-modal'));
        modal.show();
        });
        
        // 모달 확인 버튼 클릭 시 폼 제출
        $('#quiz-confirm-submit').on('click', function() {
        var selectedOptionId = $('#selected-option-id').val();
        if (!selectedOptionId) {
            alert('정답을 선택해주세요.');
            return false;
        }
        
        if (isSubmitting) {
            return false;
        }
        
        isSubmitting = true;
        var $form = $('#quiz-participate-form');
        var $btn = $('#quiz-participate-btn');
        var originalText = $btn.html();
        
        // 모달 닫기
        var modal = bootstrap.Modal.getInstance(document.getElementById('quiz-confirm-modal'));
        modal.hide();
        
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>처리 중...');
        
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    // 페이지 새로고침하여 업데이트된 통계 표시
                    location.reload();
                } else {
                    alert(response.message || '제출 중 오류가 발생했습니다.');
                    $btn.prop('disabled', false).html(originalText);
                    isSubmitting = false;
                }
            },
            error: function(xhr) {
                var errorMessage = '제출 중 오류가 발생했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
                $btn.prop('disabled', false).html(originalText);
                isSubmitting = false;
            }
        });
        
        return false;
    });
    }
});
</script>
@endif
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // board.enable_likes가 true일 때만 실행
    if ({{ $board->enable_likes ? 'true' : 'false' }}) {
        const processingPosts = new Set(); // 처리 중인 게시글 ID를 저장
        
        // 이벤트가 중복 바인딩되지 않도록 off 후 on
        $('.like-btn, .dislike-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        const postId = $btn.data('post-id');
        const type = $btn.data('type');
        
        // 이미 처리 중이면 무시
        if (processingPosts.has(postId)) {
            return false;
        }
        
        const $likeBtn = $('.like-btn[data-post-id="' + postId + '"]');
        const $dislikeBtn = $('.dislike-btn[data-post-id="' + postId + '"]');
        
        // 처리 시작
        processingPosts.add(postId);
        $btn.prop('disabled', true);
        $likeBtn.prop('disabled', true);
        $dislikeBtn.prop('disabled', true);
        
        // URL을 동적으로 생성 (data-post-id 사용)
        const url = '{{ route("posts.toggle-like", ["site" => $site->slug, "boardSlug" => $board->slug, "postId" => "POST_ID_PLACEHOLDER"]) }}'.replace('POST_ID_PLACEHOLDER', postId);
        
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                type: type
            },
            success: function(response) {
                console.log('AJAX Response:', response);
                if (response.success) {
                    // 카운트 업데이트
                    const likeCount = response.like_count || 0;
                    const dislikeCount = response.dislike_count || 0;
                    $likeBtn.find('.like-count').text(likeCount);
                    $dislikeBtn.find('.dislike-count').text(dislikeCount);
                    
                    // 버튼 상태 업데이트
                    if (response.user_like_type === 'like') {
                        $likeBtn.addClass('active').css({
                            'background-color': '#0d6efd',
                            'color': 'white',
                            'border-color': '#0d6efd'
                        });
                        $likeBtn.find('i').removeClass('bi-hand-thumbs-up').addClass('bi-hand-thumbs-up-fill');
                        $dislikeBtn.removeClass('active').css({
                            'background-color': '',
                            'color': '',
                            'border-color': ''
                        });
                        $dislikeBtn.find('i').removeClass('bi-hand-thumbs-down-fill').addClass('bi-hand-thumbs-down');
                    } else if (response.user_like_type === 'dislike') {
                        $dislikeBtn.addClass('active').css({
                            'background-color': '#dc3545',
                            'color': 'white',
                            'border-color': '#dc3545'
                        });
                        $dislikeBtn.find('i').removeClass('bi-hand-thumbs-down').addClass('bi-hand-thumbs-down-fill');
                        $likeBtn.removeClass('active').css({
                            'background-color': '',
                            'color': '',
                            'border-color': ''
                        });
                        $likeBtn.find('i').removeClass('bi-hand-thumbs-up-fill').addClass('bi-hand-thumbs-up');
                    } else {
                        // 추천/비추천 취소
                        $likeBtn.removeClass('active').css({
                            'background-color': '',
                            'color': '',
                            'border-color': ''
                        });
                        $likeBtn.find('i').removeClass('bi-hand-thumbs-up-fill').addClass('bi-hand-thumbs-up');
                        $dislikeBtn.removeClass('active').css({
                            'background-color': '',
                            'color': '',
                            'border-color': ''
                        });
                        $dislikeBtn.find('i').removeClass('bi-hand-thumbs-down-fill').addClass('bi-hand-thumbs-down');
                    }
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                if (xhr.status === 401) {
                    alert('로그인이 필요합니다.');
                } else {
                    alert('오류가 발생했습니다: ' + (xhr.responseJSON?.message || xhr.statusText));
                }
            },
            complete: function() {
                // 버튼 활성화
                processingPosts.delete(postId);
                $btn.prop('disabled', false);
                $likeBtn.prop('disabled', false);
                $dislikeBtn.prop('disabled', false);
            }
        });
        });
    }
});
</script>

<!-- 쪽지 보내기 모달 -->
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
                    @if($site->hasRegistrationFeature('point_message') && $site->getSetting('enable_point_message', '0') == '1')
                        <div class="mb-3 border-top pt-3" id="pointMessageSection">
                            <div class="mb-2">
                                <label class="form-label">보유 포인트</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="userPoints" value="{{ number_format(auth()->user()->points ?? 0) }}" readonly>
                                    <span class="input-group-text">P</span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="messagePoints" class="form-label">전송할 포인트</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="messagePoints" min="0" step="1" value="0">
                                    <span class="input-group-text">P</span>
                                </div>
                                <small class="form-text text-muted">0을 입력하면 포인트 없이 쪽지만 전송됩니다.</small>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">전송</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 전역 쪽지 보내기 함수
window.openSendMessageModal = function(userId, userName) {
    document.getElementById('messageReceiverId').value = userId;
    document.getElementById('messageReceiver').value = userName || '사용자';
    document.getElementById('messageContent').value = '';
    const modal = new bootstrap.Modal(document.getElementById('sendMessageModal'));
    modal.show();
};

// 쪽지 전송
document.addEventListener('DOMContentLoaded', function() {
    const sendMessageForm = document.getElementById('sendMessageForm');
    if (sendMessageForm) {
        sendMessageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const receiverId = document.getElementById('messageReceiverId').value;
            const content = document.getElementById('messageContent').value;
            const points = document.getElementById('messagePoints') ? parseInt(document.getElementById('messagePoints').value) || 0 : 0;
            
            if (!content.trim()) {
                alert('내용을 입력해주세요.');
                return;
            }

            @if($site->getSetting('enable_point_message', '0') == '1')
            if (points > 0) {
                const userPoints = {{ auth()->user()->points ?? 0 }};
                if (points > userPoints) {
                    alert('보유 포인트가 부족합니다.');
                    return;
                }
                if (!confirm(`포인트 ${points.toLocaleString()}P를 함께 전송하시겠습니까?`)) {
                    return;
                }
            }
            @endif
            
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
                    points: points,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('쪽지가 전송되었습니다.');
                    bootstrap.Modal.getInstance(document.getElementById('sendMessageModal')).hide();
                    document.getElementById('messageContent').value = '';
                    @if($site->hasRegistrationFeature('point_message') && $site->getSetting('enable_point_message', '0') == '1')
                    if (document.getElementById('messagePoints')) {
                        document.getElementById('messagePoints').value = '0';
                        // 포인트 업데이트
                        if (data.user_points !== undefined) {
                            document.getElementById('userPoints').value = data.user_points.toLocaleString();
                        }
                    }
                    @endif
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
});
</script>

@if($board->type === 'qa' && auth()->check() && auth()->user()->canManage())
<script>
    // 질의응답 상태 변경 함수 (전역 함수로 정의)
    function updateQaStatus(status, color) {
        if (!status) {
            console.error('Status not found');
            return false;
        }
        
        // 드롭다운 닫기
        const dropdownElement = document.getElementById('qaStatusDropdown');
        if (dropdownElement) {
            const dropdown = bootstrap.Dropdown.getInstance(dropdownElement);
            if (dropdown) {
                dropdown.hide();
            }
        }
        
        // 현재 URL에서 post_id 추출 (정확한 post_id 사용)
        const currentPath = window.location.pathname;
        const postIdMatch = currentPath.match(/\/posts\/(\d+)/);
        const postId = postIdMatch ? postIdMatch[1] : {{ $post->id }};
        
        // URL 직접 구성
        const updateUrl = `/site/{{ $site->slug }}/boards/{{ $board->slug }}/posts/${postId}/update-qa-status`;
        
        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: status
            }),
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || '상태 변경에 실패했습니다.');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // DOM 직접 업데이트
                const dropdownButton = document.getElementById('qaStatusDropdown');
                if (dropdownButton) {
                    // 버튼 텍스트 업데이트
                    const badgeElement = dropdownButton.querySelector('.badge');
                    if (badgeElement) {
                        badgeElement.textContent = data.status;
                    }
                }
                
                // 제목 옆 배지도 업데이트
                const titleBadge = document.querySelector('.card-header .badge');
                if (titleBadge) {
                    titleBadge.textContent = data.status;
                }
                
                // 페이지 리로드하여 상태 반영
                console.log('Reloading page...');
                // 강제 리로드 (캐시 무시)
                setTimeout(function() {
                    window.location.href = window.location.href.split('?')[0] + '?t=' + Date.now();
                }, 200);
            } else {
                alert(data.message || '상태 변경 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error updating QA status:', error);
            alert(error.message || '상태 변경 중 오류가 발생했습니다.');
        });
        
        return false;
    }
</script>
@endif

@if($showShare)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const shareButton = document.getElementById('shareButton');
        if (!shareButton) return;

        // Web Share API 지원 여부 확인
        const isWebShareSupported = navigator.share !== undefined;

        if (isWebShareSupported) {
            // Web Share API를 지원하는 경우
            shareButton.addEventListener('click', async function() {
                try {
                    const shareData = {
                        title: '{{ addslashes($post->title) }}',
                        text: '{{ addslashes(mb_substr(strip_tags($post->content), 0, 200)) }}',
                        url: window.location.href
                    };

                    await navigator.share(shareData);
                } catch (error) {
                    // 사용자가 공유를 취소한 경우는 에러로 처리하지 않음
                    if (error.name !== 'AbortError') {
                        console.error('공유 중 오류가 발생했습니다:', error);
                    }
                }
            });
        } else {
            // Web Share API를 지원하지 않는 경우 - 클립보드에 URL 복사
            shareButton.addEventListener('click', function() {
                const url = window.location.href;
                
                // 클립보드에 복사
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(function() {
                        // 성공 메시지 표시 (아이콘만 변경)
                        const icon = shareButton.querySelector('i');
                        if (icon) {
                            const originalClass = icon.className;
                            icon.className = 'bi bi-check-circle';
                            shareButton.classList.remove('btn-outline-secondary');
                            shareButton.classList.add('btn-success');
                            
                            setTimeout(function() {
                                icon.className = originalClass;
                                shareButton.classList.remove('btn-success');
                                shareButton.classList.add('btn-outline-secondary');
                            }, 2000);
                        }
                    }).catch(function(err) {
                        console.error('클립보드 복사 실패:', err);
                        // 폴백: 수동 복사 안내
                        prompt('링크를 복사하세요:', url);
                    });
                } else {
                    // 폴백: 수동 복사 안내
                    prompt('링크를 복사하세요:', url);
                }
            });
        }
    });
</script>
@endif

<script>
// 신고 모달 열기
function openReportModal(type, id, siteSlug, boardSlug = null, postId = null) {
    const modal = document.createElement('div');
    modal.className = 'modal fade show';
    modal.style.display = 'block';
    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">신고하기</h5>
                    <button type="button" class="btn-close" onclick="this.closest('.modal').remove()"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reportReason" class="form-label">신고 사유를 작성해주세요</label>
                        <textarea class="form-control" id="reportReason" rows="4" placeholder="신고 사유를 입력하세요..." maxlength="500" required></textarea>
                        <small class="text-muted">최대 500자까지 입력 가능합니다.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">취소</button>
                    <button type="button" class="btn btn-primary" id="submitReport">신고하기</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // 신고 제출 버튼 클릭 이벤트
    modal.querySelector('#submitReport').addEventListener('click', function() {
        const reason = modal.querySelector('#reportReason').value.trim();
        
        if (!reason) {
            alert('신고 사유를 입력해주세요.');
            return;
        }
        
        let url;
        if (type === 'comment') {
            url = '/site/' + siteSlug + '/api/boards/' + boardSlug + '/posts/' + postId + '/comments/' + id + '/report';
        } else {
            url = '/site/' + siteSlug + '/api/posts/' + id + '/report';
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            modal.remove();
            if (data.success) {
                alert('신고가 접수되었습니다.');
            } else {
                alert(data.error || '신고 접수에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modal.remove();
            alert('신고 접수에 실패했습니다.');
        });
    });
    
    // 모달 외부 클릭 시 닫기
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}
</script>
@endpush

