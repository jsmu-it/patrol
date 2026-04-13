import 'dart:convert';
import 'dart:io';

import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/notification.dart';
import 'api_client.dart';
import 'notification_storage.dart';

// Global navigator key for navigation from notification
import 'package:flutter/material.dart';

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

class NotificationService {
  NotificationService(this._apiClient, this._storage);

  final ApiClient _apiClient;
  final NotificationStorage _storage;
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  bool _initialized = false;

  static const AndroidNotificationChannel _channel = AndroidNotificationChannel(
    'jsmuguard_channel',
    'JSMUGuard Notifications',
    description: 'Notifications for JSMUGuard app',
    importance: Importance.high,
  );

  Future<void> initAndRegisterDeviceToken() async {
    if (kIsWeb) return;

    if (_initialized) return;
    _initialized = true;

    try {
      // Initialize local notifications
      await _initLocalNotifications();

      final messaging = FirebaseMessaging.instance;

      // Request permission with explicit settings
      final settings = await messaging.requestPermission(
        alert: true,
        badge: true,
        sound: true,
        provisional: false,
      );
      
      // ignore: avoid_print
      print('[FCM] Permission status: ${settings.authorizationStatus}');

      final token = await messaging.getToken();
      // ignore: avoid_print
      print('[FCM] Token obtained: ${token != null ? '${token.substring(0, 20)}...' : 'null'}');
      
      if (token != null && token.isNotEmpty) {
        await _sendTokenToBackend(token);
        // ignore: avoid_print
        print('[FCM] Token sent to backend successfully');
      }

      FirebaseMessaging.instance.onTokenRefresh.listen((newToken) {
        // ignore: avoid_print
        print('[FCM] Token refreshed, sending to backend...');
        _sendTokenToBackend(newToken);
      });

      // Handle foreground messages - show local notification
      FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

      // Handle notification tap when app is in background/terminated
      FirebaseMessaging.onMessageOpenedApp.listen((message) {
        // Save notification when opened from background
        _saveNotificationFromRemoteMessage(message);
        _handleNotificationTap(message);
      });

      // Check if app was opened from a notification (terminated state)
      final initialMessage = await messaging.getInitialMessage();
      if (initialMessage != null) {
        // Save notification when app opened from terminated state
        await _saveNotificationFromRemoteMessage(initialMessage);
        _handleNotificationTap(initialMessage);
      }
    } catch (e, stack) {
      // ignore: avoid_print
      print('[FCM] Error during initialization: $e');
      // ignore: avoid_print
      print(stack);
      _initialized = false;
    }
  }

  Future<void> _initLocalNotifications() async {
    const androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    const iosSettings = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    const initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
    );

    // Create notification channel for Android
    if (Platform.isAndroid) {
      await _localNotifications
          .resolvePlatformSpecificImplementation<
              AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(_channel);
    }
  }

  void _handleForegroundMessage(RemoteMessage message) {
    final notification = message.notification;
    final data = message.data;

    if (notification != null) {
      // Save notification to storage
      _saveNotification(
        title: notification.title ?? 'Notifikasi',
        body: notification.body ?? '',
        data: data,
      );

      _showLocalNotification(
        title: notification.title ?? 'Notifikasi',
        body: notification.body ?? '',
        payload: jsonEncode(data),
      );
    }
  }

  Future<void> _showLocalNotification({
    required String title,
    required String body,
    String? payload,
  }) async {
    const androidDetails = AndroidNotificationDetails(
      'jsmuguard_channel',
      'JSMUGuard Notifications',
      channelDescription: 'Notifications for JSMUGuard app',
      importance: Importance.high,
      priority: Priority.high,
      showWhen: true,
      icon: '@mipmap/ic_launcher',
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

    await _localNotifications.show(
      DateTime.now().millisecondsSinceEpoch.remainder(100000),
      title,
      body,
      details,
      payload: payload,
    );
  }

  void _onNotificationTapped(NotificationResponse response) {
    if (response.payload != null) {
      try {
        final data = jsonDecode(response.payload!) as Map<String, dynamic>;
        _navigateBasedOnData(data);
      } catch (_) {
        // Ignore JSON parsing errors
      }
    }
  }

  void _handleNotificationTap(RemoteMessage message) {
    // Mark as read when notification is tapped
    final notificationId = message.data['notification_id'] as String?;
    if (notificationId != null) {
      _storage.markAsRead(notificationId);
    }
    _navigateBasedOnData(message.data);
  }

  void _navigateBasedOnData(Map<String, dynamic> data) {
    final type = data['type'];

    if (type == 'payroll') {
      final payrollSlipId = data['payroll_slip_id'];
      if (payrollSlipId != null) {
        // Navigate to payroll detail screen
        final slipId = int.tryParse(payrollSlipId.toString()) ?? 0;
        if (slipId > 0) {
          navigatorKey.currentState?.pushNamed(
            '/payroll-detail',
            arguments: slipId,
          );
        }
      }
    } else if (type == 'leave_request') {
      // BUG FIX: 'leave_request' type = new request notification for ADMINS
      // 'leave_request_approved/rejected/pending' = status update for the EMPLOYEE
      // Route to admin screen only for new request notifications (for admins).
      navigatorKey.currentState?.pushNamed('/admin/leave-approvals');
    } else if (type == 'leave_request_approved' ||
        type == 'leave_request_rejected' ||
        type == 'leave_request_pending') {
      // BUG FIX: Status update notifications belong to the employee, not admin.
      // Navigate to the employee's own leave list.
      navigatorKey.currentState?.pushNamed('/leave-requests');
    } else if (type == 'attendance_reminder') {
      // Navigate to home screen for clock in/out
      navigatorKey.currentState?.pushNamed('/home');
    } else if (type == 'sos' || type == 'incident') {
      // Navigate to patrol history for SOS and incident notifications
      navigatorKey.currentState?.pushNamed('/patrol/history');
    }
    // Add more notification type handlers here as needed
  }

  Future<void> _sendTokenToBackend(String token) async {
    try {
      await _apiClient.post<dynamic>(
        '/me/device-token',
        data: {'fcm_token': token},
      );
    } catch (_) {
      // Abaikan kegagalan, akan dicoba lagi saat token di-refresh
    }
  }

  Future<void> _saveNotification({
    required String title,
    required String body,
    required Map<String, dynamic> data,
  }) async {
    try {
      final notification = AppNotification(
        id: '${DateTime.now().millisecondsSinceEpoch}_${data.hashCode}',
        title: title,
        body: body,
        data: data,
        receivedAt: DateTime.now(),
        isRead: false,
      );
      await _storage.saveNotification(notification);
    } catch (e) {
      // Ignore errors in saving notifications to not disrupt user experience
    }
  }

  Future<void> _saveNotificationFromRemoteMessage(RemoteMessage message) async {
    final notification = message.notification;
    if (notification != null) {
      await _saveNotification(
        title: notification.title ?? 'Notifikasi',
        body: notification.body ?? '',
        data: message.data,
      );
    }
  }

  Future<int> getUnreadCount() async {
    return await _storage.getUnreadCount();
  }
}

final notificationServiceProvider = Provider<NotificationService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final storage = ref.watch(notificationStorageProvider);
  return NotificationService(apiClient, storage);
});
