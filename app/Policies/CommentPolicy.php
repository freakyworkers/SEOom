<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;

class CommentPolicy
{
    /**
     * Determine if the user can update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id || $user->canManage();
    }

    /**
     * Determine if the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        $board = $comment->post->board;
        $deletePermission = $board->comment_delete_permission ?? 'author';
        
        // 관리자 권한 설정인 경우 관리자만 삭제 가능
        if ($deletePermission === 'admin') {
            return $user->canManage();
        }
        
        // 작성자 본인 권한 설정인 경우 작성자 본인 또는 관리자만 삭제 가능
        return $user->id === $comment->user_id || $user->canManage();
    }
}



