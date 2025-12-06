<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>이메일 인증</title>
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
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .code-box {
            background-color: #f3f4f6;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #666;
            text-align: center;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $fromName ?? $site->name }}</h1>
        </div>
        
        <div class="content">
            <p>안녕하세요,</p>
            <p><strong>{{ $fromName ?? $site->name }}</strong> 회원 가입을 위해 아래 인증번호를 입력해주세요.</p>
            
            <div class="code-box">
                <div class="code">{{ $verificationCode }}</div>
            </div>
            
            <div class="warning">
                <p><strong>⚠️ 주의사항</strong></p>
                <p>이 인증번호는 10분 동안만 유효합니다. 만료된 경우 다시 발송해주세요.</p>
            </div>
        </div>
        
        <div class="footer">
            <p>이 메일은 자동으로 발송된 메일입니다. 회원가입을 요청하지 않으셨다면 이 메일을 무시하셔도 됩니다.</p>
            <p>&copy; {{ date('Y') }} {{ $fromName ?? $site->name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
