import 'dart:convert';
import 'dart:developer' as developer;

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/shift.dart';
import 'api_client.dart';
import 'connectivity_service.dart';
import 'secure_storage_service.dart';

const _shiftsCacheKey = 'cached_shifts';

class ShiftService {
  ShiftService(this._apiClient, this._storage, this._connectivity);

  final ApiClient _apiClient;
  final SecureStorageService _storage;
  final ConnectivityService _connectivity;

  Future<List<Shift>> getAvailableShifts() async {
    // Fail-fast: Quick network check (instant, no ping)
    final hasNetwork = await _connectivity.hasNetworkType();
    if (!hasNetwork) {
      developer.log('No network. Returning cached shifts immediately.',
          name: 'ShiftService');
      return _loadCachedShifts();
    }

    try {
      final Response<dynamic> response =
          await _apiClient.get<dynamic>('/me/available-shifts');

      final body = response.data;

      final List<dynamic> rawList;
      if (body is Map<String, dynamic> && body['data'] is List) {
        rawList = body['data'] as List<dynamic>;
      } else if (body is List) {
        rawList = body;
      } else {
        return _loadCachedShifts(); // Return cached instead of empty
      }

      final shifts = rawList
          .whereType<Map<String, dynamic>>()
          .map(Shift.fromJson)
          .toList();

      await _cacheShifts(shifts);
      return shifts;
    } catch (e) {
      developer.log(
        'Error fetching shifts. Trying cache...',
        name: 'ShiftService',
        error: e,
      );
      final cached = await _loadCachedShifts();
      if (cached.isNotEmpty) {
        return cached;
      }
      rethrow;
    }
  }

  Future<void> _cacheShifts(List<Shift> shifts) async {
    try {
      final raw = jsonEncode(shifts.map((e) => e.toJson()).toList());
      await _storage.writeRaw(_shiftsCacheKey, raw);
    } catch (e) {
      developer.log('Error caching shifts', name: 'ShiftService', error: e);
    }
  }

  Future<List<Shift>> _loadCachedShifts() async {
    try {
      final raw = await _storage.readRaw(_shiftsCacheKey);
      if (raw == null || raw.isEmpty) return [];

      final decoded = jsonDecode(raw);
      if (decoded is List) {
        return decoded
            .whereType<Map<String, dynamic>>()
            .map(Shift.fromJson)
            .toList();
      }
    } catch (e) {
      developer.log('Error loading cached shifts',
          name: 'ShiftService', error: e);
    }
    return [];
  }
}

final shiftServiceProvider = Provider<ShiftService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final storage = ref.watch(secureStorageServiceProvider);
  final connectivity = ref.watch(connectivityServiceProvider);
  return ShiftService(apiClient, storage, connectivity);
});
