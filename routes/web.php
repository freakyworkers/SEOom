<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\PointExchangeController as AdminPointExchangeController;
use App\Http\Controllers\PointExchangeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminChatController;
use App\Http\Controllers\AdminReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Root route - 도메인 기반 또는 마스터 사이트 홈페이지
// 커스텀 도메인을 연결한 경우 루트 경로가 메인 페이지로 동작해야 하므로 라우트 이름 부여
Route::get('/', function (Request $request) {
    // 도메인 기반 접근 시 미들웨어에서 설정한 사이트 사용
    $site = $request->attributes->get('site');
    
    if ($site) {
        // 도메인 기반 접근 또는 마스터 사이트
        return app(\App\Http\Controllers\HomeController::class)->index($site);
    }
    
    // 사이트가 없으면 마스터 사이트 확인
    $masterSite = \App\Models\Site::getMasterSite();
    
    if ($masterSite) {
        // 마스터 사이트가 있으면 HomeController로 위임
        return app(\App\Http\Controllers\HomeController::class)->index($masterSite);
    }
    
    // 마스터 사이트가 없으면 welcome 페이지 표시
    return view('welcome');
})->name('home.root'); // 루트 경로에도 라우트 이름 부여

// SEO Routes - 도메인 기반 (site/{site} prefix 없이)
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\RobotsController::class, 'index'])->name('robots');

