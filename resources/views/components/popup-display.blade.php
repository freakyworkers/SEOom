@php
    use App\Models\Popup;
    use App\Models\Board;
    
    // 현재 페이지 정보
    $isHomePage = request()->routeIs('home');
    $isAttendancePage = request()->routeIs('attendance.index');
    $isPointExchangePage = request()->routeIs('point-exchange.index');
    $isEventApplicationPage = request()->routeIs('event-application.index');
    $currentBoardId = null;
    $currentBoardSlug = request()->route('board');
    
    if ($currentBoardSlug) {
        $currentBoard = Board::where('site_id', $site->id)
            ->where('slug', $currentBoardSlug)
            ->first();
        if ($currentBoard) {
            $currentBoardId = $currentBoard->id;
        }
    }
    
    // 팝업 가져오기
    $popups = Popup::where('site_id', $site->id)
        ->where('is_active', true)
        ->orderBy('order', 'asc')
        ->orderBy('created_at', 'asc')
        ->get();
    
    if ($popups->isEmpty()) {
        return; // 팝업이 없으면 표시하지 않음
    }
    
    // 현재 페이지에 표시할 팝업 필터링
    $displayPopups = $popups->filter(function($popup) use ($isHomePage, $isAttendancePage, $isPointExchangePage, $isEventApplicationPage, $currentBoardId) {
        // target_type이 'all'이면 항상 표시 (공백 제거 후 비교, 대소문자 무시, null 체크)
        $targetType = trim($popup->target_type ?? '');
        $targetTypeLower = strtolower($targetType);
        
        if ($targetTypeLower === 'all' || $targetType === '') {
            return true; // 'all'이거나 비어있으면 모든 페이지에 표시
        } elseif ($targetTypeLower === 'main') {
            return $isHomePage;
        } elseif ($targetTypeLower === 'attendance') {
            return $isAttendancePage;
        } elseif ($targetTypeLower === 'point-exchange') {
            return $isPointExchangePage;
        } elseif ($targetTypeLower === 'event-application') {
            return $isEventApplicationPage;
        } elseif (strpos($targetTypeLower, 'board_') === 0) {
            // board_123 형식인 경우
            $boardId = (int)str_replace('board_', '', $targetTypeLower);
            return $boardId == $currentBoardId;
        }
        return false;
    });
    
    if ($displayPopups->isEmpty()) {
        return; // 표시할 팝업이 없으면 표시하지 않음
    }
    
    // 표시 방식 및 위치 설정
    $displayType = $site->getSetting('popup_display_type', 'overlay');
    $position = $site->getSetting('popup_position', 'center');
    
    // 쿠키에서 오늘 하루 보지 않기 체크
    // 모든 쿠키를 먼저 확인하여 숨겨야 할 팝업 ID 수집
    $hiddenPopupIds = [];
    foreach ($_COOKIE as $cookieName => $cookieValue) {
        // popup_hidden_으로 시작하는 쿠키만 확인
        if (strpos($cookieName, 'popup_hidden_') === 0 && $cookieValue === '1') {
            // popup_hidden_8 형식에서 8 추출
            $popupId = (int)str_replace('popup_hidden_', '', $cookieName);
            if ($popupId > 0) {
                $hiddenPopupIds[] = $popupId;
            }
        }
    }
    
    // 숨겨진 팝업 제외
    $visiblePopups = $displayPopups->reject(function($popup) use ($hiddenPopupIds) {
        return in_array($popup->id, $hiddenPopupIds);
    });
    
    if ($visiblePopups->isEmpty()) {
        return; // 표시할 팝업이 없으면 표시하지 않음
    }
    
    // 위치별 CSS 클래스
    $positionClasses = [
        'center' => 'popup-center',
        'top-left' => 'popup-top-left',
        'top-right' => 'popup-top-right',
        'bottom-left' => 'popup-bottom-left',
        'bottom-right' => 'popup-bottom-right',
    ];
    
    $positionClass = $positionClasses[$position] ?? 'popup-center';
@endphp

