@php
    // 배너 가져오기
    $banners = \App\Models\Banner::where('site_id', $site->id)
        ->where('location', $location)
        ->get();
    
    if ($banners->isEmpty()) {
        return; // 배너가 없으면 표시하지 않음
    }
    
    // 설정 가져오기
    $exposureType = $site->getSetting("banner_{$location}_exposure_type", 'basic');
    $sortOrder = $site->getSetting("banner_{$location}_sort", 'created');
    $desktopPerLine = (int)$site->getSetting("banner_{$location}_desktop_per_line", 3);
    $mobilePerLine = (int)$site->getSetting("banner_{$location}_mobile_per_line", 1);
    $desktopVerticalLines = (int)$site->getSetting("banner_{$location}_desktop_rows", 0);
    $mobileVerticalLines = (int)$site->getSetting("banner_{$location}_mobile_rows", 0);
    $slideInterval = (int)$site->getSetting("banner_{$location}_slide_interval", 3);
    $slideDirection = $site->getSetting("banner_{$location}_slide_direction", '');
    // 통일된 여백 설정 (슬라이드 타입이 아닐 때만 사용)
    $desktopGap = $exposureType !== 'slide' ? (int)$site->getSetting('banner_desktop_gap', 16) : 0; // 기본값 16px
    $mobileGap = $exposureType !== 'slide' ? (int)$site->getSetting('banner_mobile_gap', 8) : 0; // 기본값 8px
    
    // 고정 배너 분리
    // 최상단 고정 배너: is_pinned_top이 true인 배너 (랜덤, 가로, 세로 개수 무시하고 최상단에 표시)
    $pinnedTopBanners = $banners->where('is_pinned_top', true);
    
    // 위치 고정 배너: pinned_position이 1 이상인 배너 (최상단 배너 제외 일반 배너 위치에서 고정)
    $pinnedPositionBanners = $banners->whereNotNull('pinned_position')
        ->where('pinned_position', '>', 0)
        ->where('is_pinned_top', false);
    
    // 일반 배너: 최상단 고정도 아니고 위치 고정도 아닌 배너
    $normalBanners = $banners->where('is_pinned_top', false)
        ->filter(function($banner) {
            return is_null($banner->pinned_position) || $banner->pinned_position == 0;
        });
    
    // 일반 배너 정렬 처리
    if ($sortOrder === 'random') {
        $normalBanners = $normalBanners->shuffle();
    } else {
        // 생성순: order 우선, 그 다음 created_at
        $normalBanners = $normalBanners->sortBy([
            ['order', 'asc'],
            ['created_at', 'asc']
        ]);
    }
    
    // 최상단 고정 배너 정렬 (pinned_position 우선, 그 다음 order)
    $pinnedTopBanners = $pinnedTopBanners->sortBy([
        ['pinned_position', 'asc'],
        ['order', 'asc'],
        ['created_at', 'asc']
    ]);
    
    // 위치 고정 배너 정렬 (pinned_position 우선, 그 다음 order)
    $pinnedPositionBanners = $pinnedPositionBanners->sortBy([
        ['pinned_position', 'asc'],
        ['order', 'asc'],
        ['created_at', 'asc']
    ]);
    
    // 배너 재구성: 최상단 고정 배너 + 일반 배너 + 위치 고정 배너
    $finalBanners = collect();
    
    // 1. 최상단 고정 배너 추가 (랜덤, 가로, 세로 개수 설정과 상관없이 최상단에 표시)
    // 최상단 고정 배너는 order 순서대로 정렬하여 모두 최상단에 표시
    if ($pinnedTopBanners->isNotEmpty()) {
        $finalBanners = $finalBanners->merge($pinnedTopBanners);
    }
    
    // 2. 일반 배너와 위치 고정 배너를 위치에 맞게 배치 (최상단 배너 제외)
    $normalBannersArray = $normalBanners->values()->all();
    $pinnedPositionBannersArray = $pinnedPositionBanners->all();
    
    // 위치 고정 배너를 해당 위치에 삽입 (최상단 배너 제외 일반 배너 위치에서)
    $combinedBanners = [];
    $normalIndex = 0;
    $maxPosition = max(
        count($normalBannersArray) + count($pinnedPositionBannersArray),
        $pinnedPositionBanners->max('pinned_position') ?? 0
    );
    
    for ($i = 0; $i < $maxPosition; $i++) {
        $position = $i + 1; // 1부터 시작 (최상단 배너 제외 일반 배너 위치)
        
        // 해당 위치에 고정된 배너가 있는지 확인
        $pinnedAtPosition = collect($pinnedPositionBannersArray)->firstWhere('pinned_position', $position);
        
        if ($pinnedAtPosition) {
            $combinedBanners[] = $pinnedAtPosition;
        } elseif ($normalIndex < count($normalBannersArray)) {
            $combinedBanners[] = $normalBannersArray[$normalIndex];
            $normalIndex++;
        }
    }
    
    // 남은 일반 배너 추가
    while ($normalIndex < count($normalBannersArray)) {
        $combinedBanners[] = $normalBannersArray[$normalIndex];
        $normalIndex++;
    }
    
    $finalBanners = $finalBanners->merge($combinedBanners);
    
    // 세로줄 수 제한 (슬라이드 타입일 때는 제한하지 않음, 최상단 고정 배너는 제외)
    if ($exposureType !== 'slide') {
        $pinnedTopCount = $pinnedTopBanners->count();
        $remainingBanners = $finalBanners->skip($pinnedTopCount);
        
        if ($desktopVerticalLines > 0) {
            $maxDesktopItems = $desktopPerLine * $desktopVerticalLines;
            $remainingBanners = $remainingBanners->take($maxDesktopItems);
        }
        if ($mobileVerticalLines > 0) {
            $maxMobileItems = $mobilePerLine * $mobileVerticalLines;
            if ($remainingBanners->count() > $maxMobileItems) {
                $remainingBanners = $remainingBanners->take($maxMobileItems);
            }
        }
        
        // 최상단 고정 배너 + 제한된 나머지 배너
        $finalBanners = $pinnedTopBanners->merge($remainingBanners);
    }
    
    $banners = $finalBanners->values(); // 인덱스 재정렬
