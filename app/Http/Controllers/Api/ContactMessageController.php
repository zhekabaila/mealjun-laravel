<?php

namespace App\Http\Controllers\Api;

use App\Models\ContactMessage;
use App\Models\AboutInfo;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;

class ContactMessageController
{
    public function __construct(protected EvolutionApiService $evolutionApi) {}

    /**
     * Create new contact message (public)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|min:10',
        ]);

        // Create contact message
        $contact = ContactMessage::create($validated);

        // Try to send WhatsApp notification
        $about = AboutInfo::first();
        if ($about && $about->whatsapp_number) {
            $messageText = "📩 *Pesan Baru Masuk — Mealjun Website*\n\n" .
                "*Dari:* {$validated['name']}\n" .
                "*Email:* {$validated['email']}\n\n" .
                "*Pesan:*\n" .
                "{$validated['message']}\n\n" .
                "_Balas pesan ini melalui panel admin._";

            $this->evolutionApi->notifyAdmin($about->whatsapp_number, $messageText);
        }

        return response()->json([
            'message' => 'Pesan Anda berhasil dikirim. Kami akan segera menghubungi Anda.',
            'id' => $contact->id,
        ], 201);
    }

    /**
     * List all contact messages (admin)
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        $messages = $query->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($messages);
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

        return response()->json($message);
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
     * Reply to contact message (admin)
     */
    public function reply(Request $request, string $id)
    {
        $message = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'reply_message' => 'required|string|min:5',
            'send_whatsapp_notif' => 'boolean',
            'recipient_phone' => 'nullable|string|max:20',
        ]);

        // Update message
        $message->update([
            'reply_message' => $validated['reply_message'],
            'replied_at' => now(),
            'is_read' => true,
        ]);

        // Send WhatsApp notification if requested
        if ($validated['send_whatsapp_notif'] && $validated['recipient_phone']) {
            $whatsappMessage = "Halo _{$message->name}_,\n\n" .
                "Terima kasih telah menghubungi kami. Berikut balasan kami:\n\n" .
                "{$validated['reply_message']}\n\n" .
                "*— Tim Mealjun*";

            $this->evolutionApi->notifyAdmin($validated['recipient_phone'], $whatsappMessage);
        }

        return response()->json([
            'message' => 'Balasan berhasil disimpan',
            'data' => $message,
        ]);
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
