{{-- 위젯 추가 폼 Partial --}}
@php
    $availableTypes = $availableTypes ?? [];
@endphp
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
            <option value="color" selected>컬러</option>
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
                   id="widget_block_background_gradient_start"
                   name="block_background_gradient_start" 
                   value="#ffffff">
            <input type="hidden" 
                   id="widget_block_background_gradient_end"
                   name="block_background_gradient_end" 
                   value="#000000">
            <input type="hidden" 
                   id="widget_block_background_gradient_angle"
                   name="block_background_gradient_angle" 
                   value="90">
        </div>
        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
    </div>
    <div class="mb-3">
        <label for="widget_block_font_color" class="form-label">폰트 컬러</label>
        <input type="color" 
               class="form-control form-control-color" 
               id="widget_block_font_color" 
               name="block_font_color" 
               value="#ffffff">
    </div>
    <div class="mb-3" id="widget_block_image_container" style="display: none;">
        <label class="form-label">배경 이미지</label>
        <div class="d-flex align-items-center gap-2 mb-2">
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
        <div class="row align-items-center mt-2">
            <div class="col-auto">
                <label for="widget_block_background_image_alpha" class="form-label mb-0">투명도 (%)</label>
            </div>
            <div class="col-auto">
                <input type="number" 
                       class="form-control form-control-sm" 
                       id="widget_block_background_image_alpha" 
                       name="block_background_image_alpha"
                       min="0" 
                       max="100" 
                       value="100"
                       style="width: 80px;">
            </div>
        </div>
        <small class="text-muted">0~100 사이의 값을 입력하세요. 0은 완전 투명, 100은 불투명입니다.</small>
        <div class="form-check mt-2">
            <input class="form-check-input" 
                   type="checkbox" 
                   id="widget_block_background_image_full_width" 
                   name="block_background_image_full_width">
            <label class="form-check-label" for="widget_block_background_image_full_width">
                이미지 가로 100% (비율 유지)
            </label>
        </div>
        <small class="text-muted">활성화 시 이미지가 블록 너비에 맞게 확장되고 높이는 비율에 맞게 자동 조절됩니다.</small>
    </div>
    <div class="mb-3">
        <label for="widget_block_padding_top" class="form-label">상단 여백 (px)</label>
        <input type="number" 
               class="form-control" 
               id="widget_block_padding_top" 
               name="block_padding_top" 
               value="20"
               min="0"
               max="200"
               step="1"
               placeholder="20">
        <small class="text-muted">블록 상단 여백을 입력하세요 (0~200).</small>
    </div>
    <div class="mb-3">
        <label for="widget_block_padding_bottom" class="form-label">하단 여백 (px)</label>
        <input type="number" 
               class="form-control" 
               id="widget_block_padding_bottom" 
               name="block_padding_bottom" 
               value="20"
               min="0"
               max="200"
               step="1"
               placeholder="20">
        <small class="text-muted">블록 하단 여백을 입력하세요 (0~200).</small>
    </div>
    <div class="mb-3">
        <label for="widget_block_padding_left" class="form-label">좌측 여백 (px)</label>
        <input type="number" 
               class="form-control" 
               id="widget_block_padding_left" 
               name="block_padding_left" 
               value="20"
               min="0"
               max="200"
               step="1"
               placeholder="20">
        <small class="text-muted">블록 좌측 여백을 입력하세요 (0~200).</small>
    </div>
    <div class="mb-3">
        <label for="widget_block_padding_right" class="form-label">우측 여백 (px)</label>
        <input type="number" 
               class="form-control" 
               id="widget_block_padding_right" 
               name="block_padding_right" 
               value="20"
               min="0"
               max="200"
               step="1"
               placeholder="20">
        <small class="text-muted">블록 우측 여백을 입력하세요 (0~200).</small>
    </div>
    <div class="mb-3">
        <label for="widget_block_title_content_gap" class="form-label">제목-내용 여백 (px)</label>
        <input type="number" 
               class="form-control" 
               id="widget_block_title_content_gap" 
               name="block_title_content_gap" 
               value="8"
               min="0"
               max="100"
               step="1"
               placeholder="8">
        <small class="text-muted">제목과 내용 사이의 여백을 입력하세요 (0~100).</small>
    </div>
    <div class="mb-3">
        <label class="form-label">버튼 관리</label>
        <div id="widget_block_buttons_list">
            <!-- 버튼들이 여기에 동적으로 추가됨 -->
        </div>
        <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addBlockButton()">
            <i class="bi bi-plus-circle me-1"></i>버튼 추가
        </button>
    </div>
    <div class="mb-3">
        <label for="widget_block_button_top_margin" class="form-label">버튼 상단 여백 (px)</label>
        <input type="number" 
               class="form-control" 
               id="widget_block_button_top_margin" 
               name="block_button_top_margin" 
               value="12"
               min="0"
               max="100"
               step="1"
               placeholder="12">
        <small class="text-muted">버튼과 위 요소 사이의 여백을 입력하세요 (0~100).</small>
    </div>
    <div class="mb-3" id="widget_block_link_container">
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
                   onchange="handleImageSlideModeChange('single')">
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
                   onchange="handleImageSlideModeChange('infinite')">
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
    <div class="mb-3" id="widget_image_slide_background_container" style="display: none;">
        <label for="widget_image_slide_background_type" class="form-label">배경 설정</label>
        <div class="mb-2">
            <select class="form-select" id="widget_image_slide_background_type" name="image_slide_background_type" onchange="handleImageSlideBackgroundTypeChange()">
                <option value="none">배경 없음</option>
                <option value="color">배경색 지정</option>
            </select>
        </div>
        <div id="widget_image_slide_background_color_container" style="display: none;">
            <input type="color" 
                   class="form-control form-control-color" 
                   id="widget_image_slide_background_color" 
                   name="image_slide_background_color" 
                   value="#ffffff"
                   title="배경색 선택">
            <small class="text-muted">무한루프 슬라이드의 배경색을 선택하세요.</small>
        </div>
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
<div class="mb-3" id="widget_contact_form_container" style="display: none;">
    <label for="widget_contact_form_id" class="form-label">컨텍트폼 선택</label>
    <select class="form-select" id="widget_contact_form_id" name="contact_form_id" required>
        <option value="">선택하세요</option>
        @if(isset($site))
            @foreach(\App\Models\ContactForm::where('site_id', $site->id)->orderBy('created_at', 'desc')->get() as $contactForm)
                <option value="{{ $contactForm->id }}">{{ $contactForm->title ?? '' }}</option>
            @endforeach
        @endif
    </select>
    <small class="text-muted">사용할 컨텍트폼을 선택하세요.</small>
