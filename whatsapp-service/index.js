const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const pino = require('pino');
const fs = require('fs');
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, Browsers, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = 3000;
let sock = null;
let currentQR = null;
let connectionStatus = 'disconnected';
let reconnectAttempts = 0;
const MAX_RECONNECT_ATTEMPTS = 10;

function clearAuthData() {
    try {
        const dir = 'baileys_auth_info';
        if (fs.existsSync(dir)) {
            fs.readdirSync(dir).forEach(f => fs.rmSync(`${dir}/${f}`, { recursive: true, force: true }));
        }
        console.log('[AUTH] Session data cleared successfully.');
    } catch (e) {
        console.error('[AUTH] Failed to clear session data:', e.message);
    }
}

function getReconnectDelay() {
    // Exponential backoff: 3s, 6s, 12s, 24s, 48s, max 60s
    const delay = Math.min(3000 * Math.pow(2, reconnectAttempts), 60000);
    return delay;
}

async function connectToWhatsApp() {
    if (reconnectAttempts >= MAX_RECONNECT_ATTEMPTS) {
        console.log(`[WA] Max reconnect attempts (${MAX_RECONNECT_ATTEMPTS}) reached. Stopping.`);
        console.log('[WA] Use POST /disconnect or restart the service to try again.');
        connectionStatus = 'failed';
        return;
    }

    try {
        console.log(`[WA] Connecting... (attempt ${reconnectAttempts + 1}/${MAX_RECONNECT_ATTEMPTS})`);
        
        const { state, saveCreds } = await useMultiFileAuthState('baileys_auth_info');
        
        let version;
        try {
            const result = await fetchLatestBaileysVersion();
            version = result.version;
            console.log('[WA] Using WhatsApp Web version:', version.join('.'));
        } catch (e) {
            console.log('[WA] Could not fetch latest version, using default');
            version = undefined;
        }

        const socketOptions = {
            auth: state,
            printQRInTerminal: true,
            logger: pino({ level: 'silent' }),
            browser: Browsers.ubuntu('Chrome'),
            connectTimeoutMs: 60000,
            qrTimeout: 60000,
        };
        
        if (version) {
            socketOptions.version = version;
        }

        sock = makeWASocket(socketOptions);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                currentQR = await qrcode.toDataURL(qr);
                connectionStatus = 'waiting_qr';
                reconnectAttempts = 0; // Reset attempts when QR is generated
                console.log('[WA] QR Code generated successfully! Ready to scan.');
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const errorMessage = lastDisconnect?.error?.message || 'Unknown';

                console.log(`[WA] Connection closed. Code: ${statusCode}, Reason: ${errorMessage}`);

                connectionStatus = 'disconnected';
                currentQR = null;
                sock = null;

                if (statusCode === DisconnectReason.loggedOut) {
                    // User explicitly logged out - clear and restart
                    console.log('[WA] Logged out by user. Clearing session...');
                    clearAuthData();
                    reconnectAttempts = 0;
                    setTimeout(() => connectToWhatsApp(), 3000);
                } else if (statusCode === 428 || statusCode === 405 || statusCode === 440) {
                    // 428 = connectionClosed, 405 = method not allowed, 440 = connectionReplaced
                    // These indicate session/protocol issues - clear auth and retry with backoff
                    console.log('[WA] Session rejected by WhatsApp. Clearing auth data...');
                    clearAuthData();
                    reconnectAttempts++;
                    const delay = getReconnectDelay();
                    console.log(`[WA] Will retry in ${delay / 1000}s (attempt ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);
                    setTimeout(() => connectToWhatsApp(), delay);
                } else {
                    // Other errors (timeout, network, etc.) - just reconnect
                    reconnectAttempts++;
                    const delay = getReconnectDelay();
                    console.log(`[WA] Will retry in ${delay / 1000}s (attempt ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);
                    setTimeout(() => connectToWhatsApp(), delay);
                }
            } else if (connection === 'open') {
                console.log('[WA] Connected successfully!');
                connectionStatus = 'connected';
                currentQR = null;
                reconnectAttempts = 0;
            }
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('messages.upsert', async m => {
            const msg = m.messages[0];
            if (!msg.message) return;

            const text = msg.message.conversation || msg.message.extendedTextMessage?.text;

            if (text === '!id') {
                const chatId = msg.key.remoteJid;
                await sock.sendMessage(chatId, { text: `Group ID ini adalah:\n\n*${chatId}*` }, { quoted: msg });
            }
        });
    } catch (err) {
        console.error('[WA] Error starting WhatsApp:', err.message);
        reconnectAttempts++;
        const delay = getReconnectDelay();
        console.log(`[WA] Will retry in ${delay / 1000}s (attempt ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);
        setTimeout(() => connectToWhatsApp(), delay);
    }
}

// Start connection attempt immediately
connectToWhatsApp();

app.get('/qr', (req, res) => {
    if (connectionStatus === 'connected') {
        return res.json({ status: 'connected', message: 'Sudah terhubung' });
    }
    if (currentQR) {
        return res.json({ status: 'waiting_qr', qr: currentQR });
    }
    if (connectionStatus === 'failed') {
        return res.json({ status: 'failed', message: 'Gagal terhubung setelah beberapa percobaan. Silakan restart service.' });
    }
    return res.json({ status: 'loading', message: 'Sedang membuat QR Code, silakan coba beberapa detik lagi' });
});

app.get('/status', (req, res) => {
    res.json({ status: connectionStatus, reconnectAttempts });
});

app.post('/send', async (req, res) => {
    const { phone, message } = req.body;

    if (connectionStatus !== 'connected' || !sock) {
        return res.status(500).json({ error: 'WhatsApp Gateway belum terhubung. Silakan scan QR code terlebih dahulu di panel Admin.' });
    }
    if (!phone || !message) {
        return res.status(400).json({ error: 'Parameter phone dan message diperlukan' });
    }

    try {
        let formattedPhone = phone;
        if (!formattedPhone.endsWith('@g.us')) {
            formattedPhone = formattedPhone.replace(/[^0-9]/g, '');
            if (formattedPhone.startsWith('0')) {
                formattedPhone = '62' + formattedPhone.substring(1);
            }
            if (!formattedPhone.endsWith('@s.whatsapp.net')) {
                formattedPhone = formattedPhone + '@s.whatsapp.net';
            }
        }

        await sock.sendMessage(formattedPhone, { text: message });
        res.json({ success: true, message: 'Pesan berhasil dikirim' });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ error: 'Gagal mengirim pesan' });
    }
});

app.post('/disconnect', async (req, res) => {
    try {
        if (sock) {
            await sock.logout();
        }
    } catch (e) {
        console.error('[WA] Error during logout:', e.message);
    }

    connectionStatus = 'disconnected';
    currentQR = null;
    sock = null;
    reconnectAttempts = 0;

    clearAuthData();

    // Connect again to generate new QR for future scan
    setTimeout(() => connectToWhatsApp(), 3000);

    res.json({ success: true, message: 'Berhasil memutuskan koneksi' });
});

// Force retry endpoint - useful when max attempts reached
app.post('/retry', (req, res) => {
    console.log('[WA] Manual retry triggered via API');
    connectionStatus = 'disconnected';
    currentQR = null;
    sock = null;
    reconnectAttempts = 0;
    clearAuthData();
    setTimeout(() => connectToWhatsApp(), 1000);
    res.json({ success: true, message: 'Retry initiated' });
});

app.listen(PORT, () => {
    console.log(`[WA] WhatsApp Baileys Service berjalan di port ${PORT}`);
});
