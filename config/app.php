<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'SEOom Builder'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Master Domain
    |--------------------------------------------------------------------------
    |
    | The master domain for the SEOom Builder platform.
    | This is used for subdomain routing and custom domain detection.
    |
    */

    'master_domain' => env('MASTER_DOMAIN', 'seoom.com'),
    
    /*
    |--------------------------------------------------------------------------
    | Server IP Address
    |--------------------------------------------------------------------------
    |
    | This is the public IP address of the server for A record DNS configuration.
    | This can be found in AWS EC2 console or by contacting server administrator.
    |
    */
    
    'server_ip' => env('SERVER_IP', null),
    
    'force_https' => env('FORCE_HTTPS', false),
    
    /*
    |--------------------------------------------------------------------------
    | ALB DNS Name
    |--------------------------------------------------------------------------
    |
    | This is the DNS name of the Application Load Balancer for CNAME record
    | configuration. When set, domains will use CNAME records pointing to the
    | ALB instead of A records pointing to a server IP.
    |
    */
    
    'alb_dns' => env('ALB_DNS', null),
    
    'base_domain' => env('BASE_DOMAIN', 'seoomweb.com'),
    
    /*
    |--------------------------------------------------------------------------
    | Nameservers
    |--------------------------------------------------------------------------
    |
    | Nameservers for custom domain configuration.
    | Users can change their domain's nameservers to these values.
    |
    */
    
    /*
    |--------------------------------------------------------------------------
    | Nameservers
    |--------------------------------------------------------------------------
    |
    | Nameservers for custom domain configuration.
    | Cloudflare를 사용하는 경우, 각 도메인마다 고유한 네임서버가 할당됩니다.
    | 사용자가 Cloudflare에 도메인을 추가한 후 해당 네임서버를 확인하여
    | 도메인 제공업체에서 변경해야 합니다.
    |
    | 일반적인 Cloudflare 네임서버 예시:
    | - alice.ns.cloudflare.com
    | - bob.ns.cloudflare.com
    |
    | .env 파일에서 설정하거나, Cloudflare API를 통해 동적으로 가져올 수 있습니다.
    |
    */
    
    'nameservers' => [
        env('NAMESERVER_1', 'ns1.cloudflare.com'),
        env('NAMESERVER_2', 'ns2.cloudflare.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    */

    'timezone' => env('APP_TIMEZONE', 'Asia/Seoul'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    */

    'locale' => env('APP_LOCALE', 'ko'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'ko_KR'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

];








