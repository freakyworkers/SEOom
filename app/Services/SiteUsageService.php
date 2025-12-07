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
            $mbToAdd = round($bytes / 1024 / 1024, 2);
            
            // Use atomic update to prevent race conditions
            DB::table('sites')
                ->where('id', $siteId)
                ->increment('traffic_used_mb', $mbToAdd);

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
     * 
     * @param Site $site
     * @return int Storage usage in MB
     */
    public function recalculateStorage(Site $site): int
    {
        $totalSize = 0;
        $basePath = storage_path('app/public');
        
        try {
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
            
        } catch (\Exception $e) {
            Log::error('Error calculating storage usage for site ' . $site->id . ': ' . $e->getMessage());
        }
        
        // Convert bytes to MB
        $storageUsedMB = (int) round($totalSize / 1024 / 1024);
        
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

