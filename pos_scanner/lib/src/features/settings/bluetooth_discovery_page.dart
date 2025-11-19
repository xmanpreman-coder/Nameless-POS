import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_blue_plus/flutter_blue_plus.dart';
import 'package:permission_handler/permission_handler.dart';

class BluetoothDiscoveryPage extends StatefulWidget {
  const BluetoothDiscoveryPage({super.key});

  @override
  State<BluetoothDiscoveryPage> createState() => _BluetoothDiscoveryPageState();
}

class _BluetoothDiscoveryPageState extends State<BluetoothDiscoveryPage> {
  StreamSubscription<List<ScanResult>>? _scanSubscription;
  List<ScanResult> _scanResults = [];
  bool _isScanning = false;

  @override
  void initState() {
    super.initState();
    _startScan();
  }

  @override
  void dispose() {
    _stopScan();
    super.dispose();
  }

  Future<void> _startScan() async {
    // Bluetooth permissions are already in AndroidManifest, but good to double-check
    if (await Permission.bluetoothScan.request().isGranted &&
        await Permission.bluetoothConnect.request().isGranted) {
      
      setState(() { _isScanning = true; });

      _scanSubscription = FlutterBluePlus.scanResults.listen((results) {
        // We use a map to filter out devices with the same ID
        final uniqueResults = <String, ScanResult>{};
        for (var r in results) {
          // Filter devices with no name, unless you want to see them
          if (r.device.platformName.isNotEmpty) {
            uniqueResults[r.device.remoteId.toString()] = r;
          }
        }
        if (mounted) {
          setState(() {
            _scanResults = uniqueResults.values.toList();
          });
        }
      });

      await FlutterBluePlus.startScan(timeout: const Duration(seconds: 10));
      
      if (mounted) {
        setState(() { _isScanning = false; });
      }
    } else {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Izin Bluetooth Scan & Connect diperlukan.'),
      ));
    }
  }

  void _stopScan() {
    FlutterBluePlus.stopScan();
    _scanSubscription?.cancel();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Cari Perangkat Bluetooth'),
        actions: [
          if (_isScanning)
            const Padding(
              padding: EdgeInsets.only(right: 16.0),
              child: Center(child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white,))),
            )
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _startScan,
        child: ListView.builder(
          itemCount: _scanResults.length,
          itemBuilder: (context, index) {
            final result = _scanResults[index];
            return ListTile(
              title: Text(result.device.platformName),
              subtitle: Text(result.device.remoteId.toString()),
              leading: const Icon(Icons.bluetooth),
              onTap: () {
                // Stop scanning and return the selected device ID
                _stopScan();
                Navigator.of(context).pop(result.device);
              },
            );
          },
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _isScanning ? null : _startScan,
        backgroundColor: _isScanning ? Colors.grey : Theme.of(context).primaryColor,
        child: const Icon(Icons.refresh),
      ),
    );
  }
}
