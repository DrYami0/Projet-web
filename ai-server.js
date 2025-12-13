const express = require('express');
const cors = require('cors');
const http = require('http');
const { Server } = require('socket.io');
const { GoogleGenerativeAI } = require('@google/generative-ai');
require('dotenv').config();

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Initialize Gemini AI
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);

// AI Chat endpoint
app.post('/ai', async (req, res) => {
    try {
        const { message, model: requestedModel } = req.body;

        if (!message) {
            return res.status(400).json({ error: 'Message is required' });
        }

        console.log('Received message:', message);
        console.log('API Key present:', !!process.env.GEMINI_API_KEY);

        // Allowed models (from user list)
        const allowedModels = {
            'gemini-2.5-flash': 'gemini-2.5-flash',
            'gemini-2.5-pro': 'gemini-2.5-pro',
            'gemini-2.0-flash': 'gemini-2.0-flash',
            'gemini-flash-latest': 'gemini-flash-latest',
            'gemini-2.5-flash-lite': 'gemini-2.5-flash-lite',
            'gemini-3-pro-preview': 'gemini-3-pro-preview'
        };
        const modelName = allowedModels[requestedModel] || 'gemini-2.5-flash';

        // Get Gemini model (stable public endpoint)
        const model = genAI.getGenerativeModel({ model: modelName });

        // Create chat context for PerFran mascot
        const context = `Tu es l'assistant virtuel de PerFran, une plateforme d'apprentissage du français. 
Tu es amical, encourageant et tu aides les utilisateurs avec leurs questions sur l'apprentissage du français, 
la grammaire, le vocabulaire et l'utilisation de la plateforme. Réponds toujours en français de manière claire et concise.`;

        const prompt = `${context}\n\nUtilisateur: ${message}\n\nAssistant:`;

        // Generate response
        const result = await model.generateContent(prompt);
        const response = await result.response;
        const text = response.text();

        console.log('Generated response:', text.substring(0, 100) + '...');
        res.json({ reply: text });

    } catch (error) {
        console.error('Full Error:', error);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        // Fallback graceful reply so frontend doesn't break when PerFranMVC/Model/key is invalid
        res.json({ 
            error: 'Une erreur est survenue',
            reply: "Désolé, le service d'IA est indisponible pour le moment. (Clé ou modèle Gemini invalide)"
        });
    }
});

