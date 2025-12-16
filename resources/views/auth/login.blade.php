@extends('layouts.app')

@section('title', '로그인')

@section('content')
<style>
    body {
        background-color: #ffffff !important;
    }
    .login-container {
        background-color: #ffffff;
        min-height: 100vh;
    }
    /* 로그인 페이지에서 상단 alert 배너 숨기기 */
    main.container .alert,
    main .alert-danger,
    main .alert {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 로그인 페이지에서 alert 배너 제거
    const alerts = document.querySelectorAll('main .alert, main.container .alert-danger, main.container .alert');
    alerts.forEach(function(alert) {
        alert.style.display = 'none';
        alert.remove();
    });
});
</script>

<div class="login-container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0">
                        <i class="bi bi-box-arrow-in-right me-2"></i>로그인
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login', ['site' => $site->slug]) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                @if($site->getSetting('registration_login_method', 'email') === 'username')
                                    아이디
                                @else
                                    이메일
                                @endif
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="{{ $site->getSetting('registration_login_method', 'email') === 'username' ? 'text' : 'email' }}" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autofocus
                                       placeholder="@if($site->getSetting('registration_login_method', 'email') === 'username')아이디를 입력하세요@else이메일을 입력하세요@endif">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">비밀번호</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="비밀번호를 입력하세요">
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                로그인 상태 유지
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>로그인
                            </button>
                        </div>
                    </form>

                    {{-- 소셜 로그인 --}}
                    @php
                        // 마스터 사이트는 두 가지 설정 키 모두 확인 (마스터 콘솔 설정 또는 일반 관리자 설정)
                        if ($site->isMasterSite()) {
                            $socialLoginEnabledRaw = $site->getSetting('social_login_enabled', $site->getSetting('registration_enable_social_login', false));
                            $googleClientId = $site->getSetting('social_login_google_client_id', $site->getSetting('google_client_id', ''));
                            $naverClientId = $site->getSetting('social_login_naver_client_id', $site->getSetting('naver_client_id', ''));
                            $kakaoClientId = $site->getSetting('social_login_kakao_client_id', $site->getSetting('kakao_client_id', ''));
                        } else {
                            $socialLoginEnabledRaw = $site->getSetting('registration_enable_social_login', false);
                            $googleClientId = $site->getSetting('google_client_id', '');
                            $naverClientId = $site->getSetting('naver_client_id', '');
                            $kakaoClientId = $site->getSetting('kakao_client_id', '');
                        }
                        // 문자열 "1", "true", 숫자 1 등을 boolean으로 변환
                        $socialLoginEnabled = filter_var($socialLoginEnabledRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                    @endphp
                    @if($socialLoginEnabled && (!empty($googleClientId) || !empty($naverClientId) || !empty($kakaoClientId)))
                    <div class="mt-4">
                        <div class="text-center mb-3">
                            <small class="text-muted">또는</small>
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            @if(!empty($googleClientId))
                            <a href="{{ $site->isMasterSite() ? route('master.social.login', ['provider' => 'google']) : route('social.login', ['site' => $site->slug, 'provider' => 'google']) }}" 
                               class="btn btn-outline-danger btn-sm" style="flex: 0 0 auto;">
                                <i class="bi bi-google me-1"></i>구글
                            </a>
                            @endif
                            @if(!empty($naverClientId))
                            <a href="{{ $site->isMasterSite() ? route('master.social.login', ['provider' => 'naver']) : route('social.login', ['site' => $site->slug, 'provider' => 'naver']) }}" 
                               class="btn btn-sm" style="background-color: #03C75A; border-color: #03C75A; color: white; flex: 0 0 auto;">
                                <span class="fw-bold me-1" style="font-size: 1em;">N</span>네이버
                            </a>
                            @endif
                            @if(!empty($kakaoClientId))
                            <a href="{{ $site->isMasterSite() ? route('master.social.login', ['provider' => 'kakao']) : route('social.login', ['site' => $site->slug, 'provider' => 'kakao']) }}" 
                               class="btn btn-sm" style="background-color: #FEE500; border-color: #FEE500; color: #000; flex: 0 0 auto;">
                                <i class="bi bi-chat-fill me-1"></i>카카오
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0 text-muted">계정이 없으신가요?</p>
                        <a href="{{ route('register', ['site' => $site->slug]) }}" class="btn btn-outline-primary mt-2">
                            <i class="bi bi-person-plus me-1"></i>회원가입
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 로그인 실패 모달 -->
<div class="modal fade" id="loginErrorModal" tabindex="-1" aria-labelledby="loginErrorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="loginErrorModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>로그인 실패
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="loginErrorMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 에러 메시지가 있는 경우 모달 표시
    @if($errors->has('email'))
        const errorMessage = @json($errors->first('email'));
        // 영문 메시지 제거 (한글만 표시)
        const koreanMessage = errorMessage.replace(/[a-zA-Z]/g, '').trim();
        if (koreanMessage) {
            document.getElementById('loginErrorMessage').textContent = koreanMessage;
            const modal = new bootstrap.Modal(document.getElementById('loginErrorModal'));
            modal.show();
        }
    @endif
    
    @if($errors->has('password'))
        const passwordErrorMessage = @json($errors->first('password'));
        const koreanPasswordMessage = passwordErrorMessage.replace(/[a-zA-Z]/g, '').trim();
        if (koreanPasswordMessage) {
            document.getElementById('loginErrorMessage').textContent = koreanPasswordMessage;
            const modal = new bootstrap.Modal(document.getElementById('loginErrorModal'));
            modal.show();
        }
    @endif
});
</script>
@endsection
