#include <ESP8266WiFi.h>
#include <DHT.h>
#include <ArduinoJson.h>

// ============ KONFIGURASI DHT11 ============
#define DHTPIN D4       // Menggunakan D4 sesuai permintaan
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

// ============ KONFIGURASI RELAY & LED ============
#define PIN_KIPAS D1       // Relay 1 (Kipas 1)
#define PIN_KIPAS2 D6      // Relay 2 (Kipas 2)
#define PIN_LAMPU_BIRU D2  // LED Biru (Active High)
#define PIN_PENGHANGAT D5  // Relay 4 (Lampu Pijar) 
#define PIN_LAMPU_MERAH D7 // LED Merah (Active High)

// ============ KONFIGURASI WIFI & API ============
const char* ssid = "monitoring_suhu_ruangan_bayi";               // Sesuaikan dengan WiFi Anda
const char* password = "11111111";
const char* serverIP = "192.168.157.241";        // IP Laptop Anda yang benar
const int serverPort = 8000;
const char* apiEndpoint = "/api/monitoring/store";
const char* deviceId = "DEVICE_LCF7P6RQYR_1777015359"; // Device ID Anda

// ============ VARIABEL WAKTU ============
unsigned long lastReadTime = 0;
unsigned long lastSendTime = 0;

void setup() {
  Serial.begin(115200);
  delay(100);

  // Inisialisasi Pin Relay sebagai OUTPUT
  pinMode(PIN_KIPAS, OUTPUT);
  pinMode(PIN_KIPAS2, OUTPUT);
  pinMode(PIN_LAMPU_BIRU, OUTPUT);
  pinMode(PIN_PENGHANGAT, OUTPUT);
  pinMode(PIN_LAMPU_MERAH, OUTPUT);

  // Matikan semua sebagai default
  digitalWrite(PIN_KIPAS, HIGH);
  digitalWrite(PIN_KIPAS2, HIGH);
  digitalWrite(PIN_LAMPU_BIRU, LOW);
  digitalWrite(PIN_PENGHANGAT, HIGH);
  digitalWrite(PIN_LAMPU_MERAH, LOW);

  Serial.println("\n\n=== SISTEM MONITORING SUHU BAYI ===");
  dht.begin();
  delay(2000); // Waktu pemanasan sensor

  // Koneksi ke WiFi
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan ke WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n✅ WiFi TERHUBUNG! IP: " + WiFi.localIP().toString());
}

void controlDevices(float t) {
  if (t > 30.0) {
    // Suhu panas (> 30): 2 Kipas & Lampu Merah ON
    digitalWrite(PIN_KIPAS, LOW);
    digitalWrite(PIN_KIPAS2, LOW);
    digitalWrite(PIN_LAMPU_MERAH, HIGH);
    digitalWrite(PIN_LAMPU_BIRU, LOW);
    digitalWrite(PIN_PENGHANGAT, HIGH);
    Serial.println("⚙️  Kondisi: PANAS (> 30) -> 2 Kipas & LED Merah ON");
  } 
  else if (t < 28.0) {
    // Suhu dingin (< 28): Penghangat & Lampu Biru ON
    digitalWrite(PIN_KIPAS, HIGH);
    digitalWrite(PIN_KIPAS2, HIGH);
    digitalWrite(PIN_LAMPU_BIRU, HIGH);
    digitalWrite(PIN_LAMPU_MERAH, LOW);
    digitalWrite(PIN_PENGHANGAT, LOW);
    Serial.println("⚙️  Kondisi: DINGIN (< 28) -> Penghangat & LED Biru ON");
  } 
  else {
    // Suhu normal (28 - 30): 1 Kipas ON
    digitalWrite(PIN_KIPAS, LOW);
    digitalWrite(PIN_KIPAS2, HIGH);
    digitalWrite(PIN_LAMPU_BIRU, LOW);
    digitalWrite(PIN_LAMPU_MERAH, LOW);
    digitalWrite(PIN_PENGHANGAT, HIGH);
    Serial.println("⚙️  Kondisi: NORMAL (28-30) -> 1 Kipas ON");
  }
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) return;

  // 1. Baca Sensor Setiap 2 Detik (Jangan lebih cepat dari ini)
  if (millis() - lastReadTime >= 2000) {
    lastReadTime = millis();
    
    float h = dht.readHumidity();
    float t = dht.readTemperature();

    if (isnan(h) || isnan(t)) {
      Serial.println("⚠️ Gagal membaca sensor fisik DHT11! Cek kabel di pin D4.");
      // Skip pengiriman data jika sensor error
      return;
    }

    // 2. Kendalikan Kipas/Lampu berdasarkan suhu terbaru
    controlDevices(t);

    // 3. Kirim Data ke Laravel Setiap 10 Detik
    if (millis() - lastSendTime >= 10000) {
      lastSendTime = millis();
      sendDataToLaravel(t, h);
    }
  }
}

void sendDataToLaravel(float t, float h) {
  WiFiClient client;
  if (!client.connect(serverIP, serverPort)) {
    Serial.println("❌ Gagal terhubung ke Laravel di IP " + String(serverIP));
    return;
  }

  // Buat JSON payload dengan memori yang cukup (512 bytes)
  StaticJsonDocument<512> doc;
  doc["device_id"] = deviceId;
  doc["temperature"] = t;
  doc["humidity"] = h;
  doc["status_kipas_1"] = (digitalRead(PIN_KIPAS) == LOW) ? "ON" : "OFF";
  doc["status_kipas_2"] = (digitalRead(PIN_KIPAS2) == LOW) ? "ON" : "OFF";
  doc["status_lampu_biru"] = (digitalRead(PIN_LAMPU_BIRU) == HIGH) ? "ON" : "OFF";
  doc["status_lampu_merah"] = (digitalRead(PIN_LAMPU_MERAH) == HIGH) ? "ON" : "OFF";
  doc["status_penghangat"] = (digitalRead(PIN_PENGHANGAT) == LOW) ? "ON" : "OFF";

  String jsonPayload;
  serializeJson(doc, jsonPayload);

  // GABUNGKAN seluruh HTTP Request menjadi satu string (Mencegah PHP Artisan Timeout/Fragmentasi)
  String request = "POST " + String(apiEndpoint) + " HTTP/1.1\r\n";
  request += "Host: " + String(serverIP) + "\r\n";
  request += "Content-Type: application/json\r\n";
  request += "Content-Length: " + String(jsonPayload.length()) + "\r\n";
  request += "Connection: close\r\n\r\n";
  request += jsonPayload;

  // Kirim secara utuh
  client.print(request);
  Serial.println("📡 Data Terkirim! Menunggu respons...");

  // Baca respons dengan Timeout 15 Detik
  unsigned long timeout = millis();
  while (client.connected() || client.available()) {
    if (client.available()) {
      String line = client.readStringUntil('\n');
      Serial.println(">> " + line); // Print jawaban asli dari server
      
      if (line.indexOf("200 OK") > -1 || line.indexOf("201 Created") > -1) {
        Serial.println("✅ SUKSES! Data masuk ke website Laravel!");
      }
      timeout = millis();
    }
    if (millis() - timeout > 15000) {
      Serial.println("❌ Timeout menunggu respons Laravel (15 dtk).");
      break;
    }
  }
  
  client.stop();
  Serial.println("----------------------------------------");
}