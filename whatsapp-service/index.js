const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const pino = require('pino');
const fs = require('fs');
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = 3000;
let sock = null;
let currentQR = null;
let connectionStatus = 'disconnected';

async function connectToWhatsApp() {
    try {
        const { state, saveCreds } = await useMultiFileAuthState('baileys_auth_info');
        
        sock = makeWASocket({
            auth: state,
            printQRInTerminal: true,
            logger: pino({ level: 'silent' })
        });

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;
        
        if (qr) {
            // Generate base64 QR code to send to the UI
            currentQR = await qrcode.toDataURL(qr);
            connectionStatus = 'waiting_qr';
        }

        if (connection === 'close') {
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
            
            console.log('Connection closed. Code:', statusCode, 'Should reconnect:', shouldReconnect);
            
            connectionStatus = 'disconnected';
            currentQR = null;
            sock = null;
            
            if (shouldReconnect) {
                setTimeout(() => connectToWhatsApp(), 2000);
            } else {
                console.log('Logged out. Clearing session data...');
                try {
                    const dir = 'baileys_auth_info';
                    if (fs.existsSync(dir)) {
                        fs.readdirSync(dir).forEach(f => fs.rmSync(`${dir}/${f}`, { recursive: true, force: true }));
                    }
                } catch(e) { console.error('Gagal menghapus auth:', e); }
                // Start fresh for new QR
                setTimeout(() => connectToWhatsApp(), 2000);
            }
        } else if (connection === 'open') {
            console.log('Opened connection');
            connectionStatus = 'connected';
            currentQR = null;
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
        console.error('Error starting WhatsApp:', err);
        setTimeout(() => connectToWhatsApp(), 5000);
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
    if (connectionStatus === 'disconnected') {
        // Jangan panggil connectToWhatsApp() lagi di sini jika sudah dipanggil di awal 
        // atau serahkan pada retry logic
        connectionStatus = 'loading';
    }
    return res.json({ status: 'loading', message: 'Sedang membuat QR Code, silakan coba beberapa detik lagi' });
});

app.get('/status', (req, res) => {
    res.json({ status: connectionStatus });
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
        // Format phone number to WhatsApp format
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
    } catch(e) {
        console.error('Error logout:', e);
    }
    
    connectionStatus = 'disconnected';
    currentQR = null;
    sock = null;
    
    try {
        const dir = 'baileys_auth_info';
        if (fs.existsSync(dir)) {
            fs.readdirSync(dir).forEach(f => fs.rmSync(`${dir}/${f}`, { recursive: true, force: true }));
        }
    } catch(e) { console.error('Gagal menghapus auth:', e); }
    
    // Connect again to generate new QR for future scan
    setTimeout(() => connectToWhatsApp(), 2000);
    
    res.json({ success: true, message: 'Berhasil memutuskan koneksi' });
});

app.listen(PORT, () => {
    console.log(`WhatsApp Baileys Service berjalan di port ${PORT}`);
});
