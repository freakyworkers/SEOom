@extends('layouts.admin')

@section('title', '메일 설정')
@section('page-title', '메일 설정')
@section('page-subtitle', '이메일 발송 및 알림 설정을 관리합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>메일 설정</h5>
            </div>
            <div class="card-body">
                <form id="mailSettingsForm" method="POST" action="{{ route('admin.mail-settings.update', ['site' => $site->slug]) }}">
                    @csrf
                    
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-gear me-2"></i>SMTP 설정</h6>
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>참고:</strong> Gmail 사용 시 앱 비밀번호가 필요합니다. 
                            <a href="https://myaccount.google.com/apppasswords" target="_blank" class="alert-link">앱 비밀번호 생성하기</a>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="mail_mailer" class="form-label fw-bold">메일 드라이버 <span class="text-danger">*</span></label>
                                        <select class="form-select" id="mail_mailer" name="mail_mailer" required>
                                            <option value="smtp" {{ ($settings['mail_mailer'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                            <option value="sendmail" {{ ($settings['mail_mailer'] ?? 'smtp') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mail_host" class="form-label fw-bold">SMTP 호스트 <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="mail_host" 
                                               name="mail_host" 
                                               value="{{ $settings['mail_host'] ?? 'smtp.gmail.com' }}" 
                                               placeholder="smtp.gmail.com"
                                               required>
                                        <small class="text-muted">Gmail: smtp.gmail.com, 네이버: smtp.naver.com</small>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="mail_port" class="form-label fw-bold">SMTP 포트 <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="mail_port" 
                                               name="mail_port" 
                                               value="{{ $settings['mail_port'] ?? '587' }}" 
                                               placeholder="587"
                                               required>
                                        <small class="text-muted">TLS: 587, SSL: 465</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mail_encryption" class="form-label fw-bold">암호화 방식 <span class="text-danger">*</span></label>
                                        <select class="form-select" id="mail_encryption" name="mail_encryption" required>
                                            <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ ($settings['mail_encryption'] ?? 'tls') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="mail_username" class="form-label fw-bold">이메일 주소 (발신자 이메일) <span class="text-danger">*</span></label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="mail_username" 
                                               name="mail_username" 
                                               value="{{ $settings['mail_username'] ?? '' }}" 
                                               placeholder="your-email@gmail.com"
                                               required>
                                        <small class="text-muted">이 이메일 주소가 발신자 이메일로 사용됩니다.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mail_password" class="form-label fw-bold">비밀번호/앱 비밀번호 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="mail_password" 
                                                   name="mail_password" 
                                                   value="{{ $settings['mail_password'] ?? '' }}" 
                                                   placeholder="앱 비밀번호 또는 비밀번호"
                                                   required>
                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('mail_password')">
                                                <i class="bi bi-eye" id="mail_password_icon"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Gmail은 앱 비밀번호가 필요합니다.</small>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="mail_from_name" class="form-label fw-bold">발신자 이름</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="mail_from_name" 
                                               name="mail_from_name" 
                                               value="{{ $settings['mail_from_name'] ?? $site->name }}" 
                                               placeholder="{{ $site->name }}">
                                        <small class="text-muted">메일 발송 시 표시될 발신자 이름입니다.</small>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary" id="testMailBtn">
                                        <i class="bi bi-send me-2"></i>테스트 메일 발송
                                    </button>
                                    <small class="text-muted ms-3">설정한 이메일 주소로 테스트 메일을 발송합니다.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-bell me-2"></i>알림 설정</h6>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="admin_notification_email" class="form-label fw-bold">운영자 알림 이메일</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="admin_notification_email" 
                                           name="admin_notification_email" 
                                           value="{{ $settings['admin_notification_email'] ?? '' }}" 
                                           placeholder="admin@example.com">
                                    <small class="text-muted">사이트 관련 알림을 받을 이메일 주소를 입력하세요.</small>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="notify_new_user" 
                                           name="notify_new_user" 
                                           value="1"
                                           {{ ($settings['notify_new_user'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notify_new_user">
                                        새 회원가입 시 알림 받기
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="notify_new_post" 
                                           name="notify_new_post" 
                                           value="1"
                                           {{ ($settings['notify_new_post'] ?? false) ? 'checked' : '' }}
                                           onchange="toggleBoardSelection('post')">
                                    <label class="form-check-label" for="notify_new_post">
                                        새 게시글 작성 시 알림 받기
                                    </label>
                                </div>
                                
                                {{-- 새 게시글 알림 게시판 선택 --}}
                                <div id="postBoardSelection" class="mb-3 ms-4" style="display: {{ ($settings['notify_new_post'] ?? false) ? 'block' : 'none' }};">
                                    <label class="form-label fw-bold mb-2">새 게시글 알림 게시판 선택</label>
                                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                        @forelse($boards ?? [] as $board)
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="notify_post_board_{{ $board->id }}" 
                                                       name="notify_post_boards[]" 
                                                       value="{{ $board->id }}"
                                                       {{ in_array($board->id, $settings['notify_post_boards'] ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notify_post_board_{{ $board->id }}">
                                                    {{ $board->name }}
                                                </label>
                                            </div>
                                        @empty
                                            <p class="text-muted mb-0">생성된 게시판이 없습니다.</p>
                                        @endforelse
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="notify_new_comment" 
                                           name="notify_new_comment" 
                                           value="1"
                                           {{ ($settings['notify_new_comment'] ?? false) ? 'checked' : '' }}
                                           onchange="toggleBoardSelection('comment')">
                                    <label class="form-check-label" for="notify_new_comment">
                                        새 댓글 작성 시 알림 받기
                                    </label>
                                </div>
                                
                                {{-- 새 댓글 알림 게시판 선택 --}}
                                <div id="commentBoardSelection" class="mb-3 ms-4" style="display: {{ ($settings['notify_new_comment'] ?? false) ? 'block' : 'none' }};">
                                    <label class="form-label fw-bold mb-2">새 댓글 알림 게시판 선택</label>
                                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                        @forelse($boards ?? [] as $board)
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="notify_comment_board_{{ $board->id }}" 
                                                       name="notify_comment_boards[]" 
                                                       value="{{ $board->id }}"
                                                       {{ in_array($board->id, $settings['notify_comment_boards'] ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notify_comment_board_{{ $board->id }}">
                                                    {{ $board->name }}
                                                </label>
                                            </div>
                                        @empty
                                            <p class="text-muted mb-0">생성된 게시판이 없습니다.</p>
                                        @endforelse
                                    </div>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="notify_new_message" 
                                           name="notify_new_message" 
                                           value="1"
                                           {{ ($settings['notify_new_message'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notify_new_message">
                                        새 쪽지 수신 시 알림 받기
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 테스트 메일 발송 모달 -->
<div class="modal fade" id="testMailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">테스트 메일 발송</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="test_email" class="form-label">테스트 이메일 주소</label>
                    <input type="email" class="form-control" id="test_email" placeholder="test@example.com" value="{{ $settings['admin_notification_email'] ?? '' }}">
                </div>
                <div id="testMailStatus"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" id="sendTestMailBtn">발송</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function toggleBoardSelection(type) {
    const checkbox = document.getElementById('notify_new_' + type);
    const boardSelection = document.getElementById(type + 'BoardSelection');
    if (checkbox.checked) {
        boardSelection.style.display = 'block';
    } else {
        boardSelection.style.display = 'none';
    }
}

// 비밀번호 필드에 붙여넣기 시 공백 자동 제거
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('mail_password');
    if (passwordInput) {
        passwordInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            // 공백 제거
            const cleanedText = pastedText.replace(/\s+/g, '');
            this.value = cleanedText;
        });
        
        // 입력 중에도 공백 제거 (선택사항)
        passwordInput.addEventListener('input', function(e) {
            if (/\s/.test(this.value)) {
                this.value = this.value.replace(/\s+/g, '');
            }
        });
    }
});

document.getElementById('testMailBtn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('testMailModal'));
    modal.show();
});

document.getElementById('sendTestMailBtn').addEventListener('click', function() {
    const email = document.getElementById('test_email').value;
    const btn = this;
    const status = document.getElementById('testMailStatus');
    
    if (!email) {
        status.innerHTML = '<div class="alert alert-danger">이메일 주소를 입력해주세요.</div>';
        return;
    }
    
    btn.disabled = true;
    btn.textContent = '발송 중...';
    status.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>테스트 메일을 발송 중입니다...</div>';
    
    fetch('{{ route("admin.mail-settings.test", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            status.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
        } else {
            status.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + (data.message || '메일 발송에 실패했습니다.') + '</div>';
        }
        btn.disabled = false;
        btn.textContent = '발송';
    })
    .catch(error => {
        console.error('Error:', error);
        status.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>메일 발송 중 오류가 발생했습니다.</div>';
        btn.disabled = false;
        btn.textContent = '발송';
    });
});

document.getElementById('mailSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>저장 중...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('메일 설정이 저장되었습니다.');
            window.location.reload();
        } else {
            alert('저장 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});
</script>
@endpush
@endsection