@if($displayType === 'overlay')
    {{-- 겹치기 방식: 한 번에 하나씩 표시 --}}
    @php
        $positionStyles = [
            'center' => 'position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important;',
            'top-left' => 'position: absolute !important; top: 20px !important; left: 20px !important; transform: none !important;',
            'top-right' => 'position: absolute !important; top: 20px !important; right: 20px !important; transform: none !important;',
            'bottom-left' => 'position: absolute !important; bottom: 20px !important; left: 20px !important; transform: none !important;',
            'bottom-right' => 'position: absolute !important; bottom: 20px !important; right: 20px !important; transform: none !important;',
        ];
        $contentStyle = $positionStyles[$position] ?? $positionStyles['center'];
    @endphp
    @foreach($visiblePopups->values() as $index => $popup)
        @php
            // 서버 측에서 쿠키 확인하여 초기 display 설정
            $cookieName = 'popup_hidden_' . $popup->id;
            $cookieValue = request()->cookie($cookieName);
            $initialDisplay = ($cookieValue !== null && $cookieValue !== '') ? 'none' : ($index === 0 ? 'flex' : 'none');
        @endphp
        <div class="popup-overlay popup-{{ $popup->id }} {{ $positionClass }}" 
             data-popup-id="{{ $popup->id }}"
             data-popup-index="{{ $index }}"
             style="position: fixed !important; z-index: 99999 !important; width: 100% !important; height: 100% !important; top: 0 !important; left: 0 !important; display: {{ $initialDisplay }} !important;">
            <div class="popup-backdrop"></div>
            <div class="popup-content" style="{{ $contentStyle }} display: flex !important; flex-direction: column !important; position: relative !important; background: white !important; border-radius: 8px !important; padding: 0 !important; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important; width: auto !important; max-width: 400px !important; height: fit-content !important; min-height: auto !important; margin: 0 !important; pointer-events: none !important; align-items: stretch !important;">
                @if($popup->type === 'html')
                    <div class="popup-html-content" style="width: 100% !important; flex: 0 0 auto !important; padding: 20px !important; overflow: auto !important; pointer-events: auto !important;">
                        {!! $popup->html_code !!}
                    </div>
                @else
                    @if($popup->link)
                        <a href="{{ $popup->link }}" 
                           @if($popup->open_new_window) target="_blank" rel="noopener noreferrer" @endif
                           class="popup-link"
                           style="display: block !important; width: 100% !important; flex: 0 0 auto !important; overflow: hidden !important; margin: 0 !important; padding: 0 !important; line-height: 0 !important; pointer-events: auto !important;">
                            <img src="{{ asset('storage/' . $popup->image_path) }}" 
                                 alt="Popup" 
                                 class="popup-image"
                                 style="max-width: 100% !important; width: 100% !important; height: auto !important; display: block !important; margin: 0 !important; padding: 0 !important; border: none !important; outline: none !important; pointer-events: auto !important; border-radius: 8px 8px 0 0 !important;">
                        </a>
                    @else
                        <img src="{{ asset('storage/' . $popup->image_path) }}" 
                             alt="Popup" 
                             class="popup-image"
                             style="max-width: 100% !important; width: 100% !important; height: auto !important; display: block !important; margin: 0 !important; padding: 0 !important; border: none !important; outline: none !important; pointer-events: auto !important; border-radius: 8px 8px 0 0 !important; flex: 0 0 auto !important;">
                    @endif
                @endif
                <div class="popup-footer" style="background: #ffffff !important; background-color: #ffffff !important; padding: 12px 16px !important; display: flex !important; justify-content: space-between !important; align-items: center !important; margin: 0 !important; width: 100% !important; box-sizing: border-box !important; flex-shrink: 0 !important; flex: 0 0 auto !important; min-width: 0 !important; pointer-events: auto !important; border-top: none !important; border-radius: 0 0 8px 8px !important;">
                    <button type="button" class="popup-dont-show-btn" data-popup-id="{{ $popup->id }}" onclick="popupDontShow({{ $popup->id }})" style="background: none !important; background-color: transparent !important; color: #000000 !important; border: none !important; padding: 8px 16px !important; cursor: pointer !important; font-size: 0.875rem !important; transition: opacity 0.2s !important; margin-right: auto !important; margin-left: 0 !important; pointer-events: auto !important; z-index: 100001 !important; position: relative !important;">
                        오늘 하루 보지 않기
                    </button>
                    <button type="button" class="popup-close-btn" data-popup-id="{{ $popup->id }}" onclick="popupClose({{ $popup->id }})" style="background: none !important; background-color: transparent !important; color: #000000 !important; border: none !important; padding: 8px 16px !important; cursor: pointer !important; font-size: 0.875rem !important; transition: opacity 0.2s !important; margin-left: auto !important; margin-right: 0 !important; pointer-events: auto !important; z-index: 100001 !important; position: relative !important;">
                        닫기
                    </button>
                </div>
            </div>
        </div>
    @endforeach
