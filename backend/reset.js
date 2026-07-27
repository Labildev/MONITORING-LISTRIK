'use strict';

/**
 * Reset Database — hapus semua data sensor, pertahankan users & settings.
 * Jalankan: npm run reset -w backend
 *
 * Tabel yang di-reset:  energy_log, energy_summary
 * Tabel yang DIJAGA:    users, settings
 */

const db = require('./db').db;

function run(sql, label) {
    return new Promise((resolve, reject) => {
        db.run(sql, [], function (err) {
            if (err) {
                console.error(`  ❌ Gagal: ${label} — ${err.message}`);
                return reject(err);
            }
            console.log(`  ✅ ${label} (${this.changes} baris dihapus)`);
            resolve(this.changes);
        });
    });
}

async function reset() {
    console.log('🔄 Mereset database...\n');

    // Tunggu sebentar agar koneksi DB siap
    await new Promise(r => setTimeout(r, 500));

    await run('DELETE FROM energy_log',     'Hapus energy_log');
    await run('DELETE FROM energy_summary', 'Hapus energy_summary');

    // Reset auto-increment counter
    await run("DELETE FROM sqlite_sequence WHERE name='energy_log'",     'Reset sequence energy_log');
    await run("DELETE FROM sqlite_sequence WHERE name='energy_summary'", 'Reset sequence energy_summary');

    console.log('\n✅ Reset selesai. Data sensor bersih, users & settings tetap aman.');
    process.exit(0);
}

reset().catch(err => {
    console.error('❌ Reset error:', err);
    process.exit(1);
});
