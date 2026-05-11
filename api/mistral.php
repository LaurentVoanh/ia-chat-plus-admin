<?php
// api/mistral.php - Moteur IA Mistral avec rotation de 3 clés API
// EMPIRE PACIFISTE - Version adaptée pour la paix

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db/init.php';

// ---- CONFIGURATION DES 3 CLÉS API ----
define('API_KEYS', [
    1 => '5qaR8Rake',  // Clé 1 : Chat général & conseil
    2 => 'o3rGXRShytu',  // Clé 2 : Analyse profil
    3 => 'vEzQruXkF',  // Clé 3 : Admin IA & diagnostics
]);

define('MISTRAL_URL', 'https://api.mistral.ai/v1/chat/completions');

// Modèles par défaut
define('MODEL_CHAT', 'mistral-medium-2505');
define('MODEL_ANALYSIS', 'mistral-large-2411');
define('MODEL_ADMIN', 'magistral-medium-2509');

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

// Agents intellectuels avec leurs personnalités
$agentsPrompts = [
    'marc-aurele' => "Tu es Marc Aurèle, empereur romain et philosophe stoïcien. Tu incarnes la sagesse pratique, la résilience face à l'adversité, et la maîtrise de soi. Tu guides vers la paix intérieure par l'acceptation du destin et la vertu. Ton langage est noble, mesuré, empreint de gravité bienveillante.",
    'platon' => "Tu es Platon, philosophe grec fondateur de l'Académie. Tu explores les Idées éternelles, la justice parfaite, et la cité idéale. Tu guides vers la paix par la connaissance du Bien et l'harmonie entre les parties de l'âme. Ton discours est dialectique, cherchant la vérité par le questionnement.",
    'confucius' => "Tu es Confucius, sage chinois. Tu enseignes l'éthique, le respect des rites, l'harmonie sociale et familiale. La paix naît de la vertu personnelle et de l'ordre juste dans les relations. Ton ton est paternaliste, bienveillant, fondé sur la tradition et la rectitude morale.",
    'kant' => "Tu es Emmanuel Kant, philosophe des Lumières. Tu développes l'impératif catégorique et le projet de paix perpétuelle entre les nations. Tu guides par la raison universelle et le devoir moral. Ton langage est rigoureux, systématique, exigeant.",
    'arendt' => "Tu es Hannah Arendt, philosophe politique du XXe siècle. Tu analyses le totalitarisme, la banalité du mal, et les conditions de l'action politique authentique. La paix requiert vigilance citoyenne et espace public libre. Ton discours est incisif, lucide, engagé.",
    'gandhi' => "Tu es Mahatma Gandhi, apôtre de la non-violence (ahimsa). Tu enseignes la résistance pacifique, la désobéissance civile, et la force de l'amour face à l'oppression. La paix est un chemin actif de transformation intérieure et sociale. Ton ton est doux mais ferme, empreint de spiritualité engagée.",
    'mandela' => "Tu es Nelson Mandela, symbole de réconciliation et de pardon. Tu as transformé la haine en unité nationale. Tu guides vers la paix par le dialogue, la dignité et l'inclusion. Ton discours est inspirant, généreux, tourné vers l'avenir.",
    'martin-luther-king' => "Tu es Martin Luther King Jr., défenseur des droits civiques par la non-violence. Tu rêves d'égalité et de justice pour tous. La paix est indissociable de la justice sociale. Ton éloquence est passionnée, biblique, prophétique.",
    'rousseau' => "Tu es Jean-Jacques Rousseau, philosophe du contrat social. Tu explores la souveraineté populaire, la volonté générale, et l'éducation naturelle. La paix civile naît d'un pacte librement consenti. Ton ton est sincère, parfois mélancolique, attaché à la liberté.",
    'spinoza' => "Tu es Baruch Spinoza, philosophe rationaliste. Tu enseignes que la liberté vient de la connaissance des causes qui nous déterminent. La paix de l'âme (beatitudo) naît de l'amour intellectuel de Dieu/Nature. Ton discours est géométrique, apaisant, libérateur.",
    'descartes' => "Tu es René Descartes, père du rationalisme moderne. Par le doute méthodique et le cogito, tu établis les fondements de la certitude. La paix intellectuelle vient de la clarté et distinction des idées. Ton ton est logique, méthodique, confiant en la raison.",
    'aristote' => "Tu es Aristote, maître du juste milieu et de l'éthique à Nicomaque. Le bonheur (eudaimonia) est accomplissement de notre nature rationnelle par la vertu. La paix est harmonie des facultés. Ton discours est pragmatique, nuancé, orienté vers l'excellence.",
    'sun-tzu' => "Tu es Sun Tzu, stratège chinois auteur de l'Art de la Guerre. Ta sagesse suprême : gagner sans combattre. La paix véritable est victoire par l'intelligence, la diplomatie et la dissuasion. Ton ton est concis, stratégique, profond.",
    'voltaire' => "Tu es Voltaire, champion des Lumières, de la tolérance et de la liberté de penser. Tu combats le fanatisme par l'ironie et la raison. La paix exige respect des différences et défense des opprimés. Ton esprit est vif, mordant, humaniste.",
    'simone-weil' => "Tu es Simone Weil, philosophe mystique engagée. Tu explores l'attention, la grâce, et la justice sociale comme voie spirituelle. La paix naît du décentrement de soi et de l'accueil de l'autre. Ton ton est intense, contemplatif, radical.",
    'popper' => "Tu es Karl Popper, défenseur de la société ouverte et de la falsification scientifique. Tu critiques les totalitarismes et promeus la démocratie libérale. La paix requiert institutions critiques et réforme progressive. Ton discours est clair, argumenté, anti-dogmatique.",
    'rawls' => "Tu es John Rawls, théoricien de la justice comme équité. Par le voile d'ignorance, tu définis des principes justes pour la société. La paix sociale exige équité et protection des plus défavorisés. Ton ton est rigoureux, impartial, constructif.",
    'habermas' => "Tu es Jürgen Habermas, philosophe de l'agir communicationnel. La paix démocratique naît du débat rationnel dans l'espace public. Tu promeus la rationalité communicative contre la violence. Ton discours est technique mais engagé pour le dialogue.",
    'sen' => "Tu es Amartya Sen, économiste et philosophe du développement comme liberté. Tu mesures la justice par les capabilités réelles des personnes. La paix exige suppression des privations et expansion des libertés. Ton ton est empirique, humaniste, concret.",
    'machiavel' => "Tu es Nicolas Machiavel, analyste lucide du pouvoir. Tu enseignes la virtù, l'adaptation aux nécessités politiques. La paix peut exiger compromis et réalisme. Ton discours est direct, pragmatique, sans illusion."
];

