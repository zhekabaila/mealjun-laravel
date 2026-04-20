<?php

namespace App\Http\Controllers\Api;

use App\Models\ContactMessage;
use App\Models\AboutInfo;
use App\Services\EvolutionApiService;
use App\Traits\PaginationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactMessageController
{
    use PaginationHelper;

    public function __construct(protected EvolutionApiService $evolutionApi) {}

    /**
     * Create new contact message (public)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|regex:/^([0-9+\-\s()])+$/',
            'message' => 'required|string|min:10',
        ]);

        // Validate WhatsApp number existence
        try {
            $checkResult = $this->evolutionApi->checkWhatsappNumbers($validated['phone_number']);

            // Check if number exists on WhatsApp
            if (!$checkResult || !$checkResult['exists']) {
                return response()->json([
                    'message' => 'Nomor WhatsApp tidak valid atau tidak terdaftar. Harap gunakan nomor yang terdaftar di WhatsApp.',
                    'errors' => [
                        'phone_number' => ['Nomor WhatsApp tidak ditemukan.'],
                    ],
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memvalidasi nomor WhatsApp. Silakan coba lagi.',
                'errors' => [
                    'phone_number' => ['Validasi nomor gagal.'],
                ],
            ], 422);
        }

        // Create contact message
        $contact = ContactMessage::create($validated);

        // Try to send WhatsApp notification to admin
        $about = AboutInfo::first();
        if ($about && $about->whatsapp_number) {
            $messageText = "📩 *Pesan Baru Masuk — Mealjun Website*\n\n" .
                "*Dari:* {$validated['name']}\n" .
                "*WhatsApp:* {$validated['phone_number']}\n\n" .
                "*Pesan:*\n" .
                "{$validated['message']}\n\n" .
                "_Balas pesan ini melalui panel admin._";

            $this->evolutionApi->notifyAdmin($about->whatsapp_number, $messageText);
        }

        return response()->json([
            'data' => [
                'message' => 'Pesan Anda berhasil dikirim. Kami akan segera menghubungi Anda.',
                'id' => $contact->id,
            ],
        ], 201);
    }

    /**
     * List all contact messages (admin)
     */
    public function index(Request $request)
    {
        $limit = $request->integer('limit', 20);
        $query = ContactMessage::query();

        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Apply search if value parameter is provided
        $query = $this->applySearch($query, $request, ['name', 'phone_number', 'message']);

        // Apply ordering if provided, otherwise default
        if ($request->has('order') && $request->has('sort')) {
            $query = $this->applyOrdering($query, $request);
        } else {
            $query = $query->orderByDesc('created_at');
        }

        $messages = $query->paginate($limit);

        return response()->json($this->formatPagination($messages));
    }

    /**
     * Get single contact message (admin)
     */
    public function show(string $id)
    {
        $message = ContactMessage::findOrFail($id);

        // Auto mark as read
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return response()->json($this->formatResource($message));
    }

    /**
     * Mark message as read (admin)
     */
    public function markAsRead(string $id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Pesan ditandai sudah dibaca']);
    }

    /**
     * Reply to contact message (admin) - Auto sends to WhatsApp
     */
    public function reply(Request $request, string $id)
    {
        $message = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'reply_message' => 'required|string|min:5',
        ]);

        // Update message
        $message->update([
            'reply_message' => $validated['reply_message'],
            'replied_at' => now(),
            'is_read' => true,
        ]);

        // Auto send WhatsApp notification to customer
        try {
            $whatsappMessage = "Halo _{$message->name}_,\n\n" .
                "Terima kasih telah menghubungi kami. Berikut balasan kami:\n\n" .
                "{$validated['reply_message']}\n\n" .
                "*— Tim Mealjun*";

            $this->evolutionApi->sendText($message->phone_number, $whatsappMessage);
        } catch (\Exception $e) {
            // Log error but don't fail the request - message is already saved
            Log::error("Failed to send WhatsApp reply to {$message->phone_number}", [
                'contact_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json($this->formatResource($message));
    }

    /**
     * Delete contact message (admin)
     */
    public function destroy(string $id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return response()->json(['message' => 'Pesan berhasil dihapus']);
    }
}
