# 🔧 ESP8266 Configuration Guide - Device 2 (Ruangan B1)

## ⚠️ IMPORTANT: Device 2 tidak bisa connect!

### Masalah yang ditemukan:
1. ❌ **IP Address SALAH**: `192.168.2.102` (seharusnya `192.168.186.241`)
2. ❌ **Device ID SALAH**: Numeric `2` (seharusnya string `DEVICE_5VGP9BAM7C_1771067547`)

### Status Perbaikan:
- ✅ IP address sudah di-update: `192.168.186.241`
- ✅ Device ID sudah di-update: `DEVICE_5VGP9BAM7C_1771067547`

---

## 📋 Updated Configuration untuk Device 2

File: `esp8266_code_ready_to_use.ino`

### WiFi Configuration (Lines 11-12):
```cpp
const char* ssid = "monitoring_suhu";
const char* password = "11111111";
```
✅ Status: SUDAH BENAR

### Server Configuration (Lines 15-18):
```cpp
const char* serverIP = "192.168.186.241";     // ✅ FIXED
const int serverPort = 8000;
const char* apiEndpoint = "/api/monitoring/store";
```
✅ Status: SUDAH DIPERBAIKI

### Device Configuration (Lines 20-21):
```cpp
const char* deviceId = "DEVICE_5VGP9BAM7C_1771067547";  // ✅ FIXED - Unique device ID
const int sendInterval = 10000;
```
✅ Status: SUDAH DIPERBAIKI

### DHT11 Pin Configuration (Lines 5-6):
```cpp
#define DHTPIN D4       // GPIO2
#define DHTTYPE DHT11
```
✅ Status: SUDAH BENAR

---

## 🚀 Steps untuk Deploy ke ESP Device 2

### 1. Persiapan Hardware
- [ ] ESP8266 D1 Mini tersedia
- [ ] DHT11 sensor tersedia
- [ ] Micro USB cable untuk upload
- [ ] Komputer dengan Arduino IDE installed

### 2. Koneksi Hardware
```
ESP8266      →    DHT11
─────────────────────────
D4 (GPIO2)   →    Data Pin
3V3 (VCC)    →    VCC (+)
GND          →    GND (-)
```

### 3. Update Board Configuration di Arduino IDE
- Tools → Board → Select "NodeMCU 1.0 (ESP8266)"
- Tools → Port → Select COM port ESP
- Tools → Upload Speed → 115200

### 4. Install Required Libraries
Sketch → Include Library → Manage Libraries:
- Search: "DHT" → Install "DHT sensor library by Adafruit"
- Search: "ArduinoJson" → Install version 6.x or 7.x
- Search: "ESP8266" → Already installed with board

### 5. Edit & Upload Code
1. Open `esp8266_code_ready_to_use.ino` di Arduino IDE
2. Verify configuration (baris 11-21):
   - WiFi SSID & Password ✅
   - Server IP: `192.168.186.241` ✅
   - Device ID: `DEVICE_5VGP9BAM7C_1771067547` ✅
3. Sketch → Upload
4. Wait untuk "Done uploading" message

### 6. Monitor in Serial Monitor
- Tools → Serial Monitor
- Baud Rate: 115200
- Watch untuk output:
  ```
  === SISTEM MONITORING SUHU & KELEMBAPAN ===
  Menginisialisasi DHT11...
  [WiFi connecting...]
  ✅ WiFi connected!
  [Sending data to server...]
  ✅ Response 201 Created!
  ```

---

## ✅ Verification Checklist

Setelah upload, verify di server:

```bash
# 1. Check if Device 2 receiving data
curl http://192.168.186.241:8000/api/monitoring/realtime/latest?device_id=7

# Response akan menunjukkan:
# - temperature: [suhu dari sensor]
# - humidity: [kelembapan dari sensor]
# - esp_status: "ONLINE"
# - seconds_ago: < 10
```

Atau buka dashboard:
```
http://192.168.186.241:8000/dashboard
```

Di kanan atas dropdown, pilih "Ruangan B1" → should show:
- ✅ Temperature: Real-time value
- ✅ Humidity: Real-time value
- ✅ ESP Status: ONLINE (green)

---

