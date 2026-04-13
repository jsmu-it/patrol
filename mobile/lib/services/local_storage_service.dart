import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// LocalStorageService - Uses SharedPreferences for non-sensitive data storage.
/// This is more stable than SecureStorage which uses Android KeyStore that can
/// become corrupted on some devices.
///
/// Use this for:
/// - Offline queue data
/// - Cached app data
/// - Non-sensitive preferences
///
/// Use SecureStorageService for:
/// - Auth tokens
/// - Sensitive credentials
class LocalStorageService {
  LocalStorageService(this._prefs);

  final SharedPreferences _prefs;

  Future<String?> getString(String key) async {
    return _prefs.getString(key);
  }

  Future<void> setString(String key, String value) async {
    await _prefs.setString(key, value);
  }

  Future<void> remove(String key) async {
    await _prefs.remove(key);
  }

  Future<void> clear() async {
    await _prefs.clear();
  }
}

/// Provider that initializes SharedPreferences asynchronously
final sharedPreferencesProvider = FutureProvider<SharedPreferences>((ref) async {
  return await SharedPreferences.getInstance();
});

/// Provider for LocalStorageService
/// Note: This requires SharedPreferences to be initialized first
final localStorageServiceProvider = Provider<LocalStorageService>((ref) {
  final prefsAsync = ref.watch(sharedPreferencesProvider);
  
  // This will throw if SharedPreferences hasn't been initialized yet
  // The app should ensure SharedPreferences is loaded before using this
  return prefsAsync.when(
    data: (prefs) => LocalStorageService(prefs),
    loading: () => throw Exception('SharedPreferences not yet initialized'),
    error: (e, _) => throw Exception('Failed to initialize SharedPreferences: $e'),
  );
});

/// Alternative: A synchronous provider that can be overridden after initialization
/// Use this pattern for better control over initialization timing
final localStorageServiceOverrideProvider = Provider<LocalStorageService?>((ref) {
  return null; // Will be overridden in main.dart after initialization
});
