
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