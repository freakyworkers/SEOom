#!/bin/bash

# SEOom Builder 배포 스크립트
# 사용법: sudo bash deploy.sh

set -e  # 에러 발생 시 스크립트 중단

echo "🚀 SEOom Builder 배포를 시작합니다..."

# 프로젝트 디렉토리로 이동
cd /var/www/seoom

echo "📥 Git에서 최신 변경사항 가져오기..."
sudo git pull origin main

echo "📦 Composer 의존성 설치..."
sudo -u www-data composer install --no-dev --optimize-autoloader

echo "🗄️ 데이터베이스 마이그레이션 실행..."
sudo -u www-data php artisan migrate --force

echo "🔧 캐시 최적화..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

echo "📁 파일 권한 설정..."
sudo chown -R www-data:www-data /var/www/seoom
sudo chmod -R 755 /var/www/seoom
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

echo "🔄 Apache 재시작..."
sudo systemctl reload apache2

echo "✅ 배포가 완료되었습니다!"
echo ""
echo "확인 사항:"
echo "1. 웹사이트 접속 확인"
echo "2. 마스터 콘솔 로그인 확인"
echo "3. 사이트 생성 테스트"

