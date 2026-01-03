<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>알림</title>
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
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
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
        .info-box {
            background-color: #f3f4f6;
            border-left: 4px solid #2563eb;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #2563eb;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $site->name }}</h1>
        </div>
        
        <div class="content">
            <p>안녕하세요, 운영자님</p>
            
            @if($type === 'new_user')
                <p><strong>새로운 회원이 가입했습니다.</strong></p>
                <div class="info-box">
                    <p><strong>회원 정보:</strong></p>
                    <p>이름: {{ $data['name'] ?? '' }}</p>
                    <p>닉네임: {{ $data['nickname'] ?? '' }}</p>
                    <p>이메일: {{ $data['email'] ?? '' }}</p>
                    <p>아이디: {{ $data['username'] ?? '' }}</p>
                    <p>가입일: {{ $data['created_at'] ?? '' }}</p>
                </div>
            @elseif($type === 'new_post')
                <p><strong>새로운 게시글이 작성되었습니다.</strong></p>
                <div class="info-box">
                    <p><strong>게시글 정보:</strong></p>
                    <p>제목: {{ $data['title'] ?? '' }}</p>
                    <p>작성자: {{ $data['author'] ?? '' }}</p>
                    <p>게시판: {{ $data['board'] ?? '' }}</p>
                    <p>작성일: {{ $data['created_at'] ?? '' }}</p>
                </div>
            @elseif($type === 'new_comment')
                <p><strong>새로운 댓글이 작성되었습니다.</strong></p>
                <div class="info-box">
                    <p><strong>댓글 정보:</strong></p>
                    <p>작성자: {{ $data['author'] ?? '' }}</p>
                    <p>게시글: {{ $data['post_title'] ?? '' }}</p>
                    <p>내용: {{ $data['content'] ?? '' }}</p>
                    <p>작성일: {{ $data['created_at'] ?? '' }}</p>
                </div>
            @elseif($type === 'new_message')
                <p><strong>새로운 쪽지를 받았습니다.</strong></p>
                <div class="info-box">
                    <p><strong>쪽지 정보:</strong></p>
                    <p>발신자: {{ $data['sender'] ?? '' }}</p>
                    <p>제목: {{ $data['title'] ?? '' }}</p>
                    <p>내용: {{ $data['content'] ?? '' }}</p>
                    <p>수신일: {{ $data['created_at'] ?? '' }}</p>
                </div>
            @endif
        </div>
        
        <div class="footer">
            <p>이 메일은 자동으로 발송된 메일입니다.</p>
            <p>&copy; {{ date('Y') }} {{ $site->name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>







