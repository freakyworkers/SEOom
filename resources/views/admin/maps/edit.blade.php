@extends('layouts.admin')

@section('title', '지도 수정')
@section('page-title', '지도 수정')
@section('page-subtitle', '지도 정보를 수정합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>지도 수정</h5>
            </div>
            <div class="card-body">
                <form id="mapForm">
                    <input type="hidden" id="map_id" name="id" value="{{ $map->id }}">
                    
                    <div class="mb-3">
                        <label for="map_name" class="form-label">지도 이름</label>
                        <input type="text" class="form-control" id="map_name" name="name" value="{{ $map->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="map_type" class="form-label">지도 타입</label>
                        <select class="form-select" id="map_type" name="map_type" required onchange="updateMapPreview()">
                            <option value="">선택하세요</option>
                            @foreach($availableMapTypes as $type => $label)
                                <option value="{{ $type }}" {{ $map->map_type === $type ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if(empty($availableMapTypes))
                            <small class="text-danger">사용 가능한 지도 서비스가 없습니다. 마스터 콘솔에서 지도 API 키를 설정해주세요.</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="map_address" class="form-label">주소 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="map_address" name="address" value="{{ $map->address }}" required placeholder="예: 서울특별시 강남구 테헤란로 152">
                            <button type="button" class="btn btn-outline-primary" onclick="searchAddress()" id="searchAddressBtn">
                                <i class="bi bi-search me-1"></i>주소 검색
                            </button>
                        </div>
                        <small class="text-muted">주소를 입력하고 '주소 검색' 버튼을 클릭하면 자동으로 좌표가 설정됩니다.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="map_latitude" class="form-label">위도</label>
                                <input type="number" step="any" class="form-control" id="map_latitude" name="latitude" value="{{ $map->latitude }}" onchange="updateMapPreview()" readonly>
                                <small class="text-muted">주소 검색 시 자동으로 설정됩니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="map_longitude" class="form-label">경도</label>
                                <input type="number" step="any" class="form-control" id="map_longitude" name="longitude" value="{{ $map->longitude }}" onchange="updateMapPreview()" readonly>
                                <small class="text-muted">주소 검색 시 자동으로 설정됩니다.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="map_zoom" class="form-label">줌 레벨</label>
                        <input type="number" class="form-control" id="map_zoom" name="zoom" min="1" max="20" value="{{ $map->zoom ?? 15 }}" onchange="updateMapPreview()">
                        <small class="text-muted">1(가장 넓게) ~ 20(가장 좁게)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">지도 미리보기</label>
                        <div id="mapPreview" style="width: 100%; height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                            <p class="text-muted mb-0">주소를 입력하면 지도가 표시됩니다.</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.maps.index', ['site' => $site->slug]) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>목록으로
                        </a>
                        <button type="button" class="btn btn-primary" onclick="saveMap()">
                            <i class="bi bi-check-circle me-1"></i>저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let mapInstance = null;
let markerInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    updateMapPreview();
});

function updateMapPreview() {
    const mapType = document.getElementById('map_type').value;
    const address = document.getElementById('map_address').value;
    const latitude = parseFloat(document.getElementById('map_latitude').value);
    const longitude = parseFloat(document.getElementById('map_longitude').value);
    const zoom = parseInt(document.getElementById('map_zoom').value) || 15;

    const previewDiv = document.getElementById('mapPreview');
    
    if (!mapType) {
        previewDiv.innerHTML = '<p class="text-muted mb-0">지도 타입을 선택해주세요.</p>';
        return;
    }
    
    if (!address && (!latitude || !longitude)) {
        previewDiv.innerHTML = '<p class="text-muted mb-0">주소를 입력하면 지도가 표시됩니다.</p>';
        return;
    }

    // 기존 지도 인스턴스 제거
    if (mapInstance) {
        mapInstance = null;
        markerInstance = null;
    }

    previewDiv.innerHTML = '';

    const googleApiKey = '{{ $googleApiKey ?? "" }}';
    const naverApiKey = '{{ $naverApiKey ?? "" }}';
    const kakaoApiKey = '{{ $kakaoApiKey ?? "" }}';

    if (mapType === 'google' && googleApiKey) {
        // 구글 지도 미리보기
        const iframe = document.createElement('iframe');
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        iframe.style.border = 'none';
        
        let src = `https://www.google.com/maps/embed/v1/place?key=${googleApiKey}&q=`;
        if (latitude && longitude) {
            src = `https://www.google.com/maps/embed/v1/view?key=${googleApiKey}&center=${latitude},${longitude}&zoom=${zoom}`;
        } else {
            src += encodeURIComponent(address);
        }
        
        iframe.src = src;
        previewDiv.appendChild(iframe);
    } else if (mapType === 'kakao' && kakaoApiKey) {
        // 카카오맵 미리보기
        previewDiv.innerHTML = '<p class="text-muted mb-0">카카오맵 미리보기는 저장 후 확인할 수 있습니다.</p>';
    } else if (mapType === 'naver' && naverApiKey) {
        // 네이버 지도 미리보기
        previewDiv.innerHTML = '<p class="text-muted mb-0">네이버 지도 미리보기는 저장 후 확인할 수 있습니다.</p>';
    } else {
        previewDiv.innerHTML = '<p class="text-muted mb-0">지도 API 키가 설정되지 않았습니다. 마스터 콘솔에서 API 키를 설정해주세요.</p>';
    }
}

function searchAddress() {
    const address = document.getElementById('map_address').value;
    const mapType = document.getElementById('map_type').value;
    
    if (!address) {
        alert('주소를 입력해주세요.');
        return;
    }
    
    if (!mapType) {
        alert('지도 타입을 선택해주세요.');
        return;
    }
    
    const searchBtn = document.getElementById('searchAddressBtn');
    const originalText = searchBtn.innerHTML;
    searchBtn.disabled = true;
    searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>검색 중...';
    
    fetch('{{ route("admin.maps.geocode", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            address: address,
            map_type: mapType
        })
    })
    .then(response => response.json())
    .then(data => {
        searchBtn.disabled = false;
        searchBtn.innerHTML = originalText;
        
        if (data.success) {
            document.getElementById('map_latitude').value = data.latitude;
            document.getElementById('map_longitude').value = data.longitude;
            updateMapPreview();
            alert('주소 검색이 완료되었습니다.');
        } else {
            alert('주소를 찾을 수 없습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        searchBtn.disabled = false;
        searchBtn.innerHTML = originalText;
        console.error('Error:', error);
        alert('주소 검색 중 오류가 발생했습니다.');
    });
}

function saveMap() {
    const form = document.getElementById('mapForm');
    const formData = new FormData(form);
    const mapId = document.getElementById('map_id').value;
    
    // 필수 필드 검증
    const name = document.getElementById('map_name').value.trim();
    const mapType = document.getElementById('map_type').value;
    const address = document.getElementById('map_address').value.trim();
    
    if (!name) {
        alert('지도 이름을 입력해주세요.');
        document.getElementById('map_name').focus();
        return;
    }
    
    if (!mapType) {
        alert('지도 타입을 선택해주세요.');
        document.getElementById('map_type').focus();
        return;
    }
    
    if (!address) {
        alert('주소를 입력해주세요.');
        document.getElementById('map_address').focus();
        return;
    }
    
    // PUT 메서드 사용 시 _method 필드 추가
    formData.append('_method', 'PUT');
    
    fetch('{{ route("admin.maps.update", ["site" => $site->slug, "map" => $map->id]) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // 응답 타입 확인
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error('서버 오류가 발생했습니다.');
            });
        }
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("admin.maps.index", ["site" => $site->slug]) }}';
        } else {
            // Validation 오류 처리
            let errorMessage = '오류가 발생했습니다.';
            if (data.message) {
                errorMessage = data.message;
            } else if (data.errors) {
                const errorMessages = [];
                for (const field in data.errors) {
                    errorMessages.push(data.errors[field].join(', '));
                }
                errorMessage = errorMessages.join('\n');
            }
            alert('오류: ' + errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다: ' + error.message);
    });
}
</script>
@endpush
@endsection


