import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/shift.dart';
import 'api_client.dart';

class ShiftService {
  ShiftService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<Shift>> getAvailableShifts() async {
    final Response<dynamic> response =
        await _apiClient.get<dynamic>('/me/available-shifts');

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
        .map(Shift.fromJson)
        .toList();
  }
}

final shiftServiceProvider = Provider<ShiftService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return ShiftService(apiClient);
});
