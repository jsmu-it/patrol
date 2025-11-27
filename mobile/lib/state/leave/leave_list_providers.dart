import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../services/leave_service.dart';
import '../../services/api_client.dart';
import 'leave_list_state.dart';

final leaveListNotifierProvider =
    StateNotifierProvider<LeaveListNotifier, LeaveListState>((ref) {
  final service = ref.watch(leaveServiceProvider);
  return LeaveListNotifier(service);
});

class LeaveListNotifier extends StateNotifier<LeaveListState> {
  LeaveListNotifier(this._service) : super(LeaveListState.initial());

  final LeaveService _service;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final items = await _service.list();
      state = state.copyWith(
        isLoading: false,
        items: items,
        errorMessage: null,
      );
    } on ApiException catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.message,
        items: const [],
      );
    } catch (_) {
      state = state.copyWith(
        isLoading: false,
        errorMessage:
            'Gagal memuat pengajuan izin/cuti. Periksa koneksi dan coba lagi.',
        items: const [],
      );
    }
  }
}
