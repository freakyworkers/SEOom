@extends('layouts.admin')

@section('title', '코드 커스텀')
@section('page-title', '코드 커스텀')
@section('page-subtitle', '사이트에 커스텀 코드를 추가합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-code-square me-2"></i>커스텀 태그</h5>
            </div>
            <div class="card-body">
                <form id="customCodesForm" method="POST" action="{{ route('admin.custom-codes.update', ['site' => $site->slug]) }}">
                    @csrf
                    <!-- PC 버전 테이블 -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 200px;">위치</th>
                                    <th>스크립트</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($locations as $key => $name)
                                <tr>
                                    <td class="align-top" style="vertical-align: top; padding-top: 1rem;">
                                        <label class="form-label mb-0 fw-bold d-inline-flex align-items-center">
                                            {{ $name }}
                                            @php
                                                $tooltipText = '';
                                                if($key === 'head_css') {
                                                    $tooltipText = 'HEAD 태그 안 바로 하단에 <style></style> 태그로 자동 추가됩니다. <style></style> 태그는 작성하지 마세요. CSS 내용만 입력하세요.';
                                                } elseif($key === 'head_js') {
                                                    $tooltipText = 'HEAD 태그 안 바로 하단에 <script></script> 태그로 자동 추가됩니다. <script></script> 태그는 작성하지 마세요. JavaScript/jQuery 코드만 입력하세요.';
                                                } elseif($key === 'head') {
                                                    $tooltipText = 'HEAD 태그 안에 직접 추가됩니다. HTML, CSS, JavaScript 코드를 입력할 수 있습니다.';
                                                } elseif($key === 'first_page_top') {
                                                    $tooltipText = '메인페이지 상단 배너보다 위쪽에 표시됩니다.';
                                                } elseif($key === 'first_page_bottom') {
                                                    $tooltipText = '메인페이지 하단 배너보다 아래쪽에 표시됩니다.';
                                                } elseif($key === 'content_top') {
                                                    $tooltipText = '본문 상단 배너보다 위쪽에 표시됩니다.';
                                                } elseif($key === 'content_bottom') {
                                                    $tooltipText = '본문 하단 배너보다 아래쪽에 표시됩니다.';
                                                } elseif($key === 'sidebar_top') {
                                                    $tooltipText = '사이드바 상단 배너 바로 위쪽에 표시됩니다.';
                                                } elseif($key === 'sidebar_bottom') {
                                                    $tooltipText = '사이드바 하단 배너 바로 아래쪽에 표시됩니다.';
                                                } elseif($key === 'body') {
                                                    $tooltipText = '모든 페이지 BODY 태그 영역의 하단에 포함됩니다.';
                                                }
                                            @endphp
                                            <i class="bi bi-question-circle ms-2 text-muted" 
                                               style="cursor: help; font-size: 0.9rem;" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="right" 
                                               data-bs-html="true"
                                               title="{{ $tooltipText }}"></i>
                                        </label>
                                    </td>
                                    <td>
                                        @if($key === 'head_css')
                                            <textarea 
                                                name="{{ $key }}" 
                                                id="{{ $key }}" 
                                                class="form-control font-monospace" 
                                                rows="8"
                                                placeholder="CSS 코드만 입력하세요 (style 태그는 자동으로 추가되므로 작성하지 마세요)">{{ old($key, $customCodes[$key] ?? '') }}</textarea>
                                            <div class="alert alert-info mt-2 mb-0 py-2" style="font-size: 0.875rem;">
                                                <i class="bi bi-info-circle me-2"></i>
                                                <strong>참고:</strong> <code>&lt;style&gt;&lt;/style&gt;</code> 태그는 자동으로 추가되므로 CSS 내용만 입력하세요.
                                            </div>
                                        @elseif($key === 'head_js')
                                            <textarea 
                                                name="{{ $key }}" 
                                                id="{{ $key }}" 
                                                class="form-control font-monospace" 
                                                rows="8"
                                                placeholder="JavaScript/jQuery 코드만 입력하세요 (script 태그는 자동으로 추가되므로 작성하지 마세요)">{{ old($key, $customCodes[$key] ?? '') }}</textarea>
                                            <div class="alert alert-info mt-2 mb-0 py-2" style="font-size: 0.875rem;">
                                                <i class="bi bi-info-circle me-2"></i>
                                                <strong>참고:</strong> <code>&lt;script&gt;&lt;/script&gt;</code> 태그는 자동으로 추가되므로 JavaScript/jQuery 코드만 입력하세요.
                                            </div>
                                        @else
                                            <textarea 
                                                name="{{ $key }}" 
                                                id="{{ $key }}" 
                                                class="form-control font-monospace" 
                                                rows="8"
                                                placeholder="HTML, CSS, JavaScript 코드를 입력하세요">{{ old($key, $customCodes[$key] ?? '') }}</textarea>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 모바일 버전 카드 레이아웃 -->
                    <div class="d-md-none">
                        @foreach($locations as $key => $name)
                            <div class="card border mb-3">
                                <div class="card-body p-3">
                                    <!-- 위치 라벨 -->
                                    <div class="mb-3">
                                        <label class="form-label mb-2 fw-bold d-flex align-items-center">
                                            {{ $name }}
                                            @php
                                                $tooltipText = '';
                                                if($key === 'head_css') {
                                                    $tooltipText = 'HEAD 태그 안 바로 하단에 <style></style> 태그로 자동 추가됩니다. <style></style> 태그는 작성하지 마세요. CSS 내용만 입력하세요.';
                                                } elseif($key === 'head_js') {
                                                    $tooltipText = 'HEAD 태그 안 바로 하단에 <script></script> 태그로 자동 추가됩니다. <script></script> 태그는 작성하지 마세요. JavaScript/jQuery 코드만 입력하세요.';
                                                } elseif($key === 'head') {
                                                    $tooltipText = 'HEAD 태그 안에 직접 추가됩니다. HTML, CSS, JavaScript 코드를 입력할 수 있습니다.';
                                                } elseif($key === 'first_page_top') {
                                                    $tooltipText = '메인페이지 상단 배너보다 위쪽에 표시됩니다.';
                                                } elseif($key === 'first_page_bottom') {
                                                    $tooltipText = '메인페이지 하단 배너보다 아래쪽에 표시됩니다.';
                                                } elseif($key === 'content_top') {
                                                    $tooltipText = '본문 상단 배너보다 위쪽에 표시됩니다.';
                                                } elseif($key === 'content_bottom') {
                                                    $tooltipText = '본문 하단 배너보다 아래쪽에 표시됩니다.';
                                                } elseif($key === 'sidebar_top') {
                                                    $tooltipText = '사이드바 상단 배너 바로 위쪽에 표시됩니다.';
                                                } elseif($key === 'sidebar_bottom') {
                                                    $tooltipText = '사이드바 하단 배너 바로 아래쪽에 표시됩니다.';
                                                } elseif($key === 'body') {
                                                    $tooltipText = '모든 페이지 BODY 태그 영역의 하단에 포함됩니다.';
                                                }
                                            @endphp
                                            <i class="bi bi-question-circle ms-2 text-muted" 
                                               style="cursor: help; font-size: 0.85rem;" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               data-bs-html="true"
                                               title="{{ $tooltipText }}"></i>
                                        </label>
                                    </div>

                                    <!-- 스크립트 입력 -->
                                    <div>
                                        @if($key === 'head_css')
                                            <textarea 
                                                name="{{ $key }}" 
                                                id="{{ $key }}_mobile" 
                                                class="form-control font-monospace" 
                                                rows="6"
                                                style="font-size: 0.85rem;"
                                                placeholder="CSS 코드만 입력하세요 (style 태그는 자동으로 추가되므로 작성하지 마세요)">{{ old($key, $customCodes[$key] ?? '') }}</textarea>
                                            <div class="alert alert-info mt-2 mb-0 py-2" style="font-size: 0.75rem;">
                                                <i class="bi bi-info-circle me-1"></i>
                                                <strong>참고:</strong> <code>&lt;style&gt;&lt;/style&gt;</code> 태그는 자동으로 추가되므로 CSS 내용만 입력하세요.
                                            </div>
                                        @elseif($key === 'head_js')
                                            <textarea 
                                                name="{{ $key }}" 
                                                id="{{ $key }}_mobile" 
                                                class="form-control font-monospace" 
                                                rows="6"
                                                style="font-size: 0.85rem;"
                                                placeholder="JavaScript/jQuery 코드만 입력하세요 (script 태그는 자동으로 추가되므로 작성하지 마세요)">{{ old($key, $customCodes[$key] ?? '') }}</textarea>
                                            <div class="alert alert-info mt-2 mb-0 py-2" style="font-size: 0.75rem;">
                                                <i class="bi bi-info-circle me-1"></i>
                                                <strong>참고:</strong> <code>&lt;script&gt;&lt;/script&gt;</code> 태그는 자동으로 추가되므로 JavaScript/jQuery 코드만 입력하세요.
                                            </div>
                                        @else
                                            <textarea 
                                                name="{{ $key }}" 
                                                id="{{ $key }}_mobile" 
                                                class="form-control font-monospace" 
                                                rows="6"
                                                style="font-size: 0.85rem;"
                                                placeholder="HTML, CSS, JavaScript 코드를 입력하세요">{{ old($key, $customCodes[$key] ?? '') }}</textarea>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 text-end d-none d-md-block">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>저장
                        </button>
                    </div>
                    <div class="mt-4 d-md-none">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-2"></i>저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap Tooltip 초기화
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const form = document.getElementById('customCodesForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 성공 메시지 표시
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                form.insertBefore(alertDiv, form.firstChild);
                
                // 3초 후 자동으로 사라지게
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
            } else {
                alert('저장 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다.');
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    textarea.form-control {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        resize: vertical;
    }
    
    .font-monospace {
        font-family: 'Courier New', monospace;
    }
    
    /* 모바일 최적화 스타일 */
    @media (max-width: 767.98px) {
        /* 카드 스타일 최적화 */
        .d-md-none .card {
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* 카드 간격 최소화 */
        .d-md-none .card.mb-3 {
            margin-bottom: 0.75rem !important;
        }
        
        /* 텍스트 영역 최적화 */
        .d-md-none textarea {
            font-size: 0.85rem !important;
            line-height: 1.5;
        }
        
        /* 알림 메시지 최적화 */
        .d-md-none .alert {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }
        
        /* 저장 버튼 최적화 */
        .d-md-none .btn-primary {
            width: 100%;
            padding: 0.5rem;
            font-size: 0.9rem;
        }
    }
</style>
@endpush
@endsection

