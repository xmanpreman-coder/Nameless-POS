import 'package:flutter/material.dart';

// This service will hold the application's global state,
// such as the connection status to the server.
class AppStateService extends ChangeNotifier {
  bool _isConnected = false;
  String? _serverIp;

  bool get isConnected => _isConnected;
  String? get serverIp => _serverIp;

  void connect(String ip) {
    _serverIp = ip;
    _isConnected = true;
    notifyListeners();
  }

  void disconnect() {
    _serverIp = null;
    _isConnected = false;
    notifyListeners();
  }
}
