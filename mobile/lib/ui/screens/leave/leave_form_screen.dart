import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';

import '../../../models/leave_balance.dart';
import '../../../state/auth/auth_providers.dart';
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
  int? _selectedLeaveTypeId;
  DateTime _dateFrom = DateTime.now();
  DateTime _dateTo = DateTime.now();
  TimeOfDay? _timeFrom;
  TimeOfDay? _timeTo;
  final _dateFormat = DateFormat('dd MMM yyyy');
  File? _sickLetterPhoto;
  File? _permitPhoto;
  
  List<LeaveBalance>? _userLeaveBalances;

  @override
  void initState() {
    super.initState();
    // Leave types will be loaded from user's leave balances
  }

  @override
  void dispose() {
    _reasonController.dispose();
    _doctorNoteController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(leaveFormNotifierProvider);
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;
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
                // Type Dropdown
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
                      // Reset type-specific fields
                      _selectedLeaveTypeId = null;
                      _timeFrom = null;
                      _timeTo = null;
                      if (value != 'Sakit') {
                        _sickLetterPhoto = null;
                      }
                      if (value != 'Izin') {
                        _permitPhoto = null;
                      }
                    });
                  },
                ),
                const SizedBox(height: 16),

                // Leave Type Dropdown (only for Cuti)
                if (_type == 'Cuti') ...[
                  // Get user's assigned leave types from their balances
                  if (user?.leaveBalances != null && user!.leaveBalances!.isNotEmpty) ...[
                    DropdownButtonFormField<int>(
                      value: _selectedLeaveTypeId,
                      decoration: const InputDecoration(
                        labelText: 'Jenis Cuti',
                        hintText: 'Pilih jenis cuti',
                      ),
                      items: user.leaveBalances!
                          .map((balance) => DropdownMenuItem(
                                value: balance.leaveTypeId,
                                child: Text(balance.leaveTypeName ?? 'Unknown'),
                              ))
                          .toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedLeaveTypeId = value;
                        });
                      },
                      validator: (value) {
                        if (_type == 'Cuti' && value == null) {
                          return 'Pilih jenis cuti';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 8),
                    // Show balance if leave type is selected
                    if (_selectedLeaveTypeId != null) ...[
                      _buildBalanceDisplay(user.leaveBalances),
                      const SizedBox(height: 8),
                    ],
                  ] else ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.orange.shade50,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.orange.shade200),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.info_outline, size: 20, color: Colors.orange.shade700),
                          const SizedBox(width: 8),
                          const Expanded(
                            child: Text(
                              'Anda belum memiliki alokasi cuti. Hubungi admin.',
                              style: TextStyle(fontSize: 12),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                  const SizedBox(height: 16),
                ],

                // Date Range (for all types)
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

                // Time Range (only for Izin)
                if (_type == 'Izin') ...[
                  const Text(
                    'Waktu Izin',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Dari Jam', style: TextStyle(fontSize: 12)),
                            OutlinedButton(
                              onPressed: () => _pickTime(context, isFrom: true),
                              child: Text(
                                _timeFrom != null
                                    ? _timeFrom!.format(context)
                                    : 'Pilih waktu',
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Sampai Jam', style: TextStyle(fontSize: 12)),
                            OutlinedButton(
                              onPressed: () => _pickTime(context, isFrom: false),
                              child: Text(
                                _timeTo != null
                                    ? _timeTo!.format(context)
                                    : 'Pilih waktu',
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                ],

                // Reason
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

                // Doctor Note (optional, for all types)
                TextFormField(
                  controller: _doctorNoteController,
                  decoration: const InputDecoration(
                    labelText: 'Catatan Dokter (opsional)',
                  ),
                  maxLines: 2,
                ),
                const SizedBox(height: 16),

                // Sick Letter Photo (only for Sakit)
                if (_type == 'Sakit') ...[
                  const Text(
                    'Foto Surat Sakit (opsional)',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  const SizedBox(height: 8),
                  if (_sickLetterPhoto != null) ...[
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.file(
                        _sickLetterPhoto!,
                        height: 150,
                        width: double.infinity,
                        fit: BoxFit.cover,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        TextButton.icon(
                          onPressed: () => _pickPhoto(isSickLetter: true),
                          icon: const Icon(Icons.photo_camera),
                          label: const Text('Ganti Foto'),
                        ),
                        TextButton.icon(
                          onPressed: () {
                            setState(() {
                              _sickLetterPhoto = null;
                            });
                          },
                          icon: const Icon(Icons.delete, color: Colors.red),
                          label: const Text('Hapus', style: TextStyle(color: Colors.red)),
                        ),
                      ],
                    ),
                  ] else
                    OutlinedButton.icon(
                      onPressed: () => _pickPhoto(isSickLetter: true),
                      icon: const Icon(Icons.add_a_photo),
                      label: const Text('Upload Foto Surat Sakit'),
                    ),
                  const SizedBox(height: 16),
                ],

                // Permit Photo (only for Izin)
                if (_type == 'Izin') ...[
                  const Text(
                    'Foto Pendukung (opsional)',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  const SizedBox(height: 8),
                  if (_permitPhoto != null) ...[
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.file(
                        _permitPhoto!,
                        height: 150,
                        width: double.infinity,
                        fit: BoxFit.cover,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        TextButton.icon(
                          onPressed: () => _pickPhoto(isSickLetter: false),
                          icon: const Icon(Icons.photo_camera),
                          label: const Text('Ganti Foto'),
                        ),
                        TextButton.icon(
                          onPressed: () {
                            setState(() {
                              _permitPhoto = null;
                            });
                          },
                          icon: const Icon(Icons.delete, color: Colors.red),
                          label: const Text('Hapus', style: TextStyle(color: Colors.red)),
                        ),
                      ],
                    ),
                  ] else
                    OutlinedButton.icon(
                      onPressed: () => _pickPhoto(isSickLetter: false),
                      icon: const Icon(Icons.add_a_photo),
                      label: const Text('Upload Foto'),
                    ),
                  const SizedBox(height: 16),
                ],

                // Error Message
                if (state.errorMessage != null)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Text(
                      state.errorMessage!,
                      style: const TextStyle(color: Colors.red),
                    ),
                  ),

                // Submit Button
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

  Widget _buildBalanceDisplay(List<LeaveBalance>? balances) {
    if (balances == null || _selectedLeaveTypeId == null) {
      return const SizedBox.shrink();
    }

    final balance = balances.firstWhere(
      (b) => b.leaveTypeId == _selectedLeaveTypeId,
      orElse: () => const LeaveBalance(
        leaveTypeId: 0,
        leaveTypeName: '',
        quota: 0,
        used: 0,
        remaining: 0,
      ),
    );

    if (balance.leaveTypeId == 0) {
      return Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.orange.shade50,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.orange.shade200),
        ),
        child: Row(
          children: [
            Icon(Icons.info_outline, size: 20, color: Colors.orange.shade700),
            const SizedBox(width: 8),
            const Expanded(
              child: Text(
                'Tidak ada saldo untuk jenis cuti ini',
                style: TextStyle(fontSize: 12),
              ),
            ),
          ],
        ),
      );
    }

    final isLow = balance.remaining < balance.quota * 0.3;

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isLow ? Colors.red.shade50 : Colors.green.shade50,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: isLow ? Colors.red.shade200 : Colors.green.shade200,
        ),
      ),
      child: Row(
        children: [
          Icon(
            isLow ? Icons.warning_amber : Icons.check_circle_outline,
            size: 20,
            color: isLow ? Colors.red.shade700 : Colors.green.shade700,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              'Saldo: ${balance.remaining} hari tersisa dari ${balance.quota}',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: isLow ? Colors.red.shade900 : Colors.green.shade900,
              ),
            ),
          ),
        ],
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

  Future<void> _pickTime(BuildContext context, {required bool isFrom}) async {
    final initial = isFrom ? _timeFrom : _timeTo;
    final picked = await showTimePicker(
      context: context,
      initialTime: initial ?? TimeOfDay.now(),
    );

    if (picked == null) return;

    setState(() {
      if (isFrom) {
        _timeFrom = picked;
      } else {
        _timeTo = picked;
      }
    });
  }

  Future<void> _pickPhoto({required bool isSickLetter}) async {
    final picker = ImagePicker();
    final source = await showModalBottomSheet<ImageSource>(
      context: context,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.camera_alt),
              title: const Text('Kamera'),
              onTap: () => Navigator.pop(context, ImageSource.camera),
            ),
            ListTile(
              leading: const Icon(Icons.photo_library),
              title: const Text('Galeri'),
              onTap: () => Navigator.pop(context, ImageSource.gallery),
            ),
          ],
        ),
      ),
    );

    if (source == null) return;

    final pickedFile = await picker.pickImage(source: source, maxWidth: 1200);
    if (pickedFile != null) {
      setState(() {
        if (isSickLetter) {
          _sickLetterPhoto = File(pickedFile.path);
        } else {
          _permitPhoto = File(pickedFile.path);
        }
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    // Additional validation for Izin type
    if (_type == 'Izin' && (_timeFrom == null || _timeTo == null)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Waktu izin wajib diisi')),
      );
      return;
    }

    final notifier = ref.read(leaveFormNotifierProvider.notifier);

    // Format time as HH:mm
    String? timeFromStr;
    String? timeToStr;
    if (_timeFrom != null) {
      timeFromStr = '${_timeFrom!.hour.toString().padLeft(2, '0')}:${_timeFrom!.minute.toString().padLeft(2, '0')}';
    }
    if (_timeTo != null) {
      timeToStr = '${_timeTo!.hour.toString().padLeft(2, '0')}:${_timeTo!.minute.toString().padLeft(2, '0')}';
    }

    final message = await notifier.submit(
      type: _type,
      leaveTypeId: _type == 'Cuti' ? _selectedLeaveTypeId : null,
      dateFrom: _dateFrom,
      dateTo: _dateTo,
      timeFrom: timeFromStr,
      timeTo: timeToStr,
      reason: _reasonController.text.trim(),
      doctorNote: _doctorNoteController.text.trim().isEmpty
          ? null
          : _doctorNoteController.text.trim(),
      sickLetterPath: _sickLetterPhoto?.path,
      permitPhotoPath: _permitPhoto?.path,
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
