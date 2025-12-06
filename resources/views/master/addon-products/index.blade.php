@extends('layouts.master')

@section('title', '추가 구매 상품 관리')
@section('page-title', '추가 구매 상품 관리')
@section('page-subtitle', '저장 용량 및 트래픽 추가 구매 상품을 관리할 수 있습니다')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('master.addon-products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>상품 생성
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($addonProducts->count() > 0)
    <div class="row">
        @foreach($addonProducts as $addonProduct)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge 
                                @if($addonProduct->type === 'storage') bg-primary
                                @elseif($addonProduct->type === 'traffic') bg-info
                                @elseif(in_array($addonProduct->type, ['feature_crawler', 'feature_event_application', 'feature_point_exchange'])) bg-success
                                @elseif($addonProduct->type === 'board_type_event') bg-warning
                                @elseif($addonProduct->type === 'registration_referral') bg-secondary
                                @else bg-dark
                                @endif">
                                @if($addonProduct->type === 'storage')
                                    <i class="bi bi-hdd me-1"></i>저장 용량
                                @elseif($addonProduct->type === 'traffic')
                                    <i class="bi bi-arrow-left-right me-1"></i>트래픽
                                @elseif($addonProduct->type === 'feature_crawler')
                                    <i class="bi bi-robot me-1"></i>크롤러
                                @elseif($addonProduct->type === 'feature_event_application')
                                    <i class="bi bi-calendar-event me-1"></i>신청형 이벤트
                                @elseif($addonProduct->type === 'feature_point_exchange')
                                    <i class="bi bi-currency-exchange me-1"></i>포인트 교환
                                @elseif($addonProduct->type === 'board_type_event')
                                    <i class="bi bi-ticket-perforated me-1"></i>이벤트 게시판
                                @elseif($addonProduct->type === 'registration_referral')
                                    <i class="bi bi-person-plus me-1"></i>추천인 기능
                                @elseif($addonProduct->type === 'feature_point_message')
                                    <i class="bi bi-envelope-heart me-1"></i>포인트 쪽지
                                @else
                                    <i class="bi bi-box me-1"></i>{{ $addonProduct->type }}
                                @endif
                            </span>
                            @if($addonProduct->is_active)
                                <span class="badge bg-success">활성</span>
                            @else
                                <span class="badge bg-secondary">비활성</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $addonProduct->name }}</h5>
                        @if($addonProduct->description)
                            <p class="card-text text-muted small">{{ $addonProduct->description }}</p>
                        @endif
                        @if(in_array($addonProduct->type, ['storage', 'traffic']))
                        <div class="mb-2">
                            <strong>용량:</strong> {{ number_format($addonProduct->amount_mb) }}MB ({{ number_format($addonProduct->amount_gb, 2) }}GB)
                        </div>
                        @endif
                        <div class="mb-2">
                            <strong>가격:</strong> {{ number_format($addonProduct->price) }}원
                            @if($addonProduct->billing_cycle === 'monthly')
                                <span class="text-muted">/월</span>
                            @else
                                <span class="text-muted">(일회성)</span>
                            @endif
                        </div>
                        @if(in_array($addonProduct->type, ['storage', 'traffic']))
                        <div class="mb-2">
                            <small class="text-muted">
                                GB당 가격: {{ number_format($addonProduct->price_per_gb) }}원
                            </small>
                        </div>
                        @endif
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="bi bi-people me-1"></i>구매자 수: {{ $addonProduct->userAddons()->count() }}명
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex gap-2">
                            <a href="{{ route('master.addon-products.edit', $addonProduct) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="bi bi-pencil me-1"></i>수정
                            </a>
                            <form action="{{ route('master.addon-products.destroy', $addonProduct) }}" method="POST" class="flex-fill" onsubmit="return confirm('정말 삭제하시겠습니까?');">
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
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
        <p class="mt-3 text-muted">등록된 추가 구매 상품이 없습니다.</p>
        <a href="{{ route('master.addon-products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>첫 상품 생성하기
        </a>
    </div>
@endif
@endsection

