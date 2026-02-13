# ✅ CHECKLIST IMPLEMENTASI ESP8266 + DHT11 → LARAVEL LOCALHOST

Gunakan checklist ini untuk memastikan tidak ada step yang terlewat!

---

## 📦 PHASE 1: PERSIAPAN HARDWARE & SOFTWARE (1-2 Hari)

### Hardware Setup
- [ ] Siapkan komponen: ESP8266, DHT11, kabel jumper
- [ ] Hubungkan DHT11 ke ESP8266 sesuai skema (VCC, GND, D4)
- [ ] Install driver CH340 di komputer (untuk USB esp8266)
- [ ] Verifikasi ESP8266 terdeteksi di USB (Control Panel → Devices)

### Software Installation
- [ ] Download & install Arduino IDE (jika belum)
- [ ] Instal Board ESP8266:
  - [ ] File → Preferences
  - [ ] Paste: `http://arduino.esp8266.com/stable/package_esp8266com_index.json`
  - [ ] Tools → Board → Boards Manager → Search "ESP8266" → Install
- [ ] Instal Library DHT:
  - [ ] Sketch → Include Library → Manage Libraries
  - [ ] Install: "DHT sensor library by Adafruit"
  - [ ] Install: "Adafruit Unified Sensor"
- [ ] Instal ArduinoJson Library:
  - [ ] Search "ArduinoJson" → Install (by Benoit Blanchon)

### Arduino IDE Configuration
- [ ] Tools → Board → **NodeMCU 1.0 (ESP8266)**
- [ ] Tools → Port → **COM[X]** (pilih port ESP8266)
- [ ] Tools → Upload Speed → **115200**

---

## 🌐 PHASE 2: SETUP NETWORK (1 Jam)

### Cari IP Address Komputer
- [ ] Buka Command Prompt (Windows + R → cmd → Enter)
- [ ] Ketik: `ipconfig`
- [ ] Catat nilai IPv4 Address (contoh: 192.168.1.100)
  ```
  IPv4 Address: ___________________
  ```

### Verifikasi WiFi
- [ ] Ketahui SSID WiFi (nama jaringan)
- [ ] Catat SSID: _________________
- [ ] Catat password: _________________
- [ ] Pastikan ESP8266 bisa deteksi signal WiFi

### Edit Kode Arduino
- [ ] Buka file: `esp8266_code_ready_to_use.ino` di Arduino IDE
- [ ] Edit bagian ini:
  ```cpp
  const char* ssid = "GANTI_SSID";               // ← EDIT
  const char* password = "GANTI_PASSWORD";       // ← EDIT
  const char* serverIP = "192.168.1.XXX";        // ← EDIT (dari ipconfig)
  const int deviceId = 1;                        // ← OK, bisa biarkan
  ```
- [ ] Verifikasi tidak ada syntax error (Ctrl + Alt + V)

---

## 📤 PHASE 3: SETUP LARAVEL API (30 Menit)

### Pastikan Database & Routes Sudah Siap
- [ ] Database sudah di-migrate: `php artisan migrate:fresh --seed`
- [ ] Device sudah ada di database (id = 1):
  ```bash
  mysql -u root monitoring_suhu_bayi -e "SELECT * FROM devices LIMIT 1;"
  ```

### Verifikasi Route API
- [ ] Routes sudah ditambahkan di `routes/api.php`
- [ ] Check: `/api/monitoring` dan `/api/monitoring/store` sudah ada
- [ ] Clear cache: `php artisan cache:clear ; php artisan view:clear`

### Jalankan Laravel Server
- [ ] Buka Command Prompt
- [ ] Navigate ke project: `cd c:\Users\Topan\Documents\sistem-monitoring-suhu-bayi`
- [ ] Jalankan: `php artisan serve`
- [ ] Lihat output: `[http://127.0.0.1:8000]` (hindari buka di browser terlebih dahulu)
- [ ] **JANGAN CLOSE Terminal ini** (biarkan running)

---

## 🧪 PHASE 4: TESTING API TANPA ESP8266 (1 Jam)

### Test 1: Gunakan Postman
- [ ] Install Postman (jika belum)
- [ ] Buka Postman

**Create manual test:**
- [ ] Method: **POST**
- [ ] URL: `http://127.0.0.1:8000/api/monitoring/store`
- [ ] Headers: `Content-Type: application/json`
- [ ] Body (raw JSON):
  ```json
  {
    "device_id": 1,
    "temperature": 25.5,
    "humidity": 60.3
  }
  ```
- [ ] Klik **Send**
- [ ] Verifikasi response: Status **200 OK**
- [ ] Verifikasi response message: "Data monitoring berhasil disimpan"

### Test 2: Verifikasi Data di Database
- [ ] Buka phpMyAdmin: `http://localhost/phpmyadmin`
- [ ] Masuk ke database: `monitoring_suhu_bayi`
- [ ] Buka table: `monitorings`
- [ ] Cek: ada data baru dengan temp=25.5, humidity=60.3

### Test 3: Test dengan cURL (Optional)
```bash
curl -X POST http://127.0.0.1:8000/api/monitoring/store ^
  -H "Content-Type: application/json" ^
  -d "{\"device_id\": 1, \"temperature\": 30.2, \"humidity\": 55.0}"
```

- [ ] Response: 200 OK

---

## 🔌 PHASE 5: UPLOAD KODE KE ESP8266 (15 Menit)

### Verifikasi Hardware Connection
- [ ] ESP8266 terhubung ke komputer via USB
- [ ] Di Arduino IDE → Tools → Port → Port tersedia (bukan "COM1")

