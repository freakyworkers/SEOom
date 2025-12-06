<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\SiteProvisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MasterMasterSiteController extends Controller
{
    protected $provisionService;

    public function __construct(SiteProvisionService $provisionService)
    {
        $this->middleware(['web', 'auth:master']);
        $this->provisionService = $provisionService;
    }

    /**
     * Display a listing of master sites.
     */
    public function index(Request $request)
    {
        // 검색이나 필터가 있는 경우에는 목록 페이지 표시
        if ($request->has('status') && $request->status !== '' || 
            ($request->has('search') && $request->search !== '')) {
            $query = Site::where('is_master_site', true);

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Search
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('domain', 'like', "%{$search}%");
                });
            }

            $sites = $query->orderBy('created_at', 'desc')->paginate(20);

            return view('master.master-sites.index', compact('sites'));
        }

        // 기본적으로 메인 마스터 사이트(루트 도메인)로 리다이렉트
        $masterSite = Site::getMasterSite();
        
        if ($masterSite) {
            return redirect()->route('master.master-sites.show', $masterSite->id);
        }

        // 메인 마스터 사이트가 없으면 목록 페이지 표시
        $sites = Site::where('is_master_site', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('master.master-sites.index', compact('sites'));
    }

    /**
     * Show the form for creating a new master site.
     */
    public function create()
    {
        return view('master.master-sites.create');
    }

    /**
     * Store a newly created master site.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sites,slug',
            'domain' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // 마스터 사이트로 설정 및 요금제는 premium으로 자동 설정 (모든 기능 사용 가능)
        $data['is_master_site'] = true;
        $data['plan'] = 'premium'; // 마스터 사이트는 항상 premium으로 설정

        $site = $this->provisionService->provision($data, true); // 두 번째 파라미터로 마스터 사이트임을 전달

        return redirect()->route('master.master-sites.show', $site->id)
            ->with('success', '마스터 사이트가 성공적으로 생성되었습니다.');
    }

    /**
     * Display the specified master site.
     */
    public function show(Site $site)
    {
        if (!$site->is_master_site) {
            abort(404);
        }

        $site->load(['users', 'boards', 'posts']);
        
        $stats = [
            'users' => $site->users()->count(),
            'boards' => $site->boards()->count(),
            'posts' => $site->posts()->count(),
            'comments' => $site->comments()->count(),
        ];

        return view('master.master-sites.show', compact('site', 'stats'));
    }

    /**
     * Show the form for editing the specified master site.
     */
    public function edit(Site $site)
    {
        if (!$site->is_master_site) {
            abort(404);
        }

        return view('master.master-sites.edit', compact('site'));
    }

    /**
     * Update the specified master site.
     */
    public function update(Request $request, Site $site)
    {
        if (!$site->is_master_site) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sites,slug,' . $site->id,
            'domain' => 'nullable|string|max:255',
            'status' => 'required|in:active,suspended,deleted',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 마스터 사이트는 요금제를 변경할 수 없음 (항상 premium)
        $updateData = $request->only(['name', 'slug', 'domain', 'status']);
        $updateData['plan'] = 'premium'; // 마스터 사이트는 항상 premium
        $site->update($updateData);

        return redirect()->route('master.master-sites.show', $site->id)
            ->with('success', '마스터 사이트가 성공적으로 수정되었습니다.');
    }

    /**
     * Remove the specified master site.
     */
    public function destroy(Site $site)
    {
        if (!$site->is_master_site) {
            abort(404);
        }

        $site->delete();

        return redirect()->route('master.master-sites.index')
            ->with('success', '마스터 사이트가 성공적으로 삭제되었습니다.');
    }

    /**
     * Suspend the specified master site.
     */
    public function suspend(Site $site)
    {
        if (!$site->is_master_site) {
            abort(404);
        }

        $site->update(['status' => 'suspended']);

        return back()->with('success', '마스터 사이트가 정지되었습니다.');
    }

    /**
     * Activate the specified master site.
     */
    public function activate(Site $site)
    {
        if (!$site->is_master_site) {
            abort(404);
        }

        $site->update(['status' => 'active']);

        return back()->with('success', '마스터 사이트가 활성화되었습니다.');
    }
}

