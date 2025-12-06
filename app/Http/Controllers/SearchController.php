<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Models\Site;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Show search form and results.
     */
    public function index(Request $request, Site $site)
    {
        $keyword = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, posts, users, boards
        $boardId = $request->get('board_id');
        $sortBy = $request->get('sort', 'latest'); // latest, views, comments
        $dateFilter = $request->get('date'); // today, week, month, year

        $results = null;
        $posts = null;
        $users = null;
        $boards = null;

        if ($keyword) {
            switch ($type) {
                case 'posts':
                    $posts = $this->searchService->searchPosts($site->id, $keyword, $boardId, $sortBy, $dateFilter);
                    break;
                case 'users':
                    $users = $this->searchService->searchUsers($site->id, $keyword);
                    break;
                case 'boards':
                    $boards = $this->searchService->searchBoards($site->id, $keyword);
                    break;
                default:
                    $results = $this->searchService->globalSearch($site->id, $keyword, $sortBy, $dateFilter);
                    $posts = $results['posts'];
                    $users = $results['users'];
                    $boards = $results['boards'];
                    break;
            }
        }

        // Get all boards for filter
        $allBoards = \App\Models\Board::where('site_id', $site->id)
            ->orderBy('name')
            ->get();

        return view('search.index', compact('site', 'keyword', 'type', 'posts', 'users', 'boards', 'allBoards', 'boardId', 'sortBy', 'dateFilter'));
    }
}

