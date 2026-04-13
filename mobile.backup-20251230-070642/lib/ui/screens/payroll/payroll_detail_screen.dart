import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../models/payroll_slip.dart';
import '../../../state/payroll/payroll_providers.dart';

class PayrollDetailScreen extends ConsumerStatefulWidget {
  const PayrollDetailScreen({super.key, required this.slipId});

  final int slipId;

  @override
  ConsumerState<PayrollDetailScreen> createState() =>
      _PayrollDetailScreenState();
}

class _PayrollDetailScreenState extends ConsumerState<PayrollDetailScreen> {
  final _currencyFormat = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref
          .read(payrollDetailNotifierProvider(widget.slipId).notifier)
          .load(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(payrollDetailNotifierProvider(widget.slipId));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Slip Gaji'),
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
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.error_outline, size: 48, color: Colors.red),
                    const SizedBox(height: 16),
                    Text(
                      state.errorMessage!,
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.red),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () {
                        ref
                            .read(payrollDetailNotifierProvider(widget.slipId)
                                .notifier)
                            .load();
                      },
                      child: const Text('Coba Lagi'),
                    ),
                  ],
                ),
              ),
            );
          }

          final slip = state.slip;
          if (slip == null) {
            return const Center(child: Text('Data tidak ditemukan.'));
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(slip),
                const SizedBox(height: 20),
                _buildEmployeeInfo(slip),
                const SizedBox(height: 20),
                _buildIncomeSection(slip),
                const SizedBox(height: 20),
                _buildDeductionSection(slip),
                const SizedBox(height: 20),
                _buildSummary(slip),
                const SizedBox(height: 20),
                _buildSignature(slip),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildHeader(PayrollSlip slip) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.blue.shade600, Colors.blue.shade800],
        ),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          const Text(
            'SLIP GAJI',
            style: TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.bold,
              letterSpacing: 2,
            ),
          ),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              'Periode: ${slip.periodMonth}',
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmployeeInfo(PayrollSlip slip) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'DATA KARYAWAN',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 14,
                color: Colors.grey,
              ),
            ),
            const Divider(),
            _buildInfoRow('Nama', slip.name ?? '-'),
            _buildInfoRow('NIP', slip.nip ?? '-'),
            _buildInfoRow('Unit', slip.unit ?? '-'),
            _buildInfoRow('Jabatan', slip.position ?? '-'),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 80,
            child: Text(
              label,
              style: TextStyle(color: Colors.grey.shade600),
            ),
          ),
          const Text(': '),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildIncomeSection(PayrollSlip slip) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(Icons.add_circle, color: Colors.green.shade600),
                const SizedBox(width: 8),
                const Text(
                  'PENDAPATAN',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
              ],
            ),
            const Divider(),
            if (slip.incomeItems.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 8),
                child: Text('Tidak ada data pendapatan'),
              )
            else
              ...slip.incomeItems.map((item) => _buildAmountRow(
                    item.label,
                    item.amount,
                    isPositive: true,
                  )),
            const Divider(),
            _buildAmountRow(
              'Total Pendapatan',
              slip.totalIncome,
              isPositive: true,
              isBold: true,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDeductionSection(PayrollSlip slip) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(Icons.remove_circle, color: Colors.red.shade600),
                const SizedBox(width: 8),
                const Text(
                  'POTONGAN',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
              ],
            ),
            const Divider(),
            if (slip.deductionItems.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 8),
                child: Text('Tidak ada potongan'),
              )
            else
              ...slip.deductionItems.map((item) => _buildAmountRow(
                    item.label,
                    item.amount,
                    isPositive: false,
                  )),
            const Divider(),
            _buildAmountRow(
              'Total Potongan',
              slip.totalDeduction,
              isPositive: false,
              isBold: true,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAmountRow(String label, double amount,
      {bool isPositive = true, bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              label,
              style: TextStyle(
                fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
              ),
            ),
          ),
          Text(
            _currencyFormat.format(amount),
            style: TextStyle(
              fontWeight: isBold ? FontWeight.bold : FontWeight.w500,
              color: isPositive ? Colors.green.shade700 : Colors.red.shade700,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummary(PayrollSlip slip) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.green.shade500, Colors.green.shade700],
        ),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          const Text(
            'GAJI BERSIH',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 14,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _currencyFormat.format(slip.netIncome),
            style: const TextStyle(
              color: Colors.white,
              fontSize: 28,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSignature(PayrollSlip slip) {
    if (slip.signLocation == null && slip.signDate == null) {
      return const SizedBox.shrink();
    }

    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            if (slip.signLocation != null)
              Text(
                slip.signLocation!,
                style: const TextStyle(fontWeight: FontWeight.w500),
              ),
            if (slip.signDate != null) ...[
              const SizedBox(height: 4),
              Text(
                slip.signDate!,
                style: TextStyle(color: Colors.grey.shade600),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
