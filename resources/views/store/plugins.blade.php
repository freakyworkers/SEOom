@extends('layouts.app')

@section('title', '플러그인 - ' . $site->name)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-puzzle me-2"></i>플러그인
                </h2>
                <p class="text-muted mt-2">추가 기능을 구매하여 사이트에 적용할 수 있습니다.</p>
            </div>

            @if($plugins->isEmpty())
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                        <p class="text-muted">등록된 플러그인이 없습니다.</p>
                    </div>
                </div>
            @else
                <div class="row g-4">
                    @foreach($plugins as $plugin)
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm">
                                @if($plugin->thumbnail)
                                    <img src="{{ asset('storage/' . $plugin->thumbnail) }}" class="card-img-top" alt="{{ $plugin->name }}" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="bi bi-puzzle display-4 text-muted"></i>
                                    </div>
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $plugin->name }}</h5>
                                    <p class="card-text text-muted small mb-3">{{ strip_tags($plugin->description) }}</p>
                                    
                                    <div class="mb-3">
                                        <h3 class="text-primary">
                                            @if($plugin->price == 0)
                                                <span class="text-success">무료</span>
                                            @elseif($plugin->billing_cycle === 'one_time')
                                                {{ number_format($plugin->price) }}원
                                                <small class="text-muted fw-light" style="font-size: 0.4em;">(1회 결제)</small>
                                            @elseif($plugin->billing_cycle === 'monthly')
                                                {{ number_format($plugin->price) }}원
                                                <small class="text-muted fw-light">/월</small>
                                            @else
                                                {{ number_format($plugin->price) }}원
                                            @endif
                                        </h3>
                                    </div>

                                    @if($plugin->amount_mb > 0)
                                        <div class="mb-3">
                                            <h6 class="small text-muted mb-2">제공 용량:</h6>
                                            <p class="small mb-0">
                                                <i class="bi bi-hdd text-info me-2"></i>{{ number_format($plugin->amount_mb / 1024, 2) }} GB
                                            </p>
                                        </div>
                                    @endif

                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pluginModal{{ $plugin->id }}">
                                                자세히 보기
                                            </button>
                                            @if($userSites->isEmpty())
                                                <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
                                                    <i class="bi bi-exclamation-circle me-1"></i>적용 가능한 사이트가 없습니다
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#pluginModal{{ $plugin->id }}">
                                                    <i class="bi bi-check-circle me-1"></i>구매하기
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 플러그인 상세 모달 --}}
                        <div class="modal fade" id="pluginModal{{ $plugin->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $plugin->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if($plugin->thumbnail)
                                            <img src="{{ asset('storage/' . $plugin->thumbnail) }}" class="img-fluid rounded mb-3" alt="{{ $plugin->name }}">
                                        @endif
                                        <p class="mb-3">{{ strip_tags($plugin->description) }}</p>
                                        <div class="mb-3">
                                            <h6>가격</h6>
                                            <p class="h4 text-primary">
                                                @if($plugin->price == 0)
                                                    <span class="text-success">무료</span>
                                                @elseif($plugin->billing_cycle === 'one_time')
                                                    {{ number_format($plugin->price) }}원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>
                                                @elseif($plugin->billing_cycle === 'monthly')
                                                    {{ number_format($plugin->price) }}원/월
                                                @else
                                                    {{ number_format($plugin->price) }}원
                                                @endif
                                            </p>
                                        </div>
                                        @if($plugin->amount_mb > 0)
                                            <div class="mb-3">
                                                <h6>제공 용량</h6>
                                                <p class="mb-0">
                                                    <i class="bi bi-hdd text-info me-2"></i>{{ number_format($plugin->amount_mb / 1024, 2) }} GB
                                                </p>
                                            </div>
                                        @endif
                                        @if($plugin->type)
                                            <div class="mb-3">
                                                <h6>상품 타입</h6>
                                                <p class="mb-0">
                                                    <i class="bi bi-tag text-info me-2"></i>{{ $plugin->type_name_ko }}
                                                </p>
                                            </div>
                                        @endif
                                        
                                        @if($userSites->isEmpty())
                                            <div class="alert alert-warning mb-0">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                적용 가능한 사이트가 없습니다. 먼저 사이트를 생성해주세요.
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <label for="pluginSiteSelect{{ $plugin->id }}" class="form-label fw-bold">
                                                    <i class="bi bi-globe me-2"></i>적용할 사이트 선택
                                                </label>
                                                <select class="form-select" id="pluginSiteSelect{{ $plugin->id }}" name="target_site_id" required>
                                                    <option value="">사이트를 선택해주세요</option>
                                                    @foreach($userSites as $userSite)
                                                        <option value="{{ $userSite->id }}">
                                                            {{ $userSite->name }}
                                                            @if($userSite->subscription && $userSite->subscription->plan)
                                                                ({{ $userSite->subscription->plan->name }})
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                        @if($userSites->isNotEmpty())
                                            <form id="pluginPurchaseForm{{ $plugin->id }}" method="POST" action="{{ route('payment.process-addon', ['userSite' => ':userSite', 'addonProduct' => $plugin->id]) }}" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="target_site_id" id="pluginTargetSiteId{{ $plugin->id }}" value="">
                                                <button type="submit" class="btn btn-primary" id="pluginSubmit{{ $plugin->id }}" disabled>
                                                    <i class="bi bi-check-circle me-1"></i>구매하기
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 플러그인 모달의 사이트 선택 처리 및 폼 제출
    @foreach($plugins as $plugin)
        @if($userSites->isNotEmpty())
            const pluginSiteSelect{{ $plugin->id }} = document.getElementById('pluginSiteSelect{{ $plugin->id }}');
            const pluginSubmit{{ $plugin->id }} = document.getElementById('pluginSubmit{{ $plugin->id }}');
            const pluginForm{{ $plugin->id }} = document.getElementById('pluginPurchaseForm{{ $plugin->id }}');
            const pluginTargetSiteId{{ $plugin->id }} = document.getElementById('pluginTargetSiteId{{ $plugin->id }}');
            
            // 사이트 선택 시 구매하기 버튼 활성화
            if (pluginSiteSelect{{ $plugin->id }}) {
                pluginSiteSelect{{ $plugin->id }}.addEventListener('change', function() {
                    if (this.value) {
                        pluginSubmit{{ $plugin->id }}.disabled = false;
                    } else {
                        pluginSubmit{{ $plugin->id }}.disabled = true;
                    }
                });
            }
            
            // 폼 제출 처리
            if (pluginForm{{ $plugin->id }} && pluginSiteSelect{{ $plugin->id }}) {
                pluginForm{{ $plugin->id }}.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const siteId = pluginSiteSelect{{ $plugin->id }}.value;
                    if (!siteId) {
                        alert('적용할 사이트를 선택해주세요.');
                        return;
                    }
                    
                    // 사이트 slug 찾기
                    const siteSlugs = {
                        @foreach($userSites as $userSite)
                            {{ $userSite->id }}: '{{ $userSite->slug }}',
                        @endforeach
                    };
                    
                    const userSiteSlug = siteSlugs[siteId];
                    if (!userSiteSlug) {
                        alert('사이트를 찾을 수 없습니다.');
                        return;
                    }
                    
                    // 폼 action URL 업데이트
                    const currentAction = pluginForm{{ $plugin->id }}.action;
                    pluginForm{{ $plugin->id }}.action = currentAction.replace(':userSite', userSiteSlug);
                    
                    // target_site_id 설정
                    if (pluginTargetSiteId{{ $plugin->id }}) {
                        pluginTargetSiteId{{ $plugin->id }}.value = siteId;
                    }
                    
                    // 폼 제출
                    pluginForm{{ $plugin->id }}.submit();
                });
            }
        @endif
    @endforeach
});
</script>
@endpush
@endsection

