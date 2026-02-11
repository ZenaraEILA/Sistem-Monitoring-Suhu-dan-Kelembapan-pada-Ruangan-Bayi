# AC Control Feature Documentation

## Ringkasan Fitur

Fitur AC Control memungkinkan petugas medis untuk mengontrol suhu AC ruangan bayi secara langsung dari dashboard website tanpa perlu mengatur AC secara manual. Sistem terintegrasi dengan ESP8266/ESP32 untuk mengirim perintah kontrol ke AC melalui IR blaster atau kontrol relai elektromekanik.

## Tujuan Fitur

1. **Mempercepat Respons**: Petugas dapat segera menyesuaikan suhu AC ketika suhu ruangan tidak sesuai standar
2. **Tracking & Audit**: Setiap perubahan suhu AC dicatat dalam database untuk audit trail
3. **Rekomendasi Otomatis**: Sistem memberikan rekomendasi otomatis ketika suhu diluar range normal
4. **Keamanan**: Hanya admin dan petugas yang dapat mengakses kontrol AC

## Komponen Sistem

### 1. Database Schema

#### Tabel: `ac_logs`
Menyimpan riwayat setiap kontrol AC yang dilakukan

```sql
CREATE TABLE ac_logs (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NOT NULL (FK: users)
  device_id BIGINT NOT NULL (FK: devices),
  action ENUM('increase', 'decrease', 'turn_on', 'turn_off'),
  ac_set_point FLOAT,
  status VARCHAR('success', 'failed'),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

#### Tabel: `devices` - Tambahan Field

```sql
ALTER TABLE devices ADD COLUMN
  ac_enabled BOOLEAN DEFAULT false,        // Enable/disable kontrol AC
  ac_set_point FLOAT DEFAULT 25.0,         // Suhu AC saat ini (set point)
  ac_status BOOLEAN DEFAULT false,         // Status AC (ON/OFF)
  ac_min_temp FLOAT DEFAULT 15.0,          // Suhu minimum AC
  ac_max_temp FLOAT DEFAULT 30.0,          // Suhu maksimum AC
  ac_api_url VARCHAR(255),                 // URL API ESP8266/ESP32
  ac_api_key VARCHAR(255)                  // API key untuk enkripsi/autentikasi
```

### 2. Models & Relationships

#### Model: `AcLog` (New)
```php
class AcLog extends Model {
  protected $fillable = [
    'user_id', 'device_id', 'action', 'ac_set_point', 'status'
  ];
  
  public function user() { }    // belongsTo User
  public function device() { }  // belongsTo Device
}
```

#### Model: `Device` (Updated)
```php
public function acLogs() {
  return $this->hasMany(AcLog::class);
}
```

### 3. Services

#### `AcControlService`

**Metode Utama:**

1. **increaseTemperature(Device, User)** - Naikkan suhu AC +1°C
2. **decreaseTemperature(Device, User)** - Turunkan suhu AC -1°C
3. **turnOn(Device, User)** - Nyalakan AC
4. **turnOff(Device, User)** - Matikan AC
5. **getTemperatureRecommendation(currentTemp, Device)** - Dapatkan rekomendasi otomatis
6. **getRecentLogs(Device)** - Ambil riwayat kontrol terbaru
7. **getControlSummary(Device)** - Statistik kontrol AC harian

**Flow Kontrol:**

```
User klik button
    ↓
AcControlService::controlAC()
    ↓
Validasi: ac_enabled, action valid, range suhu
    ↓
sendToESP() - kirim perintah ke ESP8266/ESP32
    ↓
If success:
  - Update device.ac_set_point
  - Create AcLog dengan status='success'
  - Return success response
Else:
  - Create AcLog dengan status='failed'
  - Return error response
```

### 4. API Endpoints

All endpoints require `auth:sanctum` middleware (authenticated user)

#### POST `/api/ac-control/increase`
Naikkan suhu AC
```json
Request:
{
  "device_id": 1
}

