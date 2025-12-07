<?php

namespace App\Http\Controllers;

use App\Services\SiteSettingService;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    protected $settingService;

    public function __construct(SiteSettingService $settingService)
    {
        $this->settingService = $settingService;
        $this->middleware('auth')->except(['getTermsOfService', 'getPrivacyPolicy']);
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        })->except(['getTermsOfService', 'getPrivacyPolicy']);
    }

    /**
     * Display site settings.
     */
    public function index(Site $site)
    {
        // 최신 데이터를 가져오기 위해 사이트 객체 새로고침
        $site->refresh();
        
        $settings = $this->settingService->getSettingsBySite($site->id);
        
        // 미리보기용 데이터 준비
        $themeDarkMode = $settings['theme_dark_mode'] ?? 'light';
        $isDark = $themeDarkMode === 'dark';
        
        // 헤더 미리보기 HTML 생성
        $headerTheme = $settings['theme_top'] ?? 'design1';
        $headerSiteName = $settings['site_name'] ?? $site->name ?? 'SEOom Builder';
        $headerSiteLogo = $settings['site_logo'] ?? '';
        $headerLogoType = $settings['logo_type'] ?? 'image';
        $headerLogoDesktopSize = $settings['logo_desktop_size'] ?? '300';
        $headerLogoMobileSize = $settings['logo_mobile_size'] ?? '200';
        $headerText = $isDark ? ($settings['color_dark_header_text'] ?? '#ffffff') : ($settings['color_light_header_text'] ?? '#000000');
        $headerBg = $isDark ? ($settings['color_dark_header_bg'] ?? '#000000') : ($settings['color_light_header_bg'] ?? '#ffffff');
        
        try {
            // 포인트 컬러 설정
            $pointColor = $isDark ? ($settings['color_dark_point_main'] ?? '#ffffff') : ($settings['color_light_point_main'] ?? '#0d6efd');
            
            $headerPreviewHtml = view('admin.partials.header-preview', [
                'theme' => $headerTheme,
                'site' => $site,
                'siteName' => $headerSiteName,
                'siteLogo' => $headerSiteLogo,
                'logoType' => $headerLogoType,
                'logoDesktopSize' => $headerLogoDesktopSize,
                'logoMobileSize' => $headerLogoMobileSize,
                'settings' => $settings,
                'headerText' => $headerText,
                'headerBg' => $headerBg,
                'themeTopHeaderShow' => $settings['theme_top_header_show'] ?? '0',
                'topHeaderLoginShow' => $settings['top_header_login_show'] ?? '0',
                'menuLoginShow' => $settings['menu_login_show'] ?? '0',
                'headerSticky' => $settings['header_sticky'] ?? '0',
                'themeDarkMode' => $themeDarkMode,
                'pointColor' => $pointColor,
                'headerShadow' => $settings['header_shadow'] ?? '0',
                'headerBorder' => $settings['header_border'] ?? '0',
                'headerBorderWidth' => $settings['header_border_width'] ?? '1',
                'headerBorderColor' => $settings['header_border_color'] ?? '#dee2e6',
                'menuFontSize' => $settings['menu_font_size'] ?? '1.25rem',
                'menuFontPadding' => $settings['menu_font_padding'] ?? '0.5rem',
                'menuFontWeight' => $settings['menu_font_weight'] ?? '700'
            ])->render();
            
            // 디버깅: 빈 HTML인지 확인
            if (empty(trim(strip_tags($headerPreviewHtml)))) {
                \Log::warning('Header preview HTML is empty', [
                    'theme' => $headerTheme,
                    'siteName' => $headerSiteName
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Header preview render error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $headerPreviewHtml = '<div class="text-danger p-3">미리보기 렌더링 오류: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        // 푸터 미리보기 HTML 생성
        $footerTheme = $settings['theme_bottom'] ?? 'theme03';
        $footerText = $isDark ? ($settings['color_dark_footer_text'] ?? '#ffffff') : ($settings['color_light_footer_text'] ?? '#000000');
        $footerBg = $isDark ? ($settings['color_dark_footer_bg'] ?? '#000000') : ($settings['color_light_footer_bg'] ?? '#f8f9fa');
        
        try {
            $footerPreviewHtml = view('admin.partials.footer-preview', [
                'theme' => $footerTheme,
                'site' => $site,
                'settings' => $settings,
                'footerText' => $footerText,
                'footerBg' => $footerBg
            ])->render();
            
            // 디버깅: 빈 HTML인지 확인
            if (empty(trim(strip_tags($footerPreviewHtml)))) {
                \Log::warning('Footer preview HTML is empty', [
                    'theme' => $footerTheme
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Footer preview render error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $footerPreviewHtml = '<div class="text-danger p-3">미리보기 렌더링 오류: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        // 사이트 객체 새로고침 (최신 데이터 반영)
        $site->refresh();
        
        return view('admin.settings', compact('site', 'settings', 'headerPreviewHtml', 'footerPreviewHtml'));
    }

    /**
     * Update site settings.
     */
    public function update(Request $request, Site $site)
    {
        $validator = Validator::make($request->all(), [
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'site_keywords' => 'nullable|string',
            'logo_type' => 'nullable|in:image,text',
            'site_logo' => 'nullable|string',
            'site_logo_dark' => 'nullable|string',
            'site_favicon' => 'nullable|string',
            'og_image' => 'nullable|string',
            'logo_desktop_size' => 'nullable|integer|min:50|max:1000',
            'logo_mobile_size' => 'nullable|integer|min:50|max:1000',
            // 테마 설정
            'theme_dark_mode' => 'nullable|in:light,dark',
            'theme_top' => 'nullable|string',
            'theme_top_header_show' => 'nullable|in:0,1',
            'header_sticky' => 'nullable|in:0,1',
            'theme_bottom' => 'nullable|string',
            'theme_main' => 'nullable|in:round,square',
            'theme_sidebar' => 'nullable|in:left,right,none',
            // 색상 설정 (라이트 모드)
            'color_light_header_text' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_light_header_bg' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_light_body_text' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_light_body_bg' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_light_point_main' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_light_point_bg' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            // 색상 설정 (다크 모드)
            'color_dark_header_text' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_dark_header_bg' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_dark_body_text' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_dark_body_bg' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_dark_point_main' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_dark_point_bg' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            // 폰트 설정
            'font_design' => 'nullable|string',
            'font_size' => 'nullable|in:small,normal,large',
            // 메뉴 폰트 설정
            'menu_font_size' => 'nullable|string',
            'menu_font_padding' => 'nullable|string',
            'menu_font_weight' => 'nullable|in:300,400,700',
            // 모바일 상단 설정
            'mobile_header_theme' => 'nullable|in:theme1,theme2,theme3,theme4,theme5,theme6,theme7,theme8',
            'mobile_menu_icon' => 'nullable|string',
            'mobile_menu_direction' => 'nullable|in:top-to-bottom,left-to-right,right-to-left,bottom-to-top',
            'mobile_menu_icon_border' => 'nullable|in:0,1',
            'mobile_menu_login_widget' => 'nullable|in:0,1',
            // 게시판 설정
            'best_post_criteria' => 'nullable|in:views,likes,comments',
            'write_interval' => 'nullable|integer|min:0',
            'new_post_hours' => 'nullable|integer|min:0',
            'show_views' => 'nullable|in:0,1',
            'show_datetime' => 'nullable|in:0,1',
            // 기능 ON/OFF
            'show_visitor_count' => 'nullable|in:0,1',
            'email_notification' => 'nullable|in:0,1',
            'general_login' => 'nullable|in:0,1',
            // 이용약관 & 개인정보처리방침
            'terms_of_service' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
            // 회사정보
            'company_representative' => 'nullable|string|max:255',
            'company_contact' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_registration_number' => 'nullable|string|max:255',
            'company_telecom_number' => 'nullable|string|max:255',
            'company_additional_info' => 'nullable|string',
            // 검색 엔진 인증
            'google_site_verification' => 'nullable|string|max:255',
            'naver_site_verification' => 'nullable|string|max:255',
            'daum_site_verification' => 'nullable|string|max:255',
            // robots.txt
            'robots_txt' => 'nullable|string',
            'google_analytics_id' => 'nullable|string|max:50',
            'adsense_ads_txt' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 기존 설정 가져오기
        $existingSettings = $this->settingService->getSettingsBySite($site->id);
        
        // 회사정보 필드 목록 (빈 값도 명시적으로 저장해야 함)
        $companyInfoFields = [
            'company_representative',
            'company_contact',
            'company_address',
            'company_registration_number',
            'company_telecom_number',
            'company_additional_info'
        ];
        
        // 검색 엔진 인증 필드 목록 (빈 값도 명시적으로 저장해야 함)
        $verificationFields = [
            'google_site_verification',
            'naver_site_verification',
            'daum_site_verification',
            'robots_txt',
            'google_analytics_id',
            'adsense_ads_txt',
            'google_analytics_id',
            'adsense_ads_txt'
        ];
        
        // 요청에서 받은 새 설정
        $requestData = $request->except(['_token', '_method']);
        
        // 사이드 위젯 기능이 없는 플랜의 경우 사이드바를 'none'으로 강제 설정
        if (!$site->hasFeature('sidebar_widgets')) {
            $requestData['theme_sidebar'] = 'none';
        }
        
        $newSettings = [];
        
        // 일반 필드: 빈 값 제외
        foreach ($requestData as $key => $value) {
            // 회사정보 필드는 빈 값도 포함
            if (in_array($key, $companyInfoFields)) {
                $newSettings[$key] = $value ?? '';
            }
            // 검색 엔진 인증 필드는 빈 값도 포함
            elseif (in_array($key, $verificationFields)) {
                $newSettings[$key] = $value ?? '';
            }
            // 체크박스 필드는 항상 포함
            elseif (in_array($key, [
                'theme_top_header_show',
                'top_header_login_show',
                'header_sticky',
                'menu_login_show',
                'header_shadow',
                'header_border',
                'show_views',
                'show_datetime',
                'show_visitor_count',
                'email_notification',
                'general_login',
                'mobile_menu_icon_border',
                'mobile_menu_login_widget',
                'enable_point_message',
                'theme_full_width'
            ])) {
                $newSettings[$key] = ($value == '1') ? '1' : '0';
            }
            // 메뉴 폰트 설정 필드는 빈 값이어도 포함 (0도 유효한 값일 수 있음)
            elseif (in_array($key, ['menu_font_size', 'menu_font_padding', 'menu_font_weight', 'mobile_header_theme', 'mobile_menu_icon', 'mobile_menu_direction'])) {
                $newSettings[$key] = $value ?? '';
            }
            // 기타 필드: 빈 값이 아닌 경우만 포함
            elseif ($value !== null && $value !== '') {
                $newSettings[$key] = $value;
            }
        }
        
        // 기존 설정과 새 설정 병합 (새 설정이 우선, 요청에 포함된 필드만 업데이트)
        $settingsToUpdate = array_merge($existingSettings, $newSettings);
        
        $this->settingService->setSettings($site->id, $settingsToUpdate);

        // robots.txt 파일을 public 폴더에 저장
        if (isset($newSettings['robots_txt']) || isset($requestData['robots_txt'])) {
            $this->saveRobotsTxtFile($site, $settingsToUpdate);
        }

        return back()->with('success', '설정이 저장되었습니다.');
    }

    /**
     * Increase visitor count.
     */
    public function increaseVisitor(Request $request, Site $site)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('amount')
            ], 422);
        }

        $amount = $request->input('amount');
        
        // 방문자수 증가를 위해 Visitor 모델에 더미 데이터 추가
        // 실제 방문자수는 IP 기반이지만, 여기서는 설정값으로 관리
        $currentAdjustment = (int) $this->settingService->getSetting($site->id, 'visitor_count_adjustment', 0);
        $newAdjustment = $currentAdjustment + $amount;
        
        $this->settingService->setSetting($site->id, 'visitor_count_adjustment', $newAdjustment);

        return response()->json([
            'success' => true,
            'message' => '방문자수가 ' . $amount . '만큼 증가되었습니다.',
            'new_total' => $newAdjustment
        ]);
    }

    /**
     * Upload site image (logo, favicon, og_image).
     */
    public function uploadImage(Request $request, Site $site)
    {
        // 파일이 있는지 먼저 확인
        if (!$request->hasFile('image')) {
            \Log::error('Image file not found in request', [
                'site_id' => $site->id,
                'type' => $request->input('type'),
                'all_files' => array_keys($request->allFiles()),
                'has_image' => $request->hasFile('image')
            ]);
            
            return response()->json([
                'error' => true,
                'message' => '이미지 파일을 찾을 수 없습니다.'
            ], 422);
        }

        $file = $request->file('image');
        
        // 파일 유효성 검사
        if (!$file->isValid()) {
            \Log::error('Image file is not valid', [
                'site_id' => $site->id,
                'type' => $request->input('type'),
                'error' => $file->getError(),
                'error_message' => $file->getErrorMessage()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => '이미지 파일이 유효하지 않습니다: ' . $file->getErrorMessage()
            ], 422);
        }

        // 타입 검증
        $type = $request->input('type');
        if (!in_array($type, ['logo', 'logo_dark', 'favicon', 'og_image'])) {
            return response()->json([
                'error' => true,
                'message' => '잘못된 이미지 타입입니다.'
            ], 422);
        }

        // 파일 크기 검사 (5MB = 5120KB)
        $maxSize = 5120 * 1024; // 5MB in bytes
        if ($file->getSize() > $maxSize) {
            return response()->json([
                'error' => true,
                'message' => '파일 크기가 너무 큽니다. (최대 5MB)'
            ], 422);
        }

        // MIME 타입 검사
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'];
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimes)) {
            return response()->json([
                'error' => true,
                'message' => '지원하지 않는 파일 형식입니다. (JPEG, PNG, GIF, WEBP, ICO만 가능)'
            ], 422);
        }

        \Log::info('Image upload request validated', [
            'site_id' => $site->id,
            'type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType
        ]);

        try {

            $type = $request->input('type');
            $directory = 'site-assets/' . $site->id;
            
            \Log::info('Starting file upload', [
                'site_id' => $site->id,
                'type' => $type,
                'directory' => $directory,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);
            
            // 디렉토리 생성
            \Storage::disk('public')->makeDirectory($directory);
            
            // FileUploadService 사용
            $fileUploadService = app(\App\Services\FileUploadService::class);
            $result = $fileUploadService->upload($file, $directory);

            if (!$result || !isset($result['file_path'])) {
                \Log::error('FileUploadService returned invalid result', [
                    'site_id' => $site->id,
                    'result' => $result
                ]);
                
                return response()->json([
                    'error' => true,
                    'message' => '파일 업로드 결과를 받을 수 없습니다.'
                ], 500);
            }

            // 절대 URL 생성
            $url = asset('storage/' . $result['file_path']);

            \Log::info('Image upload successful', [
                'site_id' => $site->id,
                'type' => $type,
                'url' => $url,
                'file_path' => $result['file_path']
            ]);

            return response()->json([
                'url' => $url,
                'type' => $type
            ]);
        } catch (\Exception $e) {
            \Log::error('Image upload failed', [
                'site_id' => $site->id,
                'type' => $request->input('type'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => '이미지 업로드 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview header/footer theme.
     */
    public function previewHeader(Request $request, Site $site)
    {
        $theme = $request->get('theme', 'design1');
        $type = $request->get('type', 'header');
        
        $settings = $this->settingService->getSettingsBySite($site->id);
        
        $siteName = $settings['site_name'] ?? $site->name ?? 'SEOom Builder';
        $siteLogo = $settings['site_logo'] ?? '';
        $logoType = $settings['logo_type'] ?? 'image';
        $logoDesktopSize = $settings['logo_desktop_size'] ?? '300';
        $logoMobileSize = $settings['logo_mobile_size'] ?? '200';
        
        // 색상 설정 가져오기 (요청에서 전달된 값 우선, 없으면 저장된 설정값 사용)
        $themeDarkMode = $request->get('theme_dark_mode', $settings['theme_dark_mode'] ?? 'light');
        $isDark = $themeDarkMode === 'dark';
        
        if ($type === 'header') {
            // 헤더 색상 설정 (요청에서 전달된 값 우선)
            if ($isDark) {
                $headerText = $request->get('color_dark_header_text', $settings['color_dark_header_text'] ?? '#ffffff');
                $headerBg = $request->get('color_dark_header_bg', $settings['color_dark_header_bg'] ?? '#000000');
            } else {
                $headerText = $request->get('color_light_header_text', $settings['color_light_header_text'] ?? '#000000');
                $headerBg = $request->get('color_light_header_bg', $settings['color_light_header_bg'] ?? '#ffffff');
            }
            
            $themeTopHeaderShow = $request->get('theme_top_header_show', $settings['theme_top_header_show'] ?? '0');
            $topHeaderLoginShow = $request->get('top_header_login_show', $settings['top_header_login_show'] ?? '0');
            $menuLoginShow = $request->get('menu_login_show', $settings['menu_login_show'] ?? '0');
            $headerSticky = $request->get('header_sticky', $settings['header_sticky'] ?? '0');
            $headerShadow = $request->get('header_shadow', $settings['header_shadow'] ?? '0');
            $headerBorder = $request->get('header_border', $settings['header_border'] ?? '0');
            $headerBorderWidth = $request->get('header_border_width', $settings['header_border_width'] ?? '1');
            $headerBorderColor = $request->get('header_border_color', $settings['header_border_color'] ?? '#dee2e6');
            
            // 메뉴 폰트 설정
            $menuFontSize = $request->get('menu_font_size', $settings['menu_font_size'] ?? '1.25rem');
            $menuFontPadding = $request->get('menu_font_padding', $settings['menu_font_padding'] ?? '0.5rem');
            $menuFontWeight = $request->get('menu_font_weight', $settings['menu_font_weight'] ?? '700');
            
            // 포인트 컬러 설정
            $pointColor = $isDark ? ($request->get('color_dark_point_main', $settings['color_dark_point_main'] ?? '#ffffff')) : ($request->get('color_light_point_main', $settings['color_light_point_main'] ?? '#0d6efd'));
            
            try {
                // 메뉴 폰트 설정 값 검증 및 기본값 설정
                $menuFontSize = $menuFontSize ?? '1.25rem';
                $menuFontPadding = $menuFontPadding ?? '0.5rem';
                $menuFontWeight = $menuFontWeight ?? '700';
                
                // rem 단위가 아닌 경우 기본값 사용
                if (!preg_match('/^\d+(\.\d+)?rem$/', $menuFontSize)) {
                    \Log::warning('Invalid menuFontSize format, using default', ['value' => $menuFontSize]);
                    $menuFontSize = '1.25rem';
                }
                if (!preg_match('/^\d+(\.\d+)?rem$/', $menuFontPadding)) {
                    \Log::warning('Invalid menuFontPadding format, using default', ['value' => $menuFontPadding]);
                    $menuFontPadding = '0.5rem';
                }
                
                \Log::info('Rendering header preview', [
                    'theme' => $theme,
                    'menuFontSize' => $menuFontSize,
                    'menuFontPadding' => $menuFontPadding,
                    'menuFontWeight' => $menuFontWeight
                ]);
                
                $html = view('admin.partials.header-preview', [
                    'theme' => $theme,
                    'site' => $site,
                    'siteName' => $siteName,
                    'siteLogo' => $siteLogo,
                    'logoType' => $logoType,
                    'logoDesktopSize' => $logoDesktopSize,
                    'logoMobileSize' => $logoMobileSize,
                    'settings' => $settings,
                    'headerText' => $headerText,
                    'headerBg' => $headerBg,
                    'themeTopHeaderShow' => $themeTopHeaderShow,
                    'topHeaderLoginShow' => $topHeaderLoginShow,
                    'menuLoginShow' => $menuLoginShow,
                    'headerSticky' => $headerSticky,
                    'themeDarkMode' => $themeDarkMode,
                    'pointColor' => $pointColor,
                    'headerShadow' => $headerShadow,
                    'headerBorder' => $headerBorder,
                    'headerBorderWidth' => $headerBorderWidth,
                    'headerBorderColor' => $headerBorderColor,
                    'menuFontSize' => $menuFontSize,
                    'menuFontPadding' => $menuFontPadding,
                    'menuFontWeight' => $menuFontWeight,
                ])->render();
                
                // 빈 HTML 체크
                if (empty(trim($html))) {
                    \Log::warning('Generated HTML is empty', [
                        'theme' => $theme,
                        'menuFontSize' => $menuFontSize,
                        'menuFontPadding' => $menuFontPadding,
                        'menuFontWeight' => $menuFontWeight
                    ]);
                    throw new \Exception('Generated HTML is empty');
                }
            } catch (\Exception $e) {
                \Log::error('Header preview render error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'request' => $request->all(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                // 에러 발생 시에도 기본 헤더 표시
                try {
                    $html = view('admin.partials.header-preview', [
                        'theme' => $theme,
                        'site' => $site,
                        'siteName' => $siteName,
                        'siteLogo' => $siteLogo ?? '',
                        'logoType' => $logoType ?? 'image',
                        'logoDesktopSize' => $logoDesktopSize ?? '300',
                        'logoMobileSize' => $logoMobileSize ?? '200',
                        'settings' => $settings,
                        'headerText' => $headerText ?? ($isDark ? '#ffffff' : '#000000'),
                        'headerBg' => $headerBg ?? ($isDark ? '#000000' : '#ffffff'),
                        'themeTopHeaderShow' => $themeTopHeaderShow ?? '0',
                        'topHeaderLoginShow' => $topHeaderLoginShow ?? '0',
                        'menuLoginShow' => $menuLoginShow ?? '0',
                        'headerSticky' => $headerSticky ?? '0',
                        'themeDarkMode' => $themeDarkMode ?? 'light',
                        'pointColor' => $pointColor ?? ($isDark ? '#ffffff' : '#0d6efd'),
                        'headerShadow' => $headerShadow ?? false,
                        'headerBorder' => $headerBorder ?? false,
                        'headerBorderWidth' => $headerBorderWidth ?? '1',
                        'headerBorderColor' => $headerBorderColor ?? '#dee2e6',
                        'menuFontSize' => '1.25rem',
                        'menuFontPadding' => '0.5rem',
                        'menuFontWeight' => '700',
                    ])->render();
                } catch (\Exception $e2) {
                    \Log::error('Header preview fallback render error: ' . $e2->getMessage());
                    $html = '<div class="text-danger p-3">미리보기 렌더링 오류가 발생했습니다.<br><small>원인: ' . htmlspecialchars($e->getMessage()) . '</small></div>';
                }
            }
        } else {
            // 푸터 색상 설정 (요청에서 전달된 값 우선)
            if ($isDark) {
                $footerText = $request->get('color_dark_footer_text', $settings['color_dark_footer_text'] ?? '#ffffff');
                $footerBg = $request->get('color_dark_footer_bg', $settings['color_dark_footer_bg'] ?? '#000000');
            } else {
                $footerText = $request->get('color_light_footer_text', $settings['color_light_footer_text'] ?? '#000000');
                $footerBg = $request->get('color_light_footer_bg', $settings['color_light_footer_bg'] ?? '#f8f9fa');
            }
            
            $html = view('admin.partials.footer-preview', compact('theme', 'site', 'settings', 'footerText', 'footerBg'))->render();
        }
        
        return response()->json(['html' => $html]);
    }

    /**
     * Get terms of service content (public).
     */
    public function getTermsOfService(Site $site)
    {
        // 인증 체크 없이 공개 접근 가능
        $terms = $this->settingService->getSetting($site->id, 'terms_of_service', '');
        return response()->json([
            'content' => $terms
        ]);
    }

    /**
     * Get privacy policy content (public).
     */
    public function getPrivacyPolicy(Site $site)
    {
        // 인증 체크 없이 공개 접근 가능
        $privacy = $this->settingService->getSetting($site->id, 'privacy_policy', '');
        return response()->json([
            'content' => $privacy
        ]);
    }

    /**
     * Save robots.txt file to public directory.
     */
    private function saveRobotsTxtFile(Site $site, array $settings)
    {
        try {
            $customRobotsTxt = $settings['robots_txt'] ?? '';
            $sitemapUrl = route('sitemap', ['site' => $site->slug]);
            
            // 사용자가 커스텀 robots.txt를 입력한 경우 사용
            if (!empty($customRobotsTxt)) {
                $content = $customRobotsTxt;
                
                // 사이트맵 URL이 포함되어 있지 않으면 추가
                if (stripos($content, 'Sitemap:') === false) {
                    $content .= "\n\nSitemap: {$sitemapUrl}\n";
                }
            } else {
                // 기본값 생성
                $content = "User-agent: *\n";
                $content .= "Allow: /\n\n";
                $content .= "Sitemap: {$sitemapUrl}\n";
            }
            
            // public 폴더에 robots.txt 파일 저장
            $robotsPath = public_path('robots.txt');
            file_put_contents($robotsPath, $content);
            
            \Log::info('robots.txt file saved', [
                'site_id' => $site->id,
                'site_slug' => $site->slug,
                'path' => $robotsPath
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save robots.txt file', [
                'site_id' => $site->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Preview mobile header theme.
     */
    public function previewMobileHeader(Request $request, Site $site)
    {
        $theme = $request->get('theme', 'theme1');
        $menuIcon = $request->get('menu_icon', 'bi-list');
        $menuDirection = $request->get('menu_direction', 'top-to-bottom');
        $menuIconBorder = $request->get('menu_icon_border', '0') == '1';
        $menuLoginWidget = $request->get('menu_login_widget', '0') == '1';
        
        $settings = $this->settingService->getSettingsBySite($site->id);
        
        $siteName = $settings['site_name'] ?? $site->name ?? 'SEOom Builder';
        $siteLogo = $settings['site_logo'] ?? '';
        $logoType = $settings['logo_type'] ?? 'text';
        $logoMobileSize = $settings['logo_mobile_size'] ?? '200';
        
        // 색상 설정
        $themeDarkMode = $settings['theme_dark_mode'] ?? 'light';
        $isDark = $themeDarkMode === 'dark';
        
        $headerTextColor = $isDark ? ($settings['color_dark_header_text'] ?? '#ffffff') : ($settings['color_light_header_text'] ?? '#000000');
        $headerBgColor = $isDark ? ($settings['color_dark_header_bg'] ?? '#000000') : ($settings['color_light_header_bg'] ?? '#ffffff');
        $pointColor = $isDark ? ($settings['color_dark_point_main'] ?? '#ffffff') : ($settings['color_light_point_main'] ?? '#0d6efd');
        
        // 헤더 스타일
        $headerStyle = "background-color: {$headerBgColor}; color: {$headerTextColor};";
        // 하단 메뉴가 있는 테마(5,6,7,8)는 헤더 하단에 회색 구분선만 적용하고 그림자 제거
        if (in_array($theme, ['theme5', 'theme6', 'theme7', 'theme8'])) {
            $headerStyle .= " border-bottom: 1px solid #dee2e6;";
            // 그림자는 하단 메뉴에 적용되므로 헤더에는 그림자 제거
        } else {
            if ($settings['header_shadow'] ?? '0' == '1') {
                $headerStyle .= " box-shadow: 0 2px 4px rgba(0,0,0,0.1);";
            }
            if ($settings['header_border'] ?? '0' == '1') {
                $borderWidth = $settings['header_border_width'] ?? '1';
                $borderColor = $settings['header_border_color'] ?? '#dee2e6';
                $headerStyle .= " border-bottom: {$borderWidth}px solid {$borderColor};";
            }
        }
        
        // 메뉴 로드 (하부 메뉴 포함)
        $menus = collect([]);
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('menus')) {
                $menus = \App\Models\Menu::where('site_id', $site->id)
                    ->whereNull('parent_id')
                    ->with('children')
                    ->orderBy('order')
                    ->limit(5)
                    ->get();
            }
        } catch (\Exception $e) {
            $menus = collect([]);
        }
        
        // 헤더 테두리 설정
        $headerBorder = ($settings['header_border'] ?? '0') == '1';
        $headerBorderWidth = $settings['header_border_width'] ?? '1';
        $headerBorderColor = $settings['header_border_color'] ?? '#dee2e6';
        
        // 메뉴 아이콘 테두리 설정 (요청에서 가져오거나 설정에서 가져오기)
        if (!isset($menuIconBorder)) {
            $menuIconBorder = ($settings['mobile_menu_icon_border'] ?? '0') == '1';
        }
        
        // 메뉴 방향 애니메이션 클래스
        $menuAnimationClass = '';
        switch($menuDirection) {
            case 'left-to-right':
                $menuAnimationClass = 'slide-in-left';
                break;
            case 'right-to-left':
                $menuAnimationClass = 'slide-in-right';
                break;
            case 'bottom-to-top':
                $menuAnimationClass = 'slide-in-bottom';
                break;
            default:
                $menuAnimationClass = 'slide-in-top';
        }
        
        try {
            $html = view('admin.partials.mobile-header-preview', [
                'theme' => $theme,
                'site' => $site,
                'siteName' => $siteName,
                'siteLogo' => $siteLogo,
                'logoType' => $logoType,
                'logoMobileSize' => $logoMobileSize,
                'headerStyle' => $headerStyle,
                'headerTextColor' => $headerTextColor,
                'headerBgColor' => $headerBgColor,
                'pointColor' => $pointColor,
                'menuIcon' => $menuIcon,
                'menuDirection' => $menuDirection,
                'menuAnimationClass' => $menuAnimationClass,
                'menus' => $menus,
                'headerBorder' => $headerBorder,
                'headerBorderWidth' => $headerBorderWidth,
                'headerBorderColor' => $headerBorderColor,
                'mobileMenuIconBorder' => $menuIconBorder,
                'mobileMenuLoginWidget' => $menuLoginWidget,
                'themeDarkMode' => $themeDarkMode,
            ])->render();
            
            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            \Log::error('Mobile header preview error: ' . $e->getMessage());
            return response()->json(['html' => '<div class="text-danger p-3">미리보기 오류: ' . htmlspecialchars($e->getMessage()) . '</div>']);
        }
    }
}
