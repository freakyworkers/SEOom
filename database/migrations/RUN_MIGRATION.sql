-- 마이그레이션 실행 SQL
-- 이 파일은 2025_01_16_000001_add_notification_fields_to_subscriptions_table.php 마이그레이션의 SQL입니다.
-- 데이터베이스에 직접 실행하거나, php artisan migrate 명령어를 사용하세요.

-- subscriptions 테이블에 알림 필드 추가
ALTER TABLE `subscriptions` 
ADD COLUMN `reminder_sent_7days` DATE NULL AFTER `last_payment_failed_at`,
ADD COLUMN `reminder_sent_3days` DATE NULL AFTER `reminder_sent_7days`,
ADD COLUMN `reminder_sent_1day` DATE NULL AFTER `reminder_sent_3days`,
ADD COLUMN `failure_notification_sent_at` DATE NULL AFTER `reminder_sent_1day`;





