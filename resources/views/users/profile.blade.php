@extends('layouts.app')

@section('title', '내정보')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>내정보</h4>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil-square me-1"></i>정보변경
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td style="width: 150px;"><strong>이름</strong></td>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td style="width: 150px;"><strong>닉네임</strong></td>
                            <td>{{ $user->nickname ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>포인트</strong></td>
                            <td>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>{{ number_format($user->points ?? 0) }}P</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#pointHistoryModal">
                                        <i class="bi bi-clock-history me-1"></i>포인트내역
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>게시글</strong></td>
                            <td>{{ number_format($stats['posts']) }}개</td>
                        </tr>
                        <tr>
                            <td><strong>댓글</strong></td>
                            <td>{{ number_format($stats['comments']) }}개</td>
                        </tr>
                        <tr>
                            <td><strong>쪽지</strong></td>
                            <td>{{ number_format($stats['messages']) }}개</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 메뉴 버튼 영역 -->
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <a href="{{ route('notifications.index', ['site' => $site->slug ?? 'default']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 profile-menu-card">
                        <div class="card-body text-center p-4 position-relative">
                            <div class="mb-3">
                                <i class="bi bi-bell-fill" style="font-size: 2.5rem; color: #0d6efd;"></i>
                                @php
                                    $unreadNotificationCount = 0;
                                    if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                                        $unreadNotificationCount = \App\Models\Notification::getUnreadCount(auth()->id(), $site->id);
                                    }
                                @endphp
                                @if($unreadNotificationCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.75rem; padding: 0.35em 0.5em;">
                                        {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                    </span>
                                @endif
                            </div>
                            <h6 class="mb-0 fw-bold">알림</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="{{ route('messages.index', ['site' => $site->slug ?? 'default']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 profile-menu-card">
                        <div class="card-body text-center p-4 position-relative">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill" style="font-size: 2.5rem; color: #0d6efd;"></i>
                                @php
                                    $unreadMessageCount = 0;
                                    if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('messages')) {
                                        $unreadMessageCount = \App\Models\Message::getUnreadCount(auth()->id(), $site->id);
                                    }
                                @endphp
                                @if($unreadMessageCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.75rem; padding: 0.35em 0.5em;">
                                        {{ $unreadMessageCount > 99 ? '99+' : $unreadMessageCount }}
                                    </span>
                                @endif
                            </div>
                            <h6 class="mb-0 fw-bold">쪽지함</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <div class="card shadow-sm h-100 profile-menu-card">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-pencil-square" style="font-size: 2.5rem; color: #0d6efd;"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">정보변경</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 profile-menu-card">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-file-text-fill" style="font-size: 2.5rem; color: #0d6efd;"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">내 게시글</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 profile-menu-card">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-bookmark-fill" style="font-size: 2.5rem; color: #0d6efd;"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">저장한 글</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 profile-menu-card">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-chat-dots-fill" style="font-size: 2.5rem; color: #0d6efd;"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">내 댓글</h6>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        @if($site->isMasterSite() && isset($userSites))
            <!-- 내 사이트 현황 (마스터 사이트에서만 표시) -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-house-door me-2"></i>내 사이트 현황</h5>
                        <a href="{{ route('users.my-sites', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-right me-1"></i>전체 보기
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($userSites->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                            <p class="mt-3 text-muted mb-0">아직 생성한 홈페이지가 없습니다.</p>
                            <a href="{{ route('user-sites.select-plan', ['site' => $site->slug ?? 'default']) }}" class="btn btn-primary mt-3">
                                <i class="bi bi-plus-circle me-2"></i>새 홈페이지 만들기
                            </a>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($userSites->take(3) as $userSite)
                                <div class="col-md-4">
                                    <div class="card h-100 border">
                                        <div class="card-body">
                                            <h6 class="card-title mb-2">
                                                @if($userSite->slug)
                                                    <a href="{{ route('home', ['site' => $userSite->slug]) }}" class="text-decoration-none" target="_blank">
                                                        {{ $userSite->name }}
                                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.8rem;"></i>
                                                    </a>
                                                @else
                                                    {{ $userSite->name }}
                                                @endif
                                            </h6>
                                            <p class="text-muted small mb-2">
                                                <i class="bi bi-link-45deg me-1"></i>
                                                @if($userSite->slug)
                                                    <a href="{{ route('home', ['site' => $userSite->slug]) }}" target="_blank" class="text-decoration-none text-muted">
                                                        {{ url('/site/' . $userSite->slug) }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">슬러그 없음</span>
                                                @endif
                                            </p>
                                            
                                            @if($userSite->subscription)
                                                <div class="mb-2">
                                                    <div class="d-flex gap-1 mb-1">
                                                        <span class="badge bg-primary">
                                                            {{ $userSite->subscription->plan->name ?? '플랜 없음' }}
                                                        </span>
                                                        <span class="badge 
                                                            @if($userSite->subscription->status === 'active') bg-success
                                                            @elseif($userSite->subscription->status === 'trial') bg-info
                                                            @elseif($userSite->subscription->status === 'past_due') bg-warning
                                                            @elseif($userSite->subscription->status === 'suspended') bg-danger
                                                            @else bg-secondary
                                                            @endif">
                                                            @if($userSite->subscription->status === 'active') 활성
                                                            @elseif($userSite->subscription->status === 'trial') 체험 중
                                                            @elseif($userSite->subscription->status === 'past_due') 결제 대기
                                                            @elseif($userSite->subscription->status === 'suspended') 일시 중지
                                                            @else 취소됨
                                                            @endif
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="small text-muted">
                                                        <div class="mb-1">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            <strong>생성일자:</strong> 
                                                            {{ $userSite->created_at->format('Y-m-d') }}
                                                        </div>
                                                        <div class="mb-1">
                                                            <i class="bi bi-server me-1"></i>
                                                            <strong>서버:</strong> 
                                                            @php
                                                                $serverSubscription = $userSite->serverSubscription ?? null;
                                                            @endphp
                                                            @if($serverSubscription && $serverSubscription->plan)
                                                                {{ $serverSubscription->plan->name }}
                                                            @else
                                                                기본 서버
                                                            @endif
                                                        </div>
                                                        <div class="mb-1">
                                                            <i class="bi bi-calendar-x me-1"></i>
                                                            <strong>서버 결제일:</strong> 
                                                            @if($serverSubscription && $serverSubscription->current_period_end)
                                                                {{ $serverSubscription->current_period_end->format('Y-m-d') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mb-2">
                                                    <span class="badge bg-secondary">구독 없음</span>
                                                </div>
                                            @endif
                                            
                                            <div class="d-flex flex-column gap-2 mt-2">
                                                @php
                                                    $hasSubscription = $userSite->subscription;
                                                    $isActive = $hasSubscription && $userSite->subscription->status === 'active';
                                                    $isFreePlan = $hasSubscription && $userSite->subscription->plan && $userSite->subscription->plan->billing_type === 'free';
                                                @endphp
                                                @if($hasSubscription)
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('user-sites.change-plan', ['site' => $site->slug ?? 'default', 'userSite' => $userSite->slug]) }}" class="btn btn-sm btn-outline-secondary flex-fill plan-change-btn">
                                                            <i class="bi bi-arrow-left-right me-1"></i><span>플랜 변경하기</span>
                                                        </a>
                                                        @if($isActive && !$isFreePlan)
                                                            <a href="{{ route('user-sites.server-upgrade', ['site' => $site->slug ?? 'default', 'userSite' => $userSite->slug]) }}" class="btn btn-sm btn-outline-secondary flex-fill server-upgrade-btn">
                                                                <i class="bi bi-server me-1"></i><span>서버 업그레이드</span>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endif
                                                <div class="d-flex gap-2">
                                                    @if($userSite->slug)
                                                        <a href="{{ route('home', ['site' => $userSite->slug]) }}" class="btn btn-sm btn-outline-primary flex-fill" target="_blank">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i>보기
                                                        </a>
                                                    @else
                                                        <span class="btn btn-sm btn-outline-secondary flex-fill disabled">슬러그 없음</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($userSites->count() > 3)
                            <div class="mt-3 text-center">
                                <a href="{{ route('users.my-sites', ['site' => $site->slug ?? 'default']) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-right me-1"></i>전체 {{ $userSites->count() }}개 사이트 보기
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.profile-menu-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.profile-menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    border-color: #0d6efd;
}

.profile-menu-card .card-body {
    transition: all 0.3s ease;
}

.profile-menu-card:hover .card-body {
    background-color: #f8f9fa;
}

.profile-menu-card h6 {
    color: #212529;
    transition: color 0.3s ease;
}

.profile-menu-card:hover h6 {
    color: #0d6efd;
}
</style>

<!-- 정보변경 모달 -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>정보변경
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProfileForm" method="POST" action="{{ route('users.profile.update', ['site' => $site->slug ?? 'default']) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">이름 <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="edit_name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_nickname" class="form-label">닉네임</label>
                        <input type="text" 
                               class="form-control @error('nickname') is-invalid @enderror" 
                               id="edit_nickname" 
                               name="nickname" 
                               value="{{ old('nickname', $user->nickname) }}">
                        @error('nickname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">이메일 <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="edit_email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">전화번호</label>
                        <input type="text" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               id="edit_phone" 
                               name="phone" 
                               value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">비밀번호 변경</label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="edit_password" 
                               name="password" 
                               placeholder="변경하지 않으려면 비워두세요">
                        <small class="form-text text-muted">비밀번호를 변경하지 않으려면 비워두세요.</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_password_confirmation" class="form-label">비밀번호 확인</label>
                        <input type="password" 
                               class="form-control" 
                               id="edit_password_confirmation" 
                               name="password_confirmation" 
                               placeholder="비밀번호 확인">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                        저장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 포인트 내역 모달 -->
<div class="modal fade" id="pointHistoryModal" tabindex="-1" aria-labelledby="pointHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pointHistoryModalLabel">
                    <i class="bi bi-clock-history me-2"></i>포인트 내역
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pointHistoryContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">로딩 중...</span>
                        </div>
                        <p class="mt-3 text-muted">포인트 내역을 불러오는 중...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pointHistoryModal = document.getElementById('pointHistoryModal');
    const pointHistoryContent = document.getElementById('pointHistoryContent');
    
    // URL 해시가 #point-history인 경우 모달 자동 열기
    if (window.location.hash === '#point-history') {
        const modal = new bootstrap.Modal(pointHistoryModal);
        modal.show();
        // 해시 제거 (모달이 닫힌 후에도 해시가 남아있지 않도록)
        history.replaceState(null, null, window.location.pathname);
    }
    
    pointHistoryModal.addEventListener('show.bs.modal', function() {
        // 모달이 열릴 때 포인트 내역 로드
        loadPointHistory();
    });
    
    function loadPointHistory() {
        pointHistoryContent.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">로딩 중...</span>
                </div>
                <p class="mt-3 text-muted">포인트 내역을 불러오는 중...</p>
            </div>
        `;
        
        fetch('{{ route("users.point-history", ["site" => $site->slug ?? "default"]) }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.history && data.history.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-hover">';
                    html += '<thead class="table-light"><tr><th>날짜</th><th>내용</th><th>포인트</th><th>잔액</th></tr></thead>';
                    html += '<tbody>';
                    
                    data.history.forEach(function(item) {
                        const pointClass = item.points > 0 ? 'text-success' : 'text-danger';
                        const pointSign = item.points > 0 ? '+' : '';
                        html += `<tr>
                            <td>${item.date}</td>
                            <td>${item.description}</td>
                            <td class="${pointClass}">${pointSign}${item.points.toLocaleString()}</td>
                            <td>${item.balance.toLocaleString()}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    pointHistoryContent.innerHTML = html;
                } else {
                    pointHistoryContent.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                            <p class="mt-3 text-muted">포인트 내역이 없습니다.</p>
                        </div>
                    `;
                }
            } else {
                pointHistoryContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>포인트 내역을 불러오는 중 오류가 발생했습니다.
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading point history:', error);
            pointHistoryContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>포인트 내역을 불러오는 중 오류가 발생했습니다.
                </div>
            `;
        });
    }
});

// 정보변경 폼 제출 처리
document.getElementById('editProfileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner-border');
    const originalBtnText = submitBtn.innerHTML;
    
    // 버튼 비활성화 및 로딩 표시
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
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
            // 성공 메시지 표시
            alert(data.message || '정보가 성공적으로 변경되었습니다.');
            // 페이지 새로고침
            window.location.reload();
        } else {
            // 에러 처리
            if (data.errors) {
                // 폼 에러 표시
                Object.keys(data.errors).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.parentElement.querySelector('.invalid-feedback') || document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = data.errors[key][0];
                        if (!input.parentElement.querySelector('.invalid-feedback')) {
                            input.parentElement.appendChild(feedback);
                        }
                    }
                });
            }
            alert(data.message || '정보 변경 중 오류가 발생했습니다.');
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('정보 변경 중 오류가 발생했습니다.');
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
});
</script>
@endsection

