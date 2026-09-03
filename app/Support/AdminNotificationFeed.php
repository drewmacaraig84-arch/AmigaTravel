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

    public static function clearAllCache(): void
    {
        Cache::increment('admin_notification_feed_v');
    }

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
                $autoRead = ! empty($notification['auto_read']);
                $explicitlyUnread = ($status !== null && $status->read_at === null);
                $isRead = ($autoRead && ! $explicitlyUnread) || ($status?->read_at !== null);

                return array_merge($notification, [
                    'is_read' => $isRead,
                    'read_at' => $status?->read_at ?? ($isRead ? ($notification['created_at'] ?? now()) : null),
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
        $v = Cache::get('admin_notification_feed_v', 1);
        return "admin_notification_feed:{$user->id}:v{$v}";
    }

    protected function statusCacheKey(User $user): string
    {
        $v = Cache::get('admin_notification_feed_v', 1);
        return "admin_notification_feed_statuses:{$user->id}:v{$v}";
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

    public function markAllAsRead(User $user): int
    {
        $allIds = $this->getForUser($user)
            ->where('is_read', false)
            ->pluck('id')
            ->all();

        if (empty($allIds)) {
            return 0;
        }

        return $this->markAsRead($user, $allIds);
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

    public function markBookingNotificationsAsRead(User $user, int $bookingId): int
    {
        $targetIds = [
            'booking-new-' . $bookingId,
            'booking-cancel-' . $bookingId,
            'booking-rebook-' . $bookingId,
            'booking-refund-req-' . $bookingId,
            'booking-refund-done-' . $bookingId,
            'booking-op-rebook-' . $bookingId,
        ];

        return $this->markAsRead($user, $targetIds);
    }

    protected function collectNotifications(): Collection
    {
        $notifications = collect();

        $bookings = Booking::query()
            ->with('passengers')
            ->latest('updated_at')
            ->limit(30)
            ->get();

        foreach ($bookings as $booking) {
            // Helper: get a short passenger label for item-level notifications
            $itemLabel = function (Booking $b, array $statusFilter = [], bool $withRefund = false): string {
                $passengers = $b->passengers;
                if ($passengers->isEmpty()) {
                    return $b->client_name;
                }
                if (! empty($statusFilter)) {
                    $filtered = $passengers->filter(fn ($p) => in_array($p->status, $statusFilter));
                } elseif ($withRefund) {
                    $filtered = $passengers->filter(fn ($p) => (float) $p->refund_amount > 0);
                } else {
                    $filtered = $passengers;
                }
                if ($filtered->isEmpty()) {
                    $filtered = $passengers;
                }
                $filtered = $filtered->sortBy('item_number');
                if ($filtered->count() === $passengers->count()) {
                    return $b->client_name; // all passengers → use client name
                }
                return $filtered->map(fn ($p) => ($p->name ?? 'Passenger') . " (Item {$p->item_number})")->implode(', ');
            };

            // Refund Notifications for Admin
            if (in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0) {
                $paxLabel = $itemLabel($booking, [], true);
                if ($booking->isRefundCompleted()) {
                    $notifications->push([
                        'id' => 'booking-refund-done-' . $booking->id,
                        'type' => 'refund_completed',
                        'title' => 'Refund Disbursed',
                        'message' => "Refund of ₱" . number_format((float) $booking->refund_amount, 2) . " disbursed for {$paxLabel} in #{$booking->transaction_number}" . (filled($booking->refund_reference) ? " (Ref: {$booking->refund_reference})" : ""),
                        'created_at' => $booking->refund_processed_at ?? $booking->updated_at ?? $booking->created_at,
                        'url' => '/admin/refunds',
                        'auto_read' => true,
                    ]);
                } else {
                    $notifications->push([
                        'id' => 'booking-refund-req-' . $booking->id,
                        'type' => 'refund_request',
                        'title' => 'Refund Request Pending',
                        'message' => "{$paxLabel} requested ₱" . number_format((float) $booking->refund_amount, 2) . " refund for #{$booking->transaction_number}",
                        'created_at' => $booking->updated_at ?? $booking->created_at,
                        'url' => '/admin/refunds',
                        'auto_read' => false,
                    ]);
                }
            } elseif ($booking->status === 'cancelled') {
                $paxLabel = $itemLabel($booking, ['cancelled', 'operator_cancelled']);
                $notifications->push([
                    'id' => 'booking-cancel-' . $booking->id,
                    'type' => 'cancellation',
                    'title' => 'Booking cancelled',
                    'message' => "{$paxLabel} cancelled booking #" . $booking->transaction_number,
                    'created_at' => $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/bookings/' . $booking->id,
                    'auto_read' => false,
                ]);
            }

            // Booking notification
            if (! $booking->is_rebooked) {
                $isPending = ($booking->status === 'pending');
                $notifications->push([
                    'id' => 'booking-new-' . $booking->id,
                    'type' => 'new_booking',
                    'title' => $isPending ? 'New booking' : 'Booking ' . ucfirst($booking->status),
                    'message' => $booking->client_name . ' placed booking #' . $booking->transaction_number,
                    'created_at' => $booking->created_at,
                    'url' => '/admin/bookings/' . $booking->id,
                    'auto_read' => ! $isPending,
                ]);
            }

            if ($booking->is_rebooked) {
                $isPendingRebook = ($booking->rebooking_status === 'pending');
                $paxLabel = $itemLabel($booking, ['rebooking_pending', 'operator_rebooking', 'rebooked']);
                $notifications->push([
                    'id' => 'booking-rebook-' . $booking->id,
                    'type' => 'rebooking',
                    'title' => $isPendingRebook ? 'Rebooking request' : 'Rebooking ' . ucfirst($booking->rebooking_status ?? 'processed'),
                    'message' => "{$paxLabel} submitted a rebooking request for #{$booking->transaction_number}",
                    'created_at' => $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/manage-rebookings',
                    'auto_read' => ! $isPendingRebook,
                ]);
            }

            // Operator Reschedule Request Notification
            if ($booking->status === 'operator_rebooking' || ($booking->isServiceCancellation() && $booking->disruption_status === 'reschedule_requested')) {
                $isPendingReschedule = ($booking->rebooking_status === 'reschedule_requested');
                $paxLabel = $itemLabel($booking, ['operator_rebooking', 'rebooking_pending']);
                $notifications->push([
                    'id' => 'booking-op-rebook-' . $booking->id,
                    'type' => 'operator_reschedule_request',
                    'title' => 'Operator Reschedule Request',
                    'message' => "{$paxLabel} requested replacement schedule for cancelled trip #{$booking->transaction_number}",
                    'created_at' => $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/manage-rebookings',
                    'auto_read' => ! $isPendingReschedule,
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
