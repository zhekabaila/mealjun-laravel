<?php

namespace App\Http\Controllers\Api;

use App\Models\Testimonial;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class TestimonialController
{
    public function __construct(protected CloudinaryService $cloudinary) {}

    /**
     * List all approved testimonials (public)
     */
    public function publicIndex()
    {
        $testimonials = Testimonial::where('is_approved', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($testimonials);
    }

    /**
     * List all testimonials (admin)
     */
    public function index()
    {
        $testimonials = Testimonial::orderByDesc('created_at')
            ->paginate(20);

        return response()->json($testimonials);
    }

    /**
     * Create new testimonial (admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_location' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string',
            'avatar_base64' => 'nullable|string|regex:/^data:image\/(jpeg|png|webp|gif);base64,/',
            'is_featured' => 'boolean',
            'is_approved' => 'boolean',
        ]);

        $avatarUrl = null;
        if (isset($validated['avatar_base64'])) {
            $uploadResult = $this->cloudinary->uploadBase64(
                $validated['avatar_base64'],
                'mealjun/avatars'
            );
            $avatarUrl = $uploadResult['url'];
        }

        $testimonial = Testimonial::create([
            'customer_name' => $validated['customer_name'],
            'customer_location' => $validated['customer_location'],
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'],
            'customer_avatar' => $avatarUrl,
            'is_featured' => $validated['is_featured'] ?? false,
            'is_approved' => $validated['is_approved'] ?? false,
        ]);

        return response()->json($testimonial, 201);
    }

    /**
     * Get single testimonial (admin)
     */
    public function show(string $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        return response()->json($testimonial);
    }

    /**
     * Update testimonial (admin)
     */
    public function update(Request $request, string $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $validated = $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_location' => 'sometimes|string|max:255',
            'rating' => 'sometimes|integer|min:1|max:5',
            'review_text' => 'sometimes|string',
            'avatar_base64' => 'nullable|string|regex:/^data:image\/(jpeg|png|webp|gif);base64,/',
            'is_featured' => 'boolean',
            'is_approved' => 'boolean',
        ]);

        if (isset($validated['avatar_base64'])) {
            $uploadResult = $this->cloudinary->uploadBase64(
                $validated['avatar_base64'],
                'mealjun/avatars'
            );
            $validated['customer_avatar'] = $uploadResult['url'];
            unset($validated['avatar_base64']);
        }

        $testimonial->update($validated);

        return response()->json($testimonial);
    }

    /**
     * Delete testimonial (admin)
     */
    public function destroy(string $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return response()->json(['message' => 'Testimonial berhasil dihapus']);
    }

    /**
     * Toggle featured status (admin)
     */
    public function toggleFeatured(string $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['is_featured' => !$testimonial->is_featured]);

        return response()->json($testimonial);
    }

    /**
     * Toggle approved status (admin)
     */
    public function toggleApproved(string $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['is_approved' => !$testimonial->is_approved]);

        return response()->json($testimonial);
    }
}
