@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>새 사이트 만들기
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <x-alert type="success">{{ session('success') }}</x-alert>
                    @endif

                    @if(isset($plan))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>선택한 플랜:</strong> {{ $plan->name }} 
                            @if($plan->billing_type === 'free')
                                (무료)
                            @elseif($plan->billing_type === 'one_time' && $plan->one_time_price > 0)
                                ({{ number_format($plan->one_time_price) }}원, 1회 결제)
                            @elseif($plan->billing_type === 'monthly' && $plan->price > 0)
                                ({{ number_format($plan->price) }}원/월)
                            @else
                                (무료)
                            @endif
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>안내:</strong> 아래 정보를 입력하여 홈페이지를 생성해주세요.
                    </div>

                    <form method="POST" action="{{ route('user-sites.store', ['site' => $site->slug]) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                사이트 이름 <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus
                                   placeholder="예: 내 홈페이지">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">생성할 사이트의 이름을 입력하세요.</small>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">사이트 주소 (슬러그)</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ url('/site/') }}/</span>
                                <input type="text" 
                                       class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" 
                                       name="slug" 
                                       value="{{ old('slug') }}"
                                       placeholder="자동 생성됩니다">
                            </div>
                            @error('slug')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">비워두면 사이트 이름으로 자동 생성됩니다. 영문, 숫자, 하이픈(-)만 사용 가능합니다.</small>
                        </div>

                        <div class="mb-3">
                            <label for="domain" class="form-label">도메인</label>
                            <input type="text" 
                                   class="form-control @error('domain') is-invalid @enderror" 
                                   id="domain" 
                                   name="domain" 
                                   value=""
                                   placeholder="나중에 설정할 수 있습니다"
                                   disabled
                                   readonly>
                            @error('domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>도메인은 사이트 생성 후 설정할 수 있습니다.
                            </small>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">
                            <i class="bi bi-person-gear me-2"></i>관리자 계정 설정
                        </h5>

                        <div class="mb-3">
                            <label for="login_method" class="form-label">
                                로그인 방식 <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('login_method') is-invalid @enderror" 
                                    id="login_method" 
                                    name="login_method" 
                                    required>
                                <option value="email" {{ old('login_method', 'email') === 'email' ? 'selected' : '' }}>이메일 입력 방식</option>
                                <option value="username" {{ old('login_method') === 'username' ? 'selected' : '' }}>아이디 입력 방식</option>
                            </select>
                            @error('login_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">사이트 로그인 시 사용할 인증 방식을 선택합니다.</small>
                        </div>

                        <div class="mb-3">
                            <label for="admin_username" class="form-label">
                                관리자 아이디 <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('admin_username') is-invalid @enderror" 
                                   id="admin_username" 
                                   name="admin_username" 
                                   value="{{ old('admin_username') }}" 
                                   required
                                   placeholder="예: admin">
                            @error('admin_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">생성된 사이트의 관리자 로그인 아이디입니다.</small>
                        </div>

                        <div class="mb-3">
                            <label for="admin_password" class="form-label">
                                관리자 비밀번호 <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control @error('admin_password') is-invalid @enderror" 
                                   id="admin_password" 
                                   name="admin_password" 
                                   value="{{ old('admin_password') }}" 
                                   required
                                   minlength="8"
                                   placeholder="최소 8자 이상">
                            @error('admin_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">생성된 사이트의 관리자 로그인 비밀번호입니다. (최소 8자 이상)</small>
                        </div>

                        <div class="mb-3">
                            <label for="admin_password_confirmation" class="form-label">
                                관리자 비밀번호 확인 <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control @error('admin_password') is-invalid @enderror" 
                                   id="admin_password_confirmation" 
                                   name="admin_password_confirmation" 
                                   required
                                   minlength="8"
                                   placeholder="비밀번호를 다시 입력하세요">
                            @error('admin_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('home', ['site' => $site->slug]) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>취소
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>사이트 생성
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    // 사이트 이름 입력 시 슬러그 자동 생성
    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
            const slug = nameInput.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9가-힣\s-]/g, '') // 영문, 숫자, 한글, 공백, 하이픈만 허용
                .replace(/[\s가-힣]+/g, '-') // 공백과 한글을 하이픈으로 변환
                .replace(/-+/g, '-') // 연속된 하이픈을 하나로
                .replace(/^-|-$/g, ''); // 앞뒤 하이픈 제거
            
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
    });
    
    // 슬러그 수동 입력 시 자동 생성 비활성화
    slugInput.addEventListener('input', function() {
        if (slugInput.value) {
            slugInput.dataset.autoGenerated = 'false';
        }
    });

    // 로그인 방식에 따른 관리자 아이디 필드 유효성 검사
    const loginMethodSelect = document.getElementById('login_method');
    const adminUsernameInput = document.getElementById('admin_username');
    
    function updateUsernameValidation() {
        const loginMethod = loginMethodSelect.value;
        if (loginMethod === 'username') {
            adminUsernameInput.setAttribute('pattern', '[a-zA-Z0-9_]+');
            adminUsernameInput.setAttribute('title', '영문, 숫자, 언더스코어(_)만 사용할 수 있습니다.');
        } else {
            adminUsernameInput.removeAttribute('pattern');
            adminUsernameInput.removeAttribute('title');
        }
    }
    
    loginMethodSelect.addEventListener('change', updateUsernameValidation);
    updateUsernameValidation(); // 초기 설정
});
</script>
@endsection

