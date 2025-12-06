<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display attendance board.
     */
    public function index(Site $site, Request $request)
    {
        $hasAttended = false;
        $consecutiveDays = 0;

        if (auth()->check()) {
            $hasAttended = $this->attendanceService->hasAttendedToday($site->id, auth()->id());
            $consecutiveDays = $this->attendanceService->getUserConsecutiveDays($site->id, auth()->id());
        }

        // Get per page setting
        $settings = $this->attendanceService->getSettings($site->id);
        $perPage = $settings['per_page'] ?? 15;

        // Get today's attendances
        $attendances = $this->attendanceService->getTodayAttendances($site->id, $perPage);

        return view('attendance.index', compact('site', 'attendances', 'hasAttended', 'consecutiveDays', 'settings'));
    }

    /**
     * Store attendance.
     */
    public function store(Site $site, Request $request)
    {
        $request->validate([
            'greeting' => 'nullable|string|max:255',
        ]);

        if (!auth()->check()) {
            return redirect()->route('login', ['site' => $site->slug])
                ->with('error', '출석체크를 하려면 로그인이 필요합니다.');
        }

        try {
            $attendance = $this->attendanceService->createAttendance(
                $site->id,
                auth()->id(),
                $request->input('greeting')
            );

            return redirect()->route('attendance.index', ['site' => $site->slug])
                ->with('attendance_success', true)
                ->with('attendance_points', $attendance->points_earned);
        } catch (\Exception $e) {
            return redirect()->route('attendance.index', ['site' => $site->slug])
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get attendance points info (for popup).
     */
    public function pointsInfo(Site $site)
    {
        $settings = $this->attendanceService->getSettings($site->id);
        return response()->json($settings);
    }
}

