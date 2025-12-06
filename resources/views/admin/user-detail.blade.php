@extends('layouts.admin')

@section('title', '사용자 상세보기')
@section('page-title', '사용자 상세보기')
@section('page-subtitle', '회원 정보를 확인하고 수정할 수 있습니다')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>회원 정보</h5>
                <a href="{{ route('admin.users', ['site' => $site->slug]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>목록으로
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form id="userUpdateForm" method="POST" action="{{ route('admin.users.update', ['site' => $site->slug, 'user' => $user->id]) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>기본 정보</h6>
                            <div class="mb-3">
                                <label for="id" class="form-label">No</label>
                                <input type="text" class="form-control" id="id" value="{{ $user->id }}" readonly>
                                <small class="form-text text-muted">No는 변경할 수 없습니다.</small>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">아이디 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" value="{{ old('username', $user->username) }}" 
                                       placeholder="아이디를 입력하세요" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" 
                                       placeholder="이름을 입력하세요" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="nickname" class="form-label">닉네임</label>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror" 
                                       id="nickname" name="nickname" value="{{ old('nickname', $user->nickname) }}" 
                                       placeholder="닉네임을 입력하세요">
                                @error('nickname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">이메일 <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" 
                                       placeholder="example@email.com" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($user->email_verified_at)
                                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>인증완료</small>
                                @else
                                    <small class="text-muted"><i class="bi bi-x-circle me-1"></i>미인증</small>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">전화번호</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                                       placeholder="010-1234-5678">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <hr class="my-3">
                            <div class="mb-3">
                                <label for="password" class="form-label">비밀번호 변경</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" 
                                       placeholder="변경할 비밀번호를 입력하세요 (변경하지 않으려면 비워두세요)">
                                <small class="form-text text-muted">비밀번호를 변경하려면 새 비밀번호를 입력하세요. 변경하지 않으려면 비워두세요. (최소 8자 이상)</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="bi bi-gear me-2"></i>설정 정보</h6>
                            <div class="mb-3">
                                <label for="role" class="form-label">역할 <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>사용자</option>
                                    <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>매니저</option>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>관리자</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">등급</label>
                                <div class="form-control" style="background-color: #f8f9fa;">
                                    @php
                                        $adminIcon = $site->getSetting('admin_icon_path', '');
                                        $managerIcon = $site->getSetting('manager_icon_path', '');
                                        $displayType = $site->getSetting('rank_display_type', 'icon');
                                        $userRank = null;
                                        if (!$user->isAdmin() && !$user->isManager()) {
                                            $userRank = $user->getUserRank($site->id);
                                        }
                                    @endphp
                                    @if($user->isAdmin() && $adminIcon)
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('storage/' . $adminIcon) }}" alt="Admin" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                            <span>관리자</span>
                                        </div>
                                    @elseif($user->isManager() && $managerIcon)
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('storage/' . $managerIcon) }}" alt="Manager" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                            <span>매니저</span>
                                        </div>
                                    @elseif($userRank)
                                        <div class="d-flex align-items-center">
                                            @if($displayType === 'icon' && $userRank->icon_path)
                                                <img src="{{ asset('storage/' . $userRank->icon_path) }}" alt="{{ $userRank->name }}" style="width: 20px; height: 20px; object-fit: contain; margin-right: 8px;">
                                            @elseif($displayType === 'color' && $userRank->color)
                                                <span style="color: {{ $userRank->color }}; font-weight: bold; margin-right: 8px;">{{ $userRank->name }}</span>
                                            @else
                                                <span style="margin-right: 8px;">{{ $userRank->name }}</span>
                                            @endif
                                            <span>{{ $userRank->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">등급 없음</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="points" class="form-label">포인트 <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('points') is-invalid @enderror" 
                                       id="points" name="points" value="{{ old('points', $user->points ?? 0) }}" 
                                       min="0" required>
                                @error('points')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">가입일</label>
                                <input type="text" class="form-control" value="{{ $user->created_at->format('Y-m-d H:i:s') }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">최종 수정일</label>
                                <input type="text" class="form-control" value="{{ $user->updated_at->format('Y-m-d H:i:s') }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">최종 접속 IP</label>
                                <input type="text" class="form-control" value="{{ $user->last_login_ip ?? 'N/A' }}" readonly>
                            </div>
                            @if($user->deleted_at)
                            <div class="mb-3">
                                <label class="form-label">탈퇴일</label>
                                <input type="text" class="form-control text-danger" value="{{ $user->deleted_at->format('Y-m-d H:i:s') }}" readonly>
                            </div>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="bi bi-geo-alt me-2"></i>주소 정보</h6>
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">우편번호</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" 
                                           placeholder="우편번호" readonly>
                                    <button type="button" class="btn btn-outline-secondary" id="searchAddressBtn">
                                        <i class="bi bi-search me-1"></i>주소 검색
                                    </button>
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">주소</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address', $user->address) }}" 
                                       placeholder="주소" readonly>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="address_detail" class="form-label">상세주소</label>
                                <input type="text" class="form-control @error('address_detail') is-invalid @enderror" 
                                       id="address_detail" name="address_detail" value="{{ old('address_detail', $user->address_detail) }}" 
                                       placeholder="상세주소">
                                @error('address_detail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    @if($user->referrer)
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="bi bi-people me-2"></i>추천인 정보</h6>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 150px; background-color: #f8f9fa;">추천인 ID</th>
                                        <td>
                                            <a href="{{ route('admin.users.detail', ['site' => $site->slug, 'user' => $user->referrer->id]) }}" class="text-decoration-none">
                                                {{ $user->referrer->id }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #f8f9fa;">추천인 닉네임</th>
                                        <td>{{ $user->referrer->nickname ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #f8f9fa;">추천인 이름</th>
                                        <td>{{ $user->referrer->name }}</td>
                                    </tr>
                                    <tr>
                                        <th style="background-color: #f8f9fa;">추천인 이메일</th>
                                        <td>{{ $user->referrer->email }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @php
                        $referredUsers = $user->referredUsers()->where('site_id', $site->id)->count();
                    @endphp
                    @if($referredUsers > 0)
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="bi bi-person-check me-2"></i>추천한 회원 ({{ $referredUsers }}명)</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>닉네임</th>
                                            <th>이름</th>
                                            <th>이메일</th>
                                            <th>가입일</th>
                                            <th>상세보기</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->referredUsers()->where('site_id', $site->id)->latest()->take(10)->get() as $referredUser)
                                        <tr>
                                            <td>{{ $referredUser->id }}</td>
                                            <td>{{ $referredUser->nickname ?? '-' }}</td>
                                            <td>{{ $referredUser->name }}</td>
                                            <td>{{ $referredUser->email }}</td>
                                            <td>{{ $referredUser->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <a href="{{ route('admin.users.detail', ['site' => $site->slug, 'user' => $referredUser->id]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($referredUsers > 10)
                            <p class="text-muted mt-2">최근 10명만 표시됩니다. 전체 {{ $referredUsers }}명의 추천 회원이 있습니다.</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>저장
                            </button>
                            <a href="{{ route('admin.users', ['site' => $site->slug]) }}" class="btn btn-secondary">
                                <i class="bi bi-x me-2"></i>취소
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title text-success" id="successModalLabel">
                    <i class="bi bi-check-circle me-2"></i>성공
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="successMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
document.getElementById('searchAddressBtn').addEventListener('click', function() {
    new daum.Postcode({
        oncomplete: function(data) {
            document.getElementById('postal_code').value = data.zonecode;
            document.getElementById('address').value = data.address;
            document.getElementById('address_detail').focus();
        }
    }).open();
});

// 폼 제출 처리
document.getElementById('userUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // 버튼 비활성화
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>저장 중...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 성공 모달 표시
            document.getElementById('successMessage').textContent = data.message;
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            
            // 페이지 새로고침 (최신 데이터 반영)
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            // 에러 처리
            alert('오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다. 다시 시도해주세요.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});
</script>
@endpush
