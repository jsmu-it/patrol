import 'package:intl/intl.dart';

class AttendanceRecord {
  const AttendanceRecord({
    required this.id,
    required this.userId,
    required this.projectId,
    required this.shiftId,
    required this.type,
    required this.occurredAt,
    required this.latitude,
    required this.longitude,
    this.selfiePhotoPath,
    this.note,
    required this.mode,
    this.statusDinas,
  });

  final int id;
  final int userId;
  final int projectId;
  final int shiftId;
  final String type;
  final DateTime? occurredAt;
  final double? latitude;
  final double? longitude;
  final String? selfiePhotoPath;
  final String? note;
  final String mode;
  final String? statusDinas;

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) {
    return AttendanceRecord(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      projectId: json['project_id'] as int,
      shiftId: json['shift_id'] as int,
      type: json['type'] as String? ?? '',
      occurredAt: json['occurred_at'] != null
          ? DateFormat('dd-MM-yyyy HH:mm').parse(json['occurred_at'] as String)
          : null,
      latitude: json['latitude'] != null
          ? double.tryParse(json['latitude'].toString())
          : null,
      longitude: json['longitude'] != null
          ? double.tryParse(json['longitude'].toString())
          : null,
      selfiePhotoPath: json['selfie_photo_path'] as String?,
      note: json['note'] as String?,
      mode: json['mode'] as String? ?? 'normal',
      statusDinas: json['status_dinas'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'project_id': projectId,
      'shift_id': shiftId,
      'type': type,
      'occurred_at': occurredAt != null 
          ? DateFormat('dd-MM-yyyy HH:mm').format(occurredAt!)
          : null,
      'latitude': latitude,
      'longitude': longitude,
      'selfie_photo_path': selfiePhotoPath,
      'note': note,
      'mode': mode,
      'status_dinas': statusDinas,
    };
  }
}
