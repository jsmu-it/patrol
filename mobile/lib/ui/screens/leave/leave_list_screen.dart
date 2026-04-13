import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../routes/app_router.dart';
import '../../../services/leave_service.dart';
import '../../../state/auth/auth_providers.dart';
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

  Future<void> _cancelRequest(int id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Batalkan Pengajuan?'),
        content: const Text('Apakah Anda yakin ingin membatalkan pengajuan ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Tidak'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Batalkan'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final service = ref.read(leaveServiceProvider);
      await service.cancel(id);
      ref.read(leaveListNotifierProvider.notifier).load();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan berhasil dibatalkan.')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal membatalkan pengajuan.')),
        );
      }
    }
  }

  Widget _buildLeaveBalanceCard(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;
    final balances = user?.leaveBalances;

    if (balances == null || balances.isEmpty) {
      return const SizedBox.shrink();
    }

    return Card(
      margin: const EdgeInsets.fromLTRB(16, 16, 16, 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(Icons.calendar_month, color: Theme.of(context).primaryColor),
                const SizedBox(width: 8),
                Text(
                  'Saldo Cuti Anda',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            Wrap(
              spacing: 16,
              runSpacing: 8,
              children: balances.map((balance) {
                return Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: balance.remaining > 0 
                        ? Colors.green.shade50 
                        : Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Column(
                    children: [
                      Text(
                        balance.leaveTypeName ?? 'Cuti',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${balance.remaining}/${balance.quota}',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: balance.remaining > 0 ? Colors.green : Colors.grey,
                        ),
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
          ],
        ),
      ),
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

          return Column(
            children: [
              _buildLeaveBalanceCard(context),
              Expanded(
                child: state.items.isEmpty
                    ? const Center(
                        child: Text('Belum ada pengajuan izin/cuti.'),
                      )
                    : ListView.builder(
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
                          Color statusColor;
                          switch (item.status) {
                            case 'approved':
                              status = 'Disetujui';
                              statusColor = Colors.green;
                              break;
                            case 'rejected':
                              status = 'Ditolak';
                              statusColor = Colors.red;
                              break;
                            case 'cancelled':
                              status = 'Dibatalkan';
                              statusColor = Colors.grey;
                              break;
                            default:
                              status = 'Menunggu';
                              statusColor = Colors.orange;
                          }

                          final isPending = item.status == 'pending';

                          return Card(
                            margin: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 8),
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        item.type,
                                        style: Theme.of(context)
                                            .textTheme
                                            .titleMedium,
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 4,
                                        ),
                                        decoration: BoxDecoration(
                                          color:
                                              statusColor.withValues(alpha: 0.1),
                                          borderRadius:
                                              BorderRadius.circular(4),
                                        ),
                                        child: Text(
                                          status,
                                          style: TextStyle(
                                            color: statusColor,
                                            fontWeight: FontWeight.w500,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text('Tanggal: $from s/d $to'),
                                  const SizedBox(height: 4),
                                  Text('Alasan: ${item.reason}'),
                                  if (item.doctorNote != null &&
                                      item.doctorNote!.isNotEmpty)
                                    Padding(
                                      padding: const EdgeInsets.only(top: 4),
                                      child: Text(
                                          'Catatan dokter: ${item.doctorNote}'),
                                    ),
                                  if (isPending) ...[
                                    const SizedBox(height: 8),
                                    Align(
                                      alignment: Alignment.centerRight,
                                      child: TextButton.icon(
                                        onPressed: () =>
                                            _cancelRequest(item.id),
                                        icon:
                                            const Icon(Icons.cancel, size: 18),
                                        label: const Text('Batalkan'),
                                        style: TextButton.styleFrom(
                                          foregroundColor: Colors.red,
                                        ),
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          );
                        },
                      ),
              ),
            ],
          );
        },
      ),
    );
  }
}
