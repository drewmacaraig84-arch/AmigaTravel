import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_app_badger/flutter_app_badger.dart';
import 'dart:convert';

class NotificationService {
  static final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();

  static const String _channelId = 'amiga_travel_alerts';
  static const String _channelName = 'Amiga Travel Alerts';
  static const String _channelDescription =
      'Shows real-time heads-up pop-up banners for booking status, cancellations, and vouchers.';

  // ─── Initialisation ────────────────────────────────────────────────────────

  static Future<void> initialize({
    Function(Map<String, dynamic>)? onNotificationTap,
  }) async {
    if (kIsWeb) return;

    try {
      // Use the standard launcher icon as default notification icon
      const androidInit =
          AndroidInitializationSettings('@mipmap/launcher_icon');

      const iosInit = DarwinInitializationSettings(
        requestAlertPermission: false,
        requestBadgePermission: true,
        requestSoundPermission: false,
      );

      const initSettings = InitializationSettings(
        android: androidInit,
        iOS: iosInit,
      );

      await _plugin.initialize(
        initSettings,
        onDidReceiveNotificationResponse: (NotificationResponse response) {
          if (onNotificationTap == null) return;
          if (response.payload != null && response.payload!.isNotEmpty) {
            try {
              onNotificationTap(jsonDecode(response.payload!));
            } catch (_) {
              onNotificationTap({});
            }
          } else {
            onNotificationTap({});
          }
        },
      );

      // Explicitly register the high importance channel with Android OS
      const AndroidNotificationChannel channel = AndroidNotificationChannel(
        _channelId,
        _channelName,
        description: _channelDescription,
        importance: Importance.max,
        playSound: true,
        enableVibration: true,
        showBadge: true,
      );

      final androidPlugin = _plugin
          .resolvePlatformSpecificImplementation<
              AndroidFlutterLocalNotificationsPlugin>();
      if (androidPlugin != null) {
        await androidPlugin.createNotificationChannel(channel);
      }
    } catch (e) {
      debugPrint('NotificationService initialize error: $e');
    }
  }

  // ─── Runtime permission request ────────────────────────────────────────────

  static Future<void> requestPermission() async {
    if (kIsWeb) return;

    try {
      // Android 13+ runtime permission
      final androidPlugin = _plugin
          .resolvePlatformSpecificImplementation<
              AndroidFlutterLocalNotificationsPlugin>();
      if (androidPlugin != null) {
        await androidPlugin.requestNotificationsPermission();
      }

      // iOS runtime permission (alert + badge + sound)
      final iosPlugin = _plugin
          .resolvePlatformSpecificImplementation<
              IOSFlutterLocalNotificationsPlugin>();
      if (iosPlugin != null) {
        await iosPlugin.requestPermissions(
            alert: true, badge: true, sound: true);
      }
    } catch (e) {
      debugPrint('NotificationService requestPermission error: $e');
    }
  }

  // ─── Banner notification (Shopee-style heads-up) ───────────────────────────

  static Future<void> showNotification({
    required int id,
    required String title,
    required String body,
    String? payload,
  }) async {
    if (kIsWeb) return;

    try {
      // Importance.max + Priority.high + channel settings → Heads-up banner
      const androidDetails = AndroidNotificationDetails(
        _channelId,
        _channelName,
        channelDescription: _channelDescription,
        importance: Importance.max,
        priority: Priority.high,
        icon: '@mipmap/launcher_icon',
        playSound: true,
        enableVibration: true,
        visibility: NotificationVisibility.public,
        channelShowBadge: true,
      );

      const iosDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      );

      const details = NotificationDetails(
        android: androidDetails,
        iOS: iosDetails,
      );

      await _plugin.show(id, title, body, details, payload: payload);
    } catch (e) {
      debugPrint('NotificationService showNotification error: $e');
    }
  }

  // ─── App icon badge (red number on launcher icon) ──────────────────────────

  static Future<void> setBadge(int count) async {
    if (kIsWeb) return;
    try {
      final isSupported = await FlutterAppBadger.isAppBadgeSupported();
      if (isSupported) {
        if (count > 0) {
          await FlutterAppBadger.updateBadgeCount(count);
        } else {
          await FlutterAppBadger.removeBadge();
        }
      }
    } catch (e) {
      debugPrint('NotificationService setBadge error: $e');
    }
  }

  static Future<void> clearBadge() => setBadge(0);

  // ─── Stubs kept for API compatibility ──────────────────────────────────────

  static Future<void> subscribeToUserTopic(String email) async {}
  static Future<void> unsubscribeFromUserTopic(String email) async {}
}
