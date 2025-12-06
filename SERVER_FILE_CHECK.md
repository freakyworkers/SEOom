# 서버 파일 확인 및 수정

**상황:** nano에서 검색이 안 돼요

---

## 🔍 먼저 줄 번호 확인

서버 터미널에서:

```bash
grep -n "getMasterSite" /var/www/seoom/app/Models/Site.php
```

Enter 키 누르면 줄 번호가 나와요.

**예상 결과:**
```
147:    public static function getMasterSite(): ?self
```

---

## 📋 줄 번호로 이동하기

### 1단계: 파일 열기

```bash
sudo nano /var/www/seoom/app/Models/Site.php
```

Enter 키 누르기

### 2단계: 줄 번호로 이동

1. **`Ctrl + _`** 누르기 (줄 번호로 이동)
2. **`147`** 입력
3. **Enter** 키 누르기

또는:

1. **`Ctrl + G`** 누르기 (줄 번호로 이동)
2. **`147`** 입력
3. **Enter** 키 누르기

---

## 📝 수정 방법

147줄 근처에 있는 `getMasterSite()` 메서드를 찾으면:

**현재 코드 (서버에 있을 수 있는 버전):**
```php
public static function getMasterSite(): ?self
{
    return static::where('is_master_site', true)
        ->where('status', 'active')
        ->first();
}
```

**수정할 내용:**

1. `return static::where` 줄 앞에 다음 추가:
   ```php
   try {
       // Check if table exists before querying
       if (!Schema::hasTable('sites')) {
           return null;
       }
   ```

2. `->first();` 다음 줄에 다음 추가:
   ```php
   } catch (\Exception $e) {
       // If table doesn't exist or any other error, return null
       return null;
   }
   ```

---

## 💡 nano 단축키

- **`Ctrl + _`**: 줄 번호로 이동 (가장 확실)
- **`Ctrl + G`**: 줄 번호로 이동 (다른 방법)
- **`Ctrl + W`**: 검색 (대소문자 구분, `getMasterSite`로 검색)
- **화살표 키**: 수동으로 이동

---

## ✅ Schema import도 확인

파일 맨 위 (약 7줄)에 다음이 있는지 확인:

```php
use Illuminate\Support\Facades\Schema;
```

없으면 추가하세요!

---

**먼저 `grep` 명령어로 줄 번호를 확인하세요!**

