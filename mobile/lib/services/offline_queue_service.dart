import 'dart:convert';
import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'api_client.dart';
import 'secure_storage_service.dart';

const _offlineQueueKey = 'offline_queue';

class OfflineQueueItemType {
  static const attendanceClockIn = 'attendance_clock_in';
  static const attendanceClockOut = 'attendance_clock_out';
  static const patrolLog = 'patrol_log';
  static const leaveRequest = 'leave_request';
}

class OfflineQueueService {
  OfflineQueueService(this._storage, this._apiClient);

  final SecureStorageService _storage;
  final ApiClient _apiClient;

  bool _syncing = false;

  bool isOfflineError(ApiException e) {
    return e.statusCode == null && e.data == null;
  }

  Future<List<Map<String, dynamic>>> _loadQueue() async {
    final raw = await _storage.readRaw(_offlineQueueKey);
    if (raw == null || raw.isEmpty) {
      return [];
    }

    try {
      final decoded = jsonDecode(raw);
      if (decoded is List) {
        return decoded
            .whereType<Map<String, dynamic>>()
            .map((e) => Map<String, dynamic>.from(e))
            .toList();
      }
    } catch (_) {}

    return [];
  }

  Future<void> _saveQueue(List<Map<String, dynamic>> queue) async {
    final raw = jsonEncode(queue);
    await _storage.writeRaw(_offlineQueueKey, raw);
  }

  Future<void> _addItem(Map<String, dynamic> item) async {
    final queue = await _loadQueue();
    queue.add(item);
    await _saveQueue(queue);
  }

  Future<void> _removeItem(String id) async {
    final queue = await _loadQueue();
    queue.removeWhere((item) => item['id'] == id);
    await _saveQueue(queue);
  }

  Future<int> getPendingCount() async {
    final queue = await _loadQueue();
    return queue.length;
  }

  Future<void> enqueueAttendanceClockIn({
    required int shiftId,
    required double latitude,
    required double longitude,
    required String mode,
    String? note,
    required String selfiePath,
  }) async {
    final id = '${OfflineQueueItemType.attendanceClockIn}-${DateTime.now().millisecondsSinceEpoch}';
    await _addItem({
      'id': id,
      'type': OfflineQueueItemType.attendanceClockIn,
      'data': {
        'shift_id': shiftId,
        'latitude': latitude,
        'longitude': longitude,
        'mode': mode,
        'note': note,
        'selfie_path': selfiePath,
      },
    });
  }

  Future<void> enqueueAttendanceClockOut({
    required int shiftId,
    required double latitude,
    required double longitude,
    String? note,
    String? selfiePath,
  }) async {
    final id = '${OfflineQueueItemType.attendanceClockOut}-${DateTime.now().millisecondsSinceEpoch}';
    await _addItem({
      'id': id,
      'type': OfflineQueueItemType.attendanceClockOut,
      'data': {
        'shift_id': shiftId,
        'latitude': latitude,
        'longitude': longitude,
        'note': note,
        'selfie_path': selfiePath,
      },
    });
  }

  Future<void> enqueuePatrolLog({
    required int projectId,
    required String checkpointCode,
    required String title,
    required String postName,
    String? description,
    required double latitude,
    required double longitude,
    String? photoPath,
  }) async {
    final id = '${OfflineQueueItemType.patrolLog}-${DateTime.now().millisecondsSinceEpoch}';
    await _addItem({
      'id': id,
      'type': OfflineQueueItemType.patrolLog,
      'data': {
        'project_id': projectId,
        'checkpoint_code': checkpointCode,
        'title': title,
        'post_name': postName,
        'description': description,
        'latitude': latitude,
        'longitude': longitude,
        'photo_path': photoPath,
      },
    });
  }

  Future<void> enqueueLeaveRequest({
    required String type,
    required String dateFrom,
    required String dateTo,
    required String reason,
    String? doctorNote,
  }) async {
    final id = '${OfflineQueueItemType.leaveRequest}-${DateTime.now().millisecondsSinceEpoch}';
    await _addItem({
      'id': id,
      'type': OfflineQueueItemType.leaveRequest,
      'data': {
        'type': type,
        'date_from': dateFrom,
        'date_to': dateTo,
        'reason': reason,
        'doctor_note': doctorNote,
      },
    });
  }

  Future<void> sync() async {
    if (_syncing) return;
    _syncing = true;
    try {
      var queue = await _loadQueue();
      if (queue.isEmpty) return;

      for (final item in List<Map<String, dynamic>>.from(queue)) {
        final id = item['id'] as String?;
        final type = item['type'] as String?;
        final data = item['data'] as Map<String, dynamic>?;
        if (id == null || type == null || data == null) {
          await _removeItem(id ?? '');
          queue = await _loadQueue();
          continue;
        }

        try {
          switch (type) {
            case OfflineQueueItemType.attendanceClockIn:
              await _syncAttendanceClockIn(data);
              break;
            case OfflineQueueItemType.attendanceClockOut:
              await _syncAttendanceClockOut(data);
              break;
            case OfflineQueueItemType.patrolLog:
              await _syncPatrolLog(data);
              break;
            case OfflineQueueItemType.leaveRequest:
              await _syncLeaveRequest(data);
              break;
            default:
              break;
          }

          await _removeItem(id);
          queue = await _loadQueue();
        } on ApiException catch (e) {
          if (isOfflineError(e)) {
            break;
          }

          await _removeItem(id);
          queue = await _loadQueue();
        } catch (_) {
          break;
        }
      }
    } finally {
      _syncing = false;
    }
  }

  Future<void> _syncAttendanceClockIn(Map<String, dynamic> data) async {
    final map = <String, dynamic>{
      'shift_id': data['shift_id'],
      'latitude': data['latitude'],
      'longitude': data['longitude'],
      'mode': data['mode'],
    };

    if (data['note'] != null && (data['note'] as String).isNotEmpty) {
      map['note'] = data['note'];
    }

    final selfiePath = data['selfie_path'] as String?;
    if (selfiePath != null && selfiePath.isNotEmpty) {
      final file = File(selfiePath);
      if (await file.exists()) {
        map['selfie'] = await MultipartFile.fromFile(
          selfiePath,
          filename: 'selfie.jpg',
        );
      }
    }

    final formData = FormData.fromMap(map);
    await _apiClient.post<dynamic>(
      '/attendance/clock-in',
      data: formData,
    );
  }

  Future<void> _syncAttendanceClockOut(Map<String, dynamic> data) async {
    final map = <String, dynamic>{
      'shift_id': data['shift_id'],
      'latitude': data['latitude'],
      'longitude': data['longitude'],
    };

    if (data['note'] != null && (data['note'] as String).isNotEmpty) {
      map['note'] = data['note'];
    }

    final selfiePath = data['selfie_path'] as String?;
    if (selfiePath != null && selfiePath.isNotEmpty) {
      final file = File(selfiePath);
      if (await file.exists()) {
        map['selfie'] = await MultipartFile.fromFile(
          selfiePath,
          filename: 'selfie.jpg',
        );
      }
    }

    final formData = FormData.fromMap(map);
    await _apiClient.post<dynamic>(
      '/attendance/clock-out',
      data: formData,
    );
  }

  Future<void> _syncPatrolLog(Map<String, dynamic> data) async {
    final map = <String, dynamic>{
      'project_id': data['project_id'],
      'checkpoint_code': data['checkpoint_code'],
      'title': data['title'],
      'post_name': data['post_name'],
      'latitude': data['latitude'],
      'longitude': data['longitude'],
    };

    if (data['description'] != null &&
        (data['description'] as String).isNotEmpty) {
      map['description'] = data['description'];
    }

    final photoPath = data['photo_path'] as String?;
    if (photoPath != null && photoPath.isNotEmpty) {
      final file = File(photoPath);
      if (await file.exists()) {
        map['photo'] = await MultipartFile.fromFile(
          photoPath,
          filename: 'patrol_photo.jpg',
        );
      }
    }

    final formData = FormData.fromMap(map);
    await _apiClient.post<dynamic>(
      '/patrol/logs',
      data: formData,
    );
  }

  Future<void> _syncLeaveRequest(Map<String, dynamic> data) async {
    await _apiClient.post<dynamic>(
      '/leave-requests',
      data: data,
    );
  }
}

final offlineQueueServiceProvider = Provider<OfflineQueueService>((ref) {
  final storage = ref.watch(secureStorageServiceProvider);
  final apiClient = ref.watch(apiClientProvider);
  return OfflineQueueService(storage, apiClient);
});
