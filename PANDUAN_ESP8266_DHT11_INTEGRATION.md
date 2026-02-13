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
7. [Troubleshooting](#troubleshooting)

---

## 🔌 SKEMA KONEKSI KABEL

### **Pinout DHT11:**
```
DHT11 (Sensor Kelembapan-Suhu):
┌─────────────────┐
│ 1 2 3 4         │
└─────────────────┘
  │ │ │ │
  │ │ │ └─ GND (Pin Negatif)
  │ │ └─── NC (Tidak digunakan)
  │ └───── DATA (Pin Data)
  └─────── VCC (+5V atau +3.3V)
```

### **Koneksi DHT11 ke ESP8266:**

| DHT11 Pin | Fungsi | → | ESP8266 Pin | Keterangan |
|-----------|--------|---|-------------|-----------|
| 1 | VCC (+Power) | → | 3.3V | Supply power |
| 2 | DATA | → | D4 (GPIO2) | Pin data baca suhu-kelembapan |
| 3 | NC | → | - | Tidak digunakan (kosongkan) |
| 4 | GND (-) | → | GND | Ground/Negatif |

### **Diagram Visual Koneksi:**
```
     DHT11 Sensor
        ┌──┐
        │1 ├─────────── 3.3V (VCC)
        │2 ├─────────── D4 (GPIO2)
        │3 │ (NC - kosong)
        │4 ├─────────── GND
        └──┘

     ESP8266 (NodeMCU)
   ┌─────────────────────┐
   │  3.3V  GND  D4      │
   │  ▲     │    ▲       │
   │  │     │    │       │
   └──┼─────┼────┼───────┘
      │     │    │
      └─────┼────┘
            └─── DHT11 (Power, Ground, Data)
```

### **Komponen yang Diperlukan:**
- ESP8266 (NodeMCU) - 1 buah
- Sensor DHT11 - 1 buah
- Kabel Jumper - 4 buah (min)
- Resistor 10kΩ (opsional, untuk stabilisasi pin data)

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
const int deviceId = 1;                // ID device di database Laravel
const int sendInterval = 10000;        // Kirim data setiap 10 detik (dalam milidetik)

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
  "device_id": 1,
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

Buka Command Prompt:
```bash
cd c:\xampp\mysql\bin
mysql -u root -p monitoring_suhu_bayi -e "SELECT id, device_id, temperature, humidity, status, recorded_at FROM monitorings ORDER BY id DESC LIMIT 10;"
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

## 🔍 TROUBLESHOOTING

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
