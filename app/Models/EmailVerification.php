<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'email',
        'token',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the site that owns the email verification.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Check if the verification token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the verification is already verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Mark the verification as verified.
     */
    public function markAsVerified(): void
    {
        $this->verified_at = Carbon::now();
        $this->save();
    }

    /**
     * Generate a 6-digit verification code.
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new email verification record.
     */
    public static function createVerification(int $siteId, string $email): self
    {
        // Delete any existing unverified verifications for this email
        self::where('site_id', $siteId)
            ->where('email', $email)
            ->whereNull('verified_at')
            ->delete();

        return self::create([
            'site_id' => $siteId,
            'email' => $email,
            'token' => self::generateCode(), // 6자리 숫자 코드
            'expires_at' => Carbon::now()->addMinutes(10), // 10분 유효
        ]);
    }
}

