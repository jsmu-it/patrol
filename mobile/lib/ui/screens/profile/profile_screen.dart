import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../config/app_config.dart';
import '../../../models/user.dart';
import '../../../services/offline_queue_service.dart';
import '../../../state/auth/auth_providers.dart';
import '../../../routes/app_router.dart';
import 'cv_screen.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;
    final offlineQueue = ref.watch(offlineQueueServiceProvider);

    final apiBase = ref.watch(appConfigProvider).apiBaseUrl;
    final filesBaseUrl = apiBase.replaceFirst(RegExp(r'/api/?$'), '');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (user == null) ...[ 
              const Center(child: CircularProgressIndicator()),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () {
                  ref
                      .read(authNotifierProvider.notifier)
                      .refreshCurrentUser();
                },
                child: const Text('Muat Profil'),
              ),
            ] else ...[
              // Clickable profile photo - centered
              Center(
                child: GestureDetector(
                  onTap: () {
                    _showFullPhoto(context, user, filesBaseUrl);
                  },
                  child: _buildProfileAvatar(user, filesBaseUrl),
                ),
              ),
              const SizedBox(height: 24),
              
              // Profile information in order
              _buildInfoRow(Icons.badge, 'NIP', user.nip ?? '-'),
              const SizedBox(height: 12),
              _buildInfoRow(Icons.person, 'Nama', user.name),
              const SizedBox(height: 12),
              _buildInfoRow(Icons.work, 'Jabatan', user.position ?? '-'),
              const SizedBox(height: 12),
              _buildInfoRow(Icons.business, 'Divisi', user.division ?? '-'),
              const SizedBox(height: 12),
              _buildInfoRow(Icons.location_on, 'Lokasi Tugas', user.activeProjectName ?? '-'),
              const SizedBox(height: 20),
              
              // Edit Profile button
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () {
                    Navigator.of(context).pushNamed('/edit-profile').then((_) {
                      ref.read(authNotifierProvider.notifier).refreshCurrentUser();
                    });
                  },
                  icon: const Icon(Icons.edit),
                  label: const Text('Edit Profil'),
                ),
              ),
              const SizedBox(height: 12),
              
              // View CV button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (context) => const CVScreen(),
                      ),
                    );
                  },
                  icon: const Icon(Icons.description),
                  label: const Text('Lihat CV'),
                ),
              ),
              const SizedBox(height: 16),
              
              // Offline queue status
              FutureBuilder<int>(
                future: offlineQueue.getPendingCount(),
                builder: (context, snapshot) {
                  final count = snapshot.data ?? 0;
                  return Row(
                    children: [
                      Icon(
                        Icons.cloud_off,
                        size: 18,
                        color: count > 0 ? Colors.orange : Colors.grey,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'Antrian offline: $count',
                        style: TextStyle(
                          fontSize: 12,
                          color: count > 0 ? Colors.orange : Colors.grey,
                        ),
                      ),
                      const Spacer(),
                      TextButton.icon(
                        onPressed: () {
                          final service =
                              ref.read(offlineQueueServiceProvider);
                          service.sync();
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text(
                                'Sync offline dimulai di latar belakang.',
                              ),
                            ),
                          );
                        },
                        icon: const Icon(Icons.sync, size: 16),
                        label: const Text('Sync Sekarang'),
                      ),
                    ],
                  );
                },
              ),
            ],
            const Spacer(),
            
            // Logout button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.red,
                ),
                onPressed: () async {
                  await ref.read(authNotifierProvider.notifier).logout();
                  if (!context.mounted) return;
                  Navigator.of(context).pushNamedAndRemoveUntil(
                    AppRoutes.login,
                    (route) => false,
                  );
                },
                child: const Text('Logout'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 20, color: Colors.grey[600]),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildProfileAvatar(User user, String filesBaseUrl) {
    String? url;
    if (user.profilePhotoPath != null && user.profilePhotoPath!.isNotEmpty) {
      // Ensure no double slashes when combining baseUrl and path
      final baseUrl = filesBaseUrl.endsWith('/')
          ? filesBaseUrl.substring(0, filesBaseUrl.length - 1)
          : filesBaseUrl;
      final path = user.profilePhotoPath!.startsWith('/')
          ? user.profilePhotoPath!.substring(1)
          : user.profilePhotoPath!;
      
      // Check if path already contains 'storage/' to avoid duplication
      if (path.startsWith('storage/')) {
        url = '$baseUrl/$path';
      } else {
        url = '$baseUrl/storage/$path';
      }
    } else if (user.profilePhotoUrl != null && user.profilePhotoUrl!.isNotEmpty) {
      url = user.profilePhotoUrl;
    }

    return CircleAvatar(
      radius: 60,
      backgroundColor: Colors.grey.shade300,
      backgroundImage: url != null ? NetworkImage(url) : null,
      onBackgroundImageError: url != null
          ? (exception, stackTrace) {
              debugPrint('Failed to load image: $url');
            }
          : null,
      child: url == null ? const Icon(Icons.person, size: 50) : null,
    );
  }

  void _showFullPhoto(BuildContext context, User user, String filesBaseUrl) {
    String? url;
    if (user.profilePhotoPath != null && user.profilePhotoPath!.isNotEmpty) {
      final baseUrl = filesBaseUrl.endsWith('/')
          ? filesBaseUrl.substring(0, filesBaseUrl.length - 1)
          : filesBaseUrl;
      final path = user.profilePhotoPath!.startsWith('/')
          ? user.profilePhotoPath!.substring(1)
          : user.profilePhotoPath!;
      
      if (path.startsWith('storage/')) {
        url = '$baseUrl/$path';
      } else {
        url = '$baseUrl/storage/$path';
      }
    } else if (user.profilePhotoUrl != null && user.profilePhotoUrl!.isNotEmpty) {
      url = user.profilePhotoUrl;
    }

    if (url == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Foto profil tidak tersedia')),
      );
      return;
    }

    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.black,
        child: Stack(
          children: [
            InteractiveViewer(
              child: Center(
                child: Image.network(
                  url!,
                  fit: BoxFit.contain,
                  errorBuilder: (context, error, stackTrace) {
                    return const Center(
                      child: Icon(
                        Icons.error,
                        color: Colors.white,
                        size: 50,
                      ),
                    );
                  },
                  loadingBuilder: (context, child, loadingProgress) {
                    if (loadingProgress == null) return child;
                    return Center(
                      child: CircularProgressIndicator(
                        value: loadingProgress.expectedTotalBytes != null
                            ? loadingProgress.cumulativeBytesLoaded /
                                loadingProgress.expectedTotalBytes!
                            : null,
                      ),
                    );
                  },
                ),
              ),
            ),
            Positioned(
              top: 10,
              right: 10,
              child: IconButton(
                icon: const Icon(Icons.close, color: Colors.white, size: 30),
                onPressed: () => Navigator.of(context).pop(),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
