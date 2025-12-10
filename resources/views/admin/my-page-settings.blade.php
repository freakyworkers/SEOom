@extends('layouts.admin')

@section('title', '마이페이지 설정')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>마이페이지 설정</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ $site->isMasterSite() ? route('master.admin.my-page-settings.update') : route('admin.my-page-settings.update', ['site' => $site->slug]) }}">
                        @csrf
                        @method('PUT')

                        {{-- 사이드바 로그인 위젯 표시 항목 --}}
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="bi bi-layout-sidebar me-2"></i>사이드바 로그인 위젯 표시 항목</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_experience" id="sidebar_widget_show_experience" value="1" {{ ($settings['sidebar_widget_show_experience'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_experience">
                                                경험치
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_rank" id="sidebar_widget_show_rank" value="1" {{ ($settings['sidebar_widget_show_rank'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_rank">
                                                회원등급
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_points" id="sidebar_widget_show_points" value="1" {{ ($settings['sidebar_widget_show_points'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_points">
                                                포인트
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 로그인 위젯 하단 메뉴 --}}
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="bi bi-list-ul me-2"></i>로그인 위젯 하단 메뉴</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_notifications" id="sidebar_widget_show_notifications" value="1" {{ ($settings['sidebar_widget_show_notifications'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_notifications">
                                                알림
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_messages" id="sidebar_widget_show_messages" value="1" {{ ($settings['sidebar_widget_show_messages'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_messages">
                                                쪽지함
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_my_posts" id="sidebar_widget_show_my_posts" value="1" {{ ($settings['sidebar_widget_show_my_posts'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_my_posts">
                                                내게시글
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_profile" id="sidebar_widget_show_profile" value="1" {{ ($settings['sidebar_widget_show_profile'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_profile">
                                                내정보
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_edit_profile" id="sidebar_widget_show_edit_profile" value="1" {{ ($settings['sidebar_widget_show_edit_profile'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_edit_profile">
                                                정보변경
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_saved_posts" id="sidebar_widget_show_saved_posts" value="1" {{ ($settings['sidebar_widget_show_saved_posts'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_saved_posts">
                                                저장한글
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sidebar_widget_show_my_comments" id="sidebar_widget_show_my_comments" value="1" {{ ($settings['sidebar_widget_show_my_comments'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sidebar_widget_show_my_comments">
                                                내댓글
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 마이페이지 표시 항목 --}}
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="bi bi-person-circle me-2"></i>마이페이지 표시 항목</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>참고:</strong> 사이드바 로그인 위젯에서 경험치, 회원등급, 포인트가 비활성화된 경우 마이페이지에도 해당 항목이 표시되지 않습니다.
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="my_page_show_experience" id="my_page_show_experience" value="1" {{ ($settings['my_page_show_experience'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="my_page_show_experience">
                                                경험치
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="my_page_show_rank" id="my_page_show_rank" value="1" {{ ($settings['my_page_show_rank'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="my_page_show_rank">
                                                회원등급
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="my_page_show_points" id="my_page_show_points" value="1" {{ ($settings['my_page_show_points'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="my_page_show_points">
                                                포인트
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 마이페이지 하단 메뉴 --}}
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="bi bi-list-ul me-2"></i>마이페이지 하단 메뉴</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="my_page_show_notifications" id="my_page_show_notifications" value="1" {{ ($settings['my_page_show_notifications'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="my_page_show_notifications">
                                                알림
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="my_page_show_messages" id="my_page_show_messages" value="1" {{ ($settings['my_page_show_messages'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="my_page_show_messages">
                                                쪽지함
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="my_page_show_edit_profile" id="my_page_show_edit_profile" value="1" {{ ($settings['my_page_show_edit_profile'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="my_page_show_edit_profile">
                                                정보변경
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="my_page_show_my_posts" id="my_page_show_my_posts" value="1" {{ ($settings['my_page_show_my_posts'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="my_page_show_my_posts">
                                                내게시글
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="my_page_show_saved_posts" id="my_page_show_saved_posts" value="1" {{ ($settings['my_page_show_saved_posts'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="my_page_show_saved_posts">
                                                저장한글
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="my_page_show_my_comments" id="my_page_show_my_comments" value="1" {{ ($settings['my_page_show_my_comments'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="my_page_show_my_comments">
                                                내댓글
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>저장
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

