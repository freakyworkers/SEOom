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
            $alignClass = '';
            if ($verticalAlign === 'center') {
                $alignClass = 'align-items-center';
            } elseif ($verticalAlign === 'bottom') {
                $alignClass = 'align-items-end';
            } else {
                $alignClass = 'align-items-start';
            }
            
            // 가로 100% 설정 확인 (사이드바가 없을 때만 적용)
            $isFullWidth = ($container->full_width ?? false) && ($themeSidebar === 'none');
            $isFullHeight = ($container->full_height ?? false);
            $containerClass = $isFullWidth ? 'container-fluid px-0' : '';
            $containerStyle = $isFullWidth ? 'width: 100vw; position: relative; left: 50%; transform: translateX(-50%); padding: 0;' : '';
            
            // 투명헤더가 활성화된 경우 또는 첫 번째 컨테이너가 세로 100%일 경우 상단 마진과 패딩 제거
            if (($shouldAddHeaderPadding && $index === 0) || ($isFullHeight && $index === 0)) {
                $containerStyle .= ($containerStyle ? ' ' : '') . 'margin-top: 0 !important; padding-top: 0 !important;';
            }
            
            if ($isFullHeight) {
                $containerStyle .= ($containerStyle ? ' ' : '') . 'height: 100vh; overflow: hidden;';
                $containerClass .= ' full-height-container';
            }
            
            $rowStyle = $isFullWidth ? 'margin-left: 0; margin-right: 0; width: 100%;' : '';
            if ($isFullHeight) {
                $rowStyle .= ($rowStyle ? ' ' : '') . 'height: 100%;';
            }
            
            // 컨테이너 간격 처리
            $containerSpacing = $container->widget_spacing ?? 3;
            // 이전 컨테이너 확인
            $prevContainer = $index > 0 ? $mainWidgetContainers[$index - 1] : null;
            $prevIsFullHeight = $prevContainer ? ($prevContainer->full_height ?? false) : false;
            
            // 컨테이너 여백 처리 - 세로 100%일 때도 하단 여백 유지
            // 모든 컨테이너에 동일하게 하단 여백 적용
            $containerMarginBottom = 'mb-' . min(max($containerSpacing, 0), 5);
        @endphp
        <div class="{{ $containerClass }} {{ $containerMarginBottom }}" style="{{ $containerStyle }}">
            <div class="row main-widget-container {{ $alignClass }}" data-container-id="{{ $container->id }}" style="display: flex; {{ $rowStyle }}">
                @for($i = 0; $i < $container->columns; $i++)
                    @php
                        $columnWidgets = $container->widgets->where('column_index', $i)->sortBy('order');
                        $colStyle = $isFullWidth ? 'padding-left: 0; padding-right: 0;' : '';
                        if ($isFullHeight) {
                            $colStyle .= ($colStyle ? ' ' : '') . 'height: 100%; display: flex; flex-direction: column;';
                        }
                        $colMarginBottom = $isFullHeight ? 'mb-0' : 'mb-3';
                        
                        // 위젯 간격 설정 (컨테이너별)
                        $widgetSpacing = $container->widget_spacing ?? 3;
                        $widgetSpacingClass = $isFullHeight ? 'mb-0' : 'mb-' . min(max($widgetSpacing, 0), 5);
                    @endphp
                    <div class="col-md-{{ 12 / $container->columns }} {{ $colMarginBottom }}" style="{{ $colStyle }}">
                        @foreach($columnWidgets as $index => $widget)
                            @php
                                $widgetWrapperStyle = $isFullHeight ? 'flex: 1; display: flex; flex-direction: column;' : '';
                                // 마지막 위젯이 아니면 간격 적용
                                $isLastWidget = $index === $columnWidgets->count() - 1;
                            @endphp
                            <div class="{{ !$isLastWidget && !$isFullHeight ? $widgetSpacingClass : '' }}" style="{{ $widgetWrapperStyle }}">
                                <x-main-widget :widget="$widget" :site="$site" :isFullHeight="$isFullHeight" :isFullWidth="$isFullWidth" />
                            </div>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    @endforeach
@endif
@endsection


