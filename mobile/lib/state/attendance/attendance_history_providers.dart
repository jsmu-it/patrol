import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../services/attendance_service.dart';
import '../../services/api_client.dart';
import 'attendance_history_state.dart';

final attendanceHistoryNotifierProvider = StateNotifierProvider<
    AttendanceHistoryNotifier, AttendanceHistoryState>((ref) {
  final service = ref.watch(attendanceServiceProvider);
  return AttendanceHistoryNotifier(service);
});

class AttendanceHistoryNotifier extends StateNotifier<AttendanceHistoryState> {
  AttendanceHistoryNotifier(this._service)
      : super(AttendanceHistoryState.initial());

  final AttendanceService _service;

  Future<void> loadInitial() async {
    await loadForRange(state.from, state.to);
  }

  Future<void> loadForRange(DateTime from, DateTime to) async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final records = await _service.getHistory(from: from, to: to);
      state = state.copyWith(
        isLoading: false,
        records: records,
        errorMessage: null,
        from: DateTime(from.year, from.month, from.day),
        to: DateTime(to.year, to.month, to.day),
      );
    } on ApiException catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.message,
        records: const [],
      );
    } catch (_) {
      state = state.copyWith(
        isLoading: false,
        errorMessage:
            'Gagal memuat riwayat absensi. Periksa koneksi dan coba lagi.',
        records: const [],
      );
    }
  }
}
