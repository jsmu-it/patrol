import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../../routes/app_router.dart';
import 'patrol_form_screen.dart';

class PatrolScanScreen extends ConsumerStatefulWidget {
  const PatrolScanScreen({super.key});

  @override
  ConsumerState<PatrolScanScreen> createState() => _PatrolScanScreenState();
}

class _PatrolScanScreenState extends ConsumerState<PatrolScanScreen> {
  final MobileScannerController _controller = MobileScannerController();
  bool _handled = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan QR Patroli'),
      ),
      body: Stack(
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: (capture) {
              if (_handled) return;
              final barcodes = capture.barcodes;
              if (barcodes.isEmpty) return;

              final barcode = barcodes.first;
              final rawValue = barcode.rawValue;
              if (rawValue == null || rawValue.isEmpty) return;

              // Parse URL if it's a URL (satpamapp://checkpoint?code=XYZ)
              String code = rawValue;
              try {
                final uri = Uri.parse(rawValue);
                if (uri.queryParameters.containsKey('code')) {
                  code = uri.queryParameters['code']!;
                }
              } catch (_) {
                // Not a URI, use raw value
              }

              _handled = true;
              _controller.stop();

              Navigator.of(context).pushReplacementNamed(
                AppRoutes.patrolForm,
                arguments: PatrolFormArgs(
                  mode: PatrolFormMode.normal,
                  checkpointCode: code,
                ),
              );
            },
          ),
          Align(
            alignment: Alignment.bottomCenter,
            child: Container(
              padding: const EdgeInsets.all(16),
              color: Colors.black54,
              child:
                  Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        'Arahkan kamera ke QR code checkpoint.',
                        style: Theme.of(context)
                            .textTheme
                            .bodyMedium
                            ?.copyWith(color: Colors.white),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton(
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.redAccent,
                                side: const BorderSide(
                                  color: Colors.redAccent,
                                ),
                              ),
                              onPressed: () {
                                _handled = true;
                                _controller.stop();
                                Navigator.of(context)
                                    .pushReplacementNamed(
                                  AppRoutes.patrolForm,
                                  arguments: const PatrolFormArgs(
                                    mode: PatrolFormMode.sos,
                                  ),
                                );
                              },
                              child: const Text('Laporan Darurat (SOS)'),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: OutlinedButton(
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.orangeAccent,
                                side: const BorderSide(
                                  color: Colors.orangeAccent,
                                ),
                              ),
                              onPressed: () {
                                _handled = true;
                                _controller.stop();
                                Navigator.of(context)
                                    .pushReplacementNamed(
                                  AppRoutes.patrolForm,
                                  arguments: const PatrolFormArgs(
                                    mode: PatrolFormMode.incident,
                                  ),
                                );
                              },
                              child: const Text('Laporan Insiden'),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
              ),
          ),
        ],
      ),
    );
  }
}
