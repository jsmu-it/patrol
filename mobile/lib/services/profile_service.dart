import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/user.dart';
import 'api_client.dart';

class ProfileService {
  ProfileService(this._apiClient);

  final ApiClient _apiClient;

  Future<User> updateProfile({
    File? profilePhoto,
    int? activeProjectId,
  }) async {
    final formData = FormData();

    if (profilePhoto != null) {
      formData.files.add(MapEntry(
        'profile_photo',
        await MultipartFile.fromFile(
          profilePhoto.path,
          filename: profilePhoto.path.split('/').last,
        ),
      ));
    }

    if (activeProjectId != null) {
      formData.fields.add(MapEntry('active_project_id', activeProjectId.toString()));
    }

    final Response<Map<String, dynamic>> response = await _apiClient.post<Map<String, dynamic>>(
      '/me/profile',
      data: formData,
    );

    final body = response.data;
    if (body == null || body['user'] == null) {
      throw ApiException('Respons kosong dari server.', statusCode: response.statusCode);
    }

    return User.fromJson(body['user'] as Map<String, dynamic>);
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    await _apiClient.post<Map<String, dynamic>>(
      '/me/change-password',
      data: {
        'current_password': currentPassword,
        'new_password': newPassword,
        'new_password_confirmation': confirmPassword,
      },
    );
  }

  Future<Map<String, dynamic>> getCV() async {
    final response = await _apiClient.get<Map<String, dynamic>>('/me/cv');
    return response.data ?? {};
  }
}

final profileServiceProvider = Provider<ProfileService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return ProfileService(apiClient);
});
