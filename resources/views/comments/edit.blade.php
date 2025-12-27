@extends('layouts.app')

@section('title', '?��? ?�정')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="bi bi-pencil"></i> ?��? ?�정</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('comments.update', ['site' => $site, 'boardSlug' => $boardSlug, 'post' => $post->id, 'comment' => $comment->id]) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="content" class="form-label">?��? ?�용 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" 
                                  name="content" 
                                  rows="5" 
                                  required 
                                  autofocus>{{ old('content', $comment->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('posts.show', ['site' => $site, 'boardSlug' => $boardSlug, 'post' => $post->id]) }}" 
                           class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> 취소
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> ?�정
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection










