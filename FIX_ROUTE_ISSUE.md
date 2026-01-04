# 라우트 문제 해결 가이드

## 문제
- 게시글 작성 라우트 `/site/{site}/boards/{boardSlug}/posts/create`가 404 오류 발생
- 라우트는 등록되어 있으나 실제 매칭 실패

## 원인 분석
1. 라우트 바인딩 문제: `Route::prefix('site/{site}')`에서 `{site}` 파라미터 바인딩이 제대로 작동하지 않음
2. 라우트 순서 문제: `/boards/{slug}`가 `/boards/{boardSlug}/posts/create`보다 먼저 매칭될 수 있음

## 해결 방법

### 옵션 1: 라우트 바인딩 확인
RouteServiceProvider에서 `{site}` 바인딩이 제대로 작동하는지 확인

### 옵션 2: 라우트 구조 변경
prefix 그룹 대신 각 라우트에 명시적으로 `{site}` 파라미터 포함

### 옵션 3: 라우트 순서 재조정
더 구체적인 라우트를 먼저 등록

## 현재 상태
- 라우트 등록: ✅ 정상
- 라우트 바인딩: ⚠️ 문제 가능
- 라우트 매칭: ❌ 실패











