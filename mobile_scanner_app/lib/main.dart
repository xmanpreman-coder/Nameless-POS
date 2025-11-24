import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:pos_scanner/core/services/app_state_service.dart';
import 'package:pos_scanner/features/server_pairing/server_pairing_page.dart';

void main() {
  runApp(
    // Using MultiProvider for scalability, even with one provider initially.
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AppStateService()),
      ],
      child: const MyApp(),
    ),
  );
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'POS Scanner',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: Colors.indigo,
          brightness: Brightness.dark,
        ),
        useMaterial3: true,
      ),
      // The app starts at the ServerPairingPage.
      home: const ServerPairingPage(),
    );
  }
}