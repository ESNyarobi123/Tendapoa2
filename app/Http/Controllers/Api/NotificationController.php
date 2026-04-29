<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * In-app notification APIs (Laravel database notifications).
 *
 * Endpoints:
 *   GET    /api/notifications                — paginated list + unread count
 *   GET    /api/notifications/unread-count   — quick poll endpoint (badge updates)
 *   POST   /api/notifications/{id}/read      — mark single notification as read
 *   POST   /api/notifications/read-all       — mark all unread as read
 *   DELETE /api/notifications/{id}           — delete a notification
 *   DELETE /api/notifications                — clear all notifications
 *
 * Each notification's `data` payload uses a consistent shape:
 *   { type, title, message, action_url, ...extra }
 */
class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     *
     * Query params:
     *   filter = all | unread | read   (default: all)
     *   per_page = 20 (max 50)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filter = $request->get('filter', 'all');
        $perPage = min((int) $request->get('per_page', 20), 50);

        $query = $user->notifications()->latest();
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $paginator->getCollection()->map(fn ($n) => $this->present($n))->values(),
                'unread_count' => $user->unreadNotifications()->count(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * GET /api/notifications/unread-count — lightweight badge polling endpoint.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * POST /api/notifications/{id}/read
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->find($id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification haipo.',
            ], 404);
        }

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Imewekwa kama imesomwa.',
            'data' => [
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * POST /api/notifications/read-all
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => "Notifications {$count} zimewekwa kama zimesomwa.",
            'data' => ['unread_count' => 0, 'marked_read' => $count],
        ]);
    }

    /**
     * DELETE /api/notifications/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->find($id);
        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification haipo.',
            ], 404);
        }
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification imefutwa.',
            'data' => ['unread_count' => $user->unreadNotifications()->count()],
        ]);
    }

    /**
     * DELETE /api/notifications — clear all.
     */
    public function clear(Request $request): JsonResponse
    {
        $count = $request->user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => "Notifications {$count} zimefutwa.",
            'data' => ['unread_count' => 0, 'deleted' => $count],
        ]);
    }

    /**
     * Present a single notification with a consistent, mobile-friendly shape.
     */
    private function present($notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? class_basename($notification->type),
            'title' => $data['title'] ?? null,
            'message' => $data['message'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'data' => $data, // Full original payload (job_id, worker_id, etc.)
            'read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
            'created_at_human' => $notification->created_at?->diffForHumans(),
        ];
    }
}
