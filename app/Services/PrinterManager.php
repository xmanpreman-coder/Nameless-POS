<?php

namespace App\Services;

use Exception;

class PrinterManager
{
    /**
     * Try to print raw bytes using mike42/escpos-php if available.
     * $connection can be an array like ['type' => 'network', 'host' => '192.168.1.100', 'port' => 9100]
     */
    public static function printRaw($content, array $connection)
    {
        if (!class_exists('Mike42\\Escpos\\Printer')) {
            throw new Exception('ESC/POS library not installed (mike42/escpos-php)');
        }

        // Lazy import classes
        if ($connection['type'] === 'network') {
            $connectorClass = '\\Mike42\\Escpos\\PrintConnectors\\NetworkPrintConnector';
            $connector = new $connectorClass($connection['host'], $connection['port'] ?? 9100);
        } elseif ($connection['type'] === 'windows') {
            $connectorClass = '\\Mike42\\Escpos\\PrintConnectors\\WindowsPrintConnector';
            $connector = new $connectorClass($connection['printerName']);
        } elseif ($connection['type'] === 'file') {
            $connectorClass = '\\Mike42\\Escpos\\PrintConnectors\\FilePrintConnector';
            $connector = new $connectorClass($connection['path']);
        } else {
            throw new Exception('Unsupported connector type: ' . ($connection['type'] ?? 'unknown'));
        }

        $printerClass = '\\Mike42\\Escpos\\Printer';
        $printer = new $printerClass($connector);

        // If content is string we write directly
        if (is_string($content)) {
            $printer->text($content);
        } else {
            // assume bytes or array of bytes
            $printer->write($content);
        }

        // Cut and close
        try {
            if (method_exists($printer, 'cut')) {
                $printer->cut();
            }
        } catch (\Throwable $e) {
            // ignore cut errors
        }

        $printer->close();

        return true;
    }
}
