<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(['web', 'auth:master']);
    }

    /**
     * Display monitoring dashboard.
     */
    public function index()
    {
        // Site statistics
        $siteStats = [
            'total' => Site::count(),
            'active' => Site::where('status', 'active')->count(),
            'suspended' => Site::where('status', 'suspended')->count(),
        ];

        // Database size (approximate)
        $dbSize = $this->getDatabaseSize();

        // Top sites by users
        $topSitesByUsers = Site::withCount('users')
            ->orderBy('users_count', 'desc')
            ->limit(10)
            ->get();

        // Top sites by posts
        $topSitesByPosts = Site::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();

        return view('master.monitoring', compact('siteStats', 'dbSize', 'topSitesByUsers', 'topSitesByPosts'));
    }

    /**
     * Get database size.
     */
    protected function getDatabaseSize()
    {
        try {
            $database = DB::connection()->getDatabaseName();
            $size = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                               FROM information_schema.tables 
                               WHERE table_schema = ?", [$database]);
            
            return $size[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}

