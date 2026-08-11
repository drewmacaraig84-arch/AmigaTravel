import 'package:flutter/foundation.dart';

class NotificationService {
  static Future<void> requestPermission() async {
    // No-op without firebase
  }

  static Future<void> initialize({Function(Map<String, dynamic>)? onNotificationTap}) async {
    // No-op without firebase
  }

  static Future<void> subscribeToUserTopic(String email) async {
    // No-op without firebase
  }

  static Future<void> unsubscribeFromUserTopic(String email) async {
    // No-op without firebase
  }
}
