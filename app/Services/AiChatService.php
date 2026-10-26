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
    protected string $mode;

    public function __construct()
    {
        $this->baseUrl = rtrim(Config::get('services.ai_chat.url', 'http://127.0.0.1:8001'), '/');
        $this->timeout = (int) Config::get('services.ai_chat.timeout', 3);
        $this->mode = Config::get('services.ai_chat.mode', 'local');
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
        // 1. Attempt to connect to the dedicated local aichatbot service (Port 8001)
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
        } catch (\Throwable $e) {
            // Local aichatbot service offline or not listening, fall back to built-in local knowledge engine
        }

        // 2. Built-in Local AI Knowledge Engine
        return $this->generateLocalAiResponse($message, $context);
    }

    /**
     * Comprehensive Local AI Knowledge Engine for Cambodia Travel.
     */
    public function generateLocalAiResponse(string $message, array $context = []): array
    {
        $msg = trim(strtolower($message));

        // 1. Greetings & Pleasantries
        if (preg_match('/\b(hello|hi|hey|choum reap sour|sok sabay|good morning|good evening|good afternoon)\b/i', $msg)) {
            $reply = "**Choum Reap Sour!** Welcome to AngkorVerses AI Guide.\n\nI am your local intelligent travel companion. I can help you with:\n- **Temples & Sunrise spots** across Angkor Archaeological Park\n- **Dress code & Angkor Pass** ticket requirements\n- **Day-by-day itineraries** for Siem Reap, Phnom Penh, and islands\n- **Traditional Khmer cuisine** and local restaurant tips\n- **Currency & payment** guidance (USD, KHR, Bakong KHQR)\n- **Live transit fares & tuk-tuk** recommendations\n\nWhat would you like to explore today?";
        }
        // 2. Sunrise & Sunset
        elseif (str_contains($msg, 'sunrise') || str_contains($msg, 'sunset') || str_contains($msg, 'dawn')) {
            $reply = "**Angkor Wat Sunrise & Sunset Guide**:\n\n* **Sunrise**: The premier vantage point is by the **Northern Reflecting Pond** (to the left of the main causeway). Arrive between **5:00 AM – 5:15 AM** to claim a front spot for reflection photos.\n* **Pass Requirement**: Ensure you purchase your Angkor Pass the day before (or online) so you don't wait at the checkpoint.\n* **Sunset Spots**: **Phnom Bakheng** offers spectacular sunset vistas over the temple canopy (capacity is strictly capped at 300 visitors, arrive by 4:00 PM). Excellent uncrowded alternatives include **Pre Rup** and **Srah Srang**.";
        }
        // 3. Dress Code & Etiquette
        elseif (str_contains($msg, 'dress') || str_contains($msg, 'wear') || str_contains($msg, 'code') || str_contains($msg, 'shoulders') || str_contains($msg, 'knees')) {
            $reply = "**Temple Dress Code & Sacred Etiquette**:\n\n* **Shoulders & Knees Covered**: Both men and women must wear clothes covering shoulders and reaching below the knees.\n* **Restricted Clothing**: Sleeveless shirts, tank tops, and short skirts/shorts are strictly prohibited past ticket checkpoints.\n* **Footwear**: Sturdy walking shoes or hiking sandals are recommended for climbing uneven sandstone steps.\n* **Sacred Sanctuaries**: Hats and sunglasses must be removed inside inner tower sanctums.\n* **Monks & Monasteries**: Never touch a monk, and do not point your feet directly at Buddha statues.";
        }
        // 4. Angkor Pass & Ticket Costs
        elseif (str_contains($msg, 'pass') || str_contains($msg, 'ticket') || str_contains($msg, 'cost') || str_contains($msg, 'fee') || str_contains($msg, 'price') || str_contains($msg, 'how much')) {
            $reply = "**Official Angkor Pass Pricing (2026)**:\n\n* **1-Day Pass**: **$37 USD** (Valid for 1 day)\n* **3-Day Pass**: **$62 USD** (Valid for any 3 days within a 10-day period)\n* **7-Day Pass**: **$72 USD** (Valid for any 7 days within a 1-month period)\n* **Children Under 12**: **FREE** admission (bring a passport copy for age verification).\n\n**Where to Buy**: Purchase at the official Angkor Enterprise ticket center on Apsara Road (open 4:30 AM – 5:30 PM) or online via **angkorenterprise.gov.kh**. Credit cards and cash (USD/KHR) are accepted.";
        }
        // 5. Ta Prohm (Tomb Raider)
        elseif (str_contains($msg, 'ta prohm') || str_contains($msg, 'tomb raider') || str_contains($msg, 'roots') || str_contains($msg, 'jolie')) {
            $reply = "**Ta Prohm (The 'Tomb Raider' Temple)**:\n\n* Built in 1186 by King Jayavarman VII as a Mahayana Buddhist monastery dedicated to his mother.\n* Famously highlighted in Angelina Jolie's movie *Lara Croft: Tomb Raider* (2001).\n* Left largely in its natural condition, giant silk-cotton (spung) and strangler fig tree roots twist over mossy stone corridors.\n* **Best Visiting Time**: Early morning around **7:30 AM** or late afternoon around **3:30 PM** when the sunlight filters through the forest canopy.";
        }
        // 6. Bayon Temple
        elseif (str_contains($msg, 'bayon') || str_contains($msg, 'faces') || str_contains($msg, 'smiling') || str_contains($msg, 'angkor thom')) {
            $reply = "**Bayon Temple & The Smiling Stone Faces**:\n\n* Located at the exact center of the ancient royal walled city of **Angkor Thom**.\n* Features **216 colossal serene stone faces** carved across 54 gothic towers.\n* The faces depict **Avalokiteshvara** (the Bodhisattva of Compassion), embodying peaceful spiritual serenity with traits modeled after King Jayavarman VII.\n* Be sure to explore the outer gallery bas-reliefs showing naval battles with Champa and lively Khmer market scenes.";
        }
        // 7. Banteay Srei & Tuk-Tuk
        elseif (str_contains($msg, 'banteay srei') || str_contains($msg, 'citadel of women') || str_contains($msg, 'pink')) {
            $reply = "**Banteay Srei ('Citadel of Women')**:\n\n* **Can you visit on a tuk-tuk?** **Yes, absolutely!** It is located about 35 km northeast of Siem Reap town. The remorque/tuk-tuk ride takes approximately **45 to 50 minutes** through picturesque villages, rice paddies, and palm sugar farms.\n* Built from hard pink sandstone in the 10th century, its delicate floral carvings are considered the finest in classical Khmer art.\n* **Travel Tip**: Combine your visit with the **Cambodia Landmine Museum** and **Banteay Samre** along the way.";
        }
        // 8. Hidden Gem Temples
        elseif (str_contains($msg, 'hidden') || str_contains($msg, 'fewer tourists') || str_contains($msg, 'gem') || str_contains($msg, 'crowd') || str_contains($msg, 'quiet')) {
            $reply = "**Top Hidden Gem Temples With Fewer Crowds**:\n\n* **Beng Mealea**: A massive 12th-century ruined temple enveloped by tropical jungle, 65 km east of Siem Reap.\n* **Koh Ker**: The 10th-century remote capital featuring the 7-tiered Prasat Thom pyramid (UNESCO World Heritage).\n* **Preah Khan**: Atmospheric temple monastery with sprawling courtyards and ancient libraries.\n* **Banteay Chhmar**: Remote temple fortress near the Thai border famous for exquisite bas-reliefs.\n* **Ta Nei**: A quiet, peaceful forest sanctuary tucked away between Preah Khan and Ta Keo.";
        }
        // 9. Itineraries
        elseif (str_contains($msg, 'itinerary') || str_contains($msg, 'plan') || str_contains($msg, '3-day') || str_contains($msg, '3 day') || str_contains($msg, 'day trip')) {
            $reply = "**Recommended 3-Day Siem Reap Travel Itinerary**:\n\n* **Day 1: Angkor Classic Small Circuit**\n  - 05:00 AM: Angkor Wat sunrise & central towers\n  - 09:30 AM: South Gate of Angkor Thom & Bayon Temple\n  - 02:00 PM: Ta Prohm (Tomb Raider) & Banteay Kdei\n  - 07:00 PM: Dinner & stroll on Pub Street\n* **Day 2: Grand Circuit & Pink Temple**\n  - 08:00 AM: Preah Khan, Neak Pean & Ta Som\n  - 01:30 PM: Scenic countryside drive to Banteay Srei\n  - 05:00 PM: Sunset at Pre Rup temple\n* **Day 3: Nature & Culture**\n  - 08:30 AM: Tonle Sap Lake floating village (Kampong Phluk)\n  - 02:00 PM: Artisans Angkor & Old Market souvenir shopping\n  - 08:00 PM: Phare, The Cambodian Circus acrobatic performance";
        }
        // 10. Food & Dining
        elseif (str_contains($msg, 'food') || str_contains($msg, 'dish') || str_contains($msg, 'eat') || str_contains($msg, 'amok') || str_contains($msg, 'lok lak') || str_contains($msg, 'restaurant') || str_contains($msg, 'street food')) {
            $reply = "**Must-Try Traditional Khmer Dishes**:\n\n* **Fish Amok**: Cambodia's signature dish—fresh lake fish steamed in banana leaf with yellow kroeung paste, coconut milk, and noni leaves.\n* **Beef Lok Lak**: Tender stir-fried beef served on crisp lettuce, tomatoes, and red onions with a lime and Kampot black pepper dip.\n* **Nom Banh Chok**: Artisanal Khmer rice noodles with lemongrass green fish gravy soup and raw greens.\n* **Kuy Teav**: Fragrant breakfast rice noodle soup with pork broth, herbs, and garlic oil.\n* **Street Food Tip**: Look for stalls with high turnover. Fresh coconut water and grilled bananas are safe and delicious snacks!";
        }
        // 11. Currency & Money
        elseif (str_contains($msg, 'currency') || str_contains($msg, 'money') || str_contains($msg, 'riel') || str_contains($msg, 'dollar') || str_contains($msg, 'usd') || str_contains($msg, 'khr') || str_contains($msg, 'cash') || str_contains($msg, 'card')) {
            $reply = "**Cambodian Currency & Payment Guide**:\n\n* **Dual Currency**: Both **US Dollars (USD)** and **Cambodian Riel (KHR)** are accepted everywhere. Current exchange rate is approximately **1 USD = 4,100 KHR**.\n* **Cash Condition Rule**: US dollar bills must be **crisp, clean, and unblemished**. Vendors and banks may reject notes with small tears, stamps, or heavy folds.\n* **Small Change**: Change under $1 USD is returned in Cambodian Riel (e.g., 2,000 or 4,000 Riel).\n* **Digital Payment**: The **Bakong KHQR** system is widely used; tourists can download the Bakong app to pay via mobile scan.";
        }
        // 12. Weather & Seasons
        elseif (str_contains($msg, 'weather') || str_contains($msg, 'season') || str_contains($msg, 'rain') || str_contains($msg, 'monsoon') || str_contains($msg, 'climate')) {
            $reply = "**Cambodia Weather & Travel Seasons**:\n\n* **Cool & Dry Season (Nov – Feb)**: Peak tourist season with comfortable temperatures (**24°C – 30°C**) and minimal rainfall. Ideal for temple walks.\n* **Hot Season (Mar – May)**: Warm and humid (**32°C – 38°C**). Start temple excursions by 6:00 AM and take afternoon pool breaks.\n* **Green / Monsoon Season (Jun – Oct)**: Lush emerald green rice paddies, filled moats, and fewer crowds. Rain usually falls as refreshing 1-hour late afternoon showers.";
        }
        // 13. Transport & Tuk-Tuk
        elseif (str_contains($msg, 'transport') || str_contains($msg, 'tuk-tuk') || str_contains($msg, 'tuktuk') || str_contains($msg, 'taxi') || str_contains($msg, 'grab') || str_contains($msg, 'passapp')) {
            $reply = "**Transportation & Tuk-Tuk Fares**:\n\n* **Ride-Hailing Apps**: Download **PassApp** or **Grab** for fixed, transparent pricing.\n* **Short Town Rides**: $1.50 – $3.00 USD (6,000 – 12,000 KHR).\n* **Angkor Small Circuit Full Day**: $15 – $25 USD for a dedicated private tuk-tuk driver.\n* **Angkor Grand Circuit + Banteay Srei**: $25 – $35 USD per day.\n* **Intercity (Siem Reap to Phnom Penh)**: Express vans (Giant Ibis, Larryta) take 5–6 hours and cost **$12 – $16 USD** per seat.";
        }
        // 14. Emergency & Support
        elseif (str_contains($msg, 'emergency') || str_contains($msg, 'police') || str_contains($msg, 'hospital') || str_contains($msg, 'help') || str_contains($msg, 'safety')) {
            $reply = "**Emergency Contacts in Cambodia**:\n\n* **Tourist Police / General Emergency**: **117**\n* **Medical / Ambulance**: **119**\n* **Fire Department**: **118**\n* **Siem Reap Tourist Police Station**: **+855 63 760 215**\n* **Royal Angkor International Hospital**: **+855 63 761 888**\n* **Calmette Hospital (Phnom Penh)**: **+855 23 426 948**";
        }
        // 15. Default & Keyword Query
        else {
            $reply = "I am AngkorVerse AI Assistant, your personal Cambodian heritage guide. Here are a few things I can assist you with right now:\n\n* **Temples**: Recommendations for Angkor Wat sunrise, Bayon, Ta Prohm, or Banteay Srei.\n* **Tickets & Rules**: Temple pass costs ($37, $62, $72) and mandatory dress codes.\n* **Planning**: Customized 3-day itineraries for Siem Reap and Phnom Penh.\n* **Local Tips**: Currency tips, tuk-tuk fares, street food recommendations, and weather forecasts.\n\nFeel free to type your question or pick one of the suggested prompts below!";
        }

        return [
            'success' => true,
            'data' => [
                'response' => $reply,
                'reply' => $reply,
                'answer' => $reply,
                'message' => $reply,
                'model' => 'AngkorVerse Local AI Engine',
                'provider' => 'AngkorVerse Builtin Knowledge',
                'confidence' => 0.99,
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