@else
    {{-- 나열하기 방식: 모든 팝업을 나열 --}}
    @php
        $listPositionStyles = [
            'center' => 'top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; justify-content: center !important;',
            'top-left' => 'top: 20px !important; left: 20px !important; justify-content: flex-start !important;',
            'top-right' => 'top: 20px !important; right: 20px !important; justify-content: flex-end !important;',
            'bottom-left' => 'bottom: 20px !important; left: 20px !important; justify-content: flex-start !important;',
            'bottom-right' => 'bottom: 20px !important; right: 20px !important; justify-content: flex-end !important;',
        ];
        $listContainerStyle = $listPositionStyles[$position] ?? $listPositionStyles['center'];
    @endphp
    <div class="popup-list-container {{ $positionClass }}" 
         style="position: fixed !important; z-index: 99999 !important; display: flex !important; flex-direction: row !important; flex-wrap: wrap !important; gap: 15px !important; pointer-events: none !important; max-width: 90% !important; {{ $listContainerStyle }}">
        @foreach($visiblePopups as $popup)
            <div class="popup-item popup-{{ $popup->id }}" data-popup-id="{{ $popup->id }}" style="pointer-events: auto !important; flex: 0 0 auto !important; margin: 0 !important;">
                <div class="popup-content" style="display: flex !important; flex-direction: column !important; position: relative !important; background: white !important; border-radius: 8px !important; padding: 0 !important; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important; width: auto !important; max-width: 400px !important; margin: 0 !important;">
                    @if($popup->type === 'html')
                        <div class="popup-html-content" style="width: 100% !important; flex: 1 !important; padding: 20px !important; overflow: auto !important; pointer-events: auto !important;">
                            {!! $popup->html_code !!}
                        </div>
                    @else
                        @if($popup->link)
                            <a href="{{ $popup->link }}" 
                               @if($popup->open_new_window) target="_blank" rel="noopener noreferrer" @endif
                               class="popup-link"
                               style="display: block !important; width: auto !important; flex: 0 0 auto !important; overflow: hidden !important; margin: 0 !important; padding: 0 !important; line-height: 0 !important; pointer-events: auto !important;">
                                <img src="{{ asset('storage/' . $popup->image_path) }}" 
                                     alt="Popup" 
                                     class="popup-image"
                                     style="max-width: 100% !important; width: auto !important; height: auto !important; display: block !important; margin: 0 !important; padding: 0 !important; border: none !important; outline: none !important; pointer-events: auto !important; border-radius: 8px 8px 0 0 !important;">
                            </a>
                        @else
                            <img src="{{ asset('storage/' . $popup->image_path) }}" 
                                 alt="Popup" 
                                 class="popup-image"
                                 style="max-width: 100% !important; width: auto !important; height: auto !important; display: block !important; margin: 0 !important; padding: 0 !important; border: none !important; outline: none !important; pointer-events: auto !important; border-radius: 8px 8px 0 0 !important;">
                        @endif
                    @endif
                    <div class="popup-footer" style="background: #ffffff !important; background-color: #ffffff !important; padding: 12px 16px !important; display: flex !important; justify-content: space-between !important; align-items: center !important; margin: 0 !important; width: 100% !important; box-sizing: border-box !important; flex-shrink: 0 !important; min-width: 0 !important; pointer-events: auto !important; border-top: none !important; border-radius: 0 0 8px 8px !important;">
                        <button type="button" class="popup-dont-show-btn" data-popup-id="{{ $popup->id }}" onclick="popupDontShow({{ $popup->id }})" style="background: none !important; background-color: transparent !important; color: #000000 !important; border: none !important; padding: 8px 16px !important; cursor: pointer !important; font-size: 0.875rem !important; transition: opacity 0.2s !important; margin-right: auto !important; margin-left: 0 !important; pointer-events: auto !important; z-index: 100001 !important; position: relative !important;">
                            오늘 하루 보지 않기
                        </button>
                        <button type="button" class="popup-close-btn" data-popup-id="{{ $popup->id }}" onclick="popupClose({{ $popup->id }})" style="background: none !important; background-color: transparent !important; color: #000000 !important; border: none !important; padding: 8px 16px !important; cursor: pointer !important; font-size: 0.875rem !important; transition: opacity 0.2s !important; margin-left: auto !important; margin-right: 0 !important; pointer-events: auto !important; z-index: 100001 !important; position: relative !important;">
                            닫기
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('styles')
<style>
    /* 팝업 오버레이 (겹치기 방식) */
    .popup-overlay {
        position: fixed !important;
        z-index: 99999 !important;
        width: 100% !important;
        height: 100% !important;
        top: 0 !important;
        left: 0 !important;
        pointer-events: none !important;
    }
    
    .popup-backdrop {
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        top: 0;
        left: 0;
        pointer-events: none;
    }
    
    .popup-overlay .popup-content {
        z-index: 100000 !important;
        background: transparent !important;
        border-radius: 0;
        padding: 0;
        max-width: 90%;
        height: fit-content !important;
        min-height: auto !important;
        max-height: 90vh;
        overflow: visible !important; /* Changed to visible to allow clicks outside */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: flex !important;
        flex-direction: column !important;
        width: auto !important;
        pointer-events: none !important; /* Make content container non-clickable */
    }
    
    /* 팝업 콘텐츠의 직접 자식 요소들만 클릭 가능하게 */
    .popup-overlay .popup-content > * {
        pointer-events: auto !important;
    }
    
    /* 팝업 이미지와 링크는 클릭 가능 */
    .popup-overlay .popup-image,
    .popup-overlay .popup-link,
    .popup-overlay .popup-html-content,
    .popup-overlay .popup-footer,
    .popup-overlay .popup-dont-show-btn,
    .popup-overlay .popup-close-btn {
        pointer-events: auto !important;
    }
    
    /* 위치별 스타일 - 더 구체적인 선택자 사용 */
    .popup-overlay.popup-center .popup-content {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
    }
    
    .popup-overlay.popup-top-left .popup-content {
        position: absolute !important;
        top: 20px !important;
        left: 20px !important;
        transform: none !important;
    }
    
    .popup-overlay.popup-top-right .popup-content {
        position: absolute !important;
        top: 20px !important;
        right: 20px !important;
        transform: none !important;
    }
    
    .popup-overlay.popup-bottom-left .popup-content {
        position: absolute !important;
        bottom: 20px !important;
        left: 20px !important;
        transform: none !important;
    }
    
    .popup-overlay.popup-bottom-right .popup-content {
        position: absolute !important;
        bottom: 20px !important;
        right: 20px !important;
        transform: none !important;
    }
    
    /* 팝업 닫기 버튼 */
    .popup-close-btn {
        background: none !important;
        background-color: transparent !important;
        color: #000000;
        border: none;
        padding: 8px 16px;
        cursor: pointer;
        font-size: 0.875rem;
        transition: opacity 0.2s;
        margin-left: auto;
    }
    
    .popup-close-btn:hover {
        opacity: 0.7;
    }
    
    /* 팝업 이미지 */
    .popup-image {
        max-width: 100%;
        width: auto !important;
        height: auto;
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none;
        outline: none;
        pointer-events: auto !important; /* 이미지 자체는 클릭 가능 */
    }
    
    /* 팝업 이미지 컨테이너 */
    .popup-link {
        display: block !important;
        width: auto !important;
        flex: 0 0 auto !important;
        overflow: hidden;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 0;
        pointer-events: auto !important; /* 링크는 클릭 가능 */
    }
    
    /* 팝업 이미지 직접 표시 (링크 없는 경우) */
    .popup-content > img.popup-image {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none;
        outline: none;
        flex: 0 0 auto !important;
    }
    
    /* 팝업 HTML 콘텐츠 */
    .popup-html-content {
        width: 100%;
        flex: 0 0 auto !important;
        padding: 20px;
        overflow: auto;
        pointer-events: auto !important; /* HTML 콘텐츠는 클릭 가능 */
    }
    
    /* 팝업 링크 */
    .popup-link {
        display: block !important;
        width: 100% !important;
        flex: 0 0 auto !important;
    }
    
    /* 팝업 푸터 */
    .popup-footer {
        background: #ffffff !important;
        padding: 12px 16px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
        flex-shrink: 0 !important;
        flex: 0 0 auto !important;
        min-width: 0 !important;
        pointer-events: auto !important; /* 푸터는 클릭 가능 */
    }
    
    .popup-dont-show-btn {
        background: none !important;
        background-color: transparent !important;
        color: #000000 !important;
        border: none !important;
        padding: 8px 16px !important;
        cursor: pointer !important;
        font-size: 0.875rem !important;
        transition: opacity 0.2s !important;
        margin-right: auto !important;
        margin-left: 0 !important;
    }
    
    .popup-dont-show-btn:hover {
        opacity: 0.7 !important;
    }
    
    .popup-close-btn {
        background: none !important;
        background-color: transparent !important;
        color: #000000 !important;
        border: none !important;
        padding: 8px 16px !important;
        cursor: pointer !important;
        font-size: 0.875rem !important;
        transition: opacity 0.2s !important;
        margin-left: auto !important;
        margin-right: 0 !important;
    }
    
    .popup-close-btn:hover {
        opacity: 0.7 !important;
    }
    
    /* 나열하기 방식 */
    .popup-list-container {
        position: fixed !important;
        z-index: 99999 !important;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        gap: 15px !important;
        pointer-events: none !important;
        max-width: 90% !important;
    }
    
    .popup-list-container .popup-item {
        pointer-events: auto !important;
        flex: 0 0 auto !important;
    }
    
    .popup-list-container.popup-center {
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        justify-content: center !important;
    }
    
    .popup-list-container.popup-top-left {
        top: 20px !important;
        left: 20px !important;
        justify-content: flex-start !important;
    }
    
    .popup-list-container.popup-top-right {
        top: 20px !important;
        right: 20px !important;
        justify-content: flex-end !important;
    }
    
    .popup-list-container.popup-bottom-left {
        bottom: 20px !important;
        left: 20px !important;
        justify-content: flex-start !important;
    }
    
    .popup-list-container.popup-bottom-right {
        bottom: 20px !important;
        right: 20px !important;
        justify-content: flex-end !important;
    }
    
    .popup-item {
        margin: 0 !important;
    }
    
    .popup-list-container .popup-item .popup-content {
        position: relative !important;
        background: white !important;
        border-radius: 8px !important;
        padding: 0 !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        display: flex !important;
        flex-direction: column !important;
        width: auto !important;
        max-width: 400px !important;
        margin: 0 !important;
    }
    
    /* 나열하기 방식 팝업 푸터 스타일 - 더 구체적인 선택자 사용 */
    .popup-list-container .popup-item .popup-footer {
        background: #ffffff !important;
        background-color: #ffffff !important;
        padding: 12px 16px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
        flex-shrink: 0 !important;
        min-width: 0 !important;
        pointer-events: auto !important;
        border-top: none !important;
        border-radius: 0 0 8px 8px !important;
    }
    
    .popup-list-container .popup-item .popup-dont-show-btn {
        background: none !important;
        background-color: transparent !important;
        color: #000000 !important;
        border: none !important;
        padding: 8px 16px !important;
        cursor: pointer !important;
        font-size: 0.875rem !important;
        transition: opacity 0.2s !important;
        margin-right: auto !important;
        margin-left: 0 !important;
    }
    
    .popup-list-container .popup-item .popup-dont-show-btn:hover {
        opacity: 0.7 !important;
    }
    
    .popup-list-container .popup-item .popup-close-btn {
        background: none !important;
        background-color: transparent !important;
        color: #000000 !important;
        border: none !important;
        padding: 8px 16px !important;
        cursor: pointer !important;
        font-size: 0.875rem !important;
        transition: opacity 0.2s !important;
        margin-left: auto !important;
        margin-right: 0 !important;
    }
    
    .popup-list-container .popup-item .popup-close-btn:hover {
        opacity: 0.7 !important;
    }
    
    /* 나열하기 방식 팝업 이미지 스타일 */
    .popup-list-container .popup-item .popup-image {
        max-width: 100% !important;
        width: auto !important;
        height: auto !important;
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        outline: none !important;
        pointer-events: auto !important;
        border-radius: 8px 8px 0 0 !important;
    }
    
    .popup-list-container .popup-item .popup-link {
        display: block !important;
        width: auto !important;
        flex: 0 0 auto !important;
        overflow: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 0 !important;
        pointer-events: auto !important;
    }
    
    .popup-list-container .popup-item .popup-html-content {
        width: 100% !important;
        flex: 1 !important;
        padding: 20px !important;
        overflow: auto !important;
        pointer-events: auto !important;
    }
    
    /* 모바일 반응형 */
    @media (max-width: 768px) {
        .popup-overlay .popup-content,
        .popup-item .popup-content {
            max-width: 95%;
            padding: 15px;
        }
        
        .popup-top-left .popup-content,
        .popup-top-right .popup-content,
        .popup-bottom-left .popup-content,
        .popup-bottom-right .popup-content {
            top: 10px;
            right: 10px;
            bottom: 10px;
            left: 10px;
        }
        
        .popup-list-container.popup-top-left,
        .popup-list-container.popup-top-right,
        .popup-list-container.popup-bottom-left,
        .popup-list-container.popup-bottom-right {
            top: 10px;
            right: 10px;
            bottom: 10px;
            left: 10px;
            max-width: calc(100% - 20px);
        }
    }
