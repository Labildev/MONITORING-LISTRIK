import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate, useNavigate, NavLink } from 'react-router-dom';
import io from 'socket.io-client';
import axios from 'axios';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import {
  Gauge, Activity, Cpu, DollarSign, Power, Settings, LogOut,
  LayoutDashboard, Clock, User, Moon, Sun, Menu, X, CheckCircle2, AlertTriangle, ShieldCheck, Server, Radio
} from 'lucide-react';
import './index.css';
import {
  BACKEND_URL, TARIF_PER_KWH, STORAGE_KEYS, APP_NAME, APP_TITLE,
  getRelayIds, getInitialRelayState, getDefaultRelayNames,
} from './config';

// ─── 1. Login Component (Clean Enterprise Auth) ────────────
function Login({ onLogin }) {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const res = await axios.post(`${BACKEND_URL}/api/login`, { username, password });
      if (res.data.success) {
        localStorage.setItem(STORAGE_KEYS.TOKEN, res.data.token);
        localStorage.setItem(STORAGE_KEYS.USER, username);
        onLogin(true);
        navigate('/dashboard');
      }
    } catch (err) {
      setError(err.response?.data?.error || 'Kredensial tidak valid. Silakan coba lagi.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-page-bg">
      <div className="login-card">
        {/* Left Tech Info Side */}
        <div className="login-side-info">
          <div>
            <div className="login-brand" style={{ marginBottom: '2.5rem' }}>
              <div className="brand-icon-box">
                <Gauge size={20} />
              </div>
              <span>VoltMonitor</span>
            </div>
            <div className="login-info-body">
              <h1>Power Analytics & IoT Control Platform</h1>
              <p>
                Sistem pemantauan konsumsi daya listrik terpusat berbasis ESP32 dan MQTT Telemetry Protocol.
              </p>
            </div>
          </div>

          <div className="login-tech-specs">
            <div className="spec-item">
              <span className="spec-label"><Server size={15} /> Protocol</span>
              <span className="spec-value">MQTT v3.1.1 (HiveMQ)</span>
            </div>
            <div className="spec-item">
              <span className="spec-label"><Radio size={15} /> WebSockets</span>
              <span className="spec-value">Socket.IO Real-time</span>
            </div>
            <div className="spec-item">
              <span className="spec-label"><ShieldCheck size={15} /> Encryption</span>
              <span className="spec-value">BCrypt Hash Security</span>
            </div>
          </div>
        </div>

        {/* Right Form Area */}
        <div className="login-form-area">
          <div className="login-form-header">
            <h2>Masuk ke Portal</h2>
            <p>Masukkan username dan password administrator Anda</p>
          </div>

          {error && (
            <div className="alert-box danger">
              <AlertTriangle size={18} />
              <span>{error}</span>
            </div>
          )}

          <form onSubmit={handleLogin}>
            <div className="form-group">
              <label htmlFor="username-input">Username</label>
              <div className="input-field-wrapper">
                <User size={18} className="input-icon" />
                <input
                  id="username-input"
                  type="text"
                  className="form-input"
                  value={username}
                  onChange={e => setUsername(e.target.value)}
                  placeholder="Masukkan username"
                  required
                  autoFocus
                />
              </div>
            </div>

            <div className="form-group" style={{ marginBottom: '1.75rem' }}>
              <label htmlFor="password-input">Password</label>
              <div className="input-field-wrapper">
                <Settings size={18} className="input-icon" />
                <input
                  id="password-input"
                  type="password"
                  className="form-input"
                  value={password}
                  onChange={e => setPassword(e.target.value)}
                  placeholder="Masukkan password"
                  required
                />
              </div>
            </div>

            <button type="submit" className="btn-submit-primary" disabled={loading}>
              {loading ? 'Memverifikasi...' : 'Autentikasi Masuk'}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

// ─── 2. Sidebar Component ──────────────────────────────────
function Sidebar({ username, onLogout, isOpen, closeSidebar }) {
  const displayUser = username || localStorage.getItem(STORAGE_KEYS.USER) || 'admin';
  const initialLetter = displayUser.charAt(0).toUpperCase();

  return (
    <aside className={`sidebar ${isOpen ? 'open' : ''}`}>
      <div className="sidebar-header">
        <div className="brand-badge">
          <div className="brand-icon-box">
            <Gauge size={18} />
          </div>
          <span>VoltMonitor</span>
        </div>
        <button className="mobile-menu-btn" onClick={closeSidebar} style={{ display: isOpen ? 'flex' : 'none' }}>
          <X size={20} />
        </button>
      </div>

      <div className="sidebar-links">
        <NavLink to="/dashboard" className={({ isActive }) => (isActive ? "sidebar-link active" : "sidebar-link")}>
          <LayoutDashboard size={18} /> <span>Dashboard</span>
        </NavLink>
        <NavLink to="/history" className={({ isActive }) => (isActive ? "sidebar-link active" : "sidebar-link")}>
          <Clock size={18} /> <span>Riwayat Data</span>
        </NavLink>
        <NavLink to="/settings" className={({ isActive }) => (isActive ? "sidebar-link active" : "sidebar-link")}>
          <Settings size={18} /> <span>Pengaturan Relay</span>
        </NavLink>
        <NavLink to="/profile" className={({ isActive }) => (isActive ? "sidebar-link active" : "sidebar-link")}>
          <User size={18} /> <span>Profil Akun</span>
        </NavLink>
      </div>

      <div className="sidebar-footer">
        <div className="sidebar-user-card">
          <div className="sidebar-user-info">
            <div className="user-avatar">{initialLetter}</div>
            <div className="user-details">
              <span className="user-name">{displayUser}</span>
              <span className="user-role">Administrator</span>
            </div>
          </div>
          <button className="user-logout-btn" onClick={onLogout} title="Logout">
            <LogOut size={16} />
          </button>
        </div>
      </div>
    </aside>
  );
}

// ─── 3. Dashboard Component ───────────────────────────────
function Dashboard() {
  const navigate = useNavigate();
  const relayIds = getRelayIds();
  const [currentData, setCurrentData] = useState({ tegangan: 0, arus: 0, daya: 0, energi: 0, ...getInitialRelayState() });
  const [historyData, setHistoryData] = useState([]);
  const [alertMsg, setAlertMsg] = useState(null);
  const [relayNames, setRelayNames] = useState(getDefaultRelayNames());
  const [isDeviceOnline, setIsDeviceOnline] = useState(false);

  useEffect(() => {
    if (!currentData.timestamp) return;
    const checkStatus = () => {
      const lastUpdate = new Date(currentData.timestamp).getTime();
      const now = new Date().getTime();
      setIsDeviceOnline(now - lastUpdate < 15000); // 15s tolerance threshold
    };
    checkStatus();
    const interval = setInterval(checkStatus, 2000);
    return () => clearInterval(interval);
  }, [currentData.timestamp]);

  useEffect(() => {
    const socket = io(BACKEND_URL);

    axios.get(`${BACKEND_URL}/api/settings/relays`)
      .then(res => setRelayNames(res.data))
      .catch(err => console.error("Error fetching relay names", err));

    axios.get(`${BACKEND_URL}/api/data`)
      .then(res => {
        if (res.data.length > 0) {
          setHistoryData(res.data);
          setCurrentData(res.data[res.data.length - 1]);
        }
      })
      .catch(err => console.error("Error fetching initial data", err));

    socket.on('data_listrik', (data) => {
      setCurrentData(data);
      setHistoryData(prev => {
        const newData = [...prev, data];
        if (newData.length > 25) newData.shift();
        return newData;
      });
    });

    socket.on('alert', (data) => {
      setAlertMsg(data.message);
      setTimeout(() => setAlertMsg(null), 6000);
    });

    socket.on('relay_names_updated', (names) => {
      setRelayNames(names);
    });

    return () => socket.disconnect();
  }, []);

  const toggleRelay = (id, currentStatus) => {
    const newStatus = currentStatus === 1 ? 'OFF' : 'ON';
    axios.post(`${BACKEND_URL}/api/relay`, { relayId: id, status: newStatus })
      .then(() => {
        setCurrentData(prev => ({ ...prev, [`relay${id}`]: currentStatus === 1 ? 0 : 1 }));
      })
      .catch(err => console.error(err));
  };

  const activeRelayCount = relayIds.filter(id => currentData[`relay${id}`] === 1).length;
  const biayaEstimasi = (currentData.energi * TARIF_PER_KWH).toLocaleString('id-ID');

  return (
    <div>
      {/* Title & Telemetry Status Header */}
      <div className="page-title-row">
        <div className="page-title-group">
          <h1>Dashboard Monitoring</h1>
          <p>Pemantauan telemetri kelistrikan dan kontrol beban sakelar secara real-time</p>
        </div>

        <div className="telemetry-badge-group">
          <div className={`status-pill ${isDeviceOnline ? 'online' : 'offline'}`}>
            <span className={`status-dot ${isDeviceOnline ? 'online' : 'offline'}`}></span>
            <span>{isDeviceOnline ? 'ESP32 Online' : 'ESP32 Offline'}</span>
          </div>

          <div className="status-pill" style={{ backgroundColor: 'var(--bg-subtle)', color: 'var(--text-muted)', border: '1px solid var(--border-color)' }}>
            <Cpu size={14} />
            <span>{activeRelayCount} / {relayIds.length} Beban Aktif</span>
          </div>
        </div>
      </div>

      {alertMsg && (
        <div className="alert-box danger" style={{ marginBottom: '1.5rem' }}>
          <AlertTriangle size={20} />
          <div>
            <strong>Peringatan Proteksi Daya!</strong>
            <div>{alertMsg}</div>
          </div>
        </div>
      )}

      {/* KPI Telemetry Cards */}
      <div className="kpi-grid">
        <div className="kpi-card">
          <div className="kpi-header">
            <span className="kpi-label">Tegangan Listrik</span>
            <div className="kpi-icon-box">
              <Activity size={16} />
            </div>
          </div>
          <div className="kpi-body">
            <span className="kpi-value mono-num">{currentData.tegangan.toFixed(1)}</span>
            <span className="kpi-unit">V</span>
          </div>
          <div className="kpi-footer">
            <span>Nominal: 220.0 V</span>
            <span>AC Grid</span>
          </div>
        </div>

        <div className="kpi-card">
          <div className="kpi-header">
            <span className="kpi-label">Arus Beban</span>
            <div className="kpi-icon-box">
              <Radio size={16} />
            </div>
          </div>
          <div className="kpi-body">
            <span className="kpi-value mono-num">{currentData.arus.toFixed(2)}</span>
            <span className="kpi-unit">A</span>
          </div>
          <div className="kpi-footer">
            <span>Sensor: PZEM-004T</span>
            <span>Realtime</span>
          </div>
        </div>

        <div className="kpi-card">
          <div className="kpi-header">
            <span className="kpi-label">Daya Aktif</span>
            <div className="kpi-icon-box">
              <Cpu size={16} />
            </div>
          </div>
          <div className="kpi-body">
            <span className="kpi-value mono-num">{currentData.daya.toFixed(1)}</span>
            <span className="kpi-unit">W</span>
          </div>
          <div className="kpi-footer">
            <span>Power Factor: ~0.95</span>
            <span>Live Load</span>
          </div>
        </div>

        <div className="kpi-card">
          <div className="kpi-header">
            <span className="kpi-label">Total Akumulasi</span>
            <div className="kpi-icon-box">
              <DollarSign size={16} />
            </div>
          </div>
          <div className="kpi-body">
            <span className="kpi-value mono-num">{currentData.energi.toFixed(3)}</span>
            <span className="kpi-unit">kWh</span>
          </div>
          <div className="kpi-footer">
            <span>Tarif: Rp {TARIF_PER_KWH}/kWh</span>
            <span>Akumulatif</span>
          </div>
        </div>
      </div>

      {/* Main Grid: Chart & Relay Matrix */}
      <div className="dashboard-main-grid">
        {/* Left: Recharts Live Power Area */}
        <div className="panel-card">
          <div className="panel-card-header">
            <h3>
              <Activity size={18} style={{ color: 'var(--primary)' }} />
              Grafik Telemetri Daya Aktif (Watt)
            </h3>
            <span className="header-tag">STREAMING REALTIME</span>
          </div>

          <div className="chart-wrapper">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={historyData}>
                <defs>
                  <linearGradient id="powerGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="var(--primary)" stopOpacity={0.15} />
                    <stop offset="95%" stopColor="var(--primary)" stopOpacity={0.0} />
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" stroke="var(--border-color)" vertical={false} />
                <XAxis
                  dataKey="timestamp"
                  tickFormatter={(tick) => {
                    if (!tick) return '';
                    const d = new Date(tick);
                    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}:${String(d.getSeconds()).padStart(2, '0')}`;
                  }}
                  stroke="var(--text-subtle)"
                  tick={{ fill: 'var(--text-muted)', fontSize: 11 }}
                  axisLine={false}
                  tickLine={false}
                />
                <YAxis
                  stroke="var(--text-subtle)"
                  tick={{ fill: 'var(--text-muted)', fontSize: 11 }}
                  axisLine={false}
                  tickLine={false}
                />
                <Tooltip
                  contentStyle={{
                    backgroundColor: 'var(--bg-surface)',
                    border: '1px solid var(--border-color)',
                    borderRadius: '6px',
                    color: 'var(--text-main)',
                    boxShadow: 'var(--shadow-dropdown)',
                    fontSize: '0.825rem'
                  }}
                  labelFormatter={(label) => new Date(label).toLocaleTimeString('id-ID')}
                />
                <Area
                  type="monotone"
                  dataKey="daya"
                  stroke="var(--primary)"
                  strokeWidth={2}
                  fillOpacity={1}
                  fill="url(#powerGradient)"
                  isAnimationActive={true}
                  animationDuration={300}
                />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Right: Relay Control Matrix */}
        <div className="panel-card">
          <div className="panel-card-header">
            <h3>
              <Power size={18} style={{ color: 'var(--primary)' }} />
              Matrix Kontrol Relay
            </h3>
            <button className="btn-secondary" style={{ padding: '0.25rem 0.65rem', fontSize: '0.775rem' }} onClick={() => navigate('/settings')}>
              Edit Label
            </button>
          </div>

          <div className="relay-list">
            {relayIds.map((id) => {
              const isON = currentData[`relay${id}`] === 1;
              return (
                <div className={`relay-card ${isON ? 'active' : ''}`} key={id}>
                  <div className="relay-card-left">
                    <span className={`relay-status-indicator ${isON ? 'active' : ''}`}></span>
                    <div>
                      <div className="relay-title">{relayNames[id] || `Relay ${id}`}</div>
                      <div className="relay-subtitle">LOAD #{id} • {isON ? 'STATUS: ACTIVE' : 'STATUS: OFF'}</div>
                    </div>
                  </div>

                  <label className="switch-control">
                    <input
                      type="checkbox"
                      checked={isON}
                      onChange={() => toggleRelay(id, currentData[`relay${id}`])}
                    />
                    <span className="switch-slider"></span>
                  </label>
                </div>
              );
            })}
          </div>

          {/* Cost Estimate Section */}
          <div className="cost-card">
            <div className="cost-row">
              <span className="cost-title">Estimasi Tagihan Energi</span>
              <span className="cost-value-highlight">Rp {biayaEstimasi}</span>
            </div>
            <div className="cost-caption">
              Kalkulasi otomatis berdasarkan akumulasi {currentData.energi.toFixed(3)} kWh (Tarif PLN Rp {TARIF_PER_KWH}/kWh)
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── 4. History Component ──────────────────────────────────
function History() {
  const [data, setData] = useState([]);
  const [filter, setFilter] = useState('realtime');
  const [totalKwh, setTotalKwh] = useState(0);

  useEffect(() => {
    axios.get(`${BACKEND_URL}/api/history/${filter}`)
      .then(res => {
        let fetchedData = res.data;
        if (filter === 'realtime') {
          fetchedData = fetchedData.reverse();
          if (fetchedData.length > 0) {
            const maxE = Math.max(...fetchedData.map(d => d.energi));
            const minE = Math.min(...fetchedData.map(d => d.energi));
            setTotalKwh(maxE - minE);
          } else {
            setTotalKwh(0);
          }
        } else {
          const sum = fetchedData.reduce((acc, curr) => acc + curr.kwh_used, 0);
          setTotalKwh(sum);
        }
        setData(fetchedData);
      })
      .catch(err => console.error(err));
  }, [filter]);

  return (
    <div>
      <div className="page-title-row">
        <div className="page-title-group">
          <h1>Riwayat Telemetri Energi</h1>
          <p>Laporan konsumsi energi listrik dan riwayat log parameter</p>
        </div>
      </div>

      {/* Filter Tabs */}
      <div className="filter-tabs">
        <button className={`tab-btn ${filter === 'realtime' ? 'active' : ''}`} onClick={() => setFilter('realtime')}>
          Realtime Stream
        </button>
        <button className={`tab-btn ${filter === 'daily' ? 'active' : ''}`} onClick={() => setFilter('daily')}>
          Ringkasan Harian
        </button>
        <button className={`tab-btn ${filter === 'weekly' ? 'active' : ''}`} onClick={() => setFilter('weekly')}>
          Ringkasan Mingguan
        </button>
      </div>

      {/* Table Panel */}
      <div className="panel-card">
        <div className="table-responsive">
          <table className="data-table">
            <thead>
              <tr>
                <th>{filter === 'realtime' ? 'Waktu Logging' : 'Periode Waktu'}</th>
                <th>Tegangan (V)</th>
                <th>Arus (A)</th>
                <th>Daya (W)</th>
                <th>{filter === 'realtime' ? 'Energi Akumulasi (kWh)' : 'Konsumsi kWh'}</th>
              </tr>
            </thead>
            <tbody>
              {data.map((item, index) => {
                const isSummary = item.period_label !== undefined;
                return (
                  <tr key={index}>
                    <td className="mono-num">
                      {isSummary ? item.period_label : new Date(item.timestamp).toLocaleString('id-ID')}
                    </td>
                    <td className="mono-num">
                      {isSummary ? (item.avg_tegangan || 0).toFixed(1) : (item.tegangan || 0).toFixed(1)}
                    </td>
                    <td className="mono-num">
                      {isSummary ? (item.avg_arus || 0).toFixed(2) : (item.arus || 0).toFixed(2)}
                    </td>
                    <td className="mono-num">
                      {isSummary ? (item.avg_daya || 0).toFixed(1) : (item.daya || 0).toFixed(1)}
                    </td>
                    <td className="mono-num" style={{ fontWeight: 600, color: 'var(--primary)' }}>
                      {isSummary ? (item.kwh_used || 0).toFixed(4) : (item.energi || 0).toFixed(3)}
                    </td>
                  </tr>
                );
              })}
              {data.length === 0 && (
                <tr>
                  <td colSpan="5" style={{ textAlign: 'center', padding: '2rem', color: 'var(--text-muted)' }}>
                    Belum ada data riwayat yang tercatat dalam sistem.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

// ─── 5. Settings Component ────────────────────────────────
function SettingsPage() {
  const relayIds = getRelayIds();
  const [relayNames, setRelayNames] = useState(Object.fromEntries(getRelayIds().map(id => [id, ''])));
  const [isSaving, setIsSaving] = useState(false);
  const [successMsg, setSuccessMsg] = useState('');

  useEffect(() => {
    axios.get(`${BACKEND_URL}/api/settings/relays`)
      .then(res => setRelayNames(res.data))
      .catch(err => console.error(err));
  }, []);

  const handleUpdateRelay = async (e) => {
    e.preventDefault();
    setIsSaving(true);
    setSuccessMsg('');
    try {
      await axios.post(`${BACKEND_URL}/api/settings/relays`, { names: relayNames });
      setSuccessMsg('Konfigurasi nama relay berhasil disimpan ke database server.');
      setTimeout(() => setSuccessMsg(''), 4000);
    } catch (err) {
      alert("Gagal menyimpan pengaturan relay.");
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div>
      <div className="page-title-row">
        <div className="page-title-group">
          <h1>Pengaturan Sistem</h1>
          <p>Kustomisasi penamaan beban relay dan parameter operasional</p>
        </div>
      </div>

      <div className="panel-card settings-panel">
        <div className="panel-card-header">
          <h3>
            <Settings size={18} style={{ color: 'var(--primary)' }} />
            Penamaan Perangkat (Relay 1 - {relayIds.length})
          </h3>
        </div>

        {successMsg && (
          <div className="alert-box" style={{ backgroundColor: 'var(--success-light)', color: 'var(--success)', border: '1px solid rgba(52, 211, 153, 0.3)', marginBottom: '1.25rem' }}>
            <CheckCircle2 size={18} />
            <span>{successMsg}</span>
          </div>
        )}

        <form onSubmit={handleUpdateRelay}>
          {relayIds.map(id => (
            <div className="form-group" key={id}>
              <label htmlFor={`relay-input-${id}`}>Label Beban / Relay #{id}</label>
              <input
                id={`relay-input-${id}`}
                type="text"
                className="form-input"
                style={{ paddingLeft: '0.85rem' }}
                value={relayNames[id] || ''}
                onChange={e => setRelayNames(prev => ({ ...prev, [id]: e.target.value }))}
                placeholder={`Contoh: Lampu Teras, AC Kamar ${id}`}
              />
            </div>
          ))}

          <div className="form-actions">
            <button type="submit" className="btn-submit-primary" style={{ width: 'auto' }} disabled={isSaving}>
              {isSaving ? 'Menyimpan...' : 'Simpan Konfigurasi'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

// ─── 6. Profile Component ─────────────────────────────────
function Profile({ onLogout }) {
  const [username, setUsername] = useState('');

  useEffect(() => {
    setUsername(localStorage.getItem(STORAGE_KEYS.USER) || 'admin');
  }, []);

  return (
    <div>
      <div className="page-title-row">
        <div className="page-title-group">
          <h1>Profil Administrator</h1>
          <p>Informasi akun dan pengelolaan sesi pengguna aktif</p>
        </div>
      </div>

      <div className="panel-card settings-panel">
        <div className="panel-card-header">
          <h3>
            <User size={18} style={{ color: 'var(--primary)' }} />
            Detail Identitas Sesi
          </h3>
        </div>

        <div className="form-group">
          <label>Username Aktif</label>
          <input
            type="text"
            className="form-input"
            style={{ paddingLeft: '0.85rem' }}
            value={username}
            disabled
          />
        </div>

        <div className="form-group">
          <label>Hak Akses Sesi</label>
          <input
            type="text"
            className="form-input"
            style={{ paddingLeft: '0.85rem' }}
            value="System Administrator (Full Access)"
            disabled
          />
        </div>

        <div style={{ marginTop: '2.5rem', paddingTop: '1.5rem', borderTop: '1px solid var(--border-color)' }}>
          <h4 style={{ fontSize: '0.95rem', fontWeight: 600, color: 'var(--danger)', marginBottom: '0.35rem' }}>
            Akhiri Sesi Login
          </h4>
          <p style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginBottom: '1.25rem' }}>
            Keluar dari sistem dan hapus token autentikasi lokal pada peramban ini.
          </p>

          <button onClick={onLogout} className="btn-danger-outline">
            <LogOut size={16} />
            <span>Keluar dari Aplikasi</span>
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── 7. Main App Shell Component ───────────────────────────
function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [theme, setTheme] = useState(localStorage.getItem(STORAGE_KEYS.THEME) || 'light');
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);

  useEffect(() => {
    const token = localStorage.getItem(STORAGE_KEYS.TOKEN);
    if (token) setIsAuthenticated(true);
  }, []);

  useEffect(() => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem(STORAGE_KEYS.THEME, theme);
  }, [theme]);

  const toggleTheme = () => {
    setTheme(prev => (prev === 'light' ? 'dark' : 'light'));
  };

  const handleLogout = () => {
    localStorage.removeItem(STORAGE_KEYS.TOKEN);
    localStorage.removeItem(STORAGE_KEYS.USER);
    setIsAuthenticated(false);
    window.location.href = '/login';
  };

  const currentUser = localStorage.getItem(STORAGE_KEYS.USER) || 'admin';

  return (
    <Router>
      <div className="app-container">
        {isAuthenticated && (
          <>
            <div
              className={`sidebar-overlay ${isSidebarOpen ? 'show' : ''}`}
              onClick={() => setIsSidebarOpen(false)}
            ></div>
            <Sidebar
              username={currentUser}
              onLogout={handleLogout}
              isOpen={isSidebarOpen}
              closeSidebar={() => setIsSidebarOpen(false)}
            />
          </>
        )}

        <div className={isAuthenticated ? "main-content" : "full-page-container"}>
          {isAuthenticated && (
            <>
              {/* Desktop Top Header Bar with Theme Toggle */}
              <div className="top-header-bar">
                <div className="top-header-title">
                  {/* <Gauge size={16} color="var(--primary)" /> */}
                  <span>IoT Power Analytics Platform</span>
                </div>
                <button className="theme-toggle-navbar" onClick={toggleTheme}>
                  {theme === 'light' ? <Moon size={14} /> : <Sun size={14} />}
                  <span>{theme === 'light' ? 'Mode Gelap' : 'Mode Terang'}</span>
                </button>
              </div>

              {/* Mobile Header Bar */}
              <div className="mobile-header">
                <div className="brand-badge">
                  <div className="brand-icon-box">
                    <Gauge size={16} />
                  </div>
                  <span style={{ fontSize: '1rem' }}>VoltMonitor</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.65rem' }}>
                  <button className="mobile-icon-btn" onClick={toggleTheme}>
                    {theme === 'light' ? <Moon size={18} /> : <Sun size={18} />}
                  </button>
                  <button className="mobile-menu-btn" onClick={() => setIsSidebarOpen(true)}>
                    <Menu size={20} />
                  </button>
                </div>
              </div>
            </>
          )}

          <div className={isAuthenticated ? "page-container" : ""}>
            <Routes>
              <Route
                path="/"
                element={isAuthenticated ? <Navigate to="/dashboard" /> : <Navigate to="/login" />}
              />
              <Route
                path="/login"
                element={isAuthenticated ? <Navigate to="/dashboard" /> : <Login onLogin={setIsAuthenticated} />}
              />
              <Route
                path="/dashboard"
                element={isAuthenticated ? <Dashboard /> : <Navigate to="/login" />}
              />
              <Route
                path="/history"
                element={isAuthenticated ? <History /> : <Navigate to="/login" />}
              />
              <Route
                path="/settings"
                element={isAuthenticated ? <SettingsPage /> : <Navigate to="/login" />}
              />
              <Route
                path="/profile"
                element={isAuthenticated ? <Profile onLogout={handleLogout} /> : <Navigate to="/login" />}
              />
            </Routes>
          </div>
        </div>
      </div>
    </Router>
  );
}

export default App;
