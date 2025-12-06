# getMasterSite() 메서드 찾기

**상황:** nano에서 검색이 안 돼요

---

## 🔍 다른 방법으로 찾기

### 방법 1: 줄 번호로 찾기

`getMasterSite()` 메서드는 약 **146줄** 근처에 있어요.

1. **`Ctrl + G`** 누르기 (줄 번호로 이동)
2. **`146`** 입력
3. **Enter** 키 누르기

---

### 방법 2: 수동으로 찾기

1. **화살표 키 (↓)**로 아래로 계속 이동
2. **`getMasterSite`** 텍스트를 찾기
3. 또는 **`public static function getMasterSite`** 텍스트를 찾기

---

### 방법 3: 파일 구조 확인

먼저 파일이 제대로 열렸는지 확인:

```bash
grep -n "getMasterSite" /var/www/seoom/app/Models/Site.php
```

Enter 키 누르면 줄 번호가 나와요.

---

## 📋 찾은 후 수정 방법

`getMasterSite()` 메서드를 찾으면:

**현재 코드:**
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

- **`Ctrl + G`**: 줄 번호로 이동
- **`Ctrl + W`**: 검색 (대소문자 구분)
- **`Ctrl + _`**: 줄 번호로 이동 (다른 방법)

---

**먼저 `grep` 명령어로 줄 번호를 확인해보세요!**

