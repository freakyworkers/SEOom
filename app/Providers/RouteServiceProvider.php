<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Route model binding for Site by slug (for web routes)
        Route::bind('site', function ($value, $route) {
            // 도메인 기반 접근 시 미들웨어에서 이미 설정한 site 사용
            $request = request();
            if ($request && $request->attributes->has('site')) {
                return $request->attributes->get('site');
            }
            
            // Check if it's a numeric ID (for master routes)
            if (is_numeric($value)) {
                return \App\Models\Site::findOrFail($value);
            }
            // Otherwise, treat as slug (for web routes)
            // Try to find by slug - don't enforce active status for now
            try {
                $site = \App\Models\Site::where('slug', $value)->first();
                if (!$site) {
                    \Log::warning("Site not found for slug: {$value}");
                    abort(404, "Site not found: {$value}");
                }
                return $site;
            } catch (\Exception $e) {
                \Log::error("Error binding site route: " . $e->getMessage());
                abort(404, "Site not found: {$value}");
            }
        });

        // Route model binding for userSite by slug (same as site)
        Route::bind('userSite', function ($value, $route) {
            // Check if it's a numeric ID
            if (is_numeric($value)) {
                return \App\Models\Site::findOrFail($value);
            }
            // Otherwise, treat as slug
            try {
                $site = \App\Models\Site::where('slug', $value)->first();
                if (!$site) {
                    \Log::warning("UserSite not found for slug: {$value}");
                    abort(404, "Site not found: {$value}");
                }
                return $site;
            } catch (\Exception $e) {
                \Log::error("Error binding userSite route: " . $e->getMessage());
                abort(404, "Site not found: {$value}");
            }
        });

        // Route model binding for Plan by slug
        Route::bind('plan', function ($value, $route) {
            // Check if it's a numeric ID
            if (is_numeric($value)) {
                return \App\Models\Plan::findOrFail($value);
            }
            // Otherwise, treat as slug
            try {
                $plan = \App\Models\Plan::where('slug', $value)->first();
                if (!$plan) {
                    \Log::warning("Plan not found for slug: {$value}");
                    abort(404, "Plan not found: {$value}");
                }
                return $plan;
            } catch (\Exception $e) {
                \Log::error("Error binding plan route: " . $e->getMessage());
                abort(404, "Plan not found: {$value}");
            }
        });

        // Route model binding for AddonProduct by ID or slug
        Route::bind('addonProduct', function ($value, $route) {
            // Check if it's a numeric ID
            if (is_numeric($value)) {
                return \App\Models\AddonProduct::findOrFail($value);
            }
            // Otherwise, treat as slug (if slug exists)
            try {
                $addonProduct = \App\Models\AddonProduct::where('slug', $value)->first();
                if (!$addonProduct) {
                    \Log::warning("AddonProduct not found for slug: {$value}");
                    abort(404, "AddonProduct not found: {$value}");
                }
                return $addonProduct;
            } catch (\Exception $e) {
                \Log::error("Error binding addonProduct route: " . $e->getMessage());
                abort(404, "AddonProduct not found: {$value}");
            }
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->prefix('master')
                ->group(base_path('routes/master.php'));
        });
    }
}

