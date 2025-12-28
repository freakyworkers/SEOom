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
                
                // SSL/TLS 모드를 "가변"으로 설정
                $this->setSslMode($zoneId, 'flexible');
                
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
                    
                    // SSL/TLS 모드를 "가변"으로 설정
                    $this->setSslMode($zoneId, 'flexible');
                    
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
     * Get all existing DNS records for a zone by name (all types)
     */
    protected function getExistingDnsRecordsAll(string $zoneId, string $name = ''): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get("{$this->apiUrl}/zones/{$zoneId}/dns_records");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false && !empty($data['result'])) {
                    $matchingRecords = [];
                    
                    // Zone 정보 가져오기
                    $zoneResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiToken,
                    ])->get("{$this->apiUrl}/zones/{$zoneId}");
                    
                    $zoneName = '';
                    if ($zoneResponse->successful()) {
                        $zoneData = $zoneResponse->json();
                        $zoneName = $zoneData['result']['name'] ?? '';
                    }
                    
                    foreach ($data['result'] as $record) {
                        $recordName = $record['name'] ?? '';
                        
                        // 루트 도메인 검색 (name이 '', '@', 또는 zone 이름과 동일)
                        if (($name === '' || $name === '@') && $recordName === $zoneName) {
                            $matchingRecords[] = $record;
                        }
                        // 와일드카드 검색 (*.example.com)
                        elseif ($name === '*') {
                            $wildcardName = '*.' . $zoneName;
                            if ($recordName === '*' || $recordName === $wildcardName) {
                                $matchingRecords[] = $record;
                            }
                        }
                        // 서브도메인 검색 (www.example.com 또는 www)
                        elseif ($name !== '' && $name !== '@') {
                            $fullSubdomain = $name . '.' . $zoneName;
                            if ($recordName === $name || $recordName === $fullSubdomain) {
                                $matchingRecords[] = $record;
                            }
                        }
                    }
                    
                    return $matchingRecords;
                }
            }
            return [];
        } catch (\Exception $e) {
            Log::error('Exception while getting all DNS records', [
                'zone_id' => $zoneId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Add or update DNS records for the domain
     */
    protected function addDnsRecords(string $zoneId, string $domain): void
    {
        $albDns = config('app.alb_dns');
        $serverIp = config('app.server_ip');
        
        // ALB DNS가 설정되어 있으면 CNAME 레코드 사용, 그렇지 않으면 A 레코드 사용
        $useAlb = !empty($albDns);
        
        if (!$useAlb && !$serverIp) {
            Log::warning('Neither ALB DNS nor Server IP configured, skipping DNS record creation');
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

        if ($useAlb) {
            // ALB DNS를 사용하는 CNAME 레코드 (Cloudflare CNAME Flattening 활용)
            $records = [
                [
                    'type' => 'CNAME',
                    'name' => '@', // 루트 도메인 (Cloudflare가 CNAME Flattening 처리)
                    'content' => $albDns,
                    'proxied' => true, // Cloudflare 프록시 활성화 (SSL, CDN, 보안)
                    'ttl' => 1, // Auto
                ],
                [
                    'type' => 'CNAME',
                    'name' => 'www',
                    'content' => $albDns,
                    'proxied' => true, // Cloudflare 프록시 활성화
                    'ttl' => 1, // Auto
                ],
                [
                    'type' => 'CNAME',
                    'name' => '*', // 와일드카드 (모든 서브도메인)
                    'content' => $albDns,
                    'proxied' => true, // Cloudflare 프록시 활성화
                    'ttl' => 1, // Auto
                ],
            ];
            Log::info('Using ALB DNS for domain', ['domain' => $domain, 'alb_dns' => $albDns]);
        } else {
            // 기존 A 레코드 방식
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
                [
                    'type' => 'A',
                    'name' => '*', // 와일드카드 (모든 서브도메인)
                    'content' => $serverIp,
                    'proxied' => false, // 프록시 비활성화
                    'ttl' => 1, // Auto
                ],
            ];
        }

        foreach ($records as $record) {
            try {
                // 기존 레코드 확인 (같은 이름의 A, AAAA, CNAME 레코드 모두 검색)
                $recordName = $record['name'] ?: ($zoneInfo['name'] ?? $domain);
                $existingRecords = $this->getExistingDnsRecordsAll($zoneId, $record['name']);
                
                // 기존 A, AAAA, CNAME 레코드 삭제 (타입이 다를 경우 충돌 방지)
                foreach ($existingRecords as $existingRecord) {
                    $existingType = $existingRecord['type'] ?? '';
                    if (in_array($existingType, ['A', 'AAAA', 'CNAME'])) {
                        $deleteResponse = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $this->apiToken,
                        ])->delete("{$this->apiUrl}/zones/{$zoneId}/dns_records/{$existingRecord['id']}");
                        
                        if ($deleteResponse->successful()) {
                            Log::info('Existing DNS record deleted before creating new one', [
                                'zone_id' => $zoneId,
                                'deleted_record' => $existingRecord,
                            ]);
                        }
                    }
                }
                
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
                } else {
                    Log::error('Failed to add DNS record - HTTP error', [
                        'zone_id' => $zoneId,
                        'record' => $record,
                        'status' => $response->status(),
                        'response' => $response->json(),
                    ]);
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

    /**
     * Set SSL/TLS mode for a zone
     * 
     * @param string $zoneId
     * @param string $mode 'off', 'flexible', 'full', 'strict'
     * @return bool
     */
    protected function setSslMode(string $zoneId, string $mode = 'flexible'): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->patch("{$this->apiUrl}/zones/{$zoneId}/settings/ssl", [
                'value' => $mode,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] ?? false) {
                    Log::info('SSL/TLS mode set successfully', [
                        'zone_id' => $zoneId,
                        'mode' => $mode,
                    ]);
                    return true;
                } else {
                    Log::warning('Failed to set SSL/TLS mode', [
                        'zone_id' => $zoneId,
                        'mode' => $mode,
                        'response' => $data,
                    ]);
                }
            } else {
                Log::error('Failed to set SSL/TLS mode', [
                    'zone_id' => $zoneId,
                    'mode' => $mode,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while setting SSL/TLS mode', [
                'zone_id' => $zoneId,
                'mode' => $mode,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

