class AppNotification {
  const AppNotification({
    required this.id,
    required this.title,
    required this.body,
    required this.data,
    required this.receivedAt,
    this.isRead = false,
  });

  final String id;
  final String title;
  final String body;
  final Map<String, dynamic> data;
  final DateTime receivedAt;
  final bool isRead;

  AppNotification copyWith({
    String? id,
    String? title,
    String? body,
    Map<String, dynamic>? data,
    DateTime? receivedAt,
    bool? isRead,
  }) {
    return AppNotification(
      id: id ?? this.id,
      title: title ?? this.title,
      body: body ?? this.body,
      data: data ?? this.data,
      receivedAt: receivedAt ?? this.receivedAt,
      isRead: isRead ?? this.isRead,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'body': body,
      'data': data,
      'receivedAt': receivedAt.toIso8601String(),
      'isRead': isRead,
    };
  }

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: json['id'] as String,
      title: json['title'] as String,
      body: json['body'] as String,
      data: Map<String, dynamic>.from(json['data'] as Map),
      receivedAt: DateTime.parse(json['receivedAt'] as String),
      isRead: json['isRead'] as bool? ?? false,
    );
  }
}
