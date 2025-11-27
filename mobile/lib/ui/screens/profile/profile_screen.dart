import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../config/app_config.dart';
import '../../../models/user.dart';
import '../../../services/offline_queue_service.dart';
import '../../../state/auth/auth_providers.dart';
import '../../../routes/app_router.dart';

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
              Center(
                child: Column(
                  children: [
                    _buildProfileAvatar(user, filesBaseUrl),
                    const SizedBox(height: 16),
                    Text(
                      user.name,
                      style: Theme.of(context)
                          .textTheme
                          .titleMedium
                          ?.copyWith(fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              Text('NIP: ${user.nip ?? '-'}'),
              const SizedBox(height: 8),
              Text('Nama: ${user.name}'),
              const SizedBox(height: 8),
              Text('Lokasi Tugas: ${user.activeProjectName ?? '-'}'),
              const SizedBox(height: 16),
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

  Widget _buildProfileAvatar(User user, String filesBaseUrl) {
    String? url;
    if (user.profilePhotoPath != null && user.profilePhotoPath!.isNotEmpty) {
      url = '$filesBaseUrl/storage/${user.profilePhotoPath}';
    } else if (user.profilePhotoUrl != null && user.profilePhotoUrl!.isNotEmpty) {
      url = user.profilePhotoUrl;
    }

    return CircleAvatar(
      radius: 48,
      backgroundColor: Colors.grey.shade300,
      backgroundImage: url != null ? NetworkImage(url) : null,
      onBackgroundImageError: url != null
          ? (exception, stackTrace) {
              // Handle error, maybe fallback?
            }
          : null,
      child: url == null ? const Icon(Icons.person, size: 40) : null,
    );
  }
}
