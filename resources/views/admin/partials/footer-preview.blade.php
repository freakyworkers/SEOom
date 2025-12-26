@php
    // 실제 설정된 색상 사용
    $footerBgColor = $footerBg ?? '#495057';
    $footerTextColor = $footerText ?? '#ffffff';
    
    // 기본값 설정
    $theme = $theme ?? 'theme03';
    
    // 방문자수 표기 설정 확인 (기본값: 표시)
    $showVisitorCount = isset($settings['show_visitor_count']) ? ($settings['show_visitor_count'] == '1') : true;
    
    // 방문자 수 (미리보기용 더미 데이터)
    $todayVisitors = 123;
    $totalVisitors = 4567;
    
    // 사이트 로고 정보: 다크 모드일 때 다크 모드 로고 사용
    $themeDarkMode = $settings['theme_dark_mode'] ?? 'light';
    $isDark = $themeDarkMode === 'dark';
    $siteLogo = $isDark ? ($settings['site_logo_dark'] ?? $settings['site_logo'] ?? '') : ($settings['site_logo'] ?? '');
    $logoType = $settings['logo_type'] ?? 'image';
    $siteName = $settings['site_name'] ?? ($site->name ?? 'SEOom Builder');
    $logoDesktopSize = $settings['logo_desktop_size'] ?? '300';
    $logoMobileSize = $settings['logo_mobile_size'] ?? '200';
@endphp

