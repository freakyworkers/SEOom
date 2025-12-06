<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;

class PostPolicy
{
    /**
     * Determine if the user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->canManage();
    }

    /**
     * Determine if the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        $board = $post->board;
        $deletePermission = $board->delete_permission ?? 'author';
        
        // 관리자 권한 설정인 경우 관리자만 삭제 가능
        if ($deletePermission === 'admin') {
            return $user->canManage();
        }
        
        // 작성자 본인 권한 설정인 경우 작성자 본인 또는 관리자만 삭제 가능
        return $user->id === $post->user_id || $user->canManage();
    }
}



