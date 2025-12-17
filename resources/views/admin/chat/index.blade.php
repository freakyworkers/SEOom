@extends('layouts.admin')

@section('title', '채팅 관리')
@section('page-title', '채팅 관리')
@section('page-subtitle', '채팅 설정 및 메시지 관리')

@section('content')
<!-- 채팅 설정 -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>채팅 설정</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ $site->isMasterSite() ? route('master.admin.chat.update-settings') : route('admin.chat.update-settings', ['site' => $site->slug]) }}">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="notice" class="form-label">공지사항</label>
                <textarea class="form-control" 
                          id="notice" 
                          name="notice" 
                          rows="3" 
                          placeholder="채팅창 상단에 표시될 공지사항을 입력하세요. (공란일 경우 표시되지 않습니다)">{{ $chatSetting->notice ?? '' }}</textarea>
                <small class="text-muted">공지사항이 작성되어 있을 경우 채팅창 상단에 표시됩니다.</small>
            </div>
            
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="auto_delete_24h" 
                           name="auto_delete_24h" 
                           value="1"
                           {{ $chatSetting->auto_delete_24h ? 'checked' : '' }}>
                    <label class="form-check-label" for="auto_delete_24h">
                        채팅내용 24시간뒤 삭제
                    </label>
                </div>
                <small class="text-muted">활성화 시 채팅 내용이 24시간 지나면 자동으로 삭제됩니다.</small>
            </div>
            
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="allow_guest" 
                           name="allow_guest" 
                           value="1"
                           {{ $chatSetting->allow_guest ? 'checked' : '' }}>
                    <label class="form-check-label" for="allow_guest">
                        비로그인 사용자 채팅 허용
                    </label>
                </div>
                <small class="text-muted">활성화 시 비로그인 사용자도 채팅을 사용할 수 있습니다.</small>
            </div>
            
            <div class="mb-3">
                <label for="banned_words" class="form-label">금지 단어</label>
                <textarea class="form-control" 
                          id="banned_words" 
                          name="banned_words" 
                          rows="3" 
                          placeholder="금지 단어를 줄바꿈으로 구분해서 입력하세요. (예: 단어1&#10;단어2&#10;단어3)">{{ $chatSetting->banned_words ?? '' }}</textarea>
                <small class="text-muted">금지 단어가 포함된 채팅은 전송되지 않습니다.</small>
            </div>
            
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>저장
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 채팅 기록 -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>채팅 기록</h5>
    </div>
    @if($messages->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>닉네임</th>
                        <th>내용</th>
                        <th style="width: 120px;">첨부파일</th>
                        <th style="width: 150px;">작성일</th>
                        <th style="width: 100px;">작업</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $message)
                        <tr>
                            <td>{{ $message->id }}</td>
                            <td>
                                <strong>{{ $message->nickname }}</strong>
                                @if($message->user_id)
                                    <span class="badge bg-primary ms-1">회원</span>
                                @else
                                    <span class="badge bg-secondary ms-1">게스트</span>
                                @endif
                            </td>
                            <td>
                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $message->message }}
                                </div>
                            </td>
                            <td>
                                @if($message->attachment_path)
                                    <a href="{{ Storage::url($message->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-image"></i> 이미지
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $message->created_at->format('Y-m-d H:i') }}</small>
                            </td>
                            <td>
                                <form action="{{ $site->isMasterSite() ? route('master.admin.chat.delete-message', $message->id) : route('admin.chat.delete-message', ['site' => $site->slug, 'message' => $message->id]) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="삭제">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-center mt-4">
                    @php
                        $themeDarkMode = $site->getSetting('theme_dark_mode', 'light');
                        $pointColor = $themeDarkMode === 'dark' 
                            ? $site->getSetting('color_dark_point_main', '#ffffff')
                            : $site->getSetting('color_light_point_main', '#0d6efd');
                    @endphp
                    {{ $messages->appends(request()->query())->links('pagination::bootstrap-4', ['pointColor' => $pointColor]) }}
                </div>
            </div>
        @endif
    @else
        <div class="card-body text-center py-5">
            <i class="bi bi-chat display-1 text-muted"></i>
            <h4 class="mt-3 mb-2">채팅 기록이 없습니다</h4>
            <p class="text-muted">아직 채팅 메시지가 없습니다.</p>
        </div>
    @endif
</div>
@endsection



