const mqtt = require('mqtt');

// Konfigurasi harus sama dengan server.js
const MQTT_BROKER = 'mqtt://localhost:1883';
const TOPIC_MONITOR = 'labil_listrik_123/monitor';

const client = mqtt.connect(MQTT_BROKER, {
    clientId: 'labil_sim_' + Math.random().toString(16).substr(2, 8)
});

let relay1 = 0;
let relay2 = 0;
let relay3 = 0;

client.on('connect', () => {
    console.log('Simulator terhubung ke MQTT Broker:', MQTT_BROKER);
    
    // Subscribe ke topik kontrol agar simulator tahu jika relay ditekan di frontend
    client.subscribe('labil_listrik_123/control/relay1');
    client.subscribe('labil_listrik_123/control/relay2');
    client.subscribe('labil_listrik_123/control/relay3');

    // Kirim data setiap 1 detik agar lebih real-time di UI
    setInterval(() => {
        const payload = {
            tegangan: 220 + (Math.random() * 5 - 2.5), // fluktuasi 217.5 - 222.5
            arus: 1.5 + (Math.random() * 0.5), // 1.5 - 2.0 A
            daya: 330 + (Math.random() * 110), // ~330 - 440 W
            energi: 12.5 + (Math.random() * 0.05), // simulasi kWh
            relay1: relay1,
            relay2: relay2,
            relay3: relay3,
            timestamp: new Date().toISOString()
        };

        client.publish(TOPIC_MONITOR, JSON.stringify(payload));
        console.log('Simulated Data Sent:', payload);
    }, 1000);
});

client.on('message', (topic, message) => {
    const msg = message.toString();
    console.log(`Simulator menerima perintah: ${topic} -> ${msg}`);
    
    if (topic.endsWith('relay1')) relay1 = msg === 'ON' ? 1 : 0;
    if (topic.endsWith('relay2')) relay2 = msg === 'ON' ? 1 : 0;
    if (topic.endsWith('relay3')) relay3 = msg === 'ON' ? 1 : 0;
});
