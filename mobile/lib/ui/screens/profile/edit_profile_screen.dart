import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';

import '../../../config/app_config.dart';
import '../../../models/user.dart';
import '../../../services/api_client.dart';
import '../../../services/profile_service.dart';
import '../../../state/auth/auth_providers.dart';

class EditProfileScreen extends ConsumerStatefulWidget {
  const EditProfileScreen({super.key});

  @override
  ConsumerState<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends ConsumerState<EditProfileScreen> {
  File? _selectedPhoto;
  bool _isUpdatingPhoto = false;
  bool _isChangingPassword = false;
  String? _errorMessage;
  String? _successMessage;

  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _passwordFormKey = GlobalKey<FormState>();

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;

    final apiBase = ref.watch(appConfigProvider).apiBaseUrl;
    final filesBaseUrl = apiBase.replaceFirst(RegExp(r'/api/?$'), '');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Profil'),
      ),
      body: user == null
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Error/Success messages
                  if (_errorMessage != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.red.shade50,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.error, color: Colors.red),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(_errorMessage!, style: const TextStyle(color: Colors.red)),
                          ),
                        ],
                      ),
                    ),
                  if (_successMessage != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.green.shade50,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.check_circle, color: Colors.green),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(_successMessage!, style: const TextStyle(color: Colors.green)),
                          ),
                        ],
                      ),
                    ),

                  // Profile Photo Section
                  _buildSectionTitle('Foto Profil'),
                  _buildPhotoSection(user, filesBaseUrl),
                  const SizedBox(height: 24),

                  // Location Section
                  _buildSectionTitle('Lokasi Tugas'),
                  Text(
                    user.activeProjectName ?? 'Belum ada lokasi tugas',
                    style: Theme.of(context).textTheme.bodyLarge,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Untuk mengubah lokasi tugas, hubungi admin.',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey),
                  ),
                  const SizedBox(height: 24),

                  // Password Section
                  _buildSectionTitle('Ubah Password'),
                  _buildPasswordSection(),
                ],
              ),
            ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Text(
        title,
        style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
      ),
    );
  }

  Widget _buildPhotoSection(User user, String filesBaseUrl) {
    String? url;
    if (user.profilePhotoPath != null && user.profilePhotoPath!.isNotEmpty) {
      final baseUrl = filesBaseUrl.endsWith('/')
          ? filesBaseUrl.substring(0, filesBaseUrl.length - 1)
          : filesBaseUrl;
      final path = user.profilePhotoPath!.startsWith('/')
          ? user.profilePhotoPath!.substring(1)
          : user.profilePhotoPath!;
      if (path.startsWith('storage/')) {
        url = '$baseUrl/$path';
      } else {
        url = '$baseUrl/storage/$path';
      }
    } else if (user.profilePhotoUrl != null && user.profilePhotoUrl!.isNotEmpty) {
      url = user.profilePhotoUrl;
    }

    return Column(
      children: [
        Center(
          child: Stack(
            children: [
              CircleAvatar(
                radius: 60,
                backgroundColor: Colors.grey.shade300,
                backgroundImage: _selectedPhoto != null
                    ? FileImage(_selectedPhoto!)
                    : (url != null ? NetworkImage(url) : null),
                child: (_selectedPhoto == null && url == null)
                    ? const Icon(Icons.person, size: 50)
                    : null,
              ),
              Positioned(
                bottom: 0,
                right: 0,
                child: Container(
                  decoration: BoxDecoration(
                    color: Theme.of(context).primaryColor,
                    shape: BoxShape.circle,
                  ),
                  child: IconButton(
                    icon: const Icon(Icons.camera_alt, color: Colors.white),
                    onPressed: _isUpdatingPhoto ? null : _pickPhoto,
                  ),
                ),
              ),
            ],
          ),
        ),
        if (_selectedPhoto != null) ...[
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _isUpdatingPhoto ? null : _uploadPhoto,
            child: _isUpdatingPhoto
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Simpan Foto'),
          ),
        ],
      ],
    );
  }

  Widget _buildPasswordSection() {
    return Form(
      key: _passwordFormKey,
      child: Column(
        children: [
          TextFormField(
            controller: _currentPasswordController,
            obscureText: true,
            decoration: const InputDecoration(
              labelText: 'Password Lama',
              border: OutlineInputBorder(),
            ),
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Password lama wajib diisi';
              }
              return null;
            },
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _newPasswordController,
            obscureText: true,
            decoration: const InputDecoration(
              labelText: 'Password Baru',
              border: OutlineInputBorder(),
            ),
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Password baru wajib diisi';
              }
              if (value.length < 6) {
                return 'Password minimal 6 karakter';
              }
              return null;
            },
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _confirmPasswordController,
            obscureText: true,
            decoration: const InputDecoration(
              labelText: 'Konfirmasi Password Baru',
              border: OutlineInputBorder(),
            ),
            validator: (value) {
              if (value != _newPasswordController.text) {
                return 'Konfirmasi password tidak sesuai';
              }
              return null;
            },
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _isChangingPassword ? null : _changePassword,
              child: _isChangingPassword
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Ubah Password'),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _pickPhoto() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery, maxWidth: 800);
    if (pickedFile != null) {
      setState(() {
        _selectedPhoto = File(pickedFile.path);
        _errorMessage = null;
        _successMessage = null;
      });
    }
  }

  Future<void> _uploadPhoto() async {
    if (_selectedPhoto == null) return;

    setState(() {
      _isUpdatingPhoto = true;
      _errorMessage = null;
      _successMessage = null;
    });

    try {
      final service = ref.read(profileServiceProvider);
      await service.updateProfile(profilePhoto: _selectedPhoto);
      await ref.read(authNotifierProvider.notifier).refreshCurrentUser();
      setState(() {
        _selectedPhoto = null;
        _successMessage = 'Foto profil berhasil diperbarui.';
      });
    } on ApiException catch (e) {
      setState(() {
        _errorMessage = e.message;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Gagal mengupload foto. Coba lagi.';
      });
    } finally {
      setState(() {
        _isUpdatingPhoto = false;
      });
    }
  }

  Future<void> _changePassword() async {
    if (!_passwordFormKey.currentState!.validate()) return;

    setState(() {
      _isChangingPassword = true;
      _errorMessage = null;
      _successMessage = null;
    });

    try {
      final service = ref.read(profileServiceProvider);
      await service.changePassword(
        currentPassword: _currentPasswordController.text,
        newPassword: _newPasswordController.text,
        confirmPassword: _confirmPasswordController.text,
      );
      setState(() {
        _successMessage = 'Password berhasil diubah.';
        _currentPasswordController.clear();
        _newPasswordController.clear();
        _confirmPasswordController.clear();
      });
    } on ApiException catch (e) {
      setState(() {
        _errorMessage = e.message;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Gagal mengubah password. Coba lagi.';
      });
    } finally {
      setState(() {
        _isChangingPassword = false;
      });
    }
  }
}
