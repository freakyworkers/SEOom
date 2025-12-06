@php
    use App\Models\MobileMenu;
    $mobileMenus = collect([]);
    if (\Illuminate\Support\Facades\Schema::hasTable('mobile_menus')) {
        $mobileMenus = MobileMenu::where('site_id', $site->id)
            ->orderBy('order')
            ->get();
    }
    
    // 모바일 메뉴 디자인 타입 가져오기
    $designType = $site->getSetting('mobile_menu_design_type', 'default');
    
    // 모바일 메뉴 색상 설정 가져오기
    $bgColor = $site->getSetting('mobile_menu_bg_color', '#ffffff');
    $fontColor = $site->getSetting('mobile_menu_font_color', '#495057');
@endphp

@if($mobileMenus->count() > 0)
<div class="mobile-bottom-menu-wrapper d-md-none">
    <div class="mobile-bottom-menu mobile-menu-design-{{ $designType }}">
        <div class="mobile-bottom-menu-container">
            @foreach($mobileMenus as $menu)
                <div class="mobile-menu-item-wrapper">
                    <a href="{{ $menu->url }}" class="mobile-menu-item">
                        <div class="mobile-menu-icon">
                            @if($menu->icon_type === 'image' && $menu->icon_path)
                                <img src="{{ asset('storage/' . $menu->icon_path) }}" alt="{{ $menu->name ?? '' }}" style="width: 40px; height: 40px; object-fit: contain;">
                            @elseif($menu->icon_type === 'emoji' && $menu->icon_path)
                                <span style="font-size: 32px;">{{ $menu->icon_path }}</span>
                            @else
                                <i class="{{ $menu->icon_path ?? 'bi bi-circle' }}" style="font-size: 32px;"></i>
                            @endif
                        </div>
                    </a>
                    @if(!empty($menu->name))
                        <div class="mobile-menu-label">{{ $menu->name }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
/* 모바일 메뉴 래퍼 */
.mobile-bottom-menu-wrapper {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    min-height: 60px;
}

/* 기본 스타일 */
.mobile-bottom-menu {
    position: relative;
    background-color: {{ $bgColor }};
    border-top: 1px solid #dee2e6;
    z-index: 1001;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    padding-bottom: env(safe-area-inset-bottom);
}



.mobile-bottom-menu-container {
    display: flex;
    justify-content: space-around;
    align-items: flex-start;
    padding: 8px 0;
    max-width: 100%;
    overflow-x: auto;
}

.mobile-menu-item-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    flex: 1;
    min-width: 60px;
}

.mobile-menu-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: {{ $fontColor }};
    padding: 8px 12px;
    width: 100%;
    transition: color 0.2s;
}

.mobile-menu-item:hover,
.mobile-menu-item:focus {
    color: #0d6efd;
    text-decoration: none;
}

.mobile-menu-item.active {
    color: #0d6efd;
}

.mobile-menu-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
}

.mobile-menu-label {
    font-size: 10px;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    margin-top: 4px;
    color: {{ $fontColor }};
    line-height: 1.2;
}

/* 디자인 타입: 기본타입 (default) */
.mobile-menu-design-default {
    background-color: {{ $bgColor }} !important;
    border-top: 1px solid #dee2e6;
    border-radius: 0;
}

/* 디자인 타입: 상단라운드 (top_round) */
.mobile-menu-design-top_round {
    background-color: {{ $bgColor }} !important;
    border-top: 1px solid #dee2e6;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}

/* 디자인 타입: 라운드 (round) */
.mobile-menu-design-round {
    background-color: {{ $bgColor }} !important;
    border-top: 1px solid #dee2e6;
    border-radius: 20px 20px 20px 20px;
    margin: 0 10px 10px 10px;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1), 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* 디자인 타입: 글래스 디자인 (glass) */
.mobile-menu-design-glass {
    border-top: none !important;
    border-radius: 30px 30px 30px 30px;
    margin: 0 auto 15px auto;
    max-width: fit-content;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.008), rgba(0, 0, 0, 0.003)) !important;
    backdrop-filter: blur(6px) saturate(180%) brightness(0.95) contrast(1.05);
    -webkit-backdrop-filter: blur(6px) saturate(180%) brightness(0.95) contrast(1.05);
    filter: contrast(1.05);
    box-shadow: 
        0 8px 32px 0 rgba(0, 0, 0, 0.15),
        0 4px 16px 0 rgba(0, 0, 0, 0.08),
        inset 0 1px 1px 0 rgba(255, 255, 255, 0.15),
        inset 0 -1px 1px 0 rgba(255, 255, 255, 0.1) !important;
    border: 2px solid rgba(255, 255, 255, 0.4);
    overflow: visible;
    transform: perspective(1000px) rotateX(0deg);
    position: relative;
}

