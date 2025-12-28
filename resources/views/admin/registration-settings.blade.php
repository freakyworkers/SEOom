@extends('layouts.admin')

@section('title', '회원가입 설정')
@section('page-title', '회원가입 설정')
@section('page-subtitle', '회원가입 시 입력받을 정보를 설정합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>회원가입 설정</h5>
            </div>
            <div class="card-body">
                <form id="registrationSettingsForm" method="POST" action="{{ $site->isMasterSite() ? url('registration-settings/update') : route('admin.registration-settings.update', ['site' => $site->slug]) }}">
                    @csrf
                    
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-list-check me-2"></i>기본 폼</h6>
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>기본 폼 항목:</strong> 아이디, 비밀번호, 비밀번호 확인, 이름, 닉네임, 이메일은 항상 표시됩니다.
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <label for="login_method" class="form-label fw-bold">로그인 방식</label>
                                <select class="form-select" id="login_method" name="login_method" required>
                                    <option value="email" {{ ($settings['login_method'] ?? 'email') === 'email' ? 'selected' : '' }}>이메일 입력</option>
                                    <option value="username" {{ ($settings['login_method'] ?? 'email') === 'username' ? 'selected' : '' }}>아이디 입력</option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    로그인 시 사용할 인증 방식을 선택합니다.
                                </small>
                            </div>
                        </div>
                    </div>

                    @if($site->hasRegistrationFeature('signup_points'))
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-coin me-2"></i>가입 포인트</h6>
                        <div class="card">
                            <div class="card-body">
                                <label for="signup_points" class="form-label fw-bold">가입 포인트</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="signup_points" 
                                           name="signup_points" 
                                           value="{{ $settings['signup_points'] ?? 0 }}" 
                                           min="0" 
                                           step="1">
                                    <span class="input-group-text">포인트</span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    회원가입 시 자동으로 지급되는 포인트를 설정합니다. (기본값: 0)
                                </small>
                            </div>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-gear me-2"></i>회원가입 추가 기능</h6>
                        <p class="text-muted mb-3">아래 항목들을 체크하면 회원가입 폼에 해당 입력칸이 추가됩니다.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="enable_phone" 
                                                   name="enable_phone" 
                                                   value="1"
                                                   {{ $settings['enable_phone'] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="enable_phone">
                                                전화번호
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            체크 시 회원가입 폼에 전화번호 입력칸이 표시됩니다.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="enable_address" 
                                                   name="enable_address" 
                                                   value="1"
                                                   {{ $settings['enable_address'] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="enable_address">
                                                주소
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            체크 시 주소 검색을 통한 우편번호, 주소, 상세주소 입력칸이 표시됩니다.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            @if($site->hasRegistrationFeature('phone_verification'))
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="enable_phone_verification" 
                                                   name="enable_phone_verification" 
                                                   value="1"
                                                   {{ $settings['enable_phone_verification'] ? 'checked' : '' }}
                                                   onchange="togglePhoneVerificationSettings()">
                                            <label class="form-check-label fw-bold" for="enable_phone_verification">
                                                전화번호 인증
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            체크 시 회원가입 시 전화번호 인증이 필요합니다.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($site->hasRegistrationFeature('identity_verification'))
                            <div class="col-md-6">
                                <div class="card mb-3 border-secondary">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="enable_identity_verification" 
                                                   name="enable_identity_verification" 
                                                   value="1"
                                                   {{ $settings['enable_identity_verification'] ? 'checked' : '' }}
                                                   disabled>
                                            <label class="form-check-label text-muted" for="enable_identity_verification">
                                                본인인증
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-clock-history me-1"></i>준비 중입니다.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="enable_email_verification" 
                                                   name="enable_email_verification" 
                                                   value="1"
                                                   {{ $settings['enable_email_verification'] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="enable_email_verification">
                                                이메일 인증
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            체크 시 이메일 입력칸 옆에 인증하기 버튼이 표시됩니다.
                                        </small>
                                        <div class="alert alert-warning mt-2 mb-0 py-2" style="font-size: 0.875rem;">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>참고:</strong> 이메일 인증 기능을 사용하려면 메일 서버 설정이 필요합니다.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($site->hasRegistrationFeature('referrer'))
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="enable_referrer" 
                                                   name="enable_referrer" 
                                                   value="1"
                                                   {{ $settings['enable_referrer'] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="enable_referrer">
                                                추천인
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            체크 시 회원가입 폼에 추천인 닉네임 입력칸이 표시됩니다.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    @if($site->hasRegistrationFeature('referrer'))
                    <div class="mb-4" id="referrerSettings" style="display: {{ $settings['enable_referrer'] ? 'block' : 'none' }};">
                        <h6 class="mb-3"><i class="bi bi-gift me-2"></i>추천인 혜택 설정</h6>
                        <p class="text-muted mb-3">추천인 기능이 활성화된 경우에만 적용됩니다.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <label for="referrer_points" class="form-label fw-bold">추천인 지급 포인트</label>
                                        <div class="input-group">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="referrer_points" 
                                                   name="referrer_points" 
                                                   value="{{ $settings['referrer_points'] ?? 0 }}" 
                                                   min="0" 
                                                   step="1">
                                            <span class="input-group-text">포인트</span>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            추천인(기존 회원)에게 지급되는 포인트입니다.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <label for="new_user_points" class="form-label fw-bold">가입자 지급 포인트</label>
                                        <div class="input-group">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="new_user_points" 
                                                   name="new_user_points" 
                                                   value="{{ $settings['new_user_points'] ?? 0 }}" 
                                                   min="0" 
                                                   step="1">
                                            <span class="input-group-text">포인트</span>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            추천인을 입력하고 가입한 사용자에게 지급되는 포인트입니다.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- 소셜 로그인 설정 --}}
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-share me-2"></i>소셜 로그인 설정</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="enable_social_login" 
                                           name="enable_social_login" 
                                           {{ ($settings['enable_social_login'] ?? false) ? 'checked' : '' }}
                                           onchange="toggleSocialLoginSettings()">
                                    <label class="form-check-label fw-bold" for="enable_social_login">
                                        소셜 로그인 활성화
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    체크 시 회원가입 페이지에 소셜 로그인 버튼이 표시됩니다.
                                </small>
                            </div>

                            <div id="socialLoginSettings" style="display: {{ ($settings['enable_social_login'] ?? false) ? 'block' : 'none' }};">
                                <hr>
                                
                                {{-- 구글 로그인 설정 --}}
                                <div class="mb-4">
                                    <h6 class="mb-3"><i class="bi bi-google me-2"></i>구글 로그인</h6>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="google_client_id" class="form-label fw-bold">Client ID</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="google_client_id" 
                                                           name="google_client_id" 
                                                           value="{{ $settings['google_client_id'] ?? '' }}" 
                                                           placeholder="구글 Client ID">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="google_client_secret" class="form-label fw-bold">Client Secret</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="google_client_secret" 
                                                           name="google_client_secret" 
                                                           value="{{ $settings['google_client_secret'] ?? '' }}" 
                                                           placeholder="구글 Client Secret">
                                                </div>
                                            </div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-info-circle me-1"></i>
                                                <a href="https://console.cloud.google.com/apis/credentials" target="_blank">구글 클라우드 콘솔</a>에서 OAuth 2.0 클라이언트 ID를 생성하세요.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- 네이버 로그인 설정 --}}
                                <div class="mb-4">
                                    <h6 class="mb-3"><i class="bi bi-chat-dots me-2"></i>네이버 로그인</h6>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="naver_client_id" class="form-label fw-bold">Client ID</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="naver_client_id" 
                                                           name="naver_client_id" 
                                                           value="{{ $settings['naver_client_id'] ?? '' }}" 
                                                           placeholder="네이버 Client ID">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="naver_client_secret" class="form-label fw-bold">Client Secret</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="naver_client_secret" 
                                                           name="naver_client_secret" 
                                                           value="{{ $settings['naver_client_secret'] ?? '' }}" 
                                                           placeholder="네이버 Client Secret">
                                                </div>
                                            </div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-info-circle me-1"></i>
                                                <a href="https://developers.naver.com/apps/#/register" target="_blank">네이버 개발자 센터</a>에서 애플리케이션을 등록하세요.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- 카카오 로그인 설정 --}}
                                <div class="mb-4">
                                    <h6 class="mb-3"><i class="bi bi-chat-fill me-2"></i>카카오 로그인</h6>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="kakao_client_id" class="form-label fw-bold">Client ID (REST API Key)</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="kakao_client_id" 
                                                           name="kakao_client_id" 
                                                           value="{{ $settings['kakao_client_id'] ?? '' }}" 
                                                           placeholder="카카오 REST API Key">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="kakao_client_secret" class="form-label fw-bold">Client Secret</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="kakao_client_secret" 
                                                           name="kakao_client_secret" 
                                                           value="{{ $settings['kakao_client_secret'] ?? '' }}" 
                                                           placeholder="카카오 Client Secret">
                                                </div>
                                            </div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-info-circle me-1"></i>
                                                <a href="https://developers.kakao.com/" target="_blank">카카오 디벨로퍼</a>에서 애플리케이션을 등록하세요.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 전화번호 인증 설정 --}}
                    <div id="phoneVerificationSettings" class="mb-4" style="display: {{ $settings['enable_phone_verification'] ? 'block' : 'none' }};">
                        <hr>
                        <h6 class="mb-3"><i class="bi bi-phone me-2"></i>휴대폰 인증 설정</h6>
                        
                        {{-- SMS 발신자 이름 --}}
                        <div class="card mb-3">
                            <div class="card-body">
                                <label for="sms_sender_name" class="form-label fw-bold">문자 발신자 이름</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="sms_sender_name" 
                                       name="sms_sender_name" 
                                       value="{{ $settings['sms_sender_name'] ?? '' }}" 
                                       placeholder="세움빌더">
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    입력한 이름이 문자 제목으로 사용됩니다. (예: "세움빌더 회원가입 인증번호안내")
                                </small>
                            </div>
                        </div>

                        {{-- SMS 제공업체 선택 --}}
                        <div class="card mb-3">
                            <div class="card-body">
                                <label for="sms_provider" class="form-label fw-bold">휴대폰 인증 종류</label>
                                <select class="form-select" id="sms_provider" name="sms_provider" onchange="toggleSmsProviderSettings()">
                                    <option value="cool_sms" {{ ($settings['sms_provider'] ?? 'cool_sms') === 'cool_sms' ? 'selected' : '' }}>Cool SMS</option>
                                    <option value="naver_cloud" {{ ($settings['sms_provider'] ?? 'cool_sms') === 'naver_cloud' ? 'selected' : '' }}>네이버 클라우드</option>
                                </select>
                            </div>
                        </div>

                        {{-- SOLAPI 설정 --}}
                        <div id="solapiSettings" class="card mb-3" style="display: {{ ($settings['sms_provider'] ?? 'cool_sms') === 'solapi' ? 'block' : 'none' }};">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>SOLAPI 설정</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="width: 150px;">API Key</th>
                                                <th style="width: 150px;">API Secret</th>
                                                <th style="width: 150px;">발신번호</th>
                                                <th style="width: 200px;">테스트</th>
                                                <th style="width: 100px;">설정</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="sms_solapi_api_key" 
                                                           name="sms_solapi_api_key" 
                                                           value="{{ $settings['sms_solapi_api_key'] ?? '' }}" 
                                                           placeholder="API Key">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="sms_solapi_api_secret" 
                                                           name="sms_solapi_api_secret" 
                                                           value="{{ $settings['sms_solapi_api_secret'] ?? '' }}" 
                                                           placeholder="API Secret">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control phone-format" 
                                                           id="sms_solapi_from" 
                                                           name="sms_solapi_from" 
                                                           value="{{ $settings['sms_solapi_from'] ?? '' }}" 
                                                           placeholder="01012345678"
                                                           maxlength="13">
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" 
                                                               class="form-control phone-format" 
                                                               id="solapi_test_phone" 
                                                               placeholder="010-1234-5678"
                                                               maxlength="13">
                                                        <button type="button" 
                                                                class="btn btn-outline-primary" 
                                                                onclick="testSms('solapi')">
                                                            테스트
                                                        </button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-primary w-100" 
                                                            onclick="saveSmsSettings('solapi')">
                                                        설정
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <a href="https://solapi.com" target="_blank">SOLAPI</a>에서 계정을 생성하고 API 키를 발급받으세요. 발신번호와 수신번호는 하이픈 없이 숫자만 입력하세요. (예: 01012345678)
                                </small>
                            </div>
                        </div>

                        {{-- Cool SMS 설정 --}}
                        <div id="coolSmsSettings" class="card mb-3" style="display: {{ ($settings['sms_provider'] ?? 'cool_sms') === 'cool_sms' ? 'block' : 'none' }};">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Cool SMS 설정</h6>
                            </div>
                            <div class="card-body">
                                {{-- 데스크탑 버전 (테이블) --}}
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="width: 150px;">API Key</th>
                                                <th style="width: 150px;">API Secret</th>
                                                <th style="width: 150px;">발신번호</th>
                                                <th style="width: 200px;">테스트</th>
                                                <th style="width: 100px;">설정</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="sms_cool_api_key" 
                                                           name="sms_cool_api_key" 
                                                           value="{{ $settings['sms_cool_api_key'] ?? '' }}" 
                                                           placeholder="API Key">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="sms_cool_api_secret" 
                                                           name="sms_cool_api_secret" 
                                                           value="{{ $settings['sms_cool_api_secret'] ?? '' }}" 
                                                           placeholder="API Secret">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control phone-format" 
                                                           id="sms_cool_from" 
                                                           name="sms_cool_from" 
                                                           value="{{ $settings['sms_cool_from'] ?? '' }}" 
                                                           placeholder="010-1234-5678"
                                                           maxlength="13">
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" 
                                                               class="form-control phone-format" 
                                                               id="cool_test_phone" 
                                                               placeholder="010-1234-5678"
                                                               maxlength="13">
                                                        <button type="button" 
                                                                class="btn btn-outline-primary" 
                                                                onclick="testSms('cool_sms')">
                                                            테스트
                                                        </button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-primary w-100" 
                                                            onclick="saveSmsSettings('cool_sms')">
                                                        설정
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- 모바일 버전 (카드 레이아웃) --}}
                                <div class="d-md-none">
                                    <div class="d-grid gap-3">
                                        <div>
                                            <label class="form-label small fw-bold mb-1">API Key</label>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   id="sms_cool_api_key_mobile" 
                                                   name="sms_cool_api_key" 
                                                   value="{{ $settings['sms_cool_api_key'] ?? '' }}" 
                                                   placeholder="API Key">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-bold mb-1">API Secret</label>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   id="sms_cool_api_secret_mobile" 
                                                   name="sms_cool_api_secret" 
                                                   value="{{ $settings['sms_cool_api_secret'] ?? '' }}" 
                                                   placeholder="API Secret">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-bold mb-1">발신번호</label>
                                            <input type="text" 
                                                   class="form-control form-control-sm phone-format" 
                                                   id="sms_cool_from_mobile" 
                                                   name="sms_cool_from" 
                                                   value="{{ $settings['sms_cool_from'] ?? '' }}" 
                                                   placeholder="010-1234-5678"
                                                   maxlength="13">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-bold mb-1">테스트</label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control form-control-sm phone-format" 
                                                       id="cool_test_phone_mobile" 
                                                       placeholder="010-1234-5678"
                                                       maxlength="13">
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-sm" 
                                                        onclick="testSms('cool_sms')">
                                                    테스트
                                                </button>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="button" 
                                                    class="btn btn-primary btn-sm" 
                                                    onclick="saveSmsSettings('cool_sms')">
                                                설정
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <small class="text-muted d-block mt-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <a href="https://www.coolsms.co.kr" target="_blank">Cool SMS 홈페이지</a>에서 API 키를 발급받으세요.
                                </small>
                            </div>
                        </div>

                        {{-- 네이버 클라우드 설정 --}}
                        <div id="naverCloudSettings" class="card mb-3" style="display: {{ ($settings['sms_provider'] ?? 'cool_sms') === 'naver_cloud' ? 'block' : 'none' }};">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>네이버 클라우드 플랫폼 설정</h6>
                            </div>
                            <div class="card-body">
                                {{-- 데스크탑 버전 (테이블) --}}
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="width: 150px;">API Key</th>
                                                <th style="width: 150px;">API Secret Key</th>
                                                <th style="width: 150px;">SMS Service Id</th>
                                                <th style="width: 150px;">SMS Caller Id</th>
                                                <th style="width: 200px;">테스트</th>
                                                <th style="width: 100px;">설정</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="sms_naver_api_key" 
                                                           name="sms_naver_api_key" 
                                                           value="{{ $settings['sms_naver_api_key'] ?? '' }}" 
                                                           placeholder="API Key">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="sms_naver_api_secret" 
                                                           name="sms_naver_api_secret" 
                                                           value="{{ $settings['sms_naver_api_secret'] ?? '' }}" 
                                                           placeholder="API Secret">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="sms_naver_service_id" 
                                                           name="sms_naver_service_id" 
                                                           value="{{ $settings['sms_naver_service_id'] ?? '' }}" 
                                                           placeholder="Service ID">
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="sms_naver_caller_id" 
                                                           name="sms_naver_caller_id" 
                                                           value="{{ $settings['sms_naver_caller_id'] ?? '' }}" 
                                                           placeholder="Caller ID">
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" 
                                                               class="form-control phone-format" 
                                                               id="naver_test_phone" 
                                                               placeholder="010-1234-5678"
                                                               maxlength="13">
                                                        <button type="button" 
                                                                class="btn btn-outline-primary" 
                                                                onclick="testSms('naver_cloud')">
                                                            테스트
                                                        </button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-primary w-100" 
                                                            onclick="saveSmsSettings('naver_cloud')">
                                                        설정
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- 모바일 버전 (카드 레이아웃) --}}
                                <div class="d-md-none">
                                    <div class="d-grid gap-3">
                                        <div>
                                            <label class="form-label small fw-bold mb-1">API Key</label>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   id="sms_naver_api_key_mobile" 
                                                   name="sms_naver_api_key" 
                                                   value="{{ $settings['sms_naver_api_key'] ?? '' }}" 
                                                   placeholder="API Key">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-bold mb-1">API Secret Key</label>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   id="sms_naver_api_secret_mobile" 
                                                   name="sms_naver_api_secret" 
                                                   value="{{ $settings['sms_naver_api_secret'] ?? '' }}" 
                                                   placeholder="API Secret">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-bold mb-1">SMS Service Id</label>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   id="sms_naver_service_id_mobile" 
                                                   name="sms_naver_service_id" 
                                                   value="{{ $settings['sms_naver_service_id'] ?? '' }}" 
                                                   placeholder="Service ID">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-bold mb-1">SMS Caller Id</label>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   id="sms_naver_caller_id_mobile" 
                                                   name="sms_naver_caller_id" 
                                                   value="{{ $settings['sms_naver_caller_id'] ?? '' }}" 
                                                   placeholder="Caller ID">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-bold mb-1">테스트</label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control form-control-sm phone-format" 
                                                       id="naver_test_phone_mobile" 
                                                       placeholder="010-1234-5678"
                                                       maxlength="13">
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-sm" 
                                                        onclick="testSms('naver_cloud')">
                                                    테스트
                                                </button>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="button" 
                                                    class="btn btn-primary btn-sm" 
                                                    onclick="saveSmsSettings('naver_cloud')">
                                                설정
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <small class="text-muted d-block mt-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <a href="https://www.ncloud.com" target="_blank">네이버 클라우드 플랫폼</a>에서 SMS 서비스를 신청하세요.
                                </small>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 저장 성공 모달 -->
