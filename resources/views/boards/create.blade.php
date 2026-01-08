@extends('layouts.app')

@section('title', '게시??만들�?)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-plus-circle"></i> 게시??만들�?/h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('boards.store', ['site' => $site]) }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">게시???�름 <span class="text-danger">*</span></label>
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

                    <div class="mb-3">
                        <label for="slug" class="form-label">URL ?�러�?/label>
                        <input type="text" 
                               class="form-control @error('slug') is-invalid @enderror" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug') }}">
                        <small class="form-text text-muted">비워?�면 게시???�름?�로 ?�동 ?�성?�니??</small>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">?�명</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="order" class="form-label">?�렬 ?�서</label>
                        <input type="number" 
                               class="form-control @error('order') is-invalid @enderror" 
                               id="order" 
                               name="order" 
                               value="{{ old('order', 0) }}">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            ?�성??
                        </label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('boards.index', ['site' => $site]) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> 취소
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> ?�성
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection













