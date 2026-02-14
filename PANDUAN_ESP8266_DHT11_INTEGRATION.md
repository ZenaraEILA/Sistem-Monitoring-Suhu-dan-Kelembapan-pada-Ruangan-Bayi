# 📚 PANDUAN LENGKAP: Menghubungkan ESP8266 + DHT11 ke API Laravel di Localhost

**Target:** Membuat ESP8266 membaca sensor DHT11 dan mengirim data ke API Laravel di komputer lokal (localhost).

---

## 📋 DAFTAR ISI
1. [Skema Koneksi Kabel](#skema-koneksi-kabel)
2. [Library yang Diperlukan](#library-yang-diperlukan)
3. [Kode Program ESP8266](#kode-program-esp8266)
4. [Mengakses Localhost dari ESP8266](#mengakses-localhost-dari-esp8266)
5. [Testing API di Postman](#testing-api-di-postman)
6. [Verifikasi Data di Database](#verifikasi-data-di-database)
7. [MASALAH 1: Status Device Tetap Terhubung Padahal Offline](#masalah-1-status-device-terhubung-padahal-offline)
8. [MASALAH 2: ESP8266 Tidak Terhubung dengan Website](#masalah-2-esp8266-tidak-terhubung-dengan-website)
9. [Troubleshooting Detail](#troubleshooting)

---

## 🔌 SKEMA KONEKSI KABEL

### ⚠️ **PENTING: DHT11 Versi 3 Pin vs 4 Pin**

Ada 2 versi DHT11 di pasaran:

**Versi 4 Pin** (dengan PCB modul):
```
┌─────────────────┐
│ 1 2 3 4         │ ← VCC, DATA, NC, GND
└─────────────────┘
```

**Versi 3 Pin** (langsung sensor, tanpa modul):
```
   ┌───┐
   │VCC├─ Pin 1
   │DAT├─ Pin 2
   │GND├─ Pin 3
   └───┘
```

Panduan ini untuk **DHT11 versi 3 pin**. Jika punya 4 pin, langkah sama, cukup abaikan pin NC (tidak digunakan).

---

### **Pinout DHT11 Versi 3 Pin:**
```
DHT11 (Sensor Kelembapan-Suhu):
┌──────────────┐
│ 1  2  3      │
└──────────────┘
 │  │  │
 │  │  └─ GND (Pin Negatif / Ground)
 │  └────── DATA (Pin Data - baca sensor)
 └───────── VCC (+Power - HARUS 3.3V, BUKAN 5V!)
```

### **Koneksi DHT11 3 Pin ke ESP8266:**

| DHT11 Pin | Fungsi | → | ESP8266 Pin | Keterangan |
|-----------|--------|---|-------------|-----------|
| 1 | VCC (+Power) | → | **3V3** (BUKAN 5V!) | Supply power 3.3V |
| 2 | DATA | → | D4 (GPIO2) | Pin data baca suhu-kelembapan |
| 3 | GND (-) | → | GND | Ground/Negatif |

### ⚠️ **PENTING: Kenapa HARUS 3V3, Bukan 5V?**

**Masalah jika pakai 5V:**
- ESP8266 hanya menerima 3.3V di pin GPIO
- Jika dikasih 5V, **pin GPIO rusak dan ESP8266 tidak bisa digunakan lagi**
- DHT11 akan membaca dengan tidak akurat

**Solusi:**
- **HARUS gunakan 3V3** (pin 3V3 di ESP8266)
- Jangan gunakan VIN atau 5V pin
- DHT11 versi 3 pin dirancang untuk 3.3V

### **Diagram Koneksi Fisik (3 Pin):**

```
        DHT11 Sensor (3 pin)
        ┌─────┬─────┬─────┐
        │ 1   │ 2   │ 3   │
        │ VCC │DATA │GND  │
        └─┬───┴─┬───┴─┬───┘
          │     │     │
          │     │     │
    ┌─────▼─┐   │     └──────────────┐
    │ 3V3   │   │                    │
    │       │   │                    │
    │  ESP8266  │                    │
    │           │                    │
    │  D4   ◄───┘                    │
    │  GND  ◄────────────────────────┘
    └───────┘

```

**Koneksi Kabel Lengkap:**
1. **DHT11 Pin 1 (VCC)** → ESP8266 **3V3** (pin 3.3V)
2. **DHT11 Pin 2 (DATA)** → ESP8266 **D4** (pin GPIO2)
3. **DHT11 Pin 3 (GND)** → ESP8266 **GND** (ground/negatif)

---

### **Diagram Salah ❌ (Jangan Lakukan!)**

```
SALAH - Pakai 5V:
DHT11 Pin 1 (VCC) → ESP8266 VIN atau 5V ❌
Hasil: Pin GPIO rusak, ESP8266 tidak berfungsi!

SALAH - Pin DATA ke pin lain:
DHT11 Pin 2 (DATA) → ESP8266 D0, D1, D2, D3, dsb ❌
```

### **Alasan Harus Persis Seperti Panduan:**
- **3V3 bukan 5V**: ESP8266 input maksimal 3.3V. Pin GPIO rusak jika 5V
- **D4 bukan pin lain**: D4 = GPIO2, sudah dikonfigurasi di kode Arduino
- **GND harus terhubung**: Ground adalah referensi 0V, WAJIB ada!

### **Komponen yang Diperlukan:**
- ESP8266 (NodeMCU) - 1 buah
- Sensor DHT11 versi 3 pin - 1 buah
- Kabel Jumper - 3 buah (VCC, DATA, GND)
- **Resistor 10kΩ - 1 buah** (untuk pull-up pada pin DATA, sangat penting!)
- Power supply (USB atau catu daya 5V) untuk ESP8266

### **Mengapa Resistor 10kΩ Diperlukan?**

Pin DATA DHT11 memerlukan **pull-up resistor** agar sinyal stabil:
```
             3.3V
              │
              │
         ┌─10kΩ─┐
         │      │
DHT11 ──┤      ├─── ESP8266 D4
 (DATA)  │      │
         └──┘
         
```

**Fungsi resistor:**
- Menjaga pin D4 tetap HIGH (3.3V) saat tidak ada sinyal
- Meningkatkan kecepatan pembacaan sensor
- Mengurangi noise/gangguan

**Jika tidak punya resistor:**
- Sensor kadang terbaca, kadang tidak
- Error "Sensor DHT11 tidak merespons" sering muncul
- Data tidak konsisten

---

## 📦 LIBRARY YANG DIPERLUKAN

### **1. Di Arduino IDE - Instal Board ESP8266**

**Langkah-langkah:**

**Step 1:** Buka Arduino IDE → Preferences
```
File → Preferences
```

**Step 2:** Copy-paste URL ini ke "Additional Board Manager URLs":
```
http://arduino.esp8266.com/stable/package_esp8266com_index.json
```

**Step 3:** Buka Board Manager
```
Tools → Board → Boards Manager
```

**Step 4:** Cari "ESP8266" dan klik "Install"
```
Ketik di search: esp8266
Pilih: esp8266 by ESP8266 Community
Tunggu sampai selesai (±150MB)
```

### **2. Di Arduino IDE - Instal Library DHT**

**Step 1:** Buka Library Manager
```
Sketch → Include Library → Manage Libraries
```

**Step 2:** Cari "DHT" dan instal 2 library:
```
1. DHT sensor library by Adafruit
2. Adafruit Unified Sensor
```

**Cara mengecek:** Cari di Search → Install kedua library ini

### **3. Pengaturan Board untuk Upload**

Setelah instal, atur pengaturan:
```
Tools → Board → NodeMCU 1.0 (ESP8266)
Tools → Port → COM[X] (pilih port ESP8266 Anda)
```

> **Tips:** Jika tidak muncul port USB, instal driver CH340:
> Download dari: https://www.wemos.cc/downloads

---

## 💻 KODE PROGRAM ESP8266

### **Kode Lengkap - Simpan dengan nama: `esp8266_dht_to_laravel.ino`**

```cpp
#include <ESP8266WiFi.h>
#include <DHT.h>
#include <ArduinoJson.h>

// ============ KONFIGURASI DHT11 ============
#define DHTPIN D4       // Pin D4 (GPIO2) untuk data DHT11
#define DHTTYPE DHT11   // Tipe sensor: DHT11
DHT dht(DHTPIN, DHTTYPE);

// ============ KONFIGURASI WIFI ============
const char* ssid = "NAMA_WIFI_ANDA";           // Ganti dengan SSID WiFi Anda
const char* password = "PASSWORD_WIFI_ANDA";   // Ganti dengan password WiFi Anda

// ============ KONFIGURASI API LARAVEL ============
const char* serverIP = "192.168.1.X";  // Ganti dengan IP komputer Anda (BUKAN localhost!)
const int serverPort = 8000;           // Port Laravel (default: 8000)
const char* apiEndpoint = "/api/monitoring/store"; // Endpoint API Laravel

// ============ KONFIGURASI DEVICE ============
const char* deviceId = "ruang_bayi_#1_1770853312";  // Ganti dengan device_id dari tabel devices di database
const int sendInterval = 10000;                     // Kirim data setiap 10 detik (dalam milidetik)

// ⚠️  PENTING: deviceId HARUS berupa STRING dan HARUS SESUAI dengan tabel devices!
//     SALAH:  const int deviceId = 1;  ❌ (tipe int, bukan string)
//     BENAR:  const char* deviceId = "ruang_bayi_#1_1770853312";  ✅ (string, sesuai database)

// ============ DEKLARASI VARIABEL ============
unsigned long lastSendTime = 0;
float temperature = 0.0;
float humidity = 0.0;

// ============ SETUP ============
void setup() {
  Serial.begin(115200);
  delay(100);
  
  Serial.println("\n\n");
  Serial.println("=== SISTEM MONITORING SUHU & KELEMBAPAN ===");
  Serial.println("Menginisialisasi DHT11...");
  
  // Inisialisasi DHT11
  dht.begin();
  delay(2000); // Tunggu DHT siap (DHT11 memerlukan waktu startup)
  
  // Koneksi ke WiFi
  connectToWiFi();
}

// ============ LOOP UTAMA ============
void loop() {
  // Cek koneksi WiFi
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi terputus! Mencoba reconnect...");
    connectToWiFi();
    return;
  }
  
  // Baca sensor setiap interval yang ditentukan
  if (millis() - lastSendTime >= sendInterval) {
    lastSendTime = millis();
    
    // Baca suhu dan kelembapan
    if (readDHT11()) {
      // Tampilkan data di Serial Monitor
      printSensorData();
      
      // Kirim data ke API Laravel
      sendDataToLaravel();
    } else {
      Serial.println("❌ Gagal membaca sensor DHT11!");
    }
  }
}

// ============ FUNGSI: KONEKSI WiFi ============
void connectToWiFi() {
  Serial.println("\n=== MENGHUBUNGKAN KE WiFi ===");
  Serial.print("SSID: ");
  Serial.println(ssid);
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi TERHUBUNG!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    Serial.println("\n❌ WiFi GAGAL TERHUBUNG!");
    Serial.println("Cek: SSID, Password, dan jangkauan WiFi");
  }
}

// ============ FUNGSI: BACA DHT11 ============
bool readDHT11() {
  // DHT11 perlu minimal 2 detik antar pembacaan
  static unsigned long lastReadTime = 0;
  
  if (millis() - lastReadTime < 2000) {
    return false; // Terlalu cepat, skip
  }
  
  lastReadTime = millis();
  
  // Baca nilai dari sensor
  float h = dht.readHumidity();      // Baca kelembapan
  float t = dht.readTemperature();   // Baca suhu (dalam Celsius)
  
  // Cek apakah pembacaan valid
  if (isnan(h) || isnan(t)) {
    Serial.println("❌ Sensor DHT11 tidak merespons (cek kabel & pin)");
    return false;
  }
  
  temperature = t;
  humidity = h;
  
  return true;
}

// ============ FUNGSI: TAMPILKAN DATA DI SERIAL MONITOR ============
void printSensorData() {
  Serial.println("\n=== DATA SENSOR ===");
  Serial.print("🌡️  Suhu: ");
  Serial.print(temperature, 1);
  Serial.println(" °C");
  
  Serial.print("💧 Kelembapan: ");
  Serial.print(humidity, 1);
  Serial.println(" %");
  
  Serial.print("⏰ Waktu: ");
  Serial.println(millis() / 1000);
}

// ============ FUNGSI: KIRIM DATA KE API LARAVEL ============
void sendDataToLaravel() {
  Serial.println("\n=== MENGIRIM DATA KE API ===");
  Serial.print("Target: http://");
  Serial.print(serverIP);
  Serial.print(":");
  Serial.print(serverPort);
  Serial.println(apiEndpoint);
  
  WiFiClient client;
  
  // Coba koneksi ke server
  if (!client.connect(serverIP, serverPort)) {
    Serial.println("❌ Gagal terhubung ke server!");
    Serial.println("Cek:");
    Serial.println("  1. IP address komputer (gunakan ipconfig di CMD)");
    Serial.println("  2. Port 8000 sudah running (php artisan serve)");
    Serial.println("  3. Firewall tidak memblokir koneksi");
    return;
  }
  
  Serial.println("✅ Koneksi ke server berhasil!");
  
  // Buat JSON payload
  StaticJsonDocument<200> doc;
  doc["device_id"] = deviceId;
  doc["temperature"] = temperature;
  doc["humidity"] = humidity;
  
  String jsonPayload;
  serializeJson(doc, jsonPayload);
  
  // Buat HTTP request
  String request = "POST ";
  request += apiEndpoint;
  request += " HTTP/1.1\r\n";
  request += "Host: ";
  request += serverIP;
  request += "\r\n";
  request += "Content-Type: application/json\r\n";
  request += "Content-Length: ";
  request += jsonPayload.length();
  request += "\r\n";
  request += "Connection: close\r\n";
  request += "\r\n";
  request += jsonPayload;
  
  // Kirim request
  client.print(request);
  
  // Baca response
  Serial.println("\n📨 Response dari server:");
  
  while (client.connected() || client.available()) {
    if (client.available()) {
      String line = client.readStringUntil('\n');
      if (line.indexOf("200 OK") > -1) {
        Serial.println("✅ Data berhasil dikirim (HTTP 200)!");
      } else if (line.indexOf("422") > -1) {
        Serial.println("❌ Validasi data gagal (HTTP 422)");
      } else if (line.indexOf("404") > -1) {
        Serial.println("❌ Endpoint tidak ditemukan (HTTP 404)");
      }
      Serial.println(line);
    }
  }
  
  client.stop();
  Serial.println("\n=== SELESAI ===\n");
}
```

### **Cara Menggunakan Kode:**

1. **Buka Arduino IDE**
2. **File → New** untuk membuat file baru
3. **Copy-paste kode di atas**
4. **Edit 3 bagian penting:**

```cpp
// EDIT INI:
const char* ssid = "NAMA_WIFI_ANDA";           // ← Ganti dengan WiFi Anda
const char* password = "PASSWORD_WIFI_ANDA";   // ← Ganti dengan password
const char* serverIP = "192.168.1.X";          // ← Ganti dengan IP komputer Anda
```

5. **Upload ke ESP8266**

---

## 🌐 MENGAKSES LOCALHOST DARI ESP8266

### **Masalah:**
DHT11 tidak bisa akses `localhost:8000` karena "localhost" hanya bisa diakses dari komputer itu sendiri. Dari ESP8266, Anda harus gunakan **IP Address komputer lokal**.

### **Solusi:**

### **Step 1: Cari IP Address Komputer Windows Anda**

**Cara 1 - Menggunakan Command Prompt:**
```
1. Tekan: Windows + R
2. Ketik: cmd
3. Ketik: ipconfig
4. Cari baris: IPv4 Address
   Contoh: 192.168.1.100
```

**Cara 2 - Lihat di WiFi Settings:**
```
Settings → Network & Internet → WiFi → Properties
Cari "IPv4 address" (bukan IPv6)
```

### **Contoh Output ipconfig:**
```
Ethernet adapter Local Area Connection:
   Connection-specific DNS Suffix  . :
   IPv4 Address. . . . . . . . . . . : 192.168.1.100
   Subnet Mask . . . . . . . . . . . : 255.255.255.0
   Default Gateway . . . . . . . . . : 192.168.1.1
```
→ **Gunakan: 192.168.1.100**

### **Step 2: Update Kode ESP8266**

Ganti baris ini:
```cpp
// DARI:
const char* serverIP = "192.168.1.X";

// MENJADI: (sesuai IP Anda)
const char* serverIP = "192.168.1.100";  // Contoh IP Anda
```

### **Step 3: Pastikan Port 8000 Aktif**

Di command prompt komputer, jalankan:
```bash
php artisan serve
```

Output yang benar:
```
Laravel development server started on [http://127.0.0.1:8000]
```

---

## 🚀 TESTING API DI POSTMAN

Sebelum test dari ESP8266, test dulu dari Postman untuk pastikan API bekerja.

### **Step 1: Instal Postman**
Download dari: https://www.postman.com/downloads/

### **Step 2: Buat Request**

1. Buka Postman
2. Klik **+ New**
3. Pilih **HTTP Request**

### **Step 3: Atur Request**

**Method:** POST
```
Pilih: POST (bukan GET)
```

**URL:**
```
http://127.0.0.1:8000/api/monitoring/store
```

**Headers:**
```
Key: Content-Type
Value: application/json
```

**Body (Raw - JSON):**
```json
{
  "device_id": "ruang_bayi_#1_1770853312",
  "temperature": 25.5,
  "humidity": 60.3
}
```

### **Step 4: Kirim Request**

Klik tombol **Send** (biru).

**Response yang Diharapkan:**
```json
{
  "success": true,
  "message": "Data monitoring berhasil disimpan",
  "data": {
    "id": 1,
    "device_id": 1,
    "temperature": 25.5,
    "humidity": 60.3,
    "status": "Aman",
    "recorded_at": "2026-02-13 10:30:00"
  }
}
```

### **Jika Error di Postman:**

**Error 404 - Not Found:**
```
Solusi: Cek endpoint URL, pastikan Laravel sudah serve di http://127.0.0.1:8000
```

**Error 422 - Validation Failed:**
```
Solusi: Cek format JSON, pastikan semua field ada: device_id, temperature, humidity
```

---

## 💾 VERIFIKASI DATA DI DATABASE

### **Cara 1: Menggunakan phpMyAdmin**

1. Buka browser → `http://localhost/phpmyadmin`
2. Login (username: `root`, password: kosongkan)
3. Pilih database: `monitoring_suhu_bayi`
4. Pilih table: `monitorings`
5. Klik tab **Browse**
6. Lihat apakah data sudah masuk

### **Cara 2: Menggunakan Command Line**

**Menggunakan PowerShell:**
```powershell
cd c:\xampp\mysql\bin
.\mysql -u root monitoring_suhu_bayi -e "SELECT id, device_id, temperature, humidity, status, recorded_at FROM monitorings ORDER BY id DESC LIMIT 10;"
```

**Atau menggunakan CMD (Command Prompt):**
```cmd
cd c:\xampp\mysql\bin
mysql -u root monitoring_suhu_bayi -e "SELECT id, device_id, temperature, humidity, status, recorded_at FROM monitorings ORDER BY id DESC LIMIT 10;"
```

Tekan Enter (tidak ada password, langsung tekan Enter saja)

**Output yang Diharapkan:**
```
+----+-----------+-------------+----------+--------+---------------------+
| id | device_id | temperature | humidity | status | recorded_at         |
+----+-----------+-------------+----------+--------+---------------------+
|  1 |         1 |        25.5 |     60.3 | Aman   | 2026-02-13 10:30:00 |
+----+-----------+-------------+----------+--------+---------------------+
```

### **Cara 3: Menggunakan Laravel Tinker**

```bash
php artisan tinker
>>> \App\Models\Monitoring::latest()->limit(5)->get();
```

---

## � MASALAH 1: Status Device "TERHUBUNG" Padahal Offline

### **Situasi:**
```
Website menampilkan:
✅ Status: TERHUBUNG (Hijau)
⏰ Last Update: 1 menit lalu

PADAHAL:
- ESP8266 sudah dimatikan 5 menit lalu
- Website seharusnya menampilkan ❌ TIDAK TERHUBUNG
```

### **Kenapa Ini Terjadi?**

Website hanya melihat data **terakhir** yang tersimpan di database. Jika tidak ada mekanisme pengecekan "last update", status akan tetap "TERHUBUNG" selamanya, meskipun perangkat already offline.

**Contoh:**
```
Database:
- Terakhir menerima data: 13:45:30 (temperature: 26.5, humidity: 58.0)
- Sekarang jam: 14:00:00 (15 menit berlalu, tapi status tetap hijau ❌)
```

---

### **Solusi: Buat Sistem Status Berdasarkan Last Update**

#### **Step 1: Update Database - Tambah Kolom last_ping**

Buat migration baru:
```bash
php artisan make:migration add_last_ping_to_devices_table
```

Edit file migration (di `database/migrations/`):
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->timestamp('last_ping')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('last_ping');
        });
    }
};
```

Run migration:
```bash
php artisan migrate
```

---

#### **Step 2: Update Model Device**

Edit [app/Models/Device.php](app/Models/Device.php):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device_id',
        'device_name',
        'location',
        'last_ping',
    ];

    protected $casts = [
        'last_ping' => 'datetime',
    ];

    /**
     * Fungsi: Cek apakah device ONLINE atau OFFLINE
     * Logic: Jika last_ping < 30 detik → ONLINE
     *        Jika last_ping >= 30 detik → OFFLINE
     */
    public function isOnline(): bool
    {
        if (!$this->last_ping) {
            return false; // Belum pernah kirim data
        }

        $secondsAgo = now()->diffInSeconds($this->last_ping);
        return $secondsAgo < 30; // Kurang dari 30 detik = ONLINE
    }

    /**
     * Fungsi: Get status dengan warna untuk dashboard
     */
    public function getStatus(): array
    {
        return [
            'is_online' => $this->isOnline(),
            'status_text' => $this->isOnline() ? 'TERHUBUNG' : 'TIDAK TERHUBUNG',
            'status_color' => $this->isOnline() ? 'success' : 'danger', // Bootstrap color
            'last_ping' => $this->last_ping?->format('Y-m-d H:i:s'),
            'seconds_since_ping' => $this->last_ping ? now()->diffInSeconds($this->last_ping) : null,
        ];
    }
}
```

---

#### **Step 3: Update Monitoring Controller - Update last_ping**

Edit [app/Http/Controllers/Api/MonitoringController.php](app/Http/Controllers/Api/MonitoringController.php):

```php
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'device_id' => 'required|string|exists:devices,device_id',
        'temperature' => 'required|numeric|between:-50,60',
        'humidity' => 'required|numeric|between:0,100',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $device = Device::where('device_id', $request->device_id)->first();

    // 🆕 UPDATE last_ping saat ESP kirim data
    $device->update(['last_ping' => now()]);

    // Determine status based on temperature and humidity
    $status = 'Aman';
    if ($request->temperature < 15 || $request->temperature > 30) {
        $status = 'Tidak Aman';
    }
    if ($request->humidity < 35 || $request->humidity > 60) {
        $status = 'Tidak Aman';
    }

    $monitoring = Monitoring::create([
        'device_id' => $device->id,
        'temperature' => $request->temperature,
        'humidity' => $request->humidity,
        'status' => $status,
        'recorded_at' => now(),
    ]);

    return response()->json([
        'message' => 'Data monitoring berhasil disimpan',
        'data' => $monitoring,
    ], 201);
}
```

---

#### **Step 4: Update Dashboard View - Tampilkan Status Dinamis**

Edit [resources/views/dashboard/index.blade.php](resources/views/dashboard/index.blade.php):

```blade
@foreach($devices as $device)
    <?php $statusInfo = $device->getStatus(); ?>
    
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">{{ $device->device_name }}</h5>
                    
                    {{-- Status Indicator --}}
                    @if($statusInfo['is_online'])
                        <span class="badge bg-success">
                            <i class="fas fa-solid fa-wifi"></i> TERHUBUNG
                        </span>
                    @else
                        <span class="badge bg-danger pulse">
                            <i class="fas fa-solid fa-wifi-slash"></i> TIDAK TERHUBUNG
                        </span>
                    @endif
                </div>

                <p class="text-muted small mb-2">
                    ⏰ Last Update: {{ $statusInfo['last_ping'] ?? 'Belum ada data' }}
                    <br>
                    ⌛ {{ $statusInfo['seconds_since_ping'] ?? '-' }} detik lalu
                </p>

                {{-- Konten card lainnya --}}
                <p class="mb-1">
                    <strong>🌡️ Suhu:</strong> 
                    <span id="temp-{{ $device->id }}">--</span>°C
                </p>
                <p class="mb-0">
                    <strong>💧 Kelembapan:</strong> 
                    <span id="humidity-{{ $device->id }}">--</span>%
                </p>
            </div>
        </div>
    </div>
@endforeach
```

**CSS untuk pulse animation (offline indicator):**
```css
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.pulse {
    animation: pulse 1.5s infinite;
}
```

---

#### **Step 5: Update Realtime API - Include Status**

Edit [app/Http/Controllers/Api/MonitoringController.php](app/Http/Controllers/Api/MonitoringController.php) method `getRealtimeDashboard()`:

```php
/**
 * Get all latest monitoring data for realtime dashboard
 * Includes ESP connection status (based on last ping)
 */
public function getRealtimeDashboard()
{
    $devices = Device::with(['latestMonitoring' => function($query) {
        $query->latest('recorded_at');
    }])->get()->map(function ($device) {
        return [
            'id' => $device->id,
            'device_id' => $device->device_id,
            'device_name' => $device->device_name,
            'location' => $device->location,
            'is_online' => $device->isOnline(),
            'status_text' => $device->getStatus()['status_text'],
            'last_ping' => $device->last_ping?->format('Y-m-d H:i:s'),
            'seconds_since_ping' => $device->last_ping ? now()->diffInSeconds($device->last_ping) : null,
            'latest_monitoring' => $device->latestMonitoring ? [
                'temperature' => $device->latestMonitoring->temperature,
                'humidity' => $device->latestMonitoring->humidity,
                'status' => $device->latestMonitoring->status,
                'recorded_at' => $device->latestMonitoring->recorded_at->format('Y-m-d H:i:s'),
            ] : null,
        ];
    });

    return response()->json(['data' => $devices], 200);
}
```

---

#### **Step 6: Update JavaScript - Auto-refresh Status**

```javascript
// Di dashboard/index.blade.php atau app.js
setInterval(function() {
    fetch('/api/monitoring/dashboard/realtime')
        .then(response => response.json())
        .then(data => {
            data.data.forEach(device => {
                // Update status indicator
                const statusElement = document.querySelector(`#status-${device.id}`);
                if (statusElement) {
                    if (device.is_online) {
                        statusElement.className = 'badge bg-success';
                        statusElement.innerHTML = '<i class="fas fa-wifi"></i> TERHUBUNG';
                    } else {
                        statusElement.className = 'badge bg-danger pulse';
                        statusElement.innerHTML = '<i class="fas fa-wifi-slash"></i> TIDAK TERHUBUNG';
                    }
                }

                // Update temperature and humidity
                if (device.latest_monitoring) {
                    document.querySelector(`#temp-${device.id}`).innerText = 
                        device.latest_monitoring.temperature.toFixed(1);
                    document.querySelector(`#humidity-${device.id}`).innerText = 
                        device.latest_monitoring.humidity.toFixed(1);
                }

                // Update last update time
                document.querySelector(`#last-ping-${device.id}`).innerText = 
                    device.seconds_since_ping + ' detik';
            });
        });
}, 5000); // Refresh setiap 5 detik
```

---

### **Ringkasan Perubahan:**

| Komponen | Perubahan |
|----------|-----------|
| Database | Tambah kolom `last_ping` di tabel `devices` |
| Model Device | Fungsi `isOnline()` dan `getStatus()` |
| API Controller | Update `last_ping` saat ESP kirim data |
| API Endpoint | Return status online/offline |
| Dashboard View | Tampilkan badge hijau/merah dinamis |
| JavaScript | Polling API setiap 5 detik |

---

### **Testing:**

1. **Nyalakan ESP8266** → Status berubah ke HIJAU ✅
2. **Matikan ESP8266** → Status berubah merah dalam 30 detik ❌
3. **Lihat CSS pulse** → Badge merah berkedip-kedip

---

## 🚨 MASALAH 2: ESP8266 Tidak Terhubung dengan Website

### **Gejala:**
```
Serial Monitor:
❌ Gagal terhubung ke server!

