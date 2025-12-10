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