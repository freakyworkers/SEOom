@extends('layouts.app')

@section('title', $setting->page_title)

@section('content')
@php
    // 포인트 컬러 설정
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $pointColor = $themeDarkMode === 'dark' 
        ? $site->getSetting('color_dark_point_main', '#ffffff')
        : $site->getSetting('color_light_point_main', '#0d6efd');
@endphp
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <div class="bg-white p-3 rounded shadow-sm">
                <h2 class="mb-1">{{ $setting->page_title }}</h2>
            </div>
        </div>

        <!-- Notice Box -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-center">{{ $setting->notice_title }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-0">
                    @if($setting->notices)
                        @foreach($setting->notices as $index => $notice)
                            <div class="col-md-6" style="padding: 1px;">
                                <div class="p-2 rounded" style="border: 1px solid {{ $pointColor }};">
                                    <span class="fw-bold" style="color: {{ $pointColor }};">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}.</span>
                                    <span>{{ $notice }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Total Paid Amount -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">총 지급완료액</span>
                    <span class="h4 mb-0 text-primary fw-bold">{{ number_format($totalPaid) }}P</span>
                </div>
            </div>
        </div>

                <!-- Products Grid -->
                @php
                    // 컬럼 클래스 계산 (Bootstrap 12 컬럼 시스템)
                    $mobileCols = 12 / ($setting->mobile_columns ?? 2);
                    $pcCols = 12 / ($setting->pc_columns ?? 4);
                    $colClass = 'col-' . $mobileCols . ' col-lg-' . $pcCols;
                @endphp
                <div class="row">
                    @forelse($products as $product)
                        <div class="{{ $colClass }} mb-4">
                            <div class="card shadow-sm h-100">
                                @if($product->thumbnail_path)
                                    <img src="{{ asset('storage/' . $product->thumbnail_path) }}" 
                                         class="card-img-top" 
                                         alt="{{ $product->item_content }}"
                                         style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                                <div class="card-body text-center">
                                    <h6 class="card-title">{{ $product->item_content }}</h6>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            총 금액 {{ number_format($product->total_amount) }}P<br>
                                            완료 {{ $product->completed_count }}건 보류 {{ $product->rejected_count }}건
                                        </small>
                                    </div>
                                    <a href="{{ route('point-exchange.show', ['site' => $site->slug, 'product' => $product->id]) }}" 
                                       class="btn btn-primary w-100">
                                        <i class="bi bi-arrow-repeat me-1"></i>교환하기
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                등록된 교환 상품이 없습니다.
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="mt-4 mb-4">
                        {{ $products->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                    </div>
                @endif
            </div>
        </div>
        @endsection

