// ignore_for_file: deprecated_member_use
// =============================================================================
// HMS Push Kit Notification Service (HarmonyOS)
// Replaces firebase_messaging + flutter_local_notifications on OHOS platform.
// Uses platform channels to communicate with the native ArkTS HmsEntryAbility.
// =============================================================================

import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'dart:convert';

/// Background isolate entry-point called by the native ArkTS layer.
/// Must be a top-level function annotated with @pragma('vm:entry-point').
@pragma('vm:entry-point')
Future<void> hmsBackgroundMessageHandler(Map<String, dynamic> data) async {
  debugPrint('[HMS] Background message received: $data');
}

class HmsNotificationService {
  // ── Platform channel names ────────────────────────────────────────────────
  static const MethodChannel _channel =
      MethodChannel('com.amiga.travel.flutter_app/hms_push');
  static const EventChannel _messageChannel =
      EventChannel('com.amiga.travel.flutter_app/hms_push_events');

  static const String _hmsChannelId = 'amiga_travel_alerts';
  static const String _hmsChannelName = 'Amiga Travel Alerts';

  static Function(Map<String, dynamic>)? _onNotificationTap;

  // ── Initialisation ────────────────────────────────────────────────────────

  static Future<void> initialize({
    Function(Map<String, dynamic>)? onNotificationTap,
  }) async {
    if (kIsWeb) return;

    _onNotificationTap = onNotificationTap;

    try {
      // 1. Init HMS Push on native side & retrieve push token
      final String? token = await _channel.invokeMethod<String>('initialize', {
        'channelId': _hmsChannelId,
        'channelName': _hmsChannelName,
        'channelDescription':
            'Real-time booking status, cancellations, and vouchers.',
      });
      debugPrint('[HMS] Push token: $token');

      // 2. Subscribe to global topic
      await subscribeToTopic('all_users');

      // 3. Listen for foreground + tap events from native layer
      _messageChannel.receiveBroadcastStream().listen((dynamic event) {
        try {
          final Map<String, dynamic> data = event is String
              ? Map<String, dynamic>.from(jsonDecode(event))
              : Map<String, dynamic>.from(event as Map);

          final String eventType = (data['event_type'] ?? 'message').toString();

          if (eventType == 'notification_tap' && _onNotificationTap != null) {
            final payload = data['payload'];
            if (payload is String) {
              _onNotificationTap!(jsonDecode(payload) as Map<String, dynamic>);
            } else if (payload is Map) {
              _onNotificationTap!(Map<String, dynamic>.from(payload));
            }
          } else if (eventType == 'message') {
            showNotification(
              id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
              title: data['title'] as String? ?? 'Amiga Travel',
              body: data['body'] as String? ?? '',
              payload: jsonEncode(data['data'] ?? {}),
            );
          }
        } catch (e) {
          debugPrint('[HMS] Event parse error: $e');
        }
      });

      // 4. Handle cold-start tap (app launched from terminated state notification)
      final Map<dynamic, dynamic>? initialData =
          await _channel.invokeMethod<Map<dynamic, dynamic>>('getInitialMessage');
      if (initialData != null && _onNotificationTap != null) {
        _onNotificationTap!(Map<String, dynamic>.from(initialData));
      }
    } catch (e) {
      debugPrint('[HMS] Initialization error: $e');
    }
  }

  // ── Permission request ────────────────────────────────────────────────────

  static Future<void> requestPermission() async {
    if (kIsWeb) return;
    try {
      await _channel.invokeMethod<void>('requestPermission');
    } catch (e) {
      debugPrint('[HMS] requestPermission error: $e');
    }
  }

  // ── Show local notification (heads-up banner) ─────────────────────────────

  static Future<void> showNotification({
    required int id,
    required String title,
    required String body,
    String? payload,
  }) async {
    if (kIsWeb) return;
    try {
      await _channel.invokeMethod<void>('showNotification', {
        'id': id,
        'title': title,
        'body': body,
        'payload': payload ?? '',
        'channelId': _hmsChannelId,
        'channelName': _hmsChannelName,
      });
    } catch (e) {
      debugPrint('[HMS] showNotification error: $e');
    }
  }

  // ── App icon badge ────────────────────────────────────────────────────────

  static Future<void> setBadge(int count) async {
    if (kIsWeb) return;
    try {
      await _channel.invokeMethod<void>('setBadge', {'count': count});
    } catch (e) {
      debugPrint('[HMS] setBadge error: $e');
    }
  }

  static Future<void> clearBadge() => setBadge(0);

  // ── Topic subscription ────────────────────────────────────────────────────

  static Future<void> subscribeToTopic(String topic) async {
    if (kIsWeb) return;
    try {
      final sanitized = _sanitizeTopic(topic);
      if (sanitized.isNotEmpty) {
        await _channel.invokeMethod<void>('subscribeToTopic', {'topic': sanitized});
        debugPrint('[HMS] Subscribed to topic: $sanitized');
      }
    } catch (e) {
      debugPrint('[HMS] subscribeToTopic error: $e');
    }
  }

  static Future<void> unsubscribeFromTopic(String topic) async {
    if (kIsWeb) return;
    try {
      final sanitized = _sanitizeTopic(topic);
      if (sanitized.isNotEmpty) {
        await _channel.invokeMethod<void>('unsubscribeFromTopic', {'topic': sanitized});
        debugPrint('[HMS] Unsubscribed from topic: $sanitized');
      }
    } catch (e) {
      debugPrint('[HMS] unsubscribeFromTopic error: $e');
    }
  }

  static Future<void> subscribeToUserTopic(dynamic userIdOrEmail) async {
    await subscribeToTopic(_sanitizeTopic(userIdOrEmail.toString()));
  }

  static Future<void> unsubscribeFromUserTopic(dynamic userIdOrEmail) async {
    await unsubscribeFromTopic(_sanitizeTopic(userIdOrEmail.toString()));
  }

  static String _sanitizeTopic(String input) {
    if (input.isEmpty) return '';
    if (int.tryParse(input) != null) return 'user_$input';
    if (input.startsWith('user_')) {
      return input.replaceAll(RegExp(r'[^a-zA-Z0-9-_.~%+]'), '_');
    }
    return 'user_${input.replaceAll(RegExp(r'[^a-zA-Z0-9-_.~%+]'), '_')}';
  }
}