</style>
@endpush

@push('scripts')
<script>
// 전역 함수로 팝업 닫기 및 오늘 하루 보지 않기 처리 (IIFE 외부에 정의)
window.popupDontShow = function(popupId) {
    console.log('popupDontShow called with popupId:', popupId);
    // .popup-overlay 또는 .popup-item에서 해당 버튼을 가진 팝업 찾기
    var allPopups = document.querySelectorAll('.popup-overlay, .popup-item');
    var popup = null;
    for (var i = 0; i < allPopups.length; i++) {
        if (allPopups[i].getAttribute('data-popup-id') == popupId) {
            popup = allPopups[i];
            break;
        }
    }
    if (popup) {
        // 쿠키 설정 (오늘 23:59:59까지)
        var expires = new Date();
        expires.setHours(23, 59, 59, 999);
        var cookieValue = 'popup_hidden_' + popupId + '=1; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';
        document.cookie = cookieValue;
        
        // 팝업 닫기
        if (popup.classList.contains('popup-overlay')) {
            // 겹치기 방식인 경우 다음 팝업 표시
            var currentIndex = parseInt(popup.getAttribute('data-popup-index')) || 0;
            var nextPopup = document.querySelector('.popup-overlay[data-popup-index="' + (currentIndex + 1) + '"]');
            
            if (nextPopup) {
                popup.style.display = 'none';
                nextPopup.style.display = 'flex';
                // 다음 팝업의 버튼 재설정
                setTimeout(function() {
                    if (typeof syncFooterWidth === 'function') {
                        syncFooterWidth();
                    }
                    if (typeof setupPopupButtons === 'function') {
                        setupPopupButtons();
                    }
                }, 100);
            } else {
                popup.style.display = 'none';
            }
        } else {
            // 나열하기 방식인 경우 해당 팝업만 숨김
            popup.style.display = 'none';
        }
    }
    return false;
};

