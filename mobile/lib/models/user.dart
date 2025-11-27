class User {
  const User({
    required this.id,
    required this.name,
    required this.username,
    this.email,
    this.role,
    this.activeProjectId,
    this.nip,
    this.profilePhotoPath,
    this.profilePhotoUrl,
    this.activeProjectName,
    this.projectLat,
    this.projectLng,
    this.projectRadius,
  });

  final int id;
  final String name;
  final String username;
  final String? email;
  final String? role;
  final int? activeProjectId;
   final String? nip;
   final String? profilePhotoPath;
   final String? profilePhotoUrl;
   final String? activeProjectName;
   final double? projectLat;
   final double? projectLng;
   final int? projectRadius;

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      username: json['username'] as String? ?? '',
      email: json['email'] as String?,
      role: json['role'] as String?,
      activeProjectId: _parseInt(json['active_project_id']),
      nip: json['nip'] as String?,
      profilePhotoPath: json['profile_photo_path'] as String?,
      profilePhotoUrl: json['profile_photo_url'] as String?,
      activeProjectName: json['active_project_name'] as String?,
      projectLat: _parseDouble(json['project_lat']),
      projectLng: _parseDouble(json['project_lng']),
      projectRadius: _parseInt(json['project_radius']),
    );
  }

  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    if (value is String) return int.tryParse(value);
    return null;
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'username': username,
      'email': email,
      'role': role,
      'active_project_id': activeProjectId,
      'nip': nip,
      'profile_photo_path': profilePhotoPath,
      'profile_photo_url': profilePhotoUrl,
      'active_project_name': activeProjectName,
      'project_lat': projectLat,
      'project_lng': projectLng,
      'project_radius': projectRadius,
    };
  }
}
