import 'dart:developer' as developer;

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../config/app_config.dart';
import '../state/auth/auth_providers.dart';

class ApiException implements Exception {
  ApiException(this.message, {this.statusCode, this.data});

  final String message;
  final int? statusCode;
  final dynamic data;

  @override
  String toString() => 'ApiException($statusCode): $message';
}

final dioProvider = Provider<Dio>((ref) {
  final config = ref.watch(appConfigProvider);

  // Reduced timeouts for faster failure in flaky networks
  final options = BaseOptions(
    baseUrl: config.apiBaseUrl,
    connectTimeout: const Duration(seconds: 3),
    receiveTimeout: const Duration(seconds: 5),
    sendTimeout: const Duration(seconds: 5),
  );

  final dio = Dio(options);

  developer.log(
    'Creating Dio with baseUrl=${options.baseUrl}',
    name: 'ApiClient',
  );

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) {
        final token = ref.read(authTokenProvider);
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
    ),
  );

  return dio;
});

final apiClientProvider = Provider<ApiClient>((ref) {
  final dio = ref.watch(dioProvider);
  return ApiClient(dio);
});

class ApiClient {
  ApiClient(this._dio);

  final Dio _dio;

  Future<Response<T>> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      final response =
          await _dio.get<T>(path, queryParameters: queryParameters, options: options);
      return response;
    } on DioException catch (e) {
      throw _toApiException(e);
    }
  }

  Future<Response<T>> post<T>(
    String path, {
    Object? data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      final response = await _dio.post<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
      return response;
    } on DioException catch (e) {
      throw _toApiException(e);
    }
  }

  ApiException _toApiException(DioException e) {
    final statusCode = e.response?.statusCode;
    final data = e.response?.data;
    developer.log(
      'API error: type=${e.type} status=$statusCode message=${e.message} data: $data',
      name: 'ApiClient',
      error: e,
      stackTrace: e.stackTrace,
    );

    return ApiException(
      _friendlyErrorMessage(e),
      statusCode: statusCode,
      data: data,
    );
  }

  String _friendlyErrorMessage(DioException e) {
    if (e.type == DioExceptionType.connectionTimeout ||
        e.type == DioExceptionType.receiveTimeout) {
      return 'Koneksi ke server lambat atau terputus. Silakan coba lagi.';
    }

    if (e.type == DioExceptionType.connectionError) {
      return 'Gagal terhubung ke server. Periksa koneksi internet Anda.';
    }

    final responseData = e.response?.data;
    if (responseData is Map<String, dynamic>) {
      final message = responseData['message'];
      if (message is String && message.isNotEmpty) {
        return message;
      }
    }

    final statusCode = e.response?.statusCode;

    if (statusCode == 401) {
      return 'Sesi Anda telah berakhir. Silakan login kembali.';
    }

    if (statusCode == 500) {
      return 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';
    }

    return 'Terjadi kesalahan. Silakan coba lagi.';
  }
}
