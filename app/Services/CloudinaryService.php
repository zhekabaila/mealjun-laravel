<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class CloudinaryService
{
    protected string $cloudName;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $uploadPreset;

    /**
     * Initialize CloudinaryService with configuration
     */
    public function __construct()
    {
        $this->cloudName = config('services.cloudinary.cloud_name');
        $this->apiKey = config('services.cloudinary.api_key');
        $this->apiSecret = config('services.cloudinary.api_secret');
        $this->uploadPreset = config('services.cloudinary.upload_preset');
    }

    /**
     * Upload base64 image to Cloudinary
     *
     * @param string $base64Image
     * @param string $folder
     * @param string|null $publicId
     * @return array
     * @throws Exception
     */
    public function uploadBase64(string $base64Image, string $folder, ?string $publicId = null): array
    {
        // Validate base64 format
        if (!preg_match('/^data:image\/(jpeg|png|webp|gif);base64,/', $base64Image)) {
            throw new Exception('Invalid image format. Must be base64 encoded image (data:image/type;base64,...)');
        }

        try {
            $payload = [
                'file' => $base64Image,
                'upload_preset' => $this->uploadPreset,
                'folder' => $folder,
            ];

            // Add public_id if provided
            if ($publicId !== null) {
                $payload['public_id'] = $publicId;
            }

            $response = Http::post(
                "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload",
                $payload
            );

            if ($response->failed()) {
                throw new Exception('Cloudinary upload failed: ' . $response->body());
            }

            $data = $response->json();

            return [
                'url' => $data['secure_url'],
                'public_id' => $data['public_id'],
            ];
        } catch (Exception $e) {
            throw new Exception('Image upload error: ' . $e->getMessage());
        }
    }

    /**
     * Delete image from Cloudinary
     *
     * @param string $publicId
     * @return bool
     * @throws Exception
     */
    public function delete(string $publicId): bool
    {
        try {
            $timestamp = time();
            $signature = hash('sha1', "public_id={$publicId}&timestamp={$timestamp}{$this->apiSecret}");

            $response = Http::post(
                "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy",
                [
                    'public_id' => $publicId,
                    'api_key' => $this->apiKey,
                    'timestamp' => $timestamp,
                    'signature' => $signature,
                ]
            );

            if ($response->failed()) {
                throw new Exception('Cloudinary delete failed: ' . $response->body());
            }

            $data = $response->json();

            return $data['result'] === 'ok';
        } catch (Exception $e) {
            throw new Exception('Image delete error: ' . $e->getMessage());
        }
    }
}
