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
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    
    <!-- Paho MQTT Client -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jsPDF & AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="assets/img/Logo_Politeknik_Negeri_Lhokseumawe.png" alt="Logo Poltek" style="height: 40px;">
            <div class="sidebar-brand">Izaz Power</div>
        </div>
        <div class="sidebar-menu">
            <a class="menu-item active" onclick="switchView('dashboard', this)">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a class="menu-item" onclick="switchView('history', this)">
                <span class="menu-icon">🕒</span>
                <span>Riwayat Data</span>
            </a>
            <a class="menu-item" onclick="switchView('settings', this)">
                <span class="menu-icon">⚙️</span>
                <span>Pengaturan</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <main class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="btn-menu-toggle" onclick="toggleSidebar()">☰</button>
                <div id="mqtt-status" class="status-badge status-disconnected">
                    <div class="status-dot"></div>
                    <span id="status-text">Disconnected</span>
                </div>
            </div>
            <div class="topbar-right">
                <a href="api/logout.php" class="btn-logout">Logout Admin</a>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            
            <!-- VIEW: DASHBOARD -->
            <section id="view-dashboard" class="view-section active">
                <h2 style="margin-bottom: 1.5rem; font-weight: 600;">Ringkasan Sistem</h2>
                
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
            </section>

            <!-- VIEW: HISTORY -->
            <section id="view-history" class="view-section">
                <h2 style="margin-bottom: 1.5rem; font-weight: 600;">Riwayat Data Perekaman</h2>
                
                <div class="panel">
                    <div class="filter-bar" style="display: flex; gap: 12px; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: flex-end;">
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label for="filter-start" style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Mulai Tanggal</label>
                            <input type="date" id="filter-start" style="padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; background: #f9fafb; color: var(--text-main); outline: none;">
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label for="filter-end" style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Sampai Tanggal</label>
                            <input type="date" id="filter-end" style="padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; background: #f9fafb; color: var(--text-main); outline: none;">
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-primary" onclick="applyHistoryFilter()" style="padding: 11px 20px; border-radius: 8px; font-size: 14px;">Cari</button>
                            <button class="btn-page" onclick="resetHistoryFilter()" style="padding: 11px 20px; font-size: 14px;">Reset</button>
                            <button class="btn-page" onclick="exportToPDF()" style="padding: 11px 20px; font-size: 14px; background: #fee2e2; color: #991b1b; border-color: #fecaca; font-weight: 600;">📄 Export PDF</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu Rekam</th>
                                    <th>Tegangan (V)</th>
                                    <th>Arus (A)</th>
                                    <th>Daya (W)</th>
                                    <th>Energi (kWh)</th>
                                </tr>
                            </thead>
                            <tbody id="history-tbody">
                                <!-- Data diisi via JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <div class="page-info" id="page-info">Menampilkan halaman 1 dari 1</div>
                        <div class="page-controls">
                            <button class="btn-page" id="btn-prev" onclick="loadHistoryTable(-1)">Sebelumnya</button>
                            <button class="btn-page" id="btn-next" onclick="loadHistoryTable(1)">Selanjutnya</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- VIEW: SETTINGS -->
            <section id="view-settings" class="view-section">
                <h2 style="margin-bottom: 1.5rem; font-weight: 600;">Pengaturan Sistem</h2>
                
                <div class="panel" style="max-width: 600px;">
                    <div class="panel-header">
                        <div class="panel-title">Konfigurasi Nama Beban (Relay)</div>
                    </div>
                    <form id="settingsForm">
                        <div class="form-group">
                            <label>Nama Beban 1</label>
                            <input type="text" id="input-relay1" required placeholder="Contoh: Lampu Teras">
                        </div>
                        <div class="form-group">
                            <label>Nama Beban 2</label>
                            <input type="text" id="input-relay2" required placeholder="Contoh: Kipas Angin">
                        </div>
                        <div class="form-group">
                            <label>Nama Beban 3</label>
                            <input type="text" id="input-relay3" required placeholder="Contoh: Pompa Air">
                        </div>
                        <div class="form-group">
                            <label>Nama Beban 4</label>
                            <input type="text" id="input-relay4" required placeholder="Contoh: TV">
                        </div>
                        <button type="submit" class="btn-primary" id="btnSaveSettings">Simpan Perubahan</button>
                        <p id="settings-msg" style="margin-top: 10px; font-size: 14px; color: var(--success); display: none;">Pengaturan berhasil disimpan!</p>
                    </form>
                </div>
            </section>

        </div>
    </main>

    <script>
        // --- Navigation Logic ---
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        function switchView(viewId, element) {
            // Update active menu
            document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');

            // Switch section
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            document.getElementById('view-' + viewId).classList.add('active');

            // Close sidebar on mobile after clicking
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.remove('open');
            }

            // Load specific data if needed
            if (viewId === 'history' && historyCurrentPage === 1) {
                loadHistoryTable(0); // Load page 1
            }
        }

        // --- MQTT & Chart Variables ---
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
        
        // --- Init ---
        async function init() {
            initChart();
            await loadRelaySettings();
            await loadInitialChartData();
            connectMQTT();
        }

        function initChart() {
            const ctx = document.getElementById('powerChart').getContext('2d');
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
                        x: { grid: { color: '#f1f5f9' }, ticks: { color: '#64748b' } },
                        y: { grid: { color: '#f1f5f9' }, ticks: { color: '#64748b' }, beginAtZero: true }
                    },
                    animation: { duration: 0 }
                }
            });
        }

        // --- Data Loading ---
        async function loadInitialChartData() {
            try {
                // Gunakan get_history.php untuk chart juga, tapi ambil limit 50 saja
                const res = await fetch('api/get_history.php?page=1&limit=50');
                const json = await res.json();
                
                if (json.success && json.data) {
                    // Karena data dari DB di-order DESC (terbaru di atas), kita balik (reverse)
                    // agar chart bergerak dari kiri (lama) ke kanan (baru)
                    const reversedData = json.data.reverse();

                    reversedData.forEach(row => {
                        const safeDate = row.timestamp.replace(' ', 'T');
                        const time = new Date(safeDate).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        chartLabels.push(time);
                        chartData.push(row.daya);
                    });
                    
                    if (reversedData.length > 0) {
                        const last = reversedData[reversedData.length - 1];
                        updateMetrics(last.tegangan, last.arus, last.daya, last.energi);
                        updateRelayUI(last);
                    }
                    chart.update();
                }
            } catch (e) {
                console.error("Gagal load initial chart data", e);
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
                    
                    // Update UI Controls
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

                    // Update Settings Form Values
                    const inputEl = document.getElementById(`input-relay${i}`);
                    if (inputEl && !inputEl.value) {
                        inputEl.value = name;
                    }
                }
            } catch (e) {
                console.error("Gagal load pengaturan relay", e);
            }
        }

        // --- History Table Logic ---
        let historyCurrentPage = 1;
        let historyTotalPages = 1;

        async function loadHistoryTable(direction) {
            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');
            
            historyCurrentPage += direction;
            if (historyCurrentPage < 1) historyCurrentPage = 1;

            btnPrev.disabled = true;
            btnNext.disabled = true;
            document.getElementById('history-tbody').innerHTML = `<tr><td colspan="6" style="text-align:center;">Memuat data...</td></tr>`;

            const startDate = document.getElementById('filter-start') ? document.getElementById('filter-start').value : '';
            const endDate = document.getElementById('filter-end') ? document.getElementById('filter-end').value : '';

            try {
                const res = await fetch(`api/get_history.php?page=${historyCurrentPage}&limit=15&start_date=${startDate}&end_date=${endDate}`);
                const json = await res.json();

                if (json.success) {
                    historyTotalPages = json.pagination.total_pages;
                    document.getElementById('page-info').innerText = `Menampilkan halaman ${historyCurrentPage} dari ${historyTotalPages} (Total: ${json.pagination.total_rows} data)`;
                    
                    let html = '';
                    let counter = (historyCurrentPage - 1) * 15 + 1;
                    
                    json.data.forEach(row => {
                        html += `
                            <tr>
                                <td>${counter++}</td>
                                <td>${row.timestamp}</td>
                                <td>${parseFloat(row.tegangan).toFixed(1)}</td>
                                <td>${parseFloat(row.arus).toFixed(2)}</td>
                                <td>${parseFloat(row.daya).toFixed(1)}</td>
                                <td>${parseFloat(row.energi).toFixed(3)}</td>
                            </tr>
                        `;
                    });

                    if (json.data.length === 0) {
                        html = `<tr><td colspan="6" style="text-align:center;">Tidak ada data riwayat.</td></tr>`;
                    }

                    document.getElementById('history-tbody').innerHTML = html;
                }
            } catch (e) {
                document.getElementById('history-tbody').innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--danger);">Gagal memuat data jaringan.</td></tr>`;
            }

            btnPrev.disabled = historyCurrentPage <= 1;
            btnNext.disabled = historyCurrentPage >= historyTotalPages;
        }

        function applyHistoryFilter() {
            historyCurrentPage = 1;
            loadHistoryTable(0);
        }

        function resetHistoryFilter() {
            if (document.getElementById('filter-start')) document.getElementById('filter-start').value = '';
            if (document.getElementById('filter-end')) document.getElementById('filter-end').value = '';
            historyCurrentPage = 1;
            loadHistoryTable(0);
        }

        async function exportToPDF() {
            const startDate = document.getElementById('filter-start') ? document.getElementById('filter-start').value : '';
            const endDate = document.getElementById('filter-end') ? document.getElementById('filter-end').value : '';
            
            try {
                // Fetch all data matching the filter
                const res = await fetch(`api/get_history.php?export=true&start_date=${startDate}&end_date=${endDate}`);
                const json = await res.json();
                
                if (json.success) {
                    if (json.data.length === 0) {
                        alert("Tidak ada data untuk diexport pada rentang tanggal tersebut.");
                        return;
                    }
                    
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();
                    
                    doc.text("Laporan Riwayat Pemantauan Listrik (Izaz Power Monitor)", 14, 15);
                    let subtitle = "Rentang Tanggal: Semua";
                    if (startDate && endDate) subtitle = `Rentang Tanggal: ${startDate} s/d ${endDate}`;
                    else if (startDate) subtitle = `Rentang Tanggal: Mulai ${startDate}`;
                    else if (endDate) subtitle = `Rentang Tanggal: Sampai ${endDate}`;
                    doc.setFontSize(10);
                    doc.text(subtitle, 14, 22);
                    
                    const tableData = json.data.map((row, index) => [
                        index + 1,
                        row.timestamp,
                        parseFloat(row.tegangan).toFixed(1),
                        parseFloat(row.arus).toFixed(2),
                        parseFloat(row.daya).toFixed(1),
                        parseFloat(row.energi).toFixed(3)
                    ]);
                    
                    doc.autoTable({
                        startY: 28,
                        head: [['No', 'Waktu Rekam', 'Tegangan (V)', 'Arus (A)', 'Daya (W)', 'Energi (kWh)']],
                        body: tableData,
                        headStyles: { fillColor: [0, 0, 0] },
                        styles: { fontSize: 9 }
                    });
                    
                    doc.save("Laporan_Pemantauan_Listrik.pdf");
                } else {
                    alert("Gagal mengambil data untuk export.");
                }
            } catch (e) {
                console.error(e);
                alert("Kesalahan jaringan saat export PDF.");
            }
        }


        // --- MQTT Connection ---
        function connectMQTT() {
            mqttClient = new Paho.MQTT.Client(MQTT_BROKER, Number(MQTT_PORT), "/mqtt", clientID);
            mqttClient.onConnectionLost = onConnectionLost;
            mqttClient.onMessageArrived = onMessageArrived;
            mqttClient.connect({
                useSSL: isSecure,
                onSuccess: onConnect,
                onFailure: (err) => {
                    setTimeout(connectMQTT, 5000);
                }
            });
        }

        function onConnect() {
            const badge = document.getElementById('mqtt-status');
            badge.className = 'status-badge status-connected';
            document.getElementById('status-text').innerText = 'Connected';
            mqttClient.subscribe(MQTT_TOPIC_MONITOR);
            for(let i=1; i<=4; i++) {
                const tg = document.getElementById(`relay-toggle-${i}`);
                if(tg) tg.disabled = false;
            }
        }

        function onConnectionLost(responseObject) {
            const badge = document.getElementById('mqtt-status');
            badge.className = 'status-badge status-disconnected';
            document.getElementById('status-text').innerText = 'Disconnected';
            for(let i=1; i<=4; i++) {
                const tg = document.getElementById(`relay-toggle-${i}`);
                if(tg) tg.disabled = true;
            }
            setTimeout(connectMQTT, 3000);
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
                } catch(e) {}
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
                    if (toggle) toggle.checked = (rVal == 1);
                    if (statusText) {
                        statusText.innerText = (rVal == 1) ? 'Menyala (ON)' : 'Mati (OFF)';
                        statusText.style.color = (rVal == 1) ? 'var(--success)' : 'var(--text-muted)';
                    }
                }
            }
        }

        function toggleRelay(id, state) {
            if (!mqttClient || !mqttClient.isConnected()) {
                alert("Tidak terhubung ke broker MQTT!");
                return;
            }
            const payload = state ? "ON" : "OFF";
            const message = new Paho.MQTT.Message(payload);
            message.destinationName = MQTT_TOPIC_CONTROL + id;
            mqttClient.send(message);
        }

        // --- Settings Form Submission ---
        document.getElementById('settingsForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSaveSettings');
            const msg = document.getElementById('settings-msg');
            
            btn.textContent = 'Menyimpan...';
            btn.disabled = true;
            msg.style.display = 'none';
            
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
                    msg.style.display = 'block';
                    await loadRelaySettings(); // Reload names in UI
                    setTimeout(() => { msg.style.display = 'none'; }, 3000);
                } else {
                    alert(data.error || "Gagal menyimpan");
                }
            } catch(e) {
                alert("Kesalahan jaringan");
            } finally {
                btn.textContent = 'Simpan Perubahan';
                btn.disabled = false;
            }
        });

        window.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>