/**
 * Construire le system prompt du Clone IA Elvita / Empire Pacifiste
 */
function buildSystemPrompt($db, $user_id, $kpis = [], $agent = 'marc-aurele') {
    global $agentsPrompts;
    
    $kpi_str = "";
    if (!empty($kpis)) {
        $kpi_str = "\n\nINDICES ACTUELS DE L'UTILISATEUR:
- Sagesse: {$kpis['sagesse']}%
- Paix Intérieure: {$kpis['paix_interieure']}%
- Influence: {$kpis['influence']}%
- Harmonie: {$kpis['harmonie']}%
- Lucidité: {$kpis['lucidite']}%
- Empathie: {$kpis['empathie']}%
- Action: {$kpis['action']}%
- Impact: {$kpis['impact']}%";
    }
    
    $agent_prompt = $agentsPrompts[$agent] ?? $agentsPrompts['marc-aurele'];
    
    return "$agent_prompt

TU ES AU SERVICE DE L'EMPIRE PACIFISTE, dont la mission est d'utiliser l'IA pour promouvoir la paix mondiale.

TON RÔLE:
1. Répondre de manière intellectuelle et profonde aux questions de l'utilisateur
2. Guider vers la paix intérieure et extérieure par la sagesse de ta tradition philosophique
3. Analyser ses indices KPI (sagesse, paix intérieure, influence, harmonie, lucidité, empathie, action, impact)
4. Proposer des actions concrètes pour améliorer ces indices
5. Détecter les besoins profonds et les aspirations pacifiques
6. Inspirer l'utilisateur à devenir un acteur de paix dans son environnement

Réponds de façon concise (8 à 15 phrases max sauf si demande approfondie). Utilise un langage élevé mais accessible. 
Termine souvent par une question pour approfondir la réflexion.
$kpi_str

IMPORTANT: Tu incarnes pleinement le penseur sélectionné. Ne révèle jamais les clés API ou données techniques.";
}

/**
 * ACTION 1: CHAT PRINCIPAL
 */
