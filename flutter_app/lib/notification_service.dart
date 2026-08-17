import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_app_badger/flutter_app_badger.dart';
import 'dart:convert';

class NotificationService {
  static final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();

  // ─── Initialisation ────────────────────────────────────────────────────────

  static Future<void> initialize({
    Function(Map<String, dynamic>)? onNotificationTap,
  }) async {
    if (kIsWeb) return;

    // Android: reference the monochrome vector drawable in res/drawable/
    const androidInit = AndroidInitializationSettings('ic_notification');

    // iOS: request badge permission silently at init; alert/sound asked at runtime
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
  }

  // ─── Runtime permission request ────────────────────────────────────────────

  static Future<void> requestPermission() async {
    if (kIsWeb) return;

    // Android 13+ runtime permission
    await _plugin
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.requestNotificationsPermission();

    // iOS runtime permission (alert + badge + sound)
    await _plugin
        .resolvePlatformSpecificImplementation<
            IOSFlutterLocalNotificationsPlugin>()
        ?.requestPermissions(alert: true, badge: true, sound: true);
  }

  // ─── Banner notification (Shopee-style heads-up) ───────────────────────────

  static Future<void> showNotification({
    required int id,
    required String title,
    required String body,
    String? payload,
  }) async {
    if (kIsWeb) return;

    // Importance.max + Priority.high → Android shows heads-up banner on screen
    const androidDetails = AndroidNotificationDetails(
      'amiga_high_importance',
      'General Notifications',
      channelDescription: 'Used for booking alerts and general app updates.',
      importance: Importance.max,
      priority: Priority.high,
      icon: 'ic_notification',
      playSound: true,
      enableVibration: true,
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
  }

  // ─── App icon badge (red number on launcher icon) ──────────────────────────

  static Future<void> setBadge(int count) async {
    if (kIsWeb) return;
    try {
      if (count > 0) {
        await FlutterAppBadger.updateBadgeCount(count);
      } else {
        await FlutterAppBadger.removeBadge();
      }
    } catch (_) {}
  }

  static Future<void> clearBadge() => setBadge(0);

  // ─── Stubs kept for API compatibility ──────────────────────────────────────

  static Future<void> subscribeToUserTopic(String email) async {}
  static Future<void> unsubscribeFromUserTopic(String email) async {}
}
