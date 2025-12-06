@extends('layouts.master')

@section('title', '지도 API 설정')
@section('page-title', '지도 API 설정')
@section('page-subtitle', '구글, 네이버, 카카오 지도 API 키를 설정합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>지도 API 키 설정</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('master.map-api.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- 구글 지도 API -->
                    <div class="mb-4">
                        <label for="google_api_key" class="form-label">
                            <i class="bi bi-google me-2 text-danger"></i>구글 지도 API 키
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="google_api_key" 
                               name="google_api_key" 
                               value="{{ old('google_api_key', $googleApiKey) }}"
                               placeholder="구글 지도 API 키를 입력하세요">
                        <small class="text-muted">
                            <a href="https://console.cloud.google.com/google/maps-apis" target="_blank" class="text-decoration-none">
                                구글 클라우드 콘솔에서 API 키 발급받기
                            </a>
                        </small>
                    </div>

                    <!-- 네이버 지도 API -->
                    <div class="mb-4">
                        <label for="naver_api_key" class="form-label">
                            <i class="bi bi-geo me-2 text-success"></i>네이버 지도 API 키 (Client ID)
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="naver_api_key" 
                               name="naver_api_key" 
                               value="{{ old('naver_api_key', $naverApiKey) }}"
                               placeholder="네이버 지도 API Client ID를 입력하세요">
                        <small class="text-muted">
                            <a href="https://www.ncloud.com/product/applicationService/maps" target="_blank" class="text-decoration-none">
                                네이버 클라우드 플랫폼에서 API 키 발급받기
                            </a>
                        </small>
                    </div>

                    <!-- 카카오 지도 API -->
                    <div class="mb-4">
                        <label for="kakao_api_key" class="form-label">
                            <i class="bi bi-geo-fill me-2 text-warning"></i>카카오 지도 API 키 (JavaScript 키)
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="kakao_api_key" 
                               name="kakao_api_key" 
                               value="{{ old('kakao_api_key', $kakaoApiKey) }}"
                               placeholder="카카오 지도 JavaScript 키를 입력하세요">
                        <small class="text-muted">
                            <a href="https://developers.kakao.com/console/app" target="_blank" class="text-decoration-none">
                                카카오 개발자 콘솔에서 API 키 발급받기
                            </a>
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>안내:</strong> 각 지도 서비스의 API 키를 설정하면, 사이트 운영자들이 해당 지도 서비스를 선택하여 사용할 수 있습니다. 
                        API 키가 설정되지 않은 지도 서비스는 선택할 수 없습니다.
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>네이버 지도 API 주의사항:</strong> 네이버 지도 API는 도메인 등록이 필요합니다. 
                        각 사이트의 도메인을 네이버 클라우드 플랫폼의 Application에 등록해야 정상적으로 작동합니다. 
                        와일드카드 도메인(<code>*.yourdomain.com</code>)을 사용하거나, 각 사이트별로 개별 도메인을 등록하세요.
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