### Upload Kode
- [ ] Di Arduino IDE, buka file: `esp8266_code_ready_to_use.ino`
- [ ] Klik tombol **Upload** (panah → icon)
- [ ] Tunggu sampai selesai (± 30-60 detik)
- [ ] Lihat pesan: "Done uploading"

### Buka Serial Monitor
- [ ] Tools → Serial Monitor
- [ ] Baud Rate: **115200**
- [ ] Lihat output:
  ```
  === SISTEM MONITORING SUHU & KELEMBAPAN ===
  Menginisialisasi DHT11...
  
  === MENGHUBUNGKAN KE WiFi ===
  SSID: [WiFi Anda]
  ...........................
  ✅ WiFi TERHUBUNG!
  IP Address: 192.168.1.XXX
  ```

---

## ⚡ PHASE 6: ESP8266 KIRIM DATA KE API (30 Menit)

### Monitor Serial Output
- [ ] Serial Monitor sudah buka dan running
- [ ] Lihat output setiap 10 detik (sesuai interval setting):
  ```
  === DATA SENSOR ===
  🌡️  Suhu: 25.5 °C
  💧 Kelembapan: 60.3 %
  
  === MENGIRIM DATA KE API ===
  Target: http://192.168.1.100:8000/api/monitoring/store
  ✅ Koneksi ke server berhasil!
  ✅ Data berhasil dikirim (HTTP 200)!
  ```

### Verifikasi Data Masuk Database
- [ ] Refresh phpMyAdmin table `monitorings`
- [ ] Lihat data baru masuk real-time
- [ ] Cek timestamp sesuai dengan waktu current

### Success Indicators
- [ ] ✅ Serial Monitor menampilkan data sensor
- [ ] ✅ Serial Monitor menampilkan "Data berhasil dikirim (HTTP 200)"
- [ ] ✅ Data muncul di database dengan suhu real dan kelembapan real

---

## 🐛 TROUBLESHOOTING QUICK REFERENCE

### Problem: WiFi Tidak Terhubung
- [ ] Check SSID dan password (jangan salah ketik)
- [ ] Pastikan WiFi accessible dari ESP8266's location
- [ ] Lihat RSSI value (semakin negative semakin jauh)
- Solusi: Geser ESP8266 lebih dekat ke WiFi router

### Problem: Sensor DHT11 Tidak Terbaca
- [ ] Check koneksi fisik kabel (terutama pin D4)
- [ ] Tunggu 2 detik, DHT11 butuh startup time
- [ ] Coba toggle power ESP8266
- Solusi: Lihat PANDUAN_ESP8266_DHT11_INTEGRATION.md section "Troubleshooting"

### Problem: Tidak Bisa Koneksi ke Server
- [ ] Verifikasi IP address benar (run `ipconfig` lagi)
- [ ] Pastikan Laravel server masih running (lihat command prompt Laravel)
- [ ] Disable Firewall Windows temporarily
- [ ] Pastikan ESP8266 dan komputer di WiFi yang sama
- Solusi: Check di panel Firewall Windows → Allow PHP

### Problem: API Response 404
- [ ] Check endpoint URL di kode
- [ ] Pastikan routes sudah di add ke `routes/api.php`
- [ ] Run: `php artisan cache:clear`
- [ ] Run: `php artisan route:cache`

### Problem: API Response 422
- [ ] Check JSON format, pastikan valid
- [ ] Pastikan device_id = 1 ada di database
- [ ] Pastikan suhu 0-50, kelembapan 0-100
- Solusi: Test di Postman dulu sebelum dari ESP8266

---

## 📋 FINAL VERIFICATION

### Pre-Deployment Checklist
- [ ] Hardware terhubung (DHT11 ↔ ESP8266)
- [ ] WiFi connectivity working
- [ ] API tested di Postman → 200 OK
- [ ] Database structure verified
- [ ] Laravel server running
- [ ] Kode Arduino compiled & uploaded
- [ ] Serial Monitor showing data every 10 seconds
- [ ] Database getting new records

### Performance Baseline
- [ ] Sensor reading time: < 100ms
- [ ] WiFi reconnect time: < 5 seconds
- [ ] API response time: < 500ms
- [ ] Serial baud rate: 115200 bps

---

## 📚 REFERENCE FILES

Di folder project Anda:
- ✅ `PANDUAN_ESP8266_DHT11_INTEGRATION.md` - Panduan lengkap
- ✅ `API_DOCUMENTATION_ESP8266.md` - Dokumentasi API
- ✅ `esp8266_code_ready_to_use.ino` - Kode Arduino siap pakai
- ✅ `routes/api.php` - Routes sudah include `/api/monitoring/store`

---

## 🎉 SELESAI!

Jika semua checklist ✅, Anda sudah berhasil:
1. ✅ Menghubungkan ESP8266 + DHT11 ke WiFi
2. ✅ Membaca sensor suhu & kelembapan
3. ✅ Mengirim data ke API Laravel
4. ✅ Data masuk ke database

**Next Steps:**
- Integrasi dengan frontend untuk real-time display
- Setup alert jika suhu/kelembapan abnormal
- Deploy ke production dengan proper authentication
- Tambah multiple devices

---

**Waktu Total:** ~1-2 hari (tergantung familiar dengan Arduino & networking)

**Kesulitan:** Medium (untuk pemula yang pertama kali setup IoT)

**Support:** Refer to documentation files atau check serial monitor output for debugging
