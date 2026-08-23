<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Travel\TravelGalleryCommentRequest;
use App\Http\Resources\Travel\TravelGalleryCommentResource;
use App\Http\Resources\Travel\TravelGalleryResource;
use App\Models\GalleryComment;
use App\Models\GalleryLike;
use App\Models\GalleryMedia;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TravelGalleryController extends Controller
{
    use ApiResponse;

    /**
     * Get paginated published gallery media items.
     */
    public function index(Request $request): JsonResponse
    {
        $query = GalleryMedia::with(['place', 'category', 'tags', 'allComments', 'likes'])
            ->where('status', 'Published');

        if ($placeId = $request->query('place_id')) {
            $query->where('place_id', $placeId);
        }

        if ($mediaType = $request->query('media_type') ?? $request->query('type')) {
            $query->where('type', $mediaType);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if ($tag = $request->query('tag')) {
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('tag_name', 'like', "%{$tag}%");
            });
        }

        $perPage = (int) $request->query('per_page', 12);
        $media = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->successResponse(
            TravelGalleryResource::collection($media),
            'Gallery media retrieved successfully.',
            200,
            [
                'total' => $media->total(),
                'per_page' => $media->perPage(),
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
            ]
        );
    }

    /**
     * Show single gallery media item & increment view count.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $media = GalleryMedia::with([
            'place',
            'category',
            'tags',
            'comments' => fn($q) => $q->whereNull('parent_id')->with(['user', 'replies.user'])->orderBy('created_at', 'desc'),
            'allComments',
            'likes',
        ])
            ->where('status', 'Published')
            ->find($id);

        if (!$media) {
            return $this->errorResponse('Gallery media not found.', 404);
        }

        $media->increment('views_count');
        $media->refresh();

        return $this->successResponse(
            new TravelGalleryResource($media),
            'Gallery media retrieved successfully.'
        );
    }

    /**
     * Increment view count when user is watching/viewing the media item.
     */
    public function recordView(string $id): JsonResponse
    {
        $media = GalleryMedia::find($id);

        if (!$media) {
            return $this->errorResponse('Gallery media not found.', 404);
        }

        $media->increment('views_count');
        $media->refresh();

        $views = (int) $media->views_count;

        return $this->successResponse([
            'id' => $media->id,
            'views_count' => $views,
            'view_count' => $views,
            'views' => $views,
        ], 'Media view count incremented successfully.');
    }

    /**
     * Get comments and replies for a gallery media item.
     */
    public function comments(string $id): JsonResponse
    {
        $media = GalleryMedia::find($id);

        if (!$media) {
            return $this->errorResponse('Gallery media not found.', 404);
        }

        $comments = GalleryComment::with(['user', 'replies.user'])
            ->where('gallery_media_id', $media->id)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            TravelGalleryCommentResource::collection($comments),
            'Gallery media comments retrieved successfully.'
        );
    }

    /**
     * Store a comment or reply for a gallery media item (strictly requires authenticated user).
     */
    public function storeComment(TravelGalleryCommentRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated. Please log in to comment.', 401);
        }

        $media = GalleryMedia::find($id);

        if (!$media) {
            return $this->errorResponse('Gallery media not found.', 404);
        }

        $validated = $request->validated();

        if (!empty($validated['parent_id'])) {
            $parentComment = GalleryComment::where('gallery_media_id', $media->id)
                ->find($validated['parent_id']);

            if (!$parentComment) {
                return $this->errorResponse('Parent comment not found for this media item.', 404);
            }
        }

        $comment = GalleryComment::create([
            'gallery_media_id' => $media->id,
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'comment' => $validated['comment'],
        ]);

        $comment->load(['user', 'replies.user']);

        return $this->successResponse(
            new TravelGalleryCommentResource($comment),
            $comment->parent_id ? 'Reply posted successfully.' : 'Comment posted successfully.',
            201
        );
    }

    /**
     * Delete own comment or reply (or Admin / Super Admin override).
     */
    public function deleteComment(Request $request, string $commentId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated. Please log in.', 401);
        }

        $comment = GalleryComment::find($commentId);

        if (!$comment) {
            return $this->errorResponse('Comment not found.', 404);
        }

        if ($comment->user_id !== $user->id && !$user->canModerate()) {
            return $this->errorResponse('Unauthorized. You do not have permission to delete this comment.', 403);
        }

        $comment->delete();

        return $this->successResponse(null, 'Comment deleted successfully.');
    }

    /**
     * Toggle like/unlike status on a gallery media item (strictly requires authenticated user).
     */
    public function toggleLike(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated. Please log in to like media.', 401);
        }

        $media = GalleryMedia::find($id);

        if (!$media) {
            return $this->errorResponse('Gallery media not found.', 404);
        }

        $existingLike = GalleryLike::where('gallery_media_id', $media->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $media->decrement('likes_count');
            $isLiked = false;
            $message = 'Media unliked successfully.';
        } else {
            GalleryLike::create([
                'gallery_media_id' => $media->id,
                'user_id' => $user->id,
            ]);
            $media->increment('likes_count');
            $isLiked = true;
            $message = 'Media liked successfully.';
        }

        $media->refresh();
        $likes = (int) $media->likes_count;

        return $this->successResponse([
            'id' => $media->id,
            'is_liked' => $isLiked,
            'isLiked' => $isLiked,
            'liked' => $isLiked,
            'likes_count' => $likes,
            'like_count' => $likes,
            'likes' => $likes,
        ], $message);
    }

    /**
     * Server-Sent Events (SSE) stream for real-time gallery updates (comments, likes, views).
     */
    public function stream(string $id): StreamedResponse
    {
        return response()->stream(function () use ($id) {
            $media = GalleryMedia::with([
                'comments' => fn($q) => $q->whereNull('parent_id')->with(['user', 'replies.user'])->orderBy('created_at', 'desc'),
                'allComments',
                'likes',
            ])->find($id);

            if (!$media) {
                echo "event: error\ndata: " . json_encode(['message' => 'Media not found']) . "\n\n";
                ob_flush();
                flush();
                return;
            }

            $views = (int) $media->views_count;
            $likes = (int) $media->likes_count;

            $payload = [
                'id' => $media->id,
                'views_count' => $views,
                'view_count' => $views,
                'views' => $views,
                'likes_count' => $likes,
                'like_count' => $likes,
                'likes' => $likes,
                'comments_count' => $media->allComments->count(),
                'comment_count' => $media->allComments->count(),
                'comments' => TravelGalleryCommentResource::collection($media->comments),
            ];

            echo "event: gallery_update\ndata: " . json_encode($payload) . "\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
