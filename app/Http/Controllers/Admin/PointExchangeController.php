<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\PointExchangeSetting;
use App\Models\PointExchangeProduct;
use App\Models\PointExchangeApplication;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PointExchangeController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canManage()) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display point exchange settings page.
     */
    public function index(Site $site)
    {
        $setting = PointExchangeSetting::getForSite($site->id);
        $products = PointExchangeProduct::where('site_id', $site->id)
            ->orderBy('order')
            ->orderBy('created_at')
            ->get();
        $boards = \App\Models\Board::where('site_id', $site->id)->active()->ordered()->get();

        return view('admin.point-exchange.index', compact('site', 'setting', 'products', 'boards'));
    }

    /**
     * Update point exchange settings.
     */
    public function updateSettings(Site $site, Request $request)
    {
        $request->validate([
            'page_title' => 'required|string|max:255',
            'notice_title' => 'required|string|max:255',
            'notices' => 'nullable|array',
            'notices.*' => 'nullable|string',
            'min_amount' => 'required|integer|min:1',
            'max_amount' => 'required|integer|min:1|gte:min_amount',
            'form_fields' => 'nullable|array',
            'form_fields.*.title' => 'required|string|max:255',
            'form_fields.*.content' => 'required|string|max:255',
            'requirements' => 'nullable|array',
            'requirements.*.board_id' => 'required|exists:boards,id',
            'requirements.*.post_count' => 'required|integer|min:1',
            'requirements.*.min_characters' => 'required|integer|min:1',
            'random_order' => 'nullable|boolean',
            'products_per_page' => 'required|integer|min:1',
            'pc_columns' => 'required|integer|min:1|max:12',
            'mobile_columns' => 'required|integer|min:1|max:6',
        ]);

        $setting = PointExchangeSetting::getForSite($site->id);
        $setting->update([
            'page_title' => $request->page_title,
            'notice_title' => $request->notice_title,
            'notices' => $request->notices ?? [],
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'form_fields' => $request->form_fields ?? [],
            'requirements' => $request->requirements ?? [],
            'random_order' => $request->has('random_order') ? true : false,
            'products_per_page' => $request->products_per_page ?? 12,
            'pc_columns' => $request->pc_columns ?? 4,
            'mobile_columns' => $request->mobile_columns ?? 2,
        ]);

        return redirect()->route('admin.point-exchange.index', ['site' => $site->slug])
            ->with('success', '설정이 저장되었습니다.');
    }

    /**
     * Store a new product.
     */
    public function storeProduct(Site $site, Request $request)
    {
        $request->validate([
            'thumbnail' => 'nullable|image|max:51200',
            'item_name' => 'required|string|max:255',
            'item_content' => 'required|string|max:255',
            'notice' => 'nullable|string',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $result = $this->fileUploadService->upload($request->file('thumbnail'), 'point-exchange/products');
            $thumbnailPath = $result['file_path'];
        }

        $maxOrder = PointExchangeProduct::where('site_id', $site->id)->max('order') ?? 0;

        PointExchangeProduct::create([
            'site_id' => $site->id,
            'thumbnail_path' => $thumbnailPath,
            'item_name' => $request->item_name,
            'item_content' => $request->item_content,
            'notice' => $request->notice,
            'order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.point-exchange.index', ['site' => $site->slug])
            ->with('success', '상품이 추가되었습니다.');
    }

    /**
     * Update a product.
     */
    public function updateProduct(Site $site, PointExchangeProduct $product, Request $request)
    {
        if ($product->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'thumbnail' => 'nullable|image|max:51200',
            'item_name' => 'required|string|max:255',
            'item_content' => 'required|string|max:255',
            'notice' => 'nullable|string',
        ]);

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($product->thumbnail_path) {
                Storage::disk('public')->delete($product->thumbnail_path);
            }
            $result = $this->fileUploadService->upload($request->file('thumbnail'), 'point-exchange/products');
            $product->thumbnail_path = $result['file_path'];
        }

        $product->update([
            'item_name' => $request->item_name,
            'item_content' => $request->item_content,
            'notice' => $request->notice,
        ]);

        return redirect()->route('admin.point-exchange.index', ['site' => $site->slug])
            ->with('success', '상품이 수정되었습니다.');
    }

    /**
     * Delete a product.
     */
    public function destroyProduct(Site $site, PointExchangeProduct $product)
    {
        if ($product->site_id !== $site->id) {
            abort(403);
        }

        // Delete thumbnail
        if ($product->thumbnail_path) {
            Storage::disk('public')->delete($product->thumbnail_path);
        }

        $product->delete();

        return redirect()->route('admin.point-exchange.index', ['site' => $site->slug])
            ->with('success', '상품이 삭제되었습니다.');
    }

    /**
     * Show applications for a product.
     */
    public function showApplications(Site $site, PointExchangeProduct $product)
    {
        if ($product->site_id !== $site->id) {
            abort(403);
        }

        $setting = PointExchangeSetting::getForSite($site->id);
        $applications = PointExchangeApplication::where('site_id', $site->id)
            ->where('product_id', $product->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.point-exchange.applications', compact('site', 'product', 'setting', 'applications'));
    }

    /**
     * Update application status.
     */
    public function updateApplication(Site $site, PointExchangeApplication $application, Request $request)
    {
        if ($application->site_id !== $site->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,completed,rejected,cancelled',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        // If status is being changed to rejected and rejection_reason is provided, use it
        // Otherwise, keep existing rejection_reason if status is not rejected
        $rejectionReason = $request->rejection_reason;
        if ($request->status !== 'rejected' && empty($rejectionReason)) {
            $rejectionReason = $application->rejection_reason;
        }

        $application->updateStatus($request->status, $rejectionReason);

        return redirect()->route('admin.point-exchange.applications', ['site' => $site->slug, 'product' => $application->product_id])
            ->with('success', '신청 상태가 변경되었습니다.');
    }
}

