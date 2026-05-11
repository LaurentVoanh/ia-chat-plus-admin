<?php
session_start();
require_once __DIR__ . '/db/init.php';
$db = getDB();
logVisit($db, '/', $_SESSION['user_id'] ?? null);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#010405">
    <title>ELVITA // ROYAUME COSMIQUE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=VT323&family=Rajdhani:wght@500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        /* =====================================================
           ELVITA PORTAL - STYLE 2ADVANCED FUTURISTE
           ===================================================== */
        :root {
            --bg-base: #010405;
            --panel-bg: rgba(4, 13, 20, 0.92);
            --panel-solid: #040d14;
            --neon-cyan: #00f0ff;
            --neon-cyan-dim: rgba(0, 240, 255, 0.15);
            --neon-blue-dark: #004a66;
            --neon-green: #39ff14;
            --neon-red: #ff3b30;
            --neon-gold: #ffcc00;
            --neon-purple: #bf00ff;
            --text-main: #c4f0ff;
            --text-dim: #5a7a88;
            --font-hud: 'Orbitron', sans-serif;
            --font-data: 'VT323', monospace;
            --font-mono: 'Share Tech Mono', monospace;
            --font-ui: 'Rajdhani', sans-serif;
            --scanline: rgba(0, 240, 255, 0.025);
            --border-c: rgba(0, 240, 255, 0.25);
            --header-h: 56px;
            --nav-h: 58px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0; padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            width: 100%; height: 100%;
            background: var(--bg-base);
            color: var(--text-main);
            font-family: var(--font-ui);
            overflow: hidden;
        }

        /* EFFETS DE FOND */
        .bg-grid {
            position: fixed; inset: 0;
            background-image: linear-gradient(var(--neon-blue-dark) 1px, transparent 1px),
                              linear-gradient(90deg, var(--neon-blue-dark) 1px, transparent 1px);
            background-size: 28px 28px;
            opacity: 0.12; z-index: 0; pointer-events: none;
        }
        .bg-scanlines {
            position: fixed; inset: 0;
            background: repeating-linear-gradient(0deg, transparent, transparent 3px, var(--scanline) 3px, var(--scanline) 4px);
            z-index: 1; pointer-events: none;
        }
        .bg-vignette {
            position: fixed; inset: 0;
            background: radial-gradient(ellipse at center, transparent 40%, rgba(0,0,0,0.7) 100%);
            z-index: 2; pointer-events: none;
        }
        .bg-aurora {
            position: fixed; top: -50%; left: -20%; width: 140%; height: 80%;
            background: radial-gradient(ellipse at 30% 50%, rgba(0,74,102,0.08) 0%, transparent 60%),
                        radial-gradient(ellipse at 70% 50%, rgba(191,0,255,0.04) 0%, transparent 60%);
            z-index: 0; pointer-events: none; animation: aurora 20s ease-in-out infinite alternate;
        }
        @keyframes aurora { from { transform: translateX(-5%); } to { transform: translateX(5%); } }

        /* ============ ÉCRAN SAS ============ */
        #sas-screen {
            position: fixed; inset: 0;
            background: #000;
            z-index: 500;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            padding: 20px;
            transition: opacity 0.8s ease;
        }
        .sas-logo {
            font-family: var(--font-hud);
            font-size: 32px; font-weight: 900;
            color: var(--neon-cyan);
            letter-spacing: 8px;
            text-shadow: 0 0 30px var(--neon-cyan), 0 0 60px rgba(0,240,255,0.3);
            margin-bottom: 8px;
            animation: glitch-logo 4s infinite;
        }
        @keyframes glitch-logo {
            0%,95%,100% { clip-path: none; transform: none; }
            96% { clip-path: polygon(0 20%, 100% 20%, 100% 40%, 0 40%); transform: translate(-3px, 0); }
            97% { clip-path: polygon(0 60%, 100% 60%, 100% 80%, 0 80%); transform: translate(3px, 0); }
            98% { clip-path: none; transform: none; }
        }
        .sas-sub {
            font-family: var(--font-data); font-size: 16px;
            color: var(--text-dim); letter-spacing: 4px; margin-bottom: 40px;
        }
        .sas-box {
            background: var(--panel-bg);
            border: 1px solid rgba(255,59,48,0.4);
            box-shadow: 0 0 30px rgba(255,59,48,0.15), inset 0 0 30px rgba(255,59,48,0.02);
            padding: 30px; width: 100%; max-width: 420px;
            clip-path: polygon(0 15px, 15px 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%);
        }
        .sas-title {
            font-family: var(--font-hud); color: var(--neon-red);
            font-size: 14px; letter-spacing: 3px; margin-bottom: 20px; text-align: center;
            animation: pulse-text 2s infinite;
        }
        @keyframes pulse-text { 0%,100%{opacity:1;} 50%{opacity:0.6;} }
        
        /* LOGIN FORM */
        .login-form { display: flex; flex-direction: column; gap: 12px; }
        .login-tabs { display: flex; gap: 0; margin-bottom: 20px; }
        .login-tab {
            flex: 1; padding: 10px; font-family: var(--font-hud); font-size: 11px;
            letter-spacing: 2px; cursor: pointer; border: 1px solid var(--border-c);
            background: transparent; color: var(--text-dim); text-align: center; transition: all 0.3s;
        }
        .login-tab.active { background: var(--neon-cyan-dim); color: var(--neon-cyan); border-color: var(--neon-cyan); }
        .form-input {
            background: rgba(0,0,0,0.6); border: 1px solid var(--border-c);
            color: #fff; padding: 13px 15px;
            font-family: var(--font-mono); font-size: 14px; outline: none; width: 100%;
            transition: border-color 0.3s;
        }
        .form-input:focus { border-color: var(--neon-cyan); box-shadow: 0 0 15px rgba(0,240,255,0.1); }
        .form-input::placeholder { color: var(--text-dim); }
        .btn-cyber {
            background: transparent; border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan); font-family: var(--font-hud); font-size: 12px;
            padding: 14px; cursor: pointer; letter-spacing: 3px; text-transform: uppercase;
            width: 100%; position: relative; overflow: hidden;
            clip-path: polygon(8px 0, 100% 0, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0 100%, 0 8px);
            transition: all 0.3s;
        }
        .btn-cyber::before {
            content: ''; position: absolute; inset: 0;
            background: var(--neon-cyan); transform: translateX(-100%);
            transition: transform 0.3s; z-index: -1;
        }
        .btn-cyber:hover { color: #000; }
        .btn-cyber:hover::before { transform: translateX(0); }
        .btn-bypass {
            border-color: #333; color: #444;
            clip-path: polygon(8px 0, 100% 0, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0 100%, 0 8px);
        }
        .btn-bypass::before { background: #333; }
        .btn-bypass:hover { color: #999; }
        .form-error { color: var(--neon-red); font-family: var(--font-data); font-size: 16px; text-align: center; display: none; }
        .form-toggle { font-size: 13px; color: var(--text-dim); text-align: center; cursor: pointer; }
        .form-toggle span { color: var(--neon-cyan); }

        /* ============ APP CORE ============ */
        #app-core {
            display: none; flex-direction: column;
            position: fixed; inset: 0; z-index: 10;
        }

        /* HEADER */
        .app-header {
            height: var(--header-h);
            background: var(--panel-bg);
            border-bottom: 1px solid var(--border-c);
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 15px;
            box-shadow: 0 2px 20px rgba(0,240,255,0.1);
            flex-shrink: 0; position: relative; z-index: 50;
        }
        .header-logo {
            font-family: var(--font-hud); font-size: 16px; font-weight: 900;
            color: var(--neon-cyan); letter-spacing: 4px;
            text-shadow: 0 0 15px rgba(0,240,255,0.5);
        }
        .header-logo span { color: var(--neon-gold); }
        .header-right { display: flex; align-items: center; gap: 12px; }
        .user-badge {
            font-family: var(--font-data); font-size: 15px;
            color: var(--neon-green); border: 1px solid rgba(57,255,20,0.3);
            padding: 2px 10px;
        }
        .mode-badge {
            font-family: var(--font-data); font-size: 14px;
            color: var(--neon-gold); border: 1px solid rgba(255,204,0,0.3);
            padding: 2px 8px; display: none;
        }
        @media (min-width: 400px) { .mode-badge { display: block; } }
        .btn-logout-small {
            background: transparent; border: 1px solid rgba(255,59,48,0.4);
            color: var(--neon-red); font-size: 11px; padding: 5px 10px;
            cursor: pointer; font-family: var(--font-hud); letter-spacing: 1px; transition: all 0.3s;
        }
        .btn-logout-small:hover { background: var(--neon-red); color: #fff; }

        /* PAGES */
        .app-pages {
            flex: 1; overflow: hidden; position: relative;
        }
        .page {
            position: absolute; inset: 0;
            overflow-y: auto; display: none;
            -webkit-overflow-scrolling: touch;
        }
        .page.active { display: flex; flex-direction: column; }
        
        /* SCROLLBAR */
        .page::-webkit-scrollbar { width: 3px; }
        .page::-webkit-scrollbar-thumb { background: var(--neon-cyan); }

        /* BOTTOM NAV */
        .bottom-nav {
            height: var(--nav-h);
            background: var(--panel-solid);
            border-top: 1px solid var(--border-c);
            display: flex; flex-shrink: 0;
        }
        .nav-btn {
            flex: 1; display: flex; flex-direction: column;
            justify-content: center; align-items: center; gap: 3px;
            cursor: pointer; transition: all 0.3s;
            border-right: 1px solid rgba(255,255,255,0.04);
            position: relative; overflow: hidden;
        }
        .nav-btn::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px;
            background: var(--neon-cyan); transform: scaleX(0); transition: transform 0.3s;
        }
        .nav-btn.active::after { transform: scaleX(1); }
        .nav-btn.active .nav-icon { color: var(--neon-cyan); text-shadow: 0 0 10px var(--neon-cyan); }
        .nav-btn.active .nav-label { color: var(--neon-cyan); }
        .nav-icon { font-size: 20px; transition: all 0.3s; }
        .nav-label { font-family: var(--font-data); font-size: 13px; color: var(--text-dim); transition: all 0.3s; }

        /* ======== PAGE CHAT ======== */
        #page-chat {
            flex-direction: column;
        }
        
        /* KPI DASHBOARD */
        .kpi-strip {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 0; border-bottom: 1px solid var(--border-c);
            background: rgba(0,0,0,0.4); flex-shrink: 0;
        }
        .kpi-item {
            padding: 8px 6px; border-right: 1px solid rgba(255,255,255,0.05);
            text-align: center; cursor: pointer; transition: background 0.3s;
        }
        .kpi-item:hover { background: rgba(0,240,255,0.05); }
        .kpi-name { font-family: var(--font-data); font-size: 12px; color: var(--text-dim); }
        .kpi-val { font-family: var(--font-hud); font-size: 14px; font-weight: 700; margin-top: 1px; }
        .kv-high { color: var(--neon-green); text-shadow: 0 0 8px rgba(57,255,20,0.4); }
        .kv-mid { color: var(--neon-gold); text-shadow: 0 0 8px rgba(255,204,0,0.4); }
        .kv-low { color: var(--neon-red); text-shadow: 0 0 8px rgba(255,59,48,0.4); }
        
        /* OPGA TICKER */
        .opga-ticker {
            background: rgba(0,0,0,0.5);
            border-bottom: 1px solid var(--border-c);
            padding: 5px 12px;
            display: flex; align-items: center; gap: 10px;
            flex-shrink: 0; overflow: hidden;
        }
        .ticker-label {
            font-family: var(--font-hud); font-size: 9px; color: var(--neon-gold);
            white-space: nowrap; letter-spacing: 2px;
        }
        .ticker-dot {
            width: 6px; height: 6px; background: var(--neon-cyan); border-radius: 50%;
            animation: pulse-dot 1s infinite; flex-shrink: 0;
        }
        @keyframes pulse-dot {
            0% { box-shadow: 0 0 0 0 rgba(0,240,255,0.7); }
            70% { box-shadow: 0 0 0 6px rgba(0,240,255,0); }
            100% { box-shadow: 0 0 0 0 rgba(0,240,255,0); }
        }
        .ticker-scroll { flex: 1; overflow: hidden; }
        .ticker-text {
            font-family: var(--font-mono); font-size: 12px; color: var(--text-dim);
            white-space: nowrap;
            animation: scroll-ticker 20s linear infinite;
        }
        @keyframes scroll-ticker { from { transform: translateX(100%); } to { transform: translateX(-100%); } }
        
        /* CHAT MESSAGES */
        .chat-messages {
            flex: 1; padding: 12px; overflow-y: auto;
            display: flex; flex-direction: column; gap: 10px;
            -webkit-overflow-scrolling: touch;
        }
        .chat-messages::-webkit-scrollbar { width: 3px; }
        .chat-messages::-webkit-scrollbar-thumb { background: var(--neon-cyan); }
        
        .msg-wrap { display: flex; flex-direction: column; }
        .msg-wrap.user-msg { align-items: flex-end; }
        .msg-wrap.ai-msg { align-items: flex-start; }
        .msg-wrap.monarque-msg { align-items: flex-start; }
        
        .msg-header-small {
            font-family: var(--font-hud); font-size: 9px; letter-spacing: 1px;
            margin-bottom: 4px; display: flex; align-items: center; gap: 5px;
        }
        .dot-green { display: inline-block; width: 5px; height: 5px; background: var(--neon-green); border-radius: 50%; }
        .dot-red { display: inline-block; width: 5px; height: 5px; background: var(--neon-red); border-radius: 50%; }
        .dot-gold { display: inline-block; width: 5px; height: 5px; background: var(--neon-gold); border-radius: 50%; }
        
        .msg-bubble {
            max-width: 88%; padding: 11px 14px; font-size: 14px; line-height: 1.5;
            animation: bubble-in 0.25s ease-out forwards;
        }
        @keyframes bubble-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        
        .msg-bubble.user {
            background: rgba(57,255,20,0.05); border-right: 2px solid var(--neon-green);
            color: #d8ffd8;
            clip-path: polygon(0 0, 100% 0, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0 100%);
        }
        .msg-bubble.clone {
            background: rgba(255,59,48,0.05); border-left: 2px solid var(--neon-red);
            color: #ffe0dd;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 8px 100%, 0 calc(100% - 8px));
        }
        .msg-bubble.monarque {
            background: rgba(255,204,0,0.06); border-left: 2px solid var(--neon-gold);
            color: #fff8cc;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 8px 100%, 0 calc(100% - 8px));
        }
        
        .msg-typing {
            display: flex; align-items: center; gap: 5px; padding: 12px;
        }
        .typing-dot {
            width: 6px; height: 6px; background: var(--neon-red); border-radius: 50%;
            animation: typing-bounce 1s infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing-bounce { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-5px);} }
        
        /* INPUT BAR */
        .chat-input-bar {
            padding: 10px; background: var(--panel-bg);
            border-top: 1px solid var(--border-c); display: flex; gap: 8px; flex-shrink: 0;
        }
        .chat-input {
            flex: 1; background: rgba(0,0,0,0.6); border: 1px solid var(--border-c);
            color: #fff; padding: 12px 14px; font-family: var(--font-mono); font-size: 14px;
            outline: none; transition: border-color 0.3s;
            clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 8px, 100% 100%, 0 100%);
        }
        .chat-input:focus { border-color: var(--neon-cyan); box-shadow: 0 0 15px rgba(0,240,255,0.1); }
        .chat-input::placeholder { color: var(--text-dim); }
        .btn-send {
            width: 50px; background: rgba(0,240,255,0.1); border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan); font-size: 18px; cursor: pointer; transition: all 0.3s;
            clip-path: polygon(0 0, 100% 0, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0 100%);
        }
        .btn-send:hover { background: var(--neon-cyan); color: #000; }
        .btn-analyze {
            padding: 0 10px; background: rgba(255,204,0,0.08); border: 1px solid rgba(255,204,0,0.4);
            color: var(--neon-gold); font-size: 16px; cursor: pointer; transition: all 0.3s;
            clip-path: polygon(0 0, 100% 0, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0 100%);
            white-space: nowrap; font-family: var(--font-data); font-size: 14px;
        }
        .btn-analyze:hover { background: rgba(255,204,0,0.2); }

        /* ======== PAGE OPGA ======== */
        #page-opga { padding: 15px; gap: 12px; }
        
        .page-title {
            font-family: var(--font-hud); font-size: 13px; color: var(--neon-cyan);
            letter-spacing: 3px; padding: 12px 0; border-bottom: 1px solid var(--border-c);
            margin-bottom: 10px;
        }
        .opga-form {
            background: var(--panel-bg); border: 1px solid var(--border-c);
            padding: 15px; margin-bottom: 15px;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-family: var(--font-data); font-size: 14px; color: var(--text-dim); }
        .form-group select, .form-group input, .form-group textarea {
            background: rgba(0,0,0,0.6); border: 1px solid var(--border-c);
            color: #fff; padding: 10px; font-family: var(--font-mono); font-size: 14px; outline: none;
        }
        .form-group select option { background: #040d14; }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .form-group.full { grid-column: 1 / -1; }
        .btn-submit-opga {
            width: 100%; padding: 12px; background: transparent; border: 1px solid var(--neon-green);
            color: var(--neon-green); font-family: var(--font-hud); font-size: 11px;
            letter-spacing: 3px; cursor: pointer; transition: all 0.3s; margin-top: 5px;
        }
        .btn-submit-opga:hover { background: rgba(57,255,20,0.1); }
        
        .opga-list { display: flex; flex-direction: column; gap: 8px; }
        .opga-card {
            background: var(--panel-bg); border: 1px solid var(--border-c);
            border-left: 3px solid var(--neon-cyan); padding: 12px; position: relative;
            animation: bubble-in 0.3s ease-out;
        }
        .opga-card.vente { border-left-color: var(--neon-green); }
        .opga-card.location { border-left-color: var(--neon-gold); }
        .opga-card.emprunt { border-left-color: var(--neon-purple); }
        .opga-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .opga-type-badge {
            font-family: var(--font-data); font-size: 13px; padding: 2px 8px;
            background: rgba(0,240,255,0.1); color: var(--neon-cyan); border: 1px solid var(--border-c);
        }
        .opga-card.vente .opga-type-badge { color: var(--neon-green); background: rgba(57,255,20,0.1); border-color: rgba(57,255,20,0.3); }
        .opga-card.location .opga-type-badge { color: var(--neon-gold); background: rgba(255,204,0,0.1); border-color: rgba(255,204,0,0.3); }
        .opga-date { font-size: 12px; color: var(--text-dim); font-family: var(--font-data); }
        .opga-titre { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
        .opga-user { font-family: var(--font-data); font-size: 14px; color: var(--text-dim); }

        /* ======== PAGE BOUTIQUE ======== */
        #page-boutique { padding: 15px; }
        .boutique-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px; margin-top: 12px;
        }
        .product-card {
            background: var(--panel-bg); border: 1px solid var(--border-c);
            padding: 15px; cursor: pointer; transition: all 0.3s; position: relative; overflow: hidden;
            animation: bubble-in 0.3s ease-out;
        }
        .product-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple));
        }
        .product-card:hover { border-color: var(--neon-cyan); box-shadow: 0 0 20px rgba(0,240,255,0.1); transform: translateY(-2px); }
        .product-icon { font-size: 30px; margin-bottom: 10px; }
        .product-name { font-size: 14px; font-weight: 600; margin-bottom: 6px; line-height: 1.3; }
        .product-desc { font-size: 12px; color: var(--text-dim); margin-bottom: 10px; line-height: 1.4; }
        .product-prix { font-family: var(--font-hud); font-size: 16px; color: var(--neon-gold); }
        .btn-acheter {
            width: 100%; margin-top: 10px; padding: 8px; background: transparent;
            border: 1px solid var(--neon-gold); color: var(--neon-gold);
            font-family: var(--font-hud); font-size: 10px; letter-spacing: 2px; cursor: pointer;
            transition: all 0.3s; clip-path: polygon(5px 0, 100% 0, 100% calc(100% - 5px), calc(100% - 5px) 100%, 0 100%, 0 5px);
        }
        .btn-acheter:hover { background: var(--neon-gold); color: #000; }
        
        .cat-icons { 'digital':'💾', 'formation':'🎓', 'club':'⚽', 'service':'👑' }

        /* ======== PAGE PROFIL ======== */
        #page-profil { padding: 15px; gap: 15px; }
        .profil-header {
            background: var(--panel-bg); border: 1px solid var(--border-c);
            padding: 20px; text-align: center; position: relative; overflow: hidden;
        }
        .profil-header::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--neon-cyan), var(--neon-gold), var(--neon-purple));
        }
        .profil-avatar {
            width: 70px; height: 70px; border-radius: 50%;
            border: 2px solid var(--neon-cyan);
            background: linear-gradient(135deg, var(--neon-blue-dark), #000);
            margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;
            font-size: 28px; box-shadow: 0 0 20px rgba(0,240,255,0.3);
        }
        .profil-name { font-family: var(--font-hud); font-size: 16px; color: var(--neon-cyan); margin-bottom: 4px; }
        .profil-email { font-family: var(--font-data); font-size: 15px; color: var(--text-dim); }
        .profil-cert {
            display: inline-block; margin-top: 8px; padding: 3px 12px;
            font-family: var(--font-data); font-size: 14px;
        }
        
        .kpi-full-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;
        }
        .kpi-full-card {
            background: var(--panel-bg); border: 1px solid var(--border-c); padding: 14px;
            border-left: 3px solid var(--neon-cyan);
        }
        .kpi-full-name { font-family: var(--font-data); font-size: 14px; color: var(--text-dim); margin-bottom: 5px; }
        .kpi-full-bar-wrap { height: 6px; background: rgba(255,255,255,0.05); margin-bottom: 4px; }
        .kpi-full-bar { height: 100%; background: var(--neon-cyan); transition: width 1s ease; }
        .kpi-full-val { font-family: var(--font-hud); font-size: 15px; }
        
        /* TOAST */
        .toast {
            position: fixed; bottom: 70px; left: 50%; transform: translateX(-50%);
            background: var(--panel-solid); border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan); font-family: var(--font-data); font-size: 16px;
            padding: 10px 20px; z-index: 900; opacity: 0; pointer-events: none;
            transition: opacity 0.3s; white-space: nowrap;
        }
        .toast.show { opacity: 1; }
        
        /* MODAL */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.85); z-index: 800;
            justify-content: center; align-items: flex-end;
        }
        .modal-overlay.open { display: flex; }
        .modal-sheet {
            background: var(--panel-solid); border: 1px solid var(--border-c);
            border-bottom: none; width: 100%; max-height: 80vh;
            overflow-y: auto; padding: 20px;
            animation: sheet-up 0.3s ease-out;
        }
        @keyframes sheet-up { from { transform: translateY(100%); } to { transform: translateY(0); } }
        .modal-title-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .modal-title-text { font-family: var(--font-hud); color: var(--neon-cyan); font-size: 13px; letter-spacing: 2px; }
        .modal-close-btn { background: none; border: none; color: var(--neon-red); font-size: 20px; cursor: pointer; }
        .modal-body { font-size: 14px; line-height: 1.6; color: var(--text-main); white-space: pre-wrap; }
        .modal-loading-txt { color: var(--neon-gold); font-family: var(--font-data); font-size: 18px; text-align: center; animation: pulse-text 1s infinite; }
        
        /* RESPONSIVE */
        @media (min-width: 480px) {
            .kpi-strip { grid-template-columns: repeat(4, 1fr); }
            .boutique-grid { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>

<!-- BACKGROUNDS -->
<div class="bg-grid"></div>
<div class="bg-scanlines"></div>
<div class="bg-vignette"></div>
<div class="bg-aurora"></div>
<div class="toast" id="toast"></div>

<!-- ========================== SAS ÉCRAN ========================== -->
<div id="sas-screen">
    <div class="sas-logo">ELVITA</div>
    <div class="sas-sub">ROYAUME COSMIQUE // PORTAIL v4.20</div>
    
    <div class="sas-box">
        <div class="sas-title">[!] AUTHENTIFICATION REQUISE</div>
        
        <!-- Tabs Connexion / Inscription -->
        <div class="login-tabs">
            <div class="login-tab active" id="tab-login" onclick="switchLoginTab('login')">CONNEXION</div>
            <div class="login-tab" id="tab-register" onclick="switchLoginTab('register')">INSCRIPTION</div>
        </div>
        
        <!-- Formulaire Connexion -->
        <div id="form-login" class="login-form">
            <input type="email" class="form-input" id="login-email" placeholder="email@royaume.elvita" autocomplete="email">
            <input type="password" class="form-input" id="login-pass" placeholder="MOT DE PASSE" autocomplete="current-password">
            <div class="form-error" id="login-error">⚠ Identifiants incorrects</div>
            <button class="btn-cyber" onclick="doLogin()">ACCÉDER AU ROYAUME</button>
            <button class="btn-cyber btn-bypass" onclick="enterBypass()">[ ENTRER EN MODE ANONYME ]</button>
        </div>
        
        <!-- Formulaire Inscription -->
        <div id="form-register" class="login-form" style="display:none;">
            <input type="text" class="form-input" id="reg-pseudo" placeholder="PSEUDO / IDENTITÉ">
            <input type="email" class="form-input" id="reg-email" placeholder="email@exemple.com" autocomplete="email">
            <input type="password" class="form-input" id="reg-pass" placeholder="MOT DE PASSE (6+ caractères)" autocomplete="new-password">
            <div class="form-error" id="reg-error">⚠ Erreur</div>
            <button class="btn-cyber" onclick="doRegister()">REJOINDRE LE ROYAUME</button>
        </div>
    </div>
</div>

<!-- ========================== APP CORE ========================== -->
<div id="app-core">
    
    <!-- HEADER -->
    <div class="app-header">
        <div class="header-logo">ELV<span>ITA</span></div>
        <div class="header-right">
            <div class="mode-badge">QUATTRO:AUTO</div>
            <div class="user-badge" id="header-user">USER</div>
            <button class="btn-logout-small" onclick="doLogout()">EXIT</button>
        </div>
    </div>
    
    <!-- PAGES -->
    <div class="app-pages">
        
        <!-- PAGE CHAT -->
        <div class="page active" id="page-chat">
            <!-- KPI STRIP -->
            <div class="kpi-strip" id="kpi-strip">
                <div class="kpi-item" onclick="showPage('profil')">
                    <div class="kpi-name">BONHEUR</div>
                    <div class="kpi-val kv-mid" id="kpi-bonheur">--.-</div>
                </div>
                <div class="kpi-item" onclick="showPage('profil')">
                    <div class="kpi-name">SANTÉ</div>
                    <div class="kpi-val kv-mid" id="kpi-sante">--.-</div>
                </div>
                <div class="kpi-item" onclick="showPage('profil')">
                    <div class="kpi-name">FINANCE</div>
                    <div class="kpi-val kv-mid" id="kpi-finance">--.-</div>
                </div>
                <div class="kpi-item" onclick="showPage('profil')">
                    <div class="kpi-name">KARMA</div>
                    <div class="kpi-val kv-mid" id="kpi-karma">--.-</div>
                </div>
            </div>
            
            <!-- TICKER OPGA -->
            <div class="opga-ticker">
                <div class="ticker-dot"></div>
                <div class="ticker-label">OPGA</div>
                <div class="ticker-scroll">
                    <div class="ticker-text" id="ticker-text">BASE OPGA/OPGV EN LIGNE // ROYAUME ELVITA // MOTEUR IA ACTIF // IA CHARGÉ //</div>
                </div>
            </div>
            
            <!-- MESSAGES -->
            <div class="chat-messages" id="chat-messages"></div>
            
            <!-- INPUT -->
            <div class="chat-input-bar">
                <input type="text" class="chat-input" id="chat-input" placeholder="Transmission de pensée...">
                <button class="btn-analyze" onclick="doAnalyze()" title="Analyse IA complète">⚡</button>
                <button class="btn-send" onclick="sendMessage()">▶</button>
            </div>
        </div>
        
        <!-- PAGE OPGA -->
        <div class="page" id="page-opga">
            <div class="page-title">⚡ BASE OPGA / OPGV — OFFRES & DEMANDES</div>
            
            <div class="opga-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>TYPE</label>
                        <select id="opga-type">
                            <option value="achat">ACHAT (OPGA)</option>
                            <option value="vente">VENTE (OPGV)</option>
                            <option value="location">LOCATION</option>
                            <option value="emprunt">EMPRUNT</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>CATÉGORIE</label>
                        <input type="text" id="opga-cat" placeholder="immobilier, auto...">
                    </div>
                </div>
                <div class="form-group full" style="margin-bottom:10px;">
                    <label>TITRE DE L'OFFRE / DEMANDE *</label>
                    <input type="text" id="opga-titre" placeholder="Recherche Ford GT boîte auto...">
                </div>
                <div class="form-group full" style="margin-bottom:10px;">
                    <label>DESCRIPTION</label>
                    <textarea id="opga-desc" placeholder="Détails, critères, contraintes..."></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>BUDGET MIN (€)</label>
                        <input type="number" id="opga-min" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>BUDGET MAX (€)</label>
                        <input type="number" id="opga-max" placeholder="0">
                    </div>
                </div>
                <button class="btn-submit-opga" onclick="submitOPGA()">⊕ PUBLIER DANS LA MATRICE</button>
            </div>
            
            <div class="page-title" style="font-size:11px;">FLUX TEMPS RÉEL</div>
            <div class="opga-list" id="opga-list">
                <div style="color:var(--text-dim);font-family:var(--font-data);font-size:16px;text-align:center;padding:20px;">Chargement de la matrice...</div>
            </div>
        </div>
        
        <!-- PAGE BOUTIQUE -->
        <div class="page" id="page-boutique">
            <div style="padding:15px;">
                <div class="page-title">👑 BOUTIQUE DU GRAND MONARQUE</div>
                <div style="font-family:var(--font-data);color:var(--text-dim);font-size:15px;margin-bottom:15px;">Produits & Services Exclusifs du Royaume Elvita<iframe src="https://vidoleo.com/legrandmonarque" width="100%" height="1000" style="border: none;" allowfullscreen></iframe></div>
                <div class="boutique-grid" id="boutique-grid">
                    <div style="color:var(--text-dim);font-family:var(--font-data);font-size:16px;">Chargement...</div>
                </div>
            </div>
        </div>
        
        <!-- PAGE PROFIL -->
        <div class="page" id="page-profil">
            <div class="profil-header">
                <div class="profil-avatar" id="profil-avatar">👤</div>
                <div class="profil-name" id="profil-name">---</div>
                <div class="profil-email" id="profil-email">---</div>
                <div class="profil-cert badge-red" id="profil-cert" style="border:1px solid var(--neon-red);color:var(--neon-red);font-family:var(--font-data);font-size:14px;display:inline-block;margin-top:8px;padding:3px 12px;">✗ NON CERTIFIÉ</div>
            </div>
            
            <div style="padding:15px;">
                <div class="page-title">TABLEAU DE BORD KPI PERSONNEL</div>
                <div class="kpi-full-grid" id="kpi-full-grid">
                    <!-- Rempli par JS -->
                </div>
                
                <div class="page-title" style="margin-top:20px;"> IA PERSONNEL</div>
                <div style="background:var(--panel-bg);border:1px solid var(--border-c);border-left:3px solid var(--neon-red);padding:15px;">
                    <div style="font-family:var(--font-data);font-size:16px;color:var(--neon-red);">🔴  IA ACTIF</div>
                    <div style="font-size:13px;color:var(--text-dim);margin-top:8px;line-height:1.6;">
                        Votre  IA analyse en permanence vos échanges pour affiner votre profil psycho-technique et vous représenter en votre absence.
                    </div>
                </div>
                
                <button class="btn-cyber" style="margin-top:20px;" onclick="doAnalyze()">⚡ LANCER ANALYSE COMPLÈTE IA</button>
            </div>
        </div>
        
    </div><!-- /app-pages -->
    
    <!-- BOTTOM NAV -->
    <div class="bottom-nav">
        <div class="nav-btn active" id="nav-chat" onclick="showPage('chat')">
            <div class="nav-icon">💬</div>
            <div class="nav-label">CHAT IA</div>
        </div>
        <div class="nav-btn" id="nav-opga" onclick="showPage('opga')">
            <div class="nav-icon">🔄</div>
            <div class="nav-label">OPGA</div>
        </div>
        <div class="nav-btn" id="nav-boutique" onclick="showPage('boutique')">
            <div class="nav-icon">👑</div>
            <div class="nav-label">BOUTIQUE</div>
        </div>
        <div class="nav-btn" id="nav-profil" onclick="showPage('profil')">
            <div class="nav-icon">⚡</div>
            <div class="nav-label">PROFIL</div>
        </div>
    </div>
    
</div><!-- /app-core -->

<!-- MODAL ANALYSE -->
<div class="modal-overlay" id="analyze-modal" onclick="if(event.target===this)closeModal()">
    <div class="modal-sheet">
        <div class="modal-title-bar">
            <div class="modal-title-text">⚡ ANALYSE IA ELVITA</div>
            <button class="modal-close-btn" onclick="closeModal()">✕</button>
        </div>
        <div id="analyze-body">
            <div class="modal-loading-txt">MOTEUR IA ACTIVÉ // ANALYSE EN COURS...</div>
        </div>
    </div>
</div>

<script>
/* ====================================================
   ELVITA PORTAL — JAVASCRIPT ENGINE
   ==================================================== */

let currentUser = null;
let isAnon = false;
let kpis = { bonheur:50, sante:50, finance:50, karma:50, amour:50, travail:50, confiance:50, influence:50 };

/* ---- UTILS ---- */
function toast(msg, dur = 2500) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), dur);
}
function escHtml(s) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(s));
    return d.innerHTML;
}
function kpiClass(v) {
    return v >= 70 ? 'kv-high' : v >= 40 ? 'kv-mid' : 'kv-low';
}

