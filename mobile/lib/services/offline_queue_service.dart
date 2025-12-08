import 'dart:async';
import 'dart:convert';
import 'dart:developer' as developer;
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

/// Event emitted when sync state changes
class SyncEvent {
  final int pendingCount;
  final int syncedCount;
  final bool isComplete;
  
  const SyncEvent({
    required this.pendingCount,
    required this.syncedCount,
    required this.isComplete,
  });
}

class OfflineQueueService {
  OfflineQueueService(this._storage, this._apiClient);

  final SecureStorageService _storage;
  final ApiClient _apiClient;

  bool _syncing = false;
  
  // Stream controller to notify UI when sync state changes
  final _syncController = StreamController<SyncEvent>.broadcast();
  Stream<SyncEvent> get onSyncStateChanged => _syncController.stream;

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
    
    // Notify UI that an item was added
    _syncController.add(SyncEvent(
      pendingCount: queue.length,
      syncedCount: 0,
      isComplete: false,
    ));
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
    String? occurredAt,
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
        'occurred_at': occurredAt,
      },
    });
  }

  Future<void> enqueueAttendanceClockOut({
    required int shiftId,
    required double latitude,
    required double longitude,
    String? note,
    String? selfiePath,
    String? occurredAt,
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
        'occurred_at': occurredAt,
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
    String? type,
    String? occurredAt,
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
        'type': type,
        'occurred_at': occurredAt,
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

      final pendingItems = List<Map<String, dynamic>>.from(queue);
      final processedIds = <String>[];

      // Separate items by type for parallel processing
      final independentItems = <Map<String, dynamic>>[];
      final sequentialItems = <Map<String, dynamic>>[];

      for (final item in pendingItems) {
        final type = item['type'] as String?;
        if (type == OfflineQueueItemType.patrolLog || 
            type == OfflineQueueItemType.leaveRequest) {
          independentItems.add(item);
        } else {
          sequentialItems.add(item);
        }
      }

      // 1. Process independent items in parallel (Patrol, Leave)
      // Use Future.wait for massive speedup
      await Future.wait(independentItems.map((item) async {
        final id = item['id'] as String?;
        final type = item['type'] as String?;
        final data = item['data'] as Map<String, dynamic>?;

        if (id == null || type == null || data == null) {
          // processedIds.add(id ?? ''); // Don't modify processedIds in parallel
          // Because list is not thread-safe in some contexts, though Dart is single threaded event loop
          // But to be safe and clean, we should return the ID if successful
          return;
        }

        try {
          if (type == OfflineQueueItemType.patrolLog) {
            await _syncPatrolLog(data);
          } else if (type == OfflineQueueItemType.leaveRequest) {
            await _syncLeaveRequest(data);
          }
          processedIds.add(id);
        } catch (e) {
          // Only skip if it's NOT an offline error (meaning it's a server error we might want to retry or ignore)
          // But if it IS an offline error, we should stop syncing.
          if (e is ApiException && isOfflineError(e)) {
             // Connection dropped during sync, stop the parallel loop by rethrowing
             // or we can simply continue to next loop but we want to break early.
             // In map() we cannot break. We should just return/continue.
             // But to stop OTHER tasks, we rely on them failing too.
             // Since we can't break a Future.wait easily without throwing:
             developer.log('Sync aborted due to offline error.');
             return;
          }
          // If it's a data error (4xx, 5xx), we might want to remove it or keep it.
          // For now, we keep it to be safe, but in production we should have a retry limit.
        }
      }));

      // 2. Process sequential items one by one (Attendance)
      // Strict order is important for clock-in / clock-out
      for (final item in sequentialItems) {
        final id = item['id'] as String?;
        final type = item['type'] as String?;
        final data = item['data'] as Map<String, dynamic>?;
        
        if (id == null || type == null || data == null) {
          processedIds.add(id ?? '');
          continue;
        }

        try {
          if (type == OfflineQueueItemType.attendanceClockIn) {
             await _syncAttendanceClockIn(data);
          } else if (type == OfflineQueueItemType.attendanceClockOut) {
             await _syncAttendanceClockOut(data);
          }
          processedIds.add(id);
        } on ApiException catch (e) {
          if (isOfflineError(e)) {
            // Stop sequential sync if offline
            break;
          }
          processedIds.add(id); // Skip bad request
        } catch (_) {
           processedIds.add(id); // Skip unknown error
        }
      }

      // Batch remove processed items
      if (processedIds.isNotEmpty) {
         await _removeItems(processedIds);
         
         // Notify UI that items were synced
         final remainingCount = await getPendingCount();
         _syncController.add(SyncEvent(
           pendingCount: remainingCount,
           syncedCount: processedIds.length,
           isComplete: remainingCount == 0,
         ));
         
         developer.log(
           'Sync complete: ${processedIds.length} items synced, $remainingCount remaining',
           name: 'OfflineQueueService',
         );
      }

    } finally {
      _syncing = false;
    }
  }

  Future<void> _removeItems(List<String> ids) async {
     final queue = await _loadQueue();
     queue.removeWhere((item) => ids.contains(item['id']));
     await _saveQueue(queue);
  }

  Future<void> _syncAttendanceClockIn(Map<String, dynamic> data) async {
    final map = <String, dynamic>{
      'shift_id': data['shift_id'],
      'latitude': data['latitude'],
      'longitude': data['longitude'],
      'mode': data['mode'],
    };

    if (data['occurred_at'] != null) {
      map['occurred_at'] = data['occurred_at'];
    } else if (data['created_at'] != null) {
      // Backward compatibility for old queue items
      map['occurred_at'] = data['created_at'];
    }

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
      } else {
        // Handle missing file case gracefully
      }
    }

    final formData = FormData.fromMap(map);
    await _apiClient.post<dynamic>(
      '/attendance/clock-in',
      data: formData,
      options: Options(
        sendTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 10),
      ),
    );
  }

  Future<void> _syncAttendanceClockOut(Map<String, dynamic> data) async {
    final map = <String, dynamic>{
      'shift_id': data['shift_id'],
      'latitude': data['latitude'],
      'longitude': data['longitude'],
    };

    if (data['occurred_at'] != null) {
      map['occurred_at'] = data['occurred_at'];
    } else if (data['created_at'] != null) {
      // Backward compatibility
      map['occurred_at'] = data['created_at'];
    }

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
      } else {
        // Handle missing file case gracefully
      }
    }

    final formData = FormData.fromMap(map);
    await _apiClient.post<dynamic>(
      '/attendance/clock-out',
      data: formData,
      options: Options(
        sendTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 10),
      ),
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
      'type': data['type'],
    };

    if (data['occurred_at'] != null) {
      map['occurred_at'] = data['occurred_at'];
    }

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
      options: Options(
        sendTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 10),
      ),
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