## 🐛 Troubleshooting

### Issue 1: Upload failed
**Solution:**
- [ ] Check USB cable connection
- [ ] Select correct COM port
- [ ] Select correct board: "NodeMCU 1.0 (ESP8266)"

### Issue 2: Serial Monitor shows "WiFi terputus"
**Solution:**
- [ ] Check WiFi SSID: `monitoring_suhu`
- [ ] Check WiFi password: `11111111`
- [ ] Check WiFi accessible from location
- [ ] Check WiFi strength (near router/access point)

### Issue 3: Sensor reading error: "❌ Sensor DHT11 tidak merespons"
**Solution:**
- [ ] Check DHT11 is connected to D4 (GPIO2)
- [ ] Check 3V3 power supply
- [ ] Check GND connection
- [ ] Check data pin not shorted
- [ ] Replace DHT11 sensor if defective

### Issue 4: Server connection failed: "❌ Gagal terhubung ke server!"
**Solution:**
- [ ] Check server IP: `192.168.186.241` ✅
- [ ] Check server running: `php artisan serve --host=0.0.0.0 --port=8000`
- [ ] Ping server: `ping 192.168.186.241`
- [ ] Check firewall not blocking port 8000
- [ ] Check network connectivity: ESP & server on same WiFi/network

### Issue 5: Server responds but no data in database
**Solution:**
- [ ] Check Device ID: `DEVICE_5VGP9BAM7C_1771067547` ✅
- [ ] Check API endpoint: `/api/monitoring/store` ✅
- [ ] Check database connection
- [ ] Check Laravel logs: `tail -f storage/logs/laravel.log`

---

## 📊 Expected Behavior

### When ESP is Running Correctly:

**Serial Monitor Output:**
```
=== SISTEM MONITORING SUHU & KELEMBAPAN ===
Menginisialisasi DHT11...
Koneksi ke WiFi "monitoring_suhu"
✅ WiFi connected!
IP: 192.168.1.100
Signal: -45 dBm

🌡 Pembacaan sensor:
  - Suhu: 26.5°C
  - Kelembapan: 55%

✅ Koneksi ke server berhasil!
📊 JSON: {"device_id":"DEVICE_5VGP9BAM7C_1771067547","temperature":26.5,"humidity":55}
📤 Response Code: 201
✅ Data berhasil dikirim!
```

**Dashboard Display:**
```
Ruangan B1 (Device 2)
┌─────────────────────┐
│ 🌡 Suhu: 26.5°C     │
│ 💧 Kelembapan: 55%  │
│ 📡 Status: ONLINE ✅ │
│ ⏰ 3 detik lalu     │
└─────────────────────┘
```

---

## 🔄 Configuration Summary

| Parameter | Value | Status |
|-----------|-------|--------|
| WiFi SSID | monitoring_suhu | ✅ |
| WiFi Password | 11111111 | ✅ |
| Server IP | 192.168.186.241 | ✅ FIXED |
| Server Port | 8000 | ✅ |
| API Endpoint | /api/monitoring/store | ✅ |
| Device ID | DEVICE_5VGP9BAM7C_1771067547 | ✅ FIXED |
| DHT Pin | D4 (GPIO2) | ✅ |
| Send Interval | 10 detik | ✅ |

---

## 📞 Support

Jika setelah semua fix masih tidak jalan:

1. **Check Serial Monitor output** - lihat error message
2. **Check Server Logs** - `tail -f storage/logs/laravel.log`
3. **Test API manually**:
   ```bash
   curl -X POST http://192.168.186.241:8000/api/monitoring/store \
     -H "Content-Type: application/json" \
     -d '{
       "device_id": "DEVICE_5VGP9BAM7C_1771067547",
       "temperature": 26.5,
       "humidity": 55
     }'
   ```
4. **Check database**: Device exist di `devices` table?

---

## ✅ Status: CONFIGURATION FIXED

File sudah di-update dengan konfigurasi yang benar.

**Next Step:** Upload code ke ESP8266 Device 2 dan monitor hasilnya di Serial Monitor.

---

*Last Updated: 2026-02-14*  
*For: Device 2 (Ruangan B1)*  
*Status: Ready for upload* ✅
