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
     * Get existing DNS records for a zone
     */
    protected function getExistingDnsRecords(string $zoneId, string $type = 'A', string $name = ''): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get("{$this->apiUrl}/zones/{$zoneId}/dns_records", [
                'type' => $type,
                'name' => $name ?: $zoneId, // 빈 문자열인 경우 zone 이름 사용
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false && !empty($data['result'])) {
                    // 이름이 정확히 일치하는 레코드 찾기
                    foreach ($data['result'] as $record) {
                        $recordName = $record['name'] ?? '';
                        // 루트 도메인인 경우 도메인 이름과 일치하는지 확인
                        if ($name === '' && ($recordName === $zoneId || $recordName === '')) {
                            return $record;
                        }
                        // 서브도메인인 경우 정확히 일치하는지 확인
                        if ($name !== '' && $recordName === $name) {
                            return $record;
                        }
                    }
                }
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Exception while getting DNS records', [
                'zone_id' => $zoneId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Add or update DNS records for the domain
     */
    protected function addDnsRecords(string $zoneId, string $domain): void
    {
        $serverIp = config('app.server_ip');
        if (!$serverIp) {
            Log::warning('Server IP not configured, skipping DNS record creation');
            return;
        }

        // Zone 정보 가져오기 (도메인 이름 확인용)
        $zoneInfo = null;
        try {
            $zoneResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get("{$this->apiUrl}/zones/{$zoneId}");
            
            if ($zoneResponse->successful()) {
                $zoneData = $zoneResponse->json();
                if ($zoneData['success'] ?? false) {
                    $zoneInfo = $zoneData['result'];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get zone info', ['zone_id' => $zoneId]);
        }

        $records = [
            [
                'type' => 'A',
                'name' => '', // 빈 문자열로 보내면 Cloudflare가 루트 도메인으로 인식
                'content' => $serverIp,
                'proxied' => false, // 프록시 비활성화 (서버에 SSL이 없을 수 있으므로)
                'ttl' => 1, // Auto
            ],
            [
                'type' => 'A',
                'name' => 'www',
                'content' => $serverIp,
                'proxied' => false, // 프록시 비활성화
                'ttl' => 1, // Auto
            ],
        ];

        foreach ($records as $record) {
            try {
                // 기존 레코드 확인
                $recordName = $record['name'] ?: ($zoneInfo['name'] ?? $domain);
                $existingRecord = $this->getExistingDnsRecords($zoneId, $record['type'], $record['name']);
                
                if ($existingRecord) {
                    // 기존 레코드 업데이트
                    $recordId = $existingRecord['id'];
                    $updateData = [
                        'type' => $record['type'],
                        'name' => $record['name'],
                        'content' => $record['content'],
                        'proxied' => $record['proxied'],
                        'ttl' => $record['ttl'],
                    ];
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiToken,
                        'Content-Type' => 'application/json',
                    ])->put("{$this->apiUrl}/zones/{$zoneId}/dns_records/{$recordId}", $updateData);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        if ($data['success'] ?? false) {
                            Log::info('DNS record updated successfully', [
                                'zone_id' => $zoneId,
                                'record_id' => $recordId,
                                'record' => $record,
                            ]);
                        } else {
                            Log::warning('Failed to update DNS record', [
                                'zone_id' => $zoneId,
                                'record_id' => $recordId,
                                'record' => $record,
                                'response' => $data,
                            ]);
                        }
                    }
                } else {
                    // 새 레코드 추가
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
                            $errors = $data['errors'] ?? [];
                            $errorMessages = array_column($errors, 'message');
                            if (!empty($errorMessages)) {
                                Log::warning('Failed to add DNS record', [
                                    'zone_id' => $zoneId,
                                    'record' => $record,
                                    'errors' => $errorMessages,
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Exception while processing DNS record', [
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

