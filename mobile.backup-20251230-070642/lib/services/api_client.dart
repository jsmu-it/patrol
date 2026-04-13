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

  // Reasonable timeouts for production
  final options = BaseOptions(
    baseUrl: config.apiBaseUrl,
    connectTimeout: const Duration(seconds: 15),
    receiveTimeout: const Duration(seconds: 30),
    sendTimeout: const Duration(seconds: 30),
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  );

  final dio = Dio(options);

  print('[ApiClient] Creating Dio with baseUrl=${options.baseUrl}');

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) {
        final token = ref.read(authTokenProvider);
        print('[ApiClient] REQUEST: ${options.method} ${options.path}');
        print('[ApiClient] Token: ${token != null ? "present (${token.length} chars)" : "null"}');
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        // Remove Content-Type for FormData, let Dio set it automatically
        if (options.data is FormData) {
          options.headers.remove('Content-Type');
          print('[ApiClient] FormData detected, removed Content-Type header');
        }
        return handler.next(options);
      },
      onResponse: (response, handler) {
        print('[ApiClient] RESPONSE: ${response.statusCode} ${response.requestOptions.path}');
        return handler.next(response);
      },
      onError: (error, handler) {
        print('[ApiClient] ERROR: ${error.type} ${error.requestOptions.path} - ${error.message}');
        return handler.next(error);
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
    print('[ApiClient] API error: type=${e.type} status=$statusCode message=${e.message}');

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
