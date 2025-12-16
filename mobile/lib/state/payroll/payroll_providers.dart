import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../services/api_client.dart';
import '../../services/payroll_service.dart';
import 'payroll_state.dart';

final payrollListNotifierProvider =
    StateNotifierProvider<PayrollListNotifier, PayrollListState>((ref) {
  final service = ref.watch(payrollServiceProvider);
  return PayrollListNotifier(service);
});

class PayrollListNotifier extends StateNotifier<PayrollListState> {
  PayrollListNotifier(this._service) : super(PayrollListState.initial());

  final PayrollService _service;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final items = await _service.getPayrollSlips();
      state = state.copyWith(isLoading: false, items: items);
    } on ApiException catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.message);
    } catch (_) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Gagal memuat data slip gaji.',
      );
    }
  }
}

final payrollDetailNotifierProvider =
    StateNotifierProvider.family<PayrollDetailNotifier, PayrollDetailState, int>(
        (ref, id) {
  final service = ref.watch(payrollServiceProvider);
  return PayrollDetailNotifier(service, id);
});

class PayrollDetailNotifier extends StateNotifier<PayrollDetailState> {
  PayrollDetailNotifier(this._service, this._id)
      : super(PayrollDetailState.initial());

  final PayrollService _service;
  final int _id;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final slip = await _service.getPayrollSlipDetail(_id);
      state = state.copyWith(isLoading: false, slip: slip);
    } on ApiException catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.message);
    } catch (_) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Gagal memuat detail slip gaji.',
      );
    }
  }
}
