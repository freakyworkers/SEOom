@php
    // Check if site has chat widget feature
    if (!$site->hasFeature('chat_widget')) {
        return;
    }

    $chatSetting = \App\Models\ChatSetting::firstOrCreate(
        ['site_id' => $site->id],
        [
            'notice' => null,
            'auto_delete_24h' => false,
            'allow_guest' => false,
            'banned_words' => null,
        ]
    );
    $isGuest = !auth()->check();
    
    // 게스트 채팅 허용 여부 확인 (채팅창은 항상 표시하되, 전송 시에만 체크)
    $allowGuestChat = $chatSetting->allow_guest;

    // Get user info
    $userId = auth()->id();
    $guestSessionId = null;
    $nickname = null;
    
    if ($userId) {
        $nickname = auth()->user()->nickname ?? auth()->user()->name;
    } else {
        $sessionId = session()->getId();
        $guestSession = \App\Models\ChatGuestSession::getOrCreate($sessionId, $site->id, request()->ip(), request()->userAgent());
        $guestSessionId = $guestSession->session_id; // ChatMessage 테이블의 guest_session_id는 session_id를 저장
        $nickname = $guestSession->getNickname();
    }

    // Check penalties
    $chatPenalty = \App\Models\Penalty::where('site_id', $site->id)
        ->where(function($q) use ($userId, $guestSessionId) {
            if ($userId) {
                $q->where('user_id', $userId);
            } else {
                $q->where('guest_session_id', $guestSessionId);
            }
        })
        ->where('type', 'chat_ban')
        ->where('is_active', true)
        ->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        })
        ->orderByDesc('created_at')
        ->first();
        
    $hasPenalty = !is_null($chatPenalty);
    $penaltyExpiresAt = $chatPenalty && $chatPenalty->expires_at ? $chatPenalty->expires_at->toIso8601String() : null;
    $penaltyRemainingText = $chatPenalty
        ? ($chatPenalty->expires_at
            ? now()->diffForHumans($chatPenalty->expires_at, [
                'parts' => 2,
                'short' => true,
                'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE
            ])
            : '제한 없음')
        : null;

    // Get API routes - 커스텀 도메인 여부에 따라 다르게 설정
    $currentHost = request()->getHost();
    $isCustomDomain = $site->domain && ($currentHost === $site->domain || $currentHost === 'www.' . $site->domain);
    $isSubdomain = !$isCustomDomain && str_contains($currentHost, '.' . config('app.master_domain', 'seoomweb.com'));
    
    // 커스텀 도메인이나 서브도메인을 사용하는 경우 /site/{slug} 접두사 불필요
    if ($isCustomDomain || $isSubdomain) {
        $apiBaseUrl = "";
    } else {
        $apiBaseUrl = "/site/{$site->slug}";
    }
    
    $getMessagesUrl = $apiBaseUrl . '/api/chat/messages';
    $sendMessageUrl = $apiBaseUrl . '/api/chat/messages';
    $reportUrl = $apiBaseUrl . '/api/chat/report';
    $blockUrl = $apiBaseUrl . '/api/chat/block';
    $csrfToken = csrf_token();
    
    // 다크모드 설정
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $isDark = $themeDarkMode === 'dark';
    
    // 다크모드 색상
    $chatBgColor = $isDark ? '#2b2b2b' : 'white';
    $chatMessagesBgColor = $isDark ? '#1e1e1e' : '#f8f9fa';
    $chatHeaderBgColor = $isDark ? '#333333' : '#f8f9fa';
    $chatBorderColor = $isDark ? '#444444' : '#dee2e6';
    $chatMessageBgColor = $isDark ? '#2b2b2b' : 'white';
    $chatMessageBorderColor = $isDark ? '#444444' : '#e9ecef';
    $chatTextColor = $isDark ? '#ffffff' : '#212529';
    $chatMutedColor = $isDark ? '#adb5bd' : '#6c757d';
    $chatInputBgColor = $isDark ? '#333333' : 'white';
    $chatInputBorderColor = $isDark ? '#555555' : '#dee2e6';
@endphp

<div class="chat-widget-container d-none d-md-block" id="chatWidget_{{ $site->id }}" data-site-id="{{ $site->id }}">
    {{-- 헤더는 모바일 모달에서만 표시 (닫기 버튼용) --}}
    <div class="chat-widget-header d-none">
        <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>채팅</h6>
        <button type="button" class="btn-close" id="chatWidgetCloseBtn_{{ $site->id }}" aria-label="Close" style="display: none;"></button>
    </div>
    
    @if($hasPenalty)
    <div class="alert alert-warning mb-0 rounded-0" style="border-left: none; border-right: none; border-top: none;">
        <small><i class="bi bi-exclamation-triangle me-1"></i>채팅이 금지되었습니다. @if($penaltyRemainingText) (남은기간: {{ $penaltyRemainingText }}) @endif</small>
    </div>
    @endif
    
    @if($chatSetting->notice)
    <div class="chat-notice alert alert-info mb-0 rounded-0" style="border-left: none; border-right: none; border-top: none;">
        <small>{{ $chatSetting->notice }}</small>
    </div>
    @endif
    
    <div class="chat-messages" id="chatMessages_{{ $site->id }}" style="height: 400px; overflow-y: auto; padding: 15px; background-color: {{ $chatMessagesBgColor }}; color: {{ $chatTextColor }};">
        <!-- Messages will be loaded here -->
    </div>
    
    <div class="chat-input-container" style="border-top: 1px solid {{ $chatBorderColor }}; padding: 10px; background-color: {{ $chatBgColor }}; color: {{ $chatTextColor }};">
        <div class="d-flex align-items-end gap-2">
            <div class="flex-grow-1">
                <div class="input-group">
                    <button class="btn btn-sm btn-outline-secondary" type="button" id="emojiBtn_{{ $site->id }}" title="이모지">
                        <i class="bi bi-emoji-smile"></i>
                    </button>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           id="chatInput_{{ $site->id }}" 
                           placeholder="메시지를 입력하세요..." 
                           maxlength="1000"
                           style="background-color: {{ $chatInputBgColor }}; color: {{ $chatTextColor }}; border-color: {{ $chatInputBorderColor }};">
                    <label class="btn btn-sm btn-outline-secondary" for="chatFileInput_{{ $site->id }}" title="이미지 첨부">
                        <i class="bi bi-image"></i>
                        <input type="file" 
                               id="chatFileInput_{{ $site->id }}" 
                               accept="image/*" 
                               style="display: none;">
                    </label>
                    <button class="btn btn-sm btn-primary" type="button" id="sendBtn_{{ $site->id }}">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
                <div id="chatPreview_{{ $site->id }}" class="mt-2" style="display: none;">
                    <img id="chatPreviewImg_{{ $site->id }}" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 4px;">
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="removePreviewBtn_{{ $site->id }}">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 채팅 금지 안내 모달 -->
<div class="modal fade" id="chatBanModal_{{ $site->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">채팅이 금지되었습니다.</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">채팅 금지 패널티가 적용 중입니다.</p>
                <p class="text-muted mb-0" id="chatBanRemaining_{{ $site->id }}">
                    @if($penaltyRemainingText)
                        남은기간: {{ $penaltyRemainingText }}
                    @else
                        남은기간 정보가 없습니다.
                    @endif
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.chat-widget-container {
    border: 1px solid {{ $chatBorderColor }};
    border-radius: 4px;
    background-color: {{ $chatBgColor }};
    color: {{ $chatTextColor }};
}

/* 모바일에서 채팅 위젯 숨김 (모달로 표시되므로) */
@media (max-width: 767.98px) {
    .chat-widget-container:not(.mobile-modal) {
        display: none !important;
    }
    
    /* 모바일 모달 오버레이 */
    .chat-widget-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 10000;
    }
    
    .chat-widget-modal-overlay.active {
        display: block;
    }
    
    /* 모바일에서 채팅 위젯을 모달로 표시 */
    .chat-widget-container.mobile-modal {
        display: flex !important;
        flex-direction: column;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        top: auto;
        height: 80vh;
        max-height: 80vh;
        z-index: 10001;
        border-radius: 20px 20px 0 0;
        transform: translateY(100%);
        transition: transform 0.3s ease-out;
        margin: 0;
        border: none;
        border-top: 1px solid #dee2e6;
    }
    
    .chat-widget-container.mobile-modal.show {
        transform: translateY(0);
    }
    
    .chat-widget-container.mobile-modal .chat-messages {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }
}

.chat-widget-header {
    padding: 10px 15px;
    background-color: {{ $chatHeaderBgColor }};
    border-bottom: 1px solid {{ $chatBorderColor }};
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: {{ $chatTextColor }};
}

.chat-message {
    margin-bottom: 10px;
    padding: 8px;
    background-color: {{ $chatMessageBgColor }};
    border-radius: 4px;
    border: 1px solid {{ $chatMessageBorderColor }};
    color: {{ $chatTextColor }};
}

.chat-message-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 5px;
}

.chat-message-nickname {
    font-weight: bold;
    font-size: 0.9em;
    cursor: pointer;
    position: relative;
}

