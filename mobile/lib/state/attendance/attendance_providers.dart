import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../services/attendance_service.dart';
import '../../services/api_client.dart';
import 'attendance_state.dart';

final attendanceNotifierProvider =
    StateNotifierProvider<AttendanceNotifier, AttendanceState>((ref) {
  final service = ref.watch(attendanceServiceProvider);
  return AttendanceNotifier(service);
});

class AttendanceNotifier extends StateNotifier<AttendanceState> {
  AttendanceNotifier(this._service) : super(AttendanceState.initial());

  final AttendanceService _service;

  Future<void> checkIn({
    required int shiftId,
    required double latitude,
    required double longitude,
    required String mode,
    String? note,
    required String selfiePath,
  }) async {
    state = state.copyWith(
      isCheckingIn: true,
      lastMessage: null,
      errorMessage: null,
    );

    try {
      final result = await _service.checkIn(
        shiftId: shiftId,
        latitude: latitude,
        longitude: longitude,
        mode: mode,
        note: note,
        selfiePath: selfiePath,
      );

      state = state.copyWith(
        isCheckingIn: false,
        lastMessage: result.message,
        errorMessage: null,
      );
    } on ApiException catch (e) {
      state = state.copyWith(
        isCheckingIn: false,
        errorMessage: e.message,
        lastMessage: null,
      );
    } catch (_) {
      state = state.copyWith(
        isCheckingIn: false,
        errorMessage: 'Gagal melakukan absen. Silakan coba lagi.',
        lastMessage: null,
      );
    }
  }

  Future<void> checkOut({
    required int shiftId,
    required double latitude,
    required double longitude,
    String? note,
    String? selfiePath,
  }) async {
    state = state.copyWith(
      isCheckingOut: true,
      lastMessage: null,
      errorMessage: null,
    );

    try {
      final result = await _service.checkOut(
        shiftId: shiftId,
        latitude: latitude,
        longitude: longitude,
        note: note,
        selfiePath: selfiePath,
      );

      state = state.copyWith(
        isCheckingOut: false,
        lastMessage: result.message,
        errorMessage: null,
      );
    } on ApiException catch (e) {
      state = state.copyWith(
        isCheckingOut: false,
        errorMessage: e.message,
        lastMessage: null,
      );
    } catch (_) {
      state = state.copyWith(
        isCheckingOut: false,
        errorMessage: 'Gagal melakukan absen. Silakan coba lagi.',
        lastMessage: null,
      );
    }
  }
}
