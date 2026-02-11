# Fitur Grafik Monitoring Interaktif - Dokumentasi

## 📊 Ringkasan Fitur

Sistem monitoring suhu dan kelembapan bayi kini dilengkapi dengan grafik interaktif yang memungkinkan dokter/tenaga medis untuk menganalisis data dengan presisi tinggi.

## ✨ Fitur Utama

### 1. **Rentang Waktu Fleksibel (Timeframe Selector)**
- 10 Menit Terakhir - untuk monitoring real-time dan perubahan cepat
- 30 Menit Terakhir - analisis short-term
- 1 Jam Terakhir - trend per jam
- 6 Jam Terakhir - analisis half-day
- 12 Jam Terakhir - full monitoring setengah hari
- 1 Hari Terakhir - full daily review

Pilih timeframe yang sesuai untuk melihat data dengan granularitas yang tepat.

### 2. **Interaktivitas Zoom & Pan**
- **Mouse Scroll**: Scroll ke atas untuk zoom in, scroll ke bawah untuk zoom out
- **Drag Pan**: Klik dan drag grafik ke kana/kiri untuk berpindah ke bagian data lain
- **Selection Tool**: Gunakan toolbar untuk select area tertentu
- **Reset Zoom**: Tombol "Reset Zoom" untuk kembali ke tampilan penuh

### 3. **Dual-Axis Line Chart**
- **Axis Kiri (Merah)**: Suhu dalam °C (range 10-40°C)
- **Axis Kanan (Biru)**: Kelembapan dalam % (range 0-100%)
- **Smooth Curves**: Line chart dengan kurva halus untuk visualisasi trend
- **Data Points**: Titik data yang jelas menunjukkan setiap pengukuran

### 4. **Highlight Zona "Tidak Aman" (Plot Bands)**
Sistem secara otomatis menyoroti area yang menunjukkan kondisi tidak aman:

#### Suhu:
- **Terlalu Dingin** (<15°C): Background merah pastel di area bawah
- **Terlalu Panas** (>30°C): Background merah pastel di area atas
- **Normal** (15-30°C): Area hijau/normal

#### Kelembapan:
- **Terlalu Kering** (<35%): Background biru pastel di area bawah
- **Terlalu Lembap** (>60%): Background biru pastel di area atas
- **Normal** (35-60%): Area hijau/normal

### 5. **Incident Markers**
- Menampilkan titik penting (incident markers) pada grafik
- Label otomatis untuk setiap kejadian
- Warna berbeda untuk membedakan jenis incident

### 6. **Statistik Real-Time**
Kartu statistik menampilkan:

**Statistik Suhu:**
- Rata-rata (Average)
- Maksimal (Peak)
- Minimal (Low)
- Status badge (Aman/Tidak Aman)

**Statistik Kelembapan:**
- Rata-rata (Average)
- Maksimal (Peak)
- Minimal (Low)
- Status badge (Aman/Tidak Aman)

### 7. **Status Alert**
- Alert banner yang menunjukkan status keseluruhan
- ✓ Hijau: Semua parameter normal
- ✗ Merah: Ada parameter yang abnormal dengan penjelasan spesifik

### 8. **Enhanced Tooltip (Hover Information)**
Saat hover di atas grafik, tampilkan:
- Waktu pengukuran (HH:mm:ss)
- Nilai Suhu (°C)
- Nilai Kelembapan (%)
- Status pada waktu tersebut

### 9. **Toolbar & Kontrol**
ApexCharts toolbar menyediakan:
- 📥 Download Chart: Export grafik sebagai PNG
- 🔍 Zoom: Aktivasi mode zoom
- ➕ Zoom In: Perbesar grafik
- ➖ Zoom Out: Perkecil grafik
- ✋ Pan: Ubah mode ke pan untuk drag
- ↩️ Reset: Reset ke tampilan awal

### 10. **Filter & Refresh**
- Dropdown Device: Pilih perangkat monitoring tertentu
- Dropdown Timeframe: Pilih rentang waktu
- Tombol Perbarui: Refresh data grafik

