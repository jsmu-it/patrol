import 'dart:convert';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/notification.dart';

class NotificationStorage {
  NotificationStorage(this._prefs);

  final SharedPreferences _prefs;
  static const String _notificationsKey = 'app_notifications';
  static const int _maxNotifications = 100;

  Future<void> saveNotification(AppNotification notification) async {
    final notifications = await getNotifications();
    
    // Add new notification at the beginning
    notifications.insert(0, notification);
    
    // Keep only the last _maxNotifications
    if (notifications.length > _maxNotifications) {
      notifications.removeRange(_maxNotifications, notifications.length);
    }
    
    await _saveNotifications(notifications);
  }

  Future<List<AppNotification>> getNotifications() async {
    final jsonString = _prefs.getString(_notificationsKey);
    if (jsonString == null || jsonString.isEmpty) {
      return [];
    }
    
    try {
      final List<dynamic> jsonList = jsonDecode(jsonString) as List<dynamic>;
      return jsonList
          .map((json) => AppNotification.fromJson(json as Map<String, dynamic>))
          .toList();
    } catch (e) {
      // If there's an error parsing, return empty list and clear storage
      await _prefs.remove(_notificationsKey);
      return [];
    }
  }

  Future<void> markAsRead(String id) async {
    final notifications = await getNotifications();
    final index = notifications.indexWhere((n) => n.id == id);
    
    if (index != -1) {
      notifications[index] = notifications[index].copyWith(isRead: true);
      await _saveNotifications(notifications);
    }
  }

  Future<void> markAllAsRead() async {
    final notifications = await getNotifications();
    final updatedNotifications = notifications
        .map((n) => n.copyWith(isRead: true))
        .toList();
    await _saveNotifications(updatedNotifications);
  }

  Future<void> deleteNotification(String id) async {
    final notifications = await getNotifications();
    notifications.removeWhere((n) => n.id == id);
    await _saveNotifications(notifications);
  }

  Future<void> clearAll() async {
    await _prefs.remove(_notificationsKey);
  }

  Future<int> getUnreadCount() async {
    final notifications = await getNotifications();
    return notifications.where((n) => !n.isRead).length;
  }

  Future<void> _saveNotifications(List<AppNotification> notifications) async {
    final jsonList = notifications.map((n) => n.toJson()).toList();
    final jsonString = jsonEncode(jsonList);
    await _prefs.setString(_notificationsKey, jsonString);
  }
}

final notificationStorageProvider = Provider<NotificationStorage>((ref) {
  throw UnimplementedError('NotificationStorage must be initialized with SharedPreferences');
});
