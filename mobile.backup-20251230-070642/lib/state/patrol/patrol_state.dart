class PatrolState {
  const PatrolState({
    required this.isSubmitting,
    required this.errorMessage,
  });

  factory PatrolState.initial() {
    return const PatrolState(
      isSubmitting: false,
      errorMessage: null,
    );
  }

  final bool isSubmitting;
  final String? errorMessage;

  PatrolState copyWith({
    bool? isSubmitting,
    String? errorMessage,
  }) {
    return PatrolState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      errorMessage: errorMessage,
    );
  }
}
