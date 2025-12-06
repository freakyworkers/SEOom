@extends('layouts.app')

@section('title', '알림')

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
                        <i class="bi bi-bell me-2"></i>알림 
                        @if($notifications->where('is_read', false)->count() > 0)
                            <span style="color: #dc3545;">{{ $notifications->where('is_read', false)->count() }}</span>
                        @endif
                    </h4>
                    @if($notifications->where('is_read', false)->count() > 0)
                        <form action="{{ route('notifications.mark-all-read', ['site' => $site->slug]) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                전체읽기
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                @if($notifications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            <a href="{{ route('notifications.read', ['site' => $site->slug, 'notification' => $notification->id]) }}" 
                               class="list-group-item list-group-item-action notification-item {{ !$notification->is_read ? 'bg-light' : '' }}" 
                               data-notification-type="{{ $notification->type }}"
                               style="border-left: none; border-right: none; {{ !$notification->is_read ? 'border-top: 2px solid ' . $pointColor . ';' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="notification-content" style="line-height: 1.6;">
                                            <div class="mb-1">
                                                <small class="text-muted">{{ $notification->created_at->format('m월 d일') }}</small>
                                                <span class="text-muted mx-1">|</span>
                                            </div>
                                            @if($notification->type === 'comment')
                                                @php
                                                    $data = $notification->data ?? [];
                                                    $commentUsers = $data['comment_users'] ?? [];
                                                    $commentCount = $data['comment_count'] ?? 1;
                                                    $lines = explode("\n", $notification->content);
                                                @endphp
                                                <div class="mb-1">
                                                    @if(count($commentUsers) > 1)
                                                        {{ $commentUsers[0] }}님외 {{ count($commentUsers) - 1 }}명이 글에 댓글을 남기셨습니다.
                                                    @else
                                                        {{ $lines[0] ?? '' }}
                                                    @endif
                                                </div>
                                                @if(isset($lines[1]))
                                                    <div class="text-muted" style="font-size: 0.9rem;">{{ $lines[1] }}</div>
                                                @endif
                                            @elseif($notification->type === 'reply')
                                                @php
                                                    $data = $notification->data ?? [];
                                                    $replyUsers = $data['reply_users'] ?? [];
                                                    $replyCount = $data['reply_count'] ?? 1;
                                                    $lines = explode("\n", $notification->content);
                                                @endphp
                                                <div class="mb-1">
                                                    @if(count($replyUsers) > 1)
                                                        {{ $replyUsers[0] }}님외 {{ count($replyUsers) - 1 }}명이 글에 답글을 남기셨습니다.
                                                    @else
                                                        {{ $lines[0] ?? '' }}
                                                    @endif
                                                </div>
                                                @if(isset($lines[1]))
                                                    <div class="text-muted" style="font-size: 0.9rem;">{{ $lines[1] }}</div>
                                                @endif
                                            @elseif($notification->type === 'message')
                                                <div class="mb-1">{{ $notification->content }}</div>
                                                <div class="text-primary" style="font-size: 0.875rem;">
                                                    <i class="bi bi-arrow-right-circle me-1"></i>확인하기
                                                </div>
                                            @elseif($notification->type === 'point_award')
                                                <div>{{ $notification->content }}</div>
                                            @elseif($notification->type === 'point_exchange')
                                                <div>{{ $notification->content }}</div>
                                            @elseif($notification->type === 'event_application')
                                                <div>{{ $notification->content }}</div>
                                            @else
                                                <div>{{ $notification->content }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="mt-3 text-muted">알림이 없습니다.</p>
                    </div>
                @endif
            </div>
        </div>

        @if($notifications->hasPages())
            <div class="mt-4 mb-4">
                {{ $notifications->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
            </div>
        @endif
    </div>
</div>

<style>
.notification-content {
    word-break: break-word;
}
.list-group-item:hover {
    background-color: #f8f9fa !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 알림 클릭 이벤트 처리
    document.querySelectorAll('.notification-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            const notificationType = this.getAttribute('data-notification-type');
            
            // 포인트 지급 알림인 경우 모달 열기
            if (notificationType === 'point_award') {
                e.preventDefault();
                
                // 알림 읽음 처리 (AJAX)
                const href = this.getAttribute('href');
                fetch(href, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html', // HTML 응답을 받아야 함
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.ok) {
                        // 읽음 처리 후 모달 열기
                        this.classList.remove('bg-light');
                        this.style.borderTop = '';
                        
                        // 모달 열기 (약간의 지연을 두어 읽음 처리가 완료된 후 모달 열기)
                        setTimeout(function() {
                            if (typeof window.openPointHistoryModal === 'function') {
                                window.openPointHistoryModal();
                            } else {
                                // 모달이 아직 로드되지 않은 경우 잠시 후 다시 시도
                                setTimeout(function() {
                                    if (typeof window.openPointHistoryModal === 'function') {
                                        window.openPointHistoryModal();
                                    }
                                }, 200);
                            }
                        }, 100);
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                    // 에러가 발생해도 모달은 열기
                    if (typeof window.openPointHistoryModal === 'function') {
                        window.openPointHistoryModal();
                    }
                });
            }
        });
    });
});
</script>
@endsection

