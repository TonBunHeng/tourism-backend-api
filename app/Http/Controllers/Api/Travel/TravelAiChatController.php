<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Services\AiChatService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelAiChatController extends Controller
{
    use ApiResponse;

    protected AiChatService $aiService;

    public function __construct(AiChatService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle AI chat message.
     * POST /api/travel/ai/chat or POST /api/travel/ai-chat
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:10',
        ]);

        $sessionId = $validated['session_id'] ?? ($request->user() ? ('user_' . $request->user()->id) : null);

        $context = [];
        if (!empty($validated['category'])) {
            $context['category'] = $validated['category'];
        }
        if (!empty($validated['province'])) {
            $context['province'] = $validated['province'];
        }
        if (!empty($validated['language'])) {
            $context['language'] = $validated['language'];
        }

        $result = $this->aiService->sendMessage($validated['message'], $sessionId, $context);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'AI service is currently unavailable.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'AI response generated successfully.'
        );
    }

    /**
     * Generate multi-factor smart recommendations for Cambodia destinations.
     * POST /api/travel/ai/recommendations or POST /api/travel/recommendations
     */
    public function recommendations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'budget' => 'nullable|string|in:budget,moderate,luxury',
            'travel_style' => 'nullable|string',
            'interests' => 'nullable|array',
            'interests.*' => 'string',
            'duration_days' => 'nullable|integer|min:1|max:30',
        ]);

        $result = $this->aiService->getRecommendations($validated);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'Unable to generate recommendations.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'Recommendations generated successfully.'
        );
    }

    /**
     * Generate route-optimized day-by-day travel itinerary.
     * POST /api/travel/ai/itineraries or POST /api/travel/itineraries
     */
    public function itineraries(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:100',
            'days' => 'nullable|integer|min:1|max:14',
            'duration_days' => 'nullable|integer|min:1|max:14',
            'budget' => 'nullable|string|in:budget,moderate,luxury',
            'travel_style' => 'nullable|string',
            'interests' => 'nullable|array',
            'interests.*' => 'string',
        ]);

        $result = $this->aiService->generateItinerary($validated);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'Unable to generate itinerary.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'Itinerary generated successfully.'
        );
    }

    /**
     * Get weather forecast and travel suitability advice.
     * GET /api/travel/ai/weather or GET /api/travel/weather
     */
    public function weather(Request $request): JsonResponse
    {
        $province = $request->query('province', 'Siem Reap');
        $days = (int) $request->query('days', 3);

        $result = $this->aiService->getWeather($province, $days);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'Unable to fetch weather data.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'Weather data retrieved successfully.'
        );
    }

    /**
     * Get verified Cambodian festivals and events.
     * GET /api/travel/ai/events or GET /api/travel/events
     */
    public function events(Request $request): JsonResponse
    {
        $province = $request->query('province');
        $month = $request->query('month');
        $query = $request->query('query');

        $result = $this->aiService->getEvents($province, $month, $query);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'Unable to fetch events.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'Events retrieved successfully.'
        );
    }

    /**
     * Get current USD <-> KHR reference exchange rate.
     * GET /api/travel/ai/currency or GET /api/travel/currency
     */
    public function currency(): JsonResponse
    {
        $result = $this->aiService->getCurrencyRate();

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'Unable to fetch currency rate.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'Exchange rate retrieved successfully.'
        );
    }

    /**
     * Convert currency between USD and KHR.
     * POST /api/travel/ai/currency/convert or POST /api/currency/convert
     */
    public function convertCurrency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'from_currency' => 'nullable|string|in:USD,KHR,usd,khr',
            'to_currency' => 'nullable|string|in:USD,KHR,usd,khr',
        ]);

        $result = $this->aiService->convertCurrency(
            (float) $validated['amount'],
            strtoupper($validated['from_currency'] ?? 'USD'),
            strtoupper($validated['to_currency'] ?? 'KHR')
        );

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'Currency conversion failed.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'Currency converted successfully.'
        );
    }

    /**
     * Get tailored Cambodian transit options, pricing, and tips.
     * GET /api/travel/ai/transport or GET /api/travel/transport
     */
    public function transport(Request $request): JsonResponse
    {
        $origin = $request->query('origin', 'Siem Reap');
        $destination = $request->query('destination', 'Siem Reap');
        $travelers = (int) $request->query('travelers', 2);

        $result = $this->aiService->getTransport($origin, $destination, $travelers);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'Unable to fetch transport options.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'Transport options retrieved successfully.'
        );
    }

    /**
     * Search AI tourism knowledge base.
     * POST /api/travel/ai/search or POST /api/search
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $result = $this->aiService->search($validated['query'], $validated['limit'] ?? 5);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'Search query failed.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'Search completed successfully.'
        );
    }

    /**
     * Summarize tourism topic.
     * POST /api/travel/ai/summary or POST /api/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:500',
            'length' => 'nullable|string|in:short,medium,long',
            'language' => 'nullable|string|max:10',
        ]);

        $result = $this->aiService->summarize(
            $validated['topic'],
            $validated['length'] ?? 'medium',
            $validated['language'] ?? 'en'
        );

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'Summary generation failed.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'Summary generated successfully.'
        );
    }

    /**
     * Check AI models and system status.
     * GET /api/travel/ai/status or GET /api/ai/status
     */
    public function status(): JsonResponse
    {
        $result = $this->aiService->getStatus();

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'AI service unavailable.',
                $result['status'] ?? 503
            );
        }

        return $this->successResponse(
            $result['data']['data'] ?? $result['data'],
            'AI system status retrieved successfully.'
        );
    }
}
