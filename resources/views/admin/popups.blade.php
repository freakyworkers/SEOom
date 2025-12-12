@extends('layouts.admin')

@section('title', '팝업')
@section('page-title', '팝업')
@section('page-subtitle', '팝업 설정을 관리할 수 있습니다')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-window me-2"></i>팝업 설정</h5>
    </div>
    <div class="card-body">
        <form id="popupSettingsForm">
            @csrf
            <div class="row g-3 align-items-start">
                <div class="col-md-6">
                    <label for="popup_display_type" class="form-label mb-0"><strong>표시 방식</strong></label>
                    <select class="form-select" name="display_type" id="popup_display_type">
                        <option value="overlay" {{ $displayType == 'overlay' ? 'selected' : '' }}>팝업 겹치기</option>
                        <option value="list" {{ $displayType == 'list' ? 'selected' : '' }}>나열하기</option>
                    </select>
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-info-circle me-1"></i>겹치기: 여러 팝업이 같은 위치에 겹쳐 표시됩니다. 하나를 닫으면 다음 팝업이 나타납니다.
                    </small>
                </div>
                <div class="col-md-6">
                    <label for="popup_position" class="form-label mb-0"><strong>팝업 위치</strong></label>
                    <select class="form-select" name="position" id="popup_position">
                        <option value="center" {{ $position == 'center' ? 'selected' : '' }}>중앙</option>
                        <option value="top-left" {{ $position == 'top-left' ? 'selected' : '' }}>좌상단</option>
                        <option value="top-right" {{ $position == 'top-right' ? 'selected' : '' }}>우상단</option>
                        <option value="bottom-left" {{ $position == 'bottom-left' ? 'selected' : '' }}>좌하단</option>
                        <option value="bottom-right" {{ $position == 'bottom-right' ? 'selected' : '' }}>우하단</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>팝업 등록 리스트
            <span class="badge bg-primary ms-2">전체 {{ $popups->total() }}개</span>
        </h5>
        <button type="button" class="btn btn-primary" id="addPopupBtn">
            <i class="bi bi-plus-circle me-1"></i>팝업 추가
        </button>
    </div>
    <div class="card-body">
        {{-- 데스크탑 버전 (테이블) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 200px; text-align: center;">이미지</th>
                        <th style="text-align: center;">링크</th>
                        <th style="width: 120px; text-align: center;">새 창으로 띄우기</th>
                        <th style="width: 180px; text-align: center;">등록시각</th>
                        <th style="width: 100px; text-align: center;">순서</th>
                        <th style="width: 150px; text-align: center;">위치</th>
                        <th style="width: 80px; text-align: center;">삭제</th>
                    </tr>
                </thead>
                <tbody id="popupsTableBody">
                    @forelse($popups as $popup)
                        <tr data-popup-id="{{ $popup->id }}">
                            <td style="text-align: center; vertical-align: middle;">
                                @if($popup->type === 'html')
                                    <div class="popup-html-wrapper" style="position: relative; display: inline-block; max-width: 180px; max-height: 100px; overflow: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 5px;">
                                        <div class="popup-html-preview" data-popup-id="{{ $popup->id }}">
                                            {!! $popup->html_code !!}
                                        </div>
                                    </div>
                                @else
                                    <div class="popup-image-wrapper" style="position: relative; display: inline-block;" data-popup-id="{{ $popup->id }}">
                                        @if($popup->image_path)
                                            <img src="{{ asset('storage/' . $popup->image_path) }}" 
                                                 alt="팝업 이미지" 
                                                 class="popup-preview-image" 
                                                 style="max-width: 180px; max-height: 100px; width: auto; height: auto; cursor: pointer; border: 1px solid #dee2e6; border-radius: 4px;"
                                                 data-popup-id="{{ $popup->id }}"
                                                 onerror="this.style.display='none'; $(this).siblings('.popup-image-placeholder').show();">
                                        @endif
                                        <label class="popup-image-placeholder" 
                                               style="display: {{ $popup->image_path ? 'none' : 'block' }}; width: 180px; height: 100px; border: 2px dashed #dee2e6; border-radius: 4px; cursor: pointer; text-align: center; line-height: 100px; color: #6c757d; background-color: #f8f9fa;"
                                               data-popup-id="{{ $popup->id }}">
                                            <i class="bi bi-image" style="font-size: 2rem;"></i><br>
                                            <small>이미지 클릭</small>
                                        </label>
                                        <input type="file" 
                                               class="popup-image-input d-none" 
                                               accept="image/*"
                                               data-popup-id="{{ $popup->id }}">
                                    </div>
                                @endif
                            </td>
                            <td style="vertical-align: middle;">
                                @if($popup->type === 'html')
                                    <textarea class="form-control form-control-sm popup-html-input" 
                                              name="html_codes[{{ $popup->id }}]"
                                              rows="5"
                                              placeholder="HTML 코드를 입력하세요.">{{ $popup->html_code ?? '' }}</textarea>
                                @else
                                    <input type="text" 
                                           class="form-control form-control-sm popup-link-input" 
                                           name="links[{{ $popup->id }}]"
                                           value="{{ $popup->link ?? '' }}" 
                                           placeholder="https://example.com">
                                @endif
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input popup-open-new-window-checkbox" 
                                           type="checkbox" 
                                           name="open_new_windows[{{ $popup->id }}]"
                                           value="1"
                                           id="open_new_window_{{ $popup->id }}"
                                           data-popup-id="{{ $popup->id }}"
                                           {{ $popup->open_new_window ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                {{ $popup->created_at->format('Y.m.d H:i:s') }}
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div class="btn-group-vertical" role="group">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary popup-order-up-btn" 
                                            data-popup-id="{{ $popup->id }}"
                                            {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary popup-order-down-btn" 
                                            data-popup-id="{{ $popup->id }}"
                                            {{ $loop->last ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </div>
                            </td>
                            <td style="vertical-align: middle;">
                                <select class="form-select form-select-sm popup-target-type-select" 
                                        name="target_types[{{ $popup->id }}]"
                                        data-popup-id="{{ $popup->id }}">
                                    <option value="all" {{ $popup->target_type == 'all' ? 'selected' : '' }}>전체</option>
                                    <option value="main" {{ $popup->target_type == 'main' ? 'selected' : '' }}>메인</option>
                                    <option value="attendance" {{ $popup->target_type == 'attendance' ? 'selected' : '' }}>출첵</option>
                                    <option value="point-exchange" {{ $popup->target_type == 'point-exchange' ? 'selected' : '' }}>{{ $pointExchangeTitle }}</option>
                                    <option value="event-application" {{ $popup->target_type == 'event-application' ? 'selected' : '' }}>{{ $eventApplicationTitle }}</option>
                                    @foreach($boards as $board)
                                        <option value="board_{{ $board->id }}" {{ ($popup->target_type == 'board' && $popup->target_board_id == $board->id) ? 'selected' : '' }}>
                                            {{ $board->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <button type="button" 
                                        class="btn btn-sm btn-danger delete-popup-btn" 
                                        data-popup-id="{{ $popup->id }}">
                                    <i class="bi bi-trash"></i> 삭제
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                                <p class="mt-3 text-muted">등록된 팝업이 없습니다.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 모바일 버전 (카드 레이아웃) --}}
        <div class="d-md-none">
            <div class="d-grid gap-3" id="popupsCardBody">
                @forelse($popups as $popup)
                    <div class="card shadow-sm" data-popup-id="{{ $popup->id }}">
                        {{-- 카드 헤더: 위치와 액션 버튼 --}}
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <select class="form-select form-select-sm popup-target-type-select d-inline-block" 
                                        name="target_types[{{ $popup->id }}]"
                                        data-popup-id="{{ $popup->id }}"
                                        style="width: auto; min-width: 100px;">
                                    <option value="all" {{ $popup->target_type == 'all' ? 'selected' : '' }}>전체</option>
                                    <option value="main" {{ $popup->target_type == 'main' ? 'selected' : '' }}>메인</option>
                                    <option value="attendance" {{ $popup->target_type == 'attendance' ? 'selected' : '' }}>출첵</option>
                                    <option value="point-exchange" {{ $popup->target_type == 'point-exchange' ? 'selected' : '' }}>{{ $pointExchangeTitle }}</option>
                                    <option value="event-application" {{ $popup->target_type == 'event-application' ? 'selected' : '' }}>{{ $eventApplicationTitle }}</option>
                                    @foreach($boards as $board)
                                        <option value="board_{{ $board->id }}" {{ ($popup->target_type == 'board' && $popup->target_board_id == $board->id) ? 'selected' : '' }}>
                                            {{ $board->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="badge bg-secondary ms-2">{{ $popup->created_at->format('m.d') }}</span>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary popup-order-up-btn" 
                                        data-popup-id="{{ $popup->id }}"
                                        {{ $loop->first ? 'disabled' : '' }}
                                        title="위로">
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary popup-order-down-btn" 
                                        data-popup-id="{{ $popup->id }}"
                                        {{ $loop->last ? 'disabled' : '' }}
                                        title="아래로">
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-danger delete-popup-btn" 
                                        data-popup-id="{{ $popup->id }}"
                                        title="삭제">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            {{-- 이미지/HTML 섹션 --}}
                            <div class="mb-3 text-center">
                                @if($popup->type === 'html')
                                    <div class="popup-html-wrapper" style="position: relative; display: inline-block; max-width: 100%; max-height: 150px; overflow: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 5px;">
                                        <div class="popup-html-preview" data-popup-id="{{ $popup->id }}">
                                            {!! $popup->html_code !!}
                                        </div>
                                    </div>
                                @else
                                    <div class="popup-image-wrapper" style="position: relative; display: inline-block;" data-popup-id="{{ $popup->id }}">
                                        @if($popup->image_path)
                                            <img src="{{ asset('storage/' . $popup->image_path) }}" 
                                                 alt="팝업 이미지" 
                                                 class="popup-preview-image" 
                                                 style="max-width: 100%; max-height: 150px; width: auto; height: auto; cursor: pointer; border: 1px solid #dee2e6; border-radius: 4px;"
                                                 data-popup-id="{{ $popup->id }}"
                                                 onerror="this.style.display='none'; $(this).siblings('.popup-image-placeholder').show();">
                                        @endif
                                        <label class="popup-image-placeholder" 
                                               style="display: {{ $popup->image_path ? 'none' : 'block' }}; width: 100%; max-width: 200px; height: 120px; border: 2px dashed #dee2e6; border-radius: 4px; cursor: pointer; text-align: center; line-height: 120px; color: #6c757d; background-color: #f8f9fa; margin: 0 auto;"
                                               data-popup-id="{{ $popup->id }}">
                                            <i class="bi bi-image" style="font-size: 2rem;"></i><br>
                                            <small>이미지 클릭</small>
                                        </label>
                                        <input type="file" 
                                               class="popup-image-input d-none" 
                                               accept="image/*"
                                               data-popup-id="{{ $popup->id }}">
                                    </div>
                                @endif
                            </div>

                            {{-- 링크/HTML 코드 섹션 --}}
                            <div class="mb-3">
                                <label class="form-label small mb-1 fw-bold">링크/HTML 코드</label>
                                @if($popup->type === 'html')
                                    <textarea class="form-control form-control-sm popup-html-input" 
                                              name="html_codes[{{ $popup->id }}]"
                                              rows="4"
                                              placeholder="HTML 코드를 입력하세요.">{{ $popup->html_code ?? '' }}</textarea>
                                @else
                                    <input type="text" 
                                           class="form-control form-control-sm popup-link-input" 
                                           name="links[{{ $popup->id }}]"
                                           value="{{ $popup->link ?? '' }}" 
                                           placeholder="https://example.com">
                                @endif
                            </div>

                            {{-- 새 창으로 띄우기 --}}
                            <div class="mb-2">
                                <div class="form-check">
                                    <input class="form-check-input popup-open-new-window-checkbox" 
                                           type="checkbox" 
                                           name="open_new_windows[{{ $popup->id }}]"
                                           value="1"
                                           id="open_new_window_mobile_{{ $popup->id }}"
                                           data-popup-id="{{ $popup->id }}"
                                           {{ $popup->open_new_window ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="open_new_window_mobile_{{ $popup->id }}">
                                        새 창으로 띄우기
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                            <p class="mt-3 text-muted">등록된 팝업이 없습니다.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        @if($popups->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-center mt-4">
                    @php
                        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                        $pointColor = $themeDarkMode === 'dark' 
                            ? $site->getSetting('color_dark_point_main', '#ffffff')
                            : $site->getSetting('color_light_point_main', '#0d6efd');
                    @endphp
                    {{ $popups->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                </div>
            </div>
        @endif
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary btn-lg" id="saveAllPopupSettingsBtn">
                    <i class="bi bi-save me-1"></i>저장
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 팝업 추가 모달 -->
<div class="modal fade" id="addPopupModal" tabindex="-1" aria-labelledby="addPopupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPopupModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>팝업 추가
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPopupForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="popup_type" class="form-label">팝업 타입</label>
                        <select class="form-select" name="type" id="popup_type" required>
                            <option value="image">이미지</option>
                            <option value="html">애드센스/HTML</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="popup_image_field">
                        <label for="popup_image" class="form-label">이미지</label>
                        <input type="file" class="form-control" name="image" id="popup_image" accept="image/*">
                        <small class="text-muted">최대 5MB, 지원 형식: JPEG, PNG, JPG, GIF, WEBP</small>
                    </div>
                    
                    <div class="mb-3 d-none" id="popup_html_field">
                        <label for="popup_html_code" class="form-label">HTML 코드</label>
                        <textarea class="form-control" name="html_code" id="popup_html_code" rows="10" placeholder="HTML 코드를 입력하세요."></textarea>
                    </div>
                    
                    <div class="mb-3" id="popup_link_field">
                        <label for="popup_link" class="form-label">링크</label>
                        <input type="url" class="form-control" name="link" id="popup_link" placeholder="https://example.com">
                        <small class="text-muted">이미지 타입일 때만 사용됩니다.</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="open_new_window" id="popup_open_new_window" value="1">
                            <label class="form-check-label" for="popup_open_new_window">
                                새 창으로 띄우기
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="popup_target_type" class="form-label">위치</label>
                        <select class="form-select" name="target_type" id="popup_target_type" required>
                            <option value="all">전체</option>
                            <option value="main">메인</option>
                            <option value="attendance">출첵</option>
                            <option value="point-exchange">{{ $pointExchangeTitle }}</option>
                            <option value="event-application">{{ $eventApplicationTitle }}</option>
                            @foreach($boards as $board)
                                <option value="board_{{ $board->id }}">{{ $board->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">등록</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // 팝업 타입 변경 시 필드 표시/숨김
    $('#popup_type').on('change', function() {
        if ($(this).val() === 'html') {
            $('#popup_image_field').addClass('d-none');
            $('#popup_html_field').removeClass('d-none');
            $('#popup_link_field').addClass('d-none');
            $('#popup_image').removeAttr('required');
            $('#popup_html_code').attr('required', 'required');
        } else {
            $('#popup_image_field').removeClass('d-none');
            $('#popup_html_field').addClass('d-none');
            $('#popup_link_field').removeClass('d-none');
            $('#popup_image').attr('required', 'required');
            $('#popup_html_code').removeAttr('required');
        }
    });
    
    
    // 팝업 추가 버튼 클릭
    $('#addPopupBtn').on('click', function() {
        $('#addPopupModal').modal('show');
    });
    
    // 팝업 추가 폼 제출
    $('#addPopupForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("admin.popups.store", ["site" => $site->slug]) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: function(xhr) {
                var errorMessage = '오류가 발생했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    });
    
    // 팝업 설정 폼 제출 방지 (전체 저장 버튼으로 저장)
    $('#popupSettingsForm').on('submit', function(e) {
        e.preventDefault();
    });
    
    // 전체 저장 버튼 클릭
    $('#saveAllPopupSettingsBtn').on('click', function() {
        var btn = $(this);
        var originalText = btn.html();
        
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>저장 중...');
        
        // 팝업 설정 저장
        var settingsData = {
            display_type: $('#popup_display_type').val(),
            position: $('#popup_position').val(),
            _token: '{{ csrf_token() }}'
        };
        
        $.ajax({
            url: '{{ route("admin.popups.update-settings", ["site" => $site->slug]) }}',
            type: 'POST',
            data: settingsData,
            success: function(response) {
                if (response.success) {
                    // 팝업 리스트의 모든 변경사항 저장
                    saveAllPopupItems(btn, originalText);
                } else {
                    alert('설정 저장 중 오류가 발생했습니다: ' + response.message);
                    btn.prop('disabled', false);
                    btn.html(originalText);
                }
            },
            error: function(xhr) {
                alert('설정 저장 중 오류가 발생했습니다.');
                btn.prop('disabled', false);
                btn.html(originalText);
            }
        });
    });
    
    // 모든 팝업 항목 저장
    function saveAllPopupItems(btn, originalText) {
        var popupData = [];
        
        // 데스크탑 테이블과 모바일 카드 모두에서 데이터 수집
        $('[data-popup-id]').each(function() {
            var popupId = $(this).data('popup-id');
            if (!popupId) return;
            
            var container = $(this);
            var link = container.find('.popup-link-input').val() || '';
            var htmlCodeInput = container.find('.popup-html-input');
            var htmlCode = htmlCodeInput.length > 0 ? htmlCodeInput.val() : null;
            var openNewWindow = container.find('.popup-open-new-window-checkbox').is(':checked') ? '1' : '0';
            var targetType = container.find('.popup-target-type-select').val();
            
            // 이미지 타입 팝업의 경우 html_code를 전송하지 않음
            var data = {
                id: popupId,
                link: link,
                open_new_window: openNewWindow,
                target_type: targetType
            };
            
            // HTML 타입 팝업의 경우에만 html_code 전송
            if (htmlCodeInput.length > 0) {
                data.html_code = htmlCode || '';
            }
            
            popupData.push(data);
        });
        
        if (popupData.length === 0) {
            alert('저장되었습니다.');
            btn.prop('disabled', false);
            btn.html(originalText);
            return;
        }
        
        // 모든 팝업 항목을 한번에 저장
        var savePromises = popupData.map(function(data) {
            return $.ajax({
                url: '{{ route("admin.popups.update-item", ["site" => $site->slug, "popup" => ":popupId"]) }}'.replace(':popupId', data.id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    link: data.link,
                    html_code: data.html_code,
                    open_new_window: data.open_new_window,
                    target_type: data.target_type
                }
            });
        });
        
        Promise.all(savePromises).then(function() {
            alert('모든 설정이 저장되었습니다.');
            btn.prop('disabled', false);
            btn.html(originalText);
        }).catch(function(error) {
            console.error('Popup save error:', error);
            var errorMessage = '일부 항목 저장 중 오류가 발생했습니다.';
            if (error.responseJSON && error.responseJSON.message) {
                errorMessage = '오류: ' + error.responseJSON.message;
            } else if (error.message) {
                errorMessage = '오류: ' + error.message;
            }
            alert(errorMessage);
            btn.prop('disabled', false);
            btn.html(originalText);
        });
    }
    
    // 순서 변경 버튼 (데스크탑 + 모바일)
    $(document).on('click', '.popup-order-up-btn', function() {
        var popupId = $(this).data('popup-id');
        var container = $(this).closest('[data-popup-id]');
        var prevContainer = container.prev('[data-popup-id]');
        
        if (prevContainer.length) {
            var prevPopupId = prevContainer.data('popup-id');
            swapPopupOrder(popupId, prevPopupId);
        }
    });
    
    $(document).on('click', '.popup-order-down-btn', function() {
        var popupId = $(this).data('popup-id');
        var container = $(this).closest('[data-popup-id]');
        var nextContainer = container.next('[data-popup-id]');
        
        if (nextContainer.length) {
            var nextPopupId = nextContainer.data('popup-id');
            swapPopupOrder(popupId, nextPopupId);
        }
    });
    
    function swapPopupOrder(popupId1, popupId2) {
        var popupIds = [];
        // 데스크탑 테이블과 모바일 카드 모두에서 순서 수집
        $('#popupsTableBody tr[data-popup-id], #popupsCardBody .card[data-popup-id]').each(function() {
            var id = $(this).data('popup-id');
            if (id) {
                if (id == popupId1) {
                    popupIds.push(popupId2);
                } else if (id == popupId2) {
                    popupIds.push(popupId1);
                } else {
                    popupIds.push(id);
                }
            }
        });
        
        $.ajax({
            url: '{{ route("admin.popups.update-order", ["site" => $site->slug]) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                popup_ids: popupIds
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: function() {
                alert('오류가 발생했습니다.');
            }
        });
    }
    
    // 위치 타입 변경 (자동 저장 제거, 전체 저장 버튼으로 저장)
    $('.popup-target-type-select').on('change', function() {
        var popupId = $(this).data('popup-id');
        var targetType = $(this).val();
        var boardSelect = $('.popup-target-board-select[data-popup-id="' + popupId + '"]');
        
        if (targetType === 'board') {
            boardSelect.removeClass('d-none').attr('required', 'required');
        } else {
            boardSelect.addClass('d-none').removeAttr('required');
        }
    });
    
    // 게시판 선택 변경 (자동 저장 제거)
    $('.popup-target-board-select').on('change', function() {
        // 자동 저장 제거
    });
    
    // 새 창으로 띄우기 체크박스 변경 (자동 저장 제거)
    $(document).on('change', '.popup-open-new-window-checkbox', function() {
        // 데스크탑과 모바일 체크박스 동기화
        var popupId = $(this).data('popup-id');
        var isChecked = $(this).is(':checked');
        $('.popup-open-new-window-checkbox[data-popup-id="' + popupId + '"]').not(this).prop('checked', isChecked);
    });
    
    // 링크 입력 변경 (자동 저장 제거)
    $('.popup-link-input').on('blur', function() {
        // 자동 저장 제거
    });
    
    // HTML 코드 입력 변경 (자동 저장 제거)
    $('.popup-html-input').on('blur', function() {
        // 자동 저장 제거
    });
    
    // 이미지 클릭 시 파일 선택
    $(document).on('click', '.popup-preview-image', function() {
        var popupId = $(this).data('popup-id');
        $('.popup-image-input[data-popup-id="' + popupId + '"]').click();
    });
    
    // 이미지 placeholder 클릭 시 파일 선택
    $(document).on('click', '.popup-image-placeholder', function() {
        var popupId = $(this).data('popup-id');
        $('.popup-image-input[data-popup-id="' + popupId + '"]').click();
    });
    
    // 이미지 파일 변경
    $('.popup-image-input').on('change', function(e) {
        var popupId = $(this).data('popup-id');
        var file = this.files[0];
        
        if (file) {
            var formData = new FormData();
            formData.append('image', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            $.ajax({
                url: '{{ route("admin.popups.update-item", ["site" => $site->slug, "popup" => ":popupId"]) }}'.replace(':popupId', popupId),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('오류: ' + response.message);
                    }
                },
                error: function() {
                    alert('오류가 발생했습니다.');
                }
            });
        }
    });
    
    // 팝업 항목 업데이트 (데스크탑 + 모바일)
    function updatePopupItem(popupId) {
        var container = $('[data-popup-id="' + popupId + '"]');
        var link = container.find('.popup-link-input').val();
        var htmlCode = container.find('.popup-html-input').val();
        var openNewWindow = container.find('.popup-open-new-window-checkbox').is(':checked') ? '1' : '0';
        var targetType = container.find('.popup-target-type-select').val();
        
        $.ajax({
            url: '{{ route("admin.popups.update-item", ["site" => $site->slug, "popup" => ":popupId"]) }}'.replace(':popupId', popupId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                link: link,
                html_code: htmlCode,
                open_new_window: openNewWindow,
                target_type: targetType
            },
            success: function(response) {
                if (response.success) {
                    // 성공 메시지는 표시하지 않음 (자동 저장)
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: function() {
                alert('오류가 발생했습니다.');
            }
        });
    }
    
    // 팝업 삭제 (데스크탑 + 모바일)
    $(document).on('click', '.delete-popup-btn', function() {
        if (!confirm('정말 삭제하시겠습니까?')) {
            return;
        }
        
        var popupId = $(this).data('popup-id');
        
        $.ajax({
            url: '{{ route("admin.popups.delete", ["site" => $site->slug, "popup" => ":popupId"]) }}'.replace(':popupId', popupId),
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: function() {
                alert('오류가 발생했습니다.');
            }
        });
    });
});
</script>
@endpush

@endsection

