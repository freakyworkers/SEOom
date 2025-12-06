<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use App\Models\Board;
use App\Models\Post;
use Illuminate\Http\Request;

class MasterDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['web', 'auth:master']);
    }

    /**
     * Display master dashboard.
     */
    public function index()
    {
        $stats = [
            'total_sites' => Site::count(),
            'active_sites' => Site::where('status', 'active')->count(),
            'suspended_sites' => Site::where('status', 'suspended')->count(),
            'total_users' => User::count(),
            'total_boards' => Board::count(),
            'total_posts' => Post::count(),
        ];

        $recentSites = Site::orderBy('created_at', 'desc')->limit(5)->get();
        $recentUsers = User::with('site')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('master.dashboard', compact('stats', 'recentSites', 'recentUsers'));
    }
}

