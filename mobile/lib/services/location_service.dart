import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';

class LocationException implements Exception {
  LocationException(this.message);

  final String message;

  @override
  String toString() => 'LocationException: $message';
}

class LocationService {
  Position? _lastPosition;
  DateTime? _lastPositionTime;

  /// Get current position with timeout and fallback to last known position
  /// Max wait time: 5 seconds for fresh GPS, fallback to cached/last known
  Future<Position> getCurrentPosition() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw LocationException(
        'Layanan lokasi tidak aktif. Aktifkan GPS lalu coba lagi.',
      );
    }

    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }

    if (permission == LocationPermission.denied) {
      throw LocationException(
        'Akses lokasi ditolak. Berikan izin lokasi untuk dapat absen.',
      );
    }

    if (permission == LocationPermission.deniedForever) {
      throw LocationException(
        'Akses lokasi ditolak permanen. Aktifkan izin lokasi dari pengaturan.',
      );
    }

    // Use cached position if less than 30 seconds old (instant)
    if (_lastPosition != null && _lastPositionTime != null) {
      final age = DateTime.now().difference(_lastPositionTime!);
      if (age.inSeconds < 30) {
        return _lastPosition!;
      }
    }

    // Try to get last known position first (instant, might be stale but usable)
    Position? lastKnown;
    try {
      lastKnown = await Geolocator.getLastKnownPosition();
    } catch (_) {}

    // Try to get fresh position with short timeout (5 seconds max)
    try {
      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.medium, // Faster than high
          timeLimit: Duration(seconds: 5),
        ),
      );
      
      _lastPosition = position;
      _lastPositionTime = DateTime.now();
      return position;
    } on TimeoutException {
      // Timeout - use last known position if available
      if (lastKnown != null) {
        _lastPosition = lastKnown;
        _lastPositionTime = DateTime.now();
        return lastKnown;
      }
      
      // No fallback available
      throw LocationException(
        'Gagal mendapatkan lokasi. Pastikan GPS aktif dan coba lagi.',
      );
    }
  }

  /// Get position quickly - prefer cached, then last known, then fresh with timeout
  Future<Position> getQuickPosition() async {
    // 1. Use cached if recent
    if (_lastPosition != null && _lastPositionTime != null) {
      final age = DateTime.now().difference(_lastPositionTime!);
      if (age.inMinutes < 2) {
        return _lastPosition!;
      }
    }

    // 2. Try last known (instant)
    try {
      final lastKnown = await Geolocator.getLastKnownPosition();
      if (lastKnown != null) {
        _lastPosition = lastKnown;
        _lastPositionTime = DateTime.now();
        return lastKnown;
      }
    } catch (_) {}

    // 3. Fall back to regular method
    return getCurrentPosition();
  }
}

final locationServiceProvider = Provider<LocationService>((ref) {
  return LocationService();
});
