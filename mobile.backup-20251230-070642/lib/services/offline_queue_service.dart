import 'dart:async';
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
    print('[Queue] _addItem: ${item['id']} type=${item['type']}');
    final queue = await _loadQueue();
    queue.add(item);
    await _saveQueue(queue);
    print('[Queue] Queue now has ${queue.length} items');
    
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

  /// Returns the last pending attendance type for today ('clock_in', 'clock_out', or null)
  Future<String?> getLastPendingAttendanceType() async {
    final queue = await _loadQueue();
    final today = DateTime.now();
    
    // Filter attendance items and find the last one for today
    final attendanceItems = queue.where((item) {
      final type = item['type'] as String?;
      return type == OfflineQueueItemType.attendanceClockIn || 
             type == OfflineQueueItemType.attendanceClockOut;
    }).toList();
    
    if (attendanceItems.isEmpty) return null;
    
    // Get the last attendance item
    final lastItem = attendanceItems.last;
    final type = lastItem['type'] as String?;
    
    if (type == OfflineQueueItemType.attendanceClockIn) {
      return 'clock_in';
    } else if (type == OfflineQueueItemType.attendanceClockOut) {
      return 'clock_out';
    }
    return null;
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
    print('[Queue] sync() called, _syncing=$_syncing');
    if (_syncing) {
      print('[Queue] sync() skipped - already syncing');
      return;
    }
    _syncing = true;
    try {
      var queue = await _loadQueue();
      print('[Queue] sync() queue has ${queue.length} items');
      if (queue.isEmpty) {
        print('[Queue] sync() queue is empty, returning');
        return;
      }

      final pendingItems = List<Map<String, dynamic>>.from(queue);
      final processedIds = <String>[];

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

      // Process independent items in parallel
      await Future.wait(independentItems.map((item) async {
        final id = item['id'] as String?;
        final type = item['type'] as String?;
        final data = item['data'] as Map<String, dynamic>?;

        if (id == null || type == null || data == null) return;

        try {
          if (type == OfflineQueueItemType.patrolLog) {
            await _syncPatrolLog(data);
          } else if (type == OfflineQueueItemType.leaveRequest) {
            await _syncLeaveRequest(data);
          }
          print('[Queue] Synced $type: $id');
          processedIds.add(id);
        } catch (e) {
          print('[Queue] Sync error $type: $id - $e');
          if (e is ApiException && isOfflineError(e)) {
            print('[Queue] Sync aborted due to offline error');
            return;
          }
        }
      }));

      // Process sequential items (Attendance)
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
          print('[Queue] Synced attendance: $id');
          processedIds.add(id);
        } on ApiException catch (e) {
          print('[Queue] Attendance sync error: $id - ${e.message} (${e.statusCode})');
          if (isOfflineError(e)) break;
        } catch (e) {
          print('[Queue] Attendance sync unknown error: $id - $e');
        }
      }

      if (processedIds.isNotEmpty) {
         await _removeItems(processedIds);
         final remainingCount = await getPendingCount();
         _syncController.add(SyncEvent(
           pendingCount: remainingCount,
           syncedCount: processedIds.length,
           isComplete: remainingCount == 0,
         ));
         print('[Queue] Sync complete: ${processedIds.length} synced, $remainingCount remaining');
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
      map['occurred_at'] = data['created_at'];
    }

    if (data['note'] != null && (data['note'] as String).isNotEmpty) {
      map['note'] = data['note'];
    }

    final selfiePath = data['selfie_path'] as String?;
    if (selfiePath != null && selfiePath.isNotEmpty) {
      final file = File(selfiePath);
      if (await file.exists()) {
        map['selfie'] = await MultipartFile.fromFile(selfiePath, filename: 'selfie.jpg');
      }
    }

    final formData = FormData.fromMap(map);
    print('[Queue] Sending clock-in: shift=${data['shift_id']} lat=${data['latitude']}');
    await _apiClient.post<dynamic>(
      '/attendance/clock-in',
      data: formData,
      options: Options(
        sendTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
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
      map['occurred_at'] = data['created_at'];
    }

    if (data['note'] != null && (data['note'] as String).isNotEmpty) {
      map['note'] = data['note'];
    }

    final selfiePath = data['selfie_path'] as String?;
    if (selfiePath != null && selfiePath.isNotEmpty) {
      final file = File(selfiePath);
      if (await file.exists()) {
        map['selfie'] = await MultipartFile.fromFile(selfiePath, filename: 'selfie.jpg');
      }
    }

    final formData = FormData.fromMap(map);
    print('[Queue] Sending clock-out: shift=${data['shift_id']}');
    await _apiClient.post<dynamic>(
      '/attendance/clock-out',
      data: formData,
      options: Options(
        sendTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
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

    if (data['description'] != null && (data['description'] as String).isNotEmpty) {
      map['description'] = data['description'];
    }

    final photoPath = data['photo_path'] as String?;
    if (photoPath != null && photoPath.isNotEmpty) {
      final file = File(photoPath);
      if (await file.exists()) {
        map['photo'] = await MultipartFile.fromFile(photoPath, filename: 'patrol_photo.jpg');
      }
    }

    final formData = FormData.fromMap(map);
    print('[Queue] Sending patrol: project=${data['project_id']}');
    await _apiClient.post<dynamic>(
      '/patrol/logs',
      data: formData,
      options: Options(
        sendTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
      ),
    );
  }

  Future<void> _syncLeaveRequest(Map<String, dynamic> data) async {
    await _apiClient.post<dynamic>('/leave-requests', data: data);
  }
}

final offlineQueueServiceProvider = Provider<OfflineQueueService>((ref) {
  final storage = ref.watch(secureStorageServiceProvider);
  final apiClient = ref.watch(apiClientProvider);
  return OfflineQueueService(storage, apiClient);
});
