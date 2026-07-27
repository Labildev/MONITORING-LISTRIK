'use strict';

/**
 * Script debug login — jalankan: node debug-login.js
 * Ini akan test setiap langkah proses login secara terpisah.
 */

const bcryptjs = require('bcryptjs');
const db       = require('./db');

async function debug() {
    console.log('══════════════════════════════════════');
    console.log('  DEBUG LOGIN');
    console.log('══════════════════════════════════════\n');

    // Tunggu DB siap
    await new Promise(r => setTimeout(r, 800));

    // ─── Step 1: Cek user di database ────────────────────────
    console.log('STEP 1: Cek user di database...');
    const user = await db.getUserByUsername('admin');

    if (!user) {
        console.log('  ❌ User "admin" TIDAK DITEMUKAN di database!');
        console.log('  → Jalankan: node seeder.js\n');
        process.exit(1);
    }

    console.log('  ✅ User ditemukan:');
    console.log('     id           :', user.id);
    console.log('     username     :', user.username);
    console.log('     password_hash:', user.password_hash);
    console.log('     created_at   :', user.created_at);

    // ─── Step 2: Test bcryptjs.compare ───────────────────────
    console.log('\nSTEP 2: Test bcryptjs.compare...');
    const testPasswords = ['password', 'admin', 'admin123'];

    for (const pw of testPasswords) {
        const match = await bcryptjs.compare(pw, user.password_hash);
        console.log(`  ${match ? '✅' : '❌'} compare("${pw}") = ${match}`);
    }

    // ─── Step 3: Buat hash baru dan bandingkan ────────────────
    console.log('\nSTEP 3: Generate hash baru dari "password"...');
    const freshHash = await bcryptjs.hash('password', 10);
    const freshMatch = await bcryptjs.compare('password', freshHash);
    console.log('  ✅ bcryptjs.hash + compare berfungsi:', freshMatch);

    // ─── Step 4: Cek apakah hash di DB valid ─────────────────
    console.log('\nSTEP 4: Validasi format hash di DB...');
    const isValidBcryptHash = /^\$2[aby]?\$\d+\$/.test(user.password_hash);
    console.log('  Format hash valid (bcrypt format):', isValidBcryptHash ? '✅ Ya' : '❌ Tidak — hash rusak!');

    console.log('\n══════════════════════════════════════');
    console.log('  Selesai. Baca hasil di atas.');
    console.log('══════════════════════════════════════');
    process.exit(0);
}

debug().catch(err => {
    console.error('ERROR:', err.message);
    process.exit(1);
});