window.popupClose = function(popupId) {
    console.log('popupClose called with popupId:', popupId);
    // .popup-overlay 또는 .popup-item에서 해당 버튼을 가진 팝업 찾기
    var allPopups = document.querySelectorAll('.popup-overlay, .popup-item');
    var popup = null;
    for (var i = 0; i < allPopups.length; i++) {
        if (allPopups[i].getAttribute('data-popup-id') == popupId) {
            popup = allPopups[i];
            break;
        }
    }
    if (popup) {
        // 쿠키 설정하지 않고 단순히 닫기만 (새로고침 시 다시 표시됨)
        if (popup.classList.contains('popup-overlay')) {
            // 겹치기 방식인 경우 다음 팝업 표시
            var currentIndex = parseInt(popup.getAttribute('data-popup-index')) || 0;
            var nextPopup = document.querySelector('.popup-overlay[data-popup-index="' + (currentIndex + 1) + '"]');
            
            if (nextPopup) {
                popup.style.display = 'none';
                nextPopup.style.display = 'flex';
                // 다음 팝업의 버튼 재설정
                setTimeout(function() {
                    if (typeof syncFooterWidth === 'function') {
                        syncFooterWidth();
                    }
                    if (typeof setupPopupButtons === 'function') {
                        setupPopupButtons();
                    }
                }, 100);
            } else {
                popup.style.display = 'none';
            }
        } else {
            // 나열하기 방식인 경우 해당 팝업만 숨김
            popup.style.display = 'none';
        }
    }
    return false;
};

