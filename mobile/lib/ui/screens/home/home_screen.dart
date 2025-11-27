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

class _HomeScreenState extends ConsumerState<HomeScreen> {
  late Future<List<Shift>> _shiftsFuture;
  late Future<List<AttendanceRecord>> _recentAttendanceFuture;

  late final String _filesBaseUrl;

  Shift? _cachedDefaultShift;
  String _mode = 'normal'; // 'normal' (WFO) atau 'dinas'

  final DateFormat _dateFormat = DateFormat('dd MMM yyyy');
  final DateFormat _timeFormat = DateFormat('HH:mm');

  @override
  void initState() {
    super.initState();
    final apiBase = ref.read(appConfigProvider).apiBaseUrl;
    _filesBaseUrl = apiBase.replaceFirst(RegExp(r'/api/?$'), '');
    _shiftsFuture = ref.read(shiftServiceProvider).getAvailableShifts();
    _recentAttendanceFuture = _loadRecentAttendance();

    Future.microtask(
      () => ref.read(offlineQueueServiceProvider).sync(),
    );
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
    final offlineQueue = ref.watch(offlineQueueServiceProvider);
    final user = authState.user;

    return Scaffold(
      drawer: _buildDrawer(context),
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildHeader(context, user),
              const SizedBox(height: 24),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Selamat datang, ${user?.name ?? '-'}',
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    const SizedBox(height: 24),
                    _buildAttendanceButtons(context),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _showPatrolOptions,
                        child: const Text('PATROL'),
                      ),
                    ),
                    const SizedBox(height: 16),
                    FutureBuilder<int>(
                      future: offlineQueue.getPendingCount(),
                      builder: (ctx, snapshot) {
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
                                color:
                                    count > 0 ? Colors.orange : Colors.grey,
                              ),
                            ),
                          ],
                        );
                      },
                    ),
                    const SizedBox(height: 24),
                    _buildRecentHistorySection(context),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAttendanceButtons(BuildContext context) {
    return FutureBuilder<List<AttendanceRecord>>(
      future: _recentAttendanceFuture,
      builder: (context, snapshot) {
        bool isClockedIn = false;

        if (snapshot.hasData && snapshot.data != null) {
          // Sort by time descending to find the very last action
          final records = List<AttendanceRecord>.from(snapshot.data!);
          records.sort((a, b) {
            final tA = a.occurredAt ?? DateTime(2000);
            final tB = b.occurredAt ?? DateTime(2000);
            return tB.compareTo(tA);
          });

          // Check records from today only
          final today = DateTime.now();
          final todayRecords = records.where((r) {
            final t = r.occurredAt;
            return t != null &&
                t.year == today.year &&
                t.month == today.month &&
                t.day == today.day;
          }).toList();

          if (todayRecords.isNotEmpty) {
            final lastRecord = todayRecords.first;
            if (lastRecord.type == 'clock_in') {
              isClockedIn = true;
            }
          }
        }

        return Row(
          children: [
            Expanded(
              child: ElevatedButton(
                onPressed: isClockedIn
                    ? null
                    : () => _startAttendanceFlow(true),
                style: isClockedIn
                    ? ElevatedButton.styleFrom(
                        backgroundColor: Colors.grey.shade300,
                        foregroundColor: Colors.grey,
                      )
                    : null,
                child: const Text('MASUK'),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: OutlinedButton(
                onPressed: isClockedIn
                    ? () => _startAttendanceFlow(false)
                    : null,
                style: !isClockedIn
                    ? OutlinedButton.styleFrom(
                        foregroundColor: Colors.grey,
                        side: const BorderSide(color: Colors.grey),
                      )
                    : null,
                child: const Text('KELUAR'),
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildHeader(BuildContext context, User? user) {
    const headerHeight = 260.0;
    const blueHeight = 200.0;

    return SizedBox(
      height: headerHeight,
      child: Stack(
        alignment: Alignment.topCenter,
        children: [
          Container(
            height: blueHeight,
            width: double.infinity,
            decoration: const BoxDecoration(
              color: Color(0xFF0C6CF2),
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(40),
                bottomRight: Radius.circular(40),
              ),
            ),
            child: Padding(
              padding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                children: [
                  Builder(
                    builder: (ctx) => IconButton(
                      icon: const Icon(Icons.menu, color: Colors.white),
                      onPressed: () => Scaffold.of(ctx).openDrawer(),
                    ),
                  ),
                  const Spacer(),
                  Image.asset(
                    'assets/images/logo.png',
                    height: 32,
                  ),
                ],
              ),
            ),
          ),
          Positioned(
            bottom: 0,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 180,
                  height: 180,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white,
                    border: Border.all(
                      color: const Color(0xFF0C6CF2),
                      width: 2,
                    ),
                    boxShadow: [
                      const BoxShadow(
                        color: Colors.black26,
                        blurRadius: 8,
                        offset: Offset(0, 4),
                      ),
                    ],
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: _buildProfileImage(context, user),
                ),
                const SizedBox(height: 8),
                if (user != null)
                  Text(
                    user.name.isNotEmpty ? user.name : 'Tanpa Nama',
                    style: Theme.of(context)
                        .textTheme
                        .bodyMedium
                        ?.copyWith(fontWeight: FontWeight.w600),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileImage(BuildContext context, User? user) {
    if (user == null) {
      return _buildPlaceholderPhoto(context);
    }

    final url = _resolveProfilePhotoUrl(user);
    if (url == null) {
      return _buildPlaceholderPhoto(context);
    }

    return Image.network(
      url,
      fit: BoxFit.cover,
      errorBuilder: (ctx, error, stack) => _buildPlaceholderPhoto(context),
    );
  }

  Widget _buildPlaceholderPhoto(BuildContext context) {
    return Center(
      child: Text(
        'FOTO',
        style: Theme.of(context)
            .textTheme
            .titleMedium
            ?.copyWith(letterSpacing: 2),
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
    final occurredAt = record.occurredAt?.toLocal();
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
      // 3. Get Location
      final locationService = ref.read(locationServiceProvider);
      final position = await locationService.getCurrentPosition();

      // 4. Get Shifts
      final shifts = await _shiftsFuture;

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
      } else {
        await notifier.checkOut(
          shiftId: shift.id,
          latitude: position.latitude,
          longitude: position.longitude,
          note: note,
          selfiePath: selfiePath,
        );
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

enum _PatrolOption { patrol, incident, sos }


