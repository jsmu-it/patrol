import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'api_client.dart';
import 'offline_queue_service.dart';

class PatrolService {
  PatrolService(this._apiClient, this._offlineQueue);

  final ApiClient _apiClient;
  final OfflineQueueService _offlineQueue;

  Future<String> createLog({
    required int projectId,
    String? checkpointCode, // Made optional
    required String title,
    required String postName,
    String? description,
    required double latitude,
    required double longitude,
    String? photoPath,
    String? type, // Added type parameter
  }) async {
    try {
      final map = <String, dynamic>{
        'project_id': projectId,
        'title': title,
        'post_name': postName,
        'latitude': latitude,
        'longitude': longitude,
        'type': type ?? 'patrol', // Added type to map
      };

      if (checkpointCode != null && checkpointCode.isNotEmpty) {
        map['checkpoint_code'] = checkpointCode;
      }

      if (description != null && description.isNotEmpty) {
        map['description'] = description;
      }

      if (photoPath != null && photoPath.isNotEmpty) {
        map['photo'] = await MultipartFile.fromFile(
          photoPath,
          filename: 'patrol_photo.jpg',
        );
      }

      final formData = FormData.fromMap(map);

      final Response<dynamic> response = await _apiClient.post<dynamic>(
        '/patrol/logs',
        data: formData,
      );

      await _offlineQueue.sync();

      final data = response.data;
      if (data is Map<String, dynamic> && data['title'] is String) {
        return 'Laporan patroli "${data['title']}" tersimpan.';
      }

      return 'Laporan patroli tersimpan.';
    } on ApiException catch (e) {
      if (_offlineQueue.isOfflineError(e)) {
        await _offlineQueue.enqueuePatrolLog(
          projectId: projectId,
          checkpointCode: checkpointCode ?? '', // Handle null
          title: title,
          postName: postName,
          description: description,
          latitude: latitude,
          longitude: longitude,
          photoPath: photoPath,
        );

        return 'Laporan patroli tersimpan offline. Akan dikirim saat koneksi tersedia.';
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getCheckpoint(String code) async {
    final response = await _apiClient.get<Map<String, dynamic>>(
      '/patrol/checkpoint',
      queryParameters: {'code': code},
    );

    return response.data ?? {};
  }
}

final patrolServiceProvider = Provider<PatrolService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final offlineQueue = ref.watch(offlineQueueServiceProvider);
  return PatrolService(apiClient, offlineQueue);
});
