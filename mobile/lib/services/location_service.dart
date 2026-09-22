import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';

class LocationException implements Exception {
  LocationException(this.message);

  final String message;

  @override
  String toString() => 'LocationException: $message';
}

class LocationService {
  Position? _lastPosition;
  DateTime? _lastPositionTime;

  /// Akurasi yang dianggap cukup baik untuk absensi. Begitu tercapai,
  /// pencarian dihentikan supaya petugas tidak menunggu lama.
  static const double _akurasiCukup = 25;

  /// Batas akurasi yang masih boleh dipakai. Di atas ini titiknya biasanya
  /// berasal dari menara seluler atau Wi-Fi, bukan satelit, dan bisa meleset
  /// ratusan meter — persis penyebab "sudah di lokasi tapi dibilang di luar".
  static const double _akurasiMaksimum = 100;

  /// Lama menunggu fix GPS yang layak.
  static const Duration _batasWaktu = Duration(seconds: 20);

  /// Umur maksimum titik yang boleh dipakai ulang tanpa mengukur lagi.
  static const Duration _umurCache = Duration(seconds: 15);

  /// Mengambil posisi terbaik yang bisa didapat dalam [_batasWaktu].
  ///
  /// Fix GPS pertama hampir selalu jelek: perangkat menjawab cepat memakai
  /// jaringan seluler, lalu memperbaiki dirinya begitu satelit terkunci.
  /// Karena itu aliran posisi diikuti beberapa detik dan yang dipakai adalah
  /// yang akurasinya paling kecil, bukan yang paling cepat datang.
  Future<Position> getCurrentPosition() async {
    await _pastikanIzin();

    // Titik yang baru saja diukur dan mutunya bagus boleh dipakai lagi.
    if (_lastPosition != null &&
        _lastPositionTime != null &&
        DateTime.now().difference(_lastPositionTime!) < _umurCache &&
        _lastPosition!.accuracy <= _akurasiCukup) {
      return _lastPosition!;
    }

    Position? terbaik;

    final selesai = Completer<void>();
    late final StreamSubscription<Position> langganan;

    langganan = Geolocator.getPositionStream(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.best,
        distanceFilter: 0,
      ),
    ).listen(
      (posisi) {
        if (terbaik == null || posisi.accuracy < terbaik!.accuracy) {
          terbaik = posisi;
        }
        // Sudah cukup baik — tidak perlu menahan petugas lebih lama.
        if (terbaik!.accuracy <= _akurasiCukup && !selesai.isCompleted) {
          selesai.complete();
        }
      },
      onError: (_) {
        if (!selesai.isCompleted) selesai.complete();
      },
      cancelOnError: false,
    );

    // Satu pembacaan langsung sebagai jaring pengaman bila aliran posisi
    // tidak pernah mengeluarkan apa pun (terjadi di sebagian perangkat).
    unawaited(
      Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.best,
          timeLimit: _batasWaktu,
        ),
      ).then((posisi) {
        if (terbaik == null || posisi.accuracy < terbaik!.accuracy) {
          terbaik = posisi;
        }
      }).catchError((_) => null),
    );

    try {
      await selesai.future.timeout(_batasWaktu);
    } on TimeoutException {
      // Waktu habis: pakai yang terbaik sejauh ini, kalau ada.
    } finally {
      await langganan.cancel();
    }

    final hasil = terbaik;

    if (hasil == null) {
      throw LocationException(
        'Gagal mendapatkan lokasi. Pastikan GPS aktif, lalu coba lagi di area terbuka.',
      );
    }

    if (hasil.accuracy > _akurasiMaksimum) {
      throw LocationException(
        'Sinyal GPS terlalu lemah (±${hasil.accuracy.round()} m). '
        'Coba keluar sebentar ke area terbuka, tunggu beberapa detik, lalu ulangi.',
      );
    }

    _lastPosition = hasil;
    _lastPositionTime = DateTime.now();

    return hasil;
  }

  /// Posisi untuk keperluan tampilan, mis. menghitung jarak di layar beranda.
  ///
  /// Boleh memakai titik lama supaya layar tidak terasa lambat. Jangan dipakai
  /// untuk mengirim absensi — untuk itu selalu pakai [getCurrentPosition].
  Future<Position> getQuickPosition() async {
    if (_lastPosition != null &&
        _lastPositionTime != null &&
        DateTime.now().difference(_lastPositionTime!) < const Duration(minutes: 2)) {
      return _lastPosition!;
    }

    await _pastikanIzin();

    try {
      final terakhir = await Geolocator.getLastKnownPosition();
      if (terakhir != null) {
        return terakhir;
      }
    } catch (_) {}

    return getCurrentPosition();
  }

  Future<void> _pastikanIzin() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw LocationException(
        'Layanan lokasi tidak aktif. Aktifkan GPS lalu coba lagi.',
      );
    }

    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }

    if (permission == LocationPermission.denied) {
      throw LocationException(
        'Akses lokasi ditolak. Berikan izin lokasi untuk dapat absen.',
      );
    }

    if (permission == LocationPermission.deniedForever) {
      throw LocationException(
        'Akses lokasi ditolak permanen. Aktifkan izin lokasi dari pengaturan.',
      );
    }
  }
}

final locationServiceProvider = Provider<LocationService>((ref) {
  return LocationService();
});