Website:
- Dashboard masih kosong
- Tidak ada data yang masuk ke database
```

---

### **Diagram Troubleshooting Step-by-Step:**

```
┌─────────────────────────────────────────────┐
│  ESP8266 Tidak Terhubung ke Website?        │
└─────────────┬───────────────────────────────┘
              │
              ├─ Check 1: Cek IP Address
              │  └─→ php artisan serve / ipconfig
              │
              ├─ Check 2: WiFi Sama Jaringan?
              │  └─→ Ping dari CMD
              │
              ├─ Check 3: Port 8000 Aktif?
              │  └─→ php artisan serve running?
              │
              ├─ Check 4: Firewall Windows?
              │  └─→ Allow PHP di Firewall
              │
              ├─ Check 5: Test Postman Dulu
              │  └─→ POST /api/monitoring/store
              │
              └─ Check 6: Serial Monitor Output?
                 └─→ Cari HTTP status code (200, 422, 404)
```

---

### **Check 1: Verifikasi IP Address Komputer**

#### **Masalah: IP Address Salah**

**Gejala:**
```
Arduino Code:
const char* serverIP = "192.168.1.999"; ← SALAH, IP tidak ada

Serial Output:
❌ Gagal terhubung ke server!
```

**Solusi:**

**Langkah 1: Cari IP Komputer Sebenarnya**
```powershell
# Buka PowerShell/CMD
ipconfig

