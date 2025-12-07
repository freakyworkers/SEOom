
// 도메인 저장 폼 AJAX 처리
document.addEventListener('DOMContentLoaded', function() {
    const domainForm = document.getElementById('domainForm');
    if (domainForm) {
        domainForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('domainSubmitBtn');
            const originalText = submitBtn.innerHTML;
            const domainInput = document.getElementById('domain');
            const errorDiv = document.getElementById('domainError');
            const nameserversContainer = document.getElementById('nameserversContainer');
            
            // 버튼 비활성화 및 로딩 표시
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>저장 중...';
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
            
            const formData = new FormData(this);
            // data-action-url 속성에서 URL 가져오기 (없으면 action 속성 사용)
            let url = this.getAttribute('data-action-url') || this.action;
            
            // URL이 상대 경로인 경우 절대 경로로 변환
            if (url && !url.startsWith('http') && !url.startsWith('//')) {
                if (url.startsWith('/')) {
                    url = window.location.origin + url;
                } else {
                    url = window.location.origin + '/' + url;
                }
            }
            
            // URL이 비어있거나 잘못된 경우 기본 URL 사용
            if (!url || url.includes('/login')) {
                // 기본 URL 생성: /site/{masterSiteSlug}/my-sites/{siteSlug}/domain
                const currentPath = window.location.pathname;
                const pathMatch = currentPath.match(/\/site\/([^\/]+)\/admin\/settings/);
                if (pathMatch) {
                    const siteSlug = pathMatch[1];
                    // 마스터 사이트 slug 가져오기 (현재 사이트가 마스터가 아닌 경우)
                    const masterSiteSlug = 'master'; // 기본값
                    url = window.location.origin + '/site/' + masterSiteSlug + '/my-sites/' + siteSlug + '/domain';
                } else {
                    console.error('Failed to determine site slug from URL');
                    return;
                }
            }
            
            // 디버깅: URL 확인
            console.log('Domain update URL:', url);
            
            fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': (function() { var meta = document.querySelector('meta[name="csrf-token"]'); return meta ? meta.getAttribute('content') : null; })() || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(response) {
                if (!response.ok) {
                    // 응답을 복제하여 여러 번 읽을 수 있도록 함
                    const clonedResponse = response.clone();
                    return response.json().then(function(data) {
                        const errorMessage = data.message || data.error || '저장에 실패했습니다.';
                        console.error('Domain update error:', data);
                        throw new Error(errorMessage);
                    }).catch(function() {
                        return clonedResponse.text().then(function(text) {
                            console.error('Domain update error (text):', text);
                            // HTML 응답인 경우 에러 메시지 추출 시도
                            const errorMatch = text.match(/<title>([^<]+)<\/title>/i) || text.match(/The\s+\w+\s+method\s+is\s+not\s+supported[^<]*/i);
                            const errorMessage = errorMatch ? errorMatch[0] : '저장에 실패했습니다: ' + text.substring(0, 200);
                            throw new Error(errorMessage);
                        });
                    });
                }
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    // 도메인 입력 필드 업데이트
                    if (domainInput && data.domain !== undefined) {
                        domainInput.value = data.domain || '';
                    }
                    
                    // 성공 메시지 표시
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="bi bi-check-circle me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    domainForm.insertAdjacentElement('beforebegin', alertDiv);
                    
                    // 3초 후 자동으로 알림 제거
                    setTimeout(function() {
                        alertDiv.remove();
                    }, 3000);
                    
                    // 네임서버 정보 업데이트
                    if (nameserversContainer) {
                        if (data.nameservers && data.nameservers.length > 0) {
                            let nameserversHtml = '';
                            data.nameservers.forEach((nameserver, index) => {
                                nameserversHtml += `
                                    <div class="mb-2 d-flex align-items-center">
                                        <strong class="me-2">네임서버 ${index + 1}:</strong>
                                        <code class="flex-grow-1 ms-2" style="font-size: 1.1em; background-color: white; padding: 0.5rem; border-radius: 0.25rem;">${nameserver}</code>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary ms-2" 
                                            onclick="copyNameserverToClipboard('${nameserver}', this)">
                                        <i class="bi bi-clipboard me-1"></i>복사
                                    </button>
                                </div>
                            `;
                        });
                        nameserversContainer.innerHTML = nameserversHtml;
                    } else {
                        nameserversContainer.innerHTML = '<div class="text-muted">도메인을 입력하고 저장하면 네임서버 정보가 나타납니다.</div>';
                    }
                    
                    // 도메인 정보 업데이트
                    const domainInfo = domainForm.querySelector('.form-text');
                    if (domainInfo) {
                        if (data.domain) {
                            domainInfo.innerHTML = `
                                현재 연결된 도메인: <strong>${data.domain}</strong> | 
                                <a href="https://${data.domain}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>확인
                                </a>
                            `;
                        } else {
                            const siteSlug = '{{ $site->slug }}';
                            const masterDomain = '{{ config("app.master_domain", "seoomweb.com") }}';
                            domainInfo.innerHTML = `
                                서브도메인: <strong>${siteSlug}.${masterDomain}</strong><br>
                                <strong>도메인을 입력하고 저장하면 자동으로 Cloudflare에 추가되고 DNS 레코드가 생성됩니다.</strong>
                            `;
                        }
                    }
                    
                    // 네임서버 섹션 표시/숨김 처리
                    const nameserverSection = document.querySelector('[ref="e577"]');
                    if (nameserverSection) {
                        if (data.domain && data.nameservers && data.nameservers.length > 0) {
                            nameserverSection.style.display = 'block';
                        } else if (!data.domain) {
                            nameserverSection.style.display = 'none';
                        }
                    }
                    
                    // 페이지 새로고침 (최신 데이터 반영)
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.message || '저장에 실패했습니다.');
                }
            })
            .catch(function(error) {
                errorDiv.textContent = error.message;
                errorDiv.style.display = 'block';
                console.error('Error:', error);
            })
            .finally(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // 초기 미리보기는 서버 사이드에서 렌더링되므로 초기화 불필요
    // 디자인 변경 시에만 AJAX로 업데이트

    // 테마 선택 시 미리보기 업데이트
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('theme-preview-select')) {
            const type = e.target.getAttribute('data-type');
            const targetId = e.target.id;
            
            // 메뉴 폰트 설정 드롭다운인 경우 현재 선택된 테마를 사용
            if (targetId === 'menu_font_size' || targetId === 'menu_font_padding' || targetId === 'menu_font_weight') {
                const headerSelect = document.getElementById('theme_top');
                const theme = headerSelect ? headerSelect.value : 'design1';
                console.log('Menu font setting changed:', targetId, 'using theme:', theme);
                updateThemePreview(type, theme);
            } else {
                // 일반 테마 선택인 경우
                const theme = e.target.value;
                console.log('Theme changed:', type, theme);
                updateThemePreview(type, theme);
            }
        }
    });
    
    // 다크모드 변경 시 미리보기 업데이트
    const darkModeSelect = document.getElementById('theme_dark_mode');
    if (darkModeSelect) {
        darkModeSelect.addEventListener('change', function() {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
            const footerSelect = document.getElementById('theme_bottom');
            if (footerSelect && footerSelect.value) {
                updateThemePreview('footer', footerSelect.value);
            }
        });
    }
    
    // 색상 변경 시 미리보기 업데이트
    document.addEventListener('change', function(e) {
        if (e.target.type === 'color') {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
            const footerSelect = document.getElementById('theme_bottom');
            if (footerSelect && footerSelect.value) {
                updateThemePreview('footer', footerSelect.value);
            }
        }
    });
    
    // 최상단 헤더 표시 체크박스 변경 시 미리보기 업데이트
    const topHeaderShowCheckbox = document.getElementById('theme_top_header_show');
    if (topHeaderShowCheckbox) {
        topHeaderShowCheckbox.addEventListener('change', function() {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
        });
    }
    
    // 로그인 버튼 표시 체크박스 변경 시 미리보기 업데이트
    const topHeaderLoginShowCheckbox = document.getElementById('top_header_login_show');
    if (topHeaderLoginShowCheckbox) {
        topHeaderLoginShowCheckbox.addEventListener('change', function() {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
        });
    }
    
    // 메뉴 로그인 표시 체크박스 변경 시 미리보기 업데이트
    const menuLoginShowCheckbox = document.getElementById('menu_login_show');
    if (menuLoginShowCheckbox) {
        menuLoginShowCheckbox.addEventListener('change', function() {
            const headerSelect = document.getElementById('theme_top');
            if (headerSelect && headerSelect.value) {
                updateThemePreview('header', headerSelect.value);
            }
        });
    }

    // 이미지 업로드 함수
    function uploadImage(file, $uploadArea) {
        console.log('uploadImage called', file, $uploadArea);
        if (!file) {
            console.error('No file provided');
            return;
        }
        if (!$uploadArea || $uploadArea.length === 0) {
            console.error('No upload area found');
            return;
        }

        var type = $uploadArea.data('type');
        var inputName = $uploadArea.data('input');
        var $input = $('#' + inputName);
        
        console.log('Upload params - type:', type, 'inputName:', inputName);

        // FormData 생성
        var formData = new FormData();
        formData.append('image', file);
        formData.append('type', type);
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        formData.append('_token', csrfToken);
        
        console.log('FormData created, CSRF token:', csrfToken ? 'exists' : 'missing');

        // 업로드 중 표시
        $uploadArea.html('<div class="image-upload-btn"><i class="bi bi-hourglass-split"></i><span>업로드 중...</span></div>');
        
        console.log('Starting AJAX upload to:', '{{ route("admin.settings.upload-image", ["site" => $site->slug]) }}');

        // AJAX 업로드
        $.ajax({
            url: '{{ route("admin.settings.upload-image", ["site" => $site->slug]) }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30초 타임아웃
            success: function(response) {
                console.log('Upload response received:', response);
                console.log('Response type:', typeof response);
                console.log('Response keys:', Object.keys(response || {}));
                
                // 응답 검증
                if (!response) {
                    console.error('No response received');
                    alert('서버 응답을 받을 수 없습니다.');
                    resetUploadArea($uploadArea);
                    return;
                }
                
                if (response.error) {
                    var errorMessage = response.message || '이미지 업로드에 실패했습니다.';
                    console.error('Upload error:', errorMessage);
                    alert(errorMessage);
                    resetUploadArea($uploadArea);
                    return;
                }
                
                if (!response.url) {
                    console.error('No URL in response:', response);
                    alert('이미지 URL을 받을 수 없습니다.');
                    resetUploadArea($uploadArea);
                    return;
                }
                
                // 이미지 미리보기 표시
                console.log('Upload success, URL:', response.url);
                $uploadArea.addClass('has-image');
                
                var previewStyle = type === 'favicon' ? 'max-height: 60px; display: block; width: auto; height: auto; margin: 0 auto;' : 'max-height: 120px; display: block; width: auto; height: auto; margin: 0 auto;';
                
                // 이미지 alt 텍스트
                var imageAlt = type === 'logo' ? '로고' : (type === 'logo_dark' ? '로고 (다크모드)' : (type === 'favicon' ? '파비콘' : 'OG 이미지'));
                
                // accept 타입 설정
                var acceptType = type === 'favicon' ? 'image/*,.ico' : 'image/*';
                
                // jQuery를 사용하여 안전하게 이미지 요소 생성
                var $img = $('<img>', {
                    'class': 'image-preview',
                    'src': response.url,
                    'alt': imageAlt,
                    'style': previewStyle
                });
                
                // 파일 input 생성
                var $fileInput = $('<input>', {
                    'type': 'file',
                    'class': 'hidden-file-input',
                    'accept': acceptType,
                    'data-type': type
                });
                
                // 이미지 로드 이벤트
                $img.on('load', function() {
                    console.log('Image loaded successfully');
                }).on('error', function() {
                    console.error('Image load failed, URL:', response.url);
                    alert('이미지를 불러올 수 없습니다. URL: ' + response.url);
                    resetUploadArea($uploadArea);
                });
                
                // 업로드 영역에 이미지와 파일 input 추가
                console.log('Setting HTML to upload area');
                $uploadArea.empty().append($img).append($fileInput);
                
                // 이미지가 이미 캐시된 경우 (즉시 로드 완료)
                setTimeout(function() {
                    var imgElement = $img[0];
                    if (imgElement && imgElement.complete && imgElement.naturalHeight > 0) {
                        console.log('Image already loaded from cache');
                    } else if (imgElement && imgElement.complete && imgElement.naturalHeight === 0) {
                        console.error('Image failed to load (naturalHeight is 0)');
                        alert('이미지를 불러올 수 없습니다. URL: ' + response.url);
                        resetUploadArea($uploadArea);
                    }
                }, 100);
                
                // hidden input 값 업데이트
                if ($input.length) {
                    $input.val(response.url);
                }
                
                console.log('Preview update completed');
            },
            error: function(xhr, status, error) {
                console.error('AJAX error - Status:', status, 'Error:', error);
                console.error('Response:', xhr.responseText);
                var errorMessage = '이미지 업로드에 실패했습니다.';
                if (xhr.responseJSON) {
                    console.error('Response JSON:', xhr.responseJSON);
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.errors && xhr.responseJSON.errors.image) {
                        errorMessage = Array.isArray(xhr.responseJSON.errors.image) 
                            ? xhr.responseJSON.errors.image[0] 
                            : xhr.responseJSON.errors.image;
                    }
                } else if (xhr.status === 0) {
                    errorMessage = '네트워크 오류가 발생했습니다. 인터넷 연결을 확인해주세요.';
                } else if (xhr.status === 413) {
                    errorMessage = '파일 크기가 너무 큽니다. (최대 5MB)';
                } else if (xhr.status === 422) {
                    errorMessage = '파일 형식이 올바르지 않습니다. (JPEG, PNG, JPG, GIF, WEBP, ICO만 가능)';
                } else if (xhr.status === 500) {
                    errorMessage = '서버 오류가 발생했습니다. 관리자에게 문의해주세요.';
                }
                console.error('Final error message:', errorMessage);
                alert(errorMessage);
                resetUploadArea($uploadArea);
            }
        });
    }

    // 업로드 영역 초기화 함수
    function resetUploadArea($area) {
        var type = $area.data('type');
        var inputName = $area.data('input');
        var acceptType = type === 'favicon' ? 'image/*,.ico' : 'image/*';
        var uploadBtnStyle = type === 'favicon' ? 'style="padding: 0.5rem;"><i class="bi bi-cloud-upload"></i><span style="font-size: 0.75rem;">업로드</span>' : '><i class="bi bi-cloud-upload"></i><span>업로드</span>';
        
        $area.removeClass('has-image');
        $area.html(
            '<div class="image-upload-btn" ' + uploadBtnStyle + '</div>' +
            '<input type="file" class="hidden-file-input" accept="' + acceptType + '" data-type="' + type + '">' +
            '<input type="hidden" name="' + inputName + '" id="' + inputName + '" value="">'
        );
        
        // 이벤트 위임을 사용하므로 자동으로 처리됨
        
        // hidden input 값 초기화
        var $input = $('#' + inputName);
        if ($input.length) {
            $input.val('');
        }
    }

    // 이미지 미리보기 클릭 시 삭제 또는 교체
    $(document).on('click', '.image-preview', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $area = $(this).closest('.image-upload-area');
        var action = confirm('이미지를 삭제하시겠습니까?\n\n취소를 누르면 이미지를 교체할 수 있습니다.');
        
        if (action) {
            // 삭제
            var inputName = $area.data('input');
            var $input = $('#' + inputName);
            var type = $area.data('type');
            var acceptType = type === 'favicon' ? 'image/*,.ico' : 'image/*';
            var uploadBtnStyle = type === 'favicon' ? 'style="padding: 0.5rem;"><i class="bi bi-cloud-upload"></i><span style="font-size: 0.75rem;">업로드</span>' : '><i class="bi bi-cloud-upload"></i><span>업로드</span>';
            
            $area.removeClass('has-image');
            $area.html(
                '<div class="image-upload-btn" ' + uploadBtnStyle + '</div>' +
                '<input type="file" class="hidden-file-input" accept="' + acceptType + '" data-type="' + type + '">' +
                '<input type="hidden" name="' + inputName + '" id="' + inputName + '" value="">'
            );
            
            if ($input.length) {
                $input.val('');
            }
        } else {
            // 교체 - 파일 input 동적 생성 후 클릭
            var type = $area.data('type');
            var acceptType = type === 'favicon' ? 'image/*,.ico' : 'image/*';
            
            // 기존 파일 input 제거
            $area.find('.hidden-file-input').remove();
            
            // 새 파일 input 생성 및 추가
            var $fileInput = $('<input>', {
                type: 'file',
                class: 'hidden-file-input',
                accept: acceptType,
                'data-type': type
            });
            
            $area.append($fileInput);
            
            // 파일 선택 후 업로드 처리
            $fileInput.on('change', function() {
                var file = this.files[0];
                if (file) {
                    uploadImage(file, $area);
                }
            });
            
            // 파일 input 클릭
            setTimeout(function() {
                $fileInput[0].click();
            }, 10);
        }
        return false;
    });

    // 파일 input 클릭 시 이벤트 전파 중지 (이미지 업로드 영역 클릭 이벤트와 충돌 방지)
    $(document).on('click', '.hidden-file-input', function(e) {
        e.stopPropagation();
        // 브라우저 기본 동작은 유지 (파일 선택 창 열기)
    });

    // 파일 선택 시 업로드 (이벤트 위임 사용)
    $(document).on('change', '.hidden-file-input', function(e) {
        e.stopPropagation();
        console.log('File input changed');
        var file = e.target.files[0];
        if (!file) {
            console.log('No file selected');
            return;
        }
        
        console.log('File selected:', file.name, file.size, file.type);
        var $uploadArea = $(this).closest('.image-upload-area');
        console.log('Upload area found:', $uploadArea.length);
        uploadImage(file, $uploadArea);
    });

    // 네임서버 복사 기능 (버튼 스타일 변경)
    window.copyNameserverToClipboard = function(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check me-1"></i>복사됨';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
            
            setTimeout(function() {
                button.innerHTML = originalHtml;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        }).catch(function(err) {
            // 클립보드 API가 지원되지 않는 경우 대체 방법
            var textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check me-1"></i>복사됨';
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-success');
                
                setTimeout(function() {
                    button.innerHTML = originalHtml;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }, 2000);
            } catch (err) {
                alert('복사에 실패했습니다. 수동으로 복사해주세요: ' + text);
            }
            document.body.removeChild(textArea);
        });
    };
});