// 즉시 실행하여 초기 렌더링 문제 방지 (IIFE)
(function() {
    // 팝업 이미지와 푸터 너비 맞추기 및 pointer-events 설정
    function syncFooterWidth() {
        document.querySelectorAll('.popup-overlay').forEach(function(popup) {
            var image = popup.querySelector('.popup-image');
            var footer = popup.querySelector('.popup-footer');
            var content = popup.querySelector('.popup-content');
            var htmlContent = popup.querySelector('.popup-html-content');
            var dontShowBtn = popup.querySelector('.popup-dont-show-btn');
            var closeBtn = popup.querySelector('.popup-close-btn');
            
            // 팝업 오버레이는 pointer-events: none으로 설정하여 배경 클릭 가능하게
            popup.style.pointerEvents = 'none';
            
            // 팝업 콘텐츠 컨테이너는 pointer-events: none으로 설정
            // 직접 자식 요소들만 pointer-events: auto로 설정
            if (content) {
                content.style.pointerEvents = 'none';
                // 높이를 fit-content로 설정하여 자동으로 콘텐츠에 맞게 조정
                content.style.setProperty('height', 'fit-content', 'important');
                content.style.setProperty('min-height', 'auto', 'important');
                content.style.setProperty('max-height', 'none', 'important');
                // 직접 자식 요소들에 pointer-events: auto 적용
                Array.from(content.children).forEach(function(child) {
                    child.style.pointerEvents = 'auto';
                });
            }
            
            if (image && footer) {
                var imageWidth = image.offsetWidth || image.getBoundingClientRect().width;
                if (imageWidth > 0) {
                    footer.style.width = imageWidth + 'px';
                    footer.style.minWidth = imageWidth + 'px';
                    footer.style.maxWidth = imageWidth + 'px';
                    footer.style.backgroundColor = '#ffffff';
                    footer.style.display = 'flex';
                    footer.style.justifyContent = 'space-between';
                }
            } else if (htmlContent && footer) {
                // HTML 콘텐츠의 경우 푸터 너비는 콘텐츠 너비에 맞춤
                if (htmlContent && htmlContent.offsetWidth > 0) {
                    footer.style.width = htmlContent.offsetWidth + 'px';
                    footer.style.minWidth = htmlContent.offsetWidth + 'px';
                    footer.style.maxWidth = htmlContent.offsetWidth + 'px';
                }
            }
            
            // 버튼 스타일 직접 적용 (pointer-events를 확실히 auto로 설정)
            if (dontShowBtn) {
                dontShowBtn.style.setProperty('pointer-events', 'auto', 'important');
                dontShowBtn.style.setProperty('cursor', 'pointer', 'important');
                dontShowBtn.style.setProperty('z-index', '100001', 'important');
                dontShowBtn.style.setProperty('position', 'relative', 'important');
                dontShowBtn.style.setProperty('background-color', 'transparent', 'important');
                dontShowBtn.style.setProperty('background', 'none', 'important');
                dontShowBtn.style.setProperty('color', '#000000', 'important');
                dontShowBtn.style.setProperty('border', 'none', 'important');
                dontShowBtn.style.setProperty('margin-right', 'auto', 'important');
                dontShowBtn.style.setProperty('margin-left', '0', 'important');
                
                // 이벤트 리스너 직접 등록 (중복 방지)
                if (!dontShowBtn.hasAttribute('data-click-attached')) {
                    dontShowBtn.setAttribute('data-click-attached', 'true');
                    dontShowBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        var popupId = this.getAttribute('data-popup-id');
                        if (window.popupDontShow) {
                            window.popupDontShow(popupId);
                        }
                        return false;
                    }, true);
                }
            }
            
            if (closeBtn) {
                closeBtn.style.setProperty('pointer-events', 'auto', 'important');
                closeBtn.style.setProperty('cursor', 'pointer', 'important');
                closeBtn.style.setProperty('z-index', '100001', 'important');
                closeBtn.style.setProperty('position', 'relative', 'important');
                closeBtn.style.setProperty('background-color', 'transparent', 'important');
                closeBtn.style.setProperty('background', 'none', 'important');
                closeBtn.style.setProperty('color', '#000000', 'important');
                closeBtn.style.setProperty('border', 'none', 'important');
                closeBtn.style.setProperty('margin-left', 'auto', 'important');
                closeBtn.style.setProperty('margin-right', '0', 'important');
                
                // 이벤트 리스너 직접 등록 (중복 방지)
                if (!closeBtn.hasAttribute('data-click-attached')) {
                    closeBtn.setAttribute('data-click-attached', 'true');
                    closeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        var popupId = this.getAttribute('data-popup-id');
                        if (window.popupClose) {
                            window.popupClose(popupId);
                        }
                        return false;
                    }, true);
                }
            }
        });
        
        // 나열하기 방식도 동일하게 처리
        document.querySelectorAll('.popup-list-container').forEach(function(container) {
            container.style.pointerEvents = 'none';
            container.querySelectorAll('.popup-item').forEach(function(item) {
                item.style.pointerEvents = 'auto';
            });
        });
    }
    
    // 쿠키에서 팝업 숨김 여부 확인하는 함수
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
        return null;
    }
    
    // 쿠키를 확인하여 숨겨야 할 팝업 숨기기
    function hidePopupsByCookie() {
        var hidden = false;
        document.querySelectorAll('.popup-overlay, .popup-item').forEach(function(popup) {
            var popupId = popup.getAttribute('data-popup-id');
            if (popupId) {
                var cookieName = 'popup_hidden_' + popupId;
                var cookieValue = getCookie(cookieName);
                // 쿠키가 존재하면 팝업 숨기기
                if (cookieValue && cookieValue === '1') {
                    popup.style.setProperty('display', 'none', 'important');
                    hidden = true;
                }
            }
        });
        return hidden;
    }
    
    // 즉시 실행 함수
    function initPopup() {
        // 쿠키 확인하여 숨겨야 할 팝업 숨기기
        hidePopupsByCookie();
        
        // 즉시 실행
        syncFooterWidth();
        
        // 버튼 이벤트 리스너 등록
        setupPopupButtons();
        
        // 이미지 로드 후에도 실행
        document.querySelectorAll('.popup-image').forEach(function(img) {
            if (img.complete) {
                syncFooterWidth();
            } else {
                img.addEventListener('load', function() {
                    syncFooterWidth();
                    setupPopupButtons(); // 이미지 로드 후에도 버튼 재설정
                }, { once: true });
            }
        });
    }
    
    // 즉시 실행 함수로 감싸서 즉시 실행
    (function() {
        function runHidePopups() {
            hidePopupsByCookie();
        }
        
        // DOM이 준비되면 실행
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                runHidePopups();
                initPopup();
            });
        } else {
            runHidePopups();
            requestAnimationFrame(function() {
                initPopup();
            });
        }
        
        // 추가로 여러 번 실행 (팝업이 늦게 로드되는 경우 대비)
        setTimeout(runHidePopups, 50);
        setTimeout(runHidePopups, 100);
        setTimeout(runHidePopups, 200);
        setTimeout(runHidePopups, 500);
    })();
    
    // 윈도우 리사이즈 시에도 실행
    window.addEventListener('resize', syncFooterWidth);
    
    // 버튼 이벤트 리스너 등록 함수
    function setupPopupButtons() {
        // 오늘 하루 보지 않기 버튼 클릭
        document.querySelectorAll('.popup-dont-show-btn').forEach(function(btn) {
            // 이미 이벤트 리스너가 등록되어 있는지 확인
            if (!btn.hasAttribute('data-listener-attached')) {
                btn.setAttribute('data-listener-attached', 'true');
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    var popupId = this.getAttribute('data-popup-id');
                    var popup = document.querySelector('.popup-' + popupId);
                    
                    if (popup) {
                        // 쿠키 설정 (오늘 23:59:59까지)
                        var expires = new Date();
                        expires.setHours(23, 59, 59, 999);
                        var cookieValue = 'popup_hidden_' + popupId + '=1; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';
                        document.cookie = cookieValue;
                        
                        // 팝업 닫기
                        closePopup(popup);
                    }
                    return false;
                }, true); // capture phase에서 실행
            }
        });
        
        // 팝업 닫기 버튼 클릭
        document.querySelectorAll('.popup-close-btn').forEach(function(btn) {
            // 이미 이벤트 리스너가 등록되어 있는지 확인
            if (!btn.hasAttribute('data-listener-attached')) {
                btn.setAttribute('data-listener-attached', 'true');
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    var popupId = this.getAttribute('data-popup-id');
                    var popup = document.querySelector('.popup-' + popupId);
                    
                    if (popup) {
                        // 쿠키 설정하지 않고 단순히 닫기만 (새로고침 시 다시 표시됨)
                        closePopup(popup);
                    }
                    return false;
                }, true); // capture phase에서 실행
            }
        });
    }
    
    // 팝업 닫기 함수
    function closePopup(popup) {
        // 겹치기 방식인 경우 다음 팝업 표시
        if (popup.classList.contains('popup-overlay')) {
            var currentIndex = parseInt(popup.getAttribute('data-popup-index'));
            var nextPopup = document.querySelector('.popup-overlay[data-popup-index="' + (currentIndex + 1) + '"]');
            
            if (nextPopup) {
                popup.style.display = 'none';
                nextPopup.style.display = 'flex';
                // 다음 팝업이 표시될 때 푸터 너비 다시 동기화 및 버튼 재설정
                setTimeout(function() {
                    syncFooterWidth();
                    setupPopupButtons();
                }, 100);
            } else {
                popup.style.display = 'none';
            }
        } else {
            // 나열하기 방식인 경우 해당 팝업만 숨김
            popup.style.display = 'none';
        }
    }
    
    // 배경 클릭 시 닫기 (겹치기 방식만)
    document.querySelectorAll('.popup-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function() {
            var popup = this.closest('.popup-overlay');
            if (popup) {
                var closeBtn = popup.querySelector('.popup-close-btn');
                if (closeBtn) {
                    closeBtn.click();
                }
            }
        });
    });
});
</script>
@endpush