// Suggest password endpoint (strict JSON response expected from AI, server-side fallback)
app.get('/suggest-password', async (req, res) => {
    const strength = (req.query.strength || 'strong').toLowerCase();
    const length = strength === 'strong' ? 20 : 12;

    // secure local generator fallback
    const localGenerate = (len) => {
        const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lower = 'abcdefghijklmnopqrstuvwxyz';
        const digits = 'REDACTED_INITIAL_ADMIN_PASSWORD789';
        const symbols = '!@#$%^&*()-_=+[]{};:,.<>?';
        const all = upper + lower + digits + symbols;
        const crypto = require('crypto');
        const buf = crypto.randomBytes(len);
        let pwd = '';
        // ensure at least one of each class when len>=4
        if (len >= 4) {
            pwd += upper[buf[0] % upper.length];
            pwd += lower[buf[1] % lower.length];
            pwd += digits[buf[2] % digits.length];
            pwd += symbols[buf[3] % symbols.length];
        }
        for (let i = pwd.length; i < len; i++) pwd += all[buf[i % buf.length] % all.length];
        return pwd.split('').sort(() => 0.5 - Math.random()).join('');
    };

    const isStrong = (p, len) => {
        if (!p || typeof p !== 'string') return false;
        if (p.length < len) return false;
        if (!/[A-Z]/.test(p)) return false;
        if (!/[a-z]/.test(p)) return false;
        if (!/[0-9]/.test(p)) return false;
        if (!/[!@#$%^&*()\-_=+\[\]{};:,.<>?]/.test(p)) return false;
        const weak = [/^password$/i, /^12345+$/, /^qwerty/i, /^letmein$/i];
        for (const r of weak) if (r.test(p)) return false;
        return true;
    };

    try {
        // Strong directive: return ONLY a JSON object like {"password":"..."}
        const prompt = `Génère uniquement un objet JSON valide avec la clé "password" contenant un seul mot de passe sécurisé de ${length} caractères. Le mot de passe DOIT contenir au moins une majuscule, une minuscule, un chiffre et un symbole parmi !@#$%^&*()-_=+[]{};:,.<>?. Ne fournis aucun texte additionnel, aucune explication, et respecte STRICTEMENT le format JSON. Exemple d'output attendu : {"password":"Aa1!xxxxxxxxxxxxxxx"}`;

        const model = genAI.getGenerativeModel({ model: 'gemini-2.5-flash' });
        const result = await model.generateContent(prompt);
        const response = await result.response;
        const text = await response.text();

        // Try to extract JSON object from AI output
        let extracted = null;
        try {
            // find first { ... } block
            const m = text.match(/\{[\s\S]*\}/);
            if (m) {
                extracted = JSON.parse(m[0]);
            }
        } catch (e) {
            extracted = null;
        }

        if (extracted && extracted.password && isStrong(extracted.password, length)) {
            return res.json({ password: String(extracted.password), source: 'ai' });
        }
    } catch (err) {
        console.error('suggest-password AI error:', err && err.message ? err.message : err);
    }

    // fallback to secure local generation
    const fallback = localGenerate(length);
    return res.json({ password: fallback, source: 'local' });
});

// Health check
app.get('/health', (req, res) => {
    res.json({ status: 'OK', service: 'PerfRan AI Assistant' });
});

// Socket.IO for multiplayer game rooms
const gameRooms = new Map();

io.on('connection', (socket) => {
    console.log('User connected:', socket.id);

    socket.on('create-room', ({ username, difficulty }) => {
        const roomId = 'ROOM-' + Math.random().toString(36).slice(2, 7).toUpperCase();
        
        gameRooms.set(roomId, {
            roomId,
            difficulty,
            players: [{
                id: socket.id,
                username,
                playerNumber: 1,
                ready: false
            }],
            gameStarted: false
        });

        socket.join(roomId);
        socket.emit('room-created', { roomId, playerNumber: 1 });
        console.log(`Room ${roomId} created by ${username}`);
    });

    socket.on('join-room', ({ roomId, username }) => {
        const room = gameRooms.get(roomId);
        
        if (!room) {
            socket.emit('room-error', { message: 'Room not found' });
            return;
        }

        if (room.players.length >= 2) {
            socket.emit('room-error', { message: 'Room is full' });
            return;
        }

        if (room.gameStarted) {
            socket.emit('room-error', { message: 'Game already started' });
            return;
        }

        room.players.push({
            id: socket.id,
            username,
            playerNumber: 2,
            ready: false
        });

        socket.join(roomId);
        
        // Notify both players
        io.to(roomId).emit('player-joined', {
            players: room.players.map(p => ({ username: p.username, playerNumber: p.playerNumber })),
            difficulty: room.difficulty
        });

        console.log(`${username} joined room ${roomId}`);
    });

    socket.on('player-ready', ({ roomId }) => {
        const room = gameRooms.get(roomId);
        if (!room) return;

        const player = room.players.find(p => p.id === socket.id);
        if (player) {
            player.ready = true;
        }

        // Check if both players are ready
        if (room.players.length === 2 && room.players.every(p => p.ready)) {
            room.gameStarted = true;
            io.to(roomId).emit('game-start', {
                players: room.players.map(p => ({ username: p.username, playerNumber: p.playerNumber }))
            });
            console.log(`Game starting in room ${roomId}`);
        }
    });

    socket.on('leave-room', ({ roomId }) => {
        const room = gameRooms.get(roomId);
        if (room) {
            socket.leave(roomId);
            room.players = room.players.filter(p => p.id !== socket.id);
            
            if (room.players.length === 0) {
                gameRooms.delete(roomId);
                console.log(`Room ${roomId} deleted`);
            } else {
                io.to(roomId).emit('player-left', {
                    players: room.players.map(p => ({ username: p.username, playerNumber: p.playerNumber }))
                });
            }
        }
    });

    socket.on('disconnect', () => {
        console.log('User disconnected:', socket.id);
        
        // Remove player from any room they're in
        for (const [roomId, room] of gameRooms.entries()) {
            const playerIndex = room.players.findIndex(p => p.id === socket.id);
            if (playerIndex !== -1) {
                room.players.splice(playerIndex, 1);
                
                if (room.players.length === 0) {
                    gameRooms.delete(roomId);
                    console.log(`Room ${roomId} deleted`);
                } else {
                    io.to(roomId).emit('player-left', {
                        players: room.players.map(p => ({ username: p.username, playerNumber: p.playerNumber }))
                    });
                }
                break;
            }
        }
    });
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`🤖 AI Assistant server running on http://0.0.0.0:${PORT} (or http://localhost:${PORT})`);
});
