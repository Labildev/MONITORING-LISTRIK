<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Izaz Power Monitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Paho MQTT Client -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            ⚡ Izaz Power Monitor
        </div>
        <div class="navbar-menu">
            <div id="mqtt-status" class="status-badge status-disconnected">
                <div class="status-dot"></div>
                <span id="status-text">Disconnected</span>
            </div>
            <button class="btn-settings" onclick="openSettingsModal()">Pengaturan</button>
            <a href="api/logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        
        <!-- Metrics -->
        <div class="grid-metrics">
            <div class="metric-card">
                <div class="metric-title">Tegangan</div>
                <div class="metric-value"><span id="val-tegangan">0.0</span> <span class="metric-unit">V</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Arus</div>
                <div class="metric-value"><span id="val-arus">0.00</span> <span class="metric-unit">A</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Daya Aktif</div>
                <div class="metric-value"><span id="val-daya">0.0</span> <span class="metric-unit">W</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-title">Energi Total</div>
                <div class="metric-value"><span id="val-energi">0.00</span> <span class="metric-unit">kWh</span></div>
            </div>
        </div>

        <div class="grid-main">
            <!-- Chart Panel -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Grafik Daya Real-time</div>
                </div>
                <div class="chart-container">
                    <canvas id="powerChart"></canvas>
                </div>
            </div>

            <!-- Control Panel -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Kendali Beban</div>
                </div>
                <div class="relay-list" id="relay-container">
                    <!-- Dinamis via JS -->
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Pengaturan Relay -->
    <div id="settingsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Pengaturan Nama Beban</div>
                <span class="close" onclick="closeSettingsModal()">&times;</span>
            </div>
            <form id="settingsForm">
                <div class="form-group">
                    <label>Beban 1</label>
                    <input type="text" id="input-relay1" required>
                </div>
                <div class="form-group">
                    <label>Beban 2</label>
                    <input type="text" id="input-relay2" required>
                </div>
                <div class="form-group">
                    <label>Beban 3</label>
                    <input type="text" id="input-relay3" required>
                </div>
                <div class="form-group">
                    <label>Beban 4</label>
                    <input type="text" id="input-relay4" required>
                </div>
                <button type="submit" class="btn-primary" id="btnSaveSettings">Simpan Pengaturan</button>
            </form>
        </div>
    </div>

    <script>
        // Konfigurasi MQTT (Publik)
        const MQTT_BROKER = "broker.hivemq.com";
        const isSecure = window.location.protocol === "https:";
        const MQTT_PORT = isSecure ? 8884 : 8000;
        const MQTT_TOPIC_MONITOR = "labil_listrik_123/monitor";
        const MQTT_TOPIC_CONTROL = "labil_listrik_123/control/relay";
        const clientID = "web_" + Math.random().toString(16).substr(2, 8);
        
        let mqttClient = null;
        let chart = null;
        let chartData = [];
        let chartLabels = [];
        
        // Elemen DOM
        const statusBadge = document.getElementById('mqtt-status');
        const statusText = document.getElementById('status-text');
        
        // Inisialisasi Aplikasi
        async function init() {
            initChart();
            await loadRelaySettings();
            await loadHistoryData();
            connectMQTT();
        }

        function initChart() {
            const ctx = document.getElementById('powerChart').getContext('2d');
            
            // Gradient fill
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Daya (W)',
                        data: chartData,
                        borderColor: '#3b82f6',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' }, beginAtZero: true }
                    },
                    animation: { duration: 0 } // nonaktifkan animasi untuk real-time yang cepat
                }
            });
        }

        async function loadHistoryData() {
            try {
                const res = await fetch('api/get_data.php?limit=50');
                const data = await res.json();
                
                data.forEach(row => {
                    // Replace space with 'T' for Safari/iOS compatibility
                    const safeDate = row.timestamp.replace(' ', 'T');
                    const time = new Date(safeDate).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    chartLabels.push(time);
                    chartData.push(row.daya);
                });
                
                if (data.length > 0) {
                    const last = data[data.length - 1];
                    updateMetrics(last.tegangan, last.arus, last.daya, last.energi);
                    updateRelayUI(last);
                }
                
                chart.update();
            } catch (e) {
                console.error("Gagal load data", e);
            }
        }

        async function loadRelaySettings() {
            try {
                const res = await fetch('api/get_settings.php');
                const names = await res.json();
                
                const container = document.getElementById('relay-container');
                const isEmpty = container.innerHTML.trim() === '';
                
                for(let i = 1; i <= 4; i++) {
                    const name = names[i] || `Beban ${i}`;
                    
                    if (isEmpty) {
                        container.innerHTML += `
                            <div class="relay-item">
                                <div class="relay-info">
                                    <h4 id="relay-title-${i}">${name}</h4>
                                    <p id="relay-status-${i}">Membaca status...</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" id="relay-toggle-${i}" onchange="toggleRelay(${i}, this.checked)" disabled>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        `;
                    } else {
                        const titleEl = document.getElementById(`relay-title-${i}`);
                        if (titleEl) titleEl.innerText = name;
                    }
                }
            } catch (e) {
                console.error("Gagal load pengaturan relay", e);
            }
        }

        function connectMQTT() {
            mqttClient = new Paho.MQTT.Client(MQTT_BROKER, Number(MQTT_PORT), "/mqtt", clientID);
            
            mqttClient.onConnectionLost = onConnectionLost;
            mqttClient.onMessageArrived = onMessageArrived;
            
            mqttClient.connect({
                useSSL: isSecure,
                onSuccess: onConnect,
                onFailure: (err) => {
                    console.error("MQTT Gagal Konek", err);
                    setTimeout(connectMQTT, 5000);
                }
            });
        }

        function onConnect() {
            console.log("MQTT Terhubung");
            statusBadge.className = 'status-badge status-connected';
            statusText.innerText = 'Connected to Broker';
            
            mqttClient.subscribe(MQTT_TOPIC_MONITOR);
            
            // Enable toggles
            for(let i=1; i<=4; i++) {
                const tg = document.getElementById(`relay-toggle-${i}`);
                if(tg) tg.disabled = false;
            }
        }

        function onConnectionLost(responseObject) {
            console.log("Koneksi MQTT Terputus", responseObject.errorMessage);
            statusBadge.className = 'status-badge status-disconnected';
            statusText.innerText = 'Disconnected';
            
            // Disable toggles
            for(let i=1; i<=4; i++) {
                const tg = document.getElementById(`relay-toggle-${i}`);
                if(tg) tg.disabled = true;
            }
            
            setTimeout(connectMQTT, 3000); // Reconnect
        }

        function onMessageArrived(message) {
            if (message.destinationName === MQTT_TOPIC_MONITOR) {
                try {
                    const data = JSON.parse(message.payloadString);
                    updateMetrics(data.tegangan, data.arus, data.daya, data.energi);
                    updateRelayUI(data);
                    
                    const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    chartLabels.push(time);
                    chartData.push(data.daya);
                    
                    if (chartLabels.length > 50) {
                        chartLabels.shift();
                        chartData.shift();
                    }
                    chart.update();
                } catch(e) {
                    console.error("Format data MQTT tidak valid");
                }
            }
        }

        function updateMetrics(v, a, p, e) {
            document.getElementById('val-tegangan').innerText = parseFloat(v).toFixed(1);
            document.getElementById('val-arus').innerText = parseFloat(a).toFixed(2);
            document.getElementById('val-daya').innerText = parseFloat(p).toFixed(1);
            document.getElementById('val-energi').innerText = parseFloat(e).toFixed(2);
        }

        function updateRelayUI(data) {
            for (let i = 1; i <= 4; i++) {
                const rVal = data[`relay${i}`];
                if (rVal !== undefined) {
                    const toggle = document.getElementById(`relay-toggle-${i}`);
                    const statusText = document.getElementById(`relay-status-${i}`);
                    
                    if (toggle) {
                        // Jangan ubah visual saat sedang di-klik (menunggu balasan)
                        // Tetapi untuk simplisitas, kita sinkronkan
                        toggle.checked = (rVal == 1);
                    }
                    if (statusText) {
                        statusText.innerText = (rVal == 1) ? 'Menyala (ON)' : 'Mati (OFF)';
                        statusText.style.color = (rVal == 1) ? 'var(--success)' : 'var(--text-muted)';
                    }
                }
            }
        }

        function toggleRelay(id, state) {
            if (!mqttClient || !mqttClient.isConnected()) {
                alert("Tidak terhubung ke broker!");
                return;
            }
            
            const payload = state ? "ON" : "OFF";
            const topic = MQTT_TOPIC_CONTROL + id;
            
            const message = new Paho.MQTT.Message(payload);
            message.destinationName = topic;
            mqttClient.send(message);
        }

        // Start
        window.addEventListener('DOMContentLoaded', init);

        // --- Settings Modal Logic ---
        function openSettingsModal() {
            document.getElementById('settingsModal').classList.add('show');
            // Isi form dengan nama saat ini yang ada di container
            for(let i=1; i<=4; i++) {
                const titleEl = document.querySelector(`#relay-title-${i}`);
                if(titleEl) {
                    document.getElementById(`input-relay${i}`).value = titleEl.innerText;
                }
            }
        }
        function closeSettingsModal() {
            document.getElementById('settingsModal').classList.remove('show');
        }
        
        document.getElementById('settingsForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSaveSettings');
            btn.textContent = 'Menyimpan...';
            btn.disabled = true;
            
            const payload = {
                names: {
                    1: document.getElementById('input-relay1').value,
                    2: document.getElementById('input-relay2').value,
                    3: document.getElementById('input-relay3').value,
                    4: document.getElementById('input-relay4').value
                }
            };
            
            try {
                const res = await fetch('api/save_settings.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const data = await res.json();
                if(data.success) {
                    closeSettingsModal();
                    await loadRelaySettings(); // Reload UI
                } else {
                    alert(data.error || "Gagal menyimpan");
                }
            } catch(e) {
                alert("Kesalahan jaringan");
            } finally {
                btn.textContent = 'Simpan Pengaturan';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