// 마스터 사이트 인증 라우트 (루트 경로)
Route::middleware('web')->group(function () {
    $masterSite = \App\Models\Site::getMasterSite();
    
    if ($masterSite) {
        // 마스터 사이트가 있으면 루트 경로에 인증 라우트 추가
        Route::middleware('guest')->group(function () use ($masterSite) {
            Route::get('/login', function () use ($masterSite) {
                return app(\App\Http\Controllers\AuthController::class)->showLoginForm($masterSite);
            })->name('master.login');
            
            Route::post('/login', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AuthController::class)->login($request, $masterSite);
            });
            
            Route::get('/register', function () use ($masterSite) {
                return app(\App\Http\Controllers\AuthController::class)->showRegisterForm($masterSite);
            })->name('master.register');
            
            Route::post('/register', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AuthController::class)->register($request, $masterSite);
            });
            
            // Master Site Social Login Routes
            Route::get('/auth/{provider}', function ($provider) use ($masterSite) {
                return app(\App\Http\Controllers\SocialLoginController::class)->redirectToProvider($masterSite, $provider);
            })->name('master.social.login');
        });
        
        // Master Site Social Login Callback Route (guest 미들웨어 밖으로 이동 - 구글 콜백은 세션 필요)
        Route::get('/auth/{provider}/callback', function (Request $request, $provider) use ($masterSite) {
            return app(\App\Http\Controllers\SocialLoginController::class)->handleProviderCallback($request, $provider);
        })->name('master.social.callback');
        
        // Master Console SSO Token Generation (from master site)
        Route::post('/master-console-sso-token', function (Request $request) {
            return app(\App\Http\Controllers\Master\MasterAuthController::class)->generateSsoToken();
        })->middleware('auth')->name('master.console.sso-token');
        
        Route::post('/logout', function (Request $request) use ($masterSite) {
            return app(\App\Http\Controllers\AuthController::class)->logout($request, $masterSite);
        })->middleware('auth')->name('master.logout');
        
        // Store Routes (스토어)
        Route::get('/store', [\App\Http\Controllers\StoreController::class, 'index'])->name('store.index');
        Route::get('/store/plugins', [\App\Http\Controllers\StoreController::class, 'plugins'])->name('store.plugins');
        
        // Payment Routes (마스터 사이트용)
        Route::get('/plans/{plan}/subscribe', [PaymentController::class, 'subscribe'])->name('payment.subscribe');
        Route::post('/plans/{plan}/subscribe', [PaymentController::class, 'processSubscription'])->name('payment.process-subscription');
        Route::get('/payment/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
        Route::get('/payment/change-plan-checkout', [PaymentController::class, 'changePlanCheckout'])->name('payment.change-plan-checkout');
        Route::post('/my-sites/{userSite}/addons/{addonProduct}/purchase', [PaymentController::class, 'processAddon'])->name('payment.process-addon');
        Route::get('/payment/addon-checkout', [PaymentController::class, 'addonCheckout'])->name('payment.addon-checkout');
        Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/fail', [PaymentController::class, 'fail'])->name('payment.fail');
        Route::get('/payment/success-page', [PaymentController::class, 'successPage'])->name('payment.success-page');
        Route::get('/payment/fail-page', [PaymentController::class, 'failPage'])->name('payment.fail-page');
        Route::get('/payment/select-server-capacity-site', [PaymentController::class, 'selectServerCapacitySite'])->name('payment.select-server-capacity-site')->middleware('auth');
        Route::post('/payment/apply-server-capacity', [PaymentController::class, 'applyServerCapacity'])->name('payment.apply-server-capacity')->middleware('auth');
        
        // 마스터 사이트 관리자 페이지 라우트 (루트 경로)
        // 모든 관리자 라우트를 마스터 사이트용으로 복제
        Route::prefix('admin')->middleware('auth')->group(function () use ($masterSite) {
            // Dashboard
            Route::get('/dashboard', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->dashboard($masterSite);
            })->name('master.admin.dashboard');
            Route::get('/dashboard/chart-data', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->getChartData($masterSite);
            })->name('master.admin.dashboard.chart');
            
            // Users
            Route::get('/users', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->users($masterSite, $request);
            })->name('master.admin.users');
            Route::post('/users', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeUser($request, $masterSite);
            })->name('master.admin.users.store');
            Route::get('/users/{user}', function (\App\Models\User $user) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->userDetail($masterSite, $user);
            })->name('master.admin.users.detail');
            Route::put('/users/{user}', function (Request $request, \App\Models\User $user) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateUser($request, $masterSite, $user);
            })->name('master.admin.users.update');
            
            // Registration Settings
            Route::get('/registration-settings', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->registrationSettings($masterSite);
            })->name('master.admin.registration-settings');
            Route::post('/registration-settings', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateRegistrationSettings($masterSite, $request);
            })->name('master.admin.registration-settings.update');
            Route::post('/registration-settings/test-sms', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->testSms($masterSite, $request);
            })->name('master.admin.registration-settings.test-sms');
            
            // Mail Settings
            Route::get('/mail-settings', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->mailSettings($masterSite);
            })->name('master.admin.mail-settings');
            Route::post('/mail-settings', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMailSettings($request, $masterSite);
            })->name('master.admin.mail-settings.update');
            Route::post('/mail-settings/test', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->testMail($request, $masterSite);
            })->name('master.admin.mail-settings.test');
            
            // Contact Forms
            Route::get('/contact-forms', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->contactForms($masterSite);
            })->name('master.admin.contact-forms.index');
            Route::post('/contact-forms', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeContactForm($request, $masterSite);
            })->name('master.admin.contact-forms.store');
            Route::get('/contact-forms/{contactForm}', function (\App\Models\ContactForm $contactForm) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->showContactForm($masterSite, $contactForm);
            })->name('master.admin.contact-forms.show');
            Route::put('/contact-forms/{contactForm}', function (Request $request, \App\Models\ContactForm $contactForm) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateContactForm($request, $masterSite, $contactForm);
            })->name('master.admin.contact-forms.update');
            Route::delete('/contact-forms/{contactForm}', function (\App\Models\ContactForm $contactForm) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteContactForm($masterSite, $contactForm);
            })->name('master.admin.contact-forms.delete');
            
            // Maps
            Route::get('/maps', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->mapsIndex($masterSite);
            })->name('master.admin.maps.index');
            Route::post('/maps', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->mapsStore($request, $masterSite);
            })->name('master.admin.maps.store');
            Route::get('/maps/{map}/edit', function (\App\Models\Map $map) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->mapsEdit($masterSite, $map);
            })->name('master.admin.maps.edit');
            Route::put('/maps/{map}', function (Request $request, \App\Models\Map $map) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->mapsUpdate($request, $masterSite, $map);
            })->name('master.admin.maps.update');
            Route::delete('/maps/{map}', function (\App\Models\Map $map) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->mapsDestroy($masterSite, $map);
            })->name('master.admin.maps.delete');
            Route::post('/maps/geocode', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->mapsGeocode($request, $masterSite);
            })->name('master.admin.maps.geocode');
            Route::post('/maps/add-default', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->mapsAddDefault($request, $masterSite);
            })->name('master.admin.maps.add-default');
            
            // User Ranks
            Route::get('/user-ranks', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->userRanks($masterSite);
            })->name('master.admin.user-ranks');
            Route::post('/user-ranks/store', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->userRanksStore($request, $masterSite);
            })->name('master.admin.user-ranks.store');
            Route::post('/user-ranks/update', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->userRanksUpdate($request, $masterSite);
            })->name('master.admin.user-ranks.update');
            Route::delete('/user-ranks/{userRank}', function (\App\Models\UserRank $userRank) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->userRanksDelete($masterSite, $userRank);
            })->name('master.admin.user-ranks.delete');
            
            // My Page Settings
            Route::get('/my-page-settings', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->myPageSettings($masterSite);
            })->name('master.admin.my-page-settings');
            Route::put('/my-page-settings', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMyPageSettings($masterSite, $request);
            })->name('master.admin.my-page-settings.update');
            
            // Crawlers
            Route::get('/crawlers', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->crawlersIndex($masterSite);
            })->name('master.admin.crawlers.index');
            Route::post('/crawlers', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->crawlersStore($request, $masterSite);
            })->name('master.admin.crawlers.store');
            Route::get('/crawlers/{crawler}/edit', function (\App\Models\Crawler $crawler) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->crawlersEdit($masterSite, $crawler);
            })->name('master.admin.crawlers.edit');
            Route::put('/crawlers/{crawler}', function (Request $request, \App\Models\Crawler $crawler) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->crawlersUpdate($request, $masterSite, $crawler);
            })->name('master.admin.crawlers.update');
            Route::delete('/crawlers/{crawler}', function (\App\Models\Crawler $crawler) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->crawlersDestroy($masterSite, $crawler);
            })->name('master.admin.crawlers.delete');
            Route::post('/crawlers/{crawler}/toggle-active', function (\App\Models\Crawler $crawler) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->crawlersToggleActive($masterSite, $crawler);
            })->name('master.admin.crawlers.toggle-active');
            Route::post('/crawlers/test', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->crawlersTest($request, $masterSite);
            })->name('master.admin.crawlers.test');
            Route::post('/crawlers/run-all', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->crawlersRunAll($masterSite);
            })->name('master.admin.crawlers.run-all');
            
            // Boards
            Route::get('/boards', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->boards($masterSite);
            })->name('master.admin.boards');
            Route::get('/boards/{board}/topics', function (\App\Models\Board $board) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->getBoardTopics($masterSite, $board);
            })->name('master.admin.boards.topics');
            Route::post('/banned-words', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateBannedWords($request, $masterSite);
            })->name('master.admin.banned-words.update');
            
            // Posts
            Route::get('/posts', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->posts($masterSite, $request);
            })->name('master.admin.posts');
            Route::put('/posts/{post}/board', function (Request $request, \App\Models\Post $post) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updatePostBoard($request, $masterSite, $post);
            })->name('master.admin.posts.update-board');
            Route::put('/posts/{post}/views', function (Request $request, \App\Models\Post $post) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updatePostViews($request, $masterSite, $post);
            })->name('master.admin.posts.update-views');
            
            // Messages
            Route::get('/messages', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->messages($masterSite, $request);
            })->name('master.admin.messages.index');
            Route::put('/messages/settings', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMessageSettings($request, $masterSite);
            })->name('master.admin.messages.update-settings');
            Route::put('/messages/{message}', function (Request $request, \App\Models\Message $message) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMessage($request, $masterSite, $message);
            })->name('master.admin.messages.update');
            Route::delete('/messages/{message}', function (\App\Models\Message $message) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteMessage($masterSite, $message);
            })->name('master.admin.messages.delete');
            
            // Banners
            Route::get('/banners', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->bannersIndex($masterSite);
            })->name('master.admin.banners.index');
            Route::post('/banners', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->bannersUpdate($request, $masterSite);
            })->name('master.admin.banners.update');
            Route::get('/banners/{location}', function ($location) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->bannersDetail($masterSite, $location);
            })->name('master.admin.banners.detail');
            Route::post('/banners/store', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->bannersStore($request, $masterSite);
            })->name('master.admin.banners.store');
            Route::post('/banners/update-order', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->bannersUpdateOrder($request, $masterSite);
            })->name('master.admin.banners.update-order');
            Route::post('/banners/update-item', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->bannersUpdateItem($request, $masterSite);
            })->name('master.admin.banners.update-item');
            Route::post('/banners/save-all', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->bannersSaveAll($request, $masterSite);
            })->name('master.admin.banners.save-all');
            Route::delete('/banners/{banner}', function (\App\Models\Banner $banner) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->bannersDelete($masterSite, $banner);
            })->name('master.admin.banners.delete');
            
            // Popups
            Route::get('/popups', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->popupsIndex($masterSite);
            })->name('master.admin.popups.index');
            Route::post('/popups/store', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->popupsStore($request, $masterSite);
            })->name('master.admin.popups.store');
            Route::post('/popups/settings', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->popupsUpdateSettings($request, $masterSite);
            })->name('master.admin.popups.update-settings');
            Route::post('/popups/update-order', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->popupsUpdateOrder($request, $masterSite);
            })->name('master.admin.popups.update-order');
            Route::post('/popups/{popup}/update', function (Request $request, \App\Models\Popup $popup) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->popupsUpdateItem($request, $masterSite, $popup);
            })->name('master.admin.popups.update-item');
            Route::delete('/popups/{popup}', function (\App\Models\Popup $popup) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->popupsDelete($masterSite, $popup);
            })->name('master.admin.popups.delete');
            
            // Blocked IPs
            Route::get('/blocked-ips', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->blockedIpsIndex($masterSite);
            })->name('master.admin.blocked-ips.index');
            Route::post('/blocked-ips', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->blockedIpsStore($request, $masterSite);
            })->name('master.admin.blocked-ips.store');
            Route::delete('/blocked-ips/{ip}', function (\App\Models\BlockedIp $ip) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->blockedIpsDestroy($masterSite, $ip);
            })->name('master.admin.blocked-ips.destroy');
            
            // Menus
            Route::get('/menus', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->menus($masterSite);
            })->name('master.admin.menus');
            Route::post('/menus', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeMenu($request, $masterSite);
            })->name('master.admin.menus.store');
            Route::put('/menus/order', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMenuOrder($request, $masterSite);
            })->name('master.admin.menus.update-order');
            Route::delete('/menus/{menu}', function (\App\Models\Menu $menu) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteMenu($masterSite, $menu);
            })->name('master.admin.menus.delete');
            
            // Mobile Menus
            Route::post('/mobile-menus', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeMobileMenu($request, $masterSite);
            })->name('master.admin.mobile-menus.store');
            Route::put('/mobile-menus/order', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMobileMenuOrder($request, $masterSite);
            })->name('master.admin.mobile-menus.update-order');
            Route::post('/mobile-menus/design-type', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMobileMenuDesignType($request, $masterSite);
            })->name('master.admin.mobile-menus.design-type');
            Route::put('/mobile-menus/{mobileMenu}', function (Request $request, \App\Models\MobileMenu $mobileMenu) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMobileMenu($request, $masterSite, $mobileMenu);
            })->name('master.admin.mobile-menus.update');
            Route::delete('/mobile-menus/{mobileMenu}', function (\App\Models\MobileMenu $mobileMenu) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteMobileMenu($masterSite, $mobileMenu);
            })->name('master.admin.mobile-menus.delete');
            
            // Add Points (테스트용)
            Route::get('/add-points/{points}', function ($points) use ($masterSite) {
                if (!auth()->check() || !auth()->user()->canManage()) {
                    abort(403);
                }
                $user = auth()->user();
                $user->addPoints((int)$points);
                return response()->json(['success' => true, 'message' => '포인트가 지급되었습니다.', 'points' => $user->points]);
            })->name('master.admin.add-points');
            
            // Attendance
            Route::get('/attendance', function () use ($masterSite) {
                return app(\App\Http\Controllers\Admin\AttendanceController::class)->index($masterSite);
            })->name('master.admin.attendance.index');
            Route::put('/attendance', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\AttendanceController::class)->update($request, $masterSite);
            })->name('master.admin.attendance.update');
            
            // Settings
            Route::get('/settings', function () use ($masterSite) {
                return app(\App\Http\Controllers\SiteSettingController::class)->index($masterSite);
            })->name('master.admin.settings');
            Route::put('/settings', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\SiteSettingController::class)->update($request, $masterSite);
            })->name('master.admin.settings.update');
            Route::post('/settings/upload-image', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\SiteSettingController::class)->uploadImage($request, $masterSite);
            })->name('master.admin.settings.upload-image');
            Route::get('/settings/preview-header', function () use ($masterSite) {
                return app(\App\Http\Controllers\SiteSettingController::class)->previewHeader($masterSite);
            })->name('master.admin.settings.preview-header');
            Route::get('/settings/preview-mobile-header', function () use ($masterSite) {
                return app(\App\Http\Controllers\SiteSettingController::class)->previewMobileHeader($masterSite);
            })->name('master.admin.settings.preview-mobile-header');
            Route::post('/settings/increase-visitor', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\SiteSettingController::class)->increaseVisitor($request, $masterSite);
            })->name('master.admin.settings.increase-visitor');
            Route::get('/settings/terms-of-service', function () use ($masterSite) {
                return app(\App\Http\Controllers\SiteSettingController::class)->getTermsOfService($masterSite);
            })->name('master.admin.settings.terms-of-service');
            Route::get('/settings/privacy-policy', function () use ($masterSite) {
                return app(\App\Http\Controllers\SiteSettingController::class)->getPrivacyPolicy($masterSite);
            })->name('master.admin.settings.privacy-policy');
            
            // Server Management
            Route::get('/server-management', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->serverManagement($masterSite);
            })->name('master.admin.server-management');
            
            // Toggle Menus
            Route::get('/toggle-menus', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->toggleMenus($masterSite);
            })->name('master.admin.toggle-menus');
            Route::get('/toggle-menus/list', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->getToggleMenusList($masterSite);
            })->name('master.admin.toggle-menus.list');
            Route::get('/toggle-menus/{toggleMenu}', function (\App\Models\ToggleMenu $toggleMenu) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->getToggleMenu($masterSite, $toggleMenu);
            })->name('master.admin.toggle-menus.show');
            Route::post('/toggle-menus', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeToggleMenu($request, $masterSite);
            })->name('master.admin.toggle-menus.store');
            Route::put('/toggle-menus/{toggleMenu}', function (Request $request, \App\Models\ToggleMenu $toggleMenu) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateToggleMenu($request, $masterSite, $toggleMenu);
            })->name('master.admin.toggle-menus.update');
            Route::delete('/toggle-menus/{toggleMenu}', function (\App\Models\ToggleMenu $toggleMenu) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteToggleMenu($masterSite, $toggleMenu);
            })->name('master.admin.toggle-menus.delete');
            Route::put('/toggle-menus/order', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateToggleMenuOrder($request, $masterSite);
            })->name('master.admin.toggle-menus.update-order');
            Route::post('/toggle-menus/{toggleMenu}/toggle-active', function (\App\Models\ToggleMenu $toggleMenu) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->toggleToggleMenuActive($masterSite, $toggleMenu);
            })->name('master.admin.toggle-menus.toggle-active');
            
            // Sidebar Widgets
            Route::match(['get', 'post'], '/sidebar-widgets', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->sidebarWidgets($masterSite, $request);
            })->name('master.admin.sidebar-widgets');
            Route::post('/sidebar-widgets/store', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeSidebarWidget($request, $masterSite);
            })->name('master.admin.sidebar-widgets.store');
            Route::put('/sidebar-widgets/{widget}', function (Request $request, \App\Models\SidebarWidget $widget) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateSidebarWidget($request, $masterSite, $widget);
            })->name('master.admin.sidebar-widgets.update');
            Route::delete('/sidebar-widgets/{widget}', function (\App\Models\SidebarWidget $widget) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteSidebarWidget($masterSite, $widget);
            })->name('master.admin.sidebar-widgets.delete');
            Route::post('/sidebar-widgets/reorder', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->reorderSidebarWidgets($request, $masterSite);
            })->name('master.admin.sidebar-widgets.reorder');
            
            // Main Widgets
            Route::match(['get', 'post'], '/main-widgets', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->mainWidgets($masterSite, $request);
            })->name('master.admin.main-widgets');
            Route::post('/main-widgets/containers/store', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeMainWidgetContainer($request, $masterSite);
            })->name('master.admin.main-widgets.containers.store');
            Route::put('/main-widgets/containers/{container}', function (Request $request, \App\Models\MainWidgetContainer $container) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMainWidgetContainer($request, $masterSite, $container);
            })->name('master.admin.main-widgets.containers.update');
            Route::delete('/main-widgets/containers/{container}', function (\App\Models\MainWidgetContainer $container) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteMainWidgetContainer($masterSite, $container);
            })->name('master.admin.main-widgets.containers.delete');
            Route::post('/main-widgets/store', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeMainWidget($request, $masterSite);
            })->name('master.admin.main-widgets.store');
            Route::put('/main-widgets/{widget}', function (Request $request, \App\Models\MainWidget $widget) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateMainWidget($masterSite, $widget, $request);
            })->name('master.admin.main-widgets.update');
            Route::delete('/main-widgets/{widget}', function (\App\Models\MainWidget $widget) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteMainWidget($masterSite, $widget);
            })->name('master.admin.main-widgets.delete');
            Route::post('/main-widgets/containers/reorder', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->reorderMainWidgetContainers($request, $masterSite);
            })->name('master.admin.main-widgets.containers.reorder');
            Route::post('/main-widgets/reorder', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->reorderMainWidgets($request, $masterSite);
            })->name('master.admin.main-widgets.reorder');
            
            // Custom Pages
            Route::get('/custom-pages', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->customPages($masterSite);
            })->name('master.admin.custom-pages');
            Route::post('/custom-pages/store', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeCustomPage($request, $masterSite);
            })->name('master.admin.custom-pages.store');
            Route::get('/custom-pages/{customPage}/edit', function (Request $request, \App\Models\CustomPage $customPage) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->editCustomPage($masterSite, $customPage, $request);
            })->name('master.admin.custom-pages.edit');
            Route::put('/custom-pages/{customPage}', function (Request $request, \App\Models\CustomPage $customPage) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateCustomPage($request, $masterSite, $customPage);
            })->name('master.admin.custom-pages.update');
            Route::delete('/custom-pages/{customPage}', function (\App\Models\CustomPage $customPage) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteCustomPage($masterSite, $customPage);
            })->name('master.admin.custom-pages.delete');
            Route::post('/custom-pages/{customPage}/containers/store', function (Request $request, \App\Models\CustomPage $customPage) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeCustomPageWidgetContainer($request, $masterSite, $customPage);
            })->name('master.admin.custom-pages.containers.store');
            Route::put('/custom-pages/{customPage}/containers/{container}', function (Request $request, \App\Models\CustomPage $customPage, \App\Models\CustomPageWidgetContainer $container) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateCustomPageWidgetContainer($request, $masterSite, $customPage, $container);
            })->name('master.admin.custom-pages.containers.update');
            Route::delete('/custom-pages/{customPage}/containers/{container}', function (\App\Models\CustomPage $customPage, \App\Models\CustomPageWidgetContainer $container) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteCustomPageWidgetContainer($masterSite, $customPage, $container);
            })->name('master.admin.custom-pages.containers.delete');
            Route::post('/custom-pages/{customPage}/widgets/store', function (Request $request, \App\Models\CustomPage $customPage) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->storeCustomPageWidget($request, $masterSite, $customPage);
            })->name('master.admin.custom-pages.widgets.store');
            Route::put('/custom-pages/{customPage}/widgets/{widget}', function (Request $request, \App\Models\CustomPage $customPage, \App\Models\CustomPageWidget $widget) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateCustomPageWidget($request, $masterSite, $customPage, $widget);
            })->name('master.admin.custom-pages.widgets.update');
            Route::delete('/custom-pages/{customPage}/widgets/{widget}', function (\App\Models\CustomPage $customPage, \App\Models\CustomPageWidget $widget) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->deleteCustomPageWidget($masterSite, $customPage, $widget);
            })->name('master.admin.custom-pages.widgets.delete');
            Route::post('/custom-pages/{customPage}/containers/reorder', function (Request $request, \App\Models\CustomPage $customPage) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->reorderCustomPageWidgetContainers($request, $masterSite, $customPage);
            })->name('master.admin.custom-pages.containers.reorder');
            Route::post('/custom-pages/{customPage}/widgets/reorder', function (Request $request, \App\Models\CustomPage $customPage) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->reorderCustomPageWidgets($request, $masterSite, $customPage);
            })->name('master.admin.custom-pages.widgets.reorder');
            
            // Chat Management
            Route::get('/chat', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminChatController::class)->index($masterSite);
            })->name('master.admin.chat.index');
            Route::put('/chat/settings', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminChatController::class)->updateSettings($masterSite, $request);
            })->name('master.admin.chat.update-settings');
            Route::delete('/chat/messages/{message}', function (\App\Models\ChatMessage $message) use ($masterSite) {
                return app(\App\Http\Controllers\AdminChatController::class)->deleteMessage($masterSite, $message);
            })->name('master.admin.chat.delete-message');
            Route::post('/chat/ban-user', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminChatController::class)->banUser($masterSite, $request);
            })->name('master.admin.chat.ban-user');
            
            // Reports Management
            Route::get('/reports', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminReportController::class)->index($masterSite, $request);
            })->name('master.admin.reports.index');
            Route::get('/reports/{report}', function (\App\Models\Report $report) use ($masterSite) {
                return app(\App\Http\Controllers\AdminReportController::class)->show($masterSite, $report);
            })->name('master.admin.reports.show');
            Route::put('/reports/{report}/status', function (Request $request, \App\Models\Report $report) use ($masterSite) {
                return app(\App\Http\Controllers\AdminReportController::class)->updateStatus($masterSite, $report, $request);
            })->name('master.admin.reports.update-status');
            Route::post('/reports/penalties', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminReportController::class)->issuePenalty($masterSite, $request);
            })->name('master.admin.reports.issue-penalty');
            Route::delete('/reports/penalties/{penalty}', function (\App\Models\Penalty $penalty) use ($masterSite) {
                return app(\App\Http\Controllers\AdminReportController::class)->removePenalty($masterSite, $penalty);
            })->name('master.admin.reports.remove-penalty');
            
            // Point Exchange
            Route::get('/point-exchange', function () use ($masterSite) {
                return app(\App\Http\Controllers\Admin\PointExchangeController::class)->index($masterSite);
            })->name('master.admin.point-exchange.index');
            Route::put('/point-exchange/settings', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\PointExchangeController::class)->updateSettings($request, $masterSite);
            })->name('master.admin.point-exchange.update-settings');
            Route::post('/point-exchange/products', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\PointExchangeController::class)->storeProduct($request, $masterSite);
            })->name('master.admin.point-exchange.store-product');
            Route::put('/point-exchange/products/{product}', function (Request $request, \App\Models\PointExchangeProduct $product) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\PointExchangeController::class)->updateProduct($request, $masterSite, $product);
            })->name('master.admin.point-exchange.update-product');
            Route::delete('/point-exchange/products/{product}', function (\App\Models\PointExchangeProduct $product) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\PointExchangeController::class)->destroyProduct($masterSite, $product);
            })->name('master.admin.point-exchange.destroy-product');
            Route::get('/point-exchange/products/{product}/applications', function (\App\Models\PointExchangeProduct $product) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\PointExchangeController::class)->showApplications($masterSite, $product);
            })->name('master.admin.point-exchange.applications');
            Route::put('/point-exchange/applications/{application}', function (Request $request, \App\Models\PointExchangeApplication $application) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\PointExchangeController::class)->updateApplication($request, $masterSite, $application);
            })->name('master.admin.point-exchange.update-application');
            
            // Event Application
            Route::get('/event-application', function () use ($masterSite) {
                return app(\App\Http\Controllers\Admin\EventApplicationController::class)->index($masterSite);
            })->name('master.admin.event-application.index');
            Route::put('/event-application/settings', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\EventApplicationController::class)->updateSettings($request, $masterSite);
            })->name('master.admin.event-application.update-settings');
            Route::post('/event-application/products', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\EventApplicationController::class)->storeProduct($request, $masterSite);
            })->name('master.admin.event-application.store-product');
            Route::put('/event-application/products/{product}', function (Request $request, \App\Models\EventApplicationProduct $product) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\EventApplicationController::class)->updateProduct($request, $masterSite, $product);
            })->name('master.admin.event-application.update-product');
            Route::delete('/event-application/products/{product}', function (\App\Models\EventApplicationProduct $product) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\EventApplicationController::class)->destroyProduct($masterSite, $product);
            })->name('master.admin.event-application.destroy-product');
            Route::get('/event-application/products/{product}/submissions', function (\App\Models\EventApplicationProduct $product) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\EventApplicationController::class)->showSubmissions($masterSite, $product);
            })->name('master.admin.event-application.submissions');
            Route::put('/event-application/submissions/{submission}', function (Request $request, \App\Models\EventApplicationSubmission $submission) use ($masterSite) {
                return app(\App\Http\Controllers\Admin\EventApplicationController::class)->updateSubmission($request, $masterSite, $submission);
            })->name('master.admin.event-application.update-submission');
            
            // Custom Codes
            Route::get('/custom-codes', function () use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->customCodes($masterSite);
            })->name('master.admin.custom-codes');
            Route::post('/custom-codes', function (Request $request) use ($masterSite) {
                return app(\App\Http\Controllers\AdminController::class)->updateCustomCodes($request, $masterSite);
            })->name('master.admin.custom-codes.update');
        });
    }
});

