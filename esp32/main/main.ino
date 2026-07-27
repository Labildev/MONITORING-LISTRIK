// ============================================================
//  Monitoring Listrik – ESP32 Firmware
//  Wiring:
//    GPIO21 (SDA)  → LCD SDA      (I2C default)
//    GPIO22 (SCL)  → LCD SCL      (I2C default)
//    GPIO25        → Relay 1 IN   (Active Low)
//    GPIO26        → Relay 2 IN   (Active Low)
//    GPIO27        → Relay 3 IN   (Active Low)
//    GPIO14        → Relay 4 IN   (Active Low)
//    GPIO16 (RX2)  → PZEM TX      (via voltage divider, UART2)
//    GPIO17 (TX2)  → PZEM RX      (langsung,              UART2)
//    GND           → Semua GND    (wajib disatukan)
// ============================================================

#include <WiFi.h>
#include <PubSubClient.h>
#include <PZEM004Tv30.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// --- Konfigurasi WiFi ---
const char* ssid     = "NAMA_WIFI_ANDA";
const char* password = "PASSWORD_WIFI_ANDA";

// --- Konfigurasi MQTT ---
// Ganti dengan IP lokal komputer yang menjalankan server.js
// Contoh: "192.168.1.15"
const char* mqtt_server = "91.108.119.47";
const int   mqtt_port   = 1883;
const char* topic_monitor        = "labil_listrik_123/monitor";
const char* topic_control_prefix = "labil_listrik_123/control/relay";

WiFiClient   espClient;
PubSubClient client(espClient);

// --- Konfigurasi PZEM-004T ---
// UART2: RX2=GPIO16 (dari PZEM TX via voltage divider)
//        TX2=GPIO17 (ke  PZEM RX, langsung)
PZEM004Tv30 pzem(Serial2, 16, 17);

// --- Konfigurasi Relay (Active Low) ---
const int RELAY_PIN[4] = {25, 26, 27, 14};   // Relay 1–4
int       relay_status[4] = {0, 0, 0, 0};

// --- Konfigurasi LCD I2C ---
// Alamat I2C LCD umumnya 0x27 atau 0x3F
// SDA=GPIO21, SCL=GPIO22 (I2C default ESP32)
LiquidCrystal_I2C lcd(0x27, 16, 2);

unsigned long lastMsg    = 0;
unsigned long lastLCD    = 0;
int           lcdPage    = 0;   // halaman tampilan LCD

// ============================================================
//  FUNGSI: Koneksi WiFi
// ============================================================
void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    lcd.setCursor(0, 1);
    lcd.print("................".substring(0, (millis() / 500) % 17));
  }

  Serial.println("\nWiFi connected");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("WiFi Connected!");
  lcd.setCursor(0, 1);
  lcd.print(WiFi.localIP());
  delay(2000);
}

// ============================================================
//  FUNGSI: MQTT Callback – terima perintah kontrol relay
// ============================================================
void callback(char* topic, byte* message, unsigned int length) {
  Serial.print("Message arrived on topic: ");
  Serial.print(topic);
  Serial.print(". Message: ");

  String messageTemp;
  for (unsigned int i = 0; i < length; i++) {
    Serial.print((char)message[i]);
    messageTemp += (char)message[i];
  }
  Serial.println();

  String topicStr = String(topic);

  for (int r = 0; r < 4; r++) {
    String expectedTopic = String(topic_control_prefix) + String(r + 1);
    if (topicStr == expectedTopic) {
      if (messageTemp == "ON") {
        digitalWrite(RELAY_PIN[r], LOW);   // Active Low → ON
        relay_status[r] = 1;
      } else if (messageTemp == "OFF") {
        digitalWrite(RELAY_PIN[r], HIGH);  // Active Low → OFF
        relay_status[r] = 0;
      }
      Serial.printf("Relay %d -> %s\n", r + 1, messageTemp.c_str());
      break;
    }
  }
}

