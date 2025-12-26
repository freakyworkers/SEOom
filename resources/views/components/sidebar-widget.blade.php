@php
    $widgetSettings = $widget->settings ?? [];
    $limit = $widgetSettings['limit'] ?? 10;
    
    // 헤더 테두리 설정 가져오기
    $headerBorder = $site->getSetting('header_border', '0') == '1';
    $headerBorderWidth = $site->getSetting('header_border_width', '1');
    $headerBorderColor = $site->getSetting('header_border_color', '#dee2e6');
    
    // 위젯 상단 테두리 스타일
    $widgetTopBorderStyle = '';
    if ($headerBorder) {
        $widgetTopBorderStyle = "border-top: {$headerBorderWidth}px solid {$headerBorderColor};";
    }
    
    // 테마 설정 가져오기
    $themeMain = $site->getSetting('theme_main', 'round');
    $isRoundTheme = $themeMain === 'round';
@endphp

    @if($widget->type === 'block')
    @php
        $blockSettings = $widgetSettings;
        $blockTitle = $blockSettings['block_title'] ?? '';
        $blockContent = $blockSettings['block_content'] ?? '';
        $textAlign = $blockSettings['text_align'] ?? 'left';
        $backgroundType = $blockSettings['background_type'] ?? 'color';
        $backgroundColor = $blockSettings['background_color'] ?? '#007bff';
        $backgroundImageUrl = $blockSettings['background_image_url'] ?? '';
        $paddingTop = $blockSettings['padding_top'] ?? 20;
        $paddingLeft = $blockSettings['padding_left'] ?? 20;
        $link = $blockSettings['link'] ?? '';
        $openNewTab = $blockSettings['open_new_tab'] ?? false;
        $fontColor = $blockSettings['font_color'] ?? '#ffffff';
        $titleFontSize = $blockSettings['title_font_size'] ?? '16';
        $contentFontSize = $blockSettings['content_font_size'] ?? '14';
        $showButton = $blockSettings['show_button'] ?? false;
        $buttonText = $blockSettings['button_text'] ?? '';
        $buttonBackgroundColor = $blockSettings['button_background_color'] ?? '#007bff';
        $buttonTextColor = $blockSettings['button_text_color'] ?? '#ffffff';
        
        // 스타일 생성
        $blockStyle = "padding-top: {$paddingTop}px; padding-bottom: {$paddingTop}px; padding-left: {$paddingLeft}px; padding-right: {$paddingLeft}px; text-align: {$textAlign}; color: {$fontColor};";
        
        if ($backgroundType === 'color') {
            $blockStyle .= " background-color: {$backgroundColor};";
        } else if ($backgroundType === 'image' && $backgroundImageUrl) {
            $blockStyle .= " background-image: url('{$backgroundImageUrl}'); background-size: cover; background-position: center;";
        }
    @endphp
    <div class="mb-3 shadow-sm" style="{{ $blockStyle }}">
        @if($link && !$showButton)
            <a href="{{ $link }}" 
               style="color: {{ $fontColor }}; text-decoration: none; display: block;"
               @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
        @endif
        @if($blockTitle)
            <h4 class="mb-2" style="color: {{ $fontColor }}; font-weight: bold; font-size: {{ $titleFontSize }}px;">{{ $blockTitle }}</h4>
        @endif
        @if($blockContent)
            <p class="mb-0" style="color: {{ $fontColor }}; font-size: {{ $contentFontSize }}px; white-space: pre-wrap;">{{ $blockContent }}</p>
        @endif
        @if($link && !$showButton)
            </a>
        @endif
        @if($showButton && $buttonText)
            <div class="mt-3" style="text-align: {{ $textAlign }};">
                @if($link)
                    <a href="{{ $link }}" 
                       @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                       style="text-decoration: none; display: inline-block;">
                        <button class="block-widget-button" 
                                style="border: 2px solid {{ $buttonBackgroundColor }}; color: {{ $buttonTextColor }}; background-color: {{ $buttonBackgroundColor }}; padding: 8px 20px; border-radius: 4px; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                onmouseover="this.style.opacity='0.9';"
                                onmouseout="this.style.opacity='1';">
                            {{ $buttonText }}
                        </button>
                    </a>
                @else
                    <button class="block-widget-button" 
                            style="border: 2px solid {{ $buttonBackgroundColor }}; color: {{ $buttonTextColor }}; background-color: {{ $buttonBackgroundColor }}; padding: 8px 20px; border-radius: 4px; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                            onmouseover="this.style.opacity='0.9';"
                            onmouseout="this.style.opacity='1';">
                        {{ $buttonText }}
                    </button>
                @endif
            </div>
        @endif
    </div>
