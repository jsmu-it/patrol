import '../../models/attendance_record.dart';

class AttendanceHistoryState {
  const AttendanceHistoryState({
    required this.isLoading,
    required this.records,
    required this.errorMessage,
    required this.from,
    required this.to,
  });

  factory AttendanceHistoryState.initial() {
    final now = DateTime.now();
    final from = now.subtract(const Duration(days: 7));
    return AttendanceHistoryState(
      isLoading: false,
      records: const [],
      errorMessage: null,
      from: DateTime(from.year, from.month, from.day),
      to: DateTime(now.year, now.month, now.day),
    );
  }

  final bool isLoading;
  final List<AttendanceRecord> records;
  final String? errorMessage;
  final DateTime from;
  final DateTime to;

  AttendanceHistoryState copyWith({
    bool? isLoading,
    List<AttendanceRecord>? records,
    String? errorMessage,
    DateTime? from,
    DateTime? to,
  }) {
    return AttendanceHistoryState(
      isLoading: isLoading ?? this.isLoading,
      records: records ?? this.records,
      errorMessage: errorMessage,
      from: from ?? this.from,
      to: to ?? this.to,
    );
  }
}