/* ---- LOGIN ---- */
function switchLoginTab(tab) {
    document.getElementById('form-login').style.display = tab === 'login' ? 'flex' : 'none';
    document.getElementById('form-register').style.display = tab === 'register' ? 'flex' : 'none';
    document.getElementById('tab-login').classList.toggle('active', tab === 'login');
    document.getElementById('tab-register').classList.toggle('active', tab === 'register');
}

async function doLogin() {
    const email = document.getElementById('login-email').value.trim();
    const pass = document.getElementById('login-pass').value;
    if (!email || !pass) { showFormError('login', 'Email et mot de passe requis'); return; }
    
    const res = await apiPost('api/auth.php', { action: 'login', email, password: pass });
    if (res.success) {
        currentUser = res.user;
        enterApp();
    } else {
        showFormError('login', res.error || 'Erreur');
    }
}

async function doRegister() {
    const pseudo = document.getElementById('reg-pseudo').value.trim();
    const email = document.getElementById('reg-email').value.trim();
    const pass = document.getElementById('reg-pass').value;
    if (!email || !pass) { showFormError('reg', 'Email et mot de passe requis'); return; }
    
    const res = await apiPost('api/auth.php', { action: 'register', pseudo, email, password: pass });
    if (res.success) {
        currentUser = res.user;
        enterApp();
    } else {
        showFormError('reg', res.error || 'Erreur');
    }
}