// 공통 소셜 로그인 콜백 라우트 (모든 사이트가 사용)
Route::middleware('web')->group(function () {
    Route::get('/auth/{provider}/callback', [\App\Http\Controllers\SocialLoginController::class, 'handleProviderCallback'])
        ->name('social.callback');
});

// 도메인 기반 접근을 위한 라우트 그룹 (미들웨어에서 site가 설정된 경우)
// 이 라우트들은 site/{site} prefix 없이 직접 접근 가능
// 서브도메인/커스텀 도메인으로 접근할 때 사용
Route::middleware(['block.ip', 'verify.site.user'])->group(function () {
    // 어드민 라우트 (서브도메인/커스텀 도메인용)
    Route::prefix('admin')->middleware('auth')->group(function () {
        // Dashboard
        Route::get('/dashboard', function (Request $request) {
            $site = $request->attributes->get('site');
            if (!$site) {
                abort(404);
            }
            return app(\App\Http\Controllers\AdminController::class)->dashboard($site);
        });
        
        Route::get('/dashboard/chart-data', function (Request $request) {
            $site = $request->attributes->get('site');
            if (!$site) {
                abort(404);
            }
            return app(\App\Http\Controllers\AdminController::class)->getChartData($site);
        });
        
        // Users
        Route::get('/users', function (Request $request) {
            $site = $request->attributes->get('site');
            if (!$site) {
                abort(404);
            }
            return app(\App\Http\Controllers\AdminController::class)->users($site, $request);
        });
        
        Route::post('/users', function (Request $request) {
            $site = $request->attributes->get('site');
            if (!$site) {
                abort(404);
            }
            return app(\App\Http\Controllers\AdminController::class)->storeUser($request, $site);
        });
        
        Route::get('/users/{user}', function (Request $request, \App\Models\User $user) {
            $site = $request->attributes->get('site');
            if (!$site) {
                abort(404);
            }
            return app(\App\Http\Controllers\AdminController::class)->userDetail($site, $user);
        });
        
        Route::put('/users/{user}', function (Request $request, \App\Models\User $user) {
            $site = $request->attributes->get('site');
            if (!$site) {
                abort(404);
            }
            return app(\App\Http\Controllers\AdminController::class)->updateUser($request, $site, $user);
        });
        
        // Settings
        Route::get('/settings', function (Request $request) {
            $site = $request->attributes->get('site');
            if (!$site) {
                abort(404);
            }
            return app(\App\Http\Controllers\SiteSettingController::class)->index($site);
        });
        
        Route::put('/settings', function (Request $request) {
            $site = $request->attributes->get('site');
            if (!$site) {
                abort(404);
            }
            return app(\App\Http\Controllers\SiteSettingController::class)->update($request, $site);
        });
        
        Route::post('/settings/upload-image', function (Request $request) {
            $site = $request->attributes->get('site');
            if (!$site) {
                abort(404);
            }
            return app(\App\Http\Controllers\SiteSettingController::class)->uploadImage($request, $site);
        });
        
        // 나머지 어드민 라우트는 필요시 추가
        // 일단 주요 라우트만 정의하고, 나머지는 slug 기반 라우트로 리다이렉트하거나
        // 필요에 따라 추가
    });
});

