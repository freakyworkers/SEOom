@extends('layouts.app')

@section('title', '회원가입')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>회원가입
                </h4>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- 소셜 로그인 --}}
                @php
                    $socialLoginEnabledRaw = $site->isMasterSite() 
                        ? $site->getSetting('social_login_enabled', false)
                        : $site->getSetting('registration_enable_social_login', false);
                    // 문자열 "1", "true", 숫자 1 등을 boolean으로 변환
                    $socialLoginEnabled = filter_var($socialLoginEnabledRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                    $googleClientId = $site->isMasterSite()
                        ? $site->getSetting('social_login_google_client_id', '')
                        : $site->getSetting('google_client_id', '');
                    $naverClientId = $site->isMasterSite()
                        ? $site->getSetting('social_login_naver_client_id', '')
                        : $site->getSetting('naver_client_id', '');
                    $kakaoClientId = $site->isMasterSite()
                        ? $site->getSetting('social_login_kakao_client_id', '')
                        : $site->getSetting('kakao_client_id', '');
                @endphp
                @if($socialLoginEnabled && (!empty($googleClientId) || !empty($naverClientId) || !empty($kakaoClientId)))
                <div class="mb-4">
                    <div class="text-center mb-3">
                        <small class="text-muted">또는</small>
                    </div>
                    <div class="d-grid gap-2">
                        @if(!empty($googleClientId))
                        <a href="{{ $site->isMasterSite() ? route('master.social.login', ['provider' => 'google']) : route('social.login', ['site' => $site->slug, 'provider' => 'google']) }}" 
                           class="btn btn-outline-danger">
                            <i class="bi bi-google me-2"></i>구글로 시작하기
                        </a>
                        @endif
                        @if(!empty($naverClientId))
                        <a href="{{ $site->isMasterSite() ? route('master.social.login', ['provider' => 'naver']) : route('social.login', ['site' => $site->slug, 'provider' => 'naver']) }}" 
                           class="btn btn-outline-success" style="background-color: #03C75A; border-color: #03C75A; color: white;">
                            <i class="bi bi-chat-dots me-2"></i>네이버로 시작하기
                        </a>
                        @endif
                        @if(!empty($kakaoClientId))
                        <a href="{{ $site->isMasterSite() ? route('master.social.login', ['provider' => 'kakao']) : route('social.login', ['site' => $site->slug, 'provider' => 'kakao']) }}" 
                           class="btn btn-outline-warning" style="background-color: #FEE500; border-color: #FEE500; color: #000;">
                            <i class="bi bi-chat-fill me-2"></i>카카오로 시작하기
                        </a>
                        @endif
                    </div>
                    <hr class="my-4">
                </div>
                @endif

                <form method="POST" action="{{ route('register', ['site' => $site->slug]) }}" id="registerForm">
                    @csrf

                    {{-- 아이디 --}}
                    <div class="mb-3">
                        <label for="username" class="form-label">아이디 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username') }}" 
                                   required 
                                   autofocus
                                   placeholder="아이디를 입력하세요">
                        </div>
                        @error('username')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 비밀번호 --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">비밀번호 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder="최소 8자 이상">
                        </div>
                        <small class="form-text text-muted">최소 8자 이상 입력해주세요.</small>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 비밀번호 확인 --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">비밀번호 확인 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required
                                   placeholder="비밀번호를 다시 입력하세요">
                        </div>
                    </div>

                    {{-- 이름 --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required
                                   placeholder="홍길동">
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 닉네임 --}}
                    <div class="mb-3">
                        <label for="nickname" class="form-label">닉네임 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                            <input type="text" 
                                   class="form-control @error('nickname') is-invalid @enderror" 
                                   id="nickname" 
                                   name="nickname" 
                                   value="{{ old('nickname') }}" 
                                   required
                                   placeholder="닉네임을 입력하세요">
                        </div>
                        @error('nickname')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 이메일 --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">이메일 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="text" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email_prefix" 
                                   value="{{ old('email_prefix', session('verified_email') ? explode('@', session('verified_email'))[0] : '') }}" 
                                   placeholder="이메일 아이디"
                                   {{ $site->getSetting('registration_enable_email_verification', false) && session('verified_email') ? 'readonly' : '' }}
                                   autocomplete="off">
                            <span class="input-group-text">@</span>
                            <select class="form-select @error('email') is-invalid @enderror" 
                                    id="email_domain" 
                                    {{ $site->getSetting('registration_enable_email_verification', false) && session('verified_email') ? 'disabled' : '' }}>
                                <option value="gmail.com" {{ old('email_domain', session('verified_email') ? explode('@', session('verified_email'))[1] : 'gmail.com') === 'gmail.com' ? 'selected' : '' }}>gmail.com</option>
                                <option value="naver.com" {{ old('email_domain', session('verified_email') ? explode('@', session('verified_email'))[1] : '') === 'naver.com' ? 'selected' : '' }}>naver.com</option>
                                <option value="daum.net" {{ old('email_domain', session('verified_email') ? explode('@', session('verified_email'))[1] : '') === 'daum.net' ? 'selected' : '' }}>daum.net</option>
                                <option value="hanmail.net" {{ old('email_domain', session('verified_email') ? explode('@', session('verified_email'))[1] : '') === 'hanmail.net' ? 'selected' : '' }}>hanmail.net</option>
                                <option value="kakao.com" {{ old('email_domain', session('verified_email') ? explode('@', session('verified_email'))[1] : '') === 'kakao.com' ? 'selected' : '' }}>kakao.com</option>
                                <option value="outlook.com" {{ old('email_domain', session('verified_email') ? explode('@', session('verified_email'))[1] : '') === 'outlook.com' ? 'selected' : '' }}>outlook.com</option>
                                <option value="yahoo.com" {{ old('email_domain', session('verified_email') ? explode('@', session('verified_email'))[1] : '') === 'yahoo.com' ? 'selected' : '' }}>yahoo.com</option>
                                <option value="custom">직접 입력</option>
                            </select>
                            <input type="text" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email_domain_custom" 
                                   placeholder="도메인 입력 (예: example.com)"
                                   style="display: none;"
                                   autocomplete="off"
                                   pattern="[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}">
                            <input type="hidden" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', session('verified_email')) }}" 
                                   required>
                        </div>
                        @if($site->getSetting('registration_enable_email_verification', false))
                            <div class="mt-2">
                                <button type="button" class="btn btn-outline-primary w-100" id="emailVerifyBtn">
                                    인증하기
                                </button>
                            </div>
                        @endif
                        @if($site->getSetting('registration_enable_email_verification', false))
                            <div id="emailVerifyStatus" class="mt-2"></div>
                            {{-- 인증번호 입력 필드 --}}
                            <div id="verificationCodeInput" class="mt-2" style="display: none;">
                                <label for="verification_code" class="form-label">인증번호 <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="verification_code" 
                                           name="verification_code" 
                                           maxlength="6" 
                                           pattern="[0-9]{6}"
                                           placeholder="6자리 인증번호를 입력하세요"
                                           style="font-size: 18px; letter-spacing: 4px; text-align: center;">
                                    <button type="button" class="btn btn-primary" id="verifyCodeBtn">인증 확인</button>
                                </div>
                                <div id="codeVerifyStatus" class="mt-2"></div>
                            </div>
                            <input type="hidden" id="email_verified" name="email_verified" value="0">
                        @endif
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 전화번호 --}}
                    @if($site->getSetting('registration_enable_phone', false))
                    <div class="mb-3">
                        <label for="phone" class="form-label">전화번호 @if($site->getSetting('registration_enable_phone_verification', false))<span class="text-danger">*</span>@endif</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="tel" 
                                   class="form-control phone-format @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', session('verified_phone')) }}" 
                                   placeholder="010-1234-5678"
                                   maxlength="13"
                                   {{ $site->getSetting('registration_enable_phone_verification', false) && session('verified_phone') ? 'readonly' : '' }}
                                   @if($site->getSetting('registration_enable_phone_verification', false)) required @endif>
                            @if($site->getSetting('registration_enable_phone_verification', false))
                                <button type="button" class="btn btn-outline-primary" id="phoneVerifyBtn">
                                    인증하기
                                </button>
                            @endif
                        </div>
                        @if($site->getSetting('registration_enable_phone_verification', false))
                            <div id="phoneVerifyStatus" class="mt-2"></div>
                            {{-- 인증번호 입력 필드 --}}
                            <div id="phoneVerificationCodeInput" class="mt-2" style="display: none;">
                                <label for="phone_verification_code" class="form-label">인증번호 <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                    <input type="text" 
                                           class="form-control @error('phone_verification_code') is-invalid @enderror" 
                                           id="phone_verification_code" 
                                           name="phone_verification_code" 
                                           placeholder="6자리 인증번호를 입력하세요"
                                           maxlength="6"
                                           pattern="[0-9]{6}">
                                    <button type="button" class="btn btn-primary" id="verifyPhoneCodeBtn">인증 확인</button>
                                </div>
                                @error('phone_verification_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <input type="hidden" id="phone_verified" name="phone_verified" value="0">
                        @endif
                        @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    {{-- 주소 --}}
                    @if($site->getSetting('registration_enable_address', false))
                    <div class="mb-3">
                        <label class="form-label">주소</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" 
                                   class="form-control @error('postal_code') is-invalid @enderror" 
                                   id="postal_code" 
                                   name="postal_code" 
                                   value="{{ old('postal_code') }}" 
                                   placeholder="우편번호"
                                   readonly>
                            <button type="button" class="btn btn-outline-secondary" id="addressSearchBtn">
                                <i class="bi bi-search me-1"></i>주소 검색
                            </button>
                        </div>
                        @error('postal_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-map"></i></span>
                            <input type="text" 
                                   class="form-control @error('address') is-invalid @enderror" 
                                   id="address" 
                                   name="address" 
                                   value="{{ old('address') }}" 
                                   placeholder="주소"
                                   readonly>
                        </div>
                        @error('address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-house"></i></span>
                            <input type="text" 
                                   class="form-control @error('address_detail') is-invalid @enderror" 
                                   id="address_detail" 
                                   name="address_detail" 
                                   value="{{ old('address_detail') }}" 
                                   placeholder="상세주소">
                        </div>
                        @error('address_detail')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    {{-- 추천인 --}}
                    @if($site->getSetting('registration_enable_referrer', false))
                    <div class="mb-3">
                        <label for="referrer_nickname" class="form-label">추천인</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-people"></i></span>
                            <input type="text" 
                                   class="form-control @error('referrer_nickname') is-invalid @enderror" 
                                   id="referrer_nickname" 
                                   name="referrer_nickname" 
                                   value="{{ old('referrer_nickname') }}" 
                                   placeholder="추천인 닉네임 또는 이름을 입력하세요">
                            <button type="button" class="btn btn-outline-primary" id="verifyReferrerBtn">
                                확인
                            </button>
                        </div>
                        <div id="referrerVerifyStatus" class="mt-2"></div>
                        <small class="form-text text-muted">추천인 닉네임 또는 이름을 입력하고 확인 버튼을 클릭하세요. (선택사항)</small>
                        @error('referrer_nickname')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-person-plus me-2"></i>회원가입
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <p class="mb-0 text-muted">이미 계정이 있으신가요?</p>
                    <a href="{{ route('login', ['site' => $site->slug]) }}" class="btn btn-outline-primary mt-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i>로그인
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if($site->getSetting('registration_enable_address', false))
@push('scripts')
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
document.getElementById('addressSearchBtn').addEventListener('click', function() {
    new daum.Postcode({
        oncomplete: function(data) {
            document.getElementById('postal_code').value = data.zonecode;
            document.getElementById('address').value = data.address;
            document.getElementById('address_detail').focus();
        }
    }).open();
});
</script>
@endpush
@endif

@if($site->getSetting('registration_enable_referrer', false))
@push('scripts')
<script>
document.getElementById('verifyReferrerBtn').addEventListener('click', function() {
    const nickname = document.getElementById('referrer_nickname').value;
    const btn = this;
    const statusDiv = document.getElementById('referrerVerifyStatus');
    
    if (!nickname) {
        statusDiv.innerHTML = '<div class="alert alert-warning mb-0 py-2"><i class="bi bi-exclamation-triangle me-2"></i>추천인 닉네임을 입력해주세요.</div>';
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>확인 중...';
    statusDiv.innerHTML = '';
    
    fetch('{{ route("register.verify-referrer", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ nickname: nickname })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '확인';
        
        if (data.success) {
            statusDiv.innerHTML = '<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle me-2"></i>추천인 확인 완료: ' + data.referrer.name + ' (' + data.referrer.nickname + ')</div>';
        } else {
            statusDiv.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>' + data.message + '</div>';
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '확인';
        statusDiv.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>오류가 발생했습니다.</div>';
        console.error('Error:', error);
    });
});
</script>
@endpush
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 이메일 prefix와 domain을 합쳐서 실제 email 필드에 저장
    const emailPrefix = document.getElementById('email_prefix');
    const emailDomain = document.getElementById('email_domain');
    const emailDomainCustom = document.getElementById('email_domain_custom');
    const emailInput = document.getElementById('email');
    
    function updateEmail() {
        const prefix = emailPrefix.value.trim();
        let domain = '';
        
        if (emailDomain.value === 'custom') {
            domain = emailDomainCustom.value.trim();
            if (!domain) {
                emailInput.value = '';
                return;
            }
        } else {
            domain = emailDomain.value;
        }
        
        if (prefix && domain) {
            emailInput.value = prefix + '@' + domain;
        } else {
            emailInput.value = '';
        }
    }
    
    // 직접 입력 옵션 선택 시 커스텀 입력 필드 표시
    emailDomain.addEventListener('change', function() {
        if (this.value === 'custom') {
            emailDomainCustom.style.display = 'block';
            emailDomainCustom.required = true;
            emailDomainCustom.focus();
        } else {
            emailDomainCustom.style.display = 'none';
            emailDomainCustom.required = false;
            emailDomainCustom.value = '';
        }
        updateEmail();
    });
    
    // 커스텀 도메인 입력 시 이메일 업데이트
    emailDomainCustom.addEventListener('input', function() {
        if (emailDomain.value === 'custom') {
            updateEmail();
        }
    });
    
    // prefix나 domain 변경 시 email 업데이트
    emailPrefix.addEventListener('input', updateEmail);
    emailPrefix.addEventListener('blur', updateEmail);
    emailDomainCustom.addEventListener('input', updateEmail);
    emailDomainCustom.addEventListener('blur', updateEmail);
    
    // 초기 이메일이 있는 경우 prefix와 domain 분리
    @if(old('email', session('verified_email')))
        const initialEmail = '{{ old('email', session('verified_email')) }}';
        if (initialEmail && initialEmail.includes('@')) {
            const parts = initialEmail.split('@');
            if (parts.length === 2) {
                emailPrefix.value = parts[0];
                const domain = parts[1];
                // 드롭다운에 있는 도메인인지 확인
                const domainOption = Array.from(emailDomain.options).find(opt => opt.value === domain);
                if (domainOption) {
                    emailDomain.value = domain;
                } else {
                    emailDomain.value = 'custom';
                    emailDomainCustom.style.display = 'block';
                    emailDomainCustom.value = domain;
                }
                updateEmail();
            }
        }
    @endif
    
    // 폼 제출 전 이메일 검증
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            updateEmail();
            if (!emailInput.value || !emailInput.value.includes('@')) {
                e.preventDefault();
                alert('올바른 이메일을 입력해주세요.');
                emailPrefix.focus();
                return false;
            }
        });
    }
});

