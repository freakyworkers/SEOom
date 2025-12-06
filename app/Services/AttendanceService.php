<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Check if user has already attended today.
     */
    public function hasAttendedToday($siteId, $userId)
    {
        return Attendance::where('site_id', $siteId)
            ->where('user_id', $userId)
            ->whereDate('attendance_date', today())
            ->exists();
    }

    /**
     * Get today's attendance count.
     */
    public function getTodayAttendanceCount($siteId)
    {
        return Attendance::where('site_id', $siteId)
            ->whereDate('attendance_date', today())
            ->count();
    }

    /**
     * Create attendance record.
     */
    public function createAttendance($siteId, $userId, $greeting = null)
    {
        // Check if already attended today
        if ($this->hasAttendedToday($siteId, $userId)) {
            throw new \Exception('이미 오늘 출석체크를 완료했습니다.');
        }

        DB::beginTransaction();
        try {
            // If greeting is empty, get random greeting from settings
            if (empty($greeting)) {
                $settings = $this->getSettings($siteId);
                $greetings = $settings['greetings'] ?? [];
                if (!empty($greetings)) {
                    $greeting = $greetings[array_rand($greetings)];
                }
            }

            // Get today's attendance count to determine rank
            $todayCount = $this->getTodayAttendanceCount($siteId);
            $rank = $todayCount + 1;

            // Calculate points
            $points = $this->calculatePoints($siteId, $rank, $userId);

            // Create attendance record
            $attendance = Attendance::create([
                'site_id' => $siteId,
                'user_id' => $userId,
                'attendance_date' => today(),
                'attendance_time' => now(),
                'greeting' => $greeting,
                'points_earned' => $points,
                'rank' => $rank,
            ]);

            // Add points to user
            $user = User::find($userId);
            if ($user) {
                $user->addPoints($points);
            }

            DB::commit();
            return $attendance;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate points based on rank, consecutive days, and default points.
     */
    public function calculatePoints($siteId, $rank, $userId)
    {
        $totalPoints = 0;

        // Rank-based points
        $rankPoints = $this->getRankPoints($siteId, $rank);
        $totalPoints += $rankPoints;

        // Consecutive days points (오늘 출석 전의 연속일수)
        $consecutiveDays = $this->getConsecutiveDaysBeforeToday($siteId, $userId);
        $consecutivePoints = $this->getConsecutivePoints($siteId, $consecutiveDays);
        $totalPoints += $consecutivePoints;

        // Default points (if no rank or consecutive points)
        if ($totalPoints == 0) {
            $defaultPoints = $this->getDefaultPoints($siteId);
            $totalPoints += $defaultPoints;
        }

        return $totalPoints;
    }

    /**
     * Get rank-based points.
     */
    public function getRankPoints($siteId, $rank)
    {
        $setting = AttendanceSetting::where('site_id', $siteId)
            ->where('setting_type', 'rank_points')
            ->where('setting_key', (string)$rank)
            ->first();

        return $setting ? (int)$setting->setting_value : 0;
    }

    /**
     * Get consecutive days for user (오늘 포함).
     */
    public function getConsecutiveDays($siteId, $userId)
    {
        $days = 0;
        $date = today();

        while (true) {
            $attendance = Attendance::where('site_id', $siteId)
                ->where('user_id', $userId)
                ->whereDate('attendance_date', $date)
                ->first();

            if ($attendance) {
                $days++;
                $date = $date->subDay();
            } else {
                break;
            }
        }

        return $days;
    }

    /**
     * Get consecutive days before today (오늘 출석 전의 연속일수).
     */
    public function getConsecutiveDaysBeforeToday($siteId, $userId)
    {
        $days = 0;
        $date = today()->subDay(); // 어제부터 시작

        while (true) {
            $attendance = Attendance::where('site_id', $siteId)
                ->where('user_id', $userId)
                ->whereDate('attendance_date', $date)
                ->first();

            if ($attendance) {
                $days++;
                $date = $date->subDay();
            } else {
                break;
            }
        }

        return $days;
    }

    /**
     * Get consecutive days points.
     */
    public function getConsecutivePoints($siteId, $days)
    {
        $setting = AttendanceSetting::where('site_id', $siteId)
            ->where('setting_type', 'consecutive_points')
            ->where('setting_key', (string)$days)
            ->first();

        return $setting ? (int)$setting->setting_value : 0;
    }

    /**
     * Get default points.
     */
    public function getDefaultPoints($siteId)
    {
        $setting = AttendanceSetting::where('site_id', $siteId)
            ->where('setting_type', 'default_points')
            ->where('setting_key', 'default')
            ->first();

        return $setting ? (int)$setting->setting_value : 0;
    }

    /**
     * Get today's attendances with pagination.
     */
    public function getTodayAttendances($siteId, $perPage = 15)
    {
        return Attendance::where('site_id', $siteId)
            ->whereDate('attendance_date', today())
            ->with('user')
            ->orderBy('rank', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get all attendances with pagination.
     */
    public function getAllAttendances($siteId, $perPage = 15, $date = null)
    {
        $query = Attendance::where('site_id', $siteId)
            ->with('user');

        if ($date) {
            $query->whereDate('attendance_date', $date);
        }

        return $query->orderBy('attendance_date', 'desc')
            ->orderBy('rank', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get user's consecutive days.
     */
    public function getUserConsecutiveDays($siteId, $userId)
    {
        return $this->getConsecutiveDays($siteId, $userId);
    }

    /**
     * Get attendance settings.
     */
    public function getSettings($siteId)
    {
        $settings = AttendanceSetting::where('site_id', $siteId)
            ->orderBy('setting_type')
            ->orderBy('order')
            ->get();

        $result = [
            'rank_points' => [],
            'consecutive_points' => [],
            'default_points' => 0,
            'greetings' => [],
            'per_page' => 15,
        ];

        foreach ($settings as $setting) {
            switch ($setting->setting_type) {
                case 'rank_points':
                    $result['rank_points'][$setting->setting_key] = $setting->setting_value;
                    break;
                case 'consecutive_points':
                    $result['consecutive_points'][$setting->setting_key] = $setting->setting_value;
                    break;
                case 'default_points':
                    $result['default_points'] = (int)$setting->setting_value;
                    break;
                case 'greeting':
                    $result['greetings'][] = $setting->setting_value;
                    break;
                case 'per_page':
                    $result['per_page'] = (int)$setting->setting_value;
                    break;
            }
        }

        return $result;
    }

    /**
     * Save attendance settings.
     */
    public function saveSettings($siteId, array $settings)
    {
        DB::beginTransaction();
        try {
            // Delete existing settings
            AttendanceSetting::where('site_id', $siteId)->delete();

            // Save rank points
            if (isset($settings['rank_points']) && is_array($settings['rank_points'])) {
                foreach ($settings['rank_points'] as $rank => $points) {
                    if ($points > 0) {
                        AttendanceSetting::create([
                            'site_id' => $siteId,
                            'setting_type' => 'rank_points',
                            'setting_key' => (string)$rank,
                            'setting_value' => (string)$points,
                            'order' => (int)$rank,
                        ]);
                    }
                }
            }

            // Save consecutive points
            if (isset($settings['consecutive_points']) && is_array($settings['consecutive_points'])) {
                foreach ($settings['consecutive_points'] as $days => $points) {
                    if ($points > 0) {
                        AttendanceSetting::create([
                            'site_id' => $siteId,
                            'setting_type' => 'consecutive_points',
                            'setting_key' => (string)$days,
                            'setting_value' => (string)$points,
                            'order' => (int)$days,
                        ]);
                    }
                }
            }

            // Save default points
            if (isset($settings['default_points']) && $settings['default_points'] > 0) {
                AttendanceSetting::create([
                    'site_id' => $siteId,
                    'setting_type' => 'default_points',
                    'setting_key' => 'default',
                    'setting_value' => (string)$settings['default_points'],
                    'order' => 0,
                ]);
            }

            // Save greetings
            if (isset($settings['greetings']) && is_array($settings['greetings'])) {
                foreach ($settings['greetings'] as $index => $greeting) {
                    if (!empty($greeting)) {
                        AttendanceSetting::create([
                            'site_id' => $siteId,
                            'setting_type' => 'greeting',
                            'setting_key' => (string)$index,
                            'setting_value' => $greeting,
                            'order' => $index,
                        ]);
                    }
                }
            }

            // Save per page
            if (isset($settings['per_page']) && $settings['per_page'] > 0) {
                AttendanceSetting::create([
                    'site_id' => $siteId,
                    'setting_type' => 'per_page',
                    'setting_key' => 'default',
                    'setting_value' => (string)$settings['per_page'],
                    'order' => 0,
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

