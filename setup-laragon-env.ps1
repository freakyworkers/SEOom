# Laragon 환경 변수 설정 스크립트

Write-Host "=== Laragon 환경 변수 설정 ===" -ForegroundColor Cyan
Write-Host ""

# 관리자 권한 확인
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "⚠️  관리자 권한이 필요합니다." -ForegroundColor Yellow
    Write-Host "PowerShell을 관리자 권한으로 실행한 후 다시 시도하세요." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "또는 Laragon의 '터미널' 버튼을 사용하세요!" -ForegroundColor Cyan
    Write-Host "Laragon → 터미널 버튼 클릭 → 자동으로 PHP/Composer 인식됨" -ForegroundColor White
    exit 1
}

# Laragon 경로
$laragonPath = "C:\laragon"
$phpPath = Get-ChildItem "$laragonPath\bin\php" -Directory -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
$composerPath = "$laragonPath\bin\composer"

if (-not $phpPath) {
    Write-Host "❌ Laragon PHP 경로를 찾을 수 없습니다." -ForegroundColor Red
    Write-Host "Laragon이 다른 경로에 설치되어 있을 수 있습니다." -ForegroundColor Yellow
    exit 1
}

Write-Host "PHP 경로: $phpPath" -ForegroundColor Gray
Write-Host "Composer 경로: $composerPath" -ForegroundColor Gray
Write-Host ""

# 환경 변수에 추가
$currentPath = [Environment]::GetEnvironmentVariable("Path", "Machine")
$phpBinPath = $phpPath
$composerBinPath = $composerPath

if ($currentPath -notlike "*$phpBinPath*") {
    Write-Host "PHP 경로를 환경 변수에 추가 중..." -ForegroundColor Yellow
    [Environment]::SetEnvironmentVariable("Path", "$currentPath;$phpBinPath", "Machine")
    Write-Host "✅ PHP 경로 추가 완료" -ForegroundColor Green
} else {
    Write-Host "✅ PHP 경로가 이미 등록되어 있습니다" -ForegroundColor Green
}

if (Test-Path $composerBinPath) {
    if ($currentPath -notlike "*$composerBinPath*") {
        Write-Host "Composer 경로를 환경 변수에 추가 중..." -ForegroundColor Yellow
        $newPath = [Environment]::GetEnvironmentVariable("Path", "Machine")
        [Environment]::SetEnvironmentVariable("Path", "$newPath;$composerBinPath", "Machine")
        Write-Host "✅ Composer 경로 추가 완료" -ForegroundColor Green
    } else {
        Write-Host "✅ Composer 경로가 이미 등록되어 있습니다" -ForegroundColor Green
    }
} else {
    Write-Host "⚠️  Composer가 아직 설치되지 않았습니다." -ForegroundColor Yellow
    Write-Host "Laragon → 메뉴 → Tools → Composer → Install/Update 실행" -ForegroundColor White
}

Write-Host ""
Write-Host "✅ 환경 변수 설정 완료!" -ForegroundColor Green
Write-Host ""
Write-Host "⚠️  중요: 명령 프롬프트를 재시작해야 변경사항이 적용됩니다." -ForegroundColor Yellow
Write-Host ""
Write-Host "또는 Laragon의 '터미널' 버튼을 사용하면 자동으로 인식됩니다!" -ForegroundColor Cyan










