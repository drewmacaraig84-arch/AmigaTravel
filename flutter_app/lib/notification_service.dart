import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_app_badger/flutter_app_badger.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'dart:convert';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  try {
    await Firebase.initializeApp();
    final notification = message.notification;
    if (notification != null) {
      await NotificationService.showNotification(
        id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
        title: notification.title ?? 'Amiga Travel',
        body: notification.body ?? '',
        payload: jsonEncode(message.data),
      );
    }
  } catch (e) {
    debugPrint('FCM background handler error: $e');
  }
}

class NotificationService {
  static dynamic _plugin = FlutterLocalNotificationsPlugin();

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
      // 1. Initialize Firebase
      await Firebase.initializeApp();
      FirebaseMessaging.onBackgroundMessage(
          _firebaseMessagingBackgroundHandler);

      // 2. Initialize Local Notifications Plugin
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

      // 3. Register high-importance Android channel with OS
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

      // 4. Handle Foreground FCM Messages
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        final notification = message.notification;
        if (notification != null) {
          showNotification(
            id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
            title: notification.title ?? 'Amiga Travel',
            body: notification.body ?? '',
            payload: jsonEncode(message.data),
          );
        }
      });

      // 5. Handle Notification tap when app was opened from background via push
      FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
        if (onNotificationTap != null) {
          onNotificationTap(message.data);
        }
      });

      // 6. Check if app was opened directly from a terminated state notification
      final initialMessage =
          await FirebaseMessaging.instance.getInitialMessage();
      if (initialMessage != null && onNotificationTap != null) {
        onNotificationTap(initialMessage.data);
      }

      // 7. Subscribe to global announcements topic
      await FirebaseMessaging.instance.subscribeToTopic('all_users');
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

      // Firebase iOS permission
      await FirebaseMessaging.instance.requestPermission(
        alert: true,
        badge: true,
        sound: true,
        provisional: false,
      );
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

  // ─── User-specific FCM Topic Subscription ──────────────────────────────────

  static Future<void> subscribeToUserTopic(dynamic userIdOrEmail) async {
    if (kIsWeb) return;
    try {
      final topic = _sanitizeTopic(userIdOrEmail.toString());
      if (topic.isNotEmpty) {
        await FirebaseMessaging.instance.subscribeToTopic(topic);
        debugPrint('Subscribed to FCM topic: $topic');
      }
    } catch (e) {
      debugPrint('Failed to subscribe to FCM topic: $e');
    }
  }

  static Future<void> unsubscribeFromUserTopic(dynamic userIdOrEmail) async {
    if (kIsWeb) return;
    try {
      final topic = _sanitizeTopic(userIdOrEmail.toString());
      if (topic.isNotEmpty) {
        await FirebaseMessaging.instance.unsubscribeFromTopic(topic);
        debugPrint('Unsubscribed from FCM topic: $topic');
      }
    } catch (e) {
      debugPrint('Failed to unsubscribe from FCM topic: $e');
    }
  }

  static String _sanitizeTopic(String input) {
    if (input.isEmpty) return '';
    if (int.tryParse(input) != null) {
      return 'user_$input';
    }
    if (input.startsWith('user_')) {
      return input.replaceAll(RegExp(r'[^a-zA-Z0-9-_.~%+]'), '_');
    }
    return 'user_${input.replaceAll(RegExp(r'[^a-zA-Z0-9-_.~%+]'), '_')}';
  }
}

