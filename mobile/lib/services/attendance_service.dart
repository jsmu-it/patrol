import 'dart:convert';
import 'dart:developer' as developer;

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../models/attendance_record.dart';
import 'api_client.dart';
import 'connectivity_service.dart';
import 'offline_queue_service.dart';
import 'secure_storage_service.dart';

const _attendanceHistoryCacheKey = 'cached_attendance_history';

typedef AttendanceResult = ({String message});

class AttendanceService {
  AttendanceService(this._apiClient, this._offlineQueue, this._storage,
      this._connectivity);

  final ApiClient _apiClient;
  final OfflineQueueService _offlineQueue;
  final SecureStorageService _storage;
  final ConnectivityService _connectivity;

  Future<AttendanceResult> checkIn({
    required int shiftId,
    required double latitude,
    required double longitude,
    required String mode,
    String? note,
    required String selfiePath,
  }) async {
    final now = DateTime.now();
    final occurredAt = DateFormat('dd-MM-yyyy HH:mm').format(now);

    // OPTIMISTIC PATTERN: Always queue first for instant response
    // This ensures max 2-3 second response time
    await _offlineQueue.enqueueAttendanceClockIn(
      shiftId: shiftId,
      latitude: latitude,
      longitude: longitude,
      mode: mode,
      note: note,
      selfiePath: selfiePath,
      occurredAt: occurredAt,
    );

    // Always try to sync immediately in background
    developer.log('Triggering sync after clock-in', name: 'AttendanceService');
    _offlineQueue.sync().then((_) {
      developer.log('Clock-in sync completed', name: 'AttendanceService');
    }).catchError((e) {
      developer.log('Clock-in sync failed: $e', name: 'AttendanceService');
    });

    return (message: 'Absen masuk berhasil. Sedang mengirim ke server...');
  }

  Future<AttendanceResult> checkOut({
    required int shiftId,
    required double latitude,
    required double longitude,
    String? note,
    String? selfiePath,
  }) async {
    final now = DateTime.now();
    final occurredAt = DateFormat('dd-MM-yyyy HH:mm').format(now);

    // OPTIMISTIC PATTERN: Always queue first for instant response
    await _offlineQueue.enqueueAttendanceClockOut(
      shiftId: shiftId,
      latitude: latitude,
      longitude: longitude,
      note: note,
      selfiePath: selfiePath,
      occurredAt: occurredAt,
    );

    // Always try to sync immediately in background
    developer.log('Triggering sync after clock-out', name: 'AttendanceService');
    _offlineQueue.sync().then((_) {
      developer.log('Clock-out sync completed', name: 'AttendanceService');
    }).catchError((e) {
      developer.log('Clock-out sync failed: $e', name: 'AttendanceService');
    });

    return (message: 'Absen keluar berhasil. Sedang mengirim ke server...');
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

    // Fail-fast: Quick network check (instant, no ping)
    final hasNetwork = await _connectivity.hasNetworkType();
    if (!hasNetwork) {
      developer.log('No network. Returning cached history.',
          name: 'AttendanceService');
      return _loadCachedHistory();
    }

    try {
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

      final history = rawList
          .whereType<Map<String, dynamic>>()
          .map(AttendanceRecord.fromJson)
          .toList();

      // Cache only if we are fetching the default range (last 30 days or so)
      // For simplicity, we just cache the last successful fetch regardless of range
      // because usually the home screen fetches the relevant recent history.
      await _cacheHistory(history);

      return history;
    } catch (e) {
      developer.log(
        'Error fetching history. Trying cache...',
        name: 'AttendanceService',
        error: e,
      );
      final cached = await _loadCachedHistory();
      if (cached.isNotEmpty) {
        return cached;
      }
      // If cache is empty, return empty list instead of crashing
      // so the user can at least see the UI.
      return [];
    }
  }

  Future<void> _cacheHistory(List<AttendanceRecord> history) async {
    try {
      final raw = jsonEncode(history.map((e) => e.toJson()).toList());
      await _storage.writeRaw(_attendanceHistoryCacheKey, raw);
    } catch (e) {
      developer.log('Error caching history',
          name: 'AttendanceService', error: e);
    }
  }

  Future<List<AttendanceRecord>> _loadCachedHistory() async {
    try {
      final raw = await _storage.readRaw(_attendanceHistoryCacheKey);
      if (raw == null || raw.isEmpty) return [];

      final decoded = jsonDecode(raw);
      if (decoded is List) {
        return decoded
            .whereType<Map<String, dynamic>>()
            .map(AttendanceRecord.fromJson)
            .toList();
      }
    } catch (e) {
      developer.log('Error loading cached history',
          name: 'AttendanceService', error: e);
    }
    return [];
  }
}

final attendanceServiceProvider = Provider<AttendanceService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final offlineQueue = ref.watch(offlineQueueServiceProvider);
  final storage = ref.watch(secureStorageServiceProvider);
  final connectivity = ref.watch(connectivityServiceProvider);
  return AttendanceService(apiClient, offlineQueue, storage, connectivity);
});
