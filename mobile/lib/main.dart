import 'dart:async';
import 'dart:convert';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'routes/app_router.dart';
import 'services/connectivity_service.dart';
import 'services/local_storage_service.dart';
import 'services/notification_service.dart';
import 'services/notification_storage.dart';
import 'models/notification.dart';
import 'ui/screens/auth/login_screen.dart';
import 'ui/screens/attendance/attendance_history_screen.dart';
import 'ui/screens/home/home_screen.dart';
import 'ui/screens/leave/admin_leave_approval_screen.dart';
import 'ui/screens/leave/leave_form_screen.dart';
import 'ui/screens/leave/leave_list_screen.dart';
import 'ui/screens/notifications/notifications_screen.dart';
import 'ui/screens/patrol/patrol_form_screen.dart';
import 'ui/screens/patrol/patrol_history_screen.dart';
import 'ui/screens/patrol/patrol_scan_screen.dart';
import 'ui/screens/payroll/payroll_list_screen.dart';
import 'ui/screens/payroll/payroll_detail_screen.dart';
import 'ui/screens/profile/profile_screen.dart';
import 'ui/screens/profile/edit_profile_screen.dart';
import 'ui/screens/splash/splash_screen.dart';

/// Handle background messages - must be top-level function
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  
  // Save notification to local storage when app is in background/terminated
  final notification = message.notification;
  if (notification != null) {
    try {
      final prefs = await SharedPreferences.getInstance();
      final storage = NotificationStorage(prefs);
      
      final appNotification = AppNotification(
        id: '${DateTime.now().millisecondsSinceEpoch}_${message.data.hashCode}',
        title: notification.title ?? 'Notifikasi',
        body: notification.body ?? '',
        data: message.data,
        receivedAt: DateTime.now(),
        isRead: false,
      );
      
      await storage.saveNotification(appNotification);
    } catch (e) {
      // Ignore errors to not disrupt background processing
    }
  }
}

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Indonesian Locale
  await initializeDateFormatting('id_ID', null);
  Intl.defaultLocale = 'id_ID';

  // Initialize SharedPreferences early for LocalStorageService
  final sharedPreferences = await SharedPreferences.getInstance();

  if (!kIsWeb) {
    try {
      await Firebase.initializeApp()
          .timeout(const Duration(seconds: 10), onTimeout: () {
        // ignore: avoid_print
        print('[Main] Firebase init timeout, continuing without Firebase...');
        throw TimeoutException('Firebase init timeout');
      });
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
    } catch (e) {
      // ignore: avoid_print
      print('[Main] Firebase init error: $e');
      // Continue without Firebase - app should still work for basic features
    }
  }

  runApp(
    ProviderScope(
      overrides: [
        // Override localStorageServiceProvider with initialized SharedPreferences
        localStorageServiceProvider.overrideWithValue(
          LocalStorageService(sharedPreferences),
        ),
        // Override notificationStorageProvider with initialized SharedPreferences
        notificationStorageProvider.overrideWithValue(
          NotificationStorage(sharedPreferences),
        ),
      ],
      child: const MyApp(),
    ),
  );
}

class MyApp extends ConsumerWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MaterialApp(
      navigatorKey: navigatorKey,
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
        '/edit-profile': (_) => const EditProfileScreen(),
        AppRoutes.leaveList: (_) => const LeaveListScreen(),
        AppRoutes.leaveForm: (_) => const LeaveFormScreen(),
        AppRoutes.payrollList: (_) => const PayrollListScreen(),
        AppRoutes.payrollDetail: (context) {
          final args = ModalRoute.of(context)?.settings.arguments;
          final slipId = args is int ? args : 0;
          return PayrollDetailScreen(slipId: slipId);
        },
        // Route for push notification tap
        '/payroll-detail': (context) {
          final args = ModalRoute.of(context)?.settings.arguments;
          final slipId = args is int ? args : 0;
          return PayrollDetailScreen(slipId: slipId);
        },
        // Admin leave approvals
        AppRoutes.adminLeaveApprovals: (_) => const AdminLeaveApprovalScreen(),
        // Notifications
        AppRoutes.notifications: (_) => const NotificationsScreen(),
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
