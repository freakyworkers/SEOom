# 서버에서 직접 파일 수정 (가장 빠름)

**상황:** Git pull이 소유권 문제로 실패했어요

**해결:** 서버에서 직접 파일을 수정하는 게 가장 빠르고 확실해요

---

## 🔧 해결 방법: 서버에서 직접 수정

### 1단계: 파일 열기

```bash
sudo nano /var/www/seoom/app/Models/Site.php
```

Enter 키 누르기

---

### 2단계: Schema import 추가

파일 맨 위 (약 7줄)로 이동:

**현재:**
```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
```

**수정 후:**
```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
```

**방법:**
1. 화살표 키(↑)로 파일 맨 위로 이동
2. `use Illuminate\Database\Eloquent\SoftDeletes;` 다음 줄로 이동
3. Enter 키 누르기 (새 줄 만들기)
4. 타이핑: `use Illuminate\Support\Facades\Schema;`

---

### 3단계: getMasterSite() 메서드 수정

화살표 키(↓)로 아래로 이동해서 `getMasterSite()` 메서드 찾기 (약 146줄):

**현재 (잘못됨):**
```php
public static function getMasterSite(): ?self
{
    return static::where('is_master_site', true)
        ->where('status', 'active')
        ->first();
}
```

**수정 후 (올바름):**
```php
public static function getMasterSite(): ?self
{
    try {
        // Check if table exists before querying
        if (!Schema::hasTable('sites')) {
            return null;
        }
        return static::where('is_master_site', true)
            ->where('status', 'active')
            ->first();
    } catch (\Exception $e) {
        // If table doesn't exist or any other error, return null
        return null;
    }
}
```

**방법:**
1. `getMasterSite()` 메서드 찾기
2. `return static::where` 줄을 찾아서
3. 그 위에 다음을 추가:
   ```php
   try {
       // Check if table exists before querying
       if (!Schema::hasTable('sites')) {
           return null;
       }
   ```
4. `->first();` 다음 줄에 다음을 추가:
   ```php
   } catch (\Exception $e) {
       // If table doesn't exist or any other error, return null
       return null;
   }
   ```

---

### 4단계: 저장 및 나가기

1. **`Ctrl + O`** 누르기 (저장)
2. **Enter** 키 누르기
3. **`Ctrl + X`** 누르기 (나가기)

---

## 🎯 수정 후 다시 시도

```bash
sudo -u www-data php artisan migrate --force
```

Enter 키 누르기

---

## 💡 nano 편집기 팁

- **화살표 키**: 커서 이동
- **백스페이스/Delete**: 삭제
- **Enter**: 줄바꿈
- **`Ctrl + O`**: 저장
- **`Ctrl + X`**: 나가기
- **`Ctrl + W`**: 검색 (메서드 찾을 때 유용)

---

**서버에서 파일을 직접 수정하세요!**

