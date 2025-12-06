@extends('layouts.master')

@section('title', '구독 설정')
@section('page-title', '구독 설정')
@section('page-subtitle', '무료 기간 및 토스 페이먼츠 설정을 관리합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>구독 설정</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('master.subscription-settings.update') }}">
                    @csrf
                    @method('POST')
                    
                    <!-- 무료 기간 설정 -->
                    <div class="mb-4">
                        <label for="trial_days" class="form-label">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>무료 기간 (일)
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="trial_days" 
                               name="trial_days" 
                               value="{{ old('trial_days', $trialDays) }}"
                               min="0"
                               max="30"
                               required>
                        <small class="text-muted">
                            운영자가 회원가입 후 홈페이지를 만들었을 때 제공되는 무료 기간입니다. (0~30일)
                        </small>
                    </div>

                    <!-- 토스 페이먼츠 Client Key -->
                    <div class="mb-4">
                        <label for="toss_client_key" class="form-label">
                            <i class="bi bi-key me-2 text-primary"></i>토스 페이먼츠 Client Key <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="toss_client_key" 
                               name="toss_client_key" 
                               value="{{ old('toss_client_key', $tossClientKey ?? '') }}"
                               placeholder="토스 페이먼츠 Client Key를 입력하세요">
                        <small class="text-muted">
                            결제 위젯에 사용되는 클라이언트 키입니다. 
                            <a href="https://developers.tosspayments.com/" target="_blank" class="text-decoration-none">
                                토스 페이먼츠 개발자 센터에서 API 키 발급받기
                            </a>
                        </small>
                    </div>

                    <!-- 토스 페이먼츠 Secret Key -->
                    <div class="mb-4">
                        <label for="toss_secret_key" class="form-label">
                            <i class="bi bi-key me-2 text-success"></i>토스 페이먼츠 Secret Key <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="toss_secret_key" 
                               name="toss_secret_key" 
                               value="{{ old('toss_secret_key', $tossSecretKey) }}"
                               placeholder="토스 페이먼츠 Secret Key를 입력하세요">
                        <small class="text-muted">
                            서버 사이드 결제 승인에 사용되는 시크릿 키입니다.
                        </small>
                    </div>

                    <!-- 토스 페이먼츠 Base URL -->
                    <div class="mb-4">
                        <label for="toss_base_url" class="form-label">
                            <i class="bi bi-link-45deg me-2 text-info"></i>토스 페이먼츠 Base URL
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="toss_base_url" 
                               name="toss_base_url" 
                               value="{{ old('toss_base_url', $tossBaseUrl) }}"
                               placeholder="https://api.tosspayments.com">
                        <small class="text-muted">
                            토스 페이먼츠 API의 기본 URL입니다. (기본값: https://api.tosspayments.com)
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>안내:</strong> 무료 기간이 지나면 자동으로 정기결제가 진행됩니다. 
                        결제 실패 시 3일 동안 재시도하며, 계속 실패하면 서비스가 일시 중지됩니다.
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>결제 알림:</strong> 결제 7일전, 3일전, 1일전에 운영자 이메일로 알림이 전송됩니다. 
                        결제 실패 시에도 알림이 전송됩니다.
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
@endsection

