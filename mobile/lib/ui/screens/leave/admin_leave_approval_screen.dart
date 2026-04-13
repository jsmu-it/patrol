import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../models/leave_request.dart';
import '../../../services/leave_service.dart';

class AdminLeaveApprovalScreen extends ConsumerStatefulWidget {
  const AdminLeaveApprovalScreen({super.key});

  @override
  ConsumerState<AdminLeaveApprovalScreen> createState() =>
      _AdminLeaveApprovalScreenState();
}

class _AdminLeaveApprovalScreenState
    extends ConsumerState<AdminLeaveApprovalScreen> {
  final _dateFormat = DateFormat('dd MMM yyyy');
  String _selectedFilter = 'pending';
  List<LeaveRequest> _requests = [];
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadRequests();
  }

  Future<void> _loadRequests() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final service = ref.read(leaveServiceProvider);
      final requests = await service.listAdmin(
        status: _selectedFilter == 'all' ? null : _selectedFilter,
      );
      setState(() {
        _requests = requests;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Gagal memuat data: $e';
        _isLoading = false;
      });
    }
  }

  Future<void> _approveRequest(LeaveRequest request) async {
    final confirmed = await _showConfirmDialog(
      'Setujui Pengajuan?',
      'Apakah Anda yakin ingin menyetujui pengajuan ${request.type} dari ${request.userName ?? "karyawan ini"}?',
    );

    if (confirmed != true) return;

    try {
      final service = ref.read(leaveServiceProvider);
      await service.approve(request.id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan berhasil disetujui.')),
        );
        _loadRequests();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal menyetujui: $e')),
        );
      }
    }
  }

  Future<void> _rejectRequest(LeaveRequest request) async {
    final confirmed = await _showConfirmDialog(
      'Tolak Pengajuan?',
      'Apakah Anda yakin ingin menolak pengajuan ${request.type} dari ${request.userName ?? "karyawan ini"}?',
      isDestructive: true,
    );

    if (confirmed != true) return;

    try {
      final service = ref.read(leaveServiceProvider);
      await service.reject(request.id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengajuan berhasil ditolak.')),
        );
        _loadRequests();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal menolak: $e')),
        );
      }
    }
  }

  Future<void> _setPendingRequest(LeaveRequest request) async {
    final confirmed = await _showConfirmDialog(
      'Ubah ke Pending?',
      'Apakah Anda yakin ingin mengubah status pengajuan ini menjadi pending?',
    );

    if (confirmed != true) return;

    try {
      final service = ref.read(leaveServiceProvider);
      await service.setPending(request.id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Status berhasil diubah ke pending.')),
        );
        _loadRequests();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengubah status: $e')),
        );
      }
    }
  }

  Future<bool?> _showConfirmDialog(
    String title,
    String content, {
    bool isDestructive = false,
  }) async {
    return showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(content),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: isDestructive
                ? ElevatedButton.styleFrom(backgroundColor: Colors.red)
                : null,
            child: const Text('Ya'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Persetujuan Cuti'),
      ),
      body: Column(
        children: [
          // Filter chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                _buildFilterChip('Pending', 'pending'),
                const SizedBox(width: 8),
                _buildFilterChip('Disetujui', 'approved'),
                const SizedBox(width: 8),
                _buildFilterChip('Ditolak', 'rejected'),
                const SizedBox(width: 8),
                _buildFilterChip('Semua', 'all'),
              ],
            ),
          ),
          // List
          Expanded(
            child: _buildBody(),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _selectedFilter == value;
    return FilterChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        if (selected) {
          setState(() => _selectedFilter = value);
          _loadRequests();
        }
      },
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(color: Colors.red),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _loadRequests,
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_requests.isEmpty) {
      return Center(
        child: Text(
          _selectedFilter == 'all'
              ? 'Belum ada pengajuan.'
              : 'Belum ada pengajuan dengan status $_selectedFilter.',
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadRequests,
      child: ListView.builder(
        itemCount: _requests.length,
        itemBuilder: (context, index) {
          final request = _requests[index];
          return _buildRequestCard(request);
        },
      ),
    );
  }

  Widget _buildRequestCard(LeaveRequest request) {
    final from =
        request.dateFrom != null ? _dateFormat.format(request.dateFrom!) : '-';
    final to =
        request.dateTo != null ? _dateFormat.format(request.dateTo!) : '-';

    String status;
    Color statusColor;
    switch (request.status) {
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

    final isPending = request.status == 'pending';
    final canChangeToPending = request.status == 'approved' ||
        request.status == 'rejected';

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        request.userName ?? 'Unknown',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        request.type,
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(4),
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
            const SizedBox(height: 8),
            Text('Tanggal: $from s/d $to'),
            const SizedBox(height: 4),
            Text('Alasan: ${request.reason}'),
            if (request.doctorNote != null && request.doctorNote!.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(top: 4),
                child: Text('Catatan dokter: ${request.doctorNote}'),
              ),
            // Action buttons
            if (isPending) ...[
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  TextButton.icon(
                    onPressed: () => _rejectRequest(request),
                    icon: const Icon(Icons.close, size: 18),
                    label: const Text('Tolak'),
                    style: TextButton.styleFrom(
                      foregroundColor: Colors.red,
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton.icon(
                    onPressed: () => _approveRequest(request),
                    icon: const Icon(Icons.check, size: 18),
                    label: const Text('Setujui'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ],
              ),
            ] else if (canChangeToPending) ...[
              const SizedBox(height: 12),
              Align(
                alignment: Alignment.centerRight,
                child: TextButton.icon(
                  onPressed: () => _setPendingRequest(request),
                  icon: const Icon(Icons.refresh, size: 18),
                  label: const Text('Ubah ke Pending'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