@elseif($widget->type === 'block_slide')
    @php
        $slideSettings = $widgetSettings;
        $slideDirection = $slideSettings['slide_direction'] ?? 'left';
        $blocks = $slideSettings['blocks'] ?? [];
    @endphp
    @if(count($blocks) > 0)
        <div class="mb-3 block-slide-wrapper shadow-sm" 
             data-direction="{{ $slideDirection }}" 
             data-widget-id="{{ $widget->id }}"
             style="position: relative; overflow: hidden; {{ in_array($slideDirection, ['up', 'down']) ? 'height: 200px;' : '' }}">
            <div class="block-slide-container" style="display: flex; transition: transform 0.5s ease-in-out; {{ in_array($slideDirection, ['up', 'down']) ? 'flex-direction: column; height: 100%;' : '' }}">
                @foreach($blocks as $index => $block)
                    @php
                        $blockTitle = $block['title'] ?? '';
                        $blockContent = $block['content'] ?? '';
                        $textAlign = $block['text_align'] ?? 'left';
                        $backgroundType = $block['background_type'] ?? 'color';
                        $backgroundColor = $block['background_color'] ?? '#007bff';
                        $backgroundImageUrl = $block['background_image_url'] ?? '';
                        $paddingTop = $block['padding_top'] ?? 20;
                        $paddingLeft = $block['padding_left'] ?? 20;
                        $link = $block['link'] ?? '';
                        $openNewTab = $block['open_new_tab'] ?? false;
                        $fontColor = $block['font_color'] ?? '#ffffff';
                        $titleFontSize = $block['title_font_size'] ?? '16';
                        $contentFontSize = $block['content_font_size'] ?? '14';
                        $showButton = $block['show_button'] ?? false;
                        $buttonText = $block['button_text'] ?? '';
                        $buttonBackgroundColor = $block['button_background_color'] ?? '#007bff';
                        $buttonTextColor = $block['button_text_color'] ?? '#ffffff';
                        
                        // 스타일 생성
                        $blockStyle = "padding-top: {$paddingTop}px; padding-bottom: {$paddingTop}px; padding-left: {$paddingLeft}px; padding-right: {$paddingLeft}px; text-align: {$textAlign}; color: {$fontColor};";
                        
                        if ($backgroundType === 'color') {
                            $blockStyle .= " background-color: {$backgroundColor};";
                        } else if ($backgroundType === 'image' && $backgroundImageUrl) {
                            $blockStyle .= " background-image: url('{$backgroundImageUrl}'); background-size: cover; background-position: center;";
                        }
                        
                        // 슬라이드 방향에 따른 너비/높이 설정
                        if (in_array($slideDirection, ['left', 'right'])) {
                            $blockStyle .= " width: 100%; flex-shrink: 0;";
                        } else {
                            $blockStyle .= " width: 100%; height: 100%; flex-shrink: 0;";
                        }
                    @endphp
                    <div class="block-slide-item" style="{{ $blockStyle }}" data-index="{{ $index }}">
                        @if($link && !$showButton)
                            <a href="{{ $link }}" 
                               style="color: {{ $fontColor }}; text-decoration: none; display: block;"
                               @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
                        @endif
                        @if($blockTitle)
                            <h4 class="mb-2" style="color: {{ $fontColor }}; font-weight: bold; font-size: {{ $titleFontSize }}px;">{{ $blockTitle }}</h4>
                        @endif
                        @if($blockContent)
                            <p class="mb-0" style="color: {{ $fontColor }}; font-size: {{ $contentFontSize }}px; white-space: pre-wrap;">{{ $blockContent }}</p>
                        @endif
                        @if($link && !$showButton)
                            </a>
                        @endif
                        @if($showButton && $buttonText)
                            <div class="mt-3" style="text-align: {{ $textAlign }};">
                                @if($link)
                                    <a href="{{ $link }}" 
                                       @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                       style="text-decoration: none; display: inline-block;">
                                        <button class="block-widget-button" 
                                                style="border: 2px solid {{ $buttonColor }}; color: {{ $buttonColor }}; background-color: transparent; padding: 8px 20px; border-radius: 4px; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                                onmouseover="this.style.backgroundColor='{{ $buttonColor }}'; this.style.color='#ffffff';"
                                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $buttonColor }}';">
                                            {{ $buttonText }}
                                        </button>
                                    </a>
                                @else
                                    <button class="block-widget-button" 
                                            style="border: 2px solid {{ $buttonColor }}; color: {{ $buttonColor }}; background-color: transparent; padding: 8px 20px; border-radius: 4px; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                            onmouseover="this.style.backgroundColor='{{ $buttonColor }}'; this.style.color='#ffffff';"
                                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $buttonColor }}';">
                                        {{ $buttonText }}
                                    </button>
                                @endif
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
                        $backgroundImageUrl = $block['background_image_url'] ?? '';
                        $paddingTop = $block['padding_top'] ?? 20;
                        $paddingLeft = $block['padding_left'] ?? 20;
                        $link = $block['link'] ?? '';
                        $openNewTab = $block['open_new_tab'] ?? false;
                        $fontColor = $block['font_color'] ?? '#ffffff';
                        $titleFontSize = $block['title_font_size'] ?? '16';
                        $contentFontSize = $block['content_font_size'] ?? '14';
                        $showButton = $block['show_button'] ?? false;
                        $buttonText = $block['button_text'] ?? '';
                        $buttonBackgroundColor = $block['button_background_color'] ?? '#007bff';
                        $buttonTextColor = $block['button_text_color'] ?? '#ffffff';
                        
                        // 스타일 생성
                        $blockStyle = "padding-top: {$paddingTop}px; padding-bottom: {$paddingTop}px; padding-left: {$paddingLeft}px; padding-right: {$paddingLeft}px; text-align: {$textAlign}; color: {$fontColor};";
                        
                        if ($backgroundType === 'color') {
                            $blockStyle .= " background-color: {$backgroundColor};";
                        } else if ($backgroundType === 'image' && $backgroundImageUrl) {
                            $blockStyle .= " background-image: url('{$backgroundImageUrl}'); background-size: cover; background-position: center;";
                        }
                        
                        // 슬라이드 방향에 따른 너비/높이 설정
                        if (in_array($slideDirection, ['left', 'right'])) {
                            $blockStyle .= " width: 100%; flex-shrink: 0;";
                        } else {
                            $blockStyle .= " width: 100%; height: 100%; flex-shrink: 0;";
                        }
                    @endphp
                    <div class="block-slide-item block-slide-item-clone" style="{{ $blockStyle }}" data-index="{{ $index }}">
                        @if($link && !$showButton)
                            <a href="{{ $link }}" 
                               style="color: {{ $fontColor }}; text-decoration: none; display: block;"
                               @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
                        @endif
                        @if($blockTitle)
                            <h4 class="mb-2" style="color: {{ $fontColor }}; font-weight: bold; font-size: {{ $titleFontSize }}px;">{{ $blockTitle }}</h4>
                        @endif
                        @if($blockContent)
                            <p class="mb-0" style="color: {{ $fontColor }}; font-size: {{ $contentFontSize }}px; white-space: pre-wrap;">{{ $blockContent }}</p>
                        @endif
                        @if($link && !$showButton)
                            </a>
                        @endif
                        @if($showButton && $buttonText)
                            <div class="mt-3" style="text-align: {{ $textAlign }};">
                                @if($link)
                                    <a href="{{ $link }}" 
                                       @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                                       style="text-decoration: none; display: inline-block;">
                                        <button class="block-widget-button" 
                                                style="border: 2px solid {{ $buttonColor }}; color: {{ $buttonColor }}; background-color: transparent; padding: 8px 20px; border-radius: 4px; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
                                                onmouseover="this.style.backgroundColor='{{ $buttonColor }}'; this.style.color='#ffffff';"
                                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $buttonColor }}';">
                                            {{ $buttonText }}
                                        </button>
                                    </a>
                                @else
                                    <button class="block-widget-button" 
                                            style="border: 2px solid {{ $buttonColor }}; color: {{ $buttonColor }}; background-color: transparent; padding: 8px 20px; border-radius: 4px; font-weight: 500; transition: all 0.3s ease; cursor: pointer;"
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
                
                // 마지막 원본 블록에 도달하면 (복제된 첫 번째 블록 위치)
                if (currentIndex >= totalItems) {
                    setTimeout(() => {
                        currentIndex = 0;
                        updatePosition(true);
                        setTimeout(() => {
                            isTransitioning = false;
                        }, 50);
                    }, 500); // transition 시간과 동일
                } else {
                    setTimeout(() => {
                        isTransitioning = false;
                    }, 500);
                }
            }
            
            // 초기 위치 설정
            updatePosition();
            
            // 3초마다 슬라이드 전환
            setInterval(nextSlide, 3000);
        })();
        </script>
    @endif
