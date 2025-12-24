import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../routes/app_router.dart';
import '../../../services/permission_service.dart';
import '../../../state/auth/auth_providers.dart';
import '../../../state/auth/auth_state.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    try {
      // Run these in parallel to reduce blocking time
      await Future.wait([
        // Request permissions (optimized to only ask if needed)
        ref
            .read(permissionServiceProvider)
            .requestInitialPermissions()
            .timeout(const Duration(seconds: 10), onTimeout: () {
          // ignore: avoid_print
          print('[Splash] Permission request timeout, continuing...');
        }),

        // Load auth state
        ref.read(authNotifierProvider.notifier).loadFromStorage(),
      ]);

    } catch (e, stack) {
      // ignore: avoid_print
      print('[Splash] Init error: $e');
      // ignore: avoid_print
      print(stack);
    }

    if (!mounted) return;

    final authState = ref.read(authNotifierProvider);
    if (authState.status == AuthStatus.authenticated) {
      Navigator.of(context).pushReplacementNamed(AppRoutes.home);
    } else {
      Navigator.of(context).pushReplacementNamed(AppRoutes.login);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0C6CF2),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
             Container(
               padding: const EdgeInsets.all(20),
               decoration: const BoxDecoration(
                 color: Colors.white,
                 shape: BoxShape.circle,
               ),
               child: Image.asset('assets/images/logo.png', height: 64),
             ),
             const SizedBox(height: 24),
             const CircularProgressIndicator(color: Colors.white),
          ],
        ),
      ),
    );
  }
}