<style>
    .footer-preview-wrapper {
        transform: scale(0.85);
        transform-origin: top left;
        width: 117.65%;
        margin-bottom: -15%;
    }
    .footer-preview-wrapper footer {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
</style>

<div class="footer-preview-wrapper">
    @if($theme === 'theme02')
        {{-- 테마02: 중앙 정렬 레이아웃 --}}
        <footer style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }}; border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <div class="text-center">
                    {{-- 사이트 로고 --}}
                    <div class="mb-2">
                        @if($logoType === 'text' || empty($siteLogo))
                            <h5 style="color: {{ $footerTextColor }} !important; margin: 0; font-size: 1rem;">{{ $siteName }}</h5>
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: 150px; height: auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <h5 style="color: {{ $footerTextColor }} !important; margin: 0; font-size: 1rem; display: none;">{{ $siteName }}</h5>
                        @endif
                    </div>
                    
                    {{-- 이용약관 | 개인정보 처리 방침 | 사이트맵 | RSS --}}
                    <div class="mb-2" style="font-size: 0.8rem;">
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">이용약관</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">개인정보 처리 방침</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">사이트맵</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">RSS</a>
                    </div>
                    
                    {{-- 방문자 수 --}}
                    @if($showVisitorCount)
                    <div class="mb-2" style="font-size: 0.8rem;">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            오늘 : <strong>{{ number_format($todayVisitors) }}</strong> / 전체 : <strong>{{ number_format($totalVisitors) }}</strong>
                        </p>
                    </div>
                    @endif
                    
                    {{-- Copyright --}}
                    <div class="mb-1" style="font-size: 0.75rem;">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            &copy; {{ date('Y') }} All rights reserved.
                        </p>
                    </div>
                    
                    {{-- Powered by --}}
                    <div style="font-size: 0.75rem;">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            Powered by <strong>SEOom Builder</strong>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    @elseif($theme === 'theme03')
        {{-- 테마03: 좌측 정렬 레이아웃 --}}
        <footer style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }}; border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <div class="text-start">
                    {{-- 사이트 로고 --}}
                    <div class="mb-2">
                        @if($logoType === 'text' || empty($siteLogo))
                            <h5 style="color: {{ $footerTextColor }} !important; margin: 0; font-size: 1rem;">{{ $siteName }}</h5>
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: 150px; height: auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <h5 style="color: {{ $footerTextColor }} !important; margin: 0; font-size: 1rem; display: none;">{{ $siteName }}</h5>
                        @endif
                    </div>
                    
                    {{-- 이용약관 | 개인정보처리방침 | 사이트맵 | RSS | 방문자 수 (한 줄) --}}
                    <div class="mb-2" style="font-size: 0.8rem;">
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">이용약관</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">개인정보처리방침</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">사이트맵</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">RSS</a>
                        @if($showVisitorCount)
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.3rem;">
                            오늘 <strong>{{ number_format($todayVisitors) }}</strong> / 전체 <strong>{{ number_format($totalVisitors) }}</strong>
                        </span>
                        @endif
                    </div>
                    
                    {{-- Copyright --}}
                    <div class="mb-1" style="font-size: 0.75rem;">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            ⓒ All rights reserved.
                        </p>
                    </div>
                    
                    {{-- Powered by --}}
                    <div style="font-size: 0.75rem;">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            Powered by <strong>SEOom Builder</strong>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    @elseif($theme === 'theme04')
        {{-- 테마04: 로고 좌측, 링크들 중앙 정렬, 하단 텍스트 중앙 정렬 레이아웃 --}}
        <footer style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }}; border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                {{-- 첫 번째 줄: 로고 좌측, 링크들/방문자 수 로고 높이 중앙 정렬 --}}
                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1" style="font-size: 0.8rem;">
                    <div class="d-flex align-items-center">
                        @if($logoType === 'text' || empty($siteLogo))
                            <h5 style="color: {{ $footerTextColor }} !important; margin: 0; font-size: 1rem; line-height: 1.2;">{{ $siteName }}</h5>
                        @else
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: 120px; height: auto; max-height: 40px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <h5 style="color: {{ $footerTextColor }} !important; margin: 0; font-size: 1rem; line-height: 1.2; display: none;">{{ $siteName }}</h5>
                        @endif
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-1" style="line-height: 1.2;">
                        <div class="d-flex align-items-center">
                            <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.2rem; white-space: nowrap;">이용약관</a>
                            <span style="color: {{ $footerTextColor }} !important; margin: 0 0.2rem;">|</span>
                            <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.2rem; white-space: nowrap;">개인정보처리방침</a>
                            <span style="color: {{ $footerTextColor }} !important; margin: 0 0.2rem;">|</span>
                            <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.2rem; white-space: nowrap;">사이트맵</a>
                            <span style="color: {{ $footerTextColor }} !important; margin: 0 0.2rem;">|</span>
                            <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.2rem; white-space: nowrap;">RSS</a>
                        </div>
                        @if($showVisitorCount)
                        <span style="color: {{ $footerTextColor }} !important; white-space: nowrap; margin-left: 0.2rem;">
                            오늘 : <strong>{{ number_format($todayVisitors) }}</strong> / 전체 : <strong>{{ number_format($totalVisitors) }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                
                {{-- 두 번째 줄: All rights reserved. (우측 정렬) --}}
                <div class="text-end mb-1" style="font-size: 0.75rem;">
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        ⓒ {{ $siteName }} All rights reserved.
                    </p>
                </div>
                
                {{-- 세 번째 줄: Powered by SEOom Builder (우측 정렬) --}}
                <div class="text-end" style="font-size: 0.75rem;">
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        Powered by <strong>SEOom Builder</strong>
                    </p>
                </div>
            </div>
        </footer>
    @elseif($theme === 'theme05')
        {{-- 테마05: 중앙 정렬 레이아웃 (로고 없음) --}}
        <footer style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }}; border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <div class="text-center">
                    {{-- 방문자 수 --}}
                    @if($showVisitorCount)
                    <div class="mb-1" style="font-size: 0.8rem;">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            오늘 : <strong>{{ number_format($todayVisitors) }}</strong> / 전체 : <strong>{{ number_format($totalVisitors) }}</strong>
                        </p>
                    </div>
                    @endif
                    
                    {{-- 이용약관 | 개인정보 처리방침 | 사이트맵 | RSS --}}
                    <div class="mb-1" style="font-size: 0.8rem;">
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">이용약관</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">개인정보 처리방침</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">사이트맵</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.3rem;">RSS</a>
                    </div>
                    
                    {{-- Copyright --}}
                    <div class="mb-1" style="font-size: 0.75rem;">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            ⓒ {{ $siteName }} All rights reserved.
                        </p>
                    </div>
                    
                    {{-- Powered by --}}
                    <div style="font-size: 0.75rem;">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            Powered by <strong>SEOom Builder</strong>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    @else
        {{-- 기존 테마 (theme01) --}}
        <footer style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }}; border-radius: 0.375rem;">
            <div class="container-fluid px-3">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.
                        </p>
                    </div>
                    @if($showVisitorCount)
                    <div class="col-md-4 text-center">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            오늘 방문자 : <strong>{{ number_format($todayVisitors) }}</strong>명 
                            전체 방문자 : <strong>{{ number_format($totalVisitors) }}</strong>명
                        </p>
                    </div>
                    @else
                    <div class="col-md-4"></div>
                    @endif
                    <div class="col-md-4 text-md-end">
                        <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                            Powered by <strong>SEOom Builder</strong>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    @endif
</div>

