<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointExchangeApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'product_id',
        'points',
        'status',
        'rejection_reason',
        'form_data',
    ];

    protected $casts = [
        'points' => 'integer',
        'form_data' => 'array',
    ];

    /**
     * Get the site that owns the application.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user that owns the application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product for the application.
     */
    public function product()
    {
        return $this->belongsTo(PointExchangeProduct::class, 'product_id');
    }

    /**
     * Update status and handle point refund if needed.
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

        // Handle point refunds/charges based on status changes
        // If changing from pending to rejected/cancelled, refund points (they were already deducted on creation)
        if ($oldStatus === 'pending' && in_array($status, ['rejected', 'cancelled'])) {
            $this->user->addPoints($this->points);
        }
        
        // If changing from completed to rejected/cancelled, refund points
        if ($oldStatus === 'completed' && in_array($status, ['rejected', 'cancelled'])) {
            $this->user->addPoints($this->points);
        }

        // If changing from rejected/cancelled to completed, deduct points
        if (in_array($oldStatus, ['rejected', 'cancelled']) && $status === 'completed') {
            $this->user->subtractPoints($this->points);
        }

        // If changing from pending to completed, no need to deduct (already deducted on creation)
        
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
            
            // 포인트 교환 설정에서 페이지 제목 가져오기
            $setting = \App\Models\PointExchangeSetting::getForSite($this->site_id);
            $pageTitle = $setting->page_title ?? '포인트 교환';
            
            // 항목 내용 가져오기
            $itemContent = $this->product->item_content ?? $this->product->item_name ?? '항목';
            
            // 알림 제목 형식: "{페이지 제목} - {항목내용}"
            $productTitle = $pageTitle . ' - ' . $itemContent;
            
            // 상태 매핑: completed -> completed (완료), rejected -> rejected (거절), pending -> pending (보류)
            $statusForNotification = $status; // 상태를 그대로 전달 (completed, rejected, pending)
            
            $notificationService->createPointExchangeNotification(
                $this->user_id,
                $this->site_id,
                $productTitle,
                $statusForNotification,
                $this->product_id
            );
        }
    }
}