function showFormError(prefix, msg) {
    const el = document.getElementById(prefix + '-error');
    if (el) { el.textContent = '⚠ ' + msg; el.style.display = 'block'; }
}

function enterBypass() {
    isAnon = true;
    currentUser = { email: 'anonyme@sas.elvita', pseudo: 'ANONYME', role: 'user', certified: false, kpis: kpis };
    enterApp();
}

async function doLogout() {
    await apiPost('api/auth.php', { action: 'logout' });
    currentUser = null;
    isAnon = false;
    document.getElementById('app-core').style.display = 'none';
    const sas = document.getElementById('sas-screen');
    sas.style.opacity = '1';
    sas.style.display = 'flex';
    document.getElementById('chat-messages').innerHTML = '';
}

function enterApp() {
    const sas = document.getElementById('sas-screen');
    sas.style.opacity = '0';
    setTimeout(() => {
        sas.style.display = 'none';
        const app = document.getElementById('app-core');
        app.style.display = 'flex';
        initApp();
    }, 700);
}

function initApp() {
    // Header user badge
    const u = currentUser;
    document.getElementById('header-user').textContent = u.pseudo || u.email.split('@')[0];
    
    // KPIs
    if (u.kpis) {
        kpis = u.kpis;
        updateKPIs();
        renderProfilKPIs();
    }
    
    // Profil
    document.getElementById('profil-name').textContent = u.pseudo || 'UTILISATEUR';
    document.getElementById('profil-email').textContent = u.email;
    const certEl = document.getElementById('profil-cert');
    if (u.certified) {
        certEl.textContent = '✓ CERTIFIÉ ROYAUME';
        certEl.style.color = 'var(--neon-green)';
        certEl.style.borderColor = 'var(--neon-green)';
    }
    
    // Message d'accueil IA
    setTimeout(() => {
        addMsg('clone', '🔴   ELVITA', 
            `Salutations, ${u.pseudo || 'Visiteur'}. Profil psychométrique initialisé. ` +
            `Ton taux de bonheur actuel est de ${kpis.bonheur}%. ` +
            `Comment puis-je t'assister aujourd'hui au service du Royaume ?`
        );
    }, 600);
    
    // Charger données
    loadBoutique();
    loadOPGA();
    updateTicker();
}

