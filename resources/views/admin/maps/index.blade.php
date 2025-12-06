@extends('layouts.admin')

@section('title', '지도')
@section('page-title', '지도')
@section('page-subtitle', '지도를 생성하고 관리합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>지도 목록</h5>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="addDefaultMaps()">
                        <i class="bi bi-download me-2"></i>기본 지도 추가
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="showCreateForm()">
                        <i class="bi bi-plus-circle me-2"></i>지도 생성
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(empty($availableMapTypes))
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>안내:</strong> 사용 가능한 지도 서비스가 없습니다. 마스터 콘솔에서 지도 API 키를 설정해주세요.
                    </div>
                @endif
                
                @if($maps->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3">
                            @if(empty($availableMapTypes))
                                마스터 콘솔에서 지도 API 키를 설정한 후 지도를 생성할 수 있습니다.
                            @else
                                생성된 지도가 없습니다.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>이름</th>
                                    <th>지도 타입</th>
                                    <th>주소</th>
                                    <th>생성일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($maps as $map)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.maps.edit', ['site' => $site->slug, 'map' => $map->id]) }}" class="text-decoration-none">
                                                {{ $map->name }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($map->map_type === 'google')
                                                <span class="badge bg-danger">구글 지도</span>
                                            @elseif($map->map_type === 'kakao')
                                                <span class="badge bg-warning text-dark">카카오맵</span>
                                            @else
                                                <span class="badge bg-success">네이버 지도</span>
                                            @endif
                                        </td>
                                        <td>{{ $map->address }}</td>
                                        <td>{{ $map->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.maps.edit', ['site' => $site->slug, 'map' => $map->id]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil me-1"></i>수정
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteMap({{ $map->id }})">
                                                <i class="bi bi-trash me-1"></i>삭제
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 생성/수정 모달 -->
<div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">지도 생성</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="mapForm">
                    <input type="hidden" id="map_id" name="id">
                    
                    <div class="mb-3">
                        <label for="map_name" class="form-label">지도 이름</label>
                        <input type="text" class="form-control" id="map_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="map_type" class="form-label">지도 타입</label>
                        <select class="form-select" id="map_type" name="map_type" required onchange="updateMapPreview()">
                            <option value="">선택하세요</option>
                            @foreach($availableMapTypes as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @if(empty($availableMapTypes))
                            <small class="text-danger">사용 가능한 지도 서비스가 없습니다. 마스터 콘솔에서 지도 API 키를 설정해주세요.</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="map_address" class="form-label">주소 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="map_address" name="address" required placeholder="예: 서울특별시 강남구 테헤란로 152">
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
                                <input type="number" step="any" class="form-control" id="map_latitude" name="latitude" onchange="updateMapPreview()" readonly>
                                <small class="text-muted">주소 검색 시 자동으로 설정됩니다.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="map_longitude" class="form-label">경도</label>
                                <input type="number" step="any" class="form-control" id="map_longitude" name="longitude" onchange="updateMapPreview()" readonly>
                                <small class="text-muted">주소 검색 시 자동으로 설정됩니다.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="map_zoom" class="form-label">줌 레벨</label>
                        <input type="number" class="form-control" id="map_zoom" name="zoom" min="1" max="20" value="15" onchange="updateMapPreview()">
                        <small class="text-muted">1(가장 넓게) ~ 20(가장 좁게)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">지도 미리보기</label>
                        <div id="mapPreview" style="width: 100%; height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                            <p class="text-muted mb-0">주소를 입력하면 지도가 표시됩니다.</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveMap()">저장</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let mapInstance = null;
let markerInstance = null;

function showCreateForm() {
    document.getElementById('mapForm').reset();
    document.getElementById('map_id').value = '';
    document.getElementById('modalTitle').textContent = '지도 생성';
    document.getElementById('mapPreview').innerHTML = '<p class="text-muted mb-0">주소를 입력하면 지도가 표시됩니다.</p>';
    if (mapInstance) {
        mapInstance = null;
        markerInstance = null;
    }
    new bootstrap.Modal(document.getElementById('mapModal')).show();
}

function updateMapPreview() {
    const mapType = document.getElementById('map_type').value;
    const address = document.getElementById('map_address').value;
    const latitude = parseFloat(document.getElementById('map_latitude').value);
    const longitude = parseFloat(document.getElementById('map_longitude').value);
    const zoom = parseInt(document.getElementById('map_zoom').value) || 15;

    const previewDiv = document.getElementById('mapPreview');
    
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
        previewDiv.innerHTML = '<p class="text-muted mb-0">지도 타입을 선택하고 주소를 입력해주세요.</p>';
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
    
    const url = mapId 
        ? '{{ route("admin.maps.update", ["site" => $site->slug, "map" => ":id"]) }}'.replace(':id', mapId)
        : '{{ route("admin.maps.store", ["site" => $site->slug]) }}';
    
    const method = mapId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다.');
    });
}

function deleteMap(id) {
    if (!confirm('정말 이 지도를 삭제하시겠습니까?')) {
        return;
    }
    
    fetch('{{ route("admin.maps.delete", ["site" => $site->slug, "map" => ":id"]) }}'.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('삭제 중 오류가 발생했습니다.');
    });
}

function addDefaultMaps() {
    if (!confirm('기본 지도(구글 지도, 카카오맵, 네이버 지도)를 추가하시겠습니까?\n이미 존재하는 지도는 추가되지 않습니다.')) {
        return;
    }
    
    fetch('{{ route("admin.maps.add-default", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('기본 지도 추가 중 오류가 발생했습니다.');
    });
}
</script>
@endpush
@endsection

