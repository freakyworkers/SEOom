@php
    $user = $user ?? null;
    $site = $site ?? null;
    $siteId = $siteId ?? (is_object($site) ? $site->id : ($user->site_id ?? null));
    
    if (!$user || !$siteId) {
        return;
    }

    // Site 객체 가져오기
    if (!is_object($site) && $siteId) {
        $site = \App\Models\Site::find($siteId);
    }

    // 관리자/매니저 아이콘 확인
    $adminIcon = null;
    $managerIcon = null;
    if ($site) {
        $adminIcon = $site->getSetting('admin_icon_path', '');
        $managerIcon = $site->getSetting('manager_icon_path', '');
    }

    $userRank = null;
    $displayType = 'icon';
    
    if ($user->isAdmin() && $adminIcon) {
        // 관리자 아이콘 표시
        $displayType = 'admin_icon';
    } elseif ($user->isManager() && $managerIcon) {
        // 매니저 아이콘 표시
        $displayType = 'manager_icon';
    } else {
        // 일반 사용자 등급
        $userRank = $user->getUserRank($siteId);
        if ($userRank && $site) {
            $displayType = $site->getSetting('rank_display_type', 'icon');
        }
    }
@endphp

@if($userRank || $displayType === 'admin_icon' || $displayType === 'manager_icon')
    @if($displayType === 'admin_icon' && $adminIcon)
        <img src="{{ asset('storage/' . $adminIcon) }}" alt="Admin" style="width: 20px; height: 20px; object-fit: contain; vertical-align: middle; margin-right: 4px;">
    @elseif($displayType === 'manager_icon' && $managerIcon)
        <img src="{{ asset('storage/' . $managerIcon) }}" alt="Manager" style="width: 20px; height: 20px; object-fit: contain; vertical-align: middle; margin-right: 4px;">
    @elseif($userRank)
        @if($displayType === 'icon' && $userRank->icon_path)
            <img src="{{ asset('storage/' . $userRank->icon_path) }}" alt="{{ $userRank->name }}" style="width: 20px; height: 20px; object-fit: contain; vertical-align: middle; margin-right: 4px;">
        @elseif($displayType === 'color' && $userRank->color)
            <span style="color: {{ $userRank->color }}; font-weight: bold; margin-right: 4px;">{{ $userRank->name }}</span>
        @endif
    @endif
@endif

