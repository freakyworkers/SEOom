@extends('layouts.app')

@section('title', '출석체크')

@section('content')
@php
    // 다크모드 확인 및 포인트 컬러 가져오기
    $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
    $isDark = $themeDarkMode === 'dark';
    $pointColor = $isDark 
        ? $site->getSetting('color_dark_point_main', '#ffffff')
        : $site->getSetting('color_light_point_main', '#0d6efd');
    
    // 출석 인사 placeholder 가져오기
    $greetings = $settings['greetings'] ?? [];
    $greetingPlaceholder = !empty($greetings) ? $greetings[array_rand($greetings)] : '오늘도 출석체크!';
@endphp
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-3 overflow-hidden">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="bi bi-calendar-check me-2"></i>출석체크</h3>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#pointsInfoModal" style="border-color: {{ $pointColor }}; color: {{ $pointColor }};">
                    <i class="bi bi-info-circle me-1"></i>출첵 포인트 안내
                </button>
            </div>

            @auth
                @if(!$hasAttended)
                    <form action="{{ route('attendance.store', ['site' => $site->slug]) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="d-flex flex-column flex-md-row gap-2 gap-md-3">
                            <input type="text" 
                                   name="greeting" 
                                   class="form-control flex-grow-1" 
                                   placeholder="{{ $greetingPlaceholder }}" 
                                   value="{{ old('greeting') }}"
                                   maxlength="255">
                            <button type="submit" class="btn btn-danger flex-shrink-0">
                                <i class="bi bi-stamp me-1"></i>출석체크 도장찍기
                            </button>
                        </div>
                    </form>
                @else
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-check-circle me-2"></i>오늘 출석체크를 완료했습니다!
                        </div>
                        @if($consecutiveDays > 0)
                            <div>
                                <small>연속 출석일: {{ $consecutiveDays }}일</small>
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>출석체크를 하려면 <a href="{{ route('login', ['site' => $site->slug]) }}" class="alert-link">로그인</a>이 필요합니다.
                </div>
            @endauth
        </div>
    </div>

    <div class="card shadow-sm mb-3 overflow-hidden">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px; text-align: center;">등수</th>
                            <th style="width: 150px; text-align: center;">출석시간</th>
                            <th style="text-align: center;">닉네임</th>
                            <th style="text-align: center;">출석인사</th>
                            <th style="width: 120px; text-align: center;">적립포인트</th>
                            <th style="width: 100px; text-align: center;">개근일</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td style="text-align: center;">
                                    @if($attendance->rank == 1)
                                        <i class="bi bi-trophy-fill text-warning" style="font-size: 1.5rem;"></i>
                                    @elseif($attendance->rank == 2)
                                        <i class="bi bi-trophy-fill text-secondary" style="font-size: 1.5rem;"></i>
                                    @elseif($attendance->rank == 3)
                                        <i class="bi bi-trophy-fill" style="font-size: 1.5rem; color: #cd7f32;"></i>
                                    @else
                                        {{ $attendance->rank }}
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    {{ $attendance->attendance_time->format('H:i:s') }}
                                </td>
                                <td style="text-align: center;">
                                    <x-user-rank :user="$attendance->user" :site="$site" />
                                    {{ $attendance->user->nickname ?? $attendance->user->name }}
                                </td>
                                <td style="text-align: center;">
                                    {{ $attendance->greeting ?? '-' }}
                                </td>
                                <td style="text-align: center;">
                                    <span style="color: {{ $pointColor }}; font-weight: bold;">{{ number_format($attendance->points_earned) }} P</span>
                                </td>
                                <td style="text-align: center;">
                                    @php
                                        $attendanceService = app(\App\Services\AttendanceService::class);
                                        $consecutiveDays = $attendanceService->getUserConsecutiveDays($site->id, $attendance->user_id);
                                    @endphp
                                    {{ $consecutiveDays }}일째
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0">아직 출석한 사용자가 없습니다.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendances->hasPages())
                <div class="mt-4">
                    {{ $attendances->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 출석체크 완료 모달 -->
@if(session('attendance_success'))
<div class="modal fade" id="attendanceSuccessModal" tabindex="-1" aria-labelledby="attendanceSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="background-color: white;">
                <h5 class="modal-title" id="attendanceSuccessModalLabel" style="display: none;">알림</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3 mb-2">출석체크가 완료되었습니다!</h4>
                <p class="mb-0 fs-5">
                    <span style="color: {{ $pointColor }}; font-weight: bold;">{{ number_format(session('attendance_points')) }}포인트</span>를 받았습니다.
                </p>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- 포인트 안내 모달 -->
<div class="modal fade" id="pointsInfoModal" tabindex="-1" aria-labelledby="pointsInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pointsInfoModalLabel">출첵 포인트 안내</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pointsInfoContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 출석체크 완료 모달 표시
        @if(session('attendance_success'))
        const successModal = new bootstrap.Modal(document.getElementById('attendanceSuccessModal'));
        successModal.show();
        @endif

        // 포인트 안내 모달
        const modal = document.getElementById('pointsInfoModal');
        const pointColor = '{{ $pointColor }}';
        if (modal) {
            modal.addEventListener('show.bs.modal', function() {
                fetch('{{ route("attendance.points-info", ["site" => $site->slug]) }}')
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        
                        // 기본 출첵 포인트 (가장 위)
                        if (data.default_points > 0) {
                            html += '<div class="mb-4">';
                            html += `<p class="mb-0"><span class="fw-bold"><i class="bi bi-gift me-1"></i>기본 출첵 포인트 : </span><span style="color: ${pointColor}; font-weight: bold;">${parseInt(data.default_points).toLocaleString()} P</span></p>`;
                            html += '</div>';
                        }
                        
                        // 순위별 적립 포인트
                        if (data.rank_points && Object.keys(data.rank_points).length > 0) {
                            html += '<div class="mb-4">';
                            html += '<h6 class="fw-bold mb-2"><i class="bi bi-trophy me-1"></i>순위별 적립 포인트</h6>';
                            html += '<table class="table table-bordered">';
                            html += '<thead class="table-light"><tr><th style="width: 50%;">등수</th><th style="width: 50%;">포인트</th></tr></thead>';
                            html += '<tbody>';
                            Object.keys(data.rank_points).sort((a, b) => parseInt(a) - parseInt(b)).forEach(rank => {
                                html += `<tr>`;
                                html += `<td>${rank}등</td>`;
                                html += `<td><span style="color: ${pointColor}; font-weight: bold;">${parseInt(data.rank_points[rank]).toLocaleString()} P</span></td>`;
                                html += `</tr>`;
                            });
                            html += '</tbody></table></div>';
                        }
                        
                        // 개근 적립 포인트
                        if (data.consecutive_points && Object.keys(data.consecutive_points).length > 0) {
                            html += '<div class="mb-4">';
                            html += '<h6 class="fw-bold mb-2"><i class="bi bi-calendar-check me-1"></i>개근 적립 포인트</h6>';
                            html += '<table class="table table-bordered">';
                            html += '<thead class="table-light"><tr><th style="width: 50%;">일수</th><th style="width: 50%;">포인트</th></tr></thead>';
                            html += '<tbody>';
                            Object.keys(data.consecutive_points).sort((a, b) => parseInt(a) - parseInt(b)).forEach(days => {
                                html += `<tr>`;
                                html += `<td>${days}일</td>`;
                                html += `<td><span style="color: ${pointColor}; font-weight: bold;">${parseInt(data.consecutive_points[days]).toLocaleString()} P</span></td>`;
                                html += `</tr>`;
                            });
                            html += '</tbody></table></div>';
                        }
                        
                        document.getElementById('pointsInfoContent').innerHTML = html;
                    })
                    .catch(error => {
                        document.getElementById('pointsInfoContent').innerHTML = 
                            '<div class="alert alert-danger">정보를 불러오는 중 오류가 발생했습니다.</div>';
                    });
            });
        }
    });
</script>
@endpush