Response (Success):
{
  "success": true,
  "message": "AC berhasil dinaikkan",
  "data": {
    "ac_set_point": 26.0,
    "ac_status": true
  }
}
```

#### POST `/api/ac-control/decrease`
Turunkan suhu AC
```json
Request:
{
  "device_id": 1
}

Response (Success):
{
  "success": true,
  "message": "AC berhasil diturunkan",
  "data": {
    "ac_set_point": 24.0,
    "ac_status": true
  }
}
```

#### POST `/api/ac-control/turn-on`
Nyalakan AC
```json
Request:
{
  "device_id": 1
}

Response:
{
  "success": true,
  "message": "AC berhasil dinyalakan"
}
```

#### POST `/api/ac-control/turn-off`
Matikan AC
```json
Request:
{
  "device_id": 1
}

Response:
{
  "success": true,
  "message": "AC berhasil dimatikan"
}
```

#### GET `/api/ac-control/status?device_id=1`
Ambil status AC saat ini
```json
Response:
{
  "success": true,
  "data": {
    "ac_enabled": true,
    "ac_set_point": 25.0,
    "ac_status": true,
    "ac_min_temp": 15.0,
    "ac_max_temp": 30.0,
    "recommendation": {
      "action": "decrease",
      "message": "🌡️ Suhu tinggi! Klik untuk turunkan AC",
      "class": "btn-danger"
    },
    "summary": {
      "today_actions": 5,
      "today_success": 5,
      "today_failed": 0,
      "last_action": "2026-02-11T14:30:00"
    }
  }
}
```

#### GET `/api/ac-control/logs?device_id=1&limit=10`
Ambil riwayat kontrol AC
```json
Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "device_id": 1,
      "action": "increase",
      "ac_set_point": 26.0,
      "status": "success",
      "created_at": "2026-02-11T14:30:00",
      "user": { "name": "Petugas Medis" }
    }
  ]
}
```

### 5. Dashboard UI Components

#### AC Control Widget
Widget interaktif di setiap device card menampilkan:

1. **Status AC** - Badge menunjukkan AKTIF/TIDAK AKTIF
2. **Set Point Display** - Menampilkan suhu AC saat ini
3. **Control Buttons**:
   - 🔽 Turunkan - Kurangi suhu AC
   - 🔌 Toggle - Nyalakan/Matikan AC
   - 🔼 Naikkan - Naikkan suhu AC
4. **Temperature Recommendation** - Saran otomatis jika suhu diluar range
5. **Recent Actions Log** - 3 tindakan terbaru dengan timestamp dan nama petugas

#### Features
- Real-time button feedback (loading state)
- Alert success/error notifications
- Disabled button ketika AC OFF (untuk increase/decrease)
- AJAX request tanpa reload page (optional, bisa reload setelah aksion)
- Responsive design untuk mobile

### 6. Authorization & Access Control

**Role-Based Access:**
- ✅ Admin - Full access ke kontrol AC
- ✅ Petugas - Full access ke kontrol AC
- ❌ Dokter - Read-only (lihat logs tapi tidak bisa kontrol)
- ❌ Guest - No access

**Authorization Check:**
```php
// di AcControlController
private function authorize() {
  $allowedRoles = ['admin', 'petugas'];
  if (!in_array(Auth::user()->role, $allowedRoles)) {
    abort(403, 'Unauthorized');
  }
}
```

### 7. Integrasi dengan ESP8266/ESP32

#### Request Format ke ESP

```json
POST /kontrol-ac (atau endpoint sesuai konfigurasi)

{
  "device_id": "DEVICE_XXXXX_1234567890",
  "action": "increase",           // increase|decrease|turn_on|turn_off
  "set_point": 26.0,
  "api_key": "xxxxxxxxxxxxx",
  "timestamp": "2026-02-11T14:30:00Z"
}
```

#### Configuration di Database

Setiap device harus dikonfigurasi:
1. `ac_api_url` - URL ESP8266 (contoh: `http://192.168.1.100/api/ac-control`)
2. `ac_api_key` - API key untuk autentikasi
3. `ac_min_temp` & `ac_max_temp` - Range suhu AC

