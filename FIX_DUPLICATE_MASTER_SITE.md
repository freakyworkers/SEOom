# 중복 마스터 사이트 문제 해결 가이드

## 🔴 문제 상황

MySQL에서 확인한 결과, 마스터 사이트가 2개가 있어요:

```
+----+---------------+--------+--------------+----------------+--------+
| id | name          | slug   | domain       | is_master_site | status |
+----+---------------+--------+--------------+----------------+--------+
|  1 | 세움빌더      |        | seoomweb.com |              1 | active |
|  2 | SEOom Builder | master | seoomweb.com |              1 | active |
+----+---------------+--------+--------------+----------------+--------+
```

**문제점:**
- `getMasterSite()` 메서드는 `first()`를 사용하므로 id가 작은 것(id=1)부터 반환됩니다
- id=1의 `slug`가 비어있어서 `route('home', ['site' => $site->slug ?? 'default'])`에서 'default'가 사용되는데, 실제로는 'default'라는 slug를 가진 사이트가 없어서 에러가 발생합니다
- id=2는 slug='master'를 가지고 있어서 정상적으로 작동할 수 있어요

## ✅ 해결 방법

### MySQL에서 id=1 삭제

```sql
USE seoom;

-- id=1 삭제
DELETE FROM sites WHERE id = 1;

-- 확인 (id=2만 남아야 함)
SELECT id, name, slug, domain, is_master_site, status FROM sites WHERE is_master_site = 1;

EXIT;
```

### 또는 id=1을 비활성화 (삭제 대신)

```sql
USE seoom;

-- id=1을 비활성화
UPDATE sites SET is_master_site = 0, status = 'inactive' WHERE id = 1;

-- 확인
SELECT id, name, slug, domain, is_master_site, status FROM sites WHERE is_master_site = 1;

EXIT;
```

### 서버에서 캐시 클리어

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
```

### 브라우저에서 다시 접속

`https://seoomweb.com`으로 다시 접속해 보세요.

---

## 📋 전체 명령어 (순서대로)

```bash
# 1. MySQL 접속
sudo mysql -u root

# MySQL 프롬프트에서:
USE seoom;
DELETE FROM sites WHERE id = 1;
SELECT id, name, slug, domain, is_master_site, status FROM sites WHERE is_master_site = 1;
EXIT;

# 2. 캐시 클리어
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
```

---

## 💡 참고

- 마스터 사이트는 하나만 있어야 해요
- id=2 (SEOom Builder, slug='master')가 올바른 마스터 사이트예요
- id=1 (세움빌더, slug='')는 중복이거나 잘못 생성된 레코드예요

---

**위 명령어들을 실행한 뒤 브라우저에서 다시 접속해 보세요!**

