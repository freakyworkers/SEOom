<div class="comment-item mb-3 {{ $depth > 0 ? 'ms-4 border-start ps-3' : '' }}" id="comment-{{ $comment->id }}">
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            @php
                // $board 변수가 전달되었으면 사용하고, 없으면 관계에서 가져오기
                $board = $board ?? ($comment->post->board ?? $comment->post->board()->first());
                
                // $site를 Site 객체로 변환
                if (is_string($site) && $site) {
                    $site = \App\Models\Site::where('slug', $site)->first();
                } elseif (!$site && $board) {
                    $site = $board->site ?? null;
                } elseif (!$site && $comment->user) {
                    $site = $comment->user->site ?? null;
                }
            @endphp
            @auth
                @php
                    $canEdit = (auth()->id() === $comment->user_id || auth()->user()->canManage());
                    $deletePermission = $board->comment_delete_permission ?? 'author';
                    $canDelete = false;
                    if ($deletePermission === 'admin') {
                        $canDelete = auth()->user()->canManage();
                    } else {
                        $canDelete = (auth()->id() === $comment->user_id || auth()->user()->canManage());
                    }
                    
                    // 채택 버튼 표시 조건
                    $post = $comment->post ?? $comment->post()->first();
                    $canAdoptAsAuthor = $board 
                        && $board->enable_author_comment_adopt 
                        && auth()->check() 
                        && $post 
                        && auth()->id() == $post->user_id 
                        && !$comment->is_adopted 
                        && !$post->adopted_comment_id;
                        
                    $canAdoptAsAdmin = $board && $board->enable_admin_comment_adopt 
                        && auth()->check() 
                        && auth()->user()->canManage() 
                        && !$comment->is_adopted;
                @endphp
            @endauth
            
            {{-- 모바일 레이아웃: 세로 배치 (닉네임 -> 댓글 -> 날짜+모든 아이콘) --}}
            <div class="d-flex flex-column d-md-none mb-2">
                <div class="mb-1">
                    @auth
                        @if(auth()->id() !== $comment->user_id)
                            <div class="dropdown d-inline">
                                <a class="text-decoration-none dropdown-toggle fw-bold" href="#" role="button" id="commentUserDropdownMobile{{ $comment->id }}" data-bs-toggle="dropdown" aria-expanded="false" style="color: inherit;">
                                    <x-user-rank :user="$comment->user" :site="$site" />
                                    {{ $comment->user->nickname ?? $comment->user->name }}
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="commentUserDropdownMobile{{ $comment->id }}">
                                    <li><a class="dropdown-item" href="#" onclick="openReportModal('comment', {{ $comment->id }}, '{{ $site->slug }}', '{{ $boardSlug }}', {{ $comment->post_id }}); return false;">신고하기</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="openSendMessageModal({{ $comment->user_id }}, '{{ $comment->user->nickname ?? $comment->user->name }}'); return false;">쪽지보내기</a></li>
                                </ul>
                            </div>
                        @else
                            <strong><x-user-rank :user="$comment->user" :site="$site" />{{ $comment->user->nickname ?? $comment->user->name }}</strong>
                        @endif
                    @else
                        <strong><x-user-rank :user="$comment->user" :site="$site" />{{ $comment->user->nickname ?? $comment->user->name }}</strong>
                    @endauth
                </div>
                <div class="comment-content mb-1" id="comment-content-mobile-{{ $comment->id }}" style="word-break: break-word; overflow-wrap: break-word;">
                    {!! nl2br(e($comment->content)) !!}
                </div>
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">
                        @if(isset($showDatetime) && $showDatetime)
                            {{ $comment->created_at->format('Y-m-d H:i') }}
                        @else
                            {{ $comment->created_at->format('Y-m-d') }}
                        @endif
                    </small>
                    @auth
                        @if($canAdoptAsAuthor)
                            <button type="button" 
                                    class="btn-link p-0 border-0 bg-transparent text-success comment-icon-btn" 
                                    onclick="adoptComment({{ $comment->id }}, {{ $comment->post_id }}, 'author')"
                                    title="채택"
                                    style="font-size: inherit; line-height: inherit;">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        @endif
                        @if($canAdoptAsAdmin)
                            <button type="button" 
                                    class="btn-link p-0 border-0 bg-transparent text-primary comment-icon-btn" 
                                    onclick="adoptComment({{ $comment->id }}, {{ $comment->post_id }}, 'admin')"
                                    title="운영자 채택"
                                    style="font-size: inherit; line-height: inherit;">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        @endif
                        @if($depth < 2)
                            <button type="button" 
                                    class="btn-link p-0 border-0 bg-transparent text-muted comment-icon-btn" 
                                    onclick="showReplyForm({{ $comment->id }})"
                                    title="답글"
                                    style="font-size: inherit; line-height: inherit;">
                                <i class="bi bi-reply"></i>
                            </button>
                        @endif
                        @if($canEdit)
                            <button type="button" 
                                    class="btn-link p-0 border-0 bg-transparent text-muted comment-icon-btn" 
                                    onclick="showEditComment({{ $comment->id }})"
                                    title="수정"
                                    style="font-size: inherit; line-height: inherit;">
                                <i class="bi bi-pencil"></i>
                            </button>
                        @endif
                        @if($canDelete)
                            <form action="{{ route('comments.destroy', ['site' => $site ?? 1, 'boardSlug' => $boardSlug ?? '', 'post' => $comment->post_id, 'comment' => $comment->id]) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-link p-0 border-0 bg-transparent text-danger comment-icon-btn" title="삭제" style="font-size: inherit; line-height: inherit;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
            
            {{-- 데스크톱 레이아웃: 기존 가로 배치 --}}
            <div class="d-none d-md-block">
                <div class="d-flex align-items-center mb-1">
                    @auth
                        @if(auth()->id() !== $comment->user_id)
                            <div class="dropdown d-inline me-2">
                                <a class="text-decoration-none dropdown-toggle fw-bold" href="#" role="button" id="commentUserDropdown{{ $comment->id }}" data-bs-toggle="dropdown" aria-expanded="false" style="color: inherit;">
                                    <x-user-rank :user="$comment->user" :site="$site" />
                                    {{ $comment->user->nickname ?? $comment->user->name }}
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="commentUserDropdown{{ $comment->id }}">
                                    <li><a class="dropdown-item" href="#" onclick="openReportModal('comment', {{ $comment->id }}, '{{ $site->slug }}', '{{ $boardSlug }}', {{ $comment->post_id }}); return false;">신고하기</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="openSendMessageModal({{ $comment->user_id }}, '{{ $comment->user->nickname ?? $comment->user->name }}'); return false;">쪽지보내기</a></li>
                                </ul>
                            </div>
                        @else
                            <strong class="me-2"><x-user-rank :user="$comment->user" :site="$site" />{{ $comment->user->nickname ?? $comment->user->name }}</strong>
                        @endif
                    @else
                        <strong class="me-2"><x-user-rank :user="$comment->user" :site="$site" />{{ $comment->user->nickname ?? $comment->user->name }}</strong>
                    @endauth
                    @if($comment->is_adopted)
                        <span class="badge bg-success me-2">채택됨</span>
                    @endif
                    <small class="text-muted">
                        @if(isset($showDatetime) && $showDatetime)
                            {{ $comment->created_at->format('Y-m-d H:i') }}
                        @else
                            {{ $comment->created_at->format('Y-m-d') }}
                        @endif
                    </small>
                </div>
                <div class="comment-content" id="comment-content-{{ $comment->id }}">
                    {!! nl2br(e($comment->content)) !!}
                </div>
            </div>
            <!-- 인라인 편집 폼 (기본적으로 숨김) -->
            <div id="comment-edit-form-{{ $comment->id }}" class="mt-2" style="display: none;">
                <form method="POST" 
                      class="comment-edit-form" 
                      data-comment-id="{{ $comment->id }}"
                      data-site="{{ $site ?? 1 }}"
                      data-board-slug="{{ $boardSlug ?? '' }}"
                      data-post-id="{{ $comment->post_id }}">
                    @csrf
                    @method('PUT')
                    <textarea class="form-control form-control-sm" 
                              name="content" 
                              rows="3" 
                              required>{{ $comment->content }}</textarea>
                    <div class="mt-2 d-flex gap-2 justify-content-end">
                        <button type="button" 
                                class="btn btn-sm btn-secondary" 
                                onclick="cancelEditComment({{ $comment->id }})">
                            취소
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @auth
            {{-- 데스크톱 버튼 영역 --}}
            @if($canEdit || $canDelete || $depth < 2 || $canAdoptAsAuthor || $canAdoptAsAdmin)
                <div class="d-none d-md-flex gap-2 ms-2">
                    @if($canAdoptAsAuthor)
                        <button type="button" 
                                class="btn btn-outline-success btn-sm comment-action-btn" 
                                onclick="adoptComment({{ $comment->id }}, {{ $comment->post_id }}, 'author')"
                                title="채택">
                            <i class="bi bi-check-circle"></i>
                        </button>
                    @endif
                    @if($canAdoptAsAdmin)
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm comment-action-btn" 
                                onclick="adoptComment({{ $comment->id }}, {{ $comment->post_id }}, 'admin')"
                                title="운영자 채택">
                            <i class="bi bi-check-circle"></i>
                        </button>
                    @endif
                    @if($depth < 2)
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm comment-action-btn" 
                                onclick="showReplyForm({{ $comment->id }})"
                                title="답글">
                            <i class="bi bi-reply"></i>
                        </button>
                    @endif
                    @if($canEdit)
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm comment-action-btn" 
                                onclick="showEditComment({{ $comment->id }})"
                                title="수정">
                            <i class="bi bi-pencil"></i>
                        </button>
                    @endif
                    @if($canDelete)
                        <form action="{{ route('comments.destroy', ['site' => $site ?? 1, 'boardSlug' => $boardSlug ?? '', 'post' => $comment->post_id, 'comment' => $comment->id]) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('정말 삭제하시겠습니까?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm comment-action-btn" title="삭제">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        @endauth
    </div>

    <!-- Reply Form (hidden by default) -->
    @auth
        <div id="reply-form-{{ $comment->id }}" class="mt-2" style="display: none;">
            <form method="POST" action="{{ route('comments.store', ['site' => $site ?? 1, 'boardSlug' => $boardSlug ?? '', 'post' => $comment->post_id]) }}">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <div class="mb-2">
                    <textarea class="form-control form-control-sm" 
                              name="content" 
                              rows="2" 
                              placeholder="댓글을 입력하세요.." 
                              required></textarea>
                </div>
                <div class="text-end">
                    <button type="button" 
                            class="btn btn-sm btn-secondary" 
                            onclick="hideReplyForm({{ $comment->id }})">
                        취소
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        작성
                    </button>
                </div>
            </form>
        </div>
    @endauth

    <!-- Replies -->
    @if($comment->replies->count() > 0)
        <div class="replies mt-2">
            @foreach($comment->replies as $reply)
                @include('comments.item', ['comment' => $reply, 'depth' => $depth + 1, 'board' => $board ?? null, 'site' => $site ?? null, 'boardSlug' => $boardSlug ?? null, 'showDatetime' => $showDatetime ?? true])
            @endforeach
        </div>
    @endif
