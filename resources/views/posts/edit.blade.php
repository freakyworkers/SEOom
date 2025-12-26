@extends('layouts.app')

@section('title', '게시글 수정')

@section('content')

<div class="card shadow">
    <div class="card-header bg-warning text-dark">
        <h4 class="mb-0"><i class="bi bi-pencil"></i> 게시글 수정</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('posts.update', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('title') is-invalid @enderror" 
                       id="title" 
                       name="title" 
                       value="{{ old('title', $post->title) }}" 
                       required 
                       autofocus>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if(in_array($board->type, ['photo', 'bookmark', 'blog', 'pinterest']))
                <div class="mb-3">
                    <label for="thumbnail" class="form-label">썸네일 이미지</label>
                    @if($post->thumbnail_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                 alt="현재 썸네일" 
                                 style="max-width: 300px; max-height: 200px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                            <p class="text-muted small mt-1">현재 썸네일</p>
                        </div>
                    @endif
                    <input type="file" 
                           class="form-control @error('thumbnail') is-invalid @enderror" 
                           id="thumbnail" 
                           name="thumbnail" 
                           accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                    <small class="form-text text-muted">
                        게시판 리스트에 표시될 썸네일 이미지를 업로드하세요. (최대 5MB)
                    </small>
                    @error('thumbnail')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div id="thumbnail-preview" class="mt-2" style="display: none;">
                        <img id="thumbnail-preview-img" src="" alt="썸네일 미리보기" style="max-width: 300px; max-height: 200px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                        <p class="text-muted small mt-1">새 썸네일 미리보기</p>
                    </div>
                </div>
            @endif

            @if($board->topics()->count() > 0)
                <div class="mb-3">
                    <label class="form-label">주제 <span class="text-danger">*</span></label>
                    @php
                        $selectedTopicIds = old('topic_ids', $post->topics->pluck('id')->toArray());
                    @endphp
                    @if($board->allow_multiple_topics)
                        <small class="form-text text-muted d-block mb-2">
                            여러 주제를 선택할 수 있습니다.
                        </small>
                        @foreach($board->topics()->ordered()->get() as $topic)
                            <div class="form-check mb-2">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="topic_{{ $topic->id }}" 
                                       name="topic_ids[]" 
                                       value="{{ $topic->id }}"
                                       {{ in_array($topic->id, $selectedTopicIds) ? 'checked' : '' }}>
                                <label class="form-check-label" for="topic_{{ $topic->id }}">
                                    <span class="badge" style="background-color: {{ $topic->color }}; color: white; padding: 4px 8px; border-radius: 4px;">
                                        {{ $topic->name }}
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    @else
                        <small class="form-text text-muted d-block mb-2">
                            하나의 주제만 선택할 수 있습니다.
                        </small>
                        @php
                            $selectedTopicId = is_array($selectedTopicIds) && count($selectedTopicIds) > 0 ? $selectedTopicIds[0] : null;
                        @endphp
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select @error('topic_ids') is-invalid @enderror" 
                                    id="topic_select" 
                                    name="topic_ids[]" 
                                    required>
                                <option value="">주제를 선택하세요</option>
                                @foreach($board->topics()->ordered()->get() as $topic)
                                    <option value="{{ $topic->id }}" 
                                            data-color="{{ $topic->color }}"
                                            {{ $selectedTopicId == $topic->id ? 'selected' : '' }}>
                                        {{ $topic->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="selected-topic-badge" style="display: none;">
                                <span class="badge" id="topic-badge" style="padding: 6px 12px; border-radius: 4px;"></span>
                            </div>
                        </div>
                    @endif
                    @error('topic_ids')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            @if($board->type === 'bookmark')
                <div class="mb-3">
                    <label for="link" class="form-label">링크</label>
                    <input type="url" 
                           class="form-control @error('link') is-invalid @enderror" 
                           id="link" 
                           name="link" 
                           value="{{ old('link', $post->link ?? '') }}" 
                           placeholder="https://example.com">
                    <small class="form-text text-muted">
                        "바로가기" 버튼 클릭 시 이동할 링크를 입력하세요.
                    </small>
                    @error('link')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">추가 항목</label>
                    <div id="bookmark-items-container">
                        @php
                            $oldItems = old('bookmark_items', []);
                            $postItems = $post->bookmark_items ?? [];
                            $itemsToShow = !empty($oldItems) ? $oldItems : $postItems;
                        @endphp
                        @if(count($itemsToShow) > 0)
                            @foreach($itemsToShow as $index => $item)
                                <div class="bookmark-item mb-2">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <input type="text" 
                                                   class="form-control bookmark-item-name" 
                                                   name="bookmark_items[{{ $index }}][name]" 
                                                   value="{{ $item['name'] ?? '' }}"
                                                   placeholder="항목 이름 (예: 사이트이름)">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" 
                                                   class="form-control bookmark-item-value" 
                                                   name="bookmark_items[{{ $index }}][value]" 
                                                   value="{{ $item['value'] ?? '' }}"
                                                   placeholder="내용 (예: ATM)">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-bookmark-item" title="삭제">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="bookmark-item mb-2">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <input type="text" 
                                               class="form-control bookmark-item-name" 
                                               name="bookmark_items[0][name]" 
                                               placeholder="항목 이름 (예: 사이트이름)">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" 
                                               class="form-control bookmark-item-value" 
                                               name="bookmark_items[0][value]" 
                                               placeholder="내용 (예: ATM)">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-bookmark-item" title="삭제">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-bookmark-item">
                        <i class="bi bi-plus-circle"></i> 항목 추가하기
                    </button>
                </div>
            @endif

            @if($board->type === 'event')
                {{-- 사진형 이벤트 게시판인 경우 썸네일 이미지 입력 --}}
                @if(($board->event_display_type ?? 'photo') === 'photo')
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">썸네일 이미지</label>
                        @if($post->thumbnail_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                     alt="현재 썸네일" 
                                     style="max-width: 300px; max-height: 200px; border: 1px solid #dee2e6; border-radius: 4px;">
                            </div>
                        @endif
                        <input type="file" 
                               class="form-control @error('thumbnail') is-invalid @enderror" 
                               id="thumbnail" 
                               name="thumbnail" 
                               accept="image/*">
                        <small class="form-text text-muted">
                            이벤트 리스트에 표시될 썸네일 이미지를 업로드하세요. 업로드하지 않으면 "No Image"로 표시됩니다.
                        </small>
                        @error('thumbnail')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="thumbnail-preview" class="mt-2" style="display: none;">
                            <img id="thumbnail-preview-img" src="" alt="썸네일 미리보기" style="max-width: 300px; max-height: 200px; border: 1px solid #dee2e6; border-radius: 4px;">
                        </div>
                    </div>
                    
                    {{-- 혜택 항목 (북마크처럼 항목, 내용 1개만) --}}
                    <div class="mb-3">
                        <label class="form-label">혜택</label>
                        <div id="event-benefit-container">
                            @php
                                $benefitItem = null;
                                if ($post->bookmark_items && is_array($post->bookmark_items) && count($post->bookmark_items) > 0) {
                                    $benefitItem = $post->bookmark_items[0];
                                }
                            @endphp
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" 
                                           class="form-control" 
                                           name="bookmark_items[0][name]" 
                                           value="{{ old('bookmark_items.0.name', $benefitItem['name'] ?? '혜택') }}"
                                           placeholder="항목 제목 (예: 혜택)">
                                </div>
                                <div class="col-md-7">
                                    <input type="text" 
                                           class="form-control" 
                                           name="bookmark_items[0][value]" 
                                           value="{{ old('bookmark_items.0.value', $benefitItem['value'] ?? '') }}"
                                           placeholder="항목 내용 (예: 정답시 5,000포인트 지급)">
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            이벤트 혜택 정보를 입력하세요.
                        </small>
                    </div>
                @endif
                
                <div class="mb-3">
                    <label class="form-label">이벤트 유형 <span class="text-danger">*</span></label>
                    <select class="form-select @error('event_type') is-invalid @enderror" 
                            id="event_type" 
                            name="event_type" 
                            required>
                        <option value="">선택하세요</option>
                        <option value="general" {{ old('event_type', $post->event_type) == 'general' ? 'selected' : '' }}>공지형 이벤트</option>
                        <option value="application" {{ old('event_type', $post->event_type) == 'application' ? 'selected' : '' }}>신청형 이벤트</option>
                        <option value="quiz" {{ old('event_type', $post->event_type) == 'quiz' ? 'selected' : '' }}>정답형 이벤트</option>
                    </select>
                    <small class="form-text text-muted">
                        공지형 이벤트: 단순 공지 형태의 이벤트 (참여 버튼 없음)<br>
                        신청형 이벤트: 신청 버튼으로 참여, 운영자가 일괄 포인트 지급<br>
                        정답형 이벤트: 정답을 선택해야 하는 퀴즈 이벤트
                    </small>
                    @error('event_type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="event_start_date" class="form-label">이벤트 시작일</label>
                    <input type="date" 
                           class="form-control @error('event_start_date') is-invalid @enderror" 
                           id="event_start_date" 
                           name="event_start_date" 
                           value="{{ old('event_start_date', $post->event_start_date ? $post->event_start_date->format('Y-m-d') : '') }}">
                    @error('event_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="event_end_date" class="form-label">이벤트 종료일</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="date" 
                               class="form-control @error('event_end_date') is-invalid @enderror" 
                               id="event_end_date" 
                               name="event_end_date" 
                               value="{{ old('event_end_date', $post->event_end_date ? $post->event_end_date->format('Y-m-d') : '') }}"
                               style="flex: 1;">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="event_end_undecided" 
                                   name="event_end_undecided" 
                                   value="1"
                                   {{ old('event_end_undecided', $post->event_end_undecided) ? 'checked' : '' }}>
                            <label class="form-check-label" for="event_end_undecided">
                                종료일 미정
                            </label>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        종료일 미정을 선택하면 종료일 입력이 무시됩니다.
                    </small>
                    @error('event_end_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div id="quiz-options-container" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">퀴즈 선택지</label>
                        <div id="event-options-container">
                            @if($post->event_type === 'quiz' && $post->eventOptions->count() > 0)
                                @foreach($post->eventOptions as $index => $option)
                                    <div class="event-option-item mb-2">
                                        <div class="row g-2">
                                            <div class="col-md-10">
                                                <input type="text" 
                                                       class="form-control event-option-text" 
                                                       name="event_options[{{ $index }}][text]" 
                                                       value="{{ old("event_options.{$index}.text", $option->option_text) }}"
                                                       placeholder="선택지 내용">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-event-option" title="삭제">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="event-option-item mb-2">
                                    <div class="row g-2">
                                        <div class="col-md-10">
                                            <input type="text" 
                                                   class="form-control event-option-text" 
                                                   name="event_options[0][text]" 
                                                   placeholder="선택지 내용">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-event-option" title="삭제">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-event-option">
                            <i class="bi bi-plus-circle"></i> 선택지 추가
                        </button>
                        <small class="form-text text-muted d-block mt-2">
                            퀴즈 이벤트인 경우 최소 2개 이상의 선택지를 입력하세요. 정답은 이벤트 종료 후 또는 진행 중에 운영자가 선택할 수 있습니다.
                        </small>
                    </div>
                </div>
            @endif

            <div class="mb-3">
                <label for="content" class="form-label">내용 <span class="text-danger">*</span></label>
                <textarea class="form-control @error('content') is-invalid @enderror" 
                          id="content" 
                          name="content" 
                          rows="15" 
                          required>{{ old('content', $post->content) }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($board->type === 'qa' && auth()->user()->canManage())
                <div class="mb-3">
                    <label for="qa_status" class="form-label">상태</label>
                    @php
                        $qaStatuses = $board->qa_statuses ?? [];
                        if (empty($qaStatuses)) {
                            $qaStatuses = [
                                ['name' => '답변대기', 'color' => '#ffc107'],
                                ['name' => '답변완료', 'color' => '#28a745']
                            ];
                        }
                        $currentStatus = old('qa_status', $post->qa_status ?? '');
                    @endphp
                    <select class="form-select @error('qa_status') is-invalid @enderror" 
                            id="qa_status" 
                            name="qa_status">
                        <option value="">상태를 선택하세요</option>
                        @foreach($qaStatuses as $status)
                            <option value="{{ $status['name'] }}" 
                                    {{ $currentStatus === $status['name'] ? 'selected' : '' }}
                                    data-color="{{ $status['color'] }}">
                                {{ $status['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">
                        질의응답 게시판의 상태를 선택하세요.
                    </small>
                    @error('qa_status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            @if(auth()->user()->canManage())
                @php
                    $pointColor = $site->getSetting('color_point_main', '#0d6efd');
                @endphp
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input type="hidden" name="is_notice" value="0"> {{-- Unchecked value --}}
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_notice" 
                               name="is_notice" 
                               value="1" 
                               {{ old('is_notice', $post->is_notice) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_notice">
                            <i class="bi bi-megaphone" style="color: {{ $pointColor }}; font-size: 1.2em;" title="공지사항"></i>
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="hidden" name="is_pinned" value="0"> {{-- Unchecked value --}}
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_pinned" 
                               name="is_pinned" 
                               value="1" 
                               {{ old('is_pinned', $post->is_pinned) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_pinned">
                            <i class="bi bi-pin-angle-fill" style="color: {{ $pointColor }}; font-size: 1.2em;" title="상단 고정"></i>
                        </label>
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                   class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> 취소
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check-circle"></i> 수정
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Summernote 초기화 (가장 먼저 실행)
    if (typeof $.fn.summernote === 'undefined') {
        console.error('Summernote is not loaded!');
        alert('에디터 라이브러리를 불러오는 중 오류가 발생했습니다. 페이지를 새로고침해주세요.');
    } else {
        try {
            $('#content').summernote({
                height: 400,
                lang: 'ko-KR',
                disableDragAndDrop: false, // 이미지 링크 문제 해결을 위해 false로 설정
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
                fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '36', '48', '64', '82', '150'],
                callbacks: {
                    onInit: function() {
                        // 에디터 초기화 완료
                        console.log('Summernote initialized successfully');
                        
                        // 이미지 클릭하면 data-selected 속성 부여
                        var $editor = $('#content');
                        var $iframe = $editor.next('.note-editor').find('iframe');
                        
                if ($iframe.length > 0) {
                    var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                    var $iframeBody = $(iframeDoc.body);
                    
                    // iframe 내부 이미지 클릭 이벤트
                    $iframeBody.on('click', 'img', function() {
                        // 모든 이미지에서 data-selected 제거
                        $iframeBody.find('img').removeAttr('data-selected');
                        // 클릭한 이미지에만 data-selected 부여
                        $(this).attr('data-selected', 'true');
                        console.log('Image selected:', this);
                    });
                }
                    },
                    onChange: function(contents, $editable) {
                        // 내용 변경 시 처리
                    },
                    onImageUpload: function(files) {
                        console.log('onImageUpload called - uploading immediately', files);
                        
                        if (!files || files.length === 0) {
                            return false;
                        }
                        
                        var selectedFile = files[0];
                        console.log('File selected, uploading:', selectedFile.name);
                        
                        // 모달을 닫기
                        var $modal = $('.note-image-dialog');
                        if ($modal.length > 0) {
                            $modal.modal('hide');
                        }
                        
                        // 즉시 업로드 및 삽입
                        if (typeof uploadImage === 'function') {
                            uploadImage(selectedFile);
                        } else {
                            console.error('uploadImage function is not defined');
                        }
                        
                        // Summernote의 기본 이미지 업로드 동작 비활성화
                        return false;
                    }
                }
            });
            console.log('Summernote initialization completed');
        } catch (e) {
            console.error('Summernote initialization error:', e);
            alert('에디터를 초기화하는 중 오류가 발생했습니다: ' + e.message);
        }
    }
    
    // 전역 변수로 모달 참조 저장
    var currentImageModal = null;
    var isUploading = false;
    
    // 모달이 닫히는 것을 막기 위한 전역 핸들러
    $(document).on('hide.bs.modal', '.note-image-dialog', function(e) {
        var $modal = $(this);
        var allowClose = $modal.data('allow-close');
        var uploading = $modal.data('uploading') || isUploading;
        var selectedFile = $modal.data('selected-file');
        
        if (allowClose === false || uploading === true || isUploading === true || (selectedFile && allowClose !== true)) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    });
    
    // 접근성 문제 해결: Summernote 모달의 닫기 버튼에서 aria-hidden 제거
    // MutationObserver를 사용하여 DOM 변경을 감지하고 즉시 제거
    var ariaHiddenObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                var $target = $(mutation.target);
                // 포커스를 받을 수 있는 닫기 버튼에서 aria-hidden 제거
                if ($target.is('button.close, .btn-close, button[aria-label="Close"], button[type="button"].close')) {
                    if ($target.attr('aria-hidden') === 'true') {
                        $target.removeAttr('aria-hidden');
                        console.log('Removed aria-hidden from close button');
                    }
                }
            }
        });
    });
    
    // 모든 Summernote 모달 감시
    $(document).on('shown.bs.modal', '.note-image-dialog, .note-link-dialog', function() {
        var $modal = $(this);
        var closeButtons = $modal.find('button.close, .btn-close, button[aria-label="Close"], button[type="button"].close').toArray();
        closeButtons.forEach(function(button) {
            ariaHiddenObserver.observe(button, {
                attributes: true,
                attributeFilter: ['aria-hidden']
            });
            // 즉시 aria-hidden 제거
            $(button).removeAttr('aria-hidden');
        });
        
        // 주기적으로 확인 (추가 안전장치)
        var checkInterval = setInterval(function() {
            $modal.find('button.close, .btn-close, button[aria-label="Close"], button[type="button"].close').each(function() {
                var $btn = $(this);
                if ($btn.attr('aria-hidden') === 'true') {
                    $btn.removeAttr('aria-hidden');
                }
            });
        }, 200);
        
        // 모달이 닫힐 때 interval 정리
        $modal.on('hidden.bs.modal', function() {
            clearInterval(checkInterval);
        });
    });
    
    // 포커스 이벤트에서도 확인 (추가 안전장치)
    $(document).on('focus', 'button.close, .btn-close, button[aria-label="Close"], button[type="button"].close', function() {
        var $btn = $(this);
        if ($btn.attr('aria-hidden') === 'true') {
            $btn.removeAttr('aria-hidden');
            console.log('Removed aria-hidden from focused close button');
        }
    });
    
    // Summernote 이미지 삽입 모달이 열릴 때
    $(document).on('shown.bs.modal', '.note-image-dialog', function() {
        console.log('Image modal opened');
        var $modal = $(this);
        currentImageModal = $modal;
        isUploading = false;
        $modal.data('uploading', false);
        $modal.data('allow-close', true);
        $modal.data('selected-file', null);
        
        // 이미지 링크 입력 필드 추가 (이미 있으면 스킵)
        var $linkInputGroup = $modal.find('.note-image-link-group');
        if ($linkInputGroup.length === 0) {
            var $urlGroup = $modal.find('.note-group-image-url');
            if ($urlGroup.length === 0) {
                var $modalBody = $modal.find('.modal-body');
                if ($modalBody.length === 0) {
                    $modalBody = $modal.find('.note-image-dialog-body');
                }
                
                $linkInputGroup = $('<div class="note-image-link-group mt-3 mb-3" style="padding: 10px; background-color: #f8f9fa; border-radius: 5px;"></div>');
                $linkInputGroup.html(
                    '<label class="form-label small fw-bold mb-2"><i class="bi bi-link-45deg me-1"></i>이미지 링크 (선택사항)</label>' +
                    '<input type="text" class="form-control form-control-sm note-image-link-url" placeholder="https://example.com" style="font-size: 0.875rem;">' +
                    '<small class="form-text text-muted">이미지에 하이퍼링크를 추가하려면 URL을 입력하세요. 비워두면 링크가 추가되지 않습니다.</small>'
                );
                
                if ($urlGroup.length > 0) {
                    $linkInputGroup.insertAfter($urlGroup);
                } else if ($modalBody.length > 0) {
                    $modalBody.append($linkInputGroup);
                } else {
                    var $fileInput = $modal.find('input[type="file"]');
                    if ($fileInput.length > 0) {
                        $linkInputGroup.insertAfter($fileInput.closest('.note-group-select-from-files, div'));
                    } else {
                        $modal.find('.modal-body, .note-image-dialog-body').first().append($linkInputGroup);
                    }
                }
            }
        }
        
        // 파일 입력 필드 찾기
        var $fileInput = $modal.find('input[type="file"]');
        
        if ($fileInput.length > 0) {
            var $fileGroup = $fileInput.closest('.note-group-select-from-files');
            if ($fileGroup.length === 0) {
                $fileGroup = $fileInput.parent();
            }
            
            var $fileNameDisplay = $fileGroup.find('.note-selected-file-name');
            if ($fileNameDisplay.length === 0) {
                $fileNameDisplay = $('<div class="note-selected-file-name text-muted small mt-2"></div>');
                $fileGroup.append($fileNameDisplay);
            }
            
            $fileInput.off('change.show-filename change').on('change.show-filename', function(e) {
                var files = this.files;
                if (files && files.length > 0) {
                    var fileName = files[0].name;
                    $modal.data('selected-file', files[0]);
                    $modal.data('allow-close', false);
                    isUploading = false;
                    currentImageModal = $modal;
                    $fileNameDisplay.html('<i class="bi bi-file-image"></i> 선택된 파일: <strong>' + fileName + '</strong>');
                    var $insertBtn = $modal.find('.note-image-btn');
                    $insertBtn.prop('disabled', false);
                }
                e.stopPropagation();
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            });
        }
        
        // 모달의 "그림 삽입" 버튼 클릭 이벤트 오버라이드
        var $insertBtn = $modal.find('.note-image-btn');
        if ($insertBtn.length > 0) {
            $insertBtn.off('click.custom-upload').on('click.custom-upload', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                var selectedFile = $modal.data('selected-file');
                var $urlInput = $modal.find('.note-image-url');
                var imageUrl = $urlInput.length > 0 ? $urlInput.val().trim() : '';
                
                if (selectedFile) {
                    console.log('Uploading selected file:', selectedFile.name);
                    isUploading = true;
                    $modal.data('uploading', true);
                    $modal.data('allow-close', false);
                    
                    var $linkInput = $modal.find('.note-image-link-url');
                    var linkUrl = $linkInput.length > 0 ? $linkInput.val().trim() : '';
                    
                    uploadImage(selectedFile, function(imageUrl) {
                        if (linkUrl) {
                            var $iframe = $('#content').next('.note-editor').find('iframe');
                            if ($iframe.length > 0) {
                                setTimeout(function() {
                                    var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                                    var $iframeBody = $(iframeDoc.body);
                                    var $img = $iframeBody.find('img').last();
                                    if ($img.length > 0 && !$img.closest('a').length) {
                                        $img.wrap('<a href="' + linkUrl + '" target="_blank" rel="noopener noreferrer"></a>');
                                        var newCode = iframeDoc.body.innerHTML;
                                        $('#content').summernote('code', newCode);
                                    }
                                }, 100);
                            }
                        }
                        
                        isUploading = false;
                        $modal.data('uploading', false);
                        $modal.data('allow-close', true);
                        setTimeout(function() {
                            $modal.modal('hide');
                            if ($linkInput.length > 0) {
                                $linkInput.val('');
                            }
                        }, 200);
                    });
                } else if (imageUrl) {
                    console.log('Inserting image from URL:', imageUrl);
                    
                    var $linkInput = $modal.find('.note-image-link-url');
                    var linkUrl = $linkInput.length > 0 ? $linkInput.val().trim() : '';
                    
                    $('#content').summernote('insertImage', imageUrl);
                    
                    if (linkUrl) {
                        setTimeout(function() {
                            var $iframe = $('#content').next('.note-editor').find('iframe');
                            if ($iframe.length > 0) {
                                var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                                var $iframeBody = $(iframeDoc.body);
                                var $img = $iframeBody.find('img').last();
                                if ($img.length > 0 && !$img.closest('a').length) {
                                    $img.wrap('<a href="' + linkUrl + '" target="_blank" rel="noopener noreferrer"></a>');
                                    var newCode = iframeDoc.body.innerHTML;
                                    $('#content').summernote('code', newCode);
                                }
                            }
                        }, 100);
                    }
                    
                    $modal.data('allow-close', true);
                    setTimeout(function() {
                        $modal.modal('hide');
                        if ($linkInput.length > 0) {
                            $linkInput.val('');
                        }
                    }, 200);
                } else {
                    alert('파일을 선택하거나 이미지 URL을 입력해주세요.');
                }
                
                return false;
            });
        }
    });
    
    // 이미지 업로드 함수
    function uploadImage(file, callback) {
        console.log('uploadImage function called', file);
        
        if (!file) {
            console.error('No file provided to uploadImage');
            return;
        }
        
        var formData = new FormData();
        formData.append('image', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        var uploadUrl = '{{ route("posts.upload-image", ["site" => $site->slug]) }}';
        console.log('Uploading to:', uploadUrl);
        
        $.ajax({
            url: uploadUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Upload success:', response);
                if (response.url) {
                    var $editor = $('#content');
                    var imageUrl = response.url;
                    console.log('Inserting image:', imageUrl);
                    
                    $editor.summernote('focus');
                    $editor.summernote('insertImage', imageUrl);
                    
                    setTimeout(function() {
                        $editor.summernote('focus');
                    }, 100);
                    
                    if (callback) {
                        callback(imageUrl);
                    }
                } else {
                    console.error('No URL in response');
                    alert('이미지 업로드에 실패했습니다.');
                }
            },
            error: function(xhr) {
                console.error('Upload error:', xhr);
                var errorMessage = '이미지 업로드에 실패했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
            }
        });
    }
    
    // 폼 제출 전 Summernote 내용을 textarea에 저장
    $('form').on('submit', function() {
        if (typeof $.fn.summernote !== 'undefined' && $('#content').summernote('code')) {
            $('#content').val($('#content').summernote('code'));
        }
    });

    // 썸네일 미리보기
    $('#thumbnail').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#thumbnail-preview-img').attr('src', e.target.result);
                $('#thumbnail-preview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#thumbnail-preview').hide();
        }
    });

    // 북마크 항목 추가 기능
    @if($board->type === 'bookmark')
        @php
            $oldItems = old('bookmark_items', []);
            $postItems = $post->bookmark_items ?? [];
            $itemsToShow = !empty($oldItems) ? $oldItems : $postItems;
        @endphp
        var bookmarkItemIndex = {{ count($itemsToShow) > 0 ? count($itemsToShow) : 1 }};
    @else
        var bookmarkItemIndex = 1;
    @endif
    
    $('#add-bookmark-item').on('click', function() {
        var newItem = `
            <div class="bookmark-item mb-2">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" 
                               class="form-control bookmark-item-name" 
                               name="bookmark_items[${bookmarkItemIndex}][name]" 
                               placeholder="항목 이름">
                    </div>
                    <div class="col-md-6">
                        <input type="text" 
                               class="form-control bookmark-item-value" 
                               name="bookmark_items[${bookmarkItemIndex}][value]" 
                               placeholder="내용">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-bookmark-item" title="삭제">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#bookmark-items-container').append(newItem);
        bookmarkItemIndex++;
    });

    // 북마크 항목 삭제 기능
    $(document).on('click', '.remove-bookmark-item', function() {
        $(this).closest('.bookmark-item').remove();
    });

    // 주제 드롭다운 선택 시 배지 표시
    $('#topic_select').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var topicName = selectedOption.text();
        var topicColor = selectedOption.data('color');
        var badgeContainer = $('#selected-topic-badge');
        var badge = $('#topic-badge');
        
        if ($(this).val()) {
            badge.text(topicName);
            badge.css('background-color', topicColor);
            badge.css('color', 'white');
            badgeContainer.show();
        } else {
            badgeContainer.hide();
        }
    });
    
    // 페이지 로드 시 선택된 주제 배지 표시
    $('#topic_select').trigger('change');

    // 이벤트 타입 변경 시 퀴즈 선택지 표시/숨김
    @if($board->type === 'event')
        $('#event_type').on('change', function() {
            if ($(this).val() === 'quiz') {
                $('#quiz-options-container').show();
            } else {
                $('#quiz-options-container').hide();
            }
        });

        // 페이지 로드 시 이벤트 타입에 따라 퀴즈 선택지 표시
        if ($('#event_type').val() === 'quiz') {
            $('#quiz-options-container').show();
        }

        // 이벤트 옵션 추가
        @php
            $existingOptionsCount = $post->event_type === 'quiz' && $post->eventOptions ? $post->eventOptions->count() : 0;
        @endphp
        var eventOptionIndex = {{ $existingOptionsCount > 0 ? $existingOptionsCount : 1 }};
        $('#add-event-option').on('click', function() {
            var newOption = `
                <div class="event-option-item mb-2">
                    <div class="row g-2">
                        <div class="col-md-10">
                            <input type="text" 
                                   class="form-control event-option-text" 
                                   name="event_options[${eventOptionIndex}][text]" 
                                   placeholder="선택지 내용">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-event-option" title="삭제">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#event-options-container').append(newOption);
            eventOptionIndex++;
        });

        // 이벤트 옵션 삭제
        $(document).on('click', '.remove-event-option', function() {
            $(this).closest('.event-option-item').remove();
        });
    @endif

    // 공지/고정 체크박스 변경 시 배지 표시/숨김
    $('#is_notice').on('change', function() {
        if ($(this).is(':checked')) {
            $('.notice-badge').show();
        } else {
            $('.notice-badge').hide();
        }
    });

    $('#is_pinned').on('change', function() {
        if ($(this).is(':checked')) {
            $('.pinned-badge').show();
        } else {
            $('.pinned-badge').hide();
        }
    });
    
    // 이미지 선택 상태 저장
    var selectedImageElement = null;
    var isImageSelected = false;
    
    // 워드프레스 방식: 이미지 클릭 시 선택 상태 저장 (이미지 팝오버 표시 시)
    // Summernote 초기화 후에 실행되도록 지연
    setTimeout(function() {
        try {
            var $editor = $('#content');
            var $iframe = $editor.next('.note-editor').find('iframe');
            
            if ($iframe.length === 0) {
                return;
            }
            
            var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
            var $iframeBody = $(iframeDoc.body);
            
            // 이미지 팝오버가 표시될 때마다 선택된 이미지 저장
            var checkImagePopover = function() {
                try {
                    var $popover = $('.note-image-popover');
                    if ($popover.length > 0 && $popover.is(':visible')) {
                        // 팝오버가 표시되어 있으면 선택된 이미지 찾기
                        // Summernote는 이미지 클릭 시 note-float 클래스를 추가함
                        var $img = $iframeBody.find('img.note-float');
                        if ($img.length === 0) {
                            // note-float가 없으면 마지막 이미지 사용
                            $img = $iframeBody.find('img').last();
                        }
                        if ($img.length > 0) {
                            selectedImageElement = $img[0];
                            isImageSelected = true;
                            console.log('Image selected:', selectedImageElement);
                        }
                    } else {
                        // 팝오버가 사라지면 선택 해제 (단, 링크 모달이 열려있지 않을 때만)
                        if (!$('.note-link-dialog').is(':visible')) {
                            selectedImageElement = null;
                            isImageSelected = false;
                        }
                    }
                } catch (e) {
                    console.log('Error checking image popover:', e);
                }
            };
            
            // 주기적으로 팝오버 확인
            setInterval(checkImagePopover, 300);
            
            // iframe 내부에서 이미지 클릭 이벤트 감지
            $iframeBody.on('click', 'img', function() {
                setTimeout(function() {
                    checkImagePopover();
                }, 100);
            });
            
        } catch (e) {
            console.log('Error setting up image selection:', e);
        }
    }, 2000);
    
    // Summernote 링크 삽입 모달 커스터마이징 - 이미지에 링크 적용
    $(document).on('shown.bs.modal', '.note-link-dialog', function() {
        var $modal = $(this);
        var $editor = $('#content');
        var $iframe = $editor.next('.note-editor').find('iframe');
        
        if ($iframe.length === 0) {
            return;
        }
        
        // 모달이 완전히 렌더링될 때까지 약간의 지연
        setTimeout(function() {
            try {
                var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                var $iframeBody = $(iframeDoc.body);
                
                // 해결 방법 4: data-selected 속성이 있는 이미지 찾기
                var $selectedImg = $iframeBody.find('img[data-selected]');
                var imageSelected = false;
                
                // data-selected 속성이 있는 이미지 확인
                if ($selectedImg.length > 0) {
                    imageSelected = true;
                } else {
                    // 기존 방식으로도 확인 (하위 호환성)
                    var $popover = $('.note-image-popover');
                    if ($popover.length > 0 && $popover.is(':visible')) {
                        imageSelected = true;
                    }
                    
                    // 전역 변수로 선택된 이미지 확인
                    if (isImageSelected && selectedImageElement) {
                        $selectedImg = $(selectedImageElement);
                        imageSelected = true;
                    }
                    
                    // 이미지 찾기
                    if (imageSelected && (!$selectedImg || $selectedImg.length === 0)) {
                        $selectedImg = $iframeBody.find('img.note-float').first();
                        if ($selectedImg.length === 0) {
                            $selectedImg = $iframeBody.find('img').last();
                        }
                    }
                }
                
                // 이미지가 선택되어 있으면 링크 삽입 모달 커스터마이징
                if (imageSelected && $selectedImg && $selectedImg.length > 0) {
                    console.log('Image selected, customizing link dialog');
                    
                    // 현재 이미지의 링크 확인
                    var currentLink = $selectedImg.closest('a').attr('href') || '';
                    
                    // 모든 입력 필드 찾기
                    var $allInputs = $modal.find('input[type="text"]');
                    var $urlInput = null;
                    var $textInput = null;
                    var $textInputGroup = null; // 텍스트 입력 필드의 부모 그룹 (숨기기 위해)
                    
                    // 모달 내부의 모든 입력 필드 확인
                    $allInputs.each(function() {
                        var $input = $(this);
                        var $parent = $input.parent();
                        var $label = $parent.find('label');
                        if ($label.length === 0) {
                            $label = $input.prev('label');
                        }
                        if ($label.length === 0) {
                            $label = $input.closest('.form-group, .note-form-group, .note-input-group').find('label');
                        }
                        var labelText = $label.text() || '';
                        
                        // 텍스트 입력 필드 찾기
                        if (!$textInput) {
                            if (labelText.includes('내용') || labelText.includes('표시할') || labelText.includes('text') || labelText.includes('Text') || labelText.includes('Link text')) {
                                $textInput = $input;
                                $textInputGroup = $input.closest('.form-group, .note-form-group, .note-input-group, div');
                            }
                        }
                        
                        // URL 입력 필드 찾기
                        if (!$urlInput) {
                            if (labelText.includes('URL') || labelText.includes('이동할') || labelText.includes('url') || labelText.includes('Url') || labelText.includes('To')) {
                                $urlInput = $input;
                            }
                        }
                    });
                    
                    // 필드를 찾지 못한 경우 순서로 판단
                    if ($allInputs.length >= 2) {
                        if (!$textInput) {
                            $textInput = $allInputs.first();
                            $textInputGroup = $textInput.closest('.form-group, .note-form-group, .note-input-group, div');
                        }
                        if (!$urlInput) {
                            $urlInput = $allInputs.eq(1);
                        }
                    }
                    
                    // URL 입력 필드에 현재 링크 설정
                    if ($urlInput && $urlInput.length > 0) {
                        if (currentLink) {
                            $urlInput.val(currentLink);
                        }
                    }
                    
                    // 워드프레스 방식: "링크에 표시할 내용" 필드 완전히 숨기기 (여러 방법 시도)
                    if ($textInput && $textInput.length > 0) {
                        // 방법 1: 부모 그룹 찾아서 숨기기
                        var $textInputGroup = $textInput.closest('.form-group, .note-form-group, .note-input-group, .note-input-group-wrapper, div');
                        if ($textInputGroup.length > 0) {
                            $textInputGroup.css({
                                'display': 'none !important',
                                'visibility': 'hidden',
                                'height': '0',
                                'overflow': 'hidden',
                                'margin': '0',
                                'padding': '0'
                            }).hide();
                        }
                        
                        // 방법 2: 라벨도 함께 숨기기
                        var $label = $textInput.prev('label');
                        if ($label.length === 0) {
                            $label = $textInput.closest('.form-group, .note-form-group, .note-input-group').find('label').first();
                        }
                        if ($label.length > 0) {
                            $label.hide().css('display', 'none !important');
                        }
                        
                        // 방법 3: 필드 자체도 숨기기
                        $textInput.hide().css({
                            'display': 'none !important',
                            'visibility': 'hidden'
                        }).prop('disabled', true).attr('tabindex', '-1');
                        
                        console.log('Text input field and group hidden for image link');
                    }
                    
                    // 추가로 모달 내부의 모든 form-group을 확인하여 첫 번째 그룹이 텍스트 입력이면 숨기기
                    var $firstFormGroup = $modal.find('.form-group, .note-form-group, .note-input-group').first();
                    if ($firstFormGroup.length > 0) {
                        var $firstInput = $firstFormGroup.find('input[type="text"]').first();
                        if ($firstInput.length > 0 && $firstInput !== $urlInput) {
                            var firstLabelText = $firstFormGroup.find('label').text() || '';
                            if (firstLabelText.includes('내용') || firstLabelText.includes('표시할') || firstLabelText.includes('text') || firstLabelText.includes('Text')) {
                                $firstFormGroup.hide().css('display', 'none !important');
                            }
                        }
                    }
                    
                    // URL 입력 필드에 포커스 및 지연 후 다시 한 번 텍스트 필드 숨기기 확인
                    if ($urlInput && $urlInput.length > 0) {
                        setTimeout(function() {
                            $urlInput.focus();
                            
                            // 지연 후 다시 한 번 텍스트 입력 필드 숨기기 (확실하게)
                            if ($textInput && $textInput.length > 0) {
                                $textInput.closest('.form-group, .note-form-group, .note-input-group, div').hide().css('display', 'none !important');
                                $textInput.hide().css('display', 'none !important');
                            }
                            
                            // 첫 번째 form-group도 다시 확인
                            var $firstGroup = $modal.find('.form-group, .note-form-group, .note-input-group').first();
                            if ($firstGroup.length > 0 && $firstGroup.find('input').first() !== $urlInput) {
                                var firstLabel = $firstGroup.find('label').text() || '';
                                if (firstLabel.includes('내용') || firstLabel.includes('표시할')) {
                                    $firstGroup.hide().css('display', 'none !important');
                                }
                            }
                        }, 300);
                    }
                    
                    // 링크 삽입 버튼 클릭 이벤트 오버라이드 - data-selected 속성 확인
                    // 더 확실하게 처리하기 위해 여러 방법으로 버튼 찾기
                    var $linkBtn = $modal.find('button').filter(function() {
                        var btnText = $(this).text().trim();
                        return btnText.includes('링크') || btnText.includes('Link') || btnText.includes('삽입') || btnText.includes('Insert');
                    });
                    
                    // 버튼을 찾지 못한 경우 다른 방법으로 찾기
                    if ($linkBtn.length === 0) {
                        $linkBtn = $modal.find('.note-link-btn, .btn-primary, button[type="submit"]');
                    }
                    
                    if ($linkBtn.length > 0) {
                        // 기존 이벤트 제거 후 새로 바인딩
                        $linkBtn.off('click.image-link').on('click.image-link', function(e) {
                            // data-selected 속성이 있는 이미지 확인
                            var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                            var $iframeBody = $(iframeDoc.body);
                            var $img = $iframeBody.find('img[data-selected="true"]');
                            
                            console.log('Link button clicked, selected image:', $img.length);
                            
                            if ($img.length > 0) {
                                // 이미지가 선택된 경우 - 기본 동작 방지하고 이미지에 링크 적용
                                e.preventDefault();
                                e.stopPropagation();
                                e.stopImmediatePropagation();
                                
                                // URL 가져오기
                                var linkUrl = $urlInput && $urlInput.length > 0 ? $urlInput.val().trim() : '';
                                
                                if (!linkUrl) {
                                    alert('링크 URL을 입력해주세요.');
                                    return false;
                                }
                                
                                console.log('Applying link to image:', linkUrl);
                                
                                // 이미지에 링크 적용
                                if ($img.closest('a').length > 0) {
                                    // 기존 링크 수정
                                    $img.closest('a').attr('href', linkUrl).attr('target', '_blank').attr('rel', 'noopener noreferrer');
                                } else {
                                    // 새 링크 추가
                                    $img.wrap('<a href="' + linkUrl + '" target="_blank" rel="noopener noreferrer"></a>');
                                }
                                
                                // Summernote에 변경사항 반영
                                var newCode = iframeDoc.body.innerHTML;
                                $editor.summernote('code', newCode);
                                
                                // data-selected 속성 제거
                                $img.removeAttr('data-selected');
                                
                                // 모달 닫기
                                $modal.modal('hide');
                                
                                // 기본 링크 생성 방지 (텍스트로 링크 생성되는 문제 해결)
                                return false;
                            }
                            // 텍스트 링크인 경우 기본 동작 유지 (이벤트 핸들러를 통과시킴)
                        });
                    } else {
                        console.warn('Link button not found in modal');
                    }
            } else {
                // 이미지가 선택되지 않았으면 일반 텍스트 링크 처리
                // 텍스트 선택 문제 해결: 모달이 열릴 때 선택된 텍스트 확인
                setTimeout(function() {
                    try {
                        var selection = iframeDoc.getSelection();
                        if (selection && selection.rangeCount > 0 && selection.toString().trim()) {
                            var selectedText = selection.toString();
                            var $textInput = $modal.find('input[type="text"]').filter(function() {
                                return $(this).closest('.form-group, .note-form-group').find('label').text().includes('내용') || 
                                       $(this).closest('.form-group, .note-form-group').find('label').text().includes('표시할');
                            });
                            
                            if ($textInput.length > 0 && !$textInput.val()) {
                                $textInput.val(selectedText);
                            }
                        }
                    } catch (e) {
                        // 선택 오류 무시
                    }
                }, 100);
            }
        } catch (e) {
            console.log('Error customizing link dialog:', e);
        }
        }, 100);
    });
    
    // 이미지 링크 입력 모달 표시 함수
    function showImageLinkModal($img, $editor, editableElement) {
        console.log('showImageLinkModal called', $img, $editor, editableElement);
        
        // 기존 링크 정보 가져오기
        var currentLink = $img.closest('a').attr('href') || '';
        var currentTarget = $img.closest('a').attr('target') || '';
        var openInNewWindow = currentTarget === '_blank';
        
        console.log('Current link:', currentLink, 'Open in new window:', openInNewWindow);
        
        // 모달 HTML 생성
        var modalHtml = `
            <div class="modal fade" id="imageLinkModal" tabindex="-1" role="dialog" aria-labelledby="imageLinkModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageLinkModalLabel">이미지 링크 추가</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="imageLinkUrl">링크 URL</label>
                                <input type="url" class="form-control" id="imageLinkUrl" placeholder="https://example.com" value="${currentLink}">
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="imageLinkNewWindow" ${openInNewWindow ? 'checked' : ''}>
                                    <label class="form-check-label" for="imageLinkNewWindow">
                                        새 창에서 열기
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
                            <button type="button" class="btn btn-primary" id="applyImageLinkBtn">적용</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // 기존 모달이 있으면 제거
        $('#imageLinkModal').remove();
        
        // 모달 추가
        $('body').append(modalHtml);
        var $modal = $('#imageLinkModal');
        
        console.log('Modal created and appended to body');
        
        // 모달 표시 (Bootstrap 4/5 호환)
        if (typeof $.fn.modal === 'function') {
            $modal.modal('show');
            console.log('Modal show called');
        } else {
            console.error('Bootstrap modal function not available');
            $modal.addClass('show').css('display', 'block');
            $('body').append('<div class="modal-backdrop fade show"></div>');
        }
        
        // 적용 버튼 클릭 이벤트
        $('#applyImageLinkBtn').on('click', function() {
            var linkUrl = $('#imageLinkUrl').val().trim();
            var openNewWindow = $('#imageLinkNewWindow').is(':checked');
            
            if (!linkUrl) {
                alert('링크 URL을 입력해주세요.');
                return;
            }
            
            // URL 유효성 검사 (http:// 또는 https://로 시작하는지 확인)
            if (!linkUrl.match(/^https?:\/\//i)) {
                linkUrl = 'http://' + linkUrl;
            }
            
            // 이미지에 링크 적용
            var $editable = $(editableElement);
            var $targetImg = $editable.find('img[data-selected="true"]');
            if ($targetImg.length === 0) {
                $targetImg = $editable.find('img.note-float');
            }
            
            if ($targetImg.length === 0) {
                $targetImg = $editable.find('img').last();
            }
            
            if ($targetImg.length > 0) {
                if ($targetImg.closest('a').length > 0) {
                    // 기존 링크 수정
                    var $link = $targetImg.closest('a');
                    $link.attr('href', linkUrl);
                    if (openNewWindow) {
                        $link.attr('target', '_blank').attr('rel', 'noopener noreferrer');
                    } else {
                        $link.removeAttr('target').removeAttr('rel');
                    }
                } else {
                    // 새 링크 추가
                    var targetAttr = openNewWindow ? ' target="_blank" rel="noopener noreferrer"' : '';
                    $targetImg.wrap('<a href="' + linkUrl + '"' + targetAttr + '></a>');
                }
                
                // Summernote에 변경사항 반영 (이미 DOM에 반영되었으므로 code 업데이트 불필요)
                // 하지만 확실하게 하기 위해 trigger
                $editor.summernote('code', $editable.html());
                
                // data-selected 속성 제거
                $targetImg.removeAttr('data-selected');
            }
            
            // 모달 닫기
            $modal.modal('hide');
        });
        
        // 모달이 닫힐 때 제거
        $modal.on('hidden.bs.modal', function() {
            $(this).remove();
        });
        
        // Enter 키로 적용
        $('#imageLinkUrl').on('keypress', function(e) {
            if (e.which === 13) {
                $('#applyImageLinkBtn').click();
            }
        });
    }
    
    // Summernote 이미지 팝오버에 링크 버튼 추가
    function setupImageLinkFeature() {
        console.log('setupImageLinkFeature called (edit)');
        // 이미지 팝오버가 나타날 때마다 링크 버튼 추가
        var popoverObserver = new MutationObserver(function(mutations) {
            var $popover = $('.note-image-popover');
            if ($popover.length > 0 && $popover.is(':visible')) {
                var $removeGroup = $popover.find('.note-remove');
                if ($removeGroup.length > 0) {
                    // 이미 링크 버튼이 있으면 스킵
                    if ($removeGroup.prev('.note-link').length > 0) {
                        return;
                    }
                    
                    console.log('Adding link button to popover (edit)');
                    
                    // 링크 버튼 그룹 생성
                    var $linkGroup = $('<div class="note-btn-group note-link"></div>');
                    var $linkBtn = $('<button type="button" class="note-btn" tabindex="-1" aria-label="링크 추가"><i class="note-icon-link"></i></button>');
                    $linkGroup.append($linkBtn);
                    
                    // 삭제 버튼 그룹 왼쪽에 삽입
                    $removeGroup.before($linkGroup);
                    
                    // 링크 버튼 클릭 이벤트
                    $linkBtn.off('click').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        
                        console.log('Link button clicked in popover (edit)');
                        
                        // 선택된 이미지 찾기
                        var $editor = $('#content');
                        
                        // note-editable 영역 찾기 (iframe이 아닌 경우)
                        var $editable = $editor.next('.note-editor').find('.note-editable');
                        
                        // note-editable을 찾지 못한 경우 다른 방법 시도
                        if ($editable.length === 0) {
                            $editable = $('.note-editor .note-editable');
                        }
                        
                        // 여전히 찾지 못한 경우
                        if ($editable.length === 0) {
                            $editable = $('#content').siblings('.note-editor').find('.note-editable');
                        }
                        
                        console.log('Editable area found:', $editable.length);
                        
                        if ($editable.length > 0) {
                            try {
                                var $img = $editable.find('img[data-selected="true"]');
                                
                                console.log('Image with data-selected:', $img.length);
                                
                                if ($img.length === 0) {
                                    // data-selected가 없으면 note-float 클래스로 찾기
                                    $img = $editable.find('img.note-float');
                                    console.log('Image with note-float:', $img.length);
                                }
                                
                                // note-float도 없으면 마지막 이미지 사용
                                if ($img.length === 0) {
                                    $img = $editable.find('img').last();
                                    console.log('Using last image:', $img.length);
                                }
                                
                                if ($img.length > 0) {
                                    // 이미지 선택 상태로 설정
                                    $editable.find('img').removeAttr('data-selected');
                                    $img.attr('data-selected', 'true');
                                    
                                    console.log('Calling showImageLinkModal (edit)');
                                    // 커스텀 링크 입력 모달 열기
                                    showImageLinkModal($img, $editor, $editable[0]);
                                } else {
                                    console.error('No image found to link (edit)');
                                    alert('이미지를 찾을 수 없습니다.');
                                }
                            } catch (err) {
                                console.error('Error accessing editable area (edit):', err);
                                alert('에디터에 접근할 수 없습니다.');
                            }
                        } else {
                            console.error('Editable area not found (edit)');
                            alert('에디터를 찾을 수 없습니다.');
                        }
                    });
                }
            }
        });
        
        // body 전체를 감시하여 팝오버가 나타날 때 감지
        popoverObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // 주기적으로도 확인 (추가 안전장치)
        setInterval(function() {
            var $popover = $('.note-image-popover');
            if ($popover.length > 0 && $popover.is(':visible')) {
                var $removeGroup = $popover.find('.note-remove');
                if ($removeGroup.length > 0 && $removeGroup.prev('.note-link').length === 0) {
                    // 링크 버튼 그룹이 없으면 추가
                    var $linkGroup = $('<div class="note-btn-group note-link"></div>');
                    var $linkBtn = $('<button type="button" class="note-btn" tabindex="-1" aria-label="링크 추가"><i class="note-icon-link"></i></button>');
                    $linkGroup.append($linkBtn);
                    $removeGroup.before($linkGroup);
                    
                    // 링크 버튼 클릭 이벤트 (이미 바인딩되어 있으면 스킵)
                    if (!$linkBtn.data('link-bound')) {
                        $linkBtn.data('link-bound', true);
                        $linkBtn.off('click').on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            
                            console.log('Link button clicked in setInterval (edit)');
                            
                            var $editor = $('#content');
                            var $iframe = $editor.next('.note-editor').find('iframe');
                            if ($iframe.length > 0) {
                                var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                                var $iframeBody = $(iframeDoc.body);
                                var $img = $iframeBody.find('img[data-selected="true"]');
                                
                                if ($img.length === 0) {
                                    $img = $iframeBody.find('img.note-float');
                                }
                                
                                if ($img.length > 0) {
                                    // 이미지 선택 상태로 설정
                                    $iframeBody.find('img').removeAttr('data-selected');
                                    $img.attr('data-selected', 'true');
                                    
                                    console.log('Calling showImageLinkModal from setInterval (edit)');
                                    // 커스텀 링크 입력 모달 열기
                                    showImageLinkModal($img, $editor, iframeDoc);
                                } else {
                                    console.error('No image found to link in setInterval (edit)');
                                }
                            }
                        });
                    }
                }
            }
        }, 500);
    }
    
    // Summernote 초기화 후 이미지 팝오버 링크 버튼 설정
    setTimeout(function() {
        setupImageLinkFeature();
    }, 2000);
});
</script>
@endpush
@endsection
