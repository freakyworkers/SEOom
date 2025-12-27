@extends('layouts.admin')

@section('title', '메인 위젯')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header mb-4">
                <h1 class="h3 mb-2">메인 위젯</h1>
                <p class="text-muted">컨테이너를 추가하고 각 칸에 위젯을 배치하여 메인 페이지를 구성할 수 있습니다</p>
            </div>

            <!-- 컨테이너 추가 및 목록 (같은 행) -->
            <div class="row mb-4">
                <!-- 컨테이너 추가 섹션 (왼쪽) -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">컨테이너 추가</h6>
                        </div>
                        <div class="card-body">
                            <form id="addContainerForm">
                                <div class="mb-3">
                                    <label for="container_columns" class="form-label">가로 개수</label>
                                    <select class="form-select" id="container_columns" name="columns" required>
                                        <option value="1">1개</option>
                                        <option value="2">2개</option>
                                        <option value="3">3개</option>
                                        <option value="4">4개</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="container_vertical_align" class="form-label">정렬</label>
                                    <select class="form-select" id="container_vertical_align" name="vertical_align">
                                        <option value="top">상단</option>
                                        <option value="center">중앙</option>
                                        <option value="bottom">하단</option>
                                    </select>
                                </div>
                                @php
                                    $themeSidebar = $site->getSetting('theme_sidebar', 'left');
                                    $hasSidebar = $themeSidebar !== 'none';
                                @endphp
                                <div class="mb-3">
                                    <div class="form-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" id="container_full_width" name="full_width" value="1" {{ !$hasSidebar ? '' : 'disabled' }}>
                                        <label class="form-check-label" for="container_full_width">
                                            가로 100%
                                        </label>
                                        <i class="bi bi-question-circle text-muted ms-2" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="활성화 시 해당 컨테이너가 브라우저 전체 너비를 사용합니다. 사이드바가 없음으로 설정된 경우에만 사용할 수 있습니다." 
                                           style="cursor: help; font-size: 0.9rem;"></i>
                                    </div>
                                    @if($hasSidebar)
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-info-circle me-1"></i>
                                            사이드바가 없음으로 설정된 경우에만 사용할 수 있습니다.
                                        </small>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <div class="form-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" id="container_full_height" name="full_height" value="1">
                                        <label class="form-check-label" for="container_full_height">
                                            세로 100%
                                        </label>
                                        <i class="bi bi-question-circle text-muted ms-2" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="활성화 시 해당 컨테이너가 브라우저 세로 100% 영역을 사용합니다. 컨테이너 안의 요소들이 전체 높이를 활용할 수 있습니다." 
                                           style="cursor: help; font-size: 0.9rem;"></i>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="container_widget_spacing" class="form-label">위젯 간격</label>
                                    <select class="form-select" id="container_widget_spacing" name="widget_spacing">
                                        <option value="0">없음</option>
                                        <option value="1">매우 좁음</option>
                                        <option value="2">좁음</option>
                                        <option value="3" selected>보통</option>
                                        <option value="4">넓음</option>
                                        <option value="5">매우 넓음</option>
                                    </select>
                                    <small class="text-muted">같은 컨테이너 내 위젯들 사이의 간격을 설정합니다.</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle me-2"></i>컨테이너 추가
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- 위젯 추가 폼 블록 (컨테이너 추가 바로 아래) -->
                    <div id="addWidgetBlock" class="card" style="display: none;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-white">위젯 추가</h6>
                            <button type="button" class="btn btn-sm btn-light" onclick="hideAddWidgetForm()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <form id="addWidgetForm">
                                <input type="hidden" id="widget_container_id" name="container_id">
                                <input type="hidden" id="widget_column_index" name="column_index">
                                
                                @php
                                    $availableTypes = $availableTypes ?? [];
                                @endphp
                                @include('admin.partials.widget-form', ['availableTypes' => $availableTypes, 'site' => $site])
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="hideAddWidgetForm()">취소</button>
                                    <button type="button" class="btn btn-primary" onclick="addMainWidget()">
                                        <i class="bi bi-plus-circle me-2"></i>추가
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 컨테이너 목록 (오른쪽) -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">메인 페이지</h6>
                        </div>
                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                            <div id="containersList">
                                @if($containers->isEmpty())
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        <p>추가된 컨테이너가 없습니다. 왼쪽에서 컨테이너를 추가해주세요.</p>
                                    </div>
                                @else
                                    @foreach($containers as $container)
                                        <div class="card mb-3 container-item" 
                                             data-container-id="{{ $container->id }}"
                                             data-column-merges="{{ json_encode($container->column_merges ?? []) }}">
                                            {{-- 데스크탑 버전 (기존 가로 배치) --}}
                                            <div class="card-header bg-light d-none d-md-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="mb-0 small">컨테이너 가로:</label>
                                                    <select class="form-select form-select-sm" 
                                                            style="width: auto; min-width: 80px;" 
                                                            onchange="updateContainerColumns({{ $container->id }}, this.value)"
                                                            data-container-id="{{ $container->id }}">
                                                        <option value="1" {{ $container->columns == 1 ? 'selected' : '' }}>1</option>
                                                        <option value="2" {{ $container->columns == 2 ? 'selected' : '' }}>2</option>
                                                        <option value="3" {{ $container->columns == 3 ? 'selected' : '' }}>3</option>
                                                        <option value="4" {{ $container->columns == 4 ? 'selected' : '' }}>4</option>
                                                    </select>
                                                    <label class="mb-0 small ms-3">정렬:</label>
                                                    <select class="form-select form-select-sm" 
                                                            style="width: auto; min-width: 100px;" 
                                                            onchange="updateContainerVerticalAlign({{ $container->id }}, this.value)"
                                                            data-container-id="{{ $container->id }}">
                                                        <option value="top" {{ ($container->vertical_align ?? 'top') == 'top' ? 'selected' : '' }}>상단</option>
                                                        <option value="center" {{ ($container->vertical_align ?? 'top') == 'center' ? 'selected' : '' }}>중앙</option>
                                                        <option value="bottom" {{ ($container->vertical_align ?? 'top') == 'bottom' ? 'selected' : '' }}>하단</option>
                                                    </select>
                                                    @php
                                                        $themeSidebar = $site->getSetting('theme_sidebar', 'left');
                                                        $hasSidebar = $themeSidebar !== 'none';
                                                    @endphp
                                                    <div class="form-check ms-3 d-flex align-items-center">
                                                        <input class="form-check-input container-full-width-checkbox" type="checkbox" 
                                                               id="container_full_width_{{ $container->id }}" 
                                                               @if($container->full_width) checked @endif
                                                               @if(!$hasSidebar) data-container-id="{{ $container->id }}" @else disabled @endif>
                                                        <label class="form-check-label small mb-0" for="container_full_width_{{ $container->id }}">
                                                            가로 100%
                                                        </label>
                                                        <i class="bi bi-question-circle text-muted ms-1" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top" 
                                                           title="활성화 시 해당 컨테이너가 브라우저 전체 너비를 사용합니다. 사이드바가 없음으로 설정된 경우에만 사용할 수 있습니다." 
                                                           style="cursor: help; font-size: 0.85rem;"></i>
                                                    </div>
                                                    <div class="form-check ms-3 d-flex align-items-center">
                                                        <input class="form-check-input container-full-height-checkbox" type="checkbox" 
                                                               id="container_full_height_{{ $container->id }}" 
                                                               @if($container->full_height) checked @endif
                                                               data-container-id="{{ $container->id }}">
                                                        <label class="form-check-label small mb-0" for="container_full_height_{{ $container->id }}">
                                                            세로 100%
                                                        </label>
                                                        <i class="bi bi-question-circle text-muted ms-1" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top" 
                                                           title="활성화 시 해당 컨테이너가 브라우저 세로 100% 영역을 사용합니다." 
                                                           style="cursor: help; font-size: 0.85rem;"></i>
                                                    </div>
                                                    <label class="mb-0 small ms-3">위젯 간격:</label>
                                                    <select class="form-select form-select-sm" 
                                                            style="width: auto; min-width: 100px;" 
                                                            onchange="updateContainerWidgetSpacing({{ $container->id }}, this.value)"
                                                            data-container-id="{{ $container->id }}">
                                                        <option value="0" {{ ($container->widget_spacing ?? 3) == 0 ? 'selected' : '' }}>없음</option>
                                                        <option value="1" {{ ($container->widget_spacing ?? 3) == 1 ? 'selected' : '' }}>매우 좁음</option>
                                                        <option value="2" {{ ($container->widget_spacing ?? 3) == 2 ? 'selected' : '' }}>좁음</option>
                                                        <option value="3" {{ ($container->widget_spacing ?? 3) == 3 ? 'selected' : '' }}>보통</option>
                                                        <option value="4" {{ ($container->widget_spacing ?? 3) == 4 ? 'selected' : '' }}>넓음</option>
                                                        <option value="5" {{ ($container->widget_spacing ?? 3) == 5 ? 'selected' : '' }}>매우 넓음</option>
                                                    </select>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-secondary" 
                                                            onclick="moveContainerUp({{ $container->id }})"
                                                            title="위로 이동">
                                                        <i class="bi bi-arrow-up"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-secondary" 
                                                            onclick="moveContainerDown({{ $container->id }})"
                                                            title="아래로 이동">
                                                        <i class="bi bi-arrow-down"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteContainer({{ $container->id }})">
                                                        <i class="bi bi-trash"></i> 삭제
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- 모바일 버전 (세로 배치) --}}
                                            <div class="card-header bg-light d-md-none">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 small">컨테이너 설정</h6>
                                                    <div class="d-flex gap-1">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-secondary" 
                                                                onclick="moveContainerUp({{ $container->id }})"
                                                                title="위로 이동">
                                                            <i class="bi bi-arrow-up"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-secondary" 
                                                                onclick="moveContainerDown({{ $container->id }})"
                                                                title="아래로 이동">
                                                            <i class="bi bi-arrow-down"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteContainer({{ $container->id }})">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">컨테이너 가로</label>
                                                        <select class="form-select form-select-sm" 
                                                                onchange="updateContainerColumns({{ $container->id }}, this.value)"
                                                                data-container-id="{{ $container->id }}">
                                                            <option value="1" {{ $container->columns == 1 ? 'selected' : '' }}>1</option>
                                                            <option value="2" {{ $container->columns == 2 ? 'selected' : '' }}>2</option>
                                                            <option value="3" {{ $container->columns == 3 ? 'selected' : '' }}>3</option>
                                                            <option value="4" {{ $container->columns == 4 ? 'selected' : '' }}>4</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">정렬</label>
                                                        <select class="form-select form-select-sm" 
                                                                onchange="updateContainerVerticalAlign({{ $container->id }}, this.value)"
                                                                data-container-id="{{ $container->id }}">
                                                            <option value="top" {{ ($container->vertical_align ?? 'top') == 'top' ? 'selected' : '' }}>상단</option>
                                                            <option value="center" {{ ($container->vertical_align ?? 'top') == 'center' ? 'selected' : '' }}>중앙</option>
                                                            <option value="bottom" {{ ($container->vertical_align ?? 'top') == 'bottom' ? 'selected' : '' }}>하단</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check d-flex align-items-center pt-3">
                                                            <input class="form-check-input container-full-width-checkbox" type="checkbox" 
                                                                   id="container_full_width_mobile_{{ $container->id }}" 
                                                                   @if($container->full_width) checked @endif
                                                                   @if(!$hasSidebar) data-container-id="{{ $container->id }}" @else disabled @endif>
                                                            <label class="form-check-label small mb-0 ms-2" for="container_full_width_mobile_{{ $container->id }}">
                                                                가로 100%
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check d-flex align-items-center pt-3">
                                                            <input class="form-check-input container-full-height-checkbox" type="checkbox" 
                                                                   id="container_full_height_mobile_{{ $container->id }}" 
                                                                   @if($container->full_height) checked @endif
                                                                   data-container-id="{{ $container->id }}">
                                                            <label class="form-check-label small mb-0 ms-2" for="container_full_height_mobile_{{ $container->id }}">
                                                                세로 100%
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small mb-1">위젯 간격</label>
                                                        <select class="form-select form-select-sm" 
                                                                onchange="updateContainerWidgetSpacing({{ $container->id }}, this.value)"
                                                                data-container-id="{{ $container->id }}">
                                                            <option value="0" {{ ($container->widget_spacing ?? 3) == 0 ? 'selected' : '' }}>없음</option>
                                                            <option value="1" {{ ($container->widget_spacing ?? 3) == 1 ? 'selected' : '' }}>매우 좁음</option>
                                                            <option value="2" {{ ($container->widget_spacing ?? 3) == 2 ? 'selected' : '' }}>좁음</option>
                                                            <option value="3" {{ ($container->widget_spacing ?? 3) == 3 ? 'selected' : '' }}>보통</option>
                                                            <option value="4" {{ ($container->widget_spacing ?? 3) == 4 ? 'selected' : '' }}>넓음</option>
                                                            <option value="5" {{ ($container->widget_spacing ?? 3) == 5 ? 'selected' : '' }}>매우 넓음</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    @php
                                                        $columnMerges = $container->column_merges ?? [];
                                                        $hiddenColumns = [];
                                                        foreach ($columnMerges as $startCol => $span) {
                                                            for ($j = 1; $j < $span; $j++) {
                                                                $hiddenColumns[] = $startCol + $j;
                                                            }
                                                        }
                                                    @endphp
                                                    @for($i = 0; $i < $container->columns; $i++)
                                                        @php
                                                            $isHidden = in_array($i, $hiddenColumns);
                                                            $mergeSpan = $columnMerges[$i] ?? 1;
                                                            $colWidth = $mergeSpan * (12 / $container->columns);
                                                            // 병합되지 않은 칸에서 다음 칸 병합 가능 여부
                                                            $canMerge = ($i < $container->columns - 1) && !$isHidden && !isset($columnMerges[$i]);
                                                            // 병합된 칸에서도 다음 칸 병합 가능 여부 (병합 범위 끝이 전체 칸 수보다 작으면)
                                                            $canMergeNext = false;
                                                            if (isset($columnMerges[$i]) && $mergeSpan > 1) {
                                                                $mergeEnd = $i + $mergeSpan - 1; // 병합 범위의 마지막 칸 인덱스
                                                                $canMergeNext = ($mergeEnd < $container->columns - 1) && !$isHidden;
                                                            }
                                                            $isMerged = isset($columnMerges[$i]) && $mergeSpan > 1;
                                                            $columnLabel = '칸 ' . ($i + 1);
                                                            if ($isMerged) {
                                                                if ($mergeSpan == 2) {
                                                                    $columnLabel = '칸 ' . ($i + 1) . '/' . ($i + 2);
                                                                } else {
                                                                    $columnLabel = '칸 ' . ($i + 1) . '-' . ($i + $mergeSpan);
                                                                }
                                                            }
                                                        @endphp
                                                        @if(!$isHidden)
                                                            <div class="col-md-{{ $colWidth }} column-cell" data-column-index="{{ $i }}" data-merge-span="{{ $mergeSpan }}">
                                                                <div class="border rounded p-3" style="min-height: 200px; background-color: #f8f9fa;">
                                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                                        <small class="text-muted">{{ $columnLabel }}</small>
                                                                        <div class="d-flex gap-1">
                                                                            @if($canMerge)
                                                                                <button type="button" 
                                                                                        class="btn btn-sm btn-outline-secondary" 
                                                                                        onclick="mergeNextColumn({{ $container->id }}, {{ $i }})"
                                                                                        title="다음 칸과 병합">
                                                                                    <i class="bi bi-arrow-right-circle"></i> 다음칸병합
                                                                                </button>
                                                                            @endif
                                                                            @if($isMerged)
                                                                                <button type="button" 
                                                                                        class="btn btn-sm btn-outline-warning" 
                                                                                        onclick="unmergeColumn({{ $container->id }}, {{ $i }})"
                                                                                        title="병합 해제">
                                                                                    <i class="bi bi-x-circle"></i> 병합해제
                                                                                </button>
                                                                            @endif
                                                                            @if($canMergeNext)
                                                                                <button type="button" 
                                                                                        class="btn btn-sm btn-outline-secondary" 
                                                                                        onclick="mergeNextColumn({{ $container->id }}, {{ $i }})"
                                                                                        title="다음 칸과 병합">
                                                                                    <i class="bi bi-arrow-right-circle"></i> 다음칸병합
                                                                                </button>
                                                                            @endif
                                                                            <button type="button" 
                                                                                    class="btn btn-sm btn-outline-primary" 
                                                                                    onclick="showAddWidgetForm({{ $container->id }}, {{ $i }})">
                                                                                <i class="bi bi-plus-circle"></i> 위젯 추가
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                <div class="widget-list-in-column" data-container-id="{{ $container->id }}" data-column-index="{{ $i }}">
                                                                    @php
                                                                        $columnWidgets = $container->widgets->where('column_index', $i)->sortBy('order');
                                                                    @endphp
                                                                    @if($columnWidgets->isEmpty())
                                                                        <div class="text-center py-3 text-muted">
                                                                            <small>위젯이 없습니다</small>
                                                                        </div>
                                                                    @else
                                                                        @foreach($columnWidgets as $widget)
                                                                            <div class="widget-item card mb-2" 
                                                                                 data-widget-id="{{ $widget->id }}" 
                                                                                 data-widget-title="{{ $widget->title }}"
                                                                                 data-widget-type="{{ $widget->type }}"
                                                                                 data-widget-active="{{ $widget->is_active ? '1' : '0' }}"
                                                                                 data-widget-settings="{{ json_encode($widget->settings ?? []) }}">
                                                                                {{-- 데스크탑 버전 (기존 가로 배치) --}}
                                                                                <div class="card-body p-2 d-none d-md-block">
                                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                                        <div>
                                                                                            <h6 class="mb-0 small">
                                                                                                {{ $widget->title }}
                                                                                                @if(!$widget->is_active)
                                                                                                    <span class="badge bg-secondary ms-1">비활성</span>
                                                                                                @endif
                                                                                            </h6>
                                                                                            <small class="text-muted">{{ $availableTypes[$widget->type] ?? $widget->type }}</small>
                                                                                        </div>
                                                                                        <div class="d-flex gap-1">
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-secondary p-1" 
                                                                                                    onclick="moveMainWidgetUp({{ $widget->id }}, {{ $container->id }}, {{ $i }})"
                                                                                                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                                                    title="위로 이동">
                                                                                                <i class="bi bi-arrow-up" style="font-size: 12px;"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-secondary p-1" 
                                                                                                    onclick="moveMainWidgetDown({{ $widget->id }}, {{ $container->id }}, {{ $i }})"
                                                                                                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                                                    title="아래로 이동">
                                                                                                <i class="bi bi-arrow-down" style="font-size: 12px;"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-info p-1" 
                                                                                                    onclick="openMainWidgetAnimationModal({{ $widget->id }})"
                                                                                                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                                                    title="애니메이션 설정">
                                                                                                <i class="bi bi-magic" style="font-size: 12px;"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-primary p-1" 
                                                                                                    onclick="editMainWidget({{ $widget->id }})"
                                                                                                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                                                    title="설정">
                                                                                                <i class="bi bi-gear" style="font-size: 12px;"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-danger p-1" 
                                                                                                    onclick="deleteMainWidget({{ $widget->id }})"
                                                                                                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                                                    title="삭제">
                                                                                                <i class="bi bi-trash" style="font-size: 12px;"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                {{-- 모바일 버전 (세로 배치) --}}
                                                                                <div class="card-body p-3 d-md-none">
                                                                                    <div class="mb-2">
                                                                                        <h6 class="mb-1">
                                                                                            {{ $widget->title }}
                                                                                            @if(!$widget->is_active)
                                                                                                <span class="badge bg-secondary ms-1">비활성</span>
                                                                                            @endif
                                                                                        </h6>
                                                                                        <small class="text-muted d-block">{{ $availableTypes[$widget->type] ?? $widget->type }}</small>
                                                                                    </div>
                                                                                    <div class="d-flex gap-2 justify-content-end pt-2 border-top">
                                                                                        <button type="button" 
                                                                                                class="btn btn-sm btn-outline-secondary" 
                                                                                                onclick="moveMainWidgetUp({{ $widget->id }}, {{ $container->id }}, {{ $i }})"
                                                                                                title="위로 이동"
                                                                                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                            <i class="bi bi-arrow-up"></i>
                                                                                        </button>
                                                                                        <button type="button" 
                                                                                                class="btn btn-sm btn-outline-secondary" 
                                                                                                onclick="moveMainWidgetDown({{ $widget->id }}, {{ $container->id }}, {{ $i }})"
                                                                                                title="아래로 이동"
                                                                                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                            <i class="bi bi-arrow-down"></i>
                                                                                        </button>
                                                                                        <button type="button" 
                                                                                                class="btn btn-sm btn-outline-info" 
                                                                                                onclick="openMainWidgetAnimationModal({{ $widget->id }})"
                                                                                                title="애니메이션 설정"
                                                                                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                            <i class="bi bi-magic"></i>
                                                                                        </button>
                                                                                        <button type="button" 
                                                                                                class="btn btn-sm btn-outline-primary" 
                                                                                                onclick="editMainWidget({{ $widget->id }})"
                                                                                                title="설정"
                                                                                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                            <i class="bi bi-gear"></i>
                                                                                        </button>
                                                                                        <button type="button" 
                                                                                                class="btn btn-sm btn-outline-danger" 
                                                                                                onclick="deleteMainWidget({{ $widget->id }})"
                                                                                                title="삭제"
                                                                                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                            <i class="bi bi-trash"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="button" class="btn btn-primary" id="edit_main_widget_save_btn" onclick="saveAllMainWidgets()">
                    <i class="bi bi-save me-2"></i>저장
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 저장 완료 알림 모달 -->
<div class="modal fade" id="saveSuccessModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">저장 완료</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-0">위젯 설정이 저장되었습니다.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>

<!-- 위젯 애니메이션 설정 모달 -->
<div class="modal fade" id="mainWidgetAnimationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">위젯 애니메이션 설정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="mainWidgetAnimationForm">
                    <input type="hidden" id="main_widget_animation_id" name="widget_id">
                    <div class="mb-3">
                        <label class="form-label">
                            애니메이션 방향
                            <i class="bi bi-question-circle text-muted ms-1" 
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="위젯이 화면에 나타날 때의 애니메이션 방향을 선택합니다. 스크롤하거나 페이지를 새로고침할 때 적용됩니다."></i>
                        </label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="left"
                                    onclick="selectAnimationDirection('left', this)">
                                <i class="bi bi-arrow-left"></i> 좌
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="right"
                                    onclick="selectAnimationDirection('right', this)">
                                <i class="bi bi-arrow-right"></i> 우
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="up"
                                    onclick="selectAnimationDirection('up', this)">
                                <i class="bi bi-arrow-up"></i> 상
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="down"
                                    onclick="selectAnimationDirection('down', this)">
                                <i class="bi bi-arrow-down"></i> 하
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary animation-direction-btn" 
                                    data-direction="none"
                                    onclick="selectAnimationDirection('none', this)">
                                없음
                            </button>
                        </div>
                        <input type="hidden" id="main_widget_animation_direction" name="animation_direction" value="none">
                    </div>
                    <div class="mb-3">
                        <label for="main_widget_animation_delay" class="form-label">
                            애니메이션 지연 시간 (초)
                            <i class="bi bi-question-circle text-muted ms-1" 
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="애니메이션이 시작되기 전 대기 시간을 초 단위로 설정합니다. 예: 0.5초, 1초, 1.5초 등"></i>
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="main_widget_animation_delay" 
                               name="animation_delay" 
                               value="0" 
                               min="0" 
                               step="0.1"
                               placeholder="0">
                        <small class="text-muted">0 이상의 숫자를 입력하세요 (예: 0, 0.5, 1, 1.5)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveMainWidgetAnimation()">저장</button>
            </div>
        </div>
    </div>
</div>

