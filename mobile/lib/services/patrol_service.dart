import 'dart:convert';
import 'dart:developer' as developer;

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../models/patrol_log.dart';
import 'api_client.dart';
import 'offline_queue_service.dart';
import 'connectivity_service.dart';
import 'secure_storage_service.dart';

const _checkpointCacheKey = 'cached_checkpoints';

class PatrolService {
  PatrolService(this._apiClient, this._offlineQueue, this._connectivity, this._storage);

  final ApiClient _apiClient;
  final OfflineQueueService _offlineQueue;
  final ConnectivityService _connectivity;
  final SecureStorageService _storage;

  Future<List<PatrolLog>> getHistory() async {
    try {
      final response = await _apiClient.get<dynamic>('/patrol/history');
      final body = response.data;

      final List<dynamic> rawList;
      if (body is Map<String, dynamic> && body['data'] is List) {
        rawList = body['data'] as List<dynamic>;
      } else if (body is List) {
        rawList = body;
      } else {
        return [];
      }

      return rawList
          .whereType<Map<String, dynamic>>()
          .map(PatrolLog.fromJson)
          .toList();
    } catch (e) {
      developer.log('Error fetching patrol history',
          name: 'PatrolService', error: e);
      // If offline or error, return empty list for now (or could implement caching)
      return [];
    }
  }

  Future<String> createLog({
    required int projectId,
    String? checkpointCode,
    required String title,
    required String postName,
    String? description,
    required double latitude,
    required double longitude,
    String? photoPath,
    String? type,
  }) async {
    final now = DateTime.now();
    final occurredAt = DateFormat('dd-MM-yyyy HH:mm').format(now);

    // OPTIMISTIC PATTERN: Always queue first for instant response (max 2-3 seconds)
    await _offlineQueue.enqueuePatrolLog(
      projectId: projectId,
      checkpointCode: checkpointCode ?? '',
      title: title,
      postName: postName,
      description: description,
      latitude: latitude,
      longitude: longitude,
      photoPath: photoPath,
      type: type,
      occurredAt: occurredAt,
    );

    // Always try to sync immediately in background
    developer.log('Triggering sync after patrol log', name: 'PatrolService');
    _offlineQueue.sync().then((_) {
      developer.log('Patrol sync completed', name: 'PatrolService');
    }).catchError((e) {
      developer.log('Patrol sync failed: $e', name: 'PatrolService');
    });

    return 'Laporan patroli berhasil. Sedang mengirim ke server...';
  }

  Future<Map<String, dynamic>> getCheckpoint(String code) async {
    try {
      // Check local cache first if offline or strictly preferred
      // But typically we want fresh data if online.
      // However, user requested offline support for this.
      
      // Quick network check (instant, no ping)
      final hasNetwork = await _connectivity.hasNetworkType();
      if (!hasNetwork) {
        developer.log('Offline: checking cached checkpoints for $code');
        final cached = await _getCachedCheckpoint(code);
        if (cached != null) return cached;
        return {};
      }

      final response = await _apiClient.get<Map<String, dynamic>>(
        '/patrol/checkpoint',
        queryParameters: {'code': code},
      );

      final data = response.data ?? {};
      
      // Cache successful fetch
      if (data.isNotEmpty) {
        await _cacheCheckpoint(code, data);
      }

      return data;
    } catch (e) {
      developer.log('Error fetching checkpoint. Trying cache.', error: e);
      final cached = await _getCachedCheckpoint(code);
      if (cached != null) return cached;
      
      return {};
    }
  }

  Future<void> _cacheCheckpoint(String code, Map<String, dynamic> data) async {
    try {
      final raw = await _storage.readRaw(_checkpointCacheKey);
      Map<String, dynamic> cache = {};
      if (raw != null && raw.isNotEmpty) {
        cache = Map<String, dynamic>.from(jsonDecode(raw));
      }
      
      cache[code] = data;
      
      await _storage.writeRaw(_checkpointCacheKey, jsonEncode(cache));
    } catch (e) {
      developer.log('Error caching checkpoint', error: e);
    }
  }

  Future<Map<String, dynamic>?> _getCachedCheckpoint(String code) async {
    try {
      final raw = await _storage.readRaw(_checkpointCacheKey);
      if (raw == null || raw.isEmpty) return null;

      final cache = jsonDecode(raw);
      if (cache is Map && cache.containsKey(code)) {
        return Map<String, dynamic>.from(cache[code]);
      }
    } catch (e) {
      developer.log('Error reading cached checkpoint', error: e);
    }
    return null;
  }
}

final patrolServiceProvider = Provider<PatrolService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final offlineQueue = ref.watch(offlineQueueServiceProvider);
  final connectivity = ref.watch(connectivityServiceProvider);
  final storage = ref.watch(secureStorageServiceProvider);
  return PatrolService(apiClient, offlineQueue, connectivity, storage);
});
