<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 실패 안내</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #dc2626;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .error-box {
            background-color: #fef2f2;
            border: 2px solid #dc2626;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .error-box h2 {
            color: #dc2626;
            margin-top: 0;
            font-size: 20px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #fecaca;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #991b1b;
        }
        .info-value {
            color: #7f1d1d;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning p {
            margin: 0;
            color: #92400e;
        }
        .danger {
            background-color: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .danger p {
            margin: 0;
            color: #991b1b;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $site->name }}</h1>
        </div>
        
        <div class="content">
            <p>안녕하세요,</p>
            <p><strong>{{ $plan->name }}</strong> 구독료 결제에 실패했습니다.</p>
            
            <div class="error-box">
                <h2>결제 실패 정보</h2>
                <div class="info-item">
                    <span class="info-label">플랜명:</span>
                    <span class="info-value">{{ $plan->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">결제 금액:</span>
                    <span class="info-value">{{ number_format($plan->price) }}원</span>
                </div>
                @if($payment && $payment->failure_reason)
                <div class="info-item">
                    <span class="info-label">실패 사유:</span>
                    <span class="info-value">{{ $payment->failure_reason }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">재시도 횟수:</span>
                    <span class="info-value">{{ $retryCount }}회</span>
                </div>
                <div class="info-item">
                    <span class="info-label">다음 재시도일:</span>
                    <span class="info-value">{{ $nextRetryDate->format('Y년 m월 d일') }}</span>
                </div>
            </div>
            
            @if($retryCount >= 2)
            <div class="danger">
                <p><strong>🚨 중요 안내</strong></p>
                <p>결제가 3일 연속 실패할 경우 서비스가 일시 중지됩니다. 결제 수단을 확인하고 결제를 완료해주시기 바랍니다.</p>
            </div>
            @else
            <div class="warning">
                <p><strong>⚠️ 안내</strong></p>
                <p>다음날 자동으로 재결제를 시도합니다. 결제 수단을 확인해주시기 바랍니다.</p>
            </div>
            @endif
            
            <p>결제 관련 문의사항이 있으시면 고객센터로 연락해주시기 바랍니다.</p>
        </div>
        
        <div class="footer">
            <p>이 메일은 자동으로 발송된 메일입니다.</p>
            <p>&copy; {{ date('Y') }} {{ $site->name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>



