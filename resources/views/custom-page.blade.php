@extends('layouts.app')

@section('title', $customPage->name)

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
            $containerClass = $isFullWidth ? 'container-fluid px-0' : '';
            $containerStyle = $isFullWidth ? 'width: 100vw; position: relative; left: 50%; transform: translateX(-50%); padding: 0;' : '';
            $rowStyle = $isFullWidth ? 'margin-left: 0; margin-right: 0; width: 100%;' : '';
        @endphp
        <div class="{{ $containerClass }} mb-4" style="{{ $containerStyle }}">
            <div class="row custom-page-widget-container {{ $alignClass }}" data-container-id="{{ $container->id }}" style="display: flex; {{ $rowStyle }}">
                @for($i = 0; $i < $container->columns; $i++)
                    @php
                        $columnWidgets = $container->widgets->where('column_index', $i)->sortBy('order');
                        $colStyle = $isFullWidth ? 'padding-left: 0; padding-right: 0;' : '';
                    @endphp
                    <div class="col-md-{{ 12 / $container->columns }} mb-3" style="{{ $colStyle }}">
                        @foreach($columnWidgets as $widget)
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
                            @endphp
                            <x-main-widget :widget="$widgetData" :site="$site" />
                        @endforeach
                    </div>
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

