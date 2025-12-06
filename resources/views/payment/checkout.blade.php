@extends('layouts.app')

@section('title', '결제하기 - ' . $site->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>결제하기
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
                                <th width="30%">플랜</th>
                                <td>{{ $plan->name }}</td>
                            </tr>
                            <tr>
                                <th>금액</th>
                                <td>
                                    <strong class="text-primary">
                                        @if($plan->billing_type === 'free')
                                            무료
                                        @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                            {{ number_format($plan->one_time_price) }}원 <small class="text-muted" style="font-size: 0.4em;">(1회 결제)</small>
                                        @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                            {{ number_format($plan->price) }}원/월
                                        @else
                                            무료
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
                        <strong>안내:</strong> 결제를 완료한 후에만 사이트를 생성할 수 있습니다.
                    </div>

                    <div id="payment-widget-container" class="mb-3">
                        <div id="payment-widget-loading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">로딩 중...</span>
                            </div>
                            <p class="mt-2 text-muted">결제 위젯을 불러오는 중입니다...</p>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('user-sites.select-plan', ['site' => $site->slug]) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>취소
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Toss Payments v2 SDK -->
<script src="https://js.tosspayments.com/v2/standard"></script>
<script>
async function initializePaymentWidget() {
    // Toss Payments 클라이언트 키 (마스터 사이트 설정에서 가져와야 함)
    // 주의: v2 SDK는 "결제위젯 연동 키"를 사용해야 합니다 (gck로 시작)
    // "API 개별 연동 키" (test_ck로 시작)는 지원하지 않습니다
    const clientKey = '{{ $site->getSetting("toss_payments_client_key", "test_gck_docs_Ovk5rk1EwkEbP0W43n07xlzm") }}';
    
    // 클라이언트 키 유효성 검사
    if (!clientKey || clientKey.trim() === '' || clientKey === 'null') {
        console.error('Toss Payments Client Key가 설정되지 않았습니다.');
        const container = document.getElementById('payment-widget-container');
        const loadingMessage = document.getElementById('payment-widget-loading');
        
        if (loadingMessage) {
            loadingMessage.remove();
        }
        
        if (container) {
            container.innerHTML = 
                '<div class="alert alert-danger">' +
                '<h6><i class="bi bi-exclamation-triangle me-2"></i>결제 위젯을 불러올 수 없습니다</h6>' +
                '<p>클라이언트 키가 설정되지 않았습니다. 마스터 콘솔에서 Toss Payments Client Key를 설정해주세요.</p>' +
                '</div>';
        }
        
        // 결제 버튼 비활성화
        const paymentButton = document.querySelector('#payment-button');
        if (paymentButton) {
            paymentButton.disabled = true;
            paymentButton.innerHTML = '<i class="bi bi-x-circle me-1"></i>결제 불가';
        }
        
        return;
    }
    
    console.log('Client Key:', clientKey.substring(0, 20) + '...');
    
    // customerKey는 영문 대소문자, 숫자, 특수문자(-,_,=,.,@)로 2-50자
    const userId = {{ auth()->id() }};
    const customerKey = 'customer_' + userId;
    // Toss Payments는 원 단위를 그대로 사용합니다 (29,000원 = 29000)
    @php
        $amount = 0;
        if ($plan->billing_type === 'one_time' && $plan->one_time_price > 0) {
            $amount = $plan->one_time_price;
        } elseif ($plan->billing_type === 'monthly' && $plan->price > 0) {
            $amount = $plan->price;
        }
    @endphp
    const amount = {{ $amount }};
    const orderId = '{{ $orderId }}';
    const orderName = '{{ $plan->name }} 구독';

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
        
        // 로딩 메시지 제거
        const loadingMessage = document.getElementById('payment-widget-loading');
        if (loadingMessage) {
            loadingMessage.remove();
        }
        
        // 위젯이 준비되면 결제 버튼 활성화
        const paymentButton = document.querySelector('#payment-button');
        if (paymentButton) {
            paymentButton.disabled = false;
            paymentButton.innerHTML = '<i class="bi bi-check-circle me-1"></i>결제하기';
        }
        
        // ------ 결제 요청 함수 ------
        async function requestPayment() {
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
            }
        }
        
        // 결제 버튼에 이벤트 연결
        if (paymentButton) {
            paymentButton.onclick = requestPayment;
        }
        
    } catch (error) {
        console.error('Payment widget initialization failed:', error);
        const container = document.getElementById('payment-widget-container');
        const loadingMessage = document.getElementById('payment-widget-loading');
        
        if (loadingMessage) {
            loadingMessage.remove();
        }
        
        if (container) {
            container.innerHTML = 
                '<div class="alert alert-danger">' +
                '<h6><i class="bi bi-exclamation-triangle me-2"></i>결제 위젯을 불러올 수 없습니다</h6>' +
                '<p>오류: ' + (error.message || '알 수 없는 오류') + '</p>' +
                '<p class="small">클라이언트 키를 확인하거나 관리자에게 문의해주세요.</p>' +
                '<button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="location.reload()">' +
                '<i class="bi bi-arrow-clockwise me-1"></i>새로고침</button>' +
                '</div>';
        }
        
        // 에러가 있어도 결제 버튼은 활성화 (직접 결제 시도 가능)
        const paymentButton = document.querySelector('#payment-button');
        if (paymentButton) {
            paymentButton.disabled = false;
            paymentButton.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>결제 시도하기';
        }
    }
}

// DOM이 로드된 후 실행
document.addEventListener('DOMContentLoaded', function() {
    // 결제 버튼 추가 (초기에는 비활성화)
    const paymentButton = document.createElement('button');
    paymentButton.type = 'button';
    paymentButton.id = 'payment-button';
    paymentButton.className = 'btn btn-primary';
    paymentButton.disabled = true;
    paymentButton.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>위젯 로딩 중...';
    
    const buttonContainer = document.querySelector('.d-grid.gap-2.d-md-flex.justify-content-md-end');
    if (buttonContainer) {
        buttonContainer.insertBefore(paymentButton, buttonContainer.firstChild);
    }
    
    // 위젯 초기화
    initializePaymentWidget();
});
</script>
@endpush
@endsection