@endphp

@if($banners->isNotEmpty())
    @php
        // 헤더 배너는 상하단 여백 없이 표시
        $isHeaderBanner = ($location === 'header');
        $containerMarginBottom = $isHeaderBanner ? '0' : ($exposureType !== 'slide' ? ($mobileGap . 'px') : '0');
    @endphp
    <div class="banner-container banner-{{ $location }}" data-location="{{ $location }}" style="width: 100%; max-width: 100%; @if(!in_array($location, ['mobile_menu_top', 'mobile_menu_bottom'])) overflow: hidden; @else overflow: visible; height: auto; @endif margin-bottom: {{ $containerMarginBottom }};">
        @if(in_array($location, ['left_margin', 'right_margin']))
            <style>
                /* 좌측/우측 여백 배너 스타일 */
                .banner-container.banner-{{ $location }} {
                    width: auto !important;
                    max-width: 500px !important;
                }
                
                .banner-container.banner-{{ $location }} .banner-item-{{ $location }} {
                    width: 100% !important;
                    max-width: 500px !important;
                    flex: none !important;
                }
                
                .banner-container.banner-{{ $location }} .banner-item-{{ $location }} img {
                    width: 100% !important;
                    max-width: 500px !important;
                    height: auto !important;
                    display: block !important;
                    object-fit: contain !important;
                }
                
                .banner-container.banner-{{ $location }} .banner-row-{{ $location }} {
                    flex-direction: column !important;
                    gap: {{ $mobileGap }}px !important;
                    margin-bottom: {{ $mobileGap }}px !important;
                }
                
                @if($location === 'left_margin')
                    .banner-container.banner-{{ $location }} .banner-row-{{ $location }} {
                        margin-right: 0 !important;
                        margin-left: {{ $mobileGap }}px !important;
                        padding-right: 0 !important;
                    }
                    .banner-container.banner-{{ $location }} {
                        margin-right: 0 !important;
                        padding-right: 0 !important;
                    }
                @endif
                
                @if($location === 'right_margin')
                    .banner-container.banner-{{ $location }} .banner-row-{{ $location }} {
                        margin-left: 0 !important;
                        margin-right: {{ $mobileGap }}px !important;
                        padding-left: 0 !important;
                    }
                    .banner-container.banner-{{ $location }} {
                        margin-left: 0 !important;
                        padding-left: 0 !important;
                    }
                @endif
                
                @media (min-width: 768px) {
                    .banner-container.banner-{{ $location }} .banner-row-{{ $location }} {
                        gap: {{ $desktopGap }}px !important;
                        margin-bottom: {{ $desktopGap }}px !important;
                    }
                    
                    @if($location === 'left_margin')
                        .banner-container.banner-{{ $location }} .banner-row-{{ $location }} {
                            margin-right: 0 !important;
                            margin-left: {{ $desktopGap }}px !important;
                            padding-right: 0 !important;
                        }
                        .banner-container.banner-{{ $location }} {
                            margin-right: 0 !important;
                            padding-right: 0 !important;
                        }
                    @endif
                    
                    @if($location === 'right_margin')
                        .banner-container.banner-{{ $location }} .banner-row-{{ $location }} {
                            margin-left: 0 !important;
                            margin-right: {{ $desktopGap }}px !important;
                            padding-left: 0 !important;
                        }
                        .banner-container.banner-{{ $location }} {
                            margin-left: 0 !important;
                            padding-left: 0 !important;
                        }
                    @endif
                }
            </style>
        @else
        <style>
            /* 모바일 스타일 - 가로 {{ $mobilePerLine }}개 */
            .banner-container.banner-{{ $location }} .banner-row-{{ $location }} {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: {{ $mobileGap }}px !important;
                align-items: center !important;
                margin-top: {{ $isHeaderBanner ? '0' : $mobileGap . 'px' }} !important;
                margin-bottom: {{ $isHeaderBanner ? '0' : $mobileGap . 'px' }} !important;
            }
            
            /* 최상단 고정 배너도 동일한 여백 적용 (모바일) */
            .banner-container.banner-{{ $location }} .banner-row-{{ $location }}.banner-pinned-top {
                gap: {{ $mobileGap }}px !important;
                margin-bottom: {{ $mobileGap }}px !important;
            }
            
            /* 최상단 고정 배너들 사이의 여백 (모바일) - 첫 번째 최상단 배너는 상단 여백 없음 */
            .banner-container.banner-{{ $location }} .banner-row-{{ $location }}.banner-pinned-top:not(:first-of-type) {
                margin-top: {{ $mobileGap }}px !important;
            }
            
            .banner-container.banner-{{ $location }} .banner-item-{{ $location }} {
                flex: 0 0 calc((100% - {{ ($mobilePerLine - 1) * $mobileGap }}px) / {{ $mobilePerLine }}) !important;
                max-width: calc((100% - {{ ($mobilePerLine - 1) * $mobileGap }}px) / {{ $mobilePerLine }}) !important;
                min-width: 0 !important;
                width: calc((100% - {{ ($mobilePerLine - 1) * $mobileGap }}px) / {{ $mobilePerLine }}) !important;
                box-sizing: border-box !important;
            }
            
            .banner-container.banner-{{ $location }} .banner-item-{{ $location }} img {
                max-width: 100% !important;
                width: 100% !important;
                height: auto !important;
                display: block !important;
            }
            
            /* 데스크탑 스타일 - 가로 3개 */
            @media (min-width: 768px) {
                .banner-container.banner-{{ $location }} {
                    margin-bottom: {{ $isHeaderBanner ? '0' : $desktopGap . 'px' }} !important;
                }
                
                .banner-container.banner-{{ $location }} .banner-row-{{ $location }} {
                    display: flex !important;
                    flex-wrap: wrap !important;
                    gap: {{ $desktopGap }}px !important;
                    align-items: center !important;
                    margin-top: {{ $isHeaderBanner ? '0' : $desktopGap . 'px' }} !important;
                    margin-bottom: {{ $isHeaderBanner ? '0' : $desktopGap . 'px' }} !important;
                }
                
                /* 최상단 고정 배너도 동일한 여백 적용 (데스크탑) */
                .banner-container.banner-{{ $location }} .banner-row-{{ $location }}.banner-pinned-top {
                    gap: {{ $desktopGap }}px !important;
                    margin-bottom: {{ $desktopGap }}px !important;
                }
                
                /* 최상단 고정 배너들 사이의 여백 (데스크탑) - 첫 번째 최상단 배너는 상단 여백 없음 */
                .banner-container.banner-{{ $location }} .banner-row-{{ $location }}.banner-pinned-top:not(:first-of-type) {
                    margin-top: {{ $desktopGap }}px !important;
                }
                
                .banner-container.banner-{{ $location }} .banner-item-{{ $location }} {
                    flex: 0 0 calc((100% - {{ ($desktopPerLine - 1) * $desktopGap }}px) / {{ $desktopPerLine }}) !important;
                    max-width: calc((100% - {{ ($desktopPerLine - 1) * $desktopGap }}px) / {{ $desktopPerLine }}) !important;
                    min-width: 0 !important;
                    width: calc((100% - {{ ($desktopPerLine - 1) * $desktopGap }}px) / {{ $desktopPerLine }}) !important;
                    box-sizing: border-box !important;
                    overflow: hidden !important;
                }
                
                .banner-container.banner-{{ $location }} .banner-item-{{ $location }} img {
                    max-width: 100% !important;
                    width: 100% !important;
                    height: auto !important;
                    display: block !important;
                }
                
                /* 첫 번째 줄에서 상단 여백 제거 (데스크탑) */
                @php
                    $topMarginRemovedLocations = ['main_top', 'content_top', 'sidebar_top'];
                @endphp
                @if(in_array($location, $topMarginRemovedLocations))
                    .banner-container.banner-{{ $location }} .banner-row-{{ $location }}.banner-first-row-no-top-margin {
                        margin-top: 0 !important;
                        margin-bottom: {{ $desktopGap }}px !important;
                    }
                    
                    /* 최상단 고정 배너 첫 번째는 상단 여백 없음 (데스크탑) */
                    .banner-container.banner-{{ $location }} .banner-row-{{ $location }}.banner-pinned-top.banner-first-row-no-top-margin {
                        margin-top: 0 !important;
                        margin-bottom: {{ $desktopGap }}px !important;
                    }
                @endif
                
                /* 최상단 고정 배너 하단 여백 확실히 적용 (데스크탑) - 모든 경우 */
                .banner-container.banner-{{ $location }} .banner-row-{{ $location }}.banner-pinned-top {
                    margin-bottom: {{ $desktopGap }}px !important;
                }
            }
            
            /* 첫 번째 줄에서 상단 여백 제거 (모바일) */
            @php
                $topMarginRemovedLocations = ['main_top', 'content_top', 'sidebar_top'];
            @endphp
            @if(in_array($location, $topMarginRemovedLocations))
                .banner-container.banner-{{ $location }} .banner-row-{{ $location }}.banner-first-row-no-top-margin {
                    margin-top: 0 !important;
                    margin-bottom: {{ $mobileGap }}px !important;
                }
                
            @endif
            
            /* 최상단 고정 배너 하단 여백 확실히 적용 (모바일) - 모든 경우 */
            .banner-container.banner-{{ $location }} .banner-row-{{ $location }}.banner-pinned-top {
                margin-bottom: {{ $mobileGap }}px !important;
            }
        </style>
        @endif
        
        @if($isHeaderBanner)
        {{-- 헤더 배너 접기/펼치기 버튼 --}}
        <style>
            .header-banner-wrapper {
                position: relative;
                width: 100%;
            }
            .header-banner-content {
                width: 100%;
                overflow: hidden;
                transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
            }
            .header-banner-content.collapsed {
                max-height: 0 !important;
                opacity: 0;
            }
            .header-banner-toggle {
                position: absolute;
                bottom: -12px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 100;
                background: rgba(0, 0, 0, 0.5);
                color: white;
                border: none;
                border-radius: 0 0 8px 8px;
                padding: 2px 16px;
                font-size: 12px;
                cursor: pointer;
                transition: background 0.2s;
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .header-banner-toggle:hover {
                background: rgba(0, 0, 0, 0.7);
            }
            .header-banner-toggle i {
                transition: transform 0.3s;
            }
            .header-banner-toggle.collapsed {
                bottom: 0;
                border-radius: 0 0 8px 8px;
            }
            .header-banner-toggle.collapsed i {
                transform: rotate(180deg);
            }
        </style>
        <div class="header-banner-wrapper">
            <div class="header-banner-content" id="headerBannerContent">
        @endif
        
        @if($exposureType === 'slide' && $banners->count() > 1)
            {{-- 슬라이드 타입 - 한 번에 1개만 표시 --}}
            <div class="banner-slider" id="bannerSlider{{ $location }}" style="width: 100%; max-width: 100%; overflow: hidden; position: relative;">
                @foreach($banners as $index => $banner)
                    <div class="banner-slide" data-slide-index="{{ $index }}">
                        @if($banner->type === 'html')
                            <div class="banner-html-content" style="width: 100%; display: block;">
                                {!! $banner->html_code !!}
                            </div>
                        @else
                            @if($banner->link)
                                <a href="{{ $banner->link }}" 
                                   @if($banner->open_new_window) target="_blank" rel="noopener noreferrer" @endif
                                   class="banner-link" style="width: 100%; display: block; overflow: hidden;">
                                    <img src="{{ asset('storage/' . $banner->image_path) }}" 
                                         alt="Banner" 
                                         class="banner-image"
                                         style="width: 100%; height: auto; display: block; max-width: 100%;">
                                </a>
                            @else
                                <img src="{{ asset('storage/' . $banner->image_path) }}" 
                                     alt="Banner" 
                                     class="banner-image"
                                     style="width: 100%; height: auto; display: block; max-width: 100%;">
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            {{-- 기본 타입 --}}
            <div class="banner-grid" style="width: 100%; max-width: 100%; overflow: hidden;">
                @php
                    // 최상단 고정 배너 분리
                    $pinnedTopBanners = $banners->where('is_pinned_top', true);
                    $otherBanners = $banners->where('is_pinned_top', false)->values();
                    
                    // 세로줄 수 제한 적용 (최상단 고정 배너 제외)
                    $displayBanners = $otherBanners;
                    if ($desktopVerticalLines > 0) {
                        $maxBanners = $desktopPerLine * $desktopVerticalLines;
                        $displayBanners = $otherBanners->take($maxBanners);
                    }
                    
                    // 데스크탑 기준으로 행 나누기
                    $chunks = $displayBanners->chunk($desktopPerLine);
                @endphp
                
                {{-- 최상단 고정 배너 (랜덤, 가로, 세로 개수 설정과 상관없이 최상단에 표시) --}}
                @if($pinnedTopBanners->isNotEmpty())
                    @foreach($pinnedTopBanners as $pinnedTopBanner)
                        <div class="banner-row d-flex flex-wrap banner-row-{{ $location }} banner-pinned-top @if(in_array($location, ['main_top', 'content_top', 'sidebar_top'])) banner-first-row-no-top-margin @endif" style="width: 100%;">
                            <div class="banner-item banner-item-{{ $location }}" style="overflow: hidden; width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important;">
                                @if($pinnedTopBanner->type === 'html')
                                    <div class="banner-html-content" style="width: 100%; display: block;">
                                        {!! $pinnedTopBanner->html_code !!}
                                    </div>
                                @else
                                    @if($pinnedTopBanner->link)
                                        <a href="{{ $pinnedTopBanner->link }}" 
                                           @if($pinnedTopBanner->open_new_window) target="_blank" rel="noopener noreferrer" @endif
                                           class="banner-link d-block" style="width: 100%; overflow: hidden; display: block;">
                                            <img src="{{ asset('storage/' . $pinnedTopBanner->image_path) }}" 
                                                 alt="Banner" 
                                                 class="banner-image"
                                                 style="width: 100% !important; height: auto !important; display: block !important; max-width: 100% !important; object-fit: contain;">
                                        </a>
                                    @else
                                        <img src="{{ asset('storage/' . $pinnedTopBanner->image_path) }}" 
                                             alt="Banner" 
                                             class="banner-image"
                                             style="width: 100% !important; height: auto !important; display: block !important; max-width: 100% !important; object-fit: contain;">
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
                
                @foreach($chunks as $chunk)
                    @php
                        // 첫 번째 줄에서 상단 여백을 제거할 위치들
                        $topMarginRemovedLocations = ['main_top', 'content_top', 'sidebar_top'];
                        $isFirstRowNoTopMargin = in_array($location, $topMarginRemovedLocations) && $loop->first;
                    @endphp
                    <div class="banner-row d-flex flex-wrap banner-row-{{ $location }} @if($isFirstRowNoTopMargin) banner-first-row-no-top-margin @endif">
                        @foreach($chunk as $banner)
                            <div class="banner-item banner-item-{{ $location }}" style="overflow: hidden;">
                                @if($banner->type === 'html')
                                    <div class="banner-html-content" style="width: 100%; display: block;">
                                        {!! $banner->html_code !!}
                                    </div>
                                @else
                                    @if($banner->link)
                                        <a href="{{ $banner->link }}" 
                                           @if($banner->open_new_window) target="_blank" rel="noopener noreferrer" @endif
                                           class="banner-link d-block" style="width: 100%; overflow: hidden; display: block;">
                                            <img src="{{ asset('storage/' . $banner->image_path) }}" 
                                                 alt="Banner" 
                                                 class="banner-image"
                                                 style="width: 100% !important; height: auto !important; display: block !important; max-width: 100% !important; object-fit: contain;">
                                        </a>
                                    @else
                                        <img src="{{ asset('storage/' . $banner->image_path) }}" 
                                             alt="Banner" 
                                             class="banner-image"
                                             style="width: 100% !important; height: auto !important; display: block !important; max-width: 100% !important; object-fit: contain;">
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif
        
        @if($isHeaderBanner)
            </div>
            <button class="header-banner-toggle" id="headerBannerToggle" onclick="toggleHeaderBanner()">
                <i class="bi bi-chevron-up"></i>
                <span>접기</span>
            </button>
        </div>
        <script>
        function toggleHeaderBanner() {
            const content = document.getElementById('headerBannerContent');
            const toggle = document.getElementById('headerBannerToggle');
            const isCollapsed = content.classList.contains('collapsed');
            
            if (isCollapsed) {
                content.classList.remove('collapsed');
                content.style.maxHeight = content.scrollHeight + 'px';
                toggle.classList.remove('collapsed');
                toggle.querySelector('span').textContent = '접기';
                localStorage.removeItem('headerBannerCollapsed');
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
                content.offsetHeight; // force reflow
                content.classList.add('collapsed');
                toggle.classList.add('collapsed');
                toggle.querySelector('span').textContent = '펼치기';
                localStorage.setItem('headerBannerCollapsed', 'true');
            }
        }
        
        // 페이지 로드 시 이전 상태 복원
        document.addEventListener('DOMContentLoaded', function() {
            const content = document.getElementById('headerBannerContent');
            const toggle = document.getElementById('headerBannerToggle');
            
            if (content && toggle) {
                // 초기 높이 설정
                content.style.maxHeight = content.scrollHeight + 'px';
                
                // 이전에 접었던 상태였으면 접힌 상태로 시작
                if (localStorage.getItem('headerBannerCollapsed') === 'true') {
                    content.classList.add('collapsed');
                    toggle.classList.add('collapsed');
                    toggle.querySelector('span').textContent = '펼치기';
                }
            }
        });
        </script>
        @endif
    </div>
    
    @if($exposureType === 'slide' && $banners->count() > 1)
        @push('styles')
        <style>
            #bannerSlider{{ $location }} {
                position: relative;
                overflow: hidden;
                width: 100%;
            }
            #bannerSlider{{ $location }} .banner-slide {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                opacity: 0;
                z-index: 0;
            }
            #bannerSlider{{ $location }} .banner-slide.active {
                position: absolute;
                top: 0;
                left: 0;
                opacity: 1;
                z-index: 2;
            }
            /* 슬라이드 방향별 transition - 모든 슬라이드에 기본 transition 적용 */
            #bannerSlider{{ $location }} .banner-slide {
                transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
            }
            #bannerSlider{{ $location }} .banner-slide img {
                width: 100%;
                height: auto;
                display: block;
            }
        </style>
        @endpush
        
        @push('scripts')
        <script>
        (function() {
            const slider = document.getElementById('bannerSlider{{ $location }}');
            if (!slider) return;
            
            const slides = slider.querySelectorAll('.banner-slide');
            if (slides.length <= 1) return;
            
            console.log('Total slides:', slides.length); // 디버깅용
            
            // 첫 번째 슬라이드의 높이를 기준으로 컨테이너 높이 설정
            function setSliderHeight() {
                const activeSlide = slider.querySelector('.banner-slide.active') || slides[0];
                const activeImage = activeSlide.querySelector('img');
                if (activeImage && activeImage.offsetHeight > 0) {
                    slider.style.height = activeImage.offsetHeight + 'px';
                }
            }
            
            // 이미지 로드 후 높이 설정
            slides.forEach(slide => {
                const img = slide.querySelector('img');
                if (img) {
                    img.onload = setSliderHeight;
                    if (img.complete) {
                        setSliderHeight();
                    }
                }
            });
            
            // 초기 높이 설정
            setTimeout(setSliderHeight, 100);
            
            let currentSlide = 0;
            const slideDirection = '{{ $slideDirection }}';
            let isAnimating = false;
            let slideIntervalId = null;
            
            console.log('Slide direction:', slideDirection, 'Total slides:', slides.length); // 디버깅용
            
            // 모든 슬라이드 초기 설정
            slides.forEach((slide, index) => {
                slide.style.position = 'absolute';
                slide.style.top = '0';
                slide.style.left = '0';
                slide.style.width = '100%';
                slide.style.display = 'block';
                slide.style.opacity = index === 0 ? '1' : '0';
                slide.style.transform = '';
                slide.style.zIndex = index === 0 ? '2' : '0';
                if (index === 0) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });
            
            function showSlide(index) {
                // 인덱스 범위 확인
                if (index < 0 || index >= slides.length) {
                    console.error('Invalid slide index:', index, 'Total slides:', slides.length);
                    return;
                }
                
                // 같은 슬라이드면 무시
                if (currentSlide === index && slides[index].classList.contains('active')) {
                    return;
                }
                
                // 애니메이션 중이면 무시
                if (isAnimating) {
                    return;
                }
                
                isAnimating = true;
                
                const prevSlide = slides[currentSlide];
                const nextSlide = slides[index];
                
                // 방향이 있는 경우 슬라이드 효과
                if (slideDirection && slideDirection.trim() !== '' && slideDirection !== 'null') {
                    // 이전 슬라이드 나가기
                    if (prevSlide && prevSlide !== nextSlide) {
                        prevSlide.style.transition = 'transform 0.5s ease-in-out, opacity 0.5s ease-in-out';
                        prevSlide.style.zIndex = '1';
                        if (slideDirection === 'left') {
                            prevSlide.style.transform = 'translateX(-100%)';
                        } else if (slideDirection === 'right') {
                            prevSlide.style.transform = 'translateX(100%)';
                        } else if (slideDirection === 'up') {
                            prevSlide.style.transform = 'translateY(-100%)';
                        } else if (slideDirection === 'down') {
                            prevSlide.style.transform = 'translateY(100%)';
                        }
                        prevSlide.style.opacity = '0';
                        prevSlide.classList.remove('active');
                    }
                    
                    // 다음 슬라이드 들어오기
                    nextSlide.style.transition = 'none';
                    nextSlide.style.zIndex = '2';
                    if (slideDirection === 'left') {
                        nextSlide.style.transform = 'translateX(100%)';
                    } else if (slideDirection === 'right') {
                        nextSlide.style.transform = 'translateX(-100%)';
                    } else if (slideDirection === 'up') {
                        nextSlide.style.transform = 'translateY(100%)';
                    } else if (slideDirection === 'down') {
                        nextSlide.style.transform = 'translateY(-100%)';
                    }
                    nextSlide.style.opacity = '0';
                    nextSlide.classList.add('active');
                    
                    // 다음 프레임에서 애니메이션 시작
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            nextSlide.style.transition = 'transform 0.5s ease-in-out, opacity 0.5s ease-in-out';
                            nextSlide.style.transform = '';
                            nextSlide.style.opacity = '1';
                            
                            // 애니메이션 완료 후 플래그 해제
                            setTimeout(() => {
                                isAnimating = false;
                                // 이전 슬라이드 완전히 숨기기
                                if (prevSlide && prevSlide !== nextSlide) {
                                    prevSlide.style.transform = '';
                                    prevSlide.style.opacity = '0';
                                }
                            }, 500);
                        });
                    });
                } else {
                    // 방향이 없는 경우 페이드 효과
                    if (prevSlide && prevSlide !== nextSlide) {
                        prevSlide.style.transition = 'opacity 0.5s ease-in-out';
                        prevSlide.style.zIndex = '1';
                        prevSlide.style.opacity = '0';
                        prevSlide.classList.remove('active');
                    }
                    
                    nextSlide.style.transition = 'opacity 0.5s ease-in-out';
                    nextSlide.style.zIndex = '2';
                    nextSlide.style.transform = '';
                    nextSlide.style.opacity = '0';
                    nextSlide.classList.add('active');
                    
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            nextSlide.style.opacity = '1';
                            
                            // 애니메이션 완료 후 플래그 해제
                            setTimeout(() => {
                                isAnimating = false;
                            }, 500);
                        });
                    });
                }
                
                currentSlide = index;
                setSliderHeight();
            }
            
            function nextSlide() {
                // 무한 반복을 위해 모든 슬라이드를 순환
                const nextIndex = (currentSlide + 1) % slides.length;
                console.log('Moving to slide:', nextIndex, 'of', slides.length); // 디버깅용
                showSlide(nextIndex);
            }
            
            // 초기 표시
            showSlide(0);
            
            // 설정된 간격(초)마다 슬라이드 변경 (무한 반복)
            const slideInterval = {{ $slideInterval }} * 1000; // 초를 밀리초로 변환
            slideIntervalId = setInterval(nextSlide, slideInterval);
        })();
        </script>
        @endpush
    @endif
@endif

