<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(Config::get('services.ai_chat.url', 'https://aichat-backend-pi.vercel.app'), '/');
        $this->timeout = (int) Config::get('services.ai_chat.timeout', 30);
    }

    /**
     * Get the base client instance.
     */
    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson()
            ->asJson();
    }

    /**
     * Send a chat message to the Angkor Verse AI Assistant.
     */
    public function sendMessage(string $message, ?string $sessionId = null, array $context = []): array
    {
        try {
            $payload = array_merge([
                'message' => $message,
                'session_id' => $sessionId ?? ('session_' . uniqid()),
            ], $context);

            $response = $this->client()->post('/api/chat', $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::warning('AI Chat API Error: ' . $response->body());
            return [
                'success' => false,
                'message' => 'AI Service error: ' . ($response->json('detail') ?? $response->json('message') ?? 'Unknown error'),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('AI Chat Service Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to connect to Angkor Verse AI service: ' . $e->getMessage(),
                'status' => 503,
            ];
        }
    }

    /**
     * Get multi-factor smart recommendations for Cambodia destinations.
     */
    public function getRecommendations(array $params = []): array
    {
        return $this->postRequest('/api/travel/recommendations', $params);
    }

    /**
     * Generate route-optimized day-by-day travel itinerary with budget breakdown.
     */
    public function generateItinerary(array $params = []): array
    {
        return $this->postRequest('/api/travel/itineraries', $params);
    }

    /**
     * Get live weather, forecast, and travel suitability advice for any province.
     */
    public function getWeather(?string $province = 'Siem Reap', int $days = 3): array
    {
        return $this->getRequest('/api/travel/weather', [
            'province' => $province ?? 'Siem Reap',
            'days' => $days,
        ]);
    }

    /**
     * Retrieve verified Cambodian festivals and events.
     */
    public function getEvents(?string $province = null, ?string $month = null, ?string $query = null): array
    {
        $params = array_filter([
            'province' => $province,
            'month' => $month,
            'query' => $query,
        ], fn($v) => !is_null($v));

        return $this->getRequest('/api/travel/events', $params);
    }

    /**
     * Get current USD <-> KHR reference exchange rate.
     */
    public function getCurrencyRate(): array
    {
        return $this->getRequest('/api/travel/currency');
    }

    /**
     * Convert currency amount between USD and KHR.
     */
    public function convertCurrency(float $amount, string $from = 'USD', string $to = 'KHR'): array
    {
        return $this->postRequest('/api/currency/convert', [
            'amount' => $amount,
            'from_currency' => $from,
            'to_currency' => $to,
        ]);
    }

    /**
     * Get tailored Cambodian transit options, pricing, and tips.
     */
    public function getTransport(string $origin = 'Siem Reap', string $destination = 'Siem Reap', int $travelers = 2): array
    {
        return $this->getRequest('/api/travel/transport', [
            'origin' => $origin,
            'destination' => $destination,
            'travelers' => $travelers,
        ]);
    }

    /**
     * Check availability of AI models (Gemini Online, Ollama Local) and system status.
     */
    public function getStatus(): array
    {
        return $this->getRequest('/api/ai/status');
    }

    /**
     * Get AI knowledge search results.
     */
    public function search(string $query, ?int $limit = 5): array
    {
        return $this->postRequest('/api/search', [
            'query' => $query,
            'limit' => $limit ?? 5,
        ]);
    }

    /**
     * Summarize tourism topic.
     */
    public function summarize(string $topic, ?string $length = 'medium', ?string $language = 'en'): array
    {
        return $this->postRequest('/api/summary', [
            'topic' => $topic,
            'length' => $length ?? 'medium',
            'language' => $language ?? 'en',
        ]);
    }

    /**
     * Get verified places list from AI service.
     */
    public function getPlaces(?string $category = null, ?string $province = null): array
    {
        $params = array_filter([
            'category' => $category,
            'province' => $province,
        ], fn($v) => !is_null($v));

        return $this->getRequest('/api/travel/places', $params);
    }

    /**
     * Get nearby places by coordinates.
     */
    public function getNearbyPlaces(float $lat, float $lon, float $maxDistanceKm = 25.0, int $limit = 6): array
    {
        return $this->getRequest('/api/places/nearby', [
            'lat' => $lat,
            'lon' => $lon,
            'max_distance_km' => $maxDistanceKm,
            'limit' => $limit,
        ]);
    }

    /**
     * Helper for GET requests.
     */
    protected function getRequest(string $endpoint, array $queryParams = []): array
    {
        try {
            $response = $this->client()->get($endpoint, $queryParams);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'AI Service error: ' . ($response->json('detail') ?? $response->json('message') ?? 'Unknown error'),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("AiChatService GET {$endpoint} error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to connect to Angkor Verse AI service: ' . $e->getMessage(),
                'status' => 503,
            ];
        }
    }

    /**
     * Helper for POST requests.
     */
    protected function postRequest(string $endpoint, array $payload = []): array
    {
        try {
            $response = $this->client()->post($endpoint, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'AI Service error: ' . ($response->json('detail') ?? $response->json('message') ?? 'Unknown error'),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("AiChatService POST {$endpoint} error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to connect to Angkor Verse AI service: ' . $e->getMessage(),
                'status' => 503,
            ];
        }
    }
}