// 테마 미리보기 데이터
const themePreviews = {
    header: {
        design1: { bg: '#0d6efd', text: '#ffffff', style: 'solid', desc: '로고 | 메뉴 좌측 | 로그인/회원가입' },
        design2: { bg: '#6c757d', text: '#ffffff', style: 'solid', desc: '로고 | 메뉴 중앙 | 로그인/회원가입' },
        design3: { bg: '#198754', text: '#ffffff', style: 'solid', desc: '로고 | 메뉴 우측 | 로그인/회원가입' },
        design4: { bg: '#ffc107', text: '#000000', style: 'solid', desc: '메뉴 좌측 | 로고 중앙 | 로그인/회원가입' },
        design5: { bg: '#dc3545', text: '#ffffff', style: 'solid', desc: '로고 | 검색창 | 로그인/회원가입 (하단 메뉴)' },
        design6: { bg: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', text: '#ffffff', style: 'gradient', desc: '로고 중앙 (하단 메뉴 중앙)' },
    },
    footer: {
        theme01: { bg: '#212529', text: '#ffffff', style: 'solid' },
        theme02: { bg: '#343a40', text: '#ffffff', style: 'solid' },
        theme03: { bg: '#495057', text: '#ffffff', style: 'solid' },
        theme04: { bg: '#6c757d', text: '#ffffff', style: 'solid' },
        theme05: { bg: '#adb5bd', text: '#000000', style: 'solid' },
    }
};

function updateThemePreview(type, theme) {
    // 매개변수를 로컬 변수로 저장 (스코프 문제 방지)
    var previewType = type;
    var previewTheme = theme;
    
    const previewId = previewType === 'header' ? 'theme_top_preview' : 'theme_bottom_preview';
    const previewElement = document.getElementById(previewId);
    
    if (!previewElement) {
        console.error('Preview element not found:', previewId);
        return;
    }
    
    let container = previewElement.querySelector('.theme-preview-container');
    
    if (!container) {
        container = document.createElement('div');
        container.className = 'theme-preview-container';
        previewElement.appendChild(container);
    }
    
    // 로딩 표시
    container.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    console.log('Updating preview:', previewType, previewTheme);
    var menuFontSizeEl = document.getElementById('menu_font_size');
    var menuFontPaddingEl = document.getElementById('menu_font_padding');
    var menuFontWeightEl = document.getElementById('menu_font_weight');
    console.log('Menu font settings - size:', menuFontSizeEl ? menuFontSizeEl.value : null, 'padding:', menuFontPaddingEl ? menuFontPaddingEl.value : null, 'weight:', menuFontWeightEl ? menuFontWeightEl.value : null);
    
    // 현재 입력된 색상 값 가져오기
    var themeDarkModeEl = document.getElementById('theme_dark_mode');
    const darkMode = (themeDarkModeEl && themeDarkModeEl.value) ? themeDarkModeEl.value : 'light';
    const isDark = darkMode === 'dark';
    
    // AJAX로 실제 헤더 미리보기 HTML 가져오기
    const url = '{{ route("admin.settings.preview-header", ["site" => $site->slug]) }}';
    const params = new URLSearchParams({
        theme: previewTheme,
        type: previewType,
        theme_dark_mode: darkMode
    });
    
    // 현재 입력된 색상 값 추가
    if (previewType === 'header') {
        if (isDark) {
            var darkHeaderTextEl = document.querySelector('input[name="color_dark_header_text"]');
            var darkHeaderBgEl = document.querySelector('input[name="color_dark_header_bg"]');
            var darkPointMainEl = document.querySelector('input[name="color_dark_point_main"]');
            const darkHeaderText = darkHeaderTextEl ? darkHeaderTextEl.value : null;
            const darkHeaderBg = darkHeaderBgEl ? darkHeaderBgEl.value : null;
            const darkPointMain = darkPointMainEl ? darkPointMainEl.value : null;
            if (darkHeaderText) params.append('color_dark_header_text', darkHeaderText);
            if (darkHeaderBg) params.append('color_dark_header_bg', darkHeaderBg);
            if (darkPointMain) params.append('color_dark_point_main', darkPointMain);
        } else {
            var lightHeaderTextEl = document.querySelector('input[name="color_light_header_text"]');
            var lightHeaderBgEl = document.querySelector('input[name="color_light_header_bg"]');
            var lightPointMainEl = document.querySelector('input[name="color_light_point_main"]');
            const lightHeaderText = lightHeaderTextEl ? lightHeaderTextEl.value : null;
            const lightHeaderBg = lightHeaderBgEl ? lightHeaderBgEl.value : null;
            const lightPointMain = lightPointMainEl ? lightPointMainEl.value : null;
            if (lightHeaderText) params.append('color_light_header_text', lightHeaderText);
            if (lightHeaderBg) params.append('color_light_header_bg', lightHeaderBg);
            if (lightPointMain) params.append('color_light_point_main', lightPointMain);
        }
        
        // 최상단 헤더 표시 체크박스 값 추가
        const topHeaderShowCheckbox = document.getElementById('theme_top_header_show');
        const topHeaderShow = topHeaderShowCheckbox && topHeaderShowCheckbox.checked ? '1' : '0';
        params.append('theme_top_header_show', topHeaderShow);
        
        // 로그인 버튼 표시 체크박스 값 추가
        const topHeaderLoginShowCheckbox = document.getElementById('top_header_login_show');
        const topHeaderLoginShow = topHeaderLoginShowCheckbox && topHeaderLoginShowCheckbox.checked ? '1' : '0';
        params.append('top_header_login_show', topHeaderLoginShow);
        
        // 메뉴 로그인 표시 체크박스 값 추가
        const menuLoginShowCheckbox = document.getElementById('menu_login_show');
        const menuLoginShow = menuLoginShowCheckbox && menuLoginShowCheckbox.checked ? '1' : '0';
        params.append('menu_login_show', menuLoginShow);
        
        // 그림자 체크박스 값 추가
        const headerShadowCheckbox = document.getElementById('header_shadow');
        const headerShadow = headerShadowCheckbox && headerShadowCheckbox.checked ? '1' : '0';
        params.append('header_shadow', headerShadow);
        
        // 헤더 테두리 체크박스 값 추가
        const headerBorderCheckbox = document.getElementById('header_border');
        const headerBorder = headerBorderCheckbox && headerBorderCheckbox.checked ? '1' : '0';
        params.append('header_border', headerBorder);
        
        // 헤더 테두리 두께 및 컬러 값 추가
        if (headerBorder === '1') {
            var headerBorderWidthEl = document.getElementById('header_border_width');
            var headerBorderColorEl = document.getElementById('header_border_color');
            const headerBorderWidth = (headerBorderWidthEl && headerBorderWidthEl.value) ? headerBorderWidthEl.value : '1';
            const headerBorderColor = (headerBorderColorEl && headerBorderColorEl.value) ? headerBorderColorEl.value : '#dee2e6';
            params.append('header_border_width', headerBorderWidth);
            params.append('header_border_color', headerBorderColor);
        }
        
        // 메뉴 폰트 설정 값 추가
        var menuFontSizeEl2 = document.getElementById('menu_font_size');
        var menuFontPaddingEl2 = document.getElementById('menu_font_padding');
        var menuFontWeightEl2 = document.getElementById('menu_font_weight');
        const menuFontSize = (menuFontSizeEl2 && menuFontSizeEl2.value) ? menuFontSizeEl2.value : '1.25rem';
        const menuFontPadding = (menuFontPaddingEl2 && menuFontPaddingEl2.value) ? menuFontPaddingEl2.value : '0.5rem';
        const menuFontWeight = (menuFontWeightEl2 && menuFontWeightEl2.value) ? menuFontWeightEl2.value : '700';
        params.append('menu_font_size', menuFontSize);
        params.append('menu_font_padding', menuFontPadding);
        params.append('menu_font_weight', menuFontWeight);
    } else {
        if (isDark) {
            var darkFooterTextEl = document.querySelector('input[name="color_dark_footer_text"]');
            var darkFooterBgEl = document.querySelector('input[name="color_dark_footer_bg"]');
            const darkFooterText = darkFooterTextEl ? darkFooterTextEl.value : null;
            const darkFooterBg = darkFooterBgEl ? darkFooterBgEl.value : null;
            if (darkFooterText) params.append('color_dark_footer_text', darkFooterText);
            if (darkFooterBg) params.append('color_dark_footer_bg', darkFooterBg);
        } else {
            var lightFooterTextEl = document.querySelector('input[name="color_light_footer_text"]');
            var lightFooterBgEl = document.querySelector('input[name="color_light_footer_bg"]');
            const lightFooterText = lightFooterTextEl ? lightFooterTextEl.value : null;
            const lightFooterBg = lightFooterBgEl ? lightFooterBgEl.value : null;
            if (lightFooterText) params.append('color_light_footer_text', lightFooterText);
            if (lightFooterBg) params.append('color_light_footer_bg', lightFooterBg);
        }
    }
    
    // CSRF 토큰 가져오기
    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;
    
    fetch(url + '?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken || ''
        },
        credentials: 'same-origin'
    })
    .then(function(response) {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(function(text) {
                console.error('Error response:', text);
                throw new Error('Network response was not ok: ' + response.status + ' - ' + text.substring(0, 100));
            });
        }
        return response.json();
    })
    .then(function(data) {
        console.log('Preview response received - hasData:', !!data, 'hasHtml:', !!(data && data.html), 'htmlLength:', data && data.html ? data.html.length : 0);
        
        if (data && data.html) {
            // HTML이 비어있지 않은지 확인
            const htmlContent = data.html.trim();
            if (htmlContent.length > 0) {
                try {
                    // 기존 내용을 먼저 지우고 새로 설정
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = htmlContent;
                    
                    // CSS 파싱 오류 확인을 위해 스타일 태그 검사
                    const styleTags = tempDiv.querySelectorAll('style');
                    let hasStyleError = false;
                    styleTags.forEach(function(style) {
                        try {
                            // 스타일이 유효한지 확인
                            const testEl = document.createElement('div');
                            const styleContent = style.textContent.split('{');
                            if (styleContent.length > 1) {
                                const styleBody = styleContent[1].split('}')[0] || '';
                                testEl.style.cssText = styleBody;
                            }
                        } catch (e) {
                            console.warn('Potential CSS error detected:', e);
                            hasStyleError = true;
                        }
                    });
                    
                    if (!hasStyleError) {
                        container.innerHTML = '';
                        container.innerHTML = htmlContent;
                        console.log('Preview updated successfully, HTML length:', htmlContent.length);
                        
                        // 스타일이 제대로 적용되었는지 확인
                        setTimeout(function() {
                            const navLinks = container.querySelectorAll('.nav-link');
                            console.log('Nav links found:', navLinks.length);
                            if (navLinks.length > 0) {
                                const computedStyle = window.getComputedStyle(navLinks[0]);
                                console.log('First nav-link font-size:', computedStyle.fontSize);
                                if (!computedStyle.fontSize || computedStyle.fontSize === '0px') {
                                    console.error('Font size is invalid, reloading with defaults');
                                    // 기본값으로 다시 로드
                                    updateThemePreview(previewType, previewTheme);
                                }
                            } else {
                                console.warn('No nav links found in preview');
                            }
                        }, 100);
                    } else {
                        console.error('CSS error detected, using fallback');
                        container.innerHTML = htmlContent; // 그래도 시도
                    }
                } catch (e) {
                    console.error('Error setting innerHTML:', e, e.stack);
                    // 에러 발생 시에도 기본 HTML은 표시
                    try {
                        container.innerHTML = htmlContent;
                    } catch (e2) {
                        var errorMsg = (e && e.message) ? String(e.message).replace(/</g, '&lt;').replace(/>/g, '&gt;') : '알 수 없는 오류';
                        container.innerHTML = '<div class="text-danger p-3">미리보기 표시 오류: ' + errorMsg + '</div>';
                    }
                }
            } else {
                console.error('Empty HTML in response');
                container.innerHTML = '<div class="text-warning p-3">미리보기 HTML이 비어있습니다.</div>';
            }
        } else if (data && data.error) {
            console.error('Server error:', data.error);
            container.innerHTML = '<div class="text-danger p-3">미리보기 오류: ' + (data.message || data.error) + '</div>';
        } else {
            console.error('No HTML in response:', data);
            container.innerHTML = '<div class="text-muted p-3">미리보기를 불러올 수 없습니다. (응답 데이터 없음)</div>';
        }
    })
    .catch(function(error) {
        console.error('Preview error:', error);
        var typeEscaped = (previewType || '').replace(/'/g, "\\'");
        var themeEscaped = (previewTheme || '').replace(/'/g, "\\'");
        var errorMessage = error && error.message ? String(error.message) : '알 수 없는 오류';
        container.innerHTML = '<div class="text-danger p-3">미리보기를 불러올 수 없습니다.<br><small>' + errorMessage + '</small><br><button class="btn btn-sm btn-secondary mt-2" onclick="updateThemePreview(\'' + typeEscaped + '\', \'' + themeEscaped + '\')">다시 시도</button></div>';
    });
}

// URL 복사 함수
function copyToClipboard(text, label) {
    navigator.clipboard.writeText(text).then(function() {
        alert(label + '이(가) 클립보드에 복사되었습니다.');
    }, function(err) {
        // 클립보드 API가 지원되지 않는 경우 대체 방법
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            alert(label + '이(가) 클립보드에 복사되었습니다.');
        } catch (err) {
            alert('복사에 실패했습니다. 수동으로 복사해주세요: ' + text);
        }
        document.body.removeChild(textArea);
    });
}

