enum ConnectionType { wifi, bluetooth }

class AppSettings {
  final ConnectionType connectionType;
  final String? wifiIpAddress;
  final int? wifiPort;
  final String? bluetoothDeviceId;

  AppSettings({
    this.connectionType = ConnectionType.wifi,
    this.wifiIpAddress,
    this.wifiPort,
    this.bluetoothDeviceId,
  });

  AppSettings copyWith({
    ConnectionType? connectionType,
    String? wifiIpAddress,
    int? wifiPort,
    String? bluetoothDeviceId,
  }) {
    return AppSettings(
      connectionType: connectionType ?? this.connectionType,
      wifiIpAddress: wifiIpAddress ?? this.wifiIpAddress,
      wifiPort: wifiPort ?? this.wifiPort,
      bluetoothDeviceId: bluetoothDeviceId ?? this.bluetoothDeviceId,
    );
  }
}
