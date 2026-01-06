@extends('layouts.app')

{{-- 타이틀을 설정하지 않으면 사이트 이름이 기본 타이틀로 표시됩니다 --}}

{{-- 첫 번째 컨테이너가 세로 100%일 때 main 영역 상단 여백 제거 --}}
@php
    $firstContainerIsFullHeight = isset($mainWidgetContainers) && $mainWidgetContainers->isNotEmpty() && ($mainWidgetContainers->first()->full_height ?? false);
@endphp

@if($firstContainerIsFullHeight)
@push('styles')
<style>
    /* 첫 번째 컨테이너가 세로 100%일 때 main 영역 상단 여백 완전 제거 */
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
{{-- 메인 위젯 표시 --}}
@if(isset($mainWidgetContainers) && $mainWidgetContainers->isNotEmpty())
    @php
        // 투명헤더 설정 확인 (첫 번째 컨테이너에 padding-top 추가용)
        $headerTransparent = $site->getSetting('header_transparent', '0') == '1';
        $themeSidebar = $site->getSetting('theme_sidebar', 'left');
        $hasSidebar = $themeSidebar !== 'none';
        if ($hasSidebar) {
            $headerTransparent = false;
        }
        // 메인 페이지인지 확인 - 루트 경로(/)와 /site/{site} 모두 HomeController::index를 호출하므로 동일하게 처리
        $currentPath = request()->path();
        $currentHost = request()->getHost();
        $isCustomDomain = $site->domain && ($currentHost === $site->domain || $currentHost === 'www.' . $site->domain);
        
        // 루트 경로(/)와 /site/{site} 모두 메인 페이지로 간주
        // 커스텀 도메인을 연결한 경우 루트 경로가 메인 페이지
        $isHomePage = request()->routeIs('home') 
            || request()->routeIs('home.root')  // 루트 경로 라우트 이름
            || $currentPath === '/' 
            || $currentPath === ''
            || ($isCustomDomain && ($currentPath === '/' || $currentPath === ''))
            || (request()->segment(1) === 'site' && request()->segment(2) !== null && request()->segment(3) === null);
        
        // 추가 확인: 현재 라우트의 액션이 HomeController::index인지 확인
        $currentRoute = request()->route();
        if ($currentRoute && !$isHomePage) {
            $action = $currentRoute->getActionName();
            if ($action === 'App\\Http\\Controllers\\HomeController@index' || 
                $action === 'App\\Http\\Controllers\\HomeController::index' ||
                (is_string($action) && str_contains($action, 'HomeController') && str_contains($action, 'index'))) {
                $isHomePage = true;
            }
        }
        $shouldAddHeaderPadding = $headerTransparent && $isHomePage;
        
        // 헤더 높이 추정 (실제 헤더 높이는 JavaScript로 계산)
        $estimatedHeaderHeight = 80; // 기본 헤더 높이 (px)
    @endphp
    @foreach($mainWidgetContainers as $index => $container)
        @php
            $verticalAlign = $container->vertical_align ?? 'top';
            // 같은 row 내 컬럼들이 같은 높이를 가지도록 항상 stretch 사용
            $alignClass = 'align-items-stretch';
            
            // 가로 100% 설정 확인 (사이드바가 없을 때만 적용)
            $isFullWidth = ($container->full_width ?? false) && ($themeSidebar === 'none');
            $isFullHeight = ($container->full_height ?? false);
            // 칸 고정너비 설정 확인 (가로 100%일 때만 적용)
            $fixedWidthColumns = ($container->fixed_width_columns ?? false) && $isFullWidth;
            
            $containerClass = $isFullWidth ? 'container-fluid px-0' : '';
            $containerStyle = $isFullWidth ? 'width: 100vw; position: relative; left: 50%; transform: translateX(-50%); padding: 0;' : '';
            
            // 투명헤더가 활성화된 경우 또는 첫 번째 컨테이너가 세로 100%일 경우 상단 마진과 패딩 제거
            // 가로 100% 컨테이너의 경우 상단 여백 제거
            if (($shouldAddHeaderPadding && $index === 0) || ($isFullHeight && $index === 0) || $isFullWidth) {
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
            
            // 컨테이너 간격 처리
            $containerSpacing = $container->widget_spacing ?? 3;
            // 이전 컨테이너 확인
            $prevContainer = $index > 0 ? $mainWidgetContainers[$index - 1] : null;
            $prevIsFullHeight = $prevContainer ? ($prevContainer->full_height ?? false) : false;
            
            // 컨테이너 여백은 인라인 스타일로만 적용 (Bootstrap 클래스 제거)
            $containerMarginBottom = '';
            
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
                // 패럴랙스 효과
                if ($container->background_parallax ?? false) {
                    $backgroundStyle .= ' background-attachment: fixed;';
                }
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
            
            if ($backgroundStyle) {
                $containerStyle .= ($containerStyle ? ' ' : '') . $backgroundStyle;
            }
        @endphp
        <div class="{{ $containerClass }} {{ $containerMarginBottom }}" style="{{ $containerStyle }}">
            @if($fixedWidthColumns)
            {{-- 칸 고정너비: 배경은 100%지만 칸들은 고정 너비 유지 --}}
            <div class="container">
            @endif
            <div class="row main-widget-container {{ $alignClass }}" data-container-id="{{ $container->id }}" style="display: flex; {{ $rowStyle }}">
                @php
                    $columnMerges = $container->column_merges ?? [];
                    $hiddenColumns = [];
                    foreach ($columnMerges as $startCol => $span) {
                        for ($j = 1; $j < $span; $j++) {
                            $hiddenColumns[] = $startCol + $j;
                        }
                    }
                    // 컨테이너 내 모든 칸의 위젯 개수 확인
                    $columnWidgetCounts = [];
                    for ($i = 0; $i < $container->columns; $i++) {
                        if (!in_array($i, $hiddenColumns)) {
                            $colWidgets = $container->widgets->where('column_index', $i)->sortBy('order');
                            $columnWidgetCounts[$i] = $colWidgets->count();
                        }
                    }
                    // 모든 칸의 위젯 개수가 같은지 확인
                    $uniqueWidgetCounts = array_unique(array_values($columnWidgetCounts));
                    $allColumnsHaveSameWidgetCount = count($uniqueWidgetCounts) === 1 && !empty($uniqueWidgetCounts);
                @endphp
                @for($i = 0; $i < $container->columns; $i++)
                    @php
                        $isHidden = in_array($i, $hiddenColumns);
                        $mergeSpan = $columnMerges[$i] ?? 1;
                        $colWidth = $mergeSpan * (12 / $container->columns);
                        $columnWidgets = $container->widgets->where('column_index', $i)->sortBy('order');
                        // 가로 100%이고 칸 고정너비가 아닐 때만 padding 제거, 일반적인 경우와 칸 고정너비일 때는 Bootstrap gutter 유지
                        $colStyle = ($isFullWidth && !$fixedWidthColumns) ? 'padding-left: 0; padding-right: 0;' : '';
                        if ($isFullHeight) {
                            $colStyle .= ($colStyle ? ' ' : '') . 'height: 100%; display: flex; flex-direction: column;';
                        }
                        // 컬럼 간 여백은 항상 유지 (가로 100%이고 칸 고정너비가 아닐 때만 제거)
                        $colMarginBottom = ($isFullWidth && !$fixedWidthColumns) ? 'mb-0' : ($isFullHeight ? 'mb-0' : 'mb-3');
                        
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
                                <x-main-widget :widget="$widget" :site="$site" :isFullHeight="$isFullHeight" :isFullWidth="$isFullWidth" :fixedWidthColumns="$fixedWidthColumns" :isFirstWidget="$isFirstWidget" :isLastWidget="$isLastWidget" :verticalAlign="$verticalAlign" />
                            </div>
                        @endforeach
                        </div>
                    @endif
                @endfor
            </div>
            @if($fixedWidthColumns)
            </div>
            @endif
        </div>
    @endforeach
@endif
@endsection


