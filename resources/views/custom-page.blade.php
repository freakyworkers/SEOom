@extends('layouts.app')

@section('title', $customPage->name)

{{-- 첫 번째 컨테이너가 세로 100% 또는 가로 100%일 때 main 영역 상단 여백 제거 --}}
@php
    $themeSidebar = $site->getSetting('theme_sidebar', 'left');
    $firstContainerIsFullHeight = isset($containers) && $containers->isNotEmpty() && ($containers->first()->full_height ?? false);
    $firstContainerIsFullWidth = isset($containers) && $containers->isNotEmpty() && ($containers->first()->full_width ?? false) && ($themeSidebar === 'none');
@endphp

@if($firstContainerIsFullHeight || $firstContainerIsFullWidth)
@push('styles')
<style>
    /* 첫 번째 컨테이너가 세로 100% 또는 가로 100%일 때 main 영역 상단 여백 완전 제거 */
    body main.container,
    body main.container.my-4,
    body > main.container,
    body > main.container.my-4,
    .d-flex main.container,
    .d-flex main.container.my-4 {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    /* full-height-container의 상단 여백도 제거 */
    .full-height-container:first-child {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
</style>
@endpush
@endif

@section('content')
{{-- 커스텀 페이지 위젯 표시 --}}
@if(isset($containers) && $containers->isNotEmpty())
    @foreach($containers as $container)
        @php
            $verticalAlign = $container->vertical_align ?? 'top';
            $alignClass = '';
            if ($verticalAlign === 'center') {
                $alignClass = 'align-items-center';
            } elseif ($verticalAlign === 'bottom') {
                $alignClass = 'align-items-end';
            } else {
                $alignClass = 'align-items-start';
            }
            
            // 가로 100% 설정 확인 (사이드바가 없을 때만 적용)
            $themeSidebar = $site->getSetting('theme_sidebar', 'left');
            $isFullWidth = ($container->full_width ?? false) && ($themeSidebar === 'none');
            $isFullHeight = ($container->full_height ?? false);
            // 칸 고정너비 설정 확인 (가로 100%일 때만 적용)
            $fixedWidthColumns = ($container->fixed_width_columns ?? false) && $isFullWidth;
            
            $containerClass = $isFullWidth ? 'container-fluid px-0' : '';
            $containerStyle = $isFullWidth ? 'width: 100vw; position: relative; left: 50%; transform: translateX(-50%); padding: 0;' : '';
            
            // 가로 100% 컨테이너의 경우 상단 여백 제거
            if ($isFullWidth) {
                $containerStyle .= ($containerStyle ? ' ' : '') . 'margin-top: 0 !important; padding-top: 0 !important;';
            }
            
            if ($isFullHeight) {
                $containerStyle .= ($containerStyle ? ' ' : '') . 'height: 100vh; overflow: hidden;';
                $containerClass .= ' full-height-container';
            }
            
            // 칸 고정너비가 아닐 때만 row에 100% 적용
            $rowStyle = ($isFullWidth && !$fixedWidthColumns) ? 'margin-left: 0; margin-right: 0; width: 100%;' : '';
            if ($isFullHeight) {
                $rowStyle .= ($rowStyle ? ' ' : '') . 'height: 100%;';
            }
            $containerMarginBottom = 'mb-4';
            
            // 배경 스타일 추가
            $backgroundStyle = '';
            $backgroundType = $container->background_type ?? 'none';
            if ($backgroundType === 'color' && !empty($container->background_color)) {
                $backgroundStyle = 'background-color: ' . $container->background_color . ';';
            } elseif ($backgroundType === 'gradient') {
                $gradientStart = $container->background_gradient_start ?? '#ffffff';
                $gradientEnd = $container->background_gradient_end ?? '#000000';
                $gradientAngle = $container->background_gradient_angle ?? 90;
                $backgroundStyle = 'background: linear-gradient(' . $gradientAngle . 'deg, ' . $gradientStart . ', ' . $gradientEnd . ');';
            } elseif ($backgroundType === 'image' && !empty($container->background_image_url)) {
                $backgroundStyle = 'background-image: url(' . htmlspecialchars($container->background_image_url) . '); background-size: cover; background-position: center; background-repeat: no-repeat;';
            }
            
            if ($backgroundStyle) {
                $containerStyle .= ($containerStyle ? ' ' : '') . $backgroundStyle;
            }
            
            // 컨테이너 상단/하단 마진 추가
            $marginTop = $container->margin_top ?? 0;
            $marginBottom = $container->margin_bottom ?? 24;
            if ($marginTop > 0) {
                $containerStyle .= ' margin-top: ' . $marginTop . 'px !important;';
            }
            if ($marginBottom > 0) {
                $containerStyle .= ' margin-bottom: ' . $marginBottom . 'px !important;';
            }
        @endphp
        <div class="{{ $containerClass }} {{ $containerMarginBottom }}" style="{{ $containerStyle }}">
            <div class="row custom-page-widget-container {{ $alignClass }}{{ $fixedWidthColumns ? ' container mx-auto' : '' }}" data-container-id="{{ $container->id }}" style="display: flex; {{ $rowStyle }}">
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
                        $columnWidgets = $container->widgets->where('column_index', $i)->sortBy('order');
                        // 가로 100%이고 칸 고정너비가 비활성화일 때만 padding 제거
                        $colStyle = ($isFullWidth && !$fixedWidthColumns) ? 'padding-left: 0; padding-right: 0;' : '';
                        if ($isFullHeight) {
                            $colStyle .= ($colStyle ? ' ' : '') . 'height: 100%; display: flex; flex-direction: column;';
                        }
                        // 컬럼 간 여백은 항상 유지 (가로 100%가 아닐 때만)
                        $colMarginBottom = $isFullWidth ? 'mb-0' : ($isFullHeight ? 'mb-0' : 'mb-3');
                        
                        // 위젯 간격 설정 (컨테이너별)
                        $widgetSpacing = $container->widget_spacing ?? 3;
                        $widgetSpacingValue = min(max($widgetSpacing, 0), 5);
                        // 첫 번째 위젯이 아닐 때만 상단 마진 적용, 하단 마진은 제거
                        $widgetSpacingClass = $isFullHeight ? 'mb-0 mt-0' : 'mb-0';
                        $widgetSpacingTopClass = $isFullHeight ? 'mt-0' : 'mt-' . $widgetSpacingValue;
                    @endphp
                    @if(!$isHidden)
                        @php
                            // 세로 정렬을 위해 컬럼을 flex 컨테이너로 만들기
                            $colFlexStyle = $colStyle;
                            if ($verticalAlign === 'center' || $verticalAlign === 'bottom' || $verticalAlign === 'top') {
                                $colFlexStyle .= ($colFlexStyle ? ' ' : '') . 'display: flex; flex-direction: column;';
                                if ($verticalAlign === 'center') {
                                    $colFlexStyle .= ' justify-content: center;';
                                } elseif ($verticalAlign === 'bottom') {
                                    $colFlexStyle .= ' justify-content: flex-end;';
                                } elseif ($verticalAlign === 'top') {
                                    $colFlexStyle .= ' justify-content: flex-start;';
                                }
                            }
                        @endphp
                        <div class="col-md-{{ $colWidth }} {{ $colMarginBottom }}" style="{{ $colFlexStyle }}">
                        @foreach($columnWidgets as $index => $widget)
                            @php
                                // CustomPageWidget을 MainWidget과 호환되도록 변환
                                $widgetData = (object)[
                                    'id' => $widget->id,
                                    'type' => $widget->type,
                                    'title' => $widget->title,
                                    'settings' => $widget->settings,
                                    'is_active' => $widget->is_active,
                                    'order' => $widget->order,
                                ];
                                // 블록 위젯인 경우 같은 row 내 컬럼들이 같은 높이를 가지도록 항상 flex: 1 적용
                                $isBlockWidget = $widget->type === 'block';
                                $widgetWrapperStyle = 'display: flex; flex-direction: column; width: 100%; max-width: 100%; margin-top: 0 !important; margin-bottom: 0 !important;';
                                if ($isFullHeight || $isBlockWidget) {
                                    // 세로 100%이거나 블록 위젯일 때는 항상 flex: 1 적용하여 위젯이 높이를 꽉 채우도록
                                    $widgetWrapperStyle .= ' flex: 1;';
                                } elseif ($verticalAlign === 'center') {
                                    // 중앙 정렬일 때만 flex: 1 적용
                                    $widgetWrapperStyle .= ' flex: 1;';
                                }
                                // 첫 번째 위젯과 마지막 위젯의 마진 처리
                                $isFirstWidget = $index === 0;
                                $isLastWidget = $index === $columnWidgets->count() - 1;
                                $widgetMarginClass = '';
                                $widgetWrapperStyleMargin = '';
                                if (!$isFullHeight) {
                                    // 첫 번째 위젯은 상단 마진 0
                                    if ($isFirstWidget) {
                                        $widgetMarginClass = 'mt-0';
                                        $widgetWrapperStyleMargin = 'margin-top: 0 !important;';
                                    } else {
                                        // 첫 번째가 아닌 위젯은 상단 간격 적용
                                        $widgetMarginClass .= $widgetSpacingTopClass;
                                    }
                                    // 마지막 위젯은 하단 마진 0
                                    if ($isLastWidget) {
                                        $widgetMarginClass .= ($widgetMarginClass ? ' ' : '') . 'mb-0';
                                        $widgetWrapperStyleMargin .= ($widgetWrapperStyleMargin ? ' ' : '') . 'margin-bottom: 0 !important;';
                                    }
                                }
                                if ($widgetWrapperStyleMargin) {
                                    $widgetWrapperStyle .= ($widgetWrapperStyle ? ' ' : '') . $widgetWrapperStyleMargin;
                                }
                            @endphp
                            <div class="{{ $widgetMarginClass }}" style="{{ $widgetWrapperStyle }}">
                                <x-main-widget :widget="$widgetData" :site="$site" :isFullHeight="$isFullHeight" :isFullWidth="$isFullWidth" :isFirstWidget="$isFirstWidget" :isLastWidget="$isLastWidget" :verticalAlign="$verticalAlign" />
                            </div>
                        @endforeach
                        </div>
                    @endif
                @endfor
            </div>
        </div>
    @endforeach
@else
    {{-- 위젯이 없을 때 기본 메시지 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-5 text-center">
                    <h1 class="h3 mb-3">{{ $customPage->name }}</h1>
                    <p class="text-muted mb-0">{{ $customPage->description ?? '위젯이 아직 추가되지 않았습니다.' }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

