@php
    $theme = $theme ?? 'theme03';
    
    // 실제 설정된 색상 사용
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $isDark = $themeDarkMode === 'dark';
    
    $footerTextColor = $isDark ? $site->getSetting('color_dark_footer_text', '#ffffff') : $site->getSetting('color_light_footer_text', '#000000');
    $footerBgColor = $isDark ? $site->getSetting('color_dark_footer_bg', '#000000') : $site->getSetting('color_light_footer_bg', '#f8f9fa');
    
    // 방문자수 표기 설정 확인 (기본값: 표시)
    $showVisitorCount = $site->getSetting('show_visitor_count', '1') == '1';
    
    // 방문자 수 항상 가져오기 (표시 여부와 관계없이)
    $todayVisitors = \App\Models\Visitor::getTodayCount($site->id);
    $totalVisitors = \App\Models\Visitor::getTotalCount($site->id);
    
    // 사이트 로고 정보: 다크 모드일 때 다크 모드 로고 사용
    $siteLogo = $isDark ? ($site->getSetting('site_logo_dark', '') ?: $site->getSetting('site_logo', '')) : $site->getSetting('site_logo', '');
    $logoType = $site->getSetting('logo_type', 'image');
    $siteName = $site->getSetting('site_name', $site->name ?? 'SEOom Builder');
    $logoDesktopSize = $site->getSetting('logo_desktop_size', '300');
    $logoMobileSize = $site->getSetting('logo_mobile_size', '200');
    
    // 회사정보
    $companyRepresentative = $site->getSetting('company_representative', '');
    $companyContact = $site->getSetting('company_contact', '');
    $companyAddress = $site->getSetting('company_address', '');
    $companyRegistrationNumber = $site->getSetting('company_registration_number', '');
    $companyTelecomNumber = $site->getSetting('company_telecom_number', '');
    $companyAdditionalInfo = $site->getSetting('company_additional_info', '');
    
    // 회사정보가 있는지 확인
    $hasCompanyInfo = !empty($companyRepresentative) || !empty($companyContact) || !empty($companyAddress) 
                     || !empty($companyRegistrationNumber) || !empty($companyTelecomNumber) || !empty($companyAdditionalInfo);
    
    // 모바일 메뉴가 있는지 확인
    $hasMobileMenu = false;
    if (\Illuminate\Support\Facades\Schema::hasTable('mobile_menus')) {
        $mobileMenuCount = \App\Models\MobileMenu::where('site_id', $site->id)->count();
        $hasMobileMenu = $mobileMenuCount > 0;
    }
    
    // Powered by SEOom Builder 표시 여부
    $hidePoweredBy = $site->getSetting('hide_powered_by', '0') == '1';
@endphp

