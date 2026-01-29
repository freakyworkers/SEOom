@php
    $widgetSettings = $widget->settings ?? [];
    $limit = $widgetSettings['limit'] ?? 10;
    
    // HEX 색상을 RGBA로 변환하는 헬퍼 함수
    if (!function_exists('hexToRgbaButton')) {
        function hexToRgbaButton($hex, $alpha = 1.0) {
            $hex = ltrim($hex, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            if (strlen($hex) !== 6) {
                return "rgba(0, 0, 0, {$alpha})";
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "rgba({$r}, {$g}, {$b}, {$alpha})";
        }
    }
    
    // 다크모드에서 흰색 배경을 다크 배경으로 변환하는 헬퍼 함수
    if (!function_exists('darkModeBackground')) {
        function darkModeBackground($color, $isDark) {
            if (!$isDark) return $color;
            $normalizedColor = strtolower(trim($color));
            // 흰색 계열인 경우 다크 배경으로 변환
            if ($normalizedColor === '#ffffff' || $normalizedColor === '#fff' || $normalizedColor === 'white' || $normalizedColor === 'rgb(255, 255, 255)' || $normalizedColor === 'rgba(255, 255, 255, 1)') {
                return 'rgb(43, 43, 43)';
            }
            return $color;
        }
    }
    
    // 다크모드에서 텍스트 색상 변환 헬퍼 함수
    if (!function_exists('darkModeTextColor')) {
        function darkModeTextColor($color, $isDark) {
            if (!$isDark) return $color;
            $normalizedColor = strtolower(trim($color));
            // 검은색 계열인 경우 흰색으로 변환
            if ($normalizedColor === '#000000' || $normalizedColor === '#000' || $normalizedColor === 'black' || $normalizedColor === 'rgb(0, 0, 0)') {
                return '#ffffff';
            }
            return $color;
        }
    }
    
    // 헤더 테두리 설정 가져오기
    $headerBorder = $site->getSetting('header_border', '0') == '1';
    $headerBorderWidth = $site->getSetting('header_border_width', '1');
    
    // 포인트 컬러 가져오기 (헤더 테두리 컬러로 사용)
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $isDark = $themeDarkMode === 'dark';
    $pointColor = $isDark ? $site->getSetting('color_dark_point_main', '#ffffff') : $site->getSetting('color_light_point_main', '#0d6efd');
    $headerBorderColor = $pointColor;
    
    // 다크모드 텍스트 컬러 (위젯 링크, 게시판 제목 등에 사용)
    $widgetTextColor = $isDark ? ($site->getSetting('color_dark_body_text', '#ffffff')) : '#495057';
    $widgetMutedColor = $isDark ? 'rgba(255, 255, 255, 0.7)' : '#6c757d';
    $widgetBorderColor = $isDark ? 'rgba(255, 255, 255, 0.1)' : '#dee2e6';
    $widgetHoverColor = $isDark ? 'rgba(255, 255, 255, 0.8)' : '#212529';
    
    // 위젯 상단 테두리 스타일
    $widgetTopBorderStyle = '';
    if ($headerBorder) {
        $widgetTopBorderStyle = "border-top: {$headerBorderWidth}px solid {$headerBorderColor};";
    }
    
    // 테마 설정 가져오기
    $themeMain = $site->getSetting('theme_main', 'round');
    $isRoundTheme = $themeMain === 'round';
    $isOriginalRoundTheme = $isRoundTheme; // 원래 테마 설정 저장 (버튼용)
    
    // 세로 100% 설정 확인
    $isFullHeight = $isFullHeight ?? false;
    
    // 가로 100% 설정 확인
    $isFullWidth = $isFullWidth ?? false;
    
    // 칸 고정너비 설정 확인 (가로 100%이지만 칸들은 고정 너비 유지)
    $fixedWidthColumns = $fixedWidthColumns ?? false;
    
    // 실제로 가로 100%를 적용할지 결정 (칸 고정너비일 때는 실제 가로 100% 아님)
    $isActualFullWidth = $isFullWidth && !$fixedWidthColumns;
    
    // 첫 번째 위젯 여부 확인
    $isFirstWidget = $isFirstWidget ?? false;
    
    // 마지막 위젯 여부 확인
    $isLastWidget = $isLastWidget ?? false;
    
    // 컨테이너 정렬 설정 확인
    // verticalAlign이 전달되지 않았을 경우 기본값 'top' 사용
    // null 체크를 포함하여 제대로 처리
    if (!isset($verticalAlign) || $verticalAlign === null || $verticalAlign === '') {
        $verticalAlign = 'top';
    }
    
    // 가로 100%일 때는 라운드 제거 (버튼 제외) - 칸 고정너비일 때는 유지
    if ($isActualFullWidth) {
        $isRoundTheme = false;
    }
    
    // 위젯 그림자 설정 확인 (기본값: ON)
    $widgetShadow = $site->getSetting('widget_shadow', '1') == '1';
    
    // board_viewer 위젯의 no_background 설정 확인 (외부 카드에도 적용하기 위해 미리 확인)
    $boardViewerNoBackgroundEarly = ($widget->type === 'board_viewer' && isset($widgetSettings['no_background']) && $widgetSettings['no_background']);
    
    // 그림자 클래스 결정: 실제 full width/full height이거나 그림자 설정이 OFF이거나 board_viewer의 no_background가 활성화되면 그림자 제거
    $shadowClass = ($isActualFullWidth || $isFullHeight || !$widgetShadow || $boardViewerNoBackgroundEarly) ? '' : 'shadow-sm';
    
    // 애니메이션 설정 가져오기
    $animationDirection = $widgetSettings['animation_direction'] ?? 'none';
    $animationDelay = $widgetSettings['animation_delay'] ?? 0;
    $animationClass = '';
    $animationStyle = '';
    if ($animationDirection !== 'none') {
        $animationClass = 'widget-animate widget-animate-' . $animationDirection;
        $animationStyle = 'animation-delay: ' . $animationDelay . 's;';
    }
@endphp

    @if($widget->type === 'block')
    @php
        $blockSettings = $widgetSettings;
        $blockTitle = $blockSettings['block_title'] ?? '';
        $blockContent = $blockSettings['block_content'] ?? '';
        $textAlign = $blockSettings['text_align'] ?? 'left';
        $backgroundType = $blockSettings['background_type'] ?? 'color';
        $backgroundColor = $blockSettings['background_color'] ?? '#007bff';
        $backgroundColorAlpha = isset($blockSettings['background_color_alpha']) ? $blockSettings['background_color_alpha'] : 100;
        $backgroundImageUrl = $blockSettings['background_image_url'] ?? '';
        $backgroundImageAlpha = isset($blockSettings['background_image_alpha']) ? $blockSettings['background_image_alpha'] : 100;
        $backgroundImageFullWidth = $blockSettings['background_image_full_width'] ?? false;
        $paddingTop = $blockSettings['padding_top'] ?? 20;
        $paddingBottom = $blockSettings['padding_bottom'] ?? ($blockSettings['padding_top'] ?? 20);
        $paddingLeft = $blockSettings['padding_left'] ?? 20;
        $paddingRight = $blockSettings['padding_right'] ?? ($blockSettings['padding_left'] ?? 20);
        $titleContentGap = $blockSettings['title_content_gap'] ?? 8;
        $link = $blockSettings['link'] ?? '';
        $openNewTab = $blockSettings['open_new_tab'] ?? false;
        // 제목/내용 컬러 분리 (하위 호환성: font_color도 지원)
        $titleColor = $blockSettings['title_color'] ?? $blockSettings['font_color'] ?? '#ffffff';
        $contentColor = $blockSettings['content_color'] ?? $blockSettings['font_color'] ?? '#ffffff';
        $titleFontSize = $blockSettings['title_font_size'] ?? '16';
        $contentFontSize = $blockSettings['content_font_size'] ?? '14';
        // 반응형 폰트 사이즈 계산 - clamp(최소, 선호, 최대)
        $responsiveTitleFontSize = "clamp(" . round($titleFontSize * 0.65) . "px, " . round($titleFontSize / 8, 1) . "vw, " . $titleFontSize . "px)";
        $responsiveContentFontSize = "clamp(" . round($contentFontSize * 0.65) . "px, " . round($contentFontSize / 8, 1) . "vw, " . $contentFontSize . "px)";
        
        // 블록 이미지 설정
        $enableImage = $blockSettings['enable_image'] ?? false;
        $blockImageUrl = $blockSettings['block_image_url'] ?? '';
        $blockImagePaddingTop = $blockSettings['block_image_padding_top'] ?? 0;
        $blockImagePaddingBottom = $blockSettings['block_image_padding_bottom'] ?? 0;
        $blockImagePaddingLeft = $blockSettings['block_image_padding_left'] ?? 0;
        $blockImagePaddingRight = $blockSettings['block_image_padding_right'] ?? 0;
        // 버튼 데이터 (하위 호환성: 기존 단일 버튼 데이터도 지원)
        $buttons = $blockSettings['buttons'] ?? [];
        if (!is_array($buttons)) {
            $showButton = $blockSettings['show_button'] ?? false;
            if ($showButton && isset($blockSettings['button_text'])) {
                $buttons = [[
                    'text' => $blockSettings['button_text'] ?? '',
                    'link' => $blockSettings['link'] ?? '',
                    'open_new_tab' => $blockSettings['open_new_tab'] ?? false,
                    'background_color' => $blockSettings['button_background_color'] ?? '#007bff',
                    'text_color' => $blockSettings['button_text_color'] ?? '#ffffff'
                ]];
            } else {
                $buttons = [];
            }
        }
        $buttonTopMargin = $blockSettings['button_top_margin'] ?? 12;
        $hasButtons = !empty($buttons);
        
        // 다크모드에서 텍스트 색상 조정
        $titleColor = darkModeTextColor($titleColor, $isDark);
        $contentColor = darkModeTextColor($contentColor, $isDark);
        
        // 이미지 패딩이 있는지 확인 (하나라도 0이 아니면 패딩 있음)
        $hasImagePadding = $enableImage && $blockImageUrl && ($blockImagePaddingTop > 0 || $blockImagePaddingBottom > 0 || $blockImagePaddingLeft > 0 || $blockImagePaddingRight > 0);
        
        // 외부 컨테이너 스타일 (패딩 없음, 그림자/애니메이션 등 유지)
        $outerBlockStyle = "width: 100%;";
        
        // 이미지 패딩이 있으면 외부 컨테이너에 배경 적용 (통일된 배경)
        if ($hasImagePadding) {
            if ($backgroundType === 'color') {
                $adjustedBgColor = darkModeBackground($backgroundColor, $isDark);
                if ($backgroundColorAlpha < 100) {
                    $adjustedBgColor = hexToRgbaButton($adjustedBgColor, $backgroundColorAlpha / 100);
                }
                $outerBlockStyle .= " background-color: {$adjustedBgColor};";
            } else if ($backgroundType === 'gradient') {
                $gradientStart = $blockSettings['background_gradient_start'] ?? '#ffffff';
                $gradientEnd = $blockSettings['background_gradient_end'] ?? '#000000';
                $gradientStart = darkModeBackground($gradientStart, $isDark);
                $gradientEnd = darkModeBackground($gradientEnd, $isDark);
                $gradientAngle = $blockSettings['background_gradient_angle'] ?? 90;
                $outerBlockStyle .= " background: linear-gradient({$gradientAngle}deg, {$gradientStart}, {$gradientEnd});";
            } else if ($backgroundType === 'image' && $backgroundImageUrl) {
                $bgSize = $backgroundImageFullWidth ? '100% auto' : 'cover';
                $outerBlockStyle .= " background-image: url('{$backgroundImageUrl}'); background-size: {$bgSize}; background-position: center top; background-repeat: no-repeat;";
                if ($backgroundImageAlpha < 100) {
                    $outerBlockStyle .= " opacity: " . ($backgroundImageAlpha / 100) . ";";
                }
            }
        }
        
        // 이미지 컨테이너 스타일 설정
        $imageContainerStyle = "width: 100%; margin: 0; box-sizing: border-box; overflow: hidden; flex-shrink: 0;";
        $imageStyle = "width: 100%; height: auto; display: block; margin: 0; box-sizing: border-box;";
        
        // 이미지 패딩 적용
        if ($enableImage && $blockImageUrl) {
            $imageContainerStyle .= " padding-top: {$blockImagePaddingTop}px; padding-bottom: {$blockImagePaddingBottom}px; padding-left: {$blockImagePaddingLeft}px; padding-right: {$blockImagePaddingRight}px;";
            
            // 이미지 패딩이 있으면 배경 없이 (외부 컨테이너 배경 사용)
            // 이미지 패딩이 없으면 배경 없이 (내용 컨테이너 배경 사용)
            // 배경은 적용하지 않음
        } else {
            $imageContainerStyle .= " padding: 0;";
        }
        
        if ($isRoundTheme && !$isActualFullWidth) {
            // 라운드 테마일 때 이미지 상단 라운드 적용
            if ($enableImage && $blockImageUrl) {
                $imageContainerStyle .= " border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem;";
                $imageStyle .= " border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem;";
            }
        }
        
        // 가로 100%일 때 좌우 보더 레디우스 제거 (칸 고정너비일 때는 유지)
        if ($isActualFullWidth) {
            $outerBlockStyle .= " border-radius: 0 !important;";
            $imageContainerStyle .= " border-radius: 0 !important;";
            $imageStyle .= " border-radius: 0 !important;";
        }
        
        // 모든 위젯이 상하 영역을 꽉 차게 하기 위해 flex 적용
        // 같은 row 내 컬럼들이 같은 높이를 가지도록 항상 flex: 1 적용
        // 컨테이너 정렬에 따라 justify-content 설정 (isFullHeight 여부와 관계없이)
        // verticalAlign 변수 확인 (디버깅용 - 나중에 제거 가능)
        $actualVerticalAlign = $verticalAlign ?? 'top';
        $justifyContent = 'center';
        if ($actualVerticalAlign === 'top') {
            $justifyContent = 'flex-start';
        } elseif ($actualVerticalAlign === 'bottom') {
            $justifyContent = 'flex-end';
        } else {
            $justifyContent = 'center';
        }
        // 같은 row 내 컬럼들이 같은 높이를 가지도록 항상 flex: 1과 height: 100% 적용
        $outerBlockStyle .= " flex: 1; min-height: 0; height: 100%; display: flex; flex-direction: column; justify-content: {$justifyContent}; margin-top: 0 !important; margin-bottom: 0 !important;";
        
        // 내용 컨테이너 스타일 (패딩 있음)
        $contentBlockStyle = "padding-top: {$paddingTop}px; padding-bottom: {$paddingBottom}px; padding-left: {$paddingLeft}px; padding-right: {$paddingRight}px; text-align: {$textAlign}; color: {$contentColor};";
        
        // 라운드 테마 적용 (이미지가 있으면 하단만, 없으면 전체)
        if ($isRoundTheme && !$isActualFullWidth) {
            if ($enableImage && $blockImageUrl) {
                // 이미지가 있으면 하단만 라운드
                $contentBlockStyle .= " border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem;";
            } else {
                // 이미지가 없으면 전체 라운드
                $contentBlockStyle .= " border-radius: 0.5rem;";
            }
        }
        
        // 이미지 패딩이 없을 때만 내용 컨테이너에 배경 적용
        // 이미지 패딩이 있으면 외부 컨테이너 배경 사용 (통일된 배경)
        if (!$hasImagePadding) {
            if ($backgroundType === 'color') {
                $adjustedBgColor = darkModeBackground($backgroundColor, $isDark);
                // 투명도 적용
                if ($backgroundColorAlpha < 100) {
                    $adjustedBgColor = hexToRgbaButton($adjustedBgColor, $backgroundColorAlpha / 100);
                }
                $contentBlockStyle .= " background-color: {$adjustedBgColor};";
            } else if ($backgroundType === 'gradient') {
                $gradientStart = $blockSettings['background_gradient_start'] ?? '#ffffff';
                $gradientEnd = $blockSettings['background_gradient_end'] ?? '#000000';
                // 다크모드에서 그라디언트 색상도 조정
                $gradientStart = darkModeBackground($gradientStart, $isDark);
                $gradientEnd = darkModeBackground($gradientEnd, $isDark);
                $gradientAngle = $blockSettings['background_gradient_angle'] ?? 90;
                $contentBlockStyle .= " background: linear-gradient({$gradientAngle}deg, {$gradientStart}, {$gradientEnd});";
            } else if ($backgroundType === 'image' && $backgroundImageUrl) {
                $bgSize = $backgroundImageFullWidth ? '100% auto' : 'cover';
                $contentBlockStyle .= " background-image: url('{$backgroundImageUrl}'); background-size: {$bgSize}; background-position: center top; background-repeat: no-repeat;";
                // 이미지 투명도 적용
                if ($backgroundImageAlpha < 100) {
                    $contentBlockStyle .= " opacity: " . ($backgroundImageAlpha / 100) . ";";
                }
            }
        } else {
            // 이미지 패딩이 있으면 내용 컨테이너는 배경 없이 (외부 컨테이너 배경 사용)
            $contentBlockStyle .= " background: transparent;";
        }
        
        // 내용 컨테이너도 flex로 설정하여 내용 정렬
        $contentBlockStyle .= " flex: 1; display: flex; flex-direction: column; justify-content: {$justifyContent};";
        
        // 위젯 자체의 하단 마진 제거
        $blockMarginBottom = 'mb-0';
        
        // 배경색이 없음(none)인 경우 그림자 제거
        $blockShadowClass = ($backgroundType === 'none') ? 'no-shadow-widget' : $shadowClass;
        
        // 외부 컨테이너에 라운드 테마 클래스 적용
        $outerBlockClass = $isRoundTheme && !$isActualFullWidth ? '' : 'rounded-0';
    @endphp
    <div class="{{ $blockMarginBottom }} {{ $blockShadowClass }} {{ $animationClass }} {{ $outerBlockClass }}" style="{{ $outerBlockStyle }} {{ $animationStyle }}" data-widget-id="{{ $widget->id }}">
        @if($enableImage && $blockImageUrl)
            <div style="{{ $imageContainerStyle }}">
                <img src="{{ $blockImageUrl }}" alt="블록 이미지" style="{{ $imageStyle }}">
            </div>
        @endif
        <div style="{{ $contentBlockStyle }}">
        @if($link && !$hasButtons)
            <a href="{{ $link }}" 
               style="text-decoration: none; display: block;"
               @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
        @endif
        @if($blockTitle)
            <h4 style="color: {{ $titleColor }}; font-weight: bold; font-size: {{ $responsiveTitleFontSize }}; margin-top: {{ ($enableImage && $blockImageUrl) ? $titleContentGap : 0 }}px; margin-bottom: {{ $titleContentGap }}px;">{!! nl2br(e($blockTitle)) !!}</h4>
        @endif
        @if($blockContent)
            <p class="mb-0" style="color: {{ $contentColor }}; font-size: {{ $responsiveContentFontSize }}; white-space: pre-wrap;">{{ $blockContent }}</p>
        @endif
        @if($link && !$hasButtons)
            </a>
        @endif
        @if($hasButtons)
            @php
                $justifyContent = 'flex-start';
                if ($textAlign === 'center') {
                    $justifyContent = 'center';
                } elseif ($textAlign === 'right') {
                    $justifyContent = 'flex-end';
                }
            @endphp
            <div style="margin-top: {{ $buttonTopMargin }}px; display: flex; flex-direction: row; flex-wrap: wrap; gap: 8px; justify-content: {{ $justifyContent }};">
                @foreach($buttons as $button)
                    @php
                        $buttonText = $button['text'] ?? '';
                        $buttonLink = $button['link'] ?? '';
                        $buttonOpenNewTab = $button['open_new_tab'] ?? false;
                        $buttonBackgroundColor = $button['background_color'] ?? '#007bff';
                        $buttonTextColor = $button['text_color'] ?? '#ffffff';
                        $buttonBorderColor = $button['border_color'] ?? $buttonBackgroundColor;
                        $buttonBorderWidth = $button['border_width'] ?? '2';
                        $buttonHoverBackgroundColor = $button['hover_background_color'] ?? '#0056b3';
                        $buttonHoverTextColor = $button['hover_text_color'] ?? '#ffffff';
                        $buttonHoverBorderColor = $button['hover_border_color'] ?? '#0056b3';
                        
                        // 버튼 배경 타입 및 그라데이션 설정
                        $buttonBackgroundType = $button['background_type'] ?? 'color';
                        $buttonGradientStart = $button['background_gradient_start'] ?? $buttonBackgroundColor;
                        $buttonGradientEnd = $button['background_gradient_end'] ?? $buttonHoverBackgroundColor;
                        $buttonGradientAngle = $button['background_gradient_angle'] ?? 90;
                        $buttonOpacity = isset($button['opacity']) ? floatval($button['opacity']) : 1.0;
                        
                        // 버튼 배경 스타일 생성 (투명도는 배경색에만 적용)
                        $buttonBackgroundStyle = '';
                        if ($buttonBackgroundType === 'gradient') {
                            // 그라데이션에 투명도 적용
                            $gradientStartRgba = hexToRgbaButton($buttonGradientStart, $buttonOpacity);
                            $gradientEndRgba = hexToRgbaButton($buttonGradientEnd, $buttonOpacity);
                            $buttonBackgroundStyle = "background: linear-gradient({$buttonGradientAngle}deg, {$gradientStartRgba}, {$gradientEndRgba});";
                        } else {
                            // 단색 배경에 투명도 적용
                            if ($buttonOpacity < 1.0) {
                                $bgColorRgba = hexToRgbaButton($buttonBackgroundColor, $buttonOpacity);
                                $buttonBackgroundStyle = "background-color: {$bgColorRgba};";
                            } else {
                                $buttonBackgroundStyle = "background-color: {$buttonBackgroundColor};";
                            }
                        }
                        
                        // Hover 배경 스타일 생성
                        $buttonHoverBackgroundType = $button['hover_background_type'] ?? 'color';
                        $buttonHoverGradientStart = $button['hover_background_gradient_start'] ?? $buttonHoverBackgroundColor;
                        $buttonHoverGradientEnd = $button['hover_background_gradient_end'] ?? $buttonHoverBackgroundColor;
                        $buttonHoverGradientAngle = $button['hover_background_gradient_angle'] ?? 90;
                        $buttonHoverOpacity = isset($button['hover_opacity']) ? floatval($button['hover_opacity']) : 1.0;
                        
                        // Hover 배경 스타일 (투명도는 배경색에만 적용)
                        $buttonHoverBackgroundStyle = '';
                        if ($buttonHoverBackgroundType === 'gradient') {
                            $hoverGradientStartRgba = hexToRgbaButton($buttonHoverGradientStart, $buttonHoverOpacity);
                            $hoverGradientEndRgba = hexToRgbaButton($buttonHoverGradientEnd, $buttonHoverOpacity);
                            $buttonHoverBackgroundStyle = "background: linear-gradient({$buttonHoverGradientAngle}deg, {$hoverGradientStartRgba}, {$hoverGradientEndRgba});";
                        } else {
                            if ($buttonHoverOpacity < 1.0) {
                                $hoverBgColorRgba = hexToRgbaButton($buttonHoverBackgroundColor, $buttonHoverOpacity);
                                $buttonHoverBackgroundStyle = "background-color: {$hoverBgColorRgba};";
                            } else {
                                $buttonHoverBackgroundStyle = "background-color: {$buttonHoverBackgroundColor};";
                            }
                        }
                        
                        // 버튼 border-radius 설정 (원래 테마가 라운드인 경우 라운드 적용)
                        $buttonBorderRadius = $isOriginalRoundTheme ? '0.5rem' : '4px';
                    @endphp
                    @if($buttonText)
                        @if($buttonLink)
                            <a href="{{ $buttonLink }}" 
                               @if($buttonOpenNewTab) target="_blank" rel="noopener noreferrer" @endif
                               style="text-decoration: none; display: inline-block;">
                                <button class="block-widget-button" 
                                        style="border: {{ $buttonBorderWidth }}px solid {{ $buttonBorderColor }}; color: {{ $buttonTextColor }}; {{ $buttonBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                        onmouseover="this.style.cssText = 'border: {{ $buttonBorderWidth }}px solid {{ $buttonHoverBorderColor }}; color: {{ $buttonHoverTextColor }}; {{ $buttonHoverBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;';"
                                        onmouseout="this.style.cssText = 'border: {{ $buttonBorderWidth }}px solid {{ $buttonBorderColor }}; color: {{ $buttonTextColor }}; {{ $buttonBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;';">
                                    {{ $buttonText }}
                                </button>
                            </a>
                        @else
                            <button class="block-widget-button" 
                                    style="border: {{ $buttonBorderWidth }}px solid {{ $buttonBorderColor }}; color: {{ $buttonTextColor }}; {{ $buttonBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                    onmouseover="this.style.cssText = 'border: {{ $buttonBorderWidth }}px solid {{ $buttonHoverBorderColor }}; color: {{ $buttonHoverTextColor }}; {{ $buttonHoverBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;';"
                                    onmouseout="this.style.cssText = 'border: {{ $buttonBorderWidth }}px solid {{ $buttonBorderColor }}; color: {{ $buttonTextColor }}; {{ $buttonBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;';">
                                {{ $buttonText }}
                            </button>
                        @endif
                    @endif
                @endforeach
            </div>
        @endif
        </div>
    </div>
@elseif($widget->type === 'block_slide')
    @php
        $slideSettings = $widgetSettings;
        $slideDirection = $slideSettings['slide_direction'] ?? 'left';
        $slideHoldTime = $slideSettings['slide_hold_time'] ?? 3;
        $blocks = $slideSettings['blocks'] ?? [];
    @endphp
    @if(count($blocks) > 0)
        @php
            $blockSlideWrapperStyle = $animationStyle . ' position: relative; overflow: hidden; width: 100%;';
            // 컨테이너 높이에 맞추기 위한 flex 설정 (block 위젯과 동일하게)
            $blockSlideWrapperStyle .= ' flex: 1; min-height: 0; height: 100%; display: flex; flex-direction: column;';
            // 가로 100%일 때 보더 레디우스 제거 (칸 고정너비일 때는 유지)
            if ($isActualFullWidth) {
                $blockSlideWrapperStyle .= ' border-radius: 0 !important;';
            }
        @endphp
        <div class="mb-0 block-slide-wrapper {{ $shadowClass }} {{ $animationClass }}" 
             style="{{ $blockSlideWrapperStyle }}"
             data-direction="{{ $slideDirection }}" 
             data-hold-time="{{ $slideHoldTime }}"
             data-widget-id="{{ $widget->id }}">
            <div class="block-slide-container" style="display: flex; width: calc(100% * {{ count($blocks) * 2 }}); transition: transform 0.5s ease-in-out; flex: 1; {{ in_array($slideDirection, ['up', 'down']) ? 'flex-direction: column; height: 100%;' : 'height: 100%;' }}">
                @foreach($blocks as $index => $block)
                    @php
                        $blockTitle = $block['title'] ?? '';
                        $blockContent = $block['content'] ?? '';
                        $textAlign = $block['text_align'] ?? 'left';
                        $backgroundType = $block['background_type'] ?? 'color';
                        $backgroundColor = $block['background_color'] ?? '#007bff';
                        $backgroundColorAlpha = isset($block['background_color_alpha']) ? $block['background_color_alpha'] : 100;
                        $backgroundImageUrl = $block['background_image_url'] ?? '';
                        $backgroundImageAlpha = isset($block['background_image_alpha']) ? $block['background_image_alpha'] : 100;
                        $backgroundImageFullWidth = $block['background_image_full_width'] ?? false;
                        $paddingTop = $block['padding_top'] ?? 20;
                        $paddingBottom = $block['padding_bottom'] ?? ($block['padding_top'] ?? 20);
                        $paddingLeft = $block['padding_left'] ?? 20;
                        $paddingRight = $block['padding_right'] ?? ($block['padding_left'] ?? 20);
                        $titleContentGap = $block['title_content_gap'] ?? 8;
                        $link = $block['link'] ?? '';
                        $openNewTab = $block['open_new_tab'] ?? false;
                        // 제목/내용 컬러 분리 (하위 호환성: font_color도 지원)
                        $titleColor = $block['title_color'] ?? $block['font_color'] ?? '#ffffff';
                        $contentColor = $block['content_color'] ?? $block['font_color'] ?? '#ffffff';
                        $titleFontSize = $block['title_font_size'] ?? '16';
                        $contentFontSize = $block['content_font_size'] ?? '14';
                        // 반응형 폰트 사이즈 계산
                        $responsiveTitleFontSize = "clamp(" . round($titleFontSize * 0.65) . "px, " . round($titleFontSize / 8, 1) . "vw, " . $titleFontSize . "px)";
                        $responsiveContentFontSize = "clamp(" . round($contentFontSize * 0.65) . "px, " . round($contentFontSize / 8, 1) . "vw, " . $contentFontSize . "px)";
                        // 버튼 데이터 (하위 호환성: 기존 단일 버튼 데이터도 지원)
                        $buttons = $block['buttons'] ?? [];
                        if (!is_array($buttons)) {
                            $showButton = $block['show_button'] ?? false;
                            if ($showButton && isset($block['button_text'])) {
                                $buttons = [[
                                    'text' => $block['button_text'] ?? '',
                                    'link' => $block['link'] ?? '',
                                    'open_new_tab' => $block['open_new_tab'] ?? false,
                                    'background_color' => $block['button_background_color'] ?? '#007bff',
                                    'text_color' => $block['button_text_color'] ?? '#ffffff'
                                ]];
                            } else {
                                $buttons = [];
                            }
                        }
                        $buttonTopMargin = $block['button_top_margin'] ?? 12;
                        $hasButtons = !empty($buttons);
                        
                        // 스타일 생성
                        $blockStyle = "padding-top: {$paddingTop}px; padding-bottom: {$paddingBottom}px; padding-left: {$paddingLeft}px; padding-right: {$paddingRight}px; text-align: {$textAlign};";
                        
                        if ($backgroundType === 'color') {
                            // 투명도 적용
                            if ($backgroundColorAlpha < 100) {
                                $bgColorRgba = hexToRgbaButton($backgroundColor, $backgroundColorAlpha / 100);
                                $blockStyle .= " background-color: {$bgColorRgba};";
                            } else {
                                $blockStyle .= " background-color: {$backgroundColor};";
                            }
                        } else if ($backgroundType === 'gradient') {
                            $gradientStart = $block['background_gradient_start'] ?? '#ffffff';
                            $gradientEnd = $block['background_gradient_end'] ?? '#000000';
                            $gradientAngle = $block['background_gradient_angle'] ?? 90;
                            $blockStyle .= " background: linear-gradient({$gradientAngle}deg, {$gradientStart}, {$gradientEnd});";
                        } else if ($backgroundType === 'image' && $backgroundImageUrl) {
                            $bgSize = $backgroundImageFullWidth ? '100% auto' : 'cover';
                            $blockStyle .= " background-image: url('{$backgroundImageUrl}'); background-size: {$bgSize}; background-position: center top; background-repeat: no-repeat;";
                            // 이미지 투명도 적용
                            if ($backgroundImageAlpha < 100) {
                                $blockStyle .= " opacity: " . ($backgroundImageAlpha / 100) . ";";
                            }
                        }
                        
                        // 슬라이드 방향에 따른 너비/높이 설정
                        // 각 아이템은 컨테이너 전체 너비의 1/(원본+클론) = wrapper 100%가 되도록 설정
                        $totalSlideItems = count($blocks) * 2; // 원본 + 클론
                        if (in_array($slideDirection, ['left', 'right'])) {
                            $blockStyle .= " width: calc(100% / {$totalSlideItems}); height: 100%; flex-shrink: 0;";
                        } else {
                            $blockStyle .= " width: 100%; height: calc(100% / {$totalSlideItems}); flex-shrink: 0;";
                        }
                        
                        // 컨테이너 정렬에 따라 justify-content 설정 (블록슬라이드 아이템 내부 정렬)
                        $justifyContent = 'center';
                        if ($verticalAlign === 'top') {
                            $justifyContent = 'flex-start';
                        } elseif ($verticalAlign === 'bottom') {
                            $justifyContent = 'flex-end';
                        } else {
                            $justifyContent = 'center';
                        }
                        // 블록슬라이드 아이템을 flex 컨테이너로 만들어서 내부 컨텐츠 정렬
                        $blockStyle .= " display: flex; flex-direction: column; justify-content: {$justifyContent};";
                    @endphp
                    <div class="block-slide-item" style="{{ $blockStyle }}" data-index="{{ $index }}">
                        @if($link && !$hasButtons)
                            <a href="{{ $link }}" 
                               style="text-decoration: none; display: flex; flex-direction: column; flex: 1; height: 100%;"
                               @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
                        @endif
                        @if($blockTitle)
                            <h4 style="color: {{ $titleColor }}; font-weight: bold; font-size: {{ $responsiveTitleFontSize }}; margin-bottom: {{ $titleContentGap }}px;">{!! nl2br(e($blockTitle)) !!}</h4>
                        @endif
                        @if($blockContent)
                            <p class="mb-0" style="color: {{ $contentColor }}; font-size: {{ $responsiveContentFontSize }}; white-space: pre-wrap;">{{ $blockContent }}</p>
                        @endif
                        @if($link && !$hasButtons)
                            </a>
                        @endif
                        @if($hasButtons)
                            @php
                                $justifyContent = 'flex-start';
                                if ($textAlign === 'center') {
                                    $justifyContent = 'center';
                                } elseif ($textAlign === 'right') {
                                    $justifyContent = 'flex-end';
                                }
                            @endphp
                            <div style="margin-top: {{ $buttonTopMargin }}px; display: flex; flex-direction: row; flex-wrap: wrap; gap: 8px; justify-content: {{ $justifyContent }};">
                                @foreach($buttons as $button)
                                    @php
                                        $buttonText = $button['text'] ?? '';
                                        $buttonLink = $button['link'] ?? '';
                                        $buttonOpenNewTab = $button['open_new_tab'] ?? false;
                                        $buttonBackgroundColor = $button['background_color'] ?? '#007bff';
                                        $buttonTextColor = $button['text_color'] ?? '#ffffff';
                                        $buttonBorderColor = $button['border_color'] ?? $buttonBackgroundColor;
                                        $buttonBorderWidth = $button['border_width'] ?? '2';
                                        $buttonHoverBackgroundColor = $button['hover_background_color'] ?? '#0056b3';
                                        $buttonHoverTextColor = $button['hover_text_color'] ?? '#ffffff';
                                        $buttonHoverBorderColor = $button['hover_border_color'] ?? '#0056b3';
                                        $buttonColor = $buttonBackgroundColor;
                                        
                                        // 버튼 배경 타입 및 그라데이션 설정
                                        $buttonBackgroundType = $button['background_type'] ?? 'color';
                                        $buttonGradientStart = $button['background_gradient_start'] ?? $buttonBackgroundColor;
                                        $buttonGradientEnd = $button['background_gradient_end'] ?? $buttonHoverBackgroundColor;
                                        $buttonGradientAngle = $button['background_gradient_angle'] ?? 90;
                                        $buttonOpacity = isset($button['opacity']) ? floatval($button['opacity']) : 1.0;
                                        
                                        // 버튼 배경 스타일 생성 (투명도는 배경색에만 적용)
                                        $buttonBackgroundStyle = '';
                                        if ($buttonBackgroundType === 'gradient') {
                                            $gradientStartRgba = hexToRgbaButton($buttonGradientStart, $buttonOpacity);
                                            $gradientEndRgba = hexToRgbaButton($buttonGradientEnd, $buttonOpacity);
                                            $buttonBackgroundStyle = "background: linear-gradient({$buttonGradientAngle}deg, {$gradientStartRgba}, {$gradientEndRgba});";
                                        } else {
                                            if ($buttonOpacity < 1.0) {
                                                $bgColorRgba = hexToRgbaButton($buttonBackgroundColor, $buttonOpacity);
                                                $buttonBackgroundStyle = "background-color: {$bgColorRgba};";
                                            } else {
                                                $buttonBackgroundStyle = "background-color: {$buttonBackgroundColor};";
                                            }
                                        }
                                        
                                        // Hover 배경 스타일 생성
                                        $buttonHoverBackgroundType = $button['hover_background_type'] ?? 'color';
                                        $buttonHoverGradientStart = $button['hover_background_gradient_start'] ?? $buttonHoverBackgroundColor;
                                        $buttonHoverGradientEnd = $button['hover_background_gradient_end'] ?? $buttonHoverBackgroundColor;
                                        $buttonHoverGradientAngle = $button['hover_background_gradient_angle'] ?? 90;
                                        $buttonHoverOpacity = isset($button['hover_opacity']) ? floatval($button['hover_opacity']) : 1.0;
                                        
                                        // Hover 배경 스타일 (투명도는 배경색에만 적용)
                                        $buttonHoverBackgroundStyle = '';
                                        if ($buttonHoverBackgroundType === 'gradient') {
                                            $hoverGradientStartRgba = hexToRgbaButton($buttonHoverGradientStart, $buttonHoverOpacity);
                                            $hoverGradientEndRgba = hexToRgbaButton($buttonHoverGradientEnd, $buttonHoverOpacity);
                                            $buttonHoverBackgroundStyle = "background: linear-gradient({$buttonHoverGradientAngle}deg, {$hoverGradientStartRgba}, {$hoverGradientEndRgba});";
                                        } else {
                                            if ($buttonHoverOpacity < 1.0) {
                                                $hoverBgColorRgba = hexToRgbaButton($buttonHoverBackgroundColor, $buttonHoverOpacity);
                                                $buttonHoverBackgroundStyle = "background-color: {$hoverBgColorRgba};";
                                            } else {
                                                $buttonHoverBackgroundStyle = "background-color: {$buttonHoverBackgroundColor};";
                                            }
                                        }
                                        
                                        // 버튼 border-radius 설정 (원래 테마가 라운드인 경우 라운드 적용)
                                        $buttonBorderRadius = $isOriginalRoundTheme ? '0.5rem' : '4px';
                                    @endphp
                                    @if($buttonText)
                                        @if($buttonLink)
                                            <a href="{{ $buttonLink }}" 
                                               @if($buttonOpenNewTab) target="_blank" rel="noopener noreferrer" @endif
                                               style="text-decoration: none; display: inline-block;">
                                                <button class="block-widget-button" 
                                                        style="border: {{ $buttonBorderWidth }}px solid {{ $buttonBorderColor }}; color: {{ $buttonTextColor }}; {{ $buttonBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                                        onmouseover="this.style.cssText = 'border: {{ $buttonBorderWidth }}px solid {{ $buttonHoverBorderColor }}; color: {{ $buttonHoverTextColor }}; {{ $buttonHoverBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;';"
                                                        onmouseout="this.style.cssText = 'border: {{ $buttonBorderWidth }}px solid {{ $buttonBorderColor }}; color: {{ $buttonTextColor }}; {{ $buttonBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;';">
                                                    {{ $buttonText }}
                                                </button>
                                            </a>
                                        @else
                                            <button class="block-widget-button" 
                                                    style="border: {{ $buttonBorderWidth }}px solid {{ $buttonBorderColor }}; color: {{ $buttonTextColor }}; {{ $buttonBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                                    onmouseover="this.style.cssText = 'border: {{ $buttonBorderWidth }}px solid {{ $buttonHoverBorderColor }}; color: {{ $buttonHoverTextColor }}; {{ $buttonHoverBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;';"
                                                    onmouseout="this.style.cssText = 'border: {{ $buttonBorderWidth }}px solid {{ $buttonBorderColor }}; color: {{ $buttonTextColor }}; {{ $buttonBackgroundStyle }} padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;';">
                                                {{ $buttonText }}
                                            </button>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
                {{-- 무한 슬라이드를 위한 블록 복제 --}}
                @foreach($blocks as $index => $block)
                    @php
                        $blockTitle = $block['title'] ?? '';
                        $blockContent = $block['content'] ?? '';
                        $textAlign = $block['text_align'] ?? 'left';
                        $backgroundType = $block['background_type'] ?? 'color';
                        $backgroundColor = $block['background_color'] ?? '#007bff';
                        $backgroundColorAlpha = isset($block['background_color_alpha']) ? $block['background_color_alpha'] : 100;
                        $backgroundImageUrl = $block['background_image_url'] ?? '';
                        $backgroundImageAlpha = isset($block['background_image_alpha']) ? $block['background_image_alpha'] : 100;
                        $backgroundImageFullWidth = $block['background_image_full_width'] ?? false;
                        $paddingTop = $block['padding_top'] ?? 20;
                        $paddingLeft = $block['padding_left'] ?? 20;
                        $link = $block['link'] ?? '';
                        $openNewTab = $block['open_new_tab'] ?? false;
                        // 제목/내용 컬러 분리 (하위 호환성: font_color도 지원)
                        $titleColor = $block['title_color'] ?? $block['font_color'] ?? '#ffffff';
                        $contentColor = $block['content_color'] ?? $block['font_color'] ?? '#ffffff';
                        
                        // 스타일 생성
                        $blockStyle = "padding-top: {$paddingTop}px; padding-bottom: {$paddingTop}px; padding-left: {$paddingLeft}px; padding-right: {$paddingLeft}px; text-align: {$textAlign};";
                        
                        if ($backgroundType === 'color') {
                            // 투명도 적용
                            if ($backgroundColorAlpha < 100) {
                                $bgColorRgba = hexToRgbaButton($backgroundColor, $backgroundColorAlpha / 100);
                                $blockStyle .= " background-color: {$bgColorRgba};";
                            } else {
                                $blockStyle .= " background-color: {$backgroundColor};";
                            }
                        } else if ($backgroundType === 'gradient') {
                            $gradientStart = $block['background_gradient_start'] ?? '#ffffff';
                            $gradientEnd = $block['background_gradient_end'] ?? '#000000';
                            $gradientAngle = $block['background_gradient_angle'] ?? 90;
                            $blockStyle .= " background: linear-gradient({$gradientAngle}deg, {$gradientStart}, {$gradientEnd});";
                        } else if ($backgroundType === 'image' && $backgroundImageUrl) {
                            $bgSize = $backgroundImageFullWidth ? '100% auto' : 'cover';
                            $blockStyle .= " background-image: url('{$backgroundImageUrl}'); background-size: {$bgSize}; background-position: center top; background-repeat: no-repeat;";
                            // 이미지 투명도 적용
                            if ($backgroundImageAlpha < 100) {
                                $blockStyle .= " opacity: " . ($backgroundImageAlpha / 100) . ";";
                            }
                        }
                        
                        // 슬라이드 방향에 따른 너비/높이 설정
                        // 클론 아이템도 원본과 동일한 너비로 설정
                        $totalSlideItemsClone = count($blocks) * 2; // 원본 + 클론
                        if (in_array($slideDirection, ['left', 'right'])) {
                            $blockStyle .= " width: calc(100% / {$totalSlideItemsClone}); height: 100%; flex-shrink: 0;";
                        } else {
                            $blockStyle .= " width: 100%; height: calc(100% / {$totalSlideItemsClone}); flex-shrink: 0;";
                        }
                        
                        // 클론에도 원본과 동일한 flex 정렬 적용
                        $justifyContentClone = 'center';
                        if ($verticalAlign === 'top') {
                            $justifyContentClone = 'flex-start';
                        } elseif ($verticalAlign === 'bottom') {
                            $justifyContentClone = 'flex-end';
                        }
                        $blockStyle .= " display: flex; flex-direction: column; justify-content: {$justifyContentClone};";
                    @endphp
                    @php
                        $titleFontSize = $block['title_font_size'] ?? '16';
                        $contentFontSize = $block['content_font_size'] ?? '14';
                        // 반응형 폰트 사이즈 계산
                        $responsiveTitleFontSize = "clamp(" . round($titleFontSize * 0.65) . "px, " . round($titleFontSize / 8, 1) . "vw, " . $titleFontSize . "px)";
                        $responsiveContentFontSize = "clamp(" . round($contentFontSize * 0.65) . "px, " . round($contentFontSize / 8, 1) . "vw, " . $contentFontSize . "px)";
                        $showButton = $block['show_button'] ?? false;
                        $buttonText = $block['button_text'] ?? '';
                        $buttonBackgroundColor = $block['button_background_color'] ?? '#007bff';
                        $buttonTextColor = $block['button_text_color'] ?? '#ffffff';
                    @endphp
                    <div class="block-slide-item block-slide-item-clone" style="{{ $blockStyle }}" data-index="{{ $index }}">
                        @if($link && !$showButton)
                            <a href="{{ $link }}" 
                               style="text-decoration: none; display: block;"
                               @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
                        @endif
                        @if($blockTitle)
                            <h4 class="mb-2" style="color: {{ $titleColor }}; font-weight: bold; font-size: {{ $responsiveTitleFontSize }};">{!! nl2br(e($blockTitle)) !!}</h4>
                        @endif
                        @if($blockContent)
                            <p class="mb-0" style="color: {{ $contentColor }}; font-size: {{ $responsiveContentFontSize }}; white-space: pre-wrap;">{{ $blockContent }}</p>
                        @endif
                        @if($link && !$showButton)
                            </a>
                        @endif
                        @if($showButton && $buttonText)
                            @php
                                // 버튼 border-radius 설정 (원래 테마가 라운드인 경우 라운드 적용)
                                $buttonBorderRadius = $isOriginalRoundTheme ? '0.5rem' : '4px';
                            @endphp
                            <div class="mt-3" style="text-align: {{ $textAlign }};">
                                @if($link)
                                    <a href="{{ $link }}" 
                                       @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                       style="text-decoration: none; display: inline-block;">
                                        <button class="block-widget-button" 
                                                style="border: 2px solid {{ $buttonColor }}; color: {{ $buttonColor }}; background-color: transparent; padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                                onmouseover="this.style.backgroundColor='{{ $buttonColor }}'; this.style.color='#ffffff';"
                                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $buttonColor }}';">
                                            {{ $buttonText }}
                                        </button>
                                    </a>
                                @else
                                    <button class="block-widget-button" 
                                            style="border: 2px solid {{ $buttonColor }}; color: {{ $buttonColor }}; background-color: transparent; padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                            onmouseover="this.style.backgroundColor='{{ $buttonColor }}'; this.style.color='#ffffff';"
                                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $buttonColor }}';">
                                        {{ $buttonText }}
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <script>
        (function() {
            const wrapper = document.querySelector('.block-slide-wrapper[data-widget-id="{{ $widget->id }}"]');
            if (!wrapper) return;
            
            const container = wrapper.querySelector('.block-slide-container');
            const items = wrapper.querySelectorAll('.block-slide-item:not(.block-slide-item-clone)');
            const direction = wrapper.dataset.direction;
            const totalItems = items.length;
            
            if (totalItems <= 1) return;
            
            let currentIndex = 0;
            let isTransitioning = false;
            
            // 각 슬라이드 아이템이 컨테이너에서 차지하는 비율 (원본 + 클론 = totalItems * 2)
            const itemPercentage = 100 / (totalItems * 2);
            
            // 위치 업데이트 (애니메이션 포함)
            function updatePosition() {
                container.style.transition = 'transform 0.5s ease-in-out';
                
                if (direction === 'left' || direction === 'right') {
                    container.style.transform = `translateX(-${currentIndex * itemPercentage}%)`;
                } else if (direction === 'up' || direction === 'down') {
                    container.style.flexDirection = direction === 'up' ? 'column-reverse' : 'column';
                    container.style.transform = `translateY(-${currentIndex * itemPercentage}%)`;
                }
            }
            
            // 위치 즉시 이동 (애니메이션 없이)
            function resetPosition() {
                // transition 완전히 비활성화
                container.style.transition = 'none';
                
                if (direction === 'left' || direction === 'right') {
                    container.style.transform = `translateX(-${currentIndex * itemPercentage}%)`;
                } else if (direction === 'up' || direction === 'down') {
                    container.style.transform = `translateY(-${currentIndex * itemPercentage}%)`;
                }
                
                // 강제 reflow로 즉시 적용
                void container.offsetHeight;
            }
            
            // 슬라이드 전환
            function nextSlide() {
                if (isTransitioning) return;
                
                isTransitioning = true;
                currentIndex++;
                updatePosition();
                
                // 마지막 원본 블록 다음(클론 첫번째)에 도달하면 원본 첫번째로 점프
                if (currentIndex >= totalItems) {
                    setTimeout(() => {
                        // 클론에서 원본으로 즉시 점프 (눈에 안 보이게)
                        currentIndex = 0;
                        resetPosition();
                        
                        // 충분한 시간 후에 transition 복구 및 다음 슬라이드 허용
                        setTimeout(() => {
                            isTransitioning = false;
                        }, 50);
                    }, 500); // transition 완료 후
                } else {
                    setTimeout(() => {
                        isTransitioning = false;
                    }, 500);
                }
            }
            
            // 초기 위치 설정
            updatePosition();
            
            // 슬라이드 유지 시간 (초 단위, 기본 3초)
            const holdTime = parseFloat(wrapper.dataset.holdTime) || 3;
            setInterval(nextSlide, holdTime * 1000);
        })();
        </script>
    @endif
