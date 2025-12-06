# AWS EC2 AMI 선택 가이드

**상황:** Ubuntu Server 22.04 LTS를 선택할 때 여러 옵션이 보이는 경우

---

## ✅ 정답: "Ubuntu Server 22.04 LTS (HVM), SSD Volume Type" 선택!

### 선택해야 할 것:
- ✅ **"Ubuntu Server 22.04 LTS (HVM), SSD Volume Type"**
- ✅ **아키텍처: 64-bit (x86)**

### 선택하지 말아야 할 것:
- ❌ "Ubuntu Server 22.04 LTS (HVM) with SQL Server 2022 Standard"
  - SQL Server가 미리 설치되어 있어요
  - 우리는 MySQL을 사용하니까 필요 없어요!
- ❌ 64-bit (Arm)
  - 일반적인 x86 아키텍처가 더 안정적이에요

---

## 📋 옵션 비교

| 옵션 | 설명 | 우리가 사용? |
|------|------|------------|
| Ubuntu Server 22.04 LTS (HVM), SSD Volume Type | 일반 Ubuntu 서버 | ✅ **사용** |
| Ubuntu Server 22.04 LTS (HVM) with SQL Server 2022 Standard | SQL Server 포함 | ❌ 사용 안 함 |

---

## 🖼️ 화면에서 확인하는 방법

화면에 보이는 옵션들:

1. **"Ubuntu Server 22.04 LTS (HVM), SSD Volume Type"**
   - AMI ID: `ami-010be25c3775061c9` (64-bit x86)
   - ✅ **이거 선택!**

2. **"Ubuntu Server 22.04 LTS (HVM) with SQL Server 2022 Standard"**
   - AMI ID: `ami-0bae2335fbe5a4018` (64-bit x86)
   - ❌ 이거 선택하지 마세요!

---

## 💡 왜 이걸 선택해야 하나요?

### "Ubuntu Server 22.04 LTS (HVM), SSD Volume Type"을 선택하는 이유:

1. **깨끗한 Ubuntu 서버**
   - 필요한 프로그램을 직접 설치할 수 있어요
   - 불필요한 프로그램이 없어요

2. **MySQL 사용**
   - 우리는 MySQL을 사용해요
   - SQL Server는 필요 없어요

3. **안정적**
   - 가장 일반적으로 사용되는 AMI예요
   - 문제가 생겨도 해결 방법을 쉽게 찾을 수 있어요

### "with SQL Server"를 선택하지 않는 이유:

1. **불필요한 프로그램**
   - SQL Server가 미리 설치되어 있어요
   - 우리는 MySQL을 사용하니까 필요 없어요

2. **용량 낭비**
   - SQL Server가 용량을 차지해요
   - 무료 티어에서는 용량이 제한적이에요

3. **복잡함**
   - 사용하지 않는 프로그램이 있으면 관리가 복잡해져요

---

## ✅ 선택 확인 체크리스트

선택할 때 확인하세요:

- [ ] "Ubuntu Server 22.04 LTS (HVM), SSD Volume Type" 선택
- [ ] "with SQL Server"가 **없는** 옵션 선택
- [ ] 아키텍처: **64-bit (x86)** 선택
- [ ] Arm 아키텍처 선택하지 않음

---

## 🆘 실수로 잘못 선택했어요!

**해결 방법:**
1. 인스턴스를 중지하고 삭제
2. 다시 인스턴스 시작
3. 올바른 AMI 선택

**걱정 마세요!** 인스턴스를 시작하기 전이면 아무 문제 없어요!

---

**마지막 업데이트:** 2025년 1월

