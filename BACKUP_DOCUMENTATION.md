# 백업 시스템 문서

## 백업 개요

SEOom Builder는 매일 자동으로 전체 데이터베이스를 백업합니다.

### 자동 백업 스케줄
- **백업 생성**: 매일 0시 (자정)
- **백업 보관 기간**: 7일 (1주일 이상 된 백업은 자동 삭제)
- **백업 정리**: 매일 0시 30분

## 백업에 포함되는 데이터

### 1. 사이트 정보
- **sites**: 모든 사이트 정보 (이름, 슬러그, 도메인, 플랜, 상태 등)
- **site_settings**: 사이트별 설정 (테마, 레이아웃, SEO 설정 등)

### 2. 사용자 정보
- **users**: 모든 회원 정보 (이름, 이메일, 전화번호, 주소, 포인트, 소셜 로그인 정보 등)
- **user_ranks**: 회원 등급 정보
- **email_verifications**: 이메일 인증 정보
- **phone_verifications**: 전화번호 인증 정보

### 3. 게시판 및 게시글
- **boards**: 모든 게시판 정보 (이름, 설정, 권한 등)
- **posts**: 모든 게시글 (제목, 내용, 첨부파일, 조회수, 좋아요 등)
- **post_attachments**: 게시글 첨부파일 정보
- **post_likes**: 게시글 좋아요 정보
- **saved_posts**: 저장된 게시글 정보
- **topics**: 게시글 주제/태그
- **post_topic**: 게시글-주제 연결 정보

### 4. 댓글
- **comments**: 모든 댓글 (내용, 작성자, 채택 정보 등)

### 5. 메인 위젯
- **main_widget_containers**: 메인 위젯 컨테이너 (레이아웃, 정렬 설정 등)
- **main_widgets**: 메인 위젯 (블록, 이미지, 게시판, 갤러리 등 모든 위젯)

### 6. 커스텀 페이지
- **custom_pages**: 커스텀 페이지 정보
- **custom_page_widget_containers**: 커스텀 페이지 위젯 컨테이너
- **custom_page_widgets**: 커스텀 페이지 위젯

### 7. 사이드바 위젯
- **sidebar_widgets**: 사이드바 위젯 정보

### 8. 메뉴
- **menus**: 메인 메뉴 정보
- **mobile_menus**: 모바일 메뉴 정보
- **toggle_menus**: 토글 메뉴 정보
- **toggle_menu_items**: 토글 메뉴 항목

### 9. 배너 및 팝업
- **banners**: 배너 정보
- **popups**: 팝업 정보

### 10. 문의 및 지도
- **contact_forms**: 문의 양식 설정
- **contact_form_submissions**: 문의 제출 내역
- **maps**: 지도 정보

### 11. 알림 및 메시지
- **notifications**: 알림 정보
- **messages**: 쪽지 정보
- **chat_messages**: 채팅 메시지
- **chat_settings**: 채팅 설정
- **chat_guest_sessions**: 게스트 채팅 세션

### 12. 신고 및 제재
- **reports**: 신고 내역
- **penalties**: 제재 내역
- **blocked_users**: 차단된 사용자

### 13. 출석 및 포인트
- **attendances**: 출석 정보
- **attendance_settings**: 출석 설정
- **point_exchange_settings**: 포인트 교환 설정
- **point_exchange_products**: 포인트 교환 상품
- **point_exchange_applications**: 포인트 교환 신청

### 14. 이벤트
- **event_options**: 이벤트 옵션
- **event_participants**: 이벤트 참가자
- **event_application_settings**: 이벤트 신청 설정
- **event_application_products**: 이벤트 신청 상품
- **event_application_submissions**: 이벤트 신청 제출 내역

### 15. 방문자 통계
- **visitors**: 방문자 통계

### 16. 기타
- **custom_codes**: 커스텀 코드 (헤더, 푸터 등)
- **subscriptions**: 구독 정보
- **payments**: 결제 내역
- **plans**: 플랜 정보
- **addon_products**: 애드온 상품
- **user_addons**: 사용자 애드온
- **plugins**: 플러그인
- **plugin_purchases**: 플러그인 구매 내역

### 17. 마스터 관리
- **master_users**: 마스터 사용자 정보

## 백업 파일 위치

백업 파일은 다음 위치에 저장됩니다:
- **로컬**: `storage/app/backups/`
- **파일명 형식**: 
  - 전체 백업: `backup_{데이터베이스명}_{날짜_시간}.sql`
  - 사이트 백업: `backup_site_{사이트슬러그}_{날짜_시간}.sql`

## 백업 복원

백업 파일은 SQL 덤프 형식이므로 MySQL/MariaDB에서 직접 복원할 수 있습니다:

```bash
mysql -u 사용자명 -p 데이터베이스명 < backup_파일명.sql
```

## 수동 백업

관리자 페이지(`/master/backup`)에서 수동으로 백업을 생성할 수 있습니다:
- **전체 백업**: 모든 데이터베이스 백업
- **사이트별 백업**: 특정 사이트만 백업

## 주의사항

1. 백업 파일은 서버의 디스크 공간을 사용하므로 정기적으로 정리됩니다.
2. 중요한 백업은 별도로 다운로드하여 안전한 곳에 보관하세요.
3. 백업 파일에는 사용자 비밀번호가 해시된 형태로 저장됩니다.
4. 파일 업로드(storage/app/public)는 별도로 백업해야 합니다.