/* 테두리 그라데이션 효과 - 주변 요소가 비치는 느낌 */
.mobile-menu-design-glass::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border-radius: 30px;
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.15) 0%, 
        rgba(255, 255, 255, 0.08) 25%,
        rgba(255, 255, 255, 0.03) 50%,
        rgba(255, 255, 255, 0.08) 75%,
        rgba(255, 255, 255, 0.15) 100%);
    backdrop-filter: blur(5px) saturate(160%);
    -webkit-backdrop-filter: blur(5px) saturate(160%);
    z-index: -1;
    opacity: 0.5;
    padding: 2px;
    -webkit-mask: 
        linear-gradient(#fff 0 0) content-box, 
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask: 
        linear-gradient(#fff 0 0) content-box, 
        linear-gradient(#fff 0 0);
    mask-composite: exclude;
}

.mobile-menu-design-glass::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 30px;
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.02) 0%, 
        rgba(255, 255, 255, 0.01) 25%,
        rgba(255, 255, 255, 0.002) 50%,
        rgba(255, 255, 255, 0.01) 75%,
        rgba(255, 255, 255, 0.02) 100%);
    pointer-events: none;
    z-index: 0;
}

.mobile-menu-design-glass .mobile-bottom-menu-container {
    position: relative;
    z-index: 2;
    padding: 10px 12px 8px 12px;
    display: flex;
    justify-content: center;
    gap: 8px;
    align-items: flex-start;
    gap: 8px;
}

/* 각 메뉴 항목에 별도의 글래스 박스 - 정사각형 형태 */
.mobile-menu-design-glass .mobile-menu-item-wrapper {
    flex: none;
    width: auto;
    min-width: auto;
}

.mobile-menu-design-glass .mobile-menu-item {
    position: relative;
    z-index: 2;
    background: rgba(0, 0, 0, 0.008);
    backdrop-filter: blur(5px) saturate(180%) brightness(0.95);
    -webkit-backdrop-filter: blur(5px) saturate(180%) brightness(0.95);
    filter: contrast(1.05);
    border-radius: 16px;
    border: 1.5px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 2px 6px 0 rgba(0, 0, 0, 0.1), 0 1px 3px 0 rgba(0, 0, 0, 0.08);
    padding: 6px;
    margin: 0;
    width: 56px;
    height: 56px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform: perspective(500px) translateZ(0) scale(1);
    transform-style: preserve-3d;
    -webkit-transform-style: preserve-3d;
}

/* 테두리 그라데이션 효과 - 주변 요소가 비치는 느낌 */
.mobile-menu-design-glass .mobile-menu-item::before {
    content: '';
    position: absolute;
    top: -1.5px;
    left: -1.5px;
    right: -1.5px;
    bottom: -1.5px;
    border-radius: 16px;
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.2) 0%, 
        rgba(255, 255, 255, 0.1) 25%,
        rgba(255, 255, 255, 0.04) 50%,
        rgba(255, 255, 255, 0.1) 75%,
        rgba(255, 255, 255, 0.2) 100%);
    backdrop-filter: blur(4px) saturate(160%);
    -webkit-backdrop-filter: blur(4px) saturate(160%);
    z-index: -1;
    opacity: 0.6;
    -webkit-mask: 
        linear-gradient(#fff 0 0) content-box, 
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask: 
        linear-gradient(#fff 0 0) content-box, 
        linear-gradient(#fff 0 0);
    mask-composite: exclude;
    padding: 1.5px;
}

.mobile-menu-design-glass .mobile-menu-item::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 16px;
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.015) 0%, 
        rgba(255, 255, 255, 0.008) 25%,
        rgba(255, 255, 255, 0.001) 50%,
        rgba(255, 255, 255, 0.008) 75%,
        rgba(255, 255, 255, 0.015) 100%);
    pointer-events: none;
    z-index: -1;
}

