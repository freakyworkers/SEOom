@extends('layouts.app')

@section('title', '쪽지함')

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
                        <i class="bi bi-envelope me-2"></i>쪽지함
                    </h4>
                </div>
            </div>
            <!-- 탭 네비게이션 -->
            <ul class="nav nav-tabs border-bottom" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'received' ? 'active' : '' }}" 
                       href="{{ route('messages.index', ['site' => $site->slug, 'type' => 'received']) }}"
                       style="color: {{ $type === 'received' ? $pointColor : '#6c757d' }}; border-bottom-color: {{ $type === 'received' ? $pointColor : 'transparent' }};">
                        받은 쪽지
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'sent' ? 'active' : '' }}" 
                       href="{{ route('messages.index', ['site' => $site->slug, 'type' => 'sent']) }}"
                       style="color: {{ $type === 'sent' ? $pointColor : '#6c757d' }}; border-bottom-color: {{ $type === 'sent' ? $pointColor : 'transparent' }};">
                        보낸 쪽지
                    </a>
                </li>
            </ul>
            <div class="card-body p-0">
                @if($messages->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($messages as $message)
                            <a href="#" 
                               class="list-group-item list-group-item-action message-item {{ $type === 'received' && !$message->is_read ? 'bg-light' : '' }}" 
                               data-message-id="{{ $message->id }}"
                               style="border-left: none; border-right: none; {{ $type === 'received' && !$message->is_read ? 'border-top: 2px solid ' . $pointColor . ';' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="message-content" style="line-height: 1.6;">
                                            <div class="mb-1">
                                                <strong>{{ $type === 'received' ? $message->sender->name : $message->receiver->name }}</strong>
                                                <small class="text-muted ms-2">{{ $message->created_at->format('Y-m-d H:i') }}</small>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.9rem; word-break: break-word;">
                                                {{ Str::limit(strip_tags($message->content), 100) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="mt-3 text-muted">쪽지가 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>

        @if($messages->hasPages())
            <div class="mt-4 mb-4">
                {{ $messages->appends(['type' => $type])->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
            </div>
        @endif
    </div>
</div>

<!-- 쪽지 읽기 모달 -->
<div class="modal fade" id="messageReadModal" tabindex="-1" aria-labelledby="messageReadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageReadModalLabel">쪽지</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="messageReadContent">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">로딩 중...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-warning" id="receivePointsBtn" style="display: none;">
                    <i class="bi bi-coin me-1"></i>포인트 수령하기
                </button>
                <button type="button" class="btn btn-primary" id="replyMessageBtn" style="display: none;">답장하기</button>
            </div>
        </div>
    </div>
</div>

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

<style>
.message-content {
    word-break: break-word;
}
.list-group-item:hover {
    background-color: #f8f9fa !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageReadModal = new bootstrap.Modal(document.getElementById('messageReadModal'));
    const sendMessageModal = new bootstrap.Modal(document.getElementById('sendMessageModal'));
    let currentMessageId = null;
    let replyToUserId = null;
    let replyToUserName = null;

    // 쪽지 클릭 시 읽기 모달 열기
    document.querySelectorAll('.message-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            currentMessageId = this.getAttribute('data-message-id');
            loadMessage(currentMessageId);
        });
    });

    // 쪽지 로드
    function loadMessage(messageId) {
        const content = document.getElementById('messageReadContent');
        content.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">로딩 중...</span></div></div>';
        
        const baseUrl = '{{ route("messages.show", ["site" => $site->slug, "message" => 0]) }}';
        const messageUrl = baseUrl.replace('/0', '/' + messageId);
        
        fetch(messageUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.message) {
                const message = data.message;
                const currentType = '{{ $type }}';
                const isReceived = currentType === 'received';
                const isSender = message.sender_id === {{ auth()->id() }};
                
                let html = `
                    <div class="mb-3">
                        <label class="form-label fw-bold">${isReceived ? '보낸이' : '받는이'}</label>
                        <div>${isReceived ? message.sender.name : message.receiver.name}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">내용</label>
                        <div style="white-space: pre-wrap; word-break: break-word; padding: 0.75rem; background-color: #f8f9fa; border-radius: 0.25rem;">${message.content}</div>
                    </div>
                `;
                
                // 포인트가 있는 경우 표시
                if (message.points > 0) {
                    html += `
                        <div class="mb-3">
                            <label class="form-label fw-bold">포인트</label>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-coin me-2"></i>${message.points.toLocaleString()}P
                                ${message.points_received ? '<span class="badge bg-success ms-2">수령완료</span>' : '<span class="badge bg-warning ms-2">미수령</span>'}
                            </div>
                        </div>
                    `;
                }
                
                content.innerHTML = html;
                
                // 답장 버튼 표시 (받은 쪽지이고 수신자인 경우만)
                if (isReceived && message.receiver_id === {{ auth()->id() }}) {
                    document.getElementById('replyMessageBtn').style.display = 'block';
                    replyToUserId = message.sender_id;
                    replyToUserName = message.sender.name;
                } else {
                    document.getElementById('replyMessageBtn').style.display = 'none';
                }
                
                // 포인트 수령하기 버튼 표시 (받은 쪽지이고 포인트가 있고 아직 수령하지 않은 경우만)
                const receivePointsBtn = document.getElementById('receivePointsBtn');
                if (isReceived && message.receiver_id === {{ auth()->id() }} && message.points > 0 && !message.points_received) {
                    receivePointsBtn.style.display = 'block';
                    receivePointsBtn.setAttribute('data-message-id', messageId);
                } else {
                    receivePointsBtn.style.display = 'none';
                }
                
                // 읽음 처리된 경우 배경색 제거 (받은 쪽지인 경우만)
                if (isReceived) {
                    const messageItem = document.querySelector(`[data-message-id="${messageId}"]`);
                    if (messageItem) {
                        messageItem.classList.remove('bg-light');
                        messageItem.style.borderTop = '';
                    }
                }
                
                messageReadModal.show();
            } else {
                content.innerHTML = '<div class="alert alert-danger">쪽지를 불러오는 중 오류가 발생했습니다.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading message:', error);
            content.innerHTML = '<div class="alert alert-danger">쪽지를 불러오는 중 오류가 발생했습니다.</div>';
        });
    }

    // 포인트 수령하기 버튼 클릭
    document.getElementById('receivePointsBtn').addEventListener('click', function() {
        const messageId = this.getAttribute('data-message-id');
        if (!messageId) return;

        if (!confirm('포인트를 수령하시겠습니까?')) {
            return;
        }

        fetch(`/site/{{ $site->slug }}/messages/${messageId}/receive-points`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`포인트 ${data.points.toLocaleString()}P를 수령했습니다.`);
                // 쪽지 다시 로드
                loadMessage(messageId);
            } else {
                alert(data.message || '포인트 수령 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error receiving points:', error);
            alert('포인트 수령 중 오류가 발생했습니다.');
        });
    });

    // 답장하기 버튼 클릭
    document.getElementById('replyMessageBtn').addEventListener('click', function() {
        if (replyToUserId) {
            messageReadModal.hide();
            openSendMessageModal(replyToUserId, replyToUserName);
        }
    });

    // 쪽지 보내기 모달 열기
    window.openSendMessageModal = function(userId, userName) {
        document.getElementById('messageReceiverId').value = userId;
        document.getElementById('messageReceiver').value = userName || '사용자';
        document.getElementById('messageContent').value = '';
        sendMessageModal.show();
    };

    // 쪽지 전송
    document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
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
        
        fetch('{{ route('messages.store', ['site' => $site->slug]) }}', {
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
                sendMessageModal.hide();
                @if($site->getSetting('enable_point_message', '0') == '1')
                if (document.getElementById('messagePoints')) {
                    document.getElementById('messagePoints').value = '0';
                    // 포인트 업데이트
                    if (data.user_points !== undefined) {
                        document.getElementById('userPoints').value = data.user_points.toLocaleString();
                    }
                }
                @endif
                location.reload();
            } else {
                alert(data.message || '쪽지 전송 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('쪽지 전송 중 오류가 발생했습니다.');
        });
    });
});
</script>
@endsection

