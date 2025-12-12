@extends('layouts.admin')

@section('title', '신고 관리')
@section('page-title', '신고 관리')
@section('page-subtitle', '신고 접수 내역 및 패널티 관리')

@section('content')
@php
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $pointColor = $themeDarkMode === 'dark' 
        ? $site->getSetting('color_dark_point_main', '#ffffff')
        : $site->getSetting('color_light_point_main', '#0d6efd');
@endphp

<!-- 신고 목록 -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-flag me-2"></i>신고 목록</h5>
    </div>
    
    <!-- 검색 및 필터 폼 -->
    <div class="card-body border-bottom">
        <form method="GET" action="{{ $site->isMasterSite() ? route('master.admin.reports.index') : route('admin.reports.index', ['site' => $site->slug]) }}" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3 col-12">
                    <label for="search_type" class="form-label small mb-1">검색 조건</label>
                    <select name="search_type" id="search_type" class="form-select form-select-sm">
                        <option value="all" {{ request('search_type', 'all') == 'all' ? 'selected' : '' }}>전체</option>
                        <option value="reporter" {{ request('search_type') == 'reporter' ? 'selected' : '' }}>신고자</option>
                        <option value="reported" {{ request('search_type') == 'reported' ? 'selected' : '' }}>신고 대상</option>
                        <option value="reason" {{ request('search_type') == 'reason' ? 'selected' : '' }}>신고 사유</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <label for="status" class="form-label small mb-1">상태</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">전체</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>대기</option>
                        <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>검토중</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>처리완료</option>
                        <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>기각</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <label for="report_type" class="form-label small mb-1">신고 유형</label>
                    <select name="report_type" id="report_type" class="form-select form-select-sm">
                        <option value="">전체</option>
                        <option value="post" {{ request('report_type') == 'post' ? 'selected' : '' }}>게시글</option>
                        <option value="comment" {{ request('report_type') == 'comment' ? 'selected' : '' }}>댓글</option>
                        <option value="chat" {{ request('report_type') == 'chat' ? 'selected' : '' }}>채팅</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <label for="search" class="form-label small mb-1">검색어</label>
                    <div class="input-group input-group-sm">
                        <input type="text" 
                               name="search" 
                               id="search" 
                               class="form-control" 
                               placeholder="검색어 입력"
                               value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary" style="background-color: {{ $pointColor }}; border-color: {{ $pointColor }};">
                            <i class="bi bi-search"></i>
                        </button>
                        @if(request('search') || request('status') || request('report_type'))
                            <a href="{{ $site->isMasterSite() ? route('master.admin.reports.index') : route('admin.reports.index', ['site' => $site->slug]) }}" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-3 col-12">
                    <label for="per_page" class="form-label small mb-1">보기 개수</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5개</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10개</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20개</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50개</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="penalty_search" value="{{ request('penalty_search') }}">
            <input type="hidden" name="penalty_search_type" value="{{ request('penalty_search_type') }}">
            <input type="hidden" name="penalty_type" value="{{ request('penalty_type') }}">
            <input type="hidden" name="penalty_per_page" value="{{ request('penalty_per_page', 5) }}">
        </form>
    </div>

    @if($reports->count() > 0)
        <!-- PC 버전 테이블 -->
        <div class="table-responsive d-none d-md-block">
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

        <!-- 모바일 버전 카드 레이아웃 -->
        <div class="d-md-none">
            @foreach($reports as $report)
                <div class="card border mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-secondary">ID: {{ $report->id }}</span>
                                <span class="badge ms-1 
                                    @if($report->status === 'pending') bg-warning text-dark
                                    @elseif($report->status === 'reviewed') bg-info
                                    @elseif($report->status === 'resolved') bg-success
                                    @else bg-secondary
                                    @endif">
                                    @if($report->status === 'pending') 대기
                                    @elseif($report->status === 'reviewed') 검토중
                                    @elseif($report->status === 'resolved') 처리완료
                                    @else 기각
                                    @endif
                                </span>
                            </div>
                            <small class="text-muted">{{ $report->created_at->format('Y-m-d H:i') }}</small>
                        </div>
                        
                        <div class="mb-2">
                            <div class="small text-muted mb-1">신고자</div>
                            <div class="fw-medium">
                                {{ $report->reporter_nickname }}
                                @if($report->reporter_id)
                                    <span class="badge bg-primary ms-1" style="font-size: 0.7rem;">회원</span>
                                @else
                                    <span class="badge bg-secondary ms-1" style="font-size: 0.7rem;">게스트</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="small text-muted mb-1">신고 대상</div>
                            <div class="fw-medium">
                                {{ $report->reported_nickname }}
                                @if($report->reported_user_id)
                                    <span class="badge bg-primary ms-1" style="font-size: 0.7rem;">회원</span>
                                @else
                                    <span class="badge bg-secondary ms-1" style="font-size: 0.7rem;">게스트</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="small text-muted mb-1">신고 유형</div>
                            <div>
                                @if($report->report_type === 'post')
                                    <span class="badge bg-info">게시글</span>
                                @elseif($report->report_type === 'comment')
                                    <span class="badge bg-success">댓글</span>
                                @else
                                    <span class="badge bg-warning text-dark">채팅</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">내용</div>
                            <div style="font-size: 0.9rem; word-break: break-word;">
                                @if($report->report_type === 'post' && $report->post)
                                    {{ Str::limit($report->post->title, 50) }}
                                @elseif($report->report_type === 'comment' && $report->comment)
                                    {{ Str::limit($report->comment->content, 50) }}
                                @elseif($report->report_type === 'chat' && $report->chatMessage)
                                    {{ Str::limit($report->chatMessage->message, 50) }}
                                @else
                                    {{ Str::limit($report->reason ?? '-', 50) }}
                                @endif
                            </div>
                        </div>

                        <div class="d-grid">
                            <a href="{{ $site->isMasterSite() ? route('master.admin.reports.show', $report->id) : route('admin.reports.show', ['site' => $site->slug, 'report' => $report->id]) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>상세보기
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($reports->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-center mt-3">
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
    
    <!-- 검색 및 필터 폼 -->
    <div class="card-body border-bottom">
        <form method="GET" action="{{ $site->isMasterSite() ? route('master.admin.reports.index') : route('admin.reports.index', ['site' => $site->slug]) }}" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3 col-12">
                    <label for="penalty_search_type" class="form-label small mb-1">검색 조건</label>
                    <select name="penalty_search_type" id="penalty_search_type" class="form-select form-select-sm">
                        <option value="all" {{ request('penalty_search_type', 'all') == 'all' ? 'selected' : '' }}>전체</option>
                        <option value="nickname" {{ request('penalty_search_type') == 'nickname' ? 'selected' : '' }}>닉네임</option>
                        <option value="reason" {{ request('penalty_search_type') == 'reason' ? 'selected' : '' }}>사유</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <label for="penalty_type" class="form-label small mb-1">패널티 유형</label>
                    <select name="penalty_type" id="penalty_type" class="form-select form-select-sm">
                        <option value="">전체</option>
                        <option value="chat_ban" {{ request('penalty_type') == 'chat_ban' ? 'selected' : '' }}>채팅금지</option>
                        <option value="post_ban" {{ request('penalty_type') == 'post_ban' ? 'selected' : '' }}>게시글작성차단</option>
                        <option value="comment_ban" {{ request('penalty_type') == 'comment_ban' ? 'selected' : '' }}>댓글작성차단</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <label for="penalty_search" class="form-label small mb-1">검색어</label>
                    <div class="input-group input-group-sm">
                        <input type="text" 
                               name="penalty_search" 
                               id="penalty_search" 
                               class="form-control" 
                               placeholder="검색어 입력"
                               value="{{ request('penalty_search') }}">
                        <button type="submit" class="btn btn-primary" style="background-color: {{ $pointColor }}; border-color: {{ $pointColor }};">
                            <i class="bi bi-search"></i>
                        </button>
                        @if(request('penalty_search') || request('penalty_type'))
                            <a href="{{ $site->isMasterSite() ? route('master.admin.reports.index') : route('admin.reports.index', ['site' => $site->slug]) }}" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-md-3 col-12">
                    <label for="penalty_per_page" class="form-label small mb-1">보기 개수</label>
                    <select name="penalty_per_page" id="penalty_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="5" {{ request('penalty_per_page', 5) == 5 ? 'selected' : '' }}>5개</option>
                        <option value="10" {{ request('penalty_per_page') == 10 ? 'selected' : '' }}>10개</option>
                        <option value="20" {{ request('penalty_per_page') == 20 ? 'selected' : '' }}>20개</option>
                        <option value="50" {{ request('penalty_per_page') == 50 ? 'selected' : '' }}>50개</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="search_type" value="{{ request('search_type') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="report_type" value="{{ request('report_type') }}">
            <input type="hidden" name="per_page" value="{{ request('per_page', 5) }}">
        </form>
    </div>

    @if($penalties->count() > 0)
        <!-- PC 버전 테이블 -->
        <div class="table-responsive d-none d-md-block">
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
                                @elseif($penalty->type === 'post_ban')
                                    <span class="badge bg-warning text-dark">게시글작성차단</span>
                                @else
                                    <span class="badge bg-warning text-dark">댓글작성차단</span>
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

        <!-- 모바일 버전 카드 레이아웃 -->
        <div class="d-md-none">
            @foreach($penalties as $penalty)
                <div class="card border mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-secondary">ID: {{ $penalty->id }}</span>
                                <span class="badge ms-1 {{ $penalty->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $penalty->is_active ? '활성' : '비활성' }}
                                </span>
                            </div>
                            <small class="text-muted">{{ $penalty->created_at->format('Y-m-d H:i') }}</small>
                        </div>
                        
                        <div class="mb-2">
                            <div class="small text-muted mb-1">대상</div>
                            <div class="fw-medium">
                                {{ $penalty->nickname }}
                                @if($penalty->user_id)
                                    <span class="badge bg-primary ms-1" style="font-size: 0.7rem;">회원</span>
                                @else
                                    <span class="badge bg-secondary ms-1" style="font-size: 0.7rem;">게스트</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="small text-muted mb-1">패널티 유형</div>
                            <div>
                                @if($penalty->type === 'chat_ban')
                                    <span class="badge bg-danger">채팅금지</span>
                                @elseif($penalty->type === 'post_ban')
                                    <span class="badge bg-warning text-dark">게시글작성차단</span>
                                @else
                                    <span class="badge bg-warning text-dark">댓글작성차단</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="small text-muted mb-1">사유</div>
                            <div style="font-size: 0.9rem; word-break: break-word;">
                                {{ Str::limit($penalty->reason ?? '-', 50) }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">만료일</div>
                            <div>
                                @if($penalty->expires_at)
                                    <small>{{ $penalty->expires_at->format('Y-m-d H:i') }}</small>
                                @else
                                    <span class="badge bg-dark">영구</span>
                                @endif
                            </div>
                        </div>

                        @if($penalty->is_active)
                            <div class="d-grid">
                                <form action="{{ $site->isMasterSite() ? route('master.admin.reports.remove-penalty', $penalty->id) : route('admin.reports.remove-penalty', ['site' => $site->slug, 'penalty' => $penalty->id]) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('패널티를 해제하시겠습니까?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="bi bi-x-circle me-1"></i>패널티 해제
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($penalties->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-center mt-3">
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