@if($site->getSetting('registration_enable_email_verification', false))
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const emailVerifyBtn = document.getElementById('emailVerifyBtn');
    const emailVerifyStatus = document.getElementById('emailVerifyStatus');
    const emailVerified = document.getElementById('email_verified');
    
    // 인증 완료된 이메일인 경우 상태 표시
    @if(session('verified_email'))
        emailVerifyStatus.innerHTML = '<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle me-2"></i>이메일 인증이 완료되었습니다.</div>';
        emailVerified.value = '1';
        emailVerifyBtn.textContent = '인증 완료';
        emailVerifyBtn.disabled = true;
        emailVerifyBtn.classList.remove('btn-outline-primary');
        emailVerifyBtn.classList.add('btn-success');
    @endif
    
    // 이메일 업데이트 함수
    function updateEmailForVerification() {
        const emailPrefix = document.getElementById('email_prefix');
        const emailDomain = document.getElementById('email_domain');
        const emailDomainCustom = document.getElementById('email_domain_custom');
        const prefix = emailPrefix.value.trim();
        let domain = '';
        
        if (emailDomain.value === 'custom') {
            domain = emailDomainCustom.value.trim();
            if (!domain) {
                emailInput.value = '';
                return false;
            }
        } else {
            domain = emailDomain.value;
        }
        
        if (prefix && domain) {
            emailInput.value = prefix + '@' + domain;
            return true;
        } else {
            emailInput.value = '';
            return false;
        }
    }
    
    emailVerifyBtn.addEventListener('click', function() {
        // 이메일 업데이트
        if (!updateEmailForVerification()) {
            alert('이메일을 입력해주세요.');
            const emailPrefix = document.getElementById('email_prefix');
            emailPrefix.focus();
            return;
        }
        
        const email = emailInput.value;
        const btn = this;
        
        // 이메일 형식 검증
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('올바른 이메일 형식을 입력해주세요.');
            return;
        }
        
        btn.disabled = true;
        btn.textContent = '전송 중...';
        emailVerifyStatus.innerHTML = '<div class="alert alert-info mb-0 py-2"><i class="bi bi-hourglass-split me-2"></i>인증 메일을 전송 중입니다...</div>';
        
        fetch('{{ route("register.send-verification", ["site" => $site->slug]) }}', {
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
                let statusHtml = '<div class="alert alert-success mb-2 py-2"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
                
                // 개발 모드에서 인증번호 표시
                if (data.debug_mode && data.verification_code) {
                    statusHtml += '<div class="alert alert-warning mb-2 py-2">';
                    statusHtml += '<strong>개발 모드:</strong> 인증번호: <strong style="font-size: 20px; letter-spacing: 4px;">' + data.verification_code + '</strong>';
                    statusHtml += '</div>';
                }
                
                emailVerifyStatus.innerHTML = statusHtml;
                
                // 인증번호 입력 필드 표시
                document.getElementById('verificationCodeInput').style.display = 'block';
                document.getElementById('verification_code').focus();
                const emailPrefix = document.getElementById('email_prefix');
                const emailDomain = document.getElementById('email_domain');
                const emailDomainCustom = document.getElementById('email_domain_custom');
                emailPrefix.readOnly = true;
                emailDomain.disabled = true;
                if (emailDomain.value === 'custom') {
                    emailDomainCustom.readOnly = true;
                }
                
                btn.textContent = '재전송';
                emailVerified.value = '0';
            } else {
                emailVerifyStatus.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>' + (data.message || '인증 메일 전송에 실패했습니다.') + '</div>';
                btn.textContent = '인증하기';
                emailVerified.value = '0';
            }
            btn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            emailVerifyStatus.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>인증 메일 전송에 실패했습니다.</div>';
            btn.textContent = '인증하기';
            emailVerified.value = '0';
            btn.disabled = false;
        });
    });
    
    // 인증번호 입력 필드 - 숫자만 입력
    const verificationCodeInput = document.getElementById('verification_code');
    if (verificationCodeInput) {
        verificationCodeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        verificationCodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('verifyCodeBtn').click();
            }
        });
    }
    
    // 인증번호 확인 버튼
    const verifyCodeBtn = document.getElementById('verifyCodeBtn');
    if (verifyCodeBtn) {
        verifyCodeBtn.addEventListener('click', function() {
            // 이메일 업데이트
            updateEmailForVerification();
            const email = emailInput.value;
            const code = verificationCodeInput.value;
            const btn = this;
            const codeStatus = document.getElementById('codeVerifyStatus');
            
            if (!code || code.length !== 6) {
                codeStatus.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>6자리 인증번호를 입력해주세요.</div>';
                return;
            }
            
            btn.disabled = true;
            btn.textContent = '확인 중...';
            codeStatus.innerHTML = '<div class="alert alert-info mb-0 py-2"><i class="bi bi-hourglass-split me-2"></i>인증번호를 확인 중입니다...</div>';
            
            fetch('{{ route("register.verify-code", ["site" => $site->slug]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    email: email,
                    code: code 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    codeStatus.innerHTML = '<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
                    emailVerified.value = '1';
                    emailVerifyBtn.textContent = '인증 완료';
                    emailVerifyBtn.disabled = true;
                    emailVerifyBtn.classList.remove('btn-outline-primary');
                    emailVerifyBtn.classList.add('btn-success');
                    verificationCodeInput.readOnly = true;
                    btn.disabled = true;
                    btn.textContent = '인증 완료';
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-success');
                } else {
                    codeStatus.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>' + (data.message || '인증번호가 일치하지 않습니다.') + '</div>';
                    btn.disabled = false;
                    btn.textContent = '인증 확인';
                    verificationCodeInput.focus();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                codeStatus.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>인증번호 확인에 실패했습니다.</div>';
                btn.disabled = false;
                btn.textContent = '인증 확인';
            });
        });
    }
});