</div>
<div class="mb-3" id="widget_map_container" style="display: none;">
    <label for="widget_map_id" class="form-label">지도 선택</label>
    <select class="form-select" id="widget_map_id" name="map_id" required>
        <option value="">선택하세요</option>
        @if(isset($site))
            @php
                $masterSite = \App\Models\Site::getMasterSite();
                $googleApiKey = $masterSite ? \Illuminate\Support\Facades\DB::table('site_settings')->where('site_id', $masterSite->id)->where('key', 'map_api_google_key')->value('value') : null;
                $naverApiKey = $masterSite ? \Illuminate\Support\Facades\DB::table('site_settings')->where('site_id', $masterSite->id)->where('key', 'map_api_naver_key')->value('value') : null;
                $kakaoApiKey = $masterSite ? \Illuminate\Support\Facades\DB::table('site_settings')->where('site_id', $masterSite->id)->where('key', 'map_api_kakao_key')->value('value') : null;
            @endphp
            @foreach(\App\Models\Map::where('site_id', $site->id)->orderBy('created_at', 'desc')->get() as $map)
                @php
                    $hasApiKey = false;
                    if ($map->map_type === 'google' && !empty($googleApiKey)) {
                        $hasApiKey = true;
                    } elseif ($map->map_type === 'naver' && !empty($naverApiKey)) {
                        $hasApiKey = true;
                    } elseif ($map->map_type === 'kakao' && !empty($kakaoApiKey)) {
                        $hasApiKey = true;
                    }
                @endphp
                @if($hasApiKey)
                    <option value="{{ $map->id }}">{{ $map->name ?? '' }}</option>
                @endif
            @endforeach
        @endif
    </select>
    <small class="text-muted">사용할 지도를 선택하세요.</small>
</div>
<div class="mb-3" id="widget_toggle_menu_container" style="display: none;">
    <label for="widget_toggle_menu_id" class="form-label">토글 메뉴 선택</label>
    <select class="form-select" id="widget_toggle_menu_id" name="toggle_menu_id">
        <option value="">선택하세요</option>
        <!-- 토글 메뉴 옵션들이 여기에 동적으로 추가됨 -->
    </select>
    <small class="text-muted">표시할 토글 메뉴를 선택하세요.</small>
