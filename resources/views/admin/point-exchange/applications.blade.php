@extends('layouts.admin')

@section('title', '신청 목록 - ' . $product->item_content)
@section('page-title', '신청 목록 - ' . $product->item_content)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list me-2"></i>{{ $product->item_name }}: {{ $product->item_content }}
                </h5>
                <a href="{{ route('admin.point-exchange.index', ['site' => $site->slug]) }}" 
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>목록으로
                </a>
            </div>
            <div class="card-body">
                <!-- PC 버전 테이블 -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">시각</th>
                                <th class="text-center">유저</th>
                                <th class="text-center">신청금액</th>
                                @if($setting->form_fields)
                                    @foreach($setting->form_fields as $field)
                                        <th class="text-center">{{ $field['title'] }}</th>
                                    @endforeach
                                @endif
                                <th class="text-center">상태</th>
                                <th class="text-center">거절사유</th>
                                <th class="text-center">저장</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $application)
                                <tr>
                                    <td class="text-center">{{ $application->created_at->format('Y.m.d H:i:s') }}</td>
                                    <td class="text-center">• {{ $application->user->nickname ?? $application->user->name }}</td>
                                    <td class="text-center">{{ number_format($application->points) }}P</td>
                                    @if($setting->form_fields)
                                        @foreach($setting->form_fields as $field)
                                            <td class="text-center">
                                                {{ $application->form_data[$field['title']] ?? '-' }}
                                            </td>
                                        @endforeach
                                    @endif
                                    <td class="text-center">
                                        <form action="{{ route('admin.point-exchange.update-application', ['site' => $site->slug, 'application' => $application->id]) }}" 
                                              method="POST" class="d-inline" id="statusForm{{ $application->id }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="rejection_reason" value="{{ $application->rejection_reason ?? '' }}" id="rejectionReason{{ $application->id }}">
                                            <select name="status" class="form-select form-select-sm" 
                                                    style="width: auto; display: inline-block;">
                                                <option value="pending" {{ $application->status === 'pending' ? 'selected' : '' }}>대기</option>
                                                <option value="completed" {{ $application->status === 'completed' ? 'selected' : '' }}>완료</option>
                                                <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>보류</option>
                                                <option value="cancelled" {{ $application->status === 'cancelled' ? 'selected' : '' }}>취소</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <input type="text" 
                                               class="form-control form-control-sm rejection-reason-input" 
                                               data-application-id="{{ $application->id }}"
                                               value="{{ $application->rejection_reason ?? '' }}" 
                                               placeholder="거절사유 입력"
                                               style="width: 200px; display: inline-block;">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="saveApplication({{ $application->id }})">
                                            <i class="bi bi-save"></i> 저장
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 4 + (count($setting->form_fields ?? [])) }}" class="text-center text-muted">
                                        신청 내역이 없습니다.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 모바일 버전 카드 레이아웃 -->
                <div class="d-md-none">
                    @forelse($applications as $application)
                        <div class="card border mb-3">
                            <div class="card-body p-3">
                                <!-- 상단: 시각, 유저, 신청금액 -->
                                <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                                    <div>
                                        <div class="small text-muted mb-1" style="font-size: 0.75rem;">시각</div>
                                        <div class="small fw-medium" style="font-size: 0.85rem;">{{ $application->created_at->format('Y.m.d H:i:s') }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-muted mb-1" style="font-size: 0.75rem;">신청금액</div>
                                        <div class="fw-bold text-primary" style="font-size: 0.95rem;">{{ number_format($application->points) }}P</div>
                                    </div>
                                </div>

                                <!-- 유저 정보 -->
                                <div class="mb-3">
                                    <div class="small text-muted mb-1" style="font-size: 0.75rem;">유저</div>
                                    <div class="fw-medium" style="font-size: 0.9rem;">• {{ $application->user->nickname ?? $application->user->name }}</div>
                                </div>

                                <!-- 동적 폼 필드들 -->
                                @if($setting->form_fields)
                                    @foreach($setting->form_fields as $field)
                                        <div class="mb-3">
                                            <div class="small text-muted mb-1" style="font-size: 0.75rem;">{{ $field['title'] }}</div>
                                            <div class="fw-medium" style="font-size: 0.9rem;">{{ $application->form_data[$field['title']] ?? '-' }}</div>
                                        </div>
                                    @endforeach
                                @endif

                                <!-- 상태 선택 -->
                                <div class="mb-3">
                                    <div class="small text-muted mb-1" style="font-size: 0.75rem;">상태</div>
                                    <form action="{{ route('admin.point-exchange.update-application', ['site' => $site->slug, 'application' => $application->id]) }}" 
                                          method="POST" id="statusFormMobile{{ $application->id }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="rejection_reason" value="{{ $application->rejection_reason ?? '' }}" id="rejectionReasonMobile{{ $application->id }}">
                                        <select name="status" class="form-select form-select-sm" style="font-size: 0.85rem;">
                                            <option value="pending" {{ $application->status === 'pending' ? 'selected' : '' }}>대기</option>
                                            <option value="completed" {{ $application->status === 'completed' ? 'selected' : '' }}>완료</option>
                                            <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>보류</option>
                                            <option value="cancelled" {{ $application->status === 'cancelled' ? 'selected' : '' }}>취소</option>
                                        </select>
                                    </form>
                                </div>

                                <!-- 거절사유 -->
                                <div class="mb-3">
                                    <div class="small text-muted mb-1" style="font-size: 0.75rem;">거절사유</div>
                                    <input type="text" 
                                           class="form-control form-control-sm rejection-reason-input-mobile" 
                                           data-application-id="{{ $application->id }}"
                                           value="{{ $application->rejection_reason ?? '' }}" 
                                           placeholder="거절사유 입력"
                                           style="font-size: 0.85rem;">
                                </div>

                                <!-- 저장 버튼 -->
                                <div class="d-grid">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="saveApplicationMobile({{ $application->id }})"
                                            style="font-size: 0.8rem; padding: 0.35rem 0.5rem;">
                                        <i class="bi bi-save"></i> 저장
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h4 class="mt-3 mb-2">신청 내역이 없습니다</h4>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* 모바일 최적화 스타일 */
    @media (max-width: 767.98px) {
        /* 카드 스타일 최적화 */
        .d-md-none .card {
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* 입력 필드 최적화 */
        .d-md-none .form-control-sm {
            font-size: 0.85rem;
        }
        
        /* 버튼 최적화 */
        .d-md-none .btn-sm {
            font-size: 0.8rem;
            padding: 0.35rem 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function saveApplication(applicationId) {
    const form = document.getElementById('statusForm' + applicationId);
    const rejectionInput = document.querySelector(`.rejection-reason-input[data-application-id="${applicationId}"]`);
    const hiddenInput = document.getElementById('rejectionReason' + applicationId);
    
    if (rejectionInput) {
        hiddenInput.value = rejectionInput.value;
    }
    
    form.submit();
}

function saveApplicationMobile(applicationId) {
    const form = document.getElementById('statusFormMobile' + applicationId);
    const rejectionInput = document.querySelector(`.rejection-reason-input-mobile[data-application-id="${applicationId}"]`);
    const hiddenInput = document.getElementById('rejectionReasonMobile' + applicationId);
    
    if (rejectionInput) {
        hiddenInput.value = rejectionInput.value;
    }
    
    form.submit();
}
</script>
@endpush
@endsection
