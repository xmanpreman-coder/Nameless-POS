import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class ScannerView extends StatelessWidget {
  final Function(String barcode) onBarcodeDetected;
  final bool isSending;

  const ScannerView({
    super.key,
    required this.onBarcodeDetected,
    this.isSending = false,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.center,
      children: [
        MobileScanner(
          onDetect: (capture) {
            if (isSending) return; // Ignore new scans if one is already being sent

            final List<Barcode> barcodes = capture.barcodes;
            if (barcodes.isNotEmpty && barcodes.first.rawValue != null) {
              onBarcodeDetected(barcodes.first.rawValue!);
            }
          },
        ),
        // Overlay
                  Container(
                    width: 250,
                    height: 250,
                    decoration: BoxDecoration(
                      border: Border.all(
                        color: isSending ? Colors.orange.withAlpha(178) : Colors.green.withAlpha(178),
                        width: 4,
                      ),
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),      ],
    );
  }
}
