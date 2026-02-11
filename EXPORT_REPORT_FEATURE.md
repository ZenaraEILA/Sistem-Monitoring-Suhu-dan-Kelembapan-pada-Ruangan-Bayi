# 📊 Fitur Export Laporan Monitoring

## 🎯 Ringkasan Fitur

Fitur Export Laporan adalah sistem komprehensif untuk menghasilkan laporan monitoring suhu dan kelembapan dalam format PDF dan Excel. Laporan ini dirancang khusus untuk kebutuhan medis rumah sakit, mencakup statistik detail, grafik visual, dan catatan dokter.

### Versi
- **Version**: 1.0
- **Tanggal**: Februari 2026
- **Status**: ✅ Production Ready

---

## 📋 Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Tipe Laporan](#tipe-laporan)
3. [Format Laporan](#format-laporan)
4. [Isi Laporan](#isi-laporan)
5. [Cara Menggunakan](#cara-menggunakan)
6. [Arsitektur Teknis](#arsitektur-teknis)
7. [Troubleshooting](#troubleshooting)

---

## 🚀 Fitur Utama

### 1. Export dalam 2 Format
- **📄 PDF**: Profesional, siap cetak, berkualitas tinggi
- **📊 Excel**: Interaktif, mudah dianalisis, support pivot table

### 2. 3 Tipe Laporan
- **Harian**: Data monitoring untuk 1 hari, cocok untuk shift report
- **Mingguan**: Data 7 hari berturut-turut, untuk weekly review
- **Bulanan**: Data 1 bulan penuh, untuk audit dan arsip

### 3. Konten Otomatis
- Grafik visual yang embedded langsung dalam PDF
- Statistik ringkas untuk dokter
- Tabel detail dengan 2000+ data points per laporan
- Incident markers dan catatan dokter

### 4. Filter Fleksibel
- Pilih ruangan/device
- Pilih tanggal laporan
- Opsional: filter jam (dalam pengembangan)

---

## 📰 Tipe Laporan

### A. Laporan Harian (Daily Report)

**Gunakan untuk:**
- Shift handover antar petugas
- Laporan rutin ke dokter jaga
- Monitoring kondisi bayi per hari

**Konten:**
- Data monitoring dari jam 00:00 - 23:59
- ~200-500 data points (5-15 menit per point)
- Grafik trend suhu & kelembapan harian

**Ukuran File:**
- PDF: 2-3 MB
- Excel: 200-400 KB

**Contoh Penggunaan:**
```
Tanggal: 11 Februari 2026
Ruangan: Bayi - Ruang Perawatan A
Dicetak oleh: Perawat Siti
Tujuan: Laporan shift sore ke malam
```

### B. Laporan Mingguan (Weekly Report)

**Gunakan untuk:**
- Review performa mingguan
- Analisis trend suhu dalam 7 hari
- Laporan kepada supervisor

**Konten:**
- Data monitoring 7 hari berturut-turut
- ~1500-3000 data points
- Grafik trend mingguan
- Summary statistik per hari

**Ukuran File:**
- PDF: 5-7 MB
- Excel: 1-2 MB

**Contoh Penggunaan:**
```
Periode: 5 Februari - 11 Februari 2026
Ruangan: Bayi - Ruang Perawatan A
Dicetak oleh: Supervisor Perawatan
Tujuan: Laporan mingguan ke manajemen
```

### C. Laporan Bulanan (Monthly Report)

**Gunakan untuk:**
- Laporan bulanan resmi
- Arsip medis institusi
- Audit dan evaluasi kualitas
- Dokumentasi regulasi kesehatan

**Konten:**
- Data monitoring 1 bulan penuh
- ~8000-50000 data points
- Grafik trend bulanan
- Semua incident markers
- Semua catatan dokter

**Ukuran File:**
- PDF: 10-20 MB
- Excel: 5-10 MB

**Contoh Penggunaan:**
```
Periode: 1 Februari - 29 Februari 2026
Ruangan: Bayi - Ruang Perawatan A
Dicetak oleh: Dokter Kepala Unit
Tujuan: Arsip resmi & laporan akreditasi
```

---

## 📊 Format Laporan

### Format PDF

**Keunggulan:**
- Profesional dan rapi
- Siap untuk dicetak
- Bisa langsung dikirim ke dokter
- Support embedded images (charts)
- Signature-ready format

**Struktur:**
```
┌─────────────────────────────────────────┐
│  LAPORAN MONITORING SUHU & KELEMBAPAN   │
│       RUANGAN BAYI - RUMAH SAKIT        │
├─────────────────────────────────────────┤
│ 📅 Informasi Laporan                    │
│  - Tipe: Harian/Mingguan/Bulanan       │
│  - Periode: 11/02/2026                 │
│  - Ruangan: Bayi - Perawatan A         │
│  - Dicetak: 11/02/2026 14:30:00        │
│  - Oleh: Perawat Siti                  │
├─────────────────────────────────────────┤
│ 📈 RINGKASAN STATISTIK PENTING          │
│                                         │
│ 🌡️ SUHU (°C)                            │
│  Maksimal: 32.5°C                      │
│  Minimal: 24.3°C                       │
│  Rata-rata: 28.2°C                     │
│                                         │
│ 💧 KELEMBAPAN (%)                      │
│  Maksimal: 68%                         │
│  Minimal: 45%                          │
│  Rata-rata: 55.2%                      │
│                                         │
│ Status Aman: 145 kali                 │
│ Status Tidak Aman: 3 kali              │
│ % Tidak Aman: 2.1%                     │
│ Waktu Respons Rata-rata: 5.4 menit     │
├─────────────────────────────────────────┤
│ 📊 GRAFIK MONITORING (IMAGE)            │
│ [Chart image embedded here]             │
├─────────────────────────────────────────┤
│ 📋 DATA DETAIL MONITORING               │
│ [table with hundreds of records]        │
├─────────────────────────────────────────┤
│ ⚠️ KEJADIAN PENTING                     │
│ [incident markers if any]               │
├─────────────────────────────────────────┤
│ 📝 CATATAN DOKTER                       │
│ [doctor notes if any]                   │
├─────────────────────────────────────────┤
│ 📄 Dokumen ini adalah laporan resmi...  │
└─────────────────────────────────────────┘
```

### Format Excel

**Keunggulan:**
- Mudah dianalisis lebih lanjut
- Support formula dan pivot table
- Bisa custom kolom dan filter
- Ideal untuk statistik kompleks
- Compatible dengan Excel 2010+

**Struktur Sheet:**
```
Row 1: LAPORAN MONITORING SUHU DAN KELEMBAPAN
Row 2: [blank]
Row 3: Tipe Laporan | HARIAN
Row 4: Nama Ruangan | Bayi - Perawatan A
Row 5: Lokasi | Ruang 101
Row 6: Periode | 11/02/2026 - 11/02/2026
Row 7: Dicetak pada | 11/02/2026 14:30:00
Row 8: Dicetak oleh | Perawat Siti
Row 9: [blank]
Row 10: RINGKASAN STATISTIK
Row 11: Total Data Point | 148
...
Row 30: DATA DETAIL MONITORING
Row 31: Tanggal/Waktu | Suhu (°C) | Kelembapan (%) | Status | ...
Row 32: 11/02/2026 00:05:00 | 28.5 | 52.3 | Aman | ...
Row 33: 11/02/2026 00:10:00 | 28.4 | 52.5 | Aman | ...
...
```

---

## 📑 Isi Laporan

### A. Informasi Umum

**Selalu Tercakup:**
- ✅ Nama dan lokasi ruangan/device
- ✅ Nama device monitoring
- ✅ Tanggal laporan dibuat
- ✅ Nama petugas yang membuat laporan
- ✅ Waktu cetak laporan
- ✅ Tipe laporan (Harian/Mingguan/Bulanan)

**Contoh:**
```
Ruangan: Bayi - Ruang Perawatan A
Lokasi: Lantai 3 Gedung C
Device: TEMPERATURE_SENSOR_A1
Periode: 11 Februari 2026
Dicetak: 11 Februari 2026, 14:30:00
Operator: Perawat Siti (ID: 12345)
Tipe: Laporan Harian
```

### B. Ringkasan Statistik (Summary for Doctors)

Statistik otomatis dihitung dari data monitoring:

**1. Suhu (°C)**
- Suhu Maksimal: Nilai tertinggi selama periode
- Suhu Minimal: Nilai terendah selama periode
- Rata-rata Suhu: Mean nilai suhu
- Normal Range: 15-30°C untuk bayi

**2. Kelembapan (%)**
- Kelembapan Maksimal: Nilai tertinggi
- Kelembapan Minimal: Nilai terendah
- Rata-rata Kelembapan: Mean nilai
- Normal Range: 35-60% untuk bayi

**3. Status Monitoring**
- Status Aman: Jumlah record dengan status OK
- Status Tidak Aman: Jumlah record dengan alert
- Persentase Tidak Aman: (Tidak Aman / Total) * 100%
- Rata-rata Waktu Respons: Waktu untuk intervensi

**Contoh Ringkasan:**
```
╔═══════════════════════════════════════╗
║     RINGKASAN STATISTIK PENTING       ║
╠═══════════════════════════════════════╣
║ 🌡️ SUHU (°C)                          ║
║ Maksimal: 32.5°C                     ║
║ Minimal: 24.3°C                      ║
║ Rata-rata: 28.2°C ✓ Normal           ║
║                                       ║
║ 💧 KELEMBAPAN (%)                    ║
║ Maksimal: 68% ⚠️ Sedikit tinggi       ║
║ Minimal: 45%                         ║
║ Rata-rata: 55.2% ✓ Normal            ║
║                                       ║
║ 📊 STATUS                             ║
║ Aman: 145 kali                       ║
║ Tidak Aman: 3 kali                   ║
║ % Tidak Aman: 2.1%                   ║
║                                       ║
║ ⏱️ RESPONS PETUGAS                     ║
║ Waktu Rata-rata: 5.4 menit           ║
║ Tercepatm: 2 menit                   ║
║ Terlambat: 12 menit                  ║
╚═══════════════════════════════════════╝
```

### C. Grafik Visual

**Feature:**
- Grafik Monitoring: Line chart dengan dual-axis (suhu + kelembapan)
- Grafik Status: Pie chart distribusi status (Aman vs Tidak Aman)
- Chart Image: Embedded sebagai base64 PNG dalam PDF
- Responsif: Scale otomatis sesuai data

**Data yang ditampilkan:**
- Suhu per jam (aggregated dari detail points)
- Kelembapan per jam
- Status color coding (green=safe, red=unsafe)
- Legend dan label otomatis

### D. Tabel Data Detail

**Kolom:**
1. **Tanggal/Waktu**: Timestamp lengkap (dd/mm/yyyy HH:mm:ss)
2. **Suhu (°C)**: Nilai suhu dengan 2 desimal
3. **Kelembapan (%)**: Nilai kelembapan dengan 2 desimal
4. **Status**: "Aman" atau "Tidak Aman"
5. **Rekomendasi**: Saran otomatis berdasar nilai (PDF only)
6. **Tindakan Perawat**: Aksi yang diambil jika ada
7. **Waktu Respons**: Berapa menit untuk respons

**Contoh Baris:**
```
11/02/2026 14:05:00 | 28.5 | 52.3 | Aman | Stabil | - | -
11/02/2026 14:10:00 | 29.2 | 51.8 | Aman | Optimal | - | -
11/02/2026 14:15:00 | 31.5 | 61.2 | Tidak Aman | Periksa AC | Add coolant | 5.2 min
```

### E. Incident Markers (Jika Ada)

**Informasi:**
- Waktu kejadian (timestamp)
- Tipe incident (Manual/Auto)
- Deskripsi kejadian
- Dampak dan status

**Contoh:**
```
⚠️ KEJADIAN PENTING

[11/02/2026 14:15:00] - Temperature Spike
Deskripsi: Suhu naik drastis ke 35°C, AC mungkin bermasalah
Aksi: Perawat sudah mengecek AC, coolant ditambah
Status Resolved: Ya

[11/02/2026 19:30:00] - Humidity Alert
Deskripsi: Kelembapan turun ke 30%, melebihi ambang bawah
Aksi: Humidifier dinyalakan
Status: Monitoring
```

### F. Catatan Dokter (Jika Ada)

**Konten:**
- Catatan dari dokter spesialis
- Observasi klinis
- Rekomendasi treatment
- Instruksi khusus

**Contoh:**
```
📝 CATATAN DARI DOKTER

[11/02/2026] Dr. Budi
Pasien bayi menunjukkan respons baik terhadap therapy. Suhu stabil di 28-29°C,
kelembapan ideal 50-55%. Lanjutkan monitoring setiap jam. Update status setiap shift.

Instruksi: 
- Monitor ketat untuk 48 jam ke depan
- Hubungi dokter jika suhu > 32°C
- Ganti coolant jika perlu
```

---

## 👨‍💻 Cara Menggunakan

### 1. Akses Menu Export

**Dari Dashboard:**
1. Login ke sistem
2. Klik tombol "📊 Export Laporan" di bagian atas
3. Atau pergi ke menu "LAPORAN" > "Export Laporan"

**URL langsung:**
```
http://sistem-monitoring-bayi.local/reports
```

### 2. Buat Laporan Harian

**Step-by-step:**

```
1. Pilih Tipe Laporan: "Laporan Harian"
   ↓
2. Pilih Ruangan/Device: Dropdown dengan daftar semua device
   Contoh: "Bayi - Ruang Perawatan A (Ruang 101)"
   ↓
3. Pilih Tanggal: Date picker, default hari ini
   Contoh: 11/02/2026
   ↓
4. Pilih Format:
   • PDF: Untuk cetak & distribusi formal
   • Excel: Untuk analisis & pivot table
   ↓
5. Klik "Unduh Laporan"
   ↓
6. File langsung download ke folder Downloads
   Filename: Laporan-Harian-{device_name}-{date}.pdf
```

**Tips:**
- Gunakan PDF untuk laporan shif handover
- Gunakan Excel jika perlu analisis lebih detail
- Bisa download laporan hari kemarin dengan mengubah tanggal

### 3. Buat Laporan Mingguan

**Step-by-step:**

```
1. Pilih Tipe Laporan: "Laporan Mingguan"
   ↓
2. Pilih Ruangan/Device: Sama seperti harian
   ↓
3. Pilih Hari Pertama Minggu: 
   Sistem akan ambil data 7 hari ke depan
   Contoh: Jika pilih 09/02/2026
   Data akan cover: 09/02 - 15/02/2026
   ↓
4. Pilih Format: PDF atau Excel
   ↓
5. Download file
   Filename: Laporan-Mingguan-{device_name}-{start_date}.pdf
```

**Tips:**
- Pilih hari Senin untuk minggu yang lengkap
- Cocok untuk meeting weekly review
- Excel format lebih cocok untuk statistik

### 4. Buat Laporan Bulanan

**Step-by-step:**

```
1. Pilih Tipe Laporan: "Laporan Bulanan"
   ↓
2. Pilih Ruangan/Device: Device yang ingin dilaporkan
   ↓
3. Pilih Bulan & Tahun:
   Contoh: Februari 2026
   Sistem akan otomatis cover: 01/02 - 29/02/2026
   ↓
4. Pilih Format: PDF atau Excel
   ↓
5. Download file (ukuran mungkin 10+ MB)
   Filename: Laporan-Bulanan-{device_name}-2026-02.pdf
```

**Tips:**
- Gunakan untuk arsip bulanan
- Cocok untuk audit dan laporan akreditasi
- Wait 1-2 menit karena file lebih besar

### 5. Editing Laporan (Optional)

**Setelah Download:**

**PDF:**
- Bisa tambah signature digital
- Bisa print dengan watermark
- Readonly format (aman untuk arsip)

**Excel:**
- Bisa add filter & sort
- Bisa buat pivot table
- Bisa add column custom
- Bisa export ke format lain

---

## 🏗️ Arsitektur Teknis

### Struktur Folder

```
sistem-monitoring-suhu-bayi/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── ReportController.php        # Main controller
│   └── Services/
│       ├── ChartService.php                # Chart generator (PNG)
│       ├── ExcelExportService.php          # Excel logic
│       └── PdfExportService.php            # PDF logic
├── resources/
│   └── views/
│       └── reports/
│           ├── index.blade.php             # Export form UI
│           └── pdf-export.blade.php        # PDF template
├── routes/
│   └── web.php                              # Report routes
└── storage/
    └── app/
        └── public/
            └── charts/                      # Generated chart images
```

### Class Hierarchy

```
ReportController
├── exportDaily()           POST /reports/export-daily
├── exportWeekly()          POST /reports/export-weekly
└── exportMonthly()         POST /reports/export-monthly

PdfExportService
├── export()                Main export method
└── generateSummary()       Stats calculation

ExcelExportService
├── export()                Main export method
├── generateSummary()       Stats calculation
└── ExcelExportData         Implements FromArray, WithHeadings

ChartService
├── generateMonitoringChart()  Line chart (temp + humidity)
├── generateStatusChart()      Pie chart (safe/unsafe)
├── generateChartImage()       Image generation (GD library)
└── generatePieChart()         Pie rendering
```

### Data Flow

```
USER CLICKS EXPORT
        ↓
    Validation (device_id, date, format)
        ↓
    Fetch Monitorings from Database
        ↓
    ┌─────────────────┬──────────────────┐
    ↓                 ↓                  ↓
  Generate        Generate            Generate
  Charts          Summary             Details
    ↓                 ↓                  ↓
  PNG Images    Statistics          Incidents,
                                    Doctor Notes
    ↓                 ↓                  ↓
    └─────────────────┴──────────────────┘
                      ↓
        ┌─────────────┬─────────────┐
        ↓             ↓             ↓
      Export PDF    Export Excel   Download
      (DomPDF)    (Maatwebsite)      ↓
        ↓             ↓         Send Response
        └─────────────┴──────────────→ Browser
```

### Technologies Used

| Component | Technology | Version |
|-----------|-----------|---------|
| PDF Generation | Barryvdh DomPDF | ^0.8.16 |
| Excel Export | Maatwebsite Excel | ^3.1 |
| Chart Generation | PHP GD Library | Built-in |
| Framework | Laravel | ^12.0 |
| Database | MySQL | 8.0+ |

### Database Queries

**Get Monitoring Data:**
```php
Monitoring::where('device_id', $device->id)
    ->whereBetween('recorded_at', [$startDate, $endDate])
    ->orderBy('recorded_at')
    ->get();
```

**Get Summary Stats:**
```php
// Temperature
$max = $monitorings->max('temperature');
$min = $monitorings->min('temperature');
$avg = $monitorings->avg('temperature');

// Humidity  
$max = $monitorings->max('humidity');
$min = $monitorings->min('humidity');
$avg = $monitorings->avg('humidity');

// Count
$safe = $monitorings->where('status', 'Aman')->count();
$unsafe = $monitorings->where('status', 'Tidak Aman')->count();
```

**Get Related Data:**
```php
DoctorNote::where('device_id', $device->id)
    ->whereBetween('date', [$startDate, $endDate])
    ->get();

IncidentMarker::where('device_id', $device->id)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get();
```

### Routes Definition

```php
// routes/web.php
Route::prefix('reports')->middleware('auth')->group(function() {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/export-daily', [ReportController::class, 'exportDaily'])->name('reports.export-daily');
    Route::post('/export-weekly', [ReportController::class, 'exportWeekly'])->name('reports.export-weekly');
    Route::post('/export-monthly', [ReportController::class, 'exportMonthly'])->name('reports.export-monthly');
});
```

---

## 🔧 Troubleshooting

### Problem: "File terlalu besar" saat generate laporan bulanan

**Penyebab:**
- Data terlalu banyak (>50.000 records)
- Memory PHP tidak cukup

**Solusi:**
```php
// php.ini
memory_limit = 512M
max_execution_time = 300

// .env
APP_DEBUG=false
```

### Problem: Chart tidak muncul di PDF

**Penyebab:**
- Directory `/storage/app/public/charts/` belum dibuat
- Permission denied

**Solusi:**
```bash
# Linux/Mac
mkdir -p storage/app/public/charts
chmod 755 storage/app/public/charts

# Windows PowerShell
New-Item -ItemType Directory -Path "storage/app/public/charts" -Force
```

### Problem: Excel file corrupt

**Penyebab:**
- Maatwebsite Excel tidak properly installed
- Character encoding issue

**Solusi:**
```bash
composer require maatwebsite/excel:^3.1
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
```

### Problem: Laporan hanya kosong

**Penyebab:**
- Tidak ada data monitoring untuk tanggal yang dipilih
- Device ID tidak valid

**Solusi:**
1. Cek apakah device memiliki data monitoring
   ```php
   Monitoring::where('device_id', $id)->exists();
   ```
2. Pastikan tanggal benar dan device aktif

### Problem: PDF download corrupted

**Penyebab:**
- Headers sudah dikirim sebelum PDF generation
- Large file dengan slow connection

**Solusi:**
```php
// Pastikan tidak ada output sebelum PDF
// Check routes/web.php tidak ada print/echo

// Test dengan curl
curl -I http://localhost/reports/export-daily -X POST
```

---

## 📱 Mobile Support

### Smartphone (iOS/Android)

**PDF:**
- ✅ Download ke Files app
- ✅ View di reader app
- ✅ Email atau share
- ✅ Print via AirPrint/Cloud Print

**Excel:**
- ✅ Download ke Files/Storage
- ✅ Open with Excel Mobile
- ✅ View dengan Google Sheets
- ⚠️ Editing terbatas di mobile

### Tablet

**Recommended:** Gunakan PDF untuk view optimal

---

## 📚 Best Practices

### 1. Frekuensi Export
- **Harian**: Setiap shift change (3x sehari)
- **Mingguan**: Setiap hari Jum'at untuk week review
- **Bulanan**: Hari pertama bulan berikutnya

### 2. File Naming Convention
Sistem otomatis membuat nama:
```
Laporan-[Tipe]-[Device]-[Tanggal].pdf
Contoh: Laporan-Harian-Bayi-Perawatan-A-2026-02-11.pdf
```

### 3. Storage & Archiving
```
Folder Struktur:
/Laporan Monitoring/
├── 2026/
│   ├── Februari/
│   │   ├── Harian/
│   │   ├── Mingguan/
│   │   └── Bulanan/
│   └── Maret/
│       └── ...
```

### 4. Distribution via Email
**Template:**
```
Subject: [LAPORAN] Monitoring Suhu Bayi - 11 Februari 2026

Dear Dokter/Supervisor,

Terlampir laporan monitoring suhu dan kelembapan ruangan bayi
untuk periode 11 Februari 2026.

File: Laporan-Harian-Bayi-2026-02-11.pdf
Size: 3.2 MB

Ringkasan:
- Status: NORMAL ✓
- Suhu: 24.3 - 32.5°C (Rata-rata: 28.2°C)
- Kelembapan: 45 - 68% (Rata-rata: 55.2%)
- Kejadian Tidak Normal: 3x (Solved)

Mohon review dan hubungi jika ada pertanyaan.

Best regards,
Perawat Siti (ID: 12345)
Ruang Bayi - Lantai 3
```

---

## 📞 Kontak Support

Jika mengalami masalah dengan fitur Export Laporan:

1. Check dokumentasi ini
2. Cek file `/storage/logs/laravel.log`
3. Hubungi IT Support
4. Report bug dengan detail:
   - Device ID
   - Tanggal laporan
   - Format (PDF/Excel)
   - Error message (jika ada)

---

## 🎓 Pembelajaran

### Untuk Perawat
- Pahami arti statistik min/max/avg
- Laporkan anomali langsung ke dokter
- Update incident marker jika terjadi kejadian

### Untuk Dokter
- Review laporan untuk clinical decision
- Berikan instruksi khusus jika perlu
- Archive laporan untuk medical records

### Untuk IT/Admin
- Monitor ukuran file generate
- Backup laporan secara berkala
- Update library sesuai kebutuhan
- Monitor server resources

---

## 📝 Changelog

### Version 1.0 (February 2026)
- ✅ Initial release
- ✅ PDF export dengan embed charts
- ✅ Excel export dengan detail stats
- ✅ Daily, weekly, monthly reports
- ✅ Dashboard integration
- ✅ Full documentation

### Upcoming Features (Roadmap)
- 🔄 Real-time email sending
- 🔄 Scheduled auto-export
- 🔄 Comparison between periods
- 🔄 Advanced analytics dashboard
- 🔄 Custom report builder
- 🔄 API support for 3rd party

---

## 📄 License

Bagian dari Sistem Monitoring Suhu dan Kelembapan Bayi
Hospital Management System v1.0
© 2026 All Rights Reserved

---

**Last Updated:** 11 Februari 2026
**Documentation Version:** 1.0
**Status:** ✅ Complete
