import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/auth_token.dart';
import '../models/user.dart';
import 'api_client.dart';

typedef LoginResult = ({AuthToken authToken, User user});

class AuthService {
  AuthService(this._apiClient);

  final ApiClient _apiClient;

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

    return (authToken: authToken, user: user);
  }

  Future<User> fetchMe() async {
    final Response<Map<String, dynamic>> response =
        await _apiClient.get<Map<String, dynamic>>('/me');

    final body = response.data;
    if (body == null) {
      throw ApiException(
        'Respons kosong dari server.',
        statusCode: response.statusCode,
      );
    }

    return User.fromJson(body);
  }

  Future<void> remoteLogout() async {
    try {
      await _apiClient.post<dynamic>('/logout');
    } on ApiException {
      // Abaikan error logout remote, tetap lanjut bersihkan lokal.
    }
  }
}

final authServiceProvider = Provider<AuthService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return AuthService(apiClient);
});
