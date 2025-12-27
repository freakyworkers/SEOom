<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });

        $this->attendanceService = $attendanceService;
    }

    /**
     * Display attendance settings.
     */
    public function index(Site $site)
    {
        $settings = $this->attendanceService->getSettings($site->id);
        return view('admin.attendance.index', compact('site', 'settings'));
    }

    /**
     * Update attendance settings.
     */
    public function update(Site $site, Request $request)
    {
        $settings = [
            'rank_points' => [],
            'consecutive_points' => [],
            'default_points' => (int)$request->input('default_points', 0),
            'greetings' => [],
            'per_page' => (int)$request->input('per_page', 15),
        ];

        // Rank points
        if ($request->has('rank_points')) {
            foreach ($request->input('rank_points', []) as $rank => $points) {
                if (!empty($points) && $points > 0) {
                    $settings['rank_points'][$rank] = (int)$points;
                }
            }
        }

        // Consecutive points
        if ($request->has('consecutive_points')) {
            foreach ($request->input('consecutive_points', []) as $days => $points) {
                if (!empty($points) && $points > 0) {
                    $settings['consecutive_points'][$days] = (int)$points;
                }
            }
        }

        // Greetings
        if ($request->has('greetings')) {
            foreach ($request->input('greetings', []) as $greeting) {
                if (!empty($greeting)) {
                    $settings['greetings'][] = $greeting;
                }
            }
        }

        try {
            $this->attendanceService->saveSettings($site->id, $settings);
            return redirect()->route('admin.attendance.index', ['site' => $site->slug])
                ->with('success', '출석 설정이 저장되었습니다.');
        } catch (\Exception $e) {
            return redirect()->route('admin.attendance.index', ['site' => $site->slug])
                ->with('error', '설정 저장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}







