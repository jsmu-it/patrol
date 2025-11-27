import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

const _tokenKey = 'auth_token';

class SecureStorageService {
  SecureStorageService(this._storage);

  final FlutterSecureStorage _storage;

  Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }

  Future<String?> getToken() {
    return _storage.read(key: _tokenKey);
  }

  Future<void> clearToken() async {
    await _storage.delete(key: _tokenKey);
  }

  Future<String?> readRaw(String key) {
    return _storage.read(key: key);
  }

  Future<void> writeRaw(String key, String value) {
    return _storage.write(key: key, value: value);
  }
}

final flutterSecureStorageProvider = Provider<FlutterSecureStorage>((ref) {
  const iosOptions = IOSOptions();
  const androidOptions = AndroidOptions(encryptedSharedPreferences: true);

  return const FlutterSecureStorage(
    iOptions: iosOptions,
    aOptions: androidOptions,
  );
});

final secureStorageServiceProvider = Provider<SecureStorageService>((ref) {
  final storage = ref.watch(flutterSecureStorageProvider);
  return SecureStorageService(storage);
});