// 모바일 헤더 미리보기 업데이트 함수
function updateMobileHeaderPreview() {
    const previewElement = document.getElementById('mobile_header_preview');
    if (!previewElement) return;
    
    var mobileHeaderThemeEl = document.getElementById('mobile_header_theme');
    var mobileMenuIconEl = document.getElementById('mobile_menu_icon');
    var mobileMenuDirectionEl = document.getElementById('mobile_menu_direction');
    var mobileMenuIconBorderEl = document.getElementById('mobile_menu_icon_border');
    var mobileMenuLoginWidgetEl = document.getElementById('mobile_menu_login_widget');
    const theme = (mobileHeaderThemeEl && mobileHeaderThemeEl.value) ? mobileHeaderThemeEl.value : 'theme1';
    const menuIcon = (mobileMenuIconEl && mobileMenuIconEl.value) ? mobileMenuIconEl.value : 'bi-list';
    const menuDirection = (mobileMenuDirectionEl && mobileMenuDirectionEl.value) ? mobileMenuDirectionEl.value : 'top-to-bottom';
    const menuIconBorder = (mobileMenuIconBorderEl && mobileMenuIconBorderEl.checked) ? '1' : '0';
    const menuLoginWidget = (mobileMenuLoginWidgetEl && mobileMenuLoginWidgetEl.checked) ? '1' : '0';
    
    // AJAX로 미리보기 HTML 가져오기
    const url = '{{ route("admin.settings.preview-mobile-header", ["site" => $site->slug]) }}';
    const params = new URLSearchParams({
        theme: theme,
        menu_icon: menuIcon,
        menu_direction: menuDirection,
        menu_icon_border: menuIconBorder,
        menu_login_widget: menuLoginWidget
    });
    
    var csrfTokenMeta2 = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta2 ? csrfTokenMeta2.getAttribute('content') : null;
    
    previewElement.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    fetch(url + '?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken || ''
        },
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data && data.html) {
            previewElement.innerHTML = data.html;
            
            // 미리보기 스크립트가 자동으로 메뉴를 닫도록 함
            // 스크립트는 mobile-header-preview.blade.php에 포함되어 있음
        } else {
            previewElement.innerHTML = '<div class="text-danger p-3">미리보기를 불러올 수 없습니다.</div>';
        }
    })
    .catch(function(error) {
        console.error('Mobile header preview error:', error);
        previewElement.innerHTML = '<div class="text-danger p-3">미리보기를 불러올 수 없습니다.</div>';
    });
}

