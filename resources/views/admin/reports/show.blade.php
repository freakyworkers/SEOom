@extends('layouts.admin')

@section('title', '신고 상세')
@section('page-title', '신고 상세')
@section('page-subtitle', '신고 내용 확인 및 패널티 부여')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-flag me-2"></i>신고 정보</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>신고자:</strong> {{ $report->reporter_nickname }}
                @if($report->reporter_id)
                    <span class="badge bg-primary ms-1">회원</span>
                @else
                    <span class="badge bg-secondary ms-1">게스트</span>
                @endif
            </div>
            <div class="col-md-6">
                <strong>신고 대상:</strong> 
                <a href="#" onclick="showPenaltyModal({{ $report->reported_user_id ?: 'null' }}, '{{ $report->reported_guest_session_id }}', '{{ $report->reported_nickname }}'); return false;">
                    {{ $report->reported_nickname }}
                </a>
                @if($report->reported_user_id)
                    <span class="badge bg-primary ms-1">회원</span>
                @else
                    <span class="badge bg-secondary ms-1">게스트</span>
                @endif
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>신고 유형:</strong> 
                @if($report->report_type === 'post')
                    <span class="badge bg-info">게시글</span>
                @elseif($report->report_type === 'comment')
                    <span class="badge bg-success">댓글</span>
                @else
                    <span class="badge bg-warning text-dark">채팅</span>
                @endif
            </div>
            <div class="col-md-6">
                <strong>상태:</strong> 
                @if($report->status === 'pending')
                    <span class="badge bg-warning text-dark">대기</span>
                @elseif($report->status === 'reviewed')
                    <span class="badge bg-info">검토중</span>
                @elseif($report->status === 'resolved')
                    <span class="badge bg-success">처리완료</span>
                @else
                    <span class="badge bg-secondary">기각</span>
                @endif
            </div>
        </div>
        @if($report->reason)
        <div class="mb-3">
            <strong>신고 사유:</strong>
            <p class="mt-2">{{ $report->reason }}</p>
        </div>
        @endif
        <div class="mb-3">
            <strong>신고 내용:</strong>
            <div class="card mt-2">
                <div class="card-body">
                    @if($report->report_type === 'post' && $report->post)
                        <h6>{{ $report->post->title }}</h6>
                        <div>{!! $report->post->content !!}</div>
                    @elseif($report->report_type === 'comment' && $report->comment)
                        <p>{{ $report->comment->content }}</p>
                    @elseif($report->report_type === 'chat' && $report->chatMessage)
                        <p>{{ $report->chatMessage->message }}</p>
                        @if($report->chatMessage->attachment_path)
                            <img src="/storage/{{ $report->chatMessage->attachment_path }}" alt="Attachment" style="max-width: 300px;">
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <strong>작성일:</strong> {{ $report->created_at->format('Y-m-d H:i:s') }}
            </div>
            @if($report->reviewed_at)
            <div class="col-md-6">
                <strong>검토일:</strong> {{ $report->reviewed_at->format('Y-m-d H:i:s') }}
                @if($report->reviewer)
                    ({{ $report->reviewer->nickname ?? $report->reviewer->name }})
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>신고 처리</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ $site->isMasterSite() ? url('/admin/reports/' . $report->id . '/status') : route('admin.reports.update-status', ['site' => $site->slug, 'report' => $report->id]) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="status" class="form-label">상태 변경</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>대기</option>
                    <option value="reviewed" {{ $report->status === 'reviewed' ? 'selected' : '' }}>검토중</option>
                    <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>처리완료</option>
                    <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>기각</option>
                </select>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 패널티 부여 모달 -->
<div class="modal fade" id="penaltyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">패널티 부여</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ $site->isMasterSite() ? url('reports/issue-penalty') : route('admin.reports.issue-penalty', ['site' => $site->slug]) }}" id="penaltyForm">
                @csrf
                <input type="hidden" name="report_id" value="{{ $report->id }}">
                <input type="hidden" name="user_id" id="penalty_user_id">
                <input type="hidden" name="guest_session_id" id="penalty_guest_session_id">
                <input type="hidden" name="nickname" id="penalty_nickname">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="penalty_type" class="form-label">패널티 유형</label>
                        <select class="form-select" id="penalty_type" name="type" required>
                            <option value="chat_ban">채팅금지</option>
                            <option value="post_ban">게시글작성차단</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="penalty_reason" class="form-label">사유</label>
                        <textarea class="form-control" id="penalty_reason" name="reason" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="penalty_expires_at" class="form-label">만료일 (선택사항, 비워두면 영구)</label>
                        <input type="datetime-local" class="form-control" id="penalty_expires_at" name="expires_at">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-danger">패널티 부여</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showPenaltyModal(userId, guestSessionId, nickname) {
    document.getElementById('penalty_user_id').value = userId || '';
    document.getElementById('penalty_guest_session_id').value = guestSessionId || '';
    document.getElementById('penalty_nickname').value = nickname;
    const modal = new bootstrap.Modal(document.getElementById('penaltyModal'));
    modal.show();
}
</script>
@endpush
@endsection


