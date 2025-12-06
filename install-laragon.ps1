# Laragon 자동 다운로드 및 설치 안내 스크립트

Write-Host "=== Laragon 설치 안내 ===" -ForegroundColor Cyan
Write-Host ""

Write-Host "Laragon은 PHP, MySQL, Apache를 한 번에 설치해주는 통합 패키지입니다." -ForegroundColor Yellow
Write-Host ""

# Laragon 다운로드 페이지 열기
Write-Host "1. Laragon 다운로드 페이지를 엽니다..." -ForegroundColor Yellow
Start-Process "https://laragon.org/download/"

Write-Host ""
Write-Host "2. 설치 방법:" -ForegroundColor Cyan
Write-Host "   - 'Laragon Full' 버전 다운로드" -ForegroundColor White
Write-Host "   - 설치 프로그램 실행" -ForegroundColor White
Write-Host "   - 기본 설정으로 설치 진행" -ForegroundColor White
Write-Host ""

Write-Host "3. 설치 후:" -ForegroundColor Cyan
Write-Host "   - Laragon 실행" -ForegroundColor White
Write-Host "   - 메뉴 → Tools → Composer → Install/Update" -ForegroundColor White
Write-Host ""

Write-Host "4. 설치 확인:" -ForegroundColor Cyan
Write-Host "   - 새 명령 프롬프트 열기" -ForegroundColor White
Write-Host "   - .\check-php-composer.ps1 실행" -ForegroundColor White
Write-Host ""

Write-Host "또는 수동으로 확인:" -ForegroundColor Yellow
Write-Host "   php --version" -ForegroundColor Gray
Write-Host "   composer --version" -ForegroundColor Gray
Write-Host ""

Write-Host "설치가 완료되면 '다음'이라고 말씀해주세요!" -ForegroundColor Green








