<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\Site;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class CommentService
{
    /**
     * Get comments for a post.
     */
    public function getCommentsByPost($postId)
    {
        return Comment::where('post_id', $postId)
            ->with(['user', 'replies.user'])
            ->topLevel()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get a comment by ID.
     */
    public function getComment($commentId)
    {
        return Comment::with(['user', 'post', 'parent', 'replies.user'])
            ->findOrFail($commentId);
    }

    /**
     * Create a new comment.
     */
    public function create(array $data, $userId, $siteId)
    {
        $comment = Comment::create([
            'site_id' => $siteId,
            'post_id' => $data['post_id'],
            'user_id' => $userId,
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $data['content'],
        ]);

        // 포인트 지급 (댓글 쓰기)
        $post = Post::find($data['post_id']);
        if ($post) {
            $board = $post->board;
            if ($board && $board->comment_points != 0) {
                $user = User::find($userId);
                if ($user) {
                    $user->addPoints($board->comment_points);
                }
            }
            
            // 알림 생성 (게시글 작성자에게)
            if ($post->user_id != $userId) {
                $notificationService = new NotificationService();
                $notificationService->createCommentNotification($comment);
            }
            
            // 관리자에게 새 댓글 알림 메일 발송
            $comment->load(['post.board', 'user']);
            $this->sendNewCommentNotification($comment);
        }

        return $comment;
    }

    /**
     * Send admin notification email for new comment.
     */
    private function sendNewCommentNotification(Comment $comment)
    {
        $site = Site::find($comment->site_id);
        if (!$site) {
            return;
        }

        // 알림 설정 확인
        if (!$site->getSetting('notify_new_comment', false)) {
            return;
        }

        $adminEmail = $site->getSetting('admin_notification_email', '');
        if (!$adminEmail) {
            return;
        }

        // 게시글의 게시판 확인
        $post = $comment->post;
        if (!$post || !$post->board) {
            return;
        }

        // 선택된 게시판 확인
        $notifyCommentBoardsRaw = $site->getSetting('notify_comment_boards', '[]');
        $notifyCommentBoards = is_array($notifyCommentBoardsRaw) ? $notifyCommentBoardsRaw : (json_decode($notifyCommentBoardsRaw, true) ?? []);
        if (!empty($notifyCommentBoards) && !in_array($post->board_id, $notifyCommentBoards)) {
            return;
        }

        // 메일 설정 가져오기
        $mailer = $site->getSetting('mail_mailer', 'smtp');
        if (empty($mailer)) {
            $mailer = 'smtp';
        }

        $mailUsername = $site->getSetting('mail_username', '');
        $mailConfig = [
            'mailer' => $mailer,
            'host' => $site->getSetting('mail_host', 'smtp.gmail.com'),
            'port' => (int)$site->getSetting('mail_port', '587'),
            'username' => $mailUsername,
            'password' => $site->getSetting('mail_password', ''),
            'encryption' => $site->getSetting('mail_encryption', 'tls'),
            'from' => [
                'address' => $mailUsername,
                'name' => $site->getSetting('mail_from_name', $site->name),
            ],
        ];

        // Config::set을 사용하여 메일 설정 변경
        Config::set('mail.default', $mailConfig['mailer']);
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $mailConfig['host']);
        Config::set('mail.mailers.smtp.port', $mailConfig['port']);
        Config::set('mail.mailers.smtp.encryption', $mailConfig['encryption']);
        Config::set('mail.mailers.smtp.username', $mailConfig['username']);
        Config::set('mail.mailers.smtp.password', $mailConfig['password']);
        Config::set('mail.mailers.smtp.timeout', null);
        Config::set('mail.mailers.smtp.auth_mode', null);
        Config::set('mail.from.address', $mailConfig['from']['address']);
        Config::set('mail.from.name', $mailConfig['from']['name']);

        // 메일러 재설정
        app('mail.manager')->forgetMailers();

        try {
            Mail::to($adminEmail)->send(new \App\Mail\NewCommentNotificationMail($site, $comment));
        } catch (\Exception $e) {
            \Log::error('New comment notification mail failed: ' . $e->getMessage());
        }
    }

    /**
     * Update a comment.
     */
    public function update(Comment $comment, array $data)
    {
        $comment->update($data);
        return $comment;
    }

    /**
     * Delete a comment.
     */
    public function delete(Comment $comment)
    {
        // 포인트 차감 (댓글 삭제)
        $post = $comment->post;
        if ($post) {
            $board = $post->board;
            if ($board && $board->comment_delete_points != 0) {
                $user = $comment->user;
                if ($user) {
                    $user->addPoints($board->comment_delete_points); // 음수 값이므로 차감됨
                }
            }
        }

        return $comment->delete();
    }
}


