@extends('layouts.app')

{{-- 타이틀을 설정하지 않으면 사이트 이름이 기본 타이틀로 표시됩니다 --}}

@section('content')
{{-- 메인 위젯 표시 --}}
@if(isset($mainWidgetContainers) && $mainWidgetContainers->isNotEmpty())
    @foreach($mainWidgetContainers as $container)
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
            $containerClass = $isFullWidth ? 'container-fluid px-0' : '';
            $containerStyle = $isFullWidth ? 'width: 100vw; position: relative; left: 50%; transform: translateX(-50%); padding: 0;' : '';
            if ($isFullHeight) {
                $containerStyle .= ($containerStyle ? ' ' : '') . 'height: 100vh; overflow: hidden;';
                $containerClass .= ' full-height-container';
            }
            $rowStyle = $isFullWidth ? 'margin-left: 0; margin-right: 0; width: 100%;' : '';
            if ($isFullHeight) {
                $rowStyle .= ($rowStyle ? ' ' : '') . 'height: 100%;';
            }
            $containerMarginBottom = $isFullHeight ? 'mb-0' : 'mb-4';
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
                    @endphp
                    <div class="col-md-{{ 12 / $container->columns }} {{ $colMarginBottom }}" style="{{ $colStyle }}">
                        @foreach($columnWidgets as $widget)
                            <x-main-widget :widget="$widget" :site="$site" :isFullHeight="$isFullHeight" />
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    @endforeach
@endif
@endsection


