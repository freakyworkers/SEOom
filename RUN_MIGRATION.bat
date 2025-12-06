@echo off
echo 마이그레이션 실행 중...
echo.

REM 일반적인 PHP 경로들 시도
if exist "C:\xampp\php\php.exe" (
    echo XAMPP PHP를 사용합니다.
    C:\xampp\php\php.exe artisan migrate
    goto :end
)

if exist "C:\laragon\bin\php\php-8.1\php.exe" (
    echo Laragon PHP 8.1을 사용합니다.
    C:\laragon\bin\php\php-8.1\php.exe artisan migrate
    goto :end
)

if exist "C:\laragon\bin\php\php-8.2\php.exe" (
    echo Laragon PHP 8.2를 사용합니다.
    C:\laragon\bin\php\php-8.2\php.exe artisan migrate
    goto :end
)

if exist "C:\Program Files\PHP\php.exe" (
    echo Program Files PHP를 사용합니다.
    "C:\Program Files\PHP\php.exe" artisan migrate
    goto :end
)

echo PHP를 찾을 수 없습니다.
echo.
echo 다음 중 하나를 시도해주세요:
echo 1. PHP를 PATH 환경변수에 추가
echo 2. 아래 SQL을 직접 데이터베이스에 실행:
echo    - database/migrations/RUN_MIGRATION.sql 파일 참고
echo 3. PHP 전체 경로를 사용하여 실행:
echo    예: "C:\xampp\php\php.exe" artisan migrate
echo.

:end
pause

