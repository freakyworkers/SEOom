@extends('layouts.admin')

@section('title', $bannerLocations[$location] . ' 배너')
@section('page-title', $bannerLocations[$location] . ' 배너')
@section('page-subtitle', '전체 ' . $banners->count() . '개')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-image me-2"></i>{{ $bannerLocations[$location] }} 배너
            <span class="badge bg-primary ms-2">전체 {{ $banners->count() }}개</span>
        </h5>
        <a href="{{ route('admin.banners.index', ['site' => $site->slug]) }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>목록으로
        </a>
    </div>
    <div class="card-body">
        <form id="bannersForm">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 200px; text-align: center;">이미지</th>
                            <th style="text-align: center;">링크</th>
                            <th style="width: 150px; text-align: center;">새 창으로 띄우기</th>
                            <th style="width: 180px; text-align: center;">등록시각</th>
                            <th style="width: 100px; text-align: center;">정렬</th>
                            <th style="width: 150px; text-align: center;">고정</th>
                            <th style="width: 80px; text-align: center;">삭제</th>
                        </tr>
                    </thead>
                    <tbody id="bannersTableBody">
                        @forelse($banners as $banner)
                            <tr data-banner-id="{{ $banner->id }}">
                                <td style="text-align: center; vertical-align: middle;">
                                    @if($banner->type === 'html')
                                        <div class="banner-html-wrapper" style="position: relative; display: inline-block; max-width: 180px; max-height: 100px; overflow: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 5px;">
                                            <div class="banner-html-preview" data-banner-id="{{ $banner->id }}">
                                                {!! $banner->html_code !!}
                                            </div>
                                        </div>
                                    @else
                                        <div class="banner-image-wrapper" style="position: relative; display: inline-block;">
                                            <img src="{{ asset('storage/' . $banner->image_path) }}" 
                                                 alt="배너 이미지" 
                                                 class="banner-preview-image" 
                                                 style="max-width: 180px; max-height: 100px; width: auto; height: auto; cursor: pointer; border: 1px solid #dee2e6; border-radius: 4px;"
                                                 data-banner-id="{{ $banner->id }}">
                                            <input type="file" 
                                                   class="banner-image-input d-none" 
                                                   accept="image/*"
                                                   data-banner-id="{{ $banner->id }}">
                                        </div>
                                    @endif
                                </td>
                                <td style="vertical-align: middle;">
                                    @if($banner->type === 'html')
                                        <textarea class="form-control form-control-sm banner-html-input" 
                                                  name="html_codes[{{ $banner->id }}]"
                                                  rows="5"
                                                  placeholder="HTML 코드를 입력하세요.">{{ $banner->html_code ?? '' }}</textarea>
                                    @else
                                        <input type="text" 
                                               class="form-control form-control-sm banner-link-input" 
                                               name="links[{{ $banner->id }}]"
                                               value="{{ $banner->link ?? '' }}" 
                                               placeholder="https://example.com">
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="open_new_windows[{{ $banner->id }}]"
                                               value="1"
                                               id="open_new_window_{{ $banner->id }}"
                                               {{ $banner->open_new_window ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    {{ $banner->created_at->format('Y.m.d H:i:s') }}
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div class="btn-group-vertical" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary order-up-btn" 
                                                data-banner-id="{{ $banner->id }}"
                                                {{ $loop->first ? 'disabled' : '' }}>
                                            <i class="bi bi-arrow-up"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary order-down-btn" 
                                                data-banner-id="{{ $banner->id }}"
                                                {{ $loop->last ? 'disabled' : '' }}>
                                            <i class="bi bi-arrow-down"></i>
                                        </button>
                                    </div>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div class="d-flex flex-column align-items-center gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input banner-pinned-top-checkbox" 
                                                   type="checkbox" 
                                                   name="is_pinned_top[{{ $banner->id }}]"
                                                   value="1"
                                                   id="is_pinned_top_{{ $banner->id }}"
                                                   data-banner-id="{{ $banner->id }}"
                                                   {{ $banner->is_pinned_top ?? false ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_pinned_top_{{ $banner->id }}">
                                                최상단
                                            </label>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <label for="pinned_position_{{ $banner->id }}" class="mb-0" style="font-size: 0.875rem;">위치</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm text-center banner-pinned-position-input" 
                                                   name="pinned_position[{{ $banner->id }}]"
                                                   id="pinned_position_{{ $banner->id }}"
                                                   data-banner-id="{{ $banner->id }}"
                                                   value="{{ $banner->pinned_position ?? 0 }}" 
                                                   min="0"
                                                   style="width: 60px;"
                                                   placeholder="0">
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-banner-btn" 
                                            data-banner-id="{{ $banner->id }}">
                                        <i class="bi bi-trash"></i> 삭제
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                                    <p class="mt-3 text-muted">등록된 배너가 없습니다.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                <button type="button" class="btn btn-primary" id="addBannerBtn">
                    <i class="bi bi-plus-circle me-1"></i>배너 추가
                </button>
            </div>
            
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 배너 추가 모달 -->
<div class="modal fade" id="addBannerModal" tabindex="-1" aria-labelledby="addBannerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBannerModalLabel">배너 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addBannerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bannerType" class="form-label">타입 <span class="text-danger">*</span></label>
                        <select class="form-select" id="bannerType" name="type" required>
                            <option value="image">이미지</option>
                            <option value="html">애드센스(HTML)</option>
                        </select>
                    </div>
                    
                    <!-- 이미지 타입 필드 -->
                    <div class="mb-3" id="bannerImageFields">
                        <label for="bannerImage" class="form-label">이미지 <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="bannerImage" name="image" accept="image/*">
                        <small class="form-text text-muted">이미지 파일만 업로드 가능합니다. (최대 5MB)</small>
                        <div id="bannerImagePreview" class="mt-2" style="display: none;">
                            <img id="bannerImagePreviewImg" src="" alt="미리보기" style="max-width: 100%; max-height: 200px;">
                        </div>
                    </div>
                    
                    <!-- HTML 타입 필드 -->
                    <div class="mb-3" id="bannerHtmlFields" style="display: none;">
                        <label for="bannerHtmlCode" class="form-label">HTML 코드 <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="bannerHtmlCode" name="html_code" rows="10" placeholder="애드센스 또는 HTML 코드를 입력하세요."></textarea>
                        <small class="form-text text-muted">애드센스 코드나 기타 HTML 코드를 입력할 수 있습니다.</small>
                    </div>
                    
                    <div class="mb-3" id="bannerLinkFields">
                        <label for="bannerLink" class="form-label">링크</label>
                        <input type="url" class="form-control" id="bannerLink" name="link" placeholder="https://example.com">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="open_new_window" id="bannerOpenNewWindow" value="1">
                            <label class="form-check-label" for="bannerOpenNewWindow">
                                새 창으로 띄우기
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">등록</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bannerLocation = '{{ $location }}';
    const addBannerModal = new bootstrap.Modal(document.getElementById('addBannerModal'));
    
    // 배너 이미지 클릭 시 파일 선택
    document.querySelectorAll('.banner-preview-image').forEach(function(img) {
        img.addEventListener('click', function() {
            const bannerId = this.getAttribute('data-banner-id');
            const fileInput = document.querySelector(`.banner-image-input[data-banner-id="${bannerId}"]`);
            fileInput.click();
        });
    });
    
    // 이미지 파일 선택 시 미리보기 및 업로드
    document.querySelectorAll('.banner-image-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const bannerId = this.getAttribute('data-banner-id');
            const file = this.files[0];
            
            if (file) {
                const formData = new FormData();
                formData.append('banner_id', bannerId);
                formData.append('image', file);
                formData.append('_token', '{{ csrf_token() }}');
                
                fetch('{{ route("admin.banners.update-item", ["site" => $site->slug]) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const img = document.querySelector(`.banner-preview-image[data-banner-id="${bannerId}"]`);
                        img.src = '/storage/' + data.banner.image_path + '?t=' + new Date().getTime();
                        alert('이미지가 변경되었습니다.');
                    } else {
                        alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('오류가 발생했습니다.');
                });
            }
        });
    });
    
    // 정렬 버튼
    document.querySelectorAll('.order-up-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const bannerId = this.getAttribute('data-banner-id');
            updateOrder(bannerId, 'up');
        });
    });
    
    document.querySelectorAll('.order-down-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const bannerId = this.getAttribute('data-banner-id');
            updateOrder(bannerId, 'down');
        });
    });
    
    function updateOrder(bannerId, direction) {
        fetch('{{ route("admin.banners.update-order", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                banner_id: bannerId,
                direction: direction
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    }
    
    // 위치 입력칸 기본값 설정 (0으로 초기화)
    document.querySelectorAll('.banner-pinned-position-input').forEach(function(input) {
        if (!input.value || input.value === '') {
            input.value = '0';
        }
    });
    
    // 삭제 버튼
    document.querySelectorAll('.delete-banner-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('정말로 이 배너를 삭제하시겠습니까?')) {
                return;
            }
            
            const bannerId = this.getAttribute('data-banner-id');
            
            fetch(`/site/{{ $site->slug }}/admin/banners/${bannerId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('오류가 발생했습니다.');
            });
        });
    });
    
    // 배너 추가 버튼
    document.getElementById('addBannerBtn').addEventListener('click', function() {
        addBannerModal.show();
    });
    
    // 배너 타입 변경 시 필드 표시/숨김
    document.getElementById('bannerType').addEventListener('change', function() {
        const type = this.value;
        const imageFields = document.getElementById('bannerImageFields');
        const htmlFields = document.getElementById('bannerHtmlFields');
        const linkFields = document.getElementById('bannerLinkFields');
        const imageInput = document.getElementById('bannerImage');
        const htmlCodeInput = document.getElementById('bannerHtmlCode');
        
        if (type === 'image') {
            imageFields.style.display = 'block';
            htmlFields.style.display = 'none';
            linkFields.style.display = 'block';
            imageInput.required = true;
            htmlCodeInput.required = false;
            htmlCodeInput.value = '';
        } else {
            imageFields.style.display = 'none';
            htmlFields.style.display = 'block';
            linkFields.style.display = 'none';
            imageInput.required = false;
            htmlCodeInput.required = true;
            imageInput.value = '';
            document.getElementById('bannerImagePreview').style.display = 'none';
            document.getElementById('bannerLink').value = ''; // 링크 필드 초기화
        }
    });
    
    // 배너 이미지 미리보기
    document.getElementById('bannerImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('bannerImagePreviewImg').src = e.target.result;
                document.getElementById('bannerImagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    // 배너 추가 폼 제출
    const addBannerForm = document.getElementById('addBannerForm');
    if (addBannerForm) {
        addBannerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>등록 중...';
            
            const formData = new FormData(this);
            formData.append('location', bannerLocation);
            formData.append('type', document.getElementById('bannerType').value);
            
            const url = '{{ route("admin.banners.store", ["site" => $site->slug]) }}';
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const json = JSON.parse(text);
                            throw new Error(json.message || 'HTTP error! status: ' + response.status);
                        } catch (e) {
                            if (e instanceof SyntaxError) {
                                throw new Error('HTTP error! status: ' + response.status + ', body: ' + text.substring(0, 200));
                            }
                            throw e;
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    addBannerModal.hide();
                    addBannerForm.reset();
                    location.reload();
                } else {
                    alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('오류가 발생했습니다: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // 전체 저장 폼 제출
    document.getElementById('bannersForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const bannerIds = [];
        document.querySelectorAll('[data-banner-id]').forEach(function(row) {
            bannerIds.push(row.getAttribute('data-banner-id'));
        });
        
        // location 추가
        formData.append('location', '{{ $location }}');
        
        // 각 banner_id를 개별적으로 추가
        bannerIds.forEach(function(bannerId) {
            formData.append('banner_ids[]', bannerId);
        });
        
        // HTML 코드 필드 추가
        document.querySelectorAll('.banner-html-input').forEach(function(textarea) {
            const match = textarea.name.match(/\[(\d+)\]/);
            if (match) {
                const bannerId = match[1];
                formData.append('html_codes[' + bannerId + ']', textarea.value);
            }
        });
        
        fetch('{{ route("admin.banners.save-all", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('모든 변경사항이 저장되었습니다.');
                location.reload();
            } else {
                alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    });
});
</script>
@endpush
@endsection

