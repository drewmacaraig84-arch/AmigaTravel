<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
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

        $notifications = UserNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->toArray();

        // Append App Notifications (Global)
        $appNotifs = \App\Models\AppNotification::orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => 'app_' . $n->id,
                    'title' => $n->title,
                    'body' => $n->body,
                    'is_read' => false,
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
            $notifications[] = [
                'id' => 'promo_' . $i,
                'title' => 'Promotional Ticket Available!',
                'body' => 'Book ' . $promo->name . ' at a discounted rate until ' . \Carbon\Carbon::parse($promo->promo_duration_end)->format('M d, Y h:i A') . '. Note: Promotional tickets are non-refundable.',
                'is_read' => false,
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
                $notifications[] = [
                    'id' => 'cancel_' . $booking->id,
                    'title' => 'Service Cancellation Notice',
                    'body' => 'Your booking ' . $booking->transaction_number . ' has been affected by a service cancellation: ' . $booking->serviceCancellation->customer_message,
                    'is_read' => false,
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
        if (is_numeric($id)) {
            $user = $request->user();
            $notification = UserNotification::where('user_id', $user->id)->find($id);
            if ($notification) {
                $notification->update(['is_read' => true]);
            }
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

        return response()->json(['status' => 'success']);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        UserNotification::where('user_id', $user->id)->findOrFail($id)->delete();

        return response()->json(['status' => 'success']);
    }
}
