@extends('layouts.app')

@section('title', '결제 실패')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white text-center">
                    <h4 class="mb-0"><i class="bi bi-x-circle me-2"></i>결제 실패</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="mb-3">결제 처리 중 오류가 발생했습니다.</h5>
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        결제가 완료되지 않았습니다.<br>
                        문제가 지속되면 고객센터로 문의해주세요.
                    </p>

                    <div class="d-grid gap-2">
                        <a href="/" class="btn btn-primary">
                            <i class="bi bi-house me-2"></i>홈으로 돌아가기
                        </a>
                        <a href="/plans/brand/subscribe" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>다시 시도
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