Route::middleware(['block.ip', 'verify.site.user', 'auth'])->group(function () {
    Route::get('/my-sites', function (Request $request) {
        $site = $request->attributes->get('site');
        if (!$site || !$site->isMasterSite()) {
            abort(404);
        }
        return app(\App\Http\Controllers\UserMySitesController::class)->index($request, $site);
    })->name('users.my-sites');
    
    Route::get('/profile', function (Request $request) {
        $site = $request->attributes->get('site');
        if (!$site) {
            abort(404);
        }
        return app(\App\Http\Controllers\UserController::class)->profile($site);
    })->name('users.profile');
    
    Route::get('/create-site', function (Request $request) {
        $site = $request->attributes->get('site');
        if (!$site || !$site->isMasterSite()) {
            abort(404);
        }
        return app(\App\Http\Controllers\UserSiteController::class)->selectPlan($site);
    })->name('user-sites.select-plan.master');
});

// Site-based routes (멀티테넌트 구조 - slug 사용)
Route::prefix('site/{site}')->middleware(['block.ip', 'verify.site.user'])->group(function () {
    
    // Home
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Search Routes
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    
    // Terms & Privacy Routes
    Route::get('/terms-of-service', [SiteSettingController::class, 'getTermsOfService'])->name('terms-of-service');
    Route::get('/privacy-policy', [SiteSettingController::class, 'getPrivacyPolicy'])->name('privacy-policy');
    
    // Chat API (for widget - public access)
    Route::get('/api/chat/messages', [ChatController::class, 'getMessages'])->name('api.chat.messages');
    Route::post('/api/chat/messages', [ChatController::class, 'sendMessage'])->name('api.chat.send-message');
    Route::post('/api/chat/report', [ChatController::class, 'reportMessage'])->name('api.chat.report');
    Route::post('/api/chat/block', [ChatController::class, 'blockUser'])->name('api.chat.block');
    
    // Post and Comment Report API (for users - auth required)
    Route::middleware('auth')->group(function () {
        Route::post('/api/posts/{post}/report', [\App\Http\Controllers\PostController::class, 'reportPost'])->name('api.posts.report');
        Route::post('/api/boards/{boardSlug}/posts/{post}/comments/{comment}/report', [\App\Http\Controllers\CommentController::class, 'reportComment'])->name('api.comments.report');
    });
    
    // SEO Routes (sitemap.xml과 robots.txt는 루트 레벨로 이동)
    Route::get('/robots.txt/download', [\App\Http\Controllers\RobotsController::class, 'download'])->name('robots.download');
    Route::get('/rss.xml', [\App\Http\Controllers\RssController::class, 'index'])->name('rss');
    Route::get('/ads.txt', [\App\Http\Controllers\AdsController::class, 'index'])->name('ads');

    // Authentication Routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/register/send-verification', [AuthController::class, 'sendVerificationEmail'])->name('register.send-verification');
        Route::post('/register/verify-code', [AuthController::class, 'verifyEmailCode'])->name('register.verify-code');
        Route::post('/register/send-phone-verification', [AuthController::class, 'sendPhoneVerification'])->name('register.send-phone-verification');
        Route::post('/register/verify-phone-code', [AuthController::class, 'verifyPhoneCode'])->name('register.verify-phone-code');
        Route::post('/register/verify-referrer', [AuthController::class, 'verifyReferrer'])->name('register.verify-referrer');
        
        // Social Login Routes
        Route::get('/auth/{provider}', [\App\Http\Controllers\SocialLoginController::class, 'redirectToProvider'])->name('social.login');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    // Board Routes
    Route::get('/boards', [BoardController::class, 'index'])->name('boards.index');
    
    // Attendance Routes
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->middleware('auth')->name('attendance.store');
    Route::get('/attendance/points-info', [AttendanceController::class, 'pointsInfo'])->name('attendance.points-info');
    
    // User Profile Routes (슬러그 기반 접근용)
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\UserController::class, 'profile'])->name('users.profile.slug');
        Route::put('/profile', [\App\Http\Controllers\UserController::class, 'updateProfile'])->name('users.profile.update');
        Route::get('/my-sites', [\App\Http\Controllers\UserMySitesController::class, 'index'])->name('users.my-sites.slug');
        Route::post('/my-sites/change-plan', [\App\Http\Controllers\UserMySitesController::class, 'changePlan'])->name('user-sites.change-plan-process');
    });
    
    // 플랜 변경 및 서버 업그레이드 페이지는 로그인하지 않아도 볼 수 있음 (컨트롤러에서 로그인 체크)
    Route::get('/my-sites/change-plan', [\App\Http\Controllers\UserMySitesController::class, 'showChangePlan'])->name('user-sites.change-plan');
    Route::get('/my-sites/{userSite}/change-plan', [\App\Http\Controllers\UserMySitesController::class, 'showChangePlan'])->name('user-sites.change-plan-site');
    Route::get('/my-sites/{userSite}/server-upgrade', [\App\Http\Controllers\UserMySitesController::class, 'showServerUpgrade'])->name('user-sites.server-upgrade');
    
    Route::middleware('auth')->group(function () {
        Route::get('/my-sites/{userSite}/addons', [\App\Http\Controllers\UserMySitesController::class, 'showAddons'])->name('user-sites.addons');
        Route::get('/my-sites/{userSite}/sso', [\App\Http\Controllers\UserMySitesController::class, 'ssoToSiteAdmin'])->name('user-sites.sso');
        Route::put('/my-sites/{userSite}/domain', [\App\Http\Controllers\UserMySitesController::class, 'updateDomain'])->name('user-sites.update-domain');
        Route::delete('/my-sites/{userSite}/domain', [\App\Http\Controllers\UserMySitesController::class, 'removeDomain'])->name('user-sites.remove-domain');
        Route::get('/point-history', [\App\Http\Controllers\UserController::class, 'pointHistory'])->name('users.point-history');
        Route::get('/saved-posts', [\App\Http\Controllers\UserController::class, 'savedPosts'])->name('users.saved-posts');
        Route::get('/my-posts', [\App\Http\Controllers\UserController::class, 'myPosts'])->name('users.my-posts');
        Route::get('/my-comments', [\App\Http\Controllers\UserController::class, 'myComments'])->name('users.my-comments');
        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{message}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
        Route::post('/messages/{message}/receive-points', [\App\Http\Controllers\MessageController::class, 'receivePoints'])->name('messages.receive-points');
        Route::delete('/messages/{message}', [\App\Http\Controllers\MessageController::class, 'destroy'])->name('messages.destroy');
        
        // User Site Creation (마스터 사이트에서만 사용 가능)
        Route::get('/create-site', [\App\Http\Controllers\UserSiteController::class, 'selectPlan'])->name('user-sites.select-plan');
        Route::get('/create-site/form', [\App\Http\Controllers\UserSiteController::class, 'create'])->name('user-sites.create');
        Route::post('/create-site', [\App\Http\Controllers\UserSiteController::class, 'store'])->name('user-sites.store');
    });
    
    // Board Management Routes (Admin only) - must be before /boards/{slug}
    Route::middleware('auth')->group(function () {
        Route::get('/boards/create', [BoardController::class, 'create'])->name('boards.create');
        Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');
    });

    // Post Routes - MUST be before /boards/{slug} to avoid route conflict
    // These routes have more specific patterns, so they should be matched first
    // Post create route - must be FIRST to avoid conflict with /boards/{slug}
    Route::get('/boards/{boardSlug}/posts/create', [PostController::class, 'create'])
        ->middleware('auth')
        ->name('posts.create');
    Route::get('/boards/{boardSlug}/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/boards/{boardSlug}/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    
    // Post Management Routes
    Route::middleware('auth')->group(function () {
        Route::post('/boards/{boardSlug}/posts', [PostController::class, 'store'])->name('posts.store');
        Route::get('/boards/{boardSlug}/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
        Route::put('/boards/{boardSlug}/posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/boards/{boardSlug}/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        
        // Comment Routes
        Route::post('/boards/{boardSlug}/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::get('/boards/{boardSlug}/posts/{post}/comments/{comment}/edit', [CommentController::class, 'edit'])->name('comments.edit');
        Route::put('/boards/{boardSlug}/posts/{post}/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/boards/{boardSlug}/posts/{post}/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('/boards/{boardSlug}/posts/{post}/comments/{comment}/adopt', [CommentController::class, 'adopt'])->name('comments.adopt');
    });
    
    // 이미지 업로드 (Summernote용) - 별도 경로
    Route::middleware('auth')->post('/posts/upload-image', [PostController::class, 'uploadImage'])->name('posts.upload-image');
    
    // 추천/비추천
    Route::middleware('auth')->post('/boards/{boardSlug}/posts/{postId}/toggle-like', [PostController::class, 'toggleLike'])->name('posts.toggle-like');
    
    // 저장하기/저장 취소
    Route::middleware('auth')->post('/boards/{boardSlug}/posts/{post}/toggle-save', [PostController::class, 'toggleSave'])->name('posts.toggle-save');
    
    // 이벤트 참여
    Route::middleware('auth')->post('/boards/{boardSlug}/posts/{post}/participate', [PostController::class, 'participate'])->name('posts.participate');
    
    // 이벤트 참여자 일괄 포인트 지급 (운영자만)
    Route::middleware('auth')->post('/boards/{boardSlug}/posts/{post}/award-points', [PostController::class, 'awardPoints'])->name('posts.award-points');
    Route::middleware('auth')->post('/boards/{boardSlug}/posts/{post}/award-quiz-points', [PostController::class, 'awardQuizPoints'])->name('posts.award-quiz-points');
    
    // 이벤트 종료 (관리자만)
    Route::middleware('auth')->post('/boards/{boardSlug}/posts/{post}/end-event', [PostController::class, 'endEvent'])->name('posts.end-event');
    
    // 질의응답 상태 변경 (관리자만)
    Route::middleware('auth')->post('/boards/{boardSlug}/posts/{post}/update-qa-status', [PostController::class, 'updateQaStatus'])->name('posts.update-qa-status');

    // Board detail route - MUST be last to avoid conflicts with /boards/{boardSlug}/posts/*
    Route::get('/boards/{slug}', [BoardController::class, 'show'])->name('boards.show');

    // Point Exchange Routes (User) - Custom Pages 라우트보다 먼저 배치
    Route::get('/point-exchange', [PointExchangeController::class, 'index'])->name('point-exchange.index');
    Route::get('/point-exchange/{product}', [PointExchangeController::class, 'show'])->name('point-exchange.show');
    Route::post('/point-exchange/{product}', [PointExchangeController::class, 'store'])->name('point-exchange.store');
    Route::put('/point-exchange/applications/{application}/cancel', [PointExchangeController::class, 'cancel'])->name('point-exchange.cancel');
    
    // Event Application Routes (User) - Custom Pages 라우트보다 먼저 배치
    Route::get('/event-application', [\App\Http\Controllers\EventApplicationController::class, 'index'])->name('event-application.index');
    Route::get('/event-application/{product}', [\App\Http\Controllers\EventApplicationController::class, 'show'])->name('event-application.show');
    Route::post('/event-application/{product}', [\App\Http\Controllers\EventApplicationController::class, 'store'])->name('event-application.store');
    Route::put('/event-application/submissions/{submission}/cancel', [\App\Http\Controllers\EventApplicationController::class, 'cancel'])->name('event-application.cancel');

    // Custom Pages (게시판 라우트 이후에 배치)
    Route::get('/{slug}', [HomeController::class, 'showCustomPage'])->name('custom-pages.show')->where('slug', '[a-zA-Z0-9-_가-힣]+');

    // Board Management Routes (Admin only) - edit/update/delete
    Route::middleware('auth')->group(function () {
        Route::get('/boards/{board}/edit', [BoardController::class, 'edit'])->name('boards.edit');
        Route::put('/boards/{board}', [BoardController::class, 'update'])->name('boards.update');
        Route::delete('/boards/{board}', [BoardController::class, 'destroy'])->name('boards.destroy');
        Route::post('/boards/{board}/delete-posts', [BoardController::class, 'deletePostsByDateRange'])->name('boards.delete-posts');
        Route::post('/boards/{board}/update-general', [BoardController::class, 'updateGeneral'])->name('boards.update-general');
        Route::post('/boards/{board}/update-features', [BoardController::class, 'updateFeatures'])->name('boards.update-features');
        Route::post('/boards/{board}/update-seo', [BoardController::class, 'updateSeo'])->name('boards.update-seo');
        Route::post('/boards/{board}/update-grade-points', [BoardController::class, 'updateGradePoints'])->name('boards.update-grade-points');
        Route::post('/boards/{board}/update-template', [BoardController::class, 'updateTemplate'])->name('boards.update-template');
        Route::post('/boards/{board}/update-footer', [BoardController::class, 'updateFooter'])->name('boards.update-footer');
        
        // Topic management routes
        Route::post('/boards/{board}/topics', [BoardController::class, 'storeTopic'])->name('boards.topics.store');
        Route::put('/boards/{board}/topics/{topic}', [BoardController::class, 'updateTopic'])->name('boards.topics.update');
        Route::delete('/boards/{board}/topics/{topic}', [BoardController::class, 'destroyTopic'])->name('boards.topics.destroy');
        Route::post('/boards/{board}/topics/update-order', [BoardController::class, 'updateTopicOrder'])->name('boards.topics.update-order');
    });

    // Admin Routes
    Route::prefix('admin')->middleware('auth')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/dashboard/chart-data', [AdminController::class, 'getChartData'])->name('admin.dashboard.chart');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::get('/users/{user}', [AdminController::class, 'userDetail'])->name('admin.users.detail');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::get('/registration-settings', [AdminController::class, 'registrationSettings'])->name('admin.registration-settings');
        Route::post('/registration-settings', [AdminController::class, 'updateRegistrationSettings'])->name('admin.registration-settings.update');
        Route::post('/registration-settings/test-sms', [AdminController::class, 'testSms'])->name('admin.registration-settings.test-sms');
        Route::get('/mail-settings', [AdminController::class, 'mailSettings'])->name('admin.mail-settings');
        Route::post('/mail-settings', [AdminController::class, 'updateMailSettings'])->name('admin.mail-settings.update');
        Route::post('/mail-settings/test', [AdminController::class, 'testMail'])->name('admin.mail-settings.test');
        
        // Contact Forms Management
        Route::get('/contact-forms', [AdminController::class, 'contactForms'])->name('admin.contact-forms.index');
        Route::post('/contact-forms', [AdminController::class, 'storeContactForm'])->name('admin.contact-forms.store');
        Route::get('/contact-forms/{contactForm}', [AdminController::class, 'showContactForm'])->name('admin.contact-forms.show');
        Route::put('/contact-forms/{contactForm}', [AdminController::class, 'updateContactForm'])->name('admin.contact-forms.update');
        Route::delete('/contact-forms/{contactForm}', [AdminController::class, 'deleteContactForm'])->name('admin.contact-forms.delete');
        
        // Maps Management
        Route::get('/maps', [AdminController::class, 'mapsIndex'])->name('admin.maps.index');
        Route::post('/maps', [AdminController::class, 'mapsStore'])->name('admin.maps.store');
        Route::get('/maps/{map}/edit', [AdminController::class, 'mapsEdit'])->name('admin.maps.edit');
        Route::put('/maps/{map}', [AdminController::class, 'mapsUpdate'])->name('admin.maps.update');
        Route::delete('/maps/{map}', [AdminController::class, 'mapsDestroy'])->name('admin.maps.delete');
        Route::post('/maps/geocode', [AdminController::class, 'mapsGeocode'])->name('admin.maps.geocode');
        Route::post('/maps/add-default', [AdminController::class, 'mapsAddDefault'])->name('admin.maps.add-default');
        
        Route::get('/user-ranks', [AdminController::class, 'userRanks'])->name('admin.user-ranks');
        Route::post('/user-ranks/store', [AdminController::class, 'userRanksStore'])->name('admin.user-ranks.store');
        Route::post('/user-ranks/update', [AdminController::class, 'userRanksUpdate'])->name('admin.user-ranks.update');
        Route::delete('/user-ranks/{userRank}', [AdminController::class, 'userRanksDelete'])->name('admin.user-ranks.delete');
        
        // My Page Settings
        Route::get('/my-page-settings', [AdminController::class, 'myPageSettings'])->name('admin.my-page-settings');
        Route::put('/my-page-settings', [AdminController::class, 'updateMyPageSettings'])->name('admin.my-page-settings.update');
        
        // Crawler management routes
        Route::get('/crawlers', [AdminController::class, 'crawlersIndex'])->name('admin.crawlers.index');
        Route::post('/crawlers', [AdminController::class, 'crawlersStore'])->name('admin.crawlers.store');
        Route::get('/crawlers/{crawler}', [AdminController::class, 'crawlersShow'])->name('admin.crawlers.show');
        Route::get('/crawlers/{crawler}/edit', [AdminController::class, 'crawlersEdit'])->name('admin.crawlers.edit');
        Route::put('/crawlers/{crawler}', [AdminController::class, 'crawlersUpdate'])->name('admin.crawlers.update');
        Route::delete('/crawlers/{crawler}', [AdminController::class, 'crawlersDestroy'])->name('admin.crawlers.delete');
        Route::post('/crawlers/{crawler}/toggle-active', [AdminController::class, 'crawlersToggleActive'])->name('admin.crawlers.toggle-active');
        Route::post('/crawlers/test', [AdminController::class, 'crawlersTest'])->name('admin.crawlers.test');
        Route::post('/crawlers/run-all', [AdminController::class, 'crawlersRunAll'])->name('admin.crawlers.run-all');
        Route::get('/boards', [AdminController::class, 'boards'])->name('admin.boards');
        Route::get('/boards/{board}/topics', [AdminController::class, 'getBoardTopics'])->name('admin.boards.topics');
        Route::post('/banned-words', [AdminController::class, 'updateBannedWords'])->name('admin.banned-words.update');
        Route::get('/posts', [AdminController::class, 'posts'])->name('admin.posts');
        Route::put('/posts/{post}/board', [AdminController::class, 'updatePostBoard'])->name('admin.posts.update-board');
        Route::put('/posts/{post}/views', [AdminController::class, 'updatePostViews'])->name('admin.posts.update-views');
        
        // Message management routes
        Route::get('/messages', [AdminController::class, 'messages'])->name('admin.messages.index');
        Route::put('/messages/settings', [AdminController::class, 'updateMessageSettings'])->name('admin.messages.update-settings');
        Route::put('/messages/{message}', [AdminController::class, 'updateMessage'])->name('admin.messages.update');
        Route::delete('/messages/{message}', [AdminController::class, 'deleteMessage'])->name('admin.messages.delete');
        
        // Banner management routes
        Route::get('/banners', [AdminController::class, 'bannersIndex'])->name('admin.banners.index');
        Route::post('/banners', [AdminController::class, 'bannersUpdate'])->name('admin.banners.update');
        Route::get('/banners/{location}', [AdminController::class, 'bannersDetail'])->name('admin.banners.detail');
        Route::post('/banners/store', [AdminController::class, 'bannersStore'])->name('admin.banners.store');
        Route::post('/banners/update-order', [AdminController::class, 'bannersUpdateOrder'])->name('admin.banners.update-order');
        Route::post('/banners/update-item', [AdminController::class, 'bannersUpdateItem'])->name('admin.banners.update-item');
        Route::post('/banners/save-all', [AdminController::class, 'bannersSaveAll'])->name('admin.banners.save-all');
        Route::delete('/banners/{banner}', [AdminController::class, 'bannersDelete'])->name('admin.banners.delete');
        
        Route::get('/popups', [AdminController::class, 'popupsIndex'])->name('admin.popups.index');
        Route::post('/popups/store', [AdminController::class, 'popupsStore'])->name('admin.popups.store');
        Route::post('/popups/settings', [AdminController::class, 'popupsUpdateSettings'])->name('admin.popups.update-settings');
        Route::post('/popups/update-order', [AdminController::class, 'popupsUpdateOrder'])->name('admin.popups.update-order');
        Route::post('/popups/{popup}/update', [AdminController::class, 'popupsUpdateItem'])->name('admin.popups.update-item');
        Route::delete('/popups/{popup}', [AdminController::class, 'popupsDelete'])->name('admin.popups.delete');
        
        // IP blocking routes
        Route::get('/blocked-ips', [AdminController::class, 'blockedIpsIndex'])->name('admin.blocked-ips.index');
        Route::post('/blocked-ips', [AdminController::class, 'blockedIpsStore'])->name('admin.blocked-ips.store');
        Route::delete('/blocked-ips/{ip}', [AdminController::class, 'blockedIpsDestroy'])->name('admin.blocked-ips.destroy');
        
        // Menu management routes
        Route::get('/menus', [AdminController::class, 'menus'])->name('admin.menus');
        Route::post('/menus', [AdminController::class, 'storeMenu'])->name('admin.menus.store');
        Route::put('/menus/order', [AdminController::class, 'updateMenuOrder'])->name('admin.menus.update-order');
        Route::delete('/menus/{menu}', [AdminController::class, 'deleteMenu'])->name('admin.menus.delete');
        
        // Mobile Menu Routes
        Route::post('/mobile-menus', [AdminController::class, 'storeMobileMenu'])->name('admin.mobile-menus.store');
        Route::put('/mobile-menus/order', [AdminController::class, 'updateMobileMenuOrder'])->name('admin.mobile-menus.update-order');
        Route::post('/mobile-menus/design-type', [AdminController::class, 'updateMobileMenuDesignType'])->name('admin.mobile-menus.design-type');
        Route::put('/mobile-menus/{mobileMenu}', [AdminController::class, 'updateMobileMenu'])->name('admin.mobile-menus.update');
        Route::delete('/mobile-menus/{mobileMenu}', [AdminController::class, 'deleteMobileMenu'])->name('admin.mobile-menus.delete');
        
        // Temporary route for testing - add points to current user
        Route::get('/add-points/{points}', function (\App\Models\Site $site, $points) {
            if (!auth()->check() || !auth()->user()->canManage()) {
                abort(403);
            }
            $user = auth()->user();
            $user->addPoints((int)$points);
            return response()->json(['success' => true, 'message' => '포인트가 지급되었습니다.', 'points' => $user->points]);
        })->name('admin.add-points');
        
        // Attendance Management
        Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::put('/attendance', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
        
        // Settings
        Route::get('/settings', [SiteSettingController::class, 'index'])->name('admin.settings');
        Route::put('/settings', [SiteSettingController::class, 'update'])->name('admin.settings.update');
        Route::post('/settings/upload-image', [SiteSettingController::class, 'uploadImage'])->name('admin.settings.upload-image');
        Route::get('/settings/preview-header', [SiteSettingController::class, 'previewHeader'])->name('admin.settings.preview-header');
        Route::get('/settings/preview-mobile-header', [SiteSettingController::class, 'previewMobileHeader'])->name('admin.settings.preview-mobile-header');
        Route::post('/settings/increase-visitor', [SiteSettingController::class, 'increaseVisitor'])->name('admin.settings.increase-visitor');
        Route::get('/settings/terms-of-service', [SiteSettingController::class, 'getTermsOfService'])->name('admin.settings.terms-of-service');
        Route::get('/settings/privacy-policy', [SiteSettingController::class, 'getPrivacyPolicy'])->name('admin.settings.privacy-policy');
        
        // Server Management
        Route::get('/server-management', [AdminController::class, 'serverManagement'])->name('admin.server-management');
        
        // Toggle Menus
        Route::get('/toggle-menus', [AdminController::class, 'toggleMenus'])->name('admin.toggle-menus');
        Route::get('/toggle-menus/list', [AdminController::class, 'getToggleMenusList'])->name('admin.toggle-menus.list');
        Route::get('/toggle-menus/{toggleMenu}', [AdminController::class, 'getToggleMenu'])->name('admin.toggle-menus.show');
        Route::post('/toggle-menus', [AdminController::class, 'storeToggleMenu'])->name('admin.toggle-menus.store');
        Route::put('/toggle-menus/{toggleMenu}', [AdminController::class, 'updateToggleMenu'])->name('admin.toggle-menus.update');
        Route::delete('/toggle-menus/{toggleMenu}', [AdminController::class, 'deleteToggleMenu'])->name('admin.toggle-menus.delete');
        Route::put('/toggle-menus/order', [AdminController::class, 'updateToggleMenuOrder'])->name('admin.toggle-menus.update-order');
        Route::post('/toggle-menus/{toggleMenu}/toggle-active', [AdminController::class, 'toggleToggleMenuActive'])->name('admin.toggle-menus.toggle-active');
        
        // Sidebar Widgets
        Route::match(['get', 'post'], '/sidebar-widgets', [AdminController::class, 'sidebarWidgets'])->name('admin.sidebar-widgets');
        Route::post('/sidebar-widgets/store', [AdminController::class, 'storeSidebarWidget'])->name('admin.sidebar-widgets.store');
        Route::put('/sidebar-widgets/{widget}', [AdminController::class, 'updateSidebarWidget'])->name('admin.sidebar-widgets.update');
        Route::delete('/sidebar-widgets/{widget}', [AdminController::class, 'deleteSidebarWidget'])->name('admin.sidebar-widgets.delete');
        Route::post('/sidebar-widgets/reorder', [AdminController::class, 'reorderSidebarWidgets'])->name('admin.sidebar-widgets.reorder');
        
        Route::match(['get', 'post'], '/main-widgets', [AdminController::class, 'mainWidgets'])->name('admin.main-widgets');
        Route::post('/main-widgets/containers/store', [AdminController::class, 'storeMainWidgetContainer'])->name('admin.main-widgets.containers.store');
        Route::put('/main-widgets/containers/{container}', [AdminController::class, 'updateMainWidgetContainer'])->name('admin.main-widgets.containers.update');
        Route::delete('/main-widgets/containers/{container}', [AdminController::class, 'deleteMainWidgetContainer'])->name('admin.main-widgets.containers.delete');
        Route::post('/main-widgets/store', [AdminController::class, 'storeMainWidget'])->name('admin.main-widgets.store');
        Route::put('/main-widgets/{widget}', [AdminController::class, 'updateMainWidget'])->name('admin.main-widgets.update');
        Route::delete('/main-widgets/{widget}', [AdminController::class, 'deleteMainWidget'])->name('admin.main-widgets.delete');
        Route::post('/main-widgets/containers/reorder', [AdminController::class, 'reorderMainWidgetContainers'])->name('admin.main-widgets.containers.reorder');
        Route::post('/main-widgets/reorder', [AdminController::class, 'reorderMainWidgets'])->name('admin.main-widgets.reorder');
        
        // Custom Pages Management
        Route::get('/custom-pages', [AdminController::class, 'customPages'])->name('admin.custom-pages');
        Route::post('/custom-pages/store', [AdminController::class, 'storeCustomPage'])->name('admin.custom-pages.store');
        Route::get('/custom-pages/{customPage}/edit', [AdminController::class, 'editCustomPage'])->name('admin.custom-pages.edit');
        Route::put('/custom-pages/{customPage}', [AdminController::class, 'updateCustomPage'])->name('admin.custom-pages.update');
        Route::delete('/custom-pages/{customPage}', [AdminController::class, 'deleteCustomPage'])->name('admin.custom-pages.delete');
        Route::post('/custom-pages/{customPage}/containers/store', [AdminController::class, 'storeCustomPageWidgetContainer'])->name('admin.custom-pages.containers.store');
        Route::put('/custom-pages/{customPage}/containers/{container}', [AdminController::class, 'updateCustomPageWidgetContainer'])->name('admin.custom-pages.containers.update');
        Route::delete('/custom-pages/{customPage}/containers/{container}', [AdminController::class, 'deleteCustomPageWidgetContainer'])->name('admin.custom-pages.containers.delete');
        Route::post('/custom-pages/{customPage}/widgets/store', [AdminController::class, 'storeCustomPageWidget'])->name('admin.custom-pages.widgets.store');
        Route::put('/custom-pages/{customPage}/widgets/{widget}', [AdminController::class, 'updateCustomPageWidget'])->name('admin.custom-pages.widgets.update');
        Route::delete('/custom-pages/{customPage}/widgets/{widget}', [AdminController::class, 'deleteCustomPageWidget'])->name('admin.custom-pages.widgets.delete');
        Route::post('/custom-pages/{customPage}/containers/reorder', [AdminController::class, 'reorderCustomPageWidgetContainers'])->name('admin.custom-pages.containers.reorder');
        Route::post('/custom-pages/{customPage}/widgets/reorder', [AdminController::class, 'reorderCustomPageWidgets'])->name('admin.custom-pages.widgets.reorder');
        
        // Chat Management
        Route::get('/chat', [AdminChatController::class, 'index'])->name('admin.chat.index');
        Route::put('/chat/settings', [AdminChatController::class, 'updateSettings'])->name('admin.chat.update-settings');
        Route::delete('/chat/messages/{message}', [AdminChatController::class, 'deleteMessage'])->name('admin.chat.delete-message');
        Route::post('/chat/ban-user', [AdminChatController::class, 'banUser'])->name('admin.chat.ban-user');
        
        // Reports Management
        Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/reports/{report}', [AdminReportController::class, 'show'])->name('admin.reports.show');
        Route::put('/reports/{report}/status', [AdminReportController::class, 'updateStatus'])->name('admin.reports.update-status');
        Route::post('/reports/penalties', [AdminReportController::class, 'issuePenalty'])->name('admin.reports.issue-penalty');
        Route::delete('/reports/penalties/{penalty}', [AdminReportController::class, 'removePenalty'])->name('admin.reports.remove-penalty');
        
        // Point Exchange Management
        Route::get('/point-exchange', [AdminPointExchangeController::class, 'index'])->name('admin.point-exchange.index');
        Route::put('/point-exchange/settings', [AdminPointExchangeController::class, 'updateSettings'])->name('admin.point-exchange.update-settings');
        Route::post('/point-exchange/products', [AdminPointExchangeController::class, 'storeProduct'])->name('admin.point-exchange.store-product');
        Route::put('/point-exchange/products/{product}', [AdminPointExchangeController::class, 'updateProduct'])->name('admin.point-exchange.update-product');
        Route::delete('/point-exchange/products/{product}', [AdminPointExchangeController::class, 'destroyProduct'])->name('admin.point-exchange.destroy-product');
        Route::get('/point-exchange/products/{product}/applications', [AdminPointExchangeController::class, 'showApplications'])->name('admin.point-exchange.applications');
        Route::put('/point-exchange/applications/{application}', [AdminPointExchangeController::class, 'updateApplication'])->name('admin.point-exchange.update-application');
        
        // Event Application Management
        Route::get('/event-application', [\App\Http\Controllers\Admin\EventApplicationController::class, 'index'])->name('admin.event-application.index');
        Route::put('/event-application/settings', [\App\Http\Controllers\Admin\EventApplicationController::class, 'updateSettings'])->name('admin.event-application.update-settings');
        Route::post('/event-application/products', [\App\Http\Controllers\Admin\EventApplicationController::class, 'storeProduct'])->name('admin.event-application.store-product');
        Route::put('/event-application/products/{product}', [\App\Http\Controllers\Admin\EventApplicationController::class, 'updateProduct'])->name('admin.event-application.update-product');
        Route::delete('/event-application/products/{product}', [\App\Http\Controllers\Admin\EventApplicationController::class, 'destroyProduct'])->name('admin.event-application.destroy-product');
        Route::get('/event-application/products/{product}/submissions', [\App\Http\Controllers\Admin\EventApplicationController::class, 'showSubmissions'])->name('admin.event-application.submissions');
        Route::put('/event-application/submissions/{submission}', [\App\Http\Controllers\Admin\EventApplicationController::class, 'updateSubmission'])->name('admin.event-application.update-submission');
        
        // Custom Codes Management
        Route::get('/custom-codes', [AdminController::class, 'customCodes'])->name('admin.custom-codes');
        Route::post('/custom-codes', [AdminController::class, 'updateCustomCodes'])->name('admin.custom-codes.update');
    });
    
    // Contact Form Submission (User)
    Route::post('/contact-forms/{contactForm}/submit', [\App\Http\Controllers\ContactFormController::class, 'submit'])->name('contact-forms.submit');
});

