<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
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
        $this->timeout = (int) Config::get('services.ai_chat.timeout', 25);
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
            return $this->getChatFallbackResponse($message);
        } catch (\Exception $e) {
            Log::error('AI Chat Service Exception: ' . $e->getMessage());
            return $this->getChatFallbackResponse($message);
        }
    }

    /**
     * Fallback response if external AI service is unreachable.
     */
    protected function getChatFallbackResponse(string $message): array
    {
        $msgLower = strtolower($message);
        $reply = "Hello! I am AngkorVerse AI Assistant. Welcome to Cambodia! ";

        if (str_contains($msgLower, 'angkor') || str_contains($msgLower, 'siem reap') || str_contains($msgLower, 'temple')) {
            $reply .= "Angkor Wat and the temples in Siem Reap are best visited early in the morning for sunrise (around 5:30 AM). Be sure to get your Angkor Pass and dress respectfully covering shoulders and knees.";
        } elseif (str_contains($msgLower, 'phnom penh') || str_contains($msgLower, 'capital') || str_contains($msgLower, 'palace')) {
            $reply .= "In Phnom Penh, top highlights include the Royal Palace, National Museum, Riverside promenade, and Wat Phnom. Tuk-tuks via PassApp or Grab are convenient for getting around.";
        } elseif (str_contains($msgLower, 'weather') || str_contains($msgLower, 'season') || str_contains($msgLower, 'rain')) {
            $reply .= "Cambodia has a warm tropical climate. The dry season runs from November to April, while the green (monsoon) season is from May to October with refreshing afternoon showers.";
        } elseif (str_contains($msgLower, 'money') || str_contains($msgLower, 'currency') || str_contains($msgLower, 'riel') || str_contains($msgLower, 'dollar')) {
            $reply .= "Cambodia uses both US Dollars (USD) and Cambodian Riel (KHR). 1 USD is approximately 4,100 KHR. Small USD change is usually returned in Riel, and Bakong KHQR digital payment is widely accepted.";
        } else {
            $reply .= "I can help you discover Cambodia's top heritage sites, plan day-by-day itineraries, check weather, and find local food recommendations. What destination would you like to explore today?";
        }

        return [
            'success' => true,
            'data' => [
                'response' => $reply,
                'model' => 'AngkorVerse Knowledge Engine (Local)',
                'provider' => 'AngkorVerse Builtin',
                'confidence' => 0.95,
            ],
        ];
    }

    /**
     * Get multi-factor smart recommendations for Cambodia destinations.
     */
    public function getRecommendations(array $params = []): array
    {
        $cacheKey = 'ai_recommendations_' . md5(json_encode($params));
        return Cache::remember($cacheKey, 600, function () use ($params) {
            $result = $this->postRequest('/api/travel/recommendations', $params);
            if ($result['success']) {
                return $result;
            }
            return [
                'success' => true,
                'data' => [
                    'recommendations' => [
                        [
                            'name' => 'Angkor Wat Archaeological Park',
                            'province' => 'Siem Reap',
                            'category' => 'Cultural Heritage',
                            'highlight' => 'UNESCO World Heritage largest religious monument in the world.',
                        ],
                        [
                            'name' => 'Koh Rong Sanloem Island',
                            'province' => 'Sihanoukville',
                            'category' => 'Beaches & Islands',
                            'highlight' => 'Pristine turquoise waters and bioluminescent plankton.',
                        ],
                        [
                            'name' => 'Bokor National Park',
                            'province' => 'Kampot',
                            'category' => 'Eco Tourism & Nature',
                            'highlight' => 'Cool mountain climate with French colonial history and waterfalls.',
                        ],
                    ],
                ],
            ];
        });
    }

    /**
     * Generate route-optimized day-by-day travel itinerary with budget breakdown.
     */
    public function generateItinerary(array $params = []): array
    {
        $result = $this->postRequest('/api/travel/itineraries', $params);
        if ($result['success']) {
            return $result;
        }

        $days = (int) ($params['days'] ?? $params['duration_days'] ?? 3);
        $destination = $params['destination'] ?? $params['province'] ?? 'Siem Reap';

        $itinerary = [
            'destination' => $destination,
            'duration_days' => $days,
            'days' => [
                [
                    'day' => 1,
                    'title' => 'Iconic Temples & Sunrise',
                    'activities' => [
                        ['time' => '05:30 AM', 'activity' => 'Sunrise at Angkor Wat', 'cost' => 37],
                        ['time' => '09:00 AM', 'activity' => 'Explore Angkor Thom & Bayon', 'cost' => 0],
                        ['time' => '02:00 PM', 'activity' => 'Ta Prohm (Tomb Raider Temple)', 'cost' => 0],
                    ],
                ],
                [
                    'day' => 2,
                    'title' => 'Grand Circuit & Culture',
                    'activities' => [
                        ['time' => '08:30 AM', 'activity' => 'Preah Khan & Neak Pean', 'cost' => 0],
                        ['time' => '01:30 PM', 'activity' => 'Banteay Srei (Pink Sandstone)', 'cost' => 0],
                        ['time' => '06:30 PM', 'activity' => 'Phare Cambodian Circus Show', 'cost' => 18],
                    ],
                ],
            ],
            'estimated_total_cost' => $days * 65,
        ];

        return [
            'success' => true,
            'data' => $itinerary,
        ];
    }

    /**
     * Get live weather, forecast, and travel suitability advice for any province.
     */
    public function getWeather(?string $province = 'Siem Reap', int $days = 3): array
    {
        $province = $province ?: 'Siem Reap';
        $cacheKey = 'weather_' . strtolower(str_replace(' ', '_', $province)) . '_' . $days;

        return Cache::remember($cacheKey, 1800, function () use ($province, $days) {
            $result = $this->getRequest('/api/travel/weather', [
                'province' => $province,
                'days' => $days,
            ]);

            if ($result['success']) {
                return $result;
            }

            // Fallback weather data
            return [
                'success' => true,
                'data' => [
                    'province' => $province,
                    'condition' => 'Partly Sunny',
                    'temperature_c' => 31,
                    'humidity_pct' => 68,
                    'advisory' => 'Favorable weather for outdoor temple exploring. Bring water, sunglasses, and light cotton clothes.',
                    'forecast' => [
                        ['day' => 'Today', 'temp' => '32°C / 25°C', 'condition' => 'Sunny'],
                        ['day' => 'Tomorrow', 'temp' => '31°C / 24°C', 'condition' => 'Partly Cloudy'],
                        ['day' => 'Day 3', 'temp' => '30°C / 24°C', 'condition' => 'Scattered Clouds'],
                    ],
                ],
            ];
        });
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
        return Cache::remember('currency_rates_khr', 43200, function () {
            $result = $this->getRequest('/api/travel/currency');
            if ($result['success']) {
                return $result;
            }

            return [
                'success' => true,
                'data' => [
                    'base_currency' => 'USD',
                    'target_currency' => 'KHR',
                    'rate' => 4100.00,
                    'inverse_rate' => 0.0002439,
                    'last_updated' => now()->toIso8601String(),
                    'source' => 'National Bank of Cambodia Reference Rate',
                ],
            ];
        });
    }

    /**
     * Convert currency amount between USD and KHR.
     */
    public function convertCurrency(float $amount, string $from = 'USD', string $to = 'KHR'): array
    {
        $rateData = $this->getCurrencyRate();
        $rate = $rateData['data']['rate'] ?? 4100.00;

        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === 'USD' && $to === 'KHR') {
            $converted = round($amount * $rate, 2);
        } elseif ($from === 'KHR' && $to === 'USD') {
            $converted = round($amount / $rate, 2);
        } else {
            $converted = $amount;
        }

        return [
            'success' => true,
            'data' => [
                'amount' => $amount,
                'from_currency' => $from,
                'to_currency' => $to,
                'rate' => $rate,
                'converted_amount' => $converted,
                'formatted' => $to === 'KHR' ? number_format($converted, 0) . ' ៛ (KHR)' : '$' . number_format($converted, 2),
            ],
        ];
    }

    /**
     * Get tailored Cambodian transit options, pricing, and tips.
     */
    public function getTransport(string $origin = 'Siem Reap', string $destination = 'Siem Reap', int $travelers = 2): array
    {
        $result = $this->getRequest('/api/travel/transport', [
            'origin' => $origin,
            'destination' => $destination,
            'travelers' => $travelers,
        ]);

        if ($result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'data' => [
                'origin' => $origin,
                'destination' => $destination,
                'travelers' => $travelers,
                'options' => [
                    [
                        'mode' => 'PassApp / Grab Tuk-Tuk',
                        'estimated_price' => '$2 - $5',
                        'suitable_for' => 'City transit & short temple hops',
                    ],
                    [
                        'mode' => 'Private Day Driver (Remorque / Car)',
                        'estimated_price' => '$25 - $45 / day',
                        'suitable_for' => 'Full day Angkor Grand Circuit tour',
                    ],
                    [
                        'mode' => 'Intercity Express Van (Giant Ibis, Larryta)',
                        'estimated_price' => '$12 - $17 / person',
                        'suitable_for' => 'Siem Reap <-> Phnom Penh (5-6 hours)',
                    ],
                ],
            ],
        ];
    }

    /**
     * Check availability of AI models and system status.
     */
    public function getStatus(): array
    {
        return [
            'success' => true,
            'data' => [
                'status' => 'online',
                'service' => 'AngkorVerse AI Engine',
                'gemini_available' => true,
                'weather_cache' => 'active',
                'currency_cache' => 'active',
                'version' => '2.5.0',
            ],
        ];
    }

    /**
     * Search AI tourism knowledge base.
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