@if($theme === 'theme02')
    {{-- 테마02: 중앙 정렬 레이아웃 --}}
    <footer class="bg-white py-4 mt-5 mobile-footer-padding" style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }};">
        <div class="container">
            <div class="text-center">
                {{-- 사이트 로고 --}}
                <div class="mb-3">
                    @if($logoType === 'text' || empty($siteLogo))
                        <h4 style="color: {{ $footerTextColor }} !important; margin: 0;">{{ $siteName }}</h4>
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoDesktopSize }}px; height: auto;" class="d-none d-md-inline" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; height: auto;" class="d-md-none" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <h4 style="color: {{ $footerTextColor }} !important; margin: 0; display: none;">{{ $siteName }}</h4>
                    @endif
                </div>
                
                {{-- 이용약관 | 개인정보 처리 방침 | 사이트맵 | RSS --}}
                <div class="mb-3">
                    <a href="#" class="footer-link-terms" data-bs-toggle="modal" data-bs-target="#termsModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem; cursor: pointer;">이용약관</a>
                    <span style="color: {{ $footerTextColor }} !important;">|</span>
                    <a href="#" class="footer-link-privacy" data-bs-toggle="modal" data-bs-target="#privacyModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem; cursor: pointer;">개인정보 처리 방침</a>
                    <span style="color: {{ $footerTextColor }} !important;">|</span>
                    <a href="/sitemap.xml" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem;">사이트맵</a>
                    <span style="color: {{ $footerTextColor }} !important;">|</span>
                    <a href="{{ route('rss', ['site' => $site->slug]) }}" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem;">RSS</a>
                </div>
                
                {{-- 회사정보 --}}
                @if($hasCompanyInfo)
                <div class="mb-3" style="font-size: 0.9rem; line-height: 1.6;">
                    @if(!empty($companyRepresentative))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">대표자 : {{ $companyRepresentative }}</span>
                    @endif
                    @if(!empty($companyContact))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">연락처 : {{ $companyContact }}</span>
                    @endif
                    @if(!empty($companyAddress))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">주소 : {{ $companyAddress }}</span>
                    @endif
                    @if(!empty($companyRegistrationNumber))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">사업자등록번호 : {{ $companyRegistrationNumber }}</span>
                    @endif
                    @if(!empty($companyTelecomNumber))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">통신판매업신고 번호 : {{ $companyTelecomNumber }}</span>
                    @endif
                    @if(!empty($companyAdditionalInfo))
                        <div style="color: {{ $footerTextColor }} !important; margin-top: 0.5rem;">
                            {!! $companyAdditionalInfo !!}
                        </div>
                    @endif
                </div>
                @endif
                
                {{-- 방문자 수 --}}
                @if($showVisitorCount)
                <div class="mb-3">
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        오늘 : <strong>{{ number_format($todayVisitors) }}</strong> / 전체 : <strong>{{ number_format($totalVisitors) }}</strong>
                    </p>
                </div>
                @endif
                
                {{-- Copyright --}}
                <div class="mb-2">
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        &copy; {{ date('Y') }} {{ $siteName }} All rights reserved.
                    </p>
                </div>
                
                {{-- Powered by --}}
                @if(!$hidePoweredBy)
                <div>
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        Powered by <a href="https://seoomweb.com" target="_blank" style="color: {{ $footerTextColor }} !important; text-decoration: none;"><strong>SEOom Builder</strong></a>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </footer>
@elseif($theme === 'theme03')
    {{-- 테마03: 좌측 정렬 레이아웃 --}}
    <footer class="bg-white py-4 mt-5 mobile-footer-padding" style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }};">
        <div class="container">
            <div class="text-start">
                {{-- 사이트 로고 --}}
                <div class="mb-3">
                    @if($logoType === 'text' || empty($siteLogo))
                        <h4 style="color: {{ $footerTextColor }} !important; margin: 0;">{{ $siteName }}</h4>
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoDesktopSize }}px; height: auto;" class="d-none d-md-inline" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; height: auto;" class="d-md-none" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <h4 style="color: {{ $footerTextColor }} !important; margin: 0; display: none;">{{ $siteName }}</h4>
                    @endif
                </div>
                
                    {{-- 이용약관 | 개인정보처리방침 | 사이트맵 | RSS | 방문자 수 (한 줄) --}}
                    <div class="mb-3">
                        <a href="#" class="footer-link-terms" data-bs-toggle="modal" data-bs-target="#termsModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem; cursor: pointer;">이용약관</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="#" class="footer-link-privacy" data-bs-toggle="modal" data-bs-target="#privacyModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem; cursor: pointer;">개인정보처리방침</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="/sitemap.xml" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem;">사이트맵</a>
                        <span style="color: {{ $footerTextColor }} !important;">|</span>
                        <a href="{{ route('rss', ['site' => $site->slug]) }}" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem;">RSS</a>
                    @if($showVisitorCount)
                    <span style="color: {{ $footerTextColor }} !important;">|</span>
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">
                        오늘 <strong>{{ number_format($todayVisitors) }}</strong> / 전체 <strong>{{ number_format($totalVisitors) }}</strong>
                    </span>
                    @endif
                </div>
                
                {{-- 회사정보 --}}
                @if($hasCompanyInfo)
                <div class="mb-2 text-start" style="font-size: 0.9rem; line-height: 1.6;">
                    @if(!empty($companyRepresentative))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">대표자 : {{ $companyRepresentative }}</span>
                    @endif
                    @if(!empty($companyContact))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">연락처 : {{ $companyContact }}</span>
                    @endif
                    @if(!empty($companyAddress))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">주소 : {{ $companyAddress }}</span>
                    @endif
                    @if(!empty($companyRegistrationNumber))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">사업자등록번호 : {{ $companyRegistrationNumber }}</span>
                    @endif
                    @if(!empty($companyTelecomNumber))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">통신판매업신고 번호 : {{ $companyTelecomNumber }}</span>
                    @endif
                    @if(!empty($companyAdditionalInfo))
                        <div style="color: {{ $footerTextColor }} !important; margin-top: 0.5rem;">
                            {!! $companyAdditionalInfo !!}
                        </div>
                    @endif
                </div>
                @endif
                
                {{-- Copyright --}}
                <div class="mb-2">
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        ⓒ {{ $siteName }} All rights reserved.
                    </p>
                </div>
                
                {{-- Powered by --}}
                @if(!$hidePoweredBy)
                <div>
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        Powered by <a href="https://seoomweb.com" target="_blank" style="color: {{ $footerTextColor }} !important; text-decoration: none;"><strong>SEOom Builder</strong></a>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </footer>
@elseif($theme === 'theme04')
    {{-- 테마04: 로고 좌측, 링크들 중앙 정렬, 하단 텍스트 중앙 정렬 레이아웃 --}}
    <footer class="bg-white py-4 mt-5 mobile-footer-padding" style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }};">
        <div class="container">
            {{-- 첫 번째 줄: 로고 좌측, 링크들/방문자 수 로고 높이 중앙 정렬 --}}
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    @if($logoType === 'text' || empty($siteLogo))
                        <h4 style="color: {{ $footerTextColor }} !important; margin: 0; line-height: 1.2;">{{ $siteName }}</h4>
                    @else
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoDesktopSize }}px; height: auto; max-height: 50px;" class="d-none d-md-inline" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" style="max-width: {{ $logoMobileSize }}px; height: auto; max-height: 40px;" class="d-md-none" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <h4 style="color: {{ $footerTextColor }} !important; margin: 0; line-height: 1.2; display: none;">{{ $siteName }}</h4>
                    @endif
                </div>
                <div class="d-flex align-items-center flex-wrap gap-1" style="line-height: 1.2;">
                    <div class="d-flex align-items-center">
                        <a href="#" class="footer-link-terms" data-bs-toggle="modal" data-bs-target="#termsModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.25rem; white-space: nowrap; cursor: pointer;">이용약관</a>
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.25rem;">|</span>
                        <a href="#" class="footer-link-privacy" data-bs-toggle="modal" data-bs-target="#privacyModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.25rem; white-space: nowrap; cursor: pointer;">개인정보처리방침</a>
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.25rem;">|</span>
                        <a href="/sitemap.xml" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.25rem; white-space: nowrap;">사이트맵</a>
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.25rem;">|</span>
                        <a href="{{ route('rss', ['site' => $site->slug]) }}" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.25rem; white-space: nowrap;">RSS</a>
                    </div>
                    @if($showVisitorCount)
                    <span style="color: {{ $footerTextColor }} !important; white-space: nowrap; margin-left: 0.25rem;">
                        오늘 : <strong>{{ number_format($todayVisitors) }}</strong> / 전체 : <strong>{{ number_format($totalVisitors) }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            
            {{-- 회사정보 --}}
            @if($hasCompanyInfo)
            <div class="mb-2 text-end" style="font-size: 0.9rem; line-height: 1.6;">
                @if(!empty($companyRepresentative))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">대표자 : {{ $companyRepresentative }}</span>
                @endif
                @if(!empty($companyContact))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">연락처 : {{ $companyContact }}</span>
                @endif
                @if(!empty($companyAddress))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">주소 : {{ $companyAddress }}</span>
                @endif
                @if(!empty($companyRegistrationNumber))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">사업자등록번호 : {{ $companyRegistrationNumber }}</span>
                @endif
                @if(!empty($companyTelecomNumber))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">통신판매업신고 번호 : {{ $companyTelecomNumber }}</span>
                @endif
                @if(!empty($companyAdditionalInfo))
                    <div style="color: {{ $footerTextColor }} !important; margin-top: 0.5rem;">
                        {{ $companyAdditionalInfo }}
                    </div>
                @endif
            </div>
            @endif
            
            {{-- 두 번째 줄: All rights reserved. (우측 정렬) --}}
            <div class="text-end mb-1">
                <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                    ⓒ {{ $siteName }} All rights reserved.
                </p>
            </div>
            
            {{-- 세 번째 줄: Powered by SEOom Builder (우측 정렬) --}}
            @if(!$hidePoweredBy)
            <div class="text-end">
                <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                    Powered by <a href="https://seoomweb.com" target="_blank" style="color: {{ $footerTextColor }} !important; text-decoration: none;"><strong>SEOom Builder</strong></a>
                </p>
            </div>
            @endif
        </div>
    </footer>
@elseif($theme === 'theme05')
    {{-- 테마05: 중앙 정렬 레이아웃 (로고 없음) --}}
    <footer class="bg-white py-4 mt-5 mobile-footer-padding" style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }};">
        <div class="container">
            <div class="text-center">
                {{-- 방문자 수 --}}
                @if($showVisitorCount)
                <div class="mb-2">
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        오늘 : <strong>{{ number_format($todayVisitors) }}</strong> / 전체 : <strong>{{ number_format($totalVisitors) }}</strong>
                    </p>
                </div>
                @endif
                
                {{-- 이용약관 | 개인정보 처리방침 | 사이트맵 | RSS --}}
                <div class="mb-2">
                    <a href="#" class="footer-link-terms" data-bs-toggle="modal" data-bs-target="#termsModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem; cursor: pointer;">이용약관</a>
                    <span style="color: {{ $footerTextColor }} !important;">|</span>
                    <a href="#" class="footer-link-privacy" data-bs-toggle="modal" data-bs-target="#privacyModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem; cursor: pointer;">개인정보 처리방침</a>
                    <span style="color: {{ $footerTextColor }} !important;">|</span>
                    <a href="{{ route('sitemap', ['site' => $site->slug]) }}" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem;">사이트맵</a>
                    <span style="color: {{ $footerTextColor }} !important;">|</span>
                    <a href="{{ route('rss', ['site' => $site->slug]) }}" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem;">RSS</a>
                </div>
                
                {{-- 회사정보 --}}
                @if($hasCompanyInfo)
                <div class="mb-2" style="font-size: 0.9rem; line-height: 1.6;">
                    @if(!empty($companyRepresentative))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">대표자 : {{ $companyRepresentative }}</span>
                    @endif
                    @if(!empty($companyContact))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">연락처 : {{ $companyContact }}</span>
                    @endif
                    @if(!empty($companyAddress))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">주소 : {{ $companyAddress }}</span>
                    @endif
                    @if(!empty($companyRegistrationNumber))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">사업자등록번호 : {{ $companyRegistrationNumber }}</span>
                    @endif
                    @if(!empty($companyTelecomNumber))
                        <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">통신판매업신고 번호 : {{ $companyTelecomNumber }}</span>
                    @endif
                    @if(!empty($companyAdditionalInfo))
                        <div style="color: {{ $footerTextColor }} !important; margin-top: 0.5rem;">
                            {!! $companyAdditionalInfo !!}
                        </div>
                    @endif
                </div>
                @endif
                
                {{-- Copyright --}}
                <div class="mb-2">
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        ⓒ {{ $siteName }} All rights reserved.
                    </p>
                </div>
                
                {{-- Powered by --}}
                @if(!$hidePoweredBy)
                <div>
                    <p class="mb-0" style="color: {{ $footerTextColor }} !important;">
                        Powered by <a href="https://seoomweb.com" target="_blank" style="color: {{ $footerTextColor }} !important; text-decoration: none;"><strong>SEOom Builder</strong></a>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </footer>
