@extends('layouts.app')

{{-- 타이틀을 설정하지 않으면 사이트 이름이 기본 타이틀로 표시됩니다 --}}

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
        $isHomePage = request()->routeIs('home');
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
            
            // 투명헤더가 활성화된 경우 첫 번째 컨테이너의 상단 마진과 패딩 제거 (헤더가 오버레이되므로)
            if ($shouldAddHeaderPadding && $index === 0) {
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
            
            // 첫 번째 컨테이너가 full_height이고 두 번째 컨테이너가 일반 컨테이너인 경우 여백 적용
            // 이전 컨테이너가 full_height이고 현재 컨테이너가 일반 컨테이너인 경우 여백 적용
            if ($prevIsFullHeight && !$isFullHeight) {
                // 이전 컨테이너가 full_height이고 현재 컨테이너가 일반 컨테이너인 경우 여백 적용
                $containerMarginBottom = 'mb-' . min(max($containerSpacing, 0), 5);
            } elseif (!$prevIsFullHeight && !$isFullHeight) {
                // 둘 다 일반 컨테이너인 경우 여백 적용
                $containerMarginBottom = 'mb-' . min(max($containerSpacing, 0), 5);
            } else {
                // 그 외의 경우 여백 없음 (현재 컨테이너가 full_height이거나 이전 컨테이너가 일반 컨테이너인 경우)
                $containerMarginBottom = 'mb-0';
            }
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
                                <x-main-widget :widget="$widget" :site="$site" :isFullHeight="$isFullHeight" />
                            </div>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    @endforeach
@endif
@endsection


