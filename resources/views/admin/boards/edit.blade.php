@extends('layouts.admin')

@section('title', '게시판 수정')
@section('page-title', '게시판 수정')
@section('page-subtitle', '게시판 설정을 수정합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- 일반 설정 -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>일반</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('boards.update-general', ['site' => $site->slug, 'board' => $board->id]) }}" id="generalForm" enctype="multipart/form-data">
                    @csrf
                    @method('POST')
                    {{-- 데스크탑 버전 (테이블) --}}
                    <div class="d-none d-md-block">
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <td style="width: 150px;">
                                    <label for="type" class="form-label mb-0">
                                        게시판 타입
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시판의 표시 형태를 선택하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" name="type">
                                        @foreach(\App\Models\Board::getTypes() as $value => $label)
                                            @if($site->hasBoardType($value))
                                                <option value="{{ $value }}" {{ old('type', $board->type) === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr id="event-display-type-row" style="display: none;">
                                <td style="width: 150px;">
                                    <label class="form-label mb-0">
                                        이벤트 표시 타입
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="이벤트 게시판의 표시 형태를 선택하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="event_display_type" id="event_display_type_photo" value="photo" {{ old('event_display_type', $board->event_display_type ?? 'photo') === 'photo' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="event_display_type_photo">
                                            사진 타입
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="event_display_type" id="event_display_type_general" value="general" {{ old('event_display_type', $board->event_display_type) === 'general' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="event_display_type_general">
                                            일반 타입
                                        </label>
                                    </div>
                                    @error('event_display_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr id="summary-length-row" style="display: none;">
                                <td style="width: 150px;">
                                    <label for="summary_length" class="form-label mb-0">
                                        요약 내용 길이
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="블로그 게시판 리스트에 표시될 요약 내용의 글자수를 입력하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <input type="number" 
                                           class="form-control @error('summary_length') is-invalid @enderror" 
                                           id="summary_length" 
                                           name="summary_length" 
                                           value="{{ old('summary_length', $board->summary_length ?? 150) }}" 
                                           min="50" 
                                           max="500"
                                           style="width: 150px;">
                                    <small class="form-text text-muted">기본값: 150자 (50~500자)</small>
                                    @error('summary_length')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr id="pinterest-columns-row" style="display: none;">
                                <td style="width: 150px;">
                                    <label class="form-label mb-0">
                                        가로 개수 설정
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="핀터레스트 타입 게시판의 각 디바이스별 가로 개수를 설정하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="pinterest_columns_mobile" class="form-label small">모바일</label>
                                            <input type="number" 
                                                   class="form-control @error('pinterest_columns_mobile') is-invalid @enderror" 
                                                   id="pinterest_columns_mobile" 
                                                   name="pinterest_columns_mobile" 
                                                   value="{{ old('pinterest_columns_mobile', $board->pinterest_columns_mobile ?? 2) }}" 
                                                   min="1" 
                                                   max="6"
                                                   required>
                                            <small class="form-text text-muted">기본: 2개</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pinterest_columns_tablet" class="form-label small">태블릿</label>
                                            <input type="number" 
                                                   class="form-control @error('pinterest_columns_tablet') is-invalid @enderror" 
                                                   id="pinterest_columns_tablet" 
                                                   name="pinterest_columns_tablet" 
                                                   value="{{ old('pinterest_columns_tablet', $board->pinterest_columns_tablet ?? 3) }}" 
                                                   min="1" 
                                                   max="6"
                                                   required>
                                            <small class="form-text text-muted">기본: 3개</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pinterest_columns_desktop" class="form-label small">데스크톱</label>
                                            <input type="number" 
                                                   class="form-control @error('pinterest_columns_desktop') is-invalid @enderror" 
                                                   id="pinterest_columns_desktop" 
                                                   name="pinterest_columns_desktop" 
                                                   value="{{ old('pinterest_columns_desktop', $board->pinterest_columns_desktop ?? 4) }}" 
                                                   min="1" 
                                                   max="6"
                                                   required>
                                            <small class="form-text text-muted">기본: 4개</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pinterest_columns_large" class="form-label small">큰 화면</label>
                                            <input type="number" 
                                                   class="form-control @error('pinterest_columns_large') is-invalid @enderror" 
                                                   id="pinterest_columns_large" 
                                                   name="pinterest_columns_large" 
                                                   value="{{ old('pinterest_columns_large', $board->pinterest_columns_large ?? 6) }}" 
                                                   min="1" 
                                                   max="12"
                                                   required>
                                            <small class="form-text text-muted">기본: 6개</small>
                                        </div>
                                    </div>
                                    @error('pinterest_columns_mobile')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('pinterest_columns_tablet')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('pinterest_columns_desktop')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('pinterest_columns_large')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr id="qa-statuses-row" style="display: none;">
                                <td style="width: 150px;">
                                    <label class="form-label mb-0">
                                        상태 표시 설정
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="질의응답 게시판의 상태를 설정하세요. 각 상태의 이름과 배지 색상을 지정할 수 있습니다."></i>
                                    </label>
                                </td>
                                <td>
                                    <div id="qa-statuses-container">
                                        @php
                                            $qaStatuses = old('qa_statuses', $board->qa_statuses ?? []);
                                            if (empty($qaStatuses)) {
                                                $qaStatuses = [
                                                    ['name' => '답변대기', 'color' => '#ffc107'],
                                                    ['name' => '답변완료', 'color' => '#28a745']
                                                ];
                                            }
                                        @endphp
                                        @foreach($qaStatuses as $index => $status)
                                            <div class="qa-status-item mb-2 d-flex align-items-center gap-2" data-index="{{ $index }}">
                                                <button type="button" class="btn btn-sm btn-outline-secondary move-qa-status-up" {{ $index === 0 ? 'disabled' : '' }} title="위로 이동">
                                                    <i class="bi bi-arrow-up"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary move-qa-status-down" {{ $index === count($qaStatuses) - 1 ? 'disabled' : '' }} title="아래로 이동">
                                                    <i class="bi bi-arrow-down"></i>
                                                </button>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="qa_statuses[{{ $index }}][name]" 
                                                       value="{{ $status['name'] ?? '' }}" 
                                                       placeholder="상태 이름"
                                                       style="width: 150px;">
                                                <input type="color" 
                                                       class="form-control form-control-color form-control-sm" 
                                                       name="qa_statuses[{{ $index }}][color]" 
                                                       value="{{ $status['color'] ?? '#ffc107' }}" 
                                                       style="width: 50px; height: 38px;">
                                                <button type="button" class="btn btn-sm btn-danger remove-qa-status" {{ count($qaStatuses) <= 1 ? 'disabled' : '' }}>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary mt-2" id="add-qa-status">
                                        <i class="bi bi-plus"></i> 상태 추가
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 150px;">
                                    <label for="name" class="form-label mb-0">
                                        이름
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시판의 이름을 입력하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $board->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="slug" class="form-label mb-0">
                                        연결 주소
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="URL에 사용될 슬러그를 입력하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                           id="slug" name="slug" value="{{ old('slug', $board->slug) }}">
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="description" class="form-label mb-0">
                                        설명
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시판에 대한 설명을 입력하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="2">{{ old('description', $board->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="max_posts_per_day" class="form-label mb-0">
                                        유저당 하루 최대 글
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="한 사용자가 하루에 작성할 수 있는 최대 게시글 수 (0은 제한 없음)"></i>
                                    </label>
                                </td>
                                <td>
                                    <input type="number" class="form-control @error('max_posts_per_day') is-invalid @enderror" 
                                           id="max_posts_per_day" name="max_posts_per_day" value="{{ old('max_posts_per_day', $board->max_posts_per_day ?? 0) }}" min="0" style="width: 150px;">
                                    @error('max_posts_per_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="posts_per_page" class="form-label mb-0">
                                        페이지당 표시 게시글
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="한 페이지에 표시할 게시글 수를 설정합니다"></i>
                                    </label>
                                </td>
                                <td>
                                    <input type="number" class="form-control @error('posts_per_page') is-invalid @enderror" 
                                           id="posts_per_page" name="posts_per_page" value="{{ old('posts_per_page', $board->posts_per_page ?? 20) }}" min="1" style="width: 150px;">
                                    @error('posts_per_page')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="header_image" class="form-label mb-0">
                                        상단 이미지
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시판 제목 상단에 표시될 이미지를 업로드하세요"></i>
                                    </label>
                                </td>
                                <td>
                                    @if($board->header_image_path)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $board->header_image_path) }}" alt="현재 이미지" style="max-width: 300px; max-height: 200px; border-radius: 0.25rem; border: 1px solid #dee2e6;">
                                        </div>
                                    @endif
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="file" 
                                               class="@error('header_image') is-invalid @enderror" 
                                               id="header_image" 
                                               name="header_image" 
                                               accept="image/*"
                                               style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
                                        <label for="header_image" class="btn btn-outline-secondary mb-0" style="cursor: pointer; white-space: nowrap; margin-bottom: 0 !important;">
                                            <i class="bi bi-image me-1"></i>파일 선택
                                        </label>
                                        <input type="text" class="form-control" id="header_image_filename" placeholder="선택된 파일 없음" readonly style="flex: 1;">
                                    </div>
                                    <div id="header_image_preview" class="mt-2" style="display: none;">
                                        <img id="header_image_preview_img" src="" alt="미리보기" style="max-width: 300px; max-height: 200px; border-radius: 0.25rem;">
                                    </div>
                                    @error('header_image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr id="random-order-row" style="display: {{ old('type', $board->type) === 'bookmark' ? '' : 'none' }};">
                                <td>
                                    <label class="form-label mb-0">
                                        랜덤 배치
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시글 리스트를 랜덤으로 배치합니다 (공지/고정 게시글은 항상 상단 고정)"></i>
                                    </label>
                                </td>
                                <td>
                                    @php
                                        // DB에서 불러온 값만 사용 (old() 무시 - redirect 후에는 DB 값이 정확함)
                                        $dbValue = $board->random_order;
                                        $isChecked = ($dbValue === true || $dbValue === 1 || $dbValue === '1');
                                    @endphp
                                    {{-- 항상 hidden input으로 값을 전송 (체크박스는 표시용) --}}
                                    <input type="hidden" name="random_order" id="random_order_hidden" value="{{ $isChecked ? '1' : '0' }}">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="random_order" 
                                               value="1" 
                                               {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="random_order">
                                            랜덤 배치 활성화
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">
                                        복수 주제 활성화
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시글 작성 시 여러 주제를 선택할 수 있도록 합니다"></i>
                                    </label>
                                </td>
                                <td>
                                    @php
                                        $allowMultipleTopics = $board->allow_multiple_topics;
                                        $isMultipleChecked = ($allowMultipleTopics === true || $allowMultipleTopics === 1 || $allowMultipleTopics === '1');
                                    @endphp
                                    <input type="hidden" name="allow_multiple_topics" id="allow_multiple_topics_hidden" value="{{ $isMultipleChecked ? '1' : '0' }}">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="allow_multiple_topics" 
                                               value="1" 
                                               {{ $isMultipleChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_multiple_topics">
                                            복수 주제 선택 허용
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">
                                        링크 삭제
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시글 내용에서 모든 링크를 자동으로 제거합니다. 불법 홍보성 링크 방지에 유용합니다."></i>
                                    </label>
                                </td>
                                <td>
                                    @php
                                        $removeLinks = $board->remove_links;
                                        $isRemoveLinksChecked = ($removeLinks === true || $removeLinks === 1 || $removeLinks === '1');
                                    @endphp
                                    <input type="hidden" name="remove_links" id="remove_links_hidden" value="{{ $isRemoveLinksChecked ? '1' : '0' }}">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="remove_links" 
                                               value="1" 
                                               {{ $isRemoveLinksChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remove_links">
                                            링크 삭제 활성화
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">
                                        추천 기능
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시글에 추천/비추천 버튼을 표시합니다"></i>
                                    </label>
                                </td>
                                <td>
                                    @php
                                        $enableLikes = $board->enable_likes;
                                        $isEnableLikesChecked = ($enableLikes === true || $enableLikes === 1 || $enableLikes === '1');
                                    @endphp
                                    <input type="hidden" name="enable_likes" id="enable_likes_hidden" value="{{ $isEnableLikesChecked ? '1' : '0' }}">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="enable_likes" 
                                               value="1" 
                                               {{ $isEnableLikesChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_likes">
                                            추천기능 활성화
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">
                                        저장하기
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시글에 저장 아이콘을 표시합니다"></i>
                                    </label>
                                </td>
                                <td>
                                    @php
                                        $enableSavedPosts = $board->saved_posts_enabled ?? false;
                                        $isEnableSavedPostsChecked = ($enableSavedPosts === true || $enableSavedPosts === 1 || $enableSavedPosts === '1');
                                    @endphp
                                    <input type="hidden" name="saved_posts_enabled" id="saved_posts_enabled_hidden" value="{{ $isEnableSavedPostsChecked ? '1' : '0' }}">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="saved_posts_enabled" 
                                               value="1" 
                                               {{ $isEnableSavedPostsChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="saved_posts_enabled">
                                            저장하기 활성화
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">게시판 제목 및 설명 숨기기</label>
                                </td>
                                <td>
                                    <div class="form-check">
                                        @php
                                            $hideTitleDescription = $board->hide_title_description ?? false;
                                            $isHideTitleDescriptionChecked = ($hideTitleDescription === true || $hideTitleDescription === 1 || $hideTitleDescription === '1');
                                        @endphp
                                        <input type="hidden" name="hide_title_description" id="hide_title_description_hidden" value="{{ $isHideTitleDescriptionChecked ? '1' : '0' }}">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               name="hide_title_description_checkbox" 
                                               id="hide_title_description" 
                                               {{ $isHideTitleDescriptionChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hide_title_description">
                                            게시판 제목 및 설명 숨기기
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">글 삭제</label>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="date" class="form-control" id="delete_start_date" style="width: 150px;">
                                        <span>~</span>
                                        <input type="date" class="form-control" id="delete_end_date" style="width: 150px;">
                                        <button type="button" class="btn btn-danger" onclick="confirmDeletePosts()">삭제</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">게시판 삭제</label>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger" onclick="confirmDeleteBoard()">삭제</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    
                    {{-- 모바일 버전 (카드 레이아웃) - 주요 필드만 --}}
                    <div class="d-md-none">
                        <div class="d-grid gap-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <label for="type_mobile" class="form-label fw-bold mb-2">게시판 타입</label>
                                    <select class="form-select form-select-sm" id="type_mobile" name="type">
                                        @foreach(\App\Models\Board::getTypes() as $value => $label)
                                            @if($site->hasBoardType($value))
                                                <option value="{{ $value }}" {{ old('type', $board->type) === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <label for="name_mobile" class="form-label fw-bold mb-2">이름</label>
                                    <input type="text" class="form-control form-control-sm" id="name_mobile" name="name" value="{{ old('name', $board->name) }}" required>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <label for="slug_mobile" class="form-label fw-bold mb-2">연결 주소</label>
                                    <input type="text" class="form-control form-control-sm" id="slug_mobile" name="slug" value="{{ old('slug', $board->slug) }}">
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <label for="description_mobile" class="form-label fw-bold mb-2">설명</label>
                                    <textarea class="form-control form-control-sm" id="description_mobile" name="description" rows="2">{{ old('description', $board->description) }}</textarea>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <label for="max_posts_per_day_mobile" class="form-label fw-bold mb-2">유저당 하루 최대 글</label>
                                    <input type="number" class="form-control form-control-sm" id="max_posts_per_day_mobile" name="max_posts_per_day" value="{{ old('max_posts_per_day', $board->max_posts_per_day ?? 0) }}" min="0">
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <label for="posts_per_page_mobile" class="form-label fw-bold mb-2">페이지당 표시 게시글</label>
                                    <input type="number" class="form-control form-control-sm" id="posts_per_page_mobile" name="posts_per_page" value="{{ old('posts_per_page', $board->posts_per_page ?? 20) }}" min="1">
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <label class="form-label fw-bold mb-2">저장하기</label>
                                    @php
                                        $enableSavedPosts = $board->saved_posts_enabled ?? false;
                                        $isEnableSavedPostsChecked = ($enableSavedPosts === true || $enableSavedPosts === 1 || $enableSavedPosts === '1');
                                    @endphp
                                    <input type="hidden" name="saved_posts_enabled" id="saved_posts_enabled_hidden_mobile" value="{{ $isEnableSavedPostsChecked ? '1' : '0' }}">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="saved_posts_enabled_mobile" 
                                               value="1" 
                                               {{ $isEnableSavedPostsChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="saved_posts_enabled_mobile">
                                            저장하기 활성화
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <label class="form-label fw-bold mb-2">게시판 제목 및 설명 숨기기</label>
                                    @php
                                        $hideTitleDescription = $board->hide_title_description ?? false;
                                        $isHideTitleDescriptionChecked = ($hideTitleDescription === true || $hideTitleDescription === 1 || $hideTitleDescription === '1');
                                    @endphp
                                    <input type="hidden" name="hide_title_description" id="hide_title_description_hidden_mobile" value="{{ $isHideTitleDescriptionChecked ? '1' : '0' }}">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               name="hide_title_description_checkbox" 
                                               id="hide_title_description_mobile" 
                                               {{ $isHideTitleDescriptionChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hide_title_description_mobile">
                                            게시판 제목 및 설명 숨기기
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <label class="form-label fw-bold mb-2">글 삭제</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="date" class="form-control form-control-sm" id="delete_start_date_mobile" style="width: 120px;">
                                        <span>~</span>
                                        <input type="date" class="form-control form-control-sm" id="delete_end_date_mobile" style="width: 120px;">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeletePosts()">삭제</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SEO 설정 -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-search me-2"></i>SEO</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('boards.update-seo', ['site' => $site->slug, 'board' => $board->id]) }}" id="seoForm">
                    @csrf
                    @method('POST')
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td style="width: 150px;">
                                    <label for="seo_title" class="form-label mb-0">SEO Title</label>
                                </td>
                                <td>
                                    <input type="text" class="form-control @error('seo_title') is-invalid @enderror" 
                                           id="seo_title" name="seo_title" value="{{ old('seo_title', $board->seo_title) }}">
                                    @error('seo_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="seo_description" class="form-label mb-0">SEO Description</label>
                                </td>
                                <td>
                                    <textarea class="form-control @error('seo_description') is-invalid @enderror" 
                                              id="seo_description" name="seo_description" rows="3">{{ old('seo_description', $board->seo_description) }}</textarea>
                                    @error('seo_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 등급 & 포인트 설정 -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-star me-2"></i>등급 & 포인트</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">포인트(감소는 음수로 설정하세요)</p>
                <form method="POST" action="{{ route('boards.update-grade-points', ['site' => $site->slug, 'board' => $board->id]) }}" id="gradePointsForm">
                    @csrf
                    @method('POST')
                    {{-- 데스크탑 버전 (테이블) --}}
                    <div class="d-none d-md-block">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 150px;"></th>
                                    <th>등급</th>
                                    <th>포인트</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>게시글 읽기</strong></td>
                                    <td>
                                        <select class="form-select" name="read_permission" id="read_permission">
                                            <option value="guest" {{ old('read_permission', $board->read_permission ?? 'guest') === 'guest' ? 'selected' : '' }}>비회원</option>
                                            <option value="user" {{ old('read_permission', $board->read_permission ?? 'guest') === 'user' ? 'selected' : '' }}>회원</option>
                                            <option value="admin" {{ old('read_permission', $board->read_permission ?? 'guest') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="read_points" id="read_points" value="{{ old('read_points', $board->read_points ?? 0) }}">
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>게시글 쓰기</strong></td>
                                    <td>
                                        <select class="form-select" name="write_permission" id="write_permission">
                                            <option value="guest" {{ old('write_permission', $board->write_permission ?? 'user') === 'guest' ? 'selected' : '' }}>비회원</option>
                                            <option value="user" {{ old('write_permission', $board->write_permission ?? 'user') === 'user' ? 'selected' : '' }}>회원</option>
                                            <option value="admin" {{ old('write_permission', $board->write_permission ?? 'user') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="write_points" id="write_points" value="{{ old('write_points', $board->write_points ?? 0) }}">
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>게시글 삭제</strong></td>
                                    <td>
                                        <select class="form-select" name="delete_permission" id="delete_permission">
                                            <option value="author" {{ old('delete_permission', $board->delete_permission ?? 'author') === 'author' ? 'selected' : '' }}>작성자 본인</option>
                                            <option value="admin" {{ old('delete_permission', $board->delete_permission ?? 'author') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="delete_points" id="delete_points" value="{{ old('delete_points', $board->delete_points ?? 0) }}">
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>댓글 쓰기</strong></td>
                                    <td>
                                        <select class="form-select" name="comment_permission" id="comment_permission">
                                            <option value="guest" {{ old('comment_permission', $board->comment_permission ?? 'user') === 'guest' ? 'selected' : '' }}>비회원</option>
                                            <option value="user" {{ old('comment_permission', $board->comment_permission ?? 'user') === 'user' ? 'selected' : '' }}>회원</option>
                                            <option value="admin" {{ old('comment_permission', $board->comment_permission ?? 'user') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="comment_points" id="comment_points" value="{{ old('comment_points', $board->comment_points ?? 0) }}">
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>댓글 삭제</strong></td>
                                    <td>
                                        <select class="form-select" name="comment_delete_permission" id="comment_delete_permission">
                                            <option value="author" {{ old('comment_delete_permission', $board->comment_delete_permission ?? 'author') === 'author' ? 'selected' : '' }}>작성자 본인</option>
                                            <option value="admin" {{ old('comment_delete_permission', $board->comment_delete_permission ?? 'author') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="comment_delete_points" id="comment_delete_points" value="{{ old('comment_delete_points', $board->comment_delete_points ?? 0) }}">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- 모바일 버전 (카드 레이아웃) --}}
                    <div class="d-md-none">
                        <div class="d-grid gap-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">게시글 읽기</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">등급</label>
                                        <select class="form-select form-select-sm grade-select-mobile" name="read_permission" id="read_permission_mobile">
                                            <option value="guest" {{ old('read_permission', $board->read_permission ?? 'guest') === 'guest' ? 'selected' : '' }}>비회원</option>
                                            <option value="user" {{ old('read_permission', $board->read_permission ?? 'guest') === 'user' ? 'selected' : '' }}>회원</option>
                                            <option value="admin" {{ old('read_permission', $board->read_permission ?? 'guest') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold mb-1">포인트</label>
                                        <input type="number" class="form-control form-control-sm" name="read_points" id="read_points_mobile" value="{{ old('read_points', $board->read_points ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">게시글 쓰기</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">등급</label>
                                        <select class="form-select form-select-sm grade-select-mobile" name="write_permission" id="write_permission_mobile">
                                            <option value="guest" {{ old('write_permission', $board->write_permission ?? 'user') === 'guest' ? 'selected' : '' }}>비회원</option>
                                            <option value="user" {{ old('write_permission', $board->write_permission ?? 'user') === 'user' ? 'selected' : '' }}>회원</option>
                                            <option value="admin" {{ old('write_permission', $board->write_permission ?? 'user') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold mb-1">포인트</label>
                                        <input type="number" class="form-control form-control-sm" name="write_points" id="write_points_mobile" value="{{ old('write_points', $board->write_points ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">게시글 삭제</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">등급</label>
                                        <select class="form-select form-select-sm grade-select-mobile" name="delete_permission" id="delete_permission_mobile">
                                            <option value="author" {{ old('delete_permission', $board->delete_permission ?? 'author') === 'author' ? 'selected' : '' }}>작성자 본인</option>
                                            <option value="admin" {{ old('delete_permission', $board->delete_permission ?? 'author') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold mb-1">포인트</label>
                                        <input type="number" class="form-control form-control-sm" name="delete_points" id="delete_points_mobile" value="{{ old('delete_points', $board->delete_points ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">댓글 쓰기</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">등급</label>
                                        <select class="form-select form-select-sm grade-select-mobile" name="comment_permission" id="comment_permission_mobile">
                                            <option value="guest" {{ old('comment_permission', $board->comment_permission ?? 'user') === 'guest' ? 'selected' : '' }}>비회원</option>
                                            <option value="user" {{ old('comment_permission', $board->comment_permission ?? 'user') === 'user' ? 'selected' : '' }}>회원</option>
                                            <option value="admin" {{ old('comment_permission', $board->comment_permission ?? 'user') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold mb-1">포인트</label>
                                        <input type="number" class="form-control form-control-sm" name="comment_points" id="comment_points_mobile" value="{{ old('comment_points', $board->comment_points ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">댓글 삭제</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">등급</label>
                                        <select class="form-select form-select-sm grade-select-mobile" name="comment_delete_permission" id="comment_delete_permission_mobile">
                                            <option value="author" {{ old('comment_delete_permission', $board->comment_delete_permission ?? 'author') === 'author' ? 'selected' : '' }}>작성자 본인</option>
                                            <option value="admin" {{ old('comment_delete_permission', $board->comment_delete_permission ?? 'author') === 'admin' ? 'selected' : '' }}>관리자</option>
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold mb-1">포인트</label>
                                        <input type="number" class="form-control form-control-sm" name="comment_delete_points" id="comment_delete_points_mobile" value="{{ old('comment_delete_points', $board->comment_delete_points ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 주제 관리 -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-tags me-2"></i>주제</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>주제 ({{ $board->topics()->count() }}개)</h6>
                    {{-- 데스크탑 버전 (테이블) --}}
                    <div class="d-none d-md-block">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 200px;">이름</th>
                                    <th style="width: 100px;">색상</th>
                                    <th style="width: 120px;">표시 순서</th>
                                    <th style="width: 80px;">삭제</th>
                                </tr>
                            </thead>
                            <tbody id="topicsTableBody">
                                @foreach($board->topics()->ordered()->get() as $index => $topic)
                                <tr data-topic-id="{{ $topic->id }}" data-display-order="{{ $topic->display_order }}">
                                    <td>
                                        <input type="text" class="form-control form-control-sm topic-name-input" value="{{ $topic->name }}" data-topic-id="{{ $topic->id }}" data-original-name="{{ $topic->name }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <input type="color" class="form-control form-control-color topic-color-input" value="{{ $topic->color }}" style="width: 50px; height: 38px;" data-topic-id="{{ $topic->id }}" data-original-color="{{ $topic->color }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-up-btn" data-topic-id="{{ $topic->id }}" {{ $index === 0 ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <span class="badge bg-secondary">{{ $topic->display_order }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-down-btn" data-topic-id="{{ $topic->id }}" {{ $index === $board->topics()->count() - 1 ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger delete-topic-btn" data-topic-id="{{ $topic->id }}">삭제</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- 모바일 버전 (카드 레이아웃) --}}
                    <div class="d-md-none">
                        <div class="d-grid gap-3" id="topicsCardBody">
                            @foreach($board->topics()->ordered()->get() as $index => $topic)
                            <div class="card shadow-sm" data-topic-id="{{ $topic->id }}" data-display-order="{{ $topic->display_order }}">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">이름</label>
                                        <input type="text" class="form-control form-control-sm topic-name-input" value="{{ $topic->name }}" data-topic-id="{{ $topic->id }}" data-original-name="{{ $topic->name }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">색상</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="form-control form-control-color topic-color-input" value="{{ $topic->color }}" style="width: 80px; height: 50px; cursor: pointer; border: 2px solid #dee2e6; border-radius: 4px;" data-topic-id="{{ $topic->id }}" data-original-color="{{ $topic->color }}">
                                            <span class="small text-muted">색상 선택</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">표시 순서</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-up-btn flex-fill" data-topic-id="{{ $topic->id }}" {{ $index === 0 ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-up me-1"></i>위로
                                            </button>
                                            <span class="badge bg-secondary" style="min-width: 40px; text-align: center;">{{ $topic->display_order }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary move-down-btn flex-fill" data-topic-id="{{ $topic->id }}" {{ $index === $board->topics()->count() - 1 ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-down me-1"></i>아래로
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-danger w-100 delete-topic-btn" data-topic-id="{{ $topic->id }}">
                                            <i class="bi bi-trash me-1"></i>삭제
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <h6>주제 추가</h6>
                    {{-- 데스크탑 버전 (테이블) --}}
                    <div class="d-none d-md-block">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>주제 이름</th>
                                    <th style="width: 100px;">추가</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" id="newTopicName" placeholder="주제 이름을 입력하세요">
                                    </td>
                                    <td style="width: 100px;">
                                        <button type="button" class="btn btn-primary btn-sm w-100" id="addTopicBtn">추가</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- 모바일 버전 (카드 레이아웃) --}}
                    <div class="d-md-none">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <label for="newTopicName_mobile" class="form-label fw-bold mb-2">주제 이름</label>
                                <input type="text" class="form-control form-control-sm mb-3" id="newTopicName_mobile" placeholder="주제 이름을 입력하세요">
                                <button type="button" class="btn btn-primary btn-sm w-100" id="addTopicBtn_mobile">
                                    <i class="bi bi-plus-circle me-1"></i>추가
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-primary" id="saveTopicsBtn">저장</button>
                </div>
            </div>
        </div>

        <!-- 게시글 양식 -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>게시글 양식</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('boards.update-template', ['site' => $site->slug, 'board' => $board->id]) }}" id="templateForm">
                    @csrf
                    @method('POST')
                    <div class="mb-3">
                        <label for="post_template" class="form-label">
                            게시글 양식
                            <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시글 작성 시 먼저 표시될 양식을 입력하세요. 예) 판매자 연락처 :"></i>
                        </label>
                        <textarea class="form-control @error('post_template') is-invalid @enderror" 
                                  id="post_template" 
                                  name="post_template" 
                                  rows="10">{{ old('post_template', $board->post_template) }}</textarea>
                        @error('post_template')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            게시글 작성 시 이 양식이 먼저 표시됩니다.
                        </small>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 기능 ON/OFF -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-toggle-on me-2"></i>기능 ON/OFF</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('boards.update-features', ['site' => $site->slug, 'board' => $board->id]) }}" id="featuresForm">
                    @csrf
                    {{-- 기능 ON/OFF만 저장 --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_anonymous" name="enable_anonymous" value="1" {{ old('enable_anonymous', $board->enable_anonymous) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_anonymous">
                                    익명
                                </label>
                                <small class="d-block text-muted">작성자 이름을 가립니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_secret" name="enable_secret" value="1" {{ old('enable_secret', $board->enable_secret) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_secret">
                                    비밀글
                                </label>
                                <small class="d-block text-muted">제목을 "비밀 글입니다."로 표시합니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="force_secret" name="force_secret" value="1" {{ old('force_secret', $board->force_secret ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="force_secret">
                                    비밀글 상시 활성화
                                </label>
                                <small class="d-block text-muted">모든 게시글이 자동으로 비밀글로 처리됩니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_reply" name="enable_reply" value="1" {{ old('enable_reply', $board->enable_reply) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_reply">
                                    답글
                                </label>
                                <small class="d-block text-muted">게시글 하단에 답글 버튼을 표시합니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                @php
                                    $enableCommentsValue = null;
                                    if (\Illuminate\Support\Facades\Schema::hasColumn('boards', 'enable_comments')) {
                                        $enableCommentsValue = $board->enable_comments;
                                    } else {
                                        $enableCommentsValue = true; // 컬럼이 없으면 기본값 true
                                    }
                                    $enableCommentsChecked = old('enable_comments', $enableCommentsValue) !== false && old('enable_comments', $enableCommentsValue) !== 0 && old('enable_comments', $enableCommentsValue) !== '0' && old('enable_comments', $enableCommentsValue) !== null;
                                @endphp
                                <input type="checkbox" class="form-check-input" id="enable_comments" name="enable_comments" value="1" {{ $enableCommentsChecked ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_comments">
                                    댓글
                                </label>
                                <small class="d-block text-muted">게시글에 댓글 작성 영역을 표시합니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="exclude_from_rss" name="exclude_from_rss" value="1" {{ old('exclude_from_rss', $board->exclude_from_rss) ? 'checked' : '' }}>
                                <label class="form-check-label" for="exclude_from_rss">
                                    RSS제외
                                </label>
                                <small class="d-block text-muted">RSS 피드에서 제외합니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="prevent_drag" name="prevent_drag" value="1" {{ old('prevent_drag', $board->prevent_drag) ? 'checked' : '' }}>
                                <label class="form-check-label" for="prevent_drag">
                                    드래그 방지
                                </label>
                                <small class="d-block text-muted">게시판 내용을 드래그할 수 없게 합니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_attachments" name="enable_attachments" value="1" {{ old('enable_attachments', $board->enable_attachments ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_attachments">
                                    첨부파일
                                </label>
                                <small class="d-block text-muted">첨부파일 업로드 기능을 표시합니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                @php
                                    $enableShareValue = false;
                                    if (\Illuminate\Support\Facades\Schema::hasColumn('boards', 'enable_share')) {
                                        $enableShareValue = $board->enable_share;
                                    }
                                @endphp
                                <input type="checkbox" class="form-check-input" id="enable_share" name="enable_share" value="1" {{ old('enable_share', $enableShareValue) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_share">
                                    공유 기능
                                </label>
                                <small class="d-block text-muted">게시글 하단에 소셜 공유 버튼을 표시합니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_author_comment_adopt" name="enable_author_comment_adopt" value="1" {{ old('enable_author_comment_adopt', $board->enable_author_comment_adopt) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_author_comment_adopt">
                                    작성자 댓글 채택
                                </label>
                                <small class="d-block text-muted">작성자가 댓글을 채택할 수 있습니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_admin_comment_adopt" name="enable_admin_comment_adopt" value="1" {{ old('enable_admin_comment_adopt', $board->enable_admin_comment_adopt) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_admin_comment_adopt">
                                    운영자 댓글 채택
                                </label>
                                <small class="d-block text-muted">운영자가 댓글을 채택할 수 있습니다.</small>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 게시판 하단 -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>게시판 하단</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('boards.update-footer', ['site' => $site->slug, 'board' => $board->id]) }}" id="footerForm">
                    @csrf
                    @method('POST')
                    <div class="mb-3">
                        <label for="footer_content" class="form-label">
                            설명
                            <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="게시판 하단(푸터 바로 앞)에 표시될 내용을 입력하세요"></i>
                        </label>
                        <textarea class="form-control @error('footer_content') is-invalid @enderror" 
                                  id="footer_content" 
                                  name="footer_content" 
                                  rows="10">{{ old('footer_content', $board->footer_content) }}</textarea>
                        @error('footer_content')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            게시판 하단에 이 내용이 표시됩니다.
                        </small>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">삭제 확인</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMessage">정말 삭제하시겠습니까?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">확인</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // 초기 로드 시에도 체크 (create.blade.php와 동일한 구조)
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const randomOrderRow = document.getElementById('random-order-row');
        const eventDisplayTypeRow = document.getElementById('event-display-type-row');
        const summaryLengthRow = document.getElementById('summary-length-row');
        const pinterestColumnsRow = document.getElementById('pinterest-columns-row');
        const qaStatusesRow = document.getElementById('qa-statuses-row');
        
        if (typeSelect) {
            if (typeSelect.value === 'bookmark') {
                if (randomOrderRow) randomOrderRow.style.display = '';
            } else {
                if (randomOrderRow) randomOrderRow.style.display = 'none';
            }
            
            if (typeSelect.value === 'event') {
                if (eventDisplayTypeRow) eventDisplayTypeRow.style.display = '';
            } else {
                if (eventDisplayTypeRow) eventDisplayTypeRow.style.display = 'none';
            }
            
            if (typeSelect.value === 'blog') {
                if (summaryLengthRow) summaryLengthRow.style.display = '';
            } else {
                if (summaryLengthRow) summaryLengthRow.style.display = 'none';
            }
            
            if (typeSelect.value === 'pinterest') {
                if (pinterestColumnsRow) pinterestColumnsRow.style.display = '';
            } else {
                if (pinterestColumnsRow) pinterestColumnsRow.style.display = 'none';
            }
            
            if (typeSelect.value === 'qa') {
                if (qaStatusesRow) qaStatusesRow.style.display = '';
            } else {
                if (qaStatusesRow) qaStatusesRow.style.display = 'none';
            }
        }

        // Tooltip 초기화
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // 상단 이미지 미리보기 (create.blade.php와 정확히 동일)
        const headerImageInput = document.getElementById('header_image');
        const headerImageFilename = document.getElementById('header_image_filename');
        
        if (headerImageInput) {
            headerImageInput.addEventListener('change', function(e) {
                console.log('Header image input changed');
                const file = e.target.files[0];
                if (file) {
                    console.log('File selected:', file.name);
                    // 파일명 표시
                    if (headerImageFilename) {
                        headerImageFilename.value = file.name;
                        console.log('Filename set to:', headerImageFilename.value);
                    }
                    
                    // 기존 이미지 숨기기
                    const existingImageDiv = headerImageInput.closest('td').querySelector('.mb-2');
                    if (existingImageDiv) {
                        existingImageDiv.style.display = 'none';
                        console.log('Existing image hidden');
                    }
                    
                    // 미리보기
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        console.log('FileReader loaded');
                        const previewImg = document.getElementById('header_image_preview_img');
                        const previewDiv = document.getElementById('header_image_preview');
                        console.log('Preview elements:', { img: previewImg, div: previewDiv });
                        if (previewImg && previewDiv) {
                            previewImg.src = e.target.result;
                            previewDiv.style.display = 'block';
                            console.log('Preview displayed');
                        } else {
                            console.error('Preview elements not found');
                        }
                    };
                    reader.onerror = function(error) {
                        console.error('FileReader error:', error);
                    };
                    reader.readAsDataURL(file);
                } else {
                    console.log('No file selected');
                    if (headerImageFilename) {
                        headerImageFilename.value = '';
                    }
                    const previewDiv = document.getElementById('header_image_preview');
                    if (previewDiv) {
                        previewDiv.style.display = 'none';
                    }
                }
            });
        } else {
            console.error('Header image input not found');
        }
        
        // 체크박스 변경 시 hidden input 즉시 업데이트
        const randomOrderCheckbox = document.getElementById('random_order');
        const randomOrderHidden = document.getElementById('random_order_hidden');
        if (randomOrderCheckbox && randomOrderHidden) {
            randomOrderCheckbox.addEventListener('change', function() {
                randomOrderHidden.value = this.checked ? '1' : '0';
            });
        }
        
        const allowMultipleTopicsCheckbox = document.getElementById('allow_multiple_topics');
        const allowMultipleTopicsHidden = document.getElementById('allow_multiple_topics_hidden');
        if (allowMultipleTopicsCheckbox && allowMultipleTopicsHidden) {
            allowMultipleTopicsCheckbox.addEventListener('change', function() {
                allowMultipleTopicsHidden.value = this.checked ? '1' : '0';
            });
        }
        
        const removeLinksCheckbox = document.getElementById('remove_links');
        const removeLinksHidden = document.getElementById('remove_links_hidden');
        if (removeLinksCheckbox && removeLinksHidden) {
            removeLinksCheckbox.addEventListener('change', function() {
                removeLinksHidden.value = this.checked ? '1' : '0';
            });
        }
        
        const enableLikesCheckbox = document.getElementById('enable_likes');
        const enableLikesHidden = document.getElementById('enable_likes_hidden');
        if (enableLikesCheckbox && enableLikesHidden) {
            enableLikesCheckbox.addEventListener('change', function() {
                enableLikesHidden.value = this.checked ? '1' : '0';
            });
        }
        
        const savedPostsEnabledCheckbox = document.getElementById('saved_posts_enabled');
        const savedPostsEnabledHidden = document.getElementById('saved_posts_enabled_hidden');
        if (savedPostsEnabledCheckbox && savedPostsEnabledHidden) {
            savedPostsEnabledCheckbox.addEventListener('change', function() {
                savedPostsEnabledHidden.value = this.checked ? '1' : '0';
            });
        }
        
        // 게시판 제목 및 설명 숨기기 체크박스 처리 (데스크탑)
        const hideTitleDescriptionCheckbox = document.getElementById('hide_title_description');
        const hideTitleDescriptionHidden = document.getElementById('hide_title_description_hidden');
        if (hideTitleDescriptionCheckbox && hideTitleDescriptionHidden) {
            // 초기값 설정
            hideTitleDescriptionHidden.value = hideTitleDescriptionCheckbox.checked ? '1' : '0';
            hideTitleDescriptionCheckbox.addEventListener('change', function() {
                hideTitleDescriptionHidden.value = this.checked ? '1' : '0';
                console.log('hide_title_description updated:', hideTitleDescriptionHidden.value);
            });
            // 클릭 이벤트도 추가 (change 이벤트가 발생하지 않을 수 있음)
            hideTitleDescriptionCheckbox.addEventListener('click', function() {
                setTimeout(() => {
                    hideTitleDescriptionHidden.value = this.checked ? '1' : '0';
                    console.log('hide_title_description updated on click:', hideTitleDescriptionHidden.value);
                }, 0);
            });
        }
        
        // 게시판 제목 및 설명 숨기기 체크박스 처리 (모바일)
        const hideTitleDescriptionCheckboxMobile = document.getElementById('hide_title_description_mobile');
        const hideTitleDescriptionHiddenMobile = document.getElementById('hide_title_description_hidden_mobile');
        if (hideTitleDescriptionCheckboxMobile && hideTitleDescriptionHiddenMobile) {
            // 초기값 설정
            hideTitleDescriptionHiddenMobile.value = hideTitleDescriptionCheckboxMobile.checked ? '1' : '0';
            hideTitleDescriptionCheckboxMobile.addEventListener('change', function() {
                hideTitleDescriptionHiddenMobile.value = this.checked ? '1' : '0';
            });
            // 클릭 이벤트도 추가
            hideTitleDescriptionCheckboxMobile.addEventListener('click', function() {
                setTimeout(() => {
                    hideTitleDescriptionHiddenMobile.value = this.checked ? '1' : '0';
                }, 0);
            });
        }
        
        // 모바일 저장하기 체크박스 처리
        const savedPostsEnabledCheckboxMobile = document.getElementById('saved_posts_enabled_mobile');
        const savedPostsEnabledHiddenMobile = document.getElementById('saved_posts_enabled_hidden_mobile');
        if (savedPostsEnabledCheckboxMobile && savedPostsEnabledHiddenMobile) {
            savedPostsEnabledCheckboxMobile.addEventListener('change', function() {
                savedPostsEnabledHiddenMobile.value = this.checked ? '1' : '0';
            });
        }
        
        // 모바일 글 삭제 날짜 동기화
        const deleteStartDateMobile = document.getElementById('delete_start_date_mobile');
        const deleteEndDateMobile = document.getElementById('delete_end_date_mobile');
        const deleteStartDate = document.getElementById('delete_start_date');
        const deleteEndDate = document.getElementById('delete_end_date');
        if (deleteStartDateMobile && deleteStartDate) {
            deleteStartDateMobile.addEventListener('change', function() {
                deleteStartDate.value = this.value;
            });
            deleteStartDate.addEventListener('change', function() {
                deleteStartDateMobile.value = this.value;
            });
        }
        if (deleteEndDateMobile && deleteEndDate) {
            deleteEndDateMobile.addEventListener('change', function() {
                deleteEndDate.value = this.value;
            });
            deleteEndDate.addEventListener('change', function() {
                deleteEndDateMobile.value = this.value;
            });
        }
        
        // 중복 제거 - 이미 위에서 처리됨
    });
    
    // 게시판 타입 변경 시 랜덤배치 체크박스 및 이벤트 표시 타입 표시/숨김
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const randomOrderRow = document.getElementById('random-order-row');
        const eventDisplayTypeRow = document.getElementById('event-display-type-row');
        const summaryLengthRow = document.getElementById('summary-length-row');
        const pinterestColumnsRow = document.getElementById('pinterest-columns-row');
        const qaStatusesRow = document.getElementById('qa-statuses-row');
        
        function toggleTypeSpecificFields() {
            if (typeSelect.value === 'bookmark') {
                if (randomOrderRow) randomOrderRow.style.display = '';
            } else {
                if (randomOrderRow) randomOrderRow.style.display = 'none';
                const randomOrderCheckbox = document.getElementById('random_order');
                if (randomOrderCheckbox) {
                    randomOrderCheckbox.checked = false;
                    const randomOrderHidden = document.getElementById('random_order_hidden');
                    if (randomOrderHidden) {
                        randomOrderHidden.value = '0';
                    }
                }
            }
            
            if (typeSelect.value === 'event') {
                if (eventDisplayTypeRow) eventDisplayTypeRow.style.display = '';
            } else {
                if (eventDisplayTypeRow) eventDisplayTypeRow.style.display = 'none';
            }
            
            if (typeSelect.value === 'blog') {
                if (summaryLengthRow) summaryLengthRow.style.display = '';
            } else {
                if (summaryLengthRow) summaryLengthRow.style.display = 'none';
            }
            
            if (typeSelect.value === 'pinterest') {
                if (pinterestColumnsRow) pinterestColumnsRow.style.display = '';
            } else {
                if (pinterestColumnsRow) pinterestColumnsRow.style.display = 'none';
            }
            
            if (typeSelect.value === 'qa') {
                if (qaStatusesRow) qaStatusesRow.style.display = '';
            } else {
                if (qaStatusesRow) qaStatusesRow.style.display = 'none';
            }
        }
        
        if (typeSelect) {
            typeSelect.addEventListener('change', toggleTypeSpecificFields);
            // 초기 로드 시에도 체크
            toggleTypeSpecificFields();
        }
    });
    
    // 각 폼을 개별적으로 제출
    document.getElementById('generalForm').addEventListener('submit', function(e) {
        e.preventDefault(); // 기본 제출 방지
        
        // 체크박스 값 처리 (hidden input 업데이트) - 제출 전 최종 확인
        const randomOrderCheckbox = document.getElementById('random_order');
        const randomOrderHidden = document.getElementById('random_order_hidden');
        if (randomOrderCheckbox && randomOrderHidden) {
            randomOrderHidden.value = randomOrderCheckbox.checked ? '1' : '0';
            console.log('random_order_hidden updated:', randomOrderHidden.value);
        }
        
        const allowMultipleTopicsCheckbox = document.getElementById('allow_multiple_topics');
        const allowMultipleTopicsHidden = document.getElementById('allow_multiple_topics_hidden');
        if (allowMultipleTopicsCheckbox && allowMultipleTopicsHidden) {
            allowMultipleTopicsHidden.value = allowMultipleTopicsCheckbox.checked ? '1' : '0';
            console.log('allow_multiple_topics_hidden updated:', allowMultipleTopicsHidden.value);
        }
        
        const removeLinksCheckbox = document.getElementById('remove_links');
        const removeLinksHidden = document.getElementById('remove_links_hidden');
        if (removeLinksCheckbox && removeLinksHidden) {
            removeLinksHidden.value = removeLinksCheckbox.checked ? '1' : '0';
            console.log('remove_links_hidden updated:', removeLinksHidden.value);
        }
        
        const enableLikesCheckbox = document.getElementById('enable_likes');
        const enableLikesHidden = document.getElementById('enable_likes_hidden');
        if (enableLikesCheckbox && enableLikesHidden) {
            enableLikesHidden.value = enableLikesCheckbox.checked ? '1' : '0';
            console.log('enable_likes_hidden updated:', enableLikesHidden.value);
        }
        
        const savedPostsEnabledCheckbox = document.getElementById('saved_posts_enabled');
        const savedPostsEnabledHidden = document.getElementById('saved_posts_enabled_hidden');
        if (savedPostsEnabledCheckbox && savedPostsEnabledHidden) {
            // 제출 전 최종 확인 및 업데이트
            savedPostsEnabledHidden.value = savedPostsEnabledCheckbox.checked ? '1' : '0';
            console.log('saved_posts_enabled_hidden updated before submit:', savedPostsEnabledHidden.value);
        }
        
        // 게시판 제목 및 설명 숨기기 체크박스 처리 (데스크탑 또는 모바일) - 제출 전 강제 업데이트
        const hideTitleDescriptionCheckbox = document.getElementById('hide_title_description');
        const hideTitleDescriptionCheckboxMobile = document.getElementById('hide_title_description_mobile');
        const hideTitleDescriptionHidden = document.getElementById('hide_title_description_hidden');
        const hideTitleDescriptionHiddenMobile = document.getElementById('hide_title_description_hidden_mobile');
        
        // 체크박스 상태를 확인하여 hidden input 강제 업데이트
        if (hideTitleDescriptionCheckbox) {
            const checked = hideTitleDescriptionCheckbox.checked;
            if (hideTitleDescriptionHidden) {
                hideTitleDescriptionHidden.value = checked ? '1' : '0';
            }
            console.log('hide_title_description_hidden updated before submit (desktop):', checked, hideTitleDescriptionHidden?.value);
        }
        if (hideTitleDescriptionCheckboxMobile) {
            const checked = hideTitleDescriptionCheckboxMobile.checked;
            if (hideTitleDescriptionHiddenMobile) {
                hideTitleDescriptionHiddenMobile.value = checked ? '1' : '0';
            }
            console.log('hide_title_description_hidden_mobile updated before submit:', checked, hideTitleDescriptionHiddenMobile?.value);
        }
        
        // FormData 생성 전에 모든 hidden input 업데이트 완료 확인
        // FormData는 생성 시점의 폼 상태를 캡처하므로, 이전에 모든 값을 업데이트해야 함
        
        // 게시판 제목 및 설명 숨기기 값 명시적으로 추가 (체크박스 상태 직접 확인)
        const hideTitleDescriptionCheckbox = document.getElementById('hide_title_description');
        const hideTitleDescriptionCheckboxMobile = document.getElementById('hide_title_description_mobile');
        const hideTitleDescriptionHidden = document.getElementById('hide_title_description_hidden');
        const hideTitleDescriptionHiddenMobile = document.getElementById('hide_title_description_hidden_mobile');
        
        // 체크박스 상태를 직접 확인하여 hidden input 업데이트
        if (hideTitleDescriptionCheckbox) {
            if (hideTitleDescriptionHidden) {
                hideTitleDescriptionHidden.value = hideTitleDescriptionCheckbox.checked ? '1' : '0';
            }
        } else if (hideTitleDescriptionCheckboxMobile) {
            if (hideTitleDescriptionHiddenMobile) {
                hideTitleDescriptionHiddenMobile.value = hideTitleDescriptionCheckboxMobile.checked ? '1' : '0';
            }
        }
        
        // AJAX로 제출 (FormData는 hidden input 업데이트 후 생성)
        const formData = new FormData(this);
        
        // 체크박스 상태를 직접 확인하여 FormData에 명시적으로 추가
        // hidden input 값과 체크박스 상태를 모두 확인하여 더 확실하게 처리
        let hideTitleDescriptionValue = '0';
        if (hideTitleDescriptionCheckbox) {
            hideTitleDescriptionValue = hideTitleDescriptionCheckbox.checked ? '1' : '0';
            // hidden input도 동기화
            if (hideTitleDescriptionHidden) {
                hideTitleDescriptionHidden.value = hideTitleDescriptionValue;
            }
        } else if (hideTitleDescriptionCheckboxMobile) {
            hideTitleDescriptionValue = hideTitleDescriptionCheckboxMobile.checked ? '1' : '0';
            // hidden input도 동기화
            if (hideTitleDescriptionHiddenMobile) {
                hideTitleDescriptionHiddenMobile.value = hideTitleDescriptionValue;
            }
        } else {
            // 체크박스가 없으면 hidden input 값 사용
            hideTitleDescriptionValue = hideTitleDescriptionHidden?.value || hideTitleDescriptionHiddenMobile?.value || '0';
        }
        
        // FormData에 명시적으로 추가 (기존 값이 있어도 덮어쓰기)
        formData.set('hide_title_description', hideTitleDescriptionValue);
        console.log('hide_title_description set in formData:', hideTitleDescriptionValue, 'checkbox checked:', hideTitleDescriptionCheckbox?.checked || hideTitleDescriptionCheckboxMobile?.checked, 'from hidden:', hideTitleDescriptionHidden?.value, hideTitleDescriptionHiddenMobile?.value);
        
        // 핀터레스트 컬럼 필드가 있으면 명시적으로 추가 (숨겨져 있어도 포함되도록)
        const pinterestColumnsMobile = document.getElementById('pinterest_columns_mobile');
        const pinterestColumnsTablet = document.getElementById('pinterest_columns_tablet');
        const pinterestColumnsDesktop = document.getElementById('pinterest_columns_desktop');
        const pinterestColumnsLarge = document.getElementById('pinterest_columns_large');
        
        // 핀터레스트 필드가 폼에 있으면 명시적으로 추가
        // display: none인 필드도 FormData에 포함되도록 명시적으로 추가
        if (pinterestColumnsMobile && pinterestColumnsMobile.form === this) {
            // 폼에 있으면 set()으로 업데이트
            formData.set('pinterest_columns_mobile', pinterestColumnsMobile.value || '2');
        } else if (pinterestColumnsMobile) {
            // 폼에 없으면 append()로 추가
            formData.append('pinterest_columns_mobile', pinterestColumnsMobile.value || '2');
        }
        if (pinterestColumnsTablet && pinterestColumnsTablet.form === this) {
            formData.set('pinterest_columns_tablet', pinterestColumnsTablet.value || '3');
        } else if (pinterestColumnsTablet) {
            formData.append('pinterest_columns_tablet', pinterestColumnsTablet.value || '3');
        }
        if (pinterestColumnsDesktop && pinterestColumnsDesktop.form === this) {
            formData.set('pinterest_columns_desktop', pinterestColumnsDesktop.value || '4');
        } else if (pinterestColumnsDesktop) {
            formData.append('pinterest_columns_desktop', pinterestColumnsDesktop.value || '4');
        }
        if (pinterestColumnsLarge && pinterestColumnsLarge.form === this) {
            formData.set('pinterest_columns_large', pinterestColumnsLarge.value || '6');
        } else if (pinterestColumnsLarge) {
            formData.append('pinterest_columns_large', pinterestColumnsLarge.value || '6');
        }
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('일반 설정이 저장되었습니다.');
                // 페이지 리로드하여 최신 상태 반영
                window.location.reload();
            } else {
                alert(data.message || '저장 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다.');
        });
    });

    // 기능 ON/OFF 폼 제출 처리
    document.getElementById('featuresForm').addEventListener('submit', function(e) {
        // 체크박스 값 처리 (hidden input 업데이트)
        const enableAnonymousCheckbox = document.getElementById('enable_anonymous');
        const enableSecretCheckbox = document.getElementById('enable_secret');
        const forceSecretCheckbox = document.getElementById('force_secret');
        const enableReplyCheckbox = document.getElementById('enable_reply');
        const enableCommentsCheckbox = document.getElementById('enable_comments');
        const excludeFromRssCheckbox = document.getElementById('exclude_from_rss');
        const preventDragCheckbox = document.getElementById('prevent_drag');
        const enableAttachmentsCheckbox = document.getElementById('enable_attachments');
        const enableShareCheckbox = document.getElementById('enable_share');
        const enableAuthorCommentAdoptCheckbox = document.getElementById('enable_author_comment_adopt');
        const enableAdminCommentAdoptCheckbox = document.getElementById('enable_admin_comment_adopt');
        
        // 체크박스가 체크되지 않았을 때 hidden input 추가
        const form = this;
        
        // 일반 설정 필드는 제외하고 기능 ON/OFF 필드만 전송
        // FormData를 새로 생성하여 필요한 필드만 추가
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        
        // 기능 ON/OFF 체크박스 값만 설정
        formData.append('enable_anonymous', enableAnonymousCheckbox?.checked ? '1' : '0');
        formData.append('enable_secret', enableSecretCheckbox?.checked ? '1' : '0');
        formData.append('force_secret', forceSecretCheckbox?.checked ? '1' : '0');
        formData.append('enable_reply', enableReplyCheckbox?.checked ? '1' : '0');
        // enable_comments는 항상 명시적으로 설정
        const enableCommentsValue = enableCommentsCheckbox?.checked ? '1' : '0';
        formData.append('enable_comments', enableCommentsValue);
        formData.append('exclude_from_rss', excludeFromRssCheckbox?.checked ? '1' : '0');
        formData.append('prevent_drag', preventDragCheckbox?.checked ? '1' : '0');
        formData.append('enable_attachments', enableAttachmentsCheckbox?.checked ? '1' : '0');
        formData.append('enable_share', enableShareCheckbox?.checked ? '1' : '0');
        formData.append('enable_author_comment_adopt', enableAuthorCommentAdoptCheckbox?.checked ? '1' : '0');
        formData.append('enable_admin_comment_adopt', enableAdminCommentAdoptCheckbox?.checked ? '1' : '0');
        const savedPostsEnabledCheckbox = document.getElementById('saved_posts_enabled');
        const savedPostsEnabledHidden = document.getElementById('saved_posts_enabled_hidden');
        if (savedPostsEnabledCheckbox && savedPostsEnabledHidden) {
            savedPostsEnabledHidden.value = savedPostsEnabledCheckbox.checked ? '1' : '0';
        }
        formData.append('saved_posts_enabled', savedPostsEnabledHidden?.value || '0');
        
        // 게시판 제목 및 설명 숨기기는 이미 위에서 설정됨 (중복 제거)
        
        // AJAX로 제출하여 페이지 리로드 방지
        e.preventDefault();
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 성공 메시지 표시 (기능 ON/OFF 저장됨)
                alert('기능 설정이 저장되었습니다.');
                // 페이지 리로드하여 최신 상태 반영
                window.location.reload();
            } else {
                alert(data.message || '저장 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다.');
        });
    });

    document.getElementById('seoForm').addEventListener('submit', function(e) {
        // 폼 제출은 그대로 진행
    });

    document.getElementById('gradePointsForm').addEventListener('submit', function(e) {
        // 폼 제출은 그대로 진행
    });

    function confirmDeletePosts() {
        const startDate = document.getElementById('delete_start_date')?.value || document.getElementById('delete_start_date_mobile')?.value;
        const endDate = document.getElementById('delete_end_date')?.value || document.getElementById('delete_end_date_mobile')?.value;
        
        if (!startDate || !endDate) {
            alert('시작일과 종료일을 모두 선택해주세요.');
            return;
        }
        
        document.getElementById('deleteConfirmMessage').textContent = 
            `${startDate}부터 ${endDate}까지의 게시글을 모두 삭제하시겠습니까?`;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
        
        document.getElementById('confirmDeleteBtn').onclick = function() {
            const formData = new FormData();
            formData.append('start_date', startDate);
            formData.append('end_date', endDate);
            formData.append('_token', '{{ csrf_token() }}');
            
            fetch('{{ route("boards.delete-posts", ["site" => $site->slug, "board" => $board->id]) }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || '게시글이 삭제되었습니다.');
                    window.location.reload();
                } else {
                    alert(data.message || '삭제 중 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('삭제 중 오류가 발생했습니다.');
            });
            
            modal.hide();
        };
    }

    function confirmDeleteBoard() {
        document.getElementById('deleteConfirmMessage').textContent = 
            '정말 이 게시판과 모든 게시글을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.';
        
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
        
        document.getElementById('confirmDeleteBtn').onclick = function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("boards.destroy", ["site" => $site->slug, "board" => $board->id]) }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        };
    }

    // 주제 관리 함수들 (전역 스코프에 명시적으로 등록)
    window.addTopic = function() {
        console.log('addTopic called');
        // 모바일과 데스크탑 입력 필드 모두 확인
        const nameInput = document.getElementById('newTopicName') || document.getElementById('newTopicName_mobile');
        if (!nameInput) {
            console.error('newTopicName input not found');
            alert('주제 입력 필드를 찾을 수 없습니다.');
            return;
        }
        
        const name = nameInput.value.trim();
        if (!name) {
            alert('주제 이름을 입력해주세요.');
            return;
        }

        const formData = new FormData();
        formData.append('name', name);
        formData.append('_token', '{{ csrf_token() }}');

        const url = '{{ route("boards.topics.store", ["site" => $site->slug, "board" => $board->id]) }}';
        console.log('Sending request to:', url);

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || '주제 추가 중 오류가 발생했습니다.');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                alert('주제가 추가되었습니다.');
                // 입력 필드 초기화
                if (document.getElementById('newTopicName')) {
                    document.getElementById('newTopicName').value = '';
                }
                if (document.getElementById('newTopicName_mobile')) {
                    document.getElementById('newTopicName_mobile').value = '';
                }
                window.location.reload();
            } else {
                alert(data.message || '주제 추가 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || '주제 추가 중 오류가 발생했습니다.');
        });
    };
    
    // 전역 스코프에 함수가 등록되었는지 확인
    console.log('addTopic function registered:', typeof window.addTopic);

    // 위로 이동 (데스크탑 + 모바일)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.move-up-btn')) {
            const btn = e.target.closest('.move-up-btn');
            const topicId = btn.dataset.topicId;
            
            // 데스크탑 테이블
            const row = btn.closest('tr');
            if (row) {
                const prevRow = row.previousElementSibling;
                if (prevRow) {
                    const tbody = row.parentElement;
                    tbody.insertBefore(row, prevRow);
                    updateMoveButtons();
                }
            }
            
            // 모바일 카드
            const card = btn.closest('.card[data-topic-id]');
            if (card) {
                const prevCard = card.previousElementSibling;
                if (prevCard && prevCard.classList.contains('card')) {
                    const container = card.parentElement;
                    container.insertBefore(card, prevCard);
                    updateMoveButtonsMobile();
                }
            }
        }
        
        // 아래로 이동 (데스크탑 + 모바일)
        if (e.target.closest('.move-down-btn')) {
            const btn = e.target.closest('.move-down-btn');
            const topicId = btn.dataset.topicId;
            
            // 데스크탑 테이블
            const row = btn.closest('tr');
            if (row) {
                const nextRow = row.nextElementSibling;
                if (nextRow) {
                    const tbody = row.parentElement;
                    tbody.insertBefore(nextRow, row);
                    updateMoveButtons();
                }
            }
            
            // 모바일 카드
            const card = btn.closest('.card[data-topic-id]');
            if (card) {
                const nextCard = card.nextElementSibling;
                if (nextCard && nextCard.classList.contains('card')) {
                    const container = card.parentElement;
                    container.insertBefore(nextCard, card);
                    updateMoveButtonsMobile();
                }
            }
        }
        
        // 삭제 버튼 (즉시 삭제) - 데스크탑 + 모바일
        if (e.target.closest('.delete-topic-btn')) {
            const btn = e.target.closest('.delete-topic-btn');
            const topicId = btn.dataset.topicId;
            
            if (!confirm('정말 이 주제를 삭제하시겠습니까?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'DELETE');
            
            fetch(`{{ route("boards.topics.destroy", ["site" => $site->slug, "board" => $board->id, "topic" => ":id"]) }}`.replace(':id', topicId), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || '주제 삭제 중 오류가 발생했습니다.');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || '주제 삭제 중 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || '주제 삭제 중 오류가 발생했습니다.');
            });
        }
    });

    // 위/아래 버튼 상태 업데이트 (데스크탑)
    function updateMoveButtons() {
        const rows = document.querySelectorAll('#topicsTableBody tr');
        rows.forEach((row, index) => {
            const upBtn = row.querySelector('.move-up-btn');
            const downBtn = row.querySelector('.move-down-btn');
            const badge = row.querySelector('.badge');
            
            if (upBtn) {
                upBtn.disabled = index === 0;
            }
            if (downBtn) {
                downBtn.disabled = index === rows.length - 1;
            }
            if (badge) {
                badge.textContent = index + 1;
            }
        });
    }
    
    // 위/아래 버튼 상태 업데이트 (모바일)
    function updateMoveButtonsMobile() {
        const cards = document.querySelectorAll('#topicsCardBody .card[data-topic-id]');
        cards.forEach((card, index) => {
            const upBtn = card.querySelector('.move-up-btn');
            const downBtn = card.querySelector('.move-down-btn');
            const badge = card.querySelector('.badge');
            
            if (upBtn) {
                upBtn.disabled = index === 0;
            }
            if (downBtn) {
                downBtn.disabled = index === cards.length - 1;
            }
            if (badge) {
                badge.textContent = index + 1;
            }
        });
    }
    
    // 페이지 로드 시 모바일 버튼 상태 업데이트
    document.addEventListener('DOMContentLoaded', function() {
        updateMoveButtons();
        updateMoveButtonsMobile();
        
        // 모바일과 데스크탑 주제 추가 입력 필드 동기화
        const newTopicNameDesktop = document.getElementById('newTopicName');
        const newTopicNameMobile = document.getElementById('newTopicName_mobile');
        
        if (newTopicNameDesktop && newTopicNameMobile) {
            newTopicNameDesktop.addEventListener('input', function() {
                newTopicNameMobile.value = this.value;
            });
            newTopicNameMobile.addEventListener('input', function() {
                newTopicNameDesktop.value = this.value;
            });
        }
    });

    // 저장 버튼 클릭 시 모든 변경사항 저장 (데스크탑 + 모바일)
    document.getElementById('saveTopicsBtn').addEventListener('click', function() {
        const isMobile = window.innerWidth < 768;
        const updates = [];
        
        // 새 주제 추가 확인 (모바일과 데스크탑 모두)
        const newTopicName = (document.getElementById('newTopicName') || document.getElementById('newTopicName_mobile'))?.value.trim();
        
        if (newTopicName) {
            addTopic();
            return;
        }
        
        if (isMobile) {
            // 모바일 카드에서 데이터 수집
            const cards = document.querySelectorAll('#topicsCardBody .card[data-topic-id]');
            cards.forEach((card, index) => {
                const topicId = card.dataset.topicId;
                const nameInput = card.querySelector('.topic-name-input');
                const colorInput = card.querySelector('.topic-color-input');
                
                if (nameInput && colorInput && topicId) {
                    const name = nameInput.value.trim();
                    const color = colorInput.value;
                    const originalName = nameInput.dataset.originalName;
                    const originalColor = colorInput.dataset.originalColor;
                    const newDisplayOrder = index + 1;
                    const originalDisplayOrder = parseInt(card.dataset.displayOrder);
                    
                    if (!name) {
                        alert('주제 이름을 입력해주세요.');
                        return;
                    }
                    
                    if (name !== originalName || color !== originalColor || newDisplayOrder !== originalDisplayOrder) {
                        updates.push({
                            id: topicId,
                            name: name,
                            color: color,
                            display_order: newDisplayOrder
                        });
                    }
                }
            });
        } else {
            // 데스크탑 테이블에서 데이터 수집
            const rows = document.querySelectorAll('#topicsTableBody tr');
            rows.forEach((row, index) => {
                const topicId = row.dataset.topicId;
                const nameInput = row.querySelector('.topic-name-input');
                const colorInput = row.querySelector('.topic-color-input');
                
                if (nameInput && colorInput && topicId) {
                    const name = nameInput.value.trim();
                    const color = colorInput.value;
                    const originalName = nameInput.dataset.originalName;
                    const originalColor = colorInput.dataset.originalColor;
                    const newDisplayOrder = index + 1;
                    const originalDisplayOrder = parseInt(row.dataset.displayOrder);
                    
                    if (!name) {
                        alert('주제 이름을 입력해주세요.');
                        return;
                    }
                    
                    if (name !== originalName || color !== originalColor || newDisplayOrder !== originalDisplayOrder) {
                        updates.push({
                            id: topicId,
                            name: name,
                            color: color,
                            display_order: newDisplayOrder
                        });
                    }
                }
            });
        }
        
        // 업데이트가 없으면 저장할 내용 없음
        if (updates.length === 0) {
            alert('변경된 내용이 없습니다.');
            return;
        }
        
        // 모든 업데이트를 순차적으로 처리
        let updatePromises = updates.map(update => {
            const formData = new FormData();
            formData.append('name', update.name);
            formData.append('color', update.color);
            formData.append('display_order', update.display_order);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');
            
            return fetch(`{{ route("boards.topics.update", ["site" => $site->slug, "board" => $board->id, "topic" => ":id"]) }}`.replace(':id', update.id), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            }).then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || '주제 수정 중 오류가 발생했습니다.');
                    });
                }
                return response.json();
            });
        });
        
        Promise.all(updatePromises)
            .then(results => {
                // 모든 업데이트가 성공하면 페이지 리로드
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || '주제 저장 중 오류가 발생했습니다.');
            });
    });


    // 주제 추가 버튼 이벤트 리스너
    $(document).ready(function() {
        // 주제 추가 버튼 (데스크탑 + 모바일)
        $(document).on('click', '#addTopicBtn, #addTopicBtn_mobile', function(e) {
            e.preventDefault();
            // 모바일과 데스크탑 입력 필드 모두 확인
            const nameInput = $('#newTopicName').length ? $('#newTopicName') : $('#newTopicName_mobile');
            const name = nameInput.val().trim();
            
            if (!name) {
                alert('주제 이름을 입력해주세요.');
                return false;
            }

            const formData = new FormData();
            formData.append('name', name);
            formData.append('_token', '{{ csrf_token() }}');

            const url = '{{ route("boards.topics.store", ["site" => $site->slug, "board" => $board->id]) }}';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || '주제 추가 중 오류가 발생했습니다.');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('주제가 추가되었습니다.');
                    // 입력 필드 초기화
                    $('#newTopicName').val('');
                    $('#newTopicName_mobile').val('');
                    window.location.reload();
                } else {
                    alert(data.message || '주제 추가 중 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || '주제 추가 중 오류가 발생했습니다.');
            });
            
            return false;
        });
        
        // Summernote 초기화 (게시글 양식)
        $('#post_template').summernote({
            height: 300,
            lang: 'ko-KR',
            placeholder: '예) 판매자 연락처 :',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            fontNames: ['맑은 고딕', 'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Times', 'Times New Roman', 'Verdana'],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '36', '48', '64', '82', '150']
        });
        
        // Summernote 초기화 (게시판 하단)
        $('#footer_content').summernote({
            height: 300,
            lang: 'ko-KR',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            fontNames: ['맑은 고딕', 'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Times', 'Times New Roman', 'Verdana'],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '36', '48', '64', '82', '150']
        });
    });
    
    // 게시글 양식 폼 제출
    document.getElementById('templateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitTemplateForm();
    });
    
    function submitTemplateForm() {
        const formData = new FormData();
        
        // post_template 처리 (Summernote가 있으면)
        const postTemplateEditor = $('#post_template').summernote('code');
        if (postTemplateEditor !== undefined) {
            formData.append('post_template', postTemplateEditor);
        } else {
            formData.append('post_template', document.getElementById('post_template')?.value || '');
        }
        
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("boards.update-template", ["site" => $site->slug, "board" => $board->id]) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            
            if (response.headers.get('content-type')?.includes('application/json')) {
                return response.json().then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else if (data.errors) {
                        let errorMessages = [];
                        for (let field in data.errors) {
                            errorMessages.push(data.errors[field].join(', '));
                        }
                        alert('오류가 발생했습니다:\n' + errorMessages.join('\n'));
                    }
                });
            } else {
                return response.text().then(html => {
                    window.location.reload();
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다.');
        });
    }
    
    // 질의응답 상태 관리
    @php
        $existingStatuses = old('qa_statuses', $board->qa_statuses ?? []);
        if (empty($existingStatuses)) {
            $existingStatuses = [
                ['name' => '답변대기', 'color' => '#ffc107'],
                ['name' => '답변완료', 'color' => '#28a745']
            ];
        }
    @endphp
    let qaStatusIndex = {{ count($existingStatuses) }};
    
    // 상태 추가
    document.getElementById('add-qa-status')?.addEventListener('click', function() {
        const container = document.getElementById('qa-statuses-container');
        const newItem = document.createElement('div');
        newItem.className = 'qa-status-item mb-2 d-flex align-items-center gap-2';
        newItem.setAttribute('data-index', qaStatusIndex);
        newItem.innerHTML = `
            <button type="button" class="btn btn-sm btn-outline-secondary move-qa-status-up" title="위로 이동">
                <i class="bi bi-arrow-up"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary move-qa-status-down" title="아래로 이동">
                <i class="bi bi-arrow-down"></i>
            </button>
            <input type="text" 
                   class="form-control form-control-sm" 
                   name="qa_statuses[${qaStatusIndex}][name]" 
                   value="" 
                   placeholder="상태 이름"
                   style="width: 150px;">
            <input type="color" 
                   class="form-control form-control-color form-control-sm" 
                   name="qa_statuses[${qaStatusIndex}][color]" 
                   value="#ffc107" 
                   style="width: 50px; height: 38px;">
            <button type="button" class="btn btn-sm btn-danger remove-qa-status">
                <i class="bi bi-trash"></i>
            </button>
        `;
        container.appendChild(newItem);
        qaStatusIndex++;
        updateRemoveButtons();
        updateMoveButtons();
    });
    
    // 상태 삭제
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-qa-status')) {
            const item = e.target.closest('.qa-status-item');
            const container = document.getElementById('qa-statuses-container');
            if (container && container.children.length > 1) {
                item.remove();
                reindexItems();
                updateRemoveButtons();
                updateMoveButtons();
            }
        }
        
        // 위로 이동
        if (e.target.closest('.move-qa-status-up')) {
            const item = e.target.closest('.qa-status-item');
            const container = document.getElementById('qa-statuses-container');
            if (container && item && item.previousElementSibling) {
                container.insertBefore(item, item.previousElementSibling);
                reindexItems();
                updateMoveButtons();
            }
        }
        
        // 아래로 이동
        if (e.target.closest('.move-qa-status-down')) {
            const item = e.target.closest('.qa-status-item');
            const container = document.getElementById('qa-statuses-container');
            if (container && item && item.nextElementSibling) {
                container.insertBefore(item.nextElementSibling, item);
                reindexItems();
                updateMoveButtons();
            }
        }
    });
    
    // 항목 인덱스 재설정
    function reindexItems() {
        const container = document.getElementById('qa-statuses-container');
        if (container) {
            const items = container.querySelectorAll('.qa-status-item');
            items.forEach((item, index) => {
                item.setAttribute('data-index', index);
                const nameInput = item.querySelector('input[name*="[name]"]');
                const colorInput = item.querySelector('input[name*="[color]"]');
                if (nameInput) {
                    nameInput.name = `qa_statuses[${index}][name]`;
                }
                if (colorInput) {
                    colorInput.name = `qa_statuses[${index}][color]`;
                }
            });
        }
    }
    
    // 삭제 버튼 활성화/비활성화
    function updateRemoveButtons() {
        const container = document.getElementById('qa-statuses-container');
        if (container) {
            const items = container.querySelectorAll('.qa-status-item');
            items.forEach(item => {
                const removeBtn = item.querySelector('.remove-qa-status');
                if (removeBtn) {
                    removeBtn.disabled = items.length <= 1;
                }
            });
        }
    }
    
    // 이동 버튼 활성화/비활성화
    function updateMoveButtons() {
        const container = document.getElementById('qa-statuses-container');
        if (container) {
            const items = container.querySelectorAll('.qa-status-item');
            items.forEach((item, index) => {
                const upBtn = item.querySelector('.move-qa-status-up');
                const downBtn = item.querySelector('.move-qa-status-down');
                if (upBtn) {
                    upBtn.disabled = index === 0;
                }
                if (downBtn) {
                    downBtn.disabled = index === items.length - 1;
                }
            });
        }
    }
    
    // 초기 로드 시 버튼 상태 업데이트
    updateRemoveButtons();
    updateMoveButtons();
    
    // 게시판 하단 폼 제출
    document.getElementById('footerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitFooterForm();
    });
    
    function submitFooterForm() {
        const formData = new FormData();
        
        // footer_content 처리 (Summernote가 있으면)
        const footerContentEditor = $('#footer_content').summernote('code');
        if (footerContentEditor !== undefined) {
            formData.append('footer_content', footerContentEditor);
        } else {
            formData.append('footer_content', document.getElementById('footer_content')?.value || '');
        }
        
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("boards.update-footer", ["site" => $site->slug, "board" => $board->id]) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            
            if (response.headers.get('content-type')?.includes('application/json')) {
                return response.json().then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else if (data.errors) {
                        let errorMessages = [];
                        for (let field in data.errors) {
                            errorMessages.push(data.errors[field].join(', '));
                        }
                        alert('오류가 발생했습니다:\n' + errorMessages.join('\n'));
                    }
                });
            } else {
                return response.text().then(html => {
                    window.location.reload();
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다.');
        });
    }
    // 일반 설정 모바일/데스크탑 입력 필드 동기화
    document.addEventListener('DOMContentLoaded', function() {
        // 게시판 타입
        syncGeneralFields('type', 'type_mobile');
        syncGeneralFields('name', 'name_mobile');
        syncGeneralFields('slug', 'slug_mobile');
        syncGeneralFields('description', 'description_mobile');
        syncGeneralFields('max_posts_per_day', 'max_posts_per_day_mobile');
        syncGeneralFields('posts_per_page', 'posts_per_page_mobile');
        
        function syncGeneralFields(desktopId, mobileId) {
            const desktopField = document.getElementById(desktopId);
            const mobileField = document.getElementById(mobileId);
            
            if (desktopField && mobileField) {
                // 데스크탑 -> 모바일
                desktopField.addEventListener('change', function() {
                    mobileField.value = this.value;
                });
                desktopField.addEventListener('input', function() {
                    mobileField.value = this.value;
                });
                
                // 모바일 -> 데스크탑
                mobileField.addEventListener('change', function() {
                    desktopField.value = this.value;
                });
                mobileField.addEventListener('input', function() {
                    desktopField.value = this.value;
                });
            }
        }
    });
    
    // 등급 & 포인트 모바일/데스크탑 입력 필드 동기화
    document.addEventListener('DOMContentLoaded', function() {
        // 게시글 읽기
        syncFields('read_permission', 'read_permission_mobile');
        syncFields('read_points', 'read_points_mobile');
        
        // 게시글 쓰기
        syncFields('write_permission', 'write_permission_mobile');
        syncFields('write_points', 'write_points_mobile');
        
        // 게시글 삭제
        syncFields('delete_permission', 'delete_permission_mobile');
        syncFields('delete_points', 'delete_points_mobile');
        
        // 댓글 쓰기
        syncFields('comment_permission', 'comment_permission_mobile');
        syncFields('comment_points', 'comment_points_mobile');
        
        // 댓글 삭제
        syncFields('comment_delete_permission', 'comment_delete_permission_mobile');
        syncFields('comment_delete_points', 'comment_delete_points_mobile');
        
        function syncFields(desktopId, mobileId) {
            const desktopField = document.getElementById(desktopId);
            const mobileField = document.getElementById(mobileId);
            
            if (desktopField && mobileField) {
                // 데스크탑 -> 모바일
                desktopField.addEventListener('change', function() {
                    mobileField.value = this.value;
                });
                
                // 모바일 -> 데스크탑
                mobileField.addEventListener('change', function() {
                    desktopField.value = this.value;
                });
            }
        }
    });
</script>
@push('styles')
<style>
    /* 등급 드롭다운 글씨 잘림 방지 */
    .grade-select-mobile {
        font-size: 0.875rem !important;
        padding: 0.5rem 2rem 0.5rem 0.75rem !important;
        white-space: nowrap;
        overflow: visible;
        text-overflow: clip;
    }
    
    .grade-select-mobile option {
        padding: 0.5rem;
        white-space: normal;
        word-wrap: break-word;
    }
    
    /* 데스크탑 등급 드롭다운도 개선 */
    #gradePointsForm .form-select {
        font-size: 0.875rem;
        padding: 0.5rem 2rem 0.5rem 0.75rem;
    }
    
    #gradePointsForm .form-select option {
        padding: 0.5rem;
        white-space: normal;
    }
</style>
@endpush
@endpush
@endsection

