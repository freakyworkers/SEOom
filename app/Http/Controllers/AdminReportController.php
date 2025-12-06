<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Report;
use App\Models\Penalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminReportController extends Controller
{
    /**
     * Display reports page.
     */
    public function index(Site $site)
    {
        if (!Auth::check() || !Auth::user()->canManage()) {
            abort(403);
        }

        // Get reports with pagination
        $reports = Report::where('site_id', $site->id)
            ->with(['reporter', 'reportedUser', 'post', 'chatMessage', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get penalties
        $penalties = Penalty::where('site_id', $site->id)
            ->with(['user', 'issuer', 'report'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'penalties_page');

        return view('admin.reports.index', compact('site', 'reports', 'penalties'));
    }

    /**
     * Show report details.
     */
    public function show(Site $site, Report $report)
    {
        if (!Auth::check() || !Auth::user()->canManage()) {
            abort(403);
        }

        if ($report->site_id !== $site->id) {
            abort(404);
        }

        $report->load(['reporter', 'reportedUser', 'post', 'chatMessage', 'reviewer']);

        return view('admin.reports.show', compact('site', 'report'));
    }

    /**
     * Update report status.
     */
    public function updateStatus(Site $site, Report $report, Request $request)
    {
        if (!Auth::check() || !Auth::user()->canManage()) {
            abort(403);
        }

        if ($report->site_id !== $site->id) {
            abort(404);
        }

        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
        ]);

        $report->update([
            'status' => $request->status,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', '신고 상태가 업데이트되었습니다.');
    }

    /**
     * Issue penalty.
     */
    public function issuePenalty(Site $site, Request $request)
    {
        if (!Auth::check() || !Auth::user()->canManage()) {
            abort(403);
        }

        $request->validate([
            'report_id' => 'nullable|exists:reports,id',
            'user_id' => 'nullable|exists:users,id',
            'guest_session_id' => 'nullable|string',
            'nickname' => 'required|string',
            'type' => 'required|in:chat_ban,post_ban',
            'reason' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date',
        ]);

        $penalty = Penalty::create([
            'site_id' => $site->id,
            'user_id' => $request->input('user_id'),
            'guest_session_id' => $request->input('guest_session_id'),
            'nickname' => $request->input('nickname'),
            'type' => $request->input('type'),
            'report_id' => $request->input('report_id'),
            'reason' => $request->input('reason'),
            'expires_at' => $request->input('expires_at'),
            'is_active' => true,
            'issued_by' => Auth::id(),
        ]);

        // Update report status if report_id is provided
        if ($request->input('report_id')) {
            $report = Report::findOrFail($request->input('report_id'));
            $report->update([
                'status' => 'resolved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        }

        return back()->with('success', '패널티가 부여되었습니다.');
    }

    /**
     * Remove penalty.
     */
    public function removePenalty(Site $site, Penalty $penalty)
    {
        if (!Auth::check() || !Auth::user()->canManage()) {
            abort(403);
        }

        if ($penalty->site_id !== $site->id) {
            abort(404);
        }

        $penalty->deactivate();

        return back()->with('success', '패널티가 해제되었습니다.');
    }
}

