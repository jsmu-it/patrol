import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../config/app_config.dart';
import '../../../services/profile_service.dart';
import '../../../state/auth/auth_providers.dart';

class CVScreen extends ConsumerStatefulWidget {
  const CVScreen({super.key});

  @override
  ConsumerState<CVScreen> createState() => _CVScreenState();
}

class _CVScreenState extends ConsumerState<CVScreen> {
  Map<String, dynamic>? _cvData;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadCV();
  }

  Future<void> _loadCV() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final profileService = ref.read(profileServiceProvider);
      final data = await profileService.getCV();
      setState(() {
        _cvData = data['cv'] as Map<String, dynamic>?;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Curriculum Vitae'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline, size: 64, color: Colors.red.shade300),
                      const SizedBox(height: 16),
                      Text('Error: $_error', textAlign: TextAlign.center),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadCV,
                        child: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : _cvData == null
                  ? const Center(child: Text('Data CV tidak tersedia'))
                  : _buildCVContent(),
    );
  }

  Widget _buildCVContent() {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildHeader(),
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              children: [
                _buildSectionCard('Data Pribadi', _buildPersonalInfo()),
                const SizedBox(height: 12),
                _buildSectionCard('Postur & Seragam', _buildPhysicalInfo()),
                const SizedBox(height: 12),
                _buildSectionCard('Alamat KTP', _buildAddressInfo()),
                const SizedBox(height: 12),
                _buildSectionCard('Alamat Domisili', _buildDomicileInfo()),
                const SizedBox(height: 12),
                _buildSectionCard('Identitas', _buildDocumentsInfo()),
                const SizedBox(height: 12),
                _buildSectionCard('Kontak Darurat', _buildEmergencyContact()),
                const SizedBox(height: 12),
                _buildSectionCard('Pendidikan Akademis', _buildEducationInfo()),
                const SizedBox(height: 12),
                _buildSectionCard('Pendidikan Satpam', _buildSatpamInfo()),
                const SizedBox(height: 12),
                _buildSectionCard('Pengalaman Kerja', _buildExperienceInfo()),
                const SizedBox(height: 12),
                _buildSectionCard('Sertifikasi', _buildCertificationsInfo()),
                const SizedBox(height: 12),
                if (_hasSocialMedia())
                  _buildSectionCard('Media Sosial', _buildSocialMediaInfo()),
                const SizedBox(height: 24),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    final user = ref.watch(authNotifierProvider).user;
    final personal = _cvData?['personal'] as Map<String, dynamic>?;
    final contact = _cvData?['contact'] as Map<String, dynamic>?;
    final employment = _cvData?['employment'] as Map<String, dynamic>?;
    
    final apiBase = ref.watch(appConfigProvider).apiBaseUrl;
    final filesBaseUrl = apiBase.replaceFirst(RegExp(r'/api/?$'), '');

    String? photoUrl;
    if (user?.profilePhotoPath != null && user!.profilePhotoPath!.isNotEmpty) {
      final baseUrl = filesBaseUrl.endsWith('/')
          ? filesBaseUrl.substring(0, filesBaseUrl.length - 1)
          : filesBaseUrl;
      final path = user.profilePhotoPath!.startsWith('/')
          ? user.profilePhotoPath!.substring(1)
          : user.profilePhotoPath!;
      
      if (path.startsWith('storage/')) {
        photoUrl = '$baseUrl/$path';
      } else {
        photoUrl = '$baseUrl/storage/$path';
      }
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Profile Photo
              Container(
                width: 80,
                height: 100,
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.grey.shade300),
                  borderRadius: BorderRadius.circular(8),
                  color: Colors.grey.shade100,
                ),
                child: photoUrl != null
                    ? ClipRRect(
                        borderRadius: BorderRadius.circular(7),
                        child: Image.network(
                          photoUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) {
                            return Icon(Icons.person, size: 40, color: Colors.grey.shade400);
                          },
                        ),
                      )
                    : Icon(Icons.person, size: 40, color: Colors.grey.shade400),
              ),
              const SizedBox(width: 16),
              // User Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      personal?['name'] ?? user?.name ?? '-',
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1F2937),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${personal?['position'] ?? '-'} - ${personal?['division'] ?? '-'}',
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey.shade600,
                      ),
                    ),
                    const SizedBox(height: 12),
                    _buildHeaderInfoRow('NIP', personal?['nip'] ?? '-'),
                    _buildHeaderInfoRow('Project', user?.activeProjectName ?? '-'),
                    _buildHeaderInfoRow('Email', contact?['personal_email'] ?? user?.email ?? '-'),
                    _buildHeaderInfoRow('No. HP', contact?['phone_number'] ?? '-'),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 60,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey.shade600,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 12,
                color: Color(0xFF1F2937),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionCard(String title, Widget content) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
        side: BorderSide(color: Colors.grey.shade200),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: Color(0xFF1F2937),
              ),
            ),
            Container(
              margin: const EdgeInsets.only(top: 8, bottom: 12),
              height: 1,
              color: Colors.grey.shade200,
            ),
            content,
          ],
        ),
      ),
    );
  }

  Widget _buildDataRow(String label, String? value) {
    if (value == null || value.isEmpty || value == '-') {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 13,
                color: Color(0xFF1F2937),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPersonalInfo() {
    final personal = _cvData?['personal'] as Map<String, dynamic>?;
    if (personal == null) return const Text('Data tidak tersedia', style: TextStyle(fontSize: 13));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildDataRow('Tempat, Tgl Lahir', '${personal['birth_city'] ?? '-'}, ${personal['birth_date'] ?? '-'}'),
        _buildDataRow('Usia', personal['age'] != null ? '${personal['age']} tahun' : null),
        _buildDataRow('Jenis Kelamin', personal['gender']),
        _buildDataRow('Agama', personal['religion']),
        _buildDataRow('Golongan Darah', personal['blood_type']),
        _buildDataRow('Status', personal['marital_status']),
        _buildDataRow('Jumlah Anak', personal['children_count']?.toString() ?? '0'),
        _buildDataRow('Nama Ibu', personal['mother_name']),
        _buildDataRow('NIK', personal['ktp_number']),
      ],
    );
  }

  Widget _buildPhysicalInfo() {
    final physical = _cvData?['physical'] as Map<String, dynamic>?;
    if (physical == null) return const Text('Data tidak tersedia', style: TextStyle(fontSize: 13));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildDataRow('Tinggi Badan', physical['height_cm'] != null ? '${physical['height_cm']} cm' : null),
        _buildDataRow('Berat Badan', physical['weight_kg'] != null ? '${physical['weight_kg']} kg' : null),
        _buildDataRow('Ukuran Baju', physical['uniform_shirt_size']),
        _buildDataRow('Ukuran Celana', physical['uniform_pants_size']),
        _buildDataRow('Ukuran Sepatu', physical['uniform_shoes_size']),
      ],
    );
  }

  Widget _buildAddressInfo() {
    final address = _cvData?['address'] as Map<String, dynamic>?;
    if (address == null) return const Text('Data tidak tersedia', style: TextStyle(fontSize: 13));

    final street = address['street'] ?? '';
    final rt = address['rt'] ?? '';
    final rw = address['rw'] ?? '';
    final subdistrict = address['subdistrict'] ?? '';
    final district = address['district'] ?? '';
    final regency = address['regency'] ?? '';
    final province = address['province'] ?? '';
    final postal = address['postal_code'] ?? '';

    String fullAddress = street;
    if (rt.isNotEmpty || rw.isNotEmpty) {
      fullAddress += '\nRT $rt / RW $rw';
    }
    if (subdistrict.isNotEmpty) fullAddress += '\n$subdistrict, $district, $regency';
    if (province.isNotEmpty) fullAddress += '\n$province $postal';

    return Text(
      fullAddress.trim().isEmpty ? 'Data tidak tersedia' : fullAddress.trim(),
      style: const TextStyle(fontSize: 13, color: Color(0xFF1F2937)),
    );
  }

  Widget _buildDomicileInfo() {
    final domicile = _cvData?['domicile'] as Map<String, dynamic>?;
    if (domicile == null) return const Text('Data tidak tersedia', style: TextStyle(fontSize: 13));

    final street = domicile['street'] ?? '';
    final rt = domicile['rt'] ?? '';
    final rw = domicile['rw'] ?? '';
    final subdistrict = domicile['subdistrict'] ?? '';
    final district = domicile['district'] ?? '';
    final regency = domicile['regency'] ?? '';
    final province = domicile['province'] ?? '';
    final postal = domicile['postal_code'] ?? '';

    String fullAddress = street;
    if (rt.isNotEmpty || rw.isNotEmpty) {
      fullAddress += '\nRT $rt / RW $rw';
    }
    if (subdistrict.isNotEmpty) fullAddress += '\n$subdistrict, $district, $regency';
    if (province.isNotEmpty) fullAddress += '\n$province $postal';

    return Text(
      fullAddress.trim().isEmpty ? 'Data tidak tersedia' : fullAddress.trim(),
      style: const TextStyle(fontSize: 13, color: Color(0xFF1F2937)),
    );
  }

  Widget _buildDocumentsInfo() {
    final documents = _cvData?['documents'] as Map<String, dynamic>?;
    if (documents == null) return const Text('Data tidak tersedia', style: TextStyle(fontSize: 13));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildDataRow('No. KK', documents['kk_number']),
        _buildDataRow('NPWP', documents['npwp']),
        _buildDataRow('SIM A', documents['sim_a_number']),
        _buildDataRow('SIM C', documents['sim_c_number']),
        _buildDataRow('BPJS TK', documents['bpjs_tk_number']),
        _buildDataRow('BPJS Kesehatan', documents['bpjs_kes_number']),
      ],
    );
  }

  Widget _buildEmergencyContact() {
    final contact = _cvData?['contact'] as Map<String, dynamic>?;
    if (contact == null) return const Text('Data tidak tersedia', style: TextStyle(fontSize: 13));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildDataRow('Nama', contact['emergency_name']),
        _buildDataRow('No. Telp', contact['emergency_phone']),
        _buildDataRow('Hubungan', contact['emergency_relation']),
      ],
    );
  }

  Widget _buildEducationInfo() {
    final education = _cvData?['education'] as Map<String, dynamic>?;
    if (education == null) return const Text('Data tidak tersedia', style: TextStyle(fontSize: 13));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildDataRow('Tingkat', education['level']),
        _buildDataRow('Sekolah/Universitas', education['school_name']),
        _buildDataRow('Jurusan', education['major']),
        _buildDataRow('Kota', education['city']),
        _buildDataRow('Tahun Lulus', education['graduation_year']),
      ],
    );
  }

  Widget _buildSatpamInfo() {
    final satpam = _cvData?['satpam'] as Map<String, dynamic>?;
    if (satpam == null) return const Text('Data tidak tersedia', style: TextStyle(fontSize: 13));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildDataRow('Kualifikasi', satpam['qualification']),
        _buildDataRow('Instansi', satpam['training_institution']),
        _buildDataRow('Lokasi Diklat', satpam['training_location']),
        _buildDataRow('Tanggal', satpam['training_date']),
        _buildDataRow('No. KTA', satpam['kta_number']),
        _buildDataRow('No. Ijazah', satpam['certificate_number']),
      ],
    );
  }

  Widget _buildExperienceInfo() {
    final experiences = _cvData?['experience'] as List<dynamic>?;
    if (experiences == null || experiences.isEmpty) {
      return const Text('Tidak ada data pengalaman kerja.', style: TextStyle(fontSize: 13, color: Colors.grey));
    }

    final validExperiences = experiences
        .where((exp) => exp['position'] != null || exp['company'] != null)
        .toList();

    if (validExperiences.isEmpty) {
      return const Text('Tidak ada data pengalaman kerja.', style: TextStyle(fontSize: 13, color: Colors.grey));
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: validExperiences.map((exp) {
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.only(left: 12),
          decoration: BoxDecoration(
            border: Border(
              left: BorderSide(color: Colors.blue.shade500, width: 3),
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                exp['position'] ?? '-',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF1F2937),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                '${exp['company'] ?? '-'}, ${exp['city'] ?? '-'} (${exp['year'] ?? '-'})',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade600,
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildCertificationsInfo() {
    final certifications = _cvData?['certifications'] as List<dynamic>?;
    if (certifications == null || certifications.isEmpty) {
      return const Text('Tidak ada data sertifikasi.', style: TextStyle(fontSize: 13, color: Colors.grey));
    }

    final validCertifications = certifications
        .where((cert) => cert['training'] != null || cert['organizer'] != null)
        .toList();

    if (validCertifications.isEmpty) {
      return const Text('Tidak ada data sertifikasi.', style: TextStyle(fontSize: 13, color: Colors.grey));
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: validCertifications.map((cert) {
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.only(left: 12),
          decoration: BoxDecoration(
            border: Border(
              left: BorderSide(color: Colors.green.shade500, width: 3),
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                cert['training'] ?? '-',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF1F2937),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                '${cert['organizer'] ?? '-'}, ${cert['city'] ?? '-'} (${cert['date'] ?? '-'})',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade600,
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  bool _hasSocialMedia() {
    final social = _cvData?['social_media'] as Map<String, dynamic>?;
    if (social == null) return false;
    
    return social['instagram']?.toString().isNotEmpty == true ||
           social['facebook']?.toString().isNotEmpty == true ||
           social['twitter']?.toString().isNotEmpty == true ||
           social['tiktok']?.toString().isNotEmpty == true ||
           social['linkedin']?.toString().isNotEmpty == true ||
           social['youtube']?.toString().isNotEmpty == true;
  }

  Widget _buildSocialMediaInfo() {
    final social = _cvData?['social_media'] as Map<String, dynamic>?;
    if (social == null) return const Text('Data tidak tersedia', style: TextStyle(fontSize: 13));

    return Wrap(
      spacing: 12,
      runSpacing: 8,
      children: [
        if (social['instagram']?.toString().isNotEmpty == true)
          _buildSocialChip('IG', social['instagram']),
        if (social['facebook']?.toString().isNotEmpty == true)
          _buildSocialChip('FB', social['facebook']),
        if (social['twitter']?.toString().isNotEmpty == true)
          _buildSocialChip('X', social['twitter']),
        if (social['tiktok']?.toString().isNotEmpty == true)
          _buildSocialChip('TikTok', social['tiktok']),
        if (social['linkedin']?.toString().isNotEmpty == true)
          _buildSocialChip('LinkedIn', social['linkedin']),
        if (social['youtube']?.toString().isNotEmpty == true)
          _buildSocialChip('YouTube', social['youtube']),
      ],
    );
  }

  Widget _buildSocialChip(String platform, String? handle) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Text(
        '$platform: $handle',
        style: TextStyle(
          fontSize: 12,
          color: Colors.grey.shade700,
        ),
      ),
    );
  }
}
