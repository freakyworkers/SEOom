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
                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                            <div id="containersList">
                                @if($containers->isEmpty())
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        <p>추가된 컨테이너가 없습니다. 왼쪽에서 컨테이너를 추가해주세요.</p>
                                    </div>
                                @else
                                    @foreach($containers as $container)
                                        <div class="card mb-3 container-item" data-container-id="{{ $container->id }}">
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
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    @for($i = 0; $i < $container->columns; $i++)
                                                        <div class="col-md-{{ 12 / $container->columns }} column-cell" data-column-index="{{ $i }}">
                                                            <div class="border rounded p-3" style="min-height: 200px; background-color: #f8f9fa;">
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <small class="text-muted">칸 {{ $i + 1 }}</small>
                                                                    <button type="button" 
                                                                            class="btn btn-sm btn-outline-primary" 
                                                                            onclick="showAddWidgetForm({{ $container->id }}, {{ $i }})">
                                                                        <i class="bi bi-plus-circle"></i> 위젯 추가
                                                                    </button>
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
                                                                                 data-widget-active="{{ $widget->is_active ? '1' : '0' }}">
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
                                                                                    <div class="d-flex gap-2 justify-content-end pt-2 border-top">
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
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
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
                               title="사진형 게시판, 북마크 게시판, 블로그 게시판만 선택 가능합니다."></i>
                        </label>
                        <select class="form-select" id="edit_custom_page_widget_gallery_board_id" name="gallery_board_id">
                            <option value="">선택하세요</option>
                            @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                                @if(in_array($board->type, ['photo', 'bookmark', 'blog']))
                                    <option value="{{ $board->id }}">{{ $board->name }}</option>
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
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="edit_custom_page_widget_gallery_rows" class="form-label">세로 줄수</label>
                                <select class="form-select" id="edit_custom_page_widget_gallery_rows" name="gallery_rows">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
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
                                  placeholder="HTML 코드를 입력하세요"></textarea>
                        <small class="text-muted">메인 페이지에 표시할 HTML 코드를 입력하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_custom_page_widget_block_container" style="display: none;">
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_title" class="form-label">제목</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_custom_page_widget_block_title" 
                                   name="block_title" 
                                   placeholder="제목을 입력하세요">
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
                            <select class="form-select" id="edit_custom_page_widget_block_background_type" name="block_background_type" onchange="handleEditMainBlockBackgroundTypeChange()">
                                <option value="color">컬러</option>
                                <option value="image">이미지</option>
                            </select>
                        </div>
                        <div class="mb-3" id="edit_custom_page_widget_block_color_container">
                            <label for="edit_custom_page_widget_block_background_color" class="form-label">적용 컬러</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_custom_page_widget_block_background_color" 
                                   name="block_background_color" 
                                   value="#007bff">
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_font_color" class="form-label">폰트 컬러</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_custom_page_widget_block_font_color" 
                                   name="block_font_color" 
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
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_padding_top" class="form-label">상하 여백</label>
                            <select class="form-select" id="edit_custom_page_widget_block_padding_top" name="block_padding_top">
                                <option value="0">0px</option>
                                <option value="10">10px</option>
                                <option value="20" selected>20px</option>
                                <option value="30">30px</option>
                                <option value="40">40px</option>
                                <option value="50">50px</option>
                                <option value="60">60px</option>
                                <option value="70">70px</option>
                                <option value="80">80px</option>
                                <option value="90">90px</option>
                                <option value="100">100px</option>
                                <option value="120">120px</option>
                                <option value="150">150px</option>
                                <option value="200">200px</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_page_widget_block_padding_left" class="form-label">좌우 여백</label>
                            <select class="form-select" id="edit_custom_page_widget_block_padding_left" name="block_padding_left">
                                <option value="0">0px</option>
                                <option value="10">10px</option>
                                <option value="20" selected>20px</option>
                                <option value="30">30px</option>
                                <option value="40">40px</option>
                                <option value="50">50px</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_custom_page_widget_block_show_button" 
                                       name="block_show_button"
                                       onchange="handleEditCustomPageBlockButtonToggle()">
                                <label class="form-check-label" for="edit_custom_page_widget_block_show_button">
                                    버튼 추가
                                </label>
                            </div>
                        </div>
                        <div class="mb-3" id="edit_custom_page_widget_block_button_container" style="display: none;">
                            <div class="mb-3">
                                <label for="edit_custom_page_widget_block_button_text" class="form-label">버튼 텍스트</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_custom_page_widget_block_button_text" 
                                       name="block_button_text" 
                                       placeholder="버튼 텍스트를 입력하세요">
                            </div>
                            <div class="mb-3">
                                <label for="edit_custom_page_widget_block_button_background_color" class="form-label">버튼 배경 컬러</label>
                                <input type="color" 
                                       class="form-control form-control-color" 
                                       id="edit_custom_page_widget_block_button_background_color" 
                                       name="block_button_background_color" 
                                       value="#007bff">
                            </div>
                            <div class="mb-3">
                                <label for="edit_custom_page_widget_block_button_text_color" class="form-label">버튼 텍스트 컬러</label>
                                <input type="color" 
                                       class="form-control form-control-color" 
                                       id="edit_custom_page_widget_block_button_text_color" 
                                       name="block_button_text_color" 
                                       value="#ffffff">
                            </div>
                        </div>
                        <div class="mb-3">
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
                    <div class="mb-3" id="edit_custom_page_widget_title_container_main">
                        <label for="edit_custom_page_widget_title" class="form-label">
                            위젯 제목
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

