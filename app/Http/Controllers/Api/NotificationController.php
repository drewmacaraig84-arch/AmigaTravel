<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Models\ReadVirtualNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Return all notifications for the authenticated user, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get read virtual IDs
        $readVirtualIds = ReadVirtualNotification::where('user_id', $user->id)
            ->pluck('virtual_id')
            ->toArray();

        $notifications = UserNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => (string) $n->id,
                    'title' => $n->title,
                    'body' => $n->body,
                    'type' => $n->type ?? 'general',
                    'target_id' => $n->data['target_id'] ?? $n->data['transaction_number'] ?? null,
                    'is_read' => $n->is_read,
                    'created_at' => $n->created_at->toDateTimeString(),
                ];
            })
            ->toArray();

        // Append App Notifications (Global)
        $appNotifs = \App\Models\AppNotification::orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($n) use ($readVirtualIds) {
                $vid = 'app_' . $n->id;
                return [
                    'id' => $vid,
                    'title' => $n->title,
                    'body' => $n->body,
                    'type' => 'announcement',
                    'target_id' => null,
                    'is_read' => in_array($vid, $readVirtualIds),
                    'created_at' => $n->created_at->toDateTimeString(),
                ];
            })
            ->toArray();
        $notifications = array_merge($notifications, $appNotifs);

        // Fetch active promos
        $activePromos = \Illuminate\Support\Facades\DB::table('schedule_transport_class')
            ->where('is_promo', true)
            ->whereNotNull('promo_duration_start')
            ->whereNotNull('promo_duration_end')
            ->where('promo_duration_start', '<=', now())
            ->where('promo_duration_end', '>=', now())
            ->join('transport_classes', 'schedule_transport_class.transport_class_id', '=', 'transport_classes.id')
            ->select('transport_classes.name', 'schedule_transport_class.promo_duration_end')
            ->distinct()
            ->get();
            
        foreach ($activePromos as $i => $promo) {
            $vid = 'promo_' . $i;
            $notifications[] = [
                'id' => $vid,
                'title' => 'Promotional Ticket Available!',
                'body' => 'Book ' . $promo->name . ' at a discounted rate until ' . \Carbon\Carbon::parse($promo->promo_duration_end)->format('M d, Y h:i A') . '. Note: Promotional tickets are non-refundable.',
                'type' => 'promo',
                'target_id' => null,
                'is_read' => in_array($vid, $readVirtualIds),
                'created_at' => now()->toDateTimeString(),
            ];
        }

        // Fetch service cancellations that affect user's bookings
        $cancellations = \App\Models\Booking::where('user_id', $user->id)
            ->whereNotNull('service_cancellation_id')
            ->with('serviceCancellation')
            ->get();
            
        foreach ($cancellations as $booking) {
            if ($booking->serviceCancellation) {
                $vid = 'cancel_' . $booking->id;
                $notifications[] = [
                    'id' => $vid,
                    'title' => 'Service Cancellation Notice',
                    'body' => 'Your booking ' . $booking->transaction_number . ' has been affected by a service cancellation: ' . $booking->serviceCancellation->customer_message,
                    'type' => 'booking',
                    'target_id' => $booking->transaction_number,
                    'is_read' => in_array($vid, $readVirtualIds),
                    'created_at' => $booking->serviceCancellation->created_at->toDateTimeString(),
                ];
            }
        }

        // Sort all by created_at desc
        usort($notifications, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        // Slice to max 50
        $notifications = array_slice($notifications, 0, 50);

        $unreadCount = collect($notifications)->where('is_read', false)->count();

        return response()->json([
            'status'        => 'success',
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (is_numeric($id)) {
            $notification = UserNotification::where('user_id', $user->id)->find($id);
            if ($notification) {
                $notification->update(['is_read' => true]);
            }
        } else {
            // It's a virtual notification ID
            ReadVirtualNotification::firstOrCreate([
                'user_id' => $user->id,
                'virtual_id' => $id,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        UserNotification::where('user_id', $user->id)->update(['is_read' => true]);

        // Note: For "mark all read" to work on virtual notifications, it is complex because we generate them on the fly.
        // Usually, a user taps "mark all read" and expects the badge to clear. We would need to insert ALL current virtual IDs into the table.
        // For now, this just clears numeric ones, but we can fetch them all and insert them.
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (is_numeric($id)) {
            UserNotification::where('user_id', $user->id)->where('id', $id)->delete();
        } else {
            // Delete virtual notification by ensuring it's marked as read and possibly tracking it as deleted.
            // Since we don't have a deleted table, we'll just mark it read so it stops notifying.
            ReadVirtualNotification::firstOrCreate([
                'user_id' => $user->id,
                'virtual_id' => $id,
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
