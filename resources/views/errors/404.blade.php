<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - 페이지를 찾을 수 없습니다</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container">
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6 text-center">
            <div class="error-page">
                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="h4 mb-3">페이지를 찾을 수 없습니다</h2>
                <p class="text-muted mb-4">
                    요청하신 페이지가 존재하지 않거나 이동되었을 수 있습니다.<br>
                    URL을 확인하시거나 아래 버튼을 이용해 이동해주세요.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>이전 페이지
                    </a>
                    @php
                        $currentPath = request()->path();
                        $isMasterPath = str_starts_with($currentPath, 'master');
                        
                        $siteSlug = null;
                        if (!$isMasterPath) {
                            $siteSlug = request()->route('site')?->slug ?? null;
                            if (!$siteSlug && preg_match('/^site\/([^\/]+)/', $currentPath, $matches)) {
                                $siteSlug = $matches[1];
                            }
                        }
                    @endphp
                    @if($isMasterPath)
                        <a href="{{ route('master.login') }}" class="btn btn-primary">
                            <i class="bi bi-house me-1"></i>마스터 콘솔
                        </a>
                    @elseif($siteSlug)
                        <a href="{{ route('home', ['site' => $siteSlug]) }}" class="btn btn-primary">
                            <i class="bi bi-house me-1"></i>홈으로
                        </a>
                    @else
                        <a href="/" class="btn btn-primary">
                            <i class="bi bi-house me-1"></i>홈으로
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

