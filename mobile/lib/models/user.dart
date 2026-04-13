import 'leave_balance.dart';

class User {
  const User({
    required this.id,
    required this.name,
    required this.username,
    this.email,
    this.role,
    this.activeProjectId,
    this.nip,
    this.position,
    this.division,
    this.profilePhotoPath,
    this.profilePhotoUrl,
    this.activeProjectName,
    this.projectLat,
    this.projectLng,
    this.projectRadius,
    this.leaveBalances,
  });

  final int id;
  final String name;
  final String username;
  final String? email;
  final String? role;
  final int? activeProjectId;
   final String? nip;
   final String? position;
   final String? division;
   final String? profilePhotoPath;
   final String? profilePhotoUrl;
   final String? activeProjectName;
   final double? projectLat;
   final double? projectLng;
   final int? projectRadius;
   final List<LeaveBalance>? leaveBalances;

  factory User.fromJson(Map<String, dynamic> json) {
    List<LeaveBalance>? balances;
    if (json['leave_balances'] is List) {
      balances = (json['leave_balances'] as List)
          .whereType<Map<String, dynamic>>()
          .map(LeaveBalance.fromJson)
          .toList();
    }

    return User(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      username: json['username'] as String? ?? '',
      email: json['email'] as String?,
      role: json['role'] as String?,
      activeProjectId: _parseInt(json['active_project_id']),
      nip: json['nip'] as String?,
      position: json['position'] as String?,
      division: json['division'] as String?,
      profilePhotoPath: json['profile_photo_path'] as String?,
      profilePhotoUrl: json['profile_photo_url'] as String?,
      activeProjectName: json['active_project_name'] as String?,
      projectLat: _parseDouble(json['project_lat']),
      projectLng: _parseDouble(json['project_lng']),
      projectRadius: _parseInt(json['project_radius']),
      leaveBalances: balances,
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
      'position': position,
      'division': division,
      'profile_photo_path': profilePhotoPath,
      'profile_photo_url': profilePhotoUrl,
      'active_project_name': activeProjectName,
      'project_lat': projectLat,
      'project_lng': projectLng,
      'project_radius': projectRadius,
      // Serialize leave balances so cache restore works correctly
      if (leaveBalances != null)
        'leave_balances': leaveBalances!.map((b) => b.toJson()).toList(),
    };
  }
}