// 전화번호 자동 포맷팅 (전화번호 필드가 있는 경우)
const phoneInputForFormat = document.getElementById('phone');
if (phoneInputForFormat && !phoneInputForFormat.readOnly) {
    // 전화번호 자동 포맷팅 함수
    function formatPhoneNumber(value) {
        const numbers = value.replace(/[^0-9]/g, '');
        if (numbers.length <= 3) {
            return numbers;
        } else if (numbers.length <= 7) {
            return numbers.slice(0, 3) + '-' + numbers.slice(3);
        } else if (numbers.length <= 11) {
            return numbers.slice(0, 3) + '-' + numbers.slice(3, 7) + '-' + numbers.slice(7);
        } else {
            return numbers.slice(0, 3) + '-' + numbers.slice(3, 7) + '-' + numbers.slice(7, 11);
        }
    }
    
    phoneInputForFormat.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        let formatted = '';
        
        if (value.length <= 3) {
            formatted = value;
        } else if (value.length <= 7) {
            formatted = value.slice(0, 3) + '-' + value.slice(3);
        } else if (value.length <= 11) {
            formatted = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7);
        } else {
            formatted = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7, 11);
        }
        
        e.target.value = formatted;
    });
}
</script>
@endpush
@endif

@if($site->getSetting('registration_enable_phone_verification', false))
@push('scripts')
<script>
// 전화번호 자동 포맷팅 함수
function formatPhoneNumber(value) {
    // 숫자만 추출
    const numbers = value.replace(/[^0-9]/g, '');
    
    // 11자리 숫자인 경우에만 포맷팅
    if (numbers.length <= 3) {
        return numbers;
    } else if (numbers.length <= 7) {
        return numbers.slice(0, 3) + '-' + numbers.slice(3);
    } else if (numbers.length <= 11) {
        return numbers.slice(0, 3) + '-' + numbers.slice(3, 7) + '-' + numbers.slice(7);
    } else {
        return numbers.slice(0, 3) + '-' + numbers.slice(3, 7) + '-' + numbers.slice(7, 11);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    const phoneVerifyBtn = document.getElementById('phoneVerifyBtn');
    const phoneVerifyStatus = document.getElementById('phoneVerifyStatus');
    const phoneVerified = document.getElementById('phone_verified');
    const phoneVerificationCodeInputDiv = document.getElementById('phoneVerificationCodeInput');
    const phoneVerificationCodeInput = document.getElementById('phone_verification_code');
    const verifyPhoneCodeBtn = document.getElementById('verifyPhoneCodeBtn');
    
    // 전화번호 자동 포맷팅 적용
    if (phoneInput && !phoneInput.readOnly) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            let formatted = '';
            
            if (value.length <= 3) {
                formatted = value;
            } else if (value.length <= 7) {
                formatted = value.slice(0, 3) + '-' + value.slice(3);
            } else if (value.length <= 11) {
                formatted = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7);
            } else {
                formatted = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7, 11);
            }
            
            e.target.value = formatted;
        });
    }
    
    // 전화번호가 이미 인증된 경우 UI 업데이트
    if (phoneVerified.value === '1') {
        phoneInput.readOnly = true;
        phoneVerifyBtn.textContent = '인증 완료';
        phoneVerifyBtn.disabled = true;
        phoneVerifyBtn.classList.remove('btn-outline-primary');
        phoneVerifyBtn.classList.add('btn-success');
        phoneVerificationCodeInputDiv.style.display = 'none';
    }
    
    // 전화번호 인증하기 버튼 클릭
    phoneVerifyBtn.addEventListener('click', function() {
        const phone = phoneInput.value.trim();
        const btn = this;
        
        if (!phone) {
            alert('전화번호를 입력해주세요.');
            return;
        }
        
        // 전화번호 형식 검증
        const phoneRegex = /^01[0-9]-?[0-9]{3,4}-?[0-9]{4}$/;
        if (!phoneRegex.test(phone.replace(/-/g, ''))) {
            alert('올바른 전화번호 형식을 입력해주세요. (예: 010-1234-5678)');
            return;
        }
        
        btn.disabled = true;
        btn.textContent = '전송 중...';
        phoneVerifyStatus.innerHTML = '<div class="alert alert-info mb-0 py-2"><i class="bi bi-hourglass-split me-2"></i>인증번호를 전송 중입니다...</div>';
        phoneVerificationCodeInputDiv.style.display = 'none';
        
        fetch('{{ route("register.send-phone-verification", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ phone: phone })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                phoneVerifyStatus.innerHTML = '<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
                btn.textContent = '재전송';
                phoneInput.readOnly = true;
                phoneVerificationCodeInputDiv.style.display = 'block';
                phoneVerificationCodeInput.focus();
            } else {
                phoneVerifyStatus.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>' + (data.message || '인증번호 전송에 실패했습니다.') + '</div>';
                btn.textContent = '인증하기';
                phoneInput.readOnly = false;
                phoneVerificationCodeInputDiv.style.display = 'none';
            }
            btn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            phoneVerifyStatus.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>인증번호 전송에 실패했습니다.</div>';
            btn.textContent = '인증하기';
            phoneInput.readOnly = false;
            phoneVerificationCodeInputDiv.style.display = 'none';
            btn.disabled = false;
        });
    });
    
    // 인증번호 입력 필드 - 숫자만 입력
    if (phoneVerificationCodeInput) {
        phoneVerificationCodeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        phoneVerificationCodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                verifyPhoneCodeBtn.click();
            }
        });
    }
    
    // 인증번호 확인 버튼
    verifyPhoneCodeBtn.addEventListener('click', function() {
        const phone = phoneInput.value.trim();
        const code = phoneVerificationCodeInput.value;
        const btn = this;
        
        if (!phone || !code) {
            alert('전화번호와 인증번호를 모두 입력해주세요.');
            return;
        }
        
        if (code.length !== 6 || !/^\d+$/.test(code)) {
            alert('인증번호는 6자리 숫자입니다.');
            return;
        }
        
        btn.disabled = true;
        btn.textContent = '확인 중...';
        phoneVerifyStatus.innerHTML = '<div class="alert alert-info mb-0 py-2"><i class="bi bi-hourglass-split me-2"></i>인증번호를 확인 중입니다...</div>';
        
        fetch('{{ route("register.verify-phone-code", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ phone: phone, code: code })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                phoneVerifyStatus.innerHTML = '<div class="alert alert-success mb-0 py-2"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
                phoneVerified.value = '1';
                phoneVerifyBtn.textContent = '인증 완료';
                phoneVerifyBtn.disabled = true;
                phoneVerifyBtn.classList.remove('btn-outline-primary');
                phoneVerifyBtn.classList.add('btn-success');
                phoneVerificationCodeInputDiv.style.display = 'none';
                phoneInput.readOnly = true;
            } else {
                phoneVerifyStatus.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>' + (data.message || '인증번호 확인에 실패했습니다.') + '</div>';
                phoneVerified.value = '0';
            }
            btn.disabled = false;
            btn.textContent = '인증 확인';
        })
        .catch(error => {
            console.error('Error:', error);
            phoneVerifyStatus.innerHTML = '<div class="alert alert-danger mb-0 py-2"><i class="bi bi-x-circle me-2"></i>인증번호 확인에 실패했습니다.</div>';
            phoneVerified.value = '0';
            btn.disabled = false;
            btn.textContent = '인증 확인';
        });
    });
});
</script>
@endpush
@endif
@endsection
