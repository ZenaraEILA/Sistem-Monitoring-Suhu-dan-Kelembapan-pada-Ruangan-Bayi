@extends('layouts.main')

@section('title', 'Bantuan & Panduan Sistem')

@section('content')
<div class="container-fluid py-5">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm help-header">
                <div class="card-body px-5 py-4">
                    <h1 class="mb-2">
                        <i class="fas fa-question-circle text-primary"></i> Bantuan & Panduan Sistem
                    </h1>
                    <p class="text-muted mb-0">
                        Panduan lengkap untuk memaksimalkan penggunaan Sistem Monitoring Suhu & Kelembapan Ruangan Bayi
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 mb-4">
            <div class="card help-sidebar shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-book"></i> DAFTAR ISI</h6>
                </div>
                <div class="list-group list-group-flush help-toc">
                    <a href="#dashboard" class="list-group-item help-toc-item active" data-section="dashboard">
                        <i class="fas fa-chart-line"></i> Dashboard
                        <span class="float-end">→</span>
                    </a>
                    <a href="#status" class="list-group-item help-toc-item" data-section="status">
                        <i class="fas fa-wifi"></i> Status Device
                        <span class="float-end">→</span>
                    </a>
                    <a href="#history" class="list-group-item help-toc-item" data-section="history">
                        <i class="fas fa-history"></i> Data & Riwayat
                        <span class="float-end">→</span>
                    </a>
                    <a href="#export" class="list-group-item help-toc-item" data-section="export">
                        <i class="fas fa-file-pdf"></i> Export PDF
                        <span class="float-end">→</span>
                    </a>
                    @if(auth()->user()->role === 'admin')
                    <a href="#users" class="list-group-item help-toc-item" data-section="users">
                        <i class="fas fa-users"></i> Manajemen User
                        <span class="float-end">→</span>
                    </a>
                    @endif
                    <a href="#tutorial" class="list-group-item help-toc-item" data-section="tutorial">
                        <i class="fas fa-graduation-cap"></i> Panduan Langkah
                        <span class="float-end">→</span>
                    </a>
                </div>
            </div>

            <!-- Quick Tips Card -->
            <div class="card mt-4 help-tips shadow-sm border-left-primary">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0"><i class="fas fa-lightbulb text-warning"></i> Tips Cepat</h6>
                </div>
                <div class="card-body small">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>💡 Tip 1:</strong> Buka halaman pada perangkat yang stabil untuk monitoring terbaik
                        </li>
                        <li class="mb-2">
                            <strong>💡 Tip 2:</strong> Refresh halaman setiap 5 menit untuk data real-time terbaru
                        </li>
                        <li class="mb-2">
                            <strong>💡 Tip 3:</strong> Export PDF laporan setiap akhir hari untuk dokumentasi
                        </li>
                        <li>
                            <strong>💡 Tip 4:</strong> Hubungi teknisi jika status ESP menunjukkan offline > 10 menit
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <!-- A. PENJELASAN FITUR -->
            <div id="dashboard" class="help-section mb-5">
                <div class="section-header mb-4">
                    <h2><i class="fas fa-chart-line text-primary"></i> 1. Dashboard</h2>
                    <p class="text-muted">Halaman utama untuk monitoring suhu dan kelembapan real-time</p>
                </div>

                <!-- Dashboard Subsection -->
                <div class="card mb-3 help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-chart-area"></i> Grafik Suhu & Kelembapan Real-time</h5>
                    </div>
                    <div class="card-body">
                        <p>Grafik menampilkan data suhu dan kelembapan dalam waktu real-time untuk semua ruangan.</p>
                        
                        <div class="alert alert-info">
                            <strong>📊 Cara Membaca Grafik:</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong style="color: #E74C3C;">🔴 Garis Merah:</strong> Grafik Suhu (°C)</li>
                                <li><strong style="color: #3498DB;">🔵 Garis Biru:</strong> Grafik Kelembapan (%)</li>
                                <li><strong>Sumbu X:</strong> Waktu (dalam jam atau hari)</li>
                                <li><strong>Sumbu Y:</strong> Nilai temperatur/kelembapan</li>
                            </ul>
                        </div>

                        <div class="alert alert-info">
                            <strong>🎨 Palet Warna Grafik:</strong>
                            <ul class="mb-0 mt-2 small">
                                <li><span style="display: inline-block; width: 16px; height: 16px; background: #E74C3C; border-radius: 2px; margin-right: 8px;"></span><strong>Merah (#E74C3C):</strong> Memudahkan identifikasi data Suhu</li>
                                <li><span style="display: inline-block; width: 16px; height: 16px; background: #3498DB; border-radius: 2px; margin-right: 8px;"></span><strong>Biru (#3498DB):</strong> Memudahkan identifikasi data Kelembapan</li>
                                <li><strong>*Catatan:</strong> Warna grafik adalah garis data, bukan indikator status (berbeda dengan indikator status hijau/kuning/merah)</li>
                            </ul>
                        </div>

                        <p class="mt-3"><strong>💡 Tips:</strong> Hover mouse di atas grafik untuk melihat nilai detail pada waktu tertentu.</p>
                    </div>
                </div>

                <!-- Indikator Suhu -->
                <div class="card mb-3 help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-thermometer-half"></i> Indikator Suhu</h5>
                    </div>
                    <div class="card-body">
                        <p>Indikator ini menunjukkan kondisi saat ini:</p>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="help-indicator">
                                    <div class="indicator-light safe"></div>
                                    <strong>Hijau =</strong>
                                    <span>NORMAL (15-30°C)</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="help-indicator">
                                    <div class="indicator-light warning"></div>
                                    <strong>Kuning =</strong>
                                    <span>PERINGATAN (30-35°C)</span>
                                </div>
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="help-indicator">
                                    <div class="indicator-light critical"></div>
                                    <strong>Merah =</strong>
                                    <span>KRITIS (> 35°C)</span>
                                </div>
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="help-indicator">
                                    <div class="indicator-light offline"></div>
                                    <strong>Abu-abu =</strong>
                                    <span>OFFLINE (offline/disconnected)</span>
                                </div>
                            </div>
                        </div>

                        <p class="mt-3 text-danger"><strong>⚠️ Penting:</strong> Jika indikator merah, segera hubungi teknisi atau periksa AC.</p>
                    </div>
                </div>

                <!-- Indikator Kelembapan -->
                <div class="card mb-3 help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-droplet"></i> Indikator Kelembapan</h5>
                    </div>
                    <div class="card-body">
                        <p>Menunjukkan kondisi kelembapan udara:</p>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="help-indicator">
                                    <div class="indicator-light humidity-safe"></div>
                                    <strong>Biru Muda =</strong>
                                    <span>NORMAL (35-60%)</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="help-indicator">
                                    <div class="indicator-light humidity-warning"></div>
                                    <strong>Orange =</strong>
                                    <span>TINGGI (60-75%)</span>
                                </div>
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="help-indicator">
                                    <div class="indicator-light humidity-critical"></div>
                                    <strong>Merah =</strong>
                                    <span>KRITIS (> 75%)</span>
                                </div>
                            </div>
                        </div>

                        <p class="mt-3"><strong>💡 Tips:</strong> Kelembapan tinggi dapat memicu pertumbuhan jamur. Pastikan ventilasi baik.</p>
                    </div>
                </div>

                <!-- Indikator ESP -->
                <div class="card help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-wifi"></i> Indikator Status ESP</h5>
                    </div>
                    <div class="card-body">
                        <p>Status koneksi ESP8266 (sensor hardware):</p>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="help-indicator">
                                    <div class="indicator-light esp-online"></div>
                                    <strong>Hijau =</strong>
                                    <span>ONLINE (< 10 detik)</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="help-indicator">
                                    <div class="indicator-light esp-offline"></div>
                                    <strong>Abu-abu =</strong>
                                    <span>OFFLINE (> 10 detik)</span>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-danger mt-3">
                            <strong>⚠️ Jika ESP OFFLINE:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Periksa koneksi WiFi perangkat</li>
                                <li>Restart ESP (matikan-hidupkan power)</li>
                                <li>Hubungi teknisi jika offline lebih dari 30 menit</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- B. STATUS DEVICE -->
            <div id="status" class="help-section mb-5">
                <div class="section-header mb-4">
                    <h2><i class="fas fa-wifi text-primary"></i> 2. Status Device</h2>
                    <p class="text-muted">Memahami status koneksi dan keadaan sensor</p>
                </div>

                <div class="card mb-3 help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Status ONLINE vs OFFLINE</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm help-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Arti</th>
                                    <th>Logika</th>
                                    <th>Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td><span class="badge bg-success">ONLINE</span></td>
                                    <td>Sensor sedang aktif & mengirim data</td>
                                    <td>Data terbaru < 10 detik</td>
                                    <td>✅ Normal, tidak perlu tindakan</td>
                                </tr>
                                <tr class="table-warning">
                                    <td><span class="badge bg-warning">OFFLINE</span></td>
                                    <td>Sensor belum mengirim data > 10 detik</td>
                                    <td>Data terakhir 10-300 detik</td>
                                    <td>⚠️ Check koneksi, tunggu beberapa menit</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><span class="badge bg-danger">DISCONNECTED</span></td>
                                    <td>Sensor tidak aktif/error > 5 menit</td>
                                    <td>Data terakhir > 300 detik</td>
                                    <td>🔴 Hubungi teknisi segera</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="alert alert-info mt-3">
                            <strong>📖 Contoh:</strong> Jika data terakhir 7 detik yang lalu, status = ONLINE ✅
                        </div>
                    </div>
                </div>

                <div class="card help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Notifikasi ESP OFF</h5>
                    </div>
                    <div class="card-body">
                        <p>Sistem akan menampilkan notifikasi jika ESP offline terlalu lama:</p>
                        
                        <div class="alert alert-danger">
                            <strong>❗ NOTIFIKASI:</strong> "ESP Device X OFFLINE - Data tidak terupdate"
                        </div>

                        <p><strong>Kapan muncul?</strong> Setelah 5 menit tanpa data dari sensor</p>

                        <p><strong>Langkah penanganan:</strong></p>
                        <ol>
                            <li>Cek WiFi router (pastikan power ON)</li>
                            <li>Restart ESP dengan mematikan power 30 detik</li>
                            <li>Tunggu 2 menit hingga ESP reconnect</li>
                            <li>Jika masih offline, hubungi teknisi</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- C. DATA & RIWAYAT -->
            <div id="history" class="help-section mb-5">
                <div class="section-header mb-4">
                    <h2><i class="fas fa-history text-primary"></i> 3. Data & Riwayat</h2>
                    <p class="text-muted">Melihat dan mengelola data historis</p>
                </div>

                <div class="card mb-3 help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Cara Melihat Data Sebelumnya</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Step 1:</strong> Klik menu "Riwayat Data" di sidebar</p>
                        <p><strong>Step 2:</strong> Pilih device dari dropdown</p>
                        <p><strong>Step 3:</strong> Tabel menampilkan data terakhir 100 records</p>
                        <p><strong>Step 4:</strong> Scroll ke bawah untuk melihat data lebih lama</p>
                    </div>
                </div>

                <div class="card mb-3 help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Filter Berdasarkan Tanggal</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>📅 Cara Filter:</strong>
                            <ol class="mt-2 mb-0">
                                <li>Masuk page "Riwayat Data"</li>
                                <li>Klik "Filter Tanggal" atau date picker</li>
                                <li>Pilih tanggal mulai & akhir</li>
                                <li>Klik "Filter"</li>
                                <li>Tabel akan memperbarui sesuai range tanggal</li>
                            </ol>
                        </div>

                        <p><strong>Contoh:</strong> Filter 14 Februari - 15 Februari akan menampilkan data 2 hari terakhir</p>
                    </div>
                </div>

                <div class="card help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Pembacaan Tabel Data</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Kolom-kolom yang ditampilkan:</strong></p>
                        
                        <table class="table table-sm help-table">
                            <thead>
                                <tr>
                                    <th>Kolom</th>
                                    <th>Arti</th>
                                    <th>Contoh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>No</strong></td>
                                    <td>Nomor urut data</td>
                                    <td>1, 2, 3, ...</td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu</strong></td>
                                    <td>Kapan data direkam</td>
                                    <td>14 Feb 2026, 19:30:45</td>
                                </tr>
                                <tr>
                                    <td><strong>Suhu (°C)</strong></td>
                                    <td>Temperatur saat itu</td>
                                    <td>26.5</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelembapan (%)</strong></td>
                                    <td>Persentase kelembapan</td>
                                    <td>55.2</td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>Kondisi kesehatan</td>
                                    <td>Aman, Peringatan, Kritis</td>
                                </tr>
                            </tbody>
                        </table>

                        <p class="mt-3"><strong>💡 Tips:</strong> Data disimpan setiap 10 detik, gunakan export jika perlu analisis lebih detail.</p>
                    </div>
                </div>
            </div>

            <!-- D. EXPORT PDF -->
            <div id="export" class="help-section mb-5">
                <div class="section-header mb-4">
                    <h2><i class="fas fa-file-pdf text-primary"></i> 4. Export PDF</h2>
                    <p class="text-muted">Mengunduh laporan dalam format PDF</p>
                </div>

                <div class="card mb-3 help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Cara Mengunduh Laporan</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <strong>📥 Step-by-Step:</strong>
                            <ol class="mt-2 mb-0">
                                <li>Klik menu "Laporan" di sidebar</li>
                                <li>Pilih jenis laporan: Harian / Mingguan / Bulanan</li>
                                <li>Pilih periode tanggal</li>
                                <li>Klik tombol "Unduh PDF"</li>
                                <li>File akan otomatis terunduh ke folder Downloads</li>
                            </ol>
                        </div>

                        <p class="mt-3"><strong>Format nama file:</strong> `Laporan_Monitoring_[Tanggal].pdf`</p>
                    </div>
                </div>

                <div class="card help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Isi Laporan PDF</h5>
                    </div>
                    <div class="card-body">
                        <p>Laporan PDF mencakup:</p>
                        
                        <div class="list-group list-group-flush">
                            <div class="list-group-item">
                                <strong>✓ Header Laporan</strong>
                                <p class="text-muted mb-0">Nama rumah sakit, tanggal laporan, ruangan</p>
                            </div>
                            <div class="list-group-item">
                                <strong>✓ Ringkasan Statistik</strong>
                                <p class="text-muted mb-0">Suhu rata-rata, kelembapan rata-rata, jumlah kondisi kritis</p>
                            </div>
                            <div class="list-group-item">
                                <strong>✓ Grafik Suhu & Kelembapan</strong>
                                <p class="text-muted mb-0">Visualisasi trend selama periode laporan</p>
                            </div>
                            <div class="list-group-item">
                                <strong>✓ Tabel Data Detail</strong>
                                <p class="text-muted mb-0">Semua data mentah dalam periode yang dipilih</p>
                            </div>
                            <div class="list-group-item">
                                <strong>✓ Analisis & Catatan</strong>
                                <p class="text-muted mb-0">Temuan penting dan rekomendasi</p>
                            </div>
                        </div>

                        <p class="mt-3"><strong>💡 Tips:</strong> Laporan sudah professional, bisa langsung diberikan ke kepala ruangan atau untuk keperluan dokumentasi medis.</p>
                    </div>
                </div>
            </div>

            <!-- E. MANAJEMEN USER (Admin Only) -->
            @if(auth()->user()->role === 'admin')
            <div id="users" class="help-section mb-5">
                <div class="section-header mb-4">
                    <h2><i class="fas fa-users text-primary"></i> 5. Manajemen User (Admin)</h2>
                    <p class="text-muted">Mengelola akun pengguna dan role</p>
                </div>

                <div class="card mb-3 help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Perbedaan Role Admin & Petugas</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm help-table">
                            <thead>
                                <tr>
                                    <th>Fitur</th>
                                    <th>Admin</th>
                                    <th>Petugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Akses Dashboard</strong></td>
                                    <td>✅ Ya</td>
                                    <td>✅ Ya</td>
                                </tr>
                                <tr>
                                    <td><strong>Lihat Riwayat Data</strong></td>
                                    <td>✅ Ya</td>
                                    <td>✅ Ya</td>
                                </tr>
                                <tr>
                                    <td><strong>Export PDF</strong></td>
                                    <td>✅ Ya</td>
                                    <td>✅ Ya</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelola Device</strong></td>
                                    <td>✅ Ya</td>
                                    <td>❌ Tidak</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelola User</strong></td>
                                    <td>✅ Ya</td>
                                    <td>❌ Tidak</td>
                                </tr>
                                <tr>
                                    <td><strong>Ubah Pengaturan Sistem</strong></td>
                                    <td>✅ Ya</td>
                                    <td>❌ Tidak</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card help-card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Cara Mengubah Role User</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Step 1:</strong> Klik menu "Manajemen User" (hanya tersedia untuk Admin)</p>
                        <p><strong>Step 2:</strong> Lihat daftar semua user</p>
                        <p><strong>Step 3:</strong> Klik tombol "Edit" pada user yang ingin diubah</p>
                        <p><strong>Step 4:</strong> Pilih role: Admin atau Petugas</p>
                        <p><strong>Step 5:</strong> Klik "Simpan Perubahan"</p>

                        <div class="alert alert-warning mt-3">
                            <strong>⚠️ Hati-hati:</strong> Berikan role Admin hanya kepada orang yang bertanggung jawab. Admin bisa mengubah data sistem penting.
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- F. PANDUAN LANGKAH PENGGUNAAN -->
            <div id="tutorial" class="help-section mb-5">
                <div class="section-header mb-4">
                    <h2><i class="fas fa-graduation-cap text-primary"></i> 6. Panduan Langkah Penggunaan</h2>
                    <p class="text-muted">Tutorial step-by-step penggunaan sistem</p>
                </div>

                <!-- Tutorial 1: Login -->
                <div class="card mb-3 help-card shadow-sm help-tutorial">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary">Langkah 1</span> Login ke Sistem
                        </h5>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li>Buka browser dan ketik URL: <code>http://192.168.186.241:8000</code></li>
                            <li>Halaman login akan muncul</li>
                            <li>Masukkan username (biasanya nama lengkap atau email)</li>
                            <li>Masukkan password yang diberikan</li>
                            <li>Klik tombol "Login"</li>
                            <li>Jika berhasil, Anda akan masuk ke Dashboard</li>
                        </ol>

                        <div class="alert alert-info mt-3 small">
                            <strong>💡 Tips Login:</strong> Centang "Ingat saya" untuk login otomatis di lain waktu (hanya di komputer pribadi)
                        </div>
                    </div>
                </div>

                <!-- Tutorial 2: Dashboard -->
                <div class="card mb-3 help-card shadow-sm help-tutorial">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary">Langkah 2</span> Masuk ke Dashboard
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Setelah login, Anda otomatis masuk ke halaman Dashboard:</p>

                        <ol>
                            <li><strong>Di bagian atas (Status Dropdown):</strong>
                                <ul>
                                    <li>Pilih ruangan mana yang ingin dipantau</li>
                                    <li>Indikator akan menampilkan data real-time</li>
                                </ul>
                            </li>
                            <li><strong>Di bagian tengah (Grafik):</strong>
                                <ul>
                                    <li>Dua grafik: suhu <span style="color: #E74C3C; font-weight: bold;">(merah)</span> dan kelembapan <span style="color: #3498DB; font-weight: bold;">(biru)</span></li>
                                    <li>Hover mouse untuk detail nilai</li>
                                </ul>
                            </li>
                            <li><strong>Di bagian bawah (Card Status):</strong>
                                <ul>
                                    <li>Ringkasan semua ruangan</li>
                                    <li>Status koneksi setiap sensor</li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Tutorial 3: Grafik -->
                <div class="card mb-3 help-card shadow-sm help-tutorial">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary">Langkah 3</span> Memahami Grafik
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Cara membaca grafik dengan benar:</strong></p>

                        <ol>
                            <li><strong>Lihat legend warna:</strong>
                                <ul>
                                    <li><span style="color: #E74C3C; font-weight: bold;">Merah = Suhu (°C)</span></li>
                                    <li><span style="color: #3498DB; font-weight: bold;">Biru = Kelembapan (%)</span></li>
                                </ul>
                            </li>
                            <li><strong>Amati trend:</strong>
                                <ul>
                                    <li>Grafik naik berarti suhu/kelembapan meningkat</li>
                                    <li>Grafik turun berarti kondisi membaik</li>
                                </ul>
                            </li>
                            <li><strong>Hover untuk detail:</strong>
                                <ul>
                                    <li>Arahkan mouse ke titik tertentu</li>
                                    <li>Akan muncul nilai exact pada waktu itu</li>
                                </ul>
                            </li>
                            <li><strong>Berapa lama data ditampilkan?</strong>
                                <ul>
                                    <li>Default: 24 jam terakhir</li>
                                    <li>Bisa diubah dengan tombol di atas grafik</li>
                                </ul>
                            </li>
                        </ol>

                        <div class="alert alert-success mt-3 small">
                            <strong>✅ Contoh Pembacaan yang Benar:</strong> "Grafik menunjukkan suhu konsisten 26-28°C, naik sedikit pada jam 15:00, kemudian stabil kembali. Kelembapan sekitar 55-60%, aman."
                        </div>
                    </div>
                </div>

                <!-- Tutorial 4: Cek Status -->
                <div class="card mb-3 help-card shadow-sm help-tutorial">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary">Langkah 4</span> Mengecek Status Device
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Untuk memonitor apakah sensor aktif:</strong></p>

                        <ol>
                            <li><strong>Lihat indikator di kanan atas:</strong>
                                <ul>
                                    <li>Area yang menunjukkan Suhu, Kelembapan, ESP Status</li>
                                </ul>
                            </li>
                            <li><strong>Cek secara berkala:</strong>
                                <ul>
                                    <li>Refresh halaman setiap 5 menit untuk data terbaru</li>
                                    <li>Sistem auto-update tapi refresh manual lebih aman</li>
                                </ul>
                            </li>
                            <li><strong>Jika ESP offline:</strong>
                                <ul>
                                    <li>Tunggu 2-3 menit (mungkin lagi reconnect)</li>
                                    <li>Jika masih offline, hubungi teknisi</li>
                                </ul>
                            </li>
                        </ol>

                        <div class="alert alert-danger mt-3 small">
                            <strong>🔴 Tindakan Cepat:</strong> Jika ada kondisi KRITIS (merah), segera laporkan ke kepala ruangan sebelum hal lebih buruk terjadi.
                        </div>
                    </div>
                </div>

                <!-- Tutorial 5: Unduh Laporan -->
                <div class="card mb-3 help-card shadow-sm help-tutorial">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary">Langkah 5</span> Mengunduh Laporan PDF
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Untuk membuat laporan resmi:</strong></p>

                        <ol>
                            <li>Klik menu <strong>"Laporan"</strong> di sidebar</li>
                            <li>Pilih jenis: Harian / Mingguan / Bulanan</li>
                            <li>Pilih ruangan (atau semua)</li>
                            <li>Pilih tanggal mulai dan akhir</li>
                            <li>Klik <strong>"Buat Laporan"</strong></li>
                            <li>Review laporan di preview</li>
                            <li>Klik <strong>"Unduh PDF"</strong></li>
                            <li>File akan langsung terunduh</li>
                        </ol>

                        <div class="alert alert-info mt-3 small">
                            <strong>💡 Kapan mengunduh?</strong> Setiap akhir shift atau setiap akhir hari untuk dokumentasi resmi
                        </div>
                    </div>
                </div>

                <!-- Tutorial 6: Admin User Management -->
                @if(auth()->user()->role === 'admin')
                <div class="card mb-3 help-card shadow-sm help-tutorial">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary">Langkah 6</span> Mengelola User (Admin)
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Untuk mengubah atau menambah user:</strong></p>

                        <ol>
                            <li>Klik menu <strong>"Manajemen User"</strong> (sidebar)</li>
                            <li><strong>Untuk menambah user baru:</strong>
                                <ul>
                                    <li>Klik tombol "+ Tambah User"</li>
                                    <li>Isi form: Nama, Email, Password</li>
                                    <li>Pilih role: Admin atau Petugas</li>
                                    <li>Klik "Simpan"</li>
                                </ul>
                            </li>
                            <li><strong>Untuk mengedit user:</strong>
                                <ul>
                                    <li>Cari user di daftar</li>
                                    <li>Klik tombol "Edit"</li>
                                    <li>Ubah data yang perlu diubah</li>
                                    <li>Klik "Simpan Perubahan"</li>
                                </ul>
                            </li>
                            <li><strong>Untuk menghapus user:</strong>
                                <ul>
                                    <li>Klik tombol "Hapus"</li>
                                    <li>Konfirmasi penghapusan</li>
                                </ul>
                            </li>
                        </ol>

                        <div class="alert alert-warning mt-3 small">
                            <strong>⚠️ Catatan:</strong> Simpan user baru beserta password sementaranya. User akan diminta ubah password saat login pertama.
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- FAQs -->
            <div class="help-section mb-5">
                <div class="section-header mb-4">
                    <h2><i class="fas fa-circle-question text-primary"></i> Pertanyaan Umum (FAQ)</h2>
                </div>

                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Berapa sering saya harus melihat dashboard?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sebaiknya check dashboard setiap 1-2 jam, terutama saat shift pagi. Jika ada alarm/notifikasi, segera cek setelah notifikasi muncul.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Apa yang harus saya lakukan jika ESP offline?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                1) Tunggu 2-3 menit (mungkin reconnecting)<br>
                                2) Refresh halaman<br>
                                3) Jika masih offline, periksa koneksi WiFi<br>
                                4) Jika lebih dari 10 menit, hubungi teknisi/admin
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Bagaimana cara membaca berkas PDF yang ter-export?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                PDF sudah dalam format professional dan siap cetak. Cukup buka dengan Adobe Reader atau browser. Jika ingin edit, bisa convert ke Excel terlebih dahulu atau hubungi admin.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Apakah data saya aman/terlindungi password?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ya. Setiap login harus authenticated. Jangan share password. Logout setelah selesai untuk keamanan maksimal. Data tersimpan di database server yang aman.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Bagaimana jika saya lupa password?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Hubungi Admin untuk reset password. Admin akan memberikan password baru yang harus diganti saat login pertama.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="alert alert-light border help-contact mt-5">
                <h5 class="mb-3"><i class="fas fa-headset"></i> Butuh Bantuan Lebih Lanjut?</h5>
                <p class="mb-2"><strong>Hubungi teknisi/admin:</strong></p>
                <ul class="mb-0">
                    <li>📧 Email: admin@rumahsakit.com</li>
                    <li>📞 Telepon: (021) 1234-5678</li>
                    <li>⏰ Jam operasional: 07:00 - 17:00 WIB (Senin-Jumat)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.help-header {
    border-top: 4px solid #0066cc !important;
}

