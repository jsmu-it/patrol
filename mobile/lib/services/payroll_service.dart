import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/payroll_slip.dart';
import 'api_client.dart';

class PayrollService {
  PayrollService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<PayrollSlip>> getPayrollSlips() async {
    final response = await _apiClient.get<dynamic>('/payroll');

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
        .map(PayrollSlip.fromJson)
        .toList();
  }

  Future<PayrollSlip> getPayrollSlipDetail(int id) async {
    final response = await _apiClient.get<dynamic>('/payroll/$id');

    final body = response.data;

    if (body is Map<String, dynamic>) {
      if (body.containsKey('data') && body['data'] is Map<String, dynamic>) {
        return PayrollSlip.fromJson(body['data'] as Map<String, dynamic>);
      }
      return PayrollSlip.fromJson(body);
    }

    throw ApiException('Format response tidak valid');
  }
}

final payrollServiceProvider = Provider<PayrollService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return PayrollService(apiClient);
});
