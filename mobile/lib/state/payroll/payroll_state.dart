import '../../models/payroll_slip.dart';

class PayrollListState {
  const PayrollListState({
    required this.isLoading,
    required this.items,
    this.errorMessage,
  });

  factory PayrollListState.initial() {
    return const PayrollListState(
      isLoading: false,
      items: [],
      errorMessage: null,
    );
  }

  final bool isLoading;
  final List<PayrollSlip> items;
  final String? errorMessage;

  PayrollListState copyWith({
    bool? isLoading,
    List<PayrollSlip>? items,
    String? errorMessage,
  }) {
    return PayrollListState(
      isLoading: isLoading ?? this.isLoading,
      items: items ?? this.items,
      errorMessage: errorMessage,
    );
  }
}

class PayrollDetailState {
  const PayrollDetailState({
    required this.isLoading,
    this.slip,
    this.errorMessage,
  });

  factory PayrollDetailState.initial() {
    return const PayrollDetailState(
      isLoading: false,
      slip: null,
      errorMessage: null,
    );
  }

  final bool isLoading;
  final PayrollSlip? slip;
  final String? errorMessage;

  PayrollDetailState copyWith({
    bool? isLoading,
    PayrollSlip? slip,
    String? errorMessage,
  }) {
    return PayrollDetailState(
      isLoading: isLoading ?? this.isLoading,
      slip: slip,
      errorMessage: errorMessage,
    );
  }
}
