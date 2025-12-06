<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEOom Builder - 환영합니다</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <h1 class="display-4 mb-4">
                            <i class="bi bi-house-door-fill text-primary"></i>
                            SEOom Builder
                        </h1>
                        <p class="lead mb-4">멀티테넌트 커뮤니티·홈페이지·쇼핑몰 SaaS 플랫폼</p>
                        
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle"></i>
                            아직 생성된 사이트가 없습니다.
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('master.login') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-shield-lock"></i> 마스터 콘솔 로그인
                            </a>
                            <p class="text-muted mt-3 mb-0">
                                <small>마스터 콘솔에서 사이트를 생성하세요.</small>
                            </p>
                        </div>

                        <hr class="my-4">

                        <div class="row text-start mt-4">
                            <div class="col-md-6 mb-3">
                                <h5><i class="bi bi-check-circle text-success"></i> 주요 기능</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="bi bi-dot"></i> 멀티테넌트 구조</li>
                                    <li><i class="bi bi-dot"></i> 사이트 자동 생성</li>
                                    <li><i class="bi bi-dot"></i> 게시판/게시글/댓글</li>
                                    <li><i class="bi bi-dot"></i> 관리자 페이지</li>
                                </ul>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h5><i class="bi bi-gear text-primary"></i> 마스터 콘솔</h5>
                                <ul class="list-unstyled ms-3">
                                    <li><i class="bi bi-dot"></i> 사이트 관리</li>
                                    <li><i class="bi bi-dot"></i> 모니터링</li>
                                    <li><i class="bi bi-dot"></i> 백업 관리</li>
                                    <li><i class="bi bi-dot"></i> SSO 기능</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted">
                        <small>기본 계정: admin@seoom.com / admin123</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

