# PHP & Composer 설치 확인 스크립트

Write-Host "=== PHP & Composer 설치 확인 ===" -ForegroundColor Cyan
Write-Host ""

# PHP 확인
Write-Host "1. PHP 확인 중..." -ForegroundColor Yellow
try {
    $phpVersion = php --version 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ PHP가 설치되어 있습니다!" -ForegroundColor Green
        Write-Host $phpVersion -ForegroundColor Gray
    } else {
        throw "PHP not found"
    }
} catch {
    Write-Host "❌ PHP가 설치되어 있지 않습니다." -ForegroundColor Red
    Write-Host ""
    Write-Host "설치 방법:" -ForegroundColor Yellow
    Write-Host "1. Laragon 설치 (권장): https://laragon.org/download/" -ForegroundColor White
    Write-Host "2. XAMPP 설치: https://www.apachefriends.org/download.html" -ForegroundColor White
    Write-Host "3. 수동 설치: https://windows.php.net/download/" -ForegroundColor White
    Write-Host ""
    exit 1
}

Write-Host ""

# Composer 확인
Write-Host "2. Composer 확인 중..." -ForegroundColor Yellow
try {
    $composerVersion = composer --version 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Composer가 설치되어 있습니다!" -ForegroundColor Green
        Write-Host $composerVersion -ForegroundColor Gray
    } else {
        throw "Composer not found"
    }
} catch {
    Write-Host "❌ Composer가 설치되어 있지 않습니다." -ForegroundColor Red
    Write-Host ""
    Write-Host "설치 방법:" -ForegroundColor Yellow
    Write-Host "1. Composer-Setup.exe 다운로드: https://getcomposer.org/download/" -ForegroundColor White
    Write-Host "2. 설치 프로그램 실행" -ForegroundColor White
    Write-Host ""
    exit 1
}

Write-Host ""
Write-Host "=== 모든 필수 도구가 설치되어 있습니다! ===" -ForegroundColor Green
Write-Host ""
Write-Host "다음 단계:" -ForegroundColor Cyan
Write-Host "1. composer install" -ForegroundColor White
Write-Host "2. copy .env.example .env" -ForegroundColor White
Write-Host "3. php artisan key:generate" -ForegroundColor White
Write-Host "4. php artisan migrate" -ForegroundColor White
Write-Host ""