.chat-message-time {
    font-size: 0.75em;
    color: {{ $chatMutedColor }};
}

.chat-message-content {
    word-wrap: break-word;
    color: {{ $chatTextColor }};
}

.chat-message-image {
    max-width: 100%;
    border-radius: 4px;
    margin-top: 5px;
}

.chat-user-menu {
    position: absolute;
    background: {{ $chatBgColor }};
    border: 1px solid {{ $chatBorderColor }};
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 1000;
    min-width: 150px;
    padding: 5px 0;
    display: none;
    color: {{ $chatTextColor }};
}

.chat-user-menu-item {
    padding: 8px 15px;
    cursor: pointer;
    font-size: 0.9em;
}

.chat-user-menu-item:hover {
    background-color: #f8f9fa;
}

/* 모바일 채팅 아이콘 스타일 */
.mobile-chat-icon-wrapper {
    display: none !important;
    visibility: hidden !important;
}

@media screen and (max-width: 767.98px) {
    /* 모바일에서 PC 채팅 위젯 완전히 숨기기 */
    .chat-widget-container {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }
    
    .mobile-chat-icon-wrapper {
        display: block !important;
        visibility: visible !important;
        position: fixed !important;
        left: 15px !important;
        bottom: 90px !important; /* 모바일 하단 메뉴 위에 표시 */
        z-index: 9999 !important;
        cursor: pointer !important;
        width: 56px !important;
        height: 56px !important;
        min-width: 56px !important;
        min-height: 56px !important;
        max-width: 56px !important;
        max-height: 56px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-sizing: border-box !important;
        overflow: visible !important;
        opacity: 1 !important;
    }
    
    /* 모바일 하단 메뉴가 없는 경우 */
    body:not(:has(.mobile-bottom-menu-wrapper)) .mobile-chat-icon-wrapper {
        bottom: 20px !important;
    }
    
    /* body에 직접 있는 경우 강제 표시 */
    body > .mobile-chat-icon-wrapper {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .mobile-chat-icon-wrapper .mobile-chat-icon {
        width: 56px !important;
        height: 56px !important;
        background-color: #0d6efd !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-sizing: border-box !important;
        transition: transform 0.2s, box-shadow 0.2s !important;
    }
    
    .mobile-chat-icon-wrapper .mobile-chat-icon:active {
        transform: scale(0.95) !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
    }

    .mobile-chat-icon-wrapper .mobile-chat-icon i {
        font-size: 28px !important;
        color: white !important;
        display: inline-block !important;
        line-height: 1 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* 모바일 채팅 모달 스타일 */
    .mobile-chat-modal {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        top: 0;
        z-index: 10000;
        background-color: rgba(0,0,0,0.5);
    }
    
    .mobile-chat-modal-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: {{ $chatBgColor }};
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        height: 80vh;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        transform: translateY(100%);
        transition: transform 0.3s ease-out;
        color: {{ $chatTextColor }};
    }
    
    .mobile-chat-modal-header {
        padding: 15px;
        border-bottom: 1px solid {{ $chatBorderColor }};
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        background-color: {{ $chatHeaderBgColor }};
        color: {{ $chatTextColor }};
    }
    
    .mobile-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        background-color: {{ $chatMessagesBgColor }};
        min-height: 200px;
        -webkit-overflow-scrolling: touch;
        color: {{ $chatTextColor }};
    }
    
    .mobile-chat-input-container {
        border-top: 1px solid {{ $chatBorderColor }};
        padding: 10px;
        background-color: {{ $chatBgColor }};
        flex-shrink: 0;
        color: {{ $chatTextColor }};
    }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    const siteId = {{ $site->id }};
    const widgetId = 'chatWidget_' + siteId;
    const messagesId = 'chatMessages_' + siteId;
    const inputId = 'chatInput_' + siteId;
    const sendBtnId = 'sendBtn_' + siteId;
    const fileInputId = 'chatFileInput_' + siteId;
    const previewId = 'chatPreview_' + siteId;
    const previewImgId = 'chatPreviewImg_' + siteId;
    const removePreviewBtnId = 'removePreviewBtn_' + siteId;
    const emojiBtnId = 'emojiBtn_' + siteId;
    
    const getMessagesUrl = '{{ $getMessagesUrl }}';
    const sendMessageUrl = '{{ $sendMessageUrl }}';
    const reportUrl = '{{ $reportUrl }}';
    const blockUrl = '{{ $blockUrl }}';
    const csrfToken = '{{ $csrfToken }}';
    const nickname = '{{ $nickname }}';
    const isGuest = {{ $isGuest ? 'true' : 'false' }};
    const isAdmin = {{ auth()->check() && auth()->user()->canManage() ? 'true' : 'false' }};
    const allowGuestChat = {{ $allowGuestChat ? 'true' : 'false' }};
    const hasChatPenalty = {{ $hasPenalty ? 'true' : 'false' }};
    const chatPenaltyRemainingText = {!! $penaltyRemainingText ? "'남은기간: {$penaltyRemainingText}'" : "null" !!};
    
    // 다크모드 색상 변수
    const isDarkMode = {{ $isDark ? 'true' : 'false' }};
    const chatColors = {
        bg: '{{ $chatBgColor }}',
        messagesBg: '{{ $chatMessagesBgColor }}',
        headerBg: '{{ $chatHeaderBgColor }}',
        border: '{{ $chatBorderColor }}',
        messageBg: '{{ $chatMessageBgColor }}',
        messageBorder: '{{ $chatMessageBorderColor }}',
        text: '{{ $chatTextColor }}',
        muted: '{{ $chatMutedColor }}',
        inputBg: '{{ $chatInputBgColor }}',
        inputBorder: '{{ $chatInputBorderColor }}'
    };
    
    let selectedFile = null;
    let pollInterval = null;
    
    function showChatBanModal() {
        const remainingEl = document.getElementById('chatBanRemaining_' + siteId);
        if (remainingEl && chatPenaltyRemainingText) {
            remainingEl.textContent = chatPenaltyRemainingText;
        }
        const modalEl = document.getElementById('chatBanModal_' + siteId);
        if (modalEl && window.bootstrap) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            alert('채팅이 금지되었습니다.' + (chatPenaltyRemainingText ? '\\n' + chatPenaltyRemainingText : ''));
        }
    }
    
    // Load messages
    function loadMessages() {
        return fetch(getMessagesUrl, {
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading messages:', data.error);
                return;
            }
            
            const messagesContainer = document.getElementById(messagesId);
            messagesContainer.innerHTML = '';
            
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    appendMessage(msg);
                });
            }
            
            scrollToBottom();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Append message
    function appendMessage(msg) {
        const messagesContainer = document.getElementById(messagesId);
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message';
        messageDiv.dataset.messageId = msg.id;
        
        const time = new Date(msg.created_at).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' });
        
        messageDiv.innerHTML = `
            <div class="chat-message-header">
                <span class="chat-message-nickname" data-user-id="${msg.user_id || ''}" data-guest-session-id="${msg.guest_session_id || ''}" data-nickname="${msg.nickname}" data-message-id="${msg.id}">
                    ${msg.nickname}
                </span>
                <span class="chat-message-time">${time}</span>
            </div>
            <div class="chat-message-content">
                ${escapeHtml(msg.message || msg.content || '')}
            </div>
            ${(msg.attachment_path || msg.file_path) ? `<img src="/storage/${msg.attachment_path || msg.file_path}" class="chat-message-image" alt="Attachment">` : ''}
        `;
        
        messagesContainer.appendChild(messageDiv);
        
        // Add click handler for nickname
        const nicknameEl = messageDiv.querySelector('.chat-message-nickname');
        if (nicknameEl) {
            nicknameEl.addEventListener('click', function(e) {
                e.stopPropagation();
                showUserMenu(this, e);
            });
        }
    }
    
    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Scroll to bottom
    function scrollToBottom() {
        const messagesContainer = document.getElementById(messagesId);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Send message
    function sendMessage() {
        // 게스트 채팅 허용 체크
        if (isGuest && !allowGuestChat) {
            alert('비로그인 사용자는 채팅을 사용할 수 없습니다.');
            return;
        }
        
        if (hasChatPenalty) {
            showChatBanModal();
            return;
        }
        
        // 모바일 모달이 열려있으면 모바일 모달의 입력 필드 사용, 아니면 원본 사용
        let widget = document.getElementById(widgetId);
        let isMobileModal = widget && widget.classList.contains('mobile-modal');
        
        // 입력 필드 찾기 - 여러 방법으로 시도
        let input = null;
        let fileInput = null;
        
        if (isMobileModal && widget) {
            input = widget.querySelector('#' + inputId);
            fileInput = widget.querySelector('#' + fileInputId);
        }
        
        // 위젯 내에서 찾지 못하면 document에서 직접 찾기
        if (!input) {
            input = document.getElementById(inputId);
        }
        if (!fileInput) {
            fileInput = document.getElementById(fileInputId);
        }
        
        if (!input) {
            console.error('Chat input not found:', inputId);
            return;
        }
        
        const message = input.value.trim();
        
        // 파일 가져오기 (selectedFile 변수 또는 파일 입력 필드에서 직접)
        let fileToSend = selectedFile;
        if (!fileToSend && fileInput && fileInput.files && fileInput.files.length > 0) {
            fileToSend = fileInput.files[0];
        }
        
        if (!message && !fileToSend) {
            return;
        }
        
        const formData = new FormData();
        formData.append('message', message);
        if (fileToSend) {
            formData.append('attachment', fileToSend);
        }
        formData.append('_token', csrfToken);
        
        fetch(sendMessageUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                if (data.error === '채팅이 금지되었습니다.') {
                    showChatBanModal();
                    return;
                }
                if (data.error === '금지 단어가 포함되었습니다.') {
                    alert('금지 단어가 포함되었습니다.');
                } else {
                    alert(data.error);
                }
                return;
            }
            
            if (data.success) {
                input.value = '';
                selectedFile = null;
                
                // 미리보기 숨기기
                let preview = null;
                if (isMobileModal && widget) {
                    preview = widget.querySelector('#' + previewId);
                }
                if (!preview) {
                    preview = document.getElementById(previewId);
                }
                if (preview) preview.style.display = 'none';
                
                // 파일 입력 초기화
                if (fileInput) fileInput.value = '';
                
                loadMessages().then(() => {
                    // 모바일 모달인 경우 메시지 로드 후 닫기 버튼 이벤트 재연결
                    if (isMobileModal && widget) {
                        const closeBtn = widget.querySelector('#chatWidgetCloseBtn_' + siteId);
                        if (closeBtn) {
                            closeBtn.onclick = function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                const closeFunc = window['closeMobileChatModal_' + siteId];
                                if (closeFunc && typeof closeFunc === 'function') {
                                    closeFunc();
                                }
                            };
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('메시지 전송에 실패했습니다.');
        });
    }
    
    // Show user menu
    function showUserMenu(nicknameEl, event) {
        const userId = nicknameEl.dataset.userId;
        const guestSessionId = nicknameEl.dataset.guestSessionId;
        const targetNickname = nicknameEl.dataset.nickname;
        const messageId = nicknameEl.dataset.messageId;
        
        // Remove existing menu
        const existingMenu = document.querySelector('.chat-user-menu');
        if (existingMenu) {
            existingMenu.remove();
        }
        
        const menu = document.createElement('div');
        menu.className = 'chat-user-menu';
        menu.style.display = 'block';
        menu.style.left = event.pageX + 'px';
        menu.style.top = event.pageY + 'px';
        
        const menuItems = [];
        
        if (!isAdmin) {
            menuItems.push({ text: '신고하기', action: () => reportUser(userId, guestSessionId, targetNickname, messageId) });
            menuItems.push({ text: '차단하기', action: () => blockUser(userId, guestSessionId, targetNickname) });
        } else {
            menuItems.push({ text: '신고하기', action: () => reportUser(userId, guestSessionId, targetNickname, messageId) });
            menuItems.push({ text: '차단하기', action: () => blockUser(userId, guestSessionId, targetNickname) });
            menuItems.push({ text: '채팅금지', action: () => banUserChat(userId, guestSessionId, targetNickname) });
        }
        
        menuItems.push({ text: '쪽지보내기', action: () => sendMessageToUser(userId, guestSessionId, targetNickname) });
        
        menuItems.forEach(item => {
            const menuItem = document.createElement('div');
            menuItem.className = 'chat-user-menu-item';
            menuItem.textContent = item.text;
            menuItem.addEventListener('click', item.action);
            menu.appendChild(menuItem);
        });
        
        document.body.appendChild(menu);
        
        // Close menu on outside click
        setTimeout(() => {
            document.addEventListener('click', function closeMenu() {
                menu.remove();
                document.removeEventListener('click', closeMenu);
            });
        }, 0);
    }
    
    // Report user
    function reportUser(userId, guestSessionId, targetNickname, messageId) {
        // 신고 사유 입력 모달 생성
        const modal = document.createElement('div');
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">신고하기</h5>
                        <button type="button" class="btn-close" onclick="this.closest('.modal').remove()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reportReason" class="form-label">신고 사유를 작성해주세요</label>
                            <textarea class="form-control" id="reportReason" rows="4" placeholder="신고 사유를 입력하세요..." maxlength="500"></textarea>
                            <small class="text-muted">최대 500자까지 입력 가능합니다.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">취소</button>
                        <button type="button" class="btn btn-primary" id="submitReport">신고하기</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // 신고 제출 버튼 클릭 이벤트
        modal.querySelector('#submitReport').addEventListener('click', function() {
            const reason = modal.querySelector('#reportReason').value.trim();
            
            if (!reason) {
                alert('신고 사유를 입력해주세요.');
                return;
            }
            
            fetch(reportUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message_id: messageId,
                    reason: reason,
                })
            })
            .then(response => response.json())
            .then(data => {
                modal.remove();
                if (data.success) {
                    alert('신고가 접수되었습니다.');
                } else {
                    alert(data.error || '신고 접수에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modal.remove();
                alert('신고 접수에 실패했습니다.');
            });
        });
        
        // 모달 외부 클릭 시 닫기
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
    
    // Block user
    function blockUser(userId, guestSessionId, targetNickname) {
        if (!confirm(`${targetNickname}님을 차단하시겠습니까?`)) return;
        
        fetch(blockUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                user_id: userId || null,
                guest_session_id: guestSessionId || null,
                nickname: targetNickname,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('사용자가 차단되었습니다.');
            } else {
                alert(data.error || '차단에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('차단에 실패했습니다.');
        });
    }
    
    // Ban user chat (admin only)
    function banUserChat(userId, guestSessionId, targetNickname) {
        if (!isAdmin) return;
        
        const reason = prompt('채팅 금지 사유를 입력하세요 (선택사항):');
        if (reason === null) return;
        
        // This would need to be implemented in the admin panel
        alert('관리자 패널에서 채팅 금지를 설정해주세요.');
    }
    
    // Send message to user
    function sendMessageToUser(userId, guestSessionId, targetNickname) {
        // Redirect to message page or open message modal
        alert('쪽지 기능은 별도로 구현되어 있습니다.');
    }
    
    // File input handler
    document.getElementById(fileInputId).addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            selectedFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewImgId).src = e.target.result;
                document.getElementById(previewId).style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Remove preview
    document.getElementById(removePreviewBtnId).addEventListener('click', function() {
        selectedFile = null;
        document.getElementById(previewId).style.display = 'none';
        document.getElementById(fileInputId).value = '';
    });
    
    // Send button
    document.getElementById(sendBtnId).addEventListener('click', sendMessage);
    
    // Enter key
    document.getElementById(inputId).addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Emoji picker
    let emojiPickerVisible = false;
    const emojiPicker = document.createElement('div');
    emojiPicker.id = 'emojiPicker_' + siteId;
    emojiPicker.className = 'emoji-picker';
    emojiPicker.style.cssText = 'position: absolute; bottom: 50px; left: 10px; background: ' + chatColors.bg + '; border: 1px solid ' + chatColors.border + '; border-radius: 8px; padding: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; display: none; max-width: 300px; max-height: 300px; overflow-y: auto;';
    
    // Common emojis
    const emojis = ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻', '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾'];
    
    emojis.forEach(emoji => {
        const emojiBtn = document.createElement('button');
        emojiBtn.type = 'button';
        emojiBtn.textContent = emoji;
        emojiBtn.style.cssText = 'background: none; border: none; font-size: 24px; padding: 5px; cursor: pointer; width: 35px; height: 35px; display: inline-block; text-align: center;';
        emojiBtn.addEventListener('click', function() {
            const input = document.getElementById(inputId);
            input.value += emoji;
            input.focus();
            emojiPicker.style.display = 'none';
            emojiPickerVisible = false;
        });
        emojiPicker.appendChild(emojiBtn);
    });
    
    document.getElementById(widgetId).appendChild(emojiPicker);
    
    document.getElementById(emojiBtnId).addEventListener('click', function(e) {
        e.stopPropagation();
        emojiPickerVisible = !emojiPickerVisible;
        emojiPicker.style.display = emojiPickerVisible ? 'block' : 'none';
    });
    
    // Close emoji picker when clicking outside
    document.addEventListener('click', function(e) {
        if (!emojiPicker.contains(e.target) && e.target !== document.getElementById(emojiBtnId)) {
            emojiPicker.style.display = 'none';
            emojiPickerVisible = false;
        }
    });
    
    // Initial load
    loadMessages();
    
    // Poll for new messages every 3 seconds
    pollInterval = setInterval(loadMessages, 3000);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (pollInterval) {
            clearInterval(pollInterval);
        }
    });
    
    // 전역으로 함수 및 변수 노출
    window['sendMessage_' + siteId] = sendMessage;
    window['loadMessages_' + siteId] = loadMessages;
    window['selectedFile_' + siteId] = null; // selectedFile을 전역으로 노출
    
    // selectedFile getter/setter
    window['setSelectedFile_' + siteId] = function(file) {
        selectedFile = file;
        window['selectedFile_' + siteId] = file;
    };
    window['getSelectedFile_' + siteId] = function() {
        return selectedFile || window['selectedFile_' + siteId];
    };
    
    // 전역으로 노출 (모바일에서 호출 가능하도록)
    window.loadMessages = loadMessages; // 간단한 이름으로도 접근 가능
})();
</script>
@endpush