@push('styles')
<style>
    a.btn.btn-outline-secondary.plan-change-btn,
    a.btn.btn-outline-secondary.server-upgrade-btn {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        color: #6c757d !important;
    }
    
    a.btn.btn-outline-secondary.plan-change-btn:hover,
    a.btn.btn-outline-secondary.plan-change-btn:focus,
    a.btn.btn-outline-secondary.plan-change-btn:active,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus,
    a.btn.btn-outline-secondary.server-upgrade-btn:active {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: #ffffff !important;
    }
    
    a.btn.btn-outline-secondary.plan-change-btn:hover,
    a.btn.btn-outline-secondary.plan-change-btn:hover *,
    a.btn.btn-outline-secondary.plan-change-btn:hover i,
    a.btn.btn-outline-secondary.plan-change-btn:hover span,
    a.btn.btn-outline-secondary.plan-change-btn:focus,
    a.btn.btn-outline-secondary.plan-change-btn:focus *,
    a.btn.btn-outline-secondary.plan-change-btn:focus i,
    a.btn.btn-outline-secondary.plan-change-btn:focus span,
    a.btn.btn-outline-secondary.plan-change-btn:active,
    a.btn.btn-outline-secondary.plan-change-btn:active *,
    a.btn.btn-outline-secondary.plan-change-btn:active i,
    a.btn.btn-outline-secondary.plan-change-btn:active span,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover *,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover i,
    a.btn.btn-outline-secondary.server-upgrade-btn:hover span,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus *,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus i,
    a.btn.btn-outline-secondary.server-upgrade-btn:focus span,
    a.btn.btn-outline-secondary.server-upgrade-btn:active,
    a.btn.btn-outline-secondary.server-upgrade-btn:active *,
    a.btn.btn-outline-secondary.server-upgrade-btn:active i,
    a.btn.btn-outline-secondary.server-upgrade-btn:active span {
        color: #ffffff !important;
    }
</style>
@endpush