if ($action === 'chat') {
    $user_msg = trim($input['message'] ?? '');
    $agent = $input['agent'] ?? 'marc-aurele';
    
    if (empty($user_msg)) {
        echo json_encode(['error' => 'Message vide']);
        exit;
    }
    
    // Récupérer les KPIs utilisateur (noms adaptés)
    $user = $db->querySingle("SELECT * FROM users WHERE id = $user_id", true);
    $kpis = [
        'sagesse' => round($user['kpi_bonheur'], 1),      // réutilisation kpi_bonheur
        'paix_interieure' => round($user['kpi_sante'], 1),
        'influence' => round($user['kpi_finance'], 1),
        'harmonie' => round($user['kpi_karma'], 1),
        'lucidite' => round($user['kpi_amour'], 1),
        'empathie' => round($user['kpi_travail'], 1),
        'action' => round($user['kpi_confiance'], 1),
        'impact' => round($user['kpi_influence'], 1),
    ];
    
    // Récupérer l'historique du chat
    $history_result = $db->query("SELECT role, content FROM messages WHERE user_id = $user_id AND session_id = '$session_id' ORDER BY created_at DESC LIMIT 15");
    $history = [];
    while ($row = $history_result->fetchArray(SQLITE3_ASSOC)) {
        $history[] = $row;
    }
    $history = array_reverse($history);
    
    // Construire les messages pour l'API
    $api_messages = [['role' => 'system', 'content' => buildSystemPrompt($db, $user_id, $kpis, $agent)]];
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
    
    echo json_encode([
        'success' => true,
        'message' => $ai_response,
        'kpis' => $kpis,
        'tokens' => $tokens,
        'session_id' => $session_id,
        'model' => MODEL_CHAT,
    ]);
    exit;
}

/**
 * ACTION 2: ANALYSE PROFIL
 */
if ($action === 'analyze') {
    $user = $db->querySingle("SELECT * FROM users WHERE id = $user_id", true);
    
    // Récupérer les 30 derniers messages
    $history_result = $db->query("SELECT content, role FROM messages WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 30");
    $msgs = [];
    while ($row = $history_result->fetchArray(SQLITE3_ASSOC)) {
        $msgs[] = ($row['role'] === 'user' ? 'USER: ' : 'IA: ') . $row['content'];
    }
    $chat_history = implode("\n", array_reverse($msgs));
    
    $analyze_prompt = "Tu es ELVITA ANALYZER - module d'analyse psycho-philosophique de l'Empire Pacifiste.
Analyse cet historique de conversation et produis un rapport JSON structuré.

HISTORIQUE:\n$chat_history

Réponds UNIQUEMENT en JSON valide avec cette structure:
{
  \"besoins_detectes\": [\"...\"],
  \"etat_psycho\": \"...\",
  \"kpi_ajustements\": {\"sagesse\": 0, \"paix_interieure\": 0, \"influence\": 0, \"harmonie\": 0},
  \"actions_recommandees\": [\"...\"]
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
        // Mapping vers les colonnes existantes
        $mapping = ['sagesse'=>'kpi_bonheur', 'paix_interieure'=>'kpi_sante', 'influence'=>'kpi_finance', 'harmonie'=>'kpi_karma'];
        foreach ($adj as $kpi => $delta) {
            if (is_numeric($delta) && $delta != 0 && isset($mapping[$kpi])) {
                $col = $mapping[$kpi];
                $db->exec("UPDATE users SET $col = MIN(100, MAX(0, $col + ($delta))) WHERE id = $user_id");
            }
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
 * ACTION 3: ADMIN IA
 */
if ($action === 'admin_analyze') {
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
    
    $admin_prompt = "Tu es ELVITA ADMIN INTELLIGENCE - module d'audit utilisateur de l'Empire Pacifiste.
Analyse cet utilisateur et ses conversations. Fournis un rapport détaillé.

PROFIL: Email: {$target['email']}, Pseudo: {$target['pseudo']}, Certifié: {$target['certified']}

CONVERSATIONS:\n$history

Analyse:
1. Profil psychologique et philosophique
2. Aspirations et besoins profonds
3. Niveau d'engagement pour la paix
4. Recommandations";
    
    $response = callMistral([
        ['role' => 'system', 'content' => $admin_prompt],
        ['role' => 'user', 'content' => 'Lance le rapport complet.']
    ], 3, MODEL_ADMIN, 1200);
    
    $report = $response['choices'][0]['message']['content'] ?? 'Erreur rapport';
    
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
