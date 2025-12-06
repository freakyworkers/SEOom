<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PhoneVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'phone',
        'code',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function markAsVerified(): void
    {
        $this->verified_at = Carbon::now();
        $this->save();
    }

    public static function generateCode(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function createVerification(int $siteId, string $phone): self
    {
        // 기존 미인증 코드 삭제
        self::where('site_id', $siteId)
            ->where('phone', $phone)
            ->whereNull('verified_at')
            ->delete();

        return self::create([
            'site_id' => $siteId,
            'phone' => $phone,
            'code' => self::generateCode(),
            'expires_at' => Carbon::now()->addMinutes(5), // 5분 유효
        ]);
    }
}




