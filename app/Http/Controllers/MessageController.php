<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display messages list.
     */
    public function index(Site $site, Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'received'); // 'received' or 'sent'
        
        // 테이블이 존재하지 않으면 빈 결과 반환
        if (!\Illuminate\Support\Facades\Schema::hasTable('messages')) {
            $messages = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            return view('messages.index', compact('site', 'messages', 'type'));
        }
        
        if ($type === 'sent') {
            // 보낸 쪽지
            $messages = Message::where('site_id', $site->id)
                ->where('sender_id', $user->id)
                ->with(['receiver', 'parent'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // 받은 쪽지
            $messages = Message::where('site_id', $site->id)
                ->where('receiver_id', $user->id)
                ->with(['sender', 'parent'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return view('messages.index', compact('site', 'messages', 'type'));
    }

    /**
     * Show a specific message.
     */
    public function show(Site $site, Message $message)
    {
        if ($message->receiver_id !== Auth::id() && $message->sender_id !== Auth::id()) {
            abort(403);
        }

        // 수신자인 경우 읽음 처리
        if ($message->receiver_id === Auth::id() && !$message->is_read) {
            $message->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => $message->load(['sender', 'receiver', 'parent']),
        ]);
    }

    /**
     * Store a new message.
     */
    public function store(Site $site, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:messages,id',
            'points' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $receiver = User::findOrFail($request->receiver_id);
        $sender = Auth::user();
        $points = $request->points ?? 0;

        // 자기 자신에게 쪽지 보내기 방지
        if ($receiver->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => '자기 자신에게 쪽지를 보낼 수 없습니다.',
            ], 422);
        }

        // 포인트 쪽지 활성화 확인
        // 1. 플랜에 포인트 쪽지 기능이 포함되어 있는지 확인
        $hasPointMessageFeature = $site->hasRegistrationFeature('point_message');
        
        // 2. 사이트 설정에서 포인트 쪽지가 활성화되어 있는지 확인
        $enablePointMessage = $site->getSetting('enable_point_message', '0') == '1';
        
        // 포인트가 있는 경우 검증
        if ($points > 0) {
            if (!$hasPointMessageFeature) {
                return response()->json([
                    'success' => false,
                    'message' => '포인트 쪽지 기능이 플랜에 포함되어 있지 않습니다.',
                ], 422);
            }
            
            if (!$enablePointMessage) {
                return response()->json([
                    'success' => false,
                    'message' => '포인트 쪽지 기능이 비활성화되어 있습니다.',
                ], 422);
            }

            if ($points > $sender->points) {
                return response()->json([
                    'success' => false,
                    'message' => '보유 포인트가 부족합니다.',
                ], 422);
            }

            // 포인트 차감
            $sender->subtractPoints($points);
        }

        // 부모 메시지가 있는 경우, 부모 메시지의 수신자가 현재 사용자인지 확인
        if ($request->parent_id) {
            $parentMessage = Message::findOrFail($request->parent_id);
            if ($parentMessage->receiver_id !== Auth::id() && $parentMessage->sender_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => '권한이 없습니다.',
                ], 403);
            }
        }

        $message = Message::create([
            'site_id' => $site->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $receiver->id,
            'content' => $request->content,
            'parent_id' => $request->parent_id,
            'points' => $points,
            'points_received' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => '쪽지가 전송되었습니다.',
            'data' => $message->load(['sender', 'receiver']),
            'user_points' => $sender->fresh()->points,
        ]);
    }

    /**
     * Receive points from message.
     */
    public function receivePoints(Site $site, Message $message)
    {
        // 수신자만 포인트 수령 가능
        if ($message->receiver_id !== Auth::id()) {
            abort(403);
        }

        // 이미 수령한 경우
        if ($message->points_received) {
            return response()->json([
                'success' => false,
                'message' => '이미 수령한 포인트입니다.',
            ], 422);
        }

        // 포인트가 없는 경우
        if ($message->points <= 0) {
            return response()->json([
                'success' => false,
                'message' => '수령할 포인트가 없습니다.',
            ], 422);
        }

        // 포인트 지급
        $receiver = Auth::user();
        $receiver->addPoints($message->points);

        // 수령 완료 처리
        $message->points_received = true;
        $message->save();

        return response()->json([
            'success' => true,
            'message' => '포인트를 수령했습니다.',
            'points' => $message->points,
            'user_points' => $receiver->fresh()->points,
        ]);
    }

    /**
     * Delete a message.
     */
    public function destroy(Site $site, Message $message)
    {
        // 수신자 또는 발신자만 삭제 가능
        if ($message->receiver_id !== Auth::id() && $message->sender_id !== Auth::id()) {
            abort(403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => '쪽지가 삭제되었습니다.',
        ]);
    }
}

