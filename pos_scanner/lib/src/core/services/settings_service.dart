import 'package:flutter/foundation.dart';
import 'package:pos_scanner/src/core/models/settings_model.dart';

class SettingsService extends ChangeNotifier {
  AppSettings _settings = AppSettings(
    connectionType: ConnectionType.wifi,
    wifiIpAddress: '192.168.1.100', // Default example IP
    wifiPort: 8000, // Default example port
  );

  AppSettings get settings => _settings;

  void updateSettings(AppSettings newSettings) {
    _settings = newSettings;
    notifyListeners();
  }
}
