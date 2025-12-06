@extends('layouts.master')

@section('title', '마스터 사이트 수정')
@section('page-title', '마스터 사이트 수정')
@section('page-subtitle', $site->name . ' 정보 수정')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>마스터 사이트 정보 수정</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('master.master-sites.update', $site->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">사이트 이름 <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $site->name) }}" 
                           required 
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="slug" class="form-label">슬러그</label>
                    <input type="text" 
                           class="form-control @error('slug') is-invalid @enderror" 
                           id="slug" 
                           name="slug" 
                           value="{{ old('slug', $site->slug) }}">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="domain" class="form-label">도메인</label>
                    <input type="text" 
                           class="form-control @error('domain') is-invalid @enderror" 
                           id="domain" 
                           name="domain" 
                           value="{{ old('domain', $site->domain) }}"
                           placeholder="example.com">
                    @error('domain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">상태 <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" 
                            name="status" 
                            required>
                        <option value="active" {{ old('status', $site->status) === 'active' ? 'selected' : '' }}>활성</option>
                        <option value="suspended" {{ old('status', $site->status) === 'suspended' ? 'selected' : '' }}>정지</option>
                        <option value="deleted" {{ old('status', $site->status) === 'deleted' ? 'selected' : '' }}>삭제됨</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>마스터 사이트는 요금제 제한 없이 모든 기능을 사용할 수 있습니다.</strong>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('master.master-sites.show', $site->id) }}" class="btn btn-secondary me-2">
                    <i class="bi bi-x-circle me-1"></i>취소
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

