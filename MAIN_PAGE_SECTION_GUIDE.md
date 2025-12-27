# 📝 SEOom Builder 메인페이지 섹션별 문구 및 위젯 배치 가이드

**작성일**: 2025년 1월  
**대상**: seoomweb.com 마스터 사이트 메인페이지  
**목적**: 각 섹션별 문구 작성 및 위젯 배치 방법 안내

---

## 📋 목차

1. [전체 구성 개요](#전체-구성-개요)
2. [섹션 1: 히어로 섹션](#섹션-1-히어로-섹션)
3. [섹션 2: 핵심 가치 제안](#섹션-2-핵심-가치-제안)
4. [섹션 3: 주요 기능 소개](#섹션-3-주요-기능-소개)
5. [섹션 4: 통계/사회적 증명](#섹션-4-통계사회적-증명)
6. [섹션 5: 요금제 안내](#섹션-5-요금제-안내)
7. [섹션 6: 사이트 생성 CTA](#섹션-6-사이트-생성-cta)
8. [섹션 7: FAQ 및 문의](#섹션-7-faq-및-문의)
9. [구현 체크리스트](#구현-체크리스트)
10. [문구 작성 팁](#문구-작성-팁)

---

## 🎯 전체 구성 개요

### 권장 섹션 순서
```
1. 히어로 섹션 (Hero Section)
   ↓
2. 핵심 가치 제안 (Value Proposition)
   ↓
3. 주요 기능 소개 (Features)
   ↓
4. 통계/사회적 증명 (Social Proof)
   ↓
5. 요금제 안내 (Pricing)
   ↓
6. 사이트 생성 CTA (Call to Action)
   ↓
7. FAQ 및 문의 (FAQ & Contact)
```

### 전체 레이아웃 구조
```
┌─────────────────────────────────────────┐
│  헤더 (사이트 설정)                      │
├─────────────────────────────────────────┤
│  [섹션 1] 히어로 섹션                    │
│  └─ Block 위젯 (전체 높이)              │
├─────────────────────────────────────────┤
│  [섹션 2] 핵심 가치 제안 (3열)          │
│  └─ Block 위젯 3개                      │
├─────────────────────────────────────────┤
│  [섹션 3] 주요 기능 소개 (2열)          │
│  ├─ Block 위젯 (기능 설명)              │
│  └─ Image Slide 위젯 (스크린샷)         │
├─────────────────────────────────────────┤
│  [섹션 4] 통계/사회적 증명 (4열)        │
│  └─ Block 위젯 4개                     │
├─────────────────────────────────────────┤
│  [섹션 5] 요금제 안내 (1열)             │
│  └─ Plans 위젯                         │
├─────────────────────────────────────────┤
│  [섹션 6] 사이트 생성 CTA (1열)         │
│  └─ Create Site 위젯                   │
├─────────────────────────────────────────┤
│  [섹션 7] FAQ 및 문의 (2열)            │
│  ├─ Custom HTML 위젯 (FAQ)              │
│  └─ Contact Form 위젯                  │
├─────────────────────────────────────────┤
│  푸터 (사이트 설정)                      │
└─────────────────────────────────────────┘
```

---

## 🎯 섹션 1: 히어로 섹션

### 목적
- 방문자의 첫인상 형성
- 핵심 가치 제안 전달
- 즉각적인 행동 유도 (CTA)

### 컨테이너 설정
- **컬럼 수**: 1
- **전체 너비**: ✅ 활성화 (사이드바 없음 설정 시)
- **전체 높이**: ✅ 활성화 (100vh)
- **세로 정렬**: center
- **위젯 간격**: 0

### 사용 위젯
- **위젯 타입**: Block 위젯
- **위젯 제목**: (비워두기 - 제목 숨김)

### 추천 문구

#### 메인 헤드라인 (큰 제목)
```
10분 만에 나만의 커뮤니티를 만드세요
```

**대안 문구:**
- "코딩 없이 전문적인 웹사이트 만들기"
- "누구나 쉽게 시작하는 커뮤니티 플랫폼"
- "10분이면 충분합니다. 지금 바로 시작하세요"

#### 서브 헤드라인 (설명)
```
코딩 없이, 복잡한 설정 없이
SEOom Builder로 전문적인 웹사이트를 시작하세요
```

**대안 문구:**
- "드래그 앤 드롭으로 쉽고 빠르게"
- "서버 설정, 데이터베이스 구성, 코드 작성 - 모두 필요 없습니다"
- "간단한 가입만으로 즉시 시작할 수 있습니다"

#### 주요 포인트 (3개, 선택사항)
```
✅ 10분 완성 - 복잡한 설정 없이 즉시 시작
✅ 무제한 커스터마이징 - 나만의 디자인과 기능
✅ 완전한 멀티테넌트 - 여러 사이트를 한 번에 관리
```

### 위젯 설정 (JSON)

```json
{
  "block_title": "10분 만에 나만의 커뮤니티를 만드세요",
  "block_content": "코딩 없이, 복잡한 설정 없이\nSEOom Builder로 전문적인 웹사이트를 시작하세요\n\n✅ 10분 완성 - 복잡한 설정 없이 즉시 시작\n✅ 무제한 커스터마이징 - 나만의 디자인과 기능\n✅ 완전한 멀티테넌트 - 여러 사이트를 한 번에 관리",
  "text_align": "center",
  "background_type": "image",
  "background_image_url": "/images/hero-background.jpg",
  "background_color": "#007bff",
  "padding_top": 100,
  "padding_left": 40,
  "padding_bottom": 100,
  "font_color": "#ffffff",
  "show_button": true,
  "button_text": "무료로 시작하기",
  "button_color": "#ffffff",
  "button_bg_color": "#007bff",
  "link": "/create-site"
}
```

### 디자인 가이드
- **배경**: 그라데이션 오버레이가 있는 이미지 또는 단색
- **텍스트 색상**: 흰색 (#ffffff) 또는 대비가 강한 색상
- **버튼**: 눈에 띄는 색상, 큰 크기
- **폰트 크기**: 헤드라인은 48px 이상 (모바일: 32px 이상)

### 추가 옵션
- **보조 CTA 버튼**: "데모 보기" (아웃라인 스타일)
- **배경 비디오**: 제품 데모 비디오 (선택사항)
- **스크롤 인디케이터**: "더 알아보기" 화살표 (선택사항)

---

## 💎 섹션 2: 핵심 가치 제안

### 목적
- SEOom Builder의 핵심 가치 전달
- 경쟁 우위 강조
- 사용자 이점 명확화

### 컨테이너 설정
- **컬럼 수**: 3
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: top
- **위젯 간격**: 3 (보통)

### 사용 위젯
- **위젯 타입**: Block 위젯 3개
- **위젯 제목**: (비워두기)

### 섹션 제목 (선택사항)
위젯 제목을 사용하지 않으려면, 첫 번째 Block 위젯에 섹션 제목을 포함하거나 별도의 Block 위젯을 추가할 수 있습니다.

```
왜 SEOom Builder를 선택해야 할까요?
```

### 카드 1: 빠른 시작

#### 제목
```
⚡ 10분 만에 사이트 완성
```

#### 설명
```
복잡한 서버 설정이나 코딩 지식이 필요 없습니다.
간단한 가입 후 바로 사이트를 만들 수 있습니다.
자동 Provisioning으로 즉시 사용 가능합니다.
```

#### 위젯 설정
```json
{
  "block_title": "⚡ 10분 만에 사이트 완성",
  "block_content": "복잡한 서버 설정이나 코딩 지식이 필요 없습니다.\n간단한 가입 후 바로 사이트를 만들 수 있습니다.\n자동 Provisioning으로 즉시 사용 가능합니다.",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#f8f9fa",
  "padding_top": 40,
  "padding_left": 30,
  "padding_bottom": 40,
  "font_color": "#212529",
  "show_button": false
}
```

### 카드 2: 커스터마이징

#### 제목
```
🎨 완전한 커스터마이징
```

#### 설명
```
위젯 시스템으로 자유롭게 레이아웃 구성.
커스텀 페이지로 원하는 콘텐츠 추가.
다크 모드, 반응형 디자인 자동 지원.
```

#### 위젯 설정
```json
{
  "block_title": "🎨 완전한 커스터마이징",
  "block_content": "위젯 시스템으로 자유롭게 레이아웃 구성.\n커스텀 페이지로 원하는 콘텐츠 추가.\n다크 모드, 반응형 디자인 자동 지원.",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#f8f9fa",
  "padding_top": 40,
  "padding_left": 30,
  "padding_bottom": 40,
  "font_color": "#212529",
  "show_button": false
}
```

### 카드 3: 멀티테넌트 관리

#### 제목
```
🏢 멀티테넌트 관리
```

#### 설명
```
하나의 계정으로 여러 사이트 관리.
마스터 콘솔에서 모든 사이트 통합 관리.
SSO 로그인으로 편리한 접근.
```

#### 위젯 설정
```json
{
  "block_title": "🏢 멀티테넌트 관리",
  "block_content": "하나의 계정으로 여러 사이트 관리.\n마스터 콘솔에서 모든 사이트 통합 관리.\nSSO 로그인으로 편리한 접근.",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#f8f9fa",
  "padding_top": 40,
  "padding_left": 30,
  "padding_bottom": 40,
  "font_color": "#212529",
  "show_button": false
}
```

### 디자인 가이드
- **배경 색상**: 밝은 회색 (#f8f9fa) 또는 흰색
- **카드 스타일**: 그림자 효과로 깊이감 추가
- **아이콘**: 이모지 또는 아이콘 폰트 사용
- **일관성**: 3개 카드의 높이와 스타일 통일

---

## ⚡ 섹션 3: 주요 기능 소개

### 목적
- 상세 기능 소개
- 제품의 강점 시각화
- 사용자 신뢰 구축

### 컨테이너 설정
- **컬럼 수**: 2
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: center
- **위젯 간격**: 3 (보통)

### 사용 위젯
- **컬럼 0**: Block 위젯 (기능 설명)
- **컬럼 1**: Image Slide 위젯 (스크린샷)

### 섹션 제목
```
강력한 기능으로 완벽한 커뮤니티를 만드세요
```

### Block 위젯: 기능 설명

#### 제목
(비워두기 - 섹션 제목을 별도로 표시하거나 Block 위젯 제목에 포함)

#### 기능 목록
```
• 다양한 게시판 타입
  - 일반 게시판, 클래식 게시판, 사진 게시판, 북마크 게시판

• 계층형 댓글 시스템
  - 대댓글 지원, 실시간 알림, 추천/비추천 기능

• 위젯 시스템
  - 메인 위젯, 사이드바 위젯, 커스텀 페이지 위젯
  - 드래그 앤 드롭으로 쉬운 배치

• 강력한 관리자 도구
  - 직관적인 대시보드, 통합 관리, 통계 및 분석

• SEO 최적화
  - 자동 sitemap.xml 생성, robots.txt 관리, RSS 피드

• 커스텀 도메인 지원
  - 서브도메인 자동 제공, 커스텀 도메인 연결
```

#### 위젯 설정
```json
{
  "block_title": "",
  "block_content": "• 다양한 게시판 타입\n  - 일반 게시판, 클래식 게시판, 사진 게시판, 북마크 게시판\n\n• 계층형 댓글 시스템\n  - 대댓글 지원, 실시간 알림, 추천/비추천 기능\n\n• 위젯 시스템\n  - 메인 위젯, 사이드바 위젯, 커스텀 페이지 위젯\n  - 드래그 앤 드롭으로 쉬운 배치\n\n• 강력한 관리자 도구\n  - 직관적인 대시보드, 통합 관리, 통계 및 분석\n\n• SEO 최적화\n  - 자동 sitemap.xml 생성, robots.txt 관리, RSS 피드\n\n• 커스텀 도메인 지원\n  - 서브도메인 자동 제공, 커스텀 도메인 연결",
  "text_align": "left",
  "background_type": "color",
  "background_color": "#ffffff",
  "padding_top": 40,
  "padding_left": 40,
  "padding_bottom": 40,
  "font_color": "#212529",
  "show_button": false
}
```

### Image Slide 위젯: 스크린샷

#### 위젯 제목
(비워두기)

#### 이미지 목록
1. **대시보드 스크린샷**
   - URL: `/images/screenshot-dashboard.jpg`
   - Alt: "관리자 대시보드 스크린샷"

2. **에디터 스크린샷**
   - URL: `/images/screenshot-editor.jpg`
   - Alt: "게시글 에디터 스크린샷"

3. **위젯 관리 스크린샷**
   - URL: `/images/screenshot-widgets.jpg`
   - Alt: "위젯 관리 화면 스크린샷"

#### 위젯 설정
```json
{
  "images": [
    {
      "url": "/images/screenshot-dashboard.jpg",
      "link": "",
      "alt": "관리자 대시보드 스크린샷"
    },
    {
      "url": "/images/screenshot-editor.jpg",
      "link": "",
      "alt": "게시글 에디터 스크린샷"
    },
    {
      "url": "/images/screenshot-widgets.jpg",
      "link": "",
      "alt": "위젯 관리 화면 스크린샷"
    }
  ],
  "slide_interval": 3000,
  "show_indicators": true,
  "show_controls": true
}
```

### 디자인 가이드
- **레이아웃**: 좌우 분할 (텍스트 + 이미지)
- **모바일**: 세로 배치로 변경
- **이미지**: 고품질 스크린샷, 최적화된 파일 크기

---

## 📊 섹션 4: 통계/사회적 증명

### 목적
- 신뢰도 향상
- 사회적 증명 제공
- 플랫폼의 성장과 안정성 강조

### 컨테이너 설정
- **컬럼 수**: 4
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: center
- **위젯 간격**: 2 (좁음)

### 사용 위젯
- **위젯 타입**: Block 위젯 4개

### 통계 카드 1: 사용자 수

#### 제목
```
1,000+
```

#### 설명
```
활성 사용자
```

#### 위젯 설정
```json
{
  "block_title": "1,000+",
  "block_content": "활성 사용자",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#007bff",
  "padding_top": 30,
  "padding_left": 20,
  "padding_bottom": 30,
  "font_color": "#ffffff",
  "show_button": false
}
```

### 통계 카드 2: 생성된 사이트

#### 제목
```
10,000+
```

#### 설명
```
생성된 사이트
```

#### 위젯 설정
```json
{
  "block_title": "10,000+",
  "block_content": "생성된 사이트",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#28a745",
  "padding_top": 30,
  "padding_left": 20,
  "padding_bottom": 30,
  "font_color": "#ffffff",
  "show_button": false
}
```

### 통계 카드 3: 가동률

#### 제목
```
99.9%
```

#### 설명
```
가동률
```

#### 위젯 설정
```json
{
  "block_title": "99.9%",
  "block_content": "가동률",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#ffc107",
  "padding_top": 30,
  "padding_left": 20,
  "padding_bottom": 30,
  "font_color": "#212529",
  "show_button": false
}
```

### 통계 카드 4: 평균 생성 시간

#### 제목
```
10분
```

#### 설명
```
평균 사이트 생성 시간
```

#### 위젯 설정
```json
{
  "block_title": "10분",
  "block_content": "평균 사이트 생성 시간",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#dc3545",
  "padding_top": 30,
  "padding_left": 20,
  "padding_bottom": 30,
  "font_color": "#ffffff",
  "show_button": false
}
```

### 디자인 가이드
- **색상**: 각 카드마다 다른 강조 색상 사용
- **숫자**: 큰 폰트, 볼드체
- **설명**: 작은 폰트, 명확한 라벨
- **일관성**: 모든 카드의 높이와 패딩 통일

### 데이터 업데이트
- 통계 숫자는 실제 데이터로 업데이트하거나
- Custom HTML 위젯으로 동적 데이터 표시 가능

---

## 💰 섹션 5: 요금제 안내

### 목적
- 요금제 정보 제공
- 가격 투명성 확보
- 플랜 선택 유도

### 컨테이너 설정
- **컬럼 수**: 1
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: top
- **위젯 간격**: 3 (보통)

### 사용 위젯
- **위젯 타입**: Plans 위젯
- **위젯 제목**: "나에게 맞는 플랜을 선택하세요"

### 섹션 제목
```
나에게 맞는 플랜을 선택하세요
```

### Plans 위젯 설정

```json
{
  "display_mode": "grid",
  "columns": 3,
  "show_description": true,
  "show_features": true,
  "button_text": "시작하기"
}
```

### 참고사항
- Plans 위젯은 마스터 콘솔에서 생성한 활성화된 요금제를 자동으로 표시합니다
- 요금제가 없으면 위젯이 표시되지 않습니다
- 요금제는 마스터 콘솔에서 관리합니다

### 디자인 가이드
- **레이아웃**: 그리드 형식 (3열 또는 4열)
- **인기 플랜**: "인기" 배지 표시
- **가격**: 명확하게 표시
- **기능 목록**: 체크마크로 표시

---

## 🚀 섹션 6: 사이트 생성 CTA

### 목적
- 최종 행동 유도
- 회원가입/사이트 생성 전환
- 긴급성 조성

### 컨테이너 설정
- **컬럼 수**: 1
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: center
- **위젯 간격**: 3 (보통)

### 사용 위젯
- **위젯 타입**: Create Site 위젯 (마스터 전용)
- **위젯 제목**: "지금 바로 시작하세요"

### 섹션 제목
```
지금 바로 시작하세요
```

### 메시지
```
코딩 지식 없이도 10분 만에
나만의 전문적인 웹사이트를 만들어보세요
```

### Create Site 위젯 설정

```json
{
  "button_text": "무료로 시작하기",
  "button_style": "primary",
  "show_description": true,
  "description": "코딩 지식 없이도 10분 만에\n나만의 전문적인 웹사이트를 만들어보세요"
}
```

### 신뢰 요소 (선택사항)
- "이미 1,000명 이상이 사용 중"
- "무료 체험, 신용카드 불필요"
- "언제든지 취소 가능"

### 디자인 가이드
- **배경**: 강조 색상 또는 그라데이션
- **버튼**: 큰 크기, 눈에 띄는 색상
- **텍스트**: 명확하고 간결한 메시지

---

## ❓ 섹션 7: FAQ 및 문의

### 목적
- 일반적인 질문에 대한 답변 제공
- 고객 지원 접근성 향상
- 신뢰도 구축

### 컨테이너 설정
- **컬럼 수**: 2
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: top
- **위젯 간격**: 3 (보통)

### 사용 위젯
- **컬럼 0**: Custom HTML 위젯 (FAQ)
- **컬럼 1**: Contact Form 위젯 (문의하기)

### Custom HTML 위젯: FAQ

#### 위젯 제목
```
자주 묻는 질문
```

#### FAQ HTML 코드

```html
<div class="faq-list">
  <div class="accordion" id="faqAccordion">
    <!-- FAQ 1 -->
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
          코딩 지식이 없어도 사용할 수 있나요?
        </button>
      </h2>
      <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          네, 완전히 가능합니다. SEOom Builder는 드래그 앤 드롭 방식의 직관적인 인터페이스를 제공하여 누구나 쉽게 사이트를 만들 수 있습니다.
        </div>
      </div>
    </div>

    <!-- FAQ 2 -->
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
          무료 플랜에도 모든 기능이 포함되나요?
        </button>
      </h2>
      <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          무료 플랜에는 기본 기능이 포함되어 있으며, 더 많은 기능이 필요하시면 유료 플랜으로 업그레이드할 수 있습니다.
        </div>
      </div>
    </div>

    <!-- FAQ 3 -->
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
          커스텀 도메인을 연결할 수 있나요?
        </button>
      </h2>
      <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          프로 플랜 이상에서는 커스텀 도메인을 연결할 수 있습니다. 간단한 DNS 설정만으로 연결이 완료됩니다.
        </div>
      </div>
    </div>

    <!-- FAQ 4 -->
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
          데이터 백업은 어떻게 되나요?
        </button>
      </h2>
      <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          모든 플랜에서 자동 백업이 제공되며, 엔터프라이즈 플랜에서는 더 자주 백업됩니다.
        </div>
      </div>
    </div>

    <!-- FAQ 5 -->
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
          여러 사이트를 관리할 수 있나요?
        </button>
      </h2>
      <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          네, 하나의 계정으로 여러 사이트를 만들고 관리할 수 있습니다. 마스터 콘솔에서 모든 사이트를 한눈에 볼 수 있습니다.
        </div>
      </div>
    </div>

    <!-- FAQ 6 -->
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
          환불 정책은 어떻게 되나요?
        </button>
      </h2>
      <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          30일 무조건 환불 보장 정책을 제공합니다. 만족하지 않으시면 전액 환불해드립니다.
        </div>
      </div>
    </div>
  </div>
</div>
```

### Contact Form 위젯: 문의하기

#### 위젯 제목
```
문의하기
```

#### 설정
- Contact Form 위젯은 관리자 페이지에서 생성한 컨텍트폼을 선택합니다
- 마스터 사이트에 "문의하기" 컨텍트폼을 미리 생성해두어야 합니다

#### 권장 컨텍트폼 필드
- 이름 (필수)
- 이메일 (필수)
- 제목 (필수)
- 메시지 (필수)

### 디자인 가이드
- **FAQ**: 아코디언 스타일로 공간 효율적
- **문의 폼**: 간단하고 명확한 필드
- **레이아웃**: 좌우 분할 (FAQ + 폼)

---

## ✅ 구현 체크리스트

### 준비 단계
- [ ] 히어로 배경 이미지 준비 (`/public/images/hero-background.jpg`)
- [ ] 스크린샷 이미지 3개 이상 준비
  - [ ] 대시보드 스크린샷
  - [ ] 에디터 스크린샷
  - [ ] 위젯 관리 스크린샷
- [ ] FAQ 내용 작성 및 검토
- [ ] 통계 데이터 확인 (실제 데이터 또는 목업)
- [ ] 문의하기 컨텍트폼 생성

### 컨테이너 생성
- [ ] 컨테이너 1 생성 (히어로 섹션)
  - [ ] 컬럼 수: 1
  - [ ] 전체 너비: 활성화
  - [ ] 전체 높이: 활성화
  - [ ] 세로 정렬: center
- [ ] 컨테이너 2 생성 (핵심 가치)
  - [ ] 컬럼 수: 3
  - [ ] 위젯 간격: 3
- [ ] 컨테이너 3 생성 (기능 소개)
  - [ ] 컬럼 수: 2
  - [ ] 위젯 간격: 3
- [ ] 컨테이너 4 생성 (통계)
  - [ ] 컬럼 수: 4
  - [ ] 위젯 간격: 2
- [ ] 컨테이너 5 생성 (요금제)
  - [ ] 컬럼 수: 1
- [ ] 컨테이너 6 생성 (CTA)
  - [ ] 컬럼 수: 1
- [ ] 컨테이너 7 생성 (FAQ)
  - [ ] 컬럼 수: 2
  - [ ] 위젯 간격: 3

### 위젯 추가
- [ ] 히어로 Block 위젯 추가
  - [ ] 문구 입력
  - [ ] 배경 이미지 설정
  - [ ] CTA 버튼 설정
- [ ] 핵심 가치 Block 위젯 3개 추가
  - [ ] 빠른 시작
  - [ ] 커스터마이징
  - [ ] 멀티테넌트
- [ ] 기능 소개 Block 위젯 추가
- [ ] Image Slide 위젯 추가
  - [ ] 이미지 3개 업로드 및 설정
- [ ] 통계 Block 위젯 4개 추가
  - [ ] 사용자 수
  - [ ] 생성된 사이트
  - [ ] 가동률
  - [ ] 평균 생성 시간
- [ ] Plans 위젯 추가
- [ ] Create Site 위젯 추가
- [ ] FAQ Custom HTML 위젯 추가
- [ ] Contact Form 위젯 추가

### 설정 및 테스트
- [ ] 각 위젯 설정 확인
- [ ] 모바일 반응형 테스트
- [ ] 링크 동작 확인
- [ ] 이미지 로딩 확인
- [ ] 폼 제출 테스트
- [ ] 전체 레이아웃 확인
- [ ] 다크 모드 테스트

### 최적화
- [ ] 이미지 최적화 (압축)
- [ ] 로딩 속도 확인
- [ ] SEO 메타 태그 설정
- [ ] 접근성 확인

---

## 📝 문구 작성 팁

### 효과적인 헤드라인 작성법

1. **명확성**: 무엇을 제공하는지 명확하게
   - ✅ "10분 만에 나만의 커뮤니티를 만드세요"
   - ❌ "최고의 솔루션"

2. **구체성**: 구체적인 숫자나 사실 포함
   - ✅ "10분 만에"
   - ❌ "빠르게"

3. **이점 중심**: 사용자가 얻을 수 있는 이점 강조
   - ✅ "코딩 없이"
   - ❌ "강력한 기능"

4. **행동 유도**: 명확한 행동 제시
   - ✅ "지금 바로 시작하세요"
   - ❌ "더 알아보기"

### 서브 헤드라인 작성법

1. **보완성**: 헤드라인을 보완하는 정보 제공
2. **간결성**: 핵심만 전달
3. **신뢰성**: 구체적인 사실 포함

### CTA 버튼 문구 작성법

1. **긍정적 표현**: "시작하기", "만들기"
2. **명확한 액션**: "무료로 시작하기"
3. **긴급성**: "지금 바로", "오늘"
4. **이점 강조**: "무료로", "즉시"

### FAQ 작성법

1. **실제 질문**: 사용자가 자주 묻는 질문
2. **명확한 답변**: 간결하고 이해하기 쉬운 답변
3. **긍정적 톤**: 친절하고 도움이 되는 톤
4. **구체성**: 추상적이지 않고 구체적인 정보

---

## 🎨 추가 디자인 권장사항

### 색상 팔레트
- **Primary**: #007bff (파란색)
- **Success**: #28a745 (초록색)
- **Warning**: #ffc107 (노란색)
- **Danger**: #dc3545 (빨간색)
- **Background**: #f8f9fa (밝은 회색)
- **Text**: #212529 (어두운 회색)

### 타이포그래피
- **헤드라인**: 큰 볼드체 (48px 이상)
- **서브헤드라인**: 중간 크기 (24px)
- **본문**: 읽기 쉬운 크기 (16px)
- **라인 높이**: 1.5-1.8

### 간격 및 여백
- **섹션 간격**: 충분한 여백 (80px 이상)
- **카드 간격**: 일관된 간격
- **패딩**: 충분한 내부 여백

### 반응형 디자인
- **모바일**: 세로 배치, 큰 버튼
- **태블릿**: 2열 레이아웃
- **데스크톱**: 3-4열 레이아웃

---

## 📚 참고 문서

- [MASTER_SITE_WIDGET_PLAN.md](./MASTER_SITE_WIDGET_PLAN.md) - 위젯 구성 기획서
- [MASTER_SITE_LANDING_PAGE.md](./MASTER_SITE_LANDING_PAGE.md) - 메인페이지 구성 가이드
- [LANDING_PAGE_IDEAS.md](./LANDING_PAGE_IDEAS.md) - 메인페이지 아이디어

---

**작성일**: 2025년 1월  
**버전**: 1.0  
**대상**: seoomweb.com 마스터 사이트

