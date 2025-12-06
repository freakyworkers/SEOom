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
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                    <td class="text-center">• {{ $application->user->name }}</td>
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
            </div>
        </div>
    </div>
</div>

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
</script>
@endpush
@endsection
