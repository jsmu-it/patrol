class TimezoneHelper {
  /// Converts a DateTime (which is likely UTC from backend) to Jakarta Time (UTC+7).
  static DateTime toJakarta(DateTime dateTime) {
    // 1. Normalize to UTC.
    // If input comes from JSON parse without 'Z', it might be treated as Local.
    // We must treat it as UTC because our backend sends UTC.
    final utc = dateTime.isUtc ? dateTime : DateTime.utc(
      dateTime.year, dateTime.month, dateTime.day,
      dateTime.hour, dateTime.minute, dateTime.second,
      dateTime.millisecond, dateTime.microsecond
    );
    
    // 2. Add offset
    final jakartaTime = utc.add(const Duration(hours: 7));
    
    // 3. Return as "Local" representation (stripping timezone info)
    return DateTime(
      jakartaTime.year,
      jakartaTime.month,
      jakartaTime.day,
      jakartaTime.hour,
      jakartaTime.minute,
      jakartaTime.second,
    );
  }
}
