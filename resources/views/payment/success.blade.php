@extends('layouts.app')

@section('title', '결제 완료')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0"><i class="bi bi-check-circle me-2"></i>결제 완료</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="mb-3">결제가 성공적으로 완료되었습니다!</h5>
                    
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        구독이 정상적으로 시작되었습니다.<br>
                        모든 기능을 사용하실 수 있습니다.
                    </p>

                    <div class="d-grid gap-2">
                        <a href="/" class="btn btn-primary">
                            <i class="bi bi-house me-2"></i>홈으로 돌아가기
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

