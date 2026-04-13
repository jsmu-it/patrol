class LeaveRequest {
  const LeaveRequest({
    required this.id,
    required this.userId,
    required this.type,
    this.leaveTypeId,
    this.leaveTypeName,
    required this.dateFrom,
    required this.dateTo,
    this.timeFrom,
    this.timeTo,
    required this.reason,
    required this.status,
    this.doctorNote,
    this.permitPhoto,
    this.userName,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final int userId;
  final String type;
  final int? leaveTypeId;
  final String? leaveTypeName;
  final DateTime? dateFrom;
  final DateTime? dateTo;
  final String? timeFrom; // HH:mm format
  final String? timeTo;   // HH:mm format
  final String reason;
  final String status;
  final String? doctorNote;
  final String? permitPhoto; // URL
  final String? userName; // User name from backend 'user' relation
  final DateTime? createdAt;
  final DateTime? updatedAt;

  factory LeaveRequest.fromJson(Map<String, dynamic> json) {
    // Extract user name from user object if present
    String? userName;
    if (json['user'] is Map<String, dynamic>) {
      userName = json['user']['name'] as String?;
    }

    // Extract leave type name from leave_type object if present
    String? leaveTypeName;
    if (json['leave_type'] is Map<String, dynamic>) {
      leaveTypeName = json['leave_type']['name'] as String?;
    }

    return LeaveRequest(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      type: json['type'] as String? ?? '',
      leaveTypeId: json['leave_type_id'] as int?,
      leaveTypeName: leaveTypeName,
      dateFrom: json['date_from'] != null
          ? DateTime.tryParse(json['date_from'] as String)
          : null,
      dateTo: json['date_to'] != null
          ? DateTime.tryParse(json['date_to'] as String)
          : null,
      timeFrom: json['time_from'] as String?,
      timeTo: json['time_to'] as String?,
      reason: json['reason'] as String? ?? '',
      status: json['status'] as String? ?? '',
      doctorNote: json['doctor_note'] as String?,
      permitPhoto: json['permit_photo'] as String?,
      userName: userName,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.tryParse(json['updated_at'] as String)
          : null,
    );
  }
}
