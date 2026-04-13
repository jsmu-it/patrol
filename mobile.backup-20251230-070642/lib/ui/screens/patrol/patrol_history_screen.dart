import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../config/timezone_helper.dart';
import '../../../models/patrol_log.dart';
import '../../../services/patrol_service.dart';

class PatrolHistoryScreen extends ConsumerStatefulWidget {
  const PatrolHistoryScreen({super.key});

  @override
  ConsumerState<PatrolHistoryScreen> createState() =>
      _PatrolHistoryScreenState();
}

class _PatrolHistoryScreenState extends ConsumerState<PatrolHistoryScreen> {
  late Future<List<PatrolLog>> _historyFuture;
  final _dateFormat = DateFormat('EEEE, d MMMM yyyy', 'id_ID');
  final _timeFormat = DateFormat('HH:mm', 'id_ID');

  @override
  void initState() {
    super.initState();
    _historyFuture = ref.read(patrolServiceProvider).getHistory();
  }

  Future<void> _refresh() async {
    setState(() {
      _historyFuture = ref.read(patrolServiceProvider).getHistory();
    });
    await _historyFuture;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Riwayat Patroli'),
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: FutureBuilder<List<PatrolLog>>(
          future: _historyFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }

            if (snapshot.hasError) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.error_outline,
                        size: 48, color: Colors.red),
                    const SizedBox(height: 16),
                    const Text('Gagal memuat riwayat.'),
                    const SizedBox(height: 8),
                    ElevatedButton(
                      onPressed: _refresh,
                      child: const Text('Coba Lagi'),
                    ),
                  ],
                ),
              );
            }

            final logs = snapshot.data ?? [];
            if (logs.isEmpty) {
              return const Center(
                child: Text('Belum ada riwayat patroli.'),
              );
            }

            // Sort descending by date
            logs.sort((a, b) => b.createdAt.compareTo(a.createdAt));

            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: logs.length,
              itemBuilder: (context, index) {
                final log = logs[index];
                return _buildLogCard(log);
              },
            );
          },
        ),
      ),
    );
  }

  Widget _buildLogCard(PatrolLog log) {
    // Removed TimezoneHelper usage because backend returns formatted local string
    final date = log.createdAt;
    final isIncident = log.type == 'incident';
    final isSos = log.type == 'sos';
    
    Color statusColor = Colors.blue;
    String typeLabel = 'Patroli';
    IconData icon = Icons.shield;

    if (isIncident) {
      statusColor = Colors.orange;
      typeLabel = 'Insiden';
      icon = Icons.warning;
    } else if (isSos) {
      statusColor = Colors.red;
      typeLabel = 'SOS';
      icon = Icons.sos;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: statusColor.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, size: 20, color: statusColor),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        log.title,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        typeLabel,
                        style: TextStyle(
                          color: statusColor,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
                Text(
                  _timeFormat.format(date),
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const Divider(height: 24),
            Row(
              children: [
                const Icon(Icons.calendar_today,
                    size: 14, color: Colors.grey),
                const SizedBox(width: 4),
                Text(
                  _dateFormat.format(date),
                  style: const TextStyle(fontSize: 12, color: Colors.grey),
                ),
                const Spacer(),
                const Icon(Icons.location_on, size: 14, color: Colors.grey),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    log.postName,
                    style: const TextStyle(fontSize: 12, color: Colors.grey),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    textAlign: TextAlign.end,
                  ),
                ),
              ],
            ),
            if (log.description != null && log.description!.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                log.description!,
                style: const TextStyle(fontStyle: FontStyle.italic),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
