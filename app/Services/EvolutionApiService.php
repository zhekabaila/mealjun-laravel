<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $instanceName;

    /**
     * Initialize EvolutionApiService with configuration
     */
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.evolution.url'), '/');
        $this->apiKey = config('services.evolution.api_key');
        $this->instanceName = config('services.evolution.instance_name');
    }

    /**
     * Check validity of WhatsApp numbers
     *
     * @param array $numbers
     * @return array
     * @throws Exception
     */
    public function checkWhatsappNumbers(array $numbers): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' => $this->apiKey,
            ])->post(
                "{$this->baseUrl}/chat/whatsappNumbers/{$this->instanceName}",
                ['numbers' => $numbers]
            );

            if ($response->failed()) {
                throw new Exception('Evolution API check failed: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('WhatsApp number check error: ' . $e->getMessage());
        }
    }

    /**
     * Send text message via WhatsApp
     *
     * @param string $number
     * @param string $text
     * @return array
     * @throws Exception
     */
    public function sendText(string $number, string $text): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' => $this->apiKey,
            ])->post(
                "{$this->baseUrl}/message/sendText/{$this->instanceName}",
                [
                    'number' => $number,
                    'text' => $text,
                ]
            );

            if ($response->failed()) {
                throw new Exception('Evolution API send failed: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('WhatsApp send error: ' . $e->getMessage());
        }
    }

    /**
     * Format phone number to international format
     *
     * @param string $number
     * @return string
     */
    protected function formatNumber(string $number): string
    {
        // Remove non-digit characters
        $cleaned = preg_replace('/\D/', '', $number);

        // Convert 08xx to 628xx
        if (str_starts_with($cleaned, '08')) {
            return '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Notify admin via WhatsApp (silent fail - no exceptions thrown)
     *
     * @param string $adminNumber
     * @param string $message
     * @return bool
     */
    public function notifyAdmin(string $adminNumber, string $message): bool
    {
        try {
            // Format the number
            $formattedNumber = $this->formatNumber($adminNumber);

            // Check validity
            $checkResult = $this->checkWhatsappNumbers([$formattedNumber]);

            // Verify number exists in response
            if (!isset($checkResult['numberExists']) || !$checkResult['numberExists']) {
                Log::warning("WhatsApp number validation failed for: {$formattedNumber}");
                return false;
            }

            // Send message
            $this->sendText($formattedNumber, $message);

            return true;
        } catch (Exception $e) {
            // Silent fail - log but don't throw
            Log::error("WhatsApp admin notification failed: " . $e->getMessage(), [
                'admin_number' => $adminNumber,
                'message' => $message,
            ]);

            return false;
        }
    }
}