<div class="modal fade" id="saveSuccessModal" tabindex="-1" aria-labelledby="saveSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: transparent; border-bottom: none;">
                <h5 class="modal-title" id="saveSuccessModalLabel">저장 완료</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="saveSuccessModalBody">
                회원가입 설정이 저장되었습니다.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>

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
    const form = document.getElementById('registrationSettingsForm');
    const enableReferrer = document.getElementById('enable_referrer');
    const referrerSettings = document.getElementById('referrerSettings');
    
    // 전화번호 자동 포맷팅 적용
    document.querySelectorAll('.phone-format').forEach(function(input) {
        input.addEventListener('input', function(e) {
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
    });

    // 모바일과 데스크탑 입력 필드 동기화
    function syncInputFields(desktopId, mobileId) {
        const desktopInput = document.getElementById(desktopId);
        const mobileInput = document.getElementById(mobileId);
        
        if (desktopInput && mobileInput) {
            desktopInput.addEventListener('input', function() {
                mobileInput.value = this.value;
            });
            mobileInput.addEventListener('input', function() {
                desktopInput.value = this.value;
            });
        }
    }

    // Cool SMS 필드 동기화
    syncInputFields('sms_cool_api_key', 'sms_cool_api_key_mobile');
    syncInputFields('sms_cool_api_secret', 'sms_cool_api_secret_mobile');
    syncInputFields('sms_cool_from', 'sms_cool_from_mobile');
    syncInputFields('cool_test_phone', 'cool_test_phone_mobile');

    // 네이버 클라우드 필드 동기화
    syncInputFields('sms_naver_api_key', 'sms_naver_api_key_mobile');
    syncInputFields('sms_naver_api_secret', 'sms_naver_api_secret_mobile');
    syncInputFields('sms_naver_service_id', 'sms_naver_service_id_mobile');
    syncInputFields('sms_naver_caller_id', 'sms_naver_caller_id_mobile');
    syncInputFields('naver_test_phone', 'naver_test_phone_mobile');
    
    // 추천인 기능 활성화/비활성화에 따라 설정 영역 표시/숨김
    enableReferrer.addEventListener('change', function() {
        referrerSettings.style.display = this.checked ? 'block' : 'none';
    });
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            // 응답 상태 확인
            if (!response.ok) {
                throw new Error('서버 오류: ' + response.status);
            }
            
            // 응답이 JSON인지 확인
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // JSON이 아니면 텍스트로 읽기
                return response.text().then(text => {
                    // HTML 응답인 경우 (리다이렉트 등)
                    if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                        // 성공적으로 저장된 것으로 간주하고 페이지 새로고침
                        window.location.reload();
                        return { success: true, message: '저장되었습니다.' };
                    }
                    throw new Error('예상치 못한 응답 형식입니다.');
                });
            }
        })
        .then(data => {
            if (data && data.success) {
                // 성공 모달 표시
                const successModalElement = document.getElementById('saveSuccessModal');
                if (successModalElement) {
                    const successModal = new bootstrap.Modal(successModalElement);
                    document.getElementById('saveSuccessModalLabel').innerText = '저장 완료';
                    document.getElementById('saveSuccessModalBody').innerText = data.message || '회원가입 설정이 저장되었습니다.';
                    successModal.show();
                } else {
                    alert(data.message || '회원가입 설정이 저장되었습니다.');
                }
            } else {
                alert('저장 중 오류가 발생했습니다: ' + (data?.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다: ' + error.message);
        });
    });
});

