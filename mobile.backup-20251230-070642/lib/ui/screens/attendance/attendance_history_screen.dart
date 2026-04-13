import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../config/timezone_helper.dart';
import '../../../state/attendance/attendance_history_providers.dart';

class AttendanceHistoryScreen extends ConsumerStatefulWidget {
  const AttendanceHistoryScreen({super.key});

  @override
  ConsumerState<AttendanceHistoryScreen> createState() =>
      _AttendanceHistoryScreenState();
}

class _AttendanceHistoryScreenState
    extends ConsumerState<AttendanceHistoryScreen> {
  final _dateFormat = DateFormat('EEEE, d MMMM yyyy', 'id_ID');
  final _timeFormat = DateFormat('HH:mm', 'id_ID');

  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(attendanceHistoryNotifierProvider.notifier).loadInitial(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(attendanceHistoryNotifierProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Riwayat Absensi'),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Dari'),
                    TextButton(
                      onPressed: () => _pickDate(context, isFrom: true),
                      child: Text(_dateFormat.format(state.from)),
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Sampai'),
                    TextButton(
                      onPressed: () => _pickDate(context, isFrom: false),
                      child: Text(_dateFormat.format(state.to)),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Expanded(
            child: Builder(
              builder: (context) {
                if (state.isLoading) {
                  return const Center(
                    child: CircularProgressIndicator(),
                  );
                }

                if (state.errorMessage != null) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Text(
                        state.errorMessage!,
                        textAlign: TextAlign.center,
                        style: const TextStyle(color: Colors.red),
                      ),
                    ),
                  );
                }

                if (state.records.isEmpty) {
                  return const Center(
                    child: Text('Belum ada riwayat absensi.'),
                  );
                }

                return ListView.builder(
                  itemCount: state.records.length,
                  itemBuilder: (context, index) {
                    final record = state.records[index];
                    // Removed TimezoneHelper usage because backend returns formatted local string
                    final occurredAt = record.occurredAt;
                        
                    final dateStr =
                        occurredAt != null ? _dateFormat.format(occurredAt) : '-';
                    final timeStr =
                        occurredAt != null ? _timeFormat.format(occurredAt) : '-';

                    final isClockIn = record.type == 'clock_in';
                    final jenis = isClockIn ? 'Masuk' : 'Keluar';

                    final mode = record.mode == 'dinas' ? 'Dinas' : 'Normal';
                    String status = mode;
                    if (record.mode == 'dinas') {
                      final sd = record.statusDinas;
                      if (sd == 'approved') {
                        status = 'Dinas - Disetujui';
                      } else if (sd == 'rejected') {
                        status = 'Dinas - Ditolak';
                      } else {
                        status = 'Dinas - Menunggu';
                      }
                    }

                    return Card(
                      margin:
                          const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  dateStr,
                                  style:
                                      Theme.of(context).textTheme.titleMedium,
                                ),
                                Text(timeStr),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text('Jenis: $jenis'),
                            Text('Proyek: ID ${record.projectId}'),
                            Text('Status: $status'),
                            if (record.note != null && record.note!.isNotEmpty)
                              Padding(
                                padding: const EdgeInsets.only(top: 4),
                                child: Text('Catatan: ${record.note!}'),
                              ),
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _pickDate(BuildContext context, {required bool isFrom}) async {
    final state = ref.read(attendanceHistoryNotifierProvider);
    final initial = isFrom ? state.from : state.to;

    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 1)),
    );

    if (picked == null) return;

    DateTime from = state.from;
    DateTime to = state.to;
    if (isFrom) {
      from = picked;
      if (from.isAfter(to)) {
        to = from;
      }
    } else {
      to = picked;
      if (to.isBefore(from)) {
        from = to;
      }
    }

    await ref
        .read(attendanceHistoryNotifierProvider.notifier)
        .loadForRange(from, to);
  }
}
