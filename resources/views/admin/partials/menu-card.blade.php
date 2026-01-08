@php
    $linkTypeLabels = [
        'board' => '게시판',
        'custom_page' => '커스텀 페이지',
        'external_link' => '외부링크',
        'anchor' => '컨테이너(앵커)',
        'attendance' => '출첵페이지',
        'point_exchange' => '포인트교환페이지',
        'event_application' => '신청형 이벤트 페이지',
    ];
    
    $linkTargetDisplay = '';
    if ($menu->link_type === 'board') {
        $board = \App\Models\Board::find($menu->link_target);
        $linkTargetDisplay = $board ? $board->name : '알 수 없음';
    } elseif ($menu->link_type === 'custom_page') {
        $customPage = \App\Models\CustomPage::find($menu->link_target);
        $linkTargetDisplay = $customPage ? $customPage->name : '알 수 없음';
    } elseif ($menu->link_type === 'external_link') {
        $linkTargetDisplay = $menu->link_target ?? '';
    } elseif ($menu->link_type === 'anchor') {
        $linkTargetDisplay = '#' . ($menu->link_target ?? '');
    } else {
        $linkTargetDisplay = '-';
    }
    
    // 전체 메뉴 폰트 컬러 또는 개별 메뉴 폰트 컬러
    $displayFontColor = $menu->font_color ?? ($globalMenuFontColor ?? null);
@endphp

<div class="card mb-3 menu-card" data-menu-id="{{ $menu->id }}" data-parent-id="{{ $menu->parent_id }}" style="{{ $level > 0 ? 'margin-left: 1.5rem; border-left: 3px solid #0d6efd;' : '' }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
                <h6 class="mb-1" style="{{ $level > 0 ? 'color: #6c757d;' : '' }}">
                    @if($level > 0)
                        <span class="text-muted">└─</span>
                    @endif
                    @if($displayFontColor)
                        <span style="color: {{ $displayFontColor }}; font-weight: 500;">{{ $menu->name }}</span>
                        <span class="badge bg-light text-dark ms-1" style="font-size: 10px;">{{ $displayFontColor }}</span>
                    @else
                        {{ $menu->name }}
                    @endif
                </h6>
                <div class="small text-muted">
                    <div><strong>연결 타입:</strong> {{ $linkTypeLabels[$menu->link_type] ?? $menu->link_type }}</div>
                    <div><strong>연결 대상:</strong> {{ $linkTargetDisplay }}</div>
                    @if($displayFontColor)
                        <div><strong>폰트 컬러:</strong> 
                            <span style="background-color: {{ $displayFontColor }}; width: 12px; height: 12px; display: inline-block; border: 1px solid #dee2e6; border-radius: 2px; vertical-align: middle;"></span>
                            {{ $displayFontColor }}
                            @if(!$menu->font_color && $globalMenuFontColor)
                                <span class="text-info">(전체설정)</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="order-buttons d-flex gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary order-up-btn order-btn" title="위로" data-menu-id="{{ $menu->id }}">
                    <i class="bi bi-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary order-down-btn order-btn" title="아래로" data-menu-id="{{ $menu->id }}">
                    <i class="bi bi-arrow-down"></i>
                </button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary add-submenu-btn" data-menu-id="{{ $menu->id }}">
                <i class="bi bi-plus-circle me-1"></i>하위 메뉴
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger delete-menu-btn" data-menu-id="{{ $menu->id }}">
                <i class="bi bi-trash me-1"></i>삭제
            </button>
        </div>
    </div>
</div>

@if($menu->children && $menu->children->count() > 0)
    @foreach($menu->children as $child)
        @include('admin.partials.menu-card', ['menu' => $child, 'level' => $level + 1])
    @endforeach
@endif

