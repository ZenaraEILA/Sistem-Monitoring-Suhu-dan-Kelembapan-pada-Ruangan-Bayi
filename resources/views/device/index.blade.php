@extends('layouts.main')

@section('title', 'Manajemen Device - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0"><i class="fas fa-microchip"></i> Manajemen Device</h1>
            <a href="{{ route('device.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Device
            </a>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle"></i>
    <strong>Informasi:</strong> Setiap device akan mendapatkan Device ID yang unik untuk identifikasi saat ESP mengirim data.
</div>

<!-- Devices Table -->
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Nama Device</th>
                <th>Lokasi</th>
                <th>Device ID</th>
                <th>Status Terbaru</th>
                <th>Suhu Terbaru</th>
                <th>Kelembapan Terbaru</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devices as $key => $device)
            <tr>
                <td>{{ ($devices->currentPage() - 1) * $devices->perPage() + $key + 1 }}</td>
                <td><strong>{{ $device->device_name }}</strong></td>
                <td>{{ $device->location }}</td>
                <td>
                    <code class="bg-light p-2 rounded" style="font-size: 0.85rem; word-break: break-all;">
                        {{ $device->device_id }}
                    </code>
                </td>
                <td>
                    @if($device->monitorings->count() > 0)
                        @php $monitoring = $device->monitorings->first(); @endphp
                        <span class="badge {{ $monitoring->status === 'Aman' ? 'badge-success' : 'badge-danger' }}">
                            {{ $monitoring->status }}
                        </span>
                    @else
                        <span class="badge bg-secondary">Belum ada data</span>
                    @endif
                </td>
                <td>
                    @if($device->monitorings->count() > 0)
                        @php $monitoring = $device->monitorings->first(); @endphp
                        {{ number_format($monitoring->temperature, 2) }}°C
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($device->monitorings->count() > 0)
                        @php $monitoring = $device->monitorings->first(); @endphp
                        {{ number_format($monitoring->humidity, 2) }}%
                    @else
                        -
                    @endif
                </td>
                <td>
                    <a href="{{ route('device.edit', $device->id) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route('device.destroy', $device->id) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <p class="text-muted mb-0">
                        <i class="fas fa-box-open"></i> Belum ada device terdaftar
                    </p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $devices->links() }}
</div>

<!-- Device Setup Instructions -->
<div class="card mt-5">
    <div class="card-header bg-secondary">
        <h5 class="mb-0"><i class="fas fa-code"></i> Instruksi Setup ESP8266/ESP32</h5>
    </div>
    <div class="card-body">
        <h6>1. Pastikan Device ID sudah tercatat di sini</h6>
        <p>Gunakan Device ID yang tertera di atas untuk konfigurasi ESP Anda</p>

        <h6>2. Request ke API untuk mengirim data</h6>
        <pre><code class="language-txt">POST /api/monitoring
Content-Type: application/json

{
  "device_id": "DEVICE_XXXXX_1234567890",
  "temperature": 26.5,
  "humidity": 55.2
}
</code></pre>

        <h6>3. Response sukses</h6>
        <pre><code class="language-json">{
  "message": "Data monitoring berhasil disimpan",
  "data": {
    "id": 1,
    "device_id": 1,
    "temperature": 26.5,
    "humidity": 55.2,
    "status": "Aman",
    "recorded_at": "2026-02-07 12:00:00",
    "created_at": "2026-02-07T12:00:00.000000Z"
  }
}
</code></pre>

        <h6>4. Contoh kode Arduino/ESP</h6>
        <pre><code class="language-cpp">#include &lt;ESP8266WiFi.h&gt;
#include &lt;ESP8266HTTPClient.h&gt;
#include &lt;DHT.h&gt;

#define DHT_PIN D4
#define DHT_TYPE DHT22

DHT dht(DHT_PIN, DHT_TYPE);
const char* ssid = "YOUR_SSID";
const char* password = "YOUR_PASSWORD";
const char* apiUrl = "http://your-domain.com/api/monitoring";
const char* deviceId = "DEVICE_XXXXX_1234567890";

void setup() {
  Serial.begin(115200);
  dht.begin();
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("WiFi connected");
}

void loop() {
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  
  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("Failed to read from DHT sensor");
    delay(10000);
    return;
  }
  
  sendData(temperature, humidity);
  delay(60000); // Send every 1 minute
}

void sendData(float temp, float hum) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(apiUrl);
    http.addHeader("Content-Type", "application/json");
    
    String json = "{\"device_id\":\"" + String(deviceId) + "\",\"temperature\":" + 
                  String(temp) + ",\"humidity\":" + String(hum) + "}";
    
    int httpCode = http.POST(json);
    
    if (httpCode > 0) {
      String response = http.getString();
      Serial.println(httpCode);
      Serial.println(response);
    } else {
      Serial.println("Error on HTTP request");
    }
    
    http.end();
  }
}
</code></pre>
    </div>
</div>
@endsection
