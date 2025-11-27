import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'api_client.dart';

class NotificationService {
  NotificationService(this._apiClient);

  final ApiClient _apiClient;

  bool _initialized = false;

  Future<void> initAndRegisterDeviceToken() async {
    if (kIsWeb) return;

    if (_initialized) return;
    _initialized = true;

    try {
      final messaging = FirebaseMessaging.instance;

      await messaging.requestPermission();

      final token = await messaging.getToken();
      if (token != null && token.isNotEmpty) {
        await _sendTokenToBackend(token);
      }

      FirebaseMessaging.instance.onTokenRefresh.listen((newToken) {
        _sendTokenToBackend(newToken);
      });

      FirebaseMessaging.onMessage.listen((message) {
        // Saat ini biarkan OS yang menampilkan notifikasi.
      });
    } catch (_) {
      // Jika FCM gagal di-init, jangan ganggu alur login; bisa dicoba lagi nanti.
      _initialized = false;
    }
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
}

final notificationServiceProvider = Provider<NotificationService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return NotificationService(apiClient);
});
