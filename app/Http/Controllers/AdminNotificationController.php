<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AdminNotificationFeed;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController
{
    public function dropdown(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $feed = app(AdminNotificationFeed::class);
        $allNotifications = $feed->getForUser($user);

        return response()->json([
            'total' => $allNotifications->count(),
            'unread' => $allNotifications->where('is_read', false)->count(),
            'notifications' => $allNotifications->take(5)->values(),
        ]);
    }

    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $feed = app(AdminNotificationFeed::class);
        $notifications = $feed->getForUser($user);

        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query = mb_strtolower($search);
            $notifications = $notifications->filter(function (array $notification) use ($query) {
                return str_contains(mb_strtolower($notification['title'] ?? ''), $query)
                    || str_contains(mb_strtolower($notification['message'] ?? ''), $query);
            })->values();
        }

        // Optional: filter to unread only (for Unread tab on full page)
        if (filter_var($request->query('unread_only', false), FILTER_VALIDATE_BOOLEAN)) {
            $notifications = $notifications->where('is_read', false)->values();
        }

        $perPage = max(5, min(50, (int) $request->query('per_page', 10)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = new LengthAwarePaginator(
            $notifications->forPage($page, $perPage)->values(),
            $notifications->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        return response()->json([
            'notifications' => $paginator->items(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'unread' => $notifications->where('is_read', false)->count(),
        ]);
    }

    public function markRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $notificationIds = $this->validateIds($request);
        $count = app(AdminNotificationFeed::class)->markAsRead($user, $notificationIds);

        return response()->json([
            'success' => true,
            'count' => $count,
            'unread' => app(AdminNotificationFeed::class)->getUnreadCountForUser($user),
        ]);
    }

    public function markAllRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $count = app(AdminNotificationFeed::class)->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'count' => $count,
            'unread' => 0,
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function markUnread(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $notificationIds = $this->validateIds($request);
        $count = app(AdminNotificationFeed::class)->markAsUnread($user, $notificationIds);

        return response()->json([
            'success' => true,
            'count' => $count,
            'unread' => app(AdminNotificationFeed::class)->getUnreadCountForUser($user),
        ]);
    }

    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $notificationIds = $this->validateIds($request);
        $count = app(AdminNotificationFeed::class)->deleteForUser($user, $notificationIds);

        return response()->json([
            'success' => true,
            'count' => $count,
            'unread' => app(AdminNotificationFeed::class)->getUnreadCountForUser($user),
            'total' => app(AdminNotificationFeed::class)->getTotalCountForUser($user),
        ]);
    }

    protected function validateIds(Request $request): array
    {
        return $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'],
        ])['ids'];
    }
}
