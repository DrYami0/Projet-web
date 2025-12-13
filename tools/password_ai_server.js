#!/usr/bin/env node
/**
 * Lightweight password suggestion server.
 * Usage:
 *  - set environment variable GEMINI_API_KEY with your Google Generative API key
 *  - run: npm install express cors
 *  - run: node tools/password_ai_server.js
 *
 * Endpoint: GET /suggest-password?strength=strong|medium|weak
 */
const express = require('express');
const cors = require('cors');
const fetch = global.fetch || require('node-fetch');

const app = express();
app.use(cors());

function generateLocal(strength = 'strong') {
    const maps = {
        strong: {len: 16, chars: 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZREDACTED_INITIAL_ADMIN_PASSWORD789!@#$%^&*()_+-=[]{};:,.<>?'} ,
        medium: {len: 12, chars: 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZREDACTED_INITIAL_ADMIN_PASSWORD789'},
        weak: {len: 10, chars: 'abcdefghijklmnopqrstuvwxyzREDACTED_INITIAL_ADMIN_PASSWORD789'}
    };
    const cfg = maps[strength] || maps['strong'];
    let pw = '';
    for (let i=0;i<cfg.len;i++) pw += cfg.chars[Math.floor(Math.random()*cfg.chars.length)];
    return pw;
}

app.get('/suggest-password', async (req, res) => {
    const strength = req.query.strength || 'strong';
    const apiKey = process.env.GEMINI_API_KEY || process.env.GENERATIVE_API_KEY;
    if (!apiKey) {
        return res.json({ password: generateLocal(strength), source: 'local' });
    }

    // Try calling Google Generative API (text-bison) — fallback to local on error
    try {
        const prompt = `Generate a single ${strength} secure password between 12 and 20 characters. Include upper/lowercase letters, digits and symbols when appropriate. Return only the password without explanation.`;
        const url = 'https://generativelanguage.googleapis.com/v1beta2/models/text-bison-001:generate?key=' + encodeURIComponent(apiKey);
        const body = { prompt: { text: prompt }, temperature: 0.2, maxOutputTokens: 60 };
        const r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
        if (!r.ok) throw new Error('AI API error ' + r.status);
        const j = await r.json();
        // Try common response shapes
        let pwd = '';
        if (j.candidates && j.candidates[0] && (j.candidates[0].content || j.candidates[0].output)) {
            pwd = j.candidates[0].content || j.candidates[0].output;
        } else if (j.result && j.result.output_text) {
            pwd = j.result.output_text;
        } else if (typeof j.output === 'string') {
            pwd = j.output;
        } else {
            // try to find first string in response
            const str = JSON.stringify(j);
            const m = str.match(/[A-Za-z0-9!@#\$%\^&\*()_+\-=\[\]{};:,.<>?]{8,}/);
            pwd = m ? m[0] : null;
        }
        if (!pwd) throw new Error('No password in AI response');
        // Clean: take first line and trim
        pwd = String(pwd).split(/\n|\r/)[0].trim();
        // Ensure reasonable length, else fallback
        if (pwd.length < 8) throw new Error('Password too short from AI');
        return res.json({ password: pwd, source: 'ai' });
    } catch (err) {
        console.error('Password AI error:', err && err.message);
        return res.json({ password: generateLocal(strength), source: 'local', error: err.message });
    }
});

const port = process.env.PORT || 3002;
app.listen(port, () => console.log(`Password AI server listening on http://localhost:${port}`));
