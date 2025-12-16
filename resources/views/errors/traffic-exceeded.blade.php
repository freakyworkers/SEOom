<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>트래픽 초과 - {{ $site->name ?? 'SEOom Builder' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .traffic-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .btn-upgrade {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="bi bi-exclamation-triangle-fill error-icon"></i>
        <h1>트래픽 사용량 초과</h1>
        <p class="lead">죄송합니다. 이 사이트의 월간 트래픽 사용량이 한도에 도달했습니다.</p>
        
        <div class="traffic-info">
            <h5>사용량 정보</h5>
            <p class="mb-0">
                <strong>사용 중:</strong> {{ number_format($trafficUsed, 2) }} MB<br>
                <strong>제한:</strong> {{ number_format($trafficLimit, 2) }} MB
            </p>
            <p class="text-muted mt-2 mb-0">
                <small>트래픽은 매월 1일에 자동으로 초기화됩니다.</small>
            </p>
        </div>

        <p>더 많은 트래픽이 필요하시다면 플랜을 업그레이드하거나 서버 용량을 추가 구매해주세요.</p>

        <div class="d-grid gap-2">
            <a href="https://seoomweb.com" class="btn btn-primary btn-lg btn-upgrade">
                <i class="bi bi-arrow-up-circle me-2"></i>플랜 업그레이드하기
            </a>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>이전 페이지로
            </a>
        </div>
    </div>
</body>
</html>

