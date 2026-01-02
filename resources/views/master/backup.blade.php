@extends('layouts.master')

@section('title', '백업 관리')
@section('page-title', '백업 관리')
@section('page-subtitle', '데이터베이스 백업을 생성하고 관리할 수 있습니다')

@section('content')
<!-- Backup Information -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>백업 정보</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold mb-3">자동 백업 스케줄</h6>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check-circle text-success me-2"></i>백업 생성: 매일 0시 (자정)</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>백업 보관 기간: 7일</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>오래된 백업 자동 삭제: 매일 0시 30분</li>
                </ul>
                <div class="mt-3">
                    <form method="POST" action="{{ route('master.backup.toggle-auto-backup') }}" class="d-inline" id="autoBackupForm">
                        @csrf
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="autoBackupToggle" 
                                   {{ ($autoBackupEnabled ?? '1') === '1' ? 'checked' : '' }}
                                   onchange="document.getElementById('autoBackupEnabled').value = this.checked ? '1' : '0'; this.form.submit();">
                            <input type="hidden" name="enabled" id="autoBackupEnabled" value="{{ ($autoBackupEnabled ?? '1') === '1' ? '1' : '0' }}">
                            <label class="form-check-label" for="autoBackupToggle">
                                <strong>자동 백업 {{ ($autoBackupEnabled ?? '1') === '1' ? '활성화' : '비활성화' }}</strong>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-3">백업에 포함되는 데이터</h6>
                <ul class="list-unstyled small">
                    <li><i class="bi bi-dot me-2"></i>사이트 정보 및 설정</li>
                    <li><i class="bi bi-dot me-2"></i>회원 정보 (이메일, 전화번호, 포인트 등)</li>
                    <li><i class="bi bi-dot me-2"></i>게시판 및 게시글</li>
                    <li><i class="bi bi-dot me-2"></i>댓글 및 좋아요</li>
                    <li><i class="bi bi-dot me-2"></i>메인 위젯 및 커스텀 페이지</li>
                    <li><i class="bi bi-dot me-2"></i>사이드바 위젯 및 메뉴</li>
                    <li><i class="bi bi-dot me-2"></i>배너, 팝업, 문의 양식</li>
                    <li><i class="bi bi-dot me-2"></i>알림, 쪽지, 채팅</li>
                    <li><i class="bi bi-dot me-2"></i>출석, 포인트 교환, 이벤트</li>
                    <li><i class="bi bi-dot me-2"></i>신고, 제재, 차단 사용자</li>
                    <li><i class="bi bi-dot me-2"></i>구독, 결제, 플랜 정보</li>
                    <li><i class="bi bi-dot me-2"></i>마스터 관리자 정보</li>
                </ul>
                <small class="text-muted">※ 파일 업로드(storage/app/public)도 자동으로 백업됩니다.</small>
            </div>
        </div>
    </div>
</div>

<!-- Create Backup -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>백업 생성</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('master.backup.create') }}">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <label for="site_id" class="form-label">사이트 선택 (선택사항)</label>
                    <select class="form-select" id="site_id" name="site_id">
                        <option value="">전체 백업</option>
                        @foreach(\App\Models\Site::where('status', 'active')->get() as $site)
                            <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->slug }})</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">사이트를 선택하지 않으면 전체 데이터베이스를 백업합니다.</small>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-download me-1"></i>백업 생성
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Backup List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-archive me-2"></i>백업 목록</h5>
        <span class="badge bg-primary">총 {{ count($backups) }}개</span>
    </div>
    <div class="card-body">
        @if(count($backups) > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>파일명</th>
                            <th style="width: 150px;">크기</th>
                            <th style="width: 200px;">생성일</th>
                            <th style="width: 200px;">작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                            <tr>
                                <td>
                                    <code>{{ $backup['name'] }}</code>
                                </td>
                                <td>
                                    {{ number_format($backup['size'] / 1024 / 1024, 2) }} MB
                                </td>
                                <td>
                                    <small class="text-muted">{{ date('Y-m-d H:i:s', $backup['created_at']) }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master.backup.download', $backup['name']) }}" 
                                           class="btn btn-outline-primary" title="다운로드">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form action="{{ route('master.backup.destroy', $backup['name']) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="삭제">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3 mb-2">백업 파일이 없습니다</h4>
                <p class="text-muted">위에서 백업을 생성해보세요.</p>
            </div>
        @endif
    </div>
</div>
@endsection










