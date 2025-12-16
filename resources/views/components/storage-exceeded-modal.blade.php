<!-- 저장용량 초과 모달 -->
<div class="modal fade" id="storageExceededModal" tabindex="-1" aria-labelledby="storageExceededModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="storageExceededModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>저장 용량 초과
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>저장 용량이 가득 찼습니다. 게시글이나 댓글을 작성할 수 없습니다.</p>
                <div class="alert alert-info">
                    <strong>사용 중:</strong> <span id="storageUsedDisplay">-</span> MB<br>
                    <strong>제한:</strong> <span id="storageLimitDisplay">-</span> MB
                </div>
                <p class="mb-0">더 많은 저장 용량이 필요하시다면 플랜을 업그레이드하거나 서버 용량을 추가 구매해주세요.</p>
            </div>
            <div class="modal-footer">
                <a href="https://seoomweb.com" target="_blank" class="btn btn-primary">
                    <i class="bi bi-arrow-up-circle me-2"></i>플랜 업그레이드하기
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

<script>
// 저장용량 초과 시 모달 표시 함수
function showStorageExceededModal(storageUsed, storageLimit) {
    document.getElementById('storageUsedDisplay').textContent = storageUsed ? parseFloat(storageUsed).toLocaleString('ko-KR', {maximumFractionDigits: 2}) : '-';
    document.getElementById('storageLimitDisplay').textContent = storageLimit ? parseFloat(storageLimit).toLocaleString('ko-KR', {maximumFractionDigits: 2}) : '-';
    const modal = new bootstrap.Modal(document.getElementById('storageExceededModal'));
    modal.show();
}

// 전역 에러 핸들러에서 저장용량 초과 에러 처리
document.addEventListener('DOMContentLoaded', function() {
    // AJAX 요청 실패 시 저장용량 초과 에러 확인
    document.addEventListener('ajax:error', function(event) {
        const response = event.detail[0];
        if (response && response.status === 403 && response.responseJSON && response.responseJSON.error) {
            const errorMessage = response.responseJSON.error;
            if (errorMessage.includes('저장 용량이 가득 찼습니다')) {
                showStorageExceededModal(
                    response.responseJSON.storage_used,
                    response.responseJSON.storage_limit
                );
            }
        }
    });
});
</script>

