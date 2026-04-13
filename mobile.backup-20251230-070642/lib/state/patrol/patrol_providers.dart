import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../services/patrol_service.dart';
import '../../services/api_client.dart';
import 'patrol_state.dart';

final patrolNotifierProvider =
    StateNotifierProvider<PatrolNotifier, PatrolState>((ref) {
  final service = ref.watch(patrolServiceProvider);
  return PatrolNotifier(service);
});

class PatrolNotifier extends StateNotifier<PatrolState> {
  PatrolNotifier(this._service) : super(PatrolState.initial());

  final PatrolService _service;

  Future<String?> submit({
    required int projectId,
    String? checkpointCode, // Made optional
    required String title,
    required String postName,
    String? description,
    required double latitude,
    required double longitude,
    String? photoPath,
    String? type, // Added type
  }) async {
    state = state.copyWith(isSubmitting: true, errorMessage: null);

    try {
      final message = await _service.createLog(
        projectId: projectId,
        checkpointCode: checkpointCode,
        title: title,
        postName: postName,
        description: description,
        latitude: latitude,
        longitude: longitude,
        photoPath: photoPath,
        type: type,
      );

      state = PatrolState.initial();
      return message;
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
            'Gagal mengirim laporan patroli. Periksa koneksi dan coba lagi.',
      );
      return null;
    }
  }
}
