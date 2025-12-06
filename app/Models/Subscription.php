<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'plan_id',
        'toss_payment_key',
        'toss_billing_key',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'retry_count',
        'last_payment_failed_at',
        'reminder_sent_7days',
        'reminder_sent_3days',
        'reminder_sent_1day',
        'failure_notification_sent_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'date',
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'canceled_at' => 'date',
        'last_payment_failed_at' => 'date',
        'retry_count' => 'integer',
        'reminder_sent_7days' => 'date',
        'reminder_sent_3days' => 'date',
        'reminder_sent_1day' => 'date',
        'failure_notification_sent_at' => 'date',
    ];

    /**
     * Get the site that owns the subscription.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan for this subscription.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get all payments for this subscription.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if subscription is in trial period.
     */
    public function isTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is past due.
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Check if subscription is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    /**
     * Check if subscription is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Get days remaining in trial.
     */
    public function getTrialDaysRemainingAttribute(): int
    {
        if (!$this->trial_ends_at) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }
}

