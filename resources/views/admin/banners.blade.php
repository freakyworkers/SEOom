@extends('layouts.admin')

@section('title', '배너')
@section('page-title', '배너')
@section('page-subtitle', '배너 위치별 설정을 관리할 수 있습니다')


@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-image me-2"></i>배너 위치별 설정</h5>
    </div>
    <div class="card-body">
        <form id="bannerSettingsForm">
            @csrf
            {{-- 통일된 여백 설정 테이블 --}}
            <div class="card mb-4" style="background-color: #f8f9fa;">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-layout-text-window me-2"></i>배너 여백 설정</h6>
                    {{-- 데스크탑 버전 --}}
                    <div class="row align-items-center d-none d-md-flex">
                        <div class="col-md-2">
                            <label class="form-label mb-0"><strong>여백</strong></label>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label mb-1">데스크탑 (px)</label>
                            <select class="form-select" name="banner_desktop_gap" id="banner_desktop_gap">
                                @for($i = 0; $i <= 50; $i += 2)
                                    <option value="{{ $i }}" {{ ($site->getSetting('banner_desktop_gap', 16) == $i) ? 'selected' : '' }}>{{ $i }}px</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label mb-1">모바일 (px)</label>
                            <select class="form-select" name="banner_mobile_gap" id="banner_mobile_gap">
                                @for($i = 0; $i <= 30; $i += 2)
                                    <option value="{{ $i }}" {{ ($site->getSetting('banner_mobile_gap', 8) == $i) ? 'selected' : '' }}>{{ $i }}px</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    {{-- 모바일 버전 --}}
                    <div class="d-md-none">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small mb-1">데스크탑 (px)</label>
                                <select class="form-select form-select-sm" name="banner_desktop_gap" id="banner_desktop_gap_mobile">
                                    @for($i = 0; $i <= 50; $i += 2)
                                        <option value="{{ $i }}" {{ ($site->getSetting('banner_desktop_gap', 16) == $i) ? 'selected' : '' }}>{{ $i }}px</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">모바일 (px)</label>
                                <select class="form-select form-select-sm" name="banner_mobile_gap" id="banner_mobile_gap_mobile">
                                    @for($i = 0; $i <= 30; $i += 2)
                                        <option value="{{ $i }}" {{ ($site->getSetting('banner_mobile_gap', 8) == $i) ? 'selected' : '' }}>{{ $i }}px</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>슬라이드 타입 배너에는 여백이 적용되지 않습니다.
                    </small>
                </div>
            </div>
            {{-- 데스크탑 버전 (기존 테이블) --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px; text-align: center;">위치</th>
                            <th style="width: 100px; text-align: center;">등록 개수</th>
                            <th style="width: 120px; text-align: center;">노출 타입</th>
                            <th style="width: 100px; text-align: center;">정렬</th>
                            <th colspan="2" style="text-align: center;">한줄당 개수(가로)</th>
                            <th colspan="2" style="text-align: center;">세로줄 수(0설정시 전체 보기)</th>
                            <th style="width: 100px; text-align: center;">상세</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width: 100px; text-align: center;">데스크탑</th>
                            <th style="width: 100px; text-align: center;">모바일</th>
                            <th style="width: 100px; text-align: center;">데스크탑</th>
                            <th style="width: 100px; text-align: center;">모바일</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bannerLocations as $key => $location)
                            @php
                                $settings = $bannerSettings[$key];
                            @endphp
                            <tr>
                                <td style="text-align: center; vertical-align: middle; font-weight: 500;">
                                    {{ $location['name'] }}
                                    @if(in_array($key, ['mobile_menu_top', 'mobile_menu_bottom']))
                                        <i class="bi bi-question-circle ms-1" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="{{ $location['description'] ?? '' }}"
                                           style="cursor: help; color: #6c757d;"></i>
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <span class="badge bg-secondary">{{ $settings['count'] }}</span>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <select class="form-select form-select-sm" 
                                            name="banner_{{ $key }}_exposure_type" 
                                            id="banner_{{ $key }}_exposure_type">
                                        <option value="basic" {{ $settings['exposure_type'] == 'basic' ? 'selected' : '' }}>기본타입</option>
                                        <option value="slide" {{ $settings['exposure_type'] == 'slide' ? 'selected' : '' }}>슬라이드타입</option>
                                    </select>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <select class="form-select form-select-sm" 
                                            name="banner_{{ $key }}_sort" 
                                            id="banner_{{ $key }}_sort">
                                        <option value="created" {{ $settings['sort'] == 'created' ? 'selected' : '' }}>생성순</option>
                                        <option value="random" {{ $settings['sort'] == 'random' ? 'selected' : '' }}>랜덤</option>
                                    </select>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    @if(in_array($key, ['mobile_menu_top', 'mobile_menu_bottom']))
                                        <span class="text-muted">-</span>
                                        <input type="hidden" 
                                               name="banner_{{ $key }}_desktop_per_line" 
                                               value="{{ $settings['desktop_per_line'] }}">
                                    @else
                                        <input type="number" 
                                               class="form-control form-control-sm text-center banner-per-line-input" 
                                               name="banner_{{ $key }}_desktop_per_line" 
                                               id="banner_{{ $key }}_desktop_per_line"
                                               data-location="{{ $key }}"
                                               value="{{ $settings['desktop_per_line'] }}" 
                                               min="1"
                                               {{ $settings['exposure_type'] == 'slide' ? 'disabled' : '' }}>
                                        @if($settings['exposure_type'] == 'slide')
                                            <input type="hidden" 
                                                   name="banner_{{ $key }}_desktop_per_line" 
                                                   value="{{ $settings['desktop_per_line'] }}">
                                        @endif
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="number" 
                                           class="form-control form-control-sm text-center banner-per-line-input" 
                                           name="banner_{{ $key }}_mobile_per_line" 
                                           id="banner_{{ $key }}_mobile_per_line"
                                           data-location="{{ $key }}"
                                           value="{{ $settings['mobile_per_line'] }}" 
                                           min="1"
                                           {{ $settings['exposure_type'] == 'slide' ? 'disabled' : '' }}>
                                    @if($settings['exposure_type'] == 'slide')
                                        <input type="hidden" 
                                               name="banner_{{ $key }}_mobile_per_line" 
                                               value="{{ $settings['mobile_per_line'] }}">
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    @if(in_array($key, ['mobile_menu_top', 'mobile_menu_bottom']))
                                        <span class="text-muted">-</span>
                                        <input type="hidden" 
                                               name="banner_{{ $key }}_desktop_rows" 
                                               value="{{ $settings['desktop_rows'] }}">
                                    @else
                                        <input type="number" 
                                               class="form-control form-control-sm text-center banner-rows-input" 
                                               name="banner_{{ $key }}_desktop_rows" 
                                               id="banner_{{ $key }}_desktop_rows"
                                               data-location="{{ $key }}"
                                               value="{{ $settings['desktop_rows'] }}" 
                                               min="0"
                                               {{ $settings['exposure_type'] == 'slide' ? 'disabled' : '' }}>
                                        @if($settings['exposure_type'] == 'slide')
                                            <input type="hidden" 
                                                   name="banner_{{ $key }}_desktop_rows" 
                                                   value="{{ $settings['desktop_rows'] }}">
                                        @endif
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="number" 
                                           class="form-control form-control-sm text-center banner-rows-input" 
                                           name="banner_{{ $key }}_mobile_rows" 
                                           id="banner_{{ $key }}_mobile_rows"
                                           data-location="{{ $key }}"
                                           value="{{ $settings['mobile_rows'] }}" 
                                           min="0"
                                           {{ $settings['exposure_type'] == 'slide' ? 'disabled' : '' }}>
                                    @if($settings['exposure_type'] == 'slide')
                                        <input type="hidden" 
                                               name="banner_{{ $key }}_mobile_rows" 
                                               value="{{ $settings['mobile_rows'] }}">
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <a href="{{ route('admin.banners.detail', ['site' => $site->slug, 'location' => $key]) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-list-ul"></i> 상세
                                    </a>
                                </td>
                            </tr>
                            {{-- 슬라이드 설정 행 (슬라이드 타입일 때만 표시) --}}
                            <tr class="banner-slide-settings-row" data-location="{{ $key }}" style="display: {{ $settings['exposure_type'] == 'slide' ? 'table-row' : 'none' }};">
                                <td colspan="2" style="text-align: right; vertical-align: middle; font-weight: 500;">
                                    슬라이드 설정:
                                </td>
                                <td colspan="7" style="text-align: left; vertical-align: middle;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <label for="banner_{{ $key }}_slide_interval" class="mb-0">간격(초):</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm text-center banner-slide-interval" 
                                                   name="banner_{{ $key }}_slide_interval" 
                                                   id="banner_{{ $key }}_slide_interval"
                                                   data-location="{{ $key }}"
                                                   value="{{ $settings['slide_interval'] ?? 3 }}" 
                                                   min="1"
                                                   style="width: 80px;"
                                                   {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="mb-0">방향:</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="btn-group banner-slide-direction" role="group" data-location="{{ $key }}">
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="banner_{{ $key }}_slide_direction" 
                                                           id="banner_{{ $key }}_slide_direction_left" 
                                                           value="left"
                                                           {{ ($settings['slide_direction'] ?? '') == 'left' ? 'checked' : '' }}
                                                           {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                    <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_left" title="좌측">
                                                        <i class="bi bi-arrow-left"></i>
                                                    </label>
                                                    
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="banner_{{ $key }}_slide_direction" 
                                                           id="banner_{{ $key }}_slide_direction_right" 
                                                           value="right"
                                                           {{ ($settings['slide_direction'] ?? '') == 'right' ? 'checked' : '' }}
                                                           {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                    <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_right" title="우측">
                                                        <i class="bi bi-arrow-right"></i>
                                                    </label>
                                                    
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="banner_{{ $key }}_slide_direction" 
                                                           id="banner_{{ $key }}_slide_direction_up" 
                                                           value="up"
                                                           {{ ($settings['slide_direction'] ?? '') == 'up' ? 'checked' : '' }}
                                                           {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                    <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_up" title="위">
                                                        <i class="bi bi-arrow-up"></i>
                                                    </label>
                                                    
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="banner_{{ $key }}_slide_direction" 
                                                           id="banner_{{ $key }}_slide_direction_down" 
                                                           value="down"
                                                           {{ ($settings['slide_direction'] ?? '') == 'down' ? 'checked' : '' }}
                                                           {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                    <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_down" title="아래">
                                                        <i class="bi bi-arrow-down"></i>
                                                    </label>
                                                    
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="banner_{{ $key }}_slide_direction" 
                                                           id="banner_{{ $key }}_slide_direction_none" 
                                                           value=""
                                                           {{ ($settings['slide_direction'] ?? '') == '' ? 'checked' : '' }}
                                                           {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                    <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_none" title="없음 (페이드)">
                                                        <i class="bi bi-x"></i>
                                                    </label>
                                                </div>
                                                <i class="bi bi-question-circle" 
                                                   data-bs-toggle="tooltip" 
                                                   data-bs-placement="top" 
                                                   title="슬라이드 방향을 선택하세요. 좌측/우측/위/아래는 해당 방향으로 슬라이드되며, 선택하지 않으면 페이드 인/아웃 효과로 전환됩니다."></i>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 모바일 버전 (카드 레이아웃) --}}
            <div class="d-md-none">
                <div class="d-grid gap-3">
                    @foreach($bannerLocations as $key => $location)
                        @php
                            $settings = $bannerSettings[$key];
                        @endphp
                        <div class="card">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        {{ $location['name'] }}
                                        @if(in_array($key, ['mobile_menu_top', 'mobile_menu_bottom']))
                                            <i class="bi bi-question-circle ms-1" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="{{ $location['description'] ?? '' }}"
                                               style="cursor: help; color: #6c757d;"></i>
                                        @endif
                                    </h6>
                                    <span class="badge bg-secondary">{{ $settings['count'] }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">노출 타입</label>
                                        <select class="form-select form-select-sm" 
                                                name="banner_{{ $key }}_exposure_type" 
                                                id="banner_{{ $key }}_exposure_type_mobile">
                                            <option value="basic" {{ $settings['exposure_type'] == 'basic' ? 'selected' : '' }}>기본타입</option>
                                            <option value="slide" {{ $settings['exposure_type'] == 'slide' ? 'selected' : '' }}>슬라이드타입</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">정렬</label>
                                        <select class="form-select form-select-sm" 
                                                name="banner_{{ $key }}_sort" 
                                                id="banner_{{ $key }}_sort_mobile">
                                            <option value="created" {{ $settings['sort'] == 'created' ? 'selected' : '' }}>생성순</option>
                                            <option value="random" {{ $settings['sort'] == 'random' ? 'selected' : '' }}>랜덤</option>
                                        </select>
                                    </div>
                                    @if(!in_array($key, ['mobile_menu_top', 'mobile_menu_bottom']))
                                        <div class="col-6">
                                            <label class="form-label small mb-1">한줄당 개수 (PC)</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm text-center banner-per-line-input" 
                                                   name="banner_{{ $key }}_desktop_per_line" 
                                                   id="banner_{{ $key }}_desktop_per_line_mobile"
                                                   data-location="{{ $key }}"
                                                   value="{{ $settings['desktop_per_line'] }}" 
                                                   min="1"
                                                   {{ $settings['exposure_type'] == 'slide' ? 'disabled' : '' }}>
                                            @if($settings['exposure_type'] == 'slide')
                                                <input type="hidden" 
                                                       name="banner_{{ $key }}_desktop_per_line" 
                                                       class="banner-hidden-input"
                                                       data-location="{{ $key }}"
                                                       data-field="desktop_per_line"
                                                       value="{{ $settings['desktop_per_line'] }}">
                                            @endif
                                        </div>
                                    @else
                                        <input type="hidden" 
                                               name="banner_{{ $key }}_desktop_per_line" 
                                               value="{{ $settings['desktop_per_line'] }}">
                                    @endif
                                    <div class="col-6">
                                        <label class="form-label small mb-1">한줄당 개수 (모바일)</label>
                                        <input type="number" 
                                               class="form-control form-control-sm text-center banner-per-line-input" 
                                               name="banner_{{ $key }}_mobile_per_line" 
                                               id="banner_{{ $key }}_mobile_per_line_mobile"
                                               data-location="{{ $key }}"
                                               value="{{ $settings['mobile_per_line'] }}" 
                                               min="1"
                                               {{ $settings['exposure_type'] == 'slide' ? 'disabled' : '' }}>
                                        @if($settings['exposure_type'] == 'slide')
                                            <input type="hidden" 
                                                   name="banner_{{ $key }}_mobile_per_line" 
                                                   class="banner-hidden-input"
                                                   data-location="{{ $key }}"
                                                   data-field="mobile_per_line"
                                                   value="{{ $settings['mobile_per_line'] }}">
                                        @endif
                                    </div>
                                    @if(!in_array($key, ['mobile_menu_top', 'mobile_menu_bottom']))
                                        <div class="col-6">
                                            <label class="form-label small mb-1">세로줄 수 (데스크탑)</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm text-center banner-rows-input" 
                                                   name="banner_{{ $key }}_desktop_rows" 
                                                   id="banner_{{ $key }}_desktop_rows_mobile"
                                                   data-location="{{ $key }}"
                                                   value="{{ $settings['desktop_rows'] }}" 
                                                   min="0"
                                                   {{ $settings['exposure_type'] == 'slide' ? 'disabled' : '' }}>
                                            @if($settings['exposure_type'] == 'slide')
                                                <input type="hidden" 
                                                       name="banner_{{ $key }}_desktop_rows" 
                                                       class="banner-hidden-input"
                                                       data-location="{{ $key }}"
                                                       data-field="desktop_rows"
                                                       value="{{ $settings['desktop_rows'] }}">
                                            @endif
                                        </div>
                                    @else
                                        <input type="hidden" 
                                               name="banner_{{ $key }}_desktop_rows" 
                                               value="{{ $settings['desktop_rows'] }}">
                                    @endif
                                    <div class="col-6">
                                        <label class="form-label small mb-1">세로줄 수 (모바일)</label>
                                        <input type="number" 
                                               class="form-control form-control-sm text-center banner-rows-input" 
                                               name="banner_{{ $key }}_mobile_rows" 
                                               id="banner_{{ $key }}_mobile_rows_mobile"
                                               data-location="{{ $key }}"
                                               value="{{ $settings['mobile_rows'] }}" 
                                               min="0"
                                               {{ $settings['exposure_type'] == 'slide' ? 'disabled' : '' }}>
                                        @if($settings['exposure_type'] == 'slide')
                                            <input type="hidden" 
                                                   name="banner_{{ $key }}_mobile_rows" 
                                                   class="banner-hidden-input"
                                                   data-location="{{ $key }}"
                                                   data-field="mobile_rows"
                                                   value="{{ $settings['mobile_rows'] }}">
                                        @endif
                                    </div>
                                    {{-- 슬라이드 설정 (슬라이드 타입일 때만 표시) --}}
                                    <div class="col-12 banner-slide-settings-mobile" data-location="{{ $key }}" style="display: {{ $settings['exposure_type'] == 'slide' ? 'block' : 'none' }};">
                                        <div class="border-top pt-3 mt-2">
                                            <label class="form-label small mb-2">슬라이드 설정</label>
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <label class="form-label small mb-1">간격(초)</label>
                                                    <input type="number" 
                                                           class="form-control form-control-sm text-center banner-slide-interval" 
                                                           name="banner_{{ $key }}_slide_interval" 
                                                           id="banner_{{ $key }}_slide_interval_mobile"
                                                           data-location="{{ $key }}"
                                                           value="{{ $settings['slide_interval'] ?? 3 }}" 
                                                           min="1"
                                                           {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small mb-1">방향</label>
                                                    <div class="btn-group w-100 banner-slide-direction" role="group" data-location="{{ $key }}">
                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="banner_{{ $key }}_slide_direction" 
                                                               id="banner_{{ $key }}_slide_direction_left_mobile" 
                                                               value="left"
                                                               {{ ($settings['slide_direction'] ?? '') == 'left' ? 'checked' : '' }}
                                                               {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                        <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_left_mobile" title="좌측">
                                                            <i class="bi bi-arrow-left"></i>
                                                        </label>
                                                        
                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="banner_{{ $key }}_slide_direction" 
                                                               id="banner_{{ $key }}_slide_direction_right_mobile" 
                                                               value="right"
                                                               {{ ($settings['slide_direction'] ?? '') == 'right' ? 'checked' : '' }}
                                                               {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                        <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_right_mobile" title="우측">
                                                            <i class="bi bi-arrow-right"></i>
                                                        </label>
                                                        
                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="banner_{{ $key }}_slide_direction" 
                                                               id="banner_{{ $key }}_slide_direction_up_mobile" 
                                                               value="up"
                                                               {{ ($settings['slide_direction'] ?? '') == 'up' ? 'checked' : '' }}
                                                               {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                        <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_up_mobile" title="위">
                                                            <i class="bi bi-arrow-up"></i>
                                                        </label>
                                                        
                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="banner_{{ $key }}_slide_direction" 
                                                               id="banner_{{ $key }}_slide_direction_down_mobile" 
                                                               value="down"
                                                               {{ ($settings['slide_direction'] ?? '') == 'down' ? 'checked' : '' }}
                                                               {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                        <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_down_mobile" title="아래">
                                                            <i class="bi bi-arrow-down"></i>
                                                        </label>
                                                        
                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="banner_{{ $key }}_slide_direction" 
                                                               id="banner_{{ $key }}_slide_direction_none_mobile" 
                                                               value=""
                                                               {{ ($settings['slide_direction'] ?? '') == '' ? 'checked' : '' }}
                                                               {{ $settings['exposure_type'] == 'slide' ? '' : 'disabled' }}>
                                                        <label class="btn btn-sm btn-outline-secondary" for="banner_{{ $key }}_slide_direction_none_mobile" title="없음 (페이드)">
                                                            <i class="bi bi-x"></i>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <a href="{{ route('admin.banners.detail', ['site' => $site->slug, 'location' => $key]) }}" 
                                           class="btn btn-sm btn-info w-100">
                                            <i class="bi bi-list-ul"></i> 상세
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bannerSettingsForm');
    
    // 데스크탑과 모바일 입력 필드 동기화
    function syncBannerFields() {
        // 노출 타입 동기화
        document.querySelectorAll('[id^="banner_"][id$="_exposure_type"]').forEach(function(select) {
            const location = select.id.replace('banner_', '').replace('_exposure_type', '').replace('_mobile', '');
            const desktopSelect = document.getElementById(`banner_${location}_exposure_type`);
            const mobileSelect = document.getElementById(`banner_${location}_exposure_type_mobile`);
            
            if (desktopSelect && mobileSelect) {
                desktopSelect.addEventListener('change', function() {
                    mobileSelect.value = this.value;
                });
                mobileSelect.addEventListener('change', function() {
                    desktopSelect.value = this.value;
                });
            }
        });
        
        // 정렬 동기화
        document.querySelectorAll('[id^="banner_"][id$="_sort"]').forEach(function(select) {
            const location = select.id.replace('banner_', '').replace('_sort', '').replace('_mobile', '');
            const desktopSelect = document.getElementById(`banner_${location}_sort`);
            const mobileSelect = document.getElementById(`banner_${location}_sort_mobile`);
            
            if (desktopSelect && mobileSelect) {
                desktopSelect.addEventListener('change', function() {
                    mobileSelect.value = this.value;
                });
                mobileSelect.addEventListener('change', function() {
                    desktopSelect.value = this.value;
                });
            }
        });
        
        // 여백 설정 동기화
        const desktopGapDesktop = document.getElementById('banner_desktop_gap');
        const desktopGapMobile = document.getElementById('banner_desktop_gap_mobile');
        if (desktopGapDesktop && desktopGapMobile) {
            desktopGapDesktop.addEventListener('change', function() {
                desktopGapMobile.value = this.value;
            });
            desktopGapMobile.addEventListener('change', function() {
                desktopGapDesktop.value = this.value;
            });
        }
        
        const mobileGapDesktop = document.getElementById('banner_mobile_gap');
        const mobileGapMobile = document.getElementById('banner_mobile_gap_mobile');
        if (mobileGapDesktop && mobileGapMobile) {
            mobileGapDesktop.addEventListener('change', function() {
                mobileGapMobile.value = this.value;
            });
            mobileGapMobile.addEventListener('change', function() {
                mobileGapDesktop.value = this.value;
            });
        }
    }
    
    syncBannerFields();
    
    // 노출 타입 변경 시 입력 필드 활성화/비활성화 및 슬라이드 설정 행 표시/숨김
    document.querySelectorAll('[id^="banner_"][id$="_exposure_type"]').forEach(function(select) {
        const location = select.id.replace('banner_', '').replace('_exposure_type', '').replace('_mobile', '');
        
        function toggleSlideFields() {
            const isSlide = select.value === 'slide';
            const perLineInputs = document.querySelectorAll(`.banner-per-line-input[data-location="${location}"]`);
            const rowsInputs = document.querySelectorAll(`.banner-rows-input[data-location="${location}"]`);
            const intervalInputs = document.querySelectorAll(`.banner-slide-interval[data-location="${location}"]`);
            const directionGroups = document.querySelectorAll(`.banner-slide-direction[data-location="${location}"]`);
            const slideSettingsRow = document.querySelector(`.banner-slide-settings-row[data-location="${location}"]`);
            const slideSettingsMobile = document.querySelector(`.banner-slide-settings-mobile[data-location="${location}"]`);
            
            // 한줄당 개수, 세로줄 수 입력 필드
            perLineInputs.forEach(input => {
                input.disabled = isSlide;
                if (isSlide) {
                    input.style.opacity = '0.5';
                    input.style.cursor = 'not-allowed';
                    // hidden input 생성 또는 업데이트
                    const fieldName = input.name.replace('banner_' + location + '_', '');
                    let hiddenInput = form.querySelector(`input[type="hidden"][name="${input.name}"].banner-hidden-input[data-location="${location}"][data-field="${fieldName}"]`);
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = input.name;
                        hiddenInput.className = 'banner-hidden-input';
                        hiddenInput.setAttribute('data-location', location);
                        hiddenInput.setAttribute('data-field', fieldName);
                        input.parentNode.appendChild(hiddenInput);
                    }
                    hiddenInput.value = input.value;
                } else {
                    input.style.opacity = '1';
                    input.style.cursor = 'default';
                    // hidden input 제거
                    const fieldName = input.name.replace('banner_' + location + '_', '');
                    const hiddenInput = form.querySelector(`input[type="hidden"][name="${input.name}"].banner-hidden-input[data-location="${location}"][data-field="${fieldName}"]`);
                    if (hiddenInput) {
                        hiddenInput.remove();
                    }
                }
            });
            
            rowsInputs.forEach(input => {
                input.disabled = isSlide;
                if (isSlide) {
                    input.style.opacity = '0.5';
                    input.style.cursor = 'not-allowed';
                    // hidden input 생성 또는 업데이트
                    const fieldName = input.name.replace('banner_' + location + '_', '');
                    let hiddenInput = form.querySelector(`input[type="hidden"][name="${input.name}"].banner-hidden-input[data-location="${location}"][data-field="${fieldName}"]`);
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = input.name;
                        hiddenInput.className = 'banner-hidden-input';
                        hiddenInput.setAttribute('data-location', location);
                        hiddenInput.setAttribute('data-field', fieldName);
                        input.parentNode.appendChild(hiddenInput);
                    }
                    hiddenInput.value = input.value;
                } else {
                    input.style.opacity = '1';
                    input.style.cursor = 'default';
                    // hidden input 제거
                    const fieldName = input.name.replace('banner_' + location + '_', '');
                    const hiddenInput = form.querySelector(`input[type="hidden"][name="${input.name}"].banner-hidden-input[data-location="${location}"][data-field="${fieldName}"]`);
                    if (hiddenInput) {
                        hiddenInput.remove();
                    }
                }
            });
            
            // 슬라이드 설정 필드
            intervalInputs.forEach(intervalInput => {
                intervalInput.disabled = !isSlide;
                if (!isSlide) {
                    intervalInput.style.opacity = '0.5';
                    intervalInput.style.cursor = 'not-allowed';
                } else {
                    intervalInput.style.opacity = '1';
                    intervalInput.style.cursor = 'default';
                }
            });
            
            directionGroups.forEach(directionGroup => {
                const radios = directionGroup.querySelectorAll('input[type="radio"]');
                radios.forEach(radio => {
                    radio.disabled = !isSlide;
                });
                const labels = directionGroup.querySelectorAll('label');
                labels.forEach(label => {
                    if (!isSlide) {
                        label.style.opacity = '0.5';
                        label.style.cursor = 'not-allowed';
                    } else {
                        label.style.opacity = '1';
                        label.style.cursor = 'pointer';
                    }
                });
            });
            
            // 슬라이드 설정 행 표시/숨김 (데스크탑)
            if (slideSettingsRow) {
                slideSettingsRow.style.display = isSlide ? 'table-row' : 'none';
            }
            
            // 슬라이드 설정 표시/숨김 (모바일)
            if (slideSettingsMobile) {
                slideSettingsMobile.style.display = isSlide ? 'block' : 'none';
            }
        }
        
        // 초기 상태 설정
        toggleSlideFields();
        
        // 변경 이벤트 리스너
        select.addEventListener('change', function() {
            toggleSlideFields();
            // 다른 쪽도 동기화
            const otherSelect = select.id.includes('_mobile') 
                ? document.getElementById(select.id.replace('_mobile', ''))
                : document.getElementById(select.id + '_mobile');
            if (otherSelect) {
                otherSelect.value = select.value;
                // 다른 쪽의 toggleSlideFields도 호출
                const otherLocation = otherSelect.id.replace('banner_', '').replace('_exposure_type', '').replace('_mobile', '');
                const otherPerLineInputs = document.querySelectorAll(`.banner-per-line-input[data-location="${otherLocation}"]`);
                const otherRowsInputs = document.querySelectorAll(`.banner-rows-input[data-location="${otherLocation}"]`);
                const otherIntervalInputs = document.querySelectorAll(`.banner-slide-interval[data-location="${otherLocation}"]`);
                const otherDirectionGroups = document.querySelectorAll(`.banner-slide-direction[data-location="${otherLocation}"]`);
                const otherSlideSettingsRow = document.querySelector(`.banner-slide-settings-row[data-location="${otherLocation}"]`);
                const otherSlideSettingsMobile = document.querySelector(`.banner-slide-settings-mobile[data-location="${otherLocation}"]`);
                
                const isSlide = otherSelect.value === 'slide';
                [...otherPerLineInputs, ...otherRowsInputs].forEach(input => {
                    input.disabled = isSlide;
                    input.style.opacity = isSlide ? '0.5' : '1';
                    input.style.cursor = isSlide ? 'not-allowed' : 'default';
                });
                otherIntervalInputs.forEach(input => {
                    input.disabled = !isSlide;
                    input.style.opacity = !isSlide ? '0.5' : '1';
                    input.style.cursor = !isSlide ? 'not-allowed' : 'default';
                });
                otherDirectionGroups.forEach(group => {
                    group.querySelectorAll('input[type="radio"]').forEach(radio => radio.disabled = !isSlide);
                    group.querySelectorAll('label').forEach(label => {
                        label.style.opacity = !isSlide ? '0.5' : '1';
                        label.style.cursor = !isSlide ? 'not-allowed' : 'pointer';
                    });
                });
                if (otherSlideSettingsRow) otherSlideSettingsRow.style.display = isSlide ? 'table-row' : 'none';
                if (otherSlideSettingsMobile) otherSlideSettingsMobile.style.display = isSlide ? 'block' : 'none';
            }
        });
    });
    
    // Tooltip 초기화
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // 폼 제출
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 슬라이드 타입일 때 disabled 필드의 값을 hidden input에 동기화
        document.querySelectorAll('input:disabled.banner-per-line-input, input:disabled.banner-rows-input').forEach(input => {
            const fieldName = input.name.replace(/^banner_\w+_/, '');
            const location = input.getAttribute('data-location');
            let hiddenInput = form.querySelector(`input[type="hidden"][name="${input.name}"].banner-hidden-input[data-location="${location}"][data-field="${fieldName}"]`);
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = input.name;
                hiddenInput.className = 'banner-hidden-input';
                hiddenInput.setAttribute('data-location', location);
                hiddenInput.setAttribute('data-field', fieldName);
                input.parentNode.appendChild(hiddenInput);
            }
            hiddenInput.value = input.value;
        });
        
        // FormData 생성 및 중복 필드 제거 (visible input 우선)
        const formData = new FormData();
        const seenFields = new Map();
        
        // 1단계: 먼저 visible이고 disabled가 아닌 input들을 처리 (우선순위 높음)
        const visibleInputs = form.querySelectorAll('input:not([type="hidden"]):not(:disabled), select:not(:disabled), textarea:not(:disabled)');
        visibleInputs.forEach(input => {
            if (input.type === 'checkbox' && !input.checked) {
                return;
            }
            if (input.type === 'radio' && !input.checked) {
                return;
            }
            
            const name = input.name;
            if (!name) return;
            
            // visible input은 항상 우선
            seenFields.set(name, input.value);
        });
        
        // 2단계: hidden input과 disabled input 처리 (visible input이 없을 때만)
        const hiddenAndDisabledInputs = form.querySelectorAll('input[type="hidden"], input:disabled.banner-hidden-input');
        hiddenAndDisabledInputs.forEach(input => {
            const name = input.name;
            if (!name) return;
            
            // visible input이 없을 때만 추가
            if (!seenFields.has(name)) {
                seenFields.set(name, input.value);
            }
        });
        
        // 3단계: 나머지 input 처리
        const allInputs = form.querySelectorAll('input, select, textarea');
        allInputs.forEach(input => {
            if (input.type === 'checkbox' && !input.checked) {
                return;
            }
            if (input.type === 'radio' && !input.checked) {
                return;
            }
            if (input.type === 'hidden' || (input.disabled && !input.classList.contains('banner-hidden-input'))) {
                return;
            }
            
            const name = input.name;
            if (!name) return;
            
            // 이미 처리된 필드는 건너뛰기
            if (!seenFields.has(name)) {
                seenFields.set(name, input.value);
            }
        });
        
        // Map의 모든 항목을 FormData에 추가
        seenFields.forEach((value, name) => {
            formData.append(name, value);
        });
        
        fetch('{{ route("admin.banners.update", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('배너 설정이 저장되었습니다.');
                location.reload();
            } else {
                alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    });
    
});
</script>
@endpush
@endsection

