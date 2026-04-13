import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../services/leave_service.dart';
import '../../services/api_client.dart';
import 'leave_form_state.dart';

final leaveFormNotifierProvider =
    StateNotifierProvider<LeaveFormNotifier, LeaveFormState>((ref) {
  final service = ref.watch(leaveServiceProvider);
  return LeaveFormNotifier(service);
});

class LeaveFormNotifier extends StateNotifier<LeaveFormState> {
  LeaveFormNotifier(this._service) : super(LeaveFormState.initial());

  final LeaveService _service;

  Future<String?> submit({
    required String type,
    int? leaveTypeId,
    required DateTime dateFrom,
    required DateTime dateTo,
    String? timeFrom,
    String? timeTo,
    required String reason,
    String? doctorNote,
    String? sickLetterPath,
    String? permitPhotoPath,
  }) async {
    state = state.copyWith(isSubmitting: true, errorMessage: null);
    try {
      final leave = await _service.create(
        type: type,
        leaveTypeId: leaveTypeId,
        dateFrom: dateFrom,
        dateTo: dateTo,
        timeFrom: timeFrom,
        timeTo: timeTo,
        reason: reason,
        doctorNote: doctorNote,
        sickLetterPath: sickLetterPath,
        permitPhotoPath: permitPhotoPath,
      );
      state = LeaveFormState.initial();
      return 'Pengajuan ${leave.type} berhasil disimpan.';
    } on ApiException catch (e) {
      state = state.copyWith(
        isSubmitting: false,
        errorMessage: e.message,
      );
      return null;
    } catch (_) {
      state = state.copyWith(
        isSubmitting: false,
        errorMessage:
            'Gagal mengirim pengajuan. Periksa koneksi dan coba lagi.',
      );
      return null;
    }
  }
}
