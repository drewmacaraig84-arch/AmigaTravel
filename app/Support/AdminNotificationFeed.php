<?php

namespace App\Support;

use App\Models\AdminNotificationStatus;
use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AdminNotificationFeed
{
    private const CACHE_TTL = 30;

    protected array $notificationsByUser = [];
    protected array $statusesByUser = [];

    public function getForUser(User $user): Collection
    {
        return Cache::remember($this->cacheKey($user), self::CACHE_TTL, function () use ($user) {
            return $this->buildForUser($user);
        });
    }

    protected function buildForUser(User $user): Collection
    {
        $notifications = $this->collectNotifications();
        $statuses = $this->getStatusesForUser($user);

        return $notifications
            ->map(function (array $notification) use ($statuses): array {
                $status = $statuses->get($notification['id']);

                return array_merge($notification, [
                    'is_read' => $status?->read_at !== null,
                    'read_at' => $status?->read_at,
                ]);
            })
            ->filter(function (array $notification) use ($statuses): bool {
                $status = $statuses->get($notification['id']);

                return ! $status?->trashed();
            })
            ->sortByDesc('created_at')
            ->values();
    }

    protected function getStatusesForUser(User $user): Collection
    {
        return Cache::remember($this->statusCacheKey($user), self::CACHE_TTL, function () use ($user) {
            return AdminNotificationStatus::withTrashed()
                ->where('user_id', $user->id)
                ->get()
                ->keyBy('notification_id');
        });
    }

    protected function clearCacheForUser(User $user): void
    {
        Cache::forget($this->cacheKey($user));
        Cache::forget($this->statusCacheKey($user));
        unset($this->notificationsByUser[$user->id], $this->statusesByUser[$user->id]);
    }

    protected function cacheKey(User $user): string
    {
        return "admin_notification_feed:{$user->id}";
    }

    protected function statusCacheKey(User $user): string
    {
        return "admin_notification_feed_statuses:{$user->id}";
    }

    public function getUnreadCountForUser(User $user): int
    {
        return $this->getForUser($user)->where('is_read', false)->count();
    }

    public function getTotalCountForUser(User $user): int
    {
        return $this->getForUser($user)->count();
    }

    public function markAsRead(User $user, array $notificationIds): int
    {
        $updated = 0;

        foreach ($notificationIds as $notificationId) {
            $status = AdminNotificationStatus::withTrashed()
                ->updateOrCreate(
                    ['user_id' => $user->id, 'notification_id' => $notificationId],
                    ['read_at' => now(), 'deleted_at' => null],
                );

            if ($status->wasRecentlyCreated || $status->wasChanged()) {
                $updated++;
            }
        }

        $this->clearCacheForUser($user);

        return $updated;
    }

    public function markAsUnread(User $user, array $notificationIds): int
    {
        $updated = 0;

        foreach ($notificationIds as $notificationId) {
            $status = AdminNotificationStatus::withTrashed()
                ->updateOrCreate(
                    ['user_id' => $user->id, 'notification_id' => $notificationId],
                    ['read_at' => null, 'deleted_at' => null],
                );

            if ($status->wasRecentlyCreated || $status->wasChanged()) {
                $updated++;
            }
        }

        $this->clearCacheForUser($user);

        return $updated;
    }

    public function deleteForUser(User $user, array $notificationIds): int
    {
        $deleted = 0;

        foreach ($notificationIds as $notificationId) {
            $status = AdminNotificationStatus::withTrashed()
                ->updateOrCreate(
                    ['user_id' => $user->id, 'notification_id' => $notificationId],
                    ['read_at' => null, 'deleted_at' => now()],
                );

            if ($status->wasRecentlyCreated || $status->wasChanged()) {
                $deleted++;
            }
        }

        $this->clearCacheForUser($user);

        return $deleted;
    }

    protected function collectNotifications(): Collection
    {
        $notifications = collect();

        $bookings = Booking::query()
            ->latest('created_at')
            ->limit(20)
            ->get();

        foreach ($bookings as $booking) {
            if ($booking->status === 'cancelled') {
                $notifications->push([
                    'id' => 'booking-cancel-' . $booking->id,
                    'type' => 'cancellation',
                    'title' => 'Booking cancelled',
                    'message' => $booking->client_name . ' cancelled booking #' . $booking->transaction_number,
                    'created_at' => $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/bookings/' . $booking->id,
                ]);
            }

            if ($booking->status === 'pending' && ! $booking->is_rebooked) {
                $notifications->push([
                    'id' => 'booking-new-' . $booking->id,
                    'type' => 'new_booking',
                    'title' => 'New booking',
                    'message' => $booking->client_name . ' placed booking #' . $booking->transaction_number,
                    'created_at' => $booking->created_at,
                    'url' => '/admin/bookings/' . $booking->id,
                ]);
            }

            if ($booking->is_rebooked && $booking->rebooking_status === 'pending') {
                $notifications->push([
                    'id' => 'booking-rebook-' . $booking->id,
                    'type' => 'rebooking',
                    'title' => 'Rebooking request',
                    'message' => $booking->client_name . ' submitted a rebooking request for #' . $booking->transaction_number,
                    'created_at' => $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/manage-rebookings',
                ]);
            }
        }

        $inquiries = Inquiry::query()
            ->latest('created_at')
            ->limit(20)
            ->get();

        foreach ($inquiries as $inquiry) {
            $notifications->push([
                'id' => 'inquiry-' . $inquiry->id,
                'type' => 'inquiry',
                'title' => 'New inquiry',
                'message' => $inquiry->name . ' sent an inquiry: ' . $inquiry->subject,
                'created_at' => $inquiry->created_at,
                'url' => '/admin',
            ]);
        }

        return $notifications;
    }
}
