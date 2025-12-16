@extends('layouts.admin')

@section('title', '출첵 관리')
@section('page-title', '출첵 관리')
@section('page-subtitle', '출석체크 포인트 및 설정을 관리할 수 있습니다')

@section('content')
<form action="{{ route('admin.attendance.update', ['site' => $site->slug]) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>순위별 적립 포인트</h5>
        </div>
        <div class="card-body">
            <div id="rankPointsContainer">
                @if(isset($settings['rank_points']) && count($settings['rank_points']) > 0)
                    @foreach($settings['rank_points'] as $rank => $points)
                        <div class="row mb-2 rank-point-item">
                            <div class="col-md-2">
                                <label class="form-label">등수</label>
                                <input type="number" class="form-control rank-input" value="{{ $rank }}" min="1" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">포인트</label>
                                <input type="number" class="form-control points-input" value="{{ $points }}" min="0" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger w-100 remove-rank-btn">
                                    <i class="bi bi-trash"></i> 삭제
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row mb-2 rank-point-item">
                        <div class="col-md-2">
                            <label class="form-label">등수</label>
                            <input type="number" class="form-control rank-input" value="1" min="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">포인트</label>
                            <input type="number" class="form-control points-input" value="" min="0" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger w-100 remove-rank-btn">
                                <i class="bi bi-trash"></i> 삭제
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" class="btn btn-outline-primary mt-2" id="addRankBtn">
                <i class="bi bi-plus-circle me-1"></i>순위 추가
            </button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>개근 적립 포인트</h5>
        </div>
        <div class="card-body">
            <div id="consecutivePointsContainer">
                @if(isset($settings['consecutive_points']) && count($settings['consecutive_points']) > 0)
                    @foreach($settings['consecutive_points'] as $days => $points)
                        <div class="row mb-2 consecutive-point-item">
                            <div class="col-md-2">
                                <label class="form-label">일수</label>
                                <select class="form-select days-select">
                                    @for($i = 1; $i <= 30; $i++)
                                        <option value="{{ $i }}" {{ $days == $i ? 'selected' : '' }}>{{ $i }}일</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">포인트</label>
                                <input type="number" class="form-control points-input" value="{{ $points }}" min="0" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger w-100 remove-consecutive-btn">
                                    <i class="bi bi-trash"></i> 삭제
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row mb-2 consecutive-point-item">
                        <div class="col-md-2">
                            <label class="form-label">일수</label>
                            <select class="form-select days-select">
                                @for($i = 1; $i <= 30; $i++)
                                    <option value="{{ $i }}">{{ $i }}일</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">포인트</label>
                            <input type="number" class="form-control points-input" value="" min="0" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger w-100 remove-consecutive-btn">
                                <i class="bi bi-trash"></i> 삭제
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" class="btn btn-outline-success mt-2" id="addConsecutiveBtn">
                <i class="bi bi-plus-circle me-1"></i>개근 추가
            </button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-gift me-2"></i>기본 출첵 포인트</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">기본 포인트</label>
                    <input type="number" name="default_points" class="form-control" value="{{ $settings['default_points'] ?? 0 }}" min="0">
                    <small class="form-text text-muted">순위나 개근 포인트가 없을 때 지급되는 기본 포인트입니다.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>출석 인사 작성칸</h5>
        </div>
        <div class="card-body">
            <div id="greetingsContainer">
                @if(isset($settings['greetings']) && count($settings['greetings']) > 0)
                    @foreach($settings['greetings'] as $index => $greeting)
                        <div class="row mb-2 greeting-item">
                            <div class="col-md-10">
                                <label class="form-label">출석 인사 {{ $index + 1 }}</label>
                                <input type="text" class="form-control greeting-input" value="{{ $greeting }}" maxlength="255">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger w-100 remove-greeting-btn">
                                    <i class="bi bi-trash"></i> 삭제
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row mb-2 greeting-item">
                        <div class="col-md-10">
                            <label class="form-label">출석 인사 1</label>
                            <input type="text" class="form-control greeting-input" value="" maxlength="255">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger w-100 remove-greeting-btn">
                                <i class="bi bi-trash"></i> 삭제
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" class="btn btn-outline-warning mt-2" id="addGreetingBtn">
                <i class="bi bi-plus-circle me-1"></i>출석인사 추가
            </button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>기타 설정</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">1페이지 리스트 표시 수</label>
                    <input type="number" name="per_page" class="form-control" value="{{ $settings['per_page'] ?? 15 }}" min="1" max="100" required>
                    <small class="form-text text-muted">출석체크 목록에서 한 페이지에 표시할 항목 수입니다.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ $site->getAdminDashboardUrl() }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>취소
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>저장
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
.btn-outline-danger:hover {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #ffffff !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 순위 추가
    document.getElementById('addRankBtn').addEventListener('click', function() {
        const container = document.getElementById('rankPointsContainer');
        const itemCount = container.querySelectorAll('.rank-point-item').length;
        const newItem = document.createElement('div');
        newItem.className = 'row mb-2 rank-point-item';
        newItem.innerHTML = `
            <div class="col-md-2">
                <label class="form-label">등수</label>
                <input type="number" class="form-control rank-input" value="${itemCount + 1}" min="1" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">포인트</label>
                <input type="number" class="form-control points-input" value="" min="0" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger w-100 remove-rank-btn">
                    <i class="bi bi-trash"></i> 삭제
                </button>
            </div>
        `;
        container.appendChild(newItem);
        attachRemoveRankListener(newItem);
    });

    // 개근 추가
    document.getElementById('addConsecutiveBtn').addEventListener('click', function() {
        const container = document.getElementById('consecutivePointsContainer');
        const newItem = document.createElement('div');
        newItem.className = 'row mb-2 consecutive-point-item';
        let options = '';
        for (let i = 1; i <= 30; i++) {
            options += `<option value="${i}">${i}일</option>`;
        }
        newItem.innerHTML = `
            <div class="col-md-2">
                <label class="form-label">일수</label>
                <select class="form-select days-select">${options}</select>
            </div>
            <div class="col-md-8">
                <label class="form-label">포인트</label>
                <input type="number" class="form-control points-input" value="" min="0" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger w-100 remove-consecutive-btn">
                    <i class="bi bi-trash"></i> 삭제
                </button>
            </div>
        `;
        container.appendChild(newItem);
        attachRemoveConsecutiveListener(newItem);
    });

    // 출석인사 추가
    document.getElementById('addGreetingBtn').addEventListener('click', function() {
        const container = document.getElementById('greetingsContainer');
        const itemCount = container.querySelectorAll('.greeting-item').length;
        const newItem = document.createElement('div');
        newItem.className = 'row mb-2 greeting-item';
        newItem.innerHTML = `
            <div class="col-md-10">
                <label class="form-label">출석 인사 ${itemCount + 1}</label>
                <input type="text" class="form-control greeting-input" value="" maxlength="255">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger w-100 remove-greeting-btn">
                    <i class="bi bi-trash"></i> 삭제
                </button>
            </div>
        `;
        container.appendChild(newItem);
        attachRemoveGreetingListener(newItem);
    });

    // 삭제 버튼 리스너
    function attachRemoveRankListener(item) {
        item.querySelector('.remove-rank-btn').addEventListener('click', function() {
            item.remove();
        });
    }

    function attachRemoveConsecutiveListener(item) {
        item.querySelector('.remove-consecutive-btn').addEventListener('click', function() {
            item.remove();
        });
    }

    function attachRemoveGreetingListener(item) {
        item.querySelector('.remove-greeting-btn').addEventListener('click', function() {
            item.remove();
        });
    }

    // 기존 항목에 삭제 리스너 추가
    document.querySelectorAll('.rank-point-item').forEach(item => {
        attachRemoveRankListener(item);
    });

    document.querySelectorAll('.consecutive-point-item').forEach(item => {
        attachRemoveConsecutiveListener(item);
    });

    document.querySelectorAll('.greeting-item').forEach(item => {
        attachRemoveGreetingListener(item);
    });

    // 폼 제출 시 데이터 변환
    document.querySelector('form').addEventListener('submit', function(e) {
        // 순위별 포인트
        const rankPoints = {};
        document.querySelectorAll('.rank-point-item').forEach(item => {
            const rank = item.querySelector('.rank-input').value;
            const points = item.querySelector('.points-input').value;
            if (rank && points && points > 0) {
                rankPoints[rank] = points;
            }
        });
        
        // hidden input 추가
        Object.keys(rankPoints).forEach(rank => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `rank_points[${rank}]`;
            input.value = rankPoints[rank];
            this.appendChild(input);
        });

        // 개근 포인트
        const consecutivePoints = {};
        document.querySelectorAll('.consecutive-point-item').forEach(item => {
            const days = item.querySelector('.days-select').value;
            const points = item.querySelector('.points-input').value;
            if (days && points && points > 0) {
                consecutivePoints[days] = points;
            }
        });
        
        Object.keys(consecutivePoints).forEach(days => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `consecutive_points[${days}]`;
            input.value = consecutivePoints[days];
            this.appendChild(input);
        });

        // 출석 인사
        const greetings = [];
        document.querySelectorAll('.greeting-item').forEach(item => {
            const greeting = item.querySelector('.greeting-input').value.trim();
            if (greeting) {
                greetings.push(greeting);
            }
        });
        
        greetings.forEach((greeting, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `greetings[${index}]`;
            input.value = greeting;
            this.appendChild(input);
        });
    });
});
</script>
@endpush


