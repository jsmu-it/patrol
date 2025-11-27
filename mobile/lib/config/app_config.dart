import 'package:flutter_riverpod/flutter_riverpod.dart';

class AppConfig {
  const AppConfig({required this.apiBaseUrl});

  final String apiBaseUrl;
}

//final appConfigProvider = Provider<AppConfig>((ref) {
  //return const AppConfig(
    //apiBaseUrl: 'http://10.0.2.2:8000/api',
  //);
//});
final appConfigProvider = Provider<AppConfig>((ref) {
       return const AppConfig(
         apiBaseUrl: 'http://192.168.0.75:8000/api', // ganti dengan IP PC Anda
       );
     });
