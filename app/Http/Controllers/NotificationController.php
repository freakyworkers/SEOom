<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display notifications list.
     */
    public function index(Site $site)
    {
        $user = Auth::user();
        
        // 테이블이 존재하지 않으면 빈 결과 반환
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            $notifications = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            return view('notifications.index', compact('site', 'notifications'));
        }
        
        $notifications = Notification::where('site_id', $site->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('notifications.index', compact('site', 'notifications'));
    }

    /**
     * Mark notification as read and redirect.
     */
    public function read(Site $site, Notification $notification)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return redirect()->route('notifications.index', ['site' => $site->slug])
                ->with('error', '알림 테이블이 존재하지 않습니다. 마이그레이션을 실행해주세요.');
        }
        
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        // 포인트 지급 알림인 경우 현재 페이지에 머물고 모달만 열기
        if ($notification->type === 'point_award') {
            return back();
        }

        if ($notification->link && $notification->link !== '#') {
            return redirect($notification->link);
        }

        return redirect()->route('notifications.index', ['site' => $site->slug]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Site $site)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return back()->with('error', '알림 테이블이 존재하지 않습니다. 마이그레이션을 실행해주세요.');
        }
        
        Notification::where('site_id', $site->id)
            ->where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', '모든 알림을 읽음 처리했습니다.');
    }

    /**
     * Get unread notifications count (AJAX).
     */
    public function unreadCount(Site $site)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return response()->json(['count' => 0]);
        }
        
        $count = Notification::getUnreadCount(Auth::id(), $site->id);
        
        return response()->json(['count' => $count]);
    }
}

