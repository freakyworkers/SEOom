# PHP & Composer 자동 설치 스크립트 (Windows)

Write-Host "=== PHP & Composer 자동 설치 ===" -ForegroundColor Cyan
Write-Host ""

# 관리자 권한 확인
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "⚠️  관리자 권한이 필요합니다." -ForegroundColor Yellow
    Write-Host "PowerShell을 관리자 권한으로 실행한 후 다시 시도하세요." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "또는 수동 설치 가이드를 참고하세요: INSTALL_PHP_COMPOSER.md" -ForegroundColor Cyan
    exit 1
}

# Chocolatey 확인
Write-Host "1. Chocolatey 확인 중..." -ForegroundColor Yellow
$chocoInstalled = Get-Command choco -ErrorAction SilentlyContinue

if (-not $chocoInstalled) {
    Write-Host "Chocolatey가 설치되어 있지 않습니다." -ForegroundColor Yellow
    Write-Host "Chocolatey를 설치하시겠습니까? (Y/N)" -ForegroundColor Cyan
    $response = Read-Host
    
    if ($response -eq 'Y' -or $response -eq 'y') {
        Write-Host "Chocolatey 설치 중..." -ForegroundColor Yellow
        Set-ExecutionPolicy Bypass -Scope Process -Force
        [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
        iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    } else {
        Write-Host "수동 설치 가이드를 참고하세요: INSTALL_PHP_COMPOSER.md" -ForegroundColor Cyan
        exit 1
    }
}

# PHP 설치
Write-Host ""
Write-Host "2. PHP 설치 중..." -ForegroundColor Yellow
try {
    choco install php -y
    Write-Host "✅ PHP 설치 완료!" -ForegroundColor Green
} catch {
    Write-Host "❌ PHP 설치 실패. 수동으로 설치해주세요." -ForegroundColor Red
    Write-Host "가이드: INSTALL_PHP_COMPOSER.md" -ForegroundColor Cyan
}

# Composer 설치
Write-Host ""
Write-Host "3. Composer 설치 중..." -ForegroundColor Yellow
try {
    choco install composer -y
    Write-Host "✅ Composer 설치 완료!" -ForegroundColor Green
} catch {
    Write-Host "❌ Composer 설치 실패. 수동으로 설치해주세요." -ForegroundColor Red
    Write-Host "가이드: https://getcomposer.org/download/" -ForegroundColor Cyan
}

# 환경 변수 새로고침
Write-Host ""
Write-Host "4. 환경 변수 새로고침 중..." -ForegroundColor Yellow
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# 확인
Write-Host ""
Write-Host "=== 설치 확인 ===" -ForegroundColor Cyan
php --version
composer --version

Write-Host ""
Write-Host "✅ 설치가 완료되었습니다!" -ForegroundColor Green
Write-Host ""
Write-Host "다음 단계:" -ForegroundColor Cyan
Write-Host "1. composer install" -ForegroundColor White
Write-Host "2. copy .env.example .env" -ForegroundColor White
Write-Host "3. php artisan key:generate" -ForegroundColor White











