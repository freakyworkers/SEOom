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
                    <!-- PC 버전 테이블 -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover mb-0">
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

                    <!-- 모바일 버전 카드 레이아웃 -->
                    <div class="d-md-none">
                        @foreach($crawlers as $crawler)
                            <div class="card border mb-2">
                                <div class="card-body p-3">
                                    <!-- 주소 -->
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1" style="font-size: 0.75rem;">주소</div>
                                        <a href="{{ $crawler->url }}" target="_blank" class="text-decoration-none">
                                            <div class="fw-medium text-primary" style="font-size: 0.9rem; word-break: break-all;">{{ Str::limit($crawler->url, 60) }}</div>
                                        </a>
                                    </div>

                                    <!-- 정보 그리드 -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="small text-muted mb-1" style="font-size: 0.75rem;">게시판</div>
                                            <div class="fw-medium" style="font-size: 0.9rem;">{{ $crawler->board->name ?? '-' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted mb-1" style="font-size: 0.75rem;">작성자</div>
                                            <div>
                                                @if($crawler->use_random_user)
                                                    <span class="badge bg-info" style="font-size: 0.7rem;">랜덤 유저</span>
                                                @else
                                                    <span class="fw-medium" style="font-size: 0.9rem;">{{ $crawler->author_nickname ?? '-' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 총 수량과 최근 크롤링 -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="small text-muted mb-1" style="font-size: 0.75rem;">총 수량</div>
                                            <div class="fw-bold" style="font-size: 0.9rem;">{{ number_format($crawler->total_count) }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted mb-1" style="font-size: 0.75rem;">최근 크롤링</div>
                                            <div class="small text-muted" style="font-size: 0.85rem;">
                                                @if($crawler->last_crawled_at)
                                                    {{ $crawler->last_crawled_at->format('Y-m-d H:i') }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 활성화 스위치 -->
                                    <div class="mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="small text-muted" style="font-size: 0.75rem;">활성화</div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="active_mobile_{{ $crawler->id }}" 
                                                       {{ $crawler->is_active ? 'checked' : '' }}
                                                       onchange="toggleActive({{ $crawler->id }})">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 작업 버튼 -->
                                    <div class="d-grid gap-2">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                onclick="editCrawler({{ $crawler->id }})"
                                                style="font-size: 0.8rem; padding: 0.35rem 0.5rem;">
                                            <i class="bi bi-pencil"></i> 수정
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteCrawler({{ $crawler->id }})"
                                                style="font-size: 0.8rem; padding: 0.35rem 0.5rem;">
                                            <i class="bi bi-trash"></i> 삭제
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 크롤러 커스텀 항목 -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>크롤러 커스텀 항목</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="openCrawlerModal()">
                    <i class="bi bi-plus-circle me-1"></i>크롤러 추가
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">크롤러를 추가하려면 위의 "크롤러 추가" 버튼을 클릭하세요.</p>
            </div>
        </div>
    </div>
</div>

<!-- 크롤러 생성/수정 모달 -->
<div class="modal fade" id="crawlerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crawlerModalTitle">크롤러 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="testCrawler()">
                    <i class="bi bi-play-circle me-2"></i>테스트
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="saveCrawler()">
                    <i class="bi bi-save me-2"></i>저장
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* 모바일 최적화 스타일 */
    @media (max-width: 767.98px) {
        /* 카드 스타일 최적화 */
        .d-md-none .card {
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* 버튼 최적화 */
        .d-md-none .btn-sm {
            font-size: 0.8rem;
            padding: 0.35rem 0.5rem;
        }
        
        /* 배지 최적화 */
        .d-md-none .badge {
            font-size: 0.7rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// 모달이 닫힐 때 폼 초기화
document.addEventListener('DOMContentLoaded', function() {
    const crawlerModal = document.getElementById('crawlerModal');
    if (crawlerModal) {
        crawlerModal.addEventListener('hidden.bs.modal', function() {
            resetCrawlerForm();
        });
    }
});

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
    const checkboxMobile = document.getElementById('active_mobile_' + crawlerId);
    const activeCheckbox = checkbox || checkboxMobile;
    const isActive = activeCheckbox.checked;

    // PC와 모바일 체크박스 동기화
    if (checkbox) checkbox.checked = isActive;
    if (checkboxMobile) checkboxMobile.checked = isActive;

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
            const newState = !isActive;
            if (checkbox) checkbox.checked = newState;
            if (checkboxMobile) checkboxMobile.checked = newState;
            alert('오류: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const newState = !isActive;
        if (checkbox) checkbox.checked = newState;
        if (checkboxMobile) checkboxMobile.checked = newState;
        alert('활성화 상태 변경 중 오류가 발생했습니다.');
    });
}

function openCrawlerModal() {
    // 폼 초기화
    resetCrawlerForm();
    document.getElementById('crawlerModalTitle').textContent = '크롤러 추가';
    const modal = new bootstrap.Modal(document.getElementById('crawlerModal'));
    modal.show();
}

function editCrawler(crawlerId) {
    // 크롤러 데이터 가져오기
    fetch('{{ route("admin.crawlers.show", ["site" => $site->slug, "crawler" => ":id"]) }}'.replace(':id', crawlerId), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.crawler) {
            const crawler = data.crawler;
            
            // 폼에 데이터 채우기
            document.getElementById('crawler_id').value = crawler.id;
            document.getElementById('crawler_name').value = crawler.name || '';
            document.getElementById('crawler_url').value = crawler.url || '';
            document.getElementById('list_title_selector').value = crawler.list_title_selector || '';
            document.getElementById('post_title_selector').value = crawler.post_title_selector || '';
            document.getElementById('post_content_selector').value = crawler.post_content_selector || '';
            document.getElementById('board_id').value = crawler.board_id || '';
            document.getElementById('author_nickname').value = crawler.author_nickname || '';
            document.getElementById('use_random_user').checked = crawler.use_random_user || false;
            document.getElementById('bypass_cloudflare').checked = crawler.bypass_cloudflare || false;
            
            // 작성자 닉네임 필드 활성화/비활성화
            toggleAuthorNickname();
            
            // 게시판이 선택되어 있으면 주제 로드
            if (crawler.board_id) {
                loadTopics().then(() => {
                    if (crawler.topic_id) {
                        document.getElementById('topic_id').value = crawler.topic_id;
                    }
                });
            }
            
            // 모달 제목 변경
            document.getElementById('crawlerModalTitle').textContent = '크롤러 수정';
            
            // 모달 열기
            const modal = new bootstrap.Modal(document.getElementById('crawlerModal'));
            modal.show();
        } else {
            alert('크롤러 데이터를 불러오는 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('크롤러 데이터를 불러오는 중 오류가 발생했습니다.');
    });
}

function resetCrawlerForm() {
    document.getElementById('crawlerForm').reset();
    document.getElementById('crawler_id').value = '';
    document.getElementById('topic_container').style.display = 'none';
    document.getElementById('topic_id').innerHTML = '<option value="">주제 선택 (선택사항)</option>';
    document.getElementById('test_result').style.display = 'none';
    document.getElementById('test_result_content').innerHTML = '';
    toggleAuthorNickname();
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
        return Promise.resolve();
    }

    return fetch(`{{ route("admin.boards.topics", ["site" => $site->slug, "board" => ":id"]) }}`.replace(':id', boardId), {
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
            // 모달 닫기
            const modal = bootstrap.Modal.getInstance(document.getElementById('crawlerModal'));
            if (modal) {
                modal.hide();
            }
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

