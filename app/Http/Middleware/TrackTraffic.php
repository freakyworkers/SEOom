<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SiteUsageService;

class TrackTraffic
{
    protected $usageService;

    public function __construct(SiteUsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Site가 route parameter로 있는 경우에만 추적
        if ($request->route('site')) {
            $site = $request->route('site');
            
            // 관리자 페이지나 API는 제외 (또는 선택적으로 포함)
            $path = $request->path();
            $excludePaths = ['admin', 'api'];
            
            $shouldTrack = true;
            foreach ($excludePaths as $excludePath) {
                if (str_starts_with($path, $excludePath)) {
                    $shouldTrack = false;
                    break;
                }
            }

            if ($shouldTrack && $site && !$site->is_master_site) {
                try {
                    // Get response size
                    $responseSize = $this->getResponseSize($response);
                    
                    // Add request size (approximate)
                    $requestSize = strlen($request->getContent());
                    
                    // Total traffic (request + response)
                    $totalBytes = $responseSize + $requestSize;
                    
                    // Update traffic usage
                    $this->usageService->addTraffic($site->id, $totalBytes);
                } catch (\Exception $e) {
                    // Traffic tracking failure should not break the request
                    \Log::warning('Traffic tracking failed: ' . $e->getMessage());
                }
            }
        }

        return $response;
    }

    /**
     * Get response size in bytes
     */
    protected function getResponseSize(Response $response): int
    {
        // Get content length from headers if available
        $contentLength = $response->headers->get('Content-Length');
        if ($contentLength !== null) {
            return (int) $contentLength;
        }

        // Calculate from content
        $content = $response->getContent();
        if ($content !== false) {
            return strlen($content);
        }

        return 0;
    }
}

