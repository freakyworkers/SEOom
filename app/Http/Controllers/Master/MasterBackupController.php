<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class MasterBackupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['web', 'auth:master']);
    }

    /**
     * Display backup management.
     */
    public function index()
    {
        // Get backup files
        $backups = $this->getBackupFiles();
        
        // Get auto backup setting
        $masterSite = Site::getMasterSite();
        $autoBackupEnabled = $masterSite ? $masterSite->getSetting('auto_backup_enabled', '1') : '1';

        return view('master.backup', compact('backups', 'autoBackupEnabled'));
    }
    
    /**
     * Toggle auto backup.
     */
    public function toggleAutoBackup(Request $request)
    {
        $masterSite = Site::getMasterSite();
        
        if (!$masterSite) {
            return back()->with('error', '마스터 사이트를 찾을 수 없습니다.');
        }
        
        $enabled = $request->input('enabled', '0');
        $masterSite->setSetting('auto_backup_enabled', $enabled);
        
        $status = $enabled === '1' ? '활성화' : '비활성화';
        return back()->with('success', "자동 백업이 {$status}되었습니다.");
    }

    /**
     * Create a backup.
     */
    public function create(Request $request)
    {
        $siteId = $request->input('site_id');

        try {
            if ($siteId) {
                // Backup specific site
                Artisan::call('backup:site', ['site_id' => $siteId]);
            } else {
                // Backup all
                Artisan::call('backup:run');
            }

            return back()->with('success', '백업이 생성되었습니다.');
        } catch (\Exception $e) {
            return back()->with('error', '백업 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup.
     */
    public function download($filename)
    {
        $path = 'backups/' . $filename;
        
        if (Storage::exists($path)) {
            return Storage::download($path);
        }

        return back()->with('error', '백업 파일을 찾을 수 없습니다.');
    }

    /**
     * Delete a backup.
     */
    public function destroy($filename)
    {
        $path = 'backups/' . $filename;
        
        if (Storage::exists($path)) {
            Storage::delete($path);
            return back()->with('success', '백업 파일이 삭제되었습니다.');
        }

        return back()->with('error', '백업 파일을 찾을 수 없습니다.');
    }

    /**
     * Get backup files.
     */
    protected function getBackupFiles()
    {
        $files = Storage::files('backups');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => Storage::size($file),
                'created_at' => Storage::lastModified($file),
            ];
        }

        // Sort by created_at desc
        usort($backups, function($a, $b) {
            return $b['created_at'] - $a['created_at'];
        });

        return $backups;
    }
}

