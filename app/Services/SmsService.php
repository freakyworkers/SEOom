<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $site;
    protected $provider;
    protected $config;

    public function __construct(Site $site)
    {
        $this->site = $site;
        $this->provider = $site->getSetting('sms_provider', 'cool_sms');
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        switch ($this->provider) {
            case 'naver_cloud':
                $this->config = [
                    'api_key' => $this->site->getSetting('sms_naver_api_key', ''),
                    'api_secret' => $this->site->getSetting('sms_naver_api_secret', ''),
                    'service_id' => $this->site->getSetting('sms_naver_service_id', ''),
                    'caller_id' => $this->site->getSetting('sms_naver_caller_id', ''),
                ];
                break;
            case 'twilio':
                $this->config = [
                    'sid' => $this->site->getSetting('sms_twilio_sid', ''),
                    'auth_token' => $this->site->getSetting('sms_twilio_auth_token', ''),
                    'from' => $this->site->getSetting('sms_twilio_from', ''),
                ];
                break;
            case 'solapi':
                $this->config = [
                    'api_key' => $this->site->getSetting('sms_solapi_api_key', ''),
                    'api_secret' => $this->site->getSetting('sms_solapi_api_secret', ''),
                    'from' => $this->site->getSetting('sms_solapi_from', ''),
                ];
                break;
            case 'cool_sms':
            default:
                $this->config = [
                    'api_key' => $this->site->getSetting('sms_cool_api_key', ''),
                    'api_secret' => $this->site->getSetting('sms_cool_api_secret', ''),
                    'from' => $this->site->getSetting('sms_cool_from', ''),
                ];
                break;
        }
    }

    /**
     * Set provider and config dynamically (for testing)
     */
    public function setConfig(string $provider, array $config)
    {
        $this->provider = $provider;
        $this->config = $config;
    }

    /**
     * Send SMS with verification code
     */
    public function sendVerificationCode(string $phone, string $code, ?string $senderName = null): array
    {
        $senderName = $senderName ?? $this->site->getSetting('sms_sender_name', $this->site->name ?? '');
        $message = $this->buildMessage($code, $senderName);

        switch ($this->provider) {
            case 'naver_cloud':
                return $this->sendViaNaverCloud($phone, $message);
            case 'twilio':
                return $this->sendViaTwilio($phone, $message);
            case 'solapi':
                return $this->sendViaSolapi($phone, $message);
            case 'cool_sms':
            default:
                // Cool SMS는 SOLAPI를 백엔드로 사용
                return $this->sendViaSolapi($phone, $message);
        }
    }

    protected function buildMessage(string $code, ?string $senderName = null): string
    {
        $title = !empty($senderName) ? $senderName : '회원가입';
        return $title . " 회원가입 인증번호안내\n인증번호: " . $code;
    }

    /**
     * Send via Naver Cloud Platform
     */
    protected function sendViaNaverCloud(string $phone, string $message): array
    {
        try {
            $timestamp = (string) round(microtime(true) * 1000);
            $signature = $this->generateNaverSignature($timestamp);

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                    'x-ncp-apigw-timestamp' => $timestamp,
                    'x-ncp-iam-access-key' => $this->config['api_key'],
                    'x-ncp-apigw-signature-v2' => $signature,
                ])->post("https://sens.apigw.ntruss.com/sms/v2/services/{$this->config['service_id']}/messages", [
                    'type' => 'SMS',
                    'contentType' => 'COMM',
                    'countryCode' => '82',
                    'from' => $this->config['caller_id'],
                    'content' => $message,
                    'messages' => [
                        [
                            'to' => str_replace('-', '', $phone),
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => '인증번호가 발송되었습니다.'];
            }

            Log::error('Naver Cloud SMS 발송 실패', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            return ['success' => false, 'message' => 'SMS 발송에 실패했습니다.'];
        } catch (\Exception $e) {
            Log::error('Naver Cloud SMS 발송 오류: ' . $e->getMessage());
            return ['success' => false, 'message' => 'SMS 발송 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    protected function generateNaverSignature(string $timestamp): string
    {
        $method = 'POST';
        $uri = "/sms/v2/services/{$this->config['service_id']}/messages";
        $message = $method . ' ' . $uri . "\n" . $timestamp . "\n" . $this->config['api_key'];
        return base64_encode(hash_hmac('sha256', $message, $this->config['api_secret'], true));
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio(string $phone, string $message): array
    {
        try {
            $phone = $this->formatPhoneForTwilio($phone);
            
            $response = Http::withoutVerifying()
                ->withBasicAuth($this->config['sid'], $this->config['auth_token'])
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->config['sid']}/Messages.json", [
                    'From' => $this->config['from'],
                    'To' => $phone,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => '인증번호가 발송되었습니다.'];
            }

            Log::error('Twilio SMS 발송 실패', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            return ['success' => false, 'message' => 'SMS 발송에 실패했습니다.'];
        } catch (\Exception $e) {
            Log::error('Twilio SMS 발송 오류: ' . $e->getMessage());
            return ['success' => false, 'message' => 'SMS 발송 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    protected function formatPhoneForTwilio(string $phone): string
    {
        $phone = str_replace(['-', ' ', '(', ')'], '', $phone);
        if (strpos($phone, '0') === 0) {
            $phone = '82' . substr($phone, 1);
        }
        return '+' . $phone;
    }

    /**
     * Send via Cool SMS (SOLAPI 기반)
     */
    protected function sendViaCoolSms(string $phone, string $message): array
    {
        // Cool SMS는 SOLAPI를 백엔드로 사용하므로 sendViaSolapi 호출
        return $this->sendViaSolapi($phone, $message);
    }

    /**
     * Send via SOLAPI
     */
    protected function sendViaSolapi(string $phone, string $message): array
    {
        try {
            // SOLAPI는 전화번호에서 하이픈 제거 (01012345678 형식)
            $phone = str_replace('-', '', $phone);
            $from = str_replace('-', '', $this->config['from']);

            // SOLAPI REST API 엔드포인트
            $url = 'https://api.solapi.com/messages/v4/send';
            
            // SOLAPI는 message (단수) 형식 사용
            $requestBody = [
                'message' => [
                    'to' => $phone,
                    'from' => $from,
                    'text' => $message,
                ],
            ];
            
            // SOLAPI HMAC-SHA256 인증 생성
            // ISO 8601 형식의 날짜 (예: 2023-12-01T12:00:00Z)
            $date = gmdate('Y-m-d\TH:i:s\Z');
            $timestamp = (string) (time() * 1000);
            $salt = uniqid();
            $signature = hash_hmac('sha256', $date . $salt, $this->config['api_secret']);
            
            Log::info('SOLAPI SMS 발송 시도', [
                'url' => $url,
                'from' => $from,
                'to' => $phone,
                'api_key_prefix' => substr($this->config['api_key'], 0, 10) . '...',
                'date' => $date,
            ]);
            
            // SOLAPI 인증: HMAC-SHA256 사용
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'HMAC-SHA256 ApiKey=' . $this->config['api_key'] . ', Date=' . $date . ', Salt=' . $salt . ', Signature=' . $signature,
                    'Content-Type' => 'application/json; charset=utf-8',
                ])->post($url, $requestBody);

            $responseBody = $response->body();
            $responseJson = $response->json();
            
            Log::info('SOLAPI SMS 응답', [
                'status' => $response->status(),
                'body' => $responseBody,
            ]);

            if ($response->successful()) {
                $result = $responseJson;
                // SOLAPI 성공 응답 확인
                if (isset($result['statusCode']) && ($result['statusCode'] === '2000' || $result['statusCode'] === 2000)) {
                    return ['success' => true, 'message' => '인증번호가 발송되었습니다.'];
                }
                if (isset($result['success']) && $result['success'] === true) {
                    return ['success' => true, 'message' => '인증번호가 발송되었습니다.'];
                }
                // groupId가 있으면 성공으로 간주 (SOLAPI 응답 형식)
                if (isset($result['groupId'])) {
                    return ['success' => true, 'message' => '인증번호가 발송되었습니다.'];
                }
                // 에러가 없는 경우도 성공으로 간주
                if (!isset($result['errorCode']) && !isset($result['error']) && !isset($result['errorMessage'])) {
                    return ['success' => true, 'message' => '인증번호가 발송되었습니다.'];
                }
            }

            Log::error('SOLAPI SMS 발송 실패', [
                'response' => $responseBody,
                'status' => $response->status(),
                'result' => $responseJson,
                'request_body' => $requestBody,
            ]);

            $errorMessage = 'SMS 발송에 실패했습니다.';
            $result = $responseJson;
            if (isset($result['errorMessage'])) {
                $errorMessage .= ' ' . $result['errorMessage'];
            } elseif (isset($result['error'])) {
                $errorMessage .= ' ' . $result['error'];
            } elseif (isset($result['errorCode'])) {
                $errorMessage .= ' 오류 코드: ' . $result['errorCode'];
            } else {
                $errorMessage .= ' 응답: ' . $responseBody;
            }

            return ['success' => false, 'message' => $errorMessage];
        } catch (\Exception $e) {
            Log::error('SOLAPI SMS 발송 오류: ' . $e->getMessage());
            return ['success' => false, 'message' => 'SMS 발송 중 오류가 발생했습니다: ' . $e->getMessage()];
        }
    }

    /**
     * Test SMS sending
     */
    public function testSms(string $phone): array
    {
        $testCode = '123456';
        $senderName = $this->site->getSetting('sms_sender_name', $this->site->name ?? '');
        return $this->sendVerificationCode($phone, $testCode, $senderName ?: null);
    }
}