<!-- 위젯 설정 모달 -->
<div class="modal fade" id="mainWidgetSettingsModal" tabindex="-1" data-update-route="{{ $site->isMasterSite() ? route('master.admin.main-widgets.update', ['widget' => ':id']) : route('admin.main-widgets.update', ['site' => $site->slug, 'widget' => ':id']) }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">위젯 설정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editMainWidgetForm">
                    <input type="hidden" id="edit_main_widget_id" name="id">
                    <div class="mb-3" id="edit_main_widget_board_container" style="display: none;">
                        <label for="edit_main_widget_board_id" class="form-label">게시판 선택</label>
                        <select class="form-select" id="edit_main_widget_board_id" name="board_id">
                            <option value="">선택하세요</option>
                            @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                                <option value="{{ $board->id }}">{{ $board->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="edit_main_widget_sort_order_container" style="display: none;">
                        <label for="edit_main_widget_sort_order" class="form-label">표시 방식</label>
                        <select class="form-select" id="edit_main_widget_sort_order" name="sort_order">
                            <option value="latest">최신순</option>
                            <option value="oldest">예전순</option>
                            <option value="random">랜덤</option>
                            <option value="popular">인기순</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_main_widget_marquee_direction_container" style="display: none;">
                        <label class="form-label">전광판 표시 방향</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="edit_main_direction" id="edit_main_direction_left" value="left">
                            <label class="btn btn-outline-primary" for="edit_main_direction_left">
                                <i class="bi bi-arrow-left"></i> 좌
                            </label>
                            <input type="radio" class="btn-check" name="edit_main_direction" id="edit_main_direction_right" value="right">
                            <label class="btn btn-outline-primary" for="edit_main_direction_right">
                                <i class="bi bi-arrow-right"></i> 우
                            </label>
                            <input type="radio" class="btn-check" name="edit_main_direction" id="edit_main_direction_up" value="up">
                            <label class="btn btn-outline-primary" for="edit_main_direction_up">
                                <i class="bi bi-arrow-up"></i> 상
                            </label>
                            <input type="radio" class="btn-check" name="edit_main_direction" id="edit_main_direction_down" value="down">
                            <label class="btn btn-outline-primary" for="edit_main_direction_down">
                                <i class="bi bi-arrow-down"></i> 하
                            </label>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_main_widget_gallery_container" style="display: none;">
                        <label for="edit_main_widget_gallery_board_id" class="form-label">
                            게시판 선택
                            <i class="bi bi-question-circle text-muted ms-1" 
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="사진형 게시판, 북마크 게시판, 블로그 게시판만 선택 가능합니다."></i>
                        </label>
                        <select class="form-select" id="edit_main_widget_gallery_board_id" name="gallery_board_id">
                            <option value="">선택하세요</option>
                            @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                                @if(in_array($board->type, ['photo', 'bookmark', 'blog']))
                                    <option value="{{ $board->id }}">{{ $board->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="edit_main_widget_gallery_display_type_container" style="display: none;">
                        <label for="edit_main_widget_gallery_display_type" class="form-label">표시 방식</label>
                        <select class="form-select" id="edit_main_widget_gallery_display_type" name="gallery_display_type" onchange="handleEditMainGalleryDisplayTypeChange()">
                            <option value="grid">일반</option>
                            <option value="slide">슬라이드</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_main_widget_gallery_grid_container" style="display: none;">
                        <div class="row">
                            <div class="col-6">
                                <label for="edit_main_widget_gallery_cols" class="form-label">가로 개수</label>
                                <select class="form-select" id="edit_main_widget_gallery_cols" name="gallery_cols">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="edit_main_widget_gallery_rows" class="form-label">세로 줄수</label>
                                <select class="form-select" id="edit_main_widget_gallery_rows" name="gallery_rows">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_main_widget_gallery_slide_container" style="display: none;">
                        <div class="mb-2">
                            <label for="edit_main_widget_gallery_slide_cols" class="form-label">가로 개수</label>
                            <select class="form-select" id="edit_main_widget_gallery_slide_cols" name="gallery_slide_cols">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">슬라이드 방향</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="edit_main_gallery_slide_direction" id="edit_main_gallery_direction_left" value="left">
                                <label class="btn btn-outline-primary" for="edit_main_gallery_direction_left">
                                    <i class="bi bi-arrow-left"></i> 좌
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_gallery_slide_direction" id="edit_main_gallery_direction_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_main_gallery_direction_right">
                                    <i class="bi bi-arrow-right"></i> 우
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_gallery_slide_direction" id="edit_main_gallery_direction_up" value="up">
                                <label class="btn btn-outline-primary" for="edit_main_gallery_direction_up">
                                    <i class="bi bi-arrow-up"></i> 상
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_gallery_slide_direction" id="edit_main_gallery_direction_down" value="down">
                                <label class="btn btn-outline-primary" for="edit_main_gallery_direction_down">
                                    <i class="bi bi-arrow-down"></i> 하
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_main_widget_gallery_show_title_container" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="edit_main_widget_gallery_show_title" 
                                   name="gallery_show_title">
                            <label class="form-check-label" for="edit_main_widget_gallery_show_title">
                                제목 표시
                            </label>
                        </div>
                        <small class="text-muted">썸네일 이미지 하단에 게시글 제목을 표시합니다.</small>
                    </div>
                    <div class="mb-3" id="edit_main_widget_custom_html_container" style="display: none;">
                        <label for="edit_main_widget_custom_html" class="form-label">HTML 코드</label>
                        <textarea class="form-control" 
                                  id="edit_main_widget_custom_html" 
                                  name="custom_html" 
                                  rows="10"
                                  placeholder="HTML 코드를 입력하세요"></textarea>
                        <small class="text-muted">메인 페이지에 표시할 HTML 코드를 입력하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_main_widget_block_container" style="display: none;">
                        <div class="mb-3">
                            <label for="edit_main_widget_block_title" class="form-label">제목</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_main_widget_block_title" 
                                   name="block_title" 
                                   placeholder="제목을 입력하세요">
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_content" class="form-label">내용</label>
                            <textarea class="form-control" 
                                      id="edit_main_widget_block_content" 
                                      name="block_content" 
                                      rows="3"
                                      placeholder="내용을 입력하세요"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">텍스트 정렬</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="edit_main_block_text_align" id="edit_main_block_align_left" value="left" checked>
                                <label class="btn btn-outline-primary" for="edit_main_block_align_left">
                                    <i class="bi bi-text-left"></i> 좌
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_block_text_align" id="edit_main_block_align_center" value="center">
                                <label class="btn btn-outline-primary" for="edit_main_block_align_center">
                                    <i class="bi bi-text-center"></i> 중앙
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_block_text_align" id="edit_main_block_align_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_main_block_align_right">
                                    <i class="bi bi-text-right"></i> 우
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_title_font_size" class="form-label">제목 폰트 사이즈 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_block_title_font_size" 
                                   name="block_title_font_size" 
                                   value="16"
                                   min="8"
                                   max="72"
                                   step="1"
                                   placeholder="16">
                            <small class="text-muted">기본값: 16px</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_content_font_size" class="form-label">내용 폰트 사이즈 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_block_content_font_size" 
                                   name="block_content_font_size" 
                                   value="14"
                                   min="8"
                                   max="48"
                                   step="1"
                                   placeholder="14">
                            <small class="text-muted">기본값: 14px</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_background_type" class="form-label">배경</label>
                            <select class="form-select" id="edit_main_widget_block_background_type" name="block_background_type" onchange="handleEditMainBlockBackgroundTypeChange()">
                                <option value="color">컬러</option>
                                <option value="image">이미지</option>
                            </select>
                        </div>
                        <div class="mb-3" id="edit_main_widget_block_color_container">
                            <label for="edit_main_widget_block_background_color" class="form-label">적용 컬러</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_main_widget_block_background_color" 
                                   name="block_background_color" 
                                   value="#007bff">
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_font_color" class="form-label">폰트 컬러</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_main_widget_block_font_color" 
                                   name="block_font_color" 
                                   value="#ffffff">
                        </div>
                        <div class="mb-3" id="edit_main_widget_block_image_container" style="display: none;">
                            <label class="form-label">배경 이미지</label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="edit_main_widget_block_image_btn"
                                        onclick="document.getElementById('edit_main_widget_block_image_input').click()">
                                    <i class="bi bi-image"></i> 이미지 선택
                                </button>
                                <input type="file" 
                                       id="edit_main_widget_block_image_input" 
                                       name="block_background_image_file" 
                                       accept="image/*" 
                                       style="display: none;"
                                       onchange="handleEditMainBlockImageChange(this)">
                                <input type="hidden" id="edit_main_widget_block_background_image" name="block_background_image_url">
                                <div id="edit_main_widget_block_image_preview" style="display: none;">
                                    <img id="edit_main_widget_block_image_preview_img" src="" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditMainBlockImage()">삭제</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_padding_top" class="form-label">상단 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_block_padding_top" 
                                   name="block_padding_top" 
                                   value="20"
                                   min="0"
                                   max="200"
                                   step="1"
                                   placeholder="20">
                            <small class="text-muted">블록 상단 여백을 입력하세요 (0~200).</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_padding_bottom" class="form-label">하단 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_block_padding_bottom" 
                                   name="block_padding_bottom" 
                                   value="20"
                                   min="0"
                                   max="200"
                                   step="1"
                                   placeholder="20">
                            <small class="text-muted">블록 하단 여백을 입력하세요 (0~200).</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_padding_left" class="form-label">좌측 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_block_padding_left" 
                                   name="block_padding_left" 
                                   value="20"
                                   min="0"
                                   max="200"
                                   step="1"
                                   placeholder="20">
                            <small class="text-muted">블록 좌측 여백을 입력하세요 (0~200).</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_padding_right" class="form-label">우측 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_block_padding_right" 
                                   name="block_padding_right" 
                                   value="20"
                                   min="0"
                                   max="200"
                                   step="1"
                                   placeholder="20">
                            <small class="text-muted">블록 우측 여백을 입력하세요 (0~200).</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_title_content_gap" class="form-label">제목-내용 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_block_title_content_gap" 
                                   name="block_title_content_gap" 
                                   value="8"
                                   min="0"
                                   max="100"
                                   step="1"
                                   placeholder="8">
                            <small class="text-muted">제목과 내용 사이의 여백을 입력하세요 (0~100).</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">버튼 관리</label>
                            <div id="edit_main_widget_block_buttons_list">
                                <!-- 버튼들이 여기에 동적으로 추가됨 -->
                            </div>
                            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addEditMainBlockButton()">
                                <i class="bi bi-plus-circle me-1"></i>버튼 추가
                            </button>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_block_button_top_margin" class="form-label">버튼 상단 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_block_button_top_margin" 
                                   name="block_button_top_margin" 
                                   value="12"
                                   min="0"
                                   max="100"
                                   step="1"
                                   placeholder="12">
                            <small class="text-muted">버튼과 위 요소 사이의 여백을 입력하세요 (0~100).</small>
                        </div>
                        <div class="mb-3" id="edit_main_widget_block_link_container">
                            <label for="edit_main_widget_block_link" class="form-label">
                                연결 링크 <small class="text-muted">(선택사항)</small>
                                <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="버튼이 있는 경우 버튼에 링크가 연결되고, 버튼이 없는 경우 블록 전체에 링크가 연결됩니다."></i>
                            </label>
                            <input type="url" 
                                   class="form-control" 
                                   id="edit_main_widget_block_link" 
                                   name="block_link" 
                                   placeholder="https://example.com">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_main_widget_block_open_new_tab" 
                                       name="block_open_new_tab">
                                <label class="form-check-label" for="edit_main_widget_block_open_new_tab">
                                    새창에서 열기
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_main_widget_block_slide_container" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">슬라이드 방향</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="edit_main_block_slide_direction" id="edit_main_block_slide_direction_left" value="left" checked>
                                <label class="btn btn-outline-primary" for="edit_main_block_slide_direction_left">
                                    <i class="bi bi-arrow-left"></i> 좌
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_block_slide_direction" id="edit_main_block_slide_direction_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_main_block_slide_direction_right">
                                    <i class="bi bi-arrow-right"></i> 우
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_block_slide_direction" id="edit_main_block_slide_direction_up" value="up">
                                <label class="btn btn-outline-primary" for="edit_main_block_slide_direction_up">
                                    <i class="bi bi-arrow-up"></i> 상
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_block_slide_direction" id="edit_main_block_slide_direction_down" value="down">
                                <label class="btn btn-outline-primary" for="edit_main_block_slide_direction_down">
                                    <i class="bi bi-arrow-down"></i> 하
                                </label>
                            </div>
                        </div>
                        <div id="edit_main_widget_block_slide_items">
                            <!-- 블록 아이템들이 여기에 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="addEditMainBlockSlideItem()">
                            <i class="bi bi-plus-circle me-2"></i>블록 추가하기
                        </button>
                    </div>
                    <div class="mb-3" id="edit_main_widget_image_container" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">이미지 선택</label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="edit_main_widget_image_btn"
                                        onclick="document.getElementById('edit_main_widget_image_input').click()">
                                    <i class="bi bi-image"></i> 이미지 선택
                                </button>
                                <input type="file" 
                                       id="edit_main_widget_image_input" 
                                       name="image_file" 
                                       accept="image/*" 
                                       style="display: none;"
                                       onchange="handleEditMainImageChange(this)">
                                <input type="hidden" id="edit_main_widget_image_url" name="image_url">
                                <div id="edit_main_widget_image_preview" style="display: none;">
                                    <img id="edit_main_widget_image_preview_img" src="" alt="미리보기" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditMainImage()">삭제</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_image_link" class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
                            <input type="url" 
                                   class="form-control" 
                                   id="edit_main_widget_image_link" 
                                   name="image_link" 
                                   placeholder="https://example.com">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_main_widget_image_open_new_tab" 
                                       name="image_open_new_tab">
                                <label class="form-check-label" for="edit_main_widget_image_open_new_tab">
                                    새창에서 열기
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_main_widget_image_slide_container" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">슬라이드 방향</label>
                            <div class="btn-group w-100" role="group" id="edit_main_image_slide_direction_group">
                                <input type="radio" class="btn-check" name="edit_main_image_slide_direction" id="edit_main_image_slide_direction_left" value="left" checked>
                                <label class="btn btn-outline-primary" for="edit_main_image_slide_direction_left">
                                    <i class="bi bi-arrow-left"></i> 좌
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_image_slide_direction" id="edit_main_image_slide_direction_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_main_image_slide_direction_right">
                                    <i class="bi bi-arrow-right"></i> 우
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_image_slide_direction" id="edit_main_image_slide_direction_up" value="up">
                                <label class="btn btn-outline-primary" for="edit_main_image_slide_direction_up">
                                    <i class="bi bi-arrow-up"></i> 상
                                </label>
                                <input type="radio" class="btn-check" name="edit_main_image_slide_direction" id="edit_main_image_slide_direction_down" value="down">
                                <label class="btn btn-outline-primary" for="edit_main_image_slide_direction_down">
                                    <i class="bi bi-arrow-down"></i> 하
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_main_widget_image_slide_single" 
                                       name="edit_main_image_slide_single"
                                       checked
                                       onchange="handleEditMainImageSlideModeChange()">
                                <label class="form-check-label" for="edit_main_widget_image_slide_single">
                                    1단 슬라이드
                                </label>
                                <i class="bi bi-question-circle text-muted ms-2" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="3초마다 이미지가 1개씩 슬라이드됩니다." 
                                   style="cursor: help; font-size: 0.9rem;"></i>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_main_widget_image_slide_infinite" 
                                       name="edit_main_image_slide_infinite"
                                       onchange="handleEditMainImageSlideModeChange()">
                                <label class="form-check-label" for="edit_main_widget_image_slide_infinite">
                                    무한루프 슬라이드
                                </label>
                                <i class="bi bi-question-circle text-muted ms-2" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="이미지가 좌우 방향으로 무한히 흘러가는 슬라이드입니다. 한번에 표시할 이미지 수를 지정할 수 있습니다." 
                                   style="cursor: help; font-size: 0.9rem;"></i>
                            </div>
                        </div>
                        <div class="mb-3" id="edit_main_widget_image_slide_visible_count_container" style="display: none;">
                            <label for="edit_main_widget_image_slide_visible_count" class="form-label">표시할 이미지 수 (PC)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_image_slide_visible_count" 
                                   name="edit_main_image_slide_visible_count" 
                                   min="1" 
                                   max="10" 
                                   value="3"
                                   placeholder="3">
                            <small class="text-muted">PC에서 한번에 표시할 이미지 개수를 입력하세요 (1~10).</small>
                        </div>
                        <div class="mb-3" id="edit_main_widget_image_slide_visible_count_mobile_container" style="display: none;">
                            <label for="edit_main_widget_image_slide_visible_count_mobile" class="form-label">표시할 이미지 수 (모바일)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_image_slide_visible_count_mobile" 
                                   name="edit_main_image_slide_visible_count_mobile" 
                                   min="1" 
                                   max="10" 
                                   value="2"
                                   placeholder="2">
                            <small class="text-muted">모바일에서 한번에 표시할 이미지 개수를 입력하세요 (1~10).</small>
                        </div>
                        <div class="mb-3" id="edit_main_widget_image_slide_gap_container" style="display: none;">
                            <label for="edit_main_widget_image_slide_gap" class="form-label">이미지 간격 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_image_slide_gap" 
                                   name="edit_main_image_slide_gap" 
                                   min="0" 
                                   max="50" 
                                   value="0"
                                   placeholder="0">
                            <small class="text-muted">이미지 사이 간격을 픽셀 단위로 입력하세요 (0~50).</small>
                        </div>
                        <div class="mb-3" id="edit_main_widget_image_slide_background_container" style="display: none;">
                            <label for="edit_main_widget_image_slide_background_type" class="form-label">배경 설정</label>
                            <div class="mb-2">
                                <select class="form-select" id="edit_main_widget_image_slide_background_type" name="edit_main_image_slide_background_type" onchange="handleEditMainImageSlideBackgroundTypeChange()">
                                    <option value="none">배경 없음</option>
                                    <option value="color">배경색 지정</option>
                                </select>
                            </div>
                            <div id="edit_main_widget_image_slide_background_color_container" style="display: none;">
                                <input type="color" 
                                       class="form-control form-control-color" 
                                       id="edit_main_widget_image_slide_background_color" 
                                       name="edit_main_image_slide_background_color" 
                                       value="#ffffff"
                                       title="배경색 선택">
                                <small class="text-muted">무한루프 슬라이드의 배경색을 선택하세요.</small>
                            </div>
                        </div>
                        <div id="edit_main_widget_image_slide_items">
                            <!-- 이미지 아이템들이 여기에 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="addEditMainImageSlideItem()">
                            <i class="bi bi-plus-circle me-2"></i>이미지 추가하기
                        </button>
                    </div>
                    <div class="mb-3" id="edit_main_widget_limit_container" style="display: none;">
                        <label for="edit_main_widget_limit" class="form-label">표시할 게시글 수</label>
                        <input type="number" 
                               class="form-control" 
                               id="edit_main_widget_limit" 
                               name="limit" 
                               min="1" 
                               max="50" 
                               value="10"
                               placeholder="게시글 수를 입력하세요">
                        <small class="text-muted">1~50 사이의 숫자를 입력하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_main_widget_ranking_container" style="display: none;">
                        <label class="form-label">랭킹 설정</label>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_main_widget_rank_ranking" 
                                       name="enable_rank_ranking">
                                <label class="form-check-label" for="edit_main_widget_rank_ranking">
                                    등급 랭킹
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_main_widget_point_ranking" 
                                       name="enable_point_ranking">
                                <label class="form-check-label" for="edit_main_widget_point_ranking">
                                    포인트 랭킹
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="edit_main_widget_ranking_limit" class="form-label">표시할 순위 수</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_main_widget_ranking_limit" 
                                   name="ranking_limit" 
                                   min="1" 
                                   max="50" 
                                   value="5"
                                   placeholder="순위 수를 입력하세요">
                            <small class="text-muted">1~50 사이의 숫자를 입력하세요.</small>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_main_widget_tab_menu_container" style="display: none;">
                        <label class="form-label">탭메뉴 설정</label>
                        <div id="edit_main_tab_menu_list">
                            <!-- 탭메뉴 항목들이 여기에 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addEditMainTabMenuItem()">
                            <i class="bi bi-plus-circle me-1"></i>탭메뉴 추가
                        </button>
                    </div>
                    <div class="mb-3" id="edit_main_widget_toggle_menu_container" style="display: none;">
                        <label for="edit_main_widget_toggle_menu_id" class="form-label">토글 메뉴 선택</label>
                        <select class="form-select" id="edit_main_widget_toggle_menu_id" name="toggle_menu_id">
                            <option value="">선택하세요</option>
                            <!-- 토글 메뉴 옵션들이 여기에 동적으로 추가됨 -->
                        </select>
                        <small class="text-muted">표시할 토글 메뉴를 선택하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_main_widget_contact_form_container" style="display: none;">
                        <label for="edit_main_widget_contact_form_id" class="form-label">컨텍트폼 선택</label>
                        <select class="form-select" id="edit_main_widget_contact_form_id" name="contact_form_id" required>
                            <option value="">선택하세요</option>
                            @foreach(\App\Models\ContactForm::where('site_id', $site->id)->orderBy('created_at', 'desc')->get() as $contactForm)
                                <option value="{{ $contactForm->id }}">{{ $contactForm->title ?? '제목 없음' }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">사용할 컨텍트폼을 선택하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_main_widget_map_container" style="display: none;">
                        <label for="edit_main_widget_map_id" class="form-label">지도 선택</label>
                        <select class="form-select" id="edit_main_widget_map_id" name="map_id" required>
                            <option value="">선택하세요</option>
                            @php
                                $masterSite = \App\Models\Site::getMasterSite();
                                $googleApiKey = $masterSite ? DB::table('site_settings')->where('site_id', $masterSite->id)->where('key', 'map_api_google_key')->value('value') : null;
                                $naverApiKey = $masterSite ? DB::table('site_settings')->where('site_id', $masterSite->id)->where('key', 'map_api_naver_key')->value('value') : null;
                                $kakaoApiKey = $masterSite ? DB::table('site_settings')->where('site_id', $masterSite->id)->where('key', 'map_api_kakao_key')->value('value') : null;
                            @endphp
                            @foreach(\App\Models\Map::where('site_id', $site->id)->orderBy('created_at', 'desc')->get() as $map)
                                @php
                                    $hasApiKey = false;
                                    if ($map->map_type === 'google' && !empty($googleApiKey)) {
                                        $hasApiKey = true;
                                    } elseif ($map->map_type === 'naver' && !empty($naverApiKey)) {
                                        $hasApiKey = true;
                                    } elseif ($map->map_type === 'kakao' && !empty($kakaoApiKey)) {
                                        $hasApiKey = true;
                                    }
                                @endphp
                                @if($hasApiKey)
                                    <option value="{{ $map->id }}">{{ $map->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">사용할 지도를 선택하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_main_widget_create_site_container" style="display: none;">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>마스터 사이트 전용 위젯</strong><br>
                            이 위젯은 마스터 사이트에서만 사용할 수 있으며, 로그인한 사용자에게 사이트 생성 안내를 표시합니다.
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_create_site_title" class="form-label">제목</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_main_widget_create_site_title" 
                                   name="create_site_title" 
                                   placeholder="나만의 홈페이지를 만들어보세요!"
                                   value="나만의 홈페이지를 만들어보세요!">
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_create_site_description" class="form-label">설명</label>
                            <textarea class="form-control" 
                                      id="edit_main_widget_create_site_description" 
                                      name="create_site_description" 
                                      rows="2"
                                      placeholder="회원가입 후 간단한 정보만 입력하면 바로 홈페이지를 생성할 수 있습니다.">회원가입 후 간단한 정보만 입력하면 바로 홈페이지를 생성할 수 있습니다.</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_create_site_button_text" class="form-label">버튼 텍스트</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_main_widget_create_site_button_text" 
                                   name="create_site_button_text" 
                                   placeholder="새 사이트 만들기"
                                   value="새 사이트 만들기">
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_create_site_button_link" class="form-label">버튼 링크</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_main_widget_create_site_button_link" 
                                   name="create_site_button_link" 
                                   placeholder="{{ route('user-sites.select-plan', ['site' => $site->slug]) }}"
                                   value="{{ route('user-sites.select-plan', ['site' => $site->slug]) }}">
                            <small class="text-muted">사이트 생성 페이지 링크를 입력하세요.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_create_site_icon" class="form-label">아이콘</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_main_widget_create_site_icon" 
                                   name="create_site_icon" 
                                   placeholder="bi-rocket-takeoff"
                                   value="bi-rocket-takeoff">
                            <small class="text-muted">Bootstrap Icons 클래스 이름을 입력하세요 (예: bi-rocket-takeoff)</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_create_site_background_color" class="form-label">배경색</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_main_widget_create_site_background_color" 
                                   name="create_site_background_color" 
                                   value="#007bff">
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_create_site_text_color" class="form-label">텍스트 색상</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_main_widget_create_site_text_color" 
                                   name="create_site_text_color" 
                                   value="#ffffff">
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_create_site_button_bg_color" class="form-label">버튼 배경색</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_main_widget_create_site_button_bg_color" 
                                   name="create_site_button_bg_color" 
                                   value="#0056b3">
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_create_site_button_color" class="form-label">버튼 텍스트 색상</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_main_widget_create_site_button_color" 
                                   name="create_site_button_color" 
                                   value="#ffffff">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_main_widget_create_site_show_only_when_logged_in" 
                                       name="create_site_show_only_when_logged_in"
                                       checked>
                                <label class="form-check-label" for="edit_main_widget_create_site_show_only_when_logged_in">
                                    로그인한 사용자에게만 표시
                                </label>
                            </div>
                            <small class="text-muted">체크 해제 시 로그인하지 않은 사용자에게도 표시됩니다.</small>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_main_widget_countdown_container" style="display: none;">
                        <div class="mb-3">
                            <label for="edit_main_widget_countdown_title" class="form-label">카운트 제목</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_main_widget_countdown_title" 
                                   name="countdown_title" 
                                   placeholder="카운트 제목을 입력하세요 (선택사항)">
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_countdown_content" class="form-label">내용</label>
                            <textarea class="form-control" 
                                      id="edit_main_widget_countdown_content" 
                                      name="countdown_content" 
                                      rows="3"
                                      placeholder="내용을 입력하세요 (선택사항)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_widget_countdown_type" class="form-label">카운트 타입</label>
                            <select class="form-select" id="edit_main_widget_countdown_type" name="countdown_type" onchange="handleEditCountdownTypeChange()">
                                <option value="dday">D-day 카운트</option>
                                <option value="number">숫자카운트</option>
                            </select>
                        </div>
                        <div id="edit_main_widget_countdown_dday_container" class="mb-3">
                            <div class="mb-3">
                                <label for="edit_main_widget_countdown_target_date" class="form-label">목표 날짜 및 시간</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="edit_main_widget_countdown_target_date" 
                                       name="countdown_target_date">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="edit_main_widget_countdown_dday_animation" 
                                           name="countdown_dday_animation_enabled" 
                                           value="1">
                                    <label class="form-check-label" for="edit_main_widget_countdown_dday_animation">
                                        D-day 애니메이션 활성화
                                    </label>
                                    <i class="bi bi-question-circle text-muted ms-2" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="활성화 시 카운트다운 숫자가 빠르게 변경되다가 멈춥니다." 
                                       style="cursor: help; font-size: 0.9rem;"></i>
                                </div>
                                <small class="text-muted">활성화 시 카운트다운 숫자가 빠르게 변경되다가 멈추는 애니메이션을 표시합니다.</small>
                            </div>
                        </div>
                        <div id="edit_main_widget_countdown_number_container" style="display: none;">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="edit_main_widget_countdown_animation" 
                                           name="countdown_animation_enabled" 
                                           value="1" 
                                           checked>
                                    <label class="form-check-label" for="edit_main_widget_countdown_animation">
                                        숫자 애니메이션 활성화
                                    </label>
                                    <i class="bi bi-question-circle text-muted ms-2" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="활성화 시 숫자가 슬롯처럼 돌아가다가 멈춥니다." 
                                       style="cursor: help; font-size: 0.9rem;"></i>
                                </div>
                                <small class="text-muted">슬롯처럼 0~9까지 돌아가다가 목표 숫자로 멈추는 애니메이션을 표시합니다.</small>
                            </div>
                            <label class="form-label">숫자 카운트 항목</label>
                            <div id="edit_main_widget_countdown_number_items">
                                <!-- Number items will be added here dynamically -->
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addEditCountdownNumberItem()">+ 항목 추가</button>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_main_widget_title_container_main">
                        <label for="edit_main_widget_title" class="form-label">
                            위젯 제목
                            <i class="bi bi-question-circle text-muted ms-1" 
                               id="edit_main_widget_title_help"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="제목을 입력하지 않으면 위젯 제목이 표시되지 않습니다."></i>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_main_widget_title" 
                               name="title" 
                               placeholder="위젯 제목을 입력하세요">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="edit_main_widget_is_active" 
                                   name="is_active">
                            <label class="form-check-label" for="edit_main_widget_is_active">
                                활성화
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" id="edit_main_widget_save_btn_footer" onclick="saveMainWidgetSettings()">저장</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentContainerId = null;
let currentColumnIndex = null;
let currentEditWidgetId = null;
let successModalReloadHandler = null;

// 컨테이너 추가
document.getElementById('addContainerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // 체크박스가 체크되지 않았을 때도 명시적으로 0을 전송
    const fullWidthCheckbox = document.getElementById('container_full_width');
    if (fullWidthCheckbox) {
        formData.set('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    const fullHeightCheckbox = document.getElementById('container_full_height');
    if (fullHeightCheckbox) {
        formData.set('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    
    fetch('{{ route("admin.main-widgets.containers.store", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('컨테이너 추가에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('컨테이너 추가 중 오류가 발생했습니다.');
    });
});

// 다음 칸과 병합
function mergeNextColumn(containerId, columnIndex) {
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (!containerItem) return;
    
    const columnCells = containerItem.querySelectorAll('.column-cell');
    const totalColumns = parseInt(containerItem.querySelector('select[onchange*="updateContainerColumns"]')?.value || '1');
    
    // 현재 병합 정보 가져오기
    const currentMerges = JSON.parse(containerItem.getAttribute('data-column-merges') || '{}');
    
    // 현재 칸의 병합 범위 계산
    const currentSpan = currentMerges[columnIndex] || 1;
    const mergeEnd = columnIndex + currentSpan - 1; // 병합 범위의 마지막 칸 인덱스
    
    // 병합 범위 끝이 마지막 칸이면 병합 불가
    if (mergeEnd >= totalColumns - 1) {
        alert('더 이상 병합할 수 있는 칸이 없습니다.');
        return;
    }
    
    // 다음 병합할 칸이 이미 다른 병합에 포함되어 있는지 확인
    const nextColumnIndex = mergeEnd + 1;
    if (currentMerges[nextColumnIndex]) {
        // 다음 칸이 병합의 시작점이었다면 제거하고 현재 병합에 포함
        delete currentMerges[nextColumnIndex];
    }
    
    // 병합 정보 업데이트
    currentMerges[columnIndex] = currentSpan + 1;
    
    // 서버에 저장
    const formData = new FormData();
    formData.append('column_merges', JSON.stringify(currentMerges));
    formData.append('_method', 'PUT');
    
    fetch('{{ route("admin.main-widgets.containers.update", ["site" => $site->slug, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('병합에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('병합 중 오류가 발생했습니다.');
    });
}

// 병합 해제
function unmergeColumn(containerId, columnIndex) {
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (!containerItem) return;
    
    // 현재 병합 정보 가져오기
    const currentMerges = JSON.parse(containerItem.getAttribute('data-column-merges') || '{}');
    
    if (!currentMerges[columnIndex] || currentMerges[columnIndex] <= 1) {
        return;
    }
    
    // 병합 정보 업데이트
    const currentSpan = currentMerges[columnIndex];
    if (currentSpan > 2) {
        // 3칸 이상 병합된 경우 1칸 줄임
        currentMerges[columnIndex] = currentSpan - 1;
    } else {
        // 2칸 병합된 경우 완전히 해제
        delete currentMerges[columnIndex];
    }
    
    // 서버에 저장
    const formData = new FormData();
    formData.append('column_merges', JSON.stringify(currentMerges));
    formData.append('_method', 'PUT');
    
    fetch('{{ route("admin.main-widgets.containers.update", ["site" => $site->slug, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('병합 해제에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('병합 해제 중 오류가 발생했습니다.');
    });
}

// 컨테이너 가로 개수 업데이트
function updateContainerColumns(containerId, columns) {
    if (!confirm('컨테이너의 가로 개수를 변경하시겠습니까? 컬럼 수가 줄어들면 해당 컬럼의 위젯들이 삭제됩니다.')) {
        // 취소 시 원래 값으로 복원
        location.reload();
        return;
    }
    
    const formData = new FormData();
    formData.append('columns', columns);
    // 현재 full_width 상태 유지
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    // 현재 full_height 상태 유지
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    // 현재 widget_spacing 상태 유지
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (containerItem) {
        const widgetSpacingSelect = containerItem.querySelector('select[onchange*="updateContainerWidgetSpacing"]');
        if (widgetSpacingSelect) {
            formData.append('widget_spacing', widgetSpacingSelect.value);
        }
    }
    formData.append('_method', 'PUT');
    
    fetch('{{ route("admin.main-widgets.containers.update", ["site" => $site->slug, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('컨테이너 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('컨테이너 업데이트 중 오류가 발생했습니다.');
        location.reload();
    });
}

// 컨테이너 위젯 간격 업데이트
function updateContainerWidgetSpacing(containerId, widgetSpacing) {
    try {
        const formData = new FormData();
        
        // 컨테이너 아이템 찾기
        const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
        if (!containerItem) {
            console.error('Container item not found for ID:', containerId);
            alert('컨테이너를 찾을 수 없습니다.');
            return;
        }
        
        // 컬럼 값 찾기
        const columnsSelect = containerItem.querySelector('select[data-container-id="' + containerId + '"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        } else {
            formData.append('columns', '1');
        }
        
        // 정렬 값 찾기
        const allSelects = containerItem.querySelectorAll('select[data-container-id="' + containerId + '"]');
        if (allSelects.length >= 2) {
            formData.append('vertical_align', allSelects[1].value);
        } else {
            formData.append('vertical_align', 'top');
        }
        
        // full_width 값 찾기
        const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
        if (fullWidthCheckbox) {
            formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
        }
        
        // full_height 값 찾기
        const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
        if (fullHeightCheckbox) {
            formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
        }
        
        formData.append('widget_spacing', widgetSpacing);
        formData.append('_method', 'PUT');
        
        fetch('{{ route("admin.main-widgets.containers.update", ["site" => $site->slug, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 성공 메시지 표시
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>위젯 간격이 저장되었습니다.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alertDiv);
                
                // 3초 후 자동으로 알림 제거
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
            } else {
                alert('위젯 간격 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('위젯 간격 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
        });
    } catch (error) {
        console.error('Error in updateContainerWidgetSpacing:', error);
        alert('위젯 간격 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
    }
}

// 컨테이너 정렬 업데이트
function updateContainerVerticalAlign(containerId, verticalAlign) {
    const formData = new FormData();
    formData.append('columns', document.querySelector(`select[data-container-id="${containerId}"][onchange*="updateContainerColumns"]`).value);
    formData.append('vertical_align', verticalAlign);
    // 현재 full_width 상태 유지
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    // 현재 full_height 상태 유지
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    formData.append('_method', 'PUT');
    
    fetch('{{ route("admin.main-widgets.containers.update", ["site" => $site->slug, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 성공 시 페이지 새로고침 없이 업데이트 (선택사항)
            // 또는 location.reload()로 새로고침
        } else {
            alert('정렬 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('정렬 설정 업데이트 중 오류가 발생했습니다.');
    });
}

// 컨테이너 가로 100% 업데이트
function updateContainerFullWidth(containerId, fullWidth) {
    try {
        const formData = new FormData();
        
        // 컨테이너 아이템 찾기
        const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
        if (!containerItem) {
            console.error('Container item not found for ID:', containerId);
            alert('컨테이너를 찾을 수 없습니다.');
            return;
        }
        
        // 컬럼 값 찾기 (첫 번째 select)
        const columnsSelect = containerItem.querySelector('select[data-container-id="' + containerId + '"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        } else {
            console.error('Columns select not found');
            alert('컨테이너 설정을 찾을 수 없습니다.');
            return;
        }
        
        // 정렬 값 찾기 (두 번째 select)
        const allSelects = containerItem.querySelectorAll('select[data-container-id="' + containerId + '"]');
        if (allSelects.length >= 2) {
            formData.append('vertical_align', allSelects[1].value);
        } else if (allSelects.length === 1) {
            // 정렬 셀렉터가 없으면 기본값
            formData.append('vertical_align', 'top');
        }
        
        // full_height 값 찾기
        const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
        if (fullHeightCheckbox) {
            formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
        }
        
        formData.append('full_width', fullWidth ? '1' : '0');
        formData.append('_method', 'PUT');
        
        fetch('{{ route("admin.main-widgets.containers.update", ["site" => $site->slug, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error('Network response was not ok: ' + response.status);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // 성공 시 체크박스 상태만 업데이트 (페이지 새로고침 없이)
                const checkbox = document.getElementById(`container_full_width_${containerId}`);
                if (checkbox) {
                    checkbox.checked = fullWidth;
                }
                // 성공 메시지 표시
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>가로 100% 설정이 저장되었습니다.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alertDiv);
                
                // 3초 후 자동으로 알림 제거
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
            } else {
                alert('가로 100% 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
                // 실패 시 체크박스 상태 복원
                const checkbox = document.getElementById(`container_full_width_${containerId}`);
                if (checkbox) {
                    checkbox.checked = !fullWidth;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('가로 100% 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
            // 오류 시 체크박스 상태 복원
            const checkbox = document.getElementById(`container_full_width_${containerId}`);
            if (checkbox) {
                checkbox.checked = !fullWidth;
            }
        });
    } catch (error) {
        console.error('Error in updateContainerFullWidth:', error);
        alert('가로 100% 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
    }
}

// 컨테이너 세로 100% 업데이트
function updateContainerFullHeight(containerId, fullHeight) {
    try {
        const formData = new FormData();
        
        // 컨테이너 아이템 찾기
        const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
        if (!containerItem) {
            console.error('Container item not found for ID:', containerId);
            alert('컨테이너를 찾을 수 없습니다.');
            return;
        }
        
        // 컬럼 값 찾기 (첫 번째 select)
        const columnsSelect = containerItem.querySelector('select[data-container-id="' + containerId + '"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        } else {
            console.error('Columns select not found');
            alert('컨테이너 설정을 찾을 수 없습니다.');
            return;
        }
        
        // 정렬 값 찾기 (두 번째 select)
        const allSelects = containerItem.querySelectorAll('select[data-container-id="' + containerId + '"]');
        if (allSelects.length >= 2) {
            formData.append('vertical_align', allSelects[1].value);
        } else if (allSelects.length === 1) {
            // 정렬 셀렉터가 없으면 기본값
            formData.append('vertical_align', 'top');
        }
        
        // full_width 값 찾기
        const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
        if (fullWidthCheckbox) {
            formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
        }
        
        // widget_spacing 값 찾기
        const widgetSpacingSelect = containerItem.querySelector('select[onchange*="updateContainerWidgetSpacing"]');
        if (widgetSpacingSelect) {
            formData.append('widget_spacing', widgetSpacingSelect.value);
        } else {
            formData.append('widget_spacing', '3'); // 기본값
        }
        
        formData.append('full_height', fullHeight ? '1' : '0');
        formData.append('_method', 'PUT');
        
        fetch('{{ route("admin.main-widgets.containers.update", ["site" => $site->slug, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error('Network response was not ok: ' + response.status);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // 성공 시 체크박스 상태만 업데이트 (페이지 새로고침 없이)
                const checkbox = document.getElementById(`container_full_height_${containerId}`);
                if (checkbox) {
                    checkbox.checked = fullHeight;
                }
                // 성공 메시지 표시
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>세로 100% 설정이 저장되었습니다.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alertDiv);
                
                // 3초 후 자동으로 알림 제거
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
            } else {
                alert('세로 100% 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
                // 실패 시 체크박스 상태 복원
                const checkbox = document.getElementById(`container_full_height_${containerId}`);
                if (checkbox) {
                    checkbox.checked = !fullHeight;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('세로 100% 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
            // 오류 시 체크박스 상태 복원
            const checkbox = document.getElementById(`container_full_height_${containerId}`);
            if (checkbox) {
                checkbox.checked = !fullHeight;
            }
        });
    } catch (error) {
        console.error('Error in updateContainerFullHeight:', error);
        alert('세로 100% 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
    }
}

// 컨테이너 위로 이동
function moveContainerUp(containerId) {
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (!containerItem) return;
    
    const previousItem = containerItem.previousElementSibling;
    if (!previousItem || !previousItem.classList.contains('container-item')) {
        return; // 이미 맨 위에 있거나 이전 항목이 컨테이너가 아님
    }
    
    // DOM에서 위치 변경
    containerItem.parentNode.insertBefore(containerItem, previousItem);
    
    // 순서 저장
    saveContainerOrder();
}

// 컨테이너 아래로 이동
function moveContainerDown(containerId) {
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (!containerItem) return;
    
    const nextItem = containerItem.nextElementSibling;
    if (!nextItem || !nextItem.classList.contains('container-item')) {
        return; // 이미 맨 아래에 있거나 다음 항목이 컨테이너가 아님
    }
    
    // DOM에서 위치 변경
    containerItem.parentNode.insertBefore(nextItem, containerItem);
    
    // 순서 저장
    saveContainerOrder();
}

// 컨테이너 순서 저장
function saveContainerOrder() {
    const containers = Array.from(document.querySelectorAll('.container-item'));
    const containerData = containers.map((item, index) => ({
        id: parseInt(item.dataset.containerId),
        order: index + 1
    }));
    
    fetch('{{ route("admin.main-widgets.containers.reorder", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ containers: containerData })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('순서 저장 실패:', data.message);
            alert('컨테이너 순서 저장에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
            location.reload(); // 실패 시 새로고침
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('컨테이너 순서 저장 중 오류가 발생했습니다.');
        location.reload(); // 오류 시 새로고침
    });
}

// 컨테이너 삭제
function deleteContainer(containerId) {
    if (!confirm('컨테이너를 삭제하시겠습니까? 컨테이너 내의 모든 위젯도 함께 삭제됩니다.')) {
        return;
    }
    
    fetch('{{ route("admin.main-widgets.containers.delete", ["site" => $site->slug, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('컨테이너 삭제에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('컨테이너 삭제 중 오류가 발생했습니다.');
    });
}

// 위젯 추가 폼 표시
function showAddWidgetForm(containerId, columnIndex) {
    currentContainerId = containerId;
    currentColumnIndex = columnIndex;
    document.getElementById('widget_container_id').value = containerId;
    document.getElementById('widget_column_index').value = columnIndex;
    
    // 폼 초기화
    document.getElementById('addWidgetForm').reset();
    document.getElementById('widget_container_id').value = containerId;
    document.getElementById('widget_column_index').value = columnIndex;
    
    // 위젯 타입 변경 이벤트 트리거하여 필드 숨김
    const widgetTypeSelect = document.getElementById('widget_type');
    if (widgetTypeSelect) {
        widgetTypeSelect.value = '';
        widgetTypeSelect.dispatchEvent(new Event('change'));
    }
    
    // 블록 표시 및 스크롤
    const addWidgetBlock = document.getElementById('addWidgetBlock');
    if (addWidgetBlock) {
        addWidgetBlock.style.display = 'block';
        addWidgetBlock.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// 위젯 추가 폼 숨김
function hideAddWidgetForm() {
    const addWidgetBlock = document.getElementById('addWidgetBlock');
    if (addWidgetBlock) {
        addWidgetBlock.style.display = 'none';
    }
    // 폼 초기화
    document.getElementById('addWidgetForm').reset();
    currentContainerId = null;
    currentColumnIndex = null;
}

// 위젯 추가 - 사이드 위젯의 addWidget 함수 로직을 참조
function addMainWidget() {
    const form = document.getElementById('addWidgetForm');
    const formData = new FormData(form);
    
    // settings 객체 생성 (사이드 위젯 페이지의 addWidget 함수와 동일한 로직)
    const settings = {};
    const widgetType = formData.get('type');
    
    if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
        const limit = formData.get('limit');
        if (limit) {
            settings.limit = parseInt(limit);
        }
    } else if (widgetType === 'board') {
        const boardId = formData.get('board_id');
        const limit = formData.get('limit');
        const sortOrder = formData.get('sort_order');
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (limit) {
            settings.limit = parseInt(limit);
        }
        if (sortOrder) {
            settings.sort_order = sortOrder;
        }
    } else if (widgetType === 'marquee_board') {
        if (!formData.get('title') || formData.get('title') === '') {
            formData.set('title', '게시글 전광판');
        }
        const boardId = formData.get('board_id');
        const limit = formData.get('limit');
        const sortOrder = formData.get('sort_order');
        const direction = formData.get('direction');
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (limit) {
            settings.limit = parseInt(limit);
        }
        if (sortOrder) {
            settings.sort_order = sortOrder;
        }
        if (direction) {
            settings.direction = direction;
        }
    } else if (widgetType === 'gallery') {
        const boardId = formData.get('gallery_board_id');
        const displayType = formData.get('gallery_display_type');
        const showTitle = document.getElementById('widget_gallery_show_title')?.checked;
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (displayType) {
            settings.display_type = displayType;
        }
        settings.show_title = showTitle;
        if (displayType === 'grid') {
            const cols = formData.get('gallery_cols');
            const rows = formData.get('gallery_rows');
            if (cols) {
                settings.cols = parseInt(cols);
            }
            if (rows) {
                settings.rows = parseInt(rows);
            }
            settings.limit = parseInt(cols) * parseInt(rows);
        } else if (displayType === 'slide') {
            const slideCols = formData.get('gallery_slide_cols');
            const slideDirectionRadio = document.querySelector('input[name="gallery_slide_direction"]:checked');
            const slideDirection = slideDirectionRadio ? slideDirectionRadio.value : 'left';
            if (slideCols) {
                settings.slide_cols = parseInt(slideCols);
            }
            if (slideDirection) {
                settings.slide_direction = slideDirection;
            }
            settings.limit = 10;
        }
    } else if (widgetType === 'tab_menu') {
        if (!formData.get('title') || formData.get('title') === '') {
            formData.set('title', '탭메뉴');
        }
        settings.tabs = [];
    } else if (widgetType === 'toggle_menu') {
        // 토글 메뉴 위젯 제목은 사용자가 입력한 값 사용
        // 토글 메뉴 ID 수집 (추가 폼용)
        const toggleMenuSelect = document.getElementById('widget_toggle_menu_id') || document.getElementById('edit_main_widget_toggle_menu_id');
        if (toggleMenuSelect && toggleMenuSelect.value) {
            settings.toggle_menu_id = parseInt(toggleMenuSelect.value);
        }
    } else if (widgetType === 'user_ranking') {
        if (!formData.get('title') || formData.get('title') === '') {
            formData.set('title', '회원 랭킹');
        }
        settings.enable_rank_ranking = document.getElementById('widget_rank_ranking')?.checked || false;
        settings.enable_point_ranking = document.getElementById('widget_point_ranking')?.checked || false;
        const rankingLimit = formData.get('ranking_limit');
        if (rankingLimit) {
            settings.ranking_limit = parseInt(rankingLimit);
        }
    } else if (widgetType === 'custom_html') {
        const customHtml = document.getElementById('widget_custom_html')?.value;
        if (customHtml) {
            settings.html = customHtml;
            settings.custom_html = customHtml;
        }
    } else if (widgetType === 'contact_form') {
        const contactFormId = formData.get('contact_form_id');
        if (contactFormId) {
            settings.contact_form_id = parseInt(contactFormId);
        }
    } else if (widgetType === 'map') {
        const mapId = formData.get('map_id');
        if (mapId) {
            settings.map_id = parseInt(mapId);
        }
    } else if (widgetType === 'countdown') {
        const countdownTitle = formData.get('countdown_title') || '';
        const countdownContent = formData.get('countdown_content') || '';
        const countdownType = formData.get('countdown_type') || 'dday';
        
        settings.countdown_title = countdownTitle;
        settings.countdown_content = countdownContent;
        settings.countdown_type = countdownType;
        
        if (countdownType === 'dday') {
            const targetDate = formData.get('countdown_target_date');
            if (targetDate) {
                settings.countdown_target_date = targetDate;
            }
        } else if (countdownType === 'number') {
            const animationEnabled = document.getElementById('widget_countdown_animation')?.checked || false;
            settings.countdown_animation = animationEnabled;
            
            const numberItems = [];
            const numberItemElements = document.querySelectorAll('.countdown-number-item');
            numberItemElements.forEach((item) => {
                const itemIndex = item.dataset.itemIndex;
                const itemName = item.querySelector(`input[name="countdown_number[${itemIndex}][name]"]`)?.value || '';
                const itemNumber = item.querySelector(`input[name="countdown_number[${itemIndex}][number]"]`)?.value || '';
                const itemUnit = item.querySelector(`input[name="countdown_number[${itemIndex}][unit]"]`)?.value || '';
                
                if (itemName && itemNumber) {
                    numberItems.push({
                        name: itemName,
                        number: parseInt(itemNumber) || 0,
                        unit: itemUnit
                    });
                }
            });
            settings.countdown_number_items = numberItems;
        }
    } else if (widgetType === 'block') {
        const blockTitle = formData.get('block_title');
        const blockContent = formData.get('block_content');
        const textAlign = formData.get('block_text_align') || 'left';
        const backgroundType = formData.get('block_background_type') || 'color';
        const paddingTop = formData.get('block_padding_top') || '20';
        const paddingLeft = formData.get('block_padding_left') || '20';
        const blockLink = formData.get('block_link');
        const openNewTab = document.getElementById('widget_block_open_new_tab')?.checked || false;
        const fontColor = formData.get('block_font_color') || '#ffffff';
        const titleFontSize = formData.get('block_title_font_size') || '16';
        const contentFontSize = formData.get('block_content_font_size') || '14';
        // 버튼 데이터 수집
        const buttons = [];
        const buttonInputs = document.querySelectorAll('.block-button-text');
        buttonInputs.forEach((input, index) => {
            const buttonText = input.value || '';
            if (buttonText) {
                const buttonCard = input.closest('.card');
                const buttonLink = buttonCard.querySelector('.block-button-link')?.value || '';
                const buttonOpenNewTab = buttonCard.querySelector('.block-button-open-new-tab')?.checked || false;
                const buttonBackgroundColor = buttonCard.querySelector('.block-button-background-color')?.value || '#007bff';
                const buttonTextColor = buttonCard.querySelector('.block-button-text-color')?.value || '#ffffff';
                
                buttons.push({
                    text: buttonText,
                    link: buttonLink,
                    open_new_tab: buttonOpenNewTab,
                    background_color: buttonBackgroundColor,
                    text_color: buttonTextColor
                });
            }
        });
        
        if (blockTitle) {
            settings.block_title = blockTitle;
        }
        if (blockContent) {
            settings.block_content = blockContent;
        }
        settings.text_align = textAlign;
        settings.background_type = backgroundType;
        settings.font_color = fontColor;
        settings.title_font_size = titleFontSize;
        settings.content_font_size = contentFontSize;
        settings.buttons = buttons;
        
        if (backgroundType === 'color') {
            const backgroundColor = formData.get('block_background_color') || '#007bff';
            settings.background_color = backgroundColor;
        } else if (backgroundType === 'image') {
            const imageFile = document.getElementById('widget_block_image_input')?.files[0];
            if (imageFile) {
                formData.append('block_background_image_file', imageFile);
            }
            const imageUrl = formData.get('block_background_image_url');
            if (imageUrl) {
                settings.background_image_url = imageUrl;
            }
        }
        
        settings.padding_top = parseInt(paddingTop);
        settings.padding_left = parseInt(paddingLeft);
        
        if (blockLink) {
            settings.link = blockLink;
        }
        settings.open_new_tab = openNewTab;
    } else if (widgetType === 'block_slide') {
        const slideDirection = document.querySelector('input[name="block_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        const blockItems = [];
        const blockSlideItems = document.querySelectorAll('.block-slide-item');
        blockSlideItems.forEach((item, index) => {
            const itemIndex = item.dataset.itemIndex;
            const title = item.querySelector('.block-slide-title')?.value || '';
            const content = item.querySelector('.block-slide-content')?.value || '';
            const textAlignRadio = item.querySelector(`input[name="block_slide[${itemIndex}][text_align]"]:checked`);
            const textAlign = textAlignRadio ? textAlignRadio.value : 'left';
            const backgroundType = item.querySelector('.block-slide-background-type')?.value || 'color';
            const paddingTop = item.querySelector('.block-slide-padding-top')?.value || '20';
            const paddingBottom = item.querySelector('.block-slide-padding-bottom')?.value || '20';
            const paddingLeft = item.querySelector('.block-slide-padding-left')?.value || '20';
            const paddingRight = item.querySelector('.block-slide-padding-right')?.value || '20';
            const titleContentGap = item.querySelector('.block-slide-title-content-gap')?.value || '8';
            const link = item.querySelector('.block-slide-link')?.value || '';
            const openNewTab = item.querySelector('.block-slide-open-new-tab')?.checked || false;
            const fontColor = item.querySelector('.block-slide-font-color')?.value || '#ffffff';
            const titleFontSize = item.querySelector('.block-slide-title-font-size')?.value || '16';
            const contentFontSize = item.querySelector('.block-slide-content-font-size')?.value || '14';
            // 버튼 데이터 수집
            const buttons = [];
            const buttonInputs = item.querySelectorAll('.block-slide-button-text');
            buttonInputs.forEach((input) => {
                const buttonText = input.value || '';
                if (buttonText) {
                    const buttonCard = input.closest('.card');
                    const buttonLink = buttonCard.querySelector('.block-slide-button-link')?.value || '';
                    const buttonOpenNewTab = buttonCard.querySelector('.block-slide-button-open-new-tab')?.checked || false;
                    const buttonBackgroundColor = buttonCard.querySelector('.block-slide-button-background-color')?.value || '#007bff';
                    const buttonTextColor = buttonCard.querySelector('.block-slide-button-text-color')?.value || '#ffffff';
                    
                    buttons.push({
                        text: buttonText,
                        link: buttonLink,
                        open_new_tab: buttonOpenNewTab,
                        background_color: buttonBackgroundColor,
                        text_color: buttonTextColor
                    });
                }
            });
            
            const blockItem = {
                title: title,
                content: content,
                text_align: textAlign,
                background_type: backgroundType,
                padding_top: parseInt(paddingTop),
                padding_bottom: parseInt(paddingBottom),
                padding_left: parseInt(paddingLeft),
                padding_right: parseInt(paddingRight),
                title_content_gap: parseInt(titleContentGap),
                link: link,
                open_new_tab: openNewTab,
                buttons: buttons,
                font_color: fontColor,
                title_font_size: titleFontSize,
                content_font_size: contentFontSize,
                show_button: showButton
            };
            
            if (showButton) {
                const buttonTopMargin = item.querySelector('.block-slide-button-top-margin')?.value || '12';
                blockItem.button_text = buttonText;
                blockItem.button_background_color = buttonBackgroundColor;
                blockItem.button_text_color = buttonTextColor;
                blockItem.button_top_margin = parseInt(buttonTopMargin);
            }
            
            if (backgroundType === 'color') {
                const backgroundColor = item.querySelector('.block-slide-background-color')?.value || '#007bff';
                blockItem.background_color = backgroundColor;
            } else if (backgroundType === 'image') {
                const imageFile = item.querySelector(`#block_slide_${itemIndex}_image_input`)?.files[0];
                if (imageFile) {
                    formData.append(`block_slide[${itemIndex}][background_image_file]`, imageFile);
                }
                const imageUrl = item.querySelector(`#block_slide_${itemIndex}_background_image_url`)?.value;
                if (imageUrl) {
                    blockItem.background_image_url = imageUrl;
                }
            }
            
            blockItems.push(blockItem);
        });
        
        settings.blocks = blockItems;
    } else if (widgetType === 'image') {
        const imageFile = document.getElementById('widget_image_input')?.files[0];
        if (imageFile) {
            formData.append('image_file', imageFile);
        }
        const imageUrl = document.getElementById('widget_image_url')?.value;
        if (imageUrl) {
            settings.image_url = imageUrl;
        }
        const link = document.getElementById('widget_image_link')?.value;
        if (link) {
            settings.link = link;
        }
        settings.open_new_tab = document.getElementById('widget_image_open_new_tab')?.checked || false;
    } else if (widgetType === 'image_slide') {
        const slideDirection = document.querySelector('input[name="image_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        const singleSlide = document.getElementById('widget_image_slide_single')?.checked || false;
        const infiniteSlide = document.getElementById('widget_image_slide_infinite')?.checked || false;
        const visibleCount = document.getElementById('widget_image_slide_visible_count')?.value || '3';
        const visibleCountMobile = document.getElementById('widget_image_slide_visible_count_mobile')?.value || '2';
        const imageGap = document.getElementById('widget_image_slide_gap')?.value || '0';
        
        settings.slide_mode = infiniteSlide ? 'infinite' : 'single';
        if (infiniteSlide) {
            settings.visible_count = parseInt(visibleCount) || 3;
            settings.visible_count_mobile = parseInt(visibleCountMobile) || 2;
            settings.image_gap = parseInt(imageGap) || 0;
        }
        
        const imageItems = [];
        const imageSlideItems = document.querySelectorAll('.image-slide-item');
        imageSlideItems.forEach((item, index) => {
            const itemIndex = item.dataset.itemIndex;
            const imageFile = item.querySelector(`#image_slide_${itemIndex}_image_input`)?.files[0];
            const imageUrl = item.querySelector(`#image_slide_${itemIndex}_image_url`)?.value;
            const link = item.querySelector('.image-slide-link')?.value || '';
            const openNewTab = item.querySelector('.image-slide-open-new-tab')?.checked || false;
            
            const imageItem = {
                link: link,
                open_new_tab: openNewTab
            };
            
            if (imageFile) {
                formData.append(`image_slide[${itemIndex}][image_file]`, imageFile);
            }
            if (imageUrl) {
                imageItem.image_url = imageUrl;
            }
            
            imageItems.push(imageItem);
        });
        
        settings.images = imageItems;
    } else if (widgetType === 'countdown') {
        const countdownTitle = document.getElementById('widget_countdown_title')?.value || '';
        const countdownContent = document.getElementById('widget_countdown_content')?.value || '';
        const countdownType = document.getElementById('widget_countdown_type')?.value || 'dday';
        
        settings.countdown_title = countdownTitle;
        settings.countdown_content = countdownContent;
        settings.countdown_type = countdownType;
        
        if (countdownType === 'dday') {
            const targetDate = document.getElementById('widget_countdown_target_date')?.value;
            if (targetDate) {
                // datetime-local 형식을 ISO 형식으로 변환
                const date = new Date(targetDate);
                settings.countdown_target_date = date.toISOString();
            }
            const ddayAnimationEnabled = document.getElementById('widget_countdown_dday_animation')?.checked || false;
            settings.countdown_dday_animation_enabled = ddayAnimationEnabled;
        } else if (countdownType === 'number') {
            const animationEnabled = document.getElementById('widget_countdown_animation')?.checked || false;
            settings.countdown_animation_enabled = animationEnabled;
            
            // 숫자 카운트 항목 수집
            const numberItems = [];
            const numberItemElements = document.querySelectorAll('.countdown-number-item');
            numberItemElements.forEach((item) => {
                const itemIndex = item.dataset.itemIndex;
                const nameInput = item.querySelector(`input[name="countdown_number[${itemIndex}][name]"]`);
                const numberInput = item.querySelector(`input[name="countdown_number[${itemIndex}][number]"]`);
                const unitInput = item.querySelector(`input[name="countdown_number[${itemIndex}][unit]"]`);
                
                if (nameInput && numberInput && unitInput) {
                    numberItems.push({
                        name: nameInput.value || '',
                        number: parseInt(numberInput.value) || 0,
                        unit: unitInput.value || ''
                    });
                }
            });
            settings.countdown_number_items = numberItems;
        }
    }
    
    // 제목 처리: 갤러리 위젯의 경우 제목이 없으면 빈 문자열로 설정
    const titleInput = document.getElementById('widget_title');
    if (widgetType === 'gallery' && titleInput) {
        const titleValue = titleInput.value.trim();
        if (!titleValue || titleValue === '') {
            formData.set('title', '');
        }
    }
    
    // settings를 JSON으로 추가
    if (Object.keys(settings).length > 0) {
        formData.append('settings', JSON.stringify(settings));
    }
    
    fetch('{{ route("admin.main-widgets.store", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 413) {
                throw new Error('요청 데이터가 너무 큽니다. 이미지 파일 크기를 줄이거나 설정 데이터를 간소화해주세요.');
            }
            return response.json().then(data => {
                throw new Error(data.message || '알 수 없는 오류');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            hideAddWidgetForm();
            location.reload();
        } else {
            alert('위젯 추가에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('위젯 추가 중 오류가 발생했습니다: ' + (error.message || '알 수 없는 오류'));
    });
}

// 위젯 수정
function editMainWidget(widgetId) {
    currentEditWidgetId = widgetId;
    
    // 위젯 정보 가져오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (!widgetItem) {
        alert('위젯을 찾을 수 없습니다.');
        return;
    }
    
    const title = widgetItem.dataset.widgetTitle;
    const widgetType = widgetItem.dataset.widgetType;
    const isActive = widgetItem.dataset.widgetActive === '1';
    
    document.getElementById('edit_main_widget_id').value = widgetId;
    document.getElementById('edit_main_widget_title').value = title;
    document.getElementById('edit_main_widget_is_active').checked = isActive;
    
    // 위젯 타입에 따라 게시글 수 입력 필드 표시/숨김
    const limitContainer = document.getElementById('edit_main_widget_limit_container');
    const tabMenuContainer = document.getElementById('edit_main_widget_tab_menu_container');
    const rankingContainer = document.getElementById('edit_main_widget_ranking_container');
    const titleContainer = document.getElementById('edit_main_widget_title_container_main');
    
    // 위젯 정보를 AJAX로 가져오기
    fetch(`{{ route("admin.main-widgets", ["site" => $site->slug]) }}?widget_id=${widgetId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.widget) {
            // 위젯 데이터를 sessionStorage에 저장 (나중에 저장 시 기존 설정 유지용)
            sessionStorage.setItem(`widget_${widgetId}_data`, JSON.stringify(data.widget));
            const settings = data.widget.settings || {};
            
            const boardContainer = document.getElementById('edit_main_widget_board_container');
            const sortOrderContainer = document.getElementById('edit_main_widget_sort_order_container');
            const marqueeDirectionContainer = document.getElementById('edit_main_widget_marquee_direction_container');
            
            // 모든 컨테이너 숨기기
            if (limitContainer) limitContainer.style.display = 'none';
            if (tabMenuContainer) tabMenuContainer.style.display = 'none';
            if (rankingContainer) rankingContainer.style.display = 'none';
            if (boardContainer) boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            const galleryContainer = document.getElementById('edit_main_widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('edit_main_widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('edit_main_widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('edit_main_widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('edit_main_widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            const customHtmlContainer = document.getElementById('edit_main_widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            const blockContainer = document.getElementById('edit_main_widget_block_container');
            if (blockContainer) blockContainer.style.display = 'none';
            const blockSlideContainer = document.getElementById('edit_main_widget_block_slide_container');
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            const imageContainer = document.getElementById('edit_main_widget_image_container');
            if (imageContainer) imageContainer.style.display = 'none';
            const imageSlideContainer = document.getElementById('edit_main_widget_image_slide_container');
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const contactFormContainer = document.getElementById('edit_main_widget_contact_form_container');
            if (contactFormContainer) contactFormContainer.style.display = 'none';
            const mapContainer = document.getElementById('edit_main_widget_map_container');
            if (mapContainer) mapContainer.style.display = 'none';
            const createSiteContainer = document.getElementById('edit_main_widget_create_site_container');
            if (createSiteContainer) createSiteContainer.style.display = 'none';
            const toggleMenuContainer = document.getElementById('edit_main_widget_toggle_menu_container');
            if (toggleMenuContainer) toggleMenuContainer.style.display = 'none';
            const countdownContainer = document.getElementById('edit_main_widget_countdown_container');
            if (countdownContainer) countdownContainer.style.display = 'none';
            const titleInput = document.getElementById('edit_main_widget_title');
            
            if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
                if (limitContainer) limitContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_main_widget_limit')) {
                    document.getElementById('edit_main_widget_limit').value = settings.limit || 10;
                }
                if (document.getElementById('edit_main_widget_title')) {
                    document.getElementById('edit_main_widget_title').value = title;
                }
            } else if (widgetType === 'board') {
                if (limitContainer) limitContainer.style.display = 'block';
                if (boardContainer) boardContainer.style.display = 'block';
                if (sortOrderContainer) sortOrderContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_main_widget_limit')) {
                    document.getElementById('edit_main_widget_limit').value = settings.limit || 10;
                }
                if (document.getElementById('edit_main_widget_board_id')) {
                    document.getElementById('edit_main_widget_board_id').value = settings.board_id || '';
                }
                if (document.getElementById('edit_main_widget_sort_order')) {
                    document.getElementById('edit_main_widget_sort_order').value = settings.sort_order || 'latest';
                }
                if (document.getElementById('edit_main_widget_title')) {
                    document.getElementById('edit_main_widget_title').value = title;
                }
            } else if (widgetType === 'marquee_board') {
                if (limitContainer) limitContainer.style.display = 'block';
                if (boardContainer) boardContainer.style.display = 'block';
                if (sortOrderContainer) sortOrderContainer.style.display = 'block';
                if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (document.getElementById('edit_main_widget_limit')) {
                    document.getElementById('edit_main_widget_limit').value = settings.limit || 10;
                }
                if (document.getElementById('edit_main_widget_board_id')) {
                    document.getElementById('edit_main_widget_board_id').value = settings.board_id || '';
                }
                if (document.getElementById('edit_main_widget_sort_order')) {
                    document.getElementById('edit_main_widget_sort_order').value = settings.sort_order || 'latest';
                }
                if (marqueeDirectionContainer) {
                    const direction = settings.direction || 'left';
                    const directionRadio = document.getElementById(`edit_main_direction_${direction}`);
                    if (directionRadio) directionRadio.checked = true;
                }
                if (document.getElementById('edit_main_widget_title')) {
                    document.getElementById('edit_main_widget_title').value = '게시글 전광판';
                }
            } else if (widgetType === 'gallery') {
                if (galleryContainer) galleryContainer.style.display = 'block';
                if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'block';
                if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                
                const displayType = settings.display_type || 'grid';
                if (document.getElementById('edit_main_widget_gallery_board_id')) {
                    document.getElementById('edit_main_widget_gallery_board_id').value = settings.board_id || '';
                }
                if (document.getElementById('edit_main_widget_gallery_display_type')) {
                    document.getElementById('edit_main_widget_gallery_display_type').value = displayType;
                    if (displayType === 'grid') {
                        if (galleryGridContainer) galleryGridContainer.style.display = 'block';
                        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                        if (document.getElementById('edit_main_widget_gallery_cols')) {
                            document.getElementById('edit_main_widget_gallery_cols').value = settings.cols || 3;
                        }
                        if (document.getElementById('edit_main_widget_gallery_rows')) {
                            document.getElementById('edit_main_widget_gallery_rows').value = settings.rows || 3;
                        }
                    } else {
                        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                        if (gallerySlideContainer) gallerySlideContainer.style.display = 'block';
                        if (document.getElementById('edit_main_widget_gallery_slide_cols')) {
                            document.getElementById('edit_main_widget_gallery_slide_cols').value = settings.slide_cols || 3;
                        }
                    }
                }
                if (document.getElementById('edit_main_widget_gallery_show_title')) {
                    document.getElementById('edit_main_widget_gallery_show_title').checked = settings.show_title !== false;
                }
                if (!title || title === '갤러리' || title.trim() === '') {
                    if (document.getElementById('edit_main_widget_title')) {
                        document.getElementById('edit_main_widget_title').value = '';
                    }
                } else {
                    if (document.getElementById('edit_main_widget_title')) {
                        document.getElementById('edit_main_widget_title').value = title;
                    }
                }
            } else if (widgetType === 'tab_menu') {
                if (tabMenuContainer) tabMenuContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                // 탭메뉴 데이터 로드
                const tabs = settings.tabs || [];
                const container = document.getElementById('edit_main_tab_menu_list');
                if (container) {
                    container.innerHTML = '';
                    editMainTabMenuIndex = 0;
                    tabs.forEach((tab, index) => {
                        editMainTabMenuIndex = index;
                        addEditMainTabMenuItem();
                        const item = document.getElementById(`edit_main_tab_menu_item_${index}`);
                        if (item) {
                            const nameInput = item.querySelector('.edit-main-tab-menu-name');
                            const widgetTypeSelect = item.querySelector('.edit-main-tab-menu-widget-type');
                            const limitInput = item.querySelector('.edit-main-tab-menu-limit');
                            if (nameInput) nameInput.value = tab.name || '';
                            if (widgetTypeSelect) widgetTypeSelect.value = tab.widget_type || '';
                            if (limitInput) limitInput.value = tab.limit || 10;
                            if (tab.widget_type === 'board') {
                                const boardContainer = item.querySelector('.edit-main-tab-menu-board-container');
                                if (boardContainer) boardContainer.style.display = 'block';
                                const boardSelect = item.querySelector('.edit-main-tab-menu-board-id');
                                if (boardSelect) boardSelect.value = tab.board_id || '';
                            }
                            // 로드된 항목은 접힌 상태로 시작
                            const body = document.getElementById(`edit_main_tab_menu_item_${index}_body`);
                            const icon = document.getElementById(`edit_main_tab_menu_item_${index}_icon`);
                            if (body && icon) {
                                body.style.display = 'none';
                                icon.className = 'bi bi-chevron-right';
                            }
                        }
                    });
                    editMainTabMenuIndex++;
                }
            } else if (widgetType === 'toggle_menu') {
                if (toggleMenuContainer) toggleMenuContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) {
                    titleInput.required = true;
                    titleInput.value = title || '';
                }
                // 토글 메뉴 목록 로드
                fetch('/site/{{ $site->slug }}/admin/toggle-menus/list')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const select = document.getElementById('edit_main_widget_toggle_menu_id');
                            if (select) {
                                select.innerHTML = '<option value="">선택하세요</option>';
                                const selectedId = settings.toggle_menu_id || settings.toggle_menu_ids?.[0] || null;
                                data.toggleMenus.forEach(toggleMenu => {
                                    const option = document.createElement('option');
                                    option.value = toggleMenu.id;
                                    option.textContent = toggleMenu.name;
                                    if (selectedId && selectedId == toggleMenu.id) {
                                        option.selected = true;
                                    }
                                    select.appendChild(option);
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading toggle menus:', error);
                    });
            } else if (widgetType === 'user_ranking') {
                if (rankingContainer) rankingContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (document.getElementById('edit_main_widget_rank_ranking')) {
                    document.getElementById('edit_main_widget_rank_ranking').checked = settings.enable_rank_ranking || false;
                }
                if (document.getElementById('edit_main_widget_point_ranking')) {
                    document.getElementById('edit_main_widget_point_ranking').checked = settings.enable_point_ranking || false;
                }
                if (document.getElementById('edit_main_widget_ranking_limit')) {
                    document.getElementById('edit_main_widget_ranking_limit').value = settings.ranking_limit || 5;
                }
            } else if (widgetType === 'custom_html') {
                if (customHtmlContainer) customHtmlContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_main_widget_custom_html')) {
                    document.getElementById('edit_main_widget_custom_html').value = settings.html || settings.custom_html || '';
                }
                if (document.getElementById('edit_main_widget_title')) {
                    document.getElementById('edit_main_widget_title').value = title;
                }
            } else if (widgetType === 'block') {
                if (blockContainer) blockContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                if (document.getElementById('edit_main_widget_block_title')) {
                    document.getElementById('edit_main_widget_block_title').value = settings.block_title || '';
                }
                if (document.getElementById('edit_main_widget_block_content')) {
                    document.getElementById('edit_main_widget_block_content').value = settings.block_content || '';
                }
                const textAlign = settings.text_align || 'left';
                const textAlignRadio = document.querySelector(`input[name="edit_main_block_text_align"][value="${textAlign}"]`);
                if (textAlignRadio) textAlignRadio.checked = true;
                
                const backgroundType = settings.background_type || 'color';
                if (document.getElementById('edit_main_widget_block_background_type')) {
                    document.getElementById('edit_main_widget_block_background_type').value = backgroundType;
                    handleEditMainBlockBackgroundTypeChange();
                }
                
                if (backgroundType === 'color') {
                    if (document.getElementById('edit_main_widget_block_background_color')) {
                        document.getElementById('edit_main_widget_block_background_color').value = settings.background_color || '#007bff';
                    }
                } else if (backgroundType === 'image') {
                    if (settings.background_image_url && document.getElementById('edit_main_widget_block_image_preview_img')) {
                        document.getElementById('edit_main_widget_block_image_preview_img').src = settings.background_image_url;
                        document.getElementById('edit_main_widget_block_image_preview').style.display = 'block';
                        document.getElementById('edit_main_widget_block_background_image').value = settings.background_image_url;
                    }
                }
                
                if (document.getElementById('edit_main_widget_block_font_color')) {
                    document.getElementById('edit_main_widget_block_font_color').value = settings.font_color || '#ffffff';
                }
                if (document.getElementById('edit_main_widget_block_title_font_size')) {
                    // rem을 px로 변환 (1rem = 16px 기본값)
                    let titleSize = settings.title_font_size || '16';
                    if (titleSize.includes('rem')) {
                        titleSize = parseFloat(titleSize) * 16;
                    }
                    document.getElementById('edit_main_widget_block_title_font_size').value = titleSize;
                }
                if (document.getElementById('edit_main_widget_block_content_font_size')) {
                    let contentSize = settings.content_font_size || '14';
                    if (contentSize.includes('rem')) {
                        contentSize = parseFloat(contentSize) * 16;
                    }
                    document.getElementById('edit_main_widget_block_content_font_size').value = contentSize;
                }
                
                if (document.getElementById('edit_main_widget_block_padding_top')) {
                    document.getElementById('edit_main_widget_block_padding_top').value = settings.padding_top || 20;
                }
                if (document.getElementById('edit_main_widget_block_padding_left')) {
                    document.getElementById('edit_main_widget_block_padding_left').value = settings.padding_left || 20;
                }
                if (document.getElementById('edit_main_widget_block_link')) {
                    document.getElementById('edit_main_widget_block_link').value = settings.link || '';
                }
                if (document.getElementById('edit_main_widget_block_open_new_tab')) {
                    document.getElementById('edit_main_widget_block_open_new_tab').checked = settings.open_new_tab || false;
                }
                
                // 버튼 데이터 로드
                const buttonsContainer = document.getElementById('edit_main_widget_block_buttons_list');
                if (buttonsContainer) {
                    buttonsContainer.innerHTML = '';
                    editMainBlockButtonIndex = 0;
                    
                    // 기존 버튼 데이터 로드 (하위 호환성: 기존 단일 버튼 데이터도 지원)
                    let buttons = settings.buttons || [];
                    if (!Array.isArray(buttons) && settings.show_button && settings.button_text) {
                        // 기존 단일 버튼 데이터를 배열로 변환
                        buttons = [{
                            text: settings.button_text || '',
                            link: settings.link || '',
                            open_new_tab: settings.open_new_tab || false,
                            background_color: settings.button_background_color || '#007bff',
                            text_color: settings.button_text_color || '#ffffff'
                        }];
                    }
                    
                    buttons.forEach((button, index) => {
                        if (button.text) {
                            addEditMainBlockButton();
                            const buttonId = `edit_main_block_button_${editMainBlockButtonIndex - 1}`;
                            const buttonCard = document.getElementById(buttonId);
                            if (buttonCard) {
                                buttonCard.querySelector('.edit-main-block-button-text').value = button.text || '';
                                buttonCard.querySelector('.edit-main-block-button-link').value = button.link || '';
                                buttonCard.querySelector('.edit-main-block-button-open-new-tab').checked = button.open_new_tab || false;
                                buttonCard.querySelector('.edit-main-block-button-background-color').value = button.background_color || '#007bff';
                                buttonCard.querySelector('.edit-main-block-button-text-color').value = button.text_color || '#ffffff';
                            }
                        }
                    });
                    
                    // 버튼이 있으면 연결 링크 필드 숨기기
                    const linkContainer = document.getElementById('edit_main_widget_block_link_container');
                    if (linkContainer && buttons.length > 0) {
                        linkContainer.style.display = 'none';
                    }
                }
                
                if (document.getElementById('edit_main_widget_block_button_top_margin')) {
                    document.getElementById('edit_main_widget_block_button_top_margin').value = settings.button_top_margin || 12;
                }
            } else if (widgetType === 'block_slide') {
                if (blockSlideContainer) blockSlideContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                const slideDirection = settings.slide_direction || 'left';
                const directionRadio = document.querySelector(`input[name="edit_main_block_slide_direction"][value="${slideDirection}"]`);
                if (directionRadio) directionRadio.checked = true;
                
                const blocks = settings.blocks || [];
                const itemsContainer = document.getElementById('edit_main_widget_block_slide_items');
                if (itemsContainer) {
                    itemsContainer.innerHTML = '';
                    editMainBlockSlideItemIndex = 0;
                    blocks.forEach((block) => {
                        addEditMainBlockSlideItem(block);
                    });
                }
            } else if (widgetType === 'image') {
                if (imageContainer) imageContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                if (settings.image_url && document.getElementById('edit_main_widget_image_preview_img')) {
                    document.getElementById('edit_main_widget_image_preview_img').src = settings.image_url;
                    document.getElementById('edit_main_widget_image_preview').style.display = 'block';
                    document.getElementById('edit_main_widget_image_url').value = settings.image_url;
                }
                if (document.getElementById('edit_main_widget_image_link')) {
                    document.getElementById('edit_main_widget_image_link').value = settings.image_link || '';
                }
                if (document.getElementById('edit_main_widget_image_open_new_tab')) {
                    document.getElementById('edit_main_widget_image_open_new_tab').checked = settings.image_open_new_tab || false;
                }
            } else if (widgetType === 'image_slide') {
                if (imageSlideContainer) imageSlideContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                const slideDirection = settings.slide_direction || 'left';
                const directionRadio = document.querySelector(`input[name="edit_main_image_slide_direction"][value="${slideDirection}"]`);
                if (directionRadio) directionRadio.checked = true;
                
                const slideMode = settings.slide_mode || 'single';
                const singleCheckbox = document.getElementById('edit_main_widget_image_slide_single');
                const infiniteCheckbox = document.getElementById('edit_main_widget_image_slide_infinite');
                const visibleCountInput = document.getElementById('edit_main_widget_image_slide_visible_count');
                const visibleCountMobileInput = document.getElementById('edit_main_widget_image_slide_visible_count_mobile');
                const gapInput = document.getElementById('edit_main_widget_image_slide_gap');
                
                if (slideMode === 'infinite') {
                    if (singleCheckbox) singleCheckbox.checked = false;
                    if (infiniteCheckbox) infiniteCheckbox.checked = true;
                    if (visibleCountInput) {
                        visibleCountInput.value = settings.visible_count || 3;
                        document.getElementById('edit_main_widget_image_slide_visible_count_container').style.display = 'block';
                    }
                    if (visibleCountMobileInput) {
                        visibleCountMobileInput.value = settings.visible_count_mobile || 2;
                        document.getElementById('edit_main_widget_image_slide_visible_count_mobile_container').style.display = 'block';
                    }
                    if (gapInput) {
                        gapInput.value = settings.image_gap || 0;
                        document.getElementById('edit_main_widget_image_slide_gap_container').style.display = 'block';
                    }
                    // 배경색 설정 로드
                    const backgroundTypeSelect = document.getElementById('edit_main_widget_image_slide_background_type');
                    const backgroundColorInput = document.getElementById('edit_main_widget_image_slide_background_color');
                    const backgroundContainer = document.getElementById('edit_main_widget_image_slide_background_container');
                    if (backgroundContainer) {
                        backgroundContainer.style.display = 'block';
                    }
                    if (backgroundTypeSelect) {
                        backgroundTypeSelect.value = settings.background_type || 'none';
                        handleEditMainImageSlideBackgroundTypeChange();
                    }
                    if (backgroundColorInput && settings.background_color) {
                        backgroundColorInput.value = settings.background_color;
                    }
                } else {
                    if (singleCheckbox) singleCheckbox.checked = true;
                    if (infiniteCheckbox) infiniteCheckbox.checked = false;
                }
                
                handleEditMainImageSlideModeChange();
                
                const images = settings.images || [];
                const itemsContainer = document.getElementById('edit_main_widget_image_slide_items');
                if (itemsContainer) {
                    itemsContainer.innerHTML = '';
                    editMainImageSlideItemIndex = 0;
                    images.forEach((imageItem) => {
                        addEditMainImageSlideItem(imageItem);
                    });
                }
            } else if (widgetType === 'contact_form') {
                if (contactFormContainer) contactFormContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                
                // 컨텍트폼 목록을 동적으로 업데이트
                const contactFormSelect = document.getElementById('edit_main_widget_contact_form_id');
                if (contactFormSelect) {
                    // 기존 옵션 제거 (첫 번째 "선택하세요" 옵션 제외)
                    while (contactFormSelect.options.length > 1) {
                        contactFormSelect.remove(1);
                    }
                    
                    // 컨텍트폼 목록 가져오기
                    fetch(`{{ route("admin.contact-forms.index", ["site" => $site->slug]) }}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.contactForms) {
                            data.contactForms.forEach(contactForm => {
                                const option = document.createElement('option');
                                option.value = contactForm.id;
                                option.textContent = contactForm.title || '제목 없음';
                                contactFormSelect.appendChild(option);
                            });
                            
                            // 저장된 contact_form_id 설정
                            if (settings.contact_form_id) {
                                contactFormSelect.value = settings.contact_form_id;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('컨텍트폼 목록을 가져오는 중 오류:', error);
                        // 오류 발생 시 기존 서버 사이드 옵션 사용
                        if (settings.contact_form_id) {
                            contactFormSelect.value = settings.contact_form_id;
                        }
                    });
                }
                
                if (document.getElementById('edit_main_widget_title')) {
                    document.getElementById('edit_main_widget_title').value = title;
                }
            } else if (widgetType === 'map') {
                if (mapContainer) mapContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_main_widget_map_id')) {
                    document.getElementById('edit_main_widget_map_id').value = settings.map_id || '';
                }
                if (document.getElementById('edit_main_widget_title')) {
                    document.getElementById('edit_main_widget_title').value = title;
                }
            } else if (widgetType === 'create_site') {
                if (createSiteContainer) createSiteContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_main_widget_create_site_title')) {
                    document.getElementById('edit_main_widget_create_site_title').value = settings.title || '나만의 홈페이지를 만들어보세요!';
                }
                if (document.getElementById('edit_main_widget_create_site_description')) {
                    document.getElementById('edit_main_widget_create_site_description').value = settings.description || '회원가입 후 간단한 정보만 입력하면 바로 홈페이지를 생성할 수 있습니다.';
                }
                if (document.getElementById('edit_main_widget_create_site_button_text')) {
                    document.getElementById('edit_main_widget_create_site_button_text').value = settings.button_text || '새 사이트 만들기';
                }
                if (document.getElementById('edit_main_widget_create_site_button_link')) {
                    document.getElementById('edit_main_widget_create_site_button_link').value = settings.button_link || '{{ route('user-sites.select-plan', ['site' => $site->slug]) }}';
                }
                if (document.getElementById('edit_main_widget_create_site_icon')) {
                    document.getElementById('edit_main_widget_create_site_icon').value = settings.icon || 'bi-rocket-takeoff';
                }
                if (document.getElementById('edit_main_widget_create_site_background_color')) {
                    document.getElementById('edit_main_widget_create_site_background_color').value = settings.background_color || '#007bff';
                }
                if (document.getElementById('edit_main_widget_create_site_text_color')) {
                    document.getElementById('edit_main_widget_create_site_text_color').value = settings.text_color || '#ffffff';
                }
                if (document.getElementById('edit_main_widget_create_site_button_bg_color')) {
                    document.getElementById('edit_main_widget_create_site_button_bg_color').value = settings.button_bg_color || '#0056b3';
                }
                if (document.getElementById('edit_main_widget_create_site_button_color')) {
                    document.getElementById('edit_main_widget_create_site_button_color').value = settings.button_color || '#ffffff';
                }
                if (document.getElementById('edit_main_widget_create_site_show_only_when_logged_in')) {
                    document.getElementById('edit_main_widget_create_site_show_only_when_logged_in').checked = settings.show_only_when_logged_in !== false;
                }
                if (document.getElementById('edit_main_widget_title')) {
                    document.getElementById('edit_main_widget_title').value = title || '';
                }
            } else if (widgetType === 'countdown') {
                if (countdownContainer) countdownContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                // 카운트다운 설정 로드
                if (document.getElementById('edit_main_widget_countdown_title')) {
                    document.getElementById('edit_main_widget_countdown_title').value = settings.countdown_title || '';
                }
                if (document.getElementById('edit_main_widget_countdown_content')) {
                    document.getElementById('edit_main_widget_countdown_content').value = settings.countdown_content || '';
                }
                const countdownType = settings.countdown_type || 'dday';
                if (document.getElementById('edit_main_widget_countdown_type')) {
                    document.getElementById('edit_main_widget_countdown_type').value = countdownType;
                    handleEditCountdownTypeChange();
                }
                
                if (countdownType === 'dday') {
                    if (document.getElementById('edit_main_widget_countdown_target_date')) {
                        // datetime-local 형식으로 변환 (YYYY-MM-DDTHH:mm)
                        const targetDate = settings.countdown_target_date || '';
                        if (targetDate) {
                            const date = new Date(targetDate);
                            const year = date.getFullYear();
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const day = String(date.getDate()).padStart(2, '0');
                            const hours = String(date.getHours()).padStart(2, '0');
                            const minutes = String(date.getMinutes()).padStart(2, '0');
                            document.getElementById('edit_main_widget_countdown_target_date').value = `${year}-${month}-${day}T${hours}:${minutes}`;
                        }
                    }
                    if (document.getElementById('edit_main_widget_countdown_dday_animation')) {
                        document.getElementById('edit_main_widget_countdown_dday_animation').checked = settings.countdown_dday_animation_enabled || false;
                    }
                } else if (countdownType === 'number') {
                    if (document.getElementById('edit_main_widget_countdown_animation')) {
                        // 구키(countdown_animation)와 신키(countdown_animation_enabled) 모두 대응
                        const anim = settings.countdown_animation_enabled;
                        const animLegacy = settings.countdown_animation;
                        document.getElementById('edit_main_widget_countdown_animation').checked =
                            (anim !== undefined ? anim : animLegacy) !== false;
                    }
                    
                    // 숫자 카운트 항목 로드
                    const numberItems = settings.countdown_number_items || [];
                    const itemsContainer = document.getElementById('edit_main_widget_countdown_number_items');
                    if (itemsContainer) {
                        itemsContainer.innerHTML = '';
                        editCountdownNumberItemIndex = 0;
                        numberItems.forEach((item) => {
                            addEditCountdownNumberItem(item);
                        });
                    }
                }
            } else {
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_main_widget_title')) {
                    document.getElementById('edit_main_widget_title').value = title;
                }
            }
            
            // 모든 설정이 완료된 후 모달 열기
            const modal = new bootstrap.Modal(document.getElementById('mainWidgetSettingsModal'));
            modal.show();
        } else {
            alert('위젯 정보를 가져오는데 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error fetching widget:', error);
        alert('위젯 정보를 가져오는 중 오류가 발생했습니다.');
    });
}

// 위젯 위로 이동
function moveMainWidgetUp(widgetId, containerId, columnIndex) {
    const widgetList = document.querySelector(`.widget-list-in-column[data-container-id="${containerId}"][data-column-index="${columnIndex}"]`);
    if (!widgetList) return;
    
    const widgetItem = widgetList.querySelector(`[data-widget-id="${widgetId}"]`);
    if (!widgetItem) return;
    
    const previousItem = widgetItem.previousElementSibling;
    if (!previousItem || !previousItem.classList.contains('widget-item')) {
        return; // 이미 맨 위에 있거나 이전 항목이 위젯이 아님
    }
    
    // DOM에서 위치 변경
    widgetList.insertBefore(widgetItem, previousItem);
    
    // 순서 저장
    saveMainWidgetOrder(containerId, columnIndex);
}

// 위젯 아래로 이동
function moveMainWidgetDown(widgetId, containerId, columnIndex) {
    const widgetList = document.querySelector(`.widget-list-in-column[data-container-id="${containerId}"][data-column-index="${columnIndex}"]`);
    if (!widgetList) return;
    
    const widgetItem = widgetList.querySelector(`[data-widget-id="${widgetId}"]`);
    if (!widgetItem) return;
    
    const nextItem = widgetItem.nextElementSibling;
    if (!nextItem || !nextItem.classList.contains('widget-item')) {
        return; // 이미 맨 아래에 있거나 다음 항목이 위젯이 아님
    }
    
    // DOM에서 위치 변경
    widgetList.insertBefore(nextItem, widgetItem);
    
    // 순서 저장
    saveMainWidgetOrder(containerId, columnIndex);
}

// 위젯 순서 저장
function saveMainWidgetOrder(containerId, columnIndex) {
    const widgetList = document.querySelector(`.widget-list-in-column[data-container-id="${containerId}"][data-column-index="${columnIndex}"]`);
    if (!widgetList) return;
    
    const widgets = Array.from(widgetList.querySelectorAll('.widget-item'));
    const widgetData = widgets.map((item, index) => ({
        id: parseInt(item.dataset.widgetId),
        order: index + 1
    }));
    
    fetch('{{ route("admin.main-widgets.reorder", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            container_id: containerId,
            column_index: columnIndex,
            widgets: widgetData 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('순서 저장 실패:', data.message);
            alert('위젯 순서 저장에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
            location.reload(); // 실패 시 새로고침
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('위젯 순서 저장 중 오류가 발생했습니다.');
        location.reload(); // 오류 시 새로고침
    });
}

// 위젯 삭제
function deleteMainWidget(widgetId) {
    if (!confirm('위젯을 삭제하시겠습니까?')) {
        return;
    }
    
    fetch('{{ route("admin.main-widgets.delete", ["site" => $site->slug, "widget" => ":widgetId"]) }}'.replace(':widgetId', widgetId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('위젯 삭제에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('위젯 삭제 중 오류가 발생했습니다.');
    });
}

// 위젯 타입 변경 시 필드 표시/숨김 처리 (사이드 위젯 페이지의 로직과 동일)
document.addEventListener('DOMContentLoaded', function() {
    // Tooltip 초기화
    if (!window.tooltipsInitialized) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        window.tooltipsInitialized = true;
    }
    
    // 가로 100% 체크박스 이벤트 리스너 추가
    // 가로 100% 체크박스 이벤트 리스너 추가 및 동기화
    document.querySelectorAll('.container-full-width-checkbox').forEach(function(checkbox) {
        if (!checkbox.disabled && checkbox.dataset.containerId) {
            checkbox.addEventListener('change', function() {
                const containerId = parseInt(this.dataset.containerId);
                const fullWidth = this.checked;
                
                // 데스크탑과 모바일 체크박스 동기화
                const desktopCheckbox = document.getElementById(`container_full_width_${containerId}`);
                const mobileCheckbox = document.getElementById(`container_full_width_mobile_${containerId}`);
                if (desktopCheckbox && desktopCheckbox !== this) {
                    desktopCheckbox.checked = fullWidth;
                }
                if (mobileCheckbox && mobileCheckbox !== this) {
                    mobileCheckbox.checked = fullWidth;
                }
                
                updateContainerFullWidth(containerId, fullWidth);
            });
        }
    });
    
    // 세로 100% 체크박스 이벤트 리스너 추가 및 동기화
    document.querySelectorAll('.container-full-height-checkbox').forEach(function(checkbox) {
        if (checkbox.dataset.containerId) {
            checkbox.addEventListener('change', function() {
                const containerId = parseInt(this.dataset.containerId);
                const fullHeight = this.checked;
                
                // 데스크탑과 모바일 체크박스 동기화
                const desktopCheckbox = document.getElementById(`container_full_height_${containerId}`);
                const mobileCheckbox = document.getElementById(`container_full_height_mobile_${containerId}`);
                if (desktopCheckbox && desktopCheckbox !== this) {
                    desktopCheckbox.checked = fullHeight;
                }
                if (mobileCheckbox && mobileCheckbox !== this) {
                    mobileCheckbox.checked = fullHeight;
                }
                
                updateContainerFullHeight(containerId, fullHeight);
            });
        }
    });
    const widgetTypeSelect = document.getElementById('widget_type');
    if (widgetTypeSelect) {
        widgetTypeSelect.addEventListener('change', function() {
            const widgetType = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const description = selectedOption.dataset.description;
            const descriptionDiv = document.getElementById('widget_type_description');
            const descriptionText = document.getElementById('widget_type_description_text');
            
            // 설명 표시/숨김
            if (description && descriptionDiv) {
                descriptionText.textContent = description;
                descriptionDiv.style.display = 'block';
            } else if (descriptionDiv) {
                descriptionDiv.style.display = 'none';
            }
            
            // 위젯 타입에 따라 필드 표시/숨김 (사이드 위젯 페이지의 로직과 동일)
            const limitContainer = document.getElementById('widget_limit_container');
            const rankingContainer = document.getElementById('widget_ranking_container');
            const titleContainer = document.getElementById('widget_title_container');
            const titleInput = document.getElementById('widget_title');
            const boardContainer = document.getElementById('widget_board_container');
            const sortOrderContainer = document.getElementById('widget_sort_order_container');
            const marqueeDirectionContainer = document.getElementById('widget_marquee_direction_container');
            const blockContainer = document.getElementById('widget_block_container');
            const blockSlideContainer = document.getElementById('widget_block_slide_container');
            const imageContainer = document.getElementById('widget_image_container');
            const imageSlideContainer = document.getElementById('widget_image_slide_container');
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            const titleHelp = document.getElementById('widget_title_help');
            
            // 모든 컨테이너 숨김
            if (limitContainer) limitContainer.style.display = 'none';
            if (rankingContainer) rankingContainer.style.display = 'none';
            if (boardContainer) boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            if (blockContainer) blockContainer.style.display = 'none';
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            if (imageContainer) imageContainer.style.display = 'none';
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const contactFormContainer = document.getElementById('widget_contact_form_container');
            if (contactFormContainer) contactFormContainer.style.display = 'none';
            const mapContainer = document.getElementById('widget_map_container');
            if (mapContainer) mapContainer.style.display = 'none';
            const toggleMenuContainer = document.getElementById('widget_toggle_menu_container');
            if (toggleMenuContainer) toggleMenuContainer.style.display = 'none';
            const countdownContainer = document.getElementById('widget_countdown_container');
            if (countdownContainer) countdownContainer.style.display = 'none';
            if (titleHelp) titleHelp.style.display = 'none';
            
            if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
                if (limitContainer) limitContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = true;
            } else if (widgetType === 'board') {
                if (limitContainer) limitContainer.style.display = 'block';
                if (boardContainer) boardContainer.style.display = 'block';
                if (sortOrderContainer) sortOrderContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = true;
            } else if (widgetType === 'marquee_board') {
                if (limitContainer) limitContainer.style.display = 'block';
                if (boardContainer) boardContainer.style.display = 'block';
                if (sortOrderContainer) sortOrderContainer.style.display = 'block';
                if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (titleInput) {
                    titleInput.required = false;
                    titleInput.value = '게시글 전광판';
                }
            } else if (widgetType === 'gallery') {
                if (galleryContainer) galleryContainer.style.display = 'block';
                if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'block';
                if (galleryGridContainer) galleryGridContainer.style.display = 'block';
                if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = false;
                if (titleHelp) titleHelp.style.display = 'inline';
                const displayTypeSelect = document.getElementById('widget_gallery_display_type');
                if (displayTypeSelect) {
                    displayTypeSelect.value = 'grid';
                    if (galleryGridContainer) galleryGridContainer.style.display = 'block';
                    if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                }
            } else if (widgetType === 'tab_menu') {
                if (titleContainer) titleContainer.style.display = 'none';
                if (titleInput) {
                    titleInput.required = false;
                    titleInput.value = '탭메뉴';
                }
            } else if (widgetType === 'toggle_menu') {
                if (toggleMenuContainer) toggleMenuContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) {
                    titleInput.required = true;
                    if (!titleInput.value || titleInput.value === '토글 메뉴') {
                        titleInput.value = '';
                    }
                }
                // 토글 메뉴 목록 로드
                fetch('/site/{{ $site->slug }}/admin/toggle-menus/list')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const select = document.getElementById('widget_toggle_menu_id');
                            if (select) {
                                select.innerHTML = '<option value="">선택하세요</option>';
                                data.toggleMenus.forEach(toggleMenu => {
                                    const option = document.createElement('option');
                                    option.value = toggleMenu.id;
                                    option.textContent = toggleMenu.name;
                                    select.appendChild(option);
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading toggle menus:', error);
                    });
            } else if (widgetType === 'user_ranking') {
                if (rankingContainer) rankingContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (titleInput) {
                    titleInput.required = false;
                    titleInput.value = '회원 랭킹';
                }
            } else if (widgetType === 'custom_html') {
                if (customHtmlContainer) customHtmlContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = true;
            } else if (widgetType === 'block') {
                if (blockContainer) blockContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (titleInput) titleInput.required = false;
            } else if (widgetType === 'block_slide') {
                if (blockSlideContainer) blockSlideContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (titleInput) titleInput.required = false;
                const itemsContainer = document.getElementById('widget_block_slide_items');
                if (itemsContainer && itemsContainer.children.length === 0) {
                    addBlockSlideItem();
                }
            } else if (widgetType === 'image') {
                if (imageContainer) imageContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (titleInput) titleInput.required = false;
            } else if (widgetType === 'image_slide') {
                if (imageSlideContainer) imageSlideContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (titleInput) titleInput.required = false;
                const itemsContainer = document.getElementById('widget_image_slide_items');
                if (itemsContainer && itemsContainer.children.length === 0) {
                    addImageSlideItem();
                }
                // 초기 모드 설정
                handleImageSlideModeChange();
            } else if (widgetType === 'contact_form') {
                if (contactFormContainer) contactFormContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = true;
            } else if (widgetType === 'map') {
                if (mapContainer) mapContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = true;
            } else if (widgetType === 'countdown') {
                if (countdownContainer) countdownContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (titleInput) titleInput.required = false;
                // 초기화: D-day 카운트 표시
                const countdownType = document.getElementById('widget_countdown_type');
                if (countdownType) {
                    countdownType.value = 'dday';
                    handleCountdownTypeChange();
                }
            } else {
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = true;
            }
        });
    }
});

// 사이드 위젯 페이지의 공통 함수들 (필요한 경우에만 정의)
function handleGalleryDisplayTypeChange() {
    const displayTypeSelect = document.getElementById('widget_gallery_display_type');
    const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
    const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
    
    if (!displayTypeSelect) return;
    
    if (displayTypeSelect.value === 'grid') {
        if (galleryGridContainer) galleryGridContainer.style.display = 'block';
        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
    } else {
        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
        if (gallerySlideContainer) gallerySlideContainer.style.display = 'block';
    }
}

function handleBlockBackgroundTypeChange() {
    const backgroundType = document.getElementById('widget_block_background_type')?.value;
    const colorContainer = document.getElementById('widget_block_color_container');
    const imageContainer = document.getElementById('widget_block_image_container');
    
    if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'block';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'image') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'block';
    }
}

function handleBlockImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('widget_block_image_preview');
            const previewImg = document.getElementById('widget_block_image_preview_img');
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeBlockImage() {
    const input = document.getElementById('widget_block_image_input');
    const preview = document.getElementById('widget_block_image_preview');
    const imageUrl = document.getElementById('widget_block_background_image');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function handleImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('widget_image_preview');
            const previewImg = document.getElementById('widget_image_preview_img');
            const imageUrl = document.getElementById('widget_image_url');
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const input = document.getElementById('widget_image_input');
    const preview = document.getElementById('widget_image_preview');
    const imageUrl = document.getElementById('widget_image_url');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

// 블록 슬라이드 및 이미지 슬라이드 관련 함수들 (사이드 위젯 페이지와 동일)
let blockSlideItemIndex = 0;
let imageSlideItemIndex = 0;

function addBlockSlideItem() {
    const container = document.getElementById('widget_block_slide_items');
    if (!container) return;
    
    const existingItems = container.querySelectorAll('.block-slide-item');
    existingItems.forEach((existingItem) => {
        const existingItemIndex = existingItem.dataset.itemIndex;
        const existingBody = document.getElementById(`block_slide_item_${existingItemIndex}_body`);
        const existingIcon = document.getElementById(`block_slide_item_${existingItemIndex}_icon`);
        if (existingBody && existingIcon) {
            existingBody.style.display = 'none';
            existingIcon.className = 'bi bi-chevron-right';
        }
    });
    
    const itemIndex = blockSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 block-slide-item';
    item.id = `block_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleBlockSlideItem(itemIndex);
    };
    header.innerHTML = `<span>블록 ${itemIndex + 1}</span><i class="bi bi-chevron-down" id="block_slide_item_${itemIndex}_icon"></i>`;
    
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `block_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3"><label class="form-label">제목</label><input type="text" class="form-control block-slide-title" name="block_slide[${itemIndex}][title]" placeholder="제목을 입력하세요"></div>
        <div class="mb-3"><label class="form-label">내용</label><textarea class="form-control block-slide-content" name="block_slide[${itemIndex}][content]" rows="3" placeholder="내용을 입력하세요"></textarea></div>
        <div class="mb-3"><label class="form-label">텍스트 정렬</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="block_slide[${itemIndex}][text_align]" id="block_slide_${itemIndex}_align_left" value="left" checked>
                <label class="btn btn-outline-primary" for="block_slide_${itemIndex}_align_left"><i class="bi bi-text-left"></i> 좌</label>
                <input type="radio" class="btn-check" name="block_slide[${itemIndex}][text_align]" id="block_slide_${itemIndex}_align_center" value="center">
                <label class="btn btn-outline-primary" for="block_slide_${itemIndex}_align_center"><i class="bi bi-text-center"></i> 중앙</label>
                <input type="radio" class="btn-check" name="block_slide[${itemIndex}][text_align]" id="block_slide_${itemIndex}_align_right" value="right">
                <label class="btn btn-outline-primary" for="block_slide_${itemIndex}_align_right"><i class="bi bi-text-right"></i> 우</label>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">제목 폰트 사이즈 (px)</label>
            <input type="number" 
                   class="form-control block-slide-title-font-size" 
                   name="block_slide[${itemIndex}][title_font_size]" 
                   value="16"
                   min="8"
                   max="72"
                   step="1"
                   placeholder="16">
            <small class="text-muted">기본값: 16px</small>
        </div>
        <div class="mb-3">
            <label class="form-label">내용 폰트 사이즈 (px)</label>
            <input type="number" 
                   class="form-control block-slide-content-font-size" 
                   name="block_slide[${itemIndex}][content_font_size]" 
                   value="14"
                   min="8"
                   max="48"
                   step="1"
                   placeholder="14">
            <small class="text-muted">기본값: 14px</small>
        </div>
        <div class="mb-3"><label class="form-label">배경</label>
            <select class="form-select block-slide-background-type" name="block_slide[${itemIndex}][background_type]" onchange="handleBlockSlideBackgroundTypeChange(${itemIndex})">
                <option value="color">컬러</option>
                <option value="image">이미지</option>
            </select>
        </div>
        <div class="mb-3 block-slide-color-container" id="block_slide_${itemIndex}_color_container">
            <label class="form-label">배경 컬러</label>
            <input type="color" class="form-control form-control-color block-slide-background-color" name="block_slide[${itemIndex}][background_color]" value="#007bff">
        </div>
        <div class="mb-3"><label class="form-label">폰트 컬러</label>
            <input type="color" class="form-control form-control-color block-slide-font-color" name="block_slide[${itemIndex}][font_color]" value="#ffffff">
        </div>
        <div class="mb-3 block-slide-image-container" id="block_slide_${itemIndex}_image_container" style="display: none;">
            <label class="form-label">배경 이미지</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('block_slide_${itemIndex}_image_input').click()"><i class="bi bi-image"></i> 이미지 선택</button>
                <input type="file" id="block_slide_${itemIndex}_image_input" name="block_slide[${itemIndex}][background_image]" accept="image/*" style="display: none;" onchange="handleBlockSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="block-slide-background-image-url" name="block_slide[${itemIndex}][background_image_url]" id="block_slide_${itemIndex}_background_image_url">
                <div class="block-slide-image-preview" id="block_slide_${itemIndex}_image_preview" style="display: none;">
                    <img id="block_slide_${itemIndex}_image_preview_img" src="" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeBlockSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">상단 여백 (px)</label>
            <input type="number" 
                   class="form-control block-slide-padding-top" 
                   name="block_slide[${itemIndex}][padding_top]" 
                   value="20"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 상단 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">하단 여백 (px)</label>
            <input type="number" 
                   class="form-control block-slide-padding-bottom" 
                   name="block_slide[${itemIndex}][padding_bottom]" 
                   value="20"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 하단 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">좌측 여백 (px)</label>
            <input type="number" 
                   class="form-control block-slide-padding-left" 
                   name="block_slide[${itemIndex}][padding_left]" 
                   value="20"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 좌측 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">우측 여백 (px)</label>
            <input type="number" 
                   class="form-control block-slide-padding-right" 
                   name="block_slide[${itemIndex}][padding_right]" 
                   value="20"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 우측 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">제목-내용 여백 (px)</label>
            <input type="number" 
                   class="form-control block-slide-title-content-gap" 
                   name="block_slide[${itemIndex}][title_content_gap]" 
                   value="8"
                   min="0"
                   max="100"
                   step="1"
                   placeholder="8">
            <small class="text-muted">제목과 내용 사이의 여백을 입력하세요 (0~100).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">버튼 관리</label>
            <div class="block-slide-buttons-list" id="block_slide_${itemIndex}_buttons_list">
                <!-- 버튼들이 여기에 동적으로 추가됨 -->
            </div>
            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addBlockSlideButton(${itemIndex})">
                <i class="bi bi-plus-circle me-1"></i>버튼 추가
            </button>
        </div>
        <div class="mb-3">
            <label class="form-label">버튼 상단 여백 (px)</label>
            <input type="number" 
                   class="form-control block-slide-button-top-margin" 
                   name="block_slide[${itemIndex}][button_top_margin]" 
                   value="12"
                   min="0"
                   max="100"
                   step="1"
                   placeholder="12">
            <small class="text-muted">버튼과 위 요소 사이의 여백을 입력하세요 (0~100).</small>
        </div>
        <div class="mb-3" id="block_slide_${itemIndex}_link_container"><label class="form-label">
            연결 링크 <small class="text-muted">(선택사항)</small>
            <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="버튼이 있는 경우 버튼에 링크가 연결되고, 버튼이 없는 경우 블록 전체에 링크가 연결됩니다."></i>
        </label>
            <input type="url" class="form-control block-slide-link" name="block_slide[${itemIndex}][link]" placeholder="https://example.com">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input block-slide-open-new-tab" type="checkbox" name="block_slide[${itemIndex}][open_new_tab]" id="block_slide_${itemIndex}_open_new_tab">
                <label class="form-check-label" for="block_slide_${itemIndex}_open_new_tab">새창에서 열기</label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeBlockSlideItem(${itemIndex})"><i class="bi bi-trash me-1"></i>삭제</button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
    body.style.display = 'block';
}

function toggleBlockSlideItem(itemIndex) {
    const body = document.getElementById(`block_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`block_slide_item_${itemIndex}_icon`);
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

// 블록슬라이드 버튼 관리
let blockSlideButtonIndices = {};

function addBlockSlideButton(itemIndex) {
    if (!blockSlideButtonIndices[itemIndex]) {
        blockSlideButtonIndices[itemIndex] = 0;
    }
    
    const container = document.getElementById(`block_slide_${itemIndex}_buttons_list`);
    if (!container) return;
    
    const buttonIndex = blockSlideButtonIndices[itemIndex];
    const buttonId = `block_slide_${itemIndex}_button_${buttonIndex}`;
    const buttonHtml = `
        <div class="card mb-3" id="${buttonId}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">버튼 ${buttonIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeBlockSlideButton('${buttonId}', ${itemIndex})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 텍스트</label>
                    <input type="text" 
                           class="form-control block-slide-button-text" 
                           name="block_slide[${itemIndex}][buttons][${buttonIndex}][text]" 
                           placeholder="버튼 텍스트를 입력하세요">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 링크</label>
                    <input type="url" 
                           class="form-control block-slide-button-link" 
                           name="block_slide[${itemIndex}][buttons][${buttonIndex}][link]" 
                           placeholder="https://example.com">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input block-slide-button-open-new-tab" 
                               type="checkbox" 
                               name="block_slide[${itemIndex}][buttons][${buttonIndex}][open_new_tab]" 
                               id="block_slide_${itemIndex}_button_${buttonIndex}_open_new_tab">
                        <label class="form-check-label" for="block_slide_${itemIndex}_button_${buttonIndex}_open_new_tab">
                            새창에서 열기
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-slide-button-background-color" 
                               name="block_slide[${itemIndex}][buttons][${buttonIndex}][background_color]" 
                               value="#007bff">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-slide-button-text-color" 
                               name="block_slide[${itemIndex}][buttons][${buttonIndex}][text_color]" 
                               value="#ffffff">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', buttonHtml);
    blockSlideButtonIndices[itemIndex]++;
    
    // 버튼이 추가되면 연결 링크 필드 숨기기
    const linkContainer = document.getElementById(`block_slide_${itemIndex}_link_container`);
    if (linkContainer) {
        linkContainer.style.display = 'none';
    }
}

function removeBlockSlideButton(buttonId, itemIndex) {
    const button = document.getElementById(buttonId);
    if (button) button.remove();
    
    // 버튼이 없으면 연결 링크 필드 보이기
    const container = document.getElementById(`block_slide_${itemIndex}_buttons_list`);
    const linkContainer = document.getElementById(`block_slide_${itemIndex}_link_container`);
    if (linkContainer && container) {
        const buttons = container.querySelectorAll('.card');
        if (buttons.length === 0) {
            linkContainer.style.display = 'block';
        }
    }
}

function removeBlockSlideItem(itemIndex) {
    const item = document.getElementById(`block_slide_item_${itemIndex}`);
    if (item) item.remove();
}

function handleBlockSlideBackgroundTypeChange(itemIndex) {
    const backgroundType = document.querySelector(`#block_slide_item_${itemIndex}_body .block-slide-background-type`)?.value;
    const colorContainer = document.getElementById(`block_slide_${itemIndex}_color_container`);
    const imageContainer = document.getElementById(`block_slide_${itemIndex}_image_container`);
    if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'block';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'image') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'block';
    }
}

function handleBlockSlideImageChange(itemIndex, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`block_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`block_slide_${itemIndex}_image_preview_img`);
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeBlockSlideImage(itemIndex) {
    const input = document.getElementById(`block_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`block_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`block_slide_${itemIndex}_background_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function addImageSlideItem() {
    const container = document.getElementById('widget_image_slide_items');
    if (!container) return;
    
    const existingItems = container.querySelectorAll('.image-slide-item');
    existingItems.forEach((existingItem) => {
        const existingItemIndex = existingItem.dataset.itemIndex;
        const existingBody = document.getElementById(`image_slide_item_${existingItemIndex}_body`);
        const existingIcon = document.getElementById(`image_slide_item_${existingItemIndex}_icon`);
        if (existingBody && existingIcon) {
            existingBody.style.display = 'none';
            existingIcon.className = 'bi bi-chevron-right';
        }
    });
    
    const itemIndex = imageSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 image-slide-item';
    item.id = `image_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleImageSlideItem(itemIndex);
    };
    header.innerHTML = `<span>이미지 ${itemIndex + 1}</span><i class="bi bi-chevron-down" id="image_slide_item_${itemIndex}_icon"></i>`;
    
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `image_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3"><label class="form-label">이미지 선택</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('image_slide_${itemIndex}_image_input').click()"><i class="bi bi-image"></i> 이미지 선택</button>
                <input type="file" id="image_slide_${itemIndex}_image_input" name="image_slide[${itemIndex}][image_file]" accept="image/*" style="display: none;" onchange="handleImageSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="image-slide-image-url" name="image_slide[${itemIndex}][image_url]" id="image_slide_${itemIndex}_image_url">
                <div class="image-slide-image-preview" id="image_slide_${itemIndex}_image_preview" style="display: none;">
                    <img id="image_slide_${itemIndex}_image_preview_img" src="" alt="미리보기" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeImageSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3"><label class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
            <input type="url" class="form-control image-slide-link" name="image_slide[${itemIndex}][link]" placeholder="https://example.com">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input image-slide-open-new-tab" type="checkbox" name="image_slide[${itemIndex}][open_new_tab]" id="image_slide_${itemIndex}_open_new_tab">
                <label class="form-check-label" for="image_slide_${itemIndex}_open_new_tab">새창에서 열기</label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeImageSlideItem(${itemIndex})"><i class="bi bi-trash me-1"></i>삭제</button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
    body.style.display = 'block';
}

function toggleImageSlideItem(itemIndex) {
    const body = document.getElementById(`image_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`image_slide_item_${itemIndex}_icon`);
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

function removeImageSlideItem(itemIndex) {
    const item = document.getElementById(`image_slide_item_${itemIndex}`);
    if (item) item.remove();
}

function handleImageSlideImageChange(itemIndex, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`image_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`image_slide_${itemIndex}_image_preview_img`);
            const imageUrl = document.getElementById(`image_slide_${itemIndex}_image_url`);
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImageSlideImage(itemIndex) {
    const input = document.getElementById(`image_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`image_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`image_slide_${itemIndex}_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function handleImageSlideModeChange() {
    const singleCheckbox = document.getElementById('widget_image_slide_single');
    const infiniteCheckbox = document.getElementById('widget_image_slide_infinite');
    const visibleCountContainer = document.getElementById('widget_image_slide_visible_count_container');
    const visibleCountMobileContainer = document.getElementById('widget_image_slide_visible_count_mobile_container');
    const gapContainer = document.getElementById('widget_image_slide_gap_container');
    const backgroundContainer = document.getElementById('widget_image_slide_background_container');
    const directionGroup = document.getElementById('image_slide_direction_group');
    const upRadio = document.getElementById('image_slide_direction_up');
    const downRadio = document.getElementById('image_slide_direction_down');
    const upLabel = upRadio ? upRadio.nextElementSibling : null;
    const downLabel = downRadio ? downRadio.nextElementSibling : null;
    
    // 체크박스 상호 배타적 처리 - 무한루프 체크를 먼저 확인
    if (infiniteCheckbox && infiniteCheckbox.checked) {
        if (singleCheckbox) singleCheckbox.checked = false;
        if (visibleCountContainer) visibleCountContainer.style.display = 'block';
        if (visibleCountMobileContainer) visibleCountMobileContainer.style.display = 'block';
        if (gapContainer) gapContainer.style.display = 'block';
        if (backgroundContainer) backgroundContainer.style.display = 'block';
        
        // 무한루프 슬라이드일 때 상하 방향 비활성화
        if (upRadio) {
            upRadio.disabled = true;
            if (upLabel) upLabel.classList.add('disabled');
        }
        if (downRadio) {
            downRadio.disabled = true;
            if (downLabel) downLabel.classList.add('disabled');
        }
        
        // 상하 방향이 선택되어 있으면 좌로 변경
        if (upRadio && upRadio.checked) {
            const leftRadio = document.getElementById('image_slide_direction_left');
            if (leftRadio) leftRadio.checked = true;
        }
        if (downRadio && downRadio.checked) {
            const leftRadio = document.getElementById('image_slide_direction_left');
            if (leftRadio) leftRadio.checked = true;
        }
    } else {
        if (visibleCountContainer) visibleCountContainer.style.display = 'none';
        
        // 1단 슬라이드일 때 상하 방향 활성화
        if (upRadio) {
            upRadio.disabled = false;
            if (upLabel) upLabel.classList.remove('disabled');
        }
        if (downRadio) {
            downRadio.disabled = false;
            if (downLabel) downLabel.classList.remove('disabled');
        }
    }
    
    // 체크박스 상호 배타적 처리 - 1단 슬라이드가 체크되면 무한루프 해제
    if (singleCheckbox && singleCheckbox.checked) {
        if (infiniteCheckbox) infiniteCheckbox.checked = false;
    }
    
    // 툴팁 초기화
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function handleImageSlideBackgroundTypeChange() {
    const backgroundType = document.getElementById('widget_image_slide_background_type')?.value || 'none';
    const colorContainer = document.getElementById('widget_image_slide_background_color_container');
    if (colorContainer) {
        colorContainer.style.display = backgroundType === 'color' ? 'block' : 'none';
    }
}

function handleEditMainImageSlideBackgroundTypeChange() {
    const backgroundType = document.getElementById('edit_main_widget_image_slide_background_type')?.value || 'none';
    const colorContainer = document.getElementById('edit_main_widget_image_slide_background_color_container');
    if (colorContainer) {
        colorContainer.style.display = backgroundType === 'color' ? 'block' : 'none';
    }
}

// 메인 위젯 수정 관련 함수들
let editMainTabMenuIndex = 0;
let editMainBlockSlideItemIndex = 0;
let editMainImageSlideItemIndex = 0;

// 메인 위젯 애니메이션 모달 열기
function openMainWidgetAnimationModal(widgetId) {
    document.getElementById('main_widget_animation_id').value = widgetId;
    
    // 기존 애니메이션 설정 불러오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (widgetItem) {
        const settings = widgetItem.dataset.widgetSettings ? JSON.parse(widgetItem.dataset.widgetSettings) : {};
        const animationDirection = settings.animation_direction || 'none';
        const animationDelay = settings.animation_delay || 0;
        
        // 방향 버튼 선택 상태 초기화
        document.querySelectorAll('.animation-direction-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // 선택된 방향 버튼 활성화
        const selectedBtn = document.querySelector(`.animation-direction-btn[data-direction="${animationDirection}"]`);
        if (selectedBtn) {
            selectedBtn.classList.add('active');
        }
        
        document.getElementById('main_widget_animation_direction').value = animationDirection;
        document.getElementById('main_widget_animation_delay').value = animationDelay;
    } else {
        // 기본값 설정
        document.querySelectorAll('.animation-direction-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector('.animation-direction-btn[data-direction="none"]').classList.add('active');
        document.getElementById('main_widget_animation_direction').value = 'none';
        document.getElementById('main_widget_animation_delay').value = 0;
    }
    
    // 툴팁 초기화
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('#mainWidgetAnimationModal [data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const modal = new bootstrap.Modal(document.getElementById('mainWidgetAnimationModal'));
    modal.show();
}

// 애니메이션 방향 선택
function selectAnimationDirection(direction, button) {
    // 모든 버튼에서 active 클래스 제거
    document.querySelectorAll('.animation-direction-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // 선택된 버튼에 active 클래스 추가
    button.classList.add('active');
    
    // hidden input에 값 설정
    document.getElementById('main_widget_animation_direction').value = direction;
}

// 메인 위젯 애니메이션 설정 저장
function saveMainWidgetAnimation() {
    const widgetId = document.getElementById('main_widget_animation_id').value;
    const animationDirection = document.getElementById('main_widget_animation_direction').value;
    const animationDelay = parseFloat(document.getElementById('main_widget_animation_delay').value) || 0;
    
    if (!widgetId) {
        alert('위젯 ID를 찾을 수 없습니다.');
        return;
    }
    
    // 위젯 정보 가져오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (!widgetItem) {
        alert('위젯을 찾을 수 없습니다.');
        return;
    }
    
    // 기존 설정 가져오기
    let existingSettings = {};
    try {
        const settingsStr = widgetItem.dataset.widgetSettings;
        if (settingsStr) {
            existingSettings = JSON.parse(settingsStr);
        }
    } catch (e) {
        console.error('Error parsing widget settings:', e);
    }
    
    // 애니메이션 설정 추가
    existingSettings.animation_direction = animationDirection;
    existingSettings.animation_delay = animationDelay;
    
    // 위젯 설정 업데이트 API 호출
    const updateRoute = document.getElementById('mainWidgetSettingsModal').getAttribute('data-update-route');
    const actualRoute = updateRoute.replace(':id', widgetId);
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('_method', 'PUT');
    
    // 위젯 기본 정보도 포함
    formData.append('title', widgetItem.dataset.widgetTitle || '');
    formData.append('is_active', widgetItem.dataset.widgetActive || '1');
    
    // 기존 설정 유지하면서 애니메이션 설정만 추가
    const settings = existingSettings;
    formData.append('settings', JSON.stringify(settings));
    
    // 저장 버튼 비활성화
    const saveBtn = document.querySelector('#mainWidgetAnimationModal .btn-primary');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>저장 중...';
    
    fetch(actualRoute, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
        
        if (data.success) {
            // 모달 닫기
            const modal = bootstrap.Modal.getInstance(document.getElementById('mainWidgetAnimationModal'));
            modal.hide();
            
            // 성공 메시지 표시
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>애니메이션 설정이 저장되었습니다.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.page-header').after(alertDiv);
            
            // 3초 후 자동으로 알림 제거
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
            
            // 위젯 아이템의 data 속성 업데이트
            widgetItem.setAttribute('data-widget-settings', JSON.stringify(settings));
        } else {
            alert('저장 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
        alert('저장 중 오류가 발생했습니다.');
    });
}

function saveMainWidgetSettings() {
    const form = document.getElementById('editMainWidgetForm');
    if (!form) {
        alert('폼을 찾을 수 없습니다.');
        return;
    }
    
    // 저장 버튼 비활성화 및 텍스트 변경
    const saveButton = document.getElementById('edit_main_widget_save_btn') || form.querySelector('button[onclick="saveMainWidgetSettings()"]');
    let originalButtonText = '';
    if (saveButton) {
        saveButton.disabled = true;
        originalButtonText = saveButton.innerHTML;
        saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>저장 중...';
    }
    
    // 에러 발생 시 버튼 복원을 위한 함수
    const restoreButton = () => {
        if (saveButton && originalButtonText) {
            saveButton.disabled = false;
            saveButton.innerHTML = originalButtonText;
        }
    };
    
    // form에서 FormData 생성 (모든 필드 포함)
    const formData = new FormData(form);
    const widgetId = document.getElementById('edit_main_widget_id').value;
    
    // 위젯 타입 가져오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    const widgetType = widgetItem ? widgetItem.dataset.widgetType : '';
    
    // is_active 값 처리 (명시적으로 설정)
    const isActiveCheckbox = document.getElementById('edit_main_widget_is_active');
    formData.set('is_active', isActiveCheckbox && isActiveCheckbox.checked ? '1' : '0');
    
    // 제목 처리 - 명시적으로 설정 (form에서 가져온 값이 있어도 덮어쓰기)
    const titleInput = document.getElementById('edit_main_widget_title');
    if (titleInput) {
        const titleValue = titleInput.value.trim();
        if (widgetType === 'gallery' && (!titleValue || titleValue === '')) {
            formData.set('title', '');
        } else {
            // 제목이 있으면 추가, 없으면 빈 문자열로 추가
            formData.set('title', titleValue || '');
        }
    } else {
        // 제목 입력 필드가 없으면 빈 문자열로 추가
        formData.set('title', '');
    }
    
    // settings 객체 생성 - 기존 설정 유지
    let existingSettings = {};
    try {
        const widgetDataStr = sessionStorage.getItem(`widget_${widgetId}_data`);
        if (widgetDataStr) {
            const widgetData = JSON.parse(widgetDataStr);
            if (widgetData && widgetData.settings) {
                existingSettings = widgetData.settings;
            }
        }
        // 위젯 아이템에서 직접 설정 가져오기 (애니메이션 설정 포함)
        if (widgetItem && widgetItem.dataset.widgetSettings) {
            const itemSettings = JSON.parse(widgetItem.dataset.widgetSettings);
            // 애니메이션 설정이 있으면 유지
            if (itemSettings.animation_direction !== undefined) {
                existingSettings.animation_direction = itemSettings.animation_direction;
            }
            if (itemSettings.animation_delay !== undefined) {
                existingSettings.animation_delay = itemSettings.animation_delay;
            }
        }
    } catch (e) {
        console.error('Error parsing existing settings:', e);
    }
    const settings = Object.assign({}, existingSettings);
    
    if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
        const limit = document.getElementById('edit_main_widget_limit')?.value;
        if (limit) {
            settings.limit = parseInt(limit);
        }
    } else if (widgetType === 'board') {
        const boardId = document.getElementById('edit_main_widget_board_id')?.value;
        const limit = document.getElementById('edit_main_widget_limit')?.value;
        const sortOrder = document.getElementById('edit_main_widget_sort_order')?.value;
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (limit) {
            settings.limit = parseInt(limit);
        }
        if (sortOrder) {
            settings.sort_order = sortOrder;
        }
    } else if (widgetType === 'marquee_board') {
        const boardId = document.getElementById('edit_main_widget_board_id')?.value;
        const limit = document.getElementById('edit_main_widget_limit')?.value;
        const sortOrder = document.getElementById('edit_main_widget_sort_order')?.value;
        const directionRadio = document.querySelector('input[name="edit_main_direction"]:checked');
        const direction = directionRadio ? directionRadio.value : 'left';
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (limit) {
            settings.limit = parseInt(limit);
        }
        if (sortOrder) {
            settings.sort_order = sortOrder;
        }
        if (direction) {
            settings.direction = direction;
        }
    } else if (widgetType === 'gallery') {
        const boardId = document.getElementById('edit_main_widget_gallery_board_id')?.value;
        const displayType = document.getElementById('edit_main_widget_gallery_display_type')?.value;
        const showTitle = document.getElementById('edit_main_widget_gallery_show_title')?.checked;
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (displayType) {
            settings.display_type = displayType;
        }
        settings.show_title = showTitle;
        if (displayType === 'grid') {
            const cols = document.getElementById('edit_main_widget_gallery_cols')?.value;
            const rows = document.getElementById('edit_main_widget_gallery_rows')?.value;
            if (cols) {
                settings.cols = parseInt(cols);
            }
            if (rows) {
                settings.rows = parseInt(rows);
            }
            settings.limit = parseInt(cols) * parseInt(rows);
        } else if (displayType === 'slide') {
            const slideCols = document.getElementById('edit_main_widget_gallery_slide_cols')?.value;
            const slideDirectionRadio = document.querySelector('input[name="edit_main_gallery_slide_direction"]:checked');
            const slideDirection = slideDirectionRadio ? slideDirectionRadio.value : 'left';
            if (slideCols) {
                settings.slide_cols = parseInt(slideCols);
            }
            if (slideDirection) {
                settings.slide_direction = slideDirection;
            }
            settings.limit = 10;
        }
    } else if (widgetType === 'tab_menu') {
        // 탭메뉴 설정 수집
        const tabMenus = [];
        const tabMenuItems = document.querySelectorAll('#edit_main_tab_menu_list .edit-main-tab-menu-item');
        tabMenuItems.forEach((item) => {
            const nameInput = item.querySelector('.edit-main-tab-menu-name');
            const widgetTypeSelect = item.querySelector('.edit-main-tab-menu-widget-type');
            const limitInput = item.querySelector('.edit-main-tab-menu-limit');
            const boardSelect = item.querySelector('.edit-main-tab-menu-board-id');
            if (nameInput && widgetTypeSelect) {
                const name = nameInput.value;
                const tabWidgetType = widgetTypeSelect.value;
                const limit = limitInput ? parseInt(limitInput.value) : 10;
                if (name && tabWidgetType) {
                    const tabMenu = {
                        name: name,
                        widget_type: tabWidgetType,
                        limit: limit
                    };
                    if (tabWidgetType === 'board' && boardSelect && boardSelect.value) {
                        tabMenu.board_id = parseInt(boardSelect.value);
                    }
                    tabMenus.push(tabMenu);
                }
            }
        });
        settings.tabs = tabMenus;
        
        // 탭메뉴 이름들을 위젯 제목으로 자동 설정
        const tabTitles = tabMenus.map(tab => tab.name).filter(name => name);
        if (tabTitles.length > 0) {
            formData.set('title', tabTitles.join(' · '));
        }
    } else if (widgetType === 'toggle_menu') {
        // 토글 메뉴 위젯 제목은 사용자가 입력한 값 사용
        // 토글 메뉴 ID 수집
        const toggleMenuSelect = document.getElementById('edit_main_widget_toggle_menu_id');
        if (toggleMenuSelect && toggleMenuSelect.value) {
            settings.toggle_menu_id = parseInt(toggleMenuSelect.value);
        }
    } else if (widgetType === 'user_ranking') {
        const rankRanking = document.getElementById('edit_main_widget_rank_ranking')?.checked;
        const pointRanking = document.getElementById('edit_main_widget_point_ranking')?.checked;
        const rankingLimit = document.getElementById('edit_main_widget_ranking_limit')?.value;
        settings.enable_rank_ranking = rankRanking || false;
        settings.enable_point_ranking = pointRanking || false;
        if (rankingLimit) {
            settings.ranking_limit = parseInt(rankingLimit);
        }
    } else if (widgetType === 'custom_html') {
        const customHtml = document.getElementById('edit_main_widget_custom_html')?.value;
        if (customHtml) {
            settings.html = customHtml;
            settings.custom_html = customHtml;
        }
    } else if (widgetType === 'block') {
        const blockTitle = document.getElementById('edit_main_widget_block_title')?.value;
        const blockContent = document.getElementById('edit_main_widget_block_content')?.value;
        const textAlignRadio = document.querySelector('input[name="edit_main_block_text_align"]:checked');
        const textAlign = textAlignRadio ? textAlignRadio.value : 'left';
        const backgroundType = document.getElementById('edit_main_widget_block_background_type')?.value || 'color';
        const paddingTop = document.getElementById('edit_main_widget_block_padding_top')?.value || '20';
        const paddingBottom = document.getElementById('edit_main_widget_block_padding_bottom')?.value || '20';
        const paddingLeft = document.getElementById('edit_main_widget_block_padding_left')?.value || '20';
        const paddingRight = document.getElementById('edit_main_widget_block_padding_right')?.value || '20';
        const titleContentGap = document.getElementById('edit_main_widget_block_title_content_gap')?.value || '8';
        const blockLink = document.getElementById('edit_main_widget_block_link')?.value;
        const openNewTab = document.getElementById('edit_main_widget_block_open_new_tab')?.checked;
        const fontColor = document.getElementById('edit_main_widget_block_font_color')?.value || '#ffffff';
        const titleFontSize = document.getElementById('edit_main_widget_block_title_font_size')?.value || '16';
        const contentFontSize = document.getElementById('edit_main_widget_block_content_font_size')?.value || '14';
        // 버튼 데이터 수집
        const buttons = [];
        const buttonInputs = document.querySelectorAll('.edit-main-block-button-text');
        buttonInputs.forEach((input) => {
            const buttonText = input.value || '';
            if (buttonText) {
                const buttonCard = input.closest('.card');
                const buttonLink = buttonCard.querySelector('.edit-main-block-button-link')?.value || '';
                const buttonOpenNewTab = buttonCard.querySelector('.edit-main-block-button-open-new-tab')?.checked || false;
                const buttonBackgroundColor = buttonCard.querySelector('.edit-main-block-button-background-color')?.value || '#007bff';
                const buttonTextColor = buttonCard.querySelector('.edit-main-block-button-text-color')?.value || '#ffffff';
                
                buttons.push({
                    text: buttonText,
                    link: buttonLink,
                    open_new_tab: buttonOpenNewTab,
                    background_color: buttonBackgroundColor,
                    text_color: buttonTextColor
                });
            }
        });
        
        if (blockTitle) {
            settings.block_title = blockTitle;
        }
        if (blockContent) {
            settings.block_content = blockContent;
        }
        settings.text_align = textAlign;
        settings.background_type = backgroundType;
        settings.font_color = fontColor;
        settings.title_font_size = titleFontSize;
        settings.content_font_size = contentFontSize;
        settings.buttons = buttons;
        
        const buttonTopMargin = document.getElementById('edit_main_widget_block_button_top_margin')?.value || '12';
        if (buttonTopMargin) {
            settings.button_top_margin = parseInt(buttonTopMargin);
            settings.button_top_margin = parseInt(buttonTopMargin);
        }
        
        if (backgroundType === 'color') {
            const backgroundColor = document.getElementById('edit_main_widget_block_background_color')?.value || '#007bff';
            settings.background_color = backgroundColor;
        } else if (backgroundType === 'image') {
            const imageFile = document.getElementById('edit_main_widget_block_image_input')?.files[0];
            if (imageFile) {
                formData.append('block_background_image_file', imageFile);
            }
            const imageUrl = document.getElementById('edit_main_widget_block_background_image')?.value;
            if (imageUrl) {
                settings.background_image_url = imageUrl;
            }
        }
        
        settings.padding_top = parseInt(paddingTop);
        settings.padding_bottom = parseInt(paddingBottom);
        settings.padding_left = parseInt(paddingLeft);
        settings.padding_right = parseInt(paddingRight);
        settings.title_content_gap = parseInt(titleContentGap);
        
        if (blockLink) {
            settings.link = blockLink;
        }
        settings.open_new_tab = openNewTab || false;
    } else if (widgetType === 'block_slide') {
        const slideDirection = document.querySelector('input[name="edit_main_block_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        // 블록 아이템들 수집
        const blockItems = [];
        const blockSlideItems = document.querySelectorAll('.edit-main-block-slide-item');
        blockSlideItems.forEach((item) => {
            const itemIndex = item.dataset.itemIndex;
            const titleInput = item.querySelector('.edit-main-block-slide-title');
            const contentInput = item.querySelector('.edit-main-block-slide-content');
            const textAlignRadio = item.querySelector(`input[name="edit_main_block_slide[${itemIndex}][text_align]"]:checked`);
            const backgroundTypeSelect = item.querySelector('.edit-main-block-slide-background-type');
            const paddingTopInput = item.querySelector('.edit-main-block-slide-padding-top');
            const paddingBottomInput = item.querySelector('.edit-main-block-slide-padding-bottom');
            const paddingLeftInput = item.querySelector('.edit-main-block-slide-padding-left');
            const paddingRightInput = item.querySelector('.edit-main-block-slide-padding-right');
            const titleContentGapInput = item.querySelector('.edit-main-block-slide-title-content-gap');
            const linkInput = item.querySelector('.edit-main-block-slide-link');
            const openNewTabCheckbox = item.querySelector('.edit-main-block-slide-open-new-tab');
            const fontColorInput = item.querySelector('.edit-main-block-slide-font-color');
            const titleFontSizeInput = item.querySelector('.edit-main-block-slide-title-font-size');
            const contentFontSizeInput = item.querySelector('.edit-main-block-slide-content-font-size');
            const buttonTopMarginInput = item.querySelector('.edit-main-block-slide-button-top-margin');
            
            // 버튼 데이터 수집
            const buttons = [];
            const buttonInputs = item.querySelectorAll('.edit-main-block-slide-button-text');
            buttonInputs.forEach((input) => {
                const buttonText = input.value || '';
                if (buttonText) {
                    const buttonCard = input.closest('.card');
                    const buttonLink = buttonCard.querySelector('.edit-main-block-slide-button-link')?.value || '';
                    const buttonOpenNewTab = buttonCard.querySelector('.edit-main-block-slide-button-open-new-tab')?.checked || false;
                    const buttonBackgroundColor = buttonCard.querySelector('.edit-main-block-slide-button-background-color')?.value || '#007bff';
                    const buttonTextColor = buttonCard.querySelector('.edit-main-block-slide-button-text-color')?.value || '#ffffff';
                    
                    buttons.push({
                        text: buttonText,
                        link: buttonLink,
                        open_new_tab: buttonOpenNewTab,
                        background_color: buttonBackgroundColor,
                        text_color: buttonTextColor
                    });
                }
            });
            
            const blockItem = {
                title: titleInput ? titleInput.value : '',
                content: contentInput ? contentInput.value : '',
                text_align: textAlignRadio ? textAlignRadio.value : 'left',
                background_type: backgroundTypeSelect ? backgroundTypeSelect.value : 'color',
                padding_top: paddingTopInput ? parseInt(paddingTopInput.value) : 20,
                padding_bottom: paddingBottomInput ? parseInt(paddingBottomInput.value) : 20,
                padding_left: paddingLeftInput ? parseInt(paddingLeftInput.value) : 20,
                padding_right: paddingRightInput ? parseInt(paddingRightInput.value) : 20,
                title_content_gap: titleContentGapInput ? parseInt(titleContentGapInput.value) : 8,
                title_font_size: titleFontSizeInput ? titleFontSizeInput.value : '16',
                content_font_size: contentFontSizeInput ? contentFontSizeInput.value : '14',
                link: linkInput ? linkInput.value : '',
                open_new_tab: openNewTabCheckbox ? openNewTabCheckbox.checked : false,
                font_color: fontColorInput ? fontColorInput.value : '#ffffff',
                buttons: buttons
            };
            
            if (buttonTopMarginInput) {
                blockItem.button_top_margin = parseInt(buttonTopMarginInput.value) || 12;
            }
            
            if (blockItem.background_type === 'color') {
                const backgroundColorInput = item.querySelector('.edit-main-block-slide-background-color');
                blockItem.background_color = backgroundColorInput ? backgroundColorInput.value : '#007bff';
            } else if (blockItem.background_type === 'image') {
                const imageFileInput = item.querySelector(`#edit_main_block_slide_${itemIndex}_image_input`);
                if (imageFileInput && imageFileInput.files[0]) {
                    formData.append(`edit_block_slide[${itemIndex}][background_image_file]`, imageFileInput.files[0]);
                }
                const imageUrlInput = item.querySelector(`#edit_main_block_slide_${itemIndex}_background_image_url`);
                if (imageUrlInput && imageUrlInput.value) {
                    blockItem.background_image_url = imageUrlInput.value;
                }
            }
            
            blockItems.push(blockItem);
        });
        
        settings.blocks = blockItems;
    } else if (widgetType === 'image') {
        const imageFile = document.getElementById('edit_main_widget_image_input')?.files[0];
        if (imageFile) {
            formData.append('image_file', imageFile);
        }
        const imageUrl = document.getElementById('edit_main_widget_image_url')?.value;
        if (imageUrl) {
            settings.image_url = imageUrl;
        }
        const imageLink = document.getElementById('edit_main_widget_image_link')?.value;
        if (imageLink) {
            settings.image_link = imageLink;
        }
        const imageOpenNewTab = document.getElementById('edit_main_widget_image_open_new_tab')?.checked;
        settings.image_open_new_tab = imageOpenNewTab || false;
    } else if (widgetType === 'image_slide') {
        const slideDirection = document.querySelector('input[name="edit_main_image_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        const singleSlide = document.getElementById('edit_main_widget_image_slide_single')?.checked || false;
        const infiniteSlide = document.getElementById('edit_main_widget_image_slide_infinite')?.checked || false;
        const visibleCount = document.getElementById('edit_main_widget_image_slide_visible_count')?.value || '3';
        const visibleCountMobile = document.getElementById('edit_main_widget_image_slide_visible_count_mobile')?.value || '2';
        const imageGap = document.getElementById('edit_main_widget_image_slide_gap')?.value || '0';
        const backgroundType = document.getElementById('edit_main_widget_image_slide_background_type')?.value || 'none';
        const backgroundColor = document.getElementById('edit_main_widget_image_slide_background_color')?.value || '#ffffff';
        
        settings.slide_mode = infiniteSlide ? 'infinite' : 'single';
        if (infiniteSlide) {
            settings.visible_count = parseInt(visibleCount) || 3;
            settings.visible_count_mobile = parseInt(visibleCountMobile) || 2;
            settings.image_gap = parseInt(imageGap) || 0;
            settings.background_type = backgroundType;
            if (backgroundType === 'color') {
                settings.background_color = backgroundColor;
            }
        }
        
        // 이미지 아이템들 수집
        const imageItems = [];
        const imageSlideItems = document.querySelectorAll('.edit-main-image-slide-item');
        imageSlideItems.forEach((item) => {
            const itemIndex = item.dataset.itemIndex;
            const imageFileInput = item.querySelector(`#edit_main_image_slide_${itemIndex}_image_input`);
            const imageUrlInput = item.querySelector(`#edit_main_image_slide_${itemIndex}_image_url`);
            const linkInput = item.querySelector('.edit-main-image-slide-link');
            const openNewTabCheckbox = item.querySelector('.edit-main-image-slide-open-new-tab');
            
            const imageItem = {
                image_url: imageUrlInput ? imageUrlInput.value : '',
                link: linkInput ? linkInput.value : '',
                open_new_tab: openNewTabCheckbox ? openNewTabCheckbox.checked : false
            };
            
            if (imageFileInput && imageFileInput.files[0]) {
                formData.append(`edit_image_slide[${itemIndex}][image_file]`, imageFileInput.files[0]);
            }
            
            imageItems.push(imageItem);
        });
        
        settings.images = imageItems;
    } else if (widgetType === 'contact_form') {
        const contactFormId = formData.get('contact_form_id');
        if (contactFormId) {
            settings.contact_form_id = parseInt(contactFormId);
        }
    } else if (widgetType === 'map') {
        const mapId = formData.get('map_id');
        if (mapId) {
            settings.map_id = parseInt(mapId);
        }
    } else if (widgetType === 'create_site') {
        const title = document.getElementById('edit_main_widget_create_site_title')?.value || '나만의 홈페이지를 만들어보세요!';
        const description = document.getElementById('edit_main_widget_create_site_description')?.value || '회원가입 후 간단한 정보만 입력하면 바로 홈페이지를 생성할 수 있습니다.';
        const buttonText = document.getElementById('edit_main_widget_create_site_button_text')?.value || '새 사이트 만들기';
        const buttonLink = document.getElementById('edit_main_widget_create_site_button_link')?.value || '{{ route('user-sites.select-plan', ['site' => $site->slug]) }}';
        const icon = document.getElementById('edit_main_widget_create_site_icon')?.value || 'bi-rocket-takeoff';
        const backgroundColor = document.getElementById('edit_main_widget_create_site_background_color')?.value || '#007bff';
        const textColor = document.getElementById('edit_main_widget_create_site_text_color')?.value || '#ffffff';
        const buttonBgColor = document.getElementById('edit_main_widget_create_site_button_bg_color')?.value || '#0056b3';
        const buttonColor = document.getElementById('edit_main_widget_create_site_button_color')?.value || '#ffffff';
        const showOnlyWhenLoggedIn = document.getElementById('edit_main_widget_create_site_show_only_when_logged_in')?.checked !== false;
        
        settings.title = title;
        settings.description = description;
        settings.button_text = buttonText;
        settings.button_link = buttonLink;
        settings.icon = icon;
        settings.background_color = backgroundColor;
        settings.text_color = textColor;
        settings.button_bg_color = buttonBgColor;
        settings.button_color = buttonColor;
        settings.show_only_when_logged_in = showOnlyWhenLoggedIn;
    } else if (widgetType === 'countdown') {
        const countdownTitle = document.getElementById('edit_main_widget_countdown_title')?.value || '';
        const countdownContent = document.getElementById('edit_main_widget_countdown_content')?.value || '';
        const countdownType = document.getElementById('edit_main_widget_countdown_type')?.value || 'dday';
        
        settings.countdown_title = countdownTitle;
        settings.countdown_content = countdownContent;
        settings.countdown_type = countdownType;
        
        if (countdownType === 'dday') {
            const targetDate = document.getElementById('edit_main_widget_countdown_target_date')?.value;
            if (targetDate) {
                // datetime-local 형식을 ISO 형식으로 변환
                const date = new Date(targetDate);
                settings.countdown_target_date = date.toISOString();
            } else if (existingSettings.countdown_target_date) {
                // 기존 값 유지
                settings.countdown_target_date = existingSettings.countdown_target_date;
            }
            const ddayAnimationCheckbox = document.getElementById('edit_main_widget_countdown_dday_animation');
            settings.countdown_dday_animation_enabled = ddayAnimationCheckbox ? ddayAnimationCheckbox.checked : false;
        } else if (countdownType === 'number') {
            const animationCheckbox = document.getElementById('edit_main_widget_countdown_animation');
            settings.countdown_animation_enabled = animationCheckbox ? animationCheckbox.checked : false;
            
            // 숫자 카운트 항목 수집
            const numberItems = [];
            const numberItemElements = document.querySelectorAll('#edit_main_widget_countdown_number_items .countdown-number-item');
            numberItemElements.forEach((item) => {
                const nameInput = item.querySelector('.edit-countdown-number-name');
                const numberInput = item.querySelector('.edit-countdown-number-number');
                const unitInput = item.querySelector('.edit-countdown-number-unit');
                
                if (nameInput && numberInput && unitInput) {
                    numberItems.push({
                        name: nameInput.value || '',
                        number: parseInt(numberInput.value) || 0,
                        unit: unitInput.value || ''
                    });
                }
            });
            settings.countdown_number_items = numberItems;
        }
    }
    
    // settings를 JSON으로 추가 (빈 객체가 아닌 경우에만)
    if (Object.keys(settings).length > 0) {
        formData.append('settings', JSON.stringify(settings));
    }
    
    // Laravel의 PUT 메서드를 시뮬레이션하기 위해 _method 필드 추가
    formData.append('_method', 'PUT');
    
    // route를 모달의 data 속성에서 가져오기
    const modal = document.getElementById('mainWidgetSettingsModal');
    const updateRouteTemplate = modal ? modal.getAttribute('data-update-route') : '';
    const updateUrl = updateRouteTemplate ? updateRouteTemplate.replace(':id', widgetId) : '';
    
    if (!updateUrl) {
        restoreButton();
        alert('업데이트 URL을 찾을 수 없습니다. Route: ' + updateRouteTemplate + ', WidgetId: ' + widgetId);
        return;
    }
    
    console.log('Sending request to:', updateUrl);
    console.log('FormData entries:', Array.from(formData.entries()));
    
    fetch(updateUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));
        
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text.substring(0, 500));
                throw new Error('Network response was not ok: ' + response.status);
            });
        }
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
                console.log('Response data:', data);
                return data;
            });
        } else {
            // HTML 응답인 경우 (리다이렉트 등)
            return response.text().then(text => {
                console.error('Expected JSON but got HTML:', text.substring(0, 500));
                throw new Error('서버에서 JSON 응답을 받지 못했습니다.');
            });
        }
    })
    .then(data => {
        console.log('Processing response data:', data);
        if (data && data.success) {
            // 위젯 설정 모달 닫기
            const settingsModal = bootstrap.Modal.getInstance(document.getElementById('mainWidgetSettingsModal'));
            if (settingsModal) {
                settingsModal.hide();
            }
            
            // 저장 완료 알림 모달 표시
            const successModalElement = document.getElementById('saveSuccessModal');
            
            // 기존 모달 인스턴스가 있으면 제거
            const existingModal = bootstrap.Modal.getInstance(successModalElement);
            if (existingModal) {
                existingModal.dispose();
            }
            
            // 기존 이벤트 리스너 제거 (중복 방지)
            if (successModalReloadHandler) {
                successModalElement.removeEventListener('hidden.bs.modal', successModalReloadHandler);
            }
            
            // 새로운 이벤트 리스너 생성 및 등록
            successModalReloadHandler = function() {
                location.reload();
            };
            successModalElement.addEventListener('hidden.bs.modal', successModalReloadHandler, { once: true });
            
            // 확인 버튼에 직접 이벤트 추가
            const confirmButton = successModalElement.querySelector('button.btn-primary');
            if (confirmButton) {
                // 기존 이벤트 리스너 제거
                const newConfirmButton = confirmButton.cloneNode(true);
                confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
                
                // 새 이벤트 리스너 추가
                newConfirmButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const modal = bootstrap.Modal.getInstance(successModalElement);
                    if (modal) {
                        modal.hide();
                    }
                    setTimeout(() => {
                        location.reload();
                    }, 100);
                });
            }
            
            const successModal = new bootstrap.Modal(successModalElement);
            successModal.show();
        } else {
            restoreButton();
            alert('위젯 수정에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(async error => {
        console.error('Error:', error);
        console.error('Error stack:', error.stack);
        
        // 413 오류인 경우 응답 본문을 확인하여 실제로 저장되었는지 확인
        if (error.message && error.message.includes('413')) {
            try {
                // 응답 본문을 읽어서 success가 true인지 확인
                const response = await fetch(form.action, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                // 저장이 성공했을 수 있으므로 페이지 새로고침
                if (response.ok) {
                    location.reload();
                    return;
                }
            } catch (e) {
                console.error('Failed to check response:', e);
            }
            restoreButton();
            alert('요청 데이터가 너무 큽니다. 이미지 파일 크기를 줄이거나 설정 데이터를 간소화해주세요. 저장은 완료되었을 수 있습니다.');
        } else {
            restoreButton();
            let errorMessage = '위젯 수정 중 오류가 발생했습니다.';
            if (error.message) {
                errorMessage = '위젯 수정 중 오류가 발생했습니다: ' + error.message;
            }
            alert(errorMessage);
        }
    });
}

