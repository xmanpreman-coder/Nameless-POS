import 'dart:async';
import 'package:flutter/material.dart';
import 'package:nsd/nsd.dart';
import 'package:provider/provider.dart';
import 'package:pos_scanner/core/services/app_state_service.dart';
import 'package:pos_scanner/features/sections/section_list_page.dart';

class ServerPairingPage extends StatefulWidget {
  const ServerPairingPage({super.key});

  @override
  State<ServerPairingPage> createState() => _ServerPairingPageState();
}

class _ServerPairingPageState extends State<ServerPairingPage> {
  final List<Service> _discoveredServices = [];
  bool _isScanning = true;
  StreamSubscription<Service>? _subscription;

  @override
  void initState() {
    super.initState();
    _startDiscovery();
  }

  @override
  void dispose() {
    _stopDiscovery();
    super.dispose();
  }

  Future<void> _startDiscovery() async {
    setState(() {
      _discoveredServices.clear();
      _isScanning = true;
    });

    // The service type should match what the Laravel backend advertises.
    // We'll use '_http._tcp' as a common standard for now.
    _subscription = await startDiscovery('_http._tcp', ipLookupType: IpLookupType.any);
    _subscription!.listen(
      (service) {
        // We only care about services that have a resolvable IP address.
        if (service.host != null) {
          setState(() {
            // Avoid adding duplicates.
            _discoveredServices.removeWhere((s) => s.name == service.name);
            _discoveredServices.add(service);
          });
        }
      },
      onDone: () => setState(() => _isScanning = false),
      onError: (e) {
        setState(() => _isScanning = false);
        // Optionally, show an error message
      },
    );
  }

  Future<void> _stopDiscovery() async {
    await _subscription?.cancel();
    await stopDiscovery();
    setState(() => _isScanning = false);
  }

  void _connectToServer(BuildContext context, String host) {
    context.read<AppStateService>().connect(host);
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const SectionListPage()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pair with Server'),
        actions: [
          if (_isScanning)
            const Padding(
              padding: EdgeInsets.only(right: 16.0),
              child: SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2.0),
              ),
            ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: RefreshIndicator(
              onRefresh: _startDiscovery,
              child: _buildServiceList(),
            ),
          ),
          _buildManualPairingButtons(),
        ],
      ),
    );
  }

  Widget _buildServiceList() {
    if (_isScanning && _discoveredServices.isEmpty) {
      return const Center(child: Text('Mencari server di jaringan...'));
    }
    if (_discoveredServices.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: Text(
            'Tidak ada server yang ditemukan. Tarik ke bawah untuk mencari ulang, atau gunakan metode pairing manual.',
            textAlign: TextAlign.center,
          ),
        ),
      );
    }
    return ListView.builder(
      itemCount: _discoveredServices.length,
      itemBuilder: (context, index) {
        final service = _discoveredServices[index];
        return ListTile(
          title: Text(service.name ?? 'Unknown Service'),
          subtitle: Text(service.host ?? 'N/A'),
          trailing: const Icon(Icons.chevron_right),
          onTap: () => _connectToServer(context, service.host!),
        );
      },
    );
  }

  Widget _buildManualPairingButtons() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        children: [
          ElevatedButton.icon(
            onPressed: () {
              // TODO: Navigate to QR scanner page
            },
            icon: const Icon(Icons.qr_code_scanner),
            label: const Text('Pair dengan QR Code'),
            style: ElevatedButton.styleFrom(
              minimumSize: const Size(double.infinity, 44),
            ),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: () {
              // TODO: Show manual IP input dialog
            },
            icon: const Icon(Icons.text_fields),
            label: const Text('Input Manual'),
            style: OutlinedButton.styleFrom(
              minimumSize: const Size(double.infinity, 44),
            ),
          ),
        ],
      ),
    );
  }
}