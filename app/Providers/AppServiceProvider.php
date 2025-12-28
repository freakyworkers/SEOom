<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cloudflare/ALB 프록시 뒤에서 HTTPS URL 생성 강제
        // X-Forwarded-Proto 헤더가 https인 경우에만 HTTPS 강제
        if (request()->header('X-Forwarded-Proto') === 'https' || 
            request()->secure() || 
            config('app.force_https', false)) {
            URL::forceScheme('https');
        }
        // Laravel Socialite 패키지가 설치되어 있는 경우에만 프로바이더 등록
        if (class_exists(\Laravel\Socialite\Contracts\Factory::class)) {
            try {
                $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);
                
                // 네이버 소셜 로그인 프로바이더 등록
                $socialite->extend('naver', function ($app) use ($socialite) {
                    $config = $app['config']['services.naver'] ?? [
                        'client_id' => '',
                        'client_secret' => '',
                        'redirect' => '',
                    ];
                    return $socialite->buildProvider(
                        \App\Socialite\NaverProvider::class,
                        $config
                    );
                });

                // 카카오 소셜 로그인 프로바이더 등록
                $socialite->extend('kakao', function ($app) use ($socialite) {
                    $config = $app['config']['services.kakao'] ?? [
                        'client_id' => '',
                        'client_secret' => '',
                        'redirect' => '',
                    ];
                    return $socialite->buildProvider(
                        \App\Socialite\KakaoProvider::class,
                        $config
                    );
                });
            } catch (\Exception $e) {
                // Socialite가 설치되지 않은 경우 무시
            }
        }
    }
}







