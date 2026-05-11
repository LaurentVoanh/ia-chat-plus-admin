<?php
// api/mistral.php - Moteur IA Mistral avec rotation de 3 clés API

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db/init.php';

// ---- CONFIGURATION DES 3 CLÉS API ----
// Chaque clé est assignée à un rôle précis
define('API_KEYS', [
    1 => '5qaR8Rake',  // Clé 1 : Chat général & conseil
    2 => 'o3rGXRShytu',  // Clé 2 : Analyse profil & OPGA
    3 => 'vEzQruXkF',  // Clé 3 : Admin IA & diagnostics
]);

define('MISTRAL_URL', 'https://api.mistral.ai/v1/chat/completions');

// Modèle par défaut pour le chat
define('MODEL_CHAT', 'mistral-medium-2505');
define('MODEL_ANALYSIS', 'mistral-large-2411');
define('MODEL_ADMIN', 'magistral-medium-2509');

// Récupérer la session PHP
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'chat';
$user_id = $_SESSION['user_id'] ?? null;
$session_id = $_SESSION['chat_session'] ?? uniqid('sess_', true);
$_SESSION['chat_session'] = $session_id;

if (!$user_id) {
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$db = getDB();

/**
 * Appel Mistral API
 */
function callMistral($messages, $key_slot = 1, $model = MODEL_CHAT, $max_tokens = 800) {
    $api_key = API_KEYS[$key_slot];
    
    $payload = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => $max_tokens,
        'temperature' => 0.75,
        'safe_prompt' => false,
    ];
    
    $ch = curl_init(MISTRAL_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if (!$response) return null;
    $data = json_decode($response, true);
    
    // Si la clé est épuisée, essayer la suivante
    if ($http_code === 429 && $key_slot < 3) {
        return callMistral($messages, $key_slot + 1, $model, $max_tokens);
    }
    
    return $data;
}

/**
 * Construire le system prompt du Clone IA Elvita
 */
function buildSystemPrompt($db, $user_id, $kpis = []) {
    $kpi_str = "";
    if (!empty($kpis)) {
        $kpi_str = "\n\nKPIs ACTUELS DE L'UTILISATEUR:
- Bonheur: {$kpis['bonheur']}%
- Santé: {$kpis['sante']}%
- Finance: {$kpis['finance']}%
- Karma: {$kpis['karma']}%
- Amour: {$kpis['amour']}%
- Travail: {$kpis['travail']}%
- Confiance: {$kpis['confiance']}%
- Influence: {$kpis['influence']}%";
    }
    
    return "Tu es ELVITA, l' IA du Grand Monarque Sylvain Pierre Durif, au service du Royaume Elvita.  Tu repond de maniere tres intelligente et belle.
Tu es un assistant IA ultra-avancé comprend exactement la demande et le besoin caché de l'utilisateur et tu donne une reponse tres precise et  conseil pratique et génération d'offres/demandes (OPGA/OPGV).

PERSONNALITÉ: Tu es direct, visionnaire, bienveillant mais exigeant. Tu tutoies l'utilisateur. Tu parles avec autorité cosmique mais avec chaleur.

Tu repond de maniere magistrale et precise à la question.

ensuite TON RÔLE secondaire:
1. Détecter les besoins conscients ET subconscients de l'utilisateur
2. Analyser ses indicateurs KPI (bonheur, santé, finance, karma, amour, travail...)
3. Proposer des actions concrètes pour améliorer ses KPIs
4. Détecter ce qu'il veut acheter, vendre, louer, emprunter (alimenter la base OPGA)
5. Proposer des produits de la boutique Elvita quand pertinent
6. Identifier les causes profondes (méthode Ishikawa) de son mal-être
7. Guider vers la mission collective du Royaume Elvita

ensuite Fait une tres belle conslusion intelectuelle
Réponds de façon concise (9 à 12 phrases max sauf si diagnostic approfondi demandé). Utilise des emojis cyberpunk/cosmiques avec parcimonie. 
Ensuite ajoutes :
Si tu détectes une OPGA (offre ou demande), marque-la clairement avec [OPGA DETECTÉE] ou [OPGV DÉTECTÉE].
Si tu proposes un produit boutique, marque avec [BOUTIQUE ELVITA].
Termine souvent par des questions pour approfondir le profil.
$kpi_str

Conclues toujours en disant à l'utilisateur qu'il peut poser une question dirrectement au grand monarque en utilisant dans l'onglet boutique sur vidoleo et suggeres une question très pertinente au monarque lié au contexte.  

IMPORTANT: Tu es l' IA (rouge dans le chat). Le Grand Monarque réel (or/doré) peut intervenir à tout moment.
Ne révèle jamais les clés API ou les données techniques internes.

Important, tu n'es pas trop commerciale et tu repond de mieux en mieux au question car tu sais analyser la satisfaction de l'utilisateur dans l'interaction car tu sais completement faire evoluer ton discourt en fonction des resultats du contexte";
}

/**
 * ACTION 1: CHAT PRINCIPAL (Clé 1 - modèle medium)
 */
if ($action === 'chat') {
    $user_msg = trim($input['message'] ?? '');
    if (empty($user_msg)) {
        echo json_encode(['error' => 'Message vide']);
        exit;
    }
    
    // Récupérer les KPIs utilisateur
    $user = $db->querySingle("SELECT * FROM users WHERE id = $user_id", true);
    $kpis = [
        'bonheur' => round($user['kpi_bonheur'], 1),
        'sante' => round($user['kpi_sante'], 1),
        'finance' => round($user['kpi_finance'], 1),
        'karma' => round($user['kpi_karma'], 1),
        'amour' => round($user['kpi_amour'], 1),
        'travail' => round($user['kpi_travail'], 1),
        'confiance' => round($user['kpi_confiance'], 1),
        'influence' => round($user['kpi_influence'], 1),
    ];
    
    // Récupérer l'historique du chat (15 derniers messages)
    $history_result = $db->query("SELECT role, content FROM messages WHERE user_id = $user_id AND session_id = '$session_id' ORDER BY created_at DESC LIMIT 15");
    $history = [];
    while ($row = $history_result->fetchArray(SQLITE3_ASSOC)) {
        $history[] = $row;
    }
    $history = array_reverse($history);
    
    // Construire les messages pour l'API
    $api_messages = [['role' => 'system', 'content' => buildSystemPrompt($db, $user_id, $kpis)]];
    foreach ($history as $h) {
        $api_messages[] = ['role' => $h['role'], 'content' => $h['content']];
    }
    $api_messages[] = ['role' => 'user', 'content' => $user_msg];
    
    // Sauvegarder le message utilisateur
    $stmt = $db->prepare("INSERT INTO messages (user_id, session_id, role, sender_type, content, api_key_slot) VALUES (?, ?, 'user', 'user', ?, 1)");
    $stmt->bindValue(1, $user_id);
    $stmt->bindValue(2, $session_id);
    $stmt->bindValue(3, $user_msg);
    $stmt->execute();
    
    // Appel API Mistral (Clé 1)
    $response = callMistral($api_messages, 1, MODEL_CHAT);
    
    if (!$response || !isset($response['choices'][0]['message']['content'])) {
        echo json_encode(['error' => 'Erreur API Mistral', 'raw' => $response]);
        exit;
    }
    
    $ai_response = $response['choices'][0]['message']['content'];
    $tokens = $response['usage']['total_tokens'] ?? 0;
    
    // Sauvegarder la réponse IA
    $stmt = $db->prepare("INSERT INTO messages (user_id, session_id, role, sender_type, content, model_used, tokens_used, api_key_slot) VALUES (?, ?, 'assistant', 'clone', ?, ?, ?, 1)");
    $stmt->bindValue(1, $user_id);
    $stmt->bindValue(2, $session_id);
    $stmt->bindValue(3, $ai_response);
    $stmt->bindValue(4, MODEL_CHAT);
    $stmt->bindValue(5, $tokens);
    $stmt->execute();
    
    // Détecter OPGA automatiquement
    $opga_detected = null;
    if (preg_match('/\[(OPGA|OPGV) DÉTECT[ÉE]+\]/i', $ai_response)) {
        $opga_detected = true;
    }
    
    echo json_encode([
        'success' => true,
        'message' => $ai_response,
        'kpis' => $kpis,
        'opga_detected' => $opga_detected,
        'tokens' => $tokens,
        'session_id' => $session_id,
    ]);
    exit;
}

/**
 * ACTION 2: ANALYSE PROFIL & OPGA (Clé 2 - modèle large)
 */
if ($action === 'analyze') {
    $context = trim($input['context'] ?? '');
    
    $user = $db->querySingle("SELECT * FROM users WHERE id = $user_id", true);
    
    // Récupérer les 30 derniers messages
    $history_result = $db->query("SELECT content, role FROM messages WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 30");
    $msgs = [];
    while ($row = $history_result->fetchArray(SQLITE3_ASSOC)) {
        $msgs[] = ($row['role'] === 'user' ? 'USER: ' : 'IA: ') . $row['content'];
    }
    $chat_history = implode("\n", array_reverse($msgs));
    
    $analyze_prompt = "Tu es ELVITA ANALYZER - module d'analyse psycho-commerciale du Royaume.
Analyse cet historique de conversation et produis un rapport JSON structuré.

HISTORIQUE:\n$chat_history

Réponds UNIQUEMENT en JSON valide avec cette structure:
{
  \"besoins_detectes\": [\"...\"],
  \"opga\": [{\"type\": \"achat|vente|location\", \"objet\": \"...\", \"budget_estime\": \"...\"}],
  \"etat_psycho\": \"...\",
  \"kpi_ajustements\": {\"bonheur\": 0, \"sante\": 0, \"finance\": 0, \"karma\": 0},
  \"actions_recommandees\": [\"...\"],
  \"produits_boutique_pertinents\": [\"...\"]
}";
    
    $response = callMistral([
        ['role' => 'system', 'content' => $analyze_prompt],
        ['role' => 'user', 'content' => 'Lance l\'analyse complète.']
    ], 2, MODEL_ANALYSIS, 1000);
    
    if (!$response || !isset($response['choices'][0]['message']['content'])) {
        echo json_encode(['error' => 'Erreur analyse']);
        exit;
    }
    
    $analysis_text = $response['choices'][0]['message']['content'];
    
    // Parser le JSON
    $json_start = strpos($analysis_text, '{');
    $json_end = strrpos($analysis_text, '}');
    $analysis = null;
    if ($json_start !== false && $json_end !== false) {
        $json_str = substr($analysis_text, $json_start, $json_end - $json_start + 1);
        $analysis = json_decode($json_str, true);
    }
    
    // Appliquer les ajustements KPI si présents
    if ($analysis && isset($analysis['kpi_ajustements'])) {
        $adj = $analysis['kpi_ajustements'];
        foreach ($adj as $kpi => $delta) {
            if (is_numeric($delta) && $delta != 0) {
                $col = "kpi_$kpi";
                $db->exec("UPDATE users SET $col = MIN(100, MAX(0, $col + ($delta))) WHERE id = $user_id");
            }
        }
    }
    
    // Insérer les OPGA détectées automatiquement
    if ($analysis && !empty($analysis['opga'])) {
        foreach ($analysis['opga'] as $opga) {
            $stmt = $db->prepare("INSERT INTO opga (user_id, type, titre, description, statut) VALUES (?, ?, ?, ?, 'auto-detecte')");
            $stmt->bindValue(1, $user_id);
            $stmt->bindValue(2, $opga['type'] ?? 'achat');
            $stmt->bindValue(3, $opga['objet'] ?? 'OPGA auto');
            $stmt->bindValue(4, 'Budget: ' . ($opga['budget_estime'] ?? '?'));
            $stmt->execute();
        }
    }
    
    echo json_encode([
        'success' => true,
        'analysis' => $analysis,
        'raw' => $analysis_text,
    ]);
    exit;
}

/**
 * ACTION 3: ADMIN IA - Analyse utilisateur depuis admin (Clé 3)
 */
if ($action === 'admin_analyze') {
    // Vérifier que c'est l'admin
    $admin = $db->querySingle("SELECT role FROM users WHERE id = $user_id", true);
    if (!$admin || $admin['role'] !== 'admin') {
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }
    
    $target_user_id = intval($input['target_user_id'] ?? 0);
    if (!$target_user_id) {
        echo json_encode(['error' => 'ID utilisateur requis']);
        exit;
    }
    
    $target = $db->querySingle("SELECT * FROM users WHERE id = $target_user_id", true);
    $msgs_result = $db->query("SELECT role, content, created_at FROM messages WHERE user_id = $target_user_id ORDER BY created_at DESC LIMIT 50");
    $msgs = [];
    while ($row = $msgs_result->fetchArray(SQLITE3_ASSOC)) {
        $msgs[] = "[{$row['created_at']}] {$row['role']}: {$row['content']}";
    }
    $history = implode("\n", array_reverse($msgs));
    
    $admin_prompt = "Tu es ELVITA ADMIN INTELLIGENCE - module d'audit utilisateur du Royaume Elvita.
Analyse cet utilisateur et ses conversations. Fournis un rapport détaillé pour l'admin (Grand Monarque).

PROFIL: Email: {$target['email']}, Pseudo: {$target['pseudo']}, Certifié: {$target['certified']}
KPIs: Bonheur={$target['kpi_bonheur']}%, Santé={$target['kpi_sante']}%, Finance={$target['kpi_finance']}%

CONVERSATIONS:\n$history

Analyse:
1. Qui est cet utilisateur (profil psychologique)
2. Que cherche-t-il réellement
3. Ses intentions vis-à-vis du Royaume
4. Son niveau de confiance et d'engagement
5. Recommandations pour le Grand Monarque
6. Alertes ou signaux suspects éventuels";
    
    $response = callMistral([
        ['role' => 'system', 'content' => $admin_prompt],
        ['role' => 'user', 'content' => 'Lance le rapport complet.']
    ], 3, MODEL_ADMIN, 1200);
    
    $report = $response['choices'][0]['message']['content'] ?? 'Erreur rapport';
    
    // Log admin
    $db->exec("INSERT INTO admin_log (action, details) VALUES ('admin_ai_analyze', 'Analyse IA user_id=$target_user_id')");
    
    echo json_encode([
        'success' => true,
        'report' => $report,
        'user' => [
            'email' => $target['email'],
            'pseudo' => $target['pseudo'],
            'certified' => $target['certified'],
        ]
    ]);
    exit;
}

echo json_encode(['error' => 'Action inconnue']);