@elseif($widget->type === 'image')
    @php
        $imageSettings = $widgetSettings;
        $imageUrl = $imageSettings['image_url'] ?? '';
        $link = $imageSettings['link'] ?? '';
        $openNewTab = $imageSettings['open_new_tab'] ?? false;
        
        // 이미지 width 설정 (% 값, 기본 100%)
        $imageWidth = $imageSettings['image_width'] ?? 100;
        $imageWidth = max(1, min(100, (int)$imageWidth)); // 1~100% 범위 제한
        
        // 이미지 위젯 상단/하단 마진
        $imageMarginTop = $imageSettings['margin_top'] ?? 0;
        $imageMarginBottom = $imageSettings['margin_bottom'] ?? 0;
        
        // 텍스트 오버레이 설정
        $textOverlay = $imageSettings['text_overlay'] ?? false;
        $title = $imageSettings['title'] ?? '';
        $titleFontSize = $imageSettings['title_font_size'] ?? 24;
        $content = $imageSettings['content'] ?? '';
        $contentFontSize = $imageSettings['content_font_size'] ?? 16;
        $titleContentGap = $imageSettings['title_content_gap'] ?? 10;
        $textPaddingLeft = $imageSettings['text_padding_left'] ?? 0;
        $textPaddingRight = $imageSettings['text_padding_right'] ?? 0;
        $textPaddingTop = $imageSettings['text_padding_top'] ?? 0;
        $textPaddingBottom = $imageSettings['text_padding_bottom'] ?? 10;
        $alignH = $imageSettings['align_h'] ?? 'left';
        $alignV = $imageSettings['align_v'] ?? 'middle';
        $textColor = $imageSettings['text_color'] ?? '#ffffff';
        $hasButton = $imageSettings['has_button'] ?? false;
        $buttonText = $imageSettings['button_text'] ?? '';
        $buttonLink = $imageSettings['button_link'] ?? '';
        $buttonNewTab = $imageSettings['button_new_tab'] ?? false;
        $buttonColor = $imageSettings['button_color'] ?? '#0d6efd';
        $buttonTextColor = $imageSettings['button_text_color'] ?? '#ffffff';
        $buttonBorderColor = $imageSettings['button_border_color'] ?? '#0d6efd';
        $buttonOpacity = $imageSettings['button_opacity'] ?? 100;
        $buttonHoverBgColor = $imageSettings['button_hover_bg_color'] ?? '#0b5ed7';
        $buttonHoverTextColor = $imageSettings['button_hover_text_color'] ?? '#ffffff';
        $buttonHoverBorderColor = $imageSettings['button_hover_border_color'] ?? '#0a58ca';
        
        // 반응형 폰트 사이즈 계산
        $responsiveTitleFontSize = 'clamp(' . ($titleFontSize * 0.65) . 'px, ' . ($titleFontSize / 8) . 'vw, ' . $titleFontSize . 'px)';
        $responsiveContentFontSize = 'clamp(' . ($contentFontSize * 0.65) . 'px, ' . ($contentFontSize / 8) . 'vw, ' . $contentFontSize . 'px)';
        
        // 정렬 설정
        $justifyContent = 'center';
        if ($alignH === 'left') {
            $justifyContent = 'flex-start';
        } elseif ($alignH === 'right') {
            $justifyContent = 'flex-end';
        }
        
        $alignItems = 'center';
        if ($alignV === 'top') {
            $alignItems = 'flex-start';
        } elseif ($alignV === 'bottom') {
            $alignItems = 'flex-end';
        }
        
        // 버튼 스타일 생성
        $buttonBgColor = $buttonColor;
        if ($buttonOpacity < 100) {
            $rgb = sscanf($buttonColor, "#%02x%02x%02x");
            $buttonBgColor = 'rgba(' . $rgb[0] . ', ' . $rgb[1] . ', ' . $rgb[2] . ', ' . ($buttonOpacity / 100) . ')';
        }
        $buttonStyle = 'background-color: ' . $buttonBgColor . '; color: ' . $buttonTextColor . '; border-color: ' . $buttonBorderColor . ';';
        $buttonHoverStyle = 'background-color: ' . $buttonHoverBgColor . '; color: ' . $buttonHoverTextColor . '; border-color: ' . $buttonHoverBorderColor . ';';
        
        // 컨테이너 정렬에 따라 justify-content 설정
        $imageJustifyContent = 'center';
        if ($verticalAlign === 'top') {
            $imageJustifyContent = 'flex-start';
        } elseif ($verticalAlign === 'bottom') {
            $imageJustifyContent = 'flex-end';
        }
        
        // 이미지 위젯도 같은 높이를 가지도록 flex 적용 및 세로 정렬 추가
        $imageWidgetStyle = 'display: flex; flex-direction: column; flex: 1; justify-content: ' . $imageJustifyContent . ';';
        
        // 상단/하단 마진 적용 - 마지막 위젯일 경우 하단 마진 제거
        if ($imageMarginTop > 0) {
            $imageWidgetStyle .= ' margin-top: ' . $imageMarginTop . 'px !important;';
        } else {
            $imageWidgetStyle .= ' margin-top: 0 !important;';
        }
        // 마지막 위젯인 경우 하단 마진 강제로 0 (컨테이너 세로 정렬을 위해)
        if ($isLastWidget || $imageMarginBottom == 0) {
            $imageWidgetStyle .= ' margin-bottom: 0 !important;';
        } else {
            $imageWidgetStyle .= ' margin-bottom: ' . $imageMarginBottom . 'px !important;';
        }
        
        // 이미지 링크 및 이미지 자체의 스타일
        $imageLinkStyle = $isRoundTheme ? 'display: block; border-radius: 0.5rem; overflow: hidden; width: 100%;' : 'display: block; width: 100%;';
        // 이미지에 margin: auto를 추가하여 flex 컨테이너에서 세로 중앙 정렬
        $imageStyle = 'width: 100%; height: auto; display: block; margin: auto 0;' . ($isRoundTheme ? ' border-radius: 0.5rem;' : '');
        
        // 이미지 width가 100% 미만일 때 max-width 적용 및 중앙 정렬
        $imageContainerStyle = 'position: relative; width: 100%;';
        if ($imageWidth < 100) {
            $imageContainerStyle .= ' display: flex; justify-content: center;';
            $imageLinkStyle = $isRoundTheme ? 'display: block; border-radius: 0.5rem; overflow: hidden; width: ' . $imageWidth . '%;' : 'display: block; width: ' . $imageWidth . '%;';
            $imageStyle = 'width: 100%; height: auto; display: block;' . ($isRoundTheme ? ' border-radius: 0.5rem;' : '');
        }
    @endphp
    @if($imageUrl)
        <div class="mb-0 {{ $shadowClass }} {{ $animationClass }} {{ $isRoundTheme ? '' : 'rounded-0' }}" style="{{ $isRoundTheme ? 'border-radius: 0.5rem; overflow: hidden;' : '' }} {{ $animationStyle }} width: 100%; max-width: 100%; {{ $imageWidgetStyle }}" data-widget-id="{{ $widget->id }}">
            <div style="{{ $imageContainerStyle }}">
                @if($link && !$hasButton)
                    <a href="{{ $link }}" 
                       @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                       style="{{ $imageLinkStyle }}">
                @elseif($imageWidth < 100)
                    <div style="width: {{ $imageWidth }}%;">
                @endif
                <img src="{{ $imageUrl }}" alt="이미지" style="{{ $imageStyle }}">
                @if($link && !$hasButton)
                    </a>
                @elseif($imageWidth < 100)
                    </div>
                @endif
                
                @if($textOverlay && ($title || $content))
                    <div class="image-text-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; justify-content: {{ $justifyContent }}; align-items: {{ $alignItems }}; padding: {{ $textPaddingTop }}px {{ $textPaddingRight }}px {{ $textPaddingBottom }}px {{ $textPaddingLeft }}px; pointer-events: none; z-index: 2;">
                        <div style="pointer-events: auto; text-align: {{ $alignH === 'center' ? 'center' : ($alignH === 'right' ? 'right' : 'left') }};">
                            @if($title)
                                <h3 style="color: {{ $textColor }}; font-size: {{ $responsiveTitleFontSize }}; margin: 0{{ $content ? ' 0 ' . $titleContentGap . 'px 0' : '' }};">{{ $title }}</h3>
                            @endif
                            @if($content)
                                <p style="color: {{ $textColor }}; font-size: {{ $responsiveContentFontSize }}; margin: 0;">{{ $content }}</p>
                            @endif
                            @if($hasButton && $buttonText)
                                <div style="margin-top: {{ $titleContentGap }}px;">
                                    <a href="{{ $buttonLink }}" 
                                       @if($buttonNewTab) target="_blank" rel="noopener noreferrer" @endif
                                       class="image-button"
                                       style="{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;"
                                       onmouseover="this.style.cssText += '{{ $buttonHoverStyle }}'"
                                       onmouseout="this.style.cssText = '{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;'">
                                        {{ $buttonText }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($hasButton && $buttonText)
                    <div class="image-text-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; justify-content: {{ $justifyContent }}; align-items: {{ $alignItems }}; padding: {{ $textPaddingTop }}px {{ $textPaddingRight }}px {{ $textPaddingBottom }}px {{ $textPaddingLeft }}px; pointer-events: none; z-index: 2;">
                        <div style="pointer-events: auto;">
                            <a href="{{ $buttonLink }}" 
                               @if($buttonNewTab) target="_blank" rel="noopener noreferrer" @endif
                               class="image-button"
                               style="{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;"
                               onmouseover="this.style.cssText += '{{ $buttonHoverStyle }}'"
                               onmouseout="this.style.cssText = '{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;'">
                                {{ $buttonText }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@elseif($widget->type === 'image_slide')
    @php
        $imageSlideSettings = $widgetSettings;
        $slideDirection = $imageSlideSettings['slide_direction'] ?? 'left';
        $slideMode = $imageSlideSettings['slide_mode'] ?? 'single';
        $slideSpeed = $imageSlideSettings['slide_speed'] ?? 3.0;
        $visibleCount = $imageSlideSettings['visible_count'] ?? 3;
        $visibleCountMobile = $imageSlideSettings['visible_count_mobile'] ?? 2;
        $imageGap = $imageSlideSettings['image_gap'] ?? 0;
        $backgroundType = $imageSlideSettings['background_type'] ?? 'none';
        $backgroundColor = $imageSlideSettings['background_color'] ?? '#ffffff';
        $images = $imageSlideSettings['images'] ?? [];
        
        // 배경색 스타일 생성
        $backgroundStyle = '';
        if ($slideMode === 'infinite' && $backgroundType === 'color') {
            $backgroundStyle = 'background-color: ' . $backgroundColor . ';';
        }
    @endphp
    @if(count($images) > 0)
        <div class="mb-0 image-slide-wrapper {{ $shadowClass }} {{ $animationClass }} {{ $isRoundTheme ? '' : 'rounded-0' }}" 
             data-direction="{{ $slideDirection }}" 
             data-mode="{{ $slideMode }}"
             data-slide-speed="{{ $slideSpeed }}"
             data-visible-count="{{ $visibleCount }}"
             data-visible-count-mobile="{{ $visibleCountMobile }}"
             data-image-gap="{{ $imageGap }}"
             data-widget-id="{{ $widget->id }}"
             style="position: relative; overflow: hidden; {{ ($slideMode === 'single' && in_array($slideDirection, ['up', 'down'])) ? 'height: 200px;' : '' }}{{ $isRoundTheme ? ' border-radius: 0.5rem;' : '' }} {{ $backgroundStyle }} {{ $animationStyle }} width: 100%;">
            <div class="image-slide-container" style="display: flex; {{ $slideMode === 'infinite' ? 'flex-direction: row;' : '' }} {{ ($slideMode === 'single' && in_array($slideDirection, ['up', 'down'])) ? 'flex-direction: column; height: 100%;' : '' }}{{ $slideMode === 'single' ? ' transition: transform 0.5s ease-in-out;' : '' }}">
                @if($slideMode === 'single')
                    @foreach($images as $index => $image)
                        @php
                            $imageUrl = $image['image_url'] ?? '';
                            $link = $image['link'] ?? '';
                            $openNewTab = $image['open_new_tab'] ?? false;
                            $textOverlay = $image['text_overlay'] ?? false;
                            $title = $image['title'] ?? '';
                            $titleFontSize = $image['title_font_size'] ?? 24;
                            $content = $image['content'] ?? '';
                            $contentFontSize = $image['content_font_size'] ?? 16;
                            // 반응형 폰트 사이즈 계산 - clamp(최소, 선호, 최대)
                            $responsiveTitleFontSize = "clamp(" . round($titleFontSize * 0.65) . "px, " . round($titleFontSize / 8, 1) . "vw, " . $titleFontSize . "px)";
                            $responsiveContentFontSize = "clamp(" . round($contentFontSize * 0.65) . "px, " . round($contentFontSize / 8, 1) . "vw, " . $contentFontSize . "px)";
                            $titleContentGap = $image['title_content_gap'] ?? 10;
                            $textPaddingLeft = $image['text_padding_left'] ?? 0;
                            $textPaddingRight = $image['text_padding_right'] ?? 0;
                            $textPaddingTop = $image['text_padding_top'] ?? 0;
                            $textPaddingBottom = $image['text_padding_bottom'] ?? 0;
                            $alignH = $image['align_h'] ?? 'left';
                            $alignV = $image['align_v'] ?? 'middle';
                            $textColor = $image['text_color'] ?? '#ffffff';
                            $hasButton = $image['has_button'] ?? false;
                            $buttonText = $image['button_text'] ?? '';
                            $buttonLink = $image['button_link'] ?? '';
                            $buttonNewTab = $image['button_new_tab'] ?? false;
                            $buttonColor = $image['button_color'] ?? '#0d6efd';
                            $buttonTextColor = $image['button_text_color'] ?? '#ffffff';
                            $buttonBorderColor = $image['button_border_color'] ?? '#0d6efd';
                            $buttonOpacity = $image['button_opacity'] ?? 100;
                            $buttonHoverBgColor = $image['button_hover_bg_color'] ?? '#0b5ed7';
                            $buttonHoverTextColor = $image['button_hover_text_color'] ?? '#ffffff';
                            $buttonHoverBorderColor = $image['button_hover_border_color'] ?? '#0a58ca';
                            
                            // 정렬 스타일 생성
                            $textAlignStyle = '';
                            $justifyContent = 'center';
                            $alignItems = 'center';
                            if ($alignH === 'left') $justifyContent = 'flex-start';
                            if ($alignH === 'right') $justifyContent = 'flex-end';
                            if ($alignV === 'top') $alignItems = 'flex-start';
                            if ($alignV === 'bottom') $alignItems = 'flex-end';
                            
                            // 버튼 스타일 생성
                            $buttonStyle = '';
                            $buttonHoverStyle = '';
                            if ($hasButton && $buttonText) {
                                $rgbaColor = $buttonColor;
                                if (strlen($buttonColor) === 7) {
                                    $hex = str_replace('#', '', $buttonColor);
                                    $r = hexdec(substr($hex, 0, 2));
                                    $g = hexdec(substr($hex, 2, 2));
                                    $b = hexdec(substr($hex, 4, 2));
                                    $alpha = $buttonOpacity / 100;
                                    $rgbaColor = "rgba({$r}, {$g}, {$b}, {$alpha})";
                                }
                                $buttonStyle = "background-color: {$rgbaColor}; color: {$buttonTextColor}; border-color: {$buttonBorderColor};";
                                $buttonHoverStyle = "background-color: {$buttonHoverBgColor}; color: {$buttonHoverTextColor}; border-color: {$buttonHoverBorderColor};";
                            }
                        @endphp
                        @if($imageUrl)
                            <div class="image-slide-item" style="width: 100%; flex-shrink: 0; position: relative; {{ in_array($slideDirection, ['up', 'down']) ? 'height: 100%;' : '' }}">
                                <img src="{{ $imageUrl }}" alt="이미지 {{ $index + 1 }}" style="width: 100%; height: auto; display: block;">
                                @if($textOverlay && ($title || $content))
                                    <div class="image-slide-text-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; justify-content: {{ $justifyContent }}; align-items: {{ $alignItems }}; padding: {{ $textPaddingTop }}px {{ $textPaddingRight }}px {{ $textPaddingBottom }}px {{ $textPaddingLeft }}px; pointer-events: none; z-index: 2;">
                                        <div style="pointer-events: auto; text-align: {{ $alignH === 'center' ? 'center' : ($alignH === 'right' ? 'right' : 'left') }};">
                                            @if($title)
                                                <h3 style="color: {{ $textColor }}; font-size: {{ $responsiveTitleFontSize }}; margin: 0{{ $content ? ' 0 ' . $titleContentGap . 'px 0' : '' }};">{{ $title }}</h3>
                                            @endif
                                            @if($content)
                                                <p style="color: {{ $textColor }}; font-size: {{ $responsiveContentFontSize }}; margin: 0;">{{ $content }}</p>
                                            @endif
                                            @if($hasButton && $buttonText)
                                                <a href="{{ $buttonLink }}" 
                                                   @if($buttonNewTab) target="_blank" rel="noopener noreferrer" @endif
                                                   class="image-slide-button"
                                                   style="{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 15px; transition: all 0.3s ease;"
                                                   onmouseover="this.style.cssText += '{{ $buttonHoverStyle }}'"
                                                   onmouseout="this.style.cssText = '{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 15px; transition: all 0.3s ease;'">
                                                    {{ $buttonText }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- 텍스트 오버레이가 있고 버튼이 없지만 링크가 있는 경우 --}}
                                    @if($link && !$hasButton)
                                        <a href="{{ $link }}" 
                                           @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                           style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: block; z-index: 1;">
                                        </a>
                                    @endif
                                @elseif($hasButton && $buttonText)
                                    <div class="image-slide-text-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; justify-content: {{ $justifyContent }}; align-items: {{ $alignItems }}; padding: {{ $textPaddingTop }}px {{ $textPaddingRight }}px {{ $textPaddingBottom }}px {{ $textPaddingLeft }}px; pointer-events: none; z-index: 2;">
                                        <div style="pointer-events: auto;">
                                            <a href="{{ $buttonLink }}" 
                                               @if($buttonNewTab) target="_blank" rel="noopener noreferrer" @endif
                                               class="image-slide-button"
                                               style="{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;"
                                               onmouseover="this.style.cssText += '{{ $buttonHoverStyle }}'"
                                               onmouseout="this.style.cssText = '{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;'">
                                                {{ $buttonText }}
                                            </a>
                                        </div>
                                    </div>
                                @elseif($link && !$hasButton)
                                    <a href="{{ $link }}" 
                                       @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                       style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: block; z-index: 1;">
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endforeach
                    {{-- 무한 슬라이드를 위한 이미지 복제 --}}
                    @foreach($images as $index => $image)
                        @php
                            $imageUrl = $image['image_url'] ?? '';
                            $link = $image['link'] ?? '';
                            $openNewTab = $image['open_new_tab'] ?? false;
                            $textOverlay = $image['text_overlay'] ?? false;
                            $title = $image['title'] ?? '';
                            $titleFontSize = $image['title_font_size'] ?? 24;
                            $content = $image['content'] ?? '';
                            $contentFontSize = $image['content_font_size'] ?? 16;
                            // 반응형 폰트 사이즈 계산 - clamp(최소, 선호, 최대)
                            $responsiveTitleFontSize = "clamp(" . round($titleFontSize * 0.65) . "px, " . round($titleFontSize / 8, 1) . "vw, " . $titleFontSize . "px)";
                            $responsiveContentFontSize = "clamp(" . round($contentFontSize * 0.65) . "px, " . round($contentFontSize / 8, 1) . "vw, " . $contentFontSize . "px)";
                            $titleContentGap = $image['title_content_gap'] ?? 10;
                            $textPaddingLeft = $image['text_padding_left'] ?? 0;
                            $textPaddingRight = $image['text_padding_right'] ?? 0;
                            $textPaddingTop = $image['text_padding_top'] ?? 0;
                            $textPaddingBottom = $image['text_padding_bottom'] ?? 0;
                            $alignH = $image['align_h'] ?? 'left';
                            $alignV = $image['align_v'] ?? 'middle';
                            $textColor = $image['text_color'] ?? '#ffffff';
                            $hasButton = $image['has_button'] ?? false;
                            $buttonText = $image['button_text'] ?? '';
                            $buttonLink = $image['button_link'] ?? '';
                            $buttonNewTab = $image['button_new_tab'] ?? false;
                            $buttonColor = $image['button_color'] ?? '#0d6efd';
                            $buttonTextColor = $image['button_text_color'] ?? '#ffffff';
                            $buttonBorderColor = $image['button_border_color'] ?? '#0d6efd';
                            $buttonOpacity = $image['button_opacity'] ?? 100;
                            $buttonHoverBgColor = $image['button_hover_bg_color'] ?? '#0b5ed7';
                            $buttonHoverTextColor = $image['button_hover_text_color'] ?? '#ffffff';
                            $buttonHoverBorderColor = $image['button_hover_border_color'] ?? '#0a58ca';
                            
                            // 정렬 스타일 생성
                            $justifyContent = 'center';
                            $alignItems = 'center';
                            if ($alignH === 'left') $justifyContent = 'flex-start';
                            if ($alignH === 'right') $justifyContent = 'flex-end';
                            if ($alignV === 'top') $alignItems = 'flex-start';
                            if ($alignV === 'bottom') $alignItems = 'flex-end';
                            
                            // 버튼 스타일 생성
                            $buttonStyle = '';
                            $buttonHoverStyle = '';
                            if ($hasButton && $buttonText) {
                                $rgbaColor = $buttonColor;
                                if (strlen($buttonColor) === 7) {
                                    $hex = str_replace('#', '', $buttonColor);
                                    $r = hexdec(substr($hex, 0, 2));
                                    $g = hexdec(substr($hex, 2, 2));
                                    $b = hexdec(substr($hex, 4, 2));
                                    $alpha = $buttonOpacity / 100;
                                    $rgbaColor = "rgba({$r}, {$g}, {$b}, {$alpha})";
                                }
                                $buttonStyle = "background-color: {$rgbaColor}; color: {$buttonTextColor}; border-color: {$buttonBorderColor};";
                                $buttonHoverStyle = "background-color: {$buttonHoverBgColor}; color: {$buttonHoverTextColor}; border-color: {$buttonHoverBorderColor};";
                            }
                        @endphp
                        @if($imageUrl)
                            <div class="image-slide-item image-slide-item-clone" style="width: 100%; flex-shrink: 0; position: relative; {{ in_array($slideDirection, ['up', 'down']) ? 'height: 100%;' : '' }}">
                                <img src="{{ $imageUrl }}" alt="이미지 {{ $index + 1 }}" style="width: 100%; height: auto; display: block;">
                                @if($textOverlay && ($title || $content))
                                    <div class="image-slide-text-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; justify-content: {{ $justifyContent }}; align-items: {{ $alignItems }}; padding: {{ $textPaddingTop }}px {{ $textPaddingRight }}px {{ $textPaddingBottom }}px {{ $textPaddingLeft }}px; pointer-events: none; z-index: 2;">
                                        <div style="pointer-events: auto; text-align: {{ $alignH === 'center' ? 'center' : ($alignH === 'right' ? 'right' : 'left') }};">
                                            @if($title)
                                                <h3 style="color: {{ $textColor }}; font-size: {{ $responsiveTitleFontSize }}; margin: 0{{ $content ? ' 0 ' . $titleContentGap . 'px 0' : '' }};">{{ $title }}</h3>
                                            @endif
                                            @if($content)
                                                <p style="color: {{ $textColor }}; font-size: {{ $responsiveContentFontSize }}; margin: 0;">{{ $content }}</p>
                                            @endif
                                            @if($hasButton && $buttonText)
                                                <a href="{{ $buttonLink }}" 
                                                   @if($buttonNewTab) target="_blank" rel="noopener noreferrer" @endif
                                                   class="image-slide-button"
                                                   style="{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 15px; transition: all 0.3s ease;"
                                                   onmouseover="this.style.cssText += '{{ $buttonHoverStyle }}'"
                                                   onmouseout="this.style.cssText = '{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 15px; transition: all 0.3s ease;'">
                                                    {{ $buttonText }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- 텍스트 오버레이가 있고 버튼이 없지만 링크가 있는 경우 --}}
                                    @if($link && !$hasButton)
                                        <a href="{{ $link }}" 
                                           @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                           style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: block; z-index: 1;">
                                        </a>
                                    @endif
                                @elseif($hasButton && $buttonText)
                                    <div class="image-slide-text-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; justify-content: {{ $justifyContent }}; align-items: {{ $alignItems }}; padding: {{ $textPaddingTop }}px {{ $textPaddingRight }}px {{ $textPaddingBottom }}px {{ $textPaddingLeft }}px; pointer-events: none; z-index: 2;">
                                        <div style="pointer-events: auto;">
                                            <a href="{{ $buttonLink }}" 
                                               @if($buttonNewTab) target="_blank" rel="noopener noreferrer" @endif
                                               class="image-slide-button"
                                               style="{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;"
                                               onmouseover="this.style.cssText += '{{ $buttonHoverStyle }}'"
                                               onmouseout="this.style.cssText = '{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;'">
                                                {{ $buttonText }}
                                            </a>
                                        </div>
                                    </div>
                                @elseif($link && !$hasButton)
                                    <a href="{{ $link }}" 
                                       @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                       style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: block; z-index: 1;">
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endforeach
                @else
                    {{-- 무한루프 슬라이드: 이미지를 여러 번 복제하여 무한 루프 효과 --}}
                    @for($repeat = 0; $repeat < 3; $repeat++)
                        @foreach($images as $index => $image)
                            @php
                                $imageUrl = $image['image_url'] ?? '';
                                $link = $image['link'] ?? '';
                                $openNewTab = $image['open_new_tab'] ?? false;
                                $textOverlay = $image['text_overlay'] ?? false;
                                $title = $image['title'] ?? '';
                                $titleFontSize = $image['title_font_size'] ?? 24;
                                $content = $image['content'] ?? '';
                                $contentFontSize = $image['content_font_size'] ?? 16;
                                // 반응형 폰트 사이즈 계산 - clamp(최소, 선호, 최대)
                                $responsiveTitleFontSize = "clamp(" . round($titleFontSize * 0.65) . "px, " . round($titleFontSize / 8, 1) . "vw, " . $titleFontSize . "px)";
                                $responsiveContentFontSize = "clamp(" . round($contentFontSize * 0.65) . "px, " . round($contentFontSize / 8, 1) . "vw, " . $contentFontSize . "px)";
                                $titleContentGap = $image['title_content_gap'] ?? 10;
                                $alignH = $image['align_h'] ?? 'left';
                                $alignV = $image['align_v'] ?? 'middle';
                                $textColor = $image['text_color'] ?? '#ffffff';
                                $hasButton = $image['has_button'] ?? false;
                                $buttonText = $image['button_text'] ?? '';
                                $buttonLink = $image['button_link'] ?? '';
                                $buttonNewTab = $image['button_new_tab'] ?? false;
                                $buttonColor = $image['button_color'] ?? '#0d6efd';
                                $buttonTextColor = $image['button_text_color'] ?? '#ffffff';
                                $buttonBorderColor = $image['button_border_color'] ?? '#0d6efd';
                                $buttonOpacity = $image['button_opacity'] ?? 100;
                                $buttonHoverBgColor = $image['button_hover_bg_color'] ?? '#0b5ed7';
                                $buttonHoverTextColor = $image['button_hover_text_color'] ?? '#ffffff';
                                $buttonHoverBorderColor = $image['button_hover_border_color'] ?? '#0a58ca';
                                
                                // 정렬 스타일 생성
                                $justifyContent = 'center';
                                $alignItems = 'center';
                                if ($alignH === 'left') $justifyContent = 'flex-start';
                                if ($alignH === 'right') $justifyContent = 'flex-end';
                                if ($alignV === 'top') $alignItems = 'flex-start';
                                if ($alignV === 'bottom') $alignItems = 'flex-end';
                                
                                // 버튼 스타일 생성
                                $buttonStyle = '';
                                $buttonHoverStyle = '';
                                if ($hasButton && $buttonText) {
                                    $rgbaColor = $buttonColor;
                                    if (strlen($buttonColor) === 7) {
                                        $hex = str_replace('#', '', $buttonColor);
                                        $r = hexdec(substr($hex, 0, 2));
                                        $g = hexdec(substr($hex, 2, 2));
                                        $b = hexdec(substr($hex, 4, 2));
                                        $alpha = $buttonOpacity / 100;
                                        $rgbaColor = "rgba({$r}, {$g}, {$b}, {$alpha})";
                                    }
                                    $buttonStyle = "background-color: {$rgbaColor}; color: {$buttonTextColor}; border-color: {$buttonBorderColor};";
                                    $buttonHoverStyle = "background-color: {$buttonHoverBgColor}; color: {$buttonHoverTextColor}; border-color: {$buttonHoverBorderColor};";
                                }
                            @endphp
                            @if($imageUrl)
                                <div class="image-slide-item" style="width: calc((100% - {{ $imageGap * ($visibleCount - 1) }}px) / {{ $visibleCount }}); flex-shrink: 0; position: relative;{{ $imageGap > 0 ? ' margin-right: ' . $imageGap . 'px;' : '' }}">
                                    <img src="{{ $imageUrl }}" alt="이미지 {{ $index + 1 }}" style="width: 100%; height: auto; display: block;">
                                    @if($textOverlay && ($title || $content))
                                        <div class="image-slide-text-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; justify-content: {{ $justifyContent }}; align-items: {{ $alignItems }}; padding: 20px; pointer-events: none; z-index: 2;">
                                            <div style="pointer-events: auto; text-align: {{ $alignH === 'center' ? 'center' : ($alignH === 'right' ? 'right' : 'left') }};">
                                                @if($title)
                                                    <h3 style="color: {{ $textColor }}; font-size: {{ $responsiveTitleFontSize }}; margin: 0{{ $content ? ' 0 ' . $titleContentGap . 'px 0' : '' }};">{{ $title }}</h3>
                                                @endif
                                                @if($content)
                                                    <p style="color: {{ $textColor }}; font-size: {{ $responsiveContentFontSize }}; margin: 0;">{{ $content }}</p>
                                                @endif
                                                @if($hasButton && $buttonText)
                                                    <a href="{{ $buttonLink }}" 
                                                       @if($buttonNewTab) target="_blank" rel="noopener noreferrer" @endif
                                                       class="image-slide-button"
                                                       style="{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 15px; transition: all 0.3s ease;"
                                                       onmouseover="this.style.cssText += '{{ $buttonHoverStyle }}'"
                                                       onmouseout="this.style.cssText = '{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 15px; transition: all 0.3s ease;'">
                                                        {{ $buttonText }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- 텍스트 오버레이가 있고 버튼이 없지만 링크가 있는 경우 --}}
                                        @if($link && !$hasButton)
                                            <a href="{{ $link }}" 
                                               @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                               style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: block; z-index: 1;">
                                            </a>
                                        @endif
                                    @elseif($hasButton && $buttonText)
                                        <div class="image-slide-text-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; justify-content: {{ $justifyContent }}; align-items: {{ $alignItems }}; padding: 20px; pointer-events: none;">
                                            <div style="pointer-events: auto;">
                                                <a href="{{ $buttonLink }}" 
                                                   @if($buttonNewTab) target="_blank" rel="noopener noreferrer" @endif
                                                   class="image-slide-button"
                                                   style="{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;"
                                                   onmouseover="this.style.cssText += '{{ $buttonHoverStyle }}'"
                                                   onmouseout="this.style.cssText = '{{ $buttonStyle }} padding: 10px 20px; border: 2px solid; border-radius: 5px; text-decoration: none; display: inline-block; transition: all 0.3s ease;'">
                                                    {{ $buttonText }}
                                                </a>
                                            </div>
                                        </div>
                                    @elseif($link && !$hasButton)
                                        <a href="{{ $link }}" 
                                           @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                           style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: block; z-index: 1;">
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @endfor
                @endif
            </div>
        </div>
        <script>
        (function() {
            const wrapper = document.querySelector('.image-slide-wrapper[data-widget-id="{{ $widget->id }}"]');
            if (!wrapper) return;
            
            const container = wrapper.querySelector('.image-slide-container');
            const direction = wrapper.dataset.direction;
            const mode = wrapper.dataset.mode || 'single';
            const visibleCount = parseInt(wrapper.dataset.visibleCount) || 3;
            const slideSpeed = parseFloat(wrapper.dataset.slideSpeed) || 3.0;
            
            if (mode === 'single') {
                // 1단 슬라이드 모드
                const items = wrapper.querySelectorAll('.image-slide-item:not(.image-slide-item-clone)');
                const totalItems = items.length;
                
                if (totalItems <= 1) return;
                
                let currentIndex = 0;
                let isTransitioning = false;
                
                // 초기 위치 설정
                function updatePosition(withoutTransition = false) {
                    if (withoutTransition) {
                        container.style.transition = 'none';
                    } else {
                        container.style.transition = 'transform 0.5s ease-in-out';
                    }
                    
                    if (direction === 'left' || direction === 'right') {
                        container.style.transform = `translateX(-${currentIndex * 100}%)`;
                    } else if (direction === 'up' || direction === 'down') {
                        container.style.flexDirection = direction === 'up' ? 'column-reverse' : 'column';
                        container.style.transform = `translateY(-${currentIndex * 100}%)`;
                    }
                }
                
                // 슬라이드 전환
                function nextSlide() {
                    if (isTransitioning) return;
                    
                    isTransitioning = true;
                    currentIndex++;
                    updatePosition();
                    
                    // 마지막 원본 이미지에 도달하면 (복제된 첫 번째 이미지 위치)
                    if (currentIndex >= totalItems) {
                        setTimeout(() => {
                            currentIndex = 0;
                            updatePosition(true);
                            setTimeout(() => {
                                isTransitioning = false;
                            }, 50);
                        }, 500);
                    } else {
                        setTimeout(() => {
                            isTransitioning = false;
                        }, 500);
                    }
                }
                
                // 초기 위치 설정
                updatePosition();
                
                // 설정된 속도(초)마다 슬라이드 전환
                const slideInterval = slideSpeed * 1000; // 초를 밀리초로 변환
                setInterval(nextSlide, slideInterval);
            } else {
                // 무한루프 슬라이드 모드
                const items = wrapper.querySelectorAll('.image-slide-item');
                const totalItems = items.length;
                const imageGap = parseInt(wrapper.dataset.imageGap) || 0;
                const visibleCountMobile = parseInt(wrapper.dataset.visibleCountMobile) || 2;
                
                // 화면 크기에 따라 표시 수량 결정
                function getVisibleCount() {
                    return window.innerWidth < 768 ? visibleCountMobile : visibleCount;
                }
                
                // 현재 표시 수량에 따라 아이템 너비 업데이트
                function updateItemWidths() {
                    const currentVisibleCount = getVisibleCount();
                    items.forEach(item => {
                        const gapTotal = imageGap * (currentVisibleCount - 1);
                        item.style.width = `calc((100% - ${gapTotal}px) / ${currentVisibleCount})`;
                    });
                }
                
                // 초기 너비 설정
                updateItemWidths();
                
                // 화면 크기 변경 시 너비 업데이트
                let resizeTimeout;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(() => {
                        updateItemWidths();
                        // 애니메이션 재시작
                        startAnimation();
                    }, 250);
                });
                
                if (totalItems <= visibleCount) return;
                
                // transition 제거 (부드러운 애니메이션을 위해)
                container.style.transition = 'none';
                
                // 이미지 로드 대기 후 애니메이션 시작
                function startAnimation() {
                    const currentVisibleCount = getVisibleCount();
                    if (totalItems <= currentVisibleCount) return;
                    
                    // 첫 번째 아이템의 실제 너비 계산
                    const firstItem = items[0];
                    if (!firstItem) return;
                    
                    const itemWidth = firstItem.offsetWidth;
                    if (itemWidth === 0) {
                        // 이미지가 아직 로드되지 않았으면 다시 시도
                        setTimeout(startAnimation, 100);
                        return;
                    }
                    
                    const itemWithGap = itemWidth + imageGap;
                    const singleSetWidth = (totalItems / 3) * itemWithGap;
                    
                    // 오른쪽 방향일 때는 -singleSetWidth에서 시작하여 0으로 이동
                    let position = direction === 'right' ? -singleSetWidth : 0;
                    const speed = 0.5; // 픽셀 단위 이동 속도
                    let lastTime = performance.now();
                    
                    function animate(currentTime) {
                        const deltaTime = currentTime - lastTime;
                        lastTime = currentTime;
                        
                        // 프레임 레이트에 관계없이 일정한 속도 유지
                        const frameSpeed = speed * (deltaTime / 16.67); // 60fps 기준
                        
                        if (direction === 'left') {
                            position -= frameSpeed;
                            // 첫 번째 세트가 완전히 사라지면 위치 리셋 (부드럽게)
                            if (Math.abs(position) >= singleSetWidth) {
                                position = position + singleSetWidth;
                            }
                            container.style.transform = `translateX(${position}px)`;
                        } else if (direction === 'right') {
                            position += frameSpeed;
                            // 0에 도달하면 다시 -singleSetWidth로 리셋 (부드럽게)
                            if (position >= 0) {
                                position = position - singleSetWidth;
                            }
                            container.style.transform = `translateX(${position}px)`;
                        }
                        requestAnimationFrame(animate);
                    }
                    
                    // 초기 위치 설정
                    requestAnimationFrame(animate);
                }
                
                // 이미지 로드 확인
                const images = wrapper.querySelectorAll('img');
                let loadedCount = 0;
                const totalImages = images.length;
                
                if (totalImages === 0) {
                    startAnimation();
                } else {
                    images.forEach(img => {
                        if (img.complete) {
                            loadedCount++;
                            if (loadedCount === totalImages) {
                                startAnimation();
                            }
                        } else {
                            img.addEventListener('load', () => {
                                loadedCount++;
                                if (loadedCount === totalImages) {
                                    startAnimation();
                                }
                            });
                        }
                    });
                    
                    // 타임아웃 설정 (이미지 로드 실패 시에도 애니메이션 시작)
                    setTimeout(() => {
                        if (loadedCount < totalImages) {
                            startAnimation();
                        }
                    }, 2000);
                }
            }
        })();
        </script>
    @endif
