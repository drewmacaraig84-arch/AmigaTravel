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
        $allNotifications = $this->collectNotifications();
        $needle = '-' . $bookingId;

        $targetIds = $allNotifications
            ->filter(fn ($n) => str_contains((string) ($n['id'] ?? ''), $needle))
            ->pluck('id')
            ->toArray();

        $defaultIds = [
            'booking-new-' . $bookingId,
            'booking-cancel-' . $bookingId,
            'booking-rebook-' . $bookingId,
            'booking-refund-req-' . $bookingId,
            'booking-refund-done-' . $bookingId,
            'booking-op-rebook-' . $bookingId,
        ];

        return $this->markAsRead($user, array_values(array_unique(array_merge($targetIds, $defaultIds))));
    }

    protected function collectNotifications(): Collection
    {
        $notifications = collect();

        // 1. Fetch pending bookings, bookings with refund or rebooking activity (full or per-item)
        $pendingBookings = Booking::query()
            ->with(['passengers.discount', 'transaction'])
            ->where(function ($q) {
                $q->where('status', 'pending')
                    ->orWhere('status', 'refund_pending')
                    ->orWhere('refund_status', 'pending')
                    ->orWhere('status', 'pending_rebooking')
                    ->orWhere('status', 'operator_rebooking')
                    ->orWhere('rebooking_status', 'pending')
                    ->orWhere('is_rebooked', true)
                    ->orWhereHas('passengers', function ($pq) {
                        $pq->whereIn('status', ['refund_pending', 'rebooking_pending', 'operator_rebooking'])
                           ->orWhere('refund_status', 'pending')
                           ->orWhere('rebooking_status', 'pending');
                    });
            })
            ->latest('updated_at')
            ->limit(50)
            ->get();

        // 2. Also fetch latest updated bookings so recently active bookings are included
        $recentBookings = Booking::query()
            ->with(['passengers.discount', 'transaction'])
            ->latest('updated_at')
            ->limit(30)
            ->get();

        $bookings = $pendingBookings->concat($recentBookings)
            ->unique('id')
            ->sortByDesc('updated_at')
            ->values();

        foreach ($bookings as $booking) {
            $passengers = $booking->passengers->sortBy('item_number');

            // ─── Refund Notifications (Per-Item & Booking Level) ───
            $pendingRefundPax = $passengers->filter(fn ($p) =>
                $p->status === 'refund_pending' || $p->refund_status === 'pending'
            );
            $completedRefundPax = $passengers->filter(fn ($p) =>
                $p->status === 'refunded' || $p->refund_status === 'completed'
            );

            if ($pendingRefundPax->isNotEmpty()) {
                // Item-level pending refund notifications
                foreach ($pendingRefundPax as $p) {
                    $refundAmt = (float) ($p->refund_amount > 0 ? $p->refund_amount : $p->getRefundAmount());
                    $notifications->push([
                        'id' => 'booking-refund-req-' . $booking->id . '-item-' . $p->item_number,
                        'type' => 'refund_request',
                        'title' => 'Refund Request (Item ' . $p->item_number . ')',
                        'message' => ($p->name ?? 'Passenger') . " (Item {$p->item_number}) requested ₱" . number_format($refundAmt, 2) . " refund for #{$booking->transaction_number}",
                        'created_at' => $p->updated_at ?? $booking->updated_at ?? $booking->created_at,
                        'url' => '/admin/refunds',
                        'auto_read' => false,
                    ]);
                }
            } elseif ($booking->refund_status === 'pending' || $booking->status === 'refund_pending' || (in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0 && ! $booking->isRefundCompleted())) {
                // Booking-level pending refund fallback
                $notifications->push([
                    'id' => 'booking-refund-req-' . $booking->id,
                    'type' => 'refund_request',
                    'title' => 'Refund Request Pending',
                    'message' => "{$booking->client_name} requested ₱" . number_format((float) $booking->refund_amount, 2) . " refund for #{$booking->transaction_number}",
                    'created_at' => $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/refunds',
                    'auto_read' => false,
                ]);
            }

            if ($completedRefundPax->isNotEmpty()) {
                // Item-level completed refund notifications
                foreach ($completedRefundPax as $p) {
                    $notifications->push([
                        'id' => 'booking-refund-done-' . $booking->id . '-item-' . $p->item_number,
                        'type' => 'refund_completed',
                        'title' => 'Refund Disbursed (Item ' . $p->item_number . ')',
                        'message' => "Refund of ₱" . number_format((float) $p->refund_amount, 2) . " disbursed for " . ($p->name ?? 'Passenger') . " (Item {$p->item_number}) in #{$booking->transaction_number}" . (filled($booking->refund_reference) ? " (Ref: {$booking->refund_reference})" : ""),
                        'created_at' => $p->refund_processed_at ?? $booking->refund_processed_at ?? $booking->updated_at ?? $booking->created_at,
                        'url' => '/admin/refunds',
                        'auto_read' => true,
                    ]);
                }
            } elseif ($booking->isRefundCompleted()) {
                $notifications->push([
                    'id' => 'booking-refund-done-' . $booking->id,
                    'type' => 'refund_completed',
                    'title' => 'Refund Disbursed',
                    'message' => "Refund of ₱" . number_format((float) $booking->refund_amount, 2) . " disbursed for {$booking->client_name} in #{$booking->transaction_number}" . (filled($booking->refund_reference) ? " (Ref: {$booking->refund_reference})" : ""),
                    'created_at' => $booking->refund_processed_at ?? $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/refunds',
                    'auto_read' => true,
                ]);
            } elseif ($booking->status === 'cancelled' && (float) $booking->refund_amount <= 0 && $pendingRefundPax->isEmpty()) {
                $notifications->push([
                    'id' => 'booking-cancel-' . $booking->id,
                    'type' => 'cancellation',
                    'title' => 'Booking cancelled',
                    'message' => "{$booking->client_name} cancelled booking #" . $booking->transaction_number,
                    'created_at' => $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/bookings/' . $booking->id,
                    'auto_read' => false,
                ]);
            }

            // ─── Rebooking Notifications (Per-Item & Booking Level) ───
            $pendingRebookPax = $passengers->filter(fn ($p) =>
                $p->rebooking_status === 'pending' || $p->status === 'rebooking_pending'
            );

            if ($pendingRebookPax->isNotEmpty()) {
                foreach ($pendingRebookPax as $p) {
                    $notifications->push([
                        'id' => 'booking-rebook-' . $booking->id . '-item-' . $p->item_number,
                        'type' => 'rebooking',
                        'title' => 'Rebooking Request (Item ' . $p->item_number . ')',
                        'message' => ($p->name ?? 'Passenger') . " (Item {$p->item_number}) submitted a rebooking request for #{$booking->transaction_number}",
                        'created_at' => $p->updated_at ?? $booking->updated_at ?? $booking->created_at,
                        'url' => '/admin/manage-rebookings',
                        'auto_read' => false,
                    ]);
                }
            } elseif ($booking->rebooking_status === 'pending' || $booking->status === 'pending_rebooking' || $booking->is_rebooked) {
                $isPendingRebook = ($booking->rebooking_status === 'pending' || $booking->status === 'pending_rebooking');
                $notifications->push([
                    'id' => 'booking-rebook-' . $booking->id,
                    'type' => 'rebooking',
                    'title' => $isPendingRebook ? 'Rebooking request' : 'Rebooking ' . ucfirst($booking->rebooking_status ?? 'processed'),
                    'message' => "{$booking->client_name} submitted a rebooking request for #{$booking->transaction_number}",
                    'created_at' => $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/manage-rebookings',
                    'auto_read' => ! $isPendingRebook,
                ]);
            }

            // ─── Operator Reschedule Request ───
            if ($booking->status === 'operator_rebooking' || ($booking->isServiceCancellation() && $booking->disruption_status === 'reschedule_requested')) {
                $isPendingReschedule = ($booking->rebooking_status === 'reschedule_requested' || $booking->status === 'operator_rebooking');
                $notifications->push([
                    'id' => 'booking-op-rebook-' . $booking->id,
                    'type' => 'operator_reschedule_request',
                    'title' => 'Operator Reschedule Request',
                    'message' => "{$booking->client_name} requested replacement schedule for cancelled trip #{$booking->transaction_number}",
                    'created_at' => $booking->updated_at ?? $booking->created_at,
                    'url' => '/admin/manage-rebookings',
                    'auto_read' => ! $isPendingReschedule,
                ]);
            }

            // ─── Booking Creation / Status Notification ───
            if (! $booking->is_rebooked && $booking->status !== 'pending_rebooking') {
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
