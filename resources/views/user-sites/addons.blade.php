@extends('layouts.app')

@section('title', '추가 구매 - ' . $site->name)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>추가 구매
                    </h4>
                </div>
                <div class="card-body">
                    <h5>{{ $userSite->name }}</h5>
                    <p class="text-muted mb-0">저장 용량 및 트래픽을 추가로 구매할 수 있습니다.</p>
                </div>
            </div>

            @if(session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif
            @if(session('error'))
                <x-alert type="error">{{ session('error') }}</x-alert>
            @endif

            @if($activeAddons->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">현재 활성화된 추가 구매</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($activeAddons as $addon)
                                <div class="col-md-6 mb-3">
                                    <div class="card border">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <span class="badge @if($addon->addonProduct->type === 'storage') bg-primary @else bg-info @endif">
                                                    @if($addon->addonProduct->type === 'storage')
                                                        <i class="bi bi-hdd me-1"></i>저장 용량
                                                    @else
                                                        <i class="bi bi-arrow-left-right me-1"></i>트래픽
                                                    @endif
                                                </span>
                                            </h6>
                                            <p class="mb-1"><strong>{{ $addon->addonProduct->name }}</strong></p>
                                            <p class="mb-1 text-muted small">{{ number_format($addon->amount_mb) }}MB ({{ number_format($addon->amount_mb / 1024, 2) }}GB)</p>
                                            @if($addon->expires_at)
                                                <p class="mb-0 text-muted small">만료일: {{ $addon->expires_at->format('Y-m-d') }}</p>
                                            @else
                                                <p class="mb-0 text-muted small">일회성 구매</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($addonProducts->count() > 0)
                <div class="row">
                    @foreach($addonProducts as $addonProduct)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header @if($addonProduct->type === 'storage') bg-primary text-white @else bg-info text-white @endif">
                                    <h5 class="mb-0">
                                        @if($addonProduct->type === 'storage')
                                            <i class="bi bi-hdd me-2"></i>저장 용량
                                        @else
                                            <i class="bi bi-arrow-left-right me-2"></i>트래픽
                                        @endif
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h4 class="card-title">{{ $addonProduct->name }}</h4>
                                    @if($addonProduct->description)
                                        <p class="card-text text-muted">{{ $addonProduct->description }}</p>
                                    @endif
                                    <div class="mb-3">
                                        <h3 class="mb-0">
                                            {{ number_format($addonProduct->price) }}원
                                            @if($addonProduct->billing_cycle === 'monthly')
                                                <small class="text-muted">/월</small>
                                            @else
                                                <small class="text-muted">(일회성)</small>
                                            @endif
                                        </h3>
                                    </div>
                                    <div class="mb-3">
                                        <p class="mb-1"><strong>용량:</strong> {{ number_format($addonProduct->amount_mb) }}MB</p>
                                        <p class="mb-0 text-muted small">({{ number_format($addonProduct->amount_gb, 2) }}GB)</p>
                                    </div>
                                    <div class="mt-auto">
                                        @if($addonProduct->slug)
                                            <form action="{{ route('payment.process-addon', ['site' => $site->slug, 'userSite' => $userSite->slug, 'addonProduct' => $addonProduct->slug]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="bi bi-cart-plus me-1"></i>구매하기
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-secondary w-100" disabled>
                                                <i class="bi bi-exclamation-triangle me-1"></i>구매 불가
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="mt-3 text-muted">현재 구매 가능한 추가 상품이 없습니다.</p>
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('users.my-sites', ['site' => $site->slug]) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>돌아가기
                </a>
            </div>
        </div>
    </div>
</div>
@endsection



