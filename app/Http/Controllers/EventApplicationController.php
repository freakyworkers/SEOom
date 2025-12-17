<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\EventApplicationSetting;
use App\Models\EventApplicationProduct;
use App\Models\EventApplicationSubmission;
use Illuminate\Http\Request;

class EventApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('index');
    }

    /**
     * Display event application index page.
     */
    public function index(Site $site)
    {
        $setting = EventApplicationSetting::getForSite($site->id);
        
        $query = EventApplicationProduct::where('site_id', $site->id);
        
        // Apply random order if enabled
        if ($setting->random_order) {
            $query->inRandomOrder();
        } else {
            $query->orderBy('order')->orderBy('created_at');
        }
        
        // Get products per page setting
        $perPage = $setting->products_per_page ?? 12;
        
        // Paginate products
        $products = $query->paginate($perPage)->withQueryString();

        return view('event-application.index', compact('site', 'setting', 'products'));
    }

    /**
     * Show application form for a product.
     */
    public function show(Site $site, EventApplicationProduct $product)
    {
        if ($product->site_id !== $site->id) {
            abort(404);
        }

        $setting = EventApplicationSetting::getForSite($site->id);
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login', ['site' => $site->slug])
                ->with('error', '로그인이 필요합니다.');
        }

        // Get statistics
        $completedCount = EventApplicationSubmission::where('site_id', $site->id)
            ->where('product_id', $product->id)
            ->where('status', 'completed')
            ->count();
        $rejectedCount = EventApplicationSubmission::where('site_id', $site->id)
            ->where('product_id', $product->id)
            ->where('status', 'rejected')
            ->count();

        // Get all submissions for this product
        $allSubmissions = EventApplicationSubmission::where('site_id', $site->id)
            ->where('product_id', $product->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get requirements with board names
        $requirements = [];
        if ($setting->requirements) {
            foreach ($setting->requirements as $requirement) {
                $boardId = $requirement['board_id'] ?? null;
                if ($boardId) {
                    $board = \App\Models\Board::find($boardId);
                    if ($board) {
                        // Count user's posts in the board that meet the character requirement
                        $minCharacters = $requirement['min_characters'] ?? 0;
                        $userPostCount = \App\Models\Post::where('site_id', $site->id)
                            ->where('board_id', $boardId)
                            ->where('user_id', $user->id)
                            ->whereRaw('LENGTH(content) >= ?', [$minCharacters])
                            ->count();

                        $requirements[] = [
                            'board_id' => $boardId,
                            'board_name' => $board->name,
                            'required_count' => $requirement['post_count'] ?? 0,
                            'min_characters' => $minCharacters,
                            'current_count' => $userPostCount,
                        ];
                    }
                }
            }
        }

        return view('event-application.show', compact('site', 'product', 'setting', 'user', 'completedCount', 'rejectedCount', 'allSubmissions', 'requirements'));
    }

    /**
     * Store a new application submission.
     */
    public function store(Site $site, EventApplicationProduct $product, Request $request)
    {
        if ($product->site_id !== $site->id) {
            abort(404);
        }

        $user = auth()->user();
        $setting = EventApplicationSetting::getForSite($site->id);

        // Check requirements
        if ($setting->requirements && count($setting->requirements) > 0) {
            $failedRequirements = [];
            foreach ($setting->requirements as $requirement) {
                $boardId = $requirement['board_id'] ?? null;
                $requiredPostCount = $requirement['post_count'] ?? 0;
                $minCharacters = $requirement['min_characters'] ?? 0;

                if ($boardId) {
                    // Count user's posts in the board that meet the character requirement
                    $userPostCount = \App\Models\Post::where('site_id', $site->id)
                        ->where('board_id', $boardId)
                        ->where('user_id', $user->id)
                        ->whereRaw('LENGTH(content) >= ?', [$minCharacters])
                        ->count();

                    if ($userPostCount < $requiredPostCount) {
                        $board = \App\Models\Board::find($boardId);
                        $failedRequirements[] = [
                            'board_name' => $board ? $board->name : '알 수 없는 게시판',
                            'required_count' => $requiredPostCount,
                            'min_characters' => $minCharacters,
                            'current_count' => $userPostCount,
                        ];
                    }
                }
            }

            if (count($failedRequirements) > 0) {
                $errorMessages = [];
                foreach ($failedRequirements as $req) {
                    $errorMessages[] = "{$req['board_name']}에 게시글 {$req['min_characters']}자 이상 {$req['required_count']}개의 게시글을 작성해야 신청 가능합니다.";
                }
                return back()->withErrors(['requirements' => $errorMessages])->withInput();
            }
        }

        // Validate form fields
        $formData = [];
        if ($setting->form_fields) {
            foreach ($setting->form_fields as $field) {
                $key = str_replace(' ', '_', strtolower($field['title']));
                $request->validate([
                    $key => 'required|string|max:255',
                ]);
                $formData[$field['title']] = $request->input($key);
            }
        }

        // Create submission
        $submission = EventApplicationSubmission::create([
            'site_id' => $site->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending',
            'form_data' => $formData,
        ]);

        // Update product statistics
        $product->updateStatistics();

        return redirect()->route('event-application.show', ['site' => $site->slug, 'product' => $product->id])
            ->with('success', '신청이 완료되었습니다.');
    }

    /**
     * Cancel a submission.
     */
    public function cancel(Site $site, EventApplicationSubmission $submission)
    {
        if ($submission->site_id !== $site->id || $submission->user_id !== auth()->id()) {
            abort(403);
        }

        if ($submission->status !== 'pending') {
            return back()->withErrors(['error' => '취소할 수 없는 신청입니다.']);
        }

        $submission->updateStatus('cancelled');

        return redirect()->route('event-application.show', ['site' => $site->slug, 'product' => $submission->product_id])
            ->with('success', '신청이 취소되었습니다.');
    }
}





