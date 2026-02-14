#include <ESP8266WiFi.h>
#include <DHT.h>
#include <ArduinoJson.h>

// ============ KONFIGURASI DHT11 ============
#define DHTPIN D4       // Pin D4 (GPIO2) untuk data DHT11
#define DHTTYPE DHT11   // Tipe sensor: DHT11
DHT dht(DHTPIN, DHTTYPE);

// ============ KONFIGURASI WIFI ============
const char* ssid = "monitoring_suhu";           // ← EDIT: Ganti dengan SSID WiFi Anda
const char* password = "11111111";   // ← EDIT: Ganti dengan password WiFi Anda

// ============ KONFIGURASI API LARAVEL ============
const char* serverIP = "192.168.186.241";  // ← BENAR: IP server Laravel
const int serverPort = 8000;           // Port Laravel (default: 8000)
const char* apiEndpoint = "/api/monitoring/store"; // Endpoint API Laravel

// ============ KONFIGURASI DEVICE ============
const char* deviceId = "DEVICE_5VGP9BAM7C_1771067547";  // Device 2 = Ruangan B1 (unique device ID)
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
  delay(2000); // DHT11 perlu waktu startup
  
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
    return false;
  }
  
  lastReadTime = millis();
  
  // Baca nilai dari sensor
  float h = dht.readHumidity();      // Baca kelembapan
  float t = dht.readTemperature();   // Baca suhu (Celsius)
  
  // Cek apakah pembacaan valid
  if (isnan(h) || isnan(t)) {
    Serial.println("❌ Sensor DHT11 tidak merespons (cek kabel & pin)");
    return false;
  }
  
  temperature = t;
  humidity = h;
  
  return true;
}

// ============ FUNGSI: TAMPILKAN DATA ============
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

// ============ FUNGSI: KIRIM DATA KE API ============
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
    Serial.println("  1. IP address (gunakan ipconfig di CMD)");
    Serial.println("  2. Port 8000 running (php artisan serve)");
    Serial.println("  3. Firewall tidak memblokir");
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
  
  Serial.print("📊 JSON: ");
  Serial.println(jsonPayload);
  
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
      } else if (line.indexOf("500") > -1) {
        Serial.println("❌ Error server (HTTP 500)");
      }
      
      // Tampilkan beberapa baris pertama response
      if (line.length() > 0) {
        Serial.println(line);
      }
    }
  }
  
  client.stop();
  Serial.println("\n=== SELESAI ===\n");
}
