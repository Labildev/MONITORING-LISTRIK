'use strict';

const sqlite3 = require('sqlite3').verbose();
const path    = require('path');
const { RELAY_COUNT, DB_PATH } = require('./config');

// ─── Kolom relay dibentuk secara dinamis dari RELAY_COUNT ────
const RELAY_COLS = Array.from({ length: RELAY_COUNT }, (_, i) => `relay${i + 1}`);

// ─── Koneksi Database ────────────────────────────────────────
const db = new sqlite3.Database(path.resolve(__dirname, DB_PATH), (err) => {
    if (err) {
        console.error('Error connecting to SQLite database:', err.message);
    } else {
        console.log('Connected to SQLite database.');
        initDb();
    }
});

// ─── Inisialisasi Tabel ──────────────────────────────────────
function initDb() {
    db.serialize(() => {
        // Bangun definisi kolom relay secara dinamis
        const relayColDefs = RELAY_COLS.map(col => `${col} INTEGER DEFAULT 0`).join(',\n                ');

        db.run(`
            CREATE TABLE IF NOT EXISTS energy_log (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                tegangan REAL,
                arus     REAL,
                daya     REAL,
                energi   REAL,
                ${relayColDefs},
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        `);

        // Migration: tambahkan kolom relay yang belum ada (backward-compatible)
        RELAY_COLS.forEach(col => {
            db.run(`ALTER TABLE energy_log ADD COLUMN ${col} INTEGER DEFAULT 0`, (err) => {
                if (err && !err.message.includes('duplicate column name')) {
                    console.error(`Migration error (${col}):`, err.message);
                }
            });
        });

        db.run(`
            CREATE TABLE IF NOT EXISTS energy_summary (
                tanggal    DATE PRIMARY KEY,
                kwh_harian REAL,
                biaya      REAL
            )
        `);

        db.run(`
            CREATE TABLE IF NOT EXISTS settings (
                key   TEXT PRIMARY KEY,
                value TEXT
            )
        `);

        db.run(`
            CREATE TABLE IF NOT EXISTS users (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                username      TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        `);
    });
}

// ─── Simpan data sensor ke log ───────────────────────────────
const saveLog = (data) => {
    return new Promise((resolve, reject) => {
        const staticCols = ['tegangan', 'arus', 'daya', 'energi'];
        const allCols    = [...staticCols, ...RELAY_COLS];
        const values     = [
            data.tegangan, data.arus, data.daya, data.energi,
            ...RELAY_COLS.map(col => data[col] ?? 0),
        ];
        const placeholders = allCols.map(() => '?').join(', ');

        db.run(
            `INSERT INTO energy_log (${allCols.join(', ')}) VALUES (${placeholders})`,
            values,
            function (err) {
                if (err) return reject(err);
                resolve(this.lastID);
            }
        );
    });
};

// ─── Ambil log terbaru ───────────────────────────────────────
const getRecentLogs = (limit = 20) => {
    return new Promise((resolve, reject) => {
        db.all(
            'SELECT * FROM energy_log ORDER BY id DESC LIMIT ?',
            [limit],
            (err, rows) => {
                if (err) return reject(err);
                resolve(rows.reverse()); // terlama → terbaru (untuk chart)
            }
        );
    });
};

// ─── Riwayat berdasarkan periode ─────────────────────────────
const getHistoryByPeriod = (period) => {
    const PERIOD_MAP = {
        daily: {
            groupExpr: `DATE(timestamp)`,
            label:     `DATE(timestamp) as period_label`,
            limit:     30,
        },
        weekly: {
            groupExpr: `STRFTIME('%Y-%W', timestamp)`,
            label:     `STRFTIME('%Y-%W', timestamp) as period_label`,
            limit:     12,
        },
    };

    return new Promise((resolve, reject) => {
        const config = PERIOD_MAP[period];
        if (!config) return reject(new Error(`Periode tidak valid: ${period}`));

        const query = `
            SELECT
                ${config.label},
                MAX(energi) - MIN(energi) as kwh_used,
                AVG(tegangan) as avg_tegangan,
                MAX(tegangan) as max_tegangan,
                MIN(tegangan) as min_tegangan,
                AVG(arus)     as avg_arus,
                MAX(arus)     as max_arus,
                MIN(arus)     as min_arus,
                AVG(daya)     as avg_daya
            FROM energy_log
            GROUP BY ${config.groupExpr}
            ORDER BY period_label DESC
            LIMIT ${config.limit}
        `;

        db.all(query, [], (err, rows) => {
            if (err) return reject(err);
            resolve(rows);
        });
    });
};

// ─── Ringkasan energi harian ─────────────────────────────────
const getEnergySummary = () => {
    return new Promise((resolve, reject) => {
        db.all(
            'SELECT * FROM energy_summary ORDER BY tanggal DESC LIMIT 30',
            (err, rows) => {
                if (err) return reject(err);
                resolve(rows);
            }
        );
    });
};

// ─── Baca semua setting ──────────────────────────────────────
const getSettings = () => {
    return new Promise((resolve, reject) => {
        db.all('SELECT * FROM settings', [], (err, rows) => {
            if (err) return reject(err);
            const settings = {};
            rows.forEach(row => { settings[row.key] = row.value; });
            resolve(settings);
        });
    });
};

// ─── Simpan satu setting (upsert) ───────────────────────────
const saveSetting = (key, value) => {
    return new Promise((resolve, reject) => {
        db.run(
            'INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value=excluded.value',
            [key, value],
            function (err) {
                if (err) return reject(err);
                resolve(true);
            }
        );
    });
};

// ─── Cari user berdasarkan username ─────────────────────────
const getUserByUsername = (username) => {
    return new Promise((resolve, reject) => {
        db.get('SELECT * FROM users WHERE username = ?', [username], (err, row) => {
            if (err) return reject(err);
            resolve(row || null);
        });
    });
};

// ─── Buat user baru ──────────────────────────────────────────
const createUser = (username, password_hash) => {
    return new Promise((resolve, reject) => {
        db.run(
            'INSERT INTO users (username, password_hash) VALUES (?, ?)',
            [username, password_hash],
            function (err) {
                if (err) return reject(err);
                resolve(this.lastID);
            }
        );
    });
};

module.exports = {
    db,
    RELAY_COLS,
    saveLog,
    getRecentLogs,
    getHistoryByPeriod,
    getEnergySummary,
    getSettings,
    saveSetting,
    getUserByUsername,
    createUser,
};
