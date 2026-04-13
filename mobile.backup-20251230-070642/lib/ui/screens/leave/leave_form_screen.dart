import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../state/leave/leave_form_providers.dart';

class LeaveFormScreen extends ConsumerStatefulWidget {
  const LeaveFormScreen({super.key});

  @override
  ConsumerState<LeaveFormScreen> createState() => _LeaveFormScreenState();
}

class _LeaveFormScreenState extends ConsumerState<LeaveFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _reasonController = TextEditingController();
  final _doctorNoteController = TextEditingController();
  String _type = 'Cuti';
  DateTime _dateFrom = DateTime.now();
  DateTime _dateTo = DateTime.now();
  final _dateFormat = DateFormat('dd MMM yyyy');

  @override
  void dispose() {
    _reasonController.dispose();
    _doctorNoteController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(leaveFormNotifierProvider);
    final isSubmitting = state.isSubmitting;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Pengajuan Izin / Cuti'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                DropdownButtonFormField<String>(
                  initialValue: _type,
                  decoration: const InputDecoration(labelText: 'Jenis'),
                  items: const [
                    DropdownMenuItem(value: 'Cuti', child: Text('Cuti')),
                    DropdownMenuItem(value: 'Izin', child: Text('Izin')),
                    DropdownMenuItem(value: 'Sakit', child: Text('Sakit')),
                  ],
                  onChanged: (value) {
                    if (value == null) return;
                    setState(() {
                      _type = value;
                    });
                  },
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Dari'),
                        TextButton(
                          onPressed: () => _pickDate(context, isFrom: true),
                          child: Text(_dateFormat.format(_dateFrom)),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Sampai'),
                        TextButton(
                          onPressed: () => _pickDate(context, isFrom: false),
                          child: Text(_dateFormat.format(_dateTo)),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _reasonController,
                  decoration: const InputDecoration(
                    labelText: 'Alasan',
                  ),
                  maxLines: 3,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Alasan wajib diisi';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _doctorNoteController,
                  decoration: const InputDecoration(
                    labelText: 'Catatan Dokter (opsional)',
                  ),
                  maxLines: 2,
                ),
                const SizedBox(height: 32),
                if (state.errorMessage != null)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Text(
                      state.errorMessage!,
                      style: const TextStyle(color: Colors.red),
                    ),
                  ),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: isSubmitting ? null : _submit,
                    child: isSubmitting
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Text('Kirim Pengajuan'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _pickDate(BuildContext context, {required bool isFrom}) async {
    final initial = isFrom ? _dateFrom : _dateTo;
    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );

    if (picked == null) return;

    setState(() {
      if (isFrom) {
        _dateFrom = picked;
        if (_dateFrom.isAfter(_dateTo)) {
          _dateTo = _dateFrom;
        }
      } else {
        _dateTo = picked;
        if (_dateTo.isBefore(_dateFrom)) {
          _dateFrom = _dateTo;
        }
      }
    });
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final notifier = ref.read(leaveFormNotifierProvider.notifier);

    final message = await notifier.submit(
      type: _type,
      dateFrom: _dateFrom,
      dateTo: _dateTo,
      reason: _reasonController.text.trim(),
      doctorNote: _doctorNoteController.text.trim().isEmpty
          ? null
          : _doctorNoteController.text.trim(),
    );

    if (!mounted) return;

    final error = ref.read(leaveFormNotifierProvider).errorMessage;
    if (error != null && error.isNotEmpty) {
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(error)));
      return;
    }

    if (message != null) {
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(message)));
    }

    Navigator.of(context).pop();
  }
}
