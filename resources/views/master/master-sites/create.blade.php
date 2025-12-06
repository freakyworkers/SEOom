@extends('layouts.master')

@section('title', '마스터 사이트 생성')
@section('page-title', '마스터 사이트 생성')
@section('page-subtitle', '세움 빌더 기반의 새로운 마스터 사이트를 생성합니다')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>마스터 사이트 정보</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('master.master-sites.store') }}">
            @csrf

            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>마스터 사이트란?</strong><br>
                세움 빌더 기반의 마스터 사이트로, 회원 가입 후 홈페이지를 제작할 수 있도록 하는 사이트입니다.
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">사이트 이름 <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
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
                           value="{{ old('slug') }}"
                           placeholder="자동 생성됩니다">
                    <small class="form-text text-muted">비워두면 사이트 이름으로 자동 생성됩니다.</small>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="domain" class="form-label">도메인</label>
                    <input type="text" 
                           class="form-control @error('domain') is-invalid @enderror" 
                           id="domain" 
                           name="domain" 
                           value="{{ old('domain') }}"
                           placeholder="example.com">
                    @error('domain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="alert alert-warning">
                <i class="bi bi-info-circle me-2"></i>
                <strong>참고사항:</strong><br>
                • 마스터 사이트는 요금제 제한 없이 모든 기능을 사용할 수 있습니다.<br>
                • 관리자 계정은 마스터 콘솔 로그인과 동일하게 사용됩니다.
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('master.master-sites.index') }}" class="btn btn-secondary me-2">
                    <i class="bi bi-x-circle me-1"></i>취소
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>마스터 사이트 생성
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

