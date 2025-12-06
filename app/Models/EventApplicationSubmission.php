<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventApplicationSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'product_id',
        'status',
        'rejection_reason',
        'form_data',
    ];

    protected $casts = [
        'form_data' => 'array',
    ];

    /**
     * Get the site that owns the submission.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user that owns the submission.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product for the submission.
     */
    public function product()
    {
        return $this->belongsTo(EventApplicationProduct::class, 'product_id');
    }

    /**
     * Update status.
     */
    public function updateStatus($status, $rejectionReason = null)
    {
        $oldStatus = $this->status;
        
        // If status is not changing, just update rejection reason if provided
        if ($oldStatus === $status) {
            if ($rejectionReason !== null) {
                $this->rejection_reason = $rejectionReason;
                $this->save();
            }
            return;
        }
        
        $this->status = $status;
        if ($rejectionReason !== null) {
            $this->rejection_reason = $rejectionReason;
        }
        $this->save();
        
        // Update product statistics
        $this->product->updateStatistics();
        
        // 알림 생성
        if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            $notificationService = new \App\Services\NotificationService();
            $post = $this->product->post ?? null;
            
            // 신청형 이벤트 설정에서 페이지 제목 가져오기
            $setting = \App\Models\EventApplicationSetting::getForSite($this->site_id);
            $pageTitle = $setting->page_title ?? '신청형 이벤트';
            
            // 항목 내용 가져오기
            $itemContent = $this->product->item_content ?? $this->product->item_name ?? '항목';
            
            // 알림 제목 형식: "{페이지 제목} - {항목내용}"
            $postTitle = $post ? $post->title : ($pageTitle . ' - ' . $itemContent);
            
            // 상태 매핑: completed -> completed (완료), rejected -> rejected (거절), pending -> pending (보류)
            $statusForNotification = $status; // 상태를 그대로 전달 (completed, rejected, pending)
            $postId = $post ? $post->id : null;
            $boardSlug = $post ? $post->board->slug : null;
            $notificationService->createEventApplicationNotification(
                $this->user_id,
                $this->site_id,
                $postTitle,
                $statusForNotification,
                $postId,
                $boardSlug,
                $this->product_id
            );
        }
    }
}

