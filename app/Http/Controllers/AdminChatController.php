<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\ChatMessage;
use App\Models\ChatSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
{
    /**
     * Display chat management page.
     */
    public function index(Site $site)
    {
        if (!$site->hasFeature('chat_widget')) {
            abort(403, '채팅 위젯 기능이 활성화되어 있지 않습니다.');
        }

        if (!Auth::check() || !Auth::user()->canManage()) {
            abort(403);
        }

        $chatSetting = ChatSetting::firstOrCreate(['site_id' => $site->id]);
        
        // Get messages with pagination (20 per page)
        $messages = ChatMessage::where('site_id', $site->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.chat.index', compact('site', 'chatSetting', 'messages'));
    }

    /**
     * Update chat settings.
     */
    public function updateSettings(Site $site, Request $request)
    {
        if (!$site->hasFeature('chat_widget')) {
            abort(403, '채팅 위젯 기능이 활성화되어 있지 않습니다.');
        }

        if (!Auth::check() || !Auth::user()->canManage()) {
            abort(403);
        }

        $chatSetting = ChatSetting::firstOrCreate(['site_id' => $site->id]);

        $chatSetting->update([
            'notice' => $request->input('notice'),
            'auto_delete_24h' => $request->boolean('auto_delete_24h'),
            'allow_guest' => $request->boolean('allow_guest'),
            'banned_words' => $request->input('banned_words'),
        ]);

        return back()->with('success', '채팅 설정이 업데이트되었습니다.');
    }

    /**
     * Delete chat message.
     */
    public function deleteMessage(Site $site, ChatMessage $message)
    {
        if (!$site->hasFeature('chat_widget')) {
            abort(403, '채팅 위젯 기능이 활성화되어 있지 않습니다.');
        }

        if (!Auth::check() || !Auth::user()->canManage()) {
            abort(403);
        }

        if ($message->site_id !== $site->id) {
            abort(404);
        }

        // Delete attachment if exists
        if ($message->attachment_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($message->attachment_path);
        }

        $message->delete();

        return back()->with('success', '메시지가 삭제되었습니다.');
    }

    /**
     * Ban user from chat.
     */
    public function banUser(Site $site, Request $request)
    {
        if (!$site->hasFeature('chat_widget')) {
            abort(403, '채팅 위젯 기능이 활성화되어 있지 않습니다.');
        }

        if (!Auth::check() || !Auth::user()->canManage()) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'guest_session_id' => 'nullable|string',
            'nickname' => 'required|string',
            'reason' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date',
        ]);

        $penalty = \App\Models\Penalty::create([
            'site_id' => $site->id,
            'user_id' => $request->input('user_id'),
            'guest_session_id' => $request->input('guest_session_id'),
            'nickname' => $request->input('nickname'),
            'type' => 'chat_ban',
            'reason' => $request->input('reason'),
            'expires_at' => $request->input('expires_at'),
            'is_active' => true,
            'issued_by' => Auth::id(),
        ]);

        return back()->with('success', '사용자가 채팅에서 금지되었습니다.');
    }
}

