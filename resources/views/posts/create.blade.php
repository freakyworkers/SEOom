@extends('layouts.app')

@section('title', '게시글 작성')

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="bi bi-pencil-square"></i> 게시글 작성</h4>
    </div>
    <div class="card-body">
        @if(isset($parentPost))
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>답글 작성</strong>
                <div class="mt-2">
                    <strong>원본 게시글:</strong> 
                    <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $parentPost->id]) }}" 
                       class="text-decoration-none">
                        {{ $parentPost->title }}
                    </a>
                </div>
            </div>
        @endif
        <form method="POST" action="{{ route('posts.store', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" enctype="multipart/form-data" id="postCreateForm">
            @csrf
            @if(isset($parentPost))
                <input type="hidden" name="reply_to" value="{{ $parentPost->id }}">
            @endif

            <div class="mb-3">
                <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('title') is-invalid @enderror" 
                       id="title" 
                       name="title" 
                       value="{{ old('title') }}" 
                       required 
                       autofocus>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($board->force_secret)
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>비밀글 상시 활성화</strong>
                    <p class="mb-0 mt-2">이 게시판의 모든 게시글은 자동으로 비밀글로 처리됩니다.</p>
                </div>
            @elseif($board->enable_secret)
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_secret" 
                               name="is_secret" 
                               value="1"
                               {{ old('is_secret') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_secret">
                            비밀글
                        </label>
                        <small class="d-block text-muted">비밀글은 작성자와 운영자만 확인할 수 있습니다.</small>
                    </div>
                </div>
            @endif

            @if(in_array($board->type, ['photo', 'bookmark', 'blog', 'pinterest']))
                <div class="mb-3">
                    <label for="thumbnail" class="form-label">썸네일 이미지</label>
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
                    </div>
                </div>
            @endif

            @if($board->topics()->count() > 0)
                <div class="mb-3">
                    <label class="form-label">주제 <span class="text-danger">*</span></label>
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
                                       {{ in_array($topic->id, old('topic_ids', [])) ? 'checked' : '' }}>
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
                            $selectedTopicId = old('topic_ids', []);
                            $selectedTopicId = is_array($selectedTopicId) && count($selectedTopicId) > 0 ? $selectedTopicId[0] : null;
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
                           value="{{ old('link') }}" 
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
                        <div class="bookmark-item mb-2">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" 
                                           class="form-control bookmark-item-name" 
                                           name="bookmark_items[0][name]" 
                                           placeholder="항목 이름 (예: 사이트이름)">
                                </div>
                                <div class="col-md-7">
                                    <input type="text" 
                                           class="form-control bookmark-item-value" 
                                           name="bookmark_items[0][value]" 
                                           placeholder="내용 (예: ATM)">
                                </div>
                            </div>
                        </div>
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
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" 
                                           class="form-control" 
                                           name="bookmark_items[0][name]" 
                                           value="{{ old('bookmark_items.0.name', '혜택') }}"
                                           placeholder="항목 제목 (예: 혜택)">
                                </div>
                                <div class="col-md-7">
                                    <input type="text" 
                                           class="form-control" 
                                           name="bookmark_items[0][value]" 
                                           value="{{ old('bookmark_items.0.value') }}"
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
                        <option value="general" {{ old('event_type') == 'general' ? 'selected' : '' }}>공지형 이벤트</option>
                        <option value="application" {{ old('event_type') == 'application' ? 'selected' : '' }}>신청형 이벤트</option>
                        <option value="quiz" {{ old('event_type') == 'quiz' ? 'selected' : '' }}>정답형 이벤트</option>
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
                           value="{{ old('event_start_date') }}">
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
                               value="{{ old('event_end_date') }}"
                               style="flex: 1;">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="event_end_undecided" 
                                   name="event_end_undecided" 
                                   value="1"
                                   {{ old('event_end_undecided') ? 'checked' : '' }}>
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
                            <div class="event-option-item mb-2">
                                <div class="row g-2">
                                    <div class="col-md-10">
                                        <input type="text" 
                                               class="form-control event-option-text" 
                                               name="event_options[0][text]" 
                                               placeholder="선택지 내용">
                                    </div>
                                    {{-- 정답은 게시글 작성 시 미리 정하지 않음 (운영자가 나중에 선택) --}}
                                </div>
                            </div>
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
                          required>{{ old('content', $board->post_template ?? '') }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($board->enable_attachments ?? true)
            <div class="mb-3">
                <label class="form-label">첨부파일</label>
                <div class="d-flex gap-2 align-items-center">
                    <input type="file" 
                           class="@error('attachments.*') is-invalid @enderror" 
                           id="attachments" 
                           name="attachments[]" 
                           multiple
                           accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.zip"
                           style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
                    <label for="attachments" class="btn btn-outline-secondary mb-0" style="cursor: pointer; white-space: nowrap; margin-bottom: 0 !important;">
                        <i class="bi bi-paperclip me-1"></i>파일 선택
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="fileList" 
                           placeholder="선택된 파일 없음" 
                           readonly>
                </div>
                <small class="form-text text-muted">
                    최대 10MB까지 업로드 가능합니다. 여러 파일을 선택할 수 있습니다.
                </small>
                @error('attachments.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            @endif

            @if($board->enable_author_comment_adopt && auth()->check())
                <div class="mb-3">
                    <label for="adoption_points" class="form-label">채택 포인트</label>
                    <input type="number" 
                           class="form-control @error('adoption_points') is-invalid @enderror" 
                           id="adoption_points" 
                           name="adoption_points" 
                           value="{{ old('adoption_points', 0) }}" 
                           min="0"
                           placeholder="댓글 채택 시 지급할 포인트를 입력하세요">
                    <small class="form-text text-muted">
                        댓글 채택 시 지급할 포인트를 설정합니다. (0 이상)
                    </small>
                    @error('adoption_points')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            @if(auth()->user()->canManage())
                @php
                    $pointColor = $site->getSetting('color_point_main', '#0d6efd');
                @endphp
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_notice" 
                               name="is_notice" 
                               value="1" 
                               {{ old('is_notice') ? 'checked' : '' }}>
                        <label class="form-check-label d-flex align-items-center gap-2" for="is_notice">
                            <span>공지사항</span>
                            <span class="badge notice-badge" style="display: none; background-color: {{ $pointColor }};">
                                <i class="bi bi-megaphone"></i>
                            </span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_pinned" 
                               name="is_pinned" 
                               value="1" 
                               {{ old('is_pinned') ? 'checked' : '' }}>
                        <label class="form-check-label d-flex align-items-center gap-2" for="is_pinned">
                            <span>상단 고정</span>
                            <span class="badge pinned-badge" style="display: none; background-color: {{ $pointColor }};">
                                <i class="bi bi-pin-angle-fill"></i>
                            </span>
                        </label>
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('posts.index', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" 
                   class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> 취소
                </a>
                <button type="submit" class="btn btn-primary" id="postSubmitBtn">
                    <i class="bi bi-check-circle"></i> 작성
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// 금지단어 목록 가져오기
@php
    $bannedWordsString = $site->getSetting('banned_words', '');
    $bannedWordsArray = array_filter(array_map('trim', explode(',', $bannedWordsString)));
@endphp
var bannedWords = @json($bannedWordsArray);
// 금지단어 체크 함수
function checkBannedWords(title, content) {
    if (bannedWords.length === 0) {
        return false;
    }
    // HTML 태그 제거
    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = content;
    var textContent = tempDiv.textContent || tempDiv.innerText || '';
    for (var i = 0; i < bannedWords.length; i++) {
        var bannedWord = bannedWords[i];
        if (title.toLowerCase().indexOf(bannedWord.toLowerCase()) !== -1 || 
            textContent.toLowerCase().indexOf(bannedWord.toLowerCase()) !== -1) {
            return true;
        }
    }
    return false;
}

$(document).ready(function() {
    // 폼 제출 전 금지단어 체크 및 로딩 표시
    $('#postCreateForm').on('submit', function(e) {
        var title = $('#title').val();
        var content = '';
        // Summernote 사용 시 코드에서 가져오기
        if (typeof $('#content').summernote !== 'undefined' && $('#content').summernote('code')) {
            content = $('#content').summernote('code');
        } else {
            content = $('#content').val();
        }
        if (checkBannedWords(title, content)) {
            e.preventDefault();
            // 모달 표시
            var modalHtml = '<div class="modal fade" id="bannedWordsModal" tabindex="-1" aria-labelledby="bannedWordsModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="bannedWordsModalLabel">알림</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">금지단어가 포함된 게시글을 작성할 수 없습니다.</div><div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button></div></div></div></div>';
            $('#bannedWordsModal').remove();
            $('body').append(modalHtml);
            var modal = new bootstrap.Modal(document.getElementById('bannedWordsModal'));
            modal.show();
            $('#bannedWordsModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
            return false;
        }
        
        // 금지단어 체크 통과 시 로딩 표시
        var $submitBtn = $('#postSubmitBtn');
        if ($submitBtn.length > 0) {
            $submitBtn.prop('disabled', true);
            var originalHtml = $submitBtn.html();
            $submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>저장 중...');
            
            // 폼 제출 실패 시 버튼 복원 (예: 네트워크 오류 등)
            $(window).on('beforeunload', function() {
                // 페이지가 이동되면 자동으로 복원됨
            });
        }
    });
    
    // 게시글 양식이 있으면 기본값으로 설정
    var initialContent = @json(old('content', $board->post_template ?? ''));
    
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
                        // 게시글 양식이 있으면 에디터에 설정
                        if (initialContent) {
                            $('#content').summernote('code', initialContent);
                        }
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
    
    // 이미지 선택 상태 저장 (전역 변수)
    var selectedImageElement = null;
    var isImageSelected = false;
    
    // Summernote ?? ??(body) ?? ?? - iframe / lite ??
    function getEditorArea() {
        var $editor = $('#content');
        var $noteEditor = $editor.next('.note-editor');
        var $iframe = $noteEditor.find('iframe');
        
        if ($iframe.length > 0) {
            var doc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
            return { $body: $(doc.body), doc: doc };
        }
        
        return { $body: $noteEditor.find('.note-editable'), doc: document };
    }
    
    // 모달에 파일 설정하는 헬퍼 함수
    function setupModalWithFile($modal, file) {
        console.log('Setting up modal with file:', file.name);
        currentImageModal = $modal;
        $modal.data('allow-close', false);
        $modal.data('selected-file', file);
        isUploading = false;
        
        // 파일명 표시 영역 찾기
        var $fileGroup = $modal.find('.note-group-select-from-files');
        if ($fileGroup.length === 0) {
            $fileGroup = $modal.find('input[type="file"]').parent();
        }
        var $fileNameDisplay = $fileGroup.find('.note-selected-file-name');
        if ($fileNameDisplay.length === 0) {
            $fileNameDisplay = $('<div class="note-selected-file-name text-muted small mt-2"></div>');
            $fileGroup.append($fileNameDisplay);
        }
        
        $fileNameDisplay.html('<i class="bi bi-file-image"></i> 선택된 파일: <strong>' + file.name + '</strong>');
        
        // "그림 삽입" 버튼 활성화
        var $insertBtn = $modal.find('.note-image-btn');
        $insertBtn.prop('disabled', false);
        
        console.log('Modal setup complete');
    }
    
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
                    // Summernote에 이미지 삽입
                    var $editor = $('#content');
                    var imageUrl = response.url;
                    console.log('Inserting image:', imageUrl);
                    
                    // 에디터에 포커스 주기
                    $editor.summernote('focus');
                    
                    // insertImage 메서드 사용 (Summernote의 기본 메서드)
                    $editor.summernote('insertImage', imageUrl);
                    console.log('Image inserted using insertImage method');
                    
                    // 이미지 삽입 후 스타일 적용 (약간의 지연 후)
                    setTimeout(function() {
                        var code = $editor.summernote('code');
                        // 이미지 태그에 스타일 추가
                        var escapedUrl = imageUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        var newCode = code.replace(new RegExp('<img([^>]*src=["\']' + escapedUrl + '["\'][^>]*)>', 'gi'), 
                            function(match) {
                                // 이미 class나 style이 있으면 추가하지 않음
                                if (match.indexOf('class=') === -1 && match.indexOf('style=') === -1) {
                                    return match.replace('>', ' class="img-fluid" style="max-width: 100%; height: auto;">');
                                } else if (match.indexOf('class=') === -1) {
                                    // class만 없으면 추가
                                    return match.replace('>', ' class="img-fluid">');
                                } else if (match.indexOf('style=') === -1) {
                                    // style만 없으면 추가
                                    return match.replace('>', ' style="max-width: 100%; height: auto;">');
                                }
                                return match;
                            });
                        if (newCode !== code) {
                            $editor.summernote('code', newCode);
                            console.log('Image styled');
                        }
                    }, 200);
                    
                    // 에디터 다시 포커스
                    setTimeout(function() {
                        $editor.summernote('focus');
                        console.log('Editor focused after image insertion');
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
    
    // 전역 변수로 모달 참조 저장 (가장 먼저 선언)
    var currentImageModal = null;
    var isUploading = false;
    
    // 모달이 닫히는 것을 막기 위한 전역 핸들러 (가장 먼저 등록)
    $(document).on('hide.bs.modal', '.note-image-dialog', function(e) {
        var $modal = $(this);
        var allowClose = $modal.data('allow-close');
        var uploading = $modal.data('uploading') || isUploading;
        var selectedFile = $modal.data('selected-file');
        
        console.log('Global handler: Modal trying to hide, allowClose:', allowClose, 'uploading:', uploading, 'selectedFile:', selectedFile ? selectedFile.name : 'none');
        
        // allow-close가 false이거나 업로드 중이면 모달 닫기 방지
        if (allowClose === false || uploading === true || isUploading === true || (selectedFile && allowClose !== true)) {
            console.log('Global handler: Preventing modal close');
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
        $modal.data('allow-close', true); // 초기값은 true
        $modal.data('selected-file', null);
        
        // 이미지 링크 입력 필드 추가 (이미 있으면 스킵)
        var $linkInputGroup = $modal.find('.note-image-link-group');
        if ($linkInputGroup.length === 0) {
            // URL 입력 필드 그룹 찾기
            var $urlGroup = $modal.find('.note-group-image-url');
            if ($urlGroup.length === 0) {
                // 모달 본문 찾기
                var $modalBody = $modal.find('.modal-body');
                if ($modalBody.length === 0) {
                    $modalBody = $modal.find('.note-image-dialog-body');
                }
                
                // 링크 입력 필드 그룹 생성
                $linkInputGroup = $('<div class="note-image-link-group mt-3 mb-3" style="padding: 10px; background-color: #f8f9fa; border-radius: 5px;"></div>');
                $linkInputGroup.html(
                    '<label class="form-label small fw-bold mb-2"><i class="bi bi-link-45deg me-1"></i>이미지 링크 (선택사항)</label>' +
                    '<input type="text" class="form-control form-control-sm note-image-link-url" placeholder="https://example.com" style="font-size: 0.875rem;">' +
                    '<small class="form-text text-muted">이미지에 하이퍼링크를 추가하려면 URL을 입력하세요. 비워두면 링크가 추가되지 않습니다.</small>'
                );
                
                // URL 입력 필드 다음에 추가
                if ($urlGroup.length > 0) {
                    $linkInputGroup.insertAfter($urlGroup);
                } else if ($modalBody.length > 0) {
                    $modalBody.append($linkInputGroup);
                } else {
                    // 파일 입력 필드 다음에 추가
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
        console.log('File input found:', $fileInput.length);
        
        if ($fileInput.length > 0) {
            // 파일명 표시 영역 추가
            var $fileGroup = $fileInput.closest('.note-group-select-from-files');
            if ($fileGroup.length === 0) {
                $fileGroup = $fileInput.parent();
            }
            
            var $fileNameDisplay = $fileGroup.find('.note-selected-file-name');
            if ($fileNameDisplay.length === 0) {
                $fileNameDisplay = $('<div class="note-selected-file-name text-muted small mt-2"></div>');
                $fileGroup.append($fileNameDisplay);
            }
            
            // 파일 선택 이벤트 - 파일명만 표시하고 업로드는 "그림 삽입" 버튼에서 처리
            // 가장 먼저 실행되도록 즉시 등록 (capture phase에서 실행)
            $fileInput.off('change.show-filename change').on('change.show-filename', function(e) {
                console.log('File input changed - custom handler (FIRST)');
                
                // 즉시 플래그 설정 (이벤트 전파 차단 전에)
                var files = this.files;
                if (files && files.length > 0) {
                    var fileName = files[0].name;
                    console.log('File selected:', fileName);
                    
                    // 즉시 플래그 설정하여 모달이 닫히는 것을 막기
                    $modal.data('selected-file', files[0]);
                    $modal.data('allow-close', false);
                    isUploading = false; // 아직 업로드 시작 전
                    currentImageModal = $modal;
                    
                    console.log('Set allow-close to false in file input change handler');
                    
                    // 파일명 표시
                    $fileNameDisplay.html('<i class="bi bi-file-image"></i> 선택된 파일: <strong>' + fileName + '</strong>');
                    
                    // "그림 삽입" 버튼 활성화
                    var $insertBtn = $modal.find('.note-image-btn');
                    $insertBtn.prop('disabled', false);
                }
                
                // 이벤트 전파 차단하여 Summernote의 기본 동작 방지
                e.stopPropagation();
                e.preventDefault();
                e.stopImmediatePropagation();
                
                return false;
            });
            
            // 네이티브 이벤트 리스너로도 등록 (가장 먼저 실행되도록)
            if ($fileInput[0]) {
                $fileInput[0].addEventListener('change', function(e) {
                    console.log('Native change event handler');
                    var files = this.files;
                    if (files && files.length > 0) {
                        $modal.data('allow-close', false);
                        currentImageModal = $modal;
                        console.log('Set allow-close to false in native handler');
                    }
                }, true); // capture phase
            }
            
            // 추가로 모든 이벤트 리스너 제거 후 재등록 (Summernote의 기본 핸들러 제거)
            setTimeout(function() {
                $fileInput.off('change').on('change.show-filename', function(e) {
                    console.log('File input changed - second handler');
                    e.stopPropagation();
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                });
            }, 100);
        } else {
            console.warn('No file input found in modal');
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
                
                // 파일이 선택된 경우 파일 업로드
                if (selectedFile) {
                    console.log('Uploading selected file:', selectedFile.name);
                    isUploading = true;
                    $modal.data('uploading', true);
                    $modal.data('allow-close', false);
                    
                    // 링크 URL 가져오기
                    var $linkInput = $modal.find('.note-image-link-url');
                    var linkUrl = $linkInput.length > 0 ? $linkInput.val().trim() : '';
                    
                    uploadImage(selectedFile, function(imageUrl) {
                        // 업로드 완료 후 이미지 삽입 및 링크 적용
                        if (linkUrl) {
                            // 링크가 있으면 이미지를 링크로 감싸서 삽입
                            var $iframe = $('#content').next('.note-editor').find('iframe');
                            if ($iframe.length > 0) {
                                var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                                var $iframeBody = $(iframeDoc.body);
                                
                                // 이미지 삽입
                                $('#content').summernote('insertImage', imageUrl);
                                
                                // 삽입된 이미지에 링크 적용
                                setTimeout(function() {
                                    var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                                    var $iframeBody = $(iframeDoc.body);
                                    var $img = $iframeBody.find('img').last();
                                    if ($img.length > 0 && !$img.closest('a').length) {
                                        $img.wrap('<a href="' + linkUrl + '" target="_blank" rel="noopener noreferrer"></a>');
                                        // Summernote에 변경사항 반영
                                        var newCode = iframeDoc.body.innerHTML;
                                        $('#content').summernote('code', newCode);
                                    }
                                }, 100);
                            } else {
                                // iframe이 없으면 일반 삽입
                                $('#content').summernote('insertImage', imageUrl);
                            }
                        } else {
                            // 링크가 없으면 일반 삽입
                            $('#content').summernote('insertImage', imageUrl);
                        }
                        
                        // 업로드 완료 후 모달 닫기
                        isUploading = false;
                        $modal.data('uploading', false);
                        $modal.data('allow-close', true);
                        setTimeout(function() {
                            $modal.modal('hide');
                            // 링크 입력 필드 초기화
                            $linkInput.val('');
                        }, 200);
                    });
                } 
                // URL이 입력된 경우 URL로 이미지 삽입
                else if (imageUrl) {
                    console.log('Inserting image from URL:', imageUrl);
                    
                    // 링크 URL 가져오기
                    var $linkInput = $modal.find('.note-image-link-url');
                    var linkUrl = $linkInput.length > 0 ? $linkInput.val().trim() : '';
                    
                    // 이미지 삽입
                    $('#content').summernote('insertImage', imageUrl);
                    
                    // 링크가 있으면 이미지에 링크 적용
                    if (linkUrl) {
                        setTimeout(function() {
                            var $iframe = $('#content').next('.note-editor').find('iframe');
                            if ($iframe.length > 0) {
                                var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                                var $iframeBody = $(iframeDoc.body);
                                var $img = $iframeBody.find('img').last();
                                if ($img.length > 0 && !$img.closest('a').length) {
                                    $img.wrap('<a href="' + linkUrl + '" target="_blank" rel="noopener noreferrer"></a>');
                                    // Summernote에 변경사항 반영
                                    var newCode = iframeDoc.body.innerHTML;
                                    $('#content').summernote('code', newCode);
                                }
                            }
                        }, 100);
                    }
                    
                    $modal.data('allow-close', true);
                    setTimeout(function() {
                        $modal.modal('hide');
                        // 링크 입력 필드 초기화
                        if ($linkInput.length > 0) {
                            $linkInput.val('');
                        }
                    }, 200);
                } 
                // 둘 다 없는 경우 경고
                else {
                    alert('파일을 선택하거나 이미지 URL을 입력해주세요.');
                }
                
                return false;
            });
        }
    });
    
    // 모달이 닫히려고 할 때 업로드 중이거나 파일이 선택되어 있으면 막기
    $(document).on('hide.bs.modal', '.note-image-dialog', function(e) {
        var $modal = $(this);
        var allowClose = $modal.data('allow-close');
        var uploading = $modal.data('uploading') || isUploading;
        var selectedFile = $modal.data('selected-file');
        
        console.log('Modal trying to hide, allowClose:', allowClose, 'uploading:', uploading, 'selectedFile:', selectedFile ? selectedFile.name : 'none');
        
        // allow-close가 false이거나 업로드 중이면 모달 닫기 방지
        if (allowClose === false || uploading === true || isUploading === true || (selectedFile && allowClose !== true)) {
            console.log('Preventing modal close');
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    });
    
    // 폼 제출 전 Summernote 내용을 textarea에 저장
    $('form').on('submit', function() {
        $('#content').val($('#content').summernote('code'));
    });
});

// 이 코드는 setupFileInput에서 처리하므로 제거

// 파일 선택 후 목록 업데이트
function updateFileList(input) {
    const fileList = document.getElementById('fileList');
    
    if (!input) {
        return;
    }
    
    if (!fileList) {
        return;
    }
    
    if (input.files && input.files.length > 0) {
        const fileNames = Array.from(input.files).map(file => file.name).join(', ');
        fileList.value = fileNames;
    } else {
        fileList.value = '선택된 파일 없음';
    }
}

// DOM 로드 후 파일 입력 필드에 이벤트 리스너 추가
(function() {
    function setupFileInput() {
        const fileInput = document.getElementById('attachments');
        
        if (!fileInput) {
            return;
        }
        
        // change 이벤트 리스너 추가
        fileInput.addEventListener('change', function(e) {
            updateFileList(e.target);
        });
    }
    
    // 여러 시점에서 실행 시도
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupFileInput);
    } else {
        setupFileInput();
    }
    
    // 추가 보장
    setTimeout(setupFileInput, 100);
    setTimeout(setupFileInput, 500);
    setTimeout(setupFileInput, 1000);
})();

// 썸네일 미리보기
$(document).ready(function() {
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
    var bookmarkItemIndex = 1;
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

    // 첫 번째 항목에도 삭제 버튼 추가
    $(document).ready(function() {
        if ($('.bookmark-item').length > 0 && $('.bookmark-item:first .remove-bookmark-item').length === 0) {
            $('.bookmark-item:first').find('.col-md-7').after(`
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-bookmark-item" title="삭제">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `);
            // 첫 번째 항목의 col-md-7을 col-md-6으로 변경
            $('.bookmark-item:first .col-md-7').removeClass('col-md-7').addClass('col-md-6');
        }
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
    var eventOptionIndex = 1;
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
                                    {{-- 정답은 게시글 작성 시 미리 정하지 않음 --}}
                                </div>
            </div>
        `;
        $('#event-options-container').append(newOption);
        eventOptionIndex++;
    });

    {{-- 정답 체크박스는 제거됨 (운영자가 나중에 선택) --}}

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

    // 페이지 로드 시 초기 상태 확인
    if ($('#is_notice').is(':checked')) {
        $('.notice-badge').show();
    }
    if ($('#is_pinned').is(':checked')) {
        $('.pinned-badge').show();
    }
    
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
    
    // Summernote 링크 삽입 모달 커스터마이징 - 이미지에 링크 적용 (해결 방법 4 적용)
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
                
                // 워드프레스 방식: 이미지가 선택되어 있으면 링크 삽입 모달 커스터마이징
                if (imageSelected && $selectedImg && $selectedImg.length > 0) {
                    console.log('Image selected for linking, customizing link dialog');
                    
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
                        // URL 입력 필드에 포커스 및 지연 후 다시 한 번 텍스트 필드 숨기기 확인
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
                        
                        console.log('Link button clicked in popover');
                        
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
                                    
                                    console.log('Calling showImageLinkModal');
                                    // 커스텀 링크 입력 모달 열기 (iframeDoc 대신 editable 사용)
                                    showImageLinkModal($img, $editor, $editable[0]);
                                } else {
                                    console.error('No image found to link');
                                    alert('이미지를 찾을 수 없습니다.');
                                }
                            } catch (err) {
                                console.error('Error accessing editable area:', err);
                                alert('에디터에 접근할 수 없습니다.');
                            }
                        } else {
                            console.error('Editable area not found');
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
                            
                            console.log('Link button clicked in setInterval');
                            
                            var $editor = $('#content');
                            
                            // note-editable 영역 찾기
                            var $editable = $editor.next('.note-editor').find('.note-editable');
                            
                            if ($editable.length === 0) {
                                $editable = $('.note-editor .note-editable');
                            }
                            
                            if ($editable.length === 0) {
                                $editable = $('#content').siblings('.note-editor').find('.note-editable');
                            }
                            
                            if ($editable.length > 0) {
                                try {
                                    var $img = $editable.find('img[data-selected="true"]');
                                    
                                    if ($img.length === 0) {
                                        $img = $editable.find('img.note-float');
                                    }
                                    
                                    if ($img.length === 0) {
                                        $img = $editable.find('img').last();
                                    }
                                    
                                    if ($img.length > 0) {
                                        // 이미지 선택 상태로 설정
                                        $editable.find('img').removeAttr('data-selected');
                                        $img.attr('data-selected', 'true');
                                        
                                        console.log('Calling showImageLinkModal from setInterval');
                                        // 커스텀 링크 입력 모달 열기
                                        showImageLinkModal($img, $editor, $editable[0]);
                                    } else {
                                        console.error('No image found to link in setInterval');
                                    }
                                } catch (err) {
                                    console.error('Error accessing editable area in setInterval:', err);
                                }
                            } else {
                                console.error('Editable area not found in setInterval');
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

// 저장용량 초과 체크 및 모달 표시
document.addEventListener('DOMContentLoaded', function() {
    const postForm = document.getElementById('postCreateForm');
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            // 폼 제출 전 저장용량 체크는 서버에서 처리
            // 서버에서 403 에러가 반환되면 모달 표시
        });
    }
});
</script>
@include('components.storage-exceeded-modal')
@endpush
@endsection