.mobile-menu-design-glass .mobile-menu-item:hover,
.mobile-menu-design-glass .mobile-menu-item:active {
    background: rgba(0, 0, 0, 0.02);
    transform: perspective(500px) translateZ(5px) scale(1.05);
    box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.15), 0 1px 4px 0 rgba(0, 0, 0, 0.1);
}

.mobile-menu-design-glass .mobile-menu-item:hover .mobile-menu-icon img,
.mobile-menu-design-glass .mobile-menu-item:active .mobile-menu-icon img {
    opacity: 1 !important;
    filter: none !important;
    transform: scale(1) !important;
    backface-visibility: hidden !important;
    -webkit-backface-visibility: hidden !important;
}

.mobile-menu-design-glass .mobile-menu-item:hover .mobile-menu-icon i,
.mobile-menu-design-glass .mobile-menu-item:active .mobile-menu-icon i {
    opacity: 1 !important;
    filter: none !important;
    transform: scale(1) !important;
    backface-visibility: hidden !important;
    -webkit-backface-visibility: hidden !important;
}

.mobile-menu-design-glass .mobile-menu-item:hover::before,
.mobile-menu-design-glass .mobile-menu-item:active::before {
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.4) 0%, 
        rgba(255, 255, 255, 0.25) 25%,
        rgba(255, 255, 255, 0.15) 50%,
        rgba(255, 255, 255, 0.25) 75%,
        rgba(255, 255, 255, 0.4) 100%);
    opacity: 0.9;
}


.mobile-menu-design-glass .mobile-menu-item::after {
    content: '';
    position: absolute;
    top: 1px;
    left: 1px;
    right: 1px;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
    border-radius: 16px 16px 0 0;
    pointer-events: none;
}

.mobile-menu-design-glass .mobile-menu-icon {
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    position: relative;
    z-index: 1;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
}

.mobile-menu-design-glass .mobile-menu-icon img {
    width: 44px !important;
    height: 44px !important;
    object-fit: contain;
    opacity: 1 !important;
    filter: none !important;
    transition: opacity 0.3s ease, filter 0.3s ease, transform 0.3s ease;
    transform: scale(1) !important;
    will-change: transform;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}

.mobile-menu-design-glass .mobile-menu-icon i {
    font-size: 36px !important;
    opacity: 1 !important;
    filter: none !important;
    transition: opacity 0.3s ease, filter 0.3s ease, transform 0.3s ease;
    transform: scale(1) !important;
    will-change: transform;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}

.mobile-menu-design-glass .mobile-menu-icon span {
    font-size: 36px !important;
    opacity: 1 !important;
    filter: none !important;
    transition: opacity 0.3s ease, filter 0.3s ease, transform 0.3s ease;
    transform: scale(1) !important;
    will-change: transform;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    display: inline-block;
    line-height: 1;
}

.mobile-menu-design-glass .mobile-menu-label {
    font-size: 10px;
    margin-top: 6px;
    color: {{ $fontColor }};
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 60px;
    line-height: 1.2;
}


.mobile-menu-design-glass::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.95), transparent);
    pointer-events: none;
    z-index: 0;
    border-radius: 30px 30px 0 0;
}

/* 본문 하단 여백 추가 (모바일 메뉴 높이만큼) */
@media (max-width: 767.98px) {
    /* 기본 및 상단라운드 디자인 */
    .mobile-menu-design-default,
    .mobile-menu-design-top_round {
        padding-bottom: env(safe-area-inset-bottom);
    }
    
    main {
        padding-bottom: 70px !important;
    }
    
    /* 라운드 및 글래스 디자인인 경우 추가 여백 */
    .mobile-menu-design-round,
    .mobile-menu-design-glass {
        padding-bottom: calc(env(safe-area-inset-bottom) + 10px);
    }
    
    .mobile-menu-design-round ~ *,
    .mobile-menu-design-glass ~ * {
        padding-bottom: 80px !important;
    }
}
</style>
@endif

