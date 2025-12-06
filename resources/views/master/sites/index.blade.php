@extends('layouts.master')

@section('title', '사이트 관리')
@section('page-title', '사이트 관리')
@section('page-subtitle', '모든 사이트를 관리할 수 있습니다')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('master.sites.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>사이트 생성
    </a>
</div>

<!-- Filter and Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('master.sites.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">상태</label>
                <select class="form-select" id="status" name="status">
                    <option value="">전체</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>활성</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>정지</option>
                    <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>삭제됨</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="search" class="form-label">검색</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="사이트명, 슬러그, 도메인">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>검색
                </button>
            </div>
        </form>
    </div>
</div>

@if($sites->count() > 0)
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>사이트명</th>
                        <th>슬러그</th>
                        <th>도메인</th>
                        <th style="width: 100px;">요금제</th>
                        <th style="width: 100px;">상태</th>
                        <th style="width: 150px;">생성일</th>
                        <th style="width: 200px;">작업</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sites as $site)
                        <tr>
                            <td>{{ $site->id }}</td>
                            <td>
                                <strong>{{ $site->name }}</strong>
                            </td>
                            <td><code class="small">{{ $site->slug }}</code></td>
                            <td>
                                @if($site->domain)
                                    <a href="http://{{ $site->domain }}" target="_blank" class="text-decoration-none">
                                        {{ $site->domain }} <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $plan = $site->planModel ?? \App\Models\Plan::where('slug', $site->plan)->first();
                                    $planName = $plan ? $plan->name : ($site->plan ?: '미지정');
                                    $planType = $plan ? $plan->type : 'unknown';
                                    $badgeColor = match($planType) {
                                        'community' => 'danger',
                                        'brand' => 'warning',
                                        'landing' => 'info',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">
                                    {{ $planName }}
                                </span>
                            </td>
                            <td>
                                @if($site->status === 'active')
                                    <span class="badge bg-success">활성</span>
                                @elseif($site->status === 'suspended')
                                    <span class="badge bg-warning text-dark">정지</span>
                                @else
                                    <span class="badge bg-secondary">삭제됨</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $site->created_at->format('Y-m-d') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('master.sites.show', $site->id) }}" 
                                       class="btn btn-outline-info" title="보기">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('master.sites.edit', $site->id) }}" 
                                       class="btn btn-outline-primary" title="수정">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($site->status === 'active')
                                        <form action="{{ route('master.sites.suspend', $site->id) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning" title="정지">
                                                <i class="bi bi-pause"></i>
                                            </button>
                                        </form>
                                    @elseif($site->status === 'suspended')
                                        <form action="{{ route('master.sites.activate', $site->id) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="활성화">
                                                <i class="bi bi-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('master.sites.destroy', $site->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.');">
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
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $sites->links() }}
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3 mb-2">등록된 사이트가 없습니다</h4>
            <p class="text-muted mb-4">첫 사이트를 생성해보세요!</p>
            <a href="{{ route('master.sites.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>사이트 생성
            </a>
        </div>
    </div>
@endif
@endsection







