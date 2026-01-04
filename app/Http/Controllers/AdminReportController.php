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
    public function index(Site $site, Request $request)
    {
        // 테스트 관리자는 권한 체크 우회
        if (!session('is_test_admin') || !session('test_admin_site_id')) {
            if (!Auth::check() || !Auth::user()->canManage()) {
                abort(403);
            }
        }

        // Get search parameters
        $searchType = $request->get('search_type', 'all');
        $searchKeyword = $request->get('search', '');
        $reportStatus = $request->get('status', '');
        $reportType = $request->get('report_type', '');
        $perPage = $request->get('per_page', 5);

        // Build reports query
        $reportsQuery = Report::where('site_id', $site->id)
            ->with(['reporter', 'reportedUser', 'post', 'comment', 'chatMessage', 'reviewer']);

        // Apply search filters
        if ($searchKeyword) {
            if ($searchType === 'reporter') {
                $reportsQuery->where(function($q) use ($searchKeyword) {
                    $q->where('reporter_nickname', 'like', '%' . $searchKeyword . '%')
                      ->orWhereHas('reporter', function($q) use ($searchKeyword) {
                          $q->where('name', 'like', '%' . $searchKeyword . '%')
                            ->orWhere('nickname', 'like', '%' . $searchKeyword . '%');
                      });
                });
            } elseif ($searchType === 'reported') {
                $reportsQuery->where(function($q) use ($searchKeyword) {
                    $q->where('reported_nickname', 'like', '%' . $searchKeyword . '%')
                      ->orWhereHas('reportedUser', function($q) use ($searchKeyword) {
                          $q->where('name', 'like', '%' . $searchKeyword . '%')
                            ->orWhere('nickname', 'like', '%' . $searchKeyword . '%');
                      });
                });
            } elseif ($searchType === 'reason') {
                $reportsQuery->where('reason', 'like', '%' . $searchKeyword . '%');
            } else {
                // Search all fields
                $reportsQuery->where(function($q) use ($searchKeyword) {
                    $q->where('reporter_nickname', 'like', '%' . $searchKeyword . '%')
                      ->orWhere('reported_nickname', 'like', '%' . $searchKeyword . '%')
                      ->orWhere('reason', 'like', '%' . $searchKeyword . '%')
                      ->orWhereHas('reporter', function($q) use ($searchKeyword) {
                          $q->where('name', 'like', '%' . $searchKeyword . '%')
                            ->orWhere('nickname', 'like', '%' . $searchKeyword . '%');
                      })
                      ->orWhereHas('reportedUser', function($q) use ($searchKeyword) {
                          $q->where('name', 'like', '%' . $searchKeyword . '%')
                            ->orWhere('nickname', 'like', '%' . $searchKeyword . '%');
                      });
                });
            }
        }

        // Apply status filter
        if ($reportStatus) {
            $reportsQuery->where('status', $reportStatus);
        }

        // Apply report type filter
        if ($reportType) {
            $reportsQuery->where('report_type', $reportType);
        }

        // Get reports with pagination
        $reports = $reportsQuery->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'reports_page')
            ->appends($request->except('reports_page'));

        // Get penalty search parameters
        $penaltySearchType = $request->get('penalty_search_type', 'all');
        $penaltySearchKeyword = $request->get('penalty_search', '');
        $penaltyType = $request->get('penalty_type', '');
        $penaltyPerPage = $request->get('penalty_per_page', 5);

        // Build penalties query
        $penaltiesQuery = Penalty::where('site_id', $site->id)
            ->with(['user', 'issuer', 'report'])
            ->where('is_active', true);

        // Apply penalty search filters
        if ($penaltySearchKeyword) {
            if ($penaltySearchType === 'nickname') {
                $penaltiesQuery->where(function($q) use ($penaltySearchKeyword) {
                    $q->where('nickname', 'like', '%' . $penaltySearchKeyword . '%')
                      ->orWhereHas('user', function($q) use ($penaltySearchKeyword) {
                          $q->where('name', 'like', '%' . $penaltySearchKeyword . '%')
                            ->orWhere('nickname', 'like', '%' . $penaltySearchKeyword . '%');
                      });
                });
            } elseif ($penaltySearchType === 'reason') {
                $penaltiesQuery->where('reason', 'like', '%' . $penaltySearchKeyword . '%');
            } else {
                $penaltiesQuery->where(function($q) use ($penaltySearchKeyword) {
                    $q->where('nickname', 'like', '%' . $penaltySearchKeyword . '%')
                      ->orWhere('reason', 'like', '%' . $penaltySearchKeyword . '%')
                      ->orWhereHas('user', function($q) use ($penaltySearchKeyword) {
                          $q->where('name', 'like', '%' . $penaltySearchKeyword . '%')
                            ->orWhere('nickname', 'like', '%' . $penaltySearchKeyword . '%');
                      });
                });
            }
        }

        // Apply penalty type filter
        if ($penaltyType) {
            $penaltiesQuery->where('type', $penaltyType);
        }

        // Get penalties with pagination
        $penalties = $penaltiesQuery->orderBy('created_at', 'desc')
            ->paginate($penaltyPerPage, ['*'], 'penalties_page')
            ->appends($request->except('penalties_page'));

        return view('admin.reports.index', compact('site', 'reports', 'penalties'));
    }

    /**
     * Show report details.
     */
    public function show(Site $site, Report $report)
    {
        // 테스트 관리자는 권한 체크 우회
        if (!session('is_test_admin') || !session('test_admin_site_id')) {
            if (!Auth::check() || !Auth::user()->canManage()) {
                abort(403);
            }
        }

        if ($report->site_id !== $site->id) {
            abort(404);
        }

        $report->load(['reporter', 'reportedUser', 'post', 'comment', 'chatMessage', 'reviewer']);

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
            'type' => 'required|in:chat_ban,post_ban,comment_ban',
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

