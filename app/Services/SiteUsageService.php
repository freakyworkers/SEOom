<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SiteUsageService
{
    /**
     * Update traffic usage for a site (in MB)
     * 
     * @param int $siteId
     * @param int $bytes
     * @return void
     */
    public function addTraffic(int $siteId, int $bytes): void
    {
        try {
            $site = Site::find($siteId);
            if (!$site) {
                return;
            }

            // Check if traffic should be reset (new month)
            $this->checkAndResetTraffic($site);

            // Convert bytes to MB and add to current usage
            // Use 4 decimal places for more accurate tracking of small traffic
            $mbToAdd = round($bytes / 1024 / 1024, 4);
            
            // Only update if mbToAdd is greater than 0 (avoid unnecessary updates)
            if ($mbToAdd > 0) {
                // Use atomic update to prevent race conditions
                DB::table('sites')
                    ->where('id', $siteId)
                    ->increment('traffic_used_mb', $mbToAdd);
            }

            Log::debug('Traffic updated', [
                'site_id' => $siteId,
                'bytes' => $bytes,
                'mb_added' => $mbToAdd,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update traffic', [
                'site_id' => $siteId,
                'bytes' => $bytes,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update storage usage for a site (in MB)
     * 
     * @param int $siteId
     * @param int $bytes Positive for add, negative for subtract
     * @return void
     */
    public function updateStorage(int $siteId, int $bytes): void
    {
        try {
            $site = Site::find($siteId);
            if (!$site) {
                return;
            }

            // Convert bytes to MB
            $mbToChange = round($bytes / 1024 / 1024, 2);
            
            if ($mbToChange > 0) {
                // Add storage
                DB::table('sites')
                    ->where('id', $siteId)
                    ->increment('storage_used_mb', $mbToChange);
            } elseif ($mbToChange < 0) {
                // Subtract storage (ensure it doesn't go below 0)
                DB::table('sites')
                    ->where('id', $siteId)
                    ->where('storage_used_mb', '>=', abs($mbToChange))
                    ->decrement('storage_used_mb', abs($mbToChange));
            }

            Log::debug('Storage updated', [
                'site_id' => $siteId,
                'bytes' => $bytes,
                'mb_changed' => $mbToChange,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update storage', [
                'site_id' => $siteId,
                'bytes' => $bytes,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Recalculate and update storage usage for a site
     * Includes both file storage and database storage
     * 
     * @param Site $site
     * @return float Storage usage in MB
     */
    public function recalculateStorage(Site $site): float
    {
        $totalSize = 0;
        $basePath = storage_path('app/public');
        
        try {
            // ===== FILE STORAGE =====
            
            // 1. Post Attachments - 데이터베이스에서 파일 크기 합계
            $postAttachmentsSize = \App\Models\PostAttachment::whereHas('post', function($query) use ($site) {
                $query->where('site_id', $site->id);
            })->sum('file_size');
            $totalSize += $postAttachmentsSize;
            
            // 2. Banners - 이미지 파일 크기 계산
            $banners = \App\Models\Banner::where('site_id', $site->id)
                ->whereNotNull('image_path')
                ->get();
            foreach ($banners as $banner) {
                $filePath = $basePath . '/' . $banner->image_path;
                if (file_exists($filePath) && is_file($filePath)) {
                    $totalSize += filesize($filePath);
                }
            }
            
            // 3. Popups - 이미지 파일 크기 계산
            $popups = \App\Models\Popup::where('site_id', $site->id)
                ->whereNotNull('image_path')
                ->get();
            foreach ($popups as $popup) {
                $filePath = $basePath . '/' . $popup->image_path;
                if (file_exists($filePath) && is_file($filePath)) {
                    $totalSize += filesize($filePath);
                }
            }
            
            // 4. User Avatars - 해당 사이트의 사용자 아바타 크기 계산
            $siteUserIds = $site->users()->pluck('id')->toArray();
            if (!empty($siteUserIds)) {
                foreach ($siteUserIds as $userId) {
                    $avatarPath = $basePath . '/avatars/' . $userId;
                    if (is_dir($avatarPath)) {
                        $totalSize += $this->getDirectorySize($avatarPath);
                    }
                }
            }
            
            // 5. Site-specific upload directories
            $siteUploadPath = $basePath . '/uploads/sites/' . $site->id;
            if (is_dir($siteUploadPath)) {
                $totalSize += $this->getDirectorySize($siteUploadPath);
            }
            
            // 6. Banner directories for this site
            $bannerPath = $basePath . '/banners/' . $site->id;
            if (is_dir($bannerPath)) {
                $totalSize += $this->getDirectorySize($bannerPath);
            }
            
            // 7. Attachments for posts of this site
            $sitePostIds = $site->posts()->pluck('id')->toArray();
            if (!empty($sitePostIds)) {
                foreach ($sitePostIds as $postId) {
                    $attachmentPath = $basePath . '/attachments/' . $postId;
                    if (is_dir($attachmentPath)) {
                        $totalSize += $this->getDirectorySize($attachmentPath);
                    }
                }
            }
            
            // ===== DATABASE STORAGE =====
            
            // 8. Posts - 게시글 내용 (title, content, code, bookmark_items 등)
            $postsSize = DB::table('posts')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(title), 0) + 
                    COALESCE(LENGTH(content), 0) + 
                    COALESCE(LENGTH(code), 0) + 
                    COALESCE(LENGTH(bookmark_items), 0) + 
                    COALESCE(LENGTH(thumbnail_path), 0) +
                    COALESCE(LENGTH(external_url), 0) +
                    COALESCE(LENGTH(link), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $postsSize;
            
            // 9. Boards - 게시판 설정 데이터
            $boardsSize = DB::table('boards')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(name), 0) + 
                    COALESCE(LENGTH(description), 0) + 
                    COALESCE(LENGTH(post_template), 0) + 
                    COALESCE(LENGTH(footer_content), 0) + 
                    COALESCE(LENGTH(banned_words), 0) + 
                    COALESCE(LENGTH(qa_statuses), 0) +
                    COALESCE(LENGTH(header_image_path), 0) +
                    COALESCE(LENGTH(seo_title), 0) +
                    COALESCE(LENGTH(seo_description), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $boardsSize;
            
            // 10. Comments - 댓글 내용
            $commentsSize = DB::table('comments')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(COALESCE(LENGTH(content), 0)) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $commentsSize;
            
            // 11. Custom Codes - 커스텀 코드
            $customCodesSize = DB::table('custom_codes')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(COALESCE(LENGTH(code), 0)) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $customCodesSize;
            
            // 12. Custom Pages - 커스텀 페이지 내용
            $customPagesSize = DB::table('custom_pages')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(title), 0) + 
                    COALESCE(LENGTH(content), 0) + 
                    COALESCE(LENGTH(slug), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $customPagesSize;
            
            // 13. Site Settings - 사이트 설정
            $siteSettingsSize = DB::table('site_settings')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(key), 0) + 
                    COALESCE(LENGTH(value), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $siteSettingsSize;
            
            // 14. Menus - 메뉴 설정
            $menusSize = DB::table('menus')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(name), 0) + 
                    COALESCE(LENGTH(url), 0) + 
                    COALESCE(LENGTH(icon), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $menusSize;
            
            // 15. Sidebar Widgets - 사이드바 위젯 설정
            $sidebarWidgetsSize = DB::table('sidebar_widgets')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(type), 0) + 
                    COALESCE(LENGTH(title), 0) + 
                    COALESCE(LENGTH(content), 0) + 
                    COALESCE(LENGTH(config), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $sidebarWidgetsSize;
            
            // 16. Main Widgets - 메인 위젯 설정
            $mainWidgetsSize = DB::table('main_widgets')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(type), 0) + 
                    COALESCE(LENGTH(title), 0) + 
                    COALESCE(LENGTH(content), 0) + 
                    COALESCE(LENGTH(config), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $mainWidgetsSize;
            
            // 17. Messages - 쪽지 내용
            $messagesSize = DB::table('messages')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(subject), 0) + 
                    COALESCE(LENGTH(content), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $messagesSize;
            
            // 18. Notifications - 알림 데이터
            $notificationsSize = DB::table('notifications')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(type), 0) + 
                    COALESCE(LENGTH(data), 0) + 
                    COALESCE(LENGTH(read_at), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $notificationsSize;
            
            // 19. Users - 사용자 프로필 데이터 (사이트별)
            $usersSize = DB::table('users')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(name), 0) + 
                    COALESCE(LENGTH(email), 0) + 
                    COALESCE(LENGTH(bio), 0) + 
                    COALESCE(LENGTH(avatar_path), 0) +
                    COALESCE(LENGTH(social_id), 0) +
                    COALESCE(LENGTH(social_provider), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $usersSize;
            
            // 20. Contact Forms & Submissions - 컨텍트폼 데이터
            $contactFormsSize = DB::table('contact_forms')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(name), 0) + 
                    COALESCE(LENGTH(config), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $contactFormsSize;
            
            $contactSubmissionsSize = DB::table('contact_form_submissions')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(data), 0) + 
                    COALESCE(LENGTH(ip_address), 0) + 
                    COALESCE(LENGTH(user_agent), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $contactSubmissionsSize;
            
            // 21. Maps - 지도 설정
            $mapsSize = DB::table('maps')
                ->where('site_id', $site->id)
                ->selectRaw('SUM(
                    COALESCE(LENGTH(name), 0) + 
                    COALESCE(LENGTH(address), 0) + 
                    COALESCE(LENGTH(config), 0)
                ) as total_size')
                ->value('total_size') ?? 0;
            $totalSize += $mapsSize;
            
        } catch (\Exception $e) {
            Log::error('Error calculating storage usage for site ' . $site->id . ': ' . $e->getMessage());
        }
        
        // Convert bytes to MB (keep 4 decimal places for accuracy)
        $storageUsedMB = round($totalSize / 1024 / 1024, 4);
        
        // Update site
        $site->update(['storage_used_mb' => $storageUsedMB]);
        
        return $storageUsedMB;
    }

    /**
     * Get directory size recursively (bytes).
     */
    protected function getDirectorySize(string $directory): int
    {
        $size = 0;
        
        if (!is_dir($directory)) {
            return 0;
        }
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            Log::error('Error calculating directory size for ' . $directory . ': ' . $e->getMessage());
        }
        
        return $size;
    }

    /**
     * Check and reset traffic if it's a new month
     * 
     * @param Site $site
     * @return void
     */
    protected function checkAndResetTraffic(Site $site): void
    {
        $currentMonth = now()->startOfMonth()->toDateString();
        
        // Get traffic_reset_date as string for comparison
        $resetDate = null;
        if ($site->traffic_reset_date) {
            // If it's a Carbon instance, format it; if it's a string, use it directly
            if (is_object($site->traffic_reset_date) && method_exists($site->traffic_reset_date, 'format')) {
                $resetDate = $site->traffic_reset_date->format('Y-m-d');
            } else {
                $resetDate = is_string($site->traffic_reset_date) ? $site->traffic_reset_date : (string) $site->traffic_reset_date;
            }
        }
        
        // If traffic_reset_date is null or different from current month, reset
        if (!$resetDate || $resetDate !== $currentMonth) {
            $site->update([
                'traffic_used_mb' => 0,
                'traffic_reset_date' => $currentMonth,
            ]);
            
            Log::info('Traffic reset for site', [
                'site_id' => $site->id,
                'reset_date' => $currentMonth,
            ]);
        }
    }
}

