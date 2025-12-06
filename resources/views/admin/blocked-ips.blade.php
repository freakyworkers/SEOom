@extends('layouts.admin')

@section('title', '아이피 차단')

@section('content')
<div class="page-header">
    <h1>아이피 차단</h1>
    <p class="text-muted">차단된 IP 주소를 관리할 수 있습니다</p>
</div>

<!-- 차단 아이피 추가 -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-shield-x me-2"></i>차단 아이피 추가</h5>
    </div>
    <div class="card-body">
        <form id="addBlockedIpForm">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="text-align: center; background-color: #f8f9fa; color: #6c757d;">IP</th>
                            <th style="text-align: center; background-color: #f8f9fa; color: #6c757d;">추가</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="text" 
                                       class="form-control" 
                                       id="ip_address" 
                                       name="ip" 
                                       placeholder="IP 주소를 입력하세요" 
                                       required>
                            </td>
                            <td style="text-align: right;">
                                <button type="submit" class="btn btn-primary">추가</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<!-- 아이피 차단 리스트 -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list me-2"></i>아이피 차단</h5>
    </div>
    <div class="card-body">
        @if(count($blockedIps) > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="text-align: center; background-color: #f8f9fa; color: #6c757d;">번호</th>
                            <th style="text-align: center; background-color: #f8f9fa; color: #6c757d;">아이피</th>
                            <th style="text-align: center; background-color: #f8f9fa; color: #6c757d;">차단 해제</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blockedIps as $index => $ip)
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td style="text-align: center;">{{ $ip }}</td>
                                <td style="text-align: center;">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary unblock-ip-btn" 
                                            data-ip="{{ $ip }}">
                                        해제
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center mb-0">차단된 IP 주소가 없습니다.</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // IP 추가 폼 제출
    $('#addBlockedIpForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            ip: $('#ip_address').val(),
            _token: '{{ csrf_token() }}'
        };
        
        $.ajax({
            url: '{{ route("admin.blocked-ips.store", ["site" => $site->slug]) }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message || '오류가 발생했습니다.');
                }
            },
            error: function(xhr) {
                var errorMessage = '오류가 발생했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    });
    
    // IP 차단 해제 버튼 클릭
    $(document).on('click', '.unblock-ip-btn', function() {
        var ip = $(this).data('ip');
        var btn = $(this);
        
        if (!confirm('정말 이 IP 주소의 차단을 해제하시겠습니까?')) {
            return;
        }
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.blocked-ips.destroy", ["site" => $site->slug, "ip" => ":ip"]) }}'.replace(':ip', encodeURIComponent(ip)),
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message || '오류가 발생했습니다.');
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                var errorMessage = '오류가 발생했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
                btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush




