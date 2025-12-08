import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';

import '../../../services/location_service.dart';
import '../../../services/patrol_service.dart';
import '../../../state/auth/auth_providers.dart';
import '../../../state/patrol/patrol_providers.dart';

class PatrolFormMode {
  static const normal = 'normal';
  static const sos = 'sos';
  static const incident = 'incident';
}

class PatrolFormArgs {
  const PatrolFormArgs({
    required this.mode,
    this.checkpointCode,
  });

  final String mode;
  final String? checkpointCode;
}

class PatrolFormScreen extends ConsumerStatefulWidget {
  const PatrolFormScreen({super.key, required this.args});

  final PatrolFormArgs args;

  @override
  ConsumerState<PatrolFormScreen> createState() => _PatrolFormScreenState();
}

class _PatrolFormScreenState extends ConsumerState<PatrolFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _postNameController = TextEditingController();
  final _descriptionController = TextEditingController();
  late final TextEditingController _checkpointCodeController;
  String? _photoPath;

  @override
  void initState() {
    super.initState();
    _checkpointCodeController = TextEditingController(
      text: widget.args.checkpointCode ?? '',
    );

    if (widget.args.mode == PatrolFormMode.sos) {
      _titleController.text = 'Laporan Darurat (SOS)';
    } else if (widget.args.mode == PatrolFormMode.incident) {
      _titleController.text = 'Laporan Insiden';
    } else if (widget.args.mode == PatrolFormMode.normal &&
        widget.args.checkpointCode != null &&
        widget.args.checkpointCode!.isNotEmpty) {
      _fetchCheckpointDetails(widget.args.checkpointCode!);
    }
  }

  Future<void> _fetchCheckpointDetails(String code) async {
    try {
      final service = ref.read(patrolServiceProvider);
      final data = await service.getCheckpoint(code);

      if (mounted) {
        setState(() {
          // Try to get title and post_name if available, even offline
          // If offline, this relies on getCheckpoint returning cached data
          if (data['title'] != null) _titleController.text = data['title'];
          if (data['post_name'] != null) {
            _postNameController.text = data['post_name'];
          }
          if (data['description'] != null) {
            _descriptionController.text = data['description'];
          }
        });
      }
    } catch (e) {
      // Ignore errors here, user can manually fill
      debugPrint('Error fetching checkpoint: $e');
    }
  }

  @override
  void dispose() {
    _checkpointCodeController.dispose();
    _titleController.dispose();
    _postNameController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final patrolState = ref.watch(patrolNotifierProvider);
    final isSubmitting = patrolState.isSubmitting;
    final isNormalMode = widget.args.mode == PatrolFormMode.normal;
    final isSos = widget.args.mode == PatrolFormMode.sos;
    final isIncident = widget.args.mode == PatrolFormMode.incident;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Form Patroli'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (isNormalMode) ...[
                  if (widget.args.checkpointCode != null &&
                      widget.args.checkpointCode!.isNotEmpty)
                    Text('Checkpoint code: ${widget.args.checkpointCode}')
                  else
                    TextFormField(
                      controller: _checkpointCodeController,
                      decoration: const InputDecoration(
                        labelText: 'Kode Checkpoint',
                        hintText: 'Tulis kode checkpoint (tanpa scan QR)',
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Kode checkpoint wajib diisi';
                        }
                        return null;
                      },
                    ),
                  const SizedBox(height: 16),
                ],
                if (!isSos) ...[
                  TextFormField(
                    controller: _titleController,
                    decoration: const InputDecoration(
                      labelText: 'Judul',
                    ),
                    readOnly: isIncident, // Read-only for incident
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Judul wajib diisi';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                ],
                TextFormField(
                  controller: _postNameController,
                  decoration: InputDecoration(
                    labelText: isNormalMode ? 'Nama Pos' : 'Lokasi',
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return isNormalMode
                          ? 'Nama pos wajib diisi'
                          : 'Lokasi wajib diisi';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                if (!isSos) ...[
                  TextFormField(
                    controller: _descriptionController,
                    decoration: const InputDecoration(
                      labelText: 'Deskripsi (opsional)',
                    ),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Foto (opsional)',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      ElevatedButton(
                        onPressed: isSubmitting ? null : _pickPhoto,
                        child: const Text('Ambil Foto'),
                      ),
                      const SizedBox(width: 16),
                      if (_photoPath != null)
                        SizedBox(
                          width: 80,
                          height: 80,
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: Image.file(
                              File(_photoPath!),
                              fit: BoxFit.cover,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 32),
                ],
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: isSubmitting ? null : () => _submit(context),
                    child: isSubmitting
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : Text(isSos ? 'Kirim Sinyal SOS' : 'Simpan Laporan'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _pickPhoto() async {
    final picker = ImagePicker();
    final image = await picker.pickImage(
      source: ImageSource.camera,
      imageQuality: 85,
    );

    if (image == null) return;

    setState(() {
      _photoPath = image.path;
    });
  }

  Future<void> _submit(BuildContext context) async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final isNormalMode = widget.args.mode == PatrolFormMode.normal;
    String checkpointCode = '';

    if (isNormalMode) {
      checkpointCode = (widget.args.checkpointCode ??
              _checkpointCodeController.text.trim())
          .trim();

      if (checkpointCode.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Kode checkpoint wajib diisi.')),
        );
        return;
      }
    }

    final authState = ref.read(authNotifierProvider);
    final user = authState.user;

    if (user?.activeProjectId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('User tidak memiliki project aktif.')),
      );
      return;
    }

    final locationService = ref.read(locationServiceProvider);
    final notifier = ref.read(patrolNotifierProvider.notifier);

    try {
      // Use quick position for faster response
      final position = await locationService.getQuickPosition();

      final message = await notifier.submit(
        projectId: user!.activeProjectId!,
        checkpointCode: checkpointCode,
        title: _titleController.text.trim(),
        postName: _postNameController.text.trim(),
        description: _descriptionController.text.trim().isEmpty
            ? null
            : _descriptionController.text.trim(),
        latitude: position.latitude,
        longitude: position.longitude,
        photoPath: _photoPath,
        type: widget.args.mode == PatrolFormMode.normal ? 'patrol' : widget.args.mode,
      );

      if (!context.mounted) return;

      final error = ref.read(patrolNotifierProvider).errorMessage;
      if (error != null && error.isNotEmpty) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(error)));
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message ?? 'Laporan patroli tersimpan.')),
      );

      Navigator.of(context).pop();
    } on LocationException catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.message)),
      );
    }
  }
}
