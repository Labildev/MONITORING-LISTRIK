/**
 * Konfigurasi terpusat frontend.
 *
 * URL selalu relatif (''). Di dev, Vite proxy yang forward ke backend.
 * Di production, backend langsung serve semuanya dari port yang sama.
 * Tidak ada lagi perbedaan dev/prod di sini.
 *
 * Untuk override nilai, buat file frontend/.env dan tambahkan variabel
 * dengan prefix VITE_ (contoh ada di .env.example di root project).
 */

// ─── Backend URL ─────────────────────────────────────────────
// Selalu kosong — pakai URL relatif (/api/...) agar bekerja di dev & prod
export const BACKEND_URL = '';

// ─── Perangkat ───────────────────────────────────────────────
export const RELAY_COUNT = parseInt(import.meta.env.VITE_RELAY_COUNT) || 4;

// ─── Tarif Listrik ───────────────────────────────────────────
export const TARIF_PER_KWH = parseInt(import.meta.env.VITE_TARIF_PER_KWH) || 1444;

// ─── Identitas Aplikasi ──────────────────────────────────────
export const APP_NAME  = import.meta.env.VITE_APP_NAME  || 'IoT Panel';
export const APP_TITLE = import.meta.env.VITE_APP_TITLE || 'Monitoring Listrik';

// ─── Kunci LocalStorage ──────────────────────────────────────
export const STORAGE_KEYS = {
    TOKEN: 'labil_token',
    USER:  'labil_user',
    THEME: 'labil_theme',
};

// ─── Helper: buat array ID relay [1, 2, ..., RELAY_COUNT] ────
export const getRelayIds = () =>
    Array.from({ length: RELAY_COUNT }, (_, i) => i + 1);

// ─── Helper: buat state awal relay { relay1: 0, relay2: 0, ... } ─
export const getInitialRelayState = () =>
    Object.fromEntries(getRelayIds().map(id => [`relay${id}`, 0]));

// ─── Helper: buat nama relay default { 1: 'Beban 1', ... } ──
export const getDefaultRelayNames = () =>
    Object.fromEntries(getRelayIds().map(id => [id, `Beban ${id}`]));
