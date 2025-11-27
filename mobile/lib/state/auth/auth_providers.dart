import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../services/auth_service.dart';
import '../../services/secure_storage_service.dart';
import '../../services/notification_service.dart';
import 'auth_state.dart';
import '../../services/api_client.dart';

final authTokenProvider = StateProvider<String?>((ref) => null);

final authNotifierProvider =
    StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  final authService = ref.watch(authServiceProvider);
  final secureStorage = ref.watch(secureStorageServiceProvider);
  return AuthNotifier(ref, authService, secureStorage);
});

class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier(this._ref, this._authService, this._secureStorage)
      : super(AuthState.initial());

  final Ref _ref;
  final AuthService _authService;
  final SecureStorageService _secureStorage;

  Future<void> loadFromStorage() async {
    final token = await _secureStorage.getToken();

    if (token == null || token.isEmpty) {
      _ref.read(authTokenProvider.notifier).state = null;
      state = state.copyWith(status: AuthStatus.unauthenticated);
      return;
    }

    _ref.read(authTokenProvider.notifier).state = token;
    state = state.copyWith(status: AuthStatus.authenticated);

    // Fetch user data immediately
    await refreshCurrentUser();

    // Jangan blok UI saat init Firebase/FCM; jalankan di background.
    unawaited(
      _ref
          .read(notificationServiceProvider)
          .initAndRegisterDeviceToken(),
    );
  }

  Future<void> login({
    required String username,
    required String password,
  }) async {
    // DEBUG: log when login is triggered
    // ignore: avoid_print
    print('AuthNotifier.login() called for username=$username');
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final result = await _authService.login(
        username: username,
        password: password,
      );

      // DEBUG: log when login result is received
      // ignore: avoid_print
      print('AuthNotifier.login() success for user id=${result.user.id}');

      await _secureStorage.saveToken(result.authToken.token);
      _ref.read(authTokenProvider.notifier).state = result.authToken.token;

      state = state.copyWith(
        status: AuthStatus.authenticated,
        user: result.user,
        isLoading: false,
        errorMessage: null,
      );

      // DEBUG: log new state after success
      // ignore: avoid_print
      print('AuthNotifier.login() state after success: status=${state.status}, isLoading=${state.isLoading}, userId=${state.user?.id}');

      // Registrasi FCM token tidak boleh menghambat login;
      // biarkan berjalan async di belakang.
      unawaited(
        _ref
            .read(notificationServiceProvider)
            .initAndRegisterDeviceToken(),
      );
    } on ApiException catch (e) {
      // DEBUG: log ApiException
      // ignore: avoid_print
      print('AuthNotifier.login() ApiException: ${e.message} (status=${e.statusCode})');
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.message,
      );
    } catch (e, stack) {
      // ignore: avoid_print
      print('AuthNotifier.login() unknown error: $e');
      // ignore: avoid_print
      print(stack);
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Terjadi kesalahan. Silakan coba lagi.',
      );
    }
  }

  Future<void> logout() async {
    await _authService.remoteLogout();
    await _secureStorage.clearToken();
    _ref.read(authTokenProvider.notifier).state = null;
    state = AuthState.initial().copyWith(status: AuthStatus.unauthenticated);
  }

  Future<void> refreshCurrentUser() async {
    try {
      final user = await _authService.fetchMe();
      state = state.copyWith(user: user);
    } on ApiException catch (e) {
      if (e.statusCode == 401) {
        await logout();
      }
    } catch (_) {}
  }
}

