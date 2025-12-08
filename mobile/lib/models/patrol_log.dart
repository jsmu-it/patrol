import 'package:intl/intl.dart';

class PatrolLog {
  const PatrolLog({
    required this.id,
    required this.projectId,
    this.checkpointCode,
    required this.title,
    required this.postName,
    this.description,
    required this.latitude,
    required this.longitude,
    this.photoPath,
    this.type,
    required this.createdAt,
  });

  final int id;
  final int projectId;
  final String? checkpointCode;
  final String title;
  final String postName;
  final String? description;
  final double latitude;
  final double longitude;
  final String? photoPath;
  final String? type;
  final DateTime createdAt;

  factory PatrolLog.fromJson(Map<String, dynamic> json) {
    return PatrolLog(
      id: json['id'] as int,
      projectId: json['project_id'] is int 
          ? json['project_id'] as int 
          : int.parse(json['project_id'].toString()),
      checkpointCode: json['checkpoint_code'] as String?,
      title: json['title'] as String? ?? 'Tanpa Judul',
      postName: json['post_name'] as String? ?? '-',
      description: json['description'] as String?,
      latitude: double.tryParse(json['latitude'].toString()) ?? 0.0,
      longitude: double.tryParse(json['longitude'].toString()) ?? 0.0,
      photoPath: json['photo_path'] as String?,
      type: json['type'] as String?,
      // Use occurred_at if available, fallback to created_at, then now
      // Format matches d-m-Y H:i (dd-MM-yyyy HH:mm)
      createdAt: json['occurred_at'] != null
          ? DateFormat('dd-MM-yyyy HH:mm').parse(json['occurred_at'] as String)
          : (json['created_at'] != null
              ? DateFormat('dd-MM-yyyy HH:mm').parse(json['created_at'] as String)
              : DateTime.now()),
    );
  }
}
