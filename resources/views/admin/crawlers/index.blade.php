@extends('layouts.admin')

@section('title', '크롤러')
@section('page-title', '크롤러')
@section('page-subtitle', '웹 크롤링을 통해 게시글을 1시간에 1번 자동으로 수집합니다')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- 지금 바로 크롤링 하기 버튼 -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center py-4">
                <button type="button" class="btn btn-primary btn-lg" onclick="runAllCrawlers()">
                    <i class="bi bi-play-circle me-2"></i>지금 바로 크롤링 하기
                </button>
            </div>
        </div>

        <!-- 크롤링 데이터 리스트 -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>크롤링 목록</h5>
            </div>
            <div class="card-body">
                @if($crawlers->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3">등록된 크롤러가 없습니다.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>주소</th>
                                    <th>게시판</th>
                                    <th>작성자</th>
                                    <th>총 수량</th>
                                    <th>최근 크롤링</th>
                                    <th>활성화</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($crawlers as $crawler)
                                    <tr>
                                        <td>
                                            <a href="{{ $crawler->url }}" target="_blank" class="text-decoration-none">
                                                {{ Str::limit($crawler->url, 50) }}
                                            </a>
                                        </td>
                                        <td>{{ $crawler->board->name ?? '-' }}</td>
                                        <td>
                                            @if($crawler->use_random_user)
                                                <span class="badge bg-info">랜덤 유저</span>
                                            @else
                                                {{ $crawler->author_nickname ?? '-' }}
                                            @endif
                                        </td>
                                        <td>{{ number_format($crawler->total_count) }}</td>
                                        <td>
                                            @if($crawler->last_crawled_at)
                                                {{ $crawler->last_crawled_at->format('Y-m-d H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="active_{{ $crawler->id }}" 
                                                       {{ $crawler->is_active ? 'checked' : '' }}
                                                       onchange="toggleActive({{ $crawler->id }})">
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCrawler({{ $crawler->id }})">
                                                <i class="bi bi-pencil me-1"></i>수정
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCrawler({{ $crawler->id }})">
                                                <i class="bi bi-trash me-1"></i>삭제
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- 크롤러 커스텀 항목 -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>크롤러 커스텀 항목</h5>
            </div>
            <div class="card-body">
                <form id="crawlerForm">
                    <input type="hidden" id="crawler_id" name="id">
                    
                    <div class="mb-3">
                        <label for="crawler_name" class="form-label">크롤러 이름</label>
                        <input type="text" class="form-control" id="crawler_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="crawler_url" class="form-label">크롤링 목록 URL</label>
                        <input type="url" class="form-control" id="crawler_url" name="url" required placeholder="https://example.com/list">
                    </div>

                    <div class="mb-3">
                        <label for="list_title_selector" class="form-label">리스트 제목 a태그 선택자</label>
                        <input type="text" class="form-control" id="list_title_selector" name="list_title_selector" required placeholder="예: .post-list a">
                        <small class="text-muted">게시글 목록에서 각 게시글 링크를 선택하는 CSS 선택자</small>
                    </div>

                    <div class="mb-3">
                        <label for="post_title_selector" class="form-label">게시글 제목 선택자</label>
                        <input type="text" class="form-control" id="post_title_selector" name="post_title_selector" required placeholder="예: .post-title">
                        <small class="text-muted">게시글 페이지에서 제목을 선택하는 CSS 선택자</small>
                    </div>

                    <div class="mb-3">
                        <label for="post_content_selector" class="form-label">게시글 본문 선택자</label>
                        <input type="text" class="form-control" id="post_content_selector" name="post_content_selector" required placeholder="예: .post-content">
                        <small class="text-muted">게시글 페이지에서 본문을 선택하는 CSS 선택자</small>
                    </div>

                    <div class="mb-3">
                        <label for="board_id" class="form-label">크롤링 대상 게시판</label>
                        <select class="form-select" id="board_id" name="board_id" required onchange="loadTopics()">
                            <option value="">게시판 선택</option>
                            @foreach($boards as $board)
                                <option value="{{ $board->id }}">{{ $board->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="topic_container" style="display: none;">
                        <label for="topic_id" class="form-label">주제</label>
                        <select class="form-select" id="topic_id" name="topic_id">
                            <option value="">주제 선택 (선택사항)</option>
                        </select>
                        <small class="text-muted">해당 게시판에 주제가 있는 경우 선택할 수 있습니다</small>
                    </div>

                    <div class="mb-3">
                        <label for="author_nickname" class="form-label">작성자 닉네임</label>
                        <input type="text" class="form-control" id="author_nickname" name="author_nickname" placeholder="작성자 닉네임을 입력하세요">
                        <small class="text-muted">크롤링된 게시글이 이 닉네임으로 작성됩니다</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="use_random_user" name="use_random_user" onchange="toggleAuthorNickname()">
                            <label class="form-check-label" for="use_random_user">
                                랜덤유저
                            </label>
                        </div>
                        <small class="text-muted">체크 시 가입된 사용자 중 랜덤으로 게시글이 작성됩니다</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="bypass_cloudflare" name="bypass_cloudflare">
                            <label class="form-check-label" for="bypass_cloudflare">
                                Cloudflare/안티봇 우회
                            </label>
                        </div>
                        <small class="text-muted">체크 시 Cloudflare 보안 및 안티봇 기능을 우회하여 크롤링합니다</small>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-secondary" onclick="testCrawler()">
                            <i class="bi bi-play-circle me-2"></i>테스트
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveCrawler()">
                            <i class="bi bi-save me-2"></i>저장
                        </button>
                    </div>

                    <!-- 테스트 결과 -->
                    <div id="test_result" style="display: none;" class="mt-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">테스트 결과</h6>
                            </div>
                            <div class="card-body">
                                <div id="test_result_content"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function runAllCrawlers() {
    if (!confirm('모든 활성화된 크롤러를 실행하시겠습니까?')) {
        return;
    }

    fetch('{{ route("admin.crawlers.run-all", ["site" => $site->slug]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = data.message + '\n\n';
            data.results.forEach(result => {
                if (result.success) {
                    message += `✓ ${result.name}: ${result.count}개 게시글 크롤링\n`;
                } else {
                    message += `✗ ${result.name}: ${result.message}\n`;
                }
            });
            alert(message);
            location.reload();
        } else {
            alert('오류: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('크롤링 실행 중 오류가 발생했습니다.');
    });
}

function toggleActive(crawlerId) {
    const checkbox = document.getElementById('active_' + crawlerId);
    const isActive = checkbox.checked;

    fetch('{{ route("admin.crawlers.toggle-active", ["site" => $site->slug, "crawler" => ":id"]) }}'.replace(':id', crawlerId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            checkbox.checked = !isActive;
            alert('오류: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        checkbox.checked = !isActive;
        alert('활성화 상태 변경 중 오류가 발생했습니다.');
    });
}

function editCrawler(crawlerId) {
    // TODO: 크롤러 수정 폼 로드
    alert('크롤러 수정 기능은 추후 구현 예정입니다.');
}

function deleteCrawler(crawlerId) {
    if (!confirm('정말 이 크롤러를 삭제하시겠습니까?')) {
        return;
    }
    
    fetch('{{ route("admin.crawlers.delete", ["site" => $site->slug, "crawler" => ":id"]) }}'.replace(':id', crawlerId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('삭제 중 오류가 발생했습니다.');
    });
}

function loadTopics() {
    const boardId = document.getElementById('board_id').value;
    const topicContainer = document.getElementById('topic_container');
    const topicSelect = document.getElementById('topic_id');

    if (!boardId) {
        topicContainer.style.display = 'none';
        return;
    }

    fetch(`{{ route("admin.boards.topics", ["site" => $site->slug, "board" => ":id"]) }}`.replace(':id', boardId), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        topicSelect.innerHTML = '<option value="">주제 선택 (선택사항)</option>';
        if (data.success && data.topics && data.topics.length > 0) {
            topicContainer.style.display = 'block';
            data.topics.forEach(topic => {
                const option = document.createElement('option');
                option.value = topic.id;
                option.textContent = topic.name;
                topicSelect.appendChild(option);
            });
        } else {
            topicContainer.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        topicContainer.style.display = 'none';
    });
}

function toggleAuthorNickname() {
    const useRandomUser = document.getElementById('use_random_user').checked;
    const authorNickname = document.getElementById('author_nickname');
    
    if (useRandomUser) {
        authorNickname.disabled = true;
        authorNickname.value = '';
    } else {
        authorNickname.disabled = false;
    }
}

function testCrawler() {
    const url = document.getElementById('crawler_url').value;
    const listTitleSelector = document.getElementById('list_title_selector').value;
    const postTitleSelector = document.getElementById('post_title_selector').value;
    const postContentSelector = document.getElementById('post_content_selector').value;

    if (!url || !listTitleSelector || !postTitleSelector || !postContentSelector) {
        alert('모든 필드를 입력해주세요.');
        return;
    }

    const formData = new FormData();
    formData.append('url', url);
    formData.append('list_title_selector', listTitleSelector);
    formData.append('post_title_selector', postTitleSelector);
    formData.append('post_content_selector', postContentSelector);
    formData.append('bypass_cloudflare', document.getElementById('bypass_cloudflare').checked ? '1' : '0');

    fetch('{{ route("admin.crawlers.test", ["site" => $site->slug]) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const testResult = document.getElementById('test_result');
        const testResultContent = document.getElementById('test_result_content');
        
        if (data.success) {
            testResult.style.display = 'block';
            testResultContent.innerHTML = `
                <h6>제목:</h6>
                <p>${data.data.title || '-'}</p>
                <h6>URL:</h6>
                <p><a href="${data.data.url}" target="_blank">${data.data.url}</a></p>
                <h6>본문:</h6>
                <div style="max-height: 500px; overflow-y: auto; border: 1px solid #dee2e6; padding: 15px; border-radius: 0.375rem; background-color: #fff; min-height: 100px;">
                    ${data.data.content && data.data.content.trim().length > 0 ? data.data.content : '<p class="text-muted">본문이 추출되지 않았습니다.</p>'}
                </div>
                ${data.data.content ? `<div class="mt-2"><small class="text-muted">본문 길이: ${data.data.content.length}자</small></div>` : ''}
            `;
        } else {
            testResult.style.display = 'block';
            testResultContent.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('테스트 중 오류가 발생했습니다.');
    });
}

function saveCrawler() {
    const form = document.getElementById('crawlerForm');
    const formData = new FormData(form);
    const crawlerId = document.getElementById('crawler_id').value;
    
    // 체크박스가 체크되지 않았을 때는 필드를 제거하지 않고 그대로 둠
    // 서버 측에서 nullable|boolean로 처리하므로 필드가 없어도 됨
    
    const url = crawlerId 
        ? '{{ route("admin.crawlers.update", ["site" => $site->slug, "crawler" => ":id"]) }}'.replace(':id', crawlerId)
        : '{{ route("admin.crawlers.store", ["site" => $site->slug]) }}';
    
    const method = crawlerId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다.');
    });
}
</script>
@endpush
@endsection

