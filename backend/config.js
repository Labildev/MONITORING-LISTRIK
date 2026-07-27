'use strict';

/**
 * Konfigurasi terpusat backend.
 * Semua nilai sensitif dibaca dari environment variable (.env).
 * Jika tidak ada, gunakan nilai default yang aman untuk development.
 */
module.exports = {
    // ─── Server ──────────────────────────────────────────────
    PORT: parseInt(process.env.PORT) || 3001,

    // ─── MQTT ────────────────────────────────────────────────
    MQTT_BROKER:                process.env.MQTT_BROKER         || 'mqtt://broker.hivemq.com:1883',
    MQTT_TOPIC_MONITOR:         process.env.MQTT_TOPIC_MONITOR  || 'labil_listrik_123/monitor',
    MQTT_TOPIC_CONTROL_PREFIX:  process.env.MQTT_TOPIC_CONTROL  || 'labil_listrik_123/control/relay',
    MQTT_KEEPALIVE:             60,
    MQTT_RECONNECT_PERIOD:      5000,

    // ─── Perangkat ───────────────────────────────────────────
    RELAY_COUNT:       parseInt(process.env.RELAY_COUNT)       || 4,
    POWER_LIMIT_WATT:  parseInt(process.env.POWER_LIMIT_WATT)  || 1000,

    // ─── Autentikasi ─────────────────────────────────────────
    // Token sesi — ganti dengan nilai acak yang kuat di production
    ADMIN_TOKEN: process.env.ADMIN_TOKEN || 'labil-token-123',

    // ─── Database ────────────────────────────────────────────
    DB_PATH: process.env.DB_PATH || './database.sqlite',
};
