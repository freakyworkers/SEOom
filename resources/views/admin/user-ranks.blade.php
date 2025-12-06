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
            <div class="table-responsive">
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
    // display_type 변경 시 테이블 헤더/셀 표시/숨김
    function updateDisplayType() {
        const displayType = $('#rank_display_type').val();
        if (displayType === 'color') {
            $('.color-header, .color-cell').show();
            $('.icon-header, .icon-cell').hide();
        } else {
            $('.icon-header, .icon-cell').show();
            $('.color-header, .color-cell').hide();
        }
    }

    $('#rank_display_type').on('change', updateDisplayType);
    updateDisplayType();

    // 새 등급 추가
    let newRankCounter = 0;
    function addNewRankRow() {
        const displayType = $('#rank_display_type').val();
        const tbody = $('#ranksTableBody');
        const newId = 'new_' + (++newRankCounter);
        
        let colorCell = '';
        let iconCell = '';
        
        if (displayType === 'color') {
            colorCell = '<td class="color-cell"><input type="color" class="form-control form-control-color form-control-sm" name="ranks[' + newId + '][color]" value="#000000" style="width: 60px; height: 38px;"></td>';
            iconCell = '<td class="icon-cell" style="display: none;"></td>';
        } else {
            colorCell = '<td class="color-cell" style="display: none;"></td>';
            iconCell = '<td class="icon-cell"><div class="d-flex align-items-center gap-2"><input type="file" class="form-control form-control-sm rank-icon-input" name="ranks[' + newId + '][icon]" accept="image/*" id="icon_' + newId + '" style="display: none;"><label for="icon_' + newId + '" style="cursor: pointer; margin: 0;"><div class="rank-icon-placeholder" style="width: 40px; height: 40px; border: 1px solid #dee2e6; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;"><i class="bi bi-image text-muted" style="font-size: 0.8rem;"></i></div></label></div></td>';
        }
        
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
        
        tbody.append(newRow);
    }
    
    $('#addRankBtn').on('click', addNewRankRow);

    // 새로 추가된 행 제거
    $(document).on('click', '.remove-rank-row', function() {
        const row = $(this).closest('tr');
        const rankId = row.data('rank-id');
        row.next('input[type="hidden"][name*="[' + rankId + ']"]').remove();
        row.remove();
    });

    // 아이콘 파일 선택 시 미리보기 업데이트
    $(document).on('change', '.rank-icon-input', function() {
        const input = this;
        const file = input.files[0];
        const label = $(input).siblings('label');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = label.find('.rank-icon-preview');
                if (preview.length) {
                    preview.attr('src', e.target.result);
                } else {
                    label.find('.rank-icon-placeholder').replaceWith('<img src="' + e.target.result + '" alt="Icon" class="rank-icon-preview" style="width: 40px; height: 40px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 4px;">');
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
        
        const formData = new FormData(this);
        formData.append('criteria_type', $('#rank_criteria_type').val());
        formData.append('display_type', $('#rank_display_type').val());
        
        if ($('#admin_icon')[0].files.length > 0) {
            formData.append('admin_icon', $('#admin_icon')[0].files[0]);
        }
        if ($('#manager_icon')[0].files.length > 0) {
            formData.append('manager_icon', $('#manager_icon')[0].files[0]);
        }
        
        formData.append('_token', '{{ csrf_token() }}');

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
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    alert('오류: ' + Object.values(xhr.responseJSON.errors).flat().join(', '));
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

