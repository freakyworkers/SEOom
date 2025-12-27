<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $boardName }} 새댓글 작성 알림</title>
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
        .header h2 {
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
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 5px 0;
        }
        .info-box strong {
            color: #2563eb;
            display: inline-block;
            min-width: 100px;
        }
        .content-preview {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 300px;
            overflow-y: auto;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 500;
        }
        .button:hover {
            background-color: #1d4ed8;
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
            <h2>{{ $boardName }} 새댓글 작성 알림</h2>
        </div>
        
        <div class="content">
            <p>안녕하세요, 관리자님.</p>
            <p><strong>{{ $boardName }}</strong> 게시판에 새로운 댓글이 작성되었습니다.</p>
            
            <div class="info-box">
                <p><strong>게시판 이름:</strong> {{ $boardName }}</p>
                <p><strong>게시글 제목:</strong> {{ $postTitle }}</p>
                <p><strong>작성자:</strong> {{ $authorName }}</p>
            </div>
            
            <div>
                <strong>댓글 내용:</strong>
                <div class="content-preview">{{ strip_tags($comment->content) }}</div>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ route('posts.show', ['site' => $site->slug, 'boardSlug' => $comment->post->board->slug, 'post' => $comment->post->id]) }}#comment-{{ $comment->id }}" class="button">
                    댓글 바로가기
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>이 메일은 자동으로 발송된 메일입니다.</p>
            <p>&copy; {{ date('Y') }} {{ $fromName }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>






