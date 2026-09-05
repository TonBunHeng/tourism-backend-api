<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AiChatService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TravelAiChatController extends Controller
{
    use ApiResponse;

    protected AiChatService $aiService;

    public function __construct(AiChatService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle AI chat message and persist conversation history.
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

        $user = $request->user('sanctum') ?? $request->user();
        $sessionId = $validated['session_id'] ?? ($user ? ('user_' . $user->id . '_' . date('Ymd')) : ('guest_' . Str::random(16)));

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

        // Find or create AI Conversation
        $conversation = AiConversation::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user?->id,
                'title' => Str::limit($validated['message'], 40),
                'province' => $validated['province'] ?? null,
                'category' => $validated['category'] ?? null,
                'language' => $validated['language'] ?? 'en',
            ]
        );

        if ($user && !$conversation->user_id) {
            $conversation->update(['user_id' => $user->id]);
        }

        // Record User Message
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Send to AI Service
        $result = $this->aiService->sendMessage($validated['message'], $sessionId, $context);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'] ?? 'AI service is currently unavailable.',
                $result['status'] ?? 503
            );
        }

        $responseData = $result['data']['data'] ?? $result['data'];
        $assistantReply = $responseData['response'] ?? $responseData['message'] ?? $responseData['content'] ?? (is_string($responseData) ? $responseData : json_encode($responseData));

        // Record Assistant Message
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => is_string($assistantReply) ? $assistantReply : json_encode($assistantReply),
            'metadata' => is_array($responseData) ? $responseData : null,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $this->successResponse([
            'response' => $assistantReply,
            'session_id' => $sessionId,
            'data' => $responseData,
        ], 'AI response generated successfully.');
    }

    /**
     * Get user's past AI conversations.
     * GET /api/travel/ai/conversations
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Authentication required.', 401);
        }

        $conversations = AiConversation::where('user_id', $user->id)
            ->withCount('messages')
            ->orderBy('last_message_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Conversations retrieved successfully.',
            'data' => $conversations->items(),
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    /**
     * Get message history for a specific conversation session.
     * GET /api/travel/ai/conversations/{sessionId}/messages
     */
    public function getMessages(Request $request, string $sessionId): JsonResponse
    {
        $user = $request->user();
        $conversation = AiConversation::with('messages')
            ->where('session_id', $sessionId)
            ->first();

        if (!$conversation) {
            return $this->errorResponse('Conversation not found.', 404);
        }

        if ($conversation->user_id && (!$user || $conversation->user_id !== $user->id)) {
            return $this->errorResponse('Access denied.', 403);
        }

        return $this->successResponse(
            $conversation->messages,
            'Messages retrieved successfully.'
        );
    }

    /**
     * Clear a conversation session.
     * DELETE /api/travel/ai/conversations/{sessionId}
     */
    public function clearConversation(Request $request, string $sessionId): JsonResponse
    {
        $user = $request->user();
        $conversation = AiConversation::where('session_id', $sessionId)->first();

        if (!$conversation) {
            return $this->errorResponse('Conversation not found.', 404);
        }

        if ($conversation->user_id && (!$user || $conversation->user_id !== $user->id)) {
            return $this->errorResponse('Access denied.', 403);
        }

        $conversation->delete();

        return $this->successResponse(null, 'Conversation deleted successfully.');
    }

    /**
     * Generate multi-factor smart recommendations for Cambodia destinations.
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