@elseif($widget->type === 'image')
    @php
        $imageSettings = $widgetSettings;
        $imageUrl = $imageSettings['image_url'] ?? '';
        $link = $imageSettings['link'] ?? '';
        $openNewTab = $imageSettings['open_new_tab'] ?? false;
    @endphp
    @if($imageUrl)
        <div class="mb-3 shadow-sm {{ $isRoundTheme ? '' : 'rounded-0' }}" style="{{ $isRoundTheme ? 'border-radius: 0.5rem; overflow: hidden;' : '' }}">
            @if($link)
                <a href="{{ $link }}" 
                   @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif
                   style="{{ $isRoundTheme ? 'display: block; border-radius: 0.5rem; overflow: hidden;' : 'display: block;' }}">
            @endif
            <img src="{{ $imageUrl }}" alt="이미지" style="width: 100%; height: auto; display: block;{{ $isRoundTheme ? ' border-radius: 0.5rem;' : '' }}">
            @if($link)
                </a>
            @endif
        </div>
    @endif
@elseif($widget->type === 'image_slide')
    @php
        $imageSlideSettings = $widgetSettings;
        $slideDirection = $imageSlideSettings['slide_direction'] ?? 'left';
        $slideMode = $imageSlideSettings['slide_mode'] ?? 'single';
        $visibleCount = $imageSlideSettings['visible_count'] ?? 3;
        $images = $imageSlideSettings['images'] ?? [];
    @endphp
    @if(count($images) > 0)
        <div class="mb-3 image-slide-wrapper shadow-sm {{ $isRoundTheme ? '' : 'rounded-0' }}" 
             data-direction="{{ $slideDirection }}" 
             data-mode="{{ $slideMode }}"
             data-visible-count="{{ $visibleCount }}"
             data-widget-id="{{ $widget->id }}"
             style="position: relative; overflow: hidden; {{ ($slideMode === 'single' && in_array($slideDirection, ['up', 'down'])) ? 'height: 200px;' : '' }}{{ $isRoundTheme ? ' border-radius: 0.5rem;' : '' }}">
            <div class="image-slide-container" style="display: flex; {{ $slideMode === 'infinite' ? 'flex-direction: row;' : '' }} transition: transform 0.5s ease-in-out; {{ ($slideMode === 'single' && in_array($slideDirection, ['up', 'down'])) ? 'flex-direction: column; height: 100%;' : '' }}">
                @if($slideMode === 'single')
                    @foreach($images as $index => $image)
                        @php
                            $imageUrl = $image['image_url'] ?? '';
                            $link = $image['link'] ?? '';
                            $openNewTab = $image['open_new_tab'] ?? false;
                        @endphp
                        @if($imageUrl)
                            <div class="image-slide-item" style="width: 100%; flex-shrink: 0; {{ in_array($slideDirection, ['up', 'down']) ? 'height: 100%;' : '' }}">
                                @if($link)
                                    <a href="{{ $link }}" 
                                       @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
                                @endif
                                <img src="{{ $imageUrl }}" alt="이미지 {{ $index + 1 }}" style="width: 100%; height: auto; display: block;">
                                @if($link)
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
                        @endphp
                        @if($imageUrl)
                            <div class="image-slide-item image-slide-item-clone" style="width: 100%; flex-shrink: 0; {{ in_array($slideDirection, ['up', 'down']) ? 'height: 100%;' : '' }}">
                                @if($link)
                                    <a href="{{ $link }}" 
                                       @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
                                @endif
                                <img src="{{ $imageUrl }}" alt="이미지 {{ $index + 1 }}" style="width: 100%; height: auto; display: block;">
                                @if($link)
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
                            @endphp
                            @if($imageUrl)
                                <div class="image-slide-item" style="width: calc(100% / {{ $visibleCount }}); flex-shrink: 0;">
                                    @if($link)
                                        <a href="{{ $link }}" 
                                           @if($openNewTab) target="_blank" rel="noopener noreferrer" @endif>
                                    @endif
                                    <img src="{{ $imageUrl }}" alt="이미지 {{ $index + 1 }}" style="width: 100%; height: auto; display: block;">
                                    @if($link)
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
                
                // 3초마다 슬라이드 전환
                setInterval(nextSlide, 3000);
            } else {
                // 무한루프 슬라이드 모드
                const items = wrapper.querySelectorAll('.image-slide-item');
                const totalItems = items.length;
                
                if (totalItems <= visibleCount) return;
                
                // 각 이미지의 너비 계산
                const itemWidthPercent = 100 / visibleCount;
                const singleSetWidth = (totalItems / 3) * itemWidthPercent;
                
                let position = 0;
                const speed = 0.3; // 이동 속도 (% 단위)
                
                function animate() {
                    if (direction === 'left') {
                        position -= speed;
                        // 첫 번째 세트가 완전히 사라지면 위치 리셋
                        if (Math.abs(position) >= singleSetWidth) {
                            position = 0;
                        }
                        container.style.transform = `translateX(${position}%)`;
                    } else if (direction === 'right') {
                        position += speed;
                        // 첫 번째 세트가 완전히 사라지면 위치 리셋
                        if (position >= singleSetWidth) {
                            position = 0;
                        }
                        container.style.transform = `translateX(${position}%)`;
                    }
                    requestAnimationFrame(animate);
                }
                
                animate();
            }
        })();
        </script>
    @endif
