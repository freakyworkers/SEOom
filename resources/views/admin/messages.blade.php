@extends('layouts.admin')

@section('title', '쪽지 관리')
@section('page-title', '쪽지 관리')
@section('page-subtitle', '모든 사용자들의 쪽지를 관리할 수 있습니다')

@section('content')
<!-- 포인트 쪽지 설정 -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>쪽지 설정</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.messages.update-settings', ['site' => $site->slug]) }}" id="messageSettingsForm">
            @csrf
            @method('PUT')
            <div class="row align-items-end">
                <div class="col-md-8">
                    @php
                        $hasPointMessageFeature = $site->hasRegistrationFeature('point_message');
                    @endphp
                    @if($hasPointMessageFeature)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_point_message" id="enable_point_message" value="1" {{ ($site->getSetting('enable_point_message', '0') == '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_point_message">
                                포인트 쪽지 보내기
                            </label>
                            <small class="form-text text-muted d-block">활성화 시 쪽지 작성 시 포인트를 함께 전송할 수 있습니다.</small>
                        </div>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>저장
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>쪽지 목록</h5>
        <span class="badge bg-primary">총 {{ $messages->total() }}개</span>
    </div>
    <div class="card-body">
        <!-- 필터 검색 폼 -->
        <form method="GET" action="{{ route('admin.messages.index', ['site' => $site->slug]) }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="user_name" class="form-label small">사용자 이름</label>
                    <input type="text" 
                           name="user_name" 
                           id="user_name" 
                           class="form-control form-control-sm" 
                           placeholder="보낸이/받는이 이름"
                           value="{{ request('user_name') }}">
                </div>
                <div class="col-md-3">
                    <label for="content" class="form-label small">쪽지 내용</label>
                    <input type="text" 
                           name="content" 
                           id="content" 
                           class="form-control form-control-sm" 
                           placeholder="내용 검색"
                           value="{{ request('content') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label small">시작일</label>
                    <input type="date" 
                           name="date_from" 
                           id="date_from" 
                           class="form-control form-control-sm" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label small">종료일</label>
                    <input type="date" 
                           name="date_to" 
                           id="date_to" 
                           class="form-control form-control-sm" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search me-1"></i>검색
                    </button>
                </div>
            </div>
        </form>

        <!-- 쪽지 테이블 -->
        @if($messages->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px; text-align: center;">보낸 시각</th>
                            <th style="width: 150px; text-align: center;">읽은 시각</th>
                            <th style="width: 100px; text-align: center;">보낸이</th>
                            <th style="width: 100px; text-align: center;">받는이</th>
                            <th style="text-align: center;">내용</th>
                            <th style="width: 100px; text-align: center;">포인트</th>
                            <th style="width: 150px; text-align: center;">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $message)
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">
                                    {{ $message->created_at->format('Y.m.d H:i:s') }}
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    @if($message->is_read && $message->updated_at != $message->created_at)
                                        {{ $message->updated_at->format('Y.m.d H:i:s') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    {{ $message->sender->name ?? '삭제된 사용자' }}
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    {{ $message->receiver->name ?? '삭제된 사용자' }}
                                </td>
                                <td style="vertical-align: middle;">
                                    <textarea class="form-control form-control-sm message-content-{{ $message->id }}" 
                                              rows="3" 
                                              readonly 
                                              style="resize: vertical; min-height: 60px;">{{ $message->content }}</textarea>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    @if($message->points > 0)
                                        <span class="badge bg-success">{{ number_format($message->points) }}</span>
                                        @if($message->points_received)
                                            <small class="d-block text-muted">수령완료</small>
                                        @else
                                            <small class="d-block text-warning">미수령</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <button type="button" 
                                            class="btn btn-sm btn-warning edit-message-btn" 
                                            data-message-id="{{ $message->id }}"
                                            data-content="{{ $message->content }}">
                                        <i class="bi bi-pencil"></i> 수정
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-message-btn" 
                                            data-message-id="{{ $message->id }}">
                                        <i class="bi bi-trash"></i> 삭제
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- 페이지네이션 -->
            @if($messages->hasPages())
                <div class="mt-4">
                    {{ $messages->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                <p class="mt-3 text-muted">쪽지가 없습니다.</p>
            </div>
        @endif
    </div>
</div>

<!-- 수정 모달 -->
<div class="modal fade" id="editMessageModal" tabindex="-1" aria-labelledby="editMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMessageModalLabel">쪽지 수정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMessageForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editMessageContent" class="form-label">내용</label>
                        <textarea class="form-control" id="editMessageContent" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentMessageId = null;
    const editModal = new bootstrap.Modal(document.getElementById('editMessageModal'));

    // 설정 저장 폼 제출
    document.getElementById('messageSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 체크박스 값 처리
        const formData = new FormData(this);
        if (!document.getElementById('enable_point_message').checked) {
            formData.set('enable_point_message', '0');
        }

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('설정이 저장되었습니다. 쪽지 전송 페이지를 새로고침하면 변경사항이 적용됩니다.');
                // 페이지 새로고침
                location.reload();
            } else {
                alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    });

    // 수정 버튼 클릭
    document.querySelectorAll('.edit-message-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentMessageId = this.getAttribute('data-message-id');
            const content = this.getAttribute('data-content');
            document.getElementById('editMessageContent').value = content;
            editModal.show();
        });
    });

    // 수정 폼 제출
    document.getElementById('editMessageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const content = document.getElementById('editMessageContent').value;

        fetch(`/site/{{ $site->slug }}/admin/messages/${currentMessageId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                content: content
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 테이블의 내용 업데이트
                const textarea = document.querySelector(`.message-content-${currentMessageId}`);
                if (textarea) {
                    textarea.value = content;
                    const editBtn = document.querySelector(`.edit-message-btn[data-message-id="${currentMessageId}"]`);
                    if (editBtn) {
                        editBtn.setAttribute('data-content', content);
                    }
                }
                editModal.hide();
                alert('쪽지가 수정되었습니다.');
            } else {
                alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    });

    // 삭제 버튼 클릭
    document.querySelectorAll('.delete-message-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('정말로 이 쪽지를 삭제하시겠습니까?')) {
                return;
            }

            const messageId = this.getAttribute('data-message-id');

            fetch(`/site/{{ $site->slug }}/admin/messages/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('오류가 발생했습니다.');
            });
        });
    });
});
</script>
@endpush
@endsection

