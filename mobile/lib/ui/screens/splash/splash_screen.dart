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
    // Request permissions upfront
    await ref.read(permissionServiceProvider).requestInitialPermissions();

    // Load auth state
    await ref.read(authNotifierProvider.notifier).loadFromStorage();
    
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
