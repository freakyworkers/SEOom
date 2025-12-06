# 500 Internal Server Error 디버깅 가이드

**에러:** 500 Internal Server Error

**원인 확인 방법:**

---

## 1단계: Laravel 로그 확인

```bash
sudo tail -n 50 /var/www/seoom/storage/logs/laravel.log
```

이 명령어로 최근 에러 로그를 확인할 수 있어요.

---

## 2단계: Apache 에러 로그 확인

```bash
sudo tail -n 50 /var/log/apache2/seoom_error.log
```

또는 일반 Apache 에러 로그:

```bash
sudo tail -n 50 /var/log/apache2/error.log
```

---

## 3단계: 파일 권한 재확인

```bash
sudo chmod -R 775 /var/www/seoom/storage
sudo chmod -R 775 /var/www/seoom/bootstrap/cache
sudo chown -R www-data:www-data /var/www/seoom/storage
sudo chown -R www-data:www-data /var/www/seoom/bootstrap/cache
```

---

## 4단계: .env 파일 확인

```bash
sudo cat /var/www/seoom/.env | grep -E "APP_URL|MASTER_DOMAIN|APP_DEBUG"
```

---

## 5단계: PHP 에러 로그 확인

```bash
sudo tail -n 50 /var/log/php8.3-fpm.log
```

---

**위 명령어들을 실행해서 에러 메시지를 확인하세요!**