// 탭메뉴 항목 추가 (위젯 수정 폼)
function addEditMainTabMenuItem() {
    const container = document.getElementById('edit_main_tab_menu_list');
    if (!container) return;
    
    // 기존 탭메뉴 항목들 접기
    const existingItems = container.querySelectorAll('.edit-main-tab-menu-item');
    existingItems.forEach((existingItem) => {
        const existingItemIndex = existingItem.dataset.itemIndex;
        const existingBody = document.getElementById(`edit_main_tab_menu_item_${existingItemIndex}_body`);
        const existingIcon = document.getElementById(`edit_main_tab_menu_item_${existingItemIndex}_icon`);
        if (existingBody && existingIcon) {
            existingBody.style.display = 'none';
            existingIcon.className = 'bi bi-chevron-right';
        }
    });
    
    const index = editMainTabMenuIndex++;
    const tabItem = document.createElement('div');
    tabItem.className = 'card mb-2 edit-main-tab-menu-item';
    tabItem.id = `edit_main_tab_menu_item_${index}`;
    tabItem.dataset.itemIndex = index;
    
    // 접기/펼치기 헤더
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleEditMainTabMenuItem(index);
    };
    header.innerHTML = `
        <span>탭메뉴 ${index + 1}</span>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-chevron-down" id="edit_main_tab_menu_item_${index}_icon"></i>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); removeEditMainTabMenuItem(${index})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    // 내용 영역
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `edit_main_tab_menu_item_${index}_body`;
    body.innerHTML = `
        <div class="mb-2">
            <label class="form-label">탭메뉴 이름</label>
            <input type="text" class="form-control edit-main-tab-menu-name" name="tab_menu[${index}][name]" placeholder="탭메뉴 이름을 입력하세요" required>
        </div>
        <div class="mb-2">
            <label class="form-label">위젯 내용</label>
            <select class="form-select edit-main-tab-menu-widget-type" name="tab_menu[${index}][widget_type]" required onchange="handleMainTabMenuWidgetTypeChange(this, ${index})">
                <option value="">선택하세요</option>
                <option value="popular_posts">인기 게시글</option>
                <option value="recent_posts">최근 게시글</option>
                <option value="weekly_popular_posts">주간 인기글</option>
                <option value="monthly_popular_posts">월간 인기글</option>
                <option value="board">게시판</option>
                <option value="notice">공지사항</option>
            </select>
        </div>
        <div class="mb-2 edit-main-tab-menu-board-container" style="display: none;">
            <label class="form-label">게시판 선택</label>
            <select class="form-select edit-main-tab-menu-board-id" name="tab_menu[${index}][board_id]">
                <option value="">선택하세요</option>
                @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                    <option value="{{ $board->id }}">{{ $board->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">표시할 게시글 수</label>
            <input type="number" class="form-control edit-main-tab-menu-limit" name="tab_menu[${index}][limit]" min="1" max="50" value="10" required>
        </div>
    `;
    
    tabItem.appendChild(header);
    tabItem.appendChild(body);
    container.appendChild(tabItem);
    
    // 새로 추가된 아이템은 펼쳐진 상태로 시작
    body.style.display = 'block';
}

function toggleEditMainTabMenuItem(itemIndex) {
    const body = document.getElementById(`edit_main_tab_menu_item_${itemIndex}_body`);
    const icon = document.getElementById(`edit_main_tab_menu_item_${itemIndex}_icon`);
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

function removeEditMainTabMenuItem(index) {
    const item = document.getElementById(`edit_main_tab_menu_item_${index}`);
    if (item) {
        item.remove();
    }
}

function handleMainTabMenuWidgetTypeChange(select, index) {
    const boardContainer = document.querySelector(`#edit_main_tab_menu_item_${index} .edit-main-tab-menu-board-container`);
    if (boardContainer) {
        boardContainer.style.display = select.value === 'board' ? 'block' : 'none';
    }
}

// 블록 슬라이드 항목 추가 (위젯 수정 폼)
function addEditMainBlockSlideItem(blockData = null) {
    const container = document.getElementById('edit_main_widget_block_slide_items');
    if (!container) return;
    
    if (!blockData) {
        const existingItems = container.querySelectorAll('.edit-main-block-slide-item');
        existingItems.forEach((existingItem) => {
            const existingItemIndex = existingItem.dataset.itemIndex;
            const existingBody = document.getElementById(`edit_main_block_slide_item_${existingItemIndex}_body`);
            const existingIcon = document.getElementById(`edit_main_block_slide_item_${existingItemIndex}_icon`);
            if (existingBody && existingIcon) {
                existingBody.style.display = 'none';
                existingIcon.className = 'bi bi-chevron-right';
            }
        });
    }
    
    const itemIndex = editMainBlockSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 edit-main-block-slide-item';
    item.id = `edit_main_block_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleEditMainBlockSlideItem(itemIndex);
    };
    header.innerHTML = `
        <span>블록 ${itemIndex + 1}</span>
        <i class="bi bi-chevron-down" id="edit_main_block_slide_item_${itemIndex}_icon"></i>
    `;
    
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `edit_main_block_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3">
            <label class="form-label">제목</label>
            <input type="text" 
                   class="form-control edit-main-block-slide-title" 
                   name="edit_main_block_slide[${itemIndex}][title]" 
                   placeholder="제목을 입력하세요"
                   value="${blockData ? (blockData.title || '') : ''}">
        </div>
        <div class="mb-3">
            <label class="form-label">내용</label>
            <textarea class="form-control edit-main-block-slide-content" 
                      name="edit_main_block_slide[${itemIndex}][content]" 
                      rows="3"
                      placeholder="내용을 입력하세요">${blockData ? (blockData.content || '') : ''}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">텍스트 정렬</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="edit_main_block_slide[${itemIndex}][text_align]" id="edit_main_block_slide_${itemIndex}_align_left" value="left" ${!blockData || blockData.text_align === 'left' ? 'checked' : ''}>
                <label class="btn btn-outline-primary" for="edit_main_block_slide_${itemIndex}_align_left">
                    <i class="bi bi-text-left"></i> 좌
                </label>
                <input type="radio" class="btn-check" name="edit_main_block_slide[${itemIndex}][text_align]" id="edit_main_block_slide_${itemIndex}_align_center" value="center" ${blockData && blockData.text_align === 'center' ? 'checked' : ''}>
                <label class="btn btn-outline-primary" for="edit_main_block_slide_${itemIndex}_align_center">
                    <i class="bi bi-text-center"></i> 중앙
                </label>
                <input type="radio" class="btn-check" name="edit_main_block_slide[${itemIndex}][text_align]" id="edit_main_block_slide_${itemIndex}_align_right" value="right" ${blockData && blockData.text_align === 'right' ? 'checked' : ''}>
                <label class="btn btn-outline-primary" for="edit_main_block_slide_${itemIndex}_align_right">
                    <i class="bi bi-text-right"></i> 우
                </label>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">제목 폰트 사이즈 (px)</label>
            <input type="number" 
                   class="form-control edit-main-block-slide-title-font-size" 
                   name="edit_main_block_slide[${itemIndex}][title_font_size]" 
                   value="${blockData ? (blockData.title_font_size || '16') : '16'}"
                   min="8"
                   max="72"
                   step="1"
                   placeholder="16">
            <small class="text-muted">기본값: 16px</small>
        </div>
        <div class="mb-3">
            <label class="form-label">내용 폰트 사이즈 (px)</label>
            <input type="number" 
                   class="form-control edit-main-block-slide-content-font-size" 
                   name="edit_main_block_slide[${itemIndex}][content_font_size]" 
                   value="${blockData ? (blockData.content_font_size || '14') : '14'}"
                   min="8"
                   max="48"
                   step="1"
                   placeholder="14">
            <small class="text-muted">기본값: 14px</small>
        </div>
        <div class="mb-3">
            <label class="form-label">배경</label>
            <select class="form-select edit-main-block-slide-background-type" name="edit_main_block_slide[${itemIndex}][background_type]" onchange="handleEditMainBlockSlideBackgroundTypeChange(${itemIndex})">
                <option value="color" ${!blockData || blockData.background_type === 'color' ? 'selected' : ''}>컬러</option>
                <option value="image" ${blockData && blockData.background_type === 'image' ? 'selected' : ''}>이미지</option>
            </select>
        </div>
        <div class="mb-3 edit-main-block-slide-color-container" id="edit_main_block_slide_${itemIndex}_color_container" style="${blockData && blockData.background_type === 'image' ? 'display: none;' : ''}">
            <label class="form-label">배경 컬러</label>
            <input type="color" 
                   class="form-control form-control-color edit-main-block-slide-background-color" 
                   name="edit_main_block_slide[${itemIndex}][background_color]" 
                   value="${blockData ? (blockData.background_color || '#007bff') : '#007bff'}">
        </div>
        <div class="mb-3">
            <label class="form-label">폰트 컬러</label>
            <input type="color" 
                   class="form-control form-control-color edit-main-block-slide-font-color" 
                   name="edit_main_block_slide[${itemIndex}][font_color]" 
                   value="${blockData ? (blockData.font_color || '#ffffff') : '#ffffff'}">
        </div>
        <div class="mb-3 edit-main-block-slide-image-container" id="edit_main_block_slide_${itemIndex}_image_container" style="${!blockData || blockData.background_type !== 'image' ? 'display: none;' : ''}">
            <label class="form-label">배경 이미지</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" 
                        class="btn btn-outline-secondary" 
                        onclick="document.getElementById('edit_main_block_slide_${itemIndex}_image_input').click()">
                    <i class="bi bi-image"></i> 이미지 선택
                </button>
                <input type="file" 
                       id="edit_main_block_slide_${itemIndex}_image_input" 
                       name="edit_main_block_slide[${itemIndex}][background_image]" 
                       accept="image/*" 
                       style="display: none;"
                       onchange="handleEditMainBlockSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="edit-main-block-slide-background-image-url" name="edit_main_block_slide[${itemIndex}][background_image_url]" id="edit_main_block_slide_${itemIndex}_background_image_url" value="${blockData && blockData.background_image_url ? blockData.background_image_url : ''}">
                <div class="edit-main-block-slide-image-preview" id="edit_main_block_slide_${itemIndex}_image_preview" style="${blockData && blockData.background_image_url ? '' : 'display: none;'}">
                    <img id="edit_main_block_slide_${itemIndex}_image_preview_img" src="${blockData && blockData.background_image_url ? blockData.background_image_url : ''}" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditMainBlockSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">상단 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-main-block-slide-padding-top" 
                   name="edit_main_block_slide[${itemIndex}][padding_top]" 
                   value="${blockData ? (blockData.padding_top || '20') : '20'}"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 상단 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">하단 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-main-block-slide-padding-bottom" 
                   name="edit_main_block_slide[${itemIndex}][padding_bottom]" 
                   value="${blockData ? (blockData.padding_bottom || blockData.padding_top || '20') : '20'}"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 하단 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">좌측 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-main-block-slide-padding-left" 
                   name="edit_main_block_slide[${itemIndex}][padding_left]" 
                   value="${blockData ? (blockData.padding_left || '20') : '20'}"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 좌측 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">우측 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-main-block-slide-padding-right" 
                   name="edit_main_block_slide[${itemIndex}][padding_right]" 
                   value="${blockData ? (blockData.padding_right || blockData.padding_left || '20') : '20'}"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 우측 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">제목-내용 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-main-block-slide-title-content-gap" 
                   name="edit_main_block_slide[${itemIndex}][title_content_gap]" 
                   value="${blockData ? (blockData.title_content_gap || '8') : '8'}"
                   min="0"
                   max="100"
                   step="1"
                   placeholder="8">
            <small class="text-muted">제목과 내용 사이의 여백을 입력하세요 (0~100).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">버튼 관리</label>
            <div class="edit-main-block-slide-buttons-list" id="edit_main_block_slide_${itemIndex}_buttons_list">
                <!-- 버튼들이 여기에 동적으로 추가됨 -->
            </div>
            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addEditMainBlockSlideButton(${itemIndex})">
                <i class="bi bi-plus-circle me-1"></i>버튼 추가
            </button>
        </div>
        <div class="mb-3">
            <label class="form-label">버튼 상단 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-main-block-slide-button-top-margin" 
                   name="edit_main_block_slide[${itemIndex}][button_top_margin]" 
                   value="${blockData ? (blockData.button_top_margin || '12') : '12'}"
                   min="0"
                   max="100"
                   step="1"
                   placeholder="12">
            <small class="text-muted">버튼과 위 요소 사이의 여백을 입력하세요 (0~100).</small>
        </div>
        <div class="mb-3" id="edit_main_block_slide_${itemIndex}_link_container">
            <label class="form-label">
                연결 링크 <small class="text-muted">(선택사항)</small>
                <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="버튼이 있는 경우 버튼에 링크가 연결되고, 버튼이 없는 경우 블록 전체에 링크가 연결됩니다."></i>
            </label>
            <input type="url" 
                   class="form-control edit-main-block-slide-link" 
                   name="edit_main_block_slide[${itemIndex}][link]" 
                   placeholder="https://example.com"
                   value="${blockData ? (blockData.link || '') : ''}">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input edit-main-block-slide-open-new-tab" 
                       type="checkbox" 
                       name="edit_main_block_slide[${itemIndex}][open_new_tab]"
                       id="edit_main_block_slide_${itemIndex}_open_new_tab"
                       ${blockData && blockData.open_new_tab ? 'checked' : ''}>
                <label class="form-check-label" for="edit_main_block_slide_${itemIndex}_open_new_tab">
                    새창에서 열기
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeEditMainBlockSlideItem(${itemIndex})">
            <i class="bi bi-trash me-1"></i>삭제
        </button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
    
    // 버튼 데이터 로드
    if (blockData) {
        const buttonsContainer = document.getElementById(`edit_main_block_slide_${itemIndex}_buttons_list`);
        if (buttonsContainer) {
            editMainBlockSlideButtonIndices[itemIndex] = 0;
            
            // 기존 버튼 데이터 로드 (하위 호환성: 기존 단일 버튼 데이터도 지원)
            let buttons = blockData.buttons || [];
            if (!Array.isArray(buttons) && blockData.show_button && blockData.button_text) {
                // 기존 단일 버튼 데이터를 배열로 변환
                buttons = [{
                    text: blockData.button_text || '',
                    link: blockData.link || '',
                    open_new_tab: blockData.open_new_tab || false,
                    background_color: blockData.button_background_color || '#007bff',
                    text_color: blockData.button_text_color || '#ffffff'
                }];
            }
            
            buttons.forEach((button) => {
                if (button.text) {
                    addEditMainBlockSlideButton(itemIndex);
                    const buttonIndex = editMainBlockSlideButtonIndices[itemIndex] - 1;
                    const buttonId = `edit_main_block_slide_${itemIndex}_button_${buttonIndex}`;
                    const buttonCard = document.getElementById(buttonId);
                    if (buttonCard) {
                        buttonCard.querySelector('.edit-main-block-slide-button-text').value = button.text || '';
                        buttonCard.querySelector('.edit-main-block-slide-button-link').value = button.link || '';
                        buttonCard.querySelector('.edit-main-block-slide-button-open-new-tab').checked = button.open_new_tab || false;
                        buttonCard.querySelector('.edit-main-block-slide-button-background-color').value = button.background_color || '#007bff';
                        buttonCard.querySelector('.edit-main-block-slide-button-text-color').value = button.text_color || '#ffffff';
                    }
                }
            });
            
            // 버튼이 있으면 연결 링크 필드 숨기기
            const linkContainer = document.getElementById(`edit_main_block_slide_${itemIndex}_link_container`);
            if (linkContainer && buttons.length > 0) {
                linkContainer.style.display = 'none';
            }
        }
    }
}

function toggleEditMainBlockSlideItem(itemIndex) {
    const body = document.getElementById(`edit_main_block_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`edit_main_block_slide_item_${itemIndex}_icon`);
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

function handleEditMainBlockSlideBackgroundTypeChange(itemIndex) {
    const backgroundType = document.querySelector(`#edit_main_block_slide_item_${itemIndex} .edit-main-block-slide-background-type`)?.value;
    const colorContainer = document.getElementById(`edit_main_block_slide_${itemIndex}_color_container`);
    const imageContainer = document.getElementById(`edit_main_block_slide_${itemIndex}_image_container`);
    
    if (backgroundType === 'image') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'block';
    } else {
        if (colorContainer) colorContainer.style.display = 'block';
        if (imageContainer) imageContainer.style.display = 'none';
    }
}

// 블록슬라이드 수정 모달 버튼 관리
let editMainBlockSlideButtonIndices = {};

function addEditMainBlockSlideButton(itemIndex) {
    if (!editMainBlockSlideButtonIndices[itemIndex]) {
        editMainBlockSlideButtonIndices[itemIndex] = 0;
    }
    
    const container = document.getElementById(`edit_main_block_slide_${itemIndex}_buttons_list`);
    if (!container) return;
    
    const buttonIndex = editMainBlockSlideButtonIndices[itemIndex];
    const buttonId = `edit_main_block_slide_${itemIndex}_button_${buttonIndex}`;
    const buttonHtml = `
        <div class="card mb-3" id="${buttonId}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">버튼 ${buttonIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeEditMainBlockSlideButton('${buttonId}', ${itemIndex})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 텍스트</label>
                    <input type="text" 
                           class="form-control edit-main-block-slide-button-text" 
                           name="edit_main_block_slide[${itemIndex}][buttons][${buttonIndex}][text]" 
                           placeholder="버튼 텍스트를 입력하세요">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 링크</label>
                    <input type="url" 
                           class="form-control edit-main-block-slide-button-link" 
                           name="edit_main_block_slide[${itemIndex}][buttons][${buttonIndex}][link]" 
                           placeholder="https://example.com">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input edit-main-block-slide-button-open-new-tab" 
                               type="checkbox" 
                               name="edit_main_block_slide[${itemIndex}][buttons][${buttonIndex}][open_new_tab]" 
                               id="edit_main_block_slide_${itemIndex}_button_${buttonIndex}_open_new_tab">
                        <label class="form-check-label" for="edit_main_block_slide_${itemIndex}_button_${buttonIndex}_open_new_tab">
                            새창에서 열기
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-main-block-slide-button-background-color" 
                               name="edit_main_block_slide[${itemIndex}][buttons][${buttonIndex}][background_color]" 
                               value="#007bff">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-main-block-slide-button-text-color" 
                               name="edit_main_block_slide[${itemIndex}][buttons][${buttonIndex}][text_color]" 
                               value="#ffffff">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', buttonHtml);
    editMainBlockSlideButtonIndices[itemIndex]++;
    
    // 버튼이 추가되면 연결 링크 필드 숨기기
    const linkContainer = document.getElementById(`edit_main_block_slide_${itemIndex}_link_container`);
    if (linkContainer) {
        linkContainer.style.display = 'none';
    }
}

function removeEditMainBlockSlideButton(buttonId, itemIndex) {
    const button = document.getElementById(buttonId);
    if (button) button.remove();
    
    // 버튼이 없으면 연결 링크 필드 보이기
    const container = document.getElementById(`edit_main_block_slide_${itemIndex}_buttons_list`);
    const linkContainer = document.getElementById(`edit_main_block_slide_${itemIndex}_link_container`);
    if (linkContainer && container) {
        const buttons = container.querySelectorAll('.card');
        if (buttons.length === 0) {
            linkContainer.style.display = 'block';
        }
    }
}

function handleEditMainBlockSlideImageChange(itemIndex, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`edit_main_block_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`edit_main_block_slide_${itemIndex}_image_preview_img`);
            const imageUrl = document.getElementById(`edit_main_block_slide_${itemIndex}_background_image_url`);
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeEditMainBlockSlideImage(itemIndex) {
    const input = document.getElementById(`edit_main_block_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`edit_main_block_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`edit_main_block_slide_${itemIndex}_background_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function removeEditMainBlockSlideItem(itemIndex) {
    const item = document.getElementById(`edit_main_block_slide_item_${itemIndex}`);
    if (item) {
        item.remove();
    }
}

// 이미지 슬라이드 항목 추가 (위젯 수정 폼)
function addEditMainImageSlideItem(imageData = null) {
    const container = document.getElementById('edit_main_widget_image_slide_items');
    if (!container) return;
    
    if (!imageData) {
        const existingItems = container.querySelectorAll('.edit-main-image-slide-item');
        existingItems.forEach((existingItem) => {
            const existingItemIndex = existingItem.dataset.itemIndex;
            const existingBody = document.getElementById(`edit_main_image_slide_item_${existingItemIndex}_body`);
            const existingIcon = document.getElementById(`edit_main_image_slide_item_${existingItemIndex}_icon`);
            if (existingBody && existingIcon) {
                existingBody.style.display = 'none';
                existingIcon.className = 'bi bi-chevron-right';
            }
        });
    }
    
    const itemIndex = editMainImageSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 edit-main-image-slide-item';
    item.id = `edit_main_image_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleEditMainImageSlideItem(itemIndex);
    };
    header.innerHTML = `
        <span>이미지 ${itemIndex + 1}</span>
        <i class="bi bi-chevron-down" id="edit_main_image_slide_item_${itemIndex}_icon"></i>
    `;
    
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `edit_main_image_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3">
            <label class="form-label">이미지 선택</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" 
                        class="btn btn-outline-secondary" 
                        onclick="document.getElementById('edit_main_image_slide_${itemIndex}_image_input').click()">
                    <i class="bi bi-image"></i> 이미지 선택
                </button>
                <input type="file" 
                       id="edit_main_image_slide_${itemIndex}_image_input" 
                       name="edit_main_image_slide[${itemIndex}][image_file]" 
                       accept="image/*" 
                       style="display: none;"
                       onchange="handleEditMainImageSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="edit-main-image-slide-image-url" name="edit_main_image_slide[${itemIndex}][image_url]" id="edit_main_image_slide_${itemIndex}_image_url" value="${imageData && imageData.image_url ? imageData.image_url : ''}">
                <div class="edit-main-image-slide-image-preview" id="edit_main_image_slide_${itemIndex}_image_preview" style="${imageData && imageData.image_url ? '' : 'display: none;'}">
                    <img id="edit_main_image_slide_${itemIndex}_image_preview_img" src="${imageData && imageData.image_url ? imageData.image_url : ''}" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditMainImageSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
            <input type="url" 
                   class="form-control edit-main-image-slide-link" 
                   name="edit_main_image_slide[${itemIndex}][link]" 
                   placeholder="https://example.com"
                   value="${imageData ? (imageData.link || '') : ''}">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input edit-main-image-slide-open-new-tab" 
                       type="checkbox" 
                       name="edit_main_image_slide[${itemIndex}][open_new_tab]"
                       id="edit_main_image_slide_${itemIndex}_open_new_tab"
                       ${imageData && imageData.open_new_tab ? 'checked' : ''}>
                <label class="form-check-label" for="edit_main_image_slide_${itemIndex}_open_new_tab">
                    새창에서 열기
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeEditMainImageSlideItem(${itemIndex})">
            <i class="bi bi-trash me-1"></i>삭제
        </button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
}

function toggleEditMainImageSlideItem(itemIndex) {
    const body = document.getElementById(`edit_main_image_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`edit_main_image_slide_item_${itemIndex}_icon`);
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

function handleEditMainImageSlideImageChange(itemIndex, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`edit_main_image_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`edit_main_image_slide_${itemIndex}_image_preview_img`);
            const imageUrl = document.getElementById(`edit_main_image_slide_${itemIndex}_image_url`);
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeEditMainImageSlideImage(itemIndex) {
    const input = document.getElementById(`edit_main_image_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`edit_main_image_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`edit_main_image_slide_${itemIndex}_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function handleEditMainImageSlideModeChange() {
    const singleCheckbox = document.getElementById('edit_main_widget_image_slide_single');
    const infiniteCheckbox = document.getElementById('edit_main_widget_image_slide_infinite');
    const visibleCountContainer = document.getElementById('edit_main_widget_image_slide_visible_count_container');
    const visibleCountMobileContainer = document.getElementById('edit_main_widget_image_slide_visible_count_mobile_container');
    const gapContainer = document.getElementById('edit_main_widget_image_slide_gap_container');
    const backgroundContainer = document.getElementById('edit_main_widget_image_slide_background_container');
    const directionGroup = document.getElementById('edit_main_image_slide_direction_group');
    const upRadio = document.getElementById('edit_main_image_slide_direction_up');
    const downRadio = document.getElementById('edit_main_image_slide_direction_down');
    const upLabel = upRadio ? upRadio.nextElementSibling : null;
    const downLabel = downRadio ? downRadio.nextElementSibling : null;
    
    // 체크박스 상호 배타적 처리 - 무한루프 체크를 먼저 확인
    if (infiniteCheckbox && infiniteCheckbox.checked) {
        if (singleCheckbox) singleCheckbox.checked = false;
        if (visibleCountContainer) visibleCountContainer.style.display = 'block';
        if (visibleCountMobileContainer) visibleCountMobileContainer.style.display = 'block';
        if (gapContainer) gapContainer.style.display = 'block';
        if (backgroundContainer) backgroundContainer.style.display = 'block';
        
        // 무한루프 슬라이드일 때 상하 방향 비활성화
        if (upRadio) {
            upRadio.disabled = true;
            if (upLabel) upLabel.classList.add('disabled');
        }
        if (downRadio) {
            downRadio.disabled = true;
            if (downLabel) downLabel.classList.add('disabled');
        }
        
        // 상하 방향이 선택되어 있으면 좌로 변경
        if (upRadio && upRadio.checked) {
            const leftRadio = document.getElementById('edit_main_image_slide_direction_left');
            if (leftRadio) leftRadio.checked = true;
        }
        if (downRadio && downRadio.checked) {
            const leftRadio = document.getElementById('edit_main_image_slide_direction_left');
            if (leftRadio) leftRadio.checked = true;
        }
    } else {
        if (visibleCountContainer) visibleCountContainer.style.display = 'none';
        
        // 1단 슬라이드일 때 상하 방향 활성화
        if (upRadio) {
            upRadio.disabled = false;
            if (upLabel) upLabel.classList.remove('disabled');
        }
        if (downRadio) {
            downRadio.disabled = false;
            if (downLabel) downLabel.classList.remove('disabled');
        }
    }
    
    // 체크박스 상호 배타적 처리 - 1단 슬라이드가 체크되면 무한루프 해제
    if (singleCheckbox && singleCheckbox.checked) {
        if (infiniteCheckbox) infiniteCheckbox.checked = false;
    }
    
    // 툴팁 초기화
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function removeEditMainImageSlideItem(itemIndex) {
    const item = document.getElementById(`edit_main_image_slide_item_${itemIndex}`);
    if (item) {
        item.remove();
    }
}

// 블록 배경 타입 변경 핸들러
function handleEditMainBlockBackgroundTypeChange() {
    const backgroundType = document.getElementById('edit_main_widget_block_background_type')?.value;
    const colorContainer = document.getElementById('edit_main_widget_block_color_container');
    const imageContainer = document.getElementById('edit_main_widget_block_image_container');
    
    if (backgroundType === 'image') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'block';
    } else {
        if (colorContainer) colorContainer.style.display = 'block';
        if (imageContainer) imageContainer.style.display = 'none';
    }
}

// 블록 위젯 버튼 관리 변수
let blockButtonIndex = 0;
let editMainBlockButtonIndex = 0;

// 새 위젯 추가용 블록 버튼 추가
function addBlockButton() {
    const container = document.getElementById('widget_block_buttons_list');
    if (!container) return;
    
    const buttonId = `block_button_${blockButtonIndex}`;
    const buttonHtml = `
        <div class="card mb-3" id="${buttonId}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">버튼 ${blockButtonIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeBlockButton('${buttonId}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 텍스트</label>
                    <input type="text" 
                           class="form-control block-button-text" 
                           name="block_buttons[${blockButtonIndex}][text]" 
                           placeholder="버튼 텍스트를 입력하세요">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 링크</label>
                    <input type="url" 
                           class="form-control block-button-link" 
                           name="block_buttons[${blockButtonIndex}][link]" 
                           placeholder="https://example.com">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input block-button-open-new-tab" 
                               type="checkbox" 
                               name="block_buttons[${blockButtonIndex}][open_new_tab]" 
                               id="block_button_${blockButtonIndex}_open_new_tab">
                        <label class="form-check-label" for="block_button_${blockButtonIndex}_open_new_tab">
                            새창에서 열기
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-button-background-color" 
                               name="block_buttons[${blockButtonIndex}][background_color]" 
                               value="#007bff">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-button-text-color" 
                               name="block_buttons[${blockButtonIndex}][text_color]" 
                               value="#ffffff">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', buttonHtml);
    blockButtonIndex++;
    
    // 버튼이 추가되면 연결 링크 필드 숨기기
    const linkContainer = document.getElementById('widget_block_link_container');
    if (linkContainer) {
        linkContainer.style.display = 'none';
    }
}

// 새 위젯 추가용 블록 버튼 삭제
function removeBlockButton(buttonId) {
    const button = document.getElementById(buttonId);
    if (button) button.remove();
    
    // 버튼이 없으면 연결 링크 필드 보이기
    const container = document.getElementById('widget_block_buttons_list');
    const linkContainer = document.getElementById('widget_block_link_container');
    if (linkContainer && container) {
        const buttons = container.querySelectorAll('.card');
        if (buttons.length === 0) {
            linkContainer.style.display = 'block';
        }
    }
}

// 수정 모달용 블록 버튼 추가
function addEditMainBlockButton() {
    const container = document.getElementById('edit_main_widget_block_buttons_list');
    if (!container) return;
    
    const buttonId = `edit_main_block_button_${editMainBlockButtonIndex}`;
    const buttonHtml = `
        <div class="card mb-3" id="${buttonId}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">버튼 ${editMainBlockButtonIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeEditMainBlockButton('${buttonId}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 텍스트</label>
                    <input type="text" 
                           class="form-control edit-main-block-button-text" 
                           name="edit_main_block_buttons[${editMainBlockButtonIndex}][text]" 
                           placeholder="버튼 텍스트를 입력하세요">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 링크</label>
                    <input type="url" 
                           class="form-control edit-main-block-button-link" 
                           name="edit_main_block_buttons[${editMainBlockButtonIndex}][link]" 
                           placeholder="https://example.com">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input edit-main-block-button-open-new-tab" 
                               type="checkbox" 
                               name="edit_main_block_buttons[${editMainBlockButtonIndex}][open_new_tab]" 
                               id="edit_main_block_button_${editMainBlockButtonIndex}_open_new_tab">
                        <label class="form-check-label" for="edit_main_block_button_${editMainBlockButtonIndex}_open_new_tab">
                            새창에서 열기
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-main-block-button-background-color" 
                               name="edit_main_block_buttons[${editMainBlockButtonIndex}][background_color]" 
                               value="#007bff">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-main-block-button-text-color" 
                               name="edit_main_block_buttons[${editMainBlockButtonIndex}][text_color]" 
                               value="#ffffff">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', buttonHtml);
    editMainBlockButtonIndex++;
    
    // 버튼이 추가되면 연결 링크 필드 숨기기
    const linkContainer = document.getElementById('edit_main_widget_block_link_container');
    if (linkContainer) {
        linkContainer.style.display = 'none';
    }
}

// 수정 모달용 블록 버튼 삭제
function removeEditMainBlockButton(buttonId) {
    const button = document.getElementById(buttonId);
    if (button) button.remove();
    
    // 버튼이 없으면 연결 링크 필드 보이기
    const container = document.getElementById('edit_main_widget_block_buttons_list');
    const linkContainer = document.getElementById('edit_main_widget_block_link_container');
    if (linkContainer && container) {
        const buttons = container.querySelectorAll('.card');
        if (buttons.length === 0) {
            linkContainer.style.display = 'block';
        }
    }
}

function handleEditMainBlockImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_main_widget_block_image_preview');
            const previewImg = document.getElementById('edit_main_widget_block_image_preview_img');
            const imageUrl = document.getElementById('edit_main_widget_block_background_image');
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeEditMainBlockImage() {
    const input = document.getElementById('edit_main_widget_block_image_input');
    const preview = document.getElementById('edit_main_widget_block_image_preview');
    const imageUrl = document.getElementById('edit_main_widget_block_background_image');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function handleEditMainImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_main_widget_image_preview');
            const previewImg = document.getElementById('edit_main_widget_image_preview_img');
            const imageUrl = document.getElementById('edit_main_widget_image_url');
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeEditMainImage() {
    const input = document.getElementById('edit_main_widget_image_input');
    const preview = document.getElementById('edit_main_widget_image_preview');
    const imageUrl = document.getElementById('edit_main_widget_image_url');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function handleEditMainGalleryDisplayTypeChange() {
    const displayType = document.getElementById('edit_main_widget_gallery_display_type')?.value;
    const gridContainer = document.getElementById('edit_main_widget_gallery_grid_container');
    const slideContainer = document.getElementById('edit_main_widget_gallery_slide_container');
    
    if (displayType === 'grid') {
        if (gridContainer) gridContainer.style.display = 'block';
        if (slideContainer) slideContainer.style.display = 'none';
    } else {
        if (gridContainer) gridContainer.style.display = 'none';
        if (slideContainer) slideContainer.style.display = 'block';
    }
}

// 카운트다운 숫자 카운트 항목 추가
let countdownNumberItemIndex = 0;
function addCountdownNumberItem() {
    const container = document.getElementById('widget_countdown_number_items');
    if (!container) return;
    
    const itemIndex = countdownNumberItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 countdown-number-item';
    item.dataset.itemIndex = itemIndex;
    item.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">항목명</label>
                    <input type="text" 
                           class="form-control" 
                           name="countdown_number[${itemIndex}][name]" 
                           placeholder="예: 프로젝트수">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">숫자</label>
                    <input type="number" 
                           class="form-control" 
                           name="countdown_number[${itemIndex}][number]" 
                           placeholder="예: 48"
                           min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">단위</label>
                    <input type="text" 
                           class="form-control" 
                           name="countdown_number[${itemIndex}][unit]" 
                           placeholder="예: 개">
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeCountdownNumberItem(${itemIndex})">
                <i class="bi bi-trash me-1"></i>삭제
            </button>
        </div>
    `;
    container.appendChild(item);
}

// 카운트다운 숫자 카운트 항목 삭제
function removeCountdownNumberItem(itemIndex) {
    const item = document.querySelector(`.countdown-number-item[data-item-index="${itemIndex}"]`);
    if (item) {
        item.remove();
    }
}

// 카운트다운 타입 변경 처리 (위젯 추가용)
function handleCountdownTypeChange() {
    const type = document.getElementById('widget_countdown_type').value;
    const ddayContainer = document.getElementById('widget_countdown_dday_container');
    const numberContainer = document.getElementById('widget_countdown_number_container');
    
    if (type === 'dday') {
        if (ddayContainer) ddayContainer.style.display = 'block';
        if (numberContainer) numberContainer.style.display = 'none';
    } else if (type === 'number') {
        if (ddayContainer) ddayContainer.style.display = 'none';
        if (numberContainer) numberContainer.style.display = 'block';
        // 숫자 항목이 없으면 기본 1개 추가
        const itemsContainer = document.getElementById('widget_countdown_number_items');
        if (itemsContainer) {
            const items = itemsContainer.querySelectorAll('.countdown-number-item');
            if (items.length === 0) {
                addCountdownNumberItem();
            }
        }
    }
}

// 카운트다운 타입 변경 처리 (위젯 수정용)
function handleEditCountdownTypeChange() {
    const type = document.getElementById('edit_main_widget_countdown_type').value;
    const ddayContainer = document.getElementById('edit_main_widget_countdown_dday_container');
    const numberContainer = document.getElementById('edit_main_widget_countdown_number_container');
    
    if (type === 'dday') {
        if (ddayContainer) ddayContainer.style.display = 'block';
        if (numberContainer) numberContainer.style.display = 'none';
    } else if (type === 'number') {
        if (ddayContainer) ddayContainer.style.display = 'none';
        if (numberContainer) numberContainer.style.display = 'block';
        // 숫자 항목이 없으면 기본 1개 추가
        const itemsContainer = document.getElementById('edit_main_widget_countdown_number_items');
        if (itemsContainer) {
            const items = itemsContainer.querySelectorAll('.countdown-number-item');
            if (items.length === 0) {
                addEditCountdownNumberItem();
            }
        }
    }
}

// 카운트다운 숫자 카운트 항목 추가 (위젯 수정용)
let editCountdownNumberItemIndex = 0;
function addEditCountdownNumberItem(itemData = null) {
    const container = document.getElementById('edit_main_widget_countdown_number_items');
    if (!container) return;
    
    const itemIndex = editCountdownNumberItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 countdown-number-item';
    item.dataset.itemIndex = itemIndex;
    item.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">항목명</label>
                    <input type="text" 
                           class="form-control edit-countdown-number-name" 
                           name="edit_countdown_number[${itemIndex}][name]" 
                           placeholder="예: 프로젝트수"
                           value="${itemData ? (itemData.name || '') : ''}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">숫자</label>
                    <input type="number" 
                           class="form-control edit-countdown-number-number" 
                           name="edit_countdown_number[${itemIndex}][number]" 
                           placeholder="예: 48"
                           min="0"
                           value="${itemData ? (itemData.number || '') : ''}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">단위</label>
                    <input type="text" 
                           class="form-control edit-countdown-number-unit" 
                           name="edit_countdown_number[${itemIndex}][unit]" 
                           placeholder="예: 개"
                           value="${itemData ? (itemData.unit || '') : ''}">
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeEditCountdownNumberItem(${itemIndex})">
                <i class="bi bi-trash me-1"></i>삭제
            </button>
        </div>
    `;
    container.appendChild(item);
}

// 카운트다운 숫자 카운트 항목 삭제 (위젯 수정용)
function removeEditCountdownNumberItem(itemIndex) {
    const item = document.querySelector(`#edit_main_widget_countdown_number_items .countdown-number-item[data-item-index="${itemIndex}"]`);
    if (item) {
        item.remove();
    }
}

// 메인 위젯 설정 저장 (하단 저장 버튼용)
function saveAllMainWidgets() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>저장 중...';
    
    // 메인 위젯은 컨테이너와 위젯이 이미 저장되어 있으므로, 단순히 성공 메시지만 표시
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        // 성공 알림 표시
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>메인 위젯 설정이 저장되었습니다.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.page-header').after(alertDiv);
        
        // 3초 후 자동으로 알림 제거
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }, 500);
}
</script>
@endpush
@endsection