// 전화번호 인증 설정 표시/숨김
function togglePhoneVerificationSettings() {
    const checkbox = document.getElementById('enable_phone_verification');
    const settings = document.getElementById('phoneVerificationSettings');
    settings.style.display = checkbox.checked ? 'block' : 'none';
}

// SMS 제공업체 설정 표시/숨김
function toggleSmsProviderSettings() {
    const provider = document.getElementById('sms_provider').value;
    
    document.getElementById('solapiSettings').style.display = 'none'; // SOLAPI는 더 이상 사용하지 않음
    document.getElementById('coolSmsSettings').style.display = provider === 'cool_sms' ? 'block' : 'none';
    document.getElementById('naverCloudSettings').style.display = provider === 'naver_cloud' ? 'block' : 'none';
    document.getElementById('twilioSettings').style.display = 'none'; // Twilio는 더 이상 사용하지 않음
}

// 소셜 로그인 설정 표시/숨김
function toggleSocialLoginSettings() {
    const enabled = document.getElementById('enable_social_login').checked;
    document.getElementById('socialLoginSettings').style.display = enabled ? 'block' : 'none';
}

// SMS 테스트
function testSms(provider) {
    let phoneInput, apiKeyInput, apiSecretInput, fromInput, serviceIdInput;
    
    if (provider === 'cool_sms') {
        // 모바일과 데스크탑 모두 지원
        phoneInput = document.getElementById('cool_test_phone') || document.getElementById('cool_test_phone_mobile');
        apiKeyInput = document.getElementById('sms_cool_api_key') || document.getElementById('sms_cool_api_key_mobile');
        apiSecretInput = document.getElementById('sms_cool_api_secret') || document.getElementById('sms_cool_api_secret_mobile');
        fromInput = document.getElementById('sms_cool_from') || document.getElementById('sms_cool_from_mobile');
    } else if (provider === 'naver_cloud') {
        // 모바일과 데스크탑 모두 지원
        phoneInput = document.getElementById('naver_test_phone') || document.getElementById('naver_test_phone_mobile');
        apiKeyInput = document.getElementById('sms_naver_api_key') || document.getElementById('sms_naver_api_key_mobile');
        apiSecretInput = document.getElementById('sms_naver_api_secret') || document.getElementById('sms_naver_api_secret_mobile');
        fromInput = document.getElementById('sms_naver_caller_id') || document.getElementById('sms_naver_caller_id_mobile');
        serviceIdInput = document.getElementById('sms_naver_service_id') || document.getElementById('sms_naver_service_id_mobile');
    }
    
    const phone = phoneInput.value.trim();
    if (!phone) {
        alert('테스트할 전화번호를 입력해주세요.');
        return;
    }
    
    const apiKey = apiKeyInput ? apiKeyInput.value.trim() : '';
    const apiSecret = apiSecretInput ? apiSecretInput.value.trim() : '';
    const from = fromInput ? fromInput.value.trim() : '';
    
    if (!apiKey || !apiSecret || !from) {
        alert('API Key, API Secret, 발신번호를 모두 입력해주세요.');
        return;
    }
    
    const btn = phoneInput.nextElementSibling;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '전송 중...';
    
    const requestBody = {
        provider: provider,
        phone: phone,
        api_key: apiKey,
        api_secret: apiSecret,
        from: from
    };
    
    if (provider === 'naver_cloud' && serviceIdInput) {
        requestBody.service_id = serviceIdInput.value.trim();
    }
    
    fetch('{{ $site->isMasterSite() ? route("master.admin.registration-settings.test-sms") : route("admin.registration-settings.test-sms", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(requestBody)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('테스트 SMS가 발송되었습니다. 전화번호를 확인해주세요.');
        } else {
            alert('SMS 발송에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
        btn.disabled = false;
        btn.textContent = originalText;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('SMS 발송 중 오류가 발생했습니다.');
        btn.disabled = false;
        btn.textContent = originalText;
    });
}

// SMS 설정 저장
function saveSmsSettings(provider) {
    const form = document.getElementById('registrationSettingsForm');
    const formData = new FormData(form);
    formData.append('sms_provider', provider);
    
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
            alert('SMS 설정이 저장되었습니다.');
        } else {
            alert('저장 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다.');
    });
}
</script>
@endpush
@endsection