{{-- 모바일 채팅 아이콘 및 모달 --}}
@if($site->hasFeature('chat_widget'))
@php
    // 모바일 고정메뉴 존재 여부 확인
    $hasMobileMenu = false;
    if (\Illuminate\Support\Facades\Schema::hasTable('mobile_menus')) {
        $hasMobileMenu = \App\Models\MobileMenu::where('site_id', $site->id)->count() > 0;
    }
@endphp

<div class="mobile-chat-icon-wrapper" id="mobileChatIcon_{{ $site->id }}" data-site-id="{{ $site->id }}">
    <div class="mobile-chat-icon">
        <i class="bi bi-chat-dots"></i>
    </div>
</div>

{{-- 모바일 모달 오버레이 --}}
<div class="chat-widget-modal-overlay d-md-none" id="chatWidgetModalOverlay_{{ $site->id }}"></div>

<script>
// 즉시 실행 - 스크립트가 파싱되는 즉시 실행
(function() {
    const siteId = {{ $site->id }};
    const iconId = 'mobileChatIcon_' + siteId;
    
    // 아이콘을 body로 이동하고 스타일 적용
    function ensureIconVisible() {
        let icon = document.getElementById(iconId) || document.querySelector('.mobile-chat-icon-wrapper');
        if (!icon) return false;
        
        // body로 이동
        if (icon.parentElement !== document.body) {
            const iconClone = icon.cloneNode(true);
            iconClone.id = iconId;
            if (!document.body) return false;
            document.body.appendChild(iconClone);
            icon.remove();
            icon = document.getElementById(iconId);
        }
        
        if (!icon) return false;
        
        // 모바일 체크
        const isMobile = window.innerWidth <= 767.98;
        if (!isMobile) {
            icon.style.display = 'none';
            return false;
        }
        
        // 스타일 강제 적용 (cssText 사용)
        icon.style.cssText = 'display: block !important; position: fixed !important; left: 15px !important; z-index: 9999 !important; cursor: pointer !important; width: 56px !important; height: 56px !important; min-width: 56px !important; min-height: 56px !important; margin: 0 !important; padding: 0 !important; border: none !important; box-sizing: border-box !important; overflow: visible !important; visibility: visible !important; opacity: 1 !important;';
        
        const hasMobileMenu = document.querySelector('.mobile-bottom-menu-wrapper');
        icon.style.bottom = hasMobileMenu ? '90px' : '20px';
        
        // 내부 아이콘
        const innerIcon = icon.querySelector('.mobile-chat-icon');
        if (innerIcon) {
            innerIcon.style.cssText = 'width: 56px !important; height: 56px !important; min-width: 56px !important; min-height: 56px !important; background-color: #0d6efd !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; margin: 0 !important; padding: 0 !important; border: none !important; box-sizing: border-box !important;';
            
            const iconElement = innerIcon.querySelector('i');
            if (iconElement) {
                iconElement.style.cssText = 'font-size: 28px !important; color: white !important; display: inline-block !important; line-height: 1 !important; margin: 0 !important; padding: 0 !important;';
            }
        }
        
        // 클릭 이벤트
        if (!icon.hasAttribute('data-listener-attached')) {
            icon.setAttribute('data-listener-attached', 'true');
            
            const handleClick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // PC 채팅 위젯을 모달로 표시
                const widgetId = 'chatWidget_' + siteId;
                const widget = document.getElementById(widgetId);
                const overlay = document.getElementById('chatWidgetModalOverlay_' + siteId);
                
                if (!widget) return;
                
                // 모달 열기
                setupMobileModal(widget, overlay);
            };
            
            // 모바일 모달 설정
            function setupMobileModal(widget, overlay) {
                if (!widget) return;
                
                // 위젯을 body로 이동 (아직 이동하지 않았다면)
                if (widget.parentElement !== document.body) {
                    const widgetClone = widget.cloneNode(true);
                    widgetClone.id = 'chatWidget_' + siteId;
                    document.body.appendChild(widgetClone);
                    widget.remove();
                    widget = document.getElementById('chatWidget_' + siteId);
                    if (!widget) return;
                }
                
                // 모바일 모달 클래스 추가
                widget.classList.add('mobile-modal');
                
                // 위젯 스타일 강제 적용 (모바일 모달로 표시)
                widget.style.setProperty('display', 'flex', 'important');
                widget.style.setProperty('flex-direction', 'column', 'important');
                widget.style.setProperty('position', 'fixed', 'important');
                widget.style.setProperty('bottom', '0', 'important');
                widget.style.setProperty('left', '0', 'important');
                widget.style.setProperty('right', '0', 'important');
                widget.style.setProperty('top', 'auto', 'important');
                widget.style.setProperty('height', '80vh', 'important');
                widget.style.setProperty('max-height', '80vh', 'important');
                widget.style.setProperty('z-index', '10001', 'important');
                widget.style.setProperty('border-radius', '20px 20px 0 0', 'important');
                widget.style.setProperty('margin', '0', 'important');
                widget.style.setProperty('border', 'none', 'important');
                widget.style.setProperty('border-top', '1px solid ' + chatColors.border, 'important');
                widget.style.setProperty('background-color', chatColors.bg, 'important');
                widget.style.setProperty('color', chatColors.text, 'important');
                widget.style.setProperty('visibility', 'visible', 'important');
                widget.style.setProperty('opacity', '1', 'important');
                
                // 오버레이 표시
                if (overlay) {
                    overlay.classList.add('active');
                    overlay.style.cssText = 'display: block !important; position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; background-color: rgba(0, 0, 0, 0.5) !important; z-index: 10000 !important;';
                }
                
                // body 스크롤 방지
                document.body.style.overflow = 'hidden';
                
                // 닫기 버튼 표시 및 이벤트 연결
                const closeBtn = widget.querySelector('#chatWidgetCloseBtn_' + siteId);
                if (closeBtn) {
                    closeBtn.style.display = 'block';
                    closeBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        closeMobileChatModal();
                    };
                }
                
                // 오버레이 클릭 시 닫기
                if (overlay) {
                    overlay.onclick = function(e) {
                        if (e.target === overlay) {
                            closeMobileChatModal();
                        }
                    };
                }
                
                // 애니메이션으로 표시
                widget.style.transform = 'translateY(100%)';
                setTimeout(() => {
                    widget.classList.add('show');
                    widget.style.transform = 'translateY(0)';
                }, 10);
                
                // 메시지 컨테이너 높이 조정
                const messagesContainer = widget.querySelector('#chatMessages_' + siteId);
                if (messagesContainer) {
                    messagesContainer.style.cssText = 'flex: 1 !important; overflow-y: auto !important; padding: 15px !important; background-color: #f8f9fa !important; min-height: 0 !important;';
                }
                
                // 메시지가 없으면 로드
                if (messagesContainer && messagesContainer.children.length === 0) {
                    const loadFunc = window['loadMessages_' + siteId];
                    if (loadFunc && typeof loadFunc === 'function') {
                        loadFunc();
                    }
                }
                
                // 이벤트 리스너 재연결 (cloneNode로 복사하면 이벤트 리스너가 사라짐)
                reconnectEventListeners(widget);
            }
            
            // 이벤트 리스너 재연결 함수
            function reconnectEventListeners(widget) {
                if (!widget) return;
                
                const emojiBtnId = 'emojiBtn_' + siteId;
                const sendBtnId = 'sendBtn_' + siteId;
                const inputId = 'chatInput_' + siteId;
                const emojiPickerId = 'emojiPicker_' + siteId;
                
                // 이모지 버튼 이벤트 재연결
                const emojiBtn = widget.querySelector('#' + emojiBtnId);
                if (emojiBtn) {
                    // 기존 이벤트 리스너 제거
                    const newEmojiBtn = emojiBtn.cloneNode(true);
                    emojiBtn.parentNode.replaceChild(newEmojiBtn, emojiBtn);
                    
                    // 이모지 피커 찾기 또는 생성
                    let emojiPicker = widget.querySelector('#' + emojiPickerId);
                    if (!emojiPicker) {
                        // 이모지 피커가 없으면 생성
                        emojiPicker = document.createElement('div');
                        emojiPicker.id = emojiPickerId;
                        emojiPicker.className = 'emoji-picker';
                        emojiPicker.style.cssText = 'position: absolute; bottom: 50px; left: 10px; background: ' + chatColors.bg + '; border: 1px solid ' + chatColors.border + '; border-radius: 8px; padding: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10002; display: none; max-width: 300px; max-height: 300px; overflow-y: auto;';
                        
                        const emojis = ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻', '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾'];
                        
                        emojis.forEach(emoji => {
                            const emojiBtn = document.createElement('button');
                            emojiBtn.type = 'button';
                            emojiBtn.textContent = emoji;
                            emojiBtn.style.cssText = 'background: none; border: none; font-size: 24px; padding: 5px; cursor: pointer; width: 35px; height: 35px; display: inline-block; text-align: center;';
                            emojiBtn.addEventListener('click', function() {
                                const input = widget.querySelector('#' + inputId);
                                if (input) {
                                    input.value += emoji;
                                    input.focus();
                                    emojiPicker.style.display = 'none';
                                }
                            });
                            emojiPicker.appendChild(emojiBtn);
                        });
                        
                        widget.appendChild(emojiPicker);
                    } else {
                        // 이모지 피커가 이미 존재하는 경우, 각 이모지 버튼의 이벤트 리스너 재연결
                        const emojiButtons = emojiPicker.querySelectorAll('button');
                        emojiButtons.forEach(emojiBtn => {
                            // 기존 이벤트 리스너 제거를 위해 클론
                            const newEmojiBtn = emojiBtn.cloneNode(true);
                            emojiBtn.parentNode.replaceChild(newEmojiBtn, emojiBtn);
                            
                            // 새 이벤트 리스너 연결
                            newEmojiBtn.addEventListener('click', function() {
                                const input = widget.querySelector('#' + inputId);
                                if (input) {
                                    input.value += newEmojiBtn.textContent;
                                    input.focus();
                                    emojiPicker.style.display = 'none';
                                }
                            });
                        });
                    }
                    
                    let emojiPickerVisible = false;
                    newEmojiBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        emojiPickerVisible = !emojiPickerVisible;
                        emojiPicker.style.display = emojiPickerVisible ? 'block' : 'none';
                    });
                }
                
                // 전송 버튼 이벤트 재연결
                const sendBtn = widget.querySelector('#' + sendBtnId);
                if (sendBtn) {
                    // 기존 이벤트 리스너 제거
                    const newSendBtn = sendBtn.cloneNode(true);
                    sendBtn.parentNode.replaceChild(newSendBtn, sendBtn);
                    
                    newSendBtn.addEventListener('click', function() {
                        // PC 버전의 sendMessage 함수 호출
                        const sendFunc = window['sendMessage_' + siteId];
                        if (sendFunc && typeof sendFunc === 'function') {
                            sendFunc();
                        }
                    });
                }
                
                // 입력 필드 Enter 키 이벤트 재연결
                const input = widget.querySelector('#' + inputId);
                if (input) {
                    const newInput = input.cloneNode(true);
                    input.parentNode.replaceChild(newInput, input);
                    
                    newInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            const sendFunc = window['sendMessage_' + siteId];
                            if (sendFunc && typeof sendFunc === 'function') {
                                sendFunc();
                            }
                        }
                    });
                }
                
                // 파일 입력 이벤트 재연결
                const fileInput = widget.querySelector('#chatFileInput_' + siteId);
                if (fileInput) {
                    const newFileInput = fileInput.cloneNode(true);
                    fileInput.parentNode.replaceChild(newFileInput, fileInput);
                    
                    newFileInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file && file.type.startsWith('image/')) {
                            // 전역 selectedFile 변수 설정
                            const setSelectedFile = window['setSelectedFile_' + siteId];
                            if (setSelectedFile) {
                                setSelectedFile(file);
                            }
                            
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const previewImg = widget.querySelector('#chatPreviewImg_' + siteId);
                                const preview = widget.querySelector('#chatPreview_' + siteId);
                                if (previewImg && preview) {
                                    previewImg.src = e.target.result;
                                    preview.style.display = 'block';
                                }
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }
                
                // 미리보기 제거 버튼 이벤트 재연결
                const removePreviewBtn = widget.querySelector('#removePreviewBtn_' + siteId);
                if (removePreviewBtn) {
                    const newRemovePreviewBtn = removePreviewBtn.cloneNode(true);
                    removePreviewBtn.parentNode.replaceChild(newRemovePreviewBtn, removePreviewBtn);
                    
                    newRemovePreviewBtn.addEventListener('click', function() {
                        const preview = widget.querySelector('#chatPreview_' + siteId);
                        const fileInput = widget.querySelector('#chatFileInput_' + siteId);
                        if (preview) preview.style.display = 'none';
                        if (fileInput) fileInput.value = '';
                        // 전역 selectedFile 변수 초기화
                        const setSelectedFile = window['setSelectedFile_' + siteId];
                        if (setSelectedFile) {
                            setSelectedFile(null);
                        }
                    });
                }
                
                // 닫기 버튼 이벤트 재연결
                const closeBtn = widget.querySelector('#chatWidgetCloseBtn_' + siteId);
                if (closeBtn) {
                    closeBtn.style.display = 'block';
                    // 기존 onclick 제거
                    closeBtn.onclick = null;
                    // 새 onclick 설정
                    closeBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const closeFunc = window['closeMobileChatModal_' + siteId];
                        if (closeFunc && typeof closeFunc === 'function') {
                            closeFunc();
                        }
                    };
                }
            }
            
            // 모바일 모달 닫기
            function closeMobileChatModal() {
                const widget = document.getElementById('chatWidget_' + siteId);
                const overlay = document.getElementById('chatWidgetModalOverlay_' + siteId);
                
                if (widget) {
                    // 애니메이션으로 닫기
                    widget.classList.remove('show');
                    widget.style.transform = 'translateY(100%)';
                    
                    setTimeout(() => {
                        widget.classList.remove('mobile-modal');
                        widget.style.transform = '';
                        widget.style.display = '';
                        widget.style.position = '';
                        widget.style.bottom = '';
                        widget.style.left = '';
                        widget.style.right = '';
                        widget.style.top = '';
                        widget.style.height = '';
                        widget.style.maxHeight = '';
                        widget.style.zIndex = '';
                        widget.style.borderRadius = '';
                        widget.style.margin = '';
                        widget.style.border = '';
                        widget.style.borderTop = '';
                        widget.style.backgroundColor = '';
                        widget.style.visibility = '';
                        widget.style.opacity = '';
                        
                        if (overlay) {
                            overlay.classList.remove('active');
                            overlay.style.display = '';
                        }
                        document.body.style.overflow = '';
                    }, 300);
                }
            }
            
            // 전역으로 노출
            window['openMobileChatModal_' + siteId] = function() {
                const icon = document.getElementById(iconId) || document.querySelector('.mobile-chat-icon-wrapper');
                if (icon) {
                    icon.click();
                }
            };
            window['closeMobileChatModal_' + siteId] = closeMobileChatModal;
            
            icon.addEventListener('click', handleClick);
            
            // 내부 아이콘에도 클릭 이벤트 추가
            const innerIcon = icon.querySelector('.mobile-chat-icon');
            if (innerIcon) {
                innerIcon.addEventListener('click', handleClick);
            }
        }
        
        return true;
    }
    
    // 실행 함수
    function run() {
        if (document.body) {
            ensureIconVisible();
        }
    }
    
    // 즉시 실행
    run();
    
    // DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    }
    
    // load
    window.addEventListener('load', function() {
        setTimeout(run, 50);
    });
    
    // 주기적 확인
    let attempts = 0;
    const interval = setInterval(function() {
        attempts++;
        if (run() || attempts >= 100) {
            clearInterval(interval);
        }
    }, 50);
    
    // MutationObserver
    if (typeof MutationObserver !== 'undefined' && document.body) {
        const observer = new MutationObserver(run);
        observer.observe(document.body, { childList: true, subtree: true });
    }
    
    // 리사이즈
    window.addEventListener('resize', run);
})();
</script>

