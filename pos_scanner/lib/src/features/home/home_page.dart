import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:provider/provider.dart';
import 'package:pos_scanner/src/core/services/communication_service.dart';
import 'package:pos_scanner/src/core/services/settings_service.dart';
import 'package:pos_scanner/src/features/scanner/scanner_view.dart';
import 'package:pos_scanner/src/features/settings/settings_page.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  // State for camera permission
  bool _isPermissionGranted = false;

  // State for scanner logic
  String _scannedValue = 'Belum ada data';
  bool _isSending = false;
  String _sendStatus = '';
  Timer? _statusClearTimer;
  
  // State for RawKeyboardListener
  final FocusNode _keyboardFocusNode = FocusNode();
  String _keyboardInput = '';

  @override
  void initState() {
    super.initState();
    _requestCameraPermission();
    // Ensure the focus node is active to receive keyboard events
    FocusScope.of(context).requestFocus(_keyboardFocusNode);
  }

  @override
  void dispose() {
    _statusClearTimer?.cancel();
    _keyboardFocusNode.dispose();
    super.dispose();
  }

  Future<void> _requestCameraPermission() async {
    final status = await Permission.camera.request();
    setState(() {
      _isPermissionGranted = status == PermissionStatus.granted;
    });
  }

  void _handleBarcode(String barcodeValue) async {
    if (_isSending || barcodeValue.isEmpty) return;

    setState(() {
      _scannedValue = barcodeValue;
      _isSending = true;
      _sendStatus = 'Mengirim...';
    });

    final settings = context.read<SettingsService>().settings;
    final communicationService = CommunicationServiceFactory.getService(settings);
    final bool success = await communicationService.sendBarcode(barcodeValue);

    _statusClearTimer?.cancel();

    if (mounted) {
      setState(() {
        _isSending = false;
        _sendStatus = success ? 'Berhasil Terkirim' : 'Gagal Terkirim';
      });

      _statusClearTimer = Timer(const Duration(seconds: 3), () {
        if (mounted) {
          setState(() {
            _sendStatus = '';
          });
        }
      });
    }
  }

  void _onKeyEvent(KeyEvent event) {
    if (event is KeyDownEvent) {
      // Check for the enter key
      if (event.logicalKey == LogicalKeyboardKey.enter) {
        if (_keyboardInput.isNotEmpty) {
          _handleBarcode(_keyboardInput);
          // Clear the buffer
          _keyboardInput = '';
        }
      } else {
        // Append the character to our buffer
        if(event.character != null){
           _keyboardInput += event.character!;
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return KeyboardListener(
      focusNode: _keyboardFocusNode,
      onKeyEvent: _onKeyEvent,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('POS Scanner'),
          actions: [
            IconButton(
              icon: const Icon(Icons.settings),
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (context) => const SettingsPage()),
                );
              },
            ),
          ],
        ),
        body: Column(
          children: [
            Expanded(
              flex: 5,
              child: _isPermissionGranted
                  ? ScannerView(
                      onBarcodeDetected: _handleBarcode,
                      isSending: _isSending,
                    )
                  : _buildPermissionRequestView(),
            ),
            Expanded(
              flex: 1,
              child: _buildStatusView(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPermissionRequestView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text('Izin kamera diperlukan untuk memindai barcode.'),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _requestCameraPermission,
            child: const Text('Berikan Izin'),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            'Hasil Pindaian Terakhir: $_scannedValue',
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          if (_sendStatus.isNotEmpty)
            Text(
              _sendStatus,
              style: TextStyle(
                color: _sendStatus == 'Berhasil Terkirim' ? Colors.green : Colors.red,
                fontWeight: FontWeight.bold,
              ),
            ),
        ],
      ),
    );
  }
}
