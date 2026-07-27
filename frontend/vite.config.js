import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

/**
 * Konfigurasi Vite — proxy semua request /api dan /socket.io ke backend.
 *
 * Dev:  Browser → Vite:5173 → [proxy] → Backend:3001
 * Prod: Browser → Backend:3001 (serve frontend/dist langsung)
 *
 * Frontend selalu pakai URL relatif (/api/...) — tidak ada perbedaan dev/prod.
 */
export default defineConfig({
    plugins: [react()],

    server: {
        port: 5173,
        proxy: {
            // Proxy semua request REST API ke backend
            '/api': {
                target:      'http://localhost:3001',
                changeOrigin: true,
            },
            // Proxy WebSocket Socket.IO ke backend
            '/socket.io': {
                target:      'http://localhost:3001',
                changeOrigin: true,
                ws:           true,
            },
        },
    },
});
