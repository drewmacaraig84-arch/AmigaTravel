<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Models\ReadVirtualNotification;
use App\Models\DeletedVirtualNotification;
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

        // Get deleted virtual IDs
        $deletedVirtualIds = DeletedVirtualNotification::where('user_id', $user->id)
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
            ->limit(20)
            ->get()
            ->filter(function ($n) use ($deletedVirtualIds) {
                return !in_array('app_' . $n->id, $deletedVirtualIds);
            })
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
            ->select('schedule_transport_class.id', 'transport_classes.name', 'schedule_transport_class.promo_duration_end')
            ->get();
            
        foreach ($activePromos as $promo) {
            $vid = 'promo_' . $promo->id;
            
            if (in_array($vid, $deletedVirtualIds)) {
                continue;
            }

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
        $cancellations = \App\Models\Booking::where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if (filled($user->email)) {
                    $q->orWhere('client_email', $user->email);
                }
            })
            ->whereNotNull('service_cancellation_id')
            ->with('serviceCancellation')
            ->get();
            
        foreach ($cancellations as $booking) {
            if ($booking->serviceCancellation) {
                $cancellation = $booking->serviceCancellation;
                $isResumed = ! empty($cancellation->resume_date);
                $vid = ($isResumed ? 'resume_' : 'cancel_') . $booking->id;

                if (in_array($vid, $deletedVirtualIds)) {
                    continue;
                }

                if ($isResumed) {
                    $resumeFormatted = $cancellation->resume_date ? $cancellation->resume_date->format('M d, Y') : 'soon';
                    $title = '🟢 Operations Resumed: Select New Date';
                    $body = "Travel operations for booking #{$booking->transaction_number} are resuming starting {$resumeFormatted}! Tap to select your replacement schedule at zero extra cost.";
                    $type = 'service_resumption';
                    $createdAt = $cancellation->resumed_at 
                        ? $cancellation->resumed_at->toDateTimeString() 
                        : $cancellation->updated_at->toDateTimeString();
                } else {
                    $title = '🔴 Schedule Cancellation Notice';
                    $body = "Your booking #{$booking->transaction_number} has been affected by a service cancellation: {$cancellation->customer_message}";
                    $type = 'service_cancellation';
                    $createdAt = $cancellation->created_at->toDateTimeString();
                }

                $notifications[] = [
                    'id' => $vid,
                    'title' => $title,
                    'body' => $body,
                    'type' => $type,
                    'target_id' => $booking->transaction_number,
                    'is_read' => in_array($vid, $readVirtualIds),
                    'created_at' => $createdAt,
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

        // To mark all virtuals as read, we just pull the current list and insert them all.
        $virtualIds = $this->getCurrentVirtualIds($user);
        
        foreach ($virtualIds as $vid) {
            ReadVirtualNotification::firstOrCreate([
                'user_id' => $user->id,
                'virtual_id' => $vid,
            ]);
        }
        
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
            DeletedVirtualNotification::firstOrCreate([
                'user_id' => $user->id,
                'virtual_id' => $id,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $user = $request->user();
        UserNotification::where('user_id', $user->id)->delete();

        $virtualIds = $this->getCurrentVirtualIds($user);
        foreach ($virtualIds as $vid) {
            DeletedVirtualNotification::firstOrCreate([
                'user_id' => $user->id,
                'virtual_id' => $vid,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    private function getCurrentVirtualIds($user): array
    {
        $ids = [];

        $appNotifs = \App\Models\AppNotification::orderByDesc('created_at')->limit(20)->get();
        foreach ($appNotifs as $n) {
            $ids[] = 'app_' . $n->id;
        }

        $activePromos = \Illuminate\Support\Facades\DB::table('schedule_transport_class')
            ->where('is_promo', true)
            ->whereNotNull('promo_duration_start')
            ->whereNotNull('promo_duration_end')
            ->where('promo_duration_start', '<=', now())
            ->where('promo_duration_end', '>=', now())
            ->get();
        foreach ($activePromos as $promo) {
            $ids[] = 'promo_' . $promo->id;
        }

        $cancellations = \App\Models\Booking::where('user_id', $user->id)
            ->whereNotNull('service_cancellation_id')
            ->get();
        foreach ($cancellations as $booking) {
            $ids[] = 'cancel_' . $booking->id;
        }

        return $ids;
    }
}