</div>
<div class="mb-3" id="widget_countdown_container" style="display: none;">
    <div class="mb-3">
        <label for="widget_countdown_title" class="form-label">카운트 제목</label>
        <input type="text" 
               class="form-control" 
               id="widget_countdown_title" 
               name="countdown_title" 
               placeholder="카운트 제목을 입력하세요 (선택사항)">
    </div>
    <div class="mb-3">
        <label for="widget_countdown_content" class="form-label">내용</label>
        <textarea class="form-control" 
                  id="widget_countdown_content" 
                  name="countdown_content" 
                  rows="3"
                  placeholder="내용을 입력하세요 (선택사항)"></textarea>
    </div>
    <div class="mb-3">
        <label for="widget_countdown_background_type" class="form-label">배경</label>
        <select class="form-select" id="widget_countdown_background_type" name="countdown_background_type" onchange="handleCountdownBackgroundTypeChange()">
            <option value="none">배경 없음</option>
            <option value="color">단색</option>
            <option value="gradient">그라데이션</option>
        </select>
    </div>
    <div class="mb-3" id="widget_countdown_color_container" style="display: none;">
        <label for="widget_countdown_background_color" class="form-label">배경 색상</label>
        <input type="color" 
               class="form-control form-control-color mb-2" 
               id="widget_countdown_background_color" 
               name="countdown_background_color" 
               value="#007bff">
        <div class="row align-items-center">
            <div class="col-auto">
                <label for="widget_countdown_background_opacity" class="form-label mb-0">투명도 (%)</label>
            </div>
            <div class="col-auto">
                <input type="number" 
                       class="form-control form-control-sm" 
                       id="widget_countdown_background_opacity" 
                       name="countdown_background_opacity"
                       min="0" 
                       max="100" 
                       value="100"
                       style="width: 80px;">
            </div>
        </div>
        <small class="text-muted">0~100 사이의 값을 입력하세요. 0은 완전 투명, 100은 불투명입니다.</small>
    </div>
    <div class="mb-3" id="widget_countdown_gradient_container" style="display: none;">
        <label class="form-label">그라데이션 설정</label>
        <div class="d-flex align-items-center gap-2 mb-2">
            <div id="widget_countdown_gradient_preview" 
                 style="width: 120px; height: 38px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; background: linear-gradient(90deg, #ffffff, #000000);"
                 onclick="openCountdownGradientModal('widget_countdown')"
                 title="그라데이션 설정">
            </div>
            <input type="hidden" 
                   id="widget_countdown_background_gradient_start"
                   name="countdown_background_gradient_start" 
                   value="#ffffff">
            <input type="hidden" 
                   id="widget_countdown_background_gradient_end"
                   name="countdown_background_gradient_end" 
                   value="#000000">
            <input type="hidden" 
                   id="widget_countdown_background_gradient_angle"
                   name="countdown_background_gradient_angle" 
                   value="90">
        </div>
        <div class="row align-items-center mb-2">
            <div class="col-auto">
                <label for="widget_countdown_gradient_opacity" class="form-label mb-0">투명도 (%)</label>
            </div>
            <div class="col-auto">
                <input type="number" 
                       class="form-control form-control-sm" 
                       id="widget_countdown_gradient_opacity" 
                       name="countdown_gradient_opacity"
                       min="0" 
                       max="100" 
                       value="100"
                       style="width: 80px;">
            </div>
        </div>
        <small class="text-muted">미리보기를 클릭하여 그라데이션을 설정하세요</small>
    </div>
    <div class="mb-3">
        <label for="widget_countdown_font_color" class="form-label">폰트 색상</label>
        <input type="color" 
               class="form-control form-control-color" 
               id="widget_countdown_font_color" 
               name="countdown_font_color" 
               value="#333333">
    </div>
    <div class="mb-3">
        <label for="widget_countdown_type" class="form-label">카운트 타입</label>
        <select class="form-select" id="widget_countdown_type" name="countdown_type" onchange="handleCountdownTypeChange()">
            <option value="dday">D-day 카운트</option>
            <option value="number">숫자카운트</option>
        </select>
    </div>
    <!-- D-day 카운트 설정 -->
    <div id="widget_countdown_dday_container">
        <div class="mb-3">
            <label for="widget_countdown_target_date" class="form-label">목표 날짜 및 시간</label>
            <input type="datetime-local" 
                   class="form-control" 
                   id="widget_countdown_target_date" 
                   name="countdown_target_date">
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="widget_countdown_dday_animation" 
                       name="countdown_dday_animation_enabled" 
                       value="1">
                <label class="form-check-label" for="widget_countdown_dday_animation">
                    D-day 애니메이션 활성화
                </label>
                <i class="bi bi-question-circle text-muted ms-2" 
                   data-bs-toggle="tooltip" 
                   data-bs-placement="top" 
                   title="활성화 시 카운트다운 숫자가 빠르게 변경되다가 멈춥니다." 
                   style="cursor: help; font-size: 0.9rem;"></i>
            </div>
            <small class="text-muted">활성화 시 카운트다운 숫자가 빠르게 변경되다가 멈추는 애니메이션을 표시합니다.</small>
        </div>
    </div>
    <!-- 숫자 카운트 설정 -->
    <div id="widget_countdown_number_container" style="display: none;">
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="widget_countdown_animation" 
                       name="countdown_animation" 
                       checked>
                <label class="form-check-label" for="widget_countdown_animation">
                    숫자 애니메이션 활성화
                </label>
            </div>
            <small class="text-muted">슬롯처럼 0~9까지 돌아가다가 목표 숫자로 멈추는 애니메이션을 표시합니다.</small>
        </div>
        <div id="widget_countdown_number_items">
            <!-- 숫자 카운트 항목들이 여기에 동적으로 추가됨 -->
        </div>
        <button type="button" class="btn btn-primary w-100" onclick="addCountdownNumberItem()">
            <i class="bi bi-plus-circle me-2"></i>항목 추가
        </button>
    </div>
</div>

