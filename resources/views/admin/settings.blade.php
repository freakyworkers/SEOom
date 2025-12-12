@extends('layouts.admin')

@section('title', '사이트 설정')
@section('page-title', '사이트 설정')
@section('page-subtitle', '사이트 정보와 기본 설정을 변경할 수 있습니다')

@push('styles')
<style>
    .settings-section {
        margin-bottom: 2rem;
    }
    .settings-section:last-child {
        margin-bottom: 0;
    }
    .theme-preview {
        position: relative;
    }
    .image-upload-area {
        position: relative;
        width: 100%;
        min-height: 120px;
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s;
    }
    .image-upload-area:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    .image-upload-area.has-image {
        border: 2px solid #dee2e6;
        padding: 0.5rem;
        display: block;
    }
    .image-upload-area.has-image .image-preview {
        display: block !important;
        max-width: 100%;
        max-height: 120px;
        width: auto;
        height: auto;
        margin: 0 auto;
        border-radius: 0.25rem;
        cursor: pointer;
    }
    .image-preview {
        max-width: 100%;
        max-height: 120px;
        border-radius: 0.25rem;
        cursor: pointer;
        display: block;
    }
    .image-preview:hover {
        opacity: 0.8;
    }
    .image-upload-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        color: #6c757d;
    }
    .image-upload-btn i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .help-icon {
        cursor: help;
        color: #6c757d;
        margin-left: 0.25rem;
    }
    .help-icon:hover {
        color: #0d6efd;
    }
    .logo-settings-table {
        background-color: white;
        min-width: 800px;
    }
    .logo-settings-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
    .logo-settings-table td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    .mobile-preview-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .mobile-preview-wrapper::-webkit-scrollbar {
        height: 8px;
    }
    .mobile-preview-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .mobile-preview-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .mobile-preview-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    @media (max-width: 768px) {
        .mobile-preview-wrapper {
            margin: 0;
            padding: 0;
            width: 100%;
            overflow: hidden;
            position: relative;
            height: 160px; /* 축소된 높이: 667px * 0.24 = 160px */
        }
        #mobile_header_preview {
            width: 375px !important;
            max-width: 375px !important;
            transform: scale(0.76);
            transform-origin: left top;
            margin: 0;
        }
        .col-md-8 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .row {
            margin-left: 0;
            margin-right: 0;
        }
        .row > * {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .card-body {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    }
    .size-input {
        width: 100px;
    }
    .hidden-file-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }
    .image-upload-area.has-image .image-preview {
        position: relative;
        z-index: 1;
        display: block;
        width: 100%;
        height: auto;
    }
    .theme-preview {
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #f8f9fa;
        overflow: hidden;
        position: relative;
    }
    .theme-preview-container {
        overflow: hidden;
        width: 100%;
        position: relative;
    }
    .theme-preview-wrapper {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }
    .theme-preview-wrapper::-webkit-scrollbar {
        height: 8px;
    }
    .theme-preview-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .theme-preview-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .theme-preview-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    @media (max-width: 768px) {
        .theme-preview-wrapper {
            margin: 0;
            padding: 0;
            overflow: hidden;
            width: 100%;
            position: relative;
            height: 60px; /* 축소된 높이: 200px * 0.23 = 46px, 여유를 두고 60px */
        }
        .theme-preview {
            width: 1200px;
            transform: scale(0.23);
            transform-origin: left top;
            height: 200px;
            display: block;
        }
        .theme-preview-container {
            width: 1200px;
            transform: scale(1);
        }
        /* 미리보기 내부 요소들이 항상 데스크탑 레이아웃으로 표시되도록 */
        .theme-preview .navbar {
            flex-wrap: nowrap !important;
        }
        .theme-preview .container-fluid {
            min-width: 1200px !important;
            display: flex !important;
            flex-wrap: nowrap !important;
        }
        .theme-preview .navbar-nav {
            flex-direction: row !important;
            flex-wrap: nowrap !important;
        }
        .theme-preview .navbar-brand {
            white-space: nowrap !important;
        }
    }
    .header-preview-wrapper {
        transform: scale(0.75);
        transform-origin: top left;
        width: 133.33%;
        margin-bottom: -25%;
    }
    .footer-preview-wrapper {
        transform: scale(0.75);
        transform-origin: top left;
        width: 133.33%;
        margin-bottom: -25%;
    }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.settings.update', ['site' => $site->slug]) }}" id="settingsForm">
    @csrf
    @method('PUT')
    
    <!-- 기본 설정 -->
    <div class="card shadow-sm settings-section">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>기본 설정</h5>
    </div>
    <div class="card-body">
        <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="site_name" class="form-label">사이트 이름</label>
                    <input type="text" 
                           class="form-control @error('site_name') is-invalid @enderror" 
                           id="site_name" 
                           name="site_name" 
                           value="{{ old('site_name', $settings['site_name'] ?? $site->name) }}">
                    @error('site_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="site_keywords" class="form-label">사이트 키워드</label>
                    <input type="text" 
                           class="form-control @error('site_keywords') is-invalid @enderror" 
                           id="site_keywords" 
                           name="site_keywords" 
                           value="{{ old('site_keywords', $settings['site_keywords'] ?? '') }}"
                           placeholder="키워드1, 키워드2, 키워드3">
                    <small class="form-text text-muted">쉼표로 구분하여 입력하세요.</small>
                    @error('site_keywords')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="site_description" class="form-label">사이트 설명</label>
                <textarea class="form-control @error('site_description') is-invalid @enderror" 
                          id="site_description" 
                          name="site_description" 
                          rows="3">{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
                @error('site_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr class="my-4">

            <h6 class="mb-3"><i class="bi bi-search me-2"></i>검색 엔진 등록</h6>
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>안내:</strong> 각 검색 엔진에서 제공하는 인증 코드를 입력하세요. 입력한 메타 태그는 사이트의 <code>&lt;head&gt;</code> 태그에 자동으로 삽입됩니다.
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="google_site_verification" class="form-label">
                        <i class="bi bi-google me-1"></i>구글 서치콘솔
                    </label>
                    <input type="text" 
                           class="form-control @error('google_site_verification') is-invalid @enderror" 
                           id="google_site_verification" 
                           name="google_site_verification" 
                           value="{{ old('google_site_verification', $settings['google_site_verification'] ?? '') }}"
                           placeholder="인증 코드 입력">
                    <small class="form-text text-muted">구글 서치콘솔에서 제공하는 인증 코드를 입력하세요.</small>
                    @error('google_site_verification')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="naver_site_verification" class="form-label">
                        <i class="bi bi-globe me-1"></i>네이버 서치어드바이저
                    </label>
                    <input type="text" 
                           class="form-control @error('naver_site_verification') is-invalid @enderror" 
                           id="naver_site_verification" 
                           name="naver_site_verification" 
                           value="{{ old('naver_site_verification', $settings['naver_site_verification'] ?? '') }}"
                           placeholder="인증 코드 입력">
                    <small class="form-text text-muted">네이버 서치어드바이저에서 제공하는 인증 코드를 입력하세요.</small>
                    @error('naver_site_verification')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="daum_site_verification" class="form-label">
                        <i class="bi bi-globe me-1"></i>다음 검색 등록
                    </label>
                    <input type="text" 
                           class="form-control @error('daum_site_verification') is-invalid @enderror" 
                           id="daum_site_verification" 
                           name="daum_site_verification" 
                           value="{{ old('daum_site_verification', $settings['daum_site_verification'] ?? '') }}"
                           placeholder="인증 코드 입력">
                    <small class="form-text text-muted">다음 검색 등록에서 제공하는 인증 코드를 입력하세요.</small>
                    @error('daum_site_verification')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <h6 class="mb-3">구글</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="google_analytics_id" class="form-label">애널리틱스</label>
                    <input type="text" 
                           class="form-control @error('google_analytics_id') is-invalid @enderror" 
                           id="google_analytics_id" 
                           name="google_analytics_id" 
                           value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}"
                           placeholder="G-XXXXXXXXXX">
                    <small class="form-text text-muted">구글 애널리틱스 측정 ID를 입력하세요.</small>
                    @error('google_analytics_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="adsense_ads_txt" class="form-label">애드센스 ads.txt</label>
                    <textarea class="form-control @error('adsense_ads_txt') is-invalid @enderror" 
                              id="adsense_ads_txt" 
                              name="adsense_ads_txt" 
                              rows="4"
                              placeholder="google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0">{{ old('adsense_ads_txt', $settings['adsense_ads_txt'] ?? '') }}</textarea>
                    <small class="form-text text-muted">애드센스 ads.txt 내용을 입력하세요.</small>
                    @error('adsense_ads_txt')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <h6 class="mb-3"><i class="bi bi-file-text me-2"></i>로봇 텍스트 (robots.txt)</h6>
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>안내:</strong> robots.txt는 검색 엔진 크롤러에게 사이트 크롤링 규칙을 알려주는 파일입니다. 기본값이 제공되며, 필요시 수정할 수 있습니다.
            </div>
            
            <div class="mb-3">
                <label for="robots_txt" class="form-label">robots.txt 내용</label>
                @php
                    $defaultRobotsTxt = "User-agent: *\nAllow: /\n\nSitemap: " . route('sitemap', ['site' => $site->slug]);
                    $robotsTxtValue = old('robots_txt', $settings['robots_txt'] ?? $defaultRobotsTxt);
                @endphp
                <textarea class="form-control @error('robots_txt') is-invalid @enderror" 
                          id="robots_txt" 
                          name="robots_txt" 
                          rows="8"
                          placeholder="필요한 경우 수정할 수 있습니다.">{{ $robotsTxtValue }}</textarea>
                <small class="form-text text-muted">사이트맵 URL을 포함한 기본값이 자동으로 설정되었습니다.</small>
                @error('robots_txt')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('robots', ['site' => $site->slug]) }}" 
                       class="btn btn-sm btn-outline-primary" 
                       target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i>robots.txt 보기
                    </a>
                    <a href="{{ route('sitemap', ['site' => $site->slug]) }}" 
                       class="btn btn-sm btn-outline-primary" 
                       target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i>사이트맵 보기
                    </a>
                    <a href="{{ route('rss', ['site' => $site->slug]) }}" 
                       class="btn btn-sm btn-outline-primary" 
                       target="_blank">
                        <i class="bi bi-rss me-1"></i>RSS 피드 보기
                    </a>
                </div>
            </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" form="settingsForm" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>기본 설정 저장
            </button>
        </div>
    </div>
</div>

<!-- 로고 설정 -->
<div class="card shadow-sm settings-section">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-image me-2"></i>로고</h5>
    </div>
    <div class="card-body">
        <!-- 데스크탑 테이블 뷰 -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-bordered logo-settings-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 100px;">로고</th>
                        <th>타입</th>
                        <th>이미지</th>
                        <th>이미지 (다크모드)</th>
                        <th>데스크탑 사이즈</th>
                        <th>모바일 사이즈</th>
                        <th>파비콘 <i class="bi bi-question-circle help-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="파비콘은 브라우저 탭에 표시되는 작은 아이콘입니다. 권장 사이즈: 32x32px 또는 16x16px (ICO, PNG 형식)"></i></th>
                        <th>OG 이미지 <i class="bi bi-question-circle help-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="OG(Open Graph) 이미지는 소셜 미디어 공유 시 표시되는 이미지입니다. 권장 사이즈: 1200x630px (PNG, JPG 형식)"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="2" class="text-center align-middle">
                            <strong>로고</strong>
                        </td>
                        <td>
                            <select class="form-select form-select-sm" name="logo_type" id="logo_type">
                                <option value="image" {{ ($settings['logo_type'] ?? 'image') === 'image' ? 'selected' : '' }}>이미지</option>
                                <option value="text" {{ ($settings['logo_type'] ?? '') === 'text' ? 'selected' : '' }}>텍스트</option>
                            </select>
                            <div id="logo-text-notice" class="alert alert-info mt-2 mb-0" style="display: none;">
                                <i class="bi bi-info-circle me-1"></i>사이트 이름이 로고로 표시됩니다.
                            </div>
                        </td>
                        <td>
                            <div class="image-upload-area {{ !empty($settings['site_logo'] ?? '') ? 'has-image' : '' }}" 
                                 data-type="logo" 
                                 data-input="site_logo">
                                @if(!empty($settings['site_logo'] ?? ''))
                                    <img src="{{ $settings['site_logo'] }}" alt="로고" class="image-preview">
                                @else
                                    <div class="image-upload-btn">
                                        <i class="bi bi-cloud-upload"></i>
                                        <span>업로드</span>
                                    </div>
                                @endif
                                <input type="file" class="hidden-file-input" accept="image/*" data-type="logo">
                                <input type="hidden" name="site_logo" id="site_logo" value="{{ $settings['site_logo'] ?? '' }}">
                            </div>
                        </td>
                        <td>
                            <div class="image-upload-area {{ !empty($settings['site_logo_dark'] ?? '') ? 'has-image' : '' }}" 
                                 data-type="logo_dark" 
                                 data-input="site_logo_dark">
                                @if(!empty($settings['site_logo_dark'] ?? ''))
                                    <img src="{{ $settings['site_logo_dark'] }}" alt="로고 (다크모드)" class="image-preview">
                                @else
                                    <div class="image-upload-btn">
                                        <i class="bi bi-cloud-upload"></i>
                                        <span>업로드</span>
                                    </div>
                                @endif
                                <input type="file" class="hidden-file-input" accept="image/*" data-type="logo_dark">
                                <input type="hidden" name="site_logo_dark" id="site_logo_dark" value="{{ $settings['site_logo_dark'] ?? '' }}">
                            </div>
                        </td>
                        <td>
                            <input type="number" 
                                   class="form-control form-control-sm size-input" 
                                   name="logo_desktop_size" 
                                   id="logo_desktop_size" 
                                   value="{{ old('logo_desktop_size', $settings['logo_desktop_size'] ?? '300') }}"
                                   min="50" 
                                   max="1000">
                            <small class="text-muted">px</small>
                        </td>
                        <td>
                            <input type="number" 
                                   class="form-control form-control-sm size-input" 
                                   name="logo_mobile_size" 
                                   id="logo_mobile_size" 
                                   value="{{ old('logo_mobile_size', $settings['logo_mobile_size'] ?? '200') }}"
                                   min="50" 
                                   max="1000">
                            <small class="text-muted">px</small>
                        </td>
                        <td>
                            <div class="image-upload-area {{ !empty($settings['site_favicon'] ?? '') ? 'has-image' : '' }}" 
                                 data-type="favicon" 
                                 data-input="site_favicon"
                                 style="min-height: 60px;">
                                @if(!empty($settings['site_favicon'] ?? ''))
                                    <img src="{{ $settings['site_favicon'] }}" alt="파비콘" class="image-preview" style="max-height: 60px;">
                                @else
                                    <div class="image-upload-btn" style="padding: 0.5rem;">
                                        <i class="bi bi-cloud-upload"></i>
                                        <span style="font-size: 0.75rem;">업로드</span>
                                    </div>
                                @endif
                                <input type="file" class="hidden-file-input" accept="image/*,.ico" data-type="favicon">
                                <input type="hidden" name="site_favicon" id="site_favicon" value="{{ $settings['site_favicon'] ?? '' }}">
                            </div>
                        </td>
                        <td>
                            <div class="image-upload-area {{ !empty($settings['og_image'] ?? '') ? 'has-image' : '' }}" 
                                 data-type="og_image" 
                                 data-input="og_image">
                                @if(!empty($settings['og_image'] ?? ''))
                                    <img src="{{ $settings['og_image'] }}" alt="OG 이미지" class="image-preview">
                                @else
                                    <div class="image-upload-btn">
                                        <i class="bi bi-cloud-upload"></i>
                                        <span>업로드</span>
                                    </div>
                                @endif
                                <input type="file" class="hidden-file-input" accept="image/*" data-type="og_image">
                                <input type="hidden" name="og_image" id="og_image" value="{{ $settings['og_image'] ?? '' }}">
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 모바일 카드 뷰 -->
        <div class="d-md-none">
            <!-- 로고 카드 -->
            <div class="card mb-3 border">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>로고</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">타입</label>
                        <select class="form-select" id="logo_type_mobile">
                            <option value="image" {{ ($settings['logo_type'] ?? 'image') === 'image' ? 'selected' : '' }}>이미지</option>
                            <option value="text" {{ ($settings['logo_type'] ?? '') === 'text' ? 'selected' : '' }}>텍스트</option>
                        </select>
                        <div id="logo-text-notice-mobile" class="alert alert-info mt-2 mb-0" style="display: none;">
                            <i class="bi bi-info-circle me-1"></i>사이트 이름이 로고로 표시됩니다.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">이미지</label>
                        <div class="image-upload-area {{ !empty($settings['site_logo'] ?? '') ? 'has-image' : '' }}" 
                             data-type="logo" 
                             data-input="site_logo">
                            @if(!empty($settings['site_logo'] ?? ''))
                                <img src="{{ $settings['site_logo'] }}" alt="로고" class="image-preview">
                            @else
                                <div class="image-upload-btn">
                                    <i class="bi bi-cloud-upload"></i>
                                    <span>업로드</span>
                                </div>
                            @endif
                            <input type="file" class="hidden-file-input" accept="image/*" data-type="logo">
                            <input type="hidden" name="site_logo" id="site_logo_mobile" value="{{ $settings['site_logo'] ?? '' }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">이미지 (다크모드)</label>
                        <div class="image-upload-area {{ !empty($settings['site_logo_dark'] ?? '') ? 'has-image' : '' }}" 
                             data-type="logo_dark" 
                             data-input="site_logo_dark">
                            @if(!empty($settings['site_logo_dark'] ?? ''))
                                <img src="{{ $settings['site_logo_dark'] }}" alt="로고 (다크모드)" class="image-preview">
                            @else
                                <div class="image-upload-btn">
                                    <i class="bi bi-cloud-upload"></i>
                                    <span>업로드</span>
                                </div>
                            @endif
                            <input type="file" class="hidden-file-input" accept="image/*" data-type="logo_dark">
                            <input type="hidden" name="site_logo_dark" id="site_logo_dark_mobile" value="{{ $settings['site_logo_dark'] ?? '' }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label fw-bold">데스크탑 사이즈</label>
                            <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="logo_desktop_size_mobile"
                                       value="{{ old('logo_desktop_size', $settings['logo_desktop_size'] ?? '300') }}"
                                       min="50" 
                                       max="1000">
                                <span class="input-group-text">px</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">모바일 사이즈</label>
                            <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="logo_mobile_size_mobile"
                                       value="{{ old('logo_mobile_size', $settings['logo_mobile_size'] ?? '200') }}"
                                       min="50" 
                                       max="1000">
                                <span class="input-group-text">px</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 파비콘 카드 -->
            <div class="card mb-3 border">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-star me-2"></i>파비콘
                        <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="파비콘은 브라우저 탭에 표시되는 작은 아이콘입니다. 권장 사이즈: 32x32px 또는 16x16px (ICO, PNG 형식)"></i>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="image-upload-area {{ !empty($settings['site_favicon'] ?? '') ? 'has-image' : '' }}" 
                         data-type="favicon" 
                         data-input="site_favicon"
                         style="min-height: 120px;">
                        @if(!empty($settings['site_favicon'] ?? ''))
                            <img src="{{ $settings['site_favicon'] }}" alt="파비콘" class="image-preview" style="max-height: 120px;">
                        @else
                            <div class="image-upload-btn">
                                <i class="bi bi-cloud-upload"></i>
                                <span>업로드</span>
                            </div>
                        @endif
                        <input type="file" class="hidden-file-input" accept="image/*,.ico" data-type="favicon">
                        <input type="hidden" name="site_favicon" id="site_favicon_mobile" value="{{ $settings['site_favicon'] ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- OG 이미지 카드 -->
            <div class="card mb-3 border">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-share me-2"></i>OG 이미지
                        <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="OG(Open Graph) 이미지는 소셜 미디어 공유 시 표시되는 이미지입니다. 권장 사이즈: 1200x630px (PNG, JPG 형식)"></i>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="image-upload-area {{ !empty($settings['og_image'] ?? '') ? 'has-image' : '' }}" 
                         data-type="og_image" 
                         data-input="og_image">
                        @if(!empty($settings['og_image'] ?? ''))
                            <img src="{{ $settings['og_image'] }}" alt="OG 이미지" class="image-preview">
                        @else
                            <div class="image-upload-btn">
                                <i class="bi bi-cloud-upload"></i>
                                <span>업로드</span>
                            </div>
                        @endif
                        <input type="file" class="hidden-file-input" accept="image/*" data-type="og_image">
                        <input type="hidden" name="og_image" id="og_image_mobile" value="{{ $settings['og_image'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" form="settingsForm" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>로고 설정 저장
            </button>
        </div>
    </div>
</div>

<!-- 테마 설정 -->
<div class="card shadow-sm settings-section">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-palette me-2"></i>테마</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update', ['site' => $site->slug]) }}" id="themeForm">
            @csrf
            @method('PUT')
            
            <!-- 다크모드, 메인, 사이드바 -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="theme_dark_mode" class="form-label">다크모드</label>
                    <select class="form-select" name="theme_dark_mode" id="theme_dark_mode">
                        <option value="light" {{ ($settings['theme_dark_mode'] ?? 'light') === 'light' ? 'selected' : '' }}>라이트</option>
                        <option value="dark" {{ ($settings['theme_dark_mode'] ?? '') === 'dark' ? 'selected' : '' }}>다크</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="theme_main" class="form-label">메인</label>
                    <select class="form-select" name="theme_main" id="theme_main">
                        <option value="round" {{ ($settings['theme_main'] ?? 'round') === 'round' ? 'selected' : '' }}>라운드</option>
                        <option value="square" {{ ($settings['theme_main'] ?? 'round') === 'square' ? 'selected' : '' }}>스퀘어</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="theme_sidebar" class="form-label">사이드바</label>
                    @php
                        $hasSidebarWidgets = $site->hasFeature('sidebar_widgets');
                        $sidebarValue = $hasSidebarWidgets ? ($settings['theme_sidebar'] ?? 'left') : 'none';
                    @endphp
                    <select class="form-select" 
                            name="theme_sidebar" 
                            id="theme_sidebar"
                            {{ !$hasSidebarWidgets ? 'disabled' : '' }}>
                        <option value="left" {{ $sidebarValue === 'left' ? 'selected' : '' }}>좌측</option>
                        <option value="right" {{ $sidebarValue === 'right' ? 'selected' : '' }}>우측</option>
                        <option value="none" {{ $sidebarValue === 'none' ? 'selected' : '' }}>없음</option>
                    </select>
                    @if(!$hasSidebarWidgets)
                        <input type="hidden" name="theme_sidebar" value="none">
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-info-circle me-1"></i>
                            현재 플랜에서는 사이드 위젯 기능을 사용할 수 없어 사이드바가 비활성화됩니다.
                        </small>
                    @endif
                </div>
            </div>
            
            <!-- 메뉴 폰트 설정 -->
            <div class="mb-4">
                <h5 class="mb-3">메뉴 폰트 설정</h5>
                <div class="row">
                    <div class="col-md-4">
                        <label for="menu_font_size" class="form-label">폰트 사이즈</label>
                        <select class="form-select theme-preview-select" name="menu_font_size" id="menu_font_size" data-type="header">
                            <option value="0.75rem" {{ ($settings['menu_font_size'] ?? '1.25rem') === '0.75rem' ? 'selected' : '' }}>12px</option>
                            <option value="0.875rem" {{ ($settings['menu_font_size'] ?? '1.25rem') === '0.875rem' ? 'selected' : '' }}>14px</option>
                            <option value="1rem" {{ ($settings['menu_font_size'] ?? '1.25rem') === '1rem' ? 'selected' : '' }}>16px</option>
                            <option value="1.125rem" {{ ($settings['menu_font_size'] ?? '1.25rem') === '1.125rem' ? 'selected' : '' }}>18px</option>
                            <option value="1.25rem" {{ ($settings['menu_font_size'] ?? '1.25rem') === '1.25rem' ? 'selected' : '' }}>20px</option>
                            <option value="1.5rem" {{ ($settings['menu_font_size'] ?? '1.25rem') === '1.5rem' ? 'selected' : '' }}>24px</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="menu_font_padding" class="form-label">메뉴 간격</label>
                        <select class="form-select theme-preview-select" name="menu_font_padding" id="menu_font_padding" data-type="header">
                            <option value="0.25rem" {{ ($settings['menu_font_padding'] ?? '0.5rem') === '0.25rem' ? 'selected' : '' }}>4px</option>
                            <option value="0.5rem" {{ ($settings['menu_font_padding'] ?? '0.5rem') === '0.5rem' ? 'selected' : '' }}>8px</option>
                            <option value="0.75rem" {{ ($settings['menu_font_padding'] ?? '0.5rem') === '0.75rem' ? 'selected' : '' }}>12px</option>
                            <option value="1rem" {{ ($settings['menu_font_padding'] ?? '0.5rem') === '1rem' ? 'selected' : '' }}>16px</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="menu_font_weight" class="form-label">폰트 두께</label>
                        <select class="form-select theme-preview-select" name="menu_font_weight" id="menu_font_weight" data-type="header">
                            <option value="300" {{ (isset($settings['menu_font_weight']) && (string)$settings['menu_font_weight'] === '300') ? 'selected' : '' }}>얇음</option>
                            <option value="400" {{ (isset($settings['menu_font_weight']) && (string)$settings['menu_font_weight'] === '400') ? 'selected' : '' }}>보통</option>
                            <option value="700" {{ (!isset($settings['menu_font_weight']) || (string)$settings['menu_font_weight'] === '700') ? 'selected' : '' }}>볼드</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- 상단 헤더 -->
            <div class="mb-4">
                <div class="row align-items-start">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <label for="theme_top" class="form-label mb-0 me-2">상단</label>
                            <i class="bi bi-question-circle help-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="상단 헤더 테마를 선택합니다"></i>
                        </div>
                        <select class="form-select theme-preview-select" name="theme_top" id="theme_top" data-type="header">
                            <option value="design1" {{ ($settings['theme_top'] ?? 'design1') === 'design1' ? 'selected' : '' }}>디자인1</option>
                            <option value="design2" {{ ($settings['theme_top'] ?? 'design1') === 'design2' ? 'selected' : '' }}>디자인2</option>
                            <option value="design3" {{ ($settings['theme_top'] ?? 'design1') === 'design3' ? 'selected' : '' }}>디자인3</option>
                            <option value="design4" {{ ($settings['theme_top'] ?? 'design1') === 'design4' ? 'selected' : '' }}>디자인4</option>
                            <option value="design5" {{ ($settings['theme_top'] ?? 'design1') === 'design5' ? 'selected' : '' }}>디자인5</option>
                            <option value="design6" {{ ($settings['theme_top'] ?? 'design1') === 'design6' ? 'selected' : '' }}>디자인6</option>
                        </select>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="theme_top_header_show" id="theme_top_header_show" value="1" {{ (isset($settings['theme_top_header_show']) && $settings['theme_top_header_show'] == '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="theme_top_header_show">
                                최상단 헤더 표시
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="top_header_login_show" id="top_header_login_show" value="1" {{ (isset($settings['top_header_login_show']) && $settings['top_header_login_show'] == '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="top_header_login_show">
                                최상단 헤더 로그인 버튼
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="header_sticky" id="header_sticky" value="1" {{ (isset($settings['header_sticky']) && $settings['header_sticky'] == '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="header_sticky">
                                헤더 고정
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="theme_full_width" id="theme_full_width" value="1" {{ (isset($settings['theme_full_width']) && $settings['theme_full_width'] == '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="theme_full_width">
                                가로100%
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <div class="d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" name="menu_login_show" id="menu_login_show" value="1" {{ (isset($settings['menu_login_show']) && $settings['menu_login_show'] == '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_login_show">
                                    메뉴 로그인 표시
                                </label>
                                <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="활성화 시 검색창이 있는 헤더 디자인(2, 3, 4번)에서는 검색창 위치에 로그인 버튼이 표시됩니다."></i>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="header_shadow" id="header_shadow" value="1" {{ (isset($settings['header_shadow']) && $settings['header_shadow'] == '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="header_shadow">
                                그림자
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="header_border" id="header_border" value="1" {{ (isset($settings['header_border']) && $settings['header_border'] == '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="header_border">
                                헤더 테두리
                            </label>
                        </div>
                        <div id="header_border_settings" class="mt-2 ms-4" style="display: {{ (isset($settings['header_border']) && $settings['header_border'] == '1') ? 'block' : 'none' }};">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <label for="header_border_width" class="form-label mb-0">두께:</label>
                                <select class="form-select form-select-sm" name="header_border_width" id="header_border_width" style="width: auto;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ (isset($settings['header_border_width']) && $settings['header_border_width'] == $i) ? 'selected' : '' }}>{{ $i }}px</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label for="header_border_color" class="form-label mb-0">컬러:</label>
                                <input type="color" class="form-control form-control-color form-control-sm" name="header_border_color" id="header_border_color" value="{{ $settings['header_border_color'] ?? '#dee2e6' }}" title="헤더 테두리 색상" style="width: 50px; height: 38px;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-2" style="height: 24px;">
                            <label class="form-label mb-0">미리보기</label>
                        </div>
                        <div class="theme-preview-wrapper">
                            <div id="theme_top_preview" class="theme-preview" style="height: 200px; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; background-color: #f8f9fa; overflow: hidden; position: relative;">
                                <div class="theme-preview-container" style="position: relative; width: 100%; height: 100%;">
                                    @if(isset($headerPreviewHtml) && !empty($headerPreviewHtml))
                                        {!! $headerPreviewHtml !!}
                                    @else
                                        <div class="text-muted p-3">
                                            미리보기를 불러올 수 없습니다.
                                            @if(config('app.debug'))
                                                <br><small>Debug: headerPreviewHtml is not set</small>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 하단 푸터 -->
            <div class="mb-4">
                <div class="row align-items-start">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <label for="theme_bottom" class="form-label mb-0 me-2">하단</label>
                            <i class="bi bi-question-circle help-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="푸터 디자인을 선택합니다"></i>
                        </div>
                        <select class="form-select theme-preview-select" name="theme_bottom" id="theme_bottom" data-type="footer">
                            <option value="theme01" {{ ($settings['theme_bottom'] ?? 'theme03') === 'theme01' ? 'selected' : '' }}>테마01</option>
                            <option value="theme02" {{ ($settings['theme_bottom'] ?? 'theme03') === 'theme02' ? 'selected' : '' }}>테마02</option>
                            <option value="theme03" {{ ($settings['theme_bottom'] ?? 'theme03') === 'theme03' ? 'selected' : '' }}>테마03</option>
                            <option value="theme04" {{ ($settings['theme_bottom'] ?? 'theme03') === 'theme04' ? 'selected' : '' }}>테마04</option>
                            <option value="theme05" {{ ($settings['theme_bottom'] ?? 'theme03') === 'theme05' ? 'selected' : '' }}>테마05</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-2" style="height: 24px;">
                            <label class="form-label mb-0">미리보기</label>
                        </div>
                        <div class="theme-preview-wrapper">
                            <div id="theme_bottom_preview" class="theme-preview" style="height: 200px; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; background-color: #f8f9fa; overflow: hidden; position: relative;">
                                <div class="theme-preview-container" style="position: relative; width: 100%; height: 100%;">
                                    @if(isset($footerPreviewHtml) && !empty(trim($footerPreviewHtml)))
                                        {!! $footerPreviewHtml !!}
                                    @else
                                        <div class="text-muted p-3" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                            미리보기를 불러올 수 없습니다.
                                        @if(config('app.debug'))
                                            <br><small>Debug: footerPreviewHtml is {{ isset($footerPreviewHtml) ? 'set but empty' : 'not set' }}</small>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>테마 설정 저장
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 모바일 상단 설정 -->
<div class="card shadow-sm settings-section" style="margin-top: 2rem;">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-phone me-2"></i>모바일 상단</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update', ['site' => $site->slug]) }}" id="mobileHeaderForm">
            @csrf
            @method('PUT')
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="mobile_header_theme" class="form-label">
                            <i class="bi bi-palette me-1"></i>모바일 상단
                        </label>
                        <select class="form-select mobile-header-preview-select" name="mobile_header_theme" id="mobile_header_theme" data-type="mobile-header">
                            <option value="theme1" {{ ($settings['mobile_header_theme'] ?? 'theme1') === 'theme1' ? 'selected' : '' }}>테마1</option>
                            <option value="theme2" {{ ($settings['mobile_header_theme'] ?? 'theme1') === 'theme2' ? 'selected' : '' }}>테마2</option>
                            <option value="theme3" {{ ($settings['mobile_header_theme'] ?? 'theme1') === 'theme3' ? 'selected' : '' }}>테마3</option>
                            <option value="theme4" {{ ($settings['mobile_header_theme'] ?? 'theme1') === 'theme4' ? 'selected' : '' }}>테마4</option>
                            <option value="theme5" {{ ($settings['mobile_header_theme'] ?? 'theme1') === 'theme5' ? 'selected' : '' }}>테마5</option>
                            <option value="theme6" {{ ($settings['mobile_header_theme'] ?? 'theme1') === 'theme6' ? 'selected' : '' }}>테마6</option>
                            <option value="theme7" {{ ($settings['mobile_header_theme'] ?? 'theme1') === 'theme7' ? 'selected' : '' }}>테마7</option>
                            <option value="theme8" {{ ($settings['mobile_header_theme'] ?? 'theme1') === 'theme8' ? 'selected' : '' }}>테마8</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mobile_menu_icon" class="form-label">
                            <i class="bi bi-list me-1"></i>모바일 메뉴 아이콘
                        </label>
                        <select class="form-select" name="mobile_menu_icon" id="mobile_menu_icon">
                            <option value="bi-list" {{ ($settings['mobile_menu_icon'] ?? 'bi-list') === 'bi-list' ? 'selected' : '' }}>☰ 리스트</option>
                            <option value="bi-grid-3x3-gap" {{ ($settings['mobile_menu_icon'] ?? 'bi-list') === 'bi-grid-3x3-gap' ? 'selected' : '' }}>⊞ 그리드</option>
                            <option value="bi-three-dots" {{ ($settings['mobile_menu_icon'] ?? 'bi-list') === 'bi-three-dots' ? 'selected' : '' }}>⋮ 세로 점</option>
                            <option value="bi-three-dots-vertical" {{ ($settings['mobile_menu_icon'] ?? 'bi-list') === 'bi-three-dots-vertical' ? 'selected' : '' }}>⋮ 세로 점 3개</option>
                            <option value="bi-menu-button-wide" {{ ($settings['mobile_menu_icon'] ?? 'bi-list') === 'bi-menu-button-wide' ? 'selected' : '' }}>☰ 와이드 메뉴</option>
                            <option value="bi-menu-app" {{ ($settings['mobile_menu_icon'] ?? 'bi-list') === 'bi-menu-app' ? 'selected' : '' }}>☰ 앱 메뉴</option>
                            <option value="bi-justify" {{ ($settings['mobile_menu_icon'] ?? 'bi-list') === 'bi-justify' ? 'selected' : '' }}>☰ 정렬</option>
                            <option value="bi-list-ul" {{ ($settings['mobile_menu_icon'] ?? 'bi-list') === 'bi-list-ul' ? 'selected' : '' }}>☰ 리스트 UL</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mobile_menu_direction" class="form-label">
                            <i class="bi bi-arrow-right me-1"></i>모바일 메뉴 방향
                        </label>
                        <select class="form-select" name="mobile_menu_direction" id="mobile_menu_direction">
                            <option value="top-to-bottom" {{ ($settings['mobile_menu_direction'] ?? 'top-to-bottom') === 'top-to-bottom' ? 'selected' : '' }}>위에서 아래</option>
                            <option value="left-to-right" {{ ($settings['mobile_menu_direction'] ?? 'top-to-bottom') === 'left-to-right' ? 'selected' : '' }}>좌에서 우</option>
                            <option value="right-to-left" {{ ($settings['mobile_menu_direction'] ?? 'top-to-bottom') === 'right-to-left' ? 'selected' : '' }}>우에서 좌</option>
                            <option value="bottom-to-top" {{ ($settings['mobile_menu_direction'] ?? 'top-to-bottom') === 'bottom-to-top' ? 'selected' : '' }}>아래에서 위</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="mobile_menu_icon_border" id="mobile_menu_icon_border" value="1" {{ ($settings['mobile_menu_icon_border'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="mobile_menu_icon_border">
                                메뉴 아이콘 테두리
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="mobile_menu_login_widget" id="mobile_menu_login_widget" value="1" {{ ($settings['mobile_menu_login_widget'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="mobile_menu_login_widget">
                                모바일 메뉴 로그인 위젯
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">미리보기</label>
                        <div class="mobile-preview-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">
                            <div id="mobile_header_preview" style="border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; min-height: 667px; width: 375px; background-color: #f8f9fa; overflow: hidden; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-phone"></i> 모바일 헤더 미리보기
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>모바일 상단 설정 저장
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 색상 설정 -->
<div class="card shadow-sm settings-section" style="margin-top: 2rem;">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-paint-bucket me-2"></i>색상</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update', ['site' => $site->slug]) }}" id="colorForm">
            @csrf
            @method('PUT')
        <!-- 라이트 모드 -->
        <div class="mb-4">
            <h6 class="mb-3">라이트 모드</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 100px;"></th>
                            <th>헤더</th>
                            <th>푸터</th>
                            <th>본문</th>
                            <th>포인트</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>글씨</th>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_light_header_text" value="{{ $settings['color_light_header_text'] ?? '#000000' }}" title="헤더 글씨 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_light_footer_text" value="{{ $settings['color_light_footer_text'] ?? '#000000' }}" title="푸터 글씨 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_light_body_text" value="{{ $settings['color_light_body_text'] ?? '#000000' }}" title="본문 글씨 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_light_point_main" value="{{ $settings['color_light_point_main'] ?? '#0d6efd' }}" title="포인트 색상">
                            </td>
                        </tr>
                        <tr>
                            <th>배경</th>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_light_header_bg" value="{{ $settings['color_light_header_bg'] ?? '#ffffff' }}" title="헤더 배경 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_light_footer_bg" value="{{ $settings['color_light_footer_bg'] ?? '#f8f9fa' }}" title="푸터 배경 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_light_body_bg" value="{{ $settings['color_light_body_bg'] ?? '#f8f9fa' }}" title="본문 배경 색상">
                            </td>
                            <td>
                                <span class="text-muted">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 다크 모드 -->
        <div>
            <h6 class="mb-3">다크 모드</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 100px;"></th>
                            <th>헤더</th>
                            <th>푸터</th>
                            <th>본문</th>
                            <th>포인트</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>글씨</th>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_dark_header_text" value="{{ $settings['color_dark_header_text'] ?? '#ffffff' }}" title="헤더 글씨 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_dark_footer_text" value="{{ $settings['color_dark_footer_text'] ?? '#ffffff' }}" title="푸터 글씨 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_dark_body_text" value="{{ $settings['color_dark_body_text'] ?? '#ffffff' }}" title="본문 글씨 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_dark_point_main" value="{{ $settings['color_dark_point_main'] ?? '#ffffff' }}" title="포인트 색상">
                            </td>
                        </tr>
                        <tr>
                            <th>배경</th>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_dark_header_bg" value="{{ $settings['color_dark_header_bg'] ?? '#000000' }}" title="헤더 배경 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_dark_footer_bg" value="{{ $settings['color_dark_footer_bg'] ?? '#000000' }}" title="푸터 배경 색상">
                            </td>
                            <td>
                                <input type="color" class="form-control form-control-color" name="color_dark_body_bg" value="{{ $settings['color_dark_body_bg'] ?? '#000000' }}" title="본문 배경 색상">
                            </td>
                            <td>
                                <span class="text-muted">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            <button type="submit" form="colorForm" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>색상 설정 저장
            </button>
        </div>
        </form>
    </div>
</div>

<!-- 폰트 설정 -->
<div class="card shadow-sm settings-section">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-type me-2"></i>폰트</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update', ['site' => $site->slug]) }}" id="fontForm">
            @csrf
            @method('PUT')
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 120px;">디자인</th>
                        <th>크기</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select class="form-select form-select-sm" name="font_design" id="font_design">
                                <option value="noto-sans" {{ ($settings['font_design'] ?? 'noto-sans') === 'noto-sans' ? 'selected' : '' }}>노토산스</option>
                                <option value="malgun-gothic" {{ ($settings['font_design'] ?? '') === 'malgun-gothic' ? 'selected' : '' }}>맑은 고딕</option>
                                <option value="nanum-gothic" {{ ($settings['font_design'] ?? '') === 'nanum-gothic' ? 'selected' : '' }}>나눔고딕</option>
                                <option value="nanum-myeongjo" {{ ($settings['font_design'] ?? '') === 'nanum-myeongjo' ? 'selected' : '' }}>나눔명조</option>
                                <option value="pretendard" {{ ($settings['font_design'] ?? '') === 'pretendard' ? 'selected' : '' }}>프리텐다드</option>
                                <option value="roboto" {{ ($settings['font_design'] ?? '') === 'roboto' ? 'selected' : '' }}>로보토</option>
                                <option value="arial" {{ ($settings['font_design'] ?? '') === 'arial' ? 'selected' : '' }}>Arial</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm" name="font_size" id="font_size">
                                <option value="small" {{ ($settings['font_size'] ?? 'normal') === 'small' ? 'selected' : '' }}>작게</option>
                                <option value="normal" {{ ($settings['font_size'] ?? 'normal') === 'normal' ? 'selected' : '' }}>보통</option>
                                <option value="large" {{ ($settings['font_size'] ?? '') === 'large' ? 'selected' : '' }}>크게</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            <button type="submit" form="fontForm" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>폰트 설정 저장
            </button>
        </div>
        </form>
    </div>
</div>

<!-- 게시판 설정 -->
<div class="card shadow-sm settings-section">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-clipboard me-2"></i>게시판</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update', ['site' => $site->slug]) }}" id="boardForm">
            @csrf
            @method('PUT')
            
            {{-- 데스크탑 버전 (기존 테이블) --}}
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 150px;">베스트 글 기준</th>
                                <th>글쓰기 텀 (초)</th>
                                <th>새글 기준 (시간)</th>
                                <th>조회수 공개</th>
                                <th>게시글/댓글 시각 표시</th>
                                <th style="width: 100px;">설정</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-select form-select-sm" name="best_post_criteria" id="best_post_criteria">
                                        <option value="views" {{ ($settings['best_post_criteria'] ?? 'views') === 'views' ? 'selected' : '' }}>조회수</option>
                                        <option value="likes" {{ ($settings['best_post_criteria'] ?? '') === 'likes' ? 'selected' : '' }}>추천수</option>
                                        <option value="comments" {{ ($settings['best_post_criteria'] ?? '') === 'comments' ? 'selected' : '' }}>댓글수</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           name="write_interval" 
                                           id="write_interval" 
                                           value="{{ old('write_interval', $settings['write_interval'] ?? '0') }}"
                                           min="0"
                                           step="1">
                                </td>
                                <td>
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           name="new_post_hours" 
                                           id="new_post_hours" 
                                           value="{{ old('new_post_hours', $settings['new_post_hours'] ?? '24') }}"
                                           min="0"
                                           step="1">
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="show_views" id="show_views" value="1" {{ (!isset($settings['show_views']) || $settings['show_views'] == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_views">
                                            조회수 공개
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="show_datetime" id="show_datetime" value="1" {{ (!isset($settings['show_datetime']) || $settings['show_datetime'] == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_datetime">
                                            시각 표시
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <button type="submit" form="boardForm" class="btn btn-sm btn-primary w-100">저장</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 모바일 버전 (카드 레이아웃) --}}
            <div class="d-md-none">
                <div class="mb-3">
                    <label for="best_post_criteria_mobile" class="form-label">베스트 글 기준</label>
                    <select class="form-select form-select-sm" name="best_post_criteria" id="best_post_criteria_mobile">
                        <option value="views" {{ ($settings['best_post_criteria'] ?? 'views') === 'views' ? 'selected' : '' }}>조회수</option>
                        <option value="likes" {{ ($settings['best_post_criteria'] ?? '') === 'likes' ? 'selected' : '' }}>추천수</option>
                        <option value="comments" {{ ($settings['best_post_criteria'] ?? '') === 'comments' ? 'selected' : '' }}>댓글수</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="write_interval_mobile" class="form-label">글쓰기 텀 (초)</label>
                    <input type="number" 
                           class="form-control form-control-sm" 
                           name="write_interval" 
                           id="write_interval_mobile" 
                           value="{{ old('write_interval', $settings['write_interval'] ?? '0') }}"
                           min="0"
                           step="1">
                </div>
                <div class="mb-3">
                    <label for="new_post_hours_mobile" class="form-label">새글 기준 (시간)</label>
                    <input type="number" 
                           class="form-control form-control-sm" 
                           name="new_post_hours" 
                           id="new_post_hours_mobile" 
                           value="{{ old('new_post_hours', $settings['new_post_hours'] ?? '24') }}"
                           min="0"
                           step="1">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_views" id="show_views_mobile" value="1" {{ (!isset($settings['show_views']) || $settings['show_views'] == '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_views_mobile">
                            조회수 공개
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_datetime" id="show_datetime_mobile" value="1" {{ (!isset($settings['show_datetime']) || $settings['show_datetime'] == '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_datetime_mobile">
                            게시글/댓글 시각 표시
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="submit" form="boardForm" class="btn btn-primary w-100">저장</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 기능 ON/OFF -->
<div class="card shadow-sm settings-section">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-toggle-on me-2"></i>기능 ON/OFF</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update', ['site' => $site->slug]) }}" id="featureForm">
            @csrf
            @method('PUT')
            
            {{-- 데스크탑 버전 (기존 테이블) --}}
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>방문자수 표기</th>
                                <th>방문자수 변경</th>
                                <th>이메일 알림</th>
                                <th>일반 로그인</th>
                                <th style="width: 100px;">설정</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="show_visitor_count" id="show_visitor_count" value="1" {{ (!isset($settings['show_visitor_count']) || $settings['show_visitor_count'] == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_visitor_count">
                                            방문자수 표기
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="number" 
                                               class="form-control form-control-sm" 
                                               name="visitor_count_adjust" 
                                               id="visitor_count_adjust" 
                                               value="0"
                                               min="0"
                                               step="1"
                                               style="width: 100px;">
                                        <button type="button" class="btn btn-sm btn-secondary" id="increaseVisitorBtn">증가</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="email_notification" id="email_notification" value="1" {{ (!isset($settings['email_notification']) || $settings['email_notification'] == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_notification">
                                            이메일 알림
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="general_login" id="general_login" value="1" {{ (!isset($settings['general_login']) || $settings['general_login'] == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="general_login">
                                            일반 로그인
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <button type="submit" form="featureForm" class="btn btn-sm btn-primary w-100">저장</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 모바일 버전 (카드 레이아웃) --}}
            <div class="d-md-none">
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_visitor_count" id="show_visitor_count_mobile" value="1" {{ (!isset($settings['show_visitor_count']) || $settings['show_visitor_count'] == '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_visitor_count_mobile">
                            방문자수 표기
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="visitor_count_adjust_mobile" class="form-label">방문자수 변경</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" 
                               class="form-control form-control-sm" 
                               name="visitor_count_adjust" 
                               id="visitor_count_adjust_mobile" 
                               value="0"
                               min="0"
                               step="1">
                        <button type="button" class="btn btn-sm btn-secondary" id="increaseVisitorBtnMobile">증가</button>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="email_notification" id="email_notification_mobile" value="1" {{ (!isset($settings['email_notification']) || $settings['email_notification'] == '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="email_notification_mobile">
                            이메일 알림
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="general_login" id="general_login_mobile" value="1" {{ (!isset($settings['general_login']) || $settings['general_login'] == '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="general_login_mobile">
                            일반 로그인
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="submit" form="featureForm" class="btn btn-primary w-100">저장</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 이용약관 & 개인정보처리방침 -->
<div class="card shadow-sm settings-section">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>이용약관 & 개인정보처리방침</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update', ['site' => $site->slug]) }}" id="termsForm">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="terms_of_service" class="form-label">이용약관</label>
                    <textarea class="form-control @error('terms_of_service') is-invalid @enderror" 
                              id="terms_of_service" 
                              name="terms_of_service" 
                              rows="15"
                              style="resize: vertical;">{{ old('terms_of_service', $settings['terms_of_service'] ?? '') }}</textarea>
                    <small class="form-text text-muted">푸터의 "이용약관" 링크 클릭 시 팝업으로 표시됩니다.</small>
                    @error('terms_of_service')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="privacy_policy" class="form-label">개인정보처리방침</label>
                    <textarea class="form-control @error('privacy_policy') is-invalid @enderror" 
                              id="privacy_policy" 
                              name="privacy_policy" 
                              rows="15"
                              style="resize: vertical;">{{ old('privacy_policy', $settings['privacy_policy'] ?? '') }}</textarea>
                    <small class="form-text text-muted">푸터의 "개인정보처리방침" 링크 클릭 시 팝업으로 표시됩니다.</small>
                    @error('privacy_policy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" form="termsForm" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 회사정보 -->
<div class="card shadow-sm settings-section">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-building me-2"></i>회사정보
            <i class="bi bi-question-circle help-icon" 
               data-bs-toggle="tooltip" 
               data-bs-placement="top" 
               title="회사정보는 필수 입력 항목이 아닙니다. 공란으로 두어도 되며, 작성한 내용만 푸터에 표시됩니다."></i>
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            <strong>안내:</strong> 회사정보는 필수 입력 항목이 아닙니다. 공란으로 두어도 되며, 작성한 내용만 푸터에 표시됩니다.
        </div>
        <form method="POST" action="{{ route('admin.settings.update', ['site' => $site->slug]) }}" id="companyInfoForm">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="company_representative" class="form-label">대표자</label>
                    <input type="text" 
                           class="form-control @error('company_representative') is-invalid @enderror" 
                           id="company_representative" 
                           name="company_representative" 
                           value="{{ old('company_representative', $settings['company_representative'] ?? '') }}"
                           placeholder="대표자명을 입력하세요 (선택사항)">
                    @error('company_representative')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="company_contact" class="form-label">연락처</label>
                    <input type="text" 
                           class="form-control @error('company_contact') is-invalid @enderror" 
                           id="company_contact" 
                           name="company_contact" 
                           value="{{ old('company_contact', $settings['company_contact'] ?? '') }}"
                           placeholder="연락처를 입력하세요 (선택사항)">
                    @error('company_contact')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="company_address" class="form-label">주소</label>
                    <input type="text" 
                           class="form-control @error('company_address') is-invalid @enderror" 
                           id="company_address" 
                           name="company_address" 
                           value="{{ old('company_address', $settings['company_address'] ?? '') }}"
                           placeholder="주소를 입력하세요 (선택사항)">
                    @error('company_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="company_registration_number" class="form-label">사업자등록번호</label>
                    <input type="text" 
                           class="form-control @error('company_registration_number') is-invalid @enderror" 
                           id="company_registration_number" 
                           name="company_registration_number" 
                           value="{{ old('company_registration_number', $settings['company_registration_number'] ?? '') }}"
                           placeholder="사업자등록번호를 입력하세요 (선택사항)">
                    @error('company_registration_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="company_telecom_number" class="form-label">통신판매업신고 번호</label>
                    <input type="text" 
                           class="form-control @error('company_telecom_number') is-invalid @enderror" 
                           id="company_telecom_number" 
                           name="company_telecom_number" 
                           value="{{ old('company_telecom_number', $settings['company_telecom_number'] ?? '') }}"
                           placeholder="통신판매업신고 번호를 입력하세요 (선택사항)">
                    @error('company_telecom_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mb-3">
                <label for="company_additional_info" class="form-label">추가 작성내용</label>
                <textarea class="form-control @error('company_additional_info') is-invalid @enderror" 
                          id="company_additional_info" 
                          name="company_additional_info" 
                          rows="4"
                          placeholder="고객센터 전화번호, 운영시간 등 추가 정보를 입력하세요 (선택사항)">{{ old('company_additional_info', $settings['company_additional_info'] ?? '') }}</textarea>
                <small class="form-text text-muted">추가 정보는 푸터에 텍스트로 표시됩니다. 작성한 내용만 표시되며, 공란으로 두어도 됩니다. <strong>HTML 형태의 글도 작성 가능합니다.</strong></small>
                @error('company_additional_info')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" form="companyInfoForm" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 도메인 연결 섹션 (유료 플랜만) --}}
@php
    $hasSubscription = $site->subscription;
    $isActive = $hasSubscription && $site->subscription->status === 'active';
    $isFreePlan = $hasSubscription && $site->subscription->plan && $site->subscription->plan->billing_type === 'free';
    $canUseDomain = $hasSubscription && $isActive && !$isFreePlan;
    
    // 네임서버 정보 가져오기 (실제 저장된 네임서버만 사용, 기본값 사용 안 함)
    $nameservers = $site->nameservers ?? [];
    if (is_string($nameservers)) {
        $nameservers = json_decode($nameservers, true) ?? [];
    }
    // 기본값을 사용하지 않음 - 실제 네임서버가 있을 때만 표시
@endphp

@if($canUseDomain)
<div class="card shadow-sm settings-section">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-globe me-2"></i>도메인 연결</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>도메인 연결 안내:</strong> 커스텀 도메인을 연결하려면 도메인 제공업체에서 네임서버를 변경해야 합니다.
        </div>
        
        @php
            $masterSite = \App\Models\Site::getMasterSite();
            // 마스터 사이트가 없으면 현재 사이트가 마스터 사이트인지 확인
            if (!$masterSite) {
                $masterSite = $site->isMasterSite() ? $site : null;
            }
            // 마스터 사이트 slug 사용 (없으면 'master' 기본값)
            $masterSiteSlug = $masterSite ? $masterSite->slug : 'master';
            try {
                $updateDomainUrl = route('user-sites.update-domain', ['site' => $masterSiteSlug, 'userSite' => $site->slug]);
            } catch (\Exception $e) {
                // 라우트 생성 실패 시 로그 기록
                \Log::error('Failed to generate domain update route', [
                    'masterSiteSlug' => $masterSiteSlug,
                    'userSiteSlug' => $site->slug,
                    'error' => $e->getMessage()
                ]);
                $updateDomainUrl = '/site/' . $masterSiteSlug . '/my-sites/' . $site->slug . '/domain';
            }
            // 절대 URL 생성
            $updateDomainUrlAbsolute = url($updateDomainUrl);
        @endphp
        <form method="POST" action="{{ $updateDomainUrlAbsolute }}" id="domainForm" class="mb-4" data-action-url="{{ $updateDomainUrlAbsolute }}">
            @csrf
            @method('PUT')
            <label class="form-label fw-bold">도메인 설정</label>
            <div class="input-group mb-2">
                <input type="text" 
                       class="form-control @error('domain') is-invalid @enderror" 
                       name="domain" 
                       id="domain"
                       value="{{ old('domain', $site->domain) }}"
                       placeholder="예: example.com">
                <button type="submit" class="btn btn-primary" id="domainSubmitBtn">
                    <i class="bi bi-check-circle me-1"></i>저장
                </button>
            </div>
            <div id="domainError" class="invalid-feedback d-block" style="display: none;"></div>
            @error('domain')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">
                @if($site->domain)
                    현재 연결된 도메인: <strong>{{ $site->domain }}</strong> | 
                    <a href="https://{{ $site->domain }}" target="_blank" class="text-decoration-none">
                        <i class="bi bi-box-arrow-up-right me-1"></i>확인
                    </a>
                @else
                    서브도메인: <strong>{{ $site->slug }}.{{ config('app.master_domain', 'seoomweb.com') }}</strong><br>
                    <strong>도메인을 입력하고 저장하면 자동으로 Cloudflare에 추가되고 DNS 레코드가 생성됩니다.</strong>
                @endif
            </small>
        </form>
        
        @if($site->domain)
        <div class="mb-4">
            <label class="form-label fw-bold">현재 도메인</label>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                도메인: <strong>{{ $site->domain }}</strong>
            </div>
        </div>
        @endif
        
        <div class="mb-4">
            <label class="form-label fw-bold">
                <i class="bi bi-server me-1"></i>네임서버 정보
                <button type="button" 
                        class="btn btn-sm btn-link p-0 ms-1" 
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top"
                        title="도메인 제공업체(가비아, 후이즈 등)에서 이 네임서버로 변경하세요.">
                    <i class="bi bi-question-circle text-primary"></i>
                </button>
            </label>
            <div class="card bg-light">
                <div class="card-body" id="nameserversContainer">
                    @if(!empty($nameservers))
                        @foreach($nameservers as $index => $nameserver)
                            <div class="mb-2 d-flex align-items-center">
                                <strong class="me-2">네임서버 {{ $index + 1 }}:</strong>
                                <code class="flex-grow-1 ms-2" style="font-size: 1.1em; background-color: white; padding: 0.5rem; border-radius: 0.25rem;">{{ $nameserver }}</code>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary ms-2" 
                                        onclick="copyNameserverToClipboard('{{ $nameserver }}', this)">
                                    <i class="bi bi-clipboard me-1"></i>복사
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted">
                            도메인을 입력하고 저장하면 네임서버 정보가 나타납니다.
                        </div>
                    @endif
                </div>
            </div>
            <small class="form-text text-muted mt-2">
                <strong>도메인 제공업체별 설정 위치:</strong><br>
                • <strong>가비아:</strong> 마이 가비아 → 도메인 → 네임서버 설정<br>
                • <strong>후이즈:</strong> 도메인 관리 → 네임서버 설정<br>
                • <strong>기타:</strong> 도메인 관리 페이지에서 "네임서버 설정" 또는 "네임서버 변경" 메뉴 찾기
            </small>
        </div>
        
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            <strong>팁:</strong> 네임서버를 변경하면 DNS 레코드를 하나하나 설정할 필요 없이 자동으로 연결됩니다. 변경 후 적용까지 보통 5분~24시간 정도 소요됩니다.
        </div>
        
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <strong>중요:</strong> 도메인을 먼저 입력하고 저장해야 Cloudflare에 자동으로 추가되고 네임서버 정보가 생성됩니다. 도메인을 저장한 후 위의 네임서버 정보를 도메인 제공업체에서 변경하세요.
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    // 라우트 URL 변수 설정 (JavaScript 오류 방지)
    var uploadImageRoute = @json(route('admin.settings.upload-image', ['site' => $site->slug]));

    // 도메인 저장 폼 AJAX 처리
    document.addEventListener('DOMContentLoaded', function() {
    const domainForm = document.getElementById('domainForm');
    if (domainForm) {
        domainForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('domainSubmitBtn');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            const domainInput = document.getElementById('domain');
            const errorDiv = document.getElementById('domainError');
            const nameserversContainer = document.getElementById('nameserversContainer');

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>저장 중...';
            }
            if (errorDiv) {
                errorDiv.style.display = 'none';
                errorDiv.textContent = '';
            }

            const formData = new FormData(domainForm);

            // data-action-url 속성에서 URL 가져오기 (없으면 action 속성 사용)
            let url = domainForm.getAttribute('data-action-url') || domainForm.action || '';

            // URL이 상대 경로인 경우 절대 경로로 변환
            if (url && !url.startsWith('http') && !url.startsWith('//')) {
                url = url.startsWith('/') ? window.location.origin + url : window.location.origin + '/' + url;
            }

            // URL이 비어있거나 잘못된 경우 기본 URL 사용
            if (!url || url.includes('/login')) {
                const currentPath = window.location.pathname;
                const pathMatch = currentPath.match(/\/site\/([^\/]+)\/admin\/settings/);
                if (pathMatch) {
                    const siteSlug = pathMatch[1];
                    const masterSiteSlug = 'master'; // 기본값
                    url = `${window.location.origin}/site/${masterSiteSlug}/my-sites/${siteSlug}/domain`;
                } else {
                    console.error('Failed to determine site slug from URL');
                    if (errorDiv) {
                        errorDiv.textContent = '도메인 저장 경로를 찾을 수 없습니다.';
                        errorDiv.style.display = 'block';
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                    return;
                }
            }

            console.log('Domain update URL:', url);

            try {
                const response = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': (function() { var meta = document.querySelector('meta[name="csrf-token"]'); return meta ? meta.getAttribute('content') : null; })() || '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                let data = null;
                try {
                    data = await response.json();
                } catch (jsonError) {
                    console.error('Domain update JSON parse error:', jsonError);
                }

                if (!response.ok || !data) {
                    const errorMessage = (data && (data.message || data.error)) || '저장에 실패했습니다.';
                    throw new Error(errorMessage);
                }

                if (!data.success) {
                    throw new Error(data.message || '저장에 실패했습니다.');
                }

                // 도메인 입력 필드 업데이트
                if (domainInput && data.domain !== undefined) {
                    domainInput.value = data.domain || '';
                }

                // 성공 메시지 표시
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `
                        <i class="bi bi-check-circle me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                domainForm.insertAdjacentElement('beforebegin', alertDiv);

                setTimeout(function() {
                    alertDiv.remove();
                }, 3000);

                // 네임서버 정보 업데이트
                if (nameserversContainer) {
                    if (data.nameservers && data.nameservers.length > 0) {
                        let nameserversHtml = '';
                        data.nameservers.forEach((nameserver, index) => {
                            nameserversHtml += `
                                    <div class="mb-2 d-flex align-items-center">
                                        <strong class="me-2">네임서버 ${index + 1}:</strong>
                                        <code class="flex-grow-1 ms-2" style="font-size: 1.1em; background-color: white; padding: 0.5rem; border-radius: 0.25rem;">${nameserver}</code>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary ms-2" 
                                            onclick="copyNameserverToClipboard('${nameserver}', this)">
                                        <i class="bi bi-clipboard me-1"></i>복사
                                    </button>
                                </div>
                            `;
                        });
                        nameserversContainer.innerHTML = nameserversHtml;
                    } else {
                        nameserversContainer.innerHTML = '<div class="text-muted">도메인을 입력하고 저장하면 네임서버 정보가 나타납니다.</div>';
                    }
                }

                // 도메인 정보 업데이트
                const domainInfo = domainForm.querySelector('.form-text');
                if (domainInfo) {
                    if (data.domain) {
                        domainInfo.innerHTML = `
                                현재 연결된 도메인: <strong>${data.domain}</strong> | 
                                <a href="https://${data.domain}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>확인
                                </a>
                            `;
                    } else {
                        const siteSlug = '{{ $site->slug }}';
                        const masterDomain = '{{ config("app.master_domain", "seoomweb.com") }}';
                        domainInfo.innerHTML = `
                                서브도메인: <strong>${siteSlug}.${masterDomain}</strong><br>
                                <strong>도메인을 입력하고 저장하면 자동으로 Cloudflare에 추가되고 DNS 레코드가 생성됩니다.</strong>
                            `;
                    }
                }

                // 네임서버 섹션 표시/숨김 처리
                const nameserverSection = document.querySelector('[ref="e577"]');
                if (nameserverSection) {
                    if (data.domain && data.nameservers && data.nameservers.length > 0) {
                        nameserverSection.style.display = 'block';
                    } else if (!data.domain) {
                        nameserverSection.style.display = 'none';
                    }
                }

                // 페이지 새로고침 (최신 데이터 반영)
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            } catch (error) {
                console.error('Domain update error:', error);
                if (errorDiv) {
                    errorDiv.textContent = error && error.message ? error.message : '저장에 실패했습니다.';
                    errorDiv.style.display = 'block';
                } else {
                    alert(error && error.message ? error.message : '저장에 실패했습니다.');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }
    
    // 초기 미리보기는 서버 사이드에서 렌더링되므로 초기화 불필요
    // 디자인 변경 시에만 AJAX로 업데이트

    // 테마 선택 시 미리보기 업데이트
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('theme-preview-select')) {
            const type = e.target.getAttribute('data-type');
            const targetId = e.target.id;
            
            // 메뉴 폰트 설정 드롭다운인 경우 현재 선택된 테마를 사용
            if (targetId === 'menu_font_size' || targetId === 'menu_font_padding' || targetId === 'menu_font_weight') {
                const headerSelect = document.getElementById('theme_top');
                const theme = headerSelect ? headerSelect.value : 'design1';
                console.log('Menu font setting changed:', targetId, 'using theme:', theme);
                updateThemePreview(type, theme);
            } else {
                // 일반 테마 선택인 경우
                const theme = e.target.value;
                console.log('Theme changed:', type, theme);
                updateThemePreview(type, theme);
            }
        }
    });
    
    // 다크모드 변경 시 미리보기 업데이트
    const darkModeSelect = document.getElementById('theme_dark_mode');
    if (darkModeSelect) {
        darkModeSelect.addEventListener('change', function() {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
            const footerSelect = document.getElementById('theme_bottom');
            if (footerSelect && footerSelect.value) {
                updateThemePreview('footer', footerSelect.value);
            }
        });
    }
    
    // 색상 변경 시 미리보기 업데이트
    document.addEventListener('change', function(e) {
        if (e.target.type === 'color') {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
            const footerSelect = document.getElementById('theme_bottom');
            if (footerSelect && footerSelect.value) {
                updateThemePreview('footer', footerSelect.value);
            }
        }
    });
    
    // 최상단 헤더 표시 체크박스 변경 시 미리보기 업데이트
    const topHeaderShowCheckbox = document.getElementById('theme_top_header_show');
    if (topHeaderShowCheckbox) {
        topHeaderShowCheckbox.addEventListener('change', function() {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
        });
    }
    
    // 로그인 버튼 표시 체크박스 변경 시 미리보기 업데이트
    const topHeaderLoginShowCheckbox = document.getElementById('top_header_login_show');
    if (topHeaderLoginShowCheckbox) {
        topHeaderLoginShowCheckbox.addEventListener('change', function() {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
        });
    }
    
    // 메뉴 로그인 표시 체크박스 변경 시 미리보기 업데이트
    const menuLoginShowCheckbox = document.getElementById('menu_login_show');
    if (menuLoginShowCheckbox) {
        menuLoginShowCheckbox.addEventListener('change', function() {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
        });
    }

    // 이미지 업로드 함수
    function uploadImage(file, $uploadArea) {
        console.log('uploadImage called', file, $uploadArea);
        if (!file) {
            console.error('No file provided');
            return;
        }
        if (!$uploadArea || $uploadArea.length === 0) {
            console.error('No upload area found');
            return;
        }

        var type = $uploadArea.data('type');
        var inputName = $uploadArea.data('input');
        var $input = $('#' + inputName);
        
        console.log('Upload params - type:', type, 'inputName:', inputName);

        // FileReader로 즉시 미리보기 표시 (배너 이미지처럼)
        console.log('Creating FileReader for preview...');
        var reader = new FileReader();
        reader.onload = function(e) {
            console.log('FileReader onload triggered');
            // 즉시 미리보기 표시
            $uploadArea.addClass('has-image');
            var previewStyle = type === 'favicon' ? 'max-height: 60px; display: block; width: auto; height: auto; margin: 0 auto;' : 'max-height: 120px; display: block; width: auto; height: auto; margin: 0 auto;';
            
            // 이미지 alt 텍스트 설정
            var imageAlt = '로고';
            if (type === 'logo_dark') {
                imageAlt = '로고 (다크모드)';
            } else if (type === 'favicon') {
                imageAlt = '파비콘';
            } else if (type === 'og_image') {
                imageAlt = 'OG 이미지';
            }
            
            var acceptType = type === 'favicon' ? 'image/*,.ico' : 'image/*';
            
            // 기존 내용 제거하고 미리보기 이미지와 파일 input 추가
            var $img = $('<img>', {
                'class': 'image-preview',
                'src': e.target.result,
                'alt': imageAlt,
                'style': previewStyle
            });
            
            // 이미지 로드 확인
            $img.on('load', function() {
                console.log('Preview image loaded successfully');
            }).on('error', function() {
                console.error('Preview image failed to load');
            });
            
            var $fileInput = $('<input>', {
                'type': 'file',
                'class': 'hidden-file-input',
                'accept': acceptType,
                'data-type': type
            });
            
            // 업로드 영역 내부의 hidden input은 제거 (form 내부의 hidden input만 사용)
            $uploadArea.empty().append($img).append($fileInput);
            console.log('Local preview displayed, image src:', e.target.result.substring(0, 50) + '...');
        };
        reader.onerror = function(e) {
            console.error('FileReader error:', e);
            alert('파일을 읽는 중 오류가 발생했습니다.');
        };
        reader.readAsDataURL(file);

        // FormData 생성
        var formData = new FormData();
        formData.append('image', file);
        formData.append('type', type);
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        formData.append('_token', csrfToken);
        
        console.log('FormData created, CSRF token:', csrfToken ? 'exists' : 'missing');
        console.log('Starting AJAX upload to:', uploadImageRoute);

        // AJAX 업로드
        $.ajax({
            url: uploadImageRoute,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30초 타임아웃
            success: function(response) {
                console.log('Upload response received:', response);
                
                // 응답 검증
                if (!response) {
                    console.error('No response received');
                    alert('서버 응답을 받을 수 없습니다.');
                    return;
                }
                
                if (response.error) {
                    var errorMessage = response.message || '이미지 업로드에 실패했습니다.';
                    console.error('Upload error:', errorMessage);
                    alert(errorMessage);
                    resetUploadArea($uploadArea);
                    return;
                }
                
                if (!response.url) {
                    console.error('No URL in response:', response);
                    alert('이미지 URL을 받을 수 없습니다.');
                    resetUploadArea($uploadArea);
                    return;
                }
                
                // 서버 URL로 이미지 소스 업데이트
                console.log('Upload success, URL:', response.url);
                var $img = $uploadArea.find('.image-preview');
                if ($img.length > 0) {
                    $img.attr('src', response.url);
                }
                
                // form 내부의 hidden input 값 업데이트
                var $settingsForm = $('#settingsForm');
                var inputInForm = $settingsForm.find('#' + inputName).length > 0;
                
                if ($input.length) {
                    $input.val(response.url);
                    console.log('Hidden input updated:', inputName, response.url);
                    console.log('Hidden input value after update:', $input.val());
                    console.log('Hidden input element:', $input[0]);
                    console.log('Hidden input is in form:', inputInForm);
                    
                    // form 내부에 없으면 form으로 이동
                    if (!inputInForm && $settingsForm.length > 0) {
                        console.log('Moving hidden input into form...');
                        $input.detach().appendTo($settingsForm);
                        console.log('Hidden input moved to form. Now in form:', $settingsForm.find('#' + inputName).length > 0);
                    }
                } else {
                    console.error('Hidden input not found by ID:', inputName, 'Trying to find by name attribute...');
                    // name 속성으로도 찾기 시도
                    var $inputByName = $('input[name="' + inputName + '"]');
                    if ($inputByName.length) {
                        $inputByName.val(response.url);
                        console.log('Hidden input found by name and updated:', inputName, response.url);
                        var inputByNameInForm = $settingsForm.find('input[name="' + inputName + '"]').length > 0;
                        console.log('Hidden input is in form:', inputByNameInForm);
                        
                        // form 내부에 없으면 form으로 이동
                        if (!inputByNameInForm && $settingsForm.length > 0) {
                            console.log('Moving hidden input into form...');
                            $inputByName.detach().appendTo($settingsForm);
                            console.log('Hidden input moved to form. Now in form:', $settingsForm.find('input[name="' + inputName + '"]').length > 0);
                        }
                    } else {
                        console.error('Hidden input not found by name either:', inputName);
                        // 직접 생성하여 form에 추가
                        if ($settingsForm.length > 0) {
                            var $newInput = $('<input>', {
                                type: 'hidden',
                                name: inputName,
                                id: inputName,
                                value: response.url
                            });
                            $settingsForm.append($newInput);
                            console.log('Created new hidden input and added to form:', inputName, response.url);
                        } else {
                            console.error('settingsForm not found!');
                        }
                    }
                }
                
                console.log('Preview update completed');
            },
            error: function(xhr, status, error) {
                console.error('AJAX error - Status:', status, 'Error:', error);
                console.error('Response:', xhr.responseText);
                var errorMessage = '이미지 업로드에 실패했습니다.';
                if (xhr.responseJSON) {
                    console.error('Response JSON:', xhr.responseJSON);
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.errors && xhr.responseJSON.errors.image) {
                        errorMessage = Array.isArray(xhr.responseJSON.errors.image) 
                            ? xhr.responseJSON.errors.image[0] 
                            : xhr.responseJSON.errors.image;
                    }
                } else if (xhr.status === 0) {
                    errorMessage = '네트워크 오류가 발생했습니다. 인터넷 연결을 확인해주세요.';
                } else if (xhr.status === 413) {
                    errorMessage = '파일 크기가 너무 큽니다. (최대 5MB)';
                } else if (xhr.status === 422) {
                    errorMessage = '파일 형식이 올바르지 않습니다. (JPEG, PNG, JPG, GIF, WEBP, ICO만 가능)';
                } else if (xhr.status === 500) {
                    errorMessage = '서버 오류가 발생했습니다. 관리자에게 문의해주세요.';
                }
                console.error('Final error message:', errorMessage);
                alert(errorMessage);
                resetUploadArea($uploadArea);
            }
        });
    }

    // 업로드 영역 초기화 함수
    function resetUploadArea($area) {
        var type = $area.data('type');
        var inputName = $area.data('input');
        var acceptType = type === 'favicon' ? 'image/*,.ico' : 'image/*';
        var uploadBtnStyle = type === 'favicon' ? 'style="padding: 0.5rem;"><i class="bi bi-cloud-upload"></i><span style="font-size: 0.75rem;">업로드</span>' : '><i class="bi bi-cloud-upload"></i><span>업로드</span>';
        
        $area.removeClass('has-image');
        // 업로드 영역 내부의 hidden input은 제거 (form 내부의 hidden input만 사용)
        $area.html(
            '<div class="image-upload-btn" ' + uploadBtnStyle + '</div>' +
            '<input type="file" class="hidden-file-input" accept="' + acceptType + '" data-type="' + type + '">'
        );
        
        // 이벤트 위임을 사용하므로 자동으로 처리됨
        
        // form 내부의 hidden input 값 초기화
        var $input = $('#' + inputName);
        if ($input.length) {
            $input.val('');
        }
    }

    // 이미지 미리보기 클릭 시 삭제 또는 교체
    $(document).on('click', '.image-preview', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $area = $(this).closest('.image-upload-area');
        var action = confirm('이미지를 삭제하시겠습니까?\n\n취소를 누르면 이미지를 교체할 수 있습니다.');
        
        if (action) {
            // 삭제
            var inputName = $area.data('input');
            var $input = $('#' + inputName);
            var type = $area.data('type');
            var acceptType = type === 'favicon' ? 'image/*,.ico' : 'image/*';
            var uploadBtnStyle = type === 'favicon' ? 'style="padding: 0.5rem;"><i class="bi bi-cloud-upload"></i><span style="font-size: 0.75rem;">업로드</span>' : '><i class="bi bi-cloud-upload"></i><span>업로드</span>';
            
            $area.removeClass('has-image');
            // 업로드 영역 내부의 hidden input은 제거 (form 내부의 hidden input만 사용)
            $area.html(
                '<div class="image-upload-btn" ' + uploadBtnStyle + '</div>' +
                '<input type="file" class="hidden-file-input" accept="' + acceptType + '" data-type="' + type + '">'
            );
            
            // form 내부의 hidden input 값 초기화
            if ($input.length) {
                $input.val('');
            }
        } else {
            // 교체 - 파일 input 동적 생성 후 클릭
            var type = $area.data('type');
            var acceptType = type === 'favicon' ? 'image/*,.ico' : 'image/*';
            
            // 기존 파일 input 제거
            $area.find('.hidden-file-input').remove();
            
            // 새 파일 input 생성 및 추가
            var $fileInput = $('<input>', {
                type: 'file',
                class: 'hidden-file-input',
                accept: acceptType,
                'data-type': type
            });
            
            $area.append($fileInput);
            
            // 파일 선택 후 업로드 처리
            $fileInput.on('change', function() {
                var file = this.files[0];
                if (file) {
                    uploadImage(file, $area);
                }
            });
            
            // 파일 input 클릭
            setTimeout(function() {
                $fileInput[0].click();
            }, 10);
        }
        return false;
    });

    // 이미지 업로드 영역 클릭 시 파일 선택 창 열기
    $(document).on('click', '.image-upload-area', function(e) {
        // 이미지나 파일 input을 클릭한 경우는 제외
        if ($(e.target).hasClass('image-preview') || $(e.target).hasClass('hidden-file-input')) {
            return;
        }
        var $fileInput = $(this).find('.hidden-file-input');
        if ($fileInput.length > 0) {
            $fileInput[0].click();
        }
    });

    // 파일 input 클릭 시 이벤트 전파 중지 (이미지 업로드 영역 클릭 이벤트와 충돌 방지)
    // 모바일/데스크탑 로고 설정 동기화
    function syncLogoFields() {
        // 로고 타입 동기화
        const desktopLogoType = document.getElementById('logo_type');
        const mobileLogoType = document.getElementById('logo_type_mobile');
        if (desktopLogoType && mobileLogoType) {
            mobileLogoType.addEventListener('change', function() {
                desktopLogoType.value = this.value;
                // 텍스트 공지 표시/숨김
                const notice = document.getElementById('logo-text-notice');
                const noticeMobile = document.getElementById('logo-text-notice-mobile');
                if (this.value === 'text') {
                    if (notice) notice.style.display = 'block';
                    if (noticeMobile) noticeMobile.style.display = 'block';
                } else {
                    if (notice) notice.style.display = 'none';
                    if (noticeMobile) noticeMobile.style.display = 'none';
                }
            });
            desktopLogoType.addEventListener('change', function() {
                mobileLogoType.value = this.value;
                const notice = document.getElementById('logo-text-notice');
                const noticeMobile = document.getElementById('logo-text-notice-mobile');
                if (this.value === 'text') {
                    if (notice) notice.style.display = 'block';
                    if (noticeMobile) noticeMobile.style.display = 'block';
                } else {
                    if (notice) notice.style.display = 'none';
                    if (noticeMobile) noticeMobile.style.display = 'none';
                }
            });
        }

        // 사이즈 동기화
        const desktopSize = document.getElementById('logo_desktop_size');
        const mobileSize = document.getElementById('logo_mobile_size');
        const desktopSizeMobile = document.getElementById('logo_desktop_size_mobile');
        const mobileSizeMobile = document.getElementById('logo_mobile_size_mobile');
        
        if (desktopSize && desktopSizeMobile) {
            desktopSizeMobile.addEventListener('input', function() {
                desktopSize.value = this.value;
            });
            desktopSize.addEventListener('input', function() {
                desktopSizeMobile.value = this.value;
            });
        }
        
        if (mobileSize && mobileSizeMobile) {
            mobileSizeMobile.addEventListener('input', function() {
                mobileSize.value = this.value;
            });
            mobileSize.addEventListener('input', function() {
                mobileSizeMobile.value = this.value;
            });
        }

        // 이미지 업로드 후 hidden input 동기화
        $(document).on('change', '.hidden-file-input', function() {
            const $this = $(this);
            const type = $this.data('type');
            const inputId = $this.closest('.image-upload-area').data('input');
            
            // 모바일과 데스크탑의 hidden input 동기화
            const desktopInput = document.getElementById(inputId);
            const mobileInput = document.getElementById(inputId + '_mobile');
            
            if (desktopInput && mobileInput && $this.closest('.d-md-none').length > 0) {
                // 모바일에서 업로드한 경우
                setTimeout(function() {
                    mobileInput.value = desktopInput.value;
                }, 100);
            } else if (desktopInput && mobileInput && $this.closest('.d-none').length > 0) {
                // 데스크탑에서 업로드한 경우
                setTimeout(function() {
                    mobileInput.value = desktopInput.value;
                }, 100);
            }
        });
    }
    
    syncLogoFields();

    $(document).on('click', '.hidden-file-input', function(e) {
        e.stopPropagation();
        // 브라우저 기본 동작은 유지 (파일 선택 창 열기)
    });

    // 파일 선택 시 업로드 (이벤트 위임 사용)
    $(document).on('change', '.hidden-file-input', function(e) {
        e.stopPropagation();
        e.preventDefault();
        console.log('File input changed');
        
        var file = this.files[0];
        if (!file) {
            console.log('No file selected');
            return;
        }
        
        console.log('File selected:', file.name, file.size, file.type);
        var $uploadArea = $(this).closest('.image-upload-area');
        console.log('Upload area found:', $uploadArea.length);
        
        if ($uploadArea.length === 0) {
            console.error('Upload area not found!');
            alert('업로드 영역을 찾을 수 없습니다.');
            return;
        }
        
        // uploadImage 함수가 정의되어 있는지 확인
        if (typeof uploadImage !== 'function') {
            console.error('uploadImage function is not defined!');
            alert('이미지 업로드 함수를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
            return;
        }
        
        console.log('Calling uploadImage function...');
        uploadImage(file, $uploadArea);
    });

    // 네임서버 복사 기능 (버튼 스타일 변경)
    window.copyNameserverToClipboard = function(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check me-1"></i>복사됨';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
            
            setTimeout(function() {
                button.innerHTML = originalHtml;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        }).catch(function(err) {
            // 클립보드 API가 지원되지 않는 경우 대체 방법
            var textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check me-1"></i>복사됨';
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-success');
                
                setTimeout(function() {
                    button.innerHTML = originalHtml;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }, 2000);
            } catch (err) {
                alert('복사에 실패했습니다. 수동으로 복사해주세요: ' + text);
            }
            document.body.removeChild(textArea);
        });
    };
    }); // DOMContentLoaded 끝
})(); // 즉시 실행 함수 끝

// 테마 미리보기 데이터
const themePreviews = {
    header: {
        design1: { bg: '#0d6efd', text: '#ffffff', style: 'solid', desc: '로고 | 메뉴 좌측 | 로그인/회원가입' },
        design2: { bg: '#6c757d', text: '#ffffff', style: 'solid', desc: '로고 | 메뉴 중앙 | 로그인/회원가입' },
        design3: { bg: '#198754', text: '#ffffff', style: 'solid', desc: '로고 | 메뉴 우측 | 로그인/회원가입' },
        design4: { bg: '#ffc107', text: '#000000', style: 'solid', desc: '메뉴 좌측 | 로고 중앙 | 로그인/회원가입' },
        design5: { bg: '#dc3545', text: '#ffffff', style: 'solid', desc: '로고 | 검색창 | 로그인/회원가입 (하단 메뉴)' },
        design6: { bg: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', text: '#ffffff', style: 'gradient', desc: '로고 중앙 (하단 메뉴 중앙)' },
    },
    footer: {
        theme01: { bg: '#212529', text: '#ffffff', style: 'solid' },
        theme02: { bg: '#343a40', text: '#ffffff', style: 'solid' },
        theme03: { bg: '#495057', text: '#ffffff', style: 'solid' },
        theme04: { bg: '#6c757d', text: '#ffffff', style: 'solid' },
        theme05: { bg: '#adb5bd', text: '#000000', style: 'solid' },
    }
};

function updateThemePreview(type, theme) {
    // 매개변수를 로컬 변수로 저장 (스코프 문제 방지)
    var previewType = type;
    var previewTheme = theme;
    
    const previewId = previewType === 'header' ? 'theme_top_preview' : 'theme_bottom_preview';
    const previewElement = document.getElementById(previewId);
    
    if (!previewElement) {
        console.error('Preview element not found:', previewId);
        return;
    }
    
    let container = previewElement.querySelector('.theme-preview-container');
    
    if (!container) {
        container = document.createElement('div');
        container.className = 'theme-preview-container';
        previewElement.appendChild(container);
    }
    
    // 로딩 표시
    container.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    console.log('Updating preview:', previewType, previewTheme);
    var menuFontSizeEl = document.getElementById('menu_font_size');
    var menuFontPaddingEl = document.getElementById('menu_font_padding');
    var menuFontWeightEl = document.getElementById('menu_font_weight');
    console.log('Menu font settings - size:', menuFontSizeEl ? menuFontSizeEl.value : null, 'padding:', menuFontPaddingEl ? menuFontPaddingEl.value : null, 'weight:', menuFontWeightEl ? menuFontWeightEl.value : null);
    
    // 현재 입력된 색상 값 가져오기
    var themeDarkModeEl = document.getElementById('theme_dark_mode');
    const darkMode = (themeDarkModeEl && themeDarkModeEl.value) ? themeDarkModeEl.value : 'light';
    const isDark = darkMode === 'dark';
    
    // AJAX로 실제 헤더 미리보기 HTML 가져오기
    const url = '{{ route("admin.settings.preview-header", ["site" => $site->slug]) }}';
    const params = new URLSearchParams({
        theme: previewTheme,
        type: previewType,
        theme_dark_mode: darkMode
    });
    
    // 현재 입력된 색상 값 추가
    if (previewType === 'header') {
        if (isDark) {
            var darkHeaderTextEl = document.querySelector('input[name="color_dark_header_text"]');
            var darkHeaderBgEl = document.querySelector('input[name="color_dark_header_bg"]');
            var darkPointMainEl = document.querySelector('input[name="color_dark_point_main"]');
            const darkHeaderText = darkHeaderTextEl ? darkHeaderTextEl.value : null;
            const darkHeaderBg = darkHeaderBgEl ? darkHeaderBgEl.value : null;
            const darkPointMain = darkPointMainEl ? darkPointMainEl.value : null;
            if (darkHeaderText) params.append('color_dark_header_text', darkHeaderText);
            if (darkHeaderBg) params.append('color_dark_header_bg', darkHeaderBg);
            if (darkPointMain) params.append('color_dark_point_main', darkPointMain);
        } else {
            var lightHeaderTextEl = document.querySelector('input[name="color_light_header_text"]');
            var lightHeaderBgEl = document.querySelector('input[name="color_light_header_bg"]');
            var lightPointMainEl = document.querySelector('input[name="color_light_point_main"]');
            const lightHeaderText = lightHeaderTextEl ? lightHeaderTextEl.value : null;
            const lightHeaderBg = lightHeaderBgEl ? lightHeaderBgEl.value : null;
            const lightPointMain = lightPointMainEl ? lightPointMainEl.value : null;
            if (lightHeaderText) params.append('color_light_header_text', lightHeaderText);
            if (lightHeaderBg) params.append('color_light_header_bg', lightHeaderBg);
            if (lightPointMain) params.append('color_light_point_main', lightPointMain);
        }
        
        // 최상단 헤더 표시 체크박스 값 추가
        const topHeaderShowCheckbox = document.getElementById('theme_top_header_show');
        const topHeaderShow = topHeaderShowCheckbox && topHeaderShowCheckbox.checked ? '1' : '0';
        params.append('theme_top_header_show', topHeaderShow);
        
        // 로그인 버튼 표시 체크박스 값 추가
        const topHeaderLoginShowCheckbox = document.getElementById('top_header_login_show');
        const topHeaderLoginShow = topHeaderLoginShowCheckbox && topHeaderLoginShowCheckbox.checked ? '1' : '0';
        params.append('top_header_login_show', topHeaderLoginShow);
        
        // 메뉴 로그인 표시 체크박스 값 추가
        const menuLoginShowCheckbox = document.getElementById('menu_login_show');
        const menuLoginShow = menuLoginShowCheckbox && menuLoginShowCheckbox.checked ? '1' : '0';
        params.append('menu_login_show', menuLoginShow);
        
        // 그림자 체크박스 값 추가
        const headerShadowCheckbox = document.getElementById('header_shadow');
        const headerShadow = headerShadowCheckbox && headerShadowCheckbox.checked ? '1' : '0';
        params.append('header_shadow', headerShadow);
        
        // 헤더 테두리 체크박스 값 추가
        const headerBorderCheckbox = document.getElementById('header_border');
        const headerBorder = headerBorderCheckbox && headerBorderCheckbox.checked ? '1' : '0';
        params.append('header_border', headerBorder);
        
        // 헤더 테두리 두께 및 컬러 값 추가
        if (headerBorder === '1') {
            var headerBorderWidthEl = document.getElementById('header_border_width');
            var headerBorderColorEl = document.getElementById('header_border_color');
            const headerBorderWidth = (headerBorderWidthEl && headerBorderWidthEl.value) ? headerBorderWidthEl.value : '1';
            const headerBorderColor = (headerBorderColorEl && headerBorderColorEl.value) ? headerBorderColorEl.value : '#dee2e6';
            params.append('header_border_width', headerBorderWidth);
            params.append('header_border_color', headerBorderColor);
        }
        
        // 메뉴 폰트 설정 값 추가
        var menuFontSizeEl2 = document.getElementById('menu_font_size');
        var menuFontPaddingEl2 = document.getElementById('menu_font_padding');
        var menuFontWeightEl2 = document.getElementById('menu_font_weight');
        const menuFontSize = (menuFontSizeEl2 && menuFontSizeEl2.value) ? menuFontSizeEl2.value : '1.25rem';
        const menuFontPadding = (menuFontPaddingEl2 && menuFontPaddingEl2.value) ? menuFontPaddingEl2.value : '0.5rem';
        const menuFontWeight = (menuFontWeightEl2 && menuFontWeightEl2.value) ? menuFontWeightEl2.value : '700';
        params.append('menu_font_size', menuFontSize);
        params.append('menu_font_padding', menuFontPadding);
        params.append('menu_font_weight', menuFontWeight);
    } else {
        if (isDark) {
            var darkFooterTextEl = document.querySelector('input[name="color_dark_footer_text"]');
            var darkFooterBgEl = document.querySelector('input[name="color_dark_footer_bg"]');
            const darkFooterText = darkFooterTextEl ? darkFooterTextEl.value : null;
            const darkFooterBg = darkFooterBgEl ? darkFooterBgEl.value : null;
            if (darkFooterText) params.append('color_dark_footer_text', darkFooterText);
            if (darkFooterBg) params.append('color_dark_footer_bg', darkFooterBg);
        } else {
            var lightFooterTextEl = document.querySelector('input[name="color_light_footer_text"]');
            var lightFooterBgEl = document.querySelector('input[name="color_light_footer_bg"]');
            const lightFooterText = lightFooterTextEl ? lightFooterTextEl.value : null;
            const lightFooterBg = lightFooterBgEl ? lightFooterBgEl.value : null;
            if (lightFooterText) params.append('color_light_footer_text', lightFooterText);
            if (lightFooterBg) params.append('color_light_footer_bg', lightFooterBg);
        }
    }
    
    // CSRF 토큰 가져오기
    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;
    
    fetch(url + '?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken || ''
        },
        credentials: 'same-origin'
    })
    .then(function(response) {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(function(text) {
                console.error('Error response:', text);
                throw new Error('Network response was not ok: ' + response.status + ' - ' + text.substring(0, 100));
            });
        }
        return response.json();
    })
    .then(function(data) {
        console.log('Preview response received - hasData:', !!data, 'hasHtml:', !!(data && data.html), 'htmlLength:', data && data.html ? data.html.length : 0);
        
        if (data && data.html) {
            // HTML이 비어있지 않은지 확인
            const htmlContent = data.html.trim();
            if (htmlContent.length > 0) {
                try {
                    // 기존 내용을 먼저 지우고 새로 설정
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = htmlContent;
                    
                    // CSS 파싱 오류 확인을 위해 스타일 태그 검사
                    const styleTags = tempDiv.querySelectorAll('style');
                    let hasStyleError = false;
                    styleTags.forEach(function(style) {
                        try {
                            // 스타일이 유효한지 확인
                            const testEl = document.createElement('div');
                            const styleContent = style.textContent.split('{');
                            if (styleContent.length > 1) {
                                const styleBody = styleContent[1].split('}')[0] || '';
                                testEl.style.cssText = styleBody;
                            }
                        } catch (e) {
                            console.warn('Potential CSS error detected:', e);
                            hasStyleError = true;
                        }
                    });
                    
                    if (!hasStyleError) {
                        container.innerHTML = '';
                        container.innerHTML = htmlContent;
                        console.log('Preview updated successfully, HTML length:', htmlContent.length);
                        
                        // 스타일이 제대로 적용되었는지 확인
                        setTimeout(function() {
                            const navLinks = container.querySelectorAll('.nav-link');
                            console.log('Nav links found:', navLinks.length);
                            if (navLinks.length > 0) {
                                const computedStyle = window.getComputedStyle(navLinks[0]);
                                console.log('First nav-link font-size:', computedStyle.fontSize);
                                if (!computedStyle.fontSize || computedStyle.fontSize === '0px') {
                                    console.error('Font size is invalid, reloading with defaults');
                                    // 기본값으로 다시 로드
                                    updateThemePreview(previewType, previewTheme);
                                }
                            } else {
                                console.warn('No nav links found in preview');
                            }
                        }, 100);
                    } else {
                        console.error('CSS error detected, using fallback');
                        container.innerHTML = htmlContent; // 그래도 시도
                    }
                } catch (e) {
                    console.error('Error setting innerHTML:', e, e.stack);
                    // 에러 발생 시에도 기본 HTML은 표시
                    try {
                        container.innerHTML = htmlContent;
                    } catch (e2) {
                        var errorMsg = (e && e.message) ? String(e.message).replace(/</g, '&lt;').replace(/>/g, '&gt;') : '알 수 없는 오류';
                        container.innerHTML = '<div class="text-danger p-3">미리보기 표시 오류: ' + errorMsg + '</div>';
                    }
                }
            } else {
                console.error('Empty HTML in response');
                container.innerHTML = '<div class="text-warning p-3">미리보기 HTML이 비어있습니다.</div>';
            }
        } else if (data && data.error) {
            console.error('Server error:', data.error);
            container.innerHTML = '<div class="text-danger p-3">미리보기 오류: ' + (data.message || data.error) + '</div>';
        } else {
            console.error('No HTML in response:', data);
            container.innerHTML = '<div class="text-muted p-3">미리보기를 불러올 수 없습니다. (응답 데이터 없음)</div>';
        }
    })
    .catch(function(error) {
        console.error('Preview error:', error);
        var typeEscaped = (previewType || '').replace(/'/g, "\\'");
        var themeEscaped = (previewTheme || '').replace(/'/g, "\\'");
        var errorMessage = error && error.message ? String(error.message) : '알 수 없는 오류';
        container.innerHTML = '<div class="text-danger p-3">미리보기를 불러올 수 없습니다.<br><small>' + errorMessage + '</small><br><button class="btn btn-sm btn-secondary mt-2" onclick="updateThemePreview(\'' + typeEscaped + '\', \'' + themeEscaped + '\')">다시 시도</button></div>';
    });
}

// URL 복사 함수
function copyToClipboard(text, label) {
    navigator.clipboard.writeText(text).then(function() {
        alert(label + '이(가) 클립보드에 복사되었습니다.');
    }, function(err) {
        // 클립보드 API가 지원되지 않는 경우 대체 방법
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            alert(label + '이(가) 클립보드에 복사되었습니다.');
        } catch (err) {
            alert('복사에 실패했습니다. 수동으로 복사해주세요: ' + text);
        }
        document.body.removeChild(textArea);
    });
}

// 모바일 헤더 미리보기 업데이트 함수
function updateMobileHeaderPreview() {
    const previewElement = document.getElementById('mobile_header_preview');
    if (!previewElement) return;
    
    var mobileHeaderThemeEl = document.getElementById('mobile_header_theme');
    var mobileMenuIconEl = document.getElementById('mobile_menu_icon');
    var mobileMenuDirectionEl = document.getElementById('mobile_menu_direction');
    var mobileMenuIconBorderEl = document.getElementById('mobile_menu_icon_border');
    var mobileMenuLoginWidgetEl = document.getElementById('mobile_menu_login_widget');
    const theme = (mobileHeaderThemeEl && mobileHeaderThemeEl.value) ? mobileHeaderThemeEl.value : 'theme1';
    const menuIcon = (mobileMenuIconEl && mobileMenuIconEl.value) ? mobileMenuIconEl.value : 'bi-list';
    const menuDirection = (mobileMenuDirectionEl && mobileMenuDirectionEl.value) ? mobileMenuDirectionEl.value : 'top-to-bottom';
    const menuIconBorder = (mobileMenuIconBorderEl && mobileMenuIconBorderEl.checked) ? '1' : '0';
    const menuLoginWidget = (mobileMenuLoginWidgetEl && mobileMenuLoginWidgetEl.checked) ? '1' : '0';
    
    // AJAX로 미리보기 HTML 가져오기
    const url = '{{ route("admin.settings.preview-mobile-header", ["site" => $site->slug]) }}';
    const params = new URLSearchParams({
        theme: theme,
        menu_icon: menuIcon,
        menu_direction: menuDirection,
        menu_icon_border: menuIconBorder,
        menu_login_widget: menuLoginWidget
    });
    
    var csrfTokenMeta2 = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta2 ? csrfTokenMeta2.getAttribute('content') : null;
    
    previewElement.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    fetch(url + '?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken || ''
        },
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data && data.html) {
            previewElement.innerHTML = data.html;
            
            // 미리보기 스크립트가 자동으로 메뉴를 닫도록 함
            // 스크립트는 mobile-header-preview.blade.php에 포함되어 있음
        } else {
            previewElement.innerHTML = '<div class="text-danger p-3">미리보기를 불러올 수 없습니다.</div>';
        }
    })
    .catch(function(error) {
        console.error('Mobile header preview error:', error);
        previewElement.innerHTML = '<div class="text-danger p-3">미리보기를 불러올 수 없습니다.</div>';
    });
}

$(document).ready(function() {
    // 툴팁 초기화
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // 모바일 헤더 미리보기 초기화
    updateMobileHeaderPreview();
    
    // 모바일 헤더 설정 변경 시 미리보기 업데이트
    $(document).on('change', '.mobile-header-preview-select, #mobile_menu_icon, #mobile_menu_direction, #mobile_menu_icon_border, #mobile_menu_login_widget', function() {
        updateMobileHeaderPreview();
    });
    
    // 헤더 테두리 체크박스 변경 시 설정 표시/숨김
    $('#header_border').on('change', function() {
        if ($(this).is(':checked')) {
            $('#header_border_settings').show();
        } else {
            $('#header_border_settings').hide();
        }
        // 미리보기 업데이트
        const headerSelect = document.getElementById('theme_top');
        if (headerSelect && headerSelect.value) {
            updateThemePreview('header', headerSelect.value);
        }
    });
    
    // 헤더 테두리 두께/컬러 변경 시 미리보기 업데이트
    $('#header_border_width, #header_border_color').on('change', function() {
        const headerSelect = document.getElementById('theme_top');
        if (headerSelect && headerSelect.value) {
            updateThemePreview('header', headerSelect.value);
        }
    });
    
    // 메뉴 폰트 설정 변경 시 미리보기 업데이트
    $('#menu_font_size, #menu_font_padding, #menu_font_weight').on('change', function() {
        const headerSelect = document.getElementById('theme_top');
        if (headerSelect && headerSelect.value) {
            updateThemePreview('header', headerSelect.value);
        }
    });
    
    // 그림자 체크박스 변경 시 미리보기 업데이트
    $('#header_shadow').on('change', function() {
        const headerSelect = document.getElementById('theme_top');
        if (headerSelect && headerSelect.value) {
            updateThemePreview('header', headerSelect.value);
        }
    });
    
    // 테마 form 제출 시 체크박스 처리
    $('#themeForm').on('submit', function(e) {
        // 기존 hidden input 모두 제거
        $('input[name="theme_top_header_show"][type="hidden"]').remove();
        $('input[name="top_header_login_show"][type="hidden"]').remove();
        $('input[name="header_sticky"][type="hidden"]').remove();
        $('input[name="menu_login_show"][type="hidden"]').remove();
        $('input[name="header_shadow"][type="hidden"]').remove();
        $('input[name="header_border"][type="hidden"]').remove();
        
        // 체크되지 않은 체크박스만 hidden input으로 값 전달
        // 체크된 체크박스는 value="1"이 자동으로 전달됨
        if (!$('#theme_top_header_show').is(':checked')) {
            $(this).append('<input type="hidden" name="theme_top_header_show" value="0">');
        }
        
        if (!$('#top_header_login_show').is(':checked')) {
            $(this).append('<input type="hidden" name="top_header_login_show" value="0">');
        }
        
        if (!$('#header_sticky').is(':checked')) {
            $(this).append('<input type="hidden" name="header_sticky" value="0">');
        }
        
        if (!$('#menu_login_show').is(':checked')) {
            $(this).append('<input type="hidden" name="menu_login_show" value="0">');
        }
        
        if (!$('#header_shadow').is(':checked')) {
            $(this).append('<input type="hidden" name="header_shadow" value="0">');
        }
        
        if (!$('#header_border').is(':checked')) {
            $(this).append('<input type="hidden" name="header_border" value="0">');
        }
    });

    // 게시판 form 제출 시 체크박스 처리
    $('#boardForm').on('submit', function(e) {
        // 기존 hidden input 모두 제거
        $('input[name="show_views"][type="hidden"]').remove();
        $('input[name="show_datetime"][type="hidden"]').remove();
        
        // 체크되지 않은 체크박스만 hidden input으로 값 전달 (데스크탑 또는 모바일 중 하나라도 체크되어 있으면 체크된 것으로 간주)
        const showViewsChecked = $('#show_views').is(':checked') || $('#show_views_mobile').is(':checked');
        const showDatetimeChecked = $('#show_datetime').is(':checked') || $('#show_datetime_mobile').is(':checked');
        
        if (!showViewsChecked) {
            $(this).append('<input type="hidden" name="show_views" value="0">');
        }
        
        if (!showDatetimeChecked) {
            $(this).append('<input type="hidden" name="show_datetime" value="0">');
        }
    });

    // 게시판 설정 데스크탑과 모바일 동기화
    document.addEventListener('DOMContentLoaded', function() {
        // 베스트 글 기준 동기화
        const desktopBestPostCriteria = document.getElementById('best_post_criteria');
        const mobileBestPostCriteria = document.getElementById('best_post_criteria_mobile');
        if (desktopBestPostCriteria && mobileBestPostCriteria) {
            desktopBestPostCriteria.addEventListener('change', function() {
                mobileBestPostCriteria.value = this.value;
            });
            mobileBestPostCriteria.addEventListener('change', function() {
                desktopBestPostCriteria.value = this.value;
            });
        }

        // 글쓰기 텀 동기화
        const desktopWriteInterval = document.getElementById('write_interval');
        const mobileWriteInterval = document.getElementById('write_interval_mobile');
        if (desktopWriteInterval && mobileWriteInterval) {
            desktopWriteInterval.addEventListener('change', function() {
                mobileWriteInterval.value = this.value;
            });
            mobileWriteInterval.addEventListener('change', function() {
                desktopWriteInterval.value = this.value;
            });
        }

        // 새글 기준 동기화
        const desktopNewPostHours = document.getElementById('new_post_hours');
        const mobileNewPostHours = document.getElementById('new_post_hours_mobile');
        if (desktopNewPostHours && mobileNewPostHours) {
            desktopNewPostHours.addEventListener('change', function() {
                mobileNewPostHours.value = this.value;
            });
            mobileNewPostHours.addEventListener('change', function() {
                desktopNewPostHours.value = this.value;
            });
        }

        // 조회수 공개 체크박스 동기화
        const desktopShowViews = document.getElementById('show_views');
        const mobileShowViews = document.getElementById('show_views_mobile');
        if (desktopShowViews && mobileShowViews) {
            desktopShowViews.addEventListener('change', function() {
                mobileShowViews.checked = this.checked;
            });
            mobileShowViews.addEventListener('change', function() {
                desktopShowViews.checked = this.checked;
            });
        }

        // 시각 표시 체크박스 동기화
        const desktopShowDatetime = document.getElementById('show_datetime');
        const mobileShowDatetime = document.getElementById('show_datetime_mobile');
        if (desktopShowDatetime && mobileShowDatetime) {
            desktopShowDatetime.addEventListener('change', function() {
                mobileShowDatetime.checked = this.checked;
            });
            mobileShowDatetime.addEventListener('change', function() {
                desktopShowDatetime.checked = this.checked;
            });
        }
    });

    // 기능 ON/OFF form 제출 시 체크박스 처리
    $('#featureForm').on('submit', function(e) {
        // 기존 hidden input 모두 제거
        $('input[name="show_visitor_count"][type="hidden"]').remove();
        $('input[name="email_notification"][type="hidden"]').remove();
        $('input[name="general_login"][type="hidden"]').remove();
        
        // 체크되지 않은 체크박스만 hidden input으로 값 전달 (데스크탑 또는 모바일 중 하나라도 체크되어 있으면 체크된 것으로 간주)
        const showVisitorCountChecked = $('#show_visitor_count').is(':checked') || $('#show_visitor_count_mobile').is(':checked');
        const emailNotificationChecked = $('#email_notification').is(':checked') || $('#email_notification_mobile').is(':checked');
        const generalLoginChecked = $('#general_login').is(':checked') || $('#general_login_mobile').is(':checked');
        
        if (!showVisitorCountChecked) {
            $(this).append('<input type="hidden" name="show_visitor_count" value="0">');
        }
        
        if (!emailNotificationChecked) {
            $(this).append('<input type="hidden" name="email_notification" value="0">');
        }
        
        if (!generalLoginChecked) {
            $(this).append('<input type="hidden" name="general_login" value="0">');
        }
    });

    // 방문자수 증가 버튼 클릭 (데스크탑)
    $('#increaseVisitorBtn').on('click', function(e) {
        e.preventDefault();
        var adjustValue = parseInt($('#visitor_count_adjust').val()) || 0;
        
        if (adjustValue <= 0) {
            alert('1 이상의 숫자를 입력해주세요.');
            return;
        }
        
        if (!confirm('방문자수를 ' + adjustValue + '만큼 증가시키시겠습니까?')) {
            return;
        }
        
        // AJAX로 방문자수 증가 요청
        $.ajax({
            url: '{{ route("admin.settings.increase-visitor", ["site" => $site->slug]) }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                amount: adjustValue
            },
            success: function(response) {
                if (response.success) {
                    alert('방문자수가 ' + adjustValue + '만큼 증가되었습니다.');
                    $('#visitor_count_adjust').val(0);
                    $('#visitor_count_adjust_mobile').val(0);
                } else {
                    alert('방문자수 증가에 실패했습니다: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: function(xhr) {
                var errorMessage = '방문자수 증가에 실패했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    });

    // 방문자수 증가 버튼 클릭 (모바일)
    $('#increaseVisitorBtnMobile').on('click', function(e) {
        e.preventDefault();
        var adjustValue = parseInt($('#visitor_count_adjust_mobile').val()) || 0;
        
        if (adjustValue <= 0) {
            alert('1 이상의 숫자를 입력해주세요.');
            return;
        }
        
        if (!confirm('방문자수를 ' + adjustValue + '만큼 증가시키시겠습니까?')) {
            return;
        }
        
        // AJAX로 방문자수 증가 요청
        $.ajax({
            url: '{{ route("admin.settings.increase-visitor", ["site" => $site->slug]) }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                amount: adjustValue
            },
            success: function(response) {
                if (response.success) {
                    alert('방문자수가 ' + adjustValue + '만큼 증가되었습니다.');
                    $('#visitor_count_adjust').val(0);
                    $('#visitor_count_adjust_mobile').val(0);
                } else {
                    alert('방문자수 증가에 실패했습니다: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: function(xhr) {
                var errorMessage = '방문자수 증가에 실패했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    });

    // 기능 ON/OFF 데스크탑과 모바일 동기화
    document.addEventListener('DOMContentLoaded', function() {
        // 방문자수 표기 체크박스 동기화
        const desktopShowVisitorCount = document.getElementById('show_visitor_count');
        const mobileShowVisitorCount = document.getElementById('show_visitor_count_mobile');
        if (desktopShowVisitorCount && mobileShowVisitorCount) {
            desktopShowVisitorCount.addEventListener('change', function() {
                mobileShowVisitorCount.checked = this.checked;
            });
            mobileShowVisitorCount.addEventListener('change', function() {
                desktopShowVisitorCount.checked = this.checked;
            });
        }

        // 방문자수 변경 입력 동기화
        const desktopVisitorCountAdjust = document.getElementById('visitor_count_adjust');
        const mobileVisitorCountAdjust = document.getElementById('visitor_count_adjust_mobile');
        if (desktopVisitorCountAdjust && mobileVisitorCountAdjust) {
            desktopVisitorCountAdjust.addEventListener('change', function() {
                mobileVisitorCountAdjust.value = this.value;
            });
            mobileVisitorCountAdjust.addEventListener('change', function() {
                desktopVisitorCountAdjust.value = this.value;
            });
        }

        // 이메일 알림 체크박스 동기화
        const desktopEmailNotification = document.getElementById('email_notification');
        const mobileEmailNotification = document.getElementById('email_notification_mobile');
        if (desktopEmailNotification && mobileEmailNotification) {
            desktopEmailNotification.addEventListener('change', function() {
                mobileEmailNotification.checked = this.checked;
            });
            mobileEmailNotification.addEventListener('change', function() {
                desktopEmailNotification.checked = this.checked;
            });
        }

        // 일반 로그인 체크박스 동기화
        const desktopGeneralLogin = document.getElementById('general_login');
        const mobileGeneralLogin = document.getElementById('general_login_mobile');
        if (desktopGeneralLogin && mobileGeneralLogin) {
            desktopGeneralLogin.addEventListener('change', function() {
                mobileGeneralLogin.checked = this.checked;
            });
            mobileGeneralLogin.addEventListener('change', function() {
                desktopGeneralLogin.checked = this.checked;
            });
        }
    });

    // settingsForm 제출 시 로그 및 확인
    $('#settingsForm').on('submit', function(e) {
        console.log('Settings form submitting...');
        
        // 폼 제출 전에 hidden input이 form 내부에 있는지 확인하고 없으면 추가
        var logoFieldsToCheck = ['site_logo', 'site_logo_dark', 'site_favicon', 'og_image'];
        logoFieldsToCheck.forEach(function(fieldName) {
            var $input = $('#' + fieldName);
            if ($input.length > 0) {
                var inForm = $('#settingsForm').find('#' + fieldName).length > 0;
                if (!inForm) {
                    console.log('Moving ' + fieldName + ' into form before submit...');
                    $input.detach().appendTo('#settingsForm');
                }
                console.log(fieldName + ' in form:', $('#settingsForm').find('#' + fieldName).length > 0, 'value:', $input.val());
            } else {
                // hidden input이 없으면 name으로 찾기
                var $inputByName = $('input[name="' + fieldName + '"]');
                if ($inputByName.length > 0) {
                    var inFormByName = $('#settingsForm').find('input[name="' + fieldName + '"]').length > 0;
                    if (!inFormByName) {
                        console.log('Moving ' + fieldName + ' (by name) into form before submit...');
                        $inputByName.detach().appendTo('#settingsForm');
                    }
                    console.log(fieldName + ' (by name) in form:', $('#settingsForm').find('input[name="' + fieldName + '"]').length > 0, 'value:', $inputByName.val());
                }
            }
        });
        
        var formData = new FormData(this);
        
        // 모든 form 데이터 확인
        var allFormData = {};
        for (var pair of formData.entries()) {
            allFormData[pair[0]] = pair[1];
        }
        
        // 로고 관련 필드만 확인
        var logoFields = {
            site_logo: $('#site_logo').val(),
            site_logo_dark: $('#site_logo_dark').val(),
            site_favicon: $('#site_favicon').val(),
            og_image: $('#og_image').val(),
            logo_type: $('#logo_type').val(),
            logo_desktop_size: $('#logo_desktop_size').val(),
            logo_mobile_size: $('#logo_mobile_size').val()
        };
        
        // site_logo가 allFormData에 없으면 추가
        if (!allFormData.hasOwnProperty('site_logo') && logoFields.site_logo) {
            console.log('site_logo not in formData, adding it...');
            var $siteLogoInput = $('#site_logo');
            if ($siteLogoInput.length === 0) {
                // hidden input이 없으면 생성
                $siteLogoInput = $('<input>', {
                    type: 'hidden',
                    name: 'site_logo',
                    id: 'site_logo',
                    value: logoFields.site_logo
                });
                $('#settingsForm').append($siteLogoInput);
                console.log('Created and added site_logo input to form');
            } else {
                // 있으면 form 내부로 이동
                if ($('#settingsForm').find('#site_logo').length === 0) {
                    $siteLogoInput.detach().appendTo('#settingsForm');
                    console.log('Moved site_logo input to form');
                }
            }
            // formData에 추가
            formData.append('site_logo', logoFields.site_logo);
            allFormData['site_logo'] = logoFields.site_logo;
        }
        
        // localStorage에 로그 저장 (새로고침 후에도 확인 가능)
        var logData = {
            timestamp: new Date().toISOString(),
            formAction: this.action,
            formMethod: this.method,
            allFormData: allFormData,
            logoFields: logoFields,
            hiddenInputs: {
                site_logo: $('#site_logo').val(),
                site_logo_dark: $('#site_logo_dark').val(),
                site_favicon: $('#site_favicon').val(),
                og_image: $('#og_image').val()
            },
            // site_logo가 allFormData에 포함되어 있는지 확인
            site_logo_in_formData: allFormData.hasOwnProperty('site_logo') ? allFormData['site_logo'] : 'NOT FOUND',
            // hidden input 요소 확인
            site_logo_element: {
                exists: $('#site_logo').length > 0,
                value: $('#site_logo').val(),
                name: $('#site_logo').attr('name'),
                id: $('#site_logo').attr('id')
            }
        };
        
        localStorage.setItem('lastSettingsFormSubmit', JSON.stringify(logData));
        console.log('Settings form submit data saved to localStorage');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);
        console.log('All form data:', allFormData);
        console.log('Logo-related fields:', logoFields);
    });
    
    // 페이지 로드 시 이전 제출 로그 확인
    $(document).ready(function() {
        var lastSubmit = localStorage.getItem('lastSettingsFormSubmit');
        if (lastSubmit) {
            try {
                var logData = JSON.parse(lastSubmit);
                console.log('=== Last Settings Form Submit (from localStorage) ===');
                console.log('Timestamp:', logData.timestamp);
                console.log('Form Action:', logData.formAction);
                console.log('Form Method:', logData.formMethod);
                console.log('All Form Data:', logData.allFormData);
                console.log('Logo Fields:', logData.logoFields);
                console.log('Hidden Inputs:', logData.hiddenInputs);
                console.log('==================================================');
            } catch (e) {
                console.error('Error parsing last submit log:', e);
            }
        }
    });

    // 로고 타입 변경 시 알림 표시/숨김 및 이미지 업로드 영역 제어
    function toggleLogoType() {
        var logoType = $('#logo_type').val();
        var $logoImageArea = $('[data-type="logo"]').closest('td');
        var $logoDarkImageArea = $('[data-type="logo_dark"]').closest('td');
        var $desktopSize = $('#logo_desktop_size').closest('td');
        var $mobileSize = $('#logo_mobile_size').closest('td');
        
        if (logoType === 'text') {
            $('#logo-text-notice').slideDown();
            // td는 유지하되 내용만 숨기기 (테이블 레이아웃 유지)
            $logoImageArea.css('visibility', 'hidden').css('min-width', $logoImageArea.width() + 'px');
            $logoDarkImageArea.css('visibility', 'hidden').css('min-width', $logoDarkImageArea.width() + 'px');
            $desktopSize.css('visibility', 'hidden').css('min-width', $desktopSize.width() + 'px');
            $mobileSize.css('visibility', 'hidden').css('min-width', $mobileSize.width() + 'px');
        } else {
            $('#logo-text-notice').slideUp();
            // visibility와 min-width 제거하여 다시 표시
            $logoImageArea.css('visibility', '').css('min-width', '');
            $logoDarkImageArea.css('visibility', '').css('min-width', '');
            $desktopSize.css('visibility', '').css('min-width', '');
            $mobileSize.css('visibility', '').css('min-width', '');
        }
    }

    $('#logo_type').on('change', toggleLogoType);
    toggleLogoType(); // 초기 상태 확인
});
</script>
@endpush
