class PatrolLog {
  const PatrolLog({
    required this.id,
    required this.userId,
    required this.projectId,
    required this.checkpointId,
    required this.title,
    required this.postName,
    this.description,
    this.photoPath,
    this.latitude,
    this.longitude,
    this.occurredAt,
  });

  final int id;
  final int userId;
  final int projectId;
  final int checkpointId;
  final String title;
  final String postName;
  final String? description;
  final String? photoPath;
  final double? latitude;
  final double? longitude;
  final DateTime? occurredAt;

  factory PatrolLog.fromJson(Map<String, dynamic> json) {
    return PatrolLog(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      projectId: json['project_id'] as int,
      checkpointId: json['checkpoint_id'] as int,
      title: json['title'] as String? ?? '',
      postName: json['post_name'] as String? ?? '',
      description: json['description'] as String?,
      photoPath: json['photo_path'] as String?,
      latitude: json['latitude'] != null
          ? double.tryParse(json['latitude'].toString())
          : null,
      longitude: json['longitude'] != null
          ? double.tryParse(json['longitude'].toString())
          : null,
      occurredAt: json['occurred_at'] != null
          ? DateTime.tryParse(json['occurred_at'] as String)
          : null,
    );
  }
}
