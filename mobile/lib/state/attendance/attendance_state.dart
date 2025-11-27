class AttendanceState {
  const AttendanceState({
    required this.isCheckingIn,
    required this.isCheckingOut,
    required this.lastMessage,
    required this.errorMessage,
  });

  factory AttendanceState.initial() {
    return const AttendanceState(
      isCheckingIn: false,
      isCheckingOut: false,
      lastMessage: null,
      errorMessage: null,
    );
  }

  final bool isCheckingIn;
  final bool isCheckingOut;
  final String? lastMessage;
  final String? errorMessage;

  AttendanceState copyWith({
    bool? isCheckingIn,
    bool? isCheckingOut,
    String? lastMessage,
    String? errorMessage,
  }) {
    return AttendanceState(
      isCheckingIn: isCheckingIn ?? this.isCheckingIn,
      isCheckingOut: isCheckingOut ?? this.isCheckingOut,
      lastMessage: lastMessage,
      errorMessage: errorMessage,
    );
  }
}
