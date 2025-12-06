<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Site;
use Illuminate\Support\Str;

class BoardService
{
    /**
     * Get all boards for a site.
     */
    public function getBoardsBySite($siteId)
    {
        return Board::where('site_id', $siteId)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get a board by slug.
     */
    public function getBoardBySlug($siteId, $slug)
    {
        return Board::where('site_id', $siteId)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Create a new board.
     */
    public function create(array $data, $siteId)
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $boardData = [
            'site_id' => $siteId,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'type' => $data['type'] ?? Board::TYPE_GENERAL,
            'event_display_type' => $data['event_display_type'] ?? null,
            'description' => $data['description'] ?? null,
            'header_image_path' => $data['header_image_path'] ?? null,
            'order' => $data['order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'max_posts_per_day' => $data['max_posts_per_day'] ?? 0,
            'posts_per_page' => $data['posts_per_page'] ?? 20,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'read_permission' => $data['read_permission'] ?? 'guest',
            'write_permission' => $data['write_permission'] ?? 'user',
            'delete_permission' => $data['delete_permission'] ?? 'author',
            'comment_permission' => $data['comment_permission'] ?? 'user',
            'comment_delete_permission' => $data['comment_delete_permission'] ?? 'author',
            'read_points' => $data['read_points'] ?? 0,
            'write_points' => $data['write_points'] ?? 0,
            'delete_points' => $data['delete_points'] ?? 0,
            'comment_points' => $data['comment_points'] ?? 0,
            'comment_delete_points' => $data['comment_delete_points'] ?? 0,
        ];
        
        // 북마크 게시판인 경우 random_order 처리
        if (($data['type'] ?? Board::TYPE_GENERAL) === 'bookmark') {
            $randomOrder = $data['random_order'] ?? false;
            $boardData['random_order'] = ($randomOrder == '1' || $randomOrder === true || $randomOrder === 'true');
        }
        
        // allow_multiple_topics 처리
        if (isset($data['allow_multiple_topics'])) {
            $allowMultiple = $data['allow_multiple_topics'];
            $boardData['allow_multiple_topics'] = ($allowMultiple == '1' || $allowMultiple === true || $allowMultiple === 'true');
        } else {
            $boardData['allow_multiple_topics'] = false;
        }
        
        // remove_links 처리
        if (isset($data['remove_links'])) {
            $removeLinks = $data['remove_links'];
            $boardData['remove_links'] = ($removeLinks == '1' || $removeLinks === true || $removeLinks === 'true');
        } else {
            $boardData['remove_links'] = false;
        }
        
        return Board::create($boardData);
    }

    /**
     * Update a board.
     */
    public function update(Board $board, array $data)
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // 북마크 게시판인 경우 random_order 처리
        if ($board->type === 'bookmark' && isset($data['random_order'])) {
            $randomOrder = $data['random_order'];
            $data['random_order'] = ($randomOrder == '1' || $randomOrder === true || $randomOrder === 'true');
        }
        
        // allow_multiple_topics 처리
        if (isset($data['allow_multiple_topics'])) {
            $allowMultiple = $data['allow_multiple_topics'];
            $data['allow_multiple_topics'] = ($allowMultiple == '1' || $allowMultiple === true || $allowMultiple === 'true');
        }
        
        // remove_links 처리
        if (isset($data['remove_links'])) {
            $removeLinks = $data['remove_links'];
            $data['remove_links'] = ($removeLinks == '1' || $removeLinks === true || $removeLinks === 'true');
        }
        
        // enable_likes 처리
        if (isset($data['enable_likes'])) {
            $enableLikes = $data['enable_likes'];
            $data['enable_likes'] = ($enableLikes == '1' || $enableLikes === true || $enableLikes === 'true');
        }

        $board->update($data);
        return $board;
    }

    /**
     * Delete a board.
     */
    public function delete(Board $board)
    {
        return $board->delete();
    }
}


