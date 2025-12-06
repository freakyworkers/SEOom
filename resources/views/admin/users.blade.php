@extends('layouts.admin')

@section('title', '사용자 관리')
@section('page-title', '사용자 관리')
@section('page-subtitle', '사이트 사용자를 관리할 수 있습니다')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>사용자 목록</h5>
        <span class="badge bg-primary">총 {{ $users->total() }}명</span>
    </div>
    <div class="card-body">
        {{-- 검색 및 필터 --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" action="{{ route('admin.users', ['site' => $site->slug]) }}" class="d-flex gap-2">
                    <select name="search_type" class="form-select" style="width: auto;">
                        <option value="all" {{ request('search_type', 'all') == 'all' ? 'selected' : '' }}>전체</option>
                        <option value="username" {{ request('search_type') == 'username' ? 'selected' : '' }}>아이디</option>
                        <option value="name" {{ request('search_type') == 'name' ? 'selected' : '' }}>이름</option>
                        <option value="nickname" {{ request('search_type') == 'nickname' ? 'selected' : '' }}>닉네임</option>
                    </select>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="검색어를 입력하세요" 
                           value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.users', ['site' => $site->slug]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="col-md-6 text-end">
                <form method="GET" action="{{ route('admin.users', ['site' => $site->slug]) }}" class="d-inline-flex align-items-center gap-2">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('search_type'))
                        <input type="hidden" name="search_type" value="{{ request('search_type') }}">
                    @endif
                    <label for="per_page" class="mb-0">페이지당:</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="20" {{ request('per_page', 15) == 20 ? 'selected' : '' }}>20</option>
                        <option value="30" {{ request('per_page', 15) == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>
            </div>
        </div>

        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th style="width: 60px;">아이디</th>
                            <th>닉네임</th>
                            <th>이름</th>
                            <th>
                                등급
                                <div class="d-inline-flex flex-column ms-1" style="font-size: 0.7rem;">
                                    <a href="{{ route('admin.users', array_merge(['site' => $site->slug], request()->except(['sort_by', 'sort_order']), ['sort_by' => 'rank', 'sort_order' => request('sort_by') == 'rank' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                                       class="text-decoration-none text-muted" 
                                       style="line-height: 0.8;">
                                        <i class="bi bi-chevron-up"></i>
                                    </a>
                                    <a href="{{ route('admin.users', array_merge(['site' => $site->slug], request()->except(['sort_by', 'sort_order']), ['sort_by' => 'rank', 'sort_order' => request('sort_by') == 'rank' && request('sort_order') == 'desc' ? 'asc' : 'desc'])) }}" 
                                       class="text-decoration-none text-muted" 
                                       style="line-height: 0.8;">
                                        <i class="bi bi-chevron-down"></i>
                                    </a>
                                </div>
                            </th>
                            <th style="width: 100px;">
                                포인트
                                <div class="d-inline-flex flex-column ms-1" style="font-size: 0.7rem;">
                                    <a href="{{ route('admin.users', array_merge(['site' => $site->slug], request()->except(['sort_by', 'sort_order']), ['sort_by' => 'points', 'sort_order' => request('sort_by') == 'points' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                                       class="text-decoration-none text-muted" 
                                       style="line-height: 0.8;">
                                        <i class="bi bi-chevron-up"></i>
                                    </a>
                                    <a href="{{ route('admin.users', array_merge(['site' => $site->slug], request()->except(['sort_by', 'sort_order']), ['sort_by' => 'points', 'sort_order' => request('sort_by') == 'points' && request('sort_order') == 'desc' ? 'asc' : 'desc'])) }}" 
                                       class="text-decoration-none text-muted" 
                                       style="line-height: 0.8;">
                                        <i class="bi bi-chevron-down"></i>
                                    </a>
                                </div>
                            </th>
                            <th style="width: 120px;">역할</th>
                            <th style="width: 150px;">
                                가입일
                                <div class="d-inline-flex flex-column ms-1" style="font-size: 0.7rem;">
                                    <a href="{{ route('admin.users', array_merge(['site' => $site->slug], request()->except(['sort_by', 'sort_order']), ['sort_by' => 'created_at', 'sort_order' => request('sort_by') == 'created_at' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                                       class="text-decoration-none text-muted" 
                                       style="line-height: 0.8;">
                                        <i class="bi bi-chevron-up"></i>
                                    </a>
                                    <a href="{{ route('admin.users', array_merge(['site' => $site->slug], request()->except(['sort_by', 'sort_order']), ['sort_by' => 'created_at', 'sort_order' => request('sort_by') == 'created_at' && request('sort_order') == 'desc' ? 'asc' : 'desc'])) }}" 
                                       class="text-decoration-none text-muted" 
                                       style="line-height: 0.8;">
                                        <i class="bi bi-chevron-down"></i>
                                    </a>
                                </div>
                            </th>
                            <th style="width: 100px;">작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // 가입 순서대로 No 계산 (오래된 사용자가 1번)
                            // 전체 사용자 중에서 이 사용자의 가입 순서를 찾기 (마스터 사용자 제외)
                            $masterUserEmails = \App\Models\MasterUser::pluck('email')->toArray();
                            $allUsersQuery = \App\Models\User::where('site_id', $site->id);
                            if (!empty($masterUserEmails)) {
                                $allUsersQuery->whereNotIn('email', $masterUserEmails);
                            }
                            $allUserIds = $allUsersQuery->orderBy('created_at', 'asc')
                                ->pluck('id')
                                ->toArray();
                            $userOrderMap = array_flip($allUserIds); // id => 순서 (0부터 시작)
                        @endphp
                        @foreach($users as $user)
                            <tr>
                                <td>{{ ($userOrderMap[$user->id] ?? 0) + 1 }}</td>
                                <td>{{ $user->username ?? $user->id }}</td>
                                <td>{{ $user->nickname ?? '-' }}</td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                </td>
                                <td>
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
                                            <img src="{{ asset('storage/' . $adminIcon) }}" alt="Admin" style="width: 20px; height: 20px; object-fit: contain; margin-right: 4px;">
                                            <span>관리자</span>
                                        </div>
                                    @elseif($user->isManager() && $managerIcon)
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('storage/' . $managerIcon) }}" alt="Manager" style="width: 20px; height: 20px; object-fit: contain; margin-right: 4px;">
                                            <span>매니저</span>
                                        </div>
                                    @elseif($userRank)
                                        <div class="d-flex align-items-center">
                                            @if($displayType === 'icon' && $userRank->icon_path)
                                                <img src="{{ asset('storage/' . $userRank->icon_path) }}" alt="{{ $userRank->name }}" style="width: 20px; height: 20px; object-fit: contain; margin-right: 4px;">
                                            @elseif($displayType === 'color' && $userRank->color)
                                                <span style="color: {{ $userRank->color }}; font-weight: bold; margin-right: 4px;">{{ $userRank->name }}</span>
                                            @else
                                                <span style="margin-right: 4px;">{{ $userRank->name }}</span>
                                            @endif
                                            <span>{{ $userRank->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ number_format($user->points ?? 0) }}P</td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger">관리자</span>
                                    @elseif($user->role === 'manager')
                                        <span class="badge bg-warning text-dark">매니저</span>
                                    @else
                                        <span class="badge bg-secondary">사용자</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $user->created_at->format('Y-m-d') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.users.detail', ['site' => $site->slug, 'user' => $user->id]) }}" 
                                           class="btn btn-outline-primary" 
                                           title="상세보기">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    @php
                        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                        $pointColor = $themeDarkMode === 'dark' 
                            ? $site->getSetting('color_dark_point_main', '#ffffff')
                            : $site->getSetting('color_light_point_main', '#0d6efd');
                    @endphp
                    {{ $users->appends(request()->except('page'))->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3 mb-2">등록된 사용자가 없습니다</h4>
                @if(request('search'))
                    <p class="text-muted">검색 결과가 없습니다.</p>
                @endif
            </div>
        @endif

        <!-- 사용자 추가 버튼 -->
        <div class="mt-4 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus me-1"></i>사용자 추가하기
            </button>
        </div>
    </div>
</div>

<!-- 사용자 추가 모달 -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">사용자 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_username" class="form-label">아이디 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_username" name="username" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_name" class="form-label">이름 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_nickname" class="form-label">닉네임</label>
                            <input type="text" class="form-control" id="add_nickname" name="nickname">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_email" class="form-label">이메일 <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="add_email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_password" class="form-label">비밀번호 <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add_password" name="password" required minlength="8">
                            <small class="form-text text-muted">최소 8자 이상</small>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_phone" class="form-label">전화번호</label>
                            <input type="text" class="form-control" id="add_phone" name="phone" placeholder="010-1234-5678">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="add_postal_code" class="form-label">우편번호</label>
                            <input type="text" class="form-control" id="add_postal_code" name="postal_code">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="add_address" class="form-label">주소</label>
                            <input type="text" class="form-control" id="add_address" name="address">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_address_detail" class="form-label">상세주소</label>
                        <input type="text" class="form-control" id="add_address_detail" name="address_detail">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_role" class="form-label">역할 <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_role" name="role" required>
                                <option value="user" selected>사용자</option>
                                <option value="manager">매니저</option>
                                <option value="admin">관리자</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_points" class="form-label">초기 포인트</label>
                            <input type="number" class="form-control" id="add_points" name="points" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">추가</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addUserForm = document.getElementById('addUserForm');
    const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));

    addUserForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(addUserForm);
        const data = Object.fromEntries(formData.entries());

        // 전화번호 자동 포맷팅
        if (data.phone) {
            data.phone = data.phone.replace(/-/g, '').replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        }

        fetch('{{ route("admin.users.store", ["site" => $site->slug]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addUserModal.hide();
                addUserForm.reset();
                location.reload();
            } else {
                // 에러 메시지 표시
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const input = addUserForm.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = data.errors[field][0];
                            }
                        }
                    });
                } else if (data.message) {
                    alert(data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('사용자 추가 중 오류가 발생했습니다.');
        });
    });

    // 모달이 닫힐 때 폼 초기화
    document.getElementById('addUserModal').addEventListener('hidden.bs.modal', function() {
        addUserForm.reset();
        addUserForm.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    });

    // 전화번호 자동 포맷팅
    const phoneInput = document.getElementById('add_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/-/g, '');
            if (value.length > 3 && value.length <= 7) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            } else if (value.length > 7) {
                value = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7, 11);
            }
            e.target.value = value;
        });
    }
});
</script>
@endsection
