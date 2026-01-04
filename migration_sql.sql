-- 배너 테이블에 type과 html_code 컬럼 추가
-- 이 SQL을 직접 데이터베이스에서 실행하세요

ALTER TABLE `banners` 
ADD COLUMN `type` VARCHAR(255) NOT NULL DEFAULT 'image' AFTER `location`,
ADD COLUMN `html_code` TEXT NULL AFTER `image_path`;

-- image_path를 nullable로 변경 (HTML 배너를 위해)
ALTER TABLE `banners` 
MODIFY COLUMN `image_path` VARCHAR(255) NULL;








