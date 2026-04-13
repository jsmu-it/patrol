import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../routes/app_router.dart';
import '../../../state/leave/leave_list_providers.dart';

class LeaveListScreen extends ConsumerStatefulWidget {
  const LeaveListScreen({super.key});

  @override
  ConsumerState<LeaveListScreen> createState() => _LeaveListScreenState();
}

class _LeaveListScreenState extends ConsumerState<LeaveListScreen> {
  final _dateFormat = DateFormat('dd MMM yyyy');

  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(leaveListNotifierProvider.notifier).load(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(leaveListNotifierProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Pengajuan Izin / Cuti'),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.of(context).pushNamed(AppRoutes.leaveForm).then((_) {
            ref.read(leaveListNotifierProvider.notifier).load();
          });
        },
        child: const Icon(Icons.add),
      ),
      body: Builder(
        builder: (context) {
          if (state.isLoading) {
            return const Center(child: CircularProgressIndicator());
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

          if (state.items.isEmpty) {
            return const Center(
              child: Text('Belum ada pengajuan izin/cuti.'),
            );
          }

          return ListView.builder(
            itemCount: state.items.length,
            itemBuilder: (context, index) {
              final item = state.items[index];
              final from = item.dateFrom != null
                  ? _dateFormat.format(item.dateFrom!)
                  : '-';
              final to = item.dateTo != null
                  ? _dateFormat.format(item.dateTo!)
                  : '-';

              String status;
              switch (item.status) {
                case 'approved':
                  status = 'Disetujui';
                  break;
                case 'rejected':
                  status = 'Ditolak';
                  break;
                default:
                  status = 'Menunggu';
              }

              return Card(
                margin:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.type,
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 4),
                      Text('Tanggal: $from s/d $to'),
                      const SizedBox(height: 4),
                      Text('Status: $status'),
                      const SizedBox(height: 4),
                      Text('Alasan: ${item.reason}'),
                      if (item.doctorNote != null &&
                          item.doctorNote!.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.only(top: 4),
                          child: Text('Catatan dokter: ${item.doctorNote}'),
                        ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
