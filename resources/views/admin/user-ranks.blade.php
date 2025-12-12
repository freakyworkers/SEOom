@extends('layouts.admin')

@section('title', '회원등급')
@section('page-title', '회원등급')
@section('page-subtitle', '회원 등급을 관리할 수 있습니다')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>회원 등급 기준</h5>
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="rank_criteria_type" class="form-label">회원 등급 기준</label>
                <select class="form-select" id="rank_criteria_type" name="criteria_type">
                    <option value="current_points" {{ $criteriaType == 'current_points' ? 'selected' : '' }}>현재 포인트</option>
                    <option value="max_points" {{ $criteriaType == 'max_points' ? 'selected' : '' }}>최대 포인트</option>
                    <option value="post_count" {{ $criteriaType == 'post_count' ? 'selected' : '' }}>작성 게시글</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="rank_display_type" class="form-label">색상/아이콘</label>
                <select class="form-select" id="rank_display_type" name="display_type">
                    <option value="icon" {{ $displayType == 'icon' ? 'selected' : '' }}>아이콘</option>
                    <option value="color" {{ $displayType == 'color' ? 'selected' : '' }}>색상</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>관리자/매니저 아이콘</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">관리자 아이콘</label>
                <div class="d-flex align-items-center gap-3">
                    @if($adminIcon)
                        <img src="{{ asset('storage/' . $adminIcon) }}" alt="Admin Icon" style="width: 60px; height: 60px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 8px;">
                    @else
                        <div style="width: 60px; height: 60px; border: 1px solid #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                    @endif
                    <input type="file" class="form-control" id="admin_icon" name="admin_icon" accept="image/*" style="flex: 1;">
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">매니저 아이콘</label>
                <div class="d-flex align-items-center gap-3">
                    @if($managerIcon)
                        <img src="{{ asset('storage/' . $managerIcon) }}" alt="Manager Icon" style="width: 60px; height: 60px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 8px;">
                    @else
                        <div style="width: 60px; height: 60px; border: 1px solid #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                    @endif
                    <input type="file" class="form-control" id="manager_icon" name="manager_icon" accept="image/*" style="flex: 1;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>등급 목록</h5>
    </div>
    <div class="card-body">
        <form id="ranksForm">
            {{-- 데스크탑 버전 (테이블) --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover">
                    <thead>
                            <tr>
                            <th style="width: 80px;">등급</th>
                            <th>이름</th>
                            <th style="width: 150px;">기준</th>
                            <th class="color-header" style="width: 120px; display: {{ $displayType == 'color' ? 'table-cell' : 'none' }};">색상</th>
                            <th class="icon-header" style="width: 120px; display: {{ $displayType == 'icon' ? 'table-cell' : 'none' }};">아이콘</th>
                            <th style="width: 100px;">삭제</th>
                        </tr>
                    </thead>
                    <tbody id="ranksTableBody">
                        @foreach($ranks as $rank)
                            <tr data-rank-id="{{ $rank->id }}">
                                <td>
                                    <input type="number" class="form-control form-control-sm" name="ranks[{{ $rank->id }}][rank]" value="{{ $rank->rank }}" min="1" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="ranks[{{ $rank->id }}][name]" value="{{ $rank->name }}" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" name="ranks[{{ $rank->id }}][criteria_value]" value="{{ $rank->criteria_value }}" min="0" required>
                                </td>
                                <td class="color-cell" style="display: {{ $displayType == 'color' ? 'table-cell' : 'none' }};">
                                    <input type="color" class="form-control form-control-color form-control-sm" name="ranks[{{ $rank->id }}][color]" value="{{ $rank->color ?? '#000000' }}" style="width: 60px; height: 38px;">
                                </td>
                                <td class="icon-cell" style="display: {{ $displayType == 'icon' ? 'table-cell' : 'none' }};">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="file" class="form-control form-control-sm rank-icon-input" name="ranks[{{ $rank->id }}][icon]" accept="image/*" id="icon_{{ $rank->id }}" style="display: none;">
                                        <label for="icon_{{ $rank->id }}" style="cursor: pointer; margin: 0;">
                                            @if($rank->icon_path)
                                                <img src="{{ asset('storage/' . $rank->icon_path) }}" alt="Icon" class="rank-icon-preview" style="width: 40px; height: 40px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 4px;">
                                            @else
                                                <div class="rank-icon-placeholder" style="width: 40px; height: 40px; border: 1px solid #dee2e6; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                    <i class="bi bi-image text-muted" style="font-size: 0.8rem;"></i>
                                                </div>
                                            @endif
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm delete-rank" data-rank-id="{{ $rank->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <input type="hidden" name="ranks[{{ $rank->id }}][id]" value="{{ $rank->id }}">
                            <input type="hidden" name="ranks[{{ $rank->id }}][display_type]" value="{{ $rank->display_type }}">
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 모바일 버전 (카드 레이아웃) --}}
            <div class="d-md-none">
                <div class="d-grid gap-3" id="ranksCardBody">
                    @foreach($ranks as $rank)
                        <div class="card shadow-sm" data-rank-id="{{ $rank->id }}">
                            <div class="card-body">
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold mb-1">등급</label>
                                        <input type="number" 
                                               class="form-control form-control-sm" 
                                               name="ranks[{{ $rank->id }}][rank]" 
                                               value="{{ $rank->rank }}" 
                                               min="1" 
                                               required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold mb-1">기준</label>
                                        <input type="number" 
                                               class="form-control form-control-sm" 
                                               name="ranks[{{ $rank->id }}][criteria_value]" 
                                               value="{{ $rank->criteria_value }}" 
                                               min="0" 
                                               required>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1">이름</label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           name="ranks[{{ $rank->id }}][name]" 
                                           value="{{ $rank->name }}" 
                                           required>
                                </div>
                                <div class="mb-2 color-cell-mobile" style="display: {{ $displayType == 'color' ? 'block' : 'none' }};">
                                    <label class="form-label small fw-bold mb-1">색상</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" 
                                               class="form-control form-control-color form-control-sm rank-color-input" 
                                               name="ranks[{{ $rank->id }}][color]" 
                                               value="{{ $rank->color ?? '#000000' }}" 
                                               id="color_mobile_{{ $rank->id }}"
                                               style="width: 80px; height: 50px; cursor: pointer; border: 2px solid #dee2e6; border-radius: 4px;">
                                        <span class="small text-muted">색상 선택</span>
                                    </div>
                                </div>
                                <div class="mb-2 icon-cell-mobile" style="display: {{ $displayType == 'icon' ? 'block' : 'none' }};">
                                    <label class="form-label small fw-bold mb-1">아이콘</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="file" 
                                               class="form-control form-control-sm rank-icon-input" 
                                               name="ranks[{{ $rank->id }}][icon]" 
                                               accept="image/*" 
                                               id="icon_mobile_{{ $rank->id }}" 
                                               style="display: none;">
                                        <label for="icon_mobile_{{ $rank->id }}" style="cursor: pointer; margin: 0;">
                                            @if($rank->icon_path)
                                                <img src="{{ asset('storage/' . $rank->icon_path) }}" 
                                                     alt="Icon" 
                                                     class="rank-icon-preview" 
                                                     style="width: 50px; height: 50px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 4px;">
                                            @else
                                                <div class="rank-icon-placeholder" 
                                                     style="width: 50px; height: 50px; border: 1px solid #dee2e6; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            @endif
                                        </label>
                                        <span class="small text-muted">아이콘 선택</span>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="button" 
                                            class="btn btn-danger btn-sm delete-rank" 
                                            data-rank-id="{{ $rank->id }}">
                                        <i class="bi bi-trash"></i> 삭제
                                    </button>
                                </div>
                                <input type="hidden" name="ranks[{{ $rank->id }}][id]" value="{{ $rank->id }}">
                                <input type="hidden" name="ranks[{{ $rank->id }}][display_type]" value="{{ $rank->display_type }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-success" id="addRankBtn">
                    <i class="bi bi-plus-circle me-1"></i>추가
                </button>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // display_type 변경 시 테이블 헤더/셀 표시/숨김 (데스크탑 + 모바일)
    function updateDisplayType() {
        const displayType = $('#rank_display_type').val();
        if (displayType === 'color') {
            $('.color-header, .color-cell').show();
            $('.icon-header, .icon-cell').hide();
            $('.color-cell-mobile').show();
            $('.icon-cell-mobile').hide();
        } else {
            $('.icon-header, .icon-cell').show();
            $('.color-header, .color-cell').hide();
            $('.icon-cell-mobile').show();
            $('.color-cell-mobile').hide();
        }
    }

    $('#rank_display_type').on('change', updateDisplayType);
    updateDisplayType();

    // 새 등급 추가 (데스크탑 + 모바일)
    let newRankCounter = 0;
    function addNewRankRow() {
        const displayType = $('#rank_display_type').val();
        const tbody = $('#ranksTableBody');
        const cardBody = $('#ranksCardBody');
        const newId = 'new_' + (++newRankCounter);
        
        let colorCell = '';
        let iconCell = '';
        let colorCellMobile = '';
        let iconCellMobile = '';
        
        if (displayType === 'color') {
            colorCell = '<td class="color-cell"><input type="color" class="form-control form-control-color form-control-sm" name="ranks[' + newId + '][color]" value="#000000" style="width: 60px; height: 38px;"></td>';
            iconCell = '<td class="icon-cell" style="display: none;"></td>';
            colorCellMobile = '<div class="mb-2 color-cell-mobile"><label class="form-label small fw-bold mb-1">색상</label><div class="d-flex align-items-center gap-2"><input type="color" class="form-control form-control-color form-control-sm rank-color-input" name="ranks[' + newId + '][color]" value="#000000" id="color_mobile_' + newId + '" style="width: 80px; height: 50px; cursor: pointer; border: 2px solid #dee2e6; border-radius: 4px;"><span class="small text-muted">색상 선택</span></div></div>';
            iconCellMobile = '<div class="mb-2 icon-cell-mobile" style="display: none;"></div>';
        } else {
            colorCell = '<td class="color-cell" style="display: none;"></td>';
            iconCell = '<td class="icon-cell"><div class="d-flex align-items-center gap-2"><input type="file" class="form-control form-control-sm rank-icon-input" name="ranks[' + newId + '][icon]" accept="image/*" id="icon_' + newId + '" style="display: none;"><label for="icon_' + newId + '" style="cursor: pointer; margin: 0;"><div class="rank-icon-placeholder" style="width: 40px; height: 40px; border: 1px solid #dee2e6; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;"><i class="bi bi-image text-muted" style="font-size: 0.8rem;"></i></div></label></div></td>';
            colorCellMobile = '<div class="mb-2 color-cell-mobile" style="display: none;"></div>';
            iconCellMobile = '<div class="mb-2 icon-cell-mobile"><label class="form-label small fw-bold mb-1">아이콘</label><div class="d-flex align-items-center gap-2"><input type="file" class="form-control form-control-sm rank-icon-input" name="ranks[' + newId + '][icon]" accept="image/*" id="icon_mobile_' + newId + '" style="display: none;"><label for="icon_mobile_' + newId + '" style="cursor: pointer; margin: 0;"><div class="rank-icon-placeholder" style="width: 50px; height: 50px; border: 1px solid #dee2e6; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;"><i class="bi bi-image text-muted"></i></div></label><span class="small text-muted">아이콘 선택</span></div></div>';
        }
        
        // 데스크탑 테이블 행
        const newRow = '<tr data-rank-id="' + newId + '" class="new-rank-row">' +
            '<td><input type="number" class="form-control form-control-sm" name="ranks[' + newId + '][rank]" value="1" min="1" required></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="ranks[' + newId + '][name]" value="" required></td>' +
            '<td><input type="number" class="form-control form-control-sm" name="ranks[' + newId + '][criteria_value]" value="0" min="0" required></td>' +
            colorCell +
            iconCell +
            '<td><button type="button" class="btn btn-danger btn-sm remove-rank-row"><i class="bi bi-trash"></i></button></td>' +
            '</tr>' +
            '<input type="hidden" name="ranks[' + newId + '][id]" value="' + newId + '">' +
            '<input type="hidden" name="ranks[' + newId + '][display_type]" value="' + displayType + '">';
        
        // 모바일 카드
        const newCard = '<div class="card shadow-sm new-rank-card" data-rank-id="' + newId + '">' +
            '<div class="card-body">' +
            '<div class="row g-2 mb-2">' +
            '<div class="col-6"><label class="form-label small fw-bold mb-1">등급</label><input type="number" class="form-control form-control-sm" name="ranks[' + newId + '][rank]" value="1" min="1" required></div>' +
            '<div class="col-6"><label class="form-label small fw-bold mb-1">기준</label><input type="number" class="form-control form-control-sm" name="ranks[' + newId + '][criteria_value]" value="0" min="0" required></div>' +
            '</div>' +
            '<div class="mb-2"><label class="form-label small fw-bold mb-1">이름</label><input type="text" class="form-control form-control-sm" name="ranks[' + newId + '][name]" value="" required></div>' +
            colorCellMobile +
            iconCellMobile +
            '<div class="d-grid"><button type="button" class="btn btn-danger btn-sm remove-rank-row"><i class="bi bi-trash"></i> 삭제</button></div>' +
            '<input type="hidden" name="ranks[' + newId + '][id]" value="' + newId + '">' +
            '<input type="hidden" name="ranks[' + newId + '][display_type]" value="' + displayType + '">' +
            '</div>' +
            '</div>';
        
        tbody.append(newRow);
        if (cardBody.length) {
            cardBody.append(newCard);
        }
    }
    
    $('#addRankBtn').on('click', addNewRankRow);

    // 새로 추가된 행 제거 (데스크탑 + 모바일)
    $(document).on('click', '.remove-rank-row', function() {
        const container = $(this).closest('[data-rank-id]');
        const rankId = container.data('rank-id');
        
        // 데스크탑 테이블 행 제거
        const row = $('#ranksTableBody tr[data-rank-id="' + rankId + '"]');
        if (row.length) {
            row.next('input[type="hidden"][name*="[' + rankId + ']"]').remove();
            row.remove();
        }
        
        // 모바일 카드 제거
        const card = $('#ranksCardBody .card[data-rank-id="' + rankId + '"]');
        if (card.length) {
            card.remove();
        }
    });

    // 아이콘 파일 선택 시 미리보기 업데이트 (데스크탑 + 모바일)
    $(document).on('change', '.rank-icon-input', function() {
        const input = this;
        const file = input.files[0];
        const inputId = $(input).attr('id');
        const rankId = inputId.replace('icon_', '').replace('icon_mobile_', '');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // 데스크탑 미리보기 업데이트
                const desktopLabel = $('#icon_' + rankId).siblings('label');
                if (desktopLabel.length) {
                    const preview = desktopLabel.find('.rank-icon-preview');
                    if (preview.length) {
                        preview.attr('src', e.target.result);
                    } else {
                        desktopLabel.find('.rank-icon-placeholder').replaceWith('<img src="' + e.target.result + '" alt="Icon" class="rank-icon-preview" style="width: 40px; height: 40px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 4px;">');
                    }
                }
                
                // 모바일 미리보기 업데이트
                const mobileLabel = $('#icon_mobile_' + rankId).siblings('label');
                if (mobileLabel.length) {
                    const preview = mobileLabel.find('.rank-icon-preview');
                    if (preview.length) {
                        preview.attr('src', e.target.result);
                    } else {
                        mobileLabel.find('.rank-icon-placeholder').replaceWith('<img src="' + e.target.result + '" alt="Icon" class="rank-icon-preview" style="width: 50px; height: 50px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 4px;">');
                    }
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // 등급 삭제
    $(document).on('click', '.delete-rank', function() {
        if (!confirm('정말 이 등급을 삭제하시겠습니까?')) {
            return;
        }

        const rankId = $(this).data('rank-id');
        
        $.ajax({
            url: '{{ route("admin.user-ranks.delete", ["site" => $site->slug, "userRank" => ":id"]) }}'.replace(':id', rankId),
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert('오류가 발생했습니다.');
            }
        });
    });

    // 전체 저장
    $('#ranksForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        
        // 전역 설정
        formData.append('criteria_type', $('#rank_criteria_type').val());
        formData.append('display_type', $('#rank_display_type').val());
        formData.append('_token', '{{ csrf_token() }}');
        
        // 관리자/매니저 아이콘
        if ($('#admin_icon')[0].files.length > 0) {
            formData.append('admin_icon', $('#admin_icon')[0].files[0]);
        }
        if ($('#manager_icon')[0].files.length > 0) {
            formData.append('manager_icon', $('#manager_icon')[0].files[0]);
        }
        
        // 등급 데이터 수집 (표시된 것만 수집)
        const isMobile = window.innerWidth < 768;
        const ranks = [];
        
        if (isMobile) {
            // 모바일 카드에서 데이터 수집
            $('#ranksCardBody .card[data-rank-id]').each(function() {
                const rankId = $(this).data('rank-id');
                const card = $(this);
                
                formData.append('ranks[' + rankId + '][id]', rankId);
                formData.append('ranks[' + rankId + '][rank]', card.find('input[name*="[rank]"]').val());
                formData.append('ranks[' + rankId + '][name]', card.find('input[name*="[name]"]').val());
                formData.append('ranks[' + rankId + '][criteria_value]', card.find('input[name*="[criteria_value]"]').val());
                formData.append('ranks[' + rankId + '][display_type]', card.find('input[name*="[display_type]"]').val());
                
                // 색상 추가
                const colorInput = card.find('input[name*="[color]"]');
                if (colorInput.length && colorInput.is(':visible')) {
                    formData.append('ranks[' + rankId + '][color]', colorInput.val());
                }
                
                // 아이콘 파일 추가
                const iconInput = card.find('input[type="file"][name*="[icon]"]');
                if (iconInput.length && iconInput.is(':visible') && iconInput[0].files.length > 0) {
                    formData.append('ranks[' + rankId + '][icon]', iconInput[0].files[0]);
                }
            });
        } else {
            // 데스크탑 테이블에서 데이터 수집
            $('#ranksTableBody tr[data-rank-id]').each(function() {
                const rankId = $(this).data('rank-id');
                const row = $(this);
                
                formData.append('ranks[' + rankId + '][id]', rankId);
                formData.append('ranks[' + rankId + '][rank]', row.find('input[name*="[rank]"]').val());
                formData.append('ranks[' + rankId + '][name]', row.find('input[name*="[name]"]').val());
                formData.append('ranks[' + rankId + '][criteria_value]', row.find('input[name*="[criteria_value]"]').val());
                formData.append('ranks[' + rankId + '][display_type]', row.next('input[name*="[display_type]"]').val());
                
                // 색상 추가
                const colorInput = row.find('input[name*="[color]"]');
                if (colorInput.length && colorInput.is(':visible')) {
                    formData.append('ranks[' + rankId + '][color]', colorInput.val());
                }
                
                // 아이콘 파일 추가
                const iconInput = row.find('input[type="file"][name*="[icon]"]');
                if (iconInput.length && iconInput.is(':visible') && iconInput[0].files.length > 0) {
                    formData.append('ranks[' + rankId + '][icon]', iconInput[0].files[0]);
                }
            });
        }

        $.ajax({
            url: '{{ route("admin.user-ranks.update", ["site" => $site->slug]) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('저장되었습니다.');
                    location.reload();
                } else {
                    alert('저장 중 오류가 발생했습니다: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    alert('오류: ' + Object.values(xhr.responseJSON.errors).flat().join(', '));
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert('오류: ' + xhr.responseJSON.message);
                } else {
                    alert('오류가 발생했습니다.');
                }
            }
        });
    });
});
</script>
@endpush
@endsection