.help-section {
    scroll-margin-top: 80px;
}

.section-header h2 {
    font-weight: 600;
    color: #002147;
    border-bottom: 2px solid #0066cc;
    padding-bottom: 10px;
}

.help-card {
    transition: all 0.3s;
}

.help-card:hover {
    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.15) !important;
}

.help-toc-item {
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.help-toc-item:hover {
    background-color: #f8f9fa;
    border-left-color: #0066cc;
}

.help-toc-item.active {
    background-color: #e7f1ff;
    border-left-color: #0066cc;
    font-weight: 500;
}

.help-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 4px;
}

.indicator-light {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.indicator-light.safe {
    background-color: #28a745;
}

.indicator-light.warning {
    background-color: #ffc107;
}

.indicator-light.critical {
    background-color: #dc3545;
}

.indicator-light.esp-online {
    background-color: #28a745;
}

.indicator-light.esp-offline {
    background-color: #6c757d;
}

.indicator-light.offline {
    background-color: #6c757d;
}

.indicator-light.humidity-safe {
    background-color: #17a2b8;
}

.indicator-light.humidity-warning {
    background-color: #ff9800;
}

.indicator-light.humidity-critical {
    background-color: #dc3545;
}

.help-tutorial {
    border-left: 4px solid #0066cc;
}

.help-tutorial .card-header {
    background-color: #f0f8ff !important;
}

.help-table {
    font-size: 0.9rem;
}

.help-tips {
    border-left: 4px solid #ffc107;
}

.help-contact {
    background: linear-gradient(135deg, #f0f8ff 0%, #e7f1ff 100%);
    border: 1px solid #0066cc;
    border-radius: 8px;
}

@media (max-width: 768px) {
    .help-sidebar {
        position: sticky;
        top: 80px;
        z-index: 99;
    }
}
</style>

@endsection
