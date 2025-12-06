<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TossPaymentService
{
    private $secretKey;
    private $baseUrl;

    public function __construct()
    {
        // Get Toss Payments credentials from master settings
        $masterSite = \App\Models\Site::getMasterSite();
        if ($masterSite) {
            $this->secretKey = $masterSite->getSetting('toss_payments_secret_key', '');
            $this->baseUrl = $masterSite->getSetting('toss_payments_base_url', 'https://api.tosspayments.com');
        } else {
            $this->secretKey = config('services.toss.secret_key', '');
            $this->baseUrl = config('services.toss.base_url', 'https://api.tosspayments.com');
        }
    }

    /**
     * Create a payment request for subscription.
     */
    public function createPayment(Subscription $subscription, string $orderId, int $amount, string $customerKey): array
    {
        $http = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
            'Content-Type' => 'application/json',
        ]);

        // 개발 환경에서 SSL 검증 비활성화
        if (app()->environment('local', 'development')) {
            $http = $http->withoutVerifying();
        }

        $response = $http->post($this->baseUrl . '/v1/payments', [
            'amount' => $amount,
            'orderId' => $orderId,
            'customerKey' => $customerKey,
            'successUrl' => route('payment.success'),
            'failUrl' => route('payment.fail'),
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Toss Payment creation failed', [
            'subscription_id' => $subscription->id,
            'order_id' => $orderId,
            'response' => $response->body(),
        ]);

        throw new \Exception('결제 생성에 실패했습니다: ' . $response->body());
    }

    /**
     * Create a billing key for recurring payments.
     * Toss Payments API: POST /v1/billing/authorizations/issue
     */
    public function createBillingKey(string $customerKey, string $authKey): array
    {
        $http = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
            'Content-Type' => 'application/json',
        ]);

        // 개발 환경에서 SSL 검증 비활성화
        if (app()->environment('local', 'development')) {
            $http = $http->withoutVerifying();
        }

        $response = $http->post($this->baseUrl . '/v1/billing/authorizations/issue', [
            'customerKey' => $customerKey,
            'authKey' => $authKey,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Toss Billing Key creation failed', [
            'customer_key' => $customerKey,
            'auth_key' => substr($authKey, 0, 20) . '...',
            'status' => $response->status(),
            'response' => $response->body(),
        ]);

        throw new \Exception('빌링 키 생성에 실패했습니다: ' . $response->body());
    }

    /**
     * Process recurring payment using billing key.
     */
    public function processRecurringPayment(Subscription $subscription, string $billingKey, int $amount, string $orderId): array
    {
        $http = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
            'Content-Type' => 'application/json',
        ]);

        // 개발 환경에서 SSL 검증 비활성화
        if (app()->environment('local', 'development')) {
            $http = $http->withoutVerifying();
        }

        // customerKey는 빌링키 발급 시 사용한 것과 동일해야 함
        $customerKey = 'customer_' . $subscription->user_id;

        $response = $http->post($this->baseUrl . '/v1/billing/' . $billingKey, [
            'customerKey' => $customerKey,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderName' => $subscription->plan->name . ' 구독료',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Toss Recurring Payment failed', [
            'subscription_id' => $subscription->id,
            'billing_key' => $billingKey,
            'order_id' => $orderId,
            'response' => $response->body(),
        ]);

        throw new \Exception('정기결제 처리에 실패했습니다: ' . $response->body());
    }

    /**
     * Confirm payment.
     */
    public function confirmPayment(string $paymentKey, string $orderId, int $amount): array
    {
        $http = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
            'Content-Type' => 'application/json',
        ]);

        // 개발 환경에서 SSL 검증 비활성화
        if (app()->environment('local', 'development')) {
            $http = $http->withoutVerifying();
        }

        $response = $http->post($this->baseUrl . '/v1/payments/confirm', [
            'paymentKey' => $paymentKey,
            'orderId' => $orderId,
            'amount' => $amount,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Toss Payment confirmation failed', [
            'payment_key' => $paymentKey,
            'order_id' => $orderId,
            'response' => $response->body(),
        ]);

        throw new \Exception('결제 확인에 실패했습니다: ' . $response->body());
    }

    /**
     * Get payment details.
     */
    public function getPaymentDetails(string $paymentKey): array
    {
        $http = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
        ]);

        // 개발 환경에서 SSL 검증 비활성화
        if (app()->environment('local', 'development')) {
            $http = $http->withoutVerifying();
        }

        $response = $http->get($this->baseUrl . '/v1/payments/' . $paymentKey);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Toss Payment details fetch failed', [
            'payment_key' => $paymentKey,
            'response' => $response->body(),
        ]);

        throw new \Exception('결제 정보 조회에 실패했습니다: ' . $response->body());
    }

    /**
     * Cancel payment.
     */
    public function cancelPayment(string $paymentKey, string $cancelReason, int $cancelAmount = null): array
    {
        $data = [
            'cancelReason' => $cancelReason,
        ];

        if ($cancelAmount !== null) {
            $data['cancelAmount'] = $cancelAmount;
        }

        $http = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
            'Content-Type' => 'application/json',
        ]);

        // 개발 환경에서 SSL 검증 비활성화
        if (app()->environment('local', 'development')) {
            $http = $http->withoutVerifying();
        }

        $response = $http->post($this->baseUrl . '/v1/payments/' . $paymentKey . '/cancel', $data);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Toss Payment cancellation failed', [
            'payment_key' => $paymentKey,
            'response' => $response->body(),
        ]);

        throw new \Exception('결제 취소에 실패했습니다: ' . $response->body());
    }
}

