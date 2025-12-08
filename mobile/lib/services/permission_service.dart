import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:permission_handler/permission_handler.dart';

class PermissionService {
  Future<void> requestInitialPermissions() async {
    // Request crucial permissions at startup
    // We ask for Location and Camera as they are core to the app
    await [
      Permission.location,
      Permission.camera,
      Permission.notification, // For offline/online alerts if needed
    ].request();
  }

  Future<bool> checkLocationPermission() async {
    return await Permission.location.isGranted;
  }

  Future<bool> checkCameraPermission() async {
    return await Permission.camera.isGranted;
  }
}

final permissionServiceProvider = Provider<PermissionService>((ref) {
  return PermissionService();
});
