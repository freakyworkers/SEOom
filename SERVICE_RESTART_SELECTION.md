# 서비스 재시작 선택 화면

**상황:** "Daemons using outdated libraries" 메시지가 나왔어요

---

## ✅ 해결 방법

### 옵션 1: 기본 선택된 것만 재시작 (권장)

현재 `packagekit.service`가 선택되어 있어요:
- **Tab 키**로 **"Ok"**로 이동
- **Enter 키** 누르기

### 옵션 2: 모든 서비스 재시작 (더 안전)

1. **위/아래 화살표 키**로 서비스 선택
2. **스페이스 바**로 체크박스 선택/해제
3. 모든 서비스를 선택 (체크박스에 `[*]` 표시)
4. **Tab 키**로 **"Ok"**로 이동
5. **Enter 키** 누르기

---

## 📋 화면 설명

**"Which services should be restarted?"**
- 업데이트된 라이브러리를 사용하는 서비스를 재시작할지 선택하는 화면이에요

**서비스 목록:**
- `networkd-dispatcher.service` - 네트워크 관련
- `packagekit.service` - 패키지 관리 (현재 선택됨)
- `unattended-upgrades.service` - 자동 업그레이드

---

## 💡 추천 방법

**기본 선택된 것만 재시작:**
- 가장 간단해요
- `packagekit.service`만 재시작하면 돼요
- **Tab 키** → **Enter 키**

**또는 모든 서비스 재시작:**
- 더 안전해요
- 모든 서비스를 선택하고 재시작
- **위/아래 화살표** → **스페이스 바** → **Tab** → **Enter**

---

## ✅ 빠른 방법

1. **Tab 키** 누르기 (Ok 버튼으로 이동)
2. **Enter 키** 누르기

이렇게 하면 기본 선택된 `packagekit.service`만 재시작돼요!

---

**Tab 키 → Enter 키를 누르세요!**

