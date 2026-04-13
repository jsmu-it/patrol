class LeaveBalance {
  const LeaveBalance({
    required this.leaveTypeId,
    required this.leaveTypeName,
    required this.quota,
    required this.used,
    required this.remaining,
  });

  final int leaveTypeId;
  final String leaveTypeName;
  final int quota;
  final int used;
  final int remaining;

  factory LeaveBalance.fromJson(Map<String, dynamic> json) {
    return LeaveBalance(
      leaveTypeId: json['leave_type_id'] as int,
      leaveTypeName: json['leave_type_name'] as String,
      quota: json['quota'] as int,
      used: json['used'] as int,
      remaining: json['remaining'] as int,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'leave_type_id': leaveTypeId,
      'leave_type_name': leaveTypeName,
      'quota': quota,
      'used': used,
      'remaining': remaining,
    };
  }
}
