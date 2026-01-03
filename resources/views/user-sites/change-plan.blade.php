@extends('layouts.app')

@section('title', '플랜 변경 - ' . $site->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-arrow-left-right me-2"></i>플랜 변경
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <x-alert type="success">{{ session('success') }}</x-alert>
                    @endif
                    @if(session('error'))
                        <x-alert type="error">{{ session('error') }}</x-alert>
                    @endif
                    @if(session('info'))
                        <x-alert type="info">{{ session('info') }}</x-alert>
                    @endif

                    <div class="mb-4">
                        <h5>현재 플랜</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="mb-2">{{ $subscription->plan->name }}</h6>
                                <p class="text-muted mb-0">
                                    <strong>월간 요금:</strong> {{ number_format($subscription->plan->price) }}원
                                </p>
                                @if($subscription->current_period_end)
                                    <p class="text-muted mb-0">
                                        <strong>다음 결제일:</strong> {{ $subscription->current_period_end->format('Y-m-d') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>변경할 플랜 선택</h5>
                        <form method="POST" action="{{ route('user-sites.change-plan-process', ['site' => $site->slug, 'userSite' => $userSite->slug]) }}">
                            @csrf
                            
                            <div class="row g-3">
                                @foreach($plans as $plan)
                                    <div class="col-md-4">
                                        <div class="card h-100 @if($subscription->plan_id === $plan->id) border-primary @endif">
                                            <div class="card-header @if($subscription->plan_id === $plan->id) bg-primary text-white @else bg-light @endif">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="plan_id" 
                                                           id="plan_{{ $plan->id }}" 
                                                           value="{{ $plan->id }}"
                                                           @if($subscription->plan_id === $plan->id) checked disabled @endif
                                                           required>
                                                    <label class="form-check-label fw-bold" for="plan_{{ $plan->id }}">
                                                        {{ $plan->name }}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <h3 class="card-title">
                                                    {{ number_format($plan->price) }}원<small class="text-muted fw-light">/월</small>
                                                </h3>
                                                <p class="card-text text-muted small">{{ $plan->description }}</p>
                                                
                                                @if($subscription->plan_id === $plan->id)
                                                    <div class="alert alert-info mb-0">
                                                        <small><i class="bi bi-info-circle me-1"></i>현재 플랜</small>
                                                    </div>
                                                @else
                                                    @php
                                                        $currentPrice = (float) $subscription->plan->price;
                                                        $newPrice = (float) $plan->price;
                                                        $difference = $newPrice - $currentPrice;
                                                    @endphp
                                                    @if($difference > 0)
                                                        <div class="alert alert-warning mb-0">
                                                            <small><i class="bi bi-arrow-up me-1"></i>상향: +{{ number_format($difference) }}원/월</small>
                                                        </div>
                                                    @elseif($difference < 0)
                                                        <div class="alert alert-success mb-0">
                                                            <small><i class="bi bi-arrow-down me-1"></i>하향: {{ number_format($difference) }}원/월</small>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="immediate" id="immediate" value="1">
                                    <label class="form-check-label" for="immediate">
                                        <strong>즉시 변경</strong> (비례 계산된 차액을 지금 결제/환불)
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        체크하지 않으면 다음 결제일부터 새 플랜이 적용됩니다.
                                    </small>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="{{ route('users.my-sites', ['site' => $site->slug]) }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>취소
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>플랜 변경하기
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





