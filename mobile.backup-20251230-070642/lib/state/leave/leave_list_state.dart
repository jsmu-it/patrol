import '../../models/leave_request.dart';

class LeaveListState {
  const LeaveListState({
    required this.isLoading,
    required this.items,
    required this.errorMessage,
  });

  factory LeaveListState.initial() {
    return const LeaveListState(
      isLoading: false,
      items: [],
      errorMessage: null,
    );
  }

  final bool isLoading;
  final List<LeaveRequest> items;
  final String? errorMessage;

  LeaveListState copyWith({
    bool? isLoading,
    List<LeaveRequest>? items,
    String? errorMessage,
  }) {
    return LeaveListState(
      isLoading: isLoading ?? this.isLoading,
      items: items ?? this.items,
      errorMessage: errorMessage,
    );
  }
}