// ============================================================
//  FUNGSI: Reconnect MQTT
// ============================================================
void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Connecting MQTT");

    String clientId = "ESP32Client-";
    clientId += String(random(0xffff), HEX);

    if (client.connect(clientId.c_str())) {
      Serial.println("connected");

      // Subscribe ke semua topik kontrol relay (1–4)
      for (int r = 1; r <= 4; r++) {
        String t = String(topic_control_prefix) + String(r);
        client.subscribe(t.c_str());
        Serial.print("Subscribed: ");
        Serial.println(t);
      }

      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("MQTT Connected!");
      delay(1500);

    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");

      lcd.setCursor(0, 1);
      lcd.print("Retry in 5s...");
      delay(5000);
    }
  }
}

// ============================================================
//  FUNGSI: Update tampilan LCD (bergantian tiap 4 detik)
// ============================================================
void updateLCD(float voltage, float current, float power, float energy) {
  lcd.clear();

  if (lcdPage == 0) {
    // Halaman 1: Tegangan & Arus
    lcd.setCursor(0, 0);
    lcd.print("V:");
    lcd.print(voltage, 1);
    lcd.print("V  I:");
    lcd.print(current, 2);
    lcd.print("A");

    lcd.setCursor(0, 1);
    lcd.print("P:");
    lcd.print(power, 1);
    lcd.print("W");
  } else {
    // Halaman 2: Energi & Status Relay
    lcd.setCursor(0, 0);
    lcd.print("E:");
    lcd.print(energy, 3);
    lcd.print("kWh");

    lcd.setCursor(0, 1);
    lcd.print("R:");
    for (int r = 0; r < 4; r++) {
      lcd.print(relay_status[r] ? "1" : "0");
      if (r < 3) lcd.print(" ");
    }
  }

  lcdPage = 1 - lcdPage;   // toggle halaman
}

// ============================================================
//  SETUP
// ============================================================
void setup() {
  Serial.begin(115200);

  // Inisialisasi I2C LCD (SDA=21, SCL=22 sudah default ESP32)
  Wire.begin(21, 22);
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("Monitoring");
  lcd.setCursor(0, 1);
  lcd.print("Listrik v1.0");
  delay(2000);

  // Inisialisasi pin Relay (semua OFF dulu)
  for (int r = 0; r < 4; r++) {
    pinMode(RELAY_PIN[r], OUTPUT);
    digitalWrite(RELAY_PIN[r], HIGH);   // Active Low → HIGH = OFF
  }

  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

// ============================================================
//  LOOP
// ============================================================
void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  unsigned long now = millis();

  // Kirim data sensor ke MQTT setiap 5 detik
  if (now - lastMsg > 5000) {
    lastMsg = now;

    // Baca data dari PZEM-004T
    float voltage = pzem.voltage();
    float current = pzem.current();
    float power   = pzem.power();
    float energy  = pzem.energy();

    // Tangani nilai NaN (sensor belum siap / tidak terhubung)
    if (isnan(voltage)) voltage = 0.0;
    if (isnan(current)) current = 0.0;
    if (isnan(power))   power   = 0.0;
    if (isnan(energy))  energy  = 0.0;

    // Buat payload JSON
    String payload = "{";
    payload += "\"tegangan\":"  + String(voltage, 1) + ",";
    payload += "\"arus\":"      + String(current, 3) + ",";
    payload += "\"daya\":"      + String(power,   1) + ",";
    payload += "\"energi\":"    + String(energy,  3) + ",";
    payload += "\"relay1\":"    + String(relay_status[0]) + ",";
    payload += "\"relay2\":"    + String(relay_status[1]) + ",";
    payload += "\"relay3\":"    + String(relay_status[2]) + ",";
    payload += "\"relay4\":"    + String(relay_status[3]);
    payload += "}";

    Serial.print("Publish: ");
    Serial.println(payload);
    client.publish(topic_monitor, payload.c_str());

    // Update LCD setiap 4 detik (bergantian halaman)
    if (now - lastLCD > 4000) {
      lastLCD = now;
      updateLCD(voltage, current, power, energy);
    }
  }
}
