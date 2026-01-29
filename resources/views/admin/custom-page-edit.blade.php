@extends('layouts.admin')

@section('title', '커스텀 페이지 편집')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header mb-4">
                <h1 class="h3 mb-2">{{ $customPage->name }} 편집</h1>
                <p class="text-muted">컨테이너를 추가하고 각 칸에 위젯을 배치하여 페이지를 구성할 수 있습니다</p>
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
                            <form id="addContainerForm" method="POST" action="{{ route('admin.custom-pages.containers.store', ['site' => $site->slug, 'customPage' => $customPage->id]) }}">
                                @csrf
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
                                        <input class="form-check-input" type="checkbox" id="container_full_width" name="full_width" value="1" {{ !$hasSidebar ? '' : 'disabled' }} onchange="toggleFixedWidthColumnsAdd()">
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
                                <div class="mb-3" id="fixed_width_columns_option_add" style="display: none;">
                                    <div class="form-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" id="container_fixed_width_columns" name="fixed_width_columns" value="1">
                                        <label class="form-check-label" for="container_fixed_width_columns">
                                            칸 고정너비
                                        </label>
                                        <i class="bi bi-question-circle text-muted ms-2" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="가로 100% 활성화 시 컨테이너의 배경은 전체 너비를 사용하지만, 컨테이너 안의 칸들은 고정된 너비를 유지합니다." 
                                           style="cursor: help; font-size: 0.9rem;"></i>
                                    </div>
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
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-secondary w-100" onclick="openNewContainerMarginModal()">
                                            <i class="bi bi-arrows-angle-expand"></i> 마진 설정
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-secondary w-100" onclick="openNewContainerPaddingModal()">
                                            <i class="bi bi-arrows-fullscreen"></i> 패딩 설정
                                        </button>
                                    </div>
                                </div>
                                <!-- Hidden inputs for new container margin/padding -->
                                <input type="hidden" id="new_container_margin_top" name="margin_top" value="0">
                                <input type="hidden" id="new_container_margin_bottom" name="margin_bottom" value="24">
                                <input type="hidden" id="new_container_margin_left" name="margin_left" value="0">
                                <input type="hidden" id="new_container_margin_right" name="margin_right" value="0">
                                <input type="hidden" id="new_container_padding_top" name="padding_top" value="0">
                                <input type="hidden" id="new_container_padding_bottom" name="padding_bottom" value="0">
                                <input type="hidden" id="new_container_padding_left" name="padding_left" value="0">
                                <input type="hidden" id="new_container_padding_right" name="padding_right" value="0">
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
                                    <button type="button" class="btn btn-primary" onclick="addCustomPageWidget()">
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
                            <h6 class="mb-0">{{ $customPage->name }}</h6>
                        </div>
                        <div class="card-body">
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
                                            {{-- 데스크탑 버전 (2줄 배치) --}}
                                            <div class="card-header bg-light d-none d-md-block">
                                                {{-- 첫 번째 줄: 컨테이너가로, 세로정렬, 가로100%, 세로 100% --}}
                                                <div class="d-flex align-items-center gap-2 mb-2">
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
                                                    <label class="mb-0 small ms-3">세로정렬:</label>
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
                                                               @if(!$hasSidebar) data-container-id="{{ $container->id }}" onchange="toggleFixedWidthColumnsOption({{ $container->id }})" @else disabled @endif>
                                                        <label class="form-check-label small mb-0" for="container_full_width_{{ $container->id }}">
                                                            가로 100%
                                                        </label>
                                                        <i class="bi bi-question-circle text-muted ms-1" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top" 
                                                           title="활성화 시 해당 컨테이너가 브라우저 전체 너비를 사용합니다. 사이드바가 없음으로 설정된 경우에만 사용할 수 있습니다." 
                                                           style="cursor: help; font-size: 0.85rem;"></i>
                                                    </div>
                                                    <div class="form-check ms-3 d-flex align-items-center" id="fixed_width_columns_container_{{ $container->id }}" style="display: {{ $container->full_width ? 'flex' : 'none' }} !important;">
                                                        <input class="form-check-input container-fixed-width-columns-checkbox" type="checkbox" 
                                                               id="container_fixed_width_columns_{{ $container->id }}" 
                                                               @if($container->fixed_width_columns) checked @endif
                                                               data-container-id="{{ $container->id }}"
                                                               onchange="updateContainerFixedWidthColumns({{ $container->id }}, this.checked)">
                                                        <label class="form-check-label small mb-0" for="container_fixed_width_columns_{{ $container->id }}">
                                                            칸 고정너비
                                                        </label>
                                                        <i class="bi bi-question-circle text-muted ms-1" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top" 
                                                           title="활성화 시 컨테이너 배경은 100%이지만 칸들은 기존 고정 너비를 유지합니다." 
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
                                                </div>
                                                {{-- 두 번째 줄: 위젯간격, 배경, 위로이동, 아래로이동, 삭제 아이콘 --}}
                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <label class="mb-0 small">위젯 간격:</label>
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
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-secondary ms-2"
                                                                onclick="openContainerMarginModal({{ $container->id }})"
                                                                title="마진 설정">
                                                            <i class="bi bi-arrows-angle-expand"></i> 마진
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-secondary"
                                                                onclick="openContainerPaddingModal({{ $container->id }})"
                                                                title="패딩 설정">
                                                            <i class="bi bi-arrows-fullscreen"></i> 패딩
                                                        </button>
                                                        <!-- Hidden inputs for margin/padding values -->
                                                        <input type="hidden" id="container_margin_top_{{ $container->id }}" value="{{ $container->margin_top ?? 0 }}">
                                                        <input type="hidden" id="container_margin_bottom_{{ $container->id }}" value="{{ $container->margin_bottom ?? 24 }}">
                                                        <input type="hidden" id="container_margin_left_{{ $container->id }}" value="{{ $container->margin_left ?? 0 }}">
                                                        <input type="hidden" id="container_margin_right_{{ $container->id }}" value="{{ $container->margin_right ?? 0 }}">
                                                        <input type="hidden" id="container_padding_top_{{ $container->id }}" value="{{ $container->padding_top ?? 0 }}">
                                                        <input type="hidden" id="container_padding_bottom_{{ $container->id }}" value="{{ $container->padding_bottom ?? 0 }}">
                                                        <input type="hidden" id="container_padding_left_{{ $container->id }}" value="{{ $container->padding_left ?? 0 }}">
                                                        <input type="hidden" id="container_padding_right_{{ $container->id }}" value="{{ $container->padding_right ?? 0 }}">
                                                        <label class="mb-0 small ms-2">앵커ID:</label>
                                                        <input type="text" 
                                                               class="form-control form-control-sm" 
                                                               style="width: 120px;"
                                                               id="container_anchor_id_{{ $container->id }}"
                                                               value="{{ $container->anchor_id ?? '' }}"
                                                               placeholder="section-name"
                                                               onchange="updateContainerAnchorId({{ $container->id }})"
                                                               title="메뉴에서 이 컨테이너로 스크롤 이동 시 사용되는 ID">
                                                        <label class="mb-0 small ms-2">배경:</label>
                                                        <select class="form-select form-select-sm" 
                                                                style="width: auto; min-width: 100px;" 
                                                                id="container_background_type_{{ $container->id }}"
                                                                onchange="handleContainerBackgroundTypeChange({{ $container->id }}, this.value, 'desktop')"
                                                                data-container-id="{{ $container->id }}">
                                                            <option value="none" {{ ($container->background_type ?? 'none') == 'none' ? 'selected' : '' }}>없음</option>
                                                            <option value="color" {{ ($container->background_type ?? 'none') == 'color' ? 'selected' : '' }}>단색</option>
                                                            <option value="gradient" {{ ($container->background_type ?? 'none') == 'gradient' ? 'selected' : '' }}>그라데이션</option>
                                                            <option value="image" {{ ($container->background_type ?? 'none') == 'image' ? 'selected' : '' }}>이미지</option>
                                                        </select>
                                                        <div id="container_background_color_{{ $container->id }}" style="display: {{ ($container->background_type ?? 'none') == 'color' ? 'inline-flex' : 'none' }}; align-items: center; gap: 4px; margin-left: 8px;">
                                                            <input type="color" 
                                                                   class="form-control form-control-color" 
                                                                   id="container_background_color_input_{{ $container->id }}"
                                                                   value="{{ $container->background_color ?? '#ffffff' }}"
                                                                   style="width: 40px; height: 38px;"
                                                                   onchange="updateContainerBackgroundColor({{ $container->id }})"
                                                                   title="배경 색상">
                                                            <input type="range" 
                                                                   class="form-range" 
                                                                   id="container_background_color_alpha_{{ $container->id }}"
                                                                   min="0" 
                                                                   max="100" 
                                                                   value="{{ isset($container->background_color_alpha) ? $container->background_color_alpha : 100 }}"
                                                                   style="width: 80px;"
                                                                   onchange="updateContainerBackgroundColor({{ $container->id }})"
                                                                   title="투명도">
                                                            <small class="text-muted" style="font-size: 0.75rem; min-width: 35px;" id="container_background_color_alpha_value_{{ $container->id }}">{{ isset($container->background_color_alpha) ? $container->background_color_alpha : 100 }}%</small>
                                                            <input type="hidden" 
                                                                   id="container_background_color_alpha_hidden_{{ $container->id }}"
                                                                   value="{{ isset($container->background_color_alpha) ? $container->background_color_alpha : 100 }}">
                                                        </div>
                                                        <div id="container_background_gradient_{{ $container->id }}" style="display: {{ ($container->background_type ?? 'none') == 'gradient' ? 'inline-flex' : 'none' }}; align-items: center; gap: 4px; margin-left: 8px;">
                                                            <div id="container_gradient_preview_{{ $container->id }}" 
                                                                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient({{ $container->background_gradient_angle ?? 90 }}deg, {{ $container->background_gradient_start ?? '#ffffff' }}, {{ $container->background_gradient_end ?? '#000000' }});"
                                                                 onclick="openGradientModal({{ $container->id }}, 'custom')"
                                                                 title="그라데이션 설정">
                                                            </div>
                                                            <input type="hidden" 
                                                                   id="container_background_gradient_start_{{ $container->id }}"
                                                                   value="{{ $container->background_gradient_start ?? '#ffffff' }}">
                                                            <input type="hidden" 
                                                                   id="container_background_gradient_end_{{ $container->id }}"
                                                                   value="{{ $container->background_gradient_end ?? '#000000' }}">
                                                            <input type="hidden" 
                                                                   id="container_background_gradient_angle_{{ $container->id }}"
                                                                   value="{{ $container->background_gradient_angle ?? 90 }}">
                                                        </div>
                                                        <div id="container_background_image_{{ $container->id }}" style="display: {{ ($container->background_type ?? 'none') == 'image' ? 'inline-flex' : 'none' }}; align-items: center; gap: 4px; margin-left: 8px; flex-wrap: wrap;">
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-secondary" 
                                                                    onclick="document.getElementById('container_background_image_file_{{ $container->id }}').click()"
                                                                    style="white-space: nowrap;">
                                                                <i class="bi bi-image"></i> 이미지 선택
                                                            </button>
                                                            <input type="file" 
                                                                   id="container_background_image_file_{{ $container->id }}"
                                                                   accept="image/*" 
                                                                   style="display: none;"
                                                                   onchange="handleContainerBackgroundImageUpload({{ $container->id }}, this)">
                                                            <input type="hidden" 
                                                                   id="container_background_image_url_{{ $container->id }}"
                                                                   value="{{ $container->background_image_url ?? '' }}">
                                                            @if($container->background_image_url)
                                                                <div id="container_background_image_preview_{{ $container->id }}" style="display: inline-block;">
                                                                    <img src="{{ $container->background_image_url }}" alt="미리보기" style="max-width: 60px; max-height: 60px; object-fit: cover; border-radius: 4px;">
                                                                    <button type="button" 
                                                                            class="btn btn-sm btn-danger ms-1" 
                                                                            onclick="removeContainerBackgroundImage({{ $container->id }})"
                                                                            title="이미지 제거">
                                                                        <i class="bi bi-x"></i>
                                                                    </button>
                                                                </div>
                                                            @endif
                                                            <input type="range" 
                                                                   class="form-range" 
                                                                   id="container_background_image_alpha_{{ $container->id }}"
                                                                   min="0" 
                                                                   max="100" 
                                                                   value="{{ isset($container->background_image_alpha) ? $container->background_image_alpha : 100 }}"
                                                                   style="width: 80px;"
                                                                   onchange="updateContainerBackgroundImageAlpha({{ $container->id }}, this.value)"
                                                                   title="투명도">
                                                            <small class="text-muted" style="font-size: 0.75rem; min-width: 35px;" id="container_background_image_alpha_value_{{ $container->id }}">{{ isset($container->background_image_alpha) ? $container->background_image_alpha : 100 }}%</small>
                                                            <input type="hidden" 
                                                                   id="container_background_image_alpha_hidden_{{ $container->id }}"
                                                                   value="{{ isset($container->background_image_alpha) ? $container->background_image_alpha : 100 }}">
                                                            <div class="form-check ms-2" style="display: inline-flex; align-items: center;">
                                                                <input type="checkbox" 
                                                                       class="form-check-input" 
                                                                       id="container_background_parallax_{{ $container->id }}"
                                                                       {{ ($container->background_parallax ?? false) ? 'checked' : '' }}
                                                                       onchange="updateContainerBackgroundParallax({{ $container->id }}, this.checked)"
                                                                       style="margin-top: 0;">
                                                                <label class="form-check-label ms-1" for="container_background_parallax_{{ $container->id }}" style="font-size: 0.75rem; white-space: nowrap;">패럴랙스</label>
                                                            </div>
                                                        </div>
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
                                                        <label class="form-label small mb-1">배경</label>
                                                        <select class="form-select form-select-sm" 
                                                                id="container_background_type_mobile_{{ $container->id }}"
                                                                onchange="handleContainerBackgroundTypeChange({{ $container->id }}, this.value, 'mobile')"
                                                                data-container-id="{{ $container->id }}">
                                                            <option value="none" {{ ($container->background_type ?? 'none') == 'none' ? 'selected' : '' }}>없음</option>
                                                            <option value="color" {{ ($container->background_type ?? 'none') == 'color' ? 'selected' : '' }}>단색</option>
                                                            <option value="gradient" {{ ($container->background_type ?? 'none') == 'gradient' ? 'selected' : '' }}>그라데이션</option>
                                                            <option value="image" {{ ($container->background_type ?? 'none') == 'image' ? 'selected' : '' }}>이미지</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12" id="container_background_color_mobile_{{ $container->id }}" style="display: {{ ($container->background_type ?? 'none') == 'color' ? 'block' : 'none' }};">
                                                        <label class="form-label small mb-1">색상</label>
                                                        <input type="color" 
                                                               class="form-control form-control-color" 
                                                               id="container_background_color_input_mobile_{{ $container->id }}"
                                                               value="{{ $container->background_color ?? '#ffffff' }}"
                                                               onchange="updateContainerBackground({{ $container->id }}, 'color', this.value)">
                                                    </div>
                                                    <div class="col-12" id="container_background_gradient_mobile_{{ $container->id }}" style="display: {{ ($container->background_type ?? 'none') == 'gradient' ? 'block' : 'none' }};">
                                                        <label class="form-label small mb-1">그라데이션</label>
                                                        <div id="container_gradient_preview_mobile_{{ $container->id }}" 
                                                             style="width: 100%; height: 60px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient({{ $container->background_gradient_angle ?? 90 }}deg, {{ $container->background_gradient_start ?? '#ffffff' }}, {{ $container->background_gradient_end ?? '#000000' }});"
                                                             onclick="openGradientModal({{ $container->id }}, 'custom')"
                                                             title="그라데이션 설정">
                                                        </div>
                                                        <input type="hidden" 
                                                               id="container_background_gradient_start_mobile_{{ $container->id }}"
                                                               value="{{ $container->background_gradient_start ?? '#ffffff' }}">
                                                        <input type="hidden" 
                                                               id="container_background_gradient_end_mobile_{{ $container->id }}"
                                                               value="{{ $container->background_gradient_end ?? '#000000' }}">
                                                        <input type="hidden" 
                                                               id="container_background_gradient_angle_mobile_{{ $container->id }}"
                                                               value="{{ $container->background_gradient_angle ?? 90 }}">
                                                    </div>
                                                    <div class="col-12" id="container_background_image_mobile_{{ $container->id }}" style="display: {{ ($container->background_type ?? 'none') == 'image' ? 'block' : 'none' }};">
                                                        <label class="form-label small mb-1">이미지 URL</label>
                                                        <input type="text" 
                                                               class="form-control form-control-sm" 
                                                               id="container_background_image_url_mobile_{{ $container->id }}"
                                                               value="{{ $container->background_image_url ?? '' }}"
                                                               placeholder="https://example.com/image.jpg"
                                                               onchange="updateContainerBackground({{ $container->id }}, 'image', this.value)">
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
                                                                                 data-container-id="{{ $container->id }}"
                                                                                 data-column-index="{{ $i }}"
                                                                                 data-widget-title="{{ $widget->title }}"
                                                                                 data-widget-type="{{ $widget->type }}"
                                                                                 data-widget-active="{{ $widget->is_active ? '1' : '0' }}"
                                                                                 data-widget-settings="{{ json_encode($widget->settings ?? []) }}">
                                                                                {{-- 데스크탑 버전 (이름 아래 아이콘 버튼) --}}
                                                                                <div class="card-body p-2 d-none d-md-block">
                                                                                    <div>
                                                                                        <h6 class="mb-0 small">
                                                                                            {{ $widget->title }}
                                                                                            @if(!$widget->is_active)
                                                                                                <span class="badge bg-secondary ms-1">비활성</span>
                                                                                            @endif
                                                                                        </h6>
                                                                                        <small class="text-muted d-block mb-2">{{ $availableTypes[$widget->type] ?? $widget->type }}</small>
                                                                                        <div class="d-flex gap-1">
                                                                                            <span class="bi-grip-vertical btn btn-sm btn-outline-secondary p-1" 
                                                                                                  style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; cursor: move;"
                                                                                                  title="드래그하여 이동">
                                                                                                <i class="bi bi-grip-vertical" style="font-size: 14px;"></i>
                                                                                            </span>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-secondary p-1" 
                                                                                                    onclick="moveCustomPageWidgetUp({{ $widget->id }}, {{ $container->id }}, {{ $i }})"
                                                                                                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                                                    title="위로 이동">
                                                                                                <i class="bi bi-arrow-up" style="font-size: 12px;"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-secondary p-1" 
                                                                                                    onclick="moveCustomPageWidgetDown({{ $widget->id }}, {{ $container->id }}, {{ $i }})"
                                                                                                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                                                    title="아래로 이동">
                                                                                                <i class="bi bi-arrow-down" style="font-size: 12px;"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-info p-1" 
                                                                                                    onclick="openCustomPageWidgetAnimationModal({{ $widget->id }})"
                                                                                                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                                                    title="애니메이션 설정">
                                                                                                <i class="bi bi-magic" style="font-size: 12px;"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-primary p-1 edit-custom-page-widget-btn" 
                                                                                                    data-widget-id="{{ $widget->id }}"
                                                                                                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                                                    title="설정">
                                                                                                <i class="bi bi-gear" style="font-size: 12px;"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-danger p-1 delete-custom-page-widget-btn" 
                                                                                                    data-widget-id="{{ $widget->id }}"
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
                                                                                    <div class="pt-2 border-top">
                                                                                        <div class="d-flex gap-2 justify-content-end mb-2">
                                                                                            <span class="bi-grip-vertical btn btn-sm btn-outline-secondary" 
                                                                                                  style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; cursor: move; touch-action: none;"
                                                                                                  title="드래그하여 이동">
                                                                                                <i class="bi bi-grip-vertical"></i>
                                                                                            </span>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-secondary" 
                                                                                                    onclick="moveCustomPageWidgetUp({{ $widget->id }}, {{ $container->id }}, {{ $i }})"
                                                                                                    title="위로 이동"
                                                                                                    style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                                <i class="bi bi-arrow-up"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-secondary" 
                                                                                                    onclick="moveCustomPageWidgetDown({{ $widget->id }}, {{ $container->id }}, {{ $i }})"
                                                                                                    title="아래로 이동"
                                                                                                    style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                                <i class="bi bi-arrow-down"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                        <div class="d-flex gap-2 justify-content-end">
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-info" 
                                                                                                    onclick="openCustomPageWidgetAnimationModal({{ $widget->id }})"
                                                                                                    title="애니메이션 설정"
                                                                                                    style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                                <i class="bi bi-magic"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-primary edit-custom-page-widget-btn" 
                                                                                                    data-widget-id="{{ $widget->id }}"
                                                                                                    title="설정"
                                                                                                    style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                                <i class="bi bi-gear"></i>
                                                                                            </button>
                                                                                            <button type="button" 
                                                                                                    class="btn btn-sm btn-outline-danger delete-custom-page-widget-btn" 
                                                                                                    data-widget-id="{{ $widget->id }}"
                                                                                                    title="삭제"
                                                                                                    style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                                                                <i class="bi bi-trash"></i>
                                                                                            </button>
                                                                                        </div>
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
                <button type="button" class="btn btn-primary" onclick="saveAllCustomPageWidgets()">
                    <i class="bi bi-save me-2"></i>저장
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 커스텀 페이지 위젯 애니메이션 설정 모달 -->
<div class="modal fade" id="customPageWidgetAnimationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">위젯 애니메이션 설정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customPageWidgetAnimationForm">
                    <input type="hidden" id="custom_page_widget_animation_id" name="widget_id">
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
                                    onclick="selectCustomPageAnimationDirection('left', this)">
                                <i class="bi bi-arrow-left"></i> 좌
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="right"
                                    onclick="selectCustomPageAnimationDirection('right', this)">
                                <i class="bi bi-arrow-right"></i> 우
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="up"
                                    onclick="selectCustomPageAnimationDirection('up', this)">
                                <i class="bi bi-arrow-up"></i> 상
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="down"
                                    onclick="selectCustomPageAnimationDirection('down', this)">
                                <i class="bi bi-arrow-down"></i> 하
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary animation-direction-btn" 
                                    data-direction="none"
                                    onclick="selectCustomPageAnimationDirection('none', this)">
                                없음
                            </button>
                        </div>
                        <input type="hidden" id="custom_page_widget_animation_direction" name="animation_direction" value="none">
                    </div>
                    <div class="mb-3">
                        <label for="custom_page_widget_animation_delay" class="form-label">
                            애니메이션 딜레이 (초)
                            <i class="bi bi-question-circle text-muted ms-1" 
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="위젯이 화면에 나타난 후 애니메이션이 시작되기까지의 지연 시간입니다."></i>
                        </label>
                        <input type="number" class="form-control" id="custom_page_widget_animation_delay" name="animation_delay" value="0" min="0" max="5" step="0.1">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomPageWidgetAnimation()">저장</button>
            </div>
        </div>
    </div>
</div>

<!-- 저장 완료 알림 모달 -->
<div class="modal fade" id="saveCustomPageWidgetSuccessModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
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

<!-- 위젯 설정 모달 -->
<div class="modal fade" id="customPageWidgetSettingsModal" tabindex="-1" 
     data-update-route="{{ route('admin.custom-pages.widgets.update', ['site' => $site->slug, 'customPage' => $customPage->id, 'widget' => ':id']) }}"
     data-fetch-route="{{ route('admin.custom-pages.edit', ['site' => $site->slug, 'customPage' => $customPage->id]) }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">위젯 설정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCustomPageWidgetForm">
                    <input type="hidden" id="edit_custom_page_widget_id" name="id">
                    <div class="mb-3" id="edit_custom_page_widget_board_container" style="display: none;">
                        <label for="edit_custom_page_widget_board_id" class="form-label">게시판 선택</label>
                        <select class="form-select" id="edit_custom_page_widget_board_id" name="board_id">
                            <option value="">선택하세요</option>
                            @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                                <option value="{{ $board->id }}">{{ $board->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_board_viewer_no_background_container" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_custom_page_widget_board_viewer_no_background" name="board_viewer_no_background">
                            <label class="form-check-label" for="edit_custom_page_widget_board_viewer_no_background">
                                배경색 없음 (그림자도 함께 제거됨)
                            </label>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_sort_order_container" style="display: none;">
                        <label for="edit_custom_page_widget_sort_order" class="form-label">표시 방식</label>
                        <select class="form-select" id="edit_custom_page_widget_sort_order" name="sort_order">
                            <option value="latest">최신순</option>
                            <option value="oldest">예전순</option>
                            <option value="random">랜덤</option>
                            <option value="popular">인기순</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_marquee_direction_container" style="display: none;">
                        <label class="form-label">전광판 표시 방향</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="edit_custom_page_direction" id="edit_custom_page_direction_left" value="left">
                            <label class="btn btn-outline-primary" for="edit_custom_page_direction_left">
                                <i class="bi bi-arrow-left"></i> 좌
                            </label>
                            <input type="radio" class="btn-check" name="edit_custom_page_direction" id="edit_custom_page_direction_right" value="right">
                            <label class="btn btn-outline-primary" for="edit_custom_page_direction_right">
                                <i class="bi bi-arrow-right"></i> 우
                            </label>
                            <input type="radio" class="btn-check" name="edit_custom_page_direction" id="edit_custom_page_direction_up" value="up">
                            <label class="btn btn-outline-primary" for="edit_custom_page_direction_up">
                                <i class="bi bi-arrow-up"></i> 상
                            </label>
                            <input type="radio" class="btn-check" name="edit_custom_page_direction" id="edit_custom_page_direction_down" value="down">
                            <label class="btn btn-outline-primary" for="edit_custom_page_direction_down">
                                <i class="bi bi-arrow-down"></i> 하
                            </label>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_gallery_container" style="display: none;">
                        <label for="edit_custom_page_widget_gallery_board_id" class="form-label">
                            게시판 선택
                            <i class="bi bi-question-circle text-muted ms-1" 
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="사진형, 북마크, 블로그, 이벤트 게시판만 선택 가능합니다."></i>
                        </label>
                        <select class="form-select" id="edit_custom_page_widget_gallery_board_id" name="gallery_board_id">
                            <option value="">선택하세요</option>
                            @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                                @if(in_array($board->type, ['photo', 'bookmark', 'blog', 'pinterest', 'event']))
                                    <option value="{{ $board->id }}">{{ $board->name }} @if($board->type === 'pinterest')(핀터레스트)@elseif($board->type === 'event')(이벤트)@endif</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_gallery_display_type_container" style="display: none;">
                        <label for="edit_custom_page_widget_gallery_display_type" class="form-label">표시 방식</label>
                        <select class="form-select" id="edit_custom_page_widget_gallery_display_type" name="gallery_display_type" onchange="handleEditMainGalleryDisplayTypeChange()">
                            <option value="grid">일반</option>
                            <option value="slide">슬라이드</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_gallery_grid_container" style="display: none;">
                        <div class="row">
                            <div class="col-6">
                                <label for="edit_custom_page_widget_gallery_cols" class="form-label">가로 개수</label>
                                <select class="form-select" id="edit_custom_page_widget_gallery_cols" name="gallery_cols">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="edit_custom_page_widget_gallery_rows" class="form-label">세로 줄수</label>
                                <select class="form-select" id="edit_custom_page_widget_gallery_rows" name="gallery_rows">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_gallery_slide_container" style="display: none;">
                        <div class="mb-2">
                            <label for="edit_custom_page_widget_gallery_slide_cols" class="form-label">가로 개수</label>
                            <select class="form-select" id="edit_custom_page_widget_gallery_slide_cols" name="gallery_slide_cols">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
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
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_gallery_show_title_container" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="edit_custom_page_widget_gallery_show_title" 
                                   name="gallery_show_title">
                            <label class="form-check-label" for="edit_custom_page_widget_gallery_show_title">
                                제목 표시
                            </label>
                        </div>
                        <small class="text-muted">썸네일 이미지 하단에 게시글 제목을 표시합니다.</small>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_custom_html_container" style="display: none;">
                        <label for="edit_custom_page_widget_custom_html" class="form-label">HTML 코드</label>
                        <textarea class="form-control" 
                                  id="edit_custom_page_widget_custom_html" 
                                  name="custom_html" 
                                  rows="10"
                                  placeholder="<style><script><html> 코드를 입력하세요"></textarea>
                        <small class="text-muted">위젯에 표시할 HTML 코드를 입력하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_block_container" style="display: none;">
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_custom_page_widget_block_enable_image" onchange="toggleBlockImageFields('edit_custom_page_widget_block')">
                                <label class="form-check-label" for="edit_custom_page_widget_block_enable_image">
                                    블록 위에 이미지 활성화
                                </label>
                            </div>
                        </div>
                        <div class="mb-3" id="edit_custom_page_widget_block_image_container" style="display: none;">
                            <label for="edit_custom_page_widget_block_image" class="form-label">이미지 선택</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_image" 
                                   name="block_image"
                                   accept="image/*"
                                   onchange="previewBlockImage(this, 'edit_custom_page_widget_block_image_preview')">
                            <input type="hidden" id="edit_custom_page_widget_block_image_url" name="block_image_url">
                            <div class="mt-2" id="edit_custom_page_widget_block_image_preview_container" style="display: none;">
                                <img id="edit_custom_page_widget_block_image_preview" src="" alt="미리보기" style="max-width: 100%; height: auto; border-radius: 4px;">
                                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeBlockImage('edit_custom_page_widget_block')">이미지 삭제</button>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_custom_page_widget_block_image_padding_top" class="form-label">이미지 상단 패딩 (px)</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="edit_custom_page_widget_block_image_padding_top" 
                                           name="block_image_padding_top" 
                                           value="0"
                                           min="0"
                                           max="200"
                                           step="1"
                                           placeholder="0">
                                    <small class="text-muted">이미지 상단 패딩을 입력하세요 (0~200).</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_custom_page_widget_block_image_padding_bottom" class="form-label">이미지 하단 패딩 (px)</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="edit_custom_page_widget_block_image_padding_bottom" 
                                           name="block_image_padding_bottom" 
                                           value="0"
                                           min="0"
                                           max="200"
                                           step="1"
                                           placeholder="0">
                                    <small class="text-muted">이미지 하단 패딩을 입력하세요 (0~200).</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_custom_page_widget_block_image_padding_left" class="form-label">이미지 좌측 패딩 (px)</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="edit_custom_page_widget_block_image_padding_left" 
                                           name="block_image_padding_left" 
                                           value="0"
                                           min="0"
                                           max="200"
                                           step="1"
                                           placeholder="0">
                                    <small class="text-muted">이미지 좌측 패딩을 입력하세요 (0~200).</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_custom_page_widget_block_image_padding_right" class="form-label">이미지 우측 패딩 (px)</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="edit_custom_page_widget_block_image_padding_right" 
                                           name="block_image_padding_right" 
                                           value="0"
                                           min="0"
                                           max="200"
                                           step="1"
                                           placeholder="0">
                                    <small class="text-muted">이미지 우측 패딩을 입력하세요 (0~200).</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_title" class="form-label">제목</label>
                            <textarea class="form-control" 
                                      id="edit_custom_page_widget_block_title" 
                                      name="block_title" 
                                      rows="2"
                                      placeholder="제목을 입력하세요 (엔터로 줄바꿈)"></textarea>
                            <small class="text-muted">엔터 키로 줄바꿈이 가능합니다.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_content" class="form-label">내용</label>
                            <textarea class="form-control" 
                                      id="edit_custom_page_widget_block_content" 
                                      name="block_content" 
                                      rows="3"
                                      placeholder="내용을 입력하세요"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">텍스트 정렬</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="edit_custom_page_block_text_align" id="edit_custom_page_block_align_left" value="left" checked>
                                <label class="btn btn-outline-primary" for="edit_custom_page_block_align_left">
                                    <i class="bi bi-text-left"></i> 좌
                                </label>
                                <input type="radio" class="btn-check" name="edit_custom_page_block_text_align" id="edit_custom_page_block_align_center" value="center">
                                <label class="btn btn-outline-primary" for="edit_custom_page_block_align_center">
                                    <i class="bi bi-text-center"></i> 중앙
                                </label>
                                <input type="radio" class="btn-check" name="edit_custom_page_block_text_align" id="edit_custom_page_block_align_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_custom_page_block_align_right">
                                    <i class="bi bi-text-right"></i> 우
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_title_font_size" class="form-label">제목 폰트 사이즈 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_title_font_size" 
                                   name="block_title_font_size" 
                                   value="16"
                                   min="8"
                                   max="72"
                                   step="1"
                                   placeholder="16">
                            <small class="text-muted">기본값: 16px</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_content_font_size" class="form-label">내용 폰트 사이즈 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_content_font_size" 
                                   name="block_content_font_size" 
                                   value="14"
                                   min="8"
                                   max="48"
                                   step="1"
                                   placeholder="14">
                            <small class="text-muted">기본값: 14px</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_background_type" class="form-label">배경</label>
                            <select class="form-select" id="edit_custom_page_widget_block_background_type" name="block_background_type" onchange="handleEditCustomPageBlockBackgroundTypeChange()">
                                <option value="none">배경 없음</option>
                                <option value="color">컬러</option>
                                <option value="gradient">그라데이션</option>
                            </select>
                        </div>
                        <div class="mb-3" id="edit_custom_page_widget_block_color_container" style="display: none;">
                            <label for="edit_custom_page_widget_block_background_color" class="form-label">적용 컬러</label>
                            <input type="color" 
                                   class="form-control form-control-color mb-2" 
                                   id="edit_custom_page_widget_block_background_color" 
                                   name="block_background_color" 
                                   value="#007bff">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <label for="edit_custom_page_widget_block_background_color_alpha" class="form-label mb-0">투명도 (%)</label>
                                </div>
                                <div class="col-auto">
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           id="edit_custom_page_widget_block_background_color_alpha" 
                                           name="block_background_color_alpha"
                                           min="0" 
                                           max="100" 
                                           value="100"
                                           style="width: 80px;">
                                </div>
                            </div>
                            <small class="text-muted">0~100 사이의 값을 입력하세요. 0은 완전 투명, 100은 불투명입니다.</small>
                        </div>
                        <div class="mb-3" id="edit_custom_page_widget_block_gradient_container" style="display: none;">
                            <label class="form-label">그라데이션 설정</label>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div id="edit_custom_page_widget_block_gradient_preview" 
                                     style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #ffffff, #000000);"
                                     onclick="openGradientModal('edit_custom_page_widget_block', 'custom')"
                                     title="그라데이션 설정">
                                </div>
                                <input type="hidden" 
                                       id="edit_custom_page_widget_block_gradient_start"
                                       name="block_background_gradient_start" 
                                       value="#ffffff">
                                <input type="hidden" 
                                       id="edit_custom_page_widget_block_gradient_end"
                                       name="block_background_gradient_end" 
                                       value="#000000">
                                <input type="hidden" 
                                       id="edit_custom_page_widget_block_gradient_angle"
                                       name="block_background_gradient_angle" 
                                       value="90">
                            </div>
                            <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_title_color" class="form-label">제목 컬러</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_custom_page_widget_block_title_color" 
                                   name="block_title_color" 
                                   value="#ffffff">
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_content_color" class="form-label">내용 컬러</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_custom_page_widget_block_content_color" 
                                   name="block_content_color" 
                                   value="#ffffff">
                        </div>
                        <div class="mb-3" id="edit_custom_page_widget_block_image_container" style="display: none;">
                            <label class="form-label">배경 이미지</label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="edit_custom_page_widget_block_image_btn"
                                        onclick="document.getElementById('edit_custom_page_widget_block_image_input').click()">
                                    <i class="bi bi-image"></i> 이미지 선택
                                </button>
                                <input type="file" 
                                       id="edit_custom_page_widget_block_image_input" 
                                       name="block_background_image_file" 
                                       accept="image/*" 
                                       style="display: none;"
                                       onchange="handleEditCustomPageBlockImageChange(this)">
                                <input type="hidden" id="edit_custom_page_widget_block_background_image" name="block_background_image_url">
                                <div id="edit_custom_page_widget_block_image_preview" style="display: none;">
                                    <img id="edit_custom_page_widget_block_image_preview_img" src="" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditMainBlockImage()">삭제</button>
                                </div>
                            </div>
                            <div class="row align-items-center mt-2">
                                <div class="col-auto">
                                    <label for="edit_custom_page_widget_block_background_image_alpha" class="form-label mb-0">투명도 (%)</label>
                                </div>
                                <div class="col-auto">
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           id="edit_custom_page_widget_block_background_image_alpha" 
                                           name="block_background_image_alpha"
                                           min="0" 
                                           max="100" 
                                           value="100"
                                           style="width: 80px;">
                                </div>
                            </div>
                            <small class="text-muted">0~100 사이의 값을 입력하세요. 0은 완전 투명, 100은 불투명입니다.</small>
                            <div class="form-check mt-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_custom_page_widget_block_background_image_full_width" 
                                       name="block_background_image_full_width">
                                <label class="form-check-label" for="edit_custom_page_widget_block_background_image_full_width">
                                    이미지 가로 100% (비율 유지)
                                </label>
                            </div>
                            <small class="text-muted">활성화 시 이미지가 블록 너비에 맞게 확장되고 높이는 비율에 맞게 자동 조절됩니다.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_padding_top" class="form-label">상단 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_padding_top" 
                                   name="block_padding_top" 
                                   value="20"
                                   min="0"
                                   max="200"
                                   step="1"
                                   placeholder="20">
                            <small class="text-muted">블록 상단 여백을 입력하세요 (0~200).</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_padding_bottom" class="form-label">하단 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_padding_bottom" 
                                   name="block_padding_bottom" 
                                   value="20"
                                   min="0"
                                   max="200"
                                   step="1"
                                   placeholder="20">
                            <small class="text-muted">블록 하단 여백을 입력하세요 (0~200).</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_padding_left" class="form-label">좌측 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_padding_left" 
                                   name="block_padding_left" 
                                   value="20"
                                   min="0"
                                   max="200"
                                   step="1"
                                   placeholder="20">
                            <small class="text-muted">블록 좌측 여백을 입력하세요 (0~200).</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_padding_right" class="form-label">우측 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_padding_right" 
                                   name="block_padding_right" 
                                   value="20"
                                   min="0"
                                   max="200"
                                   step="1"
                                   placeholder="20">
                            <small class="text-muted">블록 우측 여백을 입력하세요 (0~200).</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_title_content_gap" class="form-label">제목-내용 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_title_content_gap" 
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
                            <div id="edit_custom_page_widget_block_buttons_list">
                                <!-- 버튼들이 여기에 동적으로 추가됨 -->
                            </div>
                            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addEditCustomPageBlockButton()">
                                <i class="bi bi-plus-circle me-1"></i>버튼 추가
                            </button>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_button_top_margin" class="form-label">버튼 상단 여백 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_button_top_margin" 
                                   name="block_button_top_margin" 
                                   value="12"
                                   min="0"
                                   max="100"
                                   step="1"
                                   placeholder="12">
                            <small class="text-muted">버튼과 위 요소 사이의 여백을 입력하세요 (0~100).</small>
                        </div>
                        <div class="mb-3" id="edit_custom_page_widget_block_link_container">
                            <label for="edit_custom_page_widget_block_link" class="form-label">
                                연결 링크 <small class="text-muted">(선택사항)</small>
                                <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="버튼이 있는 경우 버튼에 링크가 연결되고, 버튼이 없는 경우 블록 전체에 링크가 연결됩니다."></i>
                            </label>
                            <input type="url" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_link" 
                                   name="block_link" 
                                   placeholder="https://example.com">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_custom_page_widget_block_open_new_tab" 
                                       name="block_open_new_tab">
                                <label class="form-check-label" for="edit_custom_page_widget_block_open_new_tab">
                                    새창에서 열기
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_block_slide_container" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">슬라이드 방향</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="edit_custom_page_block_slide_direction" id="edit_custom_page_block_slide_direction_left" value="left" checked>
                                <label class="btn btn-outline-primary" for="edit_custom_page_block_slide_direction_left">
                                    <i class="bi bi-arrow-left"></i> 좌
                                </label>
                                <input type="radio" class="btn-check" name="edit_custom_page_block_slide_direction" id="edit_custom_page_block_slide_direction_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_custom_page_block_slide_direction_right">
                                    <i class="bi bi-arrow-right"></i> 우
                                </label>
                                <input type="radio" class="btn-check" name="edit_custom_page_block_slide_direction" id="edit_custom_page_block_slide_direction_up" value="up">
                                <label class="btn btn-outline-primary" for="edit_custom_page_block_slide_direction_up">
                                    <i class="bi bi-arrow-up"></i> 상
                                </label>
                                <input type="radio" class="btn-check" name="edit_custom_page_block_slide_direction" id="edit_custom_page_block_slide_direction_down" value="down">
                                <label class="btn btn-outline-primary" for="edit_custom_page_block_slide_direction_down">
                                    <i class="bi bi-arrow-down"></i> 하
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">슬라이드 유지 시간 (초)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_block_slide_hold_time" 
                                   name="edit_custom_page_block_slide_hold_time" 
                                   value="3" 
                                   min="0.5" 
                                   max="60" 
                                   step="0.1"
                                   placeholder="3">
                            <small class="text-muted">각 슬라이드가 보여지는 시간 (초 단위, 소수점 입력 가능)</small>
                        </div>
                        <div id="edit_custom_page_widget_block_slide_items">
                            <!-- 블록 아이템들이 여기에 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="addEditMainBlockSlideItem()">
                            <i class="bi bi-plus-circle me-2"></i>블록 추가하기
                        </button>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_image_container" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">이미지 선택</label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="edit_custom_page_widget_image_btn"
                                        onclick="document.getElementById('edit_custom_page_widget_image_input').click()">
                                    <i class="bi bi-image"></i> 이미지 선택
                                </button>
                                <input type="file" 
                                       id="edit_custom_page_widget_image_input" 
                                       name="image_file" 
                                       accept="image/*" 
                                       style="display: none;"
                                       onchange="handleEditMainImageChange(this)">
                                <input type="hidden" id="edit_custom_page_widget_image_url" name="image_url">
                                <div id="edit_custom_page_widget_image_preview" style="display: none;">
                                    <img id="edit_custom_page_widget_image_preview_img" src="" alt="미리보기" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditMainImage()">삭제</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_image_width" class="form-label">이미지 width <small class="text-muted">(%)</small></label>
                            <div class="input-group" style="max-width: 150px;">
                                <input type="number" 
                                       class="form-control" 
                                       id="edit_custom_page_widget_image_width" 
                                       name="image_width" 
                                       value="100"
                                       min="1" 
                                       max="100"
                                       placeholder="100">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_image_link" class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
                            <input type="url" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_image_link" 
                                   name="image_link" 
                                   placeholder="https://example.com">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_custom_page_widget_image_open_new_tab" 
                                       name="image_open_new_tab">
                                <label class="form-check-label" for="edit_custom_page_widget_image_open_new_tab">
                                    새창에서 열기
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input edit-custom-page-widget-image-text-overlay" 
                                       type="checkbox" 
                                       id="edit_custom_page_widget_image_text_overlay" 
                                       name="image_text_overlay"
                                       onchange="toggleEditCustomPageWidgetImageTextOverlay()">
                                <label class="form-check-label" for="edit_custom_page_widget_image_text_overlay">
                                    이미지 위 텍스트 활성화
                                </label>
                            </div>
                        </div>
                        <div id="edit_custom_page_widget_image_text_overlay_container" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">제목</label>
                                <input type="text" class="form-control edit-custom-page-widget-image-title" id="edit_custom_page_widget_image_title" name="image_title" placeholder="제목을 입력하세요">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">제목 폰트 크기 (px)</label>
                                <input type="number" class="form-control edit-custom-page-widget-image-title-font-size" id="edit_custom_page_widget_image_title_font_size" name="image_title_font_size" value="24" min="10" max="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">내용</label>
                                <textarea class="form-control edit-custom-page-widget-image-content" id="edit_custom_page_widget_image_content" name="image_content" rows="3" placeholder="내용을 입력하세요"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">내용 폰트 크기 (px)</label>
                                <input type="number" class="form-control edit-custom-page-widget-image-content-font-size" id="edit_custom_page_widget_image_content_font_size" name="image_content_font_size" value="16" min="10" max="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">제목과 내용 사이 여백 (px)</label>
                                <input type="number" class="form-control edit-custom-page-widget-image-title-content-gap" id="edit_custom_page_widget_image_title_content_gap" name="image_title_content_gap" value="10" min="0" max="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">패딩</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">좌 (px)</label>
                                        <input type="number" class="form-control edit-custom-page-widget-image-text-padding-left" id="edit_custom_page_widget_image_text_padding_left" name="image_text_padding_left" value="0" min="0" max="200">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">우 (px)</label>
                                        <input type="number" class="form-control edit-custom-page-widget-image-text-padding-right" id="edit_custom_page_widget_image_text_padding_right" name="image_text_padding_right" value="0" min="0" max="200">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">상 (px)</label>
                                        <input type="number" class="form-control edit-custom-page-widget-image-text-padding-top" id="edit_custom_page_widget_image_text_padding_top" name="image_text_padding_top" value="0" min="0" max="200">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">하 (px)</label>
                                        <input type="number" class="form-control edit-custom-page-widget-image-text-padding-bottom" id="edit_custom_page_widget_image_text_padding_bottom" name="image_text_padding_bottom" value="10" min="0" max="200">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">수평 정렬</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check edit-custom-page-widget-image-align-h" name="edit_custom_page_widget_image_align_h" id="edit_custom_page_widget_image_align_left" value="left" checked>
                                    <label class="btn btn-outline-primary" for="edit_custom_page_widget_image_align_left"><i class="bi bi-text-left"></i> 좌측</label>
                                    <input type="radio" class="btn-check edit-custom-page-widget-image-align-h" name="edit_custom_page_widget_image_align_h" id="edit_custom_page_widget_image_align_center" value="center">
                                    <label class="btn btn-outline-primary" for="edit_custom_page_widget_image_align_center"><i class="bi bi-text-center"></i> 중앙</label>
                                    <input type="radio" class="btn-check edit-custom-page-widget-image-align-h" name="edit_custom_page_widget_image_align_h" id="edit_custom_page_widget_image_align_right" value="right">
                                    <label class="btn btn-outline-primary" for="edit_custom_page_widget_image_align_right"><i class="bi bi-text-right"></i> 우측</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">수직 정렬</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check edit-custom-page-widget-image-align-v" name="edit_custom_page_widget_image_align_v" id="edit_custom_page_widget_image_align_top" value="top">
                                    <label class="btn btn-outline-primary" for="edit_custom_page_widget_image_align_top"><i class="bi bi-align-top"></i> 상단</label>
                                    <input type="radio" class="btn-check edit-custom-page-widget-image-align-v" name="edit_custom_page_widget_image_align_v" id="edit_custom_page_widget_image_align_middle" value="middle" checked>
                                    <label class="btn btn-outline-primary" for="edit_custom_page_widget_image_align_middle"><i class="bi bi-align-middle"></i> 중앙</label>
                                    <input type="radio" class="btn-check edit-custom-page-widget-image-align-v" name="edit_custom_page_widget_image_align_v" id="edit_custom_page_widget_image_align_bottom" value="bottom">
                                    <label class="btn btn-outline-primary" for="edit_custom_page_widget_image_align_bottom"><i class="bi bi-align-bottom"></i> 하단</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">텍스트 색상</label>
                                <input type="color" class="form-control form-control-color edit-custom-page-widget-image-text-color" id="edit_custom_page_widget_image_text_color" name="image_text_color" value="#ffffff" title="텍스트 색상 선택">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input edit-custom-page-widget-image-has-button" type="checkbox" id="edit_custom_page_widget_image_has_button" name="image_has_button" onchange="toggleEditCustomPageWidgetImageButton()">
                                    <label class="form-check-label" for="edit_custom_page_widget_image_has_button">버튼 추가</label>
                                </div>
                            </div>
                            <div id="edit_custom_page_widget_image_button_container" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">버튼 텍스트</label>
                                    <input type="text" class="form-control edit-custom-page-widget-image-button-text" id="edit_custom_page_widget_image_button_text" name="image_button_text" placeholder="자세히 보기">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">버튼 링크</label>
                                    <input type="url" class="form-control edit-custom-page-widget-image-button-link" id="edit_custom_page_widget_image_button_link" name="image_button_link" placeholder="https://example.com">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input edit-custom-page-widget-image-button-new-tab" type="checkbox" id="edit_custom_page_widget_image_button_new_tab" name="image_button_new_tab">
                                        <label class="form-check-label" for="edit_custom_page_widget_image_button_new_tab">새창에서 열기</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">버튼 색상</label>
                                    <input type="color" class="form-control form-control-color edit-custom-page-widget-image-button-color" id="edit_custom_page_widget_image_button_color" name="image_button_color" value="#0d6efd" title="버튼 색상 선택">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">버튼 텍스트 색상</label>
                                    <input type="color" class="form-control form-control-color edit-custom-page-widget-image-button-text-color" id="edit_custom_page_widget_image_button_text_color" name="image_button_text_color" value="#ffffff" title="버튼 텍스트 색상 선택">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">버튼 테두리 색상</label>
                                    <input type="color" class="form-control form-control-color edit-custom-page-widget-image-button-border-color" id="edit_custom_page_widget_image_button_border_color" name="image_button_border_color" value="#0d6efd" title="버튼 테두리 색상 선택">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">버튼 배경 투명도 (%)</label>
                                    <input type="number" class="form-control edit-custom-page-widget-image-button-opacity" id="edit_custom_page_widget_image_button_opacity" name="image_button_opacity" min="0" max="100" value="100">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">버튼 호버 배경 색상</label>
                                    <input type="color" class="form-control form-control-color edit-custom-page-widget-image-button-hover-bg-color" id="edit_custom_page_widget_image_button_hover_bg_color" name="image_button_hover_bg_color" value="#0b5ed7" title="버튼 호버 배경 색상 선택">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">버튼 호버 텍스트 색상</label>
                                    <input type="color" class="form-control form-control-color edit-custom-page-widget-image-button-hover-text-color" id="edit_custom_page_widget_image_button_hover_text_color" name="image_button_hover_text_color" value="#ffffff" title="버튼 호버 텍스트 색상 선택">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">버튼 호버 테두리 색상</label>
                                    <input type="color" class="form-control form-control-color edit-custom-page-widget-image-button-hover-border-color" id="edit_custom_page_widget_image_button_hover_border_color" name="image_button_hover_border_color" value="#0a58ca" title="버튼 호버 테두리 색상 선택">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_image_slide_container" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">슬라이드 방향</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="edit_custom_page_image_slide_direction" id="edit_custom_page_image_slide_direction_left" value="left" checked>
                                <label class="btn btn-outline-primary" for="edit_custom_page_image_slide_direction_left">
                                    <i class="bi bi-arrow-left"></i> 좌
                                </label>
                                <input type="radio" class="btn-check" name="edit_custom_page_image_slide_direction" id="edit_custom_page_image_slide_direction_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_custom_page_image_slide_direction_right">
                                    <i class="bi bi-arrow-right"></i> 우
                                </label>
                                <input type="radio" class="btn-check" name="edit_custom_page_image_slide_direction" id="edit_custom_page_image_slide_direction_up" value="up">
                                <label class="btn btn-outline-primary" for="edit_custom_page_image_slide_direction_up">
                                    <i class="bi bi-arrow-up"></i> 상
                                </label>
                                <input type="radio" class="btn-check" name="edit_custom_page_image_slide_direction" id="edit_custom_page_image_slide_direction_down" value="down">
                                <label class="btn btn-outline-primary" for="edit_custom_page_image_slide_direction_down">
                                    <i class="bi bi-arrow-down"></i> 하
                                </label>
                            </div>
                        </div>
                        <div id="edit_custom_page_widget_image_slide_items">
                            <!-- 이미지 아이템들이 여기에 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="addEditMainImageSlideItem()">
                            <i class="bi bi-plus-circle me-2"></i>이미지 추가하기
                        </button>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_limit_container" style="display: none;">
                        <label for="edit_custom_page_widget_limit" class="form-label">표시할 게시글 수</label>
                        <input type="number" 
                               class="form-control" 
                               id="edit_custom_page_widget_limit" 
                               name="limit" 
                               min="1" 
                               max="50" 
                               value="10"
                               placeholder="게시글 수를 입력하세요">
                        <small class="text-muted">1~50 사이의 숫자를 입력하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_ranking_container" style="display: none;">
                        <label class="form-label">랭킹 설정</label>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_custom_page_widget_rank_ranking" 
                                       name="enable_rank_ranking">
                                <label class="form-check-label" for="edit_custom_page_widget_rank_ranking">
                                    등급 랭킹
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_custom_page_widget_point_ranking" 
                                       name="enable_point_ranking">
                                <label class="form-check-label" for="edit_custom_page_widget_point_ranking">
                                    포인트 랭킹
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="edit_custom_page_widget_ranking_limit" class="form-label">표시할 순위 수</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_ranking_limit" 
                                   name="ranking_limit" 
                                   min="1" 
                                   max="50" 
                                   value="5"
                                   placeholder="순위 수를 입력하세요">
                            <small class="text-muted">1~50 사이의 숫자를 입력하세요.</small>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_tab_menu_container" style="display: none;">
                        <label class="form-label">탭메뉴 설정</label>
                        <div id="edit_main_tab_menu_list">
                            <!-- 탭메뉴 항목들이 여기에 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addEditCustomPageTabMenuItem()">
                            <i class="bi bi-plus-circle me-1"></i>탭메뉴 추가
                        </button>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_toggle_menu_container" style="display: none;">
                        <label for="edit_custom_page_widget_toggle_menu_id" class="form-label">토글 메뉴 선택</label>
                        <select class="form-select" id="edit_custom_page_widget_toggle_menu_id" name="toggle_menu_id">
                            <option value="">선택하세요</option>
                            <!-- 토글 메뉴 옵션들이 여기에 동적으로 추가됨 -->
                        </select>
                        <small class="text-muted">표시할 토글 메뉴를 선택하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_contact_form_container" style="display: none;">
                        <label for="edit_custom_page_widget_contact_form_id" class="form-label">컨텍트폼 선택</label>
                        <select class="form-select" id="edit_custom_page_widget_contact_form_id" name="contact_form_id">
                            <option value="">선택하세요</option>
                            @foreach(\App\Models\ContactForm::where('site_id', $site->id)->orderBy('created_at', 'desc')->get() as $contactForm)
                                <option value="{{ $contactForm->id }}">{{ $contactForm->title ?? '' }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">사용할 컨텍트폼을 선택하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_map_container" style="display: none;">
                        <label for="edit_custom_page_widget_map_id" class="form-label">지도 선택</label>
                        <select class="form-select" id="edit_custom_page_widget_map_id" name="map_id">
                            <option value="">선택하세요</option>
                            @php
                                $masterSite = \App\Models\Site::getMasterSite();
                                $googleApiKey = $masterSite ? \Illuminate\Support\Facades\DB::table('site_settings')->where('site_id', $masterSite->id)->where('key', 'map_api_google_key')->value('value') : null;
                                $naverApiKey = $masterSite ? \Illuminate\Support\Facades\DB::table('site_settings')->where('site_id', $masterSite->id)->where('key', 'map_api_naver_key')->value('value') : null;
                                $kakaoApiKey = $masterSite ? \Illuminate\Support\Facades\DB::table('site_settings')->where('site_id', $masterSite->id)->where('key', 'map_api_kakao_key')->value('value') : null;
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
                                    <option value="{{ $map->id }}">{{ $map->name ?? '' }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">사용할 지도를 선택하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_title_container_main">
                        <label for="edit_custom_page_widget_title" class="form-label">
                            위젯 제목 <span id="edit_custom_page_widget_title_optional" style="display: none;">(선택사항)</span>
                            <i class="bi bi-question-circle text-muted ms-1" 
                               id="edit_custom_page_widget_title_help"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="제목을 입력하지 않으면 위젯 제목이 표시되지 않습니다."></i>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_custom_page_widget_title" 
                               name="title" 
                               placeholder="위젯 제목을 입력하세요">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="edit_custom_page_widget_is_active" 
                                   name="is_active">
                            <label class="form-check-label" for="edit_custom_page_widget_is_active">
                                활성화
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomPageWidgetSettings()">저장</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.widget-item.sortable-ghost {
    opacity: 0.4;
    background: #f0f0f0;
}
.widget-item.sortable-chosen {
    cursor: move;
    touch-action: none; /* 모바일 터치 제스처 방지 */
}
.widget-item.sortable-drag {
    opacity: 0.8;
    touch-action: none; /* 모바일 터치 제스처 방지 */
}
.bi-grip-vertical {
    cursor: move;
    user-select: none;
    touch-action: none; /* 모바일 터치 제스처 방지 */
    -webkit-user-select: none; /* iOS Safari */
    -moz-user-select: none; /* Firefox */
    -ms-user-select: none; /* IE/Edge */
}
.bi-grip-vertical:hover {
    background-color: #e9ecef;
}
.widget-list-in-column {
    touch-action: pan-y; /* 세로 스크롤만 허용 */
}

/* 투명도 슬라이더 바 스타일 통일 */
.form-range {
    background: #6c757d;
    height: 6px;
}

.form-range::-webkit-slider-thumb {
    background: #0d6efd;
    border: 2px solid #fff;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    cursor: pointer;
    -webkit-appearance: none;
    appearance: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.form-range::-moz-range-thumb {
    background: #0d6efd;
    border: 2px solid #fff;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.form-range::-webkit-slider-runnable-track {
    background: #6c757d;
    height: 6px;
    border-radius: 3px;
}

.form-range::-moz-range-track {
    background: #6c757d;
    height: 6px;
    border-radius: 3px;
}

.form-range:focus {
    outline: none;
}

.form-range:focus::-webkit-slider-thumb {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}

.form-range:focus::-moz-range-thumb {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let currentContainerId = null;
let currentColumnIndex = null;
let currentEditWidgetId = null;
let customPageWidgetSortables = {}; // 각 위젯 리스트의 Sortable 인스턴스 저장

// DOM이 로드된 후 실행
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
    
    // 컨테이너 추가
    const addContainerForm = document.getElementById('addContainerForm');
    if (addContainerForm) {
        addContainerForm.addEventListener('submit', function(e) {
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
            // 칸 고정너비 체크박스 값 추가
            const fixedWidthColumnsCheckbox = document.getElementById('container_fixed_width_columns');
            if (fixedWidthColumnsCheckbox) {
                formData.set('fixed_width_columns', fixedWidthColumnsCheckbox.checked ? '1' : '0');
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>추가 중...';
            
            fetch('{{ route('admin.custom-pages.containers.store', ['site' => $site->slug, 'customPage' => $customPage->id]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || '서버 오류가 발생했습니다.');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('컨테이너 추가에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('컨테이너 추가 중 오류가 발생했습니다: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // Sortable.js 초기화 - 모든 위젯 리스트에 드래그 앤 드롭 기능 추가
    initializeCustomPageWidgetSortables();
});

// 커스텀 페이지 위젯 Sortable 초기화
function initializeCustomPageWidgetSortables() {
    // 모든 위젯 리스트 찾기
    const widgetLists = document.querySelectorAll('.widget-list-in-column');
    
    widgetLists.forEach(widgetList => {
        const containerId = widgetList.dataset.containerId;
        const columnIndex = widgetList.dataset.columnIndex;
        const sortableKey = `${containerId}_${columnIndex}`;
        
        // 이미 초기화된 경우 제거
        if (customPageWidgetSortables[sortableKey]) {
            customPageWidgetSortables[sortableKey].destroy();
        }
        
        // Sortable 초기화
        customPageWidgetSortables[sortableKey] = Sortable.create(widgetList, {
            group: 'custom-page-widgets', // 모든 위젯 리스트 간 이동 허용
            animation: 150,
            handle: '.bi-grip-vertical', // 드래그 핸들
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            touchStartThreshold: 5, // 모바일 터치 감지 임계값
            forceFallback: false, // 네이티브 HTML5 드래그 사용
            fallbackOnBody: true, // 모바일에서 body에 클론 생성
            swapThreshold: 0.65, // 교체 임계값
            onEnd: function(evt) {
                const fromContainerId = parseInt(evt.from.dataset.containerId);
                const fromColumnIndex = parseInt(evt.from.dataset.columnIndex);
                const toContainerId = parseInt(evt.to.dataset.containerId);
                const toColumnIndex = parseInt(evt.to.dataset.columnIndex);
                const widgetId = parseInt(evt.item.dataset.widgetId);
                
                // 위젯이 다른 컨테이너로 이동한 경우
                if (fromContainerId !== toContainerId || fromColumnIndex !== toColumnIndex) {
                    // 이동된 위젯 정보 저장
                    const movedWidget = {
                        id: widgetId,
                        fromContainerId: fromContainerId,
                        fromColumnIndex: fromColumnIndex,
                        toContainerId: toContainerId,
                        toColumnIndex: toColumnIndex
                    };
                    
                    // 새 위치의 순서 저장
                    saveCustomPageWidgetOrder(toContainerId, toColumnIndex, movedWidget);
                    
                    // 원래 위치의 순서도 저장 (위젯이 제거되었으므로)
                    if (fromContainerId !== toContainerId || fromColumnIndex !== toColumnIndex) {
                        saveCustomPageWidgetOrder(fromContainerId, fromColumnIndex);
                    }
                } else {
                    // 같은 컬럼 내에서 순서만 변경
                    saveCustomPageWidgetOrder(toContainerId, toColumnIndex);
                }
            }
        });
    });
}

// 컨테이너 가로 개수 업데이트
function updateContainerColumns(containerId, columns) {
    if (!confirm('컨테이너의 가로 개수를 변경하시겠습니까? 컬럼 수가 줄어들면 해당 컬럼의 위젯들이 자동으로 기존 컬럼들에 재배치됩니다.')) {
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
    formData.append('_method', 'PUT');
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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

// 컨테이너 배경 타입 변경 핸들러
function handleContainerBackgroundTypeChange(containerId, backgroundType, viewType) {
    const suffix = viewType === 'mobile' ? '_mobile' : '';
    
    // 모든 배경 옵션 숨기기
    document.getElementById(`container_background_color${suffix}_${containerId}`).style.display = 'none';
    document.getElementById(`container_background_gradient${suffix}_${containerId}`).style.display = 'none';
    document.getElementById(`container_background_image${suffix}_${containerId}`).style.display = 'none';
    
    // 선택된 타입에 따라 표시
    if (backgroundType === 'color') {
        document.getElementById(`container_background_color${suffix}_${containerId}`).style.display = viewType === 'mobile' ? 'block' : 'inline-block';
    } else if (backgroundType === 'gradient') {
        document.getElementById(`container_background_gradient${suffix}_${containerId}`).style.display = viewType === 'mobile' ? 'block' : 'inline-flex';
    } else if (backgroundType === 'image') {
        document.getElementById(`container_background_image${suffix}_${containerId}`).style.display = viewType === 'mobile' ? 'block' : 'inline-block';
    }
    
    // 배경 타입 업데이트
    updateContainerBackgroundType(containerId, backgroundType);
}

// 컨테이너 배경 타입 업데이트
function updateContainerBackgroundType(containerId, backgroundType) {
    const formData = new FormData();
    formData.append('background_type', backgroundType);
    formData.append('_method', 'PUT');
    
    // 현재 컨테이너 설정 유지
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (containerItem) {
        const columnsSelect = containerItem.querySelector('select[onchange*="updateContainerColumns"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        }
        const verticalAlignSelect = containerItem.querySelector('select[onchange*="updateContainerVerticalAlign"]');
        if (verticalAlignSelect) {
            formData.append('vertical_align', verticalAlignSelect.value);
        }
    }
    
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
            // 성공 알림은 표시하지 않고 조용히 업데이트
        } else {
            alert('배경 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('배경 설정 업데이트 중 오류가 발생했습니다.');
    });
}

// 컨테이너 배경 업데이트 (단색 또는 이미지)
function updateContainerBackground(containerId, backgroundType, value) {
    const formData = new FormData();
    formData.append('background_type', backgroundType);
    
    if (backgroundType === 'color') {
        formData.append('background_color', value);
    } else if (backgroundType === 'image') {
        formData.append('background_image_url', value);
    }
    
    formData.append('_method', 'PUT');
    
    // 현재 컨테이너 설정 유지
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (containerItem) {
        const columnsSelect = containerItem.querySelector('select[onchange*="updateContainerColumns"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        }
        const verticalAlignSelect = containerItem.querySelector('select[onchange*="updateContainerVerticalAlign"]');
        if (verticalAlignSelect) {
            formData.append('vertical_align', verticalAlignSelect.value);
        }
    }
    
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
            // 성공 알림은 표시하지 않고 조용히 업데이트
        } else {
            alert('배경 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('배경 설정 업데이트 중 오류가 발생했습니다.');
    });
}

// 컨테이너 추가 시 칸 고정너비 옵션 토글
function toggleFixedWidthColumnsAdd() {
    const fullWidthCheckbox = document.getElementById('container_full_width');
    const fixedWidthColumnsDiv = document.getElementById('fixed_width_columns_option_add');
    
    if (fullWidthCheckbox && fixedWidthColumnsDiv) {
        if (fullWidthCheckbox.checked) {
            fixedWidthColumnsDiv.style.display = 'block';
        } else {
            fixedWidthColumnsDiv.style.display = 'none';
            const fixedWidthColumnsCheckbox = document.getElementById('container_fixed_width_columns');
            if (fixedWidthColumnsCheckbox) {
                fixedWidthColumnsCheckbox.checked = false;
            }
        }
    }
}

// 기존 컨테이너 칸 고정너비 옵션 토글
function toggleFixedWidthColumnsOption(containerId) {
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    const fixedWidthColumnsDiv = document.getElementById(`fixed_width_columns_container_${containerId}`);
    const fixedWidthColumnsCheckbox = document.getElementById(`container_fixed_width_columns_${containerId}`);

    if (fullWidthCheckbox && fixedWidthColumnsDiv && fixedWidthColumnsCheckbox) {
        if (fullWidthCheckbox.checked) {
            fixedWidthColumnsDiv.style.display = 'flex';
        } else {
            fixedWidthColumnsDiv.style.display = 'none';
            if (fixedWidthColumnsCheckbox.checked) {
                fixedWidthColumnsCheckbox.checked = false;
                updateContainerFixedWidthColumns(containerId, false);
            }
        }
    }
}

// 컨테이너 칸 고정너비 업데이트
function updateContainerFixedWidthColumns(containerId, fixedWidthColumns) {
    try {
        const formData = new FormData();
        const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
        if (!containerItem) {
            console.error('Container item not found for ID:', containerId);
            alert('컨테이너를 찾을 수 없습니다.');
            return;
        }

        const columnsSelect = containerItem.querySelector('select[data-container-id="' + containerId + '"]');
        if (columnsSelect) formData.append('columns', columnsSelect.value);
        const allSelects = containerItem.querySelectorAll('select[data-container-id="' + containerId + '"]');
        if (allSelects.length >= 2) formData.append('vertical_align', allSelects[1].value);
        const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
        if (fullWidthCheckbox) formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
        const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
        if (fullHeightCheckbox) formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
        const widgetSpacingSelect = containerItem.querySelector('select[onchange*="updateContainerWidgetSpacing"]');
        if (widgetSpacingSelect) formData.append('widget_spacing', widgetSpacingSelect.value);

        formData.append('fixed_width_columns', fixedWidthColumns ? '1' : '0');
        formData.append('_method', 'PUT');

        fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `<i class="bi bi-check-circle me-2"></i>칸 고정너비 설정이 저장되었습니다.<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                document.body.appendChild(alertDiv);
                setTimeout(() => { alertDiv.remove(); }, 3000);
            } else {
                alert('칸 고정너비 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('칸 고정너비 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
        });
    } catch (error) {
        console.error('Error in updateContainerFixedWidthColumns:', error);
        alert('칸 고정너비 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
    }
}

// 컨테이너 마진 모달 열기
function openContainerMarginModal(containerId) {
    document.getElementById('margin_modal_container_id').value = containerId;
    document.getElementById('margin_modal_is_new').value = 'false';
    
    // 현재 값 로드
    document.getElementById('margin_modal_top').value = document.getElementById(`container_margin_top_${containerId}`)?.value || 0;
    document.getElementById('margin_modal_bottom').value = document.getElementById(`container_margin_bottom_${containerId}`)?.value || 24;
    document.getElementById('margin_modal_left').value = document.getElementById(`container_margin_left_${containerId}`)?.value || 0;
    document.getElementById('margin_modal_right').value = document.getElementById(`container_margin_right_${containerId}`)?.value || 0;
    
    updateMarginPreview();
    
    const modal = new bootstrap.Modal(document.getElementById('containerMarginModal'));
    modal.show();
}

// 새 컨테이너용 마진 모달 열기
function openNewContainerMarginModal() {
    document.getElementById('margin_modal_container_id').value = '';
    document.getElementById('margin_modal_is_new').value = 'true';
    
    document.getElementById('margin_modal_top').value = document.getElementById('new_container_margin_top')?.value || 0;
    document.getElementById('margin_modal_bottom').value = document.getElementById('new_container_margin_bottom')?.value || 24;
    document.getElementById('margin_modal_left').value = document.getElementById('new_container_margin_left')?.value || 0;
    document.getElementById('margin_modal_right').value = document.getElementById('new_container_margin_right')?.value || 0;
    
    updateMarginPreview();
    
    const modal = new bootstrap.Modal(document.getElementById('containerMarginModal'));
    modal.show();
}

// 마진 미리보기 업데이트
function updateMarginPreview() {
    document.getElementById('margin_preview_top').textContent = document.getElementById('margin_modal_top').value + 'px';
    document.getElementById('margin_preview_bottom').textContent = document.getElementById('margin_modal_bottom').value + 'px';
    document.getElementById('margin_preview_left').textContent = document.getElementById('margin_modal_left').value + 'px';
    document.getElementById('margin_preview_right').textContent = document.getElementById('margin_modal_right').value + 'px';
}

// 컨테이너 마진 저장
function saveContainerMargin() {
    const isNew = document.getElementById('margin_modal_is_new').value === 'true';
    const containerId = document.getElementById('margin_modal_container_id').value;
    
    const marginTop = document.getElementById('margin_modal_top').value;
    const marginBottom = document.getElementById('margin_modal_bottom').value;
    const marginLeft = document.getElementById('margin_modal_left').value;
    const marginRight = document.getElementById('margin_modal_right').value;
    
    if (isNew) {
        // 새 컨테이너용 hidden input 업데이트
        document.getElementById('new_container_margin_top').value = marginTop;
        document.getElementById('new_container_margin_bottom').value = marginBottom;
        document.getElementById('new_container_margin_left').value = marginLeft;
        document.getElementById('new_container_margin_right').value = marginRight;
        
        bootstrap.Modal.getInstance(document.getElementById('containerMarginModal')).hide();
        
        // 성공 메시지
        showToast('마진 설정이 저장되었습니다. 컨테이너 추가 시 적용됩니다.');
    } else {
        // 기존 컨테이너 hidden input 업데이트
        document.getElementById(`container_margin_top_${containerId}`).value = marginTop;
        document.getElementById(`container_margin_bottom_${containerId}`).value = marginBottom;
        document.getElementById(`container_margin_left_${containerId}`).value = marginLeft;
        document.getElementById(`container_margin_right_${containerId}`).value = marginRight;
        
        // 서버에 저장
        updateContainerMarginAndPadding(containerId);
        
        bootstrap.Modal.getInstance(document.getElementById('containerMarginModal')).hide();
    }
}

// 컨테이너 패딩 모달 열기
function openContainerPaddingModal(containerId) {
    document.getElementById('padding_modal_container_id').value = containerId;
    document.getElementById('padding_modal_is_new').value = 'false';
    
    // 현재 값 로드
    document.getElementById('padding_modal_top').value = document.getElementById(`container_padding_top_${containerId}`)?.value || 0;
    document.getElementById('padding_modal_bottom').value = document.getElementById(`container_padding_bottom_${containerId}`)?.value || 0;
    document.getElementById('padding_modal_left').value = document.getElementById(`container_padding_left_${containerId}`)?.value || 0;
    document.getElementById('padding_modal_right').value = document.getElementById(`container_padding_right_${containerId}`)?.value || 0;
    
    updatePaddingPreview();
    
    const modal = new bootstrap.Modal(document.getElementById('containerPaddingModal'));
    modal.show();
}

// 새 컨테이너용 패딩 모달 열기
function openNewContainerPaddingModal() {
    document.getElementById('padding_modal_container_id').value = '';
    document.getElementById('padding_modal_is_new').value = 'true';
    
    document.getElementById('padding_modal_top').value = document.getElementById('new_container_padding_top')?.value || 0;
    document.getElementById('padding_modal_bottom').value = document.getElementById('new_container_padding_bottom')?.value || 0;
    document.getElementById('padding_modal_left').value = document.getElementById('new_container_padding_left')?.value || 0;
    document.getElementById('padding_modal_right').value = document.getElementById('new_container_padding_right')?.value || 0;
    
    updatePaddingPreview();
    
    const modal = new bootstrap.Modal(document.getElementById('containerPaddingModal'));
    modal.show();
}

// 패딩 미리보기 업데이트
function updatePaddingPreview() {
    document.getElementById('padding_preview_top').textContent = document.getElementById('padding_modal_top').value + 'px';
    document.getElementById('padding_preview_bottom').textContent = document.getElementById('padding_modal_bottom').value + 'px';
    document.getElementById('padding_preview_left').textContent = document.getElementById('padding_modal_left').value + 'px';
    document.getElementById('padding_preview_right').textContent = document.getElementById('padding_modal_right').value + 'px';
}

// 컨테이너 패딩 저장
function saveContainerPadding() {
    const isNew = document.getElementById('padding_modal_is_new').value === 'true';
    const containerId = document.getElementById('padding_modal_container_id').value;
    
    const paddingTop = document.getElementById('padding_modal_top').value;
    const paddingBottom = document.getElementById('padding_modal_bottom').value;
    const paddingLeft = document.getElementById('padding_modal_left').value;
    const paddingRight = document.getElementById('padding_modal_right').value;
    
    if (isNew) {
        // 새 컨테이너용 hidden input 업데이트
        document.getElementById('new_container_padding_top').value = paddingTop;
        document.getElementById('new_container_padding_bottom').value = paddingBottom;
        document.getElementById('new_container_padding_left').value = paddingLeft;
        document.getElementById('new_container_padding_right').value = paddingRight;
        
        bootstrap.Modal.getInstance(document.getElementById('containerPaddingModal')).hide();
        
        // 성공 메시지
        showToast('패딩 설정이 저장되었습니다. 컨테이너 추가 시 적용됩니다.');
    } else {
        // 기존 컨테이너 hidden input 업데이트
        document.getElementById(`container_padding_top_${containerId}`).value = paddingTop;
        document.getElementById(`container_padding_bottom_${containerId}`).value = paddingBottom;
        document.getElementById(`container_padding_left_${containerId}`).value = paddingLeft;
        document.getElementById(`container_padding_right_${containerId}`).value = paddingRight;
        
        // 서버에 저장
        updateContainerMarginAndPadding(containerId);
        
        bootstrap.Modal.getInstance(document.getElementById('containerPaddingModal')).hide();
    }
}

// 토스트 메시지 표시
function showToast(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        <i class="bi bi-check-circle me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// 컨테이너 마진/패딩 업데이트 (서버 저장)
function updateContainerMarginAndPadding(containerId) {
    try {
        const formData = new FormData();
        const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
        if (!containerItem) {
            console.error('Container item not found for ID:', containerId);
            alert('컨테이너를 찾을 수 없습니다.');
            return;
        }

        const columnsSelect = containerItem.querySelector('select[data-container-id="' + containerId + '"]');
        if (columnsSelect) formData.append('columns', columnsSelect.value);
        const allSelects = containerItem.querySelectorAll('select[data-container-id="' + containerId + '"]');
        if (allSelects.length >= 2) formData.append('vertical_align', allSelects[1].value);
        const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
        if (fullWidthCheckbox) formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
        const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
        if (fullHeightCheckbox) formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
        const fixedWidthColumnsCheckbox = document.getElementById(`container_fixed_width_columns_${containerId}`);
        if (fixedWidthColumnsCheckbox) formData.append('fixed_width_columns', fixedWidthColumnsCheckbox.checked ? '1' : '0');
        const widgetSpacingSelect = containerItem.querySelector('select[onchange*="updateContainerWidgetSpacing"]');
        if (widgetSpacingSelect) formData.append('widget_spacing', widgetSpacingSelect.value);

        // 마진 값
        formData.append('margin_top', document.getElementById(`container_margin_top_${containerId}`)?.value || 0);
        formData.append('margin_bottom', document.getElementById(`container_margin_bottom_${containerId}`)?.value || 24);
        formData.append('margin_left', document.getElementById(`container_margin_left_${containerId}`)?.value || 0);
        formData.append('margin_right', document.getElementById(`container_margin_right_${containerId}`)?.value || 0);
        
        // 패딩 값
        formData.append('padding_top', document.getElementById(`container_padding_top_${containerId}`)?.value || 0);
        formData.append('padding_bottom', document.getElementById(`container_padding_bottom_${containerId}`)?.value || 0);
        formData.append('padding_left', document.getElementById(`container_padding_left_${containerId}`)?.value || 0);
        formData.append('padding_right', document.getElementById(`container_padding_right_${containerId}`)?.value || 0);
        
        formData.append('_method', 'PUT');

        fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
                showToast('컨테이너 설정이 저장되었습니다.');
            } else {
                alert('컨테이너 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('컨테이너 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
        });
    } catch (error) {
        console.error('Error in updateContainerMarginAndPadding:', error);
        alert('컨테이너 설정 업데이트 중 오류가 발생했습니다: ' + error.message);
    }
}

// 컨테이너 상단/하단 마진 업데이트 (하위 호환성 유지)
function updateContainerMargin(containerId) {
    updateContainerMarginAndPadding(containerId);
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
        
        fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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

// 컨테이너 배경 색상 업데이트
function updateContainerBackgroundColor(containerId) {
    const colorInput = document.getElementById(`container_background_color_input_${containerId}`);
    const alphaInput = document.getElementById(`container_background_color_alpha_${containerId}`);
    const alphaValue = document.getElementById(`container_background_color_alpha_value_${containerId}`);
    const alphaHidden = document.getElementById(`container_background_color_alpha_hidden_${containerId}`);
    
    const color = colorInput.value;
    const alpha = alphaInput ? alphaInput.value : 100;
    
    if (alphaValue) alphaValue.textContent = alpha + '%';
    if (alphaHidden) alphaHidden.value = alpha;
    
    const formData = new FormData();
    formData.append('background_type', 'color');
    formData.append('background_color', color);
    formData.append('background_color_alpha', alpha);
    formData.append('_method', 'PUT');
    
    // 현재 컨테이너 설정 유지
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (containerItem) {
        const columnsSelect = containerItem.querySelector('select[onchange*="updateContainerColumns"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        }
        const verticalAlignSelect = containerItem.querySelector('select[onchange*="updateContainerVerticalAlign"]');
        if (verticalAlignSelect) {
            formData.append('vertical_align', verticalAlignSelect.value);
        }
        const widgetSpacingSelect = containerItem.querySelector('select[onchange*="updateContainerWidgetSpacing"]');
        if (widgetSpacingSelect) {
            formData.append('widget_spacing', widgetSpacingSelect.value);
        }
    }
    
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
            // 성공 알림은 표시하지 않고 조용히 업데이트
        } else {
            alert('배경 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('배경 설정 업데이트 중 오류가 발생했습니다.');
    });
}

// 컨테이너 앵커 ID 업데이트
function updateContainerAnchorId(containerId) {
    const anchorInput = document.getElementById(`container_anchor_id_${containerId}`);
    if (!anchorInput) return;
    
    const anchorId = anchorInput.value.trim();
    
    // 앵커 ID 유효성 검사 (영문 시작, 영문/숫자/하이픈/언더스코어만 허용)
    if (anchorId && !/^[a-zA-Z][a-zA-Z0-9_-]*$/.test(anchorId)) {
        alert('앵커 ID는 영문으로 시작하고 영문, 숫자, 하이픈(-), 언더스코어(_)만 사용할 수 있습니다.');
        return;
    }
    
    const formData = new FormData();
    formData.append('anchor_id', anchorId);
    formData.append('_method', 'PUT');
    
    // 현재 컨테이너 설정 유지
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (containerItem) {
        const columnsSelect = containerItem.querySelector('select[onchange*="updateContainerColumns"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        }
    }
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
            // 성공 알림 표시하지 않음
        } else {
            alert('앵커 ID 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('앵커 ID 업데이트 중 오류가 발생했습니다.');
    });
}

// 컨테이너 배경 이미지 업로드 처리
function handleContainerBackgroundImageUpload(containerId, fileInput) {
    if (!fileInput.files || !fileInput.files[0]) return;
    
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('background_type', 'image');
    formData.append('background_image_file', file);
    
    const alphaInput = document.getElementById(`container_background_image_alpha_${containerId}`);
    const alpha = alphaInput ? alphaInput.value : 100;
    formData.append('background_image_alpha', alpha);
    formData.append('_method', 'PUT');
    
    // 현재 컨테이너 설정 유지
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (containerItem) {
        const columnsSelect = containerItem.querySelector('select[onchange*="updateContainerColumns"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        }
        const verticalAlignSelect = containerItem.querySelector('select[onchange*="updateContainerVerticalAlign"]');
        if (verticalAlignSelect) {
            formData.append('vertical_align', verticalAlignSelect.value);
        }
        const widgetSpacingSelect = containerItem.querySelector('select[onchange*="updateContainerWidgetSpacing"]');
        if (widgetSpacingSelect) {
            formData.append('widget_spacing', widgetSpacingSelect.value);
        }
    }
    
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
            // 이미지 URL 업데이트
            const imageUrl = (data.container && data.container.background_image_url) || data.background_image_url;
            const imageUrlInput = document.getElementById(`container_background_image_url_${containerId}`);
            if (imageUrlInput && imageUrl) {
                imageUrlInput.value = imageUrl;
            }
            
            // 미리보기 업데이트
            const preview = document.getElementById(`container_background_image_preview_${containerId}`);
            const previewImg = document.getElementById(`container_background_image_preview_img_${containerId}`);
            if (imageUrl) {
                if (!preview) {
                    const imageContainer = document.getElementById(`container_background_image_${containerId}`);
                    if (imageContainer) {
                        const newPreview = document.createElement('div');
                        newPreview.id = `container_background_image_preview_${containerId}`;
                        newPreview.style.display = 'inline-block';
                        newPreview.innerHTML = `
                            <img id="container_background_image_preview_img_${containerId}" src="${imageUrl}" alt="미리보기" style="max-width: 60px; max-height: 60px; object-fit: cover; border-radius: 4px;">
                            <button type="button" 
                                    class="btn btn-sm btn-danger ms-1" 
                                    onclick="removeContainerBackgroundImage(${containerId})"
                                    title="이미지 제거">
                                <i class="bi bi-x"></i>
                            </button>
                        `;
                        imageContainer.appendChild(newPreview);
                    }
                } else {
                    if (previewImg) {
                        previewImg.src = imageUrl;
                    }
                    preview.style.display = 'inline-block';
                }
            }
        } else {
            alert('이미지 업로드에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('이미지 업로드 중 오류가 발생했습니다.');
    });
}

// 컨테이너 배경 이미지 투명도 업데이트
function updateContainerBackgroundImageAlpha(containerId, alpha) {
    const alphaValueDisplay = document.getElementById(`container_background_image_alpha_value_${containerId}`);
    if (alphaValueDisplay) {
        alphaValueDisplay.textContent = alpha + '%';
    }
    
    const formData = new FormData();
    formData.append('background_type', 'image');
    formData.append('background_image_alpha', alpha);
    formData.append('_method', 'PUT');
    
    // 현재 컨테이너 설정 유지
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (containerItem) {
        const columnsSelect = containerItem.querySelector('select[onchange*="updateContainerColumns"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        }
        const verticalAlignSelect = containerItem.querySelector('select[onchange*="updateContainerVerticalAlign"]');
        if (verticalAlignSelect) {
            formData.append('vertical_align', verticalAlignSelect.value);
        }
        const widgetSpacingSelect = containerItem.querySelector('select[onchange*="updateContainerWidgetSpacing"]');
        if (widgetSpacingSelect) {
            formData.append('widget_spacing', widgetSpacingSelect.value);
        }
    }
    
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    
    const imageUrlInput = document.getElementById(`container_background_image_url_${containerId}`);
    if (imageUrlInput && imageUrlInput.value) {
        formData.append('background_image_url', imageUrlInput.value);
    }
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('투명도 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('투명도 설정 업데이트 중 오류가 발생했습니다.');
    });
}

// 컨테이너 배경 이미지 패럴랙스 업데이트
function updateContainerBackgroundParallax(containerId, parallaxEnabled) {
    const formData = new FormData();
    formData.append('background_parallax', parallaxEnabled ? '1' : '0');
    formData.append('_method', 'PUT');
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('패럴랙스 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('패럴랙스 설정 업데이트 중 오류가 발생했습니다.');
    });
}

// 컨테이너 배경 이미지 제거
function removeContainerBackgroundImage(containerId) {
    const formData = new FormData();
    formData.append('background_type', 'none');
    formData.append('background_image_url', '');
    formData.append('_method', 'PUT');
    
    // 현재 컨테이너 설정 유지
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (containerItem) {
        const columnsSelect = containerItem.querySelector('select[onchange*="updateContainerColumns"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        }
        const verticalAlignSelect = containerItem.querySelector('select[onchange*="updateContainerVerticalAlign"]');
        if (verticalAlignSelect) {
            formData.append('vertical_align', verticalAlignSelect.value);
        }
        const widgetSpacingSelect = containerItem.querySelector('select[onchange*="updateContainerWidgetSpacing"]');
        if (widgetSpacingSelect) {
            formData.append('widget_spacing', widgetSpacingSelect.value);
        }
    }
    
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
            // 이미지 URL 초기화
            const imageUrlInput = document.getElementById(`container_background_image_url_${containerId}`);
            if (imageUrlInput) {
                imageUrlInput.value = '';
            }
            
            // 미리보기 제거
            const previewDiv = document.getElementById(`container_background_image_preview_${containerId}`);
            if (previewDiv) {
                previewDiv.remove();
            }
            
            // 배경 타입을 'none'으로 변경
            const backgroundTypeSelect = document.getElementById(`container_background_type_${containerId}`);
            if (backgroundTypeSelect) {
                backgroundTypeSelect.value = 'none';
                handleContainerBackgroundTypeChange(containerId, 'none', 'desktop');
            }
        } else {
            alert('이미지 제거에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('이미지 제거 중 오류가 발생했습니다.');
    });
}

// 컨테이너 그라데이션 배경 업데이트
function updateContainerBackgroundGradient(containerId) {
    // hidden input에서 값 가져오기
    const startInput = document.getElementById(`container_background_gradient_start_${containerId}`);
    const endInput = document.getElementById(`container_background_gradient_end_${containerId}`);
    const angleInput = document.getElementById(`container_background_gradient_angle_${containerId}`);
    const startInputMobile = document.getElementById(`container_background_gradient_start_mobile_${containerId}`);
    const endInputMobile = document.getElementById(`container_background_gradient_end_mobile_${containerId}`);
    const angleInputMobile = document.getElementById(`container_background_gradient_angle_mobile_${containerId}`);
    
    const startColor = startInput?.value || startInputMobile?.value || '#ffffff';
    const endColor = endInput?.value || endInputMobile?.value || '#000000';
    const angle = angleInput?.value || angleInputMobile?.value || 90;
    
    const formData = new FormData();
    formData.append('background_type', 'gradient');
    formData.append('background_gradient_start', startColor);
    formData.append('background_gradient_end', endColor);
    formData.append('background_gradient_angle', angle);
    formData.append('_method', 'PUT');
    
    // 현재 컨테이너 설정 유지
    const containerItem = document.querySelector(`.container-item[data-container-id="${containerId}"]`);
    if (containerItem) {
        const columnsSelect = containerItem.querySelector('select[onchange*="updateContainerColumns"]');
        if (columnsSelect) {
            formData.append('columns', columnsSelect.value);
        }
        const verticalAlignSelect = containerItem.querySelector('select[onchange*="updateContainerVerticalAlign"]');
        if (verticalAlignSelect) {
            formData.append('vertical_align', verticalAlignSelect.value);
        }
    }
    
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
            // 성공 알림은 표시하지 않고 조용히 업데이트
        } else {
            alert('배경 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('배경 설정 업데이트 중 오류가 발생했습니다.');
    });
}

// 컨테이너 가로 100% 업데이트
function updateContainerFullWidth(containerId, fullWidth) {
    const formData = new FormData();
    formData.append('columns', document.querySelector(`select[data-container-id="${containerId}"][onchange*="updateContainerColumns"]`).value);
    formData.append('vertical_align', document.querySelector(`select[data-container-id="${containerId}"][onchange*="updateContainerVerticalAlign"]`).value);
    formData.append('full_width', fullWidth ? '1' : '0');
    // 현재 full_height 상태 유지
    const fullHeightCheckbox = document.getElementById(`container_full_height_${containerId}`);
    if (fullHeightCheckbox) {
        formData.append('full_height', fullHeightCheckbox.checked ? '1' : '0');
    }
    formData.append('_method', 'PUT');
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
            // 성공 시 페이지 새로고침
            location.reload();
        } else {
            alert('가로 100% 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('가로 100% 설정 업데이트 중 오류가 발생했습니다.');
    });
}

// 컨테이너 세로 100% 업데이트
function updateContainerFullHeight(containerId, fullHeight) {
    const formData = new FormData();
    formData.append('columns', document.querySelector(`select[data-container-id="${containerId}"][onchange*="updateContainerColumns"]`).value);
    formData.append('vertical_align', document.querySelector(`select[data-container-id="${containerId}"][onchange*="updateContainerVerticalAlign"]`).value);
    formData.append('full_height', fullHeight ? '1' : '0');
    // 현재 full_width 상태 유지
    const fullWidthCheckbox = document.getElementById(`container_full_width_${containerId}`);
    if (fullWidthCheckbox) {
        formData.append('full_width', fullWidthCheckbox.checked ? '1' : '0');
    }
    formData.append('_method', 'PUT');
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
            // 성공 시 페이지 새로고침
            location.reload();
        } else {
            alert('세로 100% 설정 업데이트에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('세로 100% 설정 업데이트 중 오류가 발생했습니다.');
    });
}

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
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
    
    fetch('{{ route("admin.custom-pages.containers.update", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
    
    fetch('{{ route("admin.custom-pages.containers.reorder", ["site" => $site->slug, "customPage" => $customPage->id]) }}', {
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
    
    fetch('{{ route("admin.custom-pages.containers.delete", ["site" => $site->slug, "customPage" => $customPage->id, "container" => ":containerId"]) }}'.replace(':containerId', containerId), {
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
async function addCustomPageWidget() {
    const form = document.getElementById('addWidgetForm');
    const addButton = form.querySelector('button[onclick="addCustomPageWidget()"]');
    
    // 로딩 상태 표시
    if (addButton) {
        addButton.disabled = true;
        addButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>업로드 중...';
    }
    
    try {
        const formData = new FormData(form);
        
        // 이미지 파일 크기 체크 (최대 50MB)
        const maxFileSize = 50 * 1024 * 1024; // 50MB
        const fileInputs = form.querySelectorAll('input[type="file"]');
        for (const fileInput of fileInputs) {
            if (fileInput.files && fileInput.files.length > 0) {
                for (const file of fileInput.files) {
                    if (file.size > maxFileSize) {
                        const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                        alert('이미지 파일 크기가 너무 큽니다.\n\n파일명: ' + file.name + '\n파일 크기: ' + fileSizeMB + 'MB\n최대 허용 크기: 50MB\n\n이미지 파일 크기를 줄여서 다시 시도해주세요.');
                        if (addButton) {
                            addButton.disabled = false;
                            addButton.innerHTML = '<i class="bi bi-plus-circle me-2"></i>추가';
                        }
                        return;
                    }
                }
            }
        }
        
        // container_id와 column_index 확인 및 추가
    const containerId = document.getElementById('widget_container_id')?.value;
    const columnIndex = document.getElementById('widget_column_index')?.value;
    
    if (!containerId || !columnIndex) {
        alert('컨테이너 정보가 없습니다. 위젯 추가 폼을 다시 열어주세요.');
        return;
    }
    
    // FormData에 명시적으로 추가 (이미 form에 포함되어 있지만 확실하게)
    formData.set('container_id', containerId);
    formData.set('column_index', columnIndex);
    
    // 위젯 타입 확인
    const widgetType = formData.get('type');
    if (!widgetType || widgetType === '') {
        alert('위젯 종류를 선택해주세요.');
        return;
    }
    
    // settings 객체 생성 (사이드 위젯 페이지의 addWidget 함수와 동일한 로직)
    const settings = {};
    
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
    } else if (widgetType === 'board_viewer') {
        const boardId = formData.get('board_id');
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        const noBackground = document.getElementById('widget_board_viewer_no_background')?.checked;
        settings.no_background = noBackground || false;
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
        const toggleMenuSelect = document.getElementById('widget_toggle_menu_id') || document.getElementById('edit_custom_page_widget_toggle_menu_id');
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
    } else if (widgetType === 'block') {
        const blockTitle = formData.get('block_title');
        const blockContent = formData.get('block_content');
        const textAlign = formData.get('block_text_align') || 'left';
        const backgroundType = formData.get('block_background_type') || 'color';
        const paddingTop = formData.get('block_padding_top');
        const paddingBottom = formData.get('block_padding_bottom');
        const paddingLeft = formData.get('block_padding_left');
        const paddingRight = formData.get('block_padding_right');
        const titleContentGap = formData.get('block_title_content_gap');
        const buttonTopMargin = formData.get('block_button_top_margin');
        const blockLink = formData.get('block_link');
        const openNewTab = document.getElementById('widget_block_open_new_tab')?.checked || false;
        const titleColor = formData.get('block_title_color') || '#ffffff';
        const contentColor = formData.get('block_content_color') || '#ffffff';
        const titleFontSize = formData.get('block_title_font_size') || '16';
        const contentFontSize = formData.get('block_content_font_size') || '14';
        
        // 블록 이미지 데이터 수집
        const enableImage = document.getElementById('widget_block_enable_image')?.checked || false;
        const blockImageFile = document.getElementById('widget_block_image')?.files[0];
        const blockImageUrl = document.getElementById('widget_block_image_url')?.value || '';
        if (enableImage) {
            settings.enable_image = true;
            if (blockImageFile) {
                formData.append('block_image_file', blockImageFile);
            }
            if (blockImageUrl) {
                settings.block_image_url = blockImageUrl;
            }
            // 이미지 패딩 데이터 수집
            const blockImagePaddingTop = document.getElementById('widget_block_image_padding_top')?.value || 0;
            const blockImagePaddingBottom = document.getElementById('widget_block_image_padding_bottom')?.value || 0;
            const blockImagePaddingLeft = document.getElementById('widget_block_image_padding_left')?.value || 0;
            const blockImagePaddingRight = document.getElementById('widget_block_image_padding_right')?.value || 0;
            settings.block_image_padding_top = blockImagePaddingTop !== '' && blockImagePaddingTop !== null ? parseInt(blockImagePaddingTop) : 0;
            settings.block_image_padding_bottom = blockImagePaddingBottom !== '' && blockImagePaddingBottom !== null ? parseInt(blockImagePaddingBottom) : 0;
            settings.block_image_padding_left = blockImagePaddingLeft !== '' && blockImagePaddingLeft !== null ? parseInt(blockImagePaddingLeft) : 0;
            settings.block_image_padding_right = blockImagePaddingRight !== '' && blockImagePaddingRight !== null ? parseInt(blockImagePaddingRight) : 0;
        }
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
                const buttonBorderColor = buttonCard.querySelector('.block-button-border-color')?.value || buttonBackgroundColor;
                const buttonBorderWidth = buttonCard.querySelector('.block-button-border-width')?.value || '2';
                const buttonHoverBackgroundColor = buttonCard.querySelector('.block-button-hover-background-color')?.value || '#0056b3';
                const buttonHoverTextColor = buttonCard.querySelector('.block-button-hover-text-color')?.value || '#ffffff';
                const buttonHoverBorderColor = buttonCard.querySelector('.block-button-hover-border-color')?.value || '#0056b3';
                
                // 배경 타입 및 그라데이션 설정
                const buttonBackgroundType = buttonCard.querySelector('.block-button-background-type')?.value || 'color';
                const buttonGradientStart = buttonCard.querySelector('.block-button-gradient-start')?.value || buttonBackgroundColor;
                const buttonGradientEnd = buttonCard.querySelector('.block-button-gradient-end')?.value || buttonHoverBackgroundColor;
                const buttonGradientAngle = buttonCard.querySelector('.block-button-gradient-angle')?.value || '90';
                const buttonOpacityRaw = buttonCard.querySelector('.block-button-opacity')?.value || '100';
                const buttonOpacity = parseFloat(buttonOpacityRaw) / 100;
                
                // 호버 배경 타입 및 그라데이션 설정
                const buttonHoverBackgroundType = buttonCard.querySelector('.block-button-hover-background-type')?.value || 'color';
                const buttonHoverGradientStart = buttonCard.querySelector('.block-button-hover-gradient-start')?.value || buttonHoverBackgroundColor;
                const buttonHoverGradientEnd = buttonCard.querySelector('.block-button-hover-gradient-end')?.value || buttonHoverBackgroundColor;
                const buttonHoverGradientAngle = buttonCard.querySelector('.block-button-hover-gradient-angle')?.value || '90';
                const buttonHoverOpacityRaw = buttonCard.querySelector('.block-button-hover-opacity')?.value || '100';
                const buttonHoverOpacity = parseFloat(buttonHoverOpacityRaw) / 100;
                
                buttons.push({
                    text: buttonText,
                    link: buttonLink,
                    open_new_tab: buttonOpenNewTab,
                    background_color: buttonBackgroundColor,
                    text_color: buttonTextColor,
                    border_color: buttonBorderColor,
                    border_width: buttonBorderWidth,
                    hover_background_color: buttonHoverBackgroundColor,
                    hover_text_color: buttonHoverTextColor,
                    hover_border_color: buttonHoverBorderColor,
                    background_type: buttonBackgroundType,
                    background_gradient_start: buttonGradientStart,
                    background_gradient_end: buttonGradientEnd,
                    background_gradient_angle: parseInt(buttonGradientAngle) || 90,
                    opacity: buttonOpacity,
                    hover_background_type: buttonHoverBackgroundType,
                    hover_background_gradient_start: buttonHoverGradientStart,
                    hover_background_gradient_end: buttonHoverGradientEnd,
                    hover_background_gradient_angle: parseInt(buttonHoverGradientAngle) || 90,
                    hover_opacity: buttonHoverOpacity
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
        settings.title_color = titleColor;
        settings.content_color = contentColor;
        settings.title_font_size = titleFontSize;
        settings.content_font_size = contentFontSize;
        settings.buttons = buttons;
        settings.title_content_gap = titleContentGap !== '' && titleContentGap !== null ? parseInt(titleContentGap) : 8;
        settings.button_top_margin = buttonTopMargin !== '' && buttonTopMargin !== null ? parseInt(buttonTopMargin) : 12;
        
        if (backgroundType === 'color') {
            const backgroundColor = formData.get('block_background_color') || '#007bff';
            settings.background_color = backgroundColor;
        } else if (backgroundType === 'gradient') {
            const gradientStart = formData.get('block_background_gradient_start') || '#ffffff';
            const gradientEnd = formData.get('block_background_gradient_end') || '#000000';
            const gradientAngle = formData.get('block_background_gradient_angle') || 90;
            settings.background_gradient_start = gradientStart;
            settings.background_gradient_end = gradientEnd;
            settings.background_gradient_angle = parseInt(gradientAngle) || 90;
        } else if (backgroundType === 'image') {
            const imageFile = document.getElementById('widget_block_image_input')?.files[0];
            if (imageFile) {
                formData.append('block_background_image_file', imageFile);
            }
            const imageUrl = formData.get('block_background_image_url');
            if (imageUrl) {
                settings.background_image_url = imageUrl;
            }
            const imageAlphaValue = document.getElementById('widget_block_background_image_alpha')?.value;
            const imageAlpha = imageAlphaValue !== '' && imageAlphaValue !== null ? parseInt(imageAlphaValue) : 100;
            settings.background_image_alpha = imageAlpha;
            const imageFullWidth = document.getElementById('widget_block_background_image_full_width')?.checked;
            settings.background_image_full_width = imageFullWidth || false;
        }
        
        settings.padding_top = paddingTop !== '' && paddingTop !== null ? parseInt(paddingTop) : 20;
        settings.padding_bottom = paddingBottom !== '' && paddingBottom !== null ? parseInt(paddingBottom) : 20;
        settings.padding_left = paddingLeft !== '' && paddingLeft !== null ? parseInt(paddingLeft) : 20;
        settings.padding_right = paddingRight !== '' && paddingRight !== null ? parseInt(paddingRight) : 20;
        
        if (blockLink) {
            settings.link = blockLink;
        }
        settings.open_new_tab = openNewTab;
    } else if (widgetType === 'block_slide') {
        const slideDirection = document.querySelector('input[name="block_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        // 슬라이드 유지 시간
        const slideHoldTime = parseFloat(document.getElementById('block_slide_hold_time')?.value) || 3;
        settings.slide_hold_time = slideHoldTime;
        
        const blockItems = [];
        const itemsContainer = document.getElementById('widget_block_slide_items');
        if (!itemsContainer) {
            alert('블록 슬라이드 위젯에는 최소 1개 이상의 블록이 필요합니다. "블록 추가하기" 버튼을 클릭하여 블록을 추가해주세요.');
            return;
        }
        const blockSlideItems = itemsContainer.querySelectorAll('.block-slide-item');
        if (blockSlideItems.length === 0) {
            alert('블록 슬라이드 위젯에는 최소 1개 이상의 블록이 필요합니다. "블록 추가하기" 버튼을 클릭하여 블록을 추가해주세요.');
            return;
        }
        blockSlideItems.forEach((item, index) => {
            const itemIndex = item.dataset.itemIndex;
            const title = item.querySelector('.block-slide-title')?.value || '';
            const content = item.querySelector('.block-slide-content')?.value || '';
            const textAlignRadio = item.querySelector(`input[name="block_slide[${itemIndex}][text_align]"]:checked`);
            const textAlign = textAlignRadio ? textAlignRadio.value : 'left';
            const backgroundType = item.querySelector('.block-slide-background-type')?.value || 'color';
            const paddingTopVal = item.querySelector('.block-slide-padding-top')?.value;
            const paddingTop = paddingTopVal !== '' && paddingTopVal !== null && paddingTopVal !== undefined ? paddingTopVal : '20';
            const paddingBottomVal = item.querySelector('.block-slide-padding-bottom')?.value;
            const paddingBottom = paddingBottomVal !== '' && paddingBottomVal !== null && paddingBottomVal !== undefined ? paddingBottomVal : '20';
            const paddingLeftVal = item.querySelector('.block-slide-padding-left')?.value;
            const paddingLeft = paddingLeftVal !== '' && paddingLeftVal !== null && paddingLeftVal !== undefined ? paddingLeftVal : '20';
            const paddingRightVal = item.querySelector('.block-slide-padding-right')?.value;
            const paddingRight = paddingRightVal !== '' && paddingRightVal !== null && paddingRightVal !== undefined ? paddingRightVal : '20';
            const titleContentGapVal = item.querySelector('.block-slide-title-content-gap')?.value;
            const titleContentGap = titleContentGapVal !== '' && titleContentGapVal !== null && titleContentGapVal !== undefined ? titleContentGapVal : '8';
            const buttonTopMarginVal = item.querySelector('.block-slide-button-top-margin')?.value;
            const buttonTopMargin = buttonTopMarginVal !== '' && buttonTopMarginVal !== null && buttonTopMarginVal !== undefined ? buttonTopMarginVal : '12';
            const link = item.querySelector('.block-slide-link')?.value || '';
            const openNewTab = item.querySelector('.block-slide-open-new-tab')?.checked || false;
            const titleColor = item.querySelector('.block-slide-title-color')?.value || '#ffffff';
            const contentColor = item.querySelector('.block-slide-content-color')?.value || '#ffffff';
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
                    const buttonBorderColor = buttonCard.querySelector('.block-slide-button-border-color')?.value || buttonBackgroundColor;
                    const buttonBorderWidth = buttonCard.querySelector('.block-slide-button-border-width')?.value || '2';
                    const buttonHoverBackgroundColor = buttonCard.querySelector('.block-slide-button-hover-background-color')?.value || '#0056b3';
                    const buttonHoverTextColor = buttonCard.querySelector('.block-slide-button-hover-text-color')?.value || '#ffffff';
                    const buttonHoverBorderColor = buttonCard.querySelector('.block-slide-button-hover-border-color')?.value || '#0056b3';
                    
                    // 배경 타입 및 그라데이션 설정
                    const buttonBackgroundType = buttonCard.querySelector('.block-slide-button-background-type')?.value || 'color';
                    const buttonGradientStart = buttonCard.querySelector('.block-slide-button-gradient-start')?.value || buttonBackgroundColor;
                    const buttonGradientEnd = buttonCard.querySelector('.block-slide-button-gradient-end')?.value || buttonHoverBackgroundColor;
                    const buttonGradientAngle = buttonCard.querySelector('.block-slide-button-gradient-angle')?.value || '90';
                    const buttonOpacityRaw = buttonCard.querySelector('.block-slide-button-opacity')?.value || '100';
                    const buttonOpacity = parseFloat(buttonOpacityRaw) / 100;
                    
                    // 호버 배경 타입 및 그라데이션 설정
                    const buttonHoverBackgroundType = buttonCard.querySelector('.block-slide-button-hover-background-type')?.value || 'color';
                    const buttonHoverGradientStart = buttonCard.querySelector('.block-slide-button-hover-gradient-start')?.value || buttonHoverBackgroundColor;
                    const buttonHoverGradientEnd = buttonCard.querySelector('.block-slide-button-hover-gradient-end')?.value || buttonHoverBackgroundColor;
                    const buttonHoverGradientAngle = buttonCard.querySelector('.block-slide-button-hover-gradient-angle')?.value || '90';
                    const buttonHoverOpacityRaw = buttonCard.querySelector('.block-slide-button-hover-opacity')?.value || '100';
                    const buttonHoverOpacity = parseFloat(buttonHoverOpacityRaw) / 100;
                    
                    buttons.push({
                        text: buttonText,
                        link: buttonLink,
                        open_new_tab: buttonOpenNewTab,
                        background_color: buttonBackgroundColor,
                        text_color: buttonTextColor,
                        border_color: buttonBorderColor,
                        border_width: buttonBorderWidth,
                        hover_background_color: buttonHoverBackgroundColor,
                        hover_text_color: buttonHoverTextColor,
                        hover_border_color: buttonHoverBorderColor,
                        background_type: buttonBackgroundType,
                        background_gradient_start: buttonGradientStart,
                        background_gradient_end: buttonGradientEnd,
                        background_gradient_angle: parseInt(buttonGradientAngle) || 90,
                        opacity: buttonOpacity,
                        hover_background_type: buttonHoverBackgroundType,
                        hover_background_gradient_start: buttonHoverGradientStart,
                        hover_background_gradient_end: buttonHoverGradientEnd,
                        hover_background_gradient_angle: parseInt(buttonHoverGradientAngle) || 90,
                        hover_opacity: buttonHoverOpacity
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
                button_top_margin: parseInt(buttonTopMargin),
                link: link,
                open_new_tab: openNewTab,
                title_color: titleColor,
                content_color: contentColor,
                title_font_size: titleFontSize,
                content_font_size: contentFontSize,
                buttons: buttons
            };
            
            if (backgroundType === 'color') {
                const backgroundColor = item.querySelector('.block-slide-background-color')?.value || '#007bff';
                blockItem.background_color = backgroundColor;
            } else if (backgroundType === 'gradient') {
                const gradientStartInput = item.querySelector('.block-slide-gradient-start') || item.querySelector(`#block_slide_${itemIndex}_background_gradient_start`);
                const gradientEndInput = item.querySelector('.block-slide-gradient-end') || item.querySelector(`#block_slide_${itemIndex}_background_gradient_end`);
                const gradientAngleInput = item.querySelector('.block-slide-gradient-angle') || item.querySelector(`#block_slide_${itemIndex}_background_gradient_angle`);
                const gradientStart = gradientStartInput?.value || '#ffffff';
                const gradientEnd = gradientEndInput?.value || '#000000';
                const gradientAngle = gradientAngleInput?.value || '90';
                blockItem.background_gradient_start = gradientStart;
                blockItem.background_gradient_end = gradientEnd;
                blockItem.background_gradient_angle = parseInt(gradientAngle) || 90;
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
        // 이미지 width
        const imageWidth = document.getElementById('widget_image_width')?.value || '100';
        settings.image_width = parseInt(imageWidth) || 100;
        
        const link = document.getElementById('widget_image_link')?.value;
        if (link) {
            settings.link = link;
        }
        settings.open_new_tab = document.getElementById('widget_image_open_new_tab')?.checked || false;
        
        // 텍스트 오버레이 관련 데이터
        const textOverlay = document.getElementById('widget_image_text_overlay')?.checked || false;
        const title = document.getElementById('widget_image_title')?.value || '';
        const titleFontSize = document.getElementById('widget_image_title_font_size')?.value || '24';
        const content = document.getElementById('widget_image_content')?.value || '';
        const contentFontSize = document.getElementById('widget_image_content_font_size')?.value || '16';
        const titleContentGap = document.getElementById('widget_image_title_content_gap')?.value || '10';
        const textPaddingLeft = document.getElementById('widget_image_text_padding_left')?.value || '0';
        const textPaddingRight = document.getElementById('widget_image_text_padding_right')?.value || '0';
        const textPaddingTop = document.getElementById('widget_image_text_padding_top')?.value || '0';
        const textPaddingBottom = document.getElementById('widget_image_text_padding_bottom')?.value || '10';
        const alignH = document.querySelector('input[name="widget_image_align_h"]:checked')?.value || 'left';
        const alignV = document.querySelector('input[name="widget_image_align_v"]:checked')?.value || 'middle';
        const textColor = document.getElementById('widget_image_text_color')?.value || '#ffffff';
        const hasButton = document.getElementById('widget_image_has_button')?.checked || false;
        const buttonText = document.getElementById('widget_image_button_text')?.value || '';
        const buttonLink = document.getElementById('widget_image_button_link')?.value || '';
        const buttonNewTab = document.getElementById('widget_image_button_new_tab')?.checked || false;
        const buttonColor = document.getElementById('widget_image_button_color')?.value || '#0d6efd';
        const buttonTextColor = document.getElementById('widget_image_button_text_color')?.value || '#ffffff';
        const buttonBorderColor = document.getElementById('widget_image_button_border_color')?.value || '#0d6efd';
        const buttonOpacity = document.getElementById('widget_image_button_opacity')?.value ?? 100;
        const buttonHoverBgColor = document.getElementById('widget_image_button_hover_bg_color')?.value || '#0b5ed7';
        const buttonHoverTextColor = document.getElementById('widget_image_button_hover_text_color')?.value || '#ffffff';
        const buttonHoverBorderColor = document.getElementById('widget_image_button_hover_border_color')?.value || '#0a58ca';
        
        settings.text_overlay = textOverlay;
        settings.title = title;
        settings.title_font_size = parseInt(titleFontSize) || 24;
        settings.content = content;
        settings.content_font_size = parseInt(contentFontSize) || 16;
        settings.title_content_gap = parseInt(titleContentGap) || 10;
        settings.text_padding_left = parseInt(textPaddingLeft) || 0;
        settings.text_padding_right = parseInt(textPaddingRight) || 0;
        settings.text_padding_top = parseInt(textPaddingTop) || 0;
        settings.text_padding_bottom = parseInt(textPaddingBottom) || 10;
        settings.align_h = alignH;
        settings.align_v = alignV;
        settings.text_color = textColor;
        settings.has_button = hasButton;
        settings.button_text = buttonText;
        settings.button_link = buttonLink;
        settings.button_new_tab = buttonNewTab;
        settings.button_color = buttonColor;
        settings.button_text_color = buttonTextColor;
        settings.button_border_color = buttonBorderColor;
        settings.button_opacity = (buttonOpacity !== '' && buttonOpacity !== null && buttonOpacity !== undefined) ? parseInt(buttonOpacity) : 100;
        settings.button_hover_bg_color = buttonHoverBgColor;
        settings.button_hover_text_color = buttonHoverTextColor;
        settings.button_hover_border_color = buttonHoverBorderColor;
    } else if (widgetType === 'image_slide') {
        const slideDirection = document.querySelector('input[name="image_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        const imageItems = [];
        const imageSlideItems = document.querySelectorAll('.image-slide-item');
        imageSlideItems.forEach((item, index) => {
            const itemIndex = item.dataset.itemIndex;
            const imageFile = item.querySelector(`#image_slide_${itemIndex}_image_input`)?.files[0];
            const imageUrl = item.querySelector(`#image_slide_${itemIndex}_image_url`)?.value;
            const link = item.querySelector('.image-slide-link')?.value || '';
            const openNewTab = item.querySelector('.image-slide-open-new-tab')?.checked || false;
            
            // 텍스트 오버레이 관련 데이터
            const textOverlay = item.querySelector('.image-slide-text-overlay')?.checked || false;
            const title = item.querySelector('.image-slide-title')?.value || '';
            const titleFontSize = item.querySelector('.image-slide-title-font-size')?.value || '24';
            const content = item.querySelector('.image-slide-content')?.value || '';
            const contentFontSize = item.querySelector('.image-slide-content-font-size')?.value || '16';
            const titleContentGap = item.querySelector('.image-slide-title-content-gap')?.value || '10';
            const textPaddingLeft = item.querySelector('.image-slide-text-padding-left')?.value || '0';
            const textPaddingRight = item.querySelector('.image-slide-text-padding-right')?.value || '0';
            const textPaddingTop = item.querySelector('.image-slide-text-padding-top')?.value || '0';
            const textPaddingBottom = item.querySelector('.image-slide-text-padding-bottom')?.value || '0';
            const alignH = item.querySelector('.image-slide-align-h:checked')?.value || 'left';
            const alignV = item.querySelector('.image-slide-align-v:checked')?.value || 'middle';
            const textColor = item.querySelector('.image-slide-text-color')?.value || '#ffffff';
            
            // 버튼 관련 데이터
            const hasButton = item.querySelector('.image-slide-has-button')?.checked || false;
            const buttonText = item.querySelector('.image-slide-button-text')?.value || '';
            const buttonLink = item.querySelector('.image-slide-button-link')?.value || '';
            const buttonNewTab = item.querySelector('.image-slide-button-new-tab')?.checked || false;
            const buttonColor = item.querySelector('.image-slide-button-color')?.value || '#0d6efd';
            const buttonTextColor = item.querySelector('.image-slide-button-text-color')?.value || '#ffffff';
            const buttonBorderColor = item.querySelector('.image-slide-button-border-color')?.value || '#0d6efd';
            const buttonOpacityInput = item.querySelector('.image-slide-button-opacity');
            const buttonOpacity = (buttonOpacityInput && buttonOpacityInput.value !== '' && buttonOpacityInput.value !== null && buttonOpacityInput.value !== undefined) ? buttonOpacityInput.value : '100';
            const buttonHoverBgColor = item.querySelector('.image-slide-button-hover-bg-color')?.value || '#0b5ed7';
            const buttonHoverTextColor = item.querySelector('.image-slide-button-hover-text-color')?.value || '#ffffff';
            const buttonHoverBorderColor = item.querySelector('.image-slide-button-hover-border-color')?.value || '#0a58ca';
            
            const imageItem = {
                link: link,
                open_new_tab: openNewTab,
                text_overlay: textOverlay,
                title: title,
                title_font_size: parseInt(titleFontSize) || 24,
                content: content,
                content_font_size: parseInt(contentFontSize) || 16,
                title_content_gap: parseInt(titleContentGap) || 10,
                text_padding_left: parseInt(textPaddingLeft) || 0,
                text_padding_right: parseInt(textPaddingRight) || 0,
                text_padding_top: parseInt(textPaddingTop) || 0,
                text_padding_bottom: parseInt(textPaddingBottom) || 0,
                align_h: alignH,
                align_v: alignV,
                text_color: textColor,
                has_button: hasButton,
                button_text: buttonText,
                button_link: buttonLink,
                button_new_tab: buttonNewTab,
                button_color: buttonColor,
                button_text_color: buttonTextColor,
                button_border_color: buttonBorderColor,
                button_opacity: (buttonOpacity !== '' && buttonOpacity !== null && buttonOpacity !== undefined) ? parseInt(buttonOpacity) : 100,
                button_hover_bg_color: buttonHoverBgColor,
                button_hover_text_color: buttonHoverTextColor,
                button_hover_border_color: buttonHoverBorderColor
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
        const countdownTitle = formData.get('countdown_title') || '';
        const countdownContent = formData.get('countdown_content') || '';
        const countdownType = formData.get('countdown_type') || 'dday';
        
        settings.countdown_title = countdownTitle;
        settings.countdown_content = countdownContent;
        settings.countdown_type = countdownType;
        
        // 배경 설정
        const backgroundType = document.getElementById('widget_countdown_background_type')?.value || 'none';
        settings.background_type = backgroundType;
        
        if (backgroundType === 'color') {
            const backgroundColor = document.getElementById('widget_countdown_background_color')?.value || '#007bff';
            const backgroundOpacity = document.getElementById('widget_countdown_background_opacity')?.value || '100';
            settings.background_color = backgroundColor;
            settings.background_opacity = parseInt(backgroundOpacity) || 100;
        } else if (backgroundType === 'gradient') {
            const gradientStart = document.getElementById('widget_countdown_background_gradient_start')?.value || '#ffffff';
            const gradientEnd = document.getElementById('widget_countdown_background_gradient_end')?.value || '#000000';
            const gradientAngle = document.getElementById('widget_countdown_background_gradient_angle')?.value || '90';
            const gradientOpacity = document.getElementById('widget_countdown_gradient_opacity')?.value || '100';
            settings.background_gradient_start = gradientStart;
            settings.background_gradient_end = gradientEnd;
            settings.background_gradient_angle = parseInt(gradientAngle) || 90;
            settings.background_gradient_opacity = parseInt(gradientOpacity) || 100;
        }
        
        // 폰트 색상
        const fontColor = document.getElementById('widget_countdown_font_color')?.value || '#333333';
        settings.font_color = fontColor;
        
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
                        number: parseFloat(itemNumber) || 0,
                        unit: itemUnit
                    });
                }
            });
            settings.countdown_number_items = numberItems;
        }
    }
    
    // 제목 처리: 각 위젯 타입별로 title 필드 설정
    const titleInput = document.getElementById('widget_title');
    let widgetTitle = '';
    
    if (widgetType === 'block') {
        // 블록 위젯의 경우 block_title을 title로 매핑
        const blockTitle = formData.get('block_title');
        widgetTitle = blockTitle || '블록';
    } else if (widgetType === 'gallery') {
        // 갤러리 위젯의 경우 제목이 없으면 빈 문자열로 설정
        if (titleInput) {
            const titleValue = titleInput.value.trim();
            widgetTitle = titleValue || '';
        }
    } else if (widgetType === 'marquee_board') {
        // 게시글 전광판은 이미 위에서 처리됨
        widgetTitle = formData.get('title') || '게시글 전광판';
    } else if (widgetType === 'tab_menu') {
        // 탭메뉴는 이미 위에서 처리됨
        widgetTitle = formData.get('title') || '탭메뉴';
    } else if (widgetType === 'user_ranking') {
        // 회원 랭킹은 이미 위에서 처리됨
        widgetTitle = formData.get('title') || '회원 랭킹';
    } else {
        // 나머지 위젯 타입은 widget_title 필드 사용
        if (titleInput) {
            widgetTitle = titleInput.value.trim();
        }
        // title이 비어있으면 기본값 설정
        if (!widgetTitle || widgetTitle === '') {
            // 위젯 타입에 따른 기본 제목 설정
            const defaultTitles = {
                'popular_posts': '인기 게시글',
                'recent_posts': '최근 게시글',
                'weekly_popular_posts': '주간 인기글',
                'monthly_popular_posts': '월간 인기글',
                'board': '게시판',
                'custom_html': '커스텀 HTML',
                'block_slide': '블록 슬라이드',
                'image': '이미지',
                'image_slide': '이미지 슬라이드'
            };
            widgetTitle = defaultTitles[widgetType] || '위젯';
        }
    }
    
    // title 필드 설정
    formData.set('title', widgetTitle);
    
    // settings를 JSON으로 추가
    if (Object.keys(settings).length > 0) {
        formData.append('settings', JSON.stringify(settings));
    }
    
    fetch('{{ route("admin.custom-pages.widgets.store", ["site" => $site->slug, "customPage" => $customPage->id]) }}', {
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
            hideAddWidgetForm();
            location.reload();
        } else if (data.limit_exceeded) {
            // 플랜 제한 초과 모달 표시
            if (typeof showPlanLimitModal === 'function') {
                showPlanLimitModal(data.error);
            } else {
                alert(data.error);
            }
        } else {
            alert('위젯 추가에 실패했습니다: ' + (data.message || data.error || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Failed to fetch 오류는 보통 파일 크기가 너무 크거나 네트워크 오류일 때 발생
        if (error.message === 'Failed to fetch') {
            alert('위젯 추가 중 오류가 발생했습니다.\n\n가능한 원인:\n- 이미지 파일 크기가 너무 큽니다 (50MB 이하로 줄여주세요)\n- 네트워크 연결 문제\n- 서버 응답 시간 초과\n\n이미지 파일 크기를 줄이거나 잠시 후 다시 시도해주세요.');
        } else {
            alert('위젯 추가 중 오류가 발생했습니다: ' + (error.message || '알 수 없는 오류'));
        }
    })
    .finally(() => {
        // 로딩 상태 해제
        if (addButton) {
            addButton.disabled = false;
            addButton.innerHTML = '<i class="bi bi-plus-circle me-2"></i>추가';
        }
    });
    } catch (error) {
        console.error('Error:', error);
        alert('위젯 추가 중 오류가 발생했습니다: ' + (error.message || '알 수 없는 오류'));
        // 로딩 상태 해제
        if (addButton) {
            addButton.disabled = false;
            addButton.innerHTML = '<i class="bi bi-plus-circle me-2"></i>추가';
        }
    }
}

// 위젯 수정
function editCustomPageWidget(widgetId) {
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
    
    document.getElementById('edit_custom_page_widget_id').value = widgetId;
    document.getElementById('edit_custom_page_widget_title').value = title;
    document.getElementById('edit_custom_page_widget_is_active').checked = isActive;
    
    // 위젯 타입에 따라 게시글 수 입력 필드 표시/숨김
    const limitContainer = document.getElementById('edit_custom_page_widget_limit_container');
    const tabMenuContainer = document.getElementById('edit_custom_page_widget_tab_menu_container');
    const rankingContainer = document.getElementById('edit_custom_page_widget_ranking_container');
    const titleContainer = document.getElementById('edit_custom_page_widget_title_container_main');
    
    // AJAX 요청 전에 모든 컨테이너 먼저 숨기기 (이전 위젯 상태 초기화)
    const sortOrderContainerInit = document.getElementById('edit_custom_page_widget_sort_order_container');
    const marqueeDirectionContainerInit = document.getElementById('edit_custom_page_widget_marquee_direction_container');
    if (sortOrderContainerInit) sortOrderContainerInit.style.display = 'none';
    if (marqueeDirectionContainerInit) marqueeDirectionContainerInit.style.display = 'none';
    if (limitContainer) limitContainer.style.display = 'none';
    if (tabMenuContainer) tabMenuContainer.style.display = 'none';
    if (rankingContainer) rankingContainer.style.display = 'none';
    
    const widgetData = {
        id: widgetId,
        title: widgetItem.dataset.widgetTitle,
        type: widgetItem.dataset.widgetType,
        is_active: widgetItem.dataset.widgetActive === '1'
    };
    
    // Load widget settings from server
    const modal = document.getElementById('customPageWidgetSettingsModal');
    const fetchRoute = modal ? modal.getAttribute('data-fetch-route') : '';
    
    if (!fetchRoute) {
        alert('위젯 정보를 가져올 수 없습니다.');
        return;
    }
    
    fetch(`${fetchRoute}?widget_id=${widgetId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.error('Expected JSON but got:', text.substring(0, 200));
                throw new Error('서버에서 JSON 응답을 받지 못했습니다.');
            });
        }
    })
    .then(data => {
        if (data.success && data.widget) {
            const widget = data.widget;
            const settings = widget.settings || {};
            const widgetType = widget.type;
            const title = widget.title;
            const isActive = widget.is_active;
            
            // 기본 필드 설정
            document.getElementById('edit_custom_page_widget_id').value = widget.id;
            document.getElementById('edit_custom_page_widget_title').value = title;
            document.getElementById('edit_custom_page_widget_is_active').checked = isActive;
            
            // 변수 재정의 (스코프 문제 해결)
            const limitContainer = document.getElementById('edit_custom_page_widget_limit_container');
            const tabMenuContainer = document.getElementById('edit_custom_page_widget_tab_menu_container');
            const rankingContainer = document.getElementById('edit_custom_page_widget_ranking_container');
            const titleContainer = document.getElementById('edit_custom_page_widget_title_container_main');
            const boardContainer = document.getElementById('edit_custom_page_widget_board_container');
            const sortOrderContainer = document.getElementById('edit_custom_page_widget_sort_order_container');
            const marqueeDirectionContainer = document.getElementById('edit_custom_page_widget_marquee_direction_container');
            
            // 모든 컨테이너 숨기기
            if (limitContainer) limitContainer.style.display = 'none';
            if (tabMenuContainer) tabMenuContainer.style.display = 'none';
            if (rankingContainer) rankingContainer.style.display = 'none';
            if (boardContainer) boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            const galleryContainer = document.getElementById('edit_custom_page_widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('edit_custom_page_widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('edit_custom_page_widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('edit_custom_page_widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('edit_custom_page_widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            const customHtmlContainer = document.getElementById('edit_custom_page_widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            const contactFormContainer = document.getElementById('edit_custom_page_widget_contact_form_container');
            if (contactFormContainer) contactFormContainer.style.display = 'none';
            const mapContainer = document.getElementById('edit_custom_page_widget_map_container');
            if (mapContainer) mapContainer.style.display = 'none';
            const blockContainer = document.getElementById('edit_custom_page_widget_block_container');
            if (blockContainer) blockContainer.style.display = 'none';
            const blockSlideContainer = document.getElementById('edit_custom_page_widget_block_slide_container');
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            const imageContainer = document.getElementById('edit_custom_page_widget_image_container');
            if (imageContainer) imageContainer.style.display = 'none';
            const imageSlideContainer = document.getElementById('edit_custom_page_widget_image_slide_container');
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const toggleMenuContainer = document.getElementById('edit_custom_page_widget_toggle_menu_container');
            if (toggleMenuContainer) toggleMenuContainer.style.display = 'none';
            
            if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
                if (limitContainer) limitContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_custom_page_widget_limit')) {
                    document.getElementById('edit_custom_page_widget_limit').value = settings.limit || 10;
                }
                if (document.getElementById('edit_custom_page_widget_title')) {
                    document.getElementById('edit_custom_page_widget_title').value = title;
                }
            } else if (widgetType === 'board') {
                if (limitContainer) limitContainer.style.display = 'block';
                if (boardContainer) boardContainer.style.display = 'block';
                if (sortOrderContainer) sortOrderContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_custom_page_widget_limit')) {
                    document.getElementById('edit_custom_page_widget_limit').value = settings.limit || 10;
                }
                if (document.getElementById('edit_custom_page_widget_board_id')) {
                    document.getElementById('edit_custom_page_widget_board_id').value = settings.board_id || '';
                }
                if (document.getElementById('edit_custom_page_widget_sort_order')) {
                    document.getElementById('edit_custom_page_widget_sort_order').value = settings.sort_order || 'latest';
                }
                if (document.getElementById('edit_custom_page_widget_title')) {
                    document.getElementById('edit_custom_page_widget_title').value = title;
                }
            } else if (widgetType === 'board_viewer') {
                if (boardContainer) boardContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                const boardViewerNoBackgroundContainer = document.getElementById('edit_custom_page_widget_board_viewer_no_background_container');
                if (boardViewerNoBackgroundContainer) boardViewerNoBackgroundContainer.style.display = 'block';
                if (document.getElementById('edit_custom_page_widget_board_id')) {
                    document.getElementById('edit_custom_page_widget_board_id').value = settings.board_id || '';
                }
                if (document.getElementById('edit_custom_page_widget_board_viewer_no_background')) {
                    document.getElementById('edit_custom_page_widget_board_viewer_no_background').checked = settings.no_background || false;
                }
                if (document.getElementById('edit_custom_page_widget_title')) {
                    document.getElementById('edit_custom_page_widget_title').value = title;
                }
            } else if (widgetType === 'marquee_board') {
                if (limitContainer) limitContainer.style.display = 'block';
                if (boardContainer) boardContainer.style.display = 'block';
                if (sortOrderContainer) sortOrderContainer.style.display = 'block';
                if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (document.getElementById('edit_custom_page_widget_limit')) {
                    document.getElementById('edit_custom_page_widget_limit').value = settings.limit || 10;
                }
                if (document.getElementById('edit_custom_page_widget_board_id')) {
                    document.getElementById('edit_custom_page_widget_board_id').value = settings.board_id || '';
                }
                if (document.getElementById('edit_custom_page_widget_sort_order')) {
                    document.getElementById('edit_custom_page_widget_sort_order').value = settings.sort_order || 'latest';
                }
                if (marqueeDirectionContainer) {
                    const direction = settings.direction || 'left';
                    const directionRadio = document.getElementById(`edit_custom_page_direction_${direction}`);
                    if (directionRadio) directionRadio.checked = true;
                }
                if (document.getElementById('edit_custom_page_widget_title')) {
                    document.getElementById('edit_custom_page_widget_title').value = '게시글 전광판';
                }
            } else if (widgetType === 'gallery') {
                if (galleryContainer) galleryContainer.style.display = 'block';
                if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'block';
                if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                
                const displayType = settings.display_type || 'grid';
                if (document.getElementById('edit_custom_page_widget_gallery_board_id')) {
                    document.getElementById('edit_custom_page_widget_gallery_board_id').value = settings.board_id || '';
                }
                if (document.getElementById('edit_custom_page_widget_gallery_display_type')) {
                    document.getElementById('edit_custom_page_widget_gallery_display_type').value = displayType;
                    if (displayType === 'grid') {
                        if (galleryGridContainer) galleryGridContainer.style.display = 'block';
                        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                        if (document.getElementById('edit_custom_page_widget_gallery_cols')) {
                            document.getElementById('edit_custom_page_widget_gallery_cols').value = settings.cols || 3;
                        }
                        if (document.getElementById('edit_custom_page_widget_gallery_rows')) {
                            document.getElementById('edit_custom_page_widget_gallery_rows').value = settings.rows || 3;
                        }
                    } else {
                        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                        if (gallerySlideContainer) gallerySlideContainer.style.display = 'block';
                        if (document.getElementById('edit_custom_page_widget_gallery_slide_cols')) {
                            document.getElementById('edit_custom_page_widget_gallery_slide_cols').value = settings.slide_cols || 3;
                        }
                        // 슬라이드 방향 라디오 버튼 체크 (up, down은 left로 변환)
                        let slideDirection = settings.slide_direction || 'left';
                        if (slideDirection === 'up' || slideDirection === 'down') {
                            slideDirection = 'left';
                        }
                        const slideDirectionRadio = document.querySelector(`input[name="edit_main_gallery_slide_direction"][value="${slideDirection}"]`);
                        if (slideDirectionRadio) {
                            slideDirectionRadio.checked = true;
                        }
                    }
                }
                if (document.getElementById('edit_custom_page_widget_gallery_show_title')) {
                    document.getElementById('edit_custom_page_widget_gallery_show_title').checked = settings.show_title !== false;
                }
                if (!title || title === '갤러리' || title.trim() === '') {
                    if (document.getElementById('edit_custom_page_widget_title')) {
                        document.getElementById('edit_custom_page_widget_title').value = '';
                    }
                } else {
                    if (document.getElementById('edit_custom_page_widget_title')) {
                        document.getElementById('edit_custom_page_widget_title').value = title;
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
                    editCustomPageTabMenuIndex = 0;
                    tabs.forEach((tab, index) => {
                        editCustomPageTabMenuIndex = index;
                        addEditCustomPageTabMenuItem();
                        const item = document.getElementById(`edit_custom_page_tab_menu_item_${index}`);
                        if (item) {
                            const nameInput = item.querySelector('.edit-custom-page-tab-menu-name');
                            const widgetTypeSelect = item.querySelector('.edit-custom-page-tab-menu-widget-type');
                            const limitInput = item.querySelector('.edit-custom-page-tab-menu-limit');
                            if (nameInput) nameInput.value = tab.name || '';
                            if (widgetTypeSelect) widgetTypeSelect.value = tab.widget_type || '';
                            if (limitInput) limitInput.value = tab.limit || 10;
                            if (tab.widget_type === 'board') {
                                const boardContainer = item.querySelector('.edit-custom-page-tab-menu-board-container');
            } else if (widgetType === 'toggle_menu') {
                if (toggleMenuContainer) toggleMenuContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_custom_page_widget_title')) {
                    const titleInput = document.getElementById('edit_custom_page_widget_title');
                    titleInput.required = true;
                    if (!titleInput.value || titleInput.value === '토글 메뉴') {
                        titleInput.value = '';
                    }
                }
                // 토글 메뉴 목록 로드
                fetch('{{ $site->isUsingDirectDomain() ? "/admin/toggle-menus/list" : "/site/" . $site->slug . "/admin/toggle-menus/list" }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const select = document.getElementById('edit_custom_page_widget_toggle_menu_id');
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
                                if (boardContainer) boardContainer.style.display = 'block';
                                const boardSelect = item.querySelector('.edit-custom-page-tab-menu-board-id');
                                if (boardSelect) boardSelect.value = tab.board_id || '';
                            }
                            // 로드된 항목은 접힌 상태로 시작
                            const body = document.getElementById(`edit_custom_page_tab_menu_item_${index}_body`);
                            const icon = document.getElementById(`edit_custom_page_tab_menu_item_${index}_icon`);
                            if (body && icon) {
                                body.style.display = 'none';
                                icon.className = 'bi bi-chevron-right';
                            }
                        }
                    });
                    editCustomPageTabMenuIndex++;
                }
            } else if (widgetType === 'user_ranking') {
                if (rankingContainer) rankingContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                if (document.getElementById('edit_custom_page_widget_rank_ranking')) {
                    document.getElementById('edit_custom_page_widget_rank_ranking').checked = settings.enable_rank_ranking || false;
                }
                if (document.getElementById('edit_custom_page_widget_point_ranking')) {
                    document.getElementById('edit_custom_page_widget_point_ranking').checked = settings.enable_point_ranking || false;
                }
                if (document.getElementById('edit_custom_page_widget_ranking_limit')) {
                    document.getElementById('edit_custom_page_widget_ranking_limit').value = settings.ranking_limit || 5;
                }
            } else if (widgetType === 'custom_html') {
                if (customHtmlContainer) customHtmlContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                const titleOptional = document.getElementById('edit_custom_page_widget_title_optional');
                if (titleOptional) titleOptional.style.display = 'inline';
                if (document.getElementById('edit_custom_page_widget_custom_html')) {
                    document.getElementById('edit_custom_page_widget_custom_html').value = settings.html || settings.custom_html || '';
                }
                if (document.getElementById('edit_custom_page_widget_title')) {
                    document.getElementById('edit_custom_page_widget_title').value = title;
                }
            } else if (widgetType === 'contact_form') {
                if (contactFormContainer) contactFormContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_custom_page_widget_contact_form_id')) {
                    document.getElementById('edit_custom_page_widget_contact_form_id').value = settings.contact_form_id || '';
                }
                if (document.getElementById('edit_custom_page_widget_title')) {
                    document.getElementById('edit_custom_page_widget_title').value = title;
                }
            } else if (widgetType === 'map') {
                if (mapContainer) mapContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_custom_page_widget_map_id')) {
                    document.getElementById('edit_custom_page_widget_map_id').value = settings.map_id || '';
                }
                if (document.getElementById('edit_custom_page_widget_title')) {
                    document.getElementById('edit_custom_page_widget_title').value = title;
                }
            } else if (widgetType === 'block') {
                if (blockContainer) blockContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                // 블록 이미지 설정 로드
                const enableImage = settings.enable_image || false;
                const blockImageUrl = settings.block_image_url || '';
                const blockImagePaddingTop = settings.block_image_padding_top || 0;
                const blockImagePaddingBottom = settings.block_image_padding_bottom || 0;
                const blockImagePaddingLeft = settings.block_image_padding_left || 0;
                const blockImagePaddingRight = settings.block_image_padding_right || 0;
                if (document.getElementById('edit_custom_page_widget_block_enable_image')) {
                    document.getElementById('edit_custom_page_widget_block_enable_image').checked = enableImage;
                    toggleBlockImageFields('edit_custom_page_widget_block');
                }
                
                if (enableImage && blockImageUrl) {
                    const imageUrlInput = document.getElementById('edit_custom_page_widget_block_image_url');
                    const previewContainer = document.getElementById('edit_custom_page_widget_block_image_preview_container');
                    const previewImg = document.getElementById('edit_custom_page_widget_block_image_preview');
                    if (imageUrlInput) imageUrlInput.value = blockImageUrl;
                    if (previewImg) previewImg.src = blockImageUrl;
                    if (previewContainer) previewContainer.style.display = 'block';
                }
                
                // 이미지 패딩 값 로드
                if (document.getElementById('edit_custom_page_widget_block_image_padding_top')) {
                    document.getElementById('edit_custom_page_widget_block_image_padding_top').value = blockImagePaddingTop || 0;
                }
                if (document.getElementById('edit_custom_page_widget_block_image_padding_bottom')) {
                    document.getElementById('edit_custom_page_widget_block_image_padding_bottom').value = blockImagePaddingBottom || 0;
                }
                if (document.getElementById('edit_custom_page_widget_block_image_padding_left')) {
                    document.getElementById('edit_custom_page_widget_block_image_padding_left').value = blockImagePaddingLeft || 0;
                }
                if (document.getElementById('edit_custom_page_widget_block_image_padding_right')) {
                    document.getElementById('edit_custom_page_widget_block_image_padding_right').value = blockImagePaddingRight || 0;
                }
                
                if (document.getElementById('edit_custom_page_widget_block_title')) {
                    document.getElementById('edit_custom_page_widget_block_title').value = settings.block_title || '';
                }
                if (document.getElementById('edit_custom_page_widget_block_content')) {
                    document.getElementById('edit_custom_page_widget_block_content').value = settings.block_content || '';
                }
                const textAlign = settings.text_align || 'left';
                const textAlignRadio = document.querySelector(`input[name="edit_custom_page_block_text_align"][value="${textAlign}"]`);
                if (textAlignRadio) textAlignRadio.checked = true;
                
                const backgroundType = settings.background_type || 'color';
                if (document.getElementById('edit_custom_page_widget_block_background_type')) {
                    document.getElementById('edit_custom_page_widget_block_background_type').value = backgroundType;
                    handleEditCustomPageBlockBackgroundTypeChange();
                }
                
                if (backgroundType === 'color') {
                    if (document.getElementById('edit_custom_page_widget_block_background_color')) {
                        document.getElementById('edit_custom_page_widget_block_background_color').value = settings.background_color || '#007bff';
                    }
                    if (document.getElementById('edit_custom_page_widget_block_background_color_alpha')) {
                        document.getElementById('edit_custom_page_widget_block_background_color_alpha').value = settings.background_color_alpha !== undefined && settings.background_color_alpha !== null ? settings.background_color_alpha : 100;
                    }
                } else if (backgroundType === 'gradient') {
                    const gradientStart = settings.background_gradient_start || '#ffffff';
                    const gradientEnd = settings.background_gradient_end || '#000000';
                    const gradientAngle = settings.background_gradient_angle || 90;
                    if (document.getElementById('edit_custom_page_widget_block_gradient_start')) {
                        document.getElementById('edit_custom_page_widget_block_gradient_start').value = gradientStart;
                    }
                    if (document.getElementById('edit_custom_page_widget_block_gradient_end')) {
                        document.getElementById('edit_custom_page_widget_block_gradient_end').value = gradientEnd;
                    }
                    if (document.getElementById('edit_custom_page_widget_block_gradient_angle')) {
                        document.getElementById('edit_custom_page_widget_block_gradient_angle').value = gradientAngle;
                    }
                    const gradientPreview = document.getElementById('edit_custom_page_widget_block_gradient_preview');
                    if (gradientPreview) {
                        gradientPreview.style.background = `linear-gradient(${gradientAngle}deg, ${gradientStart}, ${gradientEnd})`;
                    }
                } else if (backgroundType === 'image') {
                    if (settings.background_image_url && document.getElementById('edit_custom_page_widget_block_image_preview_img')) {
                        document.getElementById('edit_custom_page_widget_block_image_preview_img').src = settings.background_image_url;
                        document.getElementById('edit_custom_page_widget_block_image_preview').style.display = 'block';
                        document.getElementById('edit_custom_page_widget_block_background_image').value = settings.background_image_url;
                    }
                    if (document.getElementById('edit_custom_page_widget_block_background_image_alpha')) {
                        document.getElementById('edit_custom_page_widget_block_background_image_alpha').value = settings.background_image_alpha !== undefined && settings.background_image_alpha !== null ? settings.background_image_alpha : 100;
                    }
                    if (document.getElementById('edit_custom_page_widget_block_background_image_full_width')) {
                        document.getElementById('edit_custom_page_widget_block_background_image_full_width').checked = settings.background_image_full_width || false;
                    }
                }
                
                if (document.getElementById('edit_custom_page_widget_block_title_color')) {
                    document.getElementById('edit_custom_page_widget_block_title_color').value = settings.title_color || settings.font_color || '#ffffff';
                }
                if (document.getElementById('edit_custom_page_widget_block_content_color')) {
                    document.getElementById('edit_custom_page_widget_block_content_color').value = settings.content_color || settings.font_color || '#ffffff';
                }
                if (document.getElementById('edit_custom_page_widget_block_title_font_size')) {
                    let titleSize = settings.title_font_size || '16';
                    if (titleSize.includes('rem')) {
                        titleSize = parseFloat(titleSize) * 16;
                    }
                    document.getElementById('edit_custom_page_widget_block_title_font_size').value = titleSize;
                }
                if (document.getElementById('edit_custom_page_widget_block_content_font_size')) {
                    let contentSize = settings.content_font_size || '14';
                    if (contentSize.includes('rem')) {
                        contentSize = parseFloat(contentSize) * 16;
                    }
                    document.getElementById('edit_custom_page_widget_block_content_font_size').value = contentSize;
                }
                
                if (document.getElementById('edit_custom_page_widget_block_padding_top')) {
                    document.getElementById('edit_custom_page_widget_block_padding_top').value = settings.padding_top !== undefined && settings.padding_top !== null ? settings.padding_top : 20;
                }
                if (document.getElementById('edit_custom_page_widget_block_padding_bottom')) {
                    document.getElementById('edit_custom_page_widget_block_padding_bottom').value = settings.padding_bottom !== undefined && settings.padding_bottom !== null ? settings.padding_bottom : 20;
                }
                if (document.getElementById('edit_custom_page_widget_block_padding_left')) {
                    document.getElementById('edit_custom_page_widget_block_padding_left').value = settings.padding_left !== undefined && settings.padding_left !== null ? settings.padding_left : 20;
                }
                if (document.getElementById('edit_custom_page_widget_block_padding_right')) {
                    document.getElementById('edit_custom_page_widget_block_padding_right').value = settings.padding_right !== undefined && settings.padding_right !== null ? settings.padding_right : 20;
                }
                if (document.getElementById('edit_custom_page_widget_block_title_content_gap')) {
                    document.getElementById('edit_custom_page_widget_block_title_content_gap').value = settings.title_content_gap !== undefined && settings.title_content_gap !== null ? settings.title_content_gap : 8;
                }
                if (document.getElementById('edit_custom_page_widget_block_button_top_margin')) {
                    document.getElementById('edit_custom_page_widget_block_button_top_margin').value = settings.button_top_margin !== undefined && settings.button_top_margin !== null ? settings.button_top_margin : 12;
                }
                if (document.getElementById('edit_custom_page_widget_block_link')) {
                    document.getElementById('edit_custom_page_widget_block_link').value = settings.link || '';
                }
                if (document.getElementById('edit_custom_page_widget_block_open_new_tab')) {
                    document.getElementById('edit_custom_page_widget_block_open_new_tab').checked = settings.open_new_tab || false;
                }
                
                // 버튼 관리 기능 로드
                const buttons = settings.buttons || [];
                const buttonsList = document.getElementById('edit_custom_page_widget_block_buttons_list');
                if (buttonsList) {
                    buttonsList.innerHTML = '';
                    editCustomPageBlockButtonIndex = 0;
                    buttons.forEach((button, index) => {
                        addEditCustomPageBlockButton(button);
                    });
                    
                    // 버튼이 있으면 연결 링크 필드 숨기기
                    const linkContainer = document.getElementById('edit_custom_page_widget_block_link_container');
                    if (linkContainer) {
                        linkContainer.style.display = buttons.length > 0 ? 'none' : 'block';
                    }
                }
            } else if (widgetType === 'block_slide') {
                if (blockSlideContainer) blockSlideContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                const slideDirection = settings.slide_direction || 'left';
                const directionRadio = document.querySelector(`input[name="edit_custom_page_block_slide_direction"][value="${slideDirection}"]`);
                if (directionRadio) directionRadio.checked = true;
                
                // 슬라이드 유지 시간 로드
                const slideHoldTime = settings.slide_hold_time || 3;
                const holdTimeInput = document.getElementById('edit_custom_page_block_slide_hold_time');
                if (holdTimeInput) holdTimeInput.value = slideHoldTime;
                
                const blocks = settings.blocks || [];
                const itemsContainer = document.getElementById('edit_custom_page_widget_block_slide_items');
                if (itemsContainer) {
                    itemsContainer.innerHTML = '';
                    editCustomPageBlockSlideItemIndex = 0;
                    blocks.forEach((block) => {
                        addEditMainBlockSlideItem(block);
                    });
                }
            } else if (widgetType === 'image') {
                if (imageContainer) imageContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                // 다른 위젯의 옵션들 숨기기
                if (sortOrderContainer) sortOrderContainer.style.display = 'none';
                if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
                
                if (settings.image_url && document.getElementById('edit_custom_page_widget_image_preview_img')) {
                    document.getElementById('edit_custom_page_widget_image_preview_img').src = settings.image_url;
                    document.getElementById('edit_custom_page_widget_image_preview').style.display = 'block';
                    document.getElementById('edit_custom_page_widget_image_url').value = settings.image_url;
                }
                // 이미지 width 로드
                if (document.getElementById('edit_custom_page_widget_image_width')) {
                    document.getElementById('edit_custom_page_widget_image_width').value = settings.image_width || 100;
                }
                if (document.getElementById('edit_custom_page_widget_image_link')) {
                    document.getElementById('edit_custom_page_widget_image_link').value = settings.image_link || '';
                }
                if (document.getElementById('edit_custom_page_widget_image_open_new_tab')) {
                    document.getElementById('edit_custom_page_widget_image_open_new_tab').checked = settings.image_open_new_tab || false;
                }
                
                // 텍스트 오버레이 설정 로드
                const textOverlayCheckbox = document.getElementById('edit_custom_page_widget_image_text_overlay');
                const textOverlayContainer = document.getElementById('edit_custom_page_widget_image_text_overlay_container');
                if (textOverlayCheckbox) {
                    textOverlayCheckbox.checked = settings.text_overlay || false;
                    if (textOverlayContainer) {
                        textOverlayContainer.style.display = settings.text_overlay ? 'block' : 'none';
                    }
                }
                if (document.getElementById('edit_custom_page_widget_image_title')) {
                    document.getElementById('edit_custom_page_widget_image_title').value = settings.title || '';
                }
                if (document.getElementById('edit_custom_page_widget_image_title_font_size')) {
                    document.getElementById('edit_custom_page_widget_image_title_font_size').value = settings.title_font_size || 24;
                }
                if (document.getElementById('edit_custom_page_widget_image_content')) {
                    document.getElementById('edit_custom_page_widget_image_content').value = settings.content || '';
                }
                if (document.getElementById('edit_custom_page_widget_image_content_font_size')) {
                    document.getElementById('edit_custom_page_widget_image_content_font_size').value = settings.content_font_size || 16;
                }
                if (document.getElementById('edit_custom_page_widget_image_title_content_gap')) {
                    document.getElementById('edit_custom_page_widget_image_title_content_gap').value = settings.title_content_gap || 10;
                }
                if (document.getElementById('edit_custom_page_widget_image_text_padding_left')) {
                    document.getElementById('edit_custom_page_widget_image_text_padding_left').value = settings.text_padding_left || 0;
                }
                if (document.getElementById('edit_custom_page_widget_image_text_padding_right')) {
                    document.getElementById('edit_custom_page_widget_image_text_padding_right').value = settings.text_padding_right || 0;
                }
                if (document.getElementById('edit_custom_page_widget_image_text_padding_top')) {
                    document.getElementById('edit_custom_page_widget_image_text_padding_top').value = settings.text_padding_top || 0;
                }
                if (document.getElementById('edit_custom_page_widget_image_text_padding_bottom')) {
                    document.getElementById('edit_custom_page_widget_image_text_padding_bottom').value = settings.text_padding_bottom || 10;
                }
                // 정렬 설정
                const alignH = settings.align_h || 'left';
                const alignHRadio = document.querySelector(`input[name="edit_custom_page_widget_image_align_h"][value="${alignH}"]`);
                if (alignHRadio) alignHRadio.checked = true;
                const alignV = settings.align_v || 'middle';
                const alignVRadio = document.querySelector(`input[name="edit_custom_page_widget_image_align_v"][value="${alignV}"]`);
                if (alignVRadio) alignVRadio.checked = true;
                if (document.getElementById('edit_custom_page_widget_image_text_color')) {
                    document.getElementById('edit_custom_page_widget_image_text_color').value = settings.text_color || '#ffffff';
                }
                // 버튼 설정
                const hasButtonCheckbox = document.getElementById('edit_custom_page_widget_image_has_button');
                const buttonContainer = document.getElementById('edit_custom_page_widget_image_button_container');
                if (hasButtonCheckbox) {
                    hasButtonCheckbox.checked = settings.has_button || false;
                    if (buttonContainer) {
                        buttonContainer.style.display = settings.has_button ? 'block' : 'none';
                    }
                }
                if (document.getElementById('edit_custom_page_widget_image_button_text')) {
                    document.getElementById('edit_custom_page_widget_image_button_text').value = settings.button_text || '';
                }
                if (document.getElementById('edit_custom_page_widget_image_button_link')) {
                    document.getElementById('edit_custom_page_widget_image_button_link').value = settings.button_link || '';
                }
                if (document.getElementById('edit_custom_page_widget_image_button_new_tab')) {
                    document.getElementById('edit_custom_page_widget_image_button_new_tab').checked = settings.button_new_tab || false;
                }
                if (document.getElementById('edit_custom_page_widget_image_button_color')) {
                    document.getElementById('edit_custom_page_widget_image_button_color').value = settings.button_color || '#0d6efd';
                }
                if (document.getElementById('edit_custom_page_widget_image_button_text_color')) {
                    document.getElementById('edit_custom_page_widget_image_button_text_color').value = settings.button_text_color || '#ffffff';
                }
                if (document.getElementById('edit_custom_page_widget_image_button_border_color')) {
                    document.getElementById('edit_custom_page_widget_image_button_border_color').value = settings.button_border_color || '#0d6efd';
                }
                if (document.getElementById('edit_custom_page_widget_image_button_opacity')) {
                    document.getElementById('edit_custom_page_widget_image_button_opacity').value = settings.button_opacity !== undefined ? settings.button_opacity : 100;
                }
                if (document.getElementById('edit_custom_page_widget_image_button_hover_bg_color')) {
                    document.getElementById('edit_custom_page_widget_image_button_hover_bg_color').value = settings.button_hover_bg_color || '#0b5ed7';
                }
                if (document.getElementById('edit_custom_page_widget_image_button_hover_text_color')) {
                    document.getElementById('edit_custom_page_widget_image_button_hover_text_color').value = settings.button_hover_text_color || '#ffffff';
                }
                if (document.getElementById('edit_custom_page_widget_image_button_hover_border_color')) {
                    document.getElementById('edit_custom_page_widget_image_button_hover_border_color').value = settings.button_hover_border_color || '#0a58ca';
                }
            } else if (widgetType === 'image_slide') {
                if (imageSlideContainer) imageSlideContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                const slideDirection = settings.slide_direction || 'left';
                const directionRadio = document.querySelector(`input[name="edit_custom_page_image_slide_direction"][value="${slideDirection}"]`);
                if (directionRadio) directionRadio.checked = true;
                
                const images = settings.images || [];
                const itemsContainer = document.getElementById('edit_custom_page_widget_image_slide_items');
                if (itemsContainer) {
                    itemsContainer.innerHTML = '';
                    editCustomPageImageSlideItemIndex = 0;
                    images.forEach((imageItem) => {
                        addEditMainImageSlideItem(imageItem);
                    });
                }
            } else {
                if (titleContainer) titleContainer.style.display = 'block';
                if (document.getElementById('edit_custom_page_widget_title')) {
                    document.getElementById('edit_custom_page_widget_title').value = title;
                }
            }
            
            // 모든 설정이 완료된 후, image 위젯에서 불필요한 옵션 숨기기 (최종 확인)
            if (widgetType === 'image' || widgetType === 'block' || widgetType === 'block_slide' || 
                widgetType === 'gallery' || widgetType === 'board_viewer' || widgetType === 'tab_menu' ||
                widgetType === 'ranking' || widgetType === 'image_slide' || widgetType === 'countdown') {
                const finalSortOrderContainer = document.getElementById('edit_custom_page_widget_sort_order_container');
                const finalMarqueeDirectionContainer = document.getElementById('edit_custom_page_widget_marquee_direction_container');
                if (widgetType !== 'board' && widgetType !== 'marquee_board') {
                    if (finalSortOrderContainer) finalSortOrderContainer.style.display = 'none';
                }
                if (widgetType !== 'marquee_board') {
                    if (finalMarqueeDirectionContainer) finalMarqueeDirectionContainer.style.display = 'none';
                }
            }
            
            // 모든 설정이 완료된 후 모달 열기
            const modal = new bootstrap.Modal(document.getElementById('customPageWidgetSettingsModal'));
            modal.show();
            
            // 이미지 위젯일 때 불필요한 옵션 HTML 요소 완전히 제거
            if (widgetType === 'image') {
                const elementsToRemove = [
                    'edit_custom_page_widget_sort_order_container',
                    'edit_custom_page_widget_marquee_direction_container',
                    'edit_custom_page_widget_gallery_container',
                    'edit_custom_page_widget_gallery_display_type_container',
                    'edit_custom_page_widget_gallery_grid_container',
                    'edit_custom_page_widget_gallery_slide_container',
                    'edit_custom_page_widget_gallery_show_title_container',
                    'edit_custom_page_widget_image_slide_speed_container',
                    'edit_custom_page_widget_image_slide_visible_count_container',
                    'edit_custom_page_widget_image_slide_visible_count_mobile_container',
                    'edit_custom_page_widget_image_slide_gap_container',
                    'edit_custom_page_widget_image_slide_background_container',
                    'edit_custom_page_widget_block_link_container',
                    'edit_custom_page_widget_countdown_dday_container',
                    'edit_custom_page_widget_block_slide_container'
                ];
                elementsToRemove.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.remove();
                });
            }
        } else {
            alert('위젯 정보를 가져오는데 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error fetching widget:', error);
        alert('위젯 정보를 가져오는 중 오류가 발생했습니다.');
    });
}

// 위젯 삭제
// 위젯 위로 이동
function moveCustomPageWidgetUp(widgetId, containerId, columnIndex) {
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
    saveCustomPageWidgetOrder(containerId, columnIndex);
}

// 위젯 아래로 이동
function moveCustomPageWidgetDown(widgetId, containerId, columnIndex) {
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
    saveCustomPageWidgetOrder(containerId, columnIndex);
}

// 위젯 순서 저장
function saveCustomPageWidgetOrder(containerId, columnIndex, movedWidget = null) {
    // DOM 업데이트를 기다리기 위해 약간의 지연
    setTimeout(() => {
        const widgetList = document.querySelector(`.widget-list-in-column[data-container-id="${containerId}"][data-column-index="${columnIndex}"]`);
        
        let widgetData = [];
        if (widgetList) {
            const widgets = Array.from(widgetList.querySelectorAll('.widget-item'));
            widgetData = widgets.map((item, index) => {
                const widgetId = parseInt(item.dataset.widgetId);
                const itemContainerId = parseInt(item.dataset.containerId);
                const itemColumnIndex = parseInt(item.dataset.columnIndex);
                
                const data = {
                    id: widgetId,
                    order: index + 1
                };
                
                // 위젯이 다른 컨테이너로 이동한 경우
                if (movedWidget && movedWidget.id === widgetId && 
                    (itemContainerId !== containerId || itemColumnIndex !== columnIndex)) {
                    data.container_id = containerId;
                    data.column_index = columnIndex;
                }
                
                return data;
            });
        }
        
        fetch('{{ route("admin.custom-pages.widgets.reorder", ["site" => $site->slug, "customPage" => $customPage->id]) }}', {
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
            } else {
                // 위젯이 이동한 경우 데이터 속성 업데이트
                if (movedWidget) {
                    const movedElement = document.querySelector(`.widget-item[data-widget-id="${movedWidget.id}"]`);
                    if (movedElement) {
                        movedElement.dataset.containerId = containerId;
                        movedElement.dataset.columnIndex = columnIndex;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('위젯 순서 저장 중 오류가 발생했습니다.');
            location.reload(); // 오류 시 새로고침
        });
    }, 100); // DOM 업데이트를 위한 100ms 지연
}

function deleteCustomPageWidget(widgetId) {
    if (!confirm('위젯을 삭제하시겠습니까?')) {
        return;
    }
    
    fetch('{{ route("admin.custom-pages.widgets.delete", ["site" => $site->slug, "customPage" => $customPage->id, "widget" => ":widgetId"]) }}'.replace(':widgetId', widgetId), {
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
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
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
            const contactFormContainer = document.getElementById('widget_contact_form_container');
            const mapContainer = document.getElementById('widget_map_container');
            const toggleMenuContainer = document.getElementById('widget_toggle_menu_container');
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
            if (contactFormContainer) contactFormContainer.style.display = 'none';
            if (mapContainer) mapContainer.style.display = 'none';
            if (toggleMenuContainer) toggleMenuContainer.style.display = 'none';
            if (blockContainer) blockContainer.style.display = 'none';
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            if (imageContainer) imageContainer.style.display = 'none';
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const countdownContainer = document.getElementById('widget_countdown_container');
            if (countdownContainer) countdownContainer.style.display = 'none';
            if (titleHelp) titleHelp.style.display = 'none';
            
            // 블록 위젯 타입이 변경될 때 버튼 인덱스 초기화
            if (widgetType === 'block' || widgetType === 'block_slide') {
                blockButtonIndex = 0;
                // 버튼 리스트 초기화
                const buttonsList = document.getElementById('widget_block_buttons_list');
                if (buttonsList) {
                    buttonsList.innerHTML = '';
                }
            }
            
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
            } else if (widgetType === 'board_viewer') {
                if (boardContainer) boardContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = false;
                const boardViewerNoBackgroundContainer = document.getElementById('widget_board_viewer_no_background_container');
                if (boardViewerNoBackgroundContainer) boardViewerNoBackgroundContainer.style.display = 'block';
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
                if (titleInput) titleInput.required = false;
                const titleOptional = document.getElementById('widget_title_optional');
                if (titleOptional) titleOptional.style.display = 'inline';
            } else if (widgetType === 'contact_form') {
                if (contactFormContainer) contactFormContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = true;
            } else if (widgetType === 'map') {
                if (mapContainer) mapContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
                if (titleInput) titleInput.required = true;
            } else if (widgetType === 'toggle_menu') {
                if (toggleMenuContainer) toggleMenuContainer.style.display = 'block';
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
            } else if (widgetType === 'countdown') {
                if (countdownContainer) countdownContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'block';
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
    const gradientContainer = document.getElementById('widget_block_gradient_container');
    
    if (backgroundType === 'none') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
    } else if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'block';
        if (gradientContainer) gradientContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'block';
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
        <div class="mb-3"><label class="form-label">제목</label><textarea class="form-control block-slide-title" name="block_slide[${itemIndex}][title]" rows="2" placeholder="제목을 입력하세요 (엔터로 줄바꿈)"></textarea><small class="text-muted">엔터 키로 줄바꿈이 가능합니다.</small></div>
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
                <option value="gradient">그라데이션</option>
                <option value="image">이미지</option>
            </select>
        </div>
        <div class="mb-3 block-slide-color-container" id="block_slide_${itemIndex}_color_container">
            <label class="form-label">배경 컬러</label>
            <input type="color" class="form-control form-control-color block-slide-background-color" name="block_slide[${itemIndex}][background_color]" value="#007bff">
        </div>
        <div class="mb-3 block-slide-gradient-container" id="block_slide_${itemIndex}_gradient_container" style="display: none;">
            <label class="form-label">그라데이션 설정</label>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div id="block_slide_${itemIndex}_gradient_preview" 
                     style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #ffffff, #000000);"
                     onclick="openBlockGradientModal('block_slide_${itemIndex}')"
                     title="그라데이션 설정">
                </div>
                <input type="hidden" 
                       class="block-slide-background-gradient-start" 
                       name="block_slide[${itemIndex}][background_gradient_start]" 
                       id="block_slide_${itemIndex}_gradient_start"
                       value="#ffffff">
                <input type="hidden" 
                       class="block-slide-background-gradient-end" 
                       name="block_slide[${itemIndex}][background_gradient_end]" 
                       id="block_slide_${itemIndex}_gradient_end"
                       value="#000000">
                <input type="hidden" 
                       class="block-slide-background-gradient-angle" 
                       name="block_slide[${itemIndex}][background_gradient_angle]" 
                       id="block_slide_${itemIndex}_gradient_angle"
                       value="90">
            </div>
            <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
        </div>
        <div class="mb-3"><label class="form-label">제목 컬러</label>
            <input type="color" class="form-control form-control-color block-slide-title-color" name="block_slide[${itemIndex}][title_color]" value="#ffffff">
        </div>
        <div class="mb-3"><label class="form-label">내용 컬러</label>
            <input type="color" class="form-control form-control-color block-slide-content-color" name="block_slide[${itemIndex}][content_color]" value="#ffffff">
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
            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addCustomPageBlockSlideButton(${itemIndex})">
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
        <div class="mb-3" id="block_slide_${itemIndex}_link_container">
            <label class="form-label">
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

function removeBlockSlideItem(itemIndex) {
    const item = document.getElementById(`block_slide_item_${itemIndex}`);
    if (item) item.remove();
}

// 커스텀 페이지 블록 슬라이드 버튼 관리
let customPageBlockSlideButtonIndices = {};

function addCustomPageBlockSlideButton(itemIndex) {
    if (!customPageBlockSlideButtonIndices[itemIndex]) {
        customPageBlockSlideButtonIndices[itemIndex] = 0;
    }
    
    const container = document.getElementById(`block_slide_${itemIndex}_buttons_list`);
    if (!container) return;
    
    const buttonIndex = customPageBlockSlideButtonIndices[itemIndex];
    const buttonId = `block_slide_${itemIndex}_button_${buttonIndex}`;
    const buttonHtml = `
        <div class="card mb-3" id="${buttonId}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">버튼 ${buttonIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeCustomPageBlockSlideButton('${buttonId}', ${itemIndex})">
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
                <div class="mb-3">
                    <label class="form-label">버튼 배경 타입</label>
                    <select class="form-select block-slide-button-background-type" 
                            name="block_slide[${itemIndex}][buttons][${buttonIndex}][background_type]"
                            onchange="handleButtonBackgroundTypeChange(this)">
                        <option value="color">컬러</option>
                        <option value="gradient">그라데이션</option>
                    </select>
                </div>
                <div class="row block-slide-button-color-container">
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
                <div class="row block-slide-button-gradient-container" style="display: none;">
                    <div class="col-12 mb-3">
                        <label class="form-label">그라데이션 설정</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div id="block_slide_${itemIndex}_button_${buttonIndex}_gradient_preview" 
                                 class="block-slide-button-gradient-preview"
                                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #007bff, #0056b3);"
                                 onclick="openButtonGradientModal('block_slide_${itemIndex}_button_${buttonIndex}')"
                                 title="그라데이션 설정">
                            </div>
                            <input type="hidden" 
                                   class="block-slide-button-gradient-start" 
                                   id="block_slide_${itemIndex}_button_${buttonIndex}_gradient_start"
                                   name="block_slide[${itemIndex}][buttons][${buttonIndex}][background_gradient_start]" 
                                   value="#007bff">
                            <input type="hidden" 
                                   class="block-slide-button-gradient-end" 
                                   id="block_slide_${itemIndex}_button_${buttonIndex}_gradient_end"
                                   name="block_slide[${itemIndex}][buttons][${buttonIndex}][background_gradient_end]" 
                                   value="#0056b3">
                            <input type="hidden" 
                                   class="block-slide-button-gradient-angle" 
                                   id="block_slide_${itemIndex}_button_${buttonIndex}_gradient_angle"
                                   name="block_slide[${itemIndex}][buttons][${buttonIndex}][background_gradient_angle]" 
                                   value="90">
                        </div>
                        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 배경 투명도</label>
                    <input type="range" 
                           class="form-range block-slide-button-opacity" 
                           name="block_slide[${itemIndex}][buttons][${buttonIndex}][opacity]" 
                           id="block_slide_${itemIndex}_button_${buttonIndex}_opacity"
                           value="100" 
                           min="0" 
                           max="100" 
                           step="1"
                           onchange="document.getElementById('block_slide_${itemIndex}_button_${buttonIndex}_opacity_value').textContent = this.value + '%'">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted" style="font-size: 0.7rem;">0%</small>
                        <small class="text-muted" id="block_slide_${itemIndex}_button_${buttonIndex}_opacity_value" style="font-size: 0.7rem;">100%</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 테두리 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-slide-button-border-color" 
                               name="block_slide[${itemIndex}][buttons][${buttonIndex}][border_color]" 
                               value="#007bff">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 테두리 두께 (px)</label>
                        <input type="number" 
                               class="form-control block-slide-button-border-width" 
                               name="block_slide[${itemIndex}][buttons][${buttonIndex}][border_width]" 
                               value="2" 
                               min="0" 
                               max="10" 
                               step="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">호버 배경 타입</label>
                    <select class="form-select block-slide-button-hover-background-type" 
                            name="block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_type]"
                            onchange="handleButtonHoverBackgroundTypeChange(this)">
                        <option value="color">컬러</option>
                        <option value="gradient">그라데이션</option>
                    </select>
                </div>
                <div class="row block-slide-button-hover-color-container">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-slide-button-hover-background-color" 
                               name="block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_color]" 
                               value="#0056b3">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-slide-button-hover-text-color" 
                               name="block_slide[${itemIndex}][buttons][${buttonIndex}][hover_text_color]" 
                               value="#ffffff">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 테두리 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-slide-button-hover-border-color" 
                               name="block_slide[${itemIndex}][buttons][${buttonIndex}][hover_border_color]" 
                               value="#0056b3">
                    </div>
                </div>
                <div class="row block-slide-button-hover-gradient-container" style="display: none;">
                    <div class="col-12 mb-3">
                        <label class="form-label">호버 그라데이션 설정</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div id="block_slide_${itemIndex}_button_${buttonIndex}_hover_gradient_preview" 
                                 class="block-slide-button-hover-gradient-preview"
                                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #0056b3, #004085);"
                                 onclick="openButtonGradientModal('block_slide_${itemIndex}_button_${buttonIndex}_hover')"
                                 title="그라데이션 설정">
                            </div>
                            <input type="hidden" 
                                   class="block-slide-button-hover-gradient-start" 
                                   id="block_slide_${itemIndex}_button_${buttonIndex}_hover_gradient_start"
                                   name="block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_gradient_start]" 
                                   value="#0056b3">
                            <input type="hidden" 
                                   class="block-slide-button-hover-gradient-end" 
                                   id="block_slide_${itemIndex}_button_${buttonIndex}_hover_gradient_end"
                                   name="block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_gradient_end]" 
                                   value="#004085">
                            <input type="hidden" 
                                   class="block-slide-button-hover-gradient-angle" 
                                   id="block_slide_${itemIndex}_button_${buttonIndex}_hover_gradient_angle"
                                   name="block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_gradient_angle]" 
                                   value="90">
                        </div>
                        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">호버 투명도</label>
                    <input type="range" 
                           class="form-range block-slide-button-hover-opacity" 
                           name="block_slide[${itemIndex}][buttons][${buttonIndex}][hover_opacity]" 
                           id="block_slide_${itemIndex}_button_${buttonIndex}_hover_opacity"
                           value="100" 
                           min="0" 
                           max="100" 
                           step="1"
                           onchange="document.getElementById('block_slide_${itemIndex}_button_${buttonIndex}_hover_opacity_value').textContent = this.value + '%'">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted" style="font-size: 0.7rem;">0%</small>
                        <small class="text-muted" id="block_slide_${itemIndex}_button_${buttonIndex}_hover_opacity_value" style="font-size: 0.7rem;">100%</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', buttonHtml);
    customPageBlockSlideButtonIndices[itemIndex]++;
    
    // 버튼이 추가되면 연결 링크 필드 숨기기
    const linkContainer = document.getElementById(`block_slide_${itemIndex}_link_container`);
    if (linkContainer) {
        linkContainer.style.display = 'none';
    }
}

function removeCustomPageBlockSlideButton(buttonId, itemIndex) {
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

function handleBlockSlideBackgroundTypeChange(itemIndex) {
    const backgroundType = document.querySelector(`#block_slide_item_${itemIndex}_body .block-slide-background-type`)?.value;
    const colorContainer = document.getElementById(`block_slide_${itemIndex}_color_container`);
    const gradientContainer = document.getElementById(`block_slide_${itemIndex}_gradient_container`);
    if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'block';
        if (gradientContainer) gradientContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'block';
    } else {
        // none
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
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
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input image-slide-text-overlay" type="checkbox" id="image_slide_${itemIndex}_text_overlay" onchange="toggleImageSlideTextOverlay(${itemIndex})">
                <label class="form-check-label" for="image_slide_${itemIndex}_text_overlay">이미지 위 텍스트 활성화</label>
            </div>
        </div>
        <div id="image_slide_${itemIndex}_text_overlay_container" style="display: none;">
            <div class="mb-3">
                <label class="form-label">제목</label>
                <input type="text" class="form-control image-slide-title" placeholder="제목을 입력하세요">
            </div>
            <div class="mb-3">
                <label class="form-label">제목 폰트 크기 (px)</label>
                <input type="number" class="form-control image-slide-title-font-size" value="24" min="10" max="100">
            </div>
            <div class="mb-3">
                <label class="form-label">내용</label>
                <textarea class="form-control image-slide-content" rows="3" placeholder="내용을 입력하세요"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">내용 폰트 크기 (px)</label>
                <input type="number" class="form-control image-slide-content-font-size" value="16" min="10" max="100">
            </div>
            <div class="mb-3">
                <label class="form-label">제목과 내용 사이 여백 (px)</label>
                <input type="number" class="form-control image-slide-title-content-gap" value="10" min="0" max="100">
            </div>
            <div class="mb-3">
                <label class="form-label">패딩</label>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small">좌 (px)</label>
                        <input type="number" class="form-control image-slide-text-padding-left" value="0" min="0" max="200">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">우 (px)</label>
                        <input type="number" class="form-control image-slide-text-padding-right" value="0" min="0" max="200">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">상 (px)</label>
                        <input type="number" class="form-control image-slide-text-padding-top" value="0" min="0" max="200">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">하 (px)</label>
                        <input type="number" class="form-control image-slide-text-padding-bottom" value="0" min="0" max="200">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">수평 정렬</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check image-slide-align-h" name="image_slide_${itemIndex}_align_h" id="image_slide_${itemIndex}_align_left" value="left" checked>
                    <label class="btn btn-outline-primary" for="image_slide_${itemIndex}_align_left"><i class="bi bi-text-left"></i> 좌측</label>
                    <input type="radio" class="btn-check image-slide-align-h" name="image_slide_${itemIndex}_align_h" id="image_slide_${itemIndex}_align_center" value="center">
                    <label class="btn btn-outline-primary" for="image_slide_${itemIndex}_align_center"><i class="bi bi-text-center"></i> 중앙</label>
                    <input type="radio" class="btn-check image-slide-align-h" name="image_slide_${itemIndex}_align_h" id="image_slide_${itemIndex}_align_right" value="right">
                    <label class="btn btn-outline-primary" for="image_slide_${itemIndex}_align_right"><i class="bi bi-text-right"></i> 우측</label>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">수직 정렬</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check image-slide-align-v" name="image_slide_${itemIndex}_align_v" id="image_slide_${itemIndex}_align_top" value="top">
                    <label class="btn btn-outline-primary" for="image_slide_${itemIndex}_align_top"><i class="bi bi-align-top"></i> 상단</label>
                    <input type="radio" class="btn-check image-slide-align-v" name="image_slide_${itemIndex}_align_v" id="image_slide_${itemIndex}_align_middle" value="middle" checked>
                    <label class="btn btn-outline-primary" for="image_slide_${itemIndex}_align_middle"><i class="bi bi-align-middle"></i> 중앙</label>
                    <input type="radio" class="btn-check image-slide-align-v" name="image_slide_${itemIndex}_align_v" id="image_slide_${itemIndex}_align_bottom" value="bottom">
                    <label class="btn btn-outline-primary" for="image_slide_${itemIndex}_align_bottom"><i class="bi bi-align-bottom"></i> 하단</label>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">텍스트 색상</label>
                <input type="color" class="form-control form-control-color image-slide-text-color" value="#ffffff" title="텍스트 색상 선택">
        </div>
        <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input image-slide-has-button" type="checkbox" id="image_slide_${itemIndex}_has_button" onchange="toggleImageSlideButton(${itemIndex})">
                    <label class="form-check-label" for="image_slide_${itemIndex}_has_button">버튼 추가</label>
                </div>
            </div>
            <div id="image_slide_${itemIndex}_button_container" style="display: none;">
                <div class="mb-3">
                    <label class="form-label">버튼 텍스트</label>
                    <input type="text" class="form-control image-slide-button-text" placeholder="자세히 보기">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 링크</label>
                    <input type="url" class="form-control image-slide-button-link" placeholder="https://example.com">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input image-slide-button-new-tab" type="checkbox" id="image_slide_${itemIndex}_button_new_tab">
                        <label class="form-check-label" for="image_slide_${itemIndex}_button_new_tab">새창에서 열기</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 색상</label>
                    <input type="color" class="form-control form-control-color image-slide-button-color" value="#0d6efd" title="버튼 색상 선택">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 텍스트 색상</label>
                    <input type="color" class="form-control form-control-color image-slide-button-text-color" value="#ffffff" title="버튼 텍스트 색상 선택">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 테두리 색상</label>
                    <input type="color" class="form-control form-control-color image-slide-button-border-color" value="#0d6efd" title="버튼 테두리 색상 선택">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 배경 투명도</label>
                    <input type="number" class="form-control image-slide-button-opacity" min="0" max="100" value="100">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 호버 배경 색상</label>
                    <input type="color" class="form-control form-control-color image-slide-button-hover-bg-color" value="#0b5ed7" title="버튼 호버 배경 색상 선택">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 호버 텍스트 색상</label>
                    <input type="color" class="form-control form-control-color image-slide-button-hover-text-color" value="#ffffff" title="버튼 호버 텍스트 색상 선택">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 호버 테두리 색상</label>
                    <input type="color" class="form-control form-control-color image-slide-button-hover-border-color" value="#0a58ca" title="버튼 호버 테두리 색상 선택">
                </div>
            </div>
        </div>
        <div class="mb-3" id="image_slide_${itemIndex}_link_container"><label class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
            <input type="url" class="form-control image-slide-link" name="image_slide[${itemIndex}][link]" placeholder="https://example.com">
        </div>
        <div class="mb-3" id="image_slide_${itemIndex}_new_tab_container">
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

// 커스텀 페이지 이미지 위젯 텍스트 오버레이 토글 함수
function toggleEditCustomPageWidgetImageTextOverlay() {
    const checkbox = document.getElementById('edit_custom_page_widget_image_text_overlay');
    const container = document.getElementById('edit_custom_page_widget_image_text_overlay_container');
    const linkContainer = document.querySelector('#editCustomPageWidgetForm .mb-3:nth-of-type(3)');
    const newTabContainer = document.querySelector('#editCustomPageWidgetForm .mb-3:nth-of-type(4)');
    const hasButtonCheckbox = document.getElementById('edit_custom_page_widget_image_has_button');
    
    // 이미지 위젯에서 불필요한 옵션 완전히 제거
    const elementsToRemove = [
        'edit_custom_page_widget_sort_order_container',
        'edit_custom_page_widget_marquee_direction_container',
        'edit_custom_page_widget_gallery_container',
        'edit_custom_page_widget_gallery_display_type_container',
        'edit_custom_page_widget_gallery_grid_container',
        'edit_custom_page_widget_gallery_slide_container',
        'edit_custom_page_widget_gallery_show_title_container',
        'edit_custom_page_widget_image_slide_speed_container',
        'edit_custom_page_widget_custom_html_container',
        'edit_custom_page_widget_block_container'
    ];
    elementsToRemove.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.remove();
    });
    
    if (checkbox && container) {
        container.style.display = checkbox.checked ? 'block' : 'none';
        // 텍스트 오버레이가 활성화되고 버튼이 추가되면 링크 입력 숨김
        if (checkbox.checked && hasButtonCheckbox && hasButtonCheckbox.checked) {
            if (linkContainer) linkContainer.style.display = 'none';
            if (newTabContainer) newTabContainer.style.display = 'none';
        } else {
            if (linkContainer) linkContainer.style.display = 'block';
            if (newTabContainer) newTabContainer.style.display = 'block';
        }
    }
}

// 커스텀 페이지 이미지 위젯 버튼 토글 함수
function toggleEditCustomPageWidgetImageButton() {
    const checkbox = document.getElementById('edit_custom_page_widget_image_has_button');
    const container = document.getElementById('edit_custom_page_widget_image_button_container');
    const linkContainer = document.querySelector('#editCustomPageWidgetForm .mb-3:nth-of-type(3)');
    const newTabContainer = document.querySelector('#editCustomPageWidgetForm .mb-3:nth-of-type(4)');
    
    if (checkbox && container) {
        container.style.display = checkbox.checked ? 'block' : 'none';
        // 버튼이 추가되면 링크 입력 숨김
        if (checkbox.checked) {
            if (linkContainer) linkContainer.style.display = 'none';
            if (newTabContainer) newTabContainer.style.display = 'none';
        } else {
            const textOverlayCheckbox = document.getElementById('edit_custom_page_widget_image_text_overlay');
            // 텍스트 오버레이가 비활성화되어 있으면 링크 입력 표시
            if (!textOverlayCheckbox || !textOverlayCheckbox.checked) {
                if (linkContainer) linkContainer.style.display = 'block';
                if (newTabContainer) newTabContainer.style.display = 'block';
            }
        }
    }
}

function toggleImageSlideTextOverlay(itemIndex) {
    const checkbox = document.getElementById(`image_slide_${itemIndex}_text_overlay`);
    const container = document.getElementById(`image_slide_${itemIndex}_text_overlay_container`);
    const linkContainer = document.getElementById(`image_slide_${itemIndex}_link_container`);
    const newTabContainer = document.getElementById(`image_slide_${itemIndex}_new_tab_container`);
    const hasButtonCheckbox = document.getElementById(`image_slide_${itemIndex}_has_button`);
    
    if (checkbox && container) {
        container.style.display = checkbox.checked ? 'block' : 'none';
        if (checkbox.checked && hasButtonCheckbox && hasButtonCheckbox.checked) {
            if (linkContainer) linkContainer.style.display = 'none';
            if (newTabContainer) newTabContainer.style.display = 'none';
        } else {
            if (linkContainer) linkContainer.style.display = 'block';
            if (newTabContainer) newTabContainer.style.display = 'block';
        }
    }
}

function toggleImageSlideButton(itemIndex) {
    const checkbox = document.getElementById(`image_slide_${itemIndex}_has_button`);
    const container = document.getElementById(`image_slide_${itemIndex}_button_container`);
    const linkContainer = document.getElementById(`image_slide_${itemIndex}_link_container`);
    const newTabContainer = document.getElementById(`image_slide_${itemIndex}_new_tab_container`);
    
    if (checkbox && container) {
        container.style.display = checkbox.checked ? 'block' : 'none';
        if (checkbox.checked) {
            if (linkContainer) linkContainer.style.display = 'none';
            if (newTabContainer) newTabContainer.style.display = 'none';
        } else {
            if (linkContainer) linkContainer.style.display = 'block';
            if (newTabContainer) newTabContainer.style.display = 'block';
        }
    }
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

// 커스텀 페이지 위젯 수정 관련 함수들
let editCustomPageTabMenuIndex = 0;
let editCustomPageBlockSlideItemIndex = 0;
let editCustomPageImageSlideItemIndex = 0;

function saveCustomPageWidgetSettings() {
    const form = document.getElementById('editCustomPageWidgetForm');
    const formData = new FormData(form);
    const widgetId = document.getElementById('edit_custom_page_widget_id').value;
    
    // 위젯 타입 가져오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    const widgetType = widgetItem ? widgetItem.dataset.widgetType : '';
    
    // is_active 값 처리
    const isActiveCheckbox = document.getElementById('edit_custom_page_widget_is_active');
    formData.set('is_active', isActiveCheckbox.checked ? '1' : '0');
    
    // 제목 처리: 갤러리 위젯의 경우 제목이 없으면 빈 문자열로 설정
    const titleInput = document.getElementById('edit_custom_page_widget_title');
    if (widgetType === 'gallery' && titleInput) {
        const titleValue = titleInput.value.trim();
        if (!titleValue || titleValue === '') {
            formData.set('title', '');
        }
    }
    
    // settings 객체 생성
    const settings = {};
    if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
        const limit = document.getElementById('edit_custom_page_widget_limit')?.value;
        if (limit) {
            settings.limit = parseInt(limit);
        }
    } else if (widgetType === 'board') {
        const boardId = document.getElementById('edit_custom_page_widget_board_id')?.value;
        const limit = document.getElementById('edit_custom_page_widget_limit')?.value;
        const sortOrder = document.getElementById('edit_custom_page_widget_sort_order')?.value;
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (limit) {
            settings.limit = parseInt(limit);
        }
        if (sortOrder) {
            settings.sort_order = sortOrder;
        }
    } else if (widgetType === 'board_viewer') {
        const boardId = document.getElementById('edit_custom_page_widget_board_id')?.value;
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        const noBackground = document.getElementById('edit_custom_page_widget_board_viewer_no_background')?.checked;
        settings.no_background = noBackground || false;
    } else if (widgetType === 'marquee_board') {
        const boardId = document.getElementById('edit_custom_page_widget_board_id')?.value;
        const limit = document.getElementById('edit_custom_page_widget_limit')?.value;
        const sortOrder = document.getElementById('edit_custom_page_widget_sort_order')?.value;
        const directionRadio = document.querySelector('input[name="edit_custom_page_direction"]:checked');
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
        const boardId = document.getElementById('edit_custom_page_widget_gallery_board_id')?.value;
        const displayType = document.getElementById('edit_custom_page_widget_gallery_display_type')?.value;
        const showTitle = document.getElementById('edit_custom_page_widget_gallery_show_title')?.checked;
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (displayType) {
            settings.display_type = displayType;
        }
        settings.show_title = showTitle;
        if (displayType === 'grid') {
            const cols = document.getElementById('edit_custom_page_widget_gallery_cols')?.value;
            const rows = document.getElementById('edit_custom_page_widget_gallery_rows')?.value;
            if (cols) {
                settings.cols = parseInt(cols);
            }
            if (rows) {
                settings.rows = parseInt(rows);
            }
            settings.limit = parseInt(cols) * parseInt(rows);
        } else if (displayType === 'slide') {
            const slideCols = document.getElementById('edit_custom_page_widget_gallery_slide_cols')?.value;
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
        const tabMenuItems = document.querySelectorAll('#edit_main_tab_menu_list .edit-custom-page-tab-menu-item');
        tabMenuItems.forEach((item) => {
            const nameInput = item.querySelector('.edit-custom-page-tab-menu-name');
            const widgetTypeSelect = item.querySelector('.edit-custom-page-tab-menu-widget-type');
            const limitInput = item.querySelector('.edit-custom-page-tab-menu-limit');
            const boardSelect = item.querySelector('.edit-custom-page-tab-menu-board-id');
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
        // 토글 메뉴 위젯은 기본 제목으로 저장
        if (!formData.get('title') || formData.get('title') === '') {
            formData.set('title', '토글 메뉴');
        }
        // 토글 메뉴 ID 수집
        const toggleMenuSelect = document.getElementById('edit_custom_page_widget_toggle_menu_ids');
        if (toggleMenuSelect) {
            const selectedOptions = Array.from(toggleMenuSelect.selectedOptions);
            settings.toggle_menu_ids = selectedOptions.map(option => parseInt(option.value));
        }
    } else if (widgetType === 'user_ranking') {
        const rankRanking = document.getElementById('edit_custom_page_widget_rank_ranking')?.checked;
        const pointRanking = document.getElementById('edit_custom_page_widget_point_ranking')?.checked;
        const rankingLimit = document.getElementById('edit_custom_page_widget_ranking_limit')?.value;
        settings.enable_rank_ranking = rankRanking || false;
        settings.enable_point_ranking = pointRanking || false;
        if (rankingLimit) {
            settings.ranking_limit = parseInt(rankingLimit);
        }
    } else if (widgetType === 'custom_html') {
        const customHtml = document.getElementById('edit_custom_page_widget_custom_html')?.value;
        if (customHtml) {
            settings.html = customHtml;
            settings.custom_html = customHtml;
        }
    } else if (widgetType === 'contact_form') {
        const contactFormId = document.getElementById('edit_custom_page_widget_contact_form_id')?.value;
        if (contactFormId) {
            settings.contact_form_id = parseInt(contactFormId);
        }
    } else if (widgetType === 'map') {
        const mapId = document.getElementById('edit_custom_page_widget_map_id')?.value;
        if (mapId) {
            settings.map_id = parseInt(mapId);
        }
    } else if (widgetType === 'block') {
        const blockTitle = document.getElementById('edit_custom_page_widget_block_title')?.value;
        const blockContent = document.getElementById('edit_custom_page_widget_block_content')?.value;
        const textAlignRadio = document.querySelector('input[name="edit_custom_page_block_text_align"]:checked');
        const textAlign = textAlignRadio ? textAlignRadio.value : 'left';
        const backgroundType = document.getElementById('edit_custom_page_widget_block_background_type')?.value || 'color';
        const paddingTop = document.getElementById('edit_custom_page_widget_block_padding_top')?.value;
        
        // 블록 이미지 데이터 수집
        const enableImage = document.getElementById('edit_custom_page_widget_block_enable_image')?.checked || false;
        const blockImageFile = document.getElementById('edit_custom_page_widget_block_image')?.files[0];
        const blockImageUrl = document.getElementById('edit_custom_page_widget_block_image_url')?.value || '';
        
        // enable_image 값을 항상 설정 (이미지 삭제 시 false로 저장되어야 함)
        settings.enable_image = enableImage;
        
        if (enableImage) {
            if (blockImageFile) {
                formData.append('block_image_file', blockImageFile);
            }
            if (blockImageUrl) {
                settings.block_image_url = blockImageUrl;
            }
            // 이미지 패딩 데이터 수집
            const blockImagePaddingTop = document.getElementById('edit_custom_page_widget_block_image_padding_top')?.value || 0;
            const blockImagePaddingBottom = document.getElementById('edit_custom_page_widget_block_image_padding_bottom')?.value || 0;
            const blockImagePaddingLeft = document.getElementById('edit_custom_page_widget_block_image_padding_left')?.value || 0;
            const blockImagePaddingRight = document.getElementById('edit_custom_page_widget_block_image_padding_right')?.value || 0;
            settings.block_image_padding_top = blockImagePaddingTop !== '' && blockImagePaddingTop !== null ? parseInt(blockImagePaddingTop) : 0;
            settings.block_image_padding_bottom = blockImagePaddingBottom !== '' && blockImagePaddingBottom !== null ? parseInt(blockImagePaddingBottom) : 0;
            settings.block_image_padding_left = blockImagePaddingLeft !== '' && blockImagePaddingLeft !== null ? parseInt(blockImagePaddingLeft) : 0;
            settings.block_image_padding_right = blockImagePaddingRight !== '' && blockImagePaddingRight !== null ? parseInt(blockImagePaddingRight) : 0;
        } else {
            // 이미지 비활성화 시 이미지 URL도 초기화
            settings.block_image_url = '';
        }
        const paddingBottom = document.getElementById('edit_custom_page_widget_block_padding_bottom')?.value;
        const paddingLeft = document.getElementById('edit_custom_page_widget_block_padding_left')?.value;
        const paddingRight = document.getElementById('edit_custom_page_widget_block_padding_right')?.value;
        const titleContentGap = document.getElementById('edit_custom_page_widget_block_title_content_gap')?.value;
        const blockLink = document.getElementById('edit_custom_page_widget_block_link')?.value;
        const openNewTab = document.getElementById('edit_custom_page_widget_block_open_new_tab')?.checked;
        const titleColor = document.getElementById('edit_custom_page_widget_block_title_color')?.value || '#ffffff';
        const contentColor = document.getElementById('edit_custom_page_widget_block_content_color')?.value || '#ffffff';
        const titleFontSize = document.getElementById('edit_custom_page_widget_block_title_font_size')?.value || '16';
        const contentFontSize = document.getElementById('edit_custom_page_widget_block_content_font_size')?.value || '14';
        const buttonTopMargin = document.getElementById('edit_custom_page_widget_block_button_top_margin')?.value;
        // 버튼 데이터 수집
        const buttons = [];
        const buttonInputs = document.querySelectorAll('.edit-custom-page-block-button-text');
        buttonInputs.forEach((input, index) => {
            const buttonText = input.value || '';
            if (buttonText) {
                const buttonCard = input.closest('.card');
                const buttonLink = buttonCard.querySelector('.edit-custom-page-block-button-link')?.value || '';
                const buttonOpenNewTab = buttonCard.querySelector('.edit-custom-page-block-button-open-new-tab')?.checked || false;
                const buttonBackgroundColor = buttonCard.querySelector('.edit-custom-page-block-button-background-color')?.value || '#007bff';
                const buttonTextColor = buttonCard.querySelector('.edit-custom-page-block-button-text-color')?.value || '#ffffff';
                const buttonBorderColor = buttonCard.querySelector('.edit-custom-page-block-button-border-color')?.value || buttonBackgroundColor;
                const buttonBorderWidth = buttonCard.querySelector('.edit-custom-page-block-button-border-width')?.value || '2';
                const buttonHoverBackgroundColor = buttonCard.querySelector('.edit-custom-page-block-button-hover-background-color')?.value || '#0056b3';
                const buttonHoverTextColor = buttonCard.querySelector('.edit-custom-page-block-button-hover-text-color')?.value || '#ffffff';
                const buttonHoverBorderColor = buttonCard.querySelector('.edit-custom-page-block-button-hover-border-color')?.value || '#0056b3';
                
                // 배경 타입 및 그라데이션 설정
                const buttonBackgroundType = buttonCard.querySelector('.edit-custom-page-block-button-background-type')?.value || 'color';
                const buttonGradientStart = buttonCard.querySelector('.edit-custom-page-block-button-gradient-start')?.value || buttonBackgroundColor;
                const buttonGradientEnd = buttonCard.querySelector('.edit-custom-page-block-button-gradient-end')?.value || buttonHoverBackgroundColor;
                const buttonGradientAngle = buttonCard.querySelector('.edit-custom-page-block-button-gradient-angle')?.value || '90';
                const buttonOpacityRaw = buttonCard.querySelector('.edit-custom-page-block-button-opacity')?.value || '100';
                const buttonOpacity = parseFloat(buttonOpacityRaw) / 100;
                
                // 호버 배경 타입 및 그라데이션 설정
                const buttonHoverBackgroundType = buttonCard.querySelector('.edit-custom-page-block-button-hover-background-type')?.value || 'color';
                const buttonHoverGradientStart = buttonCard.querySelector('.edit-custom-page-block-button-hover-gradient-start')?.value || buttonHoverBackgroundColor;
                const buttonHoverGradientEnd = buttonCard.querySelector('.edit-custom-page-block-button-hover-gradient-end')?.value || buttonHoverBackgroundColor;
                const buttonHoverGradientAngle = buttonCard.querySelector('.edit-custom-page-block-button-hover-gradient-angle')?.value || '90';
                const buttonHoverOpacityRaw = buttonCard.querySelector('.edit-custom-page-block-button-hover-opacity')?.value || '100';
                const buttonHoverOpacity = parseFloat(buttonHoverOpacityRaw) / 100;
                
                buttons.push({
                    text: buttonText,
                    link: buttonLink,
                    open_new_tab: buttonOpenNewTab,
                    background_color: buttonBackgroundColor,
                    text_color: buttonTextColor,
                    border_color: buttonBorderColor,
                    border_width: buttonBorderWidth,
                    hover_background_color: buttonHoverBackgroundColor,
                    hover_text_color: buttonHoverTextColor,
                    hover_border_color: buttonHoverBorderColor,
                    background_type: buttonBackgroundType,
                    background_gradient_start: buttonGradientStart,
                    background_gradient_end: buttonGradientEnd,
                    background_gradient_angle: parseInt(buttonGradientAngle) || 90,
                    opacity: buttonOpacity,
                    hover_background_type: buttonHoverBackgroundType,
                    hover_background_gradient_start: buttonHoverGradientStart,
                    hover_background_gradient_end: buttonHoverGradientEnd,
                    hover_background_gradient_angle: parseInt(buttonHoverGradientAngle) || 90,
                    hover_opacity: buttonHoverOpacity
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
        settings.title_color = titleColor;
        settings.content_color = contentColor;
        settings.title_font_size = titleFontSize;
        settings.content_font_size = contentFontSize;
        settings.buttons = buttons;
        settings.button_top_margin = buttonTopMargin !== '' && buttonTopMargin !== null && buttonTopMargin !== undefined ? parseInt(buttonTopMargin) : 12;
        
        // 디버깅: 버튼 투명도 값 확인
        if (buttons.length > 0) {
            console.log('Block widget buttons with opacity:', buttons.map(b => ({ text: b.text, opacity: b.opacity, hover_opacity: b.hover_opacity })));
        }
        
        if (backgroundType === 'color') {
            const backgroundColor = document.getElementById('edit_custom_page_widget_block_background_color')?.value || '#007bff';
            const backgroundColorAlphaValue = document.getElementById('edit_custom_page_widget_block_background_color_alpha')?.value;
            const backgroundColorAlpha = backgroundColorAlphaValue !== '' && backgroundColorAlphaValue !== null ? parseInt(backgroundColorAlphaValue) : 100;
            settings.background_color = backgroundColor;
            settings.background_color_alpha = backgroundColorAlpha;
        } else if (backgroundType === 'gradient') {
            // 두 가지 필드명 모두 확인
            const gradientStart = document.getElementById('edit_custom_page_widget_block_gradient_start')?.value || 
                                document.getElementById('edit_custom_page_widget_block_background_gradient_start')?.value || '#ffffff';
            const gradientEnd = document.getElementById('edit_custom_page_widget_block_gradient_end')?.value || 
                              document.getElementById('edit_custom_page_widget_block_background_gradient_end')?.value || '#000000';
            const gradientAngle = document.getElementById('edit_custom_page_widget_block_gradient_angle')?.value || 
                                document.getElementById('edit_custom_page_widget_block_background_gradient_angle')?.value || '90';
            settings.background_gradient_start = gradientStart;
            settings.background_gradient_end = gradientEnd;
            settings.background_gradient_angle = parseInt(gradientAngle);
        } else if (backgroundType === 'image') {
            const imageFile = document.getElementById('edit_custom_page_widget_block_image_input')?.files[0];
            if (imageFile) {
                formData.append('block_background_image_file', imageFile);
            }
            const imageUrl = document.getElementById('edit_custom_page_widget_block_background_image')?.value;
            if (imageUrl) {
                settings.background_image_url = imageUrl;
            }
            const imageAlphaValue = document.getElementById('edit_custom_page_widget_block_background_image_alpha')?.value;
            const imageAlpha = imageAlphaValue !== '' && imageAlphaValue !== null ? parseInt(imageAlphaValue) : 100;
            settings.background_image_alpha = imageAlpha;
            const imageFullWidth = document.getElementById('edit_custom_page_widget_block_background_image_full_width')?.checked;
            settings.background_image_full_width = imageFullWidth || false;
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
        const slideDirection = document.querySelector('input[name="edit_custom_page_block_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        // 슬라이드 유지 시간
        const slideHoldTime = parseFloat(document.getElementById('edit_custom_page_block_slide_hold_time')?.value) || 3;
        settings.slide_hold_time = slideHoldTime;
        
        // 블록 아이템들 수집
        const blockItems = [];
        const blockSlideItems = document.querySelectorAll('.edit-custom-page-block-slide-item');
        blockSlideItems.forEach((item) => {
            const itemIndex = item.dataset.itemIndex;
            const titleInput = item.querySelector('.edit-custom-page-block-slide-title');
            const contentInput = item.querySelector('.edit-custom-page-block-slide-content');
            const textAlignRadio = item.querySelector(`input[name="edit_custom_page_block_slide[${itemIndex}][text_align]"]:checked`);
            const backgroundTypeSelect = item.querySelector('.edit-custom-page-block-slide-background-type');
            const paddingTopInput = item.querySelector('.edit-custom-page-block-slide-padding-top');
            const paddingBottomInput = item.querySelector('.edit-custom-page-block-slide-padding-bottom');
            const paddingLeftInput = item.querySelector('.edit-custom-page-block-slide-padding-left');
            const paddingRightInput = item.querySelector('.edit-custom-page-block-slide-padding-right');
            const titleContentGapInput = item.querySelector('.edit-custom-page-block-slide-title-content-gap');
            const buttonTopMarginInput = item.querySelector('.edit-custom-page-block-slide-button-top-margin');
            const linkInput = item.querySelector('.edit-custom-page-block-slide-link');
            const openNewTabCheckbox = item.querySelector('.edit-custom-page-block-slide-open-new-tab');
            const titleColorInput = item.querySelector('.edit-custom-page-block-slide-title-color');
            const contentColorInput = item.querySelector('.edit-custom-page-block-slide-content-color');
            const titleFontSizeInput = item.querySelector('.edit-custom-page-block-slide-title-font-size');
            const contentFontSizeInput = item.querySelector('.edit-custom-page-block-slide-content-font-size');
            // 버튼 데이터 수집
            const buttons = [];
            const buttonInputs = item.querySelectorAll('.edit-custom-page-block-slide-button-text');
            buttonInputs.forEach((input) => {
                const buttonText = input.value || '';
                if (buttonText) {
                    const buttonCard = input.closest('.card');
                    const buttonLink = buttonCard.querySelector('.edit-custom-page-block-slide-button-link')?.value || '';
                    const buttonOpenNewTab = buttonCard.querySelector('.edit-custom-page-block-slide-button-open-new-tab')?.checked || false;
                    const buttonBackgroundColor = buttonCard.querySelector('.edit-custom-page-block-slide-button-background-color')?.value || '#007bff';
                    const buttonTextColor = buttonCard.querySelector('.edit-custom-page-block-slide-button-text-color')?.value || '#ffffff';
                    const buttonBorderColor = buttonCard.querySelector('.edit-custom-page-block-slide-button-border-color')?.value || buttonBackgroundColor;
                    const buttonBorderWidth = buttonCard.querySelector('.edit-custom-page-block-slide-button-border-width')?.value || '2';
                    const buttonHoverBackgroundColor = buttonCard.querySelector('.edit-custom-page-block-slide-button-hover-background-color')?.value || '#0056b3';
                    const buttonHoverTextColor = buttonCard.querySelector('.edit-custom-page-block-slide-button-hover-text-color')?.value || '#ffffff';
                    const buttonHoverBorderColor = buttonCard.querySelector('.edit-custom-page-block-slide-button-hover-border-color')?.value || '#0056b3';
                    
                    // 배경 타입 및 그라데이션 설정
                    const buttonBackgroundType = buttonCard.querySelector('.edit-custom-page-block-slide-button-background-type')?.value || 'color';
                    const buttonGradientStart = buttonCard.querySelector('.edit-custom-page-block-slide-button-gradient-start')?.value || buttonBackgroundColor;
                    const buttonGradientEnd = buttonCard.querySelector('.edit-custom-page-block-slide-button-gradient-end')?.value || buttonHoverBackgroundColor;
                    const buttonGradientAngle = buttonCard.querySelector('.edit-custom-page-block-slide-button-gradient-angle')?.value || '90';
                    const buttonOpacityRaw = buttonCard.querySelector('.edit-custom-page-block-slide-button-opacity')?.value || '100';
                    const buttonOpacity = parseFloat(buttonOpacityRaw) / 100;
                    
                    // 호버 배경 타입 및 그라데이션 설정
                    const buttonHoverBackgroundType = buttonCard.querySelector('.edit-custom-page-block-slide-button-hover-background-type')?.value || 'color';
                    const buttonHoverGradientStart = buttonCard.querySelector('.edit-custom-page-block-slide-button-hover-gradient-start')?.value || buttonHoverBackgroundColor;
                    const buttonHoverGradientEnd = buttonCard.querySelector('.edit-custom-page-block-slide-button-hover-gradient-end')?.value || buttonHoverBackgroundColor;
                    const buttonHoverGradientAngle = buttonCard.querySelector('.edit-custom-page-block-slide-button-hover-gradient-angle')?.value || '90';
                    const buttonHoverOpacityRaw = buttonCard.querySelector('.edit-custom-page-block-slide-button-hover-opacity')?.value || '100';
                    const buttonHoverOpacity = parseFloat(buttonHoverOpacityRaw) / 100;
                    
                    buttons.push({
                        text: buttonText,
                        link: buttonLink,
                        open_new_tab: buttonOpenNewTab,
                        background_color: buttonBackgroundColor,
                        text_color: buttonTextColor,
                        border_color: buttonBorderColor,
                        border_width: buttonBorderWidth,
                        hover_background_color: buttonHoverBackgroundColor,
                        hover_text_color: buttonHoverTextColor,
                        hover_border_color: buttonHoverBorderColor,
                        background_type: buttonBackgroundType,
                        background_gradient_start: buttonGradientStart,
                        background_gradient_end: buttonGradientEnd,
                        background_gradient_angle: parseInt(buttonGradientAngle) || 90,
                        opacity: buttonOpacity,
                        hover_background_type: buttonHoverBackgroundType,
                        hover_background_gradient_start: buttonHoverGradientStart,
                        hover_background_gradient_end: buttonHoverGradientEnd,
                        hover_background_gradient_angle: parseInt(buttonHoverGradientAngle) || 90,
                        hover_opacity: buttonHoverOpacity
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
                button_top_margin: buttonTopMarginInput ? parseInt(buttonTopMarginInput.value) : 12,
                link: linkInput ? linkInput.value : '',
                open_new_tab: openNewTabCheckbox ? openNewTabCheckbox.checked : false,
                title_color: titleColorInput ? titleColorInput.value : '#ffffff',
                content_color: contentColorInput ? contentColorInput.value : '#ffffff',
                title_font_size: titleFontSizeInput ? titleFontSizeInput.value : '16',
                content_font_size: contentFontSizeInput ? contentFontSizeInput.value : '14',
                buttons: buttons
            };
            
            if (blockItem.background_type === 'color') {
                const backgroundColorInput = item.querySelector('.edit-custom-page-block-slide-background-color');
                const backgroundColorAlphaInput = item.querySelector('.edit-custom-page-block-slide-background-color-alpha');
                blockItem.background_color = backgroundColorInput ? backgroundColorInput.value : '#007bff';
                blockItem.background_color_alpha = backgroundColorAlphaInput && backgroundColorAlphaInput.value !== '' ? parseInt(backgroundColorAlphaInput.value) : 100;
            } else if (blockItem.background_type === 'gradient') {
                // 두 가지 필드명 모두 확인
                const gradientStartInput = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_gradient_start`) || 
                                        document.getElementById(`edit_custom_page_block_slide_${itemIndex}_background_gradient_start`) ||
                                        item.querySelector(`#edit_custom_page_block_slide_${itemIndex}_gradient_start`);
                const gradientEndInput = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_gradient_end`) || 
                                       document.getElementById(`edit_custom_page_block_slide_${itemIndex}_background_gradient_end`) ||
                                       item.querySelector(`#edit_custom_page_block_slide_${itemIndex}_gradient_end`);
                const gradientAngleInput = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_gradient_angle`) || 
                                          document.getElementById(`edit_custom_page_block_slide_${itemIndex}_background_gradient_angle`) ||
                                          item.querySelector(`#edit_custom_page_block_slide_${itemIndex}_gradient_angle`);
                blockItem.background_gradient_start = gradientStartInput ? gradientStartInput.value : '#ffffff';
                blockItem.background_gradient_end = gradientEndInput ? gradientEndInput.value : '#000000';
                blockItem.background_gradient_angle = gradientAngleInput ? parseInt(gradientAngleInput.value) : 90;
            } else if (blockItem.background_type === 'image') {
                const imageFileInput = item.querySelector(`#edit_custom_page_block_slide_${itemIndex}_image_input`);
                if (imageFileInput && imageFileInput.files[0]) {
                    formData.append(`edit_custom_page_block_slide[${itemIndex}][background_image_file]`, imageFileInput.files[0]);
                }
                const imageUrlInput = item.querySelector(`#edit_custom_page_block_slide_${itemIndex}_background_image_url`);
                if (imageUrlInput && imageUrlInput.value) {
                    blockItem.background_image_url = imageUrlInput.value;
                }
                const imageAlphaInput = item.querySelector(`.edit-custom-page-block-slide-background-image-alpha`);
                blockItem.background_image_alpha = imageAlphaInput && imageAlphaInput.value !== '' ? parseInt(imageAlphaInput.value) : 100;
            }
            
            blockItems.push(blockItem);
        });
        
        settings.blocks = blockItems;
    } else if (widgetType === 'image') {
        const imageFile = document.getElementById('edit_custom_page_widget_image_input')?.files[0];
        if (imageFile) {
            formData.append('image_file', imageFile);
        }
        const imageUrl = document.getElementById('edit_custom_page_widget_image_url')?.value;
        if (imageUrl) {
            settings.image_url = imageUrl;
        }
        // 이미지 width
        const editImageWidth = document.getElementById('edit_custom_page_widget_image_width')?.value || '100';
        settings.image_width = parseInt(editImageWidth) || 100;
        
        const imageLink = document.getElementById('edit_custom_page_widget_image_link')?.value;
        if (imageLink) {
            settings.image_link = imageLink;
        }
        const imageOpenNewTab = document.getElementById('edit_custom_page_widget_image_open_new_tab')?.checked;
        settings.image_open_new_tab = imageOpenNewTab || false;
        
        // 텍스트 오버레이 관련 데이터
        const textOverlay = document.getElementById('edit_custom_page_widget_image_text_overlay')?.checked || false;
        const title = document.getElementById('edit_custom_page_widget_image_title')?.value || '';
        const titleFontSize = document.getElementById('edit_custom_page_widget_image_title_font_size')?.value || '24';
        const content = document.getElementById('edit_custom_page_widget_image_content')?.value || '';
        const contentFontSize = document.getElementById('edit_custom_page_widget_image_content_font_size')?.value || '16';
        const titleContentGap = document.getElementById('edit_custom_page_widget_image_title_content_gap')?.value || '10';
        const textPaddingLeft = document.getElementById('edit_custom_page_widget_image_text_padding_left')?.value || '0';
        const textPaddingRight = document.getElementById('edit_custom_page_widget_image_text_padding_right')?.value || '0';
        const textPaddingTop = document.getElementById('edit_custom_page_widget_image_text_padding_top')?.value || '0';
        const textPaddingBottom = document.getElementById('edit_custom_page_widget_image_text_padding_bottom')?.value || '10';
        const alignH = document.querySelector('input[name="edit_custom_page_widget_image_align_h"]:checked')?.value || 'left';
        const alignV = document.querySelector('input[name="edit_custom_page_widget_image_align_v"]:checked')?.value || 'middle';
        const textColor = document.getElementById('edit_custom_page_widget_image_text_color')?.value || '#ffffff';
        const hasButton = document.getElementById('edit_custom_page_widget_image_has_button')?.checked || false;
        const buttonText = document.getElementById('edit_custom_page_widget_image_button_text')?.value || '';
        const buttonLink = document.getElementById('edit_custom_page_widget_image_button_link')?.value || '';
        const buttonNewTab = document.getElementById('edit_custom_page_widget_image_button_new_tab')?.checked || false;
        const buttonColor = document.getElementById('edit_custom_page_widget_image_button_color')?.value || '#0d6efd';
        const buttonTextColor = document.getElementById('edit_custom_page_widget_image_button_text_color')?.value || '#ffffff';
        const buttonBorderColor = document.getElementById('edit_custom_page_widget_image_button_border_color')?.value || '#0d6efd';
        const buttonOpacity = document.getElementById('edit_custom_page_widget_image_button_opacity')?.value ?? 100;
        const buttonHoverBgColor = document.getElementById('edit_custom_page_widget_image_button_hover_bg_color')?.value || '#0b5ed7';
        const buttonHoverTextColor = document.getElementById('edit_custom_page_widget_image_button_hover_text_color')?.value || '#ffffff';
        const buttonHoverBorderColor = document.getElementById('edit_custom_page_widget_image_button_hover_border_color')?.value || '#0a58ca';
        
        settings.text_overlay = textOverlay;
        settings.title = title;
        settings.title_font_size = parseInt(titleFontSize) || 24;
        settings.content = content;
        settings.content_font_size = parseInt(contentFontSize) || 16;
        settings.title_content_gap = parseInt(titleContentGap) || 10;
        settings.text_padding_left = parseInt(textPaddingLeft) || 0;
        settings.text_padding_right = parseInt(textPaddingRight) || 0;
        settings.text_padding_top = parseInt(textPaddingTop) || 0;
        settings.text_padding_bottom = parseInt(textPaddingBottom) || 10;
        settings.align_h = alignH;
        settings.align_v = alignV;
        settings.text_color = textColor;
        settings.has_button = hasButton;
        settings.button_text = buttonText;
        settings.button_link = buttonLink;
        settings.button_new_tab = buttonNewTab;
        settings.button_color = buttonColor;
        settings.button_text_color = buttonTextColor;
        settings.button_border_color = buttonBorderColor;
        settings.button_opacity = (buttonOpacity !== '' && buttonOpacity !== null && buttonOpacity !== undefined) ? parseInt(buttonOpacity) : 100;
        settings.button_hover_bg_color = buttonHoverBgColor;
        settings.button_hover_text_color = buttonHoverTextColor;
        settings.button_hover_border_color = buttonHoverBorderColor;
    } else if (widgetType === 'image_slide') {
        const slideDirection = document.querySelector('input[name="edit_custom_page_image_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        // 이미지 아이템들 수집
        const imageItems = [];
        const imageSlideItems = document.querySelectorAll('.edit-custom-page-image-slide-item');
        imageSlideItems.forEach((item) => {
            const itemIndex = item.dataset.itemIndex;
            const imageFileInput = item.querySelector(`#edit_custom_page_image_slide_${itemIndex}_image_input`);
            const imageUrlInput = item.querySelector(`#edit_custom_page_image_slide_${itemIndex}_image_url`);
            const linkInput = item.querySelector('.edit-custom-page-image-slide-link');
            const openNewTabCheckbox = item.querySelector('.edit-custom-page-image-slide-open-new-tab');
            
            const imageItem = {
                image_url: imageUrlInput ? imageUrlInput.value : '',
                link: linkInput ? linkInput.value : '',
                open_new_tab: openNewTabCheckbox ? openNewTabCheckbox.checked : false
            };
            
            if (imageFileInput && imageFileInput.files[0]) {
                formData.append(`edit_custom_page_image_slide[${itemIndex}][image_file]`, imageFileInput.files[0]);
            }
            
            imageItems.push(imageItem);
        });
        
        settings.images = imageItems;
    }
    
    // settings를 JSON으로 추가 (빈 객체가 아닌 경우에만)
    if (Object.keys(settings).length > 0) {
        // 디버깅: 저장 전 settings 확인
        console.log('Saving settings:', JSON.stringify(settings, null, 2));
        formData.append('settings', JSON.stringify(settings));
    }
    
    // Laravel의 PUT 메서드를 시뮬레이션하기 위해 _method 필드 추가
    formData.append('_method', 'PUT');
    
    // 모달에서 업데이트 라우트 가져오기
    const modal = document.getElementById('customPageWidgetSettingsModal');
    const updateRoute = modal ? modal.getAttribute('data-update-route').replace(':id', widgetId) : '';
    
    if (!updateRoute) {
        alert('업데이트 라우트를 찾을 수 없습니다.');
        return;
    }
    
    fetch(updateRoute, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text.substring(0, 500));
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || errorData.error || 'Network response was not ok: ' + response.status);
                } catch (e) {
                    throw new Error('Network response was not ok: ' + response.status + ' - ' + text.substring(0, 200));
                }
            });
        }
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.error('Expected JSON but got:', text.substring(0, 200));
                throw new Error('서버에서 JSON 응답을 받지 못했습니다.');
            });
        }
    })
    .then(data => {
        if (data && data.success) {
            // 위젯 설정 모달 닫기
            const settingsModal = bootstrap.Modal.getInstance(document.getElementById('customPageWidgetSettingsModal'));
            if (settingsModal) {
                settingsModal.hide();
            }
            
            // 저장 완료 모달 표시
            const successModalElement = document.getElementById('saveCustomPageWidgetSuccessModal');
            if (successModalElement) {
                // 확인 버튼에 이벤트 리스너 추가
                const confirmButton = successModalElement.querySelector('button.btn-primary');
                if (confirmButton) {
                    // 기존 이벤트 리스너 제거
                    const newConfirmButton = confirmButton.cloneNode(true);
                    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
                    
                    // 새 이벤트 리스너 추가
                    newConfirmButton.addEventListener('click', function() {
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
                // 모달이 없으면 바로 새로고침
                location.reload();
            }
        } else {
            alert('위젯 수정에 실패했습니다: ' + (data?.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('위젯 수정 중 오류가 발생했습니다: ' + error.message);
    });
}

// 탭메뉴 항목 추가 (위젯 수정 폼)
function addEditCustomPageTabMenuItem() {
    const container = document.getElementById('edit_main_tab_menu_list');
    if (!container) return;
    
    // 기존 탭메뉴 항목들 접기
    const existingItems = container.querySelectorAll('.edit-custom-page-tab-menu-item');
    existingItems.forEach((existingItem) => {
        const existingItemIndex = existingItem.dataset.itemIndex;
        const existingBody = document.getElementById(`edit_custom_page_tab_menu_item_${existingItemIndex}_body`);
        const existingIcon = document.getElementById(`edit_custom_page_tab_menu_item_${existingItemIndex}_icon`);
        if (existingBody && existingIcon) {
            existingBody.style.display = 'none';
            existingIcon.className = 'bi bi-chevron-right';
        }
    });
    
    const index = editCustomPageTabMenuIndex++;
    const tabItem = document.createElement('div');
    tabItem.className = 'card mb-2 edit-custom-page-tab-menu-item';
    tabItem.id = `edit_custom_page_tab_menu_item_${index}`;
    tabItem.dataset.itemIndex = index;
    
    // 접기/펼치기 헤더
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleEditCustomPageTabMenuItem(index);
    };
    header.innerHTML = `
        <span>탭메뉴 ${index + 1}</span>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-chevron-down" id="edit_custom_page_tab_menu_item_${index}_icon"></i>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); removeEditCustomPageTabMenuItem(${index})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    // 내용 영역
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `edit_custom_page_tab_menu_item_${index}_body`;
    body.innerHTML = `
        <div class="mb-2">
            <label class="form-label">탭메뉴 이름</label>
            <input type="text" class="form-control edit-custom-page-tab-menu-name" name="tab_menu[${index}][name]" placeholder="탭메뉴 이름을 입력하세요" required>
        </div>
        <div class="mb-2">
            <label class="form-label">위젯 내용</label>
            <select class="form-select edit-custom-page-tab-menu-widget-type" name="tab_menu[${index}][widget_type]" required onchange="handleCustomPageTabMenuWidgetTypeChange(this, ${index})">
                <option value="">선택하세요</option>
                <option value="popular_posts">인기 게시글</option>
                <option value="recent_posts">최근 게시글</option>
                <option value="weekly_popular_posts">주간 인기글</option>
                <option value="monthly_popular_posts">월간 인기글</option>
                <option value="board">게시판</option>
                <option value="notice">공지사항</option>
            </select>
        </div>
        <div class="mb-2 edit-custom-page-tab-menu-board-container" style="display: none;">
            <label class="form-label">게시판 선택</label>
            <select class="form-select edit-custom-page-tab-menu-board-id" name="tab_menu[${index}][board_id]">
                <option value="">선택하세요</option>
                @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                    <option value="{{ $board->id }}">{{ $board->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">표시할 게시글 수</label>
            <input type="number" class="form-control edit-custom-page-tab-menu-limit" name="tab_menu[${index}][limit]" min="1" max="50" value="10" required>
        </div>
    `;
    
    tabItem.appendChild(header);
    tabItem.appendChild(body);
    container.appendChild(tabItem);
    
    // 새로 추가된 아이템은 펼쳐진 상태로 시작
    body.style.display = 'block';
}

function toggleEditCustomPageTabMenuItem(itemIndex) {
    const body = document.getElementById(`edit_custom_page_tab_menu_item_${itemIndex}_body`);
    const icon = document.getElementById(`edit_custom_page_tab_menu_item_${itemIndex}_icon`);
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

function removeEditCustomPageTabMenuItem(index) {
    const item = document.getElementById(`edit_custom_page_tab_menu_item_${index}`);
    if (item) {
        item.remove();
    }
}

function handleCustomPageTabMenuWidgetTypeChange(select, index) {
    const boardContainer = document.querySelector(`#edit_custom_page_tab_menu_item_${index} .edit-custom-page-tab-menu-board-container`);
    if (boardContainer) {
        boardContainer.style.display = select.value === 'board' ? 'block' : 'none';
    }
}

// 블록 슬라이드 항목 추가 (위젯 수정 폼)
function addEditMainBlockSlideItem(blockData = null) {
    const container = document.getElementById('edit_custom_page_widget_block_slide_items');
    if (!container) return;
    
    if (!blockData) {
        const existingItems = container.querySelectorAll('.edit-custom-page-block-slide-item');
        existingItems.forEach((existingItem) => {
            const existingItemIndex = existingItem.dataset.itemIndex;
            const existingBody = document.getElementById(`edit_custom_page_block_slide_item_${existingItemIndex}_body`);
            const existingIcon = document.getElementById(`edit_custom_page_block_slide_item_${existingItemIndex}_icon`);
            if (existingBody && existingIcon) {
                existingBody.style.display = 'none';
                existingIcon.className = 'bi bi-chevron-right';
            }
        });
    }
    
    const itemIndex = editCustomPageBlockSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 edit-custom-page-block-slide-item';
    item.id = `edit_custom_page_block_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleEditMainBlockSlideItem(itemIndex);
    };
    header.innerHTML = `
        <span>블록 ${itemIndex + 1}</span>
        <i class="bi bi-chevron-down" id="edit_custom_page_block_slide_item_${itemIndex}_icon"></i>
    `;
    
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `edit_custom_page_block_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3">
            <label class="form-label">제목</label>
            <textarea class="form-control edit-custom-page-block-slide-title" 
                      name="edit_custom_page_block_slide[${itemIndex}][title]" 
                      rows="2"
                      placeholder="제목을 입력하세요 (엔터로 줄바꿈)">${blockData ? (blockData.title || '') : ''}</textarea>
            <small class="text-muted">엔터 키로 줄바꿈이 가능합니다.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">내용</label>
            <textarea class="form-control edit-custom-page-block-slide-content" 
                      name="edit_custom_page_block_slide[${itemIndex}][content]" 
                      rows="3"
                      placeholder="내용을 입력하세요">${blockData ? (blockData.content || '') : ''}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">텍스트 정렬</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="edit_custom_page_block_slide[${itemIndex}][text_align]" id="edit_custom_page_block_slide_${itemIndex}_align_left" value="left" ${!blockData || blockData.text_align === 'left' ? 'checked' : ''}>
                <label class="btn btn-outline-primary" for="edit_custom_page_block_slide_${itemIndex}_align_left">
                    <i class="bi bi-text-left"></i> 좌
                </label>
                <input type="radio" class="btn-check" name="edit_custom_page_block_slide[${itemIndex}][text_align]" id="edit_custom_page_block_slide_${itemIndex}_align_center" value="center" ${blockData && blockData.text_align === 'center' ? 'checked' : ''}>
                <label class="btn btn-outline-primary" for="edit_custom_page_block_slide_${itemIndex}_align_center">
                    <i class="bi bi-text-center"></i> 중앙
                </label>
                <input type="radio" class="btn-check" name="edit_custom_page_block_slide[${itemIndex}][text_align]" id="edit_custom_page_block_slide_${itemIndex}_align_right" value="right" ${blockData && blockData.text_align === 'right' ? 'checked' : ''}>
                <label class="btn btn-outline-primary" for="edit_custom_page_block_slide_${itemIndex}_align_right">
                    <i class="bi bi-text-right"></i> 우
                </label>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">제목 폰트 사이즈 (px)</label>
            <input type="number" 
                   class="form-control edit-custom-page-block-slide-title-font-size" 
                   name="edit_custom_page_block_slide[${itemIndex}][title_font_size]" 
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
                   class="form-control edit-custom-page-block-slide-content-font-size" 
                   name="edit_custom_page_block_slide[${itemIndex}][content_font_size]" 
                   value="${blockData ? (blockData.content_font_size || '14') : '14'}"
                   min="8"
                   max="48"
                   step="1"
                   placeholder="14">
            <small class="text-muted">기본값: 14px</small>
        </div>
        <div class="mb-3">
            <label class="form-label">배경</label>
            <select class="form-select edit-custom-page-block-slide-background-type" name="edit_custom_page_block_slide[${itemIndex}][background_type]" onchange="handleEditCustomPageBlockSlideBackgroundTypeChange(${itemIndex})">
                <option value="none" ${blockData && blockData.background_type === 'none' ? 'selected' : ''}>배경 없음</option>
                <option value="color" ${!blockData || blockData.background_type === 'color' ? 'selected' : ''}>컬러</option>
                <option value="gradient" ${blockData && blockData.background_type === 'gradient' ? 'selected' : ''}>그라데이션</option>
            </select>
        </div>
        <div class="mb-3 edit-custom-page-block-slide-color-container" id="edit_custom_page_block_slide_${itemIndex}_color_container" style="${!blockData || (blockData.background_type !== 'color' && blockData.background_type !== 'none') ? 'display: none;' : ''}">
            <label class="form-label">배경 컬러</label>
            <input type="color" 
                   class="form-control form-control-color mb-2 edit-custom-page-block-slide-background-color" 
                   name="edit_custom_page_block_slide[${itemIndex}][background_color]" 
                   value="${blockData ? (blockData.background_color || '#007bff') : '#007bff'}">
            <label class="form-label">투명도</label>
            <input type="range" 
                   class="form-range edit-custom-page-block-slide-background-color-alpha" 
                   name="edit_custom_page_block_slide[${itemIndex}][background_color_alpha]"
                   min="0" 
                   max="100" 
                   value="${blockData && blockData.background_color_alpha !== undefined && blockData.background_color_alpha !== null ? blockData.background_color_alpha : 100}"
                   onchange="document.getElementById('edit_custom_page_block_slide_${itemIndex}_background_color_alpha_value').textContent = this.value + '%'">
            <div class="d-flex justify-content-between">
                <small class="text-muted" style="font-size: 0.7rem;">0%</small>
                <small class="text-muted" id="edit_custom_page_block_slide_${itemIndex}_background_color_alpha_value" style="font-size: 0.7rem;">${blockData && blockData.background_color_alpha !== undefined && blockData.background_color_alpha !== null ? blockData.background_color_alpha : 100}%</small>
                <small class="text-muted" style="font-size: 0.7rem;">100%</small>
            </div>
        </div>
        <div class="mb-3 edit-custom-page-block-slide-gradient-container" id="edit_custom_page_block_slide_${itemIndex}_gradient_container" style="${!blockData || blockData.background_type !== 'gradient' ? 'display: none;' : ''}">
            <label class="form-label">그라데이션 설정</label>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div id="edit_custom_page_block_slide_${itemIndex}_gradient_preview" 
                     style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(${blockData ? (blockData.background_gradient_angle || 90) : 90}deg, ${blockData ? (blockData.background_gradient_start || '#ffffff') : '#ffffff'}, ${blockData ? (blockData.background_gradient_end || '#000000') : '#000000'});"
                     onclick="openBlockGradientModal('edit_custom_page_block_slide_${itemIndex}')"
                     title="그라데이션 설정">
                </div>
                <input type="hidden" 
                       class="edit-custom-page-block-slide-background-gradient-start" 
                       name="edit_custom_page_block_slide[${itemIndex}][background_gradient_start]" 
                       id="edit_custom_page_block_slide_${itemIndex}_gradient_start"
                       value="${blockData ? (blockData.background_gradient_start || '#ffffff') : '#ffffff'}">
                <input type="hidden" 
                       class="edit-custom-page-block-slide-background-gradient-end" 
                       name="edit_custom_page_block_slide[${itemIndex}][background_gradient_end]" 
                       id="edit_custom_page_block_slide_${itemIndex}_gradient_end"
                       value="${blockData ? (blockData.background_gradient_end || '#000000') : '#000000'}">
                <input type="hidden" 
                       class="edit-custom-page-block-slide-background-gradient-angle" 
                       name="edit_custom_page_block_slide[${itemIndex}][background_gradient_angle]" 
                       id="edit_custom_page_block_slide_${itemIndex}_gradient_angle"
                       value="${blockData ? (blockData.background_gradient_angle || 90) : 90}">
            </div>
            <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
        </div>
        <div class="mb-3">
            <label class="form-label">제목 컬러</label>
            <input type="color" 
                   class="form-control form-control-color edit-custom-page-block-slide-title-color" 
                   name="edit_custom_page_block_slide[${itemIndex}][title_color]" 
                   value="${blockData ? (blockData.title_color || blockData.font_color || '#ffffff') : '#ffffff'}">
        </div>
        <div class="mb-3">
            <label class="form-label">내용 컬러</label>
            <input type="color" 
                   class="form-control form-control-color edit-custom-page-block-slide-content-color" 
                   name="edit_custom_page_block_slide[${itemIndex}][content_color]" 
                   value="${blockData ? (blockData.content_color || blockData.font_color || '#ffffff') : '#ffffff'}">
        </div>
        <div class="mb-3 edit-custom-page-block-slide-image-container" id="edit_custom_page_block_slide_${itemIndex}_image_container" style="${!blockData || blockData.background_type !== 'image' ? 'display: none;' : ''}">
            <label class="form-label">배경 이미지</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" 
                        class="btn btn-outline-secondary" 
                        onclick="document.getElementById('edit_custom_page_block_slide_${itemIndex}_image_input').click()">
                    <i class="bi bi-image"></i> 이미지 선택
                </button>
                <input type="file" 
                       id="edit_custom_page_block_slide_${itemIndex}_image_input" 
                       name="edit_custom_page_block_slide[${itemIndex}][background_image]" 
                       accept="image/*" 
                       style="display: none;"
                       onchange="handleEditCustomPageBlockSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="edit-custom-page-block-slide-background-image-url" name="edit_custom_page_block_slide[${itemIndex}][background_image_url]" id="edit_custom_page_block_slide_${itemIndex}_background_image_url" value="${blockData && blockData.background_image_url ? blockData.background_image_url : ''}">
                <div class="edit-custom-page-block-slide-image-preview" id="edit_custom_page_block_slide_${itemIndex}_image_preview" style="${blockData && blockData.background_image_url ? '' : 'display: none;'}">
                    <img id="edit_custom_page_block_slide_${itemIndex}_image_preview_img" src="${blockData && blockData.background_image_url ? blockData.background_image_url : ''}" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditCustomPageBlockSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
            <label class="form-label">투명도</label>
            <input type="range" 
                   class="form-range edit-custom-page-block-slide-background-image-alpha" 
                   name="edit_custom_page_block_slide[${itemIndex}][background_image_alpha]"
                   min="0" 
                   max="100" 
                   value="${blockData && blockData.background_image_alpha !== undefined && blockData.background_image_alpha !== null ? blockData.background_image_alpha : 100}"
                   onchange="document.getElementById('edit_custom_page_block_slide_${itemIndex}_background_image_alpha_value').textContent = this.value + '%'">
            <div class="d-flex justify-content-between">
                <small class="text-muted" style="font-size: 0.7rem;">0%</small>
                <small class="text-muted" id="edit_custom_page_block_slide_${itemIndex}_background_image_alpha_value" style="font-size: 0.7rem;">${blockData && blockData.background_image_alpha !== undefined && blockData.background_image_alpha !== null ? blockData.background_image_alpha : 100}%</small>
                <small class="text-muted" style="font-size: 0.7rem;">100%</small>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">상단 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-custom-page-block-slide-padding-top" 
                   name="edit_custom_page_block_slide[${itemIndex}][padding_top]" 
                   value="${blockData && (blockData.padding_top !== undefined && blockData.padding_top !== null) ? blockData.padding_top : '20'}"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 상단 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">하단 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-custom-page-block-slide-padding-bottom" 
                   name="edit_custom_page_block_slide[${itemIndex}][padding_bottom]" 
                   value="${blockData && blockData.padding_bottom !== undefined ? blockData.padding_bottom : (blockData && blockData.padding_top !== undefined ? blockData.padding_top : '20')}"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 하단 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">좌측 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-custom-page-block-slide-padding-left" 
                   name="edit_custom_page_block_slide[${itemIndex}][padding_left]" 
                   value="${blockData && (blockData.padding_left !== undefined && blockData.padding_left !== null) ? blockData.padding_left : '20'}"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 좌측 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">우측 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-custom-page-block-slide-padding-right" 
                   name="edit_custom_page_block_slide[${itemIndex}][padding_right]" 
                   value="${blockData && blockData.padding_right !== undefined ? blockData.padding_right : (blockData && blockData.padding_left !== undefined ? blockData.padding_left : '20')}"
                   min="0"
                   max="200"
                   step="1"
                   placeholder="20">
            <small class="text-muted">블록 우측 여백을 입력하세요 (0~200).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">제목-내용 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-custom-page-block-slide-title-content-gap" 
                   name="edit_custom_page_block_slide[${itemIndex}][title_content_gap]" 
                   value="${blockData && (blockData.title_content_gap !== undefined && blockData.title_content_gap !== null) ? blockData.title_content_gap : '8'}"
                   min="0"
                   max="100"
                   step="1"
                   placeholder="8">
            <small class="text-muted">제목과 내용 사이의 여백을 입력하세요 (0~100).</small>
        </div>
        <div class="mb-3">
            <label class="form-label">버튼 관리</label>
            <div class="edit-custom-page-block-slide-buttons-list" id="edit_custom_page_block_slide_${itemIndex}_buttons_list">
                <!-- 버튼들이 여기에 동적으로 추가됨 -->
            </div>
            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addEditCustomPageBlockSlideButton(${itemIndex})">
                <i class="bi bi-plus-circle me-1"></i>버튼 추가
            </button>
        </div>
        <div class="mb-3">
            <label class="form-label">버튼 상단 여백 (px)</label>
            <input type="number" 
                   class="form-control edit-custom-page-block-slide-button-top-margin" 
                   name="edit_custom_page_block_slide[${itemIndex}][button_top_margin]" 
                   value="${blockData && (blockData.button_top_margin !== undefined && blockData.button_top_margin !== null) ? blockData.button_top_margin : '12'}"
                   min="0"
                   max="100"
                   step="1"
                   placeholder="12">
            <small class="text-muted">버튼과 위 요소 사이의 여백을 입력하세요 (0~100).</small>
        </div>
        <div class="mb-3" id="edit_custom_page_block_slide_${itemIndex}_link_container">
            <label class="form-label">연결 링크 <small class="text-muted">(선택사항)</small>
                <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="버튼이 있는 경우 버튼에 링크가 연결되고, 버튼이 없는 경우 블록 전체에 링크가 연결됩니다."></i>
            </label>
            <input type="url" 
                   class="form-control edit-custom-page-block-slide-link" 
                   name="edit_custom_page_block_slide[${itemIndex}][link]" 
                   placeholder="https://example.com"
                   value="${blockData ? (blockData.link || '') : ''}">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input edit-custom-page-block-slide-open-new-tab" 
                       type="checkbox" 
                       name="edit_custom_page_block_slide[${itemIndex}][open_new_tab]"
                       id="edit_custom_page_block_slide_${itemIndex}_open_new_tab"
                       ${blockData && blockData.open_new_tab ? 'checked' : ''}>
                <label class="form-check-label" for="edit_custom_page_block_slide_${itemIndex}_open_new_tab">
                    새창에서 열기
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeEditCustomPageBlockSlideItem(${itemIndex})">
            <i class="bi bi-trash me-1"></i>삭제
        </button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
    
    // 버튼 데이터 로드
    if (blockData && blockData.buttons && Array.isArray(blockData.buttons)) {
        const buttonsList = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_buttons_list`);
        if (buttonsList) {
            if (!editCustomPageBlockSlideButtonIndices) {
                editCustomPageBlockSlideButtonIndices = {};
            }
            if (!editCustomPageBlockSlideButtonIndices[itemIndex]) {
                editCustomPageBlockSlideButtonIndices[itemIndex] = 0;
            }
            blockData.buttons.forEach((button) => {
                addEditCustomPageBlockSlideButton(itemIndex, button);
            });
            
            // 버튼이 있으면 연결 링크 필드 숨기기
            const linkContainer = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_link_container`);
            if (linkContainer) {
                linkContainer.style.display = blockData.buttons.length > 0 ? 'none' : 'block';
            }
        }
    }
}

// 커스텀 페이지 블록 슬라이드 버튼 관리 변수
let editCustomPageBlockSlideButtonIndices = {};

// 커스텀 페이지 블록 슬라이드 버튼 추가
function addEditCustomPageBlockSlideButton(itemIndex, buttonData = null) {
    if (!editCustomPageBlockSlideButtonIndices[itemIndex]) {
        editCustomPageBlockSlideButtonIndices[itemIndex] = 0;
    }
    
    const container = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_buttons_list`);
    if (!container) return;
    
    const buttonIndex = editCustomPageBlockSlideButtonIndices[itemIndex];
    const buttonId = `edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}`;
    const buttonHtml = `
        <div class="card mb-3" id="${buttonId}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">버튼 ${buttonIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeEditCustomPageBlockSlideButton('${buttonId}', ${itemIndex})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 텍스트</label>
                    <input type="text" 
                           class="form-control edit-custom-page-block-slide-button-text" 
                           name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][text]" 
                           placeholder="버튼 텍스트를 입력하세요"
                           value="${buttonData ? (buttonData.text || '') : ''}">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 링크</label>
                    <input type="url" 
                           class="form-control edit-custom-page-block-slide-button-link" 
                           name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][link]" 
                           placeholder="https://example.com"
                           value="${buttonData ? (buttonData.link || '') : ''}">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input edit-custom-page-block-slide-button-open-new-tab" 
                               type="checkbox" 
                               name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][open_new_tab]" 
                               id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_open_new_tab"
                               ${buttonData && buttonData.open_new_tab ? 'checked' : ''}>
                        <label class="form-check-label" for="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_open_new_tab">
                            새창에서 열기
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 배경 타입</label>
                    <select class="form-select edit-custom-page-block-slide-button-background-type" 
                            name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][background_type]"
                            onchange="handleButtonBackgroundTypeChange(this)">
                        <option value="color" ${buttonData && buttonData.background_type === 'gradient' ? '' : 'selected'}>컬러</option>
                        <option value="gradient" ${buttonData && buttonData.background_type === 'gradient' ? 'selected' : ''}>그라데이션</option>
                    </select>
                </div>
                <div class="row edit-custom-page-block-slide-button-color-container">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-slide-button-background-color" 
                               name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][background_color]" 
                               value="${buttonData ? (buttonData.background_color || '#007bff') : '#007bff'}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-slide-button-text-color" 
                               name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][text_color]" 
                               value="${buttonData ? (buttonData.text_color || '#ffffff') : '#ffffff'}">
                    </div>
                </div>
                <div class="row edit-custom-page-block-slide-button-gradient-container" style="display: ${buttonData && buttonData.background_type === 'gradient' ? 'block' : 'none'};">
                    <div class="col-12 mb-3">
                        <label class="form-label">그라데이션 설정</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_gradient_preview" 
                                 class="edit-custom-page-block-slide-button-gradient-preview"
                                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, ${buttonData ? (buttonData.background_gradient_start || '#007bff') : '#007bff'}, ${buttonData ? (buttonData.background_gradient_end || '#0056b3') : '#0056b3'});"
                                 onclick="openButtonGradientModal('edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}')"
                                 title="그라데이션 설정">
                            </div>
                            <input type="hidden" 
                                   class="edit-custom-page-block-slide-button-gradient-start" 
                                   id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_gradient_start"
                                   name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][background_gradient_start]" 
                                   value="${buttonData ? (buttonData.background_gradient_start || '#007bff') : '#007bff'}">
                            <input type="hidden" 
                                   class="edit-custom-page-block-slide-button-gradient-end" 
                                   id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_gradient_end"
                                   name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][background_gradient_end]" 
                                   value="${buttonData ? (buttonData.background_gradient_end || '#0056b3') : '#0056b3'}">
                            <input type="hidden" 
                                   class="edit-custom-page-block-slide-button-gradient-angle" 
                                   id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_gradient_angle"
                                   name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][background_gradient_angle]" 
                                   value="${buttonData ? (buttonData.background_gradient_angle || '90') : '90'}">
                        </div>
                        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 배경 투명도</label>
                    <input type="range" 
                           class="form-range edit-custom-page-block-slide-button-opacity" 
                           name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][opacity]" 
                           id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_opacity"
                           value="${buttonData && buttonData.opacity !== undefined ? Math.round(buttonData.opacity * 100) : '100'}" 
                           min="0" 
                           max="100" 
                           step="1"
                           onchange="document.getElementById('edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_opacity_value').textContent = this.value + '%'">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted" style="font-size: 0.7rem;">0%</small>
                        <small class="text-muted" id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_opacity_value" style="font-size: 0.7rem;">${buttonData && buttonData.opacity !== undefined ? Math.round(buttonData.opacity * 100) : '100'}%</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 테두리 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-slide-button-border-color" 
                               name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][border_color]" 
                               value="${buttonData ? (buttonData.border_color || '#007bff') : '#007bff'}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 테두리 두께 (px)</label>
                        <input type="number" 
                               class="form-control edit-custom-page-block-slide-button-border-width" 
                               name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][border_width]" 
                               value="${buttonData ? (buttonData.border_width || '2') : '2'}" 
                               min="0" 
                               max="10" 
                               step="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">호버 배경 타입</label>
                    <select class="form-select edit-custom-page-block-slide-button-hover-background-type" 
                            name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_type]"
                            onchange="handleButtonHoverBackgroundTypeChange(this)">
                        <option value="color" ${buttonData && buttonData.hover_background_type === 'gradient' ? '' : 'selected'}>컬러</option>
                        <option value="gradient" ${buttonData && buttonData.hover_background_type === 'gradient' ? 'selected' : ''}>그라데이션</option>
                    </select>
                </div>
                <div class="row edit-custom-page-block-slide-button-hover-color-container">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-slide-button-hover-background-color" 
                               name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_color]" 
                               value="${buttonData ? (buttonData.hover_background_color || '#0056b3') : '#0056b3'}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-slide-button-hover-text-color" 
                               name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][hover_text_color]" 
                               value="${buttonData ? (buttonData.hover_text_color || '#ffffff') : '#ffffff'}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 테두리 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-slide-button-hover-border-color" 
                               name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][hover_border_color]" 
                               value="${buttonData ? (buttonData.hover_border_color || '#0056b3') : '#0056b3'}">
                    </div>
                </div>
                <div class="row edit-custom-page-block-slide-button-hover-gradient-container" style="display: ${buttonData && buttonData.hover_background_type === 'gradient' ? 'block' : 'none'};">
                    <div class="col-12 mb-3">
                        <label class="form-label">호버 그라데이션 설정</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_hover_gradient_preview" 
                                 class="edit-custom-page-block-slide-button-hover-gradient-preview"
                                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, ${buttonData ? (buttonData.hover_background_gradient_start || '#0056b3') : '#0056b3'}, ${buttonData ? (buttonData.hover_background_gradient_end || '#004085') : '#004085'});"
                                 onclick="openButtonGradientModal('edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_hover')"
                                 title="그라데이션 설정">
                            </div>
                            <input type="hidden" 
                                   class="edit-custom-page-block-slide-button-hover-gradient-start" 
                                   id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_hover_gradient_start"
                                   name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_gradient_start]" 
                                   value="${buttonData ? (buttonData.hover_background_gradient_start || '#0056b3') : '#0056b3'}">
                            <input type="hidden" 
                                   class="edit-custom-page-block-slide-button-hover-gradient-end" 
                                   id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_hover_gradient_end"
                                   name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_gradient_end]" 
                                   value="${buttonData ? (buttonData.hover_background_gradient_end || '#004085') : '#004085'}">
                            <input type="hidden" 
                                   class="edit-custom-page-block-slide-button-hover-gradient-angle" 
                                   id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_hover_gradient_angle"
                                   name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][hover_background_gradient_angle]" 
                                   value="${buttonData ? (buttonData.hover_background_gradient_angle || '90') : '90'}">
                        </div>
                        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">호버 투명도</label>
                    <input type="range" 
                           class="form-range edit-custom-page-block-slide-button-hover-opacity" 
                           name="edit_custom_page_block_slide[${itemIndex}][buttons][${buttonIndex}][hover_opacity]" 
                           id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_hover_opacity"
                           value="${buttonData && buttonData.hover_opacity !== undefined ? Math.round(buttonData.hover_opacity * 100) : '100'}" 
                           min="0" 
                           max="100" 
                           step="1"
                           onchange="document.getElementById('edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_hover_opacity_value').textContent = this.value + '%'">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted" style="font-size: 0.7rem;">0%</small>
                        <small class="text-muted" id="edit_custom_page_block_slide_${itemIndex}_button_${buttonIndex}_hover_opacity_value" style="font-size: 0.7rem;">${buttonData && buttonData.hover_opacity !== undefined ? Math.round(buttonData.hover_opacity * 100) : '100'}%</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', buttonHtml);
    editCustomPageBlockSlideButtonIndices[itemIndex]++;
    
    // 버튼이 추가되면 연결 링크 필드 숨기기
    const linkContainer = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_link_container`);
    if (linkContainer) {
        linkContainer.style.display = 'none';
    }
}

// 커스텀 페이지 블록 슬라이드 버튼 삭제
function removeEditCustomPageBlockSlideButton(buttonId, itemIndex) {
    const button = document.getElementById(buttonId);
    if (button) button.remove();
    
    // 버튼이 없으면 연결 링크 필드 보이기
    const container = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_buttons_list`);
    const linkContainer = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_link_container`);
    if (linkContainer && container) {
        const buttons = container.querySelectorAll('.card');
        if (buttons.length === 0) {
            linkContainer.style.display = 'block';
        }
    }
}

function toggleEditMainBlockSlideItem(itemIndex) {
    const body = document.getElementById(`edit_custom_page_block_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`edit_custom_page_block_slide_item_${itemIndex}_icon`);
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

function handleEditCustomPageBlockSlideBackgroundTypeChange(itemIndex) {
    const backgroundType = document.querySelector(`#edit_custom_page_block_slide_item_${itemIndex} .edit-custom-page-block-slide-background-type`)?.value;
    const colorContainer = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_color_container`);
    const gradientContainer = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_gradient_container`);
    
    if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'block';
        if (gradientContainer) gradientContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'block';
    } else {
        // none
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
    }
}

function handleEditCustomPageBlockSlideImageChange(itemIndex, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_image_preview_img`);
            const imageUrl = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_background_image_url`);
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeEditCustomPageBlockSlideImage(itemIndex) {
    const input = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`edit_custom_page_block_slide_${itemIndex}_background_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function removeEditCustomPageBlockSlideItem(itemIndex) {
    const item = document.getElementById(`edit_custom_page_block_slide_item_${itemIndex}`);
    if (item) {
        item.remove();
    }
}

// 이미지 슬라이드 항목 추가 (위젯 수정 폼)
function addEditMainImageSlideItem(imageData = null) {
    const container = document.getElementById('edit_custom_page_widget_image_slide_items');
    if (!container) return;
    
    if (!imageData) {
        const existingItems = container.querySelectorAll('.edit-custom-page-image-slide-item');
        existingItems.forEach((existingItem) => {
            const existingItemIndex = existingItem.dataset.itemIndex;
            const existingBody = document.getElementById(`edit_custom_page_image_slide_item_${existingItemIndex}_body`);
            const existingIcon = document.getElementById(`edit_custom_page_image_slide_item_${existingItemIndex}_icon`);
            if (existingBody && existingIcon) {
                existingBody.style.display = 'none';
                existingIcon.className = 'bi bi-chevron-right';
            }
        });
    }
    
    const itemIndex = editCustomPageImageSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 edit-custom-page-image-slide-item';
    item.id = `edit_custom_page_image_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleEditMainImageSlideItem(itemIndex);
    };
    header.innerHTML = `
        <span>이미지 ${itemIndex + 1}</span>
        <i class="bi bi-chevron-down" id="edit_custom_page_image_slide_item_${itemIndex}_icon"></i>
    `;
    
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `edit_custom_page_image_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3">
            <label class="form-label">이미지 선택</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" 
                        class="btn btn-outline-secondary" 
                        onclick="document.getElementById('edit_custom_page_image_slide_${itemIndex}_image_input').click()">
                    <i class="bi bi-image"></i> 이미지 선택
                </button>
                <input type="file" 
                       id="edit_custom_page_image_slide_${itemIndex}_image_input" 
                       name="edit_main_image_slide[${itemIndex}][image_file]" 
                       accept="image/*" 
                       style="display: none;"
                       onchange="handleEditMainImageSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="edit-custom-page-image-slide-image-url" name="edit_main_image_slide[${itemIndex}][image_url]" id="edit_custom_page_image_slide_${itemIndex}_image_url" value="${imageData && imageData.image_url ? imageData.image_url : ''}">
                <div class="edit-custom-page-image-slide-image-preview" id="edit_custom_page_image_slide_${itemIndex}_image_preview" style="${imageData && imageData.image_url ? '' : 'display: none;'}">
                    <img id="edit_custom_page_image_slide_${itemIndex}_image_preview_img" src="${imageData && imageData.image_url ? imageData.image_url : ''}" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditMainImageSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
            <input type="url" 
                   class="form-control edit-custom-page-image-slide-link" 
                   name="edit_main_image_slide[${itemIndex}][link]" 
                   placeholder="https://example.com"
                   value="${imageData ? (imageData.link || '') : ''}">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input edit-custom-page-image-slide-open-new-tab" 
                       type="checkbox" 
                       name="edit_main_image_slide[${itemIndex}][open_new_tab]"
                       id="edit_custom_page_image_slide_${itemIndex}_open_new_tab"
                       ${imageData && imageData.open_new_tab ? 'checked' : ''}>
                <label class="form-check-label" for="edit_custom_page_image_slide_${itemIndex}_open_new_tab">
                    새창에서 열기
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeEditCustomPageImageSlideItem(${itemIndex})">
            <i class="bi bi-trash me-1"></i>삭제
        </button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
}

function toggleEditMainImageSlideItem(itemIndex) {
    const body = document.getElementById(`edit_custom_page_image_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`edit_custom_page_image_slide_item_${itemIndex}_icon`);
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
            const preview = document.getElementById(`edit_custom_page_image_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`edit_custom_page_image_slide_${itemIndex}_image_preview_img`);
            const imageUrl = document.getElementById(`edit_custom_page_image_slide_${itemIndex}_image_url`);
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
    const input = document.getElementById(`edit_custom_page_image_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`edit_custom_page_image_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`edit_custom_page_image_slide_${itemIndex}_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function removeEditCustomPageImageSlideItem(itemIndex) {
    const item = document.getElementById(`edit_custom_page_image_slide_item_${itemIndex}`);
    if (item) {
        item.remove();
    }
}

// 블록 배경 타입 변경 핸들러
function handleEditCustomPageBlockBackgroundTypeChange() {
    const backgroundType = document.getElementById('edit_custom_page_widget_block_background_type')?.value;
    const colorContainer = document.getElementById('edit_custom_page_widget_block_color_container');
    const gradientContainer = document.getElementById('edit_custom_page_widget_block_gradient_container');
    
    if (backgroundType === 'none') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'block';
    } else {
        // color
        if (colorContainer) colorContainer.style.display = 'block';
        if (gradientContainer) gradientContainer.style.display = 'none';
    }
}

// 커스텀 페이지 블록 위젯 버튼 관리 변수
let blockButtonIndex = 0; // 새 위젯 추가용
let editCustomPageBlockButtonIndex = 0; // 편집용

function handleEditCustomPageBlockButtonToggle() {
    // 이 함수는 더 이상 사용되지 않지만 호환성을 위해 유지
}

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
                <div class="mb-3">
                    <label class="form-label">버튼 배경 타입</label>
                    <select class="form-select block-button-background-type" 
                            name="block_buttons[${blockButtonIndex}][background_type]"
                            onchange="handleButtonBackgroundTypeChange(this)">
                        <option value="color">컬러</option>
                        <option value="gradient">그라데이션</option>
                    </select>
                </div>
                <div class="row block-button-color-container">
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
                <div class="row block-button-gradient-container" style="display: none;">
                    <div class="col-12 mb-3">
                        <label class="form-label">그라데이션 설정</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div id="block_button_${blockButtonIndex}_gradient_preview" 
                                 class="block-button-gradient-preview"
                                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #007bff, #0056b3);"
                                 onclick="openButtonGradientModal('block_button_${blockButtonIndex}')"
                                 title="그라데이션 설정">
                            </div>
                            <input type="hidden" 
                                   class="block-button-gradient-start" 
                                   id="block_button_${blockButtonIndex}_gradient_start"
                                   name="block_buttons[${blockButtonIndex}][background_gradient_start]" 
                                   value="#007bff">
                            <input type="hidden" 
                                   class="block-button-gradient-end" 
                                   id="block_button_${blockButtonIndex}_gradient_end"
                                   name="block_buttons[${blockButtonIndex}][background_gradient_end]" 
                                   value="#0056b3">
                            <input type="hidden" 
                                   class="block-button-gradient-angle" 
                                   id="block_button_${blockButtonIndex}_gradient_angle"
                                   name="block_buttons[${blockButtonIndex}][background_gradient_angle]" 
                                   value="90">
                        </div>
                        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0">버튼 배경 투명도 (%)</label>
                        </div>
                        <div class="col-auto">
                            <input type="number" 
                                   class="form-control form-control-sm block-button-opacity" 
                                   name="block_buttons[${blockButtonIndex}][opacity]" 
                                   id="block_button_${blockButtonIndex}_opacity"
                                   value="100" 
                                   min="0" 
                                   max="100"
                                   style="width: 80px;">
                        </div>
                    </div>
                    <small class="text-muted">0~100 사이의 값을 입력하세요.</small>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 테두리 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-button-border-color" 
                               name="block_buttons[${blockButtonIndex}][border_color]" 
                               value="#007bff">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 테두리 두께 (px)</label>
                        <input type="number" 
                               class="form-control block-button-border-width" 
                               name="block_buttons[${blockButtonIndex}][border_width]" 
                               value="2" 
                               min="0" 
                               max="10" 
                               step="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">호버 배경 타입</label>
                    <select class="form-select block-button-hover-background-type" 
                            name="block_buttons[${blockButtonIndex}][hover_background_type]"
                            onchange="handleButtonHoverBackgroundTypeChange(this)">
                        <option value="color">컬러</option>
                        <option value="gradient">그라데이션</option>
                    </select>
                </div>
                <div class="row block-button-hover-color-container">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-button-hover-background-color" 
                               name="block_buttons[${blockButtonIndex}][hover_background_color]" 
                               value="#0056b3">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-button-hover-text-color" 
                               name="block_buttons[${blockButtonIndex}][hover_text_color]" 
                               value="#ffffff">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 테두리 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color block-button-hover-border-color" 
                               name="block_buttons[${blockButtonIndex}][hover_border_color]" 
                               value="#0056b3">
                    </div>
                </div>
                <div class="row block-button-hover-gradient-container" style="display: none;">
                    <div class="col-12 mb-3">
                        <label class="form-label">호버 그라데이션 설정</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div id="block_button_${blockButtonIndex}_hover_gradient_preview" 
                                 class="block-button-hover-gradient-preview"
                                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #0056b3, #004085);"
                                 onclick="openButtonGradientModal('block_button_${blockButtonIndex}_hover')"
                                 title="그라데이션 설정">
                            </div>
                            <input type="hidden" 
                                   class="block-button-hover-gradient-start" 
                                   id="block_button_${blockButtonIndex}_hover_gradient_start"
                                   name="block_buttons[${blockButtonIndex}][hover_background_gradient_start]" 
                                   value="#0056b3">
                            <input type="hidden" 
                                   class="block-button-hover-gradient-end" 
                                   id="block_button_${blockButtonIndex}_hover_gradient_end"
                                   name="block_buttons[${blockButtonIndex}][hover_background_gradient_end]" 
                                   value="#004085">
                            <input type="hidden" 
                                   class="block-button-hover-gradient-angle" 
                                   id="block_button_${blockButtonIndex}_hover_gradient_angle"
                                   name="block_buttons[${blockButtonIndex}][hover_background_gradient_angle]" 
                                   value="90">
                        </div>
                        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0">호버 배경 투명도 (%)</label>
                        </div>
                        <div class="col-auto">
                            <input type="number" 
                                   class="form-control form-control-sm block-button-hover-opacity" 
                                   name="block_buttons[${blockButtonIndex}][hover_opacity]" 
                                   id="block_button_${blockButtonIndex}_hover_opacity"
                                   value="100" 
                                   min="0" 
                                   max="100"
                                   style="width: 80px;">
                        </div>
                    </div>
                    <small class="text-muted">0~100 사이의 값을 입력하세요.</small>
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

// 버튼 배경 타입 변경 핸들러
function handleButtonBackgroundTypeChange(selectElement) {
    const buttonCard = selectElement.closest('.card');
    if (!buttonCard) return;
    
    const backgroundType = selectElement.value;
    const colorContainer = buttonCard.querySelector('.block-button-color-container, .block-slide-button-color-container, .edit-main-block-button-color-container');
    const gradientContainer = buttonCard.querySelector('.block-button-gradient-container, .block-slide-button-gradient-container, .edit-main-block-button-gradient-container');
    
    if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'flex';
        if (gradientContainer) gradientContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'flex';
    }
}

// 버튼 호버 배경 타입 변경 핸들러
function handleButtonHoverBackgroundTypeChange(selectElement) {
    const buttonCard = selectElement.closest('.card');
    if (!buttonCard) return;
    
    const backgroundType = selectElement.value;
    const colorContainer = buttonCard.querySelector('.block-button-hover-color-container, .block-slide-button-hover-color-container, .edit-main-block-button-hover-color-container');
    const gradientContainer = buttonCard.querySelector('.block-button-hover-gradient-container, .block-slide-button-hover-gradient-container, .edit-main-block-button-hover-gradient-container');
    
    if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'flex';
        if (gradientContainer) gradientContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'flex';
    }
}

// 커스텀 페이지 블록 위젯 편집용 버튼 추가
function addEditCustomPageBlockButton(buttonData = null) {
    const container = document.getElementById('edit_custom_page_widget_block_buttons_list');
    if (!container) return;
    
    const buttonId = `edit_custom_page_block_button_${editCustomPageBlockButtonIndex}`;
    const buttonHtml = `
        <div class="card mb-3" id="${buttonId}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">버튼 ${editCustomPageBlockButtonIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeEditCustomPageBlockButton('${buttonId}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 텍스트</label>
                    <input type="text" 
                           class="form-control edit-custom-page-block-button-text" 
                           name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][text]" 
                           placeholder="버튼 텍스트를 입력하세요"
                           value="${buttonData ? (buttonData.text || '') : ''}">
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 링크</label>
                    <input type="url" 
                           class="form-control edit-custom-page-block-button-link" 
                           name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][link]" 
                           placeholder="https://example.com"
                           value="${buttonData ? (buttonData.link || '') : ''}">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input edit-custom-page-block-button-open-new-tab" 
                               type="checkbox" 
                               name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][open_new_tab]" 
                               id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_open_new_tab"
                               ${buttonData && buttonData.open_new_tab ? 'checked' : ''}>
                        <label class="form-check-label" for="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_open_new_tab">
                            새창에서 열기
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">버튼 배경 타입</label>
                    <select class="form-select edit-custom-page-block-button-background-type" 
                            name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][background_type]"
                            onchange="handleButtonBackgroundTypeChange(this)">
                        <option value="color" ${buttonData && buttonData.background_type === 'gradient' ? '' : 'selected'}>컬러</option>
                        <option value="gradient" ${buttonData && buttonData.background_type === 'gradient' ? 'selected' : ''}>그라데이션</option>
                    </select>
                </div>
                <div class="row edit-custom-page-block-button-color-container">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-button-background-color" 
                               name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][background_color]" 
                               value="${buttonData ? (buttonData.background_color || '#007bff') : '#007bff'}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-button-text-color" 
                               name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][text_color]" 
                               value="${buttonData ? (buttonData.text_color || '#ffffff') : '#ffffff'}">
                    </div>
                </div>
                <div class="row edit-custom-page-block-button-gradient-container" style="display: ${buttonData && buttonData.background_type === 'gradient' ? 'block' : 'none'};">
                    <div class="col-12 mb-3">
                        <label class="form-label">그라데이션 설정</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_gradient_preview" 
                                 class="edit-custom-page-block-button-gradient-preview"
                                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, ${buttonData ? (buttonData.background_gradient_start || '#007bff') : '#007bff'}, ${buttonData ? (buttonData.background_gradient_end || '#0056b3') : '#0056b3'});"
                                 onclick="openButtonGradientModal('edit_custom_page_block_button_${editCustomPageBlockButtonIndex}')"
                                 title="그라데이션 설정">
                            </div>
                            <input type="hidden" 
                                   class="edit-custom-page-block-button-gradient-start" 
                                   id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_gradient_start"
                                   name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][background_gradient_start]" 
                                   value="${buttonData ? (buttonData.background_gradient_start || '#007bff') : '#007bff'}">
                            <input type="hidden" 
                                   class="edit-custom-page-block-button-gradient-end" 
                                   id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_gradient_end"
                                   name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][background_gradient_end]" 
                                   value="${buttonData ? (buttonData.background_gradient_end || '#0056b3') : '#0056b3'}">
                            <input type="hidden" 
                                   class="edit-custom-page-block-button-gradient-angle" 
                                   id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_gradient_angle"
                                   name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][background_gradient_angle]" 
                                   value="${buttonData ? (buttonData.background_gradient_angle || '90') : '90'}">
                        </div>
                        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0">버튼 배경 투명도 (%)</label>
                        </div>
                        <div class="col-auto">
                            <input type="number" 
                                   class="form-control form-control-sm edit-custom-page-block-button-opacity" 
                                   name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][opacity]" 
                                   id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_opacity"
                                   value="${buttonData && buttonData.opacity !== undefined ? Math.round(buttonData.opacity * 100) : '100'}" 
                                   min="0" 
                                   max="100"
                                   style="width: 80px;">
                        </div>
                    </div>
                    <small class="text-muted">0~100 사이의 값을 입력하세요.</small>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 테두리 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-button-border-color" 
                               name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][border_color]" 
                               value="${buttonData ? (buttonData.border_color || '#007bff') : '#007bff'}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">버튼 테두리 두께 (px)</label>
                        <input type="number" 
                               class="form-control edit-custom-page-block-button-border-width" 
                               name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][border_width]" 
                               value="${buttonData ? (buttonData.border_width || '2') : '2'}" 
                               min="0" 
                               max="10" 
                               step="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">호버 배경 타입</label>
                    <select class="form-select edit-custom-page-block-button-hover-background-type" 
                            name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][hover_background_type]"
                            onchange="handleButtonHoverBackgroundTypeChange(this)">
                        <option value="color" ${buttonData && buttonData.hover_background_type === 'gradient' ? '' : 'selected'}>컬러</option>
                        <option value="gradient" ${buttonData && buttonData.hover_background_type === 'gradient' ? 'selected' : ''}>그라데이션</option>
                    </select>
                </div>
                <div class="row edit-custom-page-block-button-hover-color-container">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 배경 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-button-hover-background-color" 
                               name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][hover_background_color]" 
                               value="${buttonData ? (buttonData.hover_background_color || '#0056b3') : '#0056b3'}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 텍스트 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-button-hover-text-color" 
                               name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][hover_text_color]" 
                               value="${buttonData ? (buttonData.hover_text_color || '#ffffff') : '#ffffff'}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">호버 테두리 컬러</label>
                        <input type="color" 
                               class="form-control form-control-color edit-custom-page-block-button-hover-border-color" 
                               name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][hover_border_color]" 
                               value="${buttonData ? (buttonData.hover_border_color || '#0056b3') : '#0056b3'}">
                    </div>
                </div>
                <div class="row edit-custom-page-block-button-hover-gradient-container" style="display: ${buttonData && buttonData.hover_background_type === 'gradient' ? 'block' : 'none'};">
                    <div class="col-12 mb-3">
                        <label class="form-label">호버 그라데이션 설정</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_hover_gradient_preview" 
                                 class="edit-custom-page-block-button-hover-gradient-preview"
                                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, ${buttonData ? (buttonData.hover_background_gradient_start || '#0056b3') : '#0056b3'}, ${buttonData ? (buttonData.hover_background_gradient_end || '#004085') : '#004085'});"
                                 onclick="openButtonGradientModal('edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_hover')"
                                 title="그라데이션 설정">
                            </div>
                            <input type="hidden" 
                                   class="edit-custom-page-block-button-hover-gradient-start" 
                                   id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_hover_gradient_start"
                                   name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][hover_background_gradient_start]" 
                                   value="${buttonData ? (buttonData.hover_background_gradient_start || '#0056b3') : '#0056b3'}">
                            <input type="hidden" 
                                   class="edit-custom-page-block-button-hover-gradient-end" 
                                   id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_hover_gradient_end"
                                   name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][hover_background_gradient_end]" 
                                   value="${buttonData ? (buttonData.hover_background_gradient_end || '#004085') : '#004085'}">
                            <input type="hidden" 
                                   class="edit-custom-page-block-button-hover-gradient-angle" 
                                   id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_hover_gradient_angle"
                                   name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][hover_background_gradient_angle]" 
                                   value="${buttonData ? (buttonData.hover_background_gradient_angle || '90') : '90'}">
                        </div>
                        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0">호버 배경 투명도 (%)</label>
                        </div>
                        <div class="col-auto">
                            <input type="number" 
                                   class="form-control form-control-sm edit-custom-page-block-button-hover-opacity" 
                                   name="edit_custom_page_block_buttons[${editCustomPageBlockButtonIndex}][hover_opacity]" 
                                   id="edit_custom_page_block_button_${editCustomPageBlockButtonIndex}_hover_opacity"
                                   value="${buttonData && buttonData.hover_opacity !== undefined ? Math.round(buttonData.hover_opacity * 100) : '100'}" 
                                   min="0" 
                                   max="100"
                                   style="width: 80px;">
                        </div>
                    </div>
                    <small class="text-muted">0~100 사이의 값을 입력하세요.</small>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', buttonHtml);
    editCustomPageBlockButtonIndex++;
    
    // 버튼이 추가되면 연결 링크 필드 숨기기
    const linkContainer = document.getElementById('edit_custom_page_widget_block_link_container');
    if (linkContainer) {
        linkContainer.style.display = 'none';
    }
}

// 커스텀 페이지 블록 위젯 편집용 버튼 삭제
function removeEditCustomPageBlockButton(buttonId) {
    const button = document.getElementById(buttonId);
    if (button) button.remove();
    
    // 버튼이 없으면 연결 링크 필드 보이기
    const container = document.getElementById('edit_custom_page_widget_block_buttons_list');
    const linkContainer = document.getElementById('edit_custom_page_widget_block_link_container');
    if (linkContainer && container) {
        const buttons = container.querySelectorAll('.card');
        if (buttons.length === 0) {
            linkContainer.style.display = 'block';
        }
    }
}

function handleEditCustomPageBlockImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_custom_page_widget_block_image_preview');
            const previewImg = document.getElementById('edit_custom_page_widget_block_image_preview_img');
            const imageUrl = document.getElementById('edit_custom_page_widget_block_background_image');
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
    const input = document.getElementById('edit_custom_page_widget_block_image_input');
    const preview = document.getElementById('edit_custom_page_widget_block_image_preview');
    const imageUrl = document.getElementById('edit_custom_page_widget_block_background_image');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function handleEditMainImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_custom_page_widget_image_preview');
            const previewImg = document.getElementById('edit_custom_page_widget_image_preview_img');
            const imageUrl = document.getElementById('edit_custom_page_widget_image_url');
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
    const input = document.getElementById('edit_custom_page_widget_image_input');
    const preview = document.getElementById('edit_custom_page_widget_image_preview');
    const imageUrl = document.getElementById('edit_custom_page_widget_image_url');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function handleEditMainGalleryDisplayTypeChange() {
    const displayType = document.getElementById('edit_custom_page_widget_gallery_display_type')?.value;
    const gridContainer = document.getElementById('edit_custom_page_widget_gallery_grid_container');
    const slideContainer = document.getElementById('edit_custom_page_widget_gallery_slide_container');
    
    if (displayType === 'grid') {
        if (gridContainer) gridContainer.style.display = 'block';
        if (slideContainer) slideContainer.style.display = 'none';
    } else {
        if (gridContainer) gridContainer.style.display = 'none';
        if (slideContainer) slideContainer.style.display = 'block';
    }
}

// 이 함수는 중복이므로 제거됨 - 실제 saveCustomPageWidgetSettings는 위에 정의되어 있음

// 위젯 설정 및 삭제 버튼 이벤트 리스너
document.addEventListener('DOMContentLoaded', function() {
    // Tooltip 초기화 (이미 초기화된 경우 스킵)
    if (!window.tooltipsInitialized) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        window.tooltipsInitialized = true;
    }
    // Tooltip 초기화
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    // 위젯 설정 버튼
    document.querySelectorAll('.edit-custom-page-widget-btn').forEach(button => {
        button.addEventListener('click', function() {
            const widgetId = this.getAttribute('data-widget-id');
            if (widgetId) {
                editCustomPageWidget(parseInt(widgetId));
            }
        });
    });
    
    // 위젯 삭제 버튼
    document.querySelectorAll('.delete-custom-page-widget-btn').forEach(button => {
        button.addEventListener('click', function() {
            const widgetId = this.getAttribute('data-widget-id');
            if (widgetId) {
                deleteCustomPageWidget(parseInt(widgetId));
            }
        });
    });
});

// 커스텀 페이지 위젯 설정 저장 (하단 저장 버튼용)
let customPageSuccessModalReloadHandler = null;

function saveAllCustomPageWidgets() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>저장 중...';
    
    // 커스텀 페이지 위젯은 컨테이너와 위젯이 이미 저장되어 있으므로, 단순히 성공 메시지만 표시
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        // 성공 모달 표시
        const successModalElement = document.getElementById('saveCustomPageWidgetSuccessModal');
        
        // 기존 모달 인스턴스가 있으면 제거
        const existingModal = bootstrap.Modal.getInstance(successModalElement);
        if (existingModal) {
            existingModal.dispose();
        }
        
        // 기존 이벤트 리스너 제거 (중복 방지)
        if (customPageSuccessModalReloadHandler) {
            successModalElement.removeEventListener('hidden.bs.modal', customPageSuccessModalReloadHandler);
        }
        
        // 새 모달 인스턴스 생성 및 표시
        const successModal = new bootstrap.Modal(successModalElement);
        successModal.show();
        
        // 모달이 닫힐 때 페이지 새로고침
        customPageSuccessModalReloadHandler = () => {
            location.reload();
        };
        successModalElement.addEventListener('hidden.bs.modal', customPageSuccessModalReloadHandler, { once: true });
    }, 500);
}

// 카운트다운 타입 변경 핸들러
function handleCountdownTypeChange() {
    const countdownType = document.getElementById('widget_countdown_type')?.value;
    const ddayContainer = document.getElementById('widget_countdown_dday_container');
    const numberContainer = document.getElementById('widget_countdown_number_container');
    
    if (countdownType === 'dday') {
        if (ddayContainer) ddayContainer.style.display = 'block';
        if (numberContainer) numberContainer.style.display = 'none';
    } else if (countdownType === 'number') {
        if (ddayContainer) ddayContainer.style.display = 'none';
        if (numberContainer) numberContainer.style.display = 'block';
        // 숫자 카운트 항목이 없으면 하나 추가
        const itemsContainer = document.getElementById('widget_countdown_number_items');
        if (itemsContainer && itemsContainer.children.length === 0) {
            addCountdownNumberItem();
        }
    }
}

// 카운트다운 배경 타입 변경 핸들러
function handleCountdownBackgroundTypeChange() {
    const backgroundType = document.getElementById('widget_countdown_background_type')?.value || 'none';
    const colorContainer = document.getElementById('widget_countdown_color_container');
    const gradientContainer = document.getElementById('widget_countdown_gradient_container');
    
    if (colorContainer) colorContainer.style.display = backgroundType === 'color' ? 'block' : 'none';
    if (gradientContainer) gradientContainer.style.display = backgroundType === 'gradient' ? 'block' : 'none';
}

// 카운트다운 그라데이션 모달 열기
function openCountdownGradientModal(prefix) {
    const startColor = document.getElementById(`${prefix}_background_gradient_start`)?.value || '#ffffff';
    const endColor = document.getElementById(`${prefix}_background_gradient_end`)?.value || '#000000';
    const angle = document.getElementById(`${prefix}_background_gradient_angle`)?.value || 90;
    
    // 블록 그라데이션 모달 재사용
    const modal = document.getElementById('blockGradientModal');
    if (!modal) {
        alert('그라데이션 설정 모달을 찾을 수 없습니다.');
        return;
    }
    
    document.getElementById('block_gradient_start_color').value = startColor;
    document.getElementById('block_gradient_end_color').value = endColor;
    document.getElementById('block_gradient_angle').value = angle;
    document.getElementById('block_gradient_angle_value').textContent = angle + '°';
    updateBlockGradientPreview();
    
    window.currentCountdownGradientPrefix = prefix;
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
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
                           placeholder="예: 48.5"
                           min="0"
                           step="any">
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

// 커스텀 페이지 위젯 애니메이션 모달 열기
function openCustomPageWidgetAnimationModal(widgetId) {
    document.getElementById('custom_page_widget_animation_id').value = widgetId;
    
    // 기존 애니메이션 설정 불러오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (widgetItem) {
        const settings = widgetItem.dataset.widgetSettings ? JSON.parse(widgetItem.dataset.widgetSettings) : {};
        const animationDirection = settings.animation_direction || 'none';
        const animationDelay = settings.animation_delay || 0;
        
        // 방향 버튼 선택 상태 초기화
        document.querySelectorAll('#customPageWidgetAnimationModal .animation-direction-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // 선택된 방향 버튼 활성화
        const selectedBtn = document.querySelector(`#customPageWidgetAnimationModal .animation-direction-btn[data-direction="${animationDirection}"]`);
        if (selectedBtn) {
            selectedBtn.classList.add('active');
        }
        
        document.getElementById('custom_page_widget_animation_direction').value = animationDirection;
        document.getElementById('custom_page_widget_animation_delay').value = animationDelay;
    } else {
        // 기본값 설정
        document.querySelectorAll('#customPageWidgetAnimationModal .animation-direction-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector('#customPageWidgetAnimationModal .animation-direction-btn[data-direction="none"]').classList.add('active');
        document.getElementById('custom_page_widget_animation_direction').value = 'none';
        document.getElementById('custom_page_widget_animation_delay').value = 0;
    }
    
    // 툴팁 초기화
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('#customPageWidgetAnimationModal [data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const modal = new bootstrap.Modal(document.getElementById('customPageWidgetAnimationModal'));
    modal.show();
}

// 커스텀 페이지 애니메이션 방향 선택
function selectCustomPageAnimationDirection(direction, button) {
    // 모든 버튼에서 active 클래스 제거
    document.querySelectorAll('#customPageWidgetAnimationModal .animation-direction-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // 선택된 버튼에 active 클래스 추가
    button.classList.add('active');
    
    // hidden input에 값 설정
    document.getElementById('custom_page_widget_animation_direction').value = direction;
}

// 커스텀 페이지 위젯 애니메이션 설정 저장
function saveCustomPageWidgetAnimation() {
    const widgetId = document.getElementById('custom_page_widget_animation_id').value;
    const animationDirection = document.getElementById('custom_page_widget_animation_direction').value;
    const animationDelay = parseFloat(document.getElementById('custom_page_widget_animation_delay').value) || 0;
    
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
    const modal = document.getElementById('customPageWidgetSettingsModal');
    const fetchRoute = modal ? modal.getAttribute('data-fetch-route') : '';
    const updateRoute = modal ? modal.getAttribute('data-update-route') : '';
    
    if (!updateRoute) {
        alert('위젯 정보를 가져올 수 없습니다.');
        return;
    }
    
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
    const saveBtn = document.querySelector('#customPageWidgetAnimationModal .btn-primary');
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('customPageWidgetAnimationModal'));
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

// 그라데이션 모달 관련 변수
let currentGradientContainerId = null;
let currentGradientType = null; // 'main' or 'custom'
// 블록 그라데이션 모달 열기
let currentBlockGradientId = null;
// 버튼 그라데이션 모달 열기
let currentButtonGradientId = null;

// Hex를 RGBA로 변환
function hexToRgba(hex, alpha = 1) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// Hex를 RGB로 변환 (r, g, b 형태로 반환)
function hexToRgb(hex) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `${r}, ${g}, ${b}`;
}

// RGBA를 Hex와 Alpha로 분리
function rgbaToHexAndAlpha(rgbaString) {
    if (!rgbaString || rgbaString.startsWith('#')) {
        return { hex: rgbaString || '#ffffff', alpha: 1 };
    }
    
    const rgbaMatch = rgbaString.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
    if (rgbaMatch) {
        const r = parseInt(rgbaMatch[1]);
        const g = parseInt(rgbaMatch[2]);
        const b = parseInt(rgbaMatch[3]);
        const alpha = rgbaMatch[4] ? parseFloat(rgbaMatch[4]) : 1;
        const hex = '#' + [r, g, b].map(x => {
            const hex = x.toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        }).join('');
        return { hex, alpha };
    }
    
    return { hex: '#ffffff', alpha: 1 };
}

// 색상 컨트롤 업데이트
function updateGradientColorControl(type) {
    const colorInput = document.getElementById(`gradient_modal_${type}_color`);
    const alphaInput = document.getElementById(`gradient_modal_${type}_alpha`);
    const display = document.getElementById(`gradient_${type}_color_display`);
    const iconDisplay = document.getElementById(`gradient_${type}_icon_display`);
    
    if (!colorInput || !alphaInput) return;
    
    const hex = colorInput.value;
    const alpha = alphaInput.value / 100;
    const rgba = hexToRgba(hex, alpha);
    
    // 표시 업데이트
    if (display) {
        display.style.background = rgba;
    }
    
    // 아이콘 표시 업데이트
    if (iconDisplay) {
        iconDisplay.style.background = rgba;
    }
    
    // 미리보기 업데이트
    updateGradientPreview();
}

// 그라데이션 컨트롤을 드래그 가능하게 만들기
function makeGradientControlDraggable(control) {
    let isDragging = false;
    let startX = 0;
    let startLeft = 0;
    
    control.addEventListener('mousedown', function(e) {
        if (e.target.type === 'color' || e.target.type === 'range' || e.target.closest('input[type="color"]') || e.target.closest('input[type="range"]')) {
            return;
        }
        // 색상 표시 영역을 클릭한 경우 설정 패널 표시 (드래그 아님)
        const colorDisplay = e.target.closest('.gradient-color-display');
        if (colorDisplay) {
            // 색상 표시 영역 클릭 시 설정 패널 표시
            const controlType = control.id === 'gradient_start_control' ? 'start' : (control.id === 'gradient_end_control' ? 'end' : 'middle');
            if (typeof selectGradientControl === 'function') {
                selectGradientControl(control, controlType);
            }
            e.preventDefault();
            e.stopPropagation();
            return;
        }
        // handle을 클릭한 경우에만 드래그 시작
        if (e.target.classList.contains('gradient-control-handle')) {
            isDragging = true;
            control.style.cursor = 'grabbing';
            startX = e.clientX;
            startLeft = parseFloat(control.style.left) || 0;
            e.preventDefault();
            e.stopPropagation();
            return;
        }
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        const preview = document.getElementById('gradient_modal_preview');
        if (!preview) return;
        
        const rect = preview.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const percent = Math.max(0, Math.min(100, (x / rect.width) * 100));
        
        control.style.left = `${percent}%`;
        control.setAttribute('data-position', percent);
        
        updateGradientPreview();
    });
    
    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            control.style.cursor = 'grab';
        }
    });
    
    // 터치 이벤트 지원
    control.addEventListener('touchstart', function(e) {
        if (e.target.type === 'color') return;
        isDragging = true;
        const touch = e.touches[0];
        startX = touch.clientX;
        startLeft = parseFloat(control.style.left) || 0;
        e.preventDefault();
    });
    
    document.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        
        const preview = document.getElementById('gradient_modal_preview');
        if (!preview) return;
        
        const touch = e.touches[0];
        const rect = preview.getBoundingClientRect();
        const x = touch.clientX - rect.left;
        const percent = Math.max(0, Math.min(100, (x / rect.width) * 100));
        
        control.style.left = `${percent}%`;
        control.setAttribute('data-position', percent);
        
        updateGradientPreview();
        e.preventDefault();
    });
    
    document.addEventListener('touchend', function() {
        if (isDragging) {
            isDragging = false;
        }
    });
}

// 그라데이션 컨트롤 선택
let selectedGradientControl = null;
let selectedGradientControlType = null;

function selectGradientControl(control, type) {
    // 이전 선택 해제
    document.querySelectorAll('.gradient-color-control').forEach(c => {
        c.style.border = '';
    });
    document.querySelectorAll('.gradient-control-icon').forEach(icon => {
        icon.style.border = '';
    });
    
    // 새 선택
    selectedGradientControl = control;
    selectedGradientControlType = type;
    control.style.border = '2px solid #0d6efd';
    
    // 시작/끝 색상 아이콘 표시 및 선택 표시
    const startIcon = document.getElementById('gradient_start_icon');
    const endIcon = document.getElementById('gradient_end_icon');
    if (type === 'start' && startIcon) {
        startIcon.style.border = '2px solid #0d6efd';
        if (endIcon) endIcon.style.border = '2px solid #6c757d';
    } else if (type === 'end' && endIcon) {
        endIcon.style.border = '2px solid #0d6efd';
        if (startIcon) startIcon.style.border = '2px solid #6c757d';
    } else if (type === 'middle') {
        if (startIcon) startIcon.style.border = '2px solid #6c757d';
        if (endIcon) endIcon.style.border = '2px solid #6c757d';
    }
    
    // 설정 패널 업데이트 및 표시
    const settingsPanel = document.getElementById('gradient_selected_control_settings');
    const removeBtn = document.getElementById('gradient_remove_selected');
    
    const positionControl = document.getElementById('gradient_position_control');
    
    if (type === 'start') {
        const colorInput = document.getElementById('gradient_modal_start_color');
        const alphaInput = document.getElementById('gradient_modal_start_alpha');
        if (settingsPanel && colorInput && alphaInput) {
            document.getElementById('gradient_selected_color').value = colorInput.value;
            document.getElementById('gradient_selected_alpha').value = alphaInput.value;
            document.getElementById('gradient_selected_alpha_value').textContent = alphaInput.value + '%';
            const position = parseFloat((control.style.left || '0%').toString().replace('%', '')) || 0;
            document.getElementById('gradient_selected_position').value = position;
            document.getElementById('gradient_selected_position_value').textContent = position + '%';
            settingsPanel.style.display = 'block';
            if (positionControl) positionControl.style.display = 'block';
            // 시작/끝 색상도 위치 조정 가능하도록
            if (type === 'start' || type === 'end') {
                if (positionControl) positionControl.style.display = 'block';
            }
        }
        if (removeBtn) removeBtn.style.display = 'none';
    } else if (type === 'end') {
        const colorInput = document.getElementById('gradient_modal_end_color');
        const alphaInput = document.getElementById('gradient_modal_end_alpha');
        if (settingsPanel && colorInput && alphaInput) {
            document.getElementById('gradient_selected_color').value = colorInput.value;
            document.getElementById('gradient_selected_alpha').value = alphaInput.value;
            document.getElementById('gradient_selected_alpha_value').textContent = alphaInput.value + '%';
            const position = parseFloat((control.style.left || '100%').toString().replace('%', '')) || 100;
            document.getElementById('gradient_selected_position').value = position;
            document.getElementById('gradient_selected_position_value').textContent = position + '%';
            settingsPanel.style.display = 'block';
            if (positionControl) positionControl.style.display = 'block';
            // 시작/끝 색상도 위치 조정 가능하도록
            if (type === 'start' || type === 'end') {
                if (positionControl) positionControl.style.display = 'block';
            }
        }
        if (removeBtn) removeBtn.style.display = 'none';
    } else if (type === 'middle') {
        const colorInput = control.querySelector('.gradient-middle-color-input');
        const alphaInput = control.querySelector('.gradient-middle-alpha-input');
        if (settingsPanel && colorInput) {
            document.getElementById('gradient_selected_color').value = colorInput.value;
            if (alphaInput) {
                document.getElementById('gradient_selected_alpha').value = alphaInput.value;
                document.getElementById('gradient_selected_alpha_value').textContent = alphaInput.value + '%';
            }
            const position = parseFloat((control.style.left || control.getAttribute('data-position') || '50').toString().replace('%', '')) || 50;
            document.getElementById('gradient_selected_position').value = position;
            document.getElementById('gradient_selected_position_value').textContent = position + '%';
            settingsPanel.style.display = 'block';
            if (positionControl) positionControl.style.display = 'block';
            // 시작/끝 색상도 위치 조정 가능하도록
            if (type === 'start' || type === 'end') {
                if (positionControl) positionControl.style.display = 'block';
            }
        }
        if (removeBtn) removeBtn.style.display = 'block';
    }
    
    // 중간 색상 아이콘 업데이트
    updateGradientMiddleIcons();
}

// 아이콘 클릭 시 컨트롤 선택
function selectGradientIcon(type) {
    const control = document.getElementById(`gradient_${type}_control`);
    if (control) {
        selectGradientControl(control, type);
    }
}

// 선택된 컨트롤 업데이트
function updateSelectedGradientControl() {
    if (!selectedGradientControl || !selectedGradientControlType) return;
    
    const color = document.getElementById('gradient_selected_color').value;
    const alpha = document.getElementById('gradient_selected_alpha').value;
    document.getElementById('gradient_selected_alpha_value').textContent = alpha + '%';
    
    if (selectedGradientControlType === 'start') {
        document.getElementById('gradient_modal_start_color').value = color;
        document.getElementById('gradient_modal_start_alpha').value = alpha;
        updateGradientColorControl('start');
    } else if (selectedGradientControlType === 'end') {
        document.getElementById('gradient_modal_end_color').value = color;
        document.getElementById('gradient_modal_end_alpha').value = alpha;
        updateGradientColorControl('end');
    } else if (selectedGradientControlType === 'middle') {
        const colorInput = selectedGradientControl.querySelector('.gradient-middle-color-input');
        const alphaInput = selectedGradientControl.querySelector('.gradient-middle-alpha-input');
        if (colorInput && alphaInput) {
            colorInput.value = color;
            alphaInput.value = alpha;
            updateGradientMiddleColor(colorInput);
        }
    }
    
    // 중간 색상 아이콘 업데이트
    updateGradientMiddleIcons();
}

// 위치 조정 슬라이더 업데이트
function updateSelectedGradientControlPosition() {
    if (!selectedGradientControl || !selectedGradientControlType) return;
    
    const position = document.getElementById('gradient_selected_position').value;
    document.getElementById('gradient_selected_position_value').textContent = position + '%';
    
    selectedGradientControl.style.left = `${position}%`;
    selectedGradientControl.setAttribute('data-position', position);
    
    // 시작/끝 색상 아이콘도 업데이트
    if (selectedGradientControlType === 'start') {
        const startIcon = document.getElementById('gradient_start_icon');
        if (startIcon) {
            let positionLabel = startIcon.querySelector('small');
            if (!positionLabel) {
                positionLabel = document.createElement('small');
                positionLabel.style.cssText = 'position: absolute; bottom: -18px; left: 50%; transform: translateX(-50%); font-size: 0.7rem; white-space: nowrap;';
                startIcon.style.position = 'relative';
                startIcon.appendChild(positionLabel);
            }
            positionLabel.textContent = position + '%';
        }
    } else if (selectedGradientControlType === 'end') {
        const endIcon = document.getElementById('gradient_end_icon');
        if (endIcon) {
            let positionLabel = endIcon.querySelector('small');
            if (!positionLabel) {
                positionLabel = document.createElement('small');
                positionLabel.style.cssText = 'position: absolute; bottom: -18px; left: 50%; transform: translateX(-50%); font-size: 0.7rem; white-space: nowrap;';
                endIcon.style.position = 'relative';
                endIcon.appendChild(positionLabel);
            }
            positionLabel.textContent = position + '%';
        }
    }
    
    // 그라데이션 미리보기 업데이트
    if (typeof updateGradientPreview === 'function') {
        updateGradientPreview();
    }
    
    // 중간 색상 아이콘 업데이트
    updateGradientMiddleIcons();
}

// 중간 색상 아이콘 업데이트
function updateGradientMiddleIcons() {
    const middleIconsContainer = document.getElementById('gradient_middle_icons');
    if (!middleIconsContainer) return;
    
    middleIconsContainer.innerHTML = '';
    
    const middleControls = document.querySelectorAll('.gradient-middle-control');
    const controlsArray = Array.from(middleControls);
    
    // 위치 순서대로 정렬
    controlsArray.sort((a, b) => {
        const posA = parseFloat((a.style.left || a.getAttribute('data-position') || '50').toString().replace('%', '')) || 50;
        const posB = parseFloat((b.style.left || b.getAttribute('data-position') || '50').toString().replace('%', '')) || 50;
        return posA - posB;
    });
    
    controlsArray.forEach((control, index) => {
        const colorDisplay = control.querySelector('.gradient-middle-color-display');
        const position = parseFloat((control.style.left || control.getAttribute('data-position') || '50').toString().replace('%', '')) || 50;
        const color = colorDisplay ? window.getComputedStyle(colorDisplay).background : 'rgba(128,128,128,1)';
        
        const icon = document.createElement('div');
        icon.className = 'gradient-middle-icon gradient-control-icon';
        icon.style.cssText = 'width: 60px; height: 40px; border: 2px solid #6c757d; border-radius: 4px; background: white; padding: 2px; cursor: pointer; position: relative;';
        icon.setAttribute('data-control-index', index);
        icon.onclick = function() {
            selectGradientControl(control, 'middle');
        };
        
        const iconDisplay = document.createElement('div');
        iconDisplay.style.cssText = 'width: 100%; height: 100%; border-radius: 2px; background: ' + color + ';';
        icon.appendChild(iconDisplay);
        
        const positionLabel = document.createElement('small');
        positionLabel.style.cssText = 'position: absolute; bottom: -18px; left: 50%; transform: translateX(-50%); font-size: 0.7rem; white-space: nowrap;';
        positionLabel.textContent = position + '%';
        icon.appendChild(positionLabel);
        
        middleIconsContainer.appendChild(icon);
    });
}

// 선택된 컨트롤 제거
function removeSelectedGradientControl() {
    if (selectedGradientControl && selectedGradientControlType === 'middle') {
        selectedGradientControl.remove();
        selectedGradientControl = null;
        selectedGradientControlType = null;
        document.getElementById('gradient_selected_control_settings').style.display = 'none';
        updateGradientPreview();
    }
}

// 그라데이션 모달 열기
// 블록 그라데이션 모달 열기
function openBlockGradientModal(blockId) {
    // 컨테이너 그라데이션 ID 초기화
    currentGradientContainerId = null;
    currentGradientType = null;
    currentBlockGradientId = blockId;
    
    // 현재 값 가져오기
    const startColorValue = document.getElementById(`${blockId}_gradient_start`)?.value || 
                           document.getElementById(`${blockId}_background_gradient_start`)?.value || '#ffffff';
    const endColorValue = document.getElementById(`${blockId}_gradient_end`)?.value || 
                         document.getElementById(`${blockId}_background_gradient_end`)?.value || '#000000';
    const angle = document.getElementById(`${blockId}_gradient_angle`)?.value || 
                  document.getElementById(`${blockId}_background_gradient_angle`)?.value || 90;
    
    // RGBA 파싱
    const startParsed = rgbaToHexAndAlpha(startColorValue);
    const endParsed = rgbaToHexAndAlpha(endColorValue);
    
    // 모달에 값 설정
    const startColorInput = document.getElementById('gradient_modal_start_color');
    const startAlphaInput = document.getElementById('gradient_modal_start_alpha');
    const endColorInput = document.getElementById('gradient_modal_end_color');
    const endAlphaInput = document.getElementById('gradient_modal_end_alpha');
    const angleInput = document.getElementById('gradient_modal_angle');
    const angleSliderInput = document.getElementById('gradient_modal_angle_slider');
    
    if (startColorInput) startColorInput.value = startParsed.hex;
    if (startAlphaInput) {
        startAlphaInput.value = Math.round(startParsed.alpha * 100);
        const startAlphaValueDisplay = document.getElementById('gradient_start_alpha_value');
        if (startAlphaValueDisplay) {
            startAlphaValueDisplay.textContent = Math.round(startParsed.alpha * 100) + '%';
        }
    }
    if (endColorInput) endColorInput.value = endParsed.hex;
    if (endAlphaInput) {
        endAlphaInput.value = Math.round(endParsed.alpha * 100);
        const endAlphaValueDisplay = document.getElementById('gradient_end_alpha_value');
        if (endAlphaValueDisplay) {
            endAlphaValueDisplay.textContent = Math.round(endParsed.alpha * 100) + '%';
        }
    }
    if (angleInput) angleInput.value = angle;
    if (angleSliderInput) angleSliderInput.value = angle;
    
    // 색상 컨트롤 업데이트
    if (typeof updateGradientColorControl === 'function') {
        updateGradientColorControl('start');
        updateGradientColorControl('end');
    }
    
    // 시작/끝 컨트롤에 드래그 기능 추가
    const startControl = document.getElementById('gradient_start_control');
    const endControl = document.getElementById('gradient_end_control');
    if (startControl) {
        if (typeof makeGradientControlDraggable === 'function') {
            makeGradientControlDraggable(startControl);
        }
    }
    if (endControl) {
        if (typeof makeGradientControlDraggable === 'function') {
            makeGradientControlDraggable(endControl);
        }
    }
    
    // 중간 색상 초기화
    const middleControlsContainer = document.getElementById('gradient_middle_controls');
    if (middleControlsContainer) {
        middleControlsContainer.innerHTML = '';
    }
    
    // 미리보기 업데이트
    if (typeof updateGradientPreview === 'function') {
        updateGradientPreview();
    }
    
    // 설정 패널 숨기기
    const settingsPanel = document.getElementById('gradient_selected_control_settings');
    if (settingsPanel) {
        settingsPanel.style.display = 'none';
    }
    selectedGradientControl = null;
    selectedGradientControlType = null;
    
    // 모달 표시
    const modalElement = document.getElementById('gradientModal');
    if (!modalElement) {
        console.error('그라데이션 모달을 찾을 수 없습니다.');
        return;
    }
    
    // 기존 모달 인스턴스 확인 및 제거
    const existingModal = bootstrap.Modal.getInstance(modalElement);
    if (existingModal) {
        existingModal.dispose();
    }
    
    // 새 모달 인스턴스 생성 및 표시
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // 모달이 완전히 표시된 후 추가 업데이트
    setTimeout(() => {
        if (typeof updateGradientPreview === 'function') {
            updateGradientPreview();
        }
        if (typeof updateGradientMiddleIcons === 'function') {
            updateGradientMiddleIcons();
        }
    }, 100);
}

function openGradientModal(containerId, type) {
    // 블록 그라데이션 ID 설정 (블록/블록슬라이드인 경우)
    if (containerId && (containerId.startsWith('edit_custom_page_widget_block') || containerId.startsWith('edit_custom_page_block_slide'))) {
        currentBlockGradientId = containerId;
        currentGradientContainerId = null;
    } else {
        currentBlockGradientId = null;
        currentGradientContainerId = containerId;
    }
    currentButtonGradientId = null;
    currentGradientType = type;
    
    // 현재 값 가져오기
    let startColorValue, endColorValue, angle;
    
    if (currentBlockGradientId) {
        // 블록/블록슬라이드의 경우
        startColorValue = document.getElementById(`${containerId}_gradient_start`)?.value || 
                         document.getElementById(`${containerId}_background_gradient_start`)?.value || '#ffffff';
        endColorValue = document.getElementById(`${containerId}_gradient_end`)?.value || 
                       document.getElementById(`${containerId}_background_gradient_end`)?.value || '#000000';
        angle = document.getElementById(`${containerId}_gradient_angle`)?.value || 
                document.getElementById(`${containerId}_background_gradient_angle`)?.value || 90;
    } else {
        // 컨테이너의 경우
        startColorValue = document.getElementById(`container_background_gradient_start_${containerId}`)?.value || 
                         document.getElementById(`container_background_gradient_start_mobile_${containerId}`)?.value || '#ffffff';
        endColorValue = document.getElementById(`container_background_gradient_end_${containerId}`)?.value || 
                       document.getElementById(`container_background_gradient_end_mobile_${containerId}`)?.value || '#000000';
        angle = document.getElementById(`container_background_gradient_angle_${containerId}`)?.value || 
                document.getElementById(`container_background_gradient_angle_mobile_${containerId}`)?.value || 90;
    }
    
    // 모달 표시
    const modalElement = document.getElementById('gradientModal');
    if (!modalElement) {
        console.error('그라데이션 모달을 찾을 수 없습니다.');
        return;
    }
    
    // 기존 모달 인스턴스 확인 및 제거
    const existingModal = bootstrap.Modal.getInstance(modalElement);
    if (existingModal) {
        existingModal.dispose();
    }
    
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // 모달이 표시된 후 값 설정
    setTimeout(() => {
        // RGBA 파싱
        const startParsed = rgbaToHexAndAlpha(startColorValue);
        const endParsed = rgbaToHexAndAlpha(endColorValue);
        
        // 모달에 값 설정
        const startColorInput = document.getElementById('gradient_modal_start_color');
        const startAlphaInput = document.getElementById('gradient_modal_start_alpha');
        const endColorInput = document.getElementById('gradient_modal_end_color');
        const endAlphaInput = document.getElementById('gradient_modal_end_alpha');
        const angleInput = document.getElementById('gradient_modal_angle');
        const angleSliderInput = document.getElementById('gradient_modal_angle_slider');
        
        if (startColorInput) startColorInput.value = startParsed.hex;
        if (startAlphaInput) startAlphaInput.value = Math.round(startParsed.alpha * 100);
        if (endColorInput) endColorInput.value = endParsed.hex;
        if (endAlphaInput) endAlphaInput.value = Math.round(endParsed.alpha * 100);
        if (angleInput) angleInput.value = angle;
        if (angleSliderInput) angleSliderInput.value = angle;
        
        // 색상 컨트롤 업데이트
        if (typeof updateGradientColorControl === 'function') {
            updateGradientColorControl('start');
            updateGradientColorControl('end');
        }
        
        // 시작/끝 컨트롤에 드래그 기능 추가
        const startControl = document.getElementById('gradient_start_control');
        const endControl = document.getElementById('gradient_end_control');
        if (startControl) {
            if (typeof makeGradientControlDraggable === 'function') {
                makeGradientControlDraggable(startControl);
            }
            startControl.addEventListener('click', function(e) {
                if (e.target.type !== 'color') {
                    if (typeof selectGradientControl === 'function') {
                        selectGradientControl(startControl, 'start');
                    }
                }
            });
        }
        if (endControl) {
            if (typeof makeGradientControlDraggable === 'function') {
                makeGradientControlDraggable(endControl);
            }
            endControl.addEventListener('click', function(e) {
                if (e.target.type !== 'color') {
                    if (typeof selectGradientControl === 'function') {
                        selectGradientControl(endControl, 'end');
                    }
                }
            });
        }
        
        // 그라데이션 바 클릭 이벤트 제거 (아이콘 방식으로 변경)
        
        // 중간 색상 초기화
        const middleControlsContainer = document.getElementById('gradient_middle_controls');
        if (middleControlsContainer) {
            middleControlsContainer.innerHTML = '';
        }
        
        // 미리보기 업데이트
        if (typeof updateGradientPreview === 'function') {
            updateGradientPreview();
        }
        
        // 중간 색상 아이콘 초기화
        if (typeof updateGradientMiddleIcons === 'function') {
            updateGradientMiddleIcons();
        }
        
        // 설정 패널 숨기기
        const settingsPanel = document.getElementById('gradient_selected_control_settings');
        if (settingsPanel) {
            settingsPanel.style.display = 'none';
        }
        selectedGradientControl = null;
        selectedGradientControlType = null;
    }, 100);
}

// 그라데이션 미리보기 업데이트
function updateGradientPreview() {
    const startColorInput = document.getElementById('gradient_modal_start_color');
    const startAlphaInput = document.getElementById('gradient_modal_start_alpha');
    const endColorInput = document.getElementById('gradient_modal_end_color');
    const endAlphaInput = document.getElementById('gradient_modal_end_alpha');
    const angleInput = document.getElementById('gradient_modal_angle');
    
    if (!startColorInput || !startAlphaInput || !endColorInput || !endAlphaInput || !angleInput) return;
    
    const startColor = startColorInput.value;
    const startAlpha = startAlphaInput.value / 100;
    const endColor = endColorInput.value;
    const endAlpha = endAlphaInput.value / 100;
    const angle = angleInput.value || 90;
    
    const startRgba = hexToRgba(startColor, startAlpha);
    const endRgba = hexToRgba(endColor, endAlpha);
    
    // 시작 색상 위치 가져오기
    const startControl = document.getElementById('gradient_start_control');
    const startPositionStr = startControl ? (startControl.style.left || startControl.getAttribute('data-position') || '0') : '0';
    const startPosition = parseFloat(startPositionStr.toString().replace('%', '')) || 0;
    
    // 끝 색상 위치 가져오기
    const endControl = document.getElementById('gradient_end_control');
    const endPositionStr = endControl ? (endControl.style.left || endControl.getAttribute('data-position') || '100') : '100';
    const endPosition = parseFloat(endPositionStr.toString().replace('%', '')) || 100;
    
    // 중간 색상들 가져오기
    const middleColors = [];
    const middleControls = document.querySelectorAll('.gradient-middle-control');
    middleControls.forEach((control) => {
        const colorInput = control.querySelector('.gradient-middle-color-input');
        const alphaInput = control.querySelector('.gradient-middle-alpha-input');
        const positionStr = control.style.left || control.getAttribute('data-position') || '50';
        const position = parseFloat(positionStr.toString().replace('%', '')) || 50;
        if (colorInput) {
            const hex = colorInput.value;
            const alpha = alphaInput ? (alphaInput.value / 100) : 1;
            middleColors.push({ rgba: hexToRgba(hex, alpha), position });
        }
    });
    
    // 모든 색상 정렬 (시작, 중간, 끝)
    const allColors = [
        { rgba: startRgba, position: startPosition },
        ...middleColors,
        { rgba: endRgba, position: endPosition }
    ];
    allColors.sort((a, b) => a.position - b.position);
    
    // 그라데이션 문자열 생성
    let gradientString = `linear-gradient(${angle}deg`;
    allColors.forEach((color, index) => {
        if (index === 0) {
            gradientString += `, ${color.rgba} ${color.position}%`;
        } else {
            gradientString += `, ${color.rgba} ${color.position}%`;
        }
    });
    gradientString += `)`;
    
    // 미리보기 업데이트
    const preview = document.getElementById('gradient_modal_preview');
    if (preview) {
        preview.style.background = gradientString;
    }
}

// 중간 색상 추가
function addGradientMiddleColor(position = null) {
    const middleControlsContainer = document.getElementById('gradient_middle_controls');
    
    // 위치가 지정되지 않으면 중간에 추가
    if (position === null) {
        const middleCount = middleControlsContainer.children.length;
        const totalPositions = 100;
        position = Math.round((totalPositions / (middleCount + 2)) * (middleCount + 1));
    }
    
    const control = document.createElement('div');
    control.className = 'gradient-middle-control gradient-color-control';
    control.setAttribute('data-position', position);
    control.style.position = 'absolute';
    control.style.left = `${position}%`;
    control.style.display = 'none'; // 숨김 처리 (아이콘만 표시)
    
    control.innerHTML = `
        <input type="color" 
               class="gradient-middle-color-input" 
               value="#808080"
               onchange="updateGradientMiddleColor(this)">
        <input type="range" 
               class="form-range gradient-middle-alpha-input" 
               min="0" 
               max="100" 
               value="100"
               onchange="updateGradientMiddleColor(this.closest('.gradient-middle-control').querySelector('.gradient-middle-color-input'))">
        <div class="gradient-middle-color-display" style="width: 100%; height: 100%; border-radius: 2px; background: rgba(128,128,128,1);"></div>
    `;
    
    middleControlsContainer.appendChild(control);
    updateGradientMiddleColor(control.querySelector('.gradient-middle-color-input'));
    selectGradientControl(control, 'middle');
    updateGradientMiddleIcons();
}

// 중간 색상 업데이트
function updateGradientMiddleColor(colorInput) {
    const control = colorInput.closest('.gradient-middle-control');
    const alphaInput = control.querySelector('.gradient-middle-alpha-input');
    const display = control.querySelector('.gradient-middle-color-display');
    
    const hex = colorInput.value;
    const alpha = alphaInput ? (alphaInput.value / 100) : 1;
    const rgba = hexToRgba(hex, alpha);
    
    if (display) {
        display.style.background = rgba;
    }
    
    // 선택된 컨트롤이면 설정 패널도 업데이트
    if (selectedGradientControl === control && selectedGradientControlType === 'middle') {
        document.getElementById('gradient_selected_color').value = hex;
        if (alphaInput) {
            document.getElementById('gradient_selected_alpha').value = alphaInput.value;
            document.getElementById('gradient_selected_alpha_value').textContent = alphaInput.value + '%';
        }
    }
    
    updateGradientPreview();
    updateGradientMiddleIcons();
}

// 중간 색상 제거
function removeGradientMiddleColor(button) {
    button.closest('.gradient-middle-control').remove();
    updateGradientPreview();
    updateGradientMiddleIcons();
}

// 버튼 그라데이션 모달 열기
function openButtonGradientModal(buttonId) {
    // 컨테이너 그라데이션 ID 초기화
    currentGradientContainerId = null;
    currentGradientType = null;
    currentBlockGradientId = null;
    currentButtonGradientId = buttonId;
    
    // 현재 값 가져오기
    const startColorValue = document.getElementById(`${buttonId}_gradient_start`)?.value || '#007bff';
    const endColorValue = document.getElementById(`${buttonId}_gradient_end`)?.value || '#0056b3';
    const angle = document.getElementById(`${buttonId}_gradient_angle`)?.value || 90;
    
    // RGBA 파싱
    const startParsed = rgbaToHexAndAlpha(startColorValue);
    const endParsed = rgbaToHexAndAlpha(endColorValue);
    
    // 모달에 값 설정 (null 체크 추가)
    const startColorInput = document.getElementById('gradient_modal_start_color');
    const startAlphaInput = document.getElementById('gradient_modal_start_alpha');
    const endColorInput = document.getElementById('gradient_modal_end_color');
    const endAlphaInput = document.getElementById('gradient_modal_end_alpha');
    const angleInput = document.getElementById('gradient_modal_angle');
    const angleSliderInput = document.getElementById('gradient_modal_angle_slider');
    
    if (startColorInput) startColorInput.value = startParsed.hex;
    if (startAlphaInput) {
        startAlphaInput.value = Math.round(startParsed.alpha * 100);
        const startAlphaValueDisplay = document.getElementById('gradient_start_alpha_value');
        if (startAlphaValueDisplay) {
            startAlphaValueDisplay.textContent = Math.round(startParsed.alpha * 100) + '%';
        }
    }
    if (endColorInput) endColorInput.value = endParsed.hex;
    if (endAlphaInput) {
        endAlphaInput.value = Math.round(endParsed.alpha * 100);
        const endAlphaValueDisplay = document.getElementById('gradient_end_alpha_value');
        if (endAlphaValueDisplay) {
            endAlphaValueDisplay.textContent = Math.round(endParsed.alpha * 100) + '%';
        }
    }
    if (angleInput) angleInput.value = angle;
    if (angleSliderInput) angleSliderInput.value = angle;
    
    // 색상 컨트롤 업데이트
    if (typeof updateGradientColorControl === 'function') {
        updateGradientColorControl('start');
        updateGradientColorControl('end');
    }
    
    // 시작/끝 컨트롤에 드래그 기능 추가
    const startControl = document.getElementById('gradient_start_control');
    const endControl = document.getElementById('gradient_end_control');
    if (startControl) {
        if (typeof makeGradientControlDraggable === 'function') {
            makeGradientControlDraggable(startControl);
        }
    }
    if (endControl) {
        if (typeof makeGradientControlDraggable === 'function') {
            makeGradientControlDraggable(endControl);
        }
    }
    
    // 중간 색상 초기화
    const middleControlsContainer = document.getElementById('gradient_middle_controls');
    if (middleControlsContainer) {
        middleControlsContainer.innerHTML = '';
    }
    
    // 미리보기 업데이트
    if (typeof updateGradientPreview === 'function') {
        updateGradientPreview();
    }
    
    // 설정 패널 숨기기
    const settingsPanel = document.getElementById('gradient_selected_control_settings');
    if (settingsPanel) {
        settingsPanel.style.display = 'none';
    }
    selectedGradientControl = null;
    selectedGradientControlType = null;
    
    // 모달 표시
    const modalElement = document.getElementById('gradientModal');
    if (modalElement) {
        // 기존 모달 인스턴스 확인 및 제거
        const existingModal = bootstrap.Modal.getInstance(modalElement);
        if (existingModal) {
            existingModal.dispose();
        }
        const modal = new bootstrap.Modal(modalElement, { backdrop: false });
        modal.show();
    }
}

// 블록 그라데이션 저장
function saveBlockGradient() {
    if (!currentBlockGradientId) return;
    
    const startColor = document.getElementById('gradient_modal_start_color').value;
    const startAlpha = document.getElementById('gradient_modal_start_alpha').value;
    const endColor = document.getElementById('gradient_modal_end_color').value;
    const endAlpha = document.getElementById('gradient_modal_end_alpha').value;
    const angle = document.getElementById('gradient_modal_angle').value;
    
    const middleColors = [];
    const middleControls = document.querySelectorAll('.gradient-middle-control');
    middleControls.forEach(control => {
        const colorInput = control.querySelector('.gradient-middle-color-input');
        const alphaInput = control.querySelector('.gradient-middle-alpha-input');
        const position = parseFloat(control.getAttribute('data-position')) || parseFloat(control.style.left) || 50;
        if (colorInput) {
            const color = colorInput.value;
            const alpha = alphaInput ? (alphaInput.value / 100) : 1;
            middleColors.push({ color, alpha, position });
        }
    });
    
    middleColors.sort((a, b) => a.position - b.position);
    
    let gradientString = '';
    if (middleColors.length === 0) {
        gradientString = `linear-gradient(${angle}deg, rgba(${hexToRgb(startColor)}, ${startAlpha / 100}), rgba(${hexToRgb(endColor)}, ${endAlpha / 100}))`;
    } else {
        const colors = [
            `rgba(${hexToRgb(startColor)}, ${startAlpha / 100}) 0%`,
            ...middleColors.map(m => `rgba(${hexToRgb(m.color)}, ${m.alpha / 100}) ${m.position}%`),
            `rgba(${hexToRgb(endColor)}, ${endAlpha / 100}) 100%`
        ];
        gradientString = `linear-gradient(${angle}deg, ${colors.join(', ')})`;
    }
    
    // 블록 그라데이션 값 저장 - 두 가지 필드명 모두 확인
    const startInput = document.getElementById(`${currentBlockGradientId}_gradient_start`) || 
                      document.getElementById(`${currentBlockGradientId}_background_gradient_start`);
    const endInput = document.getElementById(`${currentBlockGradientId}_gradient_end`) || 
                    document.getElementById(`${currentBlockGradientId}_background_gradient_end`);
    const angleInput = document.getElementById(`${currentBlockGradientId}_gradient_angle`) || 
                      document.getElementById(`${currentBlockGradientId}_background_gradient_angle`);
    const preview = document.getElementById(`${currentBlockGradientId}_gradient_preview`) ||
                   document.getElementById(`${currentBlockGradientId}_background_gradient_preview`);
    
    // RGBA 형식으로 저장 (alpha 포함)
    const startRgba = hexToRgba(startColor, startAlpha / 100);
    const endRgba = hexToRgba(endColor, endAlpha / 100);
    
    if (startInput) startInput.value = startRgba;
    if (endInput) endInput.value = endRgba;
    if (angleInput) angleInput.value = angle;
    if (preview) {
        preview.style.background = gradientString;
    }
    
    // 모달 닫기 - backdrop 명시적으로 제거
    const modalElement = document.getElementById('gradientModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        modal.hide();
        // 모달이 완전히 닫힌 후 backdrop 제거
        modalElement.addEventListener('hidden.bs.modal', function() {
            // 그라데이션 모달의 backdrop만 제거 (다른 모달의 backdrop은 유지)
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                // 마지막 backdrop(그라데이션 모달의 backdrop) 제거
                backdrops[backdrops.length - 1].remove();
            }
        }, { once: true });
    }
}

// 버튼 그라데이션 저장 함수
function saveButtonGradient() {
    if (!currentButtonGradientId) return;
    
    const startColorInput = document.getElementById('gradient_modal_start_color');
    const startAlphaInput = document.getElementById('gradient_modal_start_alpha');
    const endColorInput = document.getElementById('gradient_modal_end_color');
    const endAlphaInput = document.getElementById('gradient_modal_end_alpha');
    const angleInput = document.getElementById('gradient_modal_angle');
    
    if (!startColorInput || !startAlphaInput || !endColorInput || !endAlphaInput || !angleInput) return;
    
    const startColor = startColorInput.value;
    const startAlpha = startAlphaInput.value / 100;
    const endColor = endColorInput.value;
    const endAlpha = endAlphaInput.value / 100;
    const angle = angleInput.value || 90;
    
    // 투명도가 100%면 hex, 아니면 rgba로 저장
    const startColorValue = startAlpha === 1 ? startColor : hexToRgba(startColor, startAlpha);
    const endColorValue = endAlpha === 1 ? endColor : hexToRgba(endColor, endAlpha);
    
    // 중간 색상 수집
    const middleColors = [];
    const middleControls = document.querySelectorAll('.gradient-middle-control');
    middleControls.forEach(control => {
        const colorInput = control.querySelector('.gradient-middle-color-input');
        const alphaInput = control.querySelector('.gradient-middle-alpha-input');
        const position = parseFloat(control.getAttribute('data-position')) || parseFloat(control.style.left) || 50;
        if (colorInput) {
            const color = colorInput.value;
            const alpha = alphaInput ? (alphaInput.value / 100) : 1;
            const colorValue = alpha === 1 ? color : hexToRgba(color, alpha);
            middleColors.push({ color: colorValue, position });
        }
    });
    
    // 위치 순으로 정렬
    middleColors.sort((a, b) => a.position - b.position);
    
    // 그라데이션 문자열 생성
    const startRgba = hexToRgba(startColor, startAlpha);
    const endRgba = hexToRgba(endColor, endAlpha);
    
    let gradientString = `linear-gradient(${angle}deg, ${startRgba}`;
    middleColors.forEach(mc => {
        gradientString += `, ${mc.color} ${mc.position}%`;
    });
    gradientString += `, ${endRgba})`;
    
    // 버튼 그라데이션 값 저장
    const startInput = document.getElementById(`${currentButtonGradientId}_gradient_start`);
    const endInput = document.getElementById(`${currentButtonGradientId}_gradient_end`);
    const angleInputEl = document.getElementById(`${currentButtonGradientId}_gradient_angle`);
    const preview = document.getElementById(`${currentButtonGradientId}_gradient_preview`);
    
    if (startInput) startInput.value = startColorValue;
    if (endInput) endInput.value = endColorValue;
    if (angleInputEl) angleInputEl.value = angle;
    if (preview) {
        preview.style.background = gradientString;
    }
    
    // 모달 닫기
    const modal = bootstrap.Modal.getInstance(document.getElementById('gradientModal'));
    if (modal) modal.hide();
}

// 그라데이션 저장
function saveGradient() {
    // 버튼 그라데이션인 경우
    if (currentButtonGradientId) {
        saveButtonGradient();
        return;
    }
    
    // 블록 그라데이션인 경우
    if (currentBlockGradientId) {
        saveBlockGradient();
        return;
    }
    
    if (!currentGradientContainerId) return;
    
    const startColor = document.getElementById('gradient_modal_start_color').value;
    const startAlpha = document.getElementById('gradient_modal_start_alpha').value / 100;
    const endColor = document.getElementById('gradient_modal_end_color').value;
    const endAlpha = document.getElementById('gradient_modal_end_alpha').value / 100;
    const angle = document.getElementById('gradient_modal_angle').value || 90;
    
    // 투명도가 100%면 hex, 아니면 rgba로 저장
    const startColorValue = startAlpha === 1 ? startColor : hexToRgba(startColor, startAlpha);
    const endColorValue = endAlpha === 1 ? endColor : hexToRgba(endColor, endAlpha);
    
    // hidden input 업데이트
    const startInput = document.getElementById(`container_background_gradient_start_${currentGradientContainerId}`);
    const endInput = document.getElementById(`container_background_gradient_end_${currentGradientContainerId}`);
    const angleInput = document.getElementById(`container_background_gradient_angle_${currentGradientContainerId}`);
    const startInputMobile = document.getElementById(`container_background_gradient_start_mobile_${currentGradientContainerId}`);
    const endInputMobile = document.getElementById(`container_background_gradient_end_mobile_${currentGradientContainerId}`);
    const angleInputMobile = document.getElementById(`container_background_gradient_angle_mobile_${currentGradientContainerId}`);
    
    if (startInput) startInput.value = startColorValue;
    if (endInput) endInput.value = endColorValue;
    if (angleInput) angleInput.value = angle;
    if (startInputMobile) startInputMobile.value = startColorValue;
    if (endInputMobile) endInputMobile.value = endColorValue;
    if (angleInputMobile) angleInputMobile.value = angle;
    
    // 미리보기 업데이트
    const preview = document.getElementById(`container_gradient_preview_${currentGradientContainerId}`);
    const previewMobile = document.getElementById(`container_gradient_preview_mobile_${currentGradientContainerId}`);
    
    // 시작 색상 위치 가져오기
    const startControl = document.getElementById('gradient_start_control');
    const startPositionStr = startControl ? (startControl.style.left || startControl.getAttribute('data-position') || '0') : '0';
    const startPosition = parseFloat(startPositionStr.toString().replace('%', '')) || 0;
    
    // 끝 색상 위치 가져오기
    const endControl = document.getElementById('gradient_end_control');
    const endPositionStr = endControl ? (endControl.style.left || endControl.getAttribute('data-position') || '100') : '100';
    const endPosition = parseFloat(endPositionStr.toString().replace('%', '')) || 100;
    
    // 중간 색상들 가져오기
    const middleColors = [];
    const middleControls = document.querySelectorAll('.gradient-middle-control');
    middleControls.forEach((control) => {
        const colorInput = control.querySelector('.gradient-middle-color-input');
        const alphaInput = control.querySelector('.gradient-middle-alpha-input');
        const positionStr = control.style.left || control.getAttribute('data-position') || '50';
        const position = parseFloat(positionStr.toString().replace('%', '')) || 50;
        if (colorInput) {
            const hex = colorInput.value;
            const alpha = alphaInput ? (alphaInput.value / 100) : 1;
            const rgba = alpha === 1 ? hex : hexToRgba(hex, alpha);
            middleColors.push({ color: rgba, position });
        }
    });
    
    // 모든 색상 정렬 (시작, 중간, 끝)
    const allColors = [
        { color: hexToRgba(startColor, startAlpha), position: startPosition },
        ...middleColors,
        { color: hexToRgba(endColor, endAlpha), position: endPosition }
    ];
    allColors.sort((a, b) => a.position - b.position);
    
    // 그라데이션 문자열 생성
    let gradientString = `linear-gradient(${angle}deg`;
    allColors.forEach((color, index) => {
        if (index === 0) {
            gradientString += `, ${color.color} ${color.position}%`;
        } else {
            gradientString += `, ${color.color} ${color.position}%`;
        }
    });
    gradientString += `)`;
    
    if (preview) preview.style.background = gradientString;
    if (previewMobile) previewMobile.style.background = gradientString;
    
    // 서버에 저장
    updateContainerBackgroundGradient(currentGradientContainerId);
    
    // 모달 닫기 - backdrop 명시적으로 제거
    const modalElement = document.getElementById('gradientModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        modal.hide();
        // 모달이 완전히 닫힌 후 backdrop 제거
        modalElement.addEventListener('hidden.bs.modal', function() {
            // 그라데이션 모달의 backdrop만 제거 (다른 모달의 backdrop은 유지)
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                // 마지막 backdrop(그라데이션 모달의 backdrop) 제거
                backdrops[backdrops.length - 1].remove();
            }
        }, { once: true });
    }
}

// 블록 이미지 필드 토글 함수
function toggleBlockImageFields(prefix) {
    const enableCheckbox = document.getElementById(prefix === 'widget_block' ? 'widget_block_enable_image' : (prefix === 'edit_main_widget_block' ? 'edit_main_widget_block_enable_image' : 'edit_custom_page_widget_block_enable_image'));
    const imageContainer = document.getElementById(prefix === 'widget_block' ? 'widget_block_image_container' : (prefix === 'edit_main_widget_block' ? 'edit_main_widget_block_image_container' : 'edit_custom_page_widget_block_image_container'));
    
    if (enableCheckbox && imageContainer) {
        imageContainer.style.display = enableCheckbox.checked ? 'block' : 'none';
        
        // 이미지 패딩 필드들도 함께 표시/숨김
        const paddingFields = imageContainer.querySelectorAll('[id*="image_padding"]');
        paddingFields.forEach(field => {
            const fieldContainer = field.closest('.col-md-6') || field.closest('.mb-3');
            if (fieldContainer) {
                fieldContainer.style.display = enableCheckbox.checked ? '' : 'none';
            }
        });
        
        if (!enableCheckbox.checked) {
            // 이미지 필드 초기화
            const imageInput = document.getElementById(prefix === 'widget_block' ? 'widget_block_image' : (prefix === 'edit_main_widget_block' ? 'edit_main_widget_block_image' : 'edit_custom_page_widget_block_image'));
            const imageUrlInput = document.getElementById(prefix === 'widget_block' ? 'widget_block_image_url' : (prefix === 'edit_main_widget_block' ? 'edit_main_widget_block_image_url' : 'edit_custom_page_widget_block_image_url'));
            const previewContainer = document.getElementById(prefix === 'widget_block' ? 'widget_block_image_preview_container' : (prefix === 'edit_main_widget_block' ? 'edit_main_widget_block_image_preview_container' : 'edit_custom_page_widget_block_image_preview_container'));
            if (imageInput) imageInput.value = '';
            if (imageUrlInput) imageUrlInput.value = '';
            if (previewContainer) previewContainer.style.display = 'none';
        }
    }
}

// 블록 이미지 미리보기 함수
function previewBlockImage(input, previewId) {
    const previewContainer = document.getElementById(input.id.replace('_image', '_image_preview_container'));
    const previewImg = document.getElementById(previewId);
    const imageUrlInput = document.getElementById(input.id.replace('_image', '_image_url'));
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (previewImg) {
                previewImg.src = e.target.result;
            }
            if (previewContainer) {
                previewContainer.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    } else if (imageUrlInput && imageUrlInput.value) {
        // URL이 있는 경우
        if (previewImg) {
            previewImg.src = imageUrlInput.value;
        }
        if (previewContainer) {
            previewContainer.style.display = 'block';
        }
    }
}

// 블록 이미지 삭제 함수
function removeBlockImage(prefix) {
    const imageInput = document.getElementById(prefix === 'widget_block' ? 'widget_block_image' : (prefix === 'edit_main_widget_block' ? 'edit_main_widget_block_image' : 'edit_custom_page_widget_block_image'));
    const imageUrlInput = document.getElementById(prefix === 'widget_block' ? 'widget_block_image_url' : (prefix === 'edit_main_widget_block' ? 'edit_main_widget_block_image_url' : 'edit_custom_page_widget_block_image_url'));
    const previewContainer = document.getElementById(prefix === 'widget_block' ? 'widget_block_image_preview_container' : (prefix === 'edit_main_widget_block' ? 'edit_main_widget_block_image_preview_container' : 'edit_custom_page_widget_block_image_preview_container'));
    const previewImg = document.getElementById(prefix === 'widget_block' ? 'widget_block_image_preview' : (prefix === 'edit_main_widget_block' ? 'edit_main_widget_block_image_preview' : 'edit_custom_page_widget_block_image_preview'));
    const enableImageCheckbox = document.getElementById(prefix + '_enable_image');
    const imageContainer = document.getElementById(prefix + '_image_container');
    
    if (imageInput) imageInput.value = '';
    if (imageUrlInput) imageUrlInput.value = '';
    if (previewImg) previewImg.src = '';
    if (previewContainer) previewContainer.style.display = 'none';
    
    // 체크박스 해제 및 이미지 컨테이너 숨기기
    if (enableImageCheckbox) {
        enableImageCheckbox.checked = false;
    }
    if (imageContainer) {
        imageContainer.style.display = 'none';
    }
}

// 이미지 위젯 텍스트 오버레이 토글 함수
function toggleWidgetImageTextOverlay() {
    const checkbox = document.getElementById('widget_image_text_overlay');
    const container = document.getElementById('widget_image_text_overlay_container');
    const linkContainer = document.querySelector('#widget_image_container .mb-3:nth-of-type(2)');
    const newTabContainer = document.querySelector('#widget_image_container .mb-3:nth-of-type(3)');
    const hasButtonCheckbox = document.getElementById('widget_image_has_button');
    
    if (checkbox && container) {
        container.style.display = checkbox.checked ? 'block' : 'none';
        // 텍스트 오버레이가 활성화되고 버튼이 추가되면 링크 입력 숨김
        if (checkbox.checked && hasButtonCheckbox && hasButtonCheckbox.checked) {
            if (linkContainer) linkContainer.style.display = 'none';
            if (newTabContainer) newTabContainer.style.display = 'none';
        } else {
            if (linkContainer) linkContainer.style.display = 'block';
            if (newTabContainer) newTabContainer.style.display = 'block';
        }
    }
}

// 이미지 위젯 버튼 토글 함수
function toggleWidgetImageButton() {
    const checkbox = document.getElementById('widget_image_has_button');
    const container = document.getElementById('widget_image_button_container');
    const linkContainer = document.querySelector('#widget_image_container .mb-3:nth-of-type(2)');
    const newTabContainer = document.querySelector('#widget_image_container .mb-3:nth-of-type(3)');
    
    if (checkbox && container) {
        container.style.display = checkbox.checked ? 'block' : 'none';
        // 버튼이 추가되면 링크 입력 숨김
        if (checkbox.checked) {
            if (linkContainer) linkContainer.style.display = 'none';
            if (newTabContainer) newTabContainer.style.display = 'none';
        } else {
            const textOverlayCheckbox = document.getElementById('widget_image_text_overlay');
            // 텍스트 오버레이가 비활성화되어 있으면 링크 입력 표시
            if (!textOverlayCheckbox || !textOverlayCheckbox.checked) {
                if (linkContainer) linkContainer.style.display = 'block';
                if (newTabContainer) newTabContainer.style.display = 'block';
            }
        }
    }
}

// 이미지 슬라이드 모드 변경 함수 (1단 슬라이드 / 무한루프 슬라이드 상호 배타)
function handleImageSlideModeChange(clickedType) {
    const singleCheckbox = document.getElementById('widget_image_slide_single');
    const infiniteCheckbox = document.getElementById('widget_image_slide_infinite');
    const visibleCountContainer = document.getElementById('widget_image_slide_visible_count_container');
    const visibleCountMobileContainer = document.getElementById('widget_image_slide_visible_count_mobile_container');
    const gapContainer = document.getElementById('widget_image_slide_gap_container');
    const backgroundContainer = document.getElementById('widget_image_slide_background_container');
    const speedContainer = document.getElementById('widget_image_slide_speed_container');
    const directionGroup = document.getElementById('image_slide_direction_group');
    const upRadio = document.getElementById('image_slide_direction_up');
    const downRadio = document.getElementById('image_slide_direction_down');
    const upLabel = upRadio ? upRadio.nextElementSibling : null;
    const downLabel = downRadio ? downRadio.nextElementSibling : null;
    
    // 클릭된 체크박스에 따라 상호 배타적 처리
    if (clickedType === 'single' && singleCheckbox && singleCheckbox.checked) {
        // 1단 슬라이드 클릭 시 무한루프 해제
        if (infiniteCheckbox) infiniteCheckbox.checked = false;
    } else if (clickedType === 'infinite' && infiniteCheckbox && infiniteCheckbox.checked) {
        // 무한루프 클릭 시 1단 슬라이드 해제
        if (singleCheckbox) singleCheckbox.checked = false;
    }
    
    // 둘 다 체크 해제된 경우 1단 슬라이드 기본 선택
    if (singleCheckbox && infiniteCheckbox && !singleCheckbox.checked && !infiniteCheckbox.checked) {
        singleCheckbox.checked = true;
    }
    
    // 1단 슬라이드가 체크되어 있는 경우
    if (singleCheckbox && singleCheckbox.checked) {
        if (speedContainer) speedContainer.style.display = 'block';
    } else {
        if (speedContainer) speedContainer.style.display = 'none';
    }
    
    // 무한루프가 체크되어 있는 경우
    if (infiniteCheckbox && infiniteCheckbox.checked) {
        if (visibleCountContainer) visibleCountContainer.style.display = 'block';
        if (visibleCountMobileContainer) visibleCountMobileContainer.style.display = 'block';
        if (gapContainer) gapContainer.style.display = 'block';
        if (backgroundContainer) backgroundContainer.style.display = 'block';
        if (speedContainer) speedContainer.style.display = 'none';
        
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
        if (visibleCountMobileContainer) visibleCountMobileContainer.style.display = 'none';
        if (gapContainer) gapContainer.style.display = 'none';
        if (backgroundContainer) backgroundContainer.style.display = 'none';
        
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
    
    // 툴팁 초기화
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// 이미지 슬라이드 아이템 텍스트 오버레이 토글
function toggleImageSlideTextOverlay(itemIndex) {
    const checkbox = document.getElementById(`image_slide_${itemIndex}_text_overlay`);
    const container = document.getElementById(`image_slide_${itemIndex}_text_overlay_container`);
    if (checkbox && container) {
        container.style.display = checkbox.checked ? 'block' : 'none';
    }
}

// 이미지 슬라이드 버튼 토글
function toggleImageSlideButton(itemIndex) {
    const checkbox = document.getElementById(`image_slide_${itemIndex}_has_button`);
    const container = document.getElementById(`image_slide_${itemIndex}_button_container`);
    const linkContainer = document.getElementById(`image_slide_${itemIndex}_link_container`);
    const newTabContainer = document.getElementById(`image_slide_${itemIndex}_new_tab_container`);
    
    if (checkbox && container) {
        container.style.display = checkbox.checked ? 'block' : 'none';
        // 버튼이 추가되면 링크 입력 숨김
        if (checkbox.checked) {
            if (linkContainer) linkContainer.style.display = 'none';
            if (newTabContainer) newTabContainer.style.display = 'none';
        } else {
            if (linkContainer) linkContainer.style.display = 'block';
            if (newTabContainer) newTabContainer.style.display = 'block';
        }
    }
}

// 이미지 슬라이드 아이템 토글
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

// 이미지 슬라이드 아이템 제거
function removeImageSlideItem(itemIndex) {
    const item = document.getElementById(`image_slide_item_${itemIndex}`);
    if (item) item.remove();
}

// 이미지 슬라이드 이미지 제거
function removeImageSlideImage(itemIndex) {
    const imageInput = document.getElementById(`image_slide_${itemIndex}_image_input`);
    const imageUrl = document.getElementById(`image_slide_${itemIndex}_image_url`);
    const preview = document.getElementById(`image_slide_${itemIndex}_image_preview`);
    
    if (imageInput) imageInput.value = '';
    if (imageUrl) imageUrl.value = '';
    if (preview) preview.style.display = 'none';
}

// 이미지 슬라이드 배경 타입 변경
function handleImageSlideBackgroundTypeChange() {
    const bgType = document.getElementById('widget_image_slide_background_type');
    const bgColorContainer = document.getElementById('widget_image_slide_background_color_container');
    
    if (bgType && bgColorContainer) {
        bgColorContainer.style.display = bgType.value === 'color' ? 'block' : 'none';
    }
}
</script>
@endpush

<!-- 그라데이션 설정 모달 -->
<div class="modal fade" id="gradientModal" tabindex="-1" aria-labelledby="gradientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradientModalLabel">그라데이션 설정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- 그라데이션 미리보기 바 -->
                <div class="mb-4" style="position: relative;">
                    <div id="gradient_modal_preview" 
                         style="width: 100%; height: 120px; border: 1px solid #dee2e6; border-radius: 4px; background: linear-gradient(90deg, rgba(255,255,255,1), rgba(0,0,0,1)); position: relative; overflow: hidden; cursor: crosshair;">
                    </div>
                    <!-- 숨겨진 색상 컨트롤들 (데이터 관리용) -->
                    <div style="display: none;">
                        <!-- 시작 색상 컨트롤 -->
                        <div id="gradient_start_control" class="gradient-color-control" data-position="0" style="position: absolute; left: 0%;">
                            <input type="color" 
                                   id="gradient_modal_start_color" 
                                   value="#ffffff"
                                   onchange="updateGradientColorControl('start')">
                            <input type="hidden" 
                                   id="gradient_modal_start_alpha" 
                                   value="100">
                        </div>
                        
                        <!-- 중간 색상 컨트롤들 -->
                        <div id="gradient_middle_controls"></div>
                        
                        <!-- 끝 색상 컨트롤 -->
                        <div id="gradient_end_control" class="gradient-color-control" data-position="100" style="position: absolute; left: 100%;">
                            <input type="color" 
                                   id="gradient_modal_end_color" 
                                   value="#000000"
                                   onchange="updateGradientColorControl('end')">
                            <input type="hidden" 
                                   id="gradient_modal_end_alpha" 
                                   value="100">
                        </div>
                    </div>
                    <!-- 그라데이션 바 아래 컨트롤 영역 -->
                    <div id="gradient_control_panel" style="margin-top: 10px;">
                        <!-- 시작/끝 색상 아이콘 표시 영역 -->
                        <div id="gradient_start_end_controls" style="display: flex; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;">
                            <!-- 시작 색상 아이콘 -->
                            <div id="gradient_start_icon" class="gradient-control-icon" style="width: 60px; height: 40px; border: 2px solid #6c757d; border-radius: 4px; background: white; padding: 2px; cursor: pointer;" onclick="selectGradientIcon('start')">
                                <div id="gradient_start_icon_display" style="width: 100%; height: 100%; border-radius: 2px; background: #ffffff;"></div>
                            </div>
                            <!-- 끝 색상 아이콘 -->
                            <div id="gradient_end_icon" class="gradient-control-icon" style="width: 60px; height: 40px; border: 2px solid #6c757d; border-radius: 4px; background: white; padding: 2px; cursor: pointer;" onclick="selectGradientIcon('end')">
                                <div id="gradient_end_icon_display" style="width: 100%; height: 100%; border-radius: 2px; background: #000000;"></div>
                            </div>
                        </div>
                        <!-- 중간 색상 아이콘 표시 영역 -->
                        <div id="gradient_middle_icons" style="display: flex; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;"></div>
                        
                        <!-- 선택된 색상 컨트롤의 설정 -->
                        <div id="gradient_selected_control_settings" style="display: none;">
                            <div class="mb-2">
                                <label class="form-label small">색상</label>
                                <input type="color" 
                                       id="gradient_selected_color" 
                                       onchange="updateSelectedGradientControl()"
                                       class="form-control form-control-color">
                            </div>
                            <div class="mb-2" id="gradient_position_control" style="display: none;">
                                <label class="form-label small">위치</label>
                                <input type="range" 
                                       class="form-range" 
                                       id="gradient_selected_position" 
                                       min="0" 
                                       max="100" 
                                       value="0"
                                       onchange="updateSelectedGradientControlPosition()">
                                <div class="d-flex justify-content-between">
                                    <small style="font-size: 0.7rem;">0%</small>
                                    <small id="gradient_selected_position_value" style="font-size: 0.7rem;">0%</small>
                                    <small style="font-size: 0.7rem;">100%</small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">투명도</label>
                                <input type="range" 
                                       class="form-range" 
                                       id="gradient_selected_alpha" 
                                       min="0" 
                                       max="100" 
                                       value="100"
                                       onchange="updateSelectedGradientControl()">
                                <div class="d-flex justify-content-between">
                                    <small style="font-size: 0.7rem;">0%</small>
                                    <small id="gradient_selected_alpha_value" style="font-size: 0.7rem;">100%</small>
                                    <small style="font-size: 0.7rem;">100%</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" id="gradient_remove_selected" onclick="removeSelectedGradientControl()" style="display: none;">
                                <i class="bi bi-x"></i> 색상 제거
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 중간 색상 추가 버튼 -->
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="addGradientMiddleColor()">
                        <i class="bi bi-plus"></i> 중간 색상 추가
                    </button>
                    <small class="text-muted d-block mt-1">버튼을 눌러 중간 색상을 추가할 수 있습니다. 위치 슬라이더로 중간 색상의 위치를 조정할 수 있습니다.</small>
                </div>
                
                <!-- 각도 -->
                <div class="mb-3">
                    <label for="gradient_modal_angle" class="form-label">각도 <i class="bi bi-compass" style="font-size: 0.9rem;" title="각도"></i></label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" 
                               class="form-range flex-grow-1" 
                               id="gradient_modal_angle_slider" 
                               min="0" 
                               max="360" 
                               value="90"
                               onchange="document.getElementById('gradient_modal_angle').value = this.value; updateGradientPreview();">
                        <input type="number" 
                               class="form-control" 
                               id="gradient_modal_angle" 
                               value="90"
                               min="0"
                               max="360"
                               step="1"
                               style="width: 80px;"
                               onchange="document.getElementById('gradient_modal_angle_slider').value = this.value; updateGradientPreview();">
                        <span class="text-muted">도</span>
                    </div>
                    <small class="text-muted">0도: 좌→우, 90도: 상→하, 180도: 우→좌, 270도: 하→상</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveGradient()">저장</button>
            </div>
        </div>
    </div>
</div>

<!-- 컨테이너 마진 설정 모달 -->
<div class="modal fade" id="containerMarginModal" tabindex="-1" aria-labelledby="containerMarginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="containerMarginModalLabel">
                    <i class="bi bi-arrows-angle-expand me-2"></i>컨테이너 마진 설정
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="margin_modal_container_id">
                <input type="hidden" id="margin_modal_is_new" value="false">
                
                <!-- 마진 시각화 박스 -->
                <div class="d-flex justify-content-center mb-4">
                    <div style="position: relative; width: 200px; height: 200px;">
                        <!-- 외곽 박스 (마진 영역) -->
                        <div style="position: absolute; inset: 0; background: rgba(255, 193, 7, 0.3); border: 2px dashed #ffc107; display: flex; flex-direction: column; align-items: center; justify-content: space-between;">
                            <!-- 상단 마진 표시 -->
                            <div class="text-center py-1">
                                <small class="text-warning fw-bold" id="margin_preview_top">0px</small>
                            </div>
                            <!-- 중앙 (내부 박스) -->
                            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                <div class="text-center px-1">
                                    <small class="text-warning fw-bold" id="margin_preview_left">0px</small>
                                </div>
                                <div style="width: 80px; height: 80px; background: #6c757d; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <small class="text-white">컨텐츠</small>
                                </div>
                                <div class="text-center px-1">
                                    <small class="text-warning fw-bold" id="margin_preview_right">0px</small>
                                </div>
                            </div>
                            <!-- 하단 마진 표시 -->
                            <div class="text-center py-1">
                                <small class="text-warning fw-bold" id="margin_preview_bottom">24px</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">상단 마진 (px)</label>
                        <input type="number" class="form-control" id="margin_modal_top" value="0" min="0" max="500" onchange="updateMarginPreview()">
                    </div>
                    <div class="col-6">
                        <label class="form-label">하단 마진 (px)</label>
                        <input type="number" class="form-control" id="margin_modal_bottom" value="24" min="0" max="500" onchange="updateMarginPreview()">
                    </div>
                    <div class="col-6">
                        <label class="form-label">좌측 마진 (px)</label>
                        <input type="number" class="form-control" id="margin_modal_left" value="0" min="0" max="500" onchange="updateMarginPreview()">
                    </div>
                    <div class="col-6">
                        <label class="form-label">우측 마진 (px)</label>
                        <input type="number" class="form-control" id="margin_modal_right" value="0" min="0" max="500" onchange="updateMarginPreview()">
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">마진은 컨테이너 외부 여백입니다. 기본 하단 마진: 24px</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveContainerMargin()">저장</button>
            </div>
        </div>
    </div>
</div>

<!-- 컨테이너 패딩 설정 모달 -->
<div class="modal fade" id="containerPaddingModal" tabindex="-1" aria-labelledby="containerPaddingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="containerPaddingModalLabel">
                    <i class="bi bi-arrows-fullscreen me-2"></i>컨테이너 패딩 설정
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="padding_modal_container_id">
                <input type="hidden" id="padding_modal_is_new" value="false">
                
                <!-- 패딩 시각화 박스 -->
                <div class="d-flex justify-content-center mb-4">
                    <div style="position: relative; width: 200px; height: 200px;">
                        <!-- 외곽 박스 (패딩 영역) -->
                        <div style="position: absolute; inset: 0; background: rgba(13, 110, 253, 0.3); border: 2px dashed #0d6efd; display: flex; flex-direction: column; align-items: center; justify-content: space-between;">
                            <!-- 상단 패딩 표시 -->
                            <div class="text-center py-1">
                                <small class="text-primary fw-bold" id="padding_preview_top">0px</small>
                            </div>
                            <!-- 중앙 (내부 박스) -->
                            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                <div class="text-center px-1">
                                    <small class="text-primary fw-bold" id="padding_preview_left">0px</small>
                                </div>
                                <div style="width: 80px; height: 80px; background: #6c757d; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <small class="text-white">컨텐츠</small>
                                </div>
                                <div class="text-center px-1">
                                    <small class="text-primary fw-bold" id="padding_preview_right">0px</small>
                                </div>
                            </div>
                            <!-- 하단 패딩 표시 -->
                            <div class="text-center py-1">
                                <small class="text-primary fw-bold" id="padding_preview_bottom">0px</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">상단 패딩 (px)</label>
                        <input type="number" class="form-control" id="padding_modal_top" value="0" min="0" max="500" onchange="updatePaddingPreview()">
                    </div>
                    <div class="col-6">
                        <label class="form-label">하단 패딩 (px)</label>
                        <input type="number" class="form-control" id="padding_modal_bottom" value="0" min="0" max="500" onchange="updatePaddingPreview()">
                    </div>
                    <div class="col-6">
                        <label class="form-label">좌측 패딩 (px)</label>
                        <input type="number" class="form-control" id="padding_modal_left" value="0" min="0" max="500" onchange="updatePaddingPreview()">
                    </div>
                    <div class="col-6">
                        <label class="form-label">우측 패딩 (px)</label>
                        <input type="number" class="form-control" id="padding_modal_right" value="0" min="0" max="500" onchange="updatePaddingPreview()">
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">패딩은 컨테이너 내부 여백입니다.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveContainerPadding()">저장</button>
            </div>
        </div>
    </div>
</div>

@endsection


