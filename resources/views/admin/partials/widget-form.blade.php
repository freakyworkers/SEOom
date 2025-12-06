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
        위젯 제목
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
            </select>
        </div>
        <div class="col-6">
            <label for="widget_gallery_rows" class="form-label">세로 줄수</label>
            <select class="form-select" id="widget_gallery_rows" name="gallery_rows">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3" selected>3</option>
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
            
            <input type="radio" class="btn-check" name="gallery_slide_direction" id="gallery_direction_up" value="up">
            <label class="btn btn-outline-primary" for="gallery_direction_up">
                <i class="bi bi-arrow-up"></i> 상
            </label>
            
            <input type="radio" class="btn-check" name="gallery_slide_direction" id="gallery_direction_down" value="down">
            <label class="btn btn-outline-primary" for="gallery_direction_down">
                <i class="bi bi-arrow-down"></i> 하
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
              placeholder="HTML 코드를 입력하세요"></textarea>
    <small class="text-muted">사이드바에 표시할 HTML 코드를 입력하세요.</small>
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
        <label for="widget_block_background_type" class="form-label">배경</label>
        <select class="form-select" id="widget_block_background_type" name="block_background_type" onchange="handleBlockBackgroundTypeChange()">
            <option value="color">컬러</option>
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
            <option value="60">60px</option>
            <option value="70">70px</option>
            <option value="80">80px</option>
            <option value="90">90px</option>
            <option value="100">100px</option>
            <option value="120">120px</option>
            <option value="150">150px</option>
            <option value="200">200px</option>
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
                   onchange="handleBlockButtonToggle()">
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
            <label for="widget_block_button_color" class="form-label">버튼 컬러</label>
            <input type="color" 
                   class="form-control form-control-color" 
                   id="widget_block_button_color" 
                   name="block_button_color" 
                   value="#007bff">
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
        <div class="btn-group w-100" role="group">
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

