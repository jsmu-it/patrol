class LeaveRequest {
  const LeaveRequest({
    required this.id,
    required this.userId,
    required this.type,
    required this.dateFrom,
    required this.dateTo,
    required this.reason,
    required this.status,
    this.doctorNote,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final int userId;
  final String type;
  final DateTime? dateFrom;
  final DateTime? dateTo;
  final String reason;
  final String status;
  final String? doctorNote;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  factory LeaveRequest.fromJson(Map<String, dynamic> json) {
    return LeaveRequest(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      type: json['type'] as String? ?? '',
      dateFrom: json['date_from'] != null
          ? DateTime.tryParse(json['date_from'] as String)
          : null,
      dateTo: json['date_to'] != null
          ? DateTime.tryParse(json['date_to'] as String)
          : null,
      reason: json['reason'] as String? ?? '',
      status: json['status'] as String? ?? '',
      doctorNote: json['doctor_note'] as String?,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.tryParse(json['updated_at'] as String)
          : null,
    );
  }
}
