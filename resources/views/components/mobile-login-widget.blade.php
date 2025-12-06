@php
    // 포인트 컬러 가져오기
    $pointColor = $themeDarkMode === 'dark' ? $site->getSetting('color_dark_point_main', '#ffffff') : $site->getSetting('color_light_point_main', '#0d6efd');
    
    // 사용자 정보
    $user = auth()->user();
@endphp

@auth
    {{-- 로그인 후: 사용자 이름 버튼 (드롭다운) --}}
    <div class="dropdown">
        <button class="btn btn-link text-decoration-none p-0 border-0 bg-transparent dropdown-toggle" type="button" id="mobileUserDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="color: {{ $headerTextColor }}; font-size: 0.875rem;">
            {{ $user->name }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mobileUserDropdown">
            <li><a class="dropdown-item" href="{{ route('users.profile', ['site' => $site->slug ?? 'default']) }}">내정보</a></li>
            @if(($site->isMasterSite() ?? false))
                <li><a class="dropdown-item" href="{{ route('users.my-sites', ['site' => $site->slug ?? 'default']) }}">내 홈페이지</a></li>
            @endif
            <li><a class="dropdown-item" href="{{ route('users.my-posts', ['site' => $site->slug ?? 'default']) }}">내 게시글</a></li>
            <li><a class="dropdown-item" href="{{ route('users.my-comments', ['site' => $site->slug ?? 'default']) }}">내 댓글</a></li>
            <li><a class="dropdown-item" href="{{ route('users.saved-posts', ['site' => $site->slug ?? 'default']) }}">저장한 글</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('logout', ['site' => $site->slug ?? 'default']) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">로그아웃</button>
                </form>
            </li>
        </ul>
    </div>
@else
    {{-- 로그인 전: 로그인/회원가입 버튼 --}}
    <div class="d-flex gap-2">
        <a href="{{ route('login', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm" style="background-color: {{ $pointColor }}; color: white; border: none; padding: 0.25rem 0.75rem; font-size: 0.875rem;">
            로그인
        </a>
        <a href="{{ route('register', ['site' => $site->slug ?? 'default']) }}" class="btn btn-sm btn-outline-secondary" style="border-color: {{ $headerTextColor }}; color: {{ $headerTextColor }}; padding: 0.25rem 0.75rem; font-size: 0.875rem;">
            회원가입
        </a>
    </div>
@endauth