@elseif($widget->type === 'countdown')
    @php
        $countdownSettings = $widgetSettings;
        $countdownTitle = $countdownSettings['countdown_title'] ?? '';
        $countdownContent = $countdownSettings['countdown_content'] ?? '';
        $countdownType = $countdownSettings['countdown_type'] ?? 'dday';
        
        // 라운드 스타일 설정 - 명시적으로 border-radius 적용
        $countdownBorderRadius = $isOriginalRoundTheme ? 'border-radius: 0.5rem !important; overflow: hidden;' : 'border-radius: 0 !important;';
        $countdownStyle = 'display: flex; flex-direction: column; flex: 1; justify-content: center; padding: 1.5rem; margin-top: 0 !important; margin-bottom: 0 !important; ' . $countdownBorderRadius;
        
        // 컨테이너 정렬에 따라 justify-content 설정
        if ($verticalAlign === 'top') {
            $countdownStyle = str_replace('justify-content: center;', 'justify-content: flex-start;', $countdownStyle);
        } elseif ($verticalAlign === 'bottom') {
            $countdownStyle = str_replace('justify-content: center;', 'justify-content: flex-end;', $countdownStyle);
        }
        
        // 배경 스타일 처리
        $countdownBackgroundType = $countdownSettings['background_type'] ?? 'none';
        $countdownBgStyle = '';
        if ($countdownBackgroundType === 'color') {
            $countdownBgColor = $countdownSettings['background_color'] ?? '#007bff';
            $countdownBgOpacity = isset($countdownSettings['background_opacity']) ? $countdownSettings['background_opacity'] : 100;
            if ($countdownBgOpacity < 100) {
                $countdownBgStyle = 'background-color: ' . hexToRgbaButton($countdownBgColor, $countdownBgOpacity / 100) . ';';
            } else {
                $countdownBgStyle = 'background-color: ' . $countdownBgColor . ';';
            }
        } elseif ($countdownBackgroundType === 'gradient') {
            $gradientStart = $countdownSettings['background_gradient_start'] ?? '#ffffff';
            $gradientEnd = $countdownSettings['background_gradient_end'] ?? '#000000';
            $gradientAngle = $countdownSettings['background_gradient_angle'] ?? 90;
            $gradientOpacity = isset($countdownSettings['background_gradient_opacity']) ? $countdownSettings['background_gradient_opacity'] : 100;
            if ($gradientOpacity < 100) {
                $gradientStartRgba = hexToRgbaButton($gradientStart, $gradientOpacity / 100);
                $gradientEndRgba = hexToRgbaButton($gradientEnd, $gradientOpacity / 100);
                $countdownBgStyle = 'background: linear-gradient(' . $gradientAngle . 'deg, ' . $gradientStartRgba . ', ' . $gradientEndRgba . ');';
            } else {
                $countdownBgStyle = 'background: linear-gradient(' . $gradientAngle . 'deg, ' . $gradientStart . ', ' . $gradientEnd . ');';
            }
        } else {
            // 배경 없음일 때 - 투명 배경
            $countdownBgStyle = 'background: transparent; border: none;';
        }
        
        // 폰트 색상 처리
        $countdownFontColor = $countdownSettings['font_color'] ?? ($isDark ? '#ffffff' : '#333333');
        
        // 배경 없음일 때 그림자 제거 (블록 위젯과 동일하게)
        $countdownShadowClass = ($countdownBackgroundType === 'none') ? '' : ($widgetShadow ? 'shadow-sm' : '');
    @endphp
    <div class="card {{ $countdownShadowClass }} {{ $animationClass }} mb-0" style="{{ $countdownStyle }} {{ $countdownBgStyle }} {{ $animationStyle }}" data-widget-id="{{ $widget->id }}">
        <div class="countdown-widget text-center" style="flex: 1; display: flex; flex-direction: column; justify-content: center; color: {{ $countdownFontColor }};">
            @if($countdownTitle)
                <h4 class="mb-3" style="color: {{ $countdownFontColor }};">{{ $countdownTitle }}</h4>
            @endif
            @if($countdownContent)
                <p class="mb-3" style="color: {{ $countdownFontColor }};">{{ $countdownContent }}</p>
            @endif
            
            @if($countdownType === 'dday')
                @php
                    $targetDate = $countdownSettings['countdown_target_date'] ?? '';
                    $ddayAnimationEnabled = $countdownSettings['countdown_dday_animation_enabled'] ?? false;
                @endphp
                @if($targetDate)
                    <div class="countdown-dday" data-target-date="{{ $targetDate }}" data-animation-enabled="{{ $ddayAnimationEnabled ? 'true' : 'false' }}">
                        <div class="countdown-display">
                            <span class="countdown-text">계산 중...</span>
                        </div>
                    </div>
                @else
                    <p class="text-muted">목표 날짜가 설정되지 않았습니다.</p>
                @endif
            @elseif($countdownType === 'number')
                @php
                    $numberItems = $countdownSettings['countdown_number_items'] ?? [];
                    $animationEnabled = $countdownSettings['countdown_animation_enabled'] 
                        ?? $countdownSettings['countdown_animation'] 
                        ?? false;
                @endphp
                @if(count($numberItems) > 0)
                    <div class="countdown-number-items row g-3">
                        @foreach($numberItems as $index => $item)
                            <div class="col-md-{{ 12 / min(count($numberItems), 3) }} countdown-number-item" 
                                 data-item-name="{{ $item['name'] ?? '' }}"
                                 data-item-number="{{ $item['number'] ?? 0 }}"
                                 data-item-unit="{{ $item['unit'] ?? '' }}"
                                 data-animation="{{ $animationEnabled ? 'true' : 'false' }}">
                                <div class="countdown-number-name mb-2">{{ $item['name'] ?? '' }}</div>
                                <div class="countdown-number-value">
                                    <span class="countdown-number-display" style="font-size: 2.5rem; font-weight: bold;">{{ $animationEnabled ? '0' : ($item['number'] ?? 0) }}</span>
                                    <span class="countdown-number-unit" style="font-size: 1.2rem; margin-left: 0.5rem;">{{ $item['unit'] ?? '' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">숫자 카운트 항목이 없습니다.</p>
                @endif
            @endif
        </div>
    </div>
@else
@php
    // 제목이 있을 때만 상단 테두리 적용
    $hasTitle = $widget->type !== 'user_ranking' && $widget->type !== 'marquee_board' && $widget->type !== 'block' && $widget->type !== 'block_slide' && $widget->type !== 'image' && $widget->type !== 'image_slide' && $widget->type !== 'tab_menu' && $widget->type !== 'toggle_menu' && $widget->type !== 'chat' && $widget->type !== 'chat_widget' && $widget->type !== 'create_site' && $widget->type !== 'countdown' && empty($widget->title) === false;
    $cardStyle = $hasTitle ? $widgetTopBorderStyle : '';
    if (!$isRoundTheme) {
        $cardStyle .= ' border-radius: 0 !important; border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;';
    }
    // 가로 100%일 때 좌우 보더 레디우스 제거 (칸 고정너비일 때는 유지)
    if ($isActualFullWidth) {
        $cardStyle .= ($cardStyle ? ' ' : '') . 'border-radius: 0 !important; border-top-left-radius: 0 !important; border-top-right-radius: 0 !important; border-bottom-left-radius: 0 !important; border-bottom-right-radius: 0 !important;';
    }
    // 모든 위젯이 상하 영역을 꽉 차게 하기 위해 flex 적용
    $cardStyle .= ($cardStyle ? ' ' : '') . 'flex: 1; display: flex; flex-direction: column; min-height: 0; height: 100%; margin-top: 0 !important; margin-bottom: 0 !important;';
    $cardMarginBottom = 'mb-0';
@endphp
<div class="card {{ $shadowClass }} {{ $animationClass }} {{ $cardMarginBottom }} {{ $isRoundTheme ? '' : 'rounded-0' }} {{ ($widget->type === 'chat' || $widget->type === 'chat_widget') ? 'd-none d-md-block' : '' }} {{ $boardViewerNoBackgroundEarly ? 'bg-transparent border-0' : '' }}" style="{{ $cardStyle }} {{ $animationStyle }}" data-widget-id="{{ $widget->id }}">
    @if($hasTitle)
        @php
            $cardHeaderBg = $isDark ? 'rgb(43, 43, 43)' : 'white';
            $cardHeaderBorder = $isDark ? 'rgba(255, 255, 255, 0.1)' : '#dee2e6';
            $cardHeaderTextColor = $isDark ? '#ffffff' : 'inherit';
            
            // 게시판 위젯의 경우 더보기 링크를 위한 게시판 URL 가져오기
            $boardMoreUrl = null;
            if ($widget->type === 'board' && !empty($widgetSettings['board_id'])) {
                $boardForMore = \App\Models\Board::find($widgetSettings['board_id']);
                if ($boardForMore && !empty($boardForMore->slug)) {
                    $boardMoreUrl = route('boards.show', ['site' => $site->slug, 'board' => $boardForMore->slug]);
                }
            }
        @endphp
        @if($widget->type === 'gallery')
            @if(!empty($widget->title))
            <div class="card-header" style="background-color: {{ $cardHeaderBg }}; color: {{ $cardHeaderTextColor }}; padding-top: 1rem !important;{{ $isRoundTheme ? ' border-top-left-radius: 0.5rem !important; border-top-right-radius: 0.5rem !important;' : ' border-radius: 0 !important; border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;' }} border-bottom-left-radius: 0 !important; border-bottom-right-radius: 0 !important; border: none !important; border-bottom: 1px solid {{ $cardHeaderBorder }} !important;">
                <h6 class="mb-0" style="color: {{ $cardHeaderTextColor }};">{{ $widget->title }}</h6>
            </div>
            @endif
        @else
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: {{ $cardHeaderBg }}; color: {{ $cardHeaderTextColor }}; padding-top: 1rem !important; {{ $widgetTopBorderStyle }}{{ $isRoundTheme ? ' border-top-left-radius: 0.5rem !important; border-top-right-radius: 0.5rem !important;' : ' border-radius: 0 !important; border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;' }} border-bottom-left-radius: 0 !important; border-bottom-right-radius: 0 !important; border: none !important; border-bottom: 1px solid {{ $cardHeaderBorder }} !important;">
            <h6 class="mb-0" style="color: {{ $cardHeaderTextColor }};">{{ $widget->title }}</h6>
            @if($boardMoreUrl)
                <a href="{{ $boardMoreUrl }}" class="btn btn-sm btn-outline-secondary" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">더보기 <i class="bi bi-chevron-right"></i></a>
            @endif
        </div>
        @endif
    @endif
    @php
        $cardBodyStyle = ($widget->type === 'tab_menu' || $widget->type === 'user_ranking' || $widget->type === 'toggle_menu') ? 'padding-top: 0 !important;' : '';
        if ($isFullHeight || $widget->type === 'map') {
            // 세로 100% 또는 지도 위젯일 때는 항상 flex 적용
            $cardBodyStyle .= ($cardBodyStyle ? ' ' : '') . 'flex: 1; display: flex; flex-direction: column; min-height: 0;';
        }
        // 라운드 테마일 때 card-body에 하단 라운드 추가 (card-header가 없는 경우)
        if ($isRoundTheme && ($widget->type === 'user_ranking' || $widget->type === 'marquee_board' || $widget->type === 'block' || $widget->type === 'block_slide' || $widget->type === 'image' || $widget->type === 'image_slide' || $widget->type === 'tab_menu' || $widget->type === 'toggle_menu' || $widget->type === 'chat' || $widget->type === 'chat_widget' || $widget->type === 'create_site' || $widget->type === 'countdown' || ($widget->type === 'gallery' && empty($widget->title)))) {
            $cardBodyStyle .= ($cardBodyStyle ? ' ' : '') . 'border-bottom-left-radius: 0.5rem !important; border-bottom-right-radius: 0.5rem !important;';
        }
    @endphp
    <div class="card-body" style="{{ $cardBodyStyle }}">
        @switch($widget->type)
            @case('popular_posts')
                {{-- 인기 게시글 위젯 --}}
                @php
                    $bestPostCriteria = $site->getSetting('best_post_criteria', 'views');
                    // 24시간 기준 인기글
                    $popularPosts = \App\Models\Post::where('site_id', $site->id)
                        ->with(['user', 'board'])
                        ->where('created_at', '>=', now()->subDay()) // 24시간 이내
                        ->where(function($q) {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                $q->where('is_secret', '=', 0)
                                  ->orWhereNull('is_secret');
                            }
                        })
                        ->when($bestPostCriteria === 'likes', function($query) {
                            if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                                $query->withCount(['likes as likes_count' => function($q) {
                                    $q->where('type', 'like');
                                }])
                                ->orderBy('likes_count', 'desc');
                            } else {
                                $query->orderBy('created_at', 'desc');
                            }
                        })
                        ->when($bestPostCriteria === 'comments', function($query) {
                            $query->withCount('comments')
                                  ->orderBy('comments_count', 'desc');
                        })
                        ->when($bestPostCriteria === 'views', function($query) {
                            $query->orderBy('views', 'desc');
                        })
                        ->orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->get();
                @endphp
                @if($popularPosts->isEmpty())
                    <p class="text-muted mb-0">인기 게시글이 없습니다.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($popularPosts as $post)
                            <li class="mb-2 pb-2 border-bottom">
                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug ?? 'default', 'post' => $post->id]) }}" 
                                   class="text-decoration-none d-block" 
                                   style="color: {{ $widgetTextColor }};">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.9rem; color: {{ $widgetTextColor }};">
                                        {{ $post->title }}
                                    </div>
                                    <small style="color: {{ $widgetMutedColor }}"
                                        {{ $post->board->name ?? '게시판' }} · 
                                        {{ $post->user->nickname ?? $post->user->name ?? '익명' }} · 
                                        {{ $post->created_at->diffForHumans() }}
                                    </small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @break

            @case('recent_posts')
                {{-- 최근 게시글 위젯 --}}
                @php
                    // 24시간 기준 최신글
                    $recentPosts = \App\Models\Post::where('site_id', $site->id)
                        ->with(['user', 'board'])
                        ->where('created_at', '>=', now()->subDay()) // 24시간 이내
                        ->whereHas('board', function($boardQuery) {
                            $boardQuery->where(function($bq) {
                                $bq->where('force_secret', false)
                                   ->orWhereNull('force_secret');
                            });
                        })
                        ->where(function($q) {
                            $q->whereHas('board', function($boardQuery) {
                                $boardQuery->where(function($bq) {
                                    $bq->where('enable_secret', false)
                                       ->orWhereNull('enable_secret');
                                });
                            })
                            ->orWhere(function($q2) {
                                if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                    $q2->where('is_secret', false)
                                       ->orWhereNull('is_secret');
                                }
                            });
                        })
                        ->orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->get();
                @endphp
                @if($recentPosts->isEmpty())
                    <p class="text-muted mb-0">최근 게시글이 없습니다.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($recentPosts as $post)
                            <li class="mb-2 pb-2 border-bottom">
                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug ?? 'default', 'post' => $post->id]) }}" 
                                   class="text-decoration-none d-block" 
                                   style="color: {{ $widgetTextColor }};">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.9rem; color: {{ $widgetTextColor }};">
                                        {{ $post->title }}
                                    </div>
                                    <small style="color: {{ $widgetMutedColor }}"
                                        {{ $post->board->name ?? '게시판' }} · 
                                        {{ $post->user->nickname ?? $post->user->name ?? '익명' }} · 
                                        {{ $post->created_at->diffForHumans() }}
                                    </small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @break

            @case('weekly_popular_posts')
                {{-- 주간 인기 게시글 위젯 (7일 기준) --}}
                @php
                    $bestPostCriteria = $site->getSetting('best_post_criteria', 'views');
                    $popularPosts = \App\Models\Post::where('site_id', $site->id)
                        ->with(['user', 'board'])
                        ->where('created_at', '>=', now()->subWeek()) // 7일 이내
                        ->where(function($q) {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                $q->where('is_secret', '=', 0)
                                  ->orWhereNull('is_secret');
                            }
                        })
                        ->when($bestPostCriteria === 'likes', function($query) {
                            if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                                $query->withCount(['likes as likes_count' => function($q) {
                                    $q->where('type', 'like');
                                }])
                                ->orderBy('likes_count', 'desc');
                            } else {
                                $query->orderBy('created_at', 'desc');
                            }
                        })
                        ->when($bestPostCriteria === 'comments', function($query) {
                            $query->withCount('comments')
                                  ->orderBy('comments_count', 'desc');
                        })
                        ->when($bestPostCriteria === 'views', function($query) {
                            $query->orderBy('views', 'desc');
                        })
                        ->orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->get();
                @endphp
                @if($popularPosts->isEmpty())
                    <p class="text-muted mb-0">주간 인기 게시글이 없습니다.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($popularPosts as $post)
                            <li class="mb-2 pb-2 border-bottom">
                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug ?? 'default', 'post' => $post->id]) }}" 
                                   class="text-decoration-none d-block" 
                                   style="color: {{ $widgetTextColor }};">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.9rem; color: {{ $widgetTextColor }};">
                                        {{ $post->title }}
                                    </div>
                                    <small style="color: {{ $widgetMutedColor }}"
                                        {{ $post->board->name ?? '게시판' }} · 
                                        {{ $post->user->nickname ?? $post->user->name ?? '익명' }} · 
                                        {{ $post->created_at->diffForHumans() }}
                                    </small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @break

            @case('monthly_popular_posts')
                {{-- 월간 인기 게시글 위젯 (30일 기준) --}}
                @php
                    $bestPostCriteria = $site->getSetting('best_post_criteria', 'views');
                    $popularPosts = \App\Models\Post::where('site_id', $site->id)
                        ->with(['user', 'board'])
                        ->where('created_at', '>=', now()->subDays(30)) // 30일 이내
                        ->where(function($q) {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                $q->where('is_secret', '=', 0)
                                  ->orWhereNull('is_secret');
                            }
                        })
                        ->when($bestPostCriteria === 'likes', function($query) {
                            if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                                $query->withCount(['likes as likes_count' => function($q) {
                                    $q->where('type', 'like');
                                }])
                                ->orderBy('likes_count', 'desc');
                            } else {
                                $query->orderBy('created_at', 'desc');
                            }
                        })
                        ->when($bestPostCriteria === 'comments', function($query) {
                            $query->withCount('comments')
                                  ->orderBy('comments_count', 'desc');
                        })
                        ->when($bestPostCriteria === 'views', function($query) {
                            $query->orderBy('views', 'desc');
                        })
                        ->orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->get();
                @endphp
                @if($popularPosts->isEmpty())
                    <p class="text-muted mb-0">월간 인기 게시글이 없습니다.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($popularPosts as $post)
                            <li class="mb-2 pb-2 border-bottom">
                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug ?? 'default', 'post' => $post->id]) }}" 
                                   class="text-decoration-none d-block" 
                                   style="color: {{ $widgetTextColor }};">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.9rem; color: {{ $widgetTextColor }};">
                                        {{ $post->title }}
                                    </div>
                                    <small style="color: {{ $widgetMutedColor }}"
                                        {{ $post->board->name ?? '게시판' }} · 
                                        {{ $post->user->nickname ?? $post->user->name ?? '익명' }} · 
                                        {{ $post->created_at->diffForHumans() }}
                                    </small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @break

            @case('board')
                {{-- 게시판 위젯 --}}
                @php
                    $boardId = $widgetSettings['board_id'] ?? null;
                    $sortOrder = $widgetSettings['sort_order'] ?? 'latest'; // latest, oldest, random, popular
                    $boardPosts = collect();
                    if ($boardId) {
                        $board = \App\Models\Board::find($boardId);
                        if ($board) {
                            $query = \App\Models\Post::where('site_id', $site->id)
                                ->where('board_id', $boardId)
                                ->with(['user', 'board'])
                                ->where(function($q) {
                                    if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                        $q->where('is_secret', '=', 0)
                                          ->orWhereNull('is_secret');
                                    }
                                });
                            
                            // 표시 방식에 따라 정렬
                            if ($sortOrder === 'latest') {
                                $query->orderBy('created_at', 'desc');
                            } elseif ($sortOrder === 'oldest') {
                                $query->orderBy('created_at', 'asc');
                            } elseif ($sortOrder === 'random') {
                                $query->inRandomOrder();
                            } elseif ($sortOrder === 'popular') {
                                // 인기순: 사이트 설정의 베스트글 기준 사용
                                $bestPostCriteria = $site->getSetting('best_post_criteria', 'views');
                                if ($bestPostCriteria === 'likes') {
                                    if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                                        $query->withCount(['likes as likes_count' => function($q) {
                                            $q->where('type', 'like');
                                        }])
                                        ->orderBy('likes_count', 'desc');
                                    } else {
                                        $query->orderBy('created_at', 'desc');
                                    }
                                } elseif ($bestPostCriteria === 'comments') {
                                    $query->withCount('comments')
                                          ->orderBy('comments_count', 'desc');
                                } else {
                                    // views (기본값)
                                    $query->orderBy('views', 'desc');
                                }
                                $query->orderBy('created_at', 'desc'); // 동일한 값일 경우 최신순
                            }
                            
                            $boardPosts = $query->limit($limit)->get();
                        }
                    }
                @endphp
                @if($boardPosts->isEmpty())
                    <p class="text-muted mb-0">게시글이 없습니다.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($boardPosts as $post)
                            @php
                                // 질의응답 게시판인 경우 상태 배지 정보 가져오기
                                $isQaBoard = $board && $board->type === 'qa';
                                $qaStatuses = [];
                                $currentStatus = null;
                                $statusColor = '#6c757d';
                                
                                if ($isQaBoard) {
                                    $qaStatuses = is_array($board->qa_statuses) ? $board->qa_statuses : (is_string($board->qa_statuses) ? json_decode($board->qa_statuses, true) : []);
                                    if (empty($qaStatuses) || !is_array($qaStatuses)) {
                                        $qaStatuses = [
                                            ['name' => '답변대기', 'color' => '#ffc107'],
                                            ['name' => '답변완료', 'color' => '#28a745']
                                        ];
                                    }
                                    
                                    $currentStatus = $post->qa_status ?? null;
                                    if (empty($currentStatus) && !empty($qaStatuses) && isset($qaStatuses[0]['name'])) {
                                        $currentStatus = $qaStatuses[0]['name'];
                                    }
                                    
                                    if ($currentStatus && !empty($qaStatuses)) {
                                        $statusInfo = collect($qaStatuses)->firstWhere('name', $currentStatus);
                                        if ($statusInfo) {
                                            $statusColor = $statusInfo['color'] ?? '#6c757d';
                                        } else {
                                            $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                        }
                                    } elseif (!empty($qaStatuses) && isset($qaStatuses[0]['color'])) {
                                        $statusColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                    }
                                    
                                    $displayStatus = $currentStatus;
                                    $displayColor = $statusColor;
                                    if (empty($displayStatus) && isset($qaStatuses[0]['name'])) {
                                        $displayStatus = $qaStatuses[0]['name'];
                                        $displayColor = $qaStatuses[0]['color'] ?? '#6c757d';
                                    }
                                }
                            @endphp
                            <li class="mb-2 pb-2 border-bottom">
                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug ?? 'default', 'post' => $post->id]) }}" 
                                   class="text-decoration-none d-block {{ $isQaBoard && !empty($qaStatuses) ? 'd-flex align-items-center justify-content-between' : '' }}" 
                                   style="color: {{ $widgetTextColor }};">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-truncate" style="font-size: 0.9rem; color: {{ $widgetTextColor }};">
                                            {{ $post->title }}
                                        </div>
                                        <small style="color: {{ $widgetMutedColor }}">
                                            {{ $post->user->nickname ?? $post->user->name ?? '익명' }} · 
                                            {{ $post->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    @if($isQaBoard && !empty($qaStatuses) && !empty($displayStatus))
                                        <span class="badge ms-2" style="background-color: {{ $displayColor }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0; white-space: nowrap;">{{ $displayStatus }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @break

            @case('marquee_board')
                {{-- 게시글 전광판 위젯 --}}
                @php
                    $boardId = $widgetSettings['board_id'] ?? null;
                    $sortOrder = $widgetSettings['sort_order'] ?? 'latest';
                    $marqueeDirection = $widgetSettings['direction'] ?? 'left'; // left, right, up, down
                    $marqueePosts = collect();
                    $boardName = '';
                    
                    if ($boardId) {
                        $board = \App\Models\Board::find($boardId);
                        if ($board) {
                            $boardName = $board->name;
                            $query = \App\Models\Post::where('site_id', $site->id)
                                ->where('board_id', $boardId)
                                ->with(['user', 'board'])
                                ->where(function($q) {
                                    if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                        $q->where('is_secret', '=', 0)
                                          ->orWhereNull('is_secret');
                                    }
                                });
                            
                            // 표시 방식에 따라 정렬
                            if ($sortOrder === 'latest') {
                                $query->orderBy('created_at', 'desc');
                            } elseif ($sortOrder === 'oldest') {
                                $query->orderBy('created_at', 'asc');
                            } elseif ($sortOrder === 'random') {
                                $query->inRandomOrder();
                            } elseif ($sortOrder === 'popular') {
                                // 인기순: 사이트 설정의 베스트글 기준 사용
                                $bestPostCriteria = $site->getSetting('best_post_criteria', 'views');
                                if ($bestPostCriteria === 'likes') {
                                    if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                                        $query->withCount(['likes as likes_count' => function($q) {
                                            $q->where('type', 'like');
                                        }])
                                        ->orderBy('likes_count', 'desc');
                                    } else {
                                        $query->orderBy('created_at', 'desc');
                                    }
                                } elseif ($bestPostCriteria === 'comments') {
                                    $query->withCount('comments')
                                          ->orderBy('comments_count', 'desc');
                                } else {
                                    // views (기본값)
                                    $query->orderBy('views', 'desc');
                                }
                                $query->orderBy('created_at', 'desc'); // 동일한 값일 경우 최신순
                            }
                            
                            $marqueePosts = $query->limit($limit)->get();
                        }
                    }
                @endphp
                @if($marqueePosts->isEmpty())
                    <p class="text-muted mb-0">게시글이 없습니다.</p>
                @else
                    @php
                        $isHorizontal = in_array($marqueeDirection, ['left', 'right']);
                        $isVertical = in_array($marqueeDirection, ['up', 'down']);
                        $totalItems = $marqueePosts->count();
                        $itemDuration = 3; // 3초
                        $totalDuration = $totalItems * $itemDuration;
                    @endphp
                    <div class="marquee-container" 
                         style="overflow: hidden; position: relative; width: 100%; height: 30px;">
                        <div class="marquee-content-wrapper" style="position: relative; width: 100%; height: 100%;">
                            @foreach($marqueePosts as $index => $post)
                                <div class="marquee-item marquee-item-{{ $index }}" 
                                     data-direction="{{ $marqueeDirection }}"
                                     style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; opacity: 0; transition: opacity 0.5s ease-in-out;">
                                    <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug ?? 'default', 'post' => $post->id]) }}" 
                                       class="text-decoration-none d-flex align-items-center" 
                                       style="color: {{ $widgetTextColor }}; width: 100%;">
                                        <span class="fw-semibold me-2" style="color: {{ $widgetTextColor }};">{{ $boardName }} | </span>
                                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; color: {{ $widgetTextColor }};">{{ Str::limit($post->title, 50, '...') }}</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <style>
                        @foreach($marqueePosts as $index => $post)
                            @php
                                $startTime = $index * $itemDuration;
                                $endTime = $startTime + $itemDuration;
                                $fadeInEnd = $startTime + 0.5;
                                $fadeOutStart = $endTime - 0.5;
                            @endphp
                            .marquee-item-{{ $index }} {
                                animation: marquee-fade-{{ $index }} {{ $totalDuration }}s infinite;
                            }
                            @keyframes marquee-fade-{{ $index }} {
                                0% {
                                    @if($marqueeDirection === 'left')
                                        transform: translateX(100%);
                                    @elseif($marqueeDirection === 'right')
                                        transform: translateX(-100%);
                                    @elseif($marqueeDirection === 'up')
                                        transform: translateY(100%);
                                    @else
                                        transform: translateY(-100%);
                                    @endif
                                    opacity: 0;
                                }
                                {{ ($startTime / $totalDuration * 100) }}% {
                                    @if($marqueeDirection === 'left')
                                        transform: translateX(100%);
                                    @elseif($marqueeDirection === 'right')
                                        transform: translateX(-100%);
                                    @elseif($marqueeDirection === 'up')
                                        transform: translateY(100%);
                                    @else
                                        transform: translateY(-100%);
                                    @endif
                                    opacity: 0;
                                }
                                {{ ($fadeInEnd / $totalDuration * 100) }}% {
                                    transform: translateX(0) translateY(0);
                                    opacity: 1;
                                }
                                {{ ($fadeOutStart / $totalDuration * 100) }}% {
                                    transform: translateX(0) translateY(0);
                                    opacity: 1;
                                }
                                {{ ($endTime / $totalDuration * 100) }}% {
                                    @if($marqueeDirection === 'left')
                                        transform: translateX(-100%);
                                    @elseif($marqueeDirection === 'right')
                                        transform: translateX(100%);
                                    @elseif($marqueeDirection === 'up')
                                        transform: translateY(-100%);
                                    @else
                                        transform: translateY(100%);
                                    @endif
                                    opacity: 0;
                                }
                                100% {
                                    @if($marqueeDirection === 'left')
                                        transform: translateX(-100%);
                                    @elseif($marqueeDirection === 'right')
                                        transform: translateX(100%);
                                    @elseif($marqueeDirection === 'up')
                                        transform: translateY(-100%);
                                    @else
                                        transform: translateY(100%);
                                    @endif
                                    opacity: 0;
                                }
                            }
                        @endforeach
                        .marquee-container:hover .marquee-item {
                            animation-play-state: paused;
                        }
                    </style>
                @endif
                @break

            @case('gallery')
                {{-- 갤러리 위젯 --}}
                @php
                    $boardId = $widgetSettings['board_id'] ?? null;
                    $displayType = $widgetSettings['display_type'] ?? 'grid';
                    $showTitle = $widgetSettings['show_title'] ?? true;
                    $galleryPosts = collect();
                    $board = null;
                    
                    if ($boardId) {
                        $board = \App\Models\Board::find($boardId);
                        // 핀터레스트 타입인 경우 게시판의 pinterest_show_title 설정을 따름
                        if ($board && $board->type === 'pinterest') {
                            $showTitle = $board->pinterest_show_title ?? false;
                        }
                        if ($board && in_array($board->type, ['photo', 'bookmark', 'blog', 'pinterest'])) {
                            $query = \App\Models\Post::where('site_id', $site->id)
                                ->where('board_id', $boardId)
                                ->with(['user', 'board', 'attachments'])
                                ->where(function($q) {
                                    if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                        $q->where('is_secret', '=', 0)
                                          ->orWhereNull('is_secret');
                                    }
                                })
                                ->orderBy('created_at', 'desc');
                            
                            $limit = $widgetSettings['limit'] ?? 9;
                            if ($displayType === 'grid') {
                                $cols = $widgetSettings['cols'] ?? 3;
                                $rows = $widgetSettings['rows'] ?? 3;
                                $limit = $cols * $rows;
                            }
                            
                            $galleryPosts = $query->limit($limit)->get();
                        }
                    }
                @endphp
                @if($galleryPosts->isEmpty() || !$board)
                    <p class="text-muted mb-0">게시글이 없습니다.</p>
                @else
                    @if($displayType === 'grid')
                        @php
                            $cols = $widgetSettings['cols'] ?? 3;
                            $colClass = 'col-' . (12 / $cols);
                        @endphp
                        <div class="row g-2">
                            @foreach($galleryPosts as $post)
                                @php
                                    $thumbnail = $post->thumbnail_path;
                                    if (!$thumbnail && $post->attachments->count() > 0) {
                                        $imageAttachment = $post->attachments->firstWhere('mime_type', 'like', 'image/%');
                                        if ($imageAttachment) {
                                            $thumbnail = $imageAttachment->file_path;
                                        }
                                    }
                                    if (!$thumbnail) {
                                        preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->content ?? '', $matches);
                                        $thumbnail = $matches[1] ?? null;
                                    }
                                @endphp
                                <div class="{{ $colClass }}">
                                    <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                       class="text-decoration-none d-block">
                                        <div class="position-relative" style="overflow: hidden; background-color: #f8f9fa;">
                                            @if($thumbnail)
                                                @if(str_starts_with($thumbnail, 'http'))
                                                    <img src="{{ $thumbnail }}" 
                                                         alt="{{ $post->title }}" 
                                                         class="w-100" 
                                                         style="width: 100%; height: auto; display: block;">
                                                @else
                                                    <img src="{{ asset('storage/' . $thumbnail) }}" 
                                                         alt="{{ $post->title }}" 
                                                         class="w-100" 
                                                         style="width: 100%; height: auto; display: block;">
                                                @endif
                                            @else
                                                <div class="w-100 d-flex align-items-center justify-content-center" style="min-height: 100px;">
                                                    <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        @if($showTitle)
                                            <div class="mt-1 text-center">
                                                <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">
                                                    {{ Str::limit($post->title, 20, '...') }}
                                                </small>
                                            </div>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        @php
                            $slideCols = $widgetSettings['slide_cols'] ?? 3;
                            $slideDirection = $widgetSettings['slide_direction'] ?? 'left';
                        @endphp
                        <div class="gallery-slider position-relative" id="gallery-slider-{{ $widget->id }}" style="overflow: hidden; position: relative;">
                            <div class="gallery-slide-container" style="overflow: hidden; position: relative; width: 100%;">
                                <div class="gallery-slide-wrapper" 
                                     id="gallery-slide-wrapper-{{ $widget->id }}"
                                     data-direction="{{ $slideDirection }}"
                                     data-cols="{{ $slideCols }}"
                                     data-total-items="{{ $galleryPosts->count() }}"
                                     style="display: flex; 
                                            @if($slideDirection === 'left' || $slideDirection === 'right')
                                                flex-direction: row;
                                            @else
                                                flex-direction: column;
                                            @endif">
                                    {{-- 무한 루프를 위해 아이템을 3번 반복 --}}
                                    @for($repeat = 0; $repeat < 3; $repeat++)
                                    @foreach($galleryPosts as $index => $post)
                                        @php
                                            $thumbnail = $post->thumbnail_path;
                                            if (!$thumbnail && $post->attachments->count() > 0) {
                                                $imageAttachment = $post->attachments->firstWhere('mime_type', 'like', 'image/%');
                                                if ($imageAttachment) {
                                                    $thumbnail = $imageAttachment->file_path;
                                                }
                                            }
                                            if (!$thumbnail) {
                                                preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->content ?? '', $matches);
                                                $thumbnail = $matches[1] ?? null;
                                            }
                                        @endphp
                                        <div class="gallery-slide-item" 
                                             style="flex: 0 0 {{ 100 / $slideCols }}%; max-width: {{ 100 / $slideCols }}%; padding: 0 4px; box-sizing: border-box;">
                                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                               class="text-decoration-none d-block">
                                                <div class="position-relative" style="overflow: hidden; background-color: #f8f9fa;">
                                                    @if($thumbnail)
                                                        @if(str_starts_with($thumbnail, 'http'))
                                                            <img src="{{ $thumbnail }}" 
                                                                 alt="{{ $post->title }}" 
                                                                 class="w-100" 
                                                                 style="width: 100%; height: auto; display: block;">
                                                        @else
                                                            <img src="{{ asset('storage/' . $thumbnail) }}" 
                                                                 alt="{{ $post->title }}" 
                                                                 class="w-100" 
                                                                 style="width: 100%; height: auto; display: block;">
                                                        @endif
                                                    @else
                                                        <div class="w-100 d-flex align-items-center justify-content-center" style="min-height: 100px;">
                                                            <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($showTitle)
                                                    <div class="mt-1 text-center">
                                                        <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">
                                                            {{ Str::limit($post->title, 20, '...') }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </a>
                                        </div>
                                    @endforeach
                                    @endfor
                                </div>
                            </div>
                            <script>
                                (function() {
                                    function initGallerySlide() {
                                        const widgetId = {{ $widget->id }};
                                        const container = document.getElementById('gallery-slider-' + widgetId);
                                        const wrapper = document.getElementById('gallery-slide-wrapper-' + widgetId);
                                        if (!container || !wrapper) {
                                            setTimeout(initGallerySlide, 100);
                                            return;
                                        }
                                        
                                        if (wrapper.dataset.initialized === 'true') return;
                                        wrapper.dataset.initialized = 'true';
                                        
                                        const direction = wrapper.dataset.direction || 'left';
                                        const cols = parseInt(wrapper.dataset.cols) || 3;
                                        const totalItems = parseInt(wrapper.dataset.totalItems) || {{ $galleryPosts->count() }};
                                        const items = wrapper.querySelectorAll('.gallery-slide-item');
                                        
                                        if (totalItems <= cols) return;
                                        
                                        let animationId = null;
                                        let isPaused = false;
                                        
                                        function startAnimation() {
                                            if (animationId) {
                                                cancelAnimationFrame(animationId);
                                            }
                                            
                                            const firstItem = items[0];
                                            if (!firstItem) return;
                                            
                                            // 아이템 너비 (padding 포함)
                                            const itemWidth = firstItem.offsetWidth;
                                            if (itemWidth === 0) {
                                                setTimeout(startAnimation, 100);
                                                return;
                                            }
                                            
                                            // 하나의 세트 전체 너비 (원본 아이템들의 총 너비)
                                            const singleSetWidth = totalItems * itemWidth;
                                            
                                            // 오른쪽 방향일 때는 -singleSetWidth에서 시작하여 0으로 이동
                                            let position = direction === 'right' ? -singleSetWidth : 0;
                                            const speed = 0.5; // 픽셀 단위 이동 속도
                                            let lastTime = performance.now();
                                            
                                            function animate(currentTime) {
                                                if (isPaused) {
                                                    lastTime = currentTime;
                                                    animationId = requestAnimationFrame(animate);
                                                    return;
                                                }
                                                
                                                const deltaTime = currentTime - lastTime;
                                                lastTime = currentTime;
                                                
                                                const frameSpeed = speed * (deltaTime / 16.67);
                                                
                                                if (direction === 'left') {
                                                    position -= frameSpeed;
                                                    if (Math.abs(position) >= singleSetWidth) {
                                                        position = position + singleSetWidth;
                                                    }
                                                } else if (direction === 'right') {
                                                    position += frameSpeed;
                                                    if (position >= 0) {
                                                        position = position - singleSetWidth;
                                                    }
                                                }
                                                
                                                wrapper.style.transform = `translateX(${position}px)`;
                                                animationId = requestAnimationFrame(animate);
                                            }
                                            
                                            animationId = requestAnimationFrame(animate);
                                        }
                                        
                                        // 화면 크기 변경 시 재시작
                                        let resizeTimeout;
                                        window.addEventListener('resize', () => {
                                            clearTimeout(resizeTimeout);
                                            resizeTimeout = setTimeout(startAnimation, 250);
                                        });
                                        
                                        // 호버 시 일시 정지
                                        container.addEventListener('mouseenter', () => {
                                            isPaused = true;
                                        });
                                        container.addEventListener('mouseleave', () => {
                                            isPaused = false;
                                        });
                                        
                                        // 이미지 로드 확인 후 시작
                                        const images = wrapper.querySelectorAll('img');
                                        let loadedCount = 0;
                                        const totalImages = images.length;
                                        
                                        if (totalImages === 0) {
                                            startAnimation();
                                        } else {
                                            images.forEach(img => {
                                                if (img.complete) {
                                                    loadedCount++;
                                                    if (loadedCount === totalImages) {
                                                        startAnimation();
                                                    }
                                                } else {
                                                    img.addEventListener('load', () => {
                                                        loadedCount++;
                                                        if (loadedCount === totalImages) {
                                                            startAnimation();
                                                        }
                                                    });
                                                    img.addEventListener('error', () => {
                                                        loadedCount++;
                                                        if (loadedCount === totalImages) {
                                                            startAnimation();
                                                        }
                                                    });
                                                }
                                            });
                                            
                                            // 3초 후에도 로드되지 않으면 강제 시작
                                            setTimeout(() => {
                                                if (loadedCount < totalImages) {
                                                    startAnimation();
                                                }
                                            }, 3000);
                                        }
                                    }
                                    
                                    if (document.readyState === 'loading') {
                                        document.addEventListener('DOMContentLoaded', initGallerySlide);
                                    } else {
                                        setTimeout(initGallerySlide, 500);
                                    }
                                })();
                            </script>
                        </div>
                    @endif
                @endif
                @break

            @case('board_list')
                {{-- 게시판 목록 위젯 --}}
                @php
                    $boards = \App\Models\Board::where('site_id', $site->id)
                        ->active()
                        ->orderBy('order', 'asc')
                        ->limit($limit)
                        ->get();
                @endphp
                @if($boards->isEmpty())
                    <p class="text-muted mb-0">게시판이 없습니다.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($boards as $board)
                            <li class="mb-2">
                                <a href="{{ route('posts.index', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" 
                                   class="text-decoration-none d-flex justify-content-between align-items-center" 
                                   style="color: {{ $widgetTextColor }};">
                                    <span style="color: {{ $widgetTextColor }};">{{ $board->name }}</span>
                                    <small style="color: {{ $widgetMutedColor }};">{{ $board->posts_count ?? 0 }}</small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @break

            @case('board_viewer')
                {{-- 게시판 뷰어 위젯 --}}
                @php
                    $boardId = $widgetSettings['board_id'] ?? null;
                    if ($boardId) {
                        $board = \App\Models\Board::where('site_id', $site->id)
                            ->where('id', $boardId)
                            ->first();
                        
                        if ($board) {
                            // PostService를 사용하여 게시글 가져오기
                            $postService = app(\App\Services\PostService::class);
                            $randomOrder = $board->type === 'bookmark' && $board->random_order;
                            $topicId = request()->query('topic');
                            $searchKeyword = request()->query('search');
                            $searchType = request()->query('search_type', 'title_content');
                            $perPage = $board->posts_per_page ?? 20;
                            $posts = $postService->getPostsByBoard($board->id, $perPage, $randomOrder, $topicId, $site->id, $searchKeyword, $searchType);
                            
                            // 질의응답 게시판인 경우 기존 게시글에 qa_status가 없으면 기본값 설정
                            if ($board->type === 'qa') {
                                $qaStatuses = $board->qa_statuses ?? [];
                                if (!empty($qaStatuses)) {
                                    $defaultStatus = $qaStatuses[0]['name'] ?? null;
                                    if ($defaultStatus) {
                                        foreach ($posts->items() as $post) {
                                            if (empty($post->qa_status)) {
                                                $post->update(['qa_status' => $defaultStatus]);
                                                $post->qa_status = $defaultStatus;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            $posts->appends(request()->query());
                            
                            // 추천 기능 활성화 여부 확인
                            $showLikes = $board->enable_likes && \Illuminate\Support\Facades\Schema::hasTable('post_likes');
                            $hasPostLikesTable = \Illuminate\Support\Facades\Schema::hasTable('post_likes');
                            
                            // 저장된 게시글 ID 목록 가져오기 (로그인한 경우)
                            $savedPostIds = [];
                            if (auth()->check() && $board->saved_posts_enabled && \Illuminate\Support\Facades\Schema::hasTable('saved_posts')) {
                                $savedPostIds = \App\Models\SavedPost::where('user_id', auth()->id())
                                    ->where('site_id', $site->id)
                                    ->whereIn('post_id', $posts->pluck('id'))
                                    ->pluck('post_id')
                                    ->toArray();
                            }
                            
                            // 테마 설정
                            $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                            $pointColor = $themeDarkMode === 'dark' 
                                ? $site->getSetting('color_dark_point_main', '#ffffff')
                                : $site->getSetting('color_light_point_main', '#0d6efd');
                            
                            $showViews = $site->getSetting('show_views', '1') == '1';
                            $showDatetime = $site->getSetting('show_datetime', '1') == '1';
                            $newPostHours = (int) $site->getSetting('new_post_hours', 24);
                        }
                    }
                @endphp
                @if(isset($board) && $board)
                    @php
                        // 배경색 없음 설정 확인
                        $boardViewerNoBackground = $widgetSettings['no_background'] ?? false;
                        $boardViewerShadowClass = $boardViewerNoBackground ? '' : 'shadow-sm';
                        $boardViewerBgClass = $boardViewerNoBackground ? 'bg-transparent' : 'bg-white';
                    @endphp
                    {{-- 게시판 헤더 이미지는 위젯에서는 표시하지 않음 (게시판 페이지에서만 표시) --}}
                    
                    {{-- 게시판 제목 및 설명 --}}
                    @php
                        $hideTitleDescription = $board->hide_title_description ?? false;
                    @endphp
                    @if(!$hideTitleDescription)
                        <div class="{{ $boardViewerBgClass }} p-3 rounded {{ $boardViewerShadowClass }} mb-3">
                            <h2 class="mb-1"><i class="bi bi-file-text"></i> {{ $board->name }}</h2>
                            @if($board->description)
                                <p class="text-muted mb-0">{{ $board->description }}</p>
                            @endif
                        </div>
                    @endif
                    
                    {{-- 주제 필터 --}}
                    @if($board->topics()->count() > 0)
                        <div class="mb-3">
                            <div class="topic-filter-container d-flex gap-2 align-items-center" style="overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; scrollbar-width: thin;">
                                <button type="button" 
                                   class="btn btn-sm board-viewer-topic-btn {{ !isset($topicId) || !$topicId ? 'active' : '' }}" 
                                   data-widget-id="{{ $widget->id }}"
                                   data-board-id="{{ $board->id }}"
                                   data-topic-id=""
                                   style="flex-shrink: 0; {{ !isset($topicId) || !$topicId ? 'background-color: ' . $pointColor . '; border-color: ' . $pointColor . '; color: white;' : 'background-color: transparent; border-color: ' . $pointColor . '; color: ' . $pointColor . ';' }}">
                                    전체
                                </button>
                                @foreach($board->topics()->ordered()->get() as $topic)
                                    <button type="button" 
                                       class="btn btn-sm board-viewer-topic-btn {{ (isset($topicId) && $topicId == $topic->id) ? 'active' : '' }}"
                                       data-widget-id="{{ $widget->id }}"
                                       data-board-id="{{ $board->id }}"
                                       data-topic-id="{{ $topic->id }}"
                                       style="background-color: {{ (isset($topicId) && $topicId == $topic->id) ? $pointColor : 'transparent' }}; border-color: {{ $pointColor }}; color: {{ (isset($topicId) && $topicId == $topic->id) ? 'white' : $pointColor }}; flex-shrink: 0;">
                                        {{ $topic->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    {{-- 검색 결과 표시 --}}
                    @if(request('search'))
                        @php
                            $searchTypeLabel = request('search_type', 'title_content') == 'author' ? '작성자' : '제목 또는 내용';
                        @endphp
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-search me-2"></i>
                            <strong>{{ $searchTypeLabel }}</strong>에서 <strong>"{{ request('search') }}"</strong> 검색 결과: <strong>{{ $posts->total() }}</strong>개
                            <a href="{{ route('posts.index', array_merge(['site' => $site->slug, 'boardSlug' => $board->slug], request()->except(['search', 'search_type', 'page']))) }}" 
                               class="btn btn-sm btn-outline-primary ms-2">
                                <i class="bi bi-x-lg me-1"></i>검색 초기화
                            </a>
                        </div>
                    @endif
                    
                    {{-- 게시글 목록 --}}
                    <div id="board-viewer-posts-{{ $widget->id }}">
                    @if($posts->count() > 0)
                        @if($board->type === 'pinterest')
                            {{-- 핀터레스트 게시판 레이아웃 (Masonry 스타일) --}}
                            @php
                                // 디바이스별 컬럼 수 설정 (기본값)
                                $mobileCols = $board->pinterest_columns_mobile ?? 2;
                                $tabletCols = $board->pinterest_columns_tablet ?? 3;
                                $desktopCols = $board->pinterest_columns_desktop ?? 4;
                                $largeCols = $board->pinterest_columns_large ?? 6;
                                $masonryGap = '12px';
                                $widgetMasonryId = 'pinterest-masonry-widget-' . ($widget->id ?? uniqid());
                            @endphp
                            <style>
                                #{{ $widgetMasonryId }} {
                                    column-count: {{ $mobileCols }};
                                    column-gap: {{ $masonryGap }};
                                    margin-bottom: -{{ $masonryGap }};
                                }
                                #{{ $widgetMasonryId }} .pinterest-masonry-widget-item {
                                    break-inside: avoid;
                                    margin-bottom: {{ $masonryGap }};
                                    display: inline-block;
                                    width: 100%;
                                }
                                @if($boardViewerNoBackground)
                                #{{ $widgetMasonryId }} .pinterest-masonry-widget-item .card,
                                #{{ $widgetMasonryId }} .pinterest-masonry-widget-item .card.bg-white,
                                #{{ $widgetMasonryId }} .pinterest-masonry-widget-item .card.bg-transparent,
                                #{{ $widgetMasonryId }} .pinterest-masonry-widget-item .card.shadow-sm {
                                    background: transparent !important;
                                    background-color: transparent !important;
                                    box-shadow: none !important;
                                    -webkit-box-shadow: none !important;
                                    border: none !important;
                                }
                                @endif
                                @media (min-width: 768px) {
                                    #{{ $widgetMasonryId }} {
                                        column-count: {{ $tabletCols }};
                                    }
                                }
                                @media (min-width: 992px) {
                                    #{{ $widgetMasonryId }} {
                                        column-count: {{ $desktopCols }};
                                    }
                                }
                                @media (min-width: 1200px) {
                                    #{{ $widgetMasonryId }} {
                                        column-count: {{ $largeCols }};
                                    }
                                }
                            </style>
                            <div id="{{ $widgetMasonryId }}">
                                @foreach($posts as $post)
                                    <div class="pinterest-masonry-widget-item">
                                        <div class="card {{ $boardViewerShadowClass }} {{ $boardViewerBgClass }}" style="overflow: hidden; border-radius: 12px;">
                                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                               class="text-decoration-none text-dark">
                                                {{-- 이미지 영역 --}}
                                                @if($post->thumbnail_path)
                                                    <div class="position-relative" style="overflow: hidden;{{ $boardViewerNoBackground ? '' : ' background-color: #f8f9fa;' }}">
                                                        <img src="{{ asset('storage/' . $post->thumbnail_path) }}" 
                                                             alt="{{ $post->title }}" 
                                                             class="img-fluid"
                                                             style="width: 100%; height: auto; display: block; object-fit: cover; min-height: 150px;">
                                                        {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                                        <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                                            @if($board->enable_likes && $post->like_count > 0)
                                                                <span class="badge bg-dark bg-opacity-75">
                                                                    <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                                </span>
                                                            @endif
                                                            @if($post->comments->count() > 0)
                                                                <span class="badge bg-dark bg-opacity-75">
                                                                    <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    @php
                                                        // 게시글 내용에서 첫 번째 이미지 추출
                                                        $postContent = $post->content;
                                                        preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $postContent, $matches);
                                                        $firstImage = $matches[1] ?? null;
                                                    @endphp
                                                    @if($firstImage)
                                                        <div class="position-relative" style="overflow: hidden;">
                                                            <img src="{{ $firstImage }}" 
                                                                 alt="{{ $post->title }}" 
                                                                 class="img-fluid"
                                                                 style="width: 100%; height: auto; display: block;">
                                                            {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                                            <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                                                @if($board->enable_likes && $post->like_count > 0)
                                                                    <span class="badge bg-dark bg-opacity-75">
                                                                        <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                                    </span>
                                                                @endif
                                                                @if($post->comments->count() > 0)
                                                                    <span class="badge bg-dark bg-opacity-75">
                                                                        <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="position-relative bg-secondary bg-opacity-25 d-flex flex-column align-items-center justify-content-center" 
                                                             style="min-height: 150px;">
                                                            <i class="bi bi-image display-4 text-muted mb-2"></i>
                                                            <span class="text-muted small">No image</span>
                                                            {{-- 좋아요/댓글 수 표시 (우측 하단) --}}
                                                            <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                                                @if($board->enable_likes && $post->like_count > 0)
                                                                    <span class="badge bg-dark bg-opacity-75">
                                                                        <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                                    </span>
                                                                @endif
                                                                @if($post->comments->count() > 0)
                                                                    <span class="badge bg-dark bg-opacity-75">
                                                                        <i class="bi bi-chat-dots"></i> {{ $post->comments->count() }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                                {{-- 핀터레스트 게시판 제목 표시 (pinterest_show_title이 true인 경우) --}}
                                                @if($board->pinterest_show_title ?? false)
                                                    @php
                                                        $pinterestTitleAlign = $board->pinterest_title_align ?? 'left';
                                                    @endphp
                                                    <div class="card-body p-2" style="background-color: rgba(255,255,255,0.95); text-align: {{ $pinterestTitleAlign }};">
                                                        <h6 class="card-title mb-0 small text-truncate" style="font-size: 0.85rem; line-height: 1.3;">
                                                            {{ $post->title }}
                                                        </h6>
                                                    </div>
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- 일반 게시판 레이아웃 (심플 리스트 형태) --}}
                            <div class="card {{ $boardViewerBgClass }} {{ $boardViewerShadowClass }}">
                                <div class="list-group list-group-flush">
                                @foreach($posts as $post)
                                @php
                                    $hasImage = false;
                                    if ($post->content) {
                                        $hasImage = preg_match('/<img[^>]+>/i', $post->content);
                                    }
                                    $isToday = $post->created_at->isToday();
                                @endphp
                                <div class="list-group-item list-group-item-action border-start-0 border-end-0 {{ !$loop->last ? 'border-bottom' : '' }} position-relative" style="padding: 1rem;">
                                    @auth
                                        @if($board->saved_posts_enabled && \Illuminate\Support\Facades\Schema::hasTable('saved_posts'))
                                            @php
                                                $isSaved = in_array($post->id, $savedPostIds ?? []);
                                            @endphp
                                            <button type="button" 
                                                    class="btn btn-link p-2 position-absolute" 
                                                    style="bottom: 0.5rem; right: 0.5rem; z-index: 10; color: #6c757d; text-decoration: none;"
                                                    onclick="toggleSave({{ $post->id }})"
                                                    id="save-btn-{{ $post->id }}">
                                                <i class="bi {{ $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' }}" style="font-size: 1.25rem;"></i>
                                            </button>
                                        @endif
                                    @endauth
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        @if($post->topics->count() > 0)
                                            @foreach($post->topics as $topic)
                                                <span class="badge" style="background-color: {{ $topic->color }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0;">
                                                    {{ $topic->name }}
                                                </span>
                                            @endforeach
                                        @endif
                                        <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                           class="text-decoration-none text-dark flex-grow-1" style="line-height: 1.5;">
                                            <span>
                                                @if($post->is_pinned)
                                                    <i class="bi bi-pin-angle-fill me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                                @endif
                                                @if($post->is_notice)
                                                    <i class="bi bi-megaphone me-1" style="color: {{ $pointColor }}; font-size: 1.1em;"></i>
                                                @endif
                                                @php
                                                    $isSecret = $board->force_secret || ($board->enable_secret && $post->is_secret);
                                                    $canViewSecret = false;
                                                    if ($isSecret && auth()->check()) {
                                                        $canViewSecret = (auth()->id() === $post->user_id || auth()->user()->canManage());
                                                    }
                                                @endphp
                                                @if($isSecret)
                                                    @if($canViewSecret)
                                                        <i class="bi bi-lock me-1"></i>{{ $post->title }}
                                                    @else
                                                        <i class="bi bi-lock me-1"></i>비밀 글입니다.
                                                    @endif
                                                @else
                                                    {{ $post->title }}
                                                @endif
                                                @if($post->comments->count() > 0)
                                                    <span class="fw-bold ms-1" style="color: {{ $pointColor }};">+{{ $post->comments->count() }}</span>
                                                @endif
                                                @if($board->enable_likes && $post->like_count > 0)
                                                    <span class="ms-1" style="color: #0d6efd;">
                                                        <i class="bi bi-hand-thumbs-up"></i> {{ $post->like_count }}
                                                    </span>
                                                @endif
                                                @if($isToday)
                                                    <span class="text-warning ms-1" style="font-size: 0.75rem; font-weight: bold;">N</span>
                                                @endif
                                                @if($hasImage)
                                                    <i class="bi bi-image ms-1 text-muted" style="font-size: 0.875rem;"></i>
                                                @endif
                                            </span>
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.875rem;">
                                        <span>
                                            @if($board->enable_anonymous)
                                                익명
                                            @else
                                                <x-user-rank :user="$post->user" :site="$site" />
                                                {{ $post->user->nickname ?? $post->user->name }}
                                            @endif
                                        </span>
                                        @if($showViews)
                                            <span>·</span>
                                            <span>조회수 {{ number_format($post->views) }}</span>
                                        @endif
                                        @if($showDatetime)
                                            <span>·</span>
                                            <span>{{ $post->created_at->format('Y.m.d H:i') }}</span>
                                        @endif
                                    </div>
                                    @if($post->replies->count() > 0)
                                        <div class="mt-2 pt-2 border-top" style="font-size: 0.8rem;">
                                            <div class="text-muted">
                                                <i class="bi bi-reply"></i> 답글:
                                                @foreach($post->replies as $reply)
                                                    <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $reply->id]) }}" 
                                                       class="text-decoration-none ms-1">
                                                        @if($board->enable_secret && $reply->is_secret)
                                                            @php
                                                                $canViewSecret = false;
                                                                if (auth()->check()) {
                                                                    $canViewSecret = (auth()->id() === $reply->user_id || auth()->user()->canManage());
                                                                }
                                                            @endphp
                                                            @if($canViewSecret)
                                                                {{ Str::limit($reply->title, 30) }}
                                                            @else
                                                                비밀 글입니다.
                                                            @endif
                                                        @else
                                                            {{ Str::limit($reply->title, 30) }}
                                                        @endif
                                                    </a>
                                                    @if(!$loop->last)
                                                        <span class="text-muted">|</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                            </div>
                        </div>
                        @endif
                        
                        {{-- 페이지네이션 또는 더보기 버튼 --}}
                        @if($posts->hasPages())
                            @if($board->type === 'pinterest')
                                {{-- 핀터레스트 타입: 더보기 버튼 --}}
                                @if($posts->hasMorePages())
                                <div class="mt-4 text-center" id="pinterest-widget-load-more-container-{{ $widget->id }}">
                                    <button type="button" 
                                            class="btn px-5 py-2 pinterest-widget-load-more-btn" 
                                            data-widget-id="{{ $widget->id }}"
                                            data-board-slug="{{ $board->slug }}"
                                            data-page="{{ $posts->currentPage() + 1 }}"
                                            data-url="{{ route('boards.loadMore', ['site' => $site->slug, 'slug' => $board->slug]) }}"
                                            style="border-radius: 50px; font-weight: 500; background-color: {{ $pointColor }}; border-color: {{ $pointColor }}; color: #fff;">
                                        <span class="btn-text">더보기</span>
                                        <span class="btn-loading d-none">
                                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                            로딩 중...
                                        </span>
                                    </button>
                                </div>
                                @endif
                            @else
                                <div class="mt-4">
                                    {{ $posts->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                                </div>
                            @endif
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>게시글이 없습니다.
                        </div>
                    @endif
                    </div>{{-- end of board-viewer-posts --}}
                    
                    {{-- 검색 폼 (enable_search가 활성화된 경우에만 표시) --}}
                    @if($board->enable_search ?? true)
                    <div class="mt-4 d-flex justify-content-center">
                        <form method="GET" action="{{ route('posts.index', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" class="d-flex gap-2 align-items-center">
                            <input type="hidden" name="topic" value="{{ request('topic') }}">
                            <select name="search_type" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                                <option value="title_content" {{ request('search_type', 'title_content') == 'title_content' ? 'selected' : '' }}>제목 또는 내용</option>
                                <option value="author" {{ request('search_type') == 'author' ? 'selected' : '' }}>작성자</option>
                            </select>
                            <input type="text" 
                                   name="search" 
                                   class="form-control form-control-sm" 
                                   placeholder="검색어를 입력하세요..." 
                                   value="{{ request('search') }}"
                                   style="max-width: 300px;">
                            <button type="submit" class="btn btn-sm btn-primary" style="background-color: {{ $pointColor }}; border-color: {{ $pointColor }}; height: 31px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('posts.index', array_merge(['site' => $site->slug, 'boardSlug' => $board->slug], request()->except(['search', 'search_type', 'page']))) }}" 
                                   class="btn btn-sm btn-outline-secondary" style="height: 31px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                    @endif
                    
                    {{-- 글쓰기 버튼 --}}
                    @if(auth()->check() && ($board->write_permission === 'user' || ($board->write_permission === 'admin' && auth()->user()->canManage())))
                        <div class="mt-3 text-end">
                            <a href="{{ route('posts.create', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" 
                               class="btn btn-primary"
                               style="background-color: {{ $pointColor }}; border-color: {{ $pointColor }};">
                                <i class="bi bi-pencil me-1"></i> 글쓰기
                            </a>
                        </div>
                    @elseif($board->write_permission === 'guest')
                        <div class="mt-3 text-end">
                            <a href="{{ route('posts.create', ['site' => $site->slug, 'boardSlug' => $board->slug]) }}" 
                               class="btn btn-primary"
                               style="background-color: {{ $pointColor }}; border-color: {{ $pointColor }};">
                                <i class="bi bi-pencil me-1"></i> 글쓰기
                            </a>
                        </div>
                    @endif
                    {{-- 주제 필터 AJAX 스크립트 --}}
                    <script>
                    (function() {
                        const widgetId = '{{ $widget->id }}';
                        const boardId = '{{ $board->id }}';
                        const siteSlug = '{{ $site->slug }}';
                        const boardSlug = '{{ $board->slug }}';
                        const pointColor = '{{ $pointColor }}';
                        
                        document.querySelectorAll('.board-viewer-topic-btn[data-widget-id="' + widgetId + '"]').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                const topicId = this.dataset.topicId;
                                
                                // 버튼 스타일 업데이트
                                document.querySelectorAll('.board-viewer-topic-btn[data-widget-id="' + widgetId + '"]').forEach(b => {
                                    b.classList.remove('active');
                                    b.style.backgroundColor = 'transparent';
                                    b.style.color = pointColor;
                                });
                                this.classList.add('active');
                                this.style.backgroundColor = pointColor;
                                this.style.color = 'white';
                                
                                // AJAX로 게시글 목록 가져오기
                                let url = '/api/boards/' + boardId + '/posts?site=' + siteSlug;
                                if (topicId) {
                                    url += '&topic=' + topicId;
                                }
                                
                                const postsContainer = document.getElementById('board-viewer-posts-' + widgetId);
                                if (postsContainer) {
                                    postsContainer.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
                                }
                                
                                fetch(url, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success && postsContainer) {
                                        // 게시글 목록 HTML 생성
                                        let html = '';
                                        if (data.posts && data.posts.length > 0) {
                                            if (data.board_type === 'pinterest') {
                                                html = '<div class="row g-3">';
                                                data.posts.forEach(post => {
                                                    const colClass = data.col_class || 'col-6 col-md-4 col-lg-3';
                                                    const thumbnail = post.thumbnail_path ? '/storage/' + post.thumbnail_path : (post.first_image || '');
                                                    html += '<div class="' + colClass + '">';
                                                    html += '<div class="card shadow-sm" style="overflow: hidden; border-radius: 12px;">';
                                                    html += '<a href="/site/' + siteSlug + '/boards/' + boardSlug + '/posts/' + post.id + '" class="text-decoration-none text-dark">';
                                                    if (thumbnail) {
                                                        html += '<div class="position-relative" style="overflow: hidden; background-color: #f8f9fa;">';
                                                        html += '<img src="' + thumbnail + '" alt="' + post.title + '" class="img-fluid" style="width: 100%; height: auto; display: block; object-fit: cover; min-height: 150px;">';
                                                        html += '</div>';
                                                    } else {
                                                        html += '<div class="position-relative bg-secondary bg-opacity-25 d-flex flex-column align-items-center justify-content-center" style="min-height: 150px;">';
                                                        html += '<i class="bi bi-image display-4 text-muted mb-2"></i>';
                                                        html += '<span class="text-muted small">No image</span>';
                                                        html += '</div>';
                                                    }
                                                    if (data.show_title) {
                                                        const titleAlign = data.title_align || 'left';
                                                        html += '<div class="card-body p-2" style="background-color: rgba(255,255,255,0.95); text-align: ' + titleAlign + ';">';
                                                        html += '<h6 class="card-title mb-0 small text-truncate" style="font-size: 0.85rem; line-height: 1.3;">' + post.title + '</h6>';
                                                        html += '</div>';
                                                    }
                                                    html += '</a>';
                                                    html += '</div>';
                                                    html += '</div>';
                                                });
                                                html += '</div>';
                                            } else {
                                                html = '<div class="card bg-white shadow-sm"><div class="list-group list-group-flush">';
                                                data.posts.forEach((post, index) => {
                                                    html += '<div class="list-group-item list-group-item-action border-start-0 border-end-0 ' + (index < data.posts.length - 1 ? 'border-bottom' : '') + '" style="padding: 1rem;">';
                                                    html += '<div class="d-flex align-items-start gap-2 mb-2">';
                                                    if (post.topics && post.topics.length > 0) {
                                                        post.topics.forEach(topic => {
                                                            html += '<span class="badge" style="background-color: ' + topic.color + '; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: normal; flex-shrink: 0;">' + topic.name + '</span>';
                                                        });
                                                    }
                                                    html += '<a href="/site/' + siteSlug + '/boards/' + boardSlug + '/posts/' + post.id + '" class="text-decoration-none text-dark flex-grow-1" style="line-height: 1.5;">';
                                                    html += '<span>' + post.title + '</span>';
                                                    html += '</a>';
                                                    html += '</div>';
                                                    html += '<div class="d-flex align-items-center gap-2 text-muted small">';
                                                    html += '<span>' + post.author + '</span>';
                                                    html += '<span>|</span>';
                                                    html += '<span>' + post.created_at + '</span>';
                                                    html += '</div>';
                                                    html += '</div>';
                                                });
                                                html += '</div></div>';
                                            }
                                        } else {
                                            html = '<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>게시글이 없습니다.</div>';
                                        }
                                        postsContainer.innerHTML = html;
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    if (postsContainer) {
                                        postsContainer.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>게시글을 불러오는 중 오류가 발생했습니다.</div>';
                                    }
                                });
                            });
                        });
                    })();
                    </script>
                    
                    {{-- 핀터레스트 더보기 버튼 스크립트 --}}
                    @if($board->type === 'pinterest')
                    <script>
                    (function() {
                        const widgetId = '{{ $widget->id }}';
                        const loadMoreBtn = document.querySelector('.pinterest-widget-load-more-btn[data-widget-id="' + widgetId + '"]');
                        if (!loadMoreBtn) return;
                        
                        const masonryContainer = document.getElementById('pinterest-masonry-widget-' + widgetId);
                        if (!masonryContainer) return;
                        
                        loadMoreBtn.addEventListener('click', function() {
                            const btn = this;
                            const btnText = btn.querySelector('.btn-text');
                            const btnLoading = btn.querySelector('.btn-loading');
                            const currentPage = parseInt(btn.dataset.page, 10);
                            const baseUrl = btn.dataset.url;

                            const resetButtonState = () => {
                                btn.disabled = false;
                                btnText.classList.remove('d-none');
                                btnLoading.classList.add('d-none');
                            };
                            
                            // 버튼 로딩 상태
                            btn.disabled = true;
                            btnText.classList.add('d-none');
                            btnLoading.classList.remove('d-none');
                            
                            // 쿼리 파라미터
                            const urlParams = new URLSearchParams();
                            urlParams.set('page', currentPage);
                            urlParams.set('is_widget', '1');
                            
                            fetch(baseUrl + '?' + urlParams.toString(), {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.success && data.html) {
                                    // 새 게시글 HTML 추가
                                    masonryContainer.insertAdjacentHTML('beforeend', data.html);
                                    
                                    // 다음 페이지로 업데이트
                                    btn.dataset.page = currentPage + 1;
                                    
                                    // 더 이상 페이지가 없으면 버튼 숨김
                                    if (!data.hasMorePages) {
                                        document.getElementById('pinterest-widget-load-more-container-' + widgetId).style.display = 'none';
                                    }
                                }
                                resetButtonState();
                            })
                            .catch(error => {
                                console.error('Error loading more posts:', error);
                                resetButtonState();
                            });
                        });
                    })();
                    </script>
                    @endif
                @else
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>게시판을 선택해주세요.
                    </div>
                @endif
                @break

            @case('search')
                {{-- 검색 위젯 --}}
                <form action="{{ route('search', ['site' => $site->slug]) }}" method="GET" class="d-flex">
                    <input type="text" 
                           name="q" 
                           class="form-control form-control-sm" 
                           placeholder="검색어를 입력하세요" 
                           value="{{ request('q') }}">
                    <button type="submit" class="btn btn-primary btn-sm ms-2">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                @break

            @case('statistics')
                {{-- 통계 위젯 --}}
                @php
                    $totalPosts = \App\Models\Post::where('site_id', $site->id)->count();
                    $totalUsers = \App\Models\User::where('site_id', $site->id)->count();
                    $totalBoards = \App\Models\Board::where('site_id', $site->id)->active()->count();
                @endphp
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 d-flex justify-content-between">
                        <span>전체 게시글</span>
                        <strong>{{ number_format($totalPosts) }}</strong>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span>전체 회원</span>
                        <strong>{{ number_format($totalUsers) }}</strong>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span>게시판 수</span>
                        <strong>{{ number_format($totalBoards) }}</strong>
                    </li>
                </ul>
                @break

            @case('notice')
                {{-- 공지사항 위젯 --}}
                @php
                    $notices = \App\Models\Post::where('site_id', $site->id)
                        ->where('is_notice', true)
                        ->with(['user', 'board'])
                        ->orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->get();
                @endphp
                @if($notices->isEmpty())
                    <p class="text-muted mb-0">공지사항이 없습니다.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($notices as $post)
                            <li class="mb-2 pb-2 border-bottom">
                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug ?? 'default', 'post' => $post->id]) }}" 
                                   class="text-decoration-none d-block" 
                                   style="color: {{ $widgetTextColor }};">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.9rem; color: {{ $widgetTextColor }};">
                                        {{ $post->title }}
                                    </div>
                                    <small style="color: {{ $widgetMutedColor }}"
                                        {{ $post->created_at->diffForHumans() }}
                                    </small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @break

            @case('custom_html')
                {{-- 커스텀 HTML 위젯 --}}
                @if(!empty($widgetSettings['html']))
                    {!! $widgetSettings['html'] !!}
                @else
                    <p class="text-muted mb-0">커스텀 HTML이 설정되지 않았습니다.</p>
                @endif
                @break

            @case('tab_menu')
                {{-- 탭메뉴 위젯 --}}
                @php
                    $tabs = $widgetSettings['tabs'] ?? [];
                @endphp
                @if(empty($tabs))
                    <p class="text-muted mb-0">탭메뉴가 설정되지 않았습니다.</p>
                @else
                    @php
                        $tabCount = count($tabs);
                        $isScrollable = $tabCount > 4; // 4개 초과 시 스크롤 가능
                    @endphp
                    <div class="sidebar-tab-wrapper" style="{{ $isScrollable ? 'overflow-x: auto; overflow-y: hidden;' : 'overflow: hidden;' }}">
                        <ul class="nav nav-tabs mb-0 sidebar-tab-menu" 
                            role="tablist" 
                            style="display: flex; width: {{ $isScrollable ? 'max-content' : '100%' }}; flex-wrap: nowrap;">
                            @foreach($tabs as $index => $tab)
                                <li class="nav-item" role="presentation" style="{{ $isScrollable ? 'flex: 0 0 auto; min-width: 80px;' : 'flex: 1 1 0; min-width: 0;' }}">
                                    <button class="nav-link sidebar-tab-btn {{ $index === 0 ? 'active' : '' }}" 
                                            id="tab-{{ $widget->id }}-{{ $index }}-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#tab-{{ $widget->id }}-{{ $index }}" 
                                            type="button" 
                                            role="tab"
                                            style="width: 100%; text-align: center; white-space: nowrap;">
                                        {{ $tab['name'] ?? '탭 ' . ($index + 1) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <style>
                        .sidebar-tab-menu {
                            border: none !important;
                            border-bottom: 1px solid #dee2e6 !important;
                        }
                        .sidebar-tab-menu .nav-item {
                            min-width: 0 !important;
                        }
                        .sidebar-tab-menu .sidebar-tab-btn {
                            width: 100% !important;
                            text-align: center !important;
                        }
                        .sidebar-tab-menu .sidebar-tab-btn {
                            border: none !important;
                            background-color: transparent !important;
                            color: #6c757d !important;
                            padding: 0.5rem 1rem !important;
                            margin-bottom: -1px !important;
                            border-bottom: 2px solid transparent !important;
                            border-radius: 0 !important;
                            height: 39.1875px !important;
                            min-height: 39.1875px !important;
                            max-height: 39.1875px !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            line-height: 1.2 !important;
                        }
                        .sidebar-tab-menu .sidebar-tab-btn.active {
                            border-bottom: 2px solid #0d6efd !important;
                            border-radius: 0 !important;
                            color: #0d6efd !important;
                            font-weight: 600 !important;
                            background-color: transparent !important;
                        }
                        .sidebar-tab-menu .sidebar-tab-btn:not(.active):hover {
                            color: #495057 !important;
                        }
                        .sidebar-tab-wrapper {
                            width: 100%;
                        }
                        .sidebar-tab-wrapper::-webkit-scrollbar {
                            height: 4px;
                        }
                        .sidebar-tab-wrapper::-webkit-scrollbar-track {
                            background: #f1f1f1;
                        }
                        .sidebar-tab-wrapper::-webkit-scrollbar-thumb {
                            background: #888;
                            border-radius: 2px;
                        }
                        .sidebar-tab-wrapper::-webkit-scrollbar-thumb:hover {
                            background: #555;
                        }
                    </style>
                    <div class="tab-content" style="margin-top: 0.75rem;">
                        @foreach($tabs as $index => $tab)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                                 id="tab-{{ $widget->id }}-{{ $index }}" 
                                 role="tabpanel">
                                @php
                                    $tabWidgetType = $tab['widget_type'] ?? 'recent_posts';
                                    $tabLimit = $tab['limit'] ?? 10;
                                    
                                    // 위젯 타입에 따라 데이터 가져오기
                                    if ($tabWidgetType === 'popular_posts') {
                                        // 24시간 기준 인기글
                                        $bestPostCriteria = $site->getSetting('best_post_criteria', 'views');
                                        $posts = \App\Models\Post::where('site_id', $site->id)
                                            ->with(['user', 'board'])
                                            ->where('created_at', '>=', now()->subDay()) // 24시간 이내
                                            ->where(function($q) {
                                                if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                                    $q->where('is_secret', '=', 0)
                                                      ->orWhereNull('is_secret');
                                                }
                                            })
                                            ->when($bestPostCriteria === 'likes', function($query) {
                                                if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                                                    $query->withCount(['likes as likes_count' => function($q) {
                                                        $q->where('type', 'like');
                                                    }])
                                                    ->orderBy('likes_count', 'desc');
                                                } else {
                                                    $query->orderBy('created_at', 'desc');
                                                }
                                            })
                                            ->when($bestPostCriteria === 'comments', function($query) {
                                                $query->withCount('comments')
                                                      ->orderBy('comments_count', 'desc');
                                            })
                                            ->when($bestPostCriteria === 'views', function($query) {
                                                $query->orderBy('views', 'desc');
                                            })
                                            ->orderBy('created_at', 'desc')
                                            ->limit($tabLimit)
                                            ->get();
                                    } elseif ($tabWidgetType === 'recent_posts') {
                                        // 24시간 기준 최신글
                                        $posts = \App\Models\Post::where('site_id', $site->id)
                                            ->with(['user', 'board'])
                                            ->where('created_at', '>=', now()->subDay()) // 24시간 이내
                                            ->whereHas('board', function($boardQuery) {
                                                $boardQuery->where(function($bq) {
                                                    $bq->where('force_secret', false)
                                                       ->orWhereNull('force_secret');
                                                });
                                            })
                                            ->where(function($q) {
                                                $q->whereHas('board', function($boardQuery) {
                                                    $boardQuery->where(function($bq) {
                                                        $bq->where('enable_secret', false)
                                                           ->orWhereNull('enable_secret');
                                                    });
                                                })
                                                ->orWhere(function($q2) {
                                                    if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                                        $q2->where('is_secret', false)
                                                           ->orWhereNull('is_secret');
                                                    }
                                                });
                                            })
                                            ->orderBy('created_at', 'desc')
                                            ->limit($tabLimit)
                                            ->get();
                                    } elseif ($tabWidgetType === 'weekly_popular_posts') {
                                        // 주간 인기글 (7일 기준)
                                        $bestPostCriteria = $site->getSetting('best_post_criteria', 'views');
                                        $posts = \App\Models\Post::where('site_id', $site->id)
                                            ->with(['user', 'board'])
                                            ->where('created_at', '>=', now()->subWeek()) // 7일 이내
                                            ->where(function($q) {
                                                if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                                    $q->where('is_secret', '=', 0)
                                                      ->orWhereNull('is_secret');
                                                }
                                            })
                                            ->when($bestPostCriteria === 'likes', function($query) {
                                                if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                                                    $query->withCount(['likes as likes_count' => function($q) {
                                                        $q->where('type', 'like');
                                                    }])
                                                    ->orderBy('likes_count', 'desc');
                                                } else {
                                                    $query->orderBy('created_at', 'desc');
                                                }
                                            })
                                            ->when($bestPostCriteria === 'comments', function($query) {
                                                $query->withCount('comments')
                                                      ->orderBy('comments_count', 'desc');
                                            })
                                            ->when($bestPostCriteria === 'views', function($query) {
                                                $query->orderBy('views', 'desc');
                                            })
                                            ->orderBy('created_at', 'desc')
                                            ->limit($tabLimit)
                                            ->get();
                                    } elseif ($tabWidgetType === 'monthly_popular_posts') {
                                        // 월간 인기글 (30일 기준)
                                        $bestPostCriteria = $site->getSetting('best_post_criteria', 'views');
                                        $posts = \App\Models\Post::where('site_id', $site->id)
                                            ->with(['user', 'board'])
                                            ->where('created_at', '>=', now()->subDays(30)) // 30일 이내
                                            ->where(function($q) {
                                                if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                                    $q->where('is_secret', '=', 0)
                                                      ->orWhereNull('is_secret');
                                                }
                                            })
                                            ->when($bestPostCriteria === 'likes', function($query) {
                                                if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
                                                    $query->withCount(['likes as likes_count' => function($q) {
                                                        $q->where('type', 'like');
                                                    }])
                                                    ->orderBy('likes_count', 'desc');
                                                } else {
                                                    $query->orderBy('created_at', 'desc');
                                                }
                                            })
                                            ->when($bestPostCriteria === 'comments', function($query) {
                                                $query->withCount('comments')
                                                      ->orderBy('comments_count', 'desc');
                                            })
                                            ->when($bestPostCriteria === 'views', function($query) {
                                                $query->orderBy('views', 'desc');
                                            })
                                            ->orderBy('created_at', 'desc')
                                            ->limit($tabLimit)
                                            ->get();
                                    } elseif ($tabWidgetType === 'board') {
                                        // 게시판 위젯
                                        $boardId = $tab['board_id'] ?? null;
                                        if ($boardId) {
                                            $board = \App\Models\Board::find($boardId);
                                            if ($board) {
                                                $posts = \App\Models\Post::where('site_id', $site->id)
                                                    ->where('board_id', $boardId)
                                                    ->with(['user', 'board'])
                                                    ->where(function($q) {
                                                        if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_secret')) {
                                                            $q->where('is_secret', '=', 0)
                                                              ->orWhereNull('is_secret');
                                                        }
                                                    })
                                                    ->orderBy('created_at', 'desc')
                                                    ->limit($tabLimit)
                                                    ->get();
                                            } else {
                                                $posts = collect();
                                            }
                                        } else {
                                            $posts = collect();
                                        }
                                    } elseif ($tabWidgetType === 'notice') {
                                        $posts = \App\Models\Post::where('site_id', $site->id)
                                            ->where('is_notice', true)
                                            ->with(['user', 'board'])
                                            ->orderBy('created_at', 'desc')
                                            ->limit($tabLimit)
                                            ->get();
                                    } else {
                                        $posts = collect();
                                    }
                                @endphp
                                
                                @if($posts->isEmpty())
                                    <p class="text-muted mb-0">게시글이 없습니다.</p>
                                @else
                                    <ul class="list-unstyled mb-0">
                                        @foreach($posts as $post)
                                            <li class="mb-2 pb-2 border-bottom">
                                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug ?? 'default', 'post' => $post->id]) }}" 
                                                   class="text-decoration-none d-block" 
                                                   style="color: #495057;">
                                                    <div class="fw-semibold text-truncate" style="font-size: 0.9rem;">
                                                        {{ $post->title }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $post->board->name ?? '게시판' }} · 
                                                        {{ $post->user->nickname ?? $post->user->name ?? '익명' }} · 
                                                        {{ $post->created_at->diffForHumans() }}
                                                    </small>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
                @break

            @case('toggle_menu')
                {{-- 토글 메뉴 위젯 --}}
                @php
                    $toggleMenuId = $widgetSettings['toggle_menu_id'] ?? ($widgetSettings['toggle_menu_ids'][0] ?? null);
                    // 다크모드용 토글 메뉴 색상
                    $toggleHeaderBg = $isDark ? 'rgb(53, 53, 53)' : '#f8f9fa';
                    $toggleContentBg = $isDark ? 'rgb(43, 43, 43)' : '#fff';
                    $toggleActiveBg = $isDark ? 'rgb(63, 63, 63)' : '#e7f3ff';
                    $toggleTextColor = $isDark ? '#ffffff' : 'inherit';
                @endphp
                @if(empty($toggleMenuId))
                    <p style="color: {{ $widgetMutedColor }};" class="mb-0">토글 메뉴가 설정되지 않았습니다.</p>
                @else
                    @php
                        $toggleMenu = \App\Models\ToggleMenu::where('site_id', $site->id)
                            ->where('id', $toggleMenuId)
                            ->where('is_active', true)
                            ->with('items')
                            ->first();
                    @endphp
                    @if($toggleMenu && $toggleMenu->items->count() > 0)
                        <div class="toggle-menu-widget" data-widget-id="{{ $widget->id }}">
                            @if($widget->title)
                                <h5 class="mt-3 mb-3" style="color: {{ $widgetTextColor }};">{{ $widget->title }}</h5>
                            @endif
                            @foreach($toggleMenu->items as $item)
                                <div class="toggle-menu-item-widget mb-2" data-id="{{ $toggleMenu->id }}-{{ $item->id }}">
                                    <div class="toggle-menu-header-widget" onclick="toggleWidgetItemContent(this)" style="padding: 0.75rem; background-color: {{ $toggleHeaderBg }}; border: 1px solid {{ $widgetBorderColor }}; cursor: pointer; display: flex; align-items: center; justify-content: space-between; color: {{ $toggleTextColor }}; {{ $isRoundTheme ? 'border-radius: 0.375rem 0.375rem 0 0;' : '' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-chevron-right toggle-icon-widget me-2" style="transition: transform 0.3s; color: {{ $toggleTextColor }};"></i>
                                            <strong style="color: {{ $toggleTextColor }};">{{ $item->title }}</strong>
                                        </div>
                                    </div>
                                    <div class="toggle-menu-content-widget" style="display: none; padding: 0.75rem; border: 1px solid {{ $widgetBorderColor }}; border-top: none; background-color: {{ $toggleContentBg }}; color: {{ $toggleTextColor }}; {{ $isRoundTheme ? 'border-radius: 0 0 0.375rem 0.375rem;' : '' }}">
                                        <div style="white-space: pre-wrap; color: {{ $toggleTextColor }};">{!! nl2br(e($item->content)) !!}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <script>
                        (function() {
                            const isDarkMode = {{ $isDark ? 'true' : 'false' }};
                            const toggleHeaderBg = '{{ $toggleHeaderBg }}';
                            const toggleActiveBg = '{{ $toggleActiveBg }}';
                            
                            function toggleWidgetItemContent(header) {
                                const item = header.closest('.toggle-menu-item-widget');
                                const content = item.querySelector('.toggle-menu-content-widget');
                                const icon = header.querySelector('.toggle-icon-widget');
                                
                                if (content.style.display === 'none' || !content.style.display) {
                                    content.style.display = 'block';
                                    header.style.borderRadius = '{{ $isRoundTheme ? "0.375rem 0.375rem 0 0" : "0" }}';
                                    content.style.borderRadius = '{{ $isRoundTheme ? "0 0 0.375rem 0.375rem" : "0" }}';
                                    icon.style.transform = 'rotate(90deg)';
                                    header.style.backgroundColor = toggleActiveBg;
                                } else {
                                    content.style.display = 'none';
                                    header.style.borderRadius = '{{ $isRoundTheme ? "0.375rem" : "0" }}';
                                    icon.style.transform = 'rotate(0deg)';
                                    header.style.backgroundColor = toggleHeaderBg;
                                }
                            }
                            
                            document.querySelectorAll('.toggle-menu-header-widget').forEach(header => {
                                header.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    toggleWidgetItemContent(this);
                                });
                            });
                        })();
                        </script>
                    @else
                        <p style="color: {{ $widgetMutedColor }};" class="mb-0">활성화된 토글 메뉴가 없습니다.</p>
                    @endif
                @endif
                @break

            @case('user_ranking')
                {{-- 회원 랭킹 위젯 --}}
                @php
                    $enableRankRanking = $widgetSettings['enable_rank_ranking'] ?? false;
                    $enablePointRanking = $widgetSettings['enable_point_ranking'] ?? false;
                    $rankingLimit = $widgetSettings['ranking_limit'] ?? 5;
                    $showTabs = $enableRankRanking && $enablePointRanking;
                @endphp
                
                @if(!$enableRankRanking && !$enablePointRanking)
                    <p class="text-muted mb-0">랭킹 설정이 되지 않았습니다.</p>
                @else
                    @if($showTabs)
                        {{-- 탭 형태로 표시 --}}
                        <div class="sidebar-tab-wrapper" style="overflow: hidden;">
                            <ul class="nav nav-tabs mb-0 sidebar-tab-menu" 
                                role="tablist" 
                                style="display: flex; width: 100%; flex-wrap: nowrap;">
                                @if($enableRankRanking)
                                    <li class="nav-item" role="presentation" style="flex: 1 1 0; min-width: 0;">
                                        <button class="nav-link sidebar-tab-btn active" 
                                                id="ranking-tab-{{ $widget->id }}-rank" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#ranking-{{ $widget->id }}-rank" 
                                                type="button" 
                                                role="tab"
                                                style="width: 100%; text-align: center; white-space: nowrap;">
                                            등급 랭킹
                                        </button>
                                    </li>
                                @endif
                                @if($enablePointRanking)
                                    <li class="nav-item" role="presentation" style="flex: 1 1 0; min-width: 0;">
                                        <button class="nav-link sidebar-tab-btn {{ !$enableRankRanking ? 'active' : '' }}" 
                                                id="ranking-tab-{{ $widget->id }}-point" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#ranking-{{ $widget->id }}-point" 
                                                type="button" 
                                                role="tab"
                                                style="width: 100%; text-align: center; white-space: nowrap;">
                                            포인트 랭킹
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <style>
                            .sidebar-tab-menu {
                                border: none !important;
                                border-bottom: 1px solid #dee2e6 !important;
                            }
                            .sidebar-tab-menu .nav-item {
                                min-width: 0 !important;
                            }
                            .sidebar-tab-menu .sidebar-tab-btn {
                                width: 100% !important;
                                text-align: center !important;
                            }
                            .sidebar-tab-menu .sidebar-tab-btn {
                                border: none !important;
                                background-color: transparent !important;
                                color: #6c757d !important;
                                padding: 0.5rem 1rem !important;
                                margin-bottom: -1px !important;
                                border-bottom: 2px solid transparent !important;
                                border-radius: 0 !important;
                                height: 39.1875px !important;
                                min-height: 39.1875px !important;
                                max-height: 39.1875px !important;
                                display: flex !important;
                                align-items: center !important;
                                justify-content: center !important;
                                line-height: 1.2 !important;
                            }
                            .sidebar-tab-menu .sidebar-tab-btn.active {
                                border-bottom: 2px solid #0d6efd !important;
                                border-radius: 0 !important;
                                color: #0d6efd !important;
                                font-weight: 600 !important;
                                background-color: transparent !important;
                            }
                            .sidebar-tab-menu .sidebar-tab-btn:not(.active):hover {
                                color: #495057 !important;
                            }
                            .sidebar-tab-wrapper {
                                width: 100%;
                            }
                            .sidebar-tab-wrapper::-webkit-scrollbar {
                                height: 4px;
                            }
                            .sidebar-tab-wrapper::-webkit-scrollbar-track {
                                background: #f1f1f1;
                            }
                            .sidebar-tab-wrapper::-webkit-scrollbar-thumb {
                                background: #888;
                                border-radius: 2px;
                            }
                            .sidebar-tab-wrapper::-webkit-scrollbar-thumb:hover {
                                background: #555;
                            }
                        </style>
                        <div class="tab-content" style="margin-top: 0.75rem;">
                            @if($enableRankRanking)
                                <div class="tab-pane fade show active" id="ranking-{{ $widget->id }}-rank" role="tabpanel">
                                    @php
                                        // 등급 랭킹 데이터 가져오기 (일반 사용자만, 관리자/매니저 제외)
                                        $rankRanking = \App\Models\User::where('site_id', $site->id)
                                            ->where('role', 'user') // 일반 사용자만
                                            ->get()
                                            ->map(function($user) use ($site) {
                                                // 사용자의 등급 가져오기
                                                $userRank = $user->getUserRank($site->id);
                                                $rankOrder = 0; // 가장 낮은 등급으로 초기화
                                                
                                                if ($userRank) {
                                                    // rank 필드를 직접 사용 (rank 값이 클수록 높은 등급: 1등급 < 2등급 < 3등급)
                                                    $rankOrder = $userRank->rank ?? 0;
                                                } else {
                                                    // 등급이 없는 경우 가장 낮은 등급으로 처리
                                                    $rankOrder = 0;
                                                }
                                                
                                                // 포인트 가져오기
                                                $points = $user->points ?? 0;
                                                
                                                return [
                                                    'user' => $user,
                                                    'userRank' => $userRank,
                                                    'points' => $points,
                                                    'rankOrder' => $rankOrder
                                                ];
                                            })
                                            ->sort(function($a, $b) {
                                                // 등급 순서가 높을수록(rank 값이 클수록) 우선 (1 < 2 < 3)
                                                if ($a['rankOrder'] != $b['rankOrder']) {
                                                    return $b['rankOrder'] - $a['rankOrder'];
                                                }
                                                // 동일한 등급이면 포인트가 높은 순
                                                return $b['points'] - $a['points'];
                                            })
                                            ->take($rankingLimit)
                                            ->values()
                                            ->map(function($item, $index) {
                                                return [
                                                    'rank' => $index + 1,
                                                    'user' => $item['user'],
                                                    'userRank' => $item['userRank'],
                                                    'points' => $item['points']
                                                ];
                                            });
                                    @endphp
                                    @if($rankRanking->isEmpty())
                                        <p class="text-muted mb-0">등급 랭킹 데이터가 없습니다.</p>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach($rankRanking as $item)
                                                <li class="mb-2 pb-2 border-bottom d-flex align-items-center">
                                                    @php
                                                        $adminIcon = $site->getSetting('admin_icon_path', '');
                                                        $managerIcon = $site->getSetting('manager_icon_path', '');
                                                        $displayType = $site->getSetting('rank_display_type', 'icon');
                                                        $showRankIcon = $item['rank'] <= 3; // 1등부터 3등까지 아이콘 표시
                                                    @endphp
                                                    @if($showRankIcon)
                                                        @if($item['rank'] == 1)
                                                            <span class="me-2">🥇</span>
                                                        @elseif($item['rank'] == 2)
                                                            <span class="me-2">🥈</span>
                                                        @elseif($item['rank'] == 3)
                                                            <span class="me-2">🥉</span>
                                                        @endif
                                                    @else
                                                        <span class="me-2 fw-bold" style="min-width: 30px;">{{ $item['rank'] }}</span>
                                                    @endif
                                                    @if($item['user']->isAdmin() && $adminIcon)
                                                        <img src="{{ asset('storage/' . $adminIcon) }}" alt="Admin" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                    @elseif($item['user']->isManager() && $managerIcon)
                                                        <img src="{{ asset('storage/' . $managerIcon) }}" alt="Manager" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                    @elseif($item['userRank'])
                                                        @if($displayType === 'icon' && $item['userRank']->icon_path)
                                                            <img src="{{ asset('storage/' . $item['userRank']->icon_path) }}" alt="{{ $item['userRank']->name }}" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                        @elseif($displayType === 'color' && $item['userRank']->color)
                                                            <span style="color: {{ $item['userRank']->color }}; font-weight: bold; margin-right: 8px;">{{ $item['userRank']->name }}</span>
                                                        @endif
                                                    @endif
                                                    <span>{{ $item['user']->nickname ?? $item['user']->name ?? '익명' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif
                            @if($enablePointRanking)
                                <div class="tab-pane fade {{ !$enableRankRanking ? 'show active' : '' }}" id="ranking-{{ $widget->id }}-point" role="tabpanel">
                                    @php
                                        // 포인트 랭킹 데이터 가져오기 (모든 사용자 포함)
                                        $pointRanking = \App\Models\User::where('site_id', $site->id)
                                            ->orderBy('points', 'desc')
                                            ->limit($rankingLimit)
                                            ->get()
                                            ->map(function($user, $index) use ($site) {
                                                // 모든 사용자의 등급 가져오기 (관리자/매니저 포함)
                                                $userRank = null;
                                                if (!$user->isAdmin() && !$user->isManager()) {
                                                    $userRank = $user->getUserRank($site->id);
                                                }
                                                return [
                                                    'rank' => $index + 1,
                                                    'user' => $user,
                                                    'userRank' => $userRank,
                                                    'points' => $user->points ?? 0
                                                ];
                                            });
                                    @endphp
                                    @if($pointRanking->isEmpty())
                                        <p class="text-muted mb-0">포인트 랭킹 데이터가 없습니다.</p>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach($pointRanking as $item)
                                                <li class="mb-2 pb-2 border-bottom d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        @php
                                                            $adminIcon = $site->getSetting('admin_icon_path', '');
                                                            $managerIcon = $site->getSetting('manager_icon_path', '');
                                                            $displayType = $site->getSetting('rank_display_type', 'icon');
                                                            $showRankIcon = $item['rank'] <= 3; // 1등부터 3등까지 아이콘 표시
                                                        @endphp
                                                        @if($showRankIcon)
                                                            @if($item['rank'] == 1)
                                                                <span class="me-2">🥇</span>
                                                            @elseif($item['rank'] == 2)
                                                                <span class="me-2">🥈</span>
                                                            @elseif($item['rank'] == 3)
                                                                <span class="me-2">🥉</span>
                                                            @endif
                                                        @else
                                                            <span class="me-2 fw-bold" style="min-width: 30px;">{{ $item['rank'] }}</span>
                                                        @endif
                                                        @if($item['user']->isAdmin() && $adminIcon)
                                                            <img src="{{ asset('storage/' . $adminIcon) }}" alt="Admin" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                        @elseif($item['user']->isManager() && $managerIcon)
                                                            <img src="{{ asset('storage/' . $managerIcon) }}" alt="Manager" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                        @elseif($item['userRank'])
                                                            @if($displayType === 'icon' && $item['userRank']->icon_path)
                                                                <img src="{{ asset('storage/' . $item['userRank']->icon_path) }}" alt="{{ $item['userRank']->name }}" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                            @elseif($displayType === 'color' && $item['userRank']->color)
                                                                <span style="color: {{ $item['userRank']->color }}; font-weight: bold; margin-right: 8px;">{{ $item['userRank']->name }}</span>
                                                            @endif
                                                        @endif
                                                        <span>{{ $item['user']->nickname ?? $item['user']->name ?? '익명' }}</span>
                                                    </div>
                                                    <span class="text-muted">{{ number_format($item['points']) }}P</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- 탭 없이 바로 표시 --}}
                        @if($enableRankRanking)
                            @php
                                // 등급 랭킹 데이터 가져오기 (일반 사용자만, 관리자/매니저 제외)
                                $rankRanking = \App\Models\User::where('site_id', $site->id)
                                    ->where('role', 'user') // 일반 사용자만
                                    ->get()
                                    ->map(function($user) use ($site) {
                                        // 사용자의 등급 가져오기
                                        $userRank = $user->getUserRank($site->id);
                                        $rankOrder = 0; // 가장 낮은 등급으로 초기화
                                        
                                        if ($userRank) {
                                            // rank 필드를 직접 사용 (rank 값이 클수록 높은 등급: 1등급 < 2등급 < 3등급)
                                            $rankOrder = $userRank->rank ?? 0;
                                        } else {
                                            // 등급이 없는 경우 가장 낮은 등급으로 처리
                                            $rankOrder = 0;
                                        }
                                        
                                        // 포인트 가져오기
                                        $points = $user->points ?? 0;
                                        
                                        return [
                                            'user' => $user,
                                            'userRank' => $userRank,
                                            'points' => $points,
                                            'rankOrder' => $rankOrder
                                        ];
                                    })
                                    ->sort(function($a, $b) {
                                        // 등급 순서가 높을수록(rank 값이 클수록) 우선 (1 < 2 < 3)
                                        if ($a['rankOrder'] != $b['rankOrder']) {
                                            return $b['rankOrder'] - $a['rankOrder'];
                                        }
                                        // 동일한 등급이면 포인트가 높은 순
                                        return $b['points'] - $a['points'];
                                    })
                                    ->take($rankingLimit)
                                    ->values()
                                    ->map(function($item, $index) {
                                        return [
                                            'rank' => $index + 1,
                                            'user' => $item['user'],
                                            'userRank' => $item['userRank'],
                                            'points' => $item['points']
                                        ];
                                    });
                            @endphp
                            @if($rankRanking->isEmpty())
                                <p class="text-muted mb-0">등급 랭킹 데이터가 없습니다.</p>
                            @else
                                <ul class="list-unstyled mb-0">
                                    @foreach($rankRanking as $item)
                                        <li class="mb-2 pb-2 border-bottom d-flex align-items-center">
                                            @php
                                                $adminIcon = $site->getSetting('admin_icon_path', '');
                                                $managerIcon = $site->getSetting('manager_icon_path', '');
                                                $displayType = $site->getSetting('rank_display_type', 'icon');
                                                $showRankIcon = $item['rank'] <= 3; // 1등부터 3등까지 아이콘 표시
                                            @endphp
                                            @if($showRankIcon)
                                                @if($item['rank'] == 1)
                                                    <span class="me-2">🥇</span>
                                                @elseif($item['rank'] == 2)
                                                    <span class="me-2">🥈</span>
                                                @elseif($item['rank'] == 3)
                                                    <span class="me-2">🥉</span>
                                                @endif
                                            @else
                                                <span class="me-2 fw-bold" style="min-width: 30px;">{{ $item['rank'] }}</span>
                                            @endif
                                            @if($item['user']->isAdmin() && $adminIcon)
                                                <img src="{{ asset('storage/' . $adminIcon) }}" alt="Admin" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                            @elseif($item['user']->isManager() && $managerIcon)
                                                <img src="{{ asset('storage/' . $managerIcon) }}" alt="Manager" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                            @elseif($item['userRank'])
                                                @if($displayType === 'icon' && $item['userRank']->icon_path)
                                                    <img src="{{ asset('storage/' . $item['userRank']->icon_path) }}" alt="{{ $item['userRank']->name }}" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                @elseif($displayType === 'color' && $item['userRank']->color)
                                                    <span style="color: {{ $item['userRank']->color }}; font-weight: bold; margin-right: 8px;">{{ $item['userRank']->name }}</span>
                                                @endif
                                            @endif
                                            <span>{{ $item['user']->nickname ?? $item['user']->name ?? '익명' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @elseif($enablePointRanking)
                            @php
                                // 포인트 랭킹 데이터 가져오기 (모든 사용자 포함)
                                $pointRanking = \App\Models\User::where('site_id', $site->id)
                                    ->orderBy('points', 'desc')
                                    ->limit($rankingLimit)
                                    ->get()
                                    ->map(function($user, $index) use ($site) {
                                        // 모든 사용자의 등급 가져오기 (관리자/매니저 포함)
                                        $userRank = null;
                                        if (!$user->isAdmin() && !$user->isManager()) {
                                            $userRank = $user->getUserRank($site->id);
                                        }
                                        return [
                                            'rank' => $index + 1,
                                            'user' => $user,
                                            'userRank' => $userRank,
                                            'points' => $user->points ?? 0
                                        ];
                                    });
                            @endphp
                            @if($pointRanking->isEmpty())
                                <p class="text-muted mb-0">포인트 랭킹 데이터가 없습니다.</p>
                            @else
                                <ul class="list-unstyled mb-0">
                                    @foreach($pointRanking as $item)
                                        <li class="mb-2 pb-2 border-bottom d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $adminIcon = $site->getSetting('admin_icon_path', '');
                                                    $managerIcon = $site->getSetting('manager_icon_path', '');
                                                    $displayType = $site->getSetting('rank_display_type', 'icon');
                                                    $showRankIcon = $item['rank'] <= 3; // 1등부터 3등까지 아이콘 표시
                                                @endphp
                                                @if($showRankIcon)
                                                    @if($item['rank'] == 1)
                                                        <span class="me-2">🥇</span>
                                                    @elseif($item['rank'] == 2)
                                                        <span class="me-2">🥈</span>
                                                    @elseif($item['rank'] == 3)
                                                        <span class="me-2">🥉</span>
                                                    @endif
                                                @else
                                                    <span class="me-2 fw-bold" style="min-width: 30px;">{{ $item['rank'] }}</span>
                                                @endif
                                                @if($item['user']->isAdmin() && $adminIcon)
                                                    <img src="{{ asset('storage/' . $adminIcon) }}" alt="Admin" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                @elseif($item['user']->isManager() && $managerIcon)
                                                    <img src="{{ asset('storage/' . $managerIcon) }}" alt="Manager" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                @elseif($item['userRank'])
                                                    @if($displayType === 'icon' && $item['userRank']->icon_path)
                                                        <img src="{{ asset('storage/' . $item['userRank']->icon_path) }}" alt="{{ $item['userRank']->name }}" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                                    @elseif($displayType === 'color' && $item['userRank']->color)
                                                        <span style="color: {{ $item['userRank']->color }}; font-weight: bold; margin-right: 8px;">{{ $item['userRank']->name }}</span>
                                                    @endif
                                                @endif
                                                <span>{{ $item['user']->nickname ?? $item['user']->name ?? '익명' }}</span>
                                            </div>
                                            <span class="text-muted">{{ number_format($item['points']) }}P</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                    @endif
                @endif
                @break

            @case('block')
                @php
                    $blockSettings = $widgetSettings;
                    $blockTitle = $blockSettings['block_title'] ?? '';
                    $blockContent = $blockSettings['block_content'] ?? '';
                    $textAlign = $blockSettings['text_align'] ?? 'left';
                    $backgroundType = $blockSettings['background_type'] ?? 'color';
                    $backgroundColor = $blockSettings['background_color'] ?? '#007bff';
                    $backgroundImageUrl = $blockSettings['background_image_url'] ?? '';
                    $backgroundImageFullWidth = $blockSettings['background_image_full_width'] ?? false;
                    $paddingTop = $blockSettings['padding_top'] ?? 20;
                    $paddingLeft = $blockSettings['padding_left'] ?? 20;
                    $link = $blockSettings['link'] ?? '';
                    // 제목/내용 컬러 분리 (하위 호환성: font_color도 지원)
                    $titleColor = $blockSettings['title_color'] ?? $blockSettings['font_color'] ?? '#ffffff';
                    $contentColor = $blockSettings['content_color'] ?? $blockSettings['font_color'] ?? '#ffffff';
                    $titleFontSize = $blockSettings['title_font_size'] ?? '16';
                    $contentFontSize = $blockSettings['content_font_size'] ?? '14';
                    // 반응형 폰트 사이즈 계산
                    $responsiveTitleFontSize = "clamp(" . round($titleFontSize * 0.65) . "px, " . round($titleFontSize / 8, 1) . "vw, " . $titleFontSize . "px)";
                    $responsiveContentFontSize = "clamp(" . round($contentFontSize * 0.65) . "px, " . round($contentFontSize / 8, 1) . "vw, " . $contentFontSize . "px)";
                    $showButton = $blockSettings['show_button'] ?? false;
                    $buttonText = $blockSettings['button_text'] ?? '';
                    $buttonBackgroundColor = $blockSettings['button_background_color'] ?? '#007bff';
        $buttonTextColor = $blockSettings['button_text_color'] ?? '#ffffff';
                    
                    // 스타일 생성
                    $blockStyle = "padding-top: {$paddingTop}px; padding-bottom: {$paddingTop}px; padding-left: {$paddingLeft}px; padding-right: {$paddingLeft}px; text-align: {$textAlign};";
                    
                    if ($backgroundType === 'color') {
                        $blockStyle .= " background-color: {$backgroundColor};";
                    } else if ($backgroundType === 'gradient') {
                        $gradientStart = $blockSettings['background_gradient_start'] ?? '#ffffff';
                        $gradientEnd = $blockSettings['background_gradient_end'] ?? '#000000';
                        $gradientAngle = $blockSettings['background_gradient_angle'] ?? 90;
                        $blockStyle .= " background: linear-gradient({$gradientAngle}deg, {$gradientStart}, {$gradientEnd});";
                    } else if ($backgroundType === 'image' && $backgroundImageUrl) {
                        $bgSize = $backgroundImageFullWidth ? '100% auto' : 'cover';
                        $blockStyle .= " background-image: url('{$backgroundImageUrl}'); background-size: {$bgSize}; background-position: center top; background-repeat: no-repeat;";
                    }
                    
                    // 세로 100%일 때 위젯이 전체 높이를 차지하도록
                    // 컨테이너 정렬에 따라 justify-content 설정 (isFullHeight 여부와 관계없이)
                    $justifyContent = 'center';
                    if ($verticalAlign === 'top') {
                        $justifyContent = 'flex-start';
                    } elseif ($verticalAlign === 'bottom') {
                        $justifyContent = 'flex-end';
                    }
                    if ($isFullHeight) {
                        $blockStyle .= " flex: 1; min-height: 0; display: flex; flex-direction: column; justify-content: {$justifyContent};";
                    } else {
                        // isFullHeight가 아니어도 컨테이너 정렬에 따라 justify-content 적용
                        $blockStyle .= " display: flex; flex-direction: column; justify-content: {$justifyContent};";
                    }
                @endphp
                <div style="{{ $blockStyle }}">
                    @php
                        $openNewTab = $blockSettings['open_new_tab'] ?? false;
                    @endphp
                    @if($link && !$showButton)
                        <a href="{{ $link }}" style="text-decoration: none; display: block;"
                           @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
                    @endif
                    @if($blockTitle)
                        <h4 class="mb-2" style="color: {{ $titleColor }}; font-weight: bold; font-size: {{ $responsiveTitleFontSize }};">{!! nl2br(e($blockTitle)) !!}</h4>
                    @endif
                    @if($blockContent)
                        <p class="mb-0" style="color: {{ $contentColor }}; font-size: {{ $responsiveContentFontSize }}; white-space: pre-wrap;">{{ $blockContent }}</p>
                    @endif
                    @if($link && !$showButton)
                        </a>
                    @endif
                    @if($showButton && $buttonText)
                        @php
                            // 버튼 border-radius 설정 (원래 테마가 라운드인 경우 라운드 적용)
                            $buttonBorderRadius = $isOriginalRoundTheme ? '0.5rem' : '4px';
                        @endphp
                        <div class="mt-3" style="text-align: {{ $textAlign }};">
                            @if($link)
                                <a href="{{ $link }}" 
                                   @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                   style="text-decoration: none; display: inline-block;">
                                    <button class="block-widget-button" 
                                            style="border: 2px solid {{ $buttonColor }}; color: {{ $buttonColor }}; background-color: transparent; padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                            onmouseover="this.style.backgroundColor='{{ $buttonColor }}'; this.style.color='#ffffff';"
                                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $buttonColor }}';">
                                        {{ $buttonText }}
                                    </button>
                                </a>
                            @else
                                <button class="block-widget-button" 
                                        style="border: 2px solid {{ $buttonColor }}; color: {{ $buttonColor }}; background-color: transparent; padding: 8px 20px; border-radius: {{ $buttonBorderRadius }}; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                        onmouseover="this.style.backgroundColor='{{ $buttonColor }}'; this.style.color='#ffffff';"
                                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $buttonColor }}';">
                                    {{ $buttonText }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
                @break

            @case('contact_form')
                @php
                    $contactFormId = $widgetSettings['contact_form_id'] ?? null;
                    $contactForm = $contactFormId ? \App\Models\ContactForm::find($contactFormId) : null;
                @endphp
                @if($contactForm && $contactForm->site_id === $site->id)
                    <form id="contactForm_{{ $contactForm->id }}" class="contact-form-widget" data-contact-form-id="{{ $contactForm->id }}">
                        @csrf
                        @foreach($contactForm->fields as $field)
                            <div class="mb-3">
                                @php
                                    $fieldName = $field['name'] ?? $field['label'] ?? '';
                                    $fieldLabel = $field['label'] ?? $field['name'] ?? '';
                                @endphp
                                <label class="form-label">{{ $fieldLabel }}</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="{{ $fieldName }}" 
                                       placeholder="{{ $field['placeholder'] ?? '' }}"
                                       required>
                            </div>
                        @endforeach
                        @if($contactForm->checkboxes && isset($contactForm->checkboxes['enabled']) && $contactForm->checkboxes['enabled'])
                            <div class="mb-3">
                                <label class="form-label">체크 항목</label>
                                @if(isset($contactForm->checkboxes['items']) && is_array($contactForm->checkboxes['items']))
                                    @foreach($contactForm->checkboxes['items'] as $index => $item)
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="{{ isset($contactForm->checkboxes['allow_multiple']) && $contactForm->checkboxes['allow_multiple'] ? 'checkbox' : 'radio' }}" 
                                                   name="checkboxes[]" 
                                                   id="checkbox_{{ $contactForm->id }}_{{ $index }}" 
                                                   value="{{ $item['label'] ?? '' }}"
                                                   @if(!isset($contactForm->checkboxes['allow_multiple']) || !$contactForm->checkboxes['allow_multiple'])
                                                       required
                                                   @endif>
                                            <label class="form-check-label" for="checkbox_{{ $contactForm->id }}_{{ $index }}">
                                                {{ $item['label'] ?? '' }}
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        @endif
                        @if($contactForm->has_inquiry_content)
                            <div class="mb-3">
                                <label class="form-label">문의 내용</label>
                                <textarea class="form-control" 
                                          name="문의내용" 
                                          rows="5"></textarea>
                            </div>
                        @endif
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100">
                                {{ $contactForm->button_text }}
                            </button>
                        </div>
                    </form>
                    @push('scripts')
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const form = document.getElementById('contactForm_{{ $contactForm->id }}');
                        if (form) {
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();
                                const formData = new FormData(form);
                                
                                // 체크박스 데이터 수집
                                @if($contactForm->checkboxes && isset($contactForm->checkboxes['enabled']) && $contactForm->checkboxes['enabled'])
                                    const checkboxes = form.querySelectorAll('input[name="checkboxes[]"]:checked');
                                    const checkboxValues = Array.from(checkboxes).map(cb => cb.value);
                                    // 기존 checkboxes[] 항목 제거 후 새로 추가
                                    formData.delete('checkboxes[]');
                                    checkboxValues.forEach(value => {
                                        formData.append('checkboxes[]', value);
                                    });
                                @endif
                                
                                const submitBtn = form.querySelector('button[type="submit"]');
                                const originalBtnText = submitBtn.textContent;
                                
                                submitBtn.disabled = true;
                                submitBtn.textContent = '전송 중...';
                                
                                fetch('{{ route("contact-forms.submit", ["site" => $site->slug, "contactForm" => $contactForm->id]) }}', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        return response.json().then(err => {
                                            throw new Error(JSON.stringify(err));
                                        });
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (data.success) {
                                        alert(data.message || '신청이 완료되었습니다.');
                                        form.reset();
                                    } else {
                                        alert('오류: ' + (data.message || '알 수 없는 오류'));
                                    }
                                    submitBtn.disabled = false;
                                    submitBtn.textContent = originalBtnText;
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    const errorData = JSON.parse(error.message || '{}');
                                    alert('오류: ' + (errorData.message || error.message || '알 수 없는 오류'));
                                    if (errorData.debug) {
                                        console.log('Debug info:', errorData.debug);
                                    }
                                    submitBtn.disabled = false;
                                    submitBtn.textContent = originalBtnText;
                                });
                            });
                        }
                    });
                    </script>
                    @endpush
                @else
                    <p class="text-muted mb-0">컨텍트폼을 찾을 수 없습니다.</p>
                @endif
                @break

            @case('map')
                @php
                    $mapId = $widgetSettings['map_id'] ?? null;
                    $map = $mapId ? \App\Models\Map::find($mapId) : null;
                    // 마스터 콘솔에서 설정한 지도 API 키 가져오기
                    $masterSite = \App\Models\Site::getMasterSite();
                    $googleApiKey = $masterSite ? \Illuminate\Support\Facades\DB::table('site_settings')
                        ->where('site_id', $masterSite->id)
                        ->where('key', 'map_api_google_key')
                        ->value('value') : null;
                    $naverApiKey = $masterSite ? \Illuminate\Support\Facades\DB::table('site_settings')
                        ->where('site_id', $masterSite->id)
                        ->where('key', 'map_api_naver_key')
                        ->value('value') : null;
                    $kakaoApiKey = $masterSite ? \Illuminate\Support\Facades\DB::table('site_settings')
                        ->where('site_id', $masterSite->id)
                        ->where('key', 'map_api_kakao_key')
                        ->value('value') : null;
                @endphp
                @if($map && $map->site_id === $site->id)
                    <div class="map-widget-container" style="display: flex; flex-direction: column; height: 100%;">
                        <div class="map-widget" style="width: 100%; flex: 1; min-height: 300px; border: 1px solid #dee2e6; border-radius: 0.375rem; overflow: hidden; display: flex; flex-direction: column;">
                            @if($map->map_type === 'google' && !empty($googleApiKey))
                                <div id="google-map-{{ $map->id }}" style="width: 100%; flex: 1; min-height: 300px;"></div>
                                @push('scripts')
                                <script>
                                // 구글 지도 API가 로드되기 전에 함수를 window 객체에 등록
                                window.initGoogleMap{{ $map->id }} = function() {
                                    const container = document.getElementById('google-map-{{ $map->id }}');
                                    if (!container) {
                                        console.error('Google map container not found: google-map-{{ $map->id }}');
                                        return;
                                    }
                                    
                                    @if($map->latitude && $map->longitude)
                                        const position = { lat: {{ $map->latitude }}, lng: {{ $map->longitude }} };
                                    @else
                                        const position = null;
                                    @endif
                                    
                                    // 다크모드 스타일 정의
                                    const darkModeStyles = [
                                        { elementType: "geometry", stylers: [{ color: "#242f3e" }] },
                                        { elementType: "labels.text.stroke", stylers: [{ color: "#242f3e" }] },
                                        { elementType: "labels.text.fill", stylers: [{ color: "#746855" }] },
                                        { featureType: "administrative.locality", elementType: "labels.text.fill", stylers: [{ color: "#d59563" }] },
                                        { featureType: "poi", elementType: "labels.text.fill", stylers: [{ color: "#d59563" }] },
                                        { featureType: "poi.park", elementType: "geometry", stylers: [{ color: "#263c3f" }] },
                                        { featureType: "poi.park", elementType: "labels.text.fill", stylers: [{ color: "#6b9a76" }] },
                                        { featureType: "road", elementType: "geometry", stylers: [{ color: "#38414e" }] },
                                        { featureType: "road", elementType: "geometry.stroke", stylers: [{ color: "#212a37" }] },
                                        { featureType: "road", elementType: "labels.text.fill", stylers: [{ color: "#9ca5b3" }] },
                                        { featureType: "road.highway", elementType: "geometry", stylers: [{ color: "#746855" }] },
                                        { featureType: "road.highway", elementType: "geometry.stroke", stylers: [{ color: "#1f2835" }] },
                                        { featureType: "road.highway", elementType: "labels.text.fill", stylers: [{ color: "#f3d19c" }] },
                                        { featureType: "transit", elementType: "geometry", stylers: [{ color: "#2f3948" }] },
                                        { featureType: "transit.station", elementType: "labels.text.fill", stylers: [{ color: "#d59563" }] },
                                        { featureType: "water", elementType: "geometry", stylers: [{ color: "#17263c" }] },
                                        { featureType: "water", elementType: "labels.text.fill", stylers: [{ color: "#515c6d" }] },
                                        { featureType: "water", elementType: "labels.text.stroke", stylers: [{ color: "#17263c" }] }
                                    ];
                                    
                                    const isDarkMode = {{ $isDark ?? false ? 'true' : 'false' }};
                                    
                                    const mapOptions = {
                                        zoom: {{ $map->zoom ?? 15 }},
                                        center: position || { lat: 37.5665, lng: 126.9780 },
                                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                                        styles: isDarkMode ? darkModeStyles : []
                                    };
                                    
                                    const map = new google.maps.Map(container, mapOptions);
                                    
                                    // 정보창 스타일 (다크모드 대응)
                                    const infoWindowStyle = isDarkMode 
                                        ? 'padding: 10px; min-width: 200px; background: #1e1e1e; color: #ffffff;'
                                        : 'padding: 10px; min-width: 200px;';
                                    
                                    @if($map->latitude && $map->longitude)
                                        // 좌표가 있는 경우 마커 표시
                                        const marker = new google.maps.Marker({
                                            position: position,
                                            map: map,
                                            title: '{{ addslashes($map->name) }}',
                                            animation: google.maps.Animation.DROP,
                                            draggable: false
                                        });
                                        
                                        const infoWindow = new google.maps.InfoWindow({
                                            content: '<div style="' + infoWindowStyle + '"><strong>{{ addslashes($map->name) }}</strong><br>{{ addslashes($map->address) }}</div>'
                                        });
                                        
                                        // 마커 클릭 시 정보창 표시
                                        marker.addListener('click', function() {
                                            infoWindow.open(map, marker);
                                        });
                                        
                                        // 지도 로드 시 정보창 자동 표시
                                        infoWindow.open(map, marker);
                                    @else
                                        // 주소만 있는 경우 지오코딩 후 마커 표시
                                        const geocoder = new google.maps.Geocoder();
                                        geocoder.geocode({ 
                                            address: '{{ addslashes($map->address) }}',
                                            language: 'ko'
                                        }, function(results, status) {
                                            if (status === 'OK' && results[0]) {
                                                map.setCenter(results[0].geometry.location);
                                                const marker = new google.maps.Marker({
                                                    map: map,
                                                    position: results[0].geometry.location,
                                                    title: '{{ addslashes($map->name) }}',
                                                    animation: google.maps.Animation.DROP,
                                                    draggable: false
                                                });
                                                
                                                const infoWindow = new google.maps.InfoWindow({
                                                    content: '<div style="' + infoWindowStyle + '"><strong>{{ addslashes($map->name) }}</strong><br>{{ addslashes($map->address) }}</div>'
                                                });
                                                
                                                marker.addListener('click', function() {
                                                    infoWindow.open(map, marker);
                                                });
                                                
                                                // 지도 로드 시 정보창 자동 표시
                                                infoWindow.open(map, marker);
                                            } else {
                                                console.error('Geocoding failed:', status);
                                            }
                                        });
                                    @endif
                                };
                                
                                // 구글 지도 API 스크립트 동적 로드
                                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                                    const script = document.createElement('script');
                                    script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $googleApiKey }}&callback=initGoogleMap{{ $map->id }}&language=ko';
                                    script.async = true;
                                    script.defer = true;
                                    document.head.appendChild(script);
                                } else {
                                    // 이미 로드된 경우 즉시 실행
                                    window.initGoogleMap{{ $map->id }}();
                                }
                                </script>
                                @endpush
                        @elseif($map->map_type === 'kakao' && !empty($kakaoApiKey))
                            <div id="kakao-map-{{ $map->id }}" style="width: 100%; flex: 1; min-height: 300px;"></div>
                            @push('scripts')
                            <script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey={{ $kakaoApiKey }}"></script>
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const container = document.getElementById('kakao-map-{{ $map->id }}');
                                if (container && typeof kakao !== 'undefined') {
                                    const options = {
                                        center: new kakao.maps.LatLng({{ $map->latitude ?? 37.5665 }}, {{ $map->longitude ?? 126.9780 }}),
                                        level: {{ $map->zoom ?? 15 }}
                                    };
                                    const map = new kakao.maps.Map(container, options);
                                    
                                    @if($map->latitude && $map->longitude)
                                        const marker = new kakao.maps.Marker({
                                            position: new kakao.maps.LatLng({{ $map->latitude }}, {{ $map->longitude }})
                                        });
                                        marker.setMap(map);
                                    @else
                                        const geocoder = new kakao.maps.services.Geocoder();
                                        geocoder.addressSearch('{{ $map->address }}', function(result, status) {
                                            if (status === kakao.maps.services.Status.OK) {
                                                const coords = new kakao.maps.LatLng(result[0].y, result[0].x);
                                                map.setCenter(coords);
                                                const marker = new kakao.maps.Marker({
                                                    position: coords
                                                });
                                                marker.setMap(map);
                                            }
                                        });
                                    @endif
                                }
                            });
                            </script>
                            @endpush
                        @elseif($map->map_type === 'naver' && !empty($naverApiKey))
                            <div id="naver-map-{{ $map->id }}" style="width: 100%; flex: 1; min-height: 300px;"></div>
                            @push('scripts')
                            <script type="text/javascript" src="https://oapi.map.naver.com/map3.js?v=3.0&ncpClientId={{ $naverApiKey }}"></script>
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const container = document.getElementById('naver-map-{{ $map->id }}');
                                if (container && typeof naver !== 'undefined') {
                                    const mapOptions = {
                                        center: new naver.maps.LatLng({{ $map->latitude ?? 37.5665 }}, {{ $map->longitude ?? 126.9780 }}),
                                        zoom: {{ $map->zoom ?? 15 }}
                                    };
                                    const map = new naver.maps.Map(container, mapOptions);
                                    
                                    @if($map->latitude && $map->longitude)
                                        const marker = new naver.maps.Marker({
                                            position: new naver.maps.LatLng({{ $map->latitude }}, {{ $map->longitude }}),
                                            map: map
                                        });
                                    @else
                                        naver.maps.Service.geocode({
                                            query: '{{ $map->address }}'
                                        }, function(status, response) {
                                            if (status === naver.maps.Service.Status.OK) {
                                                const item = response.result.items[0];
                                                const point = new naver.maps.Point(item.point.x, item.point.y);
                                                const latlng = naver.maps.TransCoord.fromTM128ToLatLng(point);
                                                map.setCenter(latlng);
                                                const marker = new naver.maps.Marker({
                                                    position: latlng,
                                                    map: map
                                                });
                                            }
                                        });
                                    @endif
                                }
                            });
                            </script>
                            @endpush
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                <p class="text-muted mb-0">지도 API 키가 설정되지 않았습니다. 마스터 콘솔에서 API 키를 설정해주세요.</p>
                            </div>
                        @endif
                        </div>
                        {{-- 지도 하단 주소 표시 --}}
                        @if($map->address)
                        <div class="map-address-info" style="padding: 0.75rem 1rem; background-color: #f8f9fa; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 0.375rem 0.375rem; display: block; flex-shrink: 0;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt-fill me-2 text-primary" style="font-size: 1.1rem;"></i>
                                <span style="font-size: 0.95rem; color: #495057; font-weight: 500;">{{ $map->address }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-muted mb-0">지도를 찾을 수 없습니다.</p>
                @endif
                @break

            @case('plans')
                @php
                    // 마스터 사이트에서만 플랜 위젯 표시
                    if (!$site->isMasterSite()) {
                        break;
                    }
                    
                    // 무료 플랜, 유료 플랜, 서버 용량 플랜 분리
                    $freePlans = \App\Models\Plan::where('is_active', true)
                        ->where('billing_type', 'free')
                        ->where('type', '!=', 'server')
                        ->orderBy('sort_order')
                        ->orderBy('price')
                        ->get();
                    
                    $paidPlans = \App\Models\Plan::where('is_active', true)
                        ->whereIn('billing_type', ['one_time', 'monthly'])
                        ->where('type', '!=', 'server')
                        ->orderBy('sort_order')
                        ->orderBy('price')
                        ->get();
                    
                    $serverPlans = \App\Models\Plan::where('is_active', true)
                        ->where('type', 'server')
                        ->orderBy('sort_order')
                        ->orderBy('price')
                        ->get();
                    
                    $planSettings = $widgetSettings;
                    $displayStyle = $planSettings['display_style'] ?? 'grid'; // grid, list
                    $columns = $planSettings['columns'] ?? 3; // 1, 2, 3, 4
                @endphp
                
                @if($freePlans->count() > 0 || $paidPlans->count() > 0 || $serverPlans->count() > 0)
                    <div class="plans-widget mb-3" style="{{ $isRoundTheme ? 'border-radius: 0.5rem; overflow: hidden;' : '' }}">
                        @if($widget->title)
                            <div class="widget-header p-3 bg-light" style="{{ $widgetTopBorderStyle }} {{ $isRoundTheme ? 'border-radius: 0.5rem 0.5rem 0 0;' : '' }}">
                                <h5 class="mb-0 fw-bold">{{ $widget->title }}</h5>
                            </div>
                        @endif
                        
                        <div class="widget-body p-3">
                            @if($displayStyle === 'grid')
                                {{-- 무료 플랜 섹션 --}}
                                @if($freePlans->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-3 fw-bold"><i class="bi bi-gift me-2"></i>무료 플랜</h6>
                                        <div class="row g-3">
                                            @foreach($freePlans as $plan)
                                        <div class="col-md-{{ 12 / $columns }} col-sm-6">
                                            <div class="card h-100 {{ $shadowClass }}" style="{{ $isRoundTheme ? 'border-radius: 0.5rem;' : '' }}">
                                                <div class="card-body d-flex flex-column">
                                                    <h5 class="card-title">{{ $plan->name }}</h5>
                                                    <p class="card-text text-muted small mb-3">{{ $plan->description }}</p>
                                                    <div class="mt-auto">
                                                        <div class="mb-3">
                                                            @if($plan->billing_type === 'free')
                                                                <span class="h4 fw-bold text-success">무료</span>
                                                            @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                <span class="h4 fw-bold text-primary">{{ number_format($plan->one_time_price) }}</span>
                                                                <span class="text-muted small">원 (1회)</span>
                                                            @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                <span class="h4 fw-bold text-primary">{{ number_format($plan->price) }}</span>
                                                                <span class="text-muted">원/월</span>
                                                            @else
                                                                <span class="h4 fw-bold text-success">무료</span>
                                                            @endif
                                                        </div>
                                                        <div class="d-grid gap-2">
                                                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                                자세히 보기
                                                            </button>
                                                            @if($plan->billing_type === 'free')
                                                                <a href="{{ route('payment.subscribe', ['plan' => $plan->slug]) }}" class="btn btn-success btn-sm">
                                                                    시작하기
                                                                </a>
                                                            @else
                                                                <a href="{{ route('payment.subscribe', ['plan' => $plan->slug]) }}" class="btn btn-primary btn-sm">
                                                                    구매하기
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- 플랜 상세 모달 --}}
                                        <div class="modal fade" id="planModal{{ $plan->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ $plan->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-3">{{ $plan->description }}</p>
                                                        <div class="mb-3">
                                                            <h6>가격</h6>
                                                            <p class="h4 text-primary">
                                                                @if($plan->billing_type === 'free')
                                                                    <span class="text-success">무료</span>
                                                                @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                    {{ number_format($plan->one_time_price) }}원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>
                                                                @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                    {{ number_format($plan->price) }}원/월
                                                                @else
                                                                    <span class="text-success">무료</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        @if($plan->features)
                                                            <div class="mb-3">
                                                                <h6>포함된 기능</h6>
                                                                <ul class="list-unstyled">
                                                                    @if(isset($plan->features['main_features']))
                                                                        @foreach($plan->features['main_features'] as $feature)
                                                                            <li><i class="bi bi-check-circle text-success me-2"></i>{{ $feature }}</li>
                                                                        @endforeach
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif
                                                        @if($plan->limits || $plan->traffic_limit_mb)
                                                            <div class="mb-3">
                                                                <h6>제한 사항</h6>
                                                                <ul class="list-unstyled">
                                                                    @if($plan->traffic_limit_mb)
                                                                        <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                                                    @endif
                                                                    @if(isset($plan->limits['storage']))
                                                                        <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB' }}</li>
                                                                    @endif
                                                                    @if($plan->limits)
                                                                        @php
                                                                            $limitLabels = [
                                                                                'boards' => '게시판 수',
                                                                                'widgets' => '위젯 수',
                                                                                'custom_pages' => '커스텀 페이지 수',
                                                                                'users' => '사용자 수',
                                                                            ];
                                                                        @endphp
                                                                        @foreach($plan->limits as $key => $limit)
                                                                            @if($key !== 'storage' && isset($limitLabels[$key]))
                                                                                <li><i class="bi bi-info-circle text-info me-2"></i>{{ $limitLabels[$key] }}: {{ $limit === null || $limit === '-' ? '무제한' : number_format($limit) }}</li>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                        @if($plan->billing_type === 'free')
                                                            <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-success">시작하기</a>
                                                        @else
                                                            <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-primary">구매하기</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- 유료 플랜 섹션 --}}
                                @if($paidPlans->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-3 fw-bold"><i class="bi bi-credit-card me-2"></i>유료 플랜</h6>
                                        <div class="row g-3">
                                            @foreach($paidPlans as $plan)
                                                <div class="col-md-{{ 12 / $columns }} col-sm-6">
                                                    <div class="card h-100 {{ $shadowClass }}" style="{{ $isRoundTheme ? 'border-radius: 0.5rem;' : '' }}">
                                                        <div class="card-body d-flex flex-column">
                                                            <h5 class="card-title">{{ $plan->name }}</h5>
                                                            <p class="card-text text-muted small mb-3">{{ $plan->description }}</p>
                                                            <div class="mt-auto">
                                                                <div class="mb-3">
                                                                    @if($plan->billing_type === 'free')
                                                                        <span class="h4 fw-bold text-success">무료</span>
                                                                    @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                        <span class="h4 fw-bold text-primary">{{ number_format($plan->one_time_price) }}</span>
                                                                        <span class="text-muted small">원 (1회)</span>
                                                                    @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                        <span class="h4 fw-bold text-primary">{{ number_format($plan->price) }}</span>
                                                                        <span class="text-muted">원/월</span>
                                                                    @else
                                                                        <span class="h4 fw-bold text-success">무료</span>
                                                                    @endif
                                                                </div>
                                                                <div class="d-grid gap-2">
                                                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                                        자세히 보기
                                                                    </button>
                                                                    <a href="{{ route('payment.subscribe', ['plan' => $plan->slug]) }}" class="btn btn-primary btn-sm">
                                                                        구매하기
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {{-- 플랜 상세 모달 --}}
                                                <div class="modal fade" id="planModal{{ $plan->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ $plan->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="mb-3">{{ $plan->description }}</p>
                                                                <div class="mb-3">
                                                                    <h6>가격</h6>
                                                                    <p class="h4 text-primary">
                                                                        @if($plan->billing_type === 'free')
                                                                            <span class="text-success">무료</span>
                                                                        @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                            {{ number_format($plan->one_time_price) }}원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>
                                                                        @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                            {{ number_format($plan->price) }}원/월
                                                                        @else
                                                                            <span class="text-success">무료</span>
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                                @if($plan->features)
                                                                    @php
                                                                        $mainFeatures = [
                                                                            'dashboard' => '대시보드',
                                                                            'users' => '사용자 관리',
                                                                            'registration_settings' => '회원가입 설정',
                                                                            'mail_settings' => '메일 설정',
                                                                            'contact_forms' => '컨텍트 폼',
                                                                            'maps' => '지도',
                                                                            'crawlers' => '크롤러',
                                                                            'user_ranks' => '회원등급',
                                                                            'boards' => '게시판 관리',
                                                                            'posts' => '게시글 관리',
                                                                            'attendance' => '출석',
                                                                            'point_exchange' => '포인트 교환',
                                                                            'event_application' => '신청형 이벤트',
                                                                            'menus' => '메뉴 설정',
                                                                            'messages' => '쪽지 관리',
                                                                            'banners' => '배너',
                                                                            'popups' => '팝업',
                                                                            'blocked_ips' => '아이피 차단',
                                                                            'custom_code' => '코드 커스텀',
                                                                            'settings' => '사이트 설정',
                                                                            'sidebar_widgets' => '사이드 위젯',
                                                                            'main_widgets' => '메인 위젯',
                                                                            'custom_pages' => '커스텀 페이지',
                                                                            'toggle_menus' => '토글 메뉴',
                                                                        ];
                                                                    @endphp
                                                                    <div class="mb-3">
                                                                        <h6>포함된 기능</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if(isset($plan->features['main_features']))
                                                                                @foreach($plan->features['main_features'] as $feature)
                                                                                    <li><i class="bi bi-check-circle text-success me-2"></i>{{ $mainFeatures[$feature] ?? $feature }}</li>
                                                                                @endforeach
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                                @if($plan->limits || $plan->traffic_limit_mb)
                                                                    <div class="mb-3">
                                                                        <h6>제한 사항</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if($plan->traffic_limit_mb)
                                                                                <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                                                            @endif
                                                                            @if(isset($plan->limits['storage']))
                                                                                <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB' }}</li>
                                                                            @endif
                                                                            @if($plan->limits)
                                                                                @php
                                                                                    $limitLabels = [
                                                                                        'boards' => '게시판 수',
                                                                                        'widgets' => '위젯 수',
                                                                                        'custom_pages' => '커스텀 페이지 수',
                                                                                        'users' => '사용자 수',
                                                                                    ];
                                                                                @endphp
                                                                                @foreach($plan->limits as $key => $limit)
                                                                                    @if($key !== 'storage' && isset($limitLabels[$key]))
                                                                                        <li><i class="bi bi-info-circle text-info me-2"></i>{{ $limitLabels[$key] }}: {{ $limit === null || $limit === '-' ? '무제한' : number_format($limit) }}</li>
                                                                                    @endif
                                                                                @endforeach
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                                <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-primary">구매하기</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- 서버 용량 플랜 섹션 --}}
                                @if($serverPlans->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-3 fw-bold"><i class="bi bi-server me-2"></i>서버 용량 플랜</h6>
                                        <div class="row g-3">
                                            @foreach($serverPlans as $plan)
                                                <div class="col-md-{{ 12 / $columns }} col-sm-6">
                                                    <div class="card h-100 {{ $shadowClass }}" style="{{ $isRoundTheme ? 'border-radius: 0.5rem;' : '' }}">
                                                        <div class="card-body d-flex flex-column">
                                                            <h5 class="card-title">{{ $plan->name }}</h5>
                                                            <p class="card-text text-muted small mb-3">{{ $plan->description }}</p>
                                                            <div class="mt-auto">
                                                                <div class="mb-3">
                                                                    @if($plan->billing_type === 'free')
                                                                        <span class="h4 fw-bold text-success">무료</span>
                                                                    @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                        <span class="h4 fw-bold text-primary">{{ number_format($plan->one_time_price) }}</span>
                                                                        <span class="text-muted small">원 (1회)</span>
                                                                    @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                        <span class="h4 fw-bold text-primary">{{ number_format($plan->price) }}</span>
                                                                        <span class="text-muted">원/월</span>
                                                                    @else
                                                                        <span class="h4 fw-bold text-success">무료</span>
                                                                    @endif
                                                                </div>
                                                                <div class="d-grid gap-2">
                                                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                                        자세히 보기
                                                                    </button>
                                                                    <a href="{{ route('payment.subscribe', ['plan' => $plan->slug]) }}" class="btn btn-primary btn-sm">
                                                                        구독하기
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {{-- 플랜 상세 모달 --}}
                                                <div class="modal fade" id="planModal{{ $plan->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ $plan->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="mb-3">{{ $plan->description }}</p>
                                                                <div class="mb-3">
                                                                    <h6>가격</h6>
                                                                    <p class="h4 text-primary">
                                                                        @if($plan->billing_type === 'free')
                                                                            <span class="text-success">무료</span>
                                                                        @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                            {{ number_format($plan->one_time_price) }}원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>
                                                                        @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                            {{ number_format($plan->price) }}원/월
                                                                        @else
                                                                            <span class="text-success">무료</span>
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                                @if($plan->limits || $plan->traffic_limit_mb)
                                                                    <div class="mb-3">
                                                                        <h6>제한 사항</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if($plan->traffic_limit_mb)
                                                                                <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                                                            @endif
                                                                            @if(isset($plan->limits['storage']))
                                                                                <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB' }}</li>
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                                <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-primary">구독하기</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="list-group">
                                    {{-- 무료 플랜 리스트 --}}
                                    @if($freePlans->count() > 0)
                                        <div class="mb-3">
                                            <h6 class="fw-bold"><i class="bi bi-gift me-2"></i>무료 플랜</h6>
                                            @foreach($freePlans as $plan)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">{{ $plan->name }}</h6>
                                                    <p class="mb-1 text-muted small">{{ $plan->description }}</p>
                                                    @if($plan->billing_type === 'free')
                                                        <span class="text-success fw-bold">무료</span>
                                                    @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                        <span class="text-primary fw-bold">{{ number_format($plan->one_time_price) }}원 (1회)</span>
                                                    @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                        <span class="text-primary fw-bold">{{ number_format($plan->price) }}원/월</span>
                                                    @else
                                                        <span class="text-success fw-bold">무료</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <button type="button" class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                        자세히 보기
                                                    </button>
                                                    <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-primary btn-sm">
                                                        구독하기
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- 플랜 상세 모달 (리스트 스타일도 동일) --}}
                                        <div class="modal fade" id="planModal{{ $plan->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ $plan->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-3">{{ $plan->description }}</p>
                                                        <div class="mb-3">
                                                            <h6>가격</h6>
                                                            <p class="h4 text-primary">
                                                                @if($plan->billing_type === 'free')
                                                                    <span class="text-success">무료</span>
                                                                @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                    {{ number_format($plan->one_time_price) }}원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>
                                                                @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                    {{ number_format($plan->price) }}원/월
                                                                @else
                                                                    <span class="text-success">무료</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        @if($plan->features)
                                                            <div class="mb-3">
                                                                <h6>포함된 기능</h6>
                                                                <ul class="list-unstyled">
                                                                    @if(isset($plan->features['main_features']))
                                                                        @foreach($plan->features['main_features'] as $feature)
                                                                            <li><i class="bi bi-check-circle text-success me-2"></i>{{ $feature }}</li>
                                                                        @endforeach
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif
                                                        @if($plan->limits || $plan->traffic_limit_mb)
                                                            <div class="mb-3">
                                                                <h6>제한 사항</h6>
                                                                <ul class="list-unstyled">
                                                                    @if($plan->traffic_limit_mb)
                                                                        <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                                                    @endif
                                                                    @if(isset($plan->limits['storage']))
                                                                        <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB' }}</li>
                                                                    @endif
                                                                    @if($plan->limits)
                                                                        @php
                                                                            $limitLabels = [
                                                                                'boards' => '게시판 수',
                                                                                'widgets' => '위젯 수',
                                                                                'custom_pages' => '커스텀 페이지 수',
                                                                                'users' => '사용자 수',
                                                                            ];
                                                                        @endphp
                                                                        @foreach($plan->limits as $key => $limit)
                                                                            @if($key !== 'storage' && isset($limitLabels[$key]))
                                                                                <li><i class="bi bi-info-circle text-info me-2"></i>{{ $limitLabels[$key] }}: {{ $limit === null || $limit === '-' ? '무제한' : number_format($limit) }}</li>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                        @if($plan->billing_type === 'free')
                                                            <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-success">시작하기</a>
                                                        @else
                                                            <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-primary">구매하기</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- 유료 플랜 리스트 --}}
                                    @if($paidPlans->count() > 0)
                                        <div class="mb-3">
                                            <h6 class="fw-bold"><i class="bi bi-credit-card me-2"></i>유료 플랜</h6>
                                            @foreach($paidPlans as $plan)
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1">{{ $plan->name }}</h6>
                                                            <p class="mb-1 text-muted small">{{ $plan->description }}</p>
                                                            @if($plan->billing_type === 'free')
                                                                <span class="text-success fw-bold">무료</span>
                                                            @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                <span class="text-primary fw-bold">{{ number_format($plan->one_time_price) }}원 (1회)</span>
                                                            @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                <span class="text-primary fw-bold">{{ number_format($plan->price) }}원/월</span>
                                                            @else
                                                                <span class="text-success fw-bold">무료</span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <button type="button" class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                                자세히 보기
                                                            </button>
                                                            <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-primary btn-sm">
                                                                구매하기
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {{-- 플랜 상세 모달 --}}
                                                <div class="modal fade" id="planModal{{ $plan->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ $plan->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="mb-3">{{ $plan->description }}</p>
                                                                <div class="mb-3">
                                                                    <h6>가격</h6>
                                                                    <p class="h4 text-primary">
                                                                        @if($plan->billing_type === 'free')
                                                                            <span class="text-success">무료</span>
                                                                        @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                            {{ number_format($plan->one_time_price) }}원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>
                                                                        @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                            {{ number_format($plan->price) }}원/월
                                                                        @else
                                                                            <span class="text-success">무료</span>
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                                @if($plan->features)
                                                                    @php
                                                                        $mainFeatures = [
                                                                            'dashboard' => '대시보드',
                                                                            'users' => '사용자 관리',
                                                                            'registration_settings' => '회원가입 설정',
                                                                            'mail_settings' => '메일 설정',
                                                                            'contact_forms' => '컨텍트 폼',
                                                                            'maps' => '지도',
                                                                            'crawlers' => '크롤러',
                                                                            'user_ranks' => '회원등급',
                                                                            'boards' => '게시판 관리',
                                                                            'posts' => '게시글 관리',
                                                                            'attendance' => '출석',
                                                                            'point_exchange' => '포인트 교환',
                                                                            'event_application' => '신청형 이벤트',
                                                                            'menus' => '메뉴 설정',
                                                                            'messages' => '쪽지 관리',
                                                                            'banners' => '배너',
                                                                            'popups' => '팝업',
                                                                            'blocked_ips' => '아이피 차단',
                                                                            'custom_code' => '코드 커스텀',
                                                                            'settings' => '사이트 설정',
                                                                            'sidebar_widgets' => '사이드 위젯',
                                                                            'main_widgets' => '메인 위젯',
                                                                            'custom_pages' => '커스텀 페이지',
                                                                            'toggle_menus' => '토글 메뉴',
                                                                        ];
                                                                    @endphp
                                                                    <div class="mb-3">
                                                                        <h6>포함된 기능</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if(isset($plan->features['main_features']))
                                                                                @foreach($plan->features['main_features'] as $feature)
                                                                                    <li><i class="bi bi-check-circle text-success me-2"></i>{{ $mainFeatures[$feature] ?? $feature }}</li>
                                                                                @endforeach
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                                @if($plan->limits || $plan->traffic_limit_mb)
                                                                    <div class="mb-3">
                                                                        <h6>제한 사항</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if($plan->traffic_limit_mb)
                                                                                <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                                                            @endif
                                                                            @if(isset($plan->limits['storage']))
                                                                                <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB' }}</li>
                                                                            @endif
                                                                            @if($plan->limits)
                                                                                @php
                                                                                    $limitLabels = [
                                                                                        'boards' => '게시판 수',
                                                                                        'widgets' => '위젯 수',
                                                                                        'custom_pages' => '커스텀 페이지 수',
                                                                                        'users' => '사용자 수',
                                                                                    ];
                                                                                @endphp
                                                                                @foreach($plan->limits as $key => $limit)
                                                                                    @if($key !== 'storage' && isset($limitLabels[$key]))
                                                                                        <li><i class="bi bi-info-circle text-info me-2"></i>{{ $limitLabels[$key] }}: {{ $limit === null || $limit === '-' ? '무제한' : number_format($limit) }}</li>
                                                                                    @endif
                                                                                @endforeach
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                                <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-primary">구매하기</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- 서버 용량 플랜 리스트 --}}
                                    @if($serverPlans->count() > 0)
                                        <div class="mb-3">
                                            <h6 class="fw-bold"><i class="bi bi-server me-2"></i>서버 용량 플랜</h6>
                                            @foreach($serverPlans as $plan)
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1">{{ $plan->name }}</h6>
                                                            <p class="mb-1 text-muted small">{{ $plan->description }}</p>
                                                            @if($plan->billing_type === 'free')
                                                                <span class="text-success fw-bold">무료</span>
                                                            @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                <span class="text-primary fw-bold">{{ number_format($plan->one_time_price) }}원 (1회)</span>
                                                            @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                <span class="text-primary fw-bold">{{ number_format($plan->price) }}원/월</span>
                                                            @else
                                                                <span class="text-success fw-bold">무료</span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <button type="button" class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#planModal{{ $plan->id }}">
                                                                자세히 보기
                                                            </button>
                                                            <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-primary btn-sm">
                                                                구독하기
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {{-- 플랜 상세 모달 --}}
                                                <div class="modal fade" id="planModal{{ $plan->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ $plan->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="mb-3">{{ $plan->description }}</p>
                                                                <div class="mb-3">
                                                                    <h6>가격</h6>
                                                                    <p class="h4 text-primary">
                                                                        @if($plan->billing_type === 'free')
                                                                            <span class="text-success">무료</span>
                                                                        @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                                                            {{ number_format($plan->one_time_price) }}원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>
                                                                        @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                                                            {{ number_format($plan->price) }}원/월
                                                                        @else
                                                                            <span class="text-success">무료</span>
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                                @if($plan->limits || $plan->traffic_limit_mb)
                                                                    <div class="mb-3">
                                                                        <h6>제한 사항</h6>
                                                                        <ul class="list-unstyled">
                                                                            @if($plan->traffic_limit_mb)
                                                                                <li><i class="bi bi-arrow-left-right text-info me-2"></i>트래픽: {{ number_format($plan->traffic_limit_mb) }}MB/월 ({{ number_format($plan->traffic_limit_mb / 1024, 2) }}GB/월)</li>
                                                                            @endif
                                                                            @if(isset($plan->limits['storage']))
                                                                                <li><i class="bi bi-hdd text-info me-2"></i>스토리지: {{ $plan->limits['storage'] === null || $plan->limits['storage'] === '-' ? '무제한' : number_format($plan->limits['storage']) . 'MB' }}</li>
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                                                <a href="{{ route('payment.subscribe', ['site' => $site->slug, 'plan' => $plan->slug]) }}" class="btn btn-primary">구독하기</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">등록된 플랜이 없습니다.</p>
                @endif
                @break

            @case('chat')
            @case('chat_widget')
                @if($site->hasFeature('chat_widget'))
                    <x-chat-widget :site="$site" :widget="$widget" />
                @endif
                @break

            @case('create_site')
                @php
                    // 마스터 사이트에서만 표시
                    if (!$site->isMasterSite()) {
                        break;
                    }
                    
                    $createSiteSettings = $widgetSettings;
                    $title = $createSiteSettings['title'] ?? '나만의 홈페이지를 만들어보세요!';
                    $description = $createSiteSettings['description'] ?? '회원가입 후 간단한 정보만 입력하면 바로 홈페이지를 생성할 수 있습니다.';
                    $buttonText = $createSiteSettings['button_text'] ?? '새 사이트 만들기';
                    $buttonLink = $createSiteSettings['button_link'] ?? route('user-sites.select-plan', ['site' => $site->slug]);
                    $showOnlyWhenLoggedIn = $createSiteSettings['show_only_when_logged_in'] ?? true;
                    $backgroundColor = $createSiteSettings['background_color'] ?? '#007bff';
                    $textColor = $createSiteSettings['text_color'] ?? '#ffffff';
                    $buttonColor = $createSiteSettings['button_color'] ?? '#ffffff';
                    $buttonBgColor = $createSiteSettings['button_bg_color'] ?? '#0056b3';
                    $icon = $createSiteSettings['icon'] ?? 'bi-rocket-takeoff';
                    
                    // 패딩 설정 (block 위젯과 동일하게)
                    $paddingTop = 40;
                    $paddingBottom = 40;
                    $paddingLeft = 20;
                    $paddingRight = 20;
                    
                    // card-body 영역 안에서 전체 너비를 차지하도록 스타일 생성
                    // card-body의 기본 패딩(1rem = 16px)을 음수 마진으로 상쇄
                    // 하단 패딩도 상쇄하여 하단 여백 제거
                    $createSiteStyle = "padding-top: {$paddingTop}px; padding-bottom: {$paddingBottom}px; padding-left: {$paddingLeft}px; padding-right: {$paddingRight}px; text-align: center; color: {$textColor}; background-color: {$backgroundColor}; margin-left: -1rem; margin-right: -1rem; margin-bottom: -1rem; width: calc(100% + 2rem);";
                    
                    // 라운드 테마일 때 하단 라운드 적용
                    if ($isRoundTheme) {
                        $createSiteStyle .= " border-bottom-left-radius: 0.375rem; border-bottom-right-radius: 0.375rem;";
                    }
                    
                    // 세로 100%일 때 위젯이 전체 높이를 차지하도록
                    if ($isFullHeight) {
                        $createSiteStyle .= " flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0;";
                    }
                @endphp
                
                @if(!$showOnlyWhenLoggedIn || auth()->check())
                    <div style="{{ $createSiteStyle }}">
                        <h3 class="mb-3" style="color: {{ $textColor }};">
                            <i class="bi {{ $icon }} me-2"></i>
                            {{ $title }}
                        </h3>
                        <p class="mb-4" style="color: {{ $textColor }}; opacity: 0.9;">
                            {{ $description }}
                        </p>
                        <a href="{{ $buttonLink }}" class="btn btn-lg" style="background-color: {{ $buttonBgColor }}; color: {{ $buttonColor }}; border-color: {{ $buttonBgColor }};">
                            <i class="bi bi-plus-circle me-2"></i>{{ $buttonText }}
                        </a>
                    </div>
                @endif
                @break

            @default
                {{-- 알 수 없는 위젯 타입 --}}
        @endswitch
    </div>
