import 'package:flutter/material.dart';
import 'package:flutter_blue_plus/flutter_blue_plus.dart';
import 'package:provider/provider.dart';
import 'package:pos_scanner/src/core/models/settings_model.dart';
import 'package:pos_scanner/src/core/services/settings_service.dart';
import 'package:pos_scanner/src/features/settings/bluetooth_discovery_page.dart';

class SettingsPage extends StatefulWidget {
  const SettingsPage({super.key});

  @override
  State<SettingsPage> createState() => _SettingsPageState();
}

class _SettingsPageState extends State<SettingsPage> {
  // Form controllers
  late final TextEditingController _ipController;
  late final TextEditingController _portController;
  
  // State
  late ConnectionType _selectedType;
  String? _selectedBluetoothDeviceId;
  String _selectedBluetoothDeviceName = 'Belum Dipilih';


  @override
  void initState() {
    super.initState();
    final settings = context.read<SettingsService>().settings;
    _ipController = TextEditingController(text: settings.wifiIpAddress);
    _portController = TextEditingController(text: settings.wifiPort?.toString());
    _selectedType = settings.connectionType;
    _selectedBluetoothDeviceId = settings.bluetoothDeviceId;
    // In a real app, you might want to fetch the name based on the ID here
  }

  void _saveSettings() {
    final settingsService = context.read<SettingsService>();
    final newSettings = AppSettings(
      connectionType: _selectedType,
      wifiIpAddress: _ipController.text,
      wifiPort: int.tryParse(_portController.text),
      bluetoothDeviceId: _selectedBluetoothDeviceId,
    );
    settingsService.updateSettings(newSettings);
    
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Pengaturan disimpan!')),
    );
    Navigator.of(context).pop();
  }
  
  Future<void> _openDiscoveryPage() async {
    final selectedDevice = await Navigator.of(context).push<BluetoothDevice>(
      MaterialPageRoute(builder: (context) => const BluetoothDiscoveryPage()),
    );

    if (selectedDevice != null) {
      setState(() {
        _selectedBluetoothDeviceId = selectedDevice.remoteId.toString();
        _selectedBluetoothDeviceName = selectedDevice.platformName;
      });
    }
  }

  @override
  void dispose() {
    _ipController.dispose();
    _portController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pengaturan Koneksi'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: ListView(
          children: [
            DropdownButtonFormField<ConnectionType>(
              initialValue: _selectedType,
              onChanged: (type) {
                if (type != null) {
                  setState(() { _selectedType = type; });
                }
              },
              items: const [
                DropdownMenuItem(
                  value: ConnectionType.wifi,
                  child: Text('Wi-Fi'),
                ),
                DropdownMenuItem(
                  value: ConnectionType.bluetooth,
                  child: Text('Bluetooth'),
                ),
              ],
              decoration: const InputDecoration(
                labelText: 'Tipe Koneksi',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 20),
            
            // --- Conditional UI based on Connection Type ---
            if (_selectedType == ConnectionType.wifi)
              _buildWifiSettings()
            else
              _buildBluetoothSettings(),
              
            const SizedBox(height: 32),
            ElevatedButton(
              onPressed: _saveSettings,
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
              child: const Text('Simpan'),
            )
          ],
        ),
      ),
    );
  }

  Widget _buildWifiSettings() {
    return Column(
      children: [
        TextFormField(
          controller: _ipController,
          decoration: const InputDecoration(
            labelText: 'Alamat IP Server POS',
            border: OutlineInputBorder(),
          ),
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
        ),
        const SizedBox(height: 16),
        TextFormField(
          controller: _portController,
          decoration: const InputDecoration(
            labelText: 'Port Server POS',
            border: OutlineInputBorder(),
          ),
          keyboardType: TextInputType.number,
        ),
      ],
    );
  }

  Widget _buildBluetoothSettings() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Perangkat Terpilih:', style: TextStyle(fontSize: 16)),
        ListTile(
          contentPadding: EdgeInsets.zero,
          title: Text(_selectedBluetoothDeviceName),
          subtitle: Text(_selectedBluetoothDeviceId ?? 'Tidak ada ID'),
          trailing: ElevatedButton(
            onPressed: _openDiscoveryPage,
            child: const Text('Cari'),
          ),
        ),
      ],
    );
  }
}
