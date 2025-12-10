import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';

import '../../../models/attendance_record.dart';
import '../../../models/shift.dart';
import '../../../models/user.dart';
import '../../../config/app_config.dart';
import '../../../routes/app_router.dart';
import '../../../services/attendance_service.dart';
import '../../../services/connectivity_service.dart';
import '../../../services/location_service.dart';
import '../../../services/offline_queue_service.dart';
import '../../../services/shift_service.dart';
import '../../../state/attendance/attendance_providers.dart';
import '../../../state/auth/auth_providers.dart';
import '../patrol/patrol_form_screen.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> with WidgetsBindingObserver {
  late Future<List<Shift>> _shiftsFuture;
  late Future<List<AttendanceRecord>> _recentAttendanceFuture;

  late final String _filesBaseUrl;

  Shift? _cachedDefaultShift;
  String _mode = 'normal'; // 'normal' (WFO) atau 'dinas'

  final DateFormat _dateFormat = DateFormat('EEEE, d MMMM yyyy', 'id_ID');
  final DateFormat _timeFormat = DateFormat('HH:mm', 'id_ID');

  // Stream subscriptions for auto-refresh
  StreamSubscription<SyncEvent>? _syncSubscription;
  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;
  int _pendingCount = 0;
  bool _wasOffline = false;

  // Local attendance state for offline support
  // This tracks today's attendance even when offline
  bool? _localClockedIn; // null = use server data, true/false = use local
  DateTime? _localClockInTime;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    
    final apiBase = ref.read(appConfigProvider).apiBaseUrl;
    _filesBaseUrl = apiBase.replaceFirst(RegExp(r'/api/?$'), '');
    _shiftsFuture = ref.read(shiftServiceProvider).getAvailableShifts();
    _recentAttendanceFuture = _loadRecentAttendance();

    // Initial pending count
    _loadPendingCount();

    // Listen for sync events to refresh UI
    final offlineQueue = ref.read(offlineQueueServiceProvider);
    _syncSubscription = offlineQueue.onSyncStateChanged.listen(_onSyncEvent);

    // Listen for connectivity changes
    final connectivity = ref.read(connectivityServiceProvider);
    _connectivitySubscription = connectivity.onConnectivityChanged.listen(_onConnectivityChanged);

    // Initial sync
    Future.microtask(() => offlineQueue.sync());
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _syncSubscription?.cancel();
    _connectivitySubscription?.cancel();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      // App came back to foreground - sync and refresh
      debugPrint('App resumed v2 - syncing...');
      ref.read(offlineQueueServiceProvider).sync();
      _loadPendingCount();
      // Delay refresh to allow sync to complete
      Future.delayed(const Duration(milliseconds: 500), () {
        if (mounted) _refreshAll();
      });
    }
  }

  Future<void> _loadPendingCount() async {
    final offlineQueue = ref.read(offlineQueueServiceProvider);
    final count = await offlineQueue.getPendingCount();
    
    // Also check pending attendance type to sync button state
    final pendingType = await offlineQueue.getLastPendingAttendanceType();
    
    if (mounted) {
      setState(() {
        _pendingCount = count;
        // If there's a pending attendance item, update local state
        if (pendingType != null) {
          _localClockedIn = pendingType == 'clock_in';
          if (_localClockedIn == true) {
            _localClockInTime = DateTime.now();
          }
        }
      });
    }
  }

  void _onSyncEvent(SyncEvent event) {
    if (!mounted) return;
    
    setState(() {
      _pendingCount = event.pendingCount;
    });

    // If items were synced, refresh attendance history and button state
    if (event.syncedCount > 0) {
      // Clear local state so we use fresh server data
      _localClockedIn = null;
      _localClockInTime = null;
      _refreshAll();
    }
    
    // If all items synced (queue empty), ensure UI is fully refreshed
    if (event.isComplete && event.pendingCount == 0) {
      _localClockedIn = null;
      _localClockInTime = null;
      _refreshAll();
    }
  }

  void _onConnectivityChanged(List<ConnectivityResult> results) {
    final isOffline = results.contains(ConnectivityResult.none);
    
    if (isOffline) {
      _wasOffline = true;
      return;
    }
    
    if (_wasOffline) {
      // Just came back online - trigger sync immediately and refresh
      debugPrint('Back online - syncing and refreshing...');
      final queue = ref.read(offlineQueueServiceProvider);
      
      // Sync immediately
      queue.sync().then((_) {
        // Refresh after sync completes
        if (mounted) {
          _loadPendingCount();
          _refreshAll();
        }
      });
      
      // Also refresh after short delay as fallback
      Future.delayed(const Duration(seconds: 1), () {
        if (mounted) {
          _loadPendingCount();
        }
      });
      
      _wasOffline = false;
    }
  }

  void _refreshAll() {
    setState(() {
      _shiftsFuture = ref.read(shiftServiceProvider).getAvailableShifts();
      _recentAttendanceFuture = _loadRecentAttendance();
    });
    _loadPendingCount();
  }

  Future<List<AttendanceRecord>> _loadRecentAttendance() async {
    final service = ref.read(attendanceServiceProvider);
    final now = DateTime.now();
    final from = now.subtract(const Duration(days: 30));
    return service.getHistory(from: from, to: now);
  }

  void _refreshRecentAttendance() {
    setState(() {
      _recentAttendanceFuture = _loadRecentAttendance();
    });
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;

    return Scaffold(
      backgroundColor: const Color(0xFFF8F9FA), // Light grey background
      drawer: _buildDrawer(context),
      body: Stack(
        children: [
          // Header Background
          Container(
            height: 220,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0xFF0C6CF2), Color(0xFF004AAD)],
              ),
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(32),
                bottomRight: Radius.circular(32),
              ),
            ),
          ),
          SafeArea(
            child: Column(
              children: [
                // Top Bar
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  child: Row(
                    children: [
                      Builder(
                        builder: (ctx) => IconButton(
                          icon: const Icon(Icons.menu, color: Colors.white),
                          onPressed: () => Scaffold.of(ctx).openDrawer(),
                        ),
                      ),
                      Expanded(
                        child: Text(
                          _dateFormat.format(DateTime.now()),
                          style: const TextStyle(
                            color: Colors.white70,
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.notifications_outlined,
                            color: Colors.white),
                        onPressed: () {
                          // TODO: Implement notifications
                        },
                      ),
                    ],
                  ),
                ),

                Expanded(
                  child: SingleChildScrollView(
                    physics: const BouncingScrollPhysics(),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Greeting & Profile
                          _buildGreetingSection(context, user),
                          const SizedBox(height: 24),

                          // Main Status Card (Dynamic)
                          _buildDynamicStatusCard(context),
                          
                          // Offline Indicator (if needed) - uses reactive state
                          if (_pendingCount > 0)
                            Container(
                              margin: const EdgeInsets.only(top: 16, bottom: 8),
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: Colors.orange.shade50,
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: Colors.orange.shade200),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.cloud_off,
                                      size: 16, color: Colors.orange),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      '$_pendingCount data belum terkirim (Offline)',
                                      style: TextStyle(
                                          color: Colors.orange.shade800,
                                          fontSize: 12),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          
                          const SizedBox(height: 24),

                          // Menu Grid
                          Text(
                            'Menu Utama',
                            style: Theme.of(context)
                                .textTheme
                                .titleMedium
                                ?.copyWith(fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 12),
                          _buildMenuGrid(context),

                          const SizedBox(height: 24),

                          // Recent History
                          _buildRecentHistorySection(context),
                          const SizedBox(height: 32),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGreetingSection(BuildContext context, User? user) {
    return Row(
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Halo, Petugas',
                style: TextStyle(color: Colors.white70, fontSize: 14),
              ),
              const SizedBox(height: 4),
              Text(
                user?.name ?? 'Memuat...',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  user?.activeProjectName ?? 'Lokasi Belum Diset',
                  style: const TextStyle(color: Colors.white, fontSize: 11),
                ),
              ),
            ],
          ),
        ),
        Container(
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white, width: 2),
          ),
          child: _buildProfileImage(context, user, size: 56),
        ),
      ],
    );
  }

  Widget _buildDynamicStatusCard(BuildContext context) {
    return FutureBuilder<List<AttendanceRecord>>(
      future: _recentAttendanceFuture,
      builder: (context, snapshot) {
        bool isClockedIn = false;
        DateTime? clockInTime;

        // First, check local state (for offline support)
        // Local state takes precedence if set for today
        if (_localClockedIn != null) {
          isClockedIn = _localClockedIn!;
          clockInTime = _localClockInTime;
        } else if (snapshot.hasData && snapshot.data != null) {
          // Use server data
          final records = List<AttendanceRecord>.from(snapshot.data!);
          // Sort descending
          records.sort((a, b) {
            final tA = a.occurredAt ?? DateTime(2000);
            final tB = b.occurredAt ?? DateTime(2000);
            return tB.compareTo(tA);
          });

          // Check today's attendance logic
          final today = DateTime.now();
          
          // Get records for today
          final todayRecords = records.where((r) {
            final t = r.occurredAt;
            if (t == null) return false;
            return t.year == today.year &&
                   t.month == today.month &&
                   t.day == today.day;
          }).toList();

          if (todayRecords.isNotEmpty) {
            final lastRecord = todayRecords.first;
            if (lastRecord.type == 'clock_in') {
              isClockedIn = true;
              clockInTime = lastRecord.occurredAt;
            }
          }
        }

        // Dynamic UI Variables
        final cardColor = isClockedIn
            ? const Color(0xFFE8F5E9) // Light Green
            : const Color(0xFFE3F2FD); // Light Blue
        final accentColor = isClockedIn ? Colors.green : const Color(0xFF0C6CF2);
        final title = isClockedIn ? 'SEDANG BERTUGAS' : 'SIAP BERTUGAS';
        final subtitle = isClockedIn
            ? 'Anda sudah melakukan absen masuk.'
            : 'Silakan absen masuk untuk memulai shift.';
        final icon = isClockedIn ? Icons.shield : Icons.login;
        final buttonLabel = isClockedIn ? 'ABSEN KELUAR' : 'ABSEN MASUK';
        final buttonColor = isClockedIn ? Colors.red : const Color(0xFF0C6CF2);

        return Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 20,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: Column(
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: cardColor,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(icon, color: accentColor, size: 24),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: TextStyle(
                            color: accentColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                            letterSpacing: 1,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          subtitle,
                          style: TextStyle(
                            color: Colors.grey.shade600,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              if (isClockedIn && clockInTime != null)
                Container(
                  margin: const EdgeInsets.only(bottom: 16),
                  padding:
                      const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
                  decoration: BoxDecoration(
                    color: Colors.green.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.access_time,
                          size: 16, color: Colors.green),
                      const SizedBox(width: 8),
                      Text(
                        // Removed TimezoneHelper.toJakarta call
                        'Masuk pukul ${_timeFormat.format(clockInTime!)}',
                        style: TextStyle(
                          color: Colors.green.shade800,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: () => _startAttendanceFlow(!isClockedIn),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: buttonColor,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: Text(
                    buttonLabel,
                    style: const TextStyle(
                        fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildMenuGrid(BuildContext context) {
    final menus = [
      _MenuData(
        title: 'Patroli',
        icon: Icons.local_police_outlined,
        color: Colors.blue,
        onTap: _showPatrolOptions,
      ),
      _MenuData(
        title: 'Izin / Cuti',
        icon: Icons.event_note_outlined,
        color: Colors.orange,
        onTap: () => Navigator.of(context).pushNamed(AppRoutes.leaveList),
      ),
      _MenuData(
        title: 'Riwayat',
        icon: Icons.history_edu_outlined,
        color: Colors.purple,
        onTap: () => Navigator.of(context).pushNamed(AppRoutes.attendanceHistory),
      ),
      _MenuData(
        title: 'Profil',
        icon: Icons.person_outline,
        color: Colors.teal,
        onTap: () => Navigator.of(context).pushNamed(AppRoutes.profile),
      ),
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 16,
        mainAxisSpacing: 16,
        childAspectRatio: 1.5,
      ),
      itemCount: menus.length,
      itemBuilder: (ctx, index) {
        final menu = menus[index];
        return InkWell(
          onTap: menu.onTap,
          borderRadius: BorderRadius.circular(16),
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.grey.shade200),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.02),
                  blurRadius: 8,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: menu.color.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(menu.icon, color: menu.color, size: 28),
                ),
                const SizedBox(height: 8),
                Text(
                  menu.title,
                  style: TextStyle(
                    color: Colors.grey.shade800,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildProfileImage(BuildContext context, User? user,
      {double size = 180}) {
    if (user == null) {
      return _buildPlaceholderPhoto(context, size: size);
    }

    final url = _resolveProfilePhotoUrl(user);
    if (url == null) {
      return _buildPlaceholderPhoto(context, size: size);
    }

    return ClipOval(
      child: Image.network(
        url,
        fit: BoxFit.cover,
        width: size,
        height: size,
        errorBuilder: (ctx, error, stack) =>
            _buildPlaceholderPhoto(context, size: size),
      ),
    );
  }

  Widget _buildPlaceholderPhoto(BuildContext context, {double size = 180}) {
    return Container(
      width: size,
      height: size,
      decoration: const BoxDecoration(
        shape: BoxShape.circle,
        color: Colors.white24,
      ),
      alignment: Alignment.center,
      child: Icon(
        Icons.person,
        color: Colors.white,
        size: size * 0.5,
      ),
    );
  }

  String? _resolveProfilePhotoUrl(User user) {
    if (user.profilePhotoPath != null && user.profilePhotoPath!.isNotEmpty) {
      return '$_filesBaseUrl/storage/${user.profilePhotoPath}';
    }
    if (user.profilePhotoUrl != null && user.profilePhotoUrl!.isNotEmpty) {
      return user.profilePhotoUrl;
    }
    return null;
  }

  Widget _buildRecentHistorySection(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'RIWAYAT ABSEN (1 Bulan)',
          style: Theme.of(context)
              .textTheme
              .titleMedium
              ?.copyWith(letterSpacing: 1.5),
        ),
        const SizedBox(height: 12),
        FutureBuilder<List<AttendanceRecord>>(
          future: _recentAttendanceFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(
                child: Padding(
                  padding: EdgeInsets.symmetric(vertical: 16),
                  child: CircularProgressIndicator(),
                ),
              );
            }

            if (snapshot.hasError) {
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 8),
                child: Text(
                  'Gagal memuat riwayat absen.',
                  style: const TextStyle(color: Colors.red),
                ),
              );
            }

            final records = snapshot.data ?? [];
            if (records.isEmpty) {
              return const Padding(
                padding: EdgeInsets.symmetric(vertical: 8),
                child: Text('Belum ada riwayat.'),
              );
            }

            // Sort descending
            records.sort((a, b) => (b.occurredAt ?? DateTime(2000))
                .compareTo(a.occurredAt ?? DateTime(2000)));

            final masuk =
                records.where((r) => r.type == 'clock_in').toList();
            final keluar =
                records.where((r) => r.type == 'clock_out').toList();

            return Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: _buildHistoryColumn(
                    context,
                    title: 'MASUK',
                    items: masuk,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildHistoryColumn(
                    context,
                    title: 'KELUAR',
                    items: keluar,
                  ),
                ),
              ],
            );
          },
        ),
        const SizedBox(height: 8),
        Align(
          alignment: Alignment.centerRight,
          child: TextButton(
            onPressed: () {
              Navigator.of(context)
                  .pushNamed(AppRoutes.attendanceHistory);
            },
            child: const Text('Lihat semua'),
          ),
        ),
      ],
    );
  }

  Widget _buildHistoryColumn(
    BuildContext context, {
    required String title,
    required List<AttendanceRecord> items,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: Theme.of(context)
              .textTheme
              .labelLarge
              ?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 8),
        for (final record in items.take(8))
          Container(
            margin: const EdgeInsets.only(bottom: 12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 48,
                  height: 48,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey.shade400),
                  ),
                  child: _buildHistoryPhoto(record),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _buildHistoryText(context, record),
                ),
              ],
            ),
          ),
      ],
    );
  }

  Widget _buildHistoryText(BuildContext context, AttendanceRecord record) {
    // Removed TimezoneHelper
    final occurredAt = record.occurredAt;
        
    final dateStr =
        occurredAt != null ? _dateFormat.format(occurredAt) : '-';
    final timeStr =
        occurredAt != null ? _timeFormat.format(occurredAt) : '-';

    final modeLabel = record.mode == 'dinas' ? 'DINAS' : 'WFO';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          dateStr,
          style: const TextStyle(fontSize: 12, color: Colors.grey),
        ),
        Text(
          timeStr,
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        Text(
          modeLabel,
          style: TextStyle(
            fontSize: 12,
            color: record.mode == 'dinas' ? Colors.blue : Colors.green,
            fontWeight: FontWeight.w500,
          ),
        ),
        if (record.note != null && record.note!.isNotEmpty)
          Text(
            record.note!,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontSize: 11, fontStyle: FontStyle.italic),
          ),
      ],
    );
  }

  Widget _buildHistoryPhoto(AttendanceRecord record) {
    final path = record.selfiePhotoPath;
    if (path == null || path.isEmpty) {
      return const Icon(Icons.person, size: 24, color: Colors.grey);
    }

    final url = path.startsWith('http')
        ? path
        : '$_filesBaseUrl/storage/$path';

    return ClipRRect(
      borderRadius: BorderRadius.circular(2),
      child: Image.network(
        url,
        fit: BoxFit.cover,
        width: 48,
        height: 48,
        errorBuilder: (ctx, error, stack) {
          return const Icon(Icons.broken_image, size: 20);
        },
      ),
    );
  }

  String titleCase(String value) {
    if (value.isEmpty) return value;
    return value[0].toUpperCase() + value.substring(1).toLowerCase();
  }

  Widget _buildDrawer(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;

    return Drawer(
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ListTile(
              leading: _buildDrawerAvatar(user),
              title: Text(user?.name ?? 'JSMUGuard'),
              subtitle:
                  user != null ? Text(user.username) : const SizedBox(),
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.person_outline),
              title: const Text('Profil'),
              onTap: () {
                Navigator.of(context).pop();
                Navigator.of(context).pushNamed(AppRoutes.profile);
              },
            ),
            ListTile(
              leading: const Icon(Icons.calendar_today),
              title: const Text('Izin / Cuti'),
              onTap: () {
                Navigator.of(context).pop();
                Navigator.of(context).pushNamed(AppRoutes.leaveList);
              },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.logout, color: Colors.red),
              title: const Text('Keluar', style: TextStyle(color: Colors.red)),
              onTap: () async {
                Navigator.of(context).pop();
                await ref.read(authNotifierProvider.notifier).logout();
                if (!context.mounted) return;
                Navigator.of(context).pushNamedAndRemoveUntil(
                  AppRoutes.login,
                  (route) => false,
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDrawerAvatar(User? user) {
    if (user == null) {
      return const CircleAvatar(child: Icon(Icons.person));
    }
    final url = _resolveProfilePhotoUrl(user);
    return CircleAvatar(
      backgroundImage: url != null ? NetworkImage(url) : null,
      child: url == null ? const Icon(Icons.person) : null,
    );
  }

  Future<String?> _captureSelfie() async {
    try {
      final picker = ImagePicker();
      final image = await picker.pickImage(
        source: ImageSource.camera,
        preferredCameraDevice: CameraDevice.front,
        imageQuality: 70,
        maxWidth: 800,
      );
      return image?.path;
    } catch (e) {
      debugPrint('Error picking image: $e');
      return null;
    }
  }

  Future<void> _startAttendanceFlow(bool isCheckIn) async {
    // 1. Open Camera immediately
    final selfiePath = await _captureSelfie();
    if (selfiePath == null) return; // User cancelled

    // 2. Show loading while fetching location & shifts
    if (!mounted) return;
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const Center(child: CircularProgressIndicator()),
    );

    try {
      // 3. Get Location AND Shifts in PARALLEL for speed
      final locationService = ref.read(locationServiceProvider);
      final results = await Future.wait([
        locationService.getCurrentPosition(),
        _shiftsFuture,
      ]);
      
      final position = results[0] as Position;
      final shifts = results[1] as List<Shift>;

      // 5. Check Radius
      final user = ref.read(authNotifierProvider).user;
      bool isWithinRange = true;
      double distance = 0;

      if (user?.projectLat != null && user?.projectLng != null) {
        distance = Geolocator.distanceBetween(
          position.latitude,
          position.longitude,
          user!.projectLat!,
          user.projectLng!,
        );
        final radius = user.projectRadius ?? 100; // Default 100m
        isWithinRange = distance <= radius;
      }

      if (!mounted) return;
      Navigator.of(context).pop(); // Close loading

      if (shifts.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Tidak ada shift tersedia.')),
        );
        return;
      }

      // 6. Show Form
      final formResult = await _showAttendanceBottomSheet(
        isCheckIn: isCheckIn,
        shifts: shifts,
        isWithinRange: isWithinRange,
        currentDistance: distance,
      );

      if (formResult == null) return;

      // 7. Submit
      if (!mounted) return;
      // Show loading again
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => const Center(child: CircularProgressIndicator()),
      );

      await _submitAttendance(
        isCheckIn: isCheckIn,
        shift: formResult.shift,
        mode: formResult.mode,
        note: formResult.note,
        selfiePath: selfiePath,
        position: position,
      );

      if (!mounted) return;
      Navigator.of(context).pop(); // Close loading

    } catch (e) {
      if (mounted) {
        Navigator.of(context).pop(); // Close loading if open
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Terjadi kesalahan: $e')),
        );
      }
    }
  }

  Future<_AttendanceFormResult?> _showAttendanceBottomSheet({
    required bool isCheckIn,
    required List<Shift> shifts,
    required bool isWithinRange,
    required double currentDistance,
  }) async {
    final defaultShift = _cachedDefaultShift ??
        shifts.firstWhere(
          (s) => s.isDefault,
          orElse: () => shifts.first,
        );

    // If outside range, force 'dinas'
    String selectedMode = isWithinRange ? _mode : 'dinas';
    Shift selectedShift = defaultShift;
    final noteController = TextEditingController();

    return await showModalBottomSheet<_AttendanceFormResult>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: StatefulBuilder(
            builder: (ctx, setSheetState) {
              return Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      margin: const EdgeInsets.only(bottom: 20),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  Text(
                    isCheckIn
                        ? 'Konfirmasi Absen Masuk'
                        : 'Konfirmasi Absen Keluar',
                    style: Theme.of(ctx).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 20),
                  
                  // Location Status
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: isWithinRange ? Colors.green.shade50 : Colors.orange.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: isWithinRange ? Colors.green.shade200 : Colors.orange.shade200,
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          isWithinRange ? Icons.location_on : Icons.location_off,
                          color: isWithinRange ? Colors.green : Colors.orange,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isWithinRange ? 'Dalam Area Kantor' : 'Luar Area Kantor',
                                style: const TextStyle(fontWeight: FontWeight.bold),
                              ),
                              if (!isWithinRange)
                                Text(
                                  'Jarak: ${currentDistance.toStringAsFixed(0)}m. Mode otomatis diset ke DINAS.',
                                  style: const TextStyle(fontSize: 12),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Shift Selection
                  DropdownButtonFormField<Shift>(
                    value: selectedShift,
                    decoration: const InputDecoration(
                      labelText: 'Pilih Shift',
                      border: OutlineInputBorder(),
                      contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 16),
                    ),
                    items: shifts
                        .map(
                          (s) => DropdownMenuItem<Shift>(
                            value: s,
                            child: Text(
                              '${s.name} (${s.startTime} - ${s.endTime})',
                            ),
                          ),
                        )
                        .toList(),
                    onChanged: (value) {
                      if (value == null) return;
                      setSheetState(() {
                        selectedShift = value;
                        _cachedDefaultShift = value;
                      });
                    },
                  ),
                  const SizedBox(height: 20),

                  // Mode Selection
                  Text('Mode Kehadiran', style: Theme.of(ctx).textTheme.titleSmall),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: ChoiceChip(
                          label: const Center(child: Text('WFO')),
                          selected: selectedMode == 'normal',
                          onSelected: isWithinRange
                              ? (selected) {
                                  if (!selected) return;
                                  setSheetState(() {
                                    selectedMode = 'normal';
                                    _mode = 'normal';
                                  });
                                }
                              : null, // Disabled if outside range
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ChoiceChip(
                          label: const Center(child: Text('DINAS')),
                          selected: selectedMode == 'dinas',
                          onSelected: (selected) {
                            if (!selected) return;
                            setSheetState(() {
                              selectedMode = 'dinas';
                              _mode = 'dinas';
                            });
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Note Field
                  TextField(
                    controller: noteController,
                    decoration: InputDecoration(
                      labelText: selectedMode == 'dinas' ? 'Keterangan (Wajib)' : 'Keterangan (Opsional)',
                      border: const OutlineInputBorder(),
                      alignLabelWithHint: true,
                    ),
                    maxLines: 3,
                  ),
                  
                  const SizedBox(height: 24),
                  
                  // Buttons
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => Navigator.of(ctx).pop(),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          child: const Text('BATAL'),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: ElevatedButton(
                          onPressed: () {
                            final note = noteController.text.trim();
                            
                            // Validation for DINAS
                            if (selectedMode == 'dinas' && note.isEmpty) {
                              ScaffoldMessenger.of(ctx).showSnackBar(
                                const SnackBar(content: Text('Harap isi keterangan untuk mode DINAS.')),
                              );
                              return;
                            }

                            Navigator.of(ctx).pop<_AttendanceFormResult>(
                              _AttendanceFormResult(
                                shift: selectedShift,
                                mode: selectedMode,
                                note: note.isEmpty ? null : note,
                              ),
                            );
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF0C6CF2),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          child: Text(isCheckIn ? 'KONFIRMASI' : 'KELUAR'),
                        ),
                      ),
                    ],
                  ),
                ],
              );
            },
          ),
        );
      },
    );
  }

  Future<void> _submitAttendance({
    required bool isCheckIn,
    required Shift shift,
    required String mode,
    required String? note,
    required String selfiePath,
    required Position position,
  }) async {
    final notifier = ref.read(attendanceNotifierProvider.notifier);

    try {
      if (isCheckIn) {
        await notifier.checkIn(
          shiftId: shift.id,
          latitude: position.latitude,
          longitude: position.longitude,
          mode: mode,
          note: note,
          selfiePath: selfiePath,
        );
        
        // Update local state immediately (for offline support)
        setState(() {
          _localClockedIn = true;
          _localClockInTime = DateTime.now();
        });
      } else {
        await notifier.checkOut(
          shiftId: shift.id,
          latitude: position.latitude,
          longitude: position.longitude,
          note: note,
          selfiePath: selfiePath,
        );
        
        // Update local state immediately (for offline support)
        setState(() {
          _localClockedIn = false;
          _localClockInTime = null;
        });
      }

      if (!mounted) return;

      final state = ref.read(attendanceNotifierProvider);
      
      if (state.errorMessage != null) {
         ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(state.errorMessage!)),
        );
      } else {
        final message = state.lastMessage ??
          (isCheckIn ? 'Absen masuk berhasil.' : 'Absen keluar berhasil.');
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(message)),
        );
        _refreshRecentAttendance();
      }

    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  Future<void> _showPatrolOptions() async {
    final option = await showModalBottomSheet<_PatrolOption>(
      context: context,
      builder: (ctx) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                leading: const Icon(Icons.qr_code_scanner),
                title: const Text('Patroli Checkpoint'),
                onTap: () => Navigator.of(ctx).pop(_PatrolOption.patrol),
              ),
              ListTile(
                leading: const Icon(Icons.report_problem, color: Colors.orange),
                title: const Text('Lapor Insiden'),
                onTap: () => Navigator.of(ctx).pop(_PatrolOption.incident),
              ),
              ListTile(
                leading: const Icon(Icons.history, color: Colors.blue),
                title: const Text('Riwayat Patroli'),
                onTap: () => Navigator.of(ctx).pop(_PatrolOption.history),
              ),
              ListTile(
                leading: const Icon(Icons.sos, color: Colors.red),
                title: const Text('SOS / Darurat'),
                onTap: () => Navigator.of(ctx).pop(_PatrolOption.sos),
              ),
            ],
          ),
        );
      },
    );

    if (!mounted || option == null) return;

    switch (option) {
      case _PatrolOption.patrol:
        Navigator.of(context).pushNamed(AppRoutes.patrolScan);
        break;
      case _PatrolOption.incident:
        Navigator.of(context).pushNamed(
          AppRoutes.patrolForm,
          arguments: PatrolFormArgs(
            mode: PatrolFormMode.incident,
            checkpointCode: '',
          ),
        );
        break;
      case _PatrolOption.history:
        Navigator.of(context).pushNamed(AppRoutes.patrolHistory);
        break;
      case _PatrolOption.sos:
        Navigator.of(context).pushNamed(
          AppRoutes.patrolForm,
          arguments: PatrolFormArgs(
            mode: PatrolFormMode.sos,
            checkpointCode: '',
          ),
        );
        break;
    }
  }
}

class _AttendanceFormResult {
  const _AttendanceFormResult({
    required this.shift,
    required this.mode,
    required this.note,
  });

  final Shift shift;
  final String mode;
  final String? note;
}

class _MenuData {
  final String title;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  _MenuData({
    required this.title,
    required this.icon,
    required this.color,
    required this.onTap,
  });
}

enum _PatrolOption { patrol, incident, history, sos }