</div>

@push('styles')
<style>
.comment-action-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
}

/* 모바일 댓글 내용 자동 줄바꿈 */
@media (max-width: 767.98px) {
    .comment-content {
        word-break: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }
    
    .comment-icon-btn {
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
    }
    
    .comment-icon-btn:hover {
        opacity: 0.7;
    }
    
    .comment-icon-btn i {
        font-size: 1em;
    }
}
</style>
@endpush

@push('scripts')
<script>
function adoptComment(commentId, postId, type) {
    if (type === 'admin') {
        // 운영자 채택 모달
        const points = prompt('채택 포인트를 입력하세요:', '0');
        if (points === null) return;
        const adoptionPoints = parseInt(points);
        if (isNaN(adoptionPoints) || adoptionPoints < 0) {
            alert('올바른 포인트를 입력하세요.');
            return;
        }
        submitAdoption(commentId, postId, type, adoptionPoints);
    } else {
        // 작성자 채택 (게시글의 채택 포인트 사용)
        if (confirm('이 댓글을 채택하시겠습니까?')) {
            submitAdoption(commentId, postId, type, null);
        }
    }
}

function submitAdoption(commentId, postId, type, points) {
    const url = '/site/{{ $site ?? 1 }}/boards/{{ $boardSlug ?? "" }}/posts/' + postId + '/comments/' + commentId + '/adopt';
    $.ajax({
        url: url,
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            type: type,
            points: points
        },
        success: function(response) {
            alert('댓글이 채택되었습니다.');
            location.reload();
        },
        error: function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.message) {
                alert(xhr.responseJSON.message);
            } else {
                alert('채택 처리에 실패했습니다.');
            }
        }
    });
}

