<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'guest_session_id',
        'nickname',
        'type',
        'report_id',
        'reason',
        'expires_at',
        'is_active',
        'issued_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the site that owns the penalty.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user who received the penalty.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the report that led to this penalty.
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the admin who issued the penalty.
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Check if penalty is expired.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false; // Permanent penalty
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if penalty is active and not expired.
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Deactivate penalty.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}



