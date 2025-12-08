import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/auth_token.dart';
import '../models/user.dart';
import 'api_client.dart';
import 'secure_storage_service.dart';
import 'connectivity_service.dart';

typedef LoginResult = ({AuthToken authToken, User user});

const _userCacheKey = 'cached_user_profile';

class AuthService {
  AuthService(this._apiClient, this._storage, this._connectivity);

  final ApiClient _apiClient;
  final SecureStorageService _storage;
  final ConnectivityService _connectivity;

  Future<LoginResult> login({
    required String username,
    required String password,
  }) async {
    // DEBUG: log URL & payload
    // ignore: avoid_print
    print('AuthService.login() POST /login username=$username');

    final Response<Map<String, dynamic>> response =
        await _apiClient.post<Map<String, dynamic>>(
      '/login',
      data: {
        'username': username,
        'password': password,
      },
    );

    // DEBUG: log status code
    // ignore: avoid_print
    print('AuthService.login() response status: ${response.statusCode}');

    final body = response.data;
    if (body == null) {
      throw ApiException(
        'Respons kosong dari server.',
        statusCode: response.statusCode,
      );
    }

    final tokenStr = body['access_token'] as String?;
    if (tokenStr == null || tokenStr.isEmpty) {
      throw ApiException(
        'Token tidak ditemukan dalam respons.',
        statusCode: response.statusCode,
        data: body,
      );
    }

    final userJson = body['user'] as Map<String, dynamic>?;
    if (userJson == null) {
      throw ApiException(
        'Data user tidak ditemukan dalam respons.',
        statusCode: response.statusCode,
        data: body,
      );
    }

    final user = User.fromJson(userJson);
    final authToken = AuthToken(token: tokenStr);

    // Cache user data immediately after login
    await _cacheUser(user);

    return (authToken: authToken, user: user);
  }

  Future<User> fetchMe() async {
    // Check connectivity
    final isOnline = await _connectivity.isOnline();
    if (!isOnline) {
      final cached = await _loadCachedUser();
      if (cached != null) return cached;
      // If offline and no cache, we can't do anything, but likely 
      // the caller handles the error.
      throw ApiException('Offline dan tidak ada data user tersimpan.');
    }

    try {
      final Response<Map<String, dynamic>> response =
          await _apiClient.get<Map<String, dynamic>>('/me');

      final body = response.data;
      if (body == null) {
        throw ApiException(
          'Respons kosong dari server.',
          statusCode: response.statusCode,
        );
      }

      final user = User.fromJson(body);
      await _cacheUser(user);
      return user;
    } catch (e) {
      // If fetch fails (even if "online"), try cache fallback
      final cached = await _loadCachedUser();
      if (cached != null) return cached;
      rethrow;
    }
  }

  Future<void> _cacheUser(User user) async {
    try {
      final raw = jsonEncode(user.toJson());
      await _storage.writeRaw(_userCacheKey, raw);
    } catch (_) {}
  }

  Future<User?> _loadCachedUser() async {
    try {
      final raw = await _storage.readRaw(_userCacheKey);
      if (raw != null && raw.isNotEmpty) {
        return User.fromJson(jsonDecode(raw));
      }
    } catch (_) {}
    return null;
  }

  Future<void> remoteLogout() async {
    try {
      await _apiClient.post<dynamic>('/logout');
    } on ApiException {
      // Abaikan error logout remote, tetap lanjut bersihkan lokal.
    }
    await _storage.writeRaw(_userCacheKey, ''); // Clear cache
  }
}

final authServiceProvider = Provider<AuthService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final storage = ref.watch(secureStorageServiceProvider);
  final connectivity = ref.watch(connectivityServiceProvider);
  return AuthService(apiClient, storage, connectivity);
});
