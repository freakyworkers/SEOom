<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Create a notification for comment on user's post.
     */
    public function createCommentNotification(Comment $comment)
    {
        // 테이블이 없으면 알림 생성 안함
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return;
        }
        
        $post = $comment->post;
        if (!$post || $post->user_id === $comment->user_id) {
            // 자신의 댓글이거나 게시글이 없으면 알림 생성 안함
            return;
        }

        // 같은 게시글에 대한 최근 알림 확인 (중복 방지)
        $recentNotification = Notification::where('site_id', $post->site_id)
            ->where('user_id', $post->user_id)
            ->where('type', 'comment')
            ->where('data->post_id', $post->id)
            ->where('created_at', '>', Carbon::now()->subMinutes(5))
            ->first();

        if ($recentNotification) {
            // 기존 알림 업데이트 (댓글 수 증가)
            $data = $recentNotification->data ?? [];
            $commentCount = ($data['comment_count'] ?? 1) + 1;
            $commentUsers = $data['comment_users'] ?? [];
            
            $commentUserNickname = $comment->user->nickname ?? $comment->user->name;
            if (!in_array($commentUserNickname, $commentUsers)) {
                $commentUsers[] = $commentUserNickname;
            }
            
            $data['comment_count'] = $commentCount;
            $data['comment_users'] = $commentUsers;
            
            $commentText = count($commentUsers) > 1 
                ? $commentUsers[0] . '님외 ' . (count($commentUsers) - 1) . '명이 글에 댓글을 남기셨습니다.'
                : $commentUsers[0] . '님이 글에 댓글을 남기셨습니다.';
            
            $recentNotification->update([
                'content' => $commentText . "\n" . $post->title,
                'data' => $data,
                'created_at' => Carbon::now(),
            ]);
        } else {
            // 새 알림 생성
            Notification::create([
                'site_id' => $post->site_id,
                'user_id' => $post->user_id,
                'type' => 'comment',
                'title' => '댓글 알림',
                'content' => ($comment->user->nickname ?? $comment->user->name) . '님이 글에 댓글을 남기셨습니다.' . "\n" . $post->title,
                'link' => route('posts.show', [
                    'site' => $post->site->slug,
                    'boardSlug' => $post->board->slug,
                    'post' => $post->id
                ]),
                'data' => [
                    'post_id' => $post->id,
                    'comment_id' => $comment->id,
                    'comment_count' => 1,
                    'comment_users' => [$comment->user->nickname ?? $comment->user->name],
                ],
            ]);
        }
    }

    /**
     * Create a notification for message.
     */
    public function createMessageNotification($userId, $siteId, $senderName, $messageId)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return;
        }
        
        Notification::create([
            'site_id' => $siteId,
            'user_id' => $userId,
            'type' => 'message',
            'title' => '쪽지 알림',
            'content' => $senderName . '님이 쪽지를 전송했습니다.',
            'link' => route('messages.show', ['site' => \App\Models\Site::find($siteId)->slug ?? 'default', 'message' => $messageId]),
            'data' => [
                'message_id' => $messageId,
                'sender_name' => $senderName,
            ],
        ]);
    }

    /**
     * Create a notification for point award.
     */
    public function createPointAwardNotification($userId, $siteId, $points, $eventTitle = null)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return;
        }
        
        $content = $eventTitle 
            ? $eventTitle . ' 당첨되어 ' . number_format($points) . '포인트를 지급받았습니다.'
            : number_format($points) . '포인트를 지급받았습니다.';

        Notification::create([
            'site_id' => $siteId,
            'user_id' => $userId,
            'type' => 'point_award',
            'title' => '포인트 지급 알림',
            'content' => $content,
            'link' => '#', // 포인트 지급 알림은 모달로 표시
            'data' => [
                'points' => $points,
                'event_title' => $eventTitle,
                'open_modal' => true, // 모달 열기 플래그
            ],
        ]);
    }

    /**
     * Create a notification for point exchange result.
     */
    public function createPointExchangeNotification($userId, $siteId, $productTitle, $status, $productId = null)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return;
        }
        
        // 상태 텍스트 매핑: completed -> 완료, rejected -> 거절, pending -> 보류
        $statusText = match($status) {
            'completed' => '완료',
            'rejected' => '거절',
            'pending' => '보류',
            default => '보류'
        };
        
        $link = route('point-exchange.index', ['site' => \App\Models\Site::find($siteId)->slug ?? 'default']);
        if ($productId) {
            $site = \App\Models\Site::find($siteId);
            $link = route('point-exchange.show', [
                'site' => $site->slug ?? 'default',
                'product' => $productId
            ]);
        }
        
        Notification::create([
            'site_id' => $siteId,
            'user_id' => $userId,
            'type' => 'point_exchange',
            'title' => '포인트 교환 알림',
            'content' => $productTitle . ' ' . $statusText . ' 처리되었습니다.',
            'link' => $link,
            'data' => [
                'product_title' => $productTitle,
                'status' => $status,
                'product_id' => $productId,
            ],
        ]);
    }

    /**
     * Create a notification for event application result.
     */
    public function createEventApplicationNotification($userId, $siteId, $postTitle, $status, $postId = null, $boardSlug = null, $productId = null)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return;
        }
        
        // 상태 텍스트 매핑: completed -> 완료, rejected -> 거절, pending -> 보류, approved -> 승인
        $statusText = match($status) {
            'completed' => '완료',
            'approved' => '승인',
            'rejected' => '거절',
            'pending' => '보류',
            default => '보류'
        };
        
        $link = null;
        if ($postId && $boardSlug) {
            $site = \App\Models\Site::find($siteId);
            $link = route('posts.show', [
                'site' => $site->slug ?? 'default',
                'boardSlug' => $boardSlug,
                'post' => $postId
            ]);
        } elseif ($productId) {
            $site = \App\Models\Site::find($siteId);
            $link = route('event-application.show', [
                'site' => $site->slug ?? 'default',
                'product' => $productId
            ]);
        }
        
        Notification::create([
            'site_id' => $siteId,
            'user_id' => $userId,
            'type' => 'event_application',
            'title' => '신청형 이벤트 알림',
            'content' => $postTitle . ' ' . $statusText . ' 처리되었습니다.',
            'link' => $link,
            'data' => [
                'post_title' => $postTitle,
                'status' => $status,
                'post_id' => $postId,
                'product_id' => $productId,
            ],
        ]);
    }

    /**
     * Create a notification for reply on user's post.
     */
    public function createReplyNotification(Post $replyPost)
    {
        // 테이블이 없으면 알림 생성 안함
        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return;
        }
        
        // 답글이 아니면 알림 생성 안함
        if (!$replyPost->reply_to) {
            return;
        }
        
        $parentPost = Post::find($replyPost->reply_to);
        if (!$parentPost || $parentPost->user_id === $replyPost->user_id) {
            // 자신의 답글이거나 원본 게시글이 없으면 알림 생성 안함
            return;
        }

        // 같은 게시글에 대한 최근 알림 확인 (중복 방지)
        $recentNotification = Notification::where('site_id', $parentPost->site_id)
            ->where('user_id', $parentPost->user_id)
            ->where('type', 'reply')
            ->where('data->post_id', $parentPost->id)
            ->where('created_at', '>', Carbon::now()->subMinutes(5))
            ->first();

        if ($recentNotification) {
            // 기존 알림 업데이트 (답글 수 증가)
            $data = $recentNotification->data ?? [];
            $replyCount = ($data['reply_count'] ?? 1) + 1;
            $replyUsers = $data['reply_users'] ?? [];
            
            $replyUserNickname = $replyPost->user->nickname ?? $replyPost->user->name;
            if (!in_array($replyUserNickname, $replyUsers)) {
                $replyUsers[] = $replyUserNickname;
            }
            
            $data['reply_count'] = $replyCount;
            $data['reply_users'] = $replyUsers;
            
            $replyText = count($replyUsers) > 1 
                ? $replyUsers[0] . '님외 ' . (count($replyUsers) - 1) . '명이 글에 답글을 남기셨습니다.'
                : $replyUsers[0] . '님이 글에 답글을 남기셨습니다.';
            
            $recentNotification->update([
                'content' => $replyText . "\n" . $parentPost->title,
                'data' => $data,
                'created_at' => Carbon::now(),
            ]);
        } else {
            // 새 알림 생성
            Notification::create([
                'site_id' => $parentPost->site_id,
                'user_id' => $parentPost->user_id,
                'type' => 'reply',
                'title' => '답글 알림',
                'content' => ($replyPost->user->nickname ?? $replyPost->user->name) . '님이 글에 답글을 남기셨습니다.' . "\n" . $parentPost->title,
                'link' => route('posts.show', [
                    'site' => $parentPost->site->slug,
                    'boardSlug' => $parentPost->board->slug,
                    'post' => $parentPost->id
                ]),
                'data' => [
                    'post_id' => $parentPost->id,
                    'reply_id' => $replyPost->id,
                    'reply_count' => 1,
                    'reply_users' => [$replyPost->user->nickname ?? $replyPost->user->name],
                ],
            ]);
        }
    }
}

