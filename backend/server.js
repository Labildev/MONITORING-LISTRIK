'use strict';

const fs   = require('fs');
const path = require('path');

// ─── Jaring pengaman: tangkap semua error fatal ──────────────
process.on('uncaughtException', (err) => {
    fs.appendFileSync(
        path.join(__dirname, '../error.log'),
        `${new Date().toISOString()} - Uncaught Exception: ${err.stack}\n`
    );
    console.error('Fatal Error:', err);
});
process.on('unhandledRejection', (reason) => {
    fs.appendFileSync(
        path.join(__dirname, '../error.log'),
        `${new Date().toISOString()} - Unhandled Rejection: ${reason}\n`
    );
});

const express = require('express');
const http    = require('http');
const { Server } = require('socket.io');
const mqtt   = require('mqtt');
const cors   = require('cors');
const bcrypt = require('bcryptjs');
const db     = require('./db');
const cfg    = require('./config');

// ─── Helper: buat array [1, 2, ..., RELAY_COUNT] ─────────────
const RELAY_IDS = Array.from({ length: cfg.RELAY_COUNT }, (_, i) => i + 1);

// ─── Express & Socket.IO ──────────────────────────────────────
const app    = express();
const server = http.createServer(app);
const io     = new Server(server, {
    cors: { origin: '*', methods: ['GET', 'POST'] },
});

app.use(cors());
app.use(express.json());

// ─── MQTT Client ──────────────────────────────────────────────
const mqttClient = mqtt.connect(cfg.MQTT_BROKER, {
    clientId:        `labil_server_${Math.random().toString(16).slice(2, 10)}`,
    keepalive:       cfg.MQTT_KEEPALIVE,
    reconnectPeriod: cfg.MQTT_RECONNECT_PERIOD,
});

mqttClient.on('error', (err) => {
    console.error('MQTT Connection Error:', err.message);
});

mqttClient.on('connect', () => {
    console.log('Connected to MQTT Broker:', cfg.MQTT_BROKER);
    mqttClient.subscribe(cfg.MQTT_TOPIC_MONITOR, (err) => {
        if (!err) console.log('Subscribed to topic:', cfg.MQTT_TOPIC_MONITOR);
    });
});

// ─── Proses pesan dari ESP32 ──────────────────────────────────
mqttClient.on('message', async (topic, message) => {
    if (topic !== cfg.MQTT_TOPIC_MONITOR) return;

    try {
        const data = JSON.parse(message.toString());
        console.log('Received data:', data);

        await db.saveLog(data);

        // Otomatisasi: matikan semua relay jika daya melebihi batas
        if (data.daya > cfg.POWER_LIMIT_WATT) {
            console.warn(`Peringatan: Daya ${data.daya}W melebihi batas ${cfg.POWER_LIMIT_WATT}W! Mematikan relay.`);
            RELAY_IDS.forEach(id => {
                mqttClient.publish(`${cfg.MQTT_TOPIC_CONTROL_PREFIX}${id}`, 'OFF');
            });
            io.emit('alert', { message: `Daya berlebih (${data.daya}W)! Semua relay dimatikan.` });
        }

        io.emit('data_listrik', data);
    } catch (error) {
        console.error('Error processing MQTT message:', error.message);
    }
});

// ─── WebSocket ────────────────────────────────────────────────
io.on('connection', (socket) => {
    console.log('Client connected via WebSocket:', socket.id);
    socket.on('disconnect', () => {
        console.log('Client disconnected:', socket.id);
    });
});

// ─── REST API ─────────────────────────────────────────────────

// GET  /api/data — 50 log terbaru
app.get('/api/data', async (req, res) => {
    try {
        res.json(await db.getRecentLogs(50));
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// GET  /api/history/:period — riwayat harian / mingguan
app.get('/api/history/:period', async (req, res) => {
    try {
        const { period } = req.params;
        if (period === 'realtime') {
            return res.json(await db.getRecentLogs(50));
        }
        res.json(await db.getHistoryByPeriod(period));
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// GET  /api/energy/summary
app.get('/api/energy/summary', async (req, res) => {
    try {
        res.json(await db.getEnergySummary());
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// POST /api/relay — kirim perintah ON/OFF ke relay via MQTT
app.post('/api/relay', (req, res) => {
    const { relayId, status } = req.body;

    if (!RELAY_IDS.includes(relayId) || !['ON', 'OFF'].includes(status)) {
        return res.status(400).json({ error: `relayId harus 1-${cfg.RELAY_COUNT}, status harus ON/OFF` });
    }

    const topic = `${cfg.MQTT_TOPIC_CONTROL_PREFIX}${relayId}`;
    mqttClient.publish(topic, status, (err) => {
        if (err) return res.status(500).json({ error: 'Gagal mengirim ke MQTT' });
        res.json({ message: `Perintah ${status} dikirim ke Relay ${relayId}` });
    });
});

// POST /api/login — autentikasi dari database
app.post('/api/login', async (req, res) => {
    const { username, password } = req.body;
    if (!username || !password) {
        return res.status(400).json({ success: false, error: 'Username dan password wajib diisi' });
    }
    try {
        const user = await db.getUserByUsername(username);
        if (!user) {
            return res.status(401).json({ success: false, error: 'Username atau password salah' });
        }
        const isMatch = await bcrypt.compare(password, user.password_hash);
        if (!isMatch) {
            return res.status(401).json({ success: false, error: 'Username atau password salah' });
        }
        res.json({ success: true, token: cfg.ADMIN_TOKEN });
    } catch (err) {
        console.error('Login error:', err.message);
        res.status(500).json({ success: false, error: 'Terjadi kesalahan server' });
    }
});

// GET  /api/settings/relays — ambil nama semua relay
app.get('/api/settings/relays', async (req, res) => {
    try {
        const settings = await db.getSettings();
        const relayNames = Object.fromEntries(
            RELAY_IDS.map(id => [id, settings[`relay${id}_name`] || `Beban ${id}`])
        );
        res.json(relayNames);
    } catch (error) {
        res.status(500).json({ error: 'Gagal mengambil pengaturan' });
    }
});

// POST /api/settings/relays — simpan nama relay
app.post('/api/settings/relays', async (req, res) => {
    const { names } = req.body;
    try {
        await Promise.all(
            RELAY_IDS
                .filter(id => names[id] !== undefined)
                .map(id => db.saveSetting(`relay${id}_name`, names[id]))
        );
        io.emit('relay_names_updated', names);
        res.json({ success: true });
    } catch (error) {
        res.status(500).json({ error: 'Gagal menyimpan pengaturan' });
    }
});

// ─── Serve Frontend (SPA) ─────────────────────────────────────
const frontendPath = path.join(__dirname, '../frontend/dist');
app.use(express.static(frontendPath));
app.get(/(.*)/, (req, res) => {
    res.sendFile(path.join(frontendPath, 'index.html'));
});

// ─── Jalankan Server ──────────────────────────────────────────
server.listen(cfg.PORT, () => {
    console.log(`Backend server running on http://localhost:${cfg.PORT}`);
});
