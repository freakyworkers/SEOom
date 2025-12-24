# 🏗️ SEOom Builder 마스터 사이트 메인페이지 위젯 구성 기획서

**대상**: seoomweb.com 메인 페이지  
**구성 방식**: 프로젝트 내장 위젯만 사용  
**작성일**: 2025년 1월

---

## 📋 목차
1. [페이지 구조 개요](#페이지-구조-개요)
2. [위젯 컨테이너 구성](#위젯-컨테이너-구성)
3. [각 섹션별 위젯 상세](#각-섹션별-위젯-상세)
4. [위젯 설정 가이드](#위젯-설정-가이드)
5. [구현 체크리스트](#구현-체크리스트)

---

## 🎯 페이지 구조 개요

### 전체 레이아웃
```
┌─────────────────────────────────────────┐
│  헤더 (사이트 설정)                      │
├─────────────────────────────────────────┤
│  컨테이너 1: 히어로 섹션 (1열, full_height) │
│  └─ Block 위젯 (배경 이미지 + CTA)     │
├─────────────────────────────────────────┤
│  컨테이너 2: 핵심 가치 (3열)            │
│  ├─ Block 위젯 (빠른 시작)            │
│  ├─ Block 위젯 (커스터마이징)          │
│  └─ Block 위젯 (멀티테넌트)            │
├─────────────────────────────────────────┤
│  컨테이너 3: 주요 기능 소개 (2열)       │
│  ├─ Block 위젯 (기능 설명)             │
│  └─ Image Slide 위젯 (스크린샷)        │
├─────────────────────────────────────────┤
│  컨테이너 4: 요금제 안내 (1열)          │
│  └─ Plans 위젯                         │
├─────────────────────────────────────────┤
│  컨테이너 5: 사이트 생성 CTA (1열)      │
│  └─ Create Site 위젯                   │
├─────────────────────────────────────────┤
│  컨테이너 6: 통계/사회적 증명 (4열)      │
│  ├─ Block 위젯 (사용자 수)             │
│  ├─ Block 위젯 (생성된 사이트)         │
│  ├─ Block 위젯 (가동률)                │
│  └─ Block 위젯 (평균 생성 시간)         │
├─────────────────────────────────────────┤
│  컨테이너 7: FAQ (2열)                 │
│  ├─ Custom HTML 위젯 (FAQ 목록)        │
│  └─ Contact Form 위젯 (문의하기)       │
├─────────────────────────────────────────┤
│  푸터 (사이트 설정)                      │
└─────────────────────────────────────────┘
```

---

## 📦 위젯 컨테이너 구성

### 컨테이너 1: 히어로 섹션
**설정**:
- **컬럼 수**: 1
- **전체 너비**: ✅ 활성화
- **전체 높이**: ✅ 활성화 (100vh)
- **세로 정렬**: center
- **위젯 간격**: 0

**위젯 배치**:
- **컬럼 0**: Block 위젯 (히어로 메시지)

---

### 컨테이너 2: 핵심 가치 제안
**설정**:
- **컬럼 수**: 3
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: top
- **위젯 간격**: 3

**위젯 배치**:
- **컬럼 0**: Block 위젯 (빠른 시작)
- **컬럼 1**: Block 위젯 (커스터마이징)
- **컬럼 2**: Block 위젯 (멀티테넌트)

---

### 컨테이너 3: 주요 기능 소개
**설정**:
- **컬럼 수**: 2
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: center
- **위젯 간격**: 3

**위젯 배치**:
- **컬럼 0**: Block 위젯 (기능 설명)
- **컬럼 1**: Image Slide 위젯 (스크린샷 갤러리)

---

### 컨테이너 4: 요금제 안내
**설정**:
- **컬럼 수**: 1
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: top
- **위젯 간격**: 3

**위젯 배치**:
- **컬럼 0**: Plans 위젯

---

### 컨테이너 5: 사이트 생성 CTA
**설정**:
- **컬럼 수**: 1
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: center
- **위젯 간격**: 3

**위젯 배치**:
- **컬럼 0**: Create Site 위젯 (마스터 전용)

---

### 컨테이너 6: 통계/사회적 증명
**설정**:
- **컬럼 수**: 4
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: center
- **위젯 간격**: 2

**위젯 배치**:
- **컬럼 0**: Block 위젯 (사용자 수)
- **컬럼 1**: Block 위젯 (생성된 사이트)
- **컬럼 2**: Block 위젯 (가동률)
- **컬럼 3**: Block 위젯 (평균 생성 시간)

---

### 컨테이너 7: FAQ 및 문의
**설정**:
- **컬럼 수**: 2
- **전체 너비**: ❌ 비활성화
- **전체 높이**: ❌ 비활성화
- **세로 정렬**: top
- **위젯 간격**: 3

**위젯 배치**:
- **컬럼 0**: Custom HTML 위젯 (FAQ 목록)
- **컬럼 1**: Contact Form 위젯 (문의하기)

---

## 🎨 각 섹션별 위젯 상세

### 컨테이너 1: 히어로 섹션

#### Block 위젯
**위젯 제목**: (비워두기 - 제목 숨김)

**설정**:
```json
{
  "block_title": "10분 만에 나만의 커뮤니티를 만드세요",
  "block_content": "코딩 없이, 복잡한 설정 없이\nSEOom Builder로 전문적인 웹사이트를 시작하세요",
  "text_align": "center",
  "background_type": "image",
  "background_image_url": "/images/hero-background.jpg",
  "background_color": "#007bff",
  "padding_top": 100,
  "padding_left": 40,
  "font_color": "#ffffff",
  "show_button": true,
  "button_text": "무료로 시작하기",
  "button_color": "#ffffff",
  "link": "/create-site"
}
```

**스타일 가이드**:
- 배경 이미지: 그라데이션 오버레이 적용 (CSS로 추가)
- 텍스트: 큰 폰트, 볼드
- 버튼: 흰색 아웃라인, 호버 시 채움

---

### 컨테이너 2: 핵심 가치 제안

#### Block 위젯 1: 빠른 시작
**위젯 제목**: (비워두기)

**설정**:
```json
{
  "block_title": "⚡ 10분 만에 사이트 완성",
  "block_content": "복잡한 서버 설정이나 코딩 지식이 필요 없습니다.\n간단한 가입 후 바로 사이트를 만들 수 있습니다.",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#f8f9fa",
  "padding_top": 40,
  "padding_left": 30,
  "font_color": "#212529",
  "show_button": false
}
```

#### Block 위젯 2: 커스터마이징
**위젯 제목**: (비워두기)

**설정**:
```json
{
  "block_title": "🎨 완전한 커스터마이징",
  "block_content": "위젯 시스템으로 자유롭게 레이아웃 구성.\n커스텀 페이지로 원하는 콘텐츠 추가.",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#f8f9fa",
  "padding_top": 40,
  "padding_left": 30,
  "font_color": "#212529",
  "show_button": false
}
```

#### Block 위젯 3: 멀티테넌트
**위젯 제목**: (비워두기)

**설정**:
```json
{
  "block_title": "🏢 멀티테넌트 관리",
  "block_content": "하나의 계정으로 여러 사이트 관리.\n마스터 콘솔에서 모든 사이트 통합 관리.",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#f8f9fa",
  "padding_top": 40,
  "padding_left": 30,
  "font_color": "#212529",
  "show_button": false
}
```

---

### 컨테이너 3: 주요 기능 소개

#### Block 위젯: 기능 설명
**위젯 제목**: "강력한 기능으로 완벽한 커뮤니티를 만드세요"

**설정**:
```json
{
  "block_title": "",
  "block_content": "• 다양한 게시판 타입 (일반, 클래식, 사진, 북마크)\n• 계층형 댓글 시스템\n• 위젯 시스템으로 자유로운 레이아웃\n• 강력한 관리자 도구\n• SEO 최적화\n• 커스텀 도메인 지원",
  "text_align": "left",
  "background_type": "color",
  "background_color": "#ffffff",
  "padding_top": 40,
  "padding_left": 40,
  "font_color": "#212529",
  "show_button": false
}
```

#### Image Slide 위젯: 스크린샷
**위젯 제목**: (비워두기)

**설정**:
```json
{
  "images": [
    {
      "url": "/images/screenshot-dashboard.jpg",
      "link": "",
      "alt": "대시보드 스크린샷"
    },
    {
      "url": "/images/screenshot-editor.jpg",
      "link": "",
      "alt": "에디터 스크린샷"
    },
    {
      "url": "/images/screenshot-widgets.jpg",
      "link": "",
      "alt": "위젯 관리 스크린샷"
    }
  ],
  "slide_interval": 3000,
  "show_indicators": true,
  "show_controls": true
}
```

---

### 컨테이너 4: 요금제 안내

#### Plans 위젯
**위젯 제목**: "나에게 맞는 플랜을 선택하세요"

**설정**:
```json
{
  "display_mode": "grid",
  "columns": 3,
  "show_description": true,
  "show_features": true,
  "button_text": "시작하기"
}
```

**참고**: Plans 위젯은 자동으로 활성화된 요금제를 표시합니다.

---

### 컨테이너 5: 사이트 생성 CTA

#### Create Site 위젯
**위젯 제목**: "지금 바로 시작하세요"

**설정**:
```json
{
  "button_text": "무료로 시작하기",
  "button_style": "primary",
  "show_description": true,
  "description": "코딩 지식 없이도 10분 만에\n나만의 전문적인 웹사이트를 만들어보세요"
}
```

**참고**: Create Site 위젯은 마스터 사이트에서만 사용 가능합니다.

---

### 컨테이너 6: 통계/사회적 증명

#### Block 위젯 1: 사용자 수
**위젯 제목**: (비워두기)

**설정**:
```json
{
  "block_title": "1,000+",
  "block_content": "활성 사용자",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#007bff",
  "padding_top": 30,
  "padding_left": 20,
  "font_color": "#ffffff",
  "show_button": false
}
```

#### Block 위젯 2: 생성된 사이트
**위젯 제목**: (비워두기)

**설정**:
```json
{
  "block_title": "10,000+",
  "block_content": "생성된 사이트",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#28a745",
  "padding_top": 30,
  "padding_left": 20,
  "font_color": "#ffffff",
  "show_button": false
}
```

#### Block 위젯 3: 가동률
**위젯 제목**: (비워두기)

**설정**:
```json
{
  "block_title": "99.9%",
  "block_content": "가동률",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#ffc107",
  "padding_top": 30,
  "padding_left": 20,
  "font_color": "#212529",
  "show_button": false
}
```

#### Block 위젯 4: 평균 생성 시간
**위젯 제목**: (비워두기)

**설정**:
```json
{
  "block_title": "10분",
  "block_content": "평균 사이트 생성 시간",
  "text_align": "center",
  "background_type": "color",
  "background_color": "#dc3545",
  "padding_top": 30,
  "padding_left": 20,
  "font_color": "#ffffff",
  "show_button": false
}
```

---

### 컨테이너 7: FAQ 및 문의

#### Custom HTML 위젯: FAQ 목록
**위젯 제목**: "자주 묻는 질문"

**설정**:
```html
<div class="faq-list">
  <div class="accordion" id="faqAccordion">
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
  </div>
</div>
```

#### Contact Form 위젯: 문의하기
**위젯 제목**: "문의하기"

**설정**:
- Contact Form 위젯은 관리자 페이지에서 생성한 컨텍트폼을 선택합니다.
- 마스터 사이트에 "문의하기" 컨텍트폼을 미리 생성해두어야 합니다.

**컨텍트폼 필드**:
- 이름 (필수)
- 이메일 (필수)
- 제목 (필수)
- 메시지 (필수)

---

## ⚙️ 위젯 설정 가이드

### 1. 컨테이너 생성 순서
1. 관리자 페이지 → 메인 위젯 메뉴 접근
2. "컨테이너 추가" 버튼 클릭
3. 컬럼 수, 전체 너비, 전체 높이 설정
4. 저장

### 2. 위젯 추가 순서
1. 각 컨테이너의 "위젯 추가" 버튼 클릭
2. 위젯 타입 선택
3. 위젯 제목 입력 (필요시)
4. 설정 JSON 입력 또는 폼 작성
5. 컬럼 인덱스 선택 (0부터 시작)
6. 순서 설정
7. 저장

### 3. 위젯 순서 조정
- 관리자 페이지에서 드래그 앤 드롭으로 순서 변경
- 또는 순서 숫자 직접 입력

### 4. 이미지 준비
다음 이미지들을 `/public/images/` 폴더에 준비:
- `hero-background.jpg` - 히어로 섹션 배경
- `screenshot-dashboard.jpg` - 대시보드 스크린샷
- `screenshot-editor.jpg` - 에디터 스크린샷
- `screenshot-widgets.jpg` - 위젯 관리 스크린샷

---

## ✅ 구현 체크리스트

### 준비 단계
- [ ] 히어로 배경 이미지 준비
- [ ] 스크린샷 이미지 3개 이상 준비
- [ ] FAQ 내용 작성
- [ ] 통계 데이터 확인 (실제 데이터 또는 목업)

### 컨테이너 생성
- [ ] 컨테이너 1 생성 (히어로)
- [ ] 컨테이너 2 생성 (핵심 가치)
- [ ] 컨테이너 3 생성 (기능 소개)
- [ ] 컨테이너 4 생성 (요금제)
- [ ] 컨테이너 5 생성 (CTA)
- [ ] 컨테이너 6 생성 (통계)
- [ ] 컨테이너 7 생성 (FAQ)

### 위젯 추가
- [ ] 히어로 Block 위젯 추가
- [ ] 핵심 가치 Block 위젯 3개 추가
- [ ] 기능 소개 Block 위젯 추가
- [ ] Image Slide 위젯 추가
- [ ] Plans 위젯 추가
- [ ] Create Site 위젯 추가
- [ ] 통계 Block 위젯 4개 추가
- [ ] FAQ Custom HTML 위젯 추가
- [ ] Contact Form 위젯 추가

### 설정 및 테스트
- [ ] 각 위젯 설정 확인
- [ ] 모바일 반응형 테스트
- [ ] 링크 동작 확인
- [ ] 이미지 로딩 확인
- [ ] 폼 제출 테스트
- [ ] 전체 레이아웃 확인

### 최적화
- [ ] 이미지 최적화 (압축)
- [ ] 로딩 속도 확인
- [ ] SEO 메타 태그 설정
- [ ] 다크 모드 테스트

---

## 📝 추가 참고사항

### 커스텀 CSS (필요시)
히어로 섹션에 그라데이션 오버레이를 추가하려면:
```css
.hero-block {
  position: relative;
}
.hero-block::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.2));
  z-index: 1;
}
.hero-block > * {
  position: relative;
  z-index: 2;
}
```

### 통계 데이터 업데이트
통계 Block 위젯의 숫자는 수동으로 업데이트하거나, Custom HTML 위젯으로 동적 데이터를 표시할 수 있습니다.

### 요금제 위젯
Plans 위젯은 마스터 콘솔에서 생성한 요금제를 자동으로 표시합니다. 요금제가 없으면 표시되지 않습니다.

---

**작성일**: 2025년 1월  
**버전**: 1.0  
**대상**: seoomweb.com 마스터 사이트

