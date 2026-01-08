@php
    $pointColor = $pointColor ?? ($headerTextColor ?? '#0d6efd');
    $headerBorder = $headerBorder ?? false;
    $headerBorderWidth = $headerBorderWidth ?? '1';
    // 헤더 테두리 컬러는 포인트 컬러 사용 (전달되지 않은 경우 포인트 컬러 사용)
    $headerBorderColor = $headerBorderColor ?? $pointColor;
    
    // 메뉴 폰트 설정
    $menuFontSize = $menuFontSize ?? '1.25rem';
    $menuFontPadding = $menuFontPadding ?? '0.5rem';
    $menuFontWeight = $menuFontWeight ?? '700';
    
    // 전체 메뉴 폰트 컬러 (설정되어 있으면 사용, 없으면 헤더 텍스트 컬러)
    $defaultMenuFontColor = $menuFontColor ?? $headerTextColor;
    
    // 현재 URL 가져오기
    $currentUrl = request()->url();
    $currentPath = request()->path();
@endphp

<style>
    .nav-link {
        transition: color 0.2s ease;
    }
    .nav-link:hover,
    .nav-link:focus,
    .nav-link.active,
    .nav-item.dropdown.show > .nav-link,
    .nav-item.dropdown:hover > .nav-link {
        color: {{ $pointColor }} !important;
    }
    /* 인라인 스타일보다 우선순위를 높이기 위한 선택자 */
    a.nav-link[data-menu-hover="true"]:hover {
        color: {{ $pointColor }} !important;
    }
    a.nav-link.dropdown-toggle[data-menu-hover="true"]:hover {
        color: {{ $pointColor }} !important;
    }
    .nav-item.dropdown {
        position: relative;
    }
    /* 하부 메뉴가 있는 메뉴에 hover 시 드롭다운 표시 */
    .nav-item.dropdown:hover > .dropdown-menu,
    .nav-item.dropdown > .dropdown-menu:hover {
        display: block !important;
    }
    .dropdown-menu {
        margin-top: 0 !important;
        padding-top: 7px !important;
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
        top: 100% !important;
        left: 0 !important;
        position: absolute !important;
    }
    /* 하부 메뉴가 있는 메뉴의 하단 검정 표시 제거 */
    .nav-item.dropdown > .nav-link {
        border-bottom: none !important;
        text-decoration: none !important;
    }
    .nav-item.dropdown > .nav-link.active {
        border-bottom: none !important;
        text-decoration: none !important;
    }
    /* 드롭다운 메뉴와 부모 메뉴 사이의 간격을 메우기 위한 가상 요소 (::before 사용) */
    .nav-item.dropdown > .nav-link::before {
        content: '';
        position: absolute;
        bottom: -7px;
        left: 0;
        right: 0;
        height: 7px;
        background: transparent;
        z-index: 1;
    }
    /* 하부 메뉴가 있는 메뉴에 아래 화살표 아이콘 추가 */
    .nav-item.dropdown > .nav-link.dropdown-toggle::after {
        content: '\f282' !important;
        font-family: 'bootstrap-icons' !important;
        border: none !important;
        width: auto !important;
        height: auto !important;
        margin-left: 0.5rem !important;
        vertical-align: middle !important;
        font-size: 0.75rem !important;
        opacity: 0.7 !important;
        display: inline-block !important;
        position: static !important;
        background: none !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    @if($headerBorder)
    .dropdown-menu {
        border-top: {{ $headerBorderWidth }}px solid {{ $headerBorderColor }} !important;
    }
    @endif
    /* 드롭다운 메뉴 항목 기본 스타일 (로그인 버튼 드롭다운과 동일) */
    .dropdown-item {
        font-size: 0.875rem !important;
        font-weight: normal !important;
        padding: 0.35rem 1rem !important;
        transition: color 0.2s ease, background-color 0.2s ease;
    }
    .dropdown-item:hover,
    .dropdown-item:focus {
        color: {{ $pointColor }} !important;
        background-color: rgba(0, 0, 0, 0.05) !important;
    }
    .dropdown-item.active {
        color: {{ $pointColor }} !important;
        background-color: transparent !important;
    }
    /* active 상태일 때 포인트 컬러 적용 */
    a.dropdown-item.active {
        color: {{ $pointColor }} !important;
    }
</style>

@foreach($menus as $menu)
    @php
        // 현재 URL과 메뉴 URL 비교
        $menuUrl = $menu->url;
        $isActive = false;
        if ($menuUrl !== '#' && $menuUrl !== '') {
            // URL 경로 비교 (도메인 제외)
            $menuPath = parse_url($menuUrl, PHP_URL_PATH);
            if ($menuPath && strpos($currentPath, trim($menuPath, '/')) === 0) {
                $isActive = true;
            }
        }
        // 메뉴별 폰트 컬러 (개별 메뉴에 설정되어 있으면 사용, 없으면 전체 메뉴 폰트 컬러, 그것도 없으면 헤더 텍스트 컬러)
        $menuItemFontColor = $menu->font_color ?? $defaultMenuFontColor;
    @endphp
    @if($menu->children && $menu->children->count() > 0)
        <li class="nav-item dropdown" style="margin-right: {{ $menuFontPadding }};">
            <a class="nav-link dropdown-toggle {{ $isActive ? 'active' : '' }}" href="#" id="menu{{ $menu->id }}Dropdown" role="button" data-bs-toggle="dropdown" data-menu-hover="true" style="font-size: {{ $menuFontSize }}; font-weight: {{ $menuFontWeight }}; padding: {{ $menuFontPadding }}; color: {{ $isActive ? $pointColor : $menuItemFontColor }}; border-bottom: none !important; text-decoration: none !important;">
                {{ $menu->name }}
            </a>
            <ul class="dropdown-menu" aria-labelledby="menu{{ $menu->id }}Dropdown">
                @php
                    $parentIsActive = false;
                    if ($menuUrl !== '#' && $menuUrl !== '') {
                        $menuPath = parse_url($menuUrl, PHP_URL_PATH);
                        if ($menuPath && strpos($currentPath, trim($menuPath, '/')) === 0) {
                            $parentIsActive = true;
                        }
                    }
                @endphp
                <li>
                    <a class="dropdown-item {{ $parentIsActive ? 'active' : '' }}" href="{{ $menu->url }}" @if($menu->font_color) style="color: {{ $menu->font_color }};" @endif>
                        {{ $menu->name }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @foreach($menu->children as $child)
                    @php
                        $childUrl = $child->url;
                        $childIsActive = false;
                        if ($childUrl !== '#' && $childUrl !== '') {
                            $childPath = parse_url($childUrl, PHP_URL_PATH);
                            if ($childPath && strpos($currentPath, trim($childPath, '/')) === 0) {
                                $childIsActive = true;
                            }
                        }
                        $childFontColor = $child->font_color ?? null;
                    @endphp
                    <li>
                        <a class="dropdown-item {{ $childIsActive ? 'active' : '' }}" href="{{ $childUrl }}" @if($childFontColor) style="color: {{ $childFontColor }};" @endif>
                            {{ $child->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    @else
        <li class="nav-item" style="margin-right: {{ $menuFontPadding }};">
            <a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ $menu->url }}" data-menu-hover="true" style="font-size: {{ $menuFontSize }}; font-weight: {{ $menuFontWeight }}; padding: {{ $menuFontPadding }}; color: {{ $isActive ? $pointColor : $menuItemFontColor }};">
                {{ $menu->name }}
            </a>
        </li>
    @endif
@endforeach

