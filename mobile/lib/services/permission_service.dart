import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:permission_handler/permission_handler.dart';

class PermissionService {
  Future<void> requestInitialPermissions() async {
    // Request crucial permissions at startup
    // We ask for Location and Camera as they are core to the app
    
    // Check if already granted to avoid unnecessary request overhead
    final locationStatus = await Permission.location.status;
    final cameraStatus = await Permission.camera.status;
    
    final permissionsToRequest = <Permission>[];
    
    if (!locationStatus.isGranted) {
      permissionsToRequest.add(Permission.location);
    }
    
    if (!cameraStatus.isGranted) {
      permissionsToRequest.add(Permission.camera);
    }
    
    // Notification permission might be needed for newer Android versions
    final notificationStatus = await Permission.notification.status;
    if (!notificationStatus.isGranted) {
      permissionsToRequest.add(Permission.notification);
    }

    if (permissionsToRequest.isNotEmpty) {
      await permissionsToRequest.request();
    }
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