/* ---- KPIs ---- */
function updateKPIs() {
    ['bonheur','sante','finance','karma'].forEach(k => {
        const el = document.getElementById('kpi-' + k);
        if (el) {
            el.textContent = parseFloat(kpis[k] || 0).toFixed(1) + '%';
            el.className = 'kpi-val ' + kpiClass(parseFloat(kpis[k] || 0));
        }
    });
}

function renderProfilKPIs() {
    const kpi_names = {
        bonheur:'💛 BONHEUR', sante:'❤ SANTÉ', finance:'💰 FINANCE', karma:'☯ KARMA',
        amour:'💜 AMOUR', travail:'⚡ TRAVAIL', confiance:'🔵 CONFIANCE', influence:'🌟 INFLUENCE'
    };
    const grid = document.getElementById('kpi-full-grid');
    grid.innerHTML = '';
    Object.entries(kpi_names).forEach(([key, name]) => {
        const v = parseFloat(kpis[key] || 0);
        const cls = kpiClass(v);
        grid.innerHTML += `
        <div class="kpi-full-card">
            <div class="kpi-full-name">${name}</div>
            <div class="kpi-full-bar-wrap">
                <div class="kpi-full-bar" style="width:${v}%;background:${v>=70?'var(--neon-green)':v>=40?'var(--neon-gold)':'var(--neon-red)'}"></div>
            </div>
            <div class="kpi-full-val ${cls}">${v.toFixed(1)}%</div>
        </div>`;
    });
}

