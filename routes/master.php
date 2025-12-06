<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\MasterAuthController;
use App\Http\Controllers\Master\MasterDashboardController;
use App\Http\Controllers\Master\MasterSiteController;
use App\Http\Controllers\Master\MasterMonitoringController;
use App\Http\Controllers\Master\MasterBackupController;
use App\Http\Controllers\Master\MasterMasterSiteController;
use App\Http\Controllers\Master\MasterPlanController;
use App\Http\Controllers\Master\MasterAddonProductController;
use App\Http\Controllers\Master\MasterMapApiController;

/*
|--------------------------------------------------------------------------
| Master Console Routes
|--------------------------------------------------------------------------
|
| Routes for the master operations console
|
*/

// Master Authentication Routes
Route::middleware('web')->group(function () {
    Route::get('/login', [MasterAuthController::class, 'showLoginForm'])->name('master.login');
    Route::post('/login', [MasterAuthController::class, 'login']);
    
    // Master Console SSO (from master site)
    Route::get('/sso/{token}', [MasterAuthController::class, 'sso'])->name('master.console.sso');
});

Route::post('/logout', [MasterAuthController::class, 'logout'])->middleware('auth:master')->name('master.logout');

// SSO route (no auth required, token-based)
Route::middleware(['web'])->group(function () {
    Route::get('/sites/{site}/sso', [MasterSiteController::class, 'sso'])->name('master.sites.sso');
});

// Master Console Routes (Protected)
Route::middleware(['web', 'auth:master'])->group(function () {
    Route::get('/dashboard', [MasterDashboardController::class, 'index'])->name('master.dashboard');
    
    // Sites Management
    Route::prefix('sites')->group(function () {
        Route::get('/', [MasterSiteController::class, 'index'])->name('master.sites.index');
        Route::get('/create', [MasterSiteController::class, 'create'])->name('master.sites.create');
        Route::post('/', [MasterSiteController::class, 'store'])->name('master.sites.store');
        Route::get('/{site}', [MasterSiteController::class, 'show'])->name('master.sites.show');
        Route::get('/{site}/edit', [MasterSiteController::class, 'edit'])->name('master.sites.edit');
        Route::put('/{site}', [MasterSiteController::class, 'update'])->name('master.sites.update');
        Route::delete('/{site}', [MasterSiteController::class, 'destroy'])->name('master.sites.destroy');
        Route::post('/{site}/suspend', [MasterSiteController::class, 'suspend'])->name('master.sites.suspend');
        Route::post('/{site}/activate', [MasterSiteController::class, 'activate'])->name('master.sites.activate');
        Route::post('/{site}/sso-token', [MasterSiteController::class, 'generateSsoToken'])->name('master.sites.sso-token');
    });
    
    // Monitoring
    Route::get('/monitoring', [MasterMonitoringController::class, 'index'])->name('master.monitoring');
    
    // Backup
    Route::prefix('backup')->group(function () {
        Route::get('/', [MasterBackupController::class, 'index'])->name('master.backup.index');
        Route::post('/', [MasterBackupController::class, 'create'])->name('master.backup.create');
        Route::get('/download/{filename}', [MasterBackupController::class, 'download'])->name('master.backup.download');
        Route::delete('/{filename}', [MasterBackupController::class, 'destroy'])->name('master.backup.destroy');
    });
    
    // Master Sites Management (마스터 사이트 관리)
    Route::prefix('master-sites')->group(function () {
        Route::get('/', [MasterMasterSiteController::class, 'index'])->name('master.master-sites.index');
        Route::get('/create', [MasterMasterSiteController::class, 'create'])->name('master.master-sites.create');
        Route::post('/', [MasterMasterSiteController::class, 'store'])->name('master.master-sites.store');
        Route::get('/{site}', [MasterMasterSiteController::class, 'show'])->name('master.master-sites.show');
        Route::get('/{site}/edit', [MasterMasterSiteController::class, 'edit'])->name('master.master-sites.edit');
        Route::put('/{site}', [MasterMasterSiteController::class, 'update'])->name('master.master-sites.update');
        Route::delete('/{site}', [MasterMasterSiteController::class, 'destroy'])->name('master.master-sites.destroy');
        Route::post('/{site}/suspend', [MasterMasterSiteController::class, 'suspend'])->name('master.master-sites.suspend');
        Route::post('/{site}/activate', [MasterMasterSiteController::class, 'activate'])->name('master.master-sites.activate');
    });
    
    // Plans Management (요금제 관리)
    Route::prefix('plans')->group(function () {
        Route::get('/', [MasterPlanController::class, 'index'])->name('master.plans.index');
        Route::get('/create', [MasterPlanController::class, 'create'])->name('master.plans.create');
        Route::post('/', [MasterPlanController::class, 'store'])->name('master.plans.store');
        Route::get('/{plan}', [MasterPlanController::class, 'show'])->name('master.plans.show');
        Route::get('/{plan}/edit', [MasterPlanController::class, 'edit'])->name('master.plans.edit');
        Route::put('/{plan}', [MasterPlanController::class, 'update'])->name('master.plans.update');
        Route::delete('/{plan}', [MasterPlanController::class, 'destroy'])->name('master.plans.destroy');
    });
    
    // Addon Products Management (추가 구매 상품 관리)
    Route::prefix('addon-products')->group(function () {
        Route::get('/', [MasterAddonProductController::class, 'index'])->name('master.addon-products.index');
        Route::get('/create', [MasterAddonProductController::class, 'create'])->name('master.addon-products.create');
        Route::post('/', [MasterAddonProductController::class, 'store'])->name('master.addon-products.store');
        Route::get('/{addonProduct}', [MasterAddonProductController::class, 'show'])->name('master.addon-products.show');
        Route::get('/{addonProduct}/edit', [MasterAddonProductController::class, 'edit'])->name('master.addon-products.edit');
        Route::put('/{addonProduct}', [MasterAddonProductController::class, 'update'])->name('master.addon-products.update');
        Route::delete('/{addonProduct}', [MasterAddonProductController::class, 'destroy'])->name('master.addon-products.destroy');
    });
    
    // Subscription Settings (구독 설정)
    Route::prefix('subscription-settings')->group(function () {
        Route::get('/', [\App\Http\Controllers\Master\MasterSubscriptionSettingsController::class, 'index'])->name('master.subscription-settings.index');
        Route::post('/', [\App\Http\Controllers\Master\MasterSubscriptionSettingsController::class, 'update'])->name('master.subscription-settings.update');
    });
    
    // Map API Settings (지도 API 설정)
    Route::prefix('map-api')->group(function () {
        Route::get('/', [MasterMapApiController::class, 'index'])->name('master.map-api.index');
        Route::put('/', [MasterMapApiController::class, 'update'])->name('master.map-api.update');
    });
});