@else
<div class="card shadow-sm mb-3 {{ $isRoundTheme ? '' : 'rounded-0' }}" style="{{ $widgetTopBorderStyle }}{{ !$isRoundTheme ? ' border-radius: 0 !important; border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;' : '' }}">
    @if($widget->type !== 'user_ranking' && $widget->type !== 'marquee_board' && $widget->type !== 'block' && $widget->type !== 'block_slide' && $widget->type !== 'image' && $widget->type !== 'image_slide' && $widget->type !== 'tab_menu' && $widget->type !== 'toggle_menu')
        @if($widget->type === 'gallery')
            @if(!empty($widget->title))
            <div class="card-header" style="background-color: white;{{ !$isRoundTheme ? ' border-radius: 0 !important; border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;' : '' }} border: none !important; border-bottom: 1px solid #dee2e6 !important;">
                <h6 class="mb-0">{{ $widget->title }}</h6>
            </div>
            @endif
        @else
        <div class="card-header" style="background-color: white; {{ $widgetTopBorderStyle }}{{ !$isRoundTheme ? ' border-radius: 0 !important; border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;' : '' }} border: none !important; border-bottom: 1px solid #dee2e6 !important;">
            <h6 class="mb-0">{{ $widget->title }}</h6>
        </div>
        @endif
    @endif
    <div class="card-body" style="{{ ($widget->type === 'tab_menu' || $widget->type === 'user_ranking' || $widget->type === 'toggle_menu') ? 'padding-top: 0 !important;' : '' }}">
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
                            <li class="mb-2 pb-2 border-bottom">
                                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $post->board->slug ?? 'default', 'post' => $post->id]) }}" 
                                   class="text-decoration-none d-block" 
                                   style="color: #495057;">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.9rem;">
                                        {{ $post->title }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $post->user->nickname ?? $post->user->name ?? '익명' }} · 
                                        {{ $post->created_at->diffForHumans() }}
                                    </small>
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
                                       style="color: #495057; width: 100%;">
                                        <span class="fw-semibold me-2">{{ $boardName }} | </span>
                                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;">{{ Str::limit($post->title, 50, '...') }}</span>
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
                        if ($board && in_array($board->type, ['photo', 'bookmark', 'blog'])) {
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
                                        <div class="position-relative" style="aspect-ratio: 1; overflow: hidden; background-color: #f8f9fa;">
                                            @if($thumbnail)
                                                @if(str_starts_with($thumbnail, 'http'))
                                                    <img src="{{ $thumbnail }}" 
                                                         alt="{{ $post->title }}" 
                                                         class="w-100 h-100" 
                                                         style="object-fit: cover;">
                                                @else
                                                    <img src="{{ asset('storage/' . $thumbnail) }}" 
                                                         alt="{{ $post->title }}" 
                                                         class="w-100 h-100" 
                                                         style="object-fit: cover;">
                                                @endif
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center">
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
                                     style="display: flex; 
                                            @if($slideDirection === 'left' || $slideDirection === 'right')
                                                flex-direction: row; 
                                                transition: transform 0.5s ease;
                                            @else
                                                flex-direction: column; 
                                                transition: transform 0.5s ease;
                                            @endif">
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
                                             style="@if($slideDirection === 'left' || $slideDirection === 'right')
                                                        flex: 0 0 {{ 100 / $slideCols }}%; 
                                                        padding: 0 4px;
                                                    @else
                                                        width: 100%;
                                                        padding: 4px 0;
                                                    @endif">
                                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                               class="text-decoration-none d-block">
                                                <div class="position-relative" style="aspect-ratio: 1; overflow: hidden; background-color: #f8f9fa;">
                                                    @if($thumbnail)
                                                        @if(str_starts_with($thumbnail, 'http'))
                                                            <img src="{{ $thumbnail }}" 
                                                                 alt="{{ $post->title }}" 
                                                                 class="w-100 h-100" 
                                                                 style="object-fit: cover;">
                                                        @else
                                                            <img src="{{ asset('storage/' . $thumbnail) }}" 
                                                                 alt="{{ $post->title }}" 
                                                                 class="w-100 h-100" 
                                                                 style="object-fit: cover;">
                                                        @endif
                                                    @else
                                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center">
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
                                    {{-- 무한 슬라이드를 위한 복제 아이템 --}}
                                    @foreach($galleryPosts->take($slideCols) as $index => $post)
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
                                        <div class="gallery-slide-item gallery-slide-duplicate" 
                                             style="@if($slideDirection === 'left' || $slideDirection === 'right')
                                                        flex: 0 0 {{ 100 / $slideCols }}%; 
                                                        padding: 0 4px;
                                                    @else
                                                        width: 100%;
                                                        padding: 4px 0;
                                                    @endif">
                                            <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $board->slug, 'post' => $post->id]) }}" 
                                               class="text-decoration-none d-block">
                                                <div class="position-relative" style="aspect-ratio: 1; overflow: hidden; background-color: #f8f9fa;">
                                                    @if($thumbnail)
                                                        @if(str_starts_with($thumbnail, 'http'))
                                                            <img src="{{ $thumbnail }}" 
                                                                 alt="{{ $post->title }}" 
                                                                 class="w-100 h-100" 
                                                                 style="object-fit: cover;">
                                                        @else
                                                            <img src="{{ asset('storage/' . $thumbnail) }}" 
                                                                 alt="{{ $post->title }}" 
                                                                 class="w-100 h-100" 
                                                                 style="object-fit: cover;">
                                                        @endif
                                                    @else
                                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center">
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
                            </div>
                        </div>
                        <script>
                            (function() {
                                const widgetId = {{ $widget->id }};
                                const container = document.getElementById('gallery-slider-' + widgetId);
                                const wrapper = document.getElementById('gallery-slide-wrapper-' + widgetId);
                                if (!container || !wrapper) return;
                                
                                const direction = wrapper.dataset.direction || 'left';
                                const cols = parseInt(wrapper.dataset.cols) || 3;
                                const totalItems = {{ $galleryPosts->count() }};
                                
                                if (totalItems <= cols) return; // 슬라이드 불필요
                                
                                let currentIndex = 0;
                                let intervalId;
                                let isTransitioning = false;
                                
                                function slideNext() {
                                    if (isTransitioning) return;
                                    isTransitioning = true;
                                    
                                    const items = wrapper.querySelectorAll('.gallery-slide-item:not(.gallery-slide-duplicate)');
                                    const itemCount = items.length;
                                    
                                    if (direction === 'left') {
                                        currentIndex += cols;
                                        if (currentIndex >= itemCount) {
                                            currentIndex = 0;
                                            // 무한 슬라이드를 위해 transition 없이 처음으로 이동
                                            setTimeout(() => {
                                                wrapper.style.transition = 'none';
                                                wrapper.style.transform = 'translateX(0)';
                                                setTimeout(() => {
                                                    wrapper.style.transition = 'transform 0.5s ease';
                                                    isTransitioning = false;
                                                }, 50);
                                            }, 500);
                                        } else {
                                            wrapper.style.transform = `translateX(-${currentIndex * (100 / cols)}%)`;
                                            setTimeout(() => {
                                                isTransitioning = false;
                                            }, 500);
                                        }
                                    } else if (direction === 'right') {
                                        currentIndex -= cols;
                                        if (currentIndex < 0) {
                                            currentIndex = itemCount - cols;
                                            // 무한 슬라이드를 위해 transition 없이 마지막으로 이동
                                            setTimeout(() => {
                                                wrapper.style.transition = 'none';
                                                wrapper.style.transform = `translateX(-${currentIndex * (100 / cols)}%)`;
                                                setTimeout(() => {
                                                    wrapper.style.transition = 'transform 0.5s ease';
                                                    isTransitioning = false;
                                                }, 50);
                                            }, 500);
                                        } else {
                                            wrapper.style.transform = `translateX(-${currentIndex * (100 / cols)}%)`;
                                            setTimeout(() => {
                                                isTransitioning = false;
                                            }, 500);
                                        }
                                    } else if (direction === 'up') {
                                        currentIndex += 1;
                                        if (currentIndex >= itemCount) {
                                            currentIndex = 0;
                                            setTimeout(() => {
                                                wrapper.style.transition = 'none';
                                                wrapper.style.transform = 'translateY(0)';
                                                setTimeout(() => {
                                                    wrapper.style.transition = 'transform 0.5s ease';
                                                    isTransitioning = false;
                                                }, 50);
                                            }, 500);
                                        } else {
                                            wrapper.style.transform = `translateY(-${currentIndex * 100}%)`;
                                            setTimeout(() => {
                                                isTransitioning = false;
                                            }, 500);
                                        }
                                    } else if (direction === 'down') {
                                        currentIndex -= 1;
                                        if (currentIndex < 0) {
                                            currentIndex = itemCount - 1;
                                            setTimeout(() => {
                                                wrapper.style.transition = 'none';
                                                wrapper.style.transform = `translateY(-${currentIndex * 100}%)`;
                                                setTimeout(() => {
                                                    wrapper.style.transition = 'transform 0.5s ease';
                                                    isTransitioning = false;
                                                }, 50);
                                            }, 500);
                                        } else {
                                            wrapper.style.transform = `translateY(-${currentIndex * 100}%)`;
                                            setTimeout(() => {
                                                isTransitioning = false;
                                            }, 500);
                                        }
                                    }
                                }
                                
                                function startAutoSlide() {
                                    if (intervalId) clearInterval(intervalId);
                                    intervalId = setInterval(slideNext, 3000);
                                }
                                
                                function stopAutoSlide() {
                                    if (intervalId) {
                                        clearInterval(intervalId);
                                        intervalId = null;
                                    }
                                }
                                
                                // 초기 설정
                                if (direction === 'left' || direction === 'right') {
                                    const slideContainer = container.querySelector('.gallery-slide-container');
                                    if (slideContainer) {
                                        slideContainer.style.width = '100%';
                                    }
                                }
                                
                                // 호버 시 일시 정지
                                container.addEventListener('mouseenter', stopAutoSlide);
                                container.addEventListener('mouseleave', startAutoSlide);
                                
                                // 자동 슬라이드 시작
                                startAutoSlide();
                            })();
                        </script>
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
                                   style="color: #495057;">
                                    <span>{{ $board->name }}</span>
                                    <small class="text-muted">{{ $board->posts_count ?? 0 }}</small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
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
                                   style="color: #495057;">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.9rem;">
                                        {{ $post->title }}
                                    </div>
                                    <small class="text-muted">
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
                @endphp
                @if(empty($toggleMenuId))
                    <p class="text-muted mb-0">토글 메뉴가 설정되지 않았습니다.</p>
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
                                <h5 class="mb-3">{{ $widget->title }}</h5>
                            @endif
                            @foreach($toggleMenu->items as $item)
                                <div class="toggle-menu-item-widget mb-2" data-id="{{ $toggleMenu->id }}-{{ $item->id }}">
                                    <div class="toggle-menu-header-widget" onclick="toggleWidgetItemContent(this)" style="padding: 0.75rem; background-color: #f8f9fa; border: 1px solid #dee2e6; cursor: pointer; display: flex; align-items: center; justify-content: space-between; {{ $isRoundTheme ? 'border-radius: 0.375rem 0.375rem 0 0;' : '' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-chevron-right toggle-icon-widget me-2" style="transition: transform 0.3s;"></i>
                                            <strong>{{ $item->title }}</strong>
                                        </div>
                                    </div>
                                    <div class="toggle-menu-content-widget" style="display: none; padding: 0.75rem; border: 1px solid #dee2e6; border-top: none; background-color: #fff; {{ $isRoundTheme ? 'border-radius: 0 0 0.375rem 0.375rem;' : '' }}">
                                        <div style="white-space: pre-wrap;">{!! nl2br(e($item->content)) !!}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <script>
                        (function() {
                            function toggleWidgetItemContent(header) {
                                const item = header.closest('.toggle-menu-item-widget');
                                const content = item.querySelector('.toggle-menu-content-widget');
                                const icon = header.querySelector('.toggle-icon-widget');
                                
                                if (content.style.display === 'none' || !content.style.display) {
                                    content.style.display = 'block';
                                    header.style.borderRadius = '{{ $isRoundTheme ? "0.375rem 0.375rem 0 0" : "0" }}';
                                    content.style.borderRadius = '{{ $isRoundTheme ? "0 0 0.375rem 0.375rem" : "0" }}';
                                    icon.style.transform = 'rotate(90deg)';
                                    header.style.backgroundColor = '#e7f3ff';
                                } else {
                                    content.style.display = 'none';
                                    header.style.borderRadius = '{{ $isRoundTheme ? "0.375rem" : "0" }}';
                                    icon.style.transform = 'rotate(0deg)';
                                    header.style.backgroundColor = '#f8f9fa';
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
                        <p class="text-muted mb-0">활성화된 토글 메뉴가 없습니다.</p>
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
                        <ul class="nav nav-tabs mb-3 sidebar-tab-menu" role="tablist" style="border-bottom: 1px solid #dee2e6; display: flex; width: 100%;">
                            @if($enableRankRanking)
                                <li class="nav-item" role="presentation" style="flex: 1 1 0; min-width: 0;">
                                    <button class="nav-link sidebar-tab-btn active" 
                                            id="ranking-tab-{{ $widget->id }}-rank" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#ranking-{{ $widget->id }}-rank" 
                                            type="button" 
                                            role="tab"
                                            style="width: 100%; text-align: center;">
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
                                            style="width: 100%; text-align: center;">
                                        포인트 랭킹
                                    </button>
                                </li>
                            @endif
                        </ul>
                        <div class="tab-content">
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
                    $paddingTop = $blockSettings['padding_top'] ?? 20;
                    $paddingLeft = $blockSettings['padding_left'] ?? 20;
                    $link = $blockSettings['link'] ?? '';
                    
                    // 스타일 생성
                    $blockStyle = "padding-top: {$paddingTop}px; padding-bottom: {$paddingTop}px; padding-left: {$paddingLeft}px; padding-right: {$paddingLeft}px; text-align: {$textAlign};";
                    
                    if ($backgroundType === 'color') {
                        $blockStyle .= " background-color: {$backgroundColor};";
                    } else if ($backgroundType === 'image' && $backgroundImageUrl) {
                        $blockStyle .= " background-image: url('{$backgroundImageUrl}'); background-size: cover; background-position: center;";
                    }
                @endphp
                <div style="{{ $blockStyle }}" class="text-white">
                    @if($link)
                        <a href="{{ $link }}" style="color: white; text-decoration: none; display: block;">
                    @endif
                    @if($blockTitle)
                        <h4 class="mb-2" style="color: white; font-weight: bold;">{{ $blockTitle }}</h4>
                    @endif
                    @if($blockContent)
                        <p class="mb-0" style="color: white; font-size: 0.9rem; white-space: pre-wrap;">{{ $blockContent }}</p>
                    @endif
                    @if($link)
                        </a>
                    @endif
                </div>
                @break

            @case('chat')
                @if($site->hasFeature('chat_widget'))
                    <div class="d-none d-md-block">
                        <x-chat-widget :site="$site" />
                    </div>
                @endif
                @break

            @default
                <p class="text-muted mb-0">위젯 타입을 확인할 수 없습니다.</p>
        @endswitch
    </div>
</div>
@endif