/* ---- CHAT ---- */
function addMsg(type, sender, text, isTyping = false) {
    const area = document.getElementById('chat-messages');
    const wrap = document.createElement('div');
    const classMap = { user: 'user-msg', clone: 'ai-msg', monarque: 'monarque-msg' };
    const dotMap = { user: 'dot-green', clone: 'dot-red', monarque: 'dot-gold' };
    const colorMap = { user: 'var(--neon-green)', clone: 'var(--neon-red)', monarque: 'var(--neon-gold)' };
    
    wrap.className = 'msg-wrap ' + (classMap[type] || 'ai-msg');
    
    if (isTyping) {
        wrap.id = 'typing-indicator';
        wrap.innerHTML = `
            <div class="msg-header-small" style="color:${colorMap[type]};">
                <span class="${dotMap[type]}"></span> ${sender}
            </div>
            <div class="msg-bubble ${type}">
                <div class="msg-typing">
                    <div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>
                </div>
            </div>`;
    } else {
        wrap.innerHTML = `
            <div class="msg-header-small" style="color:${colorMap[type] || 'var(--neon-cyan)}'};">
                <span class="${dotMap[type] || 'dot-green'}"></span> ${escHtml(sender)}
            </div>
            <div class="msg-bubble ${type}">${escHtml(text).replace(/\n/g,'<br>')}</div>`;
    }
    
    area.appendChild(wrap);
    area.scrollTop = area.scrollHeight;
    return wrap;
}