</div>
@endif

@push('scripts')
@if($widget->type === 'countdown')
@php
    $countdownSettings = $widgetSettings;
    $countdownType = $countdownSettings['countdown_type'] ?? 'dday';
@endphp
@if($countdownType === 'dday')
<style>
/* 모바일에서 월/일 다음 줄바꿈 (≤576px) */
@media (max-width: 576px) {
    .countdown-mobile-break { display: block; height: 0.25rem; }
    /* 모바일에서 시간/분/초 사이 간격 줄이기 */
    .countdown-display .countdown-text .countdown-gap {
        margin: 0 0.3rem !important;
        width: 0.3rem !important;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countdownElements = document.querySelectorAll('.countdown-dday');
    if (!countdownElements.length) return;

    countdownElements.forEach((countdownElement) => {
        const targetDate = new Date(countdownElement.dataset.targetDate).getTime();
        const displayElement = countdownElement.querySelector('.countdown-display .countdown-text');
        const animationEnabled = countdownElement.dataset.animationEnabled === 'true';

        let animationInterval = null;

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                if (animationInterval) {
                    clearInterval(animationInterval);
                }
                displayElement.textContent = '이미 지났습니다.';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const targetDateObj = new Date(targetDate);
            const nowDateObj = new Date(now);

            // 같은 월인지 확인
            const isSameMonth = targetDateObj.getFullYear() === nowDateObj.getFullYear() &&
                               targetDateObj.getMonth() === nowDateObj.getMonth();

            // 숫자와 단위를 분리하여 HTML로 생성 (단위 사이 여백 추가)
            // 모바일(≤576px)에서는 날짜 줄과 시간 줄을 구분하여 표시
            const gap = `<span class="countdown-gap" style="margin: 0 1.2rem; display: inline-block; width: 1.2rem;"></span>`;
            const mobileLineBreak = '<span class="d-sm-none d-block" style="height: 0.35rem;"></span>';
            
            const timePart =
                `<span style="font-size: 2.5rem; font-weight: bold;">${String(hours).padStart(2, '0')}</span><span style="font-size: 1.2rem;">시간</span>` +
                `${gap}` +
                `<span style="font-size: 2.5rem; font-weight: bold;">${String(minutes).padStart(2, '0')}</span><span style="font-size: 1.2rem;">분</span>` +
                `${gap}` +
                `<span style="font-size: 2.5rem; font-weight: bold;">${String(seconds).padStart(2, '0')}</span><span style="font-size: 1.2rem;">초</span>`;
            
            let datePart = '';
            if (isSameMonth) {
                datePart =
                    `<span style="font-size: 2.5rem; font-weight: bold;">${days}</span><span style="font-size: 1.2rem;">일</span>`;
            } else {
                const month = targetDateObj.getMonth() + 1;
                datePart =
                    `<span style="font-size: 2.5rem; font-weight: bold;">${month}</span><span style="font-size: 1.2rem;">월</span>` +
                    `${gap}` +
                    `<span style="font-size: 2.5rem; font-weight: bold;">${days}</span><span style="font-size: 1.2rem;">일</span>`;
            }
            
            countdownHTML = `${datePart}${mobileLineBreak}${gap}${timePart}`;

            if (animationEnabled) {
                // 애니메이션 효과: 숫자가 빠르게 변경되다가 최종 값으로 멈춤
                if (animationInterval) {
                    clearInterval(animationInterval);
                }

                // 초기에는 랜덤 숫자로 표시 (HTML 구조 유지)
                let animatedHTML = countdownHTML.replace(/(\d+)/g, (match) => {
                    return match.replace(/\d/g, () => Math.floor(Math.random() * 10));
                });
                displayElement.innerHTML = animatedHTML;

                // 짧은 딜레이 후 실제 값 표시
                setTimeout(() => {
                    displayElement.innerHTML = countdownHTML;
                }, 100);
            } else {
                displayElement.innerHTML = countdownHTML;
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
});
</script>
@elseif($countdownType === 'number')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const numberItems = document.querySelectorAll('.countdown-number-item');
    
    numberItems.forEach(function(item) {
        const targetNumber = parseFloat(item.dataset.itemNumber) || 0;
        const animationEnabled = item.dataset.animation === 'true';
        const displayElement = item.querySelector('.countdown-number-display');
        
        if (!displayElement) return;
        
        // 소수점 자릿수 계산
        const decimalPlaces = (targetNumber.toString().split('.')[1] || '').length;
        
        if (animationEnabled) {
            // 애니메이션: 0부터 목표 숫자까지 부드럽게 증가
            let currentNumber = 0;
            const duration = 2000; // 2초 동안 애니메이션
            const steps = 60; // 60 프레임
            const stepDuration = duration / steps;
            const increment = targetNumber / steps;
            let step = 0;
            
            function animateNumber() {
                if (step < steps) {
                    step++;
                    currentNumber = Math.min(increment * step, targetNumber);
                    
                    // 소수점 자릿수에 맞춰 표시
                    if (decimalPlaces > 0) {
                        displayElement.textContent = currentNumber.toFixed(decimalPlaces);
                    } else {
                        displayElement.textContent = Math.floor(currentNumber);
                    }
                    
                    setTimeout(animateNumber, stepDuration);
                } else {
                    // 최종적으로 목표 숫자 표시
                    if (decimalPlaces > 0) {
                        displayElement.textContent = targetNumber.toFixed(decimalPlaces);
                    } else {
                        displayElement.textContent = targetNumber;
                    }
                }
            }
            
            animateNumber();
        } else {
            // 애니메이션 없이 바로 표시
            if (decimalPlaces > 0) {
                displayElement.textContent = targetNumber.toFixed(decimalPlaces);
            } else {
                displayElement.textContent = targetNumber;
            }
        }
    });
});
</script>
@endif
@endif
@endpush

