@extends('layouts.admin')

@section('title', '사이드 위젯')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header mb-4">
                <h1 class="h3 mb-2">사이드바 위젯</h1>
                <p class="text-muted">[추가] 버튼을 눌러 위젯을 추가하고 마우스 드래그로 위치를 변경할 수 있습니다</p>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="enable_sidebar_login_widget" 
                               name="enable_sidebar_login_widget" 
                               {{ ($site->getSetting('enable_sidebar_login_widget', true) ? 'checked' : '') }}>
                        <label class="form-check-label fw-bold" for="enable_sidebar_login_widget">
                            사이드바 로그인 위젯 활성화
                        </label>
                    </div>
                    <small class="text-muted d-block mt-2 mb-3">
                        체크 해제 시 사이드바의 로그인 위젯이 표시되지 않습니다.
                    </small>
                    
                    <div class="mb-0">
                        <label class="form-label fw-bold" for="sidebar_mobile_display">
                            사이드바 모바일 표시
                        </label>
                        <select class="form-select" 
                                id="sidebar_mobile_display" 
                                name="sidebar_mobile_display">
                            <option value="top" {{ $site->getSetting('sidebar_mobile_display', 'top') === 'top' ? 'selected' : '' }}>본문 상단</option>
                            <option value="bottom" {{ $site->getSetting('sidebar_mobile_display', 'top') === 'bottom' ? 'selected' : '' }}>본문 하단</option>
                            <option value="none" {{ $site->getSetting('sidebar_mobile_display', 'top') === 'none' ? 'selected' : '' }}>표시안함</option>
                        </select>
                        <small class="text-muted d-block mt-2">
                            모바일에서 사이드바를 표시할 위치를 선택하세요. 사이드바가 활성화된 경우에만 적용됩니다.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="widgetList" class="widget-list">
                                @if($widgets->isEmpty())
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        <p>추가된 위젯이 없습니다. 오른쪽에서 위젯을 추가해주세요.</p>
                                    </div>
                                @else
                                    @foreach($widgets as $widget)
                                        <div class="widget-item card mb-3" 
                                             data-widget-id="{{ $widget->id }}" 
                                             data-widget-title="{{ $widget->title }}"
                                             data-widget-type="{{ $widget->type }}"
                                             data-widget-active="{{ $widget->is_active ? '1' : '0' }}"
                                             data-widget-settings="{{ json_encode($widget->settings ?? []) }}"
                                             draggable="true">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-grip-vertical me-3 text-muted" style="cursor: move;"></i>
                                                        <div class="d-flex flex-column me-2">
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-secondary p-1 mb-1" 
                                                                    onclick="moveWidgetUp({{ $widget->id }})"
                                                                    style="width: 24px; height: 24px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                    title="위로 이동">
                                                                <i class="bi bi-chevron-up" style="font-size: 12px;"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-secondary p-1" 
                                                                    onclick="moveWidgetDown({{ $widget->id }})"
                                                                    style="width: 24px; height: 24px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                                                    title="아래로 이동">
                                                                <i class="bi bi-chevron-down" style="font-size: 12px;"></i>
                                                            </button>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">
                                                                {{ $widget->title }}
                                                                @if(!$widget->is_active)
                                                                    <span class="badge bg-secondary ms-2">비활성</span>
                                                                @endif
                                                            </h6>
                                                            <small class="text-muted">{{ $availableTypes[$widget->type] ?? $widget->type }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-info" 
                                                                onclick="openSidebarWidgetAnimationModal({{ $widget->id }})"
                                                                title="애니메이션 설정">
                                                            <i class="bi bi-magic"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary" 
                                                                onclick="editWidget({{ $widget->id }})">
                                                            설정
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteWidget({{ $widget->id }})">
                                                            삭제
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">위젯 추가</h6>
                                </div>
                                <div class="card-body">
                                    <form id="addWidgetForm">
                                        <div class="mb-3">
                                            <label for="widget_type" class="form-label">위젯 종류</label>
                                            <select class="form-select" id="widget_type" name="type" required>
                                                <option value="">선택하세요</option>
                                                @foreach($availableTypes as $key => $label)
                                                    <option value="{{ $key }}" 
                                                            @if($key === 'gallery')
                                                            data-description="사진형 게시판, 북마크 게시판, 블로그 게시판만 적용 가능합니다."
                                                            @endif>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div id="widget_type_description" class="mt-2 text-muted small" style="display: none;">
                                                <i class="bi bi-info-circle"></i> <span id="widget_type_description_text"></span>
                                            </div>
                                        </div>
                                        <div class="mb-3" id="widget_title_container">
                                            <label for="widget_title" class="form-label">
                                                위젯 제목 <span id="widget_title_optional" style="display: none;">(선택사항)</span>
                                                <span id="widget_title_help" style="display: none;">
                                                    <i class="bi bi-question-circle text-muted ms-1" 
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="top" 
                                                       title="제목을 입력하지 않으면 위젯 제목이 표시되지 않습니다."></i>
                                                </span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="widget_title" 
                                                   name="title" 
                                                   placeholder="위젯 제목을 입력하세요"
                                                   required>
                                        </div>
                                        <div class="mb-3" id="widget_board_container" style="display: none;">
                                            <label for="widget_board_id" class="form-label">게시판 선택</label>
                                            <select class="form-select" id="widget_board_id" name="board_id">
                                                <option value="">선택하세요</option>
                                                @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                                                    <option value="{{ $board->id }}">{{ $board->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3" id="widget_sort_order_container" style="display: none;">
                                            <label for="widget_sort_order" class="form-label">표시 방식</label>
                                            <select class="form-select" id="widget_sort_order" name="sort_order">
                                                <option value="latest">최신순</option>
                                                <option value="oldest">예전순</option>
                                                <option value="random">랜덤</option>
                                                <option value="popular">인기순</option>
                                            </select>
                                        </div>
                                        <div class="mb-3" id="widget_marquee_direction_container" style="display: none;">
                                            <label class="form-label">전광판 표시 방향</label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="direction" id="direction_left" value="left" checked>
                                                <label class="btn btn-outline-primary" for="direction_left">
                                                    <i class="bi bi-arrow-left"></i> 좌
                                                </label>
                                                
                                                <input type="radio" class="btn-check" name="direction" id="direction_right" value="right">
                                                <label class="btn btn-outline-primary" for="direction_right">
                                                    <i class="bi bi-arrow-right"></i> 우
                                                </label>
                                                
                                                <input type="radio" class="btn-check" name="direction" id="direction_up" value="up">
                                                <label class="btn btn-outline-primary" for="direction_up">
                                                    <i class="bi bi-arrow-up"></i> 상
                                                </label>
                                                
                                                <input type="radio" class="btn-check" name="direction" id="direction_down" value="down">
                                                <label class="btn btn-outline-primary" for="direction_down">
                                                    <i class="bi bi-arrow-down"></i> 하
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3" id="widget_gallery_container" style="display: none;">
                                            <label for="widget_gallery_board_id" class="form-label">
                                                게시판 선택
                                                <i class="bi bi-question-circle text-muted ms-1" 
                                                   data-bs-toggle="tooltip" 
                                                   data-bs-placement="top" 
                                                   title="사진형 게시판, 북마크 게시판, 블로그 게시판만 선택 가능합니다."></i>
                                            </label>
                                            <select class="form-select" id="widget_gallery_board_id" name="gallery_board_id">
                                                <option value="">선택하세요</option>
                                                @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                                                    @if(in_array($board->type, ['photo', 'bookmark', 'blog']))
                                                        <option value="{{ $board->id }}">{{ $board->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3" id="widget_gallery_display_type_container" style="display: none;">
                                            <label for="widget_gallery_display_type" class="form-label">표시 방식</label>
                                            <select class="form-select" id="widget_gallery_display_type" name="gallery_display_type" onchange="handleGalleryDisplayTypeChange()">
                                                <option value="grid" selected>일반</option>
                                                <option value="slide">슬라이드</option>
                                            </select>
                                        </div>
                                        <div class="mb-3" id="widget_gallery_grid_container" style="display: none;">
                                            <div class="row">
                                                <div class="col-6">
                                                    <label for="widget_gallery_cols" class="form-label">가로 개수</label>
                                                    <select class="form-select" id="widget_gallery_cols" name="gallery_cols">
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3" selected>3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label for="widget_gallery_rows" class="form-label">세로 줄수</label>
                                                    <select class="form-select" id="widget_gallery_rows" name="gallery_rows">
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3" selected>3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3" id="widget_gallery_slide_container" style="display: none;">
                                            <div class="mb-2">
                                                <label for="widget_gallery_slide_cols" class="form-label">가로 개수</label>
                                                <select class="form-select" id="widget_gallery_slide_cols" name="gallery_slide_cols">
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3" selected>3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">슬라이드 방향</label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="gallery_slide_direction" id="gallery_direction_left" value="left" checked>
                                                    <label class="btn btn-outline-primary" for="gallery_direction_left">
                                                        <i class="bi bi-arrow-left"></i> 좌
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="gallery_slide_direction" id="gallery_direction_right" value="right">
                                                    <label class="btn btn-outline-primary" for="gallery_direction_right">
                                                        <i class="bi bi-arrow-right"></i> 우
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3" id="widget_gallery_show_title_container" style="display: none;">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="widget_gallery_show_title" 
                                                       name="gallery_show_title" 
                                                       checked>
                                                <label class="form-check-label" for="widget_gallery_show_title">
                                                    제목 표시
                                                </label>
                                            </div>
                                            <small class="text-muted">썸네일 이미지 하단에 게시글 제목을 표시합니다.</small>
                                        </div>
                                        <div class="mb-3" id="widget_custom_html_container" style="display: none;">
                                            <label for="widget_custom_html" class="form-label">HTML 코드</label>
                                            <textarea class="form-control" 
                                                      id="widget_custom_html" 
                                                      name="custom_html" 
                                                      rows="10"
                                                      placeholder="<style><script><html> 코드를 입력하세요"></textarea>
                                            <small class="text-muted">위젯에 표시할 HTML 코드를 입력하세요.</small>
                                        </div>
                                        <div class="mb-3" id="widget_block_container" style="display: none;">
                                            <div class="mb-3">
                                                <label for="widget_block_title" class="form-label">제목</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="widget_block_title" 
                                                       name="block_title" 
                                                       placeholder="제목을 입력하세요">
                                            </div>
                                            <div class="mb-3">
                                                <label for="widget_block_content" class="form-label">내용</label>
                                                <textarea class="form-control" 
                                                          id="widget_block_content" 
                                                          name="block_content" 
                                                          rows="3"
                                                          placeholder="내용을 입력하세요"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">텍스트 정렬</label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="block_text_align" id="block_align_left" value="left" checked>
                                                    <label class="btn btn-outline-primary" for="block_align_left">
                                                        <i class="bi bi-text-left"></i> 좌
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="block_text_align" id="block_align_center" value="center">
                                                    <label class="btn btn-outline-primary" for="block_align_center">
                                                        <i class="bi bi-text-center"></i> 중앙
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="block_text_align" id="block_align_right" value="right">
                                                    <label class="btn btn-outline-primary" for="block_align_right">
                                                        <i class="bi bi-text-right"></i> 우
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="widget_block_title_font_size" class="form-label">제목 폰트 사이즈 (px)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="widget_block_title_font_size" 
                                                       name="block_title_font_size" 
                                                       value="16"
                                                       min="8"
                                                       max="72"
                                                       step="1"
                                                       placeholder="16">
                                                <small class="text-muted">기본값: 16px</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="widget_block_content_font_size" class="form-label">내용 폰트 사이즈 (px)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="widget_block_content_font_size" 
                                                       name="block_content_font_size" 
                                                       value="14"
                                                       min="8"
                                                       max="48"
                                                       step="1"
                                                       placeholder="14">
                                                <small class="text-muted">기본값: 14px</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="widget_block_background_type" class="form-label">배경</label>
                                                <select class="form-select" id="widget_block_background_type" name="block_background_type" onchange="handleBlockBackgroundTypeChange()">
                                                    <option value="none">배경 없음</option>
                                                    <option value="color">컬러</option>
                                                    <option value="gradient">그라데이션</option>
                                                    <option value="image">이미지</option>
                                                </select>
                                            </div>
                                            <div class="mb-3" id="widget_block_color_container">
                                                <label for="widget_block_background_color" class="form-label">적용 컬러</label>
                                                <input type="color" 
                                                       class="form-control form-control-color" 
                                                       id="widget_block_background_color" 
                                                       name="block_background_color" 
                                                       value="#007bff">
                                            </div>
                                            <div class="mb-3" id="widget_block_gradient_container" style="display: none;">
                                                <label class="form-label">그라데이션 설정</label>
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <div id="widget_block_gradient_preview" 
                                                         style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #ffffff, #000000);"
                                                         onclick="openBlockGradientModal('widget_block')"
                                                         title="그라데이션 설정">
                                                    </div>
                                                    <input type="hidden" 
                                                           id="widget_block_gradient_start"
                                                           name="block_background_gradient_start" 
                                                           value="#ffffff">
                                                    <input type="hidden" 
                                                           id="widget_block_gradient_end"
                                                           name="block_background_gradient_end" 
                                                           value="#000000">
                                                    <input type="hidden" 
                                                           id="widget_block_gradient_angle"
                                                           name="block_background_gradient_angle" 
                                                           value="90">
                                                </div>
                                                <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                                            </div>
                                            <div class="mb-3" id="widget_block_image_container" style="display: none;">
                                                <label class="form-label">배경 이미지</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-secondary" 
                                                            id="widget_block_image_btn"
                                                            onclick="document.getElementById('widget_block_image_input').click()">
                                                        <i class="bi bi-image"></i> 이미지 선택
                                                    </button>
                                                    <input type="file" 
                                                           id="widget_block_image_input" 
                                                           name="block_background_image" 
                                                           accept="image/*" 
                                                           style="display: none;"
                                                           onchange="handleBlockImageChange(this)">
                                                    <input type="hidden" id="widget_block_background_image" name="block_background_image_url">
                                                    <div id="widget_block_image_preview" style="display: none;">
                                                        <img id="widget_block_image_preview_img" src="" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                                        <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeBlockImage()">삭제</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="widget_block_padding_top" class="form-label">상하 여백</label>
                                                <select class="form-select" id="widget_block_padding_top" name="block_padding_top">
                                                    <option value="0">0px</option>
                                                    <option value="10">10px</option>
                                                    <option value="20" selected>20px</option>
                                                    <option value="30">30px</option>
                                                    <option value="40">40px</option>
                                                    <option value="50">50px</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="widget_block_padding_left" class="form-label">좌우 여백</label>
                                                <select class="form-select" id="widget_block_padding_left" name="block_padding_left">
                                                    <option value="0">0px</option>
                                                    <option value="10">10px</option>
                                                    <option value="20" selected>20px</option>
                                                    <option value="30">30px</option>
                                                    <option value="40">40px</option>
                                                    <option value="50">50px</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="widget_block_show_button" 
                                                           name="block_show_button"
                                                           onchange="handleSidebarBlockButtonToggle()">
                                                    <label class="form-check-label" for="widget_block_show_button">
                                                        버튼 추가
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3" id="widget_block_button_container" style="display: none;">
                                                <div class="mb-3">
                                                    <label for="widget_block_button_text" class="form-label">버튼 텍스트</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="widget_block_button_text" 
                                                           name="block_button_text" 
                                                           placeholder="버튼 텍스트를 입력하세요">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="widget_block_button_background_color" class="form-label">버튼 배경 컬러</label>
                                                    <input type="color" 
                                                           class="form-control form-control-color" 
                                                           id="widget_block_button_background_color" 
                                                           name="block_button_background_color" 
                                                           value="#007bff">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="widget_block_button_text_color" class="form-label">버튼 텍스트 컬러</label>
                                                    <input type="color" 
                                                           class="form-control form-control-color" 
                                                           id="widget_block_button_text_color" 
                                                           name="block_button_text_color" 
                                                           value="#ffffff">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="widget_block_link" class="form-label">
                                                    연결 링크 <small class="text-muted">(선택사항)</small>
                                                    <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="버튼이 있는 경우 버튼에 링크가 연결되고, 버튼이 없는 경우 블록 전체에 링크가 연결됩니다."></i>
                                                </label>
                                                <input type="url" 
                                                       class="form-control" 
                                                       id="widget_block_link" 
                                                       name="block_link" 
                                                       placeholder="https://example.com">
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="widget_block_open_new_tab" 
                                                           name="block_open_new_tab">
                                                    <label class="form-check-label" for="widget_block_open_new_tab">
                                                        새창에서 열기
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3" id="widget_block_slide_container" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label">슬라이드 방향</label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="block_slide_direction" id="block_slide_direction_left" value="left" checked>
                                                    <label class="btn btn-outline-primary" for="block_slide_direction_left">
                                                        <i class="bi bi-arrow-left"></i> 좌
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="block_slide_direction" id="block_slide_direction_right" value="right">
                                                    <label class="btn btn-outline-primary" for="block_slide_direction_right">
                                                        <i class="bi bi-arrow-right"></i> 우
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="block_slide_direction" id="block_slide_direction_up" value="up">
                                                    <label class="btn btn-outline-primary" for="block_slide_direction_up">
                                                        <i class="bi bi-arrow-up"></i> 상
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="block_slide_direction" id="block_slide_direction_down" value="down">
                                                    <label class="btn btn-outline-primary" for="block_slide_direction_down">
                                                        <i class="bi bi-arrow-down"></i> 하
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="widget_block_slide_items">
                                                <!-- 블록 아이템들이 여기에 동적으로 추가됨 -->
                                            </div>
                                            <button type="button" class="btn btn-primary w-100" onclick="addBlockSlideItem()">
                                                <i class="bi bi-plus-circle me-2"></i>블록 추가하기
                                            </button>
                                        </div>
                                        <div class="mb-3" id="widget_image_container" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label">이미지 선택</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" 
                                                            class="btn btn-outline-secondary" 
                                                            id="widget_image_btn"
                                                            onclick="document.getElementById('widget_image_input').click()">
                                                        <i class="bi bi-image"></i> 이미지 선택
                                                    </button>
                                                    <input type="file" 
                                                           id="widget_image_input" 
                                                           name="image_file" 
                                                           accept="image/*" 
                                                           style="display: none;"
                                                           onchange="handleImageChange(this)">
                                                    <input type="hidden" id="widget_image_url" name="image_url">
                                                    <div id="widget_image_preview" style="display: none;">
                                                        <img id="widget_image_preview_img" src="" alt="미리보기" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                                                        <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeImage()">삭제</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="widget_image_link" class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
                                                <input type="url" 
                                                       class="form-control" 
                                                       id="widget_image_link" 
                                                       name="image_link" 
                                                       placeholder="https://example.com">
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="widget_image_open_new_tab" 
                                                           name="image_open_new_tab">
                                                    <label class="form-check-label" for="widget_image_open_new_tab">
                                                        새창에서 열기
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3" id="widget_image_slide_container" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label">슬라이드 방향</label>
                                                <div class="btn-group w-100" role="group" id="image_slide_direction_group">
                                                    <input type="radio" class="btn-check" name="image_slide_direction" id="image_slide_direction_left" value="left" checked>
                                                    <label class="btn btn-outline-primary" for="image_slide_direction_left">
                                                        <i class="bi bi-arrow-left"></i> 좌
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="image_slide_direction" id="image_slide_direction_right" value="right">
                                                    <label class="btn btn-outline-primary" for="image_slide_direction_right">
                                                        <i class="bi bi-arrow-right"></i> 우
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="image_slide_direction" id="image_slide_direction_up" value="up">
                                                    <label class="btn btn-outline-primary" for="image_slide_direction_up">
                                                        <i class="bi bi-arrow-up"></i> 상
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="image_slide_direction" id="image_slide_direction_down" value="down">
                                                    <label class="btn btn-outline-primary" for="image_slide_direction_down">
                                                        <i class="bi bi-arrow-down"></i> 하
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="widget_image_slide_single" 
                                                           name="image_slide_single"
                                                           checked
                                                           onchange="handleImageSlideModeChange()">
                                                    <label class="form-check-label" for="widget_image_slide_single">
                                                        1단 슬라이드
                                                    </label>
                                                    <i class="bi bi-question-circle text-muted ms-2" 
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="top" 
                                                       title="3초마다 이미지가 1개씩 슬라이드됩니다." 
                                                       style="cursor: help; font-size: 0.9rem;"></i>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="widget_image_slide_infinite" 
                                                           name="image_slide_infinite"
                                                           onchange="handleImageSlideModeChange()">
                                                    <label class="form-check-label" for="widget_image_slide_infinite">
                                                        무한루프 슬라이드
                                                    </label>
                                                    <i class="bi bi-question-circle text-muted ms-2" 
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="top" 
                                                       title="이미지가 좌우 방향으로 무한히 흘러가는 슬라이드입니다. 한번에 표시할 이미지 수를 지정할 수 있습니다." 
                                                       style="cursor: help; font-size: 0.9rem;"></i>
                                                </div>
                                            </div>
                                            <div class="mb-3" id="widget_image_slide_visible_count_container" style="display: none;">
                                                <label for="widget_image_slide_visible_count" class="form-label">표시할 이미지 수 (PC)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="widget_image_slide_visible_count" 
                                                       name="image_slide_visible_count" 
                                                       min="1" 
                                                       max="10" 
                                                       value="3"
                                                       placeholder="3">
                                                <small class="text-muted">PC에서 한번에 표시할 이미지 개수를 입력하세요 (1~10).</small>
                                            </div>
                                            <div class="mb-3" id="widget_image_slide_visible_count_mobile_container" style="display: none;">
                                                <label for="widget_image_slide_visible_count_mobile" class="form-label">표시할 이미지 수 (모바일)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="widget_image_slide_visible_count_mobile" 
                                                       name="image_slide_visible_count_mobile" 
                                                       min="1" 
                                                       max="10" 
                                                       value="2"
                                                       placeholder="2">
                                                <small class="text-muted">모바일에서 한번에 표시할 이미지 개수를 입력하세요 (1~10).</small>
                                            </div>
                                            <div class="mb-3" id="widget_image_slide_gap_container" style="display: none;">
                                                <label for="widget_image_slide_gap" class="form-label">이미지 간격 (px)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="widget_image_slide_gap" 
                                                       name="image_slide_gap" 
                                                       min="0" 
                                                       max="50" 
                                                       value="0"
                                                       placeholder="0">
                                                <small class="text-muted">이미지 사이 간격을 픽셀 단위로 입력하세요 (0~50).</small>
                                            </div>
                                            <div id="widget_image_slide_items">
                                                <!-- 이미지 아이템들이 여기에 동적으로 추가됨 -->
                                            </div>
                                            <button type="button" class="btn btn-primary w-100" onclick="addImageSlideItem()">
                                                <i class="bi bi-plus-circle me-2"></i>이미지 추가하기
                                            </button>
                                        </div>
                                        <div class="mb-3" id="widget_limit_container" style="display: none;">
                                            <label for="widget_limit" class="form-label">표시할 게시글 수</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="widget_limit" 
                                                   name="limit" 
                                                   min="1" 
                                                   max="50" 
                                                   value="10"
                                                   placeholder="게시글 수를 입력하세요">
                                            <small class="text-muted">1~50 사이의 숫자를 입력하세요.</small>
                                        </div>
                                        <div class="mb-3" id="widget_ranking_container" style="display: none;">
                                            <label class="form-label">랭킹 설정</label>
                                            <div class="mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="widget_rank_ranking" 
                                                           name="enable_rank_ranking">
                                                    <label class="form-check-label" for="widget_rank_ranking">
                                                        등급 랭킹
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="widget_point_ranking" 
                                                           name="enable_point_ranking">
                                                    <label class="form-check-label" for="widget_point_ranking">
                                                        포인트 랭킹
                                                    </label>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="widget_ranking_limit" class="form-label">표시할 순위 수</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="widget_ranking_limit" 
                                                       name="ranking_limit" 
                                                       min="1" 
                                                       max="50" 
                                                       value="5"
                                                       placeholder="순위 수를 입력하세요">
                                                <small class="text-muted">1~50 사이의 숫자를 입력하세요.</small>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-plus-circle me-2"></i>추가
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="button" class="btn btn-primary" onclick="saveSidebarWidgetSettings()">
                    <i class="bi bi-save me-2"></i>저장
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 사이드 위젯 애니메이션 설정 모달 -->
<div class="modal fade" id="sidebarWidgetAnimationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">위젯 애니메이션 설정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="sidebarWidgetAnimationForm">
                    <input type="hidden" id="sidebar_widget_animation_id" name="widget_id">
                    <div class="mb-3">
                        <label class="form-label">
                            애니메이션 방향
                            <i class="bi bi-question-circle text-muted ms-1" 
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="위젯이 화면에 나타날 때의 애니메이션 방향을 선택합니다. 스크롤하거나 페이지를 새로고침할 때 적용됩니다."></i>
                        </label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="left"
                                    onclick="selectSidebarAnimationDirection('left', this)">
                                <i class="bi bi-arrow-left"></i> 좌
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="right"
                                    onclick="selectSidebarAnimationDirection('right', this)">
                                <i class="bi bi-arrow-right"></i> 우
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="up"
                                    onclick="selectSidebarAnimationDirection('up', this)">
                                <i class="bi bi-arrow-up"></i> 상
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary animation-direction-btn" 
                                    data-direction="down"
                                    onclick="selectSidebarAnimationDirection('down', this)">
                                <i class="bi bi-arrow-down"></i> 하
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary animation-direction-btn" 
                                    data-direction="none"
                                    onclick="selectSidebarAnimationDirection('none', this)">
                                없음
                            </button>
                        </div>
                        <input type="hidden" id="sidebar_widget_animation_direction" name="animation_direction" value="none">
                    </div>
                    <div class="mb-3">
                        <label for="sidebar_widget_animation_delay" class="form-label">
                            애니메이션 지연 시간 (초)
                            <i class="bi bi-question-circle text-muted ms-1" 
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="애니메이션이 시작되기 전 대기 시간을 초 단위로 설정합니다. 예: 0.5초, 1초, 1.5초 등"></i>
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="sidebar_widget_animation_delay" 
                               name="animation_delay" 
                               value="0" 
                               min="0" 
                               step="0.1"
                               placeholder="0">
                        <small class="text-muted">0 이상의 숫자를 입력하세요 (예: 0, 0.5, 1, 1.5)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveSidebarWidgetAnimation()">저장</button>
            </div>
        </div>
    </div>
</div>

<!-- 위젯 설정 모달 -->
<div class="modal fade" id="widgetSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">위젯 설정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editWidgetForm">
                    <input type="hidden" id="edit_widget_id" name="id">
                    <div class="mb-3" id="edit_widget_board_container" style="display: none;">
                        <label for="edit_widget_board_id" class="form-label">게시판 선택</label>
                        <select class="form-select" id="edit_widget_board_id" name="board_id">
                            <option value="">선택하세요</option>
                            @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                                <option value="{{ $board->id }}">{{ $board->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="edit_widget_sort_order_container" style="display: none;">
                        <label for="edit_widget_sort_order" class="form-label">표시 방식</label>
                        <select class="form-select" id="edit_widget_sort_order" name="sort_order">
                            <option value="latest">최신순</option>
                            <option value="oldest">예전순</option>
                            <option value="random">랜덤</option>
                            <option value="popular">인기순</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_widget_marquee_direction_container" style="display: none;">
                        <label class="form-label">전광판 표시 방향</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="edit_direction" id="edit_direction_left" value="left">
                            <label class="btn btn-outline-primary" for="edit_direction_left">
                                <i class="bi bi-arrow-left"></i> 좌
                            </label>
                            
                            <input type="radio" class="btn-check" name="edit_direction" id="edit_direction_right" value="right">
                            <label class="btn btn-outline-primary" for="edit_direction_right">
                                <i class="bi bi-arrow-right"></i> 우
                            </label>
                            
                            <input type="radio" class="btn-check" name="edit_direction" id="edit_direction_up" value="up">
                            <label class="btn btn-outline-primary" for="edit_direction_up">
                                <i class="bi bi-arrow-up"></i> 상
                            </label>
                            
                            <input type="radio" class="btn-check" name="edit_direction" id="edit_direction_down" value="down">
                            <label class="btn btn-outline-primary" for="edit_direction_down">
                                <i class="bi bi-arrow-down"></i> 하
                            </label>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_widget_gallery_container" style="display: none;">
                        <label for="edit_widget_gallery_board_id" class="form-label">
                            게시판 선택
                            <i class="bi bi-question-circle text-muted ms-1" 
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="사진형 게시판, 북마크 게시판, 블로그 게시판만 선택 가능합니다."></i>
                        </label>
                        <select class="form-select" id="edit_widget_gallery_board_id" name="gallery_board_id">
                            <option value="">선택하세요</option>
                            @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                                @if(in_array($board->type, ['photo', 'bookmark', 'blog']))
                                    <option value="{{ $board->id }}">{{ $board->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="edit_widget_gallery_display_type_container" style="display: none;">
                        <label for="edit_widget_gallery_display_type" class="form-label">표시 방식</label>
                        <select class="form-select" id="edit_widget_gallery_display_type" name="gallery_display_type" onchange="handleEditGalleryDisplayTypeChange()">
                            <option value="grid">일반</option>
                            <option value="slide">슬라이드</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_widget_gallery_grid_container" style="display: none;">
                        <div class="row">
                            <div class="col-6">
                                <label for="edit_widget_gallery_cols" class="form-label">가로 개수</label>
                                <select class="form-select" id="edit_widget_gallery_cols" name="gallery_cols">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="edit_widget_gallery_rows" class="form-label">세로 줄수</label>
                                <select class="form-select" id="edit_widget_gallery_rows" name="gallery_rows">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_widget_gallery_slide_container" style="display: none;">
                        <div class="mb-2">
                            <label for="edit_widget_gallery_slide_cols" class="form-label">가로 개수</label>
                            <select class="form-select" id="edit_widget_gallery_slide_cols" name="gallery_slide_cols">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">슬라이드 방향</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="edit_gallery_slide_direction" id="edit_gallery_direction_left" value="left">
                                <label class="btn btn-outline-primary" for="edit_gallery_direction_left">
                                    <i class="bi bi-arrow-left"></i> 좌
                                </label>
                                
                                <input type="radio" class="btn-check" name="edit_gallery_slide_direction" id="edit_gallery_direction_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_gallery_direction_right">
                                    <i class="bi bi-arrow-right"></i> 우
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_widget_gallery_show_title_container" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="edit_widget_gallery_show_title" 
                                   name="gallery_show_title">
                            <label class="form-check-label" for="edit_widget_gallery_show_title">
                                제목 표시
                            </label>
                        </div>
                        <small class="text-muted">썸네일 이미지 하단에 게시글 제목을 표시합니다.</small>
                    </div>
                    <div class="mb-3" id="edit_widget_custom_html_container" style="display: none;">
                        <label for="edit_widget_custom_html" class="form-label">HTML 코드</label>
                        <textarea class="form-control" 
                                  id="edit_widget_custom_html" 
                                  name="custom_html" 
                                  rows="10"
                                  placeholder="<style><script><html> 코드를 입력하세요"></textarea>
                        <small class="text-muted">위젯에 표시할 HTML 코드를 입력하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_widget_block_container" style="display: none;">
                        <div class="mb-3">
                            <label for="edit_widget_block_title" class="form-label">제목</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_widget_block_title" 
                                   name="block_title" 
                                   placeholder="제목을 입력하세요">
                        </div>
                        <div class="mb-3">
                            <label for="edit_widget_block_content" class="form-label">내용</label>
                            <textarea class="form-control" 
                                      id="edit_widget_block_content" 
                                      name="block_content" 
                                      rows="3"
                                      placeholder="내용을 입력하세요"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">텍스트 정렬</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="edit_block_text_align" id="edit_block_align_left" value="left" checked>
                                <label class="btn btn-outline-primary" for="edit_block_align_left">
                                    <i class="bi bi-text-left"></i> 좌
                                </label>
                                
                                <input type="radio" class="btn-check" name="edit_block_text_align" id="edit_block_align_center" value="center">
                                <label class="btn btn-outline-primary" for="edit_block_align_center">
                                    <i class="bi bi-text-center"></i> 중앙
                                </label>
                                
                                <input type="radio" class="btn-check" name="edit_block_text_align" id="edit_block_align_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_block_align_right">
                                    <i class="bi bi-text-right"></i> 우
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_widget_block_title_font_size" class="form-label">제목 폰트 사이즈 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_widget_block_title_font_size" 
                                   name="block_title_font_size" 
                                   value="16"
                                   min="8"
                                   max="72"
                                   step="1"
                                   placeholder="16">
                            <small class="text-muted">기본값: 16px</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_widget_block_content_font_size" class="form-label">내용 폰트 사이즈 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_widget_block_content_font_size" 
                                   name="block_content_font_size" 
                                   value="14"
                                   min="8"
                                   max="48"
                                   step="1"
                                   placeholder="14">
                            <small class="text-muted">기본값: 14px</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_widget_block_background_type" class="form-label">배경</label>
                            <select class="form-select" id="edit_widget_block_background_type" name="block_background_type" onchange="handleEditBlockBackgroundTypeChange()">
                                <option value="none">배경 없음</option>
                                <option value="color">컬러</option>
                                <option value="gradient">그라데이션</option>
                                <option value="image">이미지</option>
                            </select>
                        </div>
                        <div class="mb-3" id="edit_widget_block_color_container">
                            <label for="edit_widget_block_background_color" class="form-label">적용 컬러</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_widget_block_background_color" 
                                   name="block_background_color" 
                                   value="#007bff">
                        </div>
                        <div class="mb-3" id="edit_widget_block_gradient_container" style="display: none;">
                            <label class="form-label">그라데이션 설정</label>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div id="edit_widget_block_gradient_preview" 
                                     style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #ffffff, #000000);"
                                     onclick="openBlockGradientModal('edit_widget_block')"
                                     title="그라데이션 설정">
                                </div>
                                <input type="hidden" 
                                       id="edit_widget_block_gradient_start"
                                       name="block_background_gradient_start" 
                                       value="#ffffff">
                                <input type="hidden" 
                                       id="edit_widget_block_gradient_end"
                                       name="block_background_gradient_end" 
                                       value="#000000">
                                <input type="hidden" 
                                       id="edit_widget_block_gradient_angle"
                                       name="block_background_gradient_angle" 
                                       value="90">
                            </div>
                            <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
                        </div>
                        <div class="mb-3" id="edit_widget_block_image_container" style="display: none;">
                            <label class="form-label">배경 이미지</label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="edit_widget_block_image_btn"
                                        onclick="document.getElementById('edit_widget_block_image_input').click()">
                                    <i class="bi bi-image"></i> 이미지 선택
                                </button>
                                <input type="file" 
                                       id="edit_widget_block_image_input" 
                                       name="block_background_image" 
                                       accept="image/*" 
                                       style="display: none;"
                                       onchange="handleEditBlockImageChange(this)">
                                <input type="hidden" id="edit_widget_block_background_image" name="block_background_image_url">
                                <div id="edit_widget_block_image_preview" style="display: none;">
                                    <img id="edit_widget_block_image_preview_img" src="" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditBlockImage()">삭제</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_widget_block_font_color" class="form-label">폰트 컬러</label>
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   id="edit_widget_block_font_color" 
                                   name="block_font_color" 
                                   value="#ffffff">
                        </div>
                        <div class="mb-3">
                            <label for="edit_widget_block_padding_top" class="form-label">상하 여백</label>
                            <select class="form-select" id="edit_widget_block_padding_top" name="block_padding_top">
                                <option value="0">0px</option>
                                <option value="10">10px</option>
                                <option value="20" selected>20px</option>
                                <option value="30">30px</option>
                                <option value="40">40px</option>
                                <option value="50">50px</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_widget_block_padding_left" class="form-label">좌우 여백</label>
                            <select class="form-select" id="edit_widget_block_padding_left" name="block_padding_left">
                                <option value="0">0px</option>
                                <option value="10">10px</option>
                                <option value="20" selected>20px</option>
                                <option value="30">30px</option>
                                <option value="40">40px</option>
                                <option value="50">50px</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_widget_block_show_button" 
                                       name="block_show_button"
                                       onchange="handleEditSidebarBlockButtonToggle()">
                                <label class="form-check-label" for="edit_widget_block_show_button">
                                    버튼 추가
                                </label>
                            </div>
                        </div>
                        <div class="mb-3" id="edit_widget_block_button_container" style="display: none;">
                            <div class="mb-3">
                                <label for="edit_widget_block_button_text" class="form-label">버튼 텍스트</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_widget_block_button_text" 
                                       name="block_button_text" 
                                       placeholder="버튼 텍스트를 입력하세요">
                            </div>
                            <div class="mb-3">
                                <label for="edit_widget_block_button_background_color" class="form-label">버튼 배경 컬러</label>
                                <input type="color" 
                                       class="form-control form-control-color" 
                                       id="edit_widget_block_button_background_color" 
                                       name="block_button_background_color" 
                                       value="#007bff">
                            </div>
                            <div class="mb-3">
                                <label for="edit_widget_block_button_text_color" class="form-label">버튼 텍스트 컬러</label>
                                <input type="color" 
                                       class="form-control form-control-color" 
                                       id="edit_widget_block_button_text_color" 
                                       name="block_button_text_color" 
                                       value="#ffffff">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_widget_block_link" class="form-label">
                                연결 링크 <small class="text-muted">(선택사항)</small>
                                <i class="bi bi-question-circle help-icon ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="버튼이 있는 경우 버튼에 링크가 연결되고, 버튼이 없는 경우 블록 전체에 링크가 연결됩니다."></i>
                            </label>
                            <input type="url" 
                                   class="form-control" 
                                   id="edit_widget_block_link" 
                                   name="block_link" 
                                   placeholder="https://example.com">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_widget_block_open_new_tab" 
                                       name="block_open_new_tab">
                                <label class="form-check-label" for="edit_widget_block_open_new_tab">
                                    새창에서 열기
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_widget_block_slide_container" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">슬라이드 방향</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="edit_block_slide_direction" id="edit_block_slide_direction_left" value="left" checked>
                                <label class="btn btn-outline-primary" for="edit_block_slide_direction_left">
                                    <i class="bi bi-arrow-left"></i> 좌
                                </label>
                                
                                <input type="radio" class="btn-check" name="edit_block_slide_direction" id="edit_block_slide_direction_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_block_slide_direction_right">
                                    <i class="bi bi-arrow-right"></i> 우
                                </label>
                                
                                <input type="radio" class="btn-check" name="edit_block_slide_direction" id="edit_block_slide_direction_up" value="up">
                                <label class="btn btn-outline-primary" for="edit_block_slide_direction_up">
                                    <i class="bi bi-arrow-up"></i> 상
                                </label>
                                
                                <input type="radio" class="btn-check" name="edit_block_slide_direction" id="edit_block_slide_direction_down" value="down">
                                <label class="btn btn-outline-primary" for="edit_block_slide_direction_down">
                                    <i class="bi bi-arrow-down"></i> 하
                                </label>
                            </div>
                        </div>
                        <div id="edit_widget_block_slide_items">
                            <!-- 블록 아이템들이 여기에 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="addEditBlockSlideItem()">
                            <i class="bi bi-plus-circle me-2"></i>블록 추가하기
                        </button>
                    </div>
                    <div class="mb-3" id="edit_widget_image_container" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">이미지 선택</label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="edit_widget_image_btn"
                                        onclick="document.getElementById('edit_widget_image_input').click()">
                                    <i class="bi bi-image"></i> 이미지 선택
                                </button>
                                <input type="file" 
                                       id="edit_widget_image_input" 
                                       name="image_file" 
                                       accept="image/*" 
                                       style="display: none;"
                                       onchange="handleEditImageChange(this)">
                                <input type="hidden" id="edit_widget_image_url" name="image_url">
                                <div id="edit_widget_image_preview" style="display: none;">
                                    <img id="edit_widget_image_preview_img" src="" alt="미리보기" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditImage()">삭제</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_widget_image_link" class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
                            <input type="url" 
                                   class="form-control" 
                                   id="edit_widget_image_link" 
                                   name="image_link" 
                                   placeholder="https://example.com">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_widget_image_open_new_tab" 
                                       name="image_open_new_tab">
                                <label class="form-check-label" for="edit_widget_image_open_new_tab">
                                    새창에서 열기
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_widget_image_slide_container" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">슬라이드 방향</label>
                            <div class="btn-group w-100" role="group" id="edit_image_slide_direction_group">
                                <input type="radio" class="btn-check" name="edit_image_slide_direction" id="edit_image_slide_direction_left" value="left" checked>
                                <label class="btn btn-outline-primary" for="edit_image_slide_direction_left">
                                    <i class="bi bi-arrow-left"></i> 좌
                                </label>
                                
                                <input type="radio" class="btn-check" name="edit_image_slide_direction" id="edit_image_slide_direction_right" value="right">
                                <label class="btn btn-outline-primary" for="edit_image_slide_direction_right">
                                    <i class="bi bi-arrow-right"></i> 우
                                </label>
                                
                                <input type="radio" class="btn-check" name="edit_image_slide_direction" id="edit_image_slide_direction_up" value="up">
                                <label class="btn btn-outline-primary" for="edit_image_slide_direction_up">
                                    <i class="bi bi-arrow-up"></i> 상
                                </label>
                                
                                <input type="radio" class="btn-check" name="edit_image_slide_direction" id="edit_image_slide_direction_down" value="down">
                                <label class="btn btn-outline-primary" for="edit_image_slide_direction_down">
                                    <i class="bi bi-arrow-down"></i> 하
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_widget_image_slide_single" 
                                       name="edit_image_slide_single"
                                       checked
                                       onchange="handleEditImageSlideModeChange()">
                                <label class="form-check-label" for="edit_widget_image_slide_single">
                                    1단 슬라이드
                                </label>
                                <i class="bi bi-question-circle text-muted ms-2" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="3초마다 이미지가 1개씩 슬라이드됩니다." 
                                   style="cursor: help; font-size: 0.9rem;"></i>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_widget_image_slide_infinite" 
                                       name="edit_image_slide_infinite"
                                       onchange="handleEditImageSlideModeChange()">
                                <label class="form-check-label" for="edit_widget_image_slide_infinite">
                                    무한루프 슬라이드
                                </label>
                                <i class="bi bi-question-circle text-muted ms-2" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="이미지가 좌우 방향으로 무한히 흘러가는 슬라이드입니다. 한번에 표시할 이미지 수를 지정할 수 있습니다." 
                                   style="cursor: help; font-size: 0.9rem;"></i>
                            </div>
                        </div>
                        <div class="mb-3" id="edit_widget_image_slide_visible_count_container" style="display: none;">
                            <label for="edit_widget_image_slide_visible_count" class="form-label">표시할 이미지 수 (PC)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_widget_image_slide_visible_count" 
                                   name="edit_image_slide_visible_count" 
                                   min="1" 
                                   max="10" 
                                   value="3"
                                   placeholder="3">
                            <small class="text-muted">PC에서 한번에 표시할 이미지 개수를 입력하세요 (1~10).</small>
                        </div>
                        <div class="mb-3" id="edit_widget_image_slide_visible_count_mobile_container" style="display: none;">
                            <label for="edit_widget_image_slide_visible_count_mobile" class="form-label">표시할 이미지 수 (모바일)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_widget_image_slide_visible_count_mobile" 
                                   name="edit_image_slide_visible_count_mobile" 
                                   min="1" 
                                   max="10" 
                                   value="2"
                                   placeholder="2">
                            <small class="text-muted">모바일에서 한번에 표시할 이미지 개수를 입력하세요 (1~10).</small>
                        </div>
                        <div class="mb-3" id="edit_widget_image_slide_gap_container" style="display: none;">
                            <label for="edit_widget_image_slide_gap" class="form-label">이미지 간격 (px)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_widget_image_slide_gap" 
                                   name="edit_image_slide_gap" 
                                   min="0" 
                                   max="50" 
                                   value="0"
                                   placeholder="0">
                            <small class="text-muted">이미지 사이 간격을 픽셀 단위로 입력하세요 (0~50).</small>
                        </div>
                        <div id="edit_widget_image_slide_items">
                            <!-- 이미지 아이템들이 여기에 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="addEditImageSlideItem()">
                            <i class="bi bi-plus-circle me-2"></i>이미지 추가하기
                        </button>
                    </div>
                    <div class="mb-3" id="edit_widget_limit_container" style="display: none;">
                        <label for="edit_widget_limit" class="form-label">표시할 게시글 수</label>
                        <input type="number" 
                               class="form-control" 
                               id="edit_widget_limit" 
                               name="limit" 
                               min="1" 
                               max="50" 
                               value="10"
                               placeholder="게시글 수를 입력하세요">
                        <small class="text-muted">1~50 사이의 숫자를 입력하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_widget_ranking_container" style="display: none;">
                        <label class="form-label">랭킹 설정</label>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_widget_rank_ranking" 
                                       name="enable_rank_ranking">
                                <label class="form-check-label" for="edit_widget_rank_ranking">
                                    등급 랭킹
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="edit_widget_point_ranking" 
                                       name="enable_point_ranking">
                                <label class="form-check-label" for="edit_widget_point_ranking">
                                    포인트 랭킹
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="edit_widget_ranking_limit" class="form-label">표시할 순위 수</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_widget_ranking_limit" 
                                   name="ranking_limit" 
                                   min="1" 
                                   max="50" 
                                   value="5"
                                   placeholder="순위 수를 입력하세요">
                            <small class="text-muted">1~50 사이의 숫자를 입력하세요.</small>
                        </div>
                    </div>
                    <div class="mb-3" id="edit_widget_tab_menu_container" style="display: none;">
                        <label class="form-label">탭메뉴 설정</label>
                        <div id="edit_tab_menu_list">
                            <!-- 탭메뉴 항목들이 여기에 동적으로 추가됨 -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addEditTabMenuItem()">
                            <i class="bi bi-plus-circle me-1"></i>탭메뉴 추가
                        </button>
                    </div>
                    <div class="mb-3" id="edit_widget_toggle_menu_container" style="display: none;">
                        <label for="edit_widget_toggle_menu_id" class="form-label">토글 메뉴 선택</label>
                        <select class="form-select" id="edit_widget_toggle_menu_id" name="toggle_menu_id">
                            <option value="">선택하세요</option>
                            <!-- 토글 메뉴 옵션들이 여기에 동적으로 추가됨 -->
                        </select>
                        <small class="text-muted">표시할 토글 메뉴를 선택하세요.</small>
                    </div>
                    <div class="mb-3" id="edit_widget_title_container_main">
                        <label for="edit_widget_title" class="form-label">
                            위젯 제목
                            <i class="bi bi-question-circle text-muted ms-1" 
                               id="edit_widget_title_help"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="제목을 입력하지 않으면 위젯 제목이 표시되지 않습니다."></i>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_widget_title" 
                               name="title" 
                               placeholder="위젯 제목을 입력하세요">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="edit_widget_is_active" 
                                   name="is_active">
                            <label class="form-check-label" for="edit_widget_is_active">
                                활성화
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveWidgetSettings()">저장</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let sortable = null;
let currentEditWidgetId = null;

function saveSidebarWidgetSettings() {
    // 사이드바 로그인 위젯 활성화 저장
    const enabled = document.getElementById('enable_sidebar_login_widget').checked;
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>저장 중...';
    
    fetch('{{ route("admin.sidebar-widgets", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            enable_sidebar_login_widget: enabled
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 성공 알림 표시
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>설정이 저장되었습니다.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.page-header').after(alertDiv);
            
            // 3초 후 알림 자동 제거
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        } else {
            alert('설정 저장에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
        btn.disabled = false;
        btn.innerHTML = originalText;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('설정 저장 중 오류가 발생했습니다.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// 갤러리 표시 방식 변경 핸들러 (위젯 추가 폼용)
function handleGalleryDisplayTypeChange() {
    const displayTypeSelect = document.getElementById('widget_gallery_display_type');
    const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
    const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
    
    if (!displayTypeSelect) return;
    
    if (displayTypeSelect.value === 'grid') {
        if (galleryGridContainer) galleryGridContainer.style.display = 'block';
        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
    } else {
        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
        if (gallerySlideContainer) gallerySlideContainer.style.display = 'block';
    }
}

// 갤러리 표시 방식 변경 핸들러 (위젯 수정 모달용)
function handleEditGalleryDisplayTypeChange() {
    const displayTypeSelect = document.getElementById('edit_widget_gallery_display_type');
    const galleryGridContainer = document.getElementById('edit_widget_gallery_grid_container');
    const gallerySlideContainer = document.getElementById('edit_widget_gallery_slide_container');
    
    if (!displayTypeSelect) return;
    
    if (displayTypeSelect.value === 'grid') {
        if (galleryGridContainer) galleryGridContainer.style.display = 'block';
        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
    } else {
        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
        if (gallerySlideContainer) gallerySlideContainer.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 사이드바 모바일 표시 드롭다운 변경 시 저장
    const sidebarMobileDisplay = document.getElementById('sidebar_mobile_display');
    if (sidebarMobileDisplay) {
        sidebarMobileDisplay.addEventListener('change', function() {
            const displayValue = this.value;
            
            fetch('{{ route("admin.sidebar-widgets", ["site" => $site->slug]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'sidebar_mobile_display=' + encodeURIComponent(displayValue)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 성공 알림 표시
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="bi bi-check-circle me-2"></i>설정이 저장되었습니다.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.page-header').after(alertDiv);
                    
                    // 3초 후 알림 자동 제거
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 3000);
                } else {
                    console.error('설정 저장 실패:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
    
    // 드래그 앤 드롭 초기화
    const widgetList = document.getElementById('widgetList');
    if (widgetList) {
        sortable = Sortable.create(widgetList, {
            handle: '.bi-grip-vertical',
            animation: 150,
            onEnd: function(evt) {
                saveWidgetOrder();
            }
        });
    }

    // 위젯 추가 폼
    document.getElementById('addWidgetForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addWidget();
    });
    
    // 위젯 타입 변경 시 게시글 수 입력 필드 표시/숨김
    const widgetTypeSelect = document.getElementById('widget_type');
    if (widgetTypeSelect) {
        widgetTypeSelect.addEventListener('change', function() {
            const widgetType = this.value;
        const selectedOption = this.options[this.selectedIndex];
        const description = selectedOption.dataset.description;
        const descriptionDiv = document.getElementById('widget_type_description');
        const descriptionText = document.getElementById('widget_type_description_text');
        
        // 설명 표시/숨김
        if (description && descriptionDiv) {
            descriptionText.textContent = description;
            descriptionDiv.style.display = 'block';
        } else if (descriptionDiv) {
            descriptionDiv.style.display = 'none';
        }
        
        const limitContainer = document.getElementById('widget_limit_container');
        const rankingContainer = document.getElementById('widget_ranking_container');
        const titleContainer = document.getElementById('widget_title_container');
        const titleInput = document.getElementById('widget_title');
        
        const boardContainer = document.getElementById('widget_board_container');
        const sortOrderContainer = document.getElementById('widget_sort_order_container');
        const marqueeDirectionContainer = document.getElementById('widget_marquee_direction_container');
        
        // 모든 위젯 타입 컨테이너 참조
        let blockContainer = document.getElementById('widget_block_container');
        let blockSlideContainer = document.getElementById('widget_block_slide_container');
        let imageContainer = document.getElementById('widget_image_container');
        let imageSlideContainer = document.getElementById('widget_image_slide_container');
        
        if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
            if (limitContainer) limitContainer.style.display = 'block';
            if (rankingContainer) rankingContainer.style.display = 'none';
            if (boardContainer) boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            // 갤러리 관련 컨테이너 숨김
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            // 커스텀 HTML 컨테이너 숨김
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            // 블록 및 이미지 컨테이너 숨김
            if (blockContainer) blockContainer.style.display = 'none';
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            if (imageContainer) imageContainer.style.display = 'none';
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            if (titleContainer) titleContainer.style.display = 'block';
            if (titleInput) titleInput.required = true;
        } else if (widgetType === 'board') {
            limitContainer.style.display = 'block';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'block';
            if (sortOrderContainer) sortOrderContainer.style.display = 'block';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            // 갤러리 관련 컨테이너 숨김
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            // 커스텀 HTML 컨테이너 숨김
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            // 블록 및 이미지 컨테이너 숨김
            if (blockContainer) blockContainer.style.display = 'none';
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            if (imageContainer) imageContainer.style.display = 'none';
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'block';
            titleInput.required = true;
        } else if (widgetType === 'marquee_board') {
            limitContainer.style.display = 'block';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'block';
            if (sortOrderContainer) sortOrderContainer.style.display = 'block';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'block';
            // 갤러리 관련 컨테이너 숨김
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            // 커스텀 HTML 컨테이너 숨김
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            // 블록 및 이미지 컨테이너 숨김
            if (blockContainer) blockContainer.style.display = 'none';
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            if (imageContainer) imageContainer.style.display = 'none';
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'none';
            titleInput.required = false;
            // 전광판 위젯은 기본 제목 설정
            titleInput.value = '게시글 전광판';
        } else if (widgetType === 'gallery') {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'block';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'block';
            if (galleryGridContainer) galleryGridContainer.style.display = 'block';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'block';
            // 커스텀 HTML 컨테이너 숨김
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            titleContainer.style.display = 'block';
            titleInput.required = false;
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'inline';
            // Bootstrap tooltip 초기화
            if (titleHelp && typeof bootstrap !== 'undefined') {
                const tooltipElement = titleHelp.querySelector('[data-bs-toggle="tooltip"]');
                if (tooltipElement) {
                    new bootstrap.Tooltip(tooltipElement);
                }
            }
            // 표시 방식 드롭다운 초기화 및 초기 상태 설정
            const displayTypeSelect = document.getElementById('widget_gallery_display_type');
            if (displayTypeSelect) {
                // 기본값을 'grid'로 설정
                displayTypeSelect.value = 'grid';
                // 초기 상태 설정
                if (galleryGridContainer) galleryGridContainer.style.display = 'block';
                if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            }
        } else if (widgetType === 'tab_menu') {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            // 갤러리 관련 컨테이너 숨김
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            // 커스텀 HTML 컨테이너 숨김
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            // 블록 및 이미지 컨테이너 숨김
            if (blockContainer) blockContainer.style.display = 'none';
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            if (imageContainer) imageContainer.style.display = 'none';
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'none';
            titleInput.required = false;
            // 탭메뉴 위젯은 기본 제목 설정
            titleInput.value = '탭메뉴';
            const toggleMenuContainer = document.getElementById('edit_widget_toggle_menu_container');
            if (toggleMenuContainer) toggleMenuContainer.style.display = 'none';
        } else if (widgetType === 'toggle_menu') {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            // 갤러리 관련 컨테이너 숨김
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            // 커스텀 HTML 컨테이너 숨김
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            // 블록 및 이미지 컨테이너 숨김
            if (blockContainer) blockContainer.style.display = 'none';
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            if (imageContainer) imageContainer.style.display = 'none';
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const tabMenuContainer = document.getElementById('edit_widget_tab_menu_container');
            if (tabMenuContainer) tabMenuContainer.style.display = 'none';
            const toggleMenuContainer = document.getElementById('edit_widget_toggle_menu_container');
            if (toggleMenuContainer) toggleMenuContainer.style.display = 'block';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'none';
            titleInput.required = false;
            // 토글 메뉴 위젯은 기본 제목 설정
            titleInput.value = '토글 메뉴';
        } else if (widgetType === 'user_ranking') {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'block';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            // 갤러리 관련 컨테이너 숨김
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            // 커스텀 HTML 컨테이너 숨김
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            // 블록 및 이미지 컨테이너 숨김
            if (blockContainer) blockContainer.style.display = 'none';
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            if (imageContainer) imageContainer.style.display = 'none';
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'none';
            titleInput.required = false;
            // 회원 랭킹 위젯은 기본 제목 설정
            titleInput.value = '회원 랭킹';
        } else if (widgetType === 'custom_html') {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'block';
            const blockContainer = document.getElementById('widget_block_container');
            if (blockContainer) blockContainer.style.display = 'none';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'block';
            titleInput.required = true;
        } else if (widgetType === 'block') {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            blockContainer = document.getElementById('widget_block_container');
            if (blockContainer) blockContainer.style.display = 'block';
            blockSlideContainer = document.getElementById('widget_block_slide_container');
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            imageContainer = document.getElementById('widget_image_container');
            if (imageContainer) imageContainer.style.display = 'none';
            imageSlideContainer = document.getElementById('widget_image_slide_container');
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'none';
            titleInput.required = false;
        } else if (widgetType === 'block_slide') {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            blockContainer = document.getElementById('widget_block_container');
            if (blockContainer) blockContainer.style.display = 'none';
            blockSlideContainer = document.getElementById('widget_block_slide_container');
            if (blockSlideContainer) blockSlideContainer.style.display = 'block';
            imageContainer = document.getElementById('widget_image_container');
            if (imageContainer) imageContainer.style.display = 'none';
            imageSlideContainer = document.getElementById('widget_image_slide_container');
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'none';
            titleInput.required = false;
            
            // 첫 번째 블록 아이템 자동 추가
            let itemsContainer = document.getElementById('widget_block_slide_items');
            if (itemsContainer && itemsContainer.children.length === 0) {
                addBlockSlideItem();
            }
        } else if (widgetType === 'image') {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            const blockContainer = document.getElementById('widget_block_container');
            if (blockContainer) blockContainer.style.display = 'none';
            const blockSlideContainer = document.getElementById('widget_block_slide_container');
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            const imageContainer = document.getElementById('widget_image_container');
            if (imageContainer) imageContainer.style.display = 'block';
            imageSlideContainer = document.getElementById('widget_image_slide_container');
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'none';
            titleInput.required = false;
        } else if (widgetType === 'image_slide') {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            imageSlideContainer = document.getElementById('widget_image_slide_container');
            if (imageSlideContainer) imageSlideContainer.style.display = 'block';
            let itemsContainer = document.getElementById('widget_image_slide_items');
            if (itemsContainer && itemsContainer.children.length === 0) {
                addImageSlideItem();
            }
            handleImageSlideModeChange();
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            blockContainer = document.getElementById('widget_block_container');
            if (blockContainer) blockContainer.style.display = 'none';
            blockSlideContainer = document.getElementById('widget_block_slide_container');
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            imageContainer = document.getElementById('widget_image_container');
            if (imageContainer) imageContainer.style.display = 'none';
            imageSlideContainer = document.getElementById('widget_image_slide_container');
            if (imageSlideContainer) imageSlideContainer.style.display = 'block';
            const titleHelp = document.getElementById('widget_title_help');
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'none';
            titleInput.required = false;
            
            // 첫 번째 이미지 아이템 자동 추가
            itemsContainer = document.getElementById('widget_image_slide_items');
            if (itemsContainer && itemsContainer.children.length === 0) {
                addImageSlideItem();
            }
        } else {
            limitContainer.style.display = 'none';
            rankingContainer.style.display = 'none';
            boardContainer.style.display = 'none';
            if (sortOrderContainer) sortOrderContainer.style.display = 'none';
            if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
            const galleryContainer = document.getElementById('widget_gallery_container');
            const galleryDisplayTypeContainer = document.getElementById('widget_gallery_display_type_container');
            const galleryGridContainer = document.getElementById('widget_gallery_grid_container');
            const gallerySlideContainer = document.getElementById('widget_gallery_slide_container');
            const galleryShowTitleContainer = document.getElementById('widget_gallery_show_title_container');
            if (galleryContainer) galleryContainer.style.display = 'none';
            if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
            if (galleryGridContainer) galleryGridContainer.style.display = 'none';
            if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
            if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
            const customHtmlContainer = document.getElementById('widget_custom_html_container');
            if (customHtmlContainer) customHtmlContainer.style.display = 'none';
            // 블록 및 이미지 컨테이너 숨김
            if (blockContainer) blockContainer.style.display = 'none';
            if (blockSlideContainer) blockSlideContainer.style.display = 'none';
            if (imageContainer) imageContainer.style.display = 'none';
            if (imageSlideContainer) imageSlideContainer.style.display = 'none';
            if (titleHelp) titleHelp.style.display = 'none';
            titleContainer.style.display = 'block';
            titleInput.required = true;
            // 갤러리 관련 필드 초기화
            const displayTypeSelect = document.getElementById('widget_gallery_display_type');
            if (displayTypeSelect) {
                displayTypeSelect.value = 'grid';
            }
        }
        });
    }
});

// 블록 배경 타입 변경 핸들러
function handleBlockBackgroundTypeChange() {
    const backgroundType = document.getElementById('widget_block_background_type').value;
    const colorContainer = document.getElementById('widget_block_color_container');
    const gradientContainer = document.getElementById('widget_block_gradient_container');
    const imageContainer = document.getElementById('widget_block_image_container');
    
    if (backgroundType === 'none') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'block';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'block';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'image') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'block';
    }
}

// 블록 이미지 변경 핸들러
function handleBlockImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('widget_block_image_preview');
            const previewImg = document.getElementById('widget_block_image_preview_img');
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// 블록 이미지 삭제
function removeBlockImage() {
    const input = document.getElementById('widget_block_image_input');
    const preview = document.getElementById('widget_block_image_preview');
    const imageUrl = document.getElementById('widget_block_background_image');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

// 편집 모달용 블록 배경 타입 변경 핸들러
function handleEditBlockBackgroundTypeChange() {
    const backgroundType = document.getElementById('edit_widget_block_background_type').value;
    const colorContainer = document.getElementById('edit_widget_block_color_container');
    const gradientContainer = document.getElementById('edit_widget_block_gradient_container');
    const imageContainer = document.getElementById('edit_widget_block_image_container');
    
    if (backgroundType === 'none') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'block';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'block';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'image') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'block';
    }
}

// 편집 모달용 블록 이미지 변경 핸들러
function handleEditBlockImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_widget_block_image_preview');
            const previewImg = document.getElementById('edit_widget_block_image_preview_img');
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// 편집 모달용 블록 이미지 삭제
function removeEditBlockImage() {
    const input = document.getElementById('edit_widget_block_image_input');
    const preview = document.getElementById('edit_widget_block_image_preview');
    const imageUrl = document.getElementById('edit_widget_block_background_image');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

let blockSlideItemIndex = 0;

function addBlockSlideItem() {
    const container = document.getElementById('widget_block_slide_items');
    if (!container) return;
    
    // 기존 블록들 접기
    const existingItems = container.querySelectorAll('.block-slide-item');
    existingItems.forEach((existingItem) => {
        const existingItemIndex = existingItem.dataset.itemIndex;
        const existingBody = document.getElementById(`block_slide_item_${existingItemIndex}_body`);
        const existingIcon = document.getElementById(`block_slide_item_${existingItemIndex}_icon`);
        if (existingBody && existingIcon) {
            existingBody.style.display = 'none';
            existingIcon.className = 'bi bi-chevron-right';
        }
    });
    
    const itemIndex = blockSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 block-slide-item';
    item.id = `block_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    // 접기/펼치기 헤더
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleBlockSlideItem(itemIndex);
    };
    header.innerHTML = `
        <span>블록 ${itemIndex + 1}</span>
        <i class="bi bi-chevron-down" id="block_slide_item_${itemIndex}_icon"></i>
    `;
    
    // 내용 영역
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `block_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3">
            <label class="form-label">제목</label>
            <input type="text" 
                   class="form-control block-slide-title" 
                   name="block_slide[${itemIndex}][title]" 
                   placeholder="제목을 입력하세요">
        </div>
        <div class="mb-3">
            <label class="form-label">내용</label>
            <textarea class="form-control block-slide-content" 
                      name="block_slide[${itemIndex}][content]" 
                      rows="3"
                      placeholder="내용을 입력하세요"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">텍스트 정렬</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="block_slide[${itemIndex}][text_align]" id="block_slide_${itemIndex}_align_left" value="left" checked>
                <label class="btn btn-outline-primary" for="block_slide_${itemIndex}_align_left">
                    <i class="bi bi-text-left"></i> 좌
                </label>
                
                <input type="radio" class="btn-check" name="block_slide[${itemIndex}][text_align]" id="block_slide_${itemIndex}_align_center" value="center">
                <label class="btn btn-outline-primary" for="block_slide_${itemIndex}_align_center">
                    <i class="bi bi-text-center"></i> 중앙
                </label>
                
                <input type="radio" class="btn-check" name="block_slide[${itemIndex}][text_align]" id="block_slide_${itemIndex}_align_right" value="right">
                <label class="btn btn-outline-primary" for="block_slide_${itemIndex}_align_right">
                    <i class="bi bi-text-right"></i> 우
                </label>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">제목 폰트 사이즈 (px)</label>
            <input type="number" 
                   class="form-control block-slide-title-font-size" 
                   name="block_slide[${itemIndex}][title_font_size]" 
                   value="16"
                   min="8"
                   max="72"
                   step="1"
                   placeholder="16">
            <small class="text-muted">기본값: 16px</small>
        </div>
        <div class="mb-3">
            <label class="form-label">내용 폰트 사이즈 (px)</label>
            <input type="number" 
                   class="form-control block-slide-content-font-size" 
                   name="block_slide[${itemIndex}][content_font_size]" 
                   value="14"
                   min="8"
                   max="48"
                   step="1"
                   placeholder="14">
            <small class="text-muted">기본값: 14px</small>
        </div>
        <div class="mb-3">
            <label class="form-label">배경</label>
            <select class="form-select block-slide-background-type" name="block_slide[${itemIndex}][background_type]" onchange="handleBlockSlideBackgroundTypeChange(${itemIndex})">
                <option value="color">컬러</option>
                <option value="gradient">그라데이션</option>
                <option value="image">이미지</option>
            </select>
        </div>
        <div class="mb-3 block-slide-color-container" id="block_slide_${itemIndex}_color_container">
            <label class="form-label">배경 컬러</label>
            <input type="color" 
                   class="form-control form-control-color block-slide-background-color" 
                   name="block_slide[${itemIndex}][background_color]" 
                   value="#007bff">
        </div>
        <div class="mb-3 block-slide-gradient-container" id="block_slide_${itemIndex}_gradient_container" style="display: none;">
            <label class="form-label">그라데이션 설정</label>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div id="block_slide_${itemIndex}_gradient_preview" 
                     style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #ffffff, #000000);"
                     onclick="openBlockGradientModal('block_slide_${itemIndex}')"
                     title="그라데이션 설정">
                </div>
                <input type="hidden" 
                       class="block-slide-background-gradient-start" 
                       name="block_slide[${itemIndex}][background_gradient_start]" 
                       id="block_slide_${itemIndex}_gradient_start"
                       value="#ffffff">
                <input type="hidden" 
                       class="block-slide-background-gradient-end" 
                       name="block_slide[${itemIndex}][background_gradient_end]" 
                       id="block_slide_${itemIndex}_gradient_end"
                       value="#000000">
                <input type="hidden" 
                       class="block-slide-background-gradient-angle" 
                       name="block_slide[${itemIndex}][background_gradient_angle]" 
                       id="block_slide_${itemIndex}_gradient_angle"
                       value="90">
            </div>
            <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
        </div>
        <div class="mb-3">
            <label class="form-label">폰트 컬러</label>
            <input type="color" 
                   class="form-control form-control-color block-slide-font-color" 
                   name="block_slide[${itemIndex}][font_color]" 
                   value="#ffffff">
        </div>
        <div class="mb-3 block-slide-image-container" id="block_slide_${itemIndex}_image_container" style="display: none;">
            <label class="form-label">배경 이미지</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" 
                        class="btn btn-outline-secondary" 
                        onclick="document.getElementById('block_slide_${itemIndex}_image_input').click()">
                    <i class="bi bi-image"></i> 이미지 선택
                </button>
                <input type="file" 
                       id="block_slide_${itemIndex}_image_input" 
                       name="block_slide[${itemIndex}][background_image]" 
                       accept="image/*" 
                       style="display: none;"
                       onchange="handleBlockSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="block-slide-background-image-url" name="block_slide[${itemIndex}][background_image_url]" id="block_slide_${itemIndex}_background_image_url">
                <div class="block-slide-image-preview" id="block_slide_${itemIndex}_image_preview" style="display: none;">
                    <img id="block_slide_${itemIndex}_image_preview_img" src="" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeBlockSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">상하 여백</label>
            <select class="form-select block-slide-padding-top" name="block_slide[${itemIndex}][padding_top]">
                <option value="0">0px</option>
                <option value="10">10px</option>
                <option value="20" selected>20px</option>
                <option value="30">30px</option>
                <option value="40">40px</option>
                <option value="50">50px</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">좌우 여백</label>
            <select class="form-select block-slide-padding-left" name="block_slide[${itemIndex}][padding_left]">
                <option value="0">0px</option>
                <option value="10">10px</option>
                <option value="20" selected>20px</option>
                <option value="30">30px</option>
                <option value="40">40px</option>
                <option value="50">50px</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">연결 링크 <small class="text-muted">(선택사항)</small></label>
            <input type="url" 
                   class="form-control block-slide-link" 
                   name="block_slide[${itemIndex}][link]" 
                   placeholder="https://example.com">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input block-slide-open-new-tab" 
                       type="checkbox" 
                       name="block_slide[${itemIndex}][open_new_tab]"
                       id="block_slide_${itemIndex}_open_new_tab">
                <label class="form-check-label" for="block_slide_${itemIndex}_open_new_tab">
                    새창에서 열기
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeBlockSlideItem(${itemIndex})">
            <i class="bi bi-trash me-1"></i>삭제
        </button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
    
    // 새로 추가된 아이템은 펼쳐진 상태로 시작
    body.style.display = 'block';
}

function toggleBlockSlideItem(itemIndex) {
    const body = document.getElementById(`block_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`block_slide_item_${itemIndex}_icon`);
    
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

function removeBlockSlideItem(itemIndex) {
    const item = document.getElementById(`block_slide_item_${itemIndex}`);
    if (item) {
        item.remove();
    }
}

function handleBlockSlideBackgroundTypeChange(itemIndex) {
    const backgroundType = document.querySelector(`#block_slide_item_${itemIndex}_body .block-slide-background-type`).value;
    const colorContainer = document.getElementById(`block_slide_${itemIndex}_color_container`);
    const gradientContainer = document.getElementById(`block_slide_${itemIndex}_gradient_container`);
    const imageContainer = document.getElementById(`block_slide_${itemIndex}_image_container`);
    
    if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'block';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'block';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'image') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'block';
    }
}

function handleBlockSlideImageChange(itemIndex, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`block_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`block_slide_${itemIndex}_image_preview_img`);
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeBlockSlideImage(itemIndex) {
    const input = document.getElementById(`block_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`block_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`block_slide_${itemIndex}_background_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

// 이미지 위젯 함수들
function handleImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('widget_image_preview');
            const previewImg = document.getElementById('widget_image_preview_img');
            const imageUrl = document.getElementById('widget_image_url');
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const input = document.getElementById('widget_image_input');
    const preview = document.getElementById('widget_image_preview');
    const imageUrl = document.getElementById('widget_image_url');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

// 이미지 슬라이드 위젯 함수들
let imageSlideItemIndex = 0;

function addImageSlideItem() {
    const container = document.getElementById('widget_image_slide_items');
    if (!container) return;
    
    // 기존 이미지들 접기
    const existingItems = container.querySelectorAll('.image-slide-item');
    existingItems.forEach((existingItem) => {
        const existingItemIndex = existingItem.dataset.itemIndex;
        const existingBody = document.getElementById(`image_slide_item_${existingItemIndex}_body`);
        const existingIcon = document.getElementById(`image_slide_item_${existingItemIndex}_icon`);
        if (existingBody && existingIcon) {
            existingBody.style.display = 'none';
            existingIcon.className = 'bi bi-chevron-right';
        }
    });
    
    const itemIndex = imageSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 image-slide-item';
    item.id = `image_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    // 접기/펼치기 헤더
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleImageSlideItem(itemIndex);
    };
    header.innerHTML = `
        <span>이미지 ${itemIndex + 1}</span>
        <i class="bi bi-chevron-down" id="image_slide_item_${itemIndex}_icon"></i>
    `;
    
    // 내용 영역
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `image_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3">
            <label class="form-label">이미지 선택</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" 
                        class="btn btn-outline-secondary" 
                        onclick="document.getElementById('image_slide_${itemIndex}_image_input').click()">
                    <i class="bi bi-image"></i> 이미지 선택
                </button>
                <input type="file" 
                       id="image_slide_${itemIndex}_image_input" 
                       name="image_slide[${itemIndex}][image_file]" 
                       accept="image/*" 
                       style="display: none;"
                       onchange="handleImageSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="image-slide-image-url" name="image_slide[${itemIndex}][image_url]" id="image_slide_${itemIndex}_image_url">
                <div class="image-slide-image-preview" id="image_slide_${itemIndex}_image_preview" style="display: none;">
                    <img id="image_slide_${itemIndex}_image_preview_img" src="" alt="미리보기" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeImageSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
            <input type="url" 
                   class="form-control image-slide-link" 
                   name="image_slide[${itemIndex}][link]" 
                   placeholder="https://example.com">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input image-slide-open-new-tab" 
                       type="checkbox" 
                       name="image_slide[${itemIndex}][open_new_tab]"
                       id="image_slide_${itemIndex}_open_new_tab">
                <label class="form-check-label" for="image_slide_${itemIndex}_open_new_tab">
                    새창에서 열기
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeImageSlideItem(${itemIndex})">
            <i class="bi bi-trash me-1"></i>삭제
        </button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
    
    // 새로 추가된 아이템은 펼쳐진 상태로 시작
    body.style.display = 'block';
}

function toggleImageSlideItem(itemIndex) {
    const body = document.getElementById(`image_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`image_slide_item_${itemIndex}_icon`);
    
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

function removeImageSlideItem(itemIndex) {
    const item = document.getElementById(`image_slide_item_${itemIndex}`);
    if (item) {
        item.remove();
    }
}

function handleImageSlideImageChange(itemIndex, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`image_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`image_slide_${itemIndex}_image_preview_img`);
            const imageUrl = document.getElementById(`image_slide_${itemIndex}_image_url`);
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImageSlideImage(itemIndex) {
    const input = document.getElementById(`image_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`image_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`image_slide_${itemIndex}_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function handleImageSlideModeChange() {
    const singleCheckbox = document.getElementById('widget_image_slide_single');
    const infiniteCheckbox = document.getElementById('widget_image_slide_infinite');
    const visibleCountContainer = document.getElementById('widget_image_slide_visible_count_container');
    const directionGroup = document.getElementById('image_slide_direction_group');
    const upRadio = document.getElementById('image_slide_direction_up');
    const downRadio = document.getElementById('image_slide_direction_down');
    const upLabel = upRadio ? upRadio.nextElementSibling : null;
    const downLabel = downRadio ? downRadio.nextElementSibling : null;
    
    if (infiniteCheckbox && infiniteCheckbox.checked) {
        if (singleCheckbox) singleCheckbox.checked = false;
        if (visibleCountContainer) visibleCountContainer.style.display = 'block';
        const visibleCountMobileContainer = document.getElementById('widget_image_slide_visible_count_mobile_container');
        if (visibleCountMobileContainer) visibleCountMobileContainer.style.display = 'block';
        if (upRadio) {
            upRadio.disabled = true;
            if (upLabel) upLabel.classList.add('disabled');
        }
        if (downRadio) {
            downRadio.disabled = true;
            if (downLabel) downLabel.classList.add('disabled');
        }
        if (upRadio && upRadio.checked) {
            const leftRadio = document.getElementById('image_slide_direction_left');
            if (leftRadio) leftRadio.checked = true;
        }
        if (downRadio && downRadio.checked) {
            const leftRadio = document.getElementById('image_slide_direction_left');
            if (leftRadio) leftRadio.checked = true;
        }
    } else {
        if (visibleCountContainer) visibleCountContainer.style.display = 'none';
        if (upRadio) {
            upRadio.disabled = false;
            if (upLabel) upLabel.classList.remove('disabled');
        }
        if (downRadio) {
            downRadio.disabled = false;
            if (downLabel) downLabel.classList.remove('disabled');
        }
    }
    
    if (singleCheckbox && singleCheckbox.checked) {
        if (infiniteCheckbox) infiniteCheckbox.checked = false;
    }
    
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function handleEditImageSlideModeChange() {
    const singleCheckbox = document.getElementById('edit_widget_image_slide_single');
    const infiniteCheckbox = document.getElementById('edit_widget_image_slide_infinite');
    const visibleCountContainer = document.getElementById('edit_widget_image_slide_visible_count_container');
    const gapContainer = document.getElementById('edit_widget_image_slide_gap_container');
    const directionGroup = document.getElementById('edit_image_slide_direction_group');
    const upRadio = document.getElementById('edit_image_slide_direction_up');
    const downRadio = document.getElementById('edit_image_slide_direction_down');
    const upLabel = upRadio ? upRadio.nextElementSibling : null;
    const downLabel = downRadio ? downRadio.nextElementSibling : null;
    
    if (infiniteCheckbox && infiniteCheckbox.checked) {
        if (singleCheckbox) singleCheckbox.checked = false;
        if (visibleCountContainer) visibleCountContainer.style.display = 'block';
        const visibleCountMobileContainer = document.getElementById('edit_widget_image_slide_visible_count_mobile_container');
        if (visibleCountMobileContainer) visibleCountMobileContainer.style.display = 'block';
        if (gapContainer) gapContainer.style.display = 'block';
        if (upRadio) {
            upRadio.disabled = true;
            if (upLabel) upLabel.classList.add('disabled');
        }
        if (downRadio) {
            downRadio.disabled = true;
            if (downLabel) downLabel.classList.add('disabled');
        }
        if (upRadio && upRadio.checked) {
            const leftRadio = document.getElementById('edit_image_slide_direction_left');
            if (leftRadio) leftRadio.checked = true;
        }
        if (downRadio && downRadio.checked) {
            const leftRadio = document.getElementById('edit_image_slide_direction_left');
            if (leftRadio) leftRadio.checked = true;
        }
    } else {
        if (visibleCountContainer) visibleCountContainer.style.display = 'none';
        if (upRadio) {
            upRadio.disabled = false;
            if (upLabel) upLabel.classList.remove('disabled');
        }
        if (downRadio) {
            downRadio.disabled = false;
            if (downLabel) downLabel.classList.remove('disabled');
        }
    }
    
    if (singleCheckbox && singleCheckbox.checked) {
        if (infiniteCheckbox) infiniteCheckbox.checked = false;
    }
    
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function addEditBlockSlideItem(blockData = null) {
    const container = document.getElementById('edit_widget_block_slide_items');
    if (!container) return;
    
    // 기존 블록들 접기 (새로 추가하는 경우에만)
    if (!blockData) {
        const existingItems = container.querySelectorAll('.edit-block-slide-item');
        existingItems.forEach((existingItem) => {
            const existingItemIndex = existingItem.dataset.itemIndex;
            const existingBody = document.getElementById(`edit_block_slide_item_${existingItemIndex}_body`);
            const existingIcon = document.getElementById(`edit_block_slide_item_${existingItemIndex}_icon`);
            if (existingBody && existingIcon) {
                existingBody.style.display = 'none';
                existingIcon.className = 'bi bi-chevron-right';
            }
        });
    }
    
    const itemIndex = editBlockSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 edit-block-slide-item';
    item.id = `edit_block_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    // 접기/펼치기 헤더
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleEditBlockSlideItem(itemIndex);
    };
    header.innerHTML = `
        <span>블록 ${itemIndex + 1}</span>
        <i class="bi bi-chevron-down" id="edit_block_slide_item_${itemIndex}_icon"></i>
    `;
    
    // 내용 영역
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `edit_block_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3">
            <label class="form-label">제목</label>
            <input type="text" 
                   class="form-control edit-block-slide-title" 
                   name="edit_block_slide[${itemIndex}][title]" 
                   placeholder="제목을 입력하세요"
                   value="${blockData ? (blockData.title || '') : ''}">
        </div>
        <div class="mb-3">
            <label class="form-label">내용</label>
            <textarea class="form-control edit-block-slide-content" 
                      name="edit_block_slide[${itemIndex}][content]" 
                      rows="3"
                      placeholder="내용을 입력하세요">${blockData ? (blockData.content || '') : ''}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">텍스트 정렬</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="edit_block_slide[${itemIndex}][text_align]" id="edit_block_slide_${itemIndex}_align_left" value="left" ${!blockData || blockData.text_align === 'left' ? 'checked' : ''}>
                <label class="btn btn-outline-primary" for="edit_block_slide_${itemIndex}_align_left">
                    <i class="bi bi-text-left"></i> 좌
                </label>
                
                <input type="radio" class="btn-check" name="edit_block_slide[${itemIndex}][text_align]" id="edit_block_slide_${itemIndex}_align_center" value="center" ${blockData && blockData.text_align === 'center' ? 'checked' : ''}>
                <label class="btn btn-outline-primary" for="edit_block_slide_${itemIndex}_align_center">
                    <i class="bi bi-text-center"></i> 중앙
                </label>
                
                <input type="radio" class="btn-check" name="edit_block_slide[${itemIndex}][text_align]" id="edit_block_slide_${itemIndex}_align_right" value="right" ${blockData && blockData.text_align === 'right' ? 'checked' : ''}>
                <label class="btn btn-outline-primary" for="edit_block_slide_${itemIndex}_align_right">
                    <i class="bi bi-text-right"></i> 우
                </label>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">제목 폰트 사이즈 (px)</label>
            <input type="number" 
                   class="form-control edit-block-slide-title-font-size" 
                   name="edit_block_slide[${itemIndex}][title_font_size]" 
                   value="${blockData ? (blockData.title_font_size || '16') : '16'}"
                   min="8"
                   max="72"
                   step="1"
                   placeholder="16">
            <small class="text-muted">기본값: 16px</small>
        </div>
        <div class="mb-3">
            <label class="form-label">내용 폰트 사이즈 (px)</label>
            <input type="number" 
                   class="form-control edit-block-slide-content-font-size" 
                   name="edit_block_slide[${itemIndex}][content_font_size]" 
                   value="${blockData ? (blockData.content_font_size || '14') : '14'}"
                   min="8"
                   max="48"
                   step="1"
                   placeholder="14">
            <small class="text-muted">기본값: 14px</small>
        </div>
        <div class="mb-3">
            <label class="form-label">배경</label>
            <select class="form-select edit-block-slide-background-type" name="edit_block_slide[${itemIndex}][background_type]" onchange="handleEditBlockSlideBackgroundTypeChange(${itemIndex})">
                <option value="color" ${!blockData || blockData.background_type === 'color' ? 'selected' : ''}>컬러</option>
                <option value="gradient" ${blockData && blockData.background_type === 'gradient' ? 'selected' : ''}>그라데이션</option>
                <option value="image" ${blockData && blockData.background_type === 'image' ? 'selected' : ''}>이미지</option>
            </select>
        </div>
        <div class="mb-3 edit-block-slide-color-container" id="edit_block_slide_${itemIndex}_color_container" style="${blockData && (blockData.background_type === 'image' || blockData.background_type === 'gradient') ? 'display: none;' : ''}">
            <label class="form-label">배경 컬러</label>
            <input type="color" 
                   class="form-control form-control-color edit-block-slide-background-color" 
                   name="edit_block_slide[${itemIndex}][background_color]" 
                   value="${blockData ? (blockData.background_color || '#007bff') : '#007bff'}">
        </div>
        <div class="mb-3 edit-block-slide-gradient-container" id="edit_block_slide_${itemIndex}_gradient_container" style="${!blockData || blockData.background_type !== 'gradient' ? 'display: none;' : ''}">
            <label class="form-label">그라데이션 설정</label>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div id="edit_block_slide_${itemIndex}_gradient_preview" 
                     style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(${blockData ? (blockData.background_gradient_angle || 90) : 90}deg, ${blockData ? (blockData.background_gradient_start || '#ffffff') : '#ffffff'}, ${blockData ? (blockData.background_gradient_end || '#000000') : '#000000'});"
                     onclick="openBlockGradientModal('edit_block_slide_${itemIndex}')"
                     title="그라데이션 설정">
                </div>
                <input type="hidden" 
                       class="edit-block-slide-background-gradient-start" 
                       name="edit_block_slide[${itemIndex}][background_gradient_start]" 
                       id="edit_block_slide_${itemIndex}_gradient_start"
                       value="${blockData ? (blockData.background_gradient_start || '#ffffff') : '#ffffff'}">
                <input type="hidden" 
                       class="edit-block-slide-background-gradient-end" 
                       name="edit_block_slide[${itemIndex}][background_gradient_end]" 
                       id="edit_block_slide_${itemIndex}_gradient_end"
                       value="${blockData ? (blockData.background_gradient_end || '#000000') : '#000000'}">
                <input type="hidden" 
                       class="edit-block-slide-background-gradient-angle" 
                       name="edit_block_slide[${itemIndex}][background_gradient_angle]" 
                       id="edit_block_slide_${itemIndex}_gradient_angle"
                       value="${blockData ? (blockData.background_gradient_angle || 90) : 90}">
            </div>
            <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
        </div>
        <div class="mb-3">
            <label class="form-label">폰트 컬러</label>
            <input type="color" 
                   class="form-control form-control-color edit-block-slide-font-color" 
                   name="edit_block_slide[${itemIndex}][font_color]" 
                   value="${blockData ? (blockData.font_color || '#ffffff') : '#ffffff'}">
        </div>
        <div class="mb-3 edit-block-slide-image-container" id="edit_block_slide_${itemIndex}_image_container" style="${!blockData || blockData.background_type !== 'image' ? 'display: none;' : ''}">
            <label class="form-label">배경 이미지</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" 
                        class="btn btn-outline-secondary" 
                        onclick="document.getElementById('edit_block_slide_${itemIndex}_image_input').click()">
                    <i class="bi bi-image"></i> 이미지 선택
                </button>
                <input type="file" 
                       id="edit_block_slide_${itemIndex}_image_input" 
                       name="edit_block_slide[${itemIndex}][background_image]" 
                       accept="image/*" 
                       style="display: none;"
                       onchange="handleEditBlockSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="edit-block-slide-background-image-url" name="edit_block_slide[${itemIndex}][background_image_url]" id="edit_block_slide_${itemIndex}_background_image_url" value="${blockData && blockData.background_image_url ? blockData.background_image_url : ''}">
                <div class="edit-block-slide-image-preview" id="edit_block_slide_${itemIndex}_image_preview" style="${blockData && blockData.background_image_url ? '' : 'display: none;'}">
                    <img id="edit_block_slide_${itemIndex}_image_preview_img" src="${blockData && blockData.background_image_url ? blockData.background_image_url : ''}" alt="미리보기" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditBlockSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">상하 여백</label>
            <select class="form-select edit-block-slide-padding-top" name="edit_block_slide[${itemIndex}][padding_top]">
                <option value="0" ${blockData && blockData.padding_top === 0 ? 'selected' : ''}>0px</option>
                <option value="10" ${blockData && blockData.padding_top === 10 ? 'selected' : ''}>10px</option>
                <option value="20" ${!blockData || blockData.padding_top === 20 ? 'selected' : ''}>20px</option>
                <option value="30" ${blockData && blockData.padding_top === 30 ? 'selected' : ''}>30px</option>
                <option value="40" ${blockData && blockData.padding_top === 40 ? 'selected' : ''}>40px</option>
                <option value="50" ${blockData && blockData.padding_top === 50 ? 'selected' : ''}>50px</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">좌우 여백</label>
            <select class="form-select edit-block-slide-padding-left" name="edit_block_slide[${itemIndex}][padding_left]">
                <option value="0" ${blockData && blockData.padding_left === 0 ? 'selected' : ''}>0px</option>
                <option value="10" ${blockData && blockData.padding_left === 10 ? 'selected' : ''}>10px</option>
                <option value="20" ${!blockData || blockData.padding_left === 20 ? 'selected' : ''}>20px</option>
                <option value="30" ${blockData && blockData.padding_left === 30 ? 'selected' : ''}>30px</option>
                <option value="40" ${blockData && blockData.padding_left === 40 ? 'selected' : ''}>40px</option>
                <option value="50" ${blockData && blockData.padding_left === 50 ? 'selected' : ''}>50px</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">연결 링크 <small class="text-muted">(선택사항)</small></label>
            <input type="url" 
                   class="form-control edit-block-slide-link" 
                   name="edit_block_slide[${itemIndex}][link]" 
                   placeholder="https://example.com"
                   value="${blockData ? (blockData.link || '') : ''}">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input edit-block-slide-open-new-tab" 
                       type="checkbox" 
                       name="edit_block_slide[${itemIndex}][open_new_tab]"
                       id="edit_block_slide_${itemIndex}_open_new_tab"
                       ${blockData && blockData.open_new_tab ? 'checked' : ''}>
                <label class="form-check-label" for="edit_block_slide_${itemIndex}_open_new_tab">
                    새창에서 열기
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeEditBlockSlideItem(${itemIndex})">
            <i class="bi bi-trash me-1"></i>삭제
        </button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
    
    // 새로 추가된 아이템은 펼쳐진 상태로 시작
    body.style.display = 'block';
    
    // 배경 타입에 따라 컨테이너 표시/숨김
    if (blockData && blockData.background_type === 'image') {
        handleEditBlockSlideBackgroundTypeChange(itemIndex);
    }
    
    // 그라데이션 미리보기 업데이트 (RGBA 값 처리)
    if (blockData && blockData.background_type === 'gradient') {
        const gradientPreview = document.getElementById(`edit_block_slide_${itemIndex}_gradient_preview`);
        const gradientStartInput = document.getElementById(`edit_block_slide_${itemIndex}_gradient_start`);
        const gradientEndInput = document.getElementById(`edit_block_slide_${itemIndex}_gradient_end`);
        const gradientAngleInput = document.getElementById(`edit_block_slide_${itemIndex}_gradient_angle`);
        
        if (gradientPreview && gradientStartInput && gradientEndInput && gradientAngleInput) {
            const startValue = gradientStartInput.value;
            const endValue = gradientEndInput.value;
            const angleValue = gradientAngleInput.value || 90;
            gradientPreview.style.background = `linear-gradient(${angleValue}deg, ${startValue}, ${endValue})`;
        }
    }
}

function toggleEditBlockSlideItem(itemIndex) {
    const body = document.getElementById(`edit_block_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`edit_block_slide_item_${itemIndex}_icon`);
    
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

function removeEditBlockSlideItem(itemIndex) {
    const item = document.getElementById(`edit_block_slide_item_${itemIndex}`);
    if (item) {
        item.remove();
    }
}

function handleEditBlockSlideBackgroundTypeChange(itemIndex) {
    const backgroundType = document.querySelector(`#edit_block_slide_item_${itemIndex}_body .edit-block-slide-background-type`).value;
    const colorContainer = document.getElementById(`edit_block_slide_${itemIndex}_color_container`);
    const gradientContainer = document.getElementById(`edit_block_slide_${itemIndex}_gradient_container`);
    const imageContainer = document.getElementById(`edit_block_slide_${itemIndex}_image_container`);
    
    if (backgroundType === 'color') {
        if (colorContainer) colorContainer.style.display = 'block';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'gradient') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'block';
        if (imageContainer) imageContainer.style.display = 'none';
    } else if (backgroundType === 'image') {
        if (colorContainer) colorContainer.style.display = 'none';
        if (gradientContainer) gradientContainer.style.display = 'none';
        if (imageContainer) imageContainer.style.display = 'block';
    }
}

function handleEditBlockSlideImageChange(itemIndex, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`edit_block_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`edit_block_slide_${itemIndex}_image_preview_img`);
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeEditBlockSlideImage(itemIndex) {
    const input = document.getElementById(`edit_block_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`edit_block_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`edit_block_slide_${itemIndex}_background_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

// 편집 모달용 이미지 위젯 함수들
function handleEditImageChange(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_widget_image_preview');
            const previewImg = document.getElementById('edit_widget_image_preview_img');
            const imageUrl = document.getElementById('edit_widget_image_url');
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeEditImage() {
    const input = document.getElementById('edit_widget_image_input');
    const preview = document.getElementById('edit_widget_image_preview');
    const imageUrl = document.getElementById('edit_widget_image_url');
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

// 편집 모달용 이미지 슬라이드 위젯 함수들
let editImageSlideItemIndex = 0;

function addEditImageSlideItem(imageData = null) {
    const container = document.getElementById('edit_widget_image_slide_items');
    if (!container) return;
    
    // 기존 이미지들 접기 (새로 추가하는 경우에만)
    if (!imageData) {
        const existingItems = container.querySelectorAll('.edit-image-slide-item');
        existingItems.forEach((existingItem) => {
            const existingItemIndex = existingItem.dataset.itemIndex;
            const existingBody = document.getElementById(`edit_image_slide_item_${existingItemIndex}_body`);
            const existingIcon = document.getElementById(`edit_image_slide_item_${existingItemIndex}_icon`);
            if (existingBody && existingIcon) {
                existingBody.style.display = 'none';
                existingIcon.className = 'bi bi-chevron-right';
            }
        });
    }
    
    const itemIndex = editImageSlideItemIndex++;
    const item = document.createElement('div');
    item.className = 'card mb-3 edit-image-slide-item';
    item.id = `edit_image_slide_item_${itemIndex}`;
    item.dataset.itemIndex = itemIndex;
    
    // 접기/펼치기 헤더
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleEditImageSlideItem(itemIndex);
    };
    header.innerHTML = `
        <span>이미지 ${itemIndex + 1}</span>
        <i class="bi bi-chevron-down" id="edit_image_slide_item_${itemIndex}_icon"></i>
    `;
    
    // 내용 영역
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `edit_image_slide_item_${itemIndex}_body`;
    body.innerHTML = `
        <div class="mb-3">
            <label class="form-label">이미지 선택</label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" 
                        class="btn btn-outline-secondary" 
                        onclick="document.getElementById('edit_image_slide_${itemIndex}_image_input').click()">
                    <i class="bi bi-image"></i> 이미지 선택
                </button>
                <input type="file" 
                       id="edit_image_slide_${itemIndex}_image_input" 
                       name="edit_image_slide[${itemIndex}][image_file]" 
                       accept="image/*" 
                       style="display: none;"
                       onchange="handleEditImageSlideImageChange(${itemIndex}, this)">
                <input type="hidden" class="edit-image-slide-image-url" name="edit_image_slide[${itemIndex}][image_url]" id="edit_image_slide_${itemIndex}_image_url" value="${imageData && imageData.image_url ? imageData.image_url : ''}">
                <div class="edit-image-slide-image-preview" id="edit_image_slide_${itemIndex}_image_preview" style="${imageData && imageData.image_url ? '' : 'display: none;'}">
                    <img id="edit_image_slide_${itemIndex}_image_preview_img" src="${imageData && imageData.image_url ? imageData.image_url : ''}" alt="미리보기" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeEditImageSlideImage(${itemIndex})">삭제</button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">링크 입력 <small class="text-muted">(선택사항)</small></label>
            <input type="url" 
                   class="form-control edit-image-slide-link" 
                   name="edit_image_slide[${itemIndex}][link]" 
                   placeholder="https://example.com"
                   value="${imageData ? (imageData.link || '') : ''}">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input edit-image-slide-open-new-tab" 
                       type="checkbox" 
                       name="edit_image_slide[${itemIndex}][open_new_tab]"
                       id="edit_image_slide_${itemIndex}_open_new_tab"
                       ${imageData && imageData.open_new_tab ? 'checked' : ''}>
                <label class="form-check-label" for="edit_image_slide_${itemIndex}_open_new_tab">
                    새창에서 열기
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeEditImageSlideItem(${itemIndex})">
            <i class="bi bi-trash me-1"></i>삭제
        </button>
    `;
    
    item.appendChild(header);
    item.appendChild(body);
    container.appendChild(item);
    
    // 새로 추가된 아이템은 펼쳐진 상태로 시작
    body.style.display = 'block';
}

function toggleEditImageSlideItem(itemIndex) {
    const body = document.getElementById(`edit_image_slide_item_${itemIndex}_body`);
    const icon = document.getElementById(`edit_image_slide_item_${itemIndex}_icon`);
    
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

function removeEditImageSlideItem(itemIndex) {
    const item = document.getElementById(`edit_image_slide_item_${itemIndex}`);
    if (item) {
        item.remove();
    }
}

function handleEditImageSlideImageChange(itemIndex, input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`edit_image_slide_${itemIndex}_image_preview`);
            const previewImg = document.getElementById(`edit_image_slide_${itemIndex}_image_preview_img`);
            const imageUrl = document.getElementById(`edit_image_slide_${itemIndex}_image_url`);
            if (preview && previewImg && imageUrl) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                imageUrl.value = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeEditImageSlideImage(itemIndex) {
    const input = document.getElementById(`edit_image_slide_${itemIndex}_image_input`);
    const preview = document.getElementById(`edit_image_slide_${itemIndex}_image_preview`);
    const imageUrl = document.getElementById(`edit_image_slide_${itemIndex}_image_url`);
    if (input) input.value = '';
    if (preview) preview.style.display = 'none';
    if (imageUrl) imageUrl.value = '';
}

function addWidget() {
    const form = document.getElementById('addWidgetForm');
    const formData = new FormData(form);
    
    // settings 객체 생성
    const settings = {};
    const widgetType = formData.get('type');
    if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
        const limit = formData.get('limit');
        if (limit) {
            settings.limit = parseInt(limit);
        }
    } else if (widgetType === 'board') {
        const boardId = formData.get('board_id');
        const limit = formData.get('limit');
        const sortOrder = formData.get('sort_order');
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (limit) {
            settings.limit = parseInt(limit);
        }
        if (sortOrder) {
            settings.sort_order = sortOrder;
        }
    } else if (widgetType === 'marquee_board') {
        // 전광판 위젯은 기본 제목으로 저장
        if (!formData.get('title') || formData.get('title') === '') {
            formData.set('title', '게시글 전광판');
        }
        const boardId = formData.get('board_id');
        const limit = formData.get('limit');
        const sortOrder = formData.get('sort_order');
        const direction = formData.get('direction');
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (limit) {
            settings.limit = parseInt(limit);
        }
        if (sortOrder) {
            settings.sort_order = sortOrder;
        }
        if (direction) {
            settings.direction = direction;
        }
    } else if (widgetType === 'gallery') {
        const boardId = formData.get('gallery_board_id');
        const displayType = formData.get('gallery_display_type');
        const showTitle = document.getElementById('widget_gallery_show_title').checked;
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (displayType) {
            settings.display_type = displayType;
        }
        settings.show_title = showTitle;
        if (displayType === 'grid') {
            const cols = formData.get('gallery_cols');
            const rows = formData.get('gallery_rows');
            if (cols) {
                settings.cols = parseInt(cols);
            }
            if (rows) {
                settings.rows = parseInt(rows);
            }
            settings.limit = parseInt(cols) * parseInt(rows);
        } else if (displayType === 'slide') {
            const slideCols = formData.get('gallery_slide_cols');
            const slideDirectionRadio = document.querySelector('input[name="gallery_slide_direction"]:checked');
            const slideDirection = slideDirectionRadio ? slideDirectionRadio.value : 'left';
            if (slideCols) {
                settings.slide_cols = parseInt(slideCols);
            }
            if (slideDirection) {
                settings.slide_direction = slideDirection;
            }
            settings.limit = 10; // 슬라이드의 경우 기본 10개
        }
    } else if (widgetType === 'tab_menu') {
        // 탭메뉴 위젯은 기본 제목으로 저장 (설정에서 나중에 변경)
        if (!formData.get('title') || formData.get('title') === '') {
            formData.set('title', '탭메뉴');
        }
        // 탭메뉴 설정은 빈 배열로 시작 (설정 모달에서 추가)
        settings.tabs = [];
    } else if (widgetType === 'toggle_menu') {
        // 토글 메뉴 위젯 제목은 사용자가 입력한 값 사용
        // 토글 메뉴 ID 수집
        const toggleMenuSelect = document.getElementById('edit_widget_toggle_menu_id');
        if (toggleMenuSelect && toggleMenuSelect.value) {
            settings.toggle_menu_id = parseInt(toggleMenuSelect.value);
        }
    } else if (widgetType === 'user_ranking') {
        // 회원 랭킹 위젯은 기본 제목으로 저장
        if (!formData.get('title') || formData.get('title') === '') {
            formData.set('title', '회원 랭킹');
        }
        // 회원 랭킹 설정 수집
        settings.enable_rank_ranking = document.getElementById('widget_rank_ranking').checked;
        settings.enable_point_ranking = document.getElementById('widget_point_ranking').checked;
        const rankingLimit = formData.get('ranking_limit');
        if (rankingLimit) {
            settings.ranking_limit = parseInt(rankingLimit);
        }
    } else if (widgetType === 'custom_html') {
        // 커스텀 HTML 설정 수집
        const customHtml = document.getElementById('widget_custom_html').value;
        if (customHtml) {
            settings.html = customHtml;
            settings.custom_html = customHtml;
        }
    } else if (widgetType === 'block') {
        // 블록 위젯 설정 수집
        const blockTitle = formData.get('block_title');
        const blockContent = formData.get('block_content');
        const textAlign = formData.get('block_text_align') || 'left';
        const backgroundType = formData.get('block_background_type') || 'color';
        const paddingTop = formData.get('block_padding_top') || '20';
        const paddingLeft = formData.get('block_padding_left') || '20';
        const blockLink = formData.get('block_link');
        const openNewTab = document.getElementById('widget_block_open_new_tab').checked;
        const titleFontSize = formData.get('block_title_font_size') || '16';
        const contentFontSize = formData.get('block_content_font_size') || '14';
        
        if (blockTitle) {
            settings.block_title = blockTitle;
        }
        if (blockContent) {
            settings.block_content = blockContent;
        }
        settings.text_align = textAlign;
        settings.background_type = backgroundType;
        settings.title_font_size = titleFontSize;
        settings.content_font_size = contentFontSize;
        
        if (backgroundType === 'color') {
            const backgroundColor = formData.get('block_background_color') || '#007bff';
            settings.background_color = backgroundColor;
        } else if (backgroundType === 'gradient') {
            // 두 가지 필드명 모두 확인
            const gradientStart = document.getElementById('widget_block_gradient_start')?.value || 
                                document.getElementById('widget_block_background_gradient_start')?.value || '#ffffff';
            const gradientEnd = document.getElementById('widget_block_gradient_end')?.value || 
                              document.getElementById('widget_block_background_gradient_end')?.value || '#000000';
            const gradientAngle = document.getElementById('widget_block_gradient_angle')?.value || 
                                document.getElementById('widget_block_background_gradient_angle')?.value || 90;
            settings.background_gradient_start = gradientStart;
            settings.background_gradient_end = gradientEnd;
            settings.background_gradient_angle = parseInt(gradientAngle);
        } else if (backgroundType === 'image') {
            const imageFile = document.getElementById('widget_block_image_input').files[0];
            if (imageFile) {
                formData.append('block_background_image_file', imageFile);
            }
            const imageUrl = formData.get('block_background_image_url');
            if (imageUrl) {
                settings.background_image_url = imageUrl;
            }
        }
        
        settings.padding_top = parseInt(paddingTop);
        settings.padding_left = parseInt(paddingLeft);
        
        const fontColor = document.getElementById('widget_block_font_color')?.value || '#ffffff';
        const showButton = document.getElementById('widget_block_show_button')?.checked || false;
        const buttonText = document.getElementById('widget_block_button_text')?.value || '';
        const buttonBackgroundColor = document.getElementById('widget_block_button_background_color')?.value || '#007bff';
        const buttonTextColor = document.getElementById('widget_block_button_text_color')?.value || '#ffffff';
        
        settings.font_color = fontColor;
        settings.show_button = showButton;
        if (showButton) {
            settings.button_text = buttonText;
            settings.button_background_color = buttonBackgroundColor;
            settings.button_text_color = buttonTextColor;
        }
        
        if (blockLink) {
            settings.link = blockLink;
        }
        settings.open_new_tab = openNewTab;
    } else if (widgetType === 'block_slide') {
        // 블록 슬라이드 위젯 설정 수집
        const slideDirection = document.querySelector('input[name="block_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        // 블록 아이템들 수집
        const blockItems = [];
        const blockSlideItems = document.querySelectorAll('.block-slide-item');
        blockSlideItems.forEach((item, index) => {
            const itemIndex = item.dataset.itemIndex;
            const title = item.querySelector('.block-slide-title')?.value || '';
            const content = item.querySelector('.block-slide-content')?.value || '';
            const textAlignRadio = item.querySelector(`input[name="block_slide[${itemIndex}][text_align]"]:checked`);
            const textAlign = textAlignRadio ? textAlignRadio.value : 'left';
            const backgroundType = item.querySelector('.block-slide-background-type')?.value || 'color';
            const paddingTop = item.querySelector('.block-slide-padding-top')?.value || '20';
            const paddingLeft = item.querySelector('.block-slide-padding-left')?.value || '20';
            const link = item.querySelector('.block-slide-link')?.value || '';
            const openNewTab = item.querySelector('.block-slide-open-new-tab')?.checked || false;
            const fontColor = item.querySelector('.block-slide-font-color')?.value || '#ffffff';
            const titleFontSize = item.querySelector('.block-slide-title-font-size')?.value || '16';
            const contentFontSize = item.querySelector('.block-slide-content-font-size')?.value || '14';
            const showButton = item.querySelector('.block-slide-show-button')?.checked || false;
            const buttonText = item.querySelector('.block-slide-button-text')?.value || '';
            const buttonBackgroundColor = item.querySelector('.block-slide-button-background-color')?.value || '#007bff';
            const buttonTextColor = item.querySelector('.block-slide-button-text-color')?.value || '#ffffff';
            
            const blockItem = {
                title: title,
                content: content,
                text_align: textAlign,
                background_type: backgroundType,
                padding_top: parseInt(paddingTop),
                padding_left: parseInt(paddingLeft),
                link: link,
                open_new_tab: openNewTab,
                font_color: fontColor,
                title_font_size: titleFontSize,
                content_font_size: contentFontSize,
                show_button: showButton
            };
            
            if (showButton) {
                blockItem.button_text = buttonText;
                blockItem.button_background_color = buttonBackgroundColor;
                blockItem.button_text_color = buttonTextColor;
            }
            
            if (backgroundType === 'color') {
                const backgroundColor = item.querySelector('.block-slide-background-color')?.value || '#007bff';
                blockItem.background_color = backgroundColor;
            } else if (backgroundType === 'gradient') {
                // hidden input에서 그라데이션 값 가져오기 (두 가지 필드명 모두 확인)
                const gradientStartInput = document.getElementById(`block_slide_${itemIndex}_gradient_start`) || 
                                         document.getElementById(`block_slide_${itemIndex}_background_gradient_start`);
                const gradientEndInput = document.getElementById(`block_slide_${itemIndex}_gradient_end`) || 
                                       document.getElementById(`block_slide_${itemIndex}_background_gradient_end`);
                const gradientAngleInput = document.getElementById(`block_slide_${itemIndex}_gradient_angle`) || 
                                          document.getElementById(`block_slide_${itemIndex}_background_gradient_angle`);
                blockItem.background_gradient_start = gradientStartInput ? gradientStartInput.value : '#ffffff';
                blockItem.background_gradient_end = gradientEndInput ? gradientEndInput.value : '#000000';
                blockItem.background_gradient_angle = gradientAngleInput ? parseInt(gradientAngleInput.value) || 90 : 90;
            } else if (backgroundType === 'image') {
                const imageFile = item.querySelector(`#block_slide_${itemIndex}_image_input`)?.files[0];
                if (imageFile) {
                    formData.append(`block_slide[${itemIndex}][background_image_file]`, imageFile);
                }
                const imageUrl = item.querySelector(`#block_slide_${itemIndex}_background_image_url`)?.value;
                if (imageUrl) {
                    blockItem.background_image_url = imageUrl;
                }
            }
            
            blockItems.push(blockItem);
        });
        
        settings.blocks = blockItems;
        
        // 블록 슬라이드 이미지 파일들을 FormData에 추가
        blockSlideItems.forEach((item, index) => {
            const itemIndex = item.dataset.itemIndex;
            const imageFile = item.querySelector(`#block_slide_${itemIndex}_image_input`)?.files[0];
            if (imageFile) {
                formData.append(`block_slide[${itemIndex}][background_image_file]`, imageFile);
            }
        });
    } else if (widgetType === 'image') {
        // 이미지 위젯 설정 수집
        const imageFile = document.getElementById('widget_image_input')?.files[0];
        if (imageFile) {
            formData.append('image_file', imageFile);
        }
        const imageUrl = document.getElementById('widget_image_url')?.value;
        if (imageUrl) {
            settings.image_url = imageUrl;
        }
        const link = document.getElementById('widget_image_link')?.value;
        if (link) {
            settings.link = link;
        }
        settings.open_new_tab = document.getElementById('widget_image_open_new_tab')?.checked || false;
    } else if (widgetType === 'image_slide') {
        // 이미지 슬라이드 위젯 설정 수집
        const slideDirection = document.querySelector('input[name="image_slide_direction"]:checked')?.value || 'left';
        settings.slide_direction = slideDirection;
        
        const singleSlide = document.getElementById('widget_image_slide_single')?.checked || false;
        const infiniteSlide = document.getElementById('widget_image_slide_infinite')?.checked || false;
        const visibleCount = document.getElementById('widget_image_slide_visible_count')?.value || '3';
        const imageGap = document.getElementById('widget_image_slide_gap')?.value || '0';
        
        settings.slide_mode = infiniteSlide ? 'infinite' : 'single';
        if (infiniteSlide) {
            settings.visible_count = parseInt(visibleCount) || 3;
            const visibleCountMobile = document.getElementById('widget_image_slide_visible_count_mobile')?.value || '2';
            settings.visible_count_mobile = parseInt(visibleCountMobile) || 2;
            settings.image_gap = parseInt(imageGap) || 0;
        }
        
        // 이미지 아이템들 수집
        const imageItems = [];
        const imageSlideItems = document.querySelectorAll('.image-slide-item');
        imageSlideItems.forEach((item, index) => {
            const itemIndex = item.dataset.itemIndex;
            const imageFile = item.querySelector(`#image_slide_${itemIndex}_image_input`)?.files[0];
            const imageUrl = item.querySelector(`#image_slide_${itemIndex}_image_url`)?.value;
            const link = item.querySelector('.image-slide-link')?.value || '';
            const openNewTab = item.querySelector('.image-slide-open-new-tab')?.checked || false;
            
            const imageItem = {
                link: link,
                open_new_tab: openNewTab
            };
            
            if (imageFile) {
                formData.append(`image_slide[${itemIndex}][image_file]`, imageFile);
            }
            if (imageUrl) {
                imageItem.image_url = imageUrl;
            }
            
            imageItems.push(imageItem);
        });
        
        settings.images = imageItems;
    }
    
    // 제목 처리: 갤러리 위젯의 경우 제목이 없으면 빈 문자열로 설정
    const titleInput = document.getElementById('widget_title');
    if (widgetType === 'gallery' && titleInput) {
        const titleValue = titleInput.value.trim();
        if (!titleValue || titleValue === '') {
            formData.set('title', '');
        }
    }
    
    // settings를 JSON으로 추가 (빈 객체가 아닌 경우에만)
    if (Object.keys(settings).length > 0) {
        formData.append('settings', JSON.stringify(settings));
    }
    
    fetch('{{ route("admin.sidebar-widgets.store", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else if (data.limit_exceeded) {
            // 플랜 제한 초과 모달 표시
            if (typeof showPlanLimitModal === 'function') {
                showPlanLimitModal(data.error);
            } else {
                alert(data.error);
            }
        } else {
            alert('위젯 추가에 실패했습니다: ' + (data.message || data.error || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('위젯 추가 중 오류가 발생했습니다.');
    });
}

function editWidget(widgetId) {
    currentEditWidgetId = widgetId;
    
    // 위젯 정보 가져오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (!widgetItem) {
        alert('위젯을 찾을 수 없습니다.');
        return;
    }
    
    const title = widgetItem.dataset.widgetTitle;
    const widgetType = widgetItem.dataset.widgetType;
    const isActive = widgetItem.dataset.widgetActive === '1';
    
    document.getElementById('edit_widget_id').value = widgetId;
    document.getElementById('edit_widget_title').value = title;
    document.getElementById('edit_widget_is_active').checked = isActive;
    
    // 위젯 타입에 따라 게시글 수 입력 필드 표시/숨김
    const limitContainer = document.getElementById('edit_widget_limit_container');
    const tabMenuContainer = document.getElementById('edit_widget_tab_menu_container');
    const rankingContainer = document.getElementById('edit_widget_ranking_container');
    const titleContainer = document.getElementById('edit_widget_title_container_main');
    
    // 위젯 정보를 AJAX로 가져오기
    fetch(`{{ route("admin.sidebar-widgets", ["site" => $site->slug]) }}?widget_id=${widgetId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.widget) {
                    const settings = data.widget.settings || {};
                    
                    const boardContainer = document.getElementById('edit_widget_board_container');
                    const sortOrderContainer = document.getElementById('edit_widget_sort_order_container');
                    const marqueeDirectionContainer = document.getElementById('edit_widget_marquee_direction_container');
                    
                    if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
                        limitContainer.style.display = 'block';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        if (boardContainer) boardContainer.style.display = 'none';
                        if (sortOrderContainer) sortOrderContainer.style.display = 'none';
                        if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
                        titleContainer.style.display = 'block';
                        document.getElementById('edit_widget_limit').value = settings.limit || 10;
                        document.getElementById('edit_widget_title').value = title;
                    } else if (widgetType === 'board') {
                        limitContainer.style.display = 'block';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        if (boardContainer) boardContainer.style.display = 'block';
                        if (sortOrderContainer) sortOrderContainer.style.display = 'block';
                        if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
                        titleContainer.style.display = 'block';
                        document.getElementById('edit_widget_limit').value = settings.limit || 10;
                        if (document.getElementById('edit_widget_board_id')) {
                            document.getElementById('edit_widget_board_id').value = settings.board_id || '';
                        }
                        if (document.getElementById('edit_widget_sort_order')) {
                            document.getElementById('edit_widget_sort_order').value = settings.sort_order || 'latest';
                        }
                        document.getElementById('edit_widget_title').value = title;
                    } else if (widgetType === 'marquee_board') {
                        limitContainer.style.display = 'block';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        if (boardContainer) boardContainer.style.display = 'block';
                        if (sortOrderContainer) sortOrderContainer.style.display = 'block';
                        if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'block';
                        titleContainer.style.display = 'none';
                        document.getElementById('edit_widget_limit').value = settings.limit || 10;
                        if (document.getElementById('edit_widget_board_id')) {
                            document.getElementById('edit_widget_board_id').value = settings.board_id || '';
                        }
                        if (document.getElementById('edit_widget_sort_order')) {
                            document.getElementById('edit_widget_sort_order').value = settings.sort_order || 'latest';
                        }
                        if (marqueeDirectionContainer) {
                            const direction = settings.direction || 'left';
                            const directionRadio = document.getElementById(`edit_direction_${direction}`);
                            if (directionRadio) directionRadio.checked = true;
                        }
                        // 전광판 위젯은 제목 입력 불필요하지만 기본값 설정
                        document.getElementById('edit_widget_title').value = '게시글 전광판';
                    } else if (widgetType === 'gallery') {
                        limitContainer.style.display = 'none';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        if (boardContainer) boardContainer.style.display = 'none';
                        if (sortOrderContainer) sortOrderContainer.style.display = 'none';
                        if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
                        const galleryContainer = document.getElementById('edit_widget_gallery_container');
                        const galleryDisplayTypeContainer = document.getElementById('edit_widget_gallery_display_type_container');
                        const galleryGridContainer = document.getElementById('edit_widget_gallery_grid_container');
                        const gallerySlideContainer = document.getElementById('edit_widget_gallery_slide_container');
                        const galleryShowTitleContainer = document.getElementById('edit_widget_gallery_show_title_container');
                        if (galleryContainer) galleryContainer.style.display = 'block';
                        if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'block';
                        if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'block';
                        titleContainer.style.display = 'block';
                        
                        const displayType = settings.display_type || 'grid';
                        if (document.getElementById('edit_widget_gallery_board_id')) {
                            document.getElementById('edit_widget_gallery_board_id').value = settings.board_id || '';
                        }
                        if (document.getElementById('edit_widget_gallery_display_type')) {
                            document.getElementById('edit_widget_gallery_display_type').value = displayType;
                            // 표시 방식에 따라 컨테이너 표시/숨김
                            if (displayType === 'grid') {
                                if (galleryGridContainer) galleryGridContainer.style.display = 'block';
                                if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                                if (document.getElementById('edit_widget_gallery_cols')) {
                                    document.getElementById('edit_widget_gallery_cols').value = settings.cols || 3;
                                }
                                if (document.getElementById('edit_widget_gallery_rows')) {
                                    document.getElementById('edit_widget_gallery_rows').value = settings.rows || 3;
                                }
                            } else {
                                if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                                if (gallerySlideContainer) gallerySlideContainer.style.display = 'block';
                                if (document.getElementById('edit_widget_gallery_slide_cols')) {
                                    document.getElementById('edit_widget_gallery_slide_cols').value = settings.slide_cols || 3;
                                }
                                // 슬라이드 방향 라디오 버튼 체크 (up, down은 left로 변환)
                                let slideDirection = settings.slide_direction || 'left';
                                if (slideDirection === 'up' || slideDirection === 'down') {
                                    slideDirection = 'left';
                                }
                                const slideDirectionRadio = document.querySelector(`input[name="edit_gallery_slide_direction"][value="${slideDirection}"]`);
                                if (slideDirectionRadio) {
                                    slideDirectionRadio.checked = true;
                                }
                                if (document.getElementById('edit_widget_gallery_show_arrows')) {
                                    document.getElementById('edit_widget_gallery_show_arrows').checked = settings.show_arrows !== false;
                                }
                            }
                        }
                        if (document.getElementById('edit_widget_gallery_show_title')) {
                            document.getElementById('edit_widget_gallery_show_title').checked = settings.show_title !== false;
                        }
                        // 갤러리 위젯은 제목 입력 가능 (필수 아님)
                        const editTitleHelp = document.getElementById('edit_widget_title_help');
                        if (editTitleHelp) editTitleHelp.style.display = 'inline';
                        // Bootstrap tooltip 초기화
                        if (editTitleHelp && typeof bootstrap !== 'undefined') {
                            const tooltipElement = editTitleHelp.querySelector('[data-bs-toggle="tooltip"]');
                            if (tooltipElement) {
                                new bootstrap.Tooltip(tooltipElement);
                            }
                        }
                        titleContainer.style.display = 'block';
                        // 제목이 없거나 기본값이면 빈 값으로 설정
                        if (!title || title === '갤러리' || title.trim() === '') {
                            document.getElementById('edit_widget_title').value = '';
                        } else {
                            document.getElementById('edit_widget_title').value = title;
                        }
                        
                        // 표시 방식 변경 이벤트
                        const displayTypeSelect = document.getElementById('edit_widget_gallery_display_type');
                        if (displayTypeSelect) {
                            // 초기 상태 설정
                            if (displayType === 'grid') {
                                if (galleryGridContainer) galleryGridContainer.style.display = 'block';
                                if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                            } else {
                                if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                                if (gallerySlideContainer) gallerySlideContainer.style.display = 'block';
                            }
                            
                            displayTypeSelect.onchange = function() {
                                if (this.value === 'grid') {
                                    if (galleryGridContainer) galleryGridContainer.style.display = 'block';
                                    if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                                } else {
                                    if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                                    if (gallerySlideContainer) gallerySlideContainer.style.display = 'block';
                                }
                            };
                        }
                    } else if (widgetType === 'tab_menu') {
                        limitContainer.style.display = 'none';
                        tabMenuContainer.style.display = 'block';
                        rankingContainer.style.display = 'none';
                        titleContainer.style.display = 'none';
                        // 탭메뉴 데이터 로드
                        const tabs = settings.tabs || [];
                        const container = document.getElementById('edit_tab_menu_list');
                        container.innerHTML = '';
                        editTabMenuIndex = 0;
                        tabs.forEach((tab, index) => {
                            editTabMenuIndex = index;
                            addEditTabMenuItem();
                            const item = document.getElementById(`edit_tab_menu_item_${index}`);
                            if (item) {
                                item.querySelector('.edit-tab-menu-name').value = tab.name || '';
                                item.querySelector('.edit-tab-menu-widget-type').value = tab.widget_type || '';
                                item.querySelector('.edit-tab-menu-limit').value = tab.limit || 10;
                                if (tab.widget_type === 'board') {
                                    const boardContainer = item.querySelector('.edit-tab-menu-board-container');
                                    if (boardContainer) boardContainer.style.display = 'block';
                                    const boardSelect = item.querySelector('.edit-tab-menu-board-id');
                                    if (boardSelect) boardSelect.value = tab.board_id || '';
                                }
                                // 로드된 항목은 접힌 상태로 시작
                                const body = document.getElementById(`edit_tab_menu_item_${index}_body`);
                                const icon = document.getElementById(`edit_tab_menu_item_${index}_icon`);
                                if (body && icon) {
                                    body.style.display = 'none';
                                    icon.className = 'bi bi-chevron-right';
                                }
                            }
                        });
                        editTabMenuIndex++;
                        const toggleMenuContainer = document.getElementById('edit_widget_toggle_menu_container');
                        if (toggleMenuContainer) toggleMenuContainer.style.display = 'none';
                    } else if (widgetType === 'toggle_menu') {
                        limitContainer.style.display = 'none';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        titleContainer.style.display = 'block';
                        const toggleMenuContainer = document.getElementById('edit_widget_toggle_menu_container');
                        if (toggleMenuContainer) toggleMenuContainer.style.display = 'block';
                        // 토글 메뉴 목록 로드
                        fetch('/site/{{ $site->slug }}/admin/toggle-menus/list')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const select = document.getElementById('edit_widget_toggle_menu_id');
                                    if (select) {
                                        select.innerHTML = '<option value="">선택하세요</option>';
                                        
                                        const selectedId = settings.toggle_menu_id || settings.toggle_menu_ids?.[0] || null;
                                        
                                        data.toggleMenus.forEach(toggleMenu => {
                                            const option = document.createElement('option');
                                            option.value = toggleMenu.id;
                                            option.textContent = toggleMenu.name;
                                            if (selectedId && selectedId == toggleMenu.id) {
                                                option.selected = true;
                                            }
                                            select.appendChild(option);
                                        });
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error loading toggle menus:', error);
                            });
                    } else if (widgetType === 'user_ranking') {
                        limitContainer.style.display = 'none';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'block';
                        titleContainer.style.display = 'none';
                        document.getElementById('edit_widget_rank_ranking').checked = settings.enable_rank_ranking || false;
                        document.getElementById('edit_widget_point_ranking').checked = settings.enable_point_ranking || false;
                        document.getElementById('edit_widget_ranking_limit').value = settings.ranking_limit || 5;
                    } else if (widgetType === 'custom_html') {
                        limitContainer.style.display = 'none';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        if (boardContainer) boardContainer.style.display = 'none';
                        if (sortOrderContainer) sortOrderContainer.style.display = 'none';
                        if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
                        const galleryContainer = document.getElementById('edit_widget_gallery_container');
                        const galleryDisplayTypeContainer = document.getElementById('edit_widget_gallery_display_type_container');
                        const galleryGridContainer = document.getElementById('edit_widget_gallery_grid_container');
                        const gallerySlideContainer = document.getElementById('edit_widget_gallery_slide_container');
                        const galleryShowTitleContainer = document.getElementById('edit_widget_gallery_show_title_container');
                        if (galleryContainer) galleryContainer.style.display = 'none';
                        if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
                        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                        if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
                        const customHtmlContainer = document.getElementById('edit_widget_custom_html_container');
                        if (customHtmlContainer) customHtmlContainer.style.display = 'block';
                        const blockContainer = document.getElementById('edit_widget_block_container');
                        if (blockContainer) blockContainer.style.display = 'none';
                        const editTitleHelp = document.getElementById('edit_widget_title_help');
                        if (editTitleHelp) editTitleHelp.style.display = 'none';
                        titleContainer.style.display = 'block';
                        if (document.getElementById('edit_widget_custom_html')) {
                            document.getElementById('edit_widget_custom_html').value = settings.html || settings.custom_html || '';
                        }
                        document.getElementById('edit_widget_title').value = title;
                    } else if (widgetType === 'block') {
                        limitContainer.style.display = 'none';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        if (boardContainer) boardContainer.style.display = 'none';
                        if (sortOrderContainer) sortOrderContainer.style.display = 'none';
                        if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
                        const galleryContainer = document.getElementById('edit_widget_gallery_container');
                        const galleryDisplayTypeContainer = document.getElementById('edit_widget_gallery_display_type_container');
                        const galleryGridContainer = document.getElementById('edit_widget_gallery_grid_container');
                        const gallerySlideContainer = document.getElementById('edit_widget_gallery_slide_container');
                        const galleryShowTitleContainer = document.getElementById('edit_widget_gallery_show_title_container');
                        if (galleryContainer) galleryContainer.style.display = 'none';
                        if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
                        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                        if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
                        const customHtmlContainer = document.getElementById('edit_widget_custom_html_container');
                        if (customHtmlContainer) customHtmlContainer.style.display = 'none';
                        const blockContainer = document.getElementById('edit_widget_block_container');
                        if (blockContainer) blockContainer.style.display = 'block';
                        const editTitleHelp = document.getElementById('edit_widget_title_help');
                        if (editTitleHelp) editTitleHelp.style.display = 'none';
                        titleContainer.style.display = 'none';
                        
                        // 블록 설정 값 채우기
                        if (document.getElementById('edit_widget_block_title')) {
                            document.getElementById('edit_widget_block_title').value = settings.block_title || '';
                        }
                        if (document.getElementById('edit_widget_block_content')) {
                            document.getElementById('edit_widget_block_content').value = settings.block_content || '';
                        }
                        const textAlign = settings.text_align || 'left';
                        document.querySelector(`input[name="edit_block_text_align"][value="${textAlign}"]`).checked = true;
                        
                        const backgroundType = settings.background_type || 'color';
                        if (document.getElementById('edit_widget_block_background_type')) {
                            document.getElementById('edit_widget_block_background_type').value = backgroundType;
                            handleEditBlockBackgroundTypeChange();
                        }
                        
                        if (backgroundType === 'color') {
                            if (document.getElementById('edit_widget_block_background_color')) {
                                document.getElementById('edit_widget_block_background_color').value = settings.background_color || '#007bff';
                            }
                        } else if (backgroundType === 'gradient') {
                            const gradientStartInput = document.getElementById('edit_widget_block_gradient_start');
                            const gradientEndInput = document.getElementById('edit_widget_block_gradient_end');
                            const gradientAngleInput = document.getElementById('edit_widget_block_gradient_angle');
                            const gradientPreview = document.getElementById('edit_widget_block_gradient_preview');
                            
                            if (gradientStartInput) {
                                gradientStartInput.value = settings.background_gradient_start || '#ffffff';
                            }
                            if (gradientEndInput) {
                                gradientEndInput.value = settings.background_gradient_end || '#000000';
                            }
                            if (gradientAngleInput) {
                                gradientAngleInput.value = settings.background_gradient_angle || 90;
                            }
                            
                            // 그라데이션 미리보기 업데이트
                            if (gradientPreview && gradientStartInput && gradientEndInput && gradientAngleInput) {
                                const startValue = gradientStartInput.value;
                                const endValue = gradientEndInput.value;
                                const angleValue = gradientAngleInput.value || 90;
                                gradientPreview.style.background = `linear-gradient(${angleValue}deg, ${startValue}, ${endValue})`;
                            }
                        } else if (backgroundType === 'image') {
                            if (settings.background_image_url && document.getElementById('edit_widget_block_image_preview_img')) {
                                document.getElementById('edit_widget_block_image_preview_img').src = settings.background_image_url;
                                document.getElementById('edit_widget_block_image_preview').style.display = 'block';
                                document.getElementById('edit_widget_block_background_image').value = settings.background_image_url;
                            }
                        }
                        
                        if (document.getElementById('edit_widget_block_padding_top')) {
                            document.getElementById('edit_widget_block_padding_top').value = settings.padding_top || 20;
                        }
                        if (document.getElementById('edit_widget_block_padding_left')) {
                            document.getElementById('edit_widget_block_padding_left').value = settings.padding_left || 20;
                        }
                        if (document.getElementById('edit_widget_block_font_color')) {
                            document.getElementById('edit_widget_block_font_color').value = settings.font_color || '#ffffff';
                        }
                        if (document.getElementById('edit_widget_block_title_font_size')) {
                            let titleSize = settings.title_font_size || '16';
                            if (titleSize.includes('rem')) {
                                titleSize = parseFloat(titleSize) * 16;
                            }
                            document.getElementById('edit_widget_block_title_font_size').value = titleSize;
                        }
                        if (document.getElementById('edit_widget_block_content_font_size')) {
                            let contentSize = settings.content_font_size || '14';
                            if (contentSize.includes('rem')) {
                                contentSize = parseFloat(contentSize) * 16;
                            }
                            document.getElementById('edit_widget_block_content_font_size').value = contentSize;
                        }
                        
                        const showButton = settings.show_button || false;
                        if (document.getElementById('edit_widget_block_show_button')) {
                            document.getElementById('edit_widget_block_show_button').checked = showButton;
                            handleEditSidebarBlockButtonToggle();
                        }
                        if (showButton) {
                            if (document.getElementById('edit_widget_block_button_text')) {
                                document.getElementById('edit_widget_block_button_text').value = settings.button_text || '';
                            }
                            if (document.getElementById('edit_widget_block_button_background_color')) {
                                document.getElementById('edit_widget_block_button_background_color').value = settings.button_background_color || '#007bff';
                            }
                            if (document.getElementById('edit_widget_block_button_text_color')) {
                                document.getElementById('edit_widget_block_button_text_color').value = settings.button_text_color || '#ffffff';
                            }
                        }
                        
                        if (document.getElementById('edit_widget_block_link')) {
                            document.getElementById('edit_widget_block_link').value = settings.link || '';
                        }
                        if (document.getElementById('edit_widget_block_open_new_tab')) {
                            document.getElementById('edit_widget_block_open_new_tab').checked = settings.open_new_tab || false;
                        }
                    } else if (widgetType === 'block_slide') {
                        limitContainer.style.display = 'none';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        if (boardContainer) boardContainer.style.display = 'none';
                        if (sortOrderContainer) sortOrderContainer.style.display = 'none';
                        if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
                        const galleryContainer = document.getElementById('edit_widget_gallery_container');
                        const galleryDisplayTypeContainer = document.getElementById('edit_widget_gallery_display_type_container');
                        const galleryGridContainer = document.getElementById('edit_widget_gallery_grid_container');
                        const gallerySlideContainer = document.getElementById('edit_widget_gallery_slide_container');
                        const galleryShowTitleContainer = document.getElementById('edit_widget_gallery_show_title_container');
                        if (galleryContainer) galleryContainer.style.display = 'none';
                        if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
                        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                        if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
                        const customHtmlContainer = document.getElementById('edit_widget_custom_html_container');
                        if (customHtmlContainer) customHtmlContainer.style.display = 'none';
                        const blockContainer = document.getElementById('edit_widget_block_container');
                        if (blockContainer) blockContainer.style.display = 'none';
                        const blockSlideContainer = document.getElementById('edit_widget_block_slide_container');
                        if (blockSlideContainer) blockSlideContainer.style.display = 'block';
                        const editTitleHelp = document.getElementById('edit_widget_title_help');
                        if (editTitleHelp) editTitleHelp.style.display = 'none';
                        titleContainer.style.display = 'none';
                        
                        // 블록 슬라이드 설정 값 채우기
                        const slideDirection = settings.slide_direction || 'left';
                        const directionRadio = document.querySelector(`input[name="edit_block_slide_direction"][value="${slideDirection}"]`);
                        if (directionRadio) directionRadio.checked = true;
                        
                        // 블록 아이템들 로드
                        const blocks = settings.blocks || [];
                        const itemsContainer = document.getElementById('edit_widget_block_slide_items');
                        if (itemsContainer) {
                            itemsContainer.innerHTML = '';
                            editBlockSlideItemIndex = 0;
                            blocks.forEach((block, index) => {
                                addEditBlockSlideItem(block);
                            });
                        }
                    } else if (widgetType === 'image_slide') {
                        limitContainer.style.display = 'none';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        if (boardContainer) boardContainer.style.display = 'none';
                        if (sortOrderContainer) sortOrderContainer.style.display = 'none';
                        if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
                        const galleryContainer = document.getElementById('edit_widget_gallery_container');
                        const galleryDisplayTypeContainer = document.getElementById('edit_widget_gallery_display_type_container');
                        const galleryGridContainer = document.getElementById('edit_widget_gallery_grid_container');
                        const gallerySlideContainer = document.getElementById('edit_widget_gallery_slide_container');
                        const galleryShowTitleContainer = document.getElementById('edit_widget_gallery_show_title_container');
                        if (galleryContainer) galleryContainer.style.display = 'none';
                        if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
                        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                        if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
                        const customHtmlContainer = document.getElementById('edit_widget_custom_html_container');
                        if (customHtmlContainer) customHtmlContainer.style.display = 'none';
                        const blockContainer = document.getElementById('edit_widget_block_container');
                        if (blockContainer) blockContainer.style.display = 'none';
                        const blockSlideContainer = document.getElementById('edit_widget_block_slide_container');
                        if (blockSlideContainer) blockSlideContainer.style.display = 'none';
                        const imageSlideContainer = document.getElementById('edit_widget_image_slide_container');
                        if (imageSlideContainer) imageSlideContainer.style.display = 'block';
                        const editTitleHelp = document.getElementById('edit_widget_title_help');
                        if (editTitleHelp) editTitleHelp.style.display = 'none';
                        titleContainer.style.display = 'none';
                        
                        // 이미지 슬라이드 설정 값 채우기
                        const slideDirection = settings.slide_direction || 'left';
                        const directionRadio = document.querySelector(`input[name="edit_image_slide_direction"][value="${slideDirection}"]`);
                        if (directionRadio) directionRadio.checked = true;
                        
                        const slideMode = settings.slide_mode || 'single';
                        const singleCheckbox = document.getElementById('edit_widget_image_slide_single');
                        const infiniteCheckbox = document.getElementById('edit_widget_image_slide_infinite');
                        const visibleCountInput = document.getElementById('edit_widget_image_slide_visible_count');
                        const visibleCountMobileInput = document.getElementById('edit_widget_image_slide_visible_count_mobile');
                        const gapInput = document.getElementById('edit_widget_image_slide_gap');
                        
                        if (slideMode === 'infinite') {
                            if (singleCheckbox) singleCheckbox.checked = false;
                            if (infiniteCheckbox) infiniteCheckbox.checked = true;
                            if (visibleCountInput) {
                                visibleCountInput.value = settings.visible_count || 3;
                                document.getElementById('edit_widget_image_slide_visible_count_container').style.display = 'block';
                            }
                            if (visibleCountMobileInput) {
                                visibleCountMobileInput.value = settings.visible_count_mobile || 2;
                                document.getElementById('edit_widget_image_slide_visible_count_mobile_container').style.display = 'block';
                            }
                            if (gapInput) {
                                gapInput.value = settings.image_gap || 0;
                                document.getElementById('edit_widget_image_slide_gap_container').style.display = 'block';
                            }
                            // 배경색 설정 로드
                            const backgroundTypeSelect = document.getElementById('edit_widget_image_slide_background_type');
                            const backgroundColorInput = document.getElementById('edit_widget_image_slide_background_color');
                            const backgroundContainer = document.getElementById('edit_widget_image_slide_background_container');
                            if (backgroundContainer) {
                                backgroundContainer.style.display = 'block';
                            }
                            if (backgroundTypeSelect) {
                                backgroundTypeSelect.value = settings.background_type || 'none';
                                handleEditImageSlideBackgroundTypeChange();
                            }
                            if (backgroundColorInput && settings.background_color) {
                                backgroundColorInput.value = settings.background_color;
                            }
                        } else {
                            if (singleCheckbox) singleCheckbox.checked = true;
                            if (infiniteCheckbox) infiniteCheckbox.checked = false;
                            if (visibleCountInput) {
                                document.getElementById('edit_widget_image_slide_visible_count_container').style.display = 'none';
                            }
                            if (visibleCountMobileInput) {
                                document.getElementById('edit_widget_image_slide_visible_count_mobile_container').style.display = 'none';
                            }
                            if (gapInput) {
                                document.getElementById('edit_widget_image_slide_gap_container').style.display = 'none';
                            }
                            const backgroundContainer = document.getElementById('edit_widget_image_slide_background_container');
                            if (backgroundContainer) {
                                backgroundContainer.style.display = 'none';
                            }
                        }
                        
                        // 이미지 아이템들 로드
                        const images = settings.images || [];
                        const itemsContainer = document.getElementById('edit_widget_image_slide_items');
                        if (itemsContainer) {
                            itemsContainer.innerHTML = '';
                            editImageSlideItemIndex = 0;
                            images.forEach((image, index) => {
                                addEditImageSlideItem();
                                const item = document.getElementById(`edit_image_slide_item_${editImageSlideItemIndex - 1}`);
                                if (item) {
                                    const imageUrlInput = item.querySelector(`#edit_image_slide_${editImageSlideItemIndex - 1}_image_url`);
                                    const linkInput = item.querySelector(`.edit-image-slide-link`);
                                    const openNewTabInput = item.querySelector(`.edit-image-slide-open-new-tab`);
                                    const preview = item.querySelector(`#edit_image_slide_${editImageSlideItemIndex - 1}_image_preview`);
                                    const previewImg = item.querySelector(`#edit_image_slide_${editImageSlideItemIndex - 1}_image_preview_img`);
                                    
                                    if (imageUrlInput && image.image_url) {
                                        imageUrlInput.value = image.image_url;
                                    }
                                    if (linkInput && image.link) {
                                        linkInput.value = image.link;
                                    }
                                    if (openNewTabInput) {
                                        openNewTabInput.checked = image.open_new_tab || false;
                                    }
                                    if (preview && previewImg && image.image_url) {
                                        previewImg.src = image.image_url;
                                        preview.style.display = 'block';
                                    }
                                }
                            });
                        }
                        
                        // 모드 변경 핸들러 호출
                        handleEditImageSlideModeChange();
                    } else {
                        limitContainer.style.display = 'none';
                        tabMenuContainer.style.display = 'none';
                        rankingContainer.style.display = 'none';
                        if (boardContainer) boardContainer.style.display = 'none';
                        if (sortOrderContainer) sortOrderContainer.style.display = 'none';
                        if (marqueeDirectionContainer) marqueeDirectionContainer.style.display = 'none';
                        const galleryContainer = document.getElementById('edit_widget_gallery_container');
                        const galleryDisplayTypeContainer = document.getElementById('edit_widget_gallery_display_type_container');
                        const galleryGridContainer = document.getElementById('edit_widget_gallery_grid_container');
                        const gallerySlideContainer = document.getElementById('edit_widget_gallery_slide_container');
                        const galleryShowTitleContainer = document.getElementById('edit_widget_gallery_show_title_container');
                        if (galleryContainer) galleryContainer.style.display = 'none';
                        if (galleryDisplayTypeContainer) galleryDisplayTypeContainer.style.display = 'none';
                        if (galleryGridContainer) galleryGridContainer.style.display = 'none';
                        if (gallerySlideContainer) gallerySlideContainer.style.display = 'none';
                        if (galleryShowTitleContainer) galleryShowTitleContainer.style.display = 'none';
                        const customHtmlContainer = document.getElementById('edit_widget_custom_html_container');
                        if (customHtmlContainer) customHtmlContainer.style.display = 'none';
                        const editTitleHelp = document.getElementById('edit_widget_title_help');
                        if (editTitleHelp) editTitleHelp.style.display = 'none';
                        titleContainer.style.display = 'block';
                        document.getElementById('edit_widget_title').value = title;
                    }
                    
                    // 모든 설정이 완료된 후 모달 열기
                    const modal = new bootstrap.Modal(document.getElementById('widgetSettingsModal'));
                    modal.show();
                } else {
                    alert('위젯 정보를 가져오는데 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error fetching widget:', error);
                alert('위젯 정보를 가져오는 중 오류가 발생했습니다.');
                // 오류 발생 시에도 기본 설정으로 모달 열기
                if (widgetType === 'popular_posts' || widgetType === 'recent_posts') {
                    limitContainer.style.display = 'block';
                    tabMenuContainer.style.display = 'none';
                    rankingContainer.style.display = 'none';
                    titleContainer.style.display = 'block';
                    document.getElementById('edit_widget_limit').value = 10;
                    document.getElementById('edit_widget_title').value = title;
                } else if (widgetType === 'tab_menu') {
                    limitContainer.style.display = 'none';
                    tabMenuContainer.style.display = 'block';
                    rankingContainer.style.display = 'none';
                    titleContainer.style.display = 'none';
                } else if (widgetType === 'toggle_menu') {
                    limitContainer.style.display = 'none';
                    tabMenuContainer.style.display = 'none';
                    rankingContainer.style.display = 'none';
                    titleContainer.style.display = 'none';
                    const toggleMenuContainer = document.getElementById('edit_widget_toggle_menu_container');
                    if (toggleMenuContainer) toggleMenuContainer.style.display = 'block';
                } else if (widgetType === 'user_ranking') {
                    limitContainer.style.display = 'none';
                    tabMenuContainer.style.display = 'none';
                    rankingContainer.style.display = 'block';
                    titleContainer.style.display = 'block';
                } else {
                    limitContainer.style.display = 'none';
                    tabMenuContainer.style.display = 'none';
                    rankingContainer.style.display = 'none';
                    titleContainer.style.display = 'block';
                    document.getElementById('edit_widget_title').value = title;
                }
                const modal = new bootstrap.Modal(document.getElementById('widgetSettingsModal'));
                modal.show();
            });
}

function saveWidgetSettings() {
    const form = document.getElementById('editWidgetForm');
    const formData = new FormData(form);
    const widgetId = document.getElementById('edit_widget_id').value;
    
    // 위젯 타입 가져오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    const widgetType = widgetItem ? widgetItem.dataset.widgetType : '';
    
    // is_active 값 처리 (체크박스는 체크되지 않으면 값이 없으므로 명시적으로 처리)
    const isActiveCheckbox = document.getElementById('edit_widget_is_active');
    formData.set('is_active', isActiveCheckbox.checked ? '1' : '0');
    
    // 제목 처리: 갤러리 위젯의 경우 제목이 없으면 빈 문자열로 설정
    const titleInput = document.getElementById('edit_widget_title');
    if (widgetType === 'gallery' && titleInput) {
        const titleValue = titleInput.value.trim();
        if (!titleValue || titleValue === '') {
            formData.set('title', '');
        }
    }
    
    // settings 객체 생성
    const settings = {};
    if (widgetType === 'popular_posts' || widgetType === 'recent_posts' || widgetType === 'weekly_popular_posts' || widgetType === 'monthly_popular_posts') {
        const limit = document.getElementById('edit_widget_limit').value;
        if (limit) {
            settings.limit = parseInt(limit);
        }
    } else if (widgetType === 'board') {
        const boardId = document.getElementById('edit_widget_board_id').value;
        const limit = document.getElementById('edit_widget_limit').value;
        const sortOrder = document.getElementById('edit_widget_sort_order').value;
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (limit) {
            settings.limit = parseInt(limit);
        }
        if (sortOrder) {
            settings.sort_order = sortOrder;
        }
    } else if (widgetType === 'marquee_board') {
        const boardId = document.getElementById('edit_widget_board_id').value;
        const limit = document.getElementById('edit_widget_limit').value;
        const sortOrder = document.getElementById('edit_widget_sort_order').value;
        const directionRadio = document.querySelector('input[name="edit_direction"]:checked');
        const direction = directionRadio ? directionRadio.value : 'left';
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (limit) {
            settings.limit = parseInt(limit);
        }
        if (sortOrder) {
            settings.sort_order = sortOrder;
        }
        if (direction) {
            settings.direction = direction;
        }
    } else if (widgetType === 'gallery') {
        const boardId = document.getElementById('edit_widget_gallery_board_id').value;
        const displayType = document.getElementById('edit_widget_gallery_display_type').value;
        const showTitle = document.getElementById('edit_widget_gallery_show_title').checked;
        if (boardId) {
            settings.board_id = parseInt(boardId);
        }
        if (displayType) {
            settings.display_type = displayType;
        }
        settings.show_title = showTitle;
        if (displayType === 'grid') {
            const cols = document.getElementById('edit_widget_gallery_cols').value;
            const rows = document.getElementById('edit_widget_gallery_rows').value;
            if (cols) {
                settings.cols = parseInt(cols);
            }
            if (rows) {
                settings.rows = parseInt(rows);
            }
            settings.limit = parseInt(cols) * parseInt(rows);
        } else if (displayType === 'slide') {
            const slideCols = document.getElementById('edit_widget_gallery_slide_cols').value;
            const slideDirectionRadio = document.querySelector('input[name="edit_gallery_slide_direction"]:checked');
            const slideDirection = slideDirectionRadio ? slideDirectionRadio.value : 'left';
            if (slideCols) {
                settings.slide_cols = parseInt(slideCols);
            }
            if (slideDirection) {
                settings.slide_direction = slideDirection;
            }
            settings.limit = 10; // 슬라이드의 경우 기본 10개
        }
    } else if (widgetType === 'tab_menu') {
        // 탭메뉴 설정 수집
        const tabMenus = [];
        const tabMenuItems = document.querySelectorAll('#edit_tab_menu_list .edit-tab-menu-item');
        tabMenuItems.forEach((item, index) => {
            const nameInput = item.querySelector('.edit-tab-menu-name');
            const widgetTypeSelect = item.querySelector('.edit-tab-menu-widget-type');
            const limitInput = item.querySelector('.edit-tab-menu-limit');
            const boardSelect = item.querySelector('.edit-tab-menu-board-id');
            if (nameInput && widgetTypeSelect) {
                const name = nameInput.value;
                const widgetType = widgetTypeSelect.value;
                const limit = limitInput ? parseInt(limitInput.value) : 10;
                if (name && widgetType) {
                    const tabMenu = {
                        name: name,
                        widget_type: widgetType,
                        limit: limit || 10
                    };
                    if (widgetType === 'board' && boardSelect && boardSelect.value) {
                        tabMenu.board_id = parseInt(boardSelect.value);
                    }
                    tabMenus.push(tabMenu);
                }
            }
        });
        settings.tabs = tabMenus;
        
        // 탭메뉴 이름들을 위젯 제목으로 자동 설정
        const tabTitles = tabMenus.map(tab => tab.name).filter(name => name);
        if (tabTitles.length > 0) {
            formData.set('title', tabTitles.join(' · '));
        }
    } else if (widgetType === 'toggle_menu') {
        // 토글 메뉴 설정 수집
        const toggleMenuCheckboxes = document.querySelectorAll('#edit_toggle_menu_list input[type="checkbox"]:checked');
        settings.toggle_menu_ids = Array.from(toggleMenuCheckboxes).map(cb => parseInt(cb.value));
    } else if (widgetType === 'custom_html') {
        // 커스텀 HTML 설정 수집
        const customHtml = document.getElementById('edit_widget_custom_html').value;
        if (customHtml) {
            settings.html = customHtml;
            settings.custom_html = customHtml;
        }
    } else if (widgetType === 'block') {
        // 블록 위젯 설정 수집
        const blockTitle = document.getElementById('edit_widget_block_title').value;
        const blockContent = document.getElementById('edit_widget_block_content').value;
        const textAlignRadio = document.querySelector('input[name="edit_block_text_align"]:checked');
        const textAlign = textAlignRadio ? textAlignRadio.value : 'left';
        const backgroundType = document.getElementById('edit_widget_block_background_type').value || 'color';
                        const paddingTop = document.getElementById('edit_widget_block_padding_top').value || '20';
                        const paddingLeft = document.getElementById('edit_widget_block_padding_left').value || '20';
                        const blockLink = document.getElementById('edit_widget_block_link').value;
                        const openNewTab = document.getElementById('edit_widget_block_open_new_tab').checked;
                        const fontColor = document.getElementById('edit_widget_block_font_color')?.value || '#ffffff';
                        const titleFontSize = document.getElementById('edit_widget_block_title_font_size')?.value || '16';
                        const contentFontSize = document.getElementById('edit_widget_block_content_font_size')?.value || '14';
                        const showButton = document.getElementById('edit_widget_block_show_button')?.checked || false;
                        const buttonText = document.getElementById('edit_widget_block_button_text')?.value || '';
                        const buttonBackgroundColor = document.getElementById('edit_widget_block_button_background_color')?.value || '#007bff';
                        const buttonTextColor = document.getElementById('edit_widget_block_button_text_color')?.value || '#ffffff';
                        
                        if (blockTitle) {
                            settings.block_title = blockTitle;
                        }
                        if (blockContent) {
                            settings.block_content = blockContent;
                        }
                        settings.text_align = textAlign;
                        settings.background_type = backgroundType;
                        settings.font_color = fontColor;
                        settings.title_font_size = titleFontSize;
                        settings.content_font_size = contentFontSize;
                        
                        if (backgroundType === 'color') {
                            const backgroundColor = document.getElementById('edit_widget_block_background_color').value || '#007bff';
                            settings.background_color = backgroundColor;
                        } else if (backgroundType === 'gradient') {
                            // 두 가지 필드명 모두 확인
                            const gradientStartInput = document.getElementById('edit_widget_block_gradient_start') || 
                                                     document.getElementById('edit_widget_block_background_gradient_start');
                            const gradientEndInput = document.getElementById('edit_widget_block_gradient_end') || 
                                                   document.getElementById('edit_widget_block_background_gradient_end');
                            const gradientAngleInput = document.getElementById('edit_widget_block_gradient_angle') || 
                                                     document.getElementById('edit_widget_block_background_gradient_angle');
                            
                            // 값이 있으면 사용, 없으면 기본값 (하지만 저장된 값이 우선)
                            if (gradientStartInput && gradientStartInput.value) {
                                settings.background_gradient_start = gradientStartInput.value;
                            }
                            if (gradientEndInput && gradientEndInput.value) {
                                settings.background_gradient_end = gradientEndInput.value;
                            }
                            if (gradientAngleInput && gradientAngleInput.value) {
                                settings.background_gradient_angle = parseInt(gradientAngleInput.value) || 90;
                            }
                        } else if (backgroundType === 'image') {
                            const imageFile = document.getElementById('edit_widget_block_image_input').files[0];
                        if (imageFile) {
                                formData.append('block_background_image_file', imageFile);
                            }
                            const imageUrl = document.getElementById('edit_widget_block_background_image').value;
                            if (imageUrl) {
                                settings.background_image_url = imageUrl;
                            }
                        }
                        
                        settings.padding_top = parseInt(paddingTop);
                        settings.padding_left = parseInt(paddingLeft);
                        
                        if (blockLink) {
                            settings.link = blockLink;
                        }
                        settings.open_new_tab = openNewTab;
                        settings.show_button = showButton;
                        if (showButton) {
                            settings.button_text = buttonText;
                            settings.button_background_color = buttonBackgroundColor;
                            settings.button_text_color = buttonTextColor;
                        }
                    } else if (widgetType === 'block_slide') {
                        // 블록 슬라이드 위젯 설정 수집
                        const slideDirection = document.querySelector('input[name="edit_block_slide_direction"]:checked')?.value || 'left';
                        settings.slide_direction = slideDirection;
                        
                        // 블록 아이템들 수집
                        const blockItems = [];
                        const blockSlideItems = document.querySelectorAll('.edit-block-slide-item');
                        blockSlideItems.forEach((item, index) => {
                            const itemIndex = item.dataset.itemIndex;
                            const title = item.querySelector('.edit-block-slide-title')?.value || '';
                            const content = item.querySelector('.edit-block-slide-content')?.value || '';
                            const textAlignRadio = item.querySelector(`input[name="edit_block_slide[${itemIndex}][text_align]"]:checked`);
                            const textAlign = textAlignRadio ? textAlignRadio.value : 'left';
                            const backgroundType = item.querySelector('.edit-block-slide-background-type')?.value || 'color';
                            const paddingTop = item.querySelector('.edit-block-slide-padding-top')?.value || '20';
                            const paddingLeft = item.querySelector('.edit-block-slide-padding-left')?.value || '20';
                            const link = item.querySelector('.edit-block-slide-link')?.value || '';
                            const openNewTab = item.querySelector('.edit-block-slide-open-new-tab')?.checked || false;
                            const fontColor = item.querySelector('.edit-block-slide-font-color')?.value || '#ffffff';
                            const titleFontSize = item.querySelector('.edit-block-slide-title-font-size')?.value || '16';
                            const contentFontSize = item.querySelector('.edit-block-slide-content-font-size')?.value || '14';
                            
                            const blockItem = {
                                title: title,
                                content: content,
                                text_align: textAlign,
                                title_font_size: titleFontSize,
                                content_font_size: contentFontSize,
                                background_type: backgroundType,
                                padding_top: parseInt(paddingTop),
                                padding_left: parseInt(paddingLeft),
                                link: link,
                                open_new_tab: openNewTab,
                                font_color: fontColor
                            };
                            
                            if (backgroundType === 'color') {
                                const backgroundColor = item.querySelector('.edit-block-slide-background-color')?.value || '#007bff';
                                blockItem.background_color = backgroundColor;
                            } else if (backgroundType === 'gradient') {
                                // 두 가지 필드명 모두 확인
                                const gradientStartInput = item.querySelector(`#edit_block_slide_${itemIndex}_gradient_start`) || 
                                                         item.querySelector(`#edit_block_slide_${itemIndex}_background_gradient_start`);
                                const gradientEndInput = item.querySelector(`#edit_block_slide_${itemIndex}_gradient_end`) || 
                                                       item.querySelector(`#edit_block_slide_${itemIndex}_background_gradient_end`);
                                const gradientAngleInput = item.querySelector(`#edit_block_slide_${itemIndex}_gradient_angle`) || 
                                                         item.querySelector(`#edit_block_slide_${itemIndex}_background_gradient_angle`);
                                
                                // 값이 있으면 사용, 없으면 기본값 (하지만 저장된 값이 우선)
                                if (gradientStartInput && gradientStartInput.value) {
                                    blockItem.background_gradient_start = gradientStartInput.value;
                                }
                                if (gradientEndInput && gradientEndInput.value) {
                                    blockItem.background_gradient_end = gradientEndInput.value;
                                }
                                if (gradientAngleInput && gradientAngleInput.value) {
                                    blockItem.background_gradient_angle = parseInt(gradientAngleInput.value) || 90;
                                }
                            } else if (backgroundType === 'image') {
                                const imageFile = item.querySelector(`#edit_block_slide_${itemIndex}_image_input`)?.files[0];
                                if (imageFile) {
                                    formData.append(`edit_block_slide[${itemIndex}][background_image_file]`, imageFile);
                                }
                                const imageUrl = item.querySelector(`#edit_block_slide_${itemIndex}_background_image_url`)?.value;
                                if (imageUrl) {
                                    blockItem.background_image_url = imageUrl;
                                }
                            }
                            
                            blockItems.push(blockItem);
                        });
                        
                        settings.blocks = blockItems;
                    } else if (widgetType === 'image_slide') {
                        // 이미지 슬라이드 위젯 설정 수집
                        const slideDirection = document.querySelector('input[name="edit_image_slide_direction"]:checked')?.value || 'left';
                        settings.slide_direction = slideDirection;
                        
                        const singleSlide = document.getElementById('edit_widget_image_slide_single')?.checked || false;
                        const infiniteSlide = document.getElementById('edit_widget_image_slide_infinite')?.checked || false;
                        const visibleCount = document.getElementById('edit_widget_image_slide_visible_count')?.value || '3';
                        const visibleCountMobile = document.getElementById('edit_widget_image_slide_visible_count_mobile')?.value || '2';
                        const imageGap = document.getElementById('edit_widget_image_slide_gap')?.value || '0';
                        const backgroundType = document.getElementById('edit_widget_image_slide_background_type')?.value || 'none';
                        const backgroundColor = document.getElementById('edit_widget_image_slide_background_color')?.value || '#ffffff';
                        
                        settings.slide_mode = infiniteSlide ? 'infinite' : 'single';
                        if (infiniteSlide) {
                            settings.visible_count = parseInt(visibleCount) || 3;
                            settings.visible_count_mobile = parseInt(visibleCountMobile) || 2;
                            settings.image_gap = parseInt(imageGap) || 0;
                            settings.background_type = backgroundType;
                            if (backgroundType === 'color') {
                                settings.background_color = backgroundColor;
                            }
                        }
                        
                        // 이미지 아이템들 수집
                        const imageItems = [];
                        const imageSlideItems = document.querySelectorAll('.edit-image-slide-item');
                        imageSlideItems.forEach((item, index) => {
                            const itemIndex = item.dataset.itemIndex;
                            const imageFile = item.querySelector(`#edit_image_slide_${itemIndex}_image_input`)?.files[0];
                            const imageUrl = item.querySelector(`#edit_image_slide_${itemIndex}_image_url`)?.value;
                            const link = item.querySelector('.edit-image-slide-link')?.value || '';
                            const openNewTab = item.querySelector('.edit-image-slide-open-new-tab')?.checked || false;
                            
                            const imageItem = {
                                link: link,
                                open_new_tab: openNewTab
                            };
                            
                            if (imageFile) {
                                formData.append(`edit_image_slide[${itemIndex}][image_file]`, imageFile);
                            }
                            if (imageUrl) {
                                imageItem.image_url = imageUrl;
                            }
                            
                            imageItems.push(imageItem);
                        });
                        
                        settings.images = imageItems;
                    }
    
    // settings를 JSON으로 추가 (빈 객체가 아닌 경우에만)
    if (Object.keys(settings).length > 0) {
        formData.append('settings', JSON.stringify(settings));
    }
    
    // Laravel의 PUT 메서드를 시뮬레이션하기 위해 _method 필드 추가
    formData.append('_method', 'PUT');
    
    fetch(`{{ route("admin.sidebar-widgets.update", ["site" => $site->slug, "widget" => ":id"]) }}`.replace(':id', widgetId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('widgetSettingsModal')).hide();
            location.reload();
        } else {
            alert('위젯 수정에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('위젯 수정 중 오류가 발생했습니다.');
    });
}

// 탭메뉴 항목 추가 (위젯 수정 폼)
let editTabMenuIndex = 0;
function addEditTabMenuItem() {
    const container = document.getElementById('edit_tab_menu_list');
    if (!container) return;
    
    // 기존 탭메뉴 항목들 접기
    const existingItems = container.querySelectorAll('.edit-tab-menu-item');
    existingItems.forEach((existingItem) => {
        const existingItemIndex = existingItem.dataset.itemIndex;
        const existingBody = document.getElementById(`edit_tab_menu_item_${existingItemIndex}_body`);
        const existingIcon = document.getElementById(`edit_tab_menu_item_${existingItemIndex}_icon`);
        if (existingBody && existingIcon) {
            existingBody.style.display = 'none';
            existingIcon.className = 'bi bi-chevron-right';
        }
    });
    
    const index = editTabMenuIndex++;
    const tabItem = document.createElement('div');
    tabItem.className = 'card mb-2 edit-tab-menu-item';
    tabItem.id = `edit_tab_menu_item_${index}`;
    tabItem.dataset.itemIndex = index;
    
    // 접기/펼치기 헤더
    const header = document.createElement('div');
    header.className = 'card-header d-flex justify-content-between align-items-center';
    header.style.cursor = 'pointer';
    header.onclick = function() {
        toggleEditTabMenuItem(index);
    };
    header.innerHTML = `
        <span>탭메뉴 ${index + 1}</span>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-chevron-down" id="edit_tab_menu_item_${index}_icon"></i>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); removeEditTabMenuItem(${index})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    // 내용 영역
    const body = document.createElement('div');
    body.className = 'card-body';
    body.id = `edit_tab_menu_item_${index}_body`;
    body.innerHTML = `
        <div class="mb-2">
            <label class="form-label">탭메뉴 이름</label>
            <input type="text" class="form-control edit-tab-menu-name" name="tab_menu[${index}][name]" placeholder="탭메뉴 이름을 입력하세요" required>
        </div>
        <div class="mb-2">
            <label class="form-label">위젯 내용</label>
            <select class="form-select edit-tab-menu-widget-type" name="tab_menu[${index}][widget_type]" required onchange="handleTabMenuWidgetTypeChange(this, ${index})">
                <option value="">선택하세요</option>
                <option value="popular_posts">인기 게시글</option>
                <option value="recent_posts">최근 게시글</option>
                <option value="weekly_popular_posts">주간 인기글</option>
                <option value="monthly_popular_posts">월간 인기글</option>
                <option value="board">게시판</option>
                <option value="notice">공지사항</option>
            </select>
        </div>
        <div class="mb-2 edit-tab-menu-board-container" style="display: none;">
            <label class="form-label">게시판 선택</label>
            <select class="form-select edit-tab-menu-board-id" name="tab_menu[${index}][board_id]">
                <option value="">선택하세요</option>
                @foreach(\App\Models\Board::where('site_id', $site->id)->active()->orderBy('order')->get() as $board)
                    <option value="{{ $board->id }}">{{ $board->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">표시할 게시글 수</label>
            <input type="number" class="form-control edit-tab-menu-limit" name="tab_menu[${index}][limit]" min="1" max="50" value="10" required>
        </div>
    `;
    
    tabItem.appendChild(header);
    tabItem.appendChild(body);
    container.appendChild(tabItem);
    
    // 새로 추가된 아이템은 펼쳐진 상태로 시작
    body.style.display = 'block';
}

function toggleEditTabMenuItem(itemIndex) {
    const body = document.getElementById(`edit_tab_menu_item_${itemIndex}_body`);
    const icon = document.getElementById(`edit_tab_menu_item_${itemIndex}_icon`);
    if (body && icon) {
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.className = 'bi bi-chevron-down';
        } else {
            body.style.display = 'none';
            icon.className = 'bi bi-chevron-right';
        }
    }
}

function removeEditTabMenuItem(index) {
    const item = document.getElementById(`edit_tab_menu_item_${index}`);
    if (item) {
        item.remove();
    }
}

function handleSidebarBlockButtonToggle() {
    const showButton = document.getElementById('widget_block_show_button')?.checked;
    const buttonContainer = document.getElementById('widget_block_button_container');
    if (buttonContainer) {
        buttonContainer.style.display = showButton ? 'block' : 'none';
    }
}

function handleEditSidebarBlockButtonToggle() {
    const showButton = document.getElementById('edit_widget_block_show_button')?.checked;
    const buttonContainer = document.getElementById('edit_widget_block_button_container');
    if (buttonContainer) {
        buttonContainer.style.display = showButton ? 'block' : 'none';
    }
}

function deleteWidget(widgetId) {
    if (!confirm('정말 이 위젯을 삭제하시겠습니까?')) {
        return;
    }
    
    fetch(`{{ route("admin.sidebar-widgets.delete", ["site" => $site->slug, "widget" => ":id"]) }}`.replace(':id', widgetId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('위젯 삭제에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('위젯 삭제 중 오류가 발생했습니다.');
    });
}

function moveWidgetUp(widgetId) {
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (!widgetItem) return;
    
    const previousItem = widgetItem.previousElementSibling;
    if (!previousItem || !previousItem.classList.contains('widget-item')) {
        return; // 이미 맨 위에 있거나 이전 항목이 위젯이 아님
    }
    
    // DOM에서 위치 변경
    widgetItem.parentNode.insertBefore(widgetItem, previousItem);
    
    // 순서 저장
    saveWidgetOrder();
}

function moveWidgetDown(widgetId) {
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (!widgetItem) return;
    
    const nextItem = widgetItem.nextElementSibling;
    if (!nextItem || !nextItem.classList.contains('widget-item')) {
        return; // 이미 맨 아래에 있거나 다음 항목이 위젯이 아님
    }
    
    // DOM에서 위치 변경
    widgetItem.parentNode.insertBefore(nextItem, widgetItem);
    
    // 순서 저장
    saveWidgetOrder();
}

function saveWidgetOrder() {
    const widgets = Array.from(document.querySelectorAll('.widget-item'));
    const widgetData = widgets.map((item, index) => ({
        id: parseInt(item.dataset.widgetId),
        order: index + 1
    }));
    
    fetch('{{ route("admin.sidebar-widgets.reorder", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ widgets: widgetData })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('순서 저장 실패:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
<style>
.widget-item {
    cursor: move;
    transition: transform 0.2s;
}

.widget-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.widget-item.sortable-ghost {
    opacity: 0.4;
}

.widget-list {
    min-height: 200px;
}
</style>
<script>
// 사이드 위젯 애니메이션 모달 열기
function openSidebarWidgetAnimationModal(widgetId) {
    document.getElementById('sidebar_widget_animation_id').value = widgetId;
    
    // 기존 애니메이션 설정 불러오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (widgetItem) {
        const settings = widgetItem.dataset.widgetSettings ? JSON.parse(widgetItem.dataset.widgetSettings) : {};
        const animationDirection = settings.animation_direction || 'none';
        const animationDelay = settings.animation_delay || 0;
        
        // 방향 버튼 선택 상태 초기화
        document.querySelectorAll('#sidebarWidgetAnimationModal .animation-direction-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // 선택된 방향 버튼 활성화
        const selectedBtn = document.querySelector(`#sidebarWidgetAnimationModal .animation-direction-btn[data-direction="${animationDirection}"]`);
        if (selectedBtn) {
            selectedBtn.classList.add('active');
        }
        
        document.getElementById('sidebar_widget_animation_direction').value = animationDirection;
        document.getElementById('sidebar_widget_animation_delay').value = animationDelay;
    } else {
        // 기본값 설정
        document.querySelectorAll('#sidebarWidgetAnimationModal .animation-direction-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector('#sidebarWidgetAnimationModal .animation-direction-btn[data-direction="none"]').classList.add('active');
        document.getElementById('sidebar_widget_animation_direction').value = 'none';
        document.getElementById('sidebar_widget_animation_delay').value = 0;
    }
    
    // 툴팁 초기화
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('#sidebarWidgetAnimationModal [data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const modal = new bootstrap.Modal(document.getElementById('sidebarWidgetAnimationModal'));
    modal.show();
}

// 사이드 위젯 애니메이션 방향 선택
function selectSidebarAnimationDirection(direction, button) {
    // 모든 버튼에서 active 클래스 제거
    document.querySelectorAll('#sidebarWidgetAnimationModal .animation-direction-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // 선택된 버튼에 active 클래스 추가
    button.classList.add('active');
    
    // hidden input에 값 설정
    document.getElementById('sidebar_widget_animation_direction').value = direction;
}

// 사이드 위젯 애니메이션 설정 저장
function saveSidebarWidgetAnimation() {
    const widgetId = document.getElementById('sidebar_widget_animation_id').value;
    const animationDirection = document.getElementById('sidebar_widget_animation_direction').value;
    const animationDelay = parseFloat(document.getElementById('sidebar_widget_animation_delay').value) || 0;
    
    if (!widgetId) {
        alert('위젯 ID를 찾을 수 없습니다.');
        return;
    }
    
    // 위젯 정보 가져오기
    const widgetItem = document.querySelector(`[data-widget-id="${widgetId}"]`);
    if (!widgetItem) {
        alert('위젯을 찾을 수 없습니다.');
        return;
    }
    
    // 기존 설정 가져오기
    let existingSettings = {};
    try {
        const settingsStr = widgetItem.dataset.widgetSettings;
        if (settingsStr) {
            existingSettings = JSON.parse(settingsStr);
        }
    } catch (e) {
        console.error('Error parsing widget settings:', e);
    }
    
    // 애니메이션 설정 추가
    existingSettings.animation_direction = animationDirection;
    existingSettings.animation_delay = animationDelay;
    
    // 위젯 설정 업데이트 API 호출
    const updateRoute = '{{ $site->isMasterSite() ? url("/admin/sidebar-widgets/:id") : route("admin.sidebar-widgets.update", ["site" => $site->slug, "widget" => ":id"]) }}';
    const actualRoute = updateRoute.replace(':id', widgetId);
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('_method', 'PUT');
    
    // 위젯 기본 정보도 포함
    formData.append('title', widgetItem.dataset.widgetTitle || '');
    formData.append('is_active', widgetItem.dataset.widgetActive || '1');
    
    // 기존 설정 유지하면서 애니메이션 설정만 추가
    const settings = existingSettings;
    formData.append('settings', JSON.stringify(settings));
    
    // 저장 버튼 비활성화
    const saveBtn = document.querySelector('#sidebarWidgetAnimationModal .btn-primary');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>저장 중...';
    
    fetch(actualRoute, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
        
        if (data.success) {
            // 모달 닫기
            const modal = bootstrap.Modal.getInstance(document.getElementById('sidebarWidgetAnimationModal'));
            modal.hide();
            
            // 성공 메시지 표시
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>애니메이션 설정이 저장되었습니다.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.page-header').after(alertDiv);
            
            // 3초 후 자동으로 알림 제거
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
            
            // 위젯 아이템의 data 속성 업데이트
            widgetItem.setAttribute('data-widget-settings', JSON.stringify(settings));
        } else {
            alert('저장 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
        alert('저장 중 오류가 발생했습니다.');
    });
}

// 그라데이션 모달 관련 변수
let currentGradientContainerId = null;
let currentGradientType = null;
let currentBlockGradientId = null;
let currentButtonGradientId = null;
let selectedGradientControl = null;
let selectedGradientControlType = null;

// RGBA를 Hex와 Alpha로 분리
function rgbaToHexAndAlpha(rgbaString) {
    if (!rgbaString || rgbaString.startsWith('#')) {
        return { hex: rgbaString || '#ffffff', alpha: 1 };
    }
    
    const rgbaMatch = rgbaString.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
    if (rgbaMatch) {
        const r = parseInt(rgbaMatch[1]);
        const g = parseInt(rgbaMatch[2]);
        const b = parseInt(rgbaMatch[3]);
        const alpha = rgbaMatch[4] ? parseFloat(rgbaMatch[4]) : 1;
        const hex = '#' + [r, g, b].map(x => {
            const hex = x.toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        }).join('');
        return { hex, alpha };
    }
    
    return { hex: '#ffffff', alpha: 1 };
}

// Hex를 RGBA로 변환
function hexToRgba(hex, alpha = 1) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// hex를 RGB로 변환
function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? 
        `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}` : 
        '255, 255, 255';
}

// 색상 컨트롤 업데이트
function updateGradientColorControl(type) {
    const colorInput = document.getElementById(`gradient_modal_${type}_color`);
    const alphaInput = document.getElementById(`gradient_modal_${type}_alpha`);
    const display = document.getElementById(`gradient_${type}_color_display`);
    const alphaValueDisplay = document.getElementById(`gradient_${type}_alpha_value`);
    const iconDisplay = document.getElementById(`gradient_${type}_icon_display`);
    
    if (!colorInput || !alphaInput) return;
    
    const hex = colorInput.value;
    const alpha = alphaInput.value / 100;
    const rgba = hexToRgba(hex, alpha);
    
    // 표시 업데이트
    if (display) {
        display.style.background = rgba;
    }
    
    // 아이콘 표시 업데이트
    if (iconDisplay) {
        iconDisplay.style.background = rgba;
    }
    
    // 투명도 값 표시 업데이트
    if (alphaValueDisplay) {
        alphaValueDisplay.textContent = alphaInput.value + '%';
    }
    
    // 미리보기 업데이트
    if (typeof updateGradientPreview === 'function') {
        updateGradientPreview();
    }
}

// 그라데이션 미리보기 업데이트
function updateGradientPreview() {
    const startColorInput = document.getElementById('gradient_modal_start_color');
    const startAlphaInput = document.getElementById('gradient_modal_start_alpha');
    const endColorInput = document.getElementById('gradient_modal_end_color');
    const endAlphaInput = document.getElementById('gradient_modal_end_alpha');
    const angleInput = document.getElementById('gradient_modal_angle');
    
    if (!startColorInput || !startAlphaInput || !endColorInput || !endAlphaInput || !angleInput) return;
    
    const startColor = startColorInput.value;
    const startAlpha = startAlphaInput.value / 100;
    const endColor = endColorInput.value;
    const endAlpha = endAlphaInput.value / 100;
    const angle = angleInput.value || 90;
    
    const startRgba = hexToRgba(startColor, startAlpha);
    const endRgba = hexToRgba(endColor, endAlpha);
    
    // 시작 색상 위치 가져오기
    const startControl = document.getElementById('gradient_start_control');
    const startPositionStr = startControl ? (startControl.style.left || startControl.getAttribute('data-position') || '0') : '0';
    const startPosition = parseFloat(startPositionStr.toString().replace('%', '')) || 0;
    
    // 끝 색상 위치 가져오기
    const endControl = document.getElementById('gradient_end_control');
    const endPositionStr = endControl ? (endControl.style.left || endControl.getAttribute('data-position') || '100') : '100';
    const endPosition = parseFloat(endPositionStr.toString().replace('%', '')) || 100;
    
    // 중간 색상들 가져오기
    const middleColors = [];
    const middleControls = document.querySelectorAll('.gradient-middle-control');
    middleControls.forEach((control) => {
        const colorInput = control.querySelector('.gradient-middle-color-input');
        const alphaInput = control.querySelector('.gradient-middle-alpha-input');
        const positionStr = control.style.left || control.getAttribute('data-position') || '50';
        const position = parseFloat(positionStr.toString().replace('%', '')) || 50;
        if (colorInput) {
            const hex = colorInput.value;
            const alpha = alphaInput ? (alphaInput.value / 100) : 1;
            middleColors.push({ rgba: hexToRgba(hex, alpha), position });
        }
    });
    
    // 모든 색상 정렬 (시작, 중간, 끝)
    const allColors = [
        { rgba: startRgba, position: startPosition },
        ...middleColors,
        { rgba: endRgba, position: endPosition }
    ];
    allColors.sort((a, b) => a.position - b.position);
    
    // 그라데이션 문자열 생성
    let gradientString = `linear-gradient(${angle}deg`;
    allColors.forEach((color, index) => {
        if (index === 0) {
            gradientString += `, ${color.rgba} ${color.position}%`;
        } else {
            gradientString += `, ${color.rgba} ${color.position}%`;
        }
    });
    gradientString += `)`;
    
    // 미리보기 업데이트
    const preview = document.getElementById('gradient_modal_preview');
    if (preview) {
        preview.style.background = gradientString;
    }
}

// 중간 색상 추가
function addGradientMiddleColor(position = null) {
    const middleControlsContainer = document.getElementById('gradient_middle_controls');
    if (!middleControlsContainer) return;
    
    // 위치가 지정되지 않으면 중간에 추가
    if (position === null) {
        const middleCount = middleControlsContainer.children.length;
        const totalPositions = 100;
        position = Math.round((totalPositions / (middleCount + 2)) * (middleCount + 1));
    }
    
    const control = document.createElement('div');
    control.className = 'gradient-middle-control gradient-color-control';
    control.setAttribute('data-position', position);
    control.style.position = 'absolute';
    control.style.left = `${position}%`;
    control.style.display = 'none'; // 숨김 처리 (아이콘만 표시)
    
    control.innerHTML = `
        <input type="color" 
               class="gradient-middle-color-input" 
               value="#808080"
               onchange="updateGradientMiddleColor(this)">
        <input type="range" 
               class="form-range gradient-middle-alpha-input" 
               min="0" 
               max="100" 
               value="100"
               onchange="updateGradientMiddleColor(this.closest('.gradient-middle-control').querySelector('.gradient-middle-color-input'))">
        <div class="gradient-middle-color-display" style="width: 100%; height: 100%; border-radius: 2px; background: rgba(128,128,128,1);"></div>
    `;
    
    middleControlsContainer.appendChild(control);
    updateGradientMiddleColor(control.querySelector('.gradient-middle-color-input'));
    selectGradientControl(control, 'middle');
    updateGradientMiddleIcons();
}

// 그라데이션 컨트롤을 드래그 가능하게 만들기
function makeGradientControlDraggable(control) {
    let isDragging = false;
    let startX = 0;
    let startLeft = 0;
    let hasMoved = false;
    
    control.addEventListener('mousedown', function(e) {
        if (e.target.type === 'color' || e.target.type === 'range' || e.target.closest('input[type="color"]') || e.target.closest('input[type="range"]')) {
            return;
        }
        // 색상 표시 영역을 클릭한 경우 설정 패널 표시 (드래그 아님)
        const colorDisplay = e.target.closest('.gradient-color-display');
        if (colorDisplay) {
            // 색상 표시 영역 클릭 시 설정 패널 표시
            const controlType = control.id === 'gradient_start_control' ? 'start' : (control.id === 'gradient_end_control' ? 'end' : 'middle');
            if (typeof selectGradientControl === 'function') {
                selectGradientControl(control, controlType);
            }
            e.preventDefault();
            e.stopPropagation();
            return;
        }
        // handle을 클릭한 경우에만 드래그 시작
        if (e.target.classList.contains('gradient-control-handle')) {
            isDragging = true;
            window.isDraggingControl = true;
            hasMoved = false;
            control.style.cursor = 'grabbing';
            startX = e.clientX;
            startLeft = parseFloat(control.style.left) || 0;
            e.preventDefault();
            e.stopPropagation();
            return;
        }
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        const moveDistance = Math.abs(e.clientX - startX);
        if (moveDistance > 3) {
            hasMoved = true;
        }
        
        const preview = document.getElementById('gradient_modal_preview');
        if (!preview) return;
        
        const rect = preview.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const percent = Math.max(0, Math.min(100, (x / rect.width) * 100));
        
        control.style.left = `${percent}%`;
        control.setAttribute('data-position', percent);
        
        updateGradientPreview();
        e.preventDefault();
        e.stopPropagation();
    });
    
    document.addEventListener('mouseup', function(e) {
        if (isDragging) {
            isDragging = false;
            window.isDraggingControl = false;
            control.style.cursor = 'grab';
            hasMoved = false;
            e.preventDefault();
            e.stopPropagation();
        }
    });
}

// 그라데이션 컨트롤 선택
function selectGradientControl(control, type) {
    document.querySelectorAll('.gradient-color-control').forEach(c => {
        c.style.border = '';
    });
    document.querySelectorAll('.gradient-control-icon').forEach(icon => {
        icon.style.border = '';
    });
    
    selectedGradientControl = control;
    selectedGradientControlType = type;
    control.style.border = '2px solid #0d6efd';
    
    const startIcon = document.getElementById('gradient_start_icon');
    const endIcon = document.getElementById('gradient_end_icon');
    if (type === 'start' && startIcon) {
        startIcon.style.border = '2px solid #0d6efd';
        if (endIcon) endIcon.style.border = '2px solid #6c757d';
    } else if (type === 'end' && endIcon) {
        endIcon.style.border = '2px solid #0d6efd';
        if (startIcon) startIcon.style.border = '2px solid #6c757d';
    } else if (type === 'middle') {
        if (startIcon) startIcon.style.border = '2px solid #6c757d';
        if (endIcon) endIcon.style.border = '2px solid #6c757d';
    }
    
    const settingsPanel = document.getElementById('gradient_selected_control_settings');
    const removeBtn = document.getElementById('gradient_remove_selected');
    const positionControl = document.getElementById('gradient_position_control');
    
    if (type === 'start') {
        const colorInput = document.getElementById('gradient_modal_start_color');
        const alphaInput = document.getElementById('gradient_modal_start_alpha');
        if (settingsPanel && colorInput && alphaInput) {
            document.getElementById('gradient_selected_color').value = colorInput.value;
            document.getElementById('gradient_selected_alpha').value = alphaInput.value;
            document.getElementById('gradient_selected_alpha_value').textContent = alphaInput.value + '%';
            const position = parseFloat((control.style.left || '0%').toString().replace('%', '')) || 0;
            document.getElementById('gradient_selected_position').value = position;
            document.getElementById('gradient_selected_position_value').textContent = position + '%';
            settingsPanel.style.display = 'block';
            if (positionControl) positionControl.style.display = 'block';
        }
        if (removeBtn) removeBtn.style.display = 'none';
    } else if (type === 'end') {
        const colorInput = document.getElementById('gradient_modal_end_color');
        const alphaInput = document.getElementById('gradient_modal_end_alpha');
        if (settingsPanel && colorInput && alphaInput) {
            document.getElementById('gradient_selected_color').value = colorInput.value;
            document.getElementById('gradient_selected_alpha').value = alphaInput.value;
            document.getElementById('gradient_selected_alpha_value').textContent = alphaInput.value + '%';
            const position = parseFloat((control.style.left || '100%').toString().replace('%', '')) || 100;
            document.getElementById('gradient_selected_position').value = position;
            document.getElementById('gradient_selected_position_value').textContent = position + '%';
            settingsPanel.style.display = 'block';
            if (positionControl) positionControl.style.display = 'block';
        }
        if (removeBtn) removeBtn.style.display = 'none';
    } else if (type === 'middle') {
        const colorInput = control.querySelector('.gradient-middle-color-input');
        const alphaInput = control.querySelector('.gradient-middle-alpha-input');
        if (settingsPanel && colorInput) {
            document.getElementById('gradient_selected_color').value = colorInput.value;
            if (alphaInput) {
                document.getElementById('gradient_selected_alpha').value = alphaInput.value;
                document.getElementById('gradient_selected_alpha_value').textContent = alphaInput.value + '%';
            }
            const position = parseFloat((control.style.left || control.getAttribute('data-position') || '50').toString().replace('%', '')) || 50;
            document.getElementById('gradient_selected_position').value = position;
            document.getElementById('gradient_selected_position_value').textContent = position + '%';
            settingsPanel.style.display = 'block';
            if (positionControl) positionControl.style.display = 'block';
        }
        if (removeBtn) removeBtn.style.display = 'block';
    }
    
    updateGradientMiddleIcons();
}

// 선택된 컨트롤 업데이트
function updateSelectedGradientControl() {
    if (!selectedGradientControl || !selectedGradientControlType) return;
    
    const color = document.getElementById('gradient_selected_color').value;
    const alpha = document.getElementById('gradient_selected_alpha').value;
    document.getElementById('gradient_selected_alpha_value').textContent = alpha + '%';
    
    if (selectedGradientControlType === 'start') {
        document.getElementById('gradient_modal_start_color').value = color;
        document.getElementById('gradient_modal_start_alpha').value = alpha;
        if (typeof updateGradientColorControl === 'function') {
            updateGradientColorControl('start');
        }
    } else if (selectedGradientControlType === 'end') {
        document.getElementById('gradient_modal_end_color').value = color;
        document.getElementById('gradient_modal_end_alpha').value = alpha;
        if (typeof updateGradientColorControl === 'function') {
            updateGradientColorControl('end');
        }
    } else if (selectedGradientControlType === 'middle') {
        const colorInput = selectedGradientControl.querySelector('.gradient-middle-color-input');
        const alphaInput = selectedGradientControl.querySelector('.gradient-middle-alpha-input');
        if (colorInput && alphaInput) {
            colorInput.value = color;
            alphaInput.value = alpha;
            if (typeof updateGradientMiddleColor === 'function') {
                updateGradientMiddleColor(colorInput);
            }
        }
    }
    
    // 그라데이션 미리보기 업데이트
    if (typeof updateGradientPreview === 'function') {
        updateGradientPreview();
    }
    
    // 중간 색상 아이콘 업데이트
    if (typeof updateGradientMiddleIcons === 'function') {
        updateGradientMiddleIcons();
    }
}

// 위치 조정 슬라이더 업데이트
function updateSelectedGradientControlPosition() {
    if (!selectedGradientControl || !selectedGradientControlType) return;
    
    const position = document.getElementById('gradient_selected_position').value;
    document.getElementById('gradient_selected_position_value').textContent = position + '%';
    
    selectedGradientControl.style.left = `${position}%`;
    selectedGradientControl.setAttribute('data-position', position);
    
    // 시작/끝 색상 아이콘도 업데이트
    if (selectedGradientControlType === 'start') {
        const startIcon = document.getElementById('gradient_start_icon');
        if (startIcon) {
            let positionLabel = startIcon.querySelector('small');
            if (!positionLabel) {
                positionLabel = document.createElement('small');
                positionLabel.style.cssText = 'position: absolute; bottom: -18px; left: 50%; transform: translateX(-50%); font-size: 0.7rem; white-space: nowrap;';
                startIcon.style.position = 'relative';
                startIcon.appendChild(positionLabel);
            }
            positionLabel.textContent = position + '%';
        }
    } else if (selectedGradientControlType === 'end') {
        const endIcon = document.getElementById('gradient_end_icon');
        if (endIcon) {
            let positionLabel = endIcon.querySelector('small');
            if (!positionLabel) {
                positionLabel = document.createElement('small');
                positionLabel.style.cssText = 'position: absolute; bottom: -18px; left: 50%; transform: translateX(-50%); font-size: 0.7rem; white-space: nowrap;';
                endIcon.style.position = 'relative';
                endIcon.appendChild(positionLabel);
            }
            positionLabel.textContent = position + '%';
        }
    }
    
    // 그라데이션 미리보기 업데이트
    if (typeof updateGradientPreview === 'function') {
        updateGradientPreview();
    }
    
    // 중간 색상 아이콘 업데이트
    updateGradientMiddleIcons();
}

// 아이콘 클릭 시 컨트롤 선택
function selectGradientIcon(type) {
    const control = document.getElementById(`gradient_${type}_control`);
    if (control) {
        selectGradientControl(control, type);
    }
}

// 중간 색상 아이콘 업데이트
function updateGradientMiddleIcons() {
    const middleIconsContainer = document.getElementById('gradient_middle_icons');
    if (!middleIconsContainer) return;
    
    middleIconsContainer.innerHTML = '';
    
    const middleControls = document.querySelectorAll('.gradient-middle-control');
    const controlsArray = Array.from(middleControls);
    
    // 위치 순서대로 정렬
    controlsArray.sort((a, b) => {
        const posA = parseFloat((a.style.left || a.getAttribute('data-position') || '50').toString().replace('%', '')) || 50;
        const posB = parseFloat((b.style.left || b.getAttribute('data-position') || '50').toString().replace('%', '')) || 50;
        return posA - posB;
    });
    
    controlsArray.forEach((control, index) => {
        const colorDisplay = control.querySelector('.gradient-middle-color-display');
        const position = parseFloat((control.style.left || control.getAttribute('data-position') || '50').toString().replace('%', '')) || 50;
        const color = colorDisplay ? window.getComputedStyle(colorDisplay).background : 'rgba(128,128,128,1)';
        
        const icon = document.createElement('div');
        icon.className = 'gradient-middle-icon gradient-control-icon';
        icon.style.cssText = 'width: 60px; height: 40px; border: 2px solid #6c757d; border-radius: 4px; background: white; padding: 2px; cursor: pointer; position: relative;';
        icon.setAttribute('data-control-index', index);
        icon.onclick = function() {
            selectGradientControl(control, 'middle');
        };
        
        const iconDisplay = document.createElement('div');
        iconDisplay.style.cssText = 'width: 100%; height: 100%; border-radius: 2px; background: ' + color + ';';
        icon.appendChild(iconDisplay);
        
        const positionLabel = document.createElement('small');
        positionLabel.style.cssText = 'position: absolute; bottom: -18px; left: 50%; transform: translateX(-50%); font-size: 0.7rem; white-space: nowrap;';
        positionLabel.textContent = position + '%';
        icon.appendChild(positionLabel);
        
        middleIconsContainer.appendChild(icon);
    });
}

// 선택된 컨트롤 제거
function removeSelectedGradientControl() {
    if (selectedGradientControl && selectedGradientControlType === 'middle') {
        selectedGradientControl.remove();
        selectedGradientControl = null;
        selectedGradientControlType = null;
        document.getElementById('gradient_selected_control_settings').style.display = 'none';
        updateGradientPreview();
        updateGradientMiddleIcons();
    }
}

// 중간 색상 업데이트
function updateGradientMiddleColor(colorInput) {
    const control = colorInput.closest('.gradient-middle-control');
    const alphaInput = control.querySelector('.gradient-middle-alpha-input');
    const display = control.querySelector('.gradient-middle-color-display');
    
    const hex = colorInput.value;
    const alpha = alphaInput ? (alphaInput.value / 100) : 1;
    const rgba = hexToRgba(hex, alpha);
    
    if (display) {
        display.style.background = rgba;
    }
    
    if (selectedGradientControl === control && selectedGradientControlType === 'middle') {
        document.getElementById('gradient_selected_color').value = hex;
        if (alphaInput) {
            document.getElementById('gradient_selected_alpha').value = alphaInput.value;
            document.getElementById('gradient_selected_alpha_value').textContent = alphaInput.value + '%';
        }
    }
    
    updateGradientPreview();
    updateGradientMiddleIcons();
}

// 블록 그라데이션 모달 열기
function openBlockGradientModal(blockId) {
    // 컨테이너 그라데이션 ID 초기화
    currentGradientContainerId = null;
    currentGradientType = null;
    currentBlockGradientId = blockId;
    
    // 현재 값 가져오기
    const startColorValue = document.getElementById(`${blockId}_gradient_start`)?.value || 
                           document.getElementById(`${blockId}_background_gradient_start`)?.value || '#ffffff';
    const endColorValue = document.getElementById(`${blockId}_gradient_end`)?.value || 
                         document.getElementById(`${blockId}_background_gradient_end`)?.value || '#000000';
    const angle = document.getElementById(`${blockId}_gradient_angle`)?.value || 
                  document.getElementById(`${blockId}_background_gradient_angle`)?.value || 90;
    
    // RGBA 파싱
    const startParsed = rgbaToHexAndAlpha(startColorValue);
    const endParsed = rgbaToHexAndAlpha(endColorValue);
    
    // 모달에 값 설정 (null 체크 추가)
    const startColorInput = document.getElementById('gradient_modal_start_color');
    const startAlphaInput = document.getElementById('gradient_modal_start_alpha');
    const endColorInput = document.getElementById('gradient_modal_end_color');
    const endAlphaInput = document.getElementById('gradient_modal_end_alpha');
    const angleInput = document.getElementById('gradient_modal_angle');
    const angleSliderInput = document.getElementById('gradient_modal_angle_slider');
    
    if (startColorInput) startColorInput.value = startParsed.hex;
    if (startAlphaInput) {
        startAlphaInput.value = Math.round(startParsed.alpha * 100);
        const startAlphaValueDisplay = document.getElementById('gradient_start_alpha_value');
        if (startAlphaValueDisplay) {
            startAlphaValueDisplay.textContent = Math.round(startParsed.alpha * 100) + '%';
        }
    }
    if (endColorInput) endColorInput.value = endParsed.hex;
    if (endAlphaInput) {
        endAlphaInput.value = Math.round(endParsed.alpha * 100);
        const endAlphaValueDisplay = document.getElementById('gradient_end_alpha_value');
        if (endAlphaValueDisplay) {
            endAlphaValueDisplay.textContent = Math.round(endParsed.alpha * 100) + '%';
        }
    }
    if (angleInput) angleInput.value = angle;
    if (angleSliderInput) angleSliderInput.value = angle;
    
    // 색상 컨트롤 업데이트
    if (typeof updateGradientColorControl === 'function') {
        updateGradientColorControl('start');
        updateGradientColorControl('end');
    }
    
    // 시작/끝 컨트롤에 드래그 기능 추가
    const startControl = document.getElementById('gradient_start_control');
    const endControl = document.getElementById('gradient_end_control');
    if (startControl) {
        if (typeof makeGradientControlDraggable === 'function') {
            makeGradientControlDraggable(startControl);
        }
        // 클릭 이벤트는 색상 표시 영역에서 처리
    }
    if (endControl) {
        if (typeof makeGradientControlDraggable === 'function') {
            makeGradientControlDraggable(endControl);
        }
        // 클릭 이벤트는 makeGradientControlDraggable에서 처리
    }
    
    // 그라데이션 바 클릭 이벤트 제거 (아이콘 방식으로 변경)
    
    // 중간 색상 초기화
    const middleControlsContainer = document.getElementById('gradient_middle_controls');
    if (middleControlsContainer) {
        middleControlsContainer.innerHTML = '';
    }
    
    // 미리보기 업데이트
    if (typeof updateGradientPreview === 'function') {
        updateGradientPreview();
    }
    
    // 설정 패널 숨기기
    const settingsPanel = document.getElementById('gradient_selected_control_settings');
    if (settingsPanel) {
        settingsPanel.style.display = 'none';
    }
    selectedGradientControl = null;
    selectedGradientControlType = null;
    
    // 모달 표시
    const modalElement = document.getElementById('gradientModal');
    if (modalElement) {
        // 기존 모달 인스턴스 확인 및 제거
        const existingModal = bootstrap.Modal.getInstance(modalElement);
        if (existingModal) {
            existingModal.dispose();
        }
        
        // z-index를 높게 설정하여 다른 모달 위에 표시
        modalElement.style.zIndex = '1060';
        
        // 새 모달 인스턴스 생성 및 표시 (backdrop 없이)
        // 위젯 설정 모달이 이미 열려있으므로 backdrop을 생성하지 않음
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: false
        });
        modal.show();
        
        // 모달이 완전히 표시된 후 z-index 재설정
        modalElement.addEventListener('shown.bs.modal', function() {
            modalElement.style.zIndex = '1060';
        }, { once: true });
    }
}

// 블록 그라데이션 저장
function saveBlockGradient() {
    if (!currentBlockGradientId) return;
    
    const startColor = document.getElementById('gradient_modal_start_color').value;
    const startAlpha = document.getElementById('gradient_modal_start_alpha').value;
    const endColor = document.getElementById('gradient_modal_end_color').value;
    const endAlpha = document.getElementById('gradient_modal_end_alpha').value;
    const angle = document.getElementById('gradient_modal_angle').value;
    
    const middleColors = [];
    const middleControls = document.querySelectorAll('.gradient-middle-control');
    middleControls.forEach(control => {
        const colorInput = control.querySelector('.gradient-middle-color-input');
        const alphaInput = control.querySelector('.gradient-middle-alpha-input');
        const position = parseFloat(control.getAttribute('data-position')) || parseFloat(control.style.left) || 50;
        if (colorInput) {
            const color = colorInput.value;
            const alpha = alphaInput ? (alphaInput.value / 100) : 1;
            middleColors.push({ color, alpha, position });
        }
    });
    
    middleColors.sort((a, b) => a.position - b.position);
    
    let gradientString = '';
    if (middleColors.length === 0) {
        gradientString = `linear-gradient(${angle}deg, rgba(${hexToRgb(startColor)}, ${startAlpha / 100}), rgba(${hexToRgb(endColor)}, ${endAlpha / 100}))`;
    } else {
        const colors = [
            `rgba(${hexToRgb(startColor)}, ${startAlpha / 100}) 0%`,
            ...middleColors.map(m => `rgba(${hexToRgb(m.color)}, ${m.alpha / 100}) ${m.position}%`),
            `rgba(${hexToRgb(endColor)}, ${endAlpha / 100}) 100%`
        ];
        gradientString = `linear-gradient(${angle}deg, ${colors.join(', ')})`;
    }
    
    // 블록 그라데이션 값 저장 - 두 가지 필드명 모두 확인
    const startInput = document.getElementById(`${currentBlockGradientId}_gradient_start`) || 
                      document.getElementById(`${currentBlockGradientId}_background_gradient_start`);
    const endInput = document.getElementById(`${currentBlockGradientId}_gradient_end`) || 
                    document.getElementById(`${currentBlockGradientId}_background_gradient_end`);
    const angleInput = document.getElementById(`${currentBlockGradientId}_gradient_angle`) || 
                      document.getElementById(`${currentBlockGradientId}_background_gradient_angle`);
    const preview = document.getElementById(`${currentBlockGradientId}_gradient_preview`) ||
                   document.getElementById(`${currentBlockGradientId}_background_gradient_preview`);
    
    // RGBA 형식으로 저장 (alpha 포함)
    const startRgba = hexToRgba(startColor, startAlpha / 100);
    const endRgba = hexToRgba(endColor, endAlpha / 100);
    
    if (startInput) startInput.value = startRgba;
    if (endInput) endInput.value = endRgba;
    if (angleInput) angleInput.value = angle;
    if (preview) {
        preview.style.background = gradientString;
    }
    
    // 모달 닫기 - backdrop 명시적으로 제거
    const modalElement = document.getElementById('gradientModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        // backdrop 없이 모달을 열었으므로 backdrop 제거 로직 불필요
        modal.hide();
    }
}

// 그라데이션 저장
function saveGradient() {
    if (currentBlockGradientId) {
        saveBlockGradient();
        return;
    }
}
</script>
@endpush
@endsection

<style>
/* 그라데이션 모달이 다른 모달 위에 표시되도록 z-index 설정 */
#gradientModal {
    z-index: 1060 !important;
}
#gradientModal.show {
    z-index: 1060 !important;
}
.modal-backdrop.show ~ .modal-backdrop.show {
    z-index: 1059 !important;
}
</style>

<!-- 그라데이션 설정 모달 -->
<div class="modal fade" id="gradientModal" tabindex="-1" aria-labelledby="gradientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradientModalLabel">그라데이션 설정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- 그라데이션 미리보기 바 -->
                <div class="mb-4" style="position: relative;">
                    <div id="gradient_modal_preview" 
                         style="width: 100%; height: 120px; border: 1px solid #dee2e6; border-radius: 4px; background: linear-gradient(90deg, rgba(255,255,255,1), rgba(0,0,0,1)); position: relative; overflow: hidden; cursor: crosshair;">
                    </div>
                    <!-- 숨겨진 색상 컨트롤들 (데이터 관리용) -->
                    <div style="display: none;">
                        <!-- 시작 색상 컨트롤 -->
                        <div id="gradient_start_control" class="gradient-color-control" data-position="0" style="position: absolute; left: 0%;">
                            <input type="color" 
                                   id="gradient_modal_start_color" 
                                   value="#ffffff"
                                   onchange="updateGradientColorControl('start')">
                            <input type="hidden" 
                                   id="gradient_modal_start_alpha" 
                                   value="100">
                        </div>
                        
                        <!-- 중간 색상 컨트롤들 -->
                        <div id="gradient_middle_controls"></div>
                        
                        <!-- 끝 색상 컨트롤 -->
                        <div id="gradient_end_control" class="gradient-color-control" data-position="100" style="position: absolute; left: 100%;">
                            <input type="color" 
                                   id="gradient_modal_end_color" 
                                   value="#000000"
                                   onchange="updateGradientColorControl('end')">
                            <input type="hidden" 
                                   id="gradient_modal_end_alpha" 
                                   value="100">
                        </div>
                    </div>
                    <!-- 그라데이션 바 아래 컨트롤 영역 -->
                    <div id="gradient_control_panel" style="margin-top: 10px;">
                        <!-- 시작/끝 색상 아이콘 표시 영역 -->
                        <div id="gradient_start_end_controls" style="display: flex; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;">
                            <!-- 시작 색상 아이콘 -->
                            <div id="gradient_start_icon" class="gradient-control-icon" style="width: 60px; height: 40px; border: 2px solid #6c757d; border-radius: 4px; background: white; padding: 2px; cursor: pointer;" onclick="selectGradientIcon('start')">
                                <div id="gradient_start_icon_display" style="width: 100%; height: 100%; border-radius: 2px; background: #ffffff;"></div>
                            </div>
                            <!-- 끝 색상 아이콘 -->
                            <div id="gradient_end_icon" class="gradient-control-icon" style="width: 60px; height: 40px; border: 2px solid #6c757d; border-radius: 4px; background: white; padding: 2px; cursor: pointer;" onclick="selectGradientIcon('end')">
                                <div id="gradient_end_icon_display" style="width: 100%; height: 100%; border-radius: 2px; background: #000000;"></div>
                            </div>
                        </div>
                        <!-- 중간 색상 아이콘 표시 영역 -->
                        <div id="gradient_middle_icons" style="display: flex; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;"></div>
                        
                        <!-- 선택된 색상 컨트롤의 설정 -->
                        <div id="gradient_selected_control_settings" style="display: none;">
                            <div class="mb-2">
                                <label class="form-label small">색상</label>
                                <input type="color" 
                                       id="gradient_selected_color" 
                                       onchange="updateSelectedGradientControl()"
                                       class="form-control form-control-color">
                            </div>
                            <div class="mb-2" id="gradient_position_control" style="display: none;">
                                <label class="form-label small">위치</label>
                                <input type="range" 
                                       class="form-range" 
                                       id="gradient_selected_position" 
                                       min="0" 
                                       max="100" 
                                       value="0"
                                       onchange="updateSelectedGradientControlPosition()">
                                <div class="d-flex justify-content-between">
                                    <small style="font-size: 0.7rem;">0%</small>
                                    <small id="gradient_selected_position_value" style="font-size: 0.7rem;">0%</small>
                                    <small style="font-size: 0.7rem;">100%</small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">투명도</label>
                                <input type="range" 
                                       class="form-range" 
                                       id="gradient_selected_alpha" 
                                       min="0" 
                                       max="100" 
                                       value="100"
                                       onchange="updateSelectedGradientControl()">
                                <div class="d-flex justify-content-between">
                                    <small style="font-size: 0.7rem;">0%</small>
                                    <small id="gradient_selected_alpha_value" style="font-size: 0.7rem;">100%</small>
                                    <small style="font-size: 0.7rem;">100%</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" id="gradient_remove_selected" onclick="removeSelectedGradientControl()" style="display: none;">
                                <i class="bi bi-x"></i> 색상 제거
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 중간 색상 추가 버튼 -->
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="addGradientMiddleColor()">
                        <i class="bi bi-plus"></i> 중간 색상 추가
                    </button>
                    <small class="text-muted d-block mt-1">버튼을 눌러 중간 색상을 추가할 수 있습니다. 위치 슬라이더로 중간 색상의 위치를 조정할 수 있습니다.</small>
                </div>
                
                <!-- 각도 -->
                <div class="mb-3">
                    <label for="gradient_modal_angle" class="form-label">각도 <i class="bi bi-compass" style="font-size: 0.9rem;" title="각도"></i></label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" 
                               class="form-range flex-grow-1" 
                               id="gradient_modal_angle_slider" 
                               min="0" 
                               max="360" 
                               value="90"
                               onchange="document.getElementById('gradient_modal_angle').value = this.value; updateGradientPreview();">
                        <input type="number" 
                               class="form-control" 
                               id="gradient_modal_angle" 
                               value="90"
                               min="0"
                               max="360"
                               step="1"
                               style="width: 80px;"
                               onchange="document.getElementById('gradient_modal_angle_slider').value = this.value; updateGradientPreview();">
                        <span class="text-muted">도</span>
                    </div>
                    <small class="text-muted">0도: 좌→우, 90도: 상→하, 180도: 우→좌, 270도: 하→상</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveGradient()">저장</button>
            </div>
        </div>
    </div>
</div>