@push('scripts')
<script>
let currentContainerId = null;
let currentColumnIndex = null;
let currentEditWidgetId = null;

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
});

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
function addCustomPageWidget() {
    const form = document.getElementById('addWidgetForm');
    const formData = new FormData(form);
    
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
        const showButton = document.getElementById('widget_block_show_button')?.checked || false;
        const buttonText = formData.get('block_button_text') || '';
        const buttonBackgroundColor = formData.get('block_button_background_color') || '#007bff';
        const buttonTextColor = formData.get('block_button_text_color') || '#ffffff';
        
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
        settings.show_button = showButton;
        if (showButton) {
            settings.button_text = buttonText;
            settings.button_background_color = buttonBackgroundColor;
            settings.button_text_color = buttonTextColor;
        }
        
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
            const paddingLeft = item.querySelector('.block-slide-padding-left')?.value || '20';
            const link = item.querySelector('.block-slide-link')?.value || '';
            const openNewTab = item.querySelector('.block-slide-open-new-tab')?.checked || false;
            const fontColor = item.querySelector('.block-slide-font-color')?.value || '#ffffff';
            const titleFontSize = item.querySelector('.block-slide-title-font-size')?.value || '16';
            const contentFontSize = item.querySelector('.block-slide-content-font-size')?.value || '14';
            
            const blockItem = {
                title: title,
                content: content,
                text_align: textAlign,
                background_type: backgroundType,
                padding_top: parseInt(paddingTop),
                padding_left: parseInt(paddingLeft),
                link: link,
                open_new_tab: openNewTab,
                font_color: fontColor,
                title_font_size: titleFontSize,
                content_font_size: contentFontSize
            };
            
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
        } else {
            alert('위젯 추가에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('위젯 추가 중 오류가 발생했습니다.');
    });
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
                fetch('/site/{{ $site->slug }}/admin/toggle-menus/list')
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
                if (document.getElementById('edit_custom_page_widget_custom_html')) {
                    document.getElementById('edit_custom_page_widget_custom_html').value = settings.html || settings.custom_html || '';
                }
                if (document.getElementById('edit_custom_page_widget_title')) {
                    document.getElementById('edit_custom_page_widget_title').value = title;
                }
            } else if (widgetType === 'block') {
                if (blockContainer) blockContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                if (document.getElementById('edit_custom_page_widget_block_title')) {
                    document.getElementById('edit_custom_page_widget_block_title').value = settings.block_title || '';
                }
                if (document.getElementById('edit_custom_page_widget_block_content')) {
                    document.getElementById('edit_custom_page_widget_block_content').value = settings.block_content || '';
                }
                const textAlign = settings.text_align || 'left';
                const textAlignRadio = document.querySelector(`input[name="edit_main_block_text_align"][value="${textAlign}"]`);
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
                } else if (backgroundType === 'image') {
                    if (settings.background_image_url && document.getElementById('edit_custom_page_widget_block_image_preview_img')) {
                        document.getElementById('edit_custom_page_widget_block_image_preview_img').src = settings.background_image_url;
                        document.getElementById('edit_custom_page_widget_block_image_preview').style.display = 'block';
                        document.getElementById('edit_custom_page_widget_block_background_image').value = settings.background_image_url;
                    }
                }
                
                if (document.getElementById('edit_custom_page_widget_block_font_color')) {
                    document.getElementById('edit_custom_page_widget_block_font_color').value = settings.font_color || '#ffffff';
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
                    document.getElementById('edit_custom_page_widget_block_padding_top').value = settings.padding_top || 20;
                }
                if (document.getElementById('edit_custom_page_widget_block_padding_left')) {
                    document.getElementById('edit_custom_page_widget_block_padding_left').value = settings.padding_left || 20;
                }
                if (document.getElementById('edit_custom_page_widget_block_link')) {
                    document.getElementById('edit_custom_page_widget_block_link').value = settings.link || '';
                }
                if (document.getElementById('edit_custom_page_widget_block_open_new_tab')) {
                    document.getElementById('edit_custom_page_widget_block_open_new_tab').checked = settings.open_new_tab || false;
                }
                
                if (document.getElementById('edit_custom_page_widget_block_font_color')) {
                    document.getElementById('edit_custom_page_widget_block_font_color').value = settings.font_color || '#ffffff';
                }
                
                const showButton = settings.show_button || false;
                if (document.getElementById('edit_custom_page_widget_block_show_button')) {
                    document.getElementById('edit_custom_page_widget_block_show_button').checked = showButton;
                    handleEditCustomPageBlockButtonToggle();
                }
                if (showButton) {
                    if (document.getElementById('edit_custom_page_widget_block_button_text')) {
                        document.getElementById('edit_custom_page_widget_block_button_text').value = settings.button_text || '';
                    }
                    if (document.getElementById('edit_custom_page_widget_block_button_background_color')) {
                        document.getElementById('edit_custom_page_widget_block_button_background_color').value = settings.button_background_color || '#007bff';
                    }
                    if (document.getElementById('edit_custom_page_widget_block_button_text_color')) {
                        document.getElementById('edit_custom_page_widget_block_button_text_color').value = settings.button_text_color || '#ffffff';
                    }
                }
            } else if (widgetType === 'block_slide') {
                if (blockSlideContainer) blockSlideContainer.style.display = 'block';
                if (titleContainer) titleContainer.style.display = 'none';
                
                const slideDirection = settings.slide_direction || 'left';
                const directionRadio = document.querySelector(`input[name="edit_custom_page_block_slide_direction"][value="${slideDirection}"]`);
                if (directionRadio) directionRadio.checked = true;
                
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
                
                if (settings.image_url && document.getElementById('edit_custom_page_widget_image_preview_img')) {
                    document.getElementById('edit_custom_page_widget_image_preview_img').src = settings.image_url;
                    document.getElementById('edit_custom_page_widget_image_preview').style.display = 'block';
                    document.getElementById('edit_custom_page_widget_image_url').value = settings.image_url;
                }
                if (document.getElementById('edit_custom_page_widget_image_link')) {
                    document.getElementById('edit_custom_page_widget_image_link').value = settings.image_link || '';
                }
                if (document.getElementById('edit_custom_page_widget_image_open_new_tab')) {
                    document.getElementById('edit_custom_page_widget_image_open_new_tab').checked = settings.image_open_new_tab || false;
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
            
            // 모든 설정이 완료된 후 모달 열기
            const modal = new bootstrap.Modal(document.getElementById('customPageWidgetSettingsModal'));
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
function saveCustomPageWidgetOrder(containerId, columnIndex) {
    const widgetList = document.querySelector(`.widget-list-in-column[data-container-id="${containerId}"][data-column-index="${columnIndex}"]`);
    if (!widgetList) return;
    
    const widgets = Array.from(widgetList.querySelectorAll('.widget-item'));
    const widgetData = widgets.map((item, index) => ({
        id: parseInt(item.dataset.widgetId),
        order: index + 1
    }));
    
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
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('위젯 순서 저장 중 오류가 발생했습니다.');
        location.reload(); // 오류 시 새로고침
    });
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
        <div class="mb-3"><label class="form-label">상하 여백</label>
            <select class="form-select block-slide-padding-top" name="block_slide[${itemIndex}][padding_top]">
                <option value="0">0px</option><option value="10">10px</option><option value="20" selected>20px</option>
                <option value="30">30px</option><option value="40">40px</option><option value="50">50px</option>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">좌우 여백</label>
            <select class="form-select block-slide-padding-left" name="block_slide[${itemIndex}][padding_left]">
                <option value="0">0px</option><option value="10">10px</option><option value="20" selected>20px</option>
                <option value="30">30px</option><option value="40">40px</option><option value="50">50px</option>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">연결 링크 <small class="text-muted">(선택사항)</small></label>
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
            const slideDirectionRadio = document.querySelector('input[name="edit_custom_page_gallery_slide_direction"]:checked');
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
        const tabMenuItems = document.querySelectorAll('#edit_custom_page_tab_menu_list .edit-custom-page-tab-menu-item');
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
    } else if (widgetType === 'block') {
        const blockTitle = document.getElementById('edit_custom_page_widget_block_title')?.value;
        const blockContent = document.getElementById('edit_custom_page_widget_block_content')?.value;
        const textAlignRadio = document.querySelector('input[name="edit_custom_page_block_text_align"]:checked');
        const textAlign = textAlignRadio ? textAlignRadio.value : 'left';
        const backgroundType = document.getElementById('edit_custom_page_widget_block_background_type')?.value || 'color';
        const paddingTop = document.getElementById('edit_custom_page_widget_block_padding_top')?.value || '20';
        const paddingLeft = document.getElementById('edit_custom_page_widget_block_padding_left')?.value || '20';
        const blockLink = document.getElementById('edit_custom_page_widget_block_link')?.value;
        const openNewTab = document.getElementById('edit_custom_page_widget_block_open_new_tab')?.checked;
        const fontColor = document.getElementById('edit_custom_page_widget_block_font_color')?.value || '#ffffff';
        const titleFontSize = document.getElementById('edit_custom_page_widget_block_title_font_size')?.value || '16';
        const contentFontSize = document.getElementById('edit_custom_page_widget_block_content_font_size')?.value || '14';
        const showButton = document.getElementById('edit_custom_page_widget_block_show_button')?.checked || false;
        const buttonText = document.getElementById('edit_custom_page_widget_block_button_text')?.value || '';
        const buttonBackgroundColor = document.getElementById('edit_custom_page_widget_block_button_background_color')?.value || '#007bff';
        const buttonTextColor = document.getElementById('edit_custom_page_widget_block_button_text_color')?.value || '#ffffff';
        
        if (blockTitle) {
            settings.block_title = blockTitle;
        }
        if (blockContent) {
            settings.block_content = blockContent;
        }
        settings.text_align = textAlign;
        settings.background_type = backgroundType;
        settings.font_color = fontColor;
        settings.show_button = showButton;
        if (showButton) {
            settings.button_text = buttonText;
            settings.button_background_color = buttonBackgroundColor;
            settings.button_text_color = buttonTextColor;
        }
        
        if (backgroundType === 'color') {
            const backgroundColor = document.getElementById('edit_custom_page_widget_block_background_color')?.value || '#007bff';
            settings.background_color = backgroundColor;
        } else if (backgroundType === 'image') {
            const imageFile = document.getElementById('edit_custom_page_widget_block_image_input')?.files[0];
            if (imageFile) {
                formData.append('block_background_image_file', imageFile);
            }
            const imageUrl = document.getElementById('edit_custom_page_widget_block_background_image')?.value;
            if (imageUrl) {
                settings.background_image_url = imageUrl;
            }
        }
        
        settings.padding_top = parseInt(paddingTop);
        settings.padding_left = parseInt(paddingLeft);
        
        if (blockLink) {
            settings.link = blockLink;
        }
        settings.open_new_tab = openNewTab || false;
    } else if (widgetType === 'block_slide') {
        const slideDirection = document.querySelector('input[name="edit_custom_page_block_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        // 블록 아이템들 수집
        const blockItems = [];
        const blockSlideItems = document.querySelectorAll('.edit-custom-page-block-slide-item');
        blockSlideItems.forEach((item) => {
            const itemIndex = item.dataset.itemIndex;
            const titleInput = item.querySelector('.edit-custom-page-block-slide-title');
            const contentInput = item.querySelector('.edit-custom-page-block-slide-content');
            const textAlignRadio = item.querySelector(`input[name="edit_custom_page_block_slide[${itemIndex}][text_align]"]:checked`);
            const backgroundTypeSelect = item.querySelector('.edit-custom-page-block-slide-background-type');
            const paddingTopSelect = item.querySelector('.edit-custom-page-block-slide-padding-top');
            const paddingLeftSelect = item.querySelector('.edit-custom-page-block-slide-padding-left');
            const linkInput = item.querySelector('.edit-custom-page-block-slide-link');
            const openNewTabCheckbox = item.querySelector('.edit-custom-page-block-slide-open-new-tab');
            const fontColorInput = item.querySelector('.edit-custom-page-block-slide-font-color');
            const titleFontSizeInput = item.querySelector('.edit-custom-page-block-slide-title-font-size');
            const contentFontSizeInput = item.querySelector('.edit-custom-page-block-slide-content-font-size');
            
            const blockItem = {
                title: titleInput ? titleInput.value : '',
                content: contentInput ? contentInput.value : '',
                text_align: textAlignRadio ? textAlignRadio.value : 'left',
                background_type: backgroundTypeSelect ? backgroundTypeSelect.value : 'color',
                padding_top: paddingTopSelect ? parseInt(paddingTopSelect.value) : 20,
                padding_left: paddingLeftSelect ? parseInt(paddingLeftSelect.value) : 20,
                link: linkInput ? linkInput.value : '',
                open_new_tab: openNewTabCheckbox ? openNewTabCheckbox.checked : false,
                font_color: fontColorInput ? fontColorInput.value : '#ffffff',
                title_font_size: titleFontSizeInput ? titleFontSizeInput.value : '16',
                content_font_size: contentFontSizeInput ? contentFontSizeInput.value : '14'
            };
            
            if (blockItem.background_type === 'color') {
                const backgroundColorInput = item.querySelector('.edit-custom-page-block-slide-background-color');
                blockItem.background_color = backgroundColorInput ? backgroundColorInput.value : '#007bff';
            } else if (blockItem.background_type === 'image') {
                const imageFileInput = item.querySelector(`#edit_custom_page_block_slide_${itemIndex}_image_input`);
                if (imageFileInput && imageFileInput.files[0]) {
                    formData.append(`edit_custom_page_block_slide[${itemIndex}][background_image_file]`, imageFileInput.files[0]);
                }
                const imageUrlInput = item.querySelector(`#edit_custom_page_block_slide_${itemIndex}_background_image_url`);
                if (imageUrlInput && imageUrlInput.value) {
                    blockItem.background_image_url = imageUrlInput.value;
                }
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
        const imageLink = document.getElementById('edit_custom_page_widget_image_link')?.value;
        if (imageLink) {
            settings.image_link = imageLink;
        }
        const imageOpenNewTab = document.getElementById('edit_custom_page_widget_image_open_new_tab')?.checked;
        settings.image_open_new_tab = imageOpenNewTab || false;
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
            throw new Error('Network response was not ok');
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
            <input type="text" 
                   class="form-control edit-custom-page-block-slide-title" 
                   name="edit_main_block_slide[${itemIndex}][title]" 
                   placeholder="제목을 입력하세요"
                   value="${blockData ? (blockData.title || '') : ''}">
        </div>
        <div class="mb-3">
            <label class="form-label">내용</label>
            <textarea class="form-control edit-custom-page-block-slide-content" 
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
                   class="form-control edit-custom-page-block-slide-title-font-size" 
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
                   class="form-control edit-custom-page-block-slide-content-font-size" 
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
            <select class="form-select edit-custom-page-block-slide-background-type" name="edit_main_block_slide[${itemIndex}][background_type]" onchange="handleEditMainBlockSlideBackgroundTypeChange(${itemIndex})">
                <option value="color" ${!blockData || blockData.background_type === 'color' ? 'selected' : ''}>컬러</option>
                <option value="image" ${blockData && blockData.background_type === 'image' ? 'selected' : ''}>이미지</option>
            </select>
        </div>
        <div class="mb-3 edit-custom-page-block-slide-color-container" id="edit_main_block_slide_${itemIndex}_color_container" style="${blockData && blockData.background_type === 'image' ? 'display: none;' : ''}">
            <label class="form-label">배경 컬러</label>
            <input type="color" 
                   class="form-control form-control-color edit-custom-page-block-slide-background-color" 
                   name="edit_main_block_slide[${itemIndex}][background_color]" 
                   value="${blockData ? (blockData.background_color || '#007bff') : '#007bff'}">
        </div>
        <div class="mb-3">
            <label class="form-label">폰트 컬러</label>
            <input type="color" 
                   class="form-control form-control-color edit-custom-page-block-slide-font-color" 
                   name="edit_main_block_slide[${itemIndex}][font_color]" 
                   value="${blockData ? (blockData.font_color || '#ffffff') : '#ffffff'}">
        </div>
        <div class="mb-3 edit-custom-page-block-slide-image-container" id="edit_main_block_slide_${itemIndex}_image_container" style="${!blockData || blockData.background_type !== 'image' ? 'display: none;' : ''}">
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
                <input type="hidden" class="edit-custom-page-block-slide-background-image-url" name="edit_main_block_slide[${itemIndex}][background_image_url]" id="edit_main_block_slide_${itemIndex}_background_image_url" value="${blockData && blockData.background_image_url ? blockData.background_image_url : ''}">
                <div class="edit-custom-page-block-slide-image-preview" id="edit_main_block_slide_${itemIndex}_image_preview" style="${blockData && blockData.background_image_url ? '' : 'display: none;'}">
                    <img id="edit_main_block_slide_${itemIndex}_image_preview_img" src="${blockData && blockData.background_image_url ? blockData.background_image_url : ''}" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditMainBlockSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">상하 여백</label>
            <select class="form-select edit-custom-page-block-slide-padding-top" name="edit_main_block_slide[${itemIndex}][padding_top]">
                <option value="0" ${blockData && blockData.padding_top === 0 ? 'selected' : ''}>0px</option>
                <option value="10" ${blockData && blockData.padding_top === 10 ? 'selected' : ''}>10px</option>
                <option value="20" ${!blockData || blockData.padding_top === 20 ? 'selected' : ''}>20px</option>
                <option value="30" ${blockData && blockData.padding_top === 30 ? 'selected' : ''}>30px</option>
                <option value="40" ${blockData && blockData.padding_top === 40 ? 'selected' : ''}>40px</option>
                <option value="50" ${blockData && blockData.padding_top === 50 ? 'selected' : ''}>50px</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">좌우 여백</label>
            <select class="form-select edit-custom-page-block-slide-padding-left" name="edit_main_block_slide[${itemIndex}][padding_left]">
                <option value="0" ${blockData && blockData.padding_left === 0 ? 'selected' : ''}>0px</option>
                <option value="10" ${blockData && blockData.padding_left === 10 ? 'selected' : ''}>10px</option>
                <option value="20" ${!blockData || blockData.padding_left === 20 ? 'selected' : ''}>20px</option>
                <option value="30" ${blockData && blockData.padding_left === 30 ? 'selected' : ''}>30px</option>
                <option value="40" ${blockData && blockData.padding_left === 40 ? 'selected' : ''}>40px</option>
                <option value="50" ${blockData && blockData.padding_left === 50 ? 'selected' : ''}>50px</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">연결 링크 <small class="text-muted">(선택사항)</small></label>
            <input type="url" 
                   class="form-control edit-custom-page-block-slide-link" 
                   name="edit_main_block_slide[${itemIndex}][link]" 
                   placeholder="https://example.com"
                   value="${blockData ? (blockData.link || '') : ''}">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input edit-custom-page-block-slide-open-new-tab" 
                       type="checkbox" 
                       name="edit_main_block_slide[${itemIndex}][open_new_tab]"
                       id="edit_main_block_slide_${itemIndex}_open_new_tab"
                       ${blockData && blockData.open_new_tab ? 'checked' : ''}>
                <label class="form-check-label" for="edit_main_block_slide_${itemIndex}_open_new_tab">
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
    const imageContainer = document.getElementById('edit_custom_page_widget_block_image_container');
    
    if (backgroundType === 'image') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'block';
    } else {
        if (colorContainer) colorContainer.style.display = 'block';
        if (imageContainer) imageContainer.style.display = 'none';
    }
}

function handleEditCustomPageBlockButtonToggle() {
    const showButton = document.getElementById('edit_custom_page_widget_block_show_button')?.checked;
    const buttonContainer = document.getElementById('edit_custom_page_widget_block_button_container');
    if (buttonContainer) {
        buttonContainer.style.display = showButton ? 'block' : 'none';
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
</script>
@endpush
@endsection


