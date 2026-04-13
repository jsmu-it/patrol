class Shift {
  const Shift({
    required this.id,
    required this.name,
    required this.code,
    required this.startTime,
    required this.endTime,
    this.toleranceMinutes,
    required this.isDefault,
  });

  final int id;
  final String name;
  final String code;
  final String startTime;
  final String endTime;
  final int? toleranceMinutes;
  final bool isDefault;

  factory Shift.fromJson(Map<String, dynamic> json) {
    return Shift(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      code: json['code'] as String? ?? '',
      startTime: json['start_time']?.toString() ?? '',
      endTime: json['end_time']?.toString() ?? '',
      toleranceMinutes: json['tolerance_minutes'] as int?,
      isDefault: (json['is_default'] as bool?) ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'code': code,
      'start_time': startTime,
      'end_time': endTime,
      'tolerance_minutes': toleranceMinutes,
      'is_default': isDefault,
    };
  }
}
