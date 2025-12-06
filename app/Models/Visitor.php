<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'ip_address',
        'user_agent',
        'visited_date',
    ];

    protected $casts = [
        'visited_date' => 'date',
    ];

    /**
     * Get the site that owns the visitor.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get today's visitor count for a site.
     * 같은 IP는 하루에 한 번만 카운트 (고유 방문자 수)
     */
    public static function getTodayCount(int $siteId): int
    {
        $result = DB::table('visitors')
            ->where('site_id', $siteId)
            ->whereDate('visited_date', today())
            ->selectRaw('COUNT(DISTINCT ip_address) as count')
            ->first();
        
        return (int) ($result->count ?? 0);
    }

    /**
     * Get total visitor count for a site.
     * 날짜별 고유 방문자 수의 합 (같은 IP가 다른 날 방문하면 각각 카운트)
     * 예: 같은 IP가 어제와 오늘 방문하면 전체 방문자는 2
     */
    public static function getTotalCount(int $siteId): int
    {
        // 날짜별로 고유 IP를 카운트하고, 전체 합을 구함
        // (ip_address, visited_date) 조합의 고유 개수를 카운트
        $uniqueVisits = DB::table('visitors')
            ->where('site_id', $siteId)
            ->select('ip_address', 'visited_date')
            ->distinct()
            ->get()
            ->count();
        
        $count = (int) $uniqueVisits;
        
        // 조정값 추가
        $site = \App\Models\Site::find($siteId);
        if ($site) {
            $adjustment = (int) $site->getSetting('visitor_count_adjustment', 0);
            $count += $adjustment;
        }
        
        return max(0, $count); // 음수 방지
    }

    /**
     * Track a visitor.
     * 같은 IP가 같은 날 이미 방문한 경우에는 새로 기록하지 않음 (고유 방문자만 추적)
     */
    public static function track(int $siteId, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $ipAddress = $ipAddress ?? request()->ip();
        $userAgent = $userAgent ?? request()->userAgent();
        
        // 같은 IP가 같은 날 이미 방문한 경우에는 새로 기록하지 않음
        $exists = self::where('site_id', $siteId)
            ->where('ip_address', $ipAddress)
            ->whereDate('visited_date', today())
            ->exists();
        
        if (!$exists) {
            self::create([
                'site_id' => $siteId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'visited_date' => today(),
            ]);
        }
    }
}
