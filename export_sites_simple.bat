@echo off
REM 로컬 사이트 데이터 Export 스크립트
REM 사용 전에 사이트 ID를 확인하고 아래 SITE_IDS를 수정하세요

echo ========================================
echo 사이트 데이터 Export 스크립트
echo ========================================
echo.

REM 1단계: 사이트 ID 확인
echo [1단계] 사이트 ID 확인 중...
echo.
echo HeidiSQL 또는 Laragon MySQL에서 다음 쿼리를 실행하세요:
echo.
echo USE seoom;
echo SELECT id, name, slug FROM sites WHERE slug IN ('test-site', 'e') AND deleted_at IS NULL;
echo.
echo 결과에서 ID를 확인한 후, 아래 SITE_IDS 변수를 수정하세요.
echo.
pause

REM 2단계: 사이트 ID 설정 (여기를 수정하세요!)
set SITE_IDS=5,13

echo [2단계] 사이트 데이터 Export 중...
echo 사이트 ID: %SITE_IDS%
echo.

REM Laragon MySQL 경로 (버전에 따라 수정 필요)
set MYSQLDUMP=C:\laragon\bin\mysql\mysql-8.0.30\bin\mysqldump.exe

REM 파일 초기화
echo -- 사이트 데이터 Export > sites_export.sql
echo -- 생성일: %date% %time% >> sites_export.sql
echo -- 사이트: test-site, e >> sites_export.sql
echo. >> sites_export.sql
echo SET FOREIGN_KEY_CHECKS=0; >> sites_export.sql
echo. >> sites_export.sql

REM 각 테이블 Export
echo [Export] sites 테이블...
%MYSQLDUMP% -u root seoom sites --where="id IN (%SITE_IDS%)" --no-create-info >> sites_export.sql

echo [Export] users 테이블...
%MYSQLDUMP% -u root seoom users --where="site_id IN (%SITE_IDS%)" --no-create-info >> sites_export.sql

echo [Export] boards 테이블...
%MYSQLDUMP% -u root seoom boards --where="site_id IN (%SITE_IDS%)" --no-create-info >> sites_export.sql

echo [Export] posts 테이블...
%MYSQLDUMP% -u root seoom posts --where="site_id IN (%SITE_IDS%)" --no-create-info >> sites_export.sql

echo [Export] comments 테이블...
%MYSQLDUMP% -u root seoom comments --where="site_id IN (%SITE_IDS%)" --no-create-info >> sites_export.sql

echo [Export] site_settings 테이블...
%MYSQLDUMP% -u root seoom site_settings --where="site_id IN (%SITE_IDS%)" --no-create-info >> sites_export.sql

REM 파일 끝에 추가
echo. >> sites_export.sql
echo SET FOREIGN_KEY_CHECKS=1; >> sites_export.sql

echo.
echo ========================================
echo Export 완료!
echo ========================================
echo.
echo 생성된 파일: sites_export.sql
echo.
echo 다음 단계:
echo 1. sites_export.sql 파일을 서버로 복사하세요.
echo 2. 서버에서 다음 명령어를 실행하세요:
echo    mysql -u seoom_user -p seoom ^< sites_export.sql
echo.
pause

