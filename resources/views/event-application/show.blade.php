@extends('layouts.app')

@section('title', $setting->page_title . ' - ' . $product->item_content)

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
                <h2 class="mb-1">{{ $setting->page_title }} - {{ $product->item_content }}</h2>
            </div>
        </div>

        <div class="row">
            <!-- Left: Advertisement Image -->
            <div class="col-md-6 mb-4">
                @if($product->thumbnail_path)
                    <img src="{{ asset('storage/' . $product->thumbnail_path) }}" 
                         class="img-fluid rounded shadow-sm" 
                         alt="{{ $product->item_content }}">
                @endif
            </div>

            <!-- Right: Application Info -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <table class="table table-bordered mb-0">
                            <tr>
                                <td class="bg-light" style="width: 120px;">{{ $product->item_name }}</td>
                                <td>{{ $product->item_content }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light">처리현황</td>
                                <td>완료 {{ $completedCount }}건 거절 {{ $rejectedCount }}건</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">신청</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('event-application.store', ['site' => $site->slug, 'product' => $product->id]) }}" method="POST" id="applicationForm">
                    @csrf
                    
                    @if($setting->form_fields)
                        @foreach($setting->form_fields as $index => $field)
                            <div class="mb-3">
                                <label for="field_{{ $index }}" class="form-label">{{ $field['title'] }}</label>
                                <input type="text" 
                                       class="form-control @error('field_' . $index) is-invalid @enderror" 
                                       id="field_{{ $index }}" 
                                       name="{{ str_replace(' ', '_', strtolower($field['title'])) }}" 
                                       placeholder="{{ $field['content'] }}" 
                                       value="{{ old(str_replace(' ', '_', strtolower($field['title']))) }}" 
                                       required>
                                @error(str_replace(' ', '_', strtolower($field['title'])))
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    @endif

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check me-1"></i>신청
                        </button>
                    </div>
                </form>

                @if($product->notice)
                    <div class="mt-3 text-center">
                        <small class="text-muted">{{ $product->notice }}</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Application History -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">신청 내역</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="filterMyApplications" style="border-color: {{ $pointColor }}; color: {{ $pointColor }};">
                    <i class="bi bi-person me-1"></i>내 신청
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">신청일</th>
                                <th class="text-center">신청인</th>
                                <th class="text-center">상태</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="submissionsTableBody">
                            @forelse($allSubmissions as $submission)
                                <tr data-user-id="{{ $submission->user_id }}" data-submission-id="{{ $submission->id }}">
                                    <td class="text-center">{{ $submission->created_at->format('Y.m.d H:i:s') }}</td>
                                    <td class="text-center">{{ $submission->user ? ($submission->user->nickname ?? $submission->user->name) : '알 수 없음' }}</td>
                                    <td class="text-center">
                                        @if($submission->status === 'pending')
                                            <span style="color: #6c757d;">대기</span>
                                        @elseif($submission->status === 'completed')
                                            <span style="color: {{ $pointColor }};">완료</span>
                                        @elseif($submission->status === 'rejected')
                                            <span style="color: #dc3545;">
                                                거절
                                                @if($submission->rejection_reason && $submission->user_id === auth()->id())
                                                    <i class="bi bi-question-circle ms-1" 
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="top" 
                                                       title="{{ $submission->rejection_reason }}"></i>
                                                @endif
                                            </span>
                                        @elseif($submission->status === 'cancelled')
                                            <span style="color: #6c757d;">취소</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($submission->status === 'pending' && $submission->user_id === auth()->id())
                                            <form action="{{ route('event-application.cancel', ['site' => $site->slug, 'submission' => $submission->id]) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('정말 취소하시겠습니까?');">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-link p-0 border-0" style="color: #dc3545;" title="취소">
                                                    <i class="bi bi-x-circle-fill" style="font-size: 1.2rem;"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">신청 내역이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Requirements Error Modal -->
<div class="modal fade" id="requirementsErrorModal" tabindex="-1" aria-labelledby="requirementsErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requirementsErrorModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>신청 조건 미충족
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="requirementsErrorMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Requirements data
    const requirements = @json($requirements ?? []);
    const applicationForm = document.getElementById('applicationForm');
    const submitBtn = document.getElementById('submitBtn');
    const requirementsErrorModal = new bootstrap.Modal(document.getElementById('requirementsErrorModal'));
    const requirementsErrorMessage = document.getElementById('requirementsErrorMessage');

    // Check requirements before form submission
    if (applicationForm && submitBtn) {
        applicationForm.addEventListener('submit', function(e) {
            if (requirements && requirements.length > 0) {
                const failedRequirements = [];
                requirements.forEach(function(req) {
                    if (req.current_count < req.required_count) {
                        failedRequirements.push({
                            board_name: req.board_name,
                            required_count: req.required_count,
                            min_characters: req.min_characters,
                            current_count: req.current_count
                        });
                    }
                });

                if (failedRequirements.length > 0) {
                    e.preventDefault();
                    let errorMessage = '';
                    failedRequirements.forEach(function(req, index) {
                        if (index > 0) errorMessage += '<br>';
                        errorMessage += `${req.board_name}에 게시글 ${req.min_characters}자 이상 ${req.required_count}개의 게시글을 작성해야 신청 가능합니다.`;
                    });
                    requirementsErrorMessage.innerHTML = errorMessage;
                    requirementsErrorModal.show();
                    return false;
                }
            }
        });
    }

    // 내 신청 필터링 기능
    const filterButton = document.getElementById('filterMyApplications');
    const tableBody = document.getElementById('submissionsTableBody');
    const currentUserId = {{ auth()->id() }};
    let showingMyApplications = false;

    if (filterButton && tableBody) {
        filterButton.addEventListener('click', function() {
            showingMyApplications = !showingMyApplications;
            const rows = tableBody.querySelectorAll('tr[data-user-id]');
            
            rows.forEach(function(row) {
                const userId = parseInt(row.getAttribute('data-user-id'));
                if (showingMyApplications) {
                    // 내 신청만 표시
                    if (userId === currentUserId) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                } else {
                    // 모든 신청 표시
                    row.style.display = '';
                }
            });

            // 버튼 텍스트 변경
            const pointColor = '{{ $pointColor }}';
            if (showingMyApplications) {
                filterButton.innerHTML = '<i class="bi bi-list me-1"></i>전체 보기';
                filterButton.classList.remove('btn-outline-primary');
                filterButton.classList.add('btn-primary');
                filterButton.style.backgroundColor = pointColor;
                filterButton.style.borderColor = pointColor;
                filterButton.style.color = '#ffffff';
            } else {
                filterButton.innerHTML = '<i class="bi bi-person me-1"></i>내 신청';
                filterButton.classList.remove('btn-primary');
                filterButton.classList.add('btn-outline-primary');
                filterButton.style.backgroundColor = 'transparent';
                filterButton.style.borderColor = pointColor;
                filterButton.style.color = pointColor;
            }
        });
    }

    // Show requirements error modal if server returned errors
    @if($errors->has('requirements'))
        const serverErrors = @json($errors->get('requirements'));
        if (serverErrors && serverErrors.length > 0) {
            let errorMessage = '';
            serverErrors.forEach(function(error, index) {
                if (index > 0) errorMessage += '<br>';
                errorMessage += error;
            });
            requirementsErrorMessage.innerHTML = errorMessage;
            requirementsErrorModal.show();
        }
    @endif
});
</script>
@endpush
@endsection

