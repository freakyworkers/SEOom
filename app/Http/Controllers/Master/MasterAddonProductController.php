<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AddonProduct;
use App\Models\AddonProductOption;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterAddonProductController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of addon products.
     */
    public function index()
    {
        $addonProducts = AddonProduct::orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('master.addon-products.index', compact('addonProducts'));
    }

    /**
     * Show the form for creating a new addon product.
     */
    public function create()
    {
        return view('master.addon-products.create');
    }

    /**
     * Store a newly created addon product.
     */
    public function store(Request $request)
    {
        $resourceTypes = ['storage', 'traffic'];
        $isResourceType = in_array($request->input('type'), $resourceTypes);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'type' => 'required|in:storage,traffic,feature_crawler,feature_event_application,feature_point_exchange,board_type_event,registration_referral,feature_point_message',
            'amount_mb' => $isResourceType ? 'nullable|integer|min:0' : 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:one_time,monthly',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'options' => 'nullable|array',
            'options.*.name' => 'required_with:options|string|max:255',
            'options.*.amount_mb' => 'nullable|integer|min:0',
            'options.*.price' => 'required_with:options|numeric|min:0',
            'options.*.sort_order' => 'nullable|integer|min:0',
            'options.*.is_active' => 'nullable|boolean',
        ]);

        $slug = $request->input('slug');
        if (empty($slug)) {
            $slug = Str::slug($request->input('name'));
        }

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (AddonProduct::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // 썸네일 업로드 처리
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $directory = 'addon-products/' . date('Y/m');
            $result = $this->fileUploadService->upload($request->file('thumbnail'), $directory);
            $thumbnailPath = $result['file_path'];
        }

        $createData = [
            'name' => $request->input('name'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'thumbnail' => $thumbnailPath,
            'type' => $request->input('type'),
            'price' => $request->input('price', 0),
            'billing_cycle' => $request->input('billing_cycle'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ];
        
        // 옵션이 없으면 기본 amount_mb와 price 사용
        $hasOptions = $request->has('options') && is_array($request->input('options')) && count($request->input('options')) > 0;
        
        if ($hasOptions) {
            // 옵션이 있으면 상품 자체의 amount_mb와 price는 0으로 설정
            $createData['amount_mb'] = 0;
            $createData['price'] = 0;
        } else {
            // 옵션이 없으면 기존 로직대로
            if ($isResourceType) {
                $createData['amount_mb'] = $request->input('amount_mb', 0);
            } else {
                $createData['amount_mb'] = 0;
            }
        }
        
        $addonProduct = AddonProduct::create($createData);

        // 옵션 저장
        if ($hasOptions) {
            foreach ($request->input('options') as $index => $optionData) {
                if (!empty($optionData['name']) && isset($optionData['price'])) {
                    AddonProductOption::create([
                        'addon_product_id' => $addonProduct->id,
                        'name' => $optionData['name'],
                        'amount_mb' => $isResourceType ? ($optionData['amount_mb'] ?? 0) : null,
                        'price' => $optionData['price'],
                        'sort_order' => $optionData['sort_order'] ?? $index,
                        'is_active' => $optionData['is_active'] ?? true,
                    ]);
                }
            }
        }

        return redirect()->route('master.addon-products.index')
            ->with('success', '추가 구매 상품이 생성되었습니다.');
    }

    /**
     * Display the specified addon product.
     */
    public function show(AddonProduct $addonProduct)
    {
        return view('master.addon-products.show', compact('addonProduct'));
    }

    /**
     * Show the form for editing the specified addon product.
     */
    public function edit(AddonProduct $addonProduct)
    {
        $addonProduct->load('options');
        return view('master.addon-products.edit', compact('addonProduct'));
    }

    /**
     * Update the specified addon product.
     */
    public function update(Request $request, AddonProduct $addonProduct)
    {
        $resourceTypes = ['storage', 'traffic'];
        $isResourceType = in_array($request->input('type'), $resourceTypes);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'type' => 'required|in:storage,traffic,feature_crawler,feature_event_application,feature_point_exchange,board_type_event,registration_referral,feature_point_message',
            'amount_mb' => $isResourceType ? 'nullable|integer|min:0' : 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:one_time,monthly',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'options' => 'nullable|array',
            'options.*.id' => 'nullable|exists:addon_product_options,id',
            'options.*.name' => 'required_with:options|string|max:255',
            'options.*.amount_mb' => 'nullable|integer|min:0',
            'options.*.price' => 'required_with:options|numeric|min:0',
            'options.*.sort_order' => 'nullable|integer|min:0',
            'options.*.is_active' => 'nullable|boolean',
        ]);

        // 썸네일 업로드 처리
        if ($request->hasFile('thumbnail')) {
            // 기존 썸네일 삭제
            if ($addonProduct->thumbnail) {
                $this->fileUploadService->delete($addonProduct->thumbnail);
            }
            
            $directory = 'addon-products/' . date('Y/m');
            $result = $this->fileUploadService->upload($request->file('thumbnail'), $directory);
            $thumbnailPath = $result['file_path'];
        } else {
            $thumbnailPath = $addonProduct->thumbnail;
        }

        // 옵션이 있는지 확인
        $hasOptions = $request->has('options') && is_array($request->input('options')) && count($request->input('options')) > 0;

        $updateData = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'thumbnail' => $thumbnailPath,
            'type' => $request->input('type'),
            'price' => $request->input('price', 0),
            'billing_cycle' => $request->input('billing_cycle'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ];
        
        if ($hasOptions) {
            // 옵션이 있으면 상품 자체의 amount_mb와 price는 0으로 설정
            $updateData['amount_mb'] = 0;
            $updateData['price'] = 0;
        } else {
            // 옵션이 없으면 기존 로직대로
            if ($isResourceType) {
                $updateData['amount_mb'] = $request->input('amount_mb', 0);
            } else {
                $updateData['amount_mb'] = 0;
            }
        }

        $addonProduct->update($updateData);

        // 옵션 처리
        if ($hasOptions) {
            $existingOptionIds = [];
            
            foreach ($request->input('options') as $index => $optionData) {
                if (!empty($optionData['name']) && isset($optionData['price'])) {
                    if (isset($optionData['id']) && $optionData['id']) {
                        // 기존 옵션 업데이트
                        $option = AddonProductOption::find($optionData['id']);
                        if ($option && $option->addon_product_id === $addonProduct->id) {
                            $option->update([
                                'name' => $optionData['name'],
                                'amount_mb' => $isResourceType ? ($optionData['amount_mb'] ?? 0) : null,
                                'price' => $optionData['price'],
                                'sort_order' => $optionData['sort_order'] ?? $index,
                                'is_active' => $optionData['is_active'] ?? true,
                            ]);
                            $existingOptionIds[] = $option->id;
                        }
                    } else {
                        // 새 옵션 생성
                        $newOption = AddonProductOption::create([
                            'addon_product_id' => $addonProduct->id,
                            'name' => $optionData['name'],
                            'amount_mb' => $isResourceType ? ($optionData['amount_mb'] ?? 0) : null,
                            'price' => $optionData['price'],
                            'sort_order' => $optionData['sort_order'] ?? $index,
                            'is_active' => $optionData['is_active'] ?? true,
                        ]);
                        $existingOptionIds[] = $newOption->id;
                    }
                }
            }
            
            // 삭제된 옵션 제거
            $addonProduct->options()->whereNotIn('id', $existingOptionIds)->delete();
        } else {
            // 옵션이 없으면 모든 옵션 삭제
            $addonProduct->options()->delete();
        }

        return redirect()->route('master.addon-products.index')
            ->with('success', '추가 구매 상품이 업데이트되었습니다.');
    }

    /**
     * Remove the specified addon product.
     */
    public function destroy(AddonProduct $addonProduct)
    {
        // Check if there are active user addons
        $activeAddons = $addonProduct->userAddons()->where('status', 'active')->count();
        
        if ($activeAddons > 0) {
            return redirect()->route('master.addon-products.index')
                ->with('error', '활성화된 사용자 추가 구매가 있어 삭제할 수 없습니다. 먼저 비활성화하세요.');
        }

        $addonProduct->delete();

        return redirect()->route('master.addon-products.index')
            ->with('success', '추가 구매 상품이 삭제되었습니다.');
    }
}

