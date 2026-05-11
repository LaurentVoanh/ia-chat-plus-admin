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
    <meta name="theme-color" content="#0a0e1a">
    <title>EMPIRE PACIFISTE // IA POUR LA PAIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #0a0e1a;
            --bg-panel: rgba(15, 20, 35, 0.95);
            --bg-solid: #0f1423;
            --gold-primary: #c9a961;
            --gold-dim: rgba(201, 169, 97, 0.15);
            --gold-dark: #8b7345;
            --silver: #b8b8b8;
            --platinum: #e5e5e5;
            --crimson: #8b1a1a;
            --azure: #1a4d8b;
            --emerald: #1a8b4d;
            --text-elite: #e8e4d9;
            --text-muted: #8a8478;
            --font-title: 'Cinzel', serif;
            --font-body: 'Cormorant Garamond', serif;
            --font-ui: 'Rajdhani', sans-serif;
            --font-mono: 'Share Tech Mono', monospace;
            --border-gold: rgba(201, 169, 97, 0.3);
            --header-h: 60px;
            --nav-h: 62px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        html, body { width: 100%; height: 100%; background: var(--bg-deep); color: var(--text-elite); font-family: var(--font-ui); overflow: hidden; }
        .bg-gradient { position: fixed; inset: 0; background: radial-gradient(ellipse at 30% 20%, rgba(201,169,97,0.06) 0%, transparent 50%), radial-gradient(ellipse at 70% 80%, rgba(26,77,139,0.08) 0%, transparent 50%); z-index: 0; pointer-events: none; }
        .bg-overlay { position: fixed; inset: 0; background: linear-gradient(180deg, rgba(10,14,26,0.8) 0%, rgba(10,14,26,0.95) 100%); z-index: 1; pointer-events: none; }
        #sas-screen { position: fixed; inset: 0; background: linear-gradient(135deg, #05070a 0%, #0a0e1a 100%); z-index: 500; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px; transition: opacity 0.8s ease; }
        .sas-emblem { font-family: var(--font-title); font-size: 36px; font-weight: 800; color: var(--gold-primary); letter-spacing: 6px; text-shadow: 0 0 40px rgba(201,169,97,0.4); margin-bottom: 10px; text-align: center; }
        .sas-motto { font-family: var(--font-body); font-size: 15px; font-style: italic; color: var(--text-muted); letter-spacing: 2px; margin-bottom: 40px; text-align: center; }
        .sas-panel { background: var(--bg-panel); border: 1px solid var(--border-gold); box-shadow: 0 0 40px rgba(201,169,97,0.1), inset 0 0 40px rgba(201,169,97,0.03); padding: 35px; width: 100%; max-width: 440px; position: relative; }
        .sas-panel::before, .sas-panel::after { content: ''; position: absolute; width: 12px; height: 12px; border: 1px solid var(--gold-primary); }
        .sas-panel::before { top: 8px; left: 8px; border-right: none; border-bottom: none; }
        .sas-panel::after { bottom: 8px; right: 8px; border-left: none; border-top: none; }
        .sas-heading { font-family: var(--font-title); color: var(--gold-primary); font-size: 13px; letter-spacing: 4px; text-align: center; margin-bottom: 25px; }
        .auth-tabs { display: flex; gap: 0; margin-bottom: 22px; border-bottom: 1px solid var(--border-gold); }
        .auth-tab { flex: 1; padding: 12px; font-family: var(--font-title); font-size: 11px; letter-spacing: 2px; cursor: pointer; border: none; background: transparent; color: var(--text-muted); text-align: center; transition: all 0.3s; border-bottom: 2px solid transparent; }
        .auth-tab.active { color: var(--gold-primary); border-bottom-color: var(--gold-primary); background: var(--gold-dim); }
        .input-elegant { background: rgba(0,0,0,0.5); border: 1px solid var(--border-gold); color: var(--text-elite); padding: 14px 16px; font-family: var(--font-body); font-size: 15px; outline: none; width: 100%; transition: all 0.3s; margin-bottom: 14px; }
        .input-elegant:focus { border-color: var(--gold-primary); box-shadow: 0 0 20px rgba(201,169,97,0.15); }
        .input-elegant::placeholder { color: var(--text-muted); }
        .btn-noble { background: transparent; border: 1px solid var(--gold-primary); color: var(--gold-primary); font-family: var(--font-title); font-size: 11px; padding: 15px; cursor: pointer; letter-spacing: 3px; text-transform: uppercase; width: 100%; transition: all 0.4s; position: relative; overflow: hidden; }
        .btn-noble::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(201,169,97,0.2), transparent); transform: translateX(-100%); transition: transform 0.4s; }
        .btn-noble:hover { background: var(--gold-dim); box-shadow: 0 0 25px rgba(201,169,97,0.2); }
        .btn-noble:hover::before { transform: translateX(100%); }
        .btn-anon { border-color: var(--text-muted); color: var(--text-muted); margin-top: 10px; }
        .btn-anon:hover { background: rgba(138,132,120,0.1); box-shadow: none; }
        .auth-error { color: var(--crimson); font-family: var(--font-mono); font-size: 14px; text-align: center; display: none; margin-bottom: 12px; }
        .auth-switch { font-size: 13px; color: var(--text-muted); text-align: center; cursor: pointer; margin-top: 15px; }
        .auth-switch span { color: var(--gold-primary); text-decoration: underline; }
        #app-core { display: none; flex-direction: column; position: fixed; inset: 0; z-index: 10; }
        .app-header { height: var(--header-h); background: var(--bg-panel); border-bottom: 1px solid var(--border-gold); display: flex; justify-content: space-between; align-items: center; padding: 0 18px; box-shadow: 0 2px 25px rgba(201,169,97,0.08); flex-shrink: 0; position: relative; z-index: 50; }
        .header-brand { font-family: var(--font-title); font-size: 15px; font-weight: 700; color: var(--gold-primary); letter-spacing: 3px; }
        .header-brand span { color: var(--silver); font-weight: 400; }
        .header-info { display: flex; align-items: center; gap: 14px; }
        .user-pill { font-family: var(--font-mono); font-size: 13px; color: var(--emerald); border: 1px solid rgba(26,139,77,0.4); padding: 3px 12px; background: rgba(26,139,77,0.08); }
        .btn-exit { background: transparent; border: 1px solid rgba(139,26,26,0.5); color: var(--crimson); font-size: 10px; padding: 5px 12px; cursor: pointer; font-family: var(--font-title); letter-spacing: 1px; transition: all 0.3s; }
        .btn-exit:hover { background: var(--crimson); color: #fff; }
        .app-content { flex: 1; overflow: hidden; position: relative; }
        .page-view { position: absolute; inset: 0; overflow-y: auto; display: none; -webkit-overflow-scrolling: touch; }
        .page-view.active { display: flex; flex-direction: column; }
        .page-view::-webkit-scrollbar { width: 3px; }
        .page-view::-webkit-scrollbar-thumb { background: var(--gold-primary); }
        .bottom-navi { height: var(--nav-h); background: var(--bg-solid); border-top: 1px solid var(--border-gold); display: flex; flex-shrink: 0; }
        .nav-item { flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 4px; cursor: pointer; transition: all 0.3s; border-right: 1px solid rgba(255,255,255,0.03); position: relative; }
        .nav-item::after { content: ''; position: absolute; bottom: 0; left: 20%; right: 20%; height: 2px; background: var(--gold-primary); transform: scaleX(0); transition: transform 0.3s; }
        .nav-item.active::after { transform: scaleX(1); }
        .nav-item.active .nav-symbol { color: var(--gold-primary); text-shadow: 0 0 12px rgba(201,169,97,0.5); }
        .nav-item.active .nav-text { color: var(--gold-primary); }
        .nav-symbol { font-size: 22px; transition: all 0.3s; }
        .nav-text { font-family: var(--font-mono); font-size: 11px; color: var(--text-muted); transition: all 0.3s; letter-spacing: 1px; }
        #view-chat { flex-direction: column; }
        .kpi-band { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border-bottom: 1px solid var(--border-gold); background: rgba(0,0,0,0.3); flex-shrink: 0; }
        .kpi-cell { padding: 9px 5px; border-right: 1px solid rgba(255,255,255,0.04); text-align: center; cursor: pointer; transition: background 0.3s; }
        .kpi-cell:hover { background: rgba(201,169,97,0.06); }
        .kpi-label { font-family: var(--font-mono); font-size: 9px; color: var(--text-muted); letter-spacing: 1px; text-transform: uppercase; }
        .kpi-value { font-family: var(--font-title); font-size: 13px; font-weight: 700; margin-top: 2px; }
        .val-high { color: var(--emerald); text-shadow: 0 0 8px rgba(26,139,77,0.4); }
        .val-med { color: var(--gold-primary); text-shadow: 0 0 8px rgba(201,169,97,0.4); }
        .val-low { color: var(--crimson); text-shadow: 0 0 8px rgba(139,26,26,0.4); }
        .agent-model-bar { padding: 10px 15px; background: rgba(0,0,0,0.4); border-bottom: 1px solid var(--border-gold); display: flex; gap: 10px; flex-wrap: wrap; flex-shrink: 0; }
        .select-group { display: flex; flex-direction: column; gap: 4px; min-width: 180px; flex: 1; }
        .select-label { font-family: var(--font-mono); font-size: 9px; color: var(--gold-primary); letter-spacing: 1px; }
        .select-elegant { background: rgba(0,0,0,0.6); border: 1px solid var(--border-gold); color: var(--text-elite); padding: 9px 12px; font-family: var(--font-body); font-size: 13px; outline: none; cursor: pointer; transition: border-color 0.3s; }
        .select-elegant:focus { border-color: var(--gold-primary); }
        .select-elegant option { background: var(--bg-solid); color: var(--text-elite); }
        .chat-area { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; -webkit-overflow-scrolling: touch; }
        .msg-row { display: flex; flex-direction: column; }
        .msg-row.user { align-items: flex-end; }
        .msg-row.agent { align-items: flex-start; }
        .msg-meta { font-family: var(--font-title); font-size: 9px; letter-spacing: 1px; margin-bottom: 5px; display: flex; align-items: center; gap: 6px; }
        .indicator { display: inline-block; width: 6px; height: 6px; border-radius: 50%; }
        .ind-user { background: var(--emerald); }
        .ind-agent { background: var(--gold-primary); }
        .msg-bubble { max-width: 85%; padding: 13px 16px; font-size: 15px; line-height: 1.6; font-family: var(--font-body); animation: msg-fade 0.3s ease-out; }
        @keyframes msg-fade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .msg-bubble.user-msg { background: rgba(26,139,77,0.08); border-right: 2px solid var(--emerald); color: #d8e8d8; }
        .msg-bubble.agent-msg { background: rgba(201,169,97,0.06); border-left: 2px solid var(--gold-primary); color: #f0e8d8; }
        .typing-indicator { display: flex; align-items: center; gap: 6px; padding: 13px; }
        .type-dot { width: 7px; height: 7px; background: var(--gold-primary); border-radius: 50%; animation: type-bounce 1.2s infinite; }
        .type-dot:nth-child(2) { animation-delay: 0.2s; }
        .type-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes type-bounce { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-6px);} }
        .input-zone { padding: 12px; background: var(--bg-panel); border-top: 1px solid var(--border-gold); display: flex; gap: 10px; flex-shrink: 0; }
        .chat-field { flex: 1; background: rgba(0,0,0,0.5); border: 1px solid var(--border-gold); color: var(--text-elite); padding: 13px 15px; font-family: var(--font-body); font-size: 15px; outline: none; transition: border-color 0.3s; }
        .chat-field:focus { border-color: var(--gold-primary); box-shadow: 0 0 18px rgba(201,169,97,0.12); }
        .chat-field::placeholder { color: var(--text-muted); }
        .btn-transmit { width: 52px; background: rgba(201,169,97,0.12); border: 1px solid var(--gold-primary); color: var(--gold-primary); font-size: 18px; cursor: pointer; transition: all 0.3s; }
        .btn-transmit:hover { background: var(--gold-primary); color: var(--bg-deep); }
        #view-agents { padding: 0; }
        .page-head { font-family: var(--font-title); font-size: 14px; color: var(--gold-primary); letter-spacing: 4px; padding: 16px 20px; border-bottom: 1px solid var(--border-gold); text-align: center; }
        .agents-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; padding: 15px; }
        .agent-card { background: var(--bg-panel); border: 1px solid var(--border-gold); padding: 18px; cursor: pointer; transition: all 0.3s; text-align: center; position: relative; overflow: hidden; }
        .agent-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, var(--gold-primary), transparent); }
        .agent-card:hover { border-color: var(--gold-primary); box-shadow: 0 0 25px rgba(201,169,97,0.15); transform: translateY(-3px); }
        .agent-avatar { font-size: 42px; margin-bottom: 10px; }
        .agent-name { font-family: var(--font-title); font-size: 13px; font-weight: 600; color: var(--gold-primary); margin-bottom: 5px; }
        .agent-domain { font-family: var(--font-mono); font-size: 10px; color: var(--text-muted); letter-spacing: 1px; }
        #view-modeles { padding: 0; }
        .models-section { padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .model-category { font-family: var(--font-title); font-size: 12px; color: var(--gold-primary); letter-spacing: 3px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border-gold); }
        .model-item { background: var(--bg-panel); border: 1px solid var(--border-gold); padding: 14px; margin-bottom: 10px; cursor: pointer; transition: all 0.3s; }
        .model-item:hover { border-color: var(--gold-primary); background: rgba(201,169,97,0.05); }
        .model-code { font-family: var(--font-mono); font-size: 12px; color: var(--gold-primary); margin-bottom: 6px; }
        .model-desc { font-family: var(--font-body); font-size: 14px; color: var(--text-muted); line-height: 1.5; }
        #view-profil { padding: 0; }
        .profil-banner { background: var(--bg-panel); border-bottom: 1px solid var(--border-gold); padding: 25px; text-align: center; position: relative; }
        .profil-banner::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--gold-primary), var(--silver), var(--gold-primary)); }
        .avatar-circle { width: 75px; height: 75px; border-radius: 50%; border: 2px solid var(--gold-primary); background: linear-gradient(135deg, var(--gold-dark), var(--bg-deep)); margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 0 25px rgba(201,169,97,0.3); }
        .profil-identity { font-family: var(--font-title); font-size: 17px; color: var(--gold-primary); margin-bottom: 5px; }
        .profil-contact { font-family: var(--font-mono); font-size: 13px; color: var(--text-muted); }
        .kpi-detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; padding: 18px; }
        .kpi-detail-card { background: var(--bg-panel); border: 1px solid var(--border-gold); border-left: 3px solid var(--gold-primary); padding: 15px; }
        .kpi-detail-name { font-family: var(--font-mono); font-size: 11px; color: var(--text-muted); letter-spacing: 1px; margin-bottom: 8px; }
        .kpi-progress-bg { height: 5px; background: rgba(255,255,255,0.06); margin-bottom: 6px; }
        .kpi-progress-fill { height: 100%; background: var(--gold-primary); transition: width 1s ease; }
        .kpi-detail-val { font-family: var(--font-title); font-size: 14px; font-weight: 700; }
        .toast-msg { position: fixed; bottom: 75px; left: 50%; transform: translateX(-50%); background: var(--bg-solid); border: 1px solid var(--gold-primary); color: var(--gold-primary); font-family: var(--font-mono); font-size: 14px; padding: 11px 24px; z-index: 900; opacity: 0; pointer-events: none; transition: opacity 0.3s; white-space: nowrap; }
        .toast-msg.show { opacity: 1; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.88); z-index: 800; justify-content: center; align-items: flex-end; }
        .modal-backdrop.open { display: flex; }
        .modal-panel { background: var(--bg-solid); border: 1px solid var(--border-gold); border-bottom: none; width: 100%; max-height: 75vh; overflow-y: auto; padding: 22px; animation: modal-rise 0.3s ease-out; }
        @keyframes modal-rise { from { transform: translateY(100%); } to { transform: translateY(0); } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .modal-heading { font-family: var(--font-title); color: var(--gold-primary); font-size: 13px; letter-spacing: 2px; }
        .modal-dismiss { background: none; border: none; color: var(--crimson); font-size: 22px; cursor: pointer; }
        .modal-content { font-size: 15px; line-height: 1.7; color: var(--text-elite); font-family: var(--font-body); white-space: pre-wrap; }
        .loading-txt { color: var(--gold-primary); font-family: var(--font-mono); font-size: 16px; text-align: center; animation: pulse-slow 1.5s infinite; }
        @keyframes pulse-slow { 0%,100%{opacity:1;} 50%{opacity:0.5;} }
        @media (min-width: 500px) { .kpi-band { grid-template-columns: repeat(8, 1fr); } .agents-grid { grid-template-columns: repeat(4, 1fr); } .kpi-detail-grid { grid-template-columns: repeat(4, 1fr); } }
    </style>
</head>
<body>
<div class="bg-gradient"></div>
<div class="bg-overlay"></div>
<div class="toast-msg" id="toast"></div>
<div id="sas-screen">
    <div class="sas-emblem">✦ EMPIRE PACIFISTE ✦</div>
    <div class="sas-motto">« L'IA au service de la paix mondiale »</div>
    <div class="sas-panel">
        <div class="sas-heading">[!] ACCÈS RÉSERVÉ AUX PACIFISTES</div>
        <div class="auth-tabs">
            <button class="auth-tab active" id="tab-login" onclick="switchAuth('login')">CONNEXION</button>
            <button class="auth-tab" id="tab-register" onclick="switchAuth('register')">ADHÉSION</button>
        </div>
        <div id="form-login" style="display:flex;flex-direction:column;">
            <input type="email" class="input-elegant" id="login-email" placeholder="votre.identite@empire.pacifiste" autocomplete="email">
            <input type="password" class="input-elegant" id="login-pass" placeholder="Mot de passe" autocomplete="current-password">
            <div class="auth-error" id="login-error">⚠ Identifiants incorrects</div>
            <button class="btn-noble" onclick="doLogin()">ACCÉDER AU CERCLE</button>
            <button class="btn-noble btn-anon" onclick="enterAnonymous()">[ ENTRER EN OBSERVATEUR ]</button>
        </div>
        <div id="form-register" style="display:none;flex-direction:column;">
            <input type="text" class="input-elegant" id="reg-identity" placeholder="Identité / Pseudonyme">
            <input type="email" class="input-elegant" id="reg-email" placeholder="email@exemple.com" autocomplete="email">
            <input type="password" class="input-elegant" id="reg-pass" placeholder="Mot de passe (6+ caractères)" autocomplete="new-password">
            <div class="auth-error" id="reg-error">⚠ Erreur</div>
            <button class="btn-noble" onclick="doRegister()">REJOINDRE L'EMPIRE</button>
        </div>
    </div>
</div>
<div id="app-core">
    <div class="app-header">
        <div class="header-brand">EMPIRE <span>PACIFISTE</span></div>
        <div class="header-info">
            <div class="user-pill" id="header-user">UTILISATEUR</div>
            <button class="btn-exit" onclick="doLogout()">QUITTER</button>
        </div>
    </div>
    <div class="app-content">
        <div class="page-view active" id="view-chat">
            <div class="kpi-band" id="kpi-band">
                <div class="kpi-cell" onclick="showView('profil')"><div class="kpi-label">SAGESSE</div><div class="kpi-value val-med" id="kpi-sagesse">--.-</div></div>
                <div class="kpi-cell" onclick="showView('profil')"><div class="kpi-label">PAIX INT.</div><div class="kpi-value val-med" id="kpi-paix">--.-</div></div>
                <div class="kpi-cell" onclick="showView('profil')"><div class="kpi-label">INFLUENCE</div><div class="kpi-value val-med" id="kpi-influence">--.-</div></div>
                <div class="kpi-cell" onclick="showView('profil')"><div class="kpi-label">HARMONIE</div><div class="kpi-value val-med" id="kpi-harmonie">--.-</div></div>
                <div class="kpi-cell" onclick="showView('profil')"><div class="kpi-label">LUCIDITÉ</div><div class="kpi-value val-med" id="kpi-lucidite">--.-</div></div>
                <div class="kpi-cell" onclick="showView('profil')"><div class="kpi-label">EMPATHIE</div><div class="kpi-value val-med" id="kpi-empathie">--.-</div></div>
                <div class="kpi-cell" onclick="showView('profil')"><div class="kpi-label">ACTION</div><div class="kpi-value val-med" id="kpi-action">--.-</div></div>
                <div class="kpi-cell" onclick="showView('profil')"><div class="kpi-label">IMPACT</div><div class="kpi-value val-med" id="kpi-impact">--.-</div></div>
            </div>
            <div class="agent-model-bar">
                <div class="select-group">
                    <div class="select-label">AGENT INTELLECTUEL</div>
                    <select class="select-elegant" id="agent-select" onchange="changeAgent()">
                        <option value="marc-aurele">Marc Aurèle — Stoïcisme</option>
                        <option value="platon">Platon — Justice & Idées</option>
                        <option value="confucius">Confucius — Harmonie Sociale</option>
                        <option value="kant">Kant — Paix Perpétuelle</option>
                        <option value="arendt">Hannah Arendt — Politique</option>
                        <option value="gandhi">Gandhi — Non-Violence</option>
                        <option value="mandela">Mandela — Réconciliation</option>
                        <option value="mlk">Martin Luther King — Droits Civiques</option>
                        <option value="rousseau">Rousseau — Contrat Social</option>
                        <option value="spinoza">Spinoza — Béatitude</option>
                        <option value="descartes">Descartes — Raison</option>
                        <option value="aristote">Aristote — Juste Milieu</option>
                        <option value="sun-tzu">Sun Tzu — Stratégie Pacifique</option>
                        <option value="voltaire">Voltaire — Tolérance</option>
                        <option value="simone-weil">Simone Weil — Attention</option>
                        <option value="popper">Popper — Société Ouverte</option>
                        <option value="rawls">Rawls — Justice comme Équité</option>
                        <option value="habermas">Habermas — Agir Communicationnel</option>
                        <option value="sen">Amartya Sen — Capabilités</option>
                        <option value="machiavel">Machiavel — Réalisme Politique</option>
                    </select>
                </div>
                <div class="select-group">
                    <div class="select-label">MODÈLE MISTRAL</div>
                    <select class="select-elegant" id="model-select">
                        <optgroup label="Codestral & Devstral (Code)"></optgroup>
                        <option value="codestral-2508">Codestral-2508 — Code avancé</option>
                        <option value="devstral-2512">Devstral-2512 — Développement</option>
                        <option value="devstral-medium-2507">Devstral-Medium — Équilibré</option>
                        <option value="devstral-small-2507">Devstral-Small — Rapide</option>
                        <optgroup label="Mistral & Magistral (Général)"></optgroup>
                        <option value="mistral-large-2411">Mistral-Large-2411 — Puissant</option>
                        <option value="mistral-large-2512">Mistral-Large-2512 — Très performant</option>
                        <option value="mistral-medium-2505">Mistral-Medium-2505 — Équilibre</option>
                        <option value="mistral-medium-2508">Mistral-Medium-2508 — Optimisé</option>
                        <option value="mistral-small-2506">Mistral-Small-2506 — Léger</option>
                        <option value="mistral-small-2603">Mistral-Small-2603 — Rapide</option>
                        <option value="magistral-medium-2509">Magistral-Medium — Raisonnement</option>
                        <option value="magistral-small-2509">Magistral-Small — Efficace</option>
                        <option value="labs-mistral-small-creative">Creative — Créativité</option>
                        <optgroup label="Ministral (Compact)"></optgroup>
                        <option value="ministral-14b-2512">Ministral-14B — Compact puissant</option>
                        <option value="ministral-3b-2512">Ministral-3B — Ultra-rapide</option>
                        <option value="ministral-8b-2512">Ministral-8B — Équilibré</option>
                        <optgroup label="Pixtral & Voxtral (Multimodal)"></optgroup>
                        <option value="pixtral-12b-2409">Pixtral-12B — Vision</option>
                        <option value="pixtral-large-2411">Pixtral-Large — Vision avancée</option>
                        <option value="voxtral-small-2507">Voxtral-Small — Audio</option>
                        <option value="voxtral-mini-2507">Voxtral-Mini — Audio léger</option>
                    </select>
                </div>
            </div>
            <div class="chat-area" id="chat-area"></div>
            <div class="input-zone">
                <input type="text" class="chat-field" id="chat-input" placeholder="Posez votre question sur la paix, la sagesse, la stratégie...">
                <button class="btn-transmit" onclick="sendMessage()" title="Transmettre">▶</button>
            </div>
        </div>
        <div class="page-view" id="view-agents">
            <div class="page-head">✦ LES 20 AGENTS INTELLECTUELS ✦</div>
            <div class="agents-grid" id="agents-grid"></div>
        </div>
        <div class="page-view" id="view-modeles">
            <div class="page-head">✦ MODÈLES MISTRAL DISPONIBLES ✦</div>
            <div id="models-list"></div>
        </div>
        <div class="page-view" id="view-profil">
            <div class="profil-banner">
                <div class="avatar-circle" id="profil-avatar">👤</div>
                <div class="profil-identity" id="profil-name">---</div>
                <div class="profil-contact" id="profil-email">---</div>
            </div>
            <div class="page-head" style="margin-top:15px;">TABLEAU DES 8 INDICES DE PAIX</div>
            <div class="kpi-detail-grid" id="kpi-detail-grid"></div>
            <div style="padding:18px;">
                <div class="page-head" style="font-size:11px;margin-bottom:12px;">ANALYSE PROFONDE</div>
                <div style="background:var(--bg-panel);border:1px solid var(--border-gold);border-left:3px solid var(--gold-primary);padding:16px;">
                    <div style="font-family:var(--font-mono);font-size:13px;color:var(--gold-primary);">✦ MODULE D'ANALYSE ACTIF</div>
                    <div style="font-family:var(--font-body);font-size:14px;color:var(--text-muted);margin-top:10px;line-height:1.6;">L'IA analyse vos échanges pour affiner vos indices de paix et vous guider vers une influence pacificatrice accrue.</div>
                </div>
                <button class="btn-noble" style="margin-top:18px;" onclick="launchAnalysis()">⚡ LANCER L'ANALYSE COMPLÈTE</button>
            </div>
        </div>
    </div>
    <div class="bottom-navi">
        <div class="nav-item active" id="nav-chat" onclick="showView('chat')"><div class="nav-symbol">💬</div><div class="nav-text">DIALOGUE</div></div>
        <div class="nav-item" id="nav-agents" onclick="showView('agents')"><div class="nav-symbol">🏛</div><div class="nav-text">AGENTS</div></div>
        <div class="nav-item" id="nav-modeles" onclick="showView('modeles')"><div class="nav-symbol">⚙</div><div class="nav-text">MODÈLES</div></div>
        <div class="nav-item" id="nav-profil" onclick="showView('profil')"><div class="nav-symbol">✦</div><div class="nav-text">PROFIL</div></div>
    </div>
</div>
<div class="modal-backdrop" id="analysis-modal" onclick="if(event.target===this)closeModal()">
    <div class="modal-panel">
        <div class="modal-header">
            <div class="modal-heading">⚡ ANALYSE DE PROFIL PACIFISTE</div>
            <button class="modal-dismiss" onclick="closeModal()">✕</button>
        </div>
        <div id="analysis-content"><div class="loading-txt">INITIALISATION DU MODULE D'ANALYSE...</div></div>
    </div>
</div>
<script>
let currentUser = null;
let isAnonymous = false;
let currentAgent = 'marc-aurele';
let kpis = { sagesse: 50, paix_interieure: 50, influence: 50, harmonie: 50, lucidite: 50, empathie: 50, action: 50, impact: 50 };
function showToast(msg, dur = 2800) { const t = document.getElementById('toast'); t.textContent = msg; t.classList.add('show'); setTimeout(() => t.classList.remove('show'), dur); }
function escapeHtml(s) { const d = document.createElement('div'); d.appendChild(document.createTextNode(s)); return d.innerHTML; }
function kpiClass(v) { return v >= 70 ? 'val-high' : v >= 40 ? 'val-med' : 'val-low'; }
function switchAuth(tab) { document.getElementById('form-login').style.display = tab === 'login' ? 'flex' : 'none'; document.getElementById('form-register').style.display = tab === 'register' ? 'flex' : 'none'; document.getElementById('tab-login').classList.toggle('active', tab === 'login'); document.getElementById('tab-register').classList.toggle('active', tab === 'register'); }
async function doLogin() { const email = document.getElementById('login-email').value.trim(); const pass = document.getElementById('login-pass').value; if (!email || !pass) { showAuthError('login', 'Email et mot de passe requis'); return; } const res = await apiCall('api/auth.php', { action: 'login', email, password: pass }); if (res.success) { currentUser = res.user; enterEmpire(); } else { showAuthError('login', res.error || 'Erreur'); } }
async function doRegister() { const identity = document.getElementById('reg-identity').value.trim(); const email = document.getElementById('reg-email').value.trim(); const pass = document.getElementById('reg-pass').value; if (!email || !pass) { showAuthError('reg', 'Email et mot de passe requis'); return; } const res = await apiCall('api/auth.php', { action: 'register', pseudo: identity, email, password: pass }); if (res.success) { currentUser = res.user; enterEmpire(); } else { showAuthError('reg', res.error || 'Erreur'); } }
function showAuthError(prefix, msg) { const el = document.getElementById(prefix + '-error'); if (el) { el.textContent = '⚠ ' + msg; el.style.display = 'block'; } }
function enterAnonymous() { isAnonymous = true; currentUser = { email: 'observateur@empire.pacifiste', pseudo: 'OBSERVATEUR', role: 'user', certified: false, kpis }; enterEmpire(); }
async function doLogout() { await apiCall('api/auth.php', { action: 'logout' }); currentUser = null; isAnonymous = false; document.getElementById('app-core').style.display = 'none'; const sas = document.getElementById('sas-screen'); sas.style.opacity = '1'; sas.style.display = 'flex'; document.getElementById('chat-area').innerHTML = ''; }
function enterEmpire() { const sas = document.getElementById('sas-screen'); sas.style.opacity = '0'; setTimeout(() => { sas.style.display = 'none'; const app = document.getElementById('app-core'); app.style.display = 'flex'; initEmpire(); }, 700); }
function initEmpire() { const u = currentUser; document.getElementById('header-user').textContent = u.pseudo || u.email.split('@')[0]; if (u.kpis) { kpis = u.kpis; refreshKPIs(); renderProfilKPIs(); } document.getElementById('profil-name').textContent = u.pseudo || 'PACIFISTE'; document.getElementById('profil-email').textContent = u.email; renderAgentsGrid(); renderModelsList(); setTimeout(() => { addMessage('agent', '✦ ' + getAgentName(currentAgent), `Salutations, ${u.pseudo || 'Pacifiste'}. Je suis ${getAgentName(currentAgent)}, à votre service pour explorer les voies de la paix. Vos indices actuels : Sagesse ${kpis.sagesse}%, Paix intérieure ${kpis.paix_interieure}%. Quelle question philosophique ou stratégique souhaitez-vous aborder ?`); }, 700); }
function refreshKPIs() { const keys = ['sagesse','paix_interieure','influence','harmonie','lucidite','empathie','action','impact']; const ids = ['kpi-sagesse','kpi-paix','kpi-influence','kpi-harmonie','kpi-lucidite','kpi-empathie','kpi-action','kpi-impact']; keys.forEach((k, i) => { const el = document.getElementById(ids[i]); if (el) { el.textContent = parseFloat(kpis[k] || 0).toFixed(1) + '%'; el.className = 'kpi-value ' + kpiClass(parseFloat(kpis[k] || 0)); } }); }
function renderProfilKPIs() { const kpiLabels = { sagesse:'SAGESSE', paix_interieure:'PAIX INTÉRIEURE', influence:'INFLUENCE', harmonie:'HARMONIE', lucidite:'LUCIDITÉ', empathie:'EMPATHIE', action:'ACTION', impact:'IMPACT' }; const grid = document.getElementById('kpi-detail-grid'); grid.innerHTML = ''; Object.entries(kpiLabels).forEach(([key, label]) => { const v = parseFloat(kpis[key] || 0); const cls = kpiClass(v); const color = v>=70?'var(--emerald)':v>=40?'var(--gold-primary)':'var(--crimson)'; grid.innerHTML += `<div class="kpi-detail-card"><div class="kpi-detail-name">${label}</div><div class="kpi-progress-bg"><div class="kpi-progress-fill" style="width:${v}%;background:${color}"></div></div><div class="kpi-detail-val ${cls}">${v.toFixed(1)}%</div></div>`; }); }
const agentsData = [{id:'marc-aurele',name:'Marc Aurèle',domain:'Stoïcisme',emoji:'🏛'},{id:'platon',name:'Platon',domain:'Justice & Idées',emoji:'📜'},{id:'confucius',name:'Confucius',domain:'Harmonie Sociale',emoji:'☯'},{id:'kant',name:'Emmanuel Kant',domain:'Paix Perpétuelle',emoji:'⚖'},{id:'arendt',name:'Hannah Arendt',domain:'Politique',emoji:'🗳'},{id:'gandhi',name:'Mahatma Gandhi',domain:'Non-Violence',emoji:'🕊'},{id:'mandela',name:'Nelson Mandela',domain:'Réconciliation',emoji:'🤝'},{id:'mlk',name:'Martin Luther King',domain:'Droits Civiques',emoji:'✊'},{id:'rousseau',name:'Jean-Jacques Rousseau',domain:'Contrat Social',emoji:'📋'},{id:'spinoza',name:'Baruch Spinoza',domain:'Béatitude',emoji:'✨'},{id:'descartes',name:'René Descartes',domain:'Raison',emoji:'🧠'},{id:'aristote',name:'Aristote',domain:'Juste Milieu',emoji:'⚖'},{id:'sun-tzu',name:'Sun Tzu',domain:'Stratégie',emoji:'🎯'},{id:'voltaire',name:'Voltaire',domain:'Tolérance',emoji:'📝'},{id:'simone-weil',name:'Simone Weil',domain:'Attention',emoji:'🙏'},{id:'popper',name:'Karl Popper',domain:'Société Ouverte',emoji:'🔓'},{id:'rawls',name:'John Rawls',domain:'Justice',emoji:'⚖'},{id:'habermas',name:'Jürgen Habermas',domain:'Communication',emoji:'💬'},{id:'sen',name:'Amartya Sen',domain:'Capabilités',emoji:'📊'},{id:'machiavel',name:'Nicolas Machiavel',domain:'Réalisme',emoji:'👁'}];
function getAgentName(id) { const a = agentsData.find(x=>x.id===id); return a ? a.name : 'Inconnu'; }
function renderAgentsGrid() { const grid = document.getElementById('agents-grid'); grid.innerHTML = agentsData.map(a => `<div class="agent-card" onclick="selectAgent('${a.id}')"><div class="agent-avatar">${a.emoji}</div><div class="agent-name">${a.name}</div><div class="agent-domain">${a.domain}</div></div>`).join(''); }
function selectAgent(id) { currentAgent = id; document.getElementById('agent-select').value = id; showView('chat'); showToast(`✦ ${getAgentName(id)} sélectionné`); }
function changeAgent() { currentAgent = document.getElementById('agent-select').value; showToast(`✦ Agent changé : ${getAgentName(currentAgent)}`); }
const modelsData = { 'Codestral & Devstral (Code)': [{code:'codestral-2508',desc:'Modèle spécialisé en génération et compréhension de code.'},{code:'devstral-2512',desc:'Dédié aux tâches de développement et débogage.'},{code:'devstral-medium-2507',desc:'Version équilibrée pour le développement.'},{code:'devstral-small-2507',desc:'Version légère et rapide pour coding simple.'}], 'Mistral & Magistral (Général)': [{code:'mistral-large-2411',desc:'Haute performance pour raisonnements complexes.'},{code:'mistral-large-2512',desc:'Version optimisée, excellente pour philosophie et stratégie.'},{code:'mistral-medium-2505',desc:'Équilibre parfait entre capacités et rapidité.'},{code:'mistral-medium-2508',desc:'Version améliorée pour discussions pacifistes.'},{code:'mistral-small-2506',desc:'Rapide et efficace pour échanges quotidiens.'},{code:'mistral-small-2603',desc:'Version récente optimisée pour réactivité.'},{code:'magistral-medium-2509',desc:'Spécialisé en raisonnement logique et argumentation.'},{code:'magistral-small-2509',desc:'Version légère pour raisonnements rapides.'},{code:'labs-mistral-small-creative',desc:'Optimisé pour créativité et idées novatrices.'}], 'Ministral (Compact)': [{code:'ministral-14b-2512',desc:'14B de paramètres : puissance compacte.'},{code:'ministral-3b-2512',desc:'Ultra-rapide avec 3B de paramètres.'},{code:'ministral-8b-2512',desc:'8B de paramètres : excellent équilibre.'}], 'Pixtral & Voxtral (Multimodal)': [{code:'pixtral-12b-2409',desc:'Modèle de vision pour images et diagrammes.'},{code:'pixtral-large-2411',desc:'Vision avancée pour analyses visuelles complexes.'},{code:'voxtral-small-2507',desc:'Traitement audio pour transcriptions.'},{code:'voxtral-mini-2507',desc:'Version légère audio pour traitements rapides.'}] };
function renderModelsList() { const container = document.getElementById('models-list'); let html = ''; for (const [cat, models] of Object.entries(modelsData)) { html += `<div class="models-section"><div class="model-category">${cat}</div>`; models.forEach(m => { html += `<div class="model-item" onclick="selectModel('${m.code}')"><div class="model-code">${m.code}</div><div class="model-desc">${m.desc}</div></div>`; }); html += '</div>'; } container.innerHTML = html; }
function selectModel(code) { document.getElementById('model-select').value = code; showToast(`⚙ Modèle : ${code}`); showView('chat'); }
function addMessage(type, sender, text, isTyping = false) { const area = document.getElementById('chat-area'); const row = document.createElement('div'); row.className = 'msg-row ' + (type === 'user' ? 'user' : 'agent'); if (isTyping) { row.id = 'typing-indicator'; row.innerHTML = `<div class="msg-meta" style="color:var(--gold-primary);"><span class="indicator ind-agent"></span> ${escapeHtml(sender)}</div><div class="msg-bubble agent-msg"><div class="typing-indicator"><div class="type-dot"></div><div class="type-dot"></div><div class="type-dot"></div></div></div>`; } else { const bubbleClass = type === 'user' ? 'user-msg' : 'agent-msg'; const indicator = type === 'user' ? 'ind-user' : 'ind-agent'; row.innerHTML = `<div class="msg-meta" style="color:${type==='user'?'var(--emerald)':'var(--gold-primary)'};"><span class="indicator ${indicator}"></span> ${escapeHtml(sender)}</div><div class="msg-bubble ${bubbleClass}">${escapeHtml(text).replace(/\n/g,'<br>')}</div>`; } area.appendChild(row); area.scrollTop = area.scrollHeight; return row; }
function removeTyping() { const t = document.getElementById('typing-indicator'); if (t) t.remove(); }
async function sendMessage() { const input = document.getElementById('chat-input'); const text = input.value.trim(); if (!text) return; if (isAnonymous) { showToast('⚠ Connexion requise pour dialoguer'); return; } addMessage('user', '🟢 ' + (currentUser?.pseudo || 'MOI'), text); input.value = ''; addMessage('agent', '✦ ' + getAgentName(currentAgent), '', true); const model = document.getElementById('model-select').value; const res = await apiCall('api/mistral.php', { action: 'chat', message: text, agent: currentAgent, model }); removeTyping(); if (res.success) { addMessage('agent', '✦ ' + getAgentName(currentAgent) + ' [' + res.model + ']', res.message); if (res.kpis) { kpis = res.kpis; refreshKPIs(); renderProfilKPIs(); } } else { addMessage('agent', '⚙ SYSTÈME', 'Erreur: ' + (res.error || 'Inconnue')); } }
document.getElementById('chat-input').addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });
async function launchAnalysis() { if (isAnonymous) { showToast('⚠ Connexion requise'); return; } document.getElementById('analysis-modal').classList.add('open'); document.getElementById('analysis-content').innerHTML = '<div class="loading-txt">⚡ INITIALISATION DE L\'ANALYSE PACIFISTE...</div>'; const res = await apiCall('api/mistral.php', { action: 'analyze' }); if (res.success && res.analysis) { const a = res.analysis; let html = ''; if (a.etat_psycho) html += `<div style="margin-bottom:16px;padding:14px;background:rgba(201,169,97,0.06);border-left:3px solid var(--gold-primary);"><b style="color:var(--gold-primary);font-family:var(--font-title);font-size:11px;">ÉTAT PSYCHOLOGIQUE</b><br>${escapeHtml(a.etat_psycho)}</div>`; if (a.besoins_detectes?.length) { html += `<b style="color:var(--gold-primary);font-family:var(--font-title);font-size:11px;">BESOINS IDENTIFIÉS</b><ul style="margin:8px 0 16px 18px;">`; a.besoins_detectes.forEach(b => html += `<li style="margin-bottom:5px;font-family:var(--font-body);">${escapeHtml(b)}</li>`); html += '</ul>'; } if (a.actions_recommandees?.length) { html += `<b style="color:var(--emerald);font-family:var(--font-title);font-size:11px;">ACTIONS RECOMMANDÉES</b><ul style="margin:8px 0 16px 18px;">`; a.actions_recommandees.forEach(act => html += `<li style="margin-bottom:5px;font-family:var(--font-body);">${escapeHtml(act)}</li>`); html += '</ul>'; } document.getElementById('analysis-content').innerHTML = `<div class="modal-content">${html}</div>`; const check = await apiCall('api/auth.php', { action: 'check' }); if (check.user?.kpis) { kpis = check.user.kpis; refreshKPIs(); renderProfilKPIs(); } } else { document.getElementById('analysis-content').innerHTML = `<div style="color:var(--crimson);font-family:var(--font-mono);font-size:15px;">ERREUR: ${escapeHtml(res.error || res.raw || 'Analyse impossible')}</div>`; } }
function closeModal() { document.getElementById('analysis-modal').classList.remove('open'); }
function showView(name) { document.querySelectorAll('.page-view').forEach(p => p.classList.remove('active')); document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active')); const view = document.getElementById('view-' + name); const nav = document.getElementById('nav-' + name); if (view) view.classList.add('active'); if (nav) nav.classList.add('active'); }
async function apiCall(url, data) { try { const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) }); return await res.json(); } catch (e) { return { error: 'Erreur réseau: ' + e.message }; } }
(async function() { try { const res = await apiCall('api/auth.php', { action: 'check' }); if (res.authenticated && res.user) { currentUser = res.user; enterEmpire(); } } catch(e) {} })();
</script>
</body>
</html>
