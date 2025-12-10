import 'dart:async';
import 'dart:developer' as developer;
import 'dart:io';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class ConnectivityService {
  ConnectivityService() {
    _init();
  }

  bool _lastKnownOnline = true;
  DateTime? _lastCheck;

  void _init() {
    Connectivity().onConnectivityChanged.listen((results) {
      developer.log('Connectivity changed: $results', name: 'ConnectivityService');
      if (results.contains(ConnectivityResult.none)) {
        _lastKnownOnline = false;
      } else {
        _lastKnownOnline = true;
      }
    });
  }

  /// Quick check - just network type, no ping (instant)
  Future<bool> hasNetworkType() async {
    final results = await Connectivity().checkConnectivity();
    developer.log('checkConnectivity results: $results', name: 'ConnectivityService');
    
    // If results is empty or only contains 'none', return false
    if (results.isEmpty) {
      developer.log('No connectivity results, assuming online', name: 'ConnectivityService');
      return true; // Assume online if can't determine
    }
    
    if (results.contains(ConnectivityResult.none) && results.length == 1) {
      return false;
    }
    
    // Return true if has any network type
    return true;
  }

  /// Fast online check with optional ping (max 2 seconds)
  Future<bool> isOnline({bool skipPing = false}) async {
    // First check network type (instant)
    final hasNetwork = await hasNetworkType();
    if (!hasNetwork) {
      _lastKnownOnline = false;
      return false;
    }

    // Use cached result if checked within last 5 seconds
    if (_lastCheck != null && 
        DateTime.now().difference(_lastCheck!) < const Duration(seconds: 5)) {
      return _lastKnownOnline;
    }

    if (skipPing) {
      return true;
    }

    // Quick ping to verify actual internet (max 2 seconds)
    try {
      final result = await InternetAddress.lookup('google.com')
          .timeout(const Duration(seconds: 2));
      _lastKnownOnline = result.isNotEmpty && result[0].rawAddress.isNotEmpty;
    } on SocketException catch (_) {
      _lastKnownOnline = false;
    } on TimeoutException catch (_) {
      _lastKnownOnline = false;
    } catch (_) {
      // If ping fails, assume offline for fail-fast behavior
      _lastKnownOnline = false;
    }

    _lastCheck = DateTime.now();
    return _lastKnownOnline;
  }

  Stream<List<ConnectivityResult>> get onConnectivityChanged =>
      Connectivity().onConnectivityChanged;
}

final connectivityServiceProvider = Provider<ConnectivityService>((ref) {
  return ConnectivityService();
});
