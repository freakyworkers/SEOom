@extends('layouts.master')

@section('title', $site->name . ' - 상세 정보')
@section('page-title', $site->name)
@section('page-subtitle', '마스터 사이트 상세 정보 및 관리')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>마스터 사이트 정보</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">사이트 이름</dt>
                    <dd class="col-sm-9">
                        {{ $site->name }}
                        <span class="badge bg-purple ms-2">마스터</span>
                    </dd>

                    <dt class="col-sm-3">슬러그</dt>
                    <dd class="col-sm-9"><code>{{ $site->slug }}</code></dd>

                    <dt class="col-sm-3">도메인</dt>
                    <dd class="col-sm-9">
                        @if($site->domain)
                            <a href="http://{{ $site->domain }}" target="_blank">
                                {{ $site->domain }} <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        @else
                            <span class="text-muted">설정되지 않음</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">요금제</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-purple">마스터 (모든 기능 사용 가능)</span>
                    </dd>

                    <dt class="col-sm-3">상태</dt>
                    <dd class="col-sm-9">
                        @if($site->status === 'active')
                            <span class="badge bg-success">활성</span>
                        @elseif($site->status === 'suspended')
                            <span class="badge bg-warning text-dark">정지</span>
                        @else
                            <span class="badge bg-secondary">삭제됨</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">생성일</dt>
                    <dd class="col-sm-9">{{ $site->created_at->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-3">수정일</dt>
                    <dd class="col-sm-9">{{ $site->updated_at->format('Y-m-d H:i:s') }}</dd>
                </dl>

                <div class="mt-4">
                    <a href="{{ route('master.master-sites.edit', $site->id) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil me-1"></i>수정
                    </a>
                    @if($site->status === 'active')
                        <form action="{{ route('master.master-sites.suspend', $site->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning me-2">
                                <i class="bi bi-pause me-1"></i>정지
                            </button>
                        </form>
                    @elseif($site->status === 'suspended')
                        <form action="{{ route('master.master-sites.activate', $site->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success me-2">
                                <i class="bi bi-play me-1"></i>활성화
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('master.master-sites.destroy', $site->id) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>삭제
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>통계</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>사용자</span>
                        <strong>{{ number_format($stats['users']) }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>게시판</span>
                        <strong>{{ number_format($stats['boards']) }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>게시글</span>
                        <strong>{{ number_format($stats['posts']) }}</strong>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span>댓글</span>
                        <strong>{{ number_format($stats['comments']) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>빠른 링크</h5>
            </div>
            <div class="card-body">
                @php
                    // 메인 마스터 사이트인지 확인
                    $mainMasterSite = \App\Models\Site::getMasterSite();
                    $isMainMasterSite = $mainMasterSite && $mainMasterSite->id === $site->id;
                @endphp
                @if($isMainMasterSite)
                    <a href="/" 
                       target="_blank" 
                       class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-house me-1"></i>사이트 보기
                    </a>
                @else
                    <a href="{{ route('home', ['site' => $site->slug]) }}" 
                       target="_blank" 
                       class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-house me-1"></i>사이트 보기
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