#### Flow ESP8266:

```
1. Terima request dari Laravel
2. Validasi API key
3. Parse action (increase/decrease/turn_on/turn_off)
4. Kontrol AC via:
   - IR Blaster (untuk AC infrared)
   - Relay Electrical (untuk control on/off)
   - Thermostat Communication (untuk set temperature)
5. Send response back ke Laravel:
   {
     "success": true,
     "status": "ok",
     "current_temp": 26.0
   }
```

### 8. Setup Instructions

#### 1. Migration Database
```bash
php artisan migrate
```

#### 2. Enable AC Control untuk Device
```php
// Di seeder atau manual via tinker
$device = Device::find(1);
$device->update([
  'ac_enabled' => true,
  'ac_set_point' => 25.0,
  'ac_min_temp' => 15.0,
  'ac_max_temp' => 30.0,
  'ac_api_url' => 'http://192.168.1.100/api/ac-control',
  'ac_api_key' => 'your-secret-key-here'
]);
```

#### 3. Configure ESP8266/ESP32
Upload firmware ke ESP dengan:
- Endpoint `/api/ac-control` atau custom sesuai `ac_api_url`
- Accept POST request dengan JSON
- Control AC relay/IR blaster sesuai aksi yang diterima

#### 4. Test via API
```bash
curl -X POST http://localhost:8000/api/ac-control/increase \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"device_id": 1}'
```

### 9. Fitur Keamanan

1. **Authentication**: Hanya user yang login (memiliki token) bisa akses
2. **Authorization**: Hanya admin & petugas yang authorized
3. **API Key**: ESP8266 di-protect dengan API key
4. **Rate Limiting**: (Optional) Limit jumlah request per user
5. **Logging**: Setiap aksi dicatat lengkap (user, aksi, timestamp, status)
6. **Validation**: 
   - Range suhu di-validate (tidak boleh < ac_min_temp atau > ac_max_temp)
   - Action di-validate (hanya accept: increase, decrease, turn_on, turn_off)
   - Device check (device harus ada dan ac_enabled = true)

### 10. Monitoring & Reporting

#### Admin dapat melihat:
1. **AC Control Logs** - Riwayat lengkap siapa yang atur AC dan kapan
2. **Daily Summary** - Total kontrol AC per device per hari
3. **By User Report** - Laporan kontrol AC berdasarkan petugas
4. **Success/Failure Rate** - Persentase kontrol AC yang berhasil

#### Query Examples:
```php
// Semua kontrol AC hari ini
AcLog::whereDate('created_at', today())->get();

// Kontrol AC per device per user
AcLog::where('device_id', $deviceId)
     ->where('user_id', $userId)
     ->orderByDesc('created_at')
     ->get();

// Failed actions
AcLog::where('status', 'failed')
     ->whereDate('created_at', today())
     ->get();
```

## Troubleshooting

### Error: "AC control tidak dikonfigurasi untuk device ini"
**Solusi**: Set `ac_enabled = true` dan `ac_api_url` untuk device di database

### Error: "Gagal menghubungi ESP8266"
**Solusi**: 
- Cek koneksi WiFi ESP8266
- Verifikasi `ac_api_url` sudah benar
- Test endpoint ESP8266 secara direct

### Button tidak responsif
**Solusi**:
- Clear browser cache
- Check browser console untuk error
- Verify user authenticated dengan token

### AC Actions tidak tersimpan di database
**Solusi**:
- Verify `ac_logs` table exists (run migration)
- Check database connection Laravel

## Future Enhancements

1. **Scheduling**: Jadwal otomatis ubah suhu AC per jam
2. **Notifications**: Notifikasi email/SMS ketika AC action gagal
3. **History Reports**: Export AC control history ke PDF
4. **Integration**: Integrasi dengan smart thermostat
5. **ML Predictions**: Prediksi kapan AC harus diatur berdasarkan pattern
6. **Two-Factor Action**: Konfirmasi 2 step untuk aksi critical

## Support

Untuk masalah atau pertanyaan tentang AC Control, hubungi tim development.
