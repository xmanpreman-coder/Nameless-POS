# -----------------------------------------------------------------------------
# POS Bridge Server
#
# Author: Gemini
# Deskripsi:
# Server ini berfungsi sebagai jembatan antara aplikasi scanner Flutter
# dengan aplikasi POS di komputer. Ia menerima data barcode melalui
# Wi-Fi (HTTP) atau Bluetooth LE, lalu mensimulasikan input keyboard
# seolah-olah barcode di-scan oleh scanner fisik.
#
# File yang dibutuhkan:
# 1. Python 3 (https://www.python.org/downloads/)
# 2. Library tambahan (install dengan 'pip install -r requirements.txt')
#
# Cara Menjalankan:
# 1. Buka terminal atau Command Prompt.
# 2. Masuk ke direktori tempat file ini disimpan.
# 3. Install library: pip install -r requirements.txt
# 4. Jalankan server: python pos_bridge_server.py
# 5. Server akan berjalan dan siap menerima koneksi.
#    Pastikan firewall Anda mengizinkan koneksi masuk pada port yang digunakan.
# -----------------------------------------------------------------------------

import asyncio
import json
import threading
from flask import Flask, request, jsonify
from bleak import BleakServer, BleakGATTCharacteristic
from pynput.keyboard import Controller, Key

# --- PENGATURAN ---
HTTP_PORT = 5000  # Port untuk koneksi Wi-Fi (HTTP)

# --- PENTING: UUID untuk Bluetooth LE ---
# UUID ini HARUS SAMA dengan yang akan Anda gunakan di aplikasi Flutter.
# Anda bisa menggunakan UUID generator online untuk membuat UUID Anda sendiri.
# Service UUID: Kelompok dari characteristic
SERVICE_UUID = "A07498CA-AD5B-474E-940D-16F1FBE7E8CD"
# Characteristic UUID: "Saluran" untuk menulis data barcode
BARCODE_CHAR_UUID = "51FF12BB-3ED8-46E5-B4F9-D64E2FEC021B"


# -----------------------------------------------------------------------------
# 1. LOGIKA KEYBOARD SIMULATOR
# -----------------------------------------------------------------------------
# Bagian ini bertanggung jawab untuk "mengetik" di komputer.
# -----------------------------------------------------------------------------
keyboard = Controller()

def type_barcode(barcode_text: str):
    """
    Fungsi untuk mensimulasikan input keyboard.
    Ia akan mengetik string barcode lalu menekan tombol Enter.
    """
    if not barcode_text:
        return

    print(f"[Keyboard] Mengetik barcode: {barcode_text}")
    try:
        keyboard.type(barcode_text)
        keyboard.press(Key.enter)
        keyboard.release(Key.enter)
        print(f"[Keyboard] Selesai.")
    except Exception as e:
        print(f"[Keyboard] Error: {e}")


# -----------------------------------------------------------------------------
# 2. SERVER UNTUK KONEKSI WI-FI (HTTP)
# -----------------------------------------------------------------------------
# Bagian ini menggunakan Flask untuk membuat web server sederhana.
# Ia akan menyediakan endpoint '/scan' yang bisa diakses dari Flutter.
# -----------------------------------------------------------------------------
app = Flask(__name__)

@app.route("/scan", methods=["POST"])
def http_scan_handler():
    """Endpoint untuk menerima data barcode via HTTP POST."""
    print("[HTTP] Menerima request...")
    try:
        data = request.get_json()
        if data and "barcode" in data:
            barcode = data["barcode"]
            print(f"[HTTP] Barcode diterima: {barcode}")
            type_barcode(barcode)
            return jsonify({"status": "success", "barcode": barcode}), 200
        else:
            print("[HTTP] Error: Format JSON tidak valid.")
            return jsonify({"status": "error", "message": "Invalid JSON format"}), 400
    except Exception as e:
        print(f"[HTTP] Error: {e}")
        return jsonify({"status": "error", "message": str(e)}), 500

def run_http_server():
    """Menjalankan server Flask di thread terpisah."""
    print(f"[HTTP] Server berjalan di http://0.0.0.0:{HTTP_PORT}")
    # '0.0.0.0' berarti server bisa diakses dari perangkat lain di jaringan.
    app.run(host="0.0.0.0", port=HTTP_PORT)


# -----------------------------------------------------------------------------
# 3. SERVER UNTUK KONEKSI BLUETOOTH LE
# -----------------------------------------------------------------------------
# Bagian ini menggunakan Bleak untuk membuat server Bluetooth LE.
# Ia akan meng-advertise sebuah service dan characteristic.
# -----------------------------------------------------------------------------

def gatt_write_handler(characteristic: BleakGATTCharacteristic, value: bytearray):
    """Callback yang dipanggil saat Flutter menulis data ke characteristic."""
    barcode = value.decode("utf-8")
    print(f"[Bluetooth] Barcode diterima: {barcode}")
    type_barcode(barcode)

async def run_ble_server():
    """Mempersiapkan dan menjalankan server Bluetooth LE."""
    print("[Bluetooth] Server berjalan...")
    
    # Loop tak terbatas untuk menjaga server tetap berjalan dan bisa konek ulang.
    while True:
        try:
            async with BleakServer(new_service()) as server:
                print("[Bluetooth] Meng-advertise service. Menunggu koneksi dari HP...")
                await asyncio.Future()  # Berjalan selamanya sampai koneksi terputus
        except Exception as e:
            print(f"[Bluetooth] Server berhenti karena error: {e}. Restart dalam 5 detik...")
            await asyncio.sleep(5)


def new_service():
    """Helper untuk membuat GATT Service."""
    # Membuat characteristic untuk barcode
    barcode_characteristic = BleakGATTCharacteristic(
        BARCODE_CHAR_UUID,
        ["write", "write-without-response"], # Izinkan client untuk menulis data
    )
    # Menambahkan handler untuk aksi 'write'
    barcode_characteristic.write_request_func = gatt_write_handler

    # Membuat service dan menambahkan characteristic ke dalamnya
    service = BleakGATTCharacteristic(SERVICE_UUID, [])
    service.add_characteristic(barcode_characteristic)
    
    return service

# -----------------------------------------------------------------------------
# 4. MAIN EXECUTION
# -----------------------------------------------------------------------------
# Bagian ini akan menjalankan kedua server (HTTP & Bluetooth) secara bersamaan.
# -----------------------------------------------------------------------------
if __name__ == "__main__":
    print("=" * 50)
    print("Memulai POS Bridge Server...")
    print("Tekan Ctrl+C untuk berhenti.")
    print("=" * 50)
    
    # 1. Jalankan server HTTP di thread terpisah agar tidak memblokir server BLE.
    http_thread = threading.Thread(target=run_http_server, daemon=True)
    http_thread.start()
    
    # 2. Jalankan server Bluetooth di thread utama.
    try:
        asyncio.run(run_ble_server())
    except KeyboardInterrupt:
        print("\nServer dihentikan.")
    except Exception as e:
        print(f"Server berhenti karena error tak terduga: {e}")
