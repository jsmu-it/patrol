import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../models/attendance_record.dart';
import 'api_client.dart';
import 'offline_queue_service.dart';

typedef AttendanceResult = ({String message});

class AttendanceService {
  AttendanceService(this._apiClient, this._offlineQueue);

  final ApiClient _apiClient;
  final OfflineQueueService _offlineQueue;

  Future<AttendanceResult> checkIn({
    required int shiftId,
    required double latitude,
    required double longitude,
    required String mode,
    String? note,
    required String selfiePath,
  }) async {
    try {
      final map = <String, dynamic>{
        'shift_id': shiftId,
        'latitude': latitude,
        'longitude': longitude,
        'mode': mode,
      };

      if (note != null && note.isNotEmpty) {
        map['note'] = note;
      }

      map['selfie'] = await MultipartFile.fromFile(
        selfiePath,
        filename: 'selfie.jpg',
      );

      final formData = FormData.fromMap(map);

      final Response<Map<String, dynamic>> response =
          await _apiClient.post<Map<String, dynamic>>(
        '/attendance/clock-in',
        data: formData,
      );

      final result = _parseAttendanceResponse(
        response,
        defaultMessage: 'Absen masuk berhasil.',
      );

      await _offlineQueue.sync();
      return result;
    } on ApiException catch (e) {
      if (_offlineQueue.isOfflineError(e)) {
        await _offlineQueue.enqueueAttendanceClockIn(
          shiftId: shiftId,
          latitude: latitude,
          longitude: longitude,
          mode: mode,
          note: note,
          selfiePath: selfiePath,
        );

        return (
          message:
              'Absen tersimpan offline. Akan dikirim saat koneksi tersedia.',
        );
      }
      rethrow;
    }
  }

  Future<AttendanceResult> checkOut({
    required int shiftId,
    required double latitude,
    required double longitude,
    String? note,
    String? selfiePath,
  }) async {
    try {
      final map = <String, dynamic>{
        'shift_id': shiftId,
        'latitude': latitude,
        'longitude': longitude,
      };

      if (note != null && note.isNotEmpty) {
        map['note'] = note;
      }

      if (selfiePath != null && selfiePath.isNotEmpty) {
        map['selfie'] = await MultipartFile.fromFile(
          selfiePath,
          filename: 'selfie.jpg',
        );
      }

      final formData = FormData.fromMap(map);

      final Response<Map<String, dynamic>> response =
          await _apiClient.post<Map<String, dynamic>>(
        '/attendance/clock-out',
        data: formData,
      );

      final result = _parseAttendanceResponse(
        response,
        defaultMessage: 'Absen keluar berhasil.',
      );

      await _offlineQueue.sync();
      return result;
    } on ApiException catch (e) {
      if (_offlineQueue.isOfflineError(e)) {
        await _offlineQueue.enqueueAttendanceClockOut(
          shiftId: shiftId,
          latitude: latitude,
          longitude: longitude,
          note: note,
          selfiePath: selfiePath,
        );

        return (
          message:
              'Absen tersimpan offline. Akan dikirim saat koneksi tersedia.',
        );
      }
      rethrow;
    }
  }

  AttendanceResult _parseAttendanceResponse(
    Response<Map<String, dynamic>> response, {
    required String defaultMessage,
  }) {
    final body = response.data;

    if (body is Map<String, dynamic>) {
      final message = body['message'] as String?;
      if (message != null && message.isNotEmpty) {
        return (message: message);
      }
    }

    return (message: defaultMessage);
  }

  Future<List<AttendanceRecord>> getHistory({
    required DateTime from,
    required DateTime to,
    int? projectId,
  }) async {
    final formatter = DateFormat('yyyy-MM-dd');
    final query = <String, dynamic>{
      'from': formatter.format(from),
      'to': formatter.format(to),
    };

    if (projectId != null) {
      query['project_id'] = projectId;
    }

    final Response<dynamic> response =
        await _apiClient.get<dynamic>(
      '/attendance/history',
      queryParameters: query,
    );

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
        .map(AttendanceRecord.fromJson)
        .toList();
  }
}

final attendanceServiceProvider = Provider<AttendanceService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final offlineQueue = ref.watch(offlineQueueServiceProvider);
  return AttendanceService(apiClient, offlineQueue);
});
