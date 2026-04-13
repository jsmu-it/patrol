class LeaveType {
  const LeaveType({
    required this.id,
    required this.name,
    this.description,
    this.defaultQuota,
  });

  final int id;
  final String name;
  final String? description;
  final int? defaultQuota;

  factory LeaveType.fromJson(Map<String, dynamic> json) {
    return LeaveType(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      defaultQuota: json['default_quota'] as int?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'default_quota': defaultQuota,
    };
  }
}
