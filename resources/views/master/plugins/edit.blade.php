@extends('layouts.master')

@section('title', '플러그인 수정')
@section('page-title', '플러그인 수정')
@section('page-subtitle', $plugin->name . ' 정보 수정')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>플러그인 정보 수정</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('master.plugins.update', $plugin) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">플러그인 이름 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $plugin->name) }}" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="slug" class="form-label">슬러그 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $plugin->slug) }}" required>
                    <small class="form-text text-muted">영문, 숫자, 하이픈만 사용 가능</small>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">설명</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $plugin->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label for="billing_type" class="form-label">결제 타입 <span class="text-danger">*</span></label>
                    <select class="form-select @error('billing_type') is-invalid @enderror" id="billing_type" name="billing_type" required>
                        <option value="free" {{ old('billing_type', $plugin->billing_type) === 'free' ? 'selected' : '' }}>무료</option>
                        <option value="one_time" {{ old('billing_type', $plugin->billing_type) === 'one_time' ? 'selected' : '' }}>1회 결제</option>
                        <option value="monthly" {{ old('billing_type', $plugin->billing_type) === 'monthly' ? 'selected' : '' }}>월간 구독</option>
                    </select>
                    @error('billing_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3" id="price_container" style="display: {{ old('billing_type', $plugin->billing_type) === 'monthly' ? 'block' : 'none' }};">
                    <label for="price" class="form-label">월간 가격</label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $plugin->price) }}" min="0" step="0.01">
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3" id="one_time_price_container" style="display: {{ old('billing_type', $plugin->billing_type) === 'one_time' ? 'block' : 'none' }};">
                    <label for="one_time_price" class="form-label">1회 결제 가격</label>
                    <input type="number" class="form-control @error('one_time_price') is-invalid @enderror" id="one_time_price" name="one_time_price" value="{{ old('one_time_price', $plugin->one_time_price) }}" min="0" step="0.01">
                    @error('one_time_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="features" class="form-label">포함된 기능</label>
                <div id="features_container">
                    @php
                        $features = old('features', $plugin->features ?? []);
                    @endphp
                    @if(is_array($features) && count($features) > 0)
                        @foreach($features as $index => $feature)
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="features[]" value="{{ $feature }}" placeholder="기능 설명">
                                <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">삭제</button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addFeature()">
                    <i class="bi bi-plus-circle me-1"></i>기능 추가
                </button>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">이미지</label>
                @if($plugin->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $plugin->image) }}" alt="{{ $plugin->name }}" style="max-height: 200px; max-width: 200px;" class="img-thumbnail">
                    </div>
                @endif
                <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                <small class="form-text text-muted">최대 2MB, JPEG, PNG, JPG, GIF, WEBP 지원</small>
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="sort_order" class="form-label">정렬 순서</label>
                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $plugin->sort_order) }}" min="0">
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $plugin->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            활성화
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('master.plugins.index') }}" class="btn btn-secondary">취소</a>
                <button type="submit" class="btn btn-primary">수정</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function addFeature() {
    const container = document.getElementById('features_container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="features[]" placeholder="기능 설명">
        <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">삭제</button>
    `;
    container.appendChild(div);
}

function removeFeature(button) {
    button.closest('.input-group').remove();
}

document.getElementById('billing_type').addEventListener('change', function() {
    const priceContainer = document.getElementById('price_container');
    const oneTimePriceContainer = document.getElementById('one_time_price_container');
    
    if (this.value === 'monthly') {
        priceContainer.style.display = 'block';
        oneTimePriceContainer.style.display = 'none';
    } else if (this.value === 'one_time') {
        priceContainer.style.display = 'none';
        oneTimePriceContainer.style.display = 'block';
    } else {
        priceContainer.style.display = 'none';
        oneTimePriceContainer.style.display = 'none';
    }
});
</script>
@endpush
@endsection

