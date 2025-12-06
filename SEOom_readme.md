# 🏗️ SEOom Builder — ALL IN ONE README  
**세움빌더(SEOom Builder)**  
멀티테넌트 커뮤니티·홈페이지·쇼핑몰 SaaS 플랫폼

(대표님 요청에 따라 전체 기획 + 개발 + 서버 구성 문서 통합 버전)

---

## 📌 1. 프로젝트 개요  
SEOom Builder(세움빌더)는 커뮤니티/홈페이지/게시판/쇼핑몰을  
10분 만에 제작할 수 있는 PHP(Laravel) 기반 웹 빌더입니다.

- 관리자 페이지 제공  
- 마스터 운영 콘솔 제공  
- AWS EC2 기반 호스팅  
- 사이트 자동 생성(Provisioning)  
- 멀티테넌트 구조 지원

---

## 📌 2. 전체 기술 스택  
### Backend  
- PHP 8.x / Laravel 10  
- MySQL(MariaDB)  
- Composer  
- Redis (옵션)  

### Frontend  
- HTML, CSS  
- jQuery  
- Bootstrap 또는 Tailwind  

### Infra  
- AWS EC2 Ubuntu  
- Apache + PHP-FPM  
- Cloudflare SSL  
- GitHub Deploy  

---

## 📌 3. 전체 개발 로드맵(실행 순서)

### 1) 로컬 개발 환경 구축  
- PHP / Composer / MySQL 설치  
- Laravel 프로젝트 세팅  
- .env 작성  
- php artisan migrate 실행  

### 2) 단일 사이트 CMS 개발  
- 회원가입/로그인  
- 게시판/게시글/댓글  
- 관리자 페이지 구축  
- 사이트 설정 기능  

### 3) 멀티테넌트 구조 적용  
- sites 테이블 생성  
- 기존 테이블에 site_id 추가  
- /site/{slug}/ URL 구조  
- Provisioning 서비스 생성  

### 4) 마스터 운영 콘솔 개발  
- 모든 사이트 관리  
- 사이트 생성/삭제/정지  
- SSO(운영자 → 고객 관리자)  
- 트래픽/DB 용량 모니터링  
- 백업/복구  

### 5) AWS EC2 배포  
- 서버 생성  
- Apache + PHP 설정  
- Git pull 배포 자동화  
- SSL 인증서 적용  

### 6) 상용 서비스 런칭  
- 요금제 적용  
- 결제 연동(옵션)  
- 고객센터/가이드 제작  

---

## 📌 4. AWS EC2 설치 및 배포

1. EC2 생성 (Ubuntu 22.04 / t3.micro)  
2. SSH 접속  
3. Apache + PHP 설치  
4. MySQL 설치  
5. GitHub에서 프로젝트 클론  
6. .env 설정  
7. php artisan migrate  
8. VirtualHost 세팅  
9. certbot SSL 발급  

자세한 명령어는 INSTALL.md와 동일하며, 이 문서에 통합되어 있습니다.

---

## 📌 5. DB 스키마 (요약)

### sites  
- id  
- name  
- slug  
- domain  
- plan  
- status  

### users  
- id  
- site_id  
- email  
- password  
- role  

### boards / posts / comments  
- 모두 site_id 필수  

---

## 📌 6. 시스템 아키텍처 구조  
```
[사용자]
   ↓  
[프론트엔드] — [고객 관리자 페이지]
   ↓  
[Laravel API 서버 (멀티테넌트)]
   ↓  
[MySQL 사이트별 데이터 관리]
   ↓  
[마스터 운영 페이지]
   ↓  
[AWS EC2 + Cloudflare]
```

---

## 📌 7. 개발 협업 규칙 (요약)

### Git 규칙  
```
feat: 기능 추가
fix: 버그 수정
docs: 문서 업데이트
refactor: 내부 구조 정리
```

### 코드 스타일  
- Service 중심 구조  
- Controller는 얇게, 비즈니스 로직은 Service로 이동  
- Blade 템플릿은 컴포넌트 구조 사용  

---

## 📌 8. 마스터 운영 콘솔 스펙  
URL 구조:
```
/master/login
/master/dashboard
/master/sites
/master/sites/{id}
/master/monitoring
/master/backup
```

기능:
- 사이트 생성/삭제/중지  
- 백업/복구  
- 고객 계정 관리  
- 공지 발행  
- 트래픽/용량 모니터링  

---

## 📌 9. Cursor + Codex 마스터 프롬프트  
```
너는 PHP 8 + Laravel 10 기반의 SEOom Builder(세움빌더) SaaS 플랫폼의 시니어 개발자이다.

요구사항:
1) 단일 사이트 CMS를 먼저 구현한다.
2) sites 테이블을 추가하여 멀티테넌트 구조를 만든다.
3) 모든 주요 테이블(users, boards, posts, comments)에 site_id를 추가한다.
4) /site/{slug}/ URL 구조를 만든다.
5) SiteProvisionService를 만들어 새 사이트 생성 시 기본 관리자/게시판/설정이 자동 생성된다.
6) /master 이하 경로에 마스터 운영 콘솔을 구현한다.
7) AWS EC2(Ubuntu + Apache + PHP + MySQL)에 배포되는 것을 기준으로 개발한다.
8) 나는 기능을 요구하면, 너는 마이그레이션, 모델, 컨트롤러, 라우트, Blade, 서비스 코드를 순서대로 제시한다.
```

---

## 📌 10. 프로젝트 진행 상황

### 현재 진행률: 약 85%

**상세한 진행 상황 및 다음 단계는 다음 문서를 참조하세요:**
- [`PROJECT_PROGRESS.md`](./PROJECT_PROGRESS.md) - **최신 작업 진행 현황 및 앞으로 할 일** ⭐
- [`PROJECT_STATUS.md`](./PROJECT_STATUS.md) - 전체 프로젝트 현황
- [`PROJECT_ROADMAP.md`](./PROJECT_ROADMAP.md) - 상세 작업 계획 및 체크리스트

### 현재 진행 중인 작업
- **Phase 1: 관리자 페이지 전반적인 기능 업데이트** (진행 중)
  - 게시글 관리 페이지 개선 (필터링, 검색, 일괄 관리)
  - 사용자 관리 페이지 개선
  - 게시판 관리 페이지 개선
  - 대시보드 개선
  - 사이트 설정 기능 확장

### 최근 완료된 주요 기능
- ✅ 게시판 타입 시스템 (일반, 클래식, 사진, 북마크)
- ✅ 주제(Topic) 시스템 (카테고리, 필터링)
- ✅ 게시글 양식(템플릿) 기능
- ✅ 게시판 하단 콘텐츠 기능
- ✅ 북마크 게시판 특화 기능
- ✅ 링크 삭제 기능
- ✅ 권한 관리 시스템 강화
- ✅ 반응형 디자인 개선

---

## 📌 11. 결론  
이 문서 하나로:

- 기획서  
- 개발 문서  
- 서버 구축 문서  
- API 구조  
- 협업 문서  
- Cursor 개발 스타터  

모두 준비되었습니다.

세움빌더 프로젝트는 이제 즉시 개발을 시작할 수 있는 상태입니다.