# Cari output:
Ethernet adapter Ethernet:
   IPv4 Address. . . . . . . . . . : 192.168.0.100  ← IP ANDA
   Subnet Mask . . . . . . . . . . : 255.255.255.0
```

**Langkah 2: Catat IP Address**
```
Contoh IP Komputer: 192.168.0.100
(Setiap komputer beda IP, jangan copy IP kami)
```

**Langkah 3: Update di Arduino Code**
```cpp
// GANTI INI dengan IP benar:
const char* serverIP = "192.168.0.100";  // ← Sesuai hasil ipconfig Anda
```

**Langkah 4: Upload ke ESP8266**

---

### **Check 2: Pastikan ESP8266 dan Komputer Satu Jaringan WiFi**

#### **Masalah: WiFi Beda Jaringan**

**Gejala:**
```
Laptop terhubung ke WiFi "Rumah" (192.168.0.x)
ESP8266 terhubung ke WiFi "Mobile Hotspot" (192.168.43.x)

Hasilnya: ESP8266 tidak bisa reach IP komputer
```

**Solusi:**

**Langkah 1: Cek WiFi Laptop**
```
Klik WiFi icon di Windows taskbar
Lihat: "Connected to: Rumah" atau "Connected to: Mobile"
Catat nama WiFi
```

**Langkah 2: Update Arduino Code dengan WiFi yang Sama**
```cpp
// HARUS sama dengan WiFi laptop:
const char* ssid = "Rumah";              // ← WiFi yang sama
const char* password = "password123";
```

**Langkah 3: Verifikasi dari PowerShell**

Setelah ESP upload, cek:
```powershell
# Buka PowerShell
arp -a