@push('scripts')
<script>

// 기존 코드 계속
(function() {
    // 모바일 채팅 아이콘 초기화 및 표시
    function initMobileChatIcon() {
        const siteId = {{ $site->id }};
        const iconId = 'mobileChatIcon_' + siteId;
        const modalId = 'mobileChatModal_' + siteId;
        let icon = document.getElementById(iconId);
        
        if (!icon) return;
        
        // 아이콘이 body에 없으면 body로 이동
        if (icon.parentElement !== document.body) {
            const iconClone = icon.cloneNode(true);
            iconClone.id = iconId;
            document.body.appendChild(iconClone);
            icon.remove();
            icon = document.getElementById(iconId);
        }
        
        // 모바일에서만 표시
        function updateIconVisibility() {
            const isMobile = window.innerWidth <= 767.98;
            if (isMobile) {
                // 아이콘 스타일 명시적으로 설정
                icon.style.cssText = 'display: block !important; position: fixed !important; left: 15px !important; z-index: 9999 !important; cursor: pointer !important; width: 56px !important; height: 56px !important; min-width: 56px !important; min-height: 56px !important; margin: 0 !important; padding: 0 !important; border: none !important; box-sizing: border-box !important; overflow: visible !important;';
                
                // 모바일 하단 메뉴가 있는지 확인하여 위치 조정
                const hasMobileMenu = document.querySelector('.mobile-bottom-menu-wrapper');
                if (hasMobileMenu) {
                    icon.style.bottom = '90px';
                } else {
                    icon.style.bottom = '20px';
                }
                
                // 내부 아이콘 스타일도 명시적으로 설정
                const innerIcon = icon.querySelector('.mobile-chat-icon');
                if (innerIcon) {
                    innerIcon.style.cssText = 'width: 56px !important; height: 56px !important; min-width: 56px !important; min-height: 56px !important; background-color: #0d6efd !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; margin: 0 !important; padding: 0 !important; border: none !important; box-sizing: border-box !important;';
                    
                    const iconElement = innerIcon.querySelector('i');
                    if (iconElement) {
                        iconElement.style.cssText = 'font-size: 28px !important; color: white !important; display: inline-block !important; line-height: 1 !important; margin: 0 !important; padding: 0 !important;';
                    }
                }
            } else {
                icon.style.display = 'none';
            }
        }
        
        // 모달 열기 함수
        function openModal() {
            const openFunc = window['openMobileChatModal_' + siteId];
            if (openFunc && typeof openFunc === 'function') {
                openFunc();
            } else {
                // 함수가 아직 준비되지 않았으면 직접 모달 열기
                const modal = document.getElementById(modalId);
                if (!modal) return;
                const modalContent = modal.querySelector('.mobile-chat-modal-content');
                if (!modalContent) return;
                
                modal.style.display = 'block';
                setTimeout(() => {
                    modalContent.style.transform = 'translateY(0)';
                }, 10);
            }
        }
        
        // 초기 설정
        updateIconVisibility();
        
        // 리사이즈 시 업데이트
        window.addEventListener('resize', updateIconVisibility);
        
        // 아이콘 클릭 이벤트
        if (!icon.hasAttribute('data-listener-attached')) {
            icon.setAttribute('data-listener-attached', 'true');
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openModal();
            });
        }
    }
    
    // 아이콘을 body로 이동하고 스타일 적용하는 함수
    function moveIconToBodyAndStyle() {
        const siteId = {{ $site->id }};
        const iconId = 'mobileChatIcon_' + siteId;
        let icon = document.getElementById(iconId);
        
        if (!icon) return false;
        
        // 이미 body에 있고 스타일이 적용되어 있으면 성공
        if (icon.parentElement === document.body && icon.style.position === 'fixed') {
            return true;
        }
        
        // body로 이동
        if (icon.parentElement !== document.body) {
            const iconClone = icon.cloneNode(true);
            iconClone.id = iconId;
            document.body.appendChild(iconClone);
            icon.remove();
            icon = document.getElementById(iconId);
        }
        
        if (!icon) return false;
        
        // 스타일 명시적으로 적용
        const isMobile = window.innerWidth <= 767.98;
        if (isMobile) {
            icon.style.cssText = 'display: block !important; position: fixed !important; left: 15px !important; z-index: 9999 !important; cursor: pointer !important; width: 56px !important; height: 56px !important; min-width: 56px !important; min-height: 56px !important; margin: 0 !important; padding: 0 !important; border: none !important; box-sizing: border-box !important; overflow: visible !important;';
            
            const hasMobileMenu = document.querySelector('.mobile-bottom-menu-wrapper');
            icon.style.bottom = hasMobileMenu ? '90px' : '20px';
            
            const innerIcon = icon.querySelector('.mobile-chat-icon');
            if (innerIcon) {
                innerIcon.style.cssText = 'width: 56px !important; height: 56px !important; min-width: 56px !important; min-height: 56px !important; background-color: #0d6efd !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; margin: 0 !important; padding: 0 !important; border: none !important; box-sizing: border-box !important;';
                
                const iconElement = innerIcon.querySelector('i');
                if (iconElement) {
                    iconElement.style.cssText = 'font-size: 28px !important; color: white !important; display: inline-block !important; line-height: 1 !important; margin: 0 !important; padding: 0 !important;';
                }
            }
        } else {
            icon.style.display = 'none';
        }
        
        return true;
    }
    
    // 아이콘 초기화 함수 (여러 번 시도)
    function initializeIcon() {
        const siteId = {{ $site->id }};
        let initialized = false;
        let attempts = 0;
        const maxAttempts = 50; // 더 많은 시도
        
        const tryInit = function() {
            attempts++;
            const iconId = 'mobileChatIcon_' + siteId;
            let icon = document.getElementById(iconId);
            
            if (icon) {
                // body로 이동 및 스타일 적용
                if (moveIconToBodyAndStyle()) {
                    initialized = true;
                    // 클릭 이벤트 추가
                    const finalIcon = document.getElementById(iconId);
                    if (finalIcon && !finalIcon.hasAttribute('data-listener-attached')) {
                        finalIcon.setAttribute('data-listener-attached', 'true');
                        finalIcon.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const modalId = 'mobileChatModal_' + siteId;
                            const modal = document.getElementById(modalId);
                            if (modal) {
                                const modalContent = modal.querySelector('.mobile-chat-modal-content');
                                if (modalContent) {
                                    modal.style.display = 'block';
                                    setTimeout(() => {
                                        modalContent.style.transform = 'translateY(0)';
                                    }, 10);
                                }
                            }
                        });
                    }
                    return true;
                }
            }
            
            if (attempts >= maxAttempts) {
                return false;
            }
            return null; // 계속 시도
        };
        
        // 즉시 시도
        const result = tryInit();
        if (result === true) {
            return;
        }
        
        // 주기적으로 시도
        const interval = setInterval(function() {
            const result = tryInit();
            if (result === true || result === false) {
                clearInterval(interval);
            }
        }, 100);
        
        // 최대 5초 후에도 안 되면 강제로 시도
        setTimeout(function() {
            clearInterval(interval);
            if (!initialized) {
                moveIconToBodyAndStyle();
            }
        }, 5000);
    }
    
    // DOMContentLoaded 대기 및 초기화
    function initialize() {
        initializeIcon();
        initMobileChat();
    }
    
    // 즉시 실행
    initialize();
    
    // DOMContentLoaded에서도 실행
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    }
    
    // window.load에서도 실행
    window.addEventListener('load', function() {
        setTimeout(initialize, 100);
    });
    
    // MutationObserver로 DOM 변경 감지
    const observer = new MutationObserver(function(mutations) {
        const siteId = {{ $site->id }};
        const iconId = 'mobileChatIcon_' + siteId;
        const icon = document.getElementById(iconId);
        
        if (icon && icon.parentElement !== document.body) {
            moveIconToBodyAndStyle();
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    function initMobileChat() {
        const siteId = {{ $site->id }};
        const mobileIconId = 'mobileChatIcon_' + siteId;
        const mobileModalId = 'mobileChatModal_' + siteId;
        const mobileCloseBtnId = 'mobileChatCloseBtn_' + siteId;
        const mobileMessagesId = 'mobileChatMessages_' + siteId;
        const mobileInputId = 'mobileChatInput_' + siteId;
        const mobileSendBtnId = 'mobileSendBtn_' + siteId;
        const mobileFileInputId = 'mobileChatFileInput_' + siteId;
        const mobilePreviewId = 'mobileChatPreview_' + siteId;
        const mobilePreviewImgId = 'mobileChatPreviewImg_' + siteId;
        const mobileRemovePreviewBtnId = 'mobileRemovePreviewBtn_' + siteId;
        const mobileEmojiBtnId = 'mobileEmojiBtn_' + siteId;
        
        const getMessagesUrl = '{{ $getMessagesUrl }}';
        const sendMessageUrl = '{{ $sendMessageUrl }}';
        const reportUrl = '{{ $reportUrl }}';
        const blockUrl = '{{ $blockUrl }}';
        const csrfToken = '{{ $csrfToken }}';
        const nickname = '{{ $nickname }}';
        const isGuest = {{ $isGuest ? 'true' : 'false' }};
        const isAdmin = {{ auth()->check() && auth()->user()->canManage() ? 'true' : 'false' }};
        const allowGuestChat = {{ $allowGuestChat ? 'true' : 'false' }};
        
        let mobileSelectedFile = null;
        let mobilePollInterval = null;
        let isMobileModalOpen = false;
        let messagesLoaded = false;
    
    // 모바일 채팅 모달 닫기 (먼저 정의)
    function closeMobileChatModal() {
        const modal = document.getElementById(mobileModalId);
        if (!modal) return;
        const modalContent = modal.querySelector('.mobile-chat-modal-content');
        if (!modalContent) return;
        
        // 애니메이션으로 내려가기
        modalContent.style.transform = 'translateY(100%)';
        setTimeout(() => {
            modal.style.display = 'none';
            // body 스크롤 복원
            document.body.style.overflow = '';
        }, 300);
        
        isMobileModalOpen = false;
        
        // 폴링 중지
        if (mobilePollInterval) {
            clearInterval(mobilePollInterval);
            mobilePollInterval = null;
        }
    }
    
    // 전역으로 노출
    window['closeMobileChatModal_' + siteId] = closeMobileChatModal;
    
    // 모바일 채팅 모달 열기
    function openMobileChatModal() {
        let modal = document.getElementById(mobileModalId);
        if (!modal) {
            console.error('Mobile chat modal not found:', mobileModalId);
            return;
        }
        
        // 모달이 body에 없으면 body로 이동
        if (modal.parentElement !== document.body) {
            const modalClone = modal.cloneNode(true);
            modalClone.id = mobileModalId;
            document.body.appendChild(modalClone);
            modal.remove();
            modal = document.getElementById(mobileModalId);
            
            // 모달 이동 후 모든 data-listener-attached 속성 제거 (이벤트 리스너가 복사되지 않았으므로)
            if (modal) {
                const allElements = modal.querySelectorAll('[data-listener-attached]');
                allElements.forEach(el => el.removeAttribute('data-listener-attached'));
            }
        }
        
        if (!modal) {
            console.error('Failed to move modal to body');
            return;
        }
        
        const modalContent = modal.querySelector('.mobile-chat-modal-content');
        if (!modalContent) {
            console.error('Mobile chat modal content not found');
            return;
        }
        
        // 모달 스타일 강제 적용
        modal.style.cssText = 'display: block !important; position: fixed !important; bottom: 0 !important; left: 0 !important; right: 0 !important; top: 0 !important; z-index: 10000 !important; background-color: rgba(0,0,0,0.5) !important;';
        
        // body 스크롤 방지
        document.body.style.overflow = 'hidden';
        
        // 모달 컨텐츠 초기 위치 설정
        modalContent.style.transform = 'translateY(100%)';
        
        // 애니메이션으로 올라오기
        setTimeout(() => {
            modalContent.style.transform = 'translateY(0)';
        }, 10);
        
        // 모달이 body에 있으면 이벤트 리스너 재연결 (항상 호출)
        // 모달이 완전히 렌더링된 후 이벤트 리스너 연결
        // 즉시 호출 + 지연 호출로 여러 시도
        reconnectMobileEventListeners();
        setTimeout(() => {
            reconnectMobileEventListeners();
        }, 100);
        setTimeout(() => {
            reconnectMobileEventListeners();
        }, 300);
        
        isMobileModalOpen = true;
        
        // 메시지가 아직 로드되지 않았으면 로드
        if (!messagesLoaded) {
            loadMobileMessages();
            messagesLoaded = true;
        }
        
        // 폴링 시작
        if (mobilePollInterval) {
            clearInterval(mobilePollInterval);
        }
        mobilePollInterval = setInterval(loadMobileMessages, 3000);
    }
    
    // 전역으로 노출
    window['openMobileChatModal_' + siteId] = openMobileChatModal;
    
    // 모바일 이벤트 리스너 재연결 함수
    function reconnectMobileEventListeners() {
        // 닫기 버튼 - closeMobileChatModal 함수 직접 호출
        const closeBtn = document.getElementById(mobileCloseBtnId);
        if (closeBtn) {
            // 기존 onclick 제거
            closeBtn.onclick = null;
            // 새 onclick 설정 - closeMobileChatModal 함수 직접 호출 (이미 정의됨)
            closeBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeMobileChatModal();
            };
        }
        
        // 모달 배경 클릭 시 닫기
        const modal = document.getElementById(mobileModalId);
        if (modal && !modal.hasAttribute('data-listener-attached')) {
            modal.setAttribute('data-listener-attached', 'true');
            modal.addEventListener('click', function(e) {
                if (e.target.id === mobileModalId) {
                    const modalContent = modal.querySelector('.mobile-chat-modal-content');
                    if (!modalContent) return;
                    
                    modalContent.style.transform = 'translateY(100%)';
                    setTimeout(() => {
                        modal.style.display = 'none';
                        document.body.style.overflow = '';
                        isMobileModalOpen = false;
                    }, 300);
                }
            });
        }
        
        // 전송 버튼
        const sendBtn = document.getElementById(mobileSendBtnId);
        if (sendBtn) {
            sendBtn.onclick = null;
            sendBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof sendMobileMessage === 'function') {
                    sendMobileMessage();
                }
            };
        }
        
        // Enter 키
        const input = document.getElementById(mobileInputId);
        if (input) {
            if (input.hasAttribute('data-listener-attached')) {
                input.removeAttribute('data-listener-attached');
            }
            input.setAttribute('data-listener-attached', 'true');
            input.onkeypress = function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMobileMessage();
                }
            };
        }
        
        // 파일 입력
        const fileInput = document.getElementById(mobileFileInputId);
        if (fileInput) {
            if (fileInput.hasAttribute('data-listener-attached')) {
                fileInput.removeAttribute('data-listener-attached');
            }
            fileInput.setAttribute('data-listener-attached', 'true');
            fileInput.onchange = function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    mobileSelectedFile = file;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewImg = document.getElementById(mobilePreviewImgId);
                        const preview = document.getElementById(mobilePreviewId);
                        if (previewImg) previewImg.src = e.target.result;
                        if (preview) preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            };
        }
        
        // 미리보기 제거 버튼
        const removePreviewBtn = document.getElementById(mobileRemovePreviewBtnId);
        if (removePreviewBtn) {
            if (removePreviewBtn.hasAttribute('data-listener-attached')) {
                removePreviewBtn.removeAttribute('data-listener-attached');
            }
            removePreviewBtn.setAttribute('data-listener-attached', 'true');
            removePreviewBtn.onclick = function() {
                mobileSelectedFile = null;
                const preview = document.getElementById(mobilePreviewId);
                const fileInput = document.getElementById(mobileFileInputId);
                if (preview) preview.style.display = 'none';
                if (fileInput) fileInput.value = '';
            };
        }
        
        // 이모지 버튼 및 피커
        setupMobileEmojiPicker();
    }
    
    // 모바일 이모지 피커 설정
    let mobileEmojiPickerVisible = false;
    let mobileEmojiPicker = null;
    
    function setupMobileEmojiPicker() {
        const modalContent = document.getElementById(mobileModalId)?.querySelector('.mobile-chat-modal-content');
        if (!modalContent) return;
        
        // 기존 이모지 피커 제거
        const existingPicker = document.getElementById('mobileEmojiPicker_' + siteId);
        if (existingPicker) {
            existingPicker.remove();
        }
        
        mobileEmojiPickerVisible = false;
        mobileEmojiPicker = document.createElement('div');
        mobileEmojiPicker.id = 'mobileEmojiPicker_' + siteId;
        mobileEmojiPicker.className = 'emoji-picker';
        mobileEmojiPicker.style.cssText = 'position: absolute; bottom: 60px; left: 10px; right: 10px; background: ' + chatColors.bg + '; border: 1px solid ' + chatColors.border + '; border-radius: 8px; padding: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10001; display: none; max-width: 100%; max-height: 300px; overflow-y: auto;';
        
        const emojis = ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻', '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾'];
        
        emojis.forEach(emoji => {
            const emojiBtn = document.createElement('button');
            emojiBtn.type = 'button';
            emojiBtn.textContent = emoji;
            emojiBtn.style.cssText = 'background: none; border: none; font-size: 24px; padding: 5px; cursor: pointer; width: 35px; height: 35px; display: inline-block; text-align: center;';
            emojiBtn.addEventListener('click', function() {
                const input = document.getElementById(mobileInputId);
                if (input) {
                    input.value += emoji;
                    input.focus();
                }
                if (mobileEmojiPicker) {
                    mobileEmojiPicker.style.display = 'none';
                }
                mobileEmojiPickerVisible = false;
            });
            mobileEmojiPicker.appendChild(emojiBtn);
        });
        
        modalContent.appendChild(mobileEmojiPicker);
        
        // 이모지 버튼
        const mobileEmojiBtn = document.getElementById(mobileEmojiBtnId);
        if (mobileEmojiBtn) {
            mobileEmojiBtn.onclick = null;
            mobileEmojiBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleMobileEmojiPicker();
            };
        }
        
        // 이모지 피커 외부 클릭 시 닫기
        document.addEventListener('click', function(e) {
            const emojiBtn = document.getElementById(mobileEmojiBtnId);
            if (mobileEmojiPicker && !mobileEmojiPicker.contains(e.target) && e.target !== emojiBtn && !emojiBtn?.contains(e.target)) {
                mobileEmojiPicker.style.display = 'none';
                mobileEmojiPickerVisible = false;
            }
        });
    }
    
    // 이모지 피커 토글 함수
    function toggleMobileEmojiPicker() {
        if (!mobileEmojiPicker) {
            setupMobileEmojiPicker();
        }
        if (mobileEmojiPicker) {
            mobileEmojiPickerVisible = !mobileEmojiPickerVisible;
            mobileEmojiPicker.style.display = mobileEmojiPickerVisible ? 'block' : 'none';
        }
    }
    
    // 전역으로 노출
    window['toggleMobileEmojiPicker_' + siteId] = toggleMobileEmojiPicker;
    
    // 모바일 채팅 모달 닫기
    function closeMobileChatModal() {
        const modal = document.getElementById(mobileModalId);
        if (!modal) return;
        const modalContent = modal.querySelector('.mobile-chat-modal-content');
        if (!modalContent) return;
        
        // 애니메이션으로 내려가기
        modalContent.style.transform = 'translateY(100%)';
        setTimeout(() => {
            modal.style.display = 'none';
            // body 스크롤 복원
            document.body.style.overflow = '';
        }, 300);
        
        isMobileModalOpen = false;
        
        // 폴링 중지
        if (mobilePollInterval) {
            clearInterval(mobilePollInterval);
            mobilePollInterval = null;
        }
    }
    
    // 초기 이벤트 리스너는 모달이 body로 이동한 후 reconnectMobileEventListeners에서 연결됨
    
    // 모바일 메시지 로드
    function loadMobileMessages() {
        fetch(getMessagesUrl, {
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading messages:', data.error);
                return;
            }
            
            const messagesContainer = document.getElementById(mobileMessagesId);
            if (!messagesContainer) return;
            messagesContainer.innerHTML = '';
            
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    appendMobileMessage(msg);
                });
            }
            
            scrollMobileToBottom();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // 전역으로 노출
    window['loadMobileMessages_' + siteId] = loadMobileMessages;
    
    // 모바일 메시지 추가
    function appendMobileMessage(msg) {
        const messagesContainer = document.getElementById(mobileMessagesId);
        if (!messagesContainer) return;
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message';
        messageDiv.dataset.messageId = msg.id;
        
        const time = new Date(msg.created_at).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' });
        
        messageDiv.innerHTML = `
            <div class="chat-message-header">
                <span class="chat-message-nickname" data-user-id="${msg.user_id || ''}" data-guest-session-id="${msg.guest_session_id || ''}" data-nickname="${msg.nickname}" data-message-id="${msg.id}">
                    ${msg.nickname}
                </span>
                <span class="chat-message-time">${time}</span>
            </div>
            <div class="chat-message-content">
                ${escapeHtml(msg.message || msg.content || '')}
            </div>
            ${(msg.attachment_path || msg.file_path) ? `<img src="/storage/${msg.attachment_path || msg.file_path}" class="chat-message-image" alt="Attachment">` : ''}
        `;
        
        messagesContainer.appendChild(messageDiv);
        
        // 닉네임 클릭 핸들러
        const nicknameEl = messageDiv.querySelector('.chat-message-nickname');
        if (nicknameEl) {
            nicknameEl.addEventListener('click', function(e) {
                e.stopPropagation();
                showMobileUserMenu(this, e);
            });
        }
    }
    
    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // 모바일 스크롤 하단으로
    function scrollMobileToBottom() {
        const messagesContainer = document.getElementById(mobileMessagesId);
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
    
    // 모바일 메시지 전송
    function sendMobileMessage() {
        // 게스트 채팅 허용 체크
        if (isGuest && !allowGuestChat) {
            alert('비로그인 사용자는 채팅을 사용할 수 없습니다.');
            return;
        }
        
        const input = document.getElementById(mobileInputId);
        if (!input) return;
        const message = input.value.trim();
        
        if (!message && !mobileSelectedFile) {
            return;
        }
        
        const formData = new FormData();
        formData.append('message', message);
        if (mobileSelectedFile) {
            formData.append('attachment', mobileSelectedFile);
        }
        formData.append('_token', csrfToken);
        
        fetch(sendMessageUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                if (data.error === '금지 단어가 포함되었습니다.') {
                    alert('금지 단어가 포함되었습니다.');
                } else {
                    alert(data.error);
                }
                return;
            }
            
            if (data.success) {
                input.value = '';
                mobileSelectedFile = null;
                const preview = document.getElementById(mobilePreviewId);
                if (preview) preview.style.display = 'none';
                loadMobileMessages();
                // PC 위젯이 보이면 PC 메시지도 업데이트
                const pcWidget = document.getElementById('chatWidget_' + siteId);
                if (pcWidget) {
                    const loadPcFunc = window['loadMessages_' + siteId];
                    if (loadPcFunc && typeof loadPcFunc === 'function') {
                        loadPcFunc();
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('메시지 전송에 실패했습니다.');
        });
    }
    
    // 전역으로 노출
    window['sendMobileMessage_' + siteId] = sendMobileMessage;
    
    // Show user menu (모바일용)
    function showMobileUserMenu(nicknameEl, event) {
        const userId = nicknameEl.dataset.userId;
        const guestSessionId = nicknameEl.dataset.guestSessionId;
        const targetNickname = nicknameEl.dataset.nickname;
        const messageId = nicknameEl.dataset.messageId;
        
        const existingMenu = document.querySelector('.chat-user-menu');
        if (existingMenu) {
            existingMenu.remove();
        }
        
        const menu = document.createElement('div');
        menu.className = 'chat-user-menu';
        menu.style.display = 'block';
        menu.style.left = event.pageX + 'px';
        menu.style.top = event.pageY + 'px';
        
        const menuItems = [];
        
        if (!isAdmin) {
            menuItems.push({ text: '신고하기', action: () => reportMobileUser(userId, guestSessionId, targetNickname, messageId) });
            menuItems.push({ text: '차단하기', action: () => blockMobileUser(userId, guestSessionId, targetNickname) });
        } else {
            menuItems.push({ text: '신고하기', action: () => reportMobileUser(userId, guestSessionId, targetNickname, messageId) });
            menuItems.push({ text: '차단하기', action: () => blockMobileUser(userId, guestSessionId, targetNickname) });
            menuItems.push({ text: '채팅금지', action: () => banMobileUserChat(userId, guestSessionId, targetNickname) });
        }
        
        menuItems.push({ text: '쪽지보내기', action: () => sendMessageToMobileUser(userId, guestSessionId, targetNickname) });
        
        menuItems.forEach(item => {
            const menuItem = document.createElement('div');
            menuItem.className = 'chat-user-menu-item';
            menuItem.textContent = item.text;
            menuItem.addEventListener('click', item.action);
            menu.appendChild(menuItem);
        });
        
        document.body.appendChild(menu);
        
        setTimeout(() => {
            document.addEventListener('click', function closeMenu() {
                menu.remove();
                document.removeEventListener('click', closeMenu);
            });
        }, 0);
    }
    
    // Report user (모바일용)
    function reportMobileUser(userId, guestSessionId, targetNickname, messageId) {
        // 신고 사유 입력 모달 생성
        const modal = document.createElement('div');
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">신고하기</h5>
                        <button type="button" class="btn-close" onclick="this.closest('.modal').remove()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reportReasonMobile" class="form-label">신고 사유를 작성해주세요</label>
                            <textarea class="form-control" id="reportReasonMobile" rows="4" placeholder="신고 사유를 입력하세요..." maxlength="500"></textarea>
                            <small class="text-muted">최대 500자까지 입력 가능합니다.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">취소</button>
                        <button type="button" class="btn btn-primary" id="submitReportMobile">신고하기</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // 신고 제출 버튼 클릭 이벤트
        modal.querySelector('#submitReportMobile').addEventListener('click', function() {
            const reason = modal.querySelector('#reportReasonMobile').value.trim();
            
            if (!reason) {
                alert('신고 사유를 입력해주세요.');
                return;
            }
            
            fetch(reportUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message_id: messageId,
                    reason: reason,
                })
            })
            .then(response => response.json())
            .then(data => {
                modal.remove();
                if (data.success) {
                    alert('신고가 접수되었습니다.');
                } else {
                    alert(data.error || '신고 접수에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modal.remove();
                alert('신고 접수에 실패했습니다.');
            });
        });
        
        // 모달 외부 클릭 시 닫기
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
    
    // Block user (모바일용)
    function blockMobileUser(userId, guestSessionId, targetNickname) {
        if (!confirm(`${targetNickname}님을 차단하시겠습니까?`)) return;
        
        fetch(blockUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                user_id: userId || null,
                guest_session_id: guestSessionId || null,
                nickname: targetNickname,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('사용자가 차단되었습니다.');
            } else {
                alert(data.error || '차단에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('차단에 실패했습니다.');
        });
    }
    
    // Ban user chat (모바일용)
    function banMobileUserChat(userId, guestSessionId, targetNickname) {
        if (!isAdmin) return;
        
        const reason = prompt('채팅 금지 사유를 입력하세요 (선택사항):');
        if (reason === null) return;
        
        alert('관리자 패널에서 채팅 금지를 설정해주세요.');
    }
    
    // Send message to user (모바일용)
    function sendMessageToMobileUser(userId, guestSessionId, targetNickname) {
        alert('쪽지 기능은 별도로 구현되어 있습니다.');
    }
    
    // 모바일 이벤트 리스너는 reconnectMobileEventListeners에서 연결됨
    // 모달이 body로 이동하기 전에는 이벤트 리스너를 연결하지 않음
    
    // 모바일 이모지 피커는 모달이 body로 이동한 후 reconnectMobileEventListeners에서 설정됨
    
    // 페이지 언로드 시 정리
    window.addEventListener('beforeunload', function() {
        if (mobilePollInterval) {
            clearInterval(mobilePollInterval);
        }
    });
    
    // 모든 함수를 전역으로 노출 (IIFE 종료 직전)
    try {
        window['toggleMobileEmojiPicker_' + siteId] = toggleMobileEmojiPicker;
        window['sendMobileMessage_' + siteId] = sendMobileMessage;
        window['closeMobileChatModal_' + siteId] = closeMobileChatModal;
        window['openMobileChatModal_' + siteId] = openMobileChatModal;
        window['loadMobileMessages_' + siteId] = loadMobileMessages;
    } catch (e) {
        console.error('Error exposing mobile chat functions:', e);
    }
    
})();
</script>
@endpush
@endif

