-- 질의응답 게시판 마이그레이션 SQL
-- boards 테이블에 qa_statuses 컬럼 추가
ALTER TABLE `boards` 
ADD COLUMN `qa_statuses` JSON NULL COMMENT '질의응답 게시판 상태 설정 (JSON)' 
AFTER `pinterest_columns_large`;

-- posts 테이블에 qa_status 컬럼 추가
ALTER TABLE `posts` 
ADD COLUMN `qa_status` VARCHAR(255) NULL COMMENT '질의응답 게시판 상태' 
AFTER `event_is_ended`;