function removeTyping() {
    const t = document.getElementById('typing-indicator');
    if (t) t.remove();
}

async function sendMessage() {
    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;
    
    if (isAnon) {
        toast('⚠ Connecte-toi pour chatter avec l\'IA');
        return;
    }
    
    addMsg('user', '🟢 ' + (currentUser?.pseudo || 'MOI'), text);
    input.value = '';
    
    // Indicateur de frappe
    addMsg('clone', '🔴   ELVITA', '', true);
    
    const res = await apiPost('api/mistral.php', { action: 'chat', message: text });
    removeTyping();
    
    if (res.success) {
        addMsg('clone', '🔴   ELVITA [PREMIUM]', res.message);
        
        // Mettre à jour KPIs si retournés
        if (res.kpis) {
            kpis = res.kpis;
            updateKPIs();
            renderProfilKPIs();
        }
        
        // Mise à jour ticker si OPGA détectée
        if (res.opga_detected) {
            toast('⚡ OPGA/OPGV AUTO-DÉTECTÉE ET ENREGISTRÉE');
            loadOPGA();
        }
    } else {
        addMsg('clone', '🔴 SYSTÈME', 'Erreur de connexion au moteur IA: ' + (res.error || 'Inconnue'));
    }
}

// Entrée clavier
document.getElementById('chat-input').addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

