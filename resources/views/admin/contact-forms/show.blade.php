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
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    @foreach($contactForm->fields as $field)
                                        <th>{{ $field['name'] }}</th>
                                    @endforeach
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
                                        @if($contactForm->has_inquiry_content)
                                            <td>{{ $submission->inquiry_content ?? '-' }}</td>
                                        @endif
                                        <td>{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $submissions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection



