@extends('layouts.master')

@section('title', '플러그인 관리')
@section('page-title', '플러그인 관리')
@section('page-subtitle', '추가 구매 상품을 관리할 수 있습니다')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('master.plugins.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>플러그인 생성
    </a>
</div>

@if($plugins->count() > 0)
    <div class="row">
        @foreach($plugins as $plugin)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    @if($plugin->image)
                        <img src="{{ asset('storage/' . $plugin->image) }}" class="card-img-top" alt="{{ $plugin->name }}" style="height: 200px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="bi bi-puzzle display-4 text-muted"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">{{ $plugin->name }}</h5>
                            @if($plugin->is_active)
                                <span class="badge bg-success">활성</span>
                            @else
                                <span class="badge bg-secondary">비활성</span>
                            @endif
                        </div>
                        @if($plugin->description)
                            <p class="card-text text-muted small">{{ Str::limit($plugin->description, 100) }}</p>
                        @endif
                        <div class="mb-2">
                            <span class="badge bg-info">
                                <i class="bi bi-cart-check me-1"></i>구매 수: {{ $plugin->purchases_count ?? 0 }}개
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong class="text-primary">
                                @if($plugin->billing_type === 'free')
                                    무료
                                @elseif($plugin->billing_type === 'one_time' && $plugin->one_time_price > 0)
                                    {{ number_format($plugin->one_time_price) }}원 (1회)
                                @elseif($plugin->billing_type === 'monthly' && $plugin->price > 0)
                                    {{ number_format($plugin->price) }}원/월
                                @else
                                    무료
                                @endif
                            </strong>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('master.plugins.edit', $plugin) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="bi bi-pencil me-1"></i>수정
                            </a>
                            <form action="{{ route('master.plugins.destroy', $plugin) }}" method="POST" class="flex-fill" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="bi bi-trash me-1"></i>삭제
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox display-4 text-muted mb-3"></i>
            <p class="text-muted">등록된 플러그인이 없습니다.</p>
            <a href="{{ route('master.plugins.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>플러그인 생성
            </a>
        </div>
    </div>
@endif
@endsection

