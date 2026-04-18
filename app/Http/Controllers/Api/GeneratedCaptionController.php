<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\CaptionTemplate;
use App\Models\GeneratedCaption;
use App\Traits\PaginationHelper;
use Illuminate\Http\Request;

class GeneratedCaptionController
{
    use PaginationHelper;

    /**
     * List generated captions by current user (admin)
     */
    public function index(Request $request)
    {
        $limit = $request->integer('limit', 20);
        $query = GeneratedCaption::where('created_by', $request->user()->id)
            ->with('product');

        // Apply search if value parameter is provided
        $query = $this->applySearch($query, $request, ['product_name', 'generated_text']);

        // Apply ordering if provided, otherwise default
        if ($request->has('order') && $request->has('sort')) {
            $query = $this->applyOrdering($query, $request);
        } else {
            $query = $query->orderByDesc('created_at');
        }

        $captions = $query->paginate($limit);

        return response()->json($this->formatPagination($captions));
    }

    /**
     * Generate caption from template (admin)
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'tone' => 'required|in:friendly,professional,playful',
            'include_emoji' => 'boolean',
        ]);

        // Find product
        $product = Product::findOrFail($validated['product_id']);

        // Find random template with matching tone and is_active
        $template = CaptionTemplate::where('tone', $validated['tone'])
            ->where('is_active', true)
            ->inRandomOrder()
            ->firstOrFail();

        // Replace placeholders
        $generatedText = str_replace(
            ['{name}', '{flavor}', '{price}', '{description}'],
            [$product->name, $product->flavor, $product->price, $product->description],
            $template->template_text
        );

        // Remove emoji if not included
        if (!$validated['include_emoji']) {
            $generatedText = preg_replace('/[\x{1F300}-\x{1FAFF}]/u', '', $generatedText);
        }

        // Create GeneratedCaption
        $caption = GeneratedCaption::create([
            'product_id' => $validated['product_id'],
            'product_name' => $product->name,
            'flavor' => $product->flavor,
            'tone' => $validated['tone'],
            'include_emoji' => $validated['include_emoji'],
            'generated_text' => $generatedText,
            'was_copied' => false,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($caption, 201);
    }

    /**
     * Mark caption as copied
     */
    public function markCopied(string $id)
    {
        $caption = GeneratedCaption::findOrFail($id);
        $caption->update(['was_copied' => true]);

        return response()->json([
            'message' => 'Caption ditandai sudah disalin',
        ]);
    }

    /**
     * Delete caption
     */
    public function destroy(string $id)
    {
        $caption = GeneratedCaption::findOrFail($id);
        $caption->delete();

        return response()->json([
            'message' => 'Caption berhasil dihapus',
        ]);
    }
}
