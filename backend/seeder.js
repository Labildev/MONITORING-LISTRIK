'use strict';

/**
 * Seeder — isi data awal ke database.
 * Jalankan: npm run seed -w backend
 */

const bcrypt = require('bcryptjs');
const db     = require('./db');

const SALT_ROUNDS = 10;

const USERS = [
    { username: 'admin', password: 'password' },
];

async function seed() {
    console.log('🌱 Menjalankan seeder...\n');

    // Tunggu sebentar agar koneksi DB & tabel siap
    await new Promise(r => setTimeout(r, 500));

    for (const user of USERS) {
        try {
            const hash = await bcrypt.hash(user.password, SALT_ROUNDS);
            await db.createUser(user.username, hash);
            console.log(`  ✅ User dibuat  → username: "${user.username}", password: "${user.password}"`);
        } catch (err) {
            if (err.message.includes('UNIQUE constraint failed')) {
                console.log(`  ⚠️  User sudah ada → username: "${user.username}" (dilewati)`);
            } else {
                console.error(`  ❌ Gagal membuat user "${user.username}":`, err.message);
            }
        }
    }

    console.log('\n✅ Seeder selesai.');
    process.exit(0);
}

seed().catch(err => {
    console.error('❌ Seeder error:', err);
    process.exit(1);
});
