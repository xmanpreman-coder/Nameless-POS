import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_blue_plus/flutter_blue_plus.dart';
import 'package:http/http.dart' as http;
import 'package:pos_scanner/src/core/models/settings_model.dart';

// Abstract base class
abstract class CommunicationService {
  Future<bool> sendBarcode(String barcode);
}

// --- Wi-Fi Implementation ---
class WifiCommunicationService implements CommunicationService {
  final AppSettings _settings;

  WifiCommunicationService(this._settings);

  @override
  Future<bool> sendBarcode(String barcode) async {
    if (_settings.wifiIpAddress == null || _settings.wifiPort == null) {
      debugPrint('Error: Wi-Fi IP Address or Port is not configured.');
      return false;
    }
    final url = Uri.http(
      '${_settings.wifiIpAddress}:${_settings.wifiPort}',
      '/api/v1/scan',
    );
    try {
      final response = await http.post(
        url,
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'barcode': barcode}),
      ).timeout(const Duration(seconds: 5));

      if (response.statusCode == 200) {
        return true;
      }
      return false;
    } catch (e) {
      debugPrint('An unexpected error occurred during Wi-Fi send: $e');
      return false;
    }
  }
}

// --- Bluetooth Implementation ---
class BluetoothCommunicationService implements CommunicationService {
  final AppSettings _settings;

  // UUIDs ini HARUS SAMA dengan yang didefinisikan di pos_bridge_server.py
  final Guid _serviceUuid = Guid("A07498CA-AD5B-474E-940D-16F1FBE7E8CD");
  final Guid _characteristicUuid = Guid("51FF12BB-3ED8-46E5-B4F9-D64E2FEC021B");

  BluetoothCommunicationService(this._settings);

  @override
  Future<bool> sendBarcode(String barcode) async {
    if (_settings.bluetoothDeviceId == null) {
      debugPrint('Error: Bluetooth device ID is not configured.');
      return false;
    }

    final device = BluetoothDevice.fromId(_settings.bluetoothDeviceId!);
    
    try {
      await device.connect(timeout: const Duration(seconds: 10));
      debugPrint('Connected to device: ${device.remoteId}');

      // ignore: missing_required_argument
      List<BluetoothService> services = await device.discoverServices();
      for (var service in services) {
        if (service.uuid == _serviceUuid) {
          for (var characteristic in service.characteristics) {
            if (characteristic.uuid == _characteristicUuid) {
              // Write the barcode string encoded as UTF-8 bytes
              await characteristic.write(utf8.encode(barcode));
              debugPrint('Successfully sent "$barcode" to characteristic.');
              await device.disconnect();
              return true;
            }
          }
        }
      }
      debugPrint('Error: Target service/characteristic not found.');
      await device.disconnect();
      return false;
    } on TimeoutException {
       debugPrint('Error: Connection to bluetooth device timed out.');
       return false;
    } catch (e) {
      debugPrint('An unexpected error occurred during Bluetooth send: $e');
      // Ensure we disconnect on error
      if (device.isConnected) {
        await device.disconnect();
      }
      return false;
    }
  }
}


// --- Factory ---
class CommunicationServiceFactory {
  static CommunicationService getService(AppSettings settings) {
    switch (settings.connectionType) {
      case ConnectionType.wifi:
        return WifiCommunicationService(settings);
      case ConnectionType.bluetooth:
        return BluetoothCommunicationService(settings);
    }
  }
}