/* ---- ANALYSE IA ---- */
async function doAnalyze() {
    if (isAnon) { toast('⚠ Connexion requise'); return; }
    
    document.getElementById('analyze-modal').classList.add('open');
    document.getElementById('analyze-body').innerHTML = '<div class="modal-loading-txt">⚡ MOTEUR IA CLEF 2 ACTIVÉ // ANALYSE PROFIL EN COURS...</div>';
    
    const res = await apiPost('api/mistral.php', { action: 'analyze' });
    
    if (res.success && res.analysis) {
        const a = res.analysis;
        let html = '';
        
        if (a.etat_psycho) html += `<div style="margin-bottom:15px;padding:12px;background:rgba(0,240,255,0.05);border-left:3px solid var(--neon-cyan);"><b style="color:var(--neon-cyan);font-family:var(--font-hud);font-size:11px;">ÉTAT PSYCHOLOGIQUE</b><br>${escHtml(a.etat_psycho)}</div>`;
        
        if (a.besoins_detectes?.length) {
            html += `<b style="color:var(--neon-gold);font-family:var(--font-hud);font-size:11px;">BESOINS DÉTECTÉS</b><ul style="margin:8px 0 15px 15px;">`;
            a.besoins_detectes.forEach(b => html += `<li style="margin-bottom:4px;">${escHtml(b)}</li>`);
            html += '</ul>';
        }
        
        if (a.opga?.length) {
            html += `<b style="color:var(--neon-red);font-family:var(--font-hud);font-size:11px;">OPGA/OPGV AUTO-GÉNÉRÉES</b><ul style="margin:8px 0 15px 15px;">`;
            a.opga.forEach(o => html += `<li style="margin-bottom:4px;"><span style="color:var(--neon-cyan);">[${o.type?.toUpperCase()}]</span> ${escHtml(o.objet)} — ${escHtml(o.budget_estime || '?')}</li>`);
            html += '</ul>';
        }
        
        if (a.actions_recommandees?.length) {
            html += `<b style="color:var(--neon-green);font-family:var(--font-hud);font-size:11px;">ACTIONS RECOMMANDÉES</b><ul style="margin:8px 0 15px 15px;">`;
            a.actions_recommandees.forEach(act => html += `<li style="margin-bottom:4px;">${escHtml(act)}</li>`);
            html += '</ul>';
        }
        
        if (a.produits_boutique_pertinents?.length) {
            html += `<b style="color:var(--neon-gold);font-family:var(--font-hud);font-size:11px;">👑 BOUTIQUE RECOMMANDÉE</b><ul style="margin:8px 0 15px 15px;">`;
            a.produits_boutique_pertinents.forEach(p => html += `<li style="margin-bottom:4px;">${escHtml(p)}</li>`);
            html += '</ul>';
        }
        
        document.getElementById('analyze-body').innerHTML = `<div class="modal-body">${html}</div>`;
        
        // Recharger KPIs
        const check = await apiPost('api/auth.php', { action: 'check' });
        if (check.user?.kpis) { kpis = check.user.kpis; updateKPIs(); renderProfilKPIs(); }
        
    } else {
        document.getElementById('analyze-body').innerHTML = `<div style="color:var(--neon-red);font-family:var(--font-data);font-size:16px;">ERREUR: ${escHtml(res.error || res.raw || 'Analyse impossible')}</div>`;
    }
}