@else
    {{-- 기존 테마 (theme01) --}}
    <footer class="bg-white py-4 mt-5 mobile-footer-padding" style="background-color: {{ $footerBgColor }} !important; color: {{ $footerTextColor }};">
        <div class="container">
            {{-- 이용약관 | 개인정보처리방침 | 사이트맵 | RSS --}}
            <div class="mb-3 text-center">
                <a href="#" class="footer-link-terms" data-bs-toggle="modal" data-bs-target="#termsModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem; cursor: pointer;">이용약관</a>
                <span style="color: {{ $footerTextColor }} !important;">|</span>
                <a href="#" class="footer-link-privacy" data-bs-toggle="modal" data-bs-target="#privacyModal" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem; cursor: pointer;">개인정보처리방침</a>
                <span style="color: {{ $footerTextColor }} !important;">|</span>
                <a href="{{ route('sitemap', ['site' => $site->slug]) }}" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem;">사이트맵</a>
                <span style="color: {{ $footerTextColor }} !important;">|</span>
                <a href="{{ route('rss', ['site' => $site->slug]) }}" style="color: {{ $footerTextColor }} !important; text-decoration: none; margin: 0 0.5rem;">RSS</a>
            </div>
            
            {{-- 회사정보 --}}
            @if($hasCompanyInfo)
            <div class="mb-3 text-center" style="font-size: 0.9rem; line-height: 1.6;">
                @if(!empty($companyRepresentative))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">대표자 : {{ $companyRepresentative }}</span>
                @endif
                @if(!empty($companyContact))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">연락처 : {{ $companyContact }}</span>
                @endif
                @if(!empty($companyAddress))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">주소 : {{ $companyAddress }}</span>
                @endif
                @if(!empty($companyRegistrationNumber))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">사업자등록번호 : {{ $companyRegistrationNumber }}</span>
                @endif
                @if(!empty($companyTelecomNumber))
                    <span style="color: {{ $footerTextColor }} !important; margin: 0 0.5rem;">통신판매업신고 번호 : {{ $companyTelecomNumber }}</span>
                @endif
                @if(!empty($companyAdditionalInfo))
                    <div style="color: {{ $footerTextColor }} !important; margin-top: 0.5rem;">
                        {{ $companyAdditionalInfo }}
                    </div>
                @endif
            </div>
            @endif
            
            <div class="row align-items-center">
                <div class="col-md-4">
                    <p class="text-muted mb-0" style="color: {{ $footerTextColor }} !important;">
                        &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.
                    </p>
                </div>
                @if($showVisitorCount)
                <div class="col-md-4 text-center">
                    <p class="text-muted mb-0" style="color: {{ $footerTextColor }} !important;">
                        오늘 방문자 : <strong>{{ number_format($todayVisitors) }}</strong>명 
                        전체 방문자 : <strong>{{ number_format($totalVisitors) }}</strong>명
                    </p>
                </div>
                @else
                <div class="col-md-4"></div>
                @endif
                @if(!$hidePoweredBy)
                <div class="col-md-4 text-md-end">
                    <p class="text-muted mb-0" style="color: {{ $footerTextColor }} !important;">
                        Powered by <a href="https://seoomweb.com" target="_blank" style="color: {{ $footerTextColor }} !important; text-decoration: none;"><strong>SEOom Builder</strong></a>
                    </p>
                </div>
                @else
                <div class="col-md-4"></div>
                @endif
            </div>
        </div>
    </footer>
@endif

@if($hasMobileMenu)
<style>
/* 모바일에서 모바일 메뉴가 있을 때 푸터 하단 패딩 추가 */
@media (max-width: 767.98px) {
    footer.mobile-footer-padding,
    footer.bg-white {
        padding-bottom: 100px !important;
    }
}
</style>
@endif

