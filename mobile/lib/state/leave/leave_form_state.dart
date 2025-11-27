class LeaveFormState {
  const LeaveFormState({
    required this.isSubmitting,
    required this.errorMessage,
  });

  factory LeaveFormState.initial() {
    return const LeaveFormState(
      isSubmitting: false,
      errorMessage: null,
    );
  }

  final bool isSubmitting;
  final String? errorMessage;

  LeaveFormState copyWith({
    bool? isSubmitting,
    String? errorMessage,
  }) {
    return LeaveFormState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      errorMessage: errorMessage,
    );
  }
}
