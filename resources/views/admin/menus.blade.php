@extends('layouts.admin')

@section('title', '메뉴 설정')
@section('page-title', '메뉴 설정')
@section('page-subtitle', '헤더에 표시될 메뉴를 관리합니다')

@push('styles')
<style>
    .menu-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .menu-table th,
    .menu-table td {
        border: 1px solid #dee2e6;
        padding: 0.75rem;
        vertical-align: middle;
    }
    .menu-table th {
        text-align: center;
    }
    .menu-table td {
        text-align: center;
    }
    .menu-table td input,
    .menu-table td select {
        text-align: center;
    }
    .menu-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .menu-row {
        background-color: #fff;
    }
    .menu-row:hover {
        background-color: #f8f9fa;
    }
    .submenu-row {
        background-color: #f8f9fa;
        padding-left: 2rem !important;
    }
    .order-buttons {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
        align-items: center;
    }
    .order-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .link-target-input {
        display: none;
    }
    .link-target-input.show {
        display: block;
    }
</style>
@endpush

@section('content')
<!-- 새로 생성 섹션 -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">새로 생성</h5>
    </div>
    <div class="card-body">
        <form id="newMenuForm">
            <div class="row g-3">
                <div class="col-md-2">
                    <label for="new_menu_name" class="form-label">이름</label>
                    <input type="text" class="form-control" id="new_menu_name" placeholder="예) 뉴스" required>
                </div>
                <div class="col-md-2">
                    <label for="new_link_type" class="form-label">연결 타입</label>
                    <select class="form-select" id="new_link_type" required>
                        <option value="">선택하세요</option>
                        <option value="board">게시판</option>
                        <option value="custom_page">커스텀 페이지</option>
                        <option value="external_link">외부링크</option>
                        <option value="anchor">컨테이너(앵커)</option>
                        @if($siteFeatures['attendance'] ?? false)
                        <option value="attendance">출첵페이지</option>
                        @endif
                        @if($siteFeatures['point_exchange'] ?? false)
                        <option value="point_exchange">포인트교환페이지</option>
                        @endif
                        @if($siteFeatures['event_application'] ?? false)
                        <option value="event_application">신청형 이벤트 페이지</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="new_link_target" class="form-label">연결 대상</label>
                    <select class="form-select link-target-select" id="new_link_target_board" style="display: none;">
                        <option value="">게시판 선택</option>
                        @foreach($boards as $board)
                            <option value="{{ $board->id }}">{{ $board->name }}</option>
                        @endforeach
                    </select>
                    <select class="form-select link-target-select" id="new_link_target_custom_page" style="display: none;">
                        <option value="">페이지 선택</option>
                        @foreach($customPages as $customPage)
                            <option value="{{ $customPage->id }}">{{ $customPage->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" class="form-control link-target-input" id="new_link_target_external" placeholder="https://example.com" style="display: none;">
                    <select class="form-select link-target-select" id="new_link_target_anchor" style="display: none;">
                        <option value="">앵커 선택</option>
                        @foreach($containerAnchors ?? [] as $anchor)
                            <option value="{{ $anchor['id'] }}">{{ $anchor['label'] }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-none" id="new_link_target_anchor_help">컨테이너에 설정한 앵커 ID를 선택하세요. 클릭 시 해당 컨테이너로 스크롤됩니다.</small>
                    <div class="link-target-placeholder" style="display: none;"></div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">등록</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 메뉴 목록 -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">메뉴 목록</h5>
    </div>
    <div class="card-body">
        <form id="menuOrderForm">
            {{-- 데스크탑 버전 (기존 테이블) --}}
            <div class="d-none d-md-block">
                <table class="table menu-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">이름</th>
                            <th style="width: 12%;">연결 타입</th>
                            <th style="width: 20%;">연결 대상</th>
                            <th style="width: 15%;">폰트 컬러</th>
                            <th style="width: 10%;">표시 순서</th>
                            <th style="width: 28%;">작업</th>
                        </tr>
                    </thead>
                    <tbody id="menuListBody">
                        @if($menus->count() > 0)
                            @foreach($menus as $menu)
                                @include('admin.partials.menu-row', ['menu' => $menu, 'level' => 0])
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">등록된 메뉴가 없습니다.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- 모바일 버전 (카드 레이아웃) --}}
            <div class="d-md-none" id="mobileMenuListBody">
                @if($menus->count() > 0)
                    @foreach($menus as $menu)
                        @include('admin.partials.menu-card', ['menu' => $menu, 'level' => 0])
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">등록된 메뉴가 없습니다.</div>
                @endif
            </div>
            
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<!-- 모바일 하단 메뉴 새로 생성 섹션 -->
<div class="card mb-4 shadow-sm mt-5">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-phone me-2"></i>모바일 하단 메뉴 새로 생성</h5>
    </div>
    <div class="card-body">
        <form id="newMobileMenuForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-start">
                <div class="col-md-2">
                    <label for="new_mobile_icon_type" class="form-label">아이콘 타입</label>
                    <select class="form-select" id="new_mobile_icon_type" name="icon_type" required>
                        <option value="default">기본 아이콘</option>
                        <option value="image">이미지</option>
                        <option value="emoji">이모지</option>
                    </select>
                </div>
                <div class="col-md-3" id="new_mobile_icon_section">
                    <label class="form-label">아이콘 선택</label>
                    <div id="new_mobile_default_icon_section">
                        <input type="text" class="form-control" id="new_mobile_icon_search" placeholder="검색어(영어로)" autocomplete="off">
                        <div class="icon-grid mt-2" id="new_mobile_icon_grid" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 0.375rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(40px, 1fr)); gap: 5px;">
                            <!-- Bootstrap Icons will be populated here -->
                        </div>
                        <input type="hidden" id="new_mobile_icon_path" name="icon_path">
                    </div>
                    <div id="new_mobile_image_icon_section" style="display: none;">
                        <input type="file" class="form-control" id="new_mobile_icon_file" name="icon_file" accept="image/*">
                        <div id="new_mobile_icon_preview" class="mt-2" style="display: none;">
                            <img id="new_mobile_icon_preview_img" src="" alt="미리보기" style="max-width: 60px; max-height: 60px; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 5px;">
                        </div>
                    </div>
                    <div id="new_mobile_emoji_icon_section" style="display: none;">
                        <div class="emoji-categories mb-2" style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-sm btn-outline-secondary emoji-category-btn active" data-category="smileys">😀 얼굴</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary emoji-category-btn" data-category="animals">🐶 동물</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary emoji-category-btn" data-category="food">🍕 음식</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary emoji-category-btn" data-category="activities">⚽ 활동</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary emoji-category-btn" data-category="travel">🚗 여행</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary emoji-category-btn" data-category="objects">💡 물건</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary emoji-category-btn" data-category="symbols">❤️ 심볼</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary emoji-category-btn" data-category="flags">🏳️ 깃발</button>
                        </div>
                        <input type="text" class="form-control mb-2" id="new_mobile_emoji_search" placeholder="이모지 검색" autocomplete="off">
                        <div class="emoji-grid mt-2" id="new_mobile_emoji_grid" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 0.375rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(40px, 1fr)); gap: 5px; font-size: 24px; text-align: center;">
                            <!-- Emojis will be populated here -->
                        </div>
                        <input type="hidden" id="new_mobile_emoji_path" name="icon_path">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="new_mobile_menu_name" class="form-label">
                        이름
                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="이름을 작성하지 않으면 아이콘만 표시됩니다." style="cursor: help; color: #6c757d; margin-left: 4px;"></i>
                    </label>
                    <input type="text" class="form-control" id="new_mobile_menu_name" name="name" placeholder="예) 뉴스">
                </div>
                <div class="col-md-2">
                    <label for="new_mobile_link_type" class="form-label">연결 타입</label>
                    <select class="form-select" id="new_mobile_link_type" name="link_type" required>
                        <option value="">선택하세요</option>
                        <option value="board">게시판</option>
                        <option value="custom_page">커스텀 페이지</option>
                        <option value="external_link">외부링크</option>
                        @if($siteFeatures['attendance'] ?? false)
                        <option value="attendance">출첵페이지</option>
                        @endif
                        @if($siteFeatures['point_exchange'] ?? false)
                        <option value="point_exchange">포인트교환페이지</option>
                        @endif
                        @if($siteFeatures['event_application'] ?? false)
                        <option value="event_application">신청형 이벤트 페이지</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="new_mobile_link_target" class="form-label">연결 대상</label>
                    <select class="form-select" id="new_mobile_link_target_board" name="link_target" style="display: none;">
                        <option value="">게시판 선택</option>
                        @foreach($boards as $board)
                            <option value="{{ $board->id }}">{{ $board->name }}</option>
                        @endforeach
                    </select>
                    <select class="form-select" id="new_mobile_link_target_custom_page" name="link_target" style="display: none;">
                        <option value="">페이지 선택</option>
                        @foreach($customPages as $customPage)
                            <option value="{{ $customPage->id }}">{{ $customPage->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" class="form-control" id="new_mobile_link_target_external" name="link_target" placeholder="https://example.com" style="display: none;">
                    <div class="link-target-placeholder" style="display: none;"></div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">등록</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 모바일 하단 메뉴 목록 -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0"><i class="bi bi-phone me-2"></i>모바일 하단 메뉴 목록</h5>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <label for="mobile_menu_design_type" class="form-label mb-0">모바일 메뉴 디자인:</label>
                <select class="form-select form-select-sm" id="mobile_menu_design_type" style="width: auto; min-width: 150px;">
                    <option value="default" {{ ($mobileMenuDesignType ?? 'default') === 'default' ? 'selected' : '' }}>기본타입</option>
                    <option value="top_round" {{ ($mobileMenuDesignType ?? 'default') === 'top_round' ? 'selected' : '' }}>상단라운드</option>
                    <option value="round" {{ ($mobileMenuDesignType ?? 'default') === 'round' ? 'selected' : '' }}>라운드</option>
                    <option value="glass" {{ ($mobileMenuDesignType ?? 'default') === 'glass' ? 'selected' : '' }}>글래스 디자인</option>
                </select>
            </div>
        </div>
        <div class="mt-3" id="mobile_menu_color_settings" style="display: none;">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div id="mobile_menu_bg_color_wrapper" class="d-flex align-items-center gap-2" style="display: none;">
                    <label for="mobile_menu_bg_color" class="form-label mb-0">배경 컬러:</label>
                    <input type="color" class="form-control form-control-color" id="mobile_menu_bg_color" value="{{ $site->getSetting('mobile_menu_bg_color', '#ffffff') }}" style="width: 50px; height: 38px;">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="mobile_menu_font_color" class="form-label mb-0">폰트 컬러:</label>
                    <input type="color" class="form-control form-control-color" id="mobile_menu_font_color" value="{{ $site->getSetting('mobile_menu_font_color', '#495057') }}" style="width: 50px; height: 38px;">
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form id="mobileMenuOrderForm">
            {{-- 데스크탑 버전 (기존 테이블) --}}
            <div class="d-none d-md-block">
                <table class="table menu-table">
                    <thead>
                        <tr>
                            <th style="width: 10%; text-align: center;">아이콘</th>
                            <th style="width: 15%; text-align: center;">이름</th>
                            <th style="width: 15%; text-align: center;">연결 타입</th>
                            <th style="width: 20%; text-align: center;">연결 대상</th>
                            <th style="width: 10%; text-align: center;">표시 순서</th>
                            <th style="width: 30%; text-align: center;">작업</th>
                        </tr>
                    </thead>
                    <tbody id="mobileMenuListBody">
                        @if($mobileMenus->count() > 0)
                            @foreach($mobileMenus as $mobileMenu)
                                <tr data-mobile-menu-id="{{ $mobileMenu->id }}">
                                    <td style="text-align: center;">
                                        @if($mobileMenu->icon_type === 'image' && $mobileMenu->icon_path)
                                            <img src="{{ asset('storage/' . $mobileMenu->icon_path) }}" alt="{{ $mobileMenu->name }}" style="max-width: 40px; max-height: 40px;">
                                        @elseif($mobileMenu->icon_type === 'emoji' && $mobileMenu->icon_path)
                                            <span style="font-size: 24px;">{{ $mobileMenu->icon_path }}</span>
                                        @else
                                            <i class="{{ $mobileMenu->icon_path ?? 'bi bi-circle' }}" style="font-size: 24px;"></i>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="text" class="form-control form-control-sm mobile-menu-name-input" value="{{ $mobileMenu->name }}" data-menu-id="{{ $mobileMenu->id }}">
                                    </td>
                                    <td style="text-align: center;">
                                        <select class="form-select form-select-sm mobile-menu-link-type-select" data-menu-id="{{ $mobileMenu->id }}">
                                            <option value="board" {{ $mobileMenu->link_type === 'board' ? 'selected' : '' }}>게시판</option>
                                            <option value="custom_page" {{ $mobileMenu->link_type === 'custom_page' ? 'selected' : '' }}>커스텀 페이지</option>
                                            <option value="external_link" {{ $mobileMenu->link_type === 'external_link' ? 'selected' : '' }}>외부링크</option>
                                            @if($siteFeatures['attendance'] ?? false)
                                            <option value="attendance" {{ $mobileMenu->link_type === 'attendance' ? 'selected' : '' }}>출첵페이지</option>
                                            @endif
                                            @if($siteFeatures['point_exchange'] ?? false)
                                            <option value="point_exchange" {{ $mobileMenu->link_type === 'point_exchange' ? 'selected' : '' }}>포인트교환페이지</option>
                                            @endif
                                            @if($siteFeatures['event_application'] ?? false)
                                            <option value="event_application" {{ $mobileMenu->link_type === 'event_application' ? 'selected' : '' }}>신청형 이벤트 페이지</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td style="text-align: center;">
                                        <select class="form-select form-select-sm mobile-menu-link-target-board" data-menu-id="{{ $mobileMenu->id }}" style="{{ $mobileMenu->link_type === 'board' ? 'display: block;' : 'display: none;' }}">
                                            <option value="">게시판 선택</option>
                                            @foreach($boards as $board)
                                                <option value="{{ $board->id }}" {{ (string)$mobileMenu->link_target === (string)$board->id ? 'selected' : '' }}>{{ $board->name }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-select form-select-sm mobile-menu-link-target-custom-page" data-menu-id="{{ $mobileMenu->id }}" style="{{ $mobileMenu->link_type === 'custom_page' ? 'display: block;' : 'display: none;' }}">
                                            <option value="">페이지 선택</option>
                                            @foreach($customPages as $customPage)
                                                <option value="{{ $customPage->id }}" {{ (string)$mobileMenu->link_target === (string)$customPage->id ? 'selected' : '' }}>{{ $customPage->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" class="form-control form-control-sm mobile-menu-link-target-external" value="{{ $mobileMenu->link_type === 'external_link' ? $mobileMenu->link_target : '' }}" data-menu-id="{{ $mobileMenu->id }}" placeholder="외부 링크 URL" style="{{ $mobileMenu->link_type === 'external_link' ? 'display: block;' : 'display: none;' }}">
                                        <span class="form-text text-muted mobile-menu-link-target-placeholder" style="{{ !in_array($mobileMenu->link_type, ['board', 'custom_page', 'external_link']) ? 'display: block;' : 'display: none;' }}">연결 타입에 따라 입력 필드가 나타납니다.</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="d-flex flex-column align-items-center">
                                            <button type="button" class="btn btn-sm btn-outline-secondary mb-1 mobile-menu-move-up-btn" data-menu-id="{{ $mobileMenu->id }}">
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary mobile-menu-move-down-btn" data-menu-id="{{ $mobileMenu->id }}">
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-danger rounded-3 delete-mobile-menu-btn" data-menu-id="{{ $mobileMenu->id }}">
                                                <i class="bi bi-trash"></i> 삭제
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">등록된 모바일 하단 메뉴가 없습니다.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- 모바일 버전 (카드 레이아웃) --}}
            <div class="d-md-none" id="mobileMenuListBodyCards">
                @if($mobileMenus->count() > 0)
                    @foreach($mobileMenus as $mobileMenu)
                        <div class="card mb-3 mobile-menu-card" data-mobile-menu-id="{{ $mobileMenu->id }}">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="flex-shrink-0" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                        @if($mobileMenu->icon_type === 'image' && $mobileMenu->icon_path)
                                            <img src="{{ asset('storage/' . $mobileMenu->icon_path) }}" alt="{{ $mobileMenu->name }}" style="max-width: 50px; max-height: 50px; border-radius: 0.375rem;">
                                        @elseif($mobileMenu->icon_type === 'emoji' && $mobileMenu->icon_path)
                                            <span style="font-size: 32px;">{{ $mobileMenu->icon_path }}</span>
                                        @else
                                            <i class="{{ $mobileMenu->icon_path ?? 'bi bi-circle' }}" style="font-size: 32px;"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label small text-muted mb-1">이름</label>
                                        <input type="text" class="form-control form-control-sm mobile-menu-name-input" value="{{ $mobileMenu->name }}" data-menu-id="{{ $mobileMenu->id }}" placeholder="메뉴 이름">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted mb-1">연결 타입</label>
                                    <select class="form-select form-select-sm mobile-menu-link-type-select" data-menu-id="{{ $mobileMenu->id }}">
                                        <option value="board" {{ $mobileMenu->link_type === 'board' ? 'selected' : '' }}>게시판</option>
                                        <option value="custom_page" {{ $mobileMenu->link_type === 'custom_page' ? 'selected' : '' }}>커스텀 페이지</option>
                                        <option value="external_link" {{ $mobileMenu->link_type === 'external_link' ? 'selected' : '' }}>외부링크</option>
                                        @if($siteFeatures['attendance'] ?? false)
                                        <option value="attendance" {{ $mobileMenu->link_type === 'attendance' ? 'selected' : '' }}>출첵페이지</option>
                                        @endif
                                        @if($siteFeatures['point_exchange'] ?? false)
                                        <option value="point_exchange" {{ $mobileMenu->link_type === 'point_exchange' ? 'selected' : '' }}>포인트교환페이지</option>
                                        @endif
                                        @if($siteFeatures['event_application'] ?? false)
                                        <option value="event_application" {{ $mobileMenu->link_type === 'event_application' ? 'selected' : '' }}>신청형 이벤트 페이지</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted mb-1">연결 대상</label>
                                    <select class="form-select form-select-sm mobile-menu-link-target-board" data-menu-id="{{ $mobileMenu->id }}" style="{{ $mobileMenu->link_type === 'board' ? 'display: block;' : 'display: none;' }}">
                                        <option value="">게시판 선택</option>
                                        @foreach($boards as $board)
                                            <option value="{{ $board->id }}" {{ (string)$mobileMenu->link_target === (string)$board->id ? 'selected' : '' }}>{{ $board->name }}</option>
                                        @endforeach
                                    </select>
                                    <select class="form-select form-select-sm mobile-menu-link-target-custom-page" data-menu-id="{{ $mobileMenu->id }}" style="{{ $mobileMenu->link_type === 'custom_page' ? 'display: block;' : 'display: none;' }}">
                                        <option value="">페이지 선택</option>
                                        @foreach($customPages as $customPage)
                                            <option value="{{ $customPage->id }}" {{ (string)$mobileMenu->link_target === (string)$customPage->id ? 'selected' : '' }}>{{ $customPage->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" class="form-control form-control-sm mobile-menu-link-target-external" value="{{ $mobileMenu->link_type === 'external_link' ? $mobileMenu->link_target : '' }}" data-menu-id="{{ $mobileMenu->id }}" placeholder="외부 링크 URL" style="{{ $mobileMenu->link_type === 'external_link' ? 'display: block;' : 'display: none;' }}">
                                    <span class="form-text text-muted small mobile-menu-link-target-placeholder" style="{{ !in_array($mobileMenu->link_type, ['board', 'custom_page', 'external_link']) ? 'display: block;' : 'display: none;' }}">연결 타입에 따라 입력 필드가 나타납니다.</span>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <div class="order-buttons d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary mobile-menu-move-up-btn" data-menu-id="{{ $mobileMenu->id }}" title="위로">
                                            <i class="bi bi-arrow-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mobile-menu-move-down-btn" data-menu-id="{{ $mobileMenu->id }}" title="아래로">
                                            <i class="bi bi-arrow-down"></i>
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger delete-mobile-menu-btn" data-menu-id="{{ $mobileMenu->id }}">
                                        <i class="bi bi-trash me-1"></i>삭제
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">등록된 모바일 하단 메뉴가 없습니다.</div>
                @endif
            </div>
            
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 연결 타입 변경 시 연결 대상 필드 표시/숨김
    const newLinkType = document.getElementById('new_link_type');
    const newLinkTargetBoard = document.getElementById('new_link_target_board');
    const newLinkTargetCustomPage = document.getElementById('new_link_target_custom_page');
    const newLinkTargetExternal = document.getElementById('new_link_target_external');
    const linkTargetPlaceholder = document.querySelector('.link-target-placeholder');
    const linkTargetLabel = document.querySelector('label[for="new_link_target"]');

    const newLinkTargetAnchor = document.getElementById('new_link_target_anchor');
    const newLinkTargetAnchorHelp = document.getElementById('new_link_target_anchor_help');

    newLinkType.addEventListener('change', function() {
        // 모든 연결 대상 필드 숨김
        newLinkTargetBoard.style.display = 'none';
        newLinkTargetCustomPage.style.display = 'none';
        newLinkTargetExternal.style.display = 'none';
        newLinkTargetAnchor.style.display = 'none';
        newLinkTargetAnchorHelp.classList.add('d-none');
        linkTargetPlaceholder.style.display = 'none';
        linkTargetLabel.style.display = 'block';

        const linkType = this.value;
        if (linkType === 'board') {
            newLinkTargetBoard.style.display = 'block';
            newLinkTargetBoard.required = true;
            newLinkTargetCustomPage.required = false;
            newLinkTargetExternal.required = false;
            newLinkTargetAnchor.required = false;
        } else if (linkType === 'custom_page') {
            newLinkTargetCustomPage.style.display = 'block';
            newLinkTargetCustomPage.required = true;
            newLinkTargetBoard.required = false;
            newLinkTargetExternal.required = false;
            newLinkTargetAnchor.required = false;
        } else if (linkType === 'external_link') {
            newLinkTargetExternal.style.display = 'block';
            newLinkTargetExternal.required = true;
            newLinkTargetBoard.required = false;
            newLinkTargetCustomPage.required = false;
            newLinkTargetAnchor.required = false;
        } else if (linkType === 'anchor') {
            newLinkTargetAnchor.style.display = 'block';
            newLinkTargetAnchorHelp.classList.remove('d-none');
            newLinkTargetAnchor.required = true;
            newLinkTargetBoard.required = false;
            newLinkTargetCustomPage.required = false;
            newLinkTargetExternal.required = false;
        } else if (['attendance', 'point_exchange', 'event_application'].includes(linkType)) {
            linkTargetPlaceholder.style.display = 'block';
            linkTargetLabel.style.display = 'none';
            newLinkTargetBoard.required = false;
            newLinkTargetCustomPage.required = false;
            newLinkTargetExternal.required = false;
            newLinkTargetAnchor.required = false;
        } else {
            newLinkTargetBoard.required = false;
            newLinkTargetCustomPage.required = false;
            newLinkTargetExternal.required = false;
            newLinkTargetAnchor.required = false;
        }
    });

    // 새 메뉴 등록
    document.getElementById('newMenuForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('new_menu_name').value;
        const linkType = document.getElementById('new_link_type').value;
        let linkTarget = null;

        if (linkType === 'board') {
            linkTarget = document.getElementById('new_link_target_board').value;
        } else if (linkType === 'custom_page') {
            linkTarget = document.getElementById('new_link_target_custom_page').value;
        } else if (linkType === 'external_link') {
            linkTarget = document.getElementById('new_link_target_external').value;
        } else if (linkType === 'anchor') {
            linkTarget = document.getElementById('new_link_target_anchor').value;
        }

        // 기본 폰트 컬러는 #000000으로 자동 적용
        const fontColor = '#000000';

        fetch('{{ route("admin.menus.store", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                link_type: linkType,
                link_target: linkTarget,
                font_color: fontColor
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '메뉴 추가에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('메뉴 추가 중 오류가 발생했습니다.');
        });
    });

    // 메뉴 순서 저장
    document.getElementById('menuOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const menus = [];
        // 데스크탑 테이블 또는 모바일 카드에서 메뉴 정보 수집
        const menuElements = document.querySelectorAll('.menu-row, .menu-card');
        menuElements.forEach((element, index) => {
            const menuId = element.dataset.menuId;
            const parentId = element.dataset.parentId || null;
            if (menuId) {
                menus.push({
                    id: menuId,
                    order: index + 1,
                    parent_id: parentId
                });
            }
        });

        fetch('{{ route("admin.menus.update-order", ["site" => $site->slug]) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                menus: menus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('메뉴 순서가 저장되었습니다.');
                location.reload();
            } else {
                alert(data.message || '메뉴 순서 저장에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('메뉴 순서 저장 중 오류가 발생했습니다.');
        });
    });

    // 표시 순서 상하 조정 (데스크탑 테이블 및 모바일 카드 모두 지원)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.order-up-btn')) {
            const btn = e.target.closest('.order-up-btn');
            const row = btn.closest('.menu-row, .menu-card');
            if (!row) return;
            
            const container = row.parentNode;
            const prevRow = row.previousElementSibling;
            if (prevRow && (prevRow.classList.contains('menu-row') || prevRow.classList.contains('menu-card'))) {
                container.insertBefore(row, prevRow);
            }
        } else if (e.target.closest('.order-down-btn')) {
            const btn = e.target.closest('.order-down-btn');
            const row = btn.closest('.menu-row, .menu-card');
            if (!row) return;
            
            const container = row.parentNode;
            const nextRow = row.nextElementSibling;
            if (nextRow && (nextRow.classList.contains('menu-row') || nextRow.classList.contains('menu-card'))) {
                container.insertBefore(nextRow, row);
            }
        }
    });

    // 메뉴 삭제
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-menu-btn')) {
            if (!confirm('이 메뉴를 삭제하시겠습니까? 하위 메뉴도 함께 삭제됩니다.')) {
                return;
            }

            const menuId = e.target.closest('.delete-menu-btn').dataset.menuId;
            
            fetch(`/site/{{ $site->slug }}/admin/menus/${menuId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '메뉴 삭제에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('메뉴 삭제 중 오류가 발생했습니다.');
            });
        }
    });

    // 하위 메뉴 추가 (데스크탑 테이블 및 모바일 카드 모두 지원)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-submenu-btn')) {
            const menuId = e.target.closest('.add-submenu-btn').dataset.menuId;
            const row = e.target.closest('.menu-row, .menu-card');
            if (!row) return;
            
            // 기존 하위 메뉴 폼이 있으면 제거
            const existingForm = row.parentNode.querySelector('.submenu-form-row, .submenu-form-card');
            if (existingForm) {
                existingForm.remove();
            }
            
            const isMobile = row.classList.contains('menu-card');
            let submenuForm;
            
            if (isMobile) {
                // 모바일 카드 형태
                submenuForm = document.createElement('div');
                submenuForm.className = 'submenu-form-card card mb-3 bg-light';
                submenuForm.innerHTML = `
                    <div class="card-body">
                        <form class="submenu-form">
                            <div class="mb-3">
                                <input type="text" class="form-control form-control-sm" name="name" placeholder="이름" required>
                            </div>
                            <div class="mb-3">
                                <select class="form-select form-select-sm" name="link_type" required>
                                    <option value="">선택하세요</option>
                                    <option value="board">게시판</option>
                                    <option value="custom_page">커스텀 페이지</option>
                                    <option value="external_link">외부링크</option>
                                    <option value="anchor">컨테이너(앵커)</option>
                                    @if($siteFeatures['attendance'] ?? false)
                                    <option value="attendance">출첵페이지</option>
                                    @endif
                                    @if($siteFeatures['point_exchange'] ?? false)
                                    <option value="point_exchange">포인트교환페이지</option>
                                    @endif
                                    @if($siteFeatures['event_application'] ?? false)
                                    <option value="event_application">신청형 이벤트 페이지</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <select class="form-select form-select-sm submenu-link-target-board" name="link_target_board" style="display: none;">
                                    <option value="">게시판 선택</option>
                                    @foreach($boards as $board)
                                        <option value="{{ $board->id }}">{{ $board->name }}</option>
                                    @endforeach
                                </select>
                                <select class="form-select form-select-sm submenu-link-target-custom-page" name="link_target_custom_page" style="display: none;">
                                    <option value="">페이지 선택</option>
                                    @foreach($customPages as $customPage)
                                        <option value="{{ $customPage->id }}">{{ $customPage->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" class="form-control form-control-sm submenu-link-target-external" name="link_target_external" placeholder="https://example.com" style="display: none;">
                                <select class="form-select form-select-sm submenu-link-target-anchor" name="link_target_anchor" style="display: none;">
                                    <option value="">앵커 선택</option>
                                    @foreach($containerAnchors ?? [] as $anchor)
                                        <option value="{{ $anchor['id'] }}">{{ $anchor['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">추가</button>
                                <button type="button" class="btn btn-sm btn-secondary cancel-submenu-btn">취소</button>
                            </div>
                        </form>
                    </div>
                `;
            } else {
                // 데스크탑 테이블 형태
                submenuForm = document.createElement('tr');
                submenuForm.className = 'submenu-form-row';
                submenuForm.innerHTML = `
                    <td colspan="5" class="bg-light p-3">
                        <form class="submenu-form">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="name" placeholder="이름" required>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="link_type" required>
                                        <option value="">선택하세요</option>
                                        <option value="board">게시판</option>
                                        <option value="custom_page">커스텀 페이지</option>
                                        <option value="external_link">외부링크</option>
                                        <option value="anchor">컨테이너(앵커)</option>
                                        @if($siteFeatures['attendance'] ?? false)
                                        <option value="attendance">출첵페이지</option>
                                        @endif
                                        @if($siteFeatures['point_exchange'] ?? false)
                                        <option value="point_exchange">포인트교환페이지</option>
                                        @endif
                                        @if($siteFeatures['event_application'] ?? false)
                                        <option value="event_application">신청형 이벤트 페이지</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select submenu-link-target-board" name="link_target_board" style="display: none;">
                                        <option value="">게시판 선택</option>
                                        @foreach($boards as $board)
                                            <option value="{{ $board->id }}">{{ $board->name }}</option>
                                        @endforeach
                                    </select>
                                    <select class="form-select submenu-link-target-custom-page" name="link_target_custom_page" style="display: none;">
                                        <option value="">페이지 선택</option>
                                        @foreach($customPages as $customPage)
                                            <option value="{{ $customPage->id }}">{{ $customPage->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" class="form-control submenu-link-target-external" name="link_target_external" placeholder="https://example.com" style="display: none;">
                                    <select class="form-select submenu-link-target-anchor" name="link_target_anchor" style="display: none;">
                                        <option value="">앵커 선택</option>
                                        @foreach($containerAnchors ?? [] as $anchor)
                                            <option value="{{ $anchor['id'] }}">{{ $anchor['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm btn-primary">추가</button>
                                    <button type="button" class="btn btn-sm btn-secondary cancel-submenu-btn">취소</button>
                                </div>
                            </div>
                        </form>
                    </td>
                `;
            }
            
            row.parentNode.insertBefore(submenuForm, row.nextSibling);

            // 연결 타입 변경 처리
            const linkTypeSelect = submenuForm.querySelector('select[name="link_type"]');
            linkTypeSelect.addEventListener('change', function() {
                const boardSelect = submenuForm.querySelector('.submenu-link-target-board');
                const customPageSelect = submenuForm.querySelector('.submenu-link-target-custom-page');
                const externalInput = submenuForm.querySelector('.submenu-link-target-external');
                const anchorInput = submenuForm.querySelector('.submenu-link-target-anchor');
                
                boardSelect.style.display = 'none';
                customPageSelect.style.display = 'none';
                externalInput.style.display = 'none';
                if (anchorInput) anchorInput.style.display = 'none';
                
                if (this.value === 'board') {
                    boardSelect.style.display = 'block';
                } else if (this.value === 'custom_page') {
                    customPageSelect.style.display = 'block';
                } else if (this.value === 'external_link') {
                    externalInput.style.display = 'block';
                } else if (this.value === 'anchor') {
                    if (anchorInput) anchorInput.style.display = 'block';
                }
            });

            // 하위 메뉴 폼 제출
            submenuForm.querySelector('.submenu-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const linkType = formData.get('link_type');
                let linkTarget = null;

                if (linkType === 'board') {
                    linkTarget = formData.get('link_target_board');
                } else if (linkType === 'custom_page') {
                    linkTarget = formData.get('link_target_custom_page');
                } else if (linkType === 'external_link') {
                    linkTarget = formData.get('link_target_external');
                } else if (linkType === 'anchor') {
                    linkTarget = formData.get('link_target_anchor');
                }

                fetch('{{ route("admin.menus.store", ["site" => $site->slug]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: formData.get('name'),
                        link_type: linkType,
                        link_target: linkTarget,
                        parent_id: menuId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || '하위 메뉴 추가에 실패했습니다.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('하위 메뉴 추가 중 오류가 발생했습니다.');
                });
            });

            // 취소 버튼
            submenuForm.querySelector('.cancel-submenu-btn').addEventListener('click', function() {
                submenuForm.remove();
            });
        }
    });

    // ========== 모바일 하단 메뉴 관련 JavaScript ==========
    
    // 아이콘 타입 변경
    const newMobileIconType = document.getElementById('new_mobile_icon_type');
    const newMobileDefaultIconSection = document.getElementById('new_mobile_default_icon_section');
    const newMobileImageIconSection = document.getElementById('new_mobile_image_icon_section');
    const newMobileEmojiIconSection = document.getElementById('new_mobile_emoji_icon_section');
    const newMobileIconFile = document.getElementById('new_mobile_icon_file');
    const newMobileIconPreview = document.getElementById('new_mobile_icon_preview');
    const newMobileIconPreviewImg = document.getElementById('new_mobile_icon_preview_img');

    newMobileIconType.addEventListener('change', function() {
        if (this.value === 'image') {
            newMobileDefaultIconSection.style.display = 'none';
            newMobileImageIconSection.style.display = 'block';
            newMobileEmojiIconSection.style.display = 'none';
        } else if (this.value === 'emoji') {
            newMobileDefaultIconSection.style.display = 'none';
            newMobileImageIconSection.style.display = 'none';
            newMobileEmojiIconSection.style.display = 'block';
            newMobileIconPreview.style.display = 'none';
            // 이모지 그리드 초기화
            if (typeof renderEmojiGrid === 'function') {
                renderEmojiGrid('smileys');
            }
        } else {
            newMobileDefaultIconSection.style.display = 'block';
            newMobileImageIconSection.style.display = 'none';
            newMobileEmojiIconSection.style.display = 'none';
            newMobileIconPreview.style.display = 'none';
        }
    });

    // 이미지 미리보기
    newMobileIconFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                newMobileIconPreviewImg.src = e.target.result;
                newMobileIconPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // 기본 아이콘 검색 및 선택
    const newMobileIconSearch = document.getElementById('new_mobile_icon_search');
    const newMobileIconGrid = document.getElementById('new_mobile_icon_grid');
    const newMobileIconPath = document.getElementById('new_mobile_icon_path');
    
    // Bootstrap Icons 목록 (확장된 아이콘 목록)
    const bootstrapIcons = [
        // 홈 및 네비게이션
        'house', 'house-fill', 'house-door', 'house-door-fill', 'house-heart', 'house-heart-fill',
        'grid', 'grid-3x3', 'grid-1x2', 'grid-1x2-fill', 'list', 'list-ul', 'list-ol', 'list-task',
        'list-nested', 'menu-button', 'menu-button-wide', 'menu-button-wide-fill', 'menu-app', 'menu-app-fill',
        'menu-down', 'menu-up', 'three-dots', 'three-dots-vertical',
        
        // 사용자 및 프로필
        'person', 'person-fill', 'person-badge', 'person-badge-fill', 'person-circle', 'person-check',
        'person-check-fill', 'person-dash', 'person-dash-fill', 'person-plus', 'person-plus-fill',
        'person-x', 'person-x-fill', 'people', 'people-fill', 'person-workspace', 'person-rolodex',
        'person-video', 'person-video2', 'person-video3', 'person-walking', 'person-running',
        
        // 검색 및 알림
        'search', 'search-heart', 'search-heart-fill', 'bell', 'bell-fill', 'bell-slash', 'bell-slash-fill',
        'megaphone', 'megaphone-fill', 'broadcast', 'broadcast-pin',
        
        // 하트 및 즐겨찾기
        'heart', 'heart-fill', 'heart-half', 'heart-pulse', 'heart-pulse-fill', 'star', 'star-fill', 'star-half',
        'bookmark', 'bookmark-fill', 'bookmark-star', 'bookmark-star-fill', 'bookmark-check', 'bookmark-check-fill',
        'bookmark-x', 'bookmark-x-fill', 'bookmark-plus', 'bookmark-plus-fill', 'bookmark-dash', 'bookmark-dash-fill',
        
        // 날짜 및 시간
        'calendar', 'calendar-event', 'calendar-event-fill', 'calendar-check', 'calendar-check-fill',
        'calendar-date', 'calendar-date-fill', 'calendar-day', 'calendar-day-fill', 'calendar-week',
        'calendar-week-fill', 'calendar-month', 'calendar-month-fill', 'calendar-range', 'calendar-range-fill',
        'calendar-plus', 'calendar-plus-fill', 'calendar-minus', 'calendar-minus-fill', 'calendar-x', 'calendar-x-fill',
        'clock', 'clock-history', 'clock-fill', 'alarm', 'alarm-fill', 'stopwatch', 'stopwatch-fill',
        'hourglass', 'hourglass-split', 'hourglass-top', 'hourglass-bottom',
        
        // 메시지 및 통신
        'envelope', 'envelope-fill', 'envelope-open', 'envelope-open-fill', 'envelope-check',
        'envelope-check-fill', 'envelope-x', 'envelope-x-fill', 'envelope-plus', 'envelope-plus-fill',
        'envelope-dash', 'envelope-dash-fill', 'chat', 'chat-dots', 'chat-dots-fill',
        'chat-left', 'chat-left-fill', 'chat-right', 'chat-right-fill', 'chat-left-text', 'chat-left-text-fill',
        'chat-right-text', 'chat-right-text-fill', 'chat-square', 'chat-square-fill', 'chat-square-text',
        'chat-square-text-fill', 'chat-square-quote', 'chat-square-quote-fill', 'chat-square-heart',
        'chat-square-heart-fill', 'telephone', 'telephone-fill', 'telephone-forward', 'telephone-forward-fill',
        'telephone-outbound', 'telephone-outbound-fill', 'telephone-inbound', 'telephone-inbound-fill',
        'phone', 'phone-fill', 'phone-vibrate', 'phone-vibrate-fill', 'voicemail',
        
        // 미디어
        'camera', 'camera-fill', 'camera-video', 'camera-video-fill', 'camera-reels', 'camera-reels-fill',
        'camera2', 'image', 'image-fill', 'images', 'images-fill', 'film', 'film-fill', 'play', 'play-fill',
        'play-circle', 'play-circle-fill', 'pause', 'pause-fill', 'pause-circle', 'pause-circle-fill',
        'stop', 'stop-fill', 'stop-circle', 'stop-circle-fill', 'skip-forward', 'skip-forward-fill',
        'skip-backward', 'skip-backward-fill', 'skip-start', 'skip-start-fill', 'skip-end', 'skip-end-fill',
        'volume-up', 'volume-up-fill', 'volume-down', 'volume-down-fill', 'volume-mute', 'volume-mute-fill',
        'music-note', 'music-note-beamed', 'music-note-list', 'vinyl', 'vinyl-fill', 'soundwave',
        
        // 파일 및 폴더
        'file', 'file-earmark', 'file-earmark-fill', 'file-text', 'file-text-fill', 'file-earmark-text',
        'file-earmark-text-fill', 'file-pdf', 'file-pdf-fill', 'file-earmark-pdf', 'file-earmark-pdf-fill',
        'file-word', 'file-word-fill', 'file-earmark-word', 'file-earmark-word-fill', 'file-excel',
        'file-excel-fill', 'file-earmark-excel', 'file-earmark-excel-fill', 'file-ppt', 'file-ppt-fill',
        'file-earmark-ppt', 'file-earmark-ppt-fill', 'file-image', 'file-image-fill', 'file-earmark-image',
        'file-earmark-image-fill', 'file-zip', 'file-zip-fill', 'file-earmark-zip', 'file-earmark-zip-fill',
        'file-play', 'file-play-fill', 'file-earmark-play', 'file-earmark-play-fill', 'file-music',
        'file-music-fill', 'file-earmark-music', 'file-earmark-music-fill', 'file-plus', 'file-plus-fill',
        'file-minus', 'file-minus-fill', 'file-x', 'file-x-fill', 'file-check', 'file-check-fill',
        'folder', 'folder-fill', 'folder2', 'folder2-open', 'folder-symlink', 'folder-symlink-fill',
        'folder-check', 'folder-check-fill', 'folder-x', 'folder-x-fill', 'folder-plus', 'folder-plus-fill',
        'folder-minus', 'folder-minus-fill', 'folder-open', 'folder-open-fill',
        
        // 업로드 및 다운로드
        'download', 'upload', 'cloud-download', 'cloud-download-fill', 'cloud-upload', 'cloud-upload-fill',
        'cloud', 'cloud-fill', 'cloud-check', 'cloud-check-fill', 'cloud-slash', 'cloud-slash-fill',
        'cloud-arrow-down', 'cloud-arrow-down-fill', 'cloud-arrow-up', 'cloud-arrow-up-fill',
        
        // 화살표 및 네비게이션
        'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down', 'arrow-left-right', 'arrow-up-down',
        'arrow-up-left', 'arrow-up-right', 'arrow-down-left', 'arrow-down-right', 'arrows-move',
        'chevron-left', 'chevron-right', 'chevron-up', 'chevron-down', 'chevron-compact-left',
        'chevron-compact-right', 'chevron-compact-up', 'chevron-compact-down', 'caret-left', 'caret-left-fill',
        'caret-right', 'caret-right-fill', 'caret-up', 'caret-up-fill', 'caret-down', 'caret-down-fill',
        'arrow-repeat', 'arrow-clockwise', 'arrow-counterclockwise', 'arrow-90deg-up', 'arrow-90deg-down',
        'arrow-90deg-left', 'arrow-90deg-right', 'arrow-return-left', 'arrow-return-right',
        
        // 기본 작업
        'plus', 'plus-circle', 'plus-circle-fill', 'plus-square', 'plus-square-fill', 'plus-lg',
        'dash', 'dash-circle', 'dash-circle-fill', 'dash-square', 'dash-square-fill', 'dash-lg',
        'x', 'x-circle', 'x-circle-fill', 'x-square', 'x-square-fill', 'x-lg', 'x-octagon',
        'x-octagon-fill', 'x-diamond', 'x-diamond-fill', 'check', 'check-circle', 'check-circle-fill',
        'check-square', 'check-square-fill', 'check-lg', 'check2', 'check2-circle', 'check2-circle-fill',
        'check2-square', 'check2-square-fill', 'check-all',
        
        // 설정 및 도구
        'gear', 'gear-fill', 'gear-wide', 'gear-wide-connected', 'wrench', 'wrench-adjustable',
        'wrench-adjustable-circle', 'tools', 'hammer', 'screwdriver', 'sliders', 'sliders2',
        'sliders2-vertical', 'nut', 'nut-fill', 'toggle-on', 'toggle-off', 'toggle2-on', 'toggle2-off',
        
        // 편집
        'pencil', 'pencil-fill', 'pencil-square', 'pencil-square-fill', 'pen', 'pen-fill',
        'brush', 'brush-fill', 'eraser', 'eraser-fill', 'highlighter', 'type', 'type-bold',
        'type-italic', 'type-underline', 'type-strikethrough', 'type-h1', 'type-h2', 'type-h3',
        'palette', 'palette-fill', 'palette2', 'droplet', 'droplet-fill', 'droplet-half',
        
        // 삭제 및 정리
        'trash', 'trash-fill', 'trash2', 'trash2-fill', 'trash3', 'trash3-fill',
        
        // 저장 및 공유
        'save', 'save-fill', 'save2', 'save2-fill', 'share', 'share-fill', 'share-arrow',
        'share-arrow-fill', 'send', 'send-fill', 'send-plus', 'send-plus-fill', 'send-x',
        'send-x-fill', 'send-check', 'send-check-fill', 'send-dash', 'send-dash-fill',
        
        // 링크 및 네트워크
        'link', 'link-45deg', 'unlink', 'link-45deg-unlink', 'box-arrow-up', 'box-arrow-up-right',
        'box-arrow-in-up', 'box-arrow-in-up-right', 'box-arrow-in-down', 'box-arrow-in-down-left',
        'box-arrow-in-down-right', 'box-arrow-in-left', 'box-arrow-in-right', 'box-arrow-out-up',
        'box-arrow-out-up-left', 'box-arrow-out-up-right', 'box-arrow-out-down', 'box-arrow-out-down-left',
        'box-arrow-out-down-right', 'box-arrow-out-left', 'box-arrow-out-right',
        
        // 지도 및 위치
        'globe', 'globe2', 'geo-alt', 'geo-alt-fill', 'geo', 'geo-fill', 'map', 'map-fill',
        'pin-map', 'pin-map-fill', 'compass', 'compass-fill', 'signpost', 'signpost-2',
        'signpost-2-fill', 'signpost-split', 'signpost-split-fill', 'geo-alt-fill', 'pin-angle',
        'pin-angle-fill', 'pin', 'pin-fill',
        
        // 쇼핑
        'cart', 'cart-fill', 'cart2', 'cart2-fill', 'cart3', 'cart3-fill', 'cart4', 'cart4-fill',
        'cart-check', 'cart-check-fill', 'cart-x', 'cart-x-fill', 'cart-plus', 'cart-plus-fill',
        'cart-dash', 'cart-dash-fill', 'bag', 'bag-fill', 'bag-check', 'bag-check-fill', 'bag-x',
        'bag-x-fill', 'bag-plus', 'bag-plus-fill', 'bag-dash', 'bag-dash-fill', 'basket', 'basket-fill',
        'basket2', 'basket2-fill', 'basket3', 'basket3-fill', 'shop', 'shop-window',
        
        // 결제
        'credit-card', 'credit-card-fill', 'credit-card-2-front', 'credit-card-2-front-fill',
        'credit-card-2-back', 'credit-card-2-back-fill', 'wallet', 'wallet-fill', 'wallet2',
        'wallet2-fill', 'cash', 'cash-coin', 'cash-stack', 'currency-dollar', 'currency-euro',
        'currency-exchange', 'currency-bitcoin', 'currency-yen', 'currency-pound', 'currency-rupee',
        'receipt', 'receipt-cutoff', 'receipt-cutoff-fill',
        
        // 차트 및 통계
        'graph-up', 'graph-up-arrow', 'graph-down', 'graph-down-arrow', 'bar-chart', 'bar-chart-fill',
        'bar-chart-line', 'bar-chart-line-fill', 'bar-chart-steps', 'pie-chart', 'pie-chart-fill',
        'pie-chart-fill-alt', 'line-chart', 'line-chart-fill', 'area-chart', 'area-chart-fill',
        'table', 'table-active', 'collection', 'collection-fill', 'collection-play',
        'collection-play-fill', 'grid-chart', 'grid-chart-fill', 'graph-up-arrow', 'graph-down-arrow',
        
        // 태그 및 분류
        'tag', 'tag-fill', 'tags', 'tags-fill', 'badge', 'badge-fill', 'badge-ad', 'badge-ad-fill',
        'badge-cc', 'badge-cc-fill', 'badge-hd', 'badge-hd-fill', 'badge-tm', 'badge-tm-fill',
        'badge-vo', 'badge-vo-fill', 'badge-vr', 'badge-vr-fill', 'badge-wc', 'badge-wc-fill',
        'badge-ar', 'badge-ar-fill', 'badge-3d', 'badge-4k', 'badge-8k', 'badge-hdr',
        
        // 기타
        'flag', 'flag-fill', 'shield', 'shield-fill', 'shield-check', 'shield-check-fill',
        'shield-exclamation', 'shield-exclamation-fill', 'shield-lock', 'shield-lock-fill',
        'shield-shaded', 'shield-slash', 'shield-slash-fill', 'shield-x', 'shield-x-fill',
        'lock', 'lock-fill', 'unlock', 'unlock-fill', 'key', 'key-fill', 'eye', 'eye-fill',
        'eye-slash', 'eye-slash-fill', 'hand-thumbs-up', 'hand-thumbs-up-fill', 'hand-thumbs-down',
        'hand-thumbs-down-fill', 'hand-index', 'hand-index-fill', 'hand-index-thumb',
        'hand-index-thumb-fill', 'emoji-smile', 'emoji-smile-fill', 'emoji-frown', 'emoji-frown-fill',
        'emoji-neutral', 'emoji-neutral-fill', 'emoji-heart-eyes', 'emoji-heart-eyes-fill',
        'emoji-wink', 'emoji-wink-fill', 'emoji-angry', 'emoji-angry-fill', 'emoji-dizzy',
        'emoji-dizzy-fill', 'emoji-expressionless', 'emoji-expressionless-fill', 'emoji-grimace',
        'emoji-grimace-fill', 'emoji-kiss', 'emoji-kiss-fill', 'emoji-laughing', 'emoji-laughing-fill',
        'emoji-sunglasses', 'emoji-sunglasses-fill', 'emoji-tear', 'emoji-tear-fill',
        'fire', 'fire-fill', 'lightning', 'lightning-fill', 'lightning-charge', 'lightning-charge-fill',
        'snow', 'snow2', 'snow3', 'cloud-rain', 'cloud-rain-fill', 'cloud-rain-heavy', 'cloud-rain-heavy-fill',
        'cloud-snow', 'cloud-snow-fill', 'cloud-lightning', 'cloud-lightning-fill', 'cloud-lightning-rain',
        'cloud-lightning-rain-fill', 'cloud-hail', 'cloud-hail-fill', 'cloud-drizzle', 'cloud-drizzle-fill',
        'cloud-sleet', 'cloud-sleet-fill', 'cloud-fog', 'cloud-fog-fill', 'cloud-fog2', 'cloud-fog2-fill',
        'sun', 'sun-fill', 'moon', 'moon-fill', 'moon-stars', 'moon-stars-fill', 'brightness-high',
        'brightness-high-fill', 'brightness-low', 'brightness-low-fill', 'thermometer', 'thermometer-half',
        'thermometer-high', 'thermometer-low', 'thermometer-snow', 'thermometer-sun',
        'trophy', 'trophy-fill', 'award', 'award-fill', 'medal', 'medal-fill', 'patch-check',
        'patch-check-fill', 'patch-exclamation', 'patch-exclamation-fill', 'patch-minus', 'patch-minus-fill',
        'patch-plus', 'patch-plus-fill', 'patch-question', 'patch-question-fill', 'patch-x', 'patch-x-fill',
        'gift', 'gift-fill', 'balloon', 'balloon-fill', 'balloon-heart', 'balloon-heart-fill',
        'cake', 'cake-fill', 'cake2', 'cake2-fill', 'cup', 'cup-fill', 'cup-hot', 'cup-hot-fill',
        'cup-straw', 'cup-straw-fill', 'egg', 'egg-fill', 'egg-fried', 'mug', 'mug-fill',
        'dice-1', 'dice-2', 'dice-3', 'dice-4', 'dice-5', 'dice-6', 'dice-1-fill', 'dice-2-fill',
        'dice-3-fill', 'dice-4-fill', 'dice-5-fill', 'dice-6-fill', 'diamond', 'diamond-fill',
        'gem', 'gem-fill', 'infinity', 'peace', 'peace-fill', 'yin-yang', 'yin-yang-fill',
        'flower1', 'flower2', 'flower3', 'sunflower', 'tulip', 'rose', 'lotus', 'cherry',
        'apple', 'banana', 'grape', 'lemon', 'orange', 'pear', 'strawberry', 'watermelon',
        'carrot', 'corn', 'cucumber', 'lettuce', 'onion', 'pepper', 'potato', 'pumpkin', 'tomato',
        'bread', 'bread-slice', 'cheese', 'hamburger', 'hotdog', 'pizza', 'sandwich', 'taco',
        'waffle', 'bacon', 'sausage', 'chicken', 'fish', 'shrimp', 'cookie', 'donut', 'muffin',
        'pie', 'ice-cream', 'lollipop', 'candy', 'chocolate', 'marshmallow', 'cotton-candy',
        'snow-cone', 'slush', 'smoothie', 'juice', 'soda', 'energy-drink', 'protein-shake',
        'milkshake', 'frappe', 'latte', 'cappuccino', 'espresso', 'mocha', 'americano',
        'macchiato', 'frappuccino', 'tea', 'green-tea', 'herbal-tea', 'bubble-tea', 'matcha',
        'chai', 'hot-chocolate', 'cocoa', 'lemonade', 'iced-tea', 'iced-coffee', 'cold-brew',
        'nitro-coffee', 'beer', 'wine', 'champagne', 'cocktail', 'martini', 'margarita',
        'mojito', 'daiquiri', 'pina-colada', 'sangria', 'mimosa', 'bloody-mary', 'whiskey',
        'vodka', 'rum', 'gin', 'tequila', 'brandy', 'cognac', 'sake', 'soju'
    ];

    function renderIconGrid(searchTerm = '') {
        newMobileIconGrid.innerHTML = '';
        const filteredIcons = bootstrapIcons.filter(icon => 
            icon.toLowerCase().includes(searchTerm.toLowerCase())
        );
        
        filteredIcons.forEach(icon => {
            const iconElement = document.createElement('div');
            iconElement.className = 'icon-item';
            iconElement.style.cssText = 'cursor: pointer; padding: 5px; text-align: center; border: 1px solid transparent; border-radius: 4px;';
            iconElement.innerHTML = `<i class="bi bi-${icon}" style="font-size: 20px;"></i>`;
            iconElement.title = icon;
            iconElement.addEventListener('click', function() {
                newMobileIconPath.value = `bi bi-${icon}`;
                                // 선택된 아이콘 하이라이트
                document.querySelectorAll('.icon-item').forEach(item => {
                    item.style.backgroundColor = '';
                    item.style.borderColor = 'transparent';
                });
                this.style.backgroundColor = '#e7f3ff';
                this.style.borderColor = '#0d6efd';
            });
            iconElement.addEventListener('mouseenter', function() {
                if (newMobileIconPath.value !== `bi bi-${icon}`) {
                    this.style.backgroundColor = '#f8f9fa';
                }
            });
            iconElement.addEventListener('mouseleave', function() {
                if (newMobileIconPath.value !== `bi bi-${icon}`) {
                    this.style.backgroundColor = '';
                }
            });
            newMobileIconGrid.appendChild(iconElement);
        });
    }

    newMobileIconSearch.addEventListener('input', function() {
        renderIconGrid(this.value);
    });

    // 초기 아이콘 그리드 렌더링
    renderIconGrid();

    // ========== 이모지 관련 JavaScript ==========
    const newMobileEmojiSearch = document.getElementById('new_mobile_emoji_search');
    const newMobileEmojiGrid = document.getElementById('new_mobile_emoji_grid');
    const newMobileEmojiPath = document.getElementById('new_mobile_emoji_path');
    const emojiCategoryButtons = document.querySelectorAll('.emoji-category-btn');
    
    // 이모지 데이터 (카테고리별)
    const emojiCategories = {
        smileys: ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '☺️', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😶‍🌫️', '😏', '😒', '🙄', '😬', '😮‍💨', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '😵', '😵‍💫', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾'],
        animals: ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐻‍❄️', '🐨', '🐯', '🦁', '🐮', '🐷', '🐽', '🐸', '🐵', '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞', '🐜', '🦟', '🦗', '🕷️', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🦣', '🐘', '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🦬', '🐃', '🐂', '🐄', '🐎', '🐖', '🐏', '🐑', '🦙', '🐐', '🦌', '🐕', '🐩', '🦮', '🐕‍🦺', '🐈', '🐈‍⬛', '🪶', '🦅', '🦆', '🦢', '🦉', '🦤', '🦩', '🦚', '🦜', '🐓', '🦃', '🦘', '🦡', '🦫', '🦨', '🦦', '🦥', '🐿️', '🦔', '🐾', '🐉', '🐲'],
        food: ['🍏', '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍈', '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆', '🥑', '🥦', '🥬', '🥒', '🌶️', '🌽', '🥕', '🫒', '🧄', '🧅', '🥔', '🍠', '🥐', '🥯', '🍞', '🥖', '🥨', '🧀', '🥚', '🍳', '🥞', '🥓', '🥩', '🍗', '🍖', '🦴', '🌭', '🍔', '🍟', '🍕', '🫓', '🥪', '🥙', '🧆', '🌮', '🌯', '🫔', '🥗', '🥘', '🥫', '🍝', '🍜', '🍲', '🍛', '🍣', '🍱', '🥟', '🦪', '🍤', '🍙', '🍚', '🍘', '🍥', '🥠', '🥮', '🍢', '🍡', '🍧', '🍨', '🍦', '🥧', '🧁', '🍰', '🎂', '🍮', '🍭', '🍬', '🍫', '🍿', '🍩', '🍪', '🌰', '🥜', '🍯', '🥛', '🍼', '🫖', '☕️', '🍵', '🧃', '🥤', '🧋', '🍶', '🍺', '🍻', '🥂', '🍷', '🥃', '🍸', '🍹', '🧉', '🍾', '🧊'],
        activities: ['⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🥅', '⛳', '🏹', '🎣', '🤿', '🥊', '🥋', '🎽', '🛹', '🛷', '⛸️', '🥌', '🎿', '⛷️', '🏂', '🪂', '🏋️‍♂️', '🤼‍♀️', '🤸‍♂️', '⛹️‍♀️', '🤺', '🤾‍♂️', '🏌️‍♀️', '🏇', '🧘‍♀️', '🏄‍♂️', '🏊‍♀️', '🤽‍♂️', '🚣‍♀️', '🧗‍♂️', '🚵‍♀️', '🚴‍♂️', '🏆', '🥇', '🥈', '🥉', '🏅', '🎖️', '🏵️', '🎗️', '🎫', '🎟️', '🎪', '🤹‍♂️', '🎭', '🩰', '🎨', '🎬', '🎤', '🎧', '🎼', '🎹', '🥁', '🎷', '🎺', '🎸', '🪕', '🎻', '🎲', '♟️', '🎯', '🎳', '🎮', '🎰', '🧩'],
        travel: ['🚗', '🚕', '🚙', '🚌', '🚎', '🏎️', '🚓', '🚑', '🚒', '🚐', '🛻', '🚚', '🚛', '🚜', '🏎️', '🏍️', '🛵', '🚲', '🛴', '🛹', '🛼', '🚁', '🛸', '✈️', '🛩️', '🛫', '🛬', '🪂', '💺', '🚢', '⛵', '🛥️', '🛳️', '⛴️', '🚤', '🛶', '🪝', '⛽', '🚧', '🚦', '🚥', '🗺️', '🗿', '🛕', '⛩️', '🕍', '🕌', '🛕', '🕋', '⛪', '🏛️', '💒', '🏩', '🏨', '🏦', '🏪', '🏫', '🏢', '🏬', '🏣', '🏤', '🏥', '🏦', '🏨', '🏩', '🏪', '🏫', '🏬', '🏭', '🏯', '🏰', '🗼', '🗽', '⛲', '⛺', '🌁', '🌃', '🏙️', '🌄', '🌅', '🌆', '🌇', '🌉', '♨️', '🎠', '🎡', '🎢', '💈', '🎪', '🚂', '🚃', '🚄', '🚅', '🚆', '🚇', '🚈', '🚉', '🚊', '🚝', '🚞', '🚋'],
        objects: ['⌚', '📱', '📲', '💻', '⌨️', '🖥️', '🖨️', '🖱️', '🖲️', '🕹️', '🗜️', '💾', '💿', '📀', '📼', '📷', '📸', '📹', '🎥', '📽️', '🎞️', '📞', '☎️', '📟', '📠', '📺', '📻', '🎙️', '🎚️', '🎛️', '🧭', '⏱️', '⏲️', '⏰', '🕰️', '⌛', '⏳', '📡', '🔋', '🔌', '💡', '🔦', '🕯️', '🧯', '🛢️', '💸', '💵', '💴', '💶', '💷', '💰', '💳', '💎', '⚖️', '🪜', '🧰', '🪛', '🔧', '🔨', '⚒️', '🛠️', '⛏️', '🪚', '🔩', '⚙️', '🪤', '🧱', '⛓️', '🧲', '🔫', '💣', '🧨', '🪓', '🔪', '🗡️', '⚔️', '🛡️', '🚬', '⚰️', '🪦', '⚱️', '🏺', '🔮', '📿', '🧿', '💈', '⚗️', '🔭', '🔬', '🕳️', '🩹', '🩺', '💊', '💉', '🩸', '🧬', '🦠', '🧫', '🧪', '🌡️', '🧹', '🪠', '🧺', '🧻', '🚽', '🚿', '🛁', '🛀', '🧼', '🪒', '🧽', '🪣', '🧴', '🛎️', '🔑', '🗝️', '🚪', '🪑', '🛋️', '🛏️', '🛌', '🧸', '🪆', '🖼️', '🪞', '🪟', '🛍️', '🛒', '🎁', '🎈', '🎀', '🪄', '🪅', '🎊', '🎉', '🪇', '🎎', '🏮', '🎐', '🧧', '✉️', '📩', '📨', '📧', '💌', '📥', '📤', '📦', '🏷️', '🪧', '📪', '📫', '📬', '📭', '📮', '📯', '📜', '📃', '📄', '📑', '🧾', '📊', '📈', '📉', '🗒️', '🗓️', '📆', '📅', '🗑️', '📇', '🗃️', '🗳️', '🗄️', '📋', '📁', '📂', '🗂️', '🗞️', '📰', '📓', '📔', '📒', '📕', '📗', '📘', '📙', '📚', '📖', '🔖', '🧷', '🔗', '📎', '🖇️', '📐', '📏', '🧮', '📌', '📍', '✂️', '🖊️', '🖋️', '✒️', '🖌️', '🖍️', '📝', '✏️', '🔍', '🔎', '🔏', '🔐', '🔒', '🔓'],
        symbols: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉️', '☸️', '🪯', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓', '🆔', '⚛️', '🉑', '☢️', '☣️', '📴', '📳', '🈶', '🈚', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️', '㊗️', '🈴', '🈵', '🈹', '🈲', '🅰️', '🅱️', '🆎', '🆑', '🅾️', '🆘', '❌', '⭕', '🛑', '⛔', '📛', '🚫', '💯', '💢', '♨️', '🚷', '🚯', '🚳', '🚱', '🔞', '📵', '🚭', '❗', '❓', '❕', '❔', '‼️', '⁉️', '🔅', '🔆', '〽️', '⚠️', '🚸', '🔱', '⚜️', '🔰', '♻️', '✅', '🈯', '💹', '❇️', '✳️', '❎', '🌐', '💠', 'Ⓜ️', '🌀', '💤', '🏧', '🚾', '♿', '🅿️', '🈳', '🈂️', '🛂', '🛃', '🛄', '🛅', '🚹', '🚺', '🚼', '🚻', '🚮', '🎦', '📶', '🈁', '🔣', 'ℹ️', '🔤', '🔡', '🔠', '🆖', '🆗', '🆙', '🆒', '🆕', '🆓', '0️⃣', '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟', '🔢', '#️⃣', '*️⃣', '⏏️', '▶️', '⏸️', '⏯️', '⏹️', '⏺️', '⏭️', '⏮️', '⏩', '⏪', '⏫', '⏬', '◀️', '🔼', '🔽', '➡️', '⬅️', '⬆️', '⬇️', '↗️', '↘️', '↙️', '↖️', '↕️', '↔️', '↪️', '↩️', '⤴️', '⤵️', '🔀', '🔁', '🔂', '🔄', '🔃', '🎵', '🎶', '➕', '➖', '➗', '✖️', '♾️', '💲', '💱', '™️', '©️', '®️', '〰️', '➰', '➿', '🔚', '🔙', '🔛', '🔜', '🔝'],
        flags: ['🏳️', '🏴', '🏁', '🚩', '🏳️‍🌈', '🏳️‍⚧️', '🇦🇨', '🇦🇩', '🇦🇪', '🇦🇫', '🇦🇬', '🇦🇮', '🇦🇱', '🇦🇲', '🇦🇴', '🇦🇶', '🇦🇷', '🇦🇸', '🇦🇹', '🇦🇺', '🇦🇼', '🇦🇽', '🇦🇿', '🇧🇦', '🇧🇧', '🇧🇩', '🇧🇪', '🇧🇫', '🇧🇬', '🇧🇭', '🇧🇮', '🇧🇯', '🇧🇱', '🇧🇲', '🇧🇳', '🇧🇴', '🇧🇶', '🇧🇷', '🇧🇸', '🇧🇹', '🇧🇻', '🇧🇼', '🇧🇾', '🇧🇿', '🇨🇦', '🇨🇨', '🇨🇩', '🇨🇫', '🇨🇬', '🇨🇭', '🇨🇮', '🇨🇰', '🇨🇱', '🇨🇲', '🇨🇳', '🇨🇴', '🇨🇵', '🇨🇷', '🇨🇺', '🇨🇻', '🇨🇼', '🇨🇽', '🇨🇾', '🇨🇿', '🇩🇪', '🇩🇬', '🇩🇯', '🇩🇰', '🇩🇲', '🇩🇴', '🇩🇿', '🇪🇦', '🇪🇨', '🇪🇪', '🇪🇬', '🇪🇭', '🇪🇷', '🇪🇸', '🇪🇹', '🇪🇺', '🇫🇮', '🇫🇯', '🇫🇰', '🇫🇲', '🇫🇴', '🇫🇷', '🇬🇦', '🇬🇧', '🇬🇩', '🇬🇪', '🇬🇫', '🇬🇬', '🇬🇭', '🇬🇮', '🇬🇱', '🇬🇲', '🇬🇳', '🇬🇵', '🇬🇶', '🇬🇷', '🇬🇸', '🇬🇹', '🇬🇺', '🇬🇼', '🇬🇾', '🇭🇰', '🇭🇲', '🇭🇳', '🇭🇷', '🇭🇹', '🇭🇺', '🇮🇨', '🇮🇩', '🇮🇪', '🇮🇱', '🇮🇲', '🇮🇳', '🇮🇴', '🇮🇶', '🇮🇷', '🇮🇸', '🇮🇹', '🇯🇪', '🇯🇲', '🇯🇴', '🇯🇵', '🇰🇪', '🇰🇬', '🇰🇭', '🇰🇮', '🇰🇲', '🇰🇳', '🇰🇵', '🇰🇷', '🇰🇼', '🇰🇾', '🇰🇿', '🇱🇦', '🇱🇧', '🇱🇨', '🇱🇮', '🇱🇰', '🇱🇷', '🇱🇸', '🇱🇹', '🇱🇺', '🇱🇻', '🇱🇾', '🇲🇦', '🇲🇨', '🇲🇩', '🇲🇪', '🇲🇫', '🇲🇬', '🇲🇭', '🇲🇰', '🇲🇱', '🇲🇲', '🇲🇳', '🇲🇴', '🇲🇵', '🇲🇶', '🇲🇷', '🇲🇸', '🇲🇹', '🇲🇺', '🇲🇻', '🇲🇼', '🇲🇽', '🇲🇾', '🇲🇿', '🇳🇦', '🇳🇨', '🇳🇪', '🇳🇫', '🇳🇬', '🇳🇮', '🇳🇱', '🇳🇴', '🇳🇵', '🇳🇷', '🇳🇺', '🇳🇿', '🇴🇲', '🇵🇦', '🇵🇪', '🇵🇫', '🇵🇬', '🇵🇭', '🇵🇰', '🇵🇱', '🇵🇲', '🇵🇳', '🇵🇷', '🇵🇸', '🇵🇹', '🇵🇼', '🇵🇾', '🇶🇦', '🇷🇪', '🇷🇴', '🇷🇸', '🇷🇺', '🇷🇼', '🇸🇦', '🇸🇧', '🇸🇨', '🇸🇩', '🇸🇪', '🇸🇬', '🇸🇭', '🇸🇮', '🇸🇯', '🇸🇰', '🇸🇱', '🇸🇲', '🇸🇳', '🇸🇴', '🇸🇷', '🇸🇸', '🇸🇹', '🇸🇻', '🇸🇽', '🇸🇾', '🇸🇿', '🇹🇦', '🇹🇨', '🇹🇩', '🇹🇫', '🇹🇬', '🇹🇭', '🇹🇯', '🇹🇰', '🇹🇱', '🇹🇲', '🇹🇳', '🇹🇴', '🇹🇷', '🇹🇹', '🇹🇻', '🇹🇼', '🇹🇿', '🇺🇦', '🇺🇬', '🇺🇲', '🇺🇳', '🇺🇸', '🇺🇾', '🇺🇿', '🇻🇦', '🇻🇨', '🇻🇪', '🇻🇬', '🇻🇮', '🇻🇳', '🇻🇺', '🇼🇫', '🇼🇸', '🇽🇰', '🇾🇪', '🇾🇹', '🇿🇦', '🇿🇲', '🇿🇼', '🏴‍☠️']
    };
    
    let currentEmojiCategory = 'smileys';
    
    // 이모지 그리드 렌더링 함수
    function renderEmojiGrid(category = 'smileys', searchTerm = '') {
        if (!newMobileEmojiGrid) return;
        newMobileEmojiGrid.innerHTML = '';
        let emojis = emojiCategories[category] || emojiCategories.smileys;
        
        // 검색어가 있으면 필터링
        if (searchTerm) {
            // 이모지 검색은 유니코드나 이름으로는 어려우므로 모든 카테고리에서 검색
            emojis = [];
            Object.values(emojiCategories).forEach(catEmojis => {
                emojis = emojis.concat(catEmojis);
            });
            // 중복 제거
            emojis = [...new Set(emojis)];
        }
        
        emojis.forEach(emoji => {
            const emojiElement = document.createElement('div');
            emojiElement.className = 'emoji-item';
            emojiElement.style.cssText = 'cursor: pointer; padding: 5px; text-align: center; border: 1px solid transparent; border-radius: 4px; font-size: 24px;';
            emojiElement.innerHTML = emoji;
            emojiElement.title = emoji;
            emojiElement.addEventListener('click', function() {
                if (newMobileEmojiPath) {
                    newMobileEmojiPath.value = emoji;
                }
                // 선택된 이모지 하이라이트
                document.querySelectorAll('.emoji-item').forEach(item => {
                    item.style.backgroundColor = '';
                    item.style.borderColor = 'transparent';
                });
                this.style.backgroundColor = '#e7f3ff';
                this.style.borderColor = '#0d6efd';
            });
            emojiElement.addEventListener('mouseenter', function() {
                if (newMobileEmojiPath && newMobileEmojiPath.value !== emoji) {
                    this.style.backgroundColor = '#f8f9fa';
                }
            });
            emojiElement.addEventListener('mouseleave', function() {
                if (newMobileEmojiPath && newMobileEmojiPath.value !== emoji) {
                    this.style.backgroundColor = '';
                }
            });
            newMobileEmojiGrid.appendChild(emojiElement);
        });
    }
    
    // 이모지 카테고리 버튼 클릭
    if (emojiCategoryButtons && emojiCategoryButtons.length > 0) {
        emojiCategoryButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                emojiCategoryButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentEmojiCategory = this.dataset.category;
                renderEmojiGrid(currentEmojiCategory, newMobileEmojiSearch ? newMobileEmojiSearch.value : '');
            });
        });
    }
    
    // 이모지 검색
    if (newMobileEmojiSearch) {
        newMobileEmojiSearch.addEventListener('input', function() {
            renderEmojiGrid(currentEmojiCategory, this.value);
        });
    }
    
    // 초기 이모지 그리드 렌더링
    if (newMobileEmojiGrid) {
        renderEmojiGrid('smileys');
    }

    // 모바일 메뉴 연결 타입 변경
    const newMobileLinkType = document.getElementById('new_mobile_link_type');
    const newMobileLinkTargetBoard = document.getElementById('new_mobile_link_target_board');
    const newMobileLinkTargetCustomPage = document.getElementById('new_mobile_link_target_custom_page');
    const newMobileLinkTargetExternal = document.getElementById('new_mobile_link_target_external');
    const newMobileLinkTargetPlaceholder = newMobileLinkTargetBoard ? newMobileLinkTargetBoard.parentElement.querySelector('.link-target-placeholder') : null;

    newMobileLinkType.addEventListener('change', function() {
        newMobileLinkTargetBoard.style.display = 'none';
        newMobileLinkTargetCustomPage.style.display = 'none';
        newMobileLinkTargetExternal.style.display = 'none';
        if (newMobileLinkTargetPlaceholder) newMobileLinkTargetPlaceholder.style.display = 'none';

        const linkType = this.value;
        if (linkType === 'board') {
            newMobileLinkTargetBoard.style.display = 'block';
            newMobileLinkTargetBoard.required = true;
            newMobileLinkTargetCustomPage.required = false;
            newMobileLinkTargetExternal.required = false;
        } else if (linkType === 'custom_page') {
            newMobileLinkTargetCustomPage.style.display = 'block';
            newMobileLinkTargetCustomPage.required = true;
            newMobileLinkTargetBoard.required = false;
            newMobileLinkTargetExternal.required = false;
        } else if (linkType === 'external_link') {
            newMobileLinkTargetExternal.style.display = 'block';
            newMobileLinkTargetExternal.required = true;
            newMobileLinkTargetBoard.required = false;
            newMobileLinkTargetCustomPage.required = false;
        } else if (['attendance', 'point_exchange', 'event_application'].includes(linkType)) {
            if (newMobileLinkTargetPlaceholder) newMobileLinkTargetPlaceholder.style.display = 'block';
            newMobileLinkTargetBoard.required = false;
            newMobileLinkTargetCustomPage.required = false;
            newMobileLinkTargetExternal.required = false;
        } else {
            newMobileLinkTargetBoard.required = false;
            newMobileLinkTargetCustomPage.required = false;
            newMobileLinkTargetExternal.required = false;
        }
    });

    // 새 모바일 메뉴 등록
    document.getElementById('newMobileMenuForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const linkType = document.getElementById('new_mobile_link_type').value;
        let linkTarget = null;

        if (linkType === 'board') {
            const boardSelect = document.getElementById('new_mobile_link_target_board');
            linkTarget = boardSelect ? boardSelect.value : null;
            if (!linkTarget || linkTarget === '') {
                alert('게시판을 선택해주세요.');
                return;
            }
        } else if (linkType === 'custom_page') {
            const customPageSelect = document.getElementById('new_mobile_link_target_custom_page');
            linkTarget = customPageSelect ? customPageSelect.value : null;
            if (!linkTarget || linkTarget === '') {
                alert('페이지를 선택해주세요.');
                return;
            }
        } else if (linkType === 'external_link') {
            const externalInput = document.getElementById('new_mobile_link_target_external');
            linkTarget = externalInput ? externalInput.value : null;
            if (!linkTarget || linkTarget.trim() === '') {
                alert('외부 링크 URL을 입력해주세요.');
                return;
            }
        } else if (['attendance', 'point_exchange', 'event_application'].includes(linkType)) {
            linkTarget = null;
        }

        // 아이콘 경로 설정
        if (formData.get('icon_type') === 'default') {
            const iconPathValue = newMobileIconPath ? newMobileIconPath.value : null;
            if (iconPathValue) {
                formData.set('icon_path', iconPathValue);
            } else {
                formData.set('icon_path', 'bi bi-circle');
            }
        } else if (formData.get('icon_type') === 'emoji') {
            const emojiValue = newMobileEmojiPath ? newMobileEmojiPath.value : null;
            if (emojiValue) {
                formData.set('icon_path', emojiValue);
            } else {
                alert('이모지를 선택해주세요.');
                return;
            }
        }

        // 기존 link_target 항목 모두 제거 (중복 방지)
        formData.delete('link_target');
        
        formData.set('link_type', linkType);
        if (linkTarget !== null && linkTarget !== '') {
            formData.set('link_target', linkTarget);
        }

        fetch('{{ route("admin.mobile-menus.store", ["site" => $site->slug]) }}', {
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
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || '모바일 메뉴 추가에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('모바일 메뉴 추가 중 오류가 발생했습니다.');
        });
    });

    // 모바일 메뉴 저장 (순서 및 모든 수정사항)
    document.getElementById('mobileMenuOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const menus = [];
        const mobileMenusData = @json($mobileMenus->keyBy('id')->toArray());
        
        // 데스크탑 테이블과 모바일 카드 모두 처리
        const menuElements = document.querySelectorAll('#mobileMenuListBody > tr[data-mobile-menu-id], #mobileMenuListBodyCards > .mobile-menu-card[data-mobile-menu-id]');
        menuElements.forEach((element, index) => {
            const menuId = element.dataset.mobileMenuId;
            if (!menuId) return; // menuId가 없으면 스킵
            
            const nameInput = element.querySelector('.mobile-menu-name-input');
            const linkTypeSelect = element.querySelector('.mobile-menu-link-type-select');
            const linkType = linkTypeSelect ? linkTypeSelect.value : null;
            let linkTarget = null;

            if (linkType === 'board') {
                const boardSelect = element.querySelector('.mobile-menu-link-target-board');
                linkTarget = boardSelect ? boardSelect.value : null;
            } else if (linkType === 'custom_page') {
                const customPageSelect = element.querySelector('.mobile-menu-link-target-custom-page');
                linkTarget = customPageSelect ? customPageSelect.value : null;
            } else if (linkType === 'external_link') {
                const externalInput = element.querySelector('.mobile-menu-link-target-external');
                linkTarget = externalInput ? externalInput.value : null;
            }

            // 현재 메뉴의 아이콘 정보 가져오기
            const currentMenu = mobileMenusData[menuId];
            const iconType = currentMenu ? currentMenu.icon_type : 'default';
            const iconPath = currentMenu ? (currentMenu.icon_path || 'bi bi-circle') : 'bi bi-circle';

            menus.push({
                id: menuId,
                order: index + 1,
                name: nameInput ? (nameInput.value || '') : '',
                link_type: linkType,
                link_target: linkTarget,
                icon_type: iconType,
                icon_path: iconPath
            });
        });

        if (menus.length === 0) {
            alert('저장할 메뉴가 없습니다.');
            return;
        }

        // 모든 메뉴 항목을 순차적으로 업데이트
        const updatePromises = menus.map(menu => {
            return fetch(`/site/{{ $site->slug }}/admin/mobile-menus/${menu.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: menu.name || '',
                    link_type: menu.link_type,
                    link_target: menu.link_target,
                    icon_type: menu.icon_type,
                    icon_path: menu.icon_path
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => ({ success: false, message: data.message || '업데이트 실패' }));
                }
                return response.json();
            })
            .catch(error => {
                console.error('Update error for menu', menu.id, error);
                return { success: false, message: '네트워크 오류가 발생했습니다.' };
            });
        });

        // 순서 업데이트
        const orderPromise = fetch('{{ route("admin.mobile-menus.update-order", ["site" => $site->slug]) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ menus: menus.map(m => ({ id: m.id, order: m.order })) })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => ({ success: false, message: data.message || '순서 업데이트 실패' }));
            }
            return response.json();
        })
        .catch(error => {
            console.error('Order update error', error);
            return { success: false, message: '순서 업데이트 중 네트워크 오류가 발생했습니다.' };
        });

        // 모든 업데이트가 완료될 때까지 대기
        Promise.all([...updatePromises, orderPromise])
            .then(results => {
                const failedResults = results.filter(result => !result.success);
                if (failedResults.length === 0) {
                    alert('모바일 메뉴가 저장되었습니다.');
                    location.reload();
                } else {
                    const errorMessages = failedResults.map(r => r.message || '알 수 없는 오류').join('\n');
                    console.error('Failed updates:', failedResults);
                    alert('일부 메뉴 저장에 실패했습니다.\n\n오류 내용:\n' + errorMessages);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('모바일 메뉴 저장 중 오류가 발생했습니다.');
            });
    });

    // 모바일 메뉴 순서 상하 조정 (데스크탑 테이블 및 모바일 카드 모두 지원)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.mobile-menu-move-up-btn')) {
            const btn = e.target.closest('.mobile-menu-move-up-btn');
            const row = btn.closest('tr[data-mobile-menu-id], .mobile-menu-card[data-mobile-menu-id]');
            if (!row) return;
            
            const container = row.parentNode;
            const prevRow = row.previousElementSibling;
            if (prevRow && (prevRow.hasAttribute('data-mobile-menu-id') || prevRow.classList.contains('mobile-menu-card'))) {
                container.insertBefore(row, prevRow);
            }
        } else if (e.target.closest('.mobile-menu-move-down-btn')) {
            const btn = e.target.closest('.mobile-menu-move-down-btn');
            const row = btn.closest('tr[data-mobile-menu-id], .mobile-menu-card[data-mobile-menu-id]');
            if (!row) return;
            
            const container = row.parentNode;
            const nextRow = row.nextElementSibling;
            if (nextRow && (nextRow.hasAttribute('data-mobile-menu-id') || nextRow.classList.contains('mobile-menu-card'))) {
                container.insertBefore(nextRow, row);
            }
        }
    });

    // 모바일 메뉴 삭제
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-mobile-menu-btn')) {
            if (!confirm('이 모바일 메뉴를 삭제하시겠습니까?')) {
                return;
            }

            const menuId = e.target.closest('.delete-mobile-menu-btn').dataset.menuId;
            
            fetch(`/site/{{ $site->slug }}/admin/mobile-menus/${menuId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || '모바일 메뉴 삭제에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('모바일 메뉴 삭제 중 오류가 발생했습니다.');
            });
        }
    });

    // 모바일 메뉴 목록에서 연결 타입 변경 시 연결 대상 필드 표시/숨김 (데스크탑 테이블 및 모바일 카드 모두 지원)
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('mobile-menu-link-type-select')) {
            const menuId = e.target.dataset.menuId;
            const row = document.querySelector(`tr[data-mobile-menu-id="${menuId}"], .mobile-menu-card[data-mobile-menu-id="${menuId}"]`);
            if (!row) return;
            
            const linkType = e.target.value;
            const boardSelect = row.querySelector('.mobile-menu-link-target-board');
            const customPageSelect = row.querySelector('.mobile-menu-link-target-custom-page');
            const externalInput = row.querySelector('.mobile-menu-link-target-external');
            const placeholder = row.querySelector('.mobile-menu-link-target-placeholder');

            if (boardSelect) boardSelect.style.display = 'none';
            if (customPageSelect) customPageSelect.style.display = 'none';
            if (externalInput) externalInput.style.display = 'none';
            if (placeholder) placeholder.style.display = 'none';

            if (linkType === 'board') {
                if (boardSelect) boardSelect.style.display = 'block';
            } else if (linkType === 'custom_page') {
                if (customPageSelect) customPageSelect.style.display = 'block';
            } else if (linkType === 'external_link') {
                if (externalInput) externalInput.style.display = 'block';
            } else if (['attendance', 'point_exchange', 'event_application'].includes(linkType)) {
                if (placeholder) placeholder.style.display = 'block';
            }
        }
    });


    // 모바일 메뉴 디자인 타입 변경 및 색상 선택 UI 표시/숨김
    const mobileMenuDesignType = document.getElementById('mobile_menu_design_type');
    const mobileMenuColorSettings = document.getElementById('mobile_menu_color_settings');
    const mobileMenuBgColorWrapper = document.getElementById('mobile_menu_bg_color_wrapper');
    const mobileMenuBgColor = document.getElementById('mobile_menu_bg_color');
    const mobileMenuFontColor = document.getElementById('mobile_menu_font_color');

    function updateMobileMenuColorSettings() {
        const designType = mobileMenuDesignType.value;
        
        if (designType === 'glass') {
            // 글래스 타입: 폰트 컬러만 표시
            mobileMenuColorSettings.style.display = 'block';
            mobileMenuBgColorWrapper.style.display = 'none';
        } else if (['default', 'top_round', 'round'].includes(designType)) {
            // 기본타입, 상단라운드, 라운드: 배경 컬러와 폰트 컬러 표시
            mobileMenuColorSettings.style.display = 'block';
            mobileMenuBgColorWrapper.style.display = 'block';
        } else {
            mobileMenuColorSettings.style.display = 'none';
        }
    }

    // 초기 로드 시 색상 선택 UI 표시/숨김
    updateMobileMenuColorSettings();

    mobileMenuDesignType.addEventListener('change', function() {
        const designType = this.value;
        updateMobileMenuColorSettings();
        
        fetch('{{ route("admin.mobile-menus.design-type", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ design_type: designType })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 페이지 새로고침 없이 설정만 저장
            } else {
                alert(data.message || '디자인 타입 저장에 실패했습니다.');
                // 실패 시 이전 값으로 복원
                this.value = '{{ $mobileMenuDesignType ?? "default" }}';
                updateMobileMenuColorSettings();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('디자인 타입 저장 중 오류가 발생했습니다.');
            // 오류 시 이전 값으로 복원
            this.value = '{{ $mobileMenuDesignType ?? "default" }}';
            updateMobileMenuColorSettings();
        });
    });

    // 배경 컬러 변경 시 저장
    mobileMenuBgColor.addEventListener('change', function() {
        const bgColor = this.value;
        
        fetch('{{ route("admin.mobile-menus.design-type", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                design_type: mobileMenuDesignType.value,
                bg_color: bgColor
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('배경 컬러 저장 실패:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // 폰트 컬러 변경 시 저장
    mobileMenuFontColor.addEventListener('change', function() {
        const fontColor = this.value;
        
        fetch('{{ route("admin.mobile-menus.design-type", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                design_type: mobileMenuDesignType.value,
                font_color: fontColor
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('폰트 컬러 저장 실패:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // 개별 메뉴 폰트 컬러 수정
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('menu-font-color-picker') || e.target.classList.contains('menu-font-color-input')) {
            const menuId = e.target.dataset.menuId;
            const picker = document.querySelector(`.menu-font-color-picker[data-menu-id="${menuId}"]`);
            const input = document.querySelector(`.menu-font-color-input[data-menu-id="${menuId}"]`);
            
            if (e.target.classList.contains('menu-font-color-picker')) {
                input.value = e.target.value;
            } else if (e.target.classList.contains('menu-font-color-input')) {
                if (e.target.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                    picker.value = e.target.value;
                }
            }
            
            const fontColor = input.value.trim();
            
            // 디바운스: 입력이 끝난 후 저장
            clearTimeout(window.menuFontColorTimeout);
            window.menuFontColorTimeout = setTimeout(function() {
                fetch('{{ route("admin.menus.update", ["site" => $site->slug]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        menu_id: menuId,
                        font_color: fontColor || null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('메뉴 폰트 컬러 저장 실패:', data.message);
                        alert(data.message || '저장에 실패했습니다.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('저장 중 오류가 발생했습니다.');
                });
            }, 500);
        }
    });
    
    // 개별 메뉴 폰트 컬러 초기화
    document.addEventListener('click', function(e) {
        if (e.target.closest('.menu-font-color-reset')) {
            const resetBtn = e.target.closest('.menu-font-color-reset');
            const menuId = resetBtn.dataset.menuId;
            const picker = document.querySelector(`.menu-font-color-picker[data-menu-id="${menuId}"]`);
            const input = document.querySelector(`.menu-font-color-input[data-menu-id="${menuId}"]`);
            
            input.value = '';
            picker.value = '#000000';
            
            // 저장
            fetch('{{ route("admin.menus.update", ["site" => $site->slug]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    menu_id: menuId,
                    font_color: null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('메뉴 폰트 컬러 초기화 실패:', data.message);
                    alert(data.message || '초기화에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('초기화 중 오류가 발생했습니다.');
            });
        }
    });

    // Bootstrap Tooltip 초기화
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush

