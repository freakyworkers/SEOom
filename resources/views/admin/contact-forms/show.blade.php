@extends('layouts.admin')

@section('title', '컨텍트폼 상세')
@section('page-title', '컨텍트폼 상세')
@section('page-subtitle', $contactForm->title)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>{{ $contactForm->title }}</h5>
                <a href="{{ route('admin.contact-forms.index', ['site' => $site->slug]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>목록으로
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>항목 설정:</strong>
                    <ul class="list-unstyled mt-2">
                        @foreach($contactForm->fields as $field)
                            <li class="mb-1">
                                <i class="bi bi-dot me-1"></i>
                                <strong>{{ $field['name'] }}</strong>
                                @if(!empty($field['placeholder']))
                                    <span class="text-muted">({{ $field['placeholder'] }})</span>
                                @endif
                            </li>
                        @endforeach
                        @if($contactForm->has_inquiry_content)
                            <li class="mb-1">
                                <i class="bi bi-dot me-1"></i>
                                <strong>문의 내용</strong>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="mb-3">
                    <strong>버튼 표시:</strong> {{ $contactForm->button_text }}
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>제출 데이터</h5>
            </div>
            <div class="card-body">
                @if($submissions->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3">제출된 데이터가 없습니다.</p>
                    </div>
                @else
                    <!-- PC 버전 테이블 -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    @foreach($contactForm->fields as $field)
                                        <th>{{ $field['name'] }}</th>
                                    @endforeach
                                    @if($contactForm->checkboxes && isset($contactForm->checkboxes['enabled']) && $contactForm->checkboxes['enabled'])
                                        <th>체크 항목</th>
                                    @endif
                                    @if($contactForm->has_inquiry_content)
                                        <th>문의내용</th>
                                    @endif
                                    <th>제출일</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $submission)
                                    <tr>
                                        @foreach($contactForm->fields as $field)
                                            <td>{{ $submission->data[$field['name']] ?? '-' }}</td>
                                        @endforeach
                                        @if($contactForm->checkboxes && isset($contactForm->checkboxes['enabled']) && $contactForm->checkboxes['enabled'])
                                            <td>
                                                @if($submission->checkbox_data && count($submission->checkbox_data) > 0)
                                                    {{ implode(', ', $submission->checkbox_data) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endif
                                        @if($contactForm->has_inquiry_content)
                                            <td>{{ $submission->inquiry_content ?? '-' }}</td>
                                        @endif
                                        <td>{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 모바일 버전 카드 레이아웃 -->
                    <div class="d-md-none">
                        @foreach($submissions as $submission)
                            <div class="card border mb-3">
                                <div class="card-body p-3">
                                    <!-- 폼 필드들 -->
                                    @foreach($contactForm->fields as $field)
                                        <div class="mb-3">
                                            <div class="small text-muted mb-1" style="font-size: 0.75rem;">{{ $field['name'] }}</div>
                                            <div class="fw-medium" style="font-size: 0.9rem;">{{ $submission->data[$field['name']] ?? '-' }}</div>
                                        </div>
                                    @endforeach

                                    <!-- 체크박스 데이터 -->
                                    @if($contactForm->checkboxes && isset($contactForm->checkboxes['enabled']) && $contactForm->checkboxes['enabled'])
                                        <div class="mb-3">
                                            <div class="small text-muted mb-1" style="font-size: 0.75rem;">체크 항목</div>
                                            <div class="fw-medium" style="font-size: 0.9rem;">
                                                @if($submission->checkbox_data && count($submission->checkbox_data) > 0)
                                                    {{ implode(', ', $submission->checkbox_data) }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- 문의내용 -->
                                    @if($contactForm->has_inquiry_content)
                                        <div class="mb-3">
                                            <div class="small text-muted mb-1" style="font-size: 0.75rem;">문의내용</div>
                                            <div class="fw-medium" style="font-size: 0.9rem; white-space: pre-wrap;">{{ $submission->inquiry_content ?? '-' }}</div>
                                        </div>
                                    @endif

                                    <!-- 제출일 -->
                                    <div class="mb-0 pt-2 border-top">
                                        <div class="small text-muted mb-1" style="font-size: 0.75rem;">제출일</div>
                                        <div class="small text-muted" style="font-size: 0.85rem;">{{ $submission->created_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $submissions->links() }}
                    </div>
                @endif
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
    }
</style>
@endpush
@endsection



