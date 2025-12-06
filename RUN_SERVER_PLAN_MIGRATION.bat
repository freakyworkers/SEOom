@echo off
echo 서버 용량 플랜 마이그레이션 실행
echo.

REM 플랜 타입에 server 추가
echo [1/2] 플랜 타입에 server 추가 중...
C:\xampp\php\php.exe artisan tinker --execute="DB::statement(\"ALTER TABLE plans MODIFY COLUMN type ENUM('landing', 'brand', 'community', 'server') DEFAULT 'landing'\"); echo '완료';"

echo.
echo [2/2] 서버 용량 플랜 생성 중...
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2025_12_08_000013_create_server_capacity_plans.php

echo.
echo 완료!
pause


