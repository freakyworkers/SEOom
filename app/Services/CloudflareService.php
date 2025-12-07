<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CloudflareService
{
    protected $apiToken;
    protected $apiUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token');
    }

    /**
     * Check if Cloudflare is enabled
     */
    public function isEnabled(): bool
    {
        return !empty($this->apiToken) && config('services.cloudflare.enabled', false);
    }

    /**
     * Add a domain to Cloudflare
     * 
     * @param string $domain
     * @return array|null Returns array with zone_id and nameservers, or null on failure
     */
    public function addDomain(string $domain): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('Cloudflare is not enabled or API token is missing');
            return null;
        }

        try {
            // Check if domain already exists
            $existingZone = $this->getZoneByDomain($domain);
            if ($existingZone) {
                $zoneId = $existingZone['id'];
                $nameservers = $this->getNameservers($zoneId);
                
                // 기존 Zone에도 DNS 레코드 추가 (없는 경우)
                $this->addDnsRecords($zoneId, $domain);
                
                return [
                    'zone_id' => $zoneId,
                    'nameservers' => $nameservers,
                    'status' => 'existing',
                ];
            }

            // Add domain to Cloudflare
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/zones", [
                'name' => $domain,
                'account' => [
                    'id' => config('services.cloudflare.account_id'),
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false) {
                    $zoneId = $data['result']['id'] ?? null;
                    $nameservers = $this->getNameservers($zoneId);
                    
                    // Add DNS records
                    $this->addDnsRecords($zoneId, $domain);
                    
                    return [
                        'zone_id' => $zoneId,
                        'nameservers' => $nameservers,
                        'status' => 'created',
                    ];
                }
            }

            Log::error('Failed to add domain to Cloudflare', [
                'domain' => $domain,
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception while adding domain to Cloudflare', [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get zone by domain name
     */
    protected function getZoneByDomain(string $domain): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get("{$this->apiUrl}/zones", [
                'name' => $domain,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false && !empty($data['result'])) {
                    return $data['result'][0] ?? null;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Exception while getting zone from Cloudflare', [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get nameservers for a zone
     */
    protected function getNameservers(string $zoneId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get("{$this->apiUrl}/zones/{$zoneId}");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false) {
                    $nameservers = $data['result']['name_servers'] ?? [];
                    
                    // 디버깅: 네임서버 정보 로깅
                    if (empty($nameservers)) {
                        Log::warning('Cloudflare zone has no nameservers', [
                            'zone_id' => $zoneId,
                            'response_data' => $data['result'] ?? null,
                        ]);
                    } else {
                        Log::info('Cloudflare nameservers retrieved', [
                            'zone_id' => $zoneId,
                            'nameservers' => $nameservers,
                        ]);
                    }
                    
                    return $nameservers;
                }
            }

            Log::error('Failed to get nameservers from Cloudflare', [
                'zone_id' => $zoneId,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Exception while getting nameservers from Cloudflare', [
                'zone_id' => $zoneId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Add DNS records for the domain
     */
    protected function addDnsRecords(string $zoneId, string $domain): void
    {
        $serverIp = config('app.server_ip');
        if (!$serverIp) {
            Log::warning('Server IP not configured, skipping DNS record creation');
            return;
        }

        $records = [
            [
                'type' => 'A',
                'name' => '', // 빈 문자열로 보내면 Cloudflare가 루트 도메인으로 인식
                'content' => $serverIp,
                'proxied' => true,
                'ttl' => 1, // Auto
            ],
            [
                'type' => 'A',
                'name' => 'www',
                'content' => $serverIp,
                'proxied' => true,
                'ttl' => 1, // Auto
            ],
        ];

        foreach ($records as $record) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type' => 'application/json',
                ])->post("{$this->apiUrl}/zones/{$zoneId}/dns_records", $record);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success'] ?? false) {
                        Log::info('DNS record added successfully', [
                            'zone_id' => $zoneId,
                            'record' => $record,
                        ]);
                    } else {
                        // 레코드가 이미 존재하는 경우 등 에러 처리
                        $errors = $data['errors'] ?? [];
                        $errorMessages = array_column($errors, 'message');
                        if (!empty($errorMessages) && !str_contains(implode(' ', $errorMessages), 'already exists')) {
                            Log::warning('Failed to add DNS record', [
                                'zone_id' => $zoneId,
                                'record' => $record,
                                'errors' => $errorMessages,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Exception while adding DNS record', [
                    'zone_id' => $zoneId,
                    'record' => $record,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get nameservers for a domain (cached)
     */
    public function getNameserversForDomain(string $domain): array
    {
        $cacheKey = "cloudflare_nameservers_{$domain}";
        
        return Cache::remember($cacheKey, 3600, function () use ($domain) {
            $zone = $this->getZoneByDomain($domain);
            if ($zone) {
                return $this->getNameservers($zone['id']);
            }
            return [];
        });
    }
}

