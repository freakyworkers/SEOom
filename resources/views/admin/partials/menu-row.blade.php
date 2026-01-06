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
    
    $rowClass = $level > 0 ? 'submenu-row' : 'menu-row';
@endphp

<tr class="{{ $rowClass }} menu-row" data-menu-id="{{ $menu->id }}" data-parent-id="{{ $menu->parent_id }}">
    <td>
        @if($level > 0)
            <span class="text-muted">└─</span>
        @endif
        {{ $menu->name }}
    </td>
    <td>{{ $linkTypeLabels[$menu->link_type] ?? $menu->link_type }}</td>
    <td>{{ $linkTargetDisplay }}</td>
    <td>
        <div class="order-buttons">
            <button type="button" class="btn btn-sm btn-outline-secondary order-up-btn order-btn" title="위로">
                <i class="bi bi-arrow-up"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary order-down-btn order-btn" title="아래로">
                <i class="bi bi-arrow-down"></i>
            </button>
        </div>
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-outline-primary add-submenu-btn" data-menu-id="{{ $menu->id }}">
            하위 메뉴 추가
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger delete-menu-btn" data-menu-id="{{ $menu->id }}">
            삭제
        </button>
    </td>
</tr>

@if($menu->children && $menu->children->count() > 0)
    @foreach($menu->children as $child)
        @include('admin.partials.menu-row', ['menu' => $child, 'level' => $level + 1])
    @endforeach
@endif


