import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:intl/intl.dart';

import 'routes/app_router.dart';
import 'services/connectivity_service.dart';
import 'ui/screens/auth/login_screen.dart';
import 'ui/screens/attendance/attendance_history_screen.dart';
import 'ui/screens/home/home_screen.dart';
import 'ui/screens/leave/leave_form_screen.dart';
import 'ui/screens/leave/leave_list_screen.dart';
import 'ui/screens/patrol/patrol_form_screen.dart';
import 'ui/screens/patrol/patrol_history_screen.dart';
import 'ui/screens/patrol/patrol_scan_screen.dart';
import 'ui/screens/payroll/payroll_list_screen.dart';
import 'ui/screens/payroll/payroll_detail_screen.dart';
import 'ui/screens/profile/profile_screen.dart';
import 'ui/screens/splash/splash_screen.dart';

Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Indonesian Locale
  await initializeDateFormatting('id_ID', null);
  Intl.defaultLocale = 'id_ID';

  if (!kIsWeb) {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
  }

  runApp(const ProviderScope(child: MyApp()));
}

class MyApp extends ConsumerWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MaterialApp(
      title: 'JSMUGuard',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepPurple),
      ),
      builder: (context, child) {
        return Stack(
          children: [
            if (child != null) child,
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: _OfflineIndicator(ref: ref),
            ),
          ],
        );
      },
      initialRoute: AppRoutes.splash,
      routes: {
        AppRoutes.splash: (_) => const SplashScreen(),
        AppRoutes.login: (_) => const LoginScreen(),
        AppRoutes.home: (_) => const HomeScreen(),
        AppRoutes.attendanceHistory: (_) => const AttendanceHistoryScreen(),
        AppRoutes.patrolScan: (_) => const PatrolScanScreen(),
        AppRoutes.patrolHistory: (_) => const PatrolHistoryScreen(), // Added route
        AppRoutes.patrolForm: (context) {
          final args = ModalRoute.of(context)?.settings.arguments;
          if (args is PatrolFormArgs) {
            return PatrolFormScreen(args: args);
          }

          final checkpointCode = args is String ? args : '';
          return PatrolFormScreen(
            args: PatrolFormArgs(
              mode: PatrolFormMode.normal,
              checkpointCode: checkpointCode,
            ),
          );
        },
        AppRoutes.profile: (_) => const ProfileScreen(),
        AppRoutes.leaveList: (_) => const LeaveListScreen(),
        AppRoutes.leaveForm: (_) => const LeaveFormScreen(),
        AppRoutes.payrollList: (_) => const PayrollListScreen(),
        AppRoutes.payrollDetail: (context) {
          final args = ModalRoute.of(context)?.settings.arguments;
          final slipId = args is int ? args : 0;
          return PayrollDetailScreen(slipId: slipId);
        },
      },
    );
  }
}

class _OfflineIndicator extends StatelessWidget {
  const _OfflineIndicator({required this.ref});

  final WidgetRef ref;

  @override
  Widget build(BuildContext context) {
    return StreamBuilder<List<ConnectivityResult>>(
      stream: ref.read(connectivityServiceProvider).onConnectivityChanged,
      builder: (context, snapshot) {
        final results = snapshot.data;
        final isOffline =
            results != null && results.contains(ConnectivityResult.none);

        if (!isOffline) return const SizedBox.shrink();

        return Material(
          color: Colors.transparent,
          child: Container(
            color: Colors.red,
            padding: const EdgeInsets.all(4),
            child: const Text(
              'Anda sedang offline',
              style: TextStyle(color: Colors.white, fontSize: 12),
              textAlign: TextAlign.center,
            ),
          ),
        );
      },
    );
  }
}
