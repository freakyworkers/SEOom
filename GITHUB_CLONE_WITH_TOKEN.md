# GitHub Private 저장소 클론 (토큰 사용)

**토큰:** 받으셨네요! 이제 클론할 수 있어요!

---

## 🎯 클론 명령어

서버 터미널에서 다음 명령어를 입력하세요:

```bash
sudo git clone https://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@github.com/freakyworkers/SEOom.git seoom
```

Enter 키 누르고 완료될 때까지 기다리세요.  
**예상 시간:** 1-2분 (프로젝트 크기에 따라 다름)

**예상 결과:**
```
Cloning into 'seoom'...
remote: Enumerating objects: ...
...
```

---

## 📋 전체 과정

### 1단계: 클론

```bash
sudo git clone https://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@github.com/freakyworkers/SEOom.git seoom
```

### 2단계: 소유권 변경

```bash
sudo chown -R www-data:www-data /var/www/seoom
sudo chmod -R 755 /var/www/seoom
```

---

## ✅ 완료 확인

클론이 완료되면:

```bash
ls -la /var/www/seoom
```

Enter 키 누르면 프로젝트 파일들이 보여요.

---

## 💡 팁

- 클론이 완료되면 `/var/www/seoom` 폴더에 프로젝트가 있어요
- 소유권 변경은 Apache가 파일에 접근할 수 있게 하기 위해서예요

---

## 🎯 다음 단계 (프로젝트 클론 완료 후)

1. **Laravel 설정** (6-10 단계)
2. **Apache 가상 호스트 설정** (6-11 단계)

---

**위 명령어를 실행하세요!**

