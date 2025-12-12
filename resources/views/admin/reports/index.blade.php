@extends('layouts.admin')

@section('title', '신고 관리')
@section('page-title', '신고 관리')
@section('page-subtitle', '신고 접수 내역 및 패널티 관리')

@section('content')
<!-- 신고 목록 -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-flag me-2"></i>신고 목록</h5>
    </div>
    @if($reports->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>신고자</th>
                        <th>신고 대상</th>
                        <th>신고 유형</th>
                        <th>내용</th>
                        <th style="width: 100px;">상태</th>
                        <th style="width: 150px;">작성일</th>
                        <th style="width: 150px;">작업</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->id }}</td>
                            <td>
                                <strong>{{ $report->reporter_nickname }}</strong>
                                @if($report->reporter_id)
                                    <span class="badge bg-primary ms-1">회원</span>
                                @else
                                    <span class="badge bg-secondary ms-1">게스트</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $report->reported_nickname }}</strong>
                                @if($report->reported_user_id)
                                    <span class="badge bg-primary ms-1">회원</span>
                                @else
                                    <span class="badge bg-secondary ms-1">게스트</span>
                                @endif
                            </td>
                            <td>
                                @if($report->report_type === 'post')
                                    <span class="badge bg-info">게시글</span>
                                @elseif($report->report_type === 'comment')
                                    <span class="badge bg-success">댓글</span>
                                @else
                                    <span class="badge bg-warning text-dark">채팅</span>
                                @endif
                            </td>
                            <td>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    @if($report->report_type === 'post' && $report->post)
                                        {{ Str::limit($report->post->title, 30) }}
                                    @elseif($report->report_type === 'comment' && $report->comment)
                                        {{ Str::limit($report->comment->content, 30) }}
                                    @elseif($report->report_type === 'chat' && $report->chatMessage)
                                        {{ Str::limit($report->chatMessage->message, 30) }}
                                    @else
                                        {{ Str::limit($report->reason ?? '-', 30) }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($report->status === 'pending')
                                    <span class="badge bg-warning text-dark">대기</span>
                                @elseif($report->status === 'reviewed')
                                    <span class="badge bg-info">검토중</span>
                                @elseif($report->status === 'resolved')
                                    <span class="badge bg-success">처리완료</span>
                                @else
                                    <span class="badge bg-secondary">기각</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $report->created_at->format('Y-m-d H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ $site->isMasterSite() ? route('master.admin.reports.show', $report->id) : route('admin.reports.show', ['site' => $site->slug, 'report' => $report->id]) }}" 
                                       class="btn btn-outline-primary" title="상세보기">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-center mt-4">
                    @php
                        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                        $pointColor = $themeDarkMode === 'dark' 
                            ? $site->getSetting('color_dark_point_main', '#ffffff')
                            : $site->getSetting('color_light_point_main', '#0d6efd');
                    @endphp
                    {{ $reports->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                </div>
            </div>
        @endif
    @else
        <div class="card-body text-center py-5">
            <i class="bi bi-flag display-1 text-muted"></i>
            <h4 class="mt-3 mb-2">신고 내역이 없습니다</h4>
            <p class="text-muted">아직 접수된 신고가 없습니다.</p>
        </div>
    @endif
</div>

<!-- 패널티 목록 -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-shield-exclamation me-2"></i>패널티 목록</h5>
    </div>
    @if($penalties->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>대상</th>
                        <th>패널티 유형</th>
                        <th>사유</th>
                        <th>만료일</th>
                        <th style="width: 100px;">상태</th>
                        <th style="width: 150px;">작성일</th>
                        <th style="width: 100px;">작업</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penalties as $penalty)
                        <tr>
                            <td>{{ $penalty->id }}</td>
                            <td>
                                <strong>{{ $penalty->nickname }}</strong>
                                @if($penalty->user_id)
                                    <span class="badge bg-primary ms-1">회원</span>
                                @else
                                    <span class="badge bg-secondary ms-1">게스트</span>
                                @endif
                            </td>
                            <td>
                                @if($penalty->type === 'chat_ban')
                                    <span class="badge bg-danger">채팅금지</span>
                                @else
                                    <span class="badge bg-warning text-dark">게시글작성차단</span>
                                @endif
                            </td>
                            <td>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ Str::limit($penalty->reason ?? '-', 30) }}
                                </div>
                            </td>
                            <td>
                                @if($penalty->expires_at)
                                    <small>{{ $penalty->expires_at->format('Y-m-d H:i') }}</small>
                                @else
                                    <span class="badge bg-dark">영구</span>
                                @endif
                            </td>
                            <td>
                                @if($penalty->is_active)
                                    <span class="badge bg-success">활성</span>
                                @else
                                    <span class="badge bg-secondary">비활성</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $penalty->created_at->format('Y-m-d H:i') }}</small>
                            </td>
                            <td>
                                @if($penalty->is_active)
                                    <form action="{{ $site->isMasterSite() ? route('master.admin.reports.remove-penalty', $penalty->id) : route('admin.reports.remove-penalty', ['site' => $site->slug, 'penalty' => $penalty->id]) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('패널티를 해제하시겠습니까?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="해제">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($penalties->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-center mt-4">
                    @php
                        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                        $pointColor = $themeDarkMode === 'dark' 
                            ? $site->getSetting('color_dark_point_main', '#ffffff')
                            : $site->getSetting('color_light_point_main', '#0d6efd');
                    @endphp
                    {{ $penalties->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                </div>
            </div>
        @endif
    @else
        <div class="card-body text-center py-5">
            <i class="bi bi-shield-exclamation display-1 text-muted"></i>
            <h4 class="mt-3 mb-2">패널티 내역이 없습니다</h4>
            <p class="text-muted">아직 부여된 패널티가 없습니다.</p>
        </div>
    @endif
</div>
@endsection


