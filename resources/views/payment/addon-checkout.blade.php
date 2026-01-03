@extends('layouts.app')

@section('title', '추가 구매 결제 - ' . $site->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-cart-plus me-2"></i>추가 구매 결제
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <x-alert type="danger">{{ session('error') }}</x-alert>
                    @endif

                    <div class="mb-4">
                        <h5>주문 정보</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">사이트</th>
                                <td>{{ $userSite->name }}</td>
                            </tr>
                            <tr>
                                <th>상품</th>
                                <td>
                                    <span class="badge @if($addonProduct->type === 'storage') bg-primary @else bg-info @endif">
                                        @if($addonProduct->type === 'storage')
                                            <i class="bi bi-hdd me-1"></i>저장 용량
                                        @else
                                            <i class="bi bi-arrow-left-right me-1"></i>트래픽
                                        @endif
                                    </span>
                                    {{ $addonProduct->name }}
                                </td>
                            </tr>
                            <tr>
                                <th>용량</th>
                                <td>{{ number_format($addonProduct->amount_mb) }}MB ({{ number_format($addonProduct->amount_gb, 2) }}GB)</td>
                            </tr>
                            <tr>
                                <th>결제 금액</th>
                                <td>
                                    <strong class="text-primary">
                                        {{ number_format($addonProduct->price) }}원
                                        @if($addonProduct->billing_cycle === 'monthly')
                                            <span class="text-muted">/월</span>
                                        @else
                                            <span class="text-muted">(일회성)</span>
                                        @endif
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <th>주문번호</th>
                                <td>{{ $orderId }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>안내:</strong> 결제를 완료하면 즉시 추가 용량이 적용됩니다.
                    </div>

                    <div id="payment-widget-container" class="mb-3">
                        <!-- Toss Payments 위젯이 여기에 로드됩니다 -->
                        <div class="d-flex justify-content-center align-items-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading payment widget...</span>
                            </div>
                            <p class="ms-3 mb-0 text-muted">결제 위젯 로딩 중...</p>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" id="payment-button" class="btn btn-primary" disabled>
                            <i class="bi bi-arrow-clockwise me-1"></i>위젯 로딩 중...
                        </button>
                        <a href="{{ route('user-sites.addons', ['site' => $site->slug, 'userSite' => $userSite->slug]) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>취소
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.tosspayments.com/v2/standard"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const paymentButton = document.getElementById('payment-button');
    const paymentWidgetContainer = document.getElementById('payment-widget-container');

    // Toss Payments 클라이언트 키
    const clientKey = '{{ $site->getSetting("toss_payments_client_key", "test_gck_docs_Ovk5rk1EwkEbP0W43n07xlzm") }}';
    
    if (!clientKey || clientKey.trim() === '' || clientKey === 'null') {
        console.error('Toss Payments Client Key가 설정되지 않았습니다.');
        if (paymentWidgetContainer) {
            paymentWidgetContainer.innerHTML = 
                '<div class="alert alert-danger">' +
                '<h6>결제 위젯을 불러올 수 없습니다</h6>' +
                '<p>클라이언트 키가 설정되지 않았습니다. 마스터 콘솔에서 Toss Payments Client Key를 설정해주세요.</p>' +
                '</div>';
        }
        return;
    }
    
    console.log('Client Key:', clientKey.substring(0, 20) + '...');
    
    const userId = {{ auth()->id() }};
    const customerKey = 'customer_' + userId;
    const amount = {{ (int) $addonProduct->price }};
    const orderId = '{{ $orderId }}';
    const orderName = '{{ $addonProduct->name }}';

    console.log('Customer Key:', customerKey);
    console.log('Amount:', amount);
    console.log('Order ID:', orderId);

    try {
        // ------ Toss Payments SDK 초기화 (v2) ------
        const tossPayments = TossPayments(clientKey);
        
        // ------ 결제위젯 인스턴스 생성 ------
        const widgets = tossPayments.widgets({
            customerKey: customerKey,
        });
        
        // ------ 주문의 결제 금액 설정 ------
        await widgets.setAmount({
            currency: "KRW",
            value: amount,
        });
        
        // ------ 결제 UI 렌더링 ------
        await widgets.renderPaymentMethods({
            selector: "#payment-widget-container",
            variantKey: "DEFAULT",
        });
        
        console.log('Payment widget rendered successfully');
        
        // 위젯이 준비되면 결제 버튼 활성화
        if (paymentButton) {
            paymentButton.disabled = false;
            paymentButton.innerHTML = '<i class="bi bi-check-circle me-1"></i>결제하기';
        }
        
        // ------ 결제 요청 함수 ------
        async function requestPayment() {
            if (paymentButton) {
                paymentButton.disabled = true;
                paymentButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 결제 요청 중...';
            }
            try {
                await widgets.requestPayment({
                    orderId: orderId,
                    orderName: orderName,
                    successUrl: window.location.origin + '{{ route("payment.success", [], false) }}?orderId=' + orderId + '&amount=' + amount,
                    failUrl: window.location.origin + '{{ route("payment.fail", [], false) }}?orderId=' + orderId,
                    customerEmail: '{{ auth()->user()->email }}',
                    customerName: '{{ auth()->user()->name }}',
                });
            } catch (error) {
                console.error('Payment request failed:', error);
                alert('결제 요청에 실패했습니다: ' + (error.message || '알 수 없는 오류가 발생했습니다.'));
                if (paymentButton) {
                    paymentButton.disabled = false;
                    paymentButton.innerHTML = '<i class="bi bi-check-circle me-1"></i>결제하기';
                }
            }
        }
        
        // 결제 버튼에 이벤트 연결
        if (paymentButton) {
            paymentButton.onclick = requestPayment;
        }
    } catch (error) {
        console.error('Payment widget initialization failed:', error);
        if (paymentWidgetContainer) {
            paymentWidgetContainer.innerHTML = 
                '<div class="alert alert-danger">' +
                '<h6>결제 위젯을 불러올 수 없습니다</h6>' +
                '<p>오류: ' + (error.message || '알 수 없는 오류') + '</p>' +
                '<p class="small">클라이언트 키를 확인하거나 관리자에게 문의해주세요.</p>' +
                '</div>';
        }
        if (paymentButton) {
            paymentButton.disabled = true;
            paymentButton.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>오류 발생';
        }
    }
});
</script>
@endpush
@endsection