# Output akan menunjukkan semua device di jaringan yang sama:
192.168.0.1    (gateway)
192.168.0.100  (komputer Anda)
192.168.0.150  (ESP8266 - harus ada di list ini!)
```

**Langkah 4: Test Ping**
```powershell
ping 192.168.0.100

# Harus reply:
Reply from 192.168.0.100: bytes=32 time=2ms TTL=64
Reply from 192.168.0.100: bytes=32 time=1ms TTL=64

# Jika "Request timed out" = tidak satu jaringan
```

---

### **Check 3: Pastikan Laravel Server Aktif**

#### **Masalah: php artisan serve Belum Dijalankan**

**Gejala:**
```
Serial Monitor:
❌ Gagal terhubung ke server!

Padahal IP sudah benar, tapi port 8000 tidak aktif
```

**Solusi:**

**Langkah 1: Buka Command Prompt / PowerShell Baru**
```powershell
cd c:\Users\Topan\Documents\sistem-monitoring-suhu-bayi
php artisan serve
```

**Hasil yang Benar:**
```
Laravel development server started on [http://127.0.0.1:8000]
```

**Langkah 2: Biarkan Tetap Running**
- Jangan close terminal Laravel
- Biarkan hingga selesai testing

**Langkah 3: Di PowerShell Lain, Test Koneksi**
```powershell
curl http://localhost:8000/

# Output:
StatusCode        : 200
```

---

### **Check 4: Firewall Windows Memblokir Port 8000**

#### **Masalah: Windows Firewall Blokir Port 8000**

**Gejala:**
```
Serial Output:
❌ Gagal terhubung ke server!

Tapi dari komputer sendiri: curl http://localhost:8000 ✅ OK

Berarti: Port 8000 blocking dari device lain (ESP8266)
```

**Solusi:**

**Langkah 1: Buka Firewall Settings**
```
Windows → Settings → Privacy & Security → Firewall & network protection
```

**Langkah 2: Click "Allow an app through firewall"**
```
Klik: "Allow an app through firewall"
```

**Langkah 3: Cari PHP**
```
Scroll cari "php.exe" atau "PHP"
Pastikan checkbox CHECKED di Private dan Public
Jika belum ada, klik "Add an app" dan browse ke:
   C:\xampp\php\php-cgi.exe
   atau
   C:\xampp\php\php.exe
```

**Langkah 4: Restart Laravel**
```
- Close php artisan serve (Ctrl+C)
- Jalankan lagi: php artisan serve
```

**Langkah 5: Test dari Serial Monitor**
- Upload kode ESP8266 lagi
- Lihat apakah Serial Monitor menampilkan ✅ "Koneksi berhasil"

---

### **Check 5: Test API Dengan Postman Dulu**

#### **Masalah: API Endpoint Tidak Valid**

Sebelum test dari ESP8266, pastikan API bekerja dari Postman:

**Langkah 1: Buka Postman**

**Langkah 2: Setup Request**
```
Method: POST
URL: http://127.0.0.1:8000/api/monitoring/store

Headers:
  Content-Type: application/json

Body (JSON):
{
  "device_id": "ruang_bayi_#1_1770853312",
  "temperature": 25.5,
  "humidity": 60.3
}
```

**Langkah 3: Klik Send**

**Output yang Benar (HTTP 201):**
```json
{
  "message": "Data monitoring berhasil disimpan",
  "data": {
    "id": 1,
    "device_id": 1,
    "temperature": 25.5,
    "humidity": 60.3,
    "status": "Aman",
    "recorded_at": "2026-02-14 10:30:00"
  }
}
```

**Jika Error 404:**
```json
{
  "message": "Endpoint tidak ditemukan"
}
```
→ Pastikan `routes/api.php` punya route POST `/api/monitoring/store`

---

### **Check 6: Analisis Serial Monitor Output**

Serial Monitor akan menampilkan HTTP status code. Artikan dengan tabel ini:

| HTTP Code | Arti | Solusi |
|-----------|------|--------|
| **200 OK** | ✅ Data berhasil dikirim | Lihat database |
| **201 Created** | ✅ Data berhasil dibuat | Selesai! |
| **400 Bad Request** | Format JSON salah | Cek tanda kutip di string |
| **404 Not Found** | Endpoint tidak ada | Cek `routes/api.php` |
| **422 Validation Error** | Data tidak valid | Cek `device_id` sesuai database? |
| **500 Error** | Server error | Lihat Laravel terminal untuk error message |

---

### **Debug Checklist:**

Jika masih tidak bisa, cek satu per satu:

```
☐ IP Address Komputer sudah benar di Arduino code?
  ipconfig → Copy IPv4 Address ke const char* serverIP

☐ WiFi ESP8266 dan Laptop SAMA JARINGAN?
  arp -a → Lihat ESP8266 di list?

☐ php artisan serve SEDANG RUNNING?
  Terminal Laravel menampilkan "started on [http://127.0.0.1:8000]"?

☐ Firewall Windows Allow PHP?
  Settings → Firewall → Allow an app → Check "php.exe"

☐ Serial Monitor: Upload Code dan Lihat Output?
  "✅ WiFi TERHUBUNG" muncul?
  "✅ Koneksi ke server berhasil" muncul?

☐ Test Postman Dulu?
  POST http://127.0.0.1:8000/api/monitoring/store → HTTP 201?

☐ Database: Lihat data masuk?
  .\mysql -u root monitoring_suhu_bayi -e "SELECT * FROM monitorings LIMIT 5;"
```

Jika semua ☐ sudah, seharusnya sistem berhasil! 🎉

---



**Error Message di Serial Monitor:**
```
❌ Validasi data gagal (HTTP 422)
atau
❌ Endpoint tidak ditemukan (HTTP 404)
```

**Penyebab:** 
`deviceId` di Arduino CODE harus **TEPAT SAMA** dengan `device_id` di tabel devices database!

**Solusi - GANTI INI:**

**Step 1: Lihat device_id yang ada di database**
```bash
mysql -u root monitoring_suhu_bayi -e "SELECT device_id FROM devices;"
```

**Contoh output:**
```
device_id
ruang_bayi_#1_1770853312
DEVICE_SC0V9SZF6A_1770968554
```

**Step 2: Copy salah satu device_id dan gunakan di Arduino code**
```cpp
❌ SALAH (tidak ada di database):
const char* deviceId = "1";
const int deviceId = 1;
const char* deviceId = "device_123";

✅ BENAR (sesuai database):
const char* deviceId = "ruang_bayi_#1_1770853312";
// atau
const char* deviceId = "DEVICE_SC0V9SZF6A_1770968554";
```

**Penjelasan:**
- `device_id` HARUS string (`const char*`), bukan integer
- HARUS TEPAT SAMA dengan apa yang ada di database - case-sensitive!
- Jika tidak cocok, API akan return HTTP 422 (Validation Error)

---

### **Problem 1: ESP8266 tidak terhubung ke WiFi**

**Gejala:**
```
Serial Monitor menampilkan: ❌ WiFi GAGAL TERHUBUNG!
```

**Solusi:**
```cpp
1. Cek SSID dan password WiFi (case-sensitive!)
   const char* ssid = "NAMA_WIFI_ANDA";
   
2. Cek ESP8266 mendapat signal WiFi (RSSI minimal -70 dBm)
   
3. Instal ulang driver CH340 (USB tidak terdeteksi)

4. Reset ESP8266:
   - Tekan tombol RESET di board
   - Atau upload kode lagi
```

### **Problem 2: Sensor DHT11 tidak terbaca**

**Gejala:**
```
Serial Monitor: ❌ Sensor DHT11 tidak merespons (cek kabel & pin)
```

**Solusi:**
```cpp
1. Cek koneksi kabel:
   - DHT Pin 1 (VCC) → 3.3V ESP8266
   - DHT Pin 2 (DATA) → D4 ESP8266
   - DHT Pin 4 (GND) → GND ESP8266

2. Cek pin D4 di kode:
   #define DHTPIN D4  ← Harus D4 (GPIO2)

3. Tunggu 2 detik setelah power on
   DHT11 butuh waktu untuk startup

4. Uji sensor dengan kode contoh Adafruit:
   File → Examples → DHT → DHT tester
```

### **Problem 3: Tidak bisa terhubung ke server Laravel**

**Gejala:**
```
Serial Monitor: ❌ Gagal terhubung ke server!
```

**Solusi:**
```cpp
1. Cek IP address komputer:
   - Buka CMD
   - Ketik: ipconfig
   - Copy IPv4 Address
   const char* serverIP = "192.168.1.100";  ← Update dengan IP Anda

2. Cek Laravel server running:
   - Buka Command Prompt
   - cd c:\Users\Topan\Documents\sistem-monitoring-suhu-bayi
   - php artisan serve
   - Lihat output: [http://127.0.0.1:8000]

3. Disable Firewall Windows:
   - Settings → Firewall & Network Protection
   - Klik "Allow an app through firewall"
   - Allow PHP (biasanya sudah default)

4. Pastikan ESP8266 dan komputer di WiFi yang sama:
   - Cek WiFi SSID sama
   - Ping dari CMD: ping 192.168.1.100
```

### **Problem 4: Data dikirim tapi tidak masuk database**

**Gejala:**
```
Serial Monitor: ✅ Data berhasil dikirim
Tapi di database belum ada data baru
```

**Solusi:**
```cpp
1. Cek format JSON yang dikirim:
   Serial Monitor akan menampilkan JSON payload
   Pastikan sesuai: {"device_id": 1, "temperature": 25.5, "humidity": 60.3}

2. Cek route API di Laravel:
   routes/api.php → POST /api/monitoring/store harus sudah didefinisikan

3. Cek response dari server di Serial Monitor:
   Cari teks "200 OK" atau error code (404, 422, etc)

4. Lihat error di Laravel:
   - Buka command prompt Laravel
   - Lihat output dari php artisan serve
   - Cari error message
```

### **Problem 5: Koneksi sering putus**

**Gejala:**
```
Serial Monitor: WiFi connected → WiFi disconnected → connect ulang
```

**Solusi:**
```cpp
1. Gunakan power supply yang stabil (bukan USB port ajaib):
   - Gunakan USB power bank
   - Atau catu daya 5V 1A

2. Tambahkan resistor 10kΩ pada pin DHT11 data:
   DHT DATA PIN → 10kΩ resistor → 3.3V (pull-up)

3. Jauh dari interference:
   - Jauhkan dari microwave
   - Jauhkan dari motor listrik
   - Gunakan antenna WiFi external
```

---

## 📱 MONITORING DARI SERIAL MONITOR ESP8266

Setelah upload kode, buka Serial Monitor untuk lihat output:

```
Tools → Serial Monitor
Baud Rate: 115200
```

**Output Normal:**
```
=== SISTEM MONITORING SUHU & KELEMBAPAN ===
Menginisialisasi DHT11...

=== MENGHUBUNGKAN KE WiFi ===
SSID: WIFI_SAYA
..........
✅ WiFi TERHUBUNG!
IP Address: 192.168.1.100
Signal Strength: -45 dBm

=== DATA SENSOR ===
🌡️  Suhu: 25.5 °C
💧 Kelembapan: 60.3 %
⏰ Waktu: 3000

=== MENGIRIM DATA KE API ===
Target: http://192.168.1.100:8000/api/monitoring/store
✅ Koneksi ke server berhasil!

📨 Response dari server:
✅ Data berhasil dikirim (HTTP 200)!

=== SELESAI ===
```

---

## 🎯 PERCOBAAN STEP BY STEP

### **Hari 1: Setup Hardware**
```
1. Hubungkan DHT11 ke ESP8266 (sesuai skema)
2. Instal driver CH340 (jika USB tidak terdeteksi)
3. Instal Arduino IDE dan library DHT, ESP8266
```

### **Hari 2: Testing Sensor**
```
1. Upload contoh kode: DHT Tester (dari Adafruit)
2. Lihat di Serial Monitor apakah sensor bekerja
3. Pastikan suhu dan kelembapan terbaca dengan baik
```

### **Hari 3: Testing WiFi**
```
1. Update WiFi SSID dan password di kode
2. Update IP address komputer
3. Upload kode
4. Lihat di Serial Monitor apakah WiFi terhubung
```

### **Hari 4: Testing API**
```
1. Test di Postman (manual POST request)
2. Pastikan API response 200 OK
3. Cek data di database phpMyAdmin
```

### **Hari 5: End-to-End Testing**
```
1. Upload kode lengkap ke ESP8266
2. Pantau Serial Monitor
3. Lihat data masuk ke database real-time
4. Celebrate! 🎉
```

---

## 📞 KONTROL KECEPATAN PENGIRIMAN DATA

Jika ingin custom interval pengiriman, ubah di kode:

```cpp
// Kirim setiap 10 detik
const int sendInterval = 10000;  // dalam milidetik (1000 = 1 detik)

// Contoh:
// 5000 = 5 detik
// 30000 = 30 detik
// 60000 = 1 menit
```

---

## 📚 REFERENSI TAMBAHAN

- **DHT11 Library:** https://github.com/adafruit/DHT-sensor-library
- **ESP8266 Arduino Core:** https://github.com/esp8266/Arduino
- **Dokumentasi ESP8266:** https://arduino-esp8266.readthedocs.io/

---

**Selamat mencoba! Jika ada pertanyaan, lihat bagian Troubleshooting atau cek serial output untuk detail error.** ✨