function closeModal() {
    document.getElementById('analyze-modal').classList.remove('open');
}

/* ---- BOUTIQUE ---- */
const catIcons = { digital:'💾', formation:'🎓', club:'⚽', service:'👑', default:'⭐' };

async function loadBoutique() {
    const res = await apiPost('api/auth.php', { action: 'boutique' });
    const grid = document.getElementById('boutique-grid');
    if (!res.items?.length) { grid.innerHTML = '<div style="color:var(--text-dim);">Boutique vide</div>'; return; }
    
    grid.innerHTML = res.items.map(item => `
        <div class="product-card">
            <div class="product-icon">${catIcons[item.categorie] || catIcons.default}</div>
            <div class="product-name">${escHtml(item.titre)}</div>
            <div class="product-desc">${escHtml(item.description)}</div>
            <div class="product-prix">${parseFloat(item.prix).toFixed(2)} €</div>
            <button class="btn-acheter" onclick="acheteProduit(${item.id}, '${escHtml(item.titre)}')">ACQUÉRIR</button>
        </div>
    `).join('');
}

function acheteProduit(id, titre) {
    if (isAnon) { toast('⚠ Connexion requise pour acheter'); return; }
    toast(`💳 Redirection paiement — ${titre.substring(0,30)}...`);
    // Ici on brancher un système de paiement (Stripe, PayPal...)
    setTimeout(() => addMsg('clone', '🔴  ELVITA', `Tu souhaites acquérir "${titre}". Je vais t'accompagner dans ce processus. Confirme-tu ton achat ? Le paiement sécurisé sera traité via notre système.`), 500);
    showPage('chat');
}

/* ---- OPGA ---- */
async function loadOPGA() {
    const res = await apiPost('api/auth.php', { action: 'opga_list' });
    const list = document.getElementById('opga-list');
    if (!res.items?.length) {
        list.innerHTML = '<div style="color:var(--text-dim);font-family:var(--font-data);font-size:16px;text-align:center;padding:20px;">MATRICE VIDE — Soyez le premier à publier</div>';
        return;
    }
    
    list.innerHTML = res.items.map(o => `
        <div class="opga-card ${o.type}">
            <div class="opga-card-header">
                <span class="opga-type-badge">${o.type.toUpperCase()}</span>
                <span class="opga-date">${o.created_at.split(' ')[0]}</span>
            </div>
            <div class="opga-titre">${escHtml(o.titre)}</div>
            <div class="opga-user">👤 ${escHtml(o.pseudo || 'Anonyme')} ${o.prix_max > 0 ? '— Budget: ' + o.prix_min + '–' + o.prix_max + ' €' : ''}</div>
        </div>
    `).join('');
}

async function submitOPGA() {
    if (isAnon) { toast('⚠ Connexion requise'); return; }
    const titre = document.getElementById('opga-titre').value.trim();
    if (!titre) { toast('⚠ Titre requis'); return; }
    
    const res = await apiPost('api/auth.php', {
        action: 'opga_create',
        type: document.getElementById('opga-type').value,
        titre,
        description: document.getElementById('opga-desc').value,
        categorie: document.getElementById('opga-cat').value,
        prix_min: document.getElementById('opga-min').value || 0,
        prix_max: document.getElementById('opga-max').value || 0,
    });
    
    if (res.success) {
        toast('✓ OPGA publiée dans la matrice');
        document.getElementById('opga-titre').value = '';
        document.getElementById('opga-desc').value = '';
        loadOPGA();
    } else {
        toast('⚠ Erreur: ' + (res.error || 'Inconnue'));
    }
}

/* ---- TICKER ---- */
function updateTicker() {
    const texts = [
        'BASE OPGA/OPGV EN LIGNE',
        'MOTEUR IA ELVITA ACTIF — CLÉS 1/2/3 CHARGÉES',
        'ROYAUME ELVITA — PORTAIL COSMIQUE',
        'CLONE IA EN MODE SEMI-AUTO',
        'GRAND MONARQUE : SYLVAIN PIERRE DURIF',
        'MISTRAL AI CONNECTÉ — MODÈLE MEDIUM',
        'PROTOCOLE QUATTROTRONIQUE ACTIF',
    ];
    document.getElementById('ticker-text').textContent = texts.join(' // ') + ' //';
}

/* ---- NAVIGATION ---- */
function showPage(name) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    
    const page = document.getElementById('page-' + name);
    const nav = document.getElementById('nav-' + name);
    if (page) page.classList.add('active');
    if (nav) nav.classList.add('active');
}

/* ---- API HELPER ---- */
async function apiPost(url, data) {
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        return await res.json();
    } catch (e) {
        return { error: 'Erreur réseau: ' + e.message };
    }
}

/* ---- CHECK SESSION AU CHARGEMENT ---- */
(async function() {
    try {
        const res = await apiPost('api/auth.php', { action: 'check' });
        if (res.authenticated && res.user) {
            currentUser = res.user;
            enterApp();
        }
    } catch(e) {}
})();

</script>
</body>
</html>
