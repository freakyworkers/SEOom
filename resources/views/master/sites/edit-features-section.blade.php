            <hr class="my-4">

            <!-- 사이트별 기능 설정 -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="bi bi-sliders me-2"></i>사이트별 기능 설정</h5>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>참고:</strong> 아래 체크박스를 통해 해당 사이트에만 기능을 추가하거나 제거할 수 있습니다. 
                    플랜에 포함된 기능은 기본적으로 활성화되어 있으며, 마스터 관리자가 필요에 따라 개별 사이트에 기능을 추가할 수 있습니다.
                </div>

                <!-- 대 메뉴 기능 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>대 메뉴 기능</h6>
                    </div>
                    <div class="card-body">
                        @php
                            // 항상 직접 가져오기 (전달된 값이 없거나 잘못된 경우 대비)
                            // SiteSetting의 getValueAttribute가 이미 JSON을 디코딩하므로 배열로 반환됨
                            $customFeatures = $site->getSetting('custom_features', null);
                            $customFeaturesArray = null;
                            if ($customFeatures !== null) {
                                if (is_array($customFeatures)) {
                                    $customFeaturesArray = $customFeatures;
                                } elseif (is_string($customFeatures)) {
                                    // 문자열인 경우 다시 디코딩 시도
                                    $decoded = json_decode($customFeatures, true);
                                    $customFeaturesArray = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null;
                                }
                            }
                            
                            // 디버깅: 로드된 값 확인
                            \Log::info('edit-features-section loading', [
                                'customFeatures_type' => gettype($customFeatures),
                                'customFeaturesArray_type' => gettype($customFeaturesArray),
                                'customFeaturesArray_is_array' => is_array($customFeaturesArray),
                                'has_main_features' => is_array($customFeaturesArray) && array_key_exists('main_features', $customFeaturesArray),
                                'main_features_count' => is_array($customFeaturesArray) && isset($customFeaturesArray['main_features']) ? count($customFeaturesArray['main_features']) : 0,
                                'has_chat_widget' => is_array($customFeaturesArray) && isset($customFeaturesArray['main_features']) && in_array('chat_widget', $customFeaturesArray['main_features']),
                            ]);
                            
                            $mainFeatures = [
                                'dashboard' => '대시보드',
                                'users' => '사용자 관리',
                                'registration_settings' => '회원가입 설정',
                                'mail_settings' => '메일 설정',
                                'contact_forms' => '컨텍트 폼',
                                'maps' => '지도',
                                'crawlers' => '크롤러',
                                'user_ranks' => '회원등급',
                                'boards' => '게시판 관리',
                                'posts' => '게시글 관리',
                                'attendance' => '출석',
                                'point_exchange' => '포인트 교환',
                                'event_application' => '신청형 이벤트',
                                'menus' => '메뉴 설정',
                                'messages' => '쪽지 관리',
                                'banners' => '배너',
                                'popups' => '팝업',
                                'blocked_ips' => '아이피 차단',
                                'custom_code' => '코드 커스텀',
                                'settings' => '사이트 설정',
                                'sidebar_widgets' => '사이드 위젯',
                                'main_widgets' => '메인 위젯',
                                'custom_pages' => '커스텀 페이지',
                                'toggle_menus' => '토글 메뉴',
                                'chat_widget' => '채팅 위젯',
                            ];
                            
                            $planMainFeatures = $currentPlan ? ($currentPlan->main_features ?? []) : [];
                            // customFeaturesArray가 null이 아니고 배열이고 main_features 키가 있으면 저장된 값 사용 (빈 배열이어도)
                            // 저장된 값이 있으면 무조건 사용 (플랜 기본값과 합치지 않음)
                            if (is_array($customFeaturesArray) && array_key_exists('main_features', $customFeaturesArray)) {
                                $customMainFeatures = $customFeaturesArray['main_features'];
                            } else {
                                $customMainFeatures = $planMainFeatures;
                            }
                            $oldMainFeatures = old('custom_main_features', $customMainFeatures);
                            
                            // 디버깅: 실제로 어떤 값이 사용되는지 확인
                            \Log::info('edit-features-section main_features', [
                                'customFeaturesArray_is_array' => is_array($customFeaturesArray),
                                'has_main_features' => is_array($customFeaturesArray) && array_key_exists('main_features', $customFeaturesArray),
                                'customMainFeatures_count' => count($customMainFeatures),
                                'customMainFeatures' => $customMainFeatures,
                                'has_chat_widget' => in_array('chat_widget', $customMainFeatures),
                                'oldMainFeatures_count' => count($oldMainFeatures),
                                'oldMainFeatures_has_chat_widget' => in_array('chat_widget', $oldMainFeatures),
                            ]);
                        @endphp
                        <div class="row">
                            @foreach($mainFeatures as $key => $label)
                                @php
                                    $isInPlan = in_array($key, $planMainFeatures);
                                    $isEnabled = in_array($key, $oldMainFeatures);
                                @endphp
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="custom_main_feature_{{ $key }}" 
                                               name="custom_main_features[]" 
                                               value="{{ $key }}"
                                               {{ $isEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="custom_main_feature_{{ $key }}">
                                            {{ $label }}
                                            @if($isInPlan)
                                                <span class="badge bg-success ms-1" style="font-size: 0.7em;">플랜 포함</span>
                                            @else
                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7em;">추가 기능</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllMainFeatures()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectPlanMainFeatures()">플랜 기능만 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deselectAllMainFeatures()">전체 해제</button>
                        </div>
                    </div>
                </div>

                <!-- 게시판 타입 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-grid me-2"></i>게시판 타입</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $planBoardTypes = $currentPlan ? ($currentPlan->board_types ?? []) : [];
                            $customBoardTypes = ($customFeaturesArray !== null && array_key_exists('board_types', $customFeaturesArray)) 
                                ? $customFeaturesArray['board_types'] 
                                : $planBoardTypes;
                            $oldBoardTypes = old('custom_board_types', $customBoardTypes);
                        @endphp
                        <div class="row">
                            @foreach($boardTypes as $key => $label)
                                @php
                                    $isInPlan = in_array($key, $planBoardTypes);
                                    $isEnabled = in_array($key, $oldBoardTypes);
                                @endphp
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="custom_board_type_{{ $key }}" 
                                               name="custom_board_types[]" 
                                               value="{{ $key }}"
                                               {{ $isEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="custom_board_type_{{ $key }}">
                                            {{ $label }}
                                            @if($isInPlan)
                                                <span class="badge bg-success ms-1" style="font-size: 0.7em;">플랜 포함</span>
                                            @else
                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7em;">추가 기능</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllBoardTypes()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectPlanBoardTypes()">플랜 기능만 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deselectAllBoardTypes()">전체 해제</button>
                        </div>
                    </div>
                </div>

                <!-- 회원가입 세부 기능 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-person-plus me-2"></i>회원가입 세부 기능</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $registrationFeatures = [
                                'signup_points' => '가입 포인트',
                                'email_verification' => '이메일 인증',
                                'phone_verification' => '전화번호 인증',
                                'identity_verification' => '본인인증',
                                'referrer' => '추천인 기능',
                                'point_message' => '포인트 쪽지',
                            ];
                            
                            $planRegFeatures = $currentPlan ? ($currentPlan->registration_features ?? []) : [];
                            $customRegFeatures = ($customFeaturesArray !== null && array_key_exists('registration_features', $customFeaturesArray)) 
                                ? $customFeaturesArray['registration_features'] 
                                : $planRegFeatures;
                            $oldRegFeatures = old('custom_registration_features', $customRegFeatures);
                        @endphp
                        <div class="row">
                            @foreach($registrationFeatures as $key => $label)
                                @php
                                    $isInPlan = in_array($key, $planRegFeatures);
                                    $isEnabled = in_array($key, $oldRegFeatures);
                                @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="custom_reg_feature_{{ $key }}" 
                                               name="custom_registration_features[]" 
                                               value="{{ $key }}"
                                               {{ $isEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="custom_reg_feature_{{ $key }}">
                                            {{ $label }}
                                            @if($isInPlan)
                                                <span class="badge bg-success ms-1" style="font-size: 0.7em;">플랜 포함</span>
                                            @else
                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7em;">추가 기능</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllRegFeatures()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectPlanRegFeatures()">플랜 기능만 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deselectAllRegFeatures()">전체 해제</button>
                        </div>
                    </div>
                </div>

                <!-- 사이드바 위젯 타입 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-layout-sidebar me-2"></i>사이드바 위젯 타입</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $planSidebarWidgetTypes = $currentPlan ? ($currentPlan->sidebar_widget_types ?? []) : [];
                            $customSidebarWidgetTypes = (is_array($customFeaturesArray) && array_key_exists('sidebar_widget_types', $customFeaturesArray)) 
                                ? $customFeaturesArray['sidebar_widget_types'] 
                                : $planSidebarWidgetTypes;
                            $oldSidebarWidgetTypes = old('custom_sidebar_widget_types', $customSidebarWidgetTypes);
                        @endphp
                        <div class="row">
                            @foreach($sidebarWidgetTypes as $key => $label)
                                @php
                                    $isInPlan = in_array($key, $planSidebarWidgetTypes);
                                    $isEnabled = in_array($key, $oldSidebarWidgetTypes);
                                @endphp
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="custom_sidebar_widget_type_{{ $key }}" 
                                               name="custom_sidebar_widget_types[]" 
                                               value="{{ $key }}"
                                               {{ $isEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="custom_sidebar_widget_type_{{ $key }}">
                                            {{ $label }}
                                            @if($isInPlan)
                                                <span class="badge bg-success ms-1" style="font-size: 0.7em;">플랜 포함</span>
                                            @else
                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7em;">추가 기능</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllSidebarWidgetTypes()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectPlanSidebarWidgetTypes()">플랜 기능만 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deselectAllSidebarWidgetTypes()">전체 해제</button>
                        </div>
                    </div>
                </div>

                <!-- 메인 위젯 타입 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>메인 위젯 타입</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $planMainWidgetTypes = $currentPlan ? ($currentPlan->main_widget_types ?? []) : [];
                            $customMainWidgetTypes = (is_array($customFeaturesArray) && array_key_exists('main_widget_types', $customFeaturesArray)) 
                                ? $customFeaturesArray['main_widget_types'] 
                                : $planMainWidgetTypes;
                            $oldMainWidgetTypes = old('custom_main_widget_types', $customMainWidgetTypes);
                        @endphp
                        <div class="row">
                            @foreach($mainWidgetTypes as $key => $label)
                                @php
                                    $isInPlan = in_array($key, $planMainWidgetTypes);
                                    $isEnabled = in_array($key, $oldMainWidgetTypes);
                                @endphp
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="custom_main_widget_type_{{ $key }}" 
                                               name="custom_main_widget_types[]" 
                                               value="{{ $key }}"
                                               {{ $isEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="custom_main_widget_type_{{ $key }}">
                                            {{ $label }}
                                            @if($isInPlan)
                                                <span class="badge bg-success ms-1" style="font-size: 0.7em;">플랜 포함</span>
                                            @else
                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7em;">추가 기능</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllMainWidgetTypes()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectPlanMainWidgetTypes()">플랜 기능만 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deselectAllMainWidgetTypes()">전체 해제</button>
                        </div>
                    </div>
                </div>

                <!-- 커스텀 페이지 위젯 타입 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>커스텀 페이지 위젯 타입</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $planCustomPageWidgetTypes = $currentPlan ? ($currentPlan->custom_page_widget_types ?? []) : [];
                            $customCustomPageWidgetTypes = (is_array($customFeaturesArray) && array_key_exists('custom_page_widget_types', $customFeaturesArray)) 
                                ? $customFeaturesArray['custom_page_widget_types'] 
                                : $planCustomPageWidgetTypes;
                            $oldCustomPageWidgetTypes = old('custom_custom_page_widget_types', $customCustomPageWidgetTypes);
                        @endphp
                        <div class="row">
                            @foreach($customPageWidgetTypes as $key => $label)
                                @php
                                    $isInPlan = in_array($key, $planCustomPageWidgetTypes);
                                    $isEnabled = in_array($key, $oldCustomPageWidgetTypes);
                                @endphp
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="custom_custom_page_widget_type_{{ $key }}" 
                                               name="custom_custom_page_widget_types[]" 
                                               value="{{ $key }}"
                                               {{ $isEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="custom_custom_page_widget_type_{{ $key }}">
                                            {{ $label }}
                                            @if($isInPlan)
                                                <span class="badge bg-success ms-1" style="font-size: 0.7em;">플랜 포함</span>
                                            @else
                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7em;">추가 기능</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllCustomPageWidgetTypes()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectPlanCustomPageWidgetTypes()">플랜 기능만 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deselectAllCustomPageWidgetTypes()">전체 해제</button>
                        </div>
                    </div>
                </div>

                <!-- 제한 사항 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-slash-circle me-2"></i>제한 사항</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $planLimits = $currentPlan ? ($currentPlan->limits ?? []) : [];
                            $customLimits = $customFeaturesArray['limits'] ?? $planLimits;
                            $oldLimits = old('custom_limits', $customLimits);
                        @endphp
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="custom_limit_boards" class="form-label">게시판 수</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="custom_limit_boards" 
                                       name="custom_limits[boards]" 
                                       value="{{ $oldLimits['boards'] ?? ($planLimits['boards'] ?? '') }}"
                                       placeholder="비워두거나 '-' 입력 시 무제한">
                                <small class="text-muted">플랜 기본값: {{ $planLimits['boards'] ?? '무제한' }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="custom_limit_widgets" class="form-label">위젯 수</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="custom_limit_widgets" 
                                       name="custom_limits[widgets]" 
                                       value="{{ $oldLimits['widgets'] ?? ($planLimits['widgets'] ?? '') }}"
                                       placeholder="비워두거나 '-' 입력 시 무제한">
                                <small class="text-muted">플랜 기본값: {{ $planLimits['widgets'] ?? '무제한' }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="custom_limit_custom_pages" class="form-label">커스텀 페이지 수</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="custom_limit_custom_pages" 
                                       name="custom_limits[custom_pages]" 
                                       value="{{ $oldLimits['custom_pages'] ?? ($planLimits['custom_pages'] ?? '') }}"
                                       placeholder="비워두거나 '-' 입력 시 무제한">
                                <small class="text-muted">플랜 기본값: {{ $planLimits['custom_pages'] ?? '무제한' }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="custom_limit_users" class="form-label">사용자 수</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="custom_limit_users" 
                                       name="custom_limits[users]" 
                                       value="{{ $oldLimits['users'] ?? ($planLimits['users'] ?? '') }}"
                                       placeholder="비워두거나 '-' 입력 시 무제한">
                                <small class="text-muted">플랜 기본값: {{ $planLimits['users'] ?? '무제한' }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="custom_limit_storage" class="form-label">스토리지 (MB)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="custom_limit_storage" 
                                       name="custom_limits[storage]" 
                                       value="{{ $oldLimits['storage'] ?? ($planLimits['storage'] ?? '') }}"
                                       min="0"
                                       placeholder="비워두거나 '-' 입력 시 무제한">
                                <small class="text-muted">플랜 기본값: {{ $planLimits['storage'] ?? '무제한' }} MB</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

