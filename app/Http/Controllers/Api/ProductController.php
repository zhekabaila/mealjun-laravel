<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Services\CloudinaryService;
use App\Traits\PaginationHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController
{
    use PaginationHelper;

    public function __construct(protected CloudinaryService $cloudinary) {}

    /**
     * List all available products (public)
     */
    public function publicIndex(Request $request)
    {
        $limit = $request->integer('limit', 12);
        $query = Product::whereIn('stock_status', ['available', 'limited']);

        if ($request->has('flavor')) {
            $query->where('flavor', $request->input('flavor'));
        }

        // Apply search if value parameter is provided
        $query = $this->applySearch($query, $request, ['name', 'flavor', 'description']);

        // Apply ordering if provided, otherwise default
        if ($request->has('order') && $request->has('sort')) {
            $query = $this->applyOrdering($query, $request);
        } else {
            $query = $query->orderByDesc('is_featured')->orderByDesc('created_at');
        }

        $products = $query->paginate($limit);

        return response()->json($this->formatPagination($products));
    }

    /**
     * Get single product by ID (public)
     */
    public function publicShow(string $id)
    {
        $product = Product::findOrFail($id);
        $product->increment('view_count');

        return response()->json($product);
    }

    /**
     * List all products (admin)
     */
    public function index(Request $request)
    {
        $limit = $request->integer('limit', 15);
        $query = Product::with('creator');

        if ($request->has('stock_status')) {
            $query->where('stock_status', $request->input('stock_status'));
        }

        // Apply search if value parameter is provided
        $query = $this->applySearch($query, $request, ['name', 'flavor', 'description']);

        // Apply ordering if provided, otherwise default
        if ($request->has('order') && $request->has('sort')) {
            $query = $this->applyOrdering($query, $request);
        } else {
            $query = $query->orderByDesc('created_at');
        }

        $products = $query->paginate($limit);

        return response()->json($this->formatPagination($products));
    }

    /**
     * Create new product (admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'flavor' => 'required|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image_base64' => 'required|string|regex:/^data:image\/(jpeg|png|webp|gif);base64,/',
            'shopee_link' => 'nullable|url',
            'tiktok_link' => 'nullable|url',
            'whatsapp_link' => 'nullable|url',
            'stock_status' => 'required|in:available,limited,out_of_stock',
            'is_featured' => 'boolean',
        ]);

        // Upload image
        $uploadResult = $this->cloudinary->uploadBase64(
            $validated['image_base64'],
            'mealjun/products'
        );

        $product = Product::create([
            'name' => $validated['name'],
            'flavor' => $validated['flavor'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'image_url' => $uploadResult['url'],
            'shopee_link' => $validated['shopee_link'],
            'tiktok_link' => $validated['tiktok_link'],
            'whatsapp_link' => $validated['whatsapp_link'],
            'stock_status' => $validated['stock_status'],
            'is_featured' => $validated['is_featured'] ?? false,
            'created_by' => auth()->id(),
        ]);

        return response()->json($product, 201);
    }

    /**
     * Get single product (admin)
     */
    public function show(string $id)
    {
        $product = Product::with('creator')->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update product (admin)
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'flavor' => 'sometimes|string|max:100',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'image_base64' => 'nullable|string|regex:/^data:image\/(jpeg|png|webp|gif);base64,/',
            'shopee_link' => 'nullable|url',
            'tiktok_link' => 'nullable|url',
            'whatsapp_link' => 'nullable|url',
            'stock_status' => 'sometimes|in:available,limited,out_of_stock',
            'is_featured' => 'boolean',
        ]);

        if (isset($validated['image_base64'])) {
            $uploadResult = $this->cloudinary->uploadBase64(
                $validated['image_base64'],
                'mealjun/products'
            );
            $validated['image_url'] = $uploadResult['url'];
            unset($validated['image_base64']);
        }

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * Delete product (admin)
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

    /**
     * Toggle featured status (admin)
     */
    public function toggleFeatured(string $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);

        return response()->json($product);
    }

    /**
     * Update stock status (admin)
     */
    public function updateStockStatus(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'stock_status' => 'required|in:available,limited,out_of_stock',
        ]);

        $product->update($validated);

        return response()->json($product);
    }
}
