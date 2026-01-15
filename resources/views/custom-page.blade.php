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
            // 패럴랙스가 활성화되면 transform 대신 margin으로 센터링 (transform은 fixed 배경을 깨뜨림)
            $hasParallax = ($container->background_type === 'image' && ($container->background_parallax ?? false));
            if ($isFullWidth && $hasParallax) {
                $containerStyle = 'width: 100vw; margin-left: calc(-50vw + 50%); padding: 0;';
            } else {
                $containerStyle = $isFullWidth ? 'width: 100vw; position: relative; left: 50%; transform: translateX(-50%); padding: 0;' : '';
            }
            
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
            // 컨테이너 여백은 인라인 스타일로만 적용 (Bootstrap 클래스 제거)
            $containerMarginBottom = '';
            
            // 배경 스타일 추가
            $backgroundStyle = '';
            $backgroundType = $container->background_type ?? 'none';
            if ($backgroundType === 'color' && !empty($container->background_color)) {
                $bgColor = $container->background_color;
                $bgAlpha = $container->background_color_alpha ?? 100;
                if ($bgAlpha < 100) {
                    // 투명도 적용 - hex를 rgba로 변환
                    $hex = str_replace('#', '', $bgColor);
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));
                    $backgroundStyle = 'background-color: rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . ($bgAlpha / 100) . ');';
                } else {
                    $backgroundStyle = 'background-color: ' . $bgColor . ';';
                }
            } elseif ($backgroundType === 'gradient') {
                $gradientStart = $container->background_gradient_start ?? '#ffffff';
                $gradientEnd = $container->background_gradient_end ?? '#000000';
                $gradientAngle = $container->background_gradient_angle ?? 90;
                $backgroundStyle = 'background: linear-gradient(' . $gradientAngle . 'deg, ' . $gradientStart . ', ' . $gradientEnd . ');';
            } elseif ($backgroundType === 'image' && !empty($container->background_image_url)) {
                $backgroundStyle = 'background-image: url(' . htmlspecialchars($container->background_image_url) . '); background-size: cover; background-position: center; background-repeat: no-repeat;';
                // 패럴랙스 효과
                if ($container->background_parallax ?? false) {
                    $backgroundStyle .= ' background-attachment: fixed;';
                }
            }
            
            if ($backgroundStyle) {
                $containerStyle .= ($containerStyle ? ' ' : '') . $backgroundStyle;
            }
            
            // 컨테이너 마진 추가 (상/하/좌/우)
            $marginTop = $container->margin_top ?? 0;
            $marginBottom = $container->margin_bottom ?? 24;
            $marginLeft = $container->margin_left ?? 0;
            $marginRight = $container->margin_right ?? 0;
            if ($marginTop > 0) {
                $containerStyle .= ' margin-top: ' . $marginTop . 'px !important;';
            }
            // margin_bottom은 0일 때도 적용해야 함
            $containerStyle .= ' margin-bottom: ' . $marginBottom . 'px !important;';
            if ($marginLeft > 0) {
                $containerStyle .= ' margin-left: ' . $marginLeft . 'px !important;';
            }
            if ($marginRight > 0) {
                $containerStyle .= ' margin-right: ' . $marginRight . 'px !important;';
            }
            
            // 컨테이너 패딩 추가 (상/하/좌/우)
            $paddingTop = $container->padding_top ?? 0;
            $paddingBottom = $container->padding_bottom ?? 0;
            $paddingLeft = $container->padding_left ?? 0;
            $paddingRight = $container->padding_right ?? 0;
            if ($paddingTop > 0) {
                $containerStyle .= ' padding-top: ' . $paddingTop . 'px !important;';
            }
            if ($paddingBottom > 0) {
                $containerStyle .= ' padding-bottom: ' . $paddingBottom . 'px !important;';
            }
            if ($paddingLeft > 0) {
                $containerStyle .= ' padding-left: ' . $paddingLeft . 'px !important;';
            }
            if ($paddingRight > 0) {
                $containerStyle .= ' padding-right: ' . $paddingRight . 'px !important;';
            }
        @endphp
        <div class="{{ $containerClass }} {{ $containerMarginBottom }}" style="{{ $containerStyle }}"@if($container->anchor_id) id="{{ $container->anchor_id }}"@endif>
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
                        // Bootstrap spacing 값을 px로 변환 (0=0px, 1=0.25rem=4px, 2=0.5rem=8px, 3=1rem=16px, 4=1.5rem=24px, 5=3rem=48px)
                        $spacingMap = [0 => '0px', 1 => '4px', 2 => '8px', 3 => '16px', 4 => '24px', 5 => '48px'];
                        $widgetGap = $spacingMap[$widgetSpacingValue] ?? '16px';
                    @endphp
                    @if(!$isHidden)
                        @php
                            // 위젯들이 상하를 꽉 차게 하기 위해 컬럼을 항상 flex 컨테이너로 만들기
                            $colFlexStyle = $colStyle;
                            $colFlexStyle .= ($colFlexStyle ? ' ' : '') . 'display: flex; flex-direction: column;';
                            // 위젯들 사이에 여백 추가
                            $colFlexStyle .= ' gap: ' . $widgetGap . ';';
                            if ($verticalAlign === 'center') {
                                $colFlexStyle .= ' justify-content: center;';
                            } elseif ($verticalAlign === 'bottom') {
                                $colFlexStyle .= ' justify-content: flex-end;';
                            } elseif ($verticalAlign === 'top') {
                                $colFlexStyle .= ' justify-content: flex-start;';
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
                                // 블록 위젯, 이미지 위젯, 지도 위젯인 경우 같은 row 내 컬럼들이 같은 높이를 가지도록 항상 flex: 1 적용
                                $isBlockWidget = $widget->type === 'block';
                                $isImageWidget = $widget->type === 'image';
                                $isMapWidget = $widget->type === 'map';
                                $widgetWrapperStyle = 'display: flex; flex-direction: column; width: 100%; max-width: 100%; margin-top: 0 !important; margin-bottom: 0 !important;';
                                if ($isFullHeight || $isBlockWidget || $isMapWidget) {
                                    // 세로 100%이거나 블록 위젯, 지도 위젯일 때는 항상 flex: 1 적용하여 위젯이 높이를 꽉 채우도록
                                    $widgetWrapperStyle .= ' flex: 1;';
                                } elseif ($verticalAlign === 'center' || $isImageWidget) {
                                    // 중앙 정렬이거나 이미지 위젯일 때 flex: 1 적용
                                    $widgetWrapperStyle .= ' flex: 1;';
                                    // 이미지 위젯의 경우 justify-content도 설정
                                    if ($isImageWidget) {
                                        if ($verticalAlign === 'top') {
                                            $widgetWrapperStyle .= ' justify-content: flex-start;';
                                        } elseif ($verticalAlign === 'bottom') {
                                            $widgetWrapperStyle .= ' justify-content: flex-end;';
                                        } else {
                                            $widgetWrapperStyle .= ' justify-content: center;';
                                        }
                                    }
                                }
                                $isFirstWidget = $index === 0;
                                $isLastWidget = $index === $columnWidgets->count() - 1;
                            @endphp
                            <div style="{{ $widgetWrapperStyle }}">
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

