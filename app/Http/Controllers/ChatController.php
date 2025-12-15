<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\ChatMessage;
use App\Models\ChatSetting;
use App\Models\ChatGuestSession;
use App\Models\Penalty;
use App\Models\BlockedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get chat messages (API).
     */
    public function getMessages(Site $site, Request $request)
    {
        // Check if site has chat widget feature
        if (!$site->hasFeature('chat_widget')) {
            return response()->json(['error' => '채팅 위젯 기능이 활성화되어 있지 않습니다.'], 403);
        }

        $chatSetting = ChatSetting::firstOrCreate(
            ['site_id' => $site->id],
            [
                'notice' => null,
                'auto_delete_24h' => false,
                'allow_guest' => false,
                'banned_words' => null,
            ]
        );
        
        // Check if guest is allowed
        $isGuest = !Auth::check();
        if ($isGuest && !$chatSetting->allow_guest) {
            return response()->json(['error' => '비로그인 사용자는 채팅을 사용할 수 없습니다.'], 403);
        }

        // Get user info
        $userId = Auth::id();
        $guestSessionId = null;
        $nickname = null;
        
        if ($userId) {
            $nickname = Auth::user()->nickname ?? Auth::user()->name;
        } else {
            $sessionId = session()->getId();
            $guestSession = ChatGuestSession::getOrCreate($sessionId, $site->id, $request->ip(), $request->userAgent());
            $guestSessionId = $guestSession->session_id;
            $nickname = $guestSession->getNickname();
        }

        // Get messages (last 20)
        $messages = ChatMessage::where('site_id', $site->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->map(function($msg) {
                // user_id가 있으면 user의 현재 nickname을 사용, 없으면 저장된 nickname 사용
                $displayNickname = $msg->nickname;
                if ($msg->user_id && $msg->user) {
                    $displayNickname = $msg->user->nickname ?? $msg->user->name ?? $msg->nickname;
                }
                
                return [
                    'id' => $msg->id,
                    'user_id' => $msg->user_id,
                    'guest_session_id' => $msg->guest_session_id,
                    'nickname' => $displayNickname,
                    'message' => $msg->message,
                    'content' => $msg->message, // alias for compatibility
                    'attachment_path' => $msg->attachment_path,
                    'file_path' => $msg->attachment_path, // alias for compatibility
                    'attachment_type' => $msg->attachment_type,
                    'created_at' => $msg->created_at->toISOString(),
                ];
            })
            ->values();

        return response()->json([
            'messages' => $messages,
            'nickname' => $nickname,
            'isGuest' => $isGuest,
            'notice' => $chatSetting->notice,
        ]);
    }

    /**
     * Send chat message (API).
     */
    public function sendMessage(Site $site, Request $request)
    {
        // Check if site has chat widget feature
        if (!$site->hasFeature('chat_widget')) {
            return response()->json(['error' => '채팅 위젯 기능이 활성화되어 있지 않습니다.'], 403);
        }

        $chatSetting = ChatSetting::firstOrCreate(
            ['site_id' => $site->id],
            [
                'notice' => null,
                'auto_delete_24h' => false,
                'allow_guest' => false,
                'banned_words' => null,
            ]
        );
        
        // Check if guest is allowed
        $isGuest = !Auth::check();
        if ($isGuest && !$chatSetting->allow_guest) {
            return response()->json(['error' => '비로그인 사용자는 채팅을 사용할 수 없습니다.'], 403);
        }

        // Get user info
        $userId = Auth::id();
        $guestSessionId = null;
        $nickname = null;
        
        if ($userId) {
            $nickname = Auth::user()->nickname ?? Auth::user()->name;
        } else {
            $sessionId = session()->getId();
            $guestSession = ChatGuestSession::getOrCreate($sessionId, $site->id, $request->ip(), $request->userAgent());
            $guestSessionId = $guestSession->session_id;
            $nickname = $guestSession->getNickname();
        }

        // Check penalties
        $penalties = Penalty::where('site_id', $site->id)
            ->where(function($q) use ($userId, $guestSessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('guest_session_id', $guestSessionId);
                }
            })
            ->where('type', 'chat_ban')
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($penalties) {
            return response()->json(['error' => '채팅이 금지되었습니다.'], 403);
        }

        // Validate - message or attachment must be present
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|image|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if at least message or attachment is provided
        if (empty($request->message) && !$request->hasFile('attachment')) {
            return response()->json(['error' => '메시지 또는 첨부파일을 입력해주세요.'], 422);
        }

        // Check banned words (only if message is provided)
        if (!empty($request->message) && $chatSetting->containsBannedWords($request->message)) {
            return response()->json(['error' => '금지 단어가 포함되었습니다.'], 422);
        }

        // Handle attachment
        $attachmentPath = null;
        $attachmentType = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file->isValid()) {
                $path = $file->store('chat/attachments', 'public');
                $attachmentPath = $path;
                $attachmentType = 'image';
            }
        }

        // Create message
        $message = ChatMessage::create([
            'site_id' => $site->id,
            'user_id' => $userId,
            'guest_session_id' => $guestSessionId,
            'nickname' => $nickname,
            'message' => $request->message ?? '',
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->load('user'),
        ]);
    }

    /**
     * Report chat message (API).
     */
    public function reportMessage(Site $site, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|exists:chat_messages,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chatMessage = ChatMessage::findOrFail($request->message_id);
        
        // Get reporter info
        $reporterId = Auth::id();
        $reporterGuestSessionId = null;
        $reporterNickname = null;
        
        if ($reporterId) {
            $reporterNickname = Auth::user()->nickname ?? Auth::user()->name;
        } else {
            $sessionId = session()->getId();
            $guestSession = ChatGuestSession::where('session_id', $sessionId)
                ->where('site_id', $site->id)
                ->first();
            if ($guestSession) {
                $reporterGuestSessionId = $guestSession->session_id;
                $reporterNickname = $guestSession->getNickname();
            } else {
                return response()->json(['error' => '세션을 찾을 수 없습니다.'], 404);
            }
        }

        // Get reported user info
        $reportedUserId = $chatMessage->user_id;
        $reportedGuestSessionId = $chatMessage->guest_session_id;
        $reportedNickname = $chatMessage->nickname;

        // Create report
        $report = \App\Models\Report::create([
            'site_id' => $site->id,
            'reporter_id' => $reporterId,
            'reporter_guest_session_id' => $reporterGuestSessionId,
            'reporter_nickname' => $reporterNickname,
            'reported_user_id' => $reportedUserId,
            'reported_guest_session_id' => $reportedGuestSessionId,
            'reported_nickname' => $reportedNickname,
            'report_type' => 'chat',
            'chat_message_id' => $chatMessage->id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => '신고가 접수되었습니다.',
        ]);
    }

    /**
     * Block user (API).
     */
    public function blockUser(Site $site, Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => '로그인이 필요합니다.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'guest_session_id' => 'nullable|string',
            'nickname' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if already blocked
        $existing = BlockedUser::where('site_id', $site->id)
            ->where('blocker_id', Auth::id())
            ->where(function($q) use ($request) {
                if ($request->user_id) {
                    $q->where('blocked_user_id', $request->user_id);
                } else {
                    $q->where('blocked_guest_session_id', $request->guest_session_id);
                }
            })
            ->first();

        if ($existing) {
            return response()->json(['error' => '이미 차단된 사용자입니다.'], 422);
        }

        // Create block
        BlockedUser::create([
            'site_id' => $site->id,
            'blocker_id' => Auth::id(),
            'blocked_user_id' => $request->user_id,
            'blocked_guest_session_id' => $request->guest_session_id,
            'blocked_nickname' => $request->nickname,
        ]);

        return response()->json([
            'success' => true,
            'message' => '사용자가 차단되었습니다.',
        ]);
    }
}

