# 마이그레이션 순서 문제 해결 가이드

**문제:** 마이그레이션들이 서로 의존하고 있어서 순서대로 실행해야 해요

---

## 🔧 해결 방법: 순서대로 마이그레이션 실행

### 1단계: remove_links 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --path=database/migrations/2025_11_22_010637_add_remove_links_to_boards_table.php --force
```

### 2단계: enable_likes 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --path=database/migrations/2025_01_22_000002_add_enable_likes_to_boards_table.php --force
```

### 3단계: 나머지 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

---

## 📋 전체 순서 (의존성 순서)

### 1단계: remove_links 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --path=database/migrations/2025_11_22_010637_add_remove_links_to_boards_table.php --force
```

### 2단계: enable_likes 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --path=database/migrations/2025_01_22_000002_add_enable_likes_to_boards_table.php --force
```

### 3단계: 나머지 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

---

## 💡 의존성 관계

- `remove_links` (2025_11_22) ← 먼저 실행 필요
- `enable_likes` (2025_01_22) ← `remove_links` 뒤에 실행
- `saved_posts_enabled` (2025_11_27) ← `enable_likes` 뒤에 실행
- `enable_anonymous` (2025_11_25) ← `enable_likes` 뒤에 실행

---

**위 순서대로 실행하세요!**