$(document).ready(function() {
    // 툴팁 초기화
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // 모바일 헤더 미리보기 초기화
    updateMobileHeaderPreview();
    
    // 모바일 헤더 설정 변경 시 미리보기 업데이트
    $(document).on('change', '.mobile-header-preview-select, #mobile_menu_icon, #mobile_menu_direction, #mobile_menu_icon_border, #mobile_menu_login_widget', function() {
        updateMobileHeaderPreview();
    });
    
    // 헤더 테두리 체크박스 변경 시 설정 표시/숨김
    $('#header_border').on('change', function() {
        if ($(this).is(':checked')) {
            $('#header_border_settings').show();
        } else {
            $('#header_border_settings').hide();
        }
        // 미리보기 업데이트
        const headerSelect = document.getElementById('theme_top');
        if (headerSelect && headerSelect.value) {
            updateThemePreview('header', headerSelect.value);
        }
    });
    
    // 헤더 테두리 두께/컬러 변경 시 미리보기 업데이트
    $('#header_border_width, #header_border_color').on('change', function() {
        const headerSelect = document.getElementById('theme_top');
        if (headerSelect && headerSelect.value) {
            updateThemePreview('header', headerSelect.value);
        }
    });
    
    // 메뉴 폰트 설정 변경 시 미리보기 업데이트
    $('#menu_font_size, #menu_font_padding, #menu_font_weight').on('change', function() {
        const headerSelect = document.getElementById('theme_top');
        if (headerSelect && headerSelect.value) {
            updateThemePreview('header', headerSelect.value);
        }
    });
    
    // 그림자 체크박스 변경 시 미리보기 업데이트
    $('#header_shadow').on('change', function() {
        const headerSelect = document.getElementById('theme_top');
        if (headerSelect && headerSelect.value) {
            updateThemePreview('header', headerSelect.value);
        }
    });
    
    // 테마 form 제출 시 체크박스 처리
    $('#themeForm').on('submit', function(e) {
        // 기존 hidden input 모두 제거
        $('input[name="theme_top_header_show"][type="hidden"]').remove();
        $('input[name="top_header_login_show"][type="hidden"]').remove();
        $('input[name="header_sticky"][type="hidden"]').remove();
        $('input[name="menu_login_show"][type="hidden"]').remove();
        $('input[name="header_shadow"][type="hidden"]').remove();
        $('input[name="header_border"][type="hidden"]').remove();
        
        // 체크되지 않은 체크박스만 hidden input으로 값 전달
        // 체크된 체크박스는 value="1"이 자동으로 전달됨
        if (!$('#theme_top_header_show').is(':checked')) {
            $(this).append('<input type="hidden" name="theme_top_header_show" value="0">');
        }
        
        if (!$('#top_header_login_show').is(':checked')) {
            $(this).append('<input type="hidden" name="top_header_login_show" value="0">');
        }
        
        if (!$('#header_sticky').is(':checked')) {
            $(this).append('<input type="hidden" name="header_sticky" value="0">');
        }
        
        if (!$('#menu_login_show').is(':checked')) {
            $(this).append('<input type="hidden" name="menu_login_show" value="0">');
        }
        
        if (!$('#header_shadow').is(':checked')) {
            $(this).append('<input type="hidden" name="header_shadow" value="0">');
        }
        
        if (!$('#header_border').is(':checked')) {
            $(this).append('<input type="hidden" name="header_border" value="0">');
        }
    });

    // 게시판 form 제출 시 체크박스 처리
    $('#boardForm').on('submit', function(e) {
        // 기존 hidden input 모두 제거
        $('input[name="show_views"][type="hidden"]').remove();
        $('input[name="show_datetime"][type="hidden"]').remove();
        
        // 체크되지 않은 체크박스만 hidden input으로 값 전달
        if (!$('#show_views').is(':checked')) {
            $(this).append('<input type="hidden" name="show_views" value="0">');
        }
        
        if (!$('#show_datetime').is(':checked')) {
            $(this).append('<input type="hidden" name="show_datetime" value="0">');
        }
    });

    // 기능 ON/OFF form 제출 시 체크박스 처리
    $('#featureForm').on('submit', function(e) {
        // 기존 hidden input 모두 제거
        $('input[name="show_visitor_count"][type="hidden"]').remove();
        $('input[name="email_notification"][type="hidden"]').remove();
        $('input[name="general_login"][type="hidden"]').remove();
        
        // 체크되지 않은 체크박스만 hidden input으로 값 전달
        if (!$('#show_visitor_count').is(':checked')) {
            $(this).append('<input type="hidden" name="show_visitor_count" value="0">');
        }
        
        if (!$('#email_notification').is(':checked')) {
            $(this).append('<input type="hidden" name="email_notification" value="0">');
        }
        
        if (!$('#general_login').is(':checked')) {
            $(this).append('<input type="hidden" name="general_login" value="0">');
        }
    });

    // 방문자수 증가 버튼 클릭
    $('#increaseVisitorBtn').on('click', function(e) {
        e.preventDefault();
        var adjustValue = parseInt($('#visitor_count_adjust').val()) || 0;
        
        if (adjustValue <= 0) {
            alert('1 이상의 숫자를 입력해주세요.');
            return;
        }
        
        if (!confirm('방문자수를 ' + adjustValue + '만큼 증가시키시겠습니까?')) {
            return;
        }
        
        // AJAX로 방문자수 증가 요청
        $.ajax({
            url: '{{ route("admin.settings.increase-visitor", ["site" => $site->slug]) }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                amount: adjustValue
            },
            success: function(response) {
                if (response.success) {
                    alert('방문자수가 ' + adjustValue + '만큼 증가되었습니다.');
                    $('#visitor_count_adjust').val(0);
                } else {
                    alert('방문자수 증가에 실패했습니다: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: function(xhr) {
                var errorMessage = '방문자수 증가에 실패했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    });

    // 로고 타입 변경 시 알림 표시/숨김 및 이미지 업로드 영역 제어
    function toggleLogoType() {
        var logoType = $('#logo_type').val();
        var $logoImageArea = $('[data-type="logo"]').closest('td');
        var $logoDarkImageArea = $('[data-type="logo_dark"]').closest('td');
        var $desktopSize = $('#logo_desktop_size').closest('td');
        var $mobileSize = $('#logo_mobile_size').closest('td');
        
        if (logoType === 'text') {
            $('#logo-text-notice').slideDown();
            $logoImageArea.hide();
            $logoDarkImageArea.hide();
            $desktopSize.hide();
            $mobileSize.hide();
        } else {
            $('#logo-text-notice').slideUp();
            $logoImageArea.show();
            $logoDarkImageArea.show();
            $desktopSize.show();
            $mobileSize.show();
        }
    }

    $('#logo_type').on('change', toggleLogoType);
    toggleLogoType(); // 초기 상태 확인
});