## 🛠️ Implementasi Teknis

### Backend (Laravel)
**File**: `app/Http/Controllers/MonitoringController.php`

```php
public function chart(Request $request)
{
    // Support timeframe: 10_min, 30_min, 1_hour, 6_hours, 12_hours, 1_day
    $timeframe = $request->get('timeframe', '1_day');
    
    // Return data dengan:
    // - temperatures[], humidities[], dates[]
    // - timestamps[] (milliseconds untuk ApexCharts)
    // - statuses[] (untuk highlighting)
    // - incidents[] (incident markers)
}
```

### Frontend (Vue/Blade + ApexCharts)
**File**: `resources/views/monitoring/chart.blade.php`

**Library**: ApexCharts (CDN)
```html
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
```

**Features**:
- Dual-axis configuration
- Plot bands untuk zone highlighting
- Annotations untuk incident markers
- Custom formatting untuk tooltip
- Zoom & pan enabled

## 📱 Responsive Design
Grafik responsif untuk berbagai ukuran layar:
- Desktop: Full functionality dengan toolbar lengkap
- Tablet: Optimized layout dengan zoom tersedia
- Mobile: Minimal controls dengan fokus pada data readability

## 🎯 Use Cases

### 1. **Emergency Response**
Dokter dapat dengan cepat zoom ke timeframe 10/30 menit untuk melihat perubahan suhu/kelembapan real-time dan mendeteksi masalah.

### 2. **Trend Analysis**
Dengan 6 hour atau 12 hour view, dapat melihat pattern dan trend jangka menengah.

### 3. **Daily Review**
View 1 day memberikan gambaran lengkap tentang kondisi bayi sepanjang hari.

### 4. **Historical Comparison**
Pan dan zoom memungkinkan perbandingan data dari waktu berbeda tanpa perlu reload halaman.

### 5. **Report Generation**
Download chart untuk dokumentasi medis atau laporan ke orangtua.

## ⚙️ Konfigurasi

### Standar Nilai Normal
- **Suhu**: 15°C - 30°C (untuk bayi di inkubator/room temperature)
- **Kelembapan**: 35% - 60% (untuk kenyamanan dan kesehatan kulit bayi)

### Warna Kode
- **Suhu Aman**: Merah (#dc3545) untuk indikator
- **Kelembapan Aman**: Biru (#0dcaf0) untuk indikator
- **Normal Range**: Hijau (#28a745)
- **Danger Zone**: Merah (#dc3545)

## 🔄 Update Data
Data otomatis refresh saat:
1. User mengubah device atau timeframe dan klik "Perbarui"
2. Browser page reload
3. User kembali ke halaman chart

## 📈 Performance
- Query dioptimalkan dengan `whereBetween` untuk timeframe selection
- Data in memory sorting untuk performance zoom/pan
- CDN for ApexCharts library (no server-side rendering)

## 🐛 Troubleshooting

### Grafik tidak menampilkan data
1. Pastikan data monitoring sudah ada di database
2. Cek console browser untuk JavaScript errors
3. Verify device_id parameter di URL

### Zoom/Pan tidak bekerja
1. Pastikan ApexCharts library sudah loaded
2. Cek browser console untuk plugin errors
3. Refresh halaman

### Incident markers tidak muncul
1. Verify relasi incidentMarkers di model Monitoring
2. Ensure incident markers ada di database
3. Cek format timestamp di backend

## 📚 Resources
- ApexCharts Documentation: https://apexcharts.com/docs/
- Laravel Carbon Documentation: https://carbon.nesbot.com/
- Bootstrap 5 Grid System

## 🔒 Security
- Data filtering berdasarkan device_id (user hanya bisa lihat device yang authorized)
- Timestamp validation untuk prevent injection
- CSRF protection di form

## 🚀 Future Enhancements
- Real-time WS updates untuk live monitoring
- Export ke PDF dengan formatting medical
- Anomaly detection dengan AI/ML
- Custom alert thresholds
- Multi-device comparison
- Trend prediction
