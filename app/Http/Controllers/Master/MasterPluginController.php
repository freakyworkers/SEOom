<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MasterPluginController extends Controller
{
    public function __construct()
    {
        $this->middleware(['web', 'auth:master']);
    }

    /**
     * Display a listing of plugins.
     */
    public function index()
    {
        $plugins = Plugin::orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // 각 플러그인의 구매 수 계산
        $plugins->each(function ($plugin) {
            $plugin->purchases_count = $plugin->purchases()->where('status', 'active')->count();
        });

        return view('master.plugins.index', compact('plugins'));
    }

    /**
     * Show the form for creating a new plugin.
     */
    public function create()
    {
        return view('master.plugins.create');
    }

    /**
     * Store a newly created plugin.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plugins,slug',
            'description' => 'nullable|string',
            'billing_type' => 'required|in:free,one_time,monthly',
            'price' => 'nullable|numeric|min:0',
            'one_time_price' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'name', 'slug', 'description', 'billing_type', 'price', 'one_time_price',
            'features', 'sort_order', 'is_active'
        ]);

        // 이미지 업로드
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('plugins', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $data['is_active'] = $request->has('is_active') && $request->is_active ? true : false;
        $data['sort_order'] = $request->sort_order ?? 0;

        Plugin::create($data);

        return redirect()->route('master.plugins.index')
            ->with('success', '플러그인이 생성되었습니다.');
    }

    /**
     * Show the form for editing the specified plugin.
     */
    public function edit(Plugin $plugin)
    {
        return view('master.plugins.edit', compact('plugin'));
    }

    /**
     * Update the specified plugin.
     */
    public function update(Request $request, Plugin $plugin)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plugins,slug,' . $plugin->id,
            'description' => 'nullable|string',
            'billing_type' => 'required|in:free,one_time,monthly',
            'price' => 'nullable|numeric|min:0',
            'one_time_price' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'name', 'slug', 'description', 'billing_type', 'price', 'one_time_price',
            'features', 'sort_order', 'is_active'
        ]);

        // 이미지 업로드
        if ($request->hasFile('image')) {
            // 기존 이미지 삭제
            if ($plugin->image) {
                Storage::disk('public')->delete($plugin->image);
            }
            
            $image = $request->file('image');
            $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('plugins', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $data['is_active'] = $request->has('is_active') && $request->is_active ? true : false;
        $data['sort_order'] = $request->sort_order ?? 0;

        $plugin->update($data);

        return redirect()->route('master.plugins.index')
            ->with('success', '플러그인이 수정되었습니다.');
    }

    /**
     * Remove the specified plugin.
     */
    public function destroy(Plugin $plugin)
    {
        // 이미지 삭제
        if ($plugin->image) {
            Storage::disk('public')->delete($plugin->image);
        }

        $plugin->delete();

        return redirect()->route('master.plugins.index')
            ->with('success', '플러그인이 삭제되었습니다.');
    }
}

