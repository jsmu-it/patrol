class PayrollSlipItem {
  const PayrollSlipItem({
    required this.label,
    required this.amount,
  });

  final String label;
  final double amount;

  factory PayrollSlipItem.fromJson(Map<String, dynamic> json) {
    return PayrollSlipItem(
      label: json['label'] as String? ?? '',
      amount: (json['amount'] as num?)?.toDouble() ?? 0,
    );
  }
}

class PayrollSlip {
  const PayrollSlip({
    required this.id,
    required this.periodMonth,
    this.nip,
    this.name,
    this.unit,
    this.position,
    required this.totalIncome,
    required this.totalDeduction,
    required this.netIncome,
    this.signLocation,
    this.signDate,
    this.incomeItems = const [],
    this.deductionItems = const [],
    this.createdAt,
  });

  final int id;
  final String periodMonth;
  final String? nip;
  final String? name;
  final String? unit;
  final String? position;
  final double totalIncome;
  final double totalDeduction;
  final double netIncome;
  final String? signLocation;
  final String? signDate;
  final List<PayrollSlipItem> incomeItems;
  final List<PayrollSlipItem> deductionItems;
  final String? createdAt;

  factory PayrollSlip.fromJson(Map<String, dynamic> json) {
    return PayrollSlip(
      id: json['id'] as int,
      periodMonth: json['period_month'] as String? ?? '',
      nip: json['nip'] as String?,
      name: json['name'] as String?,
      unit: json['unit'] as String?,
      position: json['position'] as String?,
      totalIncome: (json['total_income'] as num?)?.toDouble() ?? 0,
      totalDeduction: (json['total_deduction'] as num?)?.toDouble() ?? 0,
      netIncome: (json['net_income'] as num?)?.toDouble() ?? 0,
      signLocation: json['sign_location'] as String?,
      signDate: json['sign_date'] as String?,
      incomeItems: (json['income_items'] as List<dynamic>?)
              ?.map((e) => PayrollSlipItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      deductionItems: (json['deduction_items'] as List<dynamic>?)
              ?.map((e) => PayrollSlipItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      createdAt: json['created_at'] as String?,
    );
  }
}
