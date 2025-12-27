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
            $isFullHeight = ($container->full_height ?? false);
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
            $rowStyle = $isFullWidth ? 'margin-left: 0; margin-right: 0; width: 100%;' : '';
            if ($isFullHeight) {
                $rowStyle .= ($rowStyle ? ' ' : '') . 'height: 100%;';
            }
            $containerMarginBottom = 'mb-4';
        @endphp
        <div class="{{ $containerClass }} {{ $containerMarginBottom }}" style="{{ $containerStyle }}">
            <div class="row custom-page-widget-container {{ $alignClass }}" data-container-id="{{ $container->id }}" style="display: flex; {{ $rowStyle }}">
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
                        $colStyle = $isFullWidth ? 'padding-left: 0; padding-right: 0;' : '';
                        if ($isFullHeight) {
                            $colStyle .= ($colStyle ? ' ' : '') . 'height: 100%; display: flex; flex-direction: column;';
                        }
                        $colMarginBottom = $isFullHeight ? 'mb-0' : 'mb-3';
                    @endphp
                    @if(!$isHidden)
                        <div class="col-md-{{ $colWidth }} {{ $colMarginBottom }}" style="{{ $colStyle }}">
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
                                $widgetWrapperStyle = $isFullHeight ? 'flex: 1; display: flex; flex-direction: column;' : '';
                            @endphp
                            <div style="{{ $widgetWrapperStyle }}">
                                <x-main-widget :widget="$widgetData" :site="$site" :isFullHeight="$isFullHeight" :isFullWidth="$isFullWidth" />
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

