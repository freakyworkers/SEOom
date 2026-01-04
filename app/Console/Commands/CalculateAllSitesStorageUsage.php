<?php

namespace App\Console\Commands;

use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateAllSitesStorageUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sites:calculate-storage-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and update storage and traffic usage for all sites';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Calculating storage and traffic usage for all sites...');
        
        $sites = Site::where('is_master_site', false)->get();
        $total = $sites->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $updated = 0;
        
        foreach ($sites as $site) {
            try {
                // Calculate storage usage
                $storageUsedMB = $this->calculateStorageUsage($site);
                
                // Update site
                $site->update([
                    'storage_used_mb' => $storageUsedMB,
                ]);
                
                $updated++;
            } catch (\Exception $e) {
                Log::error('Error calculating storage usage for site ' . $site->id . ': ' . $e->getMessage());
                $this->error("\nError for site {$site->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Successfully updated {$updated} out of {$total} sites.");
        
        return Command::SUCCESS;
    }

    /**
     * Calculate actual storage usage for a site (MB).
     */
    protected function calculateStorageUsage(Site $site): int
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
            
            // 7. Attachments for posts of this site (파일 시스템에서도 확인)
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
        return (int) round($totalSize / 1024 / 1024);
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
}






