# 마이그레이션 순서 문제 해결 (header_image_path)

**문제:** `post_template` 마이그레이션이 `header_image_path` 컬럼을 찾을 수 없어요

**원인:** `header_image_path` 마이그레이션이 나중 날짜라서 아직 실행되지 않았어요

---

## 🔧 해결 방법: header_image_path 마이그레이션 먼저 실행

### 1단계: header_image_path 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --path=database/migrations/2025_11_22_020511_add_header_image_path_to_boards_table.php --force
```

Enter 키 누르기

### 2단계: 나머지 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

Enter 키 누르기

---

## 📋 전체 순서

### 1단계: header_image_path 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --path=database/migrations/2025_11_22_020511_add_header_image_path_to_boards_table.php --force
```

### 2단계: 나머지 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

---

## 💡 왜 이런 문제가 발생하나요?

마이그레이션 파일명의 날짜 순서:
- `2025_01_22_000000_add_post_template_to_boards_table.php` (1월 22일)
- `2025_11_22_020511_add_header_image_path_to_boards_table.php` (11월 22일)

1월이 11월보다 먼저 실행되니까 `header_image_path` 컬럼이 없어서 에러가 발생해요.

---

**header_image_path 마이그레이션을 먼저 실행하세요!**

