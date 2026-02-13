# 📡 DOKUMENTASI API ESP8266 INTEGRATION

## 🎯 Endpoint yang Tersedia

### **1. Menerima Data dari ESP8266 (Create Monitoring)**

**Endpoint:**
```
POST /api/monitoring
POST /api/monitoring/store   ← Gunakan for ESP8266
```

**URL Lengkap:**
```
http://127.0.0.1:8000/api/monitoring/store
```

**Method:** `POST`

**Content-Type:** `application/json`

**Request Body:**
```json
{
  "device_id": 1,
  "temperature": 25.5,
  "humidity": 60.3
}
```

**Parameter Penjelasan:**
| Parameter | Tipe | Required | Contoh | Keterangan |
|-----------|------|----------|--------|-----------|
| device_id | Integer | ✅ Ya | 1 | ID device di database (match dengan devices table) |
| temperature | Float | ✅ Ya | 25.5 | Nilai suhu dalam °C (0-50) |
| humidity | Float | ✅ Ya | 60.3 | Nilai kelembapan dalam % (0-100) |

**Success Response (HTTP 200):**
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

**Error Response (HTTP 422 - Validation Error):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "device_id": ["The device_id field is required."],
    "temperature": ["The temperature must be between 0 and 50."],
    "humidity": ["The humidity must be between 0 and 100."]
  }
}
```

**Error Response (HTTP 404 - Device Not Found):**
```json
{
  "success": false,
  "message": "Device not found"
}
```

---

### **2. Mengambil Data Monitoring Terakhir (Get Latest)**

**Endpoint:**
```
GET /api/monitoring/{deviceId}
```

**URL Contoh:**
```
http://127.0.0.1:8000/api/monitoring/1
```

**Method:** `GET`

**Path Parameters:**
| Parameter | Tipe | Contoh | Keterangan |
|-----------|------|--------|-----------|
| deviceId | Integer | 1 | ID device |

**Response (HTTP 200):**
```json
{
  "success": true,
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

---

## 🧪 Testing dengan Postman

### **Langkah 1: Instal Postman**
Download: https://www.postman.com/downloads/

### **Langkah 2: Buat Collection Baru**
```
1. Klik "+ New"
2. Pilih "Collection"
3. Beri nama: "ESP8266 Monitoring"
4. Klik "Create"
```

### **Langkah 3: Buat Request POST (Testing Kirim Data)**

**Request 1: Kirim data suhu dan kelembapan**

1. Klik tombol "+" atau "Add Request"
2. Beri nama: "Create Monitoring Data"

**Atur Request:**

**Tab: Authorization**
- Type: None (tidak perlu auth sama sekali)

**Tab: Headers**
```
Key: Content-Type
Value: application/json
```

**Tab: Body**
- Pilih: `raw` → `JSON`
- Copy-paste JSON ini:

```json
{
  "device_id": 1,
  "temperature": 25.5,
  "humidity": 60.3
}
```

**URL:**
```
http://127.0.0.1:8000/api/monitoring/store
```

**Step 4: Klik Send**

**Expected Response:**
```
Status: 200 OK
Body:
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

### **Langkah 4: Buat Request GET (Testing Ambil Data)**

**Request 2: Ambil data monitoring terbaru**

1. Klik "+" untuk request baru
2. Beri nama: "Get Latest Monitoring"

**Atur Request:**

**Method:** GET

**URL:**
```
http://127.0.0.1:8000/api/monitoring/1
```

**Step 2: Klik Send**

**Expected Response:**
```
Status: 200 OK
Body:
{
  "success": true,
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

---

## 📝 Test Cases

### **Test Case 1: Data Valid**

**Input:**
```json
{
  "device_id": 1,
  "temperature": 25.5,
  "humidity": 60.3
}
```

**Expected Result:**
- Status: 200 OK
- Message: "Data monitoring berhasil disimpan"
- Data masuk ke database

---

### **Test Case 2: Device ID Tidak Ada**

**Input:**
```json
{
  "device_id": 999,
  "temperature": 25.5,
  "humidity": 60.3
}
```

**Expected Result:**
- Status: 404 Not Found
- Message: "Device not found"

---

### **Test Case 3: Suhu di Luar Range**

**Input:**
```json
{
  "device_id": 1,
  "temperature": -10,
  "humidity": 60.3
}
```

**Expected Result:**
- Status: 422 Unprocessable Entity
- Error: "The temperature must be between 0 and 50"

---

### **Test Case 4: Kelembapan di Luar Range**

**Input:**
```json
{
  "device_id": 1,
  "temperature": 25.5,
  "humidity": 150
}
```

**Expected Result:**
- Status: 422 Unprocessable Entity
- Error: "The humidity must be between 0 and 100"

---

### **Test Case 5: Field Kosong**

**Input:**
```json
{
  "device_id": 1,
  "temperature": null,
  "humidity": 60.3
}
```

**Expected Result:**
- Status: 422 Unprocessable Entity
- Error: "The temperature field is required"

---

## 🌐 Testing dengan cURL (Command Line)

**Windows Command Prompt:**

```bash
curl -X POST http://127.0.0.1:8000/api/monitoring/store ^
  -H "Content-Type: application/json" ^
  -d "{\"device_id\": 1, \"temperature\": 25.5, \"humidity\": 60.3}"
```

**PowerShell:**

```powershell
$body = @{
    device_id = 1
    temperature = 25.5
    humidity = 60.3
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/monitoring/store" `
    -Method POST `
    -Headers @{"Content-Type"="application/json"} `
    -Body $body
```

---

## 🔍 Debugging Response

Jika API tidak bekerja, perhatikan response ini:

| Status | Meaning | Solusi |
|--------|---------|--------|
| 200 OK | ✅ Berhasil | - |
| 400 Bad Request | Syntax JSON salah | Cek format JSON menggunakan JSONLint |
| 404 Not Found | Endpoint tidak ada | Cek URL, pastikan `/api/monitoring/store` |
| 422 Unprocessable | Validasi gagal | Cek parameter: device_id, temperature, humidity |
| 500 Server Error | Error di Laravel | Lihat error di terminal/console Laravel |

---

## 💾 Verifikasi di Database

Setelah POST berhasil, cek database:

**Method 1: phpMyAdmin**
```
http://localhost/phpmyadmin
→ Database: monitoring_suhu_bayi
→ Table: monitorings
→ Browse data
```

**Method 2: MySQL Command**
```bash
mysql -u root monitoring_suhu_bayi -e "SELECT * FROM monitorings ORDER BY id DESC LIMIT 1;"
```

**Method 3: Laravel Artisan Tinker**
```bash
php artisan tinker
>>> \App\Models\Monitoring::latest()->first();
```

---

## 📊 Response Time

Normal response time:
- **10-50ms**: Sangat cepat (lokal network)
- **50-200ms**: Normal
- **> 500ms**: Lambat, cek koneksi

---

## 🛡️ Security Notes

Current setup (untuk localhost testing):
- ✅ Tidak memerlukan authentication
- ✅ Tidak validasi CORS (lokal)
- ⚠️ **JANGAN deploy ke production tanpa authentication!**

Untuk production:
- Tambahkan API token/authentication
- Enable CORS dengan whitelist IP
- Rate limit untuk prevent abuse
- HTTPS (SSL) certificate

---

## 🚀 Next Steps

1. Test manual di Postman (manual POST)
2. Upload kode ESP8266
3. Monitor Serial Output
4. Cek data di database
5. Integrate dengan frontend Laravel

---

**Need Help?**
- Cek PANDUAN_ESP8266_DHT11_INTEGRATION.md untuk troubleshooting lengkap
- Lihat serial output ESP8266 untuk error details
- Test di Postman dulu sebelum dari ESP8266
