import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../models/leave_request.dart';
import 'api_client.dart';
import 'offline_queue_service.dart';

class LeaveService {
  LeaveService(this._apiClient, this._offlineQueue);

  final ApiClient _apiClient;
  final OfflineQueueService _offlineQueue;

  Future<List<LeaveRequest>> list() async {
    final Response<dynamic> response =
        await _apiClient.get<dynamic>('/leave-requests');
    final body = response.data;

    List<dynamic> rawList;
    if (body is Map<String, dynamic> && body['data'] is List) {
      rawList = body['data'] as List<dynamic>;
    } else if (body is List) {
      rawList = body;
    } else {
      return [];
    }

    return rawList
        .whereType<Map<String, dynamic>>()
        .map(LeaveRequest.fromJson)
        .toList();
  }

  Future<LeaveRequest> create({
    required String type,
    required DateTime dateFrom,
    required DateTime dateTo,
    required String reason,
    String? doctorNote,
  }) async {
    final formatter = DateFormat('yyyy-MM-dd');
    final data = <String, dynamic>{
      'type': type,
      'date_from': formatter.format(dateFrom),
      'date_to': formatter.format(dateTo),
      'reason': reason,
    };

    if (doctorNote != null && doctorNote.isNotEmpty) {
      data['doctor_note'] = doctorNote;
    }

    try {
      final Response<Map<String, dynamic>> response =
          await _apiClient.post<Map<String, dynamic>>(
        '/leave-requests',
        data: data,
      );

      final body = response.data;
      if (body == null) {
        throw ApiException(
          'Respons kosong dari server.',
          statusCode: response.statusCode,
        );
      }

      await _offlineQueue.sync();
      return LeaveRequest.fromJson(body);
    } on ApiException catch (e) {
      if (_offlineQueue.isOfflineError(e)) {
        await _offlineQueue.enqueueLeaveRequest(
          type: data['type'] as String,
          dateFrom: data['date_from'] as String,
          dateTo: data['date_to'] as String,
          reason: data['reason'] as String,
          doctorNote: data['doctor_note'] as String?,
        );

        return LeaveRequest(
          id: 0,
          userId: 0,
          type: data['type'] as String,
          dateFrom: DateTime.tryParse(data['date_from'] as String),
          dateTo: DateTime.tryParse(data['date_to'] as String),
          reason: data['reason'] as String,
          status: 'pending',
          doctorNote: data['doctor_note'] as String?,
          createdAt: null,
          updatedAt: null,
        );
      }
      rethrow;
    }
  }
}

final leaveServiceProvider = Provider<LeaveService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final offlineQueue = ref.watch(offlineQueueServiceProvider);
  return LeaveService(apiClient, offlineQueue);
});
