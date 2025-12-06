# 서버 파일 수정 필요

**상황:** 로컬 파일은 수정했지만, 서버에 있는 파일은 아직 수정되지 않았어요

---

## 🔧 해결 방법: 서버에서 직접 수정

### 방법 1: 서버에서 직접 수정 (빠름)

서버 터미널에서:

```bash
sudo nano /var/www/seoom/app/Models/Site.php
```

Enter 키 누르기

### 수정할 부분 찾기

1. **화살표 키 (↓)**로 아래로 이동
2. **`getMasterSite()`** 메서드 찾기 (약 146줄)
3. 다음 내용으로 수정:

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

### Schema import 추가

파일 맨 위 (약 7줄)에:

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

### 저장 및 나가기

1. **`Ctrl + O`** 누르기 (저장)
2. **Enter** 키 누르기
3. **`Ctrl + X`** 누르기 (나가기)

---

## 🔧 방법 2: Git으로 업데이트 (나중에)

로컬에서 수정한 내용을 Git에 커밋하고 서버에서 pull하면 돼요.

하지만 지금은 빠르게 해결하기 위해 **방법 1**을 사용하는 게 좋아요.

---

## 🎯 수정 후 다시 시도

```bash
sudo -u www-data php artisan migrate --force
```

Enter 키 누르기

---

**서버에서 파일을 수정하세요!**