function showReplyForm(commentId) {
    document.getElementById('reply-form-' + commentId).style.display = 'block';
}

function hideReplyForm(commentId) {
    document.getElementById('reply-form-' + commentId).style.display = 'none';
}

function showEditComment(commentId) {
    // 댓글 내용 숨기기 (모바일/데스크톱 모두)
    const desktopContent = document.getElementById('comment-content-' + commentId);
    const mobileContent = document.getElementById('comment-content-mobile-' + commentId);
    if (desktopContent) desktopContent.style.display = 'none';
    if (mobileContent) mobileContent.style.display = 'none';
    // 편집 폼 표시
    document.getElementById('comment-edit-form-' + commentId).style.display = 'block';
}

function cancelEditComment(commentId) {
    // 편집 폼 숨기기
    document.getElementById('comment-edit-form-' + commentId).style.display = 'none';
    // 댓글 내용 표시 (모바일/데스크톱 모두)
    const desktopContent = document.getElementById('comment-content-' + commentId);
    const mobileContent = document.getElementById('comment-content-mobile-' + commentId);
    if (desktopContent) desktopContent.style.display = 'block';
    if (mobileContent) mobileContent.style.display = 'block';
}

// 인라인 편집 폼 제출 처리
$(document).ready(function() {
    $(document).on('submit', '.comment-edit-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const commentId = form.data('comment-id');
        const site = form.data('site');
        const boardSlug = form.data('board-slug');
        const postId = form.data('post-id');
        const content = form.find('textarea[name="content"]').val();
        
        // URL 동적 생성
        const url = '/site/' + site + '/boards/' + boardSlug + '/posts/' + postId + '/comments/' + commentId;
        
        $.ajax({
            url: url,
            method: 'PUT',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: {
                content: content,
                _token: form.find('input[name="_token"]').val()
            },
            success: function(response) {
                // 댓글 내용 업데이트 (HTML 이스케이프 처리)
                const escapedContent = $('<div>').text(content).html().replace(/\n/g, '<br>');
                $('#comment-content-' + commentId).html(escapedContent);
                $('#comment-content-mobile-' + commentId).html(escapedContent);
                // 편집 폼 숨기기
                $('#comment-edit-form-' + commentId).hide();
                // 댓글 내용 표시 (모바일/데스크톱 모두)
                $('#comment-content-' + commentId).show();
                $('#comment-content-mobile-' + commentId).show();
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    alert('댓글 수정에 실패했습니다: ' + Object.values(xhr.responseJSON.errors).flat().join(', '));
                } else {
                    alert('댓글 수정에 실패했습니다.');
                }
                console.error(xhr);
            }
        });
    });
});

// 신고 모달 열기
function openReportModal(type, id, siteSlug, boardSlug, postId) {
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
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.error || data.message || '신고 접수에 실패했습니다.');
                }
                return data;
            });
        })
        .then(data => {
            modal.remove();
            if (data.success) {
                alert('신고가 접수되었습니다.');
            } else {
                alert(data.error || data.message || '신고 접수에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modal.remove();
            alert(error.message || '신고 접수에 실패했습니다.');
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
