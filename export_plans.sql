-- Plans 테이블 데이터 Export SQL
-- 로컬 MySQL에서 실행하여 결과를 복사하세요

SELECT CONCAT(
    'INSERT INTO plans (',
    'id, name, slug, description, type, billing_type, ',
    'price, one_time_price, traffic_limit_mb, ',
    'features, limits, sort_order, is_active, is_default, ',
    'created_at, updated_at, deleted_at',
    ') VALUES (',
    id, ', ',
    QUOTE(name), ', ',
    QUOTE(slug), ', ',
    IFNULL(QUOTE(description), 'NULL'), ', ',
    QUOTE(COALESCE(type, ''landing'')), ', ',
    QUOTE(COALESCE(billing_type, ''free'')), ', ',
    COALESCE(price, 0), ', ',
    IFNULL(one_time_price, 'NULL'), ', ',
    IFNULL(traffic_limit_mb, 'NULL'), ', ',
    QUOTE(COALESCE(features, ''{}''))), ', ',
    QUOTE(COALESCE(limits, ''{}''))), ', ',
    COALESCE(sort_order, 0), ', ',
    IF(is_active, 1, 0), ', ',
    IF(is_default, 1, 0), ', ',
    QUOTE(COALESCE(created_at, NOW()))), ', ',
    QUOTE(COALESCE(updated_at, NOW()))), ', ',
    IFNULL(QUOTE(deleted_at), 'NULL'),
    ');'
) AS sql_statement
FROM plans
ORDER BY id;

