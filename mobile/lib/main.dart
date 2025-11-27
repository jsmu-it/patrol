import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'routes/app_router.dart';
import 'ui/screens/auth/login_screen.dart';
import 'ui/screens/attendance/attendance_history_screen.dart';
import 'ui/screens/home/home_screen.dart';
import 'ui/screens/leave/leave_form_screen.dart';
import 'ui/screens/leave/leave_list_screen.dart';
import 'ui/screens/patrol/patrol_form_screen.dart';
import 'ui/screens/patrol/patrol_scan_screen.dart';
import 'ui/screens/profile/profile_screen.dart';
import 'ui/screens/splash/splash_screen.dart';

Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  if (!kIsWeb) {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
  }

  runApp(const ProviderScope(child: MyApp()));
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'JSMUGuard',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepPurple),
      ),
      initialRoute: AppRoutes.splash,
      routes: {
        AppRoutes.splash: (_) => const SplashScreen(),
        AppRoutes.login: (_) => const LoginScreen(),
        AppRoutes.home: (_) => const HomeScreen(),
        AppRoutes.attendanceHistory: (_) => const AttendanceHistoryScreen(),
        AppRoutes.patrolScan: (_) => const PatrolScanScreen(),
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
      },
    );
  }
}
