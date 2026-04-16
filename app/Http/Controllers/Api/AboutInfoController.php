<?php

namespace App\Http\Controllers\Api;

use App\Models\AboutInfo;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class AboutInfoController
{
    public function __construct(protected CloudinaryService $cloudinary) {}

    /**
     * Get about info (public)
     */
    public function show()
    {
        $about = AboutInfo::first();

        if (!$about) {
            return response()->json(['message' => 'Data about belum tersedia'], 404);
        }

        return response()->json($about);
    }

    /**
     * Update about info (admin)
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'vision' => 'sometimes|string',
            'mission' => 'sometimes|string',
            'image_base64' => 'nullable|string|regex:/^data:image\/(jpeg|png|webp|gif);base64,/',
            'whatsapp_number' => 'sometimes|string|max:20',
            'email' => 'sometimes|email',
            'address' => 'sometimes|string',
        ]);

        if (isset($validated['image_base64'])) {
            $uploadResult = $this->cloudinary->uploadBase64(
                $validated['image_base64'],
                'mealjun/about'
            );
            $validated['image_url'] = $uploadResult['url'];
            unset($validated['image_base64']);
        }

        $validated['updated_by'] = auth()->id();
        $validated['updated_at'] = now();

        $about = AboutInfo::first();

        if ($about) {
            $about->update($validated);
        } else {
            // Create with defaults if doesn't exist
            $defaults = [
                'title' => '-',
                'description' => '-',
                'vision' => '-',
                'mission' => '-',
                'image_url' => '',
                'whatsapp_number' => '62',
                'email' => 'info@example.com',
                'address' => '-',
            ];

            $about = AboutInfo::create(array_merge($defaults, $validated));
        }

        return response()->json($about);
    }
}
